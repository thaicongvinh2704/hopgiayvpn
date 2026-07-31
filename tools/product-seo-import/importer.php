<?php
declare(strict_types=1);

const ROOT_DIR = 'C:\\xampp\\htdocs\\hopgiayvpn';
const SOURCE_DIR = ROOT_DIR . '\\seo-content\\product-rewrite-v1';
const FINAL_DIR = ROOT_DIR . '\\artifacts\\product-seo-final-v1';
const BACKUP_DIR = FINAL_DIR . '\\backups';
const REPORT_DIR = FINAL_DIR . '\\batch-reports';
const TARGET_DB_NAME = 'hopgiayvpnmoi';
const TARGET_DB_HOST = '127.0.0.1';
const TARGET_DB_USER = 'root';
const TARGET_DB_PASSWORD = '';
const TARGET_TABLE_PREFIX = 'wp_';
const LOCAL_HOME = 'http://localhost/hopgiayvpn';

function fail(string $message, int $code = 1): never
{
    fwrite(STDERR, "ERROR: {$message}\n");
    exit($code);
}

function ensureDir(string $dir): void
{
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        fail("Cannot create directory: {$dir}");
    }
}

function readJson(string $path): array
{
    if (!is_file($path)) {
        fail("Missing JSON: {$path}");
    }
    $decoded = json_decode((string) file_get_contents($path), true);
    if (!is_array($decoded)) {
        fail("Invalid JSON: {$path}");
    }
    return $decoded;
}

function writeJson(string $path, array $data): void
{
    ensureDir(dirname($path));
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false || file_put_contents($path, $json . PHP_EOL) === false) {
        fail("Cannot write JSON: {$path}");
    }
}

function pdo(bool $readOnly = false): PDO
{
    $pdo = new PDO(
        'mysql:host=' . TARGET_DB_HOST . ';dbname=' . TARGET_DB_NAME . ';charset=utf8mb4',
        TARGET_DB_USER,
        TARGET_DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ]
    );
    if ($readOnly) {
        $pdo->exec('SET SESSION TRANSACTION READ ONLY');
        $pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');
        if ((int) $pdo->query('SELECT @@tx_read_only')->fetchColumn() !== 1) {
            fail('Cannot enforce read-only transaction.');
        }
    }
    if ((string) $pdo->query('SELECT DATABASE()')->fetchColumn() !== TARGET_DB_NAME) {
        fail('Connected to the wrong database.');
    }
    return $pdo;
}

function loadState(): array
{
    if (!is_file(ROOT_DIR . '\\wp-load.php') || !is_file(ROOT_DIR . '\\wp-config.php')) {
        fail('WordPress source is missing.');
    }
    $manifest = readJson(SOURCE_DIR . '\\content-manifest.json');
    $baseline = readJson(BACKUP_DIR . '\\product-fields-baseline.json');
    $backupManifest = readJson(BACKUP_DIR . '\\backup-manifest.json');
    if (
        (int) ($manifest['product_count'] ?? 0) !== 179
        || (int) ($baseline['product_count'] ?? 0) !== 179
        || (int) ($backupManifest['product_baseline']['product_count'] ?? 0) !== 179
    ) {
        fail('Manifest or baseline does not cover exactly 179 products.');
    }
    $baselineHash = strtoupper((string) hash_file('sha256', BACKUP_DIR . '\\product-fields-baseline.json'));
    if ($baselineHash !== strtoupper((string) $backupManifest['product_baseline']['sha256'])) {
        fail('Product baseline hash does not match backup manifest.');
    }
    $dumpPath = (string) ($backupManifest['database_dump']['path'] ?? '');
    if (!is_file($dumpPath) || strtoupper((string) hash_file('sha256', $dumpPath)) !== strtoupper((string) $backupManifest['database_dump']['sha256'])) {
        fail('Full database backup hash validation failed.');
    }

    $products = [];
    foreach ($manifest['products'] as $product) {
        $id = (int) $product['product_id'];
        $htmlPath = (string) $product['html_file'];
        if (!is_file($htmlPath) || hash_file('sha256', $htmlPath) !== (string) $product['html_sha256']) {
            fail("Content source hash failed for product {$id}");
        }
        $product['main_content'] = (string) file_get_contents($htmlPath);
        $products[$id] = $product;
    }
    $baselineProducts = [];
    foreach ($baseline['products'] as $product) {
        $baselineProducts[(int) $product['id']] = $product;
    }
    if (count($products) !== 179 || array_keys($products) !== array_keys($baselineProducts)) {
        $sourceIds = array_keys($products);
        $baselineIds = array_keys($baselineProducts);
        sort($sourceIds, SORT_NUMERIC);
        sort($baselineIds, SORT_NUMERIC);
        if ($sourceIds !== $baselineIds) {
            fail('Content and baseline product IDs do not reconcile.');
        }
    }
    return [$manifest, $products, $baselineProducts, $backupManifest];
}

