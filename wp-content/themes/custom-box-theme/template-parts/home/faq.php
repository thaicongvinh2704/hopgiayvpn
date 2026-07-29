<section class="faq-section" id="faq" aria-labelledby="home-faq-title" data-home-faq>

    <div class="container faq-wrapper">

        <!-- LEFT IMAGE -->
        <div class="faq-left">
            <img
                src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/Faq-Section-Image.jpg'); ?>"
                alt="Packaging support team reviewing custom box questions"
                width="512"
                height="512"
                loading="lazy"
                decoding="async"
            >
        </div>

        <!-- RIGHT FAQ -->
        <div class="faq-right">
            <h2 id="home-faq-title">Frequently Asked Questions</h2>

            <div class="faq-scroll" data-faq-list>

                <!-- ITEM -->
                <?php 
                $faqs = [
                    ["01","What are your minimum order quantities (MOQs) and options for small brands?","We support low-MOQ solutions via digital short runs and hybrid production: many SKUs are available from small batches (digital prints) up to larger offset runs for high-volume orders. Typical short-run digital MOQs start in the low dozens for simple mailers; offset and specialized structural runs commonly require larger minimums. We’ll recommend the lowest-risk production path that meets both quality and budget."],
                    ["02","How long are lead times from proof to delivery?","Typical lead times: digital short runs (proof → production → ship) 7–14 business days; offset prints and structural jobs 10–25 business days depending on complexity; expedited lanes and in-stock sample options can compress timelines to 3–5 business days for urgent launches. Exact timing depends on finishing, coatings, and shipping destination — we provide a clear timeline in every quote."],
                    ["03","What materials and finishing options do you support?","Material options include corrugated (single/double-wall), kraft, SBS folding cartons, rigid set-up boxes, and coated stocks for luxury feel. Finishes: matt/gloss lamination, aqueous, soft-touch, UV spot, foil stamping, emboss/deboss, window patches, and specialty varnishes. Protective inserts: die-cut chipboard, foam, or corrugated partitions. We recommend the smallest environmental footprint that still meets protection and branding needs."],
                    ["04","Are your boxes recyclable and can you support sustainability claims?","Yes — we offer recyclable and recycled-content materials (PCR), water-based inks, and compostable options where appropriate. For clients requiring claims, we provide material specs, chain-of-custody documentation, and guidance on accurate, verifiable labeling (FSC, recycled content, recyclability instructions) to avoid greenwashing and ensure compliance with retailer and regulatory requirements."],
                    ["05","Do you provide dielines, design help, and pre-production samples?","Absolutely. We provide printable dielines, design templates, color-managed PDFs, and structural proofs. Free design assistance is included for production orders; we offer digital proofs and physical sample packs (one-offs or small batches) so teams can test fit, finish, and durability before full runs. File types supported: AI, PDF/X-1a, EPS; we manage color using industry-standard workflows."],
                    ["06","Can you print my logo and brand colors accurately?","Yes. We specialize in custom printed boxes with high-fidelity color reproduction. Using CMYK, Pantone matching, and advanced printing methods, we ensure your brand’s colors and logo appear sharp, vibrant, and consistent across every order."],
                    ["07","What finishing and customization options are available?","Choose from premium finishes such as gloss or matte lamination, soft-touch coating, embossing, debossing, foil stamping, spot UV, and window cutouts. We also provide custom inserts, dividers, and handles to enhance both protection and presentation."],
                    ["08","Do you offer eco-friendly packaging solutions?","Absolutely. Our eco-friendly custom packaging options include recyclable kraft, biodegradable materials, soy-based inks, and reduced-waste production. We also support brands in making verifiable sustainability claims with accurate recycling instructions."],
                    ["09","Can I order a sample before placing a bulk order?","Yes, we provide pre-production samples including digital proofs and physical prototypes. This allows you to test the structure, print quality, and finish before committing to larger runs, ensuring 100% satisfaction."]
                ];

                foreach ($faqs as $index => $faq) :
                    $question_id = 'home-faq-question-' . ($index + 1);
                    $answer_id = 'home-faq-answer-' . ($index + 1);
                    $is_open = 0 === $index;
                ?>

                <div class="faq-item<?php echo $is_open ? ' active' : ''; ?>" data-faq-item>
                    
                    <button
                        class="faq-question"
                        id="<?php echo esc_attr($question_id); ?>"
                        type="button"
                        aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
                        aria-controls="<?php echo esc_attr($answer_id); ?>"
                    >
                        <span class="faq-num" aria-hidden="true"><?php echo esc_html($faq[0]); ?></span>
                        <span class="faq-question-text"><?php echo esc_html($faq[1]); ?></span>
                        <span class="faq-toggle" aria-hidden="true">&#8963;</span>
                    </button>

                    <div
                        class="faq-answer"
                        id="<?php echo esc_attr($answer_id); ?>"
                        role="region"
                        aria-labelledby="<?php echo esc_attr($question_id); ?>"
                        <?php if (!$is_open) : ?>hidden<?php endif; ?>
                    >
                        <p><?php echo esc_html($faq[2]); ?></p>
                    </div>

                </div>

                <?php endforeach; ?>

            </div>

        </div>

    </div>

    <div class="faq-btn">
        <a href="<?php echo esc_url(home_url('/contact/#quote')); ?>">Ask a Custom Packaging Question</a>
    </div>

</section>
