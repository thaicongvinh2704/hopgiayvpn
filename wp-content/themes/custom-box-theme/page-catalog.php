<?php
/**
 * Template Name: Catalog Page
 *
 * B2B catalog and factory capability page for VPN Packaging.
 */

$catalog_title = 'Custom Paper Box Catalog | VPN Packaging Factory';
$catalog_description = 'Explore VPN Packaging Factory\'s custom paper box catalog, including rigid boxes, folding cartons, cosmetic boxes, gift boxes, food packaging boxes, materials, printing finishes, and OEM/ODM paper packaging solutions for global B2B customers.';

add_filter('pre_get_document_title', function () use ($catalog_title) {
    return $catalog_title;
});

add_action('wp_head', function () use ($catalog_description) {
    if (defined('RANK_MATH_VERSION')) {
        return;
    }

    printf('<meta name="description" content="%s">' . "\n", esc_attr($catalog_description));
}, 1);

get_header();

$theme_uri = get_template_directory_uri();
$catalog_url = home_url('/catalog/#catalog-preview');
$catalog_external_url = 'https://online.fliphtml5.com/ibmst/ybfa/index.html#p=4';
$profile_url = 'https://online.fliphtml5.com/ibmst/sgqj/#p=1';
$contact_url = home_url('/contact/#quote');
$quote_url = home_url('/contact/#quote');

if (!function_exists('vpn_catalog_category_image')) {
    function vpn_catalog_category_image($slugs, $fallback) {
        $slugs = (array) $slugs;

        foreach ($slugs as $slug) {
            $term = get_term_by('slug', $slug, 'product_cat');

            if (!$term || is_wp_error($term)) {
                continue;
            }

            $thumbnail_id = (int) get_term_meta($term->term_id, 'thumbnail_id', true);

            if (!$thumbnail_id) {
                $thumbnail_id = (int) get_term_meta($term->term_id, 'custom_box_category_image_id', true);
            }

            if ($thumbnail_id) {
                $image_url = wp_get_attachment_image_url($thumbnail_id, 'medium_large');

                if ($image_url) {
                    return $image_url;
                }
            }

            if (function_exists('wc_get_products')) {
                $products = wc_get_products(array(
                    'status' => 'publish',
                    'limit' => 1,
                    'category' => array($slug),
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'return' => 'ids',
                ));

                if (!empty($products)) {
                    $product_image_id = get_post_thumbnail_id((int) $products[0]);
                    $product_image_url = $product_image_id ? wp_get_attachment_image_url($product_image_id, 'medium_large') : '';

                    if ($product_image_url) {
                        return $product_image_url;
                    }
                }
            }
        }

        return $fallback;
    }
}

if (!function_exists('vpn_catalog_featured_products')) {
    function vpn_catalog_featured_products($fallback_gallery, $limit = 6) {
        if (!function_exists('wc_get_products')) {
            return $fallback_gallery;
        }

        $products = wc_get_products(array(
            'status'   => 'publish',
            'featured' => true,
            'limit'    => $limit,
            'orderby'  => 'menu_order',
            'order'    => 'ASC',
        ));

        if (count($products) < $limit) {
            $fallback_products = wc_get_products(array(
                'status'  => 'publish',
                'limit'   => $limit,
                'orderby' => 'date',
                'order'   => 'DESC',
                'exclude' => wp_list_pluck($products, 'id'),
            ));

            $products = array_merge($products, $fallback_products);
        }

        $gallery = array();

        foreach (array_slice($products, 0, $limit) as $product) {
            if (!$product instanceof WC_Product) {
                continue;
            }

            $image_id = $product->get_image_id();
            $image = $image_id ? wp_get_attachment_image_url($image_id, 'medium_large') : '';

            if (!$image) {
                continue;
            }

            $category_names = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names'));
            $category_label = (!is_wp_error($category_names) && !empty($category_names)) ? reset($category_names) : __('Custom Packaging', 'custom-box-theme');

            $gallery[] = array(
                'image' => $image,
                'alt'   => $product->get_name(),
                'title' => $product->get_name(),
                'meta'  => $category_label,
                'url'   => get_permalink($product->get_id()),
            );
        }

        return !empty($gallery) ? $gallery : $fallback_gallery;
    }
}

