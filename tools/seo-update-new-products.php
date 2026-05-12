<?php
/**
 * Update placeholder WooCommerce products with SEO-ready product data and image names.
 *
 * Run from the WordPress root:
 * php tools/seo-update-new-products.php
 */

require dirname(__DIR__) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$products = array(
    431 => array('title' => 'Black Drawer Watch Packaging Box', 'category' => 'watch-packaging-boxes', 'keywords' => 'watch packaging', 'details' => 'a black drawer structure, custom logo placement, and a fitted presentation tray for watches or small accessories'),
    426 => array('title' => 'Custom Medical Kit Packaging Box', 'category' => 'custom-packaging-boxes', 'keywords' => 'medical kit packaging', 'details' => 'a clean white presentation box, molded inserts, and organized compartments for test kits, devices, or sample sets'),
    422 => array('title' => 'Red Floral Mooncake Gift Packaging Box', 'category' => 'mooncake-gift-packaging-boxes', 'keywords' => 'mooncake gift packaging', 'details' => 'a premium red gift box, floral artwork, and a structured insert layout for festive mooncake presentation'),
    416 => array('title' => 'Cream Drawer Cookie Gift Packaging Box', 'category' => 'dessert-gift-packaging-boxes', 'keywords' => 'cookie gift packaging', 'details' => 'a cream drawer gift box, handle detail, and divided compartments for cookies, pastries, or dessert gift sets'),
    410 => array('title' => 'Custom Paper Tube Food Packaging Box', 'category' => 'custom-paper-tube-packaging-boxes', 'keywords' => 'paper tube food packaging', 'details' => 'a round paper tube structure, custom label space, and secure lid closure for dry food, tea, snacks, or specialty products'),
    405 => array('title' => 'White Perfume Display Packaging Box', 'category' => 'luxury-perfume-packaging-boxes', 'keywords' => 'perfume display packaging', 'details' => 'a white display box, transparent window, and protective insert made for perfume bottles and fragrance retail presentation'),
    401 => array('title' => 'Purple Luxury Rigid Gift Box', 'category' => 'luxury-rigid-gift-boxes', 'keywords' => 'luxury rigid gift box', 'details' => 'a purple rigid box, refined logo finishing, and a premium presentation surface for gifts, cosmetics, or retail collections'),
    397 => array('title' => 'Mint Green Cosmetic Mailer Box', 'category' => 'cosmetic-mailer-packaging-boxes', 'keywords' => 'cosmetic mailer box', 'details' => 'a mint green mailer structure, branded lid area, and compact shape for skincare, beauty, or subscription packaging'),
    391 => array('title' => 'Orange Corrugated Mailer Box', 'category' => 'custom-packaging-boxes', 'keywords' => 'corrugated mailer box', 'details' => 'a kraft corrugated exterior, bright orange interior, and tuck-front mailer style for shipping, ecommerce, and branded unboxing'),
    386 => array('title' => 'Pink Cosmetic Shipping Mailer Box', 'category' => 'cosmetic-mailer-packaging-boxes', 'keywords' => 'pink cosmetic mailer box', 'details' => 'a pink and white mailer box, folded protective sides, and clean presentation for beauty, skincare, or small retail items'),
    383 => array('title' => 'Luxury Teal Paper Gift Bags', 'category' => 'luxury-teal-paper-bags', 'keywords' => 'teal paper gift bags', 'details' => 'teal paper bags, rope handles, and matching printed details for luxury retail, gifts, and promotional packaging'),
    379 => array('title' => 'Custom Red Paper Shopping Bag', 'category' => 'custom-red-paper-bags', 'keywords' => 'red paper shopping bag', 'details' => 'a red custom paper bag, black rope handles, and strong branded display space for retail shops, events, and gift packaging'),
    373 => array('title' => 'Printed Paper Shopping Bag', 'category' => 'printed-paper-shopping-bags', 'keywords' => 'printed paper shopping bag', 'details' => 'a printed paper shopping bag, reinforced handle area, and full-surface artwork for fashion, retail, or promotional use'),
    367 => array('title' => 'Floral Mooncake Gift Packaging Box', 'category' => 'mooncake-gift-packaging-boxes', 'keywords' => 'floral mooncake packaging', 'details' => 'a colorful floral box, festive artwork, and protective fit for mooncake gifts, bakery sets, or seasonal food packaging'),
    361 => array('title' => 'Kraft Bakery Food Packaging Box', 'category' => 'bakery-food-packaging-boxes', 'keywords' => 'kraft bakery packaging box', 'details' => 'a kraft bakery box, wide opening structure, and food-friendly presentation for pastries, bread, cookies, or takeaway desserts'),
    357 => array('title' => 'Kraft Mooncake Gift Box With Inserts', 'category' => 'mooncake-gift-packaging-boxes', 'keywords' => 'kraft mooncake gift box', 'details' => 'a kraft gift box, sliding lid, and divided insert tray for mooncakes, tea cakes, or premium festive food sets'),
    351 => array('title' => 'Orange Mooncake Chocolate Gift Box', 'category' => 'mooncake-chocolate-gift-boxes', 'keywords' => 'mooncake chocolate gift box', 'details' => 'an orange rigid gift box, decorative inner artwork, and individual compartments for mooncakes, chocolates, or premium confections'),
    346 => array('title' => 'Custom Teal Rigid Gift Box', 'category' => 'luxury-rigid-gift-boxes', 'keywords' => 'teal rigid gift box', 'details' => 'a teal rigid box, metallic logo effect, and elegant square structure for branded gifts, souvenirs, or premium retail products'),
    340 => array('title' => 'Blue Cosmetic Set Packaging Box', 'category' => 'cosmetic-set-packaging-boxes', 'keywords' => 'cosmetic set packaging box', 'details' => 'a blue and white cosmetic set box, drawer-style insert, and protective layout for skincare bottles, jars, or beauty kits'),
    334 => array('title' => 'Pink Perfume Gift Set Packaging Box', 'category' => 'luxury-perfume-packaging-boxes', 'keywords' => 'perfume gift set packaging', 'details' => 'a pink rigid presentation box, fitted insert tray, and premium layout for perfume bottles, fragrance samples, or cosmetic gift sets'),
    328 => array('title' => 'Bottle Display Carrier Packaging Box', 'category' => 'wine-bottle-gift-boxes', 'keywords' => 'bottle display carrier box', 'details' => 'a cardboard bottle carrier, display front, and handle structure for beverage bottles, craft drinks, or promotional gift packs'),
    325 => array('title' => 'White Essential Oil Gift Packaging Box', 'category' => 'cosmetic-set-packaging-boxes', 'keywords' => 'essential oil gift packaging', 'details' => 'a white gift box, custom insert, and clean product layout for essential oils, small bottles, or wellness product sets'),
    323 => array('title' => 'Gray Sliding Drawer Watch Box', 'category' => 'rigid-sliding-drawer-boxes', 'keywords' => 'sliding drawer watch box', 'details' => 'a gray sliding drawer box, fitted inner tray, and compact luxury structure for watches, jewelry, or small premium accessories'),
    306 => array('title' => 'Green Candle Jar Gift Box', 'category' => 'candle-jar-packaging-boxes', 'keywords' => 'candle jar gift box', 'details' => 'a green rigid gift box, fitted base, and premium opening experience for candle jars, fragrance products, or home decor gifts'),
    301 => array('title' => 'Cream Drawer Gift Box', 'category' => 'luxury-drawer-gift-boxes', 'keywords' => 'cream drawer gift box', 'details' => 'a cream drawer box, pull-tab opening, and clean logo space for premium gifts, cosmetics, or boutique retail products'),
    295 => array('title' => 'Marble Dessert Gift Box With Inserts', 'category' => 'dessert-packaging-boxes-with-inserts', 'keywords' => 'dessert gift box with inserts', 'details' => 'a marble-pattern dessert box, clear window lid, and divided insert tray for pastries, cakes, or gift-ready desserts'),
);

