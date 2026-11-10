<?php
/** Pizza category introduction; the manufacturer landing keeps its own layout. */
defined('ABSPATH') || exit;
$pizza_assets = get_template_directory_uri() . '/assets/images/pizza-box-landing/';
$pizza_quote = home_url('/custom-pizza-boxes-manufacturer/#vpb-quote');
?>
<section class="vpc-hero" aria-labelledby="vpc-title">
    <div class="container">
        <nav class="vpc-breadcrumb" aria-label="Breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span aria-hidden="true">/</span><a href="<?php echo esc_url(home_url('/products/')); ?>">Products</a><span aria-hidden="true">/</span><span>Pizza Boxes</span></nav>
        <div class="vpc-hero-grid">
            <div class="vpc-hero-copy">
                <p class="vpc-eyebrow">Made in Vietnam · Custom printed packaging</p>
                <h1 id="vpc-title">Custom Pizza Boxes <span>for Your Brand.</span></h1>
                <p class="vpc-lead">For pizzerias, restaurant groups and packaging distributors. Compare pizza box formats, then choose the size, board and logo printing for your bulk order.</p>
                <dl class="vpc-quick-facts"><div><dt>General MOQ</dt><dd>1,000 boxes</dd></div><div><dt>Size &amp; artwork</dt><dd>Made to order</dd></div><div><dt>Before production</dt><dd>Sample approval</dd></div></dl>
                <div class="vpc-actions"><a class="vpc-button" href="<?php echo esc_url($pizza_quote); ?>">Get my pizza box quote <span aria-hidden="true">→</span></a><a class="vpc-secondary" href="#pizza-formats">Explore the formats <span aria-hidden="true">↓</span></a></div>
                <p class="vpc-small">Free quotation · No obligation. Confirm MOQ for each size and artwork.</p>
            </div>
            <div class="vpc-hero-media">
                <div class="vpc-gallery" data-pizza-gallery role="region" aria-roledescription="carousel" aria-label="Kraft pizza box views">
                    <div class="vpc-gallery-track" data-pizza-track tabindex="0" aria-label="Swipe, drag or use arrow keys to view the pizza box">
                        <?php foreach (array('closed' => 'Closed kraft pizza box with red printed lid', 'open' => 'The same kraft pizza box open, showing the interior', 'angle' => 'The same printed kraft pizza box from a lower angle') as $view => $alt) : $first_view = 'closed' === $view; ?>
                        <div class="vpc-gallery-slide" data-pizza-slide aria-hidden="<?php echo $first_view ? 'false' : 'true'; ?>">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pizza-category-20261110/kraft-pizza-' . $view . '-960.webp'); ?>" srcset="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pizza-category-20261110/kraft-pizza-' . $view . '-480.webp'); ?> 480w, <?php echo esc_url(get_template_directory_uri() . '/assets/images/pizza-category-20261110/kraft-pizza-' . $view . '-960.webp'); ?> 960w" sizes="(min-width: 1000px) 560px, (min-width: 721px) 46vw, calc(100vw - 36px)" width="960" height="640" alt="<?php echo esc_attr($alt); ?>" loading="<?php echo $first_view ? 'eager' : 'lazy'; ?>"<?php echo $first_view ? ' fetchpriority="high"' : ''; ?> decoding="async" draggable="false">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="vpc-gallery-controls" role="group" aria-label="Choose a pizza box view" data-pizza-controls hidden>
                        <button type="button" data-pizza-select="0" aria-label="Show closed pizza box" aria-pressed="true"><span></span></button>
                        <button type="button" data-pizza-select="1" aria-label="Show open pizza box" aria-pressed="false"><span></span></button>
                        <button type="button" data-pizza-select="2" aria-label="Show angled pizza box" aria-pressed="false"><span></span></button>
                    </div>
                    <p class="vpc-gallery-note">One kraft pizza box, three views · Design illustration</p>
                </div>
                <a class="vpc-workshop-teaser" href="#pizza-workshop"><img src="<?php echo esc_url($pizza_assets . 'vpn-printing-operator-480.webp'); ?>" width="480" height="270" alt="VPN team member operating a printing press" loading="lazy" decoding="async"><span><strong>Meet the people behind your packaging</strong><span>See our workshop &amp; factory video →</span></span></a>
            </div>
        </div>
    </div>
</section>
<nav class="vpc-jump-nav" aria-label="Pizza box buying information"><div class="container"><a href="#pizza-formats">Box formats</a><a href="#pizza-workshop">Our workshop</a><a href="#pizza-ordering">MOQ &amp; samples</a><a href="#pizza-box-buying-guide">Specifications</a><a href="#pizza-box-faq-title">FAQs</a></div></nav>
<div class="vpc-format-heading container" id="pizza-formats"><p class="vpc-eyebrow">Choose a starting point</p><h2>Find the format for your menu.</h2><p>Compare square, personal-size and rectangular designs. Open a product for details, or send your requirements for a custom quote.</p></div>
