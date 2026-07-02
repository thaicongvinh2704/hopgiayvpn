<?php
/**
 * Deploy the Magnetic Closure Boxes product batch.
 *
 * Usage after git pull on hosting:
 *   php tools/deploy-magnetic-closure-products.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

$root = dirname(__DIR__);
chdir($root);

function vpn_magnetic_deploy_run_script($relative_script)
{
    global $root;

    $script = $root . '/' . $relative_script;
    if (!file_exists($script)) {
        fwrite(STDERR, 'Missing script: ' . $relative_script . PHP_EOL);
        exit(1);
    }

    echo PHP_EOL . '>> ' . $relative_script . PHP_EOL;
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script);
    passthru($command, $exit_code);

    if (0 !== $exit_code) {
        fwrite(STDERR, 'Script failed: ' . $relative_script . PHP_EOL);
        exit(1);
    }
}

function vpn_magnetic_deploy_required_images()
{
    $products = array(
        'custom-perfume-magnetic-closure-box' => array('main', 'open', 'gallery', 'detail'),
        'custom-skincare-magnetic-closure-box' => array('open', 'gallery', 'detail'),
        'custom-jewelry-magnetic-closure-box' => array('main', 'open', 'gallery', 'detail'),
        'custom-watch-magnetic-presentation-box' => array('main', 'open', 'gallery', 'detail'),
        'custom-electronics-magnetic-closure-box' => array('main', 'open', 'gallery', 'detail'),
        'custom-candle-magnetic-gift-box' => array('main', 'open', 'gallery', 'detail'),
        'custom-chocolate-magnetic-closure-box' => array('main', 'open', 'gallery', 'detail'),
        'custom-apparel-magnetic-gift-box' => array('main', 'open', 'gallery', 'detail'),
        'custom-corporate-pr-kit-magnetic-box' => array('main', 'open', 'gallery', 'detail'),
        'custom-wine-magnetic-gift-box' => array('main', 'open', 'gallery', 'detail'),
    );

    $images = array();
    foreach ($products as $slug => $suffixes) {
        foreach ($suffixes as $suffix) {
            $images[] = $slug . '-' . $suffix . '.webp';
        }
    }

    return $images;
}

function vpn_magnetic_deploy_check_images()
{
    $missing = array();

    foreach (vpn_magnetic_deploy_required_images() as $filename) {
        $relative = '2026/07/' . $filename;
        $upload = WP_CONTENT_DIR . '/uploads/' . $relative;
        $bundled = function_exists('get_template_directory')
            ? get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $relative
            : '';

        if (!file_exists($upload) && (!$bundled || !file_exists($bundled))) {
            $missing[] = $filename;
        }
    }

    if ($missing) {
        fwrite(STDERR, 'Missing magnetic product source images:' . PHP_EOL);
        foreach ($missing as $filename) {
            fwrite(STDERR, '- ' . $filename . PHP_EOL);
        }
        exit(1);
    }

    echo 'Magnetic source images found: ' . count(vpn_magnetic_deploy_required_images()) . PHP_EOL;
}

vpn_magnetic_deploy_check_images();
vpn_magnetic_deploy_run_script('tools/import-magnetic-closure-products.php');
vpn_magnetic_deploy_run_script('tools/verify-magnetic-closure-products.php');

echo PHP_EOL . 'Magnetic closure product deployment complete.' . PHP_EOL;
