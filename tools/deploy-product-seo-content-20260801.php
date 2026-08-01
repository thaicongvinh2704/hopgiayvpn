<?php
declare(strict_types=1);

/**
 * Production content-only migration for the 2026-08-01 SEO release.
 *
 * Commands from the WordPress root:
 *   php tools/deploy-product-seo-content-20260801.php dry-run
 *   php tools/deploy-product-seo-content-20260801.php apply
 *   php tools/deploy-product-seo-content-20260801.php qa
 *
 * Only post_excerpt, post_content and three Rank Math meta fields are changed.
 */

const DEPLOY_ROOT = __DIR__ . '/..';
const DEPLOY_PAYLOAD = DEPLOY_ROOT . '/seo-content/product-seo-package-20260801/deploy-payload.json';
const DEPLOY_REQUIRED_PRODUCTS = 179;
const DEPLOY_META_KEYS = [
    'rank_math_title',
    'rank_math_description',
    'rank_math_focus_keyword',
];

function deployFail(string $message): never
{
    fwrite(STDERR, "ERROR: {$message}\n");
    exit(1);
}

function deployCanonical(array $fields): array
{
    $canonical = $fields;
    foreach (['post_excerpt', 'post_content'] as $field) {
        $value = str_replace(["\r\n", "\r"], "\n", (string) ($canonical[$field] ?? ''));
        if ($field === 'post_excerpt') {
            $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } else {
            $value = preg_replace('/\s(?:srcset|sizes|decoding)="[^"]*"/i', '', $value) ?? $value;
            $value = preg_replace_callback(
                '/\sstyle="([^"]*)"/i',
                static fn(array $match): string => ' style="' . rtrim(preg_replace('/\s+/', '', $match[1]) ?? $match[1], ';') . '"',
                $value
            ) ?? $value;
        }
        $canonical[$field] = $value;
    }
    return $canonical;
}

function deployFieldsEqual(array $actual, array $expected): bool
{
    return deployCanonical($actual) === deployCanonical($expected);
}

function deployCurrentFields(int $id): array
{
    $post = get_post($id);
    if (!$post) {
        return [];
    }
    return [
        'post_excerpt' => (string) $post->post_excerpt,
        'post_content' => (string) $post->post_content,
        'rank_math_title' => (string) get_post_meta($id, 'rank_math_title', true),
        'rank_math_description' => (string) get_post_meta($id, 'rank_math_description', true),
        'rank_math_focus_keyword' => (string) get_post_meta($id, 'rank_math_focus_keyword', true),
    ];
}

