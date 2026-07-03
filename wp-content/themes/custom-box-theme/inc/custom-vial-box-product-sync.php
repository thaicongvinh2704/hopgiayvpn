<?php
/**
 * Product-specific SEO/content sync for the Custom Vial Boxes product page.
 */

defined('ABSPATH') || exit;

const CUSTOM_BOX_VIAL_BOXES_SYNC_VERSION = 'custom-vial-boxes-seo-20260703-v1';

add_action('admin_init', 'custom_box_maybe_sync_custom_vial_boxes_product');
add_action('admin_notices', 'custom_box_custom_vial_boxes_admin_notice');
add_action('wp_head', 'custom_box_custom_vial_boxes_output_styles', 30);
add_action('wp_head', 'custom_box_custom_vial_boxes_output_schema_fallback', 40);
add_filter('rank_math/json_ld', 'custom_box_custom_vial_boxes_rank_math_json_ld', 40);

function custom_box_maybe_sync_custom_vial_boxes_product(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $product_id = custom_box_sync_custom_vial_boxes_product(false);

    if (is_wp_error($product_id)) {
        update_option('custom_box_custom_vial_boxes_sync_error', $product_id->get_error_message(), false);
        return;
    }

    update_option('custom_box_custom_vial_boxes_sync_error', '', false);
}

function custom_box_sync_custom_vial_boxes_product(bool $force = false)
{
    $product = get_page_by_path('custom-vial-packaging-box', OBJECT, 'product');

    if (!$product || 'trash' === $product->post_status) {
        return new WP_Error('custom_vial_boxes_missing_product', 'Custom vial packaging box product was not found.');
    }

    $product_id = (int) $product->ID;

    if (!$force && CUSTOM_BOX_VIAL_BOXES_SYNC_VERSION === get_post_meta($product_id, '_custom_box_custom_vial_boxes_sync_version', true)) {
        return $product_id;
    }

    custom_box_update_custom_vial_boxes_images($product_id);

    $updated = wp_update_post(array(
        'ID'           => $product_id,
        'post_title'   => 'Custom Vial Boxes for Glass Vials, Ampoules and Lab Samples',
        'post_name'    => 'custom-vial-packaging-box',
        'post_excerpt' => custom_box_custom_vial_boxes_short_description(),
        'post_content' => custom_box_custom_vial_boxes_long_description($product_id),
        'post_status'  => in_array($product->post_status, array('publish', 'private'), true) ? $product->post_status : 'draft',
    ), true);

    if (is_wp_error($updated)) {
        return $updated;
    }

    update_post_meta($product_id, 'rank_math_title', 'Custom Vial Boxes Manufacturer | Vial Packaging with Inserts');
    update_post_meta($product_id, 'rank_math_description', 'Custom vial boxes for glass vials, ampoules, lab samples and cosmetic vials. Vietnam paper box manufacturer with custom inserts, printing, finishing and MOQ from 1000 boxes.');
    update_post_meta($product_id, 'rank_math_focus_keyword', 'custom vial boxes');
    update_post_meta($product_id, '_custom_box_product_hero_bullets', custom_box_custom_vial_boxes_hero_bullets());
    update_post_meta($product_id, '_custom_box_product_faq_html', custom_box_custom_vial_boxes_faq_html());
    update_post_meta($product_id, '_custom_box_hide_auto_description_heading', '1');
    custom_box_update_custom_vial_boxes_specs($product_id);
    update_post_meta($product_id, '_custom_box_custom_vial_boxes_sync_version', CUSTOM_BOX_VIAL_BOXES_SYNC_VERSION);

    return $product_id;
}

