-- MTE Portal: struktur lengkap 8 tabel + data konten publik (tanpa akun user & data Surat Tugas)
-- MariaDB dump 10.18  Distrib 10.4.16-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: mte_portal
-- ------------------------------------------------------
-- Server version	10.4.16-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `mte_portal`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `mte_portal` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `mte_portal`;

--
-- Table structure for table `mte_portal_content`
--

DROP TABLE IF EXISTS `mte_portal_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mte_portal_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `section` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(140) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kind` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `label` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `publish_date` date DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mte_portal_section_slug` (`section`,`slug`),
  KEY `idx_mte_portal_public` (`section`,`is_active`,`sort_order`,`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mte_portal_gallery`
--

DROP TABLE IF EXISTS `mte_portal_gallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mte_portal_gallery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(10) unsigned NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `caption` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_mte_portal_gallery_content` (`content_id`,`sort_order`,`id`),
  CONSTRAINT `fk_mte_portal_gallery_content` FOREIGN KEY (`content_id`) REFERENCES `mte_portal_content` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mte_portal_users`
--

DROP TABLE IF EXISTS `mte_portal_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mte_portal_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `display_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Administrator MTE',
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `failed_attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `password_changed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mte_st_dokumen`
--

DROP TABLE IF EXISTS `mte_st_dokumen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mte_st_dokumen` (
  `kegiatan_id` int(10) unsigned NOT NULL,
  `nama_asli` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_simpan` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ukuran` int(10) unsigned NOT NULL,
  `diunggah_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`kegiatan_id`),
  CONSTRAINT `fk_mte_dokumen_kegiatan` FOREIGN KEY (`kegiatan_id`) REFERENCES `mte_st_kegiatan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mte_st_kegiatan`
--

