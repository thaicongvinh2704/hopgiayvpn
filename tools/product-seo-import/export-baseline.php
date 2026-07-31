<?php
declare(strict_types=1);

const ROOT_DIR = 'C:\\xampp\\htdocs\\hopgiayvpn';
const DB_NAME = 'hopgiayvpnmoi';
const DB_HOST = '127.0.0.1';
const DB_USER = 'root';
const DB_PASSWORD = '';
const TABLE_PREFIX = 'wp_';
const OUTPUT_DIR = ROOT_DIR . '\\artifacts\\product-seo-final-v1\\backups';

function fail(string $message): never
{
    fwrite(STDERR, "ERROR: {$message}\n");
    exit(1);
}

function sha256File(string $path): string
{
    $hash = hash_file('sha256', $path);
    if ($hash === false) {
        fail("Cannot hash {$path}");
    }
    return strtoupper($hash);
}

if (!is_file(ROOT_DIR . '\\wp-load.php') || !is_file(ROOT_DIR . '\\wp-config.php')) {
    fail('WordPress source files are missing.');
}

if (!is_dir(OUTPUT_DIR) && !mkdir(OUTPUT_DIR, 0775, true) && !is_dir(OUTPUT_DIR)) {
    fail('Cannot create backup directory.');
}

$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
    DB_USER,
    DB_PASSWORD,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ]
);
$pdo->exec('SET SESSION TRANSACTION READ ONLY');
$pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');

$database = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
$txReadOnly = (int) $pdo->query('SELECT @@tx_read_only')->fetchColumn();
if ($database !== DB_NAME || $txReadOnly !== 1) {
    fail('Database identity or read-only transaction check failed.');
}

$tables = $pdo->query(
    "SELECT TABLE_NAME
     FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = " . $pdo->quote(DB_NAME) . "
       AND TABLE_NAME LIKE " . $pdo->quote(TABLE_PREFIX . '%') . "
     ORDER BY TABLE_NAME"
)->fetchAll(PDO::FETCH_COLUMN);
if (count($tables) !== 68) {
    fail('Unexpected WordPress table count: ' . count($tables));
}

$tableChecksums = [];
foreach ($tables as $table) {
    if (!preg_match('/^wp_[A-Za-z0-9_]+$/', (string) $table)) {
        fail("Unsafe table identifier: {$table}");
    }
    $row = $pdo->query("CHECKSUM TABLE `{$table}`")->fetch();
    $tableChecksums[(string) $table] = isset($row['Checksum']) ? (string) $row['Checksum'] : '';
}

$products = $pdo->query(
    "SELECT ID, post_title, post_name, post_status, post_excerpt, post_content,
            post_date, post_modified, post_parent, menu_order, comment_status, ping_status
     FROM wp_posts
     WHERE post_type = 'product' AND post_status = 'publish'
     ORDER BY ID"
)->fetchAll();
if (count($products) !== 179) {
    fail('Expected 179 published products, found ' . count($products));
}

$productIds = array_map(static fn(array $row): int => (int) $row['ID'], $products);
$idList = implode(',', $productIds);
$metaKeys = [
    'rank_math_title',
    'rank_math_description',
    'rank_math_focus_keyword',
    'rank_math_canonical_url',
    '_thumbnail_id',
    '_product_image_gallery',
    '_sku',
    '_price',
    '_regular_price',
    '_sale_price',
    '_stock',
    '_stock_status',
    '_manage_stock',
];
$quotedMetaKeys = implode(',', array_map([$pdo, 'quote'], $metaKeys));
$metaRows = $pdo->query(
    "SELECT post_id, meta_key, meta_value
     FROM wp_postmeta
     WHERE post_id IN ({$idList}) AND meta_key IN ({$quotedMetaKeys})
     ORDER BY post_id, meta_id"
)->fetchAll();
$metaByProduct = [];
foreach ($metaRows as $row) {
    $metaByProduct[(int) $row['post_id']][(string) $row['meta_key']] = (string) $row['meta_value'];
}

