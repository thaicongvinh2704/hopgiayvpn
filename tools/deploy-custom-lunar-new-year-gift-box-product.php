<?php
/**
 * Deploy the Custom Lunar New Year Gift Boxes product after git pull.
 *
 * Usage:
 *   php tools/deploy-custom-lunar-new-year-gift-box-product.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

$root = dirname(__DIR__);

require $root . '/tools/import-custom-lunar-new-year-gift-box-product.php';
require $root . '/tools/verify-custom-lunar-new-year-gift-box-product.php';

echo PHP_EOL . 'Custom Lunar New Year Gift Boxes deployment complete.' . PHP_EOL;
