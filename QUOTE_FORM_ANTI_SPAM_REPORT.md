# Quote Form Anti-Spam Implementation Report

Date: 2026-07-08

## Scope

Implemented a server-side anti-spam layer for the custom WordPress quote form flow handled by:

- `wp-content/themes/custom-box-theme/inc/quote-form-handler.php`
- `wp-content/themes/custom-box-theme/template-parts/home/quote-form.php`
- `wp-content/themes/custom-box-theme/page-paper-box-manufacturer.php`
- `wp-content/themes/custom-box-theme/assets/css/main.css`
- `QUOTE_FORM_ANTI_SPAM_REPORT.md`

The anti-spam layer applies to all public quote form instances that submit to `admin-post.php` with `action=custom_box_quote_form`.

## reCAPTCHA v3 Update (2026-07-14)

Enabled Google reCAPTCHA v3 protection for all three public quote forms:

- Shared quote form on the homepage/contact and product pages
- Paper Box Manufacturer landing page forms
- Packaging landing quick quote form

The site key and secret key are configured in `wp-config.php`. On submit, the browser obtains a short-lived token for the `quote_submit` action. The server verifies it with Google's `siteverify` endpoint, and requires a matching action, a score of at least `0.5`, and the current WordPress hostname before accepting a quote submission. Failed verification returns the `captcha` status and prevents saving the lead or sending email.

## What Changed

### 1. Honeypot and timestamp fields

Added reusable helper:

- `custom_box_quote_form_anti_spam_fields($context)`

It outputs:

- Hidden honeypot field: `website_url`
- Hidden render timestamp: `custom_box_form_started_at`
- Hidden context: `custom_box_form_context`
- Hidden HMAC signature: `custom_box_form_signature`

The helper is now used in:

- Shared quote form template: `template-parts/home/quote-form.php`
- Paper box manufacturer landing quote form closure: `page-paper-box-manufacturer.php`

### 2. Server-side spam scoring

Added scoring function:

- `vpn_calculate_quote_spam_score($data)`

Implemented rules:

- Full Name or Product Name matching `RobertZet`, `Robert Zet`, `robertzet`, or `ROBERTZET`: hard-block with reason `known_hard_block_spam_name`
- Honeypot filled: `+10`
- Timestamp missing/bad/expired: `+5`
- Submitted under 5 seconds: `+5`
- Name matches known spam pattern such as `RobertZet`: `+4`
- Company empty: `+2`
- Country/region empty: `+2`
- Message contains Cyrillic, Georgian, Arabic, or unusual random characters: `+3`
- Message contains URLs: `+3`
- Quantity below 100: `+1`
- Same IP submits more than 3 times in 10 minutes: `+4`
- Disposable/suspicious email: `+3`

Thresholds:

- Score `>= 6`: blocked as spam
- Score `3 to 5`: saved as suspicious
- Score `< 3`: normal lead

Extra safety:

- Submissions under 5 seconds are blocked even when the score is exactly 5, to match the requested test case.

### 3. Spam handling

Blocked spam:

- Does not save a quote post
- Does not send admin notification
- Does not send any auto-reply
- Does not trigger marketing/Brevo style lead handling
- Saves a private spam log entry in WordPress option `custom_box_quote_spam_log`
- Redirects as success or thank-you so bots cannot tell they were blocked

Hard-block conditions:

- Honeypot filled
- Reason `known_hard_block_spam_name`
- Spam score `>= 6`

Suspicious leads:

- Are saved as `custom_box_quote`
- Get post meta:
  - `_custom_box_quote_spam_status`
  - `_custom_box_quote_spam_score`
  - `_custom_box_quote_spam_reasons`
  - `_custom_box_quote_marketing_sync_allowed`
- Admin email subject is prefixed with `[Suspicious Quote Lead]`
- Email body includes spam score and reasons
- Marketing sync flag is set to `no`

Clean leads:

- Continue the existing save, admin email, thank-you redirect, iframe success, and dataLayer-compatible flow.

### 4. IP rate limiting

Existing transient IP tracking was kept and converted from hard blocking to spam scoring.

- Transient key uses hashed IP
- Limit remains more than 3 submissions in 10 minutes
- Exceeding limit adds `+4` instead of showing a 429 error

### 5. Spam log

Blocked spam is stored in the private WordPress option:

- `custom_box_quote_spam_log`

Each entry includes:

- Timestamp
- IP
- IP hash
- Name
- Email
- Message
- Spam score
- Spam reasons
- Quote source
- Form location

