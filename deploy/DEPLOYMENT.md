# VPS deployment checklist

## Server baseline
- Ubuntu LTS, non-root deploy user, SSH key authentication.
- UFW: allow only SSH, HTTP, HTTPS.
- Install Nginx, MySQL 8, PHP 8.3-FPM and Laravel-required PHP extensions.
- Install Composer and Git.
- Configure unattended security updates and time synchronization.

## Release
```bash
cd /var/www/retail-wms
git pull --ff-only
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan down
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
php artisan up
```

Use a release-directory/symlink strategy for production rather than deploying directly into the live directory.
Keep `.env` outside source control, use least-privilege DB credentials, and ensure `storage/` and
`bootstrap/cache/` are writable by the PHP-FPM user.

## Nginx / PHP-FPM
- Set Nginx document root to the Laravel `public/` directory only.
- Use the correct PHP-FPM socket for the installed PHP version.
- Test configuration with `nginx -t`, then reload Nginx.
- Enable HTTPS with Certbot and redirect HTTP to HTTPS.
- Never expose `.env`, source files, or storage paths publicly.

## Queue and scheduler
- Run queue workers under Supervisor/systemd; restart workers after deployments:
  `php artisan queue:restart`
- Add Laravel scheduler cron:
  `* * * * * cd /var/www/retail-wms && php artisan schedule:run >> /dev/null 2>&1`

## Operational readiness
- Configure automated encrypted DB backups and test restoration.
- Centralize logs and monitor disk, CPU, memory, PHP-FPM, MySQL, and queue depth.
- Add health checks and an incident/rollback runbook.
- Keep `APP_DEBUG=false` in production.
