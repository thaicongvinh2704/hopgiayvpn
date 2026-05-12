<section class="products-slider">

    <div class="container">

        <?php
        $parent_category = get_term_by('name', 'Custom Packaging Boxes', 'product_cat');
        $slider_categories = array();

        if ($parent_category && !is_wp_error($parent_category)) {
            $slider_categories = get_terms(array(
                'taxonomy'   => 'product_cat',
                'parent'     => $parent_category->term_id,
                'hide_empty' => false,
                'orderby'    => 'term_id',
                'order'      => 'ASC',
                'number'     => 55,
            ));

            if (!empty($slider_categories) && !is_wp_error($slider_categories)) {
                usort($slider_categories, function ($a, $b) {
                    $a_featured = (int) get_term_meta($a->term_id, 'custom_box_category_featured', true);
                    $b_featured = (int) get_term_meta($b->term_id, 'custom_box_category_featured', true);

                    if ($a_featured !== $b_featured) {
                        return $b_featured <=> $a_featured;
                    }

                    return $a->term_id <=> $b->term_id;
                });

                $slider_categories = array_slice($slider_categories, 0, 12);
            }
        }
        ?>

        <div class="slider-wrapper">

            <div class="slider-track">

                <?php foreach ($slider_categories as $category) : ?>
                    <?php
                    $image_id = (int) get_term_meta($category->term_id, 'thumbnail_id', true);
                    if (!$image_id) {
                        $image_id = (int) get_term_meta($category->term_id, 'custom_box_category_image_id', true);
                    }
                    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : get_template_directory_uri() . '/assets/images/custom-cardboard-boxes.webp';
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
