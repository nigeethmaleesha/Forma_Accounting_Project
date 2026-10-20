<?php
declare(strict_types=1);
require_once __DIR__.'/server/bootstrap.php';
require_once __DIR__.'/includes/content.php';
header('Content-Type: application/xml; charset=UTF-8');
$base=rtrim(getenv('SITE_URL')?:BUSINESS_URL,'/');
$urls=['/','/about/','/services/','/contact/','/journal/','/privacy/'];
try {foreach(db()->query("SELECT slug FROM posts WHERE status='published'")->fetchAll() as $post)$urls[]='/journal/'.$post['slug'].'/';}catch(Throwable $error){}
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
foreach(array_unique($urls) as $url)echo '<url><loc>'.htmlspecialchars($base.$url,ENT_XML1).'</loc></url>'."\n";
echo '</urlset>';
