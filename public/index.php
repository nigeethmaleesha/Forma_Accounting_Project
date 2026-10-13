<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
headers_security();

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'HEAD') {
    header('Allow: GET, HEAD'); http_response_code(405); exit('Method not allowed');
}
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rawurldecode($path);
if (str_contains($path, '\\') || str_contains($path, "\0") || str_contains($path, '..') || str_contains($path, '//')) {
    http_response_code(404); render('notfound', ['title' => 'Page not found','path'=>'/404/','section'=>'none']);exit;
}
if ($path !== '/' && !str_ends_with($path, '/')) {
    header('Location: ' . $path . '/' . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : ''), true, 301);
    exit;
}
$key = trim($path, '/');
$content = require APP_ROOT . '/app/content.php';
$pages = require APP_ROOT . '/app/pages.php';
if (array_key_exists($key, $pages)) {
    if ($key === 'contact') { csrf_token(); header('Cache-Control: private, no-store'); }
    $section = $key === '' ? 'home' : $key;
    render($section, [ 'title' => $pages[$key]['title'], 'description' => $pages[$key]['description'],
        'path'=>$path,'section'=>$section,'content'=>$content ]);
    exit;
}
$parts = explode('/', $key);
if (count($parts) === 2 && in_array($parts[0], ['services','insights','newsletter'], true)) {
    $group = ['services'=>'services','insights'=>'insights','newsletter'=>'newsletters'][$parts[0]];
    foreach ($content[$group] as $entry) {
        if ($entry['slug'] === $parts[1]) {
            $kind = ['services'=>'service-detail','insights'=>'article-detail','newsletter'=>'newsletter-detail'][$parts[0]];
            render($kind, [ 'title'=>$entry['title'], 'description'=>$entry['summary'], 'path'=>$path,
                'section'=>$parts[0], 'entry'=>$entry, 'content'=>$content ]);
            exit;
        }
    }
}
http_response_code(404);
render('notfound', ['title'=>'Page not found','path'=>'/404/','section'=>'none']);
