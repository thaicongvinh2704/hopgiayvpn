<section class="hero" aria-labelledby="home-hero-title" data-home-hero data-first-screen-content>

    <div class="container hero-wrapper">

        <!-- LEFT CONTENT -->
        <div class="hero-content" data-home-hero-copy>

            <!-- Eyebrow -->
            <span class="hero-eyebrow">
                In-house Production
            </span>

            <!-- Headline -->
            <h1 id="home-hero-title" data-home-hero-title>
                VPN Paper Box Manufacturer <br>
                <span class="highlight">Custom Paper Boxes Factory</span>
            </h1>

            <!-- Subtext -->
            <p data-home-hero-value-proposition>
                VPN Paper Box Manufacturer is a Vietnam-based custom paper box manufacturer specializing in rigid boxes, gift boxes, cosmetic packaging, and factory-direct packaging solutions for global brands and wholesalers.
            </p>

            <!-- Features -->
            <ul class="hero-features" data-home-proof-list>
                <li>Free Design Support</li>
                <li>Fast, Reliable Shipping</li>
                <li>Production Capacity: 10,000 - 3,000,000 Boxes / Month</li>
                <li>Built for Global B2B Paper Box Projects</li>
            </ul>

            <!-- CTA -->
            <div class="hero-buttons" data-home-hero-actions>
                <a href="<?php echo esc_url(home_url('/contact/#quote')); ?>" class="btn-primary" data-primary-quote-action>Get Instant Quote</a>
                <a href="<?php echo esc_url(home_url('/contact/#quote')); ?>" class="btn-outline" data-secondary-sample-action>Request Free Sample</a>
            </div>

        </div>

        <!-- RIGHT IMAGE -->
        <div class="hero-image" data-home-hero-media>
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/product-banner1.webp'); ?>" srcset="<?php echo esc_url(get_template_directory_uri() . '/assets/images/product-banner1-mobile.webp'); ?> 480w, <?php echo esc_url(get_template_directory_uri() . '/assets/images/product-banner1.webp'); ?> 666w" alt="Custom luxury product packaging box" width="666" height="374" decoding="async" fetchpriority="high" sizes="(max-width: 767px) calc(100vw - 36px), 50vw">
        </div>

    </div>

</section>

<!-- BRAND SLIDER -->
<section class="brand-section home-client-brands" aria-labelledby="home-client-brands-title" data-home-client-brands>
    <h2 id="home-client-brands-title" class="screen-reader-text">Client brands</h2>
    <div
        class="brand-slider"
        role="region"
        aria-label="Client brand logos"
        tabindex="0"
        data-manual-logo-scroller
    >
        <div class="brand-track" role="list">

            <?php
            $brand_logos = array(
                array('file' => 'dcons-logo.webp', 'name' => 'DCONS', 'width' => 225, 'height' => 225),
                array('file' => 'hibiscus-gift-logo.webp', 'name' => 'Hibiscus Gift', 'width' => 500, 'height' => 500),
                array('file' => 'iplus-logo.webp', 'name' => 'iPlus', 'width' => 225, 'height' => 225),
                array('file' => 'lcsgroup-logo.webp', 'name' => 'LCS Group', 'width' => 705, 'height' => 354),
                array('file' => 'lovedears.webp', 'name' => 'Love Dears', 'width' => 225, 'height' => 225),
                array('file' => 'mkt-logo.webp', 'name' => 'MKT', 'width' => 500, 'height' => 500),
                array('file' => 'saokim-logo.webp', 'name' => 'Sao Kim', 'width' => 666, 'height' => 375),
                array('file' => 'sharon-logo-.webp', 'name' => 'Sharon', 'width' => 500, 'height' => 500),
                array('file' => 'tien-giang-logo.webp', 'name' => 'Tien Giang', 'width' => 500, 'height' => 500),
                array('file' => 'trungtamdungcu-logo.webp', 'name' => 'Trung Tam Dung Cu', 'width' => 500, 'height' => 500),
            );
            ?>

            <?php foreach ($brand_logos as $logo) : ?>
                <?php $logo_class = sanitize_html_class(pathinfo($logo['file'], PATHINFO_FILENAME)); ?>
                <span class="brand-logo-item" role="listitem">
                    <img
                        class="client-logo client-logo-<?php echo esc_attr($logo_class); ?>"
                        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/client-logos/' . $logo['file']); ?>"
                        alt="<?php echo esc_attr($logo['name']); ?>"
                        width="<?php echo esc_attr($logo['width']); ?>"
                        height="<?php echo esc_attr($logo['height']); ?>"
                        loading="lazy"
                        decoding="async"
                    >
                </span>
            <?php endforeach; ?>

        </div>
    </div>
</section>
