// Inventory + check of every interactive element on every page, per role.
const { chromium } = require('playwright');
const B = process.env.ELMS_URL || 'http://localhost/Capstone1';
const HOST = new URL(B).hostname;
// Sessions for existing accounts in the sample data (see mksess.php)
for (const [sid, id, role, f, l] of [['thead', 1, 'Head Admin', 'System', 'Administrator'], ['tteach15', 15, 'Teacher', 'Jose', 'Dinglasan'], ['tteach14', 14, 'Teacher', 'Asd', 'Asd'], ['tstud10', 10, 'Student', 'Jerby', 'X'], ['tstud20', 20, 'Student', 'Ana', 'Reyes'], ['tstud24', 24, 'Student', 'Ella', 'Cruz']])
  require('child_process').execSync(`php mksess.php ${sid} ${id} '${role}' '${f}' '${l}'`);
const ROLES = {
  admin: ['thead', ['dashboard', 'accounts', 'accounts.php?tab=create', 'subjects-enrollment', 'subjects-enrollment.php?section=Grade%209', 'modules', 'announcements', 'announcements.php?tab=banners', 'awards', 'admins', 'admins.php?tab=log', 'settings']],
  teacher: ['tteach15', ['dashboard', 'classes', 'classes.php?class=25', 'classes.php?class=25&tab=modules', 'my-students', 'modules', 'assignments.php?course=37&section=Grade%209', 'grade.php?id=8', 'quizzes.php?course=37&section=Grade%209', 'grades.php?course=37&section=Grade%209', 'attendance.php?course=37&section=Grade%209', 'announcements', 'awards', 'profile', 'edubot']],
  student: ['tstud20', ['dashboard', 'my-subjects', 'modules', 'assignments', 'quizzes', 'grades', 'attendance', 'announcements', 'awards', 'profile']],
};
const BUILTIN = new Set(['if', 'for', 'while', 'switch', 'return', 'function', 'confirm', 'alert', 'prompt', 'setTimeout', 'clearTimeout', 'String', 'Number', 'parseInt', 'parseFloat', 'encodeURIComponent', 'decodeURIComponent', 'Boolean', 'JSON', 'Math', 'Date', 'event', 'this', 'typeof', 'new', 'Array', 'Object', 'catch', 'open', 'print', 'history', 'fetch', 'scrollTo', 'encodeURI']);
(async () => {
  const b = await chromium.launch();
  const totals = { elements: 0, ok: 0, broken: 0, noHandler: 0 };
  const problems = [];
  for (const [role, [sid, pages]] of Object.entries(ROLES)) {
    const ctx = await b.newContext({ viewport: { width: 1366, height: 900 } });
    await ctx.addCookies([{ name: 'PHPSESSID', value: sid, domain: HOST, path: '/' }]);
    const p = await ctx.newPage(); const cdp = await ctx.newCDPSession(p);
    const linkStatus = {};
    let roleCount = 0;
    for (const pg of pages) {
      const url = `${B}/${role}/${pg}${pg.includes('.php') ? '' : '.php'}`;
      await p.goto(url, { waitUntil: 'networkidle' }); await p.waitForTimeout(500);
      // tag every interactive element
      const items = await p.evaluate(() => {
        const els = [...document.querySelectorAll('button, a[href], [onclick], input[type=submit], input[type=button], select[onchange], [role=button], input[type=checkbox][onchange], input[type=radio][onchange]')];
        return els.map((el, i) => { el.setAttribute('data-audit', i); return {
          i, tag: el.tagName.toLowerCase(), text: (el.innerText || el.value || el.getAttribute('aria-label') || el.title || '').trim().replace(/\s+/g, ' ').slice(0, 40),
          onclick: el.getAttribute('onclick') || el.getAttribute('onchange') || '', href: el.tagName === 'A' ? el.getAttribute('href') : null,
          type: el.getAttribute('type') || '', inForm: !!el.closest('form'), disabled: el.disabled === true }; });
      });
      for (const it of items) {
        totals.elements++; roleCount++;
        let ok = true, why = '';
        if (it.onclick) {
          const code = it.onclick.replace(/'[^']*'|"[^"]*"|`[^`]*`/g, "''");
          const names = [...code.matchAll(/(^|[^.\w$])([A-Za-z_$][\w$]*)\s*\(/g)].map(m => m[2]).filter(n => !BUILTIN.has(n));
          for (const n of names) {
            const defined = await p.evaluate(n => typeof window[n] === 'function', n);
            if (!defined) { ok = false; why = `calls ${n}() which does not exist`; break; }
          }
          const hrefs = [...it.onclick.matchAll(/location\.href\s*=\s*'([^']+)'/g)].map(m => m[1]);
          for (const h of hrefs) if (!h.includes('${')) {
            const full = new URL(h, url).href.split('#')[0];
            if (!(full in linkStatus)) { const r = await p.request.get(full, { maxRedirects: 0 }).catch(() => null); linkStatus[full] = r ? r.status() : 0; }
            if (![200, 301, 302].includes(linkStatus[full])) { ok = false; why = `goes to ${h} → HTTP ${linkStatus[full]}`; }
          }
        } else if (it.href !== null) {
          const h = it.href;
          if (h === '#' || h === '' ) {
            // must have a JS listener, otherwise it does nothing
            const { result: object } = await cdp.send('Runtime.evaluate', { expression: `document.querySelector('[data-audit="${it.i}"]')` });
            const { listeners } = await cdp.send('DOMDebugger.getEventListeners', { objectId: object.objectId, depth: 0 });
            if (!listeners.some(l => l.type === 'click')) { ok = false; why = 'link "#" with no click handler'; }
          } else if (!/^(https?:|mailto:|tel:|javascript:)/.test(h) || h.startsWith(B)) {
            const full = new URL(h, url).href.split('#')[0];
            if (!(full in linkStatus)) { const r = await p.request.get(full, { maxRedirects: 0 }).catch(() => null); linkStatus[full] = r ? r.status() : 0; }
            const st = linkStatus[full];
            if (![200, 301, 302].includes(st)) { ok = false; why = `links to ${h} → HTTP ${st}`; }
          }
        } else if (it.tag === 'button' || it.tag === 'input' || it.onclick === '') {
          if (!(it.inForm && (it.type === 'submit' || it.type === ''))) {
            // listener bound with addEventListener on the element or a parent (delegation)?
            const { result: object } = await cdp.send('Runtime.evaluate', { expression: `document.querySelector('[data-audit="${it.i}"]')` });
            let has = false, node = object.objectId, depth = 0;
            while (node && depth < 12 && !has) {
              const { listeners } = await cdp.send('DOMDebugger.getEventListeners', { objectId: node, depth: 0 });
              has = listeners.some(l => ['click', 'mousedown', 'pointerdown', 'change', 'input', 'keydown'].includes(l.type));
              const parent = await cdp.send('Runtime.callFunctionOn', { objectId: node, functionDeclaration: 'function(){return this.parentElement || (this === document.documentElement ? document : null)}' });
              node = parent.result.objectId; depth++;
            }
            if (!has) { const w = await cdp.send('Runtime.evaluate', { expression: 'window' }); const { listeners } = await cdp.send('DOMDebugger.getEventListeners', { objectId: w.result.objectId, depth: 0 }); has = listeners.some(l => l.type === 'click'); }
            if (!has) { ok = false; why = 'button with no handler'; totals.noHandler++; }
          }
        }
        if (ok) totals.ok++; else { totals.broken++; problems.push(`${role}/${pg}  <${it.tag}> "${it.text}"  ${why}  [${(it.onclick || it.href || '').slice(0, 70)}]`); }
      }
    }
    console.log(`${role}: ${roleCount} interactive elements on ${pages.length} pages`);
    await ctx.close();
  }
  console.log(JSON.stringify(totals));
  console.log(problems.join('\n'));
  require('fs').writeFileSync('buttons-report.txt', JSON.stringify(totals) + '\n' + problems.join('\n'));
  await b.close();
})();
