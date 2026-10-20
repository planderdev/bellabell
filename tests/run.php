<?php
declare(strict_types=1);
define('APP_ROOT',dirname(__DIR__));
require APP_ROOT.'/includes/admin-handler.php';
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
$token=issue_contact_token(100);
check(preg_match('/^[a-f0-9]{104}$/',$token)===1,'Token format');
$post=['csrf'=>$token,'website'=>''];
check(valid_contact_token($post,104),'Valid token rejected');
check(!valid_contact_token($post,101),'Fast bot submission accepted');
check(!valid_contact_token($post,8000),'Expired token accepted');
check(!valid_contact_token(['csrf'=>'wrong'],104),'Bad CSRF accepted');
check(!valid_contact_token(['csrf'=>substr($token,0,103).(substr($token,-1)==='0'?'1':'0')],104),'Tampered signature accepted');
check(!valid_contact_token(['csrf'=>'0000ffff'.substr($token,8)],104),'Tampered timestamp accepted');
check(!valid_contact_token(['csrf'=>$token,'website'=>'bot'],104),'Honeypot accepted');
check(!valid_contact_token(['csrf'=>[]],104),'Array CSRF accepted');
check(!valid_contact_token($post,104,contact_token_nonce($token)),'Used nonce accepted');
check(valid_contact_token($post,104,str_repeat('f',32)),'Different nonce rejected');
check(contact_token_nonce($token)===substr($token,8,32),'Nonce extraction');
check(contact_token_nonce('short')==='','Nonce from bad token');
// Admin desk: signed session cookie, session-bound form token, hourly login token.
$secret=str_repeat('s',40);
$session=admin_session_value(1000,$secret);
check(preg_match('/^\d+\.[a-f0-9]{64}$/',$session)===1,'Admin session format');
check(admin_session_valid($session,1000+ADMIN_SESSION_TTL-1,$secret),'Valid admin session rejected');
check(!admin_session_valid($session,1000+ADMIN_SESSION_TTL,$secret),'Expired admin session accepted');
check(!admin_session_valid($session,1001,'other-secret-other-secret-other-secret'),'Admin session with wrong secret accepted');
check(!admin_session_valid(substr($session,0,-1).(substr($session,-1)==='0'?'1':'0'),1001,$secret),'Tampered admin session accepted');
check(!admin_session_valid('',1001,$secret) && !admin_session_valid('abc',1001,$secret),'Garbage admin session accepted');
$form=admin_form_token($session,$secret);
check(admin_form_token_valid($form,$session,$secret),'Valid admin form token rejected');
check(!admin_form_token_valid($form,admin_session_value(2000,$secret),$secret),'Form token accepted for another session');
check(!admin_form_token_valid('',$session,$secret),'Empty form token accepted');
$login=admin_login_token(7200,$secret);
check(admin_login_token_valid($login,7200+3599,$secret),'Login token rejected in same hour');
check(admin_login_token_valid($login,7200+3600,$secret),'Login token rejected in next hour');
check(!admin_login_token_valid($login,7200+7200,$secret),'Login token accepted two hours later');
check(!admin_login_token_valid('nope',7200,$secret),'Bad login token accepted');
putenv('ADMIN_PASSWORD_HASH=');check(!admin_verify_password('anything'),'Password verified without hash');
putenv('ADMIN_PASSWORD_HASH='.password_hash('correct horse',PASSWORD_DEFAULT));
check(admin_verify_password('correct horse') && !admin_verify_password('wrong') && !admin_verify_password(''),'Password verification');
putenv('ADMIN_PASSWORD_HASH=not-a-hash');check(admin_password_hash()==='' && !admin_verify_password('x'),'Malformed hash treated as unset');
putenv('ADMIN_PASSWORD_HASH');
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
