<?php
/**
 * Update existing products with V2 content
 */
define('WP_USE_THEMES', false);
require_once dirname(__DIR__) . '/wp-load.php';

$dry_run = true;
if (php_sapi_name() === 'cli' && in_array('--execute', $argv)) {
    $dry_run = false;
} elseif (isset($_POST['execute_v2_import']) && $_POST['execute_v2_import'] == '1') {
    $dry_run = false;
}

$v2_dir = ABSPATH . 'wp-content/themes/custom-box-theme/assets/hopgiayvpn-11-products-seo-geo-aio-content-v2';

echo "<h3>Product Content V2 Importer</h3>\n";
echo $dry_run ? "<p><strong>[DRY RUN MODE] No database changes will be made.</strong></p>\n\n" : "<p><strong>[EXECUTE MODE] Updating products in database.</strong></p>\n\n";
echo "<pre>";

$products = array(
    '01-custom-white-magnetic-gift-box-black-foam-insert' => 'custom-white-magnetic-gift-box-black-foam-insert',
    '02-custom-orange-drawer-gift-box-navy-tray-ribbon-pull' => 'custom-orange-drawer-gift-box-navy-tray-ribbon-pull',
    '03-custom-ampoule-carton-molded-pulp-insert' => 'custom-ampoule-carton-molded-pulp-insert',
    '04-custom-printed-churros-takeaway-box' => 'custom-printed-churros-takeaway-box',
    '05-custom-black-mailer-box-gold-foil-logo' => 'custom-black-mailer-box-gold-foil-logo',
    '06-custom-fold-flat-apparel-mailer-box-inside-print' => 'custom-fold-flat-apparel-mailer-box-inside-print',
    '07-custom-navy-blue-mailer-box-gold-foil-logo' => 'custom-navy-blue-mailer-box-gold-foil-logo',
    '08-custom-fold-flat-pizza-delivery-box-full-color-print' => 'custom-fold-flat-pizza-delivery-box-full-color-print',
    '09-custom-printed-tool-holder-packaging-carton' => 'custom-printed-tool-holder-packaging-carton',
    '10-custom-botanical-embossed-drawer-gift-box' => 'custom-botanical-embossed-drawer-gift-box',
    '11-custom-watch-gift-box-paper-bag-set-foam-insert' => 'custom-watch-gift-box-paper-bag-set-foam-insert'
);

$stats = array('found' => 0, 'updated' => 0, 'passed' => 0, 'failed' => 0);
$results_table = array();

