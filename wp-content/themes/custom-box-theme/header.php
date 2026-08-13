<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="LOs4ohXuZmpUmZKsNAkORmduEB6kRpSOhTVwpx6FjLI">
    <meta name="msvalidate.01" content="C579B169C5D33F0F290D004CB8462FF7">

    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "wvizgvvulu");
    </script>

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php if (function_exists('custom_box_is_paper_box_manufacturer_page') && custom_box_is_paper_box_manufacturer_page()) : ?>
<header class="vpn-lp-minimal-header">
    <div class="vpn-lp-minimal-header__inner">
        <div class="vpn-lp-minimal-header__logo">
            <a href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                <?php
                if (has_custom_logo()) {
                    the_custom_logo();
                } else {
                    ?>
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-hop-giay-vpn-hcm.webp'); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" width="711" height="567">
                    <?php
                }
                ?>
            </a>
        </div>
        <nav class="vpn-lp-minimal-header__actions" aria-label="<?php esc_attr_e('Paper box quote actions', 'custom-box-theme'); ?>">
            <a class="vpn-lp-minimal-header__whatsapp pbm-js-whatsapp" href="https://wa.me/84933102653" target="_blank" rel="noopener" data-cta="header-whatsapp">WhatsApp</a>
            <a class="vpn-lp-minimal-header__quote pbm-js-quote-cta" href="#quote-form" data-cta="header-get-quote"><span class="vpn-lp-quote-text-long">Get Quote</span><span class="vpn-lp-quote-text-short">Quote</span></a>
            <button class="vpn-lp-minimal-header__menu" type="button" aria-controls="pbm-mobile-menu" aria-expanded="false" aria-label="<?php esc_attr_e('Open navigation menu', 'custom-box-theme'); ?>" data-pbm-menu-toggle>
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
        </nav>
    </div>
    <div class="vpn-lp-minimal-header__drawer" id="pbm-mobile-menu" hidden data-pbm-mobile-menu>
        <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'custom-box-theme'); ?></a>
        <a href="<?php echo esc_url(function_exists('custom_box_get_products_url') ? custom_box_get_products_url() : home_url('/products/')); ?>"><?php esc_html_e('Products', 'custom-box-theme'); ?></a>
        <a href="<?php echo esc_url(home_url('/custom-packaging-boxes-manufacturer/')); ?>"><?php esc_html_e('Custom Packaging', 'custom-box-theme'); ?></a>
        <a href="<?php echo esc_url(home_url('/about/')); ?>"><?php esc_html_e('About Us', 'custom-box-theme'); ?></a>
        <a href="<?php echo esc_url(home_url('/blog/')); ?>"><?php esc_html_e('Blog', 'custom-box-theme'); ?></a>
        <a href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Contact Us', 'custom-box-theme'); ?></a>
        <a href="<?php echo esc_url(home_url('/catalog/')); ?>"><?php esc_html_e('Catalog', 'custom-box-theme'); ?></a>
        <a href="mailto:sales.vpn@hopgiayvpn.com">Email</a>
        <button type="button" data-pbm-copy-phone>WeChat / Viber</button>
    </div>
</header>
<?php return; ?>
<?php endif; ?>

<!-- ===== TOP BAR ===== -->
<div class="top-bar">
    <div class="container top-inner">

        <div class="top-left">
            <span>
                <i class="far fa-envelope"></i>
                <a href="mailto:sales.vpn@hopgiayvpn.com">sales.vpn@hopgiayvpn.com</a>
                /
                <a href="mailto:paperbox@hopgiayvpn.com">paperbox@hopgiayvpn.com</a>
            </span>
        </div>

        <div class="top-right">
            <a href="https://www.facebook.com/people/Vietnam-Paper-Box-Factory" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.youtube.com/@VietnamPaperBoxFactory" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            <a href="https://www.tiktok.com/@paperbox84" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
            <a href="https://www.pinterest.com/VPNPaperBox" aria-label="Pinterest"><i class="fab fa-pinterest-p"></i></a>
            <a href="https://www.linkedin.com/company/vpn-advertising-co/" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            <a href="https://vpnadvertising.trustpass.alibaba.com/" aria-label="Alibaba TrustPass" target="_blank" rel="noopener">
                <i class="fas fa-store" aria-hidden="true"></i>
            </a>
        </div>

    </div>
</div>