function vpn_sentence_case_alt($title, $suffix = '') {
    return trim($title . ($suffix ? ' ' . $suffix : ''));
}

function vpn_product_description($title, $keywords, $details) {
    return sprintf(
        '<p>%1$s is designed for brands that need custom %2$s with reliable structure and a polished presentation. The packaging features %3$s.</p><p>It can be customized by size, paper material, printing method, logo placement, insert style, surface finishing, and order quantity. VPN Packaging supports dieline preparation, sampling, and bulk production for retail, gifting, ecommerce, and promotional packaging projects.</p>',
        esc_html($title),
        esc_html($keywords),
        esc_html($details)
    );
}

function vpn_product_short_description($title, $keywords) {
    return sprintf(
        'Custom %1$s for branded presentation, protective packing, and premium retail-ready unboxing. Available with tailored size, material, printing, inserts, and finishing options.',
        esc_html($keywords)
    );
}

function vpn_unique_attachment_filename($directory, $filename, $attachment_id) {
    $info = pathinfo($filename);
    $base = $info['filename'];
    $extension = isset($info['extension']) ? '.' . strtolower($info['extension']) : '';
    $candidate = $base . $extension;
    $index = 1;

    while (file_exists($directory . DIRECTORY_SEPARATOR . $candidate)) {
        $existing_id = attachment_url_to_postid(trailingslashit(wp_upload_dir()['baseurl']) . basename($directory) . '/' . $candidate);
        if ((int) $existing_id === (int) $attachment_id) {
            break;
        }

        $candidate = $base . '-' . $index . $extension;
        $index++;
    }

    return $candidate;
}

