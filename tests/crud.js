// CRUD of every role's actions, each checked against the database.
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const fs = require('fs');
const B = process.env.ELMS_URL || 'http://localhost/Capstone1';
const HOST = new URL(B).hostname;
// Sessions for existing accounts in the sample data (see mksess.php)
for (const [sid, id, role, f, l] of [['thead', 1, 'Head Admin', 'System', 'Administrator'], ['tteach15', 15, 'Teacher', 'Jose', 'Dinglasan'], ['tteach14', 14, 'Teacher', 'Asd', 'Asd'], ['tstud10', 10, 'Student', 'Jerby', 'X'], ['tstud20', 20, 'Student', 'Ana', 'Reyes'], ['tstud24', 24, 'Student', 'Ella', 'Cruz']])
  require('child_process').execSync(`php mksess.php ${sid} ${id} '${role}' '${f}' '${l}'`);
const sql = q => execSync(`mysql -uroot elmsdb -N -e "${q.replace(/"/g, '\\"')}"`).toString().trim();
let pass = 0, fail = 0; const out = [];
const check = (n, ok, info = '') => { ok ? pass++ : fail++; const l = `${ok ? 'PASS' : 'FAIL'}  ${n}${info ? '  — ' + info : ''}`; out.push(l); console.log(l); };
async function page(b, sid, path) { const c = await b.newContext(); await c.addCookies([{ name: 'PHPSESSID', value: sid, domain: HOST, path: '/' }]); const p = await c.newPage(); await p.goto(B + path, { waitUntil: 'networkidle' }); return p; }
const call = (p, path, o = {}) => p.evaluate(async ([u, o]) => {
  let body = undefined, headers = o.headers || {};
  if (o.json !== undefined) { body = JSON.stringify(o.json); headers['Content-Type'] = 'application/json'; }
  if (o.form) { body = new FormData(); for (const [k, v] of Object.entries(o.form)) body.append(k, v); }
  if (o.file) { const bin = Uint8Array.from(atob(o.file.b64), c => c.charCodeAt(0)); body.append(o.file.field, new Blob([bin], { type: o.file.type }), o.file.name); }
  const r = await fetch(u, { method: o.method || (body ? 'POST' : 'GET'), headers, body }); const t = await r.text();
  try { const j = JSON.parse(t); j._s = r.status; return j; } catch (e) { return { _s: r.status, _raw: t.slice(0, 200) }; }
}, [B + '/api/' + path, o]);
const png = { field: 'image', name: 'b.png', type: 'image/png', b64: fs.readFileSync('test.png').toString('base64') };
const pdf = { field: 'file', name: 'q.pdf', type: 'application/pdf', b64: fs.readFileSync('test.pdf').toString('base64') };

