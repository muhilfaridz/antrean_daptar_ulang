CREATE DATABASE IF NOT EXISTS antrean_daptar_ulang;
USE antrean_daptar_ulang;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'petugas') NOT NULL,
    loket INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS antrian (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nomor_antrian VARCHAR(10) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    jalur VARCHAR(50) NOT NULL,
    status_antrian ENUM('waiting', 'called', 'done') NOT NULL DEFAULT 'waiting',
    current_loket INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status_antrian (status_antrian),
    INDEX idx_current_loket (current_loket),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS panggilan_antrian (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    antrian_id INT UNSIGNED NOT NULL,
    nomor_antrian VARCHAR(10) NOT NULL,
    loket_id INT NOT NULL,
    status ENUM('called', 'repeat') NOT NULL DEFAULT 'called',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_antrian_id (antrian_id),
    INDEX idx_loket_id (loket_id),
    INDEX idx_created_at (created_at),
    CONSTRAINT fk_panggilan_antrian_antrian
        FOREIGN KEY (antrian_id) REFERENCES antrian(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;