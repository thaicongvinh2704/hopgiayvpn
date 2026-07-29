<?php
/**
 * Blog listing template.
 */

get_header();

$blog_page_id = (int) get_option('page_for_posts');
$blog_url = $blog_page_id ? get_permalink($blog_page_id) : home_url('/blog/');
$fallback_image = get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp';
$is_blog_page_template = is_page();
$blog_paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
$blog_query = $GLOBALS['wp_query'];
$blog_hero_label = __('VPN Packaging Insights', 'custom-box-theme');
$blog_hero_title = __('Packaging Insights & Custom Box Guides', 'custom-box-theme');
$blog_hero_description = __('Explore practical guides on custom packaging, paper boxes, printing methods, materials, finishing options, and brand-ready production for international buyers.', 'custom-box-theme');
$blog_breadcrumb_current = __('Blog', 'custom-box-theme');

$custom_box_author_fallback_name = __('VPN Packaging Editorial Team', 'custom-box-theme');
$custom_box_author_fallback_bio = __('VPN Packaging Editorial Team shares practical insights about custom paper boxes, rigid boxes, folding cartons, paper bags, printing finishes, materials, and B2B packaging solutions. Our content is developed based on real production experience and customer packaging projects from VPN Packaging Factory in Vietnam.', 'custom-box-theme');

if ($is_blog_page_template) {
    $blog_query = new WP_Query(array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'paged'               => $blog_paged,
        'ignore_sticky_posts' => false,
    ));
}

if (is_category()) {
    $current_category = get_queried_object();

    if ($current_category instanceof WP_Term) {
        $blog_hero_label = __('Packaging Topic', 'custom-box-theme');
        $blog_hero_title = $current_category->name;
        $blog_breadcrumb_current = $current_category->name;
        $blog_hero_description = $current_category->description
            ? $current_category->description
            : sprintf(
                /* translators: %s: category name. */
                __('Explore VPN Packaging guides filed under %s, including practical notes on materials, structure, printing, finishing, and production-ready custom paper boxes.', 'custom-box-theme'),
                $current_category->name
            );
    }
}

if (is_author()) {
    $author = get_queried_object();
    $author_name = $author instanceof WP_User ? trim((string) $author->display_name) : '';
    $author_bio = $author instanceof WP_User ? trim((string) get_the_author_meta('description', $author->ID)) : '';
    $generic_author_names = array('', 'admin', 'administrator');

    if (in_array(strtolower($author_name), $generic_author_names, true)) {
        $author_name = $custom_box_author_fallback_name;
    }

    if ('' === $author_bio) {
        $author_bio = $custom_box_author_fallback_bio;
    }

    $blog_hero_label = __('Packaging Author', 'custom-box-theme');
    $blog_hero_title = $author_name;
    $blog_breadcrumb_current = $author_name;
    $blog_hero_description = $author_bio;
}
?>

