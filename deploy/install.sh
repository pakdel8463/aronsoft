#!/usr/bin/env bash
# =============================================================================
# Aron Soft — shared-hosting installer / updater (idempotent, safe to re-run)
#
# Usage (from the project root, the folder that contains `app/` and `public_html/`):
#     bash deploy/install.sh                 # interactive install or update
#     bash deploy/install.sh --yes           # non-interactive (uses existing .env)
#     bash deploy/install.sh --no-composer   # vendor/ already uploaded
#     bash deploy/install.sh --admin you@mail.com   # also create/promote a superuser
#
# What it does: PHP/extension check -> composer install --no-dev -> .env (asks, never
# overwrites) -> APP_KEY -> DB backup -> migrate --force -> storage dirs + public link
# -> sync public/ -> public_html/ -> caches -> sitemap -> health check -> cron hint.
# It never prints secrets and never touches an existing .env except the backup copy.
# =============================================================================
set -uo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT" || exit 1

YES=0; NO_COMPOSER=0; ADMIN_EMAIL=""
while [ $# -gt 0 ]; do
  case "$1" in
    --yes|-y) YES=1 ;;
    --no-composer) NO_COMPOSER=1 ;;
    --admin) shift; ADMIN_EMAIL="${1:-}" ;;
    -h|--help) sed -n '2,16p' "$0"; exit 0 ;;
  esac; shift
done

c() { printf '\033[%sm%s\033[0m\n' "$1" "$2"; }
ok() { c 32 "  [ok]   $1"; }
warn() { c 33 "  [warn] $1"; }
fail() { c 31 "  [FAIL] $1"; exit 1; }
step() { echo; c 36 "==> $1"; }
ask() { # ask "Prompt" "default" -> echoes value
  local p="$1" d="${2:-}" v
  if [ "$YES" = 1 ]; then echo "$d"; return; fi
  read -r -p "$p${d:+ [$d]}: " v; echo "${v:-$d}"
}

# ---------------------------------------------------------------- 1. PHP
step "1/10  PHP & extensions"
PHP="${PHP_BIN:-php}"
command -v "$PHP" >/dev/null 2>&1 || fail "php not found in PATH. Set PHP_BIN=/opt/cpanel/ea-php83/root/usr/bin/php (cPanel) and re-run."
PV="$($PHP -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
$PHP -r 'exit(version_compare(PHP_VERSION,"8.2.0",">=")?0:1);' || fail "PHP $PV is too old; Laravel 12 needs >= 8.2 (switch PHP version in cPanel > MultiPHP Manager)."
ok "PHP $PV ($($PHP -r 'echo PHP_BINARY;'))"
MISSING=""
for e in ctype curl dom fileinfo filter hash mbstring openssl pcre pdo session tokenizer xml json; do
  $PHP -m | grep -qix "$e" || MISSING="$MISSING $e"
done
$PHP -m | grep -qiE '^(pdo_mysql|pdo_sqlite|pdo_pgsql)$' || MISSING="$MISSING pdo_mysql"
$PHP -m | grep -qiE '^(gd|imagick)$' || warn "gd/imagick missing — image thumbnails/webp will not be generated"
$PHP -m | grep -qix "zip" || warn "zip extension missing — some features (exports) may be limited"
[ -z "$MISSING" ] || fail "Missing PHP extensions:$MISSING (enable them in cPanel > Select PHP Version)."
ok "required extensions present"

# ---------------------------------------------------------------- 2. composer
step "2/10  Composer dependencies"
if [ "$NO_COMPOSER" = 1 ] || { [ -f vendor/autoload.php ] && [ "$YES" = 1 ]; }; then
  [ -f vendor/autoload.php ] || fail "vendor/autoload.php missing. Upload vendor/ or run without --no-composer."
  ok "using existing vendor/"
else
  COMPOSER=""
  if command -v composer >/dev/null 2>&1; then COMPOSER="composer"
  elif [ -f composer.phar ]; then COMPOSER="$PHP composer.phar"
  elif [ -f "$HOME/composer.phar" ]; then COMPOSER="$PHP $HOME/composer.phar"; fi
  if [ -z "$COMPOSER" ]; then
    if [ -f vendor/autoload.php ]; then warn "composer not found; keeping existing vendor/"
    else fail "composer not found and vendor/ missing. Install composer (https://getcomposer.org/download/) or upload vendor/ built with 'composer install --no-dev'."; fi
  else
    COMPOSER_ALLOW_SUPERUSER=1 $COMPOSER install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
      || fail "composer install failed (check memory_limit >= 512M; note: package aronlabs/captcha is a private dev-main package — it must be reachable or already in vendor/)."
    ok "composer install done"
  fi
fi

# ---------------------------------------------------------------- 3. .env
step "3/10  Environment file"
if [ -f .env ]; then
  cp .env ".env.bak.$(date +%Y%m%d-%H%M%S)" && ok ".env exists — kept as is (backup copy made)"
