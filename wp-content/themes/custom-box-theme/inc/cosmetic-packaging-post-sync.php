<?php
/**
 * One-off content sync helpers for the cosmetic packaging guide.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_cosmetic_packaging_post');

/**
 * Syncs image metadata and inline figures after the matching media files exist.
 *
 * This is intentionally idempotent: it can run after each deployment without
 * duplicating figures or publishing the draft post.
 */
function custom_box_sync_cosmetic_packaging_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $sync_version = 'types-of-cosmetic-packaging-20260604-v1';
    $post = get_page_by_path('types-of-cosmetic-packaging', OBJECT, 'post');
    $post_data = custom_box_cosmetic_packaging_post_map();

    if ($post && 'trash' === $post->post_status) {
        return;
    }

    if (!$post) {
        $post_id = wp_insert_post(array(
            'post_title' => $post_data['title'],
            'post_name' => $post_data['slug'],
            'post_type' => 'post',
            'post_status' => 'draft',
            'post_excerpt' => $post_data['excerpt'],
            'post_content' => custom_box_cosmetic_packaging_post_content(),
        ));

        if (!$post_id || is_wp_error($post_id)) {
            return;
        }

        $post = get_post($post_id);
    }

    $missing_option = get_option('custom_box_cosmetic_packaging_missing_images', array());
    if (
        get_post_meta($post->ID, '_custom_box_cosmetic_packaging_sync_version', true) === $sync_version
        && empty($missing_option)
    ) {
        return;
    }

    custom_box_update_cosmetic_packaging_post_details($post->ID, $sync_version);
    $post = get_post($post->ID);

    custom_box_update_cosmetic_packaging_post_seo($post->ID);
    custom_box_update_cosmetic_packaging_post_terms($post->ID);

    $images = custom_box_cosmetic_packaging_image_map();
    $found = array();
    $missing = array();

    foreach ($images as $filename => $image) {
        $attachment_id = custom_box_find_attachment_by_base_filename($image['base']);

        if (!$attachment_id) {
            $missing[] = $filename;
            continue;
        }

        $url = wp_get_attachment_url($attachment_id);

        if (!$url) {
            $missing[] = $filename;
            continue;
        }

        custom_box_update_attachment_seo_metadata($attachment_id, $image);

        $found[$filename] = array(
            'id' => $attachment_id,
            'url' => $url,
            'alt' => $image['alt'],
            'base' => $image['base'],
        );
    }

    update_option('custom_box_cosmetic_packaging_missing_images', $missing, false);

    if (!isset($found['types-of-cosmetic-packaging-guide.png'])) {
        return;
    }

    if ((int) get_post_thumbnail_id($post->ID) !== (int) $found['types-of-cosmetic-packaging-guide.png']['id']) {
        set_post_thumbnail($post->ID, $found['types-of-cosmetic-packaging-guide.png']['id']);
    }

    $content = $post->post_content;
    $updated_content = custom_box_insert_cosmetic_packaging_figures($content, $found);

    if ($updated_content !== $content) {
        wp_update_post(array(
            'ID' => $post->ID,
            'post_content' => $updated_content,
        ));
    }

    update_post_meta($post->ID, '_custom_box_cosmetic_packaging_sync_version', $sync_version);
}

function custom_box_cosmetic_packaging_post_map()
{
    return array(
        'title' => 'Types of Cosmetic Packaging: Practical Guide for Beauty Brands',
        'slug' => 'types-of-cosmetic-packaging',
        'excerpt' => 'A practical guide to cosmetic packaging types, materials and paper-based box options for beauty and skincare brands.',
        'categories' => array(
            array(
                'name' => 'Packaging Guides',
                'slug' => 'packaging-guides',
            ),
            array(
                'name' => 'Cosmetic Packaging',
                'slug' => 'cosmetic-packaging',
            ),
        ),
        'tags' => array(
            'types of cosmetic packaging',
            'cosmetic packaging',
            'cosmetic packaging types',
            'beauty packaging',
            'skincare packaging',
            'paper cosmetic boxes',
        ),
    );
}

