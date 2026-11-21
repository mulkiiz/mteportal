<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/portal_lib.php';

try {
    $result = mte_portal_install();
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, "Instalasi dashboard portal MTE gagal. " . $error->getMessage() . "\n");
    exit(1);
}
