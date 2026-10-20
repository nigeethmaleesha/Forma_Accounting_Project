<?php
declare(strict_types=1);
// Static Approval Preview Generator. No Database, Portal Credentials Or Client Documents Are Exported.
if(PHP_SAPI!=='cli')die('CLI Only');
define('STATIC_PREVIEW',true);
require_once dirname(__DIR__).'/includes/site.php';
$root=dirname(__DIR__);$out=$root.'/preview';
$pages=['home','about','services','contact','journal','portal','privacy','admin'];
foreach($pages as $page){
 $folder=$page==='home'?$out:$out.'/'.$page;
 if(!is_dir($folder))mkdir($folder,0755,true);
 file_put_contents($folder.'/index.html',render_site($page));
 echo 'Rendered '.$page."\n";
}
foreach(sample_posts() as $post){
 $folder=$out.'/journal/'.$post['slug'];if(!is_dir($folder))mkdir($folder,0755,true);
 file_put_contents($folder.'/index.html',render_site('article',$post['slug']));
}
$folder=$out.'/assets';if(!is_dir($folder))mkdir($folder,0755,true);
foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/assets',FilesystemIterator::SKIP_DOTS)) as $file){
 $rel=substr($file->getPathname(),strlen($root.'/assets')+1);$dest=$folder.'/'.$rel;
 if(!is_dir(dirname($dest)))mkdir(dirname($dest),0755,true);
 copy($file->getPathname(),$dest);
}
file_put_contents($out.'/robots.txt',"User-agent: *\nDisallow: /\n");
file_put_contents($out.'/404.html',render_site('404'));
echo "Static Vercel Preview Built At: $out\n";
