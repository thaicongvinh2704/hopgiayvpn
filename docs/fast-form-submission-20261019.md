# Faster form acknowledgement

The owner requested a shorter Sending state after pressing Send. The acknowledgement remains tied to durable storage; the interface never claims a failed or unconfirmed save succeeded.

## Popup

- Public endpoint URLs are supplied with the existing popup configuration. The popup prepares its guest session when it opens or receives focus and reuses it for five minutes. No contact details are sent during preparation.
- Normally, pressing Send now needs only the inbox save request, replacing the previous endpoint discovery → session bootstrap → save chain. Older cached page configurations still discover their URLs in the background.
- An expired session/CSRF response refreshes the session once and retries using the same request ID. Network/storage failures keep the draft and do not mark the lead captured. Popup frequency caps, saved email suggestions and inbox-only delivery remain unchanged.

## Quote forms using the shared theme handler

- Validation and private lead storage remain synchronous. Once saved, the request is acknowledged immediately and company email is queued; the visitor no longer waits for SMTP.
- Paper bag JSON responses return `received` with the existing saved-lead receipt; conversion events already accept that confirmed status. Existing generic/pizza success redirects and the manufacturer thank-you destination are preserved.
- Delivery uses Action Scheduler when available, with a WP-Cron fallback if enqueueing fails or throws. Scheduling failure leaves the private lead saved with `schedule_failed` metadata rather than asking the visitor to resubmit an existing lead.
- A background worker sends the mail, applies a 20-second transport timeout and retries failed sends with backoff up to five attempts. A database-backed owner lock prevents concurrent workers sending the same enquiry. Expired locks can recover after a crash; a finished worker only releases its own token. Successfully sent records are skipped. Mail errors remain recorded on the saved lead.
- This does not enable email for the popup. Its customer-inbox path still sends no company email. No global SMTP plugin or unrelated form/security configuration is changed.

## Validation and measurements

- `node tests/email-popup-inbox-browser.cjs`: 106 checks pass, including preparation before Send, cached configuration, one save request on the normal critical path, transparent session refresh, stable retry IDs, failure preservation and no external/Google/mail-form request.
- `node tests/live-chat/popup-inbox-http.cjs`: 12 checks pass using real local HTTP, WordPress and the isolated database. Desktop/mobile click-to-receipt measurements were 313 ms / 311 ms. The saved messages were opened in the existing customer inbox and continued in chat.
- `php tests/live-chat/fast-quote-submit.php`: 60 checks pass using the real shared handler, WordPress and isolated InnoDB storage. Tests cover JSON, generic, pizza and manufacturer acknowledgement; accepted/failed/throwing Action Scheduler adapters; WP-Cron fallback; queue failure with saved lead; invalid email; worker failure/retry, duplicate jobs, retry cap, expired locks and thrown mail transports. A deliberately slow fake SMTP send took 1,531.86 ms in the worker; successful submission handler times were 20.06–23.82 ms and made zero mail calls. These are handler-only local timings after WordPress bootstrap, not production/network timings.
- PHP/JavaScript syntax and scoped Git whitespace checks pass. External mail/HTTP is blocked in the isolated test installation; no production enquiry or email was sent.
- Read-only hosting checks found `DISABLE_WP_CRON` undefined and `action_scheduler_run_queue` scheduled every minute. These establish configuration, not a successful future production email delivery.

## Deployment

Pull `main`, deploy the full commit and purge page/asset/server/CDN caches. No database import or manual schema change is required. Deploy the popup MU loader/JavaScript and shared quote handler together. Background delivery requires the site's existing Action Scheduler/WP-Cron execution; check a real saved enquiry and its mail status after hosting deployment. Local timings are not a guarantee of hosting response time.

The older synchronous-mail suites (`paper-bags-quote-form.php` and `email-popup-quote-form.php`) contain historical send-count expectations; the current async acceptance suite is `tests/live-chat/fast-quote-submit.php`.