else
  [ -f .env.example ] || fail ".env.example missing"
  cp .env.example .env
  URL="$(ask 'Site URL' 'https://aron-soft.com')"
  DBH="$(ask 'DB host' 'localhost')"; DBN="$(ask 'DB name' '')"; DBU="$(ask 'DB user' '')"
  if [ "$YES" = 1 ]; then DBP=""; else read -r -s -p "DB password: " DBP; echo; fi
  MAILH="$(ask 'SMTP host' '')"; MAILU="$(ask 'SMTP user' '')"
  if [ "$YES" = 1 ]; then MAILP=""; else read -r -s -p "SMTP password: " MAILP; echo; fi
  MAILF="$(ask 'Mail from address' "info@${URL#*://}")"
  ADMN="$(ask 'Admin notification email' "$MAILF")"
  setenv() { # key value  (portable, no sed delimiter issues)
    $PHP -r '
      [$f,$k,$v]=[$argv[1],$argv[2],$argv[3]];
      $t=file_get_contents($f); $line=$k."=\"".addcslashes($v,"\"\\\\")."\"";
      if(preg_match("/^".preg_quote($k,"/")."=.*$/m",$t)){ $t=preg_replace("/^".preg_quote($k,"/")."=.*$/m",str_replace("\\\\","\\\\\\\\",$line),$t,1);} else {$t.="\n".$line;}
      file_put_contents($f,$t);' .env "$1" "$2"
  }
  setenv APP_ENV production; setenv APP_DEBUG false; setenv APP_URL "$URL"
  setenv DB_CONNECTION mysql; setenv DB_HOST "$DBH"; setenv DB_PORT 3306
  setenv DB_DATABASE "$DBN"; setenv DB_USERNAME "$DBU"; setenv DB_PASSWORD "$DBP"
  [ -z "$MAILH" ] || { setenv MAIL_MAILER smtp; setenv MAIL_HOST "$MAILH"; setenv MAIL_USERNAME "$MAILU"; setenv MAIL_PASSWORD "$MAILP"; }
  setenv MAIL_FROM_ADDRESS "$MAILF"; setenv ADMIN_NOTIFY_EMAIL "$ADMN"
  setenv SESSION_DRIVER file; setenv CACHE_STORE file; setenv QUEUE_CONNECTION sync
  chmod 600 .env 2>/dev/null || true
  ok ".env created (permissions 600)"
fi
$PHP artisan key:generate --force --no-interaction >/dev/null 2>&1 || true
grep -q '^APP_KEY=base64:' .env && ok "APP_KEY set" || fail "APP_KEY missing — run: $PHP artisan key:generate"
grep -qE '^APP_DEBUG=true' .env && warn "APP_DEBUG=true in .env — set it to false in production!"

# ---------------------------------------------------------------- 4. dirs
step "4/10  Writable directories"
mkdir -p storage/app/public storage/app/private storage/framework/{cache/data,sessions,views,testing} storage/logs storage/backups bootstrap/cache
chmod -R u+rwX,g+rX storage bootstrap/cache 2>/dev/null || true
[ -w storage ] && [ -w bootstrap/cache ] && ok "storage/ and bootstrap/cache are writable" || fail "storage/ or bootstrap/cache is not writable"

# ---------------------------------------------------------------- 5. DB
step "5/10  Database connection & backup"
$PHP artisan db:show --no-interaction >/dev/null 2>&1 || $PHP -r '
  require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
  try{Illuminate\Support\Facades\DB::connection()->getPdo();echo "ok";}catch(Throwable $e){fwrite(STDERR,$e->getMessage()."\n");exit(1);}' >/dev/null \
  || fail "Cannot connect to the database. Check DB_* in .env."
ok "database reachable"
DBC="$(grep -E '^DB_CONNECTION=' .env | cut -d= -f2- | tr -d '"')"
if [ "$DBC" = "mysql" ] && command -v mysqldump >/dev/null 2>&1; then
  g() { grep -E "^$1=" .env | head -1 | cut -d= -f2- | sed 's/^"//;s/"$//'; }
  BK="storage/backups/db-$(date +%Y%m%d-%H%M%S).sql"
  MYSQL_PWD="$(g DB_PASSWORD)" mysqldump -h "$(g DB_HOST)" -u "$(g DB_USERNAME)" --single-transaction --no-tablespaces "$(g DB_DATABASE)" > "$BK" 2>/dev/null \
    && { chmod 600 "$BK"; ok "DB backup: $BK"; } || { rm -f "$BK"; warn "mysqldump failed — take a backup from phpMyAdmin before continuing"; [ "$YES" = 1 ] || { read -r -p "Continue without backup? [y/N] " a; [ "$a" = y ] || exit 1; }; }
else
  warn "mysqldump not available — take a manual DB backup (phpMyAdmin > Export) before migrating"
  [ "$YES" = 1 ] || { read -r -p "Backup done? Continue? [y/N] " a; [ "$a" = y ] || exit 1; }
fi

# ---------------------------------------------------------------- 6. migrate
step "6/10  Migrations"
$PHP artisan config:clear >/dev/null 2>&1; $PHP artisan cache:clear >/dev/null 2>&1 || true
$PHP artisan migrate --force --no-interaction || fail "migration failed (nothing else was changed; restore backup if needed)"
ok "migrations applied"

# ---------------------------------------------------------------- 7. public
step "7/10  Public files (public/ -> public_html/) and storage link"
if [ -d public_html ]; then
  PUB=public_html
  # copy everything except .htaccess/index.php/error_log (keep host-specific ones)
  ( cd public && find . -type f ! -name '.htaccess' ! -name 'error_log' ! -path './storage/*' ! -path './hot' -print0 ) | while IFS= read -r -d '' f; do
     mkdir -p "public_html/$(dirname "$f")"; cp -p "public/$f" "public_html/$f"
  done
  [ -f public_html/.htaccess ] || cp public/.htaccess public_html/.htaccess 2>/dev/null || true
  ok "synced public/ into public_html/ (existing .htaccess kept)"
else
  PUB=public; warn "no public_html/ — using public/ as the web root"
fi
# storage link (symlink may be disabled on shared hosting)
if [ ! -e "$PUB/storage" ]; then
  ln -s "$ROOT/storage/app/public" "$PUB/storage" 2>/dev/null && ok "symlink $PUB/storage created" || {
    mkdir -p "$PUB/storage"; warn "symlink not allowed: created real folder $PUB/storage (uploads will be written/served from here; set FILESYSTEM_DISK/MEDIA_DISK accordingly)"; }
else ok "$PUB/storage already exists"; fi
mkdir -p "$PUB/uploads"; chmod -R u+rwX,g+rX "$PUB/uploads" 2>/dev/null || true
# index.php must use usePublicPath
grep -q 'usePublicPath' "$PUB/index.php" 2>/dev/null || warn "$PUB/index.php lacks \$app->usePublicPath(__DIR__) — copy public_html/index.php from the package"

# ---------------------------------------------------------------- 8. admin
step "8/10  Admin user"
if [ -n "$ADMIN_EMAIL" ]; then $PHP artisan aron:make-admin "$ADMIN_EMAIL" --no-interaction; 
elif [ "$YES" = 0 ]; then
  a="$(ask 'Create/promote a superuser now? enter email or leave empty to skip' '')"
  [ -z "$a" ] || $PHP artisan aron:make-admin "$a" --no-interaction
else ok "skipped (use --admin you@mail.com)"; fi

# ---------------------------------------------------------------- 9. caches
step "9/10  Optimize & sitemap"
$PHP artisan optimize:clear >/dev/null 2>&1 || true
$PHP artisan config:cache --no-interaction >/dev/null 2>&1 && ok "config cached" || warn "config:cache failed"
$PHP artisan route:cache --no-interaction >/dev/null 2>&1 && ok "routes cached" || warn "route:cache skipped (closure routes) — harmless"
$PHP artisan view:cache --no-interaction >/dev/null 2>&1 && ok "views cached" || warn "view:cache failed"
$PHP artisan event:cache --no-interaction >/dev/null 2>&1 || true
$PHP artisan sitemap:generate --no-interaction >/dev/null 2>&1 && ok "sitemap generated" || warn "sitemap:generate failed (run it later)"
[ -f "$PUB/sitemap.xml" ] || [ ! -f public/sitemap.xml ] || cp public/sitemap.xml "$PUB/sitemap.xml" 2>/dev/null || true

# ---------------------------------------------------------------- 10. check
step "10/10 Health check"
URL="$(grep -E '^APP_URL=' .env | head -1 | cut -d= -f2- | tr -d '"')"
if command -v curl >/dev/null 2>&1 && [ -n "$URL" ]; then
  code="$(curl -sk -o /dev/null -m 15 -w '%{http_code}' "$URL/up" 2>/dev/null || true)"; code="${code:-000}"
  [ "$code" = 200 ] && ok "GET $URL/up -> 200" || warn "GET $URL/up -> $code (check DNS/SSL/.htaccess, and storage/logs/laravel.log)"
fi
echo
c 32 "Done."
cat <<TXT

Next steps
  1) Cron (cPanel > Cron Jobs, every minute) — scheduler (daily sitemap, queue if enabled):
       * * * * * cd $ROOT && $PHP artisan schedule:run >> /dev/null 2>&1
  2) Open /panel and log in. First time only: Panel > دسترسی‌ها > "ساخت دسترسی‌های استاندارد".
  3) Test: contact form, /order wizard, newsletter e-mail, ticket e-mail (SMTP in .env).
  4) Rotate any secrets that were ever shared (DB password, APP_KEY if exposed, SMTP).
  5) Logs: storage/logs/laravel.log     Rollback: restore the DB backup in storage/backups/
TXT