function selectedProducts(array $products, string $batch): array
{
    if ($batch === 'all') {
        return $products;
    }
    if (!preg_match('/^BATCH-(?:0[1-9]|1[0-7])$/', $batch)) {
        fail("Invalid batch: {$batch}");
    }
    $selected = array_filter(
        $products,
        static fn(array $product): bool => (string) $product['batch_id'] === $batch
    );
    if (!$selected) {
        fail("No products in {$batch}");
    }
    return $selected;
}

function currentRows(PDO $pdo, array $ids): array
{
    if (!$ids) {
        return [];
    }
    $idList = implode(',', array_map('intval', $ids));
    $rows = $pdo->query(
        "SELECT ID, post_title, post_name, post_status, post_excerpt, post_content
         FROM wp_posts
         WHERE post_type = 'product' AND ID IN ({$idList})
         ORDER BY ID"
    )->fetchAll();
    $current = [];
    foreach ($rows as $row) {
        $current[(int) $row['ID']] = [
            'id' => (int) $row['ID'],
            'title' => (string) $row['post_title'],
            'slug' => (string) $row['post_name'],
            'status' => (string) $row['post_status'],
            'post_excerpt' => (string) $row['post_excerpt'],
            'post_content' => (string) $row['post_content'],
        ];
    }
    $metaKeys = [
        'rank_math_title',
        'rank_math_description',
        'rank_math_focus_keyword',
        'rank_math_canonical_url',
        '_thumbnail_id',
        '_product_image_gallery',
        '_sku',
        '_price',
        '_regular_price',
        '_sale_price',
        '_stock',
        '_stock_status',
        '_manage_stock',
    ];
    $quotedKeys = implode(',', array_map([$pdo, 'quote'], $metaKeys));
    $metaRows = $pdo->query(
        "SELECT post_id, meta_key, meta_value
         FROM wp_postmeta
         WHERE post_id IN ({$idList}) AND meta_key IN ({$quotedKeys})
         ORDER BY post_id, meta_id"
    )->fetchAll();
    foreach ($metaRows as $row) {
        $current[(int) $row['post_id']]['meta'][(string) $row['meta_key']] = (string) $row['meta_value'];
    }
    $termRows = $pdo->query(
        "SELECT tr.object_id AS product_id, t.term_id
         FROM wp_term_relationships tr
         JOIN wp_term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
         JOIN wp_terms t ON t.term_id = tt.term_id
         WHERE tr.object_id IN ({$idList}) AND tt.taxonomy = 'product_cat'
         ORDER BY tr.object_id, t.term_id"
    )->fetchAll();
    foreach ($termRows as $row) {
        $current[(int) $row['product_id']]['category_ids'][] = (int) $row['term_id'];
    }
    foreach ($current as &$row) {
        $row['meta'] = $row['meta'] ?? [];
        $row['category_ids'] = array_values(array_unique($row['category_ids'] ?? []));
        sort($row['category_ids'], SORT_NUMERIC);
    }
    unset($row);
    return $current;
}

function baselineImageIds(array $baseline): array
{
    $ids = [];
    foreach ($baseline['images'] ?? [] as $image) {
        if (!empty($image['id'])) {
            $ids[] = (int) $image['id'];
        }
    }
    return array_values(array_unique($ids));
}

function currentImageIds(array $current): array
{
    $meta = $current['meta'] ?? [];
    $ids = [];
    if (!empty($meta['_thumbnail_id'])) {
        $ids[] = (int) $meta['_thumbnail_id'];
    }
    foreach (array_filter(array_map('intval', explode(',', (string) ($meta['_product_image_gallery'] ?? '')))) as $id) {
        $ids[] = $id;
    }
    return array_values(array_unique($ids));
}

