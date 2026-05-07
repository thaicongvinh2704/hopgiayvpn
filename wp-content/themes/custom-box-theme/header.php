<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- ===== TOP BAR ===== -->
<div class="top-bar">
    <div class="container top-inner">

        <div class="top-left">
            <span>✉ inquiry@customboxesinc.com</span>
        </div>

        <div class="top-right">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-pinterest"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
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

        <!-- RIGHT -->
        <div class="header-right">

            <a href="#" class="btn-quote">
                <span class="icon-circle">
                    <i class="far fa-comment"></i>
                </span>
                <span>Custom Quote</span>
            </a>

            <a href="tel:8335650363" class="btn-phone">
                <span class="icon-circle">
                    <i class="fas fa-phone"></i>
                </span>
                <span>(833) 565-0363</span>
            </a>

        </div>

    </div>
</header>

<!-- ===== NAV ===== -->
<nav class="main-nav">
    <div class="container nav-inner">

        <!-- MOBILE TOGGLE -->
        <div class="menu-toggle">☰</div>

        <div class="all-categories">
            ☰ All Categories
        </div>

        <?php
        wp_nav_menu(array(
            'theme_location' => 'primary',
            'container'      => false,
            'menu_class'     => 'nav-menu',
            'fallback_cb'    => 'custom_box_primary_menu_fallback',
        ));
        ?>

    </div>
</nav>
