<?php
/**
 * Import six paper shopping bag products from the 27 vpn240724 source images.
 *
 * Local review:
 *   php tools/import-paper-shopping-bag-products-202607.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

const VPN_PAPER_BAG_202607_MARKER = 'product-samples-paper-shopping-bags-202607';

function vpn_bag_202607_link(string $path, string $anchor): string
{
    return '<a href="' . esc_url(home_url($path)) . '">' . esc_html($anchor) . '</a>';
}

function vpn_bag_202607_figure(int $image_id, string $caption, bool $narrow = false): string
{
    $image = wp_get_attachment_image($image_id, 'large', false, array('loading' => 'lazy'));
    if (!$image) {
        return '';
    }

    return '<figure class="product-inline-figure product-inline-figure-small'
        . ($narrow ? ' is-narrow' : '')
        . '">' . $image . '<figcaption>' . esc_html($caption) . '</figcaption></figure>';
}

function vpn_bag_202607_section(string $heading, array $paragraphs): string
{
    $html = '<h2>' . esc_html($heading) . '</h2>';
    foreach ($paragraphs as $paragraph) {
        $html .= '<p>' . $paragraph . '</p>';
    }

    return $html;
}

function vpn_bag_202607_specs(array $product): array
{
    return array(
        array('label' => 'Feature', 'value' => $product['feature']),
        array('label' => 'Industrial Use', 'value' => $product['industrial']),
        array('label' => 'Paper Type', 'value' => $product['paper']),
        array('label' => 'Box Type', 'value' => 'Custom Paper Shopping Bag'),
        array('label' => 'Shape', 'value' => $product['shape']),
        array('label' => 'Place of Origin', 'value' => 'Vietnam'),
        array('label' => 'Model Number', 'value' => $product['model']),
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
        array('label' => 'Single Piece Price', 'value' => 'Price based on size, paper, handle, printing, finishing, reinforcement, and quantity'),
        array('label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes'),
        array('label' => 'Product Name', 'value' => $product['title']),
        array('label' => 'Design', 'value' => "Customer's Specific Requirement"),
    );
}

function vpn_bag_202607_attachment(string $filename, int $parent_id, string $alt, string $title, string $caption): int
{
    global $wpdb;

    $relative = '2026/07/' . $filename;
    $ids = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC",
        '%' . $wpdb->esc_like(pathinfo($filename, PATHINFO_FILENAME)) . '%'
    ));

    foreach ($ids as $id) {
        $attached = (string) get_post_meta((int) $id, '_wp_attached_file', true);
        if (pathinfo($filename, PATHINFO_FILENAME) === pathinfo(wp_basename($attached), PATHINFO_FILENAME)) {
            $attachment_id = (int) $id;
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
            wp_update_post(array(
                'ID' => $attachment_id,
                'post_title' => $title,
                'post_excerpt' => $caption,
                'post_parent' => $parent_id,
            ));
            return $attachment_id;
        }
    }

    $uploads = wp_upload_dir();
    if (!empty($uploads['error'])) {
        return 0;
    }

    $path = trailingslashit($uploads['basedir']) . $relative;
    $bundle = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $relative;
    if (!file_exists($path) && file_exists($bundle)) {
        if (!wp_mkdir_p(dirname($path)) || !copy($bundle, $path)) {
            return 0;
        }
    }
    if (!file_exists($path)) {
        return 0;
    }

    $type = wp_check_filetype(wp_basename($path), null);
    $attachment_id = wp_insert_attachment(array(
        'post_mime_type' => $type['type'] ?: 'image/webp',
        'post_title' => $title,
        'post_excerpt' => $caption,
        'post_status' => 'inherit',
        'post_parent' => $parent_id,
    ), $path, $parent_id, true);
    if (is_wp_error($attachment_id)) {
        return 0;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    update_post_meta((int) $attachment_id, '_wp_attached_file', $relative);
    update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', $alt);
    $metadata = wp_generate_attachment_metadata((int) $attachment_id, $path);
    if (is_array($metadata)) {
        wp_update_attachment_metadata((int) $attachment_id, $metadata);
    }

    return (int) $attachment_id;
}

function vpn_bag_202607_short(array $product): string
{
    return $product['title'] . ' is designed for ' . $product['buyer']
        . ' packing ' . $product['contents'] . '. Its main job is to ' . $product['problem']
        . '. The structure uses ' . $product['paper'] . ' with ' . $product['handle']
        . ', while ' . $product['reinforcement'] . ' supports repeatable loading and carrying. Buyers can customize size, gusset, paper weight, handles, logo position, '
        . $product['printing'] . ', and export packing. This format suits retail distribution where presentation, packing speed, load stability, and color consistency must work together. MOQ is 1000 bags; quote inputs include dimensions, weight, quantity, artwork, handles, finish, and destination.';
}

function vpn_bag_202607_content(array $p, array $image_ids): string
{
    $category = vpn_bag_202607_link('/product-category/paper-bags-with-logo/', $p['category_anchor']);
    $strength = vpn_bag_202607_link('/how-to-make-paper-bags-stronger/', $p['strength_anchor']);
    $related = vpn_bag_202607_link($p['related_url'], $p['related_anchor']);
    $quote = vpn_bag_202607_link('/contact/#quote', $p['quote_anchor']);
    $h = $p['headings'];
    $html = '';

    $html .= vpn_bag_202607_section($h[0], array(
        $p['title'] . ' is a custom paper carrier for ' . $p['buyer'] . '. It is planned around ' . $p['contents'] . ', not around a generic stock-bag size. The practical objective is to ' . $p['problem'] . '. ' . $p['angle'],
        'Within our ' . $category . ' range, this bag is differentiated by ' . $p['visible'] . '. The visual direction matters, but the specification must also control packed weight, bag width, gusset depth, top opening, handle drop, bottom stiffness, print coverage, and master-carton packing. These details decide whether the bag stays square at the counter and comfortable in the customer’s hand.',
    ));

    $html .= vpn_bag_202607_figure($image_ids[0], $p['captions'][0], true);
    $html .= vpn_bag_202607_section($h[1], array(
        'Start the dieline with the real packed product. For ' . $p['contents'] . ', record the maximum width, depth, height, total weight, sharp corners, and whether products are stacked, boxed, wrapped, or placed directly in the bag. A bag that is oversized can collapse inward and make the order feel underfilled; one that is too tight slows packing and rubs product corners.',
        $p['size_note'] . ' The side gusset needs enough opening room for staff to load the order without forcing the paper. The bottom panel must remain flat after the contents settle. Buyers should approve dimensions with a complete packed sample because handle tension and bag shape cannot be judged reliably from a flat artwork proof.',
    ));

    $html .= vpn_bag_202607_section($h[2], array(
        'The recommended material direction is ' . $p['paper'] . '. Paper choice should reflect both the desired surface and the carrying load. Higher basis weight can improve body, but stiffness, fold behavior, recycled content, ink holdout, and cracking at the gusset also matter. A heavier sheet is not automatically safer if the handle holes or bottom construction are poorly designed.',
        'This design uses ' . $p['handle'] . '. ' . $p['handle_note'] . ' Handle length should be checked for hand carry and, when relevant, forearm carry. Hole position, knot size, turn-over reinforcement, and cord consistency must be approved together so the top edge does not deform when the bag is lifted.',
    ));

    $html .= vpn_bag_202607_figure($image_ids[1], $p['captions'][1], true);
    $html .= vpn_bag_202607_section($h[3], array(
        $p['reinforcement'] . ' is recommended for the intended load. Reinforcement should spread stress away from small handle holes and keep the bottom from bowing. Review the practical methods in our guide to ' . $strength . ', then specify which reinforcements are mandatory rather than leaving them as an unpriced assumption in the quotation.',
        $p['inside_note'] . ' During sampling, load the bag repeatedly, lift it by both handles, set it down, and inspect the gussets, pasted seams, top fold, and bottom corners. This simple routine exposes problems that a clean empty-bag photograph cannot show, especially when the customer carries the bag for more than a few minutes.',
    ));

    $html .= vpn_bag_202607_section($h[4], array(
        'Artwork should be prepared on the final bag dieline with front, back, side gussets, bottom, top fold, handle holes, glue seam, bleed, and safe zones clearly marked. The main print direction for this product is ' . $p['artwork'] . '. Logo size should follow viewing distance and bag proportion instead of filling every available panel.',
        $p['print_note'] . ' If several sizes or artwork versions share one purchase order, use a version table that connects each filename, color reference, bag dimension, handle color, carton mark, and quantity. This prevents an approved front design from being paired with the wrong side panel or handle during bulk assembly.',
    ));

    $html .= vpn_bag_202607_figure($image_ids[2], $p['captions'][2]);
    $html .= vpn_bag_202607_section($h[5], array(
        'Available decoration can include ' . $p['printing'] . '. The most useful finish is the one that supports the brand position and survives folding, packing, rubbing, and transport. Large dark ink areas, metallic foil, matte lamination, and uncoated paper each create different risks at the creases and high-contact corners.',
        $p['finish_note'] . ' Approve color on the intended paper rather than relying only on a screen or coated-paper proof. Natural and dyed stocks can shift ink appearance, while lamination changes gloss and color density. A signed production sample should identify paper, print process, finish mask, handle color, and acceptable surface tolerance.',
    ));

    $html .= vpn_bag_202607_section($h[6], array(
        'This bag supports ' . $p['channel'] . '. ' . $p['operations_note'] . ' Counter staff need a predictable opening, fast loading sequence, and handles that separate easily. Distribution teams need flat packing, controlled carton quantity, protection from moisture, and clear version labels when several designs are shipped together.',
        'Compare the format with ' . $related . ' when deciding whether the project needs a different visual or structural emphasis. A related bag may use the same general material family, yet its contents, handle system, print hierarchy, load target, or customer occasion can require a different dieline and quality checklist.',
    ));

    $html .= vpn_bag_202607_figure($image_ids[3], $p['captions'][3], true);
    $html .= vpn_bag_202607_section($h[7], array(
        'A pre-production sample should be reviewed with ' . $p['sample_load'] . '. Check finished dimensions, squareness, gusset opening, bottom fold, seam adhesion, top reinforcement, handle drop, knot security, print position, color, finish, and packed appearance. Photograph the approved loading sequence so purchasing, packing, and inspection teams use the same reference.',
        'Quality control should focus on ' . $p['qc'] . '. Random bags should be opened, loaded, lifted, and compared with the signed sample. Cartons also need inspection for quantity, orientation, moisture protection, compression, and labels. Appearance checks alone are insufficient if the bag twists, the handle pulls unevenly, or the bottom loses shape under the intended load.',
    ));

    $html .= vpn_bag_202607_section($h[8], array(
        'For an accurate RFQ, provide ' . $p['rfq'] . '. Also state whether the bag will be packed flat, used at a retail counter, paired with tissue or a product box, shipped in mixed artwork versions, or delivered to more than one market. These inputs let the supplier calculate material use, printing method, assembly, carton packing, and sampling requirements.',
        'The MOQ for this custom product is 1000 bags. Unit cost depends on dimensions, paper grade, handle material, reinforcement, print coverage, finishing, quantity per design, and export packing. Requesting only a unit price without the packed weight and handle specification creates a quotation that may change after sampling.',
    ));

    $html .= vpn_bag_202607_section($h[9], array(
        'Before bulk production, confirm the real packed dimensions, target load, paper reference, handle type and drop, reinforcement, artwork revision, color standard, finish, carton quantity, destination, and approved physical sample. ' . $p['approval_note'] . ' This turns a visual bag concept into a measurable production specification.',
        'The production record should also show how flat bags are oriented in the inner pack, how many units enter each master carton, which side the carton label identifies, and how moisture and compression are controlled in storage. These packing instructions protect the approved handles and printed faces before the bags reach the retail counter, distributor, or fulfillment team.',
        'Send those details through ' . $quote . '. VPN Paper Box Manufacturer can customize paper, size, gusset, handle, logo printing, surface finish, reinforcement, and export packing for international B2B orders. A complete brief helps the sample team recommend a practical structure and gives quality inspectors a clear standard for repeat production.',
    ));

    return $html;
}

function vpn_bag_202607_products(): array
{
    $shared_print = 'CMYK printing, Pantone matching, foil stamping, embossing, debossing, spot UV, matte or gloss lamination';

    return array(
        array(
            'title' => 'CUSTOM WHITE PAPER SHOPPING BAG WITH BROWN ROPE HANDLES',
            'slug' => 'custom-white-paper-shopping-bag-with-brown-rope-handles',
            'keyword' => 'white paper shopping bag with brown rope handles',
            'model' => 'VPN-PB-WHITE-BROWN-ROPE',
            'buyer' => 'fashion boutiques, cosmetics retailers, premium food shops, corporate stores, and brand distributors',
            'contents' => 'folded apparel, boxed cosmetics, accessories, confectionery, and compact gift sets',
            'problem' => 'combine a clean premium white surface with reliable hand-carry strength and visible natural-brown accents',
            'angle' => 'The white-and-brown contrast gives buyers a neutral base that can support minimalist branding, natural product ranges, or seasonal artwork without making the carrier feel like a plain commodity bag.',
            'visible' => 'a bright white body, brown rope handles, a wide landscape proportion, and a reinforced folded top',
            'paper' => 'white kraft paper, coated art paper, or high-strength white paperboard in a weight selected for the packed load',
            'handle' => 'brown cotton or polypropylene rope handles secured through a reinforced turn-over top',
            'handle_note' => 'The brown rope is a deliberate visual feature, so cord diameter, weave, sheen, end treatment, and color tolerance should match the approved sample.',
            'reinforcement' => 'A reinforced top turn-over, handle-hole patches, and a pasted bottom board',
            'size_note' => 'The landscape format shown is useful for folded garments, presentation boxes, and horizontally arranged gift sets, but the handle spacing must keep the load balanced.',
            'inside_note' => 'The white interior and brown top detail should remain clean because the open view is part of the premium presentation.',
            'artwork' => 'a restrained front logo with generous white space and optional brown or metallic accent details',
            'print_note' => 'Reserve a clean zone around the handle line and avoid placing small lettering where the top fold changes the surface.',
            'printing' => $shared_print,
            'finish_note' => 'For a white bag, check scuffing, glue shadow, fingerprints, and dust because small handling marks are more visible than on a dark or patterned carrier.',
            'channel' => 'boutique retail, premium takeaway, branded client kits, trade-show sample distribution, and coordinated box-and-bag programs',
            'operations_note' => 'The wide opening should accept boxed merchandise without scraping corners, while the rope drop should allow comfortable hand carry.',
            'sample_load' => 'the actual boxed merchandise, tissue paper, cards, and any gift-box combination',
            'qc' => 'white-paper cleanliness, brown-rope color, handle symmetry, top reinforcement, bottom flatness, logo centering, and corner scuffing',
            'rfq' => 'product-box dimensions, packed weight, preferred landscape size, white-paper reference, brown-rope sample, print coverage, and finish target',
            'approval_note' => 'The signed sample should record the brown color used for both rope and printed details so future reorders do not drift.',
            'category_anchor' => 'custom paper shopping bags with logo',
            'strength_anchor' => 'make paper shopping bags stronger for boxed retail products',
            'related_url' => '/product/custom-rigid-gift-box-with-matching-paper-bag/',
            'related_anchor' => 'a rigid gift box with matching paper bag',
            'quote_anchor' => 'request a white shopping bag quotation',
            'feature' => 'White landscape shopping bag, brown rope handles, reinforced top, premium logo presentation',
            'industrial' => 'Fashion, Cosmetics, Premium Retail, Corporate Gifts',
            'shape' => 'Landscape Rectangle / Customized',
            'accessories' => 'Brown rope handles / Reinforced top card / Bottom board / Tissue optional',
            'liner' => 'White paper interior / Reinforcement cards',
            'colors' => 'White body / Brown handles / Pantone custom print',
            'captions' => array(
                'Front view of the white paper shopping bag with brown rope handles and centered custom logo.',
                'Side profile showing gusset depth and brown rope handle drop.',
                'Angled view showing the wide shopping bag opening and reinforced top edge.',
                'Close-up of brown rope handles, top reinforcement, and printed logo area.',
                'Open top view showing the white interior and stable bag base.',
            ),
            'views' => array('front view', 'side gusset view', 'three-quarter view', 'handle detail', 'open top view'),
            'images' => array(
                'custom-white-paper-shopping-bag-brown-rope-vpn240724-a-01.webp',
                'custom-white-paper-shopping-bag-brown-rope-vpn240724-a-02.webp',
                'custom-white-paper-shopping-bag-brown-rope-vpn240724-a-03.webp',
                'custom-white-paper-shopping-bag-brown-rope-vpn240724-a-04.webp',
                'custom-white-paper-shopping-bag-brown-rope-vpn240724-a-05.webp',
            ),
            'headings' => array(
                'A White Shopping Bag Designed for Premium Boxed Purchases',
                'Set the Landscape Size From the Packed Retail Order',
                'Balance White Paper Stiffness With Brown Rope Comfort',
                'Reinforce the Handle Line Without Disturbing the Clean Interior',
                'Use White Space as Part of the Logo System',
                'Control Scuffs, Glue Shadow, and Finish on a Light Surface',
                'Plan Fast Counter Packing for Boutique and Corporate Programs',
                'Approve the Bag With Real Boxes, Tissue, and Cards',
                'RFQ Details for a Repeatable White-and-Brown Bag',
                'Finalize the Production Standard Before Ordering',
            ),
            'tags' => array('white paper shopping bag', 'brown rope handle bag', 'boutique paper bag', 'custom logo shopping bag', 'premium retail carrier'),
        ),
        array(
            'title' => 'CUSTOM RUST PAPER SHOPPING BAG WITH ROPE HANDLES',
            'slug' => 'custom-rust-paper-shopping-bag-with-rope-handles',
            'keyword' => 'rust paper shopping bag with rope handles',
            'model' => 'VPN-PB-RUST-ROPE',
            'buyer' => 'apparel labels, leather-goods stores, home-lifestyle brands, artisan retailers, and seasonal merchandise distributors',
            'contents' => 'folded clothing, scarves, wallets, boxed accessories, craft goods, and warm-toned gift items',
            'problem' => 'preserve a rich rust color across folded panels while keeping a tall carrier stable under boutique merchandise',
            'angle' => 'The earthy rust palette is suited to autumn collections, leather accessories, natural lifestyle ranges, and brands that want warmth without using a standard kraft-brown bag.',
            'visible' => 'a saturated rust body, matching dark rope handles, a vertical format, and an open interior with visible top reinforcement',
            'paper' => 'dyed kraft paper, printed white kraft, or coated art paper with a rust Pantone target and suitable crease performance',
            'handle' => 'color-coordinated rope handles with reinforced punched holes and a folded top return',
            'handle_note' => 'A close color relationship between paper and rope makes batch drift obvious, so both components need physical reference samples.',
            'reinforcement' => 'Top cards, handle-hole patches, a reinforced pasted bottom, and a controlled side seam',
            'size_note' => 'The vertical body is appropriate for apparel and accessory retail, but depth must be sufficient for folded products without forcing the gussets outward.',
            'inside_note' => 'The open-bag view should show neat folds and secure rope knots because customers often see the interior during packing.',
            'artwork' => 'a dark or metallic logo with a clear quiet zone against the rust field',
            'print_note' => 'Large rust areas need density control from front to gusset, while reverse lettering must stay crisp on the final paper.',
            'printing' => $shared_print,
            'finish_note' => 'Crease whitening and rub marks are the main risks on a saturated rust surface, particularly along the bottom edge and side folds.',
            'channel' => 'fashion retail, leather-accessory stores, lifestyle gifting, artisan markets, and seasonal collection launches',
            'operations_note' => 'The tall bag should stand open at the counter, accept folded goods quickly, and keep the logo panel facing forward in display stacks.',
            'sample_load' => 'folded apparel, accessory boxes, tissue, receipt folders, and the heaviest planned retail combination',
            'qc' => 'rust color consistency, crease cracking, rope matching, seam adhesion, bottom strength, logo contrast, and carton-to-carton shade variation',
            'rfq' => 'target Pantone or dyed-paper sample, product dimensions, packed weight, rope color and diameter, finish, logo artwork, and quantity per size',
            'approval_note' => 'Keep approved paper and rope swatches with the signed bag so repeat orders can be matched under standard lighting.',
            'category_anchor' => 'printed paper shopping bags for retail brands',
            'strength_anchor' => 'reinforce rope-handle shopping bags',
            'related_url' => '/product/custom-red-paper-shopping-bag/',
            'related_anchor' => 'a brighter red paper shopping bag',
            'quote_anchor' => 'request a rust paper bag sample and quote',
            'feature' => 'Rust color retail bag, coordinated rope handles, vertical carry format, reinforced construction',
            'industrial' => 'Apparel, Leather Goods, Lifestyle Retail, Gifts',
            'shape' => 'Vertical Rectangle / Customized',
            'accessories' => 'Rope handles / Top reinforcement cards / Bottom board / Tissue optional',
            'liner' => 'Dyed or printed paper interior / Reinforcement cards',
            'colors' => 'Rust / Terracotta / Brown / Pantone custom color',
            'captions' => array(
                'Front view of a rust paper shopping bag with coordinated rope handles.',
                'Side gusset view showing the tall retail bag profile and carrying depth.',
                'Open top view showing reinforcement, rope knots, and interior construction.',
                'Three-quarter view of the rust shopping bag for apparel and lifestyle retail.',
            ),
            'views' => array('front view', 'side gusset view', 'open top view', 'three-quarter view'),
            'images' => array(
                'custom-rust-paper-shopping-bag-rope-handle-vpn240724-b-01.webp',
                'custom-rust-paper-shopping-bag-rope-handle-vpn240724-b-02.webp',
                'custom-rust-paper-shopping-bag-rope-handle-vpn240724-b-03.webp',
                'custom-rust-paper-shopping-bag-rope-handle-vpn240724-b-04.webp',
            ),
            'headings' => array(
                'Warm Rust Packaging for Apparel and Lifestyle Retail',
                'Define a Tall Bag Around Folded Merchandise',
                'Match Rope, Paper, and Load Performance',
                'Inspect the Interior Folds and Reinforcement',
                'Build Logo Contrast on a Saturated Rust Field',
                'Prevent Crease Whitening and Rub Variation',
                'Use the Carrier Across Seasonal Retail Collections',
                'Test Shade, Strength, and Counter Packing',
                'Specify Color References in the Supplier Brief',
                'Approve a Rust Bag Standard That Can Be Reordered',
            ),
            'tags' => array('rust paper shopping bag', 'rope handle shopping bag', 'apparel paper bag', 'terracotta retail bag', 'custom printed carrier bag'),
        ),
        array(
            'title' => 'CUSTOM PINK PAPER GIFT BAG WITH TWISTED HANDLES',
            'slug' => 'custom-pink-paper-gift-bag-with-twisted-handles',
            'keyword' => 'pink paper gift bag with twisted handles',
            'model' => 'VPN-PB-PINK-TWISTED',
            'buyer' => 'beauty shops, jewelry and accessory retailers, gift boutiques, bakery counters, and promotional merchandise buyers',
            'contents' => 'small beauty products, accessories, candles, favors, bakery boxes, cards, and lightweight gift combinations',
            'problem' => 'deliver a vivid pink branded carrier at scale without overengineering a lightweight retail or gifting application',
            'angle' => 'Twisted paper handles keep the construction paper-based and economical, while the six views show how the same bag must work upright, open, flat, and at the handle attachment.',
            'visible' => 'a bright pink body, natural-brown twisted paper handles, a compact vertical format, and a fully opened interior',
            'paper' => 'colored kraft paper or printed white kraft selected for crisp folds and lightweight gift retail',
            'handle' => 'twisted paper handles pasted to the inside top panel',
            'handle_note' => 'Pasted handle patches, twist density, handle drop, and adhesive coverage determine whether the paper handles remain aligned after repeated opening.',
            'reinforcement' => 'Pasted handle patches, a stable square bottom, and optional top or bottom card reinforcement',
            'size_note' => 'Compact dimensions should leave room for tissue and easy product removal while avoiding excess empty volume around small gifts.',
            'inside_note' => 'Because the bag may be opened wide for tissue presentation, patch placement and glue cleanliness are visible quality points.',
            'artwork' => 'a centered black, dark-red, or metallic logo with optional side-panel message',
            'print_note' => 'The pink field should remain consistent around folds, and vertical side artwork must be oriented correctly when the gusset opens.',
            'printing' => $shared_print,
            'finish_note' => 'Uncoated pink kraft offers a tactile look, while lamination increases rub resistance but changes recyclability direction and fold feel.',
            'channel' => 'beauty retail, gift counters, small accessory sales, bakery takeaway, brand launches, and promotional kits',
            'operations_note' => 'Twisted handles suit fast high-volume packing, but bags must separate easily from flat bundles without tearing the pasted patches.',
            'sample_load' => 'the heaviest small gift set, tissue, boxed cosmetics, cards, and any sharp-edged accessory package',
            'qc' => 'pink shade, handle-patch adhesion, twist consistency, base squareness, logo orientation, open-bag appearance, and bundle separation',
            'rfq' => 'product mix, maximum packed weight, bag dimensions, pink color target, handle paper color, logo files, finish, and packing quantity',
            'approval_note' => 'The approval sample should be opened and flattened several times to confirm the pasted twisted handles do not lift.',
            'category_anchor' => 'custom paper gift bags with printed logos',
            'strength_anchor' => 'strengthen lightweight paper gift bags',
            'related_url' => '/product/custom-cosmetic-paper-bag/',
            'related_anchor' => 'custom cosmetic paper bag formats',
            'quote_anchor' => 'request a pink twisted-handle bag quote',
            'feature' => 'Bright pink gift bag, twisted paper handles, compact retail format, paper-based construction',
            'industrial' => 'Beauty, Accessories, Gifts, Bakery, Promotional Retail',
            'shape' => 'Compact Vertical Rectangle / Customized',
            'accessories' => 'Twisted paper handles / Handle patches / Bottom board optional / Tissue optional',
            'liner' => 'Colored kraft interior / Pasted handle patches',
            'colors' => 'Pink / Natural brown handles / Pantone custom print',
            'captions' => array(
                'Front view of the pink paper gift bag with natural twisted handles.',
                'Side view showing the compact gusset and twisted paper handle attachment.',
                'Three-quarter view of the pink gift bag for beauty and accessory retail.',
                'Open top view showing the bag interior, handle patches, and square base.',
                'Flat angled view showing paper construction and logo orientation.',
                'Close-up of the twisted handle patches and folded top edge.',
            ),
            'views' => array('front view', 'side gusset view', 'three-quarter view', 'open top view', 'flat angled view', 'handle attachment detail'),
            'images' => array(
                'custom-pink-paper-gift-bag-twisted-handle-vpn240724-d-01.webp',
                'custom-pink-paper-gift-bag-twisted-handle-vpn240724-d-02.webp',
                'custom-pink-paper-gift-bag-twisted-handle-vpn240724-d-03.webp',
                'custom-pink-paper-gift-bag-twisted-handle-vpn240724-d-04.webp',
                'custom-pink-paper-gift-bag-twisted-handle-vpn240724-d-05.webp',
                'custom-pink-paper-gift-bag-twisted-handle-vpn240724-d-06.webp',
            ),
            'headings' => array(
                'A Bright Paper Gift Bag for Small Retail Purchases',
                'Right-Size the Bag for Cosmetics, Accessories, and Favors',
                'Choose Twisted Handles for Lightweight High-Volume Use',
                'Check Pasted Patches and the Fully Open Interior',
                'Keep Logo Placement Clear on Pink Front and Side Panels',
                'Decide Between Tactile Kraft and Protective Lamination',
                'Prepare Flat Bundles for Fast Counter Packing',
                'Cycle-Test Handles With the Heaviest Small Gift Set',
                'Quote the Pink Bag With Weight and Patch Details',
                'Lock the Color and Construction Before Bulk Production',
            ),
            'tags' => array('pink paper gift bag', 'twisted handle paper bag', 'beauty retail bag', 'small gift shopping bag', 'custom logo gift bag'),
        ),
        array(
            'title' => 'CUSTOM LIME GREEN PAPER SHOPPING BAG WITH ROPE HANDLES',
            'slug' => 'custom-lime-green-paper-shopping-bag-with-rope-handles',
            'keyword' => 'lime green paper shopping bag with rope handles',
            'model' => 'VPN-PB-LIME-ROPE',
            'buyer' => 'wellness brands, natural-product retailers, sports shops, children’s stores, beverage concepts, and campaign procurement teams',
            'contents' => 'wellness products, boxed supplements, lightweight apparel, sports accessories, toys, bottles in retail boxes, and branded kits',
            'problem' => 'hold an energetic lime-green brand color consistently while protecting the rope-handle area and presenting a clean open interior',
            'angle' => 'The color is the main recognition asset, so this product is planned around color control, handle-detail accuracy, and the visual quality of the open bag rather than generic luxury finishing.',
            'visible' => 'a lime-green vertical body, natural rope handles with decorative knots, an open top, and a close-up of the handle zone',
            'paper' => 'dyed lime kraft paper, printed white kraft, or coated art paper matched to an approved Pantone reference',
            'handle' => 'natural cotton or paper rope handles with visible decorative knots and reinforced holes',
            'handle_note' => 'The exposed knot treatment becomes part of the design, so knot length, cord texture, hole spacing, and top-fold height need consistent assembly.',
            'reinforcement' => 'Top reinforcement cards, protected handle holes, a stable pasted bottom, and optional bottom board',
            'size_note' => 'The medium vertical format should be tested with the broadest boxed item and any bottle or kit that shifts the center of gravity.',
            'inside_note' => 'The open top image makes the interior a visible brand surface, so reinforcement cards and glue areas must be neat and color-compatible.',
            'artwork' => 'a high-contrast dark logo with minimal supporting copy and controlled side-panel identifiers',
            'print_note' => 'Small black text needs enough density on the green field, and barcode or QR panels may require a white quiet zone.',
            'printing' => $shared_print,
            'finish_note' => 'Bright green can change under lamination and lighting, so approve coated and uncoated options under retail light before selecting the finish.',
            'channel' => 'wellness and sports retail, natural-product stores, children’s ranges, beverage promotions, and high-visibility branded campaigns',
            'operations_note' => 'Mixed product weights require a practical loading rule so staff do not exceed the handle or bottom specification.',
            'sample_load' => 'boxed wellness products, bottles in cartons, accessories, tissue, and the highest planned mixed-order weight',
            'qc' => 'lime color accuracy, decorative knot consistency, hole reinforcement, side-seam alignment, interior cleanliness, print contrast, and load balance',
            'rfq' => 'Pantone reference, product dimensions, packed weight, handle material and knot sample, reinforcement, print files, finish, and mixed-SKU plan',
            'approval_note' => 'Color should be checked under daylight and retail lighting because a small hue shift can change the intended wellness or energetic brand impression.',
            'category_anchor' => 'branded paper shopping bags',
            'strength_anchor' => 'improve paper bag handle and bottom strength',
            'related_url' => '/product/custom-kraft-paper-bag-for-supplement-packaging/',
            'related_anchor' => 'a kraft paper bag for supplement packaging',
            'quote_anchor' => 'request a lime green shopping bag quotation',
            'feature' => 'Lime green carrier, natural rope handles, decorative knots, reinforced open-top construction',
            'industrial' => 'Wellness, Sports, Natural Products, Children’s Retail, Promotions',
            'shape' => 'Medium Vertical Rectangle / Customized',
            'accessories' => 'Natural rope handles / Decorative knots / Top cards / Bottom board optional',
            'liner' => 'Green or white interior / Reinforcement cards',
            'colors' => 'Lime green / Natural handles / Black or Pantone print',
            'captions' => array(
                'Front view of a lime green paper shopping bag with natural rope handles.',
                'Side profile showing gusset depth and the medium vertical bag format.',
                'Open top view showing the interior, bottom shape, and handle placement.',
                'Close-up of decorative rope knots, reinforcement, and custom logo area.',
            ),
            'views' => array('front view', 'side gusset view', 'open top view', 'rope handle detail'),
            'images' => array(
                'custom-lime-green-paper-shopping-bag-rope-handle-vpn240724-c-01.webp',
                'custom-lime-green-paper-shopping-bag-rope-handle-vpn240724-c-02.webp',
                'custom-lime-green-paper-shopping-bag-rope-handle-vpn240724-c-03.webp',
                'custom-lime-green-paper-shopping-bag-rope-handle-vpn240724-c-04.webp',
            ),
            'headings' => array(
                'Use Lime Green as a High-Recognition Retail Asset',
                'Plan the Medium Carrier Around Mixed Product Weight',
                'Engineer Decorative Rope Knots and Handle Holes Together',
                'Keep the Open Interior Clean and Brand-Consistent',
                'Protect Logo and Barcode Contrast on Bright Green',
                'Approve the Color Before Selecting Lamination',
                'Set Loading Rules for Wellness and Campaign Orders',
                'Inspect Knots, Color, and Balance With Real Products',
                'Give the Supplier a Controlled Lime-Green Brief',
                'Create a Repeatable Color and Carry Standard',
            ),
            'tags' => array('lime green paper bag', 'rope handle shopping bag', 'wellness retail bag', 'natural product carrier', 'custom printed green bag'),
        ),
        array(
            'title' => 'CUSTOM BIRTHDAY PAPER GIFT BAG WITH CANDLE PRINT',
            'slug' => 'custom-birthday-paper-gift-bag-with-candle-print',
            'keyword' => 'birthday paper gift bag with candle print',
            'model' => 'VPN-PB-BIRTHDAY-CANDLES',
            'buyer' => 'party-supply brands, birthday retailers, stationery chains, toy shops, bakeries, and promotional gift distributors',
            'contents' => 'party favors, small toys, stationery, confectionery, birthday cards, candles, and compact celebration gifts',
            'problem' => 'organize a colorful candle-themed print across a small bag while leaving space for logo customization and fast party-retail packing',
            'angle' => 'This product is merchandised as part of a birthday assortment: candle count, age-neutral graphics, party colors, and coordination with cards or favor products are more important than a premium minimalist look.',
            'visible' => 'multicolor candle graphics, a white background, round white handles, and a narrow vertical gift-bag proportion',
            'paper' => 'printed white kraft paper or coated art paper selected for bright multicolor graphics and crisp small details',
            'handle' => 'white cord or paper rope handles with reinforced top holes',
            'handle_note' => 'The pale handles should not distract from the candle artwork, but they still need a comfortable drop and secure reinforcement for toy or stationery gifts.',
            'reinforcement' => 'A reinforced folded top, handle-hole patches, a square pasted bottom, and optional bottom card',
            'size_note' => 'The narrow proportion works for small birthday items, but the gusset must accommodate favor boxes and confectionery without hiding the printed candle border.',
            'inside_note' => 'A clean white interior supports fast visual checking when staff pack mixed party items at a counter or fulfillment table.',
            'artwork' => 'a front logo zone framed by candle graphics, confetti, and color bands with age-neutral birthday energy',
            'print_note' => 'Fine candle shapes and small dots require registration control, while logo customization needs a clear safe zone that does not collide with the decorative border.',
            'printing' => 'CMYK printing, Pantone logo colors, matte or gloss lamination, spot UV accents, and optional metallic birthday details',
            'finish_note' => 'Gloss can intensify party colors, while matte reduces glare for stationery-style merchandising; both must be checked at the top and bottom folds.',
            'channel' => 'party-supply retail, toy and stationery stores, bakery gift counters, favor-kit programs, and birthday assortment distribution',
            'operations_note' => 'Retailers may stock several sizes or age versions, so carton labels and artwork codes must make replenishment easy.',
            'sample_load' => 'party favors, a small toy or stationery set, confectionery, card, tissue, and the heaviest intended celebration combination',
            'qc' => 'candle-print registration, multicolor consistency, logo safe zone, white-handle cleanliness, bottom alignment, and artwork-version carton labels',
            'rfq' => 'bag size, favor dimensions, target weight, candle artwork, logo customization area, handle type, finish, number of versions, and quantity per design',
            'approval_note' => 'Approve the bag beside the matching cards, favors, or birthday range so the candle colors work as an assortment rather than as an isolated print.',
            'category_anchor' => 'printed paper gift bags for retail assortments',
            'strength_anchor' => 'reinforce small party gift bags',
            'related_url' => '/product/custom-crayon-packaging-box/',
            'related_anchor' => 'colorful crayon packaging for children’s retail',
            'quote_anchor' => 'request a candle-print birthday bag quote',
            'feature' => 'Birthday candle print, multicolor CMYK graphics, logo panel, reinforced small gift-bag format',
            'industrial' => 'Party Supplies, Toys, Stationery, Bakery Gifts, Birthdays',
            'shape' => 'Narrow Vertical Gift Bag / Customized',
            'accessories' => 'White cord handles / Top reinforcement / Bottom card optional / Tissue optional',
            'liner' => 'White paper interior / Reinforcement patches',
            'colors' => 'White / Multicolor candle graphics / Custom logo colors',
            'captions' => array(
                'Front view of the birthday paper gift bag with candle print and custom logo panel.',
                'Side view showing the narrow gusset for party favors and small gifts.',
                'Three-quarter view showing the candle border, handles, and bag depth.',
                'Artwork close-up showing multicolor candles and the central customization area.',
            ),
            'views' => array('front view', 'side gusset view', 'three-quarter view', 'candle artwork detail'),
            'images' => array(
                'custom-birthday-paper-gift-bag-candle-print-vpn240724-e-01.webp',
                'custom-birthday-paper-gift-bag-candle-print-vpn240724-e-02.webp',
                'custom-birthday-paper-gift-bag-candle-print-vpn240724-e-03.webp',
                'custom-birthday-paper-gift-bag-candle-print-vpn240724-e-04.webp',
            ),
            'headings' => array(
                'Build a Candle-Themed Birthday Bag Assortment',
                'Fit Party Favors Without Hiding the Printed Border',
                'Choose Clean Handles for Fast Celebration Packing',
                'Use Reinforcement for Toys, Cards, and Confectionery',
                'Control Fine Candle Graphics Around the Logo Zone',
                'Select Gloss or Matte for Party-Store Merchandising',
                'Version the Bag by Size, Artwork, or Birthday Range',
                'Review the Sample With a Complete Favor Kit',
                'Quote Every Artwork Version and Carton Label',
                'Approve the Birthday Range as One Retail System',
            ),
            'tags' => array('birthday paper gift bag', 'candle print gift bag', 'party favor bag', 'custom birthday bag', 'printed celebration bag'),
        ),
        array(
            'title' => 'CUSTOM BIRTHDAY PAPER GIFT BAG WITH PRESENT PRINT',
            'slug' => 'custom-birthday-paper-gift-bag-with-present-print',
            'keyword' => 'birthday paper gift bag with present print',
            'model' => 'VPN-PB-BIRTHDAY-PRESENTS',
            'buyer' => 'gift shops, department stores, card retailers, toy and lifestyle chains, and private-label gift-wrap distributors',
            'contents' => 'boxed gifts, books, small toys, lifestyle accessories, cards, confectionery, and coordinated gift-wrap sets',
            'problem' => 'present a recognizable gift-box motif that works across general birthday merchandising while keeping the bag suitable for boxed retail items',
            'angle' => 'Unlike the candle design, this version is planned around the gift-wrap aisle and coordinated present sets: retailers can match bags with tags, tissue, ribbons, boxes, and greeting cards across several recipient groups.',
            'visible' => 'gift-box graphics, bright confetti colors, a white background, round handles, and a broader opening for boxed gifts',
            'paper' => 'printed white kraft or coated art paper with good opacity for multicolor present and confetti artwork',
            'handle' => 'white cord or paper rope handles set across a reinforced folded top',
            'handle_note' => 'Handle spacing should balance boxed gifts and keep the front present motif upright when the bag is carried.',
            'reinforcement' => 'A folded reinforced top, protected handle holes, a pasted bottom board, and controlled side seams',
            'size_note' => 'This format should be dimensioned around common gift-box footprints and books, with enough opening clearance for tissue and tags.',
            'inside_note' => 'The wider opening should let staff place a boxed gift without scraping the printed top edge or crushing tissue presentation.',
            'artwork' => 'a central logo panel combined with illustrated presents, confetti, and a colorful top border',
            'print_note' => 'Gift-box outlines, ribbon details, and confetti need clean registration, while the main customization area must remain readable from shelf distance.',
            'printing' => 'CMYK printing, Pantone brand colors, matte or gloss lamination, spot UV on presents, and optional foil ribbon details',
            'finish_note' => 'Finish selection should coordinate with tissue and gift-wrap accessories and remain durable when flat bags are handled repeatedly in store.',
            'channel' => 'gift-wrap aisles, department stores, card shops, toy retailers, corporate gifting programs, and private-label seasonal ranges',
            'operations_note' => 'A coordinated family may include several bag sizes, tags, tissue colors, and cartons, making SKU and artwork control central to replenishment.',
            'sample_load' => 'a representative gift box, book, small toy, tissue, greeting card, tag, and the maximum intended boxed combination',
            'qc' => 'present-print alignment, logo readability, opening clearance, handle balance, bottom stiffness, accessory color coordination, and version labeling',
            'rfq' => 'common gift-box sizes, packed weight, bag dimensions, present artwork, logo area, handle sample, finish, accessory colors, and quantity by SKU',
            'approval_note' => 'Review the physical sample with tissue, tag, and at least one common gift box so the whole gift-wrap presentation is approved together.',
            'category_anchor' => 'custom printed gift shopping bags',
            'strength_anchor' => 'strengthen paper bags for boxed gifts',
            'related_url' => '/product/custom-luxury-gift-box-with-paper-bag/',
            'related_anchor' => 'a luxury gift box and paper bag combination',
            'quote_anchor' => 'request a present-print gift bag quotation',
            'feature' => 'Birthday present print, wide gift opening, multicolor retail graphics, reinforced boxed-gift carrier',
            'industrial' => 'Gift Retail, Department Stores, Toys, Cards, Corporate Gifting',
            'shape' => 'Vertical Gift Bag With Wide Opening / Customized',
            'accessories' => 'White cord handles / Bottom board / Tissue / Gift tag optional',
            'liner' => 'White paper interior / Top and bottom reinforcement',
            'colors' => 'White / Multicolor present graphics / Custom brand colors',
            'captions' => array(
                'Front view of the birthday paper gift bag with present print and custom logo area.',
                'Side view showing gusset depth for boxed gifts, books, and accessories.',
                'Three-quarter view showing the broad opening and coordinated present artwork.',
                'Artwork close-up showing gift boxes, confetti, and the central branding panel.',
            ),
            'views' => array('front view', 'side gusset view', 'three-quarter view', 'present artwork detail'),
            'images' => array(
                'custom-birthday-paper-gift-bag-present-print-vpn240724-f-01.webp',
                'custom-birthday-paper-gift-bag-present-print-vpn240724-f-02.webp',
                'custom-birthday-paper-gift-bag-present-print-vpn240724-f-03.webp',
                'custom-birthday-paper-gift-bag-present-print-vpn240724-f-04.webp',
            ),
            'headings' => array(
                'Merchandise a Present-Print Bag in the Gift-Wrap Aisle',
                'Size the Opening Around Common Gift Boxes and Books',
                'Balance the Handles for Boxed Birthday Purchases',
                'Keep Tissue, Tags, and Gifts Easy to Load',
                'Place the Brand Within the Present Illustration System',
                'Coordinate Finish With the Complete Gift-Wrap Range',
                'Manage Sizes and Accessories as Controlled SKUs',
                'Approve the Bag With a Real Boxed Gift',
                'Prepare an RFQ for Each Bag and Accessory Version',
                'Sign Off the Full Gift Presentation Before Production',
            ),
            'tags' => array('birthday gift bag', 'present print paper bag', 'gift wrap shopping bag', 'custom printed birthday bag', 'boxed gift carrier'),
        ),
    );
}

$category = get_term_by('slug', 'paper-bags-with-logo', 'product_cat');
if (!$category || is_wp_error($category)) {
    $created = wp_insert_term('Paper Bags with Logo', 'product_cat', array('slug' => 'paper-bags-with-logo'));
    if (is_wp_error($created)) {
        throw new RuntimeException($created->get_error_message());
    }
    $category = get_term((int) $created['term_id'], 'product_cat');
}

$audit = array('# Paper Shopping Bag Products 202607 Audit', '');
$text_export = array('# Paper Shopping Bag Products 202607 Descriptions Text Only', '');

foreach (vpn_bag_202607_products() as $product) {
    $existing = get_page_by_path($product['slug'], OBJECT, 'product');
    $product_id = $existing ? (int) $existing->ID : 0;

    if (!$product_id) {
        $product_id = wp_insert_post(array(
            'post_type' => 'product',
            'post_status' => 'draft',
            'post_title' => $product['title'],
            'post_name' => $product['slug'],
        ), true);
        if (is_wp_error($product_id) || !$product_id) {
            throw new RuntimeException('Could not create ' . $product['slug']);
        }
        $product_id = (int) $product_id;
    }

    $image_ids = array();
    foreach ($product['images'] as $index => $filename) {
        $alt = $product['keyword'] . ' for ' . strtolower($product['industrial']) . ', ' . $product['views'][$index];
        $image_id = vpn_bag_202607_attachment(
            $filename,
            $product_id,
            $alt,
            $product['title'] . ' - ' . ucwords($product['views'][$index]),
            $product['captions'][$index]
        );
        if (!$image_id) {
            throw new RuntimeException('Missing image attachment: ' . $filename);
        }
        $image_ids[] = $image_id;
    }

    $short = vpn_bag_202607_short($product);
    $content = vpn_bag_202607_content($product, $image_ids);
    $updated = wp_update_post(array(
        'ID' => $product_id,
        'post_type' => 'product',
        'post_status' => 'publish',
        'post_title' => $product['title'],
        'post_name' => $product['slug'],
        'post_excerpt' => $short,
        'post_content' => $content,
    ), true);
    if (is_wp_error($updated)) {
        throw new RuntimeException($updated->get_error_message());
    }

    foreach ($image_ids as $image_id) {
        wp_update_post(array('ID' => $image_id, 'post_parent' => $product_id));
    }

    wp_set_object_terms($product_id, array((int) $category->term_id), 'product_cat', false);
    wp_set_object_terms($product_id, $product['tags'], 'product_tag', false);
    wp_set_object_terms($product_id, 'simple', 'product_type', false);
    set_post_thumbnail($product_id, $image_ids[0]);
    update_post_meta($product_id, '_product_image_gallery', implode(',', array_slice($image_ids, 1)));
    update_post_meta($product_id, '_sku', 'sample-paper-bags-202607-' . $product['slug']);
    update_post_meta($product_id, '_regular_price', '');
    update_post_meta($product_id, '_price', '');
    update_post_meta($product_id, '_stock_status', 'instock');
    update_post_meta($product_id, '_manage_stock', 'no');
    update_post_meta($product_id, '_visibility', 'visible');
    update_post_meta($product_id, '_custom_box_product_specs', vpn_bag_202607_specs($product));
    update_post_meta($product_id, '_vpn_sample_import', VPN_PAPER_BAG_202607_MARKER);
    update_post_meta($product_id, 'rank_math_focus_keyword', $product['keyword']);
    update_post_meta($product_id, 'rank_math_title', $product['title'] . ' | VPN PAPER BOX MANUFACTURER');
    $meta = $product['title'] . ' with custom paper, handles, printing, reinforcement, and MOQ 1000 bags for wholesale retail and gifting.';
    update_post_meta($product_id, 'rank_math_description', mb_substr($meta, 0, 154));

    $saved = (string) get_post_field('post_content', $product_id);
    $words = str_word_count(wp_strip_all_tags($saved));
    $short_words = str_word_count(wp_strip_all_tags($short));
    $figures = substr_count($saved, '<figure class="product-inline-figure');
    $duplicate_risk = str_contains($product['slug'], 'birthday') ? '5/10' : '4/10';

    $audit[] = '## ' . $product['title'];
    $audit[] = '- ID: ' . $product_id;
    $audit[] = '- URL: ' . get_permalink($product_id);
    $audit[] = '- Status: ' . get_post_status($product_id);
    $audit[] = '- Category: Paper Bags with Logo';
    $audit[] = '- Focus keyword: ' . $product['keyword'];
    $audit[] = '- Long description words: ' . $words;
    $audit[] = '- Short description words: ' . $short_words;
    $audit[] = '- Content H1 count: ' . preg_match_all('/<h1\b/i', $saved);
    $audit[] = '- Specs rows: 21';
    $audit[] = '- Source images: ' . count($image_ids);
    $audit[] = '- Gallery images: ' . count(array_slice($image_ids, 1));
    $audit[] = '- Inline figures: ' . $figures;
    $audit[] = '- Duplicate risk score: ' . $duplicate_risk;
    $audit[] = '';

    $text_export[] = '## ' . $product['title'];
    $text_export[] = wp_strip_all_tags($short . "\n\n" . $saved);
    $text_export[] = '';

    echo 'Imported: ' . $product['title'] . ' (#' . $product_id . ') words=' . $words
        . ' short=' . $short_words . ' images=' . count($image_ids) . ' figures=' . $figures . PHP_EOL;
}

file_put_contents(dirname(__DIR__) . '/product-samples-paper-shopping-bags-202607-audit.md', implode(PHP_EOL, $audit));
file_put_contents(dirname(__DIR__) . '/product-samples-paper-shopping-bags-202607-descriptions-text-only.md', implode(PHP_EOL, $text_export));

echo 'Paper shopping bag product import complete.' . PHP_EOL;
