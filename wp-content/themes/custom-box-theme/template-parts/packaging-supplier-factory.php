<?php
defined('ABSPATH') || exit;
$context = $args['context'] ?? array();
$images = $args['images'] ?? array();
if (!$context || !$images) {
    return;
}
?>
<section id="manufacturing-workshop" class="vpn-factory-gallery<?php echo 2 === count($images) ? ' vpn-factory-gallery--two' : ''; ?>" aria-labelledby="manufacturing-workshop-title">
    <div class="vpn-factory-gallery__inner">
        <div class="vpn-factory-gallery__heading">
            <span class="vpn-factory-gallery__eyebrow">VPN Packaging Workshop</span>
            <h2 id="manufacturing-workshop-title"><?php echo esc_html($context['title']); ?></h2>
            <p><?php echo esc_html($context['description']); ?></p>
        </div>
        <div class="vpn-factory-gallery__grid">
            <?php foreach ($images as $image) : ?>
                <figure class="vpn-factory-gallery__card">
                    <a href="<?php echo esc_url($image['url']); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr('View image: ' . $image['caption']); ?>">
                        <?php echo $image['html']; // WordPress image markup or escaped local-asset markup. ?>
                    </a>
                    <figcaption><?php echo esc_html($image['caption']); ?></figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
        <p class="vpn-factory-gallery__cta"><a href="<?php echo esc_url(home_url('/custom-packaging-boxes-manufacturer/#factory-quote')); ?>">Discuss Your Packaging Project <span aria-hidden="true">&rarr;</span></a></p>
    </div>
</section>
