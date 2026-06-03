<?php
/**
 * Product category grid archive view.
 */

defined('ABSPATH') || exit;

$current_term = isset($args['current_term']) ? $args['current_term'] : null;
$parent_term = isset($args['parent_term']) ? $args['parent_term'] : null;
$landing_categories = isset($args['landing_categories']) && is_array($args['landing_categories']) ? $args['landing_categories'] : array();
$landing_root_link = isset($args['landing_root_link']) ? $args['landing_root_link'] : home_url('/shop/');
$is_all_categories = $current_term && $parent_term && !is_wp_error($current_term) && !is_wp_error($parent_term) && (int) $current_term->term_id === (int) $parent_term->term_id;
?>

<section class="product-category-landing-section">
    <div class="container product-category-landing-layout">
        <aside class="product-category-tabs">
            <a class="<?php echo $is_all_categories ? 'is-active' : ''; ?>" href="<?php echo esc_url($landing_root_link); ?>">
                <i class="fas fa-border-all"></i>
                <span><?php esc_html_e('All Category', 'custom-box-theme'); ?></span>
            </a>
            <?php foreach ($landing_categories as $category) : ?>
                <?php $category_link = get_term_link($category); ?>
                <?php if (is_wp_error($category_link)) { continue; } ?>
                <?php $is_current_category = $current_term && !is_wp_error($current_term) && (int) $current_term->term_id === (int) $category->term_id; ?>
                <a class="<?php echo $is_current_category ? 'is-active' : ''; ?>" href="<?php echo esc_url($category_link); ?>">
                    <i class="fas fa-box-open"></i>
                    <span><?php echo esc_html($category->name); ?></span>
                </a>
            <?php endforeach; ?>
            <a class="product-category-more" href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/products/')); ?>">
                <i class="fas fa-plus-circle"></i>
                <span><?php esc_html_e('More Categories', 'custom-box-theme'); ?></span>
            </a>
        </aside>

        <div class="product-category-card-grid">
            <?php foreach ($landing_categories as $category) : ?>
                <?php
                $category_link = get_term_link($category);
                if (is_wp_error($category_link)) {
                    continue;
                }

                $thumbnail_id = (int) get_term_meta($category->term_id, 'thumbnail_id', true);
                $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium_large') : get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp';
                $is_current_category = $current_term && !is_wp_error($current_term) && (int) $current_term->term_id === (int) $category->term_id;
                ?>
                <a class="product-category-card <?php echo $is_current_category ? 'is-active' : ''; ?>" href="<?php echo esc_url($category_link); ?>">
                    <span class="product-category-card-image">
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($category->name); ?>" loading="lazy" decoding="async">
                    </span>
                    <span class="product-category-card-title"><?php echo esc_html($category->name); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
