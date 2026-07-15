<?php
/**
 * Syncs the candle packaging materials guide draft and images.
 */

add_action('admin_init', 'custom_box_sync_candle_packaging_materials_post');
add_action('admin_notices', 'custom_box_candle_packaging_materials_admin_notice');

function custom_box_sync_candle_packaging_materials_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if ('2026-07-15-v1' === get_option('custom_box_candle_packaging_materials_sync_version')) {
        return;
    }

    $post_id = custom_box_upsert_candle_packaging_materials_post();

    if (is_wp_error($post_id)) {
        update_option('custom_box_candle_packaging_materials_missing_post', $post_id->get_error_message(), false);
        return;
    }

    update_option('custom_box_candle_packaging_materials_missing_post', '', false);
    update_option('custom_box_candle_packaging_materials_sync_version', '2026-07-15-v1', false);
}

function custom_box_upsert_candle_packaging_materials_post()
{
    $post_data = custom_box_candle_packaging_materials_post_data();
    $post = custom_box_find_candle_packaging_materials_post($post_data['slug'], $post_data['title']);
    $content = custom_box_candle_packaging_materials_content();

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
    custom_box_sync_candle_packaging_materials_terms($post_id, $post_data);
    custom_box_sync_candle_packaging_materials_meta($post_id, $post_data);
    custom_box_sync_candle_packaging_materials_images($post_id);

    update_post_meta($post_id, '_custom_box_candle_packaging_materials_synced', current_time('mysql'));

    return $post_id;
}

function custom_box_candle_packaging_materials_post_data(): array
{
    return array(
        'title'           => 'How to Choose Paper Packaging Materials for Candle Products',
        'slug'            => 'how-to-choose-candle-packaging-materials',
        'seo_title'       => 'How to Choose Candle Packaging Materials | Paper Box Guide',
        'seo_description' => 'Compare rigid boxes, folding cartons, inserts and coatings to choose paper packaging materials for candle products by weight, channel and presentation.',
        'focus_keyword'   => 'how to choose candle packaging materials',
        'excerpt'         => 'Compare folding cartons, rigid boxes, inserts and surface coatings to choose a practical packaging material system for candle products.',
        'category'        => array(
            'name' => 'Packaging by Industry',
            'slug' => 'packaging-by-industry',
        ),
        'tags'            => array(
            'Candle Packaging',
            'Paper Packaging Materials',
            'Rigid Boxes',
            'Folding Cartons',
            'Packaging Inserts',
            'Surface Finishing',
        ),
    );
}

function custom_box_candle_packaging_materials_images(): array
{
    return array(
        'featured' => array(
            'base'    => 'how-to-choose-candle-packaging-materials',
            'alt'     => 'Rigid box and folding carton material options for candle product packaging',
            'title'   => 'Candle Packaging Material Selection Guide',
            'caption' => 'Rigid and folding paper box structures serve different packaging requirements.',
        ),
        'slot_1'   => array(
            'base'    => 'rigid-box-vs-folding-carton-candle-packaging',
            'alt'     => 'Rigid box and folding carton compared for glass candle jar packaging',
            'title'   => 'Rigid Box vs Folding Carton',
            'caption' => 'Structure should be selected before board grade and finishing.',
        ),
        'slot_2'   => array(
            'base'    => 'paperboard-insert-for-candle-jar-box',
            'alt'     => 'Folded paperboard insert holding a glass candle jar inside a rigid box',
            'title'   => 'Paperboard Insert for Glass Jar',
            'caption' => 'A fitted insert controls movement and transfers product weight to the box base.',
        ),
        'slot_3'   => array(
            'base'    => 'candle-box-coating-and-paper-surface-options',
            'alt'     => 'Matte laminated, uncoated, kraft and textured paper samples for candle boxes',
            'title'   => 'Candle Box Surface Options',
            'caption' => 'Surface treatments should be selected for touch, print and handling requirements.',
        ),
        'slot_4'   => array(
            'base'    => 'candle-packaging-sample-approval-check',
            'alt'     => 'Printed candle box samples prepared for product fit and surface inspection',
            'title'   => 'Candle Packaging Sample Approval',
            'caption' => 'Material approval should use the packed product, insert and finished box together.',
        ),
    );
}

function custom_box_find_candle_packaging_materials_post(string $slug, string $title): ?WP_Post
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

function custom_box_sync_candle_packaging_materials_terms(int $post_id, array $post_data): void
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

