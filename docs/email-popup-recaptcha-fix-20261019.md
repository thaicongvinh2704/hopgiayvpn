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

## Follow-up: Google initialization race

The next production screenshot reproduced on a clean browser: Google's api.js returned HTTP 200, but the loader checked for `grecaptcha.execute` at the script load event, before Google's secondary script had initialized it. The first mocked API installed `ready` and `execute` at the same time and missed this lifecycle difference.

The popup now waits for `grecaptcha.ready` before checking or calling `execute`. An existing in-progress initialization is reused. The error text no longer attributes an initialization failure to the visitor's content blocker without evidence. Version 1.0.4.

The expanded regression suite passes 73 checks, including delayed initialization and an already-loading API. In a clean browser on the public homepage, overriding only popup.js with the local fix caused the real Google API, secondary script, anchor and reload calls to return 200 and produced a token in the outgoing POST. That POST was intercepted to avoid a lead/email. This establishes client initialization/token acquisition, not production acceptance.

A separate probe forced the email field empty before allowing the request to the real server, guaranteeing rejection before saving or sending. That probe still returned `captcha`; the public response does not disclose whether it is a score, key, action, hostname or provider verification failure. Therefore production backend acceptance and mail delivery remain unverified, and need a normal visitor test plus the existing server-side reCAPTCHA logs if rejection persists. No security threshold or backend verification was weakened.

## Follow-up: confirmed filled honeypot in latest visitor attempt

Read-only inspection of the hosting log viewer identified the latest screenshot's rejection at hosting log time 2026-10-19 09:26:49: `antispam_check` and `reject_spam` with `honeypot_filled`. This occurred before the reCAPTCHA verification branch. An earlier automated probe at 09:03:43 separately recorded `recaptcha_low_score` (0.1 against 0.5); that must not be confused with the latest honeypot error. No contact values are logged or copied into this document.

The popup previously shared a CSS-offscreen `website_url` text input, which is susceptible to contact/browser/password-manager autofill. Version 1.0.5 requests a popup-specific neutral `vpn_quote_guard` field within a hidden, inert, display-none container with password-manager ignore attributes. The visible email still uses `autocomplete=email`. The guard is neither cleared nor silently ignored during submission. The backend checks the new guard for popup submissions and still rejects a filled legacy `website_url`; all other forms keep their original field. Nonce, signed timestamp, rate limiting, duplicate checks, Google token verification and the score threshold remain unchanged.

The expanded suite passes 120 local checks. It renders the actual popup and exercises the actual PHP anti-spam functions using CLI WordPress adapters and intercepted provider calls, without a database, email or production writes. New cases verify valid empty guards, rejection of both filled legacy/new guards, bad signatures, absent tokens and low scores. Browser checks verify hidden/inert markup, absence of the contact-like old field, guard inclusion in the payload and preserved email autofill. This is not proof of a production lead being saved or emailed.

Deploy the MU plugin loader and shared theme handler together. Purge cached HTML as well as asset/CDN caches: a cached page can retain the old `website_url` popup field even with the latest JavaScript. Then refresh the visitor tab before testing again. No SQL migration is involved. Production score decisions remain enforced and should be diagnosed through the existing hosting logs if a later attempt returns `captcha` rather than `spam`.
