<?php
/**
 * Theme setup, menus, widgets, and lightweight template helpers.
 */

function custom_box_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('custom-logo', array(
        'height'      => 120,
        'width'       => 320,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    add_theme_support('html5', array(
        'comment-list',
        'comment-form',
        'search-form',
        'gallery',
        'caption',
        'style',
        'script',
    ));
    add_theme_support('customize-selective-refresh-widgets');

    register_nav_menus(array(
        'primary' => __('Primary Menu', 'custom-box-theme'),
        'footer'  => __('Footer Menu', 'custom-box-theme'),
    ));
}
add_action('after_setup_theme', 'custom_box_theme_setup');

function custom_box_primary_menu_fallback() {
    echo '<ul class="nav-menu">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'custom-box-theme') . '</a></li>';
    wp_list_pages(array(
        'title_li' => '',
        'depth'    => 1,
    ));
    echo '</ul>';
}

function custom_box_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar', 'custom-box-theme'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here.', 'custom-box-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Widgets', 'custom-box-theme'),
        'id'            => 'footer-widgets',
        'description'   => __('Add footer widgets here.', 'custom-box-theme'),
        'before_widget' => '<section id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'custom_box_widgets_init');
