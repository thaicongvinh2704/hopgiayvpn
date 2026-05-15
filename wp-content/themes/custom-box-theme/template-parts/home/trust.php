<section class="trust-section">

    <!-- MAIN CONTENT -->
    <div class="container trust-content">

        <!-- LEFT -->
        <div class="trust-left">
            <h2>Why Businesses Choose<br>VPN Paper Box Manufacturer</h2>
            <p>
                Factory-direct paper box production that reduces costs while ensuring speed,
                consistency, and full control over packaging quality.
            </p>
        </div>

        <!-- RIGHT -->
        <div class="trust-right">

            <!-- TRUST AUTHORITY -->
            <p class="trust-highlight">
                <strong>VPN Paper Box Manufacturer - one of Vietnam's leading custom paper box manufacturers for international markets</strong>
            </p>

            <p>
                We provide high-quality custom paper box and packaging solutions for clients in the US, UK, and India.
                With fully in-house production, we ensure factory-direct pricing, strict quality control,
                and reliable turnaround times.
            </p>

            <p>
                Producing directly at our factory helps you cut costs, shorten production time,
                and maintain 100% quality control at every stage. With no middlemen involved,
                you get better pricing while ensuring your packaging always reflects a
                professional, high-end brand image.
            </p>

        </div>

    </div>

    <!-- FEATURES -->
    <?php
    $trust_features = array(
        array(
            'title'       => 'Factory Price &ndash; Competitive',
            'description' => 'Direct production without intermediaries. Save 20&ndash;40% compared to ordering through agencies.',
            'icon'        => 'fas fa-industry',
        ),
        array(
            'title'       => 'Custom Design',
            'description' => 'Free design files based on your brief. A creative team with extensive experience in the packaging industry.',
            'icon'        => 'fas fa-palette',
        ),
        array(
            'title'       => 'Modern Printing Technology',
            'description' => 'Advanced offset printing, foil stamping, UV coating, and embossing. Accurate colors and sharp details.',
            'icon'        => 'fas fa-print',
        ),
        array(
            'title'       => 'Fast Production &ndash; On Time',
            'description' => 'Production lead time of 3&ndash;7 working days. Committed to on-schedule delivery with no hidden costs.',
            'icon'        => 'fas fa-clock',
        ),
        array(
            'title'       => 'All-in-One Support (A&ndash;Z)',
            'description' => 'Consulting &ndash; Design &ndash; Production &ndash; Delivery. 24/7 dedicated support to answer all your inquiries.',
            'icon'        => 'fas fa-headset',
        ),
    );
    ?>

    <div class="container trust-features">
        <?php foreach ($trust_features as $feature) : ?>
            <article class="trust-card">
                <i class="<?php echo esc_attr($feature['icon']); ?>" aria-hidden="true"></i>
                <h3><?php echo wp_kses_post($feature['title']); ?></h3>
                <p><?php echo wp_kses_post($feature['description']); ?></p>
            </article>
        <?php endforeach; ?>
    </div>

    <!-- CTA -->
    <div class="trust-cta">
        <a href="<?php echo esc_url(home_url('/contact/#quote')); ?>" class="btn-primary">Get Your Instant Quote</a>
    </div>

</section>
