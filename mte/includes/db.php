<?php
declare(strict_types=1);

function mte_config(): array
{
    static $config = null;
    if ($config === null) {
        $path = getenv('MTE_CONFIG_PATH') ?: dirname(__DIR__, 3) . '/mte-private/config.php';
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('Konfigurasi database MTE belum tersedia.');
        }
        $settings = require $path;
        if (!is_array($settings) || empty($settings['database']) || empty($settings['username'])) {
            throw new RuntimeException('Konfigurasi database MTE tidak lengkap.');
        }

        $config = $settings + [
            'host' => '127.0.0.1',
            'port' => 3306,
            'password' => '',
            'st_upload_dir' => dirname($path) . '/st_files',
        ];
    }
    return $config;
}

function mte_db(): mysqli
{
    static $connection = null;
    if ($connection === null) {
        $config = mte_config();
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $candidate = new mysqli($config['host'], $config['username'], $config['password'], $config['database'], (int) $config['port']);
            $candidate->set_charset('utf8mb4');
        } catch (mysqli_sql_exception $error) {
            error_log('[MTE] Database connection failed: ' . $error->getCode());
            throw new RuntimeException('Koneksi database MTE belum tersedia.', 0, $error);
        }
        $connection = $candidate;
    }
    return $connection;
}
