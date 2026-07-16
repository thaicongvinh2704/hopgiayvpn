<?php
/**
 * Syncs the pharmaceutical paper packaging information guide and its images.
 */

add_action('admin_init', 'custom_box_sync_pharmaceutical_packaging_information_post');
add_action('admin_notices', 'custom_box_pharmaceutical_packaging_information_admin_notice');

function custom_box_sync_pharmaceutical_packaging_information_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $version = '2026-07-16-v1';
    $already_synced = $version === get_option('custom_box_pharmaceutical_packaging_information_sync_version');
    $has_issues = '' !== (string) get_option('custom_box_pharmaceutical_packaging_information_missing_post', '')
        || !empty((array) get_option('custom_box_pharmaceutical_packaging_information_missing_images', array()))
        || !empty((array) get_option('custom_box_pharmaceutical_packaging_information_missing_slots', array()));

    if ($already_synced && !$has_issues) {
        return;
    }

    $post_id = custom_box_upsert_pharmaceutical_packaging_information_post();

    if (is_wp_error($post_id)) {
        update_option('custom_box_pharmaceutical_packaging_information_missing_post', $post_id->get_error_message(), false);
        return;
    }

    update_option('custom_box_pharmaceutical_packaging_information_missing_post', '', false);
    update_option('custom_box_pharmaceutical_packaging_information_sync_version', $version, false);
}

function custom_box_upsert_pharmaceutical_packaging_information_post()
{
    $post_data = custom_box_pharmaceutical_packaging_information_post_data();
    $post = custom_box_find_pharmaceutical_packaging_information_post($post_data['slug'], $post_data['title']);
    $content = custom_box_pharmaceutical_packaging_information_content();

    $payload = array(
        'post_title'   => $post_data['title'],
        'post_name'    => $post_data['slug'],
        'post_type'    => 'post',
        'post_excerpt' => $post_data['excerpt'],
    );

    if ($post) {
        $payload['ID'] = (int) $post->ID;
        $payload['post_status'] = in_array($post->post_status, array('publish', 'private'), true) ? $post->post_status : 'draft';

        $existing_content = (string) $post->post_content;
        $is_published_or_private = in_array($post->post_status, array('publish', 'private'), true);

        if (
            !$is_published_or_private
            || '' === trim($existing_content)
            || false !== strpos($existing_content, 'IMAGE_SLOT_')
            || false === strpos($existing_content, 'vpn-pharmaceutical-packaging-information-image:')
        ) {
            $payload['post_content'] = $content;
        }

        $result = wp_update_post($payload, true);
    } else {
        $payload['post_status'] = 'draft';
        $payload['post_content'] = $content;
        $result = wp_insert_post($payload, true);
    }

    if (is_wp_error($result)) {
        return $result;
    }

    $post_id = (int) $result;
    custom_box_sync_pharmaceutical_packaging_information_terms($post_id, $post_data);
    custom_box_sync_pharmaceutical_packaging_information_meta($post_id, $post_data);
    custom_box_sync_pharmaceutical_packaging_information_images($post_id);
    update_post_meta($post_id, '_custom_box_pharmaceutical_packaging_information_synced', current_time('mysql'));

    return $post_id;
}

function custom_box_pharmaceutical_packaging_information_post_data(): array
{
    return array(
        'title'           => 'What Information Should Be Prepared for Pharmaceutical Paper Packaging?',
        'slug'            => 'what-information-pharmaceutical-paper-packaging',
        'seo_title'       => 'Pharmaceutical Packaging Information: Artwork Checklist',
        'seo_description' => 'Prepare pharmaceutical carton artwork correctly with a practical checklist for layout, readability, barcodes, batch data, Braille and approval files.',
        'focus_keyword'   => 'what information should be on pharmaceutical packaging',
        'excerpt'         => 'Learn what data, artwork files, barcode specifications, variable batch areas and readability controls should be prepared before producing pharmaceutical paper cartons.',
        'category'        => array(
            'name' => 'Industry Packaging Guides',
            'slug' => 'industry-packaging-guides',
        ),
        'tags'            => array(
            'Pharmaceutical Packaging',
            'Folding Cartons',
            'Packaging Artwork',
            'Barcode',
            'Variable Data',
            'Packaging Readability',
        ),
    );
}

