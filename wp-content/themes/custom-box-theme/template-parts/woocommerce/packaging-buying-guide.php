<?php
/** Comparison of existing products on selected category landing pages. */
defined('ABSPATH') || exit;

$profile = $args['buying_profile'];
?>
<section id="<?php echo esc_attr($profile['anchor']); ?>" class="product-category-copy-section">
    <div class="container">
        <div class="product-category-copy-card">
            <h2><?php echo esc_html($profile['comparison']); ?></h2>
            <div class="product-related-grid" style="grid-template-columns: repeat(auto-fit, minmax(min(100%, 230px), 1fr));">
                <?php foreach ($profile['formats'] as $slug => [$label, $description]) : ?>
                    <?php $example = get_page_by_path($slug, OBJECT, 'product'); ?>
                    <div>
                        <?php if ($example && $example->post_status === 'publish') : ?>
                            <a class="product-category-card" href="<?php echo esc_url(get_permalink($example)); ?>">
                                <?php if (has_post_thumbnail($example)) : ?>
                                    <span class="product-category-card-image">
                                        <?php echo get_the_post_thumbnail($example, 'woocommerce_thumbnail', ['loading' => 'lazy', 'decoding' => 'async', 'alt' => '']); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="product-category-card-title"><?php echo esc_html($label); ?></span>
                            </a>
                        <?php else : ?>
                            <h3><?php echo esc_html($label); ?></h3>
                        <?php endif; ?>
                        <p><?php echo esc_html($description); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <h3><?php echo esc_html($profile['sample_heading']); ?></h3>
            <p><?php echo esc_html($profile['sample']); ?></p>
            <h3>What to send for a bulk quote</h3>
            <p><?php echo esc_html($profile['quote']); ?></p>
            <p><a href="<?php echo esc_url(home_url('/contact/#quote')); ?>">Request a custom packaging quote</a> or <a href="<?php echo esc_url(home_url('/about/')); ?>">see VPN's packaging factory in Vietnam</a>.</p>
        </div>
    </div>
</section>
