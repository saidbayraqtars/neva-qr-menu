#!/usr/bin/env bash
#
# Neva-QR Menü — sunucu kurulumu (Ubuntu 24.04 LTS)
#
# BİR KEZ çalıştırılır. Sıfır sunucudan, uygulamanın çalışacağı hale getirir:
# nginx, PHP 8.3-FPM (kendi havuzunda), kuyruk işçisi, zamanlanmış işler,
# dosya izinleri ve güvenlik duvarı.
#
# Kullanım:
#   sudo bash deploy/kurulum.sh
#
# Sonrasında sırasıyla:
#   1) /etc/ssl/nevaqr/ altına Cloudflare Origin CA sertifikasını koyun
#   2) .env dosyasını doldurun
#   3) sudo bash deploy/guncelle.sh
#
set -euo pipefail

APP_DIR=${APP_DIR:-/var/www/nevaqr}
APP_USER=${APP_USER:-www-data}
PHP_VER=8.3
POOL=nevaqr
DOMAIN=${DOMAIN:-nevaqr.com}
REPO=${REPO:-}

log()  { printf '\n\033[1;33m▸ %s\033[0m\n' "$*"; }
ok()   { printf '  \033[0;32m✓\033[0m %s\n' "$*"; }
fail() { printf '\n\033[0;31m✗ %s\033[0m\n' "$*" >&2; exit 1; }

[[ $EUID -eq 0 ]] || fail "root olarak çalıştırın: sudo bash deploy/kurulum.sh"

# ---------------------------------------------------------------------------
log "Sistem paketleri"
# ---------------------------------------------------------------------------
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y -qq software-properties-common curl git unzip ufw fail2ban

# Ubuntu 24.04 deposunda PHP 8.3 var; yine de ondrej PPA'sı 8.4'e geçişi
# kolaylaştırır. 8.3 mevcutsa PPA eklemiyoruz — az bağımlılık, az sürpriz.
if ! apt-cache show "php${PHP_VER}-fpm" >/dev/null 2>&1; then
    add-apt-repository -y ppa:ondrej/php
    apt-get update -qq
fi

apt-get install -y -qq \
    nginx \
    "php${PHP_VER}-fpm" "php${PHP_VER}-cli" \
    "php${PHP_VER}-gd" "php${PHP_VER}-mbstring" "php${PHP_VER}-xml" \
    "php${PHP_VER}-curl" "php${PHP_VER}-zip" "php${PHP_VER}-intl" \
    "php${PHP_VER}-sqlite3" "php${PHP_VER}-bcmath" "php${PHP_VER}-opcache"
ok "nginx + PHP ${PHP_VER} kuruldu"

# Menü PDF'i headless Chrome ile üretiliyor.
if ! command -v chromium-browser >/dev/null 2>&1 && ! command -v chromium >/dev/null 2>&1; then
    apt-get install -y -qq chromium-browser || apt-get install -y -qq chromium || true
fi
command -v chromium >/dev/null 2>&1 && ok "chromium kuruldu (PDF üretimi)" \
    || echo "  ! chromium kurulamadı — PDF indirme çalışmaz, .env'de CHROME_BINARY verin"

# Node — sadece varlıkları derlemek için.
if ! command -v node >/dev/null 2>&1; then
    curl -fsSL https://deb.nodesource.com/setup_22.x | bash - >/dev/null
    apt-get install -y -qq nodejs
fi
ok "node $(node -v)"

if ! command -v composer >/dev/null 2>&1; then
    curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer --quiet
    rm -f /tmp/composer-setup.php
fi
ok "composer $(composer --version --no-ansi | cut -d' ' -f3)"

# ---------------------------------------------------------------------------
log "PHP ayarları"
# ---------------------------------------------------------------------------
# Uygulamaya özel FPM havuzu: ileride ikinci bir uygulama eklendiğinde
# birbirlerinin işçilerini tüketmezler.
cat > "/etc/php/${PHP_VER}/fpm/pool.d/${POOL}.conf" <<POOLCONF
[${POOL}]
user = ${APP_USER}
group = ${APP_USER}
listen = /run/php/php${PHP_VER}-fpm-${POOL}.sock
listen.owner = ${APP_USER}
listen.group = ${APP_USER}
listen.mode = 0660