function custom_box_pharmaceutical_packaging_information_images(): array
{
    return array(
        'featured' => array(
            'base'    => 'what-information-pharmaceutical-paper-packaging',
            'alt'     => 'Pharmaceutical paper carton artwork with barcode and batch information zones',
            'title'   => 'Pharmaceutical Packaging Information Preparation',
            'caption' => 'A production-ready carton requires approved copy, code specifications and clearly reserved variable-data areas.',
        ),
        'slot_1'   => array(
            'base'    => 'pharmaceutical-carton-panel-information-map',
            'alt'     => 'Unfolded pharmaceutical carton dieline with organized information zones',
            'title'   => 'Pharmaceutical Carton Panel Map',
            'caption' => 'Each carton panel should have a defined information and production role before graphic styling begins.',
        ),
        'slot_2'   => array(
            'base'    => 'pharmaceutical-packaging-readability-proof',
            'alt'     => 'Full-size pharmaceutical carton proof checked for text size and contrast',
            'title'   => 'Pharmaceutical Packaging Readability Check',
            'caption' => 'Readability should be assessed on a full-size proof rather than only on a computer screen.',
        ),
        'slot_3'   => array(
            'base'    => 'pharmaceutical-carton-barcode-batch-area',
            'alt'     => 'Barcode, Data Matrix, lot and expiry printing area on a paper carton',
            'title'   => 'Barcode and Variable Data Area',
            'caption' => 'Machine-readable codes and batch data need a flat, high-contrast area free from structural interference.',
        ),
        'slot_4'   => array(
            'base'    => 'pharmaceutical-carton-artwork-approval-checklist',
            'alt'     => 'Pharmaceutical carton artwork, dieline and QC documents prepared for approval',
            'title'   => 'Pharmaceutical Artwork Approval Package',
            'caption' => 'Artwork approval should cover content, structure, codes, variable printing and finishing.',
        ),
    );
}

function custom_box_find_pharmaceutical_packaging_information_post(string $slug, string $title): ?WP_Post
{
    $post = get_page_by_path($slug, OBJECT, 'post');

    if ($post && 'trash' !== $post->post_status) {
        return $post;
    }

    global $wpdb;

    $post_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts}
             WHERE post_type = 'post'
             AND post_status <> 'trash'
             AND post_title = %s
             ORDER BY ID DESC
             LIMIT 1",
            $title
        )
    );

    return $post_id ? get_post($post_id) : null;
}

function custom_box_sync_pharmaceutical_packaging_information_terms(int $post_id, array $post_data): void
{
    $category = get_term_by('slug', $post_data['category']['slug'], 'category');

    if (!$category || is_wp_error($category)) {
        $created = wp_insert_term(
            $post_data['category']['name'],
            'category',
            array('slug' => $post_data['category']['slug'])
        );

        if (!is_wp_error($created)) {
            $category = get_term((int) $created['term_id'], 'category');
        }
    }

    if ($category && !is_wp_error($category)) {
        wp_set_post_categories($post_id, array((int) $category->term_id), false);
    }

    wp_set_post_tags($post_id, $post_data['tags'], false);
}

function custom_box_sync_pharmaceutical_packaging_information_meta(int $post_id, array $post_data): void
{
    update_post_meta($post_id, 'rank_math_title', $post_data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $post_data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $post_data['focus_keyword']);
}

