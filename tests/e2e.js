// Multi-user end-to-end test: Admin, Teacher, Student in separate browsers.
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const B = process.env.ELMS_URL || 'http://localhost/Capstone1';
const HOST = new URL(B).hostname;
// Sessions for existing accounts in the sample data (see mksess.php)
for (const [sid, id, role, f, l] of [['thead', 1, 'Head Admin', 'System', 'Administrator'], ['tteach15', 15, 'Teacher', 'Jose', 'Dinglasan'], ['tteach14', 14, 'Teacher', 'Asd', 'Asd'], ['tstud10', 10, 'Student', 'Jerby', 'X'], ['tstud20', 20, 'Student', 'Ana', 'Reyes'], ['tstud24', 24, 'Student', 'Ella', 'Cruz']])
  require('child_process').execSync(`php mksess.php ${sid} ${id} '${role}' '${f}' '${l}'`);
const sql = q => execSync(`mysql -uroot elmsdb -N -e "${q.replace(/"/g, '\\"')}"`).toString().trim();
const mksess = (sid, id, role, f, l) => execSync(`php mksess.php ${sid} ${id} '${role}' '${f}' '${l}'`);
let pass = 0, fail = 0; const results = [];
function check(name, ok, info = '') { (ok ? pass++ : fail++); results.push(`${ok ? 'PASS' : 'FAIL'}  ${name}${info ? '  — ' + info : ''}`); console.log(results[results.length - 1]); }
async function ctxFor(b, sid) { const c = await b.newContext({ viewport: { width: 1280, height: 860 } }); await c.addCookies([{ name: 'PHPSESSID', value: sid, domain: HOST, path: '/' }]); return c; }
async function api(page, path, opts = {}) {
  return page.evaluate(async ([u, o]) => { const r = await fetch(u, o); let j; try { j = await r.json(); } catch (e) { j = { raw: true }; } j._status = r.status; return j; },
    [B + '/api/' + path, { method: opts.method || 'GET', headers: opts.json ? { 'Content-Type': 'application/json' } : {}, body: opts.json ? JSON.stringify(opts.json) : undefined }]);
}
async function poll(page) { await page.evaluate(() => window.ElmsLive && ElmsLive.poll()); await page.waitForTimeout(1500); }
async function toasts(page) { return page.evaluate(() => [...document.querySelectorAll('.lv-toast span')].map(s => s.textContent)); }

