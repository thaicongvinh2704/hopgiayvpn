<section class="hero">

    <div class="container hero-wrapper">

        <!-- LEFT CONTENT -->
        <div class="hero-content">

            <!-- Eyebrow -->
            <span class="hero-eyebrow">
                In-house Production
            </span>

            <!-- Headline -->
            <h1>
                VPN Paper Box Manufacturer <br>
                <span class="highlight">Custom Paper Boxes Factory</span>
            </h1>

            <!-- Subtext -->
            <p>
                VPN Packaging Factory is a Vietnam-based custom paper box manufacturer specializing in rigid boxes, gift boxes, cosmetic packaging, and factory-direct packaging solutions for global brands and wholesalers.
            </p>

            <!-- Features -->
            <ul class="hero-features">
                <li>Free Design Support</li>
                <li>Fast, Reliable Shipping</li>
                <li>Low Minimum Orders</li>
            </ul>

            <!-- CTA -->
            <div class="hero-buttons">
                <a href="#" class="btn-primary">Get Instant Quote</a>
                <a href="#" class="btn-outline">Request Free Sample</a>
            </div>

        </div>

        <!-- RIGHT IMAGE -->
        <div class="hero-image">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/product-banner1.png'); ?>" alt="Custom luxury product packaging box" width="666" height="374" decoding="async" fetchpriority="high">
        </div>

    </div>

</section>

<!-- BRAND SLIDER -->
<section class="brand-section">
    <div class="brand-slider">
        <div class="brand-track">

            <?php
            $brand_logos = array(
                'dcons-logo.png',
                'hibiscus-gift-logo.png',
                'iplus-logo.png',
                'lcsgroup-logo.png',
                'lovedears.png',
                'mkt-logo.png',
                'saokim-logo.png',
                'sharon-logo-.png',
                'tien-giang-logo.png',
                'trungtamdungcu-logo.png',
            );
            ?>

            <?php for ($loop = 0; $loop < 2; $loop++) : ?>
                <?php foreach ($brand_logos as $logo) : ?>
                    <?php $logo_class = sanitize_html_class(pathinfo($logo, PATHINFO_FILENAME)); ?>
                    <img
                        class="client-logo client-logo-<?php echo esc_attr($logo_class); ?>"
                        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/client-logos/' . $logo); ?>"
                        alt="Brand Logo"
                        loading="lazy"
                        decoding="async"
                    >
                <?php endforeach; ?>
            <?php endfor; ?>

        </div>
    </div>
</section>