function custom_box_update_cosmetic_packaging_post_details($post_id, $sync_version)
{
    $post = get_post($post_id);
    $post_data = custom_box_cosmetic_packaging_post_map();

    if (!$post) {
        return;
    }

    $post_update = array(
        'ID' => $post_id,
        'post_title' => $post_data['title'],
        'post_name' => $post_data['slug'],
        'post_excerpt' => $post_data['excerpt'],
    );

    if (get_post_meta($post_id, '_custom_box_cosmetic_packaging_sync_version', true) !== $sync_version) {
        $post_update['post_content'] = custom_box_cosmetic_packaging_post_content();
    }

    if (!in_array($post->post_status, array('publish', 'private'), true)) {
        $post_update['post_status'] = 'draft';
    }

    wp_update_post($post_update);
}

function custom_box_update_cosmetic_packaging_post_seo($post_id)
{
    $seo = custom_box_cosmetic_packaging_seo_map();

    update_post_meta($post_id, 'rank_math_title', $seo['title']);
    update_post_meta($post_id, 'rank_math_description', $seo['description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $seo['focus_keyword']);
}

function custom_box_cosmetic_packaging_seo_map()
{
    return array(
        'title' => 'Types of Cosmetic Packaging: Guide for Beauty Brands',
        'description' => 'Explore the main types of cosmetic packaging, from bottles and jars to folding cartons, rigid boxes, inserts, paper bags and materials for beauty brands.',
        'focus_keyword' => 'types of cosmetic packaging, cosmetic packaging types, cosmetic packaging',
    );
}

function custom_box_update_cosmetic_packaging_post_terms($post_id)
{
    $post_data = custom_box_cosmetic_packaging_post_map();
    $category_ids = array();

    foreach ($post_data['categories'] as $category) {
        $term = get_term_by('slug', $category['slug'], 'category');

        if (!$term) {
            $created = wp_insert_term($category['name'], 'category', array('slug' => $category['slug']));

            if (is_wp_error($created)) {
                continue;
            }

            $category_ids[] = (int) $created['term_id'];
            continue;
        }

        $category_ids[] = (int) $term->term_id;
    }

    if (!empty($category_ids)) {
        wp_set_post_categories($post_id, $category_ids, false);
    }

    wp_set_post_terms($post_id, $post_data['tags'], 'post_tag', false);
}

/**
 * Shows missing-image feedback to admins after deployment.
 */
add_action('admin_notices', 'custom_box_cosmetic_packaging_sync_notice');

function custom_box_cosmetic_packaging_sync_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing = get_option('custom_box_cosmetic_packaging_missing_images', array());

    if (empty($missing) || !is_array($missing)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo esc_html__('Cosmetic packaging post sync is waiting for these uploaded media files:', 'custom-box-theme') . ' ';
    echo esc_html(implode(', ', $missing));
    echo '</p></div>';
}

function custom_box_cosmetic_packaging_image_map()
{
    return array(
        'types-of-cosmetic-packaging-guide.png' => array(
            'base' => 'types-of-cosmetic-packaging-guide',
            'alt' => 'Types of cosmetic packaging guide for beauty brands',
            'title' => 'Types of Cosmetic Packaging Guide',
            'caption' => 'A practical guide to cosmetic packaging types for skincare and beauty brands.',
        ),
        'primary-secondary-tertiary-cosmetic-packaging.png' => array(
            'base' => 'primary-secondary-tertiary-cosmetic-packaging',
            'alt' => 'Primary secondary and tertiary cosmetic packaging layers',
            'title' => 'Primary Secondary Tertiary Cosmetic Packaging',
        ),
        'cosmetic-bottles-jars-tubes-packaging-types.png' => array(
            'base' => 'cosmetic-bottles-jars-tubes-packaging-types',
            'alt' => 'Cosmetic bottles jars and tubes packaging types',
            'title' => 'Cosmetic Bottles Jars Tubes Packaging Types',
        ),
        'folding-carton-cosmetic-boxes.png' => array(
            'base' => 'folding-carton-cosmetic-boxes',
            'alt' => 'Folding carton cosmetic boxes for skincare products',
            'title' => 'Folding Carton Cosmetic Boxes',
        ),
        'rigid-cosmetic-boxes-for-beauty-gift-sets.png' => array(
            'base' => 'rigid-cosmetic-boxes-for-beauty-gift-sets',
            'alt' => 'Rigid cosmetic boxes for premium beauty gift sets',
            'title' => 'Rigid Cosmetic Boxes for Beauty Gift Sets',
        ),
        'cosmetic-paper-inserts-and-dividers.png' => array(
            'base' => 'cosmetic-paper-inserts-and-dividers',
            'alt' => 'Paper inserts and dividers for cosmetic packaging',
            'title' => 'Cosmetic Paper Inserts and Dividers',
        ),
        'paper-based-cosmetic-packaging-solutions.png' => array(
            'base' => 'paper-based-cosmetic-packaging-solutions',
            'alt' => 'Paper based cosmetic packaging solutions for beauty brands',
            'title' => 'Paper Based Cosmetic Packaging Solutions',
        ),
    );
}

function custom_box_find_attachment_by_base_filename($base)
{
    $attachment = get_page_by_path($base, OBJECT, 'attachment');

    if ($attachment) {
        return (int) $attachment->ID;
    }

    global $wpdb;

    $like = '%' . $wpdb->esc_like('/' . $base . '.') . '%';
    $attachment_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC LIMIT 1",
            $like
        )
    );

    if ($attachment_id) {
        return (int) $attachment_id;
    }

    $like = $wpdb->esc_like($base) . '%';

    return (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_name LIKE %s ORDER BY ID DESC LIMIT 1",
            $like
        )
    );
}

