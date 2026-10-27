<?php
/** Existing workshop photographs, shown as general paper-packaging evidence. */
defined('ABSPATH') || exit;
$workshop_assets = get_template_directory_uri() . '/assets/images/supplier-factory-20261015/';
$workshop_photos = array(
    array('file' => 'vpn-factory-offset-printing-20261009.webp', 'caption' => 'Offset printing equipment', 'alt' => 'Offset printing machine and paper sheets in the VPN packaging workshop'),
    array('file' => 'vpn-factory-paper-cutting-20261009.webp', 'caption' => 'Paper cutting equipment', 'alt' => 'Paper cutting machine with stacks of material in the VPN packaging workshop'),
    array('file' => 'vpn-factory-carton-storage-20261009.webp', 'caption' => 'Carton storage area', 'alt' => 'Cartons stacked on pallets in the VPN packaging storage area'),
);
?>
<section class="cpb-workshop" id="workshop" aria-labelledby="cpb-workshop-title">
    <div class="cpb-shell">
        <div class="cpb-workshop-heading">
            <div><p class="cpb-eyebrow">Inside VPN · Vietnam</p><h2 id="cpb-workshop-title">A closer look at our workshop.</h2></div>
            <p>See our printing equipment, paper cutting and carton storage. These photographs show our wider paper packaging operation.</p>
        </div>
        <div class="cpb-workshop-photos">
            <?php foreach ($workshop_photos as $photo) : ?>
            <figure>
                <a href="<?php echo esc_url($workshop_assets . $photo['file']); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr('View full photograph: ' . $photo['caption']); ?>">
                    <img src="<?php echo esc_url($workshop_assets . $photo['file']); ?>" alt="<?php echo esc_attr($photo['alt']); ?>" width="1280" height="960" loading="lazy" decoding="async">
                </a>
                <figcaption><?php echo esc_html($photo['caption']); ?></figcaption>
            </figure>
            <?php endforeach; ?>
        </div>
        <div class="cpb-workshop-links"><a class="cpb-text-link" href="#quality">Watch the factory tour <span aria-hidden="true">↓</span></a><a class="cpb-text-link" href="<?php echo esc_url(home_url('/about/')); ?>">Meet VPN Paper Box <span aria-hidden="true">→</span></a></div>
    </div>
</section>
