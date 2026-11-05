# EventKu lecturer demo on Railway

Laravel 10 / PHP 8.3 / Blade / Tailwind 3. Railway Trial is temporary and has usage limits; this deployment does not authorize upgrading to a paid plan.

## Runtime

- Connect `ivyxynn/Web-Event1`, branch `main`, using Railpack.
- `composer.json` targets PHP 8.3; `railpack.json` pins Node 22 and builds frontend assets without baking secrets into Laravel config caches. Runtime startup creates those caches.
- Attach one volume at `/data`, use only one replica.
- SQLite is `/data/database.sqlite`; sessions, cache and uploads are in `/data/storage`.
- Startup refuses to run if the data directory or application key is missing. Do not use a pre-deploy command for migrations: Railway volumes are mounted at runtime, not during the build or pre-deploy step.
- `start-container.sh` initializes folders, migrates without deleting data, seeds an empty demo database only when explicitly enabled, links uploads, and starts FrankenPHP plus the scheduler. It does **not** run the original `DatabaseSeeder`, which uses local demo passwords.
- Approved event posters are bundled in `resources/demo/posters`. New uploads are stored only on the volume. Local SQLite, transaction history, sessions, logs, payment proof and `.env` are not published.
- Generate an HTTPS Railway domain and use it as `APP_URL`; the proxy opt-in preserves secure URLs and cookies behind Railway.

## Variables

Set through Railway's private variables, not committed files:

```text
APP_NAME=EventKu
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR-DOMAIN.up.railway.app
APP_KEY=<fresh base64 application key; keep stable on redeploy>
TRUST_PROXY_HEADERS=true
PORT=8080
DB_CONNECTION=sqlite
DB_DATABASE=/data/database.sqlite
EVENTKU_DATA_PATH=/data
LARAVEL_STORAGE_PATH=/data/storage
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
LOG_LEVEL=warning
MAIL_MAILER=log
RAILPACK_PHP_VERSION=8.3
RAILPACK_PHP_EXTENSIONS=pdo_sqlite,sqlite3,gd,bcmath
RAILPACK_SKIP_MIGRATIONS=true
COMPOSER_NO_DEV=1
EVENTKU_DEMO_SEED=true
EVENTKU_DEMO_PASSWORD=<new random password, at least 12 characters>
```

The initial accounts are `admin@eventku.test`, `organizer@eventku.test` and `peserta@eventku.test`. Passwords are configured privately, never the local default. Existing accounts/events prevent automatic reseeding and password resets. Keep demo credentials private; this is a prototype, not a hardened multi-tenant production service.

## Sandbox payment

Do not reuse the sandbox key exposed earlier. Rotate it in the Midtrans sandbox dashboard, then set `MIDTRANS_SERVER_KEY` privately on Railway and configure the HTTPS notification URL `https://YOUR-DOMAIN.up.railway.app/payments/midtrans/notification` (see `MIDTRANS_SANDBOX.md`). No production endpoint or real-money payment.

Without a configured server key, paid checkout is explicitly disabled; free checkout still works. Never invent a QR or fall back to a manual account. Test outbound HTTPS, the official simulator, signed webhook notifications, repeated status checks and exactly-once ticket issuance before declaring public payment ready.

## Acceptance and backup

Verify HTTPS pages/assets, login/logout, role restrictions, organizer CRUD, free checkout, ticket ownership/QR/check-in, helpdesk, notifications and certificate verification. Check data and uploaded images survive restart/redeploy. Review logs without publishing secrets. Keep encrypted backups of the SQLite database and `/data/storage/app/public`; do not delete or detach the volume casually. Uploaded payment proofs are not served publicly by Caddy. Legacy proofs are viewed through the authenticated `GET /orders/{order}/bukti` route, restricted to the order owner or the event's organizer, with private/no-store response headers.

The scheduler reconciles QRIS every five minutes. Hosting credit exhaustion or process suspension can stop both web and reconciliation. Railway's free/trial allowance does not guarantee an always-on website; review remaining credit before the lecturer presentation.

## Known framework limits

Laravel 10 is outside its security support window. The October 2026 Composer audit reports Laravel advisories involving debug-page XSS, temporary signed URL handling and email-rule CRLF injection. This demo disables debug, uses the log mailer rather than sending email, and does not use temporary signed URLs for payment or certificate authorization. These mitigations are **not** a framework security upgrade. Do not use real personal/payment data; migrate to a supported Laravel release and rerun acceptance/security testing before production use. No framework upgrade has been applied implicitly.

Railway currently accepts `railway.json`, but reports that Config as Code support ends on 1 December 2026. Migrate to Railway Infrastructure as Code before that date if maintaining the deployment beyond the short lecturer demo.
