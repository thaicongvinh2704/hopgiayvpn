<?php
/**
 * Box structures and styles.
 */

defined('ABSPATH') || exit;

$box_styles = isset($args['box_styles']) && is_array($args['box_styles']) ? $args['box_styles'] : array();

$style_details = array(
    'Lid and Base Box' => array(
        'icon' => 'fa-box-open',
        'image' => content_url('/uploads/2026/05/purple-luxury-rigid-gift-box-open.jpeg'),
        'alt' => __('open lid and base rigid paper gift box', 'custom-box-theme'),
        'badge' => __('Popular', 'custom-box-theme'),
        'description' => __('Classic two-piece rigid paper box for a premium and stable product presentation.', 'custom-box-theme'),
        'best_for' => __('Gifts, cosmetics, jewelry', 'custom-box-theme'),
        'tags' => array(
            __('Custom Size', 'custom-box-theme'),
            __('Premium Finish', 'custom-box-theme'),
        ),
    ),
    'Magnetic Closure Box' => array(
        'icon' => 'fa-box',
        'image' => content_url('/uploads/2026/05/white-perfume-display-packaging-box-open.jpeg'),
        'alt' => __('premium rigid magnetic closure paper box for perfume packaging', 'custom-box-theme'),
        'badge' => __('Luxury', 'custom-box-theme'),
        'description' => __('Rigid box with magnetic closure for high-end gift sets and brand presentation.', 'custom-box-theme'),
        'best_for' => __('Luxury sets, cosmetics, accessories', 'custom-box-theme'),
        'tags' => array(
            __('Premium Finish', 'custom-box-theme'),
            __('Logo Printing', 'custom-box-theme'),
        ),
    ),
    'Drawer Box' => array(
        'icon' => 'fa-inbox',
        'image' => content_url('/uploads/2026/05/black-drawer-watch-packaging-box-open.jpeg'),
        'alt' => __('black pull out drawer paper box for watch packaging', 'custom-box-theme'),
        'badge' => __('Premium', 'custom-box-theme'),
        'description' => __('Pull-out rigid box with ribbon or sleeve design for a premium unboxing experience.', 'custom-box-theme'),
        'best_for' => __('Jewelry, watches, premium gifts', 'custom-box-theme'),
        'tags' => array(
            __('Custom Size', 'custom-box-theme'),
            __('Custom Insert', 'custom-box-theme'),
        ),
    ),
    'Folding Carton Box' => array(
        'icon' => 'fa-cube',
        'image' => get_template_directory_uri() . '/assets/images/Tuck-Top-Boxes_1758880242.webp',
        'alt' => __('custom printed folding carton paper boxes', 'custom-box-theme'),
        'badge' => '',
        'description' => __('Lightweight printed paper box for retail products and large-volume production.', 'custom-box-theme'),
        'best_for' => __('Skincare, food, retail products', 'custom-box-theme'),
        'tags' => array(
            __('Logo Printing', 'custom-box-theme'),
            __('Bulk Order', 'custom-box-theme'),
        ),
    ),
    'Sleeve Box' => array(
        'icon' => 'fa-grip-lines',
        'image' => content_url('/uploads/2026/05/blue-cosmetic-set-packaging-box-detail.png'),
        'alt' => __('custom paper sleeve style cosmetic packaging box detail', 'custom-box-theme'),
        'badge' => '',
        'description' => __('Paper sleeve box structure for product sets, trays, inner boxes, and branded outer packaging.', 'custom-box-theme'),
        'best_for' => __('Product sets, trays, retail kits', 'custom-box-theme'),
        'tags' => array(
            __('Logo Printing', 'custom-box-theme'),
            __('Custom Size', 'custom-box-theme'),
        ),
    ),
    'Rigid Gift Box' => array(
        'icon' => 'fa-gift',
        'image' => content_url('/uploads/2026/05/red-floral-mooncake-gift-packaging-box-open.jpeg'),
        'alt' => __('luxury rigid paper gift box with premium printed finish', 'custom-box-theme'),
        'badge' => '',
        'description' => __('High-end rigid paper gift box with inserts, foil stamping, embossing, and lamination.', 'custom-box-theme'),
        'best_for' => __('Gift sets, premium products, branding', 'custom-box-theme'),
        'tags' => array(
            __('Custom Insert', 'custom-box-theme'),
            __('Premium Finish', 'custom-box-theme'),
        ),
    ),
);
?>

