<?php
declare(strict_types=1);

if (getenv('APP_ENV') === false) {
    putenv('APP_ENV=test');
    $_ENV['APP_ENV'] = 'test';
    $_SERVER['APP_ENV'] = 'test';
}
if (getenv('SKIP_DB') === false) {
    putenv('SKIP_DB=1');
    $_ENV['SKIP_DB'] = '1';
    $_SERVER['SKIP_DB'] = '1';
}

require_once dirname(__DIR__, 2) . '/includes/config.php';

$total = 0;
$passed = 0;

function check($condition, $message) {
    global $total, $passed;
    $total++;
    if ($condition) {
        $passed++;
        echo "[PASS] {$message}\n";
        return;
    }
    echo "[FAIL] {$message}\n";
}

echo "OgretmenPortali smoke tests basladi...\n";

// CSRF token
$token = get_csrf_token();
check(is_string($token) && strlen($token) === 64, 'CSRF token uzunlugu 64 hex');

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['csrf_token'] = $token;
check(validate_csrf_request() === true, 'CSRF validate true');

$_POST['csrf_token'] = 'invalid-token';
check(validate_csrf_request() === false, 'CSRF validate false');

// Password compatibility
$plain = 'Deneme123!';
$bcrypt = password_hash($plain, PASSWORD_DEFAULT);
$md5 = md5($plain);

check(verify_password_compat($plain, $bcrypt) === true, 'bcrypt verify');
check(verify_password_compat($plain, $md5) === true, 'md5 fallback verify');
check(verify_password_compat('yanlis', $md5) === false, 'md5 fallback negative');
check(should_upgrade_password_hash($md5) === true, 'md5 upgrade gerekli');

echo "Sonuc: {$passed}/{$total} test basarili.\n";
exit($passed === $total ? 0 : 1);