$fallback_gallery = array(
    array(
        'image' => $theme_uri . '/assets/images/Rigid-Packaging.webp',
        'alt' => 'custom rigid drawer box for luxury packaging',
        'title' => 'Rigid Drawer Box Packaging',
        'meta' => 'Luxury rigid boxes',
        'url' => home_url('/packaging/rigid-sliding-drawer-boxes/'),
    ),
    array(
        'image' => $theme_uri . '/assets/images/innerwear-Feature.webp',
        'alt' => 'custom cosmetic paper box manufacturer',
        'title' => 'Cosmetic Paper Box Packaging',
        'meta' => 'Cosmetic packaging',
        'url' => home_url('/packaging/cosmetic-set-packaging-boxes/'),
    ),
    array(
        'image' => $theme_uri . '/assets/images/SBS-Paperboard-Packaging.webp',
        'alt' => 'folding carton box for skincare products',
        'title' => 'Folding Carton Packaging',
        'meta' => 'Retail paper boxes',
        'url' => home_url('/products/'),
    ),
    array(
        'image' => $theme_uri . '/assets/images/gift-box2.jpg',
        'alt' => 'custom gift paper box packaging',
        'title' => 'Premium Gift Box Packaging',
        'meta' => 'Gift packaging',
        'url' => home_url('/packaging/luxury-rigid-gift-boxes/'),
    ),
    array(
        'image' => $theme_uri . '/assets/images/Kraft-Packaging.webp',
        'alt' => 'paper shopping bag manufacturer',
        'title' => 'Kraft Paper Packaging',
        'meta' => 'Eco paper packaging',
        'url' => home_url('/packaging/printed-paper-shopping-bags/'),
    ),
    array(
        'image' => $theme_uri . '/assets/images/Takeout-Boxes_1758880241.jpg',
        'alt' => 'food packaging paper box factory',
        'title' => 'Food Paper Box Packaging',
        'meta' => 'Food-grade packaging',
        'url' => home_url('/packaging/bakery-food-packaging-boxes/'),
    ),
);

