<?php
/**
 * Build an offline production pilot package for BATCH-01 only.
 *
 * This script reads source files and writes package copies. It does not bootstrap
 * WordPress, connect to a database or make a network request.
 */

declare(strict_types=1);

const LOCAL_BASE_URL = 'http://localhost/hopgiayvpn';
const PRODUCTION_BASE_URL = 'https://hopgiayvpn.com';
const PILOT_BATCH = 'BATCH-01';

$root = dirname(__DIR__, 2);
$sourceDir = $root . '/seo-content/product-rewrite-v1';
$releaseDir = $root . '/artifacts/product-seo-release-checkpoint-v1';
$pilotDir = $releaseDir . '/production-pilot-batch-01';
$productsDir = $pilotDir . '/products';
$sourceManifestPath = $sourceDir . '/content-manifest.json';

function pilotFail(string $message): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(2);
}

function writePilotCsv(string $path, array $headers, array $rows): void
{
    $handle = fopen($path, 'wb');
    if (!$handle) {
        pilotFail("Cannot write {$path}");
    }
    fputcsv($handle, $headers, ',', '"', '');
    foreach ($rows as $row) {
        fputcsv($handle, array_map(static fn(string $header): mixed => $row[$header] ?? '', $headers), ',', '"', '');
    }
    fclose($handle);
}

$sourceManifest = is_file($sourceManifestPath)
    ? json_decode((string) file_get_contents($sourceManifestPath), true)
    : null;
if (!is_array($sourceManifest) || 179 !== (int) ($sourceManifest['product_count'] ?? 0)) {
    pilotFail('The 179-product source manifest is missing or invalid.');
}

$pilotProducts = array_values(array_filter(
    $sourceManifest['products'],
    static fn(array $product): bool => PILOT_BATCH === ($product['batch_id'] ?? '')
));
usort($pilotProducts, static fn(array $left, array $right): int => (int) $left['product_id'] <=> (int) $right['product_id']);
if (15 !== count($pilotProducts)) {
    pilotFail('BATCH-01 must contain exactly 15 products.');
}

if (!is_dir($productsDir) && !mkdir($productsDir, 0775, true) && !is_dir($productsDir)) {
    pilotFail('Cannot create pilot package directory.');
}

$manifestProducts = [];
$validationRows = [];
$seoTitles = [];
$metaDescriptions = [];
$focusKeywords = [];

foreach ($pilotProducts as $product) {
    $id = (int) $product['product_id'];
    $sourceHtmlPath = (string) $product['html_file'];
    if (!is_file($sourceHtmlPath)) {
        pilotFail("Missing source HTML for product {$id}.");
    }
    $sourceHtml = (string) file_get_contents($sourceHtmlPath);
    $pilotHtml = str_replace(LOCAL_BASE_URL, PRODUCTION_BASE_URL, $sourceHtml);
    $pilotFileName = sprintf('%d-%s.html', $id, $product['slug']);
    $pilotHtmlPath = $productsDir . '/' . $pilotFileName;
    file_put_contents($pilotHtmlPath, $pilotHtml);

    $links = [];
    foreach ($product['internal_links'] as $link) {
        $link['target_url'] = str_replace(LOCAL_BASE_URL, PRODUCTION_BASE_URL, (string) $link['target_url']);
        $links[] = $link;
    }
    $productionUrl = str_replace(LOCAL_BASE_URL, PRODUCTION_BASE_URL, (string) $product['url']);
    $manifestProducts[] = [
        'product_id' => $id,
        'title' => $product['title'],
        'slug' => $product['slug'],
        'production_url' => $productionUrl,
        'batch_id' => PILOT_BATCH,
        'short_description' => $product['short_description'],
        'seo_title' => $product['seo_title'],
        'meta_description' => $product['meta_description'],
        'focus_keyword' => $product['focus_keyword'],
        'internal_links' => $links,
        'canonical_action' => 'PRESERVE_EXISTING',
        'content_file' => 'products/' . $pilotFileName,
        'source_html_sha256' => hash('sha256', $sourceHtml),
        'pilot_html_sha256' => hash_file('sha256', $pilotHtmlPath),
    ];
    $localUrlAbsent = !str_contains($pilotHtml, LOCAL_BASE_URL);
    $validationRows[] = [
        'product_id' => $id,
        'title' => $product['title'],
        'slug' => $product['slug'],
        'production_url' => $productionUrl,
        'starts_with_h2' => preg_match('/^\s*<h2\b/i', $pilotHtml) ? 'PASS' : 'FAIL',
        'contains_no_h1' => preg_match('/<h1\b/i', $pilotHtml) ? 'FAIL' : 'PASS',
        'local_url_absent' => $localUrlAbsent ? 'PASS' : 'FAIL',
        'source_hash_matches' => hash('sha256', $sourceHtml) === (string) $product['html_sha256'] ? 'PASS' : 'FAIL',
        'pilot_html_sha256' => hash_file('sha256', $pilotHtmlPath),
    ];
    $seoTitles[mb_strtolower(trim((string) $product['seo_title']), 'UTF-8')][] = $id;
    $metaDescriptions[mb_strtolower(trim((string) $product['meta_description']), 'UTF-8')][] = $id;
    $focusKeywords[mb_strtolower(trim((string) $product['focus_keyword']), 'UTF-8')][] = $id;
}