function baselineCategoryIds(array $baseline): array
{
    $ids = array_map(static fn(array $term): int => (int) $term['term_id'], $baseline['categories'] ?? []);
    $ids = array_values(array_unique($ids));
    sort($ids, SORT_NUMERIC);
    return $ids;
}

function immutableErrors(array $current, array $baseline): array
{
    $errors = [];
    foreach (['title', 'slug', 'status'] as $field) {
        if ((string) ($current[$field] ?? '') !== (string) ($baseline[$field] ?? '')) {
            $errors[] = "{$field} changed";
        }
    }
    $metaMap = [
        '_sku' => 'sku',
        '_price' => 'price',
        '_regular_price' => 'regular_price',
        '_sale_price' => 'sale_price',
        '_stock' => 'stock',
        '_stock_status' => 'stock_status',
        '_manage_stock' => 'manage_stock',
        'rank_math_canonical_url' => 'rank_math_canonical_url',
    ];
    foreach ($metaMap as $metaKey => $baselineKey) {
        if ((string) ($current['meta'][$metaKey] ?? '') !== (string) ($baseline[$baselineKey] ?? '')) {
            $errors[] = "{$baselineKey} changed";
        }
    }
    if (currentImageIds($current) !== baselineImageIds($baseline)) {
        $errors[] = 'images changed';
    }
    if (($current['category_ids'] ?? []) !== baselineCategoryIds($baseline)) {
        $errors[] = 'categories changed';
    }
    return $errors;
}

function targetFields(array $product): array
{
    return [
        'post_excerpt' => (string) $product['short_description'],
        'post_content' => (string) $product['main_content'],
        'rank_math_title' => (string) $product['seo_title'],
        'rank_math_description' => (string) $product['meta_description'],
        'rank_math_focus_keyword' => (string) $product['focus_keyword'],
    ];
}

function currentTargetFields(array $current): array
{
    return [
        'post_excerpt' => (string) ($current['post_excerpt'] ?? ''),
        'post_content' => (string) ($current['post_content'] ?? ''),
        'rank_math_title' => (string) ($current['meta']['rank_math_title'] ?? ''),
        'rank_math_description' => (string) ($current['meta']['rank_math_description'] ?? ''),
        'rank_math_focus_keyword' => (string) ($current['meta']['rank_math_focus_keyword'] ?? ''),
    ];
}

function fieldDiff(array $current, array $target): array
{
    $diff = [];
    foreach ($target as $field => $value) {
        $old = (string) ($current[$field] ?? '');
        $new = (string) $value;
        $diff[$field] = [
            'changed' => $old !== $new,
            'before_bytes' => strlen($old),
            'after_bytes' => strlen($new),
            'before_sha256' => hash('sha256', $old),
            'after_sha256' => hash('sha256', $new),
        ];
    }
    return $diff;
}

function dryRun(array $products, array $baselineProducts, string $batch): array
{
    $selected = selectedProducts($products, $batch);
    $pdo = pdo(true);
    $current = currentRows($pdo, array_keys($selected));
    $rows = [];
    $blocking = [];
    foreach ($selected as $id => $product) {
        if (!isset($current[$id], $baselineProducts[$id])) {
            $blocking[$id][] = 'missing current or baseline record';
            continue;
        }
        $errors = immutableErrors($current[$id], $baselineProducts[$id]);
        if ($errors) {
            $blocking[$id] = $errors;
        }
        $diff = fieldDiff(currentTargetFields($current[$id]), targetFields($product));
        $rows[] = [
            'product_id' => $id,
            'batch_id' => $product['batch_id'],
            'title' => $product['title'],
            'slug' => $product['slug'],
            'immutable_errors' => $errors,
            'changed_field_count' => count(array_filter($diff, static fn(array $field): bool => $field['changed'])),
            'field_diff' => $diff,
        ];
    }
    $pdo->commit();
    $report = [
        'mode' => 'dry-run',
        'created_at' => date(DATE_ATOM),
        'database' => TARGET_DB_NAME,
        'home_url' => LOCAL_HOME,
        'batch' => $batch,
        'product_count' => count($selected),
        'blocking_product_count' => count($blocking),
        'blocking' => $blocking,
        'rows' => $rows,
    ];
    $path = REPORT_DIR . '\\' . ($batch === 'all' ? 'ALL' : $batch) . '-dry-run.json';
    writeJson($path, $report);
    if ($blocking) {
        fail('Dry-run blocked by immutable-field drift; see ' . $path);
    }
    return $report;
}