function vpn_rename_attachment_file($attachment_id, $target_slug, $label) {
    $file = get_attached_file($attachment_id);

    if (!$file || !file_exists($file)) {
        return false;
    }

    $directory = dirname($file);
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $new_filename = sanitize_file_name($target_slug . ($label ? '-' . $label : '') . '.' . $extension);
    $new_filename = vpn_unique_attachment_filename($directory, $new_filename, $attachment_id);
    $new_file = trailingslashit($directory) . $new_filename;

    if ($file !== $new_file) {
        if (!rename($file, $new_file)) {
            return false;
        }

        update_attached_file($attachment_id, $new_file);
    }

    $metadata = wp_generate_attachment_metadata($attachment_id, $new_file);

    if (!is_wp_error($metadata) && !empty($metadata)) {
        wp_update_attachment_metadata($attachment_id, $metadata);
    }

    return basename($new_file);
}

foreach ($products as $product_id => $data) {
    $product = wc_get_product($product_id);

    if (!$product) {
        echo "Missing product {$product_id}\n";
        continue;
    }

    $slug = sanitize_title($data['title']);
    $term = get_term_by('slug', $data['category'], 'product_cat');

    wp_update_post(array(
        'ID'           => $product_id,
        'post_title'   => $data['title'],
        'post_name'    => wp_unique_post_slug($slug, $product_id, 'publish', 'product', 0),
        'post_content' => vpn_product_description($data['title'], $data['keywords'], $data['details']),
        'post_excerpt' => vpn_product_short_description($data['title'], $data['keywords']),
    ));

    if ($term && !is_wp_error($term)) {
        wp_set_object_terms($product_id, array((int) $term->term_id), 'product_cat');
    }

    $image_ids = array_values(array_filter(array_merge(array($product->get_image_id()), $product->get_gallery_image_ids())));
    $labels = array('', 'open', 'inside', 'detail', 'angle');

    foreach ($image_ids as $index => $attachment_id) {
        $label = isset($labels[$index]) ? $labels[$index] : 'view-' . ($index + 1);
        $attachment_title = $data['title'] . ($label ? ' - ' . ucwords(str_replace('-', ' ', $label)) : '');
        $new_file = vpn_rename_attachment_file($attachment_id, $slug, $label);

        wp_update_post(array(
            'ID'           => $attachment_id,
            'post_title'   => $attachment_title,
            'post_name'    => sanitize_title($attachment_title),
            'post_excerpt' => $attachment_title,
            'post_content' => vpn_sentence_case_alt($data['title'], $label ? str_replace('-', ' ', $label) . ' view' : 'main product image'),
        ));

        update_post_meta($attachment_id, '_wp_attachment_image_alt', vpn_sentence_case_alt($data['title'], $label ? str_replace('-', ' ', $label) . ' view' : 'main product image'));

        echo "Product {$product_id}: {$data['title']} image {$attachment_id} => " . ($new_file ?: 'not renamed') . "\n";
    }
}

wc_delete_product_transients();
flush_rewrite_rules(false);

echo "Done\n";