(async () => {
  const b = await chromium.launch();
  const errors = [];
  const watch = (p, who) => { p.on('pageerror', e => errors.push(who + ': ' + e.message)); p.on('console', m => { if (m.type() === 'error' && !/Failed to load resource|net::ERR|cdn|fonts/.test(m.text())) errors.push(who + ': ' + m.text()); }); };
  const stamp = Date.now() % 100000;

  // ── ADMIN ───────────────────────────────────────────
  const adminCtx = await ctxFor(b, 'thead'); const admin = await adminCtx.newPage(); watch(admin, 'admin');
  await admin.goto(B + '/admin/dashboard.php', { waitUntil: 'networkidle' });

  // T1 Admin creates Teacher
  let r = await api(admin, 'accounts.php?action=create', { method: 'POST', json: { first_name: 'Quinn', last_name: 'Teacher', role: 'Teacher', school_id: 'QA-T' + stamp, username: 'qa.t' + stamp, email: `qa.t${stamp}@example.com`, password: 'secret123', confirm_password: 'secret123', status: 'Active' } });
  const tId = +sql(`SELECT id FROM users WHERE username='qa.t${stamp}'`);
  check('T1 Admin creates Teacher → saved in users', r.success && tId > 0, r.message);
  r = await api(admin, 'accounts.php?action=create', { method: 'POST', json: { first_name: 'No', last_name: 'Email', role: 'Teacher', school_id: 'QA-N' + stamp, username: 'qa.n' + stamp, email: '', password: 'secret123', confirm_password: 'secret123' } });
  check('T1b Teacher without email is rejected (could never receive sign-in code)', !r.success, r.message);

  // T2 Admin creates Student (Grade 9)
  r = await api(admin, 'accounts.php?action=create', { method: 'POST', json: { first_name: 'Sam', last_name: 'Student', role: 'Student', school_id: 'QA-S' + stamp, username: 'qa.s' + stamp, email: `qa.s${stamp}@example.com`, password: 'secret123', confirm_password: 'secret123', section_dept: 'Grade 9', status: 'Active' } });
  const sId = +sql(`SELECT id FROM users WHERE username='qa.s${stamp}'`);
  check('T2 Admin creates Student → saved with section', r.success && sql(`SELECT section_dept FROM users WHERE id=${sId}`) === 'Grade 9', r.message);
  r = await api(admin, 'accounts.php?action=create', { method: 'POST', json: { first_name: 'Bad', last_name: 'Section', role: 'Student', school_id: 'QA-B' + stamp, username: 'qa.b' + stamp, email: `qa.b${stamp}@example.com`, password: 'secret123', confirm_password: 'secret123', section_dept: 'Grade 99' } });
  check('T2b Student with a section that does not exist is rejected', !r.success, r.message);

  // Real login form: a new Admin account signs in (admins skip the e-mail code)
  r = await api(admin, 'accounts.php?action=create', { method: 'POST', json: { first_name: 'Qa', last_name: 'Admin', role: 'Admin', school_id: 'QA-A' + stamp, username: 'qa.a' + stamp, email: `qa.a${stamp}@example.com`, password: 'secret123', confirm_password: 'secret123' } });
  const loginCtx = await b.newContext(); const lp = await loginCtx.newPage();
  await lp.goto(B + '/login.php');
  await lp.fill('input[name="login"], input[name="username"]', 'qa.a' + stamp); await lp.fill('input[name="password"]', 'secret123');
  await Promise.all([lp.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => {}), lp.press('input[name="password"]', 'Enter')]);
  check('T1c New account logs in through the real login form → its dashboard', /admin\/dashboard\.php/.test(lp.url()), lp.url().replace(B, ''));
  await lp.goto(B + '/teacher/dashboard.php'); check('Admin opening a Teacher page is sent to the admin dashboard', /admin\/dashboard/.test(lp.url()));
  await lp.goto(B + '/logout.php'); await lp.goto(B + '/admin/dashboard.php');
  check('Logout → protected page goes to login', /login\.php/.test(lp.url()));
  await loginCtx.close();

  // Sessions for the new teacher + student (their real login needs the e-mailed code)
  mksess('qateach', tId, 'Teacher', 'Quinn', 'Teacher'); mksess('qastud', sId, 'Student', 'Sam', 'Student');
  const teachCtx = await ctxFor(b, 'qateach'); const teacher = await teachCtx.newPage(); watch(teacher, 'teacher');
  const studCtx = await ctxFor(b, 'qastud'); const student = await studCtx.newPage(); watch(student, 'student');
  await teacher.goto(B + '/teacher/classes.php', { waitUntil: 'networkidle' });
  await student.goto(B + '/student/my-subjects.php', { waitUntil: 'networkidle' });

  // T3 Admin assigns Teacher to a class (Grade 9 · Science 9) — teacher's open page updates live
  const taId = +sql(`SELECT ta.id FROM teacher_assignments ta JOIN courses c ON c.id=ta.course_id WHERE ta.section='Grade 9' AND c.course_code='HS9-SCI'`);
  const prevTeacher = sql(`SELECT IFNULL(teacher_id,'NULL') FROM teacher_assignments WHERE id=${taId}`);
  r = await api(admin, 'sections.php?action=set_teacher', { method: 'POST', json: { id: taId, teacher_id: tId } });
  check('T3 Admin assigns Teacher → teacher_assignments updated', r.success && +sql(`SELECT teacher_id FROM teacher_assignments WHERE id=${taId}`) === tId, r.message);
  await poll(teacher); await teacher.waitForLoadState('networkidle'); await teacher.waitForTimeout(800);
  check('T3 live: Teacher\'s open My Classes shows the class without a manual refresh', await teacher.locator('.cl-card', { hasText: 'Science 9' }).count() === 1);

  // T4 Admin enrolls Student in Science 9 + Filipino 9 — student and teacher both see it
  const sci = +sql(`SELECT id FROM courses WHERE course_code='HS9-SCI'`), fil = +sql(`SELECT id FROM courses WHERE course_code='HS9-FIL'`);
  r = await api(admin, 'sections.php?action=set_student_subjects', { method: 'POST', json: { student_id: sId, section: 'Grade 9', course_ids: [sci, fil] } });
  check('T4 Admin enrolls Student → enrollments saved', r.success && +sql(`SELECT COUNT(*) FROM enrollments WHERE student_id=${sId} AND status='Enrolled'`) === 2, r.message);
  await poll(student); await student.waitForLoadState('networkidle'); await student.waitForTimeout(800);
  check('T4 live: Student\'s open My Subjects shows Science 9 with their teacher', await student.locator('.sj-card', { hasText: 'Science 9' }).filter({ hasText: 'Teacher, Quinn' }).count() === 1);
  await teacher.goto(B + '/teacher/classes.php?class=' + taId, { waitUntil: 'networkidle' });
  check('T4 Teacher sees the Student in the class list', await teacher.locator('tr', { hasText: 'QA-S' + stamp }).count() === 1);

  // T5 Teacher creates an assignment — student page updates live
  await student.goto(B + '/student/assignments.php', { waitUntil: 'networkidle' });
  await teacher.goto(B + `/teacher/assignments.php?course=${sci}&section=Grade%209`, { waitUntil: 'networkidle' });
  const title = 'QA Lab Report ' + stamp;
  const due = new Date(Date.now() + 3 * 864e5).toISOString().slice(0, 16).replace('T', ' ') + ':00';
  r = await teacher.evaluate(async ([u, t, d, c]) => { const fd = new FormData(); fd.append('course_id', c); fd.append('title', t); fd.append('description', 'Write it up'); fd.append('max_score', '20'); fd.append('due_date', d); const x = await fetch(u, { method: 'POST', body: fd }); return x.json(); }, [B + '/api/assignments.php?action=create', title, due, sci]);
  const aId = +sql(`SELECT id FROM assignments WHERE title='${title}'`);
  check('T5 Teacher creates Assignment → saved with owner + gradebook item', r.success && +sql(`SELECT teacher_id FROM assignments WHERE id=${aId}`) === tId && +sql(`SELECT COUNT(*) FROM gradebook_items WHERE source_type='assignment' AND source_id=${aId}`) === 1, r.message);
  r = await teacher.evaluate(async ([u, t, d, c]) => { const fd = new FormData(); fd.append('course_id', c); fd.append('title', t); fd.append('max_score', '20'); fd.append('due_date', d); const x = await fetch(u, { method: 'POST', body: fd }); return x.json(); }, [B + '/api/assignments.php?action=create', title, due, sci]);
  check('T5b Double submit of the same assignment does not create a duplicate', r.success && r.duplicate === true && +sql(`SELECT COUNT(*) FROM assignments WHERE title='${title}'`) === 1);
  // wait for the real 15-second timer once (no manual poll)
  // students update in place (5 s timer): wait until it shows, then read the toast while it is up
  for (let i = 0; i < 40 && !(await student.locator(`text=${title}`).count()); i++) await student.waitForTimeout(500);
  const tt = await toasts(student);
  check('T5 live (real 5 s timer, no reload): Student page shows the new assignment + toast', await student.locator(`text=${title}`).count() > 0 && tt.some(t => t.includes(title)), 'toasts: ' + JSON.stringify(tt));
  await poll(teacher); await teacher.waitForTimeout(1500);   // teacher pages still use the 15 s timer
  check('T5 Teacher sees the assignment in their list', await teacher.locator(`text=${title}`).count() > 0);

  // T6 Student submits through the real UI — teacher list updates live
  await student.locator('.accordion[data-code="HS9-SCI"] .acc-head').click();
  const row = student.locator('.acc-row', { hasText: title });
  await row.locator('button', { hasText: /Submit|Upload|Turn in/i }).first().click();
  await student.setInputFiles('#assign-submit-file', 'test.pdf');
  await student.click('#assignSubmitModal .btn-primary');
  await student.waitForTimeout(1500);
  check('T6 Student submits → submission saved for the right student + assignment', +sql(`SELECT COUNT(*) FROM submissions WHERE assignment_id=${aId} AND student_id=${sId} AND attempt_count=1`) === 1);
  await poll(teacher);
  const tToasts = await toasts(teacher);
  check('T6 live: Teacher gets a "submitted" notification', tToasts.some(t => /submitted/.test(t) && t.includes(title)), JSON.stringify(tToasts));
  check('T6 live: Teacher\'s assignment list refreshed to show the submission', await teacher.locator('.asg-row', { hasText: title }).filter({ hasText: /1 of \d+ submitted/ }).count() === 1);
  const fake = await api(student, 'assignments.php?action=grade_one', { method: 'POST', json: { assignment_id: aId, student_id: sId, score: 20 } });
  check('Student cannot grade (grade_one as student)', !fake.success);

  // T7 Teacher grades — student grades page updates live
  await student.goto(B + '/student/grades.php', { waitUntil: 'networkidle' });
  r = await api(teacher, 'assignments.php?action=grade_one', { method: 'POST', json: { assignment_id: aId, student_id: sId, score: 17, feedback: 'Good work' } });
  check('T7 Teacher grades → score saved', r.success && sql(`SELECT score FROM submissions WHERE assignment_id=${aId} AND student_id=${sId}`) === '17.00', r.message);
  await poll(student); await student.waitForLoadState('networkidle'); await student.waitForTimeout(800);
  check('T7 live: Student Grades shows 17 / 20 without refresh', await student.locator('td', { hasText: '17 / 20' }).count() >= 1);
  const gb = await api(teacher, `gradebook.php?action=list&course_id=${sci}&section=Grade%209`);
  const stuTot = gb.totals && gb.totals[sId];
  const studentPct = await student.locator(`#g-${sci} .acc-head`).innerText();
  check('Student total = Teacher gradebook total (one source of truth)', stuTot && studentPct.includes(stuTot.percent + '%'), `teacher ${stuTot && stuTot.percent}% · student "${studentPct.replace(/\s+/g, ' ').slice(-20)}"`);

  // T8 Teacher saves attendance — student attendance page
  await student.goto(B + '/student/attendance.php', { waitUntil: 'networkidle' });
  let day = new Date(); while ([0, 6].includes(day.getDay())) day = new Date(day - 864e5);
  const ds = day.toISOString().slice(0, 10);
  r = await api(teacher, 'attendance.php?action=save', { method: 'POST', json: { course_id: sci, date: ds, section: 'Grade 9', records: [{ student_id: sId, status: 'Late', remark: 'Traffic' }] } });
  check('T8 Teacher saves attendance → row saved (student, subject, date, status, recorded_by)', r.success && sql(`SELECT CONCAT(status,'|',recorded_by) FROM attendance WHERE student_id=${sId} AND course_id=${sci} AND date='${ds}'`) === `Late|${tId}`, r.message);
  r = await api(teacher, 'attendance.php?action=save', { method: 'POST', json: { course_id: sci, date: ds, section: 'Grade 9', records: [{ student_id: sId, status: 'Late', remark: 'Traffic' }] } });
  check('T8b Saving the same day again updates, never duplicates', +sql(`SELECT COUNT(*) FROM attendance WHERE student_id=${sId} AND course_id=${sci} AND date='${ds}'`) === 1);
  await poll(student); await student.waitForTimeout(1500);
  const sToasts = await toasts(student);
  check('T8 live: Student is notified of the attendance update', sToasts.some(t => /Attendance updated/.test(t)), JSON.stringify(sToasts));

  // T9 Teacher announcement — student receives it; bell unread count
  await student.goto(B + '/student/dashboard.php', { waitUntil: 'networkidle' });
  const before = await api(student, 'sync.php?action=notifications');
  r = await api(teacher, 'announcements.php?action=create', { method: 'POST', json: { course_id: sci, title: 'QA Lab moved ' + stamp, body: 'Room 204' } });
  check('T9 Teacher posts announcement → saved', r.success, r.message);
  await poll(student); await student.waitForTimeout(1200);
  const after = await api(student, 'sync.php?action=notifications');
  check('T9 live: Student bell gets the announcement (unread +1)', after.unread > before.unread && after.items.some(i => i.msg.includes('QA Lab moved')), `${before.unread} → ${after.unread}`);
  r = await api(student, 'sync.php?action=mark_read', { method: 'POST' });
  const afterRead = await api(student, 'sync.php?action=notifications');
  check('Mark all read is saved on the server', afterRead.unread === 0);
  const other = await api(admin, 'sync.php?action=notifications');
  check('Teacher\'s class announcement does not reach admins\' bell', !(other.items || []).some(i => i.msg.includes('QA Lab moved')));

  // Section isolation: two teachers share General Mathematics in different sections
  const tA = await ctxFor(b, 'tteach15'); const t15 = await tA.newPage(); await t15.goto(B + '/teacher/dashboard.php');
  const tB = await ctxFor(b, 'tteach14'); const t14 = await tB.newPage(); await t14.goto(B + '/teacher/dashboard.php');
  const sB = await ctxFor(b, 'tstud10'); const jer = await sB.newPage(); await jer.goto(B + '/student/dashboard.php');
  const gen = +sql(`SELECT id FROM courses WHERE course_code='SHS-GENMATH'`);
  const other15 = 'QA ABM-B only ' + stamp;
  await t15.evaluate(async ([u, t, c]) => { const fd = new FormData(); fd.append('course_id', c); fd.append('title', t); fd.append('max_score', '10'); await fetch(u, { method: 'POST', body: fd }); }, [B + '/api/assignments.php?action=create', other15, gen]);
  const jerList = await api(jer, 'assignments.php?action=my_list');
  check('Isolation: ABM-A student does not get ABM-B teacher\'s assignment', !(jerList.data || []).some(a => a.title === other15));
  const t14List = await api(t14, `assignments.php?action=list&course_id=${gen}`);
  check('Isolation: ABM-A teacher does not see it in their list', !(t14List.data || []).some(a => a.title === other15));
  const t14gb = await api(t14, `gradebook.php?action=list&course_id=${gen}`);
  check('Isolation: …nor as a "Missing" column in their gradebook', !(t14gb.items || []).some(i => i.title === other15));
  const oid = +sql(`SELECT id FROM assignments WHERE title='${other15}'`);
  const del = await api(t14, `assignments.php?action=delete&id=${oid}`, { method: 'DELETE' });
  check('Isolation: ABM-A teacher cannot delete it', !del.success && +sql(`SELECT COUNT(*) FROM assignments WHERE id=${oid}`) === 1);
  await poll(jer); const jt = await toasts(jer);
  check('Isolation: no live notification leaks to the other section', !jt.some(t => t.includes(other15)), JSON.stringify(jt));

  // Security probes
  const anon = await (await b.newContext()).newPage(); await anon.goto(B + '/login.php');
  const probes = [
    ['Student → Admin API (accounts list)', student, 'accounts.php?action=list'],
    ['Teacher → Admin API (enrollments list)', teacher, 'enrollments.php?action=list'],
    ['Teacher → Admin API (create account)', teacher, 'accounts.php?action=create', { method: 'POST', json: { role: 'Admin' } }],
    ['Student → Teacher API (gradebook)', student, `gradebook.php?action=list&course_id=${sci}`],
    ['Teacher → other teacher\'s class gradebook', t14, `gradebook.php?action=list&course_id=${fil}`],
    ['Teacher → other teacher\'s quiz', t14, 'quizzes.php?action=get&id=12'],
    ['Unauthenticated → live feed', anon, 'sync.php?action=poll&after=1'],
    ['Unauthenticated → assignments', anon, 'assignments.php?action=my_list'],
    ['Unauthenticated → announcements', anon, 'announcements.php?action=list'],
  ];
  for (const [name, pg, path, o] of probes) { const x = await api(pg, path, o || {}); check('Security: ' + name + ' is refused', !x.success, 'HTTP ' + x._status + ' ' + (x.message || '')); }
  const g2 = await api(student, `assignments.php?action=grade`, { method: 'POST', json: { grades: [{ submission_id: 1, score: 100 }] } });
  check('Security: Student cannot change grades (bulk grade)', !g2.success);

  // Network failure: go offline, teacher grades again, come back online → student catches up
  await student.goto(B + '/student/grades.php', { waitUntil: 'networkidle' });
  await studCtx.setOffline(true);
  for (let i = 0; i < 3; i++) await student.evaluate(() => ElmsLive.poll()).catch(() => {});
  await student.waitForTimeout(1500);
  const st = await student.evaluate(() => ElmsLive.state());
  check('Offline: page keeps working and shows "Connection lost"', st.failures >= 1 && await student.locator('#lvBar.show').count() === 1, JSON.stringify(st));
  await api(teacher, 'assignments.php?action=grade_one', { method: 'POST', json: { assignment_id: aId, student_id: sId, score: 19, feedback: 'Updated' } });
  await studCtx.setOffline(false);
  await student.evaluate(() => window.dispatchEvent(new Event('online')));
  await student.waitForTimeout(2500); await student.waitForLoadState('networkidle'); await student.waitForTimeout(800);
  check('Back online: missed grade change arrives and page shows 19 / 20', await student.locator('td', { hasText: '19 / 20' }).count() >= 1);

  // Admin deactivates the student → their open page stops and asks to sign in
  r = await api(admin, 'accounts.php?action=update', { method: 'PUT', json: { id: sId, first_name: 'Sam', last_name: 'Student', role: 'Student', school_id: 'QA-S' + stamp, email: `qa.s${stamp}@example.com`, section_dept: 'Grade 9', status: 'Inactive' } });
  check('Admin deactivates Student → saved', r.success && sql(`SELECT status FROM users WHERE id=${sId}`) === 'Inactive', r.message);
  await poll(student);
  check('Deactivated student: live page shows "session has ended"', await student.locator('#lvBar.warn', { hasText: /session has ended/i }).count() === 1);
  await student.goto(B + '/student/grades.php'); check('Deactivated student is signed out on next page', /login\.php/.test(student.url()));

  // Head Admin cannot demote / deactivate themselves
  r = await api(admin, 'accounts.php?action=update', { method: 'PUT', json: { id: 1, first_name: 'System', last_name: 'Administrator', role: 'Teacher', school_id: sql('SELECT school_id FROM users WHERE id=1'), email: 'admin@gmail.com', status: 'Inactive' } });
  check('Head Admin edit keeps role Head Admin and Active', sql('SELECT CONCAT(role,"|",status) FROM users WHERE id=1') === 'Head Admin|Active');

  // restore the class teacher we changed
  sql(`UPDATE teacher_assignments SET teacher_id=${prevTeacher} WHERE id=${taId}`);
  console.log('\nJS errors during the run:', errors.length ? errors : 'none');
  console.log(`\n${pass} passed, ${fail} failed`);
  require('fs').writeFileSync('e2e-results.txt', results.join('\n') + `\n\nJS errors: ${errors.length ? errors.join('\n') : 'none'}\n${pass} passed, ${fail} failed\n`);
  await b.close();
})().catch(e => { console.error('CRASH', e); process.exit(1); });
