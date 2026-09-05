#!/usr/bin/env bash
#
# Neva-QR Menü — dağıtım
#
# Her kod güncellemesinde çalıştırılır. Sıra ÖNEMLİ: bakım moduna al, kodu
# çek, bağımlılıkları kur, varlıkları derle, migrate et, önbellekleri tazele,
# kuyruk işçisini yeniden başlat, bakım modundan çık, doğrula.
#
# Kullanım:
#   sudo bash deploy/guncelle.sh     # normal dağıtım
#   sudo bash deploy/guncelle.sh --ilk  # ilk kurulum (key:generate + seed)
#
set -euo pipefail

APP_DIR=${APP_DIR:-/var/www/nevaqr}
APP_USER=${APP_USER:-www-data}
PHP_VER=${PHP_VER:-8.3}
FIRST_RUN=0
[[ "${1:-}" == "--ilk" ]] && FIRST_RUN=1

log()  { printf '\n\033[1;33m▸ %s\033[0m\n' "$*"; }
ok()   { printf '  \033[0;32m✓\033[0m %s\n' "$*"; }
fail() { printf '\n\033[0;31m✗ %s\033[0m\n' "$*" >&2; exit 1; }

# systemctl reload ve chown root ister; sudo -u ile kullanıcı düşürmek de öyle.
[[ $EUID -eq 0 ]] || fail "root olarak çalıştırın: sudo bash deploy/guncelle.sh"

cd "$APP_DIR" || fail "$APP_DIR bulunamadı"
[[ -f artisan ]] || fail "$APP_DIR bir Laravel projesi değil"
[[ -f .env ]] || fail '.env yok. `cp .env.example .env` yapıp doldurun.'

# npm ve composer ev dizini ister; www-data'nın /var/www'si yazılabilir
# olmayabilir. Önbellekleri uygulama dizininin altına alıyoruz.
export HOME="$APP_DIR/.deploy-home"
export npm_config_cache="$HOME/.npm"
export COMPOSER_HOME="$HOME/.composer"
mkdir -p "$npm_config_cache" "$COMPOSER_HOME"
chown -R "${APP_USER}:${APP_USER}" "$HOME"

# php/composer/npm'i uygulama kullanıcısı olarak çalıştır: root ile
# çalıştırılırsa storage/ altında root'a ait dosyalar oluşur ve PHP-FPM
# onlara yazamaz — "permission denied" hatasının en sık sebebi budur.
# NOT: `sudo -u kullanici VAR=deger komut` çalışmaz — sudoers `env_reset`
# değişkenleri siler. `env` ile açıkça geçiriyoruz.
# GIT_SSH_COMMAND yalnızca tanımlıysa geçirilir (aşağıda, dağıtım anahtarı
# varsa ayarlanıyor). Boş bir değişken `env`'e "=" olarak gider ve hata verir.
as_app() {
    sudo -u "$APP_USER" env \
        HOME="$HOME" \
        npm_config_cache="$npm_config_cache" \
        COMPOSER_HOME="$COMPOSER_HOME" \
        ${GIT_SSH_COMMAND:+GIT_SSH_COMMAND="$GIT_SSH_COMMAND"} \
        "$@"
}

# git 2.35+ sahibi farklı olan depoda çalışmayı reddediyor ("dubious ownership").
# Dizin zaten www-data'ya ait; yine de açıkça güvenli işaretleyelim.
as_app git config --global --add safe.directory "$APP_DIR" 2>/dev/null || true

# Özel depo için dağıtım anahtarı.
#
# NEDEN /etc/nevaqr: git komutları uygulama kullanıcısı olarak çalışıyor.
# Anahtar /root/.ssh altında dursaydı www-data okuyamaz ve fetch
# "Host key verification failed" ile düşerdi. Anahtar salt-okunur bir
# GitHub deploy key; yazma yetkisi yok.
DEPLOY_KEY=/etc/nevaqr/github_deploy
if [[ -f "$DEPLOY_KEY" ]]; then
    export GIT_SSH_COMMAND="ssh -i $DEPLOY_KEY -o IdentitiesOnly=yes -o UserKnownHostsFile=/etc/nevaqr/known_hosts -o StrictHostKeyChecking=yes"
fi

# Deploy ev dizini depoya karismasin.
grep -qxF '/.deploy-home' .gitignore 2>/dev/null || echo '/.deploy-home' >> .gitignore

# ---------------------------------------------------------------------------
log "Bakım modu"
# ---------------------------------------------------------------------------
# --render: bakım sayfası da markalı (resources/views/errors/503.blade.php).
as_app php artisan down --render="errors::503" --retry=60 >/dev/null 2>&1 || true
trap 'as_app php artisan up >/dev/null 2>&1 || true' EXIT
ok "site bakım modunda"

