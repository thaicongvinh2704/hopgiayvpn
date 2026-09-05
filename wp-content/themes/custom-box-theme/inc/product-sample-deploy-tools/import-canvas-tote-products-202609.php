<?php
/**
 * Idempotent WooCommerce importer for the four canvas/non-woven tote products.
 *
 * The source Markdown and original WebP files are deployed with the active
 * theme. This file is intentionally callable from a root tools wrapper so a
 * normal local repair or an explicitly triggered deployment can reuse the
 * same product logic.
 */

if (!defined('ABSPATH')) {
    return;
}

if (!function_exists('vpn_canvas_tote_202609_product_definitions')) {
    define('VPN_CANVAS_TOTE_202609_MARKER', 'product-samples-canvas-totes-202609');

    function vpn_canvas_tote_202609_product_definitions() {
        static $definitions = null;

        if (null !== $definitions) {
            return $definitions;
        }

        $definitions = array(
            array(
                'title'         => 'Custom Pink Ghost Laminated Woven Tote Bag',
                'slug'          => 'custom-pink-ghost-laminated-woven-tote-bag',
                'folder'        => '01-pink-ghost-laminated-tote',
                'category_slug' => 'fabric-bags',
                'category_name' => 'Fabric Bags',
                'seo_title'     => 'Custom Pink Ghost Laminated Tote Bags | VPN Packaging',
                'images'        => array(
                    '01-front-view-pink-ghost-laminated-tote.webp',
                    '02-interior-angle-pink-ghost-laminated-tote.webp',
                    '03-stitching-closeup-pink-ghost-laminated-tote.webp',
                    '04-back-bottom-view-pink-ghost-laminated-tote.webp',
                    '05-top-down-interior-pink-ghost-laminated-tote.webp',
                    '06-feature-callouts-pink-ghost-laminated-tote.webp',
                ),
                'tags'          => array('custom tote bags', 'laminated woven bags', 'halloween packaging', 'reusable shopping bags', 'promotional tote bags'),
                'specs'         => array(
                    'Feature'                   => 'Reflective pink finish with repeating white ghost artwork',
                    'Industrial Use'            => 'Halloween retail, seasonal gifting, events and promotional merchandise',
                    'Paper Type'                => 'Laminated woven-style body; exact substrate and material weight available on request',
                    'Box Type'                  => 'Custom laminated woven tote bag',
                    'Shape'                     => 'Open-top rectangular tote bag',
                    'Place of Origin'           => 'Vietnam',
                    'Model Number'              => 'Available on request',
                    'Brand Name'                => 'VPN Packaging',
                    'Province'                  => 'Ho Chi Minh City',
                    'Accessories'               => 'Matching handles and edge binding',
                    'Custom Order'              => 'Accept',
                    'Liner Type'               => 'Available on request',
                    'Logo Printing'            => 'Custom logo or artwork',
                    'Printing Handling'        => 'Available on request',
                    'Color'                    => 'Metallic/reflective pink with white ghost artwork',
                    'Size'                     => 'Available on request',
                    'Thickness'                => 'Available on request',
                    'Single Piece Price'       => 'Request a quote',
                    'Minimum Order Quantity (MOQ)' => 'Available on request',
                    'Product Name'             => 'Custom Pink Ghost Laminated Woven Tote Bag',
                    'Design'                   => "Customer's Specific Requirement",
                ),
            ),
            array(
                'title'         => 'Custom Navy Blue Non-Woven Tote Bag',
                'slug'          => 'custom-navy-blue-non-woven-tote-bag',
                'folder'        => '02-navy-blue-non-woven-tote',
                'category_slug' => 'non-woven-bags',
                'category_name' => 'Non-Woven Bags',
                'seo_title'     => 'Custom Navy Blue Non-Woven Tote Bags | VPN Packaging',
                'images'        => array(
                    '01-front-view-navy-blue-non-woven-tote.webp',
                    '02-interior-angle-navy-blue-non-woven-tote.webp',
                    '03-stitching-closeup-navy-blue-non-woven-tote.webp',
                    '04-back-bottom-view-navy-blue-non-woven-tote.webp',
                    '05-top-down-interior-navy-blue-non-woven-tote.webp',
                    '06-feature-callouts-navy-blue-non-woven-tote.webp',
                ),
                'tags'          => array('custom tote bags', 'non-woven tote bags', 'reusable shopping bags', 'promotional bags', 'event bags'),
                'specs'         => array(
                    'Feature'                   => 'Solid navy blue body with long matching handles',
                    'Industrial Use'            => 'Trade shows, corporate events, retail, schools and giveaways',
                    'Paper Type'                => 'Non-woven-style body; exact material type and weight available on request',
                    'Box Type'                  => 'Custom non-woven tote bag',
                    'Shape'                     => 'Open-top rectangular tote bag',
                    'Place of Origin'           => 'Vietnam',
                    'Model Number'              => 'Available on request',
                    'Brand Name'                => 'VPN Packaging',
                    'Province'                  => 'Ho Chi Minh City',
                    'Accessories'               => 'Matching carry handles',
                    'Custom Order'              => 'Accept',
                    'Liner Type'               => 'Available on request',
                    'Logo Printing'            => 'Custom logo or artwork',
                    'Printing Handling'        => 'Available on request',
                    'Color'                    => 'Navy blue',
                    'Size'                     => 'Available on request',
                    'Thickness'                => 'Available on request',
                    'Single Piece Price'       => 'Request a quote',
                    'Minimum Order Quantity (MOQ)' => 'Available on request',
                    'Product Name'             => 'Custom Navy Blue Non-Woven Tote Bag',
                    'Design'                   => "Customer's Specific Requirement",
                ),
            ),
            array(
                'title'         => 'Custom Blue Smiley Face Non-Woven Tote Bag',
                'slug'          => 'custom-blue-smiley-face-non-woven-tote-bag',
                'folder'        => '03-blue-smiley-non-woven-tote',
                'category_slug' => 'non-woven-bags',
                'category_name' => 'Non-Woven Bags',
                'seo_title'     => 'Custom Blue Smiley Non-Woven Tote Bags | VPN Packaging',
                'images'        => array(
                    '01-front-view-blue-smiley-non-woven-tote.webp',
                    '02-interior-angle-blue-smiley-non-woven-tote.webp',
                    '03-stitching-closeup-blue-smiley-non-woven-tote.webp',
                    '04-back-bottom-view-blue-smiley-non-woven-tote.webp',
                    '05-top-down-interior-blue-smiley-non-woven-tote.webp',
                    '06-feature-callouts-blue-smiley-non-woven-tote.webp',
                ),
                'tags'          => array('custom tote bags', 'printed non-woven bags', 'reusable shopping bags', 'school promotional bags', 'patterned tote bags'),
                'specs'         => array(
                    'Feature'                   => 'White body with a repeating sky-blue smiley pattern',
                    'Industrial Use'            => 'Schools, children\'s brands, retail, events, gifts and promotions',
                    'Paper Type'                => 'Non-woven-style body; exact material type and weight available on request',
                    'Box Type'                  => 'Custom printed non-woven tote bag',
                    'Shape'                     => 'Open-top rectangular tote bag',
                    'Place of Origin'           => 'Vietnam',
                    'Model Number'              => 'Available on request',
                    'Brand Name'                => 'VPN Packaging',
                    'Province'                  => 'Ho Chi Minh City',
                    'Accessories'               => 'Matching carry handles',
                    'Custom Order'              => 'Accept',
                    'Liner Type'               => 'Available on request',
                    'Logo Printing'            => 'Custom logo or repeat artwork',
                    'Printing Handling'        => 'Available on request',
                    'Color'                    => 'White with sky-blue smiley artwork',
                    'Size'                     => 'Available on request',
                    'Thickness'                => 'Available on request',
                    'Single Piece Price'       => 'Request a quote',
                    'Minimum Order Quantity (MOQ)' => 'Available on request',
                    'Product Name'             => 'Custom Blue Smiley Face Non-Woven Tote Bag',
                    'Design'                   => "Customer's Specific Requirement",
                ),
            ),
            array(
                'title'         => 'Custom Yellow Smiley Face Non-Woven Tote Bag',
                'slug'          => 'custom-yellow-smiley-face-non-woven-tote-bag',
                'folder'        => '04-yellow-smiley-non-woven-tote',
                'category_slug' => 'non-woven-bags',
                'category_name' => 'Non-Woven Bags',
                'seo_title'     => 'Custom Yellow Smiley Tote Bags | VPN Packaging Factory',
                'images'        => array(
                    '01-front-view-yellow-smiley-non-woven-tote.webp',
                    '02-interior-angle-yellow-smiley-non-woven-tote.webp',
                    '03-stitching-closeup-yellow-smiley-non-woven-tote.webp',
                    '04-back-bottom-view-yellow-smiley-non-woven-tote.webp',
                    '05-top-down-interior-yellow-smiley-non-woven-tote.webp',
                    '06-feature-callouts-yellow-smiley-non-woven-tote.webp',
                ),
                'tags'          => array('custom tote bags', 'printed non-woven bags', 'reusable shopping bags', 'promotional bags', 'patterned tote bags'),
                'specs'         => array(
                    'Feature'                   => 'White body with repeating yellow-and-black smile graphics',
                    'Industrial Use'            => 'Promotions, fashion retail, pop-ups, events, gifting and youth-focused brands',
                    'Paper Type'                => 'Non-woven-style body; exact material type and weight available on request',
                    'Box Type'                  => 'Custom printed non-woven tote bag',
                    'Shape'                     => 'Open-top rectangular tote bag',
                    'Place of Origin'           => 'Vietnam',
                    'Model Number'              => 'Available on request',
                    'Brand Name'                => 'VPN Packaging',
                    'Province'                  => 'Ho Chi Minh City',
                    'Accessories'               => 'Matching carry handles',
                    'Custom Order'              => 'Accept',
                    'Liner Type'               => 'Available on request',
                    'Logo Printing'            => 'Custom logo or repeat artwork',
                    'Printing Handling'        => 'Available on request',
                    'Color'                    => 'White with yellow-and-black smiley artwork',
                    'Size'                     => 'Available on request',
                    'Thickness'                => 'Available on request',
                    'Single Piece Price'       => 'Request a quote',
                    'Minimum Order Quantity (MOQ)' => 'Available on request',
                    'Product Name'             => 'Custom Yellow Smiley Face Non-Woven Tote Bag',
                    'Design'                   => "Customer's Specific Requirement",
                ),
            ),
        );

        return $definitions;
    }

    function vpn_canvas_tote_202609_source_path($definition) {
        return get_template_directory() . '/inc/product-content/canvas-tote-202609/' . $definition['folder'] . '/SEO-GEO-AIO-product-content.md';
    }

    function vpn_canvas_tote_202609_unwrap_source_value($value) {
        return trim(trim($value), "` \t\r\n");
    }

    function vpn_canvas_tote_202609_source_meta($source, $label) {
        $pattern = '/^-\s*' . preg_quote($label, '/') . ':\s*`?(.+?)`?\s*$/mi';

        if (!preg_match($pattern, $source, $matches)) {
            throw new RuntimeException('Missing source metadata: ' . $label);
        }

        return vpn_canvas_tote_202609_unwrap_source_value($matches[1]);
    }

    function vpn_canvas_tote_202609_source_image_alt_map($source) {
        $lines = preg_split('/\r\n|\r|\n/', $source);
        $map = array();
        $inside = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (preg_match('/^##\s+Image SEO:/i', $trimmed)) {
                $inside = true;
                continue;
            }

            if ($inside && preg_match('/^##\s+/i', $trimmed)) {
                break;
            }

            if (!$inside || 0 !== strpos($trimmed, '|')) {
                continue;
            }

            $cells = array_map('trim', explode('|', trim($trimmed, '|')));

            if (count($cells) < 2 || preg_match('/^-+$/', str_replace(' ', '', $cells[0]))) {
                continue;
            }

            $filename = vpn_canvas_tote_202609_unwrap_source_value($cells[0]);
            $alt = vpn_canvas_tote_202609_unwrap_source_value($cells[1]);

            if (!$filename || !$alt || 'Filename' === $filename) {
                continue;
            }

            $base = pathinfo($filename, PATHINFO_FILENAME);
            $map[$base] = $alt;
        }

        return $map;
    }

    function vpn_canvas_tote_202609_normalize_link($url) {
        $url = trim($url);
        $parsed = wp_parse_url($url);

        if (is_array($parsed) && !empty($parsed['host'])) {
            $host = strtolower($parsed['host']);

            if ('hopgiayvpn.com' === $host || 'www.hopgiayvpn.com' === $host) {
                $path = isset($parsed['path']) ? $parsed['path'] : '/';
                $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
                $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

                return home_url($path . $query . $fragment);
            }
        }

        if (0 === strpos($url, '/')) {
            return home_url($url);
        }

        return esc_url_raw($url);
    }

    function vpn_canvas_tote_202609_inline_markdown($text) {
        $links = array();

        $text = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function ($matches) use (&$links) {
            $token = '__VPN_CANVAS_LINK_' . count($links) . '__';
            $links[$token] = '<a href="' . esc_url(vpn_canvas_tote_202609_normalize_link($matches[2])) . '">' . esc_html($matches[1]) . '</a>';

            return $token;
        }, $text);

        $text = esc_html(trim($text));
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '<em>$1</em>', $text);

        foreach ($links as $token => $link) {
            $text = str_replace($token, $link, $text);
        }

        return $text;
    }

    function vpn_canvas_tote_202609_render_table($lines) {
        $rows = array();

        foreach ($lines as $line) {
            $cells = array_map('trim', explode('|', trim($line, '|')));

            if ($cells) {
                $rows[] = $cells;
            }
        }

        if (count($rows) < 2) {
            return '';
        }

        $headers = array_shift($rows);

        if (isset($rows[0]) && preg_match('/^-/', str_replace(' ', '', $rows[0][0]))) {
            array_shift($rows);
        }

        $html = '<div class="table-scroll-region" data-table-scroll-region><table><thead><tr>';

        foreach ($headers as $header) {
            $html .= '<th>' . vpn_canvas_tote_202609_inline_markdown($header) . '</th>';
        }

        $html .= '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $html .= '<tr>';

            foreach ($headers as $index => $unused) {
                $html .= '<td>' . vpn_canvas_tote_202609_inline_markdown(isset($row[$index]) ? $row[$index] : '') . '</td>';
            }

            $html .= '</tr>';
        }

        return $html . '</tbody></table></div>';
    }

    function vpn_canvas_tote_202609_render_markdown($lines) {
        $output = array();
        $paragraph = array();
        $list_items = array();
        $intro = array();
        $seen_h2 = false;
        $line_count = count($lines);

        $flush_paragraph = function () use (&$paragraph, &$output, &$intro, &$seen_h2) {
            if (!$paragraph) {
                return;
            }

            $text = vpn_canvas_tote_202609_inline_markdown(implode(' ', $paragraph));
            $output[] = '<p>' . $text . '</p>';

            if (!$seen_h2 && count($intro) < 2) {
                $intro[] = '<p>' . $text . '</p>';
            }

            $paragraph = array();
        };

        $flush_list = function () use (&$list_items, &$output) {
            if (!$list_items) {
                return;
            }

            $output[] = '<ul><li>' . implode('</li><li>', $list_items) . '</li></ul>';
            $list_items = array();
        };

        for ($index = 0; $index < $line_count; $index++) {
            $line = rtrim($lines[$index]);
            $trimmed = trim($line);

            if ('' === $trimmed) {
                $flush_paragraph();
                $flush_list();
                continue;
            }

            if (0 === strpos($trimmed, '|')) {
                $flush_paragraph();
                $flush_list();
                $table_lines = array($trimmed);

                while (($index + 1) < $line_count && 0 === strpos(trim($lines[$index + 1]), '|')) {
                    $index++;
                    $table_lines[] = trim($lines[$index]);
                }

                $output[] = vpn_canvas_tote_202609_render_table($table_lines);
                continue;
            }

            if (preg_match('/^###\s+(.+)$/', $trimmed, $matches)) {
                $flush_paragraph();
                $flush_list();
                $output[] = '<h3>' . vpn_canvas_tote_202609_inline_markdown($matches[1]) . '</h3>';
                continue;
            }

            if (preg_match('/^##\s+(.+)$/', $trimmed, $matches)) {
                $flush_paragraph();
                $flush_list();
                $seen_h2 = true;
                $output[] = '<h2>' . vpn_canvas_tote_202609_inline_markdown($matches[1]) . '</h2>';
                continue;
            }

            if (preg_match('/^[-*]\s+(.+)$/', $trimmed, $matches)) {
                $flush_paragraph();
                $list_items[] = vpn_canvas_tote_202609_inline_markdown($matches[1]);
                continue;
            }

            $flush_list();
            $paragraph[] = $trimmed;
        }

        $flush_paragraph();
        $flush_list();

        return array(
            'html'  => implode("\n", array_filter($output)),
            'intro' => $intro,
        );
    }

    function vpn_canvas_tote_202609_customer_sections($source, $expected_title) {
        $lines = preg_split('/\r\n|\r|\n/', $source);
        $title_index = -1;
        $end_index = count($lines);

        foreach ($lines as $index => $line) {
            if (trim($line) === '# ' . $expected_title) {
                $title_index = $index;
                break;
            }
        }

        if (-1 === $title_index) {
            throw new RuntimeException('Customer-facing H1 not found for ' . $expected_title);
        }

        for ($index = $title_index + 1; $index < count($lines); $index++) {
            if (preg_match('/^##\s+Image SEO:/i', trim($lines[$index]))) {
                $end_index = $index;
                break;
            }
        }

        $customer_lines = array_slice($lines, $title_index + 1, $end_index - $title_index - 1);
        $faq_start = null;
        $faq_end = count($customer_lines);

        foreach ($customer_lines as $index => $line) {
            if (preg_match('/^##\s+Frequently asked questions\s*$/i', trim($line))) {
                $faq_start = $index;
                break;
            }
        }

        if (null !== $faq_start) {
            for ($index = $faq_start + 1; $index < count($customer_lines); $index++) {
                if (preg_match('/^##\s+/', trim($customer_lines[$index]))) {
                    $faq_end = $index;
                    break;
                }
            }
        }

        $body_lines = $customer_lines;
        $faq_lines = array();

        if (null !== $faq_start) {
            $faq_lines = array_slice($customer_lines, $faq_start + 1, $faq_end - $faq_start - 1);
            $body_lines = array_merge(
                array_slice($customer_lines, 0, $faq_start),
                array_slice($customer_lines, $faq_end)
            );
        }

        return array(
            'body' => vpn_canvas_tote_202609_render_markdown($body_lines),
            'faq'  => vpn_canvas_tote_202609_parse_faq($faq_lines),
        );
    }

    function vpn_canvas_tote_202609_parse_faq($lines) {
        $items = array();
        $question = '';
        $answer = array();

        $flush = function () use (&$items, &$question, &$answer) {
            if ('' === $question) {
                return;
            }

            $rendered = vpn_canvas_tote_202609_render_markdown($answer);
            $items[] = array(
                'question' => trim($question),
                'answer'   => $rendered['html'],
            );
            $question = '';
            $answer = array();
        };

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (preg_match('/^###\s+(.+)$/', $trimmed, $matches)) {
                $flush();
                $question = $matches[1];
                continue;
            }

            if ('' !== $question) {
                $answer[] = $line;
            }
        }

        $flush();

        if (6 !== count($items)) {
            throw new RuntimeException('Expected six FAQ items, found ' . count($items));
        }

        return $items;
    }

    function vpn_canvas_tote_202609_faq_html($items) {
        $html = '<section class="product-faq canvas-tote-faq"><div class="container"><h2>Frequently Asked Questions</h2>';

        foreach ($items as $item) {
            $html .= '<details class="faq-item"><summary>' . esc_html($item['question']) . '</summary><div class="faq-answer">' . $item['answer'] . '</div></details>';
        }

        return $html . '</div></section>';
    }

    function vpn_canvas_tote_202609_exact_attachment_id($base) {
        global $wpdb;

        $like = '%' . $wpdb->esc_like($base) . '%';
        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC",
            $like
        ));

        foreach ($ids as $id) {
            $attached = (string) get_post_meta((int) $id, '_wp_attached_file', true);

            if ($base === pathinfo(wp_basename($attached), PATHINFO_FILENAME)) {
                return (int) $id;
            }
        }

        return 0;
    }

    function vpn_canvas_tote_202609_attachment($filename, $alt, $title, $caption, $product_id) {
        $uploads = wp_upload_dir();

        if (!empty($uploads['error'])) {
            throw new RuntimeException('Upload directory error: ' . $uploads['error']);
        }

        $relative = '2026/09/' . $filename;
        $upload_path = trailingslashit($uploads['basedir']) . $relative;
        $bundle_path = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $relative;
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $attachment_id = vpn_canvas_tote_202609_exact_attachment_id($base);

        if (!file_exists($bundle_path)) {
            throw new RuntimeException('Bundled image is missing: ' . $filename);
        }

        if (!file_exists($upload_path)) {
            if (!wp_mkdir_p(dirname($upload_path)) || !copy($bundle_path, $upload_path)) {
                throw new RuntimeException('Could not copy bundled image: ' . $filename);
            }
        } elseif (hash_file('sha256', $upload_path) !== hash_file('sha256', $bundle_path)) {
            throw new RuntimeException('Refusing to overwrite a different uploads file: ' . $relative);
        }

        if (!$attachment_id) {
            $attachment_id = wp_insert_attachment(
                array(
                    'post_mime_type' => 'image/webp',
                    'post_title'     => $title,
                    'post_excerpt'   => $caption,
                    'post_status'    => 'inherit',
                    'post_parent'    => $product_id,
                ),
                $upload_path,
                $product_id,
                true
            );

            if (is_wp_error($attachment_id)) {
                throw new RuntimeException('Could not create attachment ' . $filename . ': ' . $attachment_id->get_error_message());
            }
        }

        update_post_meta($attachment_id, '_wp_attached_file', $relative);
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
        wp_update_post(array(
            'ID'           => $attachment_id,
            'post_title'   => $title,
            'post_excerpt' => $caption,
            'post_parent'  => $product_id,
        ));

        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $existing_metadata = wp_get_attachment_metadata($attachment_id);

        if (empty($existing_metadata) || empty($existing_metadata['width']) || empty($existing_metadata['height'])) {
            $metadata = wp_generate_attachment_metadata($attachment_id, $upload_path);

            if (!empty($metadata)) {
                wp_update_attachment_metadata($attachment_id, $metadata);
            }
        }

        return (int) $attachment_id;
    }

    function vpn_canvas_tote_202609_find_product($definition) {
        $product = get_page_by_path($definition['slug'], OBJECT, 'product');

        if ($product instanceof WP_Post) {
            return $product;
        }

        $marker_matches = get_posts(array(
            'post_type'      => 'product',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'meta_key'       => '_vpn_canvas_tote_202609_slug',
            'meta_value'     => $definition['slug'],
        ));

        if ($marker_matches) {
            return $marker_matches[0];
        }

        $title_matches = get_posts(array(
            'post_type'      => 'product',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'title'          => $definition['title'],
        ));

        return $title_matches ? $title_matches[0] : null;
    }

    function vpn_canvas_tote_202609_category_id($slug, $name) {
        $term = get_term_by('slug', $slug, 'product_cat');

        if ($term && !is_wp_error($term)) {
            return (int) $term->term_id;
        }

        $parent = get_term_by('slug', 'custom-packaging-boxes', 'product_cat');
        $created = wp_insert_term($name, 'product_cat', array(
            'slug'   => $slug,
            'parent' => $parent && !is_wp_error($parent) ? (int) $parent->term_id : 0,
        ));

        if (is_wp_error($created)) {
            throw new RuntimeException('Could not create product category ' . $name . ': ' . $created->get_error_message());
        }

        return (int) $created['term_id'];
    }

    function vpn_canvas_tote_202609_tag_ids($slugs) {
        $term_ids = array();

        foreach ($slugs as $slug) {
            $term = get_term_by('slug', $slug, 'product_tag');

            if (!$term || is_wp_error($term)) {
                $created = wp_insert_term(ucwords(str_replace('-', ' ', $slug)), 'product_tag', array('slug' => $slug));

                if (is_wp_error($created)) {
                    throw new RuntimeException('Could not create product tag ' . $slug . ': ' . $created->get_error_message());
                }

                $term_ids[] = (int) $created['term_id'];
            } else {
                $term_ids[] = (int) $term->term_id;
            }
        }

        return $term_ids;
    }

    function vpn_canvas_tote_202609_figure($attachment_id, $alt, $caption, $slot) {
        $image = wp_get_attachment_image($attachment_id, 'large', false, array(
            'alt'      => $alt,
            'loading'  => 'lazy',
            'decoding' => 'async',
        ));

        if (!$image) {
            throw new RuntimeException('Could not render inline image attachment ' . $attachment_id);
        }

        return '<!-- stable-product-image:slot_' . (int) $slot . ' --><figure class="product-inline-figure product-inline-figure-small">' . $image . '<figcaption>' . esc_html($caption) . '</figcaption></figure>';
    }

    function vpn_canvas_tote_202609_insert_figures($html, $attachment_ids, $alt_map, $definitions_images) {
        $target_h2_positions = array(1, 3, 5, 7);
        $h2_index = 0;
        $figure_index = 0;

        $html = preg_replace_callback('/<h2\b[^>]*>.*?<\/h2>/is', function ($matches) use (&$h2_index, &$figure_index, $target_h2_positions, $attachment_ids, $alt_map, $definitions_images) {
            $h2_index++;

            if (!in_array($h2_index, $target_h2_positions, true) || $figure_index >= 4) {
                return $matches[0];
            }

            $image_index = $figure_index + 1;
            $filename = $definitions_images[$image_index];
            $base = pathinfo($filename, PATHINFO_FILENAME);
            $caption = ucfirst(str_replace('-', ' ', preg_replace('/^\d+-/', '', $base)));
            $figure_index++;

            return $matches[0] . vpn_canvas_tote_202609_figure($attachment_ids[$image_index], $alt_map[$base], $caption, $figure_index);
        }, $html);

        while ($figure_index < 4) {
            $image_index = $figure_index + 1;
            $filename = $definitions_images[$image_index];
            $base = pathinfo($filename, PATHINFO_FILENAME);
            $caption = ucfirst(str_replace('-', ' ', preg_replace('/^\d+-/', '', $base)));
            $figure_index++;
            $html .= vpn_canvas_tote_202609_figure($attachment_ids[$image_index], $alt_map[$base], $caption, $figure_index);
        }

        return $html;
    }

    function vpn_canvas_tote_202609_canonical_url($product_id) {
        $url = get_permalink($product_id);

        return $url ? $url : home_url('/product/');
    }

    function vpn_canvas_tote_202609_import_one($definition) {
        $source_path = vpn_canvas_tote_202609_source_path($definition);
        $source = file_exists($source_path) ? file_get_contents($source_path) : false;

        if (false === $source) {
            throw new RuntimeException('Source content is missing: ' . $source_path);
        }

        $source_title = '';

        if (preg_match('/^#\s+(Custom[^\r\n]+)$/mi', $source, $title_matches)) {
            $source_title = trim($title_matches[1]);
        }

        if (!$source_title) {
            throw new RuntimeException('Customer-facing product title is missing from ' . $source_path);
        }
        $source_slug = trim(vpn_canvas_tote_202609_source_meta($source, 'Proposed URL slug'), '/');
        $seo_title = vpn_canvas_tote_202609_source_meta($source, 'Meta title');
        $seo_description = vpn_canvas_tote_202609_source_meta($source, 'Meta description');
        $focus_keyword = vpn_canvas_tote_202609_source_meta($source, 'Primary keyword');
        $alt_map = vpn_canvas_tote_202609_source_image_alt_map($source);

        if ($definition['title'] !== $source_title || $definition['slug'] !== $source_slug || $definition['seo_title'] !== $seo_title) {
            throw new RuntimeException('Definition/source mismatch for ' . $definition['slug']);
        }

        if (6 !== count($definition['images'])) {
            throw new RuntimeException('Expected six images for ' . $definition['slug']);
        }

        foreach ($definition['images'] as $filename) {
            $base = pathinfo($filename, PATHINFO_FILENAME);

            if (empty($alt_map[$base])) {
                throw new RuntimeException('Missing source alt text for ' . $filename);
            }
        }

        $sections = vpn_canvas_tote_202609_customer_sections($source, $definition['title']);
        $product = vpn_canvas_tote_202609_find_product($definition);

        if (!$product) {
            $conflicts = get_posts(array(
                'name'           => $definition['slug'],
                'post_type'      => 'any',
                'post_status'    => 'any',
                'posts_per_page' => 1,
            ));

            if ($conflicts) {
                throw new RuntimeException('Slug is already used by a non-product post: ' . $definition['slug']);
            }

            $product_id = wp_insert_post(array(
                'post_type'   => 'product',
                'post_status' => 'draft',
                'post_title'  => $definition['title'],
                'post_name'   => $definition['slug'],
            ), true);

            if (is_wp_error($product_id)) {
                throw new RuntimeException('Could not create product: ' . $product_id->get_error_message());
            }
        } else {
            $product_id = (int) $product->ID;
        }

        $attachment_ids = array();

        foreach ($definition['images'] as $index => $filename) {
            $base = pathinfo($filename, PATHINFO_FILENAME);
            $caption = ucfirst(str_replace('-', ' ', preg_replace('/^\d+-/', '', $base)));
            $attachment_ids[] = vpn_canvas_tote_202609_attachment(
                $filename,
                $alt_map[$base],
                $definition['title'] . ' - ' . $caption,
                $caption,
                $product_id
            );
        }

        $content_html = vpn_canvas_tote_202609_insert_figures(
            $sections['body']['html'],
            $attachment_ids,
            $alt_map,
            $definition['images']
        );
        $faq_html = vpn_canvas_tote_202609_faq_html($sections['faq']);
        $category_id = vpn_canvas_tote_202609_category_id($definition['category_slug'], $definition['category_name']);
        $tag_ids = vpn_canvas_tote_202609_tag_ids($definition['tags']);
        $spec_rows = array();

        foreach ($definition['specs'] as $label => $value) {
            $spec_rows[] = array(
                'label' => $label,
                'value' => $value,
            );
        }

        $updated = wp_update_post(array(
            'ID'           => $product_id,
            'post_type'    => 'product',
            'post_status'  => 'publish',
            'post_title'   => $definition['title'],
            'post_name'    => $definition['slug'],
            'post_excerpt' => implode("\n", $sections['body']['intro']),
            'post_content' => $content_html,
        ), true);

        if (is_wp_error($updated)) {
            throw new RuntimeException('Could not save product ' . $definition['slug'] . ': ' . $updated->get_error_message());
        }

        if ($definition['slug'] !== get_post_field('post_name', $product_id)) {
            throw new RuntimeException('WordPress changed the requested slug for ' . $definition['slug']);
        }

        wp_set_object_terms($product_id, array($category_id), 'product_cat', false);
        wp_set_object_terms($product_id, $tag_ids, 'product_tag', false);
        wp_set_object_terms($product_id, array('simple'), 'product_type', false);
        set_post_thumbnail($product_id, $attachment_ids[0]);
        update_post_meta($product_id, '_product_image_gallery', implode(',', array_slice($attachment_ids, 1)));
        update_post_meta($product_id, '_stock_status', 'instock');
        update_post_meta($product_id, '_manage_stock', 'no');
        update_post_meta($product_id, '_visibility', 'visible');
        update_post_meta($product_id, '_custom_box_product_specs', $spec_rows);
        update_post_meta($product_id, '_custom_box_product_faq_html', $faq_html);
        update_post_meta($product_id, '_vpn_sample_import', VPN_CANVAS_TOTE_202609_MARKER);
        update_post_meta($product_id, '_vpn_canvas_tote_202609_slug', $definition['slug']);
        update_post_meta($product_id, 'rank_math_title', $seo_title);
        update_post_meta($product_id, 'rank_math_description', $seo_description);
        update_post_meta($product_id, 'rank_math_focus_keyword', $focus_keyword);
        update_post_meta($product_id, 'rank_math_canonical_url', vpn_canvas_tote_202609_canonical_url($product_id));
        update_post_meta($product_id, 'rank_math_robots', array('index', 'follow'));
        update_post_meta($product_id, 'rank_math_facebook_title', $seo_title);
        update_post_meta($product_id, 'rank_math_facebook_description', $seo_description);
        update_post_meta($product_id, 'rank_math_facebook_image_id', $attachment_ids[0]);
        update_post_meta($product_id, 'rank_math_facebook_image', wp_get_attachment_url($attachment_ids[0]));
        update_post_meta($product_id, 'rank_math_twitter_title', $seo_title);
        update_post_meta($product_id, 'rank_math_twitter_description', $seo_description);
        update_post_meta($product_id, 'rank_math_twitter_image_id', $attachment_ids[0]);
        update_post_meta($product_id, 'rank_math_twitter_image', wp_get_attachment_url($attachment_ids[0]));
        update_post_meta($product_id, 'rank_math_twitter_card_type', 'summary_large_image');

        return array(
            'id'          => (int) $product_id,
            'title'       => $definition['title'],
            'slug'        => $definition['slug'],
            'url'         => get_permalink($product_id),
            'category'    => $definition['category_name'],
            'attachments' => $attachment_ids,
            'seo_title'   => $seo_title,
            'description' => $seo_description,
        );
    }

    function vpn_canvas_tote_202609_run_import() {
        if (!function_exists('wp_insert_post') || !function_exists('wp_upload_dir')) {
            throw new RuntimeException('WordPress is not fully loaded.');
        }

        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        $results = array();

        foreach (vpn_canvas_tote_202609_product_definitions() as $definition) {
            $results[] = vpn_canvas_tote_202609_import_one($definition);
        }

        return $results;
    }
}
