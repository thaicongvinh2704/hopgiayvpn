<?php
$footer_theme_uri = get_template_directory_uri();
$footer_is_paper_bags_landing = function_exists('custom_box_is_custom_paper_bags_manufacturer_landing') && custom_box_is_custom_paper_bags_manufacturer_landing();
$footer_site_name = $footer_is_paper_bags_landing ? 'VPN Paper Box' : (get_bloginfo('name') ?: 'VPN Paper Box Manufacturer');
$footer_phone_display = '(+84) 933 102 653';
$footer_phone_link = 'tel:+84933102653';
$footer_email = 'sales.vpn@hopgiayvpn.com';
$footer_sales_email = 'paperbox@hopgiayvpn.com';
$footer_factory_name = $footer_is_paper_bags_landing ? 'VPN Paper Box' : 'VPN Paper Box Manufacturer';
$footer_factory_address = '1032 An Phu Tay, Hamlet 4, Hung Long Commune, Binh Chanh District, Ho Chi Minh City, Vietnam';
$footer_factory_map_url = 'https://maps.app.goo.gl/Z68geWnrTmx6kaCg6';
$footer_office_address = $footer_factory_address;
$footer_office_map_url = $footer_factory_map_url;
$footer_shop_url = function_exists('custom_box_get_products_url') ? custom_box_get_products_url() : home_url('/products/');
$footer_blog_page_id = (int) get_option('page_for_posts');
$footer_blog_url = $footer_blog_page_id ? get_permalink($footer_blog_page_id) : home_url('/blog/');
$footer_categories = function_exists('custom_box_get_packaging_categories') ? custom_box_get_packaging_categories(7) : array();
$footer_social_links = array(
    array('class' => 'social-facebook', 'url' => 'https://www.facebook.com/people/Vietnam-Paper-Box-Factory/61576428668265/', 'label' => 'Facebook', 'icon' => 'fab fa-facebook-f'),
    array('class' => 'social-youtube', 'url' => 'https://www.youtube.com/@VietnamPaperBoxFactory', 'label' => 'YouTube', 'icon' => 'fab fa-youtube'),
    array('class' => 'social-tiktok', 'url' => 'https://www.tiktok.com/@paperbox84', 'label' => 'TikTok', 'icon' => 'fab fa-tiktok'),
    array('class' => 'social-pinterest', 'url' => 'https://www.pinterest.com/VPNPaperBox', 'label' => 'Pinterest', 'icon' => 'fab fa-pinterest-p'),
    array('class' => 'social-linkedin', 'url' => 'https://www.linkedin.com/company/vpn-advertising-co/', 'label' => 'LinkedIn', 'icon' => 'fab fa-linkedin-in'),
    array('class' => 'social-alibaba', 'url' => 'https://vpnadvertising.trustpass.alibaba.com/', 'label' => 'Alibaba TrustPass', 'icon' => 'fas fa-store'),
);
?>