<!-- ===== MAIN HEADER ===== -->
<header class="main-header">
    <div class="container header-row">

        <!-- LOGO -->
        <div class="logo">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <?php
                if (has_custom_logo()) {
                    the_custom_logo();
                } else {
                    ?>
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-hop-giay-vpn-hcm.webp'); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" width="711" height="567">
                    <?php
                }
                ?>
            </a>
        </div>

        <!-- SEARCH -->
        <form class="header-search" id="mobile-site-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" data-search-suggestions>
            <label class="screen-reader-text" for="header-search-field"><?php esc_html_e('Search for:', 'custom-box-theme'); ?></label>
            <input id="header-search-field" type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php esc_attr_e('Search', 'custom-box-theme'); ?>" autocomplete="off" data-search-input>
            <input type="hidden" name="post_type" value="product">
            <input type="hidden" name="vpn_search_scope" value="header_product">
            <button type="submit" aria-label="<?php esc_attr_e('Search', 'custom-box-theme'); ?>">
                <i class="fas fa-search"></i>
            </button>
            <div class="header-search-suggestions" data-search-results hidden></div>
        </form>

        <button class="mobile-search-toggle" type="button" aria-controls="mobile-site-search" aria-expanded="false" aria-label="<?php esc_attr_e('Open product search', 'custom-box-theme'); ?>" data-mobile-search-toggle>
            <i class="fas fa-search" aria-hidden="true"></i>
        </button>

        <button class="mobile-menu-icon" type="button" aria-controls="mobile-site-menu" aria-expanded="false" aria-label="<?php esc_attr_e('Open navigation menu', 'custom-box-theme'); ?>" data-mobile-menu-toggle>
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>

        <!-- RIGHT -->
        <div class="header-right">

            <a href="<?php echo esc_url(home_url('/catalog/')); ?>" class="btn-catalog">
                <span class="icon-circle">
                    <i class="fas fa-book-open"></i>
                </span>
                <span>Catalog</span>
            </a>

            <a href="<?php echo esc_url(home_url('/contact/#quote')); ?>" class="btn-quote">
                <span class="icon-circle">
                    <i class="far fa-comment"></i>
                </span>
                <span>Custom Quote</span>
            </a>

            <div class="header-contact-actions" aria-label="<?php esc_attr_e('Contact options', 'custom-box-theme'); ?>">
                <a href="tel:+84933102653" class="header-phone-btn" aria-label="<?php esc_attr_e('Call us at +84 933 102 653', 'custom-box-theme'); ?>">
                    <span class="header-phone-icon" aria-hidden="true">
                        <i class="fas fa-phone"></i>
                    </span>
                    <span class="header-phone-number">(+84) 933 102 653</span>
                </a>
                <a href="https://wa.me/84933102653" class="header-chat-btn whatsapp" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('Contact us on WhatsApp', 'custom-box-theme'); ?>">
                    <i class="fab fa-whatsapp" aria-hidden="true"></i>
                    <span>WhatsApp</span>
                </a>
                <button type="button" class="header-chat-btn viber header-viber-trigger" aria-label="<?php esc_attr_e('Open Viber contact information', 'custom-box-theme'); ?>" aria-expanded="false" aria-controls="header-viber-popover" data-viber-trigger>
                    <i class="fab fa-viber" aria-hidden="true"></i>
                    <span>Viber</span>
                </button>
                <button type="button" class="header-chat-btn wechat header-wechat-trigger" aria-label="<?php esc_attr_e('Open WeChat contact information', 'custom-box-theme'); ?>" aria-expanded="false" aria-controls="header-wechat-popover" data-wechat-trigger>
                    <i class="fab fa-weixin" aria-hidden="true"></i>
                    <span>WeChat</span>
                </button>
                <div class="header-wechat-popover" id="header-viber-popover" role="dialog" aria-modal="false" aria-labelledby="header-viber-title" hidden data-viber-popover>
                    <button type="button" class="header-wechat-close" aria-label="<?php esc_attr_e('Close Viber contact information', 'custom-box-theme'); ?>" data-viber-close>
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                    <div class="header-wechat-popover-head">
                        <span class="header-wechat-badge" aria-hidden="true"><i class="fab fa-viber"></i></span>
                        <h3 id="header-viber-title"><?php esc_html_e('Contact us on Viber', 'custom-box-theme'); ?></h3>
                    </div>
                    <p class="header-wechat-text"><?php esc_html_e('Search this phone number in Viber:', 'custom-box-theme'); ?></p>
                    <div class="header-wechat-phone">+84 933 102 653</div>
                    <p class="header-wechat-helper"><?php esc_html_e('You can add us by searching this phone number in Viber.', 'custom-box-theme'); ?></p>
                    <button type="button" class="header-wechat-copy" data-viber-copy-phone aria-label="<?php esc_attr_e('Copy Viber phone number', 'custom-box-theme'); ?>"><?php esc_html_e('Copy Phone Number', 'custom-box-theme'); ?></button>
                    <span class="header-wechat-copy-status" aria-live="polite" data-viber-copy-status></span>
                </div>
                <div class="header-wechat-popover" id="header-wechat-popover" role="dialog" aria-modal="false" aria-labelledby="header-wechat-title" hidden data-wechat-popover>
                    <button type="button" class="header-wechat-close" aria-label="<?php esc_attr_e('Close WeChat contact information', 'custom-box-theme'); ?>" data-wechat-close>
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                    <div class="header-wechat-popover-head">
                        <span class="header-wechat-badge" aria-hidden="true"><i class="fab fa-weixin"></i></span>
                        <h3 id="header-wechat-title"><?php esc_html_e('Contact us on WeChat', 'custom-box-theme'); ?></h3>
                    </div>
                    <p class="header-wechat-text"><?php esc_html_e('Search this phone number in WeChat:', 'custom-box-theme'); ?></p>
                    <div class="header-wechat-phone">+84 933 102 653</div>
                    <p class="header-wechat-helper"><?php esc_html_e('You can add us by searching this phone number in WeChat.', 'custom-box-theme'); ?></p>
                    <button type="button" class="header-wechat-copy" data-copy-phone aria-label="<?php esc_attr_e('Copy WeChat phone number', 'custom-box-theme'); ?>"><?php esc_html_e('Copy Phone Number', 'custom-box-theme'); ?></button>
                    <span class="header-wechat-copy-status" aria-live="polite" data-copy-status></span>
                </div>
                <div class="header-wechat-backdrop" hidden data-viber-backdrop></div>
                <div class="header-wechat-backdrop" hidden data-wechat-backdrop></div>
            </div>

        </div>

    </div>
