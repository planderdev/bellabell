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

// ---------------------------------------------------------------------------
// Stateless CSRF token: hex(issued-at, 8) . nonce(32 hex) . hmac-sha256(64 hex).
// No server-side session is required, so the form works on serverless hosts.
// ---------------------------------------------------------------------------
function contact_secret(): string {
    $secret = getenv('APP_SECRET') ?: '';
    if ($secret !== '' && strlen($secret) >= 32) return $secret;
    if (getenv('APP_ENV') === 'production') return '';
    // Local development/test fallback. Production must set APP_SECRET (>= 32 chars).
    return hash('sha256', 'bellabell-local:' . APP_ROOT);
}
function contact_token_signature(string $issued, string $nonce, string $secret): string {
    return hash_hmac('sha256', $issued . $nonce, $secret);
}
function issue_contact_token(?int $now = null): string {
    $secret = contact_secret();
    if ($secret === '') return '';
    $issued = str_pad(dechex($now ?? time()), 8, '0', STR_PAD_LEFT);
    $nonce = bin2hex(random_bytes(16));
    return $issued . $nonce . contact_token_signature($issued, $nonce, $secret);
}
function contact_token_nonce(string $token): string {
    return strlen($token) === 104 ? substr($token, 8, 32) : '';
}
function valid_contact_token(array $input, ?int $now = null, string $usedNonce = ''): bool {
    $token = contact_value($input, 'csrf');
    $secret = contact_secret();
    if ($secret === '' || !preg_match('/^[a-f0-9]{104}$/', $token)) return false;
    $issued = substr($token, 0, 8);
    $nonce = substr($token, 8, 32);
    $signature = substr($token, 40, 64);
    if (!hash_equals(contact_token_signature($issued, $nonce, $secret), $signature)) return false;
    if ($usedNonce !== '' && hash_equals($usedNonce, $nonce)) return false;
    $age = ($now ?? time()) - (int)hexdec($issued);
    return $age >= 3 && $age <= 7200 && contact_value($input, 'website') === '';
}

// ---------------------------------------------------------------------------
// Optional PostgreSQL storage (DATABASE_URL). Used for enquiry persistence,
// shared rate limiting and single-use token enforcement across instances.
// ---------------------------------------------------------------------------
function db_ready(): bool {
    return (bool)getenv('DATABASE_URL') && extension_loaded('pdo_pgsql');
}
function db_connection(): ?\PDO {
    static $pdo = null;
    static $failed = false;
    if ($pdo instanceof \PDO) return $pdo;
    if ($failed || !db_ready()) return null;
    $parts = parse_url((string)getenv('DATABASE_URL'));
    if (!is_array($parts) || empty($parts['host'])) { $failed = true; return null; }
    $dsn = 'pgsql:host=' . $parts['host'] . ';port=' . ($parts['port'] ?? 5432) . ';dbname=' . ltrim($parts['path'] ?? '/postgres', '/');
    parse_str($parts['query'] ?? '', $query);
    $dsn .= ';sslmode=' . (preg_match('/^[a-z-]+$/', (string)($query['sslmode'] ?? '')) ? $query['sslmode'] : 'require');
    try {
        $pdo = new \PDO($dsn, rawurldecode($parts['user'] ?? ''), rawurldecode($parts['pass'] ?? ''), [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_TIMEOUT => 5,
        ]);
        return $pdo;
    } catch (\Throwable $exception) {
        $failed = true;
        error_log('Contact database connection failed (' . get_class($exception) . ').');
        return null;
    }
}
function contact_ip_hash(string $ip): string {
    return hash('sha256', $ip . '|' . contact_secret());
}
function contact_rate_allowed_db(\PDO $pdo, string $ip, string $nonce): bool {
    try {
        $ipHash = contact_ip_hash($ip);
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM contact_attempts WHERE created_at < now() - interval '1 day'");
        if ($nonce !== '') {
            $exists = $pdo->prepare('SELECT 1 FROM contact_attempts WHERE nonce = :nonce LIMIT 1');
            $exists->execute(['nonce' => $nonce]);
            if ($exists->fetchColumn()) { $pdo->rollBack(); return false; }
        }
        $count = $pdo->prepare("SELECT count(*) FROM contact_attempts WHERE ip_hash = :ip AND created_at > now() - interval '1 hour'");
        $count->execute(['ip' => $ipHash]);
        if ((int)$count->fetchColumn() >= 5) { $pdo->rollBack(); return false; }
        $insert = $pdo->prepare('INSERT INTO contact_attempts (ip_hash, nonce) VALUES (:ip, :nonce)');
        $insert->execute(['ip' => $ipHash, 'nonce' => $nonce !== '' ? $nonce : null]);
        $pdo->commit();
        return true;
    } catch (\Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('Contact rate check failed (' . get_class($exception) . ').');
        return false;
    }
}
function contact_rate_allowed(string $ip, ?int $now=null, ?string $directory=null, string $nonce=''): bool {
    $now ??= time();
    if ($directory === null && ($pdo = db_connection()) instanceof \PDO) {
        return contact_rate_allowed_db($pdo, $ip, $nonce);
    }
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
function store_contact_enquiry(array $input, string $locale, string $ip, bool $mailSent): bool {
    $pdo = db_connection();
    if (!$pdo instanceof \PDO) return false;
    try {
        $statement = $pdo->prepare('INSERT INTO contact_enquiries (locale, name, company, email, type, message, ip_hash, user_agent, mail_sent)
            VALUES (:locale, :name, :company, :email, :type, :message, :ip_hash, :user_agent, :mail_sent)');
        $statement->bindValue('locale', $locale);
        $statement->bindValue('name', contact_value($input, 'name'));
        $statement->bindValue('company', contact_value($input, 'company'));
        $statement->bindValue('email', contact_value($input, 'email'));
        $statement->bindValue('type', contact_value($input, 'type'));
        $statement->bindValue('message', contact_value($input, 'message'));
        $statement->bindValue('ip_hash', contact_ip_hash($ip));
        $statement->bindValue('user_agent', mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 512));
        $statement->bindValue('mail_sent', $mailSent, \PDO::PARAM_BOOL);
        return $statement->execute();
    } catch (\Throwable $exception) {
        error_log('Contact enquiry storage failed (' . get_class($exception) . ').');
        return false;
    }
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
// The form is enabled when a token can be signed and at least one delivery channel exists.
function contact_ready(): bool {
    return contact_secret() !== '' && (mail_ready() || db_ready());
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
