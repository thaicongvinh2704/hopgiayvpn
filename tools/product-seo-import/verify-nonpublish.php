<?php
/**
 * Verify that draft/trash products were not changed by the 179-product import.
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$inventoryPath = $root . '/artifacts/product-seo-audit-v1/product-inventory.csv';
$outputPath = $root . '/artifacts/product-seo-final-v1/nonpublish-qa.json';

function readInventory(string $path): array
{
    $handle = fopen($path, 'rb');
    if (!$handle) {
        throw new RuntimeException("Cannot read {$path}");
    }
    $headers = fgetcsv($handle);
    $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]) ?? (string) $headers[0];
    $rows = [];
    while (($values = fgetcsv($handle)) !== false) {
        $values = array_pad($values, count($headers), '');
        $rows[] = array_combine($headers, array_slice($values, 0, count($headers)));
    }
    fclose($handle);
    return $rows;
}

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/hopgiayvpn/';
$_SERVER['HTTPS'] = 'off';
require $root . '/wp-load.php';

if ('hopgiayvpnmoi' !== DB_NAME) {
    throw new RuntimeException('Unexpected database.');
}

$rows = array_values(array_filter(
    readInventory($inventoryPath),
    static fn(array $row): bool => 'publish' !== ($row['status'] ?? '')
));
$results = [];
$failures = [];
foreach ($rows as $row) {
    $id = (int) $row['id'];
    $post = get_post($id);
    $checks = [
        'record_exists' => null !== $post,
        'title_unchanged' => $post && (string) $post->post_title === (string) $row['title'],
        'slug_unchanged' => $post && (string) $post->post_name === (string) $row['slug'],
        'status_unchanged' => $post && (string) $post->post_status === (string) $row['status'],
        'excerpt_unchanged' => $post && (string) $post->post_excerpt === (string) $row['short_description_html'],
        'content_unchanged' => $post && (string) $post->post_content === (string) $row['main_content_html'],
        'seo_title_unchanged' => (string) get_post_meta($id, 'rank_math_title', true) === (string) $row['rank_math_seo_title'],
        'meta_description_unchanged' => (string) get_post_meta($id, 'rank_math_description', true) === (string) $row['rank_math_meta_description'],
        'focus_keyword_unchanged' => (string) get_post_meta($id, 'rank_math_focus_keyword', true) === (string) $row['rank_math_focus_keyword'],
        'canonical_unchanged' => (string) get_post_meta($id, 'rank_math_canonical_url', true) === (string) $row['rank_math_canonical'],
    ];
    $passed = !in_array(false, $checks, true);
    if (!$passed) {
        $failures[$id] = array_keys(array_filter($checks, static fn(bool $value): bool => !$value));
    }
    $results[] = [
        'product_id' => $id,
        'status' => $row['status'],
        'passed' => $passed,
        'checks' => $checks,
    ];
}

$payload = [
    'created_at_utc' => gmdate('c'),
    'database' => DB_NAME,
    'prefix' => $GLOBALS['wpdb']->prefix,
    'nonpublish_products_in_phase_one_inventory' => count($rows),
    'draft_count' => count(array_filter($rows, static fn(array $row): bool => 'draft' === $row['status'])),
    'trash_count' => count(array_filter($rows, static fn(array $row): bool => 'trash' === $row['status'])),
    'passed_count' => count($rows) - count($failures),
    'failed_count' => count($failures),
    'failures' => $failures,
    'rows' => $results,
];
file_put_contents($outputPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);

echo json_encode([
    'ok' => !$failures,
    'nonpublish_products' => count($rows),
    'draft' => $payload['draft_count'],
    'trash' => $payload['trash_count'],
    'passed' => $payload['passed_count'],
    'failed' => $payload['failed_count'],
    'report' => $outputPath,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
exit($failures ? 3 : 0);
