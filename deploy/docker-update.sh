#!/usr/bin/env bash
# =============================================================================
# به‌روزرسانی سایت روی سرور داکری (پوشه‌ی /opt/aronsoft) از گیت‌هاب.
#
# یک بار کپی کنید (چون خود src در حین اجرا عوض می‌شود):
#     cp /opt/aronsoft/src/deploy/docker-update.sh /opt/aronsoft/update.sh
# و هر بار:
#     bash /opt/aronsoft/update.sh
#
# کارها: بکاپ دیتابیس -> دریافت آخرین main -> ساخت ایمیج -> راه‌اندازی دوباره -> migrate
# اگر ساخت ایمیج خطا بدهد، سایت فعلی دست‌نخورده می‌ماند (کانتینر قبلی هنوز در حال اجراست).
# =============================================================================
set -euo pipefail
KIT="${KIT:-/opt/aronsoft}"
BRANCH="${BRANCH:-main}"
cd "$KIT"

say() { printf '\n\033[36m==> %s\033[0m\n' "$1"; }
ts="$(date +%Y%m%d-%H%M%S)"
mkdir -p backups

say "1/5  بکاپ دیتابیس -> backups/db-$ts.sql.gz"
docker compose exec -T mysql sh -c 'exec mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines --no-tablespaces "$MYSQL_DATABASE"' \
  | gzip > "backups/db-$ts.sql.gz"
ls -lh "backups/db-$ts.sql.gz"
# فقط ۱۰ بکاپ آخر نگه داشته می‌شود
ls -1t backups/db-*.sql.gz | tail -n +11 | xargs -r rm -f

say "2/5  دریافت کد ($BRANCH)"
git -C src fetch --depth 1 origin "$BRANCH"
old="$(git -C src rev-parse --short HEAD)"
git -C src reset --hard "origin/$BRANCH"
new="$(git -C src rev-parse --short HEAD)"
echo "   $old -> $new"

say "3/5  ساخت ایمیج (چند دقیقه)"
docker compose build app

say "4/5  راه‌اندازی دوباره"
docker compose up -d app
sleep 5

say "5/5  migrate"
docker compose exec -T app su-exec www-data php artisan migrate --force
docker compose exec -T app su-exec www-data php artisan optimize >/dev/null 2>&1 || true

port="$(grep -E '^APP_PORT=' .env | cut -d= -f2)"
code="$(curl -s -o /dev/null -w '%{http_code}' "http://127.0.0.1:${port:-8082}/" || true)"
echo
echo "   صفحه‌ی اصلی: HTTP $code"
[ "$code" = "200" ] && printf '\033[32m   به‌روزرسانی تمام شد (%s).\033[0m\n' "$new" || printf '\033[31m   صفحه‌ی اصلی 200 نداد؛ لاگ: docker compose logs --tail=80 app\033[0m\n'
