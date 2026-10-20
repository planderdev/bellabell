<?php
// Internal enquiry desk: /admin, /admin/login, /admin/logout (POST), /admin/handle (POST).
// Korean-only, no translations, no third-party JS. Requires ADMIN_PASSWORD_HASH + DATABASE_URL.
$page = 'admin';
$routeLang = 'ko';
require __DIR__ . '/includes/bootstrap.php';
require_once APP_ROOT . '/includes/admin-handler.php';
header('Cache-Control: no-store, max-age=0');
header('X-Robots-Tag: noindex, nofollow');
$action = $adminPath ?? '';
$cookie = (string)($_COOKIE['bellabell_admin'] ?? '');
$session = admin_session_valid($cookie) ? $cookie : '';
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$method = $_SERVER['REQUEST_METHOD'];
$cookieOptions = ['path' => '/admin', 'secure' => $isHttps, 'httponly' => true, 'samesite' => 'Strict'];

function admin_layout(string $title, string $body, int $status = 200): never {
    http_response_code($status);
    echo '<!doctype html><html lang="ko"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow"><title>' . e($title) . ' | BELLABELL FARM 관리</title>';
    foreach (['tokens', 'ui', 'base', 'admin'] as $css) echo '<link rel="stylesheet" href="' . e(asset('css/' . $css . '.css')) . '">';
    echo '</head><body class="root admin-body">' . $body . '</body></html>';
    exit;
}
function admin_time(?string $value): string {
    if (!$value) return '';
    try { return (new DateTime($value))->setTimezone(new DateTimeZone('Asia/Seoul'))->format('Y-m-d H:i'); } catch (\Throwable) { return substr($value, 0, 16); }
}
function admin_redirect(string $to): never { header('Location: ' . $to, true, 303); exit; }
function admin_query(array $overrides = []): string {
    $params = array_filter(array_merge(['status' => $_GET['status'] ?? 'open', 'q' => $_GET['q'] ?? '', 'page' => $_GET['page'] ?? ''], $overrides), fn($v) => $v !== '' && $v !== null);
    return $params ? '?' . http_build_query($params) : '';
}

if (!in_array($action, ['', 'login', 'logout', 'handle'], true)) { http_response_code(404); admin_layout('찾을 수 없음', '<main class="admin-shell"><h1>페이지를 찾을 수 없습니다.</h1><p><a class="text-link" href="/admin">문의 목록으로</a></p></main>', 404); }

if (!admin_ready()) {
    $missing = [];
    if (admin_password_hash() === '') $missing[] = 'ADMIN_PASSWORD_HASH';
    if (contact_secret() === '') $missing[] = 'APP_SECRET';
    if (!db_ready()) $missing[] = 'DATABASE_URL';
    admin_layout('설정 필요', '<main class="admin-shell"><h1>관리 화면이 아직 설정되지 않았습니다.</h1><p>필요한 환경변수: <code>' . e(implode(', ', $missing)) . '</code></p><p>비밀번호 해시는 <code>php -r "echo password_hash(\'비밀번호\', PASSWORD_DEFAULT);"</code>로 만듭니다.</p></main>', 503);
}

// ---- Login / logout --------------------------------------------------------
if ($action === 'login') {
    if ($session !== '') admin_redirect('/admin');
    $error = '';
    if ($method === 'POST') {
        $token = contact_value($_POST, 'token');
        $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
        if (!admin_login_token_valid($token)) { http_response_code(419); $error = '세션이 만료되었습니다. 다시 시도해 주세요.'; }
        elseif (!admin_login_allowed($clientIp)) { http_response_code(429); header('Retry-After: 3600'); $error = '시도 횟수를 초과했습니다. 1시간 뒤에 다시 시도해 주세요.'; }
        elseif (!admin_verify_password($password)) { http_response_code(401); $error = '비밀번호가 올바르지 않습니다.'; }
        else {
            setcookie('bellabell_admin', admin_session_value(), $cookieOptions + ['expires' => time() + ADMIN_SESSION_TTL]);
            admin_redirect('/admin');
        }
    }
    admin_layout('로그인', '<main class="admin-shell admin-login"><form class="admin-card" method="post" action="/admin/login" autocomplete="off">'
        . '<p class="eyebrow">BELLABELL FARM</p><h1>문의 관리</h1>'
        . ($error ? '<p class="admin-error" role="alert">' . e($error) . '</p>' : '')
        . '<input type="hidden" name="token" value="' . e(admin_login_token()) . '">'
        . '<div class="field"><label class="label" for="password">비밀번호</label><input class="input" type="password" id="password" name="password" required autofocus autocomplete="current-password"></div>'
        . '<button class="button button--block" type="submit">로그인</button></form></main>', $error ? http_response_code() : 200);
}
if ($session === '') admin_redirect('/admin/login');
$pdo = db_connection();
if (!$pdo instanceof \PDO) admin_layout('데이터베이스 오류', '<main class="admin-shell"><h1>데이터베이스에 연결할 수 없습니다.</h1><p>잠시 후 다시 시도해 주세요.</p></main>', 502);
$formToken = admin_form_token($session);

