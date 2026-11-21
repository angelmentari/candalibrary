CREATE DATABASE IF NOT EXISTS db_perpustakaan_slims;
USE db_perpustakaan_slims;

CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'petugas') DEFAULT 'petugas',
    foto VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (nama_lengkap, username, password, role) VALUES
('Administrator Utama', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Alifia Pustakawan', 'petugas', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'petugas');

CREATE TABLE IF NOT EXISTS kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    kode_ddc VARCHAR(20) NOT NULL,
    nama_kategori VARCHAR(100) NOT NULL
);

INSERT INTO kategori (kode_ddc, nama_kategori) VALUES
('000', 'Karya Umum & Ilmu Komputer'),
('100', 'Filsafat dan Psikologi'),
('200', 'Agama'),
('300', 'Ilmu Sosial'),
('400', 'Bahasa'),
('500', 'Sains dan Matematika'),
('600', 'Teknologi'),
('700', 'Kesenian dan Rekreasi'),
('800', 'Sastra'),
('900', 'Sejarah dan Geografi');

CREATE TABLE IF NOT EXISTS rak (
    id_rak INT AUTO_INCREMENT PRIMARY KEY,
    nama_rak VARCHAR(100) NOT NULL
);

INSERT INTO rak (nama_rak) VALUES
('Ruang Koleksi Umum'),
('Ruang Koleksi Referensi'),
('Ruang Koleksi Anak'),
('Ruang Koleksi Sastra & Fiksi');

CREATE TABLE IF NOT EXISTS buku (
    id_buku INT AUTO_INCREMENT PRIMARY KEY,
    nomor_induk VARCHAR(30) NOT NULL UNIQUE,
    nomor_barcode VARCHAR(30) NOT NULL UNIQUE,
    isbn VARCHAR(30) DEFAULT '-',
    nomor_kelas VARCHAR(30) NOT NULL,
    no_panggil VARCHAR(50) NOT NULL,
    judul VARCHAR(200) NOT NULL,
    pengarang VARCHAR(150) NOT NULL,
    penerbit VARCHAR(100) NOT NULL,
    tempat_terbit VARCHAR(100) DEFAULT 'Jakarta',
    tahun_terbit YEAR NOT NULL,
    bahasa VARCHAR(50) DEFAULT 'Indonesia',
    jumlah_halaman VARCHAR(50) DEFAULT '200 hlm.',
    sinopsis TEXT,
    id_kategori INT,
    id_rak INT,
    stok INT NOT NULL DEFAULT 1,
    sampul VARCHAR(255) DEFAULT '',
    lampiran_pdf VARCHAR(255) DEFAULT '',
    tanggal_masuk TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori) ON DELETE SET NULL,
    FOREIGN KEY (id_rak) REFERENCES rak(id_rak) ON DELETE SET NULL
);

INSERT INTO buku (nomor_induk, nomor_barcode, isbn, nomor_kelas, no_panggil, judul, pengarang, penerbit, tempat_terbit, tahun_terbit, bahasa, jumlah_halaman, sinopsis, id_kategori, id_rak, stok) VALUES
('001/IND/2024', 'B00001', '978-602-220-196-0', '171.2', '171.2 MAN f', 'Filosofi Teras', 'Henry Manampiring', 'Buku Kompas', 'Jakarta', 2022, 'Indonesia', 'xxiv, 320 hlm.', 'Buku pengantar filsafat Stoa (Stoikisme) yang relevan untuk mengatasi emosi negatif dan membangun ketangguhan mental di era modern.', 2, 1, 5),
('002/IND/2024', 'B00002', '978-602-424-694-5', '899.221 3', '899.221 3 CHU l', 'Laut Bercerita', 'Leila S. Chudori', 'Kepustakaan Populer Gramedia', 'Jakarta', 2017, 'Indonesia', 'x, 379 hlm.', 'Novel fiksi sejarah yang mengisahkan persahabatan, cinta, keluarga, dan kehilangan para aktivis mahasiswa pada masa menjelang reformasi 1998.', 9, 4, 4),
('003/IND/2024', 'B00003', '978-602-03-3295-6', '899.221 3', '899.221 3 TER h', 'Hujan', 'Tere Liye', 'Gramedia Pustaka Utama', 'Jakarta', 2019, 'Indonesia', '320 hlm.', 'Kisah tentang persahabatan, cinta, perpisahan, melupakan, dan hujan di masa depan dengan latar teknologi canggih.', 9, 4, 3),
('004/IND/2024', 'B00004', '978-623-040-693-5', '005.133', '005.133 RAH p', 'Pemrograman Web PHP & MySQLi', 'Budi Raharjo', 'Informatika', 'Bandung', 2023, 'Indonesia', 'xiv, 412 hlm.', 'Panduan praktis membangun sistem informasi berbasis web menggunakan bahasa pemrograman PHP native, database MySQLi, dan framework CSS Bootstrap 5.', 1, 2, 6);

