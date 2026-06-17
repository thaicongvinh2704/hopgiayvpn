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

    return array(
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
                array('Pharmaceutical Packaging Boxes', 'pharmaceutical-packaging-boxes', $theme_image_uri . 'custom-pharmaceutical-medicine-packaging-boxes-gray-background.webp'),
                array('Supplement Packaging Boxes', 'supplement-packaging-boxes', $theme_image_uri . 'custom-supplement-vitamin-packaging-boxes-gray-background.webp'),
                array('Premium Food and Beverage Packaging', 'premium-food-beverage-packaging', $theme_image_uri . 'premium-tea-coffee-chocolate-packaging-boxes-gray-background.webp'),
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
            $heading_id = sanitize_title($id_match[1]);
            $attributes = preg_replace('/\sid=["\'][^"\']+["\']/i', '', $attributes);
        } else {
            $heading_id = sanitize_title($heading_text);
        }

        $base_id = $heading_id ? $heading_id : 'section';
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

        return '<h' . $level . $attributes . ' id="' . esc_attr($heading_id) . '">' . $heading_html . '</h' . $level . '>';
    }, $content);

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

    ob_start();
    ?>
    <nav class="blog-toc blog-toc-easy is-open" aria-label="<?php esc_attr_e('Table of contents', 'custom-box-theme'); ?>">
        <button class="blog-toc-toggle" type="button" aria-expanded="true">
            <span><?php esc_html_e('Table of Contents', 'custom-box-theme'); ?></span>
            <i class="fas fa-chevron-up"></i>
        </button>
        <ul class="blog-toc-panel">
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
                    <a class="blog-toc-link" href="#<?php echo esc_attr($item['id']); ?>">
                        <?php echo esc_html($item['title']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <?php

    return ob_get_clean();
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

function custom_box_wrap_blog_image_html($image_html) {
    $caption = custom_box_get_image_alt_caption($image_html);
    $caption_html = $caption ? '<figcaption>' . esc_html($caption) . '</figcaption>' : '';

    return '<figure class="blog-content-figure">' . $image_html . $caption_html . '</figure>';
}

function custom_box_enhance_blog_article_images($content) {
    $content = preg_replace_callback('/<p\b[^>]*>\s*((?:<a[^>]*>\s*)?<img[^>]+>(?:\s*<\/a>)?)\s*<\/p>/i', function ($matches) {
        return custom_box_wrap_blog_image_html($matches[1]);
    }, $content);

    $content = preg_replace('/\ssizes=(["\']).*?\1/i', ' sizes="(max-width: 820px) 100vw, 820px"', $content);

    $content = preg_replace_callback('/<img\b[^>]*class=(["\'])[^"\']*wp-image-(\d+)[^"\']*\1[^>]*>/i', function ($matches) {
        $image_html = $matches[0];
        $attachment_id = (int) $matches[2];
        $full_url = wp_get_attachment_url($attachment_id);
        $metadata = wp_get_attachment_metadata($attachment_id);

        if (!$full_url) {
            return $image_html;
        }

        $image_html = preg_replace('/\ssrc=(["\']).*?\1/i', ' src="' . esc_url($full_url) . '"', $image_html);

        if (!empty($metadata['width']) && !empty($metadata['height'])) {
            $image_html = preg_replace('/\swidth=(["\']).*?\1/i', ' width="' . (int) $metadata['width'] . '"', $image_html);
            $image_html = preg_replace('/\sheight=(["\']).*?\1/i', ' height="' . (int) $metadata['height'] . '"', $image_html);
        }

        return $image_html;
    }, $content);

    $content = preg_replace_callback('/(<figure\b[^>]*>.*?wp-image-(\d+).*?)(<figcaption\b[^>]*>)(.*?)(<\/figcaption>)(.*?<\/figure>)/is', function ($matches) {
        $caption = trim(wp_strip_all_tags(html_entity_decode($matches[4], ENT_QUOTES, get_bloginfo('charset'))));

        if (custom_box_is_meaningful_blog_image_caption($caption)) {
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
