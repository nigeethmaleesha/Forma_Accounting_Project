<?php
declare(strict_types=1);
// Vercel publishes /preview only. This file executes exclusively on the PHP host.
if (!defined('APP_ROOT')) define('APP_ROOT',dirname(__DIR__));
if (!function_exists('env_load')) {
 function env_load(): void {
   $file=APP_ROOT.'/.env';if(!is_readable($file))return;
   foreach(file($file,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line){
     $line=trim($line);if($line===''||str_starts_with($line,'#')||!str_contains($line,'='))continue;
     [$k,$v]=explode('=',$line,2);$k=trim($k);$v=trim($v);
     if(!preg_match('/^[A-Z][A-Z0-9_]*$/',$k)||getenv($k)!==false)continue;
     if(strlen($v)>1 && (($v[0]==='"'&&str_ends_with($v,'"'))||($v[0]==="'"&&str_ends_with($v,"'"))))$v=substr($v,1,-1);
     putenv($k.'='.$v);
   }
 }
}
env_load();
if (PHP_SAPI!=='cli') {
 ini_set('session.use_strict_mode','1');
 ini_set('session.use_only_cookies','1');
 ini_set('session.cookie_httponly','1');
 $https=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')||($_SERVER['HTTP_X_FORWARDED_PROTO']??'')==='https';
 session_name('TTS_PORTAL');
 session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$https,'httponly'=>true,'samesite'=>'Lax']);
 session_start();
}
function db(): PDO {
 static $db=null; if($db instanceof PDO)return $db;
 if(!extension_loaded('pdo_mysql'))throw new RuntimeException('PDO MySQL Is Required On The PHP Host.');
 $host=getenv('DB_HOST')?:'localhost';$name=getenv('DB_NAME')?:'';$user=getenv('DB_USER')?:'';$pass=getenv('DB_PASSWORD')?:'';
 if($name===''||$user==='')throw new RuntimeException('Database Configuration Is Missing.');
 $db=new PDO('mysql:host='.$host.';dbname='.$name.';charset=utf8mb4',$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
 return $db;
}
function csrf_token(): string {if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32));return $_SESSION['csrf'];}
function csrf_validate(): void {if(!hash_equals(csrf_token(),(string)($_POST['csrf']??'')))fail('Your Session Has Expired. Please Refresh And Try Again.',419);}
function json_out(array $data,int $status=200): never {http_response_code($status);header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store, private');echo json_encode($data,JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE);exit;}
function fail(string $message,int $code=400): never {json_out(['ok'=>false,'message'=>$message],$code);}
function succeed(string $message,array $extra=[]): never {json_out(['ok'=>true,'message'=>$message]+$extra);}
function text_len(string $value): int {return function_exists('mb_strlen')?mb_strlen($value,'UTF-8'):strlen($value);}
function clean_text(mixed $value,int $max=200): string {if(!is_string($value))return ''; $v=trim(preg_replace('/\s+/u',' ',$value)??'');return function_exists('mb_substr')?mb_substr($v,0,$max,'UTF-8'):substr($v,0,$max);}
function user_auth(): ?array {
 $id=$_SESSION['user_id']??null;if(!$id||!is_numeric($id))return null;
 $q=db()->prepare('SELECT id,name,email,phone,role FROM users WHERE id=? AND active=1 LIMIT 1');$q->execute([(int)$id]);return $q->fetch()?:null;
}
function require_auth(bool $admin=false): array {
 $user=user_auth();if(!$user)fail('Please Sign In To Continue.',401);
 if($admin && $user['role']!=='admin')fail('Administrator Access Is Required.',403);
 return $user;
}
function storage_dir(): string {
 $path=getenv('PRIVATE_STORAGE_PATH')?:dirname(APP_ROOT).'/tts-private-documents';
 if(!str_starts_with($path,'/'))throw new RuntimeException('PRIVATE_STORAGE_PATH Must Be An Absolute Path.');
 if(!is_dir($path)&&!mkdir($path,0700,true)&&!is_dir($path))throw new RuntimeException('Cannot Create Secure Document Storage.');
 $storage=realpath($path);$root=realpath((string)($_SERVER['DOCUMENT_ROOT']??APP_ROOT));
 if(!$storage)throw new RuntimeException('Storage Is Unavailable.');
 if($root&&($storage===$root||str_starts_with($storage,$root.DIRECTORY_SEPARATOR)))throw new RuntimeException('Document Storage Must Be Outside The Web-Accessible Document Root.');
 return $storage;
}
