<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit('Command Line Only');}
require_once dirname(__DIR__).'/server/bootstrap.php';
if($argc<4){fwrite(STDERR,"Usage: php tools/create_admin.php email@example.com \"Administrator Name\" \"LongUniquePassword\"\n");exit(1);}
$email=strtolower(trim($argv[1]));$name=trim($argv[2]);$password=$argv[3];
if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<12||!$name){fwrite(STDERR,"Please Provide A Valid Email, Name And 12+ Character Password.\n");exit(1);}
$q=db()->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,'admin') ON DUPLICATE KEY UPDATE name=VALUES(name),password_hash=VALUES(password_hash),role='admin'");
$q->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
fwrite(STDOUT,"Administrator Account Created Or Updated.\n");