function custom_box_custom_vial_boxes_sync_report(int $product_id): string
{
    $product = get_post($product_id);

    if (!$product || 'product' !== $product->post_type) {
        return "Synced product could not be loaded.\n";
    }

    $content = (string) $product->post_content;
    $faq = (string) get_post_meta($product->ID, '_custom_box_product_faq_html', true);

    $lines = array(
        'Product ID: ' . (int) $product->ID,
        'Status: ' . get_post_status($product->ID),
        'Title: ' . $product->post_title,
        'Slug: ' . $product->post_name,
        'URL: ' . get_permalink($product->ID),
        'Rank Math title: ' . get_post_meta($product->ID, 'rank_math_title', true),
        'Rank Math description: ' . get_post_meta($product->ID, 'rank_math_description', true),
        'Focus keyword: ' . get_post_meta($product->ID, 'rank_math_focus_keyword', true),
        'Long description words: ' . str_word_count(wp_strip_all_tags($content)),
        'Content H1 count: ' . preg_match_all('/<h1\b/i', $content),
        'Image grids/cards: ' . substr_count($content, 'product-content-image-grid') . '/' . substr_count($content, 'product-content-image-card'),
        'FAQ items: ' . substr_count($faq, 'faq-item'),
        'Featured image ID: ' . (int) get_post_thumbnail_id($product->ID),
        'Old all-caps phrase in content: ' . substr_count($content . ' ' . $product->post_excerpt . ' ' . $faq, 'CUSTOM VIAL PACKAGING BOX'),
    );

    return implode(PHP_EOL, $lines) . PHP_EOL;
}

function custom_box_custom_vial_boxes_short_description(): string
{
    return 'Custom vial boxes are designed to protect and present small glass vials, ampoules, lab samples, cosmetic vials, medicine samples, essential oil vials and healthcare sample kits. VPN Paper Box Manufacturer produces custom vial packaging boxes with paperboard, SBS board, rigid board, EVA inserts, foam inserts, paper trays, custom logo printing and bulk production from 1000 boxes.';
}

function custom_box_custom_vial_boxes_hero_bullets(): array
{
    return array(
        'Custom size and structure for different vial dimensions',
        'Protective inserts for single-vial and multi-vial packaging',
        'Custom logo printing, Pantone color and premium finishing',
        'Factory-direct paper box production in Vietnam',
    );
}

function custom_box_custom_vial_boxes_image_alts(): array
{
    return array(
        'custom vial boxes with protective insert for glass vials',
        'custom printed vial packaging box for healthcare samples',
        'multi-vial paper box with organized insert cavities',
        'custom vial box with label panel for laboratory products',
        'rigid vial packaging box for premium sample kits',
        'paperboard vial box for cosmetic serum vials',
        'glass vial packaging boxes with custom printed paper structure',
    );
}

function custom_box_custom_vial_boxes_image_captions(): array
{
    return array(
        'Protective vial box structure planned around small glass containers and insert fit.',
        'Custom printed vial packaging box for healthcare samples and B2B product kits.',
        'Multi-vial paper box layout with organized cavities for several sample bottles.',
        'Label-ready vial packaging with clear information panels for laboratory products.',
        'Rigid vial packaging option for premium sample kits and brand launch programs.',
        'Paperboard vial box concept for cosmetic serum vials and compact sample packs.',
        'Glass vial packaging boxes can be adjusted by size, insert depth and printed layout.',
    );
}

function custom_box_custom_vial_boxes_image_titles(): array
{
    return array(
        'Custom Vial Boxes With Protective Insert',
        'Custom Printed Vial Packaging Box',
        'Multi-Vial Paper Box With Cavities',
        'Custom Vial Box With Label Panel',
        'Rigid Vial Packaging Box',
        'Paperboard Vial Box for Cosmetic Serum',
        'Glass Vial Packaging Boxes',
    );
}

function custom_box_custom_vial_boxes_product_image_ids(int $product_id): array
{
    $image_ids = array();
    $featured_id = (int) get_post_thumbnail_id($product_id);

    if ($featured_id) {
        $image_ids[] = $featured_id;
    }

    $gallery_ids = array_filter(array_map('absint', explode(',', (string) get_post_meta($product_id, '_product_image_gallery', true))));

    foreach ($gallery_ids as $gallery_id) {
        if ($gallery_id && !in_array($gallery_id, $image_ids, true)) {
            $image_ids[] = $gallery_id;
        }
    }

    return $image_ids;
}

