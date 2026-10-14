<?php
defined('ABSPATH') || exit;
$drawer = vpn_drawer_data();
$hero_post = get_page_by_path($drawer['models'][0]['slug'], OBJECT, 'product');
$hero_profile = $hero_post ? vpn_drawer_product_profile((int) $hero_post->ID) : array();
$hero_image = !empty($hero_profile['images']) ? end($hero_profile['images']) : array('file' => 'assets/images/drawer-20261014/cosmetic-720.webp', 'width' => 720, 'height' => 720);
$first_page = 1 === max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
?>
<section class="product-archive-hero vpn-drawer-hero">
    <div class="container">
        <nav class="product-archive-breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
            <a href="<?php echo esc_url(home_url('/products/')); ?>">Packaging</a>
            <span aria-current="page">Drawer boxes</span>
        </nav>
        <div class="vpn-drawer-hero-grid">
            <div class="vpn-drawer-hero-copy">
                <p class="product-eyebrow">Made to order · Made in Vietnam</p>
                <h1><?php echo esc_html($drawer['heading']); ?></h1>
                <p class="vpn-drawer-hero-intro">A sliding reveal, made for your brand. Customize the sleeve, tray, insert and finish around your product.</p>
                <div class="vpn-drawer-hero-actions">
                    <a class="btn-primary" href="<?php echo esc_url(vpn_drawer_quote_url()); ?>">Get a custom quote <span aria-hidden="true">↗</span></a>
                    <a class="vpn-drawer-hero-browse" href="<?php echo $first_page ? '#drawer-designs' : '#drawer-all-products'; ?>">Explore the designs <span aria-hidden="true">↓</span></a>
                </div>
                <p class="vpn-drawer-hero-help">Share your email, quantity and product dimensions to get started.</p>
                <dl class="vpn-drawer-hero-facts">
                    <div><dt>Minimum order</dt><dd>1,000 units</dd></div>
                    <div><dt>Size &amp; design</dt><dd>Fully custom</dd></div>
                    <div><dt>Custom samples</dt><dd>7–15 days*</dd></div>
                </dl>
                <p class="vpn-drawer-hero-footnote">*Depending on your design and order.</p>
            </div>
            <figure class="vpn-drawer-hero-visual">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/' . $hero_image['file']); ?>" width="<?php echo esc_attr($hero_image['width']); ?>" height="<?php echo esc_attr($hero_image['height']); ?>" alt="Custom cosmetic drawer packaging with a sliding tray and fitted insert" loading="eager" fetchpriority="high" decoding="async">
                <figcaption><span>Designed around your product</span><span>Sleeve + tray + insert</span></figcaption>
            </figure>
        </div>
    </div>
</section>
<nav class="vpn-drawer-jump" aria-label="On this page">
    <div class="container">
        <?php if ($first_page) : ?><a href="#drawer-designs">Featured styles</a><?php endif; ?>
        <a href="#drawer-all-products">All designs</a>
        <?php if ($first_page) : ?>
            <a href="#drawer-guide">Customization</a><a href="#drawer-policy">Samples &amp; production</a><a href="#drawer-questions">FAQ</a>
        <?php endif; ?>
        <a class="vpn-drawer-jump-quote" href="<?php echo esc_url(vpn_drawer_quote_url()); ?>">Request a quote <span aria-hidden="true">↗</span></a>
    </div>
</nav>
