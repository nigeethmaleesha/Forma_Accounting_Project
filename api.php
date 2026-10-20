<?php
declare(strict_types=1);
require_once __DIR__.'/server/bootstrap.php';
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Cache-Control: private, no-store');
$task=clean_text($_GET['task']??'',32);
$method=$_SERVER['REQUEST_METHOD']??'GET';
try {
 if($task==='confirm' && $method==='GET') {
   $token=(string)($_GET['token']??'');
   if(!preg_match('/^[a-f0-9]{64}$/',$token))fail('Invalid Confirmation Link.',400);
   $q=db()->prepare('UPDATE subscribers SET verified=1,verification_token_hash=NULL WHERE verification_token_hash=?');$q->execute([hash('sha256',$token)]);
   header('Location: /?newsletter='.($q->rowCount()?'confirmed':'expired'),true,303);exit;
 }
 if($task==='download' && $method==='GET') {
   $user=require_auth();$id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT);if(!$id)fail('Invalid Document.',400);
   $q=db()->prepare('SELECT * FROM documents WHERE id=?'.($user['role']==='admin'?'':' AND user_id=?').' LIMIT 1');
   $q->execute($user['role']==='admin'?[$id]:[$id,$user['id']]);$document=$q->fetch();if(!$document)fail('Document Not Found.',404);
   $file=storage_dir().DIRECTORY_SEPARATOR.$document['stored_name'];if(!is_file($file))fail('Document Is Unavailable. Contact Our Team.',404);
   header('Content-Type: '.$document['mime_type']);header('Content-Length: '.filesize($file));
   header('Content-Disposition: attachment; filename="document"; filename*=UTF-8\'\''.rawurlencode($document['original_name']));
   header('X-Content-Type-Options: nosniff');readfile($file);exit;
 }
 if($task==='session' && $method==='GET'){json_out(['ok'=>true,'user'=>user_auth()]);}
 if($task==='documents' && $method==='GET') {
  $user=require_auth();
  $q=db()->prepare('SELECT id,title,DATE_FORMAT(created_at,\'%e %M %Y\') AS created_at FROM documents WHERE user_id=? ORDER BY created_at DESC');$q->execute([$user['id']]);
  json_out(['ok'=>true,'documents'=>$q->fetchAll()]);
 }
 if($task==='admin_list' && $method==='GET') {
  require_auth(true);
  $pdo=db();
  $posts=$pdo->query('SELECT id,title,slug,category,excerpt,body,status,seo_title,seo_description,ai_summary FROM posts ORDER BY created_at DESC LIMIT 100')->fetchAll();
  $clients=$pdo->query("SELECT id,name,email FROM users WHERE role='client' ORDER BY created_at DESC LIMIT 200")->fetchAll();
  $inquiries=$pdo->query('SELECT name,email,service,message,created_at FROM inquiries ORDER BY created_at DESC LIMIT 100')->fetchAll();
  $subscribers=$pdo->query('SELECT email,verified,created_at FROM subscribers ORDER BY created_at DESC LIMIT 200')->fetchAll();
  $docs=$pdo->query('SELECT d.id,d.title,d.created_at,u.name AS client_name FROM documents d JOIN users u ON u.id=d.user_id ORDER BY d.created_at DESC LIMIT 200')->fetchAll();
  json_out(['ok'=>true,'posts'=>$posts,'clients'=>$clients,'inquiries'=>$inquiries,'subscribers'=>$subscribers,'documents'=>$docs]);
 }
 if($method!=='POST')fail('Method Not Allowed.',405);
 csrf_validate();
 // Site Origin Is Additionally Checked To Protect Sensitive Actions.
 $origin=(string)($_SERVER['HTTP_ORIGIN']??'');
 if($origin!==''){
   $host=parse_url($origin,PHP_URL_HOST);if(!$host||strtolower((string)$host)!==strtolower(explode(':',(string)($_SERVER['HTTP_HOST']??''))[0]))fail('Request Origin Is Not Allowed.',403);
 }
 if($task==='contact'){
   if(!empty($_POST['website']))succeed('Thank You. Your Enquiry Has Been Received.');
   throttle('contact',15);
   $first=clean_text($_POST['first_name']??'',90);$last=clean_text($_POST['last_name']??'',90);
   $email=strtolower(clean_text($_POST['email']??'',180));$phone=clean_text($_POST['phone']??'',40);
   $service=clean_text($_POST['service']??'',80);$message=trim((string)($_POST['message']??''));

   if(!filter_var($email,FILTER_VALIDATE_EMAIL)||text_len($first)<2||text_len($last)<1||text_len($message)<10||text_len($message)>5000||($_POST['consent']??'')!=='1')fail('Please Complete All Required Fields And Agree To Be Contacted.');
   $serviceCodes=['tax','advisory','books','payroll','plans','smsf','cfo','budget','individual','general'];
   if(!in_array($service,$serviceCodes,true))fail('Please Select A Valid Service.');
   if(getenv('TURNSTILE_SECRET')){
     $response=(string)($_POST['cf-turnstile-response']??'');
     if(!$response||!function_exists('curl_init'))fail('Human Verification Is Required. Please Try Again.',403);
     $c=curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
     curl_setopt_array($c,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query(['secret'=>getenv('TURNSTILE_SECRET'),'response'=>$response]),CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>8]);
     $verification=json_decode((string)curl_exec($c),true);curl_close($c);
     if(empty($verification['success']))fail('Human Verification Failed. Please Retry.',403);
   }
   $q=db()->prepare('INSERT INTO inquiries(name,email,phone,service,message) VALUES (?,?,?,?,?)');
   $q->execute([$first.' '.$last,$email,$phone,$service,$message]);
   $name=$first.' '.$last;
   $notice="New Website Enquiry\n\nName: ".$name."\nEmail: ".$email."\nPhone: ".$phone."\nService: ".$service."\n\nMessage:\n".$message;
   send_mail(getenv('MAIL_FROM')?:'info@trendytaxsolutions.com.au','New Website Enquiry - Trendy Tax Solutions',$notice);
   succeed('Thank You! Your Enquiry Has Been Received. Our Team Will Be In Touch.');
  }
 if($task==='newsletter'){
   throttle('newsletter',20);
   $email=strtolower(clean_text($_POST['email']??'',180));
   if(!filter_var($email,FILTER_VALIDATE_EMAIL)||($_POST['consent']??'')!=='1')fail('Please Provide A Valid Email Address And Consent.');
   $existing=db()->prepare('SELECT verified FROM subscribers WHERE email=? LIMIT 1');$existing->execute([$email]);if((int)$existing->fetchColumn()===1)succeed('This Email Address Is Already Subscribed.');
   $token=bin2hex(random_bytes(32));
   $q=db()->prepare('INSERT INTO subscribers(email,verified,verification_token_hash) VALUES (?,0,?) ON DUPLICATE KEY UPDATE verification_token_hash=IF(verified=1,NULL,VALUES(verification_token_hash))');$q->execute([$email,hash('sha256',$token)]);
   $subject='Confirm Your Trendy Tax Solutions Subscription';
   $site=rtrim(getenv('SITE_URL')?:'https://www.trendytaxsolutions.com.au','/');
   $content="Hello,\n\nPlease Confirm Your Newsletter Subscription:\n".$site.'/api.php?task=confirm&token='.$token."\n\nIf You Did Not Sign Up, You Can Ignore This Email.";
   if(!send_mail($email,$subject,$content))fail('We Could Not Send The Verification Email. Please Try Again Or Contact Us.',503);
   succeed('Please Check Your Inbox To Confirm Your Subscription.');
 }
 if($task==='login'){
   $email=strtolower(clean_text($_POST['email']??'',180));$password=(string)($_POST['password']??'');
   $ip=hash('sha256',(string)($_SERVER['REMOTE_ADDR']??'unknown'));
   $q=db()->prepare('SELECT COUNT(*) FROM auth_attempts WHERE ip_hash=? AND attempted_at>NOW()-INTERVAL 15 MINUTE');$q->execute([$ip]);
   if((int)$q->fetchColumn()>=8)fail('Too Many Attempts. Please Try Again Later.',429);
   $q=db()->prepare('SELECT id,password_hash,active FROM users WHERE email=? LIMIT 1');$q->execute([$email]);$u=$q->fetch();
   if(!$u||!(int)$u['active']||!password_verify($password,$u['password_hash'])){
     $q=db()->prepare('INSERT INTO auth_attempts(ip_hash) VALUES (?)');$q->execute([$ip]);
     usleep(300000);fail('Incorrect Email Or Password.',401);
   }
   session_regenerate_id(true);$_SESSION['user_id']=(int)$u['id'];$_SESSION['csrf']=bin2hex(random_bytes(32));
   // Send Fresh CSRF For Forms Rendered Before Login.
   succeed('You Are Now Signed In.',['csrf'=>$_SESSION['csrf']]);
 }
 if($task==='logout'){
   $_SESSION=[];if(ini_get('session.use_cookies')){setcookie(session_name(),'',time()-42000,'/');}session_destroy();succeed('Signed Out.');
 }
 if($task==='profile'){
   $user=require_auth();$name=clean_text($_POST['name']??'',180);$phone=clean_text($_POST['phone']??'',40);
   if(strlen($name)<2)fail('Please Enter Your Name.');
   $q=db()->prepare('UPDATE users SET name=?,phone=? WHERE id=?');$q->execute([$name,$phone,$user['id']]);succeed('Profile Updated Successfully.');
 }
 if($task==='admin_client'){
   require_auth(true);$name=clean_text($_POST['name']??'',180);$email=strtolower(clean_text($_POST['email']??'',180));$pass=(string)($_POST['password']??'');
   if(strlen($name)<2||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pass)<12)fail('Enter A Name, Valid Email And Password Of At Least 12 Characters.');
   $q=db()->prepare("INSERT INTO users(name,email,password_hash,role) VALUES (?,?,?,'client')");
   try{$q->execute([$name,$email,password_hash($pass,PASSWORD_DEFAULT)]);}catch(PDOException $e){if($e->getCode()==='23000')fail('An Account With That Email Already Exists.',409);throw $e;}
   succeed('Client Account Created. Share Login Details Privately.');
 }
 if($task==='admin_post'){
   require_auth(true);
   $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);$title=clean_text($_POST['title']??'',180);
   $slug=strtolower(clean_text($_POST['slug']??'',160));$category=clean_text($_POST['category']??'',120);
   $excerpt=clean_text($_POST['excerpt']??'',300);$seoTitle=clean_text($_POST['seo_title']??'',180);$seoDescription=clean_text($_POST['seo_description']??'',300);$aiSummary=clean_text($_POST['ai_summary']??'',900);
   $body=trim((string)($_POST['body']??''));$status=($_POST['status']??'')==='published'?'published':'draft';
   if(strlen($title)<4||!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/',$slug)||!$category||!$excerpt||text_len($body)<40)fail('Enter A Valid Title, URL Slug, Category, Description And Article Body (40+ Characters).');
   if(strlen($body)>200000)fail('Article Body Is Too Long.');
   // Markdown/Text Content Is Rendered Escaped, Preventing Script Injection.
   if($id){$q=db()->prepare('UPDATE posts SET title=?,slug=?,category=?,excerpt=?,body=?,seo_title=?,seo_description=?,ai_summary=?,status=?,published_at=IF(?=\'published\',COALESCE(published_at,NOW()),NULL) WHERE id=?');$params=[$title,$slug,$category,$excerpt,$body,$seoTitle?:null,$seoDescription?:null,$aiSummary?:null,$status,$status,$id];}
   else{$q=db()->prepare('INSERT INTO posts(title,slug,category,excerpt,body,seo_title,seo_description,ai_summary,status,published_at) VALUES (?,?,?,?,?,?,?,?,?,IF(?=\'published\',NOW(),NULL))');$params=[$title,$slug,$category,$excerpt,$body,$seoTitle?:null,$seoDescription?:null,$aiSummary?:null,$status,$status];}
   try{$q->execute($params);}catch(PDOException $e){if($e->getCode()==='23000')fail('This URL Slug Is Already In Use.',409);throw $e;}
   succeed($status==='published'?'Article Published Successfully.':'Draft Saved Successfully.');
 }
 if($task==='admin_upload'){
   require_auth(true);$id=filter_var($_POST['user_id']??null,FILTER_VALIDATE_INT);$title=clean_text($_POST['title']??'',180);
   $file=$_FILES['document']??null;
   if(!$id||strlen($title)<3||!$file||!is_array($file)||($file['error']??999)!==UPLOAD_ERR_OK)fail('Select A Client, Title And Valid File.');
   if((int)$file['size']>10*1024*1024||(int)$file['size']<1)fail('File Must Be Smaller Than 10 MB.');
   $q=db()->prepare("SELECT id FROM users WHERE id=? AND role='client' AND active=1");$q->execute([$id]);if(!$q->fetchColumn())fail('Client Not Found.');
   $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
   $allowed=['application/pdf'=>'pdf','image/png'=>'png','image/jpeg'=>'jpg','application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'xlsx','application/msword'=>'doc','application/vnd.ms-excel'=>'xls','application/CDFV2'=>'doc','application/x-ole-storage'=>'doc'];
   if(!isset($allowed[$mime]) && $mime!=='application/zip')fail('Unsupported File Type. Allowed: PDF, JPG, PNG, Word And Excel.');
   $original=basename(clean_text((string)($file['name']??'document'),255));$extension=strtolower(pathinfo($original,PATHINFO_EXTENSION));
   if(!in_array($extension,['pdf','jpg','jpeg','png','docx','xlsx','doc','xls'],true))fail('Unsupported File Extension.');
   if($mime==='application/zip'){
     if(!in_array($extension,['docx','xlsx'],true)||!class_exists('ZipArchive'))fail('Office Document Verification Is Not Available.');
     $zip=new ZipArchive();if($zip->open($file['tmp_name'])!==true)fail('Invalid Office Document.');
     $validZip=$zip->locateName('[Content_Types].xml')!==false && $zip->locateName($extension==='docx'?'word/document.xml':'xl/workbook.xml')!==false;
     $zip->close();if(!$validZip)fail('Invalid Office Document Contents.');
   }
   if((in_array($extension,['pdf','jpg','jpeg','png'])&&($allowed[$mime]??'')!==($extension==='jpeg'?'jpg':$extension)))fail('File Content Does Not Match Its Extension.');
   $name=bin2hex(random_bytes(24));$path=storage_dir().'/'.$name;
   if(!move_uploaded_file($file['tmp_name'],$path))fail('Could Not Save Your File. Please Try Again.',500);
   chmod($path,0600);
   try{$q=db()->prepare('INSERT INTO documents(user_id,title,stored_name,original_name,mime_type,size_bytes) VALUES (?,?,?,?,?,?)');$q->execute([$id,$title,$name,$original,$mime,(int)$file['size']]);}
   catch(Throwable $e){@unlink($path);throw $e;}
   succeed('Document Shared Securely With The Client.');
 }
 if($task==='admin_delete'){
   require_auth(true);$type=clean_text($_POST['type']??'',30);$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);if(!$id)fail('Invalid Item.');
   if($type==='post'){$q=db()->prepare('DELETE FROM posts WHERE id=?');$q->execute([$id]);succeed('Article Deleted.');}
   if($type==='document'){
     $q=db()->prepare('SELECT stored_name FROM documents WHERE id=?');$q->execute([$id]);$name=$q->fetchColumn();if(!$name)fail('Document Not Found.',404);
     $q=db()->prepare('DELETE FROM documents WHERE id=?');$q->execute([$id]);
     @unlink(storage_dir().'/'.$name);succeed('Document Deleted.');
   }
   fail('Unsupported Item Type.');
 }
 fail('Unknown Action.',404);
} catch (PDOException $e) {error_log('TTS Database Error: '.$e->getMessage());fail('A Server Error Occurred. Please Contact Support.',503);
} catch (Throwable $e) {error_log('TTS Server Error: '.$e->getMessage());fail('Service Temporarily Unavailable. Please Contact Our Team.',503);}

function throttle(string $key,int $seconds): void {
 $last=$_SESSION['rate_'.$key]??0;
 if(time()-(int)$last<$seconds)fail('Please Wait A Moment Before Submitting Another Request.',429);
 $_SESSION['rate_'.$key]=time();
}
function send_mail(string $recipient,string $subject,string $body): bool {
 $from=getenv('MAIL_FROM')?:'info@trendytaxsolutions.com.au';
 if(!filter_var($from,FILTER_VALIDATE_EMAIL)||!filter_var($recipient,FILTER_VALIDATE_EMAIL))return false;
 $header=['From: '.str_replace(["\r","\n"],'',$from),'MIME-Version: 1.0','Content-Type: text/plain; charset=UTF-8'];
 return @mail($recipient,$subject,$body,implode("\r\n",$header));
}
