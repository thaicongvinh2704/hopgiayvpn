# Email popup → customer inbox, without company email

The owner requested popup submissions go directly to the existing **Hộp thư khách hàng** instead of company email. This release replaces the popup's quote-mail/Google reCAPTCHA transport with the existing VPN Live Chat guest session, CSRF and rate-limit model. Other quote forms retain their existing mail transport and security rules.

## Behavior

- Keep the approved sitewide modal, email autocomplete, optional project details and display-frequency limits. No name or checkbox is required. Optional details are limited to 1,000 characters.
- Save the email, project details, page title/path and supported campaign attribution in the private chat tables. New conversations receive the `needs_quote` label and appear in **VPN Live Chat → Hộp thư khách hàng**.
- Reuse an active conversation only for the same authenticated customer/device. Never merge people by unverified email. Both the email and source are included in the private message body for staff.
- Return success only after the database transaction commits. A request ID stays stable across retry while the payload is unchanged, so a lost network response does not produce a duplicate. Failed requests retain the draft.
- No quote email, bot reply or SLA email is scheduled for a popup capture. Popup-created conversations carry a server-controlled `email_notifications=false` flag; the SLA worker honors it even if a later reassignment queues a job. An existing ordinary chat keeps its previous notification policy, but adding a popup brief does not create an additional email job.
- The ordinary chat widget refreshes after popup success so the visitor can continue the saved conversation without entering email again.
- The fresh popup session endpoint announces inbox delivery. Cached old JavaScript sees an empty reCAPTCHA key and submits its old form to an early compatibility handler, which saves to the same chat inbox and returns its expected receipt shape. It never falls back to company email. Missing chat support fails closed.

## Security and scope

The new `POST /vpn-chat/v1/guest/email-quote` route requires a same-origin JSON request, an authenticated guest cookie, matching guest CSRF token, a valid email and bounded text. It uses existing customer blocks and atomic rate limits. Attachments are not accepted. Staff use the existing inbox permissions. The cached-form adapter requires WordPress nonce plus actual same-origin headers before creating/restoring a chat session. It does not trust submitted IDs to choose another customer's conversation.

The popup no longer invokes Google's score-based validation or the old contact-like honeypot; this is an explicitly requested move to the existing inbox collection channel. No global Google threshold, non-popup quote checks, or ordinary chat access rules are changed. The inbox is a public enquiry collection channel; email is self-reported until separately verified.

## Deployment

Pull `main`, deploy the complete commit, then clear WordPress/server/CDN page and asset caches. Include the new `vpn-live-chat/includes/email-quote.php` and updated plugin entry point, REST/service/jobs/widget files plus the MU popup loader and assets. VPN Live Chat becomes 1.12.0; popup becomes 1.1.0. The existing chat schema stays at version 3: no SQL import, Product Sample Deploy, new table or manual database migration is required on an installation already using the inbox.

Live Chat must be active and **Nhận chat mới** enabled. After hosting deployment, submit a normal popup enquiry and verify it in **VPN Live Chat → Hộp thư khách hàng**. Hosting has not been deployed by this local/Git task.

## Validation

- `php tests/live-chat/popup-inbox.php`: 32 real WordPress REST/InnoDB checks in the isolated `vpn_chat_test` database on loopback 3311. Covers inbox appearance, optional name/details, idempotency, session/CSRF/origin, separate customers with identical email, rollback, disabled acceptance, legacy cached form and no company mail or notification job from popup capture. The background worker also suppresses a forced popup SLA job. Mail and external HTTP are intercepted by the test MU sink.
- `node tests/email-popup-inbox-browser.cjs`: 73 browser checks with API responses intercepted, covering desktop/mobile, email-only enquiry, CSRF, attribution, double click, retry ID stability, timeout, failed persistence, malformed receipt and absence of Google/old mail-form requests.
- `node tests/live-chat/popup-inbox-http.cjs`: 10 checks through real local HTTP/WordPress/database at loopback 8091: actual popup submit on desktop and mobile, success receipt, continued chat with saved email and the saved message visibly opened in the existing admin inbox. Test email/HTTP sink stays active. Screenshots/results are in ignored `tests/live-chat/results/popup-inbox/`.
- `php tests/live-chat/required-email.php`: 32 existing ordinary-chat regression checks pass.
- PHP/JavaScript syntax and scoped Git whitespace checks pass.

The earlier `email-popup-quote-form.php` / `email-popup-recaptcha.cjs` suites document the superseded mail/reCAPTCHA popup transport and are not acceptance suites for this release. Real hosting acceptance is still needed after the owner pulls/deploys. No test enquiry or outbound email was sent on production.
