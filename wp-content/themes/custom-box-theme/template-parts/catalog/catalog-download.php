<?php
/**
 * Catalog download section.
 */

defined('ABSPATH') || exit;

$catalog_url = isset($args['catalog_url']) ? $args['catalog_url'] : home_url('/catalog/#catalog-preview');
$contact_url = isset($args['contact_url']) ? $args['contact_url'] : home_url('/contact/#quote');
?>

<section class="catalog-download-section">
    <div class="container">
        <div class="catalog-download-card">
            <div class="catalog-download-icon" aria-hidden="true">
                <i class="fas fa-file-arrow-down"></i>
            </div>
            <div class="catalog-download-copy">
                <span class="catalog-kicker"><?php esc_html_e('Digital PDF Catalog', 'custom-box-theme'); ?></span>
                <h2><?php esc_html_e('Download Our Paper Box Catalog', 'custom-box-theme'); ?></h2>
                <p><?php esc_html_e('View our latest custom paper box designs, materials, printing finishes, box structures, and factory production capabilities.', 'custom-box-theme'); ?></p>
            </div>
            <div class="catalog-download-actions">
                <a class="btn-primary" href="<?php echo esc_url($catalog_url); ?>"><?php esc_html_e('Download PDF Catalog', 'custom-box-theme'); ?></a>
                <a class="btn-outline" href="<?php echo esc_url($contact_url); ?>"><?php esc_html_e('Contact Factory', 'custom-box-theme'); ?></a>
            </div>
        </div>
    </div>
</section>
