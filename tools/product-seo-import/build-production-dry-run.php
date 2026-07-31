<?php
/**
 * Build a filesystem-only production dry-run package.
 *
 * No network request or database connection is made. Local URLs are transformed
 * only inside copied package files; the authoritative local source is untouched.
 */

declare(strict_types=1);

const LOCAL_BASE = 'http://localhost/hopgiayvpn';
const PRODUCTION_BASE = 'https://hopgiayvpn.com';

$root = dirname(__DIR__, 2);
$sourceDir = $root . '/seo-content/product-rewrite-v1';
$finalDir = $root . '/artifacts/product-seo-final-v1';
$packageDir = $finalDir . '/production-dry-run';
$contentDir = $packageDir . '/products';
$manifestPath = $sourceDir . '/content-manifest.json';

function packageFail(string $message): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(2);
}

function writeCsvFile(string $path, array $headers, array $rows): void
{
    $handle = fopen($path, 'wb');
    if (!$handle) {
        packageFail("Cannot write {$path}");
    }
    fputcsv($handle, $headers, ',', '"', '');
    foreach ($rows as $row) {
        fputcsv($handle, array_map(static fn(string $header): mixed => $row[$header] ?? '', $headers), ',', '"', '');
    }
    fclose($handle);
}

$manifest = is_file($manifestPath) ? json_decode((string) file_get_contents($manifestPath), true) : null;
if (!is_array($manifest) || 179 !== (int) ($manifest['product_count'] ?? 0)) {
    packageFail('The source manifest is missing or does not contain 179 products.');
}
if (!is_dir($contentDir) && !mkdir($contentDir, 0775, true) && !is_dir($contentDir)) {
    packageFail('Cannot create production dry-run directory.');
}

$products = [];
$validationRows = [];
foreach ($manifest['products'] as $product) {
    $id = (int) $product['product_id'];
    $slug = (string) $product['slug'];
    $sourcePath = (string) $product['html_file'];
    if (!is_file($sourcePath)) {
        packageFail("Missing source content for product {$id}.");
    }
    $sourceHtml = (string) file_get_contents($sourcePath);
    $productionHtml = str_replace(LOCAL_BASE, PRODUCTION_BASE, $sourceHtml);
    if (str_contains($productionHtml, LOCAL_BASE)) {
        packageFail("Local URL remains in transformed content for product {$id}.");
    }
    $packageFile = sprintf('%d-%s.html', $id, $slug);
    $packagePath = $contentDir . '/' . $packageFile;
    file_put_contents($packagePath, $productionHtml);
    $productionUrl = str_replace(LOCAL_BASE, PRODUCTION_BASE, (string) $product['url']);
    $internalLinks = [];
    foreach ($product['internal_links'] as $link) {
        $link['target_url'] = str_replace(LOCAL_BASE, PRODUCTION_BASE, (string) $link['target_url']);
        $internalLinks[] = $link;
    }
    $products[] = [
        'product_id' => $id,
        'title' => $product['title'],
        'slug' => $slug,
        'production_url' => $productionUrl,
        'batch_id' => $product['batch_id'],
        'short_description' => $product['short_description'],
        'seo_title' => $product['seo_title'],
        'meta_description' => $product['meta_description'],
        'focus_keyword' => $product['focus_keyword'],
        'canonical_action' => 'PRESERVE_EXISTING',
        'internal_links' => $internalLinks,
        'content_file' => 'products/' . $packageFile,
        'source_local_sha256' => hash('sha256', $sourceHtml),
        'production_copy_sha256' => hash_file('sha256', $packagePath),
    ];
    $validationRows[] = [
        'product_id' => $id,
        'slug' => $slug,
        'batch_id' => $product['batch_id'],
        'production_url' => $productionUrl,
        'local_url_absent_from_content' => !str_contains($productionHtml, LOCAL_BASE) ? 'PASS' : 'FAIL',
        'starts_with_h2' => preg_match('/^\s*<h2\b/i', $productionHtml) ? 'PASS' : 'FAIL',
        'contains_no_h1' => !preg_match('/<h1\b/i', $productionHtml) ? 'PASS' : 'FAIL',
        'content_sha256' => hash_file('sha256', $packagePath),
    ];
}

