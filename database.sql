-- Database Schema untuk Aplikasi Pelaporan Kerusakan Fasilitas Sekolah
-- Buat database baru
CREATE DATABASE IF NOT EXISTS learning1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE learning1;

-- Tabel Users (untuk semua role: siswa, admin, teknisi)
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('siswa', 'admin', 'teknisi') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Laporan
CREATE TABLE IF NOT EXISTS laporan (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT NOT NULL,
    lokasi VARCHAR(100) NOT NULL,
    foto VARCHAR(255) NULL,
    status ENUM('open', 'process', 'done', 'reject') NOT NULL DEFAULT 'open',
    assigned_to INT NULL,
    tanggal_lapor DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_tanggal_lapor (tanggal_lapor),
    INDEX idx_lokasi (lokasi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Tracking Progress
CREATE TABLE IF NOT EXISTS tracking_progress (
    tracking_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    technician_id INT NULL,
    status_awal ENUM('open', 'process', 'done', 'reject') NOT NULL,
    status_akhir ENUM('open', 'process', 'done', 'reject') NOT NULL,
    catatan TEXT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES laporan(report_id) ON DELETE CASCADE,
    FOREIGN KEY (technician_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_report_id (report_id),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Sample Users
-- Password untuk semua user: password123
-- Hash menggunakan password_hash('password123', PASSWORD_DEFAULT)

INSERT INTO users (username, password, nama_lengkap, role) VALUES
('siswa1', '$2y$10$/LBlQtj.s73b80yEXSOj2uuNN41AACXa.fZ84mC069ocC4qdxHdbO', 'Ahmad Fauzi', 'siswa'),
('siswa2', '$2y$10$/LBlQtj.s73b80yEXSOj2uuNN41AACXa.fZ84mC069ocC4qdxHdbO', 'Siti Nurhaliza', 'siswa'),
('siswa3', '$2y$10$/LBlQtj.s73b80yEXSOj2uuNN41AACXa.fZ84mC069ocC4qdxHdbO', 'Budi Santoso', 'siswa'),
('admin1', '$2y$10$/LBlQtj.s73b80yEXSOj2uuNN41AACXa.fZ84mC069ocC4qdxHdbO', 'Ibu Ratna', 'admin'),
('admin2', '$2y$10$/LBlQtj.s73b80yEXSOj2uuNN41AACXa.fZ84mC069ocC4qdxHdbO', 'Pak Hendra', 'admin'),
('teknisi1', '$2y$10$/LBlQtj.s73b80yEXSOj2uuNN41AACXa.fZ84mC069ocC4qdxHdbO', 'Joko Widodo', 'teknisi'),
('teknisi2', '$2y$10$/LBlQtj.s73b80yEXSOj2uuNN41AACXa.fZ84mC069ocC4qdxHdbO', 'Andi Pratama', 'teknisi');

-- Insert Sample Laporan untuk testing
INSERT INTO laporan (user_id, judul, deskripsi, lokasi, status, tanggal_lapor) VALUES
(1, 'Lampu Kelas Mati', 'Lampu di kelas XII-A tidak menyala sejak kemarin', 'Gedung A - Lantai 2 - Kelas XII-A', 'open', NOW()),
(2, 'Meja Rusak', 'Meja nomor 5 kakinya patah', 'Gedung B - Lantai 1 - Kelas X-B', 'open', NOW()),
(3, 'AC Tidak Dingin', 'AC di ruang guru tidak dingin', 'Gedung A - Lantai 1 - Ruang Guru', 'open', NOW());
