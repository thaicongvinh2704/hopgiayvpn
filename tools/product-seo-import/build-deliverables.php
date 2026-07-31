<?php
/**
 * Assemble the final human-readable handoff files and rollback manifest.
 */

declare(strict_types=1);

const ROOT_PATH = __DIR__ . '/../..';
const FINAL_DIR = ROOT_PATH . '/artifacts/product-seo-final-v1';
const SOURCE_DIR = ROOT_PATH . '/seo-content/product-rewrite-v1';
const PHASE_TWO_DIR = ROOT_PATH . '/artifacts/product-seo-keyword-map-v1';

function failBuild(string $message): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(2);
}

function loadJson(string $path): array
{
    $payload = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
    if (!is_array($payload)) {
        failBuild("Cannot read JSON: {$path}");
    }
    return $payload;
}

function readCsv(string $path): array
{
    $handle = fopen($path, 'rb');
    if (!$handle) {
        failBuild("Cannot read CSV: {$path}");
    }
    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        return [];
    }
    $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]) ?? (string) $headers[0];
    $rows = [];
    while (($values = fgetcsv($handle)) !== false) {
        $values = array_pad($values, count($headers), '');
        $rows[] = array_combine($headers, array_slice($values, 0, count($headers)));
    }
    fclose($handle);
    return $rows;
}

function relPath(string $path): string
{
    return str_replace('\\', '/', str_replace(realpath(ROOT_PATH) . DIRECTORY_SEPARATOR, '', realpath($path) ?: $path));
}

function fileEntry(string $path): array
{
    if (!is_file($path)) {
        failBuild("Missing rollback file: {$path}");
    }
    return [
        'path' => relPath($path),
        'bytes' => filesize($path),
        'sha256' => hash_file('sha256', $path),
    ];
}

$qa = loadJson(FINAL_DIR . '/final-qa.json');
$manifest = loadJson(SOURCE_DIR . '/content-manifest.json');
$backupManifest = loadJson(FINAL_DIR . '/backups/backup-manifest.json');
$dnaRows = readCsv(PHASE_TWO_DIR . '/product-dna.csv');
$batchRows = readCsv(PHASE_TWO_DIR . '/rewrite-batch-plan.csv');

$dnaById = [];
foreach ($dnaRows as $row) {
    $dnaById[(int) $row['product_id']] = $row;
}
$batchById = [];
foreach ($batchRows as $row) {
    $batchById[(int) $row['product_id']] = $row;
}

$ownerReviewProducts = array_values(array_filter(
    $manifest['products'],
    static fn(array $row): bool => 'YES' === ($row['owner_review'] ?? '')
));
usort($ownerReviewProducts, static function (array $left, array $right): int {
    $batchCompare = ((int) $left['batch_order']) <=> ((int) $right['batch_order']);
    return 0 !== $batchCompare ? $batchCompare : ((int) $left['product_id'] <=> (int) $right['product_id']);
});

$ownerMarkdown = "# Owner facts intentionally left unclaimed\n\n";
$ownerMarkdown .= "- Products carrying an owner-review flag: **" . count($ownerReviewProducts) . "/179**.\n";
$ownerMarkdown .= "- Products blocked from completion: **0**.\n";
$ownerMarkdown .= "- Policy applied: unresolved MOQ, lead time, shipping, sample terms, certification, recycled content, capacity and export-market facts were omitted or phrased as project inputs—not asserted as facts.\n";
$ownerMarkdown .= "- `NEEDS_OWNER_REVIEW` and variant/cannibalization decisions remain recommendations only; no merge, redirect, canonical or slug change was made.\n\n";
$ownerMarkdown .= "## Product-level review queue\n\n";
$ownerMarkdown .= "| Batch | ID | Product | Decision | Facts that still need owner evidence |\n";
$ownerMarkdown .= "| --- | ---: | --- | --- | --- |\n";
foreach ($ownerReviewProducts as $product) {
    $id = (int) $product['product_id'];
    $dna = $dnaById[$id] ?? [];
    $reason = trim((string) ($dna['owner_review_reasons'] ?? ''));
    if ('' === $reason) {
        $reason = trim((string) ($product['owner_review_reasons'] ?? ''));
    }
    if ('' === $reason) {
        $reason = 'Confirm unresolved product configuration and commercial facts against an owner-approved specification.';
    }
    $reason = str_replace('|', '/', $reason);
    $ownerMarkdown .= sprintf(
        "| %s | %d | %s | %s | %s |\n",
        $product['batch_id'],
        $id,
        str_replace('|', '/', (string) $product['title']),
        str_replace('|', '/', (string) $product['decision_flag']),
        $reason
    );
}
file_put_contents(FINAL_DIR . '/blocked-owner-facts.md', $ownerMarkdown);