<main id="main-content" class="blog-page" data-blog-archive>
    <section class="blog-hero" aria-labelledby="blog-archive-title" data-blog-archive-hero>
        <div class="container">
            <nav class="blog-breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'custom-box-theme'); ?>" data-blog-breadcrumb>
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'custom-box-theme'); ?></a>
                <?php if (!is_home() && !is_page()) : ?>
                    <a href="<?php echo esc_url($blog_url); ?>"><?php esc_html_e('Blog', 'custom-box-theme'); ?></a>
                <?php endif; ?>
                <span aria-current="page"><?php echo esc_html($blog_breadcrumb_current); ?></span>
            </nav>

            <div class="blog-hero-content">
                <span class="blog-eyebrow"><?php echo esc_html($blog_hero_label); ?></span>
                <h1 id="blog-archive-title"><?php echo esc_html($blog_hero_title); ?></h1>
                <p><?php echo esc_html($blog_hero_description); ?></p>
            </div>
        </div>
    </section>

    <section class="blog-listing-section">
        <div class="container blog-layout">
            <div class="blog-main">
                <?php if ($blog_query->have_posts()) : ?>
                    <div class="blog-card-grid" data-blog-card-grid>
                        <?php while ($blog_query->have_posts()) : $blog_query->the_post(); ?>
                            <?php
                            $post_id = get_the_ID();
                            $post_thumbnail_id = get_post_thumbnail_id($post_id);
                            $post_thumbnail_path = $post_thumbnail_id ? get_attached_file($post_thumbnail_id) : '';

                            if ($post_thumbnail_id && (!$post_thumbnail_path || !is_file($post_thumbnail_path))) {
                                $post_thumbnail_id = 0;
                            }

                            $card_title_id = 'blog-card-title-' . $post_id;
                            ?>
                            <article
                                id="post-<?php the_ID(); ?>"
                                <?php post_class('blog-card'); ?>
                                aria-labelledby="<?php echo esc_attr($card_title_id); ?>"
                                data-blog-card
                            >
                                <figure class="blog-card-image" aria-hidden="true" data-blog-card-media>
                                    <?php if ($post_thumbnail_id) : ?>
                                        <?php
                                        echo get_the_post_thumbnail(
                                            $post_id,
                                            'medium_large',
                                            array(
                                                'alt'      => '',
                                                'loading'  => 'lazy',
                                                'decoding' => 'async',
                                                'sizes'    => '(max-width: 767px) 100vw, (max-width: 1024px) 50vw, 520px',
                                            )
                                        );
                                        ?>
                                    <?php else : ?>
                                        <img
                                            src="<?php echo esc_url($fallback_image); ?>"
                                            alt=""
                                            width="506"
                                            height="277"
                                            loading="lazy"
                                            decoding="async"
                                            sizes="(max-width: 767px) 100vw, (max-width: 1024px) 50vw, 520px"
                                        >
                                    <?php endif; ?>
                                </figure>

                                <div class="blog-card-body">
                                    <div class="blog-card-meta">
                                        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('M j, Y')); ?></time>
                                        <?php $primary_category = get_the_category(); ?>
                                        <?php if (!empty($primary_category)) : ?>
                                            <a href="<?php echo esc_url(get_category_link($primary_category[0])); ?>"><?php echo esc_html($primary_category[0]->name); ?></a>
                                        <?php endif; ?>
                                    </div>

                                    <h2 id="<?php echo esc_attr($card_title_id); ?>">
                                        <a class="blog-card-primary-link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h2>
                                    <p class="blog-card-excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
                                    <span class="blog-read-more" aria-hidden="true">
                                        <?php esc_html_e('Read More', 'custom-box-theme'); ?>
                                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                    </span>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>

                    <nav class="blog-pagination" aria-label="<?php esc_attr_e('Blog pagination', 'custom-box-theme'); ?>">
                        <?php
                        echo wp_kses_post(paginate_links(array(
                            'current'            => $blog_paged,
                            'total'              => (int) $blog_query->max_num_pages,
                            'prev_text'          => '<i class="fas fa-chevron-left" aria-hidden="true"></i><span class="screen-reader-text">' . esc_html__('Previous blog page', 'custom-box-theme') . '</span>',
                            'next_text'          => '<span class="screen-reader-text">' . esc_html__('Next blog page', 'custom-box-theme') . '</span><i class="fas fa-chevron-right" aria-hidden="true"></i>',
                            'before_page_number' => '<span class="screen-reader-text">' . esc_html__('Page', 'custom-box-theme') . ' </span>',
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

            <aside class="blog-sidebar" aria-label="<?php esc_attr_e('Blog sidebar', 'custom-box-theme'); ?>" data-blog-sidebar>
                <section class="blog-sidebar-widget blog-search-widget">
                    <h2><?php esc_html_e('Search Insights', 'custom-box-theme'); ?></h2>
                    <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                        <label>
                            <span class="screen-reader-text"><?php esc_html_e('Search for:', 'custom-box-theme'); ?></span>
                            <input type="search" class="search-field" placeholder="<?php esc_attr_e('Search packaging guides...', 'custom-box-theme'); ?>" value="<?php echo esc_attr(get_search_query()); ?>" name="s">
                        </label>
                        <input
                            id="blog-search-submit"
                            type="submit"
                            class="search-submit"
                            name="blog_search_submit"
                            value="<?php esc_attr_e('Search', 'custom-box-theme'); ?>"
                            aria-label="<?php esc_attr_e('Search packaging guides', 'custom-box-theme'); ?>"
                        >
                    </form>
                </section>

                <details
                    class="blog-sidebar-widget blog-sidebar-disclosure"
                    open
                    data-mobile-disclosure
                    data-mobile-default="closed"
                    data-desktop-default="open"
                    data-disclosure-name="packaging-topics"
                >
                    <summary class="blog-sidebar-disclosure-summary"><span role="heading" aria-level="2"><?php esc_html_e('Packaging Topics', 'custom-box-theme'); ?></span><span class="blog-sidebar-disclosure-icon" aria-hidden="true"></span></summary>
                    <ul class="blog-topic-list">
                        <?php
                        wp_list_categories(array(
                            'title_li' => '',
                            'number'   => 8,
                        ));
                        ?>
                    </ul>
                </details>

                <details
                    class="blog-sidebar-widget blog-sidebar-disclosure"
                    open
                    data-mobile-disclosure
                    data-mobile-default="closed"
                    data-desktop-default="open"
                    data-disclosure-name="recent-guides"
                >
                    <summary class="blog-sidebar-disclosure-summary"><span role="heading" aria-level="2"><?php esc_html_e('Recent Guides', 'custom-box-theme'); ?></span><span class="blog-sidebar-disclosure-icon" aria-hidden="true"></span></summary>
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
                </details>

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