CREATE TABLE IF NOT EXISTS anggota (
    id_anggota INT AUTO_INCREMENT PRIMARY KEY,
    kode_anggota VARCHAR(20) NOT NULL UNIQUE,
    username_anggota VARCHAR(50) NOT NULL UNIQUE,
    password_anggota VARCHAR(255) NOT NULL,
    nama_anggota VARCHAR(100) NOT NULL,
    jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL,
    tempat_lahir VARCHAR(50) DEFAULT 'Semarang',
    tanggal_lahir DATE DEFAULT '2004-01-01',
    no_telp VARCHAR(20) NOT NULL,
    email VARCHAR(100) DEFAULT '-',
    alamat TEXT NOT NULL,
    foto VARCHAR(255) DEFAULT '',
    tgl_bergabung DATE DEFAULT (CURRENT_DATE),
    status_aktif ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif'
);

-- Akun demo anggota: username 'anggota', password 'anggota123'
INSERT INTO anggota (kode_anggota, username_anggota, password_anggota, nama_anggota, jenis_kelamin, tempat_lahir, tanggal_lahir, no_telp, email, alamat, tgl_bergabung) VALUES
('AG001', 'tika', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tika Nadia Rahma', 'Perempuan', 'Semarang', '2004-05-12', '081234567890', 'tika@undip.ac.id', 'Jl. Jangli Permai, Semarang', '2024-09-01'),
('AG002', 'hilya', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hilya Nafisa', 'Perempuan', 'Kendal', '2004-08-20', '085678901234', 'hilya@undip.ac.id', 'Jl. Baskoro Tembalang, Semarang', '2024-09-01'),
('AG003', 'karmila', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Karmila Putri', 'Perempuan', 'Pekalongan', '2004-03-15', '089618173609', 'karmila@undip.ac.id', 'Jl. Sirojudin Tembalang, Semarang', '2024-09-02');

CREATE TABLE IF NOT EXISTS pengaturan_denda (
    id_denda INT AUTO_INCREMENT PRIMARY KEY,
    nominal_denda INT NOT NULL DEFAULT 1000,
    lama_pinjam_default INT NOT NULL DEFAULT 7,
    nama_perpustakaan VARCHAR(150) DEFAULT 'CANDA Library Library - Sistem Otomasi Perpustakaan',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO pengaturan_denda (nominal_denda, lama_pinjam_default, nama_perpustakaan) VALUES
(1000, 7, 'CANDA Library Library');

CREATE TABLE IF NOT EXISTS sirkulasi (
    id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
    no_pinjam VARCHAR(20) NOT NULL UNIQUE,
    id_anggota INT NOT NULL,
    id_buku INT NOT NULL,
    tgl_pinjam DATE NOT NULL,
    tgl_jatuh_tempo DATE NOT NULL,
    tgl_kembali DATE NULL,
    status ENUM('Dipinjam', 'Dikembalikan') DEFAULT 'Dipinjam',
    denda INT DEFAULT 0,
    petugas_id INT NULL,
    FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE CASCADE,
    FOREIGN KEY (id_buku) REFERENCES buku(id_buku) ON DELETE CASCADE
);

INSERT INTO sirkulasi (no_pinjam, id_anggota, id_buku, tgl_pinjam, tgl_jatuh_tempo, tgl_kembali, status, denda) VALUES
('PJ0001', 1, 1, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(CURDATE(), INTERVAL 3 DAY), NULL, 'Dipinjam', 0),
('PJ0002', 2, 2, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 2 DAY), NULL, 'Dipinjam', 0),
('PJ0003', 3, 3, DATE_SUB(CURDATE(), INTERVAL 14 DAY), DATE_SUB(CURDATE(), INTERVAL 7 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Dikembalikan', 2000);