<section class="catalog-section catalog-box-styles-section catalog-structures catalog-box-structures">
    <div class="container">
        <div class="catalog-structures-header">
            <div class="catalog-section-heading">
                <span class="catalog-kicker"><?php esc_html_e('Structures', 'custom-box-theme'); ?></span>
                <h2><?php esc_html_e('Box Structures We Manufacture', 'custom-box-theme'); ?></h2>
                <p><?php esc_html_e('Choose the right paper box structure for your product, budget, branding, and bulk production requirements.', 'custom-box-theme'); ?></p>
            </div>
            <div class="catalog-structures-trust" aria-label="<?php esc_attr_e('Manufacturing options', 'custom-box-theme'); ?>">
                <span><?php esc_html_e('Custom Size', 'custom-box-theme'); ?></span>
                <span><?php esc_html_e('OEM/ODM', 'custom-box-theme'); ?></span>
                <span><?php esc_html_e('Bulk B2B Production', 'custom-box-theme'); ?></span>
            </div>
        </div>

        <div class="catalog-chip-grid structures-grid">
            <?php foreach ($box_styles as $index => $style) : ?>
                <?php
                $detail = isset($style_details[$style]) ? $style_details[$style] : array(
                    'icon' => 'fa-box',
                    'image' => '',
                    'alt' => $style,
                    'badge' => '',
                    'description' => __('Custom paper box structure for brand packaging projects.', 'custom-box-theme'),
                    'best_for' => __('Custom packaging projects', 'custom-box-theme'),
                    'tags' => array(__('Custom Size', 'custom-box-theme'), __('Logo Printing', 'custom-box-theme')),
                );
                $advice_label = sprintf(
                    /* translators: %s: box style name. */
                    __('Get advice for this %s', 'custom-box-theme'),
                    $style
                );
                ?>
                <article class="catalog-chip-card structure-card">
                    <div class="structure-card__media">
                        <?php if (!empty($detail['image'])) : ?>
                            <img src="<?php echo esc_url($detail['image']); ?>" alt="<?php echo esc_attr($detail['alt']); ?>" loading="lazy" decoding="async">
                        <?php else : ?>
                            <div class="structure-card__icon">
                                <i class="fas <?php echo esc_attr($detail['icon']); ?>" aria-hidden="true"></i>
                            </div>
                        <?php endif; ?>
                        <div class="structure-card__media-meta">
                            <span class="structure-card__number"><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                            <?php if (!empty($detail['badge'])) : ?>
                                <em class="structure-card__badge"><?php echo esc_html($detail['badge']); ?></em>
                            <?php endif; ?>
                        </div>
                    </div>
                    <h3><?php echo esc_html($style); ?></h3>
                    <p class="structure-card__description"><?php echo esc_html($detail['description']); ?></p>
                    <div class="structure-card__best">
                        <span><?php esc_html_e('Best for', 'custom-box-theme'); ?></span>
                        <strong><?php echo esc_html($detail['best_for']); ?></strong>
                    </div>
                    <div class="structure-card__tags">
                        <?php foreach ($detail['tags'] as $tag) : ?>
                            <span><?php echo esc_html($tag); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <a class="structure-card__advice" href="<?php echo esc_url(home_url('/contact/')); ?>" aria-label="<?php echo esc_attr($advice_label); ?>">
                        <?php esc_html_e('Get advice for this box', 'custom-box-theme'); ?>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="structure-cta">
            <div>
                <h3><?php esc_html_e('Not sure which box structure fits your product?', 'custom-box-theme'); ?></h3>
                <p><?php esc_html_e('Send us your product size, target quantity, material preference, and branding requirements. Our factory team will recommend a suitable paper box structure for your project.', 'custom-box-theme'); ?></p>
            </div>
            <div class="structure-cta__actions">
                <a class="structure-cta__button structure-cta__button-primary" href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Send Product Details', 'custom-box-theme'); ?></a>
                <a class="structure-cta__button structure-cta__button-secondary" href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Talk to Our Packaging Team', 'custom-box-theme'); ?></a>
            </div>
        </div>
    </div>
</section>
