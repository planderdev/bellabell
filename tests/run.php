<?php
declare(strict_types=1);
define('APP_ROOT',dirname(__DIR__));
require APP_ROOT.'/includes/contact-handler.php';
$tests=0;$failures=[];
function check(bool $condition,string $message): void {
    global $tests,$failures;$tests++;
    if(!$condition) $failures[]=$message;
}
function keys_recursive(array $array,string $prefix=''): array {
    $keys=[];
    foreach($array as $key=>$value) {
        $path=$prefix.$key;
        if(is_array($value)) $keys=array_merge($keys,keys_recursive($value,$path.'.'));
        else $keys[]=$path;
    }
    sort($keys);return $keys;
}
$ko=require APP_ROOT.'/data/ko.php';
foreach(['en','zh-CN'] as $locale) {
    $data=require APP_ROOT.'/data/'.$locale.'.php';
    check(keys_recursive($ko)===keys_recursive($data),"Translation keys differ: $locale");
}
foreach(['ko','en','zh-CN'] as $locale) {
    $data=require APP_ROOT.'/data/'.$locale.'.php';
    array_walk_recursive($data,function($value)use($locale){
        check(is_string($value)&&trim($value)!=='',"$locale has empty/non-string text");
        check(!str_contains($value,'\\n'),"$locale has literal newline escape");
    });
}
$valid=['name'=>'QA Partner','company'=>'Example Test','email'=>'qa@example.test','type'=>'partnership','message'=>str_repeat('Synthetic test message. ',3),'consent'=>'1'];
check(validate_contact($valid)===[],'Valid request rejected');
$korean=$valid;$korean['name']='홍길동';$korean['message']=str_repeat('가',20);
check(validate_contact($korean)===[],'Unicode length validation failed');
foreach([
    ['name'=>"Name\r\nBcc: bad@example.test",'expect'=>'name'],
    ['email'=>"qa@example.test\r\nBcc: bad@example.test",'expect'=>'email'],
    ['company'=>str_repeat('x',121),'expect'=>'company'],
    ['message'=>str_repeat('x',5001),'expect'=>'message'],
    ['message'=>'short','expect'=>'message'],
    ['type'=>'injected','expect'=>'type'],
    ['consent'=>'0','expect'=>'consent'],
    ['email'=>['bad'],'expect'=>'email'],
] as $case) {
    $expected=$case['expect'];unset($case['expect']);
    check(isset(validate_contact(array_replace($valid,$case))[$expected]),"Invalid $expected accepted");
}
$session=['contact_csrf'=>str_repeat('a',64),'contact_issued'=>100];
$post=['csrf'=>str_repeat('a',64),'website'=>''];
check(valid_contact_token($post,$session,104),'Valid token rejected');
check(!valid_contact_token($post,$session,101),'Fast bot submission accepted');
check(!valid_contact_token($post,$session,8000),'Expired token accepted');
check(!valid_contact_token(['csrf'=>'wrong'],$session,104),'Bad CSRF accepted');
check(!valid_contact_token(['csrf'=>str_repeat('a',64),'website'=>'bot'],$session,104),'Honeypot accepted');
check(!valid_contact_token(['csrf'=>[]],$session,104),'Array CSRF accepted');
$rateDir=sys_get_temp_dir().'/bellabell-test-'.bin2hex(random_bytes(5));
for($i=0;$i<5;$i++) check(contact_rate_allowed('127.0.0.55',10000+$i,$rateDir),'Early rate limit');
check(!contact_rate_allowed('127.0.0.55',10010,$rateDir),'Sixth request not limited');
check(contact_rate_allowed('127.0.0.55',14000,$rateDir),'Rate limit did not expire');
foreach(glob($rateDir.'/*.json')?:[] as $file) unlink($file);
rmdir($rateDir);
$company=require APP_ROOT.'/data/company.php';
check(count(array_filter($company['research'],fn($item)=>$item['status']==='draft'))===1,'Draft fact fixture missing');
$media=require APP_ROOT.'/data/media.php';
foreach($media as $key=>$item) check(is_file(APP_ROOT.'/assets/'.$item['file']),"Missing asset: $key");
if($failures){fwrite(STDERR,implode("\n",$failures)."\n");exit(1);}
echo "$tests assertions passed.\n";
