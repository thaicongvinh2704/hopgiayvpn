<?php
/**
 * Materials and finishing options.
 */

defined('ABSPATH') || exit;

$materials = isset($args['materials']) && is_array($args['materials']) ? $args['materials'] : array();
$finishes = isset($args['finishes']) && is_array($args['finishes']) ? $args['finishes'] : array();
?>

<section class="catalog-section catalog-options-section">
    <div class="container catalog-options-layout">
        <div class="catalog-option-block">
            <div class="catalog-section-heading">
                <span class="catalog-kicker"><?php esc_html_e('Paper Materials', 'custom-box-theme'); ?></span>
                <h2><?php esc_html_e('Materials & Paper Options', 'custom-box-theme'); ?></h2>
                <p><?php esc_html_e('We support multiple paper materials depending on box structure, product weight, brand positioning, and budget requirements.', 'custom-box-theme'); ?></p>
            </div>
            <div class="catalog-option-list">
                <?php foreach ($materials as $material) : ?>
                    <span><i class="fas fa-check"></i><?php echo esc_html($material); ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="catalog-option-block">
            <div class="catalog-section-heading">
                <span class="catalog-kicker"><?php esc_html_e('Printing & Finishing', 'custom-box-theme'); ?></span>
                <h2><?php esc_html_e('Printing & Finishing Options', 'custom-box-theme'); ?></h2>
                <p><?php esc_html_e('Enhance your packaging with custom printing, premium surface finishes, and brand-focused details.', 'custom-box-theme'); ?></p>
            </div>
            <div class="catalog-option-list catalog-option-list-dense">
                <?php foreach ($finishes as $finish) : ?>
                    <span><i class="fas fa-check"></i><?php echo esc_html($finish); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
