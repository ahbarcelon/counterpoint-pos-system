# Railway deployment

The application service uses a Railway MySQL service, not your laptop’s XAMPP database. The steps below also serve as a repeatable setup guide for a future project.

## Current status

The source is published at <https://github.com/ahbarcelon/counterpoint-pos-system>, and the live app is <https://counterpoint-pos-system-production.up.railway.app/>. On 2026-10-10 the existing `giving-comfort` Railway project was renamed `counterpoint-pos-system`. Its former Tasks application service was replaced by the POS service (`counterpoint-pos`), while its existing `MySQL` service and persistent database volume were retained. A separate database named `counterpoint_pos` holds the POS tables; the old Tasks tables were not deleted. The app service has a persistent volume at `/var/www/html/public/uploads`. The Docker build, five pre-deploy migrations, HTTPS login page, and first `admin` staff login succeeded. A production upload surviving redeploy and end-to-end production sales have not yet been verified. No payment-plan change was made.

1. Push the project to a new GitHub repository using [GITHUB_SETUP.md](GITHUB_SETUP.md). Confirm the Dockerfile, Composer files, migrations, and docs are present.

2. In Railway, create a new project if your account has capacity, or use an existing project only if you intend to replace its application service. Add a **MySQL** service and confirm its data volume/persistence is enabled according to the current Railway UI. Wait until the database is healthy. In this deployment, the existing `MySQL` service was reused and a separate `counterpoint_pos` database was created to leave the previous application's tables intact.

3. Add a service from your GitHub repository, select the `main` branch, and allow Railway to build from the root `Dockerfile`. The Dockerfile uses Apache with the document root at `/var/www/html/public`. At startup Apache binds to Railway's `PORT` variable on all interfaces.

4. Add application variables to the app service. Use Railway’s variable reference picker so the exact MySQL service name is used; the example below assumes that service is named `MySQL` and may need to be changed to the name shown in your project:

   ```text
   CI_ENVIRONMENT=production
   APP_BASE_URL=https://counterpoint-pos-system-production.up.railway.app/
   APP_TIMEZONE=Asia/Manila
   APP_FORCE_HTTPS=false
   PORT=80
   RAILWAY_RUN_UID=0
   DB_HOST=${{MySQL.MYSQLHOST}}
   DB_DATABASE=counterpoint_pos
   DB_USER=${{MySQL.MYSQLUSER}}
   DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
   DB_PORT=${{MySQL.MYSQLPORT}}
   DB_DRIVER=MySQLi
   ```

   The example above reflects this existing deployment, whose app domain is set explicitly in `APP_BASE_URL` and whose reused MySQL service has the separate `counterpoint_pos` database. For a fresh MySQL service, `DB_DATABASE=${{MySQL.MYSQLDATABASE}}` is appropriate instead. The application also accepts the dotted CodeIgniter names used in `.env.example`. Use Railway’s reference picker if the MySQL service has a different name. Do not copy secrets into GitHub. Railway terminates HTTPS before forwarding to Apache; leave `APP_FORCE_HTTPS=false` to avoid an HTTP-to-HTTPS redirect loop. `public/index.php` recognizes Railway's HTTPS forwarding header before CodeIgniter starts, allowing Secure session cookies. `RAILWAY_RUN_UID=0` lets the startup script set ownership on the mounted upload volume before Apache handles requests.

5. In the app service settings, set the pre-deploy command to:

   ```text
   php spark migrate --all
   ```

   Do not configure `SampleDataSeeder` as a deployment step. It is optional demo data and would duplicate rows. The pre-deploy command runs separately from the running service and does not receive its upload volume. The first production account was created once through the app service's Railway console; its password is not in the repository. To create another initial account in a fresh deployment, open `railway ssh` for the app service and run these commands inside its shell:

   ```bash
   bash
   read -r -s -p 'Initial staff password: ' POS_INITIAL_PASSWORD; echo
   export POS_INITIAL_PASSWORD
   php spark pos:create-staff
   unset POS_INITIAL_PASSWORD
   ```

   Run `railway ssh` from a local directory linked to this Railway project, or select the app service in the CLI first. `railway run`/`railway shell` execute locally and are not substitutes for an interactive shell inside the deployed container. Never add a default password to source code.

6. Generate a public domain in the app service’s Networking settings. Set `APP_BASE_URL` to the exact HTTPS domain plus a trailing slash after the domain is known. This must match the browser URL so sessions and CSRF checks work. The current domain is the live URL shown above.

7. Keep the Dockerfile's entrypoint and default `apache2-foreground` command. The entrypoint adjusts Apache's listening port to `$PORT` and Apache serves `public/` on `0.0.0.0`. The current service and public domain target both use port `80`; if you choose another `PORT`, set the Networking target to the same value. There is no separate `/public/index.php` route to configure.

8. Uploaded images are stored in `public/uploads`. A normal redeploy replaces an ephemeral container, so attach a Railway persistent volume to the app service at exactly `/var/www/html/public/uploads`. This mount is present on the current service. The startup script creates and grants Apache access to the `products` and `avatars` subfolders. A mounted volume hides the image's `.htaccess`; the Apache site configuration independently blocks executable files in uploads. Keep `RAILWAY_RUN_UID=0` if startup ownership changes require root. Image persistence is configured but remains unverified until an uploaded image survives a redeploy.

9. Inspect build logs, the migration/deploy logs, and runtime logs. Visit the generated HTTPS domain and verify login, CRUD, uploads, sale stock decrement, insufficient-stock rejection, and sales history.

## Troubleshooting

- Composer/dependency failure: confirm `composer.lock` is present, the Docker build has network access, and the required PHP extensions are installed in the Dockerfile.
- MySQL connection failure: confirm the variable references use the exact database service name and that the app service can reach the private database host.
- Migration failure: read the first SQL error, verify the database is ready, and run `php spark migrate:status` in a shell.
- Application failed to respond: confirm the configured target port matches `$PORT` (`80` in this deployment) and that the entrypoint has not been overridden. If Apache reports “More than one MPM loaded,” check that `docker/entrypoint.sh` disables `mpm_event`/`mpm_worker` and enables only `mpm_prefork`.
- Missing images or permission errors: check the mounted path, writable permissions, and whether the browser URL is the public HTTPS domain.
- Login/session/CSRF errors: set the exact HTTPS `APP_BASE_URL`, keep production Secure cookies, and avoid mixed HTTP/HTTPS URLs. Do not force origin-side HTTPS unless trusted-proxy handling is configured.