$titleValues = [];
$metaValues = [];
$focusValues = [];
foreach ($products as $product) {
    $titleValues[mb_strtolower(trim((string) $product['seo_title']), 'UTF-8')][] = $product['product_id'];
    $metaValues[mb_strtolower(trim((string) $product['meta_description']), 'UTF-8')][] = $product['product_id'];
    $focusValues[mb_strtolower(trim((string) $product['focus_keyword']), 'UTF-8')][] = $product['product_id'];
}
$duplicateCount = static fn(array $groups): int => count(array_filter($groups, static fn(array $ids): bool => count($ids) > 1));

$packageManifest = [
    'schema_version' => 1,
    'created_at_utc' => gmdate('c'),
    'mode' => 'PRODUCTION_DRY_RUN_ONLY',
    'network_requests' => 0,
    'database_connections' => 0,
    'production_writes' => 0,
    'source_manifest_sha256' => hash_file('sha256', $manifestPath),
    'product_count' => count($products),
    'allowed_fields_for_future_explicit_import' => [
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
        'unique_titles' => count($titleValues),
        'unique_meta_descriptions' => count($metaValues),
        'unique_focus_keywords' => count($focusValues),
        'duplicate_title_groups' => $duplicateCount($titleValues),
        'duplicate_meta_groups' => $duplicateCount($metaValues),
        'duplicate_focus_groups' => $duplicateCount($focusValues),
        'local_url_occurrences_in_content' => 0,
    ],
    'products' => $products,
];
$packageManifestFile = $packageDir . '/production-content-manifest.json';
file_put_contents($packageManifestFile, json_encode($packageManifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);

writeCsvFile(
    $packageDir . '/production-dry-run-validation.csv',
    array_keys($validationRows[0]),
    $validationRows
);

$readme = "# Production deployment package — dry-run only\n\n";
$readme .= "This package was generated from the completed local 179-product source without connecting to `https://hopgiayvpn.com` or any production database.\n\n";
$readme .= "## What is included\n\n";
$readme .= "- 179 production-URL content copies in `products/`.\n";
$readme .= "- Short descriptions and the five approved import fields in `production-content-manifest.json`.\n";
$readme .= "- Internal links transformed from `http://localhost/hopgiayvpn` to `https://hopgiayvpn.com` only inside this copy.\n";
$readme .= "- Hashes and structural validation in `production-dry-run-validation.csv`.\n\n";
$readme .= "## What was not done\n\n";
$readme .= "- No production HTTP request, login, database connection, write, import, deployment, push, merge, redirect, slug change or canonical change.\n";
$readme .= "- No credentials or SQL backup are included.\n";
$readme .= "- No executable production importer is included; a future deployment must first establish production backup, exact environment guards, a dry-run against current production records and explicit owner approval.\n\n";
$readme .= "Protected fields must remain unchanged: product title/H1, slug, GUID, canonical, images, SKU, price, stock, category and status.\n";
file_put_contents($packageDir . '/README.md', $readme);

$packageFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($packageDir, FilesystemIterator::SKIP_DOTS));
$fileHashes = [];
foreach ($packageFiles as $file) {
    if (!$file->isFile() || basename((string) $file) === 'package-sha256.json') {
        continue;
    }
    $relative = str_replace('\\', '/', substr((string) $file, strlen($packageDir) + 1));
    $fileHashes[$relative] = hash_file('sha256', (string) $file);
}
ksort($fileHashes);
file_put_contents(
    $packageDir . '/package-sha256.json',
    json_encode(['files' => $fileHashes], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
);

echo json_encode([
    'ok' => true,
    'mode' => 'PRODUCTION_DRY_RUN_ONLY',
    'products' => count($products),
    'network_requests' => 0,
    'database_connections' => 0,
    'production_writes' => 0,
    'package_dir' => $packageDir,
    'manifest_sha256' => hash_file('sha256', $packageManifestFile),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
