<?php
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

$category_groups = function_exists('custom_box_get_home_packaging_category_groups')
    ? custom_box_get_home_packaging_category_groups()
    : array();
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
