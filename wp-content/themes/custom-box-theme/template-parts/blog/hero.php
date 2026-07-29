<?php
/**
 * Single blog hero.
 */

defined('ABSPATH') || exit;

$blog_page_id = (int) get_option('page_for_posts');
$blog_url = $blog_page_id ? get_permalink($blog_page_id) : home_url('/blog/');
$featured_image_id = get_post_thumbnail_id(get_the_ID());
$featured_image_path = $featured_image_id ? get_attached_file($featured_image_id) : '';

if ($featured_image_id && (!$featured_image_path || !is_file($featured_image_path))) {
    $featured_image_id = 0;
}

$featured_image_alt = $featured_image_id ? trim((string) get_post_meta($featured_image_id, '_wp_attachment_image_alt', true)) : '';

if ($featured_image_alt && 0 === strcasecmp(wp_strip_all_tags($featured_image_alt), wp_strip_all_tags(get_the_title()))) {
    $featured_image_alt = '';
}

$post_categories = get_the_category();
$primary_category = !empty($post_categories) ? $post_categories[0] : null;
$intro = has_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 28);
?>

<section class="blog-seo-hero" aria-labelledby="article-title" data-article-hero>
    <div class="container blog-seo-hero-grid">
        <div class="blog-seo-hero-copy">
            <nav class="blog-breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'custom-box-theme'); ?>" data-blog-breadcrumb>
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'custom-box-theme'); ?></a>
                <a href="<?php echo esc_url($blog_url); ?>"><?php esc_html_e('Blog', 'custom-box-theme'); ?></a>
                <span aria-current="page"><?php the_title(); ?></span>
            </nav>

            <?php if ($primary_category) : ?>
                <a class="blog-seo-category" href="<?php echo esc_url(get_category_link($primary_category)); ?>">
                    <?php echo esc_html($primary_category->name); ?>
                </a>
            <?php endif; ?>

            <h1 id="article-title"><?php the_title(); ?></h1>
            <p class="blog-seo-intro"><?php echo esc_html($intro); ?></p>

            <div class="blog-seo-meta" aria-label="<?php esc_attr_e('Article details', 'custom-box-theme'); ?>">
                <span><i class="far fa-calendar" aria-hidden="true"></i><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('M j, Y')); ?></time></span>
                <span><i class="far fa-user" aria-hidden="true"></i><?php echo esc_html(get_the_author()); ?></span>
                <span><i class="far fa-clock" aria-hidden="true"></i><?php echo esc_html(max(3, ceil(str_word_count(wp_strip_all_tags(get_the_content())) / 220))); ?> <?php esc_html_e('min read', 'custom-box-theme'); ?></span>
            </div>
        </div>

        <figure class="blog-seo-hero-image" data-article-hero-media>
            <?php if ($featured_image_id) : ?>
                <?php
                echo wp_get_attachment_image(
                    $featured_image_id,
                    'full',
                    false,
                    array(
                        'alt'           => $featured_image_alt,
                        'loading'       => 'eager',
                        'decoding'      => 'async',
                        'fetchpriority' => 'high',
                        'sizes'         => '(max-width: 900px) 100vw, 50vw',
                    )
                );
                ?>
            <?php else : ?>
                <img
                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp'); ?>"
                    alt=""
                    width="506"
                    height="277"
                    loading="eager"
                    decoding="async"
                    fetchpriority="high"
                >
            <?php endif; ?>
        </figure>
    </div>
</section>
