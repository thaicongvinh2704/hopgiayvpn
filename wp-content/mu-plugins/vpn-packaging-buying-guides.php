<?php
/**
 * Plugin Name: VPN Packaging Buying Guides
 * Description: Buyer guidance and search metadata for selected WooCommerce categories and products.
 */

defined('ABSPATH') || exit;

function vpn_packaging_buying_pages(): array {
    return [
        'bird-nest-packaging-boxes' => [
            'heading' => "Custom Bird's Nest Packaging Boxes",
            'guide' => 'bird-nest-buying-guide',
            'anchor' => 'bird-nest-box-styles',
            'title' => "Custom Bird's Nest Packaging Boxes | Vietnam Manufacturer",
            'description' => "Order custom bird's nest gift boxes from Vietnam. Compare drawer and compartment styles for sealed jars, then send your sizes and quantity for a quote.",
            'intro' => "VPN Packaging makes custom paper gift boxes in Vietnam for sealed bird's nest jars and bottled drinks. Compare a sliding drawer with a compartmented display, then specify the jar dimensions, count and artwork for a bulk quote.",
        ],
        'custom-blue-bird-nest-drawer-gift-box' => [
            'title' => "Custom Bird's Nest Drawer Gift Box | Made in Vietnam",
            'description' => "Order a rigid bird's nest drawer gift box with a fitted jar tray from Vietnam. Send your jar dimensions, filled weight and quantity for a bulk quote.",
            'intro' => "VPN Packaging makes this rigid bird's nest drawer gift box in Vietnam. A sliding tray holds sealed jars inside an outer sleeve. Send a filled jar or its dimensions and weight so the tray, sleeve and pull tab can be checked together in a sample.",
            'file' => 'bird-nest-drawer.html',
            'reviewed_content' => 'bd68e40c9b3a8918a005683068dec5faded777944160cfb887eb4eef63812302',
        ],
        'custom-green-bird-nest-compartment-gift-box' => [
            'title' => "Custom Bird's Nest Box with Compartments | Vietnam",
            'description' => "Order bird's nest gift boxes from Vietnam with separate compartments for sealed jars or bottles. Send your layout and quantity for a bulk quote.",
            'intro' => "VPN Packaging makes compartmented bird's nest gift boxes in Vietnam for sealed jars or bottles. The open display can be laid out around the number and size of the containers, with space for a product card if needed. Approve the layout with the complete filled set before bulk production.",
            'file' => 'bird-nest-compartments.html',
            'reviewed_content' => '7b33547ac429e27f2f1d7a87eab4c14b820c0821c1d1b6e6523992dbc0802eb4',
        ],
        'corporate-gift-packaging' => [
            'heading' => 'Custom Corporate Gift Packaging',
            'guide' => 'packaging-buying-guide',
            'anchor' => 'corporate-gift-box-styles',
            'title' => 'Corporate Gift Packaging | Custom Boxes from Vietnam',
            'description' => 'Order custom corporate gift boxes from Vietnam. Compare rigid magnetic PR kits and corrugated gift mailers by contents, presentation and delivery plan.',
            'intro' => 'VPN Packaging makes custom corporate gift boxes in Vietnam for employee gifts, client programs and PR launches. Choose a rigid magnetic box for presentation or a corrugated mailer for a kit that will be sent to recipients. Share the contents, quantity by version and delivery schedule for a bulk quote.',
            'comparison' => 'Choose a corporate gift box by how the kit will be delivered',
            'formats' => [
                'custom-corporate-pr-kit-magnetic-box' => ['Magnetic PR kit box', 'For a launch or client gift where the order of presentation matters. Plan the insert, message card and lid clearance around the complete kit.'],
                'custom-corrugated-gift-set-mailer-box' => ['Corrugated gift set mailer', 'For gift sets sent to recipients. Plan item restraint, the finished packed weight and any outer shipping carton before approving the sample.'],
            ],
            'sample_heading' => 'Approve the gift set your recipients will open',
            'sample' => 'Use all the actual items in the sample, including the card and tissue. For employee or client programs with several versions, record which items go in each box and which parts of the artwork stay the same. A packed sample helps verify both the presentation and the packing steps.',
            'quote' => 'Send the item list, dimensions and packed weights, quantity by version, artwork, destination and date the gifts must arrive. Both examples list a minimum of 1,000 boxes. Confirm the quantity basis, sample fee and schedule in the quotation, including any split across designs or deliveries.',
        ],
        'perfume-packaging-boxes' => [
            'heading' => 'Custom Perfume Packaging Boxes',
            'guide' => 'packaging-buying-guide',
            'anchor' => 'perfume-box-styles',
            'title' => 'Custom Perfume Packaging Boxes | Vietnam Manufacturer',
            'description' => 'Order custom perfume packaging boxes from Vietnam. Compare a retail folding carton for one bottle with a rigid gift set box and fitted insert.',
            'intro' => 'VPN Packaging manufactures custom paper perfume boxes in Vietnam. Choose a printed folding carton for an individual fragrance bottle or a rigid gift box with a fitted tray for a discovery or gift set. The filled bottle, cap and opening orientation determine how the box and insert are sized.',
            'comparison' => 'Choose a perfume box for a single bottle or a set',
            'formats' => [
                'custom-folding-carton-perfume-box' => ['Perfume folding carton', 'For a single bottle sold at retail or in repeat runs. Approve the bottom closure, bottle support and room for the cap with a filled bottle.'],
                'custom-perfume-gift-set-box-with-insert' => ['Rigid perfume gift set box', 'For mini bottles, discovery sets or mixed-size fragrances. Map each cavity, label direction and finger clearance before approving the tray.'],
            ],
            'sample_heading' => 'Check the fit with filled bottles',
            'sample' => 'Provide the widest body dimensions, total height including the cap, base profile and filled weight for every bottle in the set. Test the loaded sample with the lid or flaps closed. Check that the cap has clearance, each bottle can be lifted out and the intended label face remains visible.',
            'quote' => 'Send the bottle drawings or samples, quantity by fragrance or set, artwork and delivery destination. The folding carton lists a minimum of 1,000 boxes. The rigid gift set minimum is confirmed for the project. Ask for sample fees and timing in the written quotation.',
        ],
        'custom-corporate-pr-kit-magnetic-box' => [
            'title' => 'Custom Magnetic PR Kit Boxes | Made in Vietnam',
            'description' => 'Order custom magnetic PR kit boxes from Vietnam. Plan a fitted insert, message card and logo finish for a multi-item launch or corporate gift set.',
            'intro' => 'VPN Packaging makes rigid magnetic PR kit boxes in Vietnam for product launches and corporate gifts. The hinged lid presents the complete set, while a fitted insert assigns each item and the campaign card a position.',
            'file' => 'magnetic-pr-kit.html',
            'reviewed_content' => '355ddfc3b2aab82e9bf138945320bc3f64a6bde5fae6a4213cad18f1640ce63b',
        ],
        'custom-corrugated-gift-set-mailer-box' => [
            'title' => 'Custom Corrugated Gift Set Mailer Boxes | Vietnam',
            'description' => 'Order printed corrugated gift set mailer boxes from Vietnam. Share the contents, packed weight and destination to quote a fitted divider and bulk run.',
            'intro' => 'VPN Packaging makes corrugated gift set mailer boxes in Vietnam for corporate gifts and campaign kits sent to recipients. Size the mailer around the complete set and agree how the insert or divider will restrain each item during handling.',
            'file' => 'gift-set-mailer.html',
            'reviewed_content' => '3dcb190c2c435ca295633a9e30c4fd4cec323437ce9e9d02dfa9784ec8ff1cf6',
        ],
        'custom-folding-carton-perfume-box' => [
            'title' => 'Custom Perfume Folding Carton Boxes | Made in Vietnam',
            'description' => 'Order printed perfume folding cartons from Vietnam. Quote the bottle fit, bottom closure, paperboard insert and quantity for a bulk retail run.',
            'intro' => 'VPN Packaging makes custom perfume folding cartons in Vietnam for individual retail fragrance bottles. The carton and any paperboard support are sized around the filled bottle, its cap and the way staff will load and close the pack.',
            'file' => 'perfume-folding-carton.html',
            'reviewed_content' => 'fecbc57c9308019e4abf6227d9b6550bc81be057aafaab596d10f627d5f95173',
        ],
        'custom-perfume-gift-set-box-with-insert' => [
            'title' => 'Custom Perfume Gift Set Boxes With Inserts | Vietnam',
            'description' => 'Order rigid perfume gift set boxes from Vietnam. Send each bottle size, filled weight and the display layout to quote a fitted tray and hinged box.',
            'intro' => 'VPN Packaging makes rigid perfume gift set boxes in Vietnam with a hinged lid and fitted tray for mini bottles or mixed-size fragrance sets. Lay out the full set so each bottle is held in its assigned orientation and can be lifted out by hand.',
            'file' => 'perfume-gift-set.html',
            'reviewed_content' => '74f96a604ea8b38e9f58793b0d46043327c1ca7a0b88c2b1cc1844bfd47a8cea',
        ],
    ];
}

function vpn_packaging_current_buying_page(): array {
    $object = get_queried_object();
    if ($object instanceof WP_Post && is_singular('product') && $object->post_status === 'publish') {
        $page = vpn_packaging_buying_pages()[$object->post_name] ?? [];
        return isset($page['file']) ? $page : [];
    }
    if ($object instanceof WP_Term && is_tax('product_cat') && !is_paged() && (int) get_query_var('page') < 2) {
        $page = vpn_packaging_buying_pages()[$object->slug] ?? [];
        return isset($page['guide']) ? $page : [];
    }
    return [];
}

function vpn_packaging_buying_title($title) {
    return vpn_packaging_current_buying_page()['title'] ?? $title;
}
add_filter('rank_math/frontend/title', 'vpn_packaging_buying_title', 80);
add_filter('pre_get_document_title', 'vpn_packaging_buying_title', 80);

add_filter('rank_math/frontend/description', static function ($description) {
    return vpn_packaging_current_buying_page()['description'] ?? $description;
}, 80);

add_filter('woocommerce_short_description', static function (string $excerpt): string {
    $page = vpn_packaging_current_buying_page();
    if (empty($page['file']) || get_the_ID() !== get_queried_object_id()) { return $excerpt; }
    return '<p>' . esc_html($page['intro']) . '</p>';
}, 9);

