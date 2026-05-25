<section class="testimonials">

    <div class="container">

        <h2>Real Customer <span>Success</span> Stories</h2>
        <p class="sub">Real packaging results from brands we support</p>

        <?php
        $reviews = array(
            array(
                'image'   => 'feedback1.webp',
                'alt'     => 'Custom vape packaging boxes produced for Nova Vape Supply Co.',
                'title'   => 'Premium Custom Vape Packaging With Excellent Print Quality',
                'content' => 'VPN delivered high-quality custom vape packaging boxes with sharp printing, luxury foil finishing, and durable materials. Their team helped us achieve a premium retail look while maintaining fast production and reliable communication throughout the project. The final custom vape boxes exceeded our expectations in both appearance and quality.',
                'name'    => 'Daniel Carter',
                'company' => 'Nova Vape Supply Co.',
            ),
            array(
                'image'   => 'feedback2.webp',
                'alt'     => 'Luxury custom wine packaging boxes with premium finishing',
                'title'   => 'Luxury Wine Packaging Boxes With Elegant Premium Finishing',
                'content' => 'VPN produced exceptional custom wine packaging boxes with a refined luxury appearance and outstanding structural quality. The rigid wine box design, smooth matte texture, and gold foil logo finishing gave our products a sophisticated presentation perfect for gifting and retail display. Their team provided excellent support throughout the custom wine box production process, from material selection to final finishing details. The packaging arrived securely packed, visually stunning, and fully aligned with our premium branding goals.',
                'name'    => 'Michael Laurent',
                'company' => 'Vintage Cellars Group',
            ),
            array(
                'image'   => 'feedback3.webp',
                'alt'     => 'Premium rigid wine gift boxes with luxury presentation',
                'title'   => 'Premium Rigid Wine Gift Boxes With Luxury Presentation',
                'content' => 'VPN created beautiful custom rigid wine gift boxes that perfectly reflected our premium brand image. The magnetic closure, elegant matte finish, and precision foil logo printing gave the packaging a refined luxury appearance ideal for corporate gifting and retail presentation. The quality of the custom wine box packaging exceeded our expectations, especially the protective insert structure and overall craftsmanship. Their production team delivered on time with excellent communication throughout the entire process.',
                'name'    => 'Sophia Bennett',
                'company' => 'Royal Estate Wines',
            ),
        );
        ?>

        <div class="testi-wrapper">

            <div class="testi-track" id="testiTrack">

                <?php foreach ($reviews as $review) : ?>
                    <div class="testi-slide">
                        <div class="testi-card">

                            <div class="testi-img">
                                <img
                                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/' . $review['image']); ?>"
                                    alt="<?php echo esc_attr($review['alt']); ?>"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </div>

                            <div class="testi-text">
                                <div class="stars" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>

                                <h4>&ldquo;<?php echo esc_html($review['title']); ?>&rdquo;</h4>

                                <p><?php echo esc_html($review['content']); ?></p>

                                <div class="author">
                                    <strong><?php echo esc_html($review['name']); ?></strong>
                                    <span><?php echo esc_html($review['company']); ?></span>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

            </div>

        </div>

        <div class="testi-dots" id="testiDots"></div>

    </div>

</section>