; 2 GB RAM varsayımı: her worker ~60 MB. 8 worker ≈ 500 MB, PDF üretimi için
; yer bırakır. Sunucuyu büyütünce max_children'ı da büyütün.
pm = dynamic
pm.max_children = 8
pm.start_servers = 2
pm.min_spare_servers = 2
pm.max_spare_servers = 4
pm.max_requests = 500

php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 12M
php_admin_value[post_max_size] = 14M
php_admin_value[max_execution_time] = 90
php_admin_value[expose_php] = Off
POOLCONF

# OPcache — üretimde en büyük tek performans kazancı.
cat > "/etc/php/${PHP_VER}/fpm/conf.d/99-nevaqr.ini" <<'PHPINI'
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
; Üretimde dosya değişikliği kontrol edilmez; dağıtımda php-fpm reload edilir
; (bkz. deploy/guncelle.sh). Bunu 1 yapmayın, her istekte stat() maliyeti gelir.
opcache.validate_timestamps=0
opcache.save_comments=1

expose_php=Off
PHPINI

systemctl restart "php${PHP_VER}-fpm"
ok "PHP-FPM havuzu '${POOL}' + OPcache açık"

# ---------------------------------------------------------------------------
log "Uygulama dizini"
# ---------------------------------------------------------------------------
mkdir -p "$APP_DIR" /var/www/certbot /etc/ssl/nevaqr

if [[ -n "$REPO" && ! -d "$APP_DIR/.git" ]]; then
    git clone "$REPO" "$APP_DIR"
    ok "depo klonlandı"
elif [[ ! -f "$APP_DIR/artisan" ]]; then
    echo "  ! $APP_DIR boş. Kodu buraya kopyalayın (git clone ya da rsync), sonra deploy/guncelle.sh çalıştırın."
fi

chown -R "${APP_USER}:${APP_USER}" "$APP_DIR"
ok "$APP_DIR hazır"

# ---------------------------------------------------------------------------
log "nginx"
# ---------------------------------------------------------------------------
rm -f /etc/nginx/sites-enabled/default

if [[ -f "$APP_DIR/deploy/nginx/nevaqr.conf" ]]; then
    cp "$APP_DIR/deploy/nginx/nevaqr.conf" /etc/nginx/sites-available/nevaqr.conf
    ln -sf /etc/nginx/sites-available/nevaqr.conf /etc/nginx/sites-enabled/nevaqr.conf
    ok "sunucu bloğu kuruldu"
else
    echo "  ! deploy/nginx/nevaqr.conf bulunamadı — kod henüz yerinde değil, sonra tekrar çalıştırın."
fi

# Gerçek ziyaretçi IP'si: Cloudflare arkasındayken $remote_addr Cloudflare'in
# IP'si olur. Hız sınırları ve KVKK maskeleme buna bakıyor — düzeltilmeli.
cat > /etc/nginx/conf.d/cloudflare-realip.conf <<'REALIP'
# Cloudflare IP aralıkları. Güncel liste: https://www.cloudflare.com/ips/
# Ayda bir tazelemek yeterli; aralıklar nadiren değişir.
real_ip_header CF-Connecting-IP;
set_real_ip_from 173.245.48.0/20;
set_real_ip_from 103.21.244.0/22;
set_real_ip_from 103.22.200.0/22;
set_real_ip_from 103.31.4.0/22;
set_real_ip_from 141.101.64.0/18;
set_real_ip_from 108.162.192.0/18;
set_real_ip_from 190.93.240.0/20;
set_real_ip_from 188.114.96.0/20;
set_real_ip_from 197.234.240.0/22;
set_real_ip_from 198.41.128.0/17;
set_real_ip_from 162.158.0.0/15;
set_real_ip_from 104.16.0.0/13;
set_real_ip_from 104.24.0.0/14;
set_real_ip_from 172.64.0.0/13;
set_real_ip_from 131.0.72.0/22;
set_real_ip_from 2400:cb00::/32;
set_real_ip_from 2606:4700::/32;
set_real_ip_from 2803:f800::/32;
set_real_ip_from 2405:b500::/32;
set_real_ip_from 2405:8100::/32;
set_real_ip_from 2a06:98c0::/29;
set_real_ip_from 2c0f:f248::/32;
REALIP
ok "Cloudflare gerçek IP başlığı ayarlandı"

