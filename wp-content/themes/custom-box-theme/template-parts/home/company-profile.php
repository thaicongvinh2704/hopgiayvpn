<?php
/**
 * Company profile brochure-style banner.
 */

$factory_fly = custom_box_get_local_image_data('anh-nha-may-fly.webp', 'profile-company.webp');
$factory_one = custom_box_get_local_image_data('anh-nha-may-1.webp', 'factory-team showcase.jpg');
$factory_two = custom_box_get_local_image_data('anh-nha-may-2.webp', 'factory-team-and-production.jpg');
$factory_three = custom_box_get_local_image_data('anh-nha-may-3.webp', 'product-banner1.png');
?>

<section class="vpn-company-profile-banner" aria-labelledby="vpn-company-profile-title">
    <div class="container vpn-company-profile-shell">
        <div class="vpn-profile-left">
            <div class="vpn-profile-left-shape" aria-hidden="true"></div>

            <div class="vpn-profile-copy">
                <p class="vpn-profile-small-title">COMPANY PROFILE</p>
                <h2 id="vpn-company-profile-title">VPN Paper Box Manufacturer</h2>
                <p>
                    VPN Paper Box Manufacturer is a professional packaging manufacturer based in Vietnam, specializing in custom paper boxes, paper bags, and fabric bags. With the advantage of being a direct factory, we provide optimized packaging solutions in terms of cost, quality, and lead time. Our team consists of experienced professionals in design, engineering, and production, ensuring every product meets high standards before reaching our customers. We offer a one-stop packaging solution from design and sampling to mass production, serving businesses, brands, and distributors both domestically and internationally.
                </p>
            </div>

            <div class="vpn-profile-plant-card">
                <img src="<?php echo esc_url($factory_fly['url']); ?>" alt="Aerial view of VPN Paper Box Manufacturer in Vietnam" width="<?php echo esc_attr($factory_fly['width']); ?>" height="<?php echo esc_attr($factory_fly['height']); ?>" loading="lazy" decoding="async">
            </div>

            <div class="vpn-profile-categories">
                <h2>PRODUCT CATEGORIES</h2>
                <p>Providing packaging solutions that enhance your brand value:</p>
                <ul>
                    <li>Premium paper boxes for cosmetics, gifts, jewelry, and retail products</li>
                    <li>Rigid boxes with magnetic closure, lid and base, and drawer structures</li>
                    <li>Custom printed paper bags for retail and promotional packaging</li>
                    <li>Fabric bags including cotton, canvas, and non-woven bags</li>
                    <li>Fully customized packaging solutions for brands and distributors</li>
                </ul>
            </div>
        </div>

        <div class="vpn-profile-photo-cluster" aria-label="VPN Paper Box Manufacturer production photos">
            <figure class="vpn-profile-photo vpn-profile-photo-one">
                <img src="<?php echo esc_url($factory_one['url']); ?>" alt="Workers producing packaging inside VPN Paper Box Manufacturer" width="<?php echo esc_attr($factory_one['width']); ?>" height="<?php echo esc_attr($factory_one['height']); ?>" loading="lazy" decoding="async">
            </figure>
            <figure class="vpn-profile-photo vpn-profile-photo-two">
                <img src="<?php echo esc_url($factory_two['url']); ?>" alt="Packaging finishing and production process at VPN Paper Box Manufacturer" width="<?php echo esc_attr($factory_two['width']); ?>" height="<?php echo esc_attr($factory_two['height']); ?>" loading="lazy" decoding="async">
            </figure>
            <figure class="vpn-profile-photo vpn-profile-photo-three">
                <img src="<?php echo esc_url($factory_three['url']); ?>" alt="Premium packaging products produced by VPN Paper Box Manufacturer" width="<?php echo esc_attr($factory_three['width']); ?>" height="<?php echo esc_attr($factory_three['height']); ?>" loading="lazy" decoding="async">
            </figure>
            <figure class="vpn-profile-photo vpn-profile-photo-four">
                <img src="<?php echo esc_url($factory_fly['url']); ?>" alt="Aerial view of VPN Paper Box Manufacturer facility" width="<?php echo esc_attr($factory_fly['width']); ?>" height="<?php echo esc_attr($factory_fly['height']); ?>" loading="lazy" decoding="async">
            </figure>
        </div>

        <div class="vpn-profile-right">
            <div class="vpn-profile-right-top">
                <h2>BEST QUALITY PRODUCTS</h2>
            </div>

            <div class="vpn-profile-blue-panel">
                <div class="vpn-profile-panel-wave" aria-hidden="true"></div>

                <h2>WHY CHOOSE US</h2>
                <ul class="vpn-profile-why-list">
                    <li>
                        <span class="vpn-profile-icon"><i class="far fa-building"></i></span>
                        <p>Over 9 years of manufacturing experience</p>
                    </li>
                    <li>
                        <span class="vpn-profile-icon"><i class="far fa-clipboard"></i></span>
                        <p>Fast production and on-time delivery</p>
                    </li>
                    <li>
                        <span class="vpn-profile-icon"><i class="fas fa-chart-column"></i></span>
                        <p>Factory-direct pricing with no middlemen</p>
                    </li>
                    <li>
                        <span class="vpn-profile-icon"><i class="fas fa-box-open"></i></span>
                        <p>Design support and full customization available</p>
                    </li>
                </ul>

                <div class="vpn-profile-bottom">
                    <h2>PRODUCT CATEGORY</h2>
                    <p>Packaging and printing solutions that add value to your brand</p>
                    <a href="<?php echo esc_url('https://online.fliphtml5.com/ibmst/ybfa/index.html#p=4'); ?>" target="_blank" rel="noopener">GET CATALOG &gt;&gt;</a>
                </div>
            </div>
        </div>
    </div>
</section>
