<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit;
}

echo json_encode([
    'success' => true,
    'user_id' => $_SESSION['user_id'],
    'login'   => $_SESSION['login']
]);
