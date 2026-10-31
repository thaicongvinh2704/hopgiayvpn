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
            <p><?php esc_html_e('VPN Packaging Factory has a total production capacity of over 10 million products per month. Capacity for each order depends on product structure, material, printing requirements, and finishing complexity.', 'custom-box-theme'); ?></p>
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
