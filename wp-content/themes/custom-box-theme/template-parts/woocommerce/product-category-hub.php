<?php
/**
 * Products page category hub.
 */

defined('ABSPATH') || exit;

$hub_groups = isset($args['hub_groups']) && is_array($args['hub_groups']) ? $args['hub_groups'] : array();

$group_intros = array(
    'Paper Box Types' => __('Compare common paper box structures by opening style, strength, display needs, and production efficiency.', 'custom-box-theme'),
    'Packaging by Industry' => __('Browse packaging categories by market so your structure, print, and finish match real buyer expectations.', 'custom-box-theme'),
    'Paper Bags & Packaging Add-ons' => __('Complete your packaging system with branded paper bags and practical accessories for retail or gifting.', 'custom-box-theme'),
    'Specialty Industry Packaging' => __('Explore targeted packaging solutions for regulated, premium, seasonal, and product-specific B2B projects.', 'custom-box-theme'),
);

$get_card_image = function ($term, $size = 'medium_large') {
    if (!$term || is_wp_error($term)) {
        return '';
    }

    $image_data = function_exists('custom_box_get_product_category_card_image_data')
        ? custom_box_get_product_category_card_image_data($term)
        : array(
            'url'    => get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp',
            'width'  => 640,
            'height' => 480,
        );

    return sprintf(
        '<img src="%1$s" width="%2$d" height="%3$d" alt="" loading="lazy" decoding="async" sizes="%4$s">',
        esc_url($image_data['url']),
        (int) $image_data['width'],
        (int) $image_data['height'],
        esc_attr('(max-width: 379px) calc(100vw - 36px), (max-width: 767px) calc(50vw - 28px), (max-width: 1200px) 25vw, 320px')
    );
};

?>

<nav class="product-category-jump-nav" aria-label="<?php esc_attr_e('Product category groups', 'custom-box-theme'); ?>">
    <div class="container">
        <div class="product-category-jump-inner">
            <?php foreach ($hub_groups as $group) : ?>
                <?php
                $group_title = !empty($group['title']) ? $group['title'] : '';
                $group_anchor = !empty($group['anchor']) ? $group['anchor'] : sanitize_title($group_title);
                ?>
                <a href="#<?php echo esc_attr($group_anchor); ?>"><?php echo esc_html($group_title); ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</nav>

<section class="product-category-hub-section" id="category-hub">
    <div class="container">
        <div class="product-category-hub-grid">
            <?php foreach ($hub_groups as $group) : ?>
                <?php
                $group_title = !empty($group['title']) ? $group['title'] : '';
                $group_anchor = !empty($group['anchor']) ? $group['anchor'] : sanitize_title($group_title);
                $group_terms = !empty($group['terms']) && is_array($group['terms']) ? $group['terms'] : array();
                ?>
                <section class="product-category-hub-group" id="<?php echo esc_attr($group_anchor); ?>">
                    <div class="product-hub-section-header">
                        <h2><?php echo esc_html($group_title); ?></h2>
                        <p><?php echo esc_html($group_intros[$group_title] ?? __('Explore category options with clean internal links and practical packaging paths.', 'custom-box-theme')); ?></p>
                    </div>
                    <div class="home-packaging-category-cards product-category-hub-card-grid">
                        <?php foreach ($group_terms as $category) : ?>
                            <?php
                            $category_link = get_term_link($category);
                            if (is_wp_error($category_link)) {
                                continue;
                            }
                            ?>
                            <a class="home-packaging-category-card product-category-hub-card" href="<?php echo esc_url($category_link); ?>" data-product-card>
                                <span class="home-packaging-category-image product-category-hub-card-image"><?php echo $get_card_image($category, 'medium_large'); ?></span>
                                <h3 class="home-packaging-category-title product-category-hub-card-title"><?php echo esc_html($category->name); ?></h3>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
</section>
