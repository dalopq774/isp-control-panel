<?php
$conn = mysqli_connect('p:MySQL-8.4', 'root', '', 'network_dashboard', 3306);

if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => mysqli_connect_error()]);
    exit;
}

mysqli_set_charset($conn, 'utf8mb4');