function custom_box_update_attachment_seo_metadata($attachment_id, $image)
{
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']);

    $post_update = array(
        'ID' => $attachment_id,
        'post_title' => $image['title'],
    );

    if (isset($image['caption'])) {
        $post_update['post_excerpt'] = $image['caption'];
    }

    wp_update_post($post_update);
}

function custom_box_insert_cosmetic_packaging_figures($content, $found)
{
    $insertions = array(
        'primary-secondary-tertiary-cosmetic-packaging.png' => array(
            'pattern' => '~(<h3[^>]*>\s*<span[^>]*>\s*Primary Packaging\s*</span>\s*</h3>\s*<p[^>]*>.*?</p>)~s',
        ),
        'cosmetic-bottles-jars-tubes-packaging-types.png' => array(
            'pattern' => '~(<h2[^>]*>\s*<span[^>]*>\s*Main Types of Cosmetic Packaging\s*</span>\s*</h2>)~s',
        ),
        'folding-carton-cosmetic-boxes.png' => array(
            'pattern' => '~(<h3[^>]*>\s*<span[^>]*>\s*Folding Carton Boxes for Cosmetic Products\s*</span>\s*</h3>)~s',
        ),
        'rigid-cosmetic-boxes-for-beauty-gift-sets.png' => array(
            'pattern' => '~(<h3[^>]*>\s*<span[^>]*>\s*Rigid Cosmetic Boxes for Premium Beauty Sets\s*</span>\s*</h3>)~s',
        ),
        'cosmetic-paper-inserts-and-dividers.png' => array(
            'pattern' => '~(<h3[^>]*>\s*<span[^>]*>\s*Paper Sleeves, Inserts and Dividers\s*</span>\s*</h3>)~s',
        ),
        'paper-based-cosmetic-packaging-solutions.png' => array(
            'pattern' => '~(<h2[^>]*>\s*<span[^>]*>\s*When Paper-Based Cosmetic Packaging Is a Good Choice\s*</span>\s*</h2>\s*<p[^>]*>.*?</p>)~s',
        ),
    );

    foreach ($insertions as $filename => $config) {
        if (!isset($found[$filename])) {
            continue;
        }

        if (strpos($content, $found[$filename]['url']) !== false) {
            continue;
        }

        $content = custom_box_remove_existing_figure_for_image($content, $found[$filename]['base']);
        $figure = custom_box_cosmetic_packaging_figure($found[$filename]['url'], $found[$filename]['alt']);
        $content = preg_replace($config['pattern'], '$1' . $figure, $content, 1);
    }

    return $content;
}

function custom_box_remove_existing_figure_for_image($content, $base)
{
    $quoted_base = preg_quote($base, '~');

    $content = preg_replace('~\s*<figure>\s*<img[^>]+src="[^"]*' . $quoted_base . '[^"]*"[^>]*>\s*</figure>\s*~', "\n", $content);

    return $content;
}

function custom_box_cosmetic_packaging_figure($url, $alt)
{
    return "\n<figure>\n  <img src=\"" . esc_url($url) . "\" alt=\"" . esc_attr($alt) . "\" style=\"width:100%; height:auto;\" loading=\"lazy\" decoding=\"async\">\n</figure>\n";
}