function bootstrapWordPress(): void
{
    if (defined('ABSPATH')) {
        return;
    }
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['SERVER_NAME'] = 'localhost';
    $_SERVER['REQUEST_URI'] = '/hopgiayvpn/';
    $_SERVER['HTTPS'] = 'off';
    define('WP_USE_THEMES', false);
    require ROOT_DIR . '\\wp-load.php';
    if (
        !defined('DB_NAME') || TARGET_DB_NAME !== constant('DB_NAME')
        || !defined('WP_HOME') || rtrim((string) constant('WP_HOME'), '/') !== LOCAL_HOME
        || !function_exists('wp_get_environment_type') || wp_get_environment_type() !== 'local'
        || ($GLOBALS['table_prefix'] ?? '') !== TARGET_TABLE_PREFIX
    ) {
        fail('WordPress bootstrap safety check failed.');
    }
    add_filter('wp_revisions_to_keep', '__return_zero', PHP_INT_MAX);
}

function createBatchBackup(string $batch, array $selected, array $current): array
{
    $path = SOURCE_DIR . '\\batch-backups\\' . $batch . '.before.json';
    if (is_file($path)) {
        $existing = readJson($path);
        if ((int) ($existing['product_count'] ?? 0) !== count($selected)) {
            fail("Existing {$batch} backup has the wrong product count.");
        }
        return $existing;
    }
    $rows = [];
    foreach ($selected as $id => $product) {
        $rows[] = [
            'product_id' => $id,
            'title' => $current[$id]['title'],
            'slug' => $current[$id]['slug'],
            'status' => $current[$id]['status'],
            'fields' => currentTargetFields($current[$id]),
        ];
    }
    $backup = [
        'schema_version' => 1,
        'created_at' => date(DATE_ATOM),
        'database' => TARGET_DB_NAME,
        'batch' => $batch,
        'product_count' => count($rows),
        'products' => $rows,
    ];
    writeJson($path, $backup);
    $backup['path'] = $path;
    $backup['sha256'] = hash_file('sha256', $path);
    return $backup;
}

function updateProductFields(int $id, array $fields): void
{
    $result = wp_update_post(
        [
            'ID' => $id,
            'post_excerpt' => (string) $fields['post_excerpt'],
            'post_content' => (string) $fields['post_content'],
        ],
        true
    );
    if (is_wp_error($result) || (int) $result !== $id) {
        $message = is_wp_error($result) ? $result->get_error_message() : 'unexpected wp_update_post result';
        throw new RuntimeException("wp_update_post failed for {$id}: {$message}");
    }
    foreach ([
        'rank_math_title',
        'rank_math_description',
        'rank_math_focus_keyword',
    ] as $metaKey) {
        $value = (string) $fields[$metaKey];
        if ($value === '') {
            delete_post_meta($id, $metaKey);
        } else {
            update_post_meta($id, $metaKey, $value);
        }
    }
    clean_post_cache($id);
}

function rollbackOne(int $id, array $fields): void
{
    updateProductFields($id, $fields);
}

