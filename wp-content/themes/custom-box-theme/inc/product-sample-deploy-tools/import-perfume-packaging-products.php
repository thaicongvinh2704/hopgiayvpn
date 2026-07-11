<?php
/**
 * Import July 2026 perfume packaging products from the 40 uploaded source images.
 *
 * Run:
 *   php tools/import-perfume-packaging-products.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

function vpn_perfume_link($url, $anchor)
{
    return '<a href="' . esc_url(home_url($url)) . '">' . esc_html($anchor) . '</a>';
}

function vpn_perfume_file_base($filename)
{
    return preg_replace('/\.[^.]+$/', '', basename($filename));
}

function vpn_perfume_find_attachment_by_base($filename_base)
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
        if (0 === strcasecmp(vpn_perfume_file_base($attached_file), $filename_base)) {
            return (int) $attachment_id;
        }
    }

    return 0;
}

function vpn_perfume_import_attachment_from_uploads($filename, $alt, $title, $caption)
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

function vpn_perfume_attachment_id($filename, $alt, $title, $caption)
{
    $filename_base = vpn_perfume_file_base($filename);
    $attachment_id = vpn_perfume_find_attachment_by_base($filename_base);

    if (!$attachment_id) {
        $attachment_id = vpn_perfume_import_attachment_from_uploads($filename, $alt, $title, $caption);
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

function vpn_perfume_specs($product)
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

function vpn_perfume_section($heading, $paragraphs)
{
    $html = '<h2>' . esc_html($heading) . '</h2>';
    foreach ($paragraphs as $paragraph) {
        $html .= '<p>' . $paragraph . '</p>';
    }

    return $html;
}

function vpn_perfume_inline_image($attachment_id, $caption, $narrow = false)
{
    $image = wp_get_attachment_image($attachment_id, 'large', false, array('loading' => 'lazy'));
    if (!$image) {
        return '';
    }

    return '<figure class="product-inline-figure product-inline-figure-small' . ($narrow ? ' is-narrow' : '') . '">' .
        $image . '<figcaption>' . esc_html($caption) . '</figcaption></figure>';
}

function vpn_perfume_sentence_list($items)
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

function vpn_perfume_short_description($product)
{
    return $product['title'] . ' is a custom perfume packaging solution for ' . $product['inside'] . '. It is developed for ' . $product['buyer'] . ' that need accurate bottle fit, fragrance-brand presentation, custom logo printing, insert planning, retail information panels, and export-ready carton packing. The structure helps solve ' . $product['problem'] . '. Size, paper material, board thickness, insert material, surface finishing, color system, and artwork layout can be adjusted for private label perfume lines, launch gifts, discovery sets, retail shelves, and ecommerce orders. MOQ starts from 1000 boxes.';
}

function vpn_perfume_content($product, $image_ids)
{
    $perfume_link = vpn_perfume_link('/product-category/perfume-packaging-boxes/', 'perfume packaging boxes');
    $cosmetic_link = vpn_perfume_link('/product-category/cosmetic-paper-boxes/', 'cosmetic paper boxes');
    $beauty_link = vpn_perfume_link('/product-category/beauty-skincare-packaging/', 'beauty and skincare packaging');
    $structure_link = vpn_perfume_link('/how-to-choose-paper-box-structure-for-perfume-packaging/', 'perfume paper box structure guide');
    $material_link = vpn_perfume_link('/how-to-choose-paper-material-for-product-packaging/', 'paper material selection for product packaging');
    $artwork_link = vpn_perfume_link('/how-to-prepare-artwork-for-printed-paper-boxes/', 'print-ready artwork for paper boxes');
    $finish_link = vpn_perfume_link('/foil-stamping-and-embossing-for-paper-packaging/', 'foil stamping and embossing for packaging');
    $lamination_link = vpn_perfume_link('/matte-vs-gloss-lamination-for-packaging/', 'matte and gloss lamination options');
    $quote_link = vpn_perfume_link('/contact/#quote', 'request a perfume packaging quote');
    $related_one = vpn_perfume_link($product['related'][0][0], $product['related'][0][1]);
    $related_two = vpn_perfume_link($product['related'][1][0], $product['related'][1][1]);
    $details = vpn_perfume_sentence_list($product['details']);
    $panel_details = vpn_perfume_sentence_list($product['panel_details']);
    $qc_points = vpn_perfume_sentence_list($product['qc_points']);

    $html = vpn_perfume_section($product['headings'][0], array(
        $product['title'] . ' is built for ' . $product['buyer'] . ' that need a paper package for ' . $product['inside'] . '. Perfume packaging has to do several jobs at once: protect a glass bottle, control cap clearance, present the scent as desirable, and keep legally required information organized without making the box feel crowded.',
        'This product belongs to the ' . $perfume_link . ' range and can also support ' . $cosmetic_link . ' and broader ' . $beauty_link . ' projects when a buyer needs a coordinated fragrance, lotion, skincare, or gift set packaging family. The main packaging challenge is ' . $product['problem'] . ', so the structure should be planned around the real filled bottle instead of a generic carton size.',
        'Useful RFQ details include ' . $details . '. These details affect cavity tolerance, board selection, flap depth, tray height, printing layout, sample cost, and export carton planning. A clear product brief helps the factory quote a box that can be produced repeatedly, not only a sample that looks correct in one photo.',
    ));

    if (!empty($image_ids[0])) {
        $html .= vpn_perfume_inline_image($image_ids[0], $product['captions'][0], true);
    }

    $html .= vpn_perfume_section($product['headings'][1], array(
        $product['problem_copy'] . ' A perfume bottle is often small but dense, and a decorative cap can be taller or wider than the bottle body. If the internal space is too loose, the bottle can rotate, scuff the label, or hit the top panel. If the fit is too tight, the customer may pull from the cap and damage the pump or spray head.',
        'For ' . strtolower($product['title']) . ', the package should answer both protection and perception questions. How does the bottle stay still? How does the customer understand the scent, volume, collection, and brand level quickly? How will the box behave when it is packed in master cartons, handled by distributors, photographed for ecommerce, or opened as a gift?',
        'The sales channel changes the risk. A department-store SKU may need strong front-panel recognition and barcode discipline. A launch gift may need a slower reveal and more inner storytelling. An ecommerce fragrance package may need more attention to corner protection, cap clearance, and outer carton packing because glass perfume can be expensive to replace.',
    ));

    $html .= vpn_perfume_section($product['headings'][2], array(
        $product['structure_copy'] . ' Recommended structures include ' . $product['structure_options'] . '. The best choice depends on bottle height, cap shape, filled weight, desired opening experience, packing speed, and whether the product will be sold as a single bottle, small discovery item, refillable unit, or multi-piece fragrance set.',
        $product['tolerance_note'] . ' The ' . $structure_link . ' is a useful reference before approving a dieline because folding cartons, rigid boxes, drawer boxes, magnetic boxes, and lid-and-base structures all solve different perfume packaging problems. A structure that feels premium may still fail if the tray depth, cap clearance, or lid tolerance is wrong.',
        'Sampling should check repeated opening, bottle removal, insert friction, panel alignment, and master carton packing. The buyer should test the package with the real filled bottle or an accurate dummy, because an empty display bottle does not show the same weight, cap pressure, or shipping behavior.',
    ));

    if (!empty($image_ids[1])) {
        $html .= vpn_perfume_inline_image($image_ids[1], $product['captions'][1]);
    }

    $html .= vpn_perfume_section($product['headings'][3], array(
        $product['insert_copy'] . ' Insert design should hold the bottle body while leaving enough space for the cap, atomizer, label, and customer fingers. The insert should stop movement without making removal difficult, especially when the perfume bottle has a glossy label, metallic cap, shoulder shape, or fragile decorative edge.',
        'Insert options may include folded paperboard, EVA foam, EPE foam, molded pulp, rigid greyboard platforms, paper collars, tray cavities, card slots, and divider systems. The right choice depends on the bottle finish and the buyer position. A mass retail perfume carton may prefer recyclable paperboard, while a luxury fragrance launch may need EVA or a wrapped rigid tray for a more controlled reveal.',
        'Packing speed matters for B2B orders. A beautiful insert that requires too much hand adjustment can slow down a 1000-box production run. The best insert locks consistently, keeps the bottle facing the right direction, protects the cap area, and gives enough finger clearance so customers remove the product naturally.',
    ));

    $html .= vpn_perfume_section($product['headings'][4], array(
        'Perfume artwork must organize ' . $panel_details . '. The front panel should communicate scent name, brand level, and collection quickly. Side and back panels can carry ingredient information, warning text, barcode, batch area, importer details, volume, QR code, and any market-specific label space.',
        $product['artwork_copy'] . ' Artwork should be prepared on the final dieline with bleed, safe zones, fold direction, barcode position, and finish masks clearly marked. The ' . $artwork_link . ' guide is useful because fragrance packaging often uses small type, fine borders, metallic logo effects, and subtle color fields that can shift after lamination or foil stamping.',
        'If the fragrance line includes many scents, keep a controlled version system. The structure, logo position, text hierarchy, and barcode area can stay fixed while scent color, fragrance name, ingredient panel, or collection label changes. This avoids a production mix-up when several SKUs are printed and packed at the same time.',
    ));

    if (!empty($image_ids[2])) {
        $html .= vpn_perfume_inline_image($image_ids[2], $product['captions'][2], true);
    }

    $html .= vpn_perfume_section($product['headings'][5], array(
        'Material options include ' . $product['materials'] . '. The best paper choice depends on bottle weight, print detail, brand position, sustainability direction, and whether the package needs to feel light and retail-efficient or heavy and gift-ready. Buyers can compare common board behavior in the ' . $material_link . ' guide before finalizing the sample.',
        $product['finish_strategy'] . ' Finishing can include matte lamination, gloss lamination, soft-touch film, textured paper, foil stamping, embossing, debossing, spot UV, Pantone color matching, inner printing, and ribbon or sleeve details. Each finish should support the fragrance story, not hide weak structure or make the box difficult to glue.',
        'For premium surface decisions, review ' . $finish_link . ' and ' . $lamination_link . ' before artwork approval. Foil and embossing need separate mask layers and enough distance from fold lines. Lamination can change color appearance and may affect glue areas if the dieline is not planned correctly.',
    ));

    $html .= vpn_perfume_section($product['headings'][6], array(
        $product['channel_copy'] . ' Perfume packages can move through beauty counters, duty-free shelves, boutique gift shops, hotel amenity programs, sample subscriptions, influencer kits, distributor showrooms, and ecommerce shipments. Each channel changes the amount of information, protection, and presentation the buyer should prioritize.',
        'For retail shelves, the package needs consistent front-panel alignment, clean barcode placement, and strong scent recognition. For gifts, the package needs a reveal sequence and a clean insert. For ecommerce, the package may need an outer shipper or stronger corner packing so the glass bottle and printed surfaces survive parcel handling.',
        'This product can be compared with ' . $related_one . ' or ' . $related_two . ' when the buyer is building a complete beauty packaging range. Internal packaging families should share enough brand language to feel connected, but each structure should still solve its own product fit and handling problem.',
    ));

    if (!empty($image_ids[3])) {
        $html .= vpn_perfume_inline_image($image_ids[3], $product['captions'][3]);
    }

    $html .= vpn_perfume_section($product['headings'][7], array(
        $product['procurement_note'] . ' A practical perfume packaging brief should include bottle dimensions, cap height, filled weight, number of scents, target market, retail channel, artwork status, insert requirement, finishing direction, order quantity, and delivery deadline. These details help the factory recommend a realistic structure and sampling path.',
        'Sample approval should check product fit, bottle movement, cap pressure, opening direction, small text readability, barcode scan, color target, finish registration, insert depth, and carton packing. A sample that only looks attractive when empty is not enough for perfume packaging because the filled bottle changes balance and pressure points.',
        'For export orders, master carton planning should protect corners, dark papers, foil surfaces, window areas, and insert shape. If boxes are packed too tightly, corners can crush or surfaces can rub. If they are too loose, the perfume boxes move during transport. Carton quantity, orientation, and protective interleaving should be confirmed before mass production.',
    ));

    $html .= vpn_perfume_section($product['headings'][8], array(
        'Quality control should check ' . $qc_points . '. Perfume packaging is handled closely, so small defects are easy to notice: crooked foil, dust under a window, weak glue, poor tray fit, scratched dark paper, uneven lid gap, or a bottle that does not face forward when opened.',
        'The factory should compare bulk goods with the approved production sample for board thickness, paper texture, printed color, foil pressure, emboss depth, insert holding strength, lid movement, and final packed appearance. If several scent variants are produced together, QC should also confirm barcode, scent name, color code, and carton label accuracy.',
        'For reorder programs, keep one approved sample with the buyer and one with the factory. Perfume packaging depends on tactile details, and those details can drift over time if the team relies only on digital artwork. A physical reference protects the standard when the same box is reordered or extended to new scents.',
    ));

    $html .= '<h2>' . esc_html($product['headings'][9]) . '</h2><ul>';
    foreach ($product['mistakes'] as $mistake) {
        $html .= '<li>' . esc_html($mistake) . '</li>';
    }
    $html .= '</ul>';

    $html .= vpn_perfume_section($product['quote_heading'], array(
        'For an accurate quotation, send ' . $product['quote_details'] . '. Photos, reference samples, bottle drawings, existing artwork, or a filled sample can help the packaging team check structure, insert, material, and finishing before tooling or mass production.',
        'VPN Paper Box Manufacturer can customize size, paper material, insert, logo printing, surface finishing, inner structure, and export packing for fragrance and beauty buyers.',
        'MOQ starts from 1000 boxes. Send your project details to ' . $quote_link . ' and include the bottle size, cap height, target quantity, preferred box style, and artwork status so the sample can be reviewed around real production conditions.',
    ));

    return $html;
}

function vpn_perfume_products()
{
    return array(
        array(
            'title' => 'CUSTOM FOLDING CARTON PERFUME BOX',
            'slug' => 'custom-folding-carton-perfume-box',
            'keyword' => 'folding carton perfume box',
            'buyer' => 'fragrance brands, private label perfume factories, beauty distributors, duty-free suppliers, and high-volume retail buyers',
            'inside' => '30ml, 50ml, and 100ml perfume bottles, cologne bottles, eau de parfum cartons, and boxed fragrance retail SKUs',
            'problem' => 'protecting a glass perfume bottle in a lightweight retail carton while keeping scent name, volume, barcode, and warning information readable',
            'problem_copy' => 'Folding carton perfume packaging is often chosen for retail efficiency, but the carton still has to protect glass and preserve the premium impression of the scent.',
            'structure_copy' => 'A folding carton perfume box usually starts with a straight tuck, reverse tuck, auto-lock bottom, sleeve carton, or reinforced paperboard carton.',
            'insert_copy' => 'For a folding carton perfume box, the insert should be efficient, recyclable when possible, and easy for packing workers to place inside the carton.',
            'artwork_copy' => 'Folding carton artwork should keep the front panel elegant while using side and back panels for practical retail information.',
            'channel_copy' => 'Folding carton perfume boxes are useful for beauty chains, mass retail, distributor programs, travel retail, and private label fragrance collections.',
            'procurement_note' => 'For this carton format, procurement should confirm paperboard GSM, bottle height, cap clearance, bottom lock strength, carton packing count, and whether an insert is required.',
            'tolerance_note' => 'The carton should be tight enough to stop bottle movement but not so tight that the bottle scratches the inner panels or slows packing.',
            'finish_strategy' => 'A folding carton can still feel premium with controlled foil, embossing, matte lamination, spot UV, or a textured paper effect on the logo panel.',
            'structure_options' => 'straight tuck cartons, reverse tuck cartons, auto-lock bottom cartons, sleeve cartons, paperboard cartons with folded inserts, and retail cartons with hang or barcode panels',
            'materials' => 'SBS paperboard, ivory board, duplex board, coated art paper, kraft paperboard, folded paperboard inserts, and optional anti-scuff lamination',
            'feature' => 'Lightweight perfume retail carton, bottle-fit paper insert, custom logo printing, efficient bulk packing',
            'industrial' => 'Perfume, Fragrance, Beauty Retail, Cosmetic Packaging',
            'paper' => 'SBS Paperboard / Ivory Board / Duplex Board / Coated Art Paper',
            'box_type' => 'Folding Carton Perfume Box',
            'shape' => 'Vertical Rectangle / Bottle Fit / Customized Carton',
            'accessories' => 'Folded paper insert / Leaflet / Barcode panel / Sleeve optional',
            'liner' => 'Paperboard insert / No liner / Molded pulp optional',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Spot UV, Matte Lamination',
            'colors' => 'White / Black / Gold / Fragrance color system / Customized Color',
            'category_slugs' => array('perfume-packaging-boxes', 'cosmetic-paper-boxes', 'beauty-skincare-packaging', 'folding-carton-boxes', 'custom-paper-boxes'),
            'tags' => array('perfume box', 'fragrance carton', 'folding carton packaging', 'beauty retail packaging'),
            'images' => array('Custom-Folding-Carton-Perfume-Box.webp', 'Custom-Folding-Carton-Perfume-Box-2.webp', 'Custom-Folding-Carton-Perfume-Box-3.webp', 'Custom-Folding-Carton-Perfume-Box-4.webp'),
            'captions' => array(
                'Custom folding carton perfume box for retail fragrance bottles.',
                'Side view of folding carton perfume packaging with printed panels.',
                'Perfume carton detail showing paperboard structure and finish.',
                'Folding perfume box set for fragrance retail and distributor orders.',
            ),
            'details' => array('bottle height', 'cap diameter', 'filled weight', 'carton style', 'barcode panel', 'warning copy', 'insert preference', 'retail shelf direction'),
            'panel_details' => array('brand logo', 'scent name', 'bottle volume', 'fragrance concentration', 'ingredient or warning text', 'barcode', 'batch code area', 'market label space'),
            'qc_points' => array('carton squareness', 'bottom lock strength', 'bottle movement', 'cap clearance', 'small text readability', 'foil position', 'barcode scan', 'master carton packing'),
            'related' => array(array('/product/custom-perfume-box-with-paper-insert/', 'perfume box with paper insert'), array('/product/custom-mini-perfume-bottle-packaging-box/', 'mini perfume bottle packaging box')),
            'headings' => array('Folding Carton Perfume Box for Retail Fragrance Lines', 'Retail Carton Risks for Glass Perfume Bottles', 'Paperboard Carton Structure and Bottle Fit', 'Folded Insert Planning for Perfume Cartons', 'Artwork Layout for Scent Names and Retail Labels', 'Materials and Finishes for Folding Perfume Cartons', 'Retail, Travel, and Distributor Use Cases', 'Procurement Details Before Ordering Perfume Cartons', 'Quality Checks for Folding Carton Perfume Packaging', 'Common Mistakes With Folding Carton Perfume Boxes'),
            'quote_heading' => 'Quote Details for Custom Folding Carton Perfume Boxes',
            'quote_details' => 'bottle size, cap height, filled weight, carton style, paperboard thickness, artwork file, print colors, insert requirement, quantity, and retail market',
            'mistakes' => array(
                'Choosing thin paperboard for a heavy glass bottle without testing bottom strength.',
                'Approving artwork before confirming barcode size, warning text, and market label space.',
                'Making the inner carton too tight around the cap and spray head.',
                'Using premium finishing on the front panel while ignoring carton squareness and packing speed.',
            ),
            'duplicate_risk' => '4/10',
        ),
        array(
            'title' => 'CUSTOM LUXURY PERFUME BOX WITH MAGNETIC CLOSURE',
            'slug' => 'custom-luxury-perfume-box-with-magnetic-closure',
            'keyword' => 'luxury perfume box with magnetic closure',
            'buyer' => 'luxury fragrance houses, boutique perfume brands, influencer launch teams, hotel gift buyers, and premium beauty distributors',
            'inside' => 'premium glass perfume bottles, limited fragrance launches, atomizer gifts, scent cards, and luxury perfume gift presentations',
            'problem' => 'creating a premium reveal while protecting a fragile perfume bottle, cap, label, and inner presentation surface',
            'problem_copy' => 'A magnetic closure perfume box is chosen when the package should feel like a gift before the bottle is touched.',
            'structure_copy' => 'A luxury perfume box with magnetic closure can use a book-style rigid body, magnetic flap, wrapped greyboard shell, and fitted inner tray.',
            'insert_copy' => 'For magnetic perfume packaging, the insert should control bottle position and make the opening experience feel deliberate.',
            'artwork_copy' => 'Luxury perfume artwork often works best with restrained typography, a strong logo area, inner brand story, and one or two carefully controlled finishing effects.',
            'channel_copy' => 'Luxury magnetic perfume boxes are useful for boutique fragrance retail, launch kits, influencer seeding, hotel gifting, seasonal programs, and premium ecommerce presentation.',
            'procurement_note' => 'For this magnetic format, procurement should confirm magnet strength, lid gap, greyboard thickness, tray material, wrap paper, foil area, and whether the box ships assembled.',
            'tolerance_note' => 'The magnetic flap should close confidently without snapping too hard, and the tray should hold the bottle without forcing the customer to pull on the cap.',
            'finish_strategy' => 'Premium magnetic packaging should use finishes with restraint: foil logo, embossing, soft-touch wrap, textured paper, or inner printing can add value when the structure already feels precise.',
            'structure_options' => 'book-style magnetic boxes, rigid magnetic presentation boxes, foldable magnetic gift boxes, shoulder magnetic boxes, and magnetic boxes with EVA or wrapped paperboard trays',
            'materials' => 'rigid greyboard, coated art paper, specialty paper, black card, EVA foam, satin lining, paperboard tray, and soft-touch or anti-scratch lamination',
            'feature' => 'Magnetic luxury perfume presentation, rigid board structure, bottle-fit insert, premium logo finishing',
            'industrial' => 'Perfume, Fragrance, Luxury Beauty, Gift Packaging',
            'paper' => 'Rigid Greyboard / Coated Art Paper / Specialty Paper / Black Card',
            'box_type' => 'Luxury Perfume Magnetic Closure Box',
            'shape' => 'Book Style / Rigid Rectangle / Customized Bottle Fit',
            'accessories' => 'Magnetic closure / EVA insert / Ribbon / Scent card / Inner printing optional',
            'liner' => 'EVA foam / Wrapped paperboard tray / Satin lining / Molded pulp optional',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Soft Touch Lamination',
            'colors' => 'Black / White / Gold / Deep brand colors / Customized Color',
            'category_slugs' => array('perfume-packaging-boxes', 'magnetic-closure-boxes', 'rigid-boxes', 'cosmetic-paper-boxes', 'beauty-skincare-packaging', 'gift-paper-boxes'),
            'tags' => array('luxury perfume box', 'magnetic perfume box', 'fragrance gift packaging', 'rigid beauty box'),
            'images' => array('Custom-Luxury-Perfume-Box-with-Magnetic-Closure.webp', 'Custom-Luxury-Perfume-Box-with-Magnetic-Closure-2.webp', 'Custom-Luxury-Perfume-Box-with-Magnetic-Closure-3.webp', 'Custom-Luxury-Perfume-Box-with-Magnetic-Closure-4.webp'),
            'captions' => array(
                'Custom luxury perfume box with magnetic closure for premium fragrance launches.',
                'Open magnetic perfume box showing rigid presentation structure.',
                'Detail view of luxury perfume magnetic box finishing and tray area.',
                'Magnetic closure perfume packaging set for gift and launch programs.',
            ),
            'details' => array('bottle height', 'bottle weight', 'cap clearance', 'magnet strength', 'tray depth', 'foil logo size', 'inner story panel', 'assembled shipping requirement'),
            'panel_details' => array('brand logo', 'scent name', 'inner brand story', 'bottle volume', 'gift message', 'ingredient or warning area', 'QR code', 'batch code space'),
            'qc_points' => array('magnet pull', 'lid alignment', 'tray fit', 'bottle movement', 'foil registration', 'wrap paper corner', 'inner panel cleanliness', 'carton crush resistance'),
            'related' => array(array('/product/custom-perfume-magnetic-closure-box/', 'perfume magnetic closure box'), array('/product/custom-perfume-box-with-eva-foam-insert/', 'perfume box with EVA foam insert')),
            'headings' => array('Luxury Magnetic Perfume Box for Premium Fragrance Launches', 'Premium Reveal and Glass Bottle Protection', 'Magnetic Rigid Structure Around Perfume Bottles', 'Insert and Tray Planning for Magnetic Perfume Boxes', 'Artwork for Luxury Scent Story and Inner Panels', 'Rigid Materials and Premium Finishing Choices', 'Boutique, Launch Kit, and Gift Use Cases', 'Procurement Details for Magnetic Perfume Packaging', 'Quality Checks for Luxury Magnetic Perfume Boxes', 'Common Mistakes With Magnetic Perfume Gift Boxes'),
            'quote_heading' => 'Quote Details for Luxury Magnetic Perfume Boxes',
            'quote_details' => 'bottle dimensions, filled weight, cap height, desired magnet feel, tray material, wrap paper, artwork, foil or embossing masks, quantity, and assembly requirement',
            'mistakes' => array(
                'Using a magnetic structure without testing lid gap and magnet pull after wrap paper is applied.',
                'Making the tray look premium but leaving too little finger clearance for bottle removal.',
                'Adding too many finishes so the fragrance box feels busy instead of refined.',
                'Shipping assembled rigid boxes without confirming carton quantity and corner protection.',
            ),
            'duplicate_risk' => '5/10',
        ),
        array(
            'title' => 'CUSTOM MINI PERFUME BOTTLE PACKAGING BOX',
            'slug' => 'custom-mini-perfume-bottle-packaging-box',
            'keyword' => 'mini perfume bottle packaging box',
            'buyer' => 'sample perfume brands, discovery set suppliers, hotel amenity buyers, promotional fragrance teams, and private label mini perfume sellers',
            'inside' => 'mini perfume bottles, sample spray vials, travel atomizers, discovery scents, trial-size fragrance bottles, and promotional scent items',
            'problem' => 'making a very small perfume product visible, protected, and easy to identify without wasting packaging space',
            'problem_copy' => 'Mini perfume bottles are small enough to disappear inside a generic carton, so the packaging must create presence while still staying compact.',
            'structure_copy' => 'A mini perfume bottle packaging box can use a compact folding carton, sleeve, drawer micro-box, small rigid box, or display-ready sample carton.',
            'insert_copy' => 'For mini perfume packaging, the insert should prevent rattling and keep the small bottle from sinking too low in the box.',
            'artwork_copy' => 'Mini fragrance artwork needs disciplined hierarchy because the panel area is limited and every millimeter must work for scent name, volume, barcode, and brand cue.',
            'channel_copy' => 'Mini perfume boxes work for discovery programs, hotel amenities, travel retail, subscription samples, checkout gifts, and promotional fragrance campaigns.',
            'procurement_note' => 'For mini perfume projects, procurement should confirm bottle count, vial diameter, atomizer height, display need, barcode size, and whether the pack is single-unit or multi-scent.',
            'tolerance_note' => 'Small boxes need tighter tolerance control because a few millimeters can change whether the bottle rattles, hides behind the panel, or feels hard to remove.',
            'finish_strategy' => 'Finishing should be selective on mini cartons. A foil mark, spot UV scent name, or soft color band can add value without making small text unreadable.',
            'structure_options' => 'mini folding cartons, small sleeve boxes, sample drawer boxes, micro rigid boxes, multi-vial display packs, and compact cartons with paperboard retainers',
            'materials' => 'SBS paperboard, ivory board, art paper, rigid greyboard for gift samples, folded paperboard retainers, EVA inserts, and specialty paper wraps',
            'feature' => 'Small fragrance product visibility, anti-rattle insert, compact carton, discovery sample presentation',
            'industrial' => 'Mini Perfume, Fragrance Samples, Hotel Amenity, Beauty Promotion',
            'paper' => 'SBS Paperboard / Ivory Board / Art Paper / Rigid Greyboard',
            'box_type' => 'Mini Perfume Bottle Packaging Box',
            'shape' => 'Mini Rectangle / Sample Fit / Customized Vial Layout',
            'accessories' => 'Mini insert / Paper retainer / Sample card / Sleeve / Display tray optional',
            'liner' => 'Paperboard insert / EVA insert / No liner optional',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Spot UV, Matte Lamination',
            'colors' => 'White / Pastel / Black / Scent color variants / Customized Color',
            'category_slugs' => array('perfume-packaging-boxes', 'cosmetic-paper-boxes', 'beauty-skincare-packaging', 'folding-carton-boxes', 'custom-paper-boxes'),
            'tags' => array('mini perfume box', 'sample perfume packaging', 'discovery set packaging', 'travel fragrance box'),
            'images' => array('Custom-Mini-Perfume-Bottle-Packaging-Box.webp', 'Custom-Mini-Perfume-Bottle-Packaging-Box-2.webp', 'Custom-Mini-Perfume-Bottle-Packaging-Box-3.webp', 'Custom-Mini-Perfume-Bottle-Packaging-Box-4.webp'),
            'captions' => array(
                'Custom mini perfume bottle packaging box for sample fragrance products.',
                'Mini perfume box view showing compact bottle fit and presentation.',
                'Detail view of small perfume packaging structure and printed panels.',
                'Mini perfume bottle packaging group for discovery and promotion programs.',
            ),
            'details' => array('vial height', 'atomizer diameter', 'sample count', 'scent variant list', 'barcode size', 'display tray need', 'retainer depth', 'gift or hotel channel'),
            'panel_details' => array('scent name', 'sample size', 'brand logo', 'fragrance family', 'barcode', 'usage note', 'batch code', 'promotional message'),
            'qc_points' => array('mini bottle retention', 'rattle control', 'small text readability', 'carton squareness', 'display alignment', 'color coding', 'insert depth', 'packing count accuracy'),
            'related' => array(array('/product/custom-perfume-sample-set-box/', 'perfume sample set box'), array('/product/custom-folding-carton-perfume-box/', 'folding carton perfume box')),
            'headings' => array('Mini Perfume Bottle Packaging for Samples and Travel Sizes', 'Small Fragrance Packaging Problems', 'Compact Carton Structures for Mini Perfume Bottles', 'Retainer and Insert Planning for Sample Vials', 'Artwork for Tiny Scent Panels and Barcodes', 'Materials and Finishes for Mini Perfume Boxes', 'Discovery, Hotel, and Promotional Use Cases', 'Procurement Details for Mini Perfume Packaging', 'Quality Checks for Small Perfume Boxes', 'Common Mistakes With Mini Perfume Packaging'),
            'quote_heading' => 'Quote Details for Mini Perfume Bottle Packaging',
            'quote_details' => 'vial dimensions, atomizer height, number of scents, display need, barcode requirement, artwork versions, insert preference, quantity, and target channel',
            'mistakes' => array(
                'Making the box too large for a mini bottle and losing the sample value impression.',
                'Using small decorative type that becomes unreadable after printing and lamination.',
                'Forgetting that mini bottles can rattle loudly if the retainer is too loose.',
                'Mixing scent variants because barcode and color coding were not controlled before production.',
            ),
            'duplicate_risk' => '4/10',
        ),
        array(
            'title' => 'CUSTOM PERFUME AND LOTION GIFT SET BOX',
            'slug' => 'custom-perfume-and-lotion-gift-set-box',
            'keyword' => 'perfume and lotion gift set box',
            'buyer' => 'beauty gift set brands, fragrance retailers, hotel spa programs, holiday campaign buyers, and private label beauty set suppliers',
            'inside' => 'perfume bottles, body lotion tubes, cream jars, scent cards, mini sprays, beauty accessories, and coordinated fragrance-lotion gift sets',
            'problem' => 'organizing mixed perfume and lotion items in one gift set so the products stay protected, balanced, and easy to understand as a routine',
            'problem_copy' => 'A perfume and lotion gift set combines different product shapes, weights, and label priorities, so the box must control the whole layout instead of treating every item as the same cavity.',
            'structure_copy' => 'A perfume and lotion gift set box can use a rigid lid-and-base box, magnetic gift box, drawer box, sleeve-and-tray set, or large paperboard presentation box.',
            'insert_copy' => 'For mixed beauty sets, the insert layout is the main engineering decision because each bottle, tube, jar, or card needs its own cavity and removal space.',
            'artwork_copy' => 'Gift set artwork should explain the fragrance and body care connection without crowding the package with every ingredient detail on the front panel.',
            'channel_copy' => 'Perfume and lotion gift set boxes are useful for holiday retail, spa gifts, hotel amenities, corporate beauty gifts, ecommerce sets, and influencer launch bundles.',
            'procurement_note' => 'For gift sets, procurement should confirm every item dimension, total set weight, routine order, insert cavity map, sleeve or lid style, and whether the set needs a card pocket.',
            'tolerance_note' => 'The insert should balance products visually while accounting for different heights, cap shapes, tube flexibility, and removal space.',
            'finish_strategy' => 'A gift set can use richer finishing than a single carton, but the finish should support the set story: foil logo, inner printing, ribbon pull, sleeve band, or textured wrap can make the package feel coordinated.',
            'structure_options' => 'rigid lid-and-base boxes, magnetic gift boxes, drawer boxes, sleeve-and-tray boxes, large folding cartons with inserts, and multi-cavity beauty set boxes',
            'materials' => 'rigid greyboard, wrapped art paper, specialty paper, ivory board, SBS paperboard, molded pulp, EVA foam, paperboard trays, and soft-touch lamination',
            'feature' => 'Multi-product beauty set insert, perfume and lotion layout, premium gift presentation, custom logo printing',
            'industrial' => 'Perfume, Body Lotion, Beauty Gift Set, Cosmetic Retail',
            'paper' => 'Rigid Greyboard / Art Paper / Ivory Board / SBS Paperboard',
            'box_type' => 'Perfume and Lotion Gift Set Box',
            'shape' => 'Rectangle / Set Layout / Customized Multi-Cavity Box',
            'accessories' => 'Multi-cavity insert / Ribbon pull / Product card / Sleeve / Magnetic closure optional',
            'liner' => 'EVA insert / Paperboard tray / Molded pulp / Satin lining optional',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Spot UV, Soft Touch Lamination',
            'colors' => 'White / Cream / Gold / Botanical / Brand color system / Customized Color',
            'category_slugs' => array('perfume-packaging-boxes', 'cosmetic-paper-boxes', 'beauty-skincare-packaging', 'gift-paper-boxes', 'rigid-boxes'),
            'tags' => array('perfume gift set box', 'lotion gift set packaging', 'beauty gift packaging', 'cosmetic set box'),
            'images' => array('Custom-Perfume-and-Lotion-Gift-Set-Box.webp', 'Custom-Perfume-and-Lotion-Gift-Set-Box-2.webp', 'Custom-Perfume-and-Lotion-Gift-Set-Box-3.webp', 'Custom-Perfume-and-Lotion-Gift-Set-Box-4.webp'),
            'captions' => array(
                'Custom perfume and lotion gift set box for coordinated beauty products.',
                'Open perfume and lotion set packaging showing multi-product presentation.',
                'Detail view of beauty gift set insert and printed finish.',
                'Perfume and lotion gift packaging set for retail and holiday programs.',
            ),
            'details' => array('item count', 'perfume bottle size', 'lotion tube size', 'set weight', 'gift card space', 'routine sequence', 'insert cavity map', 'campaign quantity'),
            'panel_details' => array('set name', 'fragrance story', 'item list', 'routine order', 'volume per item', 'gift message', 'barcode', 'batch code area'),
            'qc_points' => array('multi-cavity fit', 'set weight support', 'item sequence', 'insert alignment', 'lid closure', 'surface finish', 'barcode accuracy', 'master carton packing'),
            'related' => array(array('/product/custom-skincare-set-packaging-box/', 'skincare set packaging box'), array('/product/custom-perfume-sample-set-box/', 'perfume sample set box')),
            'headings' => array('Perfume and Lotion Gift Set Box for Beauty Retail', 'Mixed Beauty Set Packaging Risks', 'Rigid and Drawer Structures for Perfume-Lotion Sets', 'Multi-Cavity Insert Design for Bottles and Tubes', 'Artwork for Fragrance, Body Care, and Gift Messages', 'Materials and Finishes for Beauty Gift Sets', 'Holiday, Spa, and Ecommerce Gift Use Cases', 'Procurement Details for Perfume and Lotion Sets', 'Quality Checks for Mixed Beauty Gift Boxes', 'Common Mistakes With Perfume and Lotion Gift Set Boxes'),
            'quote_heading' => 'Quote Details for Perfume and Lotion Gift Set Boxes',
            'quote_details' => 'all product dimensions, item count, total set weight, insert layout, gift message, artwork versions, preferred structure, finishing needs, quantity, and delivery schedule',
            'mistakes' => array(
                'Designing the outer box before measuring every perfume and lotion item.',
                'Making all cavities the same depth even when bottles, tubes, and jars have different heights.',
                'Ignoring total set weight when choosing board thickness and insert material.',
                'Presenting items in a confusing order that does not match the intended gift story.',
            ),
            'duplicate_risk' => '4/10',
        ),
        array(
            'title' => 'CUSTOM PERFUME BOX WITH EVA FOAM INSERT',
            'slug' => 'custom-perfume-box-with-eva-foam-insert',
            'keyword' => 'perfume box with EVA foam insert',
            'buyer' => 'premium perfume brands, glass bottle fragrance suppliers, boutique beauty retailers, gift set buyers, and private label perfume manufacturers',
            'inside' => 'fragile glass perfume bottles, premium caps, atomizer bottles, sample cards, refill bottles, and heavy fragrance products',
            'problem' => 'using a protective EVA foam cavity to hold a fragile perfume bottle securely without scratching the cap, label, or bottle finish',
            'problem_copy' => 'EVA foam is often used when the perfume bottle is heavy, glossy, irregular, or too valuable to move freely inside a paper tray.',
            'structure_copy' => 'A perfume box with EVA foam insert can be a rigid box, magnetic box, drawer box, lid-and-base box, or reinforced paperboard box with a die-cut foam cavity.',
            'insert_copy' => 'For this product, the EVA insert should match the bottle outline, support the base, protect the shoulder, and keep the cap area free from pressure.',
            'artwork_copy' => 'The outside artwork can stay refined because the foam insert already communicates premium protection when the customer opens the box.',
            'channel_copy' => 'Perfume boxes with EVA foam inserts are useful for fragile glass bottles, luxury fragrance launches, sample kits, high-value gift sets, and export orders that need stronger product retention.',
            'procurement_note' => 'For EVA insert projects, procurement should confirm foam thickness, density, color, cavity depth, finger notch, bottle orientation, odor requirement, and whether foam should be wrapped or exposed.',
            'tolerance_note' => 'The EVA cavity should grip the bottle body enough to stop movement but still let customers remove the bottle without pulling on the spray cap.',
            'finish_strategy' => 'Foam insert packaging often pairs well with rigid board, matte wrap, foil logo, debossed marks, or a clean inner card because the protection system is visible inside the box.',
            'structure_options' => 'rigid boxes with EVA inserts, magnetic boxes with foam trays, drawer boxes with foam cavities, lid-and-base gift boxes, and paperboard cartons with removable foam supports',
            'materials' => 'rigid greyboard, art paper, EVA foam, black card, specialty paper, coated paperboard, soft-touch film, and optional satin or paper wrap around the insert',
            'feature' => 'EVA foam bottle cavity, fragile perfume protection, premium rigid box, custom logo finishing',
            'industrial' => 'Perfume, Fragrance, Fragile Glass Bottle, Luxury Beauty',
            'paper' => 'Rigid Greyboard / Art Paper / Specialty Paper / EVA Foam',
            'box_type' => 'Perfume Box with EVA Foam Insert',
            'shape' => 'Rectangle / Bottle Cavity / Customized EVA Insert',
            'accessories' => 'EVA foam insert / Finger notch / Ribbon / Product card / Sleeve optional',
            'liner' => 'EVA foam / Wrapped foam / Satin lining optional',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Matte Lamination',
            'colors' => 'Black / White / Gold / Foam color options / Customized Color',
            'category_slugs' => array('perfume-packaging-boxes', 'cosmetic-paper-boxes', 'beauty-skincare-packaging', 'rigid-boxes', 'packaging-accessories'),
            'tags' => array('EVA foam insert', 'perfume insert box', 'fragile fragrance packaging', 'luxury perfume protection'),
            'images' => array('Custom-Perfume-Box-with-EVA-Foam-Insert.webp', 'Custom-Perfume-Box-with-EVA-Foam-Insert-2.webp', 'Custom-Perfume-Box-with-EVA-Foam-Insert-3.webp', 'Custom-Perfume-Box-with-EVA-Foam-Insert-4.webp'),
            'captions' => array(
                'Custom perfume box with EVA foam insert for fragile bottle protection.',
                'Open EVA foam perfume insert showing bottle cavity and presentation.',
                'Detail view of perfume packaging with protective foam tray.',
                'Perfume box with EVA foam insert for premium fragrance gift orders.',
            ),
            'details' => array('bottle outline', 'filled weight', 'cap clearance', 'foam thickness', 'foam density', 'finger notch', 'insert color', 'export packing route'),
            'panel_details' => array('brand logo', 'fragrance name', 'bottle volume', 'premium claim', 'warning text', 'barcode', 'batch code', 'inner card message'),
            'qc_points' => array('foam cavity fit', 'bottle movement', 'finger notch access', 'foam odor', 'cap clearance', 'foil position', 'box closure', 'export carton protection'),
            'related' => array(array('/product/custom-luxury-perfume-box-with-magnetic-closure/', 'luxury perfume magnetic box'), array('/product/custom-perfume-box-with-paper-insert/', 'perfume box with paper insert')),
            'headings' => array('Perfume Box With EVA Foam Insert for Fragile Bottles', 'Why EVA Foam Matters for Premium Fragrance Protection', 'Box Structures That Work With EVA Bottle Cavities', 'Foam Insert Planning Around Bottle Shape and Cap Clearance', 'Artwork That Lets the Protective Insert Feel Premium', 'Materials and Finishes for EVA Insert Perfume Boxes', 'Luxury, Gift, and Export Use Cases', 'Procurement Details for EVA Foam Perfume Inserts', 'Quality Checks for Perfume Boxes With Foam Inserts', 'Common Mistakes With EVA Foam Perfume Packaging'),
            'quote_heading' => 'Quote Details for Perfume Boxes With EVA Foam Inserts',
            'quote_details' => 'bottle drawing, bottle weight, cap height, foam density, cavity depth, insert color, box style, artwork, finishing masks, quantity, and shipping route',
            'mistakes' => array(
                'Cutting the EVA cavity from a photo instead of a measured bottle drawing.',
                'Making the foam grip the cap instead of the bottle body.',
                'Ignoring foam odor, dust, or color transfer for premium fragrance products.',
                'Choosing a luxury outer box but leaving no finger notch for easy bottle removal.',
            ),
            'duplicate_risk' => '4/10',
        ),
        array(
            'title' => 'CUSTOM PERFUME BOX WITH LID AND BASE',
            'slug' => 'custom-perfume-box-with-lid-and-base',
            'keyword' => 'perfume box with lid and base',
            'buyer' => 'premium fragrance brands, boutique beauty retailers, gift set suppliers, hotel amenity programs, and private label perfume teams',
            'inside' => 'perfume bottles, cologne bottles, scent cards, travel sprays, refill bottles, and premium fragrance gift items',
            'problem' => 'creating a clean lift-off gift presentation while keeping the perfume bottle stable in the base and protected during handling',
            'problem_copy' => 'A lid-and-base perfume box creates a slower opening moment than a folding carton, but the lid fit and base support must be controlled carefully.',
            'structure_copy' => 'A perfume box with lid and base can use a rigid two-piece structure, shoulder box, telescoping lid, or paperboard lid-and-base format depending on bottle weight and price point.',
            'insert_copy' => 'For lid-and-base perfume packaging, the insert should keep the bottle centered in the base so the product does not lift with the lid or tilt during opening.',
            'artwork_copy' => 'The two-piece structure allows a calmer outside panel and more detailed inner base or card messaging for scent story and gift context.',
            'channel_copy' => 'Perfume lid-and-base boxes are useful for boutique retail, gift counters, hotel gifts, seasonal fragrance programs, and premium direct-to-consumer packaging.',
            'procurement_note' => 'For lid-and-base formats, procurement should confirm lid tightness, base wall height, shoulder height if used, insert support, box assembly method, and carton packing volume.',
            'tolerance_note' => 'The lid should lift smoothly without feeling loose, and the base should remain steady when the bottle is removed.',
            'finish_strategy' => 'A lid-and-base box works well with textured paper, foil logo, debossed brand mark, contrast inner color, or a sleeve band that identifies scent variants.',
            'structure_options' => 'rigid lid-and-base boxes, shoulder boxes, telescoping gift boxes, paperboard two-piece boxes, and lid-and-base perfume boxes with fitted inserts',
            'materials' => 'rigid greyboard, coated art paper, specialty paper, ivory board, wrapped paperboard trays, EVA inserts, paperboard collars, and matte or soft-touch lamination',
            'feature' => 'Two-piece perfume gift box, lift-off lid, fitted bottle insert, premium fragrance presentation',
            'industrial' => 'Perfume, Fragrance Gift, Beauty Retail, Luxury Packaging',
            'paper' => 'Rigid Greyboard / Coated Art Paper / Specialty Paper / Ivory Board',
            'box_type' => 'Perfume Box with Lid and Base',
            'shape' => 'Two-Piece Rectangle / Shoulder Box / Customized Bottle Fit',
            'accessories' => 'Lid and base / Paper tray / EVA insert / Sleeve band / Product card optional',
            'liner' => 'Paperboard tray / EVA insert / Satin lining / Molded pulp optional',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Soft Touch Lamination',
            'colors' => 'White / Black / Gold / Cream / Fragrance color system / Customized Color',
            'category_slugs' => array('perfume-packaging-boxes', 'cosmetic-paper-boxes', 'beauty-skincare-packaging', 'lid-and-base-boxes', 'rigid-boxes', 'gift-paper-boxes'),
            'tags' => array('lid and base perfume box', 'two piece perfume packaging', 'perfume gift box', 'rigid fragrance box'),
            'images' => array('Custom-Perfume-Box-with-Lid-and-Base.webp', 'Custom-Perfume-Box-with-Lid-and-Base-2.webp', 'Custom-Perfume-Box-with-Lid-and-Base-3.webp', 'Custom-Perfume-Box-with-Lid-and-Base-4.webp'),
            'captions' => array(
                'Custom perfume box with lid and base for premium fragrance gifts.',
                'Two-piece perfume box view showing lid and base structure.',
                'Detail view of lid-and-base perfume packaging finish and insert.',
                'Perfume lid-and-base box set for boutique retail presentation.',
            ),
            'details' => array('bottle height', 'filled weight', 'lid depth', 'base wall height', 'insert material', 'sleeve band need', 'scent variant list', 'assembled box volume'),
            'panel_details' => array('brand logo', 'scent name', 'collection name', 'volume', 'gift message', 'warning text', 'barcode', 'batch code area'),
            'qc_points' => array('lid fit', 'base squareness', 'bottle stability', 'insert height', 'wrap corner quality', 'foil alignment', 'surface scuffing', 'carton packing'),
            'related' => array(array('/product/custom-luxury-perfume-box-with-magnetic-closure/', 'luxury perfume magnetic box'), array('/product/custom-perfume-drawer-box-with-insert/', 'perfume drawer box with insert')),
            'headings' => array('Perfume Box With Lid and Base for Premium Gift Presentation', 'Lid Fit and Bottle Stability Risks', 'Two-Piece Structure for Fragrance Packaging', 'Insert Planning for Lift-Off Perfume Boxes', 'Artwork for Calm Outer Panels and Inner Story', 'Materials and Finishes for Lid-and-Base Perfume Boxes', 'Boutique, Hotel, and Gift Use Cases', 'Procurement Details for Two-Piece Perfume Boxes', 'Quality Checks for Lid-and-Base Perfume Packaging', 'Common Mistakes With Lid-and-Base Perfume Boxes'),
            'quote_heading' => 'Quote Details for Perfume Boxes With Lid and Base',
            'quote_details' => 'bottle dimensions, lid depth preference, base height, insert material, wrap paper, artwork, sleeve or card needs, quantity, assembly requirement, and delivery date',
            'mistakes' => array(
                'Approving a lid fit without testing the final wrapped paper thickness.',
                'Using a shallow base for a heavy perfume bottle and making the box unstable.',
                'Forgetting scent identification when the front panel is intentionally minimal.',
                'Ignoring freight volume when rigid lid-and-base boxes ship fully assembled.',
            ),
            'duplicate_risk' => '4/10',
        ),
        array(
            'title' => 'CUSTOM PERFUME BOX WITH PAPER INSERT',
            'slug' => 'custom-perfume-box-with-paper-insert',
            'keyword' => 'perfume box with paper insert',
            'buyer' => 'fragrance brands, sustainable beauty teams, cosmetic distributors, private label perfume suppliers, and retail packaging buyers',
            'inside' => 'perfume bottles, cologne bottles, travel sprays, refill bottles, fragrance vials, and lightweight glass scent products',
            'problem' => 'using a recyclable paper insert to control bottle movement while keeping the box efficient for retail and bulk production',
            'problem_copy' => 'A paper insert perfume box is a practical choice when the buyer wants bottle stability without using foam or plastic-heavy components.',
            'structure_copy' => 'A perfume box with paper insert can use a folding carton, sleeve, rigid box, lid-and-base box, or drawer box with a folded paperboard tray.',
            'insert_copy' => 'For this product, the folded paper insert should support the bottle base, keep the cap from touching the top panel, and lock into the box without collapsing.',
            'artwork_copy' => 'Paper insert packaging gives buyers a stronger sustainability story, so artwork should communicate clean structure, product information, and responsible material choices clearly.',
            'channel_copy' => 'Perfume boxes with paper inserts work for retail fragrance lines, natural beauty brands, refillable perfume programs, ecommerce sets, and brands reducing foam use.',
            'procurement_note' => 'For paper insert projects, procurement should confirm insert board thickness, folding direction, cavity style, glue-free preference, packing speed, and whether the insert should be printed.',
            'tolerance_note' => 'The paper insert should have enough stiffness to hold the bottle after repeated handling and enough clearance so the bottle does not scrape printed panels.',
            'finish_strategy' => 'A paper insert format can use matte lamination, kraft texture, water-based coating, foil accents, or inner printing while keeping the material message honest and production-friendly.',
            'structure_options' => 'folding cartons with paperboard inserts, rigid boxes with folded trays, sleeve boxes with paper retainers, drawer boxes with paper cavities, and glue-free insert structures',
            'materials' => 'SBS paperboard, ivory board, kraft board, recycled paperboard, coated art paper, folded paperboard trays, and optional water-based coating',
            'feature' => 'Recyclable paper insert, bottle movement control, efficient perfume carton, custom brand printing',
            'industrial' => 'Perfume, Fragrance, Sustainable Beauty, Cosmetic Retail',
            'paper' => 'SBS Paperboard / Ivory Board / Kraft Board / Recycled Paperboard',
            'box_type' => 'Perfume Box with Paper Insert',
            'shape' => 'Rectangle / Paper Insert Cavity / Customized Bottle Fit',
            'accessories' => 'Folded paper insert / Product card / Sleeve / Leaflet / QR panel optional',
            'liner' => 'Paperboard insert / Recycled paper tray / No foam',
            'printing' => 'CMYK Printing, Pantone Printing, Matte Lamination, Foil Stamping, Embossing, Water-Based Coating',
            'colors' => 'White / Kraft / Natural / Scent color variants / Customized Color',
            'category_slugs' => array('perfume-packaging-boxes', 'cosmetic-paper-boxes', 'beauty-skincare-packaging', 'folding-carton-boxes', 'custom-paper-boxes'),
            'tags' => array('paper insert perfume box', 'recyclable perfume packaging', 'sustainable fragrance box', 'perfume carton insert'),
            'images' => array('Custom-Perfume-Box-with-Paper-Insert.webp', 'Custom-Perfume-Box-with-Paper-Insert-2.webp', 'Custom-Perfume-Box-with-Paper-Insert-3.webp', 'Custom-Perfume-Box-with-Paper-Insert-4.webp'),
            'captions' => array(
                'Custom perfume box with paper insert for recyclable bottle support.',
                'Open paper insert perfume box showing folded tray structure.',
                'Detail view of paper insert and perfume packaging panel design.',
                'Perfume box with paper insert set for retail fragrance programs.',
            ),
            'details' => array('bottle height', 'cap shape', 'insert board thickness', 'folding direction', 'sustainability target', 'printing on insert', 'packing speed', 'carton quantity'),
            'panel_details' => array('brand logo', 'scent name', 'material note', 'volume', 'ingredient or warning text', 'barcode', 'QR code', 'batch code area'),
            'qc_points' => array('paper insert locking', 'bottle movement', 'cap clearance', 'fold strength', 'carton squareness', 'small text readability', 'glue cleanliness', 'bulk packing speed'),
            'related' => array(array('/product/custom-folding-carton-perfume-box/', 'folding carton perfume box'), array('/product/custom-refillable-perfume-packaging-box/', 'refillable perfume packaging box')),
            'headings' => array('Perfume Box With Paper Insert for Recyclable Bottle Support', 'Paper Insert Packaging Risks for Fragrance Bottles', 'Carton and Sleeve Structures With Folded Inserts', 'Paper Tray Design Around Bottle Fit and Packing Speed', 'Artwork for Sustainable Perfume Packaging Claims', 'Materials and Finishes for Paper Insert Perfume Boxes', 'Retail, Natural Beauty, and Refill Use Cases', 'Procurement Details for Paper Insert Perfume Packaging', 'Quality Checks for Perfume Boxes With Paper Inserts', 'Common Mistakes With Paper Insert Perfume Boxes'),
            'quote_heading' => 'Quote Details for Perfume Boxes With Paper Inserts',
            'quote_details' => 'bottle size, cap height, insert board thickness, cavity style, sustainability requirement, artwork, coating preference, quantity, and packing method',
            'mistakes' => array(
                'Using a paper insert that looks sustainable but collapses under bottle weight.',
                'Forgetting to test bottle removal after the insert is folded into the final carton.',
                'Printing sustainability claims before confirming actual paper material and coating.',
                'Making a complex insert that slows down packing for high-volume retail orders.',
            ),
            'duplicate_risk' => '4/10',
        ),
        array(
            'title' => 'CUSTOM PERFUME DRAWER BOX WITH INSERT',
            'slug' => 'custom-perfume-drawer-box-with-insert',
            'keyword' => 'perfume drawer box with insert',
            'buyer' => 'premium perfume brands, discovery set sellers, boutique beauty retailers, PR kit buyers, and private label fragrance gift suppliers',
            'inside' => 'perfume bottles, sample vials, scent cards, refill bottles, atomizers, and premium fragrance gift components',
            'problem' => 'creating a sliding reveal while keeping the perfume bottle and accessories locked in the tray during opening and transport',
            'problem_copy' => 'A drawer perfume box creates an elegant sliding experience, but the inner tray must not feel loose, jammed, or unsafe for a glass bottle.',
            'structure_copy' => 'A perfume drawer box with insert can use a rigid sleeve and tray, sliding drawer with ribbon pull, double-drawer structure, or paperboard sleeve-and-tray format.',
            'insert_copy' => 'For drawer perfume packaging, the insert should hold the bottle while the tray moves so the product does not slide toward the customer or hit the sleeve edge.',
            'artwork_copy' => 'Drawer box artwork can use the outer sleeve for brand recognition and the inner tray for scent story, instructions, or gift messaging.',
            'channel_copy' => 'Perfume drawer boxes are useful for premium discovery sets, boutique fragrance gifts, PR mailers, sample assortments, and fragrance collections with cards or accessories.',
            'procurement_note' => 'For drawer formats, procurement should confirm sleeve tightness, tray pull force, ribbon strength, insert locking, product weight, and whether the drawer needs a stop mechanism.',
            'tolerance_note' => 'The drawer should slide smoothly without falling open, and the insert should stay fixed while customers pull the tray outward.',
            'finish_strategy' => 'Drawer perfume boxes can use a contrast tray color, ribbon pull, foil sleeve logo, inner printed story, or debossed detail to make the sliding reveal feel intentional.',
            'structure_options' => 'rigid drawer boxes, sleeve-and-tray boxes, ribbon-pull drawer boxes, double-drawer sample sets, and paperboard sliding boxes with fitted inserts',
            'materials' => 'rigid greyboard, coated art paper, specialty paper, black card, EVA insert, paperboard tray, ribbon, and soft-touch or anti-scratch lamination',
            'feature' => 'Sliding drawer reveal, fitted perfume insert, ribbon pull, premium fragrance gift structure',
            'industrial' => 'Perfume, Fragrance Gift, Discovery Set, Beauty Retail',
            'paper' => 'Rigid Greyboard / Art Paper / Specialty Paper / Paperboard Tray',
            'box_type' => 'Perfume Drawer Box with Insert',
            'shape' => 'Sliding Drawer / Sleeve and Tray / Customized Bottle Layout',
            'accessories' => 'Drawer tray / Ribbon pull / EVA insert / Paper tray / Card slot optional',
            'liner' => 'EVA insert / Paperboard tray / Satin lining / Molded pulp optional',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Spot UV, Soft Touch Lamination',
            'colors' => 'Black / White / Gold / Contrast inner tray / Customized Color',
            'category_slugs' => array('perfume-packaging-boxes', 'drawer-boxes', 'rigid-boxes', 'cosmetic-paper-boxes', 'beauty-skincare-packaging', 'gift-paper-boxes'),
            'tags' => array('perfume drawer box', 'sliding fragrance box', 'drawer gift packaging', 'perfume box with insert'),
            'images' => array('Custom-Perfume-Drawer-Box-with-Insert.webp', 'Custom-Perfume-Drawer-Box-with-Insert-2.webp', 'Custom-Perfume-Drawer-Box-with-Insert-3.webp', 'Custom-Perfume-Drawer-Box-with-Insert-4.webp'),
            'captions' => array(
                'Custom perfume drawer box with insert for sliding fragrance presentation.',
                'Open drawer perfume packaging showing tray and insert layout.',
                'Detail view of perfume drawer box sleeve, pull, and finishing.',
                'Perfume drawer box with insert for gift and discovery set programs.',
            ),
            'details' => array('bottle height', 'tray pull direction', 'ribbon length', 'insert material', 'drawer stop need', 'sample card size', 'set count', 'shipping orientation'),
            'panel_details' => array('outer sleeve logo', 'scent name', 'inner tray message', 'volume', 'QR code', 'warning text', 'barcode', 'batch code area'),
            'qc_points' => array('drawer sliding force', 'tray fit', 'ribbon pull strength', 'bottle movement', 'insert locking', 'sleeve squareness', 'foil position', 'carton packing'),
            'related' => array(array('/product/custom-perfume-sample-set-box/', 'perfume sample set box'), array('/product/custom-perfume-box-with-lid-and-base/', 'perfume box with lid and base')),
            'headings' => array('Perfume Drawer Box With Insert for Sliding Gift Presentation', 'Drawer Movement and Glass Bottle Risks', 'Sleeve-and-Tray Structures for Perfume Packaging', 'Insert Layout for Sliding Fragrance Trays', 'Artwork for Outer Sleeves and Inner Story Panels', 'Materials and Finishes for Perfume Drawer Boxes', 'Discovery, Gift, and PR Kit Use Cases', 'Procurement Details for Perfume Drawer Packaging', 'Quality Checks for Drawer Perfume Boxes', 'Common Mistakes With Perfume Drawer Boxes'),
            'quote_heading' => 'Quote Details for Perfume Drawer Boxes With Inserts',
            'quote_details' => 'bottle dimensions, tray size, sleeve tolerance, pull direction, ribbon requirement, insert material, artwork, finishing needs, quantity, and shipping orientation',
            'mistakes' => array(
                'Making the drawer too loose so it slides open during handling.',
                'Making the drawer too tight after lamination and wrap paper are applied.',
                'Forgetting that the bottle can shift while the tray is pulled outward.',
                'Choosing a ribbon pull without testing strength and placement on filled boxes.',
            ),
            'duplicate_risk' => '4/10',
        ),
        array(
            'title' => 'CUSTOM PERFUME SAMPLE SET BOX',
            'slug' => 'custom-perfume-sample-set-box',
            'keyword' => 'perfume sample set box',
            'buyer' => 'fragrance discovery brands, subscription sample sellers, boutique retailers, PR campaign teams, and private label perfume sample suppliers',
            'inside' => 'multiple perfume vials, mini spray bottles, scent cards, discovery notes, refill samples, and fragrance tester assortments',
            'problem' => 'arranging several small fragrance samples so each scent is visible, protected, correctly labeled, and easy to compare',
            'problem_copy' => 'Perfume sample sets require more version control than a single bottle because several scents, labels, cards, and cavities must stay in the right order.',
            'structure_copy' => 'A perfume sample set box can use a rigid presentation tray, drawer box, sleeve-and-tray structure, folding carton set, book-style box, or multi-row sample holder.',
            'insert_copy' => 'For sample sets, the insert should separate each vial or mini bottle, keep labels facing up, and allow customers to remove one scent without disturbing the others.',
            'artwork_copy' => 'Sample set artwork should help customers compare scent family, usage order, discovery notes, and QR information without turning the box into a crowded catalog.',
            'channel_copy' => 'Perfume sample set boxes are useful for discovery programs, subscription boxes, influencer seeding, retail trial kits, hotel scent menus, and seasonal fragrance launches.',
            'procurement_note' => 'For sample sets, procurement should prepare a scent list, vial dimensions, cavity count, order sequence, card size, barcode plan, and variant matrix before artwork is finalized.',
            'tolerance_note' => 'Each small cavity needs enough grip to stop vial movement and enough finger access so customers can test one scent at a time.',
            'finish_strategy' => 'Sample set packaging can use a clean grid, scent color bands, foil logo, printed insert labels, or inner guide card to make comparison easy.',
            'structure_options' => 'multi-cavity rigid trays, drawer sample boxes, sleeve-and-tray sets, book-style sample boxes, folding carton sample kits, and paperboard vial holders',
            'materials' => 'rigid greyboard, SBS paperboard, ivory board, EVA foam, folded paperboard trays, specialty paper, card stock, and matte or soft-touch lamination',
            'feature' => 'Multi-vial sample layout, scent comparison system, discovery set presentation, custom insert tray',
            'industrial' => 'Perfume Samples, Discovery Set, Fragrance Retail, Beauty Promotion',
            'paper' => 'Rigid Greyboard / SBS Paperboard / Ivory Board / Specialty Paper',
            'box_type' => 'Perfume Sample Set Box',
            'shape' => 'Multi-Cavity Rectangle / Discovery Set / Customized Vial Layout',
            'accessories' => 'Multi-cavity insert / Scent card / QR guide / Sleeve / Ribbon optional',
            'liner' => 'Paperboard tray / EVA insert / Card holder / Molded pulp optional',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Spot UV, Matte Lamination, Digital Version Printing',
            'colors' => 'White / Black / Scent color system / Gold / Customized Color',
            'category_slugs' => array('perfume-packaging-boxes', 'cosmetic-paper-boxes', 'beauty-skincare-packaging', 'gift-paper-boxes', 'rigid-boxes'),
            'tags' => array('perfume sample set', 'fragrance discovery box', 'sample vial packaging', 'perfume tester packaging'),
            'images' => array('Custom-Perfume-Sample-Set-Box.webp', 'Custom-Perfume-Sample-Set-Box-2.webp', 'Custom-Perfume-Sample-Set-Box-3.webp', 'Custom-Perfume-Sample-Set-Box-4.webp'),
            'captions' => array(
                'Custom perfume sample set box for multi-scent discovery programs.',
                'Open perfume sample set packaging showing vial organization.',
                'Detail view of sample set insert and scent information layout.',
                'Perfume sample set box for retail testers and subscription kits.',
            ),
            'details' => array('vial count', 'vial diameter', 'scent sequence', 'sample card size', 'QR code need', 'version list', 'insert cavity depth', 'subscription or retail channel'),
            'panel_details' => array('set name', 'scent list', 'fragrance family', 'usage guide', 'QR code', 'barcode', 'sample volume', 'batch code area'),
            'qc_points' => array('vial cavity fit', 'scent order', 'label direction', 'insert alignment', 'small text readability', 'QR scan', 'color coding', 'packing accuracy'),
            'related' => array(array('/product/custom-mini-perfume-bottle-packaging-box/', 'mini perfume bottle packaging box'), array('/product/custom-perfume-drawer-box-with-insert/', 'perfume drawer box with insert')),
            'headings' => array('Perfume Sample Set Box for Discovery and Trial Kits', 'Multi-Scent Sample Packaging Risks', 'Sample Set Structures for Vials and Mini Sprays', 'Insert Planning for Vial Order and Removal Space', 'Artwork for Scent Lists, QR Guides, and Comparison', 'Materials and Finishes for Perfume Sample Sets', 'Subscription, Retail, and PR Sample Use Cases', 'Procurement Details for Perfume Sample Set Packaging', 'Quality Checks for Multi-Scent Sample Boxes', 'Common Mistakes With Perfume Sample Set Boxes'),
            'quote_heading' => 'Quote Details for Perfume Sample Set Boxes',
            'quote_details' => 'vial count, vial dimensions, sample order, insert material, card or QR guide size, artwork version list, quantity, target channel, and delivery date',
            'mistakes' => array(
                'Approving a sample set without checking every scent label and cavity order.',
                'Using a shared insert for vials with different heights or caps.',
                'Making the sample cavities too tight for easy one-by-one removal.',
                'Treating the QR guide, scent card, and barcode plan as last-minute details.',
            ),
            'duplicate_risk' => '4/10',
        ),
        array(
            'title' => 'CUSTOM REFILLABLE PERFUME PACKAGING BOX',
            'slug' => 'custom-refillable-perfume-packaging-box',
            'keyword' => 'refillable perfume packaging box',
            'buyer' => 'refillable fragrance brands, sustainable beauty retailers, travel atomizer suppliers, private label perfume teams, and eco-positioned cosmetic sellers',
            'inside' => 'refillable perfume bottles, travel atomizers, refill cartridges, glass refill bottles, instruction cards, and reusable fragrance systems',
            'problem' => 'explaining the refill system clearly while protecting the bottle, refill component, and any instruction card inside the package',
            'problem_copy' => 'Refillable perfume packaging has to protect the product and also teach the customer how the refill system works.',
            'structure_copy' => 'A refillable perfume packaging box can use a folding carton, sleeve, drawer box, rigid set box, or compact refill kit structure with a dedicated card slot.',
            'insert_copy' => 'For refillable perfume packaging, the insert should separate the main bottle from refill cartridges or instruction cards so parts do not rub during shipping.',
            'artwork_copy' => 'Refillable perfume artwork should make the sustainability message specific, practical, and supported by clear usage steps rather than generic eco claims.',
            'channel_copy' => 'Refillable perfume boxes are useful for sustainable fragrance launches, travel retail, ecommerce starter kits, subscription refills, boutique beauty counters, and private label eco programs.',
            'procurement_note' => 'For refillable systems, procurement should confirm main bottle size, refill count, instruction card size, reuse claims, material preference, and whether the box must hold future refill SKUs.',
            'tolerance_note' => 'The insert should stop the main bottle and refill component from touching while leaving a clear place for instructions, QR code, or warranty information.',
            'finish_strategy' => 'A refillable perfume box can use natural kraft, FSC-style paper direction, water-based coating, matte lamination, or restrained foil if the material story remains credible.',
            'structure_options' => 'folding cartons for refillable bottles, sleeve-and-tray refill kits, drawer boxes with refill cavities, rigid starter set boxes, and paperboard cartons with instruction-card slots',
            'materials' => 'kraft paperboard, recycled paperboard, SBS paperboard, ivory board, molded pulp, folded paper inserts, coated art paper, and water-based or matte coating options',
            'feature' => 'Refill system information, sustainable paper structure, bottle and cartridge separation, custom fragrance branding',
            'industrial' => 'Refillable Perfume, Sustainable Beauty, Travel Fragrance, Cosmetic Retail',
            'paper' => 'Kraft Paperboard / Recycled Paperboard / SBS Paperboard / Ivory Board',
            'box_type' => 'Refillable Perfume Packaging Box',
            'shape' => 'Rectangle / Refill Kit / Customized Bottle and Cartridge Layout',
            'accessories' => 'Paper insert / Refill cartridge cavity / Instruction card slot / QR panel / Sleeve optional',
            'liner' => 'Paperboard insert / Molded pulp / Recycled paper tray / No foam',
            'printing' => 'CMYK Printing, Pantone Printing, Matte Lamination, Water-Based Coating, Foil Stamping, Embossing',
            'colors' => 'Kraft / White / Green / Natural / Brand color system / Customized Color',
            'category_slugs' => array('perfume-packaging-boxes', 'cosmetic-paper-boxes', 'beauty-skincare-packaging', 'folding-carton-boxes', 'custom-paper-boxes'),
            'tags' => array('refillable perfume box', 'sustainable fragrance packaging', 'travel perfume packaging', 'eco beauty packaging'),
            'images' => array('Custom-Refillable-Perfume-Packaging-Box.webp', 'Custom-Refillable-Perfume-Packaging-Box-2.webp', 'Custom-Refillable-Perfume-Packaging-Box-3.webp', 'Custom-Refillable-Perfume-Packaging-Box-4.webp'),
            'captions' => array(
                'Custom refillable perfume packaging box for sustainable fragrance systems.',
                'Open refillable perfume box showing bottle and refill kit layout.',
                'Detail view of refillable perfume packaging structure and printed panels.',
                'Refillable perfume packaging set for eco beauty and travel fragrance programs.',
            ),
            'details' => array('main bottle size', 'refill cartridge size', 'item count', 'instruction card dimensions', 'sustainability claim', 'QR guide need', 'insert material', 'subscription refill plan'),
            'panel_details' => array('brand logo', 'refill system steps', 'scent name', 'volume', 'sustainability note', 'QR code', 'barcode', 'batch code area'),
            'qc_points' => array('bottle and refill separation', 'instruction card fit', 'QR scan', 'material claim accuracy', 'insert locking', 'small text readability', 'carton strength', 'version control'),
            'related' => array(array('/product/custom-perfume-box-with-paper-insert/', 'perfume box with paper insert'), array('/product/custom-mini-perfume-bottle-packaging-box/', 'mini perfume bottle packaging box')),
            'headings' => array('Refillable Perfume Packaging Box for Sustainable Fragrance Systems', 'Refill Kit Packaging Problems to Solve', 'Box Structures for Bottles, Refills, and Instructions', 'Insert Planning for Refill Components and Cards', 'Artwork for Refill Steps and Sustainability Claims', 'Materials and Finishes for Refill-Friendly Perfume Boxes', 'Eco Beauty, Travel, and Subscription Use Cases', 'Procurement Details for Refillable Perfume Packaging', 'Quality Checks for Refillable Perfume Boxes', 'Common Mistakes With Refillable Perfume Packaging'),
            'quote_heading' => 'Quote Details for Refillable Perfume Packaging Boxes',
            'quote_details' => 'main bottle dimensions, refill cartridge count, card size, sustainability material preference, artwork, QR or instruction needs, insert style, quantity, and sales channel',
            'mistakes' => array(
                'Making refill instructions too small or hiding them inside the box without a clear access path.',
                'Using vague eco language before confirming actual paper material and coating.',
                'Letting the refill cartridge rub against the main bottle during shipping.',
                'Designing only the first starter kit and forgetting how future refill SKUs will fit the packaging system.',
            ),
            'duplicate_risk' => '4/10',
        ),
    );
}

$marker = 'product-samples-perfume-packaging-202607';
$products = vpn_perfume_products();
$category_names = array(
    'perfume-packaging-boxes' => 'Perfume Packaging Boxes',
    'cosmetic-paper-boxes' => 'Cosmetic Paper Boxes',
    'beauty-skincare-packaging' => 'Beauty and Skincare Packaging',
    'folding-carton-boxes' => 'Folding Carton Boxes',
    'custom-paper-boxes' => 'Custom Paper Boxes',
    'magnetic-closure-boxes' => 'Magnetic Closure Boxes',
    'rigid-boxes' => 'Rigid Boxes',
    'gift-paper-boxes' => 'Gift Paper Boxes',
    'packaging-accessories' => 'Packaging Accessories',
    'lid-and-base-boxes' => 'Lid and Base Boxes',
    'drawer-boxes' => 'Drawer Boxes',
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

$audit = array('# Perfume Packaging Product Import Audit', '');
$text_export = array('# Perfume Packaging Product Descriptions Text Only', '');

foreach ($products as $product) {
    $image_ids = array();
    foreach ($product['images'] as $index => $filename) {
        $image_ids[] = vpn_perfume_attachment_id(
            $filename,
            $product['keyword'] . ' for fragrance packaging, view ' . ($index + 1),
            $product['captions'][$index],
            $product['captions'][$index]
        );
    }

    $missing = array();
    foreach ($product['images'] as $index => $filename) {
        if (empty($image_ids[$index])) {
            $missing[] = $filename;
        }
    }

    if ($missing) {
        echo 'Missing images for ' . $product['title'] . ': ' . implode(', ', $missing) . PHP_EOL;
        continue;
    }

    $short = vpn_perfume_short_description($product);
    $content = vpn_perfume_content($product, $image_ids);
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
        if (!empty($term_cache[$slug])) {
            $term_ids[] = $term_cache[$slug];
        }
    }

    wp_set_object_terms($product_id, $term_ids, 'product_cat', false);
    wp_set_object_terms($product_id, 'simple', 'product_type', false);
    wp_set_object_terms(
        $product_id,
        array_merge(
            array($product['keyword'], 'perfume packaging box', 'fragrance packaging', 'custom cosmetic box', 'custom paper box'),
            $product['tags']
        ),
        'product_tag',
        false
    );
    set_post_thumbnail($product_id, $image_ids[0]);
    update_post_meta($product_id, '_product_image_gallery', implode(',', array_slice($image_ids, 1)));
    update_post_meta($product_id, '_sku', 'sample-perfume-202607-' . $product['slug']);
    update_post_meta($product_id, '_regular_price', '');
    update_post_meta($product_id, '_price', '');
    update_post_meta($product_id, '_stock_status', 'instock');
    update_post_meta($product_id, '_manage_stock', 'no');
    update_post_meta($product_id, '_visibility', 'visible');
    update_post_meta($product_id, '_custom_box_product_specs', vpn_perfume_specs($product));
    update_post_meta($product_id, '_vpn_sample_import', $marker);
    update_post_meta($product_id, 'rank_math_focus_keyword', $product['keyword']);
    update_post_meta($product_id, 'rank_math_title', $product['title'] . ' | VPN PAPER BOX MANUFACTURER');
    update_post_meta($product_id, 'rank_math_description', substr($product['title'] . ' for fragrance brands, customized with bottle insert, logo printing, paper material, finishing, and MOQ 1000 boxes.', 0, 154));

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
    $audit[] = '- Source files: ' . implode(', ', $product['images']);
    $audit[] = '- Missing image bases: none';
    $audit[] = '- Duplicate risk score: ' . $product['duplicate_risk'];
    $audit[] = '';

    $text_export[] = '## ' . $product['title'];
    $text_export[] = wp_strip_all_tags($short . "\n\n" . $saved_content);
    $text_export[] = '';

    echo 'Imported: ' . $product['title'] . ' (#' . $product_id . ') words=' . $words . ' images=' . count($image_ids) . ' figures=' . $figures . PHP_EOL;
}

file_put_contents(dirname(__DIR__) . '/product-samples-perfume-packaging-202607-audit.md', implode(PHP_EOL, $audit));
file_put_contents(dirname(__DIR__) . '/product-samples-perfume-packaging-202607-descriptions-text-only.md', implode(PHP_EOL, $text_export));

echo 'Perfume packaging product import complete.' . PHP_EOL;
