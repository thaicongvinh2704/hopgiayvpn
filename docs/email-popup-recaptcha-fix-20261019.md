# Sitewide email popup: reCAPTCHA integration fix

## Confirmed production failure

The popup session endpoint returned fresh nonce/timestamp credentials, but the popup submitted no `g-recaptcha-response`. A diagnostic request with an intentionally empty email returned HTTP 400 and `data.status=captcha` before lead creation or mail delivery. The popup displayed a generic failure for this status. No real enquiry was submitted by the diagnostic.

## Change

- The uncached session endpoint now also returns the public reCAPTCHA site key, matching the existing backend validator even when the theme's frontend reCAPTCHA handler is suppressed. The secret key is never returned.
- The popup lazily loads the Google API and obtains a new v3 token using the existing `quote_submit` action immediately before each submission. The shared backend still verifies it normally. No security validation is bypassed.
- A form marker excludes this popup from the theme's generic capture-phase reCAPTCHA handler, so two handlers cannot race or execute twice. Other forms retain their existing behavior.
- Script load/execute errors and timeout restore the submit button and keep the visitor's draft. Server captcha/session/save errors have specific messages. Email autofill, optional project details, no-checkbox contact notice and existing display frequency limits are preserved.

## Verification

`node tests/email-popup-recaptcha.cjs`: 60 checks pass using the actual PHP popup/session render and actual popup/global JavaScript, with Google and submission responses intercepted. Covers theme handler enabled/suppressed, reCAPTCHA unconfigured, rejection, blocked script and retry, execute rejection and retry, timeout, repeated submit clicks, fresh credentials, secret exclusion and other-form regression. No database, email or production writes.

PHP lint on both changed production PHP files, JavaScript syntax and scoped whitespace checks pass. The isolated fixture is CLI-only. This test does not prove a real Google score, hosting mail delivery or the eventual production submission result; verify those after deployment.

## Deployment

Pull `main`, deploy the complete commit (MU plugin loader + popup.js + shared theme quote handler), then purge page/asset/server/CDN caches. No SQL import or Product Sample Deploy is required. PHP OPcache may also need the host's normal deployment reset if file timestamp checks are disabled. Verify the popup on an uncached public page after deployment; do not count a screenshot of the old cached popup as evidence of the fix.
