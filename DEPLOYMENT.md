# Deployment Guide — HRMS API (No Docker)

Deploy menggunakan pola **releases + symlink** seperti admin-v2.

---

## Struktur Folder di Server

```
/var/www/hrms.deltamastoyota.com/
├── current -> releases/2026_04_22_1430   ← symlink aktif (dipakai Nginx)
├── releases/
│   ├── 2026_04_22_1430/
│   ├── 2026_04_21_0900/
│   └── ...
└── shared/
    ├── .env                              ← satu .env untuk semua release
    ├── gcs-key.json                      ← GCS service account key
    ├── storage/                          ← storage persisten
    │   ├── app/public/
    │   ├── framework/{sessions,views,cache}
    │   └── logs/
    └── bootstrap/cache/
```

---

## 1. Setup Awal di Server (sekali saja)

```bash
# Buat struktur folder
sudo mkdir -p /var/www/hrms.deltamastoyota.com/{releases,shared}
sudo chown -R $USER:www-data /var/www/hrms.deltamastoyota.com
sudo chmod -R 775 /var/www/hrms.deltamastoyota.com

# Buat folder shared
mkdir -p /var/www/hrms.deltamastoyota.com/shared/storage/app/public
mkdir -p /var/www/hrms.deltamastoyota.com/shared/storage/framework/{sessions,views,cache/data}
mkdir -p /var/www/hrms.deltamastoyota.com/shared/storage/logs
mkdir -p /var/www/hrms.deltamastoyota.com/shared/bootstrap/cache
```

---

## 2. Buat .env di Server

```bash
nano /var/www/hrms.deltamastoyota.com/shared/.env
```

```env
APP_NAME="HRMS API"
APP_ENV=production
APP_KEY=base64:GENERATE_DULU_DENGAN_php_artisan_key:generate
APP_DEBUG=false
APP_URL=https://hrms.deltamastoyota.com

LOG_CHANNEL=daily
LOG_LEVEL=warning

# Cloud SQL (Private IP dari GCP Console)
DB_CONNECTION=mysql
DB_HOST=10.x.x.x
DB_PORT=3306
DB_DATABASE=hrms
DB_USERNAME=hrms_user
DB_PASSWORD=your_password

DB_LEGACY_CONNECTION=mysql
DB_LEGACY_HOST=10.x.x.x
DB_LEGACY_PORT=3306
DB_LEGACY_DATABASE=hrms_legacy
DB_LEGACY_USERNAME=hrms_user
DB_LEGACY_PASSWORD=your_password

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

FILESYSTEM_DISK=gcs

# GCS — key dicari otomatis di storage/app/gcs-key.json
GOOGLE_CLOUD_KEY_FILE=
GOOGLE_CLOUD_PROJECT_ID=ddms-366407
GOOGLE_CLOUD_STORAGE_BUCKET=deltamas-storage
GOOGLE_CLOUD_STORAGE_PATH_PREFIX=hrms

# Sentry error tracking
SENTRY_LARAVEL_DSN=
```

---

## 3. Upload GCS Key

```bash
# Dari local machine
scp gcs-key.json user@server-ip:/var/www/hrms.deltamastoyota.com/shared/gcs-key.json
chmod 640 /var/www/hrms.deltamastoyota.com/shared/gcs-key.json
```

---

## 4. Setup SSH Deploy Key

```bash
# Di server — buat deploy key khusus untuk clone repo
ssh-keygen -t ed25519 -C "hrms-deploy" -f ~/.ssh/hrms-deploy -N ""

# Tampilkan public key → tambahkan ke GitHub repo
# Settings → Deploy keys → Add deploy key (Read only)
cat ~/.ssh/hrms-deploy.pub
```

---

## 5. GitHub Secrets

Di GitHub repo → Settings → Secrets and variables → Actions:

| Secret | Value |
|--------|-------|
| `HRMS_PROD_SSH_KEY` | Private key untuk SSH dari GitHub Actions ke server |
| `HRMS_PROD_HOST` | IP publik GCP VM |
| `HRMS_PROD_USER` | Username SSH (`jovinkendrico`) |
| `HRMS_PROD_DOMAIN` | `hrms.deltamastoyota.com` |

### Generate SSH Key untuk GitHub Actions

```bash
# Di local
ssh-keygen -t ed25519 -C "github-actions-hrms" -f ~/.ssh/hrms_actions -N ""

# Copy public key ke server
ssh-copy-id -i ~/.ssh/hrms_actions.pub jovinkendrico@server-ip

# Isi HRMS_PROD_SSH_KEY dengan isi private key
cat ~/.ssh/hrms_actions
```

---

## 6. Nginx Config

```nginx
server {
    listen 80;
    server_name hrms.deltamastoyota.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name hrms.deltamastoyota.com;

    root /var/www/hrms.deltamastoyota.com/current/public;
    index index.php;

    # SSL — Cloudflare origin certificate
    ssl_certificate /etc/ssl/hrms.deltamastoyota.com.pem;
    ssl_certificate_key /etc/ssl/hrms.deltamastoyota.com.key;

    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
sudo nginx -t && sudo systemctl reload nginx
```

---

## 7. Queue Worker (Supervisor)

```bash
sudo nano /etc/supervisor/conf.d/hrms-worker.conf
```

```ini
[program:hrms-worker]
command=php /var/www/hrms.deltamastoyota.com/current/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=2
process_name=%(program_name)s_%(process_num)02d
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/hrms-worker.log
stdout_logfile_maxbytes=10MB
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hrms-worker:*
```

---

## 8. Cloudflare Setup

1. DNS → Add A Record:
   - Name: `hrms` (subdomain)
   - IPv4: IP publik GCP VM
   - Proxy: **ON** (orange cloud)

2. SSL/TLS → Mode: **Full (strict)**

3. Download **Cloudflare Origin Certificate** → upload ke server sebagai `hrms.deltamastoyota.com.pem` dan `.key`

---

## 9. Monitoring

### Sentry (Error Tracking)
```bash
cd /var/www/hrms.deltamastoyota.com/current
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=https://xxx@sentry.io/xxx
```
Tambah `SENTRY_LARAVEL_DSN` ke `shared/.env`

### BetterStack Uptime
- Monitor URL: `https://hrms.deltamastoyota.com/up`
- Interval: 3 menit

### GCP Cloud Monitoring
- Sudah aktif otomatis
- Setup alert: CPU > 80%, disk > 85%

---

## Alur CI/CD (Setelah Setup)

```
git push main
  → GitHub Actions SSH ke server
  → git clone ke releases/YYYY_MM_DD_HHMM
  → symlink .env, storage, gcs-key.json, bootstrap/cache
  → composer install
  → php artisan migrate --force
  → php artisan config:cache + route:cache + view:cache
  → ln -sfn releases/xxx current   ← switch traffic
  → queue:restart + reload php-fpm
  → cleanup releases lama (simpan 5 terakhir)
  → health check ke /up
```
