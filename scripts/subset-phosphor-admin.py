#!/usr/bin/env python3
"""
Builds public/vendor/phosphor/phosphor-admin.css — the Phosphor subset used by the admin panel only.
Scans admin views/components/shell/js (and every file under resources/views/admin) for icon names:
`ph-name`, icon="name", 'name' strings, ... — any quoted token that is a real Phosphor icon name is kept.
Fonts (Phosphor*.woff2) are shared with the front subset (same directory). Run after adding icons:
    python3 scripts/subset-phosphor-admin.py
"""
import re, pathlib
root = pathlib.Path(__file__).resolve().parent.parent
full = (root / 'resources/vendor/phosphor.full.css').read_text()
rule = re.compile(r'\.(ph|ph-bold|ph-fill)\.(ph-[a-z0-9-]+):before\{content:"[^"]+";\}')
names = {m.group(2)[3:] for m in rule.finditer(full)}
scan = ['resources/views/components/admin', 'resources/views/admin', 'resources/js/admin', 'public/js/admin', 'app/Http/Controllers/Admin', 'app/Forms', 'app/Pages', 'config']
used = set()
for d in scan:
    for f in (root / d).rglob('*'):
        if f.is_file() and f.suffix in {'.php', '.js', '.md'}:
            t = f.read_text(errors='ignore')
            used |= {x[3:] for x in re.findall(r'ph-[a-z0-9]+(?:-[a-z0-9]+)*', t)}
            for tok in re.findall(r'["\']([a-z][a-z0-9]*(?:-[a-z0-9]+)*)["\']', t):
                if tok in names: used.add(tok)
used |= {'caret-down', 'caret-left', 'caret-right', 'caret-up', 'check', 'x', 'list', 'warning', 'warning-circle', 'check-circle', 'info', 'trash', 'plus', 'pencil-simple', 'eye', 'magnifying-glass', 'dots-three', 'dots-three-vertical', 'arrow-left', 'arrow-right', 'floppy-disk', 'upload-simple', 'download-simple', 'image', 'link', 'copy', 'funnel', 'arrows-clockwise'}
out, kept, pos = [], 0, 0
for m in rule.finditer(full):
    out.append(full[pos:m.start()])
    if m.group(2)[3:] in used:
        out.append(m.group(0)); kept += 1
    pos = m.end()
out.append(full[pos:])
css = ''.join(out)
(root / 'public/vendor/phosphor/phosphor-admin.css').write_text(css)
print(f'kept {kept} icon rules, {len(css)} bytes')
