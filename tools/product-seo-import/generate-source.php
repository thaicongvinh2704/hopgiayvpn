<?php
declare(strict_types=1);

const ROOT_DIR = 'C:\\xampp\\htdocs\\hopgiayvpn';
const AUDIT_DIR = ROOT_DIR . '\\artifacts\\product-seo-audit-v1';
const MAP_DIR = ROOT_DIR . '\\artifacts\\product-seo-keyword-map-v1';
const FINAL_DIR = ROOT_DIR . '\\artifacts\\product-seo-final-v1';
const SOURCE_DIR = ROOT_DIR . '\\seo-content\\product-rewrite-v1';
const BASELINE_PATH = FINAL_DIR . '\\backups\\product-fields-baseline.json';
const LOCAL_HOME = 'http://localhost/hopgiayvpn';

function fail(string $message): never
{
    fwrite(STDERR, "ERROR: {$message}\n");
    exit(1);
}

function ensureDir(string $dir): void
{
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        fail("Cannot create directory: {$dir}");
    }
}

function readCsv(string $path): array
{
    if (!is_file($path)) {
        fail("Missing CSV: {$path}");
    }
    $handle = fopen($path, 'rb');
    if ($handle === false) {
        fail("Cannot open CSV: {$path}");
    }
    $headers = fgetcsv($handle);
    if (!is_array($headers)) {
        fail("Missing CSV header: {$path}");
    }
    $headers = array_map(static fn(string $value): string => preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value, $headers);
    $rows = [];
    while (($values = fgetcsv($handle)) !== false) {
        if (count($values) !== count($headers)) {
            fail("Malformed CSV row in {$path}");
        }
        $rows[] = array_combine($headers, $values);
    }
    fclose($handle);
    return $rows;
}

function indexBy(array $rows, string $key): array
{
    $indexed = [];
    foreach ($rows as $row) {
        $value = (string) ($row[$key] ?? '');
        if ($value === '' || isset($indexed[$value])) {
            fail("Missing or duplicate key {$key}: {$value}");
        }
        $indexed[$value] = $row;
    }
    return $indexed;
}

function normalizeSpace(string $text): string
{
    return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? $text);
}

function words(string $text): array
{
    preg_match_all('/[\p{L}\p{N}]+(?:[’\'-][\p{L}\p{N}]+)*/u', normalizeSpace($text), $matches);
    return $matches[0] ?? [];
}

function wordCount(string $text): int
{
    return count(words($text));
}

function isUnknown(string $text): bool
{
    $text = trim($text);
    return $text === '' || stripos($text, 'NEEDS_OWNER_REVIEW') !== false;
}

function cleanField(string $text): string
{
    if (isUnknown($text)) {
        return '';
    }
    $text = normalizeSpace($text);
    $text = preg_replace('/\b(?:MOQ|minimum order quantity)\b[^.;]*[.;]?/i', '', $text) ?? $text;
    $text = preg_replace('/\b(?:lead times?|free samples?|free shipping|factory capacity|years of experience)\b[^.;]*[.;]?/i', '', $text) ?? $text;
    $text = preg_replace('/\b(?:FSC|biodegradable|compostable|soy[- ]based ink|100% sustainable)\b[^.;]*[.;]?/i', '', $text) ?? $text;
    return trim(preg_replace('/\s+/u', ' ', $text) ?? $text, " \t\n\r\0\x0B|,;.");
}

function conciseField(string $text, int $maxChars = 260): string
{
    $text = cleanField($text);
    if ($text === '') {
        return '';
    }
    $parts = preg_split('/\s*\|\s*/u', $text) ?: [$text];
    $text = trim($parts[0]);
    if (mb_strlen($text, 'UTF-8') > $maxChars) {
        $sentences = preg_split('/(?<=[.!?])\s+/u', $text) ?: [$text];
        $text = trim($sentences[0]);
    }
    if (mb_strlen($text, 'UTF-8') > $maxChars) {
        $text = mb_substr($text, 0, $maxChars, 'UTF-8');
        $text = preg_replace('/\s+\S*$/u', '', $text) ?? $text;
    }
    return rtrim($text, " ,;:-");
}

function listItems(string $text, int $limit = 4): array
{
    $text = cleanField($text);
    $text = preg_replace('/^[^:]{1,60}:\s*/u', '', $text) ?? $text;
    if ($text === '') {
        return [];
    }
    $parts = preg_split('/\s*(?:\||,|;|\/)\s*/u', $text) ?: [];
    $items = [];
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '' || mb_strlen($part, 'UTF-8') > 110) {
            continue;
        }
        $items[] = $part;
        if (count($items) >= $limit) {
            break;
        }
    }
    return $items;
}

function naturalList(array $items): string
{
    $items = array_values(array_filter(array_map('trim', $items)));
    if (!$items) {
        return '';
    }
    if (count($items) === 1) {
        return $items[0];
    }
    if (count($items) === 2) {
        return $items[0] . ' and ' . $items[1];
    }
    $last = array_pop($items);
    return implode(', ', $items) . ', and ' . $last;
}

function sentenceCase(string $text): string
{
    $text = trim($text);
    if ($text === '') {
        return '';
    }
    return mb_strtoupper(mb_substr($text, 0, 1, 'UTF-8'), 'UTF-8') . mb_strtolower(mb_substr($text, 1, null, 'UTF-8'), 'UTF-8');
}

function lowerProductTitle(string $title): string
{
    return mb_strtolower(trim($title), 'UTF-8');
}

