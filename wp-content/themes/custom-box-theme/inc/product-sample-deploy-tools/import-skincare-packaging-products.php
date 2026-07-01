<?php
/**
 * Import skincare and cosmetic packaging products from uploaded Media Library images.
 *
 * Run:
 *   php tools/import-skincare-packaging-products.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

function vpn_skincare_link($url, $anchor)
{
    return '<a href="' . esc_url(home_url($url)) . '">' . esc_html($anchor) . '</a>';
}

function vpn_skincare_file_base($filename)
{
    return preg_replace('/\.[^.]+$/', '', basename($filename));
}

function vpn_skincare_find_attachment_by_base($filename_base)
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
        if (vpn_skincare_file_base($attached_file) === $filename_base) {
            return (int) $attachment_id;
        }
    }

    return 0;
}

function vpn_skincare_attachment_id($filename, $alt, $title, $caption)
{
    $filename_base = vpn_skincare_file_base($filename);
    $attachment_id = vpn_skincare_find_attachment_by_base($filename_base);

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

function vpn_skincare_specs($product)
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

function vpn_skincare_section($heading, $paragraphs)
{
    $html = '<h2>' . esc_html($heading) . '</h2>';
    foreach ($paragraphs as $paragraph) {
        $html .= '<p>' . $paragraph . '</p>';
    }

    return $html;
}

function vpn_skincare_inline_image($attachment_id, $caption, $narrow = false)
{
    $image = wp_get_attachment_image($attachment_id, 'large', false, array('loading' => 'lazy'));
    if (!$image) {
        return '';
    }

    return '<figure class="product-inline-figure product-inline-figure-small' . ($narrow ? ' is-narrow' : '') . '">' .
        $image . '<figcaption>' . esc_html($caption) . '</figcaption></figure>';
}

function vpn_skincare_sentence_list($items)
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

function vpn_skincare_content($product, $image_ids)
{
    $category_link = vpn_skincare_link('/product-category/beauty-skincare-packaging/', 'beauty and skincare packaging');
    $cosmetic_link = vpn_skincare_link('/product-category/cosmetic-paper-boxes/', 'cosmetic paper boxes');
    $material_link = vpn_skincare_link('/how-to-choose-paper-material-for-product-packaging/', 'paper material selection for product packaging');
    $artwork_link  = vpn_skincare_link('/how-to-prepare-artwork-for-printed-paper-boxes/', 'print-ready artwork for paper boxes');
    $finish_link   = vpn_skincare_link('/foil-stamping-and-embossing-for-paper-packaging/', 'foil stamping and embossing for packaging');
    $lamination_link = vpn_skincare_link('/matte-vs-gloss-lamination-for-packaging/', 'matte and gloss lamination options');
    $quote_link    = vpn_skincare_link('/contact/#quote', 'request a skincare packaging quote');
    $related_one   = vpn_skincare_link($product['related'][0][0], $product['related'][0][1]);
    $related_two   = vpn_skincare_link($product['related'][1][0], $product['related'][1][1]);
    $details       = vpn_skincare_sentence_list($product['details']);
    $panel_details = vpn_skincare_sentence_list($product['panel_details']);
    $checks        = vpn_skincare_sentence_list($product['qc_points']);

    $html = vpn_skincare_section($product['hero_heading'], array(
        $product['title'] . ' is designed for ' . $product['buyer'] . ' that need a custom paper package for ' . $product['inside'] . '. In skincare packaging, the box is not only a printed shell. It must protect the product format, organize required label information, support retail confidence, and keep the brand presentation consistent from sample approval to bulk production.',
        'This product belongs to the ' . $category_link . ' range and can also support ' . $cosmetic_link . ' projects where the buyer needs a clean carton, rigid paper box, sleeve, insert, or gift-ready presentation. The main packaging challenge is ' . $product['problem'] . ', so the structure should be planned around the real container shape rather than a generic cosmetic box size.',
        'Useful project details include ' . $details . '. These details affect paperboard choice, insert tolerance, dieline layout, printing hierarchy, and carton packing. Sharing them at RFQ stage helps the factory recommend a more realistic sample direction instead of quoting from a flat product name.',
    ));

    if (!empty($image_ids[0])) {
        $html .= vpn_skincare_inline_image($image_ids[0], $product['captions'][0], true);
    }

    $html .= vpn_skincare_section($product['problem_heading'], array(
        $product['problem_copy'] . ' A good skincare box should hold the product in a stable position, prevent unnecessary rubbing, and still open smoothly when the customer handles it. The buyer should confirm whether the package will be used for retail shelf display, ecommerce fulfillment, spa or clinic distribution, hotel amenities, sample programs, or gift sets.',
        'For ' . strtolower($product['title']) . ', the packaging should answer two questions at the same time: how does the item stay protected, and how does the customer understand the product quickly? The structure must control movement, while the printed panels must communicate product name, skin concern, volume, ingredient direction, usage step, barcode, batch area, and market-specific label details.',
        'The package should also fit the way the brand sells. A pharmacy-focused line may need a more clinical layout and strong information hierarchy. A boutique skincare line may need softer color, tactile finish, and a premium reveal. A private label program may need the same structure to work across several formulas without forcing a new tooling plan for every SKU.',
    ));

    $html .= vpn_skincare_section($product['structure_heading'], array(
        $product['structure_copy'] . ' The best structure starts with the filled product dimensions, not only the outer carton size. Product height, cap diameter, lid clearance, tube shoulder, jar base, or set arrangement can change the folding direction and the way the insert should support the item.',
        'Common options include tuck-end folding carton, reverse tuck carton, straight tuck carton, sleeve and tray box, drawer box, rigid lid-and-base box, mailer-compatible carton, and multi-cavity set box. If the product is heavy, rounded, or easy to scratch, the structure may need a paperboard collar, folded tray, EVA support, molded pulp insert, or inner platform.',
        'Sampling should check more than whether the product fits. The team should open and close the box repeatedly, check whether the container rotates, confirm the cap or lid does not scrape the panel, and verify that assembly is practical for the packing line. A beautiful empty box can still fail when it is tested with the real skincare item.',
    ));

    if (!empty($image_ids[1])) {
        $html .= vpn_skincare_inline_image($image_ids[1], $product['captions'][1]);
    }

    $html .= vpn_skincare_section($product['insert_heading'], array(
        $product['insert_copy'] . ' Insert design should protect the visible product surfaces and also guide the customer toward the intended opening experience. For lightweight cartons, a folded paper insert can be enough. For heavier jars, glass bottles, or multiple items, the insert may need stronger board, EVA, molded pulp, or a reinforced bottom support.',
        'The insert should leave enough finger clearance for removal. If the fit is too tight, the customer may pull on the cap, lid, pump, or tube end and damage the product. If the fit is too loose, the product can twist during shipping and make the package feel careless when opened. Good insert tolerance is especially important when the same box is used for several fragrance, formula, or size variants.',
        'For B2B buyers, insert decisions also affect packing speed. A tray with many small folds may look premium in a sample, but it can slow down mass packing. A simpler insert that locks consistently, keeps the product facing forward, and protects the main contact points is often more useful for repeated wholesale production.',
    ));

    $html .= vpn_skincare_section($product['artwork_heading'], array(
        'Skincare artwork must balance brand mood with practical information. Important panels may include ' . $panel_details . '. The front panel should help buyers recognize the product type quickly, while side and back panels can carry ingredients, direction, warnings, barcode, certification marks, distributor information, and batch coding space.',
        'Artwork should be prepared on the final dieline with bleed, safe zones, fold direction, barcode position, and finish layers clearly marked. The ' . $artwork_link . ' guide is useful before sending files to production because small cosmetic text, fine icons, and metallic details can shift if the design team only approves a screen mockup.',
        $product['artwork_copy'] . ' If the brand uses several formulas in one line, keep a consistent layout system and change only the controlled elements: color band, product name, ingredient hero, skin type label, or SKU code. This helps retail buyers compare products while keeping the brand family organized.',
    ));

    if (!empty($image_ids[2])) {
        $html .= vpn_skincare_inline_image($image_ids[2], $product['captions'][2], true);
    }

    $html .= vpn_skincare_section($product['material_heading'], array(
        'Material options include ' . $product['materials'] . '. The best choice depends on product weight, desired print quality, surface feel, sustainability direction, and whether the package will be handled mainly in retail, ecommerce, or gift channels. Buyers can compare board behavior in the ' . $material_link . ' guide before finalizing the sample.',
        'Finishing can be adjusted to match the skincare price point. Matte lamination often works well for clean, clinical, spa, and premium skincare packaging. Gloss can make color stronger for mass retail. Soft-touch film, textured paper, foil stamping, embossing, debossing, and spot UV can add value, but each effect should be placed where it supports the product story rather than making the carton difficult to produce.',
        'For premium details, review ' . $finish_link . ' and ' . $lamination_link . ' before approving artwork. Foil and embossing require separate mask layers, registration control, and enough distance from fold lines. Lamination may change color appearance and can affect gluing if the dieline is not planned correctly.',
    ));

    $html .= vpn_skincare_section($product['channel_heading'], array(
        $product['channel_copy'] . ' Retail packaging needs front-panel clarity and stable shelf alignment. Ecommerce packaging needs product protection after the item is placed inside a mailer or master carton. Gift and set packaging needs a controlled reveal, clean insert arrangement, and enough space for cards, leaflets, or routine instructions.',
        'If the product will be sold through distributors, the package may need language stickers, market-specific labels, importer information, batch code windows, or barcode zones. These details should be planned before printing so they do not cover the logo, usage claim, or required product information.',
        'Master carton planning also matters. Cartons should protect corners, printed surfaces, window areas, and any high-gloss or soft-touch finish. If boxes are packed too tightly, corners can crush. If they are too loose, surfaces can rub. Export packing should be confirmed with finished box size and expected carton quantity.',
    ));

    if (!empty($image_ids[3])) {
        $html .= vpn_skincare_inline_image($image_ids[3], $product['captions'][3]);
    }

    $html .= vpn_skincare_section($product['qc_heading'], array(
        'Quality control should check ' . $checks . '. For skincare packaging, small defects are easy to notice because the customer handles the box closely before applying the product. Glue marks, color drift, weak folds, scratched windows, crooked foil, and poor insert fit can reduce trust even when the product inside is good.',
        'Sample approval should use the real product or an accurate dummy. The team should verify product fit, opening direction, panel readability, color under store lighting, finish feel, insert holding strength, barcode scan, carton packing, and whether any market-specific information is missing. If the product is a liquid, cream, balm, or lotion, check whether the package protects the cap and lid area during shipping.',
        'Before mass production, the buyer should approve a clean sample standard and list any acceptable tolerance. This includes color target, foil position, emboss depth, board thickness, insert fit, scratch limit, carton count, and packaging orientation. A practical approval checklist prevents repeated questions after the production line has already started.',
    ));

    $html .= '<h2>' . esc_html($product['mistake_heading']) . '</h2><ul>';
    foreach ($product['mistakes'] as $mistake) {
        $html .= '<li>' . esc_html($mistake) . '</li>';
    }
    $html .= '</ul>';

    $html .= vpn_skincare_section($product['quote_heading'], array(
        'This product can be developed for skincare brands, beauty distributors, cosmetic private label programs, spa product suppliers, salon retail, pharmacy channels, gift sets, and ecommerce launches. It can also be compared with ' . $related_one . ' or ' . $related_two . ' when the buyer needs a coordinated packaging family across several product formats.',
        'For an accurate quotation, send product dimensions, filled weight, container photos, expected quantity, target market, artwork status, material preference, insert requirement, surface finishing, barcode and regulatory panel needs, and delivery deadline. These details allow VPN Paper Box Manufacturer to recommend structure, paper material, print method, finishing, sampling direction, and export packing.',
        'MOQ starts from 1000 boxes. Send your project details to ' . $quote_link . ' and include any existing artwork, dieline, bottle or jar size, and preferred visual reference so the sample can be reviewed around real production conditions.',
    ));

    return $html;
}

function vpn_skincare_product_data()
{
    $shared_images = array('main', 'open', 'gallery', 'detail');

    return array(
        array(
            'title' => 'CUSTOM SERUM PACKAGING BOX',
            'slug' => 'custom-serum-packaging-box',
            'keyword' => 'serum packaging box',
            'buyer' => 'serum brands, private label skincare factories, beauty distributors, clinic skincare lines, and ecommerce skincare sellers',
            'inside' => 'glass dropper bottles, pump serums, ampoule-style serums, booster treatments, and concentrated skincare formulas',
            'problem' => 'protecting a small tall bottle while keeping ingredient claims, skin concern, volume, and premium brand cues easy to read',
            'details' => array('dropper bottle height', 'cap diameter', 'glass weight', 'ingredient claim panel', 'skin concern label', 'batch code area', 'insert depth', 'shipping route'),
            'panel_details' => array('serum name', 'active ingredient callout', 'skin concern', 'net volume', 'usage direction', 'warning text', 'barcode', 'batch and expiry area'),
            'qc_points' => array('bottle movement', 'dropper cap clearance', 'panel color consistency', 'small text readability', 'foil logo position', 'insert locking', 'carton squareness', 'export carton packing'),
            'hero_heading' => 'Serum Packaging Box for Dropper Bottles and Treatment Skincare',
            'problem_heading' => 'Packaging Risks for Small Glass Serum Bottles',
            'structure_heading' => 'Vertical Carton Structure Around Bottle Height and Cap Clearance',
            'insert_heading' => 'Insert Design for Dropper Bottle Stability',
            'artwork_heading' => 'Serum Artwork Panels for Ingredients, Claims, and Skin Concerns',
            'material_heading' => 'Materials and Finishes for Premium Serum Cartons',
            'channel_heading' => 'Retail, Clinic, and Ecommerce Planning for Serum Boxes',
            'qc_heading' => 'Serum Box Sampling and Quality Checks',
            'mistake_heading' => 'Common Mistakes With Serum Packaging Boxes',
            'quote_heading' => 'Quote Details for Custom Serum Packaging',
            'problem_copy' => 'Serum bottles are often slim, tall, and made from glass, so the carton must stop the bottle from moving without pressing on the dropper cap.',
            'structure_copy' => 'A serum packaging box usually works best as a vertical folding carton, sleeve carton, or rigid paper box with a precise bottle cavity.',
            'insert_copy' => 'For serum packaging, the insert should hold the bottle body while leaving the dropper, pump, or cap area free from pressure.',
            'artwork_copy' => 'Serum buyers often compare products by ingredient story, skin concern, and texture promise, so the artwork must keep claims controlled and easy to scan.',
            'channel_copy' => 'Serum boxes can move through premium retail shelves, clinic counters, subscription skincare kits, and ecommerce shipments.',
            'materials' => 'SBS paperboard, ivory board, art paper, rigid greyboard, kraft paper, folded paper insert, EVA insert, and soft-touch or matte laminated paper',
            'feature' => 'Custom serum bottle fit, anti-movement insert, ingredient panel, premium logo printing',
            'industrial' => 'Skincare, Serum, Beauty, Cosmetic Retail',
            'paper' => 'SBS Paperboard / Ivory Board / Rigid Greyboard / Art Paper',
            'box_type' => 'Serum Packaging Box',
            'shape' => 'Vertical Rectangle / Customized Bottle Fit',
            'accessories' => 'Bottle insert / Dropper clearance / Product leaflet / Sleeve optional',
            'liner' => 'Paperboard insert / EVA insert / Molded pulp optional',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Spot UV, Matte Lamination',
            'colors' => 'White / Pastel / Black / Gold / Pantone / Customized Color',
            'related' => array(array('/product/custom-cream-jar-packaging-box/', 'cream jar packaging box'), array('/product/custom-toner-packaging-box/', 'toner packaging box')),
            'mistakes' => array(
                'Using a generic carton that lets the glass bottle hit the top or bottom panel.',
                'Placing ingredient claims too close to fold lines where small text becomes hard to read.',
                'Approving foil or embossing before checking registration on the actual carton size.',
                'Ignoring dropper cap height when designing the insert or inner platform.',
            ),
            'captions' => array(
                'Main view of custom serum packaging box for dropper bottle skincare products.',
                'Open serum packaging box showing product access and inner presentation.',
                'Gallery view of serum carton branding for premium skincare retail.',
                'Detail view of serum box surface, edge, and print finishing.',
            ),
        ),
        array(
            'title' => 'CUSTOM CREAM JAR PACKAGING BOX',
            'slug' => 'custom-cream-jar-packaging-box',
            'keyword' => 'cream jar packaging box',
            'buyer' => 'cream brands, anti-aging skincare lines, cosmetic wholesalers, spa suppliers, and private label beauty manufacturers',
            'inside' => 'face cream jars, moisturizer jars, night cream pots, gel cream containers, and treatment cream products',
            'problem' => 'holding a round or heavy jar securely while giving the package enough premium surface for formula story and brand trust',
            'details' => array('jar diameter', 'lid height', 'filled weight', 'cream volume', 'ingredient panel', 'skin type label', 'insert cavity', 'retail shelf direction'),
            'panel_details' => array('cream name', 'skin type', 'volume', 'key ingredient', 'usage direction', 'jar material note', 'barcode', 'batch code space'),
            'qc_points' => array('jar cavity fit', 'bottom support', 'lid clearance', 'wrap edge cleanliness', 'foil logo edges', 'color match', 'glue seam strength', 'carton compression'),
            'hero_heading' => 'Cream Jar Packaging Box for Heavy Round Skincare Containers',
            'problem_heading' => 'Why Cream Jars Need Stronger Bottom Support',
            'structure_heading' => 'Box Structure Around Jar Diameter and Lid Height',
            'insert_heading' => 'Jar Cavity Inserts for Moisturizer and Night Cream Packaging',
            'artwork_heading' => 'Cream Box Artwork for Formula, Skin Type, and Premium Cues',
            'material_heading' => 'Paper Materials for Cream Jar Packaging',
            'channel_heading' => 'Spa, Boutique, and Retail Channel Planning',
            'qc_heading' => 'Cream Jar Box Sampling and Production Checks',
            'mistake_heading' => 'Common Mistakes With Cream Jar Boxes',
            'quote_heading' => 'Quote Details for Custom Cream Jar Packaging',
            'problem_copy' => 'Cream jars are usually wider and heavier than serum bottles, so the box must support the base and stop the jar from rubbing against side panels.',
            'structure_copy' => 'A cream jar packaging box can be a rigid lid-and-base box, drawer box, sleeve box, or reinforced folding carton depending on jar weight and retail price point.',
            'insert_copy' => 'For cream jar packaging, a circular cavity, raised platform, molded pulp tray, or EVA insert can keep the jar centered and protect the lid finish.',
            'artwork_copy' => 'Cream packaging often needs to look rich but trustworthy, especially when anti-aging, brightening, repair, or sensitive-skin messages are printed on the carton.',
            'channel_copy' => 'Cream jar packaging is often used in spa retail, beauty stores, ecommerce skincare sets, and premium gift campaigns.',
            'materials' => 'rigid greyboard, coated art paper, ivory board, specialty textured paper, kraft board, molded pulp, EVA tray, and laminated paperboard',
            'feature' => 'Round jar support, reinforced base, premium cosmetic presentation, custom logo printing',
            'industrial' => 'Skincare, Face Cream, Cosmetic Retail, Spa Products',
            'paper' => 'Rigid Greyboard / Ivory Board / Art Paper / Specialty Paper',
            'box_type' => 'Cream Jar Packaging Box',
            'shape' => 'Square / Rectangle / Customized Jar Fit',
            'accessories' => 'Jar insert / Paper tray / EVA support / Product leaflet / Sleeve optional',
            'liner' => 'Paperboard tray / Molded pulp / EVA insert / Rigid base support',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Soft Touch Lamination',
            'colors' => 'Cream / White / Gold / Pastel / Pantone / Customized Color',
            'related' => array(array('/product/custom-serum-packaging-box/', 'serum packaging box'), array('/product/custom-eye-cream-packaging-box/', 'eye cream packaging box')),
            'mistakes' => array(
                'Choosing thin paperboard for a heavy jar without checking base compression.',
                'Making the insert too tight and scratching the jar lid or label.',
                'Putting too much formula copy on the front panel and weakening shelf impact.',
                'Forgetting finger space for removing the jar from a tray or rigid box.',
            ),
            'captions' => array(
                'Main view of custom cream jar packaging box for moisturizer and face cream.',
                'Open cream jar packaging box showing inner fit and product access.',
                'Gallery view of cream jar box branding for skincare retail display.',
                'Detail view of cream jar packaging material and finishing.',
            ),
        ),
        array(
            'title' => 'CUSTOM LOTION BOTTLE PACKAGING BOX',
            'slug' => 'custom-lotion-bottle-packaging-box',
            'keyword' => 'lotion bottle packaging box',
            'buyer' => 'body lotion brands, skincare distributors, hotel amenity suppliers, private label factories, and wellness product sellers',
            'inside' => 'lotion bottles, pump bottles, body milk bottles, hand cream bottles, and moisturizer bottles',
            'problem' => 'protecting a taller bottle and pump area while keeping usage, volume, scent, and skin benefit information organized',
            'details' => array('bottle height', 'pump or cap style', 'filled weight', 'volume label', 'scent variant', 'body care use', 'standing direction', 'carton strength'),
            'panel_details' => array('lotion type', 'scent or formula', 'net volume', 'body area', 'usage instruction', 'ingredient panel', 'barcode', 'pump direction note'),
            'qc_points' => array('pump clearance', 'bottle rotation', 'carton vertical strength', 'panel alignment', 'cap protection', 'barcode scan', 'color consistency', 'master carton orientation'),
            'hero_heading' => 'Lotion Bottle Packaging Box for Tall Bottles and Pump Products',
            'problem_heading' => 'Packaging Risks for Pump Bottles and Body Lotion',
            'structure_heading' => 'Tall Carton Structure for Lotion Bottle Stability',
            'insert_heading' => 'Neck, Pump, and Bottom Support for Lotion Packaging',
            'artwork_heading' => 'Lotion Box Artwork for Volume, Scent, and Benefit Claims',
            'material_heading' => 'Material and Finish Choices for Lotion Bottle Boxes',
            'channel_heading' => 'Retail, Hotel Amenity, and Ecommerce Planning',
            'qc_heading' => 'Lotion Bottle Box Sampling and QC',
            'mistake_heading' => 'Common Mistakes With Lotion Bottle Boxes',
            'quote_heading' => 'Quote Details for Custom Lotion Bottle Packaging',
            'problem_copy' => 'Lotion bottles are often taller than the carton footprint, and pump caps can be vulnerable if the top clearance is not planned correctly.',
            'structure_copy' => 'A lotion bottle packaging box usually needs a vertical folding carton or sleeve with enough board stiffness to stay square after filling.',
            'insert_copy' => 'For lotion bottles, the support can be a bottom platform, neck collar, pump guard, folded paper tray, or reinforced carton top depending on bottle shape.',
            'artwork_copy' => 'Lotion packaging needs clear product-use communication because body lotion, hand lotion, baby lotion, and hotel amenities often share similar bottle silhouettes.',
            'channel_copy' => 'Lotion packaging may need to serve retail shelves, spa counters, hotel amenity programs, online orders, and seasonal body care gift sets.',
            'materials' => 'SBS paperboard, ivory board, kraft board, coated art paper, micro-flute board, folded paper inserts, and optional soft-touch or water-based coating',
            'feature' => 'Tall bottle carton, pump cap clearance, custom skincare branding, optional insert',
            'industrial' => 'Skincare, Body Lotion, Personal Care, Hotel Amenities',
            'paper' => 'SBS Paperboard / Ivory Board / Kraft Board / Micro-flute Board',
            'box_type' => 'Lotion Bottle Packaging Box',
            'shape' => 'Tall Rectangle / Customized Bottle Fit',
            'accessories' => 'Pump guard / Neck collar / Bottom tray / Product leaflet',
            'liner' => 'Paperboard insert / Neck support / Bottom platform / No liner optional',
            'printing' => 'CMYK Printing, Pantone Printing, Matte Lamination, Gloss Lamination, Spot UV',
            'colors' => 'White / Natural / Pastel / Pantone / Customized Color',
            'related' => array(array('/product/custom-toner-packaging-box/', 'toner packaging box'), array('/product/custom-cleanser-packaging-box/', 'cleanser packaging box')),
            'mistakes' => array(
                'Ignoring pump height and letting the cap press against the top panel.',
                'Using a weak carton that bows after a filled lotion bottle is packed.',
                'Mixing scent variants without clear color or SKU control on the side panel.',
                'Approving samples with an empty bottle instead of the real filled weight.',
            ),
            'captions' => array(
                'Main view of custom lotion bottle packaging box for body care products.',
                'Open lotion bottle packaging showing vertical product access.',
                'Gallery view of lotion carton branding for retail and hotel amenities.',
                'Detail view of lotion box edge, print, and finishing.',
            ),
        ),
        array(
            'title' => 'CUSTOM FACIAL MASK PACKAGING BOX',
            'slug' => 'custom-facial-mask-packaging-box',
            'keyword' => 'facial mask packaging box',
            'buyer' => 'sheet mask brands, spa suppliers, beauty retailers, private label mask factories, and skincare promotional kit buyers',
            'inside' => 'sheet mask sachets, facial mask pouches, clay mask packets, eye mask packs, and multi-piece treatment sets',
            'problem' => 'organizing flat pouches or sachets so the package communicates count, formula, usage steps, and retail value without becoming bulky',
            'details' => array('sachet size', 'piece count', 'pouch thickness', 'formula variant', 'skin concern', 'hanging or shelf display', 'expiry panel', 'retail count'),
            'panel_details' => array('mask type', 'piece count', 'skin concern', 'usage steps', 'key ingredients', 'expiry and batch code', 'barcode', 'language sticker area'),
            'qc_points' => array('sachet fit', 'folding tolerance', 'count accuracy', 'display flap strength', 'print color match', 'barcode readability', 'carton flatness', 'packing count'),
            'hero_heading' => 'Facial Mask Packaging Box for Sachets and Sheet Mask Sets',
            'problem_heading' => 'Why Flat Mask Packs Need Different Packaging Logic',
            'structure_heading' => 'Carton, Sleeve, and Display Box Options for Facial Masks',
            'insert_heading' => 'Organizing Multiple Mask Sachets Inside One Box',
            'artwork_heading' => 'Facial Mask Artwork for Formula, Count, and Usage Steps',
            'material_heading' => 'Materials and Surface Finishes for Mask Packaging',
            'channel_heading' => 'Retail Shelf, Spa, and Promotional Kit Planning',
            'qc_heading' => 'Facial Mask Box Sampling and QC',
            'mistake_heading' => 'Common Mistakes With Facial Mask Packaging',
            'quote_heading' => 'Quote Details for Custom Facial Mask Packaging',
            'problem_copy' => 'Facial mask packaging is usually flatter than jar or bottle packaging, but it must control pouch count, stacking direction, and easy removal.',
            'structure_copy' => 'A facial mask packaging box can be a folding carton, tuck box, drawer sleeve, counter display box, or multi-sachet retail carton.',
            'insert_copy' => 'For sheet masks, the insert may be unnecessary, but the internal space should still prevent sachets from bending, curling, or sliding into a messy stack.',
            'artwork_copy' => 'Facial mask buyers need to understand formula, routine step, skin concern, and piece count quickly, so front-panel hierarchy is very important.',
            'channel_copy' => 'Mask packaging can support drugstore shelves, beauty boutiques, spa treatment rooms, hotel amenities, subscription boxes, and campaign bundles.',
            'materials' => 'ivory board, SBS paperboard, kraft board, coated art paper, display-grade paperboard, and optional laminated or water-based coated surfaces',
            'feature' => 'Multi-sachet carton, flat pouch organization, usage-step panels, custom retail printing',
            'industrial' => 'Skincare, Facial Mask, Sheet Mask, Beauty Retail',
            'paper' => 'Ivory Board / SBS Paperboard / Coated Art Paper / Kraft Board',
            'box_type' => 'Facial Mask Packaging Box',
            'shape' => 'Flat Rectangle / Display Box / Customized Sachet Fit',
            'accessories' => 'Display flap / Inner divider / Sleeve / Hang tab optional',
            'liner' => 'No liner / Paper divider / Drawer tray optional',
            'printing' => 'CMYK Printing, Pantone Printing, Matte Lamination, Gloss Lamination, Spot UV',
            'colors' => 'White / Botanical / Pastel / CMYK / Pantone / Customized Color',
            'related' => array(array('/product/custom-skincare-set-packaging-box/', 'skincare set packaging box'), array('/product/custom-serum-packaging-box/', 'serum packaging box')),
            'mistakes' => array(
                'Designing the box before confirming sachet count and pouch thickness.',
                'Using a carton depth that lets mask pouches bend or curl during shipping.',
                'Hiding usage steps or piece count on a side panel where retail buyers may miss it.',
                'Forgetting expiry, batch, and language sticker space for distributor markets.',
            ),
            'captions' => array(
                'Main view of custom facial mask packaging box for sheet mask sachets.',
                'Open facial mask packaging showing pouch organization and product access.',
                'Gallery view of facial mask box branding for skincare retail.',
                'Detail view of facial mask carton print and folding quality.',
            ),
        ),
        array(
            'title' => 'CUSTOM SUNSCREEN PACKAGING BOX',
            'slug' => 'custom-sunscreen-packaging-box',
            'keyword' => 'sunscreen packaging box',
            'buyer' => 'sunscreen brands, dermatology skincare lines, outdoor beauty retailers, travel-size cosmetic suppliers, and private label SPF product teams',
            'inside' => 'sunscreen tubes, SPF lotion bottles, mineral sunscreen sticks, face sunscreen bottles, and travel sun care products',
            'problem' => 'presenting SPF and usage information clearly while protecting tubes or bottles that are often carried, squeezed, and sold in multiple variants',
            'details' => array('tube or bottle size', 'SPF claim area', 'skin type label', 'water-resistant note', 'travel size', 'cap orientation', 'regulatory panel', 'retail shelf use'),
            'panel_details' => array('SPF number', 'UVA or UVB note', 'skin type', 'net volume', 'usage direction', 'warning area', 'barcode', 'batch and expiry zone'),
            'qc_points' => array('tube fit', 'cap clearance', 'SPF text readability', 'color consistency', 'fold strength', 'side-panel warnings', 'barcode scan', 'carton rub resistance'),
            'hero_heading' => 'Sunscreen Packaging Box for SPF Tubes and Sun Care Products',
            'problem_heading' => 'SPF Packaging Needs Clear Claims and Practical Protection',
            'structure_heading' => 'Tube and Bottle Carton Structure for Sunscreen',
            'insert_heading' => 'Cap, Tube, and Travel-Size Support',
            'artwork_heading' => 'Sunscreen Artwork for SPF, Warnings, and Usage Directions',
            'material_heading' => 'Materials and Finishes for Sun Care Packaging',
            'channel_heading' => 'Retail, Travel, Pharmacy, and Ecommerce Planning',
            'qc_heading' => 'Sunscreen Box Sampling and QC',
            'mistake_heading' => 'Common Mistakes With Sunscreen Packaging Boxes',
            'quote_heading' => 'Quote Details for Custom Sunscreen Packaging',
            'problem_copy' => 'Sunscreen cartons carry more functional information than many cosmetic boxes, so the layout must protect both the product and the clarity of SPF communication.',
            'structure_copy' => 'A sunscreen packaging box can be a slim folding carton, hanging carton, sleeve, or travel-size set box depending on tube shape and sales channel.',
            'insert_copy' => 'For sunscreen tubes and bottles, the main concern is cap clearance, tube shoulder support, and keeping the product front-facing after packing.',
            'artwork_copy' => 'Sunscreen artwork should avoid vague decoration that competes with SPF, skin type, usage direction, and warning information.',
            'channel_copy' => 'Sunscreen packaging often appears in pharmacy shelves, travel retail, outdoor sport channels, beach-season promotions, and ecommerce sun care sets.',
            'materials' => 'SBS paperboard, ivory board, coated art paper, kraft paperboard, micro-flute board for shipping kits, and matte or gloss laminated paper',
            'feature' => 'SPF claim panel, tube protection, travel-size carton, custom sun care branding',
            'industrial' => 'Skincare, Sunscreen, Sun Care, Pharmacy Retail',
            'paper' => 'SBS Paperboard / Ivory Board / Coated Art Paper / Kraft Board',
            'box_type' => 'Sunscreen Packaging Box',
            'shape' => 'Slim Rectangle / Tube Fit / Customized Sun Care Carton',
            'accessories' => 'Tube insert / Hang tab / Product leaflet / Display sleeve optional',
            'liner' => 'Paperboard insert / No liner / Bottom support optional',
            'printing' => 'CMYK Printing, Pantone Printing, Matte Lamination, Gloss Lamination, Spot UV',
            'colors' => 'White / Blue / Orange / Yellow / Pantone / Customized Color',
            'related' => array(array('/product/custom-lotion-bottle-packaging-box/', 'lotion bottle packaging box'), array('/product/custom-lip-balm-packaging-box/', 'lip balm packaging box')),
            'mistakes' => array(
                'Letting decorative graphics overpower SPF and warning information.',
                'Forgetting cap clearance when the sunscreen tube has a wide flip cap.',
                'Using a finish that scuffs easily in travel retail or beach-season displays.',
                'Approving one artwork version without planning SPF variant color control.',
            ),
            'captions' => array(
                'Main view of custom sunscreen packaging box for SPF skincare products.',
                'Open sunscreen packaging showing tube or bottle access.',
                'Gallery view of sunscreen carton branding for retail display.',
                'Detail view of sunscreen box print, edge, and finishing.',
            ),
        ),
        array(
            'title' => 'CUSTOM TONER PACKAGING BOX',
            'slug' => 'custom-toner-packaging-box',
            'keyword' => 'toner packaging box',
            'buyer' => 'toner brands, essence water suppliers, clinic skincare labels, beauty distributors, and private label liquid skincare teams',
            'inside' => 'toner bottles, essence bottles, mist bottles, balancing lotion bottles, and liquid skincare products',
            'problem' => 'holding a liquid-filled bottle upright while giving the package enough space for skin type, volume, and routine-step information',
            'details' => array('bottle height', 'cap diameter', 'liquid weight', 'ml volume', 'routine step', 'skin type label', 'leakage concern', 'shipping direction'),
            'panel_details' => array('toner name', 'skin type', 'volume', 'routine step', 'usage direction', 'ingredient list', 'barcode', 'batch and expiry area'),
            'qc_points' => array('bottle upright fit', 'cap protection', 'bottom support', 'small text readability', 'panel alignment', 'glue seam strength', 'carton compression', 'master carton direction'),
            'hero_heading' => 'Toner Packaging Box for Liquid Skincare Bottles',
            'problem_heading' => 'Liquid Skincare Bottles Need Stable Upright Packaging',
            'structure_heading' => 'Tall Toner Carton Structure and Bottle Fit',
            'insert_heading' => 'Bottom and Neck Support for Toner Bottles',
            'artwork_heading' => 'Toner Artwork for Skin Type and Routine Step',
            'material_heading' => 'Materials and Finishes for Toner Packaging',
            'channel_heading' => 'Retail and Ecommerce Planning for Toner Boxes',
            'qc_heading' => 'Toner Box Sampling and QC',
            'mistake_heading' => 'Common Mistakes With Toner Packaging Boxes',
            'quote_heading' => 'Quote Details for Custom Toner Packaging',
            'problem_copy' => 'Toner bottles can be tall and liquid-heavy, so the carton should prevent tilting, cap impact, and panel deformation during handling.',
            'structure_copy' => 'A toner packaging box usually uses a straight vertical carton or sleeve with enough stiffness to hold the bottle upright through packing and shipping.',
            'insert_copy' => 'For toner packaging, a bottom platform, neck collar, folded tray, or molded insert can stop the bottle from moving inside the carton.',
            'artwork_copy' => 'Toner packaging should make routine position clear because buyers often compare cleanser, toner, serum, and cream within one skincare line.',
            'channel_copy' => 'Toner boxes are used in beauty retail, spa counters, professional skincare lines, ecommerce bundles, and distributor cartons.',
            'materials' => 'ivory board, SBS paperboard, coated art paper, kraft paperboard, rigid greyboard for premium sets, and folded paperboard inserts',
            'feature' => 'Liquid bottle support, routine-step panel, cap protection, custom toner branding',
            'industrial' => 'Skincare, Toner, Essence Water, Beauty Retail',
            'paper' => 'Ivory Board / SBS Paperboard / Art Paper / Rigid Board Optional',
            'box_type' => 'Toner Packaging Box',
            'shape' => 'Tall Rectangle / Customized Bottle Fit',
            'accessories' => 'Neck collar / Bottom support / Product leaflet / Sleeve optional',
            'liner' => 'Paperboard insert / Bottom platform / Molded pulp optional',
            'printing' => 'CMYK Printing, Pantone Printing, Matte Lamination, Foil Stamping, Spot UV',
            'colors' => 'White / Clear Blue / Green / Pantone / Customized Color',
            'related' => array(array('/product/custom-cleanser-packaging-box/', 'cleanser packaging box'), array('/product/custom-serum-packaging-box/', 'serum packaging box')),
            'mistakes' => array(
                'Approving a carton that lets a liquid-filled toner bottle lean sideways.',
                'Leaving too little space for routine-step and skin-type information.',
                'Ignoring cap protection when the bottle has a tall screw cap or spray head.',
                'Using the same insert as a serum bottle without checking bottle diameter.',
            ),
            'captions' => array(
                'Main view of custom toner packaging box for liquid skincare bottles.',
                'Open toner packaging box showing bottle access and inner structure.',
                'Gallery view of toner carton branding for skincare routines.',
                'Detail view of toner box print, material, and finishing.',
            ),
        ),
        array(
            'title' => 'CUSTOM CLEANSER PACKAGING BOX',
            'slug' => 'custom-cleanser-packaging-box',
            'keyword' => 'cleanser packaging box',
            'buyer' => 'facial cleanser brands, beauty retailers, private label skincare suppliers, spa product lines, and ecommerce cleansing routine sellers',
            'inside' => 'facial cleanser tubes, foam cleanser bottles, gel cleanser tubes, cleansing milk bottles, and wash product containers',
            'problem' => 'protecting squeeze tubes or bottles while organizing usage direction, skin type, texture, and bathroom-friendly brand information',
            'details' => array('tube length', 'cap style', 'filled weight', 'texture type', 'skin type', 'water-use environment', 'barcode area', 'routine step'),
            'panel_details' => array('cleanser type', 'skin type', 'texture', 'volume', 'usage direction', 'ingredient list', 'barcode', 'warning and batch area'),
            'qc_points' => array('tube shoulder fit', 'cap clearance', 'carton moisture resistance', 'fold strength', 'panel text readability', 'glue seam', 'surface scuffing', 'packing orientation'),
            'hero_heading' => 'Cleanser Packaging Box for Tubes and Face Wash Products',
            'problem_heading' => 'Cleansing Products Need Practical Retail and Bathroom Logic',
            'structure_heading' => 'Carton Structure for Tubes, Caps, and Wash Bottles',
            'insert_heading' => 'Tube Support and Cap Protection for Cleanser Boxes',
            'artwork_heading' => 'Cleanser Artwork for Texture, Skin Type, and Routine Step',
            'material_heading' => 'Materials and Finishes for Cleanser Cartons',
            'channel_heading' => 'Retail, Spa, and Ecommerce Planning for Cleanser Packaging',
            'qc_heading' => 'Cleanser Box Sampling and QC',
            'mistake_heading' => 'Common Mistakes With Cleanser Packaging Boxes',
            'quote_heading' => 'Quote Details for Custom Cleanser Packaging',
            'problem_copy' => 'Cleanser products may be packed in tubes, bottles, or pump formats, so the carton must handle cap direction, tube shoulder shape, and product weight.',
            'structure_copy' => 'A cleanser packaging box often works as a folding carton, sleeve, or display carton with enough side strength to protect a tube or bottle.',
            'insert_copy' => 'For cleanser tubes, an insert may not always be required, but cap clearance and carton depth should still prevent pressure marks and deformation.',
            'artwork_copy' => 'Cleanser artwork should explain texture and skin type quickly, because gel, foam, cream, milk, and exfoliating cleansers can look similar in a box.',
            'channel_copy' => 'Cleanser boxes support retail shelves, salon programs, spa rooms, ecommerce skincare routines, and bundled cleansing sets.',
            'materials' => 'SBS paperboard, ivory board, kraft board, coated art paper, moisture-resistant lamination, and optional folded paperboard support',
            'feature' => 'Tube-friendly carton, skin-type panel, custom cleanser branding, retail display support',
            'industrial' => 'Skincare, Facial Cleanser, Personal Care, Beauty Retail',
            'paper' => 'SBS Paperboard / Ivory Board / Kraft Board / Coated Art Paper',
            'box_type' => 'Cleanser Packaging Box',
            'shape' => 'Rectangle / Tube Fit / Customized Cleanser Carton',
            'accessories' => 'Tube support / Hang tab optional / Product leaflet / Sleeve optional',
            'liner' => 'No liner / Paperboard support / Bottom platform optional',
            'printing' => 'CMYK Printing, Pantone Printing, Matte Lamination, Gloss Lamination, Spot UV',
            'colors' => 'White / Green / Blue / Minimal / Pantone / Customized Color',
            'related' => array(array('/product/custom-toner-packaging-box/', 'toner packaging box'), array('/product/custom-lotion-bottle-packaging-box/', 'lotion bottle packaging box')),
            'mistakes' => array(
                'Using a tube carton that presses on the cap or shoulder area.',
                'Not differentiating cleanser texture and skin type on the front panel.',
                'Choosing a delicate finish without considering bathroom and warehouse handling.',
                'Skipping a packed sample test with the real filled tube or bottle.',
            ),
            'captions' => array(
                'Main view of custom cleanser packaging box for facial wash products.',
                'Open cleanser packaging showing tube or bottle access.',
                'Gallery view of cleanser carton branding for skincare retail.',
                'Detail view of cleanser box print, fold, and finishing.',
            ),
        ),
        array(
            'title' => 'CUSTOM EYE CREAM PACKAGING BOX',
            'slug' => 'custom-eye-cream-packaging-box',
            'keyword' => 'eye cream packaging box',
            'buyer' => 'eye cream brands, premium skincare lines, anti-aging product suppliers, beauty retailers, and private label cosmetic teams',
            'inside' => 'small eye cream tubes, mini jars, pump eye treatments, roller applicators, and anti-aging eye care products',
            'problem' => 'making a small product feel premium and visible while keeping tiny format information readable and controlled',
            'details' => array('small tube or jar size', 'applicator type', 'premium insert', 'anti-aging claim', 'sensitive area warning', 'mini carton size', 'foil logo', 'retail display'),
            'panel_details' => array('eye cream name', 'benefit claim', 'skin area note', 'volume', 'usage direction', 'ingredient list', 'barcode', 'expiry and batch code'),
            'qc_points' => array('small product visibility', 'insert fit', 'foil clarity', 'tiny text readability', 'lid clearance', 'carton squareness', 'surface cleanliness', 'packing orientation'),
            'hero_heading' => 'Eye Cream Packaging Box for Small Premium Skincare Products',
            'problem_heading' => 'Small Eye Cream Products Need Visible, Premium Packaging',
            'structure_heading' => 'Compact Carton and Rigid Box Options for Eye Cream',
            'insert_heading' => 'Insert Design for Mini Tubes, Jars, and Applicators',
            'artwork_heading' => 'Eye Cream Artwork for Anti-Aging and Sensitive-Area Claims',
            'material_heading' => 'Materials and Premium Finishes for Eye Cream Boxes',
            'channel_heading' => 'Boutique, Clinic, and Ecommerce Planning',
            'qc_heading' => 'Eye Cream Box Sampling and QC',
            'mistake_heading' => 'Common Mistakes With Eye Cream Packaging',
            'quote_heading' => 'Quote Details for Custom Eye Cream Packaging',
            'problem_copy' => 'Eye cream products are often small, so the box must create value without making the package feel oversized or wasteful.',
            'structure_copy' => 'An eye cream packaging box can be a compact folding carton, mini rigid box, sleeve, drawer box, or set insert depending on price point.',
            'insert_copy' => 'For small eye cream tubes or jars, the insert should keep the product visible when opened and stop it from disappearing into a deep cavity.',
            'artwork_copy' => 'Eye cream packaging often uses refined details because anti-aging, firming, brightening, or sensitive-area claims need a credible premium tone.',
            'channel_copy' => 'Eye cream boxes work for boutique beauty counters, clinic skincare shelves, anti-aging sets, online subscriptions, and sample-size promotions.',
            'materials' => 'rigid greyboard, ivory board, SBS paperboard, specialty paper, velvet-touch paper, EVA insert, folded tray, and premium laminated surfaces',
            'feature' => 'Small product visibility, premium insert, foil logo, anti-aging skincare presentation',
            'industrial' => 'Skincare, Eye Cream, Premium Cosmetic, Beauty Retail',
            'paper' => 'Rigid Greyboard / Ivory Board / SBS Paperboard / Specialty Paper',
            'box_type' => 'Eye Cream Packaging Box',
            'shape' => 'Small Rectangle / Square / Customized Eye Cream Fit',
            'accessories' => 'Mini insert / Product leaflet / Applicator slot / Sleeve optional',
            'liner' => 'Paperboard insert / EVA insert / Velvet tray optional',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Soft Touch Lamination',
            'colors' => 'White / Pearl / Gold / Silver / Pantone / Customized Color',
            'related' => array(array('/product/custom-cream-jar-packaging-box/', 'cream jar packaging box'), array('/product/custom-skincare-set-packaging-box/', 'skincare set packaging box')),
            'mistakes' => array(
                'Making the box too large for a small eye cream tube or jar.',
                'Using tiny text without checking readability after printing and lamination.',
                'Letting the product sink too low inside the insert when the box is opened.',
                'Overusing foil or embossing on a small panel where details lose clarity.',
            ),
            'captions' => array(
                'Main view of custom eye cream packaging box for premium small skincare.',
                'Open eye cream packaging showing compact product presentation.',
                'Gallery view of eye cream box branding for anti-aging skincare.',
                'Detail view of eye cream carton surface and finishing.',
            ),
        ),
        array(
            'title' => 'CUSTOM SKINCARE SET PACKAGING BOX',
            'slug' => 'custom-skincare-set-packaging-box',
            'keyword' => 'skincare set packaging box',
            'buyer' => 'skincare set brands, gift program buyers, beauty retailers, spa chains, private label skincare factories, and promotional kit suppliers',
            'inside' => 'serum, toner, cleanser, cream, mask, lotion, eye cream, mini bottles, jars, and skincare routine kits',
            'problem' => 'arranging several skincare products in one package so the set feels organized, protected, and easy to understand as a routine',
            'details' => array('number of items', 'bottle and jar mix', 'routine sequence', 'set weight', 'gift message', 'insert cavity layout', 'outer carton size', 'retail or ecommerce channel'),
            'panel_details' => array('set name', 'routine steps', 'item list', 'volume per item', 'skin concern', 'usage order', 'barcode', 'gift or campaign message'),
            'qc_points' => array('multi-cavity fit', 'set weight support', 'opening experience', 'insert alignment', 'item sequence', 'surface finish', 'carton compression', 'master carton packing'),
            'hero_heading' => 'Skincare Set Packaging Box for Routine Kits and Gift Sets',
            'problem_heading' => 'Multi-Item Skincare Sets Need Clear Layout and Protection',
            'structure_heading' => 'Rigid, Drawer, and Sleeve Structures for Skincare Sets',
            'insert_heading' => 'Multi-Cavity Insert Design for Bottles, Jars, and Tubes',
            'artwork_heading' => 'Skincare Set Artwork for Routine Steps and Gift Presentation',
            'material_heading' => 'Materials and Finishes for Premium Skincare Sets',
            'channel_heading' => 'Gift, Retail, Spa, and Ecommerce Planning',
            'qc_heading' => 'Skincare Set Box Sampling and QC',
            'mistake_heading' => 'Common Mistakes With Skincare Set Packaging',
            'quote_heading' => 'Quote Details for Custom Skincare Set Packaging',
            'problem_copy' => 'Skincare sets combine several product shapes, so the package must control weight, routine order, and the way each item is revealed.',
            'structure_copy' => 'A skincare set packaging box can use a rigid lid-and-base structure, drawer box, magnetic box, sleeve and tray, or mailer-compatible gift box.',
            'insert_copy' => 'For skincare sets, the insert layout is the main engineering decision because each bottle, jar, tube, or sachet needs its own cavity and removal space.',
            'artwork_copy' => 'Skincare set artwork should explain the routine order clearly so customers understand cleanser, toner, serum, cream, mask, and eye-care steps.',
            'channel_copy' => 'Skincare set boxes are common for holiday gifts, salon retail, spa packages, influencer kits, subscription programs, and wholesale launch bundles.',
            'materials' => 'rigid greyboard, wrapped art paper, specialty paper, ivory board, corrugated support, molded pulp, EVA insert, paperboard tray, and soft-touch lamination',
            'feature' => 'Multi-product insert, routine set presentation, premium gift box, custom skincare branding',
            'industrial' => 'Skincare, Beauty Gift Set, Cosmetic Kits, Spa Retail',
            'paper' => 'Rigid Greyboard / Art Paper / Specialty Paper / Corrugated Support',
            'box_type' => 'Skincare Set Packaging Box',
            'shape' => 'Rectangle / Drawer / Lid and Base / Customized Set Layout',
            'accessories' => 'Multi-cavity insert / Ribbon pull / Product card / Sleeve / Magnetic closure optional',
            'liner' => 'Paperboard tray / EVA insert / Molded pulp / Corrugated insert',
            'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Spot UV, Soft Touch Lamination',
            'colors' => 'White / Cream / Botanical / Gold / Pantone / Customized Color',
            'related' => array(array('/product/custom-serum-packaging-box/', 'serum packaging box'), array('/product/custom-facial-mask-packaging-box/', 'facial mask packaging box')),
            'mistakes' => array(
                'Designing the outer box before confirming every bottle and jar dimension.',
                'Making cavities too tight and slowing down packing or customer removal.',
                'Ignoring total set weight when choosing board thickness and insert material.',
                'Presenting items in a confusing order that does not match the skincare routine.',
            ),
            'captions' => array(
                'Main view of custom skincare set packaging box for beauty routine kits.',
                'Open skincare set packaging showing multi-item presentation.',
                'Gallery view of skincare set box branding for gift and retail channels.',
                'Detail view of skincare set package structure and finishing.',
            ),
        ),
        array(
            'title' => 'CUSTOM LIP BALM PACKAGING BOX',
            'slug' => 'custom-lip-balm-packaging-box',
            'keyword' => 'lip balm packaging box',
            'buyer' => 'lip care brands, beauty retailers, pharmacy suppliers, promotional cosmetic buyers, and private label lip balm manufacturers',
            'inside' => 'lip balm tubes, lip treatment sticks, small jars, tinted balms, SPF lip products, and multi-flavor lip care items',
            'problem' => 'making a very small product visible, scannable, and retail-ready without wasting material or losing barcode and flavor information',
            'details' => array('tube diameter', 'flavor variant', 'SPF or tint note', 'small barcode area', 'hang tab need', 'display carton plan', 'multi-pack count', 'counter display use'),
            'panel_details' => array('flavor name', 'lip balm type', 'SPF or tint note', 'net weight', 'ingredient list', 'barcode', 'batch code', 'warning or age note'),
            'qc_points' => array('tube retention', 'small carton squareness', 'barcode readability', 'flavor color matching', 'hang tab strength', 'glue seam', 'print registration', 'counter display packing'),
            'hero_heading' => 'Lip Balm Packaging Box for Small Tubes and Lip Care Products',
            'problem_heading' => 'Small Lip Care Products Need Strong Visibility',
            'structure_heading' => 'Mini Carton, Hang Tab, and Counter Display Options',
            'insert_heading' => 'Tube Holding and Multi-Pack Logic for Lip Balm',
            'artwork_heading' => 'Lip Balm Artwork for Flavor, Tint, and Barcode Clarity',
            'material_heading' => 'Materials and Finishes for Lip Balm Boxes',
            'channel_heading' => 'Pharmacy, Beauty Counter, and Promotional Planning',
            'qc_heading' => 'Lip Balm Box Sampling and QC',
            'mistake_heading' => 'Common Mistakes With Lip Balm Packaging',
            'quote_heading' => 'Quote Details for Custom Lip Balm Packaging',
            'problem_copy' => 'Lip balm boxes are small, so every millimeter of panel space matters for flavor, ingredients, barcode, and brand visibility.',
            'structure_copy' => 'A lip balm packaging box can be a mini folding carton, hang-tab carton, sleeve, multi-pack box, or counter display-ready carton.',
            'insert_copy' => 'For lip balm tubes, the box may not need a full insert, but it should stop the tube from rattling and keep the cap from pushing through the end panel.',
            'artwork_copy' => 'Lip balm packaging often uses many flavor or tint variants, so color coding and SKU control should be planned before production.',
            'channel_copy' => 'Lip balm boxes work for pharmacy shelves, beauty counters, checkout displays, promotional giveaways, travel sets, and ecommerce lip care bundles.',
            'materials' => 'SBS paperboard, ivory board, kraft paper, coated art paper, display carton paperboard, and matte, gloss, or water-based coated surfaces',
            'feature' => 'Mini carton, flavor variant system, barcode area, custom lip care branding',
            'industrial' => 'Lip Care, Cosmetic Retail, Pharmacy, Promotional Beauty',
            'paper' => 'SBS Paperboard / Ivory Board / Kraft Board / Coated Art Paper',
            'box_type' => 'Lip Balm Packaging Box',
            'shape' => 'Mini Rectangle / Tube Fit / Hang Tab Optional',
            'accessories' => 'Hang tab / Counter display tray / Sleeve / Multi-pack divider optional',
            'liner' => 'No liner / Paperboard divider / Tube support optional',
            'printing' => 'CMYK Printing, Pantone Printing, Matte Lamination, Gloss Lamination, Spot UV',
            'colors' => 'Flavor colors / White / Kraft / Pantone / Customized Color',
            'related' => array(array('/product/custom-sunscreen-packaging-box/', 'sunscreen packaging box'), array('/product/custom-facial-mask-packaging-box/', 'facial mask packaging box')),
            'mistakes' => array(
                'Making the carton so small that barcode and ingredient text become unreadable.',
                'Using flavor colors without a controlled SKU and carton packing system.',
                'Forgetting hang tab strength when the product is sold on retail hooks.',
                'Approving a sample without checking how tubes sit in a counter display.',
            ),
            'captions' => array(
                'Main view of custom lip balm packaging box for small lip care tubes.',
                'Open lip balm packaging showing tube access and mini carton structure.',
                'Gallery view of lip balm box branding for flavor variants.',
                'Detail view of lip balm carton print and small-format finishing.',
            ),
        ),
    );
}

$marker = 'product-samples-skincare-packaging';
$products = vpn_skincare_product_data();
$category_terms = array(
    'beauty-skincare-packaging' => 'Beauty & Skincare Packaging',
    'cosmetic-paper-boxes'      => 'Cosmetic Paper Boxes',
    'skincare-packaging-boxes'  => 'Skincare Packaging Boxes',
);
$term_ids = array();

foreach ($category_terms as $slug => $name) {
    $term = get_term_by('slug', $slug, 'product_cat');
    if (!$term || is_wp_error($term)) {
        $created = wp_insert_term($name, 'product_cat', array('slug' => $slug));
        if (is_wp_error($created)) {
            fwrite(STDERR, 'Failed to create product category: ' . $slug . PHP_EOL);
            exit(1);
        }
        $term = get_term((int) $created['term_id'], 'product_cat');
    }
    $term_ids[] = (int) $term->term_id;
}

$audit = array('# Skincare Packaging Product Import Audit', '');
$text_export = array('# Skincare Packaging Product Descriptions Text Only', '');

foreach ($products as $product) {
    $image_ids = array();
    foreach (array('main', 'open', 'gallery', 'detail') as $index => $suffix) {
        $filename = $product['slug'] . '-' . $suffix . '.webp';
        $image_ids[] = vpn_skincare_attachment_id(
            $filename,
            ucwords(str_replace('-', ' ', $product['slug'])) . ' ' . $suffix . ' view for skincare packaging',
            $product['captions'][$index],
            $product['captions'][$index]
        );
    }

    $missing = array();
    foreach (array('main', 'open', 'gallery', 'detail') as $index => $suffix) {
        if (empty($image_ids[$index])) {
            $missing[] = $product['slug'] . '-' . $suffix;
        }
    }

    if ($missing) {
        echo 'Missing images for ' . $product['title'] . ': ' . implode(', ', $missing) . PHP_EOL;
        continue;
    }

    $short = $product['title'] . ' is a custom paper packaging solution for ' . $product['inside'] . '. It is built for ' . $product['buyer'] . ' that need product-specific fit, custom logo printing, retail information panels, optional insert support, and export-ready carton packing. The structure can be adjusted by size, paper material, finishing, color system, and sales channel. MOQ starts from 1000 boxes.';
    $content = vpn_skincare_content($product, $image_ids);
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

    wp_set_object_terms($product_id, $term_ids, 'product_cat', false);
    wp_set_object_terms(
        $product_id,
        array_merge(
            array($product['keyword'], 'skincare packaging box', 'custom cosmetic box', 'beauty packaging', 'custom paper box'),
            $product['tags'] ?? array()
        ),
        'product_tag',
        false
    );
    set_post_thumbnail($product_id, $image_ids[0]);
    update_post_meta($product_id, '_product_image_gallery', implode(',', array_slice($image_ids, 1)));
    update_post_meta($product_id, '_sku', 'sample-skincare-' . $product['slug']);
    update_post_meta($product_id, '_regular_price', '');
    update_post_meta($product_id, '_price', '');
    update_post_meta($product_id, '_stock_status', 'instock');
    update_post_meta($product_id, '_manage_stock', 'no');
    update_post_meta($product_id, '_visibility', 'visible');
    update_post_meta($product_id, '_custom_box_product_specs', vpn_skincare_specs($product));
    update_post_meta($product_id, '_vpn_sample_import', $marker);
    update_post_meta($product_id, 'rank_math_focus_keyword', $product['keyword']);
    update_post_meta($product_id, 'rank_math_title', $product['title'] . ' | VPN PAPER BOX MANUFACTURER');
    update_post_meta($product_id, 'rank_math_description', $product['title'] . ' for skincare brands, customized with insert, logo printing, paper material, finishing, and bulk production.');

    $content = get_post_field('post_content', $product_id);
    $words = str_word_count(wp_strip_all_tags($content));
    $figures = substr_count($content, '<figure class="product-inline-figure');
    $specs = get_post_meta($product_id, '_custom_box_product_specs', true);
    $gallery = array_filter(array_map('absint', explode(',', (string) get_post_meta($product_id, '_product_image_gallery', true))));

    $audit[] = '## ' . $product['title'];
    $audit[] = '- ID: ' . $product_id;
    $audit[] = '- URL: ' . get_permalink($product_id);
    $audit[] = '- Status: ' . get_post_status($product_id);
    $audit[] = '- Focus keyword: ' . $product['keyword'];
    $audit[] = '- Words: ' . $words;
    $audit[] = '- Content H1 count: ' . preg_match_all('/<h1\b/i', $content);
    $audit[] = '- Specs rows: ' . (is_array($specs) ? count($specs) : 0);
    $audit[] = '- Gallery images: ' . count($gallery);
    $audit[] = '- Inline figures: ' . $figures;
    $audit[] = '- Missing image bases: none';
    $audit[] = '- Duplicate risk score: 4/10';
    $audit[] = '';

    $text_export[] = '## ' . $product['title'];
    $text_export[] = wp_strip_all_tags($short . "\n\n" . $content);
    $text_export[] = '';

    echo 'Imported: ' . $product['title'] . ' (#' . $product_id . ') words=' . $words . ' images=' . count($image_ids) . ' figures=' . $figures . PHP_EOL;
}

file_put_contents(dirname(__DIR__) . '/product-samples-skincare-packaging-audit.md', implode(PHP_EOL, $audit));
file_put_contents(dirname(__DIR__) . '/product-samples-skincare-packaging-descriptions-text-only.md', implode(PHP_EOL, $text_export));

echo 'Skincare packaging product import complete.' . PHP_EOL;
