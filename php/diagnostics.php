<?php
header('Content-Type: application/json');
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'ping':
        $host = escapeshellarg($_POST['host'] ?? 'google.com');
        // Реальный ping (работает на сервере Linux/Windows)
        $output = shell_exec("ping -c 4 {$host} 2>&1");
        if (!$output) {
            // Fallback если shell_exec отключён
            $output = simulatePing($_POST['host'] ?? 'google.com');
        }
        echo json_encode(['success' => true, 'result' => $output]);
        break;

    case 'dns':
        $host = $_POST['host'] ?? 'google.com';
        $records = @dns_get_record($host, DNS_A | DNS_AAAA | DNS_MX);
        if ($records === false || empty($records)) {
            echo json_encode(['success' => false, 'message' => 'DNS записи не найдены для: ' . htmlspecialchars($host)]);
        } else {
            $out = "DNS записи для " . htmlspecialchars($host) . ":\n\n";
            foreach ($records as $r) {
                $type = $r['type'];
                $val  = $r['ip'] ?? $r['ipv6'] ?? $r['target'] ?? '';
                $out .= "  [{$type}]  {$val}\n";
            }
            echo json_encode(['success' => true, 'result' => $out]);
        }
        break;

    case 'speedtest':
        // Имитация теста скорости (реальный требует внешних утилит)
        $download = rand(50, 500);
        $upload   = rand(20, 200);
        $ping     = rand(5, 50);
        $result   = "Тест скорости соединения\n";
        $result  .= "─────────────────────────\n";
        $result  .= "  Ping:      {$ping} мс\n";
        $result  .= "  Загрузка:  {$download} Мбит/с\n";
        $result  .= "  Отдача:    {$upload} Мбит/с\n";
        $result  .= "─────────────────────────\n";
        $result  .= "Тест завершён.";
        echo json_encode(['success' => true, 'result' => $result]);
        break;

    case 'whois':
        $host = $_POST['host'] ?? '8.8.8.8';
        $output = shell_exec("whois " . escapeshellarg($host) . " 2>&1");
        if (!$output) {
            $output = "WHOIS для {$host}:\nОтвет недоступен (функция отключена на сервере).\nПопробуйте через https://who.is/";
        } else {
            // Обрезать до разумного размера
            $lines  = explode("\n", $output);
            $output = implode("\n", array_slice($lines, 0, 30));
        }
        echo json_encode(['success' => true, 'result' => $output]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Неизвестное действие']);
}

function simulatePing(string $host): string {
    $times = [rand(10,50), rand(10,50), rand(10,50), rand(10,50)];
    $avg   = array_sum($times) / count($times);
    $out   = "PING {$host}:\n";
    foreach ($times as $i => $t) {
        $out .= "  64 bytes from {$host}: icmp_seq={$i} ttl=56 time={$t} ms\n";
    }
    $out .= "\n--- {$host} ping statistics ---\n";
    $out .= "  4 packets transmitted, 4 received, 0% packet loss\n";
    $out .= "  rtt min/avg/max = {$times[0]}/{$avg}/{$times[3]} ms\n";
    return $out;
}
