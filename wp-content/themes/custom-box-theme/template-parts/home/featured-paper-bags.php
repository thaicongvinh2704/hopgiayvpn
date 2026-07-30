<?php
$featured_paper_bags = array(
    array(
        'slug'  => 'custom-birthday-paper-gift-bag-with-present-print',
        'title' => 'Custom Birthday Paper Gift Bag',
    ),
    array(
        'slug'  => 'custom-kraft-paper-bag-for-supplement-packaging',
        'title' => 'Custom Kraft Supplement Paper Bag',
    ),
    array(
        'slug'  => 'custom-luxury-paper-gift-bag-with-ribbon-handles',
        'title' => 'Luxury Paper Gift Bag with Ribbon Handles',
    ),
    array(
        'slug'  => 'custom-white-paper-shopping-bag-with-brown-rope-handles',
        'title' => 'White Paper Shopping Bag with Rope Handles',
    ),
    array(
        'slug'  => 'custom-lime-green-paper-shopping-bag-with-rope-handles',
        'title' => 'Lime Green Paper Shopping Bag',
    ),
    array(
        'slug'  => 'custom-luxury-gift-box-with-paper-bag',
        'title' => 'Custom Luxury Gift Box with Paper Bag',
    ),
);

$fallback_image_url = get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp';
?>

<section class="featured-paper-bags-section" aria-labelledby="featured-paper-bags-title" data-featured-paper-bags>
    <div class="container">
        <div class="featured-paper-bags-head">
            <span>Selected Paper Bags</span>
            <h2 id="featured-paper-bags-title">Featured Paper Bag Products</h2>
            <p>Discover a selection of our standout paper bags, from kraft and luxury gift bags to branded retail shopping bags for supplements, gifts, and lifestyle brands.</p>
        </div>

        <div class="featured-paper-bags-grid">
            <?php foreach ($featured_paper_bags as $featured_paper_bag) : ?>
                <?php
                $product = get_page_by_path($featured_paper_bag['slug'], OBJECT, 'product');
                $product_url = $product instanceof WP_Post && 'publish' === $product->post_status
                    ? get_permalink($product)
                    : home_url('/product/' . $featured_paper_bag['slug'] . '/');
                $thumbnail_id = $product instanceof WP_Post ? get_post_thumbnail_id($product) : 0;
                ?>
                <a class="featured-paper-bag-card" href="<?php echo esc_url($product_url); ?>">
                    <span class="featured-paper-bag-image">
                        <?php if ($thumbnail_id) : ?>
                            <?php
                            echo wp_get_attachment_image(
                                $thumbnail_id,
                                'medium_large',
                                false,
                                array(
                                    'alt'      => $featured_paper_bag['title'],
                                    'loading'  => 'lazy',
                                    'decoding' => 'async',
                                )
                            );
                            ?>
                        <?php else : ?>
                            <img
                                src="<?php echo esc_url($fallback_image_url); ?>"
                                alt="<?php echo esc_attr($featured_paper_bag['title']); ?>"
                                loading="lazy"
                                decoding="async"
                                width="506"
                                height="277"
                            >
                        <?php endif; ?>
                    </span>
                    <span class="featured-paper-bag-title"><?php echo esc_html($featured_paper_bag['title']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
