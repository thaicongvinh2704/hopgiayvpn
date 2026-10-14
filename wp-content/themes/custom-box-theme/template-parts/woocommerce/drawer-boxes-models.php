<?php
defined('ABSPATH') || exit;
$style_copy = array(
    array('Skincare & cosmetics', 'Skincare gift sets', 'A fitted insert for bottles, jars and coordinated sets.'),
    array('Fashion & accessories', 'Ribbon-pull apparel boxes', 'A simple pull detail with a tray tailored to your items.'),
    array('Watches & accessories', 'Watch presentation boxes', 'A watch compartment with an additional accessory drawer.'),
    array('Bakery & food gifts', 'Kraft pastry boxes', 'A sleeve and divided tray for individually wrapped treats.'),
    array('Gifting & collections', 'Multi-drawer gift boxes', 'Separate compartments for a coordinated gift collection.'),
);
?>
<section class="vpn-drawer-page vpn-drawer-models" id="drawer-designs" aria-labelledby="drawer-models-title">
    <div class="container">
        <div class="vpn-drawer-section-heading vpn-drawer-model-heading">
            <div><p class="product-eyebrow">Find your starting point</p>
            <h2 id="drawer-models-title">A style for every product.</h2>
            <p>Five ideas to explore. Every size, insert and finish can be made to your brief.</p></div>
            <a class="vpn-drawer-text-link" href="#drawer-all-products">See all designs <span aria-hidden="true">↗</span></a>
        </div>
        <div class="vpn-drawer-model-grid">
            <?php foreach (vpn_drawer_data()['models'] as $index => $model) :
                $post = get_page_by_path($model['slug'], OBJECT, 'product');
                if (!$post || 'publish' !== $post->post_status) { continue; }
                $profile = vpn_drawer_product_profile($post->ID);
                [$use, $label, $hint] = $style_copy[$index];
            ?>
            <article class="vpn-drawer-model">
                <a class="vpn-drawer-model-image" href="<?php echo esc_url(get_permalink($post)); ?>"><?php echo vpn_drawer_card_image($post->ID); ?></a>
                <div class="vpn-drawer-model-copy">
                    <p class="vpn-drawer-use"><?php echo esc_html($use); ?></p>
                    <h3><a href="<?php echo esc_url(get_permalink($post)); ?>"><?php echo esc_html($label); ?></a></h3>
                    <p class="vpn-drawer-model-hint"><?php echo esc_html($hint); ?></p>
                    <details class="vpn-drawer-model-specs">
                        <summary>Size &amp; structure <span aria-hidden="true">+</span></summary>
                        <div><p><?php echo esc_html($model['construction']); ?></p>
                        <p class="vpn-drawer-size">Reference internal size: <strong><?php echo esc_html($model['size']); ?></strong></p>
                        <p>Suggested configuration; fully customizable.</p></div>
                    </details>
                    <div class="vpn-drawer-model-actions">
                        <a class="vpn-drawer-model-view" href="<?php echo esc_url(get_permalink($post)); ?>">View design <span aria-hidden="true">↗</span></a>
                        <a class="vpn-drawer-model-quote" href="<?php echo esc_url(vpn_drawer_quote_url($model['slug'])); ?>">Get a quote <span aria-hidden="true">→</span><span class="screen-reader-text"> for <?php echo esc_html($label); ?></span></a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
            <aside class="vpn-drawer-custom-card" aria-labelledby="drawer-custom-title">
                <span class="vpn-drawer-custom-mark" aria-hidden="true">↗</span>
                <p class="product-eyebrow">Your design comes first</p>
                <h3 id="drawer-custom-title">Have something<br>else in mind?</h3>
                <p>We can develop a different box around your product. Start with an idea, a sketch or a reference.</p>
                <ul><li>Your product dimensions</li><li>Quantity &amp; destination</li><li>Your email for the quotation</li></ul>
                <a href="<?php echo esc_url(vpn_drawer_quote_url()); ?>">Tell us about your project <span aria-hidden="true">→</span></a>
            </aside>
        </div>
        <p class="vpn-drawer-note">Sizes above are suggested internal L × W × H configurations, not measurements taken from the images. Confirm the product fit, insert and finished dimensions during sampling. Images illustrate packaging styles.</p>
    </div>
</section>
