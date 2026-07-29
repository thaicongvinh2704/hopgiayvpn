<?php
$shape_images = array(
    'perfume' => custom_box_get_local_image_data('Pyramid-Boxes.webp'),
    'watch'   => custom_box_get_local_image_data('banner-watch-box.webp', 'gift-box2.jpg'),
    'kraft'   => custom_box_get_local_image_data('Hexagon-Boxes.webp'),
    'pizza'   => custom_box_get_local_image_data('Octagon-Boxes.webp'),
    'wine'    => custom_box_get_local_image_data('Dispenser-Boxes.webp'),
    'drawer'  => custom_box_get_local_image_data('Perforated-Boxes.webp'),
    'display' => custom_box_get_local_image_data('Tuck-Top-Boxes_1758880242.webp'),
    'gift'    => custom_box_get_local_image_data('gift-box2.webp'),
    'winegift'=> custom_box_get_local_image_data('RETT-Boxes.webp'),
    'candle'  => custom_box_get_local_image_data('Pillow-Boxes.webp'),
);
?>

<section class="shapes-section">

    <div class="container">

        <!-- HEADER -->
        <div class="shapes-header">
            <h2>Explore <span>Custom Packaging</span> by Product Type</h2>
            <p>
                From perfume and jewelry boxes to food, cosmetic, and gift packaging, each style is made to match your product presentation.
            </p>
        </div>

        <!-- GRID -->
        <div class="shapes-grid">

            <!-- LEFT TALL -->
            <div class="shape-card tall">
                <img src="<?php echo esc_url($shape_images['perfume']['url']); ?>" alt="Perfume Boxes" width="<?php echo esc_attr($shape_images['perfume']['width']); ?>" height="<?php echo esc_attr($shape_images['perfume']['height']); ?>" loading="lazy" decoding="async">
                <div class="shape-label">Perfume Boxes</div>
            </div>

            <!-- CENTER BIG -->
            <div class="shape-card wide">
                <img src="<?php echo esc_url($shape_images['watch']['url']); ?>" alt="Watch Boxes" width="<?php echo esc_attr($shape_images['watch']['width']); ?>" height="<?php echo esc_attr($shape_images['watch']['height']); ?>" loading="lazy" decoding="async">
                <div class="shape-label">Watch Boxes</div>
            </div>

            <!-- RIGHT -->
            <div class="shape-card">
                <img src="<?php echo esc_url($shape_images['kraft']['url']); ?>" alt="Kraft Boxes" width="<?php echo esc_attr($shape_images['kraft']['width']); ?>" height="<?php echo esc_attr($shape_images['kraft']['height']); ?>" loading="lazy" decoding="async">
                <div class="shape-label">Kraft Boxes</div>
            </div>

            <!-- LEFT BOTTOM -->
            <div class="shape-card tall">
                <img src="<?php echo esc_url($shape_images['pizza']['url']); ?>" alt="Pizza Boxes" width="<?php echo esc_attr($shape_images['pizza']['width']); ?>" height="<?php echo esc_attr($shape_images['pizza']['height']); ?>" loading="lazy" decoding="async">
                <div class="shape-label">Pizza Boxes</div>
            </div>

            <!-- SMALL CARDS -->
            <div class="shape-card">
                <img src="<?php echo esc_url($shape_images['wine']['url']); ?>" alt="Wine Boxes" width="<?php echo esc_attr($shape_images['wine']['width']); ?>" height="<?php echo esc_attr($shape_images['wine']['height']); ?>" loading="lazy" decoding="async">
                <div class="shape-label">Wine Boxes</div>
            </div>

            <div class="shape-card">
                <img src="<?php echo esc_url($shape_images['drawer']['url']); ?>" alt="Drawer Boxes" width="<?php echo esc_attr($shape_images['drawer']['width']); ?>" height="<?php echo esc_attr($shape_images['drawer']['height']); ?>" loading="lazy" decoding="async">
                <div class="shape-label">Drawer Boxes</div>
            </div>

            <div class="shape-card">
                <img src="<?php echo esc_url($shape_images['display']['url']); ?>" alt="Product Display Boxes" width="<?php echo esc_attr($shape_images['display']['width']); ?>" height="<?php echo esc_attr($shape_images['display']['height']); ?>" loading="lazy" decoding="async">
                <div class="shape-label">Product Display Boxes</div>
            </div>

            <div class="shape-card">
                <img src="<?php echo esc_url($shape_images['gift']['url']); ?>" alt="Magnetic Gift Boxes" width="<?php echo esc_attr($shape_images['gift']['width']); ?>" height="<?php echo esc_attr($shape_images['gift']['height']); ?>" loading="lazy" decoding="async">
                <div class="shape-label">Magnetic Gift Boxes</div>
            </div>

            <div class="shape-card">
                <img src="<?php echo esc_url($shape_images['winegift']['url']); ?>" alt="Wine Gift Boxes" width="<?php echo esc_attr($shape_images['winegift']['width']); ?>" height="<?php echo esc_attr($shape_images['winegift']['height']); ?>" loading="lazy" decoding="async">
                <div class="shape-label">Wine Gift Boxes</div>
            </div>

            <div class="shape-card">
                <img src="<?php echo esc_url($shape_images['candle']['url']); ?>" alt="Candle Boxes" width="<?php echo esc_attr($shape_images['candle']['width']); ?>" height="<?php echo esc_attr($shape_images['candle']['height']); ?>" loading="lazy" decoding="async">
                <div class="shape-label">Candle Boxes</div>
            </div>

        </div>

        <!-- BUTTON -->
        <div class="shapes-btn">
            <a href="<?php echo esc_url(home_url('/contact/#quote')); ?>" class="btn-primary">Start Customizing Now</a>
        </div>

    </div>

</section>
