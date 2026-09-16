<?php
/**
 * Idempotent importer for the five Halloween paper bag products.
 *
 * The six JPG images for each product are bundled with the active theme at
 * inc/product-sample-deploy-assets/uploads/2026/09/. Product copy is kept in
 * this deployable importer so a git pull followed by the Product Sample Deploy
 * tool reproduces the same products, media, SEO fields, specs and FAQ markup.
 */

if (!defined('ABSPATH')) {
    $project_root = 'product-sample-deploy-tools' === basename(__DIR__)
        ? dirname(__DIR__, 5)
        : dirname(__DIR__);
    $wp_load = $project_root . '/wp-load.php';
    if (file_exists($wp_load)) {
        require_once $wp_load;
    }
}

if (!defined('ABSPATH')) {
    return;
}

if (!function_exists('vpn_halloween_bag_202609_product_definitions')) {
    define('VPN_HALLOWEEN_BAG_202609_MARKER', 'product-samples-halloween-paper-bags-202609');

    function vpn_halloween_bag_202609_product_definitions(): array
    {
        static $definitions = null;

        if (null !== $definitions) {
            return $definitions;
        }

        $definitions = array(
            array(
                'title'            => 'Custom Creepy Cute Ghost Portal Halloween Paper Bag',
                'slug'             => 'custom-creepy-cute-ghost-portal-halloween-paper-bag',
                'keyword'          => 'custom ghost Halloween paper bag',
                'seo_title'        => 'Custom Ghost Halloween Paper Bag | VPN Packaging',
                'seo_description'  => 'Custom ghost Halloween paper bag with rope handles, seasonal print and made-to-size construction for candy, gifts, retail and event campaigns.',
                'model'            => 'VPN-HALLOWEEN-GHOST-PORTAL-BAG',
                'folder_key'       => '01-creepy-cute-ghost-portal-paper-bag',
                'buyer'            => 'candy shops, children\'s brands, gift retailers, pop-up events and seasonal promotional campaigns',
                'contents'         => 'candy, small toys, party favors, boxed treats and compact Halloween gifts',
                'visual'           => 'a cheerful ghost-and-bat illustration with pumpkins, wrapped candy and a black-and-cream checkerboard gusset',
                'structure'        => 'a rectangular paper shopping bag with a broad front panel, side gusset and reinforced top fold',
                'handle'           => 'twisted rope handles',
                'paper'            => 'a paper specification selected from kraft, coated art paper or another approved paper stock',
                'fit'              => 'a friendly, colorful presentation for younger audiences and approachable Halloween gifting',
                'print'             => 'full-color front-and-side artwork with a controlled pastel pink, black, cream, orange and purple palette',
                'finish'           => 'matte, gloss or protective coating selected after the paper and color coverage are approved',
                'operations'       => 'retail counters, party favor handouts, event kits and small ecommerce gift orders',
                'quality'          => 'checkerboard registration, ghost illustration clarity, handle symmetry, gusset squareness and clean top folds',
                'feature'          => 'Creepy-cute ghost portal artwork, checkerboard gusset, pumpkin and candy motifs, twisted rope handles',
                'industrial'       => 'Halloween confectionery, children\'s retail, party favors, events, gifts and promotional merchandise',
                'shape'            => 'Rectangular shopping bag with side gusset / Customized',
                'accessories'      => 'Twisted rope handles / Reinforced top fold / Bottom board optional',
                'liner'            => 'Paper interior / Additional lining or barrier available on request',
                'colors'           => 'Pastel pink, black, cream, orange and purple / CMYK or Pantone customized',
                'captions'         => array(
                    'Front view of a custom ghost portal Halloween paper bag with candy, pumpkin and bat artwork.',
                    'Open interior of the creepy-cute Halloween paper bag showing the top fold, side gusset and carry space.',
                    'Handle print detail on the custom ghost portal paper bag with seasonal artwork continuing across the top area.',
                    'Rear and side gusset view showing checkerboard alignment and the bag\'s rectangular construction.',
                    'High-angle view of the custom Halloween shopping bag showing the gusset opening and rope handle placement.',
                    'Feature callouts for the ghost portal Halloween paper bag, including seasonal graphics and carry structure.',
                ),
                'tags'             => array('halloween paper bags', 'ghost paper bags', 'seasonal packaging', 'custom paper bags', 'party favor bags'),
            ),
            array(
                'title'            => 'Custom Pumpkin Lantern Die-Cut Handle Halloween Paper Bag',
                'slug'             => 'custom-pumpkin-lantern-die-cut-handle-halloween-paper-bag',
                'keyword'          => 'custom pumpkin Halloween paper bag',
                'seo_title'        => 'Custom Pumpkin Halloween Paper Bag | VPN Packaging',
                'seo_description'  => 'Custom pumpkin Halloween paper bag with die-cut handle, orange seasonal artwork and made-to-size production for candy, gifts, events and retail.',
                'model'            => 'VPN-HALLOWEEN-PUMPKIN-LANTERN-BAG',
                'folder_key'       => '02-pumpkin-lantern-die-cut-handle-paper-bag',
                'buyer'            => 'confectionery brands, event organizers, retail promotions, family attractions and Halloween gift programs',
                'contents'         => 'candy assortments, snack packs, small toys, party favors and lightweight retail purchases',
                'visual'           => 'a bold orange pumpkin-lantern face with cream highlights, black features and dark green botanical side artwork',
                'structure'        => 'a wide rectangular paper bag with a flat die-cut carry handle, side gusset and reinforced top edge',
                'handle'           => 'a die-cut handle with a reinforced opening',
                'paper'            => 'kraft, white kraft, coated art paper or another selected stock matched to the print and carrying load',
                'fit'              => 'high-visibility seasonal retail presentation where the pumpkin face communicates the event quickly',
                'print'             => 'full-color orange, cream, black and dark green print with a large front-panel pumpkin illustration',
                'finish'           => 'uncoated, matte-coated or gloss-coated surface selected for the intended handling and visual target',
                'operations'       => 'counter service, Halloween attraction stores, promotional booths, candy distribution and event fulfillment',
                'quality'          => 'die-cut handle smoothness, face registration, side-panel continuity, top reinforcement and base flatness',
                'feature'          => 'Orange pumpkin-lantern face, die-cut handle, dark botanical side panels and wide rectangular format',
                'industrial'       => 'Halloween candy, family events, retail promotions, attractions, party favors and seasonal gifting',
                'shape'            => 'Wide rectangular shopping bag with die-cut handle / Customized',
                'accessories'      => 'Die-cut handle reinforcement / Folded top edge / Bottom board optional',
                'liner'            => 'Paper interior / Additional lining or barrier available on request',
                'colors'           => 'Orange, cream, black and dark green / CMYK or Pantone customized',
                'captions'         => array(
                    'Front view of the custom pumpkin lantern Halloween paper bag with a die-cut handle and bold seasonal print.',
                    'Open interior view showing the die-cut handle opening, top reinforcement and side gusset of the pumpkin bag.',
                    'Close-up of the pumpkin lantern face and handle area on the custom Halloween paper shopping bag.',
                    'Rear and side gusset view showing dark botanical panels and the wide bag construction.',
                    'High-angle structure view showing the top opening, handle placement and stable rectangular base.',
                    'Feature callouts for the custom pumpkin lantern die-cut handle Halloween paper bag.',
                ),
                'tags'             => array('halloween paper bags', 'pumpkin paper bags', 'die cut handle bags', 'seasonal packaging', 'candy packaging'),
            ),
            array(
                'title'            => 'Custom Retro Haunted Carnival Halloween Paper Bag',
                'slug'             => 'custom-retro-haunted-carnival-halloween-paper-bag',
                'keyword'          => 'custom retro Halloween paper bag',
                'seo_title'        => 'Custom Retro Halloween Paper Bag | VPN Packaging',
                'seo_description'  => 'Custom retro Halloween paper bag with haunted carnival artwork, rope handles and made-to-size construction for gifts, events, retail and promotions.',
                'model'            => 'VPN-HALLOWEEN-HAUNTED-CARNIVAL-BAG',
                'folder_key'       => '03-retro-haunted-carnival-paper-bag',
                'buyer'            => 'theme parks, escape rooms, event merchandise teams, specialty retailers and Halloween campaign owners',
                'contents'         => 'souvenirs, apparel, boxed gifts, concession items, party kits and themed merchandise',
                'visual'           => 'a retro haunted-carnival scene with a moon, castle, ferris wheel, bats, witch silhouette and checkerboard gusset',
                'structure'        => 'a roomy rectangular paper shopping bag with broad printed faces, side gusset and dark twisted rope handles',
                'handle'           => 'dark twisted rope handles',
                'paper'            => 'kraft or coated art paper selected for the required print depth, handle load and surface finish',
                'fit'              => 'story-led event merchandise and retail programs where the package extends the visitor or customer experience',
                'print'             => 'illustrated full-color artwork using orange, black, cream, teal and muted gold visual accents',
                'finish'           => 'matte coating, gloss highlights or selective premium finishing evaluated against the detailed illustration',
                'operations'       => 'theme-park retail, event counters, attraction merchandise, gift shops and seasonal ecommerce fulfillment',
                'quality'          => 'fine illustration registration, dark-area scuff control, rope handle balance, gusset alignment and base stability',
                'feature'          => 'Retro haunted carnival scene, moonlit castle and fairground artwork, checkerboard gusset, rope handles',
                'industrial'       => 'Theme parks, attraction merchandise, event retail, gifts, apparel and Halloween promotional programs',
                'shape'            => 'Roomy rectangular shopping bag with side gusset / Customized',
                'accessories'      => 'Twisted rope handles / Reinforced top fold / Bottom board optional',
                'liner'            => 'Printed or natural paper interior / Additional lining available on request',
                'colors'           => 'Orange, black, cream, teal and muted gold / CMYK or Pantone customized',
                'captions'         => array(
                    'Front view of a custom retro haunted carnival Halloween paper bag with moonlit castle and fairground artwork.',
                    'Open interior view showing the spacious carnival paper bag, rope handles and gusset construction.',
                    'Handle and upper print detail on the retro haunted carnival paper shopping bag.',
                    'Rear and side gusset view showing the checkerboard side panel and continuous seasonal artwork.',
                    'High-angle view of the haunted carnival paper bag showing the opening, depth and handle spacing.',
                    'Feature callouts for the custom retro haunted carnival Halloween paper bag.',
                ),
                'tags'             => array('halloween paper bags', 'retro paper bags', 'event packaging', 'theme park packaging', 'custom paper bags'),
            ),
            array(
                'title'            => 'Custom Gothic Celestial Raven Halloween Paper Bag',
                'slug'             => 'custom-gothic-celestial-raven-halloween-paper-bag',
                'keyword'          => 'custom gothic Halloween paper bag',
                'seo_title'        => 'Custom Gothic Halloween Paper Bag | VPN Packaging',
                'seo_description'  => 'Custom gothic Halloween paper bag with raven, moon-phase and botanical artwork, ribbon handles and made-to-size production for premium retail and gifts.',
                'model'            => 'VPN-HALLOWEEN-GOTHIC-RAVEN-BAG',
                'folder_key'       => '04-gothic-celestial-raven-paper-bag',
                'buyer'            => 'alternative fashion brands, candle and fragrance retailers, occult-inspired gift shops and premium event programs',
                'contents'         => 'bottles, candles, cosmetics, books, accessories and dark-academia gift sets',
                'visual'           => 'a deep charcoal-purple field with a raven on a crescent moon, moon phases, stars and botanical linework',
                'structure'        => 'a tall narrow paper shopping bag with side gusset, structured top and contrasting black ribbon handles',
                'handle'           => 'flat black ribbon handles secured through reinforced handle points',
                'paper'            => 'dark kraft, dyed paper, coated art paper or another approved stock that holds fine artwork and carries the packed product',
                'fit'              => 'premium seasonal gifting and vertical products that benefit from a tall presentation profile',
                'print'             => 'dark-base artwork with warm cream, copper, muted orange and botanical highlights',
                'finish'           => 'matte surface, selective gloss, foil or embossing evaluated for the artwork and handling environment',
                'operations'       => 'premium retail counters, fragrance and candle gifting, launch events and curated ecommerce orders',
                'quality'          => 'dark-area color consistency, fine line clarity, crescent and raven registration, ribbon alignment and top-edge strength',
                'feature'          => 'Gothic raven-and-moon artwork, moon phases, botanical border, tall profile and black ribbon handles',
                'industrial'       => 'Candle, fragrance, alternative fashion, premium gifts, boutiques and Halloween event merchandise',
                'shape'            => 'Tall narrow rectangular shopping bag with side gusset / Customized',
                'accessories'      => 'Black ribbon handles / Reinforced handle patches / Bottom board optional',
                'liner'            => 'Dark or natural paper interior / Additional lining available on request',
                'colors'           => 'Charcoal, deep purple, cream, copper and muted orange / CMYK or Pantone customized',
                'captions'         => array(
                    'Front view of the custom gothic celestial raven Halloween paper bag with crescent moon and botanical artwork.',
                    'Open interior view showing the tall bag profile, dark ribbon handles and side gusset.',
                    'Upper handle and fine-line print detail on the gothic raven paper shopping bag.',
                    'Rear and side gusset view showing the dark artwork system and tall rectangular structure.',
                    'High-angle view showing the top opening, ribbon handle spacing and narrow presentation profile.',
                    'Feature callouts for the custom gothic celestial raven Halloween paper bag.',
                ),
                'tags'             => array('halloween paper bags', 'gothic paper bags', 'raven packaging', 'premium gift bags', 'seasonal packaging'),
            ),
            array(
                'title'            => 'Custom Witch Apothecary Kraft Halloween Paper Bag',
                'slug'             => 'custom-witch-apothecary-kraft-halloween-paper-bag',
                'keyword'          => 'custom kraft witch Halloween paper bag',
                'seo_title'        => 'Custom Witch Kraft Halloween Paper Bag | VPN Packaging',
                'seo_description'  => 'Custom kraft witch Halloween paper bag with apothecary artwork, rope handles and made-to-size production for candles, gifts, cosmetics and events.',
                'model'            => 'VPN-HALLOWEEN-WITCH-APOTHECARY-BAG',
                'folder_key'       => '05-witch-apothecary-kraft-paper-bag',
                'buyer'            => 'candle makers, herbal and beauty brands, artisan gift shops, Halloween markets and natural-product retailers',
                'contents'         => 'candles, jars, soaps, cosmetics, small bottles, stationery and artisan gift sets',
                'visual'           => 'a natural kraft base with moth, moon, botanical, mushroom, cat and apothecary bottle illustrations',
                'structure'        => 'a wide landscape paper shopping bag with side gusset, folded top and dark green twisted rope handles',
                'handle'           => 'dark green twisted rope handles',
                'paper'            => 'natural kraft, recycled-content kraft or a selected art-paper alternative matched to the artwork and load',
                'fit'              => 'craft-led and botanical Halloween programs that want a warmer, less cartoon-focused seasonal presentation',
                'print'             => 'dark green, charcoal, rust, cream and muted orange artwork printed over a kraft-toned visual field',
                'finish'           => 'natural uncoated appearance, matte coating or selective accent finish chosen to preserve the botanical illustration',
                'operations'       => 'artisan retail, candle gifting, farmers markets, seasonal pop-ups, beauty counters and small-batch ecommerce',
                'quality'          => 'kraft color consistency, botanical line clarity, handle-hole reinforcement, side-panel repeat and bottom squareness',
                'feature'          => 'Kraft apothecary artwork with moth, moon, botanicals, mushrooms and dark green rope handles',
                'industrial'       => 'Candles, cosmetics, artisan gifts, wellness products, markets, boutiques and Halloween retail events',
                'shape'            => 'Landscape rectangular shopping bag with side gusset / Customized',
                'accessories'      => 'Dark green twisted rope handles / Reinforced top fold / Bottom board optional',
                'liner'            => 'Natural kraft or printed paper interior / Additional lining available on request',
                'colors'           => 'Kraft, dark green, charcoal, rust, cream and muted orange / CMYK or Pantone customized',
                'captions'         => array(
                    'Front view of the custom witch apothecary kraft Halloween paper bag with moth, moon and botanical artwork.',
                    'Open interior view showing the natural kraft bag, rope handles, top fold and gusset.',
                    'Handle and printed apothecary illustration detail on the custom kraft Halloween paper bag.',
                    'Rear and side gusset view showing the botanical side artwork and landscape construction.',
                    'High-angle view showing the opening, base depth and dark green handle placement.',
                    'Feature callouts for the custom witch apothecary kraft Halloween paper bag.',
                ),
                'tags'             => array('halloween paper bags', 'kraft paper bags', 'witch packaging', 'candle gift bags', 'artisan packaging'),
            ),
        );

        foreach ($definitions as &$definition) {
            $prefix = $definition['folder_key'];
            $definition['images'] = array(
                '01-' . substr($prefix, 3) . '-hero.jpg',
                '02-' . substr($prefix, 3) . '-open-interior.jpg',
                '03-' . substr($prefix, 3) . '-handle-print-detail.jpg',
                '04-' . substr($prefix, 3) . '-rear-side-gusset.jpg',
                '05-' . substr($prefix, 3) . '-high-angle-structure.jpg',
                '06-' . substr($prefix, 3) . '-feature-callouts.jpg',
            );
        }
        unset($definition);

        return $definitions;
    }

    function vpn_halloween_bag_202609_link(string $path, string $label): string
    {
        $url = 0 === strpos($path, 'http') ? $path : home_url($path);

        return '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
    }

    function vpn_halloween_bag_202609_figure(int $attachment_id, string $alt, string $caption, int $slot): string
    {
        $image = wp_get_attachment_image($attachment_id, 'large', false, array(
            'alt'      => $alt,
            'loading'  => 'lazy',
            'decoding' => 'async',
        ));

        if (!$image) {
            throw new RuntimeException('Could not render Halloween paper bag inline image ' . $attachment_id);
        }

        return '<!-- stable-product-image:slot_' . (int) $slot . ' --><figure class="product-inline-figure product-inline-figure-small">'
            . $image . '<figcaption>' . esc_html($caption) . '</figcaption></figure>';
    }

    function vpn_halloween_bag_202609_specs(array $product): array
    {
        return array(
            array('label' => 'Feature', 'value' => $product['feature']),
            array('label' => 'Industrial Use', 'value' => $product['industrial']),
            array('label' => 'Paper Type', 'value' => $product['paper'] . '; exact grade and weight to be specified'),
            array('label' => 'Box Type', 'value' => 'Custom Halloween paper shopping bag'),
            array('label' => 'Shape', 'value' => $product['shape']),
            array('label' => 'Place of Origin', 'value' => 'Vietnam'),
            array('label' => 'Model Number', 'value' => $product['model']),
            array('label' => 'Brand Name', 'value' => 'VPN Packaging'),
            array('label' => 'Province', 'value' => 'Ho Chi Minh City'),
            array('label' => 'Accessories', 'value' => $product['accessories']),
            array('label' => 'Custom Order', 'value' => 'Accept'),
            array('label' => 'Liner Type', 'value' => $product['liner']),
            array('label' => 'Logo Printing', 'value' => 'Custom logo, campaign artwork or repeat pattern'),
            array('label' => 'Printing Handling', 'value' => 'CMYK, Pantone, digital print, matte or gloss coating, foil, embossing and spot UV subject to approval'),
            array('label' => 'Color', 'value' => $product['colors']),
            array('label' => 'Size', 'value' => 'Customized to packed product'),
            array('label' => 'Thickness', 'value' => 'Customized paper weight and construction'),
            array('label' => 'Single Piece Price', 'value' => 'Request a quote based on size, paper, handles, print, finish and quantity'),
            array('label' => 'Minimum Order Quantity (MOQ)', 'value' => 'Available on request'),
            array('label' => 'Product Name', 'value' => $product['title']),
            array('label' => 'Design', 'value' => "Customer's Specific Requirement"),
        );
    }

    function vpn_halloween_bag_202609_short_description(array $p): string
    {
        return '<p>' . esc_html($p['title']) . ' is a made-to-order seasonal paper shopping bag for ' . esc_html($p['buyer']) . '. '
            . 'The design uses ' . esc_html($p['visual']) . ' on ' . esc_html($p['structure']) . '. '
            . 'It is intended for ' . esc_html($p['contents']) . ' and can be customized for product dimensions, paper grade, handle system, print coverage, color references, coating, premium finishes, reinforcement and export packing. '
            . 'The exact paper, dimensions, carry load, food-contact boundary, MOQ and price must be confirmed from the buyer\'s product brief and approved sample. '
            . 'For an accurate quote, send packed dimensions, weight, quantity by artwork, destination, required delivery date and the preferred Halloween visual direction. VPN develops seasonal paper bags in Ho Chi Minh City, Vietnam for international B2B packaging programs.</p>';
    }

    function vpn_halloween_bag_202609_content(array $p, array $attachment_ids): string
    {
        $category_link = vpn_halloween_bag_202609_link('/products/halloween-packaging/', 'Halloween Packaging category');
        $bags_link = vpn_halloween_bag_202609_link('/products/paper-bags-with-logo/', 'custom paper bags with logo');
        $dieline_link = vpn_halloween_bag_202609_link('/what-is-a-dieline-in-packaging/', 'packaging dieline guide');
        $gift_link = vpn_halloween_bag_202609_link('/products/gift-paper-boxes/', 'gift paper box range');
        $quote_link = vpn_halloween_bag_202609_link('/contact/#quote', 'request a Halloween paper bag quote');
        $html = '';

        $sections = array(
            array(
                'heading' => 'What Is This Halloween Paper Bag?',
                'paragraphs' => array(
                    '<strong>Quick answer:</strong> ' . esc_html($p['title']) . ' is a seasonal paper carrier developed around a product, event or retail campaign. The visual concept is ' . esc_html($p['visual']) . ', while the production specification controls ' . esc_html($p['structure']) . ', paper, handle, dimensions, print and packing. The bag belongs to the ' . $category_link . ' collection and is shown as a reference for buyers planning a custom Halloween program.',
                    esc_html($p['title']) . ' should be treated as a made-to-order product rather than a fixed stock size. A supplier needs the finished product, packed weight, intended quantity, destination and artwork plan before confirming the construction. The image establishes the intended appearance; the final material grade, tolerances, reinforcement and handle attachment are approved through the quotation and sample process. This keeps the seasonal idea attractive while making the package measurable for purchasing and quality control.',
                ),
            ),
            array(
                'heading' => 'Who Uses This Seasonal Paper Bag?',
                'paragraphs' => array(
                    'The format is a practical starting point for ' . esc_html($p['buyer']) . '. It can carry ' . esc_html($p['contents']) . ' when the bag size and handle system are engineered for the actual load. A Halloween bag can act as a take-home carrier, a gift wrapper, an event handout or an ecommerce presentation layer. The correct role changes the priorities: a counter bag needs fast loading and comfortable carry, while an event bag may prioritize visibility, repeat artwork and efficient flat packing.',
                    'Buyers should decide whether the bag is the primary presentation package, a secondary carrier around a boxed product, or one part of a coordinated campaign with cartons, tissue, stickers and cards. If a candle, cosmetic, snack or boxed gift is placed inside, the inner product package still provides the relevant product protection and contact boundary. This distinction prevents a decorative paper bag from being given performance claims that belong to another layer of the pack.',
                ),
            ),
            array(
                'heading' => 'Start With Product Fit and Bag Geometry',
                'paragraphs' => array(
                    'The first engineering input is the maximum packed length, width, height and weight. Record whether the item is boxed, wrapped, nested, cushioned or combined with other products. Then define the bag opening, front-panel proportion, side gusset, bottom construction, top fold and handle drop. A bag that is too loose can collapse around a small gift; a bag that is too tight can slow packing and scrape corners. The goal is controlled fit with enough clearance for the intended loading sequence.',
                    'For ' . esc_html($p['title']) . ', the visual direction favors ' . esc_html($p['fit']) . '. The final geometry still depends on the products and the channel. ' . esc_html($p['structure']) . ' should be checked with a filled sample, not only a flat artwork file. Review squareness, opening width, gusset recovery, bottom flatness, handle spacing and the way the printed face looks after the contents settle. The approved dieline should identify cut, fold, glue, handle-hole, reinforcement, bleed and safe-area information before artwork is released.',
                ),
            ),
            array(
                'heading' => 'Choose Paper and Handle Components Together',
                'paragraphs' => array(
                    'The paper decision is not only about color. ' . esc_html(ucfirst($p['paper'])) . ' must be reviewed with basis weight, stiffness, fold behavior, print holdout, surface feel, recycled-content direction, moisture exposure and the intended carry load. A heavier sheet can add body, but it does not correct a weak handle hole, poor pasted seam or unstable bottom. Request the exact paper name, weight or thickness, grain direction where relevant, coating and substitution rule in the quotation.',
                    'This product uses ' . esc_html($p['handle']) . '. Handle drop, attachment method, reinforcement patch, hole size, cord or ribbon specification and color tolerance should appear in the sample record. Lift the filled bag from both handles, place it down, and inspect the top fold and side seams. If the package will be carried for several minutes, comfort and balance are part of the product experience. The visual accent should remain consistent across bag, handle, tissue, carton label and any companion ' . $gift_link . '.',
                ),
            ),
            array(
                'heading' => 'Build Halloween Artwork on the Approved Dieline',
                'paragraphs' => array(
                    'The main print direction for this bag is ' . esc_html($p['print']) . '. Keep the brand, product name, required information and any barcode legible before adding seasonal motifs. Mark the front, back, gussets, bottom, top fold, glue seam, handle openings and visible wrap areas. Keep small type and critical registration away from scores and folds. The latest version of the artwork should use a controlled filename and revision table that connects each size, artwork, handle color and quantity.',
                    'A strong Halloween design can be playful, nostalgic, gothic or craft-led, but it should still look like the buyer\'s brand. Confirm whether the artwork is supplied as a finished print file, a repeat pattern, a logo placement or a reference mood board. The factory can then separate CMYK, spot colors, white ink, foil, embossing, debossing and spot-UV layers as needed. Use the ' . $dieline_link . ' to reduce confusion about bleed, cut lines, fold lines and safe areas.',
                ),
            ),
            array(
                'heading' => 'Select Finishes for the Campaign and Handling Route',
                'paragraphs' => array(
                    esc_html(ucfirst($p['finish'])) . ' can change color density, glare, tactile feel, rub resistance, folding behavior and cost. A matte surface may support a soft illustrated direction, gloss can increase contrast in selected areas, and foil or embossing can reserve attention for a logo, moon, star or key motif. The best finish is the one that survives loading, stacking, transport and customer handling while making the campaign easier to recognize.',
                    'Dark ink coverage, large orange fields, metallic details and uncoated kraft each need a physical review. A digital proof verifies copy and layout, but a production-relevant sample is more informative for substrate color, print registration, crease behavior, handle attachment and surface marks. Ask for a clear approval reference that names paper, print process, coating, finish mask and acceptable variation. This is especially important for ' . esc_html($p['quality']) . '.',
                ),
            ),
            array(
                'heading' => 'Plan the Loading and Carrying Experience',
                'paragraphs' => array(
                    'The bag should support a repeatable loading sequence for ' . esc_html($p['contents']) . '. Define whether staff load the largest item first, use tissue or a card, separate multiple products, or place a box against a particular panel. The opening should remain accessible, the side gusset should expand without tearing, and the bottom should stay stable after the contents settle. A photo or short work instruction based on the approved sample helps purchasing, packing and inspection teams follow the same method.',
                    'The intended channel is ' . esc_html($p['operations']) . '. That channel determines whether the priority is hand comfort, visibility, speed, flat shipping, moisture protection, stackability or mixed-SKU identification. Carton packing should state the unit count, orientation, internal protection, shipping marks and artwork version. The bag is part of a larger distribution system, so its finished condition should be checked after the same handling steps it will face in actual use.',
                ),
            ),
            array(
                'heading' => 'Food, Candle and Cosmetic Uses Need a Defined Boundary',
                'paragraphs' => array(
                    'A paper shopping bag is commonly a secondary carrier. If candy, cookies, chocolate, candles, cosmetics or jars are placed inside, specify which component directly touches the product. A printed outer bag does not by itself establish food-contact suitability, grease resistance, migration performance, moisture protection or regulatory compliance. The primary package, inner liner, tray, pouch or wrapped product should be evaluated for the destination market and the actual storage and handling conditions.',
                    'For a food or candle program, send the product dimensions, filled weight, temperature or moisture exposure, inner-pack format and any required declarations with the RFQ. Check abrasion against printed surfaces, glass or jar movement, odor transfer where relevant, and whether the customer can remove the product without pulling the handle or top fold. If the bag is only a carrier around an approved carton, say so in the specification instead of presenting it as a direct-contact package.',
                ),
            ),
            array(
                'heading' => 'Right-Size the Bag and Explain Material Claims Precisely',
                'paragraphs' => array(
                    'A useful sustainability brief starts with the right size, enough protection and a clear material system. Reduce unused volume, avoid unnecessary layers, and make it clear whether handles, coatings, windows, labels or mixed-material components are present. Paper-based packaging is not automatically recyclable in every location or collection system. The buyer should confirm the local acceptance rules and the separation method for the finished bag and any companion components.',
                    'If the program requires recycled content, a certification or a particular fiber source, place that requirement in the approved material specification and request supporting documentation. Avoid broad claims such as eco-friendly without defining the material, scope and evidence. A seasonal bag also supports sustainability when it is durable enough for its intended use and helps prevent avoidable product damage, rework or replacement. The exact claim belongs to the approved material and market, not to the Halloween artwork alone.',
                ),
            ),
            array(
                'heading' => 'Sampling and Quality Control Checklist',
                'paragraphs' => array(
                    'Before mass production, review a filled sample using the real product or a representative pack-out. Check finished width, height, depth, opening, gusset, bottom fold, seam adhesion, handle drop, reinforcement, print position, color, coating and surface condition. For this concept, pay particular attention to ' . esc_html($p['quality']) . '. Photograph the approved front, side, open and loaded views so the production and quality teams have a common reference.',
                    'During inspection, sample both empty and loaded bags. Confirm the quantity and artwork version in each master carton, then test handle lift, bottom stability, top-edge recovery and the condition of high-contact printed areas. A visual check alone can miss a bag that twists or loses shape under load. The acceptance record should identify the product dimensions, paper, handle, print reference, finish, tolerances, carton quantity and the person who approved the sample.',
                ),
            ),
            array(
                'heading' => 'What to Send for a Halloween Paper Bag Quote',
                'paragraphs' => array(
                    'For an accurate quotation, send the product or packed dimensions, total weight, quantity by size and artwork, preferred bag proportion, paper direction, handle reference, print coverage, finish target, reinforcement, packing method, destination and required arrival date. Include whether the bag is a secondary carrier or the primary presentation layer. If several designs share one order, provide a version table linking artwork, handle color, bag size, carton mark and quantity.',
                    'The exact MOQ and price for ' . esc_html($p['title']) . ' are available after the specification is reviewed. Size, paper, handle, print coverage, finishing, tooling if any, assembly, inspection, carton quantity and shipping term can all change the commercial plan. A written quote should separate confirmed requirements from assumptions and state which sample, material and artwork approvals are included. This makes supplier comparisons more meaningful than a unit price without pack-out details.',
                ),
            ),
            array(
                'heading' => 'Made-to-Order Halloween Paper Bags From Vietnam',
                'paragraphs' => array(
                    'VPN Paper Box Manufacturer develops custom paper bags in Ho Chi Minh City, Vietnam for brands, importers, retailers, event teams and agencies. This Halloween product is part of a wider ' . $bags_link . ' capability covering custom size, paper, gusset, handle, logo, print, finish, reinforcement and export packing. Buyers can use the sample as a visual starting point and then refine the structure around the product and campaign schedule.',
                    'Send the complete brief through the ' . $quote_link . '. VPN can coordinate structural review, artwork preparation, sampling, print and finish approval, production checks and export-ready packing according to the agreed scope. If the campaign includes cartons or premium gift sets, compare the bag with the ' . $gift_link . ' range so each packaging layer has a clear job. A controlled sample and written specification are the foundation for repeat seasonal orders.',
                ),
            ),
        );

        $figure_index = 0;
        foreach ($sections as $index => $section) {
            $html .= '<h2>' . esc_html($section['heading']) . '</h2>';
            foreach ($section['paragraphs'] as $paragraph) {
                $html .= '<p>' . $paragraph . '</p>';
            }

            if (in_array($index, array(0, 2, 4, 6), true) && $figure_index < 4) {
                $image_index = $figure_index + 1;
                $html .= vpn_halloween_bag_202609_figure(
                    $attachment_ids[$image_index],
                    $p['captions'][$image_index],
                    $p['captions'][$image_index],
                    $image_index
                );
                $figure_index++;
            }
        }

        return $html;
    }

    function vpn_halloween_bag_202609_faq_html(array $p): string
    {
        $faqs = array(
            array('What is the main use of this Halloween paper bag?', 'It is a made-to-order seasonal carrier for ' . $p['buyer'] . '. The final dimensions and handle specification must be checked with the actual contents and packed weight.'),
            array('Can the artwork, paper and handle be customized?', 'Yes. Buyers can customize the bag size, paper direction, logo, campaign artwork, print coverage, handle, reinforcement, coating and selected premium finishes. The final combination is approved through a production-relevant sample.'),
            array('What product information is needed before sampling?', 'Send the packed length, width, height and weight, loading method, quantity by artwork, destination, required arrival date, preferred paper and handle, artwork status and any food-contact or sustainability requirements.'),
            array('Can this bag carry food, candles or cosmetics?', 'It can serve as a secondary carrier around an appropriate primary package. Direct food contact, grease, moisture, odor, migration, temperature and destination-market requirements must be specified separately and are not established by the outer bag artwork.'),
            array('What are the MOQ and price?', 'MOQ and price are available on request. They depend on size, paper, handle, printing, finishing, reinforcement, quantity, sampling, packing and shipping requirements. A written quote should confirm the complete scope.'),
            array('Where are these custom Halloween paper bags made?', 'VPN Paper Box Manufacturer develops and produces made-to-order packaging in Ho Chi Minh City, Vietnam for international B2B buyers. The origin, materials, specification and delivery plan should be recorded in the approved quotation.'),
        );
        $html = '<section class="product-faq halloween-paper-bag-faq" itemscope itemtype="https://schema.org/FAQPage"><div class="container"><h2>Halloween Paper Bag FAQ</h2>';

        foreach ($faqs as $faq) {
            $html .= '<details itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">'
                . '<summary itemprop="name">' . esc_html($faq[0]) . '</summary>'
                . '<div itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer"><p itemprop="text">' . esc_html($faq[1]) . '</p></div>'
                . '</details>';
        }

        return $html . '</div></section>';
    }

    function vpn_halloween_bag_202609_attachment_id(string $base): int
    {
        global $wpdb;

        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC",
            '%' . $wpdb->esc_like($base) . '%'
        ));

        foreach ($ids as $id) {
            $attached = (string) get_post_meta((int) $id, '_wp_attached_file', true);
            if ($base === pathinfo(wp_basename($attached), PATHINFO_FILENAME)) {
                return (int) $id;
            }
        }

        return 0;
    }

    function vpn_halloween_bag_202609_attachment(string $filename, int $parent_id, string $alt, string $title, string $caption): int
    {
        $uploads = wp_upload_dir();
        if (!empty($uploads['error'])) {
            throw new RuntimeException('Upload directory error: ' . $uploads['error']);
        }

        $relative = '2026/09/' . $filename;
        $upload_path = trailingslashit($uploads['basedir']) . $relative;
        $bundle_path = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $relative;
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $attachment_id = vpn_halloween_bag_202609_attachment_id($base);

        if (!file_exists($bundle_path)) {
            throw new RuntimeException('Bundled Halloween image is missing: ' . $filename);
        }

        if (!file_exists($upload_path)) {
            if (!wp_mkdir_p(dirname($upload_path)) || !copy($bundle_path, $upload_path)) {
                throw new RuntimeException('Could not copy bundled Halloween image: ' . $filename);
            }
        } elseif (hash_file('sha256', $upload_path) !== hash_file('sha256', $bundle_path)) {
            throw new RuntimeException('Refusing to overwrite a different uploads file: ' . $relative);
        }

        if (!$attachment_id) {
            $filetype = wp_check_filetype(wp_basename($upload_path), null);
            $attachment_id = wp_insert_attachment(
                array(
                    'post_mime_type' => $filetype['type'] ?: 'image/jpeg',
                    'post_title'     => $title,
                    'post_excerpt'   => $caption,
                    'post_status'    => 'inherit',
                    'post_parent'    => $parent_id,
                ),
                $upload_path,
                $parent_id,
                true
            );

            if (is_wp_error($attachment_id)) {
                throw new RuntimeException('Could not create attachment ' . $filename . ': ' . $attachment_id->get_error_message());
            }
        }

        update_post_meta((int) $attachment_id, '_wp_attached_file', $relative);
        update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', $alt);
        wp_update_post(array(
            'ID'           => (int) $attachment_id,
            'post_title'   => $title,
            'post_excerpt' => $caption,
            'post_parent'  => $parent_id,
        ));

        require_once ABSPATH . 'wp-admin/includes/image.php';
        $metadata = wp_generate_attachment_metadata((int) $attachment_id, $upload_path);
        if (is_array($metadata) && !empty($metadata)) {
            wp_update_attachment_metadata((int) $attachment_id, $metadata);
        }

        return (int) $attachment_id;
    }

    function vpn_halloween_bag_202609_category_id(string $slug, string $name, string $parent_slug = 'custom-packaging-boxes'): int
    {
        $term = get_term_by('slug', $slug, 'product_cat');
        if ($term && !is_wp_error($term)) {
            return (int) $term->term_id;
        }

        $parent = get_term_by('slug', $parent_slug, 'product_cat');
        $created = wp_insert_term($name, 'product_cat', array(
            'slug'   => $slug,
            'parent' => $parent && !is_wp_error($parent) ? (int) $parent->term_id : 0,
        ));

        if (is_wp_error($created)) {
            throw new RuntimeException('Could not create category ' . $name . ': ' . $created->get_error_message());
        }

        return (int) $created['term_id'];
    }

    function vpn_halloween_bag_202609_tag_ids(array $slugs): array
    {
        $ids = array();

        foreach ($slugs as $slug) {
            $term = get_term_by('slug', $slug, 'product_tag');
            if (!$term || is_wp_error($term)) {
                $created = wp_insert_term(ucwords(str_replace('-', ' ', $slug)), 'product_tag', array('slug' => $slug));
                if (is_wp_error($created)) {
                    throw new RuntimeException('Could not create product tag ' . $slug . ': ' . $created->get_error_message());
                }
                $ids[] = (int) $created['term_id'];
            } else {
                $ids[] = (int) $term->term_id;
            }
        }

        return $ids;
    }

    function vpn_halloween_bag_202609_find_product(array $definition): ?WP_Post
    {
        $product = get_page_by_path($definition['slug'], OBJECT, 'product');
        if ($product instanceof WP_Post) {
            return $product;
        }

        $matches = get_posts(array(
            'post_type'      => 'product',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'meta_key'       => '_vpn_halloween_bag_202609_slug',
            'meta_value'     => $definition['slug'],
        ));

        return !empty($matches[0]) ? $matches[0] : null;
    }

    function vpn_halloween_bag_202609_import_one(array $definition): array
    {
        if (6 !== count($definition['images'])) {
            throw new RuntimeException('Expected six images for ' . $definition['slug']);
        }

        $product = vpn_halloween_bag_202609_find_product($definition);
        if (!$product) {
            $conflict = get_posts(array(
                'name'           => $definition['slug'],
                'post_type'      => 'any',
                'post_status'    => 'any',
                'posts_per_page' => 1,
            ));
            if ($conflict) {
                throw new RuntimeException('Slug is already used by a non-product post: ' . $definition['slug']);
            }

            $product_id = wp_insert_post(array(
                'post_type'   => 'product',
                'post_status' => 'draft',
                'post_title'  => $definition['title'],
                'post_name'   => $definition['slug'],
            ), true);
            if (is_wp_error($product_id)) {
                throw new RuntimeException('Could not create product ' . $definition['slug'] . ': ' . $product_id->get_error_message());
            }
        } else {
            $product_id = (int) $product->ID;
        }

        $attachment_ids = array();
        foreach ($definition['images'] as $index => $filename) {
            $base = pathinfo($filename, PATHINFO_FILENAME);
            $caption = $definition['captions'][$index];
            $attachment_ids[] = vpn_halloween_bag_202609_attachment(
                $filename,
                $product_id,
                $caption,
                $definition['title'] . ' - ' . $caption,
                $caption
            );
        }

        if (6 !== count(array_unique($attachment_ids))) {
            throw new RuntimeException('Image attachment IDs are not unique for ' . $definition['slug']);
        }

        if (function_exists('custom_box_sync_halloween_packaging_category')) {
            custom_box_sync_halloween_packaging_category();
        }

        $halloween_category_id = vpn_halloween_bag_202609_category_id('halloween-packaging', 'Halloween Packaging');
        $paper_bag_category_id = vpn_halloween_bag_202609_category_id('paper-bags-with-logo', 'Paper Bags with Logo');
        $tag_ids = vpn_halloween_bag_202609_tag_ids($definition['tags']);
        $content = vpn_halloween_bag_202609_content($definition, $attachment_ids);
        $short_description = vpn_halloween_bag_202609_short_description($definition);
        $faq_html = vpn_halloween_bag_202609_faq_html($definition);

        $updated = wp_update_post(array(
            'ID'           => $product_id,
            'post_type'    => 'product',
            'post_status'  => 'publish',
            'post_title'   => $definition['title'],
            'post_name'    => $definition['slug'],
            'post_excerpt' => $short_description,
            'post_content' => $content,
        ), true);
        if (is_wp_error($updated)) {
            throw new RuntimeException('Could not save product ' . $definition['slug'] . ': ' . $updated->get_error_message());
        }

        wp_set_object_terms($product_id, array($halloween_category_id, $paper_bag_category_id), 'product_cat', false);
        wp_set_object_terms($product_id, $tag_ids, 'product_tag', false);
        wp_set_object_terms($product_id, array('simple'), 'product_type', false);
        update_post_meta($product_id, 'rank_math_primary_product_cat', $halloween_category_id);
        set_post_thumbnail($product_id, $attachment_ids[0]);
        update_post_meta($product_id, '_product_image_gallery', implode(',', array_slice($attachment_ids, 1)));
        update_post_meta($product_id, '_stock_status', 'instock');
        update_post_meta($product_id, '_manage_stock', 'no');
        update_post_meta($product_id, '_visibility', 'visible');
        update_post_meta($product_id, '_custom_box_product_specs', vpn_halloween_bag_202609_specs($definition));
        update_post_meta($product_id, '_custom_box_product_hero_bullets', array(
            $definition['feature'],
            'Made-to-size ' . $definition['shape'],
            'Custom paper, print, handle, reinforcement and finish options',
            'Vietnam production; MOQ and price available on request',
        ));
        update_post_meta($product_id, '_custom_box_product_faq_html', $faq_html);
        update_post_meta($product_id, '_vpn_sample_import', VPN_HALLOWEEN_BAG_202609_MARKER);
        update_post_meta($product_id, '_vpn_halloween_bag_202609_slug', $definition['slug']);
        update_post_meta($product_id, 'rank_math_title', $definition['seo_title']);
        update_post_meta($product_id, 'rank_math_description', $definition['seo_description']);
        update_post_meta($product_id, 'rank_math_focus_keyword', $definition['keyword']);
        update_post_meta($product_id, 'rank_math_canonical_url', get_permalink($product_id));
        update_post_meta($product_id, 'rank_math_robots', array('index', 'follow'));
        update_post_meta($product_id, 'rank_math_facebook_title', $definition['seo_title']);
        update_post_meta($product_id, 'rank_math_facebook_description', $definition['seo_description']);
        update_post_meta($product_id, 'rank_math_facebook_image_id', $attachment_ids[0]);
        update_post_meta($product_id, 'rank_math_facebook_image', wp_get_attachment_url($attachment_ids[0]));
        update_post_meta($product_id, 'rank_math_twitter_title', $definition['seo_title']);
        update_post_meta($product_id, 'rank_math_twitter_description', $definition['seo_description']);
        update_post_meta($product_id, 'rank_math_twitter_image_id', $attachment_ids[0]);
        update_post_meta($product_id, 'rank_math_twitter_image', wp_get_attachment_url($attachment_ids[0]));
        update_post_meta($product_id, 'rank_math_twitter_card_type', 'summary_large_image');

        return array(
            'id'          => (int) $product_id,
            'title'       => $definition['title'],
            'slug'        => $definition['slug'],
            'url'         => get_permalink($product_id),
            'category'    => 'Halloween Packaging',
            'attachments' => $attachment_ids,
            'seo_title'   => $definition['seo_title'],
            'description' => $definition['seo_description'],
        );
    }

    function vpn_halloween_bag_202609_run_import(): array
    {
        if (!function_exists('wp_insert_post') || !function_exists('wp_upload_dir')) {
            throw new RuntimeException('WordPress is not fully loaded.');
        }

        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        $results = array();
        foreach (vpn_halloween_bag_202609_product_definitions() as $definition) {
            $results[] = vpn_halloween_bag_202609_import_one($definition);
        }

        return $results;
    }
}

try {
    foreach (vpn_halloween_bag_202609_run_import() as $result) {
        echo 'Imported: ' . $result['title'] . ' (#' . $result['id'] . ') images=' . count($result['attachments']) . ' URL=' . $result['url'] . PHP_EOL;
    }
    echo 'Halloween paper bag product import complete: 5 products.' . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