function custom_box_cosmetic_packaging_post_content()
{
    return <<<'HTML'
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Cosmetic packaging is much more than the container that holds a beauty product. It protects the formula, communicates the brand image, supports retail display, improves the unboxing experience and helps customers understand how to use the product correctly. For beauty brands, skincare startups, product managers and packaging buyers, choosing the right type of cosmetic packaging can affect both product performance and customer perception.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Different cosmetic products require different packaging decisions. A serum may need a dropper bottle and a printed paper box. A face cream may need a jar, an inner liner and a folding carton. A premium skincare set may need a rigid cosmetic box with inserts and a matching paper bag. This guide explains the main types of cosmetic packaging, how they are used, what materials to consider and how to choose the right option for different beauty products.</span></p>

<h2 style="font-size: 22px; line-height: 1.45;"><span style="font-size: 110%;">What Is Cosmetic Packaging?</span></h2>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Cosmetic packaging refers to the complete packaging system used to contain, protect, present, transport and sell cosmetic or beauty products. It can include the container that directly touches the product, the outer box that displays branding and product information, and the shipping packaging used during storage and delivery.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Good cosmetic packaging has several roles. It helps protect the product from leakage, breakage, contamination, light exposure or handling damage. It also supports brand communication through color, logo placement, typography, materials and finishing. For retail and e-commerce brands, packaging also affects how professional the product looks before the customer even tries the formula inside.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Because cosmetics are often visual, personal and experience-driven products, packaging design must balance function and emotion. A package should be practical enough for production and shipping, but also attractive enough to create trust and interest at the first touchpoint.</span></p>

<h2 style="font-size: 22px; line-height: 1.45;"><span style="font-size: 110%;">Primary, Secondary and Tertiary Cosmetic Packaging</span></h2>
<h3 style="font-size: 18px; line-height: 1.45;"><span style="font-size: 110%;">Primary Packaging</span></h3>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Primary packaging is the packaging that directly contacts or holds the cosmetic formula. It is the first layer of protection and must be suitable for the product?s texture, ingredients, viscosity and usage method. Common examples include bottles, jars, tubes, pumps, droppers, compacts, sticks and palettes.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">For example, a toner may use a bottle, a moisturizer may use a jar or tube, a serum may use a dropper or pump bottle, and a foundation may use a compact or airless pump. The main priority of primary packaging is product compatibility, hygiene, dispensing control and user convenience.</span></p>

<h3 style="font-size: 18px; line-height: 1.45;"><span style="font-size: 110%;">Secondary Packaging</span></h3>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Secondary packaging is the outer packaging that holds or surrounds the primary container. In cosmetics, this often includes folding carton cosmetic boxes, rigid cosmetic boxes, paper sleeves, paper inserts, dividers and printed outer boxes. This layer is important for branding, product information, shelf presentation and additional protection.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">For many beauty brands, secondary packaging is where the brand story becomes visible. It gives space for the logo, product name, ingredients, usage instructions, barcode, certification icons, color system and visual identity. Paper-based secondary packaging is especially common for skincare, makeup, perfume, gift sets and retail beauty products.</span></p>

<h3 style="font-size: 18px; line-height: 1.45;"><span style="font-size: 110%;">Tertiary Packaging</span></h3>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Tertiary packaging is used for transportation, warehouse handling and bulk shipping. It is usually not seen by the final consumer. Examples include shipping cartons, outer cartons, corrugated boxes, pallets, protective fillers and export packing materials.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">For international buyers and e-commerce sellers, tertiary packaging is still important because cosmetic items may travel through long supply chains. A beautiful retail box can still arrive damaged if the outer shipping packaging is not planned correctly.</span></p>

<h2 style="font-size: 22px; line-height: 1.45;"><span style="font-size: 110%;">Main Types of Cosmetic Packaging</span></h2>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Cosmetic packaging can include both the primary container and the outer packaging used for branding, protection and retail presentation. The table below gives a quick comparison of common cosmetic packaging types before we look at each option in more detail.</span></p>

<table style="width: 100%; border-collapse: collapse; margin: 24px 0; font-size: 16px; line-height: 1.6;">
<thead>
<tr>
<th style="border: 1px solid #ddd; padding: 12px; text-align: left;"><span style="font-size: 110%;">Packaging Type</span></th>
<th style="border: 1px solid #ddd; padding: 12px; text-align: left;"><span style="font-size: 110%;">Common Uses</span></th>
<th style="border: 1px solid #ddd; padding: 12px; text-align: left;"><span style="font-size: 110%;">Main Advantage</span></th>
<th style="border: 1px solid #ddd; padding: 12px; text-align: left;"><span style="font-size: 110%;">Best For</span></th>
</tr>
</thead>
<tbody>
<tr>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Bottles</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Toners, lotions, shampoos, liquid skincare</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Easy dispensing and daily use</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Liquid and semi-liquid products</span></td>
</tr>
<tr>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Jars</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Creams, balms, scrubs, masks</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Premium feel and easy product access</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Thicker formulas and treatment products</span></td>
</tr>
<tr>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Tubes</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Cleansers, sunscreen, hand cream, gels</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Lightweight, convenient and portable</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Travel-size and everyday-use cosmetics</span></td>
</tr>
<tr>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Droppers and Pumps</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Serums, oils, essences, treatment products</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Better dosage control and premium usage experience</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">High-value skincare formulas</span></td>
</tr>
<tr>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Folding Carton Boxes</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Skincare, lipstick, cream, serum, small beauty products</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Good printing area, lightweight and scalable</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Retail cosmetic packaging and branded outer boxes</span></td>
</tr>
<tr>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Rigid Cosmetic Boxes</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Luxury skincare sets, perfume sets, PR kits, gift boxes</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Premium presentation and stronger structure</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">High-end beauty sets and gift packaging</span></td>
</tr>
<tr>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Paper Sleeves and Inserts</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Gift sets, bundles, multi-item cosmetic kits</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Keeps products organized and improves unboxing</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Cosmetic sets and paper-based packaging layouts</span></td>
</tr>
<tr>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Paper Bags</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Retail stores, beauty events, gift packaging</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Extends brand experience after purchase</span></td>
<td style="border: 1px solid #ddd; padding: 12px;"><span style="font-size: 110%;">Retail cosmetic stores and promotional gift sets</span></td>
</tr>
</tbody>
</table>
<h3 style="font-size: 18px; line-height: 1.45;"><span style="font-size: 110%;">Bottles for Lotions, Toners and Liquid Products</span></h3>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Bottles are one of the most common cosmetic packaging types for liquid and semi-liquid products. They are often used for toners, lotions, shampoos, conditioners, body wash, cleansing water and liquid skincare formulas. Depending on the formula, bottles may come with screw caps, flip-top caps, pumps, sprays or dispensing closures.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Bottles are practical when the product needs controlled pouring or repeated daily use. However, beauty brands should consider the bottle shape, cap quality, leakage resistance and how the bottle will be displayed inside a paper box or retail shelf.</span></p>

<h3 style="font-size: 18px; line-height: 1.45;"><span style="font-size: 110%;">Jars for Creams, Balms and Masks</span></h3>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Jars are commonly used for creams, balms, scrubs, masks and thicker cosmetic formulas. They often create a premium feeling because customers can see and access the product easily. Wide-mouth jars are especially popular for moisturizers, body butters and treatment masks.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">The main considerations for jars include hygiene, sealing, inner liners and product protection. If the jar is heavy or fragile, the outer cosmetic paper box should be designed with suitable thickness, structure and insert support.</span></p>

<h3 style="font-size: 18px; line-height: 1.45;"><span style="font-size: 110%;">Tubes for Gels, Creams and Cleansers</span></h3>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Tubes are widely used for facial cleansers, sunscreen, hand cream, gel products, lotions and travel-size cosmetics. They are convenient, lightweight and easy to squeeze. For customers, tubes are practical because they are simple to carry and use.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">From a secondary packaging perspective, tubes are often packed in folding cartons. The carton helps improve shelf presentation, creates more printable surface area and makes the product feel more complete in retail environments.</span></p>

<h3 style="font-size: 18px; line-height: 1.45;"><span style="font-size: 110%;">Droppers and Pump Packaging for Serums</span></h3>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Droppers and pumps are common for serums, oils, essences and treatment products. These packaging types help control dosage and create a more precise application experience. A serum with a dropper bottle often feels more professional and targeted than a simple open bottle.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Because serum bottles are often small and sometimes made from glass, the outer box must protect the product while presenting it clearly. A well-printed paper box can communicate active ingredients, usage instructions and brand positioning without making the primary container too crowded.</span></p>

<h3 style="font-size: 18px; line-height: 1.45;"><span style="font-size: 110%;">Folding Carton Boxes for Cosmetic Products</span></h3>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Folding carton cosmetic boxes are one of the most important types of packaging for cosmetics, especially for skincare, lipstick, cream, serum, perfume samples, facial masks and small beauty products. They are usually made from paperboard and can be printed with brand colors, product information, logo, instructions and visual design elements.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Folding cartons are popular because they are flexible, lightweight and suitable for many product sizes. They can be designed as tuck-end boxes, straight tuck boxes, reverse tuck boxes, auto-lock bottom boxes or custom die-cut structures. For brands that need scalable retail packaging, folding cartons are often a practical choice.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">For more details about printed paper box solutions, beauty brands can also read this guide on <a href="https://hopgiayvpn.com/custom-printed-paper-boxes-with-logo-wholesale/">custom printed paper boxes with logo wholesale</a>.</span></p>

<h3 style="font-size: 18px; line-height: 1.45;"><span style="font-size: 110%;">Rigid Cosmetic Boxes for Premium Beauty Sets</span></h3>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Rigid cosmetic boxes are used when a brand wants a more premium presentation. They are often selected for luxury skincare sets, perfume sets, beauty gift boxes, influencer PR kits and high-value cosmetic collections. Compared with folding cartons, rigid boxes are thicker, stronger and more suitable for premium unboxing experiences.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Common rigid box structures include lid-and-base boxes, magnetic closure boxes, drawer boxes and book-style boxes. These structures can be combined with paper inserts or dividers to hold multiple cosmetic items in place. Rigid boxes are usually more expensive than standard folding cartons, so they are best suited for products where brand positioning and perceived value are important.</span></p>

<h3 style="font-size: 18px; line-height: 1.45;"><span style="font-size: 110%;">Paper Sleeves, Inserts and Dividers</span></h3>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Paper sleeves, inserts and dividers help organize and protect cosmetic products inside a box. They are useful for beauty gift sets, skincare bundles, serum sets, perfume sets and promotional kits. A good insert keeps each item in the correct position and improves the overall product layout when the box is opened.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Paper inserts can be designed from paperboard, corrugated paperboard or rigid board depending on product weight and protection needs. For brands that want a paper-based packaging experience, inserts are a practical alternative to excessive plastic trays.</span></p>

<h3 style="font-size: 18px; line-height: 1.45;"><span style="font-size: 110%;">Paper Bags for Cosmetic Retail and Gift Packaging</span></h3>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Paper bags are often used by cosmetic stores, beauty retailers, gift shops, events and promotional campaigns. A printed paper bag can extend the brand experience after purchase and make the product feel more giftable. For cosmetic sets, paper bags are often matched with the box design to create a consistent retail presentation.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Beauty brands that sell through retail stores or events may find this guide on <a href="https://hopgiayvpn.com/custom-paper-bags-with-logo-wholesale-guide/">custom paper bags with logo wholesale</a> useful when planning matching bags for cosmetic packaging.</span></p>

<h2 style="font-size: 22px; line-height: 1.45;"><span style="font-size: 110%;">Cosmetic Packaging Materials to Consider</span></h2>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Cosmetic packaging materials depend on whether the packaging is primary, secondary or tertiary. Primary packaging may use glass, plastic, aluminum or other materials depending on the formula. For example, liquid products may require bottles or tubes, while creams may require jars or airless pumps.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">For paper-based secondary packaging, common materials include ivory board, art paper, kraft paper, greyboard, corrugated paperboard and specialty paper. Ivory board is often used for folding carton cosmetic boxes because it offers a smooth printing surface. Kraft paper gives a natural and simple appearance. Greyboard is commonly used inside rigid boxes because it provides thickness and structure. Corrugated paperboard may be used for stronger outer protection or inserts.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">The right paper material depends on the product weight, printing requirements, box structure, budget and brand image. Buyers who want to compare different paper options can refer to this guide on <a href="https://hopgiayvpn.com/paper-materials-for-custom-paper-boxes/">paper materials for custom paper boxes</a>.</span></p>

<h2 style="font-size: 22px; line-height: 1.45;"><span style="font-size: 110%;">How to Choose the Right Type of Cosmetic Packaging</span></h2>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Choosing the right type of packaging for cosmetics starts with the product itself. A liquid toner, a cream jar, a lipstick, a perfume bottle and a skincare gift set all have different packaging needs. The product form, size, weight and fragility should guide the first decision.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Protection is another important factor. Glass bottles, serum droppers and multi-item sets may need stronger outer boxes or inserts. Products sold through e-commerce may need more shipping protection than products sold only in retail stores. If the package must travel internationally, both secondary and tertiary packaging should be planned together.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Brand positioning also matters. A mass-market cleanser may only need a simple folding carton, while a luxury skincare set may need a rigid box with a custom insert. Budget and quantity should be considered early because different structures, materials and finishing options can significantly affect production cost.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Sustainability goals can also influence packaging choices. Some brands may prefer paper-based secondary packaging, recyclable paperboard, reduced plastic inserts or simpler structures that use fewer materials. The best decision is not always the most expensive option. It is the option that fits the product, brand, channel and customer expectation.</span></p>

<h2 style="font-size: 22px; line-height: 1.45;"><span style="font-size: 110%;">Cosmetic Packaging Design Tips for Beauty Brands</span></h2>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Cosmetic packaging design should be clear, attractive and practical. A strong design usually starts with a consistent color palette, readable typography and well-placed logo. For small cosmetic boxes, too much text or decoration can make the package look crowded. Important information should be organized so customers can understand the product quickly.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Material texture also affects the final impression. Matte lamination can create a soft, modern look. Gloss lamination can make colors appear more vibrant. Foil stamping, embossing, debossing and spot UV can highlight selected design elements, but they should be used carefully. Too many finishing effects can make a cosmetic box look less refined.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">For beauty product packaging, the unboxing experience is also important. The box should open smoothly, hold the product securely and present the item in a clean layout. If the package contains multiple items, inserts and dividers should be designed around the actual product dimensions, not only the visual concept.</span></p>

<h2 style="font-size: 22px; line-height: 1.45;"><span style="font-size: 110%;">Common Mistakes When Choosing Cosmetic Packaging</span></h2>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">One common mistake is choosing packaging only because it looks beautiful in a mockup. A design may look attractive on screen but fail in real production if the material, structure or dimensions are not suitable. Cosmetic packaging must be tested with the actual product size and weight.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Another mistake is ignoring shipping conditions. A thin paper box may look fine for shelf display, but it may not protect a glass bottle during e-commerce delivery without proper inserts or outer cartons. Brands should think about the full journey from factory to warehouse, retail store and final customer.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Poor artwork quality is also a frequent issue. Low-resolution logos, unclear text, incorrect color settings or crowded layouts can reduce the final packaging quality. Before mass production, brands should review dielines, sample boxes and printed proofs carefully.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Using too many finishing effects is another risk. Foil, embossing, spot UV and special textures can be attractive, but they should support the brand message. A clean, well-balanced design often performs better than a package overloaded with decorative details.</span></p>

<h2 style="font-size: 22px; line-height: 1.45;"><span style="font-size: 110%;">When Paper-Based Cosmetic Packaging Is a Good Choice</span></h2>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Paper-based cosmetic packaging is a good choice when a beauty brand needs strong secondary packaging, clear logo printing, retail display, gift presentation or a more paper-focused brand experience. It is especially suitable for folding carton cosmetic boxes, rigid cosmetic boxes, paper sleeves, paper inserts and paper bags.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Paper packaging does not replace every type of cosmetic packaging. For example, liquid formulas still need suitable primary containers such as bottles, tubes, jars or pumps. However, paper boxes and paper bags can make the product more complete, easier to present and more professional in retail or e-commerce channels.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">For skincare brands, paper boxes are often used for serums, creams, masks, essential oils, lip products and gift sets. For beauty retailers, paper bags and rigid boxes can help create a more memorable customer experience, especially during seasonal campaigns, product launches or gift promotions.</span></p>

<h2 style="font-size: 22px; line-height: 1.45;"><span style="font-size: 110%;">Why Work with VPN Paper Box Manufacturer?</span></h2>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;"><a href="https://hopgiayvpn.com/">VPN Paper Box Manufacturer</a> supports custom paper packaging solutions for beauty and skincare brands, including folding carton boxes, rigid cosmetic boxes, paper bags, paper sleeves and paper inserts. For brands that need paper-based secondary packaging, working with a manufacturer can help turn product dimensions, artwork and packaging ideas into practical box structures.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">As a Vietnam-based paper packaging manufacturer, VPN focuses on custom paper boxes and paper bags for B2B buyers, importers, retail brands and packaging buyers. International buyers who are comparing sourcing locations can also read more about <a href="https://hopgiayvpn.com/why-choose-vietnam-for-export-paper-box-manufacturing/">why businesses choose Vietnam for export paper box manufacturing</a>.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">The best way to develop custom cosmetic packaging is to start with clear product details: product type, size, weight, quantity, artwork, packaging structure, shipping method and target retail channel. These details help the packaging supplier recommend a more suitable paper material, box structure and finishing approach.</span></p>

<h2 style="font-size: 22px; line-height: 1.45;"><span style="font-size: 110%;">Frequently Asked Questions</span></h2>
<h3 style="font-size: 18px; line-height: 1.45;"><span style="font-size: 110%;">What are the main types of cosmetic packaging?</span></h3>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">The main types of cosmetic packaging include primary packaging, secondary packaging and tertiary packaging. Common examples include bottles, jars, tubes, pumps, droppers, compacts, folding carton boxes, rigid boxes, paper sleeves, inserts, dividers, labels, pouches, paper bags and shipping cartons.</span></p>

<h3 style="font-size: 18px; line-height: 1.45;"><span style="font-size: 110%;">What is the difference between primary and secondary cosmetic packaging?</span></h3>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Primary packaging directly holds or touches the cosmetic formula, such as a bottle, jar, tube or compact. Secondary packaging is the outer layer, such as a paper box, sleeve or rigid box, used for branding, protection, product information and retail presentation.</span></p>

<h3 style="font-size: 18px; line-height: 1.45;"><span style="font-size: 110%;">Are paper boxes suitable for cosmetic packaging?</span></h3>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Yes, paper boxes are widely used as secondary packaging for cosmetics. They are suitable for skincare bottles, cream jars, lipstick, perfume, masks, beauty sets and gift packaging. The right paper material and structure should be chosen based on product size, weight and protection needs.</span></p>

<h3 style="font-size: 18px; line-height: 1.45;"><span style="font-size: 110%;">What type of packaging is best for skincare products?</span></h3>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">The best packaging for skincare depends on the formula. Toners and lotions often use bottles, creams may use jars or tubes, serums often use droppers or pumps, and outer paper boxes are commonly used for branding and retail display.</span></p>

<h3 style="font-size: 18px; line-height: 1.45;"><span style="font-size: 110%;">What materials are commonly used for cosmetic paper boxes?</span></h3>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Common materials for cosmetic paper boxes include ivory board, art paper, kraft paper, greyboard and corrugated paperboard. Folding cartons often use printable paperboard, while rigid cosmetic boxes usually use greyboard wrapped with printed paper.</span></p>

<h3 style="font-size: 18px; line-height: 1.45;"><span style="font-size: 110%;">How do I choose packaging for a new beauty brand?</span></h3>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">Start with the product type, size, formula, sales channel and brand positioning. Then decide the primary container, secondary paper box or bag, protection requirements, design style, budget and production quantity. It is also recommended to review samples before mass production.</span></p>

<h2 style="font-size: 22px; line-height: 1.45;"><span style="font-size: 110%;">Conclusion</span></h2>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">There is no single best packaging type for every cosmetic product. The right choice depends on the formula, product size, protection needs, customer experience, brand positioning, sales channel and budget. A serum, cream, lipstick, perfume and skincare gift set may each need a different combination of primary, secondary and tertiary packaging.</span></p>
<p style="font-size: 16px; line-height: 1.8;"><span style="font-size: 110%;">For beauty brands planning paper-based cosmetic packaging, folding cartons, rigid boxes, paper sleeves, inserts and paper bags can play an important role in presentation and protection. If you need custom paper-based cosmetic packaging, you can contact VPN Paper Box Manufacturer and share your product dimensions, quantity, artwork, packaging style and shipping requirements for further discussion.</span></p>
HTML;
}
