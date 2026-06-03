<?php
$uploads_2026_05_uri = content_url('/uploads/2026/05/');
$old_category_image_uri = $uploads_2026_05_uri;
$theme_image_uri = get_template_directory_uri() . '/assets/images/';

$resolve_category_url = function ($slug) {
    $term = get_term_by('slug', $slug, 'product_cat');
    if ($term && !is_wp_error($term)) {
        $term_link = get_term_link($term);
        if (!is_wp_error($term_link)) {
            return $term_link;
        }
    }

    $url = home_url('/packaging/' . trim($slug, '/') . '/');

    if (url_to_postid($url)) {
        return $url;
    }

    return home_url('/products/');
};

$category_groups = array(
    array(
        'title' => 'Paper Box Types',
        'items' => array(
            array('Custom Paper Boxes', 'custom-paper-boxes', $old_category_image_uri . '29.jpg'),
            array('Custom Printed Paper Boxes', 'custom-printed-paper-boxes', $old_category_image_uri . '20.jpg'),
            array('Rigid Boxes', 'rigid-boxes', $old_category_image_uri . '13.jpg'),
            array('Folding Carton Boxes', 'folding-carton-boxes', $old_category_image_uri . 'folding-carton-boxes.webp'),
            array('Magnetic Closure Boxes', 'magnetic-closure-boxes', $old_category_image_uri . 'red-floral-mooncake-gift-packaging-box.jpeg'),
            array('Drawer Boxes', 'drawer-boxes', $old_category_image_uri . '15.jpg'),
            array('Lid and Base Boxes', 'lid-and-base-boxes', $old_category_image_uri . 'lid-and-base-boxes.webp'),
            array('Paper Tube Packaging', 'paper-tube-packaging', $old_category_image_uri . '12.jpg'),
            array('Corrugated Mailer Boxes', 'corrugated-mailer-boxes', $old_category_image_uri . 'orange-corrugated-mailer-box-768x768.jpeg'),
        ),
    ),
    array(
        'title' => 'Packaging by Industry',
        'items' => array(
            array('Cosmetic Paper Boxes', 'cosmetic-paper-boxes', $old_category_image_uri . 'blue-cosmetic-set-packaging-box-open-1024x930.png'),
            array('Perfume Packaging Boxes', 'perfume-packaging-boxes', $old_category_image_uri . '26.jpg'),
            array('Skincare Packaging Boxes', 'skincare-packaging-boxes', $old_category_image_uri . '40.jpg'),
            array('Jewelry Paper Boxes', 'jewelry-paper-boxes', $old_category_image_uri . 'watch-box.jpg'),
            array('Gift Paper Boxes', 'gift-paper-boxes', $old_category_image_uri . '27.jpg'),
            array('Chocolate Gift Boxes', 'chocolate-gift-boxes', $old_category_image_uri . '19.jpg'),
            array('Food Paper Boxes', 'food-paper-boxes', $old_category_image_uri . '32.jpg'),
            array('Bakery Packaging Boxes', 'bakery-packaging-boxes', $old_category_image_uri . '31.jpg'),
            array('Candle Packaging Boxes', 'candle-packaging-boxes', $old_category_image_uri . '17.jpg'),
        ),
    ),
    array(
        'title' => 'Paper Bags & Packaging Add-ons',
        'items' => array(
            array('Paper Bags with Logo', 'paper-bags-with-logo', $old_category_image_uri . '35.jpg'),
            array('Packaging Accessories', 'packaging-accessories', $old_category_image_uri . '33.jpg'),
        ),
    ),
    array(
        'title' => 'Packaging by Industry',
        'items' => array(
            array('Pharmaceutical Packaging Boxes', 'pharmaceutical-packaging-boxes', $theme_image_uri . 'custom-pharmaceutical-medicine-packaging-boxes.webp'),
            array('Supplement Packaging Boxes', 'supplement-packaging-boxes', $theme_image_uri . 'custom-supplement-vitamin-packaging-boxes.webp'),
            array('Beauty and Skincare Packaging', 'beauty-skincare-packaging', $theme_image_uri . 'custom-cosmetic-skincare-packaging-boxes.webp'),
            array('Premium Food and Beverage Packaging', 'premium-food-beverage-packaging', $theme_image_uri . 'premium-tea-coffee-chocolate-packaging-boxes.webp'),
            array('Electronics Accessories Packaging', 'electronics-accessories-packaging', $theme_image_uri . 'custom-phone-accessories-packaging-boxes.webp'),
            array('Fashion and Sportswear Packaging', 'fashion-sportswear-packaging', $theme_image_uri . 'category-fasion.webp'),
            array('Wine and Premium Drink Packaging', 'wine-premium-drink-packaging', $theme_image_uri . 'custom-wine-premium-beverage-packaging-boxes.webp'),
            array('Corporate Gift Packaging', 'corporate-gift-packaging', $theme_image_uri . 'custom-corporate-gift-set-packaging-boxes.webp'),
            array('Home and Lifestyle Packaging', 'home-lifestyle-packaging', $theme_image_uri . 'custom-home-lifestyle-product-packaging-boxes.webp'),
            array('Back-to-School and Stationery Packaging', 'back-to-school-stationery-packaging', $theme_image_uri . 'custom-stationery-school-supplies-packaging-boxes.webp'),
        ),
    ),
);
?>

<section class="home-packaging-category-section" id="packaging-categories">
    <div class="container">
        <div class="home-packaging-category-head">
            <span>Manufacturing Range</span>
            <h2>Explore Our Paper Packaging Categories</h2>
            <p>Choose by box structure, industry use, or packaging add-on, then customize size, material, printing, finishing, inserts, and order quantity.</p>
        </div>

        <div class="home-packaging-category-cards">
            <?php foreach ($category_groups as $category_group) : ?>
                <?php if (!empty($category_group['hidden'])) : ?>
                    <?php continue; ?>
                <?php endif; ?>
                <?php foreach ($category_group['items'] as $category_item) : ?>
                    <a class="home-packaging-category-card" href="<?php echo esc_url($resolve_category_url($category_item[1])); ?>">
                        <span class="home-packaging-category-image <?php echo empty($category_item[2]) ? 'is-empty' : ''; ?>">
                            <?php if (!empty($category_item[2])) : ?>
                                <img src="<?php echo esc_url($category_item[2]); ?>" alt="<?php echo esc_attr($category_item[0]); ?>" loading="lazy" decoding="async">
                            <?php endif; ?>
                        </span>
                        <span class="home-packaging-category-title"><?php echo esc_html($category_item[0]); ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