function custom_box_update_custom_vial_boxes_images(int $product_id): void
{
    $image_ids = custom_box_custom_vial_boxes_product_image_ids($product_id);
    $alts = custom_box_custom_vial_boxes_image_alts();
    $titles = custom_box_custom_vial_boxes_image_titles();
    $captions = custom_box_custom_vial_boxes_image_captions();

    foreach ($image_ids as $index => $image_id) {
        update_post_meta($image_id, '_wp_attachment_image_alt', $alts[$index] ?? $alts[0]);
        wp_update_post(array(
            'ID'           => $image_id,
            'post_parent'  => $product_id,
            'post_title'   => $titles[$index] ?? $titles[0],
            'post_excerpt' => $captions[$index] ?? $captions[0],
        ));
    }
}

function custom_box_update_custom_vial_boxes_specs(int $product_id): void
{
    $specs = get_post_meta($product_id, '_custom_box_product_specs', true);

    if (!is_array($specs)) {
        return;
    }

    foreach ($specs as &$spec) {
        if (empty($spec['label'])) {
            continue;
        }

        if ('Model Number' === $spec['label']) {
            $spec['value'] = 'VPN-CUSTOM-VIAL-BOXES';
        }

        if ('Product Name' === $spec['label']) {
            $spec['value'] = 'Custom Vial Boxes';
        }
    }
    unset($spec);

    update_post_meta($product_id, '_custom_box_product_specs', $specs);
}

function custom_box_custom_vial_boxes_image_grid(int $product_id, array $indexes): string
{
    $image_ids = custom_box_custom_vial_boxes_product_image_ids($product_id);
    $alts = custom_box_custom_vial_boxes_image_alts();
    $captions = custom_box_custom_vial_boxes_image_captions();
    $figures = array();

    foreach ($indexes as $index) {
        if (empty($image_ids[$index])) {
            continue;
        }

        $image_id = (int) $image_ids[$index];
        $image_url = wp_get_attachment_image_url($image_id, 'large');

        if (!$image_url) {
            continue;
        }

        $figures[] = sprintf(
            '<figure class="product-content-image-card"><img src="%s" alt="%s" loading="lazy" decoding="async"><figcaption>%s</figcaption></figure>',
            esc_url($image_url),
            esc_attr($alts[$index] ?? $alts[0]),
            esc_html($captions[$index] ?? $captions[0])
        );
    }

    if (empty($figures)) {
        return '';
    }

    return '<div class="product-content-image-grid">' . implode('', $figures) . '</div>';
}

