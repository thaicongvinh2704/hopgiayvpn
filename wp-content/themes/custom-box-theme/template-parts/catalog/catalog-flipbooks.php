	<?php
/**
 * Catalog flipbook preview.
 */

defined('ABSPATH') || exit;

$profile_url = isset($args['profile_url']) ? $args['profile_url'] : home_url('/contact/#quote');
$catalog_url = isset($args['catalog_external_url']) ? $args['catalog_external_url'] : home_url('/catalog/#catalog-preview');
$contact_url = isset($args['contact_url']) ? $args['contact_url'] : home_url('/contact/#quote');
$quote_url = isset($args['quote_url']) ? $args['quote_url'] : home_url('/contact/#quote');

$flipbooks = array(
    array(
        'title' => __('Company Profile', 'custom-box-theme'),
        'label' => __('Review our factory capability, company background, production standards, and export-ready packaging support.', 'custom-box-theme'),
        'url' => $profile_url,
        'dflip_id' => 495,
        'primary_cta' => __('Contact Sales Team', 'custom-box-theme'),
        'primary_url' => $contact_url,
    ),
    array(
        'title' => __('Product Catalog', 'custom-box-theme'),
        'label' => __('Browse custom paper box categories, structures, materials, printing finishes, and packaging options for B2B projects.', 'custom-box-theme'),
        'url' => $catalog_url,
        'dflip_id' => 584,
        'primary_cta' => __('Request a Quote', 'custom-box-theme'),
        'primary_url' => $quote_url,
    ),
);
?>

<section class="catalog-flipbook-section" id="catalog-preview">
    <div class="container">
        <div class="catalog-section-heading catalog-section-heading-center">
            <span class="catalog-kicker"><?php esc_html_e('Interactive Preview', 'custom-box-theme'); ?></span>
            <h2><?php esc_html_e('Company Profile & Product Catalog Preview', 'custom-box-theme'); ?></h2>
            <p><?php esc_html_e('Review our company profile and paper box catalog directly on this page, or open either document in a larger browser view.', 'custom-box-theme'); ?></p>
        </div>

        <div class="catalog-flipbook-stage catalog-flipbook-stack" aria-label="<?php esc_attr_e('Company profile and product catalog preview', 'custom-box-theme'); ?>">
            <?php foreach ($flipbooks as $flipbook) : ?>
                <article class="catalog-flipbook-panel">
                    <div class="catalog-flipbook-header">
                        <div>
                            <h3><?php echo esc_html($flipbook['title']); ?></h3>
                            <p><?php echo esc_html($flipbook['label']); ?></p>
                        </div>
                        <a href="<?php echo esc_url($flipbook['url']); ?>" target="_blank" rel="noopener"><?php esc_html_e('Open Full Screen', 'custom-box-theme'); ?></a>
                    </div>
                    <div class="catalog-dearflip-book">
                        <?php echo do_shortcode('[dflip id="' . absint($flipbook['dflip_id']) . '"][/dflip]'); ?>
                    </div>
                    <div class="catalog-flipbook-actions">
                        <a class="btn-primary" href="<?php echo esc_url($flipbook['primary_url']); ?>"><?php echo esc_html($flipbook['primary_cta']); ?></a>
                        <a class="btn-outline" href="<?php echo esc_url($flipbook['url']); ?>" target="_blank" rel="noopener"><?php esc_html_e('Open Larger View', 'custom-box-theme'); ?></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
