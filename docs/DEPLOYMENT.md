# Production Deployment — Ubuntu / Nginx / PostgreSQL

## 0. Immutable dependency prerequisite
A production deployment should use committed `composer.lock` and `package-lock.json` files. The generation environment had no Composer registry/npm registry connectivity, so those lock files could not be resolved reliably here. On the first connected workstation/CI runner:

```bash
composer update
npm install
php artisan test
./vendor/bin/phpunit --configuration phpunit.postgresql.xml
./vendor/bin/pint --test
npm run typecheck
npm run lint
npm run build
```

Review the resolved versions and commit both lock files. **Do not perform the first production cutover until this validation succeeds.** Subsequent environments must use `composer install` and `npm ci` rather than updating dependencies during deployment.

## 1. Server prerequisites
Install Nginx, PostgreSQL, PHP-FPM 8.3+ and common Laravel extensions (`pgsql`, `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `intl`, `fileinfo`), Composer 2, and Node.js supported by Vite 8 when assets are built on-server. Use a dedicated non-root service account and a dedicated PostgreSQL role/database.

## 2. Initial release
```bash
git clone <repository-url> <APP_PATH>
cd <APP_PATH>
composer install --no-dev --optimize-autoloader
cp .env.example .env
# Populate the production .env before building assets.
php artisan key:generate
npm ci
npm run build
```

Prefer building assets in CI and deploying `public/build` if Node should not be installed on the application server.

## 3. Required production environment
At minimum provide:

- `APP_NAME`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://...`, `APP_KEY`.
- PostgreSQL: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
- Session/cache/queue: `SESSION_DRIVER`, `SESSION_SECURE_COOKIE=true`, `CACHE_STORE`, `QUEUE_CONNECTION=database` (initial deployment).
- Mail: `MAIL_MAILER`, host/port/username/password/encryption, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`.
- Storage: `PUBLIC_FILESYSTEM_DRIVER`, `PRIVATE_FILESYSTEM_DRIVER`; for S3/R2 also `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, region, public/private buckets, endpoint/path-style settings, and `PUBLIC_FILESYSTEM_URL`.
- Reverb server: `REVERB_SERVER_HOST=127.0.0.1`, `REVERB_SERVER_PORT=<internal-port>`.
- Reverb browser/app: `REVERB_APP_ID`, key/secret, public `REVERB_HOST`, `REVERB_PORT=443`, `REVERB_SCHEME=https`, exact `REVERB_ALLOWED_ORIGINS`, and matching `VITE_REVERB_*` variables.
- Site settings fallbacks: `SITE_CONTACT_EMAIL`, optional phone/social URLs.

Recommended HTTPS cookie settings:

```env
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

Never set `REVERB_ALLOWED_ORIGINS=*` in production. `VITE_*` values are compiled into the browser bundle, so change them **before** `npm run build`.

## 4. Database and framework caches
```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
# Only when PUBLIC_FILESYSTEM_DRIVER=local:
php artisan storage:link
```

`storage:link` exposes only the public artwork disk. Never link the private disk into `public/`.

Create the first administrator interactively:

```bash
php artisan app:create-admin
```

Do not run `DemoSeeder` in production.

## 5. Nginx application host
Use `<APP_PATH>/public` as the document root and the installed PHP-FPM socket. A minimal location pattern is:

```nginx
server {
    listen 443 ssl http2;
    server_name <APP_DOMAIN>;
    root <APP_PATH>/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_pass unix:<PHP_FPM_SOCKET>;
    }

    location ~ /\. { deny all; }
}
```

Redirect HTTP to HTTPS and use a trusted TLS certificate. Adjust headers/timeouts according to your infrastructure.

## 6. Reverb WebSocket proxy
A dedicated WebSocket hostname keeps proxying simple. Example:

```nginx
server {
    listen 443 ssl http2;
    server_name <WS_DOMAIN>;

    location / {
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
        proxy_pass http://127.0.0.1:<REVERB_SERVER_PORT>;
    }
}
```

Production browser configuration then uses `REVERB_HOST=<WS_DOMAIN>`, `REVERB_PORT=443`, `REVERB_SCHEME=https` and identical `VITE_REVERB_*` public values. Keep Reverb bound to localhost and restrict `REVERB_ALLOWED_ORIGINS` to the actual application origin(s).

## 7. Queue worker — Supervisor example
Review every placeholder before use:

```ini
[program:afp-queue]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php <APP_PATH>/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
directory=<APP_PATH>
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=<SERVICE_USER>
numprocs=2
redirect_stderr=true
stdout_logfile=<APP_PATH>/storage/logs/queue-worker.log
stopwaitsecs=3600
```

After a deployment:

```bash
php artisan queue:restart
```

## 8. Reverb supervision
```ini
[program:afp-reverb]
command=/usr/bin/php <APP_PATH>/artisan reverb:start --host=127.0.0.1 --port=<REVERB_SERVER_PORT>
directory=<APP_PATH>
autostart=true
autorestart=true
user=<SERVICE_USER>
redirect_stderr=true
stdout_logfile=<APP_PATH>/storage/logs/reverb.log
```

Restart this process after relevant application/config changes.

## 9. Scheduler
```cron
* * * * * cd <APP_PATH> && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

## 10. Deployment update sequence
With committed lock files:

```bash
git pull --ff-only
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
# restart the supervised Reverb process
```

If frontend assets are built in CI, omit Node/npm commands from the server and deploy the already-built assets. Release directories with atomic symlink switching are recommended as traffic grows.

## 11. Backups
Schedule PostgreSQL backups with `pg_dump`, encrypt them as appropriate, store copies off-server, define retention, and test restores into an isolated database. For S3-compatible uploaded media/documents, use the provider's versioning/backup/lifecycle capabilities where appropriate and test restoring both public and private objects.

## Production checklist
- [ ] Dependency lock files committed; full tests/typecheck/lint/build passed on the exact locked versions.
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, HTTPS enforced, canonical `APP_URL` correct.
- [ ] Secure cookies, production mail and least-privileged PostgreSQL credentials configured.
- [ ] PostgreSQL backup schedule exists and a restore has been tested.
- [ ] Queue workers, scheduler and Reverb are supervised and healthy.
- [ ] Public/private filesystems are mapped correctly; private storage is not web-accessible.
- [ ] WebSocket allowed origins contain only real application origins.
- [ ] Database migrated; config/routes/views cached; local public storage linked only when needed.
- [ ] Administrator created with `app:create-admin`; demo data/accounts absent.
- [ ] `/sitemap.xml`, `/robots.txt`, branded error pages and core workflows pass QA.
- [ ] DNS cutover happens only after `docs/QA_CHECKLIST.md` is completed.
