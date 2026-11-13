"""Build database/seed_full_modules_<batch>.sql from the full-lesson files in this folder.

Usage:  python3 build_full.py grade10
Each <batch>_*.py file defines MODULES = {course_code: [(title, description, html), (…)]}.
Module 1 and 2 of each subject are replaced, unless an admin already edited them.
"""
import glob, importlib.util, os, re, sys

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)  # so batch files can `from _lesson import L`
ALLOWED = {'p','br','h3','h4','strong','b','em','i','u','ul','ol','li','blockquote','table','thead','tbody','tr','th','td','details','summary'}
batch = sys.argv[1] if len(sys.argv) > 1 else 'grade10'


def parse_basic(path):
    """Simple lesson format (definitions + example + quick check) used for Grade 7-9.
    @ CODE [fil]            subject (fil = headings in Filipino)
    # Title | description   new module
    > intro paragraph       * extra paragraph
    = Term :: definition    ! Label :: example text
    ? Question :: answer
    """
    import html as H
    subjects, cur, mod = {}, None, None
    for ln, raw in enumerate(open(path, encoding='utf-8'), 1):
        line = raw.strip()
        if not line: continue
        tag, rest = line[0], line[1:].strip()
        if tag == '@':
            parts = rest.split(); cur = parts[0]
            subjects[cur] = {'fil': 'fil' in parts[1:], 'mods': []}
        elif tag == '#':
            title, desc = [x.strip() for x in rest.split('|', 1)]
            mod = {'title': title, 'desc': desc, 'intro': [], 'terms': [], 'examples': [], 'qa': []}
            subjects[cur]['mods'].append(mod)
        elif tag in '>*': mod['intro'].append(rest)
        elif tag in '=!?':
            a, b = [x.strip() for x in rest.split('::', 1)]
            mod[{'=': 'terms', '!': 'examples', '?': 'qa'}[tag]].append((a, b))
        else: sys.exit(f'{path}:{ln} unparsed: {line}')
    out = {}
    for code, s in subjects.items():
        L = (('Mahahalagang Kahulugan', 'Halimbawa', 'Pagsusulit', 'Ipakita ang sagot') if s['fil']
             else ('Key terms', 'Example', 'Quick check', 'Show answers'))
        e = H.escape
        items = []
        for m in s['mods']:
            h = ''.join(f'<p>{e(p)}</p>' for p in m['intro'])
            h += f'<h3>{L[0]}</h3><ul>' + ''.join(f'<li><strong>{e(a)}</strong> – {e(b)}</li>' for a, b in m['terms']) + '</ul>'
            h += f'<h3>{L[1]}</h3>' + ''.join(
                (f'<blockquote><p>{e(b)}</p></blockquote>' if a.lower() in ('example', 'halimbawa')
                 else f'<blockquote><p><strong>{e(a)}</strong></p><p>{e(b)}</p></blockquote>') for a, b in m['examples'])
            h += f'<h3>{L[2]}</h3><ol>' + ''.join(f'<li>{e(q)}</li>' for q, _ in m['qa']) + '</ol>'
            h += f'<details><summary>{L[3]}</summary><ol>' + ''.join(f'<li>{e(a)}</li>' for _, a in m['qa']) + '</ol></details>'
            items.append((m['title'], m['desc'], h))
        out[code] = items
    return out

modules = {}
for path in sorted(glob.glob(os.path.join(HERE, f'{batch}_*.py'))):
    spec = importlib.util.spec_from_file_location(os.path.basename(path)[:-3], path)
    mod = importlib.util.module_from_spec(spec); spec.loader.exec_module(mod)
    for code, items in mod.MODULES.items():
        assert code not in modules, f'duplicate {code}'
        modules[code] = items
for path in sorted(glob.glob(os.path.join(HERE, f'{batch}_*.txt'))):
    for code, items in parse_basic(path).items():
        assert code not in modules, f'duplicate {code}'
        modules[code] = items

errors = []
for code, items in modules.items():
    if len(items) != 2: errors.append(f'{code}: expected 2 modules, got {len(items)}')
    for n, (title, desc, html) in enumerate(items, 1):
        tags = {t.lower() for t in re.findall(r'</?([a-zA-Z0-9]+)', html)}
        bad = tags - ALLOWED
        if bad: errors.append(f'{code} m{n}: tags not allowed {bad}')
        if re.search(r'<[a-zA-Z0-9]+\s+[^>]*>', html): errors.append(f'{code} m{n}: attributes found')
        for t in ('table','tr','ul','ol','details','blockquote'):
            if html.count(f'<{t}>') != html.count(f'</{t}>'): errors.append(f'{code} m{n}: unbalanced <{t}>')
if errors: sys.exit('\n'.join(errors))

def q(s): return "'" + s.replace('\\', '\\\\').replace("'", "''") + "'"
def tidy(html): return re.sub(r'>\s+<', '><', html.strip())

out = ["-- ============================================================",
       f"--  Arandia College eLMS — Full lessons: {batch}",
       f"--  File: database/seed_full_modules_{batch}.sql",
       "--",
       "--  Import AFTER cleanup_keep_two_modules.sql. Safe to run more than once.",
       "--  Replaces Module 1 and 2 of each subject below with the full lesson.",
       "--  A module an admin has already edited (updated_by set) is left alone.",
       f"--  {len(modules)} subjects x 2 modules = {2*len(modules)} lessons.",
       "-- ============================================================", "SET NAMES utf8mb4;", ""]
words = 0
for code, items in modules.items():
    out.append(f"-- {code}")
    for n, (title, desc, html) in enumerate(items, 1):
        h = tidy(html); words += len(re.sub(r'<[^>]+>', ' ', h).split())
        out.append(f"UPDATE modules m JOIN courses c ON c.id = m.course_id\n"
                   f"SET m.title = {q(title)}, m.description = {q(desc)}, m.content = {q(h)}, m.published = 1\n"
                   f"WHERE c.course_code = {q(code)} AND m.module_number = {n} AND m.updated_by IS NULL;")
        out.append(f"INSERT INTO modules (course_id, module_number, title, description, content, published)\n"
                   f"SELECT c.id, {n}, {q(title)}, {q(desc)}, {q(h)}, 1 FROM courses c\n"
                   f"WHERE c.course_code = {q(code)} AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = {n});")
    out.append("")
dest = os.path.join(HERE, '..', '..', f'seed_full_modules_{batch}.sql')
open(dest, 'w', encoding='utf-8').write('\n'.join(out))
print(f'OK: {len(modules)} subjects, {2*len(modules)} modules, ~{words} words -> {os.path.normpath(dest)}')
