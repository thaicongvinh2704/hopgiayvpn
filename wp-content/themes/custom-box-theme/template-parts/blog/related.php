<?php
/**
 * Related blog posts.
 */

defined('ABSPATH') || exit;

$blog_page_id = (int) get_option('page_for_posts');
$blog_url = $blog_page_id ? get_permalink($blog_page_id) : home_url('/blog/');
$post_categories = get_the_category();
$related_category_ids = wp_list_pluck($post_categories, 'term_id');
$related_posts = new WP_Query(array(
    'post_type'           => 'post',
    'posts_per_page'      => 3,
    'post__not_in'        => array(get_the_ID()),
    'category__in'        => $related_category_ids,
    'ignore_sticky_posts' => true,
));
?>

<?php if ($related_posts->have_posts()) : ?>
    <section class="blog-related-section">
        <div class="container">
            <div class="blog-section-heading">
                <h2><?php esc_html_e('Related Packaging Guides', 'custom-box-theme'); ?></h2>
                <a href="<?php echo esc_url($blog_url); ?>"><?php esc_html_e('View All Insights', 'custom-box-theme'); ?></a>
            </div>

            <div class="blog-card-grid blog-related-grid">
                <?php while ($related_posts->have_posts()) : $related_posts->the_post(); ?>
                    <?php
                    $related_image = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
                    if (!$related_image) {
                        $related_image = get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp';
                    }
                    ?>
                    <article <?php post_class('blog-card'); ?>>
                        <a class="blog-card-image" href="<?php the_permalink(); ?>">
                            <img src="<?php echo esc_url($related_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy" decoding="async">
                        </a>
                        <div class="blog-card-body">
                            <div class="blog-card-meta">
                                <span><?php echo esc_html(get_the_date('M j, Y')); ?></span>
                            </div>
                            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <a class="blog-read-more" href="<?php the_permalink(); ?>">
                                <?php esc_html_e('Read More', 'custom-box-theme'); ?>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php wp_reset_postdata(); ?>
<?php endif; ?>
