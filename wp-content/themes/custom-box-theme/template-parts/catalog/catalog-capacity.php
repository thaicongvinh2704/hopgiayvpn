<?php
/**
 * Factory production capacity.
 */

defined('ABSPATH') || exit;

$capacity = isset($args['capacity']) && is_array($args['capacity']) ? $args['capacity'] : array();
?>

<section class="catalog-section catalog-capacity-section">
    <div class="container">
        <div class="catalog-section-heading">
            <span class="catalog-kicker"><?php esc_html_e('Factory Capability', 'custom-box-theme'); ?></span>
            <h2><?php esc_html_e('Factory Production Capacity', 'custom-box-theme'); ?></h2>
            <p><?php esc_html_e('VPN Packaging Factory supports custom paper box production from 10,000 to 3,000,000 boxes per month, depending on box structure, material, printing requirements, and finishing complexity.', 'custom-box-theme'); ?></p>
        </div>

        <div class="catalog-stat-grid">
            <?php foreach ($capacity as $stat) : ?>
                <article class="catalog-stat-card">
                    <i class="fas <?php echo esc_attr($stat['icon']); ?>"></i>
                    <strong><?php echo esc_html($stat['value']); ?></strong>
                    <span><?php echo esc_html($stat['label']); ?></span>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
