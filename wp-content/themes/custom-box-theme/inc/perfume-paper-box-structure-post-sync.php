<?php
/**
 * Syncs the perfume paper box structure guide draft and images.
 */

add_action('admin_init', 'custom_box_sync_perfume_paper_box_structure_post');
add_action('admin_notices', 'custom_box_perfume_paper_box_structure_admin_notice');

function custom_box_sync_perfume_paper_box_structure_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $post_id = custom_box_upsert_perfume_paper_box_structure_post();

    if (is_wp_error($post_id)) {
        update_option('custom_box_perfume_paper_box_structure_missing_post', $post_id->get_error_message(), false);
        return;
    }

    update_option('custom_box_perfume_paper_box_structure_missing_post', '', false);
}

function custom_box_upsert_perfume_paper_box_structure_post()
{
    $post_data = custom_box_perfume_paper_box_structure_post_data();
    $post = custom_box_find_perfume_paper_box_structure_post($post_data['slug'], $post_data['title']);
    $content = custom_box_perfume_paper_box_structure_content();

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
            || false === strpos($existing_content, 'vpn-perfume-paper-box-structure-image:')
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
    custom_box_sync_perfume_paper_box_structure_terms($post_id, $post_data);
    custom_box_sync_perfume_paper_box_structure_meta($post_id, $post_data);
    custom_box_sync_perfume_paper_box_structure_images($post_id);

    update_post_meta($post_id, '_custom_box_perfume_paper_box_structure_synced', current_time('mysql'));

    return $post_id;
}

function custom_box_perfume_paper_box_structure_post_data(): array
{
    return array(
        'title'           => 'How to Choose Paper Box Structure for Perfume Packaging',
        'slug'            => 'how-to-choose-paper-box-structure-for-perfume-packaging',
        'seo_title'       => 'How to Choose Paper Box Structure for Perfume Packaging',
        'seo_description' => 'Compare perfume paper box structures, inserts, finishing, and QC checks before choosing folding cartons, rigid boxes, drawer boxes, sleeves, or tubes for fragrance packaging.',
        'focus_keyword'   => 'how to choose perfume box structure',
        'excerpt'         => 'Learn how to choose the right paper box structure for perfume packaging by comparing bottle fit, inserts, rigid boxes, folding cartons, finishing, QC checks, and RFQ details.',
        'category'        => array(
            'name' => 'Packaging Guide',
            'slug' => 'packaging-guide',
        ),
        'tags'            => array(
            'Perfume Packaging',
            'Paper Box Structure',
            'Rigid Boxes',
            'Folding Cartons',
            'Packaging Inserts',
            'B2B Packaging',
        ),
    );
}

function custom_box_perfume_paper_box_structure_images(): array
{
    return array(
        'featured' => array(
            'base'    => 'perfume-paper-box-structure-guide',
            'alt'     => 'Paper box structure options for perfume packaging with inserts and rigid box samples',
            'title'   => 'How to Choose Perfume Paper Box Structure',
            'caption' => 'A practical visual guide to choosing perfume box structure, insert support, and finishing direction.',
        ),
        'slot_1'   => array(
            'base'    => 'perfume-bottle-measurement-before-box-structure',
            'alt'     => 'Perfume bottle measurements used to choose paper box structure and insert fit',
            'title'   => 'Perfume Bottle Measurement Before Box Structure',
            'caption' => 'Accurate bottle size, weight, cap height, and shape should guide the box structure decision.',
        ),
        'slot_2'   => array(
            'base'    => 'perfume-box-structure-comparison',
            'alt'     => 'Folding carton rigid box drawer box and sleeve box structure comparison for perfume packaging',
            'title'   => 'Perfume Box Structure Comparison',
            'caption' => 'Different perfume box structures solve different needs around cost, presentation, assembly, and protection.',
        ),
        'slot_3'   => array(
            'base'    => 'perfume-box-insert-support-detail',
            'alt'     => 'Paperboard insert supporting a glass perfume bottle inside a custom box',
            'title'   => 'Perfume Box Insert Support Detail',
            'caption' => 'The insert controls bottle movement, cap clearance, and the final unboxing experience.',
        ),
        'slot_4'   => array(
            'base'    => 'perfume-packaging-sample-qc-check',
            'alt'     => 'QC inspection of perfume paper box sample with insert fit and finishing details',
            'title'   => 'Perfume Packaging Sample QC Check',
            'caption' => 'Sample approval should check fit, closure, insert stability, surface finish, and export packing.',
        ),
    );
}

