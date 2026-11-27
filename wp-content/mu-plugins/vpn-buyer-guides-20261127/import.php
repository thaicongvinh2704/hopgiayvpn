<?php
defined('ABSPATH') || exit;

function vpn_buyer_guides_require(bool $valid, string $message): void {
    if (!$valid) {
        throw new RuntimeException($message);
    }
}

function vpn_buyer_guides_hash(string $value): string {
    return hash('sha256', trim(str_replace(["\r\n", "\r"], "\n", $value)));
}

function vpn_buyer_guides_category(string $slug): string {
    $term = get_term_by('slug', $slug, 'product_cat');
    vpn_buyer_guides_require($term instanceof WP_Term, 'Missing shop category: ' . $slug);
    $url = get_term_link($term);
    vpn_buyer_guides_require(is_string($url) && $url !== '', 'Missing shop category URL: ' . $slug);
    return $url;
}

function vpn_buyer_guides_product_link(array $product): array {
    $post = get_page_by_path($product['slug'], OBJECT, 'product');
    if ($post && $post->post_status === 'publish' && $post->post_password === '') {
        $wc = wc_get_product($post->ID);
        if ($wc && $wc->get_catalog_visibility() !== 'hidden') {
            return ['markup' => '<li><a href="' . esc_url(get_permalink($post)) . '">' .
                esc_html($product['anchor']) . '</a></li>', 'fallback' => false];
        }
    }
    $category = $product['fallback_category'];
    return ['markup' => $category ? '<li><a href="' . esc_url(vpn_buyer_guides_category($category)) .
        '">custom box catalog</a></li>' : '', 'fallback' => true];
}

