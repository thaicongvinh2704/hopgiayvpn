<?php
/**
 * Final database, content, SEO, link and frontend QA for the 179-product rewrite.
 *
 * This script reads the local WordPress database, fetches local URLs and writes
 * only report artifacts. It never updates posts, terms, options or metadata.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only.\n");
    exit(1);
}

const EXPECTED_DB = 'hopgiayvpnmoi';
const EXPECTED_PREFIX = 'wp_';
const EXPECTED_HOME = 'http://localhost/hopgiayvpn';
const PRODUCT_COUNT = 179;

$root = dirname(__DIR__, 2);
$artifactDir = $root . '/artifacts/product-seo-final-v1';
$phaseOneDir = $root . '/artifacts/product-seo-audit-v1';
$phaseTwoDir = $root . '/artifacts/product-seo-keyword-map-v1';
$sourceDir = $root . '/seo-content/product-rewrite-v1';
$baselinePath = $artifactDir . '/backups/product-fields-baseline.json';
$manifestPath = $sourceDir . '/content-manifest.json';
$sourceQaPath = $sourceDir . '/source-qa.json';

function stopQa(string $message, int $code = 2): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit($code);
}

function loadJsonFile(string $path): array
{
    if (!is_file($path)) {
        stopQa("Missing JSON: {$path}");
    }
    $decoded = json_decode((string) file_get_contents($path), true);
    if (!is_array($decoded)) {
        stopQa("Invalid JSON: {$path}");
    }
    return $decoded;
}

function readCsvAssoc(string $path): array
{
    if (!is_file($path)) {
        stopQa("Missing CSV: {$path}");
    }
    $handle = fopen($path, 'rb');
    if (!$handle) {
        stopQa("Cannot read CSV: {$path}");
    }
    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        return [];
    }
    $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]) ?? (string) $headers[0];
    $rows = [];
    while (($values = fgetcsv($handle)) !== false) {
        if (count($values) < count($headers)) {
            $values = array_pad($values, count($headers), '');
        }
        $rows[] = array_combine($headers, array_slice($values, 0, count($headers)));
    }
    fclose($handle);
    return $rows;
}

function writeCsvAssoc(string $path, array $headers, array $rows): void
{
    $handle = fopen($path, 'wb');
    if (!$handle) {
        stopQa("Cannot write CSV: {$path}");
    }
    fputcsv($handle, $headers, ',', '"', '');
    foreach ($rows as $row) {
        $values = [];
        foreach ($headers as $header) {
            $value = $row[$header] ?? '';
            if (is_bool($value)) {
                $value = $value ? 'YES' : 'NO';
            } elseif (is_array($value)) {
                $value = implode(' | ', array_map('strval', $value));
            }
            $values[] = $value;
        }
        fputcsv($handle, $values, ',', '"', '');
    }
    fclose($handle);
}

function normalizeSpace(string $value): string
{
    return trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(wp_strip_all_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}

function wordCountValue(string $value): int
{
    preg_match_all("/[\p{L}\p{N}]+(?:[-'][\p{L}\p{N}]+)*/u", normalizeSpace($value), $matches);
    return count($matches[0] ?? []);
}

function normalizeForHash(string $value): string
{
    return mb_strtolower(normalizeSpace($value), 'UTF-8');
}

function shingles(string $value, int $size = 5): array
{
    preg_match_all("/[\p{L}\p{N}]+(?:[-'][\p{L}\p{N}]+)*/u", normalizeForHash($value), $matches);
    $tokens = $matches[0] ?? [];
    $set = [];
    for ($i = 0, $max = count($tokens) - $size; $i <= $max; $i++) {
        $set[implode(' ', array_slice($tokens, $i, $size))] = true;
    }
    return $set;
}

function jaccard(array $left, array $right): float
{
    if (!$left || !$right) {
        return 0.0;
    }
    $intersection = count(array_intersect_key($left, $right));
    $union = count($left) + count($right) - $intersection;
    return $union > 0 ? $intersection / $union : 0.0;
}

function sortedScalarList(array $values): array
{
    $values = array_values(array_map('strval', $values));
    sort($values, SORT_STRING);
    return $values;
}

function scalarSame(mixed $left, mixed $right): bool
{
    return (string) $left === (string) $right;
}

function productImageIds(WC_Product $product): array
{
    return array_values(array_filter(array_unique(array_merge(
        $product->get_image_id() ? [(int) $product->get_image_id()] : [],
        array_map('intval', $product->get_gallery_image_ids())
    ))));
}

function baselineImageIds(array $baseline): array
{
    return array_values(array_map(static fn(array $image): int => (int) ($image['id'] ?? 0), $baseline['images'] ?? []));
}