<footer class="site-footer">
    <div class="container">
        <div class="footer-logo">
            <img src="<?php echo esc_url($footer_theme_uri . '/assets/images/logo-hop-giay-vpn-hcm.webp'); ?>" alt="<?php echo esc_attr($footer_site_name); ?>" width="711" height="567" loading="lazy" decoding="async">
        </div>

        <div class="footer-contact">
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <strong><?php esc_html_e('Address', 'custom-box-theme'); ?></strong>
                <p>
                    <?php echo esc_html($footer_factory_name); ?><br>
                    <?php if ($footer_is_paper_bags_landing) : ?>
                        <span class="footer-address-line">
                            <span class="footer-address-label"><?php esc_html_e('Office & Factory', 'custom-box-theme'); ?></span>
                            <a href="<?php echo esc_url($footer_factory_map_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($footer_factory_address); ?></a>
                        </span>
                    <?php else : ?>
                        <span class="footer-address-line">
                            <span class="footer-address-label"><?php esc_html_e('Office', 'custom-box-theme'); ?></span>
                            <a href="<?php echo esc_url($footer_office_map_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($footer_office_address); ?></a>
                        </span>
                        <span class="footer-address-line">
                            <span class="footer-address-label"><?php esc_html_e('Factory', 'custom-box-theme'); ?></span>
                            <a href="<?php echo esc_url($footer_factory_map_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($footer_factory_address); ?></a>
                        </span>
                    <?php endif; ?>
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
                <p>
                    <a href="mailto:<?php echo esc_attr($footer_email); ?>"><?php echo esc_html($footer_email); ?></a><br>
                    <a href="mailto:<?php echo esc_attr($footer_sales_email); ?>"><?php echo esc_html($footer_sales_email); ?></a>
                </p>
            </div>

            <div class="contact-item">
                <i class="fas fa-clock"></i>
                <strong><?php esc_html_e('Working Hours', 'custom-box-theme'); ?></strong>
                <p><?php esc_html_e('Mon - Sat: 8:00 AM - 6:00 PM', 'custom-box-theme'); ?><br><span><?php esc_html_e('Vietnam time zone', 'custom-box-theme'); ?></span></p>
            </div>
        </div>

        <div class="footer-main">
            <div class="footer-col">
                <h2><?php echo esc_html($footer_site_name); ?></h2>
                <p>
                    <?php if ($footer_is_paper_bags_landing) : ?>
                        <?php esc_html_e('Vietnam-based paper packaging production support for brands, retailers, importers, distributors, and agencies. VPN Paper Box is a packaging brand of Công ty TNHH Quảng Cáo VPN.', 'custom-box-theme'); ?>
                    <?php else : ?>
                        <?php esc_html_e('Vietnam-based packaging manufacturer specializing in custom paper boxes, rigid boxes, paper bags, and export-ready packaging for brands, importers, distributors, and agencies.', 'custom-box-theme'); ?>
                    <?php endif; ?>
                </p>

                <div class="footer-social">
                    <?php foreach ($footer_social_links as $social_link) : ?>
                        <a class="<?php echo esc_attr($social_link['class']); ?>" href="<?php echo esc_url($social_link['url']); ?>" aria-label="<?php echo esc_attr($social_link['label']); ?>" target="_blank" rel="noopener">
                            <?php if (!empty($social_link['image'])) : ?>
                                <img class="social-icon-img" src="<?php echo esc_url($social_link['image']); ?>" alt="" width="24" height="24" loading="lazy" decoding="async">
                            <?php else : ?>
                                <i class="<?php echo esc_attr($social_link['icon']); ?>"></i>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if (!$footer_is_paper_bags_landing) : ?>
                    <div class="footer-payment">
                        <h3><?php esc_html_e('Payment System:', 'custom-box-theme'); ?></h3>
                        <div class="payment-grid">
                            <div class="payment-item">
                                <img src="<?php echo esc_url($footer_theme_uri . '/assets/images/paypal.png'); ?>" alt="PayPal" width="56" height="17" loading="lazy" decoding="async">
                            </div>
                            <div class="payment-item">
                                <img src="<?php echo esc_url($footer_theme_uri . '/assets/images/master-card.png'); ?>" alt="Mastercard" width="38" height="25" loading="lazy" decoding="async">
                            </div>
                            <div class="payment-item">
                                <img src="<?php echo esc_url($footer_theme_uri . '/assets/images/visa.png'); ?>" alt="Visa" width="46" height="15" loading="lazy" decoding="async">
                            </div>
                            <div class="payment-item">
                                <img src="<?php echo esc_url($footer_theme_uri . '/assets/images/maestro.png'); ?>" alt="Maestro" width="38" height="25" loading="lazy" decoding="async">
                            </div>
                            <div class="payment-item">
                                <img src="<?php echo esc_url($footer_theme_uri . '/assets/images/bank.png'); ?>" alt="<?php esc_attr_e('Bank transfer', 'custom-box-theme'); ?>" width="60" height="21" loading="lazy" decoding="async">
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <details class="footer-col footer-disclosure" open data-responsive-disclosure>
                <summary><span class="footer-disclosure-title" role="heading" aria-level="3"><?php esc_html_e('Quick Links', 'custom-box-theme'); ?></span></summary>
                <ul>
                    <li><a href="<?php echo esc_url(home_url('/about/')); ?>"><?php esc_html_e('About Us', 'custom-box-theme'); ?></a></li>
                    <li><a href="<?php echo esc_url($footer_shop_url); ?>"><?php esc_html_e('Products', 'custom-box-theme'); ?></a></li>
                    <li><a href="<?php echo esc_url($footer_blog_url); ?>"><?php esc_html_e('Blog', 'custom-box-theme'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Contact Us', 'custom-box-theme'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/contact/#quote')); ?>"><?php esc_html_e('Request a Quote', 'custom-box-theme'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/#faq')); ?>"><?php esc_html_e('Read FAQs', 'custom-box-theme'); ?></a></li>
                </ul>
            </details>

            <details class="footer-col footer-disclosure" open data-responsive-disclosure>
                <summary><span class="footer-disclosure-title" role="heading" aria-level="3"><?php esc_html_e('Packaging Categories', 'custom-box-theme'); ?></span></summary>
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
            </details>

            <details class="footer-col footer-disclosure" open data-responsive-disclosure>
                <summary><span class="footer-disclosure-title" role="heading" aria-level="3"><?php echo $footer_is_paper_bags_landing ? esc_html__('Paper Bag Capabilities', 'custom-box-theme') : esc_html__('Factory Capabilities', 'custom-box-theme'); ?></span></summary>
                <ul>
                    <li><?php echo $footer_is_paper_bags_landing ? esc_html__('Paper bag production support in Vietnam', 'custom-box-theme') : esc_html__('Direct factory production in Vietnam', 'custom-box-theme'); ?></li>
                    <?php if ($footer_is_paper_bags_landing) : ?>
                        <li><?php esc_html_e('Custom sizes, paper stocks, handles, artwork and finishing', 'custom-box-theme'); ?></li>
                        <li><?php esc_html_e('One-color, CMYK and Pantone printing options', 'custom-box-theme'); ?></li>
                        <li><?php esc_html_e('Quality checkpoints for print, folds, handles and finished appearance', 'custom-box-theme'); ?></li>
                    <?php else : ?>
                        <li><?php esc_html_e('Custom size, structure, dieline, and inserts', 'custom-box-theme'); ?></li>
                        <li><?php esc_html_e('Offset and digital printing support', 'custom-box-theme'); ?></li>
                        <li><?php esc_html_e('Foil stamping, embossing, lamination, and spot UV', 'custom-box-theme'); ?></li>
                    <?php endif; ?>
                    <li><?php echo $footer_is_paper_bags_landing ? esc_html__('Sampling can be arranged before bulk production when required', 'custom-box-theme') : esc_html__('Sampling before mass production', 'custom-box-theme'); ?></li>
                    <li><?php echo $footer_is_paper_bags_landing ? esc_html__('Carton packing and shipment details reviewed against the approved order', 'custom-box-theme') : esc_html__('Export-ready packing for international buyers', 'custom-box-theme'); ?></li>
                </ul>
            </details>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo esc_html(gmdate('Y')); ?> <?php echo esc_html($footer_site_name); ?>. <?php esc_html_e('All rights reserved.', 'custom-box-theme'); ?></p>
        </div>
    </div>
</footer>

<?php if (!is_page_template('page-contact.php') && !is_page('contact')) : ?>
    <nav class="mobile-conversion-bar" aria-label="<?php esc_attr_e('Quick contact actions', 'custom-box-theme'); ?>" data-mobile-conversion-bar>
        <a class="mobile-conversion-bar__quote" href="<?php echo esc_url(home_url('/contact/#quote')); ?>">
            <i class="far fa-comment" aria-hidden="true"></i>
            <span><?php esc_html_e('Request Quote', 'custom-box-theme'); ?></span>
        </a>
        <a class="mobile-conversion-bar__whatsapp" href="https://wa.me/84933102653" target="_blank" rel="noopener noreferrer">
            <i class="fab fa-whatsapp" aria-hidden="true"></i>
            <span>WhatsApp</span>
        </a>
    </nav>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