add_filter('the_content', static function (string $content): string {
    $page = vpn_packaging_current_buying_page();
    if (empty($page['file']) || !is_main_query() || !in_the_loop() || get_the_ID() !== get_queried_object_id()) {
        return $content;
    }
    $path = __DIR__ . '/vpn-packaging-buying-guides/' . $page['file'];
    if (!is_readable($path)) { return $content; }
    $html = file_get_contents($path);
    if (!$html) { return $content; }
    $home = untrailingslashit(home_url('/'));
    $html = str_replace('{{HOME}}', esc_url($home), $html);

    // Replace the reviewed text only. A newer description or embedded media remains visible.
    $normalized = str_replace([$home, 'http://localhost/hopgiayvpn', 'https://hopgiayvpn.com', 'http://hopgiayvpn.com'], '{{HOME}}', $content);
    if (hash('sha256', trim(preg_replace('/\s+/', ' ', $normalized))) === $page['reviewed_content']) {
        return $html;
    }
    return $html . "\n\n" . $content;
}, 8);

add_filter('rank_math/json_ld', static function (array $entities): array {
    $page = vpn_packaging_current_buying_page();
    if (empty($page['file'])) { return $entities; }
    foreach ($entities as &$entity) {
        if (is_array($entity) && array_intersect(['Product', 'WooCommerceProduct'], (array) ($entity['@type'] ?? []))) {
            $entity['description'] = $page['intro'];
            $entity['countryOfOrigin'] = ['@type' => 'Country', 'name' => 'Vietnam'];
        }
    }
    unset($entity);
    return $entities;
}, 210);
