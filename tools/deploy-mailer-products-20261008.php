<?php
/** Deploy this batch independently without running historical imports. */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI required.'); }
require_once dirname(__DIR__) . '/wp-load.php';
require get_template_directory() . '/inc/product-sample-deploy-tools/import-mailer-products-20261008.php';
require get_template_directory() . '/inc/product-sample-deploy-tools/verify-mailer-products-20261008.php';