function custom_box_sync_pharmaceutical_packaging_information_images(int $post_id): void
{
    $images = custom_box_pharmaceutical_packaging_information_images();
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach ($images as $key => $image) {
        $attachment_id = custom_box_find_pharmaceutical_packaging_information_attachment($image['base']);

        if (!$attachment_id || !wp_get_attachment_url($attachment_id)) {
            $missing_images[] = $image['base'];
            continue;
        }

        update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']);
        wp_update_post(array(
            'ID'           => $attachment_id,
            'post_parent'  => $post_id,
            'post_title'   => $image['title'],
            'post_excerpt' => $image['caption'],
        ));

        if ('featured' === $key) {
            set_post_thumbnail($post_id, $attachment_id);
            continue;
        }

        $slot_number = (int) substr($key, -1);
        $marker = '<!-- vpn-pharmaceutical-packaging-information-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_pharmaceutical_packaging_information_figure($attachment_id, $image);
        $slot = '<!-- IMAGE_SLOT_' . $slot_number . ' -->';
        $slot_wrapper_pattern = '/<span\b[^>]*>\s*' . preg_quote($slot, '/') . '\s*<\/span>/i';
        $marker_pattern = '/' . preg_quote($marker, '/') . '\s*<figure\b.*?<\/figure>/is';

        if (false !== strpos($content, $marker)) {
            $content = preg_replace($marker_pattern, $figure, $content, 1);
        } elseif (preg_match($slot_wrapper_pattern, $content)) {
            $content = preg_replace($slot_wrapper_pattern, $figure, $content, 1);
        } elseif (false !== strpos($content, $slot)) {
            $content = str_replace($slot, $figure, $content);
        } else {
            $missing_slots[] = 'IMAGE_SLOT_' . $slot_number;
        }
    }

    if ($post && $content !== (string) $post->post_content) {
        wp_update_post(array(
            'ID'           => $post_id,
            'post_content' => $content,
        ));
    }

    update_option('custom_box_pharmaceutical_packaging_information_missing_images', $missing_images, false);
    update_option('custom_box_pharmaceutical_packaging_information_missing_slots', $missing_slots, false);
}

function custom_box_find_pharmaceutical_packaging_information_attachment(string $base): int
{
    $attachment = get_page_by_path(sanitize_title($base), OBJECT, 'attachment');

    if ($attachment) {
        return (int) $attachment->ID;
    }

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

function custom_box_pharmaceutical_packaging_information_figure(int $attachment_id, array $image): string
{
    return sprintf(
        '<figure><img src="%s" alt="%s" style="width:100%%; height:auto;" loading="lazy" decoding="async"></figure>',
        esc_url(wp_get_attachment_url($attachment_id)),
        esc_attr($image['alt'])
    );
}

function custom_box_pharmaceutical_packaging_information_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing_post = (string) get_option('custom_box_pharmaceutical_packaging_information_missing_post', '');
    $missing_images = (array) get_option('custom_box_pharmaceutical_packaging_information_missing_images', array());
    $missing_slots = (array) get_option('custom_box_pharmaceutical_packaging_information_missing_slots', array());

    if ('' === $missing_post && empty($missing_images) && empty($missing_slots)) {
        return;
    }

    $messages = array();

    if ('' !== $missing_post) {
        $messages[] = 'post issue: ' . esc_html($missing_post);
    }

    if (!empty($missing_images)) {
        $messages[] = 'missing images: ' . esc_html(implode(', ', $missing_images));
    }

    if (!empty($missing_slots)) {
        $messages[] = 'missing slots: ' . esc_html(implode(', ', $missing_slots));
    }

    echo '<div class="notice notice-warning"><p><strong>Pharmaceutical packaging information post sync:</strong> ';
    echo implode(' | ', $messages);
    echo '</p></div>';
}