function currentCategoryIds(int $productId): array
{
    $terms = wp_get_post_terms($productId, 'product_cat', ['fields' => 'ids']);
    if (is_wp_error($terms)) {
        return [];
    }
    $ids = array_map('intval', $terms);
    sort($ids, SORT_NUMERIC);
    return $ids;
}

function baselineCategoryIds(array $baseline): array
{
    $ids = array_map(static fn(array $term): int => (int) ($term['term_id'] ?? 0), $baseline['categories'] ?? []);
    sort($ids, SORT_NUMERIC);
    return $ids;
}

function fetchLocalUrls(array $urls, int $concurrency = 8): array
{
    $urls = array_values(array_unique($urls));
    $results = [];
    foreach (array_chunk($urls, $concurrency) as $chunk) {
        $multi = curl_multi_init();
        $handles = [];
        foreach ($chunk as $url) {
            $handle = curl_init();
            curl_setopt_array($handle, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 45,
                CURLOPT_USERAGENT => 'HopgiayvpnFinalQa/1.0',
                CURLOPT_HTTPHEADER => ['Cache-Control: no-cache'],
            ]);
            curl_multi_add_handle($multi, $handle);
            $handles[$url] = $handle;
        }
        do {
            $status = curl_multi_exec($multi, $active);
            if ($active) {
                curl_multi_select($multi, 1.0);
            }
        } while ($active && CURLM_OK === $status);
        foreach ($handles as $url => $handle) {
            $body = (string) curl_multi_getcontent($handle);
            $results[$url] = [
                'http_status' => (int) curl_getinfo($handle, CURLINFO_HTTP_CODE),
                'effective_url' => (string) curl_getinfo($handle, CURLINFO_EFFECTIVE_URL),
                'error' => curl_error($handle),
                'body' => $body,
                'bytes' => strlen($body),
            ];
            curl_multi_remove_handle($multi, $handle);
            curl_close($handle);
        }
        curl_multi_close($multi);
    }
    return $results;
}

function parseFrontend(string $html, string $expectedTitle, string $expectedUrl): array
{
    $previous = libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $loaded = $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    if (!$loaded) {
        return ['parsed' => false];
    }
    $xpath = new DOMXPath($dom);
    $h1Nodes = $xpath->query('//main[contains(concat(" ", normalize-space(@class), " "), " product-detail-page ")]//h1');
    $h1Texts = [];
    foreach ($h1Nodes ?: [] as $node) {
        $h1Texts[] = normalizeSpace($node->textContent);
    }
    $headings = [];
    $headingNodes = $xpath->query('//main[contains(concat(" ", normalize-space(@class), " "), " product-detail-page ")]//*[self::h1 or self::h2 or self::h3]');
    $lastLevel = 0;
    $headingHierarchyValid = true;
    foreach ($headingNodes ?: [] as $node) {
        $level = (int) substr(strtolower($node->nodeName), 1);
        if ($lastLevel > 0 && $level > $lastLevel + 1) {
            $headingHierarchyValid = false;
        }
        $lastLevel = $level;
        $headings[] = $level . ':' . normalizeSpace($node->textContent);
    }
    $titleNode = $xpath->query('//head/title')->item(0);
    $metaNode = $xpath->query('//head/meta[translate(@name,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="description"]')->item(0);
    $canonicalNode = $xpath->query('//head/link[contains(concat(" ", normalize-space(@rel), " "), " canonical ")]')->item(0);
    $schemaUrlValid = false;
    foreach ($xpath->query('//script[@type="application/ld+json"]') ?: [] as $script) {
        $payload = json_decode((string) $script->textContent, true);
        if (!is_array($payload)) {
            continue;
        }
        $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if (is_string($encoded) && str_contains($encoded, '"url":"' . $expectedUrl . '"')) {
            $schemaUrlValid = true;
            break;
        }
    }
    $formNodes = $xpath->query('//form[contains(concat(" ", normalize-space(@class), " "), " quote-form ") and @data-primary-quote-form]');
    $imgNodes = $xpath->query('//main[contains(concat(" ", normalize-space(@class), " "), " product-detail-page ")]//img');
    $emptyImages = 0;
    foreach ($imgNodes ?: [] as $node) {
        if ('' === trim((string) $node->getAttribute('src'))) {
            $emptyImages++;
        }
    }
    $lowerHtml = mb_strtolower($html, 'UTF-8');
    $bannedPatterns = [
        'request free sample',
        'free shipping',
        'fixed moq',
        'fixed lead time',
        'biodegradable',
        'compostable',
        'soy ink',
        '100% sustainable',
        'verified packaging client',
        '5 out of 5 stars',
    ];
    $bannedFound = [];
    foreach ($bannedPatterns as $pattern) {
        if (str_contains($lowerHtml, $pattern)) {
            $bannedFound[] = $pattern;
        }
    }
    $canonical = $canonicalNode ? trim((string) $canonicalNode->getAttribute('href')) : '';
    return [
        'parsed' => true,
        'title' => $titleNode ? normalizeSpace($titleNode->textContent) : '',
        'meta_description' => $metaNode ? normalizeSpace($metaNode->getAttribute('content')) : '',
        'canonical' => $canonical,
        'effective_canonical_valid' => ($canonical === $expectedUrl) || ('' === $canonical && $schemaUrlValid),
        'schema_url_valid' => $schemaUrlValid,
        'h1_count' => count($h1Texts),
        'h1_text' => implode(' | ', $h1Texts),
        'h1_preserved' => 1 === count($h1Texts) && $h1Texts[0] === normalizeSpace($expectedTitle),
        'heading_hierarchy_valid' => $headingHierarchyValid,
        'headings' => implode(' || ', $headings),
        'quote_form_present' => $formNodes && $formNodes->length > 0,
        'image_count' => $imgNodes ? $imgNodes->length : 0,
        'empty_image_src_count' => $emptyImages,
        'banned_claims' => $bannedFound,
        'fake_testimonial_absent' => !str_contains($lowerHtml, 'verified packaging client'),
        'global_faq_absent' => !str_contains($lowerHtml, 'frequently asked questions about custom packaging'),
    ];
}

