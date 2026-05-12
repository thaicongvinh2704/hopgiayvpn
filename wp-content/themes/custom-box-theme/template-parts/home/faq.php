<section class="faq-section">

    <div class="container faq-wrapper">

        <!-- LEFT IMAGE -->
        <div class="faq-left">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/Faq-Section-Image.jpg" alt="Packaging support team reviewing custom box questions" loading="lazy" decoding="async">
        </div>

        <!-- RIGHT FAQ -->
        <div class="faq-right">

            <div class="faq-scroll">

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

                foreach($faqs as $index => $faq):
                ?>

                <div class="faq-item <?php echo $index == 0 ? 'active' : ''; ?>">
                    
                    <div class="faq-question">
                        <span class="faq-num"><?php echo $faq[0]; ?></span>
                        <h4><?php echo $faq[1]; ?></h4>
                        <span class="faq-toggle">⌃</span>
                    </div>

                    <div class="faq-answer">
                        <p><?php echo $faq[2]; ?></p>
                    </div>

                </div>

                <?php endforeach; ?>

            </div>

        </div>

    </div>

    <div class="faq-btn">
        <a href="#">More FAQs</a>
    </div>

</section>
