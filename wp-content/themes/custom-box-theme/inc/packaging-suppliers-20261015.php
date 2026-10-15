<?php
/** Supplier intent on the existing manufacturer page; owner-confirmed order policies. */
defined('ABSPATH') || exit;
add_filter('body_class',static function($classes){
    if(function_exists('custom_box_is_packaging_money_page')&&custom_box_is_packaging_money_page()){$classes[]='vpn-supplier-context';}
    return $classes;
});

function vpn_packaging_supplier_comparison(): array {
    return array(
        array('Product fit', 'Share the packed product dimensions, weight and number of items.', 'Internal size, loading clearance, tray movement and access to each item.'),
        array('Material and construction', 'Confirm the paperboard, wrap, insert and preferred box structure.', 'A sample specification that identifies the agreed construction and materials.'),
        array('Quotation scope', 'Ask which printing, finishing, tooling, sample, packing and freight charges are included.', 'A written quote with quantities and inclusions stated separately.'),
        array('Sample approval', 'Review fit, assembly, opening, artwork placement and the selected finish.', 'An approved sample and artwork reference for bulk production.'),
        array('Production and QC', 'Agree the order schedule and the quality checks at each stage.', 'Project-specific checks for dimensions, print, finishing, assembly and packing.'),
        array('Delivery and reorders', 'Provide the receiving country, target date and packing requirements.', 'Separate production and transit schedules; confirm specifications again when reordering.'),
    );
}
function vpn_packaging_supplier_faqs(): array {
    return array(
        array('Is VPN a packaging manufacturer or a supplier?', 'VPN manufactures and supplies custom paper packaging in Vietnam. The range includes rigid boxes, folding cartons, drawer boxes, corrugated mailers and paper bags, with size, inserts, printing and finishing developed for each order.'),
        array('What should I compare when choosing packaging suppliers?', 'Compare product fit, structure, material, quotation scope, the sample approval process, production schedule, quality checks and packing. Use the same brief and quantity when comparing quotations so the specifications and included charges can be assessed together.'),
        array('What is the minimum order quantity?', 'The general minimum order is 1,000 units. Share the packaging type, dimensions, quantity and delivery country so the project specification and quotation can be reviewed.'),
        array('Can you make any box size or design?', 'VPN makes packaging to your requested design and specifications. Share the product dimensions, layout, reference style and preferred finish. The structure and product fit are confirmed during sampling; design support is available.'),
        array('How much does a custom sample cost?', 'Custom samples are made to the requested design. The sample fee depends on the design requirements. Orders over 5,000 pcs qualify for a refund of 50% of the sample fee.'),
        array('How long do samples and production take?', 'Sampling takes 7–15 days depending on the order. Production takes 7–45 days after sample approval, depending on the order. Transit time is separate; provide your receiving country and target date for the project schedule.'),
        array('How do you check quality and pack the boxes?', 'Quality is checked at each production stage. Finished boxes are packed in cartons or shrink-wrapped bundles according to the order requirements. Confirm the product-specific checks and packing method with the approved specification.'),
        array('Can you print our logo and brand colors?', 'Custom logo printing, CMYK artwork, Pantone color references and finishes such as foil, embossing, debossing, spot UV and lamination can be discussed. Confirm the selected materials, color reference and finish on the sample and artwork proof.'),
        array('Do you supply international buyers?', 'VPN supports international B2B packaging enquiries from brands, importers, distributors and agencies. Production is based in Vietnam. Share the delivery country so packing, freight scope and the delivery schedule can be discussed for your order.'),
        array('What if I do not know the box specifications yet?', 'Send your email, product type, approximate quantity, destination and a reference image or description. Add product dimensions when available. Sales can discuss structure, insert and design options before the final specification is approved.'),
    );
}
function vpn_packaging_supplier_factory_image(string $key): array {
    static $catalog;
    if(null===$catalog){$catalog=json_decode((string)file_get_contents(__DIR__.'/packaging-supplier-factory-20261015.json'),true)?:array();}
    if(empty($catalog[$key])){return array();}
    $image=$catalog[$key];$relative='/assets/images/supplier-factory-20261015/'.basename($image['file']);
    if(!is_readable(get_template_directory().$relative)){return array();}
    $image['url']=get_template_directory_uri().$relative;
    $image['html']=sprintf('<img class="vpn-factory-gallery__image" src="%s" alt="%s" width="%d" height="%d" loading="lazy" decoding="async">',esc_url($image['url']),esc_attr($image['alt']),(int)$image['width'],(int)$image['height']);
    return $image;
}
function vpn_packaging_supplier_factory_gallery(): void {
    $images=array();foreach(array('printing','cutting','die-cutting','laminating','finishing','storage') as $key){$image=vpn_packaging_supplier_factory_image($key);if($image){$images[]=$image;}}
    $context=array('title'=>'Printing, Cutting and Box Finishing','description'=>'Explore the equipment, finishing workstations and carton storage used in our packaging workshop. Share your product dimensions, artwork and delivery destination so we can discuss the right construction and production steps for your order.');
    get_template_part('template-parts/packaging-supplier-factory',null,array('context'=>$context,'images'=>$images));
}
add_action('wp_enqueue_scripts', static function () {
    if (!function_exists('custom_box_is_packaging_money_page') || !custom_box_is_packaging_money_page()) { return; }
    $file='/assets/css/packaging-suppliers-20261015.css';
    wp_enqueue_style('vpn-packaging-suppliers',get_template_directory_uri().$file,array('main-style'),filemtime(get_template_directory().$file));
}, 35);
add_filter('rank_math/json_ld', static function ($data) {
    if (!function_exists('custom_box_is_packaging_money_page') || !custom_box_is_packaging_money_page() || !is_array($data)) { return $data; }
    $organization=custom_box_get_business_schema();
    // One stable business entity, with details visible on this page; no invented ratings or commercial offer.
    unset($organization['priceRange']);
    foreach($data as $key=>$node){
        $types=is_array($node)?(array)($node['@type']??array()):array();
        if(array_intersect($types,array('Organization','LocalBusiness','FAQPage'))){unset($data[$key]);continue;}
        if(in_array('WebPage',$types,true)){
            $data[$key]['about']=array('@id'=>home_url('/#organization'));
            $data[$key]['publisher']=array('@id'=>home_url('/#organization'));
            $data[$key]['dateModified']='2026-10-15';
        }
    }
    $data['schema-vpnPackagingSupplier']=$organization;
    return $data;
},90);
