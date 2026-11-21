<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__.'/st_lib.php';
try {
    echo json_encode(mte_install($argv[1] ?? null), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'Instalasi ST gagal: '.$e->getMessage().PHP_EOL);
    exit(1);
}
