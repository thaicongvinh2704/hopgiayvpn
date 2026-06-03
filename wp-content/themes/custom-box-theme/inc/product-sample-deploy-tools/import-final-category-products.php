<?php
/**
 * Create/update final category-balance products that are not part of the older
 * generated sample batches.
 *
 * Usage:
 *   php tools/import-final-category-products.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

require_once dirname(__DIR__) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$slug = 'custom-pink-cosmetic-gift-box-with-satin-lining';
$title = 'CUSTOM PINK COSMETIC GIFT BOX WITH SATIN LINING';
$category_slug = 'beauty-skincare-packaging';
$category_name = 'Beauty and Skincare Packaging';
$source_dir = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/2026/05';
$image_count = 6;

if (!is_dir($source_dir)) {
    fwrite(STDERR, "Source image folder not found: {$source_dir}\n");
    exit(1);
}

function vpn_satin_get_or_create_attachment(string $source, string $target_name, int $parent_id): int {
    $upload_dir = wp_upload_dir();

    if (!empty($upload_dir['error'])) {
        fwrite(STDERR, 'Upload dir error: ' . $upload_dir['error'] . PHP_EOL);
        return 0;
    }

    $existing = get_posts(array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => array(
            array(
                'key'     => '_wp_attached_file',
                'value'   => '/' . $target_name,
                'compare' => 'LIKE',
            ),
        ),
    ));

    if ($existing) {
        return (int) $existing[0];
    }

    $target_dir = $upload_dir['path'];

    if (!wp_mkdir_p($target_dir)) {
        fwrite(STDERR, "Could not create upload folder: {$target_dir}\n");
        return 0;
    }

    $unique_name = wp_unique_filename($target_dir, $target_name);
    $target = trailingslashit($target_dir) . $unique_name;

    if (!copy($source, $target)) {
        fwrite(STDERR, "Could not copy image: {$source}\n");
        return 0;
    }

    $filetype = wp_check_filetype($unique_name, null);
    $attachment_id = wp_insert_attachment(array(
        'post_mime_type' => $filetype['type'] ?: 'image/webp',
        'post_title'     => preg_replace('/\.[^.]+$/', '', $unique_name),
        'post_content'   => '',
        'post_status'    => 'inherit',
        'post_parent'    => $parent_id,
    ), $target, $parent_id);

    if (is_wp_error($attachment_id) || !$attachment_id) {
        fwrite(STDERR, "Could not insert attachment: {$target}\n");
        return 0;
    }

    $metadata = wp_generate_attachment_metadata((int) $attachment_id, $target);
    wp_update_attachment_metadata((int) $attachment_id, $metadata);

    return (int) $attachment_id;
}

function vpn_satin_specs(string $title): array {
    return array(
        array('label' => 'Feature', 'value' => 'Custom logo, satin lining, premium rigid gift presentation'),
        array('label' => 'Industrial Use', 'value' => 'Skincare, cosmetics, fragrance, makeup, spa gift sets, beauty retail'),
        array('label' => 'Paper Type', 'value' => 'Rigid greyboard wrapped with art paper, specialty paper, or coated paper'),
        array('label' => 'Box Type', 'value' => 'Rigid cosmetic gift box, magnetic gift box, hinged presentation box'),
        array('label' => 'Shape', 'value' => 'Rectangular / square / custom structure'),
        array('label' => 'Place of Origin', 'value' => 'Vietnam'),
        array('label' => 'Model Number', 'value' => $title),
        array('label' => 'Brand Name', 'value' => 'VPN'),
        array('label' => 'Province', 'value' => 'Ho Chi Minh City'),
        array('label' => 'Accessories', 'value' => 'Satin lining, ribbon puller, paper insert, EVA insert, magnetic closure, printed sleeve'),
        array('label' => 'Custom Order', 'value' => 'Accept'),
        array('label' => 'Liner Type', 'value' => 'Satin fabric lining / paper tray / EVA or foam insert'),
        array('label' => 'Logo Printing', 'value' => 'Custom logo'),
        array('label' => 'Printing Handling', 'value' => 'CMYK printing, Pantone printing, foil stamping, embossing, debossing, spot UV, matte lamination, soft-touch coating'),
        array('label' => 'Color', 'value' => 'Pink / custom Pantone / CMYK / brand color matching'),
        array('label' => 'Size', 'value' => 'Customized size'),
        array('label' => 'Thickness', 'value' => 'Customized thickness'),
        array('label' => 'Single Piece Price', 'value' => 'Price based on size, material, insert, printing, finishing, and quantity'),
        array('label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes'),
        array('label' => 'Product Name', 'value' => $title),
        array('label' => 'Design', 'value' => "Customer's Specific Requirement"),
    );
}

function vpn_satin_short_description(): string {
    return 'CUSTOM PINK COSMETIC GIFT BOX WITH SATIN LINING is a premium rigid paper packaging solution for skincare sets, makeup collections, perfume gifts, spa products, beauty samples, and limited-edition cosmetic launches. The pink outer box and soft satin interior create a polished unboxing experience while helping protect printed cartons, glass bottles, jars, and delicate beauty accessories from rubbing during handling. Brands can customize the box size, paper wrap, satin color, magnetic closure, ribbon, insert layout, logo printing, foil stamping, embossing, spot UV, and soft-touch finish. This packaging is suitable for beauty brands, skincare distributors, cosmetic retailers, private label manufacturers, hotel and spa gift programs, influencer campaign kits, and OEM/ODM bulk orders. MOQ is 1000 boxes, with material, structure, artwork, and finishing adjusted around each product set.';
}

function vpn_satin_inline_figure(int $attachment_id, string $caption, bool $narrow = false): string {
    if (!$attachment_id) {
        return '';
    }

    $classes = 'product-inline-figure product-inline-figure-small' . ($narrow ? ' is-narrow' : '');
    $html = '<figure class="' . esc_attr($classes) . '">';
    $html .= wp_get_attachment_image($attachment_id, 'large', false, array('loading' => 'lazy'));
    $html .= '<figcaption>' . esc_html($caption) . '</figcaption>';
    $html .= '</figure>';

    return $html;
}

function vpn_satin_build_content(array $image_ids): string {
    $sections = array(
        array(
            'Custom Pink Cosmetic Gift Box for Beauty Presentation',
            array(
                'A custom pink cosmetic gift box with satin lining is made for beauty products that need a stronger emotional first impression than a standard folding carton can provide. The visible product in this image group is a rigid pink presentation box with a soft satin interior, designed to turn skincare, makeup, fragrance, and spa products into a gift-ready set. For beauty buyers, the package is often judged before the product is opened. A clean outer color, neat logo position, smooth lid movement, and refined inner fabric all help the product feel more valuable at the moment of handover.',
                'This type of packaging is especially useful when the product line includes multiple components. A skincare brand may need to hold a serum bottle, cream jar, facial mask, and applicator. A makeup brand may need compartments for lipstick, compact powder, brush, and promotional card. A perfume or spa brand may use the same structure for a holiday gift set or VIP client box. The rigid board gives the set a stable shape, while the satin lining adds softness, shine, and a premium tactile layer that supports beauty retail, online gifting, boutique display, and influencer campaign packaging.',
            ),
        ),
        array(
            'Rigid Structure and Satin Interior',
            array(
                'The main structure can be produced as a hinged rigid box, magnetic gift box, lid-and-base rigid box, book-style presentation box, or drawer-style cosmetic box depending on how the products need to be arranged. Rigid greyboard is commonly used as the core because it keeps the box square and firm. The outside can be wrapped with coated art paper, specialty textured paper, dyed paper, pearl paper, or printed paper with matte, gloss, or soft-touch lamination. For this pink cosmetic gift box, the calm pastel tone works well for skincare, facial care, perfume, beauty accessories, and wellness gift sets.',
                'The satin lining is not just decoration. It reduces visible rubbing between the product and the inner box surface, creates a softer bed for delicate retail packaging, and makes the opening experience more ceremonial. The satin can be flat-lined, gathered, pleated, or combined with a shaped insert. For heavier bottles or jars, satin may be placed over EVA, foam, molded pulp, or a paperboard tray so the product remains stable during transit. This approach gives the brand a premium look while still keeping the inner construction practical for production and packing.',
            ),
        ),
        array(
            'Product Protection for Cosmetic Sets',
            array(
                'Beauty products often combine fragile containers, glossy labels, metallic caps, pumps, droppers, and printed secondary cartons. If the box is loose inside, these surfaces can scratch each other during packing, warehouse handling, and courier delivery. A custom insert layout solves this problem by giving every item a defined position. The insert can be made for one hero bottle, a set of jars, several sample vials, or a complete beauty routine. For glass containers, the cavity depth, edge clearance, and bottom support should be checked carefully before mass production.',
                'The outer rigid box also protects the perceived value of the product. Even when the goods are packed into an export carton, the retail box must arrive clean, square, and presentable. For e-commerce gift sets, the package may travel through more touchpoints than a normal store order. The combination of rigid board, custom insert, satin lining, and correct carton packing helps reduce movement and presentation damage. It also makes the product easier for fulfillment teams to assemble repeatedly because every item has a clear location in the box.',
            ),
        ),
        array(
            'Branding and Surface Finishing Options',
            array(
                'A pink cosmetic gift box can be made minimal and soft, or it can become a high-impact luxury box depending on the finishing process. Common branding options include foil stamping, embossing, debossing, spot UV, blind logo pressing, metallic ink, pearl ink, and full-color offset printing. For premium skincare, a small gold or rose-gold foil logo on a matte pink surface often looks elegant. For a younger makeup line, the same structure can use stronger color blocking, pattern printing, gloss UV, holographic foil, or a custom sleeve for seasonal campaigns.',
                'The finishing plan should protect readability as well as beauty. Ingredient claims, product set names, barcode labels, QR codes, and regulatory marks must remain clear if they are printed on the box or sleeve. Logo size should be balanced with the lid area so the package feels intentional instead of crowded. If the satin interior is visible in photos or unboxing videos, the inner color should be considered part of the brand system. Matching the satin with the outer paper, ribbon, tray, and printed card gives the set a more complete retail identity.',
            ),
        ),
    );

    $content = '';
    foreach ($sections as $index => $section) {
        $content .= '<h2>' . esc_html($section[0]) . '</h2>';
        foreach ($section[1] as $paragraph) {
            $content .= '<p>' . esc_html($paragraph) . '</p>';
        }

        if (0 === $index) {
            $content .= vpn_satin_inline_figure($image_ids[0] ?? 0, 'Pink cosmetic gift box with satin lining for skincare and beauty gift set presentation.');
        } elseif (1 === $index) {
            $content .= vpn_satin_inline_figure($image_ids[2] ?? 0, 'Close view of the satin lined cosmetic gift box structure and pink rigid paper wrap.', true);
        }
    }

    $content .= '<h2>Customization for Skincare, Makeup, and Fragrance Brands</h2>'
        . '<p>Different beauty products need different packaging behavior. A skincare set may require a calm medical-beauty look with clean typography and protective insert cavities. A makeup set may need a stronger retail color story and an interior layout that reveals several shades at once. A fragrance gift box may need more empty space around the bottle to make the product feel exclusive. Because of that, the same pink cosmetic gift box can be adjusted through size, lid depth, tray height, satin color, logo placement, and accessory selection.</p>'
        . '<p>Buyers can choose a magnetic closure for a smooth opening, a ribbon puller for drawer versions, a paper sleeve for extra product information, or a printed belly band for seasonal promotions. Inside the box, cards can be added for product instructions, skin routine steps, ingredient highlights, warranty notes, or campaign messages. For private label and OEM/ODM projects, the packaging can be developed around existing bottles and jars, or the box structure can be designed first so the product set feels more curated when launched.</p>'
        . vpn_satin_inline_figure($image_ids[3] ?? 0, 'Custom satin lining and insert planning for premium cosmetic gift packaging.')
        . '<h2>Material Selection and Production Details</h2>'
        . '<p>The material selection starts with the product weight, target price level, and expected retail environment. A heavier set with glass bottles usually needs thicker rigid board and a stronger inner support system. A light sample kit can use a slimmer board with a simpler satin-lined tray. The outer wrap may be printed art paper for full-color branding, specialty paper for a tactile luxury feel, or dyed paper for a clean solid color. When a soft-touch surface is used, the brand should check fingerprint resistance and scuff resistance because beauty packaging is often handled closely by customers.</p>'
        . '<p>Before bulk production, a dieline and sample should be reviewed with real product components. The review should check lid alignment, magnet strength, tray fit, satin tension, corner wrapping, logo position, paper color, and carton packing. If the box will be photographed for e-commerce, the sample should also be checked under lighting because satin can reflect differently from paper. A careful sample stage helps avoid changes after mass production begins and gives the buyer confidence that the package will match the intended launch image.</p>'
        . '<h2>Retail Display and Unboxing Experience</h2>'
        . '<p>For cosmetic products, the box is part of the sales message. In a boutique, a rigid pink box can sit on a counter as a gift set rather than a basic container. In online sales, it becomes the first branded object customers see when opening the parcel. For influencer or PR campaigns, the satin lining helps the product look more photogenic when the lid is opened. The inner fabric frames the product, reduces visual noise, and gives the set a softer beauty tone that works well for skincare, perfume, wellness, and premium makeup.</p>'
        . '<p>The unboxing path can be planned in layers. The customer may first see a clean outer lid with a foil logo, then open the magnetic flap, then discover the satin surface, product cavities, printed care card, and accessories. Each layer should support the same brand feeling. A minimal luxury brand may keep everything quiet and tonal. A colorful makeup brand may use contrast satin, printed patterns, or a sleeve with campaign artwork. A spa brand may choose a warmer paper texture and soft ribbon detail. The goal is to make the package feel intentional without making packing difficult for the production team.</p>'
        . vpn_satin_inline_figure($image_ids[4] ?? 0, 'Pink rigid cosmetic gift box side view for beauty retail and gifting.', true)
        . '<h2>Bulk Order Planning for B2B Buyers</h2>'
        . '<p>This product is designed for bulk custom packaging projects, not one-piece retail resale. MOQ is 1000 boxes, and the final quotation depends on size, board thickness, paper type, satin lining method, insert material, printing, finishing, accessory choices, and order quantity. For accurate costing, buyers should prepare product dimensions, product weight, set arrangement, target market, artwork files, and any special packing requirements. If the products are still being developed, approximate dimensions can be used for the first structure recommendation, then refined during sampling.</p>'
        . '<p>For international orders, export carton strength and packing method should be discussed together with the retail box. Rigid cosmetic boxes with satin lining need to be protected from compression, dust, humidity, and surface rubbing during shipment. Cartons can be arranged by box size and weight, with protective film, paper wrapping, or inner dividers when needed. This helps the boxes arrive clean for final product packing, retail display, or direct distribution to beauty campaign partners.</p>'
        . '<h2>Artwork, Proofing, and Quality Control</h2>'
        . '<p>Artwork can be prepared in AI, PDF, or other production-ready formats after the dieline is confirmed. Important areas include logo position, safe margins, bleed, folding edges, wrap seams, foil stamping location, and color references. For pink packaging, Pantone matching is often preferred when the brand needs a specific shade across box, sleeve, ribbon, and printed card. Digital proofs help check layout, while physical samples confirm material feeling, color behavior, and structural fit.</p>'
        . '<p>Quality control should include board thickness, paper wrapping, corner finish, glue cleanliness, magnet placement, satin alignment, insert fit, printing clarity, and final packing. Cosmetic packaging is inspected closely by buyers and end customers, so small details matter. A neat corner, centered logo, smooth inner lining, and stable tray can make the box feel much more premium. For repeat orders, keeping the approved sample and production notes helps maintain consistency across future product launches and seasonal campaigns.</p>'
        . '<h2>Request a Custom Pink Cosmetic Gift Box Quote</h2>'
        . '<p>Send the product size, item count, preferred box structure, brand color, logo file, insert requirement, satin lining preference, and target quantity to receive a suitable packaging recommendation. VPN Paper Box can adjust this custom pink cosmetic gift box with satin lining for skincare sets, makeup collections, perfume gifts, spa products, beauty retail kits, and private label cosmetic launches. The result is a rigid beauty package that protects the product, supports brand storytelling, and gives customers a polished gift-ready unboxing experience.</p>';

    return $content;
}

$term = get_term_by('slug', $category_slug, 'product_cat');
if (!$term || is_wp_error($term)) {
    $created = wp_insert_term($category_name, 'product_cat', array('slug' => $category_slug));
    $term_id = is_wp_error($created) ? 0 : (int) $created['term_id'];
} else {
    $term_id = (int) $term->term_id;
}

if (!$term_id) {
    fwrite(STDERR, "Could not find or create category: {$category_slug}\n");
    exit(1);
}

$existing = get_posts(array(
    'name'           => $slug,
    'post_type'      => 'product',
    'post_status'    => array('publish', 'draft', 'pending', 'private', 'trash'),
    'posts_per_page' => 1,
));

$post_data = array(
    'post_title'   => $title,
    'post_name'    => $slug,
    'post_type'    => 'product',
    'post_status'  => 'publish',
    'post_excerpt' => vpn_satin_short_description(),
    'post_content' => '',
);

if ($existing) {
    $product_id = (int) $existing[0]->ID;
    $post_data['ID'] = $product_id;
    wp_untrash_post($product_id);
    wp_update_post($post_data);
} else {
    $product_id = (int) wp_insert_post($post_data);
}

if (!$product_id || is_wp_error($product_id)) {
    fwrite(STDERR, "Could not create product.\n");
    exit(1);
}

wp_set_object_terms($product_id, array($term_id), 'product_cat', false);
wp_set_object_terms($product_id, 'simple', 'product_type');
wp_set_object_terms($product_id, array(
    'cosmetic gift box',
    'pink cosmetic box',
    'satin lining gift box',
    'beauty packaging',
    'custom packaging',
), 'product_tag');

update_post_meta($product_id, '_vpn_sample_import', 'final-category-products');
update_post_meta($product_id, '_regular_price', '');
update_post_meta($product_id, '_price', '');
update_post_meta($product_id, '_stock_status', 'instock');
update_post_meta($product_id, '_manage_stock', 'no');
update_post_meta($product_id, '_visibility', 'visible');
update_post_meta($product_id, 'rank_math_focus_keyword', 'pink cosmetic gift box');
update_post_meta($product_id, 'rank_math_title', $title . ' | VPN PAPER BOX MANUFACTURER');
update_post_meta($product_id, 'rank_math_description', 'Custom pink cosmetic gift box with satin lining for skincare, makeup, fragrance, and beauty gift sets with logo printing and inserts.');
update_post_meta($product_id, '_custom_box_product_specs', vpn_satin_specs($title));

$image_ids = array();

for ($i = 1; $i <= $image_count; $i++) {
    $source = trailingslashit($source_dir) . $slug . '-' . $i . '.webp';

    if (!is_file($source)) {
        echo "Missing source image: {$source}\n";
        continue;
    }

    $attachment_id = vpn_satin_get_or_create_attachment($source, $slug . '-' . $i . '.webp', $product_id);
    if ($attachment_id) {
        update_post_meta($attachment_id, '_wp_attachment_image_alt', 'Pink cosmetic gift box for beauty gift set with satin lining view ' . $i);
    }
    $image_ids[] = $attachment_id;
}

$image_ids = array_values(array_filter(array_map('absint', $image_ids)));

if ($image_ids) {
    set_post_thumbnail($product_id, $image_ids[0]);
    update_post_meta($product_id, '_product_image_gallery', implode(',', array_slice($image_ids, 1)));
}

$content = vpn_satin_build_content($image_ids);
wp_update_post(array(
    'ID'           => $product_id,
    'post_content' => $content,
));

echo 'Created/updated product #' . $product_id . ' ' . $title . PHP_EOL;
echo 'Images attached: ' . implode(', ', $image_ids) . PHP_EOL;
echo 'Long description words: ' . str_word_count(wp_strip_all_tags($content)) . PHP_EOL;
echo 'Specifications rows: ' . count(vpn_satin_specs($title)) . PHP_EOL;
