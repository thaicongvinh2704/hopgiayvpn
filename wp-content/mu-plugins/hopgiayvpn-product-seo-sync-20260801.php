<?php
/**
 * Plugin Name: HopgiayVPN Product SEO Sync 2026-08-01
 * Description: Admin button for the production-only 179-product SEO content release.
 */

if (!defined('ABSPATH')) {
    exit;
}

const HGVN_SEO_SYNC_PAYLOAD = ABSPATH . 'seo-content/product-seo-package-20260801/deploy-payload.json';
const HGVN_SEO_SYNC_META_KEYS = [
    'rank_math_title',
    'rank_math_description',
    'rank_math_focus_keyword',
];

function hgvn_seo_sync_canonical($fields)
{
    foreach (['post_excerpt', 'post_content'] as $field) {
        $value = str_replace(["\r\n", "\r"], "\n", (string) ($fields[$field] ?? ''));
        if ($field === 'post_excerpt') {
            $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } else {
            $value = preg_replace('/\s(?:srcset|sizes|decoding)="[^"]*"/i', '', $value);
            $value = preg_replace_callback(
                '/\sstyle="([^"]*)"/i',
                static function ($match) {
                    return ' style="' . rtrim(preg_replace('/\s+/', '', $match[1]), ';') . '"';
                },
                $value
            );
        }
        $fields[$field] = $value;
    }
    return $fields;
}

function hgvn_seo_sync_current_fields($id)
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

function hgvn_seo_sync_load_payload()
{
    if (!is_file(HGVN_SEO_SYNC_PAYLOAD)) {
        return [null, 'Không tìm thấy payload content SEO trong bản deploy.'];
    }
    $payload = json_decode((string) file_get_contents(HGVN_SEO_SYNC_PAYLOAD), true);
    if (!is_array($payload)
        || ($payload['environment'] ?? '') !== 'production'
        || ($payload['live_home'] ?? '') !== 'https://hopgiayvpn.com'
        || (int) ($payload['product_count'] ?? 0) !== 179
        || !is_array($payload['products'] ?? null)
        || count($payload['products']) !== 179
    ) {
        return [null, 'Payload không hợp lệ hoặc không đủ 179 sản phẩm.'];
    }

    $products = [];
    foreach ($payload['products'] as $product) {
        $id = (int) ($product['id'] ?? 0);
        if ($id <= 0 || isset($products[$id])) {
            return [null, 'Payload có Product ID thiếu hoặc trùng.'];
        }
        foreach (['title', 'slug', 'post_excerpt', 'post_content', 'rank_math_title', 'rank_math_description', 'rank_math_focus_keyword'] as $field) {
            if (!array_key_exists($field, $product) || !is_string($product[$field])) {
                return [null, "Product {$id} thiếu trường {$field}. "];
            }
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
        return "Từ chối chạy trên URL không phải production: {$home}";
    }

    $failures = [];
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
        }
        if ($row) {
            $failures[$id] = implode(', ', $row);
        }
    }
    return $failures ? 'Preflight lỗi: ' . wp_json_encode($failures, JSON_UNESCAPED_UNICODE) : null;
}

function hgvn_seo_sync_apply($products)
{
    foreach ($products as $id => $product) {
        $result = wp_update_post([
            'ID' => $id,
            'post_excerpt' => wp_slash($product['post_excerpt']),
            'post_content' => wp_slash($product['post_content']),
        ], true);
        if (is_wp_error($result) || (int) $result !== (int) $id) {
            return "Không cập nhật được Product {$id}.";
        }
        foreach (HGVN_SEO_SYNC_META_KEYS as $key) {
            if ($product[$key] === '') {
                delete_post_meta($id, $key);
            } else {
                update_post_meta($id, $key, $product[$key]);
            }
        }
        clean_post_cache($id);
    }

    $failures = [];
    foreach ($products as $id => $product) {
        if (hgvn_seo_sync_canonical(hgvn_seo_sync_current_fields($id)) !== hgvn_seo_sync_canonical([
            'post_excerpt' => $product['post_excerpt'],
            'post_content' => $product['post_content'],
            'rank_math_title' => $product['rank_math_title'],
            'rank_math_description' => $product['rank_math_description'],
            'rank_math_focus_keyword' => $product['rank_math_focus_keyword'],
        ])) {
            $failures[$id] = 'five_fields_mismatch';
        }
    }
    return $failures ? 'QA sau cập nhật lỗi: ' . wp_json_encode($failures, JSON_UNESCAPED_UNICODE) : null;
}

function hgvn_seo_sync_admin_menu()
{
    add_management_page(
        'Product SEO Content Sync',
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_admin_referer('hgvn_seo_sync_apply');
        [$products, $loadError] = hgvn_seo_sync_load_payload();
        if ($loadError) {
            $message = $loadError;
        } else {
            $message = hgvn_seo_sync_preflight($products);
            if (!$message) {
                $message = hgvn_seo_sync_apply($products);
                $success = !$message;
            }
        }
    } else {
        [$products, $loadError] = hgvn_seo_sync_load_payload();
        if ($loadError) {
            $message = $loadError;
        } else {
            $message = hgvn_seo_sync_preflight($products);
        }
    }
    ?>
    <div class="wrap">
        <h1>SEO Content Sync</h1>
        <?php if ($message): ?>
            <div class="notice notice-<?php echo $success ? 'success' : 'error'; ?> is-dismissible"><p><?php echo esc_html($message); ?></p></div>
        <?php else: ?>
            <div class="notice notice-success"><p>Preflight đạt: sẵn sàng đồng bộ 179 sản phẩm.</p></div>
        <?php endif; ?>
        <p>Thao tác này chỉ cập nhật <code>post_excerpt</code>, <code>post_content</code> và 3 trường Rank Math. Không tạo/xóa sản phẩm.</p>
        <?php if (!$message || $success): ?>
            <form method="post">
                <?php wp_nonce_field('hgvn_seo_sync_apply'); ?>
                <p><button type="submit" class="button button-primary" <?php disabled($success); ?>><?php echo $success ? 'Đã đồng bộ 179 sản phẩm' : 'Đồng bộ content SEO'; ?></button></p>
            </form>
        <?php endif; ?>
    </div>
    <?php
}
