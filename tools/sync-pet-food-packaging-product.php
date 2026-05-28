<?php
/**
 * Create or update the pet food packaging WooCommerce product.
 *
 * Run from the WordPress root:
 * php tools/sync-pet-food-packaging-product.php
 */

require dirname(__DIR__) . '/wp-load.php';

if (!class_exists('WooCommerce') || !class_exists('WC_Product_Simple')) {
    fwrite(STDERR, "WooCommerce is not available.\n");
    exit(1);
}

$product_name = 'Custom Printed Corrugated Pet Food Packaging Boxes';
$product_slug = 'custom-printed-corrugated-pet-food-packaging-boxes';

$short_description = 'Custom printed corrugated pet food packaging boxes designed for hay, treats, dry food, and other pet products. Made with durable corrugated paperboard, full-color printing, and retail-ready structure to protect products and improve shelf presentation.';

$description = <<<HTML
<p>Our custom printed corrugated pet food packaging boxes are designed for brands that need strong, attractive, and retail-ready packaging for pet products such as hay, treats, dry food, supplements, and small animal supplies.</p>
<p>Made from durable corrugated paperboard, these boxes provide better protection during storage, shipping, and retail display. The outer surface can be printed with full-color artwork, product information, brand logo, nutrition details, barcode, and usage instructions.</p>
<p>This packaging style is suitable for pet food brands, animal care products, rabbit hay, guinea pig food, bird food, dog treats, cat treats, and other pet-related products. The box structure can be customized based on product weight, size, display needs, and shipping requirements.</p>
<p>VPN Paper Box supports custom size, material thickness, printing design, surface finishing, and bulk production for B2B pet product packaging projects. We help brands create professional packaging that looks good on shelves and protects products during transportation.</p>
<p><strong>Key advantages:</strong></p>
<ul>
<li>Durable corrugated paperboard structure</li>
<li>Full-color custom printing</li>
<li>Suitable for pet food and animal care products</li>
<li>Custom size, shape, and box structure available</li>
<li>Retail-ready and export-friendly packaging</li>
<li>Factory-direct production from Vietnam</li>
</ul>
HTML;

$images = array(
    array(
        'slug' => 'custom-printed-corrugated-pet-food-box',
        'file' => 'custom-printed-corrugated-pet-food-box.webp',
        'alt' => 'Custom printed corrugated pet food packaging box for hay and pet products',
    ),
    array(
        'slug' => 'custom-printed-corrugated-pet-food-box-2',
        'file' => 'custom-printed-corrugated-pet-food-box-2.webp',
        'alt' => 'Front view of custom printed pet food packaging box',
    ),
    array(
        'slug' => 'custom-printed-corrugated-pet-food-box-3',
        'file' => 'custom-printed-corrugated-pet-food-box-3.webp',
        'alt' => 'Side view of corrugated pet food packaging box with custom logo',
    ),
    array(
        'slug' => 'custom-printed-corrugated-pet-food-box-4',
        'file' => 'custom-printed-corrugated-pet-food-box-4.webp',
        'alt' => 'Custom printed pet product packaging box with full color artwork',
    ),
);

function vpn_pet_food_get_or_create_attachment($image) {
    $existing = get_page_by_path($image['slug'], OBJECT, 'attachment');
    if ($existing) {
        update_post_meta($existing->ID, '_wp_attachment_image_alt', $image['alt']);
        return (int) $existing->ID;
    }

    $upload = wp_upload_dir(null, false);
    $file_path = trailingslashit($upload['basedir']) . '2026/05/' . $image['file'];

    if (!file_exists($file_path)) {
        fwrite(STDERR, "Missing image file: {$file_path}\n");
        exit(1);
    }

    $attachment_id = wp_insert_attachment(array(
        'post_mime_type' => 'image/webp',
        'post_title' => pathinfo($image['file'], PATHINFO_FILENAME),
        'post_name' => $image['slug'],
        'post_status' => 'inherit',
    ), $file_path);

    if (is_wp_error($attachment_id)) {
        fwrite(STDERR, $attachment_id->get_error_message() . "\n");
        exit(1);
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $file_path));
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']);

    return (int) $attachment_id;
}

