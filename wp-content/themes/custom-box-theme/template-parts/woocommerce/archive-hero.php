<?php
/**
 * Product archive hero.
 */

defined('ABSPATH') || exit;

$current_term = isset($args['current_term']) ? $args['current_term'] : null;
$archive_title = isset($args['archive_title']) ? $args['archive_title'] : '';
$archive_description = isset($args['archive_description']) ? $args['archive_description'] : '';
$products_url = function_exists('custom_box_get_products_url') ? custom_box_get_products_url() : home_url('/products/');
$is_products_hub = function_exists('is_shop') && is_shop();
$current_group = $current_term && !is_wp_error($current_term) && function_exists('custom_box_get_packaging_group_for_term')
    ? custom_box_get_packaging_group_for_term($current_term)
    : null;
$hero_image_id = 0;
$hero_image_url = $current_term && !is_wp_error($current_term) && function_exists('custom_box_get_product_category_card_image_url')
    ? custom_box_get_product_category_card_image_url($current_term, 'large')
    : '';

if ($current_term && !is_wp_error($current_term) && 'product_cat' === $current_term->taxonomy) {
    $hero_image_id = (int) get_term_meta($current_term->term_id, 'thumbnail_id', true);

    if (!$hero_image_id) {
        $hero_image_id = (int) get_term_meta($current_term->term_id, 'custom_box_category_image_id', true);
    }

    if (!$hero_image_id && $hero_image_url) {
        $hero_image_id = (int) attachment_url_to_postid($hero_image_url);
    }

    if ($hero_image_id) {
        $term_image_url = wp_get_attachment_image_url($hero_image_id, 'large');

        if ($term_image_url) {
            $hero_image_url = $term_image_url;
        }
    }

    if (!$hero_image_url && function_exists('wc_get_products')) {
        $category_products = wc_get_products(array(
            'status'   => 'publish',
            'limit'    => 1,
            'orderby'  => 'date',
            'order'    => 'DESC',
            'category' => array($current_term->slug),
            'return'   => 'ids',
        ));

        if (!empty($category_products[0])) {
            $product_image_id = get_post_thumbnail_id((int) $category_products[0]);
            $product_image_url = $product_image_id ? wp_get_attachment_image_url($product_image_id, 'large') : '';

            if ($product_image_url) {
                $hero_image_id = (int) $product_image_id;
                $hero_image_url = $product_image_url;
            }
        }
    }
}

if (!$hero_image_url) {
    $hero_image_url = get_template_directory_uri() . '/assets/images/product-banner1.png';
}

$get_local_image_dimensions = static function ($image_url) {
    $locations = array(
        array(
            'url'  => get_template_directory_uri(),
            'path' => get_template_directory(),
        ),
    );
    $uploads = wp_get_upload_dir();

    if (!empty($uploads['baseurl']) && !empty($uploads['basedir'])) {
        $locations[] = array(
            'url'  => $uploads['baseurl'],
            'path' => $uploads['basedir'],
        );
    }

    foreach ($locations as $location) {
        if (0 !== strpos($image_url, $location['url'])) {
            continue;
        }

        $relative_path = rawurldecode((string) wp_parse_url(substr($image_url, strlen($location['url'])), PHP_URL_PATH));
        $local_path = trailingslashit($location['path']) . ltrim($relative_path, '/\\');

        if (!is_readable($local_path)) {
            continue;
        }

        $dimensions = function_exists('wp_getimagesize')
            ? wp_getimagesize($local_path)
            : getimagesize($local_path);

        if (!empty($dimensions[0]) && !empty($dimensions[1])) {
            return array((int) $dimensions[0], (int) $dimensions[1]);
        }
    }

    return array(640, 480);
};
$hero_image_dimensions = $hero_image_id ? array() : $get_local_image_dimensions($hero_image_url);
?>

<section class="product-archive-hero product-category-landing-hero <?php echo $is_products_hub ? 'product-hub-hero' : ''; ?>">
    <div class="container">
        <nav class="product-archive-breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'custom-box-theme'); ?>">
            <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'custom-box-theme'); ?></a>
            <a href="<?php echo esc_url($products_url); ?>"><?php esc_html_e('Products', 'custom-box-theme'); ?></a>
            <?php if ($current_term && !is_wp_error($current_term)) : ?>
                <?php if (!empty($current_group['title'])) : ?>
                    <a href="<?php echo esc_url(function_exists('custom_box_get_packaging_group_url') ? custom_box_get_packaging_group_url($current_group['title']) : $products_url); ?>"><?php echo esc_html($current_group['title']); ?></a>
                <?php endif; ?>
                <span aria-current="page"><?php echo esc_html($current_term->name); ?></span>
            <?php else : ?>
                <span aria-current="page"><?php echo esc_html($archive_title); ?></span>
            <?php endif; ?>
        </nav>

        <div class="product-category-hero-grid">
            <div class="product-archive-hero-content">
                <p class="product-eyebrow"><?php esc_html_e('Custom Packaging Catalog', 'custom-box-theme'); ?></p>
                <h1><?php echo esc_html($archive_title); ?></h1>
                <p><?php echo esc_html(wp_strip_all_tags($archive_description)); ?></p>
                <div class="product-category-hero-actions">
                    <a class="btn-primary" href="<?php echo esc_url($is_products_hub ? '#category-hub' : home_url('/contact/#quote')); ?>"><?php echo esc_html($is_products_hub ? __('Explore Categories', 'custom-box-theme') : __('Get Your Box', 'custom-box-theme')); ?></a>
                    <a class="btn-outline" href="<?php echo esc_url(home_url('/contact/#quote')); ?>"><?php echo esc_html($is_products_hub ? __('Request Free Quote', 'custom-box-theme') : __('Request Free Sample', 'custom-box-theme')); ?></a>
                </div>
            </div>
            <div class="product-category-hero-image">
                <?php if ($hero_image_id) : ?>
                    <?php
                    echo wp_get_attachment_image(
                        $hero_image_id,
                        'large',
                        false,
                        array(
                            'alt'           => '',
                            'loading'       => 'eager',
                            'fetchpriority' => 'high',
                            'decoding'      => 'async',
                            'sizes'         => '(max-width: 767px) calc(100vw - 36px), (max-width: 1200px) 45vw, 640px',
                        )
                    );
                    ?>
                <?php else : ?>
                    <img
                        src="<?php echo esc_url($hero_image_url); ?>"
                        width="<?php echo esc_attr($hero_image_dimensions[0]); ?>"
                        height="<?php echo esc_attr($hero_image_dimensions[1]); ?>"
                        alt=""
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                    >
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