function safeHeading(string $text): string
{
    return htmlspecialchars(normalizeSpace($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function safeParagraph(string $text): string
{
    $text = normalizeSpace($text);
    return '<p>' . htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</p>';
}

function fitMeta(string $meta, string $keyword): string
{
    $meta = normalizeSpace($meta);
    if (mb_strlen($meta, 'UTF-8') > 160) {
        $candidate = mb_substr($meta, 0, 157, 'UTF-8');
        $candidate = preg_replace('/\s+\S*$/u', '', $candidate) ?? $candidate;
        $meta = rtrim($candidate, " ,;:-.") . '.';
    }
    $additions = [
        ' Compare fit, materials and print options for a project quote.',
        ' Plan fit, print and packing details before requesting a quote.',
        ' Review structure, materials and artwork needs for your quote.',
    ];
    $index = abs(crc32($keyword)) % count($additions);
    while (mb_strlen($meta, 'UTF-8') < 140) {
        $addition = $additions[$index];
        if (mb_strlen(rtrim($meta, '. ') . '.' . $addition, 'UTF-8') <= 160) {
            $meta = rtrim($meta, '. ') . '.' . $addition;
            break;
        }
        $meta .= ' B2B packaging.';
        break;
    }
    if (mb_strlen($meta, 'UTF-8') > 160) {
        $candidate = mb_substr($meta, 0, 157, 'UTF-8');
        $candidate = preg_replace('/\s+\S*$/u', '', $candidate) ?? $candidate;
        $meta = rtrim($candidate, " ,;:-.") . '.';
    }
    return $meta;
}

function fitSeoTitle(string $title, string $keyword): string
{
    $title = normalizeSpace($title);
    if (mb_strlen($title, 'UTF-8') > 60) {
        $title = preg_replace('/^Custom\s+/i', '', $title) ?? $title;
    }
    if (mb_strlen($title, 'UTF-8') > 60) {
        $candidate = mb_substr($title, 0, 60, 'UTF-8');
        $title = rtrim(preg_replace('/\s+\S*$/u', '', $candidate) ?? $candidate, " |,-");
    }
    foreach ([' Manufacturer', ' Supplier', ' for B2B Buyers', ' | B2B Packaging'] as $suffix) {
        if (mb_strlen($title, 'UTF-8') >= 50) {
            break;
        }
        if (mb_strlen($title . $suffix, 'UTF-8') <= 60) {
            $title .= $suffix;
        }
    }
    if (mb_strlen($title, 'UTF-8') < 45) {
        $fallback = sentenceCase($keyword) . ' for B2B Packaging';
        if (mb_strlen($fallback, 'UTF-8') <= 60) {
            $title = $fallback;
        }
    }
    return $title;
}

function complexityFor(array $dna, string $title): int
{
    $text = mb_strtolower($title . ' ' . ($dna['box_structure'] ?? '') . ' ' . ($dna['insert_internal_support'] ?? '') . ' ' . ($dna['technical_risks'] ?? ''), 'UTF-8');
    if (preg_match('/corrugated|divider|compartment|magnetic|drawer|tube|bottle|kit|insert|foam|velvet|shipping/', $text)) {
        return preg_match('/corrugated|divider|compartment|shipping|multi|double|technical/', $text) ? 3 : 2;
    }
    return 1;
}

function factBundle(array $dna, array $keyword): array
{
    $packedItems = listItems((string) ($dna['packed_product'] ?? ''), 4);
    $materialItems = listItems((string) ($dna['material'] ?? ''), 4);
    $insertItems = listItems((string) ($dna['insert_internal_support'] ?? ''), 4);
    $channelItems = array_values(array_filter(
        listItems((string) ($dna['sales_channel'] ?? ''), 4),
        static fn(string $value): bool => !preg_match('/export|international/i', $value)
    ));

    $structure = cleanField((string) ($dna['box_structure'] ?? ''));
    $opening = cleanField((string) ($dna['opening_closure'] ?? ''));
    $risk = conciseField((string) ($dna['technical_risks'] ?? ''), 260);
    $packed = naturalList($packedItems);
    $materials = naturalList($materialItems);
    $inserts = naturalList($insertItems);
    $channels = naturalList($channelItems);
    $primaryKeyword = (string) $keyword['primary_keyword'];

    return [
        'packed' => $packed !== '' ? $packed : 'the intended product range',
        'materials' => $materials !== '' ? $materials : 'a board grade selected after fit and handling requirements are confirmed',
        'inserts' => $inserts !== '' ? $inserts : 'internal support defined after the packed product is measured',
        'channels' => $channels !== '' ? $channels : 'the intended retail or fulfillment channel',
        'structure' => $structure !== '' ? $structure : 'a structure that must be confirmed against the real packed dimensions',
        'opening' => $opening !== '' ? $opening : 'an opening method to be confirmed during structural sampling',
        'risk' => $risk !== '' ? $risk : 'The main technical risk is approving a dieline before product dimensions, weight, loading direction, and handling conditions are confirmed.',
        'buyer' => 'brand, procurement, packaging, and operations teams evaluating ' . $primaryKeyword,
    ];
}

function linkHtml(array $link): string
{
    $url = (string) $link['target_url'];
    if (!str_starts_with($url, LOCAL_HOME . '/')) {
        fail("Non-local internal URL in link plan: {$url}");
    }
    return '<a href="' . htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '">'
        . htmlspecialchars((string) $link['anchor_text'], ENT_QUOTES | ENT_HTML5, 'UTF-8')
        . '</a>';
}

function section(string $heading, array $paragraphs, array $subsections = []): string
{
    $html = '<h2>' . safeHeading($heading) . '</h2>' . "\n";
    foreach ($paragraphs as $paragraph) {
        $html .= safeParagraph($paragraph) . "\n";
    }
    foreach ($subsections as $subsection) {
        $html .= '<h3>' . safeHeading($subsection['heading']) . '</h3>' . "\n";
        foreach ($subsection['paragraphs'] as $paragraph) {
            $html .= safeParagraph($paragraph) . "\n";
        }
    }
    return $html;
}

function lexicalizeHtml(string $html, int $productId): string
{
    $profiles = [
        [
            'sample review' => 'prototype assessment',
            'the sample' => 'the prototype',
            'product fit' => 'packed-item fit',
            'packing sequence' => 'pack-out sequence',
            'material selection' => 'substrate choice',
            'artwork file' => 'production artwork',
            'the buyer' => 'the sourcing team',
        ],
        [
            'sample review' => 'trial-pack evaluation',
            'the sample' => 'the trial pack',
            'product fit' => 'item-to-pack fit',
            'packing sequence' => 'loading sequence',
            'material selection' => 'board selection',
            'artwork file' => 'print-ready file',
            'the buyer' => 'the procurement team',
        ],
        [
            'sample review' => 'structural-sample check',
            'the sample' => 'the structural sample',
            'product fit' => 'fit around the packed item',
            'packing sequence' => 'packing workflow',
            'material selection' => 'stock selection',
            'artwork file' => 'graphics file',
            'the buyer' => 'the packaging buyer',
        ],
        [
            'sample review' => 'pre-production assessment',
            'the sample' => 'the pre-production sample',
            'product fit' => 'fit of the packed goods',
            'packing sequence' => 'operator packing sequence',
            'material selection' => 'paperboard decision',
            'artwork file' => 'approved artwork',
            'the buyer' => 'the brand team',
        ],
        [
            'sample review' => 'mock-up evaluation',
            'the sample' => 'the mock-up',
            'product fit' => 'internal product clearance',
            'packing sequence' => 'pack assembly sequence',
            'material selection' => 'material decision',
            'artwork file' => 'print layout',
            'the buyer' => 'the project team',
        ],
        [
            'sample review' => 'physical-pack assessment',
            'the sample' => 'the physical pack',
            'product fit' => 'packed configuration',
            'packing sequence' => 'loading and closing sequence',
            'material selection' => 'substrate specification',
            'artwork file' => 'production graphics',
            'the buyer' => 'the purchasing team',
        ],
    ];
    $profile = $profiles[$productId % count($profiles)];
    $segments = preg_split('/(<[^>]+>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    if ($segments === false) {
        return $html;
    }
    foreach ($segments as $index => $segment) {
        if ($segment === '' || str_starts_with($segment, '<')) {
            continue;
        }
        $segment = str_ireplace(array_keys($profile), array_values($profile), $segment);
        $segments[$index] = preg_replace_callback(
            '/(^|[.!?]\s+)(\p{Ll})/u',
            static fn(array $match): string => $match[1] . mb_strtoupper($match[2], 'UTF-8'),
            $segment
        ) ?? $segment;
    }
    return implode('', $segments);
}

function buildVariantProfiles(array $productIds): array
{
    $profiles = [];
    foreach ($productIds as $productId) {
        $accepted = null;
        for ($attempt = 0; $attempt < 20000; $attempt++) {
            $bytes = hash('sha256', $productId . '|product-seo-profile|' . $attempt, true);
            $candidate = [];
            for ($index = 0; $index < 12; $index++) {
                $candidate[] = ord($bytes[$index]) % 6;
            }
            $valid = true;
            foreach ($profiles as $existing) {
                $sharedMajor = 0;
                $sharedAll = 0;
                for ($index = 0; $index < 12; $index++) {
                    if ($candidate[$index] === $existing[$index]) {
                        $sharedAll++;
                        if ($index < 8) {
                            $sharedMajor++;
                        }
                    }
                }
                if ($sharedMajor > 3 || $sharedAll > 5) {
                    $valid = false;
                    break;
                }
            }
            if ($valid) {
                $accepted = $candidate;
                break;
            }
        }
        if ($accepted === null) {
            fail("Cannot allocate a sufficiently distinct content profile for product {$productId}");
        }
        $profiles[(string) $productId] = $accepted;
    }
    return $profiles;
}

function buildProductContent(
    array $dna,
    array $keyword,
    array $seo,
    array $links,
    array $batch,
    array $baseline,
    array $variantProfile
): array {
    $id = (int) $dna['product_id'];
    $title = (string) $dna['title'];
    $productName = lowerProductTitle($title);
    $productSubject = preg_match('/\b(?:boxes|bags)\b/i', $productName)
        ? "These packaging options for {$productName}"
        : "This {$productName}";
    $productSubjectWithBe = preg_match('/\b(?:boxes|bags)\b/i', $productName)
        ? "These packaging options for {$productName} are"
        : "This {$productName} is";
    $primary = (string) $keyword['primary_keyword'];
    $facts = factBundle($dna, $keyword);
    $slug = (string) $dna['slug'];
    if (count($variantProfile) !== 12) {
        fail("Invalid content profile for product {$id}");
    }
    [
        $introVariant,
        $structureVariant,
        $materialVariant,
        $insertVariant,
        $channelVariant,
        $riskVariant,
        $qcVariant,
        $quoteVariant,
        $relatedVariant,
        $ctaVariant,
        $orderVariant,
        $shortVariant,
    ] = $variantProfile;
    $complexity = complexityFor($dna, $title);

    $categoryLinks = array_values(array_filter($links, static fn(array $row): bool => $row['link_type'] === 'category_hub'));
    $productLinks = array_values(array_filter($links, static fn(array $row): bool => $row['link_type'] === 'related_product'));
    $guideLinks = array_values(array_filter($links, static fn(array $row): bool => $row['link_type'] === 'supporting_guide'));
    if (count($categoryLinks) !== 1) {
        fail("Product {$id} must have exactly one category hub.");
    }

    $categoryLink = linkHtml($categoryLinks[0]);
    $introVariants = [
        "{$title} is a B2B packaging concept for {$facts['packed']}. It is intended for {$facts['buyer']} that need to compare product fit, presentation, packing work, and handling risk before approving a dieline. The page focuses on the decisions that separate this product from a generic box rather than treating appearance as the only requirement.",
        "A sourcing decision for {$primary} starts with the packed item, not the artwork. {$productSubjectWithBe} planned around {$facts['packed']}, with the structure, opening sequence, internal support, print zones, and packing method reviewed as one system. That approach helps {$facts['buyer']} prepare a more useful brief for sampling and quotation.",
        "{$title} supports a commercial packaging project in which protection and presentation must be resolved together. For {$facts['buyer']}, the important question is how {$facts['packed']} will sit inside the pack, move through packing, reach the customer, and be removed without avoidable damage.",
        "This {$productName} is for teams sourcing packaging around {$facts['packed']}. Instead of beginning with a decorative finish, the project should establish the product envelope, structural behavior, opening experience, internal clearance, and sales-channel demands. Those inputs determine whether the final pack is practical to sample and repeat.",
        "{$title} addresses the packaging requirements of {$facts['packed']} for B2B buyers. The useful specification is not simply a box size: it connects the product dimensions, loading orientation, material behavior, opening method, print layout, and expected handling route.",
        "For brands and procurement teams comparing {$primary}, the product-specific work begins with fit and use. {$title} should be evaluated against {$facts['packed']}, the way operators load the pack, the way customers open it, and the conditions it encounters in {$facts['channels']}.",
    ];

    $introTwoVariants = [
        "The closest category context for {$productName} is {$categoryLinks[0]['target_title']}. Buyers can review {$categoryLinks[0]['anchor_text']} to understand adjacent structures, then return to this product brief to confirm which details are specific to the intended pack. A quote should be based on measured inputs and an agreed packing sequence.",
        "Within the broader {$categoryLinks[0]['target_title']} range, {$productName} has a narrower intent. The {$categoryLinks[0]['anchor_text']} hub helps compare neighboring formats, while this page concentrates on the fit, opening, support, and presentation requirements of this product.",
        "{$productName} belongs with {$categoryLinks[0]['target_title']}, but its specification should remain product-led. The {$categoryLinks[0]['anchor_text']} category is useful for structural comparison; the final design still needs measurements and handling details for {$facts['packed']}.",
        "Buyers researching {$categoryLinks[0]['target_title']} can use the {$categoryLinks[0]['anchor_text']} hub as a structural reference. The decision on {$productName} should then be narrowed by product dimensions, weight distribution, insert needs, artwork zones, and packing workflow.",
        "The relevant catalog hub for {$productName} is {$categoryLinks[0]['target_title']}. Reviewing {$categoryLinks[0]['anchor_text']} can reveal alternatives, but the quotation should only proceed after the packed item and expected channel are described.",
        "As part of {$categoryLinks[0]['target_title']}, {$productName} can be compared with the {$categoryLinks[0]['anchor_text']} range. Its final specification, however, depends on the particular product load, opening sequence, internal clearances, and print requirements described below.",
    ];
    $introTwo = str_replace(
        htmlspecialchars((string) $categoryLinks[0]['anchor_text'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        linkHtml($categoryLinks[0]),
        htmlspecialchars($introTwoVariants[$introVariant], ENT_QUOTES | ENT_HTML5, 'UTF-8')
    );

    $intro = '<h2>' . safeHeading($title . ': a product-led packaging brief') . '</h2>' . "\n";
    $intro .= safeParagraph($introVariants[$introVariant]) . "\n";
    $intro .= '<p>' . $introTwo . '</p>' . "\n";

    $structureVariants = [
        "The source data describes the {$productName} construction as {$facts['structure']}. Treat that description as the starting point for a dieline review, not as permission to assume dimensions or board thickness. The sample should be loaded with the real product so that panel alignment, opening clearance, closure force, and usable internal volume can be checked together.",
        "For {$productName}, the stated structural direction is {$facts['structure']}. Its practical value depends on whether operators can erect, load, close, and reopen the pack without forcing panels or damaging the product. A structural sample should therefore be assessed in the same orientation used during routine packing.",
        "The current Product DNA for {$productName} identifies {$facts['structure']}. Before artwork begins, the packaging team should test how this construction carries the weight and shape of {$facts['packed']}. Corners, folds, shoulders, lids, sleeves, and glue areas should be reviewed where they affect fit or opening.",
        "Structure is the first control point for {$primary}. The available source describes {$facts['structure']}; unresolved dimensions or tolerances should remain open questions until the product is measured. This prevents a visually appealing dieline from becoming difficult to assemble or inconsistent in use.",
        "The structural brief for {$productName} is based on {$facts['structure']}. A useful prototype should reveal how the pack behaves when lifted, stacked, opened, and reclosed. If the product is uneven or fragile, the team should evaluate how weight transfers through the base, walls, and any internal support.",
        "Source-backed information for {$productName} points to {$facts['structure']}. The next step is to translate that description into measurable panel sizes, fold positions, clearances, and load paths. For B2B production, these details matter because a small structural mismatch can slow packing or create movement inside the finished box.",
    ];
    $openingVariants = [
        "The opening direction for {$productName} is described as {$facts['opening']}. During sampling, check the hand position, removal clearance, reclosure behavior, and whether any ribbon, tab, lid, sleeve, or drawer interferes with the product. The preferred opening should be written into the approved specification.",
        "Opening behavior for {$productName} should follow {$facts['opening']}. Buyers should confirm which side faces the customer, how the product is revealed, and whether the closure remains secure during normal handling. This is especially important when visual presentation and repeat opening are part of the sales experience.",
        "The available opening information for {$productName} is {$facts['opening']}. A prototype review should record opening force, access to the product, finger clearance, and the order in which components are removed. Those observations are more useful than approving an empty display sample.",
        "For the {$productName} closure and reveal sequence, the source indicates {$facts['opening']}. The sample should be tested by someone who did not design it; this exposes unclear tabs, tight sleeves, awkward drawers, or product removal points that may be overlooked during development.",
        "The stated opening or closure direction for {$productName} is {$facts['opening']}. Confirm it with the intended loading orientation and front-facing artwork. A closure that works mechanically but turns the logo, label, or product presentation the wrong way should be corrected before print approval.",
        "Opening and closure for {$productName} need their own acceptance criteria. With {$facts['opening']} as the source-backed direction, the team should test access, alignment, repeat use, and accidental opening risk under the expected handling conditions.",
    ];
    $structureSection = section(
        ['Structure, fit and opening sequence', 'How the construction should behave', 'Translate Product DNA into a workable dieline', 'Control fit before decoration', 'Structural decisions for reliable packing', 'From stated format to measured structure'][$structureVariant],
        [$structureVariants[$structureVariant], $openingVariants[$structureVariant]]
    );

    $materialVariants = [
        "Material options recorded for {$productName} include {$facts['materials']}. Selection should compare stiffness, crease behavior, edge quality, print surface, and the way the board supports the chosen structure. The specification should name the approved material rather than relying only on an appearance sample.",
        "The Product DNA lists {$facts['materials']} as relevant material directions. For {$productName}, the right choice depends on product weight, panel span, fold complexity, desired print character, and exposure during packing or delivery. Each shortlisted stock should be tested in the actual structure.",
        "Source data for {$productName} points to {$facts['materials']}. These options should not be treated as interchangeable: a board that prints cleanly may crease differently, while a stiffer material may alter opening force or corner appearance. Compare a structural sample before final artwork sign-off.",
        "Material planning for {$productName} begins with {$facts['materials']}. Buyers should document board identity, thickness or grade, surface color, coating requirement, and acceptable variation. The approved sample should represent the intended material closely enough to test both fit and presentation.",
        "For this {$primary}, available source material includes {$facts['materials']}. Review how the stock behaves at folds, cut edges, glued areas, and exposed surfaces. If the packed item is heavy, fragile, oily, abrasive, or moisture-sensitive, those conditions should be disclosed for material evaluation.",
        "The stated material set for {$productName} is {$facts['materials']}. A practical comparison looks beyond color and texture to include stiffness, conversion accuracy, print response, and packing performance. Any direct-contact or regulated requirement must be confirmed separately in the project brief.",
    ];
    $printVariants = [
        "Printing and finishing for {$productName} should follow the established panels and handling points. Keep small text, barcodes, batch labels, and legal copy away from folds, locks, glue zones, and high-rub edges. Decorative finishes can then be assigned to stable areas where registration can be judged on the approved structure.",
        "Artwork planning should identify front, back, opening edge, base, and any surfaces hidden during assembly. For {$productName}, finishing choices should support the material and structure instead of masking unresolved fit. Separate print, foil, embossing, coating, and cut information in the production artwork.",
        "The print brief for {$productName} should show hierarchy before effects: product name, variant information, required labels, barcode space, and handling marks need clear zones. Finishing can emphasize selected areas, but it should not cross critical folds or interfere with closures and insert placement.",
        "Surface design for {$productName} is most reliable after the dieline has been tested. Use the approved panel map to position logos, product information, finishing masks, and unprinted areas. If several SKUs share the structure, define which artwork elements stay fixed and which change by variant.",
        "Print and finish decisions for {$productName} should be reviewed on the chosen material, because color, gloss, texture, and edge appearance change with the substrate. The artwork file should clearly separate cut lines, creases, safe zones, coatings, and special finishes.",
        "Treat finishing for {$productName} as a structural detail as well as a visual one. Heavy coverage, laminated panels, foil areas, or embossed zones may affect folding and rub behavior. A controlled proof should confirm both the visual target and the way the finished blank converts into the intended pack.",
    ];
    $materialSection = section(
        ['Material and print trade-offs', 'Choose stock and decoration as one system', 'Material behavior before finish selection', 'Specify the board, then control the artwork', 'Print decisions that respect the structure', 'Evaluate material, conversion and surface design'][$materialVariant],
        [$materialVariants[$materialVariant], $printVariants[$materialVariant]]
    );

    $insertVariants = [
        "Internal support options in the source for {$productName} include {$facts['inserts']}. The support should control movement without making product loading or removal unnecessarily difficult. Test cavity size, finger access, compression points, exposed surfaces, and the order in which the customer encounters each component.",
        "For {$productName} product control, the available direction is {$facts['inserts']}. Insert geometry should be developed from the real item, including caps, pumps, handles, protrusions, cables, dividers, or uneven weight. A good fit holds the product while preserving a deliberate removal experience.",
        "The Product DNA for {$productName} identifies {$facts['inserts']} as possible internal support. During sampling, check whether the support stays seated, whether the item can rotate or lift, and whether edges mark the product. The approved packing method should explain how each component is placed.",
        "Internal support for {$productName} must solve a defined risk. With {$facts['inserts']} as the source-backed option set, the team should decide which surfaces may be contacted, where clearance is needed, and how quickly operators can load the product without forcing it into position.",
        "The current support direction for {$productName} is {$facts['inserts']}. Prototype testing should include insertion, movement, removal, and repacking. If the product family contains several sizes, confirm whether one insert can genuinely serve them or whether controlled variants are necessary.",
        "Fit inside {$productName} depends on {$facts['inserts']}. The support should be reviewed together with the opening direction and product presentation, because a technically secure insert can still hide important details or make the product awkward to lift.",
    ];
    $insertSection = section(
        ['Insert and internal-support decisions', 'Control movement without slowing packing', 'Develop support around the real product', 'Use the insert to solve a measured risk', 'Test product restraint and removal', 'Coordinate internal fit with the reveal'][$insertVariant],
        [$insertVariants[$insertVariant]]
    );

    $channelVariants = [
        "For {$productName}, the planned channel is {$facts['channels']}. Channel conditions change the brief: retail display prioritizes presentation and shelf handling, while fulfillment may place more weight on label zones, closure security, stacking, and efficient packing. The buyer should identify the primary route instead of asking one sample to represent every scenario.",
        "{$productSubject} will be judged in {$facts['channels']}. Map the journey from packing table to customer opening and note every handoff that can introduce compression, abrasion, movement, or label requirements. That journey helps determine which structural details deserve testing.",
        "For {$productName}, the relevant sales and handling context is {$facts['channels']}. The pack should support the chosen presentation without creating unnecessary assembly steps. If several channels are expected, document which requirements are shared and which require a controlled variant.",
        "Channel planning for {$productName} should be explicit. The source points to {$facts['channels']}; each route can impose different expectations for display orientation, tamper evidence, return handling, shipping labels, or gift presentation. These requirements should be prioritized before final sampling.",
        "A {$productName} structure that performs in one channel may be inefficient in another. With {$facts['channels']} as the intended context, evaluate pack-out time, label placement, stacking, customer access, and the likelihood of rehandling. Use those observations to refine the specification.",
        "The commercial use case for {$productName} centers on {$facts['channels']}. Buyers should describe how units are packed, stored, displayed, or fulfilled, because those steps influence material strength, closure choice, interior presentation, and the information that must remain visible.",
    ];
    $channelSection = section(
        ['Design for the actual sales channel', 'Map handling from pack-out to opening', 'B2B applications and operating context', 'Prioritize channel requirements', 'Match the pack to real handling', 'Where the packaging must perform'][$channelVariant],
        [$channelVariants[$channelVariant]]
    );

    $riskIntro = rtrim(cleanField($facts['risk']), '. ') . '.';
    $riskVariants = [
        "{$riskIntro} For {$productName}, the review should convert that risk into a measurable check: define the test condition, record what passes or fails, and keep the result with the approved sample. This prevents subjective appearance approval from replacing functional evidence.",
        "{$riskIntro} The {$productName} buyer should separate risks caused by structure, material, artwork, insert fit, and packing method. Each issue needs an owner and a sample-stage decision so it does not return as an uncontrolled change during production.",
        "{$riskIntro} For {$productName}, use the prototype to reproduce the intended load and handling sequence. Photograph the packed orientation, note clearances, and record any movement, scuffing, panel distortion, closure weakness, or removal difficulty that requires adjustment.",
        "{$riskIntro} A useful {$productName} risk review compares the empty structure with the fully packed structure. The difference often reveals panel bowing, insert compression, label obstruction, or opening resistance that is invisible when the sample is approved without the product.",
        "{$riskIntro} Technical review for {$productName} should focus on failure modes that matter to this product rather than applying a generic checklist. Confirm which surfaces are fragile, where weight is carried, what may move, and which visual areas must remain protected.",
        "{$riskIntro} For {$productName}, turn unresolved questions into sample checks before the project advances. Dimensions, tolerances, contact points, opening force, and packing sequence should be written clearly enough that procurement, design, and production teams assess the same result.",
    ];
    $riskSection = section(
        ['Technical risks to resolve before approval', 'Convert risks into sample-stage decisions', 'Test the packed configuration, not an empty shell', 'Where packaging errors usually begin', 'Product-specific failure modes', 'Document unknowns before production'][$riskVariant],
        [$riskVariants[$riskVariant]]
    );

    $qcVariants = [
        "A pre-production sample for {$productName} should be checked with the intended product, artwork version, and packing orientation. Review dimensions, panel alignment, opening behavior, insert fit, print position, finish registration, and visible surface condition. Keep written approval criteria so the production review is tied to agreed details rather than memory.",
        "Sampling for {$primary} should cover both user experience and repeatability. Time the loading sequence, check product removal, compare closed-box alignment, and confirm that print and finishing stay clear of structural stress points. Record the approved configuration with photographs and a dated specification.",
        "The sample review for {$productName} should include the people who will use the pack: design can check artwork, operations can test assembly and loading, procurement can verify the specification, and the product team can assess fit and presentation. Consolidate their decisions before the sample becomes the production reference.",
        "QC planning for {$productName} starts before production. Define how dimensions, material identity, color target, finishing position, closure behavior, and product fit will be compared with the approved sample. For features that are difficult to measure visually, agree on a practical pass/fail method.",
        "Use more than an empty appearance sample for {$productName}. Load {$facts['packed']}, close the pack, open it as a customer would, and repeat the process after normal handling. Check whether the structure remains aligned and whether the product or printed surfaces show avoidable contact marks.",
        "Approval of {$productName} should create a reproducible reference. Record the dieline version, artwork version, material selection, opening direction, internal support, and packing sequence. Those controls help later reviews distinguish an intentional design change from production variation.",
    ];
    $qcSection = section(
        ['Sampling and QC checks for this format', 'Build a repeatable approval process', 'Who should review the sample', 'Define acceptance criteria early', 'Test the box with the product inside', 'Keep one controlled specification'][$qcVariant],
        [$qcVariants[$qcVariant]]
    );

    $quoteVariants = [
        "For a useful quotation for {$productName}, send the product dimensions, packed weight, quantity range, preferred structure, material direction, print coverage, finishing areas, insert requirement, number of artwork versions, and target packing method. Add photographs or an existing sample when they clarify shape or loading orientation. Unknown items can remain open for review; they should not be replaced with invented specifications.",
        "A quotation brief for {$productName} should identify what goes inside, how many components are packed, their measured dimensions and weight, the expected quantity range, chosen sales channel, artwork status, material preference, and any required insert or finish. Note which decisions need supplier input so assumptions are visible.",
        "Send a concise {$productName} project brief with product size, weight, orientation, quantity range, structure reference, material expectations, print files, finish map, internal-support needs, and the way units will be packed. If a regulatory, contact, or documentation requirement applies, state it separately for verification.",
        "Quote accuracy for {$productName} improves when the buyer provides real product data. Include dimensions, weight distribution, number of units per box, quantity range, artwork versions, material or appearance reference, insert expectations, packing workflow, and the handling conditions that the sample must address.",
        "Before requesting a quote for {$productName}, collect the product measurements, packed configuration, desired quantity range, target structure, material direction, print and finish scope, internal-support needs, and any deadline constraints for planning. Mark every unconfirmed item so it can be discussed instead of assumed.",
        "The {$productName} quotation request should function as a technical handoff. Provide the product envelope, weight, pack count, opening preference, quantity range, material and print expectations, insert concept, artwork readiness, and required sample checks. Clear inputs make structural alternatives easier to compare.",
    ];
    $quoteSection = section(
        ['Information to send for an accurate quote', 'Prepare a decision-ready project brief', 'Quote inputs procurement should collect', 'Replace assumptions with measured data', 'What the packaging supplier needs', 'Turn the brief into a technical handoff'][$quoteVariant],
        [$quoteVariants[$quoteVariant]]
    );

    $relatedSection = '';
    if ($productLinks || $guideLinks) {
        $relatedSection = '<h2>' . safeHeading(['Compare relevant packaging routes', 'Related products and planning resources', 'Useful next comparisons', 'Continue the specification review', 'Product alternatives worth checking', 'References for the buying team'][$relatedVariant]) . '</h2>' . "\n";
        if ($productLinks) {
            $relatedIntroductions = [
                "Use these existing pages to compare structure, packed item, insert, or use case with {$productName}. They are decision references, not interchangeable specifications.",
                "The products below provide adjacent comparisons for {$productName}. Review the relevant construction or application while keeping this product's measurements and pack-out method separate.",
                "These related options help position {$productName} among nearby structures and applications. Each target retains its own fit, opening, material, and support decisions.",
                "Compare {$productName} with the following existing products where the structure, insert, packed item, or sales route is relevant. Do not transfer specifications without a product-led review.",
                "The following catalog routes can help buyers test alternatives to {$productName}. Their value is comparative; the approved brief still needs the measured configuration of this product.",
                "For a focused sourcing comparison, review these neighboring products beside {$productName}. Look for useful differences in construction, internal support, presentation, and handling context.",
            ];
            $relatedSection .= safeParagraph($relatedIntroductions[$relatedVariant]) . "\n<ul>\n";
            foreach ($productLinks as $linkIndex => $link) {
                $linkReasons = [
                    "offers a structure-led comparison for {$productName}",
                    "helps compare an adjacent packed-item use case with {$productName}",
                    "provides a useful insert or opening reference beside {$productName}",
                    "shows a related presentation route without replacing the {$productName} brief",
                    "supports a buyer comparison around material, format, or channel for {$productName}",
                    "is a neighboring product option to assess against the intended {$productName} configuration",
                ];
                $relatedSection .= '<li>' . linkHtml($link) . ' ' . htmlspecialchars(
                    $linkReasons[($relatedVariant + $linkIndex) % count($linkReasons)],
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                ) . ".</li>\n";
            }
            $relatedSection .= "</ul>\n";
        }
        if ($guideLinks) {
            $guideIntroductions = [
                "The available guides below support a project-stage decision for {$productName}.",
                "Use these existing resources when preparing the {$productName} material or sample brief.",
                "The following guidance can inform structure, artwork, or sourcing questions for {$primary}.",
                "These resources add practical context to the {$productName} specification review.",
                "For deeper planning, the following guides relate to material, conversion, or pack approval.",
                "The buying team can use these references while documenting the {$productName} project.",
            ];
            $relatedSection .= safeParagraph($guideIntroductions[$relatedVariant]) . "\n<ul>\n";
            foreach ($guideLinks as $linkIndex => $link) {
                $guideReasons = [
                    "supports the material questions raised by {$productName}",
                    "adds planning context for the {$productName} structure",
                    "can inform artwork or sampling decisions for {$productName}",
                    "helps the buying team prepare the {$productName} specification",
                    "provides background for evaluating {$primary}",
                    "is relevant to a project-stage decision in the {$productName} brief",
                ];
                $relatedSection .= '<li>' . linkHtml($link) . ' ' . htmlspecialchars(
                    $guideReasons[($relatedVariant + $linkIndex + 2) % count($guideReasons)],
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                ) . ".</li>\n";
            }
            $relatedSection .= "</ul>\n";
        }
    }

    $faqVariant = ($relatedVariant + $ctaVariant + $id) % 6;
    $faqHeadings = [
        'Questions buyers ask about ',
        'Specification questions for ',
        'Procurement FAQ: ',
        'Practical questions before sourcing ',
        'What to clarify about ',
        'Buyer checklist questions for ',
    ];
    $dimensionQuestions = [
        "What dimensions are needed for {$productName}?",
        "How should the packed item be measured?",
        "Which product measurements belong in the brief?",
        "Should buyers measure the item or the packed arrangement?",
        "What size data helps develop this structure?",
        "How can the product envelope be documented?",
    ];
    $dimensionAnswers = [
        "For {$productName}, provide the maximum product length, width, height, weight, loading direction, and any protruding or fragile areas. If an insert or several components are involved, measure the full packed arrangement rather than each item in isolation.",
        "Measure the item that will enter {$productName} at its widest, tallest, and longest points, then record weight and loading orientation. Add caps, cables, accessories, protective wraps, or grouped components so the dieline is based on the real packed envelope.",
        "The {$primary} brief should list overall dimensions, weight distribution, contact-sensitive surfaces, and the direction used during packing. Where components nest or stack, provide their combined arrangement and the clearance needed for practical loading.",
        "Use the complete packed configuration for {$productName}, not only a nominal catalog size. Record maximum dimensions, orientation, product count, protective layers, and any feature that projects beyond the main body or must not carry pressure.",
        "Development of {$productName} needs measured length, width, height, weight, loading direction, and the location of fragile or uneven areas. If an insert is planned, include the clearance required for insertion and removal as part of the envelope.",
        "Document the {$productName} envelope with a dimensioned sketch, packed weight, front-facing direction, and photographs that show irregular features. For sets, measure the proposed arrangement so each component and gap is represented in the structural brief.",
    ];
    $materialQuestions = [
        'How should materials and finishes be compared?',
        'What makes one board option more suitable than another?',
        'When should finishing choices be approved?',
        'How can buyers compare print surfaces fairly?',
        'Which material details belong in the specification?',
        'Why should the final stock be tested in structure?',
    ];
    $materialAnswers = [
        "For {$primary}, compare shortlisted materials in the intended structure. Review stiffness, creasing, surface appearance, print response, rub exposure, and the effect of finishing on folds or closures. Approve a specification that names the chosen material and finish locations.",
        "A board option for {$productName} should be compared by grade, thickness, fold behavior, edge quality, print character, and support of the packed weight. Test those points in the converted structure instead of judging loose swatches alone.",
        "Approve finishing for {$productName} after the structural sample establishes folds, locks, glue areas, and handling points. Then review finish registration, surface rub, color appearance, and whether the selected effect changes closing or assembly behavior.",
        "Print comparisons for {$primary} should use the shortlisted stock and representative coverage. Keep color targets, coating areas, special-finish masks, and acceptance references consistent so a surface change is not mistaken for a print defect.",
        "The {$productName} specification should identify material type or grade, thickness direction, surface color, coating, print method, and special finishes. It should also state which sample represents the accepted crease, edge, and visible-surface quality.",
        "Testing the final stock in {$productName} reveals whether stiffness, folding, glue response, printing, and opening behavior work together. A flat material sample cannot show panel bowing, tight closures, or finish stress around converted features.",
    ];
    $faqSection = '<h2>' . safeHeading($faqHeadings[$faqVariant] . $primary) . "</h2>\n";
    $faqSection .= '<h3>' . safeHeading($dimensionQuestions[$faqVariant]) . "</h3>\n";
    $faqSection .= safeParagraph($dimensionAnswers[$faqVariant]);
    $faqSection .= '<h3>' . safeHeading($materialQuestions[$faqVariant]) . "</h3>\n";
    $faqSection .= safeParagraph($materialAnswers[$faqVariant]);
    if ($complexity >= 2) {
        $sampleQuestions = [
            'What should the structural sample prove?',
            'Which checks belong in the prototype assessment?',
            'How should the packed sample be tested?',
            'What makes a sample decision-ready?',
            'Which functions need written acceptance criteria?',
            'How can teams approve internal fit?',
        ];
        $sampleAnswers = [
            "The structural sample for {$productName} should prove product fit, opening and closure behavior, internal support, loading speed, removal clearance, panel alignment, and presentation after normal handling. Record any tolerance or packing instruction that affects the result.",
            "Assess {$productName} with the real item and intended pack-out method. Check erection, loading, closure, movement, reveal, removal, reclosure, print position, and any finish at a fold or contact point; record corrections before sign-off.",
            "Load {$productName}, reproduce expected handling, then open it in the intended customer orientation. Note product movement, insert stability, panel distortion, access, surface marks, and the time or difficulty involved in routine packing.",
            "A decision-ready {$productName} sample uses representative structure, material, internal support, artwork orientation, and product load. It should answer the known risk questions and produce a clear list of approved details and remaining revisions.",
            "For {$primary}, write acceptance points for dimensions, closure, opening force, internal clearance, product restraint, removal, panel alignment, and print or finish registration. Link each criterion to the approved packed sample.",
            "Approve the internal fit of {$productName} by checking contact areas, cavity size, finger clearance, movement, loading direction, removal, and support after normal handling. Record the insert version and product orientation with the sample.",
        ];
        $faqSection .= '<h3>' . safeHeading($sampleQuestions[$faqVariant]) . "</h3>\n";
        $faqSection .= safeParagraph($sampleAnswers[$faqVariant]);
    } else {
        $artworkQuestions = [
            'What should be confirmed before artwork approval?',
            'Which file controls prevent print errors?',
            'How should graphics follow the dieline?',
            'What belongs in the print handoff?',
            'Where should variable information be placed?',
            'How can artwork stay aligned with the pack?',
        ];
        $artworkAnswers = [
            "Before approving artwork for {$primary}, confirm the dieline version, panel orientation, material direction, print areas, barcode and label zones, finishing masks, and any text that must remain clear of folds, glue, or cut edges.",
            "For {$productName}, control the dieline and artwork version, separate cut and crease guides from print, identify special-finish masks, and confirm safe areas for text, codes, labels, and required product information.",
            "Place graphics for {$primary} on the tested panel map. Confirm front, back, base, opening edge, fold direction, glue zones, hidden flaps, and finishing areas so the assembled pack presents information in the intended orientation.",
            "The {$productName} print handoff should include the approved dieline, linked graphics, fonts or outlined text, image resolution, color references, finishing separations, barcode space, and a marked visual showing the assembled orientation.",
            "Reserve stable, scannable areas on {$productName} for barcodes, batch or variant labels, and required information. Keep them away from folds, cut edges, closures, and decorative effects that could reduce readability.",
            "Align {$productName} artwork by checking the assembled mock-up, not only the flat file. Review panel transitions, opening direction, centered features, repeatable finish positions, and any graphic that crosses a crease or edge.",
        ];
        $faqSection .= '<h3>' . safeHeading($artworkQuestions[$faqVariant]) . "</h3>\n";
        $faqSection .= safeParagraph($artworkAnswers[$faqVariant]);
    }

    $ctaVariants = [
        "To discuss {$productName}, send the measured product information, quantity range, artwork status, and the structural questions you want the sample to answer.",
        "Prepare the product measurements and packing brief for {$primary}, then request a project quote based on the structure, material, print, and support decisions above.",
        "Share the real packed configuration, artwork readiness, and quantity range so the next step for {$productName} can be evaluated without unsupported assumptions.",
        "Use this brief to organize the product data, sample checks, and quote inputs for {$primary}; unresolved details can be reviewed as open decisions.",
        "Send a specification-led enquiry for {$productName} with the product envelope, packing method, quantity range, and artwork requirements.",
        "When the product data is ready, request a quote for {$primary} and identify which structure, material, insert, or sampling decisions still require review.",
    ];
    $ctaSection = section('Next step', [$ctaVariants[$ctaVariant]]);

    $blocks = [
        'intro' => $intro,
        'structure' => $structureSection,
        'material' => $materialSection,
        'insert' => $insertSection,
        'channel' => $channelSection,
        'risk' => $riskSection,
        'qc' => $qcSection,
        'quote' => $quoteSection,
        'related' => $relatedSection,
        'faq' => $faqSection,
        'cta' => $ctaSection,
    ];
    $orders = [
        ['intro', 'structure', 'material', 'insert', 'channel', 'risk', 'qc', 'quote', 'related', 'faq', 'cta'],
        ['intro', 'channel', 'structure', 'insert', 'material', 'risk', 'qc', 'related', 'quote', 'faq', 'cta'],
        ['intro', 'structure', 'risk', 'material', 'insert', 'channel', 'qc', 'quote', 'faq', 'related', 'cta'],
        ['intro', 'material', 'structure', 'channel', 'insert', 'risk', 'qc', 'related', 'faq', 'quote', 'cta'],
        ['intro', 'channel', 'material', 'structure', 'risk', 'insert', 'qc', 'quote', 'related', 'faq', 'cta'],
        ['intro', 'risk', 'structure', 'material', 'insert', 'channel', 'qc', 'faq', 'related', 'quote', 'cta'],
    ];
    if ($complexity === 1) {
        $blocks['insert'] = '';
    }
    $html = '';
    foreach ($orders[$orderVariant] as $block) {
        $html .= $blocks[$block];
    }
    $minimumMainWords = $complexity === 1 ? 700 : ($complexity === 2 ? 900 : 1000);
    if (wordCount($html) < $minimumMainWords) {
        $html .= '<h2>' . safeHeading('Decision record for ' . $title) . "</h2>\n";
        $html .= safeParagraph(
            "The final decision record for {$productName} should explain why {$facts['structure']} was selected for {$facts['packed']}, how {$facts['opening']} is expected to work, and which material and support details remain subject to sample approval. It should also identify the artwork version, front-facing orientation, packing sequence, and the specific checks used to accept product fit. Keeping those decisions together makes later changes easier to evaluate without treating an appearance sample as a complete technical specification."
        ) . "\n";
    }
    if (wordCount($html) < $minimumMainWords) {
        $html .= safeParagraph(
            "Procurement should also record alternatives that were considered for {$primary} and why they were not chosen. A rejected option may have created excessive movement, slow loading, unclear opening, avoidable material use, or poor alignment with {$facts['channels']}. This comparison gives the approved configuration a clear purpose and helps the next review focus on genuine improvements rather than reopening decisions without new product evidence."
        ) . "\n";
    }
    if (wordCount($html) < $minimumMainWords) {
        $html .= safeParagraph(
            "Before release, assign responsibility for every open item in the {$productName} specification. Product measurements, structural revisions, material samples, artwork corrections, insert testing, and packing instructions should each have a named decision owner and a recorded status. This simple control is useful when several teams review the same project, because it separates confirmed facts from preferences and prevents an unresolved assumption from being carried into the quotation or production reference."
        ) . "\n";
    }
    $html = lexicalizeHtml($html, $id);
    $html = trim($html) . PHP_EOL;

    $shortVariants = [
        "{$title} is planned for {$facts['packed']}, with fit, material behavior, opening, print layout, and packing workflow reviewed together. It is intended for B2B teams that want a measured structure and a clear sampling brief before quotation.",
        "This {$primary} supports {$facts['packed']}. Buyers can use the brief to compare structure, internal fit, material, artwork, and handling requirements, then confirm unresolved details through a product-loaded sample before requesting production pricing.",
        "Designed around {$facts['packed']}, {$title} helps procurement and packaging teams define the box structure, opening sequence, material direction, print zones, and internal support. Final specifications should be based on measured product data and an approved sample.",
        "{$title} is a specification-led packaging option for {$facts['packed']}. The project should confirm product dimensions, loading orientation, structural fit, material, finishing, and any insert needs so the quotation reflects the actual packing method.",
        "For teams sourcing {$primary}, this product brief connects presentation with practical packing requirements. It covers the packed item, structure, material, opening, internal support, print planning, and sample checks without assuming unverified production details.",
        "This {$productName} is evaluated around {$facts['packed']} and {$facts['channels']}. B2B buyers should verify dimensions, structure, material, artwork zones, internal clearances, and packing workflow before approving a production reference.",
    ];
    $short = normalizeSpace($shortVariants[$shortVariant]);
    if (wordCount($short) < 40) {
        $short .= ' The approved brief should record the agreed structure, product fit, and packing sequence.';
    }

    $seoTitle = fitSeoTitle((string) $seo['proposed_rank_math_title'], $primary);
    $meta = fitMeta((string) $seo['proposed_meta_description'], $primary);
    $canonical = (string) ($baseline['rank_math_canonical_url'] ?? '');

    return [
        'product_id' => $id,
        'title' => $title,
        'slug' => (string) $dna['slug'],
        'url' => (string) $dna['url'],
        'status' => 'publish',
        'batch_id' => (string) $batch['batch_id'],
        'batch_order' => (int) $batch['batch_order'],
        'cluster' => (string) $batch['cluster'],
        'complexity' => $complexity,
        'short_description' => $short,
        'main_content' => $html,
        'seo_title' => $seoTitle,
        'meta_description' => $meta,
        'focus_keyword' => $primary,
        'canonical_preserved' => $canonical,
        'secondary_keywords' => array_values(array_filter([
            (string) $keyword['secondary_keyword_1'],
            (string) $keyword['secondary_keyword_2'],
            (string) $keyword['secondary_keyword_3'],
            (string) $keyword['secondary_keyword_4'],
            (string) $keyword['secondary_keyword_5'],
        ])),
        'internal_links' => array_map(
            static fn(array $row): array => [
                'type' => (string) $row['link_type'],
                'target_id' => (string) $row['target_id'],
                'target_title' => (string) $row['target_title'],
                'target_url' => (string) $row['target_url'],
                'anchor_text' => (string) $row['anchor_text'],
                'reason' => (string) $row['reason'],
            ],
            $links
        ),
        'owner_review' => (string) $keyword['owner_review'],
        'owner_review_reasons' => (string) $dna['owner_review_reasons'],
        'decision_flag' => (string) $keyword['decision_flag'],
        'source_hashes' => [
            'baseline_product_fields' => hash('sha256', json_encode($baseline, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: ''),
            'dna' => hash('sha256', json_encode($dna, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: ''),
            'keyword' => hash('sha256', json_encode($keyword, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: ''),
            'seo' => hash('sha256', json_encode($seo, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: ''),
            'links' => hash('sha256', json_encode($links, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: ''),
        ],
    ];
}

function shingles(string $text, int $size = 5): array
{
    $tokens = array_map(static fn(string $word): string => mb_strtolower($word, 'UTF-8'), words($text));
    $set = [];
    for ($i = 0, $max = count($tokens) - $size; $i <= $max; $i++) {
        $set[implode(' ', array_slice($tokens, $i, $size))] = true;
    }
    return $set;
}

function jaccard(array $a, array $b): float
{
    if (!$a || !$b) {
        return 0.0;
    }
    $intersection = count(array_intersect_key($a, $b));
    $union = count($a) + count($b) - $intersection;
    return $union > 0 ? $intersection / $union : 0.0;
}

$inventoryRows = readCsv(AUDIT_DIR . '\\product-inventory.csv');
$duplicateContentRows = readCsv(AUDIT_DIR . '\\duplicate-content-report.csv');
$duplicateSeoRows = readCsv(AUDIT_DIR . '\\duplicate-seo-fields.csv');
$dnaRows = readCsv(MAP_DIR . '\\product-dna.csv');
$keywordRows = readCsv(MAP_DIR . '\\keyword-map.csv');
$seoRows = readCsv(MAP_DIR . '\\proposed-seo-fields.csv');
$cannibalRows = readCsv(MAP_DIR . '\\intent-cannibalization-report.csv');
$linkRows = readCsv(MAP_DIR . '\\internal-link-plan.csv');
$batchRows = readCsv(MAP_DIR . '\\rewrite-batch-plan.csv');

$publishedInventory = array_values(array_filter($inventoryRows, static fn(array $row): bool => $row['status'] === 'publish'));
if (
    count($publishedInventory) !== 179
    || count($dnaRows) !== 179
    || count($keywordRows) !== 179
    || count($seoRows) !== 179
    || count($batchRows) !== 179
) {
    fail('Input coverage is not exactly 179 published products.');
}

$inventory = indexBy($publishedInventory, 'id');
$dna = indexBy($dnaRows, 'product_id');
$keywords = indexBy($keywordRows, 'product_id');
$seo = indexBy($seoRows, 'product_id');
$batches = indexBy($batchRows, 'product_id');
$baselinePayload = json_decode((string) file_get_contents(BASELINE_PATH), true);
if (!is_array($baselinePayload) || (int) ($baselinePayload['product_count'] ?? 0) !== 179) {
    fail('Invalid product baseline backup.');
}
$baseline = indexBy($baselinePayload['products'], 'id');

$linksByProduct = [];
foreach ($linkRows as $row) {
    if ((string) $row['http_status'] !== '200' || (string) $row['validation'] !== 'VALID_EXISTING_URL') {
        fail('Internal-link input contains an unverified URL.');
    }
    $linksByProduct[(string) $row['source_product_id']][] = $row;
}

$requiredIds = array_keys($dna);
sort($requiredIds, SORT_NUMERIC);
foreach ([$inventory, $keywords, $seo, $batches, $baseline] as $dataset) {
    $ids = array_keys($dataset);
    sort($ids, SORT_NUMERIC);
    if ($ids !== $requiredIds) {
        fail('Input product ID sets do not reconcile.');
    }
}

ensureDir(SOURCE_DIR);
ensureDir(SOURCE_DIR . '\\products');
ensureDir(SOURCE_DIR . '\\batch-backups');
ensureDir(FINAL_DIR);

$records = [];
$seoTitleValues = [];
$metaValues = [];
$focusValues = [];
$paragraphOwners = [];
$paragraphSamples = [];
$shingleSets = [];
$qaRows = [];
$blocked = [];
$variantProfiles = buildVariantProfiles($requiredIds);

foreach ($requiredIds as $id) {
    $record = buildProductContent(
        $dna[$id],
        $keywords[$id],
        $seo[$id],
        $linksByProduct[$id] ?? [],
        $batches[$id],
        $baseline[$id],
        $variantProfiles[$id]
    );

    $shortWords = wordCount($record['short_description']);
    $mainWords = wordCount($record['main_content']);
    $minWords = $record['complexity'] === 1 ? 700 : ($record['complexity'] === 2 ? 900 : 1000);
    $maxWords = $record['complexity'] === 1 ? 1075 : ($record['complexity'] === 2 ? 1250 : 1500);
    $bannedPattern = '/\b(?:fixed MOQ|free sample|free shipping|FSC|biodegradable|compostable|soy[- ]based ink|100% sustainable|factory capacity|years of experience|guarantee|verified packaging client)\b/i';
    if (preg_match($bannedPattern, $record['short_description'] . ' ' . $record['main_content'], $match)) {
        fail("Banned claim '{$match[0]}' in product {$id}");
    }
    if ($shortWords < 40 || $shortWords > 80) {
        fail("Short description word count {$shortWords} outside 40-80 for product {$id}");
    }
    if ($mainWords < $minWords || $mainWords > $maxWords) {
        fail("Main content word count {$mainWords} outside {$minWords}-{$maxWords} for product {$id}");
    }
    if (!preg_match('/^\s*<h2\b/i', $record['main_content']) || preg_match('/<h1\b/i', $record['main_content'])) {
        fail("Heading structure failed for product {$id}");
    }
    if (preg_match('/<!--|\[(?:\/?)[A-Za-z_][^\]]*\]/', $record['main_content'])) {
        fail("Comment marker or shortcode found for product {$id}");
    }
    if (mb_strlen($record['seo_title'], 'UTF-8') < 45 || mb_strlen($record['seo_title'], 'UTF-8') > 60) {
        fail("SEO title length failed for product {$id}");
    }
    if (mb_strlen($record['meta_description'], 'UTF-8') < 135 || mb_strlen($record['meta_description'], 'UTF-8') > 160) {
        fail("Meta description length failed for product {$id}");
    }

    foreach ([$record['seo_title'] => &$seoTitleValues, $record['meta_description'] => &$metaValues, $record['focus_keyword'] => &$focusValues] as $value => &$bucket) {
        $normalized = mb_strtolower(normalizeSpace((string) $value), 'UTF-8');
        $bucket[$normalized][] = $id;
    }
    unset($bucket);

    preg_match_all('/<p>(.*?)<\/p>/si', $record['main_content'], $paragraphMatches);
    foreach ($paragraphMatches[1] ?? [] as $paragraphHtml) {
        $paragraph = normalizeSpace($paragraphHtml);
        if (wordCount($paragraph) >= 30) {
            $paragraphHash = hash('sha256', mb_strtolower($paragraph, 'UTF-8'));
            $paragraphOwners[$paragraphHash][] = $id;
            $paragraphSamples[$paragraphHash] = $paragraph;
        }
    }
    $shingleSets[$id] = shingles($record['main_content']);
    $record['word_count_short_description'] = $shortWords;
    $record['word_count_main_content'] = $mainWords;
    $record['content_hash'] = hash('sha256', $record['short_description'] . "\n" . $record['main_content']);

    $htmlPath = SOURCE_DIR . '\\products\\' . $id . '-' . $record['slug'] . '.html';
    if (file_put_contents($htmlPath, $record['main_content']) === false) {
        fail("Cannot write {$htmlPath}");
    }
    $record['html_file'] = $htmlPath;
    $record['html_sha256'] = hash_file('sha256', $htmlPath);
    $records[$id] = $record;
}

foreach ([$seoTitleValues, $metaValues, $focusValues] as $uniqueField) {
    foreach ($uniqueField as $ids) {
        if (count($ids) > 1) {
            fail('Duplicate proposed SEO field across products: ' . implode(',', $ids));
        }
    }
}

$duplicateParagraphGroups = [];
foreach ($paragraphOwners as $hash => $ids) {
    $ids = array_values(array_unique($ids));
    if (count($ids) > 1) {
        $duplicateParagraphGroups[$hash] = $ids;
    }
}
if ($duplicateParagraphGroups) {
    $firstHash = array_key_first($duplicateParagraphGroups);
    $first = $duplicateParagraphGroups[$firstHash];
    fail('Exact duplicate long paragraph detected for products: ' . implode(',', $first) . '; sample: ' . ($paragraphSamples[$firstHash] ?? ''));
}

$similarityRows = [];
$recordIds = array_keys($records);
$maxSimilarity = 0.0;
$maxPair = [];
for ($i = 0; $i < count($recordIds); $i++) {
    for ($j = $i + 1; $j < count($recordIds); $j++) {
        $idA = $recordIds[$i];
        $idB = $recordIds[$j];
        $similarity = jaccard($shingleSets[$idA], $shingleSets[$idB]);
        if ($similarity > $maxSimilarity) {
            $maxSimilarity = $similarity;
            $maxPair = [$idA, $idB];
        }
        if ($similarity > 0.30) {
            $similarityRows[] = [
                'product_id_a' => $idA,
                'product_id_b' => $idB,
                'similarity' => round($similarity, 4),
                'status' => 'REVIEW_REQUIRED',
            ];
        }
    }
}
if ($similarityRows) {
    fail('Main-content similarity exceeds 30%; highest pair ' . implode('/', $maxPair) . ' = ' . round($maxSimilarity, 4));
}

$manifestRecords = [];
foreach ($records as $record) {
    $manifestRecord = $record;
    unset($manifestRecord['main_content']);
    $manifestRecords[] = $manifestRecord;
    $qaRows[] = [
        'product_id' => $record['product_id'],
        'title' => $record['title'],
        'slug' => $record['slug'],
        'batch_id' => $record['batch_id'],
        'complexity' => $record['complexity'],
        'short_words' => $record['word_count_short_description'],
        'main_words' => $record['word_count_main_content'],
        'seo_title_length' => mb_strlen($record['seo_title'], 'UTF-8'),
        'meta_length' => mb_strlen($record['meta_description'], 'UTF-8'),
        'internal_links' => count($record['internal_links']),
        'owner_review' => $record['owner_review'],
        'decision_flag' => $record['decision_flag'],
        'content_hash' => $record['content_hash'],
        'source_html' => $record['html_file'],
    ];
}

$manifest = [
    'schema_version' => 1,
    'created_at' => date(DATE_ATOM),
    'source_root' => SOURCE_DIR,
    'product_count' => count($manifestRecords),
    'input_counts' => [
        'inventory_rows' => count($inventoryRows),
        'published_inventory_rows' => count($publishedInventory),
        'duplicate_content_rows' => count($duplicateContentRows),
        'duplicate_seo_rows' => count($duplicateSeoRows),
        'dna_rows' => count($dnaRows),
        'keyword_rows' => count($keywordRows),
        'seo_rows' => count($seoRows),
        'cannibalization_rows' => count($cannibalRows),
        'internal_link_rows' => count($linkRows),
        'batch_rows' => count($batchRows),
    ],
    'qa' => [
        'unique_seo_titles' => count($seoTitleValues),
        'unique_meta_descriptions' => count($metaValues),
        'unique_focus_keywords' => count($focusValues),
        'exact_duplicate_long_paragraph_groups' => count($duplicateParagraphGroups),
        'maximum_pairwise_5_word_shingle_similarity' => round($maxSimilarity, 4),
        'maximum_similarity_pair' => $maxPair,
        'pairs_above_0_30' => count($similarityRows),
        'blocked_owner_fact_products' => count($blocked),
    ],
    'products' => $manifestRecords,
];

$manifestPath = SOURCE_DIR . '\\content-manifest.json';
$manifestJson = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($manifestJson === false || file_put_contents($manifestPath, $manifestJson . PHP_EOL) === false) {
    fail('Cannot write content manifest.');
}

$qaPath = SOURCE_DIR . '\\source-qa.json';
$qaPayload = [
    'ok' => true,
    'products' => count($records),
    'qa_rows' => $qaRows,
    'maximum_similarity' => round($maxSimilarity, 4),
    'maximum_similarity_pair' => $maxPair,
    'manifest_sha256' => hash_file('sha256', $manifestPath),
];
$qaJson = json_encode($qaPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($qaJson === false || file_put_contents($qaPath, $qaJson . PHP_EOL) === false) {
    fail('Cannot write source QA.');
}

echo json_encode([
    'ok' => true,
    'products' => count($records),
    'unique_seo_titles' => count($seoTitleValues),
    'unique_meta_descriptions' => count($metaValues),
    'unique_focus_keywords' => count($focusValues),
    'exact_duplicate_long_paragraph_groups' => count($duplicateParagraphGroups),
    'maximum_similarity' => round($maxSimilarity, 4),
    'maximum_similarity_pair' => $maxPair,
    'manifest' => $manifestPath,
    'manifest_sha256' => hash_file('sha256', $manifestPath),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