function custom_box_pharmaceutical_packaging_information_content(): string
{
    return <<<'HTML'
<p>Pharmaceutical paper packaging should not enter artwork production with only a logo, a box size, and a spreadsheet of product text. Before a folding carton can be designed correctly, the project team must separate approved regulatory copy, variable production data, machine-readable codes, and operational areas such as Braille, tamper evidence, overlabels, or dispensing labels.</p>

<p>The packaging supplier’s role is to convert approved information into a printable and manufacturable carton. It is not the supplier’s role to decide which medical claims, warnings, abbreviations, barcode data, expiry format, or market-specific statements are legally required.</p>

<p><em>This guide is a packaging-preparation checklist for brand owners, procurement teams, artwork coordinators, and packaging manufacturers. It is not legal, regulatory, or medical advice. The final content and layout must be approved by the responsible regulatory and quality teams for every target market.</em></p>

<!-- IMAGE_SLOT_1 -->
<h2>Organize the Information Before Designing the Carton</h2>
<p>A common artwork failure occurs when every piece of information is treated as ordinary printed copy. In practice, pharmaceutical packaging information belongs to several different production categories. Each category has a different owner, approval route, printing method, and risk.</p>
<table>
<thead>
<tr>
<th>Information category</th>
<th>Typical examples</th>
<th>Primary owner</th>
<th>Packaging implication</th>
</tr>
</thead>
<tbody>
<tr>
<td>Approved fixed content</td>
<td>Product identity, strength, pharmaceutical form, pack contents, approved warnings, company details, market references and storage statements</td>
<td>Regulatory affairs or marketing authorisation holder</td>
<td>Must be supplied as controlled text and mapped to the correct carton panels</td>
</tr>
<tr>
<td>Variable production data</td>
<td>Batch or lot number, expiry date, manufacturing date where applicable, serial number and market-specific variable fields</td>
<td>Quality, production or serialization team</td>
<td>Requires a reserved print zone, character limits, print-method confirmation and inspection criteria</td>
</tr>
<tr>
<td>Machine-readable information</td>
<td>Linear barcode, two-dimensional code, unique identifier or internal packaging-control code</td>
<td>Regulatory, serialization or supply-chain team</td>
<td>Requires an approved data string, symbol specification, clear area, contrast and scan verification</td>
</tr>
<tr>
<td>Operational information areas</td>
<td>Braille, tamper-evident label, pharmacy label, local overlabel, leaflet insertion and inspection marks</td>
<td>Cross-functional project team</td>
<td>Must be included in the dieline and carton construction before the artwork is locked</td>
</tr>
</tbody>
</table>
<p>This separation prevents the designer from placing a permanent graphic beneath an area that later needs to receive an inkjet code, serialized Data Matrix, pharmacy label, Braille embossing, or tamper-evident seal.</p>
<h2>Prepare a Market-Specific Content Master</h2>
<p>The artwork process should begin with one approved content master for each market or group of markets that uses exactly the same information. A previous carton PDF should not be treated as the source of truth unless its revision and approval status have been confirmed.</p>

<p>The content master should identify:</p>
<ul>
<li>The destination country or regulatory market.</li>
<li>The product presentation, strength, pack size and sales channel.</li>
<li>The controlled source document for every approved statement.</li>
<li>The artwork language or languages.</li>
<li>The approved translation for each language.</li>
<li>Which information is mandatory, conditional or optional.</li>
<li>Which abbreviations have been approved.</li>
<li>Which fields differ among strengths, pack sizes or markets.</li>
<li>The content version, approval date and responsible approver.</li>
<li>Any local price, reimbursement, authorization or overlabel area that must remain available.</li>
</ul>
<p>A packaging converter should not independently shorten, translate, reword or rearrange controlled pharmaceutical statements. Even a small editorial change can create a mismatch between the carton artwork and the approved source text.</p>
<h3>Create a SKU Matrix Before Creating Artwork Variants</h3>
<p>Projects with several strengths, languages, pack counts or markets require a SKU matrix. The matrix should show every field that remains common and every field that changes.</p>
<table>
<thead>
<tr>
<th>SKU control field</th>
<th>Example of what should be recorded</th>
</tr>
</thead>
<tbody>
<tr>
<td>Artwork identity</td>
<td>Artwork number, revision and approval status</td>
</tr>
<tr>
<td>Market</td>
<td>Country, region or approved multi-market group</td>
</tr>
<tr>
<td>Product variant</td>
<td>Strength, form, pack count and presentation</td>
</tr>
<tr>
<td>Languages</td>
<td>Exact language set and translation version</td>
</tr>
<tr>
<td>Machine-readable data</td>
<td>Approved barcode type, data owner and data-file reference</td>
</tr>
<tr>
<td>Variable coding</td>
<td>Required fields, format, maximum length and printing process</td>
</tr>
<tr>
<td>Carton specification</td>
<td>Dieline version, board, coating and finishing restrictions</td>
</tr>
</tbody>
</table>
<p>This matrix is more reliable than creating one finished design and manually replacing product information for each additional variant.</p>
<h2>Build a Panel Map Before Styling the Artwork</h2>
<p>The dieline should be converted into an annotated panel map before graphic styling begins. The map assigns a job to every printable face and shows where information cannot be placed because of creases, glue, locks, seals or production marks.</p>

<p>The following panel plan is a practical starting point rather than a universal regulatory rule:</p>
<table>
<thead>
<tr>
<th>Carton area</th>
<th>Practical information role</th>
<th>Production risk to avoid</th>
</tr>
</thead>
<tbody>
<tr>
<td>Primary display panel</td>
<td>Critical product identification and clear variant differentiation</td>
<td>Allowing branding, imagery or promotional text to compete with product identity</td>
</tr>
<tr>
<td>Secondary side panel</td>
<td>Approved supporting information, company details, storage or other market-specific statements</td>
<td>Using condensed text simply to make an undersized carton work</td>
</tr>
<tr>
<td>Dedicated coding panel</td>
<td>Barcode, Data Matrix, human-readable code and variable batch or expiry data</td>
<td>Positioning codes over a crease, glue seam, embossing, foil or reflective background</td>
</tr>
<tr>
<td>Top or bottom closure</td>
<td>Secondary repeated identity or variable coding when the packing line requires it</td>
<td>Placing data where a flap overlaps, rubs or becomes hidden after closing</td>
</tr>
<tr>
<td>Glue flap</td>
<td>Glue and production control only</td>
<td>Placing critical copy, barcode quiet zones or variable data on the glued area</td>
</tr>
<tr>
<td>Internal panels</td>
<td>Only approved internal information or production marks</td>
<td>Assuming inside printing is acceptable without regulatory and migration review</td>
</tr>
</tbody>
</table>
<p>Panel dimensions should be measured after the structural design is confirmed. The nominal panel width is not the same as the safe information area. Crease allowances, cut tolerance, glue width, locking tabs and print-to-cut variation reduce the usable space.</p>

<!-- IMAGE_SLOT_2 -->
<h2>Design for Readability at Finished Size</h2>
<p>Artwork that appears clear at 200% zoom may fail when printed, folded and viewed under ordinary lighting. Readability should therefore be assessed on a full-size proof and a folded physical sample.</p>
<h3>Group Critical Information Instead of Scattering It</h3>
<p>The most important identifying information should be easy to locate and should not be interrupted by decorative copy, logos, background text or unrelated graphics. When the package is small, related information should still be grouped logically rather than distributed randomly across every available flap.</p>
<h3>Measure the Printed Character, Not Only the Software Point Size</h3>
<p>Two fonts set to the same point size can produce different lowercase heights, stroke widths and readability. The project specification should therefore consider the actual printed character height, font weight, line spacing and printing process.</p>

<p>A condensed typeface may technically fit more words but can reduce legibility. The same problem occurs when designers use extensive capital letters, italics, thin strokes or long lines of tightly spaced text.</p>
<h3>Protect Contrast and White Space</h3>
<p>Small regulatory copy should normally sit on a controlled, high-contrast background. Patterns, photography, metallic effects and low-contrast color combinations can make text harder to read even when the font size has not changed.</p>

<p>White space is functional. It separates information groups, makes headings easier to locate and prevents a dense carton from becoming one continuous text block.</p>
<h3>Review Finishing Behind Important Text</h3>
<p>Gloss lamination, foil, metallic paper and highly reflective coatings can create glare. Embossing and debossing can distort characters. Spot UV may change contrast or interfere with later coding if it extends into the wrong area.</p>

<p>The finishing map should therefore identify:</p>
<ul>
<li>Areas where small text must remain free from decorative effects.</li>
<li>Areas reserved for machine-readable codes.</li>
<li>Areas that will receive variable inkjet or laser coding.</li>
<li>Areas that may be covered by a seal or overlabel.</li>
<li>Areas used for Braille embossing.</li>
</ul>
<h3>Do Not Use Color as the Only Variant Identifier</h3>
<p>Color can support differentiation between strengths or pack sizes, but it should not be the only cue. Product name, strength, form and pack count should remain clearly distinguishable in text because color appearance can change with lighting, printing tolerance and visual ability.</p>
<h3>When the Carton Is Too Small, Redesign the System</h3>
<p>Shrinking the text should not be the first solution to an overcrowded carton. The project team should consider:</p>
<ul>
<li>Increasing one or more carton panels.</li>
<li>Changing the folding-carton structure.</li>
<li>Moving approved supporting information to the leaflet where permitted.</li>
<li>Reducing non-essential branding or decorative content.</li>
<li>Separating languages more clearly.</li>
<li>Using an approved local overlabel strategy.</li>
</ul>
<p>The final approach must be confirmed by the responsible regulatory team. The packaging supplier can assess manufacturability but cannot decide which controlled information may be removed.</p>
<h2>Prepare Barcode and Machine-Readable Data as a Technical Package</h2>
<p>A barcode should not be supplied to the packaging manufacturer as a screenshot inside a presentation file. The prepress team needs an approved technical package that explains both the symbol and the data behind it.</p>

<p>The barcode package should include:</p>
<ul>
<li>The required carrier, such as an approved linear or two-dimensional symbol.</li>
<li>The exact encoded data or the controlled source from which it will be generated.</li>
<li>Any required human-readable information.</li>
<li>The approved physical dimensions and magnification.</li>
<li>The required quiet zone or clear area.</li>
<li>The intended orientation on the carton.</li>
<li>The print color and background requirements.</li>
<li>The verification method and acceptance target.</li>
<li>Whether the code is static, batch-variable or unique to each pack.</li>
<li>The party responsible for generating and approving production data.</li>
</ul>
<h3>Choose a Flat, Stable Print Area</h3>
<p>The code should remain completely inside one usable panel whenever possible. It should not cross:</p>
<ul>
<li>A crease or cut line.</li>
<li>A glue seam.</li>
<li>A locking tab.</li>
<li>A strongly curved or damaged edge.</li>
<li>An embossed or debossed area.</li>
<li>Foil stamping or an uncontrolled reflective finish.</li>
<li>A tamper seal or dispensing-label zone.</li>
</ul>
<p>The clear area around the symbol is part of the barcode specification. It should not be treated as unused space that can later be filled with a background pattern or small print.</p>
<h3>Separate Product Identification from Printer Control Codes</h3>
<p>A pharmaceutical carton may contain several machine-readable marks. A product-identification barcode, serialized identifier and internal print-control code perform different jobs. One should not be substituted for another without approval from the data owner.</p>

<p>The artwork brief should label each code by function so the converter does not remove, resize or duplicate the wrong symbol during prepress.</p>

<!-- IMAGE_SLOT_3 -->
<h2>Reserve a Real Production Zone for Batch and Expiry Information</h2>
<p>A box marked “leave space for batch and expiry” is not production-ready. The reserved area must be defined according to the longest expected data, the coding technology and the inspection system used on the packing line.</p>
<table>
<thead>
<tr>
<th>Variable-data specification</th>
<th>What should be confirmed</th>
</tr>
</thead>
<tbody>
<tr>
<td>Data fields</td>
<td>Batch or lot, expiry, serial number and any additional approved variable field</td>
</tr>
<tr>
<td>Format</td>
<td>Approved prefixes, date format, separators and order of information</td>
</tr>
<tr>
<td>Maximum length</td>
<td>Maximum number of characters for each field, including spaces and prefixes</td>
</tr>
<tr>
<td>Print technology</td>
<td>Inkjet, laser, thermal transfer, offline printing or another validated process</td>
</tr>
<tr>
<td>Print direction</td>
<td>Orientation of the carton as it passes through the coding equipment</td>
</tr>
<tr>
<td>Surface</td>
<td>Board, ink, varnish or coating condition required for adhesion and contrast</td>
</tr>
<tr>
<td>Clear zone</td>
<td>Area that must remain free from graphics, foil, embossing and structural interference</td>
</tr>
<tr>
<td>Inspection</td>
<td>Camera, scanner or manual inspection method and rejection criteria</td>
</tr>
<tr>
<td>Durability</td>
<td>Required resistance to rubbing, handling and contact with adjacent cartons</td>
</tr>
</tbody>
</table>
<p>The area should be tested on the actual printed and coated paperboard. A code that adheres to an unprinted sample may smear, fade or lose contrast when printed over a dark ink, varnish or laminate.</p>
<h3>Use the Longest Data in the Artwork Trial</h3>
<p>The proof should not use an unrealistically short sample such as a four-character batch number when the production system may need a much longer value. A representative test can use controlled placeholders such as:</p>
<ul>
<li><strong>LOT [maximum approved character count]</strong></li>
<li><strong>EXP [approved market format]</strong></li>
<li><strong>SERIAL [maximum approved data length]</strong></li>
</ul>
<p>These placeholders describe the required space without prescribing the final legal format.</p>
<h3>Test the Code After Folding and Packing</h3>
<p>Variable data should be checked after the carton has passed through printing, die-cutting, folding, gluing, coding and packing. The final review should confirm that the data remain visible, readable and undamaged after normal handling.</p>
<h2>Plan Braille, Tamper Evidence and Overlabels Before Locking the Dieline</h2>
<p>These features affect the physical carton and cannot be added safely at the end of the artwork process.</p>
<h3>Braille Preparation</h3>
<p>Where Braille is required for the target market, the packaging supplier should receive:</p>
<ul>
<li>The exact approved Braille text.</li>
<li>The required language and Braille standard.</li>
<li>The embossing orientation.</li>
<li>The approved panel location.</li>
<li>The permitted relationship between Braille and printed information.</li>
<li>Any dot-geometry or inspection requirement supplied by the responsible technical team.</li>
</ul>
<p>Board thickness, grain direction, coating and embossing pressure can affect Braille formation. A physical sample should be approved rather than relying only on a digital artwork layer.</p>
<h3>Tamper-Evident Feature</h3>
<p>The artwork and structural brief should show whether the carton will use a label, glued flap, perforation, tear strip or another approved tamper-evident system. The brief should state the seal size, application position, opening method and areas that must remain free from varnish or print.</p>
<h3>Dispensing Label or Local Overlabel</h3>
<p>If a pharmacy label, market sticker or reimbursement label may be applied, reserve a flat and clearly defined area. The applied label must not unintentionally cover product identity, machine-readable codes, variable data, Braille or other approved information.</p>

<p>The exact requirement and area must be confirmed for the destination market and distribution channel.</p>

<!-- IMAGE_SLOT_4 -->
<h2>Control Artwork Versions and Changes</h2>
<p>Pharmaceutical cartons should be managed as controlled packaging components. Every artwork file should have a unique identity and a traceable approval history.</p>

<p>A practical artwork record includes:</p>
<ul>
<li>Artwork number and revision.</li>
<li>Product and SKU identification.</li>
<li>Market and language set.</li>
<li>Dieline version.</li>
<li>Content-master version.</li>
<li>Barcode or serialization data reference.</li>
<li>Color and finishing specification.</li>
<li>Date of regulatory approval.</li>
<li>Date of packaging and print approval.</li>
<li>Reason for the revision.</li>
<li>Superseded artwork reference.</li>
</ul>
<h3>Red Flags Before Releasing Artwork</h3>
<ul>
<li>The barcode was copied from a previous SKU without written confirmation.</li>
<li>The batch and expiry area has no maximum character length.</li>
<li>Several strengths use the same layout and are differentiated only by color.</li>
<li>A designer translated or abbreviated controlled copy.</li>
<li>The artwork contains critical text on a crease or glue flap.</li>
<li>The final finishing layer overlaps a barcode or coding area.</li>
<li>Braille is shown visually but has not been structurally tested.</li>
<li>The folded carton has never been reviewed at actual size.</li>
<li>The artwork filename is being used as the only version-control system.</li>
<li>The approved leaflet does not fit the final carton structure.</li>
</ul>
<h2>Approve More Than a Digital PDF</h2>
<p>A screen proof verifies content placement but cannot demonstrate every production risk. A robust approval process uses several review levels.</p>
<table>
<thead>
<tr>
<th>Approval stage</th>
<th>What it verifies</th>
</tr>
</thead>
<tbody>
<tr>
<td>Content comparison</td>
<td>All controlled text and data match the approved source</td>
</tr>
<tr>
<td>One-to-one print proof</td>
<td>Actual character size, hierarchy, line spacing, contrast and panel fit</td>
</tr>
<tr>
<td>Structural white sample</td>
<td>Carton dimensions, flap movement, leaflet fit and pack assembly</td>
</tr>
<tr>
<td>Printed folded sample</td>
<td>Color, finishing, barcode placement, variable-data area and Braille interaction</td>
</tr>
<tr>
<td>Barcode verification</td>
<td>Symbol dimensions, clear area, contrast and scan performance</td>
</tr>
<tr>
<td>Variable-code trial</td>
<td>Ink adhesion, maximum data length, readability and camera inspection</td>
</tr>
<tr>
<td>Packing-line trial</td>
<td>Carton orientation, coding position, closing, sealing and rejection controls</td>
</tr>
</tbody>
</table>
<p>The project team should sign off the production-relevant sample that reflects the approved board, inks, coatings, finishing and coding process. Approving only an uncoated digital mock-up leaves important risks unresolved.</p>
<h2>Information to Send with a Pharmaceutical Carton RFQ</h2>
<p>A useful quotation request should contain more than box dimensions and quantity. Prepare the following package:</p>
<ul>
<li>Finished carton dimensions and product arrangement.</li>
<li>Primary-container or blister dimensions.</li>
<li>Leaflet size, fold pattern and thickness.</li>
<li>Approved or draft dieline status.</li>
<li>Destination market and languages.</li>
<li>Approved content master or clearly marked draft content.</li>
<li>SKU and market matrix.</li>
<li>Barcode and serialization specification.</li>
<li>Variable-data fields, formats and maximum lengths.</li>
<li>Braille, overlabel and tamper-evident requirements.</li>
<li>Paperboard and printing requirements.</li>
<li>Coating and finishing restrictions.</li>
<li>Required physical samples and verification reports.</li>
<li>Packing-line orientation and coding method.</li>
<li>Quantity by SKU.</li>
</ul>
<p>Providing this information allows a <a href="https://hopgiayvpn.com/custom-packaging-boxes-manufacturer/">packaging boxes manufacturer</a> to assess the dieline, printable area, coding zones, finishing compatibility and sample requirements without inventing regulatory content. The final artwork should still be reviewed and approved by the responsible regulatory, quality and serialization teams before production.</p>
HTML;
}