function deployValidatePayload(array $payload): array
{
    if (($payload['environment'] ?? '') !== 'production'
        || ($payload['live_home'] ?? '') !== 'https://hopgiayvpn.com'
        || (int) ($payload['product_count'] ?? 0) !== DEPLOY_REQUIRED_PRODUCTS
    ) {
        deployFail('Payload environment, live URL or product count is invalid.');
    }
    $products = $payload['products'] ?? null;
    if (!is_array($products) || count($products) !== DEPLOY_REQUIRED_PRODUCTS) {
        deployFail('Payload must contain exactly 179 products.');
    }

    $indexed = [];
    foreach ($products as $product) {
        if (!is_array($product)) {
            deployFail('Malformed product row in payload.');
        }
        $id = (int) ($product['id'] ?? 0);
        if ($id <= 0 || isset($indexed[$id])) {
            deployFail('Missing or duplicate Product ID in payload.');
        }
        foreach (['title', 'slug', 'post_excerpt', 'post_content', 'rank_math_title', 'rank_math_description', 'rank_math_focus_keyword'] as $field) {
            if (!array_key_exists($field, $product) || !is_string($product[$field])) {
                deployFail("Product {$id} is missing field {$field}.");
            }
        }
        $serialized = json_encode($product, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($serialized === false || preg_match('/(?:localhost|127\.0\.0\.1|staging)/i', $serialized)) {
            deployFail("Product {$id} contains a local or staging URL.");
        }
        $indexed[$id] = $product;
    }
    ksort($indexed, SORT_NUMERIC);
    return $indexed;
}

function deployPreflight(array $products): array
{
    $home = untrailingslashit((string) home_url('/'));
    $host = strtolower((string) parse_url($home, PHP_URL_HOST));
    if (!in_array($host, ['hopgiayvpn.com', 'www.hopgiayvpn.com'], true)) {
        deployFail("Refusing to run on non-production home URL: {$home}");
    }

    $failures = [];
    foreach ($products as $id => $product) {
        $post = get_post($id);
        $rowFailures = [];
        if (!$post) {
            $rowFailures[] = 'record_missing';
        } else {
            if ($post->post_type !== 'product') {
                $rowFailures[] = 'post_type_mismatch';
            }
            if ($post->post_status !== 'publish') {
                $rowFailures[] = 'post_status_not_publish';
            }
            if ((string) $post->post_name !== $product['slug']) {
                $rowFailures[] = 'slug_mismatch';
            }
            if ((string) $post->post_title !== $product['title']) {
                $rowFailures[] = 'title_mismatch';
            }
        }
        if ($rowFailures) {
            $failures[$id] = $rowFailures;
        }
    }
    if ($failures) {
        deployFail('Preflight failed: ' . json_encode($failures, JSON_UNESCAPED_SLASHES));
    }
    return [
        'home_url' => $home,
        'product_count' => count($products),
        'preflight' => 'passed',
    ];
}

function deployApply(int $id, array $product): void
{
    $result = wp_update_post([
        'ID' => $id,
        'post_excerpt' => wp_slash($product['post_excerpt']),
        'post_content' => wp_slash($product['post_content']),
    ], true);
    if (is_wp_error($result) || (int) $result !== $id) {
        deployFail('wp_update_post failed for Product ' . $id . ': ' . (is_wp_error($result) ? $result->get_error_message() : 'unexpected result'));
    }
    foreach (DEPLOY_META_KEYS as $key) {
        if ($product[$key] === '') {
            delete_post_meta($id, $key);
        } else {
            update_post_meta($id, $key, $product[$key]);
        }
    }
    clean_post_cache($id);
}

function deployVerify(array $products): array
{
    $failures = [];
    foreach ($products as $id => $product) {
        $actual = deployCurrentFields((int) $id);
        if (!deployFieldsEqual($actual, $product)) {
            $failures[$id] = 'five_fields_mismatch';
        }
    }
    return $failures;
}

if (!is_file(DEPLOY_ROOT . '/wp-load.php')) {
    deployFail('Run this command from the WordPress installation root.');
}
if (!is_file(DEPLOY_PAYLOAD)) {
    deployFail('Missing deploy payload: ' . DEPLOY_PAYLOAD);
}

require_once DEPLOY_ROOT . '/wp-load.php';
$payload = json_decode((string) file_get_contents(DEPLOY_PAYLOAD), true);
if (!is_array($payload)) {
    deployFail('Invalid JSON deploy payload.');
}
$products = deployValidatePayload($payload);
$preflight = deployPreflight($products);
$command = strtolower((string) ($argv[1] ?? 'dry-run'));

if ($command === 'dry-run') {
    echo json_encode([
        'mode' => 'production-content-only-dry-run',
        'status' => 'ready',
        'changed_fields' => ['post_excerpt', 'post_content', ...DEPLOY_META_KEYS],
        'preflight' => $preflight,
        'product_count' => count($products),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(0);
}

if ($command === 'apply') {
    foreach ($products as $id => $product) {
        deployApply((int) $id, $product);
    }
    $failures = deployVerify($products);
    if ($failures) {
        deployFail('Post-apply QA failed: ' . json_encode($failures, JSON_UNESCAPED_SLASHES));
    }
    echo json_encode([
        'mode' => 'production-content-only-apply',
        'status' => 'passed',
        'applied_count' => count($products),
        'failed_count' => 0,
        'preflight' => $preflight,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(0);
}

if ($command === 'qa') {
    $failures = deployVerify($products);
    echo json_encode([
        'mode' => 'production-content-only-qa',
        'status' => $failures ? 'failed' : 'passed',
        'product_count' => count($products),
        'passed_count' => count($products) - count($failures),
        'failed_count' => count($failures),
        'failures' => $failures,
        'preflight' => $preflight,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit($failures ? 1 : 0);
}

deployFail('Unknown command. Use dry-run, apply or qa.');
