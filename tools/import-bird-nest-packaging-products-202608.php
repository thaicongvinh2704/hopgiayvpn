<?php
/**
 * Import the August 2026 bird nest packaging batch from 40 Media Library images.
 *
 * Run:
 *   php tools/import-bird-nest-packaging-products-202608.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

function vpn_bird_202608_link($path, $anchor)
{
    return '<a href="' . esc_url(home_url($path)) . '">' . esc_html($anchor) . '</a>';
}

function vpn_bird_202608_base($filename)
{
    return preg_replace('/\.[^.]+$/', '', basename($filename));
}

function vpn_bird_202608_attachment_id($filename, $alt, $title, $caption)
{
    $base = vpn_bird_202608_base($filename);
    $ids = get_posts(array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => array(array('key' => '_wp_attached_file', 'value' => $base, 'compare' => 'LIKE')),
    ));

    $attachment_id = 0;
    foreach ($ids as $id) {
        $attached = (string) get_post_meta((int) $id, '_wp_attached_file', true);
        if (0 === strcasecmp(vpn_bird_202608_base($attached), $base)) {
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
    wp_update_post(array('ID' => $attachment_id, 'post_title' => $title, 'post_excerpt' => $caption));
    return (int) $attachment_id;
}

function vpn_bird_202608_figure($attachment_id, $caption, $narrow = false)
{
    $image = wp_get_attachment_image($attachment_id, 'large', false, array('loading' => 'lazy'));
    if (!$image) {
        return '';
    }
    return '<figure class="product-inline-figure product-inline-figure-small' . ($narrow ? ' is-narrow' : '') . '">' .
        $image . '<figcaption>' . esc_html($caption) . '</figcaption></figure>';
}

function vpn_bird_202608_section($heading, $paragraphs)
{
    $html = '<h2>' . esc_html($heading) . '</h2>';
    foreach ($paragraphs as $paragraph) {
        $html .= '<p>' . $paragraph . '</p>';
    }
    return $html;
}

function vpn_bird_202608_list($items)
{
    $items = array_values(array_filter($items));
    $last = array_pop($items);
    return $items ? implode(', ', $items) . ', and ' . $last : (string) $last;
}

function vpn_bird_202608_specs($product)
{
    return array(
        array('label' => 'Feature', 'value' => $product['feature']),
        array('label' => 'Industrial Use', 'value' => 'Bird Nest, Health Food, Beverage, Premium Gift, and Retail Packaging'),
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
        array('label' => 'Printing Handling', 'value' => $product['finish']),
        array('label' => 'Color', 'value' => 'Custom Pantone, CMYK, metallic, and brand color system'),
        array('label' => 'Size', 'value' => 'Customized size'),
        array('label' => 'Thickness', 'value' => 'Customized thickness'),
        array('label' => 'Single Piece Price', 'value' => 'Price based on size, board, insert, window, printing, finishing, accessories, and quantity'),
        array('label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes'),
        array('label' => 'Product Name', 'value' => $product['title']),
        array('label' => 'Design', 'value' => "Customer's Specific Requirement"),
    );
}

function vpn_bird_202608_short($product)
{
    return $product['title'] . ' is custom paper packaging for ' . $product['inside'] . '. It is developed for ' . $product['buyers'] . ' that need ' . $product['problem'] . '. The pack can be configured with ' . $product['structure'] . ', ' . $product['insert'] . ', and brand panels for ' . $product['information'] . '. Buyers should provide packed-product dimensions, filled weight, component count, approved artwork, insert preference, sales channel, and shipping requirements before sampling. Materials, colors, windows, handles, ribbons, trays, and premium finishes can be customized for retail, festival, corporate, and export programs. MOQ starts at 1000 boxes.';
}

function vpn_bird_202608_content($product, $image_ids)
{
    $category = vpn_bird_202608_link('/product-category/bird-nest-packaging-boxes/', 'bird nest packaging boxes');
    $guide = vpn_bird_202608_link('/how-to-choose-paper-material-for-product-packaging/', 'paper material selection guide');
    $contact = vpn_bird_202608_link('/contact/#quote', 'request a bird nest packaging quote');
    $related_one = vpn_bird_202608_link($product['related'][0][0], $product['related'][0][1]);
    $related_two = vpn_bird_202608_link($product['related'][1][0], $product['related'][1][1]);
    $details = vpn_bird_202608_list($product['details']);
    $tests = vpn_bird_202608_list($product['tests']);
    $mistakes = vpn_bird_202608_list($product['mistakes']);

    $html = vpn_bird_202608_section($product['headings'][0], array(
        $product['title'] . ' is designed for ' . $product['inside'] . '. It serves ' . $product['buyers'] . ' that need protection, orderly presentation, reliable packing, and a premium health-food identity. Bird nest products are commonly bought as gifts, but the package must still be engineered around the actual jars, bottles, sachets, dried nests, bowls, spoons, or inner containers.',
        $product['overview_copy'] . ' This product belongs to our ' . $category . ' range. Its structure should be developed from the final packed set rather than copied from a generic gift box. Changes in jar diameter, bottle shoulder, tray height, spoon length, pouch count, or product weight can alter the usable internal space and opening experience.',
        'A useful packaging brief identifies ' . $details . '. These inputs allow the factory to estimate board consumption, insert tooling, manual assembly, printing controls, master-carton quantity, sampling work, and finished-unit cost. They also reduce late changes after artwork and structure have already been approved.',
    ));
    $html .= vpn_bird_202608_figure($image_ids[0], $product['captions'][0], true);

    $html .= vpn_bird_202608_section($product['headings'][1], array(
        $product['fit_copy'] . ' The main packaging challenge is ' . $product['problem'] . '. Empty mock containers often feel light and stable, while filled glass jars or bottles create much higher pressure on the tray, base, handles, and outer case during distribution.',
        'Measure the product in its final sale condition, including ' . $product['measure'] . '. Use maximum dimensions from representative samples and confirm the intended label direction. For food and beverage gift sets, the package should hold products securely without hiding the brand face or forcing customers to pull on lids, caps, seals, or fragile decorative components.',
        'A packed trial should show how an operator loads every component and how the customer removes it. Finger access, jar spacing, bottle-neck clearance, pouch seal protection, window position, and accessory order should be intentional. Controlled fit makes both factory packing and unboxing more repeatable.',
    ));

    $html .= vpn_bird_202608_section($product['headings'][2], array(
        $product['structure_copy'] . ' Recommended construction is ' . $product['structure'] . '. The final format depends on filled weight, product count, desired reveal, display direction, hand-carry requirements, packing speed, and whether the package is primarily for retail sale, festival gifting, corporate presentation, or ecommerce.',
        'Internal organization can use ' . $product['insert'] . '. Contact points should support stable container areas, stop products knocking together, preserve printed labels, and provide enough clearance for comfortable removal. The tray should also be practical for the packing team to assemble and load at the target quantity.',
        'Prototype evaluation should cover opening and closing, tray lift, lid fit, magnetic or tuck closure, window strength, handle pull where used, repeated product removal, and master-carton packing. A visually impressive sample still needs consistent assembly and dimensional control for mass production.',
    ));
    $html .= vpn_bird_202608_figure($image_ids[1], $product['captions'][1]);

    $html .= vpn_bird_202608_section($product['headings'][3], array(
        $product['display_copy'] . ' The insert and opening sequence should explain the value of the set before the customer reads every detail. Jar rows, bottle pairs, double layers, a featured bowl, a cylindrical reveal, or a product window each creates a different visual rhythm and customer expectation.',
        'When several items share one box, give each component a defined cavity or compartment. Accessories such as spoons, preparation cards, sachets, rock sugar, or certificates should not float above heavier containers. Dividers and end stops reduce contact during shipping and keep the presentation organized after the lid is opened.',
        'For gift programs, consider the sequence from outer case to retail box, gift bag, lid, information card, and product tray. The reveal should feel deliberate without creating unnecessary layers that slow packing, increase shipping volume, or make disposal confusing.',
    ));

    $html .= vpn_bird_202608_section($product['headings'][4], array(
        $product['branding_copy'] . ' Artwork may need room for ' . $product['information'] . '. The front panel should communicate product type, grade or collection, pack count, and brand level quickly. Side and back panels can carry practical food information, preparation guidance, barcode, QR code, batch area, importer details, and storage directions.',
        'Bird nest brands often use gold, deep red, navy, green, ivory, botanical illustration, swallow motifs, or restrained health-product colors. These elements should create a consistent hierarchy instead of competing with small information panels. Window products also need enough solid print area for identity and mandatory copy.',
        'Prepare artwork on the approved production dieline with bleed, safe zones, folds, glue areas, window cut lines, barcode position, and finish masks. Multiple flavors, grades, jar counts, or festival versions should use a controlled artwork list so similar cartons are not mixed during printing and packing.',
    ));
    $html .= vpn_bird_202608_figure($image_ids[2], $product['captions'][2], true);

    $html .= vpn_bird_202608_section($product['headings'][5], array(
        $product['material_copy'] . ' Suitable substrates include ' . $product['materials'] . '. Board caliper and construction should be selected from product weight, panel span, insert pressure, window size, surface finishing, and distribution conditions rather than appearance alone.',
        'Printing and finishing can include ' . $product['finish'] . '. Foil and embossing work best when logo size, fold distance, texture, and wrap alignment are considered early. Large decorative areas should not reduce barcode contrast, small-text readability, glue performance, or tray accuracy.',
        'Procurement teams can use the ' . $guide . ' to compare stiffness, surface smoothness, recycled content, wrapping behavior, and print results. Sustainability statements should match the actual board, coating, insert, window film, magnet, ribbon, and other components specified for the order.',
    ));

    $html .= vpn_bird_202608_section($product['headings'][6], array(
        $product['operations_copy'] . ' The packing plan should define carton or rigid-box preparation, tray insertion, product orientation, accessory placement, card or leaflet loading, closure, coding, final inspection, and master-case arrangement. Manual and semi-automatic projects have different limits on insert complexity and packing time.',
        'A work instruction should show the acceptable position of every item. Heavy glass jars should not rely on decorative paper alone, bottle caps should remain clear of top pressure, sachet seals should not be pinched, and dried nests should not contact a display window unless their primary tray is designed for it.',
        'For multiple product grades or campaign versions, the buyer should define line clearance and reconciliation. Color is helpful for recognition but should not be the only control. Product code, count, flavor, grade, language, batch format, and carton version should match approved production documents.',
    ));
    $html .= vpn_bird_202608_figure($image_ids[3], $product['captions'][3]);

    $html .= vpn_bird_202608_section($product['headings'][7], array(
        $product['channel_copy'] . ' Distribution can include ' . $product['channels'] . '. Each route changes the balance between shelf visibility, gift presentation, carry strength, case compression, barcode access, surface protection, and shipping volume.',
        'Retail packs need consistent front-facing alignment and fast product recognition. Corporate and festival gift sets need a ceremonial reveal and clean handover. Ecommerce programs may require a separate corrugated shipper because a premium presentation box is not automatically designed to withstand parcel handling without added protection.',
        'Buyers can compare ' . $related_one . ' and ' . $related_two . ' when developing a coordinated bird nest range. Related packs may share colors, logos, and finishing rules, but tray cavities and structural protection should remain specific to the products inside.',
    ));

    $html .= vpn_bird_202608_section($product['headings'][8], array(
        $product['qc_copy'] . ' A practical approval plan covers ' . $tests . '. Test methods, packed sample quantities, acceptance criteria, conditioning, and responsible approvers should be agreed before mass production when they affect the project.',
        'Visual inspection can cover print color, foil registration, wrap alignment, window cleanliness, edge finishing, glue marks, logo position, and label direction. Structural checks can cover dimensions, tray fit, closure action, handle strength, compartment stability, product removal, and case compression.',
        'Keep approved references for artwork, materials, color, structure, insert, and packed configuration. Recheck the package when a jar, bottle, lid, pouch, bowl, spoon, label, or product count changes, even when the commercial product name remains the same.',
    ));

    $html .= vpn_bird_202608_section($product['headings'][9], array(
        $product['sustainability_copy'] . ' Common mistakes include ' . $mistakes . '. These issues are easier to correct during dieline, tray, and packed-sample review than after printed boxes reach the filling or gift-assembly site.',
        'For quotation, provide ' . $product['quote'] . '. Also state quantity, annual forecast, delivery schedule, target market, proofing method, export-case needs, and whether the order includes several artworks or seasonal versions.',
        'Send the completed brief through ' . $contact . '. VPN can develop the structure, insert, sample, printing plan, and production quotation. The buyer remains responsible for approving food labeling, ingredients, nutrition data, shelf life, origin statements, health claims, barcodes, and market-specific compliance.',
    ));

    return $html;
}

function vpn_bird_202608_products()
{
    $products = array(
        array(
            'slug' => 'custom-2-bottle-bird-nest-beverage-box',
            'title' => 'CUSTOM 2 BOTTLE BIRD NEST BEVERAGE BOX',
            'keyword' => '2 bottle bird nest beverage box',
            'inside' => 'two bottled bird nest beverages, ready-to-drink health tonics, and paired premium drink sets',
            'buyers' => 'bird nest beverage brands, drink fillers, pharmacy distributors, hotel programs, and premium food retailers',
            'problem' => 'holding two dense bottles upright without bottle-to-bottle contact, cap pressure, label scuffing, or base failure',
            'structure' => 'a reinforced folding carton with a two-bottle divider, locked base, top clearance, and controlled opening',
            'insert' => 'a corrugated or paperboard center partition, bottle-base locators, neck clearance, and a preparation card pocket',
            'information' => 'beverage name, bottle count, volume, ingredients, nutrition, storage, serving guidance, barcode, batch, and expiry data',
            'channels' => 'pharmacy retail, convenience and health stores, hotel welcome gifts, ecommerce, and export beverage distribution',
            'measure' => 'filled bottle weight, base diameter, shoulder, cap, tamper ring, label seam, divider allowance, and top clearance',
            'overview_copy' => 'A paired beverage carton should feel compact and giftable while carrying the combined weight of two filled glass or plastic bottles safely.',
            'fit_copy' => 'Bottle weight concentrates on the base, and uncontrolled side movement can make containers knock together or damage labels during case transport.',
            'structure_copy' => 'The most efficient format is a folding beverage carton with a strong bottom and center divider that creates one repeatable loading position for each bottle.',
            'display_copy' => 'An open carton should reveal both bottle labels evenly, while finger access allows removal without pulling on caps or disturbing the second bottle.',
            'branding_copy' => 'Front artwork can position the pair as a daily wellness set, hotel gift, tasting pack, or premium retail beverage bundle.',
            'material_copy' => 'Higher-caliper folding boxboard or micro-corrugated support can provide the stiffness needed for a compact two-bottle pack.',
            'operations_copy' => 'Operators should erect the locked base, place the divider fully, orient both labels, confirm cap clearance, and close the carton without bottle pressure.',
            'channel_copy' => 'The paired format works across health retail and gifting but needs a separate parcel shipper for direct-to-consumer delivery.',
            'qc_copy' => 'Packed testing should focus on bottom strength, divider retention, bottle contact, cap clearance, label scuffing, and case compression.',
            'sustainability_copy' => 'Accurate bottle sizing and a recyclable paper divider can reduce void fill and unnecessary outer volume.',
            'materials' => 'SBS paperboard, ivory board, kraft board, paperboard divider, or micro-corrugated reinforcement',
            'finish' => 'Offset printing, Pantone colors, matte aqueous coating, foil stamping, embossing, and barcode zones',
            'feature' => 'Two-bottle divider, reinforced base, upright beverage display, cap clearance',
            'paper' => 'SBS / Ivory Board / Kraft Board / Micro-Corrugated Support',
            'box_type' => 'Two Bottle Bird Nest Beverage Folding Carton',
            'shape' => 'Tall rectangular two-bottle carton',
            'accessories' => 'Center divider, base locator, product card, tamper label',
            'liner' => 'Paperboard or corrugated bottle partition',
            'details' => array('filled bottle dimensions and weight', 'bottle count', 'cap clearance', 'label direction', 'divider style', 'base closure', 'product card', 'coding zone', 'case count', 'sales channel'),
            'tests' => array('bottom load', 'divider retention', 'bottle contact', 'cap clearance', 'label scuffing', 'closure strength', 'barcode scan', 'case compression'),
            'mistakes' => array('sizing from empty bottles', 'using no center partition', 'loading the top flap onto bottle caps', 'selecting a weak bottom closure'),
            'quote' => 'filled bottle samples, maximum dimensions, total packed weight, label direction, divider preference, approved copy, base style, and master-case plan',
            'related' => array(array('/product/custom-6-jar-bird-nest-magnetic-gift-box/', 'six-jar magnetic bird nest gift boxes'), array('/product/custom-green-bird-nest-gift-box-with-handle/', 'bird nest gift boxes with carry handles')),
            'headings' => array('Two-Bottle Bird Nest Beverage Cartons', 'Managing Bottle Weight and Separation', 'Reinforced Cartons With Center Dividers', 'Presenting a Balanced Beverage Pair', 'Beverage Information and Brand Panels', 'Board Strength and Finishing Options', 'Two-Bottle Packing-Line Sequence', 'Retail, Hotel, and Ecommerce Channels', 'Packed Beverage Carton Quality Checks', 'Quotation Details for Two-Bottle Boxes'),
            'duplicate_risk' => 3,
        ),
        array(
            'slug' => 'custom-6-jar-bird-nest-magnetic-gift-box',
            'title' => 'CUSTOM 6 JAR BIRD NEST MAGNETIC GIFT BOX',
            'keyword' => '6 jar bird nest magnetic gift box',
            'inside' => 'six bottled bird nest jars arranged as a premium health-food gift set',
            'buyers' => 'bird nest brands, corporate gift companies, health-food distributors, hotels, and festival retail programs',
            'problem' => 'supporting six heavy jars while maintaining magnetic-lid alignment, equal label presentation, and easy jar removal',
            'structure' => 'a hinged rigid magnetic box with a six-cavity tray, reinforced spine, lid stop, and optional ribbon or card pocket',
            'insert' => 'a paper-wrapped EVA, molded pulp, paperboard, or foam tray with six separated jar cavities and finger notches',
            'information' => 'gift collection, jar count, flavor or grade, ingredients, nutrition, storage, serving directions, origin, barcode, batch, and expiry',
            'channels' => 'Lunar New Year gifting, corporate appreciation, premium retail, hotel gifts, distributor programs, and export health-food sales',
            'measure' => 'filled jar weight, base diameter, lid width, label direction, six-cavity spacing, tray depth, lid clearance, and total set weight',
            'overview_copy' => 'A six-jar magnetic set combines substantial packed weight with a ceremonial hinged opening and should feel stable before and after the lid is raised.',
            'fit_copy' => 'Equal cavity spacing keeps jars from colliding and creates a disciplined label row, while finger notches prevent customers pulling on jar lids.',
            'structure_copy' => 'Rigid greyboard, a reinforced hinge, accurately positioned magnets, and a fitted tray work together to control the heavy multi-jar set.',
            'display_copy' => 'The six jars should form a symmetrical reveal with consistent cap height, front-facing labels, and enough negative space for premium presentation.',
            'branding_copy' => 'The large lid and inner panel can carry a collection story, origin message, festival greeting, or corporate gift personalization.',
            'material_copy' => 'Wrapped rigid board provides a premium hand feel, while the tray material should balance jar retention, recyclability, and production cost.',
            'operations_copy' => 'Assembly should control tray seating, magnet polarity, hinge movement, label direction, cavity count, and final lid closure under packed weight.',
            'channel_copy' => 'This format is strongest for high-value gifting and premium counters where opening experience matters more than minimum shipping volume.',
            'qc_copy' => 'Test magnet engagement, lid alignment, hinge flex, tray compression, jar spacing, removal access, and export-case protection.',
            'sustainability_copy' => 'A paper-based tray and optimized board thickness may reduce mixed materials when jar protection is confirmed through packed testing.',
            'materials' => 'rigid greyboard, coated art paper, specialty wrapping paper, paperboard tray, molded pulp, or EVA insert',
            'finish' => 'Offset printing, Pantone color, gold foil, embossing, debossing, spot UV, matte lamination, and inner printing',
            'feature' => 'Six-jar fitted tray, magnetic hinged lid, premium label alignment, reinforced rigid structure',
            'paper' => 'Rigid Greyboard / Art Paper / Specialty Paper / Paper or EVA Tray',
            'box_type' => 'Six Jar Magnetic Rigid Bird Nest Gift Box',
            'shape' => 'Wide rectangular hinged gift box',
            'accessories' => 'Magnets, six-cavity tray, ribbon tab, card pocket',
            'liner' => 'Paper-wrapped EVA, paperboard, molded pulp, or foam tray',
            'details' => array('filled jar dimensions and weight', 'six-jar layout', 'label direction', 'tray depth', 'finger notches', 'lid angle', 'magnet strength', 'inner artwork', 'gift card', 'export case'),
            'tests' => array('magnet closure', 'hinge cycling', 'tray retention', 'jar-to-jar clearance', 'removal force', 'lid alignment', 'surface scuffing', 'case compression'),
            'mistakes' => array('underestimating total jar weight', 'placing cavities too close', 'using weak hinge paper', 'providing no finger access'),
            'quote' => 'six filled jars, maximum dimensions and total weight, desired lid angle, tray preference, magnet requirement, artwork, finishing masks, and export case target',
            'related' => array(array('/product/custom-8-jar-bird-nest-lid-and-base-gift-box/', 'eight-jar lid-and-base gift boxes'), array('/product/custom-green-bird-nest-compartment-gift-box/', 'bird nest boxes with organized jar compartments')),
            'headings' => array('Six-Jar Magnetic Bird Nest Gift Boxes', 'Controlling Heavy Jar Rows and Label Direction', 'Hinged Rigid Construction and Magnetic Closure', 'Creating a Symmetrical Six-Jar Reveal', 'Gift Branding Across Lid and Inner Panels', 'Rigid Board, Tray, and Foil Selection', 'Assembling Magnetic Multi-Jar Sets', 'Festival and Corporate Gift Programs', 'Magnetic Box and Tray Quality Control', 'Six-Jar Gift Box RFQ Checklist'),
            'duplicate_risk' => 3,
        ),
        array(
            'slug' => 'custom-8-jar-bird-nest-lid-and-base-gift-box',
            'title' => 'CUSTOM 8 JAR BIRD NEST LID AND BASE GIFT BOX',
            'keyword' => '8 jar bird nest lid and base gift box',
            'inside' => 'eight bird nest jars arranged in a premium two-piece presentation set',
            'buyers' => 'health-food brands, premium grocers, festival gift suppliers, corporate procurement teams, and distributors',
            'problem' => 'balancing eight filled jars across a wide tray while keeping the removable lid easy to lift and the base resistant to bowing',
            'structure' => 'a rigid lid-and-base box with a deep lower tray, eight separated cavities, lid clearance, and optional lifting ribbon',
            'insert' => 'a two-row paperboard, molded pulp, or paper-wrapped tray with eight jar wells, label orientation, and finger access',
            'information' => 'collection name, eight-jar count, flavors or grades, ingredients, nutrition, storage, origin, serving guidance, barcode, batch, and expiry',
            'channels' => 'festival gifting, premium supermarkets, corporate gifts, family health sets, distributor showrooms, and export retail',
            'measure' => 'filled jar weight, jar and lid diameter, two-row spacing, tray wall height, lid overlap, base panel span, and total set weight',
            'overview_copy' => 'An eight-jar two-piece box offers a broad premium display and a familiar ceremonial lift-off lid, but its wide base must resist tray and product load.',
            'fit_copy' => 'Two balanced rows distribute weight more evenly when cavities, divider walls, and bottom support are aligned with the rigid base.',
            'structure_copy' => 'The lid should release smoothly without vacuum drag or excessive looseness, while the lower box keeps its shape under repeated lifting.',
            'display_copy' => 'Eight aligned jars create a collection effect suitable for assorted flavors, grades, or a longer wellness program.',
            'branding_copy' => 'The large lid can carry restrained luxury artwork, while the inner card or tray edge explains assortment and serving order.',
            'material_copy' => 'Thick greyboard and a stable tray prevent wide panels from bowing; wrapping paper and foil define the gift tier.',
            'operations_copy' => 'Packing should confirm all eight labels, flavors, cavity positions, card version, tray seating, and lid orientation before final case loading.',
            'channel_copy' => 'The wide format suits premium display and hand-delivered gifts, but efficient case nesting should be reviewed for export orders.',
            'qc_copy' => 'Check lid lift force, base squareness, tray compression, row spacing, jar removal, wrap alignment, and stacked-case performance.',
            'sustainability_copy' => 'Right-sized cavity walls and a paper tray can reduce foam while retaining the disciplined eight-jar layout.',
            'materials' => 'rigid greyboard, coated or specialty wrapping paper, paperboard tray, molded pulp, or paper-wrapped EVA',
            'finish' => 'Offset printing, Pantone color, foil stamping, embossing, matte lamination, spot UV, and inner-lid printing',
            'feature' => 'Eight-jar two-row display, lift-off rigid lid, wide reinforced base, fitted insert',
            'paper' => 'Rigid Greyboard / Art Paper / Specialty Paper / Fitted Tray',
            'box_type' => 'Eight Jar Lid and Base Bird Nest Gift Box',
            'shape' => 'Wide rectangular two-piece rigid box',
            'accessories' => 'Eight-cavity tray, lifting ribbon, information card, gift sleeve',
            'liner' => 'Paperboard, molded pulp, or paper-wrapped fitted tray',
            'details' => array('eight filled jars', 'two-row layout', 'jar lid diameter', 'tray depth', 'finger access', 'lid overlap', 'base reinforcement', 'assortment order', 'gift card', 'case nesting'),
            'tests' => array('lid lift', 'base bowing', 'tray compression', 'jar spacing', 'removal access', 'wrap alignment', 'surface rub', 'case stacking'),
            'mistakes' => array('using a shallow lid overlap', 'leaving a wide base unsupported', 'mixing assortment order', 'making jar cavities difficult to grip'),
            'quote' => 'eight filled jars, assortment plan, maximum dimensions, total set weight, tray material, lid overlap preference, artwork, finishes, and case quantity',
            'related' => array(array('/product/custom-6-jar-bird-nest-magnetic-gift-box/', 'six-jar magnetic gift sets'), array('/product/custom-12-jar-double-layer-bird-nest-gift-box/', 'double-layer twelve-jar gift boxes')),
            'headings' => array('Eight-Jar Lid-and-Base Bird Nest Boxes', 'Balancing Two Rows of Filled Jars', 'Lift-Off Lid Fit and Wide Base Support', 'Presenting an Eight-Jar Assortment', 'Large-Lid Branding and Collection Information', 'Rigid Board and Fitted Tray Materials', 'Packing Eight-Jar Product Variants', 'Premium Retail and Gift Distribution', 'Lid, Base, and Tray Quality Checks', 'Eight-Jar Box Quotation Requirements'),
            'duplicate_risk' => 3,
        ),
        array(
            'slug' => 'custom-12-jar-double-layer-bird-nest-gift-box',
            'title' => 'CUSTOM 12 JAR DOUBLE LAYER BIRD NEST GIFT BOX',
            'keyword' => '12 jar double layer bird nest gift box',
            'inside' => 'twelve bird nest jars organized across two stacked presentation layers',
            'buyers' => 'premium bird nest brands, VIP gift suppliers, distributors, corporate buyers, and festival campaign managers',
            'problem' => 'supporting twelve heavy jars without crushing the lower layer, confusing assortment order, or making the second tray difficult to access',
            'structure' => 'a reinforced rigid box with upper and lower six-jar trays, a removable lift system, deep base, and controlled lid closure',
            'insert' => 'two load-bearing fitted trays with six separated cavities each, lifting tabs, layer spacers, and jar-label orientation',
            'information' => 'twelve-jar assortment, flavor or grade map, ingredients, nutrition, storage, serving plan, origin, barcode, batch, and expiry',
            'channels' => 'VIP and corporate gifting, Lunar New Year, distributor programs, premium retail, family wellness sets, and export gifts',
            'measure' => 'filled jar weight, jar and lid diameter, six-jar tray dimensions, stacked-layer height, lift-tab access, spacer strength, and total load',
            'overview_copy' => 'A twelve-jar double-layer set delivers abundance and premium value, but its engineering challenge is vertical load transfer and usable access to the lower tray.',
            'fit_copy' => 'The upper tray should carry its own jar load through stable perimeter or spacer supports instead of resting pressure on lower jar lids.',
            'structure_copy' => 'A deep reinforced base, controlled tray ledges, and strong lift tabs let customers remove the upper assortment without tilting jars.',
            'display_copy' => 'The opening sequence can reveal six jars first and a coordinated second layer, creating a progressive presentation for a large gift program.',
            'branding_copy' => 'An assortment map or inner-lid card helps explain flavors, grades, sequence, and product differences across twelve units.',
            'material_copy' => 'High-density rigid board, load-bearing tray material, and reinforced corners are needed for the combined weight and deep structure.',
            'operations_copy' => 'Packing requires controlled lower-layer count, spacer placement, upper-tray seating, lift-tab position, assortment order, and final lid clearance.',
            'channel_copy' => 'The large format is suited to hand-delivered VIP gifts; freight cube, carrying method, and master-case strength require early review.',
            'qc_copy' => 'Test vertical tray load, spacer position, lower-jar clearance, lift-tab strength, base bowing, lid closure, and packed-case compression.',
            'sustainability_copy' => 'Efficient stacked trays can reduce the footprint compared with a single twelve-jar row while material choices still need load verification.',
            'materials' => 'thick rigid greyboard, coated art paper, specialty wrap, load-bearing paperboard tray, molded pulp, or reinforced EVA',
            'finish' => 'Offset printing, Pantone color, gold foil, embossing, debossing, matte lamination, and premium inner printing',
            'feature' => 'Two six-jar layers, load-bearing tray system, lift tabs, reinforced deep rigid box',
            'paper' => 'High-Density Rigid Greyboard / Specialty Paper / Reinforced Tray',
            'box_type' => 'Twelve Jar Double Layer Bird Nest Gift Box',
            'shape' => 'Deep rectangular double-layer rigid box',
            'accessories' => 'Two fitted trays, lifting tabs, layer spacer, assortment card',
            'liner' => 'Load-bearing paperboard, molded pulp, or reinforced fitted trays',
            'details' => array('twelve filled jars', 'two six-jar layers', 'total packed weight', 'tray bearing points', 'jar-lid clearance', 'lift tabs', 'assortment map', 'lid depth', 'carry method', 'export case'),
            'tests' => array('upper tray load', 'lower jar clearance', 'lift-tab pull', 'base bowing', 'lid closure', 'corner strength', 'jar movement', 'case compression'),
            'mistakes' => array('resting the upper tray on jar lids', 'using weak lift tabs', 'hiding the lower assortment', 'ignoring total set weight and carrying method'),
            'quote' => 'twelve filled jars, layer arrangement, maximum dimensions, total packed weight, tray support concept, lift preference, artwork, finishing, and export-case requirements',
            'related' => array(array('/product/custom-8-jar-bird-nest-lid-and-base-gift-box/', 'eight-jar lid-and-base sets'), array('/product/custom-green-bird-nest-cabinet-gift-box/', 'cabinet-style bird nest gift packaging')),
            'headings' => array('Twelve-Jar Double-Layer Bird Nest Gift Boxes', 'Protecting the Lower Jar Layer From Load', 'Stacked Trays, Spacers, and Lift Access', 'Creating a Progressive Two-Level Reveal', 'Assortment Maps for Twelve-Jar Collections', 'Load-Bearing Rigid Materials and Finishes', 'Double-Layer Packing and Count Control', 'VIP Gift Distribution and Freight Planning', 'Vertical Load and Case Quality Testing', 'Double-Layer Gift Box RFQ Inputs'),
            'duplicate_risk' => 3,
        ),
        array(
            'slug' => 'custom-bird-nest-bowl-and-spoon-gift-box',
            'title' => 'CUSTOM BIRD NEST BOWL AND SPOON GIFT BOX',
            'keyword' => 'bird nest bowl and spoon gift box',
            'inside' => 'bird nest jars paired with a ceramic serving bowl, lid, spoon, and preparation accessories',
            'buyers' => 'premium food brands, ceremonial gift suppliers, hotels, corporate buyers, and home-wellness retailers',
            'problem' => 'separating fragile ceramic accessories from glass jars while creating a balanced preparation-themed presentation',
            'structure' => 'a hinged rigid gift box with distinct jar, bowl, lid, and spoon cavities plus a reinforced base and controlled closure',
            'insert' => 'a multi-depth fitted tray with a bowl well, spoon channel, jar cavity, lid recess, finger notches, and soft surface protection',
            'information' => 'set contents, bird nest grade, serving ritual, preparation guidance, material care, ingredients, storage, origin, barcode, batch, and expiry',
            'channels' => 'ceremonial gifting, hotels, premium home retail, corporate programs, holiday campaigns, and export gift distribution',
            'measure' => 'bowl diameter and foot, lid profile, spoon length, filled jar size, fragile edges, cavity depths, component order, and total set weight',
            'overview_copy' => 'A bowl-and-spoon set turns bird nest packaging into a complete serving ritual and requires protection for mixed glass and ceramic geometries. The bowl foot, glazed rim, loose lid, and long spoon create different impact points, so the tray needs a ceramic-first layout rather than a standard jar grid. Hospitality buyers may also need the serving pieces to be removable in a natural left-to-right sequence.',
            'fit_copy' => 'The bowl, lid, spoon, and jar each need independent support so one component cannot transfer impact into another during handling.',
            'structure_copy' => 'A hinged rigid box and multi-level tray can display the bowl as the focal point while keeping the jar and spoon securely aligned.',
            'display_copy' => 'The layout should immediately explain how the accessories belong together and provide finger access without lifting fragile pieces by their edges.',
            'branding_copy' => 'Inner-lid artwork can communicate serving steps, gift meaning, ceramic care, or a hospitality story that supports the ceremonial theme.',
            'material_copy' => 'Rigid board and a soft-faced fitted tray provide both structural support and surface protection for glazed ceramic and printed jars.',
            'operations_copy' => 'Packers should verify bowl seating, lid orientation, spoon channel, jar label, card version, cavity count, and lid closure.',
            'channel_copy' => 'This presentation is best for premium gifting and hospitality programs where accessories add value beyond the food product alone.',
            'qc_copy' => 'Check ceramic contact points, tray depth, spoon retention, jar clearance, lid security, box closure, and packed drop protection.',
            'sustainability_copy' => 'A molded pulp or engineered paper tray may be evaluated as an alternative to foam when fragile-component testing is successful.',
            'materials' => 'rigid greyboard, specialty wrapping paper, soft-touch art paper, molded pulp, paper-wrapped EVA, or fabric-covered tray',
            'finish' => 'Offset printing, foil stamping, embossing, matte lamination, textured paper, inner printing, and ribbon detail',
            'feature' => 'Separate bowl, spoon, lid, and jar cavities with ceremonial gift presentation',
            'paper' => 'Rigid Greyboard / Specialty Paper / Molded Pulp or Soft-Faced Tray',
            'box_type' => 'Bird Nest Bowl and Spoon Rigid Gift Box',
            'shape' => 'Wide hinged multi-component gift box',
            'accessories' => 'Ceramic bowl, lid, spoon, fitted tray, information card, ribbon',
            'liner' => 'Soft-faced EVA, molded pulp, fabric, or paperboard fitted tray',
            'details' => array('bowl and foot dimensions', 'lid profile', 'spoon length', 'jar dimensions', 'fragile contact zones', 'cavity depth', 'component sequence', 'care card', 'total weight', 'outer shipper'),
            'tests' => array('bowl retention', 'lid separation', 'spoon channel', 'jar clearance', 'surface scratching', 'tray compression', 'box closure', 'packed drop test'),
            'mistakes' => array('allowing ceramic pieces to touch', 'using shallow spoon retention', 'providing no finger access around the bowl', 'testing the box without all filled components'),
            'quote' => 'production-equivalent bowl, lid, spoon, filled jar, component map, fragile zones, tray material preference, artwork, card, and shipping test expectations',
            'related' => array(array('/product/custom-bird-nest-rock-sugar-gift-box/', 'bird nest and rock sugar gift sets'), array('/product/custom-green-bird-nest-compartment-gift-box/', 'multi-compartment bird nest gift boxes')),
            'headings' => array('Bird Nest Bowl and Spoon Presentation Boxes', 'Separating Fragile Ceramic and Glass Components', 'Multi-Depth Trays for Serving Accessories', 'Designing a Complete Preparation Ritual', 'Gift Story and Care Information Panels', 'Soft-Faced Tray and Rigid Board Choices', 'Packing Bowls, Spoons, Lids, and Jars', 'Hospitality and Ceremonial Gift Channels', 'Fragile Accessory Quality Testing', 'Bowl-and-Spoon Gift Box Quote Details'),
            'duplicate_risk' => 3,
        ),
        array(
            'slug' => 'custom-bird-nest-paper-tube-packaging',
            'title' => 'CUSTOM BIRD NEST PAPER TUBE PACKAGING',
            'keyword' => 'bird nest paper tube packaging',
            'inside' => 'a single premium bird nest jar, dried nest container, concentrate jar, or cylindrical health-food product',
            'buyers' => 'boutique bird nest brands, premium grocers, hotel gift shops, specialty retailers, and ecommerce sellers',
            'problem' => 'centering one jar inside a cylindrical pack while controlling cap and base clearance, label direction, and tube-lid fit',
            'structure' => 'a rigid spiral-wound paper tube with fitted top and bottom, jar locator, pull access, and optional shoulder or inner collar',
            'insert' => 'a circular paperboard platform, molded pulp ring, neck collar, bottom pad, and finger or ribbon lift',
            'information' => 'product grade, jar size, ingredients, nutrition, origin, storage, preparation, barcode, batch, expiry, and disposal guidance',
            'channels' => 'boutique retail, hotel gifting, specialty ecommerce, travel gifts, distributor samples, and export health-food sales',
            'measure' => 'jar diameter, lid diameter, total height, label direction, tube inner diameter, top clearance, bottom pad, and removal access',
            'overview_copy' => 'A cylindrical tube gives a single bird nest jar distinctive shelf presence and a reusable presentation, but round tolerances must be controlled carefully.',
            'fit_copy' => 'A loose inner diameter allows the jar to rotate and rattle, while an overly tight ring can trap the jar or scrape its label.',
            'structure_copy' => 'The tube wall, top shoulder, base, and circular locator should work together so the jar remains centered and easy to lift.',
            'display_copy' => 'Removing the tube lid can reveal the jar cap and label as a single focal product, supported by a ribbon or finger notch.',
            'branding_copy' => 'Wraparound artwork can create a continuous botanical, origin, or premium story, but barcode and small text need a readable flat viewing zone.',
            'material_copy' => 'Spiral paper cores, art-paper wraps, specialty paper, and circular paper inserts determine wall stiffness and surface quality.',
            'operations_copy' => 'Tube assembly should control roundness, wrap seam, cap fit, jar orientation, locator seating, and label alignment before packing.',
            'channel_copy' => 'The compact round pack suits boutique display and gifting, while shipping cases should prevent tube rolling and cap movement.',
            'qc_copy' => 'Check internal diameter, cap friction, base security, jar rattle, removal force, wrap seam, foil position, and case fit.',
            'sustainability_copy' => 'Paper-based circular supports and right-sized tube walls can reduce plastic inserts while maintaining centered presentation.',
            'materials' => 'spiral-wound paper core, coated art paper, kraft paper, specialty wrap, paperboard rings, or molded pulp locator',
            'finish' => 'Offset printing, Pantone colors, foil stamping, embossing, matte lamination, textured wrap, and inner printing',
            'feature' => 'Cylindrical rigid structure, centered single jar, fitted lid and base, wraparound branding',
            'paper' => 'Spiral Paper Core / Art Paper / Kraft Paper / Specialty Wrap',
            'box_type' => 'Rigid Bird Nest Paper Tube',
            'shape' => 'Round cylindrical tube',
            'accessories' => 'Circular locator, neck collar, bottom pad, ribbon lift',
            'liner' => 'Paperboard ring, molded pulp collar, or circular base platform',
            'details' => array('jar and lid diameter', 'overall jar height', 'tube inner diameter', 'top clearance', 'base pad', 'label direction', 'ribbon lift', 'wrap seam', 'cap fit', 'case layout'),
            'tests' => array('tube roundness', 'cap friction', 'base security', 'jar centering', 'label scuffing', 'removal access', 'wrap alignment', 'case fit'),
            'mistakes' => array('using nominal jar diameter only', 'making the locator too tight', 'placing the barcode on a difficult curve', 'shipping tubes without anti-roll case support'),
            'quote' => 'filled jar sample, maximum jar and lid diameter, tube height, locator preference, cap style, wrap artwork, foil mask, removal method, and case layout',
            'related' => array(array('/product/custom-single-jar-bird-nest-window-box/', 'single-jar window cartons'), array('/product/custom-paper-tube-food-packaging-box/', 'custom paper tube food boxes')),
            'headings' => array('Cylindrical Paper Tubes for Bird Nest Jars', 'Controlling Round Jar Clearance', 'Tube Walls, Caps, and Circular Locators', 'Creating a Single-Product Reveal', 'Wraparound Bird Nest Branding', 'Paper Core and Specialty Wrap Options', 'Tube Assembly and Jar Orientation', 'Boutique Retail and Ecommerce Use', 'Round-Pack Fit and Closure Testing', 'Bird Nest Paper Tube RFQ Checklist'),
            'duplicate_risk' => 3,
        ),
        array(
            'slug' => 'custom-bird-nest-rock-sugar-gift-box',
            'title' => 'CUSTOM BIRD NEST ROCK SUGAR GIFT BOX',
            'keyword' => 'bird nest rock sugar gift box',
            'inside' => 'bird nest jars paired with rock sugar, preparation ingredients, a spoon, and serving information',
            'buyers' => 'bird nest brands, health-food gift suppliers, premium grocers, corporate programs, and hotel retailers',
            'problem' => 'organizing ingredients and accessories of different sizes while preventing jars, sugar containers, and spoons from contacting each other',
            'structure' => 'a hinged or lid-and-base rigid box with separate jar, rock-sugar, spoon, and information-card compartments',
            'insert' => 'a multi-cavity paperboard, molded pulp, or soft-faced tray with ingredient separation and clear preparation order',
            'information' => 'set contents, bird nest grade, rock sugar details, ingredients, preparation steps, nutrition, storage, origin, barcode, batch, and expiry',
            'channels' => 'premium food retail, preparation gift sets, corporate gifting, hotel programs, seasonal campaigns, and export distribution',
            'measure' => 'jar and sugar-container dimensions, spoon length, component weights, seal clearance, tray depths, preparation card, and total set weight',
            'overview_copy' => 'A bird nest and rock sugar set presents both the premium ingredient and its preparation ritual, making component order central to the packaging story. Rock sugar is a separate food component with its own container, label, seal, and quantity, so the tray map should distinguish ingredients clearly. The opening sequence can guide a first-time customer from the bird nest jar to sugar, spoon, and preparation card.',
            'fit_copy' => 'Separate cavities protect glass and food containers while keeping the spoon and preparation card from moving over printed labels.',
            'structure_copy' => 'A rigid presentation box with a multi-depth tray can place the main bird nest product first and supporting ingredients beside it.',
            'display_copy' => 'The customer should understand which items are included and how they relate without removing every component from the tray.',
            'branding_copy' => 'Inner-panel artwork can explain preparation steps, ingredient origin, serving suggestions, and the distinction between the bird nest and sugar components.',
            'material_copy' => 'Rigid board and a clean cream, gold, or botanical wrap support a premium culinary-health position.',
            'operations_copy' => 'Packers should verify jar, sugar, spoon, card, labels, cavity order, seals, and final closure as a complete kit.',
            'channel_copy' => 'The educational gift format works for new customers, hotels, corporate gifts, and premium stores that want a ready-to-prepare set.',
            'qc_copy' => 'Check component count, cavity fit, spoon retention, container clearance, card version, tray compression, and case protection.',
            'sustainability_copy' => 'A well-designed paper or molded-pulp tray can organize the ingredient set without excessive decorative layers.',
            'materials' => 'rigid greyboard, coated art paper, specialty paper, paperboard tray, molded pulp, or paper-wrapped EVA',
            'finish' => 'Offset printing, gold foil, embossing, matte lamination, spot UV, inner printing, and ribbon detail',
            'feature' => 'Separated bird nest, rock sugar, spoon, and card compartments with preparation story',
            'paper' => 'Rigid Greyboard / Art Paper / Specialty Paper / Fitted Tray',
            'box_type' => 'Bird Nest and Rock Sugar Rigid Gift Box',
            'shape' => 'Wide multi-component presentation box',
            'accessories' => 'Rock sugar container, spoon, fitted tray, preparation card, ribbon',
            'liner' => 'Paperboard, molded pulp, or soft-faced multi-cavity tray',
            'details' => array('bird nest jar size', 'rock sugar container', 'spoon length', 'component weights', 'seal clearance', 'tray map', 'preparation card', 'ingredient panels', 'total weight', 'shipping case'),
            'tests' => array('component count', 'jar clearance', 'sugar-container retention', 'spoon channel', 'card fit', 'tray compression', 'closure', 'case protection'),
            'mistakes' => array('letting the spoon move across labels', 'mixing ingredient order', 'using one tray depth for all components', 'omitting preparation-card space'),
            'quote' => 'all filled components, jar and sugar-container dimensions, spoon, tray map, preparation card, approved ingredient copy, artwork, and shipping requirements',
            'related' => array(array('/product/custom-bird-nest-bowl-and-spoon-gift-box/', 'bird nest serving bowl gift sets'), array('/product/custom-green-bird-nest-compartment-gift-box/', 'compartment bird nest gift packaging')),
            'headings' => array('Bird Nest and Rock Sugar Gift Packaging', 'Separating Ingredients and Serving Accessories', 'Multi-Cavity Trays for Preparation Sets', 'Explaining the Bird Nest Preparation Ritual', 'Ingredient, Origin, and Serving Information', 'Premium Rigid Materials and Finishes', 'Complete-Kit Packing and Count Control', 'Corporate, Hotel, and Retail Gift Use', 'Component-Fit Quality Checks', 'Rock Sugar Gift Box Quotation Inputs'),
            'duplicate_risk' => 3,
        ),
        array(
            'slug' => 'custom-bird-nest-sachet-packaging-box',
            'title' => 'CUSTOM BIRD NEST SACHET PACKAGING BOX',
            'keyword' => 'bird nest sachet packaging box',
            'inside' => 'single-dose bird nest concentrate sachets, powder sticks, daily wellness pouches, and counted treatment-style packs',
            'buyers' => 'bird nest concentrate brands, nutraceutical manufacturers, sachet fillers, pharmacies, and subscription wellness companies',
            'problem' => 'controlling sachet count and seal edges while providing convenient dispensing, preparation guidance, and retail display',
            'structure' => 'a folding sachet carton with count-sized volume, optional divider, dispensing or display opening, leaflet space, and secure closure',
            'insert' => 'paper dividers, count-control blocks, an inner band, withdrawal notch, and leaflet separation where required',
            'information' => 'sachet count, serving size, ingredients, nutrition, preparation, storage, flavor or grade, barcode, batch, and expiry',
            'channels' => 'pharmacy retail, daily wellness programs, travel packs, ecommerce subscriptions, clinics, and export health-food distribution',
            'measure' => 'filled sachet width, seal flange, thickness variation, powder settlement, tear notch, stack count, leaflet fold, and dispensing direction',
            'overview_copy' => 'Bird nest concentrate sachets create a portable daily format, and the carton should keep the counted set orderly from first opening to the last serving.',
            'fit_copy' => 'Flexible pouches change thickness as contents settle; excess space creates disorder while tight fit can crease heat seals and hide tear notches.',
            'structure_copy' => 'A compact folding carton may use a divider or dispensing cutout to keep sachets upright, counted, and easy to withdraw.',
            'display_copy' => 'The opening can reveal a clean row of individual sachets and make the daily-use sequence easy to understand.',
            'branding_copy' => 'The carton can connect premium bird nest identity with practical count, flavor, serving, and preparation information.',
            'material_copy' => 'Printable folding boxboard supports strong retail colors, diagrams, controlled perforations, and high-volume packing.',
            'operations_copy' => 'Trials should account for sachet settlement, seal orientation, count verification, leaflet position, dispensing feature, and final closure.',
            'channel_copy' => 'The portable format suits pharmacies, subscriptions, travel, sampling, and daily wellness routines.',
            'qc_copy' => 'Check count fit, seal clearance, carton bulging, sachet removal, perforation, leaflet fit, barcode, and version control.',
            'sustainability_copy' => 'Right-sized cartons and paper dividers can reduce empty volume while each sachet retains the direct food barrier.',
            'materials' => 'SBS paperboard, ivory board, kraft board, recycled folding boxboard, and paper divider',
            'finish' => 'Offset printing, Pantone or CMYK colors, matte coating, foil accents, perforation, barcode, and batch zones',
            'feature' => 'Counted sachet arrangement, seal-edge clearance, dispensing option, daily-use information',
            'paper' => 'SBS / Ivory Board / Kraft or Recycled Folding Boxboard',
            'box_type' => 'Bird Nest Sachet Folding Carton',
            'shape' => 'Tall or rectangular sachet carton',
            'accessories' => 'Paper divider, dispensing perforation, leaflet, inner band',
            'liner' => 'Paperboard divider or count-control block',
            'details' => array('filled sachet dimensions', 'seal-edge width', 'sachets per carton', 'settled stack thickness', 'tear-notch direction', 'leaflet size', 'dispensing option', 'flavor variants', 'coding area', 'case count'),
            'tests' => array('count fit', 'seal clearance', 'carton bulge', 'sachet removal', 'perforation tear', 'leaflet loading', 'barcode scan', 'version reconciliation'),
            'mistakes' => array('measuring empty sachets only', 'pinching heat seals', 'hiding tear notches', 'weakening the carton with an oversized display cutout'),
            'quote' => 'filled sachets, maximum settled thickness, count, seal and tear-notch drawing, leaflet dummy, dispensing preference, variants, artwork, and line speed',
            'related' => array(array('/product/custom-single-jar-bird-nest-window-box/', 'single-jar bird nest retail boxes'), array('/product/custom-sachet-stick-pack-carton/', 'custom sachet and stick-pack cartons')),
            'headings' => array('Bird Nest Sachet Cartons for Daily Servings', 'Planning Around Filled Pouch Thickness', 'Dividers and Dispensing Openings', 'Presenting a Clean Row of Sachets', 'Count, Flavor, and Preparation Information', 'Folding Board and Perforation Choices', 'Sachet Settlement and Packing Control', 'Pharmacy, Travel, and Subscription Use', 'Count and Dispensing Quality Tests', 'Bird Nest Sachet Carton RFQ Checklist'),
            'duplicate_risk' => 3,
        ),
        array(
            'slug' => 'custom-dried-bird-nest-window-display-box',
            'title' => 'CUSTOM DRIED BIRD NEST WINDOW DISPLAY BOX',
            'keyword' => 'dried bird nest window display box',
            'inside' => 'individually protected dried bird nests, shaped nest portions, premium trays, and product information cards',
            'buyers' => 'dried bird nest processors, premium food retailers, specialty grocers, gift suppliers, and export distributors',
            'problem' => 'showing delicate dried nests through a window without crushing portions, exposing them beyond their approved primary protection, or weakening the carton',
            'structure' => 'a rigid or folding display box with a controlled window, protected inner tray, nest cavities, information panel, and secure closure',
            'insert' => 'individual paper or molded-pulp nests trays, cavity dividers, a clear cover where approved, and removal tabs',
            'information' => 'grade, piece count, net weight, origin, preparation, ingredients, storage, authenticity details, barcode, batch, and expiry',
            'channels' => 'premium counters, specialty food stores, gift retail, distributor showrooms, hotel shops, and export dried-food sales',
            'measure' => 'primary tray dimensions, nest portion height, cavity count, clear-cover clearance, window cutout, edge distance, card size, and packed weight',
            'overview_copy' => 'A display window lets customers inspect dried nest shape and presentation, but the carton must protect this fragile product rather than treating visibility as the only goal.',
            'fit_copy' => 'Dried nests should remain inside their approved tray or cover, with enough top clearance to prevent contact with the window during handling.',
            'structure_copy' => 'The window cutout needs a stable border, accurately bonded film where used, and a fitted inner tray that prevents portions shifting into the opening.',
            'display_copy' => 'Cavity spacing, nest direction, background color, and window geometry should present the natural product clearly without overcrowding.',
            'branding_copy' => 'Solid panels can explain grade, piece count, origin, authenticity, preparation, and storage while the window supplies visual confidence.',
            'material_copy' => 'Stiff paperboard, rigid board, a clean tray, and carefully specified window film maintain presentation and structural integrity.',
            'operations_copy' => 'Packing should verify portion count, tray position, clear cover, card, window cleanliness, closure, and label or batch information.',
            'channel_copy' => 'Window display works well at premium counters, but ecommerce and export orders still need an outer shipper and vibration control.',
            'qc_copy' => 'Check window bonding, dust and scratches, border stiffness, cavity clearance, portion movement, tray removal, and case compression.',
            'sustainability_copy' => 'Window size can be minimized or a cellulose-based option evaluated when it meets visibility, cleanliness, and performance needs.',
            'materials' => 'high-stiffness SBS, ivory board, rigid greyboard, paper or molded-pulp tray, and PET or specified cellulose window',
            'finish' => 'Offset printing, matte lamination, gold foil, embossing, spot UV, window die cutting, and inner printing',
            'feature' => 'Product-view window, protected dried nest cavities, premium grade information, anti-crush tray',
            'paper' => 'High-Stiffness SBS / Rigid Board / Paper Tray / Window Film',
            'box_type' => 'Dried Bird Nest Window Display Box',
            'shape' => 'Wide rectangular window presentation box',
            'accessories' => 'Window film, nest tray, clear cover, information card, removal tabs',
            'liner' => 'Paperboard or molded-pulp individual nest tray',
            'details' => array('primary nest tray', 'portion dimensions', 'piece count', 'top clearance', 'window size', 'window edge distance', 'clear cover', 'grade card', 'net weight', 'shipping case'),
            'tests' => array('window bond', 'border stiffness', 'top clearance', 'portion movement', 'tray retention', 'window scratching', 'product visibility', 'case compression'),
            'mistakes' => array('letting nests touch the window', 'cutting the window too close to folds', 'using no protective primary tray', 'leaving too little solid panel for required information'),
            'quote' => 'protected dried nest samples, tray dimensions, portion height and count, window target, clear-cover specification, card, approved copy, artwork, and shipping tests',
            'related' => array(array('/product/custom-single-jar-bird-nest-window-box/', 'single-jar window bird nest boxes'), array('/product/custom-green-bird-nest-compartment-gift-box/', 'bird nest compartment display boxes')),
            'headings' => array('Window Boxes for Delicate Dried Bird Nests', 'Protecting Nest Portions Behind the Display Area', 'Window Borders, Films, and Fitted Trays', 'Arranging Natural Nests for Visual Inspection', 'Grade, Origin, and Authenticity Information', 'Stiff Board and Clean Window Materials', 'Dried Nest Count and Packing Control', 'Premium Counter and Export Distribution', 'Window and Anti-Crush Quality Testing', 'Dried Bird Nest Display Box Quote Inputs'),
            'duplicate_risk' => 3,
        ),
        array(
            'slug' => 'custom-single-jar-bird-nest-window-box',
            'title' => 'CUSTOM SINGLE JAR BIRD NEST WINDOW BOX',
            'keyword' => 'single jar bird nest window box',
            'inside' => 'one bird nest jar, concentrate jar, dried nest container, or premium health-food bottle',
            'buyers' => 'bird nest brands, specialty food retailers, pharmacies, hotel gift shops, and ecommerce sellers',
            'problem' => 'keeping one jar upright and label-forward while the window remains strong, clean, and correctly aligned with the product',
            'structure' => 'a vertical folding carton with a die-cut window, reinforced side panels, base locator, top clearance, and secure tuck or locked bottom',
            'insert' => 'a folded base platform, side rails, circular locator, neck guide, and finger-access opening',
            'information' => 'product type, jar volume, grade, ingredients, nutrition, origin, preparation, storage, barcode, batch, and expiry',
            'channels' => 'specialty retail, pharmacy shelves, hotel gift shops, sampling, ecommerce, and export single-unit sales',
            'measure' => 'filled jar weight, base and lid diameter, label position, jar height, window alignment, top clearance, base closure, and case orientation',
            'overview_copy' => 'A single-jar window carton combines direct product visibility with a compact retail footprint and should keep the label aligned behind the opening.',
            'fit_copy' => 'The jar needs enough support to prevent rotation and rattle without loading the lid or scraping the visible label.',
            'structure_copy' => 'A vertical folding carton can use a base locator and reinforced window border to stabilize the jar and preserve panel strength.',
            'display_copy' => 'Window height and width should frame the intended product face rather than expose an unprinted seam, barcode, or empty area.',
            'branding_copy' => 'The solid front and side areas can carry premium identity and required food information while the window proves the packed product format.',
            'material_copy' => 'Stiff SBS or ivory board helps narrow panels remain straight; the window film and adhesive need clean, consistent application.',
            'operations_copy' => 'Packers should orient the jar label, seat the base locator, verify window cleanliness, confirm top clearance, and close the bottom securely.',
            'channel_copy' => 'The single-unit carton works for shelf display and sampling, while ecommerce needs an additional protective shipper.',
            'qc_copy' => 'Check jar rotation, label-window alignment, border stiffness, film bond, cap clearance, bottom load, and barcode readability.',
            'sustainability_copy' => 'A restrained window and folded paper locator can reduce material complexity while keeping the jar visible.',
            'materials' => 'high-stiffness SBS, ivory board, kraft board, paper locator, and PET or specified cellulose window film',
            'finish' => 'Offset printing, matte coating, foil stamping, embossing, spot UV, window die cutting, and barcode zones',
            'feature' => 'Single upright jar, label-view window, reinforced folding carton, base locator',
            'paper' => 'SBS / Ivory Board / Kraft Board / Window Film',
            'box_type' => 'Single Jar Bird Nest Window Folding Box',
            'shape' => 'Tall narrow rectangular window carton',
            'accessories' => 'Window film, base locator, neck guide, tamper label',
            'liner' => 'Folded paperboard jar platform or circular locator',
            'details' => array('filled jar dimensions and weight', 'lid diameter', 'label direction', 'window size', 'window alignment', 'top clearance', 'base locator', 'bottom closure', 'coding area', 'case orientation'),
            'tests' => array('jar rotation', 'window alignment', 'film bond', 'border stiffness', 'cap clearance', 'bottom load', 'label scuffing', 'barcode scan'),
            'mistakes' => array('showing the label seam through the window', 'cutting away too much front-panel strength', 'supporting the jar by its lid', 'using no anti-rotation locator'),
            'quote' => 'filled jar, maximum lid and body dimensions, visible label area, window target, locator preference, bottom style, artwork, approved information, and case count',
            'related' => array(array('/product/custom-bird-nest-paper-tube-packaging/', 'single-jar bird nest paper tubes'), array('/product/custom-dried-bird-nest-window-display-box/', 'dried bird nest window display boxes')),
            'headings' => array('Single-Jar Bird Nest Window Cartons', 'Holding the Jar Upright and Label-Forward', 'Reinforced Window Carton Structures', 'Framing the Product Through the Window', 'Balancing Visibility With Food Information', 'Stiff Board, Film, and Surface Finishes', 'Jar Orientation During Packing', 'Retail Shelf and Ecommerce Use', 'Window Alignment and Bottom-Load Tests', 'Single-Jar Window Box RFQ Checklist'),
            'duplicate_risk' => 3,
        ),
    );

    $views = array('front packaging view', 'back information-panel view', 'open package and product-fit view', 'open display and component view');
    foreach ($products as &$product) {
        $product['images'] = array();
        $product['captions'] = array();
        $product['alts'] = array();
        foreach ($views as $index => $view) {
            $product['images'][] = $product['slug'] . '-' . ($index + 1) . '.webp';
            $product['captions'][] = ucwords(strtolower($product['title'])) . ' — ' . $view . '.';
            $product['alts'][] = $product['keyword'] . ' for premium food packaging, ' . $view;
        }
    }
    unset($product);
    return $products;
}

$marker = 'product-samples-bird-nest-packaging-202608';
$products = vpn_bird_202608_products();
$category = get_term_by('slug', 'bird-nest-packaging-boxes', 'product_cat');
if (!$category || is_wp_error($category)) {
    $created = wp_insert_term('Bird Nest Packaging Boxes', 'product_cat', array('slug' => 'bird-nest-packaging-boxes'));
    if (is_wp_error($created)) {
        fwrite(STDERR, 'Unable to create Bird Nest Packaging Boxes category.' . PHP_EOL);
        exit(1);
    }
    $category = get_term((int) $created['term_id'], 'product_cat');
}

$audit = array('# Bird Nest Packaging Products 202608 Audit', '', 'Local batch marker: `' . $marker . '`', '');
$export = array('# Bird Nest Packaging Products 202608 — Text Only', '', 'Generated from local WooCommerce products for duplicate-risk review.', '');

foreach ($products as $product) {
    $image_ids = array();
    foreach ($product['images'] as $index => $filename) {
        $image_ids[] = vpn_bird_202608_attachment_id($filename, $product['alts'][$index], $product['captions'][$index], $product['captions'][$index]);
    }
    if (count(array_filter($image_ids)) !== 4) {
        fwrite(STDERR, 'Missing image attachment for ' . $product['title'] . PHP_EOL);
        continue;
    }

    $short = vpn_bird_202608_short($product);
    $content = vpn_bird_202608_content($product, $image_ids);
    $existing = get_page_by_path($product['slug'], OBJECT, 'product');
    $postarr = array(
        'post_type' => 'product', 'post_status' => 'publish', 'post_title' => $product['title'],
        'post_name' => $product['slug'], 'post_excerpt' => $short, 'post_content' => $content,
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
    wp_set_object_terms($product_id, array((int) $category->term_id), 'product_cat', false);
    wp_set_object_terms($product_id, 'simple', 'product_type', false);
    wp_set_object_terms($product_id, array($product['keyword'], 'bird nest packaging', 'health food packaging', 'premium gift box', 'custom paper box'), 'product_tag', false);
    set_post_thumbnail($product_id, $image_ids[0]);
    update_post_meta($product_id, '_product_image_gallery', implode(',', array_slice($image_ids, 1)));
    update_post_meta($product_id, '_sku', 'sample-bird-nest-202608-' . $product['slug']);
    update_post_meta($product_id, '_regular_price', '');
    update_post_meta($product_id, '_price', '');
    update_post_meta($product_id, '_stock_status', 'instock');
    update_post_meta($product_id, '_manage_stock', 'no');
    update_post_meta($product_id, '_visibility', 'visible');
    update_post_meta($product_id, '_custom_box_product_specs', vpn_bird_202608_specs($product));
    update_post_meta($product_id, '_vpn_sample_import', $marker);
    update_post_meta($product_id, '_vpn_product_specific_details', $product['details']);
    update_post_meta($product_id, '_vpn_duplicate_risk_score', $product['duplicate_risk']);
    update_post_meta($product_id, 'rank_math_focus_keyword', $product['keyword']);
    update_post_meta($product_id, 'rank_math_title', $product['title'] . ' | VPN PAPER BOX MANUFACTURER');
    $meta = 'Custom ' . $product['keyword'] . ' with product-fit insert, premium printing, four image views, and MOQ from 1000 boxes.';
    update_post_meta($product_id, 'rank_math_description', substr($meta, 0, 154));

    $saved = (string) get_post_field('post_content', $product_id);
    $plain = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($saved)));
    $words = str_word_count($plain);
    $short_words = str_word_count(wp_strip_all_tags($short));
    $figures = substr_count($saved, '<figure class="product-inline-figure');

    $audit[] = '## ' . $product['title'];
    $audit[] = '- ID: ' . $product_id;
    $audit[] = '- URL: ' . get_permalink($product_id);
    $audit[] = '- Status: ' . get_post_status($product_id);
    $audit[] = '- Focus keyword: ' . $product['keyword'];
    $audit[] = '- Long description words: ' . $words;
    $audit[] = '- Short description words: ' . $short_words;
    $audit[] = '- Content H1 count: ' . preg_match_all('/<h1\b/i', $saved);
    $audit[] = '- Product images: ' . count(array_unique($image_ids));
    $audit[] = '- Inline figures: ' . $figures;
    $audit[] = '- Internal links: ' . substr_count($saved, '<a ');
    $audit[] = '- Specification rows: ' . count(vpn_bird_202608_specs($product));
    $audit[] = '- Duplicate risk score: ' . $product['duplicate_risk'] . '/10';
    $audit[] = '- Product-specific details: ' . implode('; ', $product['details']);
    $audit[] = '- Source images: ' . implode(', ', $product['images']);
    $audit[] = '';

    $export[] = '## ' . $product['title'];
    $export[] = '';
    $export[] = '- URL: ' . get_permalink($product_id);
    $export[] = '- Word count: ' . $words;
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

    echo 'Imported: ' . $product['title'] . ' (#' . $product_id . ') words=' . $words . ' short=' . $short_words . ' images=4 figures=' . $figures . PHP_EOL;
}

file_put_contents(dirname(__DIR__) . '/product-samples-bird-nest-packaging-202608-audit.md', implode(PHP_EOL, $audit));
file_put_contents(dirname(__DIR__) . '/product-samples-bird-nest-packaging-202608-descriptions-text-only.md', implode(PHP_EOL, $export));
echo 'Bird nest packaging August 2026 import complete.' . PHP_EOL;
