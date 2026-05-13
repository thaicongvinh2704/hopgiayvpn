<?php
$footer_theme_uri = get_template_directory_uri();
$footer_site_name = get_bloginfo('name') ?: 'VPN Packaging Factory';
$footer_phone_display = '(+84) 933 102 653';
$footer_phone_link = 'tel:+84933102653';
$footer_email = 'paperbox@hopgiayvpn.com';
$footer_factory_name = 'VPN Packaging Factory';
$footer_address = '1032 An Phu Tay, Hamlet 4, Hung Long Commune, Binh Chanh District, Ho Chi Minh City, Vietnam';
$footer_map_url = 'https://www.google.com/maps/place/X%C6%B0%E1%BB%9Fng+In+VPN/@10.6610408,106.6027949,16.5z/data=!4m14!1m7!3m6!1s0x31753300767fbee3:0x57937201bb84acb2!2zWMaw4bufbmcgSW4gVlBO!8m2!3d10.6604288!4d106.6085237!16s%2Fg%2F11ldlz6hl_!3m5!1s0x31753300767fbee3:0x57937201bb84acb2!8m2!3d10.6604288!4d106.6085237!16s%2Fg%2F11ldlz6hl_?entry=ttu&g_ep=EgoyMDI2MDUwNi4wIKXMDSoASAFQAw%3D%3D';
$footer_shop_url = function_exists('custom_box_get_products_url') ? custom_box_get_products_url() : (function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/products/'));
$footer_blog_page_id = (int) get_option('page_for_posts');
$footer_blog_url = $footer_blog_page_id ? get_permalink($footer_blog_page_id) : home_url('/blog/');
$footer_categories = function_exists('custom_box_get_packaging_categories') ? custom_box_get_packaging_categories(7) : array();
$footer_social_links = array(
    array('class' => 'social-facebook', 'url' => 'https://www.facebook.com/people/Vietnam-Paper-Box-Factory', 'label' => 'Facebook', 'icon' => 'fab fa-facebook-f'),
    array('class' => 'social-youtube', 'url' => 'https://www.youtube.com/@VietnamPaperBoxFactory', 'label' => 'YouTube', 'icon' => 'fab fa-youtube'),
    array('class' => 'social-tiktok', 'url' => 'https://www.tiktok.com/@paperbox84', 'label' => 'TikTok', 'icon' => 'fab fa-tiktok'),
    array('class' => 'social-linkedin', 'url' => 'https://www.linkedin.com/company/vpn-advertising-co/', 'label' => 'LinkedIn', 'icon' => 'fab fa-linkedin-in'),
);
?>

<footer class="site-footer">
    <div class="container">
        <div class="footer-logo">
            <img src="<?php echo esc_url($footer_theme_uri . '/assets/images/logo-hop-giay-vpn-hcm.png'); ?>" alt="<?php echo esc_attr($footer_site_name); ?>">
        </div>

        <div class="footer-contact">
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <strong><?php esc_html_e('Address', 'custom-box-theme'); ?></strong>
                <p>
                    <?php echo esc_html($footer_factory_name); ?><br>
                    <a href="<?php echo esc_url($footer_map_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($footer_address); ?></a>
                </p>
            </div>

            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <strong><?php esc_html_e('Phone', 'custom-box-theme'); ?></strong>
                <p><a href="<?php echo esc_url($footer_phone_link); ?>"><?php echo esc_html($footer_phone_display); ?></a></p>
            </div>

            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <strong><?php esc_html_e('Email', 'custom-box-theme'); ?></strong>
                <p><a href="mailto:<?php echo esc_attr($footer_email); ?>"><?php echo esc_html($footer_email); ?></a></p>
            </div>

            <div class="contact-item">
                <i class="fas fa-clock"></i>
                <strong><?php esc_html_e('Working Hours', 'custom-box-theme'); ?></strong>
                <p><?php esc_html_e('Mon - Sat: 8:00 AM - 6:00 PM', 'custom-box-theme'); ?><br><span><?php esc_html_e('Vietnam time zone', 'custom-box-theme'); ?></span></p>
            </div>
        </div>

        <div class="footer-main">
            <div class="footer-col">
                <h4><?php esc_html_e('VPN Packaging Factory', 'custom-box-theme'); ?></h4>
                <p>
                    <?php esc_html_e('Vietnam-based packaging manufacturer specializing in custom paper boxes, rigid boxes, paper bags, and export-ready packaging for brands, importers, distributors, and agencies.', 'custom-box-theme'); ?>
                </p>

                <div class="footer-social">
                    <?php foreach ($footer_social_links as $social_link) : ?>
                        <a class="<?php echo esc_attr($social_link['class']); ?>" href="<?php echo esc_url($social_link['url']); ?>" aria-label="<?php echo esc_attr($social_link['label']); ?>" target="_blank" rel="noopener">
                            <i class="<?php echo esc_attr($social_link['icon']); ?>"></i>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="footer-payment">
                    <h5><?php esc_html_e('Payment System:', 'custom-box-theme'); ?></h5>
                    <div class="payment-grid">
                        <div class="payment-item">
                            <img src="<?php echo esc_url($footer_theme_uri . '/assets/images/paypal.png'); ?>" alt="PayPal" loading="lazy" decoding="async">
                        </div>
                        <div class="payment-item">
                            <img src="<?php echo esc_url($footer_theme_uri . '/assets/images/master-card.png'); ?>" alt="Mastercard" loading="lazy" decoding="async">
                        </div>
                        <div class="payment-item">
                            <img src="<?php echo esc_url($footer_theme_uri . '/assets/images/visa.png'); ?>" alt="Visa" loading="lazy" decoding="async">
                        </div>
                        <div class="payment-item">
                            <img src="<?php echo esc_url($footer_theme_uri . '/assets/images/maestro.png'); ?>" alt="Maestro" loading="lazy" decoding="async">
                        </div>
                        <div class="payment-item">
                            <img src="<?php echo esc_url($footer_theme_uri . '/assets/images/bank.png'); ?>" alt="<?php esc_attr_e('Bank transfer', 'custom-box-theme'); ?>" loading="lazy" decoding="async">
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-col">
                <h4><?php esc_html_e('Quick Links', 'custom-box-theme'); ?></h4>
                <ul>
                    <li><a href="<?php echo esc_url(home_url('/about/')); ?>"><?php esc_html_e('About Us', 'custom-box-theme'); ?></a></li>
                    <li><a href="<?php echo esc_url($footer_shop_url); ?>"><?php esc_html_e('Products', 'custom-box-theme'); ?></a></li>
                    <li><a href="<?php echo esc_url($footer_blog_url); ?>"><?php esc_html_e('Blog', 'custom-box-theme'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Contact Us', 'custom-box-theme'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/#quote')); ?>"><?php esc_html_e('Request a Quote', 'custom-box-theme'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/#faq')); ?>"><?php esc_html_e('Read FAQs', 'custom-box-theme'); ?></a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4><?php esc_html_e('Packaging Categories', 'custom-box-theme'); ?></h4>
                <ul>
                    <?php if (!empty($footer_categories)) : ?>
                        <?php foreach ($footer_categories as $footer_category) : ?>
                            <?php $footer_category_link = get_term_link($footer_category); ?>
                            <?php if (!is_wp_error($footer_category_link)) : ?>
                                <li><a href="<?php echo esc_url($footer_category_link); ?>"><?php echo esc_html($footer_category->name); ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <li><a href="<?php echo esc_url($footer_shop_url); ?>"><?php esc_html_e('Custom Packaging Products', 'custom-box-theme'); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="footer-col">
                <h4><?php esc_html_e('Factory Capabilities', 'custom-box-theme'); ?></h4>
                <ul>
                    <li><?php esc_html_e('Direct factory production in Vietnam', 'custom-box-theme'); ?></li>
                    <li><?php esc_html_e('Custom size, structure, dieline, and inserts', 'custom-box-theme'); ?></li>
                    <li><?php esc_html_e('Offset and digital printing support', 'custom-box-theme'); ?></li>
                    <li><?php esc_html_e('Foil stamping, embossing, lamination, and spot UV', 'custom-box-theme'); ?></li>
                    <li><?php esc_html_e('Sampling before mass production', 'custom-box-theme'); ?></li>
                    <li><?php esc_html_e('Export-ready packing for international buyers', 'custom-box-theme'); ?></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo esc_html(gmdate('Y')); ?> <?php echo esc_html($footer_site_name); ?>. <?php esc_html_e('All rights reserved.', 'custom-box-theme'); ?></p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
