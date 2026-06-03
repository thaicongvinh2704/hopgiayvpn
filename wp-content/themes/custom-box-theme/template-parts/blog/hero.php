<?php
/**
 * Single blog hero.
 */

defined('ABSPATH') || exit;

$blog_page_id = (int) get_option('page_for_posts');
$blog_url = $blog_page_id ? get_permalink($blog_page_id) : home_url('/blog/');
$post_image = get_the_post_thumbnail_url(get_the_ID(), 'full');
if (!$post_image) {
    $post_image = get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp';
}

$post_categories = get_the_category();
$primary_category = !empty($post_categories) ? $post_categories[0] : null;
$intro = has_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 28);
?>

<section class="blog-seo-hero">
    <div class="container blog-seo-hero-grid">
        <div class="blog-seo-hero-copy">
            <nav class="blog-breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'custom-box-theme'); ?>">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'custom-box-theme'); ?></a>
                <a href="<?php echo esc_url($blog_url); ?>"><?php esc_html_e('Blog', 'custom-box-theme'); ?></a>
                <span><?php the_title(); ?></span>
            </nav>

            <?php if ($primary_category) : ?>
                <a class="blog-seo-category" href="<?php echo esc_url(get_category_link($primary_category)); ?>">
                    <?php echo esc_html($primary_category->name); ?>
                </a>
            <?php endif; ?>

            <h1><?php the_title(); ?></h1>
            <p><?php echo esc_html($intro); ?></p>

            <div class="blog-seo-meta">
                <span><i class="far fa-calendar"></i><?php echo esc_html(get_the_date('M j, Y')); ?></span>
                <span><i class="far fa-user"></i><?php echo esc_html(get_the_author()); ?></span>
                <span><i class="far fa-clock"></i><?php echo esc_html(max(3, ceil(str_word_count(wp_strip_all_tags(get_the_content())) / 220))); ?> <?php esc_html_e('min read', 'custom-box-theme'); ?></span>
            </div>
        </div>

        <figure class="blog-seo-hero-image">
            <img src="<?php echo esc_url($post_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" decoding="async">
        </figure>
    </div>
</section>
