-- ============================================
-- Создание базы данных и таблиц
-- Импортировать через phpMyAdmin
-- ============================================

CREATE DATABASE IF NOT EXISTS network_dashboard
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE network_dashboard;

-- Таблица пользователей
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  login VARCHAR(64) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица логов авторизации
CREATE TABLE IF NOT EXISTS auth_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  ip VARCHAR(45),
  status ENUM('success', 'fail') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Таблица сетевых настроек (для демонстрации)
CREATE TABLE IF NOT EXISTS network_info (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  connection_type VARCHAR(32) DEFAULT 'Ethernet',
  external_ip VARCHAR(45) DEFAULT '0.0.0.0',
  status ENUM('connected', 'disconnected') DEFAULT 'connected',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Тестовый пользователь: login=admin, password=admin123
INSERT INTO users (login, password) VALUES
  ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Сетевая инфа для admin
INSERT INTO network_info (user_id, connection_type, external_ip, status) VALUES
  (1, 'Ethernet', '10.100.12.23', 'connected');
