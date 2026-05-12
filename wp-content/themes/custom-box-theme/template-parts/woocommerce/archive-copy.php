<?php
/**
 * Product archive supporting copy.
 */

defined('ABSPATH') || exit;

$archive_title = isset($args['archive_title']) ? $args['archive_title'] : '';
$copy_variant = isset($args['copy_variant']) ? $args['copy_variant'] : 'products';
?>

<section class="product-category-copy-section">
    <div class="container">
        <div class="product-category-copy-card">
            <?php if ('categories' === $copy_variant) : ?>
                <h2><?php printf(esc_html__('Why %s Leads Premium Packaging Solutions', 'custom-box-theme'), esc_html($archive_title)); ?></h2>
                <p><?php esc_html_e('When it comes to packaging, one size never fits all. Our category system helps brands explore structures, finishes, and materials made for real product presentation and reliable protection.', 'custom-box-theme'); ?></p>
                <h3><?php esc_html_e('Comprehensive Packaging Solutions Tailored to Every Business', 'custom-box-theme'); ?></h3>
                <ul>
                    <li><i class="fas fa-check-circle"></i><?php esc_html_e('Retail packaging categories crafted for shelf impact, premium finishes, and structural integrity.', 'custom-box-theme'); ?></li>
                    <li><i class="fas fa-check-circle"></i><?php esc_html_e('Eco-friendly packaging options with kraft, recyclable paperboard, and reduced-waste production paths.', 'custom-box-theme'); ?></li>
                </ul>
            <?php else : ?>
                <h2><?php printf(esc_html__('Custom %s Built Around Your Brand', 'custom-box-theme'), esc_html($archive_title)); ?></h2>
                <p><?php esc_html_e('Explore packaging options made for presentation, protection, and production flexibility. Each product can be customized by size, material, printing, finishing, quantity, and artwork requirements.', 'custom-box-theme'); ?></p>
                <h3><?php esc_html_e('Why Choose This Packaging Category?', 'custom-box-theme'); ?></h3>
                <ul>
                    <li><i class="fas fa-check-circle"></i><?php esc_html_e('Flexible structures and sizes for different product dimensions.', 'custom-box-theme'); ?></li>
                    <li><i class="fas fa-check-circle"></i><?php esc_html_e('Premium finishing options including matte, gloss, foil, embossing, and spot UV.', 'custom-box-theme'); ?></li>
                    <li><i class="fas fa-check-circle"></i><?php esc_html_e('Factory-direct support for samples, dielines, artwork checks, and bulk production.', 'custom-box-theme'); ?></li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</section>
