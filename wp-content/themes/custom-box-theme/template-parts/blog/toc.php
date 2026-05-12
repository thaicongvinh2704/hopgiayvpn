<?php
/**
 * Sticky table of contents.
 */

defined('ABSPATH') || exit;

$toc = isset($args['toc']) && is_array($args['toc']) ? $args['toc'] : array();
?>

<?php if (!empty($toc)) : ?>
    <aside class="blog-toc" aria-label="<?php esc_attr_e('Table of contents', 'custom-box-theme'); ?>">
        <button class="blog-toc-toggle" type="button" aria-expanded="false">
            <span><?php esc_html_e('Table of Contents', 'custom-box-theme'); ?></span>
            <i class="fas fa-chevron-down"></i>
        </button>
        <nav class="blog-toc-panel">
            <?php foreach ($toc as $item) : ?>
                <a class="blog-toc-link blog-toc-level-<?php echo esc_attr($item['level']); ?>" href="#<?php echo esc_attr($item['id']); ?>">
                    <?php echo esc_html($item['title']); ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>
<?php endif; ?>
