<?php
define('WP_USE_THEMES', false);
require_once dirname(__DIR__) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$v2_dir = ABSPATH . 'wp-content/themes/custom-box-theme/assets/hopgiayvpn-11-products-seo-geo-aio-content-v2';
$bundled_uploads = ABSPATH . 'wp-content/themes/custom-box-theme/inc/product-sample-deploy-assets/uploads/2026/08';

echo "<h3>V2 Content Full Deploy (11 Products)</h3>\n<pre>";

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

function vpn_get_or_upload_image($filename, $bundled_uploads, $post_id) {
    global $wpdb;
    $title = preg_replace('/\.[^.]+$/', '', $filename);
    $attachment = $wpdb->get_row($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'attachment'", $title));
    if ($attachment) return $attachment->ID;
    
    $source_path = $bundled_uploads . '/' . $filename;
    if (!file_exists($source_path)) return false;
    
    $upload_dir = wp_upload_dir('2026/08');
    $dest_path = $upload_dir['path'] . '/' . $filename;
    if (!file_exists($upload_dir['path'])) wp_mkdir_p($upload_dir['path']);
    copy($source_path, $dest_path);
    
    $filetype = wp_check_filetype(basename($dest_path), null);
    $attachment_data = array(
        'guid'           => $upload_dir['url'] . '/' . basename($dest_path), 
        'post_mime_type' => $filetype['type'],
        'post_title'     => $title,
        'post_content'   => '',
        'post_status'    => 'inherit'
    );
    $attach_id = wp_insert_attachment($attachment_data, $dest_path, $post_id);
    if (!is_wp_error($attach_id)) {
        $attach_data = wp_generate_attachment_metadata($attach_id, $dest_path);
        wp_update_attachment_metadata($attach_id, $attach_data);
        return $attach_id;
    }
    return false;
}

foreach ($products as $folder => $target_slug) {
    echo "Processing folder: $folder\n";
    $seo_fields = json_decode(file_get_contents("$v2_dir/$folder/seo-fields.json"), true);
    $short_desc = file_get_contents("$v2_dir/$folder/short-description.html");
    $long_desc  = file_get_contents("$v2_dir/$folder/long-description.html");
    $specs      = json_decode(file_get_contents("$v2_dir/$folder/specifications.json"), true);
    
    $p = get_page_by_path($target_slug, OBJECT, 'product');
    $product_id = 0;
    
    if (!$p) {
        $post_data = array(
            'post_title'   => $seo_fields['product_name'],
            'post_name'    => $target_slug,
            'post_content' => '', // temporary
            'post_excerpt' => $short_desc,
            'post_status'  => ($target_slug === 'custom-fold-flat-pizza-delivery-box-full-color-print') ? 'draft' : 'publish',
            'post_type'    => 'product',
        );
        $product_id = wp_insert_post($post_data);
        echo "  [CREATED] Product $target_slug created with ID $product_id.\n";
    } else {
        $product_id = $p->ID;
        echo "  [FOUND] Product $target_slug found (ID $product_id).\n";
        // Update status if it's not the pizza box
        if ($target_slug !== 'custom-fold-flat-pizza-delivery-box-full-color-print') {
            wp_update_post(array('ID' => $product_id, 'post_status' => 'publish'));
        }
    }
    
    // Process images
    $manifest_lines = file("$v2_dir/$folder/image-manifest.csv");
    array_shift($manifest_lines);
    $featured_id = 0;
    $gallery_ids = array();
    
    foreach ($manifest_lines as $line) {
        $parts = str_getcsv($line);
        if (count($parts) < 4) continue;
        $order = (int)$parts[0];
        $filename = trim($parts[1]);
        $alt = trim($parts[3]);
        
        $att_id = vpn_get_or_upload_image($filename, $bundled_uploads, $product_id);
        if ($att_id) {
            update_post_meta($att_id, '_wp_attachment_image_alt', $alt);
            if ($order === 2) {
                $featured_id = $att_id;
            } else {
                $gallery_ids[] = $att_id;
            }
            
            // Replace token in content
            $token = '{{MEDIA_URL:' . $filename . '}}';
            $url = wp_get_attachment_url($att_id);
            $long_desc = str_replace($token, $url, $long_desc);
        }
    }
    
    // Update product content and meta
    wp_update_post(array(
        'ID' => $product_id,
        'post_content' => $long_desc,
        'post_excerpt' => $short_desc
    ));
    
    if ($featured_id) set_post_thumbnail($product_id, $featured_id);
    if (!empty($gallery_ids)) update_post_meta($product_id, '_product_image_gallery', implode(',', $gallery_ids));
    
    update_post_meta($product_id, 'rank_math_title', $seo_fields['seo_title']);
    update_post_meta($product_id, 'rank_math_description', $seo_fields['meta_description']);
    update_post_meta($product_id, 'rank_math_focus_keyword', $seo_fields['focus_keyword']);
    update_post_meta($product_id, '_custom_box_product_specs', $specs);
    
    if (isset($seo_fields['category'])) {
        $cat = get_term_by('name', $seo_fields['category'], 'product_cat');
        if ($cat) {
            wp_set_object_terms($product_id, $cat->term_id, 'product_cat');
        }
    }
    echo "  [UPDATED] Meta, SEO, and images applied.\n";
}

echo "\nDone!\n</pre>";
