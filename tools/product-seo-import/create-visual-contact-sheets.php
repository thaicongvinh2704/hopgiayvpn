<?php
/**
 * Create one top-of-page visual contact sheet per batch from Chrome screenshots.
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$screenshotsRoot = $root . '/artifacts/product-seo-release-checkpoint-v1/screenshots';
$outputRoot = $root . '/artifacts/product-seo-release-checkpoint-v1/contact-sheets';

if (!extension_loaded('gd')) {
    fwrite(STDERR, "GD extension is required.\n");
    exit(2);
}
if (!is_dir($outputRoot) && !mkdir($outputRoot, 0775, true) && !is_dir($outputRoot)) {
    fwrite(STDERR, "Cannot create contact-sheet directory.\n");
    exit(2);
}

function drawCrop(
    GdImage $canvas,
    string $sourcePath,
    int $targetX,
    int $targetY,
    int $targetWidth,
    int $sourceCropHeight
): int {
    $source = imagecreatefrompng($sourcePath);
    if (!$source) {
        throw new RuntimeException("Cannot open {$sourcePath}");
    }
    $sourceWidth = imagesx($source);
    $sourceHeight = imagesy($source);
    $cropHeight = min($sourceCropHeight, $sourceHeight);
    $targetHeight = (int) round($cropHeight * ($targetWidth / $sourceWidth));
    imagecopyresampled(
        $canvas,
        $source,
        $targetX,
        $targetY,
        0,
        0,
        $targetWidth,
        $targetHeight,
        $sourceWidth,
        $cropHeight
    );
    imagedestroy($source);
    return $targetHeight;
}

$created = [];
for ($batch = 1; $batch <= 17; $batch++) {
    $batchId = sprintf('BATCH-%02d', $batch);
    $batchDir = $screenshotsRoot . '/' . $batchId;
    $desktop = glob($batchDir . '/*-desktop-1440-viewport.png') ?: [];
    $mobile = glob($batchDir . '/*-mobile-390-viewport.png') ?: [];
    sort($desktop, SORT_STRING);
    sort($mobile, SORT_STRING);
    if (2 !== count($desktop) || 2 !== count($mobile)) {
        throw new RuntimeException("{$batchId} must contain two desktop and two mobile screenshots.");
    }

    $canvas = imagecreatetruecolor(1800, 2200);
    $white = imagecolorallocate($canvas, 250, 250, 250);
    $black = imagecolorallocate($canvas, 22, 22, 22);
    $border = imagecolorallocate($canvas, 180, 180, 180);
    imagefill($canvas, 0, 0, $white);
    imagestring($canvas, 5, 20, 16, "{$batchId} — desktop 1440 and mobile 390 top-page QA", $black);

    foreach ([0, 1] as $index) {
        $x = 25 + ($index * 875);
        imagestring($canvas, 4, $x, 52, basename($desktop[$index]), $black);
        imagerectangle($canvas, $x - 1, 79, $x + 851, 1025, $border);
        drawCrop($canvas, $desktop[$index], $x, 80, 850, 1600);
    }
    foreach ([0, 1] as $index) {
        $x = 210 + ($index * 900);
        imagestring($canvas, 4, $x, 1050, basename($mobile[$index]), $black);
        imagerectangle($canvas, $x - 1, 1079, $x + 451, 2100, $border);
        drawCrop($canvas, $mobile[$index], $x, 1080, 450, 1800);
    }

    $output = $outputRoot . '/' . $batchId . '-viewport-contact-sheet-v2.png';
    imagepng($canvas, $output, 6);
    imagedestroy($canvas);
    $created[] = [
        'batch_id' => $batchId,
        'path' => str_replace('\\', '/', substr($output, strlen($root) + 1)),
        'bytes' => filesize($output),
        'sha256' => hash_file('sha256', $output),
    ];
}

file_put_contents(
    $outputRoot . '/contact-sheet-manifest.json',
    json_encode(
        [
            'created_at_utc' => gmdate('c'),
            'sheet_count' => count($created),
            'sheets' => $created,
        ],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) . PHP_EOL
);

echo json_encode(
    [
        'ok' => true,
        'sheets' => count($created),
        'output' => $outputRoot,
    ],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
) . PHP_EOL;