function custom_box_find_perfume_paper_box_structure_post(string $slug, string $title): ?WP_Post
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

function custom_box_sync_perfume_paper_box_structure_terms(int $post_id, array $post_data): void
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

function custom_box_sync_perfume_paper_box_structure_meta(int $post_id, array $post_data): void
{
    update_post_meta($post_id, 'rank_math_title', $post_data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $post_data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $post_data['focus_keyword']);
}

function custom_box_sync_perfume_paper_box_structure_images(int $post_id): array
{
    $images = custom_box_perfume_paper_box_structure_images();
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach ($images as $key => $image) {
        $attachment_id = custom_box_find_perfume_paper_box_structure_attachment($image['base']);

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
        $marker = '<!-- vpn-perfume-paper-box-structure-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_perfume_paper_box_structure_figure($attachment_id, $image);
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

    update_option('custom_box_perfume_paper_box_structure_missing_images', $missing_images, false);
    update_option('custom_box_perfume_paper_box_structure_missing_slots', $missing_slots, false);

    return array(
        'missing_images' => $missing_images,
        'missing_slots'   => $missing_slots,
    );
}

function custom_box_find_perfume_paper_box_structure_attachment(string $base): int
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

    if ($ids) {
        return (int) $ids[0];
    }

    return custom_box_create_perfume_paper_box_structure_attachment($base);
}

function custom_box_create_perfume_paper_box_structure_attachment(string $base): int
{
    $uploads = wp_get_upload_dir();
    if (empty($uploads['basedir'])) {
        return 0;
    }

    $relative_file = '2026/07/' . $base . '.webp';
    $file_path = trailingslashit($uploads['basedir']) . $relative_file;

    if (!file_exists($file_path)) {
        return 0;
    }

    $attachment_id = wp_insert_attachment(
        array(
            'post_mime_type' => 'image/webp',
            'post_title'     => ucwords(str_replace('-', ' ', $base)),
            'post_name'      => sanitize_title($base),
            'post_status'    => 'inherit',
        ),
        $file_path
    );

    if (is_wp_error($attachment_id) || !$attachment_id) {
        return 0;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';

    $metadata = wp_generate_attachment_metadata((int) $attachment_id, $file_path);
    if (!is_wp_error($metadata) && !empty($metadata)) {
        wp_update_attachment_metadata((int) $attachment_id, $metadata);
    }

    update_post_meta((int) $attachment_id, '_wp_attached_file', $relative_file);

    return (int) $attachment_id;
}

function custom_box_perfume_paper_box_structure_figure(int $attachment_id, array $image): string
{
    return sprintf(
        '<figure><img src="%s" alt="%s" style="width:100%%; height:auto;" loading="lazy" decoding="async"><figcaption>%s</figcaption></figure>',
        esc_url(wp_get_attachment_url($attachment_id)),
        esc_attr($image['alt']),
        esc_html($image['caption'])
    );
}

function custom_box_perfume_paper_box_structure_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing_post = (string) get_option('custom_box_perfume_paper_box_structure_missing_post', '');
    $missing_images = (array) get_option('custom_box_perfume_paper_box_structure_missing_images', array());
    $missing_slots = (array) get_option('custom_box_perfume_paper_box_structure_missing_slots', array());

    if ('' === $missing_post && empty($missing_images) && empty($missing_slots)) {
        return;
    }

    echo '<div class="notice notice-warning"><p><strong>Perfume paper box structure post sync:</strong> ';

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

function custom_box_perfume_paper_box_structure_content(): string
{
    return <<<'HTML'
<p>Choosing a perfume paper box structure is not just a packaging style decision. The right answer usually starts with the bottle, then the insert, then the box structure, then the finishing, and finally the QC sample check. If that order is skipped, the project can look good on screen and still fail in hand.</p>
<p>A fragrance bottle can be slim, heavy, square, round, or highly decorative. The cap may be tall. The atomizer may need clearance. The glass may be delicate. Because of that, the best perfume packaging box is the one that fits the real filled bottle, protects the spray head and cap, and still supports the brand story without overcomplicating production.</p>
<p>This guide explains how to choose paper box structure for perfume packaging in a way that is practical for buyers, sourcing teams, and packaging agencies.</p>

<!-- IMAGE_SLOT_1 -->

<h2>Start With the Bottle Before Choosing the Box Style</h2>
<p>Do not start with the visual mockup. Start with the real filled bottle. Measure the full product, not only the empty glass body, because the cap and spray head often decide whether the box feels safe or too tight.</p>
<ul>
  <li>Bottle body width, depth, and height.</li>
  <li>Filled weight, especially for thick glass bottles and heavy fragrance sets.</li>
  <li>Cap height and total finished height including the atomizer or decorative top.</li>
  <li>Bottle shape: round, square, rectangular, curved, or custom molded.</li>
  <li>Sales channel: retail shelf, gift set, ecommerce, sample kit, or export shipment.</li>
  <li>Whether the project is a single SKU, a family of sizes, or a gift set with multiple items.</li>
</ul>
<p>A 30ml bottle can often work in a clean folding carton. A heavy 50ml or 100ml premium fragrance bottle may need a rigid box and a better insert. A discovery set or PR kit may need a drawer box or sleeve format that creates a more controlled opening experience.</p>

<h2>Compare the Main Structure Families</h2>
<p>Perfume packaging usually relies on a few core paper box families. Each one solves a different balance of cost, presentation, packing speed, and protection.</p>
<table>
<thead>
<tr>
<th>Box structure</th>
<th>Best use</th>
<th>Main strength</th>
<th>Trade-off to check</th>
</tr>
</thead>
<tbody>
<tr>
<td>Folding carton</td>
<td>Single bottles, retail SKUs, volume fragrance lines</td>
<td>Efficient, printable, and ships flat</td>
<td>Needs the right board stiffness and insert support</td>
</tr>
<tr>
<td>Rigid box</td>
<td>Premium fragrance, launch kits, gift sets</td>
<td>Strong structure and higher perceived value</td>
<td>Higher unit cost and more shipping volume</td>
</tr>
<tr>
<td>Drawer box</td>
<td>Discovery sets, PR kits, multi-piece fragrance packs</td>
<td>Good unboxing flow and organized presentation</td>
<td>Drawer fit and pull strength must be controlled</td>
</tr>
<tr>
<td>Sleeve box</td>
<td>Minimalist perfume lines, secondary packaging, sampler sleeves</td>
<td>Simple appearance with lower material use</td>
<td>Usually needs an inner tray or strong insert</td>
</tr>
<tr>
<td>Paper tube</td>
<td>Round bottles, travel sets, specialty presentation</td>
<td>Distinctive form and strong shelf recognition</td>
<td>Diameter and cap clearance must be exact</td>
</tr>
<tr>
<td>Mailer box</td>
<td>Ecommerce fragrance, sample bundles, shipping packs</td>
<td>Better protection for direct shipping</td>
<td>Not always the best choice for premium shelf display</td>
</tr>
</tbody>
</table>
<p>If you want to compare premium structures against efficient retail formats, review <a href="https://hopgiayvpn.com/products/rigid-boxes/">rigid boxes</a> and <a href="https://hopgiayvpn.com/products/folding-carton-boxes/">folding carton boxes</a>. If the brief needs trays or dividers, <a href="https://hopgiayvpn.com/products/packaging-accessories/">packaging accessories</a> can help define the insert discussion.</p>

<!-- IMAGE_SLOT_2 -->

<h2>Use a Decision Matrix From Bottle to Box</h2>
<p>A simple way to avoid bad structure choices is to start with the bottle profile and map it to the packaging job it needs to do.</p>
<table>
<thead>
<tr>
<th>Bottle or brief</th>
<th>Start with</th>
<th>Insert</th>
<th>Finish to test</th>
<th>Why</th>
</tr>
</thead>
<tbody>
<tr>
<td>Single 30ml glass perfume bottle for retail</td>
<td>Folding carton</td>
<td>Paperboard or corrugated neck support</td>
<td>Matte or gloss lamination</td>
<td>Efficient for volume while keeping the front panel clean</td>
</tr>
<tr>
<td>50ml premium fragrance launch</td>
<td>Rigid box</td>
<td>Rigid tray or molded paper insert</td>
<td>Soft-touch, foil, or embossing</td>
<td>Signals higher value and holds the bottle more securely</td>
</tr>
<tr>
<td>Two-bottle gift set or discovery kit</td>
<td>Drawer box</td>
<td>Divider or fitted tray</td>
<td>Matte plus selective foil</td>
<td>Keeps the set organized and improves unboxing flow</td>
</tr>
<tr>
<td>Cylindrical bottle or travel spray</td>
<td>Paper tube or sleeve plus tray</td>
<td>Tight round cutout or internal sleeve</td>
<td>Printed wrap with scuff-resistant finish</td>
<td>The geometry should match the round format instead of forcing a square carton</td>
</tr>
<tr>
<td>Ecommerce sample bundle</td>
<td>Mailer or reinforced carton</td>
<td>Internal dividers</td>
<td>Scratch-resistant lamination</td>
<td>Shipping protection matters more than shelf drama</td>
</tr>
</tbody>
</table>
<p>The short rule is simple: if the bottle is fragile, let the insert do the protection work. If the brand is premium, let the structure support the presentation. If the product ships directly, let the outer pack reduce damage before it reaches the customer.</p>

<!-- IMAGE_SLOT_3 -->

<h2>Size the Insert Around the Real Filled Bottle</h2>
<p>Insert design should be based on the actual bottle dimensions, not only the artwork mockup. A perfume bottle may look simple, but the cap, neck, shoulder, and atomizer all create small clearances that matter in production.</p>
<p>For a glass fragrance bottle, the insert should control movement around the body and keep the cap from rubbing the top panel. For a set of samples, the tray should stop the items from colliding. For a drawer box, the inner tray must be tight enough to feel secure but not so tight that the customer thinks the box is poorly assembled.</p>
<p>Typical insert choices include paperboard inserts, folded paper trays, corrugated inserts, molded paper trays, and dividers. The right choice depends on bottle weight, fragility, and how the packing team will load the product.</p>
<ul>
  <li>Check whether the bottle moves when the box is gently shaken.</li>
  <li>Check whether the cap or spray head touches the top panel.</li>
  <li>Check whether the bottle can be inserted and removed without scraping the label.</li>
  <li>Check whether the insert still works after several units are packed in sequence.</li>
  <li>Check whether the tray keeps the bottle aligned for retail display and shipping.</li>
</ul>
<p>If the insert only works in a render, it is not ready. A real filled bottle should sit cleanly, stay stable, and still look premium when the box is opened.</p>

<h2>Choose Paper Material for Weight and Print Behavior</h2>
<p>Material changes how the box feels in the hand and how the print behaves on the surface. A perfume box can use white coated board, ivory board, greyboard wrapped with art paper, kraft board, textured paper, or micro-flute board depending on the product and shipping route.</p>
<ul>
  <li><strong>White coated board:</strong> good for clean color, fine typography, and retail-friendly print.</li>
  <li><strong>Ivory board:</strong> good for stable folding cartons and clear brand graphics.</li>
  <li><strong>Greyboard wrapped with art paper:</strong> common for rigid boxes, drawer boxes, and premium sets.</li>
  <li><strong>Kraft board:</strong> good for natural or minimalist positioning, but brand color should be proofed carefully.</li>
  <li><strong>Textured paper:</strong> good for tactile premium packaging, but fine details and foil need testing.</li>
  <li><strong>Micro-flute or corrugated board:</strong> useful when the box needs more shipping strength.</li>
</ul>
<p>If the design depends on a specific color mood, request the proof on the same material and finish that will be used in production. The same artwork can look very different on kraft, coated board, or wrapped rigid stock.</p>

<h2>Use Finishing to Support the Brand, Not Replace the Structure</h2>
<p>Finishing should make the perfume packaging clearer and more intentional, not hide a weak structure. Matte lamination can create a soft modern surface. Gloss can make color brighter. Soft-touch can feel premium. Foil stamping, embossing, debossing, and spot UV can add focus when used with restraint.</p>
<table>
<thead>
<tr>
<th>Finishing option</th>
<th>What it signals</th>
<th>Good use</th>
<th>What to test</th>
</tr>
</thead>
<tbody>
<tr>
<td>Matte lamination</td>
<td>Calm, premium, controlled</td>
<td>Minimal fragrance packaging and soft luxury positioning</td>
<td>Scuff resistance on dark surfaces</td>
</tr>
<tr>
<td>Gloss lamination</td>
<td>Bright, retail-visible, vivid</td>
<td>High-color artwork and strong shelf presence</td>
<td>Whether the look becomes too busy</td>
</tr>
<tr>
<td>Soft-touch lamination</td>
<td>Refined and tactile</td>
<td>Premium gift sets and launch boxes</td>
<td>Fingerprint and scratch behavior</td>
</tr>
<tr>
<td>Foil stamping</td>
<td>Luxury accent and brand emphasis</td>
<td>Logo, name, or small decorative highlight</td>
<td>Fine lines and very small text</td>
</tr>
<tr>
<td>Embossing or debossing</td>
<td>Depth and tactile detail</td>
<td>Seal, logo, or brand mark</td>
<td>Paper thickness and registration</td>
</tr>
<tr>
<td>Spot UV</td>
<td>Contrast and shine</td>
<td>Pattern, icon, or selective logo emphasis</td>
<td>Alignment on the printed surface</td>
</tr>
</tbody>
</table>
<p>For perfume packaging, it is often better to choose one or two finishes and execute them well than to stack several effects on top of a box that is already hard to produce.</p>

<h2>QC Sample Review Before Production</h2>
<p>The sample is where structure, insert, finish, and bottle fit become real. A good QC review should tell you whether the box works in hand, not only in a digital mockup.</p>
<ul>
  <li>Check that the box closes cleanly and opens without tearing the edges.</li>
  <li>Check that the bottle does not move too much inside the insert.</li>
  <li>Check that the cap and atomizer have enough clearance.</li>
  <li>Check that the print color looks acceptable under normal light.</li>
  <li>Check that foil, embossing, or spot UV is aligned and clean.</li>
  <li>Check that small text, barcode areas, and product details remain readable.</li>
  <li>Check that the surface can handle rubbing, stacking, and packing pressure.</li>
  <li>Check whether the retail box still looks good after outer packing is applied.</li>
</ul>

<!-- IMAGE_SLOT_4 -->

<h2>Common Mistakes to Avoid</h2>
<p>Most perfume box problems are created before the first sample is approved. They are usually not expensive to avoid if the team catches them early.</p>
<ul>
  <li>Choosing the box style before measuring the filled bottle.</li>
  <li>Using a rigid box for every project, even when the product does not need it.</li>
  <li>Ignoring cap height, atomizer clearance, or bottle shoulder shape.</li>
  <li>Forcing a bottle into a square insert when the geometry needs a better cutout.</li>
  <li>Adding too many finishing effects across fold lines or tight edges.</li>
  <li>Approving the mockup without a physical sample.</li>
  <li>Forgetting the shipping route and outer carton requirement.</li>
</ul>
<p>A perfume box can fail quietly if it looks good but does not pack cleanly, does not protect the bottle, or does not survive the route to the customer.</p>

<h2>RFQ Checklist Before Asking for a Quote</h2>
<p>A clear brief helps the supplier recommend a realistic structure and quote with fewer assumptions. Before contacting a <a href="https://hopgiayvpn.com/custom-packaging-boxes-manufacturer/">custom packaging boxes manufacturer</a>, prepare the details below.</p>
<ul>
  <li>Product type, product photos, and the filled bottle dimensions.</li>
  <li>Filled product weight and bottle material.</li>
  <li>Preferred structure: folding carton, rigid box, drawer box, sleeve, paper tube, or mailer.</li>
  <li>Sales channel and shipping destination.</li>
  <li>Material preference or brand direction.</li>
  <li>Printing requirements, such as CMYK, spot color, or inside printing.</li>
  <li>Finishing preference, such as matte, gloss, soft-touch, foil, embossing, debossing, or spot UV.</li>
  <li>Insert requirements for one bottle, multiple bottles, or a sample set.</li>
  <li>Artwork status, dieline status, and whether layout support is needed.</li>
  <li>Any packing or export notes that affect the final structure.</li>
</ul>
<p>If you send the supplier the bottle size, filled weight, cap height, and the preferred presentation level, it becomes much easier to compare structures and avoid unnecessary revisions.</p>

<h2>Final Thought</h2>
<p>The best perfume paper box structure is the one that fits the real bottle, supports the insert, matches the brand position, and still works in production. Structure solves the physical problem. Insert solves the movement problem. Finishing solves the perception problem. QC solves the risk problem.</p>
<p>When those decisions are made in the right order, perfume packaging becomes easier to sample, easier to approve, and easier to produce without surprises.</p>
HTML;
}
