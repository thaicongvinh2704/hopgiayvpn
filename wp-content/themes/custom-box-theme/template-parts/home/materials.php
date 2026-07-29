<?php
$material_images = array(
    'rigid'      => custom_box_get_local_image_data('Rigid-Packaging.webp'),
    'paperboard' => custom_box_get_local_image_data('SBS-Paperboard-Packaging.webp'),
    'kraft'      => custom_box_get_local_image_data('Kraft-Packaging.webp'),
    'cardboard'  => custom_box_get_local_image_data('Cardboard-Packaging.webp'),
    'corrugated' => custom_box_get_local_image_data('Corrugated-Packaging.webp'),
);
?>

<section class="materials-section">

    <div class="container">

        <!-- HEADER -->
        <div class="materials-header">

            <div class="left">
                <h2>
                    Premium Packaging <span>Materials Offer</span>
                </h2>
                <p>Durable, eco-conscious, and customizable to your brand.</p>
            </div>

            <div class="right">
                <p>
                    We use high-quality paper-based materials engineered for
                    strength, sustainability, and luxury — crafted for protection,
                    presentation, and printing excellence.
                </p>
            </div>

        </div>

        <!-- GRID -->
        <div class="materials-grid">

            <!-- BIG LEFT -->
            <div class="material-card big">
                <img src="<?php echo esc_url($material_images['rigid']['url']); ?>" alt="Rigid packaging material" width="<?php echo esc_attr($material_images['rigid']['width']); ?>" height="<?php echo esc_attr($material_images['rigid']['height']); ?>" loading="lazy" decoding="async">
                <div class="card-overlay">
                    <strong>Rigid</strong>
                    <p>Luxury feel premium impact</p>
                </div>
            </div>

            <!-- TOP CENTER -->
            <div class="material-card wide">
                <img src="<?php echo esc_url($material_images['paperboard']['url']); ?>" alt="SBS paperboard packaging material" width="<?php echo esc_attr($material_images['paperboard']['width']); ?>" height="<?php echo esc_attr($material_images['paperboard']['height']); ?>" loading="lazy" decoding="async">
                <div class="card-overlay">
                    <strong>SBS Paperboard</strong>
                    <p>Perfect for high-quality print finishes</p>
                </div>
            </div>

            <!-- RIGHT -->
            <div class="material-card tall">
                <img src="<?php echo esc_url($material_images['kraft']['url']); ?>" alt="Kraft packaging material" width="<?php echo esc_attr($material_images['kraft']['width']); ?>" height="<?php echo esc_attr($material_images['kraft']['height']); ?>" loading="lazy" decoding="async">
                <div class="card-overlay">
                    <strong>Kraft</strong>
                    <p>100% recyclable & natural appeal</p>
                </div>
            </div>

            <!-- BOTTOM LEFT -->
            <div class="material-card">
                <img src="<?php echo esc_url($material_images['cardboard']['url']); ?>" alt="Cardboard packaging material" width="<?php echo esc_attr($material_images['cardboard']['width']); ?>" height="<?php echo esc_attr($material_images['cardboard']['height']); ?>" loading="lazy" decoding="async">
                <div class="card-overlay">
                    <strong>Cardboard</strong>
                    <p>Lightweight & Economical</p>
                </div>
            </div>

            <!-- BOTTOM RIGHT -->
            <div class="material-card wide">
                <img src="<?php echo esc_url($material_images['corrugated']['url']); ?>" alt="Corrugated packaging material" width="<?php echo esc_attr($material_images['corrugated']['width']); ?>" height="<?php echo esc_attr($material_images['corrugated']['height']); ?>" loading="lazy" decoding="async">
                <div class="card-overlay">
                    <strong>Corrugated</strong>
                    <p>Ideal for shipping & eCommerce</p>
                </div>
            </div>

        </div>

    </div>

</section>
