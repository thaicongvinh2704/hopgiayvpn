# Sitewide email quote popup

Local implementation: desktop center modal and mobile bottom sheet. Uses the theme shared private Quote Requests inbox and existing sales email/retry transport.

- 30 seconds of foreground browsing, accumulated across pages in the same tab.
- At most once per visit (30-minute inactivity reset), twice per rolling 30-day window, with at least 24 hours between appearances.
- Stops after confirmed quote receipt; boolean cookie lasts one year. Form interaction suppresses the current visit; active chat also suppresses it.
- Frequency is per browser/site; clearing storage or changing device resets it. No email or message is persisted in browser storage or sent to analytics.
- Email uses native autocomplete=email. Actual suggestions depend on the visitor browser and its saved data.
- The public token endpoint refreshes WordPress form tokens on submission, including cached pages.
- Local administrators can use the admin toolbar "Xem thử popup" to preview immediately without consuming a display allowance. The URL flag is ignored for guests and outside the local environment. Dismissing a preview keeps it closed until another preview page load. Diagnostic data attributes expose only the suppression reason and elapsed seconds, never contact data.
- Legal, checkout, account, preview, 404, feed and admin views are excluded.
- The supplied packaging photo is an AI-generated decorative illustration, not evidence of a real factory/order. Native ImageGen source and prompt are recorded in asset-source.json.

Deploy together: MU plugin PHP + entire asset folder + updated theme inc/quote-form-handler.php. No SQL import or Product Sample Deploy step. A functioning hosting email transport is still required for notifications; saved leads remain in WordPress even if mail delivery needs retry.