</header>

<!-- ===== NAV ===== -->
<nav class="main-nav">
    <div class="container nav-inner">

        <!-- MOBILE TOGGLE -->
        <button class="menu-toggle" type="button" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e('Open navigation menu', 'custom-box-theme'); ?>">
            <i class="fas fa-bars"></i>
        </button>

        <div class="all-categories-wrap">
            <?php
            $products_link = function_exists('custom_box_get_products_url') ? custom_box_get_products_url() : home_url('/products/');
            ?>
            <a class="all-categories" href="<?php echo esc_url($products_link); ?>">
                <i class="fas fa-table-cells-large"></i>
                <span><?php esc_html_e('All Categories', 'custom-box-theme'); ?></span>
                <i class="fas fa-chevron-down"></i>
            </a>

            <?php
            $all_categories_columns = function_exists('custom_box_get_all_categories_menu_columns') ? custom_box_get_all_categories_menu_columns() : array();
            $product_group_links = function_exists('custom_box_get_all_categories_sidebar_links') ? custom_box_get_all_categories_sidebar_links($products_link) : array();
            ?>
            <?php if (!empty($all_categories_columns)) : ?>
                <div class="all-categories-dropdown all-categories-mega">
                    <aside class="all-categories-sidebar">
                        <h3><?php esc_html_e('Product Categories', 'custom-box-theme'); ?></h3>
                        <?php $group_index = 0; ?>
                        <?php foreach ($product_group_links as $label => $link) : ?>
                            <a class="<?php echo 0 === $group_index ? 'is-active' : ''; ?>" href="<?php echo esc_url($link); ?>">
                                <span><?php echo esc_html($label); ?></span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <?php $group_index++; ?>
                        <?php endforeach; ?>
                    </aside>

                    <div class="all-categories-mega-grid">
                        <?php foreach ($all_categories_columns as $column) : ?>
                            <?php
                            $column_terms = !empty($column['terms']) && is_array($column['terms']) ? $column['terms'] : array();
                            $column_class = count($column_terms) > 6 ? ' all-categories-column-wide' : '';
                            ?>
                            <section class="all-categories-column<?php echo esc_attr($column_class); ?>">
                                <h4><?php echo esc_html($column['title']); ?></h4>
                                <div class="all-categories-link-list">
                                    <?php foreach ($column_terms as $category) : ?>
                                        <?php
                                        $category_link = get_term_link($category);
                                        if (is_wp_error($category_link)) {
                                            continue;
                                        }
                                        ?>
                                        <a href="<?php echo esc_url($category_link); ?>"><?php echo esc_html($category->name); ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php
        wp_nav_menu(array(
            'theme_location' => 'primary',
            'container'      => false,
            'menu_id'        => 'primary-menu',
            'menu_class'     => 'nav-menu',
            'fallback_cb'    => 'custom_box_primary_menu_fallback',
            'depth'          => 2,
        ));
        ?>

    </div>
