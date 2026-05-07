<?php get_header(); ?>

<main class="site-main">
    <div class="container">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <h1><?php the_title(); ?></h1>

                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else : ?>
            <article class="no-results">
                <h1><?php esc_html_e('Nothing Found', 'custom-box-theme'); ?></h1>
                <p><?php esc_html_e('No content is available yet.', 'custom-box-theme'); ?></p>
            </article>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