function vpn_pet_food_get_or_create_term($name, $taxonomy, $args = array()) {
    $term = term_exists($name, $taxonomy);
    if (!$term) {
        $term = wp_insert_term($name, $taxonomy, $args);
    } elseif (!empty($args['parent'])) {
        $term_id = is_array($term) ? (int) $term['term_id'] : (int) $term;
        wp_update_term($term_id, $taxonomy, array('parent' => (int) $args['parent']));
    }

    if (is_wp_error($term)) {
        fwrite(STDERR, $term->get_error_message() . "\n");
        exit(1);
    }

    return is_array($term) ? (int) $term['term_id'] : (int) $term;
}

$attachment_ids = array_map('vpn_pet_food_get_or_create_attachment', $images);

$parent_category_id = vpn_pet_food_get_or_create_term('Custom Packaging Boxes', 'product_cat', array(
    'slug' => 'custom-packaging-boxes',
));
$category_id = vpn_pet_food_get_or_create_term('Pet Food Packaging Boxes', 'product_cat', array(
    'slug' => 'pet-food-packaging-boxes',
    'parent' => $parent_category_id,
));

$tag_ids = array_map(function ($tag) {
    return vpn_pet_food_get_or_create_term($tag, 'product_tag');
}, array(
    'pet food packaging',
    'pet food boxes',
    'corrugated pet food box',
    'custom pet packaging',
    'printed corrugated boxes',
    'hay packaging box',
    'animal care packaging',
    'retail pet product packaging',
));

$existing_product = get_page_by_path($product_slug, OBJECT, 'product');
$product = $existing_product ? wc_get_product($existing_product->ID) : new WC_Product_Simple();

$product->set_name($product_name);
$product->set_slug($product_slug);
$product->set_status('publish');
$product->set_catalog_visibility('visible');
$product->set_short_description($short_description);
$product->set_description($description);
$product->set_regular_price('');
$product->set_price('');
$product->set_image_id($attachment_ids[0]);
$product->set_gallery_image_ids(array_slice($attachment_ids, 1));
$product->set_category_ids(array($category_id));
$product->set_tag_ids($tag_ids);

$product_id = $product->save();

update_post_meta($product_id, 'rank_math_focus_keyword', 'custom printed pet food packaging boxes');
update_post_meta($product_id, 'rank_math_title', 'Custom Printed Pet Food Packaging Boxes | VPN Paper Box');
update_post_meta($product_id, 'rank_math_description', 'Custom printed corrugated pet food packaging boxes for hay, treats, dry food, and pet supplies. Durable paperboard, full-color printing, custom size, factory-direct production from Vietnam.');

$specs = array(
    'Feature' => 'Custom Printed Pet Food Packaging',
    'Industrial Use' => 'Pet Food, Animal Care, Retail Packaging',
    'Paper Type' => 'Corrugated Paperboard',
    'Box Type' => 'Custom Printed Corrugated Box',
    'Shape' => 'Rectangular Box',
    'Place of Origin' => 'Vietnam',
    'Model Number' => 'Custom Pet Food Box',
    'Brand Name' => 'VPN',
    'Province' => 'Ho Chi Minh City',
    'Custom Order' => 'Accept',
    'Logo Printing' => 'Custom Logo Printing',
    'Printing Handling' => 'Offset Printing, Digital Printing, Flexo Printing',
    'Surface Finish' => 'Matte Lamination, Gloss Lamination, Varnish',
    'Color' => 'Full Color CMYK Printing',
    'Material' => 'Corrugated Cardboard',
    'Usage' => 'Pet Food Packaging, Hay Packaging, Treat Packaging',
    'Size' => 'Custom Size Accepted',
    'Design' => 'Customer Artwork Accepted',
    'MOQ' => '1000 Pieces',
    'Sample' => 'Available',
    'OEM/ODM' => 'Accept',
    'Packaging' => 'Flat Packed or Assembled',
    'Application' => 'Rabbit Hay, Guinea Pig Food, Dog Treats, Cat Treats, Pet Supplies',
);

update_post_meta($product_id, '_custom_box_product_specs', array_map(function ($label, $value) {
    return array('label' => $label, 'value' => $value);
}, array_keys($specs), $specs));

wc_delete_product_transients($product_id);
clean_post_cache($product_id);

echo "Synced product ID: {$product_id}\n";
echo "Permalink: " . get_permalink($product_id) . "\n";
