<?php
/** Local integration test: create isolated draft fixtures, verify, test retries/conflicts, then remove only fixtures. */
require __DIR__ . '/luxury-bags-local-bootstrap-20261102.php';
require_once dirname(__DIR__) . '/wp-content/mu-plugins/vpn-luxury-bags-20261102/import.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
$admins = get_users(['role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'fields' => 'ID']);
vpn_lb02_check($admins, 'Existing local administrator required.');
wp_set_current_user((int) $admins[0]);
add_filter('pre_wp_mail', '__return_true', PHP_INT_MAX);
$data = vpn_lb02_package(); $context = vpn_lb02_context();
$before = [];
foreach ($data['products'] as $p) { $before[] = vpn_lb02_verify($p, VPN_LB02_MARKER, (int) $context['term']->term_id); }
$token = 'lb02-smoke-' . bin2hex(random_bytes(5));
$test = $context; $test['asset_marker'] = $token; $test['target'] = rtrim($context['target'], '/') . '-' . $token . '/'; $test['status'] = 'draft';
$fixtures = []; $results = []; $failure = null;
try {
    foreach ($data['products'] as $p) {
        $p['slug'] = $token . '-' . $p['slug']; $p['sku'] = $token . '-' . $p['sku'];
        $fixtures[] = $p;
        $result = vpn_lb02_import_one($p, $test);
        vpn_lb02_check($result['verified'] && $result['status'] === 'draft' && count($result['image_ids']) === 6, 'Fixture readback failed.');
        $again = vpn_lb02_import_one($p, $test);
        vpn_lb02_check($again['id'] === $result['id'] && $again['result'] === 'preserved completed product', 'Retry duplicated a product.');
        $results[] = ['code' => $p['code'], 'create_readback' => 'passed', 'retry' => 'same ID; preserved'];
    }
    $p = $fixtures[0]; $post = vpn_lb02_find($p); $id = (int) $post->ID;
    $savedMedia = array_merge([wc_get_product($id)->get_image_id()], wc_get_product($id)->get_gallery_image_ids());
    delete_post_meta($id, '_vpn_luxury_complete');
    $resumed = vpn_lb02_import_one($p, $test);
    vpn_lb02_check($resumed['id'] === $id && $resumed['image_ids'] === $savedMedia, 'Partial retry duplicated media.');
    wp_update_post(['ID' => $id, 'post_title' => 'Owner edit retained']);
    vpn_lb02_import_one($p, $test);
    vpn_lb02_check(get_post_field('post_title', $id) === 'Owner edit retained', 'Completed owner edit overwritten.');
    delete_post_meta($id, '_vpn_luxury_complete');
    $rejected = false;
    try { vpn_lb02_import_one($p, $test); } catch (RuntimeException $e) { $rejected = str_contains($e->getMessage(), 'partial product preserved'); }
    vpn_lb02_check($rejected && get_post_field('post_title', $id) === 'Owner edit retained', 'Edited partial product not protected.');
    $conflict = $fixtures[1]; $conflict['slug'] = $fixtures[0]['slug'];
    $rejected = false;
    try { vpn_lb02_find($conflict); } catch (RuntimeException $e) { $rejected = str_contains($e->getMessage(), 'SKU preserved'); }
    vpn_lb02_check($rejected, 'Conflicting SKU not protected.');
    // A slug occupied by an unrelated record must be refused even if it is a draft.
    delete_post_meta($id, '_vpn_luxury_import');
    $rejected = false;
    try { vpn_lb02_find($p); } catch (RuntimeException $e) { $rejected = str_contains($e->getMessage(), 'Unrelated existing slug preserved'); }
    vpn_lb02_check($rejected, 'Unrelated slug not protected.');
    update_post_meta($id, '_vpn_luxury_import', VPN_LB02_MARKER);
    foreach ($data['products'] as $i => $original) {
        $again = vpn_lb02_import_one($original, $context);
        vpn_lb02_check($again['id'] === $before[$i]['id'], 'Approved product identity changed.');
        vpn_lb02_check(vpn_lb02_verify($original, VPN_LB02_MARKER, (int) $context['term']->term_id) === $before[$i], 'Approved local product changed.');
    }
} catch (Throwable $error) { $failure = $error; }
finally {
    foreach ($fixtures as $p) {
        $post = get_page_by_path($p['slug'], OBJECT, 'product');
        if (!$post || !str_starts_with($post->post_name, $token . '-')) { continue; }
        foreach ($p['images'] as $i => $img) {
            $assets = get_posts(['post_type' => 'attachment', 'post_status' => 'any', 'numberposts' => -1,
                'meta_key' => '_vpn_luxury_asset', 'meta_value' => $token . '/' . $p['code'] . '/' . $i, 'fields' => 'ids']);
            foreach ($assets as $aid) {
                if ((int) get_post_field('post_parent', $aid) === (int) $post->ID) { wp_delete_attachment($aid, true); }
            }
        }
        wp_delete_post($post->ID, true);
    }
    // Only remove this known temporary directory when empty; never recurse into shared uploads.
    if (is_dir($test['target'])) { rmdir($test['target']); }
}
if ($failure) { throw $failure; }
foreach ($fixtures as $p) { vpn_lb02_check(!get_page_by_path($p['slug'], OBJECT, 'product'), 'Fixture cleanup failed.'); }
echo wp_json_encode(['products' => $results, 'owner_edits' => 'preserved', 'partial_edits' => 'rejected',
    'partial_media_retry' => 'same 6 IDs', 'unrelated_slug' => 'rejected', 'SKU_conflict' => 'rejected',
    'original_5_products' => 'unchanged', 'fixtures' => 'removed'], JSON_PRETTY_PRINT);