function custom_box_custom_vial_boxes_long_description(int $product_id): string
{
    $pharma_url = esc_url(home_url('/products/pharmaceutical-packaging-boxes/'));
    $printed_url = esc_url(home_url('/products/custom-printed-paper-boxes/'));
    $rigid_url = esc_url(home_url('/products/rigid-boxes/'));
    $folding_url = esc_url(home_url('/products/folding-carton-boxes/'));
    $materials_url = esc_url(home_url('/paper-materials-for-custom-paper-boxes/'));
    $ampoule_url = esc_url(home_url('/product/custom-ampoule-packaging-box/'));
    $grid_one = custom_box_custom_vial_boxes_image_grid($product_id, array(1, 2));
    $grid_two = custom_box_custom_vial_boxes_image_grid($product_id, array(3, 4));

    return <<<HTML
<section class="product-seo-content custom-vial-boxes-content">

  <h2>Custom Vial Boxes Built Around the Vial, Not a Generic Carton</h2>
  <p>Custom vial boxes need more precision than standard paper cartons because the product is usually small, fragile and easy to move inside the packaging. A glass vial, ampoule, serum sample or laboratory bottle can look simple from the outside, but the box must control the inner fit, opening direction, insert depth, label area and packing method before mass production.</p>
  <p>VPN Paper Box Manufacturer produces custom vial packaging boxes for brands, distributors, healthcare suppliers, cosmetic sample programs, laboratory product companies and OEM/ODM packaging projects. Each box can be adjusted by vial diameter, vial height, vial count, artwork layout, paper material, insert type and export packing requirements.</p>

  <h2>Protective Inserts for Glass Vials, Ampoules and Sample Bottles</h2>
  <p>For vial packaging, the insert is often more important than the outer artwork. A good insert keeps the vial stable during storage, retail handling, fulfillment and international shipping. Depending on the product weight and presentation goal, the box can use EVA inserts, foam inserts, paperboard trays, dividers or molded pulp supports.</p>
  <p>Single-vial boxes can be designed for one bottle with a tight cavity and clear front-panel branding. Multi-vial boxes can hold 2, 3, 5, 10 or more vials in an organized layout. For glass products, the sample should be tested with the real vial to confirm movement, cavity depth, side-wall strength, lid closure and packing speed before bulk production.</p>

  {$grid_one}

  <h2>Box Styles and Size Options for Custom Vial Boxes</h2>
  <p>Different vial products need different structures. A <a href="{$folding_url}">folding carton box</a> is cost-efficient for retail and sample distribution. A <a href="{$rigid_url}">rigid box</a> gives a stronger premium feel for healthcare kits, cosmetic launch sets or high-value sample programs. Drawer boxes and sleeve boxes create a cleaner reveal experience, while multi-cavity boxes help organize several SKUs in one kit.</p>

  <div class="seo-table-wrapper">
    <table class="seo-product-table">
      <thead>
        <tr>
          <th>Vial Box Option</th>
          <th>Suitable For</th>
          <th>Common Customization</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Single vial box</td>
          <td>One glass vial, serum vial, ampoule or sample bottle</td>
          <td>Custom cavity, front label area, logo printing, barcode panel</td>
        </tr>
        <tr>
          <td>Multi-vial box</td>
          <td>Sample kits, distributor sets, clinical or laboratory packs</td>
          <td>2-vial, 3-vial, 5-vial or custom cavity layout</td>
        </tr>
        <tr>
          <td>10ml vial box</td>
          <td>10ml glass vials, peptide vials, serum samples or essential oil samples</td>
          <td>Custom height, diameter, insert depth and printed product information</td>
        </tr>
        <tr>
          <td>Rigid vial kit</td>
          <td>Premium cosmetic, healthcare or launch kit packaging</td>
          <td>Rigid board, EVA insert, magnetic closure, foil logo, sleeve</td>
        </tr>
      </tbody>
    </table>
  </div>

  <h2>Materials for Custom Vial Packaging Boxes</h2>
  <p>Material selection should match the vial weight, sales channel and brand positioning. SBS board and ivory paper are suitable for clean printing and small text. Duplex board can support cost-efficient production. Rigid board is better for premium kits that need a stronger hand feel. Kraft paper and recycled paperboard can support natural or eco-focused packaging concepts when the structure is still strong enough for the product.</p>
  <p>For export orders, the material should be selected together with the insert and outer carton plan. A beautiful box can still fail if the insert is too loose, the board is too thin, or the packaging cannot handle stacking and shipping pressure. Buyers can also review <a href="{$materials_url}">paper material options for custom paper boxes</a> before confirming the final specification.</p>

  <h2>Custom Printing, Finishing and Label Panels</h2>
  <p><a href="{$printed_url}">Custom printed paper boxes</a> for vials can include CMYK printing, Pantone color matching, foil stamping, embossing, debossing, spot UV, matte lamination, gloss lamination and soft-touch finishing. For pharmaceutical, healthcare and laboratory products, the design should stay clean and readable. Important areas may include dosage information, batch number, barcode, QR code, warning text, certification marks, product name and multilingual labels.</p>
  <p>For cosmetic vials and premium sample kits, the box can use more visual branding, color coding and tactile finishing while still keeping product information clear. A practical design is not the one with the most effects; it is the one that protects the vial, communicates the product clearly and supports repeat production.</p>

  <h2>Applications of Custom Vial Boxes</h2>
  <p>These custom vial boxes can be used for glass vials, ampoules, laboratory samples, cosmetic serum vials, essential oil sample bottles, medicine samples, peptide vial packaging, healthcare sample kits, distributor sample packs and OEM private label product lines. Related buyers may also compare our <a href="{$ampoule_url}">custom ampoule packaging box</a> when the product requires similar small glass container protection.</p>
  <p>This product belongs to our <a href="{$pharma_url}">pharmaceutical packaging boxes</a> range and is useful for B2B buyers who need consistent packaging across different products or markets. A distributor can keep the same structural design while changing language panels, product labels and SKU information. A brand owner can use one packaging style for different vial sizes while maintaining a consistent visual identity.</p>

  {$grid_two}

  <h2>How to Order Custom Vial Boxes from VPN Paper Box Manufacturer</h2>
  <p>To prepare an accurate quotation, send the vial size, vial quantity per box, expected order quantity, artwork requirements, preferred material, insert preference and shipping market. If you are unsure which structure is suitable, VPN can recommend a folding carton, rigid box, sleeve box, drawer box, paperboard tray, EVA insert or foam insert based on the product and budget.</p>
  <p>The typical process includes product size confirmation, structure suggestion, dieline preparation, artwork checking, sample approval, mass printing, finishing, die cutting, insert assembly, quality inspection and export packing. For the safest result, the sample should be tested with the real vial before approving bulk production.</p>

  <h2>Request a Quote for Custom Vial Boxes</h2>
  <p>Need custom vial boxes for glass vials, ampoules, lab samples, cosmetic vials or healthcare kits? Send your product dimensions, target quantity and branding requirements to VPN Paper Box Manufacturer. Our team can help you choose the right paper material, box structure, insert type, printing method and finishing option for your next bulk packaging order.</p>

</section>
HTML;
}