if ($action === 'logout') {
    if ($method === 'POST' && admin_form_token_valid(contact_value($_POST, 'token'), $session)) {
        setcookie('bellabell_admin', '', $cookieOptions + ['expires' => time() - 3600]);
    }
    admin_redirect('/admin/login');
}
if ($action === 'handle') {
    if ($method !== 'POST') admin_redirect('/admin');
    if (!admin_form_token_valid(contact_value($_POST, 'token'), $session)) { http_response_code(419); admin_layout('세션 만료', '<main class="admin-shell"><h1>세션이 만료되었습니다.</h1><p><a class="text-link" href="/admin">다시 열기</a></p></main>', 419); }
    $id = (int)contact_value($_POST, 'id');
    $done = contact_value($_POST, 'done') === '1';
    if ($id > 0) { try { admin_set_handled($pdo, $id, $done); } catch (\Throwable $exception) { error_log('Admin handled update failed (' . get_class($exception) . ').'); } }
    $back = contact_value($_POST, 'back');
    admin_redirect('/admin' . (preg_match('/^\?[A-Za-z0-9=&%+._-]*$/', $back) ? $back : '') . '#enquiry-' . $id);
}

// ---- List ------------------------------------------------------------------
$status = (string)($_GET['status'] ?? 'open');
if (!in_array($status, ['open', 'done', 'all'], true)) $status = 'open';
$query = mb_substr(trim((string)($_GET['q'] ?? '')), 0, 100);
$pageNo = max(1, (int)($_GET['page'] ?? 1));
try {
    $counts = admin_counts($pdo);
    $result = admin_enquiries($pdo, $status, $query, $pageNo);
} catch (\Throwable $exception) {
    error_log('Admin list query failed (' . get_class($exception) . ').');
    admin_layout('데이터베이스 오류', '<main class="admin-shell"><h1>문의 목록을 불러오지 못했습니다.</h1></main>', 502);
}
$types = ['distribution' => '유통·입점', 'partnership' => '브랜드 협업', 'global' => '해외 파트너십', 'other' => '기타'];
$locales = ['ko' => '한국어', 'en' => 'English', 'zh-CN' => '简体中文'];
$back = admin_query();
ob_start();
?>
<header class="admin-header">
    <div class="admin-shell admin-header-inner">
        <div><p class="eyebrow">BELLABELL FARM</p><h1>문의 관리</h1></div>
        <form method="post" action="/admin/logout"><input type="hidden" name="token" value="<?= e($formToken) ?>"><button class="button button--outline button--sm" type="submit">로그아웃</button></form>
    </div>
</header>
<main class="admin-shell">
    <div class="admin-toolbar">
        <nav class="admin-tabs" aria-label="상태">
            <?php foreach (['open' => '미처리 ' . $counts['open'], 'done' => '처리 완료 ' . $counts['done'], 'all' => '전체 ' . ($counts['open'] + $counts['done'])] as $key => $label): ?>
            <a class="chip <?= $status === $key ? 'is-active' : '' ?>" href="/admin<?= e(admin_query(['status' => $key, 'page' => ''])) ?>" <?= $status === $key ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <form class="admin-search" method="get" action="/admin" role="search">
            <input type="hidden" name="status" value="<?= e($status) ?>">
            <input class="input" type="search" name="q" value="<?= e($query) ?>" placeholder="이름, 회사, 이메일, 내용 검색" aria-label="검색">
            <button class="button button--weak" type="submit">검색</button>
        </form>
    </div>
    <?php if (!$result['rows']): ?>
    <p class="admin-empty" role="status"><?= $query !== '' ? '검색 결과가 없습니다.' : ($status === 'open' ? '미처리 문의가 없습니다.' : '문의가 없습니다.') ?></p>
    <?php else: ?>
    <ol class="admin-list">
        <?php foreach ($result['rows'] as $row): $handled = $row['handled_at'] !== null; ?>
        <li class="admin-card admin-item <?= $handled ? 'is-handled' : '' ?>" id="enquiry-<?= (int)$row['id'] ?>">
            <div class="admin-item-head">
                <div class="admin-meta">
                    <span class="admin-badge admin-badge--type"><?= e($types[$row['type']] ?? $row['type']) ?></span>
                    <span class="admin-badge"><?= e($locales[$row['locale']] ?? $row['locale']) ?></span>
                    <?php if ($row['mail_sent']): ?><span class="admin-badge">메일 발송됨</span><?php endif; ?>
                    <time datetime="<?= e($row['created_at']) ?>"><?= e(admin_time($row['created_at'])) ?> KST</time>
                </div>
                <form method="post" action="/admin/handle">
                    <input type="hidden" name="token" value="<?= e($formToken) ?>"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><input type="hidden" name="done" value="<?= $handled ? '0' : '1' ?>"><input type="hidden" name="back" value="<?= e($back) ?>">
                    <button class="button button--sm <?= $handled ? 'button--outline' : '' ?>" type="submit"><?= $handled ? '처리 취소' : '처리 완료' ?></button>
                </form>
            </div>
            <h2><?= e($row['name']) ?><?php if ($row['company'] !== ''): ?> <span class="admin-company">· <?= e($row['company']) ?></span><?php endif; ?></h2>
            <p class="admin-contact"><a class="text-link" href="mailto:<?= e($row['email']) ?>?subject=<?= rawurlencode('[BELLABELL FARM] 문의 답변') ?>"><?= e($row['email']) ?></a></p>
            <p class="admin-message"><?= nl2br(e($row['message']), false) ?></p>
            <?php if ($handled): ?><p class="admin-handled">처리 완료 <?= e(admin_time($row['handled_at'])) ?> KST</p><?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ol>
    <nav class="admin-pager" aria-label="페이지">
        <?php if ($pageNo > 1): ?><a class="button button--outline button--sm" href="/admin<?= e(admin_query(['page' => $pageNo - 1 > 1 ? $pageNo - 1 : ''])) ?>">이전</a><?php endif; ?>
        <?php if ($result['more']): ?><a class="button button--outline button--sm" href="/admin<?= e(admin_query(['page' => $pageNo + 1])) ?>">다음</a><?php endif; ?>
    </nav>
    <?php endif; ?>
</main>
<?php
admin_layout('문의 목록', ob_get_clean());
