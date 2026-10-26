<?php
/** Existing VPN workshop material; general factory context, not a bag-line claim. */
defined('ABSPATH') || exit;
$factory_assets = get_template_directory_uri() . '/assets/images/supplier-factory-20261015/';
$workshop_quote_url = !empty($args['quote_url']) ? $args['quote_url'] : home_url('/custom-paper-bags-manufacturer/#quote');
$workshop_photos = array(
    array('file' => 'vpn-factory-offset-printing-20261009.webp', 'caption' => 'Offset printing equipment', 'alt' => 'Offset printing machine in the VPN paper packaging workshop'),
    array('file' => 'vpn-factory-paper-cutting-20261009.webp', 'caption' => 'Paper cutting equipment', 'alt' => 'Paper cutting machine and stacked sheets in the VPN packaging workshop'),
    array('file' => 'vpn-factory-carton-storage-20261009.webp', 'caption' => 'Carton storage area', 'alt' => 'Cartons stacked on pallets in the VPN packaging storage area'),
);
?>
<section class="vpn-kraft-workshop" id="kraft-workshop" aria-labelledby="kraft-workshop-title">
    <div class="container">
        <div class="vpn-kraft-workshop-heading">
            <p class="product-eyebrow">VPN Packaging · Vietnam</p>
            <h2 id="kraft-workshop-title">Inside our packaging workshop</h2>
            <p>Take a closer look at our workshop, from printing and paper cutting equipment to carton storage. These photos and the factory tour show our wider paper packaging operation.</p>
        </div>
        <div class="vpn-kraft-workshop-photos">
            <?php foreach ($workshop_photos as $photo) : ?>
                <figure>
                    <a href="<?php echo esc_url($factory_assets . $photo['file']); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr('View full image: ' . $photo['caption']); ?>">
                        <img src="<?php echo esc_url($factory_assets . $photo['file']); ?>" alt="<?php echo esc_attr($photo['alt']); ?>" width="1280" height="960" loading="lazy" decoding="async">
                    </a>
                    <figcaption><?php echo esc_html($photo['caption']); ?></figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
        <div class="vpn-kraft-workshop-tour">
            <div>
                <div class="factory-video-frame">
                    <button class="factory-video-launch" type="button" data-youtube-video-id="nD0iRaJHgLQ" aria-label="Play Inside Our Manufacturing Factory by Vietnam Paper Box Factory">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/anh-nha-may-2-16x9-100kb.webp'); ?>" alt="" width="1280" height="720" loading="lazy" decoding="async">
                        <span class="factory-video-play" aria-hidden="true"></span>
                        <span class="factory-video-launch-label">Watch the factory tour</span>
                    </button>
                </div>
                <p class="vpn-kraft-workshop-video-caption">Factory tour · Vietnam Paper Box Factory · <a href="https://www.youtube.com/watch?v=nD0iRaJHgLQ" target="_blank" rel="noopener">Watch on YouTube</a></p>
            </div>
            <div class="vpn-kraft-workshop-order">
                <h3>Review your bag before production</h3>
                <p><?php echo esc_html($args['order_intro'] ?? 'For a kraft bag order, we work from your product, artwork and agreed specification. Use the sample review to check:'); ?></p>
                <ul>
                    <li>Paper, printed colors and artwork placement</li>
                    <li>Size, opening and fit with the packed product</li>
                    <li>Base, seams and handles where specified</li>
                    <li>Carton packing or shrink-wrapped bundles</li>
                </ul>
                <p>Quality checks take place at each production stage. Tell us about any food-contact or carrying requirements when you send the brief.</p>
                <a class="btn-primary" href="<?php echo esc_url($workshop_quote_url); ?>"><?php echo esc_html($args['order_cta'] ?? 'Discuss Your Kraft Bag Order'); ?></a>
                <a class="vpn-kraft-workshop-about" href="<?php echo esc_url(home_url('/about/')); ?>">More about VPN Packaging <span aria-hidden="true">→</span></a>
            </div>
        </div>
    </div>
</section>