# Sertifika yoksa nginx başlamaz; geçici kendinden imzalı koy ki kurulum
# ilerlesin. Gerçek sertifikayı koyduğunuzda üzerine yazın.
if [[ ! -f /etc/ssl/nevaqr/origin.pem ]]; then
    openssl req -x509 -nodes -newkey rsa:2048 -days 30 \
        -keyout /etc/ssl/nevaqr/origin.key \
        -out /etc/ssl/nevaqr/origin.pem \
        -subj "/CN=${DOMAIN}" -addext "subjectAltName=DNS:${DOMAIN},DNS:*.${DOMAIN}" 2>/dev/null
    chmod 600 /etc/ssl/nevaqr/origin.key
    echo "  ! GEÇİCİ kendinden imzalı sertifika kuruldu."
    echo "    Cloudflare › SSL/TLS › Origin Server › Create Certificate ile"
    echo "    ${DOMAIN} ve *.${DOMAIN} için sertifika üretip şu dosyaların üzerine yazın:"
    echo "      /etc/ssl/nevaqr/origin.pem   (certificate)"
    echo "      /etc/ssl/nevaqr/origin.key   (private key)"
fi

nginx -t && systemctl reload nginx
ok "nginx çalışıyor"

# ---------------------------------------------------------------------------
log "Kuyruk işçisi"
# ---------------------------------------------------------------------------
# Yayına alma (DNS + doğrulama) ve tüm e-postalar kuyrukta çalışır.
# QUEUE_CONNECTION=sync kalırsa admin "Onayla"ya bastığında tarayıcı bekler.
cat > /etc/systemd/system/nevaqr-queue.service <<QUEUE
[Unit]
Description=Neva-QR kuyruk isçisi
After=network.target

[Service]
User=${APP_USER}
Group=${APP_USER}
Restart=always
RestartSec=5
WorkingDirectory=${APP_DIR}
ExecStart=/usr/bin/php artisan queue:work --tries=3 --max-time=3600 --sleep=3 --backoff=10

# 2 GB'lık makinede tek bir kaçak iş sunucuyu yere sermesin.
MemoryMax=384M

[Install]
WantedBy=multi-user.target
QUEUE

systemctl daemon-reload
systemctl enable nevaqr-queue >/dev/null 2>&1
ok "nevaqr-queue servisi tanımlandı (kod yerleşince başlatın)"

# ---------------------------------------------------------------------------
log "Zamanlanmış işler"
# ---------------------------------------------------------------------------
# TEK cron satırı yeterli — gerisini Laravel'in kendi zamanlayıcısı yönetir
# (bkz. routes/console.php).
cat > /etc/cron.d/nevaqr <<CRON
SHELL=/bin/bash
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
* * * * * ${APP_USER} cd ${APP_DIR} && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
CRON
chmod 644 /etc/cron.d/nevaqr
ok "dakikalık schedule:run kuruldu"

# ---------------------------------------------------------------------------
log "Güvenlik duvarı"
# ---------------------------------------------------------------------------
ufw allow OpenSSH >/dev/null
ufw allow 'Nginx Full' >/dev/null
ufw --force enable >/dev/null
systemctl enable --now fail2ban >/dev/null 2>&1 || true
ok "ufw açık (22, 80, 443) · fail2ban çalışıyor"

# ---------------------------------------------------------------------------
log "Kurulum bitti"
# ---------------------------------------------------------------------------
cat <<SONRAKI

  Sıradaki adımlar:

  1) Cloudflare Origin CA sertifikasını yerleştirin:
       /etc/ssl/nevaqr/origin.pem
       /etc/ssl/nevaqr/origin.key
     Cloudflare'de DNS: A  @  -> sunucu IP  (proxy AÇIK)
                        A  *  -> sunucu IP  (proxy AÇIK)
     SSL/TLS modu: Full (strict)

  2) Kodu ${APP_DIR} içine alın ve .env dosyasını doldurun:
       cp .env.example .env && nano .env
     (APP_URL=https://${DOMAIN} · NEVA_ROOT_DOMAIN=${DOMAIN} · QUEUE_CONNECTION=database · SMTP)

  3) İlk dağıtım:
       sudo bash ${APP_DIR}/deploy/guncelle.sh --ilk

  4) Doğrulama:
       php artisan neva:onkontrol

SONRAKI
