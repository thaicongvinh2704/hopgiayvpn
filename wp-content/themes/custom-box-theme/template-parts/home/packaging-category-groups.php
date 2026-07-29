<?php
$resolve_category_url = function ($slug) {
    $term = get_term_by('slug', $slug, 'product_cat');
    if ($term && !is_wp_error($term)) {
        $term_link = get_term_link($term);
        if (!is_wp_error($term_link)) {
            return $term_link;
        }
    }

    $url = home_url('/products/' . trim($slug, '/') . '/');

    if (url_to_postid($url)) {
        return $url;
    }

    return home_url('/products/');
};

$category_groups = function_exists('custom_box_get_home_packaging_category_groups')
    ? custom_box_get_home_packaging_category_groups()
    : array();

$priority_category_slugs = array(
    'custom-paper-boxes',
    'rigid-boxes',
    'folding-carton-boxes',
    'corrugated-mailer-boxes',
    'cosmetic-paper-boxes',
    'paper-bags-with-logo',
);

$category_items_by_slug = array();
$category_item_order = array();

foreach ($category_groups as $category_group) {
    if (!empty($category_group['hidden']) || empty($category_group['items'])) {
        continue;
    }

    foreach ($category_group['items'] as $category_item) {
        $category_slug = !empty($category_item[1]) ? sanitize_title($category_item[1]) : '';

        if (!$category_slug || isset($category_items_by_slug[$category_slug])) {
            continue;
        }

        $category_items_by_slug[$category_slug] = $category_item;
        $category_item_order[] = $category_slug;
    }
}

$ordered_category_slugs = array();

foreach ($priority_category_slugs as $priority_category_slug) {
    if (isset($category_items_by_slug[$priority_category_slug])) {
        $ordered_category_slugs[] = $priority_category_slug;
    }
}

foreach ($category_item_order as $category_slug) {
    if (!in_array($category_slug, $ordered_category_slugs, true)) {
        $ordered_category_slugs[] = $category_slug;
    }
}

$fallback_image_url = get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp';
$fallback_image_path = get_template_directory() . '/assets/images/Cardboard-Packaging.webp';
$fallback_image_size = wp_getimagesize($fallback_image_path);

$resolve_local_image = static function ($image_url) use ($fallback_image_url, $fallback_image_path, $fallback_image_size) {
    $resolved_url = $image_url ? $image_url : $fallback_image_url;
    $resolved_path = '';
    $content_base_url = trailingslashit(content_url());
    $content_base_path = trailingslashit(WP_CONTENT_DIR);

    if (0 === strpos($resolved_url, $content_base_url)) {
        $relative_path = rawurldecode(substr($resolved_url, strlen($content_base_url)));
        $resolved_path = $content_base_path . wp_normalize_path($relative_path);
    }

    if (!$resolved_path || !is_file($resolved_path)) {
        $resolved_url = $fallback_image_url;
        $resolved_path = $fallback_image_path;
    }

    $resolved_size = wp_getimagesize($resolved_path);

    if (!$resolved_size) {
        $resolved_size = $fallback_image_size;
    }

    return array(
        'url'    => $resolved_url,
        'width'  => !empty($resolved_size[0]) ? (int) $resolved_size[0] : 506,
        'height' => !empty($resolved_size[1]) ? (int) $resolved_size[1] : 277,
    );
};
?>

<section class="home-packaging-category-section" id="packaging-categories" aria-labelledby="home-packaging-categories-title" data-home-packaging-categories>
    <div class="container">
        <div class="home-packaging-category-head">
            <span class="home-packaging-category-eyebrow">Manufacturing Range</span>
            <h2 id="home-packaging-categories-title">Explore Our Paper Packaging Categories</h2>
            <p>Choose by box structure, industry use, or packaging add-on, then customize size, material, printing, finishing, inserts, and order quantity.</p>
        </div>

        <div class="home-packaging-category-cards" id="home-packaging-category-cards" data-home-category-grid>
            <?php foreach ($ordered_category_slugs as $category_slug) : ?>
                <?php
                $category_item = $category_items_by_slug[$category_slug];
                $category_image_url = !empty($category_item[2]) ? $category_item[2] : '';
                $is_priority_category = in_array($category_slug, $priority_category_slugs, true);

                if (!$category_image_url && function_exists('custom_box_get_product_category_card_image_url')) {
                    $category_term = get_term_by('slug', $category_slug, 'product_cat');

                    if ($category_term && !is_wp_error($category_term)) {
                        $category_image_url = custom_box_get_product_category_card_image_url($category_term, 'medium_large');
                    }
                }

                $category_image = $resolve_local_image($category_image_url);
                $category_image_id = attachment_url_to_postid($category_image['url']);
                ?>
                <a
                    class="home-packaging-category-card<?php echo $is_priority_category ? ' is-priority-category' : ' is-additional-category'; ?>"
                    href="<?php echo esc_url($resolve_category_url($category_slug)); ?>"
                    data-home-category-card
                    data-packaging-category-card
                    data-category-slug="<?php echo esc_attr($category_slug); ?>"
                    data-category-priority="<?php echo $is_priority_category ? 'true' : 'false'; ?>"
                    <?php if (!$is_priority_category) : ?>data-home-category-extra<?php endif; ?>
                >
                    <span class="home-packaging-category-image">
                        <?php if ($category_image_id) : ?>
                            <?php
                            echo wp_get_attachment_image(
                                $category_image_id,
                                'medium_large',
                                false,
                                array(
                                    'alt'      => '',
                                    'loading'  => 'lazy',
                                    'decoding' => 'async',
                                    'sizes'    => '(max-width: 479px) 50vw, (max-width: 767px) 33vw, 220px',
                                )
                            );
                            ?>
                        <?php else : ?>
                            <img
                                src="<?php echo esc_url($category_image['url']); ?>"
                                alt=""
                                width="<?php echo esc_attr($category_image['width']); ?>"
                                height="<?php echo esc_attr($category_image['height']); ?>"
                                loading="lazy"
                                decoding="async"
                                sizes="(max-width: 479px) 50vw, (max-width: 767px) 33vw, 220px"
                            >
                        <?php endif; ?>
                    </span>
                    <span class="home-packaging-category-title"><?php echo esc_html($category_item[0]); ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="home-packaging-category-actions">
            <a
                class="btn-outline home-packaging-category-view-all"
                href="<?php echo esc_url(function_exists('custom_box_get_products_url') ? custom_box_get_products_url() : home_url('/products/')); ?>"
                aria-controls="home-packaging-category-cards"
                aria-expanded="false"
                data-home-category-toggle
                data-expanded-label="<?php esc_attr_e('Show Priority Packaging Categories', 'custom-box-theme'); ?>"
            ><?php esc_html_e('View All Packaging Categories', 'custom-box-theme'); ?></a>
        </div>
    </div>
</section>
