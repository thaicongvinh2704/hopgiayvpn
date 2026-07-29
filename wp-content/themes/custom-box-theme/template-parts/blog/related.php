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
    <section class="blog-related-section" aria-labelledby="blog-related-title" data-blog-related>
        <div class="container">
            <div class="blog-section-heading">
                <h2 id="blog-related-title"><?php esc_html_e('Related Packaging Guides', 'custom-box-theme'); ?></h2>
                <a href="<?php echo esc_url($blog_url); ?>"><?php esc_html_e('View All Insights', 'custom-box-theme'); ?></a>
            </div>

            <div class="blog-card-grid blog-related-grid">
                <?php while ($related_posts->have_posts()) : $related_posts->the_post(); ?>
                    <?php
                    $related_post_id = get_the_ID();
                    $related_thumbnail_id = get_post_thumbnail_id($related_post_id);
                    $related_thumbnail_path = $related_thumbnail_id ? get_attached_file($related_thumbnail_id) : '';

                    if ($related_thumbnail_id && (!$related_thumbnail_path || !is_file($related_thumbnail_path))) {
                        $related_thumbnail_id = 0;
                    }

                    $related_title_id = 'related-blog-title-' . $related_post_id;
                    ?>
                    <article <?php post_class('blog-card'); ?> aria-labelledby="<?php echo esc_attr($related_title_id); ?>" data-blog-card>
                        <figure class="blog-card-image" aria-hidden="true" data-blog-card-media>
                            <?php if ($related_thumbnail_id) : ?>
                                <?php
                                echo get_the_post_thumbnail(
                                    $related_post_id,
                                    'medium_large',
                                    array(
                                        'alt'      => '',
                                        'loading'  => 'lazy',
                                        'decoding' => 'async',
                                        'sizes'    => '(max-width: 767px) 100vw, (max-width: 1024px) 50vw, 360px',
                                    )
                                );
                                ?>
                            <?php else : ?>
                                <img
                                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp'); ?>"
                                    alt=""
                                    width="506"
                                    height="277"
                                    loading="lazy"
                                    decoding="async"
                                    sizes="(max-width: 767px) 100vw, (max-width: 1024px) 50vw, 360px"
                                >
                            <?php endif; ?>
                        </figure>
                        <div class="blog-card-body">
                            <div class="blog-card-meta">
                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('M j, Y')); ?></time>
                            </div>
                            <h3 id="<?php echo esc_attr($related_title_id); ?>">
                                <a class="blog-card-primary-link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <span class="blog-read-more" aria-hidden="true">
                                <?php esc_html_e('Read More', 'custom-box-theme'); ?>
                                <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            </span>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php wp_reset_postdata(); ?>
<?php endif; ?>
