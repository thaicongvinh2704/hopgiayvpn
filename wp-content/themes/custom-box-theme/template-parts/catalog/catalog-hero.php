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
    <div class="container catalog-hero-grid">
        <div class="catalog-hero-copy">
            <nav class="catalog-breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'custom-box-theme'); ?>">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'custom-box-theme'); ?></a>
                <span><?php esc_html_e('Catalog', 'custom-box-theme'); ?></span>
            </nav>
            <span class="catalog-kicker"><?php esc_html_e('Custom Packaging Catalog', 'custom-box-theme'); ?></span>
            <h1><?php esc_html_e('Custom Paper Box Catalog', 'custom-box-theme'); ?></h1>
            <p><?php esc_html_e('Explore our custom paper box packaging catalog for rigid boxes, folding cartons, cosmetic boxes, gift boxes, food packaging boxes, and OEM/ODM packaging solutions.', 'custom-box-theme'); ?></p>
            <div class="catalog-actions">
                <a class="btn-primary" href="<?php echo esc_url($catalog_url); ?>"><?php esc_html_e('Download Catalog', 'custom-box-theme'); ?></a>
                <a class="btn-outline" href="<?php echo esc_url($quote_url); ?>"><?php esc_html_e('Request a Quote', 'custom-box-theme'); ?></a>
            </div>
        </div>

        <?php if ($hero_image) : ?>
            <div class="catalog-hero-media">
                <img src="<?php echo esc_url($hero_image); ?>" alt="<?php esc_attr_e('custom paper box catalog product showcase', 'custom-box-theme'); ?>" decoding="async">
            </div>
        <?php endif; ?>
    </div>
</section>
