<?php
$featured_paper_bags = array(
    array(
        'slug'  => 'custom-printed-flat-bread-paper-bags',
        'title' => 'Custom Printed Flat Bread Paper Bags',
    ),
    array(
        'slug'  => 'custom-kraft-side-gusset-bread-bags',
        'title' => 'Custom Kraft Side Gusset Bread Bags',
    ),
    array(
        'slug'  => 'custom-printed-baguette-paper-bags',
        'title' => 'Custom Printed Baguette Paper Bags',
    ),
    array(
        'slug'  => 'custom-square-bottom-bakery-paper-bags',
        'title' => 'Custom Square Bottom Bakery Paper Bags',
    ),
    array(
        'slug'  => 'custom-paper-bread-bags-with-window',
        'title' => 'Custom Paper Bread Bags With Window',
    ),
    array(
        'slug'  => 'custom-greaseproof-bakery-paper-bags',
        'title' => 'Custom Greaseproof Bakery Paper Bags',
    ),
    array(
        'slug'  => 'custom-die-cut-handle-bakery-paper-bags',
        'title' => 'Custom Die Cut Handle Bakery Paper Bags',
    ),
    array(
        'slug'  => 'custom-twisted-handle-bakery-paper-bags',
        'title' => 'Custom Twisted Handle Bakery Paper Bags',
    ),
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

        <div class="featured-paper-bags-carousel" data-featured-paper-bags-carousel>
            <div class="featured-paper-bags-viewport">
                <div class="featured-paper-bags-track">
            <?php foreach ($featured_paper_bags as $featured_paper_bag) : ?>
                <?php
                $product = get_page_by_path($featured_paper_bag['slug'], OBJECT, 'product');
                $product_url = $product instanceof WP_Post && 'publish' === $product->post_status
                    ? get_permalink($product)
                    : home_url('/product/' . $featured_paper_bag['slug'] . '/');
                $thumbnail_id = $product instanceof WP_Post ? get_post_thumbnail_id($product) : 0;
                ?>
                <div class="featured-paper-bags-slide">
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
                </div>
            <?php endforeach; ?>
                </div>
            </div>
            <div class="featured-paper-bags-dots" aria-label="Featured paper bag products navigation"></div>
        </div>
    </div>
</section>
