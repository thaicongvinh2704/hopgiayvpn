<?php
/**
 * Catalog frequently asked questions.
 */

defined('ABSPATH') || exit;

$faqs = array(
    array(
        'question' => __('What types of custom paper boxes do you manufacture?', 'custom-box-theme'),
        'answer' => __('VPN Packaging Factory manufactures custom rigid boxes, folding carton boxes, cosmetic paper boxes, food packaging boxes, luxury gift boxes, jewelry and watch boxes, wine and beverage boxes, and paper shopping bags for B2B customers.', 'custom-box-theme'),
    ),
    array(
        'question' => __('What is your minimum order quantity?', 'custom-box-theme'),
        'answer' => __('Our production supports bulk paper box orders starting from approximately 10,000 units, depending on box structure, material, printing requirements, and finishing complexity.', 'custom-box-theme'),
    ),
    array(
        'question' => __('Can you support OEM/ODM paper box production?', 'custom-box-theme'),
        'answer' => __('Yes. We support OEM and ODM paper box production, including custom box structure, size, material selection, logo printing, color customization, inserts, and premium finishing options.', 'custom-box-theme'),
    ),
    array(
        'question' => __('Can I customize box size, material, printing, and finishing?', 'custom-box-theme'),
        'answer' => __('Yes. Customers can customize dimensions, paper materials, CMYK or Pantone printing, foil stamping, embossing, debossing, spot UV, matte or gloss lamination, inserts, and other packaging details.', 'custom-box-theme'),
    ),
    array(
        'question' => __('Do you support international B2B orders?', 'custom-box-theme'),
        'answer' => __('Yes. VPN Packaging Factory supports international B2B customers and export packaging projects, including brands, distributors, wholesalers, and OEM/ODM packaging buyers.', 'custom-box-theme'),
    ),
);
?>

<section class="catalog-section catalog-faq-section" id="catalog-faq">
    <div class="container">
        <div class="catalog-section-heading catalog-section-heading-center">
            <span class="catalog-kicker"><?php esc_html_e('FAQ', 'custom-box-theme'); ?></span>
            <h2><?php esc_html_e('Frequently Asked Questions', 'custom-box-theme'); ?></h2>
            <p><?php esc_html_e('Find quick answers about our custom paper box catalog, production capability, and OEM/ODM packaging services.', 'custom-box-theme'); ?></p>
        </div>

        <div class="catalog-faq-list">
            <?php foreach ($faqs as $faq) : ?>
                <article class="catalog-faq-item">
                    <h3><?php echo esc_html($faq['question']); ?></h3>
                    <p><?php echo esc_html($faq['answer']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
