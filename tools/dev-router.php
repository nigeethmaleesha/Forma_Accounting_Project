<?php
// php -S 127.0.0.1:8001 tools/dev-router.php
$path=parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH);
$file=dirname(__DIR__).$path;
if($path==='/sitemap.xml'){require dirname(__DIR__).'/sitemap.php';return true;}
if($path!=='/'&&is_file($file))return false;
require dirname(__DIR__).'/index.php';
