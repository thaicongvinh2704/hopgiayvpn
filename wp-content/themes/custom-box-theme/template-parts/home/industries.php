<section class="products-slider">

    <div class="container">

        <?php
        $slider_categories = array();

        if (function_exists('custom_box_get_all_categories_menu_columns')) {
            foreach (custom_box_get_all_categories_menu_columns() as $column) {
                if (empty($column['terms']) || !is_array($column['terms'])) {
                    continue;
                }

                foreach ($column['terms'] as $category) {
                    $slider_categories[] = $category;
                }
            }
        }
        ?>

        <div class="slider-wrapper">

            <div class="slider-track">

                <?php foreach ($slider_categories as $category) : ?>
                    <?php
                    $image_url = function_exists('custom_box_get_product_category_asset_image_url') ? custom_box_get_product_category_asset_image_url($category) : '';

                    if (!$image_url) {
                        $image_id = (int) get_term_meta($category->term_id, 'thumbnail_id', true);
                        if (!$image_id) {
                            $image_id = (int) get_term_meta($category->term_id, 'custom_box_category_image_id', true);
                        }
                        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : get_template_directory_uri() . '/assets/images/Cardboard-Packaging.webp';
                    }

                    $category_link = get_term_link($category);

                    if (is_wp_error($category_link)) {
                        continue;
                    }
                    ?>
                    <div class="slide">
                        <a class="card product-slider-card" href="<?php echo esc_url($category_link); ?>">

                            <div class="product-slider-img">
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($category->name); ?>" loading="lazy" decoding="async">
                            </div>

                            <div class="card-label">
                                <?php echo esc_html($category->name); ?>
                            </div>

                        </a>
                    </div>
                <?php endforeach; ?>

            </div>

        </div>

        <div class="slider-dots"></div>

    </div>

</section>