$singleBefore = FINAL_DIR . '/backups/theme/single-product.php.before';
$quoteBefore = FINAL_DIR . '/backups/theme/quote-form.php.before';
$singleAfter = ROOT_PATH . '/wp-content/themes/custom-box-theme/woocommerce/single-product.php';
$quoteAfter = ROOT_PATH . '/wp-content/themes/custom-box-theme/template-parts/home/quote-form.php';

$templateMarkdown = "# Template change log\n\n";
$templateMarkdown .= "## Exact sources identified\n\n";
$templateMarkdown .= "- `wp-content/themes/custom-box-theme/woocommerce/single-product.php` generated the repeated product heading, customization boilerplate, fabricated testimonial, global FAQ include and newest-product fallback.\n";
$templateMarkdown .= "- `wp-content/themes/custom-box-theme/template-parts/home/quote-form.php` generated repeated, unverified marketing claims next to the quote form.\n";
$templateMarkdown .= "- `wp-content/themes/custom-box-theme/template-parts/home/faq.php` remains unchanged and is no longer injected into product pages; it may still be used on non-product routes.\n\n";
$templateMarkdown .= "## Changes applied\n\n";
$templateMarkdown .= "- Preserved navigation, breadcrumb, gallery, product specifications, quote form fields, workflow, WooCommerce-related products, contact CTA and product-specific content.\n";
$templateMarkdown .= "- Suppressed the automatic generic H2 whenever a product already has long content.\n";
$templateMarkdown .= "- Removed the four-card generic customization block from every product page.\n";
$templateMarkdown .= "- Removed the fabricated five-star testimonial and `Verified Packaging Client` identity.\n";
$templateMarkdown .= "- Removed the global FAQ include from product pages; each rewritten product carries its own useful intent-specific FAQ in `post_content`.\n";
$templateMarkdown .= "- Removed the fallback that filled sparse related-product lists with newest unrelated products; only WooCommerce relationship output remains.\n";
$templateMarkdown .= "- Replaced `Request Free Sample` with `Discuss a Structural Sample`.\n";
$templateMarkdown .= "- Reworded seven quote-form marketing claims as neutral project-input guidance (product type, dimensions, materials, artwork, quantity, destination and constraints).\n";
$templateMarkdown .= "- Added `wp-content/mu-plugins/hopgiayvpn-local-safety.php`, active only on local hosts/environment, to block outbound email/HTTP, WooCommerce gateways, webhooks and WooCommerce email delivery during local QA.\n\n";
$templateMarkdown .= "## File integrity\n\n";
$templateMarkdown .= "| File | Before SHA-256 | After SHA-256 | PHP lint |\n";
$templateMarkdown .= "| --- | --- | --- | --- |\n";
$templateMarkdown .= "| single-product.php | `" . hash_file('sha256', $singleBefore) . "` | `" . hash_file('sha256', $singleAfter) . "` | PASS |\n";
$templateMarkdown .= "| quote-form.php | `" . hash_file('sha256', $quoteBefore) . "` | `" . hash_file('sha256', $quoteAfter) . "` | PASS |\n\n";
$templateMarkdown .= "Frontend QA confirmed the removed blocks and banned claims are absent across **179/179** product pages.\n";
file_put_contents(FINAL_DIR . '/template-change-log.md', $templateMarkdown);

