# Testing and rubric checklist

## Automated/local checks

The checks below were rerun on the completed project using XAMPP PHP 8.2.12 and MariaDB 10.4.32 on 2026-10-10.

- `php -l` on all 92 project PHP files: passed.
- `composer validate --strict`: passed and confirmed the Composer manifest and lockfile metadata.
- `composer audit --locked`: passed with no known dependency security advisories.
- `php spark routes`: listed all expected routes and filters.
- `php spark migrate --all`: all five migrations passed against isolated verification databases.
- `php spark db:seed SampleDataSeeder`: optional local demo data passed.
- `php vendor/bin/phpunit --testdox`: six tests and 28 assertions passed, including transactional rollback, duplicate submission protection, and concurrent sale contention.
- Fresh HTTP smoke check against `php spark serve`: passed unauthenticated redirect, login, dashboard access, product/customer/staff creation, image re-encoding, successful sale, duplicate submission protection, insufficient stock, sales history, protected customer deletion, product archiving, and unused staff deletion.
- A focused two-session check from the preceding continuation also confirmed that deleting a staff account invalidates its other active session on the next protected request.

## Manual functional checks

1. Authentication: unauthenticated requests to `/dashboard`, `/products`, `/customers`, `/staff`, and `/sales` redirect to `/login`; valid login regenerates the session; logout destroys it.
2. Products: create, edit, upload a valid image, reject oversized/non-image uploads, confirm nonnegative price and whole-number stock validation, archive a product, and confirm old sales remain visible.
3. Customers: create, view, edit, and delete a customer with no sales. Attempt to delete a customer referenced by a sale and confirm it is blocked.
4. Staff roles: as administrator, create and edit team accounts. As an ordinary staff user, confirm `/staff` leads to that user's profile, self-edit succeeds, and direct GET/POST access to another profile or staff creation/deletion returns HTTP 403. A forged `is_admin=1` form value must not grant administrator access. Confirm the final/current administrator cannot be removed. Also test avatar upload, blank password preserving the current password, duplicate usernames, and no password in HTML or list queries. Sign in as a second staff member, delete an unused account from another administrator session, and confirm its next protected request returns to login.
5. Sales: record a walk-in sale and a customer sale. Confirm the selected product’s database price is used, stock decreases, the total is stored, and a redirect prevents a refresh from repeating the POST.
6. Failure behavior: try a missing product/customer, zero or negative quantity, and quantity greater than stock. Confirm clear error messages and unchanged stock/sales rows.
7. Concurrency: with a product quantity of one, submit two requests at nearly the same time. Exactly one should succeed; the other should receive insufficient stock. This depends on a real MySQL/MariaDB connection and InnoDB.
8. Rollback: temporarily test with a database constraint or controlled failure after the stock update in a non-production environment. Confirm the transaction rolls back both the stock update and sale insert.
9. History: confirm newest first, Walk-in display, staff/customer/product names, stored totals, and graceful empty state.
10. Optional PC-store showcase: after creating the first administrator, run `php spark db:seed PcStoreSampleSeeder` once and confirm 20 sample products, 20 fictional customers, 20 non-admin demo staff, and 20 sales. Rerun it and confirm that the counts and stock quantities do not change. Demo staff passwords must remain random and undisclosed; the existing administrator password must remain unchanged.

## Rubric mapping

| Category | Evidence in project |
| --- | --- |
| Architecture and design (20) | CI4 MVC controllers/models/views, reusable layout, filters, `SaleService`, migrations, responsive CSS |
| Features and functionality (30) | Product/customer/staff CRUD or archive, auth, sale transaction, stock, history, uploads |
| Validation and security (20) | CSRF, server validation, escaped output, password hashing, auth filter, MIME/content checks, generated filenames, transaction locking |
| Code quality (15) | Small controllers, allowed model fields, no SQL in views, comments/docs where decisions matter, framework conventions |
| Documentation/deployment (15) | Windows setup, GitHub steps, Dockerfile, Railway steps, troubleshooting, testing checklist |

## Status boundaries

Composer installation, validation, advisory audit, PHP lint, route listing, MariaDB migration/seed, automated tests, concurrent sale workers, and the broader local HTTP smoke flow passed. The supplied DOCX rubric and Markdown guide were read and reconciled with the implementation. Docker was not installed locally; however, Railway successfully built and started the container, ran all five pre-deploy migrations, served the public HTTPS login page, and accepted the first staff login on 2026-10-10. The app upload volume is mounted, but an actual image surviving redeploy and the full production CRUD/sales checklist above remain unverified. See `RAILWAY_DEPLOYMENT.md` for the deployed configuration.

A final post-deployment rerun of `composer validate --strict` and `php -l public/index.php` passed. The quick PHPUnit rerun had two passing tests and four skipped integration tests because the dedicated local MariaDB test database was not running. A repeat `composer audit --locked` could not reach Packagist from this sandbox, so the earlier successful audit remains the latest completed advisory check. None of those skips or network failures should be mistaken for a new successful integration or advisory result.
