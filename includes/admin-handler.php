<?php
declare(strict_types=1);
// Admin area helpers: single shared password (ADMIN_PASSWORD_HASH), stateless signed
// session cookie (APP_SECRET) and enquiry queries. Requires the PostgreSQL storage.
require_once __DIR__ . '/contact-handler.php';

const ADMIN_SESSION_TTL = 43200; // 12 hours
const ADMIN_PAGE_SIZE = 50;

function admin_password_hash(): string {
    $hash = getenv('ADMIN_PASSWORD_HASH') ?: '';
    return password_get_info($hash)['algo'] ? $hash : '';
}
function admin_ready(): bool {
    return admin_password_hash() !== '' && contact_secret() !== '' && db_ready();
}
function admin_verify_password(string $password): bool {
    $hash = admin_password_hash();
    return $hash !== '' && $password !== '' && strlen($password) <= 256 && password_verify($password, $hash);
}
function admin_sign(string $payload, ?string $secret = null): string {
    return hash_hmac('sha256', $payload, $secret ?? contact_secret());
}
// Session cookie: "<expires-unix>.<hmac>". Nothing is stored server-side.
function admin_session_value(?int $now = null, ?string $secret = null): string {
    $expires = (string)(($now ?? time()) + ADMIN_SESSION_TTL);
    return $expires . '.' . admin_sign('admin-session|' . $expires, $secret);
}
function admin_session_valid(string $cookie, ?int $now = null, ?string $secret = null): bool {
    if (!preg_match('/^(\d{1,12})\.([a-f0-9]{64})$/', $cookie, $m)) return false;
    if ((int)$m[1] <= ($now ?? time())) return false;
    return hash_equals(admin_sign('admin-session|' . $m[1], $secret), $m[2]);
}
// Form token bound to the current session cookie value (double submit, signed).
function admin_form_token(string $sessionValue, ?string $secret = null): string {
    return admin_sign('admin-form|' . $sessionValue, $secret);
}
function admin_form_token_valid(string $token, string $sessionValue, ?string $secret = null): bool {
    return preg_match('/^[a-f0-9]{64}$/', $token) === 1 && hash_equals(admin_form_token($sessionValue, $secret), $token);
}
// Login form token: valid for the current and previous clock hour, no session needed.
function admin_login_token(?int $now = null, ?string $secret = null): string {
    return admin_sign('admin-login|' . intdiv($now ?? time(), 3600), $secret);
}
function admin_login_token_valid(string $token, ?int $now = null, ?string $secret = null): bool {
    if (preg_match('/^[a-f0-9]{64}$/', $token) !== 1) return false;
    $hour = intdiv($now ?? time(), 3600);
    return hash_equals(admin_sign('admin-login|' . $hour, $secret), $token)
        || hash_equals(admin_sign('admin-login|' . ($hour - 1), $secret), $token);
}
// Login attempts share the contact limiter (5 per hour) under their own bucket.
function admin_login_allowed(string $ip): bool {
    return contact_rate_allowed('admin-login|' . $ip);
}

function admin_counts(\PDO $pdo): array {
    $row = $pdo->query('SELECT count(*) FILTER (WHERE handled_at IS NULL) AS open, count(*) FILTER (WHERE handled_at IS NOT NULL) AS done FROM contact_enquiries')->fetch(\PDO::FETCH_ASSOC);
    return ['open' => (int)($row['open'] ?? 0), 'done' => (int)($row['done'] ?? 0)];
}
function admin_enquiries(\PDO $pdo, string $status, string $query, int $page): array {
    $where = []; $params = [];
    if ($status === 'open') $where[] = 'handled_at IS NULL';
    elseif ($status === 'done') $where[] = 'handled_at IS NOT NULL';
    if ($query !== '') {
        $where[] = '(name ILIKE :q OR company ILIKE :q OR email ILIKE :q OR message ILIKE :q)';
        $params['q'] = '%' . addcslashes($query, '%_\\') . '%';
    }
    $sql = 'SELECT id, locale, name, company, email, type, message, mail_sent, handled_at, created_at FROM contact_enquiries'
        . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
        . ' ORDER BY created_at DESC LIMIT ' . (ADMIN_PAGE_SIZE + 1) . ' OFFSET ' . (max(1, $page) - 1) * ADMIN_PAGE_SIZE;
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
    $more = count($rows) > ADMIN_PAGE_SIZE;
    return ['rows' => array_slice($rows, 0, ADMIN_PAGE_SIZE), 'more' => $more];
}
function admin_set_handled(\PDO $pdo, int $id, bool $handled): bool {
    $statement = $pdo->prepare('UPDATE contact_enquiries SET handled_at = ' . ($handled ? 'now()' : 'NULL') . ' WHERE id = :id');
    $statement->execute(['id' => $id]);
    return $statement->rowCount() === 1;
}