function vpn_buyer_guides_heading(string $value): string {
    return trim(str_replace('’', "'", html_entity_decode(wp_strip_all_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}

function vpn_buyer_guides_incoming(array $spec, string $slug): array {
    $post = get_page_by_path($spec['slug'], OBJECT, 'post');
    vpn_buyer_guides_require($post && $post->post_status === 'publish', 'Missing original article: ' . $spec['slug']);
    $path = user_trailingslashit('/' . $slug . '/', 'single');
    $target = home_url($path);
    $addition = '<p>' . wp_kses_post(str_replace('{{HOME}}', untrailingslashit(home_url('/')), $spec['text'])) . '</p>';
    vpn_buyer_guides_require(str_contains($addition, 'href="' . esc_url($target) . '"'),
        'Check permalink settings for ' . $slug);
    $body = $post->post_content;
    if (str_contains($body, $addition)) {
        return ['id' => $post->ID, 'before' => $body, 'after' => $body];
    }
    vpn_buyer_guides_require(!str_contains($body, 'href="' . esc_url($target) . '"'),
        'Review the existing link in ' . $spec['slug']);
    preg_match_all('~(?:<!--\s+wp:heading(?:\s+\{[^\r\n]*\})?\s+-->\s*)?<h2\b[^>]*>(.*?)</h2>~is',
        $body, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    $matching = array_values(array_filter($matches, static fn(array $match): bool =>
        vpn_buyer_guides_heading($match[1][0]) === vpn_buyer_guides_heading($spec['before'])));
    vpn_buyer_guides_require(count($matching) === 1, 'Review the link placement in ' . $spec['slug']);
    [$markup, $offset] = $matching[0][0];
    if (str_starts_with($markup, '<!--')) {
        $addition = "<!-- wp:paragraph -->\n" . $addition . "\n<!-- /wp:paragraph -->";
    }
    return ['id' => $post->ID, 'before' => $body, 'after' => substr_replace($body, $addition . "\n\n", $offset, 0)];
}

function vpn_buyer_guides_preflight(): array {
    global $wpdb;
    vpn_buyer_guides_require(defined('RANK_MATH_VERSION') && function_exists('wc_get_product'), 'Activate Rank Math and WooCommerce.');
    vpn_buyer_guides_require(wp_image_editor_supports(['mime_type' => 'image/webp']), 'WordPress requires WebP image support.');
    $category = get_term_by('slug', 'packaging-guides', 'category');
    vpn_buyer_guides_require($category instanceof WP_Term, 'Missing Packaging Guides blog category.');
    $rows = json_decode(file_get_contents(__DIR__ . '/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
    vpn_buyer_guides_require(count($rows) === 3, 'Expected three approved articles.');
    $home = untrailingslashit(home_url('/'));
    $files = [];
    foreach ($rows as &$row) {
        $slug = $row['slug'];
        vpn_buyer_guides_require($slug === sanitize_title($slug), 'Invalid article slug.');
        $path = __DIR__ . '/content/' . $slug . '.html';
        $raw_content = is_file($path) && is_readable($path) ? file_get_contents($path) : false;
        vpn_buyer_guides_require(is_string($raw_content), 'Missing or unreadable article source: ' . $slug);
        // Validate the full file with LF before trimming it for WordPress.
        $source_content = str_replace(["\r\n", "\r"], "\n", $raw_content);
        vpn_buyer_guides_require(hash_equals($row['content_sha256'], hash('sha256', $source_content)),
            'Article content checksum differs: ' . $slug);
        $content = trim($source_content);
        vpn_buyer_guides_require(substr_count($content, '<h2>') === 8 && substr_count($content, '{{PRODUCT_OPTION}}') === 1 &&
            str_starts_with(strtolower($row['seo_title']), strtolower($row['keyword'])) &&
            str_contains($row['description'], 'Vietnam') && count($row['images']) === 4, 'Incomplete article: ' . $slug);
        foreach ($row['category_dependencies'] as $dependency) {
            vpn_buyer_guides_category($dependency);
        }
        $option = vpn_buyer_guides_product_link($row['product']);
        $row['body'] = str_replace(['{{HOME}}', '{{PRODUCT_OPTION}}'], [$home, $option['markup']], $content);
        $row['fallback'] = $option['fallback'];
        $row['category_id'] = (int) $category->term_id;
        $row['incoming_patch'] = vpn_buyer_guides_incoming($row['incoming'], $slug);

        $matches = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'post' AND post_name IN (%s, %s)", $slug, $slug . '__trashed'
        ));
        vpn_buyer_guides_require(count($matches) <= 1, 'Duplicate article slug: ' . $slug);
        $existing = $matches ? get_post((int) $matches[0]) : null;
        if ($existing) {
            $owned = get_post_meta($existing->ID, '_vpn_buyer_guides_release', true) === VPN_BUYER_GUIDES_RELEASE;
            $baseline = $row['baseline'];
            $known_draft = $existing->post_title === $baseline['title'] &&
                vpn_buyer_guides_hash($existing->post_content) === $baseline['content_sha256'] &&
                vpn_buyer_guides_hash($existing->post_excerpt) === $baseline['excerpt_sha256'];
            vpn_buyer_guides_require($existing->post_name === $slug && $existing->post_status === 'draft' &&
                $existing->post_password === '' && ($owned || $known_draft), 'Review the existing article before updating: ' . $slug);
        }
        $row['post'] = $existing;
        foreach ($row['images'] as &$image) {
            $name = $image['name'];
            $source = dirname(__DIR__, 3) . '/' . $image['path'];
            vpn_buyer_guides_require(!isset($files[$name]) && preg_match('/^[a-z0-9-]+\.webp$/D', $name) &&
                is_file($source) && hash_file('sha256', $source) === $image['sha256'] &&
                wp_get_image_mime($source) === 'image/webp' && trim($image['alt']) !== '', 'Invalid media: ' . $name);
            $files[$name] = true;
            $image['source'] = $source;
            if ($image['placement'] !== null) {
                vpn_buyer_guides_require(substr_count($row['body'], $image['placement']) === 1, 'Missing image position: ' . $name);
            }
        }
        unset($image);
    }
    unset($row);
    return $rows;
}

function vpn_buyer_guides_attach(array $image, string $basedir, int $post_id): int {
    global $wpdb;
    $relative = '2026/11/' . VPN_BUYER_GUIDES_RELEASE . '/' . $image['name'];
    $target = trailingslashit($basedir) . $relative;
    $matches = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value = %s", $relative
    ));
    vpn_buyer_guides_require(count($matches) <= 1, 'Duplicate attachment: ' . $image['name']);
    if ($matches) {
        $id = (int) $matches[0];
        vpn_buyer_guides_require(get_post_type($id) === 'attachment' && (int) wp_get_post_parent_id($id) === $post_id,
            'Attachment belongs to another article: ' . $image['name']);
    }
    if (!is_file($target)) {
        $temp = wp_tempnam($image['name'], dirname($target));
        vpn_buyer_guides_require(is_string($temp), 'Cannot stage image: ' . $image['name']);
        try {
            vpn_buyer_guides_require(copy($image['source'], $temp) && hash_file('sha256', $temp) === $image['sha256'] &&
                rename($temp, $target), 'Cannot copy image: ' . $image['name']);
        } finally {
            if (is_file($temp)) {
                wp_delete_file($temp);
            }
        }
    }
    vpn_buyer_guides_require(hash_file('sha256', $target) === $image['sha256'], 'Image checksum differs: ' . $image['name']);
    if (!$matches) {
        $id = wp_insert_attachment(wp_slash([
            'post_parent' => $post_id, 'post_status' => 'inherit', 'post_mime_type' => 'image/webp',
            'post_title' => $image['title'], 'post_excerpt' => $image['caption'],
        ]), $target, $post_id, true);
        vpn_buyer_guides_require(is_int($id) && $id > 0, 'Unable to create media: ' . $image['name']);
    }
    update_post_meta($id, '_wp_attachment_image_alt', wp_slash($image['alt']));
    $metadata = wp_get_attachment_metadata($id);
    if (empty($metadata['sizes'])) {
        $metadata = wp_generate_attachment_metadata($id, $target);
        vpn_buyer_guides_require(!empty($metadata['sizes']), 'Unable to generate image sizes: ' . $image['name']);
        wp_update_attachment_metadata($id, $metadata);
    }
    vpn_buyer_guides_require(get_post_meta($id, '_wp_attachment_image_alt', true) === $image['alt'] &&
        wp_get_attachment_caption($id) === $image['caption'], 'Image description differs: ' . $image['name']);
    return $id;
}

function vpn_buyer_guides_stage(array $row, string $basedir): array {
    if ($row['post']) {
        $post_id = $row['post']->ID;
        $current = get_post($post_id, ARRAY_A, 'raw');
        vpn_buyer_guides_require($current && $current['post_status'] === 'draft' &&
            $current['post_content'] === $row['post']->post_content &&
            $current['post_modified_gmt'] === $row['post']->post_modified_gmt, 'The draft changed during the update.');
    } else {
        $post_id = wp_insert_post(wp_slash([
            'post_type' => 'post', 'post_status' => 'draft', 'post_name' => $row['slug'], 'post_title' => $row['title'],
            'meta_input' => ['_vpn_buyer_guides_release' => VPN_BUYER_GUIDES_RELEASE],
        ]), true);
        vpn_buyer_guides_require(is_int($post_id) && $post_id > 0, 'Unable to create article: ' . $row['slug']);
    }
    update_post_meta($post_id, '_vpn_buyer_guides_release', VPN_BUYER_GUIDES_RELEASE);
    $body = $row['body'];
    $ids = [];
    foreach ($row['images'] as $index => $image) {
        $attachment = vpn_buyer_guides_attach($image, $basedir, $post_id);
        $ids[] = $attachment;
        if ($index === 0) {
            set_post_thumbnail($post_id, $attachment);
            continue;
        }
        $factory = $image['placement'] === '<!-- vpn-workshop-photo -->';
        $picture = wp_get_attachment_image($attachment, 'medium_large', false, [
            'loading' => 'lazy', 'decoding' => 'async',
            'class' => $factory ? 'vpn-blog-factory-photo' : 'attachment-medium_large size-medium_large',
        ]);
        vpn_buyer_guides_require($picture !== '', 'Unable to render ' . $image['name']);
        $figure = '<figure class="wp-block-image size-medium_large' . ($factory ? ' vpn-blog-factory-evidence' : '') . '">' .
            $picture . '<figcaption>' . esc_html($image['caption']) . '</figcaption></figure>';
        $body = str_replace($image['placement'], $factory ? $figure : $figure . "\n\n" . $image['placement'], $body);
    }
    $result = wp_update_post(wp_slash([
        'ID' => $post_id, 'post_name' => $row['slug'], 'post_title' => $row['title'],
        'post_excerpt' => $row['excerpt'], 'post_content' => $body, 'post_category' => [$row['category_id']],
        'comment_status' => 'closed', 'ping_status' => 'closed',
    ]), true);
    vpn_buyer_guides_require($result === $post_id, 'Unable to stage ' . $row['slug']);
    $fields = [
        'rank_math_title' => $row['seo_title'], 'rank_math_description' => $row['description'],
        'rank_math_focus_keyword' => $row['keyword'], 'rank_math_facebook_title' => $row['seo_title'],
        'rank_math_facebook_description' => $row['description'],
    ];
    foreach ($fields as $key => $value) {
        update_post_meta($post_id, $key, wp_slash($value));
        vpn_buyer_guides_require(get_post_meta($post_id, $key, true) === $value, 'Unable to verify ' . $key);
    }
    update_post_meta($post_id, '_vpn_blog_evidence_20261127', '1');
    $draft = get_post($post_id);
    vpn_buyer_guides_require($draft->post_status === 'draft' && $draft->post_name === $row['slug'] &&
        $draft->post_content === $body && $draft->post_excerpt === $row['excerpt'] &&
        (int) get_post_thumbnail_id($post_id) === $ids[0] &&
        count(array_unique($ids)) === 4 && substr_count($body, '<figure') === 3, 'Draft could not be verified: ' . $row['slug']);
    return ['id' => $post_id, 'title' => $row['title'], 'images' => $ids, 'fallback' => $row['fallback']];
}

function vpn_buyer_guides_publish(): array {
    global $wpdb;
    vpn_buyer_guides_require(vpn_buyer_guides_release_allowed(), 'An authorized WordPress administrator is required.');
    $lock = 'vpn_buyer_guides_' . md5(DB_NAME . $wpdb->prefix);
    vpn_buyer_guides_require((string) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock)) === '1', 'Another blog update is running.');
    try {
        $state = get_option(VPN_BUYER_GUIDES_RELEASE, []);
        if (isset($state['articles'])) {
            return $state['articles'];
        }
        $rows = vpn_buyer_guides_preflight();
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $uploads = wp_upload_dir(null, false);
        vpn_buyer_guides_require(empty($uploads['error']) && wp_mkdir_p(trailingslashit($uploads['basedir']) .
            '2026/11/' . VPN_BUYER_GUIDES_RELEASE), 'WordPress uploads are unavailable.');
        $articles = [];
        foreach ($rows as $row) {
            $articles[$row['slug']] = vpn_buyer_guides_stage($row, $uploads['basedir']);
        }

        $published = $written_links = [];
        try {
            foreach ($rows as $row) {
                $slug = $row['slug'];
                $id = $articles[$slug]['id'];
                $published[] = $id;
                $result = wp_update_post(['ID' => $id, 'post_status' => 'publish'], true);
                vpn_buyer_guides_require($result === $id && get_post_status($id) === 'publish' &&
                    get_permalink($id) === home_url(user_trailingslashit('/' . $slug . '/', 'single')),
                    'Article permalink or status differs: ' . $slug);
                $articles[$slug]['url'] = get_permalink($id);
            }
            foreach ($rows as $row) {
                $patch = $row['incoming_patch'];
                if ($patch['before'] === $patch['after']) {
                    continue;
                }
                $id = $patch['id'];
                vpn_buyer_guides_require(get_post_field('post_content', $id, 'raw') === $patch['before'],
                    'Existing article changed during the update.');
                $written_links[] = $patch;
                $result = wp_update_post(wp_slash(['ID' => $id, 'post_content' => $patch['after']]), true);
                vpn_buyer_guides_require($result === $id && get_post_field('post_content', $id, 'raw') === $patch['after'],
                    'Unable to verify the context link for ' . $row['slug']);
            }
            if (class_exists('RankMath\Sitemap\Cache')) {
                \RankMath\Sitemap\Cache::invalidate_storage();
            }
            vpn_buyer_guides_require(update_option(VPN_BUYER_GUIDES_RELEASE, ['articles' => $articles], false),
                'Unable to record the completed update.');
        } catch (Throwable $error) {
            foreach ($written_links as $patch) {
                wp_update_post(wp_slash(['ID' => $patch['id'], 'post_content' => $patch['before']]));
            }
            foreach ($published as $id) {
                wp_update_post(['ID' => $id, 'post_status' => 'draft']);
            }
            throw $error;
        }
        return $articles;
    } finally {
        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
    }
}