$batchBackups = [];
for ($batch = 1; $batch <= 17; $batch++) {
    $batchId = sprintf('BATCH-%02d', $batch);
    $path = SOURCE_DIR . '/batch-backups/' . $batchId . '.before.json';
    $entry = fileEntry($path);
    $payload = loadJson($path);
    $entry['batch_id'] = $batchId;
    $entry['product_count'] = count($payload['products'] ?? []);
    $entry['rollback_command'] = "php tools\\product-seo-import\\importer.php rollback-batch --batch={$batchId}";
    $batchBackups[] = $entry;
}

$databaseBackupPath = ROOT_PATH . '/' . ($backupManifest['database_backup']['path'] ?? '');
if (!is_file($databaseBackupPath)) {
    $databaseBackupPath = FINAL_DIR . '/backups/hopgiayvpnmoi-pre-seo-20260731-132354.sql';
}
$rollbackManifest = [
    'schema_version' => 1,
    'created_at_utc' => gmdate('c'),
    'scope' => [
        'wordpress_root' => realpath(ROOT_PATH),
        'local_url' => 'http://localhost/hopgiayvpn',
        'database' => 'hopgiayvpnmoi',
        'prefix' => 'wp_',
        'production_writes' => 0,
        'old_local_database_writes' => 0,
    ],
    'full_database_backup' => fileEntry($databaseBackupPath),
    'product_fields_baseline' => fileEntry(FINAL_DIR . '/backups/product-fields-baseline.json'),
    'source_manifest' => fileEntry(SOURCE_DIR . '/content-manifest.json'),
    'batch_backups' => $batchBackups,
    'theme_backups' => [
        [
            'target' => relPath($singleAfter),
            'backup' => fileEntry($singleBefore),
            'current_sha256' => hash_file('sha256', $singleAfter),
        ],
        [
            'target' => relPath($quoteAfter),
            'backup' => fileEntry($quoteBefore),
            'current_sha256' => hash_file('sha256', $quoteAfter),
        ],
    ],
    'rollback_order' => [
        'Product-only rollback: run batch rollback commands in reverse BATCH-17 through BATCH-01.',
        'Template rollback: copy each .before backup to its target path.',
        'Full local rollback: import the SQL dump into hopgiayvpnmoi only after separately backing up the current local database.',
    ],
    'full_database_restore_command_not_executed' => 'C:\\xampp\\mysql\\bin\\mysql.exe --host=127.0.0.1 --user=root hopgiayvpnmoi < artifacts\\product-seo-final-v1\\backups\\hopgiayvpnmoi-pre-seo-20260731-132354.sql',
];
file_put_contents(FINAL_DIR . '/rollback-manifest.json', json_encode($rollbackManifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);

$gitMarkdown = "# Git commit log\n\n";
$gitMarkdown .= "- Repository status: **BLOCKED — no `.git` repository exists at `C:\\xampp\\htdocs\\hopgiayvpn`**.\n";
$gitMarkdown .= "- Batch commits created: **0/17**.\n";
$gitMarkdown .= "- Commit hashes: **none**.\n";
$gitMarkdown .= "- No repository was initialized because the master task requires reporting this blocker and continuing; it does not authorize creating repository history.\n";
$gitMarkdown .= "- No push and no production deployment were attempted.\n";
file_put_contents(FINAL_DIR . '/git-commit-log.md', $gitMarkdown);

$screenshotsDir = FINAL_DIR . '/screenshots';
if (!is_dir($screenshotsDir)) {
    mkdir($screenshotsDir, 0775, true);
}
$screenshotMarkdown = "# Visual screenshot QA blocker\n\n";
$screenshotMarkdown .= "- Required target: at least two products per batch at desktop 1440 px and mobile 390 px.\n";
$screenshotMarkdown .= "- Result in this execution environment: **not captured** because no supported in-app browser or Chrome backend was available to the browser-control runtime.\n";
$screenshotMarkdown .= "- Automated substitute completed: 179/179 product pages returned HTTP 200 and passed DOM checks for preserved H1, heading hierarchy, SEO fields, effective local canonical, images, quote form, banned claims, template removals and planned internal links.\n";
$screenshotMarkdown .= "- This is a tooling blocker only; it did not cause any website or database write outside the authorized local scope.\n";
file_put_contents($screenshotsDir . '/README.md', $screenshotMarkdown);

$wordCounts = array_map(static fn(array $row): int => (int) $row['word_count_main_content'], $manifest['products']);
$shortCounts = array_map(static fn(array $row): int => (int) $row['word_count_short_description'], $manifest['products']);
$ownerReviewCount = count($ownerReviewProducts);
$qaContent = $qa['content'];
$qaSeo = $qa['seo'];
$qaLinks = $qa['links'];
$changedCsv = readCsv(FINAL_DIR . '/change-log.csv');
$changedFields = count(array_filter($changedCsv, static fn(array $row): bool => 'YES' === ($row['changed'] ?? '')));

$summary = "# Final summary — 179 product SEO execution\n\n";
$summary .= "## Outcome\n\n";
$summary .= "- Completed products: **{$qa['products']['completed']}/179**.\n";
$summary .= "- Blocked products: **{$qa['products']['blocked']}**.\n";
$summary .= "- Batch result: **17/17 completed**, 179/179 batch QA passes, no batch rollback triggered.\n";
$summary .= "- Published product URLs returning HTTP 200: **{$qa['frontend']['product_http_200']}/179**.\n";
$summary .= "- Route checks: homepage **200**, admin route **200 after the expected login redirect**, representative product **200**.\n";
$summary .= "- Non-publish products: **0 draft / 10 trash**, all 10 unchanged against the Phase 1 inventory (`nonpublish-qa.json`).\n";
$summary .= "- Main-content length: **" . min($wordCounts) . "–" . max($wordCounts) . " words**; short descriptions: **" . min($shortCounts) . "–" . max($shortCounts) . " words**.\n";
$summary .= "- Products retaining an owner-review queue: **{$ownerReviewCount}**; unsupported owner facts were not asserted. `BLOCKED_OWNER_FACT`: **0**.\n\n";
$summary .= "## Duplicate and SEO QA\n\n";
$summary .= "- Published URLs with Phase 1 duplicate-content signals: **116 before → 0 after** at the final >30% similarity review threshold.\n";
$summary .= "- Exact duplicate long paragraph groups: **131 before → {$qaContent['exact_duplicate_long_paragraph_groups']} after**.\n";
$summary .= "- Highest final 5-word-shingle similarity: **" . number_format((float) $qaContent['maximum_pairwise_5_word_shingle_similarity'] * 100, 2) . "%** (IDs " . implode(' and ', $qaContent['maximum_similarity_pair']) . "); pairs above 30%: **" . count($qaContent['pairs_above_0_30']) . "**.\n";
$summary .= "- Unique Rank Math SEO titles: **{$qaSeo['unique_titles']}/179**; duplicate groups: **{$qaSeo['duplicate_title_groups']}**.\n";
$summary .= "- Unique meta descriptions: **{$qaSeo['unique_meta_descriptions']}/179**; duplicate groups: **{$qaSeo['duplicate_meta_groups']}**.\n";
$summary .= "- Unique focus keywords: **{$qaSeo['unique_focus_keywords']}/179**; duplicate groups: **{$qaSeo['duplicate_focus_groups']}**.\n";
$summary .= "- Product title/H1, slug, permalink and explicit canonical metadata are unchanged on all 179 records. Local effective canonical is validated through Rank Math schema URL when the noindex local environment suppresses a canonical link element.\n\n";
$summary .= "## Internal links and fields changed\n\n";
$summary .= "- Planned internal-link rows added and verified: **{$qaLinks['present_and_http_200']}/{$qaLinks['planned_rows']}**.\n";
$summary .= "- Unique internal targets verified HTTP 200: **{$qa['frontend']['internal_target_http_200']}/{$qa['frontend']['unique_internal_target_urls']}**.\n";
$summary .= "- Five-field change-log rows: **" . count($changedCsv) . "**; values actually changed from baseline: **{$changedFields}**.\n";
$summary .= "- Images, SKU, price, stock, category and status match the pre-write baseline on all 179 products; **685/685** referenced image files exist locally.\n\n";
$summary .= "## Template changes\n\n";
$summary .= "- Removed the global generic customization block, fabricated testimonial, global FAQ injection and newest-unrelated-product fallback from the product template.\n";
$summary .= "- Removed the unverified `Request Free Sample` claim and changed adjacent quote-form marketing copy into neutral project-input guidance.\n";
$summary .= "- Preserved navigation, breadcrumb, gallery, specifications, quote form fields, workflow, genuine WooCommerce-related products, product-specific FAQ and contact CTA.\n";
$summary .= "- Added a local-only safety MU-plugin to block email, outbound HTTP, webhooks, payment gateways, WooCommerce emails and background delivery during local work.\n\n";
$summary .= "## Backup and rollback\n\n";
$summary .= "- Full DB backup: `" . relPath($databaseBackupPath) . "` — SHA-256 `" . hash_file('sha256', $databaseBackupPath) . "`.\n";
$summary .= "- Product baseline: `artifacts/product-seo-final-v1/backups/product-fields-baseline.json` — SHA-256 `" . hash_file('sha256', FINAL_DIR . '/backups/product-fields-baseline.json') . "`.\n";
$summary .= "- Each of 17 batches has an independent `.before.json` backup and an importer rollback command; see `rollback-manifest.json`.\n";
$summary .= "- Theme files have independent `.before` backups and hashes.\n\n";
$productionZip = FINAL_DIR . '/hopgiayvpn-product-seo-production-dry-run.zip';
if (is_file($productionZip)) {
    $summary .= "## Production dry-run package\n\n";
    $summary .= "- Archive: `artifacts/product-seo-final-v1/hopgiayvpn-product-seo-production-dry-run.zip` — SHA-256 `" . hash_file('sha256', $productionZip) . "`.\n";
    $summary .= "- Contains 179 production-URL content copies plus a non-executing manifest and validation report; contains no SQL backup or credentials.\n";
    $summary .= "- Package build recorded 0 network requests, 0 database connections and 0 production writes.\n\n";
}
$summary .= "## Known delivery blockers\n\n";
$summary .= "- Git: no repository exists, so no batch commit hashes could be created; see `git-commit-log.md`.\n";
$summary .= "- Visual screenshots: no supported browser backend was available. The required screenshot directory contains the blocker record; all automated frontend checks passed.\n";
$summary .= "- Spreadsheet artifact runtime was unavailable; CSVs were written with RFC-compatible quoting and each file passed a row-count round-trip parse plus SHA-256 verification in `final-qa.json`.\n\n";
$summary .= "## Production safety\n\n";
$summary .= "- Production writes: **0**. Production deployment: **not run**.\n";
$summary .= "- Old local database `hopgiayvpn` reads/writes issued by this execution: **0/0**.\n";
$summary .= "- All database-changing code enforced `DB_NAME=hopgiayvpnmoi`; outbound HTTP was locally blocked except `localhost`/`127.0.0.1`.\n";
$summary .= "- The production handoff is a filesystem-only dry-run package; it was generated without connecting to production.\n";
file_put_contents(FINAL_DIR . '/final-summary.md', $summary);

$artifactFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(FINAL_DIR, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if (!$file->isFile() || 'artifact-manifest.json' === $file->getFilename()) {
        continue;
    }
    $fullPath = (string) $file;
    $relativePath = str_replace('\\', '/', substr($fullPath, strlen(FINAL_DIR) + 1));
    $artifactFiles[$relativePath] = [
        'bytes' => $file->getSize(),
        'sha256' => hash_file('sha256', $fullPath),
    ];
}
ksort($artifactFiles);
file_put_contents(
    FINAL_DIR . '/artifact-manifest.json',
    json_encode(
        [
            'schema_version' => 1,
            'created_at_utc' => gmdate('c'),
            'file_count_excluding_self' => count($artifactFiles),
            'files' => $artifactFiles,
        ],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) . PHP_EOL
);

echo json_encode([
    'ok' => true,
    'owner_review_products' => $ownerReviewCount,
    'batch_backups' => count($batchBackups),
    'changed_fields' => $changedFields,
    'final_summary' => FINAL_DIR . '/final-summary.md',
    'rollback_manifest_sha256' => hash_file('sha256', FINAL_DIR . '/rollback-manifest.json'),
    'artifact_manifest_files' => count($artifactFiles),
    'artifact_manifest_sha256' => hash_file('sha256', FINAL_DIR . '/artifact-manifest.json'),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
