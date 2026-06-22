<?php
/**
 * Import three candle packaging products from uploaded Media Library images.
 *
 * Run:
 *   php tools/import-candle-packaging-products.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

function vpn_candle_link($url, $anchor)
{
    return '<a href="' . esc_url(home_url($url)) . '">' . esc_html($anchor) . '</a>';
}

function vpn_candle_attachment_id($filename, $alt, $title)
{
    $attached_file = '2026/06/' . $filename;
    $ids = get_posts(array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_wp_attached_file',
        'meta_value'     => $attached_file,
    ));

    if (!$ids) {
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $ids = get_posts(array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'name'           => sanitize_title($base),
        ));
    }

    if (!$ids) {
        return 0;
    }

    $attachment_id = (int) $ids[0];
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
    wp_update_post(array(
        'ID'         => $attachment_id,
        'post_title' => $title,
    ));

    return $attachment_id;
}

function vpn_candle_specs($product)
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

function vpn_candle_inline_image($image_id, $caption, $narrow = false)
{
    $image = wp_get_attachment_image($image_id, 'large', false, array('loading' => 'lazy'));
    if (!$image) {
        return '';
    }

    return '<figure class="product-inline-figure product-inline-figure-small' . ($narrow ? ' is-narrow' : '') . '">' .
        $image . '<figcaption>' . esc_html($caption) . '</figcaption></figure>';
}

function vpn_candle_section($heading, $paragraphs)
{
    $html = '<h2>' . esc_html($heading) . '</h2>';
    foreach ($paragraphs as $paragraph) {
        $html .= '<p>' . $paragraph . '</p>';
    }

    return $html;
}

function vpn_candle_content($key, $image_ids)
{
    $cat_link = vpn_candle_link('/product-category/candle-packaging-boxes/', 'candle packaging boxes');
    $material_link = vpn_candle_link('/paper-materials-for-custom-paper-boxes/', 'paper materials for custom paper boxes');
    $finish_link = vpn_candle_link('/matte-vs-gloss-lamination-for-packaging/', 'matte vs gloss lamination for packaging');
    $quote_link = vpn_candle_link('/contact/#quote', 'request a candle packaging quotation');
    $mailer_link = vpn_candle_link('/product/kraft-corrugated-mailer-box/', 'kraft corrugated mailer box');
    $gift_link = vpn_candle_link('/product/custom-rigid-gift-box/', 'custom rigid gift box');
    $window_link = vpn_candle_link('/product/custom-mug-packaging-box-with-window/', 'window packaging for fragile lifestyle products');

    if ('mailer' === $key) {
        $html = vpn_candle_section('Candle Shipping Mailer Box Built Around Breakage Risk', array(
            'A custom candle shipping mailer box with corrugated insert is designed for candle brands that sell glass jar candles, ceramic candle cups, soy wax candles, fragrance gift items and subscription boxes through e-commerce. The box is not only a brown shipping container. It is the first protective layer that decides whether the candle arrives without cracked glass, dented lids, wax scuffs, label scratches or a poor opening experience.',
            'This product belongs in our ' . $cat_link . ' range, but its buyer intent is closer to fulfillment protection than boutique shelf display. A candle jar is dense, smooth and easy to damage when it moves inside a carton. The corrugated insert shown in these images creates a defined cavity so the jar stays centered while the outer mailer absorbs compression and rubbing during delivery.',
        ));
        $html .= vpn_candle_inline_image($image_ids[1], 'Corrugated insert detail for holding a candle jar during courier shipping.', true);
        $html .= vpn_candle_section('Why Candle E-commerce Needs a Different Box Than Retail', array(
            'Retail candle packaging can focus heavily on shelf color, fragrance story and surface finish. E-commerce candle packaging must start with the shipping route. A single jar may pass through warehouse picking, courier sorting, conveyor drops, van delivery and customer handling before it is opened. If the candle can rotate, hit a corner or press directly against the wall, the brand risks breakage and replacement cost.',
            'The mailer structure also affects packing speed. Fulfillment teams need a carton that opens quickly, receives the jar and insert without forcing, closes securely, and fits into master cartons or shipping labels without extra tape. For subscription candle programs, consistent dimensions are important because postal costs and warehouse slots are often calculated around carton size.',
        ));
        $html .= vpn_candle_section('Corrugated Insert Logic for Glass Candle Jars', array(
            'The insert should be designed around the real candle diameter, jar height, lid shape and label position. A shallow cradle may look neat in a photo but still allow the jar to jump during transit. A cavity that is too tight can scratch labels or make packing slow. The right solution keeps the candle stable while leaving enough finger space for the customer to lift it naturally.',
            'Corrugated inserts can be made as folded sleeves, cross partitions, raised platforms, ring supports or multi-panel cushions. For heavier glass jars, the insert should prevent bottom impact and side contact at the same time. If the candle uses a metal lid, the lid edge should not rub against the printed inner wall. These small clearance details decide whether the package feels engineered or improvised.',
        ));
        $html .= vpn_candle_inline_image($image_ids[2], 'Angled view showing the candle mailer structure and protective inner support.');
        $html .= vpn_candle_section('Material Choices for Protective Candle Mailers', array(
            'Common materials include kraft corrugated board, white corrugated board, E-flute, B-flute, micro-flute board, recycled kraft liner and coated paper liner for higher print quality. Buyers can compare board stiffness and surface choices in our ' . $material_link . ' guide. The final material should reflect jar weight, shipping distance, fragrance brand position and whether the mailer is the only outer package.',
            'A natural kraft outside can fit handmade, soy, aromatherapy and eco-positioned candle brands. A white printed liner can support premium fragrance branding. For gift subscription programs, inside printing can add a stronger unboxing moment without changing the protective structure. Lamination is not always required on a shipping mailer, but water resistance, rub resistance and ink coverage should be checked when the box travels through mixed courier conditions.',
        ));
        $html .= vpn_candle_section('Printing Space: Keep the Outside Useful, Make the Inside Memorable', array(
            'The outside of a candle shipping mailer usually needs a logo, shipping label zone, barcode, carton marks and maybe a small fragrance or collection identifier. Overdecorating the outer panel can interfere with labels or increase cost without improving delivery performance. The inside gives more room for brand voice: scent story, care instructions, QR code, thank-you message or reorder prompt.',
            'For candle brands selling many scents, artwork can be separated into a shared structural dieline and replaceable labels or sleeve graphics. This keeps the box economical across lavender, vanilla, sandalwood, citrus or holiday editions. Pantone notes, black ink density, logo placement and small text should be approved on the actual corrugated surface, not only on a smooth art-paper proof.',
        ));
        $html .= vpn_candle_inline_image($image_ids[3], 'Open candle shipping mailer showing how the insert and jar presentation work together.', true);
        $html .= vpn_candle_section('Sampling and Drop-Handling Checks Before Bulk Orders', array(
            'A candle mailer sample should be tested with the real filled jar, not an empty container. The team should shake the box, tip it, press the corners, stack several units and check whether the insert shifts after repeated handling. For export or marketplace fulfillment, buyers may request drop testing, compression review or master carton packing simulation.',
            'Quality control should include flute direction, board thickness, die-cut accuracy, insert folding tolerance, closure security, glue cleanliness, print registration, lid clearance and final packed appearance. If the candle ships with matches, cards, dust covers or small accessories, each extra item needs its own position so it does not scratch the jar or block closure.',
        ));
        $html .= vpn_candle_section('Planning the Dieline Around Fulfillment Reality', array(
            'The dieline should be planned around how the candle is packed, not only around the candle diameter. A fulfillment worker needs to place the insert quickly, drop the jar into the cavity, add any card or tissue, close the mailer and apply a label without fighting the structure. If the insert has too many folds, the packaging may look clever in a sample room but slow down daily packing.',
            'Mailer locking tabs, dust flaps and tuck panels should also be checked with the final board thickness. A tab that locks well on thin sample paper may feel too tight after the board is upgraded. A loose tab may open during handling. These tolerances are small, but they decide whether a 1000-box order feels easy to use or frustrating for the warehouse team.',
            'For candle subscription programs, the same outer mailer may need to support several scent variants. In that case, the dieline can stay fixed while the printed insert, sticker, scent card or inside message changes. This keeps tooling cost under control and lets the buyer launch seasonal candles without redesigning the entire package each time.',
        ));
        $html .= vpn_candle_section('Brand Experience After the Shipping Label Is Removed', array(
            'A candle mailer often reaches the customer with a shipping label, courier marks and handling wear on the outside. The inside panel is therefore valuable brand space. Many candle brands use the inner lid for a short scent ritual, candle care instructions, reorder QR code, social media prompt or sustainability message. The goal is to turn a protective mailer into a quiet unboxing moment.',
            'The message should remain practical. Candle customers may need burn instructions, wick trimming notes, first-burn guidance, allergy or safety reminders and disposal information. Placing these details inside the box can reduce the need for extra leaflets while keeping the outer package clean. For premium fragrance products, a small insert card can be held in a slot so it does not rub against the glass jar.',
            'If the mailer is printed inside and outside, buyers should ask the factory to confirm ink coverage, drying time and any odor concern. Candle packaging should not introduce a paper or ink smell that conflicts with the fragrance experience. A sample review after several days of packing is more useful than judging the package immediately after printing.',
        ));
        $html .= vpn_candle_section('Common Buyer Mistakes With Candle Shipping Boxes', array(
            'One common mistake is choosing the smallest possible mailer to reduce freight cost. A tight carton can transfer impact directly to the glass, especially when the candle has a raised lid or thick label. Saving a few millimeters is only useful when the insert still creates a protective buffer. The packed candle should have controlled space, not uncontrolled emptiness or harmful pressure.',
            'Another mistake is approving the box without checking how the shipping label will sit on the top panel. If the logo, QR code or warning mark is placed where the courier label must go, the outside design loses value. A practical e-commerce mailer reserves a clean label zone and puts the brand message where it will still be visible after dispatch.',
            'Buyers should also avoid judging corrugated color from a digital mockup. Kraft liner, white liner and recycled board can all shift printed color. If the brand uses soft fragrance tones, request a printed proof on the actual board. This is especially important for subscription brands that need the same packaging look across repeat monthly shipments.',
        ));
        $html .= vpn_candle_section('Best Buyers for This Candle Mailer Format', array(
            'This structure fits direct-to-consumer candle brands, subscription candle boxes, small-batch fragrance studios, private label home fragrance suppliers, wellness gift sellers and online retailers that need packaging from 1000 boxes upward. It can also be used as an inner protective pack inside a larger gift set or seasonal hamper.',
            'If your project needs a premium shelf-ready rigid package, compare this mailer with a ' . $gift_link . '. If your main concern is standard e-commerce protection across many products, a ' . $mailer_link . ' may also be useful. Send candle diameter, jar height, packed weight, shipping market, artwork direction and order quantity to ' . $quote_link . ' so we can recommend the correct board and insert before sampling.',
        ));
        return $html;
    }

    if ('two_piece' === $key) {
        $html = vpn_candle_section('Two-Piece Candle Gift Box for a Slower, More Premium Reveal', array(
            'A custom two-piece candle gift box with lid and base is made for candle products that need more ceremony than a simple folding carton. The separate lid creates a clean opening moment, while the base can hold a jar candle, tin candle, ceramic vessel, fragrance set or limited-edition home decor product. For premium candle brands, the package should make the customer pause before the candle is even lit.',
            'This product sits in the ' . $cat_link . ' category, but the core angle is gifting and perceived value. Candle buyers often choose products by scent, vessel design and mood. A lid-and-base gift box helps the packaging become part of that mood: calm, warm, tactile, and ready for retail shelves, holiday sets, hotel amenities, wedding favors or boutique gift programs.',
        ));
        $html .= vpn_candle_inline_image($image_ids[1], 'Front view of a two-piece candle gift box with lid and base structure.', true);
        $html .= vpn_candle_section('Lid and Base Tolerance Matters More Than It Looks', array(
            'The most important technical detail in a two-piece candle box is the fit between the lid and the base. If the lid is too loose, the box feels cheap and may open during handling. If it is too tight, the customer has to pull hard and the unboxing feels awkward. The tolerance must account for board thickness, wrap paper, lamination, humidity and the final candle weight.',
            'A good sample should be tested by opening and closing the box repeatedly with the real candle inside. The lid should lift smoothly, the base should remain stable, and the candle should not rise with the lid. For heavier glass jars, the base walls may need reinforcement or an insert that spreads weight evenly across the bottom panel.',
        ));
        $html .= vpn_candle_section('Insert Design for Candle Gifts and Fragile Vessels', array(
            'The insert can be simple or highly structured depending on the product. A single candle may use a paperboard ring, EVA insert, molded pulp tray, foam base or folded collar. A candle gift set may need separate cavities for jars, fragrance cards, wick trimmers, matches, sample tins or care leaflets. The goal is to hold each item in a deliberate position instead of letting accessories float under the lid.',
            'For scented candle collections, the insert can support storytelling. A center candle cavity can be paired with a side card slot, a small accessory well or an inner-lid message. The box then feels curated, not merely packed. This is different from a shipping mailer, where the insert is mostly about shock control.',
        ));
        $html .= vpn_candle_inline_image($image_ids[2], 'Detail view showing candle gift box material, lid edge and product fit.');
        $html .= vpn_candle_section('Material and Finish: Let the Candle Brand Feel Intentional', array(
            'Material options include rigid greyboard with printed art paper wrap, specialty textured paper, coated paper, kraft paper, dyed paper and laminated paperboard for lighter versions. Buyers can review ' . $material_link . ' when deciding whether the project needs a rigid gift feel or a more economical paperboard construction. The choice should follow candle price point, jar weight and retail environment.',
            'Finishing should support the scent and brand mood. Matte lamination can work for wellness, minimal, botanical or luxury fragrance lines. Gloss can be useful for brighter seasonal packaging. Foil stamping, debossing, embossing, spot UV, belly bands, ribbon pulls and textured paper can add identity, but too many effects can make the candle box feel less refined. The ' . $finish_link . ' guide is useful when comparing surface direction before approving samples.',
        ));
        $html .= vpn_candle_section('Artwork Hierarchy for Premium Candle Boxes', array(
            'A candle gift box usually needs less front-panel information than a technical product. The main brand, scent name, collection name and net weight can stay clean, while burn time, ingredients, warning text, barcode and importer information can move to the base, back label or inner card. This keeps the gift presentation calm while still meeting retail information needs.',
            'For brands with several scents, color coding should be controlled carefully. A lavender scent, amber scent and winter candle may share the same structural box, but the wrap color, label, sleeve or foil color can change. A version list helps the factory avoid mixing scent names, barcodes and colorways during bulk production.',
        ));
        $html .= vpn_candle_inline_image($image_ids[3], 'Open two-piece candle gift box showing the premium presentation for a candle jar.', true);
        $html .= vpn_candle_section('Retail, Boutique and Gift Program Use Cases', array(
            'This box is suitable for luxury candle launches, boutique fragrance stores, spa gifts, hotel room amenities, wedding candle favors, corporate wellness gifts and holiday candle sets. It is especially useful when the candle vessel itself is attractive and the packaging should feel like a keepsake rather than disposable secondary packaging.',
            'For retail shelves, the lid-and-base box should still be easy to identify. Scent labels, color bands or side-panel names can help store staff and customers distinguish variants. For e-commerce, the gift box may need an outer shipper or protective mailer, because a premium rigid box is not always designed to survive direct courier handling by itself.',
        ));
        $html .= vpn_candle_section('Building a Candle Collection Without Making Every Box the Same', array(
            'Premium candle brands often sell by collection: floral, woody, citrus, spa, festive, travel, wedding or hotel lines. The two-piece structure can stay consistent while the paper wrap, foil color, label panel or sleeve changes by scent. This creates a recognizable brand family without making every SKU look identical. Buyers should prepare a variant matrix before production so scent names, color codes and barcodes stay organized.',
            'A collection system can also use different insert depths for different jar heights. For example, an 8 oz glass candle and a shorter ceramic vessel may share the same outer footprint but require different cavity height or collar support. If the brand plans repeat orders, keeping the outer size stable can reduce storage complexity while allowing flexible product launches.',
            'For gift sets, the inner layout can tell a story. A candle may sit beside matches, a wick trimmer, a scent card, a sample tin or a small ceramic accessory. The customer should understand the order of discovery when the lid is lifted. This is why the insert should be reviewed as part of the brand experience, not only as a protection component.',
        ));
        $html .= vpn_candle_section('Packing, Storage and Export Notes for Rigid Candle Boxes', array(
            'Rigid candle boxes usually occupy more space than folding cartons because they are often delivered assembled. This affects sea freight, warehouse storage and carton quantity. Buyers should confirm whether the box can be shipped nested, semi-assembled or fully assembled, and whether the insert will be packed separately or installed before delivery.',
            'For export orders, the outer master carton should protect corners and lid edges. A beautiful rigid box can still arrive with rubbed edges if the cartons are too loose or if the boxes move against each other. Tissue interleaving, polybags, paper sleeves or divided master cartons may be needed for dark matte papers, metallic foil surfaces or textured wraps.',
            'The buyer should keep an approved production sample for future reorders. Rigid box hand feel depends on board thickness, wrap tension, paper grain, glue amount and corner finishing. Without a reference sample, a repeat order may drift subtly even if the artwork file has not changed.',
        ));
        $html .= vpn_candle_section('How to Brief the Factory for a Premium Candle Launch', array(
            'A strong brief should include candle diameter, candle height, filled weight, vessel material, lid shape, label position, target retail price and whether the box is for shelf sale, gift sale or online shipment. These details help the factory decide whether the base needs reinforcement, whether the insert should be foam or paper, and whether the lid clearance should be adjusted for a smoother reveal.',
            'Artwork files should separate the structural dieline, logo file, scent names, foil area, embossing area and any inner printing. If the same structure will hold several scents, prepare a spreadsheet with each scent name, Pantone reference, barcode and quantity. This reduces the risk of printing a winter scent label on a summer colorway or mixing foil colors across variants.',
            'Premium candle packaging also benefits from a sample approval note. Record the approved lid tightness, insert height, corner finish, paper texture and logo position. A written note may feel boring, but it protects the visual standard when the order is repeated, expanded to new scents or produced during a busy seasonal schedule.',
            'If the candle is sold through distributors, the brief should also mention carton labels, retail price stickers and how stores will identify scent variants without opening the gift box. Small operational details like these can prevent warehouse confusion and keep the premium package clean at the point of sale.',
        ));
        $html .= vpn_candle_section('QC Notes for Premium Candle Gift Packaging', array(
            'Quality control should check lid fit, base squareness, corner wrapping, paper bubbles, glue marks, foil position, logo alignment, insert height, candle movement and final carton packing. For dark matte papers, scuffing and fingerprints should be reviewed under angled light. For foil logos, the factory should keep the approved sample as a reference for pressure and placement.',
            'To quote this product accurately, send candle diameter, jar height, weight, preferred opening style, insert requirement, artwork, finish direction and quantity to ' . $quote_link . '. If you need stronger shipping protection, pair this gift box with a candle mailer or compare with ' . $mailer_link . ' for the outer packaging layer.',
        ));
        return $html;
    }

    $html = vpn_candle_section('Window Candle Box for Tumbler Candles and Shelf Confidence', array(
        'A custom window candle box for tumbler candles is built for brands that want customers to see the vessel, color, label or lid before purchase. The window turns the box into a display frame, which is useful for glass tumbler candles, ceramic candle cups, decorative jars, seasonal fragrance collections and lifestyle retail products. It gives visual proof without forcing the customer to open the package.',
        'This product belongs to our ' . $cat_link . ' range, but its strongest SEO and buyer angle is retail transparency. A candle often sells through surface, scent name and mood. A clear window helps the buyer confirm the jar style and label alignment while the paperboard still provides branding space, warning information, barcode area and shelf structure.',
    ));
    $html .= vpn_candle_inline_image($image_ids[1], 'Front view of a custom window candle box for tumbler candle retail display.', true);
    $html .= vpn_candle_section('Window Shape Should Support the Candle, Not Weaken the Box', array(
        'The window opening must be planned around jar diameter, label position, candle height and panel strength. A window that is too large can weaken the front panel, especially when the candle is heavy. A window that is too small may hide the part customers want to inspect. The best design shows the candle clearly while leaving enough board around the edges for strength and clean gluing.',
        'Window patching also adds production details. The PET or biodegradable film must be glued neatly, remain flat, and avoid touching the candle surface. If the jar label has metallic ink or glossy varnish, the film reflection should be checked under retail lighting so the package does not create confusing glare.',
    ));
    $html .= vpn_candle_section('Retail Storytelling for Tumbler Candle Packaging', array(
        'A tumbler candle package needs a different information layout from a shipping mailer or rigid gift box. The front panel can combine brand name, scent, window view and a short mood line. Side panels can carry burn time, wax type, fragrance notes, care instructions and safety icons. The back panel can hold barcode, importer information, ingredients and QR code.',
        'The window gives the product a voice, so the printed artwork should not fight it. Minimal typography, balanced spacing and restrained color often work well for premium tumbler candles. For seasonal or mass retail collections, brighter illustrations and gloss accents may be appropriate, as long as warning text remains readable.',
    ));
    $html .= vpn_candle_inline_image($image_ids[2], 'Open window candle box showing the paperboard structure and product access.');
    $html .= vpn_candle_section('Paperboard, Film and Surface Finish Options', array(
        'Common materials include ivory board, SBS paperboard, duplex board, kraft paperboard, coated paperboard and micro-flute board for heavier candles. Window film can be PET, recycled PET or another transparent material depending on the buyer requirement. Review ' . $material_link . ' if you are still comparing board strength, print surface and natural kraft appearance.',
        'Finish selection should match the candle brand. Matte lamination gives a soft lifestyle feel. Gloss lamination can make color and product photography brighter. Soft-touch film can feel premium but should be tested for scuff marks. Foil stamping, embossing, debossing and spot UV can highlight the logo or scent name, but the window should remain the main visual focus. For surface decisions, compare ' . $finish_link . '.',
    ));
    $html .= vpn_candle_section('Insert and Bottom Support for Heavy Tumbler Candles', array(
        'A window box may look like a simple folding carton, but tumbler candles can be heavy. The bottom structure, tuck flap, glue seam and optional insert need to hold the candle securely. Depending on weight, buyers may choose a reinforced base, folded paperboard platform, collar insert or inner tray to stop the jar from pressing directly against the front window.',
        'The insert should also protect label orientation. If the customer sees the jar through the window, the logo and scent label should face forward after packing. This requires a snug but practical fit. During sampling, the team should rotate, shake and reopen the box to check whether the candle stays aligned.',
    ));
    $html .= vpn_candle_inline_image($image_ids[3], 'Side-angle view showing the window candle box depth and tumbler candle presentation.', true);
    $html .= vpn_candle_section('Where This Window Candle Box Works Best', array(
        'This format is suitable for lifestyle boutiques, home fragrance retailers, gift shops, spa products, supermarket candle shelves, hotel amenity programs and seasonal gift displays. It is especially useful when the candle container has visual value: amber glass, frosted glass, ceramic texture, printed label, wooden lid or colored wax.',
        'Compared with a premium ' . $gift_link . ', the window candle box is more direct and retail-driven. Compared with ' . $window_link . ', the candle version must pay more attention to heat warnings, wax product information, jar weight and scent variants. For direct shipping, an outer mailer may still be needed to protect the window film and carton corners.',
    ));
    $html .= vpn_candle_section('Merchandising Different Scents on One Shelf', array(
        'Window candle packaging is useful when a retailer needs customers to compare scents quickly. The front window shows the jar, but the printed panels still need scent names, color cues and product codes. A lavender candle, vanilla candle and amber candle can use the same window structure while changing label color, side-panel band or small illustration. This keeps the brand system consistent while helping customers choose.',
        'The box should also support shelf blocking. If several units stand side by side, the logo position, window height and scent label should align. A low window on one SKU and a high window on another can make the shelf look messy. For wholesale orders with multiple fragrances, the buyer should approve a shelf mockup or at least compare printed samples together under the same lighting.',
        'Retailers often need barcode access, batch codes and price labels. These details should not cover the window or hide the product view. A planned side or back label area keeps store operations practical. If the box will be sold in several markets, reserve space for language stickers or distributor labels before printing the first batch.',
    ));
    $html .= vpn_candle_section('Safety Text and Candle Care Information', array(
        'Candles require more safety communication than many lifestyle products. The package may need burn warnings, keep-away-from-children notes, trimming guidance, maximum burn time, surface caution, ingredient information and disposal details. These instructions should be readable after lamination and should not be squeezed into the window area as an afterthought.',
        'If the candle brand uses a minimalist front panel, safety text can be organized on the back panel or inner flap. The important point is hierarchy. Customers should quickly understand scent and brand on the front, then find care instructions when they handle the box. A QR code can support extended candle care content, but essential safety warnings should remain printed on the package.',
        'For international B2B buyers, compliance language may vary by destination market. The same structural box can include different back-panel artwork versions or stickers. Preparing these versions early prevents the factory from mixing market-specific labels during packing.',
    ));
    $html .= vpn_candle_section('Mistakes to Avoid With Candle Window Packaging', array(
        'The first mistake is making the window too generous. A large cutout may look attractive on a screen, but it reduces panel strength and can make the box deform when stacked. For a heavy tumbler candle, the front panel needs enough paperboard around the window to keep the structure square. The designer should balance visibility with compression performance.',
        'The second mistake is forgetting that transparent film can scratch or collect dust. A candle window box should be packed and stored carefully, especially if the film covers a large area. The factory should inspect for glue marks, trapped fibers, fingerprints and static dust before final packing. A dusty window makes a clean candle look old before it reaches the shelf.',
        'The third mistake is treating every scent as only an artwork swap. Different jars, lids or wax colors may change the visible area through the window. If the brand uses multiple vessels, the sample should be checked with each vessel type. A centered window for a straight glass tumbler may not frame a round ceramic jar or wooden-lid candle in the same way.',
        'The fourth mistake is ignoring how the customer removes the candle. A tight carton with a window may look secure, but customers still need finger space to lift the tumbler without tearing the box. During sampling, open the package the way a real shopper would: standing at a counter, holding the box in one hand, and trying not to touch the wax or damage the label.',
    ));
    $html .= vpn_candle_section('Production Checks and Quotation Details', array(
        'Quality control should include window film adhesion, dust inside the window, scratch marks, board cracking around the cutout, bottom strength, candle alignment, barcode readability, color consistency and carton packing. For kraft board, buyers should approve how ink appears on the natural surface. For white board, check whether the window edge stays clean after die cutting.',
        'For wholesale deliveries, the master carton should protect the window face from pressure. If boxes are packed too tightly, the film can scuff; if they are too loose, corners can rub. Carton quantity and pallet stacking should be confirmed before bulk shipment.',
        'To quote accurately, send tumbler diameter, height, candle weight, window preference, label orientation, artwork, film requirement, finish direction and order quantity to ' . $quote_link . '. VPN can recommend paperboard thickness, window size, insert method and printing plan for bulk candle packaging production from 1000 boxes.',
    ));
    return $html;
}

$marker = 'product-samples-candle-packaging';

$products = array(
    array(
        'key' => 'mailer',
        'title' => 'CUSTOM CANDLE SHIPPING MAILER BOX WITH CORRUGATED INSERT',
        'slug' => 'custom-candle-shipping-mailer-box-with-corrugated-insert',
        'keyword' => 'candle shipping mailer box',
        'category_slug' => 'corrugated-mailer-boxes',
        'category_name' => 'Corrugated Mailer Boxes',
        'short' => 'CUSTOM CANDLE SHIPPING MAILER BOX WITH CORRUGATED INSERT is a protective e-commerce package for glass jar candles, soy candles, fragrance products, subscription candle boxes and boutique online orders. The corrugated insert keeps the candle centered, reduces jar movement and helps protect labels, lids and vessel corners during courier handling. It can be customized with kraft or white corrugated board, internal printing, logo artwork, postal label area, scent card slot, folded insert, locking tabs and export carton packing. MOQ starts from 1000 boxes.',
        'meta' => 'Custom candle shipping mailer box with corrugated insert for glass jar candles, e-commerce delivery, subscription boxes and bulk orders.',
        'feature' => 'Corrugated mailer protection, candle jar insert, e-commerce shipping structure, custom logo printing',
        'industrial' => 'Candles, Home Fragrance, E-commerce, Subscription Boxes',
        'paper' => 'Kraft Corrugated Board / White Corrugated Board / E-flute / B-flute',
        'box_type' => 'Candle Shipping Mailer Box',
        'shape' => 'Rectangle / Customized Candle Fit',
        'accessories' => 'Corrugated insert / Locking tabs / Scent card slot / Shipping label area',
        'liner' => 'Corrugated insert / Paperboard support / Molded pulp optional',
        'printing' => 'CMYK Printing, Pantone Printing, Inside Printing, Matte Lamination, Water-based Coating',
        'colors' => 'Kraft / White / Black / CMYK / Pantone / Customized Color',
        'images' => array(
            'custom-candle-shipping-mailer-box-with-corrugated-insert-front-view.webp',
            'custom-candle-shipping-mailer-box-with-corrugated-insert-detail-view.webp',
            'custom-candle-shipping-mailer-box-with-corrugated-insert-angle-view.webp',
            'custom-candle-shipping-mailer-box-with-corrugated-insert-open-box.webp',
        ),
        'captions' => array(
            'Custom candle shipping mailer box with corrugated insert front view.',
            'Detail view of corrugated insert for candle jar protection.',
            'Angled view of candle mailer packaging for e-commerce delivery.',
            'Open candle shipping mailer with jar cavity and protective support.',
        ),
        'alt' => 'Custom candle shipping mailer box with corrugated insert for glass jar candles',
        'tags' => array('candle shipping mailer box', 'candle mailer packaging', 'corrugated candle box', 'custom candle box', 'e-commerce candle packaging'),
    ),
    array(
        'key' => 'two_piece',
        'title' => 'CUSTOM TWO-PIECE CANDLE GIFT BOX WITH LID AND BASE',
        'slug' => 'custom-two-piece-candle-gift-box-with-lid-and-base',
        'keyword' => 'two-piece candle gift box',
        'category_slug' => 'candle-packaging-boxes',
        'category_name' => 'Candle Packaging Boxes',
        'short' => 'CUSTOM TWO-PIECE CANDLE GIFT BOX WITH LID AND BASE is a premium paper packaging option for scented candles, jar candles, ceramic candle vessels, holiday candle gifts and boutique fragrance collections. The separate lid and base create a slower unboxing experience while the insert can hold the candle securely and present the vessel cleanly. Buyers can customize rigid board, art paper wrap, textured paper, foil logo, embossing, inner printing, paperboard or EVA insert, scent cards and retail sleeve details. MOQ starts from 1000 boxes.',
        'meta' => 'Custom two-piece candle gift box with lid and base for premium scented candles, jar candles and boutique fragrance gift sets.',
        'feature' => 'Lid and base structure, premium candle gift presentation, custom insert, foil logo option',
        'industrial' => 'Candles, Home Fragrance, Gift Packaging, Boutique Retail',
        'paper' => 'Rigid Greyboard / Art Paper / Specialty Paper / Coated Paperboard',
        'box_type' => 'Two-Piece Candle Gift Box',
        'shape' => 'Square / Rectangle / Customized Candle Size',
        'accessories' => 'Paperboard insert / EVA insert / Scent card / Ribbon lift / Sleeve optional',
        'liner' => 'Paperboard tray / EVA foam / Molded pulp / Fabric-covered insert',
        'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Spot UV, Matte Lamination',
        'colors' => 'White / Kraft / Black / Gold / Pantone / Customized Color',
        'images' => array(
            'custom-two-piece-candle-gift-box-with-lid-and-base-open-box.webp',
            'custom-two-piece-candle-gift-box-with-lid-and-base-front-view.webp',
            'custom-two-piece-candle-gift-box-with-lid-and-base-detail-view.webp',
            'custom-two-piece-candle-gift-box-with-lid-and-base-angle-view.webp',
        ),
        'captions' => array(
            'Open two-piece candle gift box with lid and base presentation.',
            'Front view of custom candle gift box for premium fragrance brands.',
            'Detail view of lid-and-base candle box structure and material finish.',
            'Angled view of premium two-piece candle packaging with insert space.',
        ),
        'alt' => 'Custom two-piece candle gift box with lid and base for premium scented candles',
        'tags' => array('two-piece candle gift box', 'candle gift packaging', 'lid and base candle box', 'premium candle box', 'custom candle packaging'),
    ),
    array(
        'key' => 'window',
        'title' => 'CUSTOM WINDOW CANDLE BOX FOR TUMBLER CANDLES',
        'slug' => 'custom-window-candle-box-for-tumbler-candles',
        'keyword' => 'window candle box',
        'category_slug' => 'candle-packaging-boxes',
        'category_name' => 'Candle Packaging Boxes',
        'short' => 'CUSTOM WINDOW CANDLE BOX FOR TUMBLER CANDLES is a retail paperboard package for glass tumbler candles, ceramic candle cups, colored wax candles and lifestyle fragrance products. The window lets customers see the vessel, label and product color while the carton provides branding, scent information, warning text and barcode space. It can be customized with ivory board, kraft paperboard, PET or recycled PET window, reinforced bottom, paper insert, matte or gloss lamination, foil logo and custom scent variants. MOQ starts from 1000 boxes.',
        'meta' => 'Custom window candle box for tumbler candles with clear display window, printed paperboard, reinforced base and custom logo options.',
        'feature' => 'Clear window display, tumbler candle fit, reinforced paperboard base, custom printed retail carton',
        'industrial' => 'Candles, Home Fragrance, Lifestyle Retail, Gift Shops',
        'paper' => 'Ivory Board / SBS Paperboard / Kraft Paperboard / Coated Paper / PET Window',
        'box_type' => 'Window Candle Box',
        'shape' => 'Vertical Rectangle / Customized Tumbler Candle Fit',
        'accessories' => 'PET window / Paperboard insert / Reinforced base / Scent card / Hang tag optional',
        'liner' => 'Paperboard tray / Reinforced bottom / No liner optional',
        'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Spot UV, Matte Lamination, Gloss Lamination',
        'colors' => 'White / Kraft / Amber / Black / CMYK / Pantone / Customized Color',
        'images' => array(
            'custom-window-candle-box-for-tumbler-candles-detail-view.webp',
            'custom-window-candle-box-for-tumbler-candles-front-view.webp',
            'custom-window-candle-box-for-tumbler-candles-open-box.webp',
            'custom-window-candle-box-for-tumbler-candles-side-angle.webp',
        ),
        'captions' => array(
            'Detail view of custom window candle box for tumbler candles.',
            'Front view of window candle packaging for retail shelf display.',
            'Open paperboard candle box with clear window and product access.',
            'Side angle of window candle box showing depth and tumbler fit.',
        ),
        'alt' => 'Custom window candle box for tumbler candles with clear display window',
        'tags' => array('window candle box', 'tumbler candle packaging', 'candle box with window', 'custom candle carton', 'retail candle packaging'),
    ),
);

$audit = array('# Candle Packaging Product Import Audit', '');

foreach ($products as $product) {
    $category = get_term_by('slug', $product['category_slug'], 'product_cat');
    if (!$category || is_wp_error($category)) {
        $created = wp_insert_term($product['category_name'], 'product_cat', array('slug' => $product['category_slug']));
        if (is_wp_error($created)) {
            fwrite(STDERR, 'Missing product category: ' . $product['category_slug'] . PHP_EOL);
            continue;
        }
        $category = get_term((int) $created['term_id'], 'product_cat');
    }

    $image_ids = array();
    foreach ($product['images'] as $index => $filename) {
        $image_ids[] = vpn_candle_attachment_id(
            $filename,
            $product['alt'] . ' view ' . ($index + 1),
            $product['captions'][$index]
        );
    }

    if (count(array_filter($image_ids)) !== count($product['images'])) {
        echo 'Failed images: ' . $product['title'] . PHP_EOL;
        continue;
    }

    $existing = get_page_by_path($product['slug'], OBJECT, 'product');
    $postarr = array(
        'post_type'    => 'product',
        'post_status'  => 'publish',
        'post_title'   => $product['title'],
        'post_name'    => $product['slug'],
        'post_excerpt' => $product['short'],
        'post_content' => vpn_candle_content($product['key'], $image_ids),
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

    wp_set_object_terms($product_id, array((int) $category->term_id), 'product_cat', false);
    wp_set_object_terms($product_id, $product['tags'], 'product_tag', false);
    set_post_thumbnail($product_id, $image_ids[0]);
    update_post_meta($product_id, '_product_image_gallery', implode(',', array_slice($image_ids, 1)));
    update_post_meta($product_id, '_sku', 'sample-candle-' . $product['slug']);
    update_post_meta($product_id, '_regular_price', '');
    update_post_meta($product_id, '_price', '');
    update_post_meta($product_id, '_stock_status', 'instock');
    update_post_meta($product_id, '_manage_stock', 'no');
    update_post_meta($product_id, '_visibility', 'visible');
    update_post_meta($product_id, '_custom_box_product_specs', vpn_candle_specs($product));
    update_post_meta($product_id, '_vpn_sample_import', $marker);
    update_post_meta($product_id, 'rank_math_focus_keyword', $product['keyword']);
    update_post_meta($product_id, 'rank_math_title', $product['title'] . ' | VPN PAPER BOX MANUFACTURER');
    update_post_meta($product_id, 'rank_math_description', $product['meta']);

    $content = get_post_field('post_content', $product_id);
    $words = str_word_count(wp_strip_all_tags($content));
    $figures = substr_count($content, 'product-inline-figure');
    $specs = get_post_meta($product_id, '_custom_box_product_specs', true);
    $gallery = array_filter(array_map('absint', explode(',', (string) get_post_meta($product_id, '_product_image_gallery', true))));

    $audit[] = '## ' . $product['title'];
    $audit[] = '- ID: ' . $product_id;
    $audit[] = '- URL: ' . get_permalink($product_id);
    $audit[] = '- Category: ' . $product['category_name'];
    $audit[] = '- Focus keyword: ' . $product['keyword'];
    $audit[] = '- Words: ' . $words;
    $audit[] = '- Content H1 count: ' . preg_match_all('/<h1\b/i', $content);
    $audit[] = '- Specs rows: ' . (is_array($specs) ? count($specs) : 0);
    $audit[] = '- Gallery images: ' . count($gallery);
    $audit[] = '- Inline figures: ' . $figures;
    $audit[] = '- Duplicate risk score: ' . ('mailer' === $product['key'] ? '3/10' : ('two_piece' === $product['key'] ? '4/10' : '4/10'));
    $audit[] = '';

    echo 'Imported: ' . $product['title'] . ' (#' . $product_id . ') words=' . $words . ' figures=' . $figures . PHP_EOL;
}

file_put_contents(dirname(__DIR__) . '/product-samples-candle-packaging-audit.md', implode(PHP_EOL, $audit));
echo 'Candle packaging product import complete.' . PHP_EOL;