</nav>

<div class="mobile-menu-overlay" hidden data-mobile-menu-overlay></div>
<aside
    class="mobile-menu-drawer"
    id="mobile-site-menu"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
    aria-labelledby="mobile-site-menu-title"
    hidden
    data-mobile-menu-drawer
>
    <div class="mobile-menu-drawer__header">
        <strong id="mobile-site-menu-title"><?php esc_html_e('Menu', 'custom-box-theme'); ?></strong>
        <button type="button" class="mobile-menu-drawer__close" aria-label="<?php esc_attr_e('Close navigation menu', 'custom-box-theme'); ?>" data-mobile-menu-close>
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
    </div>

    <nav class="mobile-menu-drawer__nav" aria-label="<?php esc_attr_e('Mobile navigation', 'custom-box-theme'); ?>">
        <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'custom-box-theme'); ?></a>
        <a href="<?php echo esc_url($products_link); ?>"><?php esc_html_e('Products', 'custom-box-theme'); ?></a>
        <a href="<?php echo esc_url(home_url('/custom-packaging-boxes-manufacturer/')); ?>"><?php esc_html_e('Custom Packaging', 'custom-box-theme'); ?></a>
        <a href="<?php echo esc_url(home_url('/about/')); ?>"><?php esc_html_e('About Us', 'custom-box-theme'); ?></a>
        <a href="<?php echo esc_url(home_url('/blog/')); ?>"><?php esc_html_e('Blog', 'custom-box-theme'); ?></a>
        <a href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Contact Us', 'custom-box-theme'); ?></a>
    </nav>

    <?php if (!empty($all_categories_columns)) : ?>
        <div class="mobile-menu-drawer__categories" aria-label="<?php esc_attr_e('Product categories', 'custom-box-theme'); ?>">
            <h2><?php esc_html_e('Product Categories', 'custom-box-theme'); ?></h2>
            <?php foreach ($all_categories_columns as $mobile_column_index => $column) : ?>
                <?php
                $mobile_column_terms = !empty($column['terms']) && is_array($column['terms']) ? $column['terms'] : array();
                if (empty($mobile_column_terms)) {
                    continue;
                }
                ?>
                <details class="mobile-menu-category">
                    <summary>
                        <span><?php echo esc_html($column['title']); ?></span>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </summary>
                    <div class="mobile-menu-category__links">
                        <?php foreach ($mobile_column_terms as $category) : ?>
                            <?php
                            $mobile_category_link = get_term_link($category);
                            if (is_wp_error($mobile_category_link)) {
                                continue;
                            }
                            ?>
                            <a href="<?php echo esc_url($mobile_category_link); ?>"><?php echo esc_html($category->name); ?></a>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="mobile-menu-drawer__contact">
        <h2><?php echo function_exists('custom_box_is_custom_paper_bags_manufacturer_landing') && custom_box_is_custom_paper_bags_manufacturer_landing() ? esc_html__('Contact VPN Paper Box Sales', 'custom-box-theme') : esc_html__('Contact Factory Sales', 'custom-box-theme'); ?></h2>
        <a href="tel:+84933102653"><i class="fas fa-phone" aria-hidden="true"></i><span>(+84) 933 102 653</span></a>
        <a href="mailto:sales.vpn@hopgiayvpn.com"><i class="fas fa-envelope" aria-hidden="true"></i><span>sales.vpn@hopgiayvpn.com</span></a>
        <a href="https://wa.me/84933102653" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp" aria-hidden="true"></i><span>WhatsApp</span></a>
        <button type="button" data-mobile-copy-contact="+84933102653">
            <i class="fab fa-viber" aria-hidden="true"></i>
            <span><?php esc_html_e('Copy number for Viber / WeChat', 'custom-box-theme'); ?></span>
        </button>
        <span class="mobile-menu-copy-status" role="status" aria-live="polite" data-mobile-copy-status></span>
    </div>
