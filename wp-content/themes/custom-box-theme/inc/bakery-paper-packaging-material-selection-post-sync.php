<?php
/**
 * Syncs the bakery paper packaging material selection guide draft and images.
 */

add_action('admin_init', 'custom_box_sync_bakery_paper_packaging_material_selection_post');
add_action('admin_notices', 'custom_box_bakery_paper_packaging_material_selection_admin_notice');

function custom_box_sync_bakery_paper_packaging_material_selection_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }
    if ('2026-07-13-v1' === get_option('custom_box_bakery_paper_packaging_material_selection_sync_version')) {
        return;
    }

    $post_id = custom_box_upsert_bakery_paper_packaging_material_selection_post();

    if (is_wp_error($post_id)) {
        update_option('custom_box_bakery_paper_packaging_material_selection_missing_post', $post_id->get_error_message(), false);
        return;
    }

    update_option('custom_box_bakery_paper_packaging_material_selection_missing_post', '', false);
    update_option('custom_box_bakery_paper_packaging_material_selection_sync_version', '2026-07-13-v1', false);
}

function custom_box_upsert_bakery_paper_packaging_material_selection_post()
{
    $post_data = custom_box_bakery_paper_packaging_material_selection_post_data();
    $post = custom_box_find_bakery_paper_packaging_material_selection_post($post_data['slug'], $post_data['title']);
    $content = custom_box_bakery_paper_packaging_material_selection_content();

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
            || false === strpos($existing_content, 'vpn-bakery-paper-packaging-material-selection-image:')
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
    custom_box_sync_bakery_paper_packaging_material_selection_terms($post_id, $post_data);
    custom_box_sync_bakery_paper_packaging_material_selection_meta($post_id, $post_data);
    custom_box_sync_bakery_paper_packaging_material_selection_images($post_id);

    update_post_meta($post_id, '_custom_box_bakery_paper_packaging_material_selection_synced', current_time('mysql'));

    return $post_id;
}

function custom_box_bakery_paper_packaging_material_selection_post_data(): array
{
    return array(
        'title'           => 'How to Choose Paper Packaging Materials for Bakery Products',
        'slug'            => 'how-to-choose-bakery-packaging-materials',
        'seo_title'       => 'How to Choose Paper Packaging Materials for Bakery Products',
        'seo_description' => 'Choose bakery paper packaging materials with confidence. Compare kraft paper, paperboard, window boxes, inserts, grease concerns and transport protection before production.',
        'focus_keyword'   => 'how to choose bakery packaging materials',
        'excerpt'         => 'Learn how to choose paper packaging materials for bakery products, including kraft paper, paperboard, window boxes, inserts, grease concerns and transport protection.',
        'category'        => array(
            'name' => 'Packaging Guides',
            'slug' => 'packaging-guides',
        ),
        'tags'            => array(
            'bakery packaging',
            'paper packaging',
            'kraft bakery box',
            'bakery window box',
            'packaging materials',
        ),
    );
}

function custom_box_bakery_paper_packaging_material_selection_images(): array
{
    return array(
        'featured' => array(
            'base'    => 'bakery-packaging-material-selection-guide',
            'alt'     => 'Bakery packaging material selection guide with kraft boxes, paperboard window boxes and paper inserts',
            'title'   => 'Bakery Packaging Material Selection Guide',
            'caption' => 'Bakery packaging materials should match product condition, display needs and transport handling.',
        ),
        'slot_1'   => array(
            'base'    => 'bakery-material-options-kraft-paperboard',
            'alt'     => 'Kraft and paperboard bakery packaging material options on a packaging workbench',
            'title'   => 'Bakery Material Options: Kraft and Paperboard',
            'caption' => 'Kraft and paperboard serve different bakery packaging roles depending on print, grease and structure needs.',
        ),
        'slot_2'   => array(
            'base'    => 'bakery-window-box-structure-detail',
            'alt'     => 'Bakery paper window box structure with clear window and strong paper frame',
            'title'   => 'Bakery Window Box Structure Detail',
            'caption' => 'Window size should improve product visibility without weakening the paper frame.',
        ),
        'slot_3'   => array(
            'base'    => 'bakery-insert-protection-comparison',
            'alt'     => 'Paper inserts and dividers protecting cupcakes and bakery gift products inside paper boxes',
            'title'   => 'Bakery Insert Protection Comparison',
            'caption' => 'Inserts help reduce movement, tilting and surface damage during handling.',
        ),
        'slot_4'   => array(
            'base'    => 'bakery-packaging-transport-qc-check',
            'alt'     => 'Bakery packaging transport and QC check with sample boxes, inserts and master carton',
            'title'   => 'Bakery Packaging Transport QC Check',
            'caption' => 'Sample approval should include real product fit, stacking and transport packing.',
        ),
    );
}