function applyBatch(
    string $batch,
    array $products,
    array $baselineProducts
): array {
    if ($batch === 'all') {
        fail('applyBatch requires one concrete batch.');
    }
    $selected = selectedProducts($products, $batch);
    dryRun($products, $baselineProducts, $batch);
    $pdo = pdo(true);
    $current = currentRows($pdo, array_keys($selected));
    $pdo->commit();
    $backup = createBatchBackup($batch, $selected, $current);
    $backupById = [];
    foreach ($backup['products'] as $row) {
        $backupById[(int) $row['product_id']] = $row['fields'];
    }

    bootstrapWordPress();
    $applied = [];
    $failed = [];
    foreach ($selected as $id => $product) {
        try {
            updateProductFields($id, targetFields($product));
            $storedPost = get_post($id);
            $stored = [
                'post_excerpt' => (string) $storedPost->post_excerpt,
                'post_content' => (string) $storedPost->post_content,
                'rank_math_title' => (string) get_post_meta($id, 'rank_math_title', true),
                'rank_math_description' => (string) get_post_meta($id, 'rank_math_description', true),
                'rank_math_focus_keyword' => (string) get_post_meta($id, 'rank_math_focus_keyword', true),
            ];
            if ($stored !== targetFields($product)) {
                throw new RuntimeException("post-write verification failed for {$id}");
            }
            $applied[] = $id;
        } catch (Throwable $error) {
            try {
                if (isset($backupById[$id])) {
                    rollbackOne($id, $backupById[$id]);
                }
            } catch (Throwable $rollbackError) {
                fail("Product {$id} failed and rollback also failed: {$rollbackError->getMessage()}");
            }
            $failed[$id] = $error->getMessage();
        }
    }
    wp_cache_flush();
    if (class_exists('LiteSpeed_Cache_API') && method_exists('LiteSpeed_Cache_API', 'purge_all')) {
        LiteSpeed_Cache_API::purge_all();
    }

    $report = [
        'mode' => 'apply',
        'created_at' => date(DATE_ATOM),
        'database' => TARGET_DB_NAME,
        'batch' => $batch,
        'product_count' => count($selected),
        'applied_count' => count($applied),
        'failed_count' => count($failed),
        'applied_product_ids' => $applied,
        'failed_products' => $failed,
        'batch_backup_path' => $backup['path'] ?? SOURCE_DIR . '\\batch-backups\\' . $batch . '.before.json',
        'batch_backup_sha256' => $backup['sha256'] ?? hash_file('sha256', SOURCE_DIR . '\\batch-backups\\' . $batch . '.before.json'),
    ];
    writeJson(REPORT_DIR . "\\{$batch}-apply.json", $report);
    return $report;
}

function qaProducts(array $selected, array $baselineProducts, array $allowFailed = []): array
{
    $pdo = pdo(true);
    $current = currentRows($pdo, array_keys($selected));
    $pdo->commit();
    $rows = [];
    $failures = [];
    foreach ($selected as $id => $product) {
        if (isset($allowFailed[$id])) {
            continue;
        }
        $checks = [
            'record_exists' => isset($current[$id]),
            'immutable_fields' => isset($current[$id]) && immutableErrors($current[$id], $baselineProducts[$id]) === [],
            'five_fields_exact' => isset($current[$id]) && currentTargetFields($current[$id]) === targetFields($product),
            'starts_with_h2' => preg_match('/^\s*<h2\b/i', (string) ($current[$id]['post_content'] ?? '')) === 1,
            'contains_no_h1' => preg_match('/<h1\b/i', (string) ($current[$id]['post_content'] ?? '')) !== 1,
            'contains_no_marker_or_shortcode' => preg_match('/<!--|\[(?:\/?)[A-Za-z_][^\]]*\]/', (string) ($current[$id]['post_content'] ?? '')) !== 1,
            'contains_no_banned_claim' => preg_match(
                '/\b(?:fixed MOQ|free sample|free shipping|FSC|biodegradable|compostable|soy[- ]based ink|100% sustainable|factory capacity|years of experience|guarantee|verified packaging client)\b/i',
                (string) ($current[$id]['post_excerpt'] ?? '') . ' ' . (string) ($current[$id]['post_content'] ?? '')
            ) !== 1,
        ];
        foreach ($product['internal_links'] as $link) {
            if (!str_contains((string) ($current[$id]['post_content'] ?? ''), 'href="' . $link['target_url'] . '"')) {
                $checks['all_planned_internal_links'] = false;
                break;
            }
            $checks['all_planned_internal_links'] = true;
        }
        if (!isset($checks['all_planned_internal_links'])) {
            $checks['all_planned_internal_links'] = true;
        }
        $passed = !in_array(false, $checks, true);
        if (!$passed) {
            $failures[$id] = array_keys(array_filter($checks, static fn(bool $value): bool => !$value));
        }
        $rows[] = [
            'product_id' => $id,
            'batch_id' => $product['batch_id'],
            'passed' => $passed,
            'checks' => $checks,
        ];
    }
    return [
        'product_count' => count($selected) - count($allowFailed),
        'passed_count' => count($selected) - count($allowFailed) - count($failures),
        'failed_count' => count($failures),
        'failures' => $failures,
        'rows' => $rows,
    ];
}

function qaBatch(string $batch, array $products, array $baselineProducts, array $allowFailed = []): array
{
    $selected = selectedProducts($products, $batch);
    $qa = qaProducts($selected, $baselineProducts, $allowFailed);
    $report = [
        'mode' => 'qa',
        'created_at' => date(DATE_ATOM),
        'database' => TARGET_DB_NAME,
        'batch' => $batch,
    ] + $qa;
    writeJson(REPORT_DIR . "\\{$batch}-qa.json", $report);
    return $report;
}

