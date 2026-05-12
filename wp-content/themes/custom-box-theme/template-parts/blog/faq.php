<?php
/**
 * Blog FAQ accordion with schema.
 */

defined('ABSPATH') || exit;

$faqs = array(
    array(
        'question' => __('Can VPN Packaging customize the packaging from this guide?', 'custom-box-theme'),
        'answer'   => __('Yes. We can customize structure, size, material, printing method, finishing, inserts, and packaging quantity based on your product and brand requirements.', 'custom-box-theme'),
    ),
    array(
        'question' => __('Do you support samples before mass production?', 'custom-box-theme'),
        'answer'   => __('Yes. We can support dielines, artwork checking, digital proofs, and physical samples before bulk production begins.', 'custom-box-theme'),
    ),
    array(
        'question' => __('What information should I send for a quote?', 'custom-box-theme'),
        'answer'   => __('Please send product size, box style, quantity, material preference, printing requirements, finishing options, destination country, and artwork if available.', 'custom-box-theme'),
    ),
);

$schema = array(
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array(),
);

foreach ($faqs as $faq) {
    $schema['mainEntity'][] = array(
        '@type'          => 'Question',
        'name'           => wp_strip_all_tags($faq['question']),
        'acceptedAnswer' => array(
            '@type' => 'Answer',
            'text'  => wp_strip_all_tags($faq['answer']),
        ),
    );
}
?>

<section class="blog-faq" aria-labelledby="blog-faq-heading">
    <span class="blog-section-kicker"><?php esc_html_e('FAQ', 'custom-box-theme'); ?></span>
    <h2 id="blog-faq-heading"><?php esc_html_e('Packaging Questions Buyers Often Ask', 'custom-box-theme'); ?></h2>

    <div class="blog-faq-list">
        <?php foreach ($faqs as $index => $faq) : ?>
            <div class="blog-faq-item <?php echo 0 === $index ? 'is-open' : ''; ?>">
                <button class="blog-faq-question" type="button" aria-expanded="<?php echo 0 === $index ? 'true' : 'false'; ?>">
                    <span><?php echo esc_html($faq['question']); ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="blog-faq-answer">
                    <p><?php echo esc_html($faq['answer']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script type="application/ld+json"><?php echo wp_json_encode($schema); ?></script>
</section>
