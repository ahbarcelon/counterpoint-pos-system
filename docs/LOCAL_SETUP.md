# Local setup on Windows/XAMPP

Run PowerShell commands from the project directory, for example `C:\path\to\pos-app`.

1. Install XAMPP with PHP 8.2 or newer, Composer 2, and Git. Start Apache and MySQL from the XAMPP Control Panel. Check the tools:

   ```powershell
   php -v
   where.exe php
   composer --version
   git --version
   ```

   `where.exe php` should show the intended XAMPP installation, normally `C:\xampp\php\php.exe`. If another PHP appears first, fix the Windows PATH or call `C:\xampp\php\php.exe` explicitly.

2. In `C:\xampp\php\php.ini`, enable these extensions by removing a leading semicolon, then restart any terminal: `curl`, `fileinfo`, `gd`, `intl`, `mbstring`, `mysqli`, `openssl`, and `zip`. Confirm with `php -m`. GD is required for image processing and ZIP is required by Composer for efficient package downloads.

3. Extract the project or clone your own repository into a working directory. Do not place real `.env` files, uploads, or secrets in Git.

4. Install dependencies:

   ```powershell
   composer install
   ```

   The project includes a verified `composer.lock`; use `composer install` so all machines install the same versions.

5. Create a database. In phpMyAdmin, create a database named `pos_system` with `utf8mb4` collation. Or use the XAMPP MySQL client:

   ```powershell
   C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE pos_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

6. Create the environment file and edit it with your local values:

   ```powershell
   Copy-Item .env.example .env
   notepad .env
   ```

   Set `app.baseURL = 'http://localhost:8080/'`, `database.default.database = pos_system`, and your MySQL username/password. `.env` is ignored by Git.

7. Ensure these directories exist and are writable by the web server: `writable`, `public/uploads/products`, and `public/uploads/avatars`. The repository includes empty `.gitkeep` files, but runtime contents remain ignored.

8. Apply the schema, then create the first administrator:

   ```powershell
   php spark migrate --all
   ```

   Create the first account without typing its password into shell history. The first account receives administrator privileges:

   ```powershell
   $securePassword = Read-Host 'Initial staff password' -AsSecureString
   $env:POS_INITIAL_PASSWORD = [System.Net.NetworkCredential]::new('', $securePassword).Password
   php spark pos:create-staff
   Remove-Item Env:POS_INITIAL_PASSWORD
   ```

   Enter a password of at least 8 characters at the hidden prompt. The command then asks for a unique username and display name. The password is hashed in the database; clear the temporary environment variable even if account creation fails.

   Optionally add 20 PC-store products, 20 fictional customers, 20 non-administrator demo staff, and 20 sample sales:

   ```powershell
   php spark db:seed SampleDataSeeder
   ```

   This seeder is repeatable and does not reset the administrator password or duplicate its own sales. Demo staff have random, undisclosed passwords until an administrator sets one. Never run sample seeding automatically on each production deployment.

9. Start the development server:

   ```powershell
   php spark serve
   ```

   Open `http://localhost:8080/login` and sign in.

10. Test the navigation: add/edit/archive products, upload a product image, create/view/edit/delete a customer, create/view/edit/delete staff, upload an avatar, record a walk-in sale and a customer sale, try insufficient stock, and confirm sales history keeps the original total after a product price edit.

11. To run the automated tests, create a separate disposable MySQL/MariaDB database (for example `pos_system_test`) and set `DB_TEST_DATABASE`, `DB_TEST_HOST`, `DB_TEST_USER`, and `DB_TEST_PASSWORD` in the current PowerShell session. Then run `php vendor/bin/phpunit --testdox`. Never point these tests at your production database; the test suite creates and removes test rows.

## Troubleshooting

- “Class not found” or Composer ZIP errors: enable `zip`, confirm `composer install`, and make sure the terminal uses the intended XAMPP PHP.
- Database connection errors: confirm MySQL is running, the database exists, and `.env` uses `MySQLi`, the correct port, and the correct credentials. XAMPP’s local database is separate from Railway’s production database.
- Migration errors: inspect the first failing migration, confirm the database user can create foreign keys, and use `php spark migrate:status` before retrying.
- Image errors or missing images: enable GD and fileinfo, check directory write permissions, and inspect `writable/logs`.
- CSRF errors: use the application forms, keep the session cookie enabled, and ensure `app.baseURL` matches the URL in the browser.
- Login loop: confirm the session directory is writable and that the browser URL matches `app.baseURL`.
