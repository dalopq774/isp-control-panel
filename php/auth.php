<?php
header('Content-Type: application/json');
session_start();
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$login    = trim($data['login']    ?? '');
$password = trim($data['password'] ?? '');

if (!$login || !$password) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE login = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $login);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['login']   = $user['login'];

    // Лог успешного входа
    $log = mysqli_prepare($conn, "INSERT INTO auth_logs (user_id, ip, status) VALUES (?, ?, 'success')");
    mysqli_stmt_bind_param($log, "is", $user['id'], $ip);
    mysqli_stmt_execute($log);

    echo json_encode(['success' => true]);
} else {
    // Лог неудачного входа
    $uid = $user['id'] ?? null;
    $log = mysqli_prepare($conn, "INSERT INTO auth_logs (user_id, ip, status) VALUES (?, ?, 'fail')");
    mysqli_stmt_bind_param($log, "is", $uid, $ip);
    mysqli_stmt_execute($log);

    echo json_encode(['success' => false, 'message' => 'Неверный логин или пароль']);
}
