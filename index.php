<?php
declare(strict_types=1);
require_once __DIR__.'/server/bootstrap.php';
require_once __DIR__.'/includes/site.php';
$path=trim((string)parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH),'/');
$parts=$path===''?[]:explode('/',$path);
$page=$parts[0]??'home'; $slug=null;
if($page==='journal' && count($parts)>1){$page='article';$slug=$parts[1];}
if($page==='home')$page='home';
if($page==='private') {http_response_code(404);$page='404';}
if($page==='404')http_response_code(404);
if(!in_array($page,['home','about','services','contact','journal','article','portal','privacy','admin'],true)) {http_response_code(404);$page='404';}
if($page==='article' && article_for_slug((string)$slug)===null)http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: DENY');
echo render_site($page,$slug);