function custom_box_custom_vial_boxes_faq_html(): string
{
    return <<<'HTML'
<section class="product-faq custom-vial-boxes-faq">
  <div class="container">
    <h2>Custom Vial Boxes FAQ</h2>

    <div class="faq-item">
      <h3>What are custom vial boxes used for?</h3>
      <p>Custom vial boxes are used to package small glass vials, ampoules, cosmetic serum samples, laboratory samples, medicine samples, essential oil bottles, peptide vials and healthcare sample kits. They help protect the vial, organize product information and improve brand presentation.</p>
    </div>

    <div class="faq-item">
      <h3>Can you make vial boxes with inserts?</h3>
      <p>Yes. VPN can produce vial boxes with EVA inserts, foam inserts, paperboard trays, dividers or molded pulp supports. The insert can be customized by vial diameter, vial height, vial count and packing direction.</p>
    </div>

    <div class="faq-item">
      <h3>Can you produce 10ml vial boxes?</h3>
      <p>Yes. We can produce custom 10ml vial boxes and other vial size options based on the real product dimensions. Please provide the vial height, diameter, cap size and quantity per box for a more accurate structure recommendation.</p>
    </div>

    <div class="faq-item">
      <h3>What materials are available for custom vial packaging boxes?</h3>
      <p>Common materials include SBS board, ivory paper, duplex board, coated paper, kraft paper, recycled paperboard, rigid board, EVA, foam and paperboard inserts. The right material depends on product weight, brand style, protection needs and order quantity.</p>
    </div>

    <div class="faq-item">
      <h3>Can you print custom logos and product information?</h3>
      <p>Yes. Custom printing options include CMYK printing, Pantone color matching, logo printing, foil stamping, embossing, debossing, spot UV, matte lamination and gloss lamination. The dieline can reserve areas for product name, barcode, QR code, batch number, warning text and multilingual information.</p>
    </div>

    <div class="faq-item">
      <h3>What information do I need to request a quote?</h3>
      <p>Please send the vial size, vial count per box, target order quantity, material preference, insert preference, artwork requirements and shipping market. If available, also send product photos or a reference packaging style.</p>
    </div>
  </div>
</section>
HTML;
}

function custom_box_custom_vial_boxes_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $error = (string) get_option('custom_box_custom_vial_boxes_sync_error', '');

    if ('' === $error) {
        return;
    }

    echo '<div class="notice notice-warning"><p><strong>Custom vial boxes sync:</strong> ' . esc_html($error) . '</p></div>';
}

function custom_box_is_custom_vial_boxes_product_page(): bool
{
    return function_exists('is_product')
        && is_product()
        && 'custom-vial-packaging-box' === get_post_field('post_name', get_queried_object_id());
}