$duplicateGroups = static fn(array $groups): int => count(array_filter(
    $groups,
    static fn(array $ids): bool => count($ids) > 1
));
$validationFailures = array_values(array_filter(
    $validationRows,
    static fn(array $row): bool => in_array('FAIL', $row, true)
));

$pilotManifest = [
    'schema_version' => 1,
    'created_at_utc' => gmdate('c'),
    'mode' => 'PRODUCTION_PILOT_DRY_RUN_ONLY',
    'batch_id' => PILOT_BATCH,
    'product_count' => count($manifestProducts),
    'source_manifest_sha256' => hash_file('sha256', $sourceManifestPath),
    'network_requests' => 0,
    'database_connections' => 0,
    'production_writes' => 0,
    'allowed_fields_for_future_explicit_pilot' => [
        'post_excerpt',
        'post_content',
        'rank_math_title',
        'rank_math_description',
        'rank_math_focus_keyword',
    ],
    'protected_fields' => [
        'post_title',
        'post_name',
        'guid',
        'rank_math_canonical_url',
        'images',
        'sku',
        'price',
        'stock',
        'categories',
        'status',
    ],
    'qa' => [
        'validation_failures' => count($validationFailures),
        'unique_seo_titles' => count($seoTitles),
        'unique_meta_descriptions' => count($metaDescriptions),
        'unique_focus_keywords' => count($focusKeywords),
        'duplicate_title_groups' => $duplicateGroups($seoTitles),
        'duplicate_meta_groups' => $duplicateGroups($metaDescriptions),
        'duplicate_focus_groups' => $duplicateGroups($focusKeywords),
    ],
    'products' => $manifestProducts,
];

$pilotManifestPath = $pilotDir . '/pilot-manifest.json';
file_put_contents(
    $pilotManifestPath,
    json_encode($pilotManifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
);
writePilotCsv(
    $pilotDir . '/pilot-validation.csv',
    array_keys($validationRows[0]),
    $validationRows
);

$readme = "# Production pilot — BATCH-01 — dry-run only\n\n";
$readme .= "- Scope: 15 products from `BATCH-01` only.\n";
$readme .= "- Production URL copies are prepared, but **nothing has been executed against production**.\n";
$readme .= "- Package generation made 0 network requests and 0 database connections.\n";
$readme .= "- Before any future pilot execution: create a fresh production backup, verify the current 15 IDs/slugs/canonicals, run a production dry-run, obtain explicit approval and retain an immediate rollback path.\n";
$readme .= "- Only five fields may be changed in a future approved pilot: excerpt, content, Rank Math title, description and focus keyword.\n";
$readme .= "- Product title, slug, GUID, canonical, images, SKU, price, stock, categories and status are protected.\n";
file_put_contents($pilotDir . '/README.md', $readme);

$files = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pilotDir, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if (!$file->isFile() || 'pilot-package-sha256.json' === $file->getFilename()) {
        continue;
    }
    $relative = str_replace('\\', '/', substr((string) $file, strlen($pilotDir) + 1));
    $files[$relative] = [
        'bytes' => $file->getSize(),
        'sha256' => hash_file('sha256', (string) $file),
    ];
}
ksort($files);
file_put_contents(
    $pilotDir . '/pilot-package-sha256.json',
    json_encode(['files' => $files], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
);

$fatal = count($validationFailures) > 0
    || 15 !== count($seoTitles)
    || 15 !== count($metaDescriptions)
    || 15 !== count($focusKeywords);

echo json_encode([
    'ok' => !$fatal,
    'mode' => 'PRODUCTION_PILOT_DRY_RUN_ONLY',
    'batch_id' => PILOT_BATCH,
    'products' => count($manifestProducts),
    'validation_failures' => count($validationFailures),
    'network_requests' => 0,
    'database_connections' => 0,
    'production_writes' => 0,
    'pilot_dir' => $pilotDir,
    'manifest_sha256' => hash_file('sha256', $pilotManifestPath),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
exit($fatal ? 3 : 0);