DROP TABLE IF EXISTS `mte_st_kegiatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mte_st_kegiatan` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kode_sumber` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_st` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `status` enum('usulan','terbit','dibatalkan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'usulan',
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `presisi_waktu` enum('bulan','tanggal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bulan',
  `tempat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `catatan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `versi` int(10) unsigned NOT NULL DEFAULT 1,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_sumber` (`kode_sumber`),
  KEY `idx_mte_kegiatan_periode` (`tanggal_mulai`,`tanggal_selesai`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mte_st_meta`
--

DROP TABLE IF EXISTS `mte_st_meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mte_st_meta` (
  `nama` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mte_st_penugasan`
--

DROP TABLE IF EXISTS `mte_st_penugasan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mte_st_penugasan` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kegiatan_id` int(10) unsigned NOT NULL,
  `personel_id` int(10) unsigned NOT NULL,
  `peran` enum('ketua','sekretaris','anggota') COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` int(10) unsigned NOT NULL DEFAULT 0,
  `divisi` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `tanggung_jawab` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mte_tim_personel` (`kegiatan_id`,`personel_id`),
  KEY `idx_mte_penugasan_personel` (`personel_id`,`peran`),
  CONSTRAINT `fk_mte_penugasan_kegiatan` FOREIGN KEY (`kegiatan_id`) REFERENCES `mte_st_kegiatan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mte_penugasan_personel` FOREIGN KEY (`personel_id`) REFERENCES `mte_st_personel` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mte_st_personel`
--

DROP TABLE IF EXISTS `mte_st_personel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mte_st_personel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kode_sumber` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nip` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `golongan` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `status_kepegawaian` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `kelompok` enum('dosen','referensi') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dosen',
  `sumber` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_sumber` (`kode_sumber`),
  UNIQUE KEY `nip` (`nip`),
  KEY `idx_mte_personel_nama` (`nama`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-25 13:27:16

-- Data konten publik (mte_portal_content & mte_portal_gallery)
-- MariaDB dump 10.18  Distrib 10.4.16-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: mte_portal
-- ------------------------------------------------------
-- Server version	10.4.16-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `mte_portal_content`
--

LOCK TABLES `mte_portal_content` WRITE;
/*!40000 ALTER TABLE `mte_portal_content` DISABLE KEYS */;
INSERT INTO `mte_portal_content` VALUES (1,'vmts','visi','','Visi','',NULL,'Visi Program Studi MTE','','Menjadi Program studi magister Teknik Elektro yang unggul di tingkat internasional dalam pengembangan teknologi dan keilmuan Teknik Elektro untuk mendukung pengembangan sumber daya lokal.','',1,1,'2026-11-21 09:53:56','2026-11-21 10:24:17'),(2,'vmts','misi','','Misi','',NULL,'Misi Program Studi MTE','','<p></p><ol><li>Melaksanakan pendidikan magister teknik elektro yang bermutu untuk menghasilkan lulusan yang berkarakter, berkualitas, dan profesional; </li><li>Mengembangkan inovasi rekayasa teknologi elektro yang menghasilkan karya ilmiah bertaraf nasional dan internasional untuk mendukung pengembangan potensi sumberdaya lokal; </li><li>Menerapkan rekayasa teknologi elektro melalui transfer teknologi untuk memberdayakan dan mengembangkan sumber daya lokal.</li></ol>','',2,1,'2026-11-21 09:53:56','2026-11-21 11:13:09'),(3,'vmts','tujuan','','Tujuan','',NULL,'Tujuan Program Studi','','<p></p><ol><li>Menghasilkan lulusan yang berkarakter, berkualitas, dan profesional; </li><li>Menghasilkan inovasi dan karya ilmiah yang bereputasi dalam pengembangan keilmuan \r\nEnergi Listrik, Teknologi Informasi dan Biomedis;</li><li>Menghasilkan lulusan yang mampu mengikuti perkembangan keilmuan di bidang Energi \r\nListrik, Teknologi Informasi dan Biomedis untuk mendukung pengembangan sumber daya \r\nlokal.</li></ol>','',3,1,'2026-11-21 09:53:56','2026-11-21 11:14:20'),(4,'vmts','program-dan-indikator','','Strategi','',NULL,'Program dan indikator','','<ol><li>Memperkuat pembelajaran berbasis studi kasus. Menggunakan persoalan lokal sebagai bahan pembelajaran serta menilai kemampuan mahasiswa dalam merancang, menguji, dan mempertanggungjawabkan solusi secara profesional dan etis.</li><li>Mengembangkan riset bersama mitra. Melibatkan masyarakat, pemerintah daerah, industri, atau perguruan tinggi dalam pemilihan masalah dan pengujian hasil riset, dengan memperhatikan kondisi setempat serta kebutuhan pengguna.</li><li>Memperluas pemanfaatan hasil penelitian. Mendampingi mitra melalui uji lapangan, pelatihan penggunaan dan pemeliharaan teknologi, serta evaluasi manfaat setelah pendampingan.</li><li>Membangun pengakuan akademik nasional dan internasional. Menghasilkan publikasi bereputasi bersama dosen dan mahasiswa serta memperluas pengujian hasil penelitian pada konteks yang berbeda.</li></ol>','',4,1,'2026-11-21 09:53:56','2026-11-24 02:51:04'),(5,'cpl','cpl-01','','','01',NULL,'Solusi Energi Berkelanjutan','','<p>Lulusan mampu menunjukkan pengetahuan mendalam tentang prinsip-prinsip teknik elektro dan penerapannya dalam mengembangkan solusi energi berkelanjutan.</p>','',1,1,'2026-11-21 09:53:56','2026-11-21 11:44:44'),(6,'cpl','cpl-02','','','02',NULL,'Merancang Solusi Inovatif','','<p>Lulusan mampu mengidentifikasi, menganalisis tantangan, dan menerapkan prinsip-prinsip teknik untuk merancang solusi inovatif.</p>','',2,1,'2026-11-21 09:53:56','2026-11-21 11:46:55'),(7,'cpl','cpl-03','','','03',NULL,'Manajemen Sumber Daya','','<p>Lulusan memiliki keterampilan untuk mengintegrasikan teknologi pintar, serta mempromosikan manajemen sumber daya yang efektif dan efisien.</p>','',3,1,'2026-11-21 09:53:56','2026-11-21 11:46:55'),(8,'cpl','cpl-04','','','04',NULL,'Melakukan Penelitian','','<p>Lulusan mampu menunjukkan kemampuan untuk melakukan penelitian yang membahas kesenjangan pemanfaatan teknologi elektro di masyarakat.</p>','',4,1,'2026-11-21 09:53:56','2026-11-21 11:46:55'),(9,'ppm','ppm-perekayasa-profesional','','','',NULL,'Perekayasa Profesional','','<p>Memiliki pengetahuan umum, keahlian umum dan teknis, \r\nketerampilan berpikir kritis, dan landasan etika untuk menangani kompleksitas tantangan \r\nteknik elektro modern dan penggunaannya dalam pengembangan sumber daya lokal. </p>','',1,1,'2026-11-21 09:53:56','2026-11-21 11:48:43'),(10,'ppm','ppm-problem-solver','','','',NULL,'Problem Solver','','<p>Mampu memimpin secara inovatif dan bertanggung jawab \r\ndalam mengembangkan riset atau pun rekayasa untuk menjadi solusi pengembangan \r\npedesaan dengan memanfaatkan pengetahuan yang dimilikinya.</p>','',2,1,'2026-11-21 09:53:56','2026-11-21 11:49:34'),(12,'berita','seminar-riset','agenda','','','2026-10-12','Seminar riset teknologi elektro','Program studi menyelenggarakan forum diskusi untuk mempertemukan gagasan riset dosen, mahasiswa, dan mitra.','<p>Dalam agenda ini, dosen, mahasiswa, dan mitra berbagi perkembangan penelitian, mendiskusikan tantangan di lapangan, serta menyusun peluang kolaborasi lanjutan. Narasi ini merupakan teks dummy untuk menunjukkan tampilan halaman berita.</p>','',1,1,'2026-11-21 09:53:56','2026-11-21 13:10:53'),(13,'berita','kuliah-tamu','agenda','','','2026-09-24','Kuliah tamu bidang energi','Praktisi dan akademisi berbagi wawasan mengenai perkembangan rekayasa sistem tenaga dan energi.','<p>Ketika listrik padam, biasanya kampung ikut redup. Tapi suatu malam di Sidakangen, Kalimanah, Purbalingga, ada kabar yang justru membuat orang‑orang tersenyum. “Apalagi saya dapat laporan dari warga, wilayah mereka mati lampu tapi tempat mereka masih terang,” kata Ir. Yogi Ramadhani, S.T., M.Eng, dosen Teknik Elektro UNSOED, mengenang momen yang paling ia ingat.</p><img src=\"mte-upload:konten/bb1b9a99ffe5a7408f8b2c734ba438db.png\" alt=\"yogir\" loading=\"lazy\" width=\"379\"><p>Terang itu bukan dari genset yang berisik, bukan pula dari kabel sambungan darurat. Ia datang dari sinar matahari yang disimpan siang hari, lalu dipakai untuk menjaga sebuah rumah kaca tetap hidup. Di sanalah Yogi dan tim mewujudkan gagasan kampus berdampak: teknologi tidak boleh berhenti jadi tumpukan laporan. “Suatu inovasi yang berdampak dan bagus itu adalah suatu inovasi yang tidak hanya berhenti di laporan penelitian, tapi inovasi yang juga dapat diterapkan di masyarakat,” ujarnya.</p>','',2,1,'2026-11-21 09:53:56','2026-11-22 03:27:45'),(14,'berita','lokakarya-proposal','pengumuman','','','2026-08-15','Lokakarya proposal penelitian','Mahasiswa mengikuti pendampingan penyusunan proposal, metodologi, dan rencana publikasi ilmiah.','<p>Peserta meninjau rumusan masalah, metodologi, dan rencana luaran penelitian bersama dosen pembimbing. Isi ini disiapkan sebagai tata letak berita.</p>','',3,1,'2026-11-21 09:53:56','2026-11-21 13:10:53'),(15,'berita','kerja-sama-riset','berita','','','2026-06-08','Kerja sama penelitian','Pertemuan penjajakan memetakan peluang kolaborasi penelitian bersama mitra.','<p>Pertemuan membahas peluang kolaborasi penelitian dan kebutuhan mitra. Seluruh naskah ini adalah konten dummy untuk demonstrasi portal.</p>','',4,1,'2026-11-21 09:53:56','2026-11-21 13:10:53'),(16,'berita','kemajuan-riset','berita','','','2026-04-18','Presentasi kemajuan riset','Mahasiswa memaparkan kemajuan penelitian dan memperoleh masukan dari komunitas akademik.','<p>Mahasiswa membagikan progres, temuan sementara, dan rencana lanjutan penelitian. Narasi ini hanya konten halaman detail.</p>','',5,1,'2026-11-21 09:53:56','2026-11-21 13:10:53'),(17,'berita','diskusi-kurikulum','agenda','','','2026-02-10','Diskusi kurikulum prodi','Forum pemangku kepentingan membahas relevansi pembelajaran dengan perkembangan keilmuan.','<p>Pemangku kepentingan berdiskusi tentang perkembangan keilmuan dan keterkaitannya dengan proses pembelajaran. Informasi ini memuat agenda diskusi kurikulum prodi.</p>','',6,1,'2026-11-21 09:53:56','2026-11-21 13:10:53'),(18,'dokumen','pedoman-akademik','PDF','','',NULL,'Pedoman Akademik MTE','informasi mengenai ketentuan akademik dan tahapan studi mahasiswa.','<p>Dokumen ini dapat memuat ringkasan aturan akademik, tahapan perkuliahan, evaluasi studi, serta layanan akademik yang tersedia bagi mahasiswa. Perbarui uraian ini dengan keterangan resmi program studi.</p>','contoh.pdf',1,1,'2026-11-21 09:53:56','2026-11-21 13:10:53'),(19,'dokumen','panduan-penyusunan-tesis','PDF','','',NULL,'Panduan Penyusunan Tesis','panduan struktur tesis, format penulisan, sitasi, dan penyerahan naskah.','<p>Panduan ini dapat menjelaskan susunan naskah tesis, format penulisan, tata cara sitasi, pemeriksaan kemiripan, dan proses penyerahan naskah akhir. Silakan sesuaikan narasi dan berkas dengan pedoman yang berlaku di program studi.</p>','contoh.pdf',2,1,'2026-11-21 09:53:56','2026-11-21 13:10:53'),(20,'dokumen','seminar-proposal','PDF','','',NULL,'Panduan Seminar Proposal','alur pendaftaran, persyaratan, dan pelaksanaan seminar proposal penelitian.','<p>Informasi detail dapat memuat persyaratan pendaftaran, dokumen yang perlu disiapkan, proses penjadwalan, serta tata tertib seminar proposal. Sesuaikan informasi dengan prosedur seminar proposal yang berlaku.</p>','contoh.pdf',3,1,'2026-11-21 09:53:56','2026-11-21 13:10:53'),(21,'dokumen','seminar-hasil','PDF','','',NULL,'Panduan Seminar Hasil','persiapan dan ketentuan seminar hasil penelitian mahasiswa.','<p>Halaman ini dapat merangkum syarat seminar hasil, kelengkapan administrasi, susunan presentasi, dan mekanisme pencatatan masukan penguji. Ganti uraian dan berkas ini dengan ketentuan resmi program studi.</p>','contoh.pdf',4,1,'2026-11-21 09:53:56','2026-11-21 13:10:53'),(22,'dokumen','ujian-tesis','PDF','','',NULL,'Panduan Ujian Tesis','persyaratan administrasi dan tahapan pelaksanaan ujian tesis.','<p>Detail dokumen dapat menerangkan persyaratan sebelum ujian, pengajuan jadwal, komposisi tim penguji, serta tindak lanjut setelah ujian. Lengkapi halaman ini dengan uraian resmi tahapan ujian tesis.</p>','contoh.pdf',5,1,'2026-11-21 09:53:56','2026-11-21 13:10:53'),(23,'dokumen','kalender-akademik','PDF','','',NULL,'Kalender Akademik Program Studi','ringkasan jadwal penting kegiatan akademik dalam satu periode.','<p>Kalender akademik dapat memuat periode registrasi, perkuliahan, pengajuan penelitian, seminar, dan kegiatan evaluasi. Perbarui isi dan berkas sesuai kalender akademik yang telah ditetapkan.</p>','contoh.pdf',6,1,'2026-11-21 09:53:56','2026-11-21 13:10:53'),(24,'peminatan','energi-listrik-cerdas','','','',NULL,'Energi Listrik Cerdas (ELC)','','<p>Pendalaman teknologi energi, sistem tenaga, jaringan cerdas, dan integrasi energi berkelanjutan. Prodi menyediakan mata kuliah pilihan berikut ini untuk memperkuat peminatan tersebut.</p><p><strong>Daftar Mata Kuliah Pilihan</strong></p><div class=\"rich-table-scroll\"><table><thead><tr><th>Kode</th><th>Mata Kuliah</th><th>SKS</th></tr></thead><tbody><tr><td>MTE25411</td><td>Teknologi Konversi Energi Terbarukan</td><td>3</td></tr><tr><td>MTE25412</td><td>Jaringan Listrik Cerdas</td><td>3</td></tr><tr><td>MTE25413</td><td>Smart Farming</td><td>3</td></tr><tr><td>MTE25414</td><td>Manajemen Energi Listrik Modern</td><td>3</td></tr><tr><td>MTE25415</td><td>Teknologi Kendaraan Listrik</td><td>3</td></tr><tr><td>MTE25416</td><td>Operasi Optimal Sistem Tenaga</td><td>3</td></tr><tr><td>MTE25417</td><td>Instrumentasi dan Kontrol Tenaga Listrik</td><td>3</td></tr><tr><td>MTE25418</td><td>Kualitas dan Keandalan Tenaga Listrik</td><td>3</td></tr><tr><td>MTE25419</td><td>Sistem Listrik Industri</td><td>3</td></tr></tbody></table></div>','',1,1,'2026-11-21 09:53:56','2026-11-21 13:07:08'),(25,'peminatan','teknologi-informasi-biomedis','','','',NULL,'Teknologi Informasi dan Biomedis (TIB)','','<p>Pendalaman rekayasa teknologi informasi, sistem komputasi, instrumentasi, dan aplikasi biomedis. Prodi menyediakan mata kuliah pilihan berikut ini untuk memperkuat peminatan tersebut.</p><p><strong>Daftar Mata Kuliah Pilihan</strong></p><div class=\"rich-table-scroll\"><table><thead><tr><th>Kode</th><th>Mata Kuliah</th><th>SKS</th></tr></thead><tbody><tr><td>MTE25421</td><td>Pengolahan Citra Medis</td><td>3</td></tr><tr><td>MTE25422</td><td>Instrumentasi Biomedis</td><td>3</td></tr><tr><td>MTE25423</td><td>Optimasi Komputasi</td><td>3</td></tr><tr><td>MTE25424</td><td>Pengembangan Perangkat Lunak Berbasis DevOps</td><td>3</td></tr><tr><td>MTE25425</td><td>Teknologi Cloud Computing dan Big Data</td><td>3</td></tr><tr><td>MTE25426</td><td>Data Mining dan Machine Learning</td><td>3</td></tr><tr><td>MTE25427</td><td>Aplikasi Berbasis Blockchain</td><td>3</td></tr><tr><td>MTE25428</td><td>Metode Verifikasi Formal</td><td>3</td></tr><tr><td>MTE25429</td><td>Keamanan Jaringan dan Aplikasi</td><td>3</td></tr></tbody></table></div>','',2,1,'2026-11-21 09:53:56','2026-11-21 13:07:18'),(26,'cpl','cpl-05','','','05',NULL,'Praktik Rekayasa','','<p>Lulusan mampu memahami dan menerapkan prinsip-prinsip etika dalam praktik rekayasa yang mendukung keadilan sosial dan keberlanjutan lingkungan.</p>','',5,1,'2026-11-21 11:33:24','2026-11-21 11:46:55'),(27,'cpl','cpl-06','','','06',NULL,'Komunikasi dan Kolaborasi','','<p>Lulusan mampu mengkomunikasikan informasi teknis secara efektif, untuk mendorong keterlibatan dan kolaborasi dalam inisiatif pembangunan.</p>','',6,1,'2026-11-21 11:33:24','2026-11-21 11:46:55'),(28,'cpl','cpl-07','','','07',NULL,'Pembelajaran Seumur Hidup','','<p>Lulusan mampu menyadari pentingnya pembelajaran seumur hidup dan pengembangan profesional, khususnya dalam beradaptasi dengan teknologi baru.</p>','',7,1,'2026-11-21 11:33:24','2026-11-21 11:46:55'),(29,'cpl','cpl-08','','','08',NULL,'Merancang Sistem Modern','','<p>Lulusan mampu menunjukkan kemahiran dalam menggunakan metode dan teknik modern untuk menganalisis dan merancang sistem pada masalah kompleks inter-multi disiplin.</p>','',8,1,'2026-11-21 11:33:24','2026-11-21 11:46:55'),(30,'kurikulum','mte25111','MKU','1','MTE25111',NULL,'Komputasi Numeris','3','','',1,1,'2026-11-21 12:35:35','2026-11-21 12:35:35'),(31,'kurikulum','mte25112','MKU','1','MTE25112',NULL,'Kecerdasan Buatan','3','','',2,1,'2026-11-21 12:35:35','2026-11-21 12:35:35'),(32,'kurikulum','mte25113','MKU','1','MTE25113',NULL,'Internet of Things','3','','',3,1,'2026-11-21 12:35:35','2026-11-21 12:35:35'),(33,'kurikulum','mte25114','MKU','1','MTE25114',NULL,'Etika Profesi','2','','',4,1,'2026-11-21 12:35:35','2026-11-21 12:35:35'),(34,'kurikulum','mte25115','MKU','1','MTE25115',NULL,'Energi Berkelanjutan','3','','',5,1,'2026-11-21 12:35:35','2026-11-21 12:35:35'),(35,'kurikulum','mte25121','MKU','2','MTE25121',NULL,'Metodologi Penelitian','3','','',6,1,'2026-11-21 12:44:19','2026-11-21 12:44:19'),(36,'kurikulum','mte2512xx-pilihan-1','MKP','2','MTE2512xx',NULL,'Matakuliah Pilihan 1','3','','',7,1,'2026-11-21 12:44:19','2026-11-21 12:44:19'),(37,'kurikulum','mte2512xx-pilihan-2','MKP','2','MTE2512xx',NULL,'Matakuliah Pilihan 2','3','','',8,1,'2026-11-21 12:44:19','2026-11-21 12:44:19'),(38,'kurikulum','mte2512xx-pilihan-3','MKP','2','MTE2512xx',NULL,'Matakuliah Pilihan 3','3','','',9,1,'2026-11-21 12:44:19','2026-11-21 12:44:19'),(39,'kurikulum','mte25211','MKU','3','MTE25211',NULL,'Seminar Proposal','2','','',10,1,'2026-11-21 12:44:19','2026-11-21 12:44:19'),(40,'kurikulum','mte25212','MKU','3','MTE25212',NULL,'Tesis','5','','',11,1,'2026-11-21 12:44:19','2026-11-21 12:44:19'),(41,'kurikulum','mte25221','MKU','4','MTE25221',NULL,'Seminar Hasil','2','','',12,1,'2026-11-21 12:44:19','2026-11-21 12:44:19'),(42,'kurikulum','mte25222','MKU','4','MTE25222',NULL,'Ujian Akhir','1','','',13,1,'2026-11-21 12:44:19','2026-11-21 12:44:19'),(43,'dosen','arief-sudarmaji','9','8','267',NULL,'Arief Sudarmaji, Ph.D','Control; Electronics; Instrumentation; Agricultural Engineering','','https://scholar.google.com/citations?user=ooEkh_4AAAAJ&hl=en',1,1,'2026-11-25 06:19:55','2026-11-25 06:27:15'),(44,'dosen','ari-fadli','15','23','812',NULL,'Dr. Ir. Ari Fadli, M.Eng.','Machine Learning; Deep Learning; Artificial Intelligence; Information System','','https://scholar.google.com/citations?user=OUDUTj0AAAAJ&hl=id',2,1,'2026-11-25 06:19:55','2026-11-25 06:27:15'),(45,'dosen','farida-asriani','5','4','96',NULL,'Dr. Ir. Farida Asriani, M.T.','Image Processing; Computer Vision; Artificial Intelligece & Machine Learning; Intelligent System','','https://scholar.google.com/citations?user=vSLVtycAAAAJ&hl=id',3,1,'2026-11-25 06:19:55','2026-11-25 06:27:15'),(46,'dosen','hari-siswantoro','4','2','96',NULL,'Ir. Hari Siswantoro, Ph.D','automated analysis; security; access control; legal compliance','','https://scholar.google.com/citations?user=LZHFkBAAAAAJ&hl=en',4,1,'2026-11-25 06:19:55','2026-11-25 06:27:15'),(47,'dosen','jamrud-aminuddin','11','12','364',NULL,'Prof. Dr. Ir. Jamrud Aminuddin','Theoretical and Computational Physics; Atmospheric Remote Sensing','','https://scholar.google.com/citations?user=liJ_0egAAAAJ&hl=en',5,1,'2026-11-25 06:19:55','2026-11-25 06:27:15'),(48,'dosen','mulki-indana-zulfa','11','12','370',NULL,'Dr. Ir. Mulki Indana Zulfa, M.T.','information technology; database; data mining; web programming','','https://scholar.google.com/citations?user=TqYM_x4AAAAJ&hl=en',6,1,'2026-11-25 06:19:55','2026-11-25 06:27:15'),(49,'dosen','retno-supriyanti','13','18','477',NULL,'Prof. Dr. Eng. Ir. Retno Supriyanti','image processing; artifcial intelligence; telemedicine; sensor and actuator; Renewable Energy','','https://scholar.google.com/citations?user=vUJQJu4AAAAJ&hl=id',7,1,'2026-11-25 06:19:55','2026-11-25 06:27:15'),(50,'dosen','suroso','10','12','411',NULL,'Prof. Dr. Eng. Ir. Suroso','Power Electronics; Renewable Energy','','https://scholar.google.com/citations?user=nXhlkmUAAAAJ&hl=id',8,1,'2026-11-25 06:19:55','2026-11-25 06:27:15'),(51,'dosen','acep-taryana','7','5','157',NULL,'Dr. Ir. Acep Taryana, M.T.','Software Engineering; Formal Method; Verification System','','https://scholar.google.com/citations?user=EjvohFkAAAAJ&hl=id',9,1,'2026-11-25 06:19:55','2026-11-25 06:27:15');
/*!40000 ALTER TABLE `mte_portal_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `mte_portal_gallery`
--

LOCK TABLES `mte_portal_gallery` WRITE;
/*!40000 ALTER TABLE `mte_portal_gallery` DISABLE KEYS */;
INSERT INTO `mte_portal_gallery` VALUES (1,12,'asset:galeri-mte-seminar-1.svg','Ilustrasi diskusi riset teknik elektro','Diskusi tim riset',1,'2026-11-21 10:01:01'),(2,12,'asset:galeri-mte-seminar-2.svg','Ilustrasi presentasi teknologi energi','Presentasi teknologi energi',2,'2026-11-21 10:01:01'),(3,12,'asset:galeri-mte-seminar-3.svg','Ilustrasi kolaborasi di laboratorium','Kolaborasi laboratorium',3,'2026-11-21 10:01:01');
/*!40000 ALTER TABLE `mte_portal_gallery` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-25 13:27:16
