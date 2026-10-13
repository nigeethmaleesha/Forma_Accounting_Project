<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');
if (config('SITE_MODE') !== 'production' || !config('SITE_URL')) {
    echo "User-agent: *\nDisallow: /\n";
} else {
    echo "User-agent: *\nAllow: /\nDisallow: /api/\nSitemap: " . rtrim((string)config('SITE_URL'), '/') . "/sitemap.xml\n";
}
