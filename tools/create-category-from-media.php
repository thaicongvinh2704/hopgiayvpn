<?php
require dirname(__DIR__) . '/wp-load.php';

$parent = get_term_by('slug', 'custom-packaging-boxes', 'category');

if (!$parent || is_wp_error($parent)) {
    fwrite(STDERR, "Missing parent category: custom-packaging-boxes\n");
    exit(1);
}

$attachments = get_posts(array(
    'post_type'      => 'attachment',
    'post_status'    => 'inherit',
    'posts_per_page' => -1,
    'post_mime_type' => 'image',
    'orderby'        => 'title',
    'order'          => 'ASC',
));

echo "Parent: {$parent->term_id} {$parent->name}\n";
echo "Images: " . count($attachments) . "\n";

foreach ($attachments as $attachment) {
    $file = get_attached_file($attachment->ID);
    $base = $file ? pathinfo($file, PATHINFO_FILENAME) : $attachment->post_title;
    $name = trim(preg_replace('/[-_]+/', ' ', $base));
    $name = preg_replace('/\s+/', ' ', $name);
    $name = ucwords($name);

    if ($name === '') {
        continue;
    }

    $slug = sanitize_title($name);
    $term = get_term_by('slug', $slug, 'category');

    if (!$term || is_wp_error($term)) {
        $created = wp_insert_term($name, 'category', array(
            'slug'   => $slug,
            'parent' => (int) $parent->term_id,
        ));

        if (is_wp_error($created)) {
            echo "ERROR create {$name}: " . $created->get_error_message() . "\n";
            continue;
        }

        $term_id = (int) $created['term_id'];
        echo "CREATED {$term_id}: {$name}\n";
    } else {
        $term_id = (int) $term->term_id;

        if ((int) $term->parent !== (int) $parent->term_id) {
            wp_update_term($term_id, 'category', array(
                'parent' => (int) $parent->term_id,
            ));
        }

        echo "EXISTS {$term_id}: {$name}\n";
    }

    update_term_meta($term_id, 'custom_box_category_image_id', (int) $attachment->ID);
}

$children = get_terms(array(
    'taxonomy'   => 'category',
    'parent'     => (int) $parent->term_id,
    'hide_empty' => false,
));

echo "Children now: " . count($children) . "\n";