function custom_box_custom_vial_boxes_output_styles(): void
{
    if (!custom_box_is_custom_vial_boxes_product_page()) {
        return;
    }
    ?>
    <style>
        .custom-vial-boxes-content {
            margin-top: 40px;
        }

        .custom-vial-boxes-content h2,
        .custom-vial-boxes-faq h2 {
            margin-top: 32px;
            margin-bottom: 14px;
        }

        .custom-vial-boxes-content p,
        .custom-vial-boxes-faq p {
            margin-bottom: 16px;
            line-height: 1.7;
        }

        .seo-table-wrapper {
            overflow-x: auto;
            margin: 24px 0;
        }

        .seo-product-table {
            width: 100%;
            border-collapse: collapse;
        }

        .seo-product-table th,
        .seo-product-table td {
            padding: 12px 14px;
            border: 1px solid rgba(0, 0, 0, 0.12);
            text-align: left;
            vertical-align: top;
        }

        .product-content-image-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin: 28px 0;
        }

        .product-content-image-card {
            margin: 0;
        }

        .product-content-image-grid img {
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: contain;
            display: block;
            border-radius: 8px;
            background: #f7f7f5;
        }

        .custom-vial-boxes-faq {
            padding: 56px 0 24px;
        }

        .custom-vial-boxes-faq .faq-item {
            padding: 18px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .custom-vial-boxes-faq .faq-item h3 {
            margin: 0 0 10px;
            font-size: 1.05rem;
            line-height: 1.35;
        }

        @media (max-width: 767px) {
            .product-content-image-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <?php
}

function custom_box_custom_vial_boxes_schema(): array
{
    return array(
        '@context'     => 'https://schema.org/',
        '@type'        => 'Product',
        'name'         => 'Custom Vial Boxes',
        'description'  => 'Custom vial boxes for glass vials, ampoules, lab samples, cosmetic vials, medicine samples, essential oil vials and healthcare kits. Manufactured in Vietnam with custom paperboard, inserts, printing, finishing and MOQ from 1000 boxes.',
        'brand'        => array(
            '@type' => 'Brand',
            'name'  => 'VPN Paper Box Manufacturer',
        ),
        'manufacturer' => array(
            '@type'   => 'Organization',
            'name'    => 'VPN Paper Box Manufacturer',
            'address' => array(
                '@type'           => 'PostalAddress',
                'addressLocality' => 'Ho Chi Minh City',
                'addressCountry'  => 'VN',
            ),
        ),
        'sku'          => 'custom-vial-boxes',
        'category'     => 'Pharmaceutical Packaging Boxes',
        'offers'       => array(
            '@type'              => 'Offer',
            'url'                => home_url('/product/custom-vial-packaging-box/'),
            'priceCurrency'      => 'USD',
            'availability'       => 'https://schema.org/InStock',
            'priceSpecification' => array(
                '@type'         => 'PriceSpecification',
                'priceCurrency' => 'USD',
                'description'   => 'Price based on size, material, insert, printing, finishing and quantity. MOQ from 1000 boxes.',
            ),
        ),
    );
}

function custom_box_custom_vial_boxes_rank_math_json_ld($data)
{
    if (!is_array($data) || !custom_box_is_custom_vial_boxes_product_page()) {
        return $data;
    }

    $schema = custom_box_custom_vial_boxes_schema();
    unset($schema['@context']);

    $has_product_schema = false;

    foreach ($data as $key => $entity) {
        if (!is_array($entity)) {
            continue;
        }

        $types = isset($entity['@type']) ? (array) $entity['@type'] : array();

        if (array_intersect($types, array('Product', 'WooCommerceProduct', 'ProductGroup'))) {
            $data[$key] = array_merge($entity, $schema);
            $has_product_schema = true;
        }
    }

    if (!$has_product_schema) {
        $data['customVialBoxesProduct'] = $schema;
    }

    return $data;
}

function custom_box_custom_vial_boxes_output_schema_fallback(): void
{
    if (defined('RANK_MATH_VERSION') || !custom_box_is_custom_vial_boxes_product_page()) {
        return;
    }

    echo '<script type="application/ld+json">' . wp_json_encode(custom_box_custom_vial_boxes_schema(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}