# ---------------------------------------------------------------------------
log "Kod"
# ---------------------------------------------------------------------------
if [[ -d .git ]]; then
    as_app git fetch --quiet origin
    BRANCH=$(git rev-parse --abbrev-ref HEAD)
    as_app git reset --hard -q "origin/${BRANCH}"
    ok "$(git log -1 --pretty='%h %s')"
else
    echo "  ! git deposu değil — kod elle güncellenmiş varsayılıyor"
fi

# ---------------------------------------------------------------------------
log "Depo iskeleti"
# ---------------------------------------------------------------------------
# Git BOŞ DİZİN TAKİP ETMEZ ve bu yollar .gitignore'da. Taze bir klonda
# storage/framework altındaki dizinler hiç oluşmaz; Laravel de "Please provide
# a valid cache path" / "View path not found" diyerek 500 döner. Her dağıtımda
# garanti altına alıyoruz — var olanı bozmaz.
for d in \
    storage/app/public \
    storage/app/uploads \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/testing \
    storage/logs \
    bootstrap/cache
do
    mkdir -p "$d"
done
ok "storage/ ve bootstrap/cache dizinleri yerinde"

# ---------------------------------------------------------------------------
log "Bağımlılıklar"
# ---------------------------------------------------------------------------
as_app composer install --no-dev --optimize-autoloader --no-interaction --quiet
ok "composer"

# Node yalnızca derleme için gerekli; çıktısı public/build altına düşer.
if [[ -f package-lock.json ]]; then
    as_app npm ci --silent
    as_app npm run build --silent
    ok "varlıklar derlendi"
fi

# ---------------------------------------------------------------------------
if [[ $FIRST_RUN -eq 1 ]]; then
    log "İlk kurulum"
    grep -q '^APP_KEY=base64:' .env || { as_app php artisan key:generate --force; ok "APP_KEY üretildi"; }

    # SQLite kullanılıyorsa dosyayı oluştur.
    if grep -q '^DB_CONNECTION=sqlite' .env; then
        as_app mkdir -p database
        [[ -f database/database.sqlite ]] || as_app touch database/database.sqlite
        ok "SQLite dosyası hazır"
    fi
fi

# ---------------------------------------------------------------------------
log "Veritabanı"
# ---------------------------------------------------------------------------
as_app php artisan migrate --force
ok "migration"

if [[ $FIRST_RUN -eq 1 ]]; then
    as_app php artisan db:seed --class=PlanSeeder --force
    ok "paketler yüklendi"
fi

# ---------------------------------------------------------------------------
log "Önbellekler"
# ---------------------------------------------------------------------------
# optimize:clear ÖNCE: eski derlenmiş config, yeni .env değerlerini gölgeler.
as_app php artisan optimize:clear >/dev/null
as_app php artisan optimize >/dev/null
ok "config + route + view önbelleği"

# Menü HTML önbelleği menu_version ile anahtarlı; şablon dosyası değiştiyse
# sürüm artmaz ama HTML eskir. Dağıtımda temizlemek doğrusu.
as_app php artisan cache:clear >/dev/null
ok "menü önbelleği temizlendi"

# ---------------------------------------------------------------------------
log "İzinler"
# ---------------------------------------------------------------------------
chown -R "${APP_USER}:${APP_USER}" storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
ok "storage/ ve bootstrap/cache yazılabilir"

# ---------------------------------------------------------------------------
log "Servisler"
# ---------------------------------------------------------------------------
# OPcache validate_timestamps=0 olduğu için FPM reload ŞART; yoksa sunucu
# eski kodu çalıştırmaya devam eder ve "neden değişmedi" diye ararsınız.
systemctl reload "php${PHP_VER}-fpm"
ok "php-fpm yeniden yüklendi (OPcache tazelendi)"

# İşçi eski kodu bellekte tutar.
as_app php artisan queue:restart >/dev/null
systemctl restart nevaqr-queue 2>/dev/null || true
ok "kuyruk işçisi yeniden başlatıldı"

# ---------------------------------------------------------------------------
log "Yayında"
# ---------------------------------------------------------------------------
as_app php artisan up >/dev/null
trap - EXIT
ok "bakım modu kapatıldı"

# ---------------------------------------------------------------------------
log "Doğrulama"
# ---------------------------------------------------------------------------
# neva:onkontrol hatalı kontrol varsa 1 döner — dağıtımı düşürmüyoruz ama
# çıktı görünsün ki eksik ayar fark edilsin.
as_app php artisan neva:onkontrol || true

echo
curl -fsS -o /dev/null -w "  /up  → HTTP %{http_code}  (%{time_total}s)\n" \
    "$(grep -m1 '^APP_URL=' .env | cut -d= -f2- | tr -d '"')/up" \
    || echo "  ! /up yanıt vermedi — nginx/php-fpm loglarına bakın"
echo