</aside>

<script>
(function() {
    var phoneNumber = '+84 933 102 653';
    var phoneNumberCompact = '+84933102653';
    var activeContact = null;

    function fallbackCopyText(text) {
        var input = document.createElement('input');
        input.value = text;
        input.setAttribute('readonly', '');
        input.style.position = 'fixed';
        input.style.opacity = '0';
        input.style.pointerEvents = 'none';
        input.style.left = '-9999px';
        document.body.appendChild(input);
        input.select();

        try {
            document.execCommand('copy');
        } finally {
            document.body.removeChild(input);
        }
    }

    function setupContactPopover(config) {
        var trigger = document.querySelector(config.trigger);
        var popover = document.querySelector(config.popover);
        var backdrop = document.querySelector(config.backdrop);
        var closeButton = document.querySelector(config.close);
        var copyButton = document.querySelector(config.copy);
        var copyStatus = document.querySelector(config.status);
        var copyTimer = null;

        if (!trigger || !popover || !backdrop) {
            return null;
        }

        function setOpen(isOpen) {
            popover.hidden = !isOpen;
            backdrop.hidden = !isOpen;
            trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            trigger.classList.toggle('is-open', isOpen);

            if (!isOpen && copyStatus) {
                copyStatus.textContent = '';
            }

            if (!isOpen && copyTimer) {
                window.clearTimeout(copyTimer);
                copyTimer = null;
            }
        }

        function closePopover(shouldFocus) {
            setOpen(false);
            if (shouldFocus) {
                trigger.focus({ preventScroll: true });
            }
            if (activeContact === contact) {
                activeContact = null;
            }
        }

        function openPopover() {
            if (activeContact && activeContact !== contact) {
                activeContact.close(false);
            }
            setOpen(true);
            activeContact = contact;
        }

        var contact = {
            close: closePopover,
            isOpen: function() {
                return !popover.hidden;
            }
        };

        trigger.addEventListener('click', function() {
            if (contact.isOpen()) {
                closePopover(true);
            } else {
                openPopover();
            }
        });

        backdrop.addEventListener('click', function() {
            closePopover(true);
        });

        if (closeButton) {
            closeButton.addEventListener('click', function() {
                closePopover(true);
            });
        }

        if (copyButton) {
            function showCopied() {
                if (!copyStatus) {
                    return;
                }

                copyStatus.textContent = 'Copied.';
                if (copyTimer) {
                    window.clearTimeout(copyTimer);
                }
                copyTimer = window.setTimeout(function() {
                    if (copyStatus) {
                        copyStatus.textContent = '';
                    }
                    copyTimer = null;
                }, 1500);
            }

            copyButton.addEventListener('click', function() {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(phoneNumberCompact).then(showCopied).catch(function() {
                        try {
                            fallbackCopyText(phoneNumberCompact);
                            showCopied();
                        } catch (error) {
                            window.prompt('Copy the phone number:', phoneNumber);
                        }
                    });
                    return;
                }

                try {
                    fallbackCopyText(phoneNumberCompact);
                    showCopied();
                } catch (error) {
                    window.prompt('Copy the phone number:', phoneNumber);
                }
            });
        }

        return contact;
    }

    setupContactPopover({
        trigger: '[data-viber-trigger]',
        popover: '[data-viber-popover]',
        backdrop: '[data-viber-backdrop]',
        close: '[data-viber-close]',
        copy: '[data-viber-copy-phone]',
        status: '[data-viber-copy-status]'
    });

    setupContactPopover({
        trigger: '[data-wechat-trigger]',
        popover: '[data-wechat-popover]',
        backdrop: '[data-wechat-backdrop]',
        close: '[data-wechat-close]',
        copy: '[data-copy-phone]',
        status: '[data-copy-status]'
    });

    document.addEventListener('keydown', function(event) {
        if ('Escape' === event.key && activeContact) {
            activeContact.close(true);
        }
    });
})();
</script>