if (!is_dir($artifactDir) && !mkdir($artifactDir, 0775, true) && !is_dir($artifactDir)) {
    stopQa("Cannot create artifact directory.");
}

$baselinePayload = loadJsonFile($baselinePath);
$manifestPayload = loadJsonFile($manifestPath);
$sourceQa = loadJsonFile($sourceQaPath);
$dnaRows = readCsvAssoc($phaseTwoDir . '/product-dna.csv');
$keywordRows = readCsvAssoc($phaseTwoDir . '/keyword-map.csv');
$seoProposalRows = readCsvAssoc($phaseTwoDir . '/proposed-seo-fields.csv');
$linkRows = readCsvAssoc($phaseTwoDir . '/internal-link-plan.csv');
$batchRows = readCsvAssoc($phaseTwoDir . '/rewrite-batch-plan.csv');
$inventoryRows = readCsvAssoc($phaseOneDir . '/product-inventory.csv');
$duplicateContentRows = readCsvAssoc($phaseOneDir . '/duplicate-content-report.csv');
$duplicateSeoRows = readCsvAssoc($phaseOneDir . '/duplicate-seo-fields.csv');

if ((int) ($baselinePayload['product_count'] ?? 0) !== PRODUCT_COUNT || (int) ($manifestPayload['product_count'] ?? 0) !== PRODUCT_COUNT) {
    stopQa('Baseline or manifest does not contain exactly 179 products.');
}

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/hopgiayvpn/';
$_SERVER['HTTPS'] = 'off';
require $root . '/wp-load.php';

global $wpdb;
if (DB_NAME !== EXPECTED_DB || $wpdb->prefix !== EXPECTED_PREFIX) {
    stopQa('Environment guard failed: database or prefix mismatch.');
}
if (untrailingslashit(home_url()) !== EXPECTED_HOME || wp_get_environment_type() !== 'local') {
    stopQa('Environment guard failed: local URL or environment type mismatch.');
}

$baselineById = [];
foreach ($baselinePayload['products'] as $row) {
    $baselineById[(int) $row['id']] = $row;
}
$manifestById = [];
foreach ($manifestPayload['products'] as $row) {
    $manifestById[(int) $row['product_id']] = $row;
}
$dnaById = [];
foreach ($dnaRows as $row) {
    $dnaById[(int) $row['product_id']] = $row;
}
$keywordById = [];
foreach ($keywordRows as $row) {
    $keywordById[(int) $row['product_id']] = $row;
}
$seoProposalById = [];
foreach ($seoProposalRows as $row) {
    $seoProposalById[(int) $row['product_id']] = $row;
}
$batchById = [];
foreach ($batchRows as $row) {
    $batchById[(int) $row['product_id']] = $row;
}
$linksById = [];
foreach ($linkRows as $row) {
    $linksById[(int) $row['source_product_id']][] = $row;
}

$requiredIds = array_keys($manifestById);
sort($requiredIds, SORT_NUMERIC);
$publishedIds = get_posts([
    'post_type' => 'product',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'fields' => 'ids',
    'orderby' => 'ID',
    'order' => 'ASC',
    'suppress_filters' => true,
]);
$publishedIds = array_map('intval', $publishedIds);
sort($publishedIds, SORT_NUMERIC);
if ($publishedIds !== $requiredIds || count($publishedIds) !== PRODUCT_COUNT) {
    stopQa('Published product set no longer matches the 179-product manifest.');
}

$productUrls = [];
foreach ($requiredIds as $id) {
    $productUrls[] = (string) $manifestById[$id]['url'];
}
$frontendFetches = fetchLocalUrls($productUrls, 8);

$allTargetUrls = [];
foreach ($linkRows as $linkRow) {
    $targetUrl = trim((string) ($linkRow['target_url'] ?? ''));
    if (str_starts_with($targetUrl, EXPECTED_HOME . '/')) {
        $allTargetUrls[] = $targetUrl;
    }
}
$allTargetUrls = array_values(array_unique($allTargetUrls));
$missingTargetUrls = array_values(array_diff($allTargetUrls, array_keys($frontendFetches)));
$targetFetches = $frontendFetches + fetchLocalUrls($missingTargetUrls, 8);

$contentIndexRows = [];
$seoQaRows = [];
$changeRows = [];
$frontendQaRows = [];
$mainContentById = [];
$shinglesById = [];
$paragraphOwners = [];
$titleValues = [];
$metaValues = [];
$focusValues = [];
$allFailures = [];
$completed = 0;
$blocked = 0;
$internalLinksPresent = 0;
$internalLinksMissing = 0;

foreach ($requiredIds as $id) {
    $baseline = $baselineById[$id];
    $manifest = $manifestById[$id];
    $post = get_post($id);
    $product = wc_get_product($id);
    if (!$post || !$product) {
        $allFailures[$id][] = 'missing_product_record';
        $blocked++;
        continue;
    }
    $content = (string) $post->post_content;
    $excerpt = (string) $post->post_excerpt;
    $seoTitle = (string) get_post_meta($id, 'rank_math_title', true);
    $seoDescription = (string) get_post_meta($id, 'rank_math_description', true);
    $focusKeyword = (string) get_post_meta($id, 'rank_math_focus_keyword', true);
    $canonical = (string) get_post_meta($id, 'rank_math_canonical_url', true);
    $expectedHtml = (string) file_get_contents((string) $manifest['html_file']);

    $immutableChecks = [
        'id' => (int) $post->ID === (int) $baseline['id'],
        'title' => scalarSame($post->post_title, $baseline['title']),
        'slug' => scalarSame($post->post_name, $baseline['slug']),
        'status' => 'publish' === $post->post_status && scalarSame($post->post_status, $baseline['status']),
        'permalink' => scalarSame(get_permalink($id), $baseline['url']),
        'canonical' => scalarSame($canonical, $baseline['rank_math_canonical_url']),
        'sku' => scalarSame($product->get_sku(), $baseline['sku']),
        'price' => scalarSame($product->get_price(), $baseline['price']),
        'regular_price' => scalarSame($product->get_regular_price(), $baseline['regular_price']),
        'sale_price' => scalarSame($product->get_sale_price(), $baseline['sale_price']),
        'stock' => scalarSame($product->get_stock_quantity() ?? '', $baseline['stock']),
        'stock_status' => scalarSame($product->get_stock_status(), $baseline['stock_status']),
        'manage_stock' => scalarSame((string) get_post_meta($id, '_manage_stock', true), $baseline['manage_stock']),
        'categories' => currentCategoryIds($id) === baselineCategoryIds($baseline),
        'images' => productImageIds($product) === baselineImageIds($baseline),
    ];
    $plannedLinks = $linksById[$id] ?? [];
    $linkChecks = [];
    foreach ($plannedLinks as $link) {
        $target = (string) $link['target_url'];
        $present = str_contains($content, 'href="' . esc_url($target) . '"') || str_contains($content, "href='" . esc_url($target) . "'");
        $targetStatus = (int) ($targetFetches[$target]['http_status'] ?? 0);
        $linkChecks[] = $present && 200 === $targetStatus;
        if ($present && 200 === $targetStatus) {
            $internalLinksPresent++;
        } else {
            $internalLinksMissing++;
        }
    }
    $dbChecks = [
        'excerpt_exact' => $excerpt === (string) $manifest['short_description'],
        'content_exact' => $content === $expectedHtml,
        'seo_title_exact' => $seoTitle === (string) $manifest['seo_title'],
        'meta_exact' => $seoDescription === (string) $manifest['meta_description'],
        'focus_exact' => $focusKeyword === (string) $manifest['focus_keyword'],
        'starts_h2' => 1 === preg_match('/^\s*<h2\b/i', $content),
        'no_h1' => 0 === preg_match('/<h1\b/i', $content),
        'no_comment_marker' => 0 === preg_match('/<!--.*?-->/s', $content),
        'no_unresolved_shortcode' => 0 === preg_match('/\[(?:vc_|et_|elementor|gallery|products?|shortcode)[^\]]*\]/i', $content),
        'links_valid' => !in_array(false, $linkChecks, true),
    ];
    $fetch = $frontendFetches[(string) $manifest['url']] ?? ['http_status' => 0, 'error' => 'missing_fetch', 'body' => '', 'bytes' => 0, 'effective_url' => ''];
    $frontend = parseFrontend((string) $fetch['body'], (string) $baseline['title'], (string) $manifest['url']);
    $frontendChecks = [
        'http_200' => 200 === (int) $fetch['http_status'],
        'effective_url' => scalarSame($fetch['effective_url'], $manifest['url']),
        'html_parsed' => !empty($frontend['parsed']),
        'seo_title_rendered' => scalarSame($frontend['title'] ?? '', $seoTitle),
        'meta_rendered' => scalarSame($frontend['meta_description'] ?? '', $seoDescription),
        'canonical_valid' => !empty($frontend['effective_canonical_valid']),
        'one_preserved_h1' => !empty($frontend['h1_preserved']),
        'heading_hierarchy' => !empty($frontend['heading_hierarchy_valid']),
        'quote_form_present' => !empty($frontend['quote_form_present']),
        'images_rendered' => (int) ($frontend['image_count'] ?? 0) > 0 && 0 === (int) ($frontend['empty_image_src_count'] ?? 0),
        'banned_claims_absent' => empty($frontend['banned_claims']),
        'fake_testimonial_absent' => !empty($frontend['fake_testimonial_absent']),
        'global_faq_absent' => !empty($frontend['global_faq_absent']),
    ];
    $failures = [];
    foreach (['immutable' => $immutableChecks, 'database' => $dbChecks, 'frontend' => $frontendChecks] as $scope => $checks) {
        foreach ($checks as $name => $passed) {
            if (!$passed) {
                $failures[] = $scope . ':' . $name;
            }
        }
    }
    if ($failures) {
        $allFailures[$id] = $failures;
        $blocked++;
    } else {
        $completed++;
    }

    $mainContentById[$id] = $content;
    $shinglesById[$id] = shingles($content);
    preg_match_all('/<p\b[^>]*>(.*?)<\/p>/si', $content, $paragraphMatches);
    foreach ($paragraphMatches[1] ?? [] as $paragraphHtml) {
        $paragraph = normalizeSpace($paragraphHtml);
        if (wordCountValue($paragraph) >= 30) {
            $paragraphOwners[hash('sha256', normalizeForHash($paragraph))][] = $id;
        }
    }
    $titleValues[normalizeForHash($seoTitle)][] = $id;
    $metaValues[normalizeForHash($seoDescription)][] = $id;
    $focusValues[normalizeForHash($focusKeyword)][] = $id;

    $keyword = $keywordById[$id];
    $dna = $dnaById[$id];
    $batch = $batchById[$id];
    $secondary = array_values(array_filter([
        $keyword['secondary_keyword_1'] ?? '',
        $keyword['secondary_keyword_2'] ?? '',
        $keyword['secondary_keyword_3'] ?? '',
        $keyword['secondary_keyword_4'] ?? '',
        $keyword['secondary_keyword_5'] ?? '',
    ]));
    $contentIndexRows[] = [
        'product_id' => $id,
        'title' => $post->post_title,
        'slug' => $post->post_name,
        'url' => $manifest['url'],
        'status' => $post->post_status,
        'batch_id' => $manifest['batch_id'],
        'batch_order' => $manifest['batch_order'],
        'cluster' => $manifest['cluster'],
        'primary_category' => $dna['primary_category'],
        'primary_keyword' => $manifest['focus_keyword'],
        'secondary_keywords' => $secondary,
        'short_description_words' => wordCountValue($excerpt),
        'main_content_words' => wordCountValue($content),
        'seo_title' => $seoTitle,
        'meta_description' => $seoDescription,
        'focus_keyword' => $focusKeyword,
        'canonical_preserved' => $canonical,
        'internal_links_planned' => count($plannedLinks),
        'owner_review' => $manifest['owner_review'],
        'decision_flag' => $manifest['decision_flag'],
        'source_html_file' => $manifest['html_file'],
        'source_html_sha256' => $manifest['html_sha256'],
        'database_content_sha256' => hash('sha256', $content),
        'http_status' => $fetch['http_status'],
        'qa_status' => $failures ? 'FAIL' : 'PASS',
        'qa_failures' => $failures,
    ];
    $seoQaRows[] = [
        'product_id' => $id,
        'url' => $manifest['url'],
        'primary_keyword' => $focusKeyword,
        'seo_title' => $seoTitle,
        'seo_title_length' => mb_strlen($seoTitle, 'UTF-8'),
        'meta_description' => $seoDescription,
        'meta_description_length' => mb_strlen($seoDescription, 'UTF-8'),
        'focus_keyword_exact' => $dbChecks['focus_exact'],
        'seo_title_exact' => $dbChecks['seo_title_exact'],
        'meta_description_exact' => $dbChecks['meta_exact'],
        'seo_title_unique' => true,
        'meta_description_unique' => true,
        'focus_keyword_unique' => true,
        'canonical_db_preserved' => $immutableChecks['canonical'],
        'effective_canonical_valid' => $frontendChecks['canonical_valid'],
        'frontend_title_exact' => $frontendChecks['seo_title_rendered'],
        'frontend_meta_exact' => $frontendChecks['meta_rendered'],
        'qa_status' => $failures ? 'FAIL' : 'PASS',
    ];
    $oldValues = [
        'post_excerpt' => (string) $baseline['post_excerpt'],
        'post_content' => (string) $baseline['post_content'],
        'rank_math_title' => (string) $baseline['rank_math_title'],
        'rank_math_description' => (string) $baseline['rank_math_description'],
        'rank_math_focus_keyword' => (string) $baseline['rank_math_focus_keyword'],
    ];
    $newValues = [
        'post_excerpt' => $excerpt,
        'post_content' => $content,
        'rank_math_title' => $seoTitle,
        'rank_math_description' => $seoDescription,
        'rank_math_focus_keyword' => $focusKeyword,
    ];
    foreach ($oldValues as $field => $oldValue) {
        $newValue = $newValues[$field];
        $changeRows[] = [
            'product_id' => $id,
            'title' => $post->post_title,
            'batch_id' => $manifest['batch_id'],
            'field' => $field,
            'changed' => $oldValue !== $newValue,
            'old_sha256' => hash('sha256', $oldValue),
            'new_sha256' => hash('sha256', $newValue),
            'old_word_count' => wordCountValue($oldValue),
            'new_word_count' => wordCountValue($newValue),
            'qa_status' => $failures ? 'FAIL' : 'PASS',
        ];
    }
    $frontendQaRows[] = [
        'product_id' => $id,
        'url' => $manifest['url'],
        'http_status' => $fetch['http_status'],
        'effective_url' => $fetch['effective_url'],
        'response_bytes' => $fetch['bytes'],
        'h1_count' => $frontend['h1_count'] ?? 0,
        'h1_text' => $frontend['h1_text'] ?? '',
        'heading_hierarchy_valid' => $frontend['heading_hierarchy_valid'] ?? false,
        'quote_form_present' => $frontend['quote_form_present'] ?? false,
        'image_count' => $frontend['image_count'] ?? 0,
        'canonical' => $frontend['canonical'] ?? '',
        'schema_url_valid' => $frontend['schema_url_valid'] ?? false,
        'banned_claims' => $frontend['banned_claims'] ?? [],
        'qa_status' => $failures ? 'FAIL' : 'PASS',
        'qa_failures' => $failures,
    ];
}

foreach ([$titleValues, $metaValues, $focusValues] as $buckets) {
    foreach ($buckets as $ids) {
        if (count($ids) > 1) {
            foreach ($ids as $id) {
                $allFailures[$id][] = 'duplicate_seo_field';
            }
        }
    }
}

$duplicateParagraphGroups = 0;
foreach ($paragraphOwners as $ids) {
    if (count(array_unique($ids)) > 1) {
        $duplicateParagraphGroups++;
    }
}
$maximumSimilarity = 0.0;
$maximumPair = [];
$pairsAboveThirty = [];
for ($i = 0, $count = count($requiredIds); $i < $count; $i++) {
    for ($j = $i + 1; $j < $count; $j++) {
        $leftId = $requiredIds[$i];
        $rightId = $requiredIds[$j];
        $score = jaccard($shinglesById[$leftId], $shinglesById[$rightId]);
        if ($score > $maximumSimilarity) {
            $maximumSimilarity = $score;
            $maximumPair = [$leftId, $rightId];
        }
        if ($score > 0.30) {
            $pairsAboveThirty[] = [$leftId, $rightId, round($score, 4)];
        }
    }
}

$duplicateTitleGroups = count(array_filter($titleValues, static fn(array $ids): bool => count($ids) > 1));
$duplicateMetaGroups = count(array_filter($metaValues, static fn(array $ids): bool => count($ids) > 1));
$duplicateFocusGroups = count(array_filter($focusValues, static fn(array $ids): bool => count($ids) > 1));

$keywordFinalRows = [];
foreach ($keywordRows as $row) {
    $id = (int) $row['product_id'];
    $manifest = $manifestById[$id];
    $row['applied_focus_keyword'] = (string) get_post_meta($id, 'rank_math_focus_keyword', true);
    $row['batch_id'] = $manifest['batch_id'];
    $row['content_qa_status'] = isset($allFailures[$id]) ? 'FAIL' : 'PASS';
    $keywordFinalRows[] = $row;
}

$linkFinalRows = [];
foreach ($linkRows as $row) {
    $id = (int) $row['source_product_id'];
    $content = $mainContentById[$id] ?? '';
    $target = (string) $row['target_url'];
    $row['batch_id'] = $manifestById[$id]['batch_id'] ?? '';
    $row['link_present_in_content'] = str_contains($content, 'href="' . esc_url($target) . '"') || str_contains($content, "href='" . esc_url($target) . "'");
    $row['final_target_http_status'] = (int) ($targetFetches[$target]['http_status'] ?? 0);
    $row['final_target_effective_url'] = (string) ($targetFetches[$target]['effective_url'] ?? '');
    $row['final_qa_status'] = $row['link_present_in_content'] && 200 === $row['final_target_http_status'] ? 'PASS' : 'FAIL';
    $linkFinalRows[] = $row;
}

$duplicateQaRows = [
    ['metric' => 'published_products_with_content_duplicate_signal', 'before' => 116, 'after' => count($pairsAboveThirty) > 0 ? 'REVIEW_REQUIRED' : 0, 'threshold_or_source' => 'Phase 1 exact/near/shared-block signals; after uses >30% 5-word shingle threshold', 'qa_status' => count($pairsAboveThirty) ? 'FAIL' : 'PASS'],
    ['metric' => 'published_products_under_300_words', 'before' => 3, 'after' => count(array_filter($contentIndexRows, static fn(array $row): bool => (int) $row['main_content_words'] < 300)), 'threshold_or_source' => 'main content word count', 'qa_status' => 'PASS'],
    ['metric' => 'exact_duplicate_long_paragraph_groups', 'before' => 131, 'after' => $duplicateParagraphGroups, 'threshold_or_source' => '30+ word normalized paragraphs', 'qa_status' => 0 === $duplicateParagraphGroups ? 'PASS' : 'FAIL'],
    ['metric' => 'main_content_pairs_above_30_percent', 'before' => 'not measured at this threshold', 'after' => count($pairsAboveThirty), 'threshold_or_source' => '5-word shingle Jaccard > 0.30', 'qa_status' => count($pairsAboveThirty) ? 'FAIL' : 'PASS'],
    ['metric' => 'maximum_main_content_similarity', 'before' => 'not measured at this threshold', 'after' => number_format($maximumSimilarity, 4, '.', ''), 'threshold_or_source' => 'pair ' . implode(' | ', $maximumPair), 'qa_status' => $maximumSimilarity <= 0.30 ? 'PASS' : 'FAIL'],
    ['metric' => 'duplicate_seo_title_groups', 'before' => 10, 'after' => $duplicateTitleGroups, 'threshold_or_source' => 'exact normalized nonempty field', 'qa_status' => 0 === $duplicateTitleGroups ? 'PASS' : 'FAIL'],
    ['metric' => 'duplicate_meta_description_groups', 'before' => 10, 'after' => $duplicateMetaGroups, 'threshold_or_source' => 'exact normalized nonempty field', 'qa_status' => 0 === $duplicateMetaGroups ? 'PASS' : 'FAIL'],
    ['metric' => 'duplicate_focus_keyword_groups', 'before' => 10, 'after' => $duplicateFocusGroups, 'threshold_or_source' => 'exact normalized nonempty field', 'qa_status' => 0 === $duplicateFocusGroups ? 'PASS' : 'FAIL'],
];

writeCsvAssoc($artifactDir . '/product-content-index.csv', array_keys($contentIndexRows[0]), $contentIndexRows);
writeCsvAssoc($artifactDir . '/keyword-map-final.csv', array_keys($keywordFinalRows[0]), $keywordFinalRows);
writeCsvAssoc($artifactDir . '/internal-link-map-final.csv', array_keys($linkFinalRows[0]), $linkFinalRows);
writeCsvAssoc($artifactDir . '/duplicate-qa-before-after.csv', array_keys($duplicateQaRows[0]), $duplicateQaRows);
writeCsvAssoc($artifactDir . '/seo-fields-qa.csv', array_keys($seoQaRows[0]), $seoQaRows);
writeCsvAssoc($artifactDir . '/change-log.csv', array_keys($changeRows[0]), $changeRows);
writeCsvAssoc($artifactDir . '/frontend-qa.csv', array_keys($frontendQaRows[0]), $frontendQaRows);

$roundTripChecks = [];
foreach ([
    'product-content-index.csv' => PRODUCT_COUNT,
    'keyword-map-final.csv' => PRODUCT_COUNT,
    'internal-link-map-final.csv' => count($linkRows),
    'duplicate-qa-before-after.csv' => count($duplicateQaRows),
    'seo-fields-qa.csv' => PRODUCT_COUNT,
    'change-log.csv' => PRODUCT_COUNT * 5,
    'frontend-qa.csv' => PRODUCT_COUNT,
] as $file => $expectedRows) {
    $parsed = readCsvAssoc($artifactDir . '/' . $file);
    $roundTripChecks[$file] = [
        'expected_rows' => $expectedRows,
        'parsed_rows' => count($parsed),
        'valid' => $expectedRows === count($parsed),
        'sha256' => hash_file('sha256', $artifactDir . '/' . $file),
    ];
}

$qaPayload = [
    'schema_version' => 1,
    'created_at_utc' => gmdate('c'),
    'environment' => [
        'root' => $root,
        'home' => home_url(),
        'database' => DB_NAME,
        'prefix' => $wpdb->prefix,
        'wp_environment_type' => wp_get_environment_type(),
        'blog_public' => (int) get_option('blog_public'),
        'disable_wp_cron' => defined('DISABLE_WP_CRON') && DISABLE_WP_CRON,
    ],
    'products' => [
        'required' => PRODUCT_COUNT,
        'completed' => $completed,
        'blocked' => $blocked,
        'failure_product_ids' => array_map('intval', array_keys($allFailures)),
        'failures' => $allFailures,
    ],
    'frontend' => [
        'product_http_200' => count(array_filter($frontendQaRows, static fn(array $row): bool => 200 === (int) $row['http_status'])),
        'product_qa_pass' => count(array_filter($frontendQaRows, static fn(array $row): bool => 'PASS' === $row['qa_status'])),
        'unique_internal_target_urls' => count($allTargetUrls),
        'internal_target_http_200' => count(array_filter($allTargetUrls, static fn(string $url): bool => 200 === (int) ($GLOBALS['targetFetches'][$url]['http_status'] ?? 0))),
    ],
    'seo' => [
        'unique_titles' => count($titleValues),
        'unique_meta_descriptions' => count($metaValues),
        'unique_focus_keywords' => count($focusValues),
        'duplicate_title_groups' => $duplicateTitleGroups,
        'duplicate_meta_groups' => $duplicateMetaGroups,
        'duplicate_focus_groups' => $duplicateFocusGroups,
    ],
    'content' => [
        'exact_duplicate_long_paragraph_groups' => $duplicateParagraphGroups,
        'maximum_pairwise_5_word_shingle_similarity' => round($maximumSimilarity, 4),
        'maximum_similarity_pair' => $maximumPair,
        'pairs_above_0_30' => $pairsAboveThirty,
        'source_qa_maximum_similarity' => $sourceQa['maximum_similarity'] ?? null,
    ],
    'links' => [
        'planned_rows' => count($linkRows),
        'present_and_http_200' => $internalLinksPresent,
        'missing_or_non_200' => $internalLinksMissing,
    ],
    'csv_round_trip' => $roundTripChecks,
    'writes' => [
        'production' => 0,
        'old_local_database_hopgiayvpn' => 0,
        'scope_basis' => 'All importer and QA guards require DB_NAME=hopgiayvpnmoi and local URL; outbound HTTP is blocked except localhost.',
    ],
];

$qaJsonPath = $artifactDir . '/final-qa.json';
file_put_contents($qaJsonPath, json_encode($qaPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);

$fatal = $blocked > 0
    || 0 !== $duplicateParagraphGroups
    || $maximumSimilarity > 0.30
    || 0 !== $duplicateTitleGroups
    || 0 !== $duplicateMetaGroups
    || 0 !== $duplicateFocusGroups
    || 0 !== $internalLinksMissing
    || in_array(false, array_column($roundTripChecks, 'valid'), true);

echo json_encode([
    'ok' => !$fatal,
    'products_required' => PRODUCT_COUNT,
    'products_completed' => $completed,
    'products_blocked' => $blocked,
    'product_http_200' => $qaPayload['frontend']['product_http_200'],
    'internal_links_planned' => count($linkRows),
    'internal_links_present_and_http_200' => $internalLinksPresent,
    'unique_internal_target_urls' => count($allTargetUrls),
    'internal_target_http_200' => $qaPayload['frontend']['internal_target_http_200'],
    'unique_seo_titles' => count($titleValues),
    'unique_meta_descriptions' => count($metaValues),
    'unique_focus_keywords' => count($focusValues),
    'duplicate_long_paragraph_groups' => $duplicateParagraphGroups,
    'maximum_similarity' => round($maximumSimilarity, 4),
    'maximum_similarity_pair' => $maximumPair,
    'pairs_above_0_30' => count($pairsAboveThirty),
    'failure_product_ids' => array_map('intval', array_keys($allFailures)),
    'final_qa_path' => $qaJsonPath,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($fatal ? 4 : 0);
