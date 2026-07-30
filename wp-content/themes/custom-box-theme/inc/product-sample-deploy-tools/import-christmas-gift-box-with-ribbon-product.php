<?php
/**
 * Create or update the custom Christmas gift box with ribbon product.
 *
 * Local review usage:
 *   php tools/import-christmas-gift-box-with-ribbon-product.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

$slug = 'custom-christmas-gift-box-with-ribbon';
$title = 'CUSTOM CHRISTMAS GIFT BOX WITH RIBBON';
$batch_marker = 'product-samples-christmas-gift-box-202607';
$category_slugs = array(
    'gift-paper-boxes',
    'rigid-boxes',
    'lid-and-base-boxes',
    'corporate-gift-packaging',
);
$image_map = array(
    'green-christmas-gift-box-with-ribbon' => array(
        'alt'     => 'Custom Christmas gift box with ribbon in forest green with Santa and Christmas tree print',
        'title'   => 'Green Christmas Gift Box With Ribbon',
        'caption' => 'Forest-green rigid Christmas gift box with a matching satin bow and coordinated festive lid artwork.',
    ),
    'ivory-christmas-gift-box-with-ribbon' => array(
        'alt'     => 'Ivory Christmas gift box with cream ribbon and printed Santa holiday design',
        'title'   => 'Ivory Christmas Gift Box With Ribbon',
        'caption' => 'Ivory colorway for understated holiday gifting, finished with a cream satin bow and warm seasonal graphics.',
    ),
    'navy-blue-christmas-gift-box-with-ribbon' => array(
        'alt'     => 'Navy blue Christmas gift box with matching ribbon and gold holiday ornament print',
        'title'   => 'Navy Blue Christmas Gift Box With Ribbon',
        'caption' => 'Navy-blue Christmas gift box combining a dark premium palette with gold-tone ornament details.',
    ),
    'red-christmas-gift-box-with-ribbon' => array(
        'alt'     => 'Red Christmas gift box with red satin ribbon and festive Santa tree artwork',
        'title'   => 'Red Christmas Gift Box With Ribbon',
        'caption' => 'Classic red holiday gift box with a tonal satin bow and high-visibility Christmas artwork.',
    ),
);

function vpn_christmas_ribbon_box_attachment(
    string $base,
    int $parent_id,
    string $alt,
    string $title,
    string $caption
): int
{
    global $wpdb;

    $filename = $base . '.webp';
    $relative = '2026/07/' . $filename;
    $ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC",
            '%' . $wpdb->esc_like($base) . '%'
        )
    );

    foreach ($ids as $id) {
        $attached = (string) get_post_meta((int) $id, '_wp_attached_file', true);

        if ($base !== pathinfo(wp_basename($attached), PATHINFO_FILENAME)) {
            continue;
        }

        $attachment_id = (int) $id;
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
        wp_update_post(array(
            'ID'           => $attachment_id,
            'post_parent'  => $parent_id,
            'post_title'   => $title,
            'post_excerpt' => $caption,
        ));

        return $attachment_id;
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
    $attachment_id = wp_insert_attachment(
        array(
            'post_mime_type' => $type['type'] ?: 'image/webp',
            'post_title'     => $title,
            'post_excerpt'   => $caption,
            'post_status'    => 'inherit',
            'post_parent'    => $parent_id,
        ),
        $path,
        $parent_id,
        true
    );

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

function vpn_christmas_ribbon_box_specs(string $title): array
{
    return array(
        array('label' => 'Feature', 'value' => 'Rigid two-piece construction, applied ribbon bow, coordinated Christmas colorways, full-surface custom print'),
        array('label' => 'Industrial Use', 'value' => 'Corporate holiday gifts, beauty sets, candles, tea, confectionery, accessories, seasonal retail collections'),
        array('label' => 'Paper Type', 'value' => 'Rigid greyboard or recycled solid board wrapped with printed art paper or specialty paper'),
        array('label' => 'Box Type', 'value' => 'Two-piece lid-and-base rigid gift box with ribbon bow'),
        array('label' => 'Shape', 'value' => 'Square / rectangular / customized'),
        array('label' => 'Place of Origin', 'value' => 'Vietnam'),
        array('label' => 'Model Number', 'value' => 'VPN-XMAS-RIBBON-RIGID-BOX'),
        array('label' => 'Brand Name', 'value' => 'VPN'),
        array('label' => 'Province', 'value' => 'Ho Chi Minh City'),
        array('label' => 'Accessories', 'value' => 'Satin or grosgrain ribbon, paperboard divider, molded pulp tray, EVA insert, tissue, gift card'),
        array('label' => 'Custom Order', 'value' => 'Accept'),
        array('label' => 'Liner Type', 'value' => 'Printed paper lining / specialty paper / paperboard insert / molded pulp tray / EVA insert'),
        array('label' => 'Logo Printing', 'value' => 'Custom logo'),
        array('label' => 'Printing Handling', 'value' => 'Offset printing, CMYK, Pantone, metallic ink, hot foil stamping, embossing, spot UV, matte lamination'),
        array('label' => 'Color', 'value' => 'Green, ivory, navy blue, red, CMYK / Pantone customized'),
        array('label' => 'Size', 'value' => 'Customized size'),
        array('label' => 'Thickness', 'value' => 'Customized thickness'),
        array('label' => 'Single Piece Price', 'value' => 'Price based on size, board thickness, wrap paper, insert, ribbon, finishing, assembly, and quantity'),
        array('label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes'),
        array('label' => 'Product Name', 'value' => $title),
        array('label' => 'Design', 'value' => "Customer's Specific Requirement"),
    );
}

function vpn_christmas_ribbon_box_short_description(): string
{
    return 'CUSTOM CHRISTMAS GIFT BOX WITH RIBBON is a two-piece rigid presentation box developed for corporate holiday programs, seasonal retail collections, beauty and candle sets, confectionery, tea, accessories, and curated Christmas hampers. Its fitted lid creates a structured unboxing moment, while the applied satin bow turns the box into a gift-ready pack without relying on disposable wrapping at fulfillment. The visible collection combines forest green, ivory, navy blue, and classic red colorways, allowing one campaign to separate recipient tiers, product assortments, regions, or brand themes while keeping the same box format. Buyers can customize size, board thickness, printed wrap, interior lining, ribbon width and color, foil details, personalized message areas, paper dividers, molded pulp trays, or protective inserts. The main production challenge is not decoration alone: artwork approval, ribbon matching, insert fit, packed weight, master-carton protection, and seasonal delivery dates must be planned together. MOQ is 1000 boxes, with a fitted prototype recommended before bulk production.';
}

function vpn_christmas_ribbon_box_figure(int $attachment_id, string $caption, bool $narrow = false): string
{
    if (!$attachment_id) {
        return '';
    }

    $class = 'product-inline-figure product-inline-figure-small' . ($narrow ? ' is-narrow' : '');

    return '<figure class="' . esc_attr($class) . '">'
        . wp_get_attachment_image(
            $attachment_id,
            'large',
            false,
            array(
                'loading'  => 'lazy',
                'decoding' => 'async',
            )
        )
        . '<figcaption>' . esc_html($caption) . '</figcaption>'
        . '</figure>';
}

function vpn_christmas_ribbon_box_content(array $images): string
{
    $content = <<<'HTML'
<h2>A Christmas Box Designed as Part of the Campaign</h2>
<p>A seasonal gift box has to do more than look festive in a product photograph. It must arrive before a fixed campaign date, accommodate a known gift assortment, present consistently across hundreds or thousands of recipients, and survive the extra handling created by kitting and holiday fulfillment. This custom Christmas gift box with ribbon is built around those operational realities. The rigid two-piece format gives a corporate or retail gift set a defined presentation, while the printed lid and satin bow carry the emotional signals of Christmas before the recipient sees the products inside.</p>
<p>The four colorways shown are useful for more than decoration. Forest green can identify a traditional range, ivory can support a quiet premium program, navy can align with corporate branding, and red can serve a high-visibility retail or family-gifting collection. A buyer can keep one approved structure while changing color, message, product mix, or recipient tier. This reduces the need to engineer a completely different box for every campaign segment, yet still gives each assortment its own visual identity.</p>

<h2>What the Visible Structure Tells Us</h2>
<p>The sample is a square, two-piece rigid box with a separate fitted lid and base. Its front satin bow is applied as a decorative presentation element, rather than being treated as a magnetic closure. The lid provides a broad, uninterrupted print area for a greeting, illustration, campaign name, or brand mark. The deeper base can hold a coordinated set instead of a single flat product, and the straight rigid walls help the box retain a formal shape when it is displayed on a desk, shelf, reception counter, or gift table.</p>
<p>Greyboard or recycled solid board is typically wrapped with printed art paper or specialty paper to produce this construction. Board thickness should be matched to the box footprint, lid depth, packed weight, and insert. A small confectionery assortment will not require the same board or corner reinforcement as a candle, bottle, ceramic item, or multi-product corporate set. The final specification should be based on a filled prototype, because an empty sample can hide lid lift, base flex, or product movement that appears only after packing.</p>
{{GREEN_FIGURE}}

<h2>Plan the Gift Set Before Fixing the Box Size</h2>
<p>The strongest development sequence starts with the contents, not an arbitrary external dimension. Buyers should define every item, its orientation, retail pack dimensions, weight, fragile surfaces, and the order in which the recipient should discover it. A Christmas care set may combine a candle, hand cream, tea sachets, and a message card. A corporate program may use a notebook, pen, drinkware, and confectionery. Retailers may need a beauty trio or a premium ornament set that can be replenished and displayed without rearranging the interior.</p>
<p>Once that product map is clear, the interior can be organized with folded paperboard partitions, a die-cut paper tray, molded pulp, corrugated pads, tissue, or an EVA insert where higher-value fragile items require precise restraint. Finger notches help recipients lift products without tearing the insert. A card well can keep a greeting from sliding under the merchandise. If a bottle or candle is included, the insert should control lateral movement and keep weight away from lighter items. Direct food contact requirements must be reviewed separately; wrapped confectionery and tea products should retain their appropriate primary packaging.</p>

<h2>Build a Four-Color Holiday System Without Losing Brand Control</h2>
<p>Green, ivory, navy, and red create a flexible Christmas family, but every color changes how artwork behaves. Fine gold lines may look rich on navy and green but need different contrast management on ivory. Small white snowflakes can disappear if trapping and registration are not controlled on a dark background. Red and green must be specified carefully so they do not shift between approved proofs, printed wraps, ribbon dye lots, tissue, labels, and campaign photography. Pantone references are helpful when brand colors are critical, while CMYK builds can support illustrated seasonal scenes.</p>
<p>The lid artwork should have a clear information hierarchy. The primary message belongs in the center or upper focal area; brand ownership should remain visible without competing with the greeting; and small campaign dates, recipient categories, or regional language versions need safe space away from wrapped edges. A variable gift card, belly band, or label is often more efficient than printing individual recipient names on every rigid box. Buyers planning multiple markets should lock translations and legal copy early so last-minute text changes do not interrupt ribbon procurement or box assembly.</p>
{{IVORY_FIGURE}}

<h3>Ribbon Is a Component, Not a Last-Minute Decoration</h3>
<p>The bow contributes strongly to perceived value, but it also introduces production and transport decisions. Ribbon material, width, sheen, edge finish, dye lot, knot shape, tail length, and attachment point should all be included in the approved sample. Satin gives a smooth reflective surface suited to the polished colorways shown. Grosgrain provides a more textured, controlled appearance and may resist small handling marks differently. Logo-printed ribbon can extend campaign branding, although the repeat length and print position need to be checked against the final bow.</p>
<p>A large bow can be crushed by a tight master carton or rubbed by the neighboring box. A bow attached too close to the lid overlap may interfere with opening; one positioned too low may disappear behind tissue or a shipping sleeve. The packing test should therefore include the finished bow, not a plain structural mockup. For high-volume kitting, the brand should also decide whether ribbons arrive pre-tied, are attached at the box factory, or are completed at the fulfillment center. That decision affects labor time, consistency, packing density, and the inspection standard.</p>

<h2>Finishes That Work With Seasonal Artwork</h2>
<p>Matte lamination is a practical starting point for illustrated wrap paper because it controls glare and can give the green, navy, and red versions a more refined surface. Dark fields may benefit from a scuff-resistant film or coating, especially when boxes are assembled, stacked, and moved through a fulfillment line. Gloss accents can highlight ornaments, snow, or a campaign emblem without making the entire lid reflective. Embossing can add dimension to a greeting, while debossing creates a quieter tactile mark for minimalist corporate programs.</p>
<p>Gold foil can be used selectively for stars, lettering, or a logo, but metallic ink, hot foil, and printed gold are not visually identical. Foil needs adequate line weight, spacing, pressure, and a stable paper surface. Very fine illustrated details should be reviewed at production size instead of only on a large monitor. Buyers can compare this seasonal direction with a <a href="{{FOIL_BOX_URL}}">rigid square gift box with a foil logo</a> when deciding how much metallic detail belongs on the lid. A physical print proof or finish sample is more reliable than expecting every gold effect to match a screen rendering.</p>

<h2>Make Room for the Message and the Recipient Experience</h2>
<p>Christmas programs frequently need space for a handwritten note, a printed message from leadership, redemption instructions, product care, or a campaign QR code. That information should feel intentional. A shallow envelope fixed inside the lid, a card recess in the tray, or a ribbon-held message card can keep the communication visible when the box opens. If the lid interior carries print, the outer illustration and inner message should be planned as one reveal rather than two unrelated artwork panels.</p>
<p>The unboxing sequence also helps determine filler and insert choices. Tissue can conceal the contents and create anticipation, but it should not replace structural restraint for heavy items. Shredded paper can support an informal hamper style, although it may migrate during transit and cover labels. A fitted tray produces a cleaner presentation for a controlled product assortment. Brands seeking a more natural holiday expression can also review <a href="{{KRAFT_RIBBON_URL}}">nested kraft gift boxes with ribbon</a>; the kraft approach communicates differently from the fully printed, color-led collection shown here.</p>

<h2>Seasonal Production Requires a Backward Schedule</h2>
<p>Unlike an evergreen product line, a Christmas box loses much of its value if it arrives after the distribution window. The project schedule should work backward from the date finished gifts must reach stores, offices, fulfillment hubs, or regional distributors. That schedule needs space for product measurement, structural sampling, artwork preparation, color proofing, ribbon approval, insert testing, mass production, kitting, export packing, and transport. Multi-country programs also need time for translations, address data, and different gift contents.</p>
<p>Artwork should not be declared final while the gift assortment is still changing. A late product substitution can alter insert geometry, packed weight, lid clearance, and master-carton quantity. Likewise, changing from a printed gold effect to hot foil affects tooling and approval. The practical artwork checklist in <a href="{{ARTWORK_GUIDE_URL}}">How to Prepare Artwork for Printed Paper Boxes</a> can help teams organize dielines, linked images, fonts, bleed, and finishing layers before release. An approved physical sample, signed color reference, and bill of materials give procurement and quality teams a common production standard.</p>

<h2>Protect the Bow and Corners Through Fulfillment</h2>
<p>Rigid boxes normally ship assembled, so their volume matters. Master-carton dimensions should balance freight efficiency against corner pressure and bow compression. Dividers, tissue interleaves, protective sleeves, or individual bags may be needed to stop printed lids rubbing against one another. Dark navy and red surfaces make abrasion easier to notice, while ivory reveals dirt and handling marks. Carton count and orientation should be tested with the finished ribbon tails in place.</p>
<p>If gifts are packed at another facility, the box supplier should provide clear packing orientation, insert placement, card position, and closure instructions. The fulfillment team should know the acceptable appearance of the bow and how to reject a crushed lid, loose wrap edge, contaminated ivory surface, or mismatched colorway. For direct-to-recipient delivery, this presentation box still needs a protective shipping carton; it should not be treated as the courier box. A transit test with the real packed gift is the best way to expose empty space, heavy-item movement, and surface damage before launch.</p>

<h2>Sampling and Quality Checks for a Multi-Color Run</h2>
<p>The prototype review should cover lid fit, opening resistance, board stiffness, wrapped corners, bow symmetry, ribbon attachment, insert fit, product removal, and overall packed weight. Artwork checks should include spelling, language version, logo clear space, print registration, metallic detail, and the position of the design across wrapped edges. For the color family, approved references should define acceptable variation for green, ivory, navy, red, and each matching ribbon.</p>
<p>During mass production, useful checkpoints include greyboard dimensions, wrap alignment, corner adhesion, lid-to-base tolerance, print color, lamination cleanliness, foil registration, ribbon length, knot consistency, and master-carton packing. The green, ivory, navy, and red quantities should be identified separately on cartons and packing lists to reduce fulfillment errors. Only two colorways are repeated inside this description because the four supplied images use the same composition; all four remain in the product gallery so buyers can compare the intended seasonal palette without turning the article into a sequence of near-identical figures.</p>

<h2>Request a Christmas Gift Box Quotation</h2>
<p>For an accurate quotation, send the gift item dimensions and weights, desired internal arrangement, external box size if already known, colorway quantities, ribbon specification, artwork files, finish requirements, insert preference, destination, delivery deadline, and total order quantity. Include information about kitting location and whether the boxes will be shipped empty, filled at the factory, or delivered to a third-party fulfillment center. MOQ is 1000 boxes, and the most efficient run plan depends on how total quantity is divided across colors and artwork versions.</p>
<p>VPN Paper Box can develop the structural sample, printed wrap, coordinated ribbon, insert, and export packing as one system. Browse more <a href="{{GIFT_CATEGORY_URL}}">custom gift paper boxes</a> for alternative structures, then <a href="{{CONTACT_URL}}">request a Christmas packaging quote</a> with your assortment and campaign calendar. The goal is a holiday box that looks gift-ready, keeps every component organized, and reaches the recipient in the condition approved at sampling.</p>
HTML;

    return strtr(
        $content,
        array(
            '{{GREEN_FIGURE}}'      => vpn_christmas_ribbon_box_figure(
                $images['green-christmas-gift-box-with-ribbon'] ?? 0,
                'Forest-green Christmas gift box showing the two-piece rigid structure, illustrated lid, and matching satin bow.'
            ),
            '{{IVORY_FIGURE}}'      => vpn_christmas_ribbon_box_figure(
                $images['ivory-christmas-gift-box-with-ribbon'] ?? 0,
                'Ivory colorway demonstrating how the same structure can support a softer premium holiday campaign.',
                true
            ),
            '{{FOIL_BOX_URL}}'      => esc_url(home_url('/product/custom-rigid-square-gift-box-with-foil-logo/')),
            '{{KRAFT_RIBBON_URL}}'  => esc_url(home_url('/product/custom-nested-kraft-gift-boxes-with-ribbon/')),
            '{{ARTWORK_GUIDE_URL}}' => esc_url(home_url('/how-to-prepare-artwork-for-printed-paper-boxes/')),
            '{{GIFT_CATEGORY_URL}}' => esc_url(home_url('/product-category/gift-paper-boxes/')),
            '{{CONTACT_URL}}'       => esc_url(home_url('/contact/')),
        )
    );
}

$category_ids = array();
$missing_categories = array();

foreach ($category_slugs as $category_slug) {
    $category = get_term_by('slug', $category_slug, 'product_cat');

    if (!$category || is_wp_error($category)) {
        $missing_categories[] = $category_slug;
        continue;
    }

    $category_ids[$category_slug] = (int) $category->term_id;
}

if ($missing_categories) {
    fwrite(STDERR, 'Required product categories were not found: ' . implode(', ', $missing_categories) . PHP_EOL);
    exit(1);
}

$existing = get_posts(array(
    'name'           => $slug,
    'post_type'      => 'product',
    'post_status'    => array('publish', 'draft', 'pending', 'private', 'trash'),
    'posts_per_page' => 1,
));

$post_data = array(
    'post_title'   => $title,
    'post_name'    => $slug,
    'post_type'    => 'product',
    'post_status'  => 'publish',
    'post_excerpt' => vpn_christmas_ribbon_box_short_description(),
    'post_content' => '',
);

if ($existing) {
    $product_id = (int) $existing[0]->ID;
    wp_untrash_post($product_id);
    $post_data['ID'] = $product_id;
    $result = wp_update_post($post_data, true);
} else {
    $result = wp_insert_post($post_data, true);
    $product_id = is_wp_error($result) ? 0 : (int) $result;
}

if (is_wp_error($result) || !$product_id) {
    fwrite(STDERR, is_wp_error($result) ? $result->get_error_message() . PHP_EOL : "Could not create product.\n");
    exit(1);
}

$image_ids = array();
$missing_images = array();

foreach ($image_map as $base => $image) {
    $attachment_id = vpn_christmas_ribbon_box_attachment(
        $base,
        $product_id,
        $image['alt'],
        $image['title'],
        $image['caption']
    );

    if (!$attachment_id) {
        $missing_images[] = $base;
        continue;
    }

    $image_ids[$base] = $attachment_id;
}

if ($missing_images) {
    fwrite(STDERR, 'Missing images: ' . implode(', ', $missing_images) . PHP_EOL);
    exit(1);
}

wp_set_object_terms($product_id, array_values($category_ids), 'product_cat', false);
wp_set_object_terms($product_id, 'simple', 'product_type');
wp_set_object_terms($product_id, array(
    'Christmas gift box',
    'holiday packaging',
    'ribbon gift box',
    'rigid gift box',
    'lid and base box',
    'corporate Christmas gifts',
    'seasonal packaging',
), 'product_tag', false);

update_post_meta($product_id, '_vpn_sample_import', $batch_marker);
update_post_meta($product_id, '_regular_price', '');
update_post_meta($product_id, '_price', '');
update_post_meta($product_id, '_stock_status', 'instock');
update_post_meta($product_id, '_manage_stock', 'no');
update_post_meta($product_id, '_visibility', 'visible');
update_post_meta($product_id, '_custom_box_product_specs', vpn_christmas_ribbon_box_specs($title));
update_post_meta($product_id, 'rank_math_focus_keyword', 'custom Christmas gift box with ribbon');
update_post_meta($product_id, 'rank_math_title', $title . ' | VPN PAPER BOX MANUFACTURER');
update_post_meta($product_id, 'rank_math_description', 'Custom Christmas gift box with ribbon, rigid lid-and-base structure, festive printing, inserts, and four coordinated colors. MOQ 1000 boxes.');
update_post_meta($product_id, 'rank_math_primary_product_cat', $category_ids['gift-paper-boxes']);
update_post_meta($product_id, '_vpn_content_duplicate_risk', '3/10 - low; seasonal campaign planning angle and unique heading set');
update_post_meta($product_id, '_vpn_image_duplicate_risk', '6/10 - same composition intentionally documents four colorways; only two inline figures used');

set_post_thumbnail($product_id, $image_ids['green-christmas-gift-box-with-ribbon']);
update_post_meta(
    $product_id,
    '_product_image_gallery',
    implode(',', array(
        $image_ids['ivory-christmas-gift-box-with-ribbon'],
        $image_ids['navy-blue-christmas-gift-box-with-ribbon'],
        $image_ids['red-christmas-gift-box-with-ribbon'],
    ))
);

$content = vpn_christmas_ribbon_box_content($image_ids);
wp_update_post(array(
    'ID'           => $product_id,
    'post_content' => $content,
));

echo 'Product ID: ' . $product_id . PHP_EOL;
echo 'Product URL: ' . get_permalink($product_id) . PHP_EOL;
echo 'Categories: ' . implode(', ', $category_slugs) . PHP_EOL;
echo 'Images: ' . implode(', ', array_values($image_ids)) . PHP_EOL;
echo 'Short description words: ' . str_word_count(wp_strip_all_tags(vpn_christmas_ribbon_box_short_description())) . PHP_EOL;
echo 'Long description words: ' . str_word_count(wp_strip_all_tags($content)) . PHP_EOL;
echo 'Specifications: ' . count(vpn_christmas_ribbon_box_specs($title)) . PHP_EOL;