$termRows = $pdo->query(
    "SELECT tr.object_id AS product_id, t.term_id, t.name, t.slug, tt.term_taxonomy_id
     FROM wp_term_relationships tr
     JOIN wp_term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
     JOIN wp_terms t ON t.term_id = tt.term_id
     WHERE tr.object_id IN ({$idList}) AND tt.taxonomy = 'product_cat'
     ORDER BY tr.object_id, t.term_id"
)->fetchAll();
$termsByProduct = [];
foreach ($termRows as $row) {
    $termsByProduct[(int) $row['product_id']][] = [
        'term_id' => (int) $row['term_id'],
        'term_taxonomy_id' => (int) $row['term_taxonomy_id'],
        'name' => (string) $row['name'],
        'slug' => (string) $row['slug'],
    ];
}

$attachmentIds = [];
foreach ($productIds as $productId) {
    $meta = $metaByProduct[$productId] ?? [];
    if (!empty($meta['_thumbnail_id'])) {
        $attachmentIds[(int) $meta['_thumbnail_id']] = true;
    }
    foreach (array_filter(array_map('intval', explode(',', $meta['_product_image_gallery'] ?? ''))) as $attachmentId) {
        $attachmentIds[$attachmentId] = true;
    }
}

$attachments = [];
if ($attachmentIds) {
    $attachmentIdList = implode(',', array_keys($attachmentIds));
    $attachmentRows = $pdo->query(
        "SELECT p.ID, p.post_title, p.post_name, p.guid, p.post_mime_type,
                MAX(CASE WHEN pm.meta_key = '_wp_attachment_image_alt' THEN pm.meta_value ELSE NULL END) AS alt_text,
                MAX(CASE WHEN pm.meta_key = '_wp_attached_file' THEN pm.meta_value ELSE NULL END) AS attached_file
         FROM wp_posts p
         LEFT JOIN wp_postmeta pm
           ON pm.post_id = p.ID
          AND pm.meta_key IN ('_wp_attachment_image_alt', '_wp_attached_file')
         WHERE p.ID IN ({$attachmentIdList})
         GROUP BY p.ID, p.post_title, p.post_name, p.guid, p.post_mime_type
         ORDER BY p.ID"
    )->fetchAll();
    foreach ($attachmentRows as $row) {
        $attachments[(int) $row['ID']] = [
            'id' => (int) $row['ID'],
            'title' => (string) $row['post_title'],
            'slug' => (string) $row['post_name'],
            'guid' => (string) $row['guid'],
            'mime_type' => (string) $row['post_mime_type'],
            'alt_text' => (string) ($row['alt_text'] ?? ''),
            'attached_file' => (string) ($row['attached_file'] ?? ''),
        ];
    }
}

$baselineProducts = [];
foreach ($products as $product) {
    $id = (int) $product['ID'];
    $meta = $metaByProduct[$id] ?? [];
    $imageIds = [];
    if (!empty($meta['_thumbnail_id'])) {
        $imageIds[] = (int) $meta['_thumbnail_id'];
    }
    foreach (array_filter(array_map('intval', explode(',', $meta['_product_image_gallery'] ?? ''))) as $attachmentId) {
        $imageIds[] = $attachmentId;
    }
    $imageIds = array_values(array_unique($imageIds));
    $imageData = [];
    foreach ($imageIds as $imageId) {
        $imageData[] = $attachments[$imageId] ?? ['id' => $imageId, 'missing_attachment_record' => true];
    }

    $baselineProducts[] = [
        'id' => $id,
        'title' => (string) $product['post_title'],
        'slug' => (string) $product['post_name'],
        'url' => 'http://localhost/hopgiayvpn/product/' . (string) $product['post_name'] . '/',
        'status' => (string) $product['post_status'],
        'post_excerpt' => (string) $product['post_excerpt'],
        'post_content' => (string) $product['post_content'],
        'rank_math_title' => (string) ($meta['rank_math_title'] ?? ''),
        'rank_math_description' => (string) ($meta['rank_math_description'] ?? ''),
        'rank_math_focus_keyword' => (string) ($meta['rank_math_focus_keyword'] ?? ''),
        'rank_math_canonical_url' => (string) ($meta['rank_math_canonical_url'] ?? ''),
        'sku' => (string) ($meta['_sku'] ?? ''),
        'price' => (string) ($meta['_price'] ?? ''),
        'regular_price' => (string) ($meta['_regular_price'] ?? ''),
        'sale_price' => (string) ($meta['_sale_price'] ?? ''),
        'stock' => (string) ($meta['_stock'] ?? ''),
        'stock_status' => (string) ($meta['_stock_status'] ?? ''),
        'manage_stock' => (string) ($meta['_manage_stock'] ?? ''),
        'categories' => $termsByProduct[$id] ?? [],
        'images' => $imageData,
        'post_date' => (string) $product['post_date'],
        'post_modified' => (string) $product['post_modified'],
        'post_parent' => (int) $product['post_parent'],
        'menu_order' => (int) $product['menu_order'],
        'comment_status' => (string) $product['comment_status'],
        'ping_status' => (string) $product['ping_status'],
        'field_hashes' => [
            'post_excerpt' => hash('sha256', (string) $product['post_excerpt']),
            'post_content' => hash('sha256', (string) $product['post_content']),
            'rank_math_title' => hash('sha256', (string) ($meta['rank_math_title'] ?? '')),
            'rank_math_description' => hash('sha256', (string) ($meta['rank_math_description'] ?? '')),
            'rank_math_focus_keyword' => hash('sha256', (string) ($meta['rank_math_focus_keyword'] ?? '')),
        ],
    ];
}

