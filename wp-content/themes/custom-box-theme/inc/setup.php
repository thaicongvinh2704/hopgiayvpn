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

function custom_box_get_packaging_categories($limit = 48) {
    $custom_boxes_parent = taxonomy_exists('product_cat') ? get_term_by('name', 'Custom Packaging Boxes', 'product_cat') : false;

    if (!$custom_boxes_parent || is_wp_error($custom_boxes_parent)) {
        return array();
    }

    $categories = get_terms(array(
        'taxonomy'   => 'product_cat',
        'parent'     => $custom_boxes_parent->term_id,
        'hide_empty' => false,
        'orderby'    => 'term_id',
        'order'      => 'ASC',
        'number'     => $limit,
    ));

    return is_wp_error($categories) ? array() : $categories;
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

function custom_box_get_product_category_by_slug($slug) {
    if (!taxonomy_exists('product_cat')) {
        return null;
    }

    $term = get_term_by('slug', $slug, 'product_cat');

    return ($term && !is_wp_error($term)) ? $term : null;
}

function custom_box_get_all_categories_menu_columns() {
    $columns = array(
        array(
            'title' => __('Custom Paper Tube Packaging Boxes', 'custom-box-theme'),
            'slugs' => array(
                'custom-paper-tube-packaging-boxes',
                'luxury-rigid-gift-boxes',
                'custom-cake-packaging-boxes',
                'luxury-drawer-gift-boxes',
                'luxury-wine-bottle-packaging-boxes',
                'printed-paper-shopping-bags',
            ),
        ),
        array(
            'title' => __('Custom Cosmetic Packaging Boxes', 'custom-box-theme'),
            'slugs' => array(
                'cosmetic-mailer-packaging-boxes',
                'cosmetic-set-packaging-boxes',
                'luxury-perfume-packaging-boxes',
                'custom-soap-packaging-boxes',
                'custom-chocolate-gift-boxes',
            ),
        ),
        array(
            'title' => __('Jewelry Gift Packaging Boxes', 'custom-box-theme'),
            'slugs' => array(
                'rigid-sliding-drawer-boxes',
                'premium-ribbon-gift-boxes',
                'kraft-round-gift-boxes',
                'candle-gift-packaging-boxes',
                'candle-jar-packaging-boxes',
            ),
        ),
        array(
            'title' => __('Food Gift Packaging Boxes', 'custom-box-theme'),
            'slugs' => array(
                'bakery-food-packaging-boxes',
                'dessert-gift-packaging-boxes',
                'dessert-packaging-boxes-with-inserts',
                'custom-chocolate-display-boxes',
                'pizza-packaging-boxes',
                'luxury-retail-paper-bags',
            ),
        ),
        array(
            'title' => __('Mooncake Gift Packaging Boxes', 'custom-box-theme'),
            'slugs' => array(
                'mooncake-gift-packaging-boxes',
                'mooncake-chocolate-gift-boxes',
                'custom-red-paper-bags',
                'luxury-teal-paper-bags',
                'luxury-watch-packaging-boxes',
                'watch-packaging-boxes',
                'pink-ribbon-gift-boxes',
            ),
        ),
    );

    foreach ($columns as $column_index => $column) {
        $terms = array();

        foreach ($column['slugs'] as $slug) {
            $term = custom_box_get_product_category_by_slug($slug);

            if ($term) {
                $terms[] = $term;
            }
        }

        $columns[$column_index]['terms'] = $terms;
    }

    return array_values(array_filter($columns, function ($column) {
        return !empty($column['terms']);
    }));
}

function custom_box_build_blog_article_content($raw_content) {
    $content = apply_filters('the_content', $raw_content);
    $toc = array();
    $seen_ids = array();

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

    return array(
        'content' => $content,
        'toc'     => $toc,
    );
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
    ?>
    <ul class="nav-menu">
        <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'custom-box-theme'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/about/')); ?>"><?php esc_html_e('About Us', 'custom-box-theme'); ?></a></li>
        <li class="product-menu-item">
            <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/')); ?>">
                <?php esc_html_e('Products', 'custom-box-theme'); ?>
            </a>
        </li>
        <li><a href="<?php echo esc_url($blog_link); ?>"><?php esc_html_e('Blog', 'custom-box-theme'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Contact Us', 'custom-box-theme'); ?></a></li>
    </ul>
    <?php
}

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
