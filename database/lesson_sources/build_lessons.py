import glob, html, os, re, sys
HERE = os.path.dirname(os.path.abspath(__file__))
EXPECTED_HS = [f"HS{g}-{s}" for g in (7,8,9,10) for s in ("FIL","ENGLISH","MATH","SCI","AP","ESP","MAPEH","TLE")]
EXPECTED_SHS = ["SHS-GENMATH","SHS-STATS","SHS-EARTH","SHS-PHYSSCI","SHS-ORALCOM","SHS-RECOM","SHS-EMPTECH","SHS-APPECON",
 "SHS-CREATIVEW","SHS-PR","SHS-PE","SHS-WORKIMM","STEM-PRECAL","STEM-BIO1","STEM-CHEM1","STEM-BIO2","STEM-CHEM2",
 "STEM-PHYSICS","STEM-BASICCAL","ABM-BUSMATH","ABM-FABM1","ABM-FABM2","ABM-ORGMGT","ABM-MARKETING","ABM-BUSFIN",
 "ABM-BUSETHICS","HUMSS-INTRO","HUMSS-DISAS","HUMSS-PPG","HUMSS-CNF","HUMSS-TNCT","HUMSS-APPSOCSCI","HUMSS-CESC",
 "GAS-HUMANITIES","GAS-SOCIALSCI"]
KEEP = 2   # modules per subject that go into the seed (the source files keep all 5 outlines)
subjects = {}
cur = mod = None
for path in sorted(glob.glob(os.path.join(HERE, '*.txt'))):
    for ln, raw in enumerate(open(path, encoding='utf-8'), 1):
        line = raw.rstrip('\n').strip()
        if not line: continue
        if line.startswith('@ '):
            parts = line[2:].split()
            cur = parts[0]; assert cur not in subjects, cur
            subjects[cur] = {'fil': 'fil' in parts[1:], 'mods': []}
        elif re.match(r'^\d+ \| ', line):
            n, title, desc = [p.strip() for p in line.split(' | ', 2)]
            mod = {'n': int(n), 'title': title, 'desc': desc, 'points': [], 'act': None}
            subjects[cur]['mods'].append(mod)
        elif line.startswith('- '): mod['points'].append(line[2:])
        elif line.startswith('? '): mod['act'] = line[2:]
        else: sys.exit(f'{path}:{ln} unparsed: {line}')
errs = []
for code in EXPECTED_HS + EXPECTED_SHS:
    if code not in subjects: errs.append(f'missing {code}')
for code, s in subjects.items():
    if code not in EXPECTED_HS + EXPECTED_SHS: errs.append(f'unexpected {code}')
    if [m['n'] for m in s['mods']] != [1,2,3,4,5]: errs.append(f'{code} numbering {[m["n"] for m in s["mods"]]}')
    for m in s['mods']:
        if len(m['points']) < 3 or not m['act']: errs.append(f'{code} m{m["n"]} incomplete')
if errs: sys.exit('\n'.join(errs))

def q(s):  # MySQL string literal
    return "'" + s.replace('\\', '\\\\').replace("'", "''") + "'"
def body(m, fil):
    h_pts, h_act = ('Mahahalagang Punto', 'Subukan Mo') if fil else ('Key points', 'Try this')
    e = html.escape
    return (f"<p>{e(m['desc'])}</p><h3>{h_pts}</h3><ul>" + ''.join(f"<li>{e(p)}</li>" for p in m['points'])
            + f"</ul><h3>{h_act}</h3><p>{e(m['act'])}</p>")

out = ["-- ============================================================",
"--  Arandia College eLMS — Starter lessons (2 modules per subject)",
"--  File: database/seed_module_lessons.sql",
"--",
"--  Import AFTER migration_modules.sql, seed_hs_subjects.sql and",
"--  seed_shs_subjects.sql. Safe to run more than once: a slot that",
"--  already has a module is left alone.",
f"--  {len(subjects)} subjects x {KEEP} modules = {len(subjects) * KEEP} lessons.",
"-- ============================================================", "SET NAMES utf8mb4;", ""]
for code in EXPECTED_HS + EXPECTED_SHS:
    s = subjects[code]
    out.append(f"-- {code}")
    for m in s['mods'][:KEEP]:
        out.append("INSERT INTO modules (course_id, module_number, title, description, content, published)\n"
                   f"SELECT c.id, {m['n']}, {q(m['title'])}, {q(m['desc'])}, {q(body(m, s['fil']))}, 1 FROM courses c\n"
                   f"WHERE c.course_code = {q(code)} AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = {m['n']});")
    out.append("")
open(os.path.join(HERE, '..', 'seed_module_lessons.sql'), 'w', encoding='utf-8').write('\n'.join(out))
print(f"OK: {len(subjects)} subjects, {sum(len(s['mods']) for s in subjects.values())} modules")
