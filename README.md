# CounterPoint — CodeIgniter 4 POS

CounterPoint is a small, conventional MVC point-of-sale application for the IT0049 Web System Technologies midterm laboratory. It supports staff authentication, product inventory, customers, staff management, one-product-per-sale recording, and sales history.

## Implementation approach

- CodeIgniter 4.7.5 with PHP 8.2+, Composer 2, MySQL/MariaDB, and MySQLi. The supplied guide uses 4.7.4; 4.7.5 is the current compatible security patch release and is captured by the genuine Composer lockfile.
- Required products, customers, users, and sales fields are preserved in ordered migrations. `products.archived_at` is an intentional addition so inventory can be retired without breaking history.
- Sale recording locks the selected product row, performs a conditional stock decrement, calculates money in integer cents, inserts the sale, and commits both changes together. A unique request token prevents a repeated form submission from charging the same sale twice.
- CSRF protection applies globally. Management routes are protected by the `auth` filter. Output is escaped with `esc()` and uploads are MIME/content checked, resized, re-encoded, and given generated names.
- Product deletion is implemented as archiving. Customers and staff with sales cannot be deleted. The final staff account and the currently signed-in staff account cannot be removed.
- A persisted administrator flag controls team management. Administrators can create and manage staff; ordinary staff can view and edit only their own profile. The final administrator cannot be deleted.
- Protected requests verify that the signed-in staff account still exists, so deletion invalidates any other active session for that account on its next request.

The completed implementation was reconciled against the supplied IT0049 DOCX rubric and the CodeIgniter/XAMPP/GitHub/Railway Markdown guide. The DOCX is the authority for the required schema, features, relationships, rubric, and submission links; the Markdown guide informed the Windows and deployment workflow.

## Quick start

See [docs/LOCAL_SETUP.md](docs/LOCAL_SETUP.md). The short version is:

```powershell
Copy-Item .env.example .env
composer install
php spark migrate --all
$securePassword = Read-Host 'Initial staff password' -AsSecureString
$env:POS_INITIAL_PASSWORD = [System.Net.NetworkCredential]::new('', $securePassword).Password
php spark pos:create-staff
Remove-Item Env:POS_INITIAL_PASSWORD
php spark db:seed SampleDataSeeder # optional: 20 PC-store rows in each sample category
php spark serve
```

Then open `http://localhost:8080/login`. Create the first administrator before loading samples. The sample staff receive independent random, undisclosed passwords; an administrator can set a password for any sample account that needs to sign in. Do not configure the seeder to run automatically on every production deployment.

## Deployment and submission

- Source repository: <https://github.com/ahbarcelon/counterpoint-pos-system>
- Live application: <https://counterpoint-pos-system-production.up.railway.app/>
- [GitHub setup](docs/GITHUB_SETUP.md) records the published repository and retains reusable update commands.
- [Railway deployment](docs/RAILWAY_DEPLOYMENT.md) documents the generated Dockerfile, database settings, and current deployment status.
- [Testing checklist](docs/TESTING_CHECKLIST.md) separates local checks from manual XAMPP/Railway checks.

Local verification on XAMPP PHP 8.2.12 and MariaDB 10.4.32 included Composer validation, a locked-dependency security advisory audit, PHP lint on all 92 project PHP files, route listing, all five migrations, the optional seeder, and six PHPUnit tests (28 assertions). A fresh HTTP smoke run passed protected routing, login, product/customer/staff creation, secure raster upload re-encoding, successful sale recording, duplicate submission protection, insufficient-stock rejection, sales history, protected customer deletion, product archiving, and allowed staff deletion. The test databases and temporary upload were removed after verification.

On 2026-10-10, the existing Railway project `giving-comfort` was renamed `counterpoint-pos-system` and its former Tasks application service was replaced with this POS. The existing MySQL service and its old data were retained; POS tables were migrated into a separate `counterpoint_pos` database. The Docker build, pre-deploy migrations, public login page, and first staff login were verified on the live HTTPS domain. A persistent volume is mounted for uploads, but image survival across a redeploy and end-to-end production CRUD/sale flows have not yet been checked. See [docs/RAILWAY_DEPLOYMENT.md](docs/RAILWAY_DEPLOYMENT.md) for the exact configuration. Never commit `.env`, credentials, generated uploads, or runtime data.
