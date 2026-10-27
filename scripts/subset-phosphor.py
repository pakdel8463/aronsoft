#!/usr/bin/env python3
"""
public/vendor/phosphor/phosphor.css را فقط با آیکن‌هایی که در پروژه استفاده شده می‌سازد
(منبع کامل: resources/vendor/phosphor.full.css). بعد از افزودن آیکن جدید دوباره اجرا کنید:
    python3 scripts/subset-phosphor.py
"""
import re, pathlib
root = pathlib.Path(__file__).resolve().parent.parent
full = (root / 'resources/vendor/phosphor.full.css').read_text()
scan = ['resources', 'app', 'config', 'database/seeders', 'routes']
used = set()
for d in scan:
    for f in (root / d).rglob('*'):
        if f.is_file() and f.suffix in {'.php', '.js', '.css', '.html'} and 'phosphor.full.css' not in f.name:
            t = f.read_text(errors='ignore')
            used |= set(re.findall(r'ph-[a-z0-9]+(?:-[a-z0-9]+)*', t))
            # آیکن‌های پویا: 'icon' => 'user' یا ph-{{ $icon }}
            used |= {'ph-' + x for x in re.findall(r"['\"]icon['\"]\s*=>\s*['\"]([a-z0-9-]+)['\"]", t)}
# پیش‌ثبت‌شده‌ها که ممکن است از دیتابیس (آیکن منو) بیایند
used |= {'ph-caret-down', 'ph-caret-left', 'ph-caret-right', 'ph-check', 'ph-x', 'ph-list'}
rule = re.compile(r'\.(ph|ph-bold|ph-fill)\.(ph-[a-z0-9-]+):before\{content:"[^"]+";\}')
kept, out = 0, []
pos = 0
for m in rule.finditer(full):
    out.append(full[pos:m.start()])
    if m.group(2) in used:
        out.append(m.group(0)); kept += 1
    pos = m.end()
out.append(full[pos:])
css = ''.join(out)
(root / 'public/vendor/phosphor/phosphor.css').write_text(css)
print(f'kept {kept} icon rules, {len(css)} bytes (full {len(full)})')
