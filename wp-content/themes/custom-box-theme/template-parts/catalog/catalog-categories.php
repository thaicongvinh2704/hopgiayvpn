<?php
/**
 * Product catalog categories.
 */

defined('ABSPATH') || exit;

$categories = isset($args['categories']) && is_array($args['categories']) ? $args['categories'] : array();
?>

<section class="catalog-section catalog-categories-section">
    <div class="container">
        <div class="catalog-section-heading">
            <span class="catalog-kicker"><?php esc_html_e('Product Categories', 'custom-box-theme'); ?></span>
            <h2><?php esc_html_e('Product Catalog Categories', 'custom-box-theme'); ?></h2>
            <p><?php esc_html_e('Explore major paper packaging categories for retail brands, importers, wholesalers, and global B2B packaging buyers.', 'custom-box-theme'); ?></p>
        </div>

        <div class="catalog-card-grid catalog-card-grid-four">
            <?php foreach ($categories as $category) : ?>
                <article class="catalog-category-card">
                    <a class="catalog-category-image" href="<?php echo esc_url($category['url']); ?>">
                        <img src="<?php echo esc_url($category['image']); ?>" alt="<?php echo esc_attr($category['alt']); ?>" loading="lazy" decoding="async">
                    </a>
                    <div class="catalog-category-body">
                        <h3><?php echo esc_html($category['title']); ?></h3>
                        <p><?php echo esc_html($category['description']); ?></p>
                        <a class="catalog-text-link" href="<?php echo esc_url($category['url']); ?>"><?php esc_html_e('View Details', 'custom-box-theme'); ?></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
