<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit;
}

$recommendations = [
    [
        'title'       => 'Обновите прошивку роутера',
        'description' => 'Устаревшая прошивка может содержать уязвимости. Проверьте наличие обновлений в панели управления роутером.',
        'priority'    => 'high',
    ],
    [
        'title'       => 'Используйте DNS over HTTPS',
        'description' => 'Замените стандартный DNS на зашифрованный (1.1.1.1 от Cloudflare или 8.8.8.8 от Google) для повышения приватности.',
        'priority'    => 'medium',
    ],
    [
        'title'       => 'Включите брандмауэр',
        'description' => 'Убедитесь что брандмауэр на роутере и ПК активен для защиты от внешних угроз.',
        'priority'    => 'high',
    ],
    [
        'title'       => 'Смените стандартный пароль роутера',
        'description' => 'Стандартные пароли (admin/admin) легко угадать. Установите сложный уникальный пароль.',
        'priority'    => 'high',
    ],
    [
        'title'       => 'Используйте WPA3 шифрование',
        'description' => 'Если ваш роутер поддерживает WPA3 — включите его вместо WPA2 для более надёжного шифрования Wi-Fi.',
        'priority'    => 'medium',
    ],
    [
        'title'       => 'Отключите WPS',
        'description' => 'WPS (Wi-Fi Protected Setup) имеет известные уязвимости. Рекомендуется отключить эту функцию.',
        'priority'    => 'medium',
    ],
];

echo json_encode(['success' => true, 'recommendations' => $recommendations]);