function custom_box_sync_candle_packaging_materials_meta(int $post_id, array $post_data): void
{
    update_post_meta($post_id, 'rank_math_title', $post_data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $post_data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $post_data['focus_keyword']);
}

function custom_box_sync_candle_packaging_materials_images(int $post_id): array
{
    $images = custom_box_candle_packaging_materials_images();
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach ($images as $key => $image) {
        $attachment_id = custom_box_find_candle_packaging_materials_attachment($image['base']);

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
        $marker = '<!-- vpn-candle-packaging-materials-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_candle_packaging_materials_figure($attachment_id, $image);
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

    update_option('custom_box_candle_packaging_materials_missing_images', $missing_images, false);
    update_option('custom_box_candle_packaging_materials_missing_slots', $missing_slots, false);

    return array(
        'missing_images' => $missing_images,
        'missing_slots'  => $missing_slots,
    );
}

function custom_box_find_candle_packaging_materials_attachment(string $base): int
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

    return custom_box_create_candle_packaging_materials_attachment($base);
}

function custom_box_create_candle_packaging_materials_attachment(string $base): int
{
    $uploads = wp_get_upload_dir();

    if (empty($uploads['basedir']) || empty($uploads['baseurl'])) {
        return 0;
    }

    $relative_dir = '2026/07';
    $extensions = array('webp', 'jpg', 'jpeg', 'png');
    $file_path = '';
    $relative_file = '';

    foreach ($extensions as $extension) {
        $candidate_relative = $relative_dir . '/' . $base . '.' . $extension;
        $candidate_path = trailingslashit($uploads['basedir']) . $candidate_relative;

        if (file_exists($candidate_path)) {
            $file_path = $candidate_path;
            $relative_file = $candidate_relative;
            break;
        }
    }

    if ('' === $file_path || '' === $relative_file) {
        return 0;
    }

    $filetype = wp_check_filetype($file_path);
    $attachment_id = wp_insert_attachment(
        array(
            'guid'           => trailingslashit($uploads['baseurl']) . $relative_file,
            'post_mime_type' => !empty($filetype['type']) ? $filetype['type'] : 'image/webp',
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

function custom_box_candle_packaging_materials_figure(int $attachment_id, array $image): string
{
    return sprintf(
        '<figure><img src="%s" alt="%s" style="width:100%%; height:auto;" loading="lazy" decoding="async"><figcaption>%s</figcaption></figure>',
        esc_url(wp_get_attachment_url($attachment_id)),
        esc_attr($image['alt']),
        esc_html($image['caption'])
    );
}

function custom_box_candle_packaging_materials_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing_post = (string) get_option('custom_box_candle_packaging_materials_missing_post', '');
    $missing_images = (array) get_option('custom_box_candle_packaging_materials_missing_images', array());
    $missing_slots = (array) get_option('custom_box_candle_packaging_materials_missing_slots', array());

    if ('' === $missing_post && empty($missing_images) && empty($missing_slots)) {
        return;
    }

    echo '<div class="notice notice-warning"><p><strong>Candle packaging materials guide post sync:</strong> ';

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

function custom_box_candle_packaging_materials_content(): string
{
    $manufacturer_link = '<a href="' . esc_url(home_url('/custom-packaging-boxes-manufacturer/')) . '">custom packaging boxes manufacturer</a>';
    $structure_link = '<a href="' . esc_url(home_url('/how-to-choose-paper-box-structure-product/')) . '">how to choose the right paper box structure for a product</a>';
    $insert_link = '<a href="' . esc_url(home_url('/how-inserts-protect-products-paper-boxes/')) . '">how inserts help protect products inside paper boxes</a>';
    $shipping_link = '<a href="' . esc_url(home_url('/how-to-reduce-paper-box-damage-during-shipping/')) . '">how to reduce paper box damage during shipping and handling</a>';

    $content = <<<'HTML'
<p>Choosing candle packaging materials is easier when the team works in the right order. Start with the filled candle weight and the sales channel, then decide whether the product needs a folding carton, a rigid box, or a retail box plus a separate shipping layer. After that, choose the board grade, insert, surface treatment, and packed sample test plan. If that order is skipped, teams often over-spec the outer box and under-spec the insert or the shipper.</p>
<p>This guide is written for candle brand owners, packaging designers, importers, procurement teams, gift suppliers, and private-label buyers who need to choose paper packaging materials with less guesswork. The goal is not to memorize every material name. The goal is to choose a paper packaging system that fits the real candle, the route to market, and the budget.</p>
<p>If you need a broader starting point on structure before picking board and finish, review the {STRUCTURE_LINK} page first.</p>

<h2>Start With Weight, Fragility, and Channel</h2>
<p>A lightweight tin candle sold in volume does not need the same material stack as a heavy glass jar candle sold as a gift. Candle packaging works best when the buyer defines the load, the channel, and the presentation target before asking for samples.</p>
<p>The first question is not whether the box should look premium. The first question is what the box must do. A retail tin candle may need a clean print face and fast packing. A glass jar candle may need a stronger insert and better base support. A gift set may need slower reveal and a tray that keeps each item organized. An ecommerce candle may need a retail box plus a shipper that absorbs the transport stress.</p>

<table>
<thead>
<tr>
<th>Packaging situation</th>
<th>First direction</th>
<th>Why it usually fits</th>
<th>Main thing to check</th>
</tr>
</thead>
<tbody>
<tr>
<td>Light tin candle for retail volume</td>
<td>Folding carton</td>
<td>Efficient, printable, and easy to pack flat</td>
<td>Board stiffness and surface scuff resistance</td>
</tr>
<tr>
<td>Heavy glass jar candle for retail shelf</td>
<td>Folding carton with strong insert or rigid box</td>
<td>Supports weight and reduces movement</td>
<td>Bottom support and lid clearance</td>
</tr>
<tr>
<td>Direct-to-consumer candle shipment</td>
<td>Retail box plus corrugated shipper</td>
<td>The shipper absorbs the transport risk</td>
<td>Outer carton compression and corner crush</td>
</tr>
<tr>
<td>Premium candle gift set</td>
<td>Rigid box with fitted tray</td>
<td>Creates a slower and more controlled reveal</td>
<td>Tray precision and premium finish control</td>
</tr>
<tr>
<td>Natural or eco-positioned candle line</td>
<td>Kraft or uncoated folding carton</td>
<td>Supports a natural material story</td>
<td>Print color shift and claim accuracy</td>
</tr>
</tbody>
</table>

<p>Do not choose a rigid box by default when the candle is low ticket, shipped in high volume, or already packed inside a strong corrugated outer layer. In those cases, a folding carton often gives the buyer a better balance of cost, speed, and shelf communication.</p>

<!-- IMAGE_SLOT_1 -->

<h2>Choose Structure Before Board Grade</h2>
<p>If you want a deeper comparison between box families, the {STRUCTURE_LINK} guide is a useful baseline. For candles, structure should be decided before board grade because the structure determines how much protection and presentation the board must carry.</p>
<p>Folding cartons are usually the practical answer when the buyer needs flat packing, clear print space, and efficient volume. Rigid boxes are usually the right answer when the candle is premium enough to justify extra board, extra glue, and extra freight. The best choice depends on the real product, not on the mockup.</p>
<ul>
<li>Choose a folding carton when the candle is light to medium weight, retail-driven, and price sensitive.</li>
<li>Choose a rigid box when the candle is heavy, premium, gift oriented, or part of a set with a controlled reveal.</li>
<li>Choose a folding carton with a stronger insert when the candle needs protection but the brand cannot absorb rigid-box freight.</li>
<li>Choose a modular structure when the brand launches seasonal scents and wants one base format with changeable artwork or sleeve layers.</li>
</ul>
<p>The same candle can move through different channels, and the best structure can change with it. A jar candle sold on a shelf may use one material stack, while the same jar shipped direct to consumer may need a different board thickness and a different outer pack. This is why structure, board, insert, and shipping plan should be discussed together instead of in separate meetings.</p>

<h2>Pick the Board Grade That Matches the Job</h2>
<p>Board is not the same as protection. A higher gsm number does not automatically mean better performance if the caliper, stiffness, fold behavior, and print surface are not right. The candle pack should use the lightest board that still supports the real product and the chosen route to market.</p>
<p>For a candle box, the board needs to carry the visual side of the package and also work with the insert and the shipping layer. The outer board should not be asked to solve every problem on its own. The insert should handle movement and the outer shipper should handle transport.</p>

<table>
<thead>
<tr>
<th>Material</th>
<th>Best use</th>
<th>Strength</th>
<th>Watch out for</th>
</tr>
</thead>
<tbody>
<tr>
<td>SBS or white coated board</td>
<td>Clean retail cartons, bright graphics, premium white surfaces</td>
<td>Strong print clarity and good brand color control</td>
<td>Scuff visibility on darker artwork</td>
</tr>
<tr>
<td>FBB or ivory board</td>
<td>General retail cartons and mid-weight candle products</td>
<td>Balanced stiffness and reliable folding behavior</td>
<td>Proof color on the exact surface before bulk approval</td>
</tr>
<tr>
<td>Kraft paperboard</td>
<td>Natural, handmade, or low-decoration candle brands</td>
<td>Supports a warm and honest material story</td>
<td>Color shifts and less neutral print reproduction</td>
</tr>
<tr>
<td>Greyboard wrapped with art paper</td>
<td>Rigid candle boxes and premium gift sets</td>
<td>Strong structure and better perceived value</td>
<td>Higher freight volume and higher unit cost</td>
</tr>
<tr>
<td>Micro-flute or corrugated board</td>
<td>Shipping packs, ecommerce protectors, outer cartons</td>
<td>Better compression and transport strength</td>
<td>Not always the right surface for premium shelf branding</td>
</tr>
<tr>
<td>Molded fibre</td>
<td>Paper-based insert or tray system</td>
<td>Stable cavity and strong sustainability story</td>
<td>Fine detail and exact fit need testing</td>
</tr>
</tbody>
</table>

<p>If the candle is heavy, make the insert carry the weight and let the outer board keep the face clean. Do not solve a weight problem only by making the outer carton thicker. That often increases cost without fixing the movement inside the box.</p>

<h2>Build the Insert Around the Load Path</h2>
<p>The insert is the part that turns a box into a packaging system. In a candle box, the load path should start at the jar base, pass through the insert, and end at the bottom panel. The lid should protect the top, not carry the weight.</p>
<p>This is where many projects go wrong. The insert is drawn as a neat shape in a render, but the real candle can still bounce, lean, or rub if the cavity is too shallow, too deep, or too loose. The insert should fit the body, control the lid, and leave room for fingers during removal.</p>
<ul>
<li>Paperboard inserts work well for light to medium jar candles when the buyer wants a paper-led package.</li>
<li>Corrugated inserts work better when the candle is heavy or the route includes parcel handling and vibration.</li>
<li>Molded fibre trays are useful when the buyer wants a stable cavity with a more natural material story.</li>
<li>Greyboard trays are common inside rigid boxes when the project needs premium presentation and precise fit.</li>
<li>EVA can be used for special premium cases, but it should be a deliberate choice rather than the default answer.</li>
</ul>
<p>The real candle should sit cleanly without squeezing the label, rubbing the wick area, or pressing against the lid. If the insert only works when the box is empty, it is not ready for production.</p>
<p>For a more detailed insert comparison, review the {INSERT_LINK} guide.</p>

<!-- IMAGE_SLOT_2 -->

<h2>Treat Coating and Surface as Performance Choices</h2>
<p>The surface layer should support handling, print, and brand tone. A finish can improve the package, but it cannot rescue a weak structure or a loose insert. That is why the finish should be chosen after the material stack and the fit are already close.</p>
<p>Candle brands often want a calm, premium, and tactile surface. That can be done with matte lamination, soft-touch film, textured paper, or a controlled coating. A brighter retail look can use gloss lamination or varnish. A natural brand may prefer uncoated kraft or a lighter aqueous coating. The right answer depends on the handling environment, not only on the mockup.</p>

<table>
<thead>
<tr>
<th>Surface choice</th>
<th>Best use</th>
<th>Benefit</th>
<th>Watch out for</th>
</tr>
</thead>
<tbody>
<tr>
<td>Matte lamination</td>
<td>Premium retail candles and gift boxes</td>
<td>Soft, controlled, and less reflective</td>
<td>Can show scuff on dark surfaces</td>
</tr>
<tr>
<td>Gloss lamination</td>
<td>Bright retail graphics and stronger color impact</td>
<td>Good visual punch and surface protection</td>
<td>Can feel less refined on premium candle lines</td>
</tr>
<tr>
<td>Soft-touch film</td>
<td>Luxury candles and gift sets</td>
<td>High tactile value</td>
<td>Fingerprint and scratch behavior should be tested</td>
</tr>
<tr>
<td>Aqueous coating or varnish</td>
<td>Retail cartons with moderate protection needs</td>
<td>Lightweight finish and simpler surface treatment</td>
<td>Must be checked against the handling route and print goal</td>
</tr>
<tr>
<td>Uncoated kraft</td>
<td>Natural or handmade candle brands</td>
<td>Honest material feel and simple look</td>
<td>Color shifts and print density can change</td>
</tr>
<tr>
<td>Textured paper</td>
<td>Premium candles with a tactile identity</td>
<td>Creates a distinctive brand feel</td>
<td>Fine text, foil, and small details need testing</td>
</tr>
<tr>
<td>Spot UV, foil, embossing</td>
<td>Selected branding accents</td>
<td>Adds focus and premium detail</td>
<td>Use only after the base stack is proven</td>
</tr>
</tbody>
</table>

<p>If recyclability claims matter, confirm the full stack with the supplier instead of assuming every coating, glue, or tray is automatically suitable for the same claim in every market. Surface choice should support the brand story, but it should also survive the real packing and delivery route.</p>

<!-- IMAGE_SLOT_3 -->

<h2>Separate the Retail Box From the Shipping Pack</h2>
<p>A retail box can be beautiful and still fail in transit if it is asked to do the job of a shipper. Candle packaging should separate the presentation layer from the transport layer whenever the product is heavy, fragile, or sold direct to consumer.</p>
<p>If shipping damage is a major concern, the {SHIPPING_LINK} guide is a useful reference, because the outer pack often decides whether the inner box survives corners, drops, and stacking pressure.</p>
<ul>
<li>For direct-to-consumer candle orders, test the retail box inside a corrugated mailer or master carton.</li>
<li>For export, confirm the outer carton count, orientation, and corner protection before approving the retail design.</li>
<li>For gift sets, make sure the tray keeps the jars apart even when the outer carton is compressed.</li>
<li>For retail shelf only, still check that the box can be packed, stacked, and palletized without scuffing.</li>
</ul>
<p>Do not ask a rigid box to replace the shipper. If the shipping route is harsh, use a shipper that absorbs the movement and let the retail box stay clean. That separation keeps the pack more stable and usually reduces avoidable damage claims.</p>

<h2>Approve a Packed Sample, Not an Empty Sample</h2>
<p>A candle sample should be reviewed with the real filled product, the chosen insert, and the finished outer box together. Empty samples can hide weight problems, lid clearance problems, and label rub problems. A good sample review should tell you whether the whole package works when it is filled, closed, moved, and packed at production speed.</p>

<!-- IMAGE_SLOT_4 -->

<ol>
<li>Check the filled candle dimensions and weight.</li>
<li>Approve the dieline, lock the box style, and confirm glue zones and fold direction.</li>
<li>Review the board sample and the insert blank before print is finalized.</li>
<li>Pack the real candle and test shake, tilt, and reopen behavior.</li>
<li>Pack several units and check whether the line team can repeat the process without forcing the box.</li>
<li>Place the retail box into the outer shipping pack and test compression, scuffing, and stack stability.</li>
</ol>
<p>The sample should answer one practical question: does the whole package still work when it is filled, closed, moved, and packed at production speed? If the answer is unclear, the material stack needs another round of review before bulk production begins.</p>

<h2>RFQ Checklist for Candle Packaging Materials</h2>
<p>Before contacting the {MANUFACTURER_LINK}, prepare a brief that lets the supplier recommend the right material stack instead of guessing.</p>
<ul>
<li>Candle format: glass jar, tin candle, ceramic cup, travel size, or gift set.</li>
<li>Filled weight, full dimensions, lid height, and any fragile edges.</li>
<li>Target structure: folding carton, rigid box, sleeve, or retail box with a separate shipping pack.</li>
<li>Insert direction: paperboard, corrugated, molded fibre, greyboard tray, or another approved cavity.</li>
<li>Surface direction: matte, gloss, soft-touch, uncoated, textured, or coated kraft.</li>
<li>Print needs: CMYK, spot color, inside print, barcode, warning copy, or scent family information.</li>
<li>Outer pack: master carton, mailer, or export carton requirement.</li>
<li>Order quantity, target market, and packing schedule.</li>
<li>Artwork status and whether the supplier should supply a dieline.</li>
<li>Any recyclability or certification claim that must be verified before the quote is approved.</li>
</ul>
<p>If the buyer can answer those questions, the supplier can quote the right material system faster and with fewer sample revisions.</p>
<p>The best candle packaging materials are the ones that fit the real product, protect the load path, match the sales channel, and still look intentional when the packed sample is reviewed.</p>
HTML;

    return str_replace(
        array('{MANUFACTURER_LINK}', '{STRUCTURE_LINK}', '{INSERT_LINK}', '{SHIPPING_LINK}'),
        array($manufacturer_link, $structure_link, $insert_link, $shipping_link),
        $content
    );
}