$catalog_context = array(
    'theme_uri' => $theme_uri,
    'catalog_url' => $catalog_url,
    'catalog_external_url' => $catalog_external_url,
    'profile_url' => $profile_url,
    'contact_url' => $contact_url,
    'quote_url' => $quote_url,
    'hero_image' => $theme_uri . '/assets/images/product-banner1.png',
    'categories' => array(
        array(
            'title' => 'Rigid Boxes',
            'description' => 'Premium rigid paper boxes for luxury gifts, cosmetics, jewelry, watches, and high-value retail products.',
            'image' => vpn_catalog_category_image(array('luxury-rigid-gift-boxes', 'rigid-sliding-drawer-boxes', 'luxury-drawer-gift-boxes'), $theme_uri . '/assets/images/gift-box2.jpg'),
            'alt' => 'custom rigid paper box manufacturer vietnam',
            'url' => home_url('/packaging/luxury-rigid-gift-boxes/'),
        ),
        array(
            'title' => 'Folding Carton Boxes',
            'description' => 'Custom printed folding carton boxes for retail, cosmetic, food, and consumer product packaging.',
            'image' => $theme_uri . '/assets/images/Tuck-Top-Boxes_1758880242.jpg',
            'alt' => 'folding carton boxes factory vietnam',
            'url' => home_url('/products/'),
        ),
        array(
            'title' => 'Cosmetic Paper Boxes',
            'description' => 'Custom cosmetic and skincare packaging boxes with brand logo printing and premium finishing options.',
            'image' => vpn_catalog_category_image(array('cosmetic-set-packaging-boxes', 'luxury-perfume-packaging-boxes', 'cosmetic-mailer-packaging-boxes'), $theme_uri . '/assets/images/gift-box.png'),
            'alt' => 'custom cosmetic paper box packaging',
            'url' => home_url('/packaging/cosmetic-set-packaging-boxes/'),
        ),
        array(
            'title' => 'Food Packaging Boxes',
            'description' => 'Food-grade paper packaging boxes for bakery, confectionery, beverage, and retail food products.',
            'image' => vpn_catalog_category_image(array('bakery-food-packaging-boxes', 'custom-cake-packaging-boxes', 'pizza-packaging-boxes', 'custom-chocolate-gift-boxes'), $theme_uri . '/assets/images/Takeout-Boxes_1758880241.jpg'),
            'alt' => 'food paper packaging boxes manufacturer',
            'url' => home_url('/packaging/bakery-food-packaging-boxes/'),
        ),
        array(
            'title' => 'Luxury Gift Boxes',
            'description' => 'High-end paper gift boxes with rigid structure, foil stamping, embossing, and custom inserts.',
            'image' => vpn_catalog_category_image(array('luxury-drawer-gift-boxes', 'luxury-rigid-gift-boxes', 'premium-ribbon-gift-boxes', 'mooncake-gift-packaging-boxes'), $theme_uri . '/assets/images/gift-box2.jpg'),
            'alt' => 'luxury gift paper boxes factory',
            'url' => home_url('/packaging/luxury-rigid-gift-boxes/'),
        ),
        array(
            'title' => 'Jewelry & Watch Boxes',
            'description' => 'Elegant paper packaging boxes for jewelry, watches, accessories, and premium retail products.',
            'image' => vpn_catalog_category_image(array('watch-packaging-boxes', 'luxury-watch-packaging-boxes'), $theme_uri . '/assets/images/Rigid-Packaging.webp'),
            'alt' => 'jewelry watch paper box packaging',
            'url' => home_url('/packaging/watch-packaging-boxes/'),
        ),
        array(
            'title' => 'Wine & Beverage Boxes',
            'description' => 'Custom paper boxes for wine, beverage, bottle packaging, and gift packaging projects.',
            'image' => vpn_catalog_category_image(array('luxury-wine-bottle-packaging-boxes', 'wine-bottle-gift-boxes'), $theme_uri . '/assets/images/gift-box.png'),
            'alt' => 'wine beverage paper box manufacturer',
            'url' => home_url('/packaging/luxury-wine-bottle-packaging-boxes/'),
        ),
        array(
            'title' => 'Paper Shopping Bags',
            'description' => 'Custom printed paper bags for retail stores, fashion brands, gifts, and promotional packaging.',
            'image' => vpn_catalog_category_image(array('printed-paper-shopping-bags', 'luxury-retail-paper-bags', 'custom-red-paper-bags', 'luxury-teal-paper-bags'), $theme_uri . '/assets/images/custom-cardboard-boxes.webp'),
            'alt' => 'custom paper shopping bags vietnam',
            'url' => home_url('/packaging/printed-paper-shopping-bags/'),
        ),
    ),
    'box_styles' => array(
        'Lid and Base Box',
        'Magnetic Closure Box',
        'Drawer Box',
        'Folding Carton Box',
        'Sleeve Box',
        'Rigid Gift Box',
    ),
    'materials' => array(
        'Art Paper',
        'Ivory Paper',
        'Kraft Paper',
        'Duplex Paper',
        'Corrugated Paper',
        'Rigid Greyboard',
        'Specialty Paper',
        'Recycled Paper',
    ),
    'finishes' => array(
        'Offset Printing',
        'CMYK Printing',
        'Pantone Color Printing',
        'Foil Stamping',
        'Embossing',
        'Debossing',
        'Spot UV',
        'Matte Lamination',
        'Gloss Lamination',
        'Soft Touch Lamination',
        'Die Cutting',
        'Custom Inserts',
    ),
    'capacity' => array(
        array('value' => '9+', 'label' => 'Years Manufacturing Experience', 'icon' => 'fa-award'),
        array('value' => '2,000 m2', 'label' => 'Factory Space', 'icon' => 'fa-industry'),
        array('value' => '10,000 - 3,000,000', 'label' => 'Boxes / Month', 'icon' => 'fa-boxes-stacked'),
        array('value' => 'OEM / ODM', 'label' => 'Packaging Support', 'icon' => 'fa-drafting-compass'),
        array('value' => 'Export-Ready', 'label' => 'Production', 'icon' => 'fa-earth-americas'),
        array('value' => 'Factory-Direct', 'label' => 'Pricing', 'icon' => 'fa-handshake'),
    ),
    'process' => array(
        'Send box size, quantity, material, and design requirements',
        'Receive consultation and quotation',
        'Confirm structure, artwork, and sample',
        'Start bulk production',
        'Quality inspection and packing',
        'Delivery or export support',
    ),
    'gallery' => vpn_catalog_featured_products($fallback_gallery),
);
?>

<main class="catalog-page">
    <?php
    get_template_part('template-parts/catalog/catalog-hero', null, $catalog_context);
    get_template_part('template-parts/catalog/catalog-download', null, $catalog_context);
    get_template_part('template-parts/catalog/catalog-flipbooks', null, $catalog_context);
    get_template_part('template-parts/catalog/catalog-categories', null, $catalog_context);
    get_template_part('template-parts/catalog/catalog-materials-finishing', null, $catalog_context);
    get_template_part('template-parts/catalog/catalog-capacity', null, $catalog_context);
    get_template_part('template-parts/catalog/catalog-process', null, $catalog_context);
    get_template_part('template-parts/catalog/catalog-gallery', null, $catalog_context);
    get_template_part('template-parts/catalog/catalog-faq', null, $catalog_context);
    ?>
</main>

<?php get_footer(); ?>
