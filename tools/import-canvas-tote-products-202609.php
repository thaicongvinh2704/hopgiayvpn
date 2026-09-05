<?php

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

require_once get_template_directory() . '/inc/product-sample-deploy-tools/import-canvas-tote-products-202609.php';

try {
    $results = vpn_canvas_tote_202609_run_import();
    echo wp_json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