(async () => {
  const b = await chromium.launch(); const st = Date.now() % 100000;
  // ═══ ADMIN ═══
  const A = await page(b, 'thead', '/admin/subjects-enrollment.php');
  let r = await call(A, 'sections.php?action=create_section', { json: { name: 'Grade 9 - QA' + st, level: 'HS' } });
  check('Admin: Add section', r.success && sql(`SELECT COUNT(*) FROM sections WHERE name='Grade 9 - QA${st}'`) === '1', r.message);
  const secId = sql(`SELECT id FROM sections WHERE name='Grade 9 - QA${st}'`);
  r = await call(A, 'sections.php?action=update_section', { json: { id: +secId, name: 'Grade 9 - QB' + st, level: 'HS', status: 'Active' } });
  check('Admin: Rename section', r.success && sql(`SELECT name FROM sections WHERE id=${secId}`) === 'Grade 9 - QB' + st, r.message);
  const sec = 'Grade 9 - QB' + st;
  r = await call(A, 'sections.php?action=add_subject', { json: { section: sec, course_ids: [34, 35], teacher_id: 15, school_year: '2025-2026', semester: '1st' } });
  check('Admin: Add subjects to section with a teacher', r.success && sql(`SELECT COUNT(*) FROM teacher_assignments WHERE section='${sec}' AND teacher_id=15`) === '2', r.message);
  r = await call(A, 'accounts.php?action=create', { json: { first_name: 'Cee', last_name: 'Rud', role: 'Student', school_id: 'CR-' + st, username: 'cr' + st, email: `cr${st}@example.com`, password: 'secret123', confirm_password: 'secret123', section_dept: sec } });
  const sid = +sql(`SELECT id FROM users WHERE username='cr${st}'`);
  check('Admin: Add student into new section', r.success && sid > 0, r.message);
  r = await call(A, 'sections.php?action=enroll_all', { json: { section: sec } });
  check('Admin: Enroll all in section', r.success && sql(`SELECT COUNT(*) FROM enrollments WHERE student_id=${sid} AND status='Enrolled'`) === '2', r.message);
  r = await call(A, 'sections.php?action=enroll_all', { json: { section: sec } });
  check('Admin: Enroll all again does not duplicate', sql(`SELECT COUNT(*) FROM enrollments WHERE student_id=${sid}`) === '2');
  r = await call(A, 'sections.php?action=set_student_subjects', { json: { student_id: sid, section: sec, course_ids: [34] } });
  check('Admin: Remove one enrollment (Manage student)', r.success && sql(`SELECT COUNT(*) FROM enrollments WHERE student_id=${sid}`) === '1', r.message);
  const ta35 = sql(`SELECT id FROM teacher_assignments WHERE section='${sec}' AND course_id=35`);
  r = await call(A, 'sections.php?action=set_teacher', { json: { id: +ta35, teacher_id: null } });
  check('Admin: Unassign teacher from a subject', r.success && sql(`SELECT IFNULL(teacher_id,'NULL') FROM teacher_assignments WHERE id=${ta35}`) === 'NULL', r.message);
  r = await call(A, 'sections.php?action=remove_subject', { json: { id: +ta35 } });
  check('Admin: Remove subject from section', r.success && sql(`SELECT COUNT(*) FROM teacher_assignments WHERE id=${ta35}`) === '0', r.message);
  r = await call(A, 'accounts.php?action=update', { method: 'PUT', json: { id: sid, first_name: 'Cee', last_name: 'Rudd', role: 'Student', school_id: 'CR-' + st, email: `cr${st}@example.com`, section_dept: sec, status: 'Active' } });
  check('Admin: Edit student', r.success && sql(`SELECT last_name FROM users WHERE id=${sid}`) === 'Rudd', r.message);
  r = await call(A, 'accounts.php?action=update', { method: 'PUT', json: { id: sid, first_name: 'Cee', last_name: 'Rudd', role: 'Teacher', school_id: 'CR-' + st, email: `cr${st}@example.com`, section_dept: sec, status: 'Active' } });
  check('Admin: Student with enrollments cannot be turned into a Teacher', !r.success, r.message);
  r = await call(A, 'accounts.php?action=update', { method: 'PUT', json: { id: sid, first_name: 'Cee', last_name: 'Rudd', role: 'Student', school_id: sql('SELECT school_id FROM users WHERE id=20'), email: `cr${st}@example.com`, section_dept: sec } });
  check('Admin: Duplicate School ID on edit is rejected', !r.success, r.message);
  r = await call(A, 'accounts.php?action=update', { method: 'PUT', json: { id: sid, first_name: 'Cee', last_name: 'Rudd', role: 'Student', school_id: 'CR-' + st, email: `cr${st}@example.com`, section_dept: sec, status: 'Inactive' } });
  check('Admin: Disable student', r.success && sql(`SELECT status FROM users WHERE id=${sid}`) === 'Inactive');
  r = await call(A, 'sections.php?action=delete_section', { json: { id: +secId } });
  check('Admin: Section that still has subjects/students is protected from delete', !r.success || sql(`SELECT COUNT(*) FROM sections WHERE id=${secId}`) === '0', r.message);
  r = await call(A, `accounts.php?action=delete&id=${sid}`, { method: 'DELETE' });
  check('Admin: Delete student (enrollments removed with it)', r.success && sql(`SELECT COUNT(*) FROM enrollments WHERE student_id=${sid}`) === '0', r.message);
  for (const t of sql(`SELECT id FROM teacher_assignments WHERE section='${sec}'`).split('\n').filter(Boolean)) await call(A, 'sections.php?action=remove_subject', { json: { id: +t } });
  r = await call(A, 'sections.php?action=delete_section', { json: { id: +secId } });
  check('Admin: Delete empty section', r.success && sql(`SELECT COUNT(*) FROM sections WHERE id=${secId}`) === '0', r.message);
  // admin accounts + permissions
  r = await call(A, 'accounts.php?action=create', { json: { first_name: 'Reg', last_name: 'Admin', role: 'Admin', school_id: 'RA-' + st, username: 'ra' + st, email: `ra${st}@example.com`, password: 'secret123', confirm_password: 'secret123', permissions: { view_students: true } } });
  const aid = +sql(`SELECT id FROM users WHERE username='ra${st}'`);
  check('Head Admin: Create admin with permissions', r.success && sql(`SELECT COUNT(*) FROM admin_permissions WHERE admin_id=${aid} AND granted=1`) >= '1', r.message);
  r = await call(A, `accounts.php?action=get_permissions&admin_id=${aid}`); check('Head Admin: Read permissions', r.success !== false, r.message || '');
  r = await call(A, 'accounts.php?action=save_permissions', { json: { admin_id: aid, permissions: { view_students: true, manage_banners: true } } });
  check('Head Admin: Save permissions', r.success && sql(`SELECT granted FROM admin_permissions WHERE admin_id=${aid} AND perm_key='manage_banners'`) === '1', r.message);
  r = await call(A, 'accounts.php?action=toggle_status', { json: { id: aid } });
  check('Head Admin: Disable admin', r.success && sql(`SELECT status FROM users WHERE id=${aid}`) === 'Inactive', r.message);
  r = await call(A, `accounts.php?action=delete&id=${aid}`, { method: 'DELETE' }); check('Head Admin: Delete admin', r.success, r.message);
  // announcements + banners
  r = await call(A, 'announcements.php?action=create', { form: { title: 'Admin QA ' + st, body: 'Hello school', target_audience: 'Teachers' } });
  const annId = sql(`SELECT id FROM announcements WHERE title='Admin QA ${st}'`);
  check('Admin: Publish announcement (Teachers only)', r.success && annId !== '', r.message);
  const S20 = await page(b, 'tstud20', '/student/announcements.php');
  check('…students do NOT see a Teachers-only post', !(await S20.content()).includes('Admin QA ' + st));
  const T15 = await page(b, 'tteach15', '/teacher/announcements.php');
  check('…teachers DO see it', (await T15.content()).includes('Admin QA ' + st));
  r = await call(A, `announcements.php?action=delete&id=${annId}`, { method: 'DELETE' }); check('Admin: Delete announcement', r.success && sql(`SELECT COUNT(*) FROM announcements WHERE id=${annId}`) === '0', r.message);
  r = await call(A, 'banners.php?action=create', { form: { title: 'QA banner', caption: 'c', link_url: '', status: 'Active' }, file: png });
  const bid = sql(`SELECT id FROM dashboard_banners WHERE title='QA banner' ORDER BY id DESC LIMIT 1`);
  check('Admin: Add dashboard banner (with image)', r.success && bid !== '', r.message);
  r = await call(A, 'banners.php?action=update', { json: { id: +bid, title: 'QA banner 2', caption: 'c', link_url: '', status: 'Inactive' } });
  check('Admin: Edit banner', r.success && sql(`SELECT CONCAT(title,'|',status) FROM dashboard_banners WHERE id=${bid}`) === 'QA banner 2|Inactive', r.message);
  r = await call(A, 'banners.php?action=move', { json: { id: +bid, direction: 'up' } }); check('Admin: Reorder banner', r.success, r.message);
  r = await call(A, `banners.php?action=delete&id=${bid}`, { method: 'DELETE' }); check('Admin: Delete banner', r.success && sql(`SELECT COUNT(*) FROM dashboard_banners WHERE id=${bid}`) === '0', r.message);
  // modules
  r = await call(A, 'admin_modules.php?action=save', { form: { course_id: 37, title: 'QA Module ' + st, description: 'd', content: '<p>Lesson <script>alert(1)</script><b>bold</b></p>' } });
  const mid = sql(`SELECT id FROM modules WHERE title='QA Module ${st}'`);
  check('Admin: Create module (scripts stripped)', r.success && mid !== '' && !sql(`SELECT content FROM modules WHERE id=${mid}`).includes('script'), r.message);
  r = await call(A, 'admin_modules.php?action=save', { form: { id: mid, title: 'QA Module ' + st + ' v2', description: 'd', content: '<p>Lesson 2</p>' } });
  check('Admin: Edit module', r.success && sql(`SELECT title FROM modules WHERE id=${mid}`).endsWith('v2'), r.message);
  r = await call(A, 'admin_modules.php?action=move', { form: { id: mid, dir: 'up' } }); check('Admin: Reorder module', r.success, r.message);
  const S20m = await page(b, 'tstud20', '/student/modules.php');
  check('…enrolled student sees the new module', (await S20m.content()).includes('QA Module ' + st));
  r = await call(A, 'admin_modules.php?action=delete', { form: { id: mid } }); check('Admin: Delete module', r.success && sql(`SELECT COUNT(*) FROM modules WHERE id=${mid}`) === '0', r.message);
  // award types
  const AW = await page(b, 'thead', '/admin/awards.php'); const tok = (await AW.content()).match(/AWA?_CSRF = "([a-f0-9]+)"/)[1];
  r = await call(AW, 'awards.php?action=type_save', { method: 'POST', headers: { 'X-CSRF-Token': tok, 'Content-Type': 'application/json' }, json: undefined }).catch(() => null);
  r = await AW.evaluate(async ([u, t]) => (await fetch(u, { method: 'POST', headers: { 'X-CSRF-Token': t, 'Content-Type': 'application/json' }, body: JSON.stringify({ name: 'QA Award', description: 'x', category: 'Academic Excellence', icon: 'medal', tone: 'navy', status: 'Active' }) })).json(), [B + '/api/awards.php?action=type_save', tok]);
  const typeId = sql(`SELECT id FROM award_types WHERE name='QA Award'`);
  check('Admin: Create award type (needs the primary-key migration)', r.success && typeId !== '', r.message);
  r = await AW.evaluate(async ([u, t, id]) => (await fetch(u, { method: 'POST', headers: { 'X-CSRF-Token': t, 'Content-Type': 'application/json' }, body: JSON.stringify({ id: +id, status: 'Inactive' }) })).json(), [B + '/api/awards.php?action=type_status', tok, typeId]);
  check('Admin: Deactivate award type', r.success && sql(`SELECT status FROM award_types WHERE id=${typeId}`) === 'Inactive', r.message);

  // ═══ TEACHER ═══
  const T = await page(b, 'tteach15', '/teacher/quizzes.php?course=37&section=Grade%209');
  r = await call(T, 'quizzes.php?action=create', { json: { course_id: 37, title: 'QA quiz ' + st, status: 'Draft', max_score: 2, description: 'QA rules', passing_score: 50, open_at: '2020-01-01 08:00', close_at: '2035-01-01 08:00', questions: [{ question_text: '2+2', question_type: 'multiple_choice', points: 1, choices: [{ choice_text: '4', is_correct: 1 }, { choice_text: '5', is_correct: 0 }] }, { question_text: 'Capital of PH', question_type: 'essay', points: 1, identification_answer: 'Manila' }] } });
  const qid = +sql(`SELECT id FROM quizzes WHERE title='QA quiz ${st}'`);
  check('Teacher: Create quiz (draft)', r.success && qid > 0 && sql(`SELECT CONCAT(status,'|',teacher_id) FROM quizzes WHERE id=${qid}`) === 'Draft|15', r.message);
  const S = await page(b, 'tstud20', '/student/quizzes.php');
  check('…draft is NOT visible to students', !(await S.content()).includes('QA quiz ' + st));
  r = await call(T, 'quizzes.php?action=update', { json: { id: qid, title: 'QA quiz ' + st, status: 'Published', max_score: 2, description: 'QA rules', passing_score: 50, open_at: '2020-01-01 08:00', close_at: '2035-01-01 08:00', } });
  check('Teacher: Publish quiz', r.success && sql(`SELECT status FROM quizzes WHERE id=${qid}`) === 'Published', r.message);
  r = await call(T, 'quizzes.php?action=update', { json: { id: qid, title: 'QA quiz ' + st, status: 'Published', max_score: 2, description: 'QA rules', passing_score: 50, open_at: '2026-10-10 10:00', close_at: '2026-10-09 10:00' } });
  check('Teacher: Quiz closing before opening is rejected', !r.success, r.message);
  r = await call(T, 'quizzes.php?action=update', { json: { id: qid, title: 'QA quiz ' + st, status: 'Published', max_score: '2.5', description: 'QA rules', passing_score: 50, open_at: '2020-01-01 08:00', close_at: '2035-01-01 08:00', } });
  check('Teacher: Decimal max score is rejected', !r.success, r.message);
  r = await call(T, 'quizzes.php?action=update', { json: { id: qid, title: 'QA quiz ' + st, status: 'Published', max_score: 2, description: '', passing_score: '', open_at: '', close_at: '' } });
  check('Teacher: Missing instructions / passing score / dates are rejected', !r.success, r.message);
  // student takes it
  r = await call(S, `student_quiz.php?action=start&id=${qid}`);
  check('Student: Start published quiz (answers hidden)', r.success && r.data.questions.length === 2 && !JSON.stringify(r.data).includes('is_correct'), r.message);
  const q1 = r.data.questions[0], q2 = r.data.questions[1];
  const right = q1.choices.find(c => c.choice_text === '4');
  r = await call(S, 'student_quiz.php?action=submit', { json: { attempt_id: r.data.attempt_id, answers: { [q1.id]: { choice_id: right.id }, [q2.id]: { answer_text: 'manila' } } } });
  check('Student: Submit quiz → graded on the server', r.success && sql(`SELECT score FROM quiz_attempts WHERE quiz_id=${qid} AND student_id=20`) !== 'NULL', r.message + ' score ' + sql(`SELECT score FROM quiz_attempts WHERE quiz_id=${qid} AND student_id=20`));
  r = await call(S, `student_quiz.php?action=start&id=${qid}`); check('Student: Cannot retake a submitted quiz', !r.success, r.message);
  const J = await page(b, 'tstud10', '/student/quizzes.php');
  r = await call(J, `student_quiz.php?action=start&id=${qid}`); check('Student from another subject cannot open the quiz', !r.success, r.message);
  r = await call(T, `quizzes.php?action=results&id=${qid}`); check('Teacher: Quiz results list the student', r.success && r.data.some(x => +x.student_id === 20));
  await call(T, 'quizzes.php?action=update', { json: { id: qid, title: 'QA quiz ' + st, status: 'Published', max_score: 2, description: 'QA rules', passing_score: 50, open_at: '2030-01-01 08:00', close_at: '2030-01-02 08:00' } });
  r = await call(J, `student_quiz.php?action=start&id=12`); // other quiz, not enrolled
  const S2 = await page(b, 'tstud10', '/student/quizzes.php');
  // window test with an enrolled student: Ella (24) has not taken it
  const E = await page(b, 'tstud24', '/student/quizzes.php');
  r = await call(E, `student_quiz.php?action=start&id=${qid}`); check('Student: Quiz not open yet is refused', !r.success && /not open/i.test(r.message), r.message);
  r = await call(T, `quizzes.php?action=delete&id=${qid}`, { method: 'DELETE' });
  check('Teacher: Delete quiz (attempts + responses + gradebook item removed)', r.success && sql(`SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id=${qid}`) === '0' && sql(`SELECT COUNT(*) FROM gradebook_items WHERE source_type='quiz' AND source_id=${qid}`) === '0');
  // file quiz + grading
  r = await call(T, 'quizzes.php?action=create', { form: { course_id: 37, title: 'QA file quiz ' + st, description: 'upload', time_limit: '', max_score: 10, passing_score: 50, open_at: '2020-01-01 08:00', close_at: '2035-01-01 08:00' }, file: pdf });
  const fq = +sql(`SELECT id FROM quizzes WHERE title='QA file quiz ${st}'`);
  check('Teacher: Create file quiz', r.success && fq > 0, r.message);
  r = await call(S, 'student_quiz.php?action=submit_file', { form: { quiz_id: fq }, file: pdf });
  const fqa = sql(`SELECT id FROM quiz_attempts WHERE quiz_id=${fq} AND student_id=20`);
  check('Student: Upload file quiz answer', r.success && fqa !== '', r.message);
  r = await call(T, 'quizzes.php?action=grade', { json: { grades: [{ attempt_id: +fqa, score: 8, remarks: 'ok' }] } });
  check('Teacher: Grade file quiz', r.success && sql(`SELECT score FROM quiz_attempts WHERE id=${fqa}`) === '8.00', r.message);
  r = await call(T, 'quizzes.php?action=grade', { json: { grades: [{ attempt_id: +fqa, score: 80 }] } });
  check('Teacher: Score above maximum is skipped', sql(`SELECT score FROM quiz_attempts WHERE id=${fqa}`) === '8.00', r.message);
  await call(T, `quizzes.php?action=delete&id=${fq}`, { method: 'DELETE' });
  // assignment edit/delete
  r = await call(T, 'assignments.php?action=create', { form: { course_id: 37, title: 'QA hw ' + st, description: 'x', max_score: 10, due_date: '' } });
  const hw = sql(`SELECT id FROM assignments WHERE title='QA hw ${st}'`);
  r = await call(T, 'assignments.php?action=update', { form: { id: hw, title: 'QA hw ' + st + ' edited', description: 'y', max_score: 12, due_date: '' } });
  check('Teacher: Edit assignment', r.success && sql(`SELECT CONCAT(title,'|',max_score) FROM assignments WHERE id=${hw}`) === `QA hw ${st} edited|12.00`, r.message);
  r = await call(T, 'assignments.php?action=update', { form: { id: hw, title: '', max_score: 12 } }); check('Teacher: Empty title rejected', !r.success, r.message);
  r = await call(T, 'assignments.php?action=update', { form: { id: hw, title: 'x', max_score: 0 } }); check('Teacher: Zero max score rejected', !r.success, r.message);
  r = await call(T, `assignments.php?action=delete&id=${hw}`, { method: 'DELETE' }); check('Teacher: Delete assignment', r.success && sql(`SELECT COUNT(*) FROM assignments WHERE id=${hw}`) === '0');
  // gradebook manual item
  r = await call(T, 'gradebook.php?action=add_item', { json: { course_id: 37, title: 'QA Recitation', max_score: 10 } });
  check('Teacher: Adding a gradebook item is refused (button removed)', !r.success, r.message);
  // an item added before the buttons were removed: typing scores must still work
  const gi = Number(sql(`INSERT INTO gradebook_items (course_id, teacher_id, source_type, source_id, title, max_score) VALUES (37, 15, 'manual', NULL, 'QA Recitation', 10); SELECT LAST_INSERT_ID();`));
  r = await call(T, 'gradebook.php?action=save_score', { json: { item_id: gi, student_id: 20, score: '9' } }); check('Teacher: Type a score', r.success, r.message);
  const Sg = await page(b, 'tstud20', '/student/grades.php');
  check('…student Grades shows the item as 9 / 10', (await Sg.locator('tr', { hasText: 'QA Recitation' }).innerText()).includes('9 / 10'));
  r = await call(T, 'gradebook.php?action=update_item', { json: { item_id: gi, title: 'QA Recitation 2', max_score: 20 } }); check('Teacher: Editing a gradebook item is refused (button removed)', !r.success, r.message);
  r = await call(T, 'gradebook.php?action=save_score', { json: { item_id: gi, student_id: 20, score: '' } }); check('Teacher: Clear a score', r.success, r.message);
  r = await call(T, 'gradebook.php?action=delete_item', { json: { item_id: gi } }); check('Teacher: Deleting a gradebook item is refused (button removed)', !r.success && sql(`SELECT COUNT(*) FROM gradebook_items WHERE id=${gi}`) === '1', r.message);
  sql(`DELETE FROM gradebook_items WHERE id=${gi}`);   // clean up the test item
  // announcements
  r = await call(T, 'announcements.php?action=create', { json: { course_id: null, title: 'school wide?', body: 'x' } }); check('Teacher: Cannot post school-wide', !r.success, r.message);
  r = await call(T, 'announcements.php?action=create', { json: { course_id: 4, title: 'QA GenMath ' + st, body: 'x' } });
  const gAnn = sql(`SELECT id FROM announcements WHERE title='QA GenMath ${st}'`);
  const Jer = await page(b, 'tstud10', '/student/announcements.php');
  check('Teacher announcement in a shared subject reaches only their own section', r.success && !(await Jer.content()).includes('QA GenMath ' + st));
  const T14 = await page(b, 'tteach14', '/teacher/announcements.php');
  r = await call(T14, `announcements.php?action=delete&id=${gAnn}`, { method: 'DELETE' }); check('Teacher cannot delete another teacher\'s announcement', !r.success, r.message);
  r = await call(T, `announcements.php?action=delete&id=${gAnn}`, { method: 'DELETE' }); check('Teacher: Delete own announcement', r.success, r.message);
  // awards
  const TA = await page(b, 'tteach15', '/teacher/awards.php'); const ttok = (await TA.content()).match(/AW_CSRF = "([a-f0-9]+)"/)[1];
  const aw = (a, body) => TA.evaluate(async ([u, t, b]) => (await fetch(u, { method: 'POST', headers: { 'X-CSRF-Token': t, 'Content-Type': 'application/json' }, body: JSON.stringify(b) })).json(), [B + '/api/awards.php?action=' + a, ttok, body]);
  r = await aw('give', { student_id: 20, course_id: 37, award_type_id: 1, reason: 'Great essay on haiku', request_token: 'qa' + st + 'abcdefghijklmn' });
  const awId = r.id; check('Teacher: Give award', r.success && awId > 0, r.message);
  r = await aw('give', { student_id: 10, course_id: 37, award_type_id: 1, reason: 'Not my student here', request_token: 'qb' + st + 'abcdefghijklmn' }); check('Teacher cannot award a student outside their class', !r.success, r.message);
  r = await aw('update', { id: awId, award_type_id: 2, reason: 'Great essay on tanka' }); check('Teacher: Edit award', r.success, r.message);
  const Sa = await page(b, 'tstud20', '/student/awards.php'); check('…student sees the award', (await Sa.content()).includes('Great essay on tanka'));
  r = await aw('revoke', { id: awId }); check('Teacher: Revoke award (kept, hidden from student)', r.success && sql(`SELECT status FROM student_awards WHERE id=${awId}`) === 'Revoked', r.message);
  // attendance history + export
  r = await call(T, 'attendance.php?action=history&course_id=37&section=Grade%209&week=' + new Date().toISOString().slice(0, 10)); check('Teacher: Attendance history', r.success !== false, r.message || '');
  r = await T.evaluate(async u => { const x = await fetch(u); return { s: x.status, t: (await x.text()).slice(0, 60) }; }, B + '/api/gradebook.php?action=export&course_id=37&section=Grade%209');
  check('Teacher: Export gradebook CSV', r.s === 200 && r.t.includes('Student'), r.t.replace(/\n/g, ' '));
  // profile
  r = await call(T, 'profile.php?action=update', { json: { first_name: 'Jose Fernando', last_name: 'Dinglasan', middle_name: '', email: 'dinglasan.363935@globalcity.sti.edu.ph', contact: '09170000000' } });
  check('Teacher: Update own profile', r.success && sql(`SELECT contact FROM users WHERE id=15`) === '09170000000', r.message);

  // ═══ STUDENT ═══
  const mod = sql(`SELECT id FROM modules WHERE course_id=37 ORDER BY module_number LIMIT 1`);
  r = await call(S, 'module_progress.php?action=open', { json: { module_id: +mod } }); check('Student: Open module (records Opened)', r.success && sql(`SELECT COUNT(*) FROM module_views WHERE student_id=20 AND module_id=${mod}`) === '1', r.message);
  r = await call(S, 'module_progress.php?action=mark', { json: { module_id: +mod, course_id: 999 } }); check('Student: Mark module done (subject taken from the module)', r.success && sql(`SELECT course_id FROM module_progress WHERE student_id=20 AND module_id=${mod}`) === '37', r.message);
  r = await call(S, 'module_progress.php?action=unmark', { json: { module_id: +mod } }); check('Student: Undo done', r.success, r.message);
  const otherMod = sql(`SELECT id FROM modules WHERE course_id=38 LIMIT 1`);
  r = await call(S, 'module_progress.php?action=mark', { json: { module_id: +otherMod } }); check('Student: Cannot mark a module of a subject they are not in', !r.success, r.message);
  r = await call(S, 'profile.php?action=update', { json: { first_name: 'Ana', last_name: 'Reyes', middle_name: '', email: 'demo.ana@example.com', contact: '0917' } }); check('Student: Update own profile', r.success, r.message);
  r = await call(S, 'student_attendance.php'); check('Student: Own attendance API', r.success !== false, r.message || '');
  r = await call(S, 'dashboard_progress.php'); check('Student: Dashboard progress API', r.success);

  console.log(`\n${pass} passed, ${fail} failed`);
  fs.writeFileSync('crud-results.txt', out.join('\n') + `\n${pass} passed, ${fail} failed\n`);
  await b.close();
})().catch(e => { console.error('CRASH', e); process.exit(1); });
