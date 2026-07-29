<?php
/**
 * SEO-focused single blog post template.
 */

get_header();

while (have_posts()) :
    the_post();

    $article_data = function_exists('custom_box_build_blog_article_content')
        ? custom_box_build_blog_article_content(get_the_content())
        : array(
            'content' => apply_filters('the_content', get_the_content()),
            'toc'     => array(),
        );

    $blog_context = array(
        'article_content' => $article_data['content'],
        'toc'             => $article_data['toc'],
    );
    ?>

    <main id="main-content" class="blog-page blog-single-page blog-seo-template" aria-labelledby="article-title" data-blog-article>
        <?php get_template_part('template-parts/blog/hero', null, $blog_context); ?>

        <section class="blog-article-section" aria-label="<?php esc_attr_e('Article content', 'custom-box-theme'); ?>" data-article-section>
            <div class="container blog-article-layout">
                <article id="article-content" <?php post_class('blog-article'); ?> tabindex="-1" aria-labelledby="article-title">
                    <div class="blog-content blog-article-content" data-article-content>
                        <?php echo wp_kses_post($blog_context['article_content']); ?>
                    </div>

                    <?php
                    wp_link_pages(array(
                        'before' => '<nav class="blog-post-pages" aria-label="' . esc_attr__('Article pages', 'custom-box-theme') . '">' . esc_html__('Pages:', 'custom-box-theme'),
                        'after'  => '</nav>',
                    ));
                    ?>

                    <?php get_template_part('template-parts/blog/author-box'); ?>
                </article>
            </div>
        </section>

        <?php get_template_part('template-parts/blog/related', null, $blog_context); ?>
    </main>

<?php endwhile; ?>

<?php get_footer(); ?>
