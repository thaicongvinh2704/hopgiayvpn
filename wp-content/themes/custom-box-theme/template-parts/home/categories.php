<section class="categories-section">

    <div class="container">

        <h2 class="section-title">Explore Packaging Categories</h2>

        <p class="section-subtitle">
            Explore popular custom packaging categories designed for product protection, branding, and presentation.
        </p>

        <div class="categories-grid">

            <?php
            $parent_category = get_term_by('name', 'Custom Packaging Boxes', 'product_cat');
            $packaging_categories = array();

            if ($parent_category && !is_wp_error($parent_category)) {
                $packaging_categories = get_terms(array(
                    'taxonomy'   => 'product_cat',
                    'parent'     => $parent_category->term_id,
                    'hide_empty' => false,
                    'orderby'    => 'term_id',
                    'order'      => 'ASC',
                    'number'     => 55,
                ));

                if (!empty($packaging_categories) && !is_wp_error($packaging_categories)) {
                    usort($packaging_categories, function ($a, $b) {
                        $a_featured = (int) get_term_meta($a->term_id, 'custom_box_category_featured', true);
                        $b_featured = (int) get_term_meta($b->term_id, 'custom_box_category_featured', true);

                        if ($a_featured !== $b_featured) {
                            return $b_featured <=> $a_featured;
                        }

                        return $a->term_id <=> $b->term_id;
                    });
                }
            }

            if (!empty($packaging_categories) && !is_wp_error($packaging_categories)) :
                foreach ($packaging_categories as $category) :
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

                <a class="category-card" href="<?php echo esc_url($category_link); ?>">
                    <div class="card-img">
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($category->name); ?>" loading="lazy" decoding="async">
                    </div>

                    <p class="card-title">
                        <?php echo esc_html($category->name); ?>
                    </p>
                </a>

                <?php endforeach; ?>
            <?php else : ?>

                <p class="categories-empty">
                    Please add child categories under Custom Packaging Boxes.
                </p>

            <?php endif; ?>

        </div>

        <!-- BUTTON -->
        <div class="categories-btn">
            <?php if ($parent_category && !is_wp_error($parent_category)) : ?>
                <a href="<?php echo esc_url(get_term_link($parent_category)); ?>" class="btn-primary categories-more">More</a>
            <?php endif; ?>
        </div>

    </div>

</section>
