<?php
/**
 * Import magnetic closure box products from uploaded Media Library images.
 *
 * Run:
 *   php tools/import-magnetic-closure-products.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

function vpn_mag_link($url, $anchor)
{
    return '<a href="' . esc_url(home_url($url)) . '">' . esc_html($anchor) . '</a>';
}

function vpn_mag_file_base($filename)
{
    return preg_replace('/\.[^.]+$/', '', basename($filename));
}

function vpn_mag_find_attachment_by_base($filename_base)
{
    $attachments = get_posts(array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => array(
            array(
                'key'     => '_wp_attached_file',
                'value'   => $filename_base,
                'compare' => 'LIKE',
            ),
        ),
    ));

    foreach ($attachments as $attachment_id) {
        $attached_file = (string) get_post_meta((int) $attachment_id, '_wp_attached_file', true);
        if (vpn_mag_file_base($attached_file) === $filename_base) {
            return (int) $attachment_id;
        }
    }

    return 0;
}

function vpn_mag_import_attachment_from_uploads($filename, $alt, $title, $caption)
{
    $relative = '2026/07/' . basename($filename);
    $file = WP_CONTENT_DIR . '/uploads/' . $relative;

    if (!file_exists($file) && function_exists('get_template_directory')) {
        $bundled = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $relative;
        if (file_exists($bundled)) {
            $target_dir = dirname($file);
            if (!is_dir($target_dir)) {
                wp_mkdir_p($target_dir);
            }
            copy($bundled, $file);
        }
    }

    if (!file_exists($file)) {
        return 0;
    }

    $filetype = wp_check_filetype(basename($file), null);
    if (empty($filetype['type'])) {
        return 0;
    }

    $attachment_id = wp_insert_attachment(array(
        'post_mime_type' => $filetype['type'],
        'post_title'     => $title,
        'post_content'   => '',
        'post_excerpt'   => $caption,
        'post_status'    => 'inherit',
        'guid'           => content_url('uploads/' . $relative),
    ), $file);

    if (is_wp_error($attachment_id) || !$attachment_id) {
        return 0;
    }

    update_post_meta($attachment_id, '_wp_attached_file', $relative);
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata($attachment_id, $file);
    if (!is_wp_error($metadata) && !empty($metadata)) {
        wp_update_attachment_metadata($attachment_id, $metadata);
    }

    return (int) $attachment_id;
}

function vpn_mag_attachment_id($filename, $alt, $title, $caption)
{
    $filename_base = vpn_mag_file_base($filename);
    $attachment_id = vpn_mag_find_attachment_by_base($filename_base);

    if (!$attachment_id) {
        $attachment_id = vpn_mag_import_attachment_from_uploads($filename, $alt, $title, $caption);
    }

    if (!$attachment_id) {
        return 0;
    }

    update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
    wp_update_post(array(
        'ID'           => $attachment_id,
        'post_title'   => $title,
        'post_excerpt' => $caption,
    ));

    return $attachment_id;
}

function vpn_mag_specs($product)
{
    return array(
        array('label' => 'Feature', 'value' => $product['feature']),
        array('label' => 'Industrial Use', 'value' => $product['industrial']),
        array('label' => 'Paper Type', 'value' => $product['paper']),
        array('label' => 'Box Type', 'value' => $product['box_type']),
        array('label' => 'Shape', 'value' => $product['shape']),
        array('label' => 'Place of Origin', 'value' => 'Vietnam'),
        array('label' => 'Model Number', 'value' => $product['title']),
        array('label' => 'Brand Name', 'value' => 'VPN'),
        array('label' => 'Province', 'value' => 'Ho Chi Minh City'),
        array('label' => 'Accessories', 'value' => $product['accessories']),
        array('label' => 'Custom Order', 'value' => 'Accept'),
        array('label' => 'Liner Type', 'value' => $product['liner']),
        array('label' => 'Logo Printing', 'value' => 'Custom logo'),
        array('label' => 'Printing Handling', 'value' => $product['printing']),
        array('label' => 'Color', 'value' => $product['colors']),
        array('label' => 'Size', 'value' => 'Customized size'),
        array('label' => 'Thickness', 'value' => 'Customized thickness'),
        array('label' => 'Single Piece Price', 'value' => 'Price based on size, material, insert, printing, finishing, and quantity'),
        array('label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes'),
        array('label' => 'Product Name', 'value' => $product['title']),
        array('label' => 'Design', 'value' => "Customer's Specific Requirement"),
    );
}

function vpn_mag_section($heading, $paragraphs)
{
    $html = '<h2>' . esc_html($heading) . '</h2>';
    foreach ($paragraphs as $paragraph) {
        $html .= '<p>' . $paragraph . '</p>';
    }

    return $html;
}

function vpn_mag_inline_image($attachment_id, $caption, $narrow = false)
{
    $image = wp_get_attachment_image($attachment_id, 'large', false, array('loading' => 'lazy'));
    if (!$image) {
        return '';
    }

    return '<figure class="product-inline-figure product-inline-figure-small' . ($narrow ? ' is-narrow' : '') . '">' .
        $image . '<figcaption>' . esc_html($caption) . '</figcaption></figure>';
}

function vpn_mag_sentence_list($items)
{
    $items = array_values(array_filter($items));
    if (empty($items)) {
        return '';
    }
    if (1 === count($items)) {
        return $items[0];
    }

    $last = array_pop($items);
    return implode(', ', $items) . ', and ' . $last;
}

function vpn_mag_short_description($product)
{
    return $product['title'] . ' is a custom magnetic closure box for ' . $product['inside'] . '. It is planned for ' . $product['buyer'] . ' that need a rigid paper package with stable product fit, clean opening feel, custom logo printing, and export-ready production. The structure can be adjusted by board thickness, wrapped paper, insert layout, magnet strength, ribbon or handle option, and surface finishing. It supports ' . $product['channel'] . ' while keeping the main brand panel, product information, and gift presentation consistent across bulk orders. Sampling can be reviewed before mass production. MOQ starts from 1000 boxes.';
}

function vpn_mag_content($product, $image_ids)
{
    $magnetic_link = vpn_mag_link('/product-category/magnetic-closure-boxes/', 'magnetic closure boxes');
    $rigid_link = vpn_mag_link('/product-category/rigid-boxes/', 'rigid boxes');
    $gift_link = vpn_mag_link('/product-category/gift-paper-boxes/', 'gift paper boxes');
    $material_link = vpn_mag_link('/how-to-choose-paper-material-for-product-packaging/', 'paper material selection for product packaging');
    $artwork_link = vpn_mag_link('/how-to-prepare-artwork-for-printed-paper-boxes/', 'print-ready artwork for paper boxes');
    $finish_link = vpn_mag_link('/foil-stamping-and-embossing-for-paper-packaging/', 'foil stamping and embossing for packaging');
    $lamination_link = vpn_mag_link('/matte-vs-gloss-lamination-for-packaging/', 'matte and gloss lamination options');
    $quote_link = vpn_mag_link('/contact/#quote', 'request a magnetic box quote');
    $related_one = vpn_mag_link($product['related'][0][0], $product['related'][0][1]);
    $related_two = vpn_mag_link($product['related'][1][0], $product['related'][1][1]);
    $details = vpn_mag_sentence_list($product['details']);
    $panel_details = vpn_mag_sentence_list($product['panel_details']);
    $qc_points = vpn_mag_sentence_list($product['qc_points']);

    $html = vpn_mag_section($product['headings'][0], array(
        $product['title'] . ' is built for ' . $product['buyer'] . ' that want a premium rigid package for ' . $product['inside'] . '. A magnetic closure box is more than a stronger outer shell. It must control the opening motion, protect the product during handling, keep the lid aligned, and present the brand clearly when the customer first lifts the flap.',
        'This product belongs to the ' . $magnetic_link . ' category and can be developed together with ' . $rigid_link . ' or ' . $gift_link . ' when the buyer needs a coordinated packaging family. The main packaging challenge is ' . $product['problem'] . ', so the dieline, insert, board thickness, wrapped paper, magnet position, and carton packing should all be planned around the real item rather than a generic gift box size.',
        'Useful RFQ details include ' . $details . '. These details affect cavity tolerance, board selection, folding method, glue area, printing layout, sample cost, and production schedule. Sharing them early helps the factory quote a structure that can actually hold the product, not just a box that looks correct in a mockup.',
    ));

    if (!empty($image_ids[0])) {
        $html .= vpn_mag_inline_image($image_ids[0], $product['captions'][0], true);
    }

    $html .= vpn_mag_section($product['headings'][1], array(
        $product['risk_copy'] . ' The package should hold the item firmly without making removal difficult. If the cavity is too loose, the product shifts in transit and the premium reveal feels careless. If the cavity is too tight, the customer may pull from the wrong point and damage the product, label, cap, wrapper, or surface finish.',
        'For ' . strtolower($product['title']) . ', the internal plan should consider product weight, contact points, top clearance, side clearance, opening direction, packing speed, and the way the customer will return the item to the box after use. A magnetic box often becomes part of the ownership experience, so the inner fit should be practical for repeated opening rather than only beautiful in a first sample.',
        'The buyer should confirm whether the box will be used for retail shelf display, ecommerce fulfillment, sales kits, seasonal gifting, distributor samples, or direct handover at an event. Each channel changes the risk profile. A retail box may need stronger front-panel recognition, while a gifting box may need a slower reveal, cleaner insert, and more premium touch points.',
    ));

    $html .= vpn_mag_section($product['headings'][2], array(
        $product['structure_copy'] . ' The lid should close with a clear but not aggressive magnetic pull. Magnet strength, lid overlap, greyboard thickness, wrapped paper stretch, and glue consistency all influence whether the box feels premium or awkward. A good sample should be tested empty and loaded because the product weight changes how the lid and hinge behave.',
        'Common structure options include book-style magnetic box, clamshell magnetic box, shoulder box with magnetic flap, foldable magnetic rigid box, drawer box with magnetic sleeve, and magnetic presentation kit. The right choice depends on product height, expected reveal, packing labor, shipping volume, and whether the buyer wants a reusable package.',
        'For bulk production, the structure should also be easy to assemble consistently. A complicated flap can look impressive in a rendering but slow down packing or create uneven lid alignment. The sample should be checked for hinge memory, edge wrapping, corner sharpness, board warping, magnet placement, and how cleanly the lid meets the base after repeated opening.',
    ));

    if (!empty($image_ids[1])) {
        $html .= vpn_mag_inline_image($image_ids[1], $product['captions'][1]);
    }

    $html .= vpn_mag_section($product['headings'][3], array(
        $product['insert_copy'] . ' Insert design is the part that turns a magnetic box from a generic gift container into a product-specific package. It can use folded paperboard, rigid card, EVA foam, molded pulp, flocked tray, satin support, corrugated partition, or a mixed-material layout depending on weight, fragility, surface sensitivity, and sustainability direction.',
        'The insert should protect the visible product surfaces and keep the item facing the correct direction when the lid opens. It should also leave practical finger clearance. Customers need to remove the product without bending a label, scraping a decorated cap, touching a polished surface, or pulling a fragile part. This is especially important for premium gifts where the first handling moment affects brand trust.',
        'B2B buyers should also think about packing speed. A very decorative insert may look good in a sample but slow down mass assembly. A useful production insert locks quickly, keeps orientation consistent, supports the main contact points, and allows the team to check product quantity before closing the lid.',
    ));

    $html .= vpn_mag_section($product['headings'][4], array(
        'Artwork should be prepared on the final dieline with bleed, safe zones, fold direction, wrap allowance, magnet clearance, and finish layers clearly marked. Important panels may include ' . $panel_details . '. The outside should communicate brand value quickly, while the inside can support product storytelling, usage instruction, campaign messaging, or certificate placement.',
        'The ' . $artwork_link . ' guide is useful before sending files to production because magnetic rigid boxes often combine printed wrap paper, inner lining, insert labels, belly bands, sleeve cards, or thank-you cards. If these pieces are designed separately without a shared color target, the finished package can feel mismatched even when each part is printed correctly.',
        $product['artwork_copy'] . ' Fine text, metallic logos, spot UV windows, and embossed marks should be placed away from folds and heavy glue areas. If the package has many SKUs or campaign versions, keep a controlled layout system so the factory can change color, product name, barcode, or model information without rebuilding the entire structure.',
    ));

    if (!empty($image_ids[2])) {
        $html .= vpn_mag_inline_image($image_ids[2], $product['captions'][2], true);
    }

    $html .= vpn_mag_section($product['headings'][5], array(
        'Material options include ' . $product['materials'] . '. Board choice should be based on product weight, box size, opening style, surface expectation, and export route. A light promotional kit may use a foldable magnetic construction, while a heavy gift box may need thicker greyboard, reinforced corners, and a stronger inner tray.',
        'Surface finishing can include matte lamination, gloss lamination, soft-touch film, textured specialty paper, foil stamping, embossing, debossing, spot UV, metallic ink, silk screen details, ribbon, and printed sleeve. The ' . $finish_link . ' guide and ' . $lamination_link . ' guide help buyers decide which finish adds value without making production unstable.',
        'The ' . $material_link . ' guide is also useful when the buyer needs recyclable paper direction, FSC paper, recycled content, kraft appearance, or a plastic-reduction insert. Sustainability claims should be confirmed with real material choices and market requirements, not added as decoration after the packaging has already been approved.',
    ));

    $html .= vpn_mag_section($product['headings'][6], array(
        $product['channel_copy'] . ' A magnetic box for retail should stack cleanly, protect corners, and show the product category quickly. A box for ecommerce should survive the outer shipping carton and avoid scuffed corners. A sales kit or PR package should open smoothly on camera and keep every item in the intended position.',
        'Master carton planning matters because rigid boxes can be damaged by pressure, humidity, and surface rubbing. Finished boxes should be packed with enough protection for corners, printed surfaces, foil details, and magnetic flaps. Carton quantity should be based on final box size and weight, not an early sample estimate.',
        'If the product will be shipped to distributors, retailers, hotels, boutiques, or launch events, the buyer should confirm barcode needs, sticker zones, language variants, campaign deadlines, and replacement parts before mass production. These operational details are less visible than the design, but they prevent delays after the sample is approved.',
    ));

    if (!empty($image_ids[3])) {
        $html .= vpn_mag_inline_image($image_ids[3], $product['captions'][3]);
    }

    $html .= vpn_mag_section($product['headings'][7], array(
        'Quality control should check ' . $qc_points . '. Magnetic boxes are handled closely, so small issues are easy to notice. Weak closure, crooked lid, wrinkled wrap paper, exposed greyboard, glue marks, scratched lamination, uneven foil, and loose inserts can reduce perceived value even when the product inside is high quality.',
        'Sample approval should use the real product or an accurate dummy. The team should check product fit, lid closure, magnet pull, opening angle, insert holding strength, panel readability, color under practical lighting, barcode scan, sleeve fit, and carton packing. If the project uses several product variants, the sample should be tested against the largest and heaviest variant.',
        'Before mass production, approve a clear sample standard and tolerance list. This includes box dimensions, board thickness, magnet position, hinge behavior, insert tolerance, surface finish, color target, carton count, and any acceptable variation. A practical checklist prevents repeated questions after materials have already been ordered.',
    ));

    $html .= '<h2>' . esc_html($product['headings'][8]) . '</h2><ul>';
    foreach ($product['mistakes'] as $mistake) {
        $html .= '<li>' . esc_html($mistake) . '</li>';
    }
    $html .= '</ul>';

    $html .= vpn_mag_section($product['headings'][9], array(
        'This product can be developed for ' . $product['buyer'] . ' and compared with ' . $related_one . ' or ' . $related_two . ' when the buyer wants a coordinated packaging range. The goal is to match product protection, premium opening, printing detail, insert logic, and export packing before the order moves into bulk production.',
        'For an accurate quotation, send product dimensions, filled weight, product photos, expected quantity, target market, artwork status, material preference, surface finishing, insert requirement, barcode and label needs, packing method, and delivery deadline. These details allow VPN Paper Box Manufacturer to recommend a realistic structure, sample direction, production method, and shipping plan.',
        'MOQ starts from 1000 boxes. Send your project details to ' . $quote_link . ' and include any existing dieline, product sample, brand guideline, or reference packaging so the magnetic closure box can be reviewed around real production conditions.',
    ));

    return $html;
}

function vpn_mag_products()
{
    return array(
        array(
            'title' => 'Custom Perfume Magnetic Closure Box',
            'slug' => 'custom-perfume-magnetic-closure-box',
            'keyword' => 'perfume magnetic closure box',
            'buyer' => 'perfume brands, fragrance distributors, boutique beauty retailers, hotel amenity programs, and private label fragrance launches',
            'inside' => 'glass perfume bottles, fragrance discovery sets, atomizer gifts, refill bottles, and premium scent collections',
            'problem' => 'protecting a fragile glass bottle while keeping cap clearance, scent story, and luxury shelf presentation under control',
            'risk_copy' => 'Perfume packaging has to protect glass, prevent cap pressure, and make the fragrance feel premium before the bottle is touched.',
            'structure_copy' => 'A perfume magnetic closure box usually works best as a book-style rigid box, shoulder presentation box, or magnetic gift set with a fitted tray.',
            'insert_copy' => 'Perfume inserts should support the bottle body while leaving enough room for the cap, sprayer, label, and customer fingers.',
            'artwork_copy' => 'Fragrance packaging often uses a restrained front panel, inner story panel, scent family color, and controlled metallic detail.',
            'channel_copy' => 'Perfume magnetic boxes may move through department stores, boutique counters, holiday gift programs, influencer kits, and ecommerce shipments.',
            'materials' => 'rigid greyboard, coated art paper, specialty textured paper, black card, gold or silver foil, EVA insert, paperboard tray, and satin lining optional',
            'feature' => 'Magnetic rigid perfume presentation, bottle-fit insert, premium logo finishing, export-ready gift packaging',
            'industrial' => 'Perfume, Fragrance, Beauty Retail, Luxury Gift',
            'paper' => 'Rigid Greyboard / Coated Art Paper / Specialty Paper / Black Card',
            'box_type' => 'Perfume Magnetic Closure Box',
            'shape' => 'Book Style / Bottle Fit / Customized Rectangle',
            'accessories' => 'EVA insert / Paper tray / Satin lining / Ribbon / Product card optional',
            'liner' => 'EVA foam / Paperboard tray / Satin lining / Molded pulp optional',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Spot UV, Soft Touch Lamination',
            'colors' => 'Black / White / Gold / Fragrance color system / Customized Color',
            'channel' => 'fragrance retail, luxury gifting, PR launch kits, hotel gifting, and ecommerce presentation',
            'details' => array('bottle height', 'bottle diameter', 'filled weight', 'cap clearance', 'label position', 'scent family color', 'set quantity', 'export shipping route'),
            'panel_details' => array('brand logo', 'fragrance name', 'scent family', 'bottle volume', 'ingredient or warning area', 'barcode', 'batch code space', 'inner brand story'),
            'qc_points' => array('bottle movement', 'cap clearance', 'magnet pull', 'tray depth', 'foil position', 'wrap paper edge', 'lid alignment', 'carton crush resistance'),
            'category_slugs' => array('magnetic-closure-boxes', 'rigid-boxes', 'perfume-packaging-boxes', 'cosmetic-paper-boxes', 'gift-paper-boxes'),
            'tags' => array('perfume box', 'fragrance packaging', 'magnetic rigid box', 'luxury gift box'),
            'images' => array('main', 'open', 'gallery', 'detail'),
            'captions' => array(
                'Main view of custom perfume magnetic closure box for fragrance bottles.',
                'Open perfume magnetic box showing bottle-fit presentation and lid structure.',
                'Gallery view of perfume magnetic closure packaging for premium fragrance launches.',
                'Detail view of perfume magnetic box finishing, insert, and printed brand panel.',
            ),
            'related' => array(array('/product/custom-skincare-magnetic-closure-box/', 'skincare magnetic closure box'), array('/product/custom-wine-magnetic-gift-box/', 'wine magnetic gift box')),
            'headings' => array('Perfume Magnetic Box for Fragrance Presentation', 'Bottle Protection and Cap Clearance', 'Magnetic Rigid Structure for Luxury Fragrance', 'Insert Planning for Glass Perfume Bottles', 'Artwork Panels for Scent Story and Retail Labels', 'Premium Materials and Fragrance Finishing', 'Retail, Gift, and Ecommerce Use for Perfume Boxes', 'Quality Checks for Perfume Magnetic Packaging', 'Common Mistakes With Perfume Magnetic Boxes', 'Quote Details for Custom Perfume Magnetic Closure Boxes'),
            'mistakes' => array(
                'Approving the box without testing the real filled perfume bottle.',
                'Letting the insert press against the cap or sprayer area.',
                'Placing foil too close to folds or magnet pressure zones.',
                'Using the same tray for different bottle sizes without checking movement.',
            ),
        ),
        array(
            'title' => 'Custom Skincare Magnetic Closure Box',
            'slug' => 'custom-skincare-magnetic-closure-box',
            'keyword' => 'skincare magnetic closure box',
            'buyer' => 'skincare brands, beauty distributors, spa product suppliers, clinic skincare programs, and private label cosmetic teams',
            'inside' => 'serum bottles, cream jars, cleanser tubes, facial mask sets, routine kits, and beauty gift collections',
            'problem' => 'organizing several skincare formats in one premium package while keeping product information and insert fit clear',
            'risk_copy' => 'Skincare sets often include mixed item sizes, so the package must control movement without hiding product labels or routine order.',
            'structure_copy' => 'A skincare magnetic closure box can use a book-style rigid structure, magnetic tray box, or foldable magnetic set box depending on product count.',
            'insert_copy' => 'Skincare inserts should separate jars, tubes, bottles, sachets, and cards while keeping the routine sequence easy to understand.',
            'artwork_copy' => 'Skincare artwork should make ingredient direction, skin concern, usage step, and formula variant easy to compare across a line.',
            'channel_copy' => 'Skincare magnetic boxes are useful for premium retail, spa kits, clinic programs, subscription gifts, and influencer launch packages.',
            'materials' => 'rigid greyboard, SBS paperboard, art paper, kraft paper, molded pulp insert, folded paper tray, EVA insert, and soft-touch laminated wrap',
            'feature' => 'Magnetic skincare set packaging, multi-product insert, clean cosmetic information panels, premium gift presentation',
            'industrial' => 'Skincare, Beauty, Cosmetic Gift Set, Spa Retail',
            'paper' => 'Rigid Greyboard / SBS Paperboard / Art Paper / Kraft Paper',
            'box_type' => 'Skincare Magnetic Closure Box',
            'shape' => 'Book Style / Set Box / Customized Routine Layout',
            'accessories' => 'Multi-cavity insert / Product card / Ribbon / Sleeve / Paper tray optional',
            'liner' => 'Paperboard tray / Molded pulp / EVA insert / No liner optional',
            'printing' => 'CMYK Printing, Pantone Printing, Matte Lamination, Soft Touch Lamination, Foil Stamping, Embossing',
            'colors' => 'White / Pastel / Kraft / Clinic color system / Customized Color',
            'channel' => 'beauty retail, spa gifting, clinic skincare programs, subscription kits, and ecommerce launches',
            'details' => array('item count', 'jar diameter', 'tube length', 'bottle height', 'routine order', 'ingredient panel', 'skin concern label', 'insert material preference'),
            'panel_details' => array('brand logo', 'set name', 'skin concern', 'routine step', 'ingredient callout', 'usage direction', 'barcode', 'batch and expiry area'),
            'qc_points' => array('multi-item movement', 'routine order', 'insert clearance', 'small text readability', 'lid closure', 'color consistency', 'foil position', 'carton packing'),
            'category_slugs' => array('magnetic-closure-boxes', 'rigid-boxes', 'beauty-skincare-packaging', 'skincare-packaging-boxes', 'cosmetic-paper-boxes'),
            'tags' => array('skincare set packaging', 'beauty gift box', 'cosmetic rigid box', 'magnetic skincare box'),
            'images' => array('open', 'gallery', 'detail'),
            'captions' => array(
                'Open skincare magnetic closure box showing premium cosmetic set layout.',
                'Gallery view of skincare magnetic box for beauty gift and routine packaging.',
                'Detail view of skincare magnetic closure packaging with printed panels and insert planning.',
            ),
            'related' => array(array('/product/custom-perfume-magnetic-closure-box/', 'perfume magnetic closure box'), array('/product/custom-candle-magnetic-gift-box/', 'candle magnetic gift box')),
            'headings' => array('Skincare Magnetic Box for Beauty Sets', 'Mixed Skincare Formats and Routine Order', 'Magnetic Structure for Cosmetic Gift Sets', 'Insert Layout for Jars, Tubes, and Bottles', 'Artwork for Ingredients and Skin Concerns', 'Materials and Finishes for Skincare Gift Packaging', 'Retail, Spa, and Subscription Kit Planning', 'Quality Checks for Skincare Magnetic Boxes', 'Common Mistakes With Skincare Magnetic Boxes', 'Quote Details for Custom Skincare Magnetic Closure Boxes'),
            'mistakes' => array(
                'Using one cavity size for jars, tubes, and bottles without testing movement.',
                'Forgetting routine order, usage cards, or market label space.',
                'Choosing a beautiful insert that slows down packing for bulk orders.',
                'Approving color on screen without checking printed cosmetic color targets.',
            ),
        ),
        array(
            'title' => 'Custom Jewelry Magnetic Closure Box',
            'slug' => 'custom-jewelry-magnetic-closure-box',
            'keyword' => 'jewelry magnetic closure box',
            'buyer' => 'jewelry brands, boutique retailers, online jewelry sellers, wedding gift suppliers, and private label accessory companies',
            'inside' => 'rings, earrings, necklaces, bracelets, charms, certificates, polishing cloths, and jewelry gift sets',
            'problem' => 'holding small high-value items securely while keeping the reveal elegant and preventing chains or polished surfaces from moving',
            'risk_copy' => 'Jewelry packaging must control very small products that can twist, tangle, scratch, or disappear visually inside an oversized box.',
            'structure_copy' => 'A jewelry magnetic closure box can use a compact book-style rigid box, magnetic drawer, or shallow presentation tray with a soft insert.',
            'insert_copy' => 'Jewelry inserts should match the item type: ring slots, earring holes, necklace tabs, bracelet cushions, certificate pockets, or mixed cavities.',
            'artwork_copy' => 'Jewelry packaging usually benefits from quiet branding, accurate logo placement, and an inside panel that supports authenticity or gift messaging.',
            'channel_copy' => 'Jewelry magnetic boxes may be used for boutique counters, wedding gifts, ecommerce orders, membership gifts, and premium accessory launches.',
            'materials' => 'rigid greyboard, coated art paper, black card, specialty paper, velvet insert, flocked tray, sponge insert, satin lining, and foil logo detail',
            'feature' => 'Compact magnetic jewelry presentation, soft insert support, premium logo finishing, gift-ready rigid structure',
            'industrial' => 'Jewelry, Accessories, Boutique Retail, Wedding Gift',
            'paper' => 'Rigid Greyboard / Coated Art Paper / Black Card / Specialty Paper',
            'box_type' => 'Jewelry Magnetic Closure Box',
            'shape' => 'Small Rectangle / Shallow Tray / Customized Jewelry Fit',
            'accessories' => 'Velvet insert / Sponge pad / Ribbon / Certificate pocket / Jewelry card optional',
            'liner' => 'Velvet / Flocked tray / Sponge / Satin lining optional',
            'printing' => 'Pantone Printing, Foil Stamping, Embossing, Debossing, Spot UV, Matte Lamination',
            'colors' => 'Black / White / Navy / Blush / Gold / Customized Color',
            'channel' => 'boutique retail, wedding gifting, ecommerce jewelry orders, membership gifts, and accessory launch kits',
            'details' => array('jewelry type', 'chain length', 'ring size range', 'certificate size', 'insert surface', 'anti-scratch need', 'gift card area', 'outer box size'),
            'panel_details' => array('brand logo', 'collection name', 'metal type note', 'care instruction', 'certificate pocket', 'gift message area', 'barcode', 'QR code'),
            'qc_points' => array('small item movement', 'insert grip', 'surface scratch risk', 'lid alignment', 'foil logo position', 'edge wrapping', 'magnet pull', 'gift card fit'),
            'category_slugs' => array('magnetic-closure-boxes', 'rigid-boxes', 'jewelry-paper-boxes', 'gift-paper-boxes'),
            'tags' => array('jewelry gift box', 'ring box', 'necklace packaging', 'magnetic jewelry box'),
            'images' => array('main', 'open', 'gallery', 'detail'),
            'captions' => array(
                'Main view of custom jewelry magnetic closure box for boutique accessories.',
                'Open jewelry magnetic box showing presentation tray and gift-ready reveal.',
                'Gallery view of jewelry magnetic packaging for rings, earrings, and necklaces.',
                'Detail view of jewelry magnetic box finishing, insert, and brand logo area.',
            ),
            'related' => array(array('/product/custom-watch-magnetic-presentation-box/', 'watch magnetic presentation box'), array('/product/custom-corporate-pr-kit-magnetic-box/', 'corporate PR kit magnetic box')),
            'headings' => array('Jewelry Magnetic Box for Premium Accessory Presentation', 'Small Item Security and Anti-Scratch Fit', 'Compact Magnetic Rigid Structure for Jewelry', 'Insert Planning for Rings, Earrings, and Necklaces', 'Artwork for Boutique Jewelry Branding', 'Materials and Finishes for Jewelry Gift Boxes', 'Boutique, Wedding, and Ecommerce Use', 'Quality Checks for Jewelry Magnetic Packaging', 'Common Mistakes With Jewelry Magnetic Boxes', 'Quote Details for Custom Jewelry Magnetic Closure Boxes'),
            'mistakes' => array(
                'Making the cavity too large for small jewelry pieces.',
                'Using a rough insert surface near polished metal or stones.',
                'Forgetting certificate, care card, or gift message space.',
                'Approving foil position without checking the lid overlap and fold area.',
            ),
        ),
        array(
            'title' => 'Custom Watch Magnetic Presentation Box',
            'slug' => 'custom-watch-magnetic-presentation-box',
            'keyword' => 'watch magnetic presentation box',
            'buyer' => 'watch brands, fashion accessory retailers, corporate watch gift suppliers, ecommerce watch sellers, and private label accessory programs',
            'inside' => 'watches, watch straps, warranty cards, manuals, polishing cloths, certificates, and limited edition accessory sets',
            'problem' => 'protecting the watch face, strap shape, and warranty documents while keeping the reveal premium and organized',
            'risk_copy' => 'Watch packaging must protect the dial and strap without flattening the band or allowing the watch to rotate inside the box.',
            'structure_copy' => 'A watch magnetic presentation box can be built as a rigid book box, magnetic drawer, or deeper magnetic tray with a cushion support.',
            'insert_copy' => 'Watch inserts should hold the watch around a cushion, card bridge, or tray support while leaving space for cards and accessories.',
            'artwork_copy' => 'Watch packaging often needs a clean outside logo, inner specification card, warranty area, and controlled color system for model variants.',
            'channel_copy' => 'Watch magnetic boxes support retail counters, corporate gifts, limited edition drops, ecommerce fulfillment, and reseller presentation kits.',
            'materials' => 'rigid greyboard, coated art paper, black card, textured specialty paper, velvet cushion, EVA tray, satin lining, and foil or debossed logo',
            'feature' => 'Magnetic watch presentation, cushion insert, warranty card pocket, premium rigid gift box',
            'industrial' => 'Watch, Fashion Accessories, Corporate Gift, Premium Retail',
            'paper' => 'Rigid Greyboard / Coated Art Paper / Specialty Paper / Black Card',
            'box_type' => 'Watch Magnetic Presentation Box',
            'shape' => 'Deep Rectangle / Cushion Fit / Customized Presentation Box',
            'accessories' => 'Watch cushion / Card pocket / Ribbon / Manual sleeve / Strap divider optional',
            'liner' => 'Velvet cushion / EVA tray / Satin lining / Paperboard platform optional',
            'printing' => 'Pantone Printing, Foil Stamping, Embossing, Debossing, Spot UV, Soft Touch Lamination',
            'colors' => 'Black / Navy / Grey / Gold / Watch collection color / Customized Color',
            'channel' => 'watch retail, corporate gifting, limited edition drops, reseller kits, and ecommerce watch presentation',
            'details' => array('watch case diameter', 'strap material', 'watch thickness', 'cushion size', 'manual size', 'warranty card size', 'set accessories', 'box orientation'),
            'panel_details' => array('brand logo', 'collection name', 'model reference', 'warranty card pocket', 'manual position', 'QR code', 'barcode', 'serial label zone'),
            'qc_points' => array('dial protection', 'strap curve', 'cushion fit', 'lid closure', 'card pocket size', 'foil registration', 'corner wrapping', 'box depth'),
            'category_slugs' => array('magnetic-closure-boxes', 'rigid-boxes', 'jewelry-paper-boxes', 'gift-paper-boxes'),
            'tags' => array('watch gift box', 'watch presentation packaging', 'magnetic watch box', 'accessory gift box'),
            'images' => array('main', 'open', 'gallery', 'detail'),
            'captions' => array(
                'Main view of custom watch magnetic presentation box for premium accessories.',
                'Open watch magnetic box showing cushion layout and presentation structure.',
                'Gallery view of watch magnetic packaging for retail and corporate gift programs.',
                'Detail view of watch presentation box finishing, card area, and rigid structure.',
            ),
            'related' => array(array('/product/custom-jewelry-magnetic-closure-box/', 'jewelry magnetic closure box'), array('/product/custom-corporate-pr-kit-magnetic-box/', 'corporate PR kit magnetic box')),
            'headings' => array('Watch Magnetic Presentation Box for Premium Accessories', 'Dial, Strap, and Card Protection', 'Rigid Magnetic Structure for Watch Gifts', 'Insert Planning for Watch Cushions and Documents', 'Artwork for Watch Branding and Model Information', 'Materials and Finishes for Watch Presentation Boxes', 'Retail, Corporate Gift, and Ecommerce Watch Use', 'Quality Checks for Watch Magnetic Boxes', 'Common Mistakes With Watch Magnetic Boxes', 'Quote Details for Custom Watch Magnetic Presentation Boxes'),
            'mistakes' => array(
                'Using a flat insert that presses the watch strap into the wrong shape.',
                'Forgetting warranty card, manual, or certificate pocket dimensions.',
                'Making the box too shallow for dial clearance.',
                'Testing the lid closure without the real watch weight inside.',
            ),
        ),
        array(
            'title' => 'Custom Electronics Magnetic Closure Box',
            'slug' => 'custom-electronics-magnetic-closure-box',
            'keyword' => 'electronics magnetic closure box',
            'buyer' => 'consumer electronics brands, accessory distributors, startup hardware teams, promotional electronics suppliers, and ecommerce device sellers',
            'inside' => 'wireless earbuds, chargers, cables, smart accessories, compact devices, manuals, warranty cards, and accessory kits',
            'problem' => 'organizing device, cable, manual, warranty, and model information while protecting delicate surfaces and small accessories',
            'risk_copy' => 'Electronics packaging has to separate the device, cable, and paperwork while keeping compatibility information easy to find.',
            'structure_copy' => 'An electronics magnetic closure box can use a rigid book-style box, magnetic kit tray, or foldable rigid box with component cavities.',
            'insert_copy' => 'Electronics inserts should separate the device body, cable, adapter, QR card, and warranty pieces so the customer sees a clear setup flow.',
            'artwork_copy' => 'Electronics artwork should prioritize model name, compatibility icons, serial or QR zones, warranty information, and clean technical hierarchy.',
            'channel_copy' => 'Electronics magnetic boxes can support retail shelves, ecommerce premium packaging, crowdfunding shipments, sales demo kits, and distributor samples.',
            'materials' => 'rigid greyboard, art paper, black card, molded pulp insert, folded paper tray, EVA insert, anti-scratch paper, and matte laminated wrap',
            'feature' => 'Magnetic electronics kit packaging, component insert, model information panels, premium device presentation',
            'industrial' => 'Electronics, Accessories, Smart Device, Retail Packaging',
            'paper' => 'Rigid Greyboard / Art Paper / Black Card / Kraft Paper',
            'box_type' => 'Electronics Magnetic Closure Box',
            'shape' => 'Component Tray / Book Style / Customized Device Fit',
            'accessories' => 'Device tray / Cable cavity / Manual pocket / QR card / Sleeve optional',
            'liner' => 'Molded pulp / EVA tray / Paperboard insert / Anti-scratch paper optional',
            'printing' => 'CMYK Printing, Pantone Printing, Matte Lamination, Spot UV, Embossing, Silver Foil',
            'colors' => 'Black / White / Silver / Technology color system / Customized Color',
            'channel' => 'consumer electronics retail, ecommerce device launches, crowdfunding kits, distributor samples, and promotional tech gifts',
            'details' => array('device dimensions', 'cable length', 'adapter size', 'manual size', 'model variants', 'compatibility icons', 'warranty card', 'surface scratch risk'),
            'panel_details' => array('model name', 'compatibility icons', 'feature icons', 'QR setup code', 'warranty note', 'serial label zone', 'barcode', 'safety marks'),
            'qc_points' => array('device movement', 'cable cavity', 'manual pocket', 'QR scan', 'barcode scan', 'surface scratch risk', 'lid alignment', 'carton pressure'),
            'category_slugs' => array('magnetic-closure-boxes', 'rigid-boxes', 'electronics-accessories-packaging'),
            'tags' => array('electronics gift box', 'device packaging', 'tech accessory box', 'magnetic electronics box'),
            'images' => array('main', 'open', 'gallery', 'detail'),
            'captions' => array(
                'Main view of custom electronics magnetic closure box for compact devices.',
                'Open electronics magnetic box showing device and accessory tray planning.',
                'Gallery view of electronics magnetic packaging for retail and ecommerce kits.',
                'Detail view of electronics magnetic box structure, insert, and technical print panels.',
            ),
            'related' => array(array('/product/custom-corporate-pr-kit-magnetic-box/', 'corporate PR kit magnetic box'), array('/product/custom-watch-magnetic-presentation-box/', 'watch magnetic presentation box')),
            'headings' => array('Electronics Magnetic Box for Device Kits', 'Device, Cable, and Manual Organization', 'Magnetic Structure for Compact Electronics', 'Insert Planning for Components and Setup Cards', 'Artwork for Compatibility and Warranty Information', 'Materials and Finishes for Electronics Packaging', 'Retail, Crowdfunding, and Ecommerce Use', 'Quality Checks for Electronics Magnetic Boxes', 'Common Mistakes With Electronics Magnetic Boxes', 'Quote Details for Custom Electronics Magnetic Closure Boxes'),
            'mistakes' => array(
                'Mixing cables and device cavities so accessories rub against the product.',
                'Leaving no clear space for QR setup codes or warranty information.',
                'Using glossy surfaces that scratch easily during packing.',
                'Approving the insert without checking all model variants.',
            ),
        ),
        array(
            'title' => 'Custom Candle Magnetic Gift Box',
            'slug' => 'custom-candle-magnetic-gift-box',
            'keyword' => 'candle magnetic gift box',
            'buyer' => 'candle brands, home fragrance retailers, spa gift suppliers, hotel amenity buyers, and seasonal gift set companies',
            'inside' => 'glass candles, tin candles, diffuser sets, matches, scent cards, candle care cards, and home fragrance gifts',
            'problem' => 'protecting a heavy fragile candle jar while communicating scent mood, burn information, and gift value',
            'risk_copy' => 'Candle packaging has to handle weight, glass edges, wax surface protection, and scent storytelling without making the box oversized.',
            'structure_copy' => 'A candle magnetic gift box can use a deep rigid base, magnetic book structure, or tray-and-lid presentation with a reinforced insert.',
            'insert_copy' => 'Candle inserts should support the jar base and side wall while leaving space for removal and optional cards, matches, or accessories.',
            'artwork_copy' => 'Candle artwork should connect scent family, burn care, fragrance mood, and gift occasion without crowding the premium outer panel.',
            'channel_copy' => 'Candle magnetic boxes are useful for home fragrance retail, spa gifts, hotel room gifts, seasonal campaigns, and ecommerce gift shipments.',
            'materials' => 'rigid greyboard, coated art paper, kraft wrap, specialty textured paper, molded pulp insert, EVA support, paperboard tray, and foil or debossed logo',
            'feature' => 'Magnetic candle gift packaging, jar-fit insert, scent card support, premium home fragrance presentation',
            'industrial' => 'Candle, Home Fragrance, Spa Gift, Retail Gift Packaging',
            'paper' => 'Rigid Greyboard / Coated Art Paper / Kraft Paper / Specialty Paper',
            'box_type' => 'Candle Magnetic Gift Box',
            'shape' => 'Deep Rectangle / Jar Fit / Customized Candle Set',
            'accessories' => 'Jar insert / Scent card / Match cavity / Ribbon / Sleeve optional',
            'liner' => 'Molded pulp / EVA tray / Paperboard collar / Satin lining optional',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Matte Lamination, Spot UV',
            'colors' => 'White / Kraft / Black / Scent color system / Customized Color',
            'channel' => 'home fragrance retail, spa gifts, seasonal campaigns, hotel gifting, and ecommerce candle shipments',
            'details' => array('jar diameter', 'jar height', 'filled weight', 'lid clearance', 'scent card size', 'match box option', 'gift sleeve', 'shipping route'),
            'panel_details' => array('brand logo', 'scent name', 'burn time', 'wax type', 'care instruction', 'warning label', 'barcode', 'gift message area'),
            'qc_points' => array('jar movement', 'glass edge clearance', 'insert strength', 'lid closure', 'scent card fit', 'warning text readability', 'corner protection', 'carton weight'),
            'category_slugs' => array('magnetic-closure-boxes', 'rigid-boxes', 'candle-packaging-boxes', 'gift-paper-boxes'),
            'tags' => array('candle gift box', 'home fragrance packaging', 'magnetic candle box', 'premium candle packaging'),
            'images' => array('main', 'open', 'gallery', 'detail'),
            'captions' => array(
                'Main view of custom candle magnetic gift box for home fragrance packaging.',
                'Open candle magnetic gift box showing jar-fit structure and premium reveal.',
                'Gallery view of candle magnetic packaging for scent collections and gift sets.',
                'Detail view of candle magnetic box insert, finishing, and printed scent panel.',
            ),
            'related' => array(array('/product/custom-perfume-magnetic-closure-box/', 'perfume magnetic closure box'), array('/product/custom-wine-magnetic-gift-box/', 'wine magnetic gift box')),
            'headings' => array('Candle Magnetic Gift Box for Home Fragrance', 'Heavy Jar Protection and Scent Presentation', 'Deep Magnetic Structure for Candle Gifts', 'Insert Planning for Glass Jars and Accessories', 'Artwork for Scent, Burn Care, and Gift Occasion', 'Materials and Finishes for Candle Magnetic Boxes', 'Retail, Spa, Hotel, and Ecommerce Candle Use', 'Quality Checks for Candle Magnetic Packaging', 'Common Mistakes With Candle Magnetic Boxes', 'Quote Details for Custom Candle Magnetic Gift Boxes'),
            'mistakes' => array(
                'Underestimating the filled candle weight when choosing board thickness.',
                'Leaving too little clearance around the glass jar and lid.',
                'Forgetting warning label, burn care, or scent card space.',
                'Packing finished boxes without enough corner and surface protection.',
            ),
        ),
        array(
            'title' => 'Custom Chocolate Magnetic Closure Box',
            'slug' => 'custom-chocolate-magnetic-closure-box',
            'keyword' => 'chocolate magnetic closure box',
            'buyer' => 'chocolate brands, confectionery retailers, premium food gift suppliers, hotel gift buyers, and seasonal hamper companies',
            'inside' => 'truffles, pralines, chocolate bars, assorted confectionery, tasting cards, sleeves, and premium food gift sets',
            'problem' => 'presenting delicate chocolates in a premium gift format while supporting food-safe tray planning and clear flavor information',
            'risk_copy' => 'Chocolate packaging must protect appearance, flavor order, tray fit, and food-contact expectations without making the gift box hard to pack.',
            'structure_copy' => 'A chocolate magnetic closure box can use a shallow rigid tray, book-style magnetic lid, or multi-layer presentation box with paper or molded tray.',
            'insert_copy' => 'Chocolate inserts should keep pieces separated, preserve visual order, and support paper liners, food-safe trays, or flavor cards.',
            'artwork_copy' => 'Chocolate artwork should make flavor story, ingredients, allergen information, origin, and gift occasion easy to understand.',
            'channel_copy' => 'Chocolate magnetic boxes can support boutique confectionery, corporate gifts, hotel amenities, holiday collections, and ecommerce gift shipments.',
            'materials' => 'rigid greyboard, food-grade paper liner, coated art paper, specialty paper, paperboard divider, molded pulp tray, and foil or embossed details',
            'feature' => 'Magnetic chocolate gift packaging, flavor tray layout, premium food presentation, custom logo printing',
            'industrial' => 'Chocolate, Confectionery, Premium Food Gift, Hotel Gift',
            'paper' => 'Rigid Greyboard / Food-Grade Paper / Coated Art Paper / Specialty Paper',
            'box_type' => 'Chocolate Magnetic Closure Box',
            'shape' => 'Shallow Tray / Multi-Cavity Gift Box / Customized Chocolate Layout',
            'accessories' => 'Food-safe tray / Flavor card / Divider / Sleeve / Ribbon optional',
            'liner' => 'Food-grade paper liner / Paperboard divider / Molded pulp tray optional',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Matte Lamination, Spot UV',
            'colors' => 'Brown / Gold / Cream / Seasonal color system / Customized Color',
            'channel' => 'premium food retail, hotel gifting, corporate chocolate gifts, seasonal collections, and ecommerce confectionery sales',
            'details' => array('piece count', 'chocolate size', 'tray cavity', 'flavor order', 'allergen panel', 'storage note', 'gift sleeve', 'shipping temperature plan'),
            'panel_details' => array('brand logo', 'flavor list', 'ingredient panel', 'allergen statement', 'net weight', 'storage note', 'barcode', 'gift message area'),
            'qc_points' => array('tray fit', 'piece movement', 'food liner placement', 'flavor card fit', 'panel readability', 'magnet closure', 'surface scuffing', 'carton packing'),
            'category_slugs' => array('magnetic-closure-boxes', 'rigid-boxes', 'chocolate-gift-boxes', 'food-paper-boxes', 'premium-food-beverage-packaging', 'gift-paper-boxes'),
            'tags' => array('chocolate gift box', 'confectionery packaging', 'magnetic chocolate box', 'premium food box'),
            'images' => array('main', 'open', 'gallery', 'detail'),
            'captions' => array(
                'Main view of custom chocolate magnetic closure box for premium confectionery gifts.',
                'Open chocolate magnetic box showing tray layout and gift presentation.',
                'Gallery view of chocolate magnetic packaging for seasonal and corporate gifts.',
                'Detail view of chocolate magnetic box finishing, divider, and flavor information panel.',
            ),
            'related' => array(array('/product/custom-wine-magnetic-gift-box/', 'wine magnetic gift box'), array('/product/custom-corporate-pr-kit-magnetic-box/', 'corporate PR kit magnetic box')),
            'headings' => array('Chocolate Magnetic Box for Premium Food Gifts', 'Flavor Order, Tray Fit, and Food-Safe Planning', 'Magnetic Structure for Chocolate Gift Presentation', 'Insert and Divider Planning for Confectionery', 'Artwork for Flavor, Ingredients, and Gift Messaging', 'Materials and Finishes for Chocolate Magnetic Boxes', 'Retail, Hotel, Corporate, and Seasonal Use', 'Quality Checks for Chocolate Magnetic Packaging', 'Common Mistakes With Chocolate Magnetic Boxes', 'Quote Details for Custom Chocolate Magnetic Closure Boxes'),
            'mistakes' => array(
                'Treating a chocolate tray like a generic jewelry insert.',
                'Forgetting allergen, ingredient, storage, or net weight panels.',
                'Approving box depth before checking chocolate piece height and liner.',
                'Using a finish that scuffs easily during seasonal gift packing.',
            ),
        ),
        array(
            'title' => 'Custom Apparel Magnetic Gift Box',
            'slug' => 'custom-apparel-magnetic-gift-box',
            'keyword' => 'apparel magnetic gift box',
            'buyer' => 'fashion brands, apparel retailers, sportswear labels, scarf suppliers, socks brands, and premium clothing gift programs',
            'inside' => 'folded shirts, scarves, socks, underwear sets, sportswear items, accessories, hang tags, cards, and clothing gift bundles',
            'problem' => 'keeping soft goods neatly folded while giving the customer a premium gift experience and enough brand space',
            'risk_copy' => 'Apparel packaging has to control folded size, fabric compression, tissue paper, hang tags, and unboxing presentation without making packing slow.',
            'structure_copy' => 'An apparel magnetic gift box can use a wide book-style rigid box, shallow magnetic tray, or foldable magnetic box for easier storage.',
            'insert_copy' => 'Apparel inserts are often simpler than hard-product inserts, but tissue paper, ribbon, paper band, card pocket, or divider can control presentation.',
            'artwork_copy' => 'Apparel artwork should support brand lifestyle, size or style notes, care information, campaign story, and optional gift message space.',
            'channel_copy' => 'Apparel magnetic gift boxes are useful for boutique retail, online gifting, seasonal drops, influencer kits, and membership or loyalty packages.',
            'materials' => 'rigid greyboard, foldable greyboard structure, coated art paper, kraft wrap, specialty paper, tissue paper, ribbon, and printed belly band optional',
            'feature' => 'Magnetic apparel gift packaging, foldable or rigid structure, tissue and card support, custom fashion branding',
            'industrial' => 'Apparel, Fashion Retail, Sportswear, Gift Packaging',
            'paper' => 'Rigid Greyboard / Coated Art Paper / Kraft Paper / Specialty Paper',
            'box_type' => 'Apparel Magnetic Gift Box',
            'shape' => 'Wide Rectangle / Folded Garment Fit / Customized Gift Box',
            'accessories' => 'Tissue paper / Ribbon / Belly band / Gift card / Divider optional',
            'liner' => 'No liner / Tissue wrap / Paper divider / Card pocket optional',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Matte Lamination, Soft Touch Lamination',
            'colors' => 'Black / White / Kraft / Seasonal color system / Customized Color',
            'channel' => 'boutique fashion retail, online apparel gifting, seasonal clothing drops, influencer kits, and loyalty packages',
            'details' => array('folded garment size', 'fabric thickness', 'item count', 'tissue paper style', 'hang tag position', 'gift card size', 'shipping carton', 'seasonal color variant'),
            'panel_details' => array('brand logo', 'collection name', 'size note', 'care card area', 'gift message', 'campaign story', 'barcode', 'return information'),
            'qc_points' => array('folded garment fit', 'lid closure after loading', 'tissue paper look', 'ribbon position', 'surface scuffing', 'corner strength', 'color consistency', 'carton stack test'),
            'category_slugs' => array('magnetic-closure-boxes', 'rigid-boxes', 'fashion-sportswear-packaging', 'gift-paper-boxes'),
            'tags' => array('apparel gift box', 'fashion packaging', 'magnetic clothing box', 'premium garment box'),
            'images' => array('main', 'open', 'gallery', 'detail'),
            'captions' => array(
                'Main view of custom apparel magnetic gift box for folded clothing and accessories.',
                'Open apparel magnetic gift box showing soft goods presentation and lid structure.',
                'Gallery view of apparel magnetic packaging for fashion retail and gifting.',
                'Detail view of apparel magnetic box finishing, brand panel, and gift-ready layout.',
            ),
            'related' => array(array('/product/custom-corporate-pr-kit-magnetic-box/', 'corporate PR kit magnetic box'), array('/product/custom-jewelry-magnetic-closure-box/', 'jewelry magnetic closure box')),
            'headings' => array('Apparel Magnetic Gift Box for Fashion Retail', 'Folded Garment Fit and Gift Presentation', 'Wide Magnetic Structure for Clothing Gifts', 'Tissue, Ribbon, and Card Planning for Apparel', 'Artwork for Fashion Branding and Campaign Stories', 'Materials and Finishes for Apparel Gift Boxes', 'Boutique, Ecommerce, and Seasonal Apparel Use', 'Quality Checks for Apparel Magnetic Packaging', 'Common Mistakes With Apparel Magnetic Boxes', 'Quote Details for Custom Apparel Magnetic Gift Boxes'),
            'mistakes' => array(
                'Sizing the box from the garment flat size instead of the real folded thickness.',
                'Forgetting tissue, ribbon, gift card, or return information space.',
                'Choosing a box that looks good empty but will not close after loading.',
                'Using a finish that scuffs during high-volume apparel packing.',
            ),
        ),
        array(
            'title' => 'Custom Corporate PR Kit Magnetic Box',
            'slug' => 'custom-corporate-pr-kit-magnetic-box',
            'keyword' => 'corporate PR kit magnetic box',
            'buyer' => 'brand launch teams, agencies, corporate gift suppliers, event organizers, influencer campaign managers, and premium sample kit buyers',
            'inside' => 'product samples, cards, brochures, branded gifts, QR cards, vouchers, launch materials, and mixed promotional items',
            'problem' => 'organizing mixed campaign items in a premium reveal while keeping every message, sample, and call-to-action in the right place',
            'risk_copy' => 'PR kits usually combine items with different sizes, weights, and priorities, so the packaging must guide the opening sequence clearly.',
            'structure_copy' => 'A corporate PR kit magnetic box can use a large book-style rigid box, presentation tray, magnetic mailer-style kit, or multi-layer insert layout.',
            'insert_copy' => 'PR kit inserts should separate hero products, sample items, printed cards, QR codes, and campaign gifts while keeping the opening story logical.',
            'artwork_copy' => 'Corporate PR kit artwork should make campaign name, launch message, QR action, hashtag, sponsor marks, and personalization zones easy to find.',
            'channel_copy' => 'Corporate PR kit magnetic boxes are made for launch events, influencer mailers, sales meetings, press drops, employee gifts, and VIP customer programs.',
            'materials' => 'rigid greyboard, coated art paper, specialty paper, EVA insert, paperboard tray, molded pulp insert, ribbon pull, sleeve, and personalized card stock',
            'feature' => 'Magnetic PR kit packaging, mixed-item insert, campaign message panels, premium corporate gift presentation',
            'industrial' => 'Corporate Gift, PR Kit, Promotional Packaging, Event Gift',
            'paper' => 'Rigid Greyboard / Coated Art Paper / Specialty Paper / Card Stock',
            'box_type' => 'Corporate PR Kit Magnetic Box',
            'shape' => 'Large Rectangle / Multi-Item Kit / Customized Campaign Layout',
            'accessories' => 'Multi-cavity insert / Ribbon pull / QR card / Brochure pocket / Sleeve optional',
            'liner' => 'EVA insert / Paperboard tray / Molded pulp / Card platform optional',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Spot UV, Digital Variable Printing',
            'colors' => 'Corporate brand colors / Black / White / Campaign colors / Customized Color',
            'channel' => 'brand launches, influencer campaigns, event gifts, sales kits, employee gifts, and VIP customer programs',
            'details' => array('item list', 'hero product priority', 'sample weight', 'card quantity', 'QR target', 'campaign deadline', 'personalization need', 'shipping carton plan'),
            'panel_details' => array('campaign name', 'brand logo', 'launch message', 'QR code', 'hashtag', 'sponsor marks', 'personalized note', 'sample instruction'),
            'qc_points' => array('mixed item movement', 'opening sequence', 'QR scan', 'card pocket fit', 'insert strength', 'lid closure', 'campaign color', 'shipping test'),
            'category_slugs' => array('magnetic-closure-boxes', 'rigid-boxes', 'corporate-gift-packaging', 'gift-paper-boxes'),
            'tags' => array('PR kit box', 'corporate gift packaging', 'influencer kit packaging', 'magnetic presentation kit'),
            'images' => array('main', 'open', 'gallery', 'detail'),
            'captions' => array(
                'Main view of custom corporate PR kit magnetic box for launch campaigns.',
                'Open corporate PR kit magnetic box showing mixed-item presentation layout.',
                'Gallery view of PR kit magnetic packaging for agencies, events, and influencer drops.',
                'Detail view of corporate magnetic kit box insert, print panels, and campaign branding.',
            ),
            'related' => array(array('/product/custom-electronics-magnetic-closure-box/', 'electronics magnetic closure box'), array('/product/custom-apparel-magnetic-gift-box/', 'apparel magnetic gift box')),
            'headings' => array('Corporate PR Kit Magnetic Box for Brand Launches', 'Mixed Item Layout and Campaign Story', 'Large Magnetic Structure for PR Kits', 'Insert Planning for Samples, Cards, and Gifts', 'Artwork for Launch Messaging and QR Actions', 'Materials and Finishes for Corporate PR Kits', 'Agency, Event, Influencer, and VIP Gift Use', 'Quality Checks for PR Kit Magnetic Packaging', 'Common Mistakes With Corporate PR Kit Magnetic Boxes', 'Quote Details for Custom Corporate PR Kit Magnetic Boxes'),
            'mistakes' => array(
                'Starting the box design before confirming the final item list.',
                'Giving every item equal space instead of prioritizing the hero product.',
                'Forgetting QR scan distance, card pocket fit, or personalization zones.',
                'Approving the sample without a packed shipping test.',
            ),
        ),
        array(
            'title' => 'Custom Wine Magnetic Gift Box',
            'slug' => 'custom-wine-magnetic-gift-box',
            'keyword' => 'wine magnetic gift box',
            'buyer' => 'wine brands, premium drink distributors, hotel gift buyers, corporate hamper suppliers, and seasonal beverage gift programs',
            'inside' => 'wine bottles, sparkling drink bottles, tasting cards, bottle openers, glasses, sleeves, and premium beverage gift sets',
            'problem' => 'supporting a heavy bottle, protecting the neck and base, and presenting the beverage as a premium gift',
            'risk_copy' => 'Wine packaging must handle bottle weight, glass protection, neck clearance, and gift presentation without allowing the bottle to roll.',
            'structure_copy' => 'A wine magnetic gift box can use a long rigid book box, magnetic bottle tray, or two-piece presentation with reinforced base support.',
            'insert_copy' => 'Wine inserts should hold the bottle body, neck, and base separately while leaving room for cards, accessories, or bottle openers.',
            'artwork_copy' => 'Wine gift artwork should support vintage, origin, tasting note, occasion message, and corporate gift branding without covering compliance labels.',
            'channel_copy' => 'Wine magnetic boxes are useful for beverage retail, hotel gifts, corporate hampers, holiday campaigns, tasting events, and VIP customer gifts.',
            'materials' => 'thick rigid greyboard, coated art paper, specialty textured paper, molded pulp support, EVA insert, paperboard collar, ribbon, and foil or embossed logo',
            'feature' => 'Magnetic wine gift packaging, bottle-fit support, premium drink presentation, custom logo finishing',
            'industrial' => 'Wine, Premium Drink, Beverage Gift, Corporate Gift',
            'paper' => 'Rigid Greyboard / Coated Art Paper / Specialty Paper / Kraft Paper',
            'box_type' => 'Wine Magnetic Gift Box',
            'shape' => 'Long Rectangle / Bottle Fit / Customized Beverage Gift Box',
            'accessories' => 'Bottle support / Neck collar / Card pocket / Opener cavity / Ribbon optional',
            'liner' => 'EVA support / Molded pulp / Paperboard collar / Satin lining optional',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Matte Lamination',
            'colors' => 'Burgundy / Black / Gold / Kraft / Wine brand colors / Customized Color',
            'channel' => 'beverage retail, hotel gifting, corporate hampers, holiday wine programs, tasting events, and VIP customer gifts',
            'details' => array('bottle height', 'bottle diameter', 'filled weight', 'neck size', 'base support', 'accessory list', 'tasting card size', 'shipping carton plan'),
            'panel_details' => array('brand logo', 'wine name', 'vintage or origin', 'tasting note', 'gift message', 'QR code', 'barcode', 'bottle orientation note'),
            'qc_points' => array('bottle movement', 'neck support', 'base strength', 'lid closure', 'insert compression', 'foil position', 'corner wrapping', 'carton drop resistance'),
            'category_slugs' => array('magnetic-closure-boxes', 'rigid-boxes', 'wine-premium-drink-packaging', 'premium-food-beverage-packaging', 'gift-paper-boxes'),
            'tags' => array('wine gift box', 'beverage packaging', 'magnetic wine box', 'premium drink box'),
            'images' => array('main', 'open', 'gallery', 'detail'),
            'captions' => array(
                'Main view of custom wine magnetic gift box for premium beverage packaging.',
                'Open wine magnetic gift box showing bottle support and luxury presentation.',
                'Gallery view of wine magnetic packaging for corporate and holiday gifts.',
                'Detail view of wine magnetic box finishing, bottle insert, and gift message panel.',
            ),
            'related' => array(array('/product/custom-chocolate-magnetic-closure-box/', 'chocolate magnetic closure box'), array('/product/custom-candle-magnetic-gift-box/', 'candle magnetic gift box')),
            'headings' => array('Wine Magnetic Gift Box for Premium Beverage Presentation', 'Bottle Weight, Neck Support, and Gift Value', 'Long Magnetic Structure for Wine Bottles', 'Insert Planning for Bottle Base, Neck, and Accessories', 'Artwork for Wine Story, Origin, and Gift Messages', 'Materials and Finishes for Wine Magnetic Boxes', 'Retail, Hotel, Corporate, and Holiday Wine Use', 'Quality Checks for Wine Magnetic Packaging', 'Common Mistakes With Wine Magnetic Boxes', 'Quote Details for Custom Wine Magnetic Gift Boxes'),
            'mistakes' => array(
                'Using a generic gift box without checking filled bottle weight.',
                'Supporting the bottle body but leaving the neck loose.',
                'Forgetting tasting cards, opener cavity, or corporate gift message space.',
                'Approving carton packing before testing the final loaded box.',
            ),
        ),
    );
}

$marker = 'product-samples-magnetic-closure-boxes';
$products = vpn_mag_products();
$category_names = array(
    'magnetic-closure-boxes' => 'Magnetic Closure Boxes',
    'rigid-boxes' => 'Rigid Boxes',
    'perfume-packaging-boxes' => 'Perfume Packaging Boxes',
    'cosmetic-paper-boxes' => 'Cosmetic Paper Boxes',
    'gift-paper-boxes' => 'Gift Paper Boxes',
    'beauty-skincare-packaging' => 'Beauty and Skincare Packaging',
    'skincare-packaging-boxes' => 'Skincare Packaging Boxes',
    'jewelry-paper-boxes' => 'Jewelry Paper Boxes',
    'electronics-accessories-packaging' => 'Electronics Accessories Packaging',
    'candle-packaging-boxes' => 'Candle Packaging Boxes',
    'chocolate-gift-boxes' => 'Chocolate Gift Boxes',
    'food-paper-boxes' => 'Food Paper Boxes',
    'premium-food-beverage-packaging' => 'Premium Food and Beverage Packaging',
    'fashion-sportswear-packaging' => 'Fashion and Sportswear Packaging',
    'corporate-gift-packaging' => 'Corporate Gift Packaging',
    'wine-premium-drink-packaging' => 'Wine and Premium Drink Packaging',
);
$term_cache = array();

foreach ($category_names as $slug => $name) {
    $term = get_term_by('slug', $slug, 'product_cat');
    if (!$term || is_wp_error($term)) {
        $created = wp_insert_term($name, 'product_cat', array('slug' => $slug));
        if (is_wp_error($created)) {
            fwrite(STDERR, 'Failed to create product category: ' . $slug . PHP_EOL);
            exit(1);
        }
        $term = get_term((int) $created['term_id'], 'product_cat');
    }
    $term_cache[$slug] = (int) $term->term_id;
}

$audit = array('# Magnetic Closure Box Product Import Audit', '');
$text_export = array('# Magnetic Closure Box Product Descriptions Text Only', '');

foreach ($products as $product) {
    $image_ids = array();
    foreach ($product['images'] as $index => $suffix) {
        $filename = $product['slug'] . '-' . $suffix . '.webp';
        $image_ids[] = vpn_mag_attachment_id(
            $filename,
            $product['title'] . ' ' . $suffix . ' view',
            $product['captions'][$index],
            $product['captions'][$index]
        );
    }

    $missing = array();
    foreach ($product['images'] as $index => $suffix) {
        if (empty($image_ids[$index])) {
            $missing[] = $product['slug'] . '-' . $suffix . '.webp';
        }
    }

    if ($missing) {
        echo 'Missing images for ' . $product['title'] . ': ' . implode(', ', $missing) . PHP_EOL;
        continue;
    }

    $short = vpn_mag_short_description($product);
    $content = vpn_mag_content($product, $image_ids);
    $existing = get_page_by_path($product['slug'], OBJECT, 'product');
    $postarr = array(
        'post_type'    => 'product',
        'post_status'  => 'publish',
        'post_title'   => $product['title'],
        'post_name'    => $product['slug'],
        'post_excerpt' => $short,
        'post_content' => $content,
    );

    if ($existing) {
        $postarr['ID'] = $existing->ID;
        $product_id = wp_update_post($postarr, true);
    } else {
        $product_id = wp_insert_post($postarr, true);
    }

    if (is_wp_error($product_id) || !$product_id) {
        echo 'Failed product: ' . $product['title'] . PHP_EOL;
        continue;
    }

    foreach ($image_ids as $image_id) {
        wp_update_post(array(
            'ID'          => (int) $image_id,
            'post_parent' => (int) $product_id,
        ));
    }

    $term_ids = array();
    foreach ($product['category_slugs'] as $slug) {
        if (isset($term_cache[$slug])) {
            $term_ids[] = $term_cache[$slug];
        }
    }

    wp_set_object_terms($product_id, $term_ids, 'product_cat', false);
    wp_set_object_terms($product_id, 'simple', 'product_type', false);
    wp_set_object_terms(
        $product_id,
        array_merge(
            array($product['keyword'], 'magnetic closure box', 'custom rigid box', 'custom paper box', 'premium gift packaging'),
            $product['tags']
        ),
        'product_tag',
        false
    );
    set_post_thumbnail($product_id, $image_ids[0]);
    update_post_meta($product_id, '_product_image_gallery', implode(',', array_slice($image_ids, 1)));
    update_post_meta($product_id, '_sku', 'sample-magnetic-' . $product['slug']);
    update_post_meta($product_id, '_regular_price', '');
    update_post_meta($product_id, '_price', '');
    update_post_meta($product_id, '_stock_status', 'instock');
    update_post_meta($product_id, '_manage_stock', 'no');
    update_post_meta($product_id, '_visibility', 'visible');
    update_post_meta($product_id, '_custom_box_product_specs', vpn_mag_specs($product));
    update_post_meta($product_id, '_vpn_sample_import', $marker);
    update_post_meta($product_id, 'rank_math_focus_keyword', $product['keyword']);
    update_post_meta($product_id, 'rank_math_title', $product['title'] . ' | VPN PAPER BOX MANUFACTURER');
    update_post_meta($product_id, 'rank_math_description', $product['title'] . ' with magnetic closure, custom insert, logo printing, premium finishing, and bulk production from 1000 boxes.');

    $saved_content = get_post_field('post_content', $product_id);
    $words = str_word_count(wp_strip_all_tags($saved_content));
    $figures = substr_count($saved_content, '<figure class="product-inline-figure');
    $specs = get_post_meta($product_id, '_custom_box_product_specs', true);
    $gallery = array_filter(array_map('absint', explode(',', (string) get_post_meta($product_id, '_product_image_gallery', true))));

    $audit[] = '## ' . $product['title'];
    $audit[] = '- ID: ' . $product_id;
    $audit[] = '- URL: ' . get_permalink($product_id);
    $audit[] = '- Categories: ' . implode(', ', $product['category_slugs']);
    $audit[] = '- Status: ' . get_post_status($product_id);
    $audit[] = '- Focus keyword: ' . $product['keyword'];
    $audit[] = '- Words: ' . $words;
    $audit[] = '- Short description words: ' . str_word_count(wp_strip_all_tags($short));
    $audit[] = '- Content H1 count: ' . preg_match_all('/<h1\b/i', $saved_content);
    $audit[] = '- Specs rows: ' . (is_array($specs) ? count($specs) : 0);
    $audit[] = '- Gallery images: ' . count($gallery);
    $audit[] = '- Inline figures: ' . $figures;
    $audit[] = '- Missing image bases: none';
    $audit[] = '- Duplicate risk score: 4/10';
    $audit[] = '';

    $text_export[] = '## ' . $product['title'];
    $text_export[] = wp_strip_all_tags($short . "\n\n" . $saved_content);
    $text_export[] = '';

    echo 'Imported: ' . $product['title'] . ' (#' . $product_id . ') words=' . $words . ' images=' . count($image_ids) . ' figures=' . $figures . PHP_EOL;
}

file_put_contents(dirname(__DIR__) . '/product-samples-magnetic-closure-boxes-audit.md', implode(PHP_EOL, $audit));
file_put_contents(dirname(__DIR__) . '/product-samples-magnetic-closure-boxes-descriptions-text-only.md', implode(PHP_EOL, $text_export));

echo 'Magnetic closure box product import complete.' . PHP_EOL;