foreach ($products as $folder => $target_slug) {
    echo "Processing folder: $folder\n";
    $p = get_page_by_path($target_slug, OBJECT, 'product');
    
    if (!$p) {
        echo "  [ERROR] Product not found by slug: $target_slug\n";
        $stats['failed']++;
        continue;
    }
    $stats['found']++;
    $product_id = $p->ID;
    
    $folder_path = "$v2_dir/$folder";
    $short_desc = file_get_contents("$folder_path/short-description.html");
    $long_desc = file_get_contents("$folder_path/long-description.html");
    $seo_fields = json_decode(file_get_contents("$folder_path/seo-fields.json"), true);
    $specs = json_decode(file_get_contents("$folder_path/specifications.json"), true);
    
    // Resolve MEDIA_URL tokens
    preg_match_all('/\{\{MEDIA_URL:([^\}]+)\}\}/', $long_desc, $matches);
    $resolved_all = true;
    $missing_files = [];
    foreach ($matches[1] as $index => $filename) {
        // Search media library for this filename
        global $wpdb;
        $attachment = $wpdb->get_row($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s", '%' . $wpdb->esc_like($filename)));
        if ($attachment) {
            $url = wp_get_attachment_url($attachment->post_id);
            $long_desc = str_replace($matches[0][$index], $url, $long_desc);
        } else {
            $resolved_all = false;
            $missing_files[] = $filename;
        }
    }
    
    // Validations
    $short_words = str_word_count(strip_tags($short_desc));
    $long_words = str_word_count(strip_tags($long_desc));
    
    preg_match_all('/<h1[^>]*>/i', $long_desc, $h1s);
    preg_match_all('/<h2[^>]*>/i', $long_desc, $h2s);
    preg_match_all('/<h3[^>]*>/i', $long_desc, $h3s);
    $h1_count = count($h1s[0]);
    $h2_count = count($h2s[0]);
    $h3_count = count($h3s[0]);
    
    $spec_count = count($specs);
    $moq_val = '';
    foreach ($specs as $spec) {
        if (strpos(strtolower($spec['label']), 'moq') !== false) {
            $moq_val = $spec['value'];
        }
    }
    
    $internal_links = 0;
    preg_match_all('/href="([^"]+)"/i', $long_desc, $hrefs);
    foreach ($hrefs[1] as $href) {
        if (strpos($href, 'hopgiayvpn.com') !== false || substr($href, 0, 1) === '/') {
            $internal_links++;
        }
    }
    
    $meta_desc_len = mb_strlen($seo_fields['meta_description']);
    $unresolved_tokens = substr_count($long_desc, '{{MEDIA_URL:');
    
    $passed = true;
    $errors = [];
    
    if ($short_words < 120 || $short_words > 180) { $passed = false; $errors[] = "Short desc words: $short_words"; }
    if ($long_words < 1500 || $long_words > 2000) { $passed = false; $errors[] = "Long desc words: $long_words"; }
    if ($h1_count > 0) { $passed = false; $errors[] = "H1 count: $h1_count"; }
    if ($h2_count < 7 || $h2_count > 10) { $passed = false; $errors[] = "H2 count: $h2_count"; }
    if ($spec_count !== 21) { $passed = false; $errors[] = "Spec count: $spec_count"; }
    if ($moq_val !== '1000 boxes') { $passed = false; $errors[] = "MOQ is not '1000 boxes'"; }
    if ($meta_desc_len > 155) { $passed = false; $errors[] = "Meta desc len: $meta_desc_len"; }
    if ($internal_links < 4) { $passed = false; $errors[] = "Internal links: $internal_links"; }
    if (!$resolved_all || $unresolved_tokens > 0) { $passed = false; $errors[] = "Unresolved images: " . implode(',', $missing_files); }
    
    echo "  Validations:\n";
    echo "    Short words: $short_words (need 120-180)\n";
    echo "    Long words: $long_words (need 1500-2000)\n";
    echo "    H1s: $h1_count (need 0)\n";
    echo "    H2s: $h2_count (need 7-10)\n";
    echo "    Specs: $spec_count (need 21)\n";
    echo "    MOQ: $moq_val (need 1000 boxes)\n";
    echo "    Meta desc len: $meta_desc_len (need <= 155)\n";
    echo "    Internal links: $internal_links (need >= 4)\n";
    echo "    Unresolved tokens: $unresolved_tokens\n";
    
    if ($passed) {
        echo "  [PASS] All validations passed.\n";
        $stats['passed']++;
    } else {
        echo "  [FAIL] Validation errors: " . implode(' | ', $errors) . "\n";
        $stats['failed']++;
    }
    
    $results_table[] = array(
        'product' => $target_slug,
        'id' => $product_id,
        'passed' => $passed ? 'PASS' : 'FAIL',
        'errors' => implode('; ', $errors)
    );
    
    // Update if execution mode and passed
    if (!$dry_run && $passed) {
        $post_data = array(
            'ID' => $product_id,
            'post_excerpt' => $short_desc,
            'post_content' => $long_desc
        );
        
        // Status updates based on rules
        if ($target_slug === 'custom-fold-flat-pizza-delivery-box-full-color-print') {
            // Must remain draft
            $post_data['post_status'] = 'draft';
        } else {
            // Respect status in seo-fields if provided and appropriate
            if (isset($seo_fields['status']) && $seo_fields['status'] === 'publish' && $p->post_status === 'publish') {
                $post_data['post_status'] = 'publish';
            }
        }
        
        wp_update_post($post_data);
        
        // Update SEO meta
        update_post_meta($product_id, 'rank_math_title', $seo_fields['seo_title']);
        update_post_meta($product_id, 'rank_math_description', $seo_fields['meta_description']);
        update_post_meta($product_id, 'rank_math_focus_keyword', $seo_fields['focus_keyword']);
        
        // Update custom specs
        update_post_meta($product_id, '_custom_box_product_specs', $specs);
        
        echo "  [UPDATED] Product $product_id successfully updated.\n";
        $stats['updated']++;
    }
    echo "\n";
}

echo "=== SUMMARY ===\n";
echo "Found: {$stats['found']}\n";
echo "Passed Validation: {$stats['passed']}\n";
echo "Failed Validation: {$stats['failed']}\n";
echo "Updated: {$stats['updated']}\n";

file_put_contents(dirname(__FILE__) . '/import-v2-results.json', json_encode(array('stats' => $stats, 'details' => $results_table), JSON_PRETTY_PRINT));
echo "</pre>";
