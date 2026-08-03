<?php
/**
 * Plugin Name: HopgiayVPN Product Long Description Sync v3
 * Description: Admin tool for the approved 179-product v3 long-description release.
 */

if (!defined('ABSPATH')) {
    exit;
}

const HGVN_SEO_SYNC_PAYLOAD = ABSPATH . 'seo-content/product-seo-package-20260801/deploy-payload.json';
const HGVN_SEO_SYNC_RELEASE = 'product-long-description-v3-2026-08-03';

function hgvn_seo_sync_canonical_content($content)
{
    $value = str_replace(["\r\n", "\r"], "\n", (string) $content);
    $value = preg_replace('/\s(?:srcset|sizes|decoding)="[^"]*"/i', '', $value);
    $value = preg_replace_callback(
        '/\sstyle="([^"]*)"/i',
        static function ($match) {
            return ' style="' . rtrim(preg_replace('/\s+/', '', $match[1]), ';') . '"';
        },
        $value
    );
    return html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function hgvn_seo_sync_content_equal($actual, $expected)
{
    return hgvn_seo_sync_canonical_content($actual) === hgvn_seo_sync_canonical_content($expected);
}

function hgvn_seo_sync_current_content($id)
{
    $post = get_post($id);
    return $post ? (string) $post->post_content : null;
}

function hgvn_seo_sync_load_payload()
{
    if (!is_file(HGVN_SEO_SYNC_PAYLOAD)) {
        return [null, 'Không tìm thấy payload long description v3 trong bản deploy.'];
    }
    $payload = json_decode((string) file_get_contents(HGVN_SEO_SYNC_PAYLOAD), true);
    if (!is_array($payload)
        || (int) ($payload['schema_version'] ?? 0) !== 2
        || ($payload['release'] ?? '') !== HGVN_SEO_SYNC_RELEASE
        || ($payload['environment'] ?? '') !== 'production'
        || ($payload['live_home'] ?? '') !== 'https://hopgiayvpn.com'
        || (int) ($payload['product_count'] ?? 0) !== 179
        || ($payload['fields'] ?? null) !== ['post_content']
        || !is_array($payload['products'] ?? null)
        || count($payload['products']) !== 179
    ) {
        return [null, 'Payload v3 không hợp lệ, sai phạm vi hoặc không đủ 179 sản phẩm.'];
    }

    $products = [];
    foreach ($payload['products'] as $product) {
        $id = (int) ($product['id'] ?? 0);
        if ($id <= 0 || isset($products[$id])) {
            return [null, 'Payload có Product ID thiếu hoặc trùng.'];
        }
        foreach (['title', 'slug', 'status', 'post_content', 'post_content_sha256'] as $field) {
            if (!array_key_exists($field, $product) || !is_string($product[$field])) {
                return [null, "Product {$id} thiếu trường {$field}."];
            }
        }
        if ($product['status'] !== 'publish') {
            return [null, "Product {$id} trong payload không ở trạng thái publish."];
        }
        if (!hash_equals($product['post_content_sha256'], hash('sha256', $product['post_content']))) {
            return [null, "Checksum content của Product {$id} không hợp lệ."];
        }
        $encoded = wp_json_encode($product, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (preg_match('/(?:localhost|127\.0\.0\.1|staging)/i', (string) $encoded)) {
            return [null, "Product {$id} còn URL local hoặc staging."];
        }
        $products[$id] = $product;
    }
    ksort($products, SORT_NUMERIC);
    return [$products, null];
}

function hgvn_seo_sync_preflight($products)
{
    $home = untrailingslashit((string) home_url('/'));
    $host = strtolower((string) parse_url($home, PHP_URL_HOST));
    if (!in_array($host, ['hopgiayvpn.com', 'www.hopgiayvpn.com'], true)) {
        return ["Từ chối chạy trên URL không phải production: {$home}", null];
    }

    $failures = [];
    $changed = 0;
    foreach ($products as $id => $product) {
        $post = get_post($id);
        $row = [];
        if (!$post) {
            $row[] = 'không tồn tại';
        } else {
            if ($post->post_type !== 'product') {
                $row[] = 'sai post type';
            }
            if ($post->post_status !== 'publish') {
                $row[] = 'chưa publish';
            }
            if ((string) $post->post_name !== $product['slug']) {
                $row[] = 'sai slug';
            }
            if ((string) $post->post_title !== $product['title']) {
                $row[] = 'sai title';
            }
            if (!hgvn_seo_sync_content_equal($post->post_content, $product['post_content'])) {
                $changed++;
            }
        }
        if ($row) {
            $failures[$id] = implode(', ', $row);
        }
    }
    if ($failures) {
        return ['Preflight lỗi: ' . wp_json_encode($failures, JSON_UNESCAPED_UNICODE), null];
    }
    return [null, [
        'changed_count' => $changed,
        'unchanged_count' => count($products) - $changed,
    ]];
}

function hgvn_seo_sync_write_content($id, $content)
{
    $result = wp_update_post([
        'ID' => (int) $id,
        'post_content' => wp_slash($content),
    ], true);
    if (is_wp_error($result) || (int) $result !== (int) $id) {
        return is_wp_error($result) ? $result->get_error_message() : 'wp_update_post trả về kết quả không hợp lệ';
    }
    clean_post_cache($id);
    return null;
}

function hgvn_seo_sync_restore($before, $ids)
{
    $errors = [];
    foreach ($ids as $id) {
        $error = hgvn_seo_sync_write_content($id, $before[$id]);
        if ($error) {
            $errors[$id] = $error;
        }
    }
    return $errors;
}

function hgvn_seo_sync_verify($products)
{
    $failures = [];
    foreach ($products as $id => $product) {
        $actual = hgvn_seo_sync_current_content($id);
        if ($actual === null || !hgvn_seo_sync_content_equal($actual, $product['post_content'])) {
            $failures[$id] = 'post_content_mismatch';
        }
    }
    return $failures;
}

function hgvn_seo_sync_apply($products)
{
    $before = [];
    $applied = [];
    foreach ($products as $id => $product) {
        $current = hgvn_seo_sync_current_content($id);
        if ($current !== null && hgvn_seo_sync_content_equal($current, $product['post_content'])) {
            continue;
        }
        $before[$id] = (string) $current;
        $error = hgvn_seo_sync_write_content($id, $product['post_content']);
        if ($error) {
            $rollbackErrors = hgvn_seo_sync_restore($before, array_keys($before));
            return ["Không cập nhật được Product {$id}: {$error}. Rollback: " . wp_json_encode($rollbackErrors), 0];
        }
        $applied[] = $id;
    }

    $failures = hgvn_seo_sync_verify($products);
    if ($failures) {
        $rollbackErrors = hgvn_seo_sync_restore($before, $applied);
        return ['QA sau cập nhật lỗi: ' . wp_json_encode($failures) . '. Rollback: ' . wp_json_encode($rollbackErrors), 0];
    }
    wp_cache_flush();
    return [null, count($applied)];
}

function hgvn_seo_sync_admin_menu()
{
    add_management_page(
        'Product Long Description Sync v3',
        'SEO Content Sync',
        'manage_options',
        'hopgiayvpn-product-seo-sync',
        'hgvn_seo_sync_render_admin_page'
    );
}
add_action('admin_menu', 'hgvn_seo_sync_admin_menu');

function hgvn_seo_sync_render_admin_page()
{
    if (!current_user_can('manage_options')) {
        wp_die('Bạn không có quyền thực hiện thao tác này.');
    }

    $message = null;
    $success = false;
    $stats = null;
    $appliedCount = null;
    [$products, $loadError] = hgvn_seo_sync_load_payload();
    if ($loadError) {
        $message = $loadError;
    } else {
        [$preflightError, $stats] = hgvn_seo_sync_preflight($products);
        if ($preflightError) {
            $message = $preflightError;
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('hgvn_seo_sync_apply');
            [$applyError, $appliedCount] = hgvn_seo_sync_apply($products);
            if ($applyError) {
                $message = $applyError;
            } else {
                $success = true;
            }
        }
    }
    ?>
    <div class="wrap">
        <h1>SEO Content Sync — Long Description v3</h1>
        <?php if ($message): ?>
            <div class="notice notice-error is-dismissible"><p><?php echo esc_html($message); ?></p></div>
        <?php elseif ($success): ?>
            <div class="notice notice-success"><p><?php echo esc_html("Đồng bộ thành công {$appliedCount}/179 sản phẩm; toàn bộ 179/179 đã qua QA."); ?></p></div>
        <?php else: ?>
            <div class="notice notice-success"><p><?php echo esc_html("Preflight đạt: {$stats['changed_count']} sản phẩm cần cập nhật, {$stats['unchanged_count']} sản phẩm đã đúng v3."); ?></p></div>
        <?php endif; ?>
        <p>Release: <code><?php echo esc_html(HGVN_SEO_SYNC_RELEASE); ?></code></p>
        <p>Tool này chỉ thay <code>wp_posts.post_content</code> của đúng 179 sản phẩm. Không thay mô tả ngắn, SEO, tiêu đề, slug, ảnh, danh mục, SKU, giá hoặc tồn kho.</p>
        <?php if (!$message && !$success): ?>
            <form method="post">
                <?php wp_nonce_field('hgvn_seo_sync_apply'); ?>
                <p><button type="submit" class="button button-primary">Đồng bộ 179 long description v3</button></p>
            </form>
        <?php endif; ?>
    </div>
    <?php
}
