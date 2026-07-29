<?php
$factory_team_image = custom_box_get_local_image_data('factory-team showcase.webp');
$factory_certificate_image = custom_box_get_local_image_data('vpn-packaging-iso-9001-certification-banner.webp');
?>

<section class="factory-showcase-section" aria-labelledby="factory-showcase-title">
    <div class="container factory-video-wrapper">
        <h2 id="factory-showcase-title" class="screen-reader-text">VPN Paper Box Manufacturer production video</h2>

        <div class="factory-video-layout">
            <div class="factory-capacity-panel">
                <span>Production Capacity</span>
                <p>At VPN Paper Box Manufacturer, we take pride in our strong and scalable manufacturing capabilities.</p>

                <div class="factory-capacity-stat">
                    <small>Folding Carton Boxes</small>
                    <strong>3,000,000</strong>
                    <em>Units/Month</em>
                </div>

                <div class="factory-capacity-stat">
                    <small>Rigid Box Production</small>
                    <strong>1,000,000</strong>
                    <em>Units/Month</em>
                </div>
            </div>

            <div class="factory-video-frame">
                <button
                    class="factory-video-launch"
                    type="button"
                    data-youtube-video-id="nD0iRaJHgLQ"
                    aria-label="Watch factory video: VPN Paper Box Manufacturer production capacity video"
                >
                    <img
                        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/anh-nha-may-2-16x9-100kb.webp'); ?>"
                        alt=""
                        width="1280"
                        height="720"
                        loading="lazy"
                        decoding="async"
                    >
                    <span class="factory-video-play" aria-hidden="true"></span>
                    <span class="factory-video-launch-label">Watch factory video</span>
                </button>
                <noscript>
                    <a class="factory-video-fallback" href="https://www.youtube.com/watch?v=nD0iRaJHgLQ" target="_blank" rel="noopener">Watch factory video on YouTube</a>
                </noscript>
            </div>
        </div>
    </div>

    <div class="container factory-showcase-wrapper">
        <h2 class="screen-reader-text">Factory Team Showcase</h2>

        <div class="factory-showcase-media">
            <img
                src="<?php echo esc_url($factory_team_image['url']); ?>"
                alt="VPN Paper Box Manufacturer team and production showcase"
                width="<?php echo esc_attr($factory_team_image['width']); ?>"
                height="<?php echo esc_attr($factory_team_image['height']); ?>"
                loading="lazy"
                decoding="async"
            >
        </div>

        <div class="factory-certification-media">
            <img
                src="<?php echo esc_url($factory_certificate_image['url']); ?>"
                alt="<?php esc_attr_e('VPN Paper Box Manufacturer ISO 9001 certification banner', 'custom-box-theme'); ?>"
                width="<?php echo esc_attr($factory_certificate_image['width']); ?>"
                height="<?php echo esc_attr($factory_certificate_image['height']); ?>"
                loading="lazy"
                decoding="async"
            >
        </div>
    </div>
</section>
