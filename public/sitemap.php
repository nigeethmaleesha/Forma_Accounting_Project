<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');
if (config('SITE_MODE') !== 'production' || !config('SITE_URL')) {
    http_response_code(404);
    echo '<?xml version="1.0" encoding="UTF-8"?><error>Preview mode: sitemap disabled</error>';
    exit;
}
$urls=['/','/about/','/services/','/insights/','/newsletter/','/contact/','/privacy/'];
$data=require APP_ROOT . '/app/content.php';
foreach (['services'=>'services','insights'=>'insights','newsletters'=>'newsletter'] as $group=>$prefix) {
    foreach ($data[$group] as $item) $urls[]='/' . $prefix . '/' . rawurlencode($item['slug']) . '/';
}
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
foreach ($urls as $path) echo '<url><loc>' . e(site_url($path)) . '</loc></url>';
echo '</urlset>';
