<?php
/**
 * Import 21 products for the Toy and Game, Tea and Coffee, and Pet Product categories.
 *
 * Local review:
 *   php tools/import-three-new-category-products.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

const VPN_THREECAT_MARKER = 'product-samples-three-new-categories-202607';

function vpn_threecat_items(string $value): array {
    return array_values(array_filter(array_map('trim', explode('|', $value))));
}

function vpn_threecat_sentence_list(array $items): string {
    $items = array_values(array_filter($items));

    if (!$items) {
        return '';
    }

    if (1 === count($items)) {
        return $items[0];
    }

    $last = array_pop($items);

    return implode(', ', $items) . ', and ' . $last;
}

function vpn_threecat_link(string $path, string $anchor): string {
    return '<a href="' . esc_url(home_url($path)) . '">' . esc_html($anchor) . '</a>';
}

function vpn_threecat_profiles(): array {
    return array(
        'toy' => array(
            'category_name' => 'Toy and Game Packaging Boxes',
            'category_slug' => 'toy-and-game-packaging-boxes',
            'industry' => 'Toys, Games, Children Products, Educational Retail',
            'paper' => 'SBS Paperboard / Ivory Board / Rigid Greyboard / Corrugated Board',
            'category_anchor' => 'toy and game packaging boxes',
            'risk_context' => 'Toy and game packaging has to control small parts, age guidance, component count, opening sequence, and shelf communication. A package can look playful yet still fail if pieces shift, cards bend, a figure rubs against a window, or the age mark is difficult to find.',
            'material_context' => 'For children-facing products, board stiffness, clean die-cut edges, controlled inks, durable corners, and an easy opening method deserve as much attention as the graphics. Material selection should match the weight and number of components rather than relying on one standard toy carton.',
            'operations_context' => 'Retailers and distributors need consistent component counts, readable SKU labels, compact master-carton loading, and packaging that can survive repeated shelf handling. Educational and craft products also need a clear reset or storage logic when the box is intended for reuse.',
            'general_tags' => array('toy packaging', 'game packaging', 'children product packaging', 'custom paper box'),
        ),
        'tea' => array(
            'category_name' => 'Tea and Coffee Packaging Boxes',
            'category_slug' => 'tea-and-coffee-packaging-boxes',
            'industry' => 'Tea, Coffee, Beverage, Specialty Food Retail',
            'paper' => 'SBS Paperboard / Ivory Board / Kraft Paperboard / Rigid Greyboard',
            'category_anchor' => 'tea and coffee packaging boxes',
            'risk_context' => 'Tea and coffee cartons do not replace the sealed food-contact pouch, capsule barrier, or sachet wrap. Their role is to organize those primary packs, protect presentation, communicate roast or blend information, and keep batch, weight, brewing, and barcode areas easy to verify.',
            'material_context' => 'Paperboard should be chosen around filled weight, aroma-sensitive inner packs, retail humidity, stacking pressure, and the desired premium level. Dark inks, natural kraft, botanical artwork, foil, and matte coatings all behave differently during folding, gluing, packing, and long-distance shipment.',
            'operations_context' => 'Roasters, tea blenders, private-label suppliers, gift buyers, and distributors often manage several origins, strengths, flavors, or pack counts. A repeatable carton system should make version control obvious while keeping the same dieline, barcode zone, lot-code area, and packing method wherever possible.',
            'general_tags' => array('tea packaging', 'coffee packaging', 'beverage packaging', 'custom food box'),
        ),
        'pet' => array(
            'category_name' => 'Pet Product Packaging Boxes',
            'category_slug' => 'pet-product-packaging-boxes',
            'industry' => 'Pet Food, Pet Care, Pet Accessories, Veterinary Retail',
            'paper' => 'SBS Paperboard / Kraft Paperboard / Corrugated Board / Rigid Greyboard',
            'category_anchor' => 'pet product packaging boxes',
            'risk_context' => 'Pet packaging must separate brand personality from practical information. Feeding directions, ingredient or material notes, pet size, dosage, warnings, barcode, lot code, and product format must remain clear while the package protects pouches, bottles, treats, accessories, or mixed kits.',
            'material_context' => 'The outer paper box should be selected around product weight, oil or moisture risk from the sealed primary pack, bottle pressure points, accessory shape, parcel handling, and whether the package is sold on shelf, by subscription, or as a premium gift.',
            'operations_context' => 'Pet brands frequently run multiple flavors, animal sizes, life stages, formulas, or monthly kit combinations. Procurement therefore needs a controlled version matrix, reliable packing sequence, clear labels, and master-carton planning that prevents the wrong product from entering the wrong printed box.',
            'general_tags' => array('pet product packaging', 'pet care packaging', 'pet retail box', 'custom paper box'),
        ),
    );
}

function vpn_threecat_make_product(string $family, array $data): array {
    $profiles = vpn_threecat_profiles();
    $profile = $profiles[$family];
    $slug = $data['slug'];
    $display = ucwords(str_replace(array('-', 'Childrens'), array(' ', "Children's"), $slug));
    $images = array();

    for ($index = 1; $index <= 4; $index++) {
        $images[] = $slug . '-' . $index . '.webp';
    }

    return array_merge(array(
        'family' => $family,
        'title' => strtoupper($display),
        'display' => $display,
        'keyword' => $slug,
        'images' => $images,
        'industrial' => $profile['industry'],
        'paper' => $profile['paper'],
        'duplicate_risk' => '4/10',
    ), $data);
}

function vpn_threecat_products(): array {
    $products = array();

    $products[] = vpn_threecat_make_product('toy', array(
        'slug' => 'board-game-packaging-box',
        'keyword' => 'board game packaging box',
        'inside' => 'adventure board games, card decks, wooden tokens, rule books, dice, trackers, and expansion components',
        'buyer' => 'board-game publishers, tabletop studios, educational game brands, crowdfunding teams, and specialty retailers',
        'challenge' => 'keeping cards, tokens, rules, and boards in a fixed order while protecting corners and making setup feel intuitive',
        'structure' => 'a rigid lid-and-base game box with a compartmented paperboard or molded-pulp organizer sized around the final component inventory',
        'fit' => 'Separate wells for the deck, tokens, tracker, and rule booklet prevent mixing and let players see whether every component has been returned after use.',
        'artwork' => 'The cover should carry the game title and theme, while side panels reserve age, player count, play time, barcode, language, and edition information.',
        'channel' => 'crowdfunding fulfillment, hobby stores, educational retail, direct-to-consumer launches, and distributor programs',
        'procurement' => 'final component list, folded board size, card count, token thickness, rule-book dimensions, insert preference, packed weight, and edition matrix',
        'details' => 'board dimensions|card deck count|token count|rule-book size|player tracker size|packed weight|age grade|edition quantity',
        'panels' => 'game title|player count|age range|play time|component list|barcode|language version|safety note',
        'qc' => 'lid fit|corner strength|card well depth|token separation|board clearance|print registration|component count|master-carton compression',
        'materials' => 'rigid greyboard, wrapped art paper, SBS insert board, molded pulp, chipboard dividers, and anti-scuff matte lamination',
        'finish' => 'matte lamination with selective foil or spot UV on the title, plus durable edge wrapping for repeated shelf and home use',
        'box_type' => 'Rigid Board Game Lid and Base Box',
        'shape' => 'Square / Rectangular / Compartmented Game Set',
        'accessories' => 'Compartment insert / Rule-book well / Token tray / Card deck divider',
        'liner' => 'Paperboard organizer / Molded pulp / Rigid divider',
        'colors' => 'Illustrated game palette / Dark green / Gold / Customized Color',
        'category_slugs' => array('toy-and-game-packaging-boxes', 'rigid-boxes', 'lid-and-base-boxes'),
        'tags' => array('board game box', 'tabletop game packaging', 'game component insert'),
        'views' => 'closed adventure game box|open box with cards and tokens|compartment insert layout|card and token detail',
        'related' => array('card-game-packaging-box', 'puzzle-packaging-box'),
    ));
    $products[] = vpn_threecat_make_product('toy', array(
        'slug' => 'card-game-packaging-box',
        'keyword' => 'card game packaging box',
        'inside' => 'standard playing cards, trading cards, educational flash cards, compact rule cards, and promotional card decks',
        'buyer' => 'card-game publishers, learning brands, promotional agencies, casino suppliers, and retail card manufacturers',
        'challenge' => 'protecting card edges and print surfaces while keeping a compact carton easy to open, close, count, and merchandise',
        'structure' => 'a reverse-tuck or straight-tuck folding carton with a snug card cavity, optional thumb notch, and reinforced bottom',
        'fit' => 'The cavity should match the wrapped deck thickness and leave controlled finger access without allowing the deck to rattle or slide out.',
        'artwork' => 'Front and spine panels carry the deck identity; the back panel organizes card count, age, instructions, barcode, edition, and manufacturer details.',
        'channel' => 'mass retail, bookstores, travel games, promotional campaigns, classroom supply, and ecommerce multipacks',
        'procurement' => 'finished card size, wrapped deck thickness, card count, tuck style, opening direction, hang-tab need, barcode size, and retail display plan',
        'details' => 'card width|card height|deck thickness|card count|wrap thickness|thumb-notch size|hang-tab need|display orientation',
        'panels' => 'deck name|card count|age mark|quick rules|barcode|edition number|warning text|manufacturer information',
        'qc' => 'deck fit|edge clearance|tuck friction|bottom lock|thumb-notch shape|small text readability|barcode scan|carton squareness',
        'materials' => 'SBS paperboard, ivory board, coated card stock, kraft paperboard, and optional matte or anti-scuff lamination',
        'finish' => 'clean CMYK or Pantone printing with optional spot UV, foil detail, embossing, or matte coating away from glue areas',
        'box_type' => 'Folding Carton Card Game Box',
        'shape' => 'Vertical Rectangle / Deck Fit / Compact Carton',
        'accessories' => 'Thumb notch / Hang tab / Inner card sleeve / Tamper seal optional',
        'liner' => 'No liner / Folded card retainer',
        'colors' => 'White / Black / Graphic color blocks / Customized Color',
        'category_slugs' => array('toy-and-game-packaging-boxes', 'folding-carton-boxes', 'custom-printed-paper-boxes'),
        'tags' => array('card deck box', 'playing card packaging', 'flash card carton'),
        'views' => 'closed retail card carton|opened top tuck with deck|compact deck presentation|card edge and carton detail',
        'related' => array('board-game-packaging-box', 'educational-toy-packaging-box'),
    ));
    $products[] = vpn_threecat_make_product('toy', array(
        'slug' => 'childrens-craft-kit-packaging-box',
        'keyword' => "children's craft kit packaging box",
        'inside' => 'colored pencils, markers, glue sticks, scissors, paper sheets, stickers, beads, and supervised craft accessories',
        'buyer' => 'creative toy brands, school suppliers, museum shops, subscription craft programs, and educational distributors',
        'challenge' => 'organizing mixed-size tools safely so sharp, liquid, and loose components stay separated and the kit can be repacked after use',
        'structure' => 'a corrugated or sturdy paperboard mailer with a hinged lid, shallow tool compartments, paper dividers, and an activity-sheet pocket',
        'fit' => 'Long tools need channels, small pieces need closed wells, and glue or paint containers need upright support to reduce movement and leakage risk.',
        'artwork' => 'The outer panels should show the project result, age guidance, contents, adult-supervision notes, skill level, and any non-toxic or material statements.',
        'channel' => 'classroom programs, craft subscriptions, museum retail, holiday activities, and direct-to-family ecommerce',
        'procurement' => 'complete tool list, longest item, liquid-container dimensions, age grade, safety copy, activity-sheet size, refill plan, and shipping method',
        'details' => 'tool count|longest pencil or marker|scissor size|glue-stick diameter|paper-sheet size|small-parts list|age grade|refill configuration',
        'panels' => 'kit name|project image|age range|contents list|supervision note|non-toxic statement|barcode|activity instructions',
        'qc' => 'tool separation|small-parts containment|lid closure|divider strength|print accuracy|age-mark visibility|component count|parcel-drop resistance',
        'materials' => 'E-flute corrugated board, kraft liner, SBS paperboard, folded dividers, recycled paper tray, and water-based coating',
        'finish' => 'bright CMYK printing, scuff-resistant coating, selective gloss on project graphics, and rounded or protected paper edges',
        'box_type' => "Children's Craft Kit Mailer Box",
        'shape' => 'Shallow Rectangle / Hinged Mailer / Tool Organizer',
        'accessories' => 'Paper dividers / Activity pocket / Tool channels / Small-parts wells',
        'liner' => 'Corrugated divider / Folded paper tray',
        'colors' => 'Natural kraft / Bright learning colors / Customized Color',
        'category_slugs' => array('toy-and-game-packaging-boxes', 'corrugated-mailer-boxes', 'back-to-school-stationery-packaging'),
        'tags' => array('craft kit box', 'kids activity packaging', 'school art kit packaging'),
        'views' => 'closed kids craft kit|opened kit with tools|organized component compartments|pencil glue and scissor detail',
        'related' => array('educational-toy-packaging-box', 'custom-toy-packaging-box'),
        'duplicate_risk' => '5/10',
    ));
    $products[] = vpn_threecat_make_product('toy', array(
        'slug' => 'collectible-figure-packaging-box',
        'keyword' => 'collectible figure packaging box',
        'inside' => 'vinyl figures, character models, limited-edition miniatures, accessories, display stands, and collector cards',
        'buyer' => 'collectible brands, animation licensors, designer-toy studios, event merchandise teams, and specialty retailers',
        'challenge' => 'holding a sculpted figure without paint rub while keeping the character visible and the numbered edition presentation undamaged',
        'structure' => 'a printed folding carton with a clear display window and a thermoformed, paperboard, or molded-pulp inner tray',
        'fit' => 'The tray should support the torso and base rather than pressure fragile ears, hats, hands, painted faces, or removable accessories.',
        'artwork' => 'Character art, figure number, license marks, age guidance, authenticity details, barcode, and edition information need controlled placement around the window.',
        'channel' => 'collector shops, conventions, licensed retail, limited drops, ecommerce launches, and subscription collectibles',
        'procurement' => 'figure height, widest point, fragile projections, accessory list, window size, tray material, edition numbering, and anti-scratch requirement',
        'details' => 'figure height|figure width|fragile projection points|base dimensions|accessory count|window opening|edition number|packed weight',
        'panels' => 'character name|figure number|license mark|age guidance|edition statement|barcode|authenticity QR|warning text',
        'qc' => 'figure restraint|paint clearance|window scratch|tray locking|carton corner|edition print|license mark|drop orientation',
        'materials' => 'SBS paperboard, PET or cellulose window film, molded pulp, thermoformed tray, black card, and anti-scratch lamination',
        'finish' => 'matte black or full-color printing with foil numbering, spot UV, window patching, and controlled anti-scuff surface treatment',
        'box_type' => 'Window Collectible Figure Carton',
        'shape' => 'Vertical Display Carton / Window Box / Figure Fit',
        'accessories' => 'Display window / Fitted tray / Collector card slot / Tamper seal',
        'liner' => 'Thermoformed tray / Paperboard cradle / Molded pulp',
        'colors' => 'Black / Character palette / Gold numbering / Customized Color',
        'category_slugs' => array('toy-and-game-packaging-boxes', 'folding-carton-boxes', 'custom-printed-paper-boxes'),
        'tags' => array('collectible figure box', 'designer toy packaging', 'window display carton'),
        'views' => 'front window figure carton|angled retail display|opened fitted figure tray|figure and tray clearance detail',
        'related' => array('custom-toy-packaging-box', 'board-game-packaging-box'),
    ));
    $products[] = vpn_threecat_make_product('toy', array(
        'slug' => 'custom-toy-packaging-box',
        'keyword' => 'custom toy packaging box',
        'inside' => 'wooden building blocks, shape sets, construction toys, loose geometric pieces, and early-learning play components',
        'buyer' => 'wooden toy makers, Montessori brands, preschool suppliers, educational retailers, and private-label toy companies',
        'challenge' => 'containing loose blocks by shape and count while presenting natural materials, age suitability, and a reusable storage experience',
        'structure' => 'a sturdy folding carton or compact mailer with a locking top, grouped paperboard wells, and enough board strength for dense wooden pieces',
        'fit' => 'Heavy blocks should sit low and distribute weight evenly; separators keep painted surfaces from rubbing and make the component count easy to inspect.',
        'artwork' => 'Simple geometry, play ideas, age guidance, piece count, material statement, care instructions, and safety information should remain easy to scan.',
        'channel' => 'preschool retail, Montessori catalogs, educational distributors, gift shops, and family ecommerce',
        'procurement' => 'block count, largest shape, total weight, paint finish, age grade, storage expectation, insert layout, and shipping orientation',
        'details' => 'piece count|largest block|smallest component|total weight|paint finish|age grade|storage use|drop-test requirement',
        'panels' => 'toy name|piece count|age mark|material note|play examples|care instruction|warning area|barcode',
        'qc' => 'piece count|divider fit|bottom strength|paint-rub clearance|tuck lock|age-mark visibility|corner crush|master-carton weight',
        'materials' => 'SBS paperboard, kraft board, E-flute corrugated board, folded paper dividers, molded pulp, and abrasion-resistant coating',
        'finish' => 'natural kraft or warm CMYK graphics with matte coating, Pantone accents, and optional embossing on simple geometric artwork',
        'box_type' => 'Toy Building Block Packaging Box',
        'shape' => 'Vertical Rectangle / Block Set / Reusable Carton',
        'accessories' => 'Piece dividers / Paper tray / Carry tab optional / Instruction card',
        'liner' => 'Folded paper divider / Molded pulp',
        'colors' => 'Kraft / Navy / Orange / Educational palette / Customized Color',
        'category_slugs' => array('toy-and-game-packaging-boxes', 'folding-carton-boxes', 'custom-paper-boxes'),
        'tags' => array('toy box packaging', 'building block box', 'wooden toy packaging'),
        'views' => 'closed building toy carton|second retail angle|opened box with wooden blocks|printed corner and block detail',
        'related' => array('educational-toy-packaging-box', 'childrens-craft-kit-packaging-box'),
        'duplicate_risk' => '5/10',
    ));
    $products[] = vpn_threecat_make_product('toy', array(
        'slug' => 'educational-toy-packaging-box',
        'keyword' => 'educational toy packaging box',
        'inside' => 'alphabet cards, number blocks, picture cards, phonics pieces, counting tools, and early-learning activity components',
        'buyer' => 'learning brands, preschool chains, tutoring suppliers, school distributors, and child-development product companies',
        'challenge' => 'organizing lesson components in a sequence that supports learning while keeping small pieces visible, countable, and easy to return',
        'structure' => 'a rigid lid-and-base learning set box with a divided insert for cards, letters, numbers, and instruction materials',
        'fit' => 'The insert should separate lesson groups and provide finger access so a teacher or parent can remove one activity without scattering the rest.',
        'artwork' => 'Learning outcomes, age level, component list, language, lesson examples, safety notes, and skill icons should support purchase decisions.',
        'channel' => 'preschool supply, tutoring centers, home learning, educational gifts, and multilingual retail programs',
        'procurement' => 'lesson component list, card dimensions, letter count, block size, language versions, age grade, teacher-guide size, and storage plan',
        'details' => 'card count|letter count|number-piece count|block size|guide-book size|language version|age level|lesson sequence',
        'panels' => 'learning objective|age range|component list|language|skill icons|lesson example|barcode|safety note',
        'qc' => 'component count|insert labels|card fit|piece access|lid alignment|language accuracy|age-mark visibility|repacking test',
        'materials' => 'rigid greyboard, wrapped art paper, SBS divider board, molded pulp, laminated learning cards, and matte coating',
        'finish' => 'soft matte lamination, clean Pantone colors, selective foil or embossing on the title, and durable inner printing',
        'box_type' => 'Rigid Educational Toy Set Box',
        'shape' => 'Horizontal Rectangle / Learning Set / Divided Tray',
        'accessories' => 'Divided insert / Lesson card pocket / Piece wells / Teacher guide',
        'liner' => 'Paperboard organizer / Molded pulp',
        'colors' => 'Cream / Navy / Learning accent colors / Customized Color',
        'category_slugs' => array('toy-and-game-packaging-boxes', 'rigid-boxes', 'lid-and-base-boxes'),
        'tags' => array('learning toy box', 'educational set packaging', 'preschool packaging'),
        'views' => 'closed educational toy set|alphabet and number components|opened divided learning tray|learning card and piece detail',
        'related' => array('childrens-craft-kit-packaging-box', 'card-game-packaging-box'),
    ));
    $products[] = vpn_threecat_make_product('toy', array(
        'slug' => 'puzzle-packaging-box',
        'keyword' => 'puzzle packaging box',
        'inside' => 'jigsaw puzzle pieces, reference artwork, instruction leaflets, sorting cards, and optional storage bags',
        'buyer' => 'puzzle publishers, museum shops, gift brands, educational retailers, and custom artwork licensors',
        'challenge' => 'protecting printed puzzle pieces from crushed edges and lost components while making the finished image and piece count unmistakable',
        'structure' => 'a rigid lid-and-base puzzle box with a shallow paper tray, optional inner bag, and lid depth sized for the full piece stack',
        'fit' => 'The cavity should prevent pieces from moving excessively but leave enough room for a sealed bag, reference sheet, or sorting insert.',
        'artwork' => 'The lid prioritizes finished artwork and title; side and base panels carry piece count, finished size, age, barcode, license, and warning information.',
        'channel' => 'bookstores, museum retail, gift shops, educational programs, licensed merchandise, and ecommerce',
        'procurement' => 'piece count, piece thickness, finished puzzle size, bag or tray preference, reference print size, license marks, and packed weight',
        'details' => 'piece count|piece thickness|finished dimensions|stack height|inner bag size|reference sheet|age grade|license artwork',
        'panels' => 'finished artwork|puzzle title|piece count|finished size|age mark|license line|barcode|warning area',
        'qc' => 'piece count|lid fit|corner strength|print color|inner-bag seal|reference-sheet accuracy|barcode scan|carton compression',
        'materials' => 'rigid greyboard, wrapped art paper, chipboard tray, glassine or paper inner bag, and anti-scuff matte lamination',
        'finish' => 'high-fidelity CMYK artwork with matte or linen texture, optional spot UV, foil title, and reinforced wrapped corners',
        'box_type' => 'Rigid Jigsaw Puzzle Box',
        'shape' => 'Vertical Rectangle / Lid and Base / Puzzle Stack',
        'accessories' => 'Inner paper bag / Reference sheet / Sorting insert optional',
        'liner' => 'Shallow paper tray / No liner',
        'colors' => 'Artwork matched / Green / Cream / Customized Color',
        'category_slugs' => array('toy-and-game-packaging-boxes', 'rigid-boxes', 'lid-and-base-boxes'),
        'tags' => array('jigsaw puzzle box', 'puzzle game packaging', 'rigid puzzle carton'),
        'views' => 'closed puzzle box|opened lid and piece tray|puzzle pieces inside box|corner and piece detail',
        'related' => array('board-game-packaging-box', 'card-game-packaging-box'),
    ));

    $products[] = vpn_threecat_make_product('tea', array(
        'slug' => 'coffee-capsule-packaging-box',
        'keyword' => 'coffee capsule packaging box',
        'inside' => 'aluminum or plastic coffee capsules arranged by roast, flavor, intensity, and serving count',
        'buyer' => 'coffee roasters, capsule fillers, private-label beverage brands, hotels, offices, and specialty retailers',
        'challenge' => 'keeping capsules separated and countable while protecting foil tops and communicating machine compatibility, intensity, and serving quantity',
        'structure' => 'a rigid hinged or lid-and-base presentation box with a cell divider sized to each capsule diameter and rim',
        'fit' => 'Divider cells should hold the capsule body without pressing the foil seal, and the layout should make missing or incorrect flavors immediately visible.',
        'artwork' => 'Compatibility, roast intensity, capsule count, origin, brewing volume, recycling notes, barcode, lot, and best-before areas require a disciplined hierarchy.',
        'channel' => 'specialty retail, hotel amenities, office coffee supply, subscription programs, gifting, and private-label ecommerce',
        'procurement' => 'capsule diameter, rim width, height, count, flavor sequence, machine compatibility, filled weight, and primary-barrier specification',
        'details' => 'capsule diameter|rim width|capsule height|capsule count|flavor order|machine compatibility|brewing volume|filled weight',
        'panels' => 'roast name|intensity scale|capsule count|compatibility|origin|brew volume|barcode|lot and best-before zone',
        'qc' => 'cell size|foil clearance|capsule count|flavor order|lid closure|small text|barcode scan|divider compression',
        'materials' => 'rigid greyboard, SBS divider board, wrapped art paper, kraft paper, and food-retail suitable surface coating',
        'finish' => 'matte botanical printing, foil logo, restrained embossing, and inner color coding for capsule varieties',
        'box_type' => 'Coffee Capsule Compartment Box',
        'shape' => 'Horizontal Rectangle / Hinged Lid / Capsule Grid',
        'accessories' => 'Cell divider / Flavor card / Magnetic or tuck closure optional',
        'liner' => 'Paperboard cell divider',
        'colors' => 'Cream / Olive / Coffee brown / Customized Color',
        'category_slugs' => array('tea-and-coffee-packaging-boxes', 'rigid-boxes', 'lid-and-base-boxes'),
        'tags' => array('coffee capsule box', 'pod packaging', 'capsule gift box'),
        'views' => 'closed coffee capsule box|opened capsule presentation|full capsule divider grid|capsule and divider detail',
        'related' => array('coffee-drip-bag-packaging-box', 'tea-coffee-gift-set-box'),
    ));
    $products[] = vpn_threecat_make_product('tea', array(
        'slug' => 'coffee-drip-bag-packaging-box',
        'keyword' => 'coffee drip bag packaging box',
        'inside' => 'individually sealed pour-over drip coffee sachets organized by roast, origin, and serving count',
        'buyer' => 'specialty roasters, hotel suppliers, travel coffee brands, office programs, and private-label beverage distributors',
        'challenge' => 'holding slim sealed sachets upright without crushing their filter frames while keeping count, origin, and brew instructions easy to read',
        'structure' => 'a vertical folding carton with a secure top tuck, reinforced base, and internal width matched to the filled sachet stack',
        'fit' => 'The carton should control lateral movement but avoid pressure that can crease the filter arms or puncture the aroma-barrier sachet.',
        'artwork' => 'Roast, origin, tasting notes, sachet count, brew steps, water volume, barcode, lot, and best-before information need clear panel zones.',
        'channel' => 'travel retail, hotels, office supply, convenience stores, subscriptions, and sampler programs',
        'procurement' => 'sachet dimensions, filter-frame width, pack count, roast versions, brew instructions, barrier pouch type, filled weight, and carton opening',
        'details' => 'sachet width|sachet height|filter-frame width|pack count|roast version|water volume|brew time|barrier pouch type',
        'panels' => 'coffee origin|roast level|tasting notes|sachet count|brew steps|barcode|lot code|best-before area',
        'qc' => 'sachet fit|filter-frame clearance|tuck strength|pack count|version accuracy|brew-text readability|barcode scan|carton compression',
        'materials' => 'SBS paperboard, ivory board, kraft paperboard, coated art paper, and optional water-based or matte coating',
        'finish' => 'warm CMYK printing, Pantone roast coding, matte lamination, optional foil seal mark, and readable uncoated instruction areas',
        'box_type' => 'Vertical Drip Coffee Sachet Carton',
        'shape' => 'Tall Rectangle / Sachet Stack / Folding Carton',
        'accessories' => 'Inner card spacer / Tear strip / Display perforation optional',
        'liner' => 'No liner / Paper spacer',
        'colors' => 'Coffee brown / Kraft / Burgundy / Customized Color',
        'category_slugs' => array('tea-and-coffee-packaging-boxes', 'folding-carton-boxes', 'custom-printed-paper-boxes'),
        'tags' => array('drip coffee box', 'pour over coffee packaging', 'coffee sachet carton'),
        'views' => 'closed drip coffee carton|carton with sealed sachet|opened sachet stack|drip bag and panel detail',
        'related' => array('custom-coffee-bean-packaging-box', 'coffee-capsule-packaging-box'),
    ));
    $products[] = vpn_threecat_make_product('tea', array(
        'slug' => 'custom-coffee-bean-packaging-box',
        'keyword' => 'custom coffee bean packaging box',
        'inside' => 'sealed whole-bean coffee pouches, roast sample bags, retail bean packs, and origin-specific coffee products',
        'buyer' => 'specialty roasters, cafe chains, origin exporters, private-label sellers, and gourmet food retailers',
        'challenge' => 'supporting a sealed aroma-barrier pouch while presenting bean visibility, roast identity, origin, and freshness information on shelf',
        'structure' => 'a tall folding carton with reinforced bottom, top tuck, optional product window, and internal clearance for a degassing-valve pouch',
        'fit' => 'The carton must allow for pouch expansion and valve position without squeezing roasted beans or pushing the top flaps open.',
        'artwork' => 'Origin, varietal, process, roast date, roast level, tasting notes, net weight, barcode, lot, and storage guidance form the information system.',
        'channel' => 'cafes, grocery shelves, farm-to-cup programs, subscription coffee, export gifting, and private-label retail',
        'procurement' => 'filled pouch dimensions, valve location, bean weight, roast variants, window requirement, roast-date coding, shelf orientation, and master-carton count',
        'details' => 'filled pouch width|pouch gusset|valve position|net weight|bean origin|roast variants|window size|date-code method',
        'panels' => 'origin|varietal|process|roast level|tasting notes|net weight|barcode|roast and lot code',
        'qc' => 'pouch clearance|valve clearance|bottom strength|window bonding|color accuracy|date-code zone|barcode scan|stacking strength',
        'materials' => 'SBS paperboard, kraft board, ivory board, cellulose or PET window film, and moisture-resistant surface coating',
        'finish' => 'botanical CMYK artwork, matte lamination, kraft texture, spot UV on bean graphics, or selective foil on the roaster mark',
        'box_type' => 'Coffee Bean Pouch Folding Carton',
        'shape' => 'Tall Rectangle / Window Carton / Pouch Fit',
        'accessories' => 'Product window / Reinforced base / Pouch spacer optional',
        'liner' => 'No liner / Paperboard spacer',
        'colors' => 'Kraft / Forest green / Coffee brown / Customized Color',
        'category_slugs' => array('tea-and-coffee-packaging-boxes', 'folding-carton-boxes', 'custom-printed-paper-boxes'),
        'tags' => array('coffee bean box', 'roasted coffee packaging', 'coffee pouch carton'),
        'views' => 'closed coffee bean window box|second retail angle|opened carton with bean pouch|window and roast detail',
        'related' => array('coffee-drip-bag-packaging-box', 'tea-coffee-gift-set-box'),
    ));
    $products[] = vpn_threecat_make_product('tea', array(
        'slug' => 'custom-tea-bag-packaging-box',
        'keyword' => 'custom tea bag packaging box',
        'inside' => 'individually wrapped herbal tea bags, pyramid sachets, infusion envelopes, and assorted flavor packs',
        'buyer' => 'tea blenders, wellness brands, hotel suppliers, grocery retailers, and private-label beverage companies',
        'challenge' => 'keeping wrapped tea bags upright and counted while protecting delicate sachets and presenting blend benefits without crowding the carton',
        'structure' => 'a vertical folding carton with reinforced tuck closure, optional perforated dispenser, and interior sized around the envelope stack',
        'fit' => 'Envelope width, seal edges, and pack count determine carton depth; excessive pressure can crease sachets or make the dispenser difficult to use.',
        'artwork' => 'Blend name, ingredients, caffeine note, tea-bag count, brew time, water temperature, net weight, barcode, lot, and best-before areas must be organized.',
        'channel' => 'grocery retail, wellness shops, hotel rooms, office pantry programs, subscriptions, and gift assortments',
        'procurement' => 'wrapped sachet size, tea-bag style, envelope count, blend versions, brew instructions, ingredient panel, coding method, and dispenser need',
        'details' => 'envelope width|envelope height|tea-bag style|pack count|blend versions|brew temperature|brew time|dispenser opening',
        'panels' => 'blend name|ingredients|caffeine note|tea-bag count|brew guide|net weight|barcode|lot and best-before zone',
        'qc' => 'sachet count|envelope clearance|tuck closure|dispenser tear|ingredient accuracy|brew-text readability|barcode scan|carton squareness',
        'materials' => 'SBS paperboard, ivory board, kraft paperboard, coated art paper, and optional food-retail matte coating',
        'finish' => 'soft botanical printing, Pantone blend coding, matte or water-based coating, embossing, and restrained foil on the brand mark',
        'box_type' => 'Tea Bag Folding Carton',
        'shape' => 'Tall Rectangle / Sachet Stack / Optional Dispenser',
        'accessories' => 'Dispenser perforation / Inner spacer / Tamper seal optional',
        'liner' => 'No liner / Paper spacer',
        'colors' => 'Cream / Sage green / Botanical palette / Customized Color',
        'category_slugs' => array('tea-and-coffee-packaging-boxes', 'folding-carton-boxes', 'custom-printed-paper-boxes'),
        'tags' => array('tea bag box', 'herbal tea carton', 'tea sachet packaging'),
        'views' => 'closed herbal tea carton|second tea retail angle|opened wrapped tea bags|tea bag and botanical detail',
        'related' => array('loose-leaf-tea-packaging-box', 'tea-gift-box-with-compartments'),
    ));
    $products[] = vpn_threecat_make_product('tea', array(
        'slug' => 'loose-leaf-tea-packaging-box',
        'keyword' => 'loose leaf tea packaging box',
        'inside' => 'sealed loose-leaf tea pouches, refill bags, origin teas, botanical blends, and premium single-estate packs',
        'buyer' => 'tea estates, specialty blenders, wellness retailers, private-label brands, and export tea suppliers',
        'challenge' => 'supporting a flexible sealed pouch while communicating origin, grade, harvest, brew method, and freshness without implying the paper carton is the aroma barrier',
        'structure' => 'a tall reverse-tuck or auto-bottom carton with pouch clearance, reinforced base, and optional foil or embossing for premium shelf presence',
        'fit' => 'Pouch gusset, fill volume, seal width, and trapped air determine the real carton size; testing should use a filled production pouch.',
        'artwork' => 'Tea type, estate, origin, harvest, grade, ingredients, brew ratio, temperature, net weight, barcode, lot, and best-before zones need version control.',
        'channel' => 'specialty tea shops, wellness retail, export gifting, ecommerce refills, hotel boutiques, and subscription programs',
        'procurement' => 'filled pouch dimensions, net weight, tea grade, harvest variants, seal width, coding method, sustainability claims, and shelf direction',
        'details' => 'filled pouch width|pouch gusset|seal width|net weight|tea grade|harvest version|brew ratio|date-code method',
        'panels' => 'tea type|estate and origin|harvest|grade|ingredients|brew guide|barcode|lot and best-before area',
        'qc' => 'pouch fit|bottom strength|tuck closure|origin accuracy|small text|foil registration|barcode scan|stacking compression',
        'materials' => 'SBS paperboard, ivory board, kraft paperboard, specialty textured paper, and matte or water-based coating',
        'finish' => 'natural botanical graphics, subtle foil, embossing, matte lamination, textured stock, or an uncoated premium paper direction',
        'box_type' => 'Loose Leaf Tea Pouch Carton',
        'shape' => 'Tall Rectangle / Pouch Fit / Folding Carton',
        'accessories' => 'Pouch spacer / Reinforced base / Tamper label optional',
        'liner' => 'No liner / Paperboard support',
        'colors' => 'Cream / Olive / Natural paper / Customized Color',
        'category_slugs' => array('tea-and-coffee-packaging-boxes', 'folding-carton-boxes', 'custom-paper-boxes'),
        'tags' => array('loose leaf tea box', 'tea pouch carton', 'premium tea packaging'),
        'views' => 'closed loose leaf tea box|box with sealed tea pouch|opened pouch carton|premium lid and logo detail',
        'related' => array('custom-tea-bag-packaging-box', 'tea-coffee-gift-set-box'),
    ));
    $products[] = vpn_threecat_make_product('tea', array(
        'slug' => 'tea-coffee-gift-set-box',
        'keyword' => 'tea coffee gift set box',
        'inside' => 'tea tins, coffee tins, mugs or tumblers, brew accessories, tasting cards, and mixed beverage gifts',
        'buyer' => 'premium beverage brands, corporate gift buyers, hotels, distributors, and seasonal retail programs',
        'challenge' => 'balancing containers of different heights and weights while creating a controlled reveal and preventing metal tins or cups from colliding',
        'structure' => 'a rigid presentation box with a hinged or lift-off lid and a multi-cavity paperboard, EVA, or molded-pulp insert',
        'fit' => 'Each cavity should support the container body, protect printed surfaces, and provide finger access without letting the tallest item press against the lid.',
        'artwork' => 'Outer branding can stay restrained while the inner panel explains tea, coffee, brew tools, tasting notes, gift message, and component order.',
        'channel' => 'corporate gifting, holiday retail, hotel welcome gifts, distributor presentations, influencer kits, and premium ecommerce',
        'procurement' => 'item dimensions, filled weights, component order, insert material, lid style, gift-message area, finishing masks, and assembled shipping requirement',
        'details' => 'tea tin size|coffee tin size|cup or accessory size|component count|filled weights|insert depth|lid clearance|gift-card size',
        'panels' => 'brand logo|gift-set name|component list|tea description|coffee description|brew note|QR code|gift message',
        'qc' => 'cavity fit|item separation|lid alignment|insert locking|foil position|inner print|component count|carton drop resistance',
        'materials' => 'rigid greyboard, wrapped art paper, specialty paper, EVA, molded pulp, folded paperboard tray, and anti-scuff lamination',
        'finish' => 'soft-touch or matte wrap, foil logo, embossing, inner printing, ribbon pull, and restrained color blocking',
        'box_type' => 'Rigid Tea and Coffee Gift Set Box',
        'shape' => 'Horizontal Rectangle / Multi-Cavity Gift Set',
        'accessories' => 'Fitted insert / Ribbon / Gift card / Magnetic closure optional',
        'liner' => 'Wrapped paperboard tray / EVA / Molded pulp',
        'colors' => 'Cream / Deep green / Coffee brown / Customized Color',
        'category_slugs' => array('tea-and-coffee-packaging-boxes', 'rigid-boxes', 'gift-paper-boxes'),
        'tags' => array('tea coffee gift box', 'beverage gift set', 'corporate drink gift packaging'),
        'views' => 'closed tea and coffee gift box|opened mixed beverage set|full insert and container layout|tea coffee compartment detail',
        'related' => array('tea-gift-box-with-compartments', 'coffee-capsule-packaging-box'),
    ));
    $products[] = vpn_threecat_make_product('tea', array(
        'slug' => 'tea-gift-box-with-compartments',
        'keyword' => 'tea gift box with compartments',
        'inside' => 'assorted tea sachets, tea envelopes, tasting cards, flavor collections, and premium infusion selections',
        'buyer' => 'tea houses, hospitality brands, corporate gift teams, wellness retailers, and seasonal gift suppliers',
        'challenge' => 'keeping multiple tea varieties separated and labeled while making the tasting sequence easy to understand and refill',
        'structure' => 'a rigid drawer or lid-and-base gift box with a paperboard grid, ribbon pull, and labeled tea compartments',
        'fit' => 'Compartment dimensions should match the largest sachet envelope and maintain enough finger space for removal without tearing wrappers.',
        'artwork' => 'Flavor names, tea type, caffeine note, brew guide, tasting order, QR story, and gift information can be divided between sleeve, drawer, and inner grid.',
        'channel' => 'hotel tea service, corporate gifts, wellness gifting, tea subscriptions, holiday retail, and premium tasting programs',
        'procurement' => 'sachet dimensions, flavor count, sachets per flavor, compartment grid, drawer tolerance, label method, ribbon length, and refill expectation',
        'details' => 'sachet width|sachet height|flavor count|units per flavor|grid dimensions|drawer clearance|ribbon length|tasting-card size',
        'panels' => 'gift-set name|flavor list|tea type|caffeine note|brew guide|tasting order|QR story|gift message',
        'qc' => 'drawer movement|grid size|flavor order|label accuracy|ribbon strength|lid alignment|foil registration|component count',
        'materials' => 'rigid greyboard, specialty paper, wrapped paperboard dividers, art paper, ribbon, and anti-scuff matte lamination',
        'finish' => 'foil title, debossing, botanical inner printing, matte wrap, ribbon pull, and color-coded compartment labels',
        'box_type' => 'Compartment Tea Drawer Gift Box',
        'shape' => 'Horizontal Drawer / Grid Insert / Tasting Set',
        'accessories' => 'Compartment grid / Ribbon pull / Tasting card / Flavor labels',
        'liner' => 'Wrapped paperboard grid',
        'colors' => 'Olive / Cream / Burgundy accents / Customized Color',
        'category_slugs' => array('tea-and-coffee-packaging-boxes', 'drawer-boxes', 'rigid-boxes', 'gift-paper-boxes'),
        'tags' => array('tea gift box', 'tea assortment packaging', 'compartment tea box'),
        'views' => 'closed tea drawer gift box|opened tea compartment grid|full assortment presentation|sachet and divider detail',
        'related' => array('tea-coffee-gift-set-box', 'custom-tea-bag-packaging-box'),
    ));

    $products[] = vpn_threecat_make_product('pet', array(
        'slug' => 'custom-pet-food-packaging-box',
        'keyword' => 'custom pet food packaging box',
        'inside' => 'sealed dry-food pouches, portion packs, sample bags, freeze-dried meals, and shelf-ready pet food units',
        'buyer' => 'pet food brands, veterinary retailers, subscription suppliers, distributors, and private-label manufacturers',
        'challenge' => 'supporting the sealed primary food pack while making species, life stage, flavor, feeding information, net weight, and traceability easy to verify',
        'structure' => 'a reinforced folding carton with top tuck, stable base, optional product window, and clearance for the filled pouch gusset',
        'fit' => 'The box should accommodate pouch expansion without squeezing food pieces or interfering with seals, zippers, or lot-code areas.',
        'artwork' => 'Species, life stage, recipe, feeding guide, ingredients, analysis, storage, net weight, barcode, lot, and best-before information need a clear hierarchy.',
        'channel' => 'pet stores, veterinary retail, grocery shelves, sampling programs, subscriptions, and ecommerce',
        'procurement' => 'filled pouch size, net weight, recipe versions, life-stage matrix, window need, feeding panel, coding method, and shelf orientation',
        'details' => 'filled pouch width|pouch gusset|net weight|food format|recipe versions|life stage|window size|lot-code method',
        'panels' => 'species|life stage|recipe|ingredients|feeding guide|net weight|barcode|lot and best-before area',
        'qc' => 'pouch clearance|base strength|window bond|recipe accuracy|feeding-text readability|barcode scan|lot-code zone|stacking pressure',
        'materials' => 'SBS paperboard, kraft paperboard, ivory board, cellulose or PET window film, and moisture-resistant coating',
        'finish' => 'natural kraft or warm CMYK illustration, matte coating, Pantone recipe coding, and selective gloss on product cues',
        'box_type' => 'Pet Food Pouch Folding Carton',
        'shape' => 'Tall Rectangle / Pouch Fit / Window Carton',
        'accessories' => 'Product window / Reinforced base / Pouch spacer optional',
        'liner' => 'No liner / Paperboard spacer',
        'colors' => 'Kraft / Olive / Recipe color system / Customized Color',
        'category_slugs' => array('pet-product-packaging-boxes', 'folding-carton-boxes', 'food-paper-boxes'),
        'tags' => array('pet food box', 'dog food packaging', 'cat food carton'),
        'views' => 'closed pet food window box|second retail angle|opened box with sealed food pouch|food window and information detail',
        'related' => array('custom-pet-treat-packaging-box', 'pet-subscription-mailer-box'),
    ));
    $products[] = vpn_threecat_make_product('pet', array(
        'slug' => 'custom-pet-treat-packaging-box',
        'keyword' => 'custom pet treat packaging box',
        'inside' => 'sealed dog treats, biscuit pouches, dental chews, training rewards, and portioned snack packs',
        'buyer' => 'pet treat brands, bakeries, veterinary shops, training suppliers, and private-label pet retailers',
        'challenge' => 'protecting brittle treat shapes and the sealed inner pack while making flavor, size, feeding limits, ingredients, and pet suitability obvious',
        'structure' => 'a folding carton with secure tuck closure, reinforced bottom, optional display window, and enough depth for irregular treat shapes',
        'fit' => 'The carton must be tested around the filled pouch or tray because bone-shaped biscuits create different pressure points from small training treats.',
        'artwork' => 'Pet type, treat purpose, flavor, ingredients, feeding guidance, net weight, barcode, lot, and best-before zones should not compete with the brand story.',
        'channel' => 'pet specialty retail, training programs, subscription boxes, veterinary counters, gift add-ons, and ecommerce',
        'procurement' => 'treat shape, inner-pack size, net weight, breakage risk, flavor versions, feeding guidance, window size, and coding method',
        'details' => 'treat dimensions|inner-pack size|net weight|breakage risk|flavor versions|feeding limit|window opening|date-code method',
        'panels' => 'pet type|treat purpose|flavor|ingredients|feeding guide|net weight|barcode|lot and best-before zone',
        'qc' => 'inner-pack fit|treat breakage|window bond|bottom strength|flavor accuracy|feeding-text readability|barcode scan|carton compression',
        'materials' => 'SBS paperboard, ivory board, kraft board, cellulose or PET window film, and scuff-resistant matte coating',
        'finish' => 'friendly CMYK graphics, Pantone flavor bands, matte lamination, spot UV on the product name, and clean window patching',
        'box_type' => 'Pet Treat Window Folding Carton',
        'shape' => 'Vertical Rectangle / Treat Pouch Fit / Window Box',
        'accessories' => 'Product window / Inner spacer / Tamper seal optional',
        'liner' => 'No liner / Paperboard support',
        'colors' => 'Cream / Blue / Orange flavor accents / Customized Color',
        'category_slugs' => array('pet-product-packaging-boxes', 'folding-carton-boxes', 'food-paper-boxes'),
        'tags' => array('pet treat box', 'dog biscuit packaging', 'training treat carton'),
        'views' => 'closed dog treat window box|second carton angle|opened treat carton|treat window and feeding detail',
        'related' => array('custom-pet-food-packaging-box', 'pet-product-sample-kit-box'),
        'duplicate_risk' => '5/10',
    ));
    $products[] = vpn_threecat_make_product('pet', array(
        'slug' => 'pet-accessory-gift-box',
        'keyword' => 'pet accessory gift box',
        'inside' => 'collars, leashes, harness accessories, tags, care cards, and premium pet-owner gifts',
        'buyer' => 'pet accessory brands, boutiques, groomers, corporate gift teams, and premium ecommerce retailers',
        'challenge' => 'presenting flexible straps and metal hardware without tangling, scratching, or shifting while creating a gift-ready reveal',
        'structure' => 'a rigid lid-and-base gift box with a shallow wrapped tray, strap channels, hardware recesses, and optional paper filler',
        'fit' => 'Rolled straps should be supported at a consistent diameter and metal clips should be isolated from coated surfaces and printed cards.',
        'artwork' => 'Size, pet type, material, care, hardware finish, gift message, barcode, and brand story can be divided between outer lid and inner card.',
        'channel' => 'pet boutiques, gifting, influencer kits, premium ecommerce, groomer retail, and branded pet-owner programs',
        'procurement' => 'collar or leash dimensions, strap width, hardware size, rolled diameter, item count, insert style, gift-card size, and shipping method',
        'details' => 'strap width|strap length|rolled diameter|hardware dimensions|item count|care-card size|insert depth|packed weight',
        'panels' => 'brand logo|accessory name|pet size|material|care guide|hardware finish|barcode|gift message',
        'qc' => 'strap restraint|hardware isolation|lid fit|insert depth|surface scratches|foil position|component count|drop protection',
        'materials' => 'rigid greyboard, wrapped art paper, specialty paper, folded paper tray, molded pulp, tissue, and paper filler',
        'finish' => 'matte or soft-touch wrap, foil logo, embossing, inner brand message, and restrained premium color blocking',
        'box_type' => 'Rigid Pet Accessory Gift Box',
        'shape' => 'Square / Lid and Base / Accessory Tray',
        'accessories' => 'Strap channels / Hardware recess / Care card / Paper filler',
        'liner' => 'Wrapped paperboard tray / Molded pulp',
        'colors' => 'Cream / Forest green / Gold / Customized Color',
        'category_slugs' => array('pet-product-packaging-boxes', 'rigid-boxes', 'gift-paper-boxes', 'lid-and-base-boxes'),
        'tags' => array('pet accessory box', 'collar gift packaging', 'pet gift box'),
        'views' => 'opened pet accessory gift box|second lid and base angle|organized collar and hardware|strap and insert detail',
        'related' => array('pet-product-sample-kit-box', 'pet-subscription-mailer-box'),
    ));
    $products[] = vpn_threecat_make_product('pet', array(
        'slug' => 'pet-grooming-product-packaging-box',
        'keyword' => 'pet grooming product packaging box',
        'inside' => 'grooming sprays, deodorizing bottles, coat-care serums, small shampoos, brushes, and instruction leaflets',
        'buyer' => 'pet grooming brands, salons, veterinary retailers, private-label care suppliers, and ecommerce sellers',
        'challenge' => 'holding a bottle upright, protecting the pump or spray head, and organizing usage, ingredient, caution, and pet-type information',
        'structure' => 'a vertical folding carton with a bottle-fit paperboard collar, reinforced bottom, and top clearance around the pump',
        'fit' => 'The insert should support the bottle shoulder or base without pressing the actuator, and the carton should be tested with the filled bottle weight.',
        'artwork' => 'Product purpose, coat type, usage steps, ingredients, caution, volume, barcode, batch, and expiry or PAO information need readable zones.',
        'channel' => 'grooming salons, veterinary shops, pet specialty retail, sampling, private label, and ecommerce',
        'procurement' => 'bottle dimensions, filled weight, pump height, actuator clearance, formula versions, leaflet size, caution copy, and shipping orientation',
        'details' => 'bottle diameter|bottle height|pump width|actuator clearance|filled weight|formula variants|leaflet size|shipping direction',
        'panels' => 'product purpose|pet or coat type|usage steps|ingredients|caution|volume|barcode|batch and expiry zone',
        'qc' => 'bottle fit|pump clearance|bottom strength|insert locking|ingredient accuracy|caution readability|barcode scan|leak orientation',
        'materials' => 'SBS paperboard, ivory board, kraft board, folded paper insert, molded pulp, and moisture-resistant matte coating',
        'finish' => 'clean care-focused CMYK printing, Pantone formula coding, matte lamination, spot UV, and optional foil on the brand mark',
        'box_type' => 'Pet Grooming Bottle Folding Carton',
        'shape' => 'Tall Rectangle / Bottle Fit / Pump Clearance',
        'accessories' => 'Bottle collar / Leaflet slot / Reinforced base',
        'liner' => 'Folded paperboard insert / Molded pulp',
        'colors' => 'Cream / Slate blue / Formula color system / Customized Color',
        'category_slugs' => array('pet-product-packaging-boxes', 'folding-carton-boxes', 'custom-paper-boxes'),
        'tags' => array('pet grooming box', 'pet spray carton', 'pet care bottle packaging'),
        'views' => 'grooming spray carton with bottle|second care product angle|opened bottle-fit carton|pump clearance and insert detail',
        'related' => array('pet-supplement-packaging-box', 'pet-product-sample-kit-box'),
    ));
    $products[] = vpn_threecat_make_product('pet', array(
        'slug' => 'pet-product-sample-kit-box',
        'keyword' => 'pet product sample kit box',
        'inside' => 'sample treat pouches, dental chews, grooming minis, supplement bottles, information cards, and trial-size pet products',
        'buyer' => 'pet brands, veterinary programs, subscription marketers, retail launch teams, and distributor sampling campaigns',
        'challenge' => 'organizing different formats and versions in one compact kit so every sample stays separated, labeled, and easy to evaluate',
        'structure' => 'a corrugated mailer or rigid sample box with divided paperboard cavities, product labels, and a card pocket in the lid',
        'fit' => 'Pouches need flat support, bottles need upright cavities, and mixed samples need enough separation to prevent leaks, abrasion, or label confusion.',
        'artwork' => 'Kit purpose, sample list, pet profile, trial order, usage notes, QR feedback, warnings, and campaign information can guide the recipient.',
        'channel' => 'vet trials, influencer seeding, new-product launches, retailer education, subscription acquisition, and distributor presentations',
        'procurement' => 'sample list, each item dimension, filled weights, usage sequence, card size, version matrix, campaign quantity, and parcel-shipping route',
        'details' => 'sample count|pouch sizes|bottle size|filled weights|usage sequence|card dimensions|version matrix|parcel route',
        'panels' => 'kit name|sample list|pet profile|usage order|QR feedback|warnings|campaign code|contact information',
        'qc' => 'sample count|cavity fit|bottle restraint|pouch separation|version accuracy|card placement|QR scan|mailer drop test',
        'materials' => 'E-flute corrugated board, rigid greyboard, kraft liner, folded paper dividers, molded pulp, and matte printed paper',
        'finish' => 'friendly inner printing, matte coating, campaign labels, Pantone accents, and simple recyclable paper insert construction',
        'box_type' => 'Pet Product Sample Kit Mailer',
        'shape' => 'Shallow Rectangle / Mixed Sample Kit / Divided Mailer',
        'accessories' => 'Multi-cavity divider / Card pocket / Sample labels / QR card',
        'liner' => 'Corrugated divider / Folded paper tray',
        'colors' => 'Cream / Sage / Campaign accents / Customized Color',
        'category_slugs' => array('pet-product-packaging-boxes', 'corrugated-mailer-boxes', 'gift-paper-boxes'),
        'tags' => array('pet sample kit', 'pet trial box', 'pet product launch packaging'),
        'views' => 'closed pet sample kit|opened mixed trial products|full divider and sample layout|treat bottle and cavity detail',
        'related' => array('pet-subscription-mailer-box', 'custom-pet-treat-packaging-box'),
    ));
    $products[] = vpn_threecat_make_product('pet', array(
        'slug' => 'pet-subscription-mailer-box',
        'keyword' => 'pet subscription mailer box',
        'inside' => 'monthly treat pouches, toys, grooming items, accessories, cards, and rotating pet-care products',
        'buyer' => 'pet subscription companies, ecommerce brands, loyalty programs, shelters, and direct-to-consumer retailers',
        'challenge' => 'protecting a changing product mix during parcel delivery while preserving a repeatable packing order and branded unboxing experience',
        'structure' => 'a self-locking corrugated mailer with dust flaps, reinforced corners, printed inner lid, and optional paper dividers or filler',
        'fit' => 'The box should be sized around the largest monthly combination, with fillers or dividers that control movement without excessive dimensional weight.',
        'artwork' => 'Outer shipping marks can stay discreet while the inner lid carries campaign message, contents, pet profile, QR engagement, and reorder information.',
        'channel' => 'monthly subscriptions, adoption kits, loyalty gifts, influencer boxes, seasonal bundles, and ecommerce fulfillment',
        'procurement' => 'largest item, monthly item matrix, total weight, parcel carrier, filler plan, return label need, campaign versions, and master-carton delivery',
        'details' => 'largest item|monthly item count|total weight|box dimensions|carrier limits|filler volume|campaign versions|packing sequence',
        'panels' => 'subscription name|pet profile|monthly theme|contents|QR engagement|reorder message|shipping mark|recycling note',
        'qc' => 'mailer lock|corner strength|item movement|divider fit|inner print|campaign accuracy|drop test|dimensional-weight target',
        'materials' => 'E-flute or B-flute corrugated board, kraft liner, white-top liner, folded dividers, tissue, and shredded paper filler',
        'finish' => 'flexographic or offset printing, water-based coating, kraft presentation, inner-lid CMYK, and optional branded tissue',
        'box_type' => 'Corrugated Pet Subscription Mailer',
        'shape' => 'Shallow Mailer / Self-Locking / Mixed Product Kit',
        'accessories' => 'Dust flaps / Paper filler / Divider / Printed card / Tissue',
        'liner' => 'Corrugated divider / Paper filler',
        'colors' => 'Natural kraft / Olive / Seasonal campaign colors',
        'category_slugs' => array('pet-product-packaging-boxes', 'corrugated-mailer-boxes', 'custom-paper-boxes'),
        'tags' => array('pet subscription box', 'pet mailer box', 'monthly pet packaging'),
        'views' => 'opened pet subscription mailer|second full kit angle|monthly products and filler|treat toy and kraft detail',
        'related' => array('pet-product-sample-kit-box', 'pet-accessory-gift-box'),
    ));
    $products[] = vpn_threecat_make_product('pet', array(
        'slug' => 'pet-supplement-packaging-box',
        'keyword' => 'pet supplement packaging box',
        'inside' => 'pet vitamin bottles, joint-support tablets, probiotic jars, calming supplements, and veterinary wellness products',
        'buyer' => 'pet supplement brands, veterinary distributors, private-label laboratories, wellness retailers, and ecommerce sellers',
        'challenge' => 'holding a bottle securely while giving dosage, species, pet weight, ingredients, warnings, count, and traceability enough readable space',
        'structure' => 'a bottle-fit folding carton with reinforced base, top clearance, and optional paperboard collar or leaflet slot',
        'fit' => 'Bottle diameter, cap height, filled weight, and label clearance should drive the dieline; the insert must not hide the lot or expiry area.',
        'artwork' => 'Species, benefit, active ingredients, dosage by pet weight, serving count, warnings, storage, barcode, lot, and expiry information need strict hierarchy.',
        'channel' => 'veterinary clinics, pet wellness retail, pharmacies, subscriptions, private label, and ecommerce',
        'procurement' => 'bottle dimensions, cap height, tablet count, formula versions, dosage table, leaflet size, lot and expiry coding, and market requirements',
        'details' => 'bottle diameter|bottle height|cap height|tablet count|formula versions|dosage table|leaflet size|lot-code method',
        'panels' => 'species|wellness benefit|active ingredients|dosage by weight|serving count|warnings|barcode|lot and expiry zone',
        'qc' => 'bottle fit|cap clearance|bottom strength|dosage accuracy|small text|lot-code area|barcode scan|version control',
        'materials' => 'SBS paperboard, ivory board, kraft paperboard, folded paper insert, molded pulp, and matte or water-based coating',
        'finish' => 'trust-focused CMYK printing, Pantone formula coding, matte lamination, spot UV, and restrained foil away from dosage text',
        'box_type' => 'Pet Supplement Bottle Folding Carton',
        'shape' => 'Vertical Rectangle / Bottle Fit / Information Carton',
        'accessories' => 'Bottle collar / Leaflet slot / Tamper seal optional',
        'liner' => 'Folded paperboard insert / Molded pulp',
        'colors' => 'Cream / Sage / Formula accent colors / Customized Color',
        'category_slugs' => array('pet-product-packaging-boxes', 'supplement-packaging-boxes', 'folding-carton-boxes'),
        'tags' => array('pet supplement box', 'pet vitamin packaging', 'veterinary wellness carton'),
        'views' => 'pet supplement carton with bottle|second wellness product angle|opened bottle-fit carton|cap clearance and dosage panel detail',
        'related' => array('pet-grooming-product-packaging-box', 'custom-pet-food-packaging-box'),
    ));

    return $products;
}

function vpn_threecat_specs(array $product): array {
    return array(
        array('label' => 'Feature', 'value' => $product['challenge']),
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
        array('label' => 'Printing Handling', 'value' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Spot UV, Matte Lamination'),
        array('label' => 'Color', 'value' => $product['colors']),
        array('label' => 'Size', 'value' => 'Customized size'),
        array('label' => 'Thickness', 'value' => 'Customized thickness'),
        array('label' => 'Single Piece Price', 'value' => 'Price based on size, material, insert, printing, finishing, and quantity'),
        array('label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes'),
        array('label' => 'Product Name', 'value' => $product['title']),
        array('label' => 'Design', 'value' => "Customer's Specific Requirement"),
    );
}

function vpn_threecat_find_attachment(string $filename): int {
    $base = pathinfo($filename, PATHINFO_FILENAME);
    $ids = get_posts(array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => array(array(
            'key' => '_wp_attached_file',
            'value' => $base,
            'compare' => 'LIKE',
        )),
    ));

    foreach ($ids as $id) {
        $attached = (string) get_post_meta((int) $id, '_wp_attached_file', true);

        if (0 === strcasecmp(pathinfo($attached, PATHINFO_FILENAME), $base)) {
            return (int) $id;
        }
    }

    return 0;
}

function vpn_threecat_import_attachment(string $filename, string $alt, string $title, string $caption): int {
    $relative = '2026/07/' . basename($filename);
    $file = WP_CONTENT_DIR . '/uploads/' . $relative;

    if (!file_exists($file) && function_exists('get_template_directory')) {
        $bundled = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $relative;

        if (file_exists($bundled)) {
            $target_dir = dirname($file);

            if (!is_dir($target_dir)) {
                wp_mkdir_p($target_dir);
            }

            copy($bundled, $file);
        }
    }

    if (!file_exists($file)) {
        return 0;
    }

    $filetype = wp_check_filetype(basename($file), null);

    if (empty($filetype['type'])) {
        return 0;
    }

    $attachment_id = wp_insert_attachment(array(
        'post_mime_type' => $filetype['type'],
        'post_title' => $title,
        'post_content' => '',
        'post_excerpt' => $caption,
        'post_status' => 'inherit',
        'guid' => content_url('uploads/' . $relative),
    ), $file);

    if (is_wp_error($attachment_id) || !$attachment_id) {
        return 0;
    }

    update_post_meta($attachment_id, '_wp_attached_file', $relative);
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata($attachment_id, $file);

    if (!is_wp_error($metadata) && !empty($metadata)) {
        wp_update_attachment_metadata($attachment_id, $metadata);
    }

    return (int) $attachment_id;
}

function vpn_threecat_attachment_id(string $filename, string $alt, string $title, string $caption): int {
    $attachment_id = vpn_threecat_find_attachment($filename);

    if (!$attachment_id) {
        $attachment_id = vpn_threecat_import_attachment($filename, $alt, $title, $caption);
    }

    if (!$attachment_id) {
        return 0;
    }

    update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
    wp_update_post(array(
        'ID' => $attachment_id,
        'post_title' => $title,
        'post_excerpt' => $caption,
    ));

    return $attachment_id;
}

function vpn_threecat_figure(int $attachment_id, string $caption, bool $narrow = false): string {
    $image = wp_get_attachment_image($attachment_id, 'large', false, array('loading' => 'lazy', 'decoding' => 'async'));

    if (!$image) {
        return '';
    }

    return '<figure class="product-inline-figure product-inline-figure-small' . ($narrow ? ' is-narrow' : '') . '">' .
        $image . '<figcaption>' . esc_html($caption) . '</figcaption></figure>';
}

function vpn_threecat_section(string $heading, array $paragraphs): string {
    $html = '<h2>' . esc_html($heading) . '</h2>';

    foreach ($paragraphs as $paragraph) {
        $html .= '<p>' . $paragraph . '</p>';
    }

    return $html;
}

function vpn_threecat_headings(array $product): array {
    $display = $product['display'];
    $family = $product['family'];

    if ('toy' === $family) {
        return array(
            $display . ' for Organized Play and Retail Presentation',
            'Component Safety and Packing Risks for ' . $display,
            'Choosing a Structure Around the Real Play Set',
            'Insert Planning for Pieces, Cards, Tools, and Accessories',
            'Artwork Hierarchy for Age, Contents, and Play Information',
            'Board Strength and Finishing for Repeated Handling',
            'Retail, Education, Gift, and Ecommerce Use Cases',
            'Product Data Needed Before Sampling ' . $display,
            'Quality Checks for Count, Fit, and Opening Experience',
            'Common Mistakes and Quote Details for ' . $display,
        );
    }

    if ('tea' === $family) {
        return array(
            $display . ' for Specialty Beverage Brands',
            'Aroma-Sensitive Primary Packs and Outer Carton Priorities',
            'Structure Decisions for Pack Count, Weight, and Shelf Format',
            'Organizing Pouches, Sachets, Capsules, Tins, or Gift Items',
            'Artwork for Origin, Brew Guidance, Batch, and SKU Versions',
            'Paper Materials and Finishes for Tea and Coffee Retail',
            'Roaster, Tea House, Hospitality, and Gift Applications',
            'RFQ Details Before Sampling ' . $display,
            'Quality Control for Beverage Packaging Production',
            'Ordering and Quote Checklist for ' . $display,
        );
    }

    return array(
        $display . ' for Pet Brands and Retail Programs',
        'Protection, Information, and Packing Risks to Resolve',
        'Selecting the Box Structure Around the Pet Product',
        'Insert and Fit Planning for Pouches, Bottles, or Accessories',
        'Artwork for Pet Type, Use, Warnings, and Traceability',
        'Paperboard and Finishing Choices for Pet Retail',
        'Pet Store, Veterinary, Subscription, and Ecommerce Uses',
        'Procurement Details Before Sampling ' . $display,
        'Quality Checks for Pet Product Packaging',
        'Mistakes to Avoid and Quote Details for ' . $display,
    );
}

function vpn_threecat_short_description(array $product): string {
    return $product['display'] . ' is developed for ' . $product['buyer'] . ' packing ' . $product['inside'] .
        '. The package addresses ' . $product['challenge'] . '. Recommended production can use ' . $product['structure'] .
        ' Size, board grade, insert layout, opening direction, logo printing, information panels, surface finishing, and export carton packing are customized around the real product and sales channel. Buyers should provide product dimensions, filled weight, component count, artwork versions, and target quantity before sampling. MOQ starts from 1000 boxes for custom production by VPN Paper Box Manufacturer in Vietnam.';
}

function vpn_threecat_content(array $product, array $image_ids): string {
    $profiles = vpn_threecat_profiles();
    $profile = $profiles[$product['family']];
    $headings = vpn_threecat_headings($product);
    $category_link = vpn_threecat_link('/products/' . $profile['category_slug'] . '/', $profile['category_anchor']);
    $material_link = vpn_threecat_link('/how-to-choose-paper-material-for-product-packaging/', 'paper material selection for product packaging');
    $artwork_link = vpn_threecat_link('/how-to-prepare-artwork-for-printed-paper-boxes/', 'print-ready artwork preparation');
    $related_one = vpn_threecat_link('/product/' . $product['related'][0] . '/', ucwords(str_replace('-', ' ', $product['related'][0])));
    $quote_link = vpn_threecat_link('/contact/#quote', 'request a custom packaging quote');
    $details = vpn_threecat_sentence_list(vpn_threecat_items($product['details']));
    $panels = vpn_threecat_sentence_list(vpn_threecat_items($product['panels']));
    $qc = vpn_threecat_sentence_list(vpn_threecat_items($product['qc']));
    $captions = vpn_threecat_items($product['views']);

    $html = vpn_threecat_section($headings[0], array(
        $product['display'] . ' is planned for ' . $product['buyer'] . ' that need reliable packaging for ' . $product['inside'] . '. The product should be evaluated as a packing system rather than as printed paper alone. Product dimensions, weight, component order, customer opening behavior, shelf direction, and master-carton loading all influence whether the final box performs consistently.',
        'The central challenge is ' . $product['challenge'] . '. This page keeps that buying problem separate from other products in the ' . $category_link . ' range. The recommended starting direction is ' . $product['structure'] . ', but the final dieline should only be approved after testing the real product or an accurate production dummy.',
        'A useful first brief includes ' . $details . '. Those details affect board thickness, flap or lid tolerance, insert depth, image hierarchy, packing labor, sample cost, and shipping efficiency. A complete brief also prevents a visually attractive sample from becoming difficult to pack when the order moves to 1000 boxes or more.',
    ));

    $html .= vpn_threecat_figure($image_ids[0], $captions[0], true);
    $html .= vpn_threecat_section($headings[1], array(
        $profile['risk_context'] . ' For this product specifically, ' . $product['challenge'] . '. The packaging team should list every pressure point, movable part, fragile surface, information requirement, and customer action before deciding the board grade or opening style.',
        'The sample should be handled in the same way as the production pack: load the product, close the box, shake it, stack it, place it in the expected retail or parcel orientation, and open it several times. This exposes movement, rubbing, crushed edges, difficult removal, weak closures, and information panels that become hidden after assembly.',
    ));

    $html .= vpn_threecat_section($headings[2], array(
        'The proposed structure is ' . $product['structure'] . '. Structure must follow the product rather than a reference photo. A few millimeters of extra height, a different pouch gusset, a heavier bottle, a wider deck, or one additional component can change the insert, bottom lock, lid depth, board caliper, and master-carton count.',
        'Sampling should compare opening direction, assembly time, board stiffness, product removal, and repacking. A structure that feels premium but slows the packing line can raise cost and create inconsistency. A lighter carton may be efficient, but it still needs enough compression resistance and panel area for the intended channel.',
    ));

    $html .= vpn_threecat_figure($image_ids[1], $captions[1]);
    $html .= vpn_threecat_section($headings[3], array(
        $product['fit'] . ' Insert and fit planning should control movement without creating pressure that damages the product, its primary pack, printed surface, cap, card edge, or accessory. Finger access and packing access are separate requirements and should both be tested during sample review.',
        'Possible support materials include ' . $product['liner'] . '. The selected system should use the fewest pieces needed to achieve repeatable fit, sensible packing speed, and the required presentation. Overcomplicated inserts increase assembly time; weak dividers can collapse before the customer opens the package.',
    ));

    $html .= vpn_threecat_section($headings[4], array(
        $product['artwork'] . ' The working dieline should organize ' . $panels . '. Front, side, back, lid, drawer, and inner panels have different jobs. Decorative artwork should never reduce barcode scanning, warning visibility, dosage or brew readability, component checking, or version control.',
        'Use the final dieline for ' . $artwork_link . ' with bleed, safe zones, folds, glue areas, cut lines, barcode quiet zones, and finish masks marked separately. When several flavors, formulas, editions, languages, or age grades share one structure, keep a version table that connects each artwork file to the correct product and carton label.',
    ));

    $html .= vpn_threecat_figure($image_ids[2], $captions[2], true);
    $html .= vpn_threecat_section($headings[5], array(
        'Recommended materials include ' . $product['materials'] . '. ' . $profile['material_context'] . ' Buyers can review ' . $material_link . ' before sampling to compare stiffness, print behavior, crease quality, edge appearance, sustainability direction, and unit-cost tradeoffs.',
        'The finishing direction is ' . $product['finish'] . '. Foil, embossing, spot UV, lamination, windows, and specialty papers need production tolerances and glue planning. Finishes should reinforce hierarchy and shelf recognition, not make small text hard to read or create surfaces that scratch during bulk packing.',
    ));

    $html .= vpn_threecat_section($headings[6], array(
        'This product supports ' . $product['channel'] . '. Each channel changes priorities. Retail needs front-facing recognition and consistent barcodes; gifting needs a controlled reveal; ecommerce needs stronger movement control; distributor programs need repeatable version and carton labeling; subscription formats need efficient mixed-item packing.',
        $profile['operations_context'] . ' Buyers comparing a nearby structure can review ' . $related_one . '. That product may share an industry, but its insert logic, information hierarchy, opening behavior, and quote inputs are different.',
    ));

    $html .= vpn_threecat_figure($image_ids[3], $captions[3]);
    $html .= vpn_threecat_section($headings[7], array(
        'Before requesting a sample, prepare ' . $product['procurement'] . '. The factory also needs target quantity, artwork status, preferred board or paper direction, finishing expectations, destination market, delivery deadline, and whether boxes ship flat or assembled.',
        'The approved sample should record product fit, board grade, insert layout, opening direction, color target, finish masks, packing sequence, carton quantity, and protective packing. Keeping one signed sample with the buyer and one with the factory gives both teams a physical standard for mass production and later reorders.',
    ));

    $html .= vpn_threecat_section($headings[8], array(
        'Quality control should check ' . $qc . '. Inspection must cover function as well as appearance. A box should not pass only because the print looks clean if the product moves, the insert lifts, the bottom opens, the lid rubs, or the wrong information version has been packed.',
        'Bulk checks should compare board thickness, crease position, glue strength, print color, finish registration, product count, insert fit, barcode or QR performance, and master-carton compression against the approved sample. Version-controlled projects also need line-clearance checks so old artwork, flavors, formulas, or editions do not mix with the new run.',
        'Pre-production review should turn the product DNA into measurable acceptance criteria, including ' . $details . '. Record the approved dimensions, product orientation, contact points, closure tension, packing sequence, and allowable movement in a signed checklist. Photograph the correctly loaded sample and its master-carton arrangement so inspectors can distinguish an intentional tolerance from a packing error. This evidence also gives purchasing, design, production, and quality teams the same reference when a repeat order changes quantity, artwork, component dimensions, or shipping route.',
    ));

    $html .= '<h2>' . esc_html($headings[9]) . '</h2><ul>';
    foreach (array(
        'Approving the dieline before the real packed dimensions and weight are confirmed.',
        'Choosing decorative finishing before the insert, information zones, and packing method are stable.',
        'Using one generic artwork file without a controlled SKU, flavor, formula, language, or edition matrix.',
        'Reviewing an empty sample instead of testing product loading, removal, movement, stacking, and shipment.',
    ) as $mistake) {
        $html .= '<li>' . esc_html($mistake) . '</li>';
    }
    $html .= '</ul>';
    $html .= vpn_threecat_section('Request a Quote for ' . $product['display'], array(
        'For an accurate quotation, send ' . $product['procurement'] . ', together with product photos, target quantity, artwork files, finishing references, destination market, and delivery timing. These inputs allow the packaging team to recommend a realistic structure, insert, sample path, and export carton plan.',
        'VPN Paper Box Manufacturer can customize size, paper material, insert, logo printing, surface finishing, opening style, and shipping protection for international B2B orders. The minimum order quantity for this product is 1000 boxes, with pricing based on structure, dimensions, material, print coverage, finishing, insert, and quantity.',
        'Send the project details through ' . $quote_link . ' and identify the exact product, category, packed dimensions, preferred structure, and required launch date. A clear RFQ reduces revision rounds and gives the sample team a measurable standard for fit, presentation, and repeat production.',
    ));

    return $html;
}

function vpn_threecat_meta_description(array $product): string {
    $description = $product['display'] . ' with custom structure, insert, printing, finishing, and MOQ 1000 boxes for B2B packaging projects.';

    return mb_substr($description, 0, 154);
}

$products = vpn_threecat_products();
$category_names = array(
    'toy-and-game-packaging-boxes' => 'Toy and Game Packaging Boxes',
    'tea-and-coffee-packaging-boxes' => 'Tea and Coffee Packaging Boxes',
    'pet-product-packaging-boxes' => 'Pet Product Packaging Boxes',
    'rigid-boxes' => 'Rigid Boxes',
    'lid-and-base-boxes' => 'Lid and Base Boxes',
    'folding-carton-boxes' => 'Folding Carton Boxes',
    'custom-printed-paper-boxes' => 'Custom Printed Paper Boxes',
    'corrugated-mailer-boxes' => 'Corrugated Mailer Boxes',
    'back-to-school-stationery-packaging' => 'Back-to-School and Stationery Packaging',
    'custom-paper-boxes' => 'Custom Paper Boxes',
    'gift-paper-boxes' => 'Gift Paper Boxes',
    'food-paper-boxes' => 'Food Paper Boxes',
    'supplement-packaging-boxes' => 'Supplement Packaging Boxes',
    'drawer-boxes' => 'Drawer Boxes',
);
$term_ids = array();

foreach ($category_names as $slug => $name) {
    $term = get_term_by('slug', $slug, 'product_cat');

    if (!$term || is_wp_error($term)) {
        $created = wp_insert_term($name, 'product_cat', array('slug' => $slug));

        if (is_wp_error($created)) {
            throw new RuntimeException('Failed category: ' . $slug . '. ' . $created->get_error_message());
        }

        $term = get_term((int) $created['term_id'], 'product_cat');
    }

    $term_ids[$slug] = (int) $term->term_id;
}

$audit = array('# Three New Product Categories Import Audit', '');
$text_export = array('# Three New Product Categories Descriptions Text Only', '');

foreach ($products as $product) {
    $image_ids = array();
    $views = vpn_threecat_items($product['views']);

    foreach ($product['images'] as $index => $filename) {
        $alt = $product['keyword'] . ' for ' . strtolower($product['industrial']) . ', ' . $views[$index];
        $attachment_id = vpn_threecat_attachment_id(
            $filename,
            $alt,
            $product['display'] . ' - ' . $views[$index],
            $views[$index]
        );

        if (!$attachment_id) {
            throw new RuntimeException('Missing image: ' . $filename);
        }

        $image_ids[] = $attachment_id;
    }

    $short = vpn_threecat_short_description($product);
    $content = vpn_threecat_content($product, $image_ids);
    $existing = get_page_by_path($product['slug'], OBJECT, 'product');
    $post_data = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'post_title' => $product['title'],
        'post_name' => $product['slug'],
        'post_excerpt' => $short,
        'post_content' => $content,
    );

    if ($existing) {
        $post_data['ID'] = (int) $existing->ID;
        $product_id = wp_update_post($post_data, true);
    } else {
        $product_id = wp_insert_post($post_data, true);
    }

    if (is_wp_error($product_id) || !$product_id) {
        $message = is_wp_error($product_id) ? $product_id->get_error_message() : 'Unknown WordPress insert error.';
        throw new RuntimeException('Failed product: ' . $product['slug'] . '. ' . $message);
    }

    foreach ($image_ids as $image_id) {
        wp_update_post(array('ID' => $image_id, 'post_parent' => (int) $product_id));
    }

    $product_terms = array();

    foreach ($product['category_slugs'] as $slug) {
        $product_terms[] = $term_ids[$slug];
    }

    $profiles = vpn_threecat_profiles();
    $profile = $profiles[$product['family']];
    wp_set_object_terms($product_id, $product_terms, 'product_cat', false);
    wp_set_object_terms($product_id, 'simple', 'product_type', false);
    wp_set_object_terms(
        $product_id,
        array_values(array_unique(array_merge($profile['general_tags'], $product['tags'], array($product['keyword'])))),
        'product_tag',
        false
    );

    set_post_thumbnail($product_id, $image_ids[0]);
    update_post_meta($product_id, '_product_image_gallery', implode(',', array_slice($image_ids, 1)));
    update_post_meta($product_id, '_sku', 'sample-threecat-202607-' . $product['slug']);
    update_post_meta($product_id, '_regular_price', '');
    update_post_meta($product_id, '_price', '');
    update_post_meta($product_id, '_stock_status', 'instock');
    update_post_meta($product_id, '_manage_stock', 'no');
    update_post_meta($product_id, '_visibility', 'visible');
    update_post_meta($product_id, '_custom_box_product_specs', vpn_threecat_specs($product));
    update_post_meta($product_id, '_vpn_sample_import', VPN_THREECAT_MARKER);
    update_post_meta($product_id, 'rank_math_focus_keyword', $product['keyword']);
    update_post_meta($product_id, 'rank_math_title', $product['title'] . ' | VPN PAPER BOX MANUFACTURER');
    update_post_meta($product_id, 'rank_math_description', vpn_threecat_meta_description($product));

    $saved = get_post_field('post_content', $product_id);
    $words = str_word_count(wp_strip_all_tags($saved));
    $short_words = str_word_count(wp_strip_all_tags($short));
    $figures = substr_count($saved, '<figure class="product-inline-figure');
    $specs = get_post_meta($product_id, '_custom_box_product_specs', true);

    $audit[] = '## ' . $product['title'];
    $audit[] = '- ID: ' . $product_id;
    $audit[] = '- URL: ' . get_permalink($product_id);
    $audit[] = '- Primary category: ' . $profile['category_slug'];
    $audit[] = '- Categories: ' . implode(', ', $product['category_slugs']);
    $audit[] = '- Status: ' . get_post_status($product_id);
    $audit[] = '- Focus keyword: ' . $product['keyword'];
    $audit[] = '- Long description words: ' . $words;
    $audit[] = '- Short description words: ' . $short_words;
    $audit[] = '- Content H1 count: ' . preg_match_all('/<h1\b/i', $saved);
    $audit[] = '- Specs rows: ' . (is_array($specs) ? count($specs) : 0);
    $audit[] = '- Gallery images: ' . count(array_slice($image_ids, 1));
    $audit[] = '- Inline figures: ' . $figures;
    $audit[] = '- Source images: ' . implode(', ', $product['images']);
    $audit[] = '- Duplicate risk score: ' . $product['duplicate_risk'];
    $audit[] = '';

    $text_export[] = '## ' . $product['title'];
    $text_export[] = wp_strip_all_tags($short . "\n\n" . $saved);
    $text_export[] = '';

    echo 'Imported: ' . $product['title'] . ' (#' . $product_id . ') words=' . $words . ' short=' . $short_words . ' images=4 figures=' . $figures . PHP_EOL;
}

file_put_contents(
    dirname(__DIR__) . '/product-samples-three-new-categories-202607-audit.md',
    implode(PHP_EOL, $audit)
);
file_put_contents(
    dirname(__DIR__) . '/product-samples-three-new-categories-202607-descriptions-text-only.md',
    implode(PHP_EOL, $text_export)
);

echo 'Three-category product import complete.' . PHP_EOL;
