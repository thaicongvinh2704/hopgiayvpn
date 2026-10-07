# Shared admin maintenance verification

Use only the dedicated WordPress installation from `tests/live-chat/runtime/wp`, database `vpn_chat_test` on `127.0.0.1:3311`, HTTP port 8091. PHP setup/profile/backend scripts refuse the primary database. Browser tests target only loopback and block external destinations.

With that database running:

```powershell
C:/xampp/php/php.exe tests/admin/setup.php
C:/xampp/php/php.exe tests/admin/maintenance.php
C:/xampp/php/php.exe tests/admin/profile-maintenance.php after
C:/xampp/php/php.exe -S 127.0.0.1:8091 -t tests/live-chat/runtime/wp tests/live-chat/router.php
```

Run `tests/admin/browser.mjs` using the cached Node runtime in a separate terminal, then stop the test HTTP server. Run `C:/xampp/php/php.exe tests/admin/setup.php restore` after verification to restore the original test theme/plugins.

The backend suite intentionally marks content bundles complete in the test DB so browser checks exercise normal admin navigation instead of importing all source bundles. IndexNow HTTP is mocked for failure/success and delayed 300 ms. Tests do not measure or access production admin. Reports are in `artifacts/admin-performance/`; baseline profile was captured before the maintenance changes.
