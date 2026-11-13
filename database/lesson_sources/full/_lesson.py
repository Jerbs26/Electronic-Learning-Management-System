"""Helper that turns structured lesson data into the safe HTML the eLMS reader shows.

L(intro, sections, table=None, qa=(), fil=False)
  intro    : str or list of paragraphs
  sections : list of (heading, lead_paragraph_or_None, [ (term, explanation) or plain text ], closing_paragraph_or_None)
  table    : (heading, [header, ...], [[cell, ...], ...]) or None
  qa       : list of (question, answer)
  fil      : True = Filipino labels
"""
import html as _h

def _e(s): return _h.escape(s, quote=False)

def L(intro, sections, table=None, qa=(), fil=False):
    out = []
    for p in ([intro] if isinstance(intro, str) else intro):
        out.append(f'<p>{_e(p)}</p>')
    for heading, lead, items, tail in sections:
        out.append(f'<h3>{_e(heading)}</h3>')
        if lead: out.append(f'<p>{_e(lead)}</p>')
        if items:
            lis = []
            for it in items:
                if isinstance(it, tuple): lis.append(f'<li><strong>{_e(it[0])}</strong>: {_e(it[1])}</li>')
                else: lis.append(f'<li>{_e(it)}</li>')
            out.append('<ul>' + ''.join(lis) + '</ul>')
        if tail: out.append(f'<p>{_e(tail)}</p>')
    if table:
        heading, head, rows = table
        out.append(f'<h3>{_e(heading)}</h3><table><thead><tr>' + ''.join(f'<th>{_e(c)}</th>' for c in head) + '</tr></thead><tbody>'
                   + ''.join('<tr>' + ''.join(f'<td>{_e(c)}</td>' for c in r) + '</tr>' for r in rows) + '</tbody></table>')
    if qa:
        qh, sh = ('Pagsusulit', 'Ipakita ang sagot') if fil else ('Quick check', 'Show answers')
        out.append(f'<h3>{qh}</h3><ol>' + ''.join(f'<li>{_e(q)}</li>' for q, _ in qa) + '</ol>')
        out.append(f'<details><summary>{sh}</summary><ol>' + ''.join(f'<li>{_e(a)}</li>' for _, a in qa) + '</ol></details>')
    return ''.join(out)
