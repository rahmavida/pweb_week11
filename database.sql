-- LaundryCrafty - skema database
CREATE DATABASE IF NOT EXISTS laundrycrafty CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE laundrycrafty;

DROP TABLE IF EXISTS transaksi;
DROP TABLE IF EXISTS pelanggan;
DROP TABLE IF EXISTS layanan;
DROP TABLE IF EXISTS user;

CREATE TABLE pelanggan (
    id_pelanggan INT NOT NULL AUTO_INCREMENT,
    nama         VARCHAR(64)  NOT NULL,
    alamat       VARCHAR(255) NOT NULL,
    no_hp        VARCHAR(20)  NOT NULL,
    PRIMARY KEY (id_pelanggan)
) ENGINE=InnoDB;

CREATE TABLE layanan (
    id_layanan    INT NOT NULL AUTO_INCREMENT,
    nama_layanan  VARCHAR(64) NOT NULL,
    harga_per_kg  INT NOT NULL,
    PRIMARY KEY (id_layanan)
) ENGINE=InnoDB;

CREATE TABLE transaksi (
    id_transaksi    INT NOT NULL AUTO_INCREMENT,
    id_pelanggan    INT NOT NULL,
    id_layanan      INT NOT NULL,
    tanggal_masuk   DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    berat           DECIMAL(6,2) NOT NULL,
    total_harga     INT NOT NULL,
    status          ENUM('Proses','Selesai','Sudah Diambil') NOT NULL DEFAULT 'Proses',
    PRIMARY KEY (id_transaksi),
    CONSTRAINT fk_trx_pelanggan FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan),
    CONSTRAINT fk_trx_layanan   FOREIGN KEY (id_layanan)   REFERENCES layanan(id_layanan)
) ENGINE=InnoDB;

CREATE TABLE user (
    id_user  INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(32)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role     ENUM('admin','kasir') NOT NULL,
    PRIMARY KEY (id_user)
) ENGINE=InnoDB;

-- Akun awal: admin / admin123 dan kasir / kasir123 (hash dari password_hash())
INSERT INTO user (username, password, role) VALUES
('admin', '$2y$10$QG0DdoJdQwkKuL55kHBux.B9/rqPRqK71jDlI/n0C.O930mpHSaYO', 'admin'),
('kasir', '$2y$10$UCQbE2XS4qftTkqXbjVKZ.BZZ.f6g6RKIPMtqHSxhJ8gBYp0O6yMC', 'kasir');

INSERT INTO layanan (nama_layanan, harga_per_kg) VALUES
('Cuci Kering', 5000),
('Cuci Setrika', 7000),
('Express', 10000);

INSERT INTO pelanggan (nama, alamat, no_hp) VALUES
('Gita Aulia', 'Mulyosari', '081234567801'),
('Alessandro Almaz Filemon', 'Keputih', '081234567802'),
('Vida', 'Gebang', '081234567803');

INSERT INTO transaksi (id_pelanggan, id_layanan, tanggal_masuk, tanggal_selesai, berat, total_harga, status) VALUES
(1, 2, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 3.50, 24500, 'Proses'),
(2, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), CURDATE(), 4.00, 20000, 'Selesai'),
(3, 3, DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_SUB(CURDATE(), INTERVAL 2 DAY), 2.00, 20000, 'Sudah Diambil');