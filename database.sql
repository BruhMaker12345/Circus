CREATE DATABASE IF NOT EXISTS circus_db 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE circus_db;

CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    message TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Администратор: логин admin, пароль admin
-- Пароль хешируется через password_hash('admin', PASSWORD_DEFAULT)
INSERT INTO admins (username, password) 
VALUES ('admin', '$2y$10$e0NRmQ6y1QKz7v8Zp3qKYeJZ9xLmYnB2cD3eF4gH5iJ6kL7mN8oPq');