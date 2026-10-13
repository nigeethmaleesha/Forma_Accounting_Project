<?php
// Local development router: php -S 127.0.0.1:8000 -t public public/router.php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (str_contains($path, '..') || str_contains($path, "\0") || str_starts_with($path, '/.')) {
    http_response_code(404); exit('Not found');
}
if (str_starts_with($path, '/assets/') && is_file(__DIR__ . $path)) return false;
if ($path === '/api/inquiry.php') { require __DIR__ . '/api/inquiry.php'; return true; }
if ($path === '/robots.txt') { require __DIR__ . '/robots.php'; return true; }
if ($path === '/sitemap.xml') { require __DIR__ . '/sitemap.php'; return true; }
if (str_ends_with($path, '.php') || str_ends_with($path, '.html')) { http_response_code(404); exit('Not found'); }
require __DIR__ . '/index.php';
