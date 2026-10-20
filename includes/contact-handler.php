<?php
declare(strict_types=1);

function unicode_length(string $value): int {
    return preg_match_all('/./us', $value, $unused) ?: 0;
}
function contact_value(array $input, string $key): string {
    return isset($input[$key]) && is_string($input[$key]) ? trim($input[$key]) : '';
}
function validate_contact(array $input): array {
    $errors = [];
    $name = contact_value($input, 'name');
    $organization = contact_value($input, 'company');
    $email = contact_value($input, 'email');
    $type = contact_value($input, 'type');
    $message = contact_value($input, 'message');
    if (unicode_length($name)<2 || unicode_length($name)>80 || preg_match('/[\r\n\x00]/', $name)) $errors['name']='nameError';
    if (unicode_length($organization)>120 || preg_match('/[\r\n\x00]/', $organization)) $errors['company']='companyError';
    if (strlen($email)>254 || !filter_var($email,FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n\x00]/', $email)) $errors['email']='emailError';
    if (!in_array($type, ['distribution','partnership','global','other'], true)) $errors['type']='typeError';
    if (unicode_length($message)<20 || unicode_length($message)>5000 || str_contains($message,"\0")) $errors['message']='messageError';
    if (contact_value($input,'consent') !== '1') $errors['consent']='consentError';
    return $errors;
}
function valid_contact_token(array $input, array $session, ?int $now=null): bool {
    $token=contact_value($input,'csrf');
    $issued=$session['contact_issued']??0;
    $age=($now??time())-(is_int($issued)?$issued:0);
    return isset($session['contact_csrf']) && is_string($session['contact_csrf'])
        && $token !== '' && hash_equals($session['contact_csrf'],$token)
        && $age>=3 && $age<=7200 && contact_value($input,'website')==='';
}
function contact_rate_allowed(string $ip, ?int $now=null, ?string $directory=null): bool {
    $now ??= time();
    $directory ??= getenv('CONTACT_RATE_DIR') ?: sys_get_temp_dir() . '/bellabell-rate-' . substr(hash('sha256',APP_ROOT),0,16);
    if (!is_dir($directory) && !@mkdir($directory,0700,true) && !is_dir($directory)) return false;
    $rootReal=realpath(APP_ROOT);
    $directoryReal=realpath($directory);
    if (!$directoryReal || ($rootReal && (strtolower($directoryReal)===strtolower($rootReal) || str_starts_with(strtolower($directoryReal), strtolower($rootReal) . DIRECTORY_SEPARATOR)))) return false;
    // Only our own hashed rate records are eligible for expiry cleanup.
    foreach (array_slice(glob($directoryReal.'/rate-*.json')?:[],0,256) as $expired) {
        if (preg_match('/^rate-[a-f0-9]{64}\.json$/',basename($expired)) && !is_link($expired) && filemtime($expired)<time()-3600) @unlink($expired);
    }
    $file = $directoryReal . '/rate-' . hash('sha256', $ip) . '.json';
    $handle=@fopen($file,'c+');
    if (!$handle) return false;
    try {
        if (!flock($handle,LOCK_EX)) return false;
        $raw=stream_get_contents($handle);
        $stored=json_decode($raw?:'[]',true);
        $times=is_array($stored)?array_values(array_filter($stored,fn($stamp)=>is_int($stamp)&&$stamp>$now-3600)):[];
        if (count($times)>=5) return false;
        $times[]=$now;
        rewind($handle);ftruncate($handle,0);
        if (fwrite($handle,json_encode($times))===false) return false;
        fflush($handle);
        return true;
    } finally {flock($handle,LOCK_UN);fclose($handle);}
}
function mail_ready(): bool {
    $test = getenv('APP_ENV') === 'test';
    $transport = getenv('MAIL_TRANSPORT') ?: 'disabled';
    return $transport === 'smtp'
        && (bool)getenv('SMTP_HOST')
        && (bool)filter_var(getenv('MAIL_FROM'),FILTER_VALIDATE_EMAIL)
        && (bool)filter_var(getenv('MAIL_TO') ?: 'partner@jejubellabell.com',FILTER_VALIDATE_EMAIL)
        && ($test || (extension_loaded('openssl') && in_array(getenv('SMTP_ENCRYPTION')?:'tls',['tls','ssl'],true)))
        && is_file(APP_ROOT . '/vendor/autoload.php');
}
function send_contact_email(array $input, string $locale): bool {
    if (!mail_ready()) return false;
    require_once APP_ROOT . '/vendor/autoload.php';
    $mailer=new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mailer->isSMTP();
        $mailer->Host=getenv('SMTP_HOST');
        $mailer->Port=(int)(getenv('SMTP_PORT')?:587);
        $mailer->Timeout=15;
        $mailer->CharSet='UTF-8';
        $mailer->SMTPAuth=(bool)getenv('SMTP_USERNAME');
        $mailer->Username=getenv('SMTP_USERNAME')?:'';
        $mailer->Password=getenv('SMTP_PASSWORD')?:'';
        if (getenv('APP_ENV')==='test' && getenv('SMTP_ENCRYPTION')==='none' && in_array($mailer->Host,['127.0.0.1','localhost','::1'],true)) {
            $mailer->SMTPSecure='';$mailer->SMTPAutoTLS=false;
        } else {
            $mailer->SMTPSecure=(getenv('SMTP_ENCRYPTION')?:'tls')==='ssl'
                ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mailer->SMTPOptions=['ssl'=>['verify_peer'=>true,'verify_peer_name'=>true,'allow_self_signed'=>false]];
        }
        $mailer->setFrom(getenv('MAIL_FROM'),getenv('MAIL_FROM_NAME')?:'BELLABELL FARM');
        $mailer->addAddress(getenv('MAIL_TO')?:'partner@jejubellabell.com');
        $mailer->addReplyTo(contact_value($input,'email'),contact_value($input,'name'));
        // User input is confined to the text body and validated Reply-To. Header values are fixed.
        $mailer->Subject='[BELLABELL FARM] Partnership enquiry';
        $mailer->isHTML(false);
        $mailer->Body="Locale: {$locale}\nName: ".contact_value($input,'name')
            ."\nCompany: ".contact_value($input,'company')
            ."\nEmail: ".contact_value($input,'email')
            ."\nType: ".contact_value($input,'type')
            ."\n\n".contact_value($input,'message')."\n\nPrivacy consent: accepted";
        return $mailer->send();
    } catch (\Throwable $exception) {
        // Do not put contact contents, credentials or SMTP conversations in logs.
        error_log('Contact mail delivery failed (' . get_class($exception) . ').');
        return false;
    }
}
