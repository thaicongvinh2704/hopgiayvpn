# Product batches for Google Search Console

After pulling this release, open **Tools > Product Google Submission**. Click
**Submit 5 Mailer Products to Google** for the October 8 mailer batch. For other
published products, select up to 200 in **Products**, choose **Google: Submit
product batch sitemap** under Bulk actions and click Apply.

Connect Google once in **Rank Math SEO > General Settings > Analytics**, granting
Search Console access, and select the property for this website. The tool reuses
Rank Math's existing authentication and token refresh. No service-account file,
new API key or credentials in Git are needed. Missing connection, wrong property
and rejected permission produce errors rather than a success message.

Each selection generates a public XML sitemap containing only those published,
unprotected, indexable products with self-referencing canonical URLs. This batch
sitemap is linked from the main Rank Math sitemap index. The tool refreshes Rank
Math's XML cache and sends an authenticated PUT to the official Search Console
Sitemaps API to register that index. The 20 most recent selection groups remain
linked; the standard product sitemaps still cover all eligible products.

The result page records selection count, time and the actual HTTP response for
this PUT. **Sitemap received by Google** means Google accepted the sitemap
submission, not that its product URLs have already been crawled or indexed.
Transport, token refresh and permission failures are recorded as failed. Only an
administrator can submit; forms and product bulk actions require WordPress
nonces. Local, staging and private websites cannot send requests. No automatic
repeated submissions or attempts to exceed quotas are performed.

This is Google's supported bulk-discovery workflow for product pages. It is not
the individual **URL Inspection > Request indexing** operation. The URL
Inspection API reads the indexed version's status; it does not expose that
request button. Google's separate Indexing API supports eligible job postings
and livestream pages, not these packaging products.

## Verification

- PHP syntax checks and Git whitespace checks.
- Isolated WordPress database with the real WooCommerce and Rank Math plugins.
- Actual Rank Math API request with HTTP mocked before WordPress startup; no
  Google requests, emails or production database writes in the local test.
- Administrator, empty/oversized selection, nonproduct, missing connection,
  wrong property, noindex, external canonical and private-site checks.
- Five-URL XML parses, uses the target domain and contains no credentials.
- Batch appears through the real Rank Math sitemap-index filter.
- HTTP 200 records receipt; HTTP 403 and failed token refresh record failure.
  Failed refresh cannot reuse a prior request's success response.
- Same selection updates the same batch rather than creating duplicates.
- Production delivery must be checked after hosting pulls this release and an
  administrator uses the tool with a connected Google account.

References: [Google Sitemaps submission API](https://developers.google.com/webmaster-tools/v1/sitemaps/submit),
[request crawling for many URLs](https://developers.google.com/search/docs/crawling-indexing/ask-google-to-recrawl),
[URL Inspection API](https://developers.google.com/webmaster-tools/v1/urlInspection.index/inspect).
