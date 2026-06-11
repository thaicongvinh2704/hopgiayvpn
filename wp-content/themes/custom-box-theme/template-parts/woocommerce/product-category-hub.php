<?php
/**
 * Products page category hub.
 */

defined('ABSPATH') || exit;

$hub_groups = isset($args['hub_groups']) && is_array($args['hub_groups']) ? $args['hub_groups'] : array();

$category_summaries = array(
    'custom-packaging-boxes'                 => __('A flexible starting point for branded paper packaging projects.', 'custom-box-theme'),
    'custom-paper-boxes'                     => __('Custom paperboard boxes for retail, gift, and export packaging.', 'custom-box-theme'),
    'custom-printed-paper-boxes'             => __('Printed paper boxes with logo, color, and finishing options.', 'custom-box-theme'),
    'rigid-boxes'                            => __('Premium rigid boxes for high-value product presentation.', 'custom-box-theme'),
    'folding-carton-boxes'                   => __('Lightweight folding cartons for efficient retail packaging.', 'custom-box-theme'),
    'magnetic-closure-boxes'                 => __('Magnetic boxes with a refined opening and closing experience.', 'custom-box-theme'),
    'drawer-boxes'                           => __('Sliding drawer boxes for gift sets and organized product kits.', 'custom-box-theme'),
    'lid-and-base-boxes'                     => __('Classic two-piece boxes for premium gift and retail packaging.', 'custom-box-theme'),
    'paper-tube-packaging'                   => __('Round paper tube packaging for food, beauty, and specialty goods.', 'custom-box-theme'),
    'corrugated-mailer-boxes'                => __('Protective mailer boxes for ecommerce and shipping presentation.', 'custom-box-theme'),
    'cosmetic-paper-boxes'                   => __('Beauty packaging boxes for skincare, makeup, and cosmetic lines.', 'custom-box-theme'),
    'perfume-packaging-boxes'                => __('Perfume boxes designed for fragrance bottles and gift sets.', 'custom-box-theme'),
    'skincare-packaging-boxes'               => __('Clean skincare packaging for creams, serums, and treatment sets.', 'custom-box-theme'),
    'jewelry-paper-boxes'                    => __('Compact premium boxes for jewelry, watches, and accessories.', 'custom-box-theme'),
    'gift-paper-boxes'                       => __('Gift boxes for seasonal campaigns and premium retail presentation.', 'custom-box-theme'),
    'chocolate-gift-boxes'                   => __('Chocolate packaging for confectionery gifts and retail displays.', 'custom-box-theme'),
    'food-paper-boxes'                       => __('Food paper boxes for dry goods, snacks, bakery, and takeaway items.', 'custom-box-theme'),
    'bakery-packaging-boxes'                 => __('Bakery boxes for cakes, pastries, desserts, and gift treats.', 'custom-box-theme'),
    'candle-packaging-boxes'                 => __('Candle boxes with protective structure and premium shelf appeal.', 'custom-box-theme'),
    'paper-bags-with-logo'                   => __('Branded paper bags for retail carry-out and gift presentation.', 'custom-box-theme'),
    'packaging-accessories'                  => __('Add-ons such as inserts, sleeves, ribbons, and finishing details.', 'custom-box-theme'),
    'pharmaceutical-packaging-boxes'          => __('Healthcare cartons for medicine, medical kits, and regulated products.', 'custom-box-theme'),
    'supplement-packaging-boxes'             => __('Supplement boxes for vitamins, wellness products, and capsules.', 'custom-box-theme'),
    'beauty-skincare-packaging'              => __('Packaging systems for beauty, skincare, and personal care brands.', 'custom-box-theme'),
    'premium-food-beverage-packaging'        => __('Premium packaging for tea, coffee, chocolate, and beverage products.', 'custom-box-theme'),
    'electronics-accessories-packaging'      => __('Retail boxes for phone accessories, cables, and small electronics.', 'custom-box-theme'),
    'fashion-sportswear-packaging'           => __('Fashion packaging for apparel, footwear, and sportswear products.', 'custom-box-theme'),
    'sports-packaging-boxes'                 => __('Packaging for sports goods, fitness accessories, and athletic products.', 'custom-box-theme'),
    'wine-premium-drink-packaging'           => __('Wine and drink packaging for bottles, sets, and premium gifting.', 'custom-box-theme'),
    'corporate-gift-packaging'               => __('Corporate gift packaging for events, VIP kits, and campaigns.', 'custom-box-theme'),
    'home-lifestyle-packaging'               => __('Packaging for homeware, lifestyle products, and fragile goods.', 'custom-box-theme'),
    'back-to-school-stationery-packaging'    => __('Stationery packaging for school supplies, art sets, and gift kits.', 'custom-box-theme'),
);

$group_intros = array(
    'Paper Box Types' => __('Compare common paper box structures by opening style, strength, display needs, and production efficiency.', 'custom-box-theme'),
    'Packaging by Industry' => __('Browse packaging categories by market so your structure, print, and finish match real buyer expectations.', 'custom-box-theme'),
    'Paper Bags & Packaging Add-ons' => __('Complete your packaging system with branded paper bags and practical accessories for retail or gifting.', 'custom-box-theme'),
    'Specialty Industry Packaging' => __('Explore targeted packaging solutions for regulated, premium, seasonal, and product-specific B2B projects.', 'custom-box-theme'),
);

$popular_slugs = array(
    'custom-packaging-boxes',
    'rigid-boxes',
    'drawer-boxes',
    'cosmetic-paper-boxes',
    'food-paper-boxes',
    'paper-bags-with-logo',
);

