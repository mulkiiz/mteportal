CREATE TABLE IF NOT EXISTS mte_st_meta (
    nama VARCHAR(80) NOT NULL PRIMARY KEY,
    nilai VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mte_st_personel (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    kode_sumber VARCHAR(100) NULL UNIQUE,
    nama VARCHAR(200) NOT NULL,
    nip VARCHAR(30) NULL UNIQUE,
    golongan VARCHAR(20) NOT NULL DEFAULT '',
    status_kepegawaian VARCHAR(30) NOT NULL DEFAULT '',
    kelompok ENUM('dosen','referensi') NOT NULL DEFAULT 'dosen',
    sumber VARCHAR(255) NOT NULL DEFAULT '',
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_mte_personel_nama (nama)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mte_st_kegiatan (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    kode_sumber VARCHAR(100) NULL UNIQUE,
    nama VARCHAR(255) NOT NULL,
    nomor_st VARCHAR(120) NOT NULL DEFAULT '',
    status ENUM('usulan','terbit','dibatalkan') NOT NULL DEFAULT 'usulan',
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    presisi_waktu ENUM('bulan','tanggal') NOT NULL DEFAULT 'bulan',
    tempat VARCHAR(255) NOT NULL DEFAULT '',
    catatan TEXT NOT NULL,
    versi INT UNSIGNED NOT NULL DEFAULT 1,
    dibuat_oleh INT NULL,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_mte_kegiatan_periode (tanggal_mulai, tanggal_selesai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mte_st_penugasan (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    kegiatan_id INT UNSIGNED NOT NULL,
    personel_id INT UNSIGNED NOT NULL,
    peran ENUM('ketua','sekretaris','anggota') NOT NULL,
    urutan INT UNSIGNED NOT NULL DEFAULT 0,
    divisi VARCHAR(200) NOT NULL DEFAULT '',
    tanggung_jawab VARCHAR(1000) NOT NULL DEFAULT '',
    UNIQUE KEY uq_mte_tim_personel (kegiatan_id, personel_id),
    INDEX idx_mte_penugasan_personel (personel_id, peran),
    CONSTRAINT fk_mte_penugasan_kegiatan FOREIGN KEY (kegiatan_id) REFERENCES mte_st_kegiatan(id) ON DELETE CASCADE,
    CONSTRAINT fk_mte_penugasan_personel FOREIGN KEY (personel_id) REFERENCES mte_st_personel(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mte_st_dokumen (
    kegiatan_id INT UNSIGNED NOT NULL PRIMARY KEY,
    nama_asli VARCHAR(255) NOT NULL,
    nama_simpan VARCHAR(80) NOT NULL,
    ukuran INT UNSIGNED NOT NULL,
    diunggah_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mte_dokumen_kegiatan FOREIGN KEY (kegiatan_id) REFERENCES mte_st_kegiatan(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
