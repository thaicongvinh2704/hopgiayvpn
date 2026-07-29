<?php
/**
 * Theme setup, menus, widgets, and lightweight template helpers.
 */

function custom_box_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('custom-logo', array(
        'height'      => 120,
        'width'       => 320,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    add_theme_support('html5', array(
        'comment-list',
        'comment-form',
        'search-form',
        'gallery',
        'caption',
        'style',
        'script',
    ));
    add_theme_support('customize-selective-refresh-widgets');

    register_nav_menus(array(
        'primary' => __('Primary Menu', 'custom-box-theme'),
        'footer'  => __('Footer Menu', 'custom-box-theme'),
    ));
}
add_action('after_setup_theme', 'custom_box_theme_setup');

function custom_box_primary_menu_fallback() {
    custom_box_primary_menu();
}

/**
 * Resolve a local image URL to a real file with intrinsic dimensions.
 *
 * Theme templates historically referenced WebP variants that may not exist
 * even though a JPG, JPEG, or PNG with the same basename is available. This
 * helper also validates upload URLs before rendering them and falls back to a
 * tracked theme image instead of allowing a front-end 404.
 *
 * @param string $image    Theme image filename or local WordPress image URL.
 * @param string $fallback Theme image filename used when the preferred image is missing.
 * @return array{url:string,path:string,width:int,height:int}
 */
function custom_box_get_local_image_data($image, $fallback = 'Cardboard-Packaging.webp') {
    $theme_url = trailingslashit(get_template_directory_uri()) . 'assets/images/';
    $theme_path = trailingslashit(get_template_directory()) . 'assets/images/';
    $content_url = trailingslashit(content_url());
    $content_path = trailingslashit(WP_CONTENT_DIR);
    $extensions = array('webp', 'jpg', 'jpeg', 'png');

    $resolve_candidate = static function ($candidate) use ($theme_url, $theme_path, $content_url, $content_path, $extensions) {
        $candidate = trim((string) $candidate);

        if ('' === $candidate) {
            return null;
        }

        $candidate_url = $candidate;
        $candidate_path = '';
        $is_theme_image = false;

        if (!preg_match('#^https?://#i', $candidate)) {
            $candidate = ltrim(wp_normalize_path($candidate), '/');
            $candidate_url = $theme_url . $candidate;
            $candidate_path = $theme_path . $candidate;
            $is_theme_image = true;
        } elseif (0 === strpos($candidate, $theme_url)) {
            $relative_path = rawurldecode(substr($candidate, strlen($theme_url)));
            $candidate_path = $theme_path . ltrim(wp_normalize_path($relative_path), '/');
            $is_theme_image = true;
        } elseif (0 === strpos($candidate, $content_url)) {
            $relative_path = rawurldecode(substr($candidate, strlen($content_url)));
            $candidate_path = $content_path . ltrim(wp_normalize_path($relative_path), '/');
        }

        $candidate_variants = array(
            array(
                'url'  => $candidate_url,
                'path' => $candidate_path,
            ),
        );

        if ($is_theme_image && $candidate_path) {
            $path_info = pathinfo($candidate_path);
            $url_path_info = pathinfo($candidate_url);

            if (!empty($path_info['dirname']) && !empty($path_info['filename']) && !empty($url_path_info['dirname'])) {
                foreach ($extensions as $extension) {
                    $candidate_variants[] = array(
                        'url'  => trailingslashit($url_path_info['dirname']) . $path_info['filename'] . '.' . $extension,
                        'path' => trailingslashit($path_info['dirname']) . $path_info['filename'] . '.' . $extension,
                    );
                }
            }
        }

        foreach ($candidate_variants as $variant) {
            if (empty($variant['path']) || !is_file($variant['path'])) {
                continue;
            }

            $size = wp_getimagesize($variant['path']);

            if (empty($size[0]) || empty($size[1])) {
                continue;
            }

            return array(
                'url'    => $variant['url'],
                'path'   => $variant['path'],
                'width'  => (int) $size[0],
                'height' => (int) $size[1],
            );
        }

        return null;
    };

    $resolved = $resolve_candidate($image);

    if ($resolved) {
        return $resolved;
    }

    $resolved = $resolve_candidate($fallback);

    if ($resolved) {
        return $resolved;
    }

    return array(
        'url'    => $theme_url . 'Cardboard-Packaging.webp',
        'path'   => $theme_path . 'Cardboard-Packaging.webp',
        'width'  => 506,
        'height' => 277,
    );
}

function custom_box_get_product_category_manifest_categories() {
    static $categories = null;

    if (null !== $categories) {
        return $categories;
    }

    $categories = array();
    $manifest_path = get_template_directory() . '/inc/product-category-assignment-manifest.json';

    if (!is_readable($manifest_path)) {
        return $categories;
    }

    $manifest = json_decode((string) file_get_contents($manifest_path), true);

    if (empty($manifest['categories']) || !is_array($manifest['categories'])) {
        return $categories;
    }

    foreach ($manifest['categories'] as $key => $category) {
        if (!is_array($category)) {
            continue;
        }

        $slug = sanitize_title(!empty($category['slug']) ? $category['slug'] : $key);
        $name = !empty($category['name']) ? sanitize_text_field($category['name']) : '';

        if (!$slug || !$name) {
            continue;
        }

        $thumbnail_asset = '';

        if (!empty($category['thumbnail_asset'])) {
            $candidate = ltrim(wp_normalize_path((string) $category['thumbnail_asset']), '/');

            if (false === strpos($candidate, '..')) {
                $thumbnail_asset = $candidate;
            }
        }

        $categories[$slug] = array(
            'name'            => $name,
            'slug'            => $slug,
            'group'           => !empty($category['group']) ? sanitize_text_field($category['group']) : '',
            'thumbnail_asset' => $thumbnail_asset,
        );
    }

    return $categories;
}

function custom_box_get_packaging_category_slugs() {
    $official_slugs = custom_box_get_official_packaging_category_slugs();

    if (!empty($official_slugs)) {
        return $official_slugs;
    }

    $slugs = array();

    foreach (custom_box_get_packaging_menu_groups() as $group) {
        if (empty($group['slugs']) || !is_array($group['slugs'])) {
            continue;
        }

        foreach ($group['slugs'] as $slug) {
            $slugs[] = $slug;
        }
    }

    return array_values(array_unique($slugs));
}

function custom_box_get_products_url() {
    return home_url('/products/');
}

function custom_box_get_home_packaging_category_groups() {
    $uploads_2026_05_uri = content_url('/uploads/2026/05/');
    $theme_image_uri = get_template_directory_uri() . '/assets/images/';

    $groups = array(
        array(
            'title' => 'Paper Box Types',
            'items' => array(
                array('Custom Paper Boxes', 'custom-paper-boxes', $uploads_2026_05_uri . '29.jpg'),
                array('Custom Printed Paper Boxes', 'custom-printed-paper-boxes', $uploads_2026_05_uri . '20.jpg'),
                array('Rigid Boxes', 'rigid-boxes', $uploads_2026_05_uri . '13.jpg'),
                array('Folding Carton Boxes', 'folding-carton-boxes', $uploads_2026_05_uri . 'folding-carton-boxes.webp'),
                array('Magnetic Closure Boxes', 'magnetic-closure-boxes', $uploads_2026_05_uri . 'red-floral-mooncake-gift-packaging-box.jpeg'),
                array('Drawer Boxes', 'drawer-boxes', $uploads_2026_05_uri . 'black-drawer-watch-packaging-box.jpeg'),
                array('Lid and Base Boxes', 'lid-and-base-boxes', $uploads_2026_05_uri . 'lid-and-base-boxes.webp'),
                array('Paper Tube Packaging', 'paper-tube-packaging', $uploads_2026_05_uri . '12.jpg'),
                array('Corrugated Mailer Boxes', 'corrugated-mailer-boxes', $uploads_2026_05_uri . 'orange-corrugated-mailer-box-768x768.jpeg'),
            ),
        ),
        array(
            'title' => 'Packaging by Industry',
            'items' => array(
                array('Cosmetic Paper Boxes', 'cosmetic-paper-boxes', $uploads_2026_05_uri . 'blue-cosmetic-set-packaging-box-open-1024x930.png'),
                array('Perfume Packaging Boxes', 'perfume-packaging-boxes', $uploads_2026_05_uri . '26.jpg'),
                array('Skincare Packaging Boxes', 'skincare-packaging-boxes', $uploads_2026_05_uri . '40.jpg'),
                array('Jewelry Paper Boxes', 'jewelry-paper-boxes', $uploads_2026_05_uri . 'watch-box.jpg'),
                array('Gift Paper Boxes', 'gift-paper-boxes', $uploads_2026_05_uri . '27.jpg'),
                array('Chocolate Gift Boxes', 'chocolate-gift-boxes', $uploads_2026_05_uri . '19.jpg'),
                array('Food Paper Boxes', 'food-paper-boxes', $uploads_2026_05_uri . '32.jpg'),
                array('Bakery Packaging Boxes', 'bakery-packaging-boxes', $uploads_2026_05_uri . '31.jpg'),
                array('Candle Packaging Boxes', 'candle-packaging-boxes', $uploads_2026_05_uri . '17.jpg'),
            ),
        ),
        array(
            'title' => 'Paper Bags & Packaging Add-ons',
            'items' => array(
                array('Paper Bags with Logo', 'paper-bags-with-logo', $uploads_2026_05_uri . '35.jpg'),
                array('Packaging Accessories', 'packaging-accessories', $uploads_2026_05_uri . '33.jpg'),
            ),
        ),
        array(
            'title' => 'Specialty Industry Packaging',
            'items' => array(
                array('Beauty and Skincare Packaging', 'beauty-skincare-packaging', $theme_image_uri . 'custom-cosmetic-skincare-packaging-boxes-gray-background.webp'),
                array('Pharmaceutical Packaging Boxes', 'pharmaceutical-packaging-boxes', $theme_image_uri . 'custom-pharmaceutical-medicine-packaging-boxes-gray-background.webp'),
                array('Supplement Packaging Boxes', 'supplement-packaging-boxes', $theme_image_uri . 'custom-supplement-vitamin-packaging-boxes-gray-background.webp'),
                array('Premium Food and Beverage Packaging', 'premium-food-beverage-packaging', $theme_image_uri . 'premium-tea-coffee-chocolate-packaging-boxes-gray-background.webp'),
                array('Bird Nest Packaging Boxes', 'bird-nest-packaging-boxes', $theme_image_uri . 'bird-nest-packaging-boxes.webp'),
                array('Electronics Accessories Packaging', 'electronics-accessories-packaging', $theme_image_uri . 'custom-phone-accessories-packaging-boxes-gray-background.webp'),
                array('Fashion and Sportswear Packaging', 'fashion-sportswear-packaging', $theme_image_uri . 'custom-apparel-packaging-boxes-gray-background.webp'),
                array('Sports Packaging Boxes', 'sports-packaging-boxes', $theme_image_uri . 'sport-packaging-box-thumbnail.webp'),
                array('Wine and Premium Drink Packaging', 'wine-premium-drink-packaging', $theme_image_uri . 'custom-wine-premium-beverage-packaging-boxes-gray-background.webp'),
                array('Corporate Gift Packaging', 'corporate-gift-packaging', $theme_image_uri . 'custom-corporate-gift-set-packaging-boxes-gray-background.webp'),
                array('Home and Lifestyle Packaging', 'home-lifestyle-packaging', $theme_image_uri . 'custom-home-lifestyle-product-packaging-boxes-gray-background.webp'),
                array('Back-to-School and Stationery Packaging', 'back-to-school-stationery-packaging', $theme_image_uri . 'custom-stationery-school-supplies-packaging-boxes-gray-background.webp'),
            ),
        ),
    );

    $configured_slugs = array();

    foreach ($groups as $group) {
        foreach ($group['items'] as $item) {
            if (!empty($item[1])) {
                $configured_slugs[] = $item[1];
            }
        }
    }

    foreach (custom_box_get_product_category_manifest_categories() as $category) {
        if (in_array($category['slug'], $configured_slugs, true)) {
            continue;
        }

        $target_group = $category['group'] ?: 'Specialty Industry Packaging';
        $thumbnail_url = !empty($category['thumbnail_asset'])
            ? get_template_directory_uri() . '/' . ltrim($category['thumbnail_asset'], '/')
            : '';

        foreach ($groups as &$group) {
            if ($group['title'] !== $target_group) {
                continue;
            }

            $group['items'][] = array($category['name'], $category['slug'], $thumbnail_url);
            $configured_slugs[] = $category['slug'];
            continue 2;
        }
        unset($group);

        $groups[] = array(
            'title' => $target_group,
            'items' => array(
                array($category['name'], $category['slug'], $thumbnail_url),
            ),
        );
        $configured_slugs[] = $category['slug'];
    }
    unset($group);

    return $groups;
}

function custom_box_get_home_packaging_category_image_url($term_or_slug) {
    $slug = is_object($term_or_slug) && isset($term_or_slug->slug) ? $term_or_slug->slug : (string) $term_or_slug;
    $slug = sanitize_title($slug);

    if (!$slug) {
        return '';
    }

    foreach (custom_box_get_home_packaging_category_groups() as $group) {
        foreach ($group['items'] as $item) {
            if (!empty($item[1]) && $slug === $item[1]) {
                return !empty($item[2]) ? $item[2] : '';
            }
        }
    }

    return '';
}

function custom_box_get_official_packaging_parent_category() {
    if (!taxonomy_exists('product_cat')) {
        return null;
    }

    $parent = get_term_by('slug', 'custom-packaging-boxes', 'product_cat');

    if (!$parent || is_wp_error($parent)) {
        $parent = get_term_by('name', 'Custom Packaging Boxes', 'product_cat');
    }

    return ($parent && !is_wp_error($parent)) ? $parent : null;
}

function custom_box_get_official_packaging_categories($limit = 0) {
    $parent = custom_box_get_official_packaging_parent_category();

    if (!$parent) {
        return array();
    }

    $categories = get_terms(array(
        'taxonomy'   => 'product_cat',
        'parent'     => (int) $parent->term_id,
        'hide_empty' => false,
        'orderby'    => 'term_id',
        'order'      => 'ASC',
        'number'     => $limit > 0 ? (int) $limit : 0,
    ));

    if (empty($categories) || is_wp_error($categories)) {
        return array();
    }

    usort($categories, function ($a, $b) {
        $a_featured = (int) get_term_meta($a->term_id, 'custom_box_category_featured', true);
        $b_featured = (int) get_term_meta($b->term_id, 'custom_box_category_featured', true);

        if ($a_featured !== $b_featured) {
            return $b_featured <=> $a_featured;
        }

        return $a->term_id <=> $b->term_id;
    });

    return $limit > 0 ? array_slice($categories, 0, (int) $limit) : $categories;
}

function custom_box_get_official_packaging_category_slugs() {
    $manifest_categories = custom_box_get_product_category_manifest_categories();

    if (!empty($manifest_categories)) {
        return array_keys($manifest_categories);
    }

    $slugs = array();

    foreach (custom_box_get_home_packaging_category_groups() as $group) {
        foreach ($group['items'] as $item) {
            if (!empty($item[1])) {
                $slugs[] = $item[1];
            }
        }
    }

    return array_values(array_unique($slugs));
}

function custom_box_get_packaging_group_anchor($title) {
    return sanitize_title($title);
}

function custom_box_get_packaging_group_url($title) {
    return custom_box_get_products_url() . '#' . custom_box_get_packaging_group_anchor($title);
}

function custom_box_get_packaging_group_for_slug($slug) {
    $slug = sanitize_title($slug);

    foreach (custom_box_get_packaging_menu_groups() as $group) {
        if (!empty($group['slugs']) && in_array($slug, $group['slugs'], true)) {
            return $group;
        }
    }

    return null;
}

function custom_box_get_packaging_group_for_term($term) {
    if (!$term || is_wp_error($term) || empty($term->slug)) {
        return null;
    }

    return custom_box_get_packaging_group_for_slug($term->slug);
}

function custom_box_get_packaging_categories($limit = 48, $require_products = true) {
    if (!taxonomy_exists('product_cat')) {
        return array();
    }

    $categories = array();

    foreach (custom_box_get_packaging_category_slugs() as $slug) {
        $category = custom_box_get_product_category_by_slug($slug);

        if (!$category) {
            continue;
        }

        if ($require_products && !custom_box_product_category_has_products($category)) {
            continue;
        }

        $categories[] = $category;

        if ($limit > 0 && count($categories) >= $limit) {
            break;
        }
    }

    return $categories;
}

function custom_box_get_product_group_link($name, $fallback = '#') {
    if (!taxonomy_exists('product_cat')) {
        return $fallback;
    }

    $term = get_term_by('name', $name, 'product_cat');

    if (!$term || is_wp_error($term)) {
        return $fallback;
    }

    $link = get_term_link($term);

    return is_wp_error($link) ? $fallback : $link;
}

function custom_box_product_category_has_products($term) {
    if (!$term || is_wp_error($term) || !taxonomy_exists('product_cat')) {
        return false;
    }

    if ((int) $term->count > 0) {
        return true;
    }

    $children = get_terms(array(
        'taxonomy'   => 'product_cat',
        'child_of'   => (int) $term->term_id,
        'hide_empty' => true,
        'fields'     => 'ids',
        'number'     => 1,
    ));

    return !is_wp_error($children) && !empty($children);
}

function custom_box_get_packaging_parent_category() {
    return null;
}

function custom_box_get_packaging_menu_groups() {
    $groups = array();

    foreach (custom_box_get_home_packaging_category_groups() as $group) {
        $slugs = array();

        foreach ($group['items'] as $item) {
            if (!empty($item[1])) {
                $slugs[] = $item[1];
            }
        }

        if (!empty($slugs)) {
            $groups[] = array(
                'title' => !empty($group['title']) ? $group['title'] : __('Product Categories', 'custom-box-theme'),
                'slugs' => $slugs,
            );
        }
    }

    return $groups;
}

function custom_box_get_all_categories_sidebar_links($fallback = '#') {
    $links = array();

    foreach (custom_box_get_all_categories_menu_columns() as $column) {
        if (empty($column['terms']) || empty($column['title'])) {
            continue;
        }

        $links[$column['title']] = custom_box_get_packaging_group_url($column['title']);
    }

    if (empty($links) && $fallback) {
        $links[__('Product Categories', 'custom-box-theme')] = $fallback;
    }

    return $links;
}

function custom_box_get_product_category_by_slug($slug) {
    if (!taxonomy_exists('product_cat')) {
        return null;
    }

    $term = get_term_by('slug', $slug, 'product_cat');

    return ($term && !is_wp_error($term)) ? $term : null;
}

function custom_box_get_all_categories_menu_columns() {
    if (!taxonomy_exists('product_cat')) {
        return array();
    }

    $columns = array();

    foreach (custom_box_get_packaging_menu_groups() as $group) {
        $terms = array();

        foreach ($group['slugs'] as $slug) {
            $term = custom_box_get_product_category_by_slug($slug);

            if ($term && custom_box_product_category_has_products($term)) {
                $terms[] = $term;
            }
        }

        if (!empty($terms)) {
            $columns[] = array(
                'title' => $group['title'],
                'terms' => $terms,
            );
        }
    }

    return $columns;
}

function custom_box_get_packaging_hub_groups($require_products = false) {
    if (!taxonomy_exists('product_cat')) {
        return array();
    }

    $groups = array();
    $official_slugs = custom_box_get_official_packaging_category_slugs();
    $official_positions = !empty($official_slugs) ? array_flip($official_slugs) : array();

    foreach (custom_box_get_packaging_menu_groups() as $group) {
        $terms = array();

        foreach ($group['slugs'] as $slug) {
            $term = custom_box_get_product_category_by_slug($slug);

            if (!$term) {
                continue;
            }

            if ($require_products && !custom_box_product_category_has_products($term)) {
                continue;
            }

            $terms[] = $term;
        }

        if (!empty($official_positions)) {
            usort($terms, function ($a, $b) use ($official_positions) {
                $a_position = $official_positions[$a->slug] ?? PHP_INT_MAX;
                $b_position = $official_positions[$b->slug] ?? PHP_INT_MAX;

                if ($a_position !== $b_position) {
                    return $a_position <=> $b_position;
                }

                return $a->term_id <=> $b->term_id;
            });
        }

        $groups[] = array(
            'title'  => $group['title'],
            'anchor' => custom_box_get_packaging_group_anchor($group['title']),
            'terms'  => $terms,
        );
    }

    return $groups;
}

function custom_box_build_blog_article_content($raw_content) {
    $has_toc_placeholder = false !== strpos($raw_content, '[vpn_toc]');
    $content = apply_filters('the_content', $raw_content);
    $toc = array();
    $seen_ids = array();

    $content = custom_box_normalize_blog_article_headings($content);
    $content = custom_box_enhance_blog_article_images($content);

    $content = preg_replace_callback('/<h([23])([^>]*)>(.*?)<\/h\1>/is', function ($matches) use (&$toc, &$seen_ids) {
        $level = (int) $matches[1];
        $attributes = $matches[2];
        $heading_html = $matches[3];
        $heading_text = trim(wp_strip_all_tags($heading_html));

        if (!$heading_text) {
            return $matches[0];
        }

        if (preg_match('/\sid=["\']([^"\']+)["\']/i', $attributes, $id_match)) {
            $heading_id = sanitize_title(html_entity_decode($id_match[1], ENT_QUOTES, get_bloginfo('charset')));
            $attributes = preg_replace('/\sid=["\'][^"\']+["\']/i', '', $attributes);
        } else {
            $heading_id = sanitize_title(html_entity_decode($heading_text, ENT_QUOTES, get_bloginfo('charset')));
        }

        $base_id = $heading_id ? $heading_id : 'section';
        $heading_id = $base_id;
        $suffix = 2;

        while (in_array($heading_id, $seen_ids, true)) {
            $heading_id = $base_id . '-' . $suffix;
            $suffix++;
        }

        $seen_ids[] = $heading_id;
        $toc[] = array(
            'id'    => $heading_id,
            'title' => $heading_text,
            'level' => $level,
        );

        $attributes = preg_replace('/\s(?:tabindex|data-article-anchor)(?:=(["\']).*?\1)?/i', '', $attributes);

        return '<h' . $level . $attributes . ' id="' . esc_attr($heading_id) . '" tabindex="-1" data-article-anchor>' . $heading_html . '</h' . $level . '>';
    }, $content);

    $content = custom_box_enhance_blog_article_tables($content);

    if (count($toc) >= 2) {
        $toc_html = custom_box_render_blog_toc($toc);

        if ($has_toc_placeholder) {
            $content = preg_replace('/<p>\s*\[vpn_toc\]\s*<\/p>/i', $toc_html, $content);
            $content = str_replace('[vpn_toc]', $toc_html, $content);
        } else {
            $content = preg_replace('/<h2\b/i', $toc_html . '<h2', $content, 1);
        }
    }

    return array(
        'content' => $content,
        'toc'     => $toc,
    );
}

function custom_box_normalize_blog_article_headings($content) {
    return preg_replace('/<h1\b([^>]*)>(.*?)<\/h1>/is', '<h2$1>$2</h2>', $content);
}

function custom_box_render_blog_toc($toc) {
    if (empty($toc) || !is_array($toc)) {
        return '';
    }

    $section_index = 0;
    $subsection_index = 0;
    $toc_suffix = get_the_ID() ? (string) get_the_ID() : wp_unique_id();
    $toc_id = 'blog-article-toc-' . sanitize_html_class($toc_suffix);
    $toc_toggle_id = $toc_id . '-toggle';
    $toc_panel_id = $toc_id . '-panel';

    ob_start();
    ?>
    <nav
        class="blog-toc blog-toc-easy is-open"
        id="<?php echo esc_attr($toc_id); ?>"
        aria-label="<?php esc_attr_e('Table of contents', 'custom-box-theme'); ?>"
        data-article-toc
        data-mobile-default="closed"
        data-desktop-default="open"
    >
        <button
            class="blog-toc-toggle"
            id="<?php echo esc_attr($toc_toggle_id); ?>"
            type="button"
            aria-expanded="true"
            aria-controls="<?php echo esc_attr($toc_panel_id); ?>"
            data-article-toc-toggle
        >
            <span><?php esc_html_e('Table of Contents', 'custom-box-theme'); ?></span>
            <i class="fas fa-chevron-up" aria-hidden="true"></i>
        </button>
        <ul
            class="blog-toc-panel"
            id="<?php echo esc_attr($toc_panel_id); ?>"
            aria-labelledby="<?php echo esc_attr($toc_toggle_id); ?>"
            data-article-toc-panel
        >
            <?php foreach ($toc as $item) : ?>
                <?php
                if (2 === (int) $item['level']) {
                    $section_index++;
                    $subsection_index = 0;
                    $toc_number = (string) $section_index;
                } else {
                    if (0 === $section_index) {
                        $section_index = 1;
                    }

                    $subsection_index++;
                    $toc_number = $section_index . '.' . $subsection_index;
                }
                ?>
                <li class="blog-toc-item blog-toc-level-<?php echo esc_attr($item['level']); ?>">
                    <span class="blog-toc-number"><?php echo esc_html($toc_number); ?></span>
                    <a class="blog-toc-link" href="#<?php echo esc_attr($item['id']); ?>" data-article-toc-link>
                        <?php echo esc_html($item['title']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <?php

    return ob_get_clean();
}

function custom_box_enhance_blog_article_tables($content) {
    if (false !== strpos($content, 'data-article-table-scroll')) {
        return $content;
    }

    $table_index = 0;
    $post_id = max(0, (int) get_the_ID());

    return preg_replace_callback('/<table\b[^>]*>.*?<\/table>/is', function ($matches) use (&$table_index, $post_id) {
        $table_index++;

        $table_html = $matches[0];
        $cue_id = 'blog-table-scroll-cue-' . $post_id . '-' . $table_index;
        $table_label = sprintf(
            /* translators: %d: table number within the article. */
            __('Scrollable data table %d', 'custom-box-theme'),
            $table_index
        );

        if (preg_match('/<table\b[^>]*\sclass=(["\'])(.*?)\1/i', $table_html, $class_match)) {
            $table_classes = trim($class_match[2] . ' blog-content-table');
            $table_html = preg_replace(
                '/(<table\b[^>]*\sclass=)(["\'])(.*?)\2/i',
                '$1$2' . esc_attr($table_classes) . '$2',
                $table_html,
                1
            );
        } else {
            $table_html = preg_replace('/<table\b/i', '<table class="blog-content-table"', $table_html, 1);
        }

        if (false === strpos($table_html, 'data-article-table')) {
            $table_html = preg_replace('/<table\b/i', '<table data-article-table', $table_html, 1);
        }

        return '<div class="blog-table-block" data-article-table-block>'
            . '<p class="blog-table-scroll-cue" id="' . esc_attr($cue_id) . '" data-article-table-cue>'
            . esc_html__('Scroll horizontally to view all table columns.', 'custom-box-theme')
            . '</p>'
            . '<div class="blog-table-scroll" tabindex="0" role="region" aria-label="' . esc_attr($table_label) . '" aria-describedby="' . esc_attr($cue_id) . '" data-article-table-scroll>'
            . $table_html
            . '</div>'
            . '</div>';
    }, $content);
}

function custom_box_get_image_alt_caption($image_html) {
    if (!preg_match('/\salt=(["\'])(.*?)\1/i', $image_html, $alt_match)) {
        return '';
    }

    $caption = trim(wp_strip_all_tags(html_entity_decode($alt_match[2], ENT_QUOTES, get_bloginfo('charset'))));

    if (!custom_box_is_meaningful_blog_image_caption($caption)) {
        return '';
    }

    return $caption;
}

function custom_box_is_meaningful_blog_image_caption($caption) {
    $caption = trim(wp_strip_all_tags((string) $caption));

    if (!$caption || filter_var($caption, FILTER_VALIDATE_URL)) {
        return false;
    }

    return !preg_match('#^https?://#i', $caption);
}

function custom_box_get_attachment_image_caption($attachment_id) {
    $attachment = get_post($attachment_id);

    if (!$attachment) {
        return '';
    }

    $candidates = array(
        $attachment->post_excerpt,
        get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
    );

    foreach ($candidates as $candidate) {
        $candidate = trim(wp_strip_all_tags((string) $candidate));

        if (custom_box_is_meaningful_blog_image_caption($candidate)) {
            return $candidate;
        }
    }

    return '';
}

function custom_box_set_blog_image_attribute($image_html, $attribute, $value) {
    $attribute = sanitize_key($attribute);

    if (!$attribute) {
        return $image_html;
    }

    $escaped_value = esc_attr((string) $value);
    $attribute_pattern = '/\s' . preg_quote($attribute, '/') . '=(["\']).*?\1/i';

    if (preg_match($attribute_pattern, $image_html)) {
        return preg_replace($attribute_pattern, ' ' . $attribute . '="' . $escaped_value . '"', $image_html, 1);
    }

    return preg_replace('/<img\b/i', '<img ' . $attribute . '="' . $escaped_value . '"', $image_html, 1);
}

function custom_box_wrap_blog_image_html($image_html) {
    $caption = custom_box_get_image_alt_caption($image_html);
    $caption_html = $caption ? '<figcaption>' . esc_html($caption) . '</figcaption>' : '';

    if ($caption) {
        $image_html = custom_box_set_blog_image_attribute($image_html, 'alt', '');
    }

    if (preg_match('/^\s*<a\b/i', $image_html) && !preg_match('/^\s*<a\b[^>]*\saria-label=/i', $image_html)) {
        $link_label = $caption
            ? sprintf(
                /* translators: %s: image caption. */
                __('View full-size image: %s', 'custom-box-theme'),
                $caption
            )
            : sprintf(
                /* translators: %s: article title. */
                __('View an image from %s', 'custom-box-theme'),
                get_the_title()
            );

        $image_html = preg_replace(
            '/<a\b/i',
            '<a aria-label="' . esc_attr($link_label) . '" data-article-image-link',
            $image_html,
            1
        );
    }

    return '<figure class="blog-content-figure" data-article-figure>' . $image_html . $caption_html . '</figure>';
}

function custom_box_enhance_blog_article_images($content) {
    $content = preg_replace_callback('/<img\b[^>]*>/i', function ($matches) {
        $image_html = $matches[0];
        $attachment_id = 0;
        $image_width = 0;
        $image_height = 0;

        if (preg_match('/\sclass=(["\'])[^"\']*wp-image-(\d+)[^"\']*\1/i', $image_html, $class_match)) {
            $attachment_id = (int) $class_match[2];
        }

        if (!$attachment_id && preg_match('/\ssrc=(["\'])(.*?)\1/i', $image_html, $src_match)) {
            $image_src = html_entity_decode($src_match[2], ENT_QUOTES, get_bloginfo('charset'));
            $attachment_id = (int) attachment_url_to_postid($image_src);

            if (!$attachment_id) {
                $content_base_url = trailingslashit(content_url());
                $content_base_path = trailingslashit(WP_CONTENT_DIR);

                if (0 === strpos($image_src, $content_base_url)) {
                    $relative_path = rawurldecode((string) wp_parse_url(substr($image_src, strlen($content_base_url)), PHP_URL_PATH));
                    $local_path = $content_base_path . wp_normalize_path($relative_path);
                    $local_size = is_file($local_path) ? wp_getimagesize($local_path) : false;

                    if ($local_size) {
                        $image_width = (int) $local_size[0];
                        $image_height = (int) $local_size[1];
                    } else {
                        $fallback_path = get_template_directory() . '/assets/images/Cardboard-Packaging.webp';
                        $fallback_url = get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp';
                        $fallback_size = is_file($fallback_path) ? wp_getimagesize($fallback_path) : false;
                        $image_html = custom_box_set_blog_image_attribute($image_html, 'src', $fallback_url);
                        $image_html = preg_replace('/\ssrcset=(["\']).*?\1/i', '', $image_html);
                        $image_width = !empty($fallback_size[0]) ? (int) $fallback_size[0] : 506;
                        $image_height = !empty($fallback_size[1]) ? (int) $fallback_size[1] : 277;
                    }
                }
            }
        }

        if ($attachment_id) {
            $attachment_path = get_attached_file($attachment_id);
            $attachment_url = wp_get_attachment_url($attachment_id);

            if ($attachment_path && is_file($attachment_path) && $attachment_url) {
                $image_html = custom_box_set_blog_image_attribute($image_html, 'src', $attachment_url);
            } else {
                $fallback_path = get_template_directory() . '/assets/images/Cardboard-Packaging.webp';
                $fallback_url = get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp';
                $fallback_size = is_file($fallback_path) ? wp_getimagesize($fallback_path) : false;

                $image_html = custom_box_set_blog_image_attribute($image_html, 'src', $fallback_url);
                $image_html = preg_replace('/\ssrcset=(["\']).*?\1/i', '', $image_html);
                $image_width = !empty($fallback_size[0]) ? (int) $fallback_size[0] : 506;
                $image_height = !empty($fallback_size[1]) ? (int) $fallback_size[1] : 277;
                $attachment_id = 0;
            }
        }

        if ($attachment_id) {
            $metadata = wp_get_attachment_metadata($attachment_id);

            if (!empty($metadata['width']) && !empty($metadata['height'])) {
                $image_width = (int) $metadata['width'];
                $image_height = (int) $metadata['height'];
            }

            if (!preg_match('/\ssrcset=(["\']).*?\1/i', $image_html)) {
                $srcset = wp_get_attachment_image_srcset($attachment_id, 'full');

                if ($srcset) {
                    $image_html = custom_box_set_blog_image_attribute($image_html, 'srcset', $srcset);
                }
            }
        }

        if ($image_width > 0 && $image_height > 0) {
            $image_html = custom_box_set_blog_image_attribute($image_html, 'width', $image_width);
            $image_html = custom_box_set_blog_image_attribute($image_html, 'height', $image_height);
        }

        $current_alt = '';
        $has_alt = preg_match('/\salt=(["\'])(.*?)\1/i', $image_html, $alt_match);

        if ($has_alt) {
            $current_alt = trim(wp_strip_all_tags(html_entity_decode($alt_match[2], ENT_QUOTES, get_bloginfo('charset'))));
        }

        if (!$has_alt || ($current_alt && !custom_box_is_meaningful_blog_image_caption($current_alt))) {
            $fallback_alt = $attachment_id ? custom_box_get_attachment_image_caption($attachment_id) : '';
            $image_html = custom_box_set_blog_image_attribute($image_html, 'alt', $fallback_alt);
        }

        $image_html = custom_box_set_blog_image_attribute($image_html, 'loading', 'lazy');
        $image_html = custom_box_set_blog_image_attribute($image_html, 'decoding', 'async');
        $image_html = custom_box_set_blog_image_attribute($image_html, 'sizes', '(max-width: 820px) 100vw, 820px');

        if (false === strpos($image_html, 'data-article-image')) {
            $image_html = preg_replace('/<img\b/i', '<img data-article-image', $image_html, 1);
        }

        return $image_html;
    }, $content);

    $content = preg_replace_callback('/<p\b[^>]*>\s*((?:<a[^>]*>\s*)?<img[^>]+>(?:\s*<\/a>)?)\s*<\/p>/i', function ($matches) {
        return custom_box_wrap_blog_image_html($matches[1]);
    }, $content);

    $content = preg_replace_callback('/(<figure\b[^>]*>.*?wp-image-(\d+).*?)(<figcaption\b[^>]*>)(.*?)(<\/figcaption>)(.*?<\/figure>)/is', function ($matches) {
        $caption = trim(wp_strip_all_tags(html_entity_decode($matches[4], ENT_QUOTES, get_bloginfo('charset'))));

        if (custom_box_is_meaningful_blog_image_caption($caption)) {
            $image_alt = custom_box_get_image_alt_caption($matches[0]);

            if ($image_alt && 0 === strcasecmp($image_alt, $caption)) {
                return custom_box_set_blog_image_attribute($matches[0], 'alt', '');
            }

            return $matches[0];
        }

        $fallback_caption = custom_box_get_attachment_image_caption((int) $matches[2]);

        if (!$fallback_caption) {
            return $matches[1] . $matches[6];
        }

        return $matches[1] . $matches[3] . esc_html($fallback_caption) . $matches[5] . $matches[6];
    }, $content);

    return $content;
}

function custom_box_get_blog_product_recommendations($limit = 3) {
    if (!function_exists('wc_get_products')) {
        return array();
    }

    return wc_get_products(array(
        'status'  => 'publish',
        'limit'   => $limit,
        'orderby' => 'date',
        'order'   => 'DESC',
    ));
}

function custom_box_primary_menu() {
    $blog_page_id = (int) get_option('page_for_posts');
    $blog_link = $blog_page_id ? get_permalink($blog_page_id) : home_url('/blog/');
    $packaging_money_page_url = function_exists('custom_box_get_packaging_money_page_url')
        ? custom_box_get_packaging_money_page_url()
        : home_url('/custom-packaging-boxes-manufacturer/');
    ?>
    <ul class="nav-menu">
        <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'custom-box-theme'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/about/')); ?>"><?php esc_html_e('About Us', 'custom-box-theme'); ?></a></li>
        <li class="product-menu-item">
            <a href="<?php echo esc_url(custom_box_get_products_url()); ?>">
                <?php esc_html_e('Products', 'custom-box-theme'); ?>
            </a>
        </li>
        <li><a href="<?php echo esc_url($packaging_money_page_url); ?>"><?php esc_html_e('Custom Packaging', 'custom-box-theme'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/catalog/')); ?>"><?php esc_html_e('Catalog', 'custom-box-theme'); ?></a></li>
        <li><a href="<?php echo esc_url($blog_link); ?>"><?php esc_html_e('Blog', 'custom-box-theme'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Contact Us', 'custom-box-theme'); ?></a></li>
    </ul>
    <?php
}

function custom_box_add_packaging_money_page_to_primary_menu($items, $args) {
    if (empty($args->theme_location) || 'primary' !== $args->theme_location) {
        return $items;
    }

    $packaging_money_page_url = function_exists('custom_box_get_packaging_money_page_url')
        ? custom_box_get_packaging_money_page_url()
        : home_url('/custom-packaging-boxes-manufacturer/');
    $packaging_path = wp_parse_url($packaging_money_page_url, PHP_URL_PATH);

    if ($packaging_path && false !== strpos($items, $packaging_path)) {
        return $items;
    }

    $menu_item = sprintf(
        '<li class="menu-item menu-item-packaging-money-page"><a href="%s">%s</a></li>',
        esc_url($packaging_money_page_url),
        esc_html__('Custom Packaging', 'custom-box-theme')
    );

    if (preg_match('/<li[^>]*>\s*<a[^>]+href=["\'][^"\']*\/products\/?["\'][^>]*>.*?<\/a>\s*<\/li>/is', $items, $matches, PREG_OFFSET_CAPTURE)) {
        $insert_at = $matches[0][1] + strlen($matches[0][0]);
        return substr($items, 0, $insert_at) . $menu_item . substr($items, $insert_at);
    }

    return $items . $menu_item;
}
add_filter('wp_nav_menu_items', 'custom_box_add_packaging_money_page_to_primary_menu', 20, 2);

function custom_box_normalize_products_menu_url($atts) {
    if (empty($atts['href'])) {
        return $atts;
    }

    $path = wp_parse_url($atts['href'], PHP_URL_PATH);

    if (!$path) {
        return $atts;
    }

    $home_path = wp_parse_url(home_url('/'), PHP_URL_PATH);
    $relative_path = trim(preg_replace('#^' . preg_quote($home_path, '#') . '#', '', $path), '/');

    if (in_array($relative_path, array('p/products', 'custom-packaging-product-categories'), true)) {
        $atts['href'] = custom_box_get_products_url();
    }

    return $atts;
}
add_filter('nav_menu_link_attributes', 'custom_box_normalize_products_menu_url', 20);

function custom_box_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar', 'custom-box-theme'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here.', 'custom-box-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Widgets', 'custom-box-theme'),
        'id'            => 'footer-widgets',
        'description'   => __('Add footer widgets here.', 'custom-box-theme'),
        'before_widget' => '<section id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'custom_box_widgets_init');