$get_summary = function ($term) use ($category_summaries) {
    if ($term && !is_wp_error($term) && !empty($category_summaries[$term->slug])) {
        return $category_summaries[$term->slug];
    }

    return __('Customizable packaging with size, material, printing, and finishing options.', 'custom-box-theme');
};

$get_card_image = function ($term, $size = 'medium_large') {
    if (!$term || is_wp_error($term)) {
        return '';
    }

    $image_id = (int) get_term_meta($term->term_id, 'thumbnail_id', true);

    if (!$image_id) {
        $image_id = (int) get_term_meta($term->term_id, 'custom_box_category_image_id', true);
    }

    if ($image_id) {
        return wp_get_attachment_image(
            $image_id,
            $size,
            false,
            array(
                'alt'      => $term->name,
                'loading'  => 'lazy',
                'decoding' => 'async',
            )
        );
    }

    $asset_url = function_exists('custom_box_get_product_category_asset_image_url') ? custom_box_get_product_category_asset_image_url($term) : '';

    if (!$asset_url && function_exists('wc_get_products')) {
        $category_products = wc_get_products(array(
            'status'   => 'publish',
            'limit'    => 1,
            'orderby'  => 'date',
            'order'    => 'DESC',
            'category' => array($term->slug),
            'return'   => 'ids',
        ));

        if (!empty($category_products[0])) {
            $product_image_id = get_post_thumbnail_id((int) $category_products[0]);
            $asset_url = $product_image_id ? wp_get_attachment_image_url($product_image_id, $size) : '';
        }
    }

    if (!$asset_url) {
        $asset_url = get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp';
    }

    return '<img src="' . esc_url($asset_url) . '" width="640" height="480" alt="' . esc_attr($term->name) . '" loading="lazy" decoding="async">';
};

$popular_terms = array();

foreach ($popular_slugs as $popular_slug) {
    $popular_term = function_exists('custom_box_get_product_category_by_slug') ? custom_box_get_product_category_by_slug($popular_slug) : null;

    if ($popular_term) {
        $popular_terms[] = $popular_term;
    }
}

$featured_products = function_exists('wc_get_products') ? wc_get_products(array(
    'status'  => 'publish',
    'limit'   => 6,
    'orderby' => 'date',
    'order'   => 'DESC',
)) : array();
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

<?php if (!empty($popular_terms)) : ?>
    <section class="popular-categories-section" id="popular-categories">
        <div class="container">
            <div class="product-hub-section-header">
                <p class="product-eyebrow"><?php esc_html_e('Start Here', 'custom-box-theme'); ?></p>
                <h2><?php esc_html_e('Popular Categories', 'custom-box-theme'); ?></h2>
            </div>

            <div class="popular-category-grid">
                <?php foreach ($popular_terms as $category) : ?>
                    <?php
                    $category_link = get_term_link($category);
                    if (is_wp_error($category_link)) {
                        continue;
                    }
                    ?>
                    <a class="popular-category-card" href="<?php echo esc_url($category_link); ?>">
                        <span class="popular-category-image"><?php echo wp_kses_post($get_card_image($category, 'medium_large')); ?></span>
                        <span class="popular-category-body">
                            <h3 class="popular-category-title"><?php echo esc_html($category->name); ?></h3>
                            <span class="popular-category-summary"><?php echo esc_html($get_summary($category)); ?></span>
                            <span class="popular-category-cta"><?php esc_html_e('View Category', 'custom-box-theme'); ?> <i class="fas fa-arrow-right"></i></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

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
                    <div class="product-category-hub-card-grid">
                        <?php foreach ($group_terms as $category) : ?>
                            <?php
                            $category_link = get_term_link($category);
                            if (is_wp_error($category_link)) {
                                continue;
                            }
                            ?>
                            <a class="product-category-hub-card" href="<?php echo esc_url($category_link); ?>">
                                <span class="product-category-hub-card-image"><?php echo wp_kses_post($get_card_image($category, 'medium_large')); ?></span>
                                <span class="product-category-hub-card-body">
                                    <h3><?php echo esc_html($category->name); ?></h3>
                                    <span class="product-category-hub-card-summary"><?php echo esc_html($get_summary($category)); ?></span>
                                    <span class="product-category-hub-card-cta"><?php esc_html_e('View Category', 'custom-box-theme'); ?> <i class="fas fa-arrow-right"></i></span>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if (!empty($featured_products)) : ?>
    <section class="featured-packaging-examples-section">
        <div class="container">
            <div class="product-hub-section-header">
                <p class="product-eyebrow"><?php esc_html_e('Packaging Examples', 'custom-box-theme'); ?></p>
                <h2><?php esc_html_e('Featured Packaging Examples', 'custom-box-theme'); ?></h2>
                <p><?php esc_html_e('A few recent product examples for buyers who want to compare structure, finish, and presentation after choosing a category path.', 'custom-box-theme'); ?></p>
            </div>

            <div class="featured-packaging-examples-grid">
                <?php foreach ($featured_products as $product) : ?>
                    <?php
                    if (!$product instanceof WC_Product) {
                        continue;
                    }

                    $product_image = $product->get_image('medium_large', array(
                        'loading'  => 'lazy',
                        'decoding' => 'async',
                    ));
                    ?>
                    <a class="featured-packaging-example-card" href="<?php echo esc_url($product->get_permalink()); ?>">
                        <span class="featured-packaging-example-image"><?php echo wp_kses_post($product_image); ?></span>
                        <span class="featured-packaging-example-title"><?php echo esc_html($product->get_name()); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
