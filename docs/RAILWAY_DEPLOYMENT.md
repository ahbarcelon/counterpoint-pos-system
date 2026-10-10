# Manual Railway deployment

This document is for the student to execute. The application service must use a Railway MySQL service; it must never connect to your laptop’s XAMPP database.

1. Push the project to a new GitHub repository using [GITHUB_SETUP.md](GITHUB_SETUP.md). Confirm the Dockerfile, Composer files, migrations, and docs are present.

2. In Railway, create a new project. Add a **MySQL** service and confirm its data volume/persistence is enabled according to the current Railway UI. Wait until the database is healthy.

3. Add a service from your GitHub repository, select the `main` branch, and allow Railway to build from the root `Dockerfile`. The Dockerfile uses Apache with the document root at `/var/www/html/public`. At startup Apache binds to Railway's `PORT` variable (default `8080`) on all interfaces.

4. Add application variables to the app service. Use Railway’s variable reference picker so the exact MySQL service name is used; the example below assumes that service is named `MySQL` and may need to be changed to the name shown in your project:

   ```text
   CI_ENVIRONMENT=production
   APP_BASE_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}/
   APP_TIMEZONE=Asia/Manila
   APP_FORCE_HTTPS=false
   DB_HOST=${{MySQL.MYSQLHOST}}
   DB_DATABASE=${{MySQL.MYSQLDATABASE}}
   DB_USER=${{MySQL.MYSQLUSER}}
   DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
   DB_PORT=${{MySQL.MYSQLPORT}}
   DB_DRIVER=MySQLi
   ```

   The application also accepts the dotted CodeIgniter names used in `.env.example`; the uppercase names above are used in this copyable Railway example because they are conventional platform variable names. If Railway exposes a private host/port variable instead of the names above, choose the matching variables from the MySQL service. Do not copy secrets into GitHub. Railway terminates HTTPS before forwarding to Apache, so leave `APP_FORCE_HTTPS=false` to avoid an HTTP-to-HTTPS redirect loop; production cookies are marked Secure by the application.

5. In the app service settings, set the pre-deploy command to:

   ```text
   php spark migrate --all
   ```

   Do not configure `SampleDataSeeder` as a deployment step. It is optional demo data and would duplicate rows. The pre-deploy command runs separately from the running service and does not receive its upload volume. Create the first production account once, after the service is healthy, by opening `railway ssh` for the app service and running these commands inside its shell:

   ```bash
   bash
   read -r -s -p 'Initial staff password: ' POS_INITIAL_PASSWORD; echo
   export POS_INITIAL_PASSWORD
   php spark pos:create-staff
   unset POS_INITIAL_PASSWORD
   ```

   Run `railway ssh` from a local directory linked to this Railway project, or select the app service in the CLI first. `railway run`/`railway shell` execute locally and are not substitutes for an interactive shell inside the deployed container. Never add a default password to source code.

6. Generate a public domain in the app service’s Networking settings. Replace the `app.baseURL` value with the exact HTTPS domain plus a trailing slash after the domain is known. This must match the browser URL so sessions and CSRF checks work. Do not invent the final URL in a submission.

7. Keep the Dockerfile's entrypoint and default `apache2-foreground` command. The entrypoint adjusts Apache's listening port to `$PORT` and Apache serves `public/`. If the Railway Networking UI asks for a target port, use the value of `$PORT` (normally `8080`). There is no separate `/public/index.php` route to configure.

8. Uploaded images are stored in `public/uploads`. A normal redeploy replaces an ephemeral container, so attach a Railway persistent volume to the app service at exactly `/var/www/html/public/uploads`. The startup script creates and grants Apache access to the `products` and `avatars` subfolders, but this still needs a live deployment check. A mounted volume hides the image's `.htaccess`; the Apache site configuration independently blocks executable files in uploads. If the volume's permissions prevent startup, confirm the app runs as root for the startup ownership step (Railway's `RAILWAY_RUN_UID=0` setting may be needed). Do not claim persistence until an upload survives a redeploy.

9. Inspect build logs, the migration/deploy logs, and runtime logs. Visit the generated HTTPS domain and verify login, CRUD, uploads, sale stock decrement, insufficient-stock rejection, and sales history.

## Troubleshooting

- Composer/dependency failure: confirm `composer.lock` is present, the Docker build has network access, and the required PHP extensions are installed in the Dockerfile.
- MySQL connection failure: confirm the variable references use the exact database service name and that the app service can reach the private database host.
- Migration failure: read the first SQL error, verify the database is ready, and run `php spark migrate:status` in a shell.
- Application failed to respond: confirm the configured target port matches `$PORT` (default `8080`) and that the entrypoint has not been overridden.
- Missing images or permission errors: check the mounted path, writable permissions, and whether the browser URL is the public HTTPS domain.
- Login/session/CSRF errors: set the exact HTTPS `APP_BASE_URL`, keep production Secure cookies, and avoid mixed HTTP/HTTPS URLs. Do not force origin-side HTTPS unless trusted-proxy handling is configured.