$payload = [
    'schema_version' => 1,
    'created_at' => date(DATE_ATOM),
    'environment' => [
        'wordpress_root' => ROOT_DIR,
        'home_url' => 'http://localhost/hopgiayvpn',
        'database' => DB_NAME,
        'host' => DB_HOST,
        'table_prefix' => TABLE_PREFIX,
        'tx_read_only' => $txReadOnly,
        'table_count' => count($tables),
    ],
    'table_checksums' => $tableChecksums,
    'product_count' => count($baselineProducts),
    'products' => $baselineProducts,
];

$baselinePath = OUTPUT_DIR . '\\product-fields-baseline.json';
$encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($encoded === false || file_put_contents($baselinePath, $encoded . PHP_EOL) === false) {
    fail('Cannot write product baseline backup.');
}
$pdo->commit();

$dumpCandidates = glob(OUTPUT_DIR . '\\hopgiayvpnmoi-pre-seo-*.sql') ?: [];
rsort($dumpCandidates, SORT_STRING);
if (!$dumpCandidates) {
    fail('Full database dump is missing.');
}
$dumpPath = $dumpCandidates[0];
$dumpTail = file_get_contents($dumpPath, false, null, max(0, filesize($dumpPath) - 4096));
if ($dumpTail === false || !str_contains($dumpTail, 'Dump completed')) {
    fail('Full database dump does not contain a completion marker.');
}

$manifest = [
    'schema_version' => 1,
    'created_at' => date(DATE_ATOM),
    'database_dump' => [
        'path' => $dumpPath,
        'bytes' => filesize($dumpPath),
        'sha256' => sha256File($dumpPath),
        'create_table_statements' => preg_match_all('/^CREATE TABLE /m', (string) file_get_contents($dumpPath)),
        'completion_marker' => true,
    ],
    'product_baseline' => [
        'path' => $baselinePath,
        'bytes' => filesize($baselinePath),
        'sha256' => sha256File($baselinePath),
        'product_count' => count($baselineProducts),
    ],
];
$manifestPath = OUTPUT_DIR . '\\backup-manifest.json';
$manifestJson = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($manifestJson === false || file_put_contents($manifestPath, $manifestJson . PHP_EOL) === false) {
    fail('Cannot write backup manifest.');
}

echo json_encode([
    'ok' => true,
    'database' => $database,
    'tx_read_only' => $txReadOnly,
    'tables' => count($tables),
    'products' => count($baselineProducts),
    'database_dump_sha256' => $manifest['database_dump']['sha256'],
    'product_baseline_sha256' => $manifest['product_baseline']['sha256'],
    'manifest_path' => $manifestPath,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
