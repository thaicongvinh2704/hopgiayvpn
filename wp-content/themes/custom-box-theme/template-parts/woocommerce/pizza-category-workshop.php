<?php
/** Existing VPN workshop media; no claim of pizza-specific production evidence. */
defined('ABSPATH') || exit;
$pizza_assets = get_template_directory_uri() . '/assets/images/pizza-box-landing/';
$pizza_quote = home_url('/custom-pizza-boxes-manufacturer/#vpb-quote');
$pizza_business = function_exists('custom_box_get_business_schema') ? custom_box_get_business_schema() : array();
$pizza_address = $pizza_business['address'] ?? array();
?>
<section class="vpc-workshop" id="pizza-workshop" aria-labelledby="vpc-workshop-title"><div class="container">
    <div class="vpc-section-heading"><p class="vpc-eyebrow">The company behind your order</p><h2 id="vpc-workshop-title">See inside the VPN workshop.</h2><p>Meet our team and see the printing and box-finishing areas in our paper-packaging operation in Vietnam.</p></div>
    <div class="vpc-workshop-grid">
        <div class="vpc-workshop-media">
            <div class="factory-video-frame vpc-video"><button class="factory-video-launch" type="button" data-youtube-video-id="nD0iRaJHgLQ" aria-label="Watch the VPN packaging factory tour"><img src="<?php echo esc_url($pizza_assets . 'vpn-paper-packaging-workshop-800.webp'); ?>" srcset="<?php echo esc_url($pizza_assets . 'vpn-paper-packaging-workshop-480.webp'); ?> 480w, <?php echo esc_url($pizza_assets . 'vpn-paper-packaging-workshop-800.webp'); ?> 800w" sizes="(min-width: 1000px) 720px, calc(100vw - 36px)" width="800" height="450" alt="VPN paper-packaging workshop with staff and equipment" loading="lazy" decoding="async"><span class="vpc-play" aria-hidden="true">▶</span><span class="vpc-video-label">Watch the factory tour</span></button></div>
            <div class="vpc-photo-grid">
                <figure><img src="<?php echo esc_url($pizza_assets . 'vpn-printing-operator-480.webp'); ?>" width="480" height="270" alt="VPN operator working at the paper-packaging printing press" loading="lazy" decoding="async"><figcaption><strong>Printing</strong>Our team at the printing press.</figcaption></figure>
                <figure><img src="<?php echo esc_url($pizza_assets . 'vpn-box-assembly-floor-480.webp'); ?>" width="480" height="270" alt="VPN staff at paper-box assembly and finishing workstations" loading="lazy" decoding="async"><figcaption><strong>Box finishing</strong>Assembly and finishing workstations.</figcaption></figure>
            </div>
            <p class="vpc-media-note">These images show VPN’s wider paper-packaging workshop. Pizza box designs in this collection are references; confirm the construction and materials on your approved sample. <a href="https://www.youtube.com/watch?v=nD0iRaJHgLQ" target="_blank" rel="noopener">Watch the tour on YouTube ↗</a></p>
        </div>
        <aside class="vpc-company" aria-labelledby="vpc-company-title"><p class="vpc-eyebrow">Your manufacturing contact</p><h3 id="vpc-company-title">VPN Paper Box</h3>
            <?php if (!empty($pizza_business['legalName'])) : ?><p class="vpc-legal"><?php echo esc_html($pizza_business['legalName']); ?></p><?php endif; ?>
            <address><?php if (!empty($pizza_address['streetAddress'])) : ?><span><?php echo esc_html($pizza_address['streetAddress']); ?></span><?php endif; ?><span>Ho Chi Minh City, Vietnam</span></address>
            <p>Discuss your pizza size, print and order volume directly with our packaging team.</p>
            <div class="vpc-company-contacts"><a href="mailto:paperbox@hopgiayvpn.com">paperbox@hopgiayvpn.com</a><a href="mailto:sales.vpn@hopgiayvpn.com">sales.vpn@hopgiayvpn.com</a><a href="tel:+84933102653">+84 933 102 653</a></div>
            <a class="vpc-button" href="<?php echo esc_url($pizza_quote); ?>">Discuss my pizza box order →</a>
            <a class="vpc-secondary" href="https://wa.me/84933102653" target="_blank" rel="noopener">Talk on WhatsApp ↗</a>
            <div class="vpc-company-links"><a href="<?php echo esc_url(home_url('/about/')); ?>">About VPN →</a><?php if (!empty($pizza_business['hasMap'])) : ?><a href="<?php echo esc_url($pizza_business['hasMap']); ?>" target="_blank" rel="noopener">View location ↗</a><?php endif; ?></div>
        </aside>
    </div>
</div></section>
<section class="vpc-ordering" id="pizza-ordering" aria-labelledby="vpc-ordering-title"><div class="container">
    <div class="vpc-section-heading"><p class="vpc-eyebrow">From enquiry to production</p><h2 id="vpc-ordering-title">A clear route to your custom pizza box.</h2></div>
    <ol class="vpc-order-steps"><li><span>01</span><h3>Share your brief</h3><p>Send your pizza or inside-box size, quantity per design and delivery country. General MOQ: 1,000 boxes, subject to the agreed specification.</p></li><li><span>02</span><h3>Review the sample</h3><p>Confirm the board, artwork, folding and filled-box fit. Custom sampling usually takes 7–15 days, depending on the order.</p></li><li><span>03</span><h3>Approve bulk production</h3><p>Production typically takes 7–45 days after sample approval, depending on the order. Confirm packing, freight and the schedule in your quote.</p></li></ol>
    <div class="vpc-order-note"><p>For repeat orders or several sizes, share the quantity for each size and artwork so the quotation covers the full program.</p><a class="vpc-button" href="<?php echo esc_url($pizza_quote); ?>">Request a project quote →</a></div>
</div></section>
