<?php
/**
 * Benefits Section
 */

$benefits_image = custom_box_get_local_image_data('gift-box2.webp');
?>

<section class="benefits-section">
  <div class="benefits-container">

    <!-- HEADER -->
    <div class="benefits-header">
      <h2>What You’ll Get with Us</h2>
      <p>Every box we make is a promise of quality, precision, and customer satisfaction.</p>
    </div>

    <!-- GRID -->
    <div class="benefits-grid">

      <!-- LEFT -->
      <div class="benefits-left">
        <ul class="benefits-list">
          <li>Free Expert Design Support</li>
          <li>Low Minimum Order Quantities</li>
          <li>Competitive Wholesale Prices</li>
          <li>Fast Nationwide Delivery</li>
          <li>100% Customizable Options</li>
          <li>Sustainable Eco Packaging</li>
          <li>Premium Printing & Finishes</li>
          <li>Dedicated Support Team</li>
        </ul>
      </div>

      <!-- RIGHT -->
      <div class="benefits-right">
        <img 
          src="<?php echo esc_url($benefits_image['url']); ?>"
          alt="Custom Box"
          width="<?php echo esc_attr($benefits_image['width']); ?>"
          height="<?php echo esc_attr($benefits_image['height']); ?>"
          loading="lazy"
          decoding="async"
        >
      </div>

    </div>

    <!-- BUTTON -->
    <div class="benefits-btn">
      <a href="<?php echo esc_url(home_url('/contact/#quote')); ?>">Let's Talk Today</a>
    </div>

  </div>
</section>
