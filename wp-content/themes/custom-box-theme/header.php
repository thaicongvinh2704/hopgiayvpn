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

<!-- ===== TOP BAR ===== -->
<div class="top-bar">
    <div class="container top-inner">

        <div class="top-left">
            <span>
                <i class="far fa-envelope"></i>
                <a href="mailto:paperbox@hopgiayvpn.com">paperbox@hopgiayvpn.com</a>
                /
                <a href="mailto:sale02.vpn@hopgiayvpn.com">sale02.vpn@hopgiayvpn.com</a>
            </span>
        </div>

        <div class="top-right">
            <a href="https://www.facebook.com/people/Vietnam-Paper-Box-Factory" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.youtube.com/@VietnamPaperBoxFactory" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            <a href="https://www.tiktok.com/@paperbox84" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
            <a href="https://www.pinterest.com/VPNPaperBox" aria-label="Pinterest"><i class="fab fa-pinterest-p"></i></a>
            <a href="https://www.linkedin.com/company/vpn-advertising-co/" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            <a href="https://vpnadvertising.trustpass.alibaba.com/" aria-label="Alibaba TrustPass" target="_blank" rel="noopener">
                <img class="social-icon-img" src="https://cdn.simpleicons.org/alibabadotcom/FFFFFF" alt="" loading="lazy" decoding="async">
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
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-hop-giay-vpn-hcm.png'); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
                    <?php
                }
                ?>
            </a>
        </div>

        <!-- SEARCH -->
        <div class="header-search">
            <input type="text" placeholder="Search">
            <button type="button">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <button class="mobile-menu-icon" type="button" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e('Open navigation menu', 'custom-box-theme'); ?>">
            <i class="fas fa-bars"></i>
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

            <a href="tel:933102653" class="btn-phone">
                <span class="icon-circle">
                    <i class="fas fa-phone"></i>
                </span>
                <span>(+84) 933 102 653</span>
            </a>

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
            $custom_boxes_parent = taxonomy_exists('product_cat') ? get_term_by('name', 'Custom Packaging Boxes', 'product_cat') : false;
            $custom_boxes_link = ($custom_boxes_parent && !is_wp_error($custom_boxes_parent)) ? get_term_link($custom_boxes_parent) : '';
            $custom_boxes_link = is_wp_error($custom_boxes_link) || !$custom_boxes_link ? (function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')) : $custom_boxes_link;
            ?>
            <a class="all-categories" href="<?php echo esc_url($custom_boxes_link); ?>">
                <i class="fas fa-table-cells-large"></i>
                <span><?php esc_html_e('All Categories', 'custom-box-theme'); ?></span>
                <i class="fas fa-chevron-down"></i>
            </a>

            <?php
            $all_categories_columns = function_exists('custom_box_get_all_categories_menu_columns') ? custom_box_get_all_categories_menu_columns() : array();
            $product_group_links = function_exists('custom_box_get_product_group_link') ? array(
                'Custom Boxes'    => $custom_boxes_link,
                'Hang Tags'       => custom_box_get_product_group_link('Hang Tags', $custom_boxes_link),
                'Wrapping Paper'  => custom_box_get_product_group_link('Wrapping Paper', $custom_boxes_link),
                'Business Cards'  => custom_box_get_product_group_link('Business Cards', $custom_boxes_link),
                'Canvas Bags'     => custom_box_get_product_group_link('Canvas Bags', $custom_boxes_link),
            ) : array();
            ?>
            <?php if (!empty($all_categories_columns)) : ?>
                <div class="all-categories-dropdown all-categories-mega">
                    <aside class="all-categories-sidebar">
                        <h3><?php esc_html_e('Product Categories', 'custom-box-theme'); ?></h3>
                        <?php foreach ($product_group_links as $label => $link) : ?>
                            <a class="<?php echo 'Custom Boxes' === $label ? 'is-active' : ''; ?>" href="<?php echo esc_url($link); ?>">
                                <span><?php echo esc_html($label); ?></span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endforeach; ?>
                    </aside>

                    <div class="all-categories-mega-grid">
                        <?php foreach ($all_categories_columns as $column) : ?>
                            <section class="all-categories-column">
                                <h4><?php echo esc_html($column['title']); ?></h4>
                                <?php foreach ($column['terms'] as $category) : ?>
                                    <?php
                                    $category_link = get_term_link($category);
                                    if (is_wp_error($category_link)) {
                                        continue;
                                    }
                                    ?>
                                    <a href="<?php echo esc_url($category_link); ?>"><?php echo esc_html($category->name); ?></a>
                                <?php endforeach; ?>
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