function rollbackBatch(string $batch): array
{
    if (!preg_match('/^BATCH-(?:0[1-9]|1[0-7])$/', $batch)) {
        fail("Invalid rollback batch: {$batch}");
    }
    $path = SOURCE_DIR . '\\batch-backups\\' . $batch . '.before.json';
    $backup = readJson($path);
    bootstrapWordPress();
    $restored = [];
    foreach ($backup['products'] as $row) {
        $id = (int) $row['product_id'];
        rollbackOne($id, $row['fields']);
        $restored[] = $id;
    }
    wp_cache_flush();
    $report = [
        'mode' => 'rollback',
        'created_at' => date(DATE_ATOM),
        'database' => TARGET_DB_NAME,
        'batch' => $batch,
        'restored_count' => count($restored),
        'restored_product_ids' => $restored,
        'backup_sha256' => hash_file('sha256', $path),
    ];
    writeJson(REPORT_DIR . "\\{$batch}-rollback.json", $report);
    return $report;
}

function applyAll(array $products, array $baselineProducts): array
{
    $batches = [];
    foreach ($products as $product) {
        $batches[(string) $product['batch_id']] = true;
    }
    $batchIds = array_keys($batches);
    sort($batchIds, SORT_STRING);
    if (count($batchIds) !== 17) {
        fail('Expected 17 batches.');
    }

    $run = [
        'mode' => 'apply-all',
        'started_at' => date(DATE_ATOM),
        'database' => TARGET_DB_NAME,
        'batches' => [],
        'applied_products' => 0,
        'blocked_products' => [],
        'stopped_for_sitewide_failure' => false,
    ];
    foreach ($batchIds as $batch) {
        $apply = applyBatch($batch, $products, $baselineProducts);
        $failedIds = array_fill_keys(array_map('intval', array_keys($apply['failed_products'])), true);
        if ($apply['applied_count'] === 0 && $apply['failed_count'] > 0) {
            $run['stopped_for_sitewide_failure'] = true;
            $run['batches'][$batch] = ['apply' => $apply, 'qa' => null];
            writeJson(REPORT_DIR . '\\apply-all-run.json', $run);
            fail("All products failed in {$batch}; stopped as site-wide risk.");
        }
        $qa = qaBatch($batch, $products, $baselineProducts, $failedIds);
        if ($qa['failed_count'] > 0) {
            rollbackBatch($batch);
            $run['stopped_for_sitewide_failure'] = true;
            $run['batches'][$batch] = ['apply' => $apply, 'qa' => $qa, 'rolled_back' => true];
            writeJson(REPORT_DIR . '\\apply-all-run.json', $run);
            fail("Batch QA failed for {$batch}; batch rolled back.");
        }
        $run['batches'][$batch] = ['apply' => $apply, 'qa' => $qa, 'rolled_back' => false];
        $run['applied_products'] += (int) $apply['applied_count'];
        foreach ($apply['failed_products'] as $id => $message) {
            $run['blocked_products'][(string) $id] = $message;
        }
        writeJson(REPORT_DIR . '\\apply-all-run.json', $run);
    }
    $run['completed_at'] = date(DATE_ATOM);
    writeJson(REPORT_DIR . '\\apply-all-run.json', $run);
    return $run;
}

function cliBatch(array $argv): string
{
    foreach ($argv as $arg) {
        if (str_starts_with($arg, '--batch=')) {
            return substr($arg, strlen('--batch='));
        }
    }
    return 'all';
}

ensureDir(REPORT_DIR);
[$manifest, $products, $baselineProducts] = loadState();
$command = $argv[1] ?? '';
$batch = cliBatch($argv);

$result = match ($command) {
    'dry-run' => dryRun($products, $baselineProducts, $batch),
    'apply-batch' => applyBatch($batch, $products, $baselineProducts),
    'qa-batch' => qaBatch($batch, $products, $baselineProducts),
    'rollback-batch' => rollbackBatch($batch),
    'apply-all' => applyAll($products, $baselineProducts),
    default => fail('Usage: php importer.php dry-run [--batch=BATCH-01|all] | apply-batch --batch=BATCH-01 | qa-batch --batch=BATCH-01 | rollback-batch --batch=BATCH-01 | apply-all'),
};

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
