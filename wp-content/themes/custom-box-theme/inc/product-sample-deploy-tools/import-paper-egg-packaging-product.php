<?php
/**
 * Create or update the custom paper egg packaging product.
 *
 * Usage:
 *   php tools/import-paper-egg-packaging-product.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

$slug = 'custom-paper-egg-packaging-boxes';
$title = 'CUSTOM PAPER EGG PACKAGING BOXES';
$category_slug = 'food-paper-boxes';
$image_map = array(
    'paper-egg-tray-with-outer-box' => array(
        'alt'     => 'Custom paper egg packaging box with molded pulp tray and open outer carton',
        'title'   => 'Paper Egg Tray With Outer Box',
        'caption' => 'Protective molded pulp egg tray fitted inside a custom kraft paper outer box.',
    ),
    'kraft-paper-egg-box-6-pack' => array(
        'alt'     => 'Kraft paper egg box for six eggs with hinged lid',
        'title'   => 'Kraft Paper Egg Box 6 Pack',
        'caption' => 'Compact six-egg kraft paper box for farm shops, specialty retail, and branded food packs.',
    ),
    'paper-egg-box-with-window-12-pack' => array(
        'alt'     => 'Paper egg box with clear window for twelve egg retail display',
        'title'   => 'Paper Egg Box With Window 12 Pack',
        'caption' => 'Windowed paper egg box that combines product visibility with a printable retail surface.',
    ),
    'molded-pulp-egg-carton-10-pack' => array(
        'alt'     => 'Molded pulp egg carton for ten eggs with protective locking lid',
        'title'   => 'Molded Pulp Egg Carton 10 Pack',
        'caption' => 'Molded pulp carton with individual pockets for egg separation and transport protection.',
    ),
);

function vpn_egg_find_attachment(string $base): int
{
    $ids = get_posts(array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => array(
            array(
                'key'     => '_wp_attached_file',
                'value'   => '/' . $base . '.',
                'compare' => 'LIKE',
            ),
        ),
    ));

    return $ids ? (int) $ids[0] : 0;
}

function vpn_egg_specs(string $title): array
{
    return array(
        array('label' => 'Feature', 'value' => 'Recyclable paper structure, molded egg pockets, custom print, retail-ready protection'),
        array('label' => 'Industrial Use', 'value' => 'Fresh eggs, organic eggs, farm produce, poultry brands, grocery retail, food delivery'),
        array('label' => 'Paper Type', 'value' => 'Kraft paperboard, corrugated board, molded pulp, recycled paper fiber'),
        array('label' => 'Box Type', 'value' => 'Egg carton, hinged egg box, window box, tray with outer paper box'),
        array('label' => 'Shape', 'value' => 'Rectangular / 6-pack / 10-pack / 12-pack / customized capacity'),
        array('label' => 'Place of Origin', 'value' => 'Vietnam'),
        array('label' => 'Model Number', 'value' => 'VPN-EGG-PACKAGING-BOX'),
        array('label' => 'Brand Name', 'value' => 'VPN'),
        array('label' => 'Province', 'value' => 'Ho Chi Minh City'),
        array('label' => 'Accessories', 'value' => 'Molded pulp tray, paper divider, clear window, locking tab, printed sleeve, label area'),
        array('label' => 'Custom Order', 'value' => 'Accept'),
        array('label' => 'Liner Type', 'value' => 'Molded pulp egg tray / recycled fiber pocket tray / paperboard divider'),
        array('label' => 'Logo Printing', 'value' => 'Custom logo'),
        array('label' => 'Printing Handling', 'value' => 'Flexographic printing, offset printing, CMYK, Pantone, embossing, label application'),
        array('label' => 'Color', 'value' => 'Natural kraft, white, grey molded pulp, CMYK / customized'),
        array('label' => 'Size', 'value' => 'Customized size'),
        array('label' => 'Thickness', 'value' => 'Customized thickness'),
        array('label' => 'Single Piece Price', 'value' => 'Price based on capacity, material, tray design, printing, finishing, and quantity'),
        array('label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes'),
        array('label' => 'Product Name', 'value' => $title),
        array('label' => 'Design', 'value' => "Customer's Specific Requirement"),
    );
}

function vpn_egg_short_description(): string
{
    return 'CUSTOM PAPER EGG PACKAGING BOXES are protective fiber-based packs for farms, egg distributors, organic food brands, grocery chains, specialty retailers, and delivery programs. Available as compact 6-pack cartons, molded pulp 10-pack carriers, windowed 12-pack paper boxes, or larger trays fitted inside corrugated outer cartons, each structure is developed around egg size, count, handling route, and shelf presentation. Individual pockets and dividers reduce egg-to-egg contact, while locking lids and fitted outer boxes help control movement during packing and transport. Buyers can customize capacity, kraft or white paper surfaces, molded pulp color, ventilation, window shape, labels, logo printing, traceability panels, barcode areas, and export packing. MOQ is 1000 boxes, with samples recommended to verify fit, closure strength, stacking, and breakage protection before bulk production.';
}

function vpn_egg_figure(int $attachment_id, string $caption, bool $narrow = false): string
{
    if (!$attachment_id) {
        return '';
    }

    $class = 'product-inline-figure product-inline-figure-small' . ($narrow ? ' is-narrow' : '');

    return '<figure class="' . esc_attr($class) . '">'
        . wp_get_attachment_image($attachment_id, 'large', false, array('loading' => 'lazy'))
        . '<figcaption>' . esc_html($caption) . '</figcaption>'
        . '</figure>';
}

function vpn_egg_content(array $images): string
{
    return '<h2>Paper Egg Packaging Designed Around Fragile Food Products</h2>'
        . '<p>Egg packaging has a simple appearance, but its structural job is demanding. Each shell must be separated from neighboring eggs, supported below its widest point, protected from lid pressure, and held securely when the pack is lifted, stacked, or moved through distribution. Custom paper egg packaging boxes address these requirements with molded pockets, paperboard dividers, locking tabs, and outer cartons selected for the actual egg count and handling route. The four formats shown in this product group cover compact farm-shop packs, standard molded pulp cartons, display boxes with windows, and larger trays carried inside a protective paper box.</p>'
        . '<p>This product is intended for poultry farms, egg grading and packing facilities, organic food brands, supermarket suppliers, wholesalers, hospitality distributors, meal-kit companies, and specialty retailers. A buyer supplying six premium eggs to a local shop has different needs from a distributor moving ten- or twelve-egg packs through a warehouse. Likewise, a bulk tray used for delivery needs stronger stacking control than a carton placed directly on a refrigerated retail shelf. The packaging specification should therefore begin with egg size, count, packing speed, transport distance, display method, and expected stacking load.</p>'
        . vpn_egg_figure($images['paper-egg-tray-with-outer-box'] ?? 0, 'Molded pulp tray and fitted outer paper box for organized egg packing and transport protection.')
        . '<h2>Four Practical Egg Box Structures</h2>'
        . '<p>The six-pack kraft egg box is a compact format for farm shops, farmers markets, tasting sets, gift hampers, and premium free-range or organic eggs. Its hinged paperboard lid provides a broad printable surface while internal pockets keep the eggs separated. The molded pulp ten-pack carton uses a familiar fiber structure with raised posts between eggs and a shaped lid that helps distribute pressure. It is suitable for high-volume packing where nesting, quick closing, and dependable shell protection are priorities.</p>'
        . '<p>The twelve-pack window box takes a more retail-oriented approach. A clear window lets customers see the egg color and arrangement without opening the package, while the surrounding kraft board can carry logos, product claims, origin details, and handling instructions. The tray-with-outer-box structure is designed for larger counts or more demanding distribution. A molded pulp tray carries the eggs, and a separate corrugated or kraft paper box adds sidewall strength, stacking support, and a clean branded exterior. This format is useful for delivery services, wholesale packs, hospitality supply, or premium egg collections that need more protection than an exposed tray.</p>'
        . vpn_egg_figure($images['kraft-paper-egg-box-6-pack'] ?? 0, 'Six-pack kraft paper egg box with individual pockets and a hinged lid.', true)
        . '<h2>Molded Pulp Protection and Egg Separation</h2>'
        . '<p>Molded pulp is widely used for egg packaging because it can form individual cavities around fragile shells while absorbing small shocks and vibrations. The pocket diameter, depth, center posts, lid profile, and closing pressure all affect performance. If a cavity is too loose, the egg can move and strike the sidewall. If it is too tight, workers may apply excess force during packing or customers may struggle to remove the egg. Samples should be tested with the actual egg grades rather than a generic reference size.</p>'
        . '<p>For mixed sizes or specialty eggs, the tray geometry can be adjusted. Quail eggs, duck eggs, standard chicken eggs, and larger premium eggs require different spacing and support. Paperboard outer boxes can also include internal stops so a tray does not slide during transport. When breakage risk is high, the supplier should review the full packing system: primary egg carton, master carton, stacking arrangement, pallet pattern, and delivery conditions. A well-designed retail carton cannot compensate for a weak master carton or uncontrolled empty space during shipment.</p>'
        . vpn_egg_figure($images['molded-pulp-egg-carton-10-pack'] ?? 0, 'Ten-pack molded pulp egg carton with locking points and shaped protective pockets.')
        . '<h2>Retail Branding, Traceability, and Product Information</h2>'
        . '<p>Paper egg boxes provide more than physical protection. They are also a communication surface for product origin, farm identity, egg grade, pack count, nutrition details, storage guidance, packing date, expiry information, barcode, batch code, and certification marks. Organic, cage-free, free-range, pasture-raised, or specialty-feed claims should be placed clearly and supported by the documentation required in the destination market. A structured information panel helps buyers and packing teams avoid crowding important text around folds, locking tabs, or window edges.</p>'
        . '<p>Natural kraft paper supports an agricultural and minimally processed visual direction. White or coated paperboard provides stronger color reproduction for supermarket brands that need bright graphics and consistent shelf recognition. Molded pulp can remain grey or natural, use a paper label, or be combined with a printed sleeve. Flexographic printing is practical for simple high-volume graphics, while offset-printed wraps or cartons can support detailed images and premium branding. Pantone references are useful when farm colors must remain consistent across cartons, labels, delivery cases, and retail signage.</p>'
        . '<h2>Window Boxes for Product Visibility</h2>'
        . '<p>A windowed egg box allows shoppers to inspect the visible eggs while keeping the package closed. This can be valuable for premium brown eggs, mixed-color farm eggs, or curated packs where appearance is part of the selling point. The window dimensions should reveal enough product without weakening the lid. Its film must be attached cleanly, and the edge should remain clear of creases and locking areas. Ventilation and condensation also need consideration when eggs move between temperature-controlled environments.</p>'
        . '<p>The window material and food-packaging requirements should be confirmed for the target market and intended use. Some projects may prefer an open paper cutout, a cellulose-based transparent film, or a conventional clear film depending on visibility, strength, recycling goals, and local collection systems. Environmental statements should refer to the actual material combination rather than assuming that every component follows the same recycling route. Buyers should request accurate material documentation before printing claims on the package.</p>'
        . vpn_egg_figure($images['paper-egg-box-with-window-12-pack'] ?? 0, 'Twelve-pack kraft paper egg box with a clear viewing window for retail display.')
        . '<h2>Stacking, Ventilation, and Packing-Line Requirements</h2>'
        . '<p>Egg cartons are frequently stacked while empty, filled, stored, and transported. Empty packs should nest efficiently without becoming difficult to separate on the packing line. Filled packs need enough vertical resistance to protect the top row without transferring excessive pressure to the eggs. Lid ribs, center posts, sidewalls, and the master-carton arrangement all contribute to stacking performance. For manual packing, the closure must be intuitive and fast. For semi-automatic or automatic lines, dimensions and tolerances need tighter control.</p>'
        . '<p>Ventilation openings may be added to help temperature equalization and reduce trapped moisture, but every opening changes the strength of the structure. The correct pattern depends on pack size, material, refrigeration, storage time, and shipping environment. Handles can be considered for larger consumer packs, while tear strips, locking tabs, or tamper-evident labels may support delivery and retail programs. Samples should be filled, stacked, refrigerated if relevant, and transported through a representative route before the final order is approved.</p>'
        . '<h2>Material Choices for Different Sales Channels</h2>'
        . '<p>Recycled molded pulp is a practical starting material for standard egg cartons and trays because it combines cushioning, cavity formation, and efficient nesting. Kraft paperboard works well for smaller branded packs and sleeves. Corrugated board is appropriate for outer boxes, delivery packs, and larger trays where sidewall and stacking strength are needed. Material thickness should not be selected by appearance alone; it must be matched to the egg count, total packed weight, panel span, moisture exposure, and carton arrangement.</p>'
        . '<p>Farm-gate retail may prioritize a natural appearance, simple one-color printing, and small pack counts. Supermarket programs need barcode consistency, shelf-facing graphics, production efficiency, and reliable case packing. E-commerce and direct delivery require stronger outer protection because the pack may experience mixed handling and less predictable stacking. Hospitality and bakery supply can use larger tray-and-box formats that focus on safe transport and fast unpacking rather than individual consumer graphics. One packaging family can support these channels, but each format should have its own tested specification.</p>'
        . '<h2>Food Packaging Review and Responsible Claims</h2>'
        . '<p>Egg packaging requirements vary by destination, sales channel, and whether any component is intended for direct food contact. Buyers should provide the target country and relevant compliance requirements during quotation. The material supplier can then confirm available declarations, test reports, inks, adhesives, window films, and production controls for the proposed construction. Packaging should also provide enough space for legally required labeling and traceability information.</p>'
        . '<p>Recyclable, recycled-content, compostable, biodegradable, and plastic-free claims are not interchangeable. A molded pulp carton may follow a different disposal route from a windowed paperboard box containing transparent film. The final claim should reflect every component, local infrastructure, and supporting evidence. Clear material specifications are more useful than broad environmental language, especially for international buyers managing retailer approval and packaging reporting.</p>'
        . '<h2>Sampling and Quality Checks Before Bulk Production</h2>'
        . '<p>A structural sample should be checked with the real eggs and the intended packing process. Review cavity fit, removal force, lid clearance, locking strength, tray movement, window attachment, ventilation, print position, barcode readability, and master-carton fit. The filled sample should be lifted, tilted, stacked, and transported to reveal movement or pressure points. Drop and vibration testing can be considered when delivery conditions create higher risk.</p>'
        . '<p>Production quality checks should monitor moisture, pulp forming consistency, pocket dimensions, paperboard creasing, glue application, print registration, closure performance, and pack cleanliness. Because eggs are naturally variable, the approved tolerance should account for the actual size range supplied by the farm. Keeping an approved physical sample and packing specification helps maintain consistency across repeat orders and makes future artwork or capacity changes easier to control.</p>'
        . '<h2>Request a Custom Paper Egg Packaging Quote</h2>'
        . '<p>For an accurate quotation, provide the egg type and size range, number of eggs per pack, preferred carton style, filled weight, sales channel, packing method, required printing, window or label needs, destination country, estimated quantity, and delivery conditions. Photos of the current pack and master carton are useful when the goal is to reduce breakage, improve branding, or change material. MOQ is 1000 boxes, while the most efficient quantity depends on the structure and printing process.</p>'
        . '<p>VPN Paper Box can develop six-pack cartons, molded pulp ten-pack carriers, twelve-pack window boxes, and larger egg trays with protective outer cartons. Buyers can also compare related <a href="' . esc_url(home_url('/product-category/food-paper-boxes/')) . '">food paper box solutions</a>, review <a href="' . esc_url(home_url('/custom-kraft-bakery-box-with-window/')) . '">kraft window packaging</a>, or study <a href="' . esc_url(home_url('/custom-pastry-gift-box-with-insert/')) . '">paper box insert structures</a> when planning a branded food-packaging range. The final egg box should protect fragile shells, support efficient packing, and communicate the product clearly from farm to customer.</p>';
}

$category = get_term_by('slug', $category_slug, 'product_cat');

if (!$category || is_wp_error($category)) {
    fwrite(STDERR, "Required product category was not found: {$category_slug}\n");
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
    'post_excerpt' => vpn_egg_short_description(),
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
$missing = array();

foreach ($image_map as $base => $image) {
    $attachment_id = vpn_egg_find_attachment($base);

    if (!$attachment_id) {
        $missing[] = $base;
        continue;
    }

    $image_ids[$base] = $attachment_id;
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']);
    wp_update_post(array(
        'ID'           => $attachment_id,
        'post_parent'  => $product_id,
        'post_title'   => $image['title'],
        'post_excerpt' => $image['caption'],
    ));
}

if ($missing) {
    fwrite(STDERR, 'Missing images: ' . implode(', ', $missing) . PHP_EOL);
    exit(1);
}

wp_set_object_terms($product_id, array((int) $category->term_id), 'product_cat', false);
wp_set_object_terms($product_id, 'simple', 'product_type');
wp_set_object_terms($product_id, array(
    'paper egg box',
    'egg packaging',
    'molded pulp egg carton',
    'kraft egg box',
    'food packaging',
    'custom paper packaging',
), 'product_tag', false);

update_post_meta($product_id, '_vpn_sample_import', 'paper-egg-packaging-product');
update_post_meta($product_id, '_regular_price', '');
update_post_meta($product_id, '_price', '');
update_post_meta($product_id, '_stock_status', 'instock');
update_post_meta($product_id, '_manage_stock', 'no');
update_post_meta($product_id, '_visibility', 'visible');
update_post_meta($product_id, '_custom_box_product_specs', vpn_egg_specs($title));
update_post_meta($product_id, 'rank_math_focus_keyword', 'custom paper egg packaging boxes');
update_post_meta($product_id, 'rank_math_title', $title . ' | VPN PAPER BOX MANUFACTURER');
update_post_meta($product_id, 'rank_math_description', 'Custom paper egg packaging boxes with molded pulp trays, kraft cartons, windows, logo printing, and protective 6, 10, or 12-egg formats.');

set_post_thumbnail($product_id, $image_ids['paper-egg-tray-with-outer-box']);
update_post_meta(
    $product_id,
    '_product_image_gallery',
    implode(',', array(
        $image_ids['kraft-paper-egg-box-6-pack'],
        $image_ids['paper-egg-box-with-window-12-pack'],
        $image_ids['molded-pulp-egg-carton-10-pack'],
    ))
);

$content = vpn_egg_content($image_ids);
wp_update_post(array(
    'ID'           => $product_id,
    'post_content' => $content,
));

echo 'Product ID: ' . $product_id . PHP_EOL;
echo 'Product URL: ' . get_permalink($product_id) . PHP_EOL;
echo 'Category: ' . $category->name . PHP_EOL;
echo 'Images: ' . implode(', ', array_values($image_ids)) . PHP_EOL;
echo 'Description words: ' . str_word_count(wp_strip_all_tags($content)) . PHP_EOL;
echo 'Specifications: ' . count(vpn_egg_specs($title)) . PHP_EOL;
