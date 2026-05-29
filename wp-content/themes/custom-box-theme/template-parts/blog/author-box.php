<?php
/**
 * Blog author box for EEAT signals.
 */

defined('ABSPATH') || exit;

$author_id = (int) get_the_author_meta('ID');
$fallback_name = 'VPN Packaging Editorial Team';
$fallback_title = 'Packaging Content Specialist & Paper Packaging Consultant';
$fallback_bio = 'VPN Packaging Editorial Team shares practical insights about custom paper boxes, rigid boxes, folding cartons, paper bags, printing finishes, materials, and B2B packaging solutions. Our content is developed based on real production experience and customer packaging projects from VPN Packaging Factory in Vietnam.';

$author_name = trim((string) get_the_author());
$generic_author_names = array('admin', 'administrator');
if ('' === $author_name || in_array(strtolower($author_name), $generic_author_names, true)) {
    $author_name = $fallback_name;
}

$author_title = '';
foreach (array('job_title', 'title', 'position', 'custom_box_author_title') as $title_meta_key) {
    $meta_title = trim((string) get_the_author_meta($title_meta_key, $author_id));
    if ('' !== $meta_title) {
        $author_title = $meta_title;
        break;
    }
}
if ('' === $author_title) {
    $author_title = $fallback_title;
}

$author_bio = trim((string) get_the_author_meta('description', $author_id));
if ('' === $author_bio) {
    $author_bio = $fallback_bio;
}

$author_url = $author_id ? get_author_posts_url($author_id) : home_url('/author/');
$author_logo_url = get_template_directory_uri() . '/assets/images/logo-hop-giay-vpn-hcm.png';
$custom_logo_id = (int) get_theme_mod('custom_logo');
if ($custom_logo_id) {
    $custom_logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
    if ($custom_logo_url) {
        $author_logo_url = $custom_logo_url;
    }
}
?>

<aside class="vpn-author-box" aria-labelledby="vpn-author-box-title">
    <div class="vpn-author-avatar">
        <img
            class="vpn-author-avatar-img"
            src="<?php echo esc_url($author_logo_url); ?>"
            alt="<?php echo esc_attr($author_name); ?>"
            loading="lazy"
            decoding="async"
        >
    </div>

    <div class="vpn-author-content">
        <span class="vpn-author-label"><?php esc_html_e('About the author', 'custom-box-theme'); ?></span>
        <h2 id="vpn-author-box-title" class="vpn-author-name"><?php echo esc_html($author_name); ?></h2>
        <p class="vpn-author-title"><?php echo esc_html($author_title); ?></p>
        <p class="vpn-author-bio"><?php echo esc_html($author_bio); ?></p>
        <a class="vpn-author-link" href="<?php echo esc_url($author_url); ?>">
            <?php esc_html_e('View Author Profile', 'custom-box-theme'); ?>
        </a>
    </div>
</aside>
