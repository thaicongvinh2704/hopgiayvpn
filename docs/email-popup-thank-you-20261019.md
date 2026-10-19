# Sitewide email popup confirmation

Updated locally on 19 October 2026 after the owner requested a more professional acknowledgement.

After the existing saved-inbox receipt check, the popup displays:

- Heading: “We've received your email.”
- Message: “Thank you for getting in touch. Our team will review your packaging enquiry and contact you as soon as possible.”
- A blue confirmation mark, a small “Enquiry received” label and the submitted email under “We’ll contact you at”.
- A full-width “Continue browsing” button which closes the modal. The existing close icon and Escape behavior remain available.

The submitted email is retained in the request payload and inserted into the confirmation with `textContent`; it is never rendered as HTML. Focus moves to the confirmation heading and the status region announces receipt. Desktop retains the existing packaging image; mobile uses the existing bottom-sheet layout. No new image, external font or request is added.

Delivery, receipt validation, rate limits, popup frequency and suppression, retries, same-origin session protection and the inbox-only route are unchanged. Success still requires a valid committed receipt; this acknowledgement does not claim that company email has been sent or promise an exact response time.

Validation: PHP lint and scoped diff checks pass. The existing frontend inbox suite passes 106 checks across success, retry, malformed receipt, unavailable service and timeout scenarios (all endpoints mocked; no real leads/emails). A visual preview using actual PHP popup markup and current CSS/JS was reviewed at 1440×1000, 390×844 and 320×568, including a long submitted address, correct recipient, heading focus, hidden form and close action. Evidence: `outputs/email-popup-thank-you-20261019/`.

Not deployed or pushed in this task. Future release must include `wp-content/mu-plugins/vpn-email-quote-popup.php` (1.2.1) and both `vpn-email-quote-popup/popup.js` / `popup.css`, followed by page/asset cache purge. Asset versions already use filemtime. There is no SQL import or Product Sample Deploy step. Older cached markup still gets the revised heading, explanatory message and close button; the added mark and recipient panel appear when current markup is served.
