<?php
/**
 * Blog listing template.
 */

get_header();

$blog_page_id = (int) get_option('page_for_posts');
$blog_url = $blog_page_id ? get_permalink($blog_page_id) : home_url('/blog/');
$fallback_image = get_template_directory_uri() . '/assets/images/custom-cardboard-boxes.webp';
$is_blog_page_template = is_page();
$blog_paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
$blog_query = $GLOBALS['wp_query'];

if ($is_blog_page_template) {
    $blog_query = new WP_Query(array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'paged'               => $blog_paged,
        'ignore_sticky_posts' => false,
    ));
}
?>

<main class="blog-page">
    <section class="blog-hero">
        <div class="container">
            <div class="blog-breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'custom-box-theme'); ?></a>
                <span><?php esc_html_e('Blog', 'custom-box-theme'); ?></span>
            </div>

            <div class="blog-hero-content">
                <span class="blog-eyebrow"><?php esc_html_e('VPN Packaging Insights', 'custom-box-theme'); ?></span>
                <h1><?php esc_html_e('Packaging Insights & Custom Box Guides', 'custom-box-theme'); ?></h1>
                <p><?php esc_html_e('Explore practical guides on custom packaging, paper boxes, printing methods, materials, finishing options, and brand-ready production for international buyers.', 'custom-box-theme'); ?></p>
            </div>
        </div>
    </section>

    <section class="blog-listing-section">
        <div class="container blog-layout">
            <div class="blog-main">
                <?php if ($blog_query->have_posts()) : ?>
                    <div class="blog-card-grid">
                        <?php while ($blog_query->have_posts()) : $blog_query->the_post(); ?>
                            <?php
                            $post_image = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
                            if (!$post_image) {
                                $post_image = $fallback_image;
                            }
                            ?>
                            <article id="post-<?php the_ID(); ?>" <?php post_class('blog-card'); ?>>
                                <a class="blog-card-image" href="<?php the_permalink(); ?>">
                                    <img src="<?php echo esc_url($post_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy" decoding="async">
                                </a>

                                <div class="blog-card-body">
                                    <div class="blog-card-meta">
                                        <span><?php echo esc_html(get_the_date('M j, Y')); ?></span>
                                        <?php $primary_category = get_the_category(); ?>
                                        <?php if (!empty($primary_category)) : ?>
                                            <a href="<?php echo esc_url(get_category_link($primary_category[0])); ?>"><?php echo esc_html($primary_category[0]->name); ?></a>
                                        <?php endif; ?>
                                    </div>

                                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                                    <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 24)); ?></p>
                                    <a class="blog-read-more" href="<?php the_permalink(); ?>">
                                        <?php esc_html_e('Read More', 'custom-box-theme'); ?>
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>

                    <nav class="blog-pagination" aria-label="<?php esc_attr_e('Blog pagination', 'custom-box-theme'); ?>">
                        <?php
                        echo wp_kses_post(paginate_links(array(
                            'current'   => $blog_paged,
                            'total'     => (int) $blog_query->max_num_pages,
                            'prev_text' => '<i class="fas fa-chevron-left"></i>',
                            'next_text' => '<i class="fas fa-chevron-right"></i>',
                        )));
                        ?>
                    </nav>
                    <?php if ($is_blog_page_template) { wp_reset_postdata(); } ?>
                <?php else : ?>
                    <div class="blog-empty">
                        <h2><?php esc_html_e('Packaging articles are coming soon', 'custom-box-theme'); ?></h2>
                        <p><?php esc_html_e('We are preparing practical guides about custom boxes, printing, materials, and finishing for global packaging buyers.', 'custom-box-theme'); ?></p>
                        <a class="btn-primary" href="<?php echo esc_url(home_url('/contact/#quote')); ?>"><?php esc_html_e('Request a Custom Paper Box Quote', 'custom-box-theme'); ?></a>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="blog-sidebar" aria-label="<?php esc_attr_e('Blog sidebar', 'custom-box-theme'); ?>">
                <section class="blog-sidebar-widget blog-search-widget">
                    <h2><?php esc_html_e('Search Insights', 'custom-box-theme'); ?></h2>
                    <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                        <label>
                            <span class="screen-reader-text"><?php esc_html_e('Search for:', 'custom-box-theme'); ?></span>
                            <input type="search" class="search-field" placeholder="<?php esc_attr_e('Search packaging guides...', 'custom-box-theme'); ?>" value="<?php echo esc_attr(get_search_query()); ?>" name="s">
                        </label>
                        <input type="submit" class="search-submit" value="<?php esc_attr_e('Search', 'custom-box-theme'); ?>">
                    </form>
                </section>

                <section class="blog-sidebar-widget">
                    <h2><?php esc_html_e('Packaging Topics', 'custom-box-theme'); ?></h2>
                    <ul class="blog-topic-list">
                        <?php
                        wp_list_categories(array(
                            'title_li' => '',
                            'number'   => 8,
                        ));
                        ?>
                    </ul>
                </section>

                <section class="blog-sidebar-widget">
                    <h2><?php esc_html_e('Recent Guides', 'custom-box-theme'); ?></h2>
                    <ul class="blog-recent-list">
                        <?php
                        $recent_posts = new WP_Query(array(
                            'post_type'           => 'post',
                            'posts_per_page'      => 4,
                            'post__not_in'        => array(),
                            'ignore_sticky_posts' => true,
                        ));
                        ?>
                        <?php if ($recent_posts->have_posts()) : ?>
                            <?php while ($recent_posts->have_posts()) : $recent_posts->the_post(); ?>
                                <li>
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    <span><?php echo esc_html(get_the_date('M j, Y')); ?></span>
                                </li>
                            <?php endwhile; ?>
                            <?php wp_reset_postdata(); ?>
                        <?php endif; ?>
                    </ul>
                </section>

                <section class="blog-sidebar-cta">
                    <span><?php esc_html_e('Need custom packaging?', 'custom-box-theme'); ?></span>
                    <p><?php esc_html_e('Share your box size, material, artwork, and quantity. Our team will recommend a production-ready solution.', 'custom-box-theme'); ?></p>
                    <a class="btn-primary" href="<?php echo esc_url(home_url('/contact/#quote')); ?>"><?php esc_html_e('Request a Quote', 'custom-box-theme'); ?></a>
                </section>
            </aside>
        </div>
    </section>
</main>

<?php get_footer(); ?>
