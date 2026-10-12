<?php
/**
 * save.php — Simpan & baca konfigurasi STAMAD TV
 * Letak dalam folder yang sama dengan STAMADTV.HTML
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$FILE = __DIR__ . '/config.json';

/* ============ KATA LALUAN (pilihan) ============
   Biarkan kosong '' untuk tiada kata laluan.
   Jika diisi, admin perlu taip kata laluan untuk simpan. */
$PASSWORD = '';

/* ============ BACA (GET) ============ */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!file_exists($FILE)) {
        echo '{}';
        exit;
    }
    readfile($FILE);
    exit;
}

/* ============ SIMPAN (POST) ============ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'JSON tidak sah']);
        exit;
    }

    // Semak kata laluan (jika ditetapkan)
    if ($PASSWORD !== '') {
        $pass = isset($data['_password']) ? (string)$data['_password'] : '';
        if ($pass !== $PASSWORD) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Kata laluan salah']);
            exit;
        }
    }
    unset($data['_password']);

    // Tulis ke fail
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Backup dulu (jika fail sedia ada)
    if (file_exists($FILE)) {
        @copy($FILE, $FILE . '.bak');
    }

    if (@file_put_contents($FILE, $json, LOCK_EX) === false) {
        http_response_code(500);
        echo json_encode([
            'ok'    => false,
            'error' => 'Gagal menulis config.json. Pastikan folder boleh ditulis (chmod 755 atau 777).'
        ]);
        exit;
    }

    echo json_encode([
        'ok'   => true,
        'masa' => date('c'),
        'saiz' => strlen($json)
    ]);
    exit;
}

/* ============ KAEDAH LAIN ============ */
http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Kaedah tidak dibenarkan']);