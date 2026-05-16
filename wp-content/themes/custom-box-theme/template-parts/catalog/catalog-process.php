<?php
/**
 * Custom packaging process.
 */

defined('ABSPATH') || exit;

$process = isset($args['process']) && is_array($args['process']) ? $args['process'] : array();
?>

<section class="catalog-section catalog-process-section">
    <div class="container">
        <div class="catalog-section-heading catalog-section-heading-center">
            <span class="catalog-kicker"><?php esc_html_e('Production Workflow', 'custom-box-theme'); ?></span>
            <h2><?php esc_html_e('How Custom Paper Box Production Works', 'custom-box-theme'); ?></h2>
        </div>

        <ol class="catalog-process-grid">
            <?php foreach ($process as $index => $step) : ?>
                <li>
                    <span><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                    <h3><?php echo esc_html($step); ?></h3>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
