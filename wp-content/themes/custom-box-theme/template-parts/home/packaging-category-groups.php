<?php
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
            array('Custom Paper Boxes', 'custom-paper-boxes', $theme_image_uri . 'Cardboard-Packaging.webp'),
            array('Custom Printed Paper Boxes', 'custom-printed-paper-boxes', $theme_image_uri . 'SBS-Paperboard-Packaging.webp'),
            array('Rigid Boxes', 'rigid-boxes', $theme_image_uri . 'Rigid-Packaging.webp'),
            array('Folding Carton Boxes', 'folding-carton-boxes', $theme_image_uri . 'Tuck-Top-Boxes_1758880242.webp'),
            array('Magnetic Closure Boxes', 'magnetic-closure-boxes', $theme_image_uri . 'gift-box.webp'),
            array('Drawer Boxes', 'drawer-boxes', $theme_image_uri . 'Perforated-Boxes.webp'),
            array('Lid and Base Boxes', 'lid-and-base-boxes', $theme_image_uri . 'RETT-Boxes.webp'),
            array('Paper Tube Packaging', 'paper-tube-packaging', $theme_image_uri . 'Pyramid-Boxes.webp'),
            array('Corrugated Mailer Boxes', 'corrugated-mailer-boxes', $theme_image_uri . 'Corrugated-Packaging.webp'),
        ),
    ),
    array(
        'title' => 'Packaging by Industry',
        'items' => array(
            array('Cosmetic Paper Boxes', 'cosmetic-paper-boxes', $theme_image_uri . 'custom-cosmetic-skincare-packaging-boxes-gray-background.webp'),
            array('Perfume Packaging Boxes', 'perfume-packaging-boxes', $theme_image_uri . 'custom-cosmetic-skincare-packaging-boxes-gray-background.webp'),
            array('Skincare Packaging Boxes', 'skincare-packaging-boxes', $theme_image_uri . 'custom-cosmetic-skincare-packaging-boxes-gray-background.webp'),
            array('Jewelry Paper Boxes', 'jewelry-paper-boxes', $theme_image_uri . 'Rigid-Packaging.webp'),
            array('Gift Paper Boxes', 'gift-paper-boxes', $theme_image_uri . 'gift-box2.webp'),
            array('Chocolate Gift Boxes', 'chocolate-gift-boxes', $theme_image_uri . 'premium-tea-coffee-chocolate-packaging-boxes-gray-background.webp'),
            array('Food Paper Boxes', 'food-paper-boxes', $theme_image_uri . 'premium-tea-coffee-chocolate-packaging-boxes-gray-background.webp'),
            array('Bakery Packaging Boxes', 'bakery-packaging-boxes', $theme_image_uri . 'Takeout-Boxes_1758880241.webp'),
            array('Candle Packaging Boxes', 'candle-packaging-boxes', $theme_image_uri . 'custom-home-lifestyle-product-packaging-boxes-gray-background.webp'),
        ),
    ),
    array(
        'title' => 'Paper Bags & Packaging Add-ons',
        'items' => array(
            array('Paper Bags with Logo', 'paper-bags-with-logo', $theme_image_uri . 'Kraft-Packaging.webp'),
            array('Packaging Accessories', 'packaging-accessories', $theme_image_uri . 'Dispenser-Boxes.webp'),
        ),
    ),
    array(
        'title' => 'Packaging by Industry',
        'items' => array(
            array('Pharmaceutical Packaging Boxes', 'pharmaceutical-packaging-boxes', $theme_image_uri . 'custom-pharmaceutical-medicine-packaging-boxes-gray-background.webp'),
            array('Supplement Packaging Boxes', 'supplement-packaging-boxes', $theme_image_uri . 'custom-supplement-vitamin-packaging-boxes-gray-background.webp'),
            array('Beauty and Skincare Packaging', 'beauty-skincare-packaging', $theme_image_uri . 'custom-cosmetic-skincare-packaging-boxes-gray-background.webp'),
            array('Premium Food and Beverage Packaging', 'premium-food-beverage-packaging', $theme_image_uri . 'premium-tea-coffee-chocolate-packaging-boxes-gray-background.webp'),
            array('Electronics Accessories Packaging', 'electronics-accessories-packaging', $theme_image_uri . 'custom-phone-accessories-packaging-boxes-gray-background.webp'),
            array('Fashion and Sportswear Packaging', 'fashion-sportswear-packaging', $theme_image_uri . 'custom-apparel-packaging-boxes-gray-background.webp'),
            array('Sports Packaging Boxes', 'sports-packaging-boxes', function_exists('custom_box_get_product_category_asset_image_url') ? custom_box_get_product_category_asset_image_url('sports-packaging-boxes') : ''),
            array('Wine and Premium Drink Packaging', 'wine-premium-drink-packaging', $theme_image_uri . 'custom-wine-premium-beverage-packaging-boxes-gray-background.webp'),
            array('Corporate Gift Packaging', 'corporate-gift-packaging', $theme_image_uri . 'custom-corporate-gift-set-packaging-boxes-gray-background.webp'),
            array('Home and Lifestyle Packaging', 'home-lifestyle-packaging', $theme_image_uri . 'custom-home-lifestyle-product-packaging-boxes-gray-background.webp'),
            array('Back-to-School and Stationery Packaging', 'back-to-school-stationery-packaging', $theme_image_uri . 'custom-stationery-school-supplies-packaging-boxes-gray-background.webp'),
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
                    <?php
                    $category_image_url = function_exists('custom_box_get_product_category_asset_image_url') ? custom_box_get_product_category_asset_image_url($category_item[1]) : '';
                    if (!$category_image_url && !empty($category_item[2])) {
                        $category_image_url = $category_item[2];
                    }
                    ?>
                    <a class="home-packaging-category-card" href="<?php echo esc_url($resolve_category_url($category_item[1])); ?>">
                        <span class="home-packaging-category-image <?php echo empty($category_image_url) ? 'is-empty' : ''; ?>">
                            <?php if (!empty($category_image_url)) : ?>
                                <img src="<?php echo esc_url($category_image_url); ?>" alt="<?php echo esc_attr($category_item[0]); ?>" loading="lazy" decoding="async">
                            <?php endif; ?>
                        </span>
                        <span class="home-packaging-category-title"><?php echo esc_html($category_item[0]); ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