function custom_box_find_bakery_paper_packaging_material_selection_post(string $slug, string $title): ?WP_Post
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

function custom_box_sync_bakery_paper_packaging_material_selection_terms(int $post_id, array $post_data): void
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

function custom_box_sync_bakery_paper_packaging_material_selection_meta(int $post_id, array $post_data): void
{
    update_post_meta($post_id, 'rank_math_title', $post_data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $post_data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $post_data['focus_keyword']);
}

function custom_box_sync_bakery_paper_packaging_material_selection_images(int $post_id): void
{
    $images = custom_box_bakery_paper_packaging_material_selection_images();
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach ($images as $key => $image) {
        $attachment_id = custom_box_find_bakery_paper_packaging_material_selection_attachment($image['base']);

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
        $marker = '<!-- vpn-bakery-paper-packaging-material-selection-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_bakery_paper_packaging_material_selection_figure($attachment_id, $image);
        $slot = '<!-- IMAGE_SLOT_' . $slot_number . ' -->';
        $slot_pattern = '/<span\b[^>]*>\s*' . preg_quote($slot, '/') . '\s*<\/span>/i';
        $marker_pattern = '/' . preg_quote($marker, '/') . '\s*<figure\b.*?<\/figure>/is';

        if (false !== strpos($content, $marker)) {
            $content = preg_replace($marker_pattern, $figure, $content, 1);
        } elseif (false !== strpos($content, $slot)) {
            $content = str_replace($slot, $figure, $content);
        } elseif (preg_match($slot_pattern, $content)) {
            $content = preg_replace($slot_pattern, $figure, $content, 1);
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

    update_option('custom_box_bakery_paper_packaging_material_selection_missing_images', $missing_images, false);
    update_option('custom_box_bakery_paper_packaging_material_selection_missing_slots', $missing_slots, false);
}

function custom_box_find_bakery_paper_packaging_material_selection_attachment(string $base): int
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

function custom_box_bakery_paper_packaging_material_selection_figure(int $attachment_id, array $image): string
{
    return sprintf(
        '<figure><img src="%s" alt="%s" style="width:100%%; height:auto;" loading="lazy" decoding="async"><figcaption>%s</figcaption></figure>',
        esc_url(wp_get_attachment_url($attachment_id)),
        esc_attr($image['alt']),
        esc_html($image['caption'])
    );
}

function custom_box_bakery_paper_packaging_material_selection_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing_post = (string) get_option('custom_box_bakery_paper_packaging_material_selection_missing_post', '');
    $missing_images = (array) get_option('custom_box_bakery_paper_packaging_material_selection_missing_images', array());
    $missing_slots = (array) get_option('custom_box_bakery_paper_packaging_material_selection_missing_slots', array());

    if ('' === $missing_post && empty($missing_images) && empty($missing_slots)) {
        return;
    }

    echo '<div class="notice notice-warning"><p><strong>Bakery paper packaging material selection post sync:</strong> ';

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

    echo implode(' | ', $messages);
    echo '</p></div>';
}

function custom_box_bakery_paper_packaging_material_selection_content(): string
{
    $manufacturer_link = '<a href="' . esc_url(home_url('/custom-packaging-boxes-manufacturer/')) . '">packaging boxes manufacturer</a>';

    $content = <<<'HTML'
<p>Choosing paper packaging materials for bakery products is a practical production decision, not a style exercise. A cookie box, a cupcake carton, a pastry window box, and a cake transport box can all look similar in a mockup and still need very different paper materials once grease, moisture, weight, display, and delivery are considered.</p>
<p>The safest starting point is the bakery product itself. Decide whether the item is dry or oily, light or heavy, single-piece or multi-piece, shelf-display or delivery-focused, and whether the paper is the primary package or only the branded outer layer. Once that is clear, the choice between kraft paper, paperboard, coated stock, corrugated board, inserts, and window structures becomes much easier to justify.</p>
<p>This guide explains the material choices that matter most for bakery buyers: product behavior, display, inserts, grease and moisture, transport packing, and RFQ preparation.</p>

<h2>Start With the Bakery Product, Not the Paper Grade</h2>
<p>The first mistake many buyers make is choosing paper because it looks premium on a render. Bakery packaging materials should be selected from the real product condition. A dry cookie pack, a butter pastry box, a cupcake gift box, and a chilled dessert carton do not create the same risks.</p>
<p>Before you choose the material, confirm these points:</p>
<ul>
  <li>Product type: bread, cake, cupcake, cookie, pastry, tart, donut, or assorted gift set.</li>
  <li>Product condition: dry, oily, chilled, freshly baked, or ready for delivery.</li>
  <li>Weight and size: single item, multi-piece set, or stacked product layout.</li>
  <li>Display need: retail shelf, bakery counter, gift presentation, or e-commerce shipment.</li>
  <li>Handling route: hand carry, local delivery, courier shipping, or export packing.</li>
  <li>Whether paper touches the bakery product directly or acts only as outer packaging should be confirmed for the target market and project specification.</li>
</ul>
<p>A dry biscuit line may only need a clean paperboard carton. A rich pastry with butter or cream may need a better barrier approach. A bakery gift box may need structure and insert control more than print complexity. Material selection should follow those product realities, not the other way around.</p>

<!-- IMAGE_SLOT_1 -->

<h2>Compare the Main Bakery Paper Material Families</h2>
<p>Most bakery packaging projects are built from a small group of paper material families. The right choice depends on how much display value, stiffness, print quality, and transport protection the box needs.</p>

<table>
<thead>
<tr>
<th>Material</th>
<th>Best for</th>
<th>Main strength</th>
<th>What to watch</th>
</tr>
</thead>
<tbody>
<tr>
<td>Kraft paper</td>
<td>Natural-looking bakery boxes, simple branding, dry baked goods</td>
<td>Natural appearance and simple material story</td>
<td>Color output, grease marks, and print contrast should be checked carefully</td>
</tr>
<tr>
<td>Paperboard</td>
<td>Cookie cartons, cupcake boxes, retail bakery boxes</td>
<td>Good all-round balance of print and structure</td>
<td>Thickness and folding behavior need to match product weight</td>
</tr>
<tr>
<td>Coated paperboard</td>
<td>Premium display boxes, brighter artwork, detailed graphics</td>
<td>Smoother print surface and stronger visual finish</td>
<td>Scuffing, gloss level, and surface reflection should be reviewed</td>
</tr>
<tr>
<td>Corrugated board</td>
<td>Transport cartons, heavier cake boxes, delivery packs</td>
<td>Better compression and shipping strength</td>
<td>Bulk, print look, and shelf presentation are less refined</td>
</tr>
<tr>
<td>Specialty wrap paper</td>
<td>Premium rigid bakery gift boxes</td>
<td>Better tactile feel and brand effect</td>
<td>Cost, crease behavior, and finish compatibility should be tested</td>
</tr>
</tbody>
</table>

<p>Kraft paper is often chosen for a more natural bakery story, but it is not the answer to every product. White or coated paperboard may be a better fit if the brand needs cleaner color reproduction or sharper graphics. Corrugated board belongs more in transport or outer packing than in premium shelf presentation, unless the packaging concept specifically needs that stronger structure.</p>

<h2>Use Window Boxes When Display Matters, But Test the Frame</h2>
<p>Bakery window boxes are useful when shoppers need to see the product before buying. The window can help showcase a cake slice, cupcake arrangement, pastry color, or gift assortment. But every window cut changes the strength of the paper frame, so the structure needs to be evaluated together with the display goal.</p>
<p>The frame should still hold its shape after the window is added. Window size, placement, and film choice should all be checked because they affect visibility, stiffness, fogging, and the amount of remaining board around the opening. For chilled or warm bakery products, condensation should also be tested in the real handling environment.</p>

<!-- IMAGE_SLOT_2 -->

<p>For bakery projects, a window is only useful if it improves sell-through without making the box weak or hard to pack. That is why the correct box material and the correct window layout should be decided together.</p>

<h2>Use Inserts and Dividers to Control Movement</h2>
<p>Bakery inserts are often the difference between a product that arrives neatly and a product that shifts in transit. Inserts help hold cupcakes upright, keep pastry pieces separated, and prevent gift items from tilting or rubbing against each other. For heavier or more delicate bakery assortments, the insert is often as important as the outer carton.</p>
<p>Paper inserts, paperboard dividers, folded trays, and simple partitions can all work, depending on the product shape. The insert should be selected according to the real filled product, not an idealized sketch. If the insert is too loose, the product moves. If it is too tight, packing becomes slow and the box can deform.</p>
<ul>
  <li>Check whether the product moves when the box is gently shaken.</li>
  <li>Check whether the product tilts when the box is lifted from one side.</li>
  <li>Check whether the product can be removed without damaging toppings, glaze, or surface decoration.</li>
  <li>Check whether the insert is still practical for the packing team at production speed.</li>
</ul>

<!-- IMAGE_SLOT_3 -->

<h2>Plan for Grease and Moisture Before Production</h2>
<p>Grease and moisture are two of the biggest reasons bakery packaging fails in real use. Butter, cream, warm steam, and condensation can all change how paper looks and behaves after the box has been assembled. A surface that looks fine in a mockup may soften, stain, or lose stiffness during actual use.</p>
<p>When the bakery item is oily or warm, the buyer should confirm how much direct contact the paper will have with the product and whether the chosen material can handle the expected use window. For many projects, the answer is not to force one paper grade to solve everything, but to combine the right box structure with the right inner packaging and the right transport method.</p>
<p>Finishes such as matte, gloss, or soft-touch can change the brand feel, but they do not replace barrier testing. Grease behavior and moisture behavior should be reviewed with real samples under the same conditions the bakery product will face after packing.</p>

<h2>Choose Transport Packing as Part of the Material Decision</h2>
<p>Bakery packaging is judged not only on shelf appearance but also on how it behaves in a delivery route. A retail box that looks good on a counter may need a stronger outer carton, better stacking logic, or tighter insert control if it also has to travel through courier handling or export freight.</p>
<p>The buyer should review the retail box, the insert, and the master carton together. If the outer carton is too weak, a good retail box can still get crushed. If the outer carton is too large, the product can move and lose shape. Transport packing should therefore be treated as a separate design layer, not as an afterthought.</p>

<ul>
  <li>Check stacking strength for the filled retail box and the master carton.</li>
  <li>Check whether the bakery item is shipped alone or inside a second protective carton.</li>
  <li>Check whether the box still closes cleanly after repeated packing.</li>
  <li>Check whether corners, window edges, and glued areas stay intact during handling.</li>
  <li>Check whether the chosen board thickness is practical for the actual delivery route.</li>
</ul>

<!-- IMAGE_SLOT_4 -->

<h2>Use a Simple Sample Approval Checklist</h2>
<p>A bakery packaging sample should be approved as a working production sample, not just as a design object. The sample needs to prove that the box fits the bakery product, survives handling, and still looks acceptable after packing and transport.</p>
<ul>
  <li>Confirm fit with the real product, not a generic sample.</li>
  <li>Confirm the box closes without forcing the board or window frame.</li>
  <li>Confirm the insert holds the product steady during lifting and shaking.</li>
  <li>Confirm print, labels, and barcode areas remain readable.</li>
  <li>Confirm the surface still looks acceptable after light rubbing and stacking.</li>
  <li>Confirm the master carton arrangement supports the retail box shape.</li>
</ul>
<p>If the sample fails, the feedback should be practical and specific. Comments such as "increase divider height", "reduce window width", or "use a stiffer board" are much more useful than vague approval notes.</p>

<h2>Prepare a Clear RFQ Before Asking for a Quote</h2>
<p>A clear RFQ helps a supplier recommend a realistic material and quote with fewer assumptions. Before contacting a {MANUFACTURER_LINK}, prepare the following details:</p>
<ul>
  <li>Bakery product type, photos, and filled dimensions.</li>
  <li>Product weight and whether it is dry, oily, chilled, or warm.</li>
  <li>Preferred box style: window box, folding carton, insert box, sleeve, or transport carton.</li>
  <li>Whether the paper touches the product directly or only serves as outer packaging.</li>
  <li>Material preference, finish preference, and any structure constraints.</li>
  <li>Printing needs, inside printing, outside printing, or simple logo use.</li>
  <li>Insert, divider, or tray requirement.</li>
  <li>Target market, quantity, delivery route, and artwork status.</li>
</ul>
<p>The more specific the brief, the easier it is for the packaging team to recommend the right material and avoid expensive revisions later.</p>

<h2>When Paper May Not Be Enough</h2>
<p>Paper is useful for many bakery products, but it is not the right answer for every case. If the product is very greasy, very moist, held warm for a long time, or exposed to repeated condensation, paper alone may not deliver the performance the buyer needs.</p>
<p>In those cases, a hybrid solution, a stronger inner pack, or a paper outer box around a sealed product may make more sense. The final decision should be based on the bakery product behavior, the handling route, and the target market requirements rather than a general sustainability claim.</p>

<h2>Final Thought</h2>
<p>How to choose paper packaging materials for bakery products comes down to a few practical questions: what the bakery item is, how much grease or moisture it creates, whether display matters, whether inserts are needed, and how the product will be handled in transit.</p>
<p>When product behavior, material choice, window design, insert fit, stacking, and RFQ details are planned together, bakery packaging becomes easier to sample, quote, and produce with fewer surprises.</p>
HTML;

    return str_replace('{MANUFACTURER_LINK}', $manufacturer_link, $content);
}