The log is not written to a public web path.

### 6. Form updates

The shared quote form now includes:

- Company Name
- Country / Region

This allows real leads to avoid the empty-company and empty-country scoring rules.

### 7. Mobile layout protection

Updated `main.css` so quote form rows stack on mobile and hidden honeypot fields stay invisible.

## Test Cases

### A. Real lead should pass

Use normal human timing, leave honeypot empty, wait more than 5 seconds, complete math challenge, and submit:

- Full Name: `Anna Lee`
- Company: `Bright Retail Co`
- Country: `United States`
- Email: `anna@brightretail.example`
- Quantity: `1000`
- Message: `We need a custom rigid box quote for a skincare gift set.`

Expected:

- Spam score below 3
- Quote post saved
- Admin email sent normally
- Normal success or thank-you redirect

### B. RobertZet spam should be blocked

Submit:

- Full Name: `RobertZet`
- Company: empty
- Country: empty
- Quantity: `10`
- Message with unusual foreign/random characters or URL

Expected:

- Score >= 6
- No admin email
- No quote post
- Logged in `custom_box_quote_spam_log`
- User/bot sees normal success flow

### C. Honeypot filled should be blocked

Fill hidden field:

- `website_url=https://spam.example`

Expected:

- Score +10
- Blocked
- Logged in spam option
- Normal success-style redirect

### D. Submit under 5 seconds should be blocked

Submit immediately after page load with a valid-looking lead.

Expected:

- Reason `submitted_under_5_seconds`
- Blocked even if score is exactly 5
- Logged in spam option
- Normal success-style redirect

### E. Same IP submitting many times

Submit more than 3 quote requests from the same IP within 10 minutes.

Expected:

- Reason `ip_rate_limit_exceeded`
- Score +4
- Submission is marked suspicious or blocked if combined with other signals
- No 429 message is shown to the bot

## Verification Performed

PHP syntax checks passed for:

- `inc/quote-form-handler.php`
- `template-parts/home/quote-form.php`
- `page-paper-box-manufacturer.php`

Local homepage rendering check passed:

- Google reCAPTCHA script loads with the configured v3 site key and `render=<site-key>`.
- The quote form contains the hidden `g-recaptcha-response` field.
- The submit handler requests a `quote_submit` token before forwarding the form submission.

## Self-Test Results

Test method:

- Bootstrapped WordPress through `wp-load.php`
- Called `vpn_calculate_quote_spam_score()` and `custom_box_quote_form_is_blocked_spam()` directly
- Tested transient IP rate limiting with a documentation IP, then deleted the test transient
- Did not call the full submit handler, so no real quote post or email was created during this self-test

Results:

- Real lead should pass: `PASS`, score `0`, blocked `no`
- RobertZet spam should block: `PASS`, score `12`, blocked `yes`
- Honeypot filled should block: `PASS`, score `10`, blocked `yes`
- Submit under 5 seconds should block: `PASS`, score `5`, blocked `yes`
- Same IP scoring should mark suspicious: `PASS`, score `4`, blocked `no`
- Transient rate limiter increments to 4th submit: `PASS`, count `4`, allowed `no`
- Cyrillic/unusual character rule: `PASS`, score `3`, reason `message_unusual_characters`

## Hard-Block Retest Results

Retest method:

- Bootstrapped WordPress through `wp-load.php`
- Called `vpn_calculate_quote_spam_score()` and `custom_box_quote_form_is_blocked_spam()` directly
- Did not call the full submit handler, so no real quote post or email was created during this retest
- Because the handler checks `custom_box_quote_form_is_blocked_spam()` before save/email/sync, a passing hard-block test means admin email and quote post creation are skipped for that submission path

Results:

- Full Name `RobertZet`: `PASS`, blocked `yes`, reason `known_hard_block_spam_name`
- Product Name `RobertZet`: `PASS`, blocked `yes`, reason `known_hard_block_spam_name`
- Full Name `robertzet`: `PASS`, blocked `yes`, reason `known_hard_block_spam_name`
- Full Name `ROBERTZET`: `PASS`, blocked `yes`, reason `known_hard_block_spam_name`
- Full Name `Robert Zet`: `PASS`, blocked `yes`, reason `known_hard_block_spam_name`
- Product Name `Robert Zet`: `PASS`, blocked `yes`, reason `known_hard_block_spam_name`
- Real lead with normal name/company/country/quantity/message: `PASS`, score `0`, blocked `no`
