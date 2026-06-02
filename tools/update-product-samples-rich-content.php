<?php
/**
 * Expand the 10 local sample products with richer product descriptions,
 * in-content images, internal links, and product specifications.
 *
 * Usage:
 *   php tools/update-product-samples-rich-content.php
 */

require_once dirname( __DIR__ ) . '/wp-load.php';

function vpn_rich_url( string $path ): string {
	return esc_url( home_url( $path ) );
}

function vpn_rich_link( string $path, string $anchor ): string {
	return '<a href="' . vpn_rich_url( $path ) . '">' . esc_html( $anchor ) . '</a>';
}

function vpn_rich_image_html( int $image_id, string $caption, string $modifier = '' ): string {
	$image = wp_get_attachment_image(
		$image_id,
		'large',
		false,
		array(
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);

	if ( ! $image ) {
		return '';
	}

	$class = trim( 'product-inline-figure product-inline-figure-small ' . $modifier );

	return '<figure class="' . esc_attr( $class ) . '">' . $image . '<figcaption>' . esc_html( $caption ) . '</figcaption></figure>';
}

function vpn_rich_product_images( int $product_id, array $d ): array {
	$ids          = array();
	$thumbnail_id = get_post_thumbnail_id( $product_id );

	if ( $thumbnail_id ) {
		$ids[] = $thumbnail_id;
	}

	$gallery = get_post_meta( $product_id, '_product_image_gallery', true );

	foreach ( array_filter( array_map( 'absint', explode( ',', (string) $gallery ) ) ) as $gallery_id ) {
		if ( $gallery_id && ! in_array( $gallery_id, $ids, true ) ) {
			$ids[] = $gallery_id;
		}
	}

	$captions = array(
		$d['image_caption'],
		$d['title'] . ' side view showing structure, print area, and product presentation.',
		$d['title'] . ' detail view for material, insert, logo, and finishing reference.',
		$d['title'] . ' application image for wholesale packaging and retail display planning.',
	);

	$images = array();

	foreach ( array_slice( $ids, 0, 4 ) as $index => $image_id ) {
		$images[] = vpn_rich_image_html( $image_id, $captions[ $index ] ?? $d['image_caption'], 1 === $index % 2 ? 'is-narrow' : '' );
	}

	return $images;
}

function vpn_specs( array $d ): array {
	return array(
		array( 'label' => 'Feature', 'value' => $d['feature'] ),
		array( 'label' => 'Industrial Use', 'value' => $d['industrial_use'] ),
		array( 'label' => 'Paper Type', 'value' => $d['paper_type'] ),
		array( 'label' => 'Box Type', 'value' => $d['box_type'] ),
		array( 'label' => 'Shape', 'value' => $d['shape'] ),
		array( 'label' => 'Place of Origin', 'value' => 'Vietnam' ),
		array( 'label' => 'Model Number', 'value' => $d['title'] ),
		array( 'label' => 'Brand Name', 'value' => 'VPN' ),
		array( 'label' => 'Province', 'value' => 'Ho Chi Minh City' ),
		array( 'label' => 'Accessories', 'value' => $d['accessories'] ),
		array( 'label' => 'Custom Order', 'value' => 'Accept' ),
		array( 'label' => 'Liner Type', 'value' => $d['liner_type'] ),
		array( 'label' => 'Logo Printing', 'value' => 'Custom logo' ),
		array( 'label' => 'Printing Handling', 'value' => $d['printing'] ),
		array( 'label' => 'Color', 'value' => $d['color'] ),
		array( 'label' => 'Size', 'value' => 'Customized size' ),
		array( 'label' => 'Thickness', 'value' => 'Customized thickness' ),
		array( 'label' => 'Single Piece Price', 'value' => 'Price based on size, material, insert, printing, finishing, and quantity' ),
		array( 'label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes' ),
		array( 'label' => 'Product Name', 'value' => $d['title'] ),
		array( 'label' => 'Design', 'value' => "Customer's Specific Requirement" ),
	);
}

function vpn_rich_content( int $product_id, array $d ): string {
	$d = vpn_normalize_product_data( $d );
	$category_link = vpn_rich_link( $d['category_url'], $d['category_anchor'] );
	$structure_link = vpn_rich_link( $d['structure_url'], $d['structure_anchor'] );
	$material_link = vpn_rich_link( '/paper-materials-for-custom-paper-boxes/', $d['material_anchor'] );
	$related_a = vpn_rich_link( $d['related'][0][0], $d['related'][0][1] );
	$related_b = vpn_rich_link( $d['related'][1][0], $d['related'][1][1] );
	$cta_link = vpn_rich_link( '/contact/#quote', $d['cta_anchor'] );
	$images = vpn_rich_product_images( $product_id, $d );
	$image_1 = $images[0] ?? '';
	$image_2 = $images[1] ?? '';
	$image_3 = $images[2] ?? '';
	$image_4 = $images[3] ?? '';

	return <<<HTML
<h2>Packaging Designed Around {$d['industry_heading']}</h2>
<p>{$d['intro_1']} This product belongs naturally within {$category_link}, but the content, structure, and surface treatment should be planned around the exact product that will go inside the box. For {$d['buyer']}, packaging is not only a container; it is the first sales argument customers see before they touch the product. A weak box can make a capable product look ordinary, while a well-built paper package can help the item feel safer, clearer, and more valuable.</p>
<p>{$d['intro_2']} The goal of this {$d['keyword']} is to make the product easier to present, easier to protect, and easier to order in bulk. Each project can be adjusted for retail display, export cartons, e-commerce fulfillment, distributor programs, promotional sets, or private label production. This is especially important for brands that need one reliable structure across multiple SKUs while still keeping enough visual difference between each product line.</p>

{$image_1}

<h2>Box Structure and Opening Experience</h2>
<p>{$d['structure_1']} The structure can be developed as {$d['structure_options']} depending on product weight, retail channel, and unboxing expectation. A good structure should hold the product in a fixed position, reduce movement during shipping, and give the customer a clear first impression when the package is opened. If the product is fragile or premium, the design can include a custom tray, divider, pull tab, paper sleeve, window, or reinforced bottom.</p>
<p>{$d['structure_2']} For buyers comparing different formats, {$structure_link} can be used as a reference point for structure planning. The final engineering should consider how the product is inserted by the packing team, how fast the box can be assembled, whether the package needs to stand on shelf, and whether the end user will keep the box after purchase. This practical thinking helps the packaging look refined without becoming slow or expensive to produce.</p>

<h2>Suitable Paper Materials</h2>
<p>{$d['material_1']} Common material choices include {$d['paper_type']}. The best option depends on the product weight, desired surface texture, target price point, and brand positioning. Smooth coated paper is useful for detailed printing and clean color blocks, while kraft paper gives a natural impression. Rigid board is stronger for premium sets, and corrugated paper can be used when protection or export handling is more important.</p>
<p>{$d['material_2']} Material thickness should be chosen after measuring the product, checking the insert design, and understanding how the goods will be shipped. A light retail carton may work for small items, but heavier or fragile products need stronger board and better internal support. For buyers planning a full packaging program, our {$material_link} guide can help compare board types, paper finishes, and practical use cases before confirming production.</p>

{$image_2}

<h2>Practical Applications</h2>
<p>{$d['application_1']} This packaging can be used for {$d['applications']}. In real wholesale projects, one box style may need to serve multiple product variants, so the artwork system, label area, and insert design should be flexible. For example, a brand may keep the same structure but change the color, product name, barcode, dosage panel, size mark, or model number across different SKUs.</p>
<p>{$d['application_2']} Related buyers often compare this solution with {$related_a} or {$related_b} when building a wider packaging collection. Linking these product groups together helps the customer see alternative structures without leaving the same packaging category. It also supports a cleaner internal website structure because each product page connects to solutions that share the same buyer intent, material logic, or retail environment.</p>

<h2>Custom Size, Logo, Color, and Insert Options</h2>
<p>{$d['custom_1']} Every box can be customized by length, width, height, opening direction, insert format, color palette, logo placement, and printed information. If the product already has a bottle, jar, pouch, cable, case, pencil set, or gift component, the first step is to measure the actual item and decide how tightly it should sit inside the package. This prevents wasted space and improves the feeling of precision when the customer opens the box.</p>
<p>{$d['custom_2']} Brand colors can be matched through CMYK or Pantone printing. Logo printing can be simple and clean for minimalist brands, or more decorative with foil, embossing, debossing, or spot UV for premium products. Inserts can be made from paperboard, corrugated board, EVA, foam, molded pulp, or specialty trays depending on the required protection level and sustainability target. The same structure can also be adapted for seasonal campaigns or distributor-exclusive versions.</p>

<h2>Printing and Finishing Techniques</h2>
<p>{$d['printing_1']} Offset printing is commonly used for stable color reproduction, fine text, ingredient panels, icons, and retail graphics. Digital proofing or sampling can be used before mass production when the artwork is new. For larger orders, offset printing gives better consistency across thousands of boxes and allows the design team to control details such as solid color areas, gradients, small warnings, QR codes, and barcode readability.</p>
<p>{$d['printing_2']} Finishing options include {$d['printing']}. Matte lamination can make the package feel calm and premium, gloss lamination can strengthen color impact, and soft-touch lamination creates a smoother hand feel. Foil stamping is useful for logos and luxury details, while embossing or debossing adds tactile depth. Spot UV can highlight patterns, product names, or visual accents without covering the entire surface.</p>

{$image_3}

<h2>B2B Benefits for Wholesale and OEM Projects</h2>
<p>{$d['b2b_1']} For B2B customers, the value of this packaging is not limited to appearance. The box must be repeatable in production, easy to assemble, efficient to pack, and suitable for transportation. A stable design helps reduce packing mistakes, improves inventory control, and supports consistent presentation across distributors, online channels, retail shelves, trade shows, and export shipments.</p>
<p>{$d['b2b_2']} Bulk production also allows brands to control unit cost while keeping a professional finish. When the box structure is planned correctly, the same die-line can often support several product variations. This is useful for OEM and ODM manufacturers that handle private label orders, because each client can receive a different printed identity without requiring a completely new packaging engineering process every time.</p>

{$image_4}

<h2>Ordering and Production Process</h2>
<p>{$d['process_1']} The order process usually begins with product dimensions, target quantity, artwork files, material preference, and any required insert or finishing method. After reviewing the product and sales channel, we recommend a structure, prepare a quotation, and confirm the printing approach. If needed, a sample can be produced before mass production so the buyer can check size, opening feel, material thickness, color direction, and insert fit.</p>
<p>{$d['process_2']} After sample approval, production moves through printing, lamination, die-cutting, folding, gluing, insert assembly, quality checking, packing, and shipping preparation. For export orders, carton packing and handling requirements should be discussed early. This makes the final shipment easier to receive, store, and distribute, especially when the buyer is managing multiple packaging SKUs at the same time.</p>

<h2>Why Choose VPN Paper Box Manufacturer</h2>
<p>VPN Paper Box Manufacturer supports international buyers with factory-direct custom paper packaging from Vietnam. Our team can help with structure planning, material selection, artwork adjustment, sampling, mass production, and quality inspection. For {$d['title']}, we focus on making the packaging practical for real packing operations while still giving the brand a polished retail appearance.</p>
<p>Working with a direct manufacturer also helps buyers communicate technical requirements more clearly. Instead of choosing from a fixed catalog only, you can adjust the size, paper, insert, color, finishing, and packing method around the product. This is useful for importers, brand owners, agencies, and OEM/ODM suppliers that need reliable production for repeated wholesale orders.</p>

<h2>Request a Quote for {$d['title']}</h2>
<p>Send your product size, reference image, artwork file, expected quantity, and finishing requirements to {$cta_link}. Our team will review the product, suggest a suitable paper box structure, and prepare a quotation based on material, size, insert, printing, finishing, and order volume. The minimum order quantity for this product is 1000 boxes.</p>
HTML;
}

function vpn_normalize_product_data( array $d ): array {
	$d['intro_1'] = $d['title'] . ' is planned for ' . strtolower( $d['industry_heading'] ) . ', where the packaging must match the product value, buyer expectation, and retail environment.';
	$d['intro_2'] = 'For ' . $d['buyer'] . ', the box needs to support product protection, brand recognition, bulk packing efficiency, and a presentation style that can work across international markets.';
	$d['structure_1'] = 'The structure of this ' . $d['keyword'] . ' should be selected according to product weight, opening experience, display method, and the level of protection required during shipping.';
	$d['structure_2'] = 'A well-engineered structure also helps the packing team work faster because the product position, insert format, folding line, and closure method are all confirmed before mass production.';
	$d['material_1'] = 'The paper material for ' . strtolower( $d['title'] ) . ' should balance printing quality, box strength, surface feel, and the cost target for wholesale production.';
	$d['material_2'] = 'Material selection should also consider whether the product is fragile, heavy, luxury-positioned, eco-focused, or intended for fast retail turnover.';
	$d['application_1'] = 'This packaging can be used for ' . $d['applications'] . '.';
	$d['application_2'] = 'In real B2B projects, the same structure can often be adjusted for different sizes, colors, formulas, models, or product variants while keeping the brand system consistent.';
	$d['custom_1'] = $d['custom_1'] ?? 'The packaging should be measured around the actual product, insert, information panel, and retail display requirement before artwork is finalized.';
	$d['custom_2'] = $d['custom_2'] ?? 'Logo position, color palette, product information, insert layout, and special finishing can be customized around each brand and sales channel.';
	$d['printing_1'] = 'Printing for ' . strtolower( $d['title'] ) . ' should keep the logo, product name, technical information, barcode, and visual hierarchy clear at retail distance.';
	$d['printing_2'] = 'The finishing plan should support the brand position: clean and minimal for practical wholesale lines, or more tactile and premium for gift, beauty, and luxury products.';
	$d['b2b_1'] = 'For B2B customers, this packaging must be repeatable, easy to assemble, consistent in quality, and practical for warehouse handling, export cartons, and distributor inventory.';
	$d['b2b_2'] = 'A stable packaging system also helps brands launch multiple SKUs with lower development risk because artwork and inserts can be adjusted without rebuilding every detail from zero.';
	$d['process_1'] = 'The order process starts with product dimensions, quantity, artwork, material preference, box structure, insert requirement, and any finishing process that should appear on the final package.';
	$d['process_2'] = 'After the structure and sample are approved, the project moves through printing, surface finishing, die-cutting, folding, gluing, insert assembly, inspection, carton packing, and shipment preparation.';

	return $d;
}

$products = array(
	'custom-ampoule-packaging-box' => array(
		'title' => 'CUSTOM AMPOULE PACKAGING BOX',
		'keyword' => 'ampoule packaging box',
		'industry_heading' => 'Skincare Ampoules and Beauty Treatment Products',
		'buyer' => 'skincare brands, serum manufacturers, beauty distributors, and OEM cosmetic factories',
		'category_url' => '/product-category/cosmetic-packaging-boxes/',
		'category_anchor' => 'custom cosmetic packaging boxes',
		'structure_url' => '/product/custom-supplement-drawer-packaging-box/',
		'structure_anchor' => 'drawer-style packaging for small bottle sets',
		'material_anchor' => 'paper materials for cosmetic boxes',
		'related' => array( array( '/product/custom-cosmetic-packaging-box/', 'custom cosmetic packaging box' ), array( '/product/custom-supplement-drawer-packaging-box/', 'custom supplement drawer packaging box' ) ),
		'cta_anchor' => 'request an ampoule packaging quotation',
		'image_caption' => 'Custom ampoule packaging box with a vertical structure for skincare serum and beauty treatment products.',
		'intro_1' => 'Ampoule packaging needs to protect small fragile containers while communicating a clean, clinical, and premium beauty image.',
		'intro_2' => 'Because ampoules are often sold as concentrated treatment programs, the outer box must help customers understand value, formula positioning, quantity, and usage sequence.',
		'structure_options' => 'a tuck-end carton, sleeve box, drawer box, small rigid box, or set box with inner tray',
		'structure_1' => 'The most important structural requirement is keeping each ampoule stable and separated from impact.',
		'structure_2' => 'For multi-piece skincare programs, a drawer or sleeve structure can create a more premium routine-based unboxing experience.',
		'material_1' => 'Ampoule products usually need paper that prints fine cosmetic details clearly and supports protective inserts.',
		'material_2' => 'If the ampoule is glass, the inner support should be planned together with the outer board instead of being treated as an afterthought.',
		'application_1' => 'Ampoule boxes are suitable for concentrated serums, anti-aging treatments, brightening ampoules, repair programs, sample vials, and professional salon skincare kits.',
		'application_2' => 'For beauty brands with multiple formulas, the same box can use different color codes for hydration, repair, whitening, anti-aging, or sensitive-skin lines.',
		'applications' => 'skincare ampoules, serum vials, beauty sample tubes, professional treatment sets, salon skincare kits, and cosmetic trial programs',
		'custom_1' => 'Customization should begin with the diameter and height of the ampoule, the number of vials per set, and the way the user removes each piece.',
		'custom_2' => 'Clean typography, calm color systems, and accurate logo placement are especially important for premium skincare because the package must look trustworthy before ingredients are read.',
		'printing_1' => 'Cosmetic ampoule packaging often requires accurate small text for ingredients, usage instructions, batch information, and compliance icons.',
		'printing_2' => 'A subtle foil logo, embossed brand mark, or spot UV treatment can make the box feel refined without making the design too busy.',
		'b2b_1' => 'For skincare B2B projects, stable packaging reduces vial movement, improves packing speed, and keeps the product presentation consistent across wholesale cartons.',
		'b2b_2' => 'Private label manufacturers can reuse the structure across multiple formulas while changing artwork, color, and product claims.',
		'process_1' => 'Please provide ampoule size, number of ampoules per box, bottle material, insert preference, artwork, and expected quantity.',
		'process_2' => 'We check the vial fit carefully before mass production because small glass products need precise insert tolerance.',
		'feature' => 'Custom logo, ampoule protection, inner tray, compact skincare presentation',
		'industrial_use' => 'Skincare, Cosmetic, Beauty Treatment, Serum, OEM/ODM Packaging',
		'paper_type' => 'Ivory Paper / Art Paper / Duplex Board / Rigid Greyboard / Kraft Paper Optional',
		'box_type' => 'Ampoule Packaging Box with Custom Insert',
		'shape' => 'Rectangle / Vertical Box / Customized Shape',
		'accessories' => 'Paper tray / EVA insert / Foam insert / Carton divider / Instruction card',
		'liner_type' => 'Paper insert / EVA insert / Foam insert / Custom ampoule tray',
		'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Spot UV, Matte Lamination, Soft-touch Lamination',
		'color' => 'White / Pastel / Clinical Color / CMYK / Pantone / Customized Color',
	),
	'custom-charging-cable-packaging-box' => array(
		'title' => 'CUSTOM CHARGING CABLE PACKAGING BOX',
		'keyword' => 'charging cable packaging box',
		'industry_heading' => 'USB Cables and Mobile Electronics Accessories',
		'buyer' => 'electronics brands, mobile accessory wholesalers, Amazon sellers, and cable OEM factories',
		'category_url' => '/product-category/electronics-packaging-boxes/',
		'category_anchor' => 'electronics packaging boxes',
		'structure_url' => '/product/custom-phone-case-packaging-box/',
		'structure_anchor' => 'retail mobile accessory packaging',
		'material_anchor' => 'paperboard options for electronics packaging',
		'related' => array( array( '/product/custom-phone-case-packaging-box/', 'custom phone case packaging box' ), array( '/product/custom-corporate-gift-set-packaging-boxes/', 'corporate gift set packaging boxes' ) ),
		'cta_anchor' => 'request a cable packaging quote',
		'image_caption' => 'Custom charging cable packaging box for USB, Type-C, and mobile accessory retail display.',
		'intro_1' => 'Charging cable packaging must explain compatibility quickly while making a compact low-cost product look organized and reliable.',
		'intro_2' => 'Cable buyers often compare connector type, length, charging speed, warranty, and device compatibility, so the package needs strong front-panel communication.',
		'structure_options' => 'a tuck-end box, hang-tab retail box, sleeve carton, drawer box, or window display box',
		'structure_1' => 'The structure should hold the coiled cable neatly and prevent the product from shifting inside the box.',
		'structure_2' => 'For retail stores, a hang tab can be added; for online brands, a sleeve or drawer format can improve the opening experience.',
		'material_1' => 'Cable boxes usually need a balance of cost control, clean printing, and enough board strength for stacking.',
		'material_2' => 'If the cable is braided, premium, or bundled with an adapter, a thicker board or inner tray may be more suitable.',
		'application_1' => 'This box is suitable for Type-C cables, USB-A cables, Lightning cables, fast charging cables, braided cords, audio cables, and accessory bundles.',
		'application_2' => 'The same system can be adjusted for different cable lengths by changing the label panel and internal packing layout.',
		'applications' => 'Type-C cables, USB cables, Lightning cables, braided charging cords, fast charging cable sets, audio cables, and mobile accessory bundles',
		'custom_1' => 'The package should be sized around the cable coil diameter, paper insert, instruction leaflet, and any included adapter.',
		'custom_2' => 'Model labels, QR codes, warranty icons, connector diagrams, and compatibility marks can be added to improve retail clarity.',
		'printing_1' => 'Electronics packaging often uses clear icons, sharp contrast, and technical specification panels that must stay readable.',
		'printing_2' => 'Spot UV on icons or a foil logo can make the package look more professional while keeping production efficient.',
		'b2b_1' => 'For wholesale cable programs, consistent packaging helps organize many connector types and reduces confusion during picking and shipping.',
		'b2b_2' => 'A shared die-line can be reused for different cable colors, lengths, and connector types, which helps control tooling cost.',
		'process_1' => 'Please send cable length, packed coil size, connector type, display requirement, artwork, and quantity.',
		'process_2' => 'We confirm the packing method before production so the cable fits neatly without making the box larger than necessary.',
		'feature' => 'Custom logo, hang tab option, compact cable display, retail-ready accessory packaging',
		'industrial_use' => 'Electronics, Mobile Accessories, USB Cable, Retail, E-commerce',
		'paper_type' => 'Ivory Paper / Duplex Board / Kraft Paper / Corrugated Paper Optional',
		'box_type' => 'Charging Cable Retail Packaging Box',
		'shape' => 'Rectangle / Slim Box / Customized Shape',
		'accessories' => 'Hang tab / Paper insert / Window patch / Instruction card / Barcode label area',
		'liner_type' => 'Paper tray / Cable holder / Custom folded insert',
		'printing' => 'CMYK Printing, Pantone Printing, Spot UV, Matte Lamination, Glossy Lamination, Foil Stamping',
		'color' => 'Black / White / Tech Grey / CMYK / Pantone / Customized Color',
	),
);

$products += array(
	'custom-colored-pencil-packaging-box' => array_merge( $products['custom-charging-cable-packaging-box'], array(
		'title' => 'CUSTOM COLORED PENCIL PACKAGING BOX',
		'keyword' => 'colored pencil packaging box',
		'industry_heading' => 'Colored Pencil Sets and Art Supplies',
		'buyer' => 'stationery brands, art supply distributors, school product wholesalers, and creative kit manufacturers',
		'category_url' => '/product-category/stationery-packaging-boxes/',
		'category_anchor' => 'stationery packaging boxes',
		'structure_url' => '/product/custom-crayon-packaging-box/',
		'structure_anchor' => 'children art supply packaging structures',
		'related' => array( array( '/product/custom-crayon-packaging-box/', 'custom crayon packaging box' ), array( '/product/custom-corporate-gift-set-packaging-boxes/', 'custom corporate gift set packaging boxes' ) ),
		'cta_anchor' => 'request a colored pencil box quote',
		'image_caption' => 'Custom colored pencil packaging box for art supply sets, school stationery, and retail display.',
		'intro_1' => 'Colored pencil packaging must organize many pieces neatly while showing creativity, color range, and product value.',
		'intro_2' => 'For school, artist, and gift channels, the package should protect pencil tips and make the full set easy to understand.',
		'structure_options' => 'a folding carton, window box, drawer box, lid and base box, or sleeve set with pencil tray',
		'structure_1' => 'The structure should keep pencils aligned and stop them from breaking during storage, shipping, or repeated opening.',
		'structure_2' => 'Drawer and tray formats are useful for premium art sets, while folding cartons work well for high-volume school products.',
		'applications' => '12-color pencils, 24-color pencils, 36-color sets, sketch pencils, watercolor pencils, school art kits, and gift stationery sets',
		'custom_1' => 'The package should be sized around the number of pencils, the pencil diameter, the tray spacing, and the way the user views the color order.',
		'custom_2' => 'Color charts, age information, safety marks, and illustrated front panels can be customized so the box feels suitable for school, hobby, or artist retail channels.',
		'feature' => 'Custom logo, color chart, pencil tray, retail art supply presentation',
		'industrial_use' => 'Stationery, Art Supplies, School Products, Gift, Retail',
		'paper_type' => 'Ivory Paper / Kraft Paper / Duplex Board / Rigid Greyboard',
		'box_type' => 'Colored Pencil Packaging Box with Tray',
		'accessories' => 'Paper tray / Color chart / Window patch / Sleeve / Instruction card',
		'liner_type' => 'Paper insert / Pencil tray / Custom divider',
		'color' => 'Bright Color / CMYK / Pantone / Customized Color',
	) ),
	'custom-corporate-gift-set-packaging-boxes' => array_merge( $products['custom-charging-cable-packaging-box'], array(
		'title' => 'CUSTOM CORPORATE GIFT SET PACKAGING BOXES',
		'keyword' => 'corporate gift set packaging boxes',
		'industry_heading' => 'Corporate Gift Sets and Branded Business Campaigns',
		'buyer' => 'corporate buyers, agencies, event organizers, HR teams, gift suppliers, and promotional distributors',
		'category_url' => '/product-category/gift-packaging-boxes/',
		'category_anchor' => 'custom gift packaging boxes',
		'structure_url' => '/product/custom-supplement-drawer-packaging-box/',
		'structure_anchor' => 'drawer and rigid gift box structures',
		'related' => array( array( '/product/custom-dinnerware-packaging-box/', 'custom dinnerware packaging box' ), array( '/product/custom-cosmetic-packaging-box/', 'custom cosmetic packaging box' ) ),
		'cta_anchor' => 'request a corporate gift box quotation',
		'image_caption' => 'Custom corporate gift set packaging boxes for branded business gifts and premium promotional campaigns.',
		'intro_1' => 'Corporate gift packaging must make several products feel like one complete branded experience.',
		'intro_2' => 'For event campaigns, employee welcome kits, and VIP customer gifts, the box often carries the emotional value of the whole project.',
		'structure_options' => 'a magnetic rigid box, drawer box, lid and base box, book-style box, or handle gift box',
		'structure_1' => 'The structure should separate different gift items cleanly while keeping the presentation stable when the lid is opened.',
		'structure_2' => 'A rigid gift structure can make the box reusable and more memorable for business recipients.',
		'applications' => 'employee kits, conference gifts, wine sets, stationery sets, technology bundles, beauty gifts, VIP client packages, and holiday campaigns',
		'custom_1' => 'The box should be sized around the full gift layout, including the largest item, insert thickness, greeting card, and any ribbon or closure detail.',
		'custom_2' => 'Corporate colors, event messages, recipient cards, and logo placement can be customized so the box feels connected to a specific campaign instead of a generic gift package.',
		'feature' => 'Custom logo, premium rigid structure, multiple compartments, corporate gift presentation',
		'industrial_use' => 'Gift, Corporate, Promotional, Retail, Luxury Packaging',
		'paper_type' => 'Art Paper / Specialty Paper / Textured Paper / Kraft Paper Optional / Rigid Greyboard',
		'box_type' => 'Corporate Gift Set Packaging Box',
		'accessories' => 'Ribbon / Magnetic closure / EVA insert / Paper tray / Foam insert / Greeting card',
		'liner_type' => 'EVA insert / Foam insert / Paper tray / Custom gift divider',
		'color' => 'Brand Color / Grey / Black / White / CMYK / Pantone / Customized Color',
	) ),
	'custom-cosmetic-packaging-box' => array_merge( $products['custom-ampoule-packaging-box'], array(
		'title' => 'CUSTOM COSMETIC PACKAGING BOX',
		'keyword' => 'cosmetic packaging box',
		'industry_heading' => 'Skincare, Makeup, and Beauty Retail Products',
		'buyer' => 'beauty brands, cosmetic distributors, private label suppliers, and skincare OEM/ODM factories',
		'related' => array( array( '/product/custom-ampoule-packaging-box/', 'custom ampoule packaging box' ), array( '/product/custom-cosmetic-paper-bag/', 'custom cosmetic paper bag' ) ),
		'cta_anchor' => 'request a cosmetic packaging quote',
		'image_caption' => 'Custom cosmetic packaging box for skincare, makeup, beauty products, and branded retail display.',
		'applications' => 'face creams, serums, toners, lotions, makeup, facial masks, beauty sample kits, and skincare gift sets',
		'custom_1' => 'The package should be measured around the jar, bottle, tube, palette, or beauty set so the product sits securely and presents well when opened.',
		'custom_2' => 'Ingredient panels, product claims, certification icons, shade names, and brand colors can be customized for each cosmetic line while keeping the same packaging family.',
		'feature' => 'Custom logo, premium cosmetic presentation, retail display, optional insert',
		'industrial_use' => 'Cosmetic, Skincare, Makeup, Beauty, Retail',
		'box_type' => 'Cosmetic Packaging Box',
		'accessories' => 'Paper tray / EVA insert / Sleeve / Window patch / Product card',
		'liner_type' => 'Paper insert / EVA insert / Foam insert / Custom cosmetic tray',
	) ),
	'custom-cosmetic-paper-bag' => array_merge( $products['custom-ampoule-packaging-box'], array(
		'title' => 'CUSTOM COSMETIC PAPER BAG',
		'keyword' => 'cosmetic paper bag',
		'industry_heading' => 'Beauty Retail Shopping and Cosmetic Gift Packaging',
		'buyer' => 'cosmetic stores, skincare brands, perfume retailers, salons, and promotional beauty suppliers',
		'category_url' => '/product-category/paper-bags/',
		'category_anchor' => 'custom paper bags',
		'structure_url' => '/product/custom-cosmetic-packaging-box/',
		'structure_anchor' => 'matching cosmetic product boxes',
		'related' => array( array( '/product/custom-cosmetic-packaging-box/', 'custom cosmetic packaging box' ), array( '/product/custom-ampoule-packaging-box/', 'custom ampoule packaging box' ) ),
		'cta_anchor' => 'request a cosmetic paper bag quote',
		'image_caption' => 'Custom cosmetic paper bag for skincare boutiques, beauty retail stores, and gift sets.',
		'structure_options' => 'a rope-handle paper bag, ribbon-handle gift bag, flat-handle shopping bag, or reinforced boutique bag',
		'applications' => 'skincare sets, perfume boxes, makeup products, beauty samples, salon gifts, boutique retail purchases, and promotional campaigns',
		'custom_1' => 'The paper bag should be sized around the product box or gift set it will carry, including gusset width, handle length, and bottom reinforcement.',
		'custom_2' => 'Handle material, ribbon color, side gusset, inner printing, logo position, and shopping campaign message can be customized for beauty retail and gifting.',
		'feature' => 'Custom logo, reinforced bottom, premium handle, cosmetic retail presentation',
		'industrial_use' => 'Cosmetic, Beauty Retail, Gift, Shopping, Promotional Packaging',
		'paper_type' => 'Art Paper / Kraft Paper / Ivory Paper / Specialty Paper',
		'box_type' => 'Cosmetic Paper Bag',
		'accessories' => 'Cotton handle / Rope handle / Ribbon handle / Reinforced base / Hang tag',
		'liner_type' => 'Reinforced paper bottom / Folded gusset / Custom paper support',
	) ),
	'custom-crayon-packaging-box' => array_merge( $products['custom-charging-cable-packaging-box'], array(
		'title' => 'CUSTOM CRAYON PACKAGING BOX',
		'keyword' => 'crayon packaging box',
		'industry_heading' => 'Children Art Supplies and School Coloring Products',
		'buyer' => 'kids stationery brands, school suppliers, educational product distributors, and crayon manufacturers',
		'related' => array( array( '/product/custom-colored-pencil-packaging-box/', 'custom colored pencil packaging box' ), array( '/product/custom-corporate-gift-set-packaging-boxes/', 'custom corporate gift set packaging boxes' ) ),
		'cta_anchor' => 'request a crayon packaging quote',
		'image_caption' => 'Custom crayon packaging box for children art supplies, school kits, and colorful retail display.',
		'applications' => 'crayon sets, oil pastels, chalk sets, school art packs, classroom kits, children gift sets, and promotional coloring products',
		'custom_1' => 'The package should be sized around the number of crayons, the crayon diameter, and the divider layout so each color stays organized.',
		'custom_2' => 'Bright graphics, child-friendly icons, non-toxic marks, color count labels, and safety information can be customized for school and retail markets.',
		'feature' => 'Custom logo, colorful kids design, crayon divider, retail display',
		'industrial_use' => 'Stationery, Kids Art Supplies, School Products, Retail',
		'box_type' => 'Crayon Packaging Box',
		'accessories' => 'Paper divider / Color chart / Window patch / Safety information panel',
		'liner_type' => 'Paper divider / Crayon tray / Custom folded insert',
	) ),
	'custom-dinnerware-packaging-box' => array_merge( $products['custom-charging-cable-packaging-box'], array(
		'title' => 'CUSTOM DINNERWARE PACKAGING BOX',
		'keyword' => 'dinnerware packaging box',
		'industry_heading' => 'Ceramic Dinnerware, Tableware, and Homeware Sets',
		'buyer' => 'tableware brands, ceramic factories, homeware distributors, hotel suppliers, and gift set buyers',
		'category_url' => '/product-category/retail-packaging-boxes/',
		'category_anchor' => 'retail packaging boxes',
		'structure_url' => '/product/custom-corporate-gift-set-packaging-boxes/',
		'structure_anchor' => 'rigid gift set packaging structures',
		'related' => array( array( '/product/custom-corporate-gift-set-packaging-boxes/', 'corporate gift set packaging boxes' ), array( '/product/custom-cosmetic-paper-bag/', 'custom cosmetic paper bag' ) ),
		'cta_anchor' => 'request a dinnerware packaging quote',
		'image_caption' => 'Custom dinnerware packaging box for ceramic tableware, bowls, plates, mugs, and homeware gift sets.',
		'applications' => 'ceramic plates, bowls, mugs, tea sets, dinner sets, kitchenware gifts, hotel tableware, and homeware retail bundles',
		'custom_1' => 'The package should be sized around the tableware diameter, stack height, product weight, and divider thickness before artwork is finalized.',
		'custom_2' => 'Protective inserts, fragile labels, handle options, brand patterns, and gift sleeve artwork can be customized for homeware retail and export shipments.',
		'feature' => 'Custom logo, fragile product protection, divider insert, tableware gift presentation',
		'industrial_use' => 'Dinnerware, Ceramic, Tableware, Homeware, Gift, Retail',
		'paper_type' => 'Corrugated Paper / Duplex Board / Kraft Paper / Rigid Greyboard',
		'box_type' => 'Dinnerware Packaging Box with Protective Divider',
		'accessories' => 'Corrugated divider / Paper pulp tray / EVA insert / Handle / Fragile label',
		'liner_type' => 'Corrugated partition / Paper tray / EVA insert / Molded pulp insert',
	) ),
	'custom-phone-case-packaging-box' => array_merge( $products['custom-charging-cable-packaging-box'], array(
		'title' => 'CUSTOM PHONE CASE PACKAGING BOX',
		'keyword' => 'phone case packaging box',
		'industry_heading' => 'Mobile Phone Cases and Accessory Retail',
		'buyer' => 'phone case brands, accessory wholesalers, online sellers, and mobile product OEM suppliers',
		'related' => array( array( '/product/custom-charging-cable-packaging-box/', 'custom charging cable packaging box' ), array( '/product/custom-corporate-gift-set-packaging-boxes/', 'custom corporate gift set packaging boxes' ) ),
		'cta_anchor' => 'request a phone case packaging quote',
		'image_caption' => 'Custom phone case packaging box for mobile accessory retail display and e-commerce sales.',
		'applications' => 'silicone phone cases, TPU cases, clear cases, leather cases, protective cases, MagSafe-compatible cases, and screen protector kits',
		'custom_1' => 'The package should be sized around the phone case model, hanging display requirement, product window, and label area for device compatibility.',
		'custom_2' => 'Model stickers, barcode panels, QR codes, window shape, product icons, and logo style can be customized for different phone series and retail channels.',
		'feature' => 'Custom logo, hang tab option, window display, model label area',
		'industrial_use' => 'Electronics, Mobile Accessories, Phone Case, Retail',
		'paper_type' => 'Ivory Paper / Duplex Board / Kraft Paper / PET Window Optional',
		'box_type' => 'Phone Case Retail Packaging Box',
		'accessories' => 'Hang tab / Window patch / Paper insert / Model sticker / Barcode label area',
		'liner_type' => 'Paper insert / Window support / Custom phone case holder',
	) ),
	'custom-supplement-drawer-packaging-box' => array_merge( $products['custom-ampoule-packaging-box'], array(
		'title' => 'CUSTOM SUPPLEMENT DRAWER PACKAGING BOX',
		'keyword' => 'supplement drawer packaging box',
		'industry_heading' => 'Vitamins, Supplements, and Wellness Kits',
		'buyer' => 'supplement brands, nutraceutical companies, wellness distributors, and private label health product manufacturers',
		'category_url' => '/product-category/health-supplement-packaging-boxes/',
		'category_anchor' => 'health supplement packaging boxes',
		'structure_url' => '/product/custom-ampoule-packaging-box/',
		'structure_anchor' => 'small bottle packaging with custom inserts',
		'related' => array( array( '/product/custom-ampoule-packaging-box/', 'custom ampoule packaging box' ), array( '/product/custom-cosmetic-packaging-box/', 'custom cosmetic packaging box' ) ),
		'cta_anchor' => 'request a supplement drawer box quote',
		'image_caption' => 'Custom supplement drawer packaging box for vitamins, capsules, wellness kits, and health product sets.',
		'applications' => 'vitamin bottles, probiotics, collagen products, herbal supplements, capsule packs, wellness kits, and subscription health boxes',
		'custom_1' => 'The drawer box should be sized around the bottle diameter, bottle height, insert thickness, sleeve clearance, and expected unboxing feel.',
		'custom_2' => 'Dosage panels, ingredient notes, certification marks, batch information, and calm wellness color systems can be customized for each supplement formula.',
		'feature' => 'Custom logo, slide-out drawer structure, bottle insert, premium wellness presentation',
		'industrial_use' => 'Supplement, Vitamin, Nutraceutical, Wellness, Health Product Packaging',
		'paper_type' => 'Rigid Greyboard / Ivory Paper / Art Paper / Duplex Board',
		'box_type' => 'Supplement Drawer Packaging Box',
		'accessories' => 'Ribbon pull / Paper tray / EVA insert / Foam insert / Instruction card',
		'liner_type' => 'Paper insert / EVA insert / Foam insert / Custom bottle tray',
	) ),
);

$updated = 0;

foreach ( $products as $slug => $data ) {
	$product = get_page_by_path( $slug, OBJECT, 'product' );

	if ( ! $product ) {
		echo "Missing product: {$slug}\n";
		continue;
	}

	wp_update_post(
		array(
			'ID'           => $product->ID,
			'post_status'  => 'publish',
			'post_content' => vpn_rich_content( $product->ID, $data ),
		)
	);

	update_post_meta( $product->ID, '_custom_box_product_specs', vpn_specs( $data ) );
	update_post_meta( $product->ID, '_vpn_rich_content_update', current_time( 'mysql' ) );

	if ( function_exists( 'wc_delete_product_transients' ) ) {
		wc_delete_product_transients( $product->ID );
	}

	echo "Updated rich content and specs: {$data['title']} (#{$product->ID})\n";
	$updated++;
}

echo "Total updated: {$updated}\n";
