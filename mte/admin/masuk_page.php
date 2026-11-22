<?php
if (!defined('MTE_PORTAL_ROUTED')) {
    http_response_code(404);
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'none'; style-src 'self'; img-src 'self' data:; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");

mte_portal_session_start();
$nextTool = $_GET['lanjut'] ?? '';
$nextTool = is_string($nextTool) && isset($adminTools[$nextTool]) ? $nextTool : '';
$destination = mte_portal_url($nextTool !== '' ? '/' . $nextTool . '/' : '/admin/');
$loginAction = mte_portal_url('/masuk' . ($nextTool !== '' ? '?lanjut=' . $nextTool : ''));
if (mte_portal_current_user()) {
    header('Location: ' . $destination);
    exit;
}

$pesanMasuk = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        mte_portal_require_csrf();
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        if (!is_string($username) || !is_string($password)) {
            throw new InvalidArgumentException('Nama pengguna atau password tidak cocok.');
        }
        if (mte_portal_login($username, $password)) {
            header('Location: ' . $destination, true, 303);
            exit;
        }
        $pesanMasuk = 'Nama pengguna atau password tidak cocok, atau akun sedang dikunci sementara.';
    } catch (Throwable $error) {
        $pesanMasuk = $error instanceof InvalidArgumentException || $error instanceof RuntimeException
            ? $error->getMessage()
            : 'Login belum dapat diproses. Coba lagi.';
    }
}

$basePathLogin = mte_portal_base_path();
$csrfLogin = mte_portal_csrf_token();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Masuk Admin · MTE UNSOED</title>
    <link rel="icon" type="image/svg+xml" href="<?= mte_portal_escape($basePathLogin) ?>/simulasi_assets/unsoed-favicon.svg?v=1">
    <link rel="stylesheet" href="<?= mte_portal_escape($basePathLogin) ?>/simulasi_assets/portal-admin.css?v=3">
</head>
<body class="login-page">
    <main class="login-backdrop">
        <div class="login-context" aria-hidden="true">
            <span class="login-brand-mark">MTE</span>
            <span>Magister Teknik Elektro</span>
        </div>
        <section class="login-dialog" role="dialog" aria-modal="true" aria-labelledby="login-title">
            <a class="login-close" href="<?= mte_portal_escape($basePathLogin) ?>/" aria-label="Kembali ke portal">×</a>
            <span class="admin-eyebrow">RUANG PENGELOLA</span>
            <h1 id="login-title">Masuk dashboard</h1>
            <p>Kelola informasi Program Studi Magister Teknik Elektro.</p>
            <?php if ($pesanMasuk !== ''): ?>
                <div class="admin-alert" role="alert"><?= mte_portal_escape($pesanMasuk) ?></div>
            <?php endif; ?>
            <form method="post" action="<?= mte_portal_escape($loginAction) ?>" class="login-form">
                <input type="hidden" name="csrf_token" value="<?= mte_portal_escape($csrfLogin) ?>">
                <label for="username">Nama pengguna</label>
                <input id="username" name="username" type="text" autocomplete="username" maxlength="80" required autofocus>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" maxlength="200" required>
                <button class="admin-button admin-button-primary" type="submit">Masuk</button>
            </form>
            <a class="login-back-link" href="<?= mte_portal_escape($basePathLogin) ?>/">Kembali ke portal MTE</a>
        </section>
    </main>
</body>
</html>
