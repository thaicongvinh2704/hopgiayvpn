<?php
/**
 * Product showcase gallery.
 */

defined('ABSPATH') || exit;

$gallery = isset($args['gallery']) && is_array($args['gallery']) ? $args['gallery'] : array();
?>

<section class="catalog-section catalog-gallery-section">
    <div class="container">
        <div class="catalog-section-heading">
            <span class="catalog-kicker"><?php esc_html_e('Product Showcase', 'custom-box-theme'); ?></span>
            <h2><?php esc_html_e('Product Showcase', 'custom-box-theme'); ?></h2>
            <p><?php esc_html_e('Explore real custom paper box packaging examples produced for different product categories and brand requirements.', 'custom-box-theme'); ?></p>
        </div>

        <div class="catalog-gallery-grid">
            <?php foreach ($gallery as $item) : ?>
                <figure>
                    <?php if (!empty($item['url'])) : ?>
                        <a href="<?php echo esc_url($item['url']); ?>">
                    <?php endif; ?>
                    <img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['alt']); ?>" loading="lazy" decoding="async">
                    <?php if (!empty($item['title']) || !empty($item['meta'])) : ?>
                        <figcaption>
                            <?php if (!empty($item['meta'])) : ?>
                                <span><?php echo esc_html($item['meta']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($item['title'])) : ?>
                                <strong><?php echo esc_html($item['title']); ?></strong>
                            <?php endif; ?>
                        </figcaption>
                    <?php endif; ?>
                    <?php if (!empty($item['url'])) : ?>
                        </a>
                    <?php endif; ?>
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>
