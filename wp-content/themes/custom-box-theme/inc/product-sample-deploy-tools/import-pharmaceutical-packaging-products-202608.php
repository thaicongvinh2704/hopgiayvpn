<?php
/**
 * Import the August 2026 pharmaceutical packaging batch from 40 Media Library images.
 *
 * Run:
 *   php tools/import-pharmaceutical-packaging-products-202608.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

function vpn_pharma_202608_link($path, $anchor)
{
    return '<a href="' . esc_url(home_url($path)) . '">' . esc_html($anchor) . '</a>';
}

function vpn_pharma_202608_file_base($filename)
{
    return preg_replace('/\.[^.]+$/', '', basename($filename));
}

function vpn_pharma_202608_attachment_id($filename, $alt, $title, $caption)
{
    $base = vpn_pharma_202608_file_base($filename);
    $ids = get_posts(array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => array(array(
            'key'     => '_wp_attached_file',
            'value'   => $base,
            'compare' => 'LIKE',
        )),
    ));

    $attachment_id = 0;
    foreach ($ids as $id) {
        $attached = (string) get_post_meta((int) $id, '_wp_attached_file', true);
        if (0 === strcasecmp(vpn_pharma_202608_file_base($attached), $base)) {
            $attachment_id = (int) $id;
            break;
        }
    }

    if (!$attachment_id) {
        $relative = '2026/08/' . basename($filename);
        $file = WP_CONTENT_DIR . '/uploads/' . $relative;
        if (!file_exists($file)) {
            return 0;
        }

        $filetype = wp_check_filetype(basename($file), null);
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
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $metadata = wp_generate_attachment_metadata($attachment_id, $file);
        if (!is_wp_error($metadata) && $metadata) {
            wp_update_attachment_metadata($attachment_id, $metadata);
        }
    }

    update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
    wp_update_post(array(
        'ID'           => $attachment_id,
        'post_title'   => $title,
        'post_excerpt' => $caption,
    ));

    return (int) $attachment_id;
}

function vpn_pharma_202608_figure($attachment_id, $caption, $narrow = false)
{
    $image = wp_get_attachment_image($attachment_id, 'large', false, array('loading' => 'lazy'));
    if (!$image) {
        return '';
    }

    return '<figure class="product-inline-figure product-inline-figure-small' . ($narrow ? ' is-narrow' : '') . '">' .
        $image . '<figcaption>' . esc_html($caption) . '</figcaption></figure>';
}

function vpn_pharma_202608_section($heading, $paragraphs)
{
    $html = '<h2>' . esc_html($heading) . '</h2>';
    foreach ($paragraphs as $paragraph) {
        $html .= '<p>' . $paragraph . '</p>';
    }
    return $html;
}

function vpn_pharma_202608_sentence_list($items)
{
    $items = array_values(array_filter($items));
    $last = array_pop($items);
    return $items ? implode(', ', $items) . ', and ' . $last : (string) $last;
}

function vpn_pharma_202608_specs($product)
{
    return array(
        array('label' => 'Feature', 'value' => $product['feature']),
        array('label' => 'Industrial Use', 'value' => 'Pharmaceutical and healthcare secondary packaging'),
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
        array('label' => 'Color', 'value' => 'Custom Pantone and CMYK healthcare color system'),
        array('label' => 'Size', 'value' => 'Customized size'),
        array('label' => 'Thickness', 'value' => 'Customized thickness'),
        array('label' => 'Single Piece Price', 'value' => 'Price based on size, board, insert, printing, finishing, testing, and quantity'),
        array('label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes'),
        array('label' => 'Product Name', 'value' => $product['title']),
        array('label' => 'Design', 'value' => "Customer's Specific Requirement"),
    );
}

function vpn_pharma_202608_short($product)
{
    return $product['title'] . ' is a custom secondary paper carton for ' . $product['inside'] . '. It is developed for ' . $product['buyers'] . ' that need ' . $product['problem'] . '. The packaging can be configured with ' . $product['structure'] . ', ' . $product['support'] . ', and approved artwork panels for ' . $product['information'] . '. It supports ' . $product['channels'] . '. Buyers should provide product samples, approved copy, market requirements, packing-line details, and test criteria before sampling. MOQ starts at 1000 boxes. Regulatory teams remain responsible for approving claims, warnings, identifiers, and market-specific information.';
}

function vpn_pharma_202608_content($product, $image_ids)
{
    $category = vpn_pharma_202608_link('/products/pharmaceutical-packaging-boxes/', 'pharmaceutical packaging boxes');
    $guide = vpn_pharma_202608_link('/what-information-pharmaceutical-paper-packaging/', 'pharmaceutical carton information checklist');
    $materials = 'paper material selection criteria';
    $artwork = 'print-ready packaging artwork preparation';
    $contact = vpn_pharma_202608_link('/contact/#quote', 'request a pharmaceutical packaging quote');
    $related_one = vpn_pharma_202608_link($product['related'][0][0], $product['related'][0][1]);
    $related_two = vpn_pharma_202608_link($product['related'][1][0], $product['related'][1][1]);
    $details = vpn_pharma_202608_sentence_list($product['details']);
    $tests = vpn_pharma_202608_sentence_list($product['tests']);
    $mistakes = vpn_pharma_202608_sentence_list($product['mistakes']);

    $html = vpn_pharma_202608_section($product['headings'][0], array(
        $product['title'] . ' is planned as secondary paper packaging for ' . $product['inside'] . '. It serves ' . $product['buyers'] . ' that must combine product protection, repeatable packing, readable information, and a professional healthcare presentation. The carton is not the sterile barrier or primary container; its job starts after the approved primary pack has been defined.',
        $product['overview_copy'] . ' This product sits within our ' . $category . ' range, but its dieline should be developed from the actual product geometry rather than borrowed from another medicine carton. Small differences in caps, shoulders, device guards, pouch stacks, or leaflet thickness can change the usable internal space.',
        'A practical RFQ should identify ' . $details . '. These inputs allow the box factory to estimate board use, insert tooling, printing controls, packing labor, master-carton quantity, and sampling work. They also reduce the risk of approving an attractive mockup that cannot run consistently in production.',
    ));

    $html .= vpn_pharma_202608_figure($image_ids[0], $product['captions'][0], true);

    $html .= vpn_pharma_202608_section($product['headings'][1], array(
        $product['fit_copy'] . ' The central packaging problem is ' . $product['problem'] . '. A few millimeters of uncontrolled movement can affect presentation, rub printed primary labels, bend a device component, or make the pack sound loose when handled. Excessive compression can be equally harmful.',
        'Measure the product in its final sale condition, including ' . $product['measurement'] . '. Record maximum dimensions across representative samples instead of relying only on a nominal drawing. If the supplier receives only an empty carton target, it cannot reliably plan clearance, support points, or the opening sequence.',
        'The packing trial should show how an operator loads the primary pack, where fingers need clearance, which face must remain visible, and whether the product can be removed without pulling a sensitive component. This turns fit into a controlled manufacturing requirement rather than a subjective judgment.',
    ));

    $html .= vpn_pharma_202608_section($product['headings'][2], array(
        $product['structure_copy'] . ' Recommended construction is ' . $product['structure'] . '. The final choice depends on filled weight, board stiffness, flap direction, packing speed, leaflet volume, display orientation, and whether the carton will receive tamper labels or automated coding.',
        'Internal support can use ' . $product['support'] . '. Support should contact stable zones of the primary pack and avoid loading delicate areas. Paperboard inserts are useful when recyclability and flat shipping matter; molded pulp or specialist trays may be evaluated when geometry and protection requirements are more demanding.',
        'Prototype evaluation should include repeated opening, closing, product removal, flap locking, insert assembly, and case packing. A structure that performs well by hand may still need changes when hundreds of cartons are erected, loaded, coded, and closed during a production shift.',
    ));

    $html .= vpn_pharma_202608_figure($image_ids[1], $product['captions'][1]);

    $html .= vpn_pharma_202608_section($product['headings'][3], array(
        $product['information_copy'] . ' Typical panel planning may need room for ' . $product['information'] . '. The responsible pharmaceutical, medical-device, quality, and regulatory teams must supply and approve the exact content for every market. The packaging supplier should not invent dosage statements, indications, warnings, symbols, or compliance claims.',
        'Use the ' . $guide . ' to organize fixed copy, variable batch zones, barcode specifications, serialization areas, Braille or accessibility requirements where applicable, and approval responsibilities. Keep critical text away from folds, glue seams, tuck edges, perforations, and surfaces that may curve during carton erection.',
        'Artwork should be built on the approved production dieline. The ' . $artwork . ' explains bleed, safe zones, linked fonts, image resolution, spot colors, barcode placement, and finish masks. Version names should clearly distinguish market, language, strength, pack count, device variant, and revision status.',
    ));

    $html .= vpn_pharma_202608_section($product['headings'][4], array(
        $product['material_copy'] . ' Suitable substrates include ' . $product['paper'] . '. Board caliper should be selected from carton dimensions, span, filled weight, insert pressure, print process, and distribution conditions rather than from appearance alone.',
        'Printing and finishing can include ' . $product['printing'] . '. Healthcare cartons usually benefit from controlled contrast and disciplined hierarchy. Decorative effects should never reduce the legibility of approved instructions, identifiers, expiry information, barcodes, or warning panels.',
        'The ' . $materials . ' can help procurement teams compare surface smoothness, stiffness, recycled content, coating, and print behavior. Any sustainability statement should be supported by the actual material specification and approved by the brand team responsible for environmental claims.',
    ));

    $html .= vpn_pharma_202608_figure($image_ids[2], $product['captions'][2], true);

    $html .= vpn_pharma_202608_section($product['headings'][5], array(
        $product['operations_copy'] . ' Packing-line design should confirm carton erection direction, product orientation, leaflet insertion, coding location, closure method, tamper-label application, and reject inspection. Manual, semi-automatic, and automatic lines place different limits on flap geometry and insert complexity.',
        'A clear work instruction should show the loading sequence and acceptable orientation. The carton should not require operators to force the product past a narrow flap or repeatedly adjust a tray. Reducing unnecessary hand movements supports output consistency and lowers the chance of mixed components or damaged print surfaces.',
        'For multi-SKU production, use controlled line clearance and reconciliation procedures defined by the buyer. Carton color alone is not a reliable identification system. Strength, dose, count, language, product code, and market should be checked against approved production documents.',
    ));

    $html .= vpn_pharma_202608_section($product['headings'][6], array(
        $product['channel_copy'] . ' Distribution may include ' . $product['channels'] . '. Each route changes the balance between shelf communication, case compression, tamper evidence, scanning, product orientation, and surface protection.',
        'Retail packs need consistent front-panel alignment and readable scanning zones. Clinic or institutional packs may prioritize rapid identification and case-level organization. Ecommerce programs may need an additional shipping carton because a folding pharmaceutical carton is not automatically designed to survive parcel handling by itself.',
        'Buyers building a coordinated healthcare range can compare ' . $related_one . ' and ' . $related_two . '. Related cartons may share typography, color rules, and procurement specifications, but cavity dimensions and protection logic should remain specific to the primary pack inside.',
    ));

    $html .= vpn_pharma_202608_figure($image_ids[3], $product['captions'][3]);

    $html .= vpn_pharma_202608_section($product['headings'][7], array(
        $product['qc_copy'] . ' A useful approval plan covers ' . $tests . '. Test methods, sample quantities, acceptance limits, conditioning, and responsible approvers should be agreed before mass production whenever they are material to the project.',
        'Print quality checks can cover color tolerance, text clarity, registration, barcode verification, variable-data position, coating, scuffing, and glue control. Structural checks can cover dimensions, squareness, flap engagement, insert fit, compression, opening force, and product removal.',
        'Retain approved references for artwork, color, dieline, materials, and packed configuration. When a primary pack changes, assess the carton again even if the product name is unchanged. A revised cap, nozzle, guard, pouch laminate, or leaflet can invalidate the previous fit.',
    ));

    $html .= vpn_pharma_202608_section($product['headings'][8], array(
        $product['sustainability_copy'] . ' Material reduction should begin with accurate sizing and efficient dieline nesting. Removing board without checking panel stiffness or product support can transfer damage and waste to the filling line or distribution stage.',
        'Paper inserts, single-material concepts, water-based coatings, responsibly sourced board, and clear disposal communication may be considered when compatible with product protection and approved claims. The secondary carton cannot compensate for moisture, oxygen, sterility, or chemical-barrier functions assigned to the primary packaging.',
        'Variant planning should separate elements that can remain common from those that must change. A shared structure may reduce tooling complexity, but every strength, count, language, market, or device configuration needs controlled artwork and a verified fit before release.',
    ));

    $html .= vpn_pharma_202608_section($product['headings'][9], array(
        'Common mistakes for this product include ' . $mistakes . '. These issues are easier to correct during the structural sample and artwork proof stages than after printed cartons reach the packing site.',
        'For quotation, provide ' . $product['quote'] . '. Also state the required quantity, annual forecast, delivery schedule, packing location, target market, print standard, proofing method, test expectations, and whether the order contains multiple artwork versions.',
        'Send the approved packaging brief through ' . $contact . '. VPN can develop the dieline, structural sample, insert, printing plan, and mass-production quotation. Final legal, medical, regulatory, serialization, accessibility, and market-specific approval remains with the buyer and its authorized quality teams.',
    ));

    return $html;
}

function vpn_pharma_202608_products()
{
    $common_views = array('front three-quarter carton view', 'back information-panel view', 'open carton and product-fit view', 'open presentation and component view');

    $products = array(
        array(
            'slug' => 'custom-autoinjector-pen-box',
            'title' => 'CUSTOM AUTOINJECTOR PEN BOX',
            'keyword' => 'autoinjector pen box',
            'inside' => 'single-use autoinjector pens, emergency injection devices, biologic delivery pens, and patient instruction leaflets',
            'buyers' => 'pharmaceutical brands, medical-device companies, contract packers, and injection-device program managers',
            'problem' => 'holding a long injection device without loading the activation end, needle guard, cap, or viewing window',
            'structure' => 'a straight-tuck or reverse-tuck folding carton with a locking paperboard cradle and dedicated leaflet space',
            'support' => 'die-cut end stops, a folded bridge, finger-access notches, and an orientation key around the stable pen body',
            'information' => 'device name, strength, route, storage, unique device identification, lot and expiry data, warnings, instructions, and serialization',
            'channels' => 'specialty pharmacy, clinic distribution, home treatment programs, cold-chain secondary packing, and export supply',
            'measurement' => 'the activation end, safety cap, needle guard, dose window, label overlap, and any sealed primary tray or pouch',
            'overview_copy' => 'An autoinjector is a directional device, so the carton and insert should make the intended orientation obvious while protecting the ends from accidental pressure.',
            'fit_copy' => 'The pen body may appear cylindrical and simple, yet the activation end and safety features create zones that should remain unloaded during packing and transit.',
            'structure_copy' => 'A slim outer carton can use a folded cradle that captures the main barrel at two points while leaving device labels and removal areas visible.',
            'information_copy' => 'The panel hierarchy should help users and healthcare staff distinguish product, strength, device instructions, storage conditions, and emergency-use information quickly.',
            'material_copy' => 'Clean white SBS or high-quality ivory board supports fine medical typography, controlled color bands, and a precise device presentation.',
            'operations_copy' => 'The device should enter the cradle in one repeatable direction without operators pressing the activation mechanism or scraping the printed barrel.',
            'channel_copy' => 'Autoinjector programs may move through controlled healthcare networks, specialty pharmacies, clinics, or direct-to-patient fulfillment.',
            'qc_copy' => 'Fit trials should verify end clearance, retention after vibration, removal force, leaflet space, code readability, and closure security.',
            'sustainability_copy' => 'A right-sized paper cradle can reduce mixed-material inserts when its support performance is confirmed with the approved device configuration.',
            'feature' => 'Directional device cradle, protected activation end, leaflet space, controlled medical artwork',
            'paper' => 'SBS paperboard, ivory board, or FSC-certified medical-grade folding boxboard',
            'box_type' => 'Folding autoinjector device carton with insert',
            'shape' => 'Long rectangular carton',
            'accessories' => 'Paperboard cradle, instruction leaflet, tamper label, serialization panel',
            'liner' => 'Die-cut paperboard device cradle',
            'printing' => 'Offset printing, Pantone color bands, matte aqueous coating, barcode and small-text control',
            'details' => array('device length and maximum diameter', 'activation-end clearance', 'needle-guard geometry', 'device orientation', 'leaflet size and fold', 'storage statement', 'coding area', 'tamper-label position', 'packing-line method', 'master-case quantity'),
            'tests' => array('device retention', 'activation-end clearance', 'insert compression', 'drop and vibration performance', 'removal force', 'leaflet fit', 'barcode verification', 'carton closure'),
            'mistakes' => array('supporting the device on its activation end', 'hiding the dose or inspection window', 'omitting finger clearance', 'using one cavity for different pen diameters without verification'),
            'quote' => 'a dimensioned device drawing, protected-zone map, real or controlled dummy pens, leaflet dimensions, approved artwork copy, coding requirements, and packing-line sequence',
            'related' => array(array('/product/custom-prefilled-syringe-box/', 'prefilled syringe secondary cartons'), array('/product/custom-medical-kit-packaging-box/', 'medical kit packaging with organized inserts')),
            'headings' => array('Autoinjector Pen Secondary Cartons for Device Protection', 'Protecting Activation Ends and Safety Features', 'Cradle and Carton Structures for Injection Pens', 'Device Instructions, Identification, and Panel Hierarchy', 'Paperboard and Print Controls for Autoinjector Packs', 'Loading Autoinjector Pens Without Device Pressure', 'Specialty Pharmacy and Patient Distribution', 'Autoinjector Carton Fit and Quality Checks', 'Right-Sizing a Paper-Based Device Pack', 'Autoinjector Packaging RFQ and Approval Checklist'),
            'duplicate_risk' => 3,
        ),
        array(
            'slug' => 'custom-blister-pack-medicine-box',
            'title' => 'CUSTOM BLISTER PACK MEDICINE BOX',
            'keyword' => 'blister pack medicine box',
            'inside' => 'tablet and capsule blister cards, patient leaflets, calendar packs, and multi-blister medicine courses',
            'buyers' => 'medicine manufacturers, generic drug brands, contract packers, pharmacy suppliers, and clinical program teams',
            'problem' => 'controlling blister count and leaflet fit while preventing foil faces, formed cavities, and card corners from rubbing or bending',
            'structure' => 'a folding medicine carton sized to the blister stack with tuck or glued closure and optional paperboard divider',
            'support' => 'stack-control tabs, a folded spacer, leaflet separation, and end clearance around formed blister cavities',
            'information' => 'product and strength, tablet count, dosage copy, route, warnings, storage, lot and expiry, barcode, serialization, and market identifiers',
            'channels' => 'pharmacy shelves, hospital supply, clinic dispensing, government tenders, clinical programs, and export medicine distribution',
            'measurement' => 'the formed blister cavity height, sealed flange, foil overhang, card bow, stack count, leaflet thickness, and tear-off features',
            'overview_copy' => 'Blister cards combine rigid formed pockets with vulnerable lidding foil, so carton depth and stack control should be based on the sealed packs supplied by the filling site.',
            'fit_copy' => 'A carton that is too shallow can press on blister domes; one that is too deep allows cards and leaflets to shuffle, producing damaged corners and an untidy opening experience.',
            'structure_copy' => 'The most efficient format is often a compact folding carton, but internal tabs or a paper spacer may be useful for multiple cards, calendar order, or leaflet separation.',
            'information_copy' => 'Medicine strength and pack count must be distinguished clearly when several blister SKUs share a common visual system.',
            'material_copy' => 'Folding boxboard with a smooth print face supports dense text, color-coded strengths, Braille where approved, and dependable carton-machine performance.',
            'operations_copy' => 'The loading sequence should keep the blister stack aligned, prevent leaflets from catching on cut foil edges, and maintain the approved card order.',
            'channel_copy' => 'Blister cartons often move through high-volume pharmacy and institutional channels where rapid SKU recognition and case reconciliation are important.',
            'qc_copy' => 'Approval should check stack compression, cavity clearance, leaflet retention, scuffing, count presentation, barcode quality, and closure after repeated handling.',
            'sustainability_copy' => 'Accurate stack sizing can reduce excess carton volume while the primary blister retains the barrier function required for the medicine.',
            'feature' => 'Controlled blister stack, leaflet separation, clear medicine count, pharmacy-ready artwork',
            'paper' => 'SBS paperboard, ivory board, or recyclable folding boxboard',
            'box_type' => 'Folding medicine carton for blister cards',
            'shape' => 'Rectangular tablet carton',
            'accessories' => 'Patient leaflet, paperboard spacer, tamper label, Braille area where specified',
            'liner' => 'Folded paperboard stack spacer',
            'printing' => 'High-resolution offset printing, strength color coding, matte aqueous coating, barcode and variable-data zones',
            'details' => array('blister card dimensions', 'formed cavity height', 'cards per carton', 'tablet or capsule count', 'leaflet folded size', 'card orientation', 'strength variants', 'coding zone', 'tamper-evidence plan', 'case-packing quantity'),
            'tests' => array('blister dome clearance', 'foil scuff inspection', 'stack movement', 'leaflet loading', 'carton compression', 'barcode grade', 'Braille check where specified', 'count reconciliation'),
            'mistakes' => array('measuring an empty formed web instead of a sealed blister', 'compressing blister domes', 'mixing leaflet and card order', 'relying only on carton color to distinguish strengths'),
            'quote' => 'sealed blister samples, maximum card dimensions, cards per carton, leaflet dummy, approved information map, strength variants, coding equipment details, and case configuration',
            'related' => array(array('/product/custom-pill-packaging-box/', 'custom pill packaging cartons'), array('/product/custom-pharmaceutical-medicine-packaging-boxes/', 'general pharmaceutical medicine boxes')),
            'headings' => array('Medicine Cartons Designed Around Real Blister Cards', 'Blister Dome, Foil, and Stack Clearance', 'Compact Folding Structures for Tablet Courses', 'Organizing Strength, Count, and Dosage Information', 'Board Selection for High-Volume Medicine Cartons', 'Blister and Leaflet Packing-Line Sequence', 'Pharmacy, Hospital, and Tender Distribution', 'Quality Controls for Blister Medicine Boxes', 'Reducing Empty Space Around Blister Packs', 'Blister Carton Quotation Inputs and Common Errors'),
            'duplicate_risk' => 4,
        ),
        array(
            'slug' => 'custom-eye-drop-packaging-box',
            'title' => 'CUSTOM EYE DROP PACKAGING BOX',
            'keyword' => 'eye drop packaging box',
            'inside' => 'ophthalmic dropper bottles, preservative-free eye drops, small solution bottles, and folded patient information leaflets',
            'buyers' => 'ophthalmic brands, pharmaceutical manufacturers, contract packers, clinic suppliers, and private-label eye-care companies',
            'problem' => 'keeping a small bottle upright and easy to remove without pressing the nozzle, tamper ring, or dropper cap',
            'structure' => 'a vertical folding carton with controlled top clearance, bottom support, leaflet space, and optional paperboard collar',
            'support' => 'a folded base platform, neck collar, side rails, and finger clearance beside the bottle body',
            'information' => 'solution name, concentration, fill volume, administration instructions, storage, sterility statements, warnings, lot, expiry, and barcode data',
            'channels' => 'retail pharmacy, ophthalmology clinics, hospital supply, prescription fulfillment, and export eye-care distribution',
            'measurement' => 'the bottle body, shoulder, nozzle, cap, tamper ring, label overlap, fill weight, leaflet fold, and required headspace',
            'overview_copy' => 'Eye-drop bottles are small but top-sensitive, and the customer normally grips the cap and shoulder area when removing them from the carton.',
            'fit_copy' => 'The carton must prevent rattle while leaving enough headspace so the nozzle and closure are not loaded when cartons are stacked or squeezed.',
            'structure_copy' => 'A vertical tuck carton can use a folded base or paper collar to stabilize the bottle and reserve one side for a compact leaflet.',
            'information_copy' => 'Ophthalmic packs need a calm hierarchy that keeps product identity, concentration, route, storage, and safe-use information readable on narrow panels.',
            'material_copy' => 'Smooth white paperboard and precise blue or product-specific color coding suit the clean, high-legibility expectations of eye-care packaging.',
            'operations_copy' => 'Operators should load bottles by the stable body, confirm cap seating, add the correct leaflet, and avoid forcing the nozzle under a tight top flap.',
            'channel_copy' => 'Narrow eye-drop cartons must remain easy to identify and scan when stored upright in pharmacy drawers, shelf trays, or clinic inventory.',
            'qc_copy' => 'Check upright stability, cap clearance, removal access, leaflet pressure, closure, print legibility, and resistance to bottle movement.',
            'sustainability_copy' => 'A simple folded paper platform may replace a plastic locator when bottle retention and line performance are demonstrated.',
            'feature' => 'Upright bottle control, protected dropper cap, leaflet space, narrow-panel readability',
            'paper' => 'SBS paperboard or high-white ivory folding boxboard',
            'box_type' => 'Vertical folding eye-drop bottle carton',
            'shape' => 'Tall narrow rectangular carton',
            'accessories' => 'Folded leaflet, bottle collar, base platform, tamper label',
            'liner' => 'Paperboard bottle collar or folded base',
            'printing' => 'Offset printing, medical blue or Pantone accents, matte aqueous coating, fine text and barcode control',
            'details' => array('bottle body diameter', 'overall cap height', 'nozzle protection zone', 'fill volume', 'leaflet fold', 'headspace target', 'label orientation', 'coding area', 'tamper feature', 'shipping orientation'),
            'tests' => array('cap clearance', 'upright retention', 'bottle removal', 'leaflet fit', 'panel bowing', 'barcode scan', 'closure security', 'case compression'),
            'mistakes' => array('using the cap as a load-bearing point', 'leaving no finger access', 'overfilling the carton with leaflet folds', 'placing critical copy across a narrow glue seam'),
            'quote' => 'filled bottle samples, maximum closure dimensions, protected nozzle zone, leaflet dummy, label orientation, approved copy, coding requirements, and target case count',
            'related' => array(array('/product/custom-liquid-medicine-bottle-box/', 'liquid medicine bottle cartons'), array('/product/custom-nasal-spray-packaging-box/', 'nasal spray bottle packaging')),
            'headings' => array('Vertical Eye Drop Cartons for Small Ophthalmic Bottles', 'Nozzle Clearance and Upright Bottle Fit', 'Paper Collars and Base Supports for Eye Drops', 'Readable Information on Narrow Eye-Care Panels', 'Clean Paperboard and Controlled Medical Colors', 'Bottle and Leaflet Loading for Eye-Drop Lines', 'Pharmacy Storage and Ophthalmic Distribution', 'Eye Drop Carton Retention and Print Checks', 'Paper-Based Support for Compact Bottle Packs', 'Eye Drop Packaging Quote Preparation'),
            'duplicate_risk' => 3,
        ),
        array(
            'slug' => 'custom-inhaler-packaging-box',
            'title' => 'CUSTOM INHALER PACKAGING BOX',
            'keyword' => 'inhaler packaging box',
            'inside' => 'metered-dose inhalers, dry-powder inhalers, actuator devices, dose-counter inhalers, and patient instruction leaflets',
            'buyers' => 'respiratory medicine brands, device manufacturers, contract packers, hospitals, and pharmacy suppliers',
            'problem' => 'accommodating an irregular actuator profile and mouthpiece without pressing the dose counter, canister, cap, or patient-use surfaces',
            'structure' => 'a folding respiratory-device carton with a shaped paperboard platform, leaflet compartment, and positive opening orientation',
            'support' => 'side supports around the actuator body, mouthpiece clearance, an end stop, and a separate leaflet channel',
            'information' => 'medicine and device name, strength, dose count, priming and cleaning instructions, storage, warnings, lot, expiry, barcode, and serialization',
            'channels' => 'pharmacy retail, respiratory clinics, hospital discharge, chronic-care programs, and international medicine distribution',
            'measurement' => 'the actuator body, mouthpiece cap, canister projection, dose counter, label, widest profile, leaflet fold, and orientation',
            'overview_copy' => 'Inhalers have asymmetric device geometry, and the mouthpiece or dose counter can be damaged or hidden by a poorly planned support.',
            'fit_copy' => 'The pack should control the actuator body while preserving access to the cap and avoiding pressure on surfaces used by the patient.',
            'structure_copy' => 'A shaped folded insert can establish one loading direction, support the stable housing, and keep the leaflet from wrapping around the device.',
            'information_copy' => 'Respiratory packaging often carries both medicine information and device-use instructions, so panel hierarchy and leaflet coordination require early planning.',
            'material_copy' => 'Stiff folding boxboard supports a wider device carton and provides a smooth surface for diagrams, dose-count information, and controlled color families.',
            'operations_copy' => 'The line should verify device orientation, mouthpiece-cap presence, leaflet version, dose variant, and carton coding before closure.',
            'channel_copy' => 'Inhaler cartons must remain recognizable across repeat prescriptions, hospital programs, and pharmacy shelves where several strengths may look related.',
            'qc_copy' => 'Device fit checks should cover actuator support, mouthpiece clearance, dose-counter visibility, leaflet pressure, vibration, and removal force.',
            'sustainability_copy' => 'Replacing a generic plastic tray with a fitted paper platform may be evaluated when it does not compromise device protection or packing speed.',
            'feature' => 'Shaped inhaler support, mouthpiece clearance, leaflet channel, respiratory information layout',
            'paper' => 'SBS paperboard, ivory board, or strong folding boxboard',
            'box_type' => 'Folding inhaler device carton with paper insert',
            'shape' => 'Rectangular respiratory-device carton',
            'accessories' => 'Shaped paper insert, patient leaflet, tamper label, coding panel',
            'liner' => 'Folded paperboard inhaler platform',
            'printing' => 'Offset printing, device-use diagrams, Pantone strength coding, matte coating, barcode control',
            'details' => array('maximum device profile', 'mouthpiece-cap clearance', 'dose-counter location', 'canister projection', 'device orientation', 'leaflet size', 'strength variants', 'coding panel', 'tamper plan', 'case orientation'),
            'tests' => array('actuator support', 'mouthpiece protection', 'dose-counter clearance', 'leaflet fit', 'device removal', 'vibration retention', 'barcode grade', 'closure strength'),
            'mistakes' => array('treating the inhaler as a simple rectangle', 'pressing the mouthpiece cap', 'allowing the leaflet to obscure the device', 'using similar artwork without strong dose differentiation'),
            'quote' => 'finished inhaler samples or controlled dummies, maximum profile drawing, protected zones, leaflet dummy, dose variants, approved diagrams, coding needs, and line-loading method',
            'related' => array(array('/product/custom-nasal-spray-packaging-box/', 'nasal spray device cartons'), array('/product/custom-medical-kit-packaging-box/', 'medical device kit packaging')),
            'headings' => array('Custom Cartons Built Around Inhaler Geometry', 'Mouthpiece, Actuator, and Dose-Counter Protection', 'Directional Paper Inserts for Respiratory Devices', 'Combining Medicine and Device-Use Information', 'Board Stiffness for Wider Inhaler Cartons', 'Controlling Device and Leaflet Loading', 'Respiratory Care Distribution and SKU Recognition', 'Inhaler Carton Device-Fit Testing', 'Reducing Mixed Materials in Inhaler Packs', 'Inhaler Packaging Development Brief'),
            'duplicate_risk' => 3,
        ),
        array(
            'slug' => 'custom-liquid-medicine-bottle-box',
            'title' => 'CUSTOM LIQUID MEDICINE BOTTLE BOX',
            'keyword' => 'liquid medicine bottle box',
            'inside' => 'glass or plastic syrup bottles, oral solutions, suspensions, measuring devices, and medicine leaflets',
            'buyers' => 'oral medicine brands, pediatric product manufacturers, contract fillers, pharmacy suppliers, and healthcare distributors',
            'problem' => 'supporting a dense filled bottle upright while protecting the cap, label, dosing accessory, and carton panels from leakage-related damage',
            'structure' => 'a reinforced vertical folding carton with a bottom platform, neck clearance, accessory pocket, and secure tuck or glued base',
            'support' => 'a paperboard base, neck locator, side buffer, and separate compartment for a cup, spoon, syringe, or leaflet',
            'information' => 'product name, concentration, fill volume, dosing instructions, shake statements, storage, warnings, lot, expiry, barcode, and manufacturer data',
            'channels' => 'pharmacy retail, pediatric care, clinic supply, hospital dispensing, private label programs, and export distribution',
            'measurement' => 'the filled bottle weight, base diameter, shoulder, neck, cap, tamper ring, label seam, dosing accessory, and leaflet',
            'overview_copy' => 'Liquid medicine bottles concentrate weight in a small footprint, making bottom strength, upright control, and cap clearance more important than the empty bottle suggests.',
            'fit_copy' => 'Bottle movement can scuff labels and bow panels, while a tight top fit can transfer compression into the cap or tamper ring.',
            'structure_copy' => 'A reinforced base and optional neck locator stabilize the bottle; a side pocket can prevent the measuring device from rubbing against the label.',
            'information_copy' => 'Liquid-medicine cartons need enough hierarchy for concentration, volume, dosing, storage, shake instructions, and accessory information without crowding.',
            'material_copy' => 'A higher-caliper SBS or ivory board can provide vertical stiffness and clean printing for bottle packs with greater filled weight.',
            'operations_copy' => 'The line should confirm cap torque status under the buyer process, bottle label orientation, accessory presence, leaflet version, and bottom closure before case packing.',
            'channel_copy' => 'Bottle cartons may stand on pharmacy shelves but also travel through cases where upright arrows, dividers, and leakage response are considered by the buyer.',
            'qc_copy' => 'Packed testing should examine bottom strength, cap clearance, bottle movement, accessory retention, panel bulging, print scuff, and case compression.',
            'sustainability_copy' => 'Right-sizing around the filled bottle and using a folded paper locator can reduce void space while preserving upright stability.',
            'feature' => 'Reinforced bottle base, upright support, cap clearance, dosing-accessory compartment',
            'paper' => 'High-caliper SBS, ivory board, or strong recyclable folding boxboard',
            'box_type' => 'Vertical liquid medicine bottle carton',
            'shape' => 'Tall rectangular bottle box',
            'accessories' => 'Bottle locator, measuring cup or spoon compartment, leaflet, tamper label',
            'liner' => 'Folded paperboard base and neck locator',
            'printing' => 'Offset printing, protective aqueous or matte coating, dosing diagrams, barcode and variable-data zones',
            'details' => array('filled bottle weight', 'base diameter', 'cap and tamper-ring size', 'label orientation', 'dosing accessory', 'leaflet fold', 'bottom closure method', 'storage copy', 'coding zone', 'master-case divider plan'),
            'tests' => array('bottom load', 'bottle retention', 'cap clearance', 'accessory count', 'leaflet fit', 'panel bow', 'carton scuffing', 'case compression'),
            'mistakes' => array('sizing from an empty bottle only', 'using a weak crash-lock or tuck base for the filled weight', 'letting the measuring device rub the label', 'leaving no plan for leakage-damaged cartons'),
            'quote' => 'filled and closed bottle samples, maximum dimensions and weight, cap details, dosing accessory, leaflet dummy, label orientation, bottom style, approved copy, and shipping-case plan',
            'related' => array(array('/product/custom-eye-drop-packaging-box/', 'small dropper bottle cartons'), array('/product/custom-pharmaceutical-tube-box/', 'pharmaceutical tube packaging cartons')),
            'headings' => array('Secondary Cartons for Filled Liquid Medicine Bottles', 'Bottle Weight, Cap Clearance, and Upright Fit', 'Reinforced Bases and Dosing-Accessory Compartments', 'Dosing and Storage Information for Oral Liquids', 'Board Strength for Dense Bottle Packs', 'Bottle, Accessory, and Leaflet Packing Sequence', 'Pharmacy Display and Upright Case Packing', 'Packed-Bottle Carton Quality Tests', 'Reducing Void Space Around Medicine Bottles', 'Liquid Medicine Bottle Box RFQ Checklist'),
            'duplicate_risk' => 3,
        ),
        array(
            'slug' => 'custom-nasal-spray-packaging-box',
            'title' => 'CUSTOM NASAL SPRAY PACKAGING BOX',
            'keyword' => 'nasal spray packaging box',
            'inside' => 'metered nasal spray bottles, pump dispensers, saline sprays, prescription devices, and patient leaflets',
            'buyers' => 'nasal-care brands, pharmaceutical manufacturers, device fillers, contract packers, and pharmacy distributors',
            'problem' => 'stabilizing a pump bottle while protecting the nozzle cap, actuator, tamper feature, and spray-device orientation',
            'structure' => 'a tall folding carton with a bottle base, pump-head clearance, optional neck collar, and leaflet channel',
            'support' => 'a die-cut bottle platform, neck guide, side rails, and finger-access space around the stable bottle body',
            'information' => 'spray name, concentration, metered doses, priming, cleaning, storage, warnings, lot, expiry, barcode, and device-use diagrams',
            'channels' => 'retail pharmacy, allergy clinics, hospital supply, seasonal healthcare programs, and export nasal-care distribution',
            'measurement' => 'the bottle base, shoulder, pump, actuator, nozzle cap, tamper ring, label, dose configuration, and folded leaflet',
            'overview_copy' => 'Nasal spray packs combine a bottle with a mechanical pump, making head clearance and one-direction loading central to carton design.',
            'fit_copy' => 'The pump and nozzle should remain unloaded while the lower bottle body is controlled against rattle and label scuffing.',
            'structure_copy' => 'A base platform and neck guide can keep the spray upright, reserve space for instructions, and make the cap visible when opened.',
            'information_copy' => 'The panel system may need both medicine copy and device steps such as priming, cleaning, dose count, and disposal instructions.',
            'material_copy' => 'Tall narrow cartons benefit from stiff, smooth board that maintains vertical panels and reproduces small device-use diagrams clearly.',
            'operations_copy' => 'Loading should use the bottle body, maintain nozzle-cap presence, align the label, and prevent the leaflet from pushing against the pump.',
            'channel_copy' => 'Seasonal and chronic-use nasal sprays require clear variant recognition when multiple strengths, formulations, or pack counts share pharmacy space.',
            'qc_copy' => 'Verify pump clearance, upright retention, cap presence, label visibility, leaflet fit, removal access, and scanning performance.',
            'sustainability_copy' => 'A folded paper locator can provide orientation and reduce empty volume without claiming barrier functions supplied by the primary bottle.',
            'feature' => 'Protected spray pump, upright bottle locator, leaflet channel, device-use artwork',
            'paper' => 'SBS paperboard or stiff ivory folding boxboard',
            'box_type' => 'Vertical nasal spray bottle carton',
            'shape' => 'Tall narrow spray-device carton',
            'accessories' => 'Paper bottle locator, leaflet, tamper label, serialization area',
            'liner' => 'Paperboard base and neck guide',
            'printing' => 'Offset printing, instruction diagrams, Pantone healthcare accents, matte coating, barcode control',
            'details' => array('bottle and pump height', 'nozzle-cap width', 'protected actuator zone', 'dose count', 'label orientation', 'leaflet fold', 'device-use diagrams', 'tamper feature', 'coding location', 'case orientation'),
            'tests' => array('pump-head clearance', 'upright retention', 'cap presence', 'bottle removal', 'leaflet pressure', 'carton compression', 'barcode scan', 'panel legibility'),
            'mistakes' => array('resting the top flap on the pump', 'allowing the leaflet to bend the actuator', 'omitting priming-information space', 'using the same color hierarchy for easily confused variants'),
            'quote' => 'finished spray bottles, pump and cap drawings, protected zones, leaflet dummy, dose variants, approved instructions, coding details, and line-loading orientation',
            'related' => array(array('/product/custom-eye-drop-packaging-box/', 'eye drop bottle cartons'), array('/product/custom-inhaler-packaging-box/', 'respiratory inhaler cartons')),
            'headings' => array('Nasal Spray Cartons With Controlled Pump Clearance', 'Protecting the Nozzle, Cap, and Actuator', 'Bottle Locators for Tall Spray Packaging', 'Priming and Device-Use Information Layout', 'Stiff Paperboard for Narrow Spray Cartons', 'Loading Pump Bottles and Patient Leaflets', 'Seasonal and Chronic Nasal-Care Distribution', 'Nasal Spray Carton Fit Verification', 'Compact Paper Support for Spray Bottles', 'Nasal Spray Packaging Quotation Details'),
            'duplicate_risk' => 3,
        ),
        array(
            'slug' => 'custom-pharmaceutical-tube-box',
            'title' => 'CUSTOM PHARMACEUTICAL TUBE BOX',
            'keyword' => 'pharmaceutical tube box',
            'inside' => 'ointment tubes, topical cream tubes, medicated gel tubes, dermatology products, and folded medicine leaflets',
            'buyers' => 'topical medicine brands, dermatology manufacturers, contract fillers, pharmacy suppliers, and private-label healthcare companies',
            'problem' => 'protecting the tube shoulder, nozzle, cap, crimp, and printed body from bending or rubbing inside a long narrow carton',
            'structure' => 'a long folding carton with end clearance, optional paperboard rails, leaflet space, and tuck or glued closure',
            'support' => 'folded side rails, a cap stop, crimp clearance, and a leaflet separator beside or above the tube',
            'information' => 'active product identity, strength, net weight, application instructions, warnings, storage, lot, expiry, barcode, and manufacturer details',
            'channels' => 'pharmacy retail, dermatology clinics, hospital dispensing, ecommerce healthcare, and export topical-medicine programs',
            'measurement' => 'the filled tube width and thickness, shoulder, nozzle, cap, sealed crimp, printed body, leaflet fold, and maximum bow',
            'overview_copy' => 'A filled pharmaceutical tube is flexible through the body but rigid at the cap and crimp, so a carton should avoid forcing those ends.',
            'fit_copy' => 'Loose tubes can slide and crease; tight cartons can rub decoration, distort the shoulder, or load the cap and sealed crimp.',
            'structure_copy' => 'A long tuck carton with shallow paper rails can center the tube, create finger access, and keep the leaflet away from the crimp edge.',
            'information_copy' => 'Topical medicine panels must organize strength, net weight, route or application information, warnings, and product identification on a narrow format.',
            'material_copy' => 'Smooth SBS or ivory board supports fine dermatology typography, clean white areas, and scuff-resistant coating around a long carton.',
            'operations_copy' => 'The line should orient the cap and crimp consistently, prevent the leaflet from catching, and avoid pushing the tube through tight end flaps.',
            'channel_copy' => 'Tube cartons need strong end recognition in pharmacy drawers and clean side panels for shelf, clinic, and ecommerce handling.',
            'qc_copy' => 'Check cap and crimp clearance, tube removal, rail alignment, surface rub, leaflet fit, closure, coding, and carton straightness.',
            'sustainability_copy' => 'Accurate tube dimensions and light folded rails can reduce carton volume while maintaining controlled end clearance.',
            'feature' => 'Tube end clearance, cap and crimp protection, leaflet separation, narrow-panel print',
            'paper' => 'SBS paperboard, ivory board, or recyclable folding boxboard',
            'box_type' => 'Long folding carton for pharmaceutical tubes',
            'shape' => 'Long narrow rectangular carton',
            'accessories' => 'Paper rails, leaflet separator, tamper label, coding zone',
            'liner' => 'Folded paperboard side rails',
            'printing' => 'Offset printing, matte or aqueous coating, fine medical text, barcode and lot-expiry zones',
            'details' => array('filled tube dimensions', 'cap diameter', 'crimp width', 'tube bow tolerance', 'print-rub zones', 'leaflet fold', 'loading direction', 'coding area', 'tamper label', 'case quantity'),
            'tests' => array('cap clearance', 'crimp clearance', 'tube removal', 'surface scuffing', 'leaflet fit', 'end-flap closure', 'carton straightness', 'barcode verification'),
            'mistakes' => array('measuring a flat empty tube', 'pressing the crimp against an end flap', 'providing no removal notch', 'allowing a leaflet edge to scrape the printed tube'),
            'quote' => 'filled and sealed tube samples, maximum bowed dimensions, cap and crimp details, leaflet dummy, loading direction, approved copy, coding area, and shipping-case plan',
            'related' => array(array('/product/custom-liquid-medicine-bottle-box/', 'liquid medicine bottle boxes'), array('/product/custom-pharmaceutical-medicine-packaging-boxes/', 'custom pharmaceutical cartons')),
            'headings' => array('Long Folding Cartons for Pharmaceutical Tubes', 'Protecting Tube Caps, Shoulders, and Crimps', 'Paper Rails and Leaflet Separation', 'Topical Medicine Information on Narrow Panels', 'Scuff-Resistant Board and Printing Choices', 'Tube Orientation on the Packing Line', 'Dermatology, Pharmacy, and Ecommerce Channels', 'Pharmaceutical Tube Carton Quality Checks', 'Right-Sizing Around Filled Ointment Tubes', 'Tube Packaging Sample and Quote Inputs'),
            'duplicate_risk' => 3,
        ),
        array(
            'slug' => 'custom-prefilled-syringe-box',
            'title' => 'CUSTOM PREFILLED SYRINGE BOX',
            'keyword' => 'prefilled syringe box',
            'inside' => 'prefilled syringes already protected by approved primary packaging, safety syringes, injection devices, and patient or professional instructions',
            'buyers' => 'biopharmaceutical companies, injection-device manufacturers, contract packers, clinical suppliers, and specialty medicine programs',
            'problem' => 'securing the approved syringe primary pack without transferring force to the plunger, flange, needle shield, safety guard, or label',
            'structure' => 'a secondary folding carton with a locked paperboard tray or approved device tray, controlled end clearance, and instruction compartment',
            'support' => 'a non-sterile secondary cradle around stable tray zones, end stops, finger access, and separation from the instruction leaflet',
            'information' => 'product and strength, route, storage, handling, device identification, lot, expiry, tamper evidence, barcode, serialization, and professional-use statements',
            'channels' => 'specialty pharmacy, hospital supply, clinic administration, cold-chain programs, clinical distribution, and export biopharmaceutical supply',
            'measurement' => 'the approved sealed primary tray or pouch, syringe guard, plunger projection, flange, label, device count, leaflet, and temperature-conditioned dimensions',
            'overview_copy' => 'For prefilled syringes, the secondary paper carton must be designed around the approved sterile-barrier or primary tray rather than touching an exposed syringe as an improvised holder.',
            'fit_copy' => 'Plunger, flange, needle shield, and safety-device zones should remain free from carton pressure, while the primary pack is retained against movement.',
            'structure_copy' => 'A locked tray or cradle can support stable primary-pack edges, establish device orientation, and separate professional instructions from sensitive components.',
            'information_copy' => 'High-value injectable products require disciplined strength, route, storage, handling, device, lot, and serialization hierarchy across carton panels.',
            'material_copy' => 'High-quality folding boxboard supports clean small text, controlled cold-chain handling instructions, tamper labels, and premium specialty-medicine presentation.',
            'operations_copy' => 'Loading and inspection should avoid contact with protected device zones and follow the buyer-approved line, cleanliness, reconciliation, and temperature-control procedures.',
            'channel_copy' => 'Prefilled syringe cartons may travel through specialty and cold-chain networks where case labels, orientation, tamper evidence, and traceability are closely controlled.',
            'qc_copy' => 'Testing should use the approved primary-pack configuration and verify protected-zone clearance, tray retention, removal force, leaflet fit, and case performance.',
            'sustainability_copy' => 'Secondary board and insert optimization should never alter the validated sterile barrier, device protection, or temperature-management system.',
            'feature' => 'Protected syringe-device zones, locked secondary cradle, instruction compartment, serialization layout',
            'paper' => 'Premium SBS, high-stiffness ivory board, or specified folding boxboard',
            'box_type' => 'Secondary prefilled-syringe carton with cradle',
            'shape' => 'Long rectangular injection-device box',
            'accessories' => 'Locked cradle, instruction leaflet, tamper label, serialization panel',
            'liner' => 'Paperboard secondary tray or buyer-approved device tray',
            'printing' => 'Controlled offset printing, Pantone strength coding, matte aqueous coating, serialization and barcode zones',
            'details' => array('approved primary-pack dimensions', 'syringe count', 'protected device zones', 'plunger and shield clearance', 'tray orientation', 'leaflet size', 'storage statement', 'tamper plan', 'serialization area', 'cold-chain case configuration'),
            'tests' => array('primary-pack retention', 'plunger and shield clearance', 'tray lock', 'device removal', 'leaflet fit', 'tamper-label position', 'barcode verification', 'conditioned case testing'),
            'mistakes' => array('designing direct contact around an exposed syringe without approval', 'loading the plunger or needle shield', 'using an unverified tray substitute', 'ignoring temperature-conditioned dimensions'),
            'quote' => 'approved sealed primary-pack samples, protected-zone drawing, syringe count, tray specification, leaflet dummy, storage conditions, serialization needs, line method, and validated distribution assumptions',
            'related' => array(array('/product/custom-autoinjector-pen-box/', 'autoinjector device cartons'), array('/product/custom-medical-kit-packaging-box/', 'medical kit presentation boxes')),
            'headings' => array('Secondary Paper Cartons for Prefilled Syringe Systems', 'Keeping Plungers, Shields, and Flanges Unloaded', 'Locked Cradles Around Approved Primary Packs', 'Injectable Product Identification and Traceability', 'Folding Board for Specialty Medicine Cartons', 'Controlled Loading of Syringe Primary Packs', 'Specialty Pharmacy and Cold-Chain Distribution', 'Prefilled Syringe Carton Verification', 'Material Optimization Without Changing the Sterile Barrier', 'Prefilled Syringe Packaging Development Inputs'),
            'duplicate_risk' => 3,
        ),
        array(
            'slug' => 'custom-sachet-stick-pack-carton',
            'title' => 'CUSTOM SACHET STICK PACK CARTON',
            'keyword' => 'sachet stick pack carton',
            'inside' => 'single-dose powder sticks, oral granule sachets, electrolyte packets, supplement sticks, and counted treatment courses',
            'buyers' => 'pharmaceutical brands, nutraceutical manufacturers, sachet fillers, contract packers, clinics, and retail healthcare companies',
            'problem' => 'keeping the correct sachet count orderly while protecting seals, controlling dispensing, and presenting dosage or course information clearly',
            'structure' => 'a folding stick-pack carton with count-sized internal volume, optional divider, display perforation, and secure tuck or glued closure',
            'support' => 'paperboard dividers, count-control blocks, a dispensing opening, leaflet compartment, or inner paper band where approved',
            'information' => 'product and flavor or strength, stick count, preparation instructions, dosage, warnings, storage, lot, expiry, barcode, and serialization',
            'channels' => 'pharmacy retail, clinic programs, travel healthcare, wellness subscriptions, institutional supply, and export distribution',
            'measurement' => 'the filled sachet width, seal edges, thickness range, powder settlement, tear notch, stack count, leaflet, and intended dispensing orientation',
            'overview_copy' => 'Flexible stick packs change thickness as powder settles, so carton volume should be tested with a complete counted set rather than one flat empty sachet.',
            'fit_copy' => 'Too much space produces disorder and counting uncertainty; too little space can crease seals, obscure tear notches, and make the final sticks difficult to remove.',
            'structure_copy' => 'A compact carton may use a simple divider or perforated dispensing panel to keep sticks upright, counted, and accessible throughout use.',
            'information_copy' => 'Stick-pack cartons must connect the outer-product identity with strength or flavor, count, preparation method, and information printed on each individual sachet.',
            'material_copy' => 'Printable folding boxboard works well for bright retail color, instruction diagrams, count communication, and repeatable perforation performance.',
            'operations_copy' => 'Packing trials should account for sachet settlement, seal orientation, count verification, leaflet position, perforation integrity, and final closure.',
            'channel_copy' => 'Sachet cartons can serve medicine courses, clinic sampling, travel packs, and subscription programs where count and dispensing convenience matter.',
            'qc_copy' => 'Check filled count, seal-edge clearance, stick removal, perforation tear, carton bulging, leaflet fit, barcode, and version reconciliation.',
            'sustainability_copy' => 'A right-sized carton and paper divider can reduce unused volume while the individual sachet remains responsible for direct product barrier protection.',
            'feature' => 'Controlled stick count, seal-edge clearance, dispensing option, preparation-information panels',
            'paper' => 'SBS paperboard, ivory board, or recyclable folding boxboard',
            'box_type' => 'Folding carton for sachets and stick packs',
            'shape' => 'Rectangular count pack or display carton',
            'accessories' => 'Paper divider, dispensing perforation, leaflet, inner paper band',
            'liner' => 'Paperboard divider or count-control block',
            'printing' => 'Offset printing, Pantone or CMYK variants, matte coating, perforation control, barcode and lot-expiry zones',
            'details' => array('filled sachet dimensions', 'seal-edge width', 'sticks per carton', 'settled stack thickness', 'tear-notch orientation', 'leaflet fold', 'dispensing requirement', 'strength or flavor variants', 'coding zone', 'case count'),
            'tests' => array('count fit', 'seal clearance', 'carton bulging', 'stick removal', 'dispensing perforation', 'leaflet loading', 'barcode grade', 'version reconciliation'),
            'mistakes' => array('measuring one empty sachet', 'pinching heat seals', 'hiding tear notches', 'creating a dispensing opening that weakens the shipping carton'),
            'quote' => 'filled stick packs, maximum settled thickness, count per carton, seal and tear-notch drawing, leaflet dummy, display requirement, variants, approved preparation copy, and line speed',
            'related' => array(array('/product/custom-supplement-drawer-packaging-box/', 'supplement routine-kit packaging'), array('/product/custom-pill-packaging-box/', 'pill and tablet packaging cartons')),
            'headings' => array('Count-Controlled Cartons for Sachets and Stick Packs', 'Planning for Filled Sachet Thickness and Seal Edges', 'Dividers and Dispensing Features for Stick Packs', 'Strength, Count, and Preparation Instructions', 'Paperboard for Perforated Retail Cartons', 'Sachet Settlement and Packing-Line Control', 'Course Packs, Clinics, and Subscription Channels', 'Stick Pack Carton Count and Dispensing Tests', 'Right-Sizing Around Flexible Primary Packs', 'Sachet Carton RFQ and Sampling Checklist'),
            'duplicate_risk' => 3,
        ),
        array(
            'slug' => 'custom-transdermal-patch-box',
            'title' => 'CUSTOM TRANSDERMAL PATCH BOX',
            'keyword' => 'transdermal patch box',
            'inside' => 'individually sealed transdermal patch pouches, topical delivery systems, counted treatment packs, and patient leaflets',
            'buyers' => 'transdermal medicine brands, pharmaceutical manufacturers, pouch converters, contract packers, and specialty pharmacy programs',
            'problem' => 'organizing flat sealed pouches without bending seal edges, mixing count order, or confusing strength, wear period, and disposal information',
            'structure' => 'a shallow folding carton with a controlled pouch stack, leaflet compartment, opening thumb cut, and secure tamper-evident closure plan',
            'support' => 'a paperboard stack frame, divider, withdrawal notch, leaflet separator, and optional opening sequence for counted pouches',
            'information' => 'product and strength, patch count, wear duration, application and disposal instructions, storage, warnings, lot, expiry, barcode, and serialization',
            'channels' => 'specialty pharmacy, chronic-care programs, clinics, hospital discharge, controlled home delivery, and export medicine supply',
            'measurement' => 'the sealed pouch length and width, seal flange, laminate thickness, stack count, pouch curl, leaflet fold, and withdrawal direction',
            'overview_copy' => 'Transdermal patches remain inside individual barrier pouches, while the paper carton organizes the treatment count, instructions, and user access.',
            'fit_copy' => 'The carton should control the flat stack without creasing pouch seals or requiring the user to bend remaining pouches during removal.',
            'structure_copy' => 'A shallow carton with a thumb notch and leaflet separator can keep pouches flat, present them in order, and support repeat access over a treatment period.',
            'information_copy' => 'Patch packaging requires clear separation of strength, wear duration, application schedule, handling, disposal, storage, and pouch-count information.',
            'material_copy' => 'Stable folding boxboard helps a shallow pack resist bowing while providing broad panels for diagrams, warnings, and treatment identification.',
            'operations_copy' => 'The packing line should control pouch orientation, count, lot reconciliation, leaflet version, carton closure, and tamper feature without damaging seals.',
            'channel_copy' => 'Patch cartons often support chronic-use programs where users reopen the pack, making orderly access and durable information panels important.',
            'qc_copy' => 'Verify pouch-count fit, seal-edge clearance, withdrawal access, leaflet separation, carton bow, tamper closure, barcode, and version control.',
            'sustainability_copy' => 'The secondary carton can be optimized and use a paper stack frame, but barrier performance remains the responsibility of each approved primary pouch.',
            'feature' => 'Flat pouch-stack control, seal-edge clearance, repeated-access opening, disposal-information space',
            'paper' => 'SBS paperboard, ivory board, or stiff recyclable folding boxboard',
            'box_type' => 'Shallow folding carton for sealed patch pouches',
            'shape' => 'Flat rectangular treatment-pack box',
            'accessories' => 'Pouch stack frame, leaflet separator, thumb notch, tamper label',
            'liner' => 'Paperboard stack frame or divider',
            'printing' => 'Offset printing, strength color coding, matte aqueous coating, diagrams, barcode and serialization zones',
            'details' => array('sealed pouch dimensions', 'seal-flange width', 'pouches per carton', 'pouch curl', 'withdrawal direction', 'leaflet fold', 'wear-period variants', 'disposal information', 'coding zone', 'tamper plan'),
            'tests' => array('pouch seal clearance', 'stack fit', 'pouch withdrawal', 'leaflet separation', 'carton bowing', 'tamper closure', 'barcode verification', 'count reconciliation'),
            'mistakes' => array('bending primary pouch seals', 'making remaining pouches difficult to withdraw', 'mixing wear-period artwork', 'implying the carton supplies moisture or chemical barrier protection'),
            'quote' => 'finished sealed pouches, maximum stack and curl dimensions, pouches per carton, leaflet dummy, withdrawal sequence, strength and wear variants, approved disposal copy, and coding plan',
            'related' => array(array('/product/custom-blister-pack-medicine-box/', 'blister medicine cartons'), array('/product/custom-sachet-stick-pack-carton/', 'counted sachet cartons')),
            'headings' => array('Shallow Cartons for Sealed Transdermal Patch Pouches', 'Protecting Pouch Seals and Flat Stack Order', 'Repeated-Access Structures for Patch Courses', 'Strength, Wear Period, and Disposal Information', 'Board Stiffness for Flat Treatment Packs', 'Pouch Count and Orientation on Packing Lines', 'Chronic-Care and Specialty Pharmacy Use', 'Transdermal Patch Carton Verification', 'Optimizing the Carton Without Changing the Barrier Pouch', 'Patch Packaging Quote and Version Checklist'),
            'duplicate_risk' => 3,
        ),
    );

    foreach ($products as &$product) {
        $product['images'] = array();
        $product['captions'] = array();
        $product['alts'] = array();
        foreach ($common_views as $index => $view) {
            $product['images'][] = $product['slug'] . '-' . ($index + 1) . '.webp';
            $product['captions'][] = ucwords(strtolower($product['title'])) . ' — ' . $view . '.';
            $product['alts'][] = $product['keyword'] . ' for pharmaceutical use, ' . $view;
        }
    }
    unset($product);

    return $products;
}

$marker = 'product-samples-pharmaceutical-packaging-202608';
$products = vpn_pharma_202608_products();
$category_names = array(
    'pharmaceutical-packaging-boxes' => 'Pharmaceutical Packaging Boxes',
    'folding-carton-boxes' => 'Folding Carton Boxes',
    'custom-paper-boxes' => 'Custom Paper Boxes',
);
$term_ids = array();

foreach ($category_names as $slug => $name) {
    $term = get_term_by('slug', $slug, 'product_cat');
    if (!$term || is_wp_error($term)) {
        $created = wp_insert_term($name, 'product_cat', array('slug' => $slug));
        if (is_wp_error($created)) {
            fwrite(STDERR, 'Unable to create category ' . $slug . ': ' . $created->get_error_message() . PHP_EOL);
            exit(1);
        }
        $term = get_term((int) $created['term_id'], 'product_cat');
    }
    $term_ids[] = (int) $term->term_id;
}

$audit = array('# Pharmaceutical Packaging Products 202608 Audit', '', 'Local batch marker: `' . $marker . '`', '');
$export = array('# Pharmaceutical Packaging Products 202608 — Text Only', '', 'Generated from local WooCommerce products for duplicate-risk review.', '');

foreach ($products as $product) {
    $image_ids = array();
    foreach ($product['images'] as $index => $filename) {
        $image_ids[] = vpn_pharma_202608_attachment_id(
            $filename,
            $product['alts'][$index],
            $product['captions'][$index],
            $product['captions'][$index]
        );
    }

    if (count(array_filter($image_ids)) !== 4) {
        fwrite(STDERR, 'Missing image attachment for ' . $product['title'] . PHP_EOL);
        continue;
    }

    $short = vpn_pharma_202608_short($product);
    $content = vpn_pharma_202608_content($product, $image_ids);
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
        $postarr['ID'] = (int) $existing->ID;
        $product_id = wp_update_post($postarr, true);
    } else {
        $product_id = wp_insert_post($postarr, true);
    }

    if (is_wp_error($product_id) || !$product_id) {
        fwrite(STDERR, 'Failed to import ' . $product['title'] . PHP_EOL);
        continue;
    }

    foreach ($image_ids as $image_id) {
        wp_update_post(array('ID' => $image_id, 'post_parent' => (int) $product_id));
    }

    wp_set_object_terms($product_id, $term_ids, 'product_cat', false);
    wp_set_object_terms($product_id, 'simple', 'product_type', false);
    wp_set_object_terms($product_id, array(
        $product['keyword'],
        'pharmaceutical packaging',
        'medicine carton',
        'healthcare packaging box',
        'custom folding carton',
    ), 'product_tag', false);
    set_post_thumbnail($product_id, $image_ids[0]);
    update_post_meta($product_id, '_product_image_gallery', implode(',', array_slice($image_ids, 1)));
    update_post_meta($product_id, '_sku', 'sample-pharma-202608-' . $product['slug']);
    update_post_meta($product_id, '_regular_price', '');
    update_post_meta($product_id, '_price', '');
    update_post_meta($product_id, '_stock_status', 'instock');
    update_post_meta($product_id, '_manage_stock', 'no');
    update_post_meta($product_id, '_visibility', 'visible');
    update_post_meta($product_id, '_custom_box_product_specs', vpn_pharma_202608_specs($product));
    update_post_meta($product_id, '_vpn_sample_import', $marker);
    update_post_meta($product_id, '_vpn_product_specific_details', $product['details']);
    update_post_meta($product_id, '_vpn_duplicate_risk_score', $product['duplicate_risk']);
    update_post_meta($product_id, 'rank_math_focus_keyword', $product['keyword']);
    update_post_meta($product_id, 'rank_math_title', $product['title'] . ' | VPN PAPER BOX MANUFACTURER');
    $meta_description = 'Custom ' . $product['keyword'] . ' with product-fit support, approved healthcare printing, four image views, and MOQ from 1000 boxes.';
    update_post_meta($product_id, 'rank_math_description', substr($meta_description, 0, 154));

    $saved = (string) get_post_field('post_content', $product_id);
    $plain = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($saved)));
    $word_count = str_word_count($plain);
    $short_words = str_word_count(wp_strip_all_tags($short));
    $figures = substr_count($saved, '<figure class="product-inline-figure');

    $audit[] = '## ' . $product['title'];
    $audit[] = '- ID: ' . $product_id;
    $audit[] = '- URL: ' . get_permalink($product_id);
    $audit[] = '- Status: ' . get_post_status($product_id);
    $audit[] = '- Focus keyword: ' . $product['keyword'];
    $audit[] = '- Long description words: ' . $word_count;
    $audit[] = '- Short description words: ' . $short_words;
    $audit[] = '- Content H1 count: ' . preg_match_all('/<h1\b/i', $saved);
    $audit[] = '- Product images: ' . count(array_unique($image_ids));
    $audit[] = '- Inline figures: ' . $figures;
    $audit[] = '- Specification rows: ' . count(vpn_pharma_202608_specs($product));
    $audit[] = '- Duplicate risk score: ' . $product['duplicate_risk'] . '/10';
    $audit[] = '- Product-specific details: ' . implode('; ', $product['details']);
    $audit[] = '- Source images: ' . implode(', ', $product['images']);
    $audit[] = '';

    $export[] = '## ' . $product['title'];
    $export[] = '';
    $export[] = '- URL: ' . get_permalink($product_id);
    $export[] = '- Word count: ' . $word_count;
    $export[] = '- Duplicate risk score: ' . $product['duplicate_risk'] . '/10';
    $export[] = '- Product-specific details: ' . implode('; ', $product['details']);
    $export[] = '';
    $export[] = '### Short Description';
    $export[] = '';
    $export[] = wp_strip_all_tags($short);
    $export[] = '';
    $export[] = '### Long Description';
    $export[] = '';
    $export[] = $plain;
    $export[] = '';

    echo 'Imported: ' . $product['title'] . ' (#' . $product_id . ') words=' . $word_count . ' short=' . $short_words . ' images=4 figures=' . $figures . PHP_EOL;
}

file_put_contents(dirname(__DIR__) . '/product-samples-pharmaceutical-packaging-202608-audit.md', implode(PHP_EOL, $audit));
file_put_contents(dirname(__DIR__) . '/product-samples-pharmaceutical-packaging-202608-descriptions-text-only.md', implode(PHP_EOL, $export));

echo 'Pharmaceutical packaging August 2026 import complete.' . PHP_EOL;
