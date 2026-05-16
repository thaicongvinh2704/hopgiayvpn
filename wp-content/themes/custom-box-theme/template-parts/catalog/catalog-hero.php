<?php
/**
 * Catalog hero.
 */

defined('ABSPATH') || exit;

$hero_image = isset($args['hero_image']) ? $args['hero_image'] : '';
$catalog_url = isset($args['catalog_url']) ? $args['catalog_url'] : home_url('/catalog/#catalog-preview');
$quote_url = isset($args['quote_url']) ? $args['quote_url'] : home_url('/contact/#quote');
?>

<section class="catalog-hero">
    <div class="container">
        <nav class="catalog-breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'custom-box-theme'); ?>">
            <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'custom-box-theme'); ?></a>
            <span><?php esc_html_e('Catalog', 'custom-box-theme'); ?></span>
        </nav>

        <?php if ($hero_image) : ?>
            <div class="catalog-banner-frame">
                <img src="<?php echo esc_url($hero_image); ?>" alt="<?php esc_attr_e('VPN Packaging Factory custom paper box catalog banner', 'custom-box-theme'); ?>" decoding="async" fetchpriority="high">
            </div>
        <?php endif; ?>

        <div class="catalog-banner-copy">
            <div class="catalog-hero-copy">
                <span class="catalog-kicker"><?php esc_html_e('Custom Packaging Catalog', 'custom-box-theme'); ?></span>
                <h1><?php esc_html_e('Custom Paper Box Catalog', 'custom-box-theme'); ?></h1>
                <p><?php esc_html_e('Explore our custom paper box packaging catalog for rigid boxes, folding cartons, cosmetic boxes, gift boxes, food packaging boxes, and OEM/ODM packaging solutions.', 'custom-box-theme'); ?></p>
            </div>
            <div class="catalog-actions">
                <a class="btn-primary" href="<?php echo esc_url($catalog_url); ?>"><?php esc_html_e('View Catalog Preview', 'custom-box-theme'); ?></a>
                <a class="btn-outline" href="<?php echo esc_url($quote_url); ?>"><?php esc_html_e('Request a Quote', 'custom-box-theme'); ?></a>
            </div>
            <div class="catalog-proof-row" aria-label="<?php esc_attr_e('Factory highlights', 'custom-box-theme'); ?>">
                <span><strong>9+</strong> <?php esc_html_e('Years Experience', 'custom-box-theme'); ?></span>
                <span><strong>50+</strong> <?php esc_html_e('Packaging Categories', 'custom-box-theme'); ?></span>
                <span><strong>OEM</strong> / <?php esc_html_e('ODM Support', 'custom-box-theme'); ?></span>
            </div>
        </div>
    </div>
</section>
