<?php
/**
 * Product category grid archive view.
 */

defined('ABSPATH') || exit;

$current_term = isset($args['current_term']) ? $args['current_term'] : null;
$parent_term = isset($args['parent_term']) ? $args['parent_term'] : null;
$landing_categories = isset($args['landing_categories']) && is_array($args['landing_categories']) ? $args['landing_categories'] : array();
$landing_root_link = isset($args['landing_root_link']) ? $args['landing_root_link'] : (function_exists('custom_box_get_products_url') ? custom_box_get_products_url() : home_url('/products/'));
$is_all_categories = $current_term && $parent_term && !is_wp_error($current_term) && !is_wp_error($parent_term) && (int) $current_term->term_id === (int) $parent_term->term_id;
?>

<section class="product-category-landing-section">
    <div class="container product-category-landing-layout">
        <div class="product-category-card-grid product-category-results" data-product-list>
            <?php foreach ($landing_categories as $category) : ?>
                <?php
                $category_link = get_term_link($category);
                if (is_wp_error($category_link)) {
                    continue;
                }

                $image_data = function_exists('custom_box_get_product_category_card_image_data')
                    ? custom_box_get_product_category_card_image_data($category)
                    : array(
                        'url'    => get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp',
                        'width'  => 640,
                        'height' => 480,
                    );

                $is_current_category = $current_term && !is_wp_error($current_term) && (int) $current_term->term_id === (int) $category->term_id;
                $category_title_id = 'product-category-card-title-' . (int) $category->term_id;
                ?>
                <a
                    class="product-category-card <?php echo $is_current_category ? 'is-active' : ''; ?>"
                    href="<?php echo esc_url($category_link); ?>"
                    data-product-card
                    aria-labelledby="<?php echo esc_attr($category_title_id); ?>"
                    <?php echo $is_current_category ? 'aria-current="page"' : ''; ?>
                >
                    <span class="product-category-card-image">
                        <img
                            src="<?php echo esc_url($image_data['url']); ?>"
                            width="<?php echo esc_attr((string) $image_data['width']); ?>"
                            height="<?php echo esc_attr((string) $image_data['height']); ?>"
                            alt=""
                            loading="lazy"
                            decoding="async"
                            sizes="(max-width: 379px) calc(100vw - 36px), (max-width: 767px) calc(50vw - 28px), (max-width: 1200px) 33vw, 360px"
                        >
                    </span>
                    <h2 id="<?php echo esc_attr($category_title_id); ?>" class="product-category-card-title"><?php echo esc_html($category->name); ?></h2>
                </a>
            <?php endforeach; ?>
        </div>

        <details
            class="product-category-tabs-disclosure product-category-taxonomy"
            data-archive-categories-disclosure
            data-responsive-disclosure
        >
            <summary class="product-category-tabs-summary"><?php esc_html_e('Browse Categories', 'custom-box-theme'); ?></summary>
            <nav class="product-category-tabs" aria-label="<?php esc_attr_e('Product categories', 'custom-box-theme'); ?>">
                <a class="<?php echo $is_all_categories ? 'is-active' : ''; ?>" href="<?php echo esc_url($landing_root_link); ?>" <?php echo $is_all_categories ? 'aria-current="page"' : ''; ?>>
                    <i class="fas fa-border-all" aria-hidden="true"></i>
                    <span><?php esc_html_e('All Categories', 'custom-box-theme'); ?></span>
                </a>
                <?php foreach ($landing_categories as $category) : ?>
                    <?php $category_link = get_term_link($category); ?>
                    <?php if (is_wp_error($category_link)) { continue; } ?>
                    <?php $is_current_category = $current_term && !is_wp_error($current_term) && (int) $current_term->term_id === (int) $category->term_id; ?>
                    <a class="<?php echo $is_current_category ? 'is-active' : ''; ?>" href="<?php echo esc_url($category_link); ?>" <?php echo $is_current_category ? 'aria-current="page"' : ''; ?>>
                        <i class="fas fa-box-open" aria-hidden="true"></i>
                        <span><?php echo esc_html($category->name); ?></span>
                    </a>
                <?php endforeach; ?>
                <a class="product-category-more" href="<?php echo esc_url(function_exists('custom_box_get_products_url') ? custom_box_get_products_url() : home_url('/products/')); ?>">
                    <i class="fas fa-plus-circle" aria-hidden="true"></i>
                    <span><?php esc_html_e('More Categories', 'custom-box-theme'); ?></span>
                </a>
            </nav>
        </details>
    </div>
</section>
