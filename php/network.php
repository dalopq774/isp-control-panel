<?php
header('Content-Type: application/json');
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit;
}

$uid = $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM network_info WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$info   = mysqli_fetch_assoc($result);

// Если записи ещё нет — создать
if (!$info) {
    $ins = mysqli_prepare($conn, "INSERT INTO network_info (user_id) VALUES (?)");
    mysqli_stmt_bind_param($ins, "i", $uid);
    mysqli_stmt_execute($ins);
    $info = [
        'connection_type' => 'Ethernet',
        'external_ip'     => '0.0.0.0',
        'status'          => 'connected',
    ];
}

// Получить реальный IP клиента (внешний)
$client_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// Логи авторизации (последние 5)
$logs_stmt = mysqli_prepare($conn,
    "SELECT ip, status, created_at FROM auth_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5"
);
mysqli_stmt_bind_param($logs_stmt, "i", $uid);
mysqli_stmt_execute($logs_stmt);
$logs_result = mysqli_stmt_get_result($logs_stmt);
$logs = [];
while ($row = mysqli_fetch_assoc($logs_result)) {
    $logs[] = $row;
}

echo json_encode([
    'success'         => true,
    'connection_type' => $info['connection_type'],
    'external_ip'     => $info['external_ip'],
    'status'          => $info['status'],
    'client_ip'       => $client_ip,
    'auth_logs'       => $logs,
    'updated_at'      => $info['updated_at'] ?? date('Y-m-d H:i:s'),
]);
