<?php
declare(strict_types=1);

$situsJurusan = 'https://elektro.ft.unsoed.ac.id/';
$situsProgramJurusan = 'https://elektro.ft.unsoed.ac.id/#/programs';
$situsFakultas = 'https://ft.unsoed.ac.id/';
$situsUnsoed = 'https://unsoed.ac.id/';
$jalurScript = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = rtrim(str_replace('\\', '/', dirname($jalurScript)), '/');
if ($basePath === '.' || $basePath === '/') {
    $basePath = '';
}
$portalPath = $basePath . '/';
$assetPath = $basePath . '/simulasi_assets';
$requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$routePath = is_string($requestPath) ? rtrim($requestPath, '/') : '';
$adminTools = ['kelola-st' => 'kelola_st.php', 'simulasi-akreditasi' => 'simulasi.php'];
if ($routePath === $basePath . '/masuk') {
    header('X-Robots-Tag: noindex, nofollow');
    define('MTE_PORTAL_ROUTED', true);
    require_once __DIR__ . '/includes/portal_lib.php';
    require __DIR__ . '/admin/masuk_page.php';
    exit;
}
if ($routePath === $basePath . '/admin') {
    header('X-Robots-Tag: noindex, nofollow');
    define('MTE_PORTAL_ROUTED', true);
    require_once __DIR__ . '/includes/portal_lib.php';
    require __DIR__ . '/admin/admin_page.php';
    exit;
}
foreach ($adminTools as $tool => $legacyFile) {
    $toolPath = $basePath . '/' . $tool;
    if ($requestPath === $toolPath || $routePath === $toolPath . '/index.php' || $routePath === $basePath . '/' . $legacyFile) {
        $query = $_SERVER['QUERY_STRING'] ?? '';
        header('Location: ' . $toolPath . '/' . ($query !== '' ? '?' . $query : ''), true, 307);
        exit;
    }
    if ($routePath === $toolPath) {
        header('X-Robots-Tag: noindex, nofollow');
        define('MTE_PORTAL_ROUTED', true);
        require_once __DIR__ . '/includes/portal_lib.php';
        $adminUser = mte_portal_require_admin($tool);
        require __DIR__ . '/' . $tool . '/index.php';
        exit;
    }
}

$bagian = [
    'vmts' => ['VMTS', 'Visi, Misi, Tujuan, dan Strategi', 'Visi, misi, tujuan, dan strategi prodi'],
    'cpl' => ['CPL Prodi', 'Capaian Pembelajaran Lulusan', 'Capaian pembelajaran lulusan'],
    'ppm' => ['PPM Prodi', 'Profil Profesional Mandiri', 'Profil profesional mandiri lulusan'],
    'kurikulum' => ['Kurikulum', 'Kurikulum Program Studi', 'Daftar mata kuliah dan beban SKS per semester'],
    'peminatan' => ['Peminatan', 'Peminatan Program Studi', 'Energi Listrik Cerdas (ELC) dan Teknologi Informasi dan Biomedis (TIB)'],
    'dosen' => ['Dosen', 'Dosen Program Studi', 'Daftar dosen, bidang riset, dan tautan Google Scholar'],
    'berita' => ['Berita', 'Berita Program Studi', 'Berita, pengumuman, dan agenda program studi'],
    'dokumen' => ['Dokumen', 'Dokumen Program Studi', 'Pedoman dan berkas akademik'],
];
$pilihanBagian = $_GET['bagian'] ?? 'vmts';
$aktif = is_string($pilihanBagian) && isset($bagian[$pilihanBagian]) ? $pilihanBagian : 'vmts';
$judulHalaman = isset($_GET['bagian']) && is_string($pilihanBagian) && isset($bagian[$pilihanBagian])
    ? $bagian[$aktif][1] . ' · MTE UNSOED'
    : 'Portal Akademik S2 Teknik Elektro Unsoed';

$vmts = [
    ['label' => 'VISI', 'judul' => 'Arah pengembangan keilmuan', 'teks' => 'CONTOH NARASI: Menjadi program magister teknik elektro yang unggul dalam pengembangan rekayasa dan teknologi untuk mendukung potensi wilayah.'],
    ['label' => 'MISI', 'judul' => 'Pendidikan, riset, dan kemitraan', 'teks' => 'CONTOH NARASI: Menyelenggarakan pendidikan magister, penelitian yang menjawab kebutuhan masyarakat, dan kerja sama dengan pemangku kepentingan.'],
    ['label' => 'TUJUAN', 'judul' => 'Dampak bagi lulusan', 'teks' => 'CONTOH NARASI: Menghasilkan lulusan yang mengembangkan keahlian, karya, dan kepemimpinan di bidang teknik elektro.'],
    ['label' => 'STRATEGI', 'judul' => 'Program dan indikator', 'teks' => 'CONTOH NARASI: Meninjau kurikulum, memperluas kemitraan riset, dan mengevaluasi ketercapaian program secara berkala.'],
];

$cpl = [
    ['kode' => '01', 'judul' => 'Solusi Energi Berkelanjutan', 'teks' => 'Lulusan mampu menunjukkan pengetahuan mendalam tentang prinsip-prinsip teknik elektro dan penerapannya dalam mengembangkan solusi energi berkelanjutan.'],
    ['kode' => '02', 'judul' => 'Merancang Solusi Inovatif', 'teks' => 'Lulusan mampu mengidentifikasi, menganalisis tantangan, dan menerapkan prinsip-prinsip teknik untuk merancang solusi inovatif.'],
    ['kode' => '03', 'judul' => 'Manajemen Sumber Daya', 'teks' => 'Lulusan memiliki keterampilan untuk mengintegrasikan teknologi pintar, serta mempromosikan manajemen sumber daya yang efektif dan efisien.'],
    ['kode' => '04', 'judul' => 'Melakukan Penelitian', 'teks' => 'Lulusan mampu menunjukkan kemampuan untuk melakukan penelitian yang membahas kesenjangan pemanfaatan teknologi elektro di masyarakat.'],
    ['kode' => '05', 'judul' => 'Praktik Rekayasa', 'teks' => 'Lulusan mampu memahami dan menerapkan prinsip-prinsip etika dalam praktik rekayasa yang mendukung keadilan sosial dan keberlanjutan lingkungan.'],
    ['kode' => '06', 'judul' => 'Komunikasi dan Kolaborasi', 'teks' => 'Lulusan mampu mengkomunikasikan informasi teknis secara efektif, untuk mendorong keterlibatan dan kolaborasi dalam inisiatif pembangunan.'],
    ['kode' => '07', 'judul' => 'Pembelajaran Seumur Hidup', 'teks' => 'Lulusan mampu menyadari pentingnya pembelajaran seumur hidup dan pengembangan profesional, khususnya dalam beradaptasi dengan teknologi baru.'],
    ['kode' => '08', 'judul' => 'Merancang Sistem Modern', 'teks' => 'Lulusan mampu menunjukkan kemahiran dalam menggunakan metode dan teknik modern untuk menganalisis dan merancang sistem pada masalah kompleks inter-multi disiplin.'],
];

$ppm = [
    ['kode' => '01', 'judul' => 'Perekayasa profesional', 'teks' => 'CONTOH PPM: Dalam beberapa tahun setelah lulus, mampu mengembangkan solusi rekayasa elektro sesuai kebutuhan bidang kerjanya.'],
    ['kode' => '02', 'judul' => 'Peneliti dan pengembang', 'teks' => 'CONTOH PPM: Mampu memimpin atau berkontribusi pada penelitian dan pengembangan teknologi yang bermanfaat.'],
    ['kode' => '03', 'judul' => 'Pemimpin dan pembelajar', 'teks' => 'CONTOH PPM: Mampu memimpin kolaborasi profesional dan mengembangkan kompetensi secara berkelanjutan.'],
];

$kurikulum = [
    ['semester' => 1, 'kode' => 'MTE25111', 'kelompok' => 'MKU', 'judul' => 'Komputasi Numeris', 'sks' => 3],
    ['semester' => 1, 'kode' => 'MTE25112', 'kelompok' => 'MKU', 'judul' => 'Kecerdasan Buatan', 'sks' => 3],
    ['semester' => 1, 'kode' => 'MTE25113', 'kelompok' => 'MKU', 'judul' => 'Internet of Things', 'sks' => 3],
    ['semester' => 1, 'kode' => 'MTE25114', 'kelompok' => 'MKU', 'judul' => 'Etika Profesi', 'sks' => 2],
    ['semester' => 1, 'kode' => 'MTE25115', 'kelompok' => 'MKU', 'judul' => 'Energi Berkelanjutan', 'sks' => 3],
];

$berita = [
    ['id' => 'seminar-riset', 'jenis' => 'agenda', 'tanggal' => '2026-10-12', 'judul' => 'Contoh: Seminar riset teknologi elektro', 'ringkasan' => 'CONTOH BERITA: Program studi menyelenggarakan forum diskusi untuk mempertemukan gagasan riset dosen, mahasiswa, dan mitra.', 'isi' => 'Dalam contoh agenda ini, dosen, mahasiswa, dan mitra berbagi perkembangan penelitian, mendiskusikan tantangan di lapangan, serta menyusun peluang kolaborasi lanjutan. Narasi ini merupakan teks dummy untuk menunjukkan tampilan halaman berita.'],
    ['id' => 'kuliah-tamu', 'jenis' => 'agenda', 'tanggal' => '2026-09-24', 'judul' => 'Contoh: Kuliah tamu bidang energi', 'ringkasan' => 'CONTOH BERITA: Praktisi dan akademisi berbagi wawasan mengenai perkembangan rekayasa sistem tenaga dan energi.', 'isi' => 'Kuliah tamu ini memberi ruang bagi mahasiswa untuk mengenal persoalan profesi, pendekatan rekayasa mutakhir, dan topik penelitian bidang energi. Narasi ini merupakan teks dummy untuk halaman berita prodi.'],
    ['id' => 'lokakarya-proposal', 'jenis' => 'pengumuman', 'tanggal' => '2026-08-15', 'judul' => 'Contoh: Lokakarya proposal penelitian', 'ringkasan' => 'CONTOH BERITA: Mahasiswa mengikuti pendampingan penyusunan proposal, metodologi, dan rencana publikasi ilmiah.', 'isi' => 'Peserta meninjau rumusan masalah, metodologi, dan rencana luaran penelitian bersama dosen pembimbing. Isi ini disiapkan sebagai contoh tata letak berita.'],
    ['id' => 'kerja-sama-riset', 'jenis' => 'berita', 'tanggal' => '2026-06-08', 'judul' => 'Contoh: Kerja sama penelitian', 'ringkasan' => 'CONTOH BERITA: Pertemuan penjajakan memetakan peluang kolaborasi penelitian bersama mitra.', 'isi' => 'Pertemuan membahas peluang kolaborasi penelitian dan kebutuhan mitra. Seluruh naskah ini adalah konten dummy untuk demonstrasi portal.'],
    ['id' => 'kemajuan-riset', 'jenis' => 'berita', 'tanggal' => '2026-04-18', 'judul' => 'Contoh: Presentasi kemajuan riset', 'ringkasan' => 'CONTOH BERITA: Mahasiswa memaparkan kemajuan penelitian dan memperoleh masukan dari komunitas akademik.', 'isi' => 'Mahasiswa membagikan progres, temuan sementara, dan rencana lanjutan penelitian. Narasi ini hanya contoh konten halaman detail.'],
    ['id' => 'diskusi-kurikulum', 'jenis' => 'agenda', 'tanggal' => '2026-02-10', 'judul' => 'Contoh: Diskusi kurikulum prodi', 'ringkasan' => 'CONTOH BERITA: Forum pemangku kepentingan membahas relevansi pembelajaran dengan perkembangan keilmuan.', 'isi' => 'Pemangku kepentingan berdiskusi tentang perkembangan keilmuan dan keterkaitannya dengan proses pembelajaran. Narasi ini merupakan contoh untuk pratinjau portal.'],
];

$tipeBerita = [
    'berita' => 'Berita',
    'pengumuman' => 'Pengumuman',
    'agenda' => 'Agenda',
];
$galeriSeminar = [
    ['file' => 'galeri-mte-seminar-1.svg', 'alt' => 'Ilustrasi diskusi riset teknik elektro', 'caption' => 'Diskusi tim riset'],
    ['file' => 'galeri-mte-seminar-2.svg', 'alt' => 'Ilustrasi presentasi teknologi energi', 'caption' => 'Presentasi teknologi energi'],
    ['file' => 'galeri-mte-seminar-3.svg', 'alt' => 'Ilustrasi kolaborasi di laboratorium', 'caption' => 'Kolaborasi laboratorium'],
];

$dokumen = [
    ['id' => 'pedoman-akademik', 'jenis' => 'PDF', 'judul' => 'Contoh: Pedoman Akademik MTE', 'keterangan' => 'Contoh informasi mengenai ketentuan akademik dan tahapan studi mahasiswa.', 'narasi' => 'Dokumen ini dapat memuat ringkasan aturan akademik, tahapan perkuliahan, evaluasi studi, serta layanan akademik yang tersedia bagi mahasiswa. Narasi ini masih berupa contoh dan dapat diganti dengan keterangan resmi.', 'file' => 'contoh.pdf'],
    ['id' => 'panduan-penyusunan-tesis', 'jenis' => 'PDF', 'judul' => 'Contoh: Panduan Penyusunan Tesis', 'keterangan' => 'Contoh panduan struktur tesis, format penulisan, sitasi, dan penyerahan naskah.', 'narasi' => 'Panduan ini dapat menjelaskan susunan naskah tesis, format penulisan, tata cara sitasi, pemeriksaan kemiripan, dan proses penyerahan naskah akhir. Silakan sesuaikan narasi dan berkas dengan pedoman yang berlaku di program studi.', 'file' => 'contoh.pdf'],
    ['id' => 'seminar-proposal', 'jenis' => 'PDF', 'judul' => 'Contoh: Panduan Seminar Proposal', 'keterangan' => 'Contoh alur pendaftaran, persyaratan, dan pelaksanaan seminar proposal penelitian.', 'narasi' => 'Informasi detail dapat memuat persyaratan pendaftaran, dokumen yang perlu disiapkan, proses penjadwalan, serta tata tertib seminar proposal. Konten ini merupakan contoh awal untuk pengelolaan dokumen prodi.', 'file' => 'contoh.pdf'],
    ['id' => 'seminar-hasil', 'jenis' => 'PDF', 'judul' => 'Contoh: Panduan Seminar Hasil', 'keterangan' => 'Contoh persiapan dan ketentuan seminar hasil penelitian mahasiswa.', 'narasi' => 'Halaman ini dapat merangkum syarat seminar hasil, kelengkapan administrasi, susunan presentasi, dan mekanisme pencatatan masukan penguji. Ganti uraian dan dokumen contoh ini dengan ketentuan resmi program studi.', 'file' => 'contoh.pdf'],
    ['id' => 'ujian-tesis', 'jenis' => 'PDF', 'judul' => 'Contoh: Panduan Ujian Tesis', 'keterangan' => 'Contoh persyaratan administrasi dan tahapan pelaksanaan ujian tesis.', 'narasi' => 'Detail dokumen dapat menerangkan persyaratan sebelum ujian, pengajuan jadwal, komposisi tim penguji, serta tindak lanjut setelah ujian. Narasi ini disiapkan sebagai dummy untuk contoh halaman detail.', 'file' => 'contoh.pdf'],
    ['id' => 'kalender-akademik', 'jenis' => 'PDF', 'judul' => 'Contoh: Kalender Akademik Program Studi', 'keterangan' => 'Contoh ringkasan jadwal penting kegiatan akademik dalam satu periode.', 'narasi' => 'Kalender akademik dapat memuat periode registrasi, perkuliahan, pengajuan penelitian, seminar, dan kegiatan evaluasi. Perbarui isi dan berkas sesuai kalender akademik yang telah ditetapkan.', 'file' => 'contoh.pdf'],
];

$peminatan = [
    ['judul' => 'Energi Listrik Cerdas (ELC)', 'keterangan' => 'CONTOH NARASI: Pendalaman teknologi energi, sistem tenaga, jaringan cerdas, dan integrasi energi berkelanjutan.'],
    ['judul' => 'Teknologi Informasi dan Biomedis (TIB)', 'keterangan' => 'CONTOH NARASI: Pendalaman rekayasa teknologi informasi, sistem komputasi, instrumentasi, dan aplikasi biomedis.'],
];

$dosen = [
    ['nama' => 'Arief Sudarmaji, Ph.D', 'scholar' => 'https://scholar.google.com/citations?user=ooEkh_4AAAAJ&hl=en', 'bidang' => 'Control; Electronics; Instrumentation; Agricultural Engineering', 'citations' => 267, 'h_index' => 9, 'i10_index' => 8],
    ['nama' => 'Dr. Ir. Ari Fadli, M.Eng.', 'scholar' => 'https://scholar.google.com/citations?user=OUDUTj0AAAAJ&hl=id', 'bidang' => 'Machine Learning; Deep Learning; Artificial Intelligence; Information System', 'citations' => 812, 'h_index' => 15, 'i10_index' => 23],
    ['nama' => 'Dr. Ir. Farida Asriani, M.T.', 'scholar' => 'https://scholar.google.com/citations?user=vSLVtycAAAAJ&hl=id', 'bidang' => 'Image Processing; Computer Vision; Artificial Intelligece & Machine Learning; Intelligent System', 'citations' => 96, 'h_index' => 5, 'i10_index' => 4],
    ['nama' => 'Ir. Hari Siswantoro, Ph.D', 'scholar' => 'https://scholar.google.com/citations?user=LZHFkBAAAAAJ&hl=en', 'bidang' => 'automated analysis; security; access control; legal compliance', 'citations' => 96, 'h_index' => 4, 'i10_index' => 2],
    ['nama' => 'Prof. Dr. Ir. Jamrud Aminuddin', 'scholar' => 'https://scholar.google.com/citations?user=liJ_0egAAAAJ&hl=en', 'bidang' => 'Theoretical and Computational Physics; Atmospheric Remote Sensing', 'citations' => 364, 'h_index' => 11, 'i10_index' => 12],
    ['nama' => 'Dr. Ir. Mulki Indana Zulfa, M.T.', 'scholar' => 'https://scholar.google.com/citations?user=TqYM_x4AAAAJ&hl=en', 'bidang' => 'information technology; database; data mining; web programming', 'citations' => 370, 'h_index' => 11, 'i10_index' => 12],
    ['nama' => 'Prof. Dr. Eng. Ir. Retno Supriyanti', 'scholar' => 'https://scholar.google.com/citations?user=vUJQJu4AAAAJ&hl=id', 'bidang' => 'image processing; artifcial intelligence; telemedicine; sensor and actuator; Renewable Energy', 'citations' => 477, 'h_index' => 13, 'i10_index' => 18],
    ['nama' => 'Prof. Dr. Eng. Ir. Suroso', 'scholar' => 'https://scholar.google.com/citations?user=nXhlkmUAAAAJ&hl=id', 'bidang' => 'Power Electronics; Renewable Energy', 'citations' => 411, 'h_index' => 10, 'i10_index' => 12],
    ['nama' => 'Dr. Ir. Acep Taryana, M.T.', 'scholar' => 'https://scholar.google.com/citations?user=EjvohFkAAAAJ&hl=id', 'bidang' => 'Software Engineering; Formal Method; Verification System', 'citations' => 157, 'h_index' => 7, 'i10_index' => 5],
];

try {
    require_once __DIR__ . '/includes/portal_lib.php';
    $kontenDariDatabase = mte_portal_public_content();
    $vmts = $cpl = $ppm = $kurikulum = $berita = $dokumen = $peminatan = $dosen = [];
    foreach ($kontenDariDatabase as $row) {
        if ($row['section'] === 'vmts') {
            $vmts[] = ['label' => $row['label'], 'judul' => $row['title'], 'teks' => $row['body']];
        } elseif ($row['section'] === 'cpl' || $row['section'] === 'ppm') {
            $item = ['kode' => $row['code'], 'judul' => $row['title'], 'teks' => $row['body']];
            if ($row['section'] === 'cpl') {
                $cpl[] = $item;
            } else {
                $ppm[] = $item;
            }
        } elseif ($row['section'] === 'kurikulum') {
            $kurikulum[] = ['semester' => (int) $row['label'], 'kode' => $row['code'], 'kelompok' => $row['kind'], 'judul' => $row['title'], 'sks' => (int) $row['summary']];
        } elseif ($row['section'] === 'berita') {
            $fotoBerita = [];
            $galeriDikelola = false;
            try {
                foreach (mte_portal_gallery_for_content((int) $row['id']) as $foto) {
                    $fotoBerita[] = ['file' => $foto['file_name'], 'alt' => $foto['alt_text'], 'caption' => $foto['caption']];
                }
                $galeriDikelola = true;
            } catch (Throwable $galleryError) {
                $fotoBerita = [];
            }
            $berita[] = ['id' => $row['slug'], 'record_id' => (int) $row['id'], 'jenis' => $row['kind'], 'tanggal' => (string) $row['publish_date'], 'judul' => $row['title'], 'ringkasan' => $row['summary'], 'isi' => $row['body'], 'galeri' => $fotoBerita, 'galeri_managed' => $galeriDikelola];
        } elseif ($row['section'] === 'dokumen') {
            $dokumen[] = ['id' => $row['slug'], 'jenis' => $row['kind'] ?: 'PDF', 'judul' => $row['title'], 'keterangan' => $row['summary'], 'narasi' => $row['body'], 'file' => $row['file_name']];
        } elseif ($row['section'] === 'peminatan') {
            $peminatan[] = ['judul' => $row['title'], 'keterangan' => $row['body']];
        } elseif ($row['section'] === 'dosen') {
            $dosen[] = [
                'nama' => (string) $row['title'],
                'scholar' => (string) $row['file_name'],
                'bidang' => (string) $row['summary'],
                'citations' => (int) $row['code'],
                'h_index' => (int) $row['kind'],
                'i10_index' => (int) $row['label'],
            ];
        }
    }
} catch (Throwable $e) {
    error_log('MTE portal: database konten belum tersedia; menampilkan konten bawaan.');
}

$kurikulumPerSemester = [];
foreach ($kurikulum as $mataKuliah) {
    $kurikulumPerSemester[(int) $mataKuliah['semester']][] = $mataKuliah;
}
ksort($kurikulumPerSemester, SORT_NUMERIC);

$pilihanTipeBerita = $_GET['tipe'] ?? '';
$tipeAktif = is_string($pilihanTipeBerita) && isset($tipeBerita[$pilihanTipeBerita]) ? $pilihanTipeBerita : '';
$beritaTerfilter = array_values(array_filter($berita, static function (array $artikel) use ($tipeAktif): bool {
    return $tipeAktif === '' || $artikel['jenis'] === $tipeAktif;
}));

$perHalamanDokumen = 9;
$nilaiHalamanDokumen = $_GET['halaman'] ?? 1;
$nomorHalamanDokumen = is_scalar($nilaiHalamanDokumen) ? filter_var((string) $nilaiHalamanDokumen, FILTER_VALIDATE_INT) : false;
$totalHalamanDokumen = max(1, (int) ceil(count($dokumen) / $perHalamanDokumen));
$halamanDokumen = $nomorHalamanDokumen === false ? 1 : max(1, min($totalHalamanDokumen, (int) $nomorHalamanDokumen));
$dokumenTampil = array_slice($dokumen, ($halamanDokumen - 1) * $perHalamanDokumen, $perHalamanDokumen);
$idDokumen = $_GET['id'] ?? '';
$dokumenDetail = null;
if ($aktif === 'dokumen' && is_string($idDokumen)) {
    foreach ($dokumen as $itemDokumen) {
        if ($itemDokumen['id'] === $idDokumen) {
            $dokumenDetail = $itemDokumen;
            break;
        }
    }
}

function mteEscape(string $teks): string
{
    return htmlspecialchars($teks, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function mteAdminBrand(string $subtitle): void
{
    ?>
    <span class="admin-brand-mark">MTE</span>
    <span><strong>Magister Teknik Elektro</strong><small><?= mteEscape($subtitle) ?></small></span>
    <?php
}

function mteIcon(string $assetPath, string $name): void
{
    $iconHref = mteEscape($assetPath . '/portal-icons.svg?v=3#' . rawurlencode($name));
    ?>
    <svg class="ui-icon" width="18" height="18" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><use href="<?= $iconHref ?>"/></svg>
    <?php
}

function mteEncodePath(string $path): string
{
    return implode('/', array_map('rawurlencode', explode('/', $path)));
}

function mteSafeExternalUrl(string $url): string
{
    $url = trim($url);
    if ($url === '' || !preg_match('#^https?://#i', $url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
        return '';
    }
    return $url;
}

function mteDosenFields(string $raw): array
{
    $parts = preg_split('/\s*;\s*/', trim($raw)) ?: [];
    return array_values(array_filter(array_map('trim', $parts), static function (string $item): bool {
        return $item !== '';
    }));
}

function mteBodyHtml(string $html): string
{
    if (function_exists('mte_portal_sanitize_html')) {
        $safeHtml = mte_portal_sanitize_html($html);
        return function_exists('mte_portal_keep_hyphenated_words_together')
            ? mte_portal_keep_hyphenated_words_together($safeHtml)
            : $safeHtml;
    }
    return '<p>' . nl2br(mteEscape($html), false) . '</p>';
}

function mteTanggal(string $tanggal): string
{
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    [$tahun, $nomorBulan, $hari] = explode('-', $tanggal);

    return (int) $hari . ' ' . $bulan[(int) $nomorBulan] . ' ' . $tahun;
}

function mteNewsDate(string $tanggal): void
{
    ?>
    <time class="news-date" datetime="<?= mteEscape($tanggal) ?>">
        <svg aria-hidden="true" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="5" width="18" height="16" rx="2" />
            <path d="M16 3v4M8 3v4M3 10h18" />
        </svg>
        <?= mteEscape(mteTanggal($tanggal)) ?>
    </time>
    <?php
}

function mteUrlDokumen(string $basePath, int $halaman): string
{
    $url = rtrim($basePath, '/') . '/dokumen/';
    if ($halaman > 1) {
        $url .= 'halaman/' . $halaman . '/';
    }

    return mteEscape($url) . '#isi';
}

function mteUrlDetailDokumen(string $basePath, string $id, int $halaman): string
{
    $url = rtrim($basePath, '/') . '/dokumen/' . rawurlencode($id) . '/';
    if ($halaman > 1) {
        $url .= 'halaman/' . $halaman . '/';
    }

    return mteEscape($url) . '#isi';
}

$perHalaman = 9;
$nilaiHalaman = $_GET['halaman'] ?? 1;
$halaman = is_scalar($nilaiHalaman) ? filter_var((string) $nilaiHalaman, FILTER_VALIDATE_INT) : false;
$totalHalaman = max(1, (int) ceil(count($beritaTerfilter) / $perHalaman));
$halaman = $halaman === false ? 1 : max(1, min($totalHalaman, (int) $halaman));
$beritaTampil = array_slice($beritaTerfilter, ($halaman - 1) * $perHalaman, $perHalaman);

function mteUrlBerita(string $basePath, string $tipe, int $halaman): string
{
    $url = rtrim($basePath, '/') . '/berita/';
    if ($tipe !== '') {
        $url .= 'tipe/' . rawurlencode($tipe) . '/';
    }
    if ($halaman > 1) {
        $url .= 'halaman/' . $halaman . '/';
    }

    return mteEscape($url) . '#isi';
}

function mteUrlDetailBerita(string $basePath, string $id, string $tipe, int $halaman): string
{
    $url = rtrim($basePath, '/') . '/berita/';
    if ($tipe !== '') {
        $url .= 'tipe/' . rawurlencode($tipe) . '/';
    }
    $url .= rawurlencode($id) . '/';
    if ($halaman > 1) {
        $url .= 'halaman/' . $halaman . '/';
    }

    return mteEscape($url) . '#isi';
}

$idBerita = $_GET['id'] ?? '';
$beritaDetail = null;
if ($aktif === 'berita' && is_string($idBerita)) {
    foreach ($berita as $artikel) {
        if ($artikel['id'] === $idBerita) {
            $beritaDetail = $artikel;
            break;
        }
    }
}

function mteSeoConfiguredBaseUrl(): string
{
    $candidate = trim((string) (getenv('MTE_PUBLIC_URL') ?: ''));
    if ($candidate === '' && function_exists('mte_config')) {
        try {
            $candidate = trim((string) (mte_config()['public_url'] ?? ''));
        } catch (Throwable $error) {
            $candidate = '';
        }
    }
    if ($candidate === '') {
        return '';
    }

    $parts = parse_url($candidate);
    if (!is_array($parts)
        || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
        || empty($parts['host'])
        || isset($parts['user'])
        || isset($parts['pass'])
        || isset($parts['query'])
        || isset($parts['fragment'])) {
        return '';
    }

    $host = strtolower((string) $parts['host']);
    if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local')) {
        return '';
    }
    if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return '';
        }
    } elseif (filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false) {
        return '';
    }

    $path = (string) ($parts['path'] ?? '');
    if (preg_match('#(?:^|/)\.\.(?:/|$)#', rawurldecode($path))) {
        return '';
    }
    $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';
    return strtolower((string) $parts['scheme']) . '://' . $host . $port . rtrim($path, '/');
}

function mteSeoIsLocalRequest(): bool
{
    $requestHost = trim((string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
    if ($requestHost === '') {
        return true;
    }
    $host = strtolower((string) (parse_url('http://' . $requestHost, PHP_URL_HOST) ?: ''));
    if ($host === '' || $host === 'localhost' || str_ends_with($host, '.localhost')
        || str_ends_with($host, '.local') || str_ends_with($host, '.test')
        || str_ends_with($host, '.internal') || str_ends_with($host, '.invalid')) {
        return true;
    }
    if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
        return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
    return filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false;
}

function mteSeoPlainText(string $value, int $limit = 300): string
{
    $plain = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $plain = preg_replace('/\s+/u', ' ', trim($plain)) ?? trim($plain);
    if (function_exists('mb_substr')) {
        return mb_substr($plain, 0, $limit, 'UTF-8');
    }
    return substr($plain, 0, $limit);
}

function mteExcerpt(string $value, int $limit = 115): string
{
    $plain = mteSeoPlainText($value, $limit + 1);
    $len = function_exists('mb_strlen') ? mb_strlen($plain, 'UTF-8') : strlen($plain);
    if ($len <= $limit) {
        return $plain;
    }
    $cut = function_exists('mb_substr') ? mb_substr($plain, 0, $limit, 'UTF-8') : substr($plain, 0, $limit);
    return rtrim($cut) . '…';
}

function mteSeoAbsoluteUrl(string $baseUrl, string $path): string
{
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

$seoBaseUrl = mteSeoConfiguredBaseUrl();
$seoPath = '/';
$seoSectionWasRequested = isset($_GET['bagian']) && is_string($pilihanBagian) && isset($bagian[$pilihanBagian]);
if ($seoSectionWasRequested) {
    $seoPath = $aktif === 'vmts' ? '/' : '/' . rawurlencode($aktif) . '/';
    if ($aktif === 'berita') {
        if ($beritaDetail !== null) {
            $seoPath .= rawurlencode((string) $beritaDetail['id']) . '/';
        } else {
            if ($tipeAktif !== '') {
                $seoPath .= 'tipe/' . rawurlencode($tipeAktif) . '/';
            }
        }
        if ($beritaDetail === null && $halaman > 1) {
            $seoPath .= 'halaman/' . $halaman . '/';
        }
    } elseif ($aktif === 'dokumen') {
        if ($dokumenDetail !== null) {
            $seoPath .= rawurlencode((string) $dokumenDetail['id']) . '/';
        } elseif ($halamanDokumen > 1) {
            $seoPath .= 'halaman/' . $halamanDokumen . '/';
        }
    }
}
$seoCanonical = $seoBaseUrl !== '' ? mteSeoAbsoluteUrl($seoBaseUrl, $seoPath) : '';
$seoRobots = mteSeoIsLocalRequest() ? 'noindex, nofollow' : 'index, follow';
$seoTitle = $seoSectionWasRequested
    ? $bagian[$aktif][1] . ' | Magister Teknik Elektro UNSOED'
    : 'Portal Akademik S2 Teknik Elektro Unsoed';
$seoDescription = 'Mencari Prodi S2 Teknik Elektro di Jawa Tengah? Magister Teknik Elektro UNSOED di Purwokerto menyajikan info pascasarjana, kurikulum, peminatan ELC/TIB, CPL, VMTS, agenda, dan berita.';
$seoType = 'website';
if ($seoSectionWasRequested) {
    $seoDescriptions = [
        'vmts' => 'Visi dan misi Prodi S2 Teknik Elektro UNSOED (Magister Teknik Elektro): menjadi program studi unggul bertaraf internasional dalam pengembangan teknologi elektro.',
        'cpl' => 'CPL Prodi S2 atau Magister Teknik Elektro UNSOED: capaian pembelajaran dan kompetensi lulusan Program Studi Teknik Elektro Universitas Jenderal Soedirman.',
        'ppm' => 'Profil Profesional Mandiri (PPM) lulusan Magister Teknik Elektro UNSOED dan gambaran peran profesional di bidang rekayasa elektro.',
        'kurikulum' => 'Kurikulum Prodi S2 Teknik Elektro UNSOED atau Magister Teknik Elektro: mata kuliah dan beban SKS tiap semester.',
        'berita' => 'Berita, pengumuman, dan agenda Prodi Magister Teknik Elektro UNSOED di Purwokerto, Jawa Tengah.',
        'dokumen' => 'Dokumen akademik Prodi S2 Teknik Elektro UNSOED, termasuk pedoman studi dan informasi Magister Teknik Elektro.',
        'peminatan' => 'Peminatan Magister Teknik Elektro UNSOED: Energi Listrik Cerdas (ELC) dan Teknologi Informasi dan Biomedis (TIB).',
        'dosen' => 'Daftar dosen Program Studi Magister Teknik Elektro UNSOED beserta bidang riset, metrik sitasi, dan tautan Google Scholar.',
    ];
    $seoDescription = $seoDescriptions[$aktif] ?? $seoDescription;
    if ($aktif === 'berita') {
        if ($beritaDetail !== null) {
            $seoTitle = (string) $beritaDetail['judul'] . ' | Berita Magister Teknik Elektro UNSOED';
            $ringkasanBerita = mteSeoPlainText((string) $beritaDetail['ringkasan'], 190);
            $seoDescription = trim($ringkasanBerita . ' Berita Magister Teknik Elektro UNSOED.');
            $seoType = 'article';
        } elseif ($tipeAktif !== '') {
            $seoTitle = $tipeBerita[$tipeAktif] . ' S2 Teknik Elektro UNSOED | Berita';
            $seoDescription = $tipeBerita[$tipeAktif] . ' Program Studi Magister Teknik Elektro UNSOED, dengan informasi terkini untuk mahasiswa dan calon mahasiswa.';
        }
    } elseif ($aktif === 'dokumen' && $dokumenDetail !== null) {
        $seoTitle = (string) $dokumenDetail['judul'] . ' | Dokumen Magister Teknik Elektro UNSOED';
        $ringkasanDokumen = mteSeoPlainText((string) $dokumenDetail['keterangan'], 190);
        $seoDescription = trim($ringkasanDokumen . ' Dokumen akademik S2 Teknik Elektro UNSOED.');
    }
    if ($aktif === 'berita' && !$beritaDetail && isset($_GET['id'])) {
        $seoRobots = 'noindex, follow';
    } elseif ($aktif === 'dokumen' && !$dokumenDetail && isset($_GET['id'])) {
        $seoRobots = 'noindex, follow';
    }
    if ($aktif === 'berita' && !$beritaDetail && $halaman > 1) {
        $seoTitle .= ' | Halaman ' . $halaman;
    } elseif ($aktif === 'dokumen' && $halamanDokumen > 1) {
        $seoTitle .= ' | Halaman ' . $halamanDokumen;
    }
}

$seoGraph = [];
if ($seoBaseUrl !== '') {
    $organizationId = mteSeoAbsoluteUrl($seoBaseUrl, '/#organization');
    $seoGraph[] = [
        '@type' => 'WebSite',
        '@id' => mteSeoAbsoluteUrl($seoBaseUrl, '/#website'),
        'url' => mteSeoAbsoluteUrl($seoBaseUrl, '/'),
        'name' => 'Portal Akademik S2 Teknik Elektro Unsoed',
        'inLanguage' => 'id-ID',
        'publisher' => ['@id' => $organizationId],
    ];
    $seoGraph[] = [
        '@type' => 'EducationalOrganization',
        '@id' => $organizationId,
        'name' => 'Program Studi Magister Teknik Elektro, Universitas Jenderal Soedirman',
        'url' => mteSeoAbsoluteUrl($seoBaseUrl, '/'),
        'parentOrganization' => [
            '@type' => 'CollegeOrUniversity',
            'name' => 'Universitas Jenderal Soedirman',
            'url' => $situsUnsoed,
        ],
    ];
    $breadcrumbItems = [[
        '@type' => 'ListItem',
        'position' => 1,
        'name' => 'Portal Akademik S2 Teknik Elektro Unsoed',
        'item' => mteSeoAbsoluteUrl($seoBaseUrl, '/'),
    ]];
    if ($seoSectionWasRequested) {
        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => $bagian[$aktif][1],
            'item' => mteSeoAbsoluteUrl($seoBaseUrl, $aktif === 'vmts' ? '/' : '/' . $aktif . '/'),
        ];
        $breadcrumbDetail = $aktif === 'berita' ? $beritaDetail : ($aktif === 'dokumen' ? $dokumenDetail : null);
        if ($breadcrumbDetail !== null) {
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => (string) $breadcrumbDetail['judul'],
                'item' => $seoCanonical,
            ];
        }
    }
    $seoGraph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => $breadcrumbItems];
    if ($beritaDetail !== null && $seoType === 'article') {
        $articleData = [
            '@type' => 'NewsArticle',
            'headline' => (string) $beritaDetail['judul'],
            'description' => $seoDescription,
            'mainEntityOfPage' => $seoCanonical,
            'publisher' => ['@id' => $organizationId],
            'inLanguage' => 'id-ID',
        ];
        $publishedDate = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $beritaDetail['tanggal']);
        if ($publishedDate instanceof DateTimeImmutable && $publishedDate->format('Y-m-d') === (string) $beritaDetail['tanggal']) {
            $articleData['datePublished'] = $publishedDate->format('Y-m-d');
        }
        $seoGraph[] = $articleData;
    }
}

$seoRequest = $_GET['seo'] ?? '';
if ($seoRequest === 'robots.txt') {
    header('Content-Type: text/plain; charset=UTF-8');
    header('X-Robots-Tag: noindex, nofollow');
    $seoBasePath = $seoBaseUrl !== '' ? (string) (parse_url($seoBaseUrl, PHP_URL_PATH) ?: '') : $basePath;
    $robotsPrefix = rtrim($seoBasePath, '/') . '/';
    echo "User-agent: *\nAllow: /\nDisallow: {$robotsPrefix}admin/\nDisallow: {$robotsPrefix}masuk\nDisallow: {$robotsPrefix}kelola-st/\nDisallow: {$robotsPrefix}simulasi-akreditasi/\n";
    if ($seoBaseUrl !== '') {
        echo 'Sitemap: ' . mteSeoAbsoluteUrl($seoBaseUrl, '/sitemap.xml') . "\n";
    }
    exit;
}
if ($seoRequest === 'sitemap.xml') {
    header('Content-Type: application/xml; charset=UTF-8');
    header('X-Robots-Tag: noindex, nofollow');
    if ($seoBaseUrl === '') {
        http_response_code(503);
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<error>Configure MTE_PUBLIC_URL or public_url before publishing the sitemap.</error>";
        exit;
    }
    $sitemapEntries = [];
    foreach (['', 'cpl/', 'ppm/', 'kurikulum/', 'peminatan/', 'dosen/', 'berita/', 'dokumen/'] as $publicPath) {
        $sitemapEntries[mteSeoAbsoluteUrl($seoBaseUrl, '/' . $publicPath)] = '';
    }
    foreach ($berita as $article) {
        $articleSlug = (string) ($article['id'] ?? '');
        if ($articleSlug !== '') {
            $articleUrl = mteSeoAbsoluteUrl($seoBaseUrl, '/berita/' . rawurlencode($articleSlug) . '/');
            $date = (string) ($article['tanggal'] ?? '');
            $validDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            $sitemapEntries[$articleUrl] = $validDate instanceof DateTimeImmutable && $validDate->format('Y-m-d') === $date ? $date : '';
        }
    }
    foreach ($dokumen as $document) {
        $documentSlug = (string) ($document['id'] ?? '');
        if ($documentSlug !== '') {
            $sitemapEntries[mteSeoAbsoluteUrl($seoBaseUrl, '/dokumen/' . rawurlencode($documentSlug) . '/')] = '';
        }
    }
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach ($sitemapEntries as $location => $lastModified) {
        echo "  <url><loc>" . htmlspecialchars($location, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
        if ($lastModified !== '') {
            echo '<lastmod>' . htmlspecialchars($lastModified, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</lastmod>';
        }
        echo "</url>\n";
    }
    echo "</urlset>\n";
    exit;
}

$requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$indexPath = $basePath . '/index.php';
if (is_string($requestPath) && rtrim($requestPath, '/') === rtrim($indexPath, '/')) {
    $tujuan = $portalPath;
    if (isset($_GET['bagian']) && is_string($pilihanBagian) && isset($bagian[$pilihanBagian])) {
        $tujuan .= rawurlencode($pilihanBagian) . '/';
        if ($pilihanBagian === 'berita') {
            if ($tipeAktif !== '') {
                $tujuan .= 'tipe/' . rawurlencode($tipeAktif) . '/';
            }
            if (is_string($idBerita) && $beritaDetail !== null) {
                $tujuan .= rawurlencode($idBerita) . '/';
                $nomorHalaman = is_scalar($nilaiHalaman) ? filter_var((string) $nilaiHalaman, FILTER_VALIDATE_INT) : false;
                if ($nomorHalaman !== false && $nomorHalaman > 1) {
                    $tujuan .= 'halaman/' . min((int) $nomorHalaman, $totalHalaman) . '/';
                }
            } else {
                $nomorHalaman = is_scalar($nilaiHalaman) ? filter_var((string) $nilaiHalaman, FILTER_VALIDATE_INT) : false;
                if ($nomorHalaman !== false && $nomorHalaman > 1) {
                    $tujuan .= 'halaman/' . min((int) $nomorHalaman, $totalHalaman) . '/';
                }
            }
        } elseif ($pilihanBagian === 'dokumen') {
            if (is_string($idDokumen) && $dokumenDetail !== null) {
                $tujuan .= rawurlencode($idDokumen) . '/';
            }
            if ($halamanDokumen > 1) {
                $tujuan .= 'halaman/' . $halamanDokumen . '/';
            }
        }
    }

    header('Location: ' . $tujuan, true, 301);
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'none'; img-src 'self' data:; script-src 'self'; style-src 'self'; base-uri 'none'; frame-ancestors 'none'");
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="description" content="<?= mteEscape($seoDescription) ?>">
    <meta name="robots" content="<?= mteEscape($seoRobots) ?>">
    <meta property="og:type" content="<?= mteEscape($seoType) ?>">
    <meta property="og:site_name" content="Portal Akademik S2 Teknik Elektro Unsoed">
    <meta property="og:locale" content="id_ID">
    <meta property="og:title" content="<?= mteEscape($seoTitle) ?>">
    <meta property="og:description" content="<?= mteEscape($seoDescription) ?>">
    <?php if ($seoCanonical !== ''): ?>
        <link rel="canonical" href="<?= mteEscape($seoCanonical) ?>">
        <meta property="og:url" content="<?= mteEscape($seoCanonical) ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary">
    <title><?= mteEscape($seoTitle) ?></title>
    <?php if ($seoGraph): ?>
        <script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => $seoGraph], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}' ?></script>
    <?php endif; ?>
    <link rel="icon" type="image/svg+xml" href="<?= mteEscape($assetPath) ?>/unsoed-favicon.svg?v=1">
    <link rel="stylesheet" href="<?= mteEscape($assetPath) ?>/simulasi.css?v=2">
    <link rel="stylesheet" href="<?= mteEscape($assetPath) ?>/mte-portal.css?v=20">
</head>
<body>
    <a class="skip-link" href="#main">Langsung ke konten</a>

    <header class="site-header">
        <a class="brand" href="<?= mteEscape($portalPath) ?>">
            <span class="brand-mark">MTE</span>
            <div><strong>Magister Teknik Elektro</strong><span>Jurusan Teknik Elektro</span></div>
        </a>
        <nav class="header-tools" aria-label="Situs resmi">
            <a href="<?= mteEscape($situsFakultas) ?>"><?php mteIcon($assetPath, 'faculty'); ?> Fakultas Teknik</a>
            <a href="<?= mteEscape($situsUnsoed) ?>"><?php mteIcon($assetPath, 'campus'); ?> Universitas Jenderal Soedirman</a>
        </nav>
    </header>

    <main id="main" class="page">
        <section class="hero" aria-label="Pengantar portal">
            <div>
                <span class="eyebrow">PORTAL PROGRAM STUDI</span>
                <h1>Portal Akademik<br><span>S2 Teknik Elektro Unsoed</span></h1>
                <p>Menyajikan informasi VMTS, CPL, PPM, Kurikulum, Pengumuman, Agenda dan Berita</p>
            </div>
            <div class="hero-index vision-index">
                <span class="mini-label"><?php mteIcon($assetPath, 'vmts'); ?> VISI PROGRAM STUDI</span>
                <blockquote>Menjadi program studi Magister Teknik Elektro yang unggul di tingkat internasional dalam pengembangan teknologi dan keilmuan Teknik Elektro untuk mendukung pengembangan sumber daya lokal.</blockquote>
            </div>
        </section>

        <?php
        $isPortalHome = !$seoSectionWasRequested;
        $tautanJurusan = [
            ['campus', '', 'Profil Jurusan', 'Identitas dan arah jurusan'],
            ['dosen', '#/faculty', 'Dosen', 'Profil tenaga pengajar'],
            ['riset', '#/research', 'Riset', 'Bidang dan kegiatan riset'],
            ['kemahasiswaan', '#/student-life', 'Kemahasiswaan', 'Kegiatan dan kehidupan mahasiswa'],
        ];
        ?>

        <nav class="portal-nav portal-nav-top" aria-label="Menu portal MTE">
            <a href="<?= mteEscape($portalPath) ?>"<?= $isPortalHome ? ' aria-current="page"' : '' ?> title="Ringkasan seluruh modul portal MTE">
                <span class="menu-icon"><?php mteIcon($assetPath, 'campus'); ?></span>
                <span>Beranda</span>
            </a>
            <?php foreach ($bagian as $kode => [$label, $judul, $deskripsi]): ?>
                <a href="<?= mteEscape($portalPath . rawurlencode($kode)) ?>/#isi"<?= (!$isPortalHome && $aktif === $kode) ? ' aria-current="page"' : '' ?> title="<?= mteEscape($deskripsi) ?>">
                    <span class="menu-icon"><?php mteIcon($assetPath, $kode); ?></span>
                    <span><?= mteEscape($label) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php if ($isPortalHome): ?>
            <?php
            $beritaTerbaru = array_slice($berita, 0, 3);
            $beritaUtama = $beritaTerbaru[0] ?? null;
            $beritaLain = array_slice($beritaTerbaru, 1);
            $dokumenRingkas = array_slice($dokumen, 0, 4);
            $totalSksKurikulum = array_sum(array_map('intval', array_column($kurikulum, 'sks')));
            ?>
            <section id="isi" class="bento-portal" aria-label="Ringkasan portal MTE">
                <div class="bento-grid">
                    <div class="bento-box bento-span-12 bento-vmts">
                        <div class="bento-head">
                            <div class="bento-head-title">
                                <span class="menu-icon"><?php mteIcon($assetPath, 'vmts'); ?></span>
                                <div>
                                    <span class="eyebrow">ARAH &amp; MANDAT</span>
                                    <h2>Visi, Misi, Tujuan &amp; Strategi</h2>
                                </div>
                            </div>
                            <a class="bento-more" href="<?= mteEscape($portalPath . 'vmts/#isi') ?>">Selengkapnya <?php mteIcon($assetPath, 'arrow'); ?></a>
                        </div>
                        <div class="bento-vmts-grid">
                            <?php foreach ($vmts as $item): ?>
                                <a class="bento-vmts-tile" href="<?= mteEscape($portalPath . 'vmts/#isi') ?>">
                                    <span class="outcome-code"><?= mteEscape($item['label']) ?></span>
                                    <h3><?= mteEscape($item['judul']) ?></h3>
                                    <p><?= mteEscape(mteExcerpt((string) $item['teks'], 105)) ?></p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bento-box bento-span-7 bento-news">
                        <div class="bento-head">
                            <div class="bento-head-title">
                                <span class="menu-icon"><?php mteIcon($assetPath, 'berita'); ?></span>
                                <div>
                                    <span class="eyebrow">KABAR TERKINI</span>
                                    <h2>Berita, Pengumuman &amp; Agenda</h2>
                                </div>
                            </div>
                            <a class="bento-more" href="<?= mteEscape($portalPath . 'berita/#isi') ?>">Semua <?php mteIcon($assetPath, 'arrow'); ?></a>
                        </div>
                        <?php if ($beritaUtama !== null): ?>
                            <a class="bento-news-featured" href="<?= mteUrlDetailBerita($basePath, (string) $beritaUtama['id'], '', 1) ?>">
                                <div class="bento-news-meta">
                                    <span class="news-type-tag news-type-<?= mteEscape($beritaUtama['jenis']) ?>"><?= mteEscape($tipeBerita[$beritaUtama['jenis']]) ?></span>
                                    <?php mteNewsDate((string) $beritaUtama['tanggal']); ?>
                                </div>
                                <h3><?= mteEscape($beritaUtama['judul']) ?></h3>
                                <p><?= mteEscape(mteExcerpt((string) $beritaUtama['ringkasan'], 150)) ?></p>
                            </a>
                        <?php endif; ?>
                        <?php if ($beritaLain): ?>
                            <div class="bento-news-list">
                                <?php foreach ($beritaLain as $artikel): ?>
                                    <a class="bento-news-row" href="<?= mteUrlDetailBerita($basePath, (string) $artikel['id'], '', 1) ?>">
                                        <span class="news-type-tag news-type-<?= mteEscape($artikel['jenis']) ?>"><?= mteEscape($tipeBerita[$artikel['jenis']]) ?></span>
                                        <strong class="bento-news-row-title"><?= mteEscape($artikel['judul']) ?></strong>
                                        <span class="bento-news-row-date"><?= mteEscape(mteTanggal((string) $artikel['tanggal'])) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="bento-box bento-span-5 bento-peminatan">
                        <div class="bento-head">
                            <div class="bento-head-title">
                                <span class="menu-icon"><?php mteIcon($assetPath, 'peminatan'); ?></span>
                                <div>
                                    <span class="eyebrow">KONSENTRASI</span>
                                    <h2>Peminatan Prodi</h2>
                                </div>
                            </div>
                            <a class="bento-more" href="<?= mteEscape($portalPath . 'peminatan/#isi') ?>">Detail <?php mteIcon($assetPath, 'arrow'); ?></a>
                        </div>
                        <div class="bento-track-list">
                            <?php foreach ($peminatan as $idx => $item): ?>
                                <a class="bento-track-card" href="<?= mteEscape($portalPath . 'peminatan/#isi') ?>">
                                    <span class="bento-track-badge">0<?= $idx + 1 ?></span>
                                    <div>
                                        <h3><?= mteEscape($item['judul']) ?></h3>
                                        <p><?= mteEscape(mteExcerpt((string) $item['keterangan'], 110)) ?></p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bento-box bento-span-4 bento-kurikulum">
                        <div class="bento-head">
                            <div class="bento-head-title">
                                <span class="menu-icon"><?php mteIcon($assetPath, 'kurikulum'); ?></span>
                                <div>
                                    <span class="eyebrow">STRUKTUR STUDI</span>
                                    <h2>Kurikulum</h2>
                                </div>
                            </div>
                            <a class="bento-more" href="<?= mteEscape($portalPath . 'kurikulum/#isi') ?>">Lengkap <?php mteIcon($assetPath, 'arrow'); ?></a>
                        </div>
                        <div class="bento-stat-bar">
                            <div><strong><?= (int) $totalSksKurikulum ?></strong><span>Total SKS</span></div>
                            <div><strong><?= count($kurikulumPerSemester) ?></strong><span>Semester</span></div>
                            <div><strong><?= count($kurikulum) ?></strong><span>Mata Kuliah</span></div>
                        </div>
                        <div class="bento-sem-grid">
                            <?php foreach ($kurikulumPerSemester as $sem => $mks): ?>
                                <?php $sksSem = array_sum(array_column($mks, 'sks')); ?>
                                <a class="bento-sem-tile" href="<?= mteEscape($portalPath . 'kurikulum/#semester-' . (int) $sem) ?>">
                                    <strong>Semester <?= (int) $sem ?></strong>
                                    <span><?= (int) $sksSem ?> SKS · <?= count($mks) ?> MK</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bento-box bento-span-4 bento-dokumen">
                        <div class="bento-head">
                            <div class="bento-head-title">
                                <span class="menu-icon"><?php mteIcon($assetPath, 'dokumen'); ?></span>
                                <div>
                                    <span class="eyebrow">UNDUHAN RESMI</span>
                                    <h2>Dokumen Akademik</h2>
                                </div>
                            </div>
                            <a class="bento-more" href="<?= mteEscape($portalPath . 'dokumen/#isi') ?>">Semua <?php mteIcon($assetPath, 'arrow'); ?></a>
                        </div>
                        <div class="bento-doc-list">
                            <?php foreach ($dokumenRingkas as $doc): ?>
                                <div class="bento-doc-row">
                                    <a class="bento-doc-link" href="<?= mteUrlDetailDokumen($basePath, (string) $doc['id'], 1) ?>">
                                        <span class="bento-doc-type"><?= mteEscape($doc['jenis']) ?></span>
                                        <span class="bento-doc-title"><?= mteEscape($doc['judul']) ?></span>
                                    </a>
                                    <a class="bento-doc-dl" href="<?= mteEscape($portalPath . 'unggah/' . mteEncodePath((string) $doc['file'])) ?>" download aria-label="Unduh <?= mteEscape($doc['judul']) ?>">
                                        <svg aria-hidden="true" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5M12 15V3"/></svg>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bento-box bento-span-4 bento-kompetensi">
                        <div class="bento-head">
                            <div class="bento-head-title">
                                <span class="menu-icon"><?php mteIcon($assetPath, 'cpl'); ?></span>
                                <div>
                                    <span class="eyebrow">KOMPETENSI</span>
                                    <h2>CPL &amp; PPM Lulusan</h2>
                                </div>
                            </div>
                        </div>
                        <div class="bento-stat-pair">
                            <a class="bento-stat-card" href="<?= mteEscape($portalPath . 'cpl/#isi') ?>">
                                <strong><?= count($cpl) ?></strong>
                                <span>Butir CPL Prodi</span>
                                <small>Capaian Pembelajaran →</small>
                            </a>
                            <a class="bento-stat-card" href="<?= mteEscape($portalPath . 'ppm/#isi') ?>">
                                <strong><?= count($ppm) ?></strong>
                                <span>Profil PPM</span>
                                <small>Profil Mandiri →</small>
                            </a>
                        </div>
                        <div class="bento-chip-cloud">
                            <?php foreach (array_slice($cpl, 0, 4) as $item): ?>
                                <a class="bento-chip" href="<?= mteEscape($portalPath . 'cpl/#isi') ?>">
                                    <b>CPL-<?= mteEscape($item['kode']) ?></b> <?= mteEscape($item['judul']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <section class="portal-links" aria-labelledby="tautan-jurusan">
                    <div class="portal-links-head">
                        <span class="eyebrow">INFORMASI JURUSAN</span>
                        <h3 id="tautan-jurusan">Jelajahi Jurusan Teknik Elektro</h3>
                    </div>
                    <div class="portal-links-grid">
                        <?php foreach ($tautanJurusan as [$icon, $fragment, $label, $description]): ?>
                            <a class="portal-link-tile" href="<?= mteEscape($situsJurusan . $fragment) ?>" target="_blank" rel="noopener noreferrer">
                                <span class="menu-icon"><?php mteIcon($assetPath, $icon); ?></span>
                                <span class="portal-link-text"><strong><?= mteEscape($label) ?></strong><small><?= mteEscape($description) ?></small></span>
                                <span class="menu-external"><?php mteIcon($assetPath, 'external'); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            </section>
        <?php else: ?>
        <section id="isi" class="tab-panel" aria-labelledby="judul-bagian">
            <div class="section-heading">
                <span class="section-icon"><?php mteIcon($assetPath, $aktif); ?></span>
                <div>
                    <h2 id="judul-bagian"><?= mteEscape($bagian[$aktif][1]) ?></h2>
                    <?php if ($aktif === 'vmts'): ?>
                        <p>Ruang untuk arah, mandat, sasaran, dan rencana pengembangan program studi.</p>
                    <?php elseif ($aktif === 'cpl'): ?>
                        <p>Ruang untuk daftar capaian lulusan dan rumusan lengkap setiap butir.</p>
                    <?php elseif ($aktif === 'ppm'): ?>
                        <p>Ruang untuk profil profesional lulusan setelah memasuki dunia kerja.</p>
                    <?php elseif ($aktif === 'kurikulum'): ?>
                        <p>Daftar mata kuliah per semester beserta kode dan beban SKS.</p>
                    <?php elseif ($aktif === 'dokumen'): ?>
                        <p><?= $dokumenDetail !== null ? 'Rincian dokumen dan berkas unduh program studi.' : 'Daftar dokumen program studi, lengkap dengan akses unduh langsung dan halaman detail.' ?></p>
                    <?php elseif ($aktif === 'peminatan'): ?>
                        <p>Gambaran bidang keilmuan yang dapat dipilih mahasiswa program studi.</p>
                    <?php elseif ($aktif === 'dosen'): ?>
                        <p>Daftar dosen pengajar beserta bidang riset, metrik sitasi, dan tautan profil Google Scholar.</p>
                    <?php else: ?>
                        <p>Berita, pengumuman, dan agenda program studi dengan filter kategori.</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($aktif !== 'dosen'): ?>
            <div class="detail-layout">
                <aside class="detail-dosen-aside" aria-label="Daftar dosen program studi">
                    <div class="dosen-aside-head">
                        <div>
                            <span class="eyebrow">DOSEN MTE</span>
                            <h3>Daftar Dosen</h3>
                        </div>
                        <a class="dosen-aside-all" href="<?= mteEscape($portalPath . 'dosen/#isi') ?>">Semua <?php mteIcon($assetPath, 'arrow'); ?></a>
                    </div>
                    <?php if (!$dosen): ?>
                        <p class="empty-state">Informasi dosen belum tersedia.</p>
                    <?php else: ?>
                        <?php
                        $dosenAside = $dosen;
                        shuffle($dosenAside);
                        $dosenAside = array_slice($dosenAside, 0, 5);
                        ?>
                        <ul class="dosen-aside-list" role="list">
                            <?php foreach ($dosenAside as $item): ?>
                                <?php $scholarUrl = mteSafeExternalUrl((string) ($item['scholar'] ?? '')); ?>
                                <li class="dosen-aside-item">
                                    <?php if ($scholarUrl !== ''): ?>
                                        <a class="dosen-name-link" href="<?= mteEscape($scholarUrl) ?>" target="_blank" rel="noopener noreferrer">
                                            <span><?= mteEscape($item['nama']) ?></span>
                                            <?php mteIcon($assetPath, 'external'); ?>
                                        </a>
                                    <?php else: ?>
                                        <strong class="dosen-name-text"><?= mteEscape($item['nama']) ?></strong>
                                    <?php endif; ?>
                                    <div class="dosen-cited-tags">
                                        <span class="dosen-cite-tag is-citations">Cited: <?= (int) $item['citations'] ?></span>
                                        <span class="dosen-cite-tag">h-index: <?= (int) $item['h_index'] ?></span>
                                        <span class="dosen-cite-tag">i10-index: <?= (int) $item['i10_index'] ?></span>
                                    </div>
                                    <?php $fields = mteDosenFields((string) ($item['bidang'] ?? '')); ?>
                                    <?php if ($fields): ?>
                                        <div class="dosen-field-tags">
                                            <?php foreach ($fields as $field): ?>
                                                <span class="dosen-field-tag"><?= mteEscape($field) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </aside>
            <?php endif; ?>

            <div class="portal-content">
                    <?php if ($aktif === 'vmts' || $aktif === 'cpl' || $aktif === 'ppm'): ?>
                        <?php $daftar = $aktif === 'vmts' ? $vmts : ($aktif === 'cpl' ? $cpl : $ppm); ?>
                        <div class="outcome-list">
                            <?php foreach ($daftar as $item): ?>
                                <article class="outcome">
                                    <span class="outcome-code"><?= mteEscape($aktif === 'vmts' ? $item['label'] : ($aktif === 'cpl' ? 'CPL-' . $item['kode'] : 'PPM-' . $item['kode'])) ?></span>
                                    <div>
                                        <h3><?= mteEscape($item['judul']) ?></h3>
                                        <div class="rich-content"><?= mteBodyHtml((string) $item['teks']) ?></div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($aktif === 'kurikulum'): ?>
                        <div class="curriculum-list">
                            <?php if (!$kurikulumPerSemester): ?>
                                <p class="empty-state">Informasi kurikulum belum tersedia.</p>
                            <?php endif; ?>
                            <?php foreach ($kurikulumPerSemester as $semester => $mataKuliahSemester): ?>
                                <?php $totalSksSemester = array_sum(array_column($mataKuliahSemester, 'sks')); ?>
                                <section class="curriculum-semester" aria-labelledby="semester-<?= (int) $semester ?>">
                                    <header class="curriculum-semester-head" data-semester="<?= (int) $semester ?>">
                                        <h3 id="semester-<?= (int) $semester ?>">Semester <?= (int) $semester ?></h3>
                                        <span class="curriculum-sks-total"><?= (int) $totalSksSemester ?> SKS</span>
                                    </header>
                                    <ul class="curriculum-courses" role="list">
                                        <?php foreach ($mataKuliahSemester as $mataKuliah): ?>
                                            <li class="curriculum-course">
                                                <code class="curriculum-course-code" title="<?= mteEscape($mataKuliah['kode']) ?>"><?= mteEscape($mataKuliah['kode']) ?></code>
                                                <h4 title="<?= mteEscape($mataKuliah['judul']) ?>"><?= mteEscape($mataKuliah['judul']) ?></h4>
                                                <span class="curriculum-course-sks"><?= (int) $mataKuliah['sks'] ?> SKS</span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </section>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($aktif === 'dokumen'): ?>
                        <?php if ($dokumenDetail !== null): ?>
                            <a class="button detail-back" href="<?= mteUrlDokumen($basePath, $halamanDokumen) ?>">Kembali ke daftar dokumen</a>
                            <article class="method-card detail-article document-detail">
                                <span class="news-type-tag">DOKUMEN PROGRAM STUDI</span>
                                <h3><?= mteEscape($dokumenDetail['judul']) ?></h3>
                                <div class="document-meta">
                                    <span>Format</span><strong>PDF</strong>
                                    <span>Nama berkas</span><strong><?= mteEscape($dokumenDetail['file']) ?></strong>
                                </div>
                                <div class="rich-content"><?= mteBodyHtml((string) $dokumenDetail['narasi']) ?></div>
                                <a class="button document-download" href="<?= mteEscape($portalPath . 'unggah/' . mteEncodePath($dokumenDetail['file'])) ?>" download>
                                    <svg aria-hidden="true" viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5M12 15V3"/></svg>
                                    Unduh dokumen PDF
                                </a>
                            </article>
                        <?php else: ?>
                            <div class="document-list">
                                <?php foreach ($dokumenTampil as $item): ?>
                                    <article class="method-card document-card">
                                        <span class="mini-label"><?= mteEscape($item['jenis']) ?></span>
                                        <h3><?= mteEscape($item['judul']) ?></h3>
                                        <p><?= mteEscape($item['keterangan']) ?></p>
                                        <div class="document-actions">
                                            <a href="<?= mteUrlDetailDokumen($basePath, $item['id'], $halamanDokumen) ?>">Detail dokumen <?php mteIcon($assetPath, 'arrow'); ?></a>
                                            <a class="download-icon" href="<?= mteEscape($portalPath . 'unggah/' . mteEncodePath($item['file'])) ?>" download aria-label="Unduh <?= mteEscape($item['judul']) ?> PDF">
                                                <svg aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5M12 15V3"/></svg>
                                            </a>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                            <nav class="pager" aria-label="Navigasi halaman dokumen">
                                <span>Halaman <?= $halamanDokumen ?> dari <?= $totalHalamanDokumen ?></span>
                                <div class="pager-nav">
                                    <?php if ($halamanDokumen > 1): ?>
                                        <a href="<?= mteUrlDokumen($basePath, $halamanDokumen - 1) ?>" aria-label="Halaman dokumen sebelumnya">‹ Sebelumnya</a>
                                    <?php else: ?>
                                        <span class="pager-disabled" aria-disabled="true">‹ Sebelumnya</span>
                                    <?php endif; ?>
                                    <?php for ($i = 1; $i <= $totalHalamanDokumen; $i++): ?>
                                        <?php if ($i === $halamanDokumen): ?>
                                            <span class="pager-current" aria-current="page"><?= $i ?></span>
                                        <?php else: ?>
                                            <a href="<?= mteUrlDokumen($basePath, $i) ?>" aria-label="Halaman dokumen <?= $i ?>"><?= $i ?></a>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    <?php if ($halamanDokumen < $totalHalamanDokumen): ?>
                                        <a href="<?= mteUrlDokumen($basePath, $halamanDokumen + 1) ?>" aria-label="Halaman dokumen berikutnya">Berikutnya ›</a>
                                    <?php else: ?>
                                        <span class="pager-disabled" aria-disabled="true">Berikutnya ›</span>
                                    <?php endif; ?>
                                </div>
                            </nav>
                        <?php endif; ?>
                    <?php elseif ($aktif === 'peminatan'): ?>
                        <div class="idea-grid">
                            <?php foreach ($peminatan as $item): ?>
                                <article class="method-card document-card">
                                    <span class="mini-label">PEMINATAN</span>
                                    <h3><?= mteEscape($item['judul']) ?></h3>
                                    <div class="rich-content"><?= mteBodyHtml((string) $item['keterangan']) ?></div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($aktif === 'dosen'): ?>
                        <?php if (!$dosen): ?>
                            <p class="empty-state">Informasi dosen belum tersedia.</p>
                        <?php else: ?>
                            <div class="dosen-directory-grid">
                                <?php foreach ($dosen as $item): ?>
                                    <?php $scholarUrl = mteSafeExternalUrl((string) ($item['scholar'] ?? '')); ?>
                                    <article class="method-card dosen-card">
                                        <h3>
                                            <?php if ($scholarUrl !== ''): ?>
                                                <a class="dosen-name-link" href="<?= mteEscape($scholarUrl) ?>" target="_blank" rel="noopener noreferrer">
                                                    <span><?= mteEscape($item['nama']) ?></span>
                                                    <?php mteIcon($assetPath, 'external'); ?>
                                                </a>
                                            <?php else: ?>
                                                <?= mteEscape($item['nama']) ?>
                                            <?php endif; ?>
                                        </h3>
                                        <div class="dosen-cited-tags">
                                            <span class="dosen-cite-tag is-citations">Cited: <?= (int) $item['citations'] ?></span>
                                            <span class="dosen-cite-tag">h-index: <?= (int) $item['h_index'] ?></span>
                                            <span class="dosen-cite-tag">i10-index: <?= (int) $item['i10_index'] ?></span>
                                        </div>
                                        <?php $fields = mteDosenFields((string) ($item['bidang'] ?? '')); ?>
                                        <?php if ($fields): ?>
                                            <div class="dosen-field-tags">
                                                <?php foreach ($fields as $field): ?>
                                                    <span class="dosen-field-tag"><?= mteEscape($field) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if ($beritaDetail !== null): ?>
                            <?php $galeriBeritaTampil = !empty($beritaDetail['galeri_managed']) ? ($beritaDetail['galeri'] ?? []) : ($beritaDetail['id'] === 'seminar-riset' ? $galeriSeminar : []); ?>
                            <a class="button detail-back" href="<?= mteUrlBerita($basePath, $tipeAktif, $halaman) ?>">Kembali ke daftar berita</a>
                            <article class="method-card detail-article news-detail-article">
                                <span class="news-type-tag news-type-<?= mteEscape($beritaDetail['jenis']) ?>"><?= mteEscape($tipeBerita[$beritaDetail['jenis']]) ?></span>
                                <h3><?= mteEscape($beritaDetail['judul']) ?></h3>
                                <?php mteNewsDate($beritaDetail['tanggal']); ?>
                                <div class="rich-content"><?= mteBodyHtml((string) $beritaDetail['isi']) ?></div>
                            <?php if ($galeriBeritaTampil): ?>
                                <section class="gallery-section" aria-labelledby="judul-galeri">
                                    <div class="gallery-heading">
                                        <div>
                                            <span class="eyebrow">DOKUMENTASI KEGIATAN</span>
                                            <h3 id="judul-galeri">Galeri foto</h3>
                                        </div>
                                        <span><?= count($galeriBeritaTampil) ?> foto</span>
                                    </div>
                                    <div class="gallery-grid">
                                        <?php foreach ($galeriBeritaTampil as $indeks => $foto): ?>
                                            <?php
                                            if (strpos($foto['file'], 'asset:') === 0) {
                                                $srcFoto = $assetPath . '/' . rawurlencode(substr($foto['file'], 6));
                                            } elseif (strpos($foto['file'], 'galeri/') === 0) {
                                                $srcFoto = $portalPath . 'unggah/' . mteEncodePath($foto['file']);
                                            } else {
                                                $srcFoto = $assetPath . '/' . rawurlencode($foto['file']);
                                            }
                                            ?>
                                            <button class="gallery-thumb" type="button" data-gallery-open data-gallery-index="<?= $indeks ?>" aria-label="Buka foto: <?= mteEscape($foto['caption']) ?>">
                                                <img src="<?= mteEscape($srcFoto) ?>" alt="<?= mteEscape($foto['alt']) ?>" loading="lazy">
                                                <span><?= mteEscape($foto['caption']) ?></span>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php endif; ?>
                            </article>
                            <?php if ($galeriBeritaTampil): ?>
                                <dialog class="gallery-dialog" data-gallery-dialog aria-label="Galeri foto berita">
                                    <div class="gallery-dialog-head">
                                        <span data-gallery-count></span>
                                        <button class="gallery-close" type="button" data-gallery-close aria-label="Tutup galeri">Tutup ×</button>
                                    </div>
                                    <div class="gallery-stage">
                                        <button class="gallery-arrow" type="button" data-gallery-prev aria-label="Foto sebelumnya">‹</button>
                                        <figure>
                                            <img data-gallery-image src="" alt="">
                                            <figcaption data-gallery-caption></figcaption>
                                        </figure>
                                        <button class="gallery-arrow" type="button" data-gallery-next aria-label="Foto berikutnya">›</button>
                                    </div>
                                </dialog>
                                <script src="<?= mteEscape($assetPath) ?>/mte-gallery.js" defer></script>
                            <?php endif; ?>
                        <?php else: ?>
                        <nav class="news-filters" aria-label="Filter jenis informasi">
                            <a href="<?= mteUrlBerita($basePath, '', 1) ?>"<?= $tipeAktif === '' ? ' aria-current="page"' : '' ?>>Semua</a>
                            <?php foreach ($tipeBerita as $tipe => $labelTipe): ?>
                                <a href="<?= mteUrlBerita($basePath, $tipe, 1) ?>"<?= $tipeAktif === $tipe ? ' aria-current="page"' : '' ?>><?= mteEscape($labelTipe) ?></a>
                            <?php endforeach; ?>
                        </nav>
                        <div class="news-list">
                            <?php foreach ($beritaTampil as $artikel): ?>
                                <article class="method-card news-card">
                                    <span class="news-type-tag news-type-<?= mteEscape($artikel['jenis']) ?>"><?= mteEscape($tipeBerita[$artikel['jenis']]) ?></span>
                                    <h3><?= mteEscape($artikel['judul']) ?></h3>
                                    <p><?= mteEscape($artikel['ringkasan']) ?></p>
                                    <?php mteNewsDate($artikel['tanggal']); ?>
                                    <a class="news-read" href="<?= mteUrlDetailBerita($basePath, $artikel['id'], $tipeAktif, $halaman) ?>">Baca <?php mteIcon($assetPath, 'arrow'); ?></a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                        <nav class="pager" aria-label="Navigasi halaman berita">
                            <span>Halaman <?= $halaman ?> dari <?= $totalHalaman ?></span>
                            <div class="pager-nav">
                                <?php if ($halaman > 1): ?>
                                    <a href="<?= mteUrlBerita($basePath, $tipeAktif, $halaman - 1) ?>" aria-label="Halaman berita sebelumnya">‹ Sebelumnya</a>
                                <?php else: ?>
                                    <span class="pager-disabled" aria-disabled="true">‹ Sebelumnya</span>
                                <?php endif; ?>
                                <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                                    <?php if ($i === $halaman): ?>
                                        <span class="pager-current" aria-current="page"><?= $i ?></span>
                                    <?php else: ?>
                                    <a href="<?= mteUrlBerita($basePath, $tipeAktif, $i) ?>" aria-label="Halaman berita <?= $i ?>"><?= $i ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <?php if ($halaman < $totalHalaman): ?>
                                    <a href="<?= mteUrlBerita($basePath, $tipeAktif, $halaman + 1) ?>" aria-label="Halaman berita berikutnya">Berikutnya ›</a>
                                <?php else: ?>
                                    <span class="pager-disabled" aria-disabled="true">Berikutnya ›</span>
                                <?php endif; ?>
                            </div>
                        </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php if ($aktif !== 'dosen'): ?>
            </div>
            <?php endif; ?>

            <section class="portal-links" aria-labelledby="tautan-jurusan">
                <div class="portal-links-head">
                    <span class="eyebrow">INFORMASI JURUSAN</span>
                    <h3 id="tautan-jurusan">Jelajahi Jurusan Teknik Elektro</h3>
                </div>
                <div class="portal-links-grid">
                    <?php foreach ($tautanJurusan as [$icon, $fragment, $label, $description]): ?>
                        <a class="portal-link-tile" href="<?= mteEscape($situsJurusan . $fragment) ?>" target="_blank" rel="noopener noreferrer">
                            <span class="menu-icon"><?php mteIcon($assetPath, $icon); ?></span>
                            <span class="portal-link-text"><strong><?= mteEscape($label) ?></strong><small><?= mteEscape($description) ?></small></span>
                            <span class="menu-external"><?php mteIcon($assetPath, 'external'); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </section>
        <?php endif; ?>

        <footer class="page-footer">
            <div class="footer-grid">
                <div class="footer-logo">
                    <a class="brand" href="<?= mteEscape($portalPath) ?>">
                        <span class="brand-mark">MTE</span>
                        <div><strong>Magister Teknik Elektro</strong><span>Jurusan Teknik Elektro</span></div>
                    </a>
                    <p class="footer-address">Jln. Mayjen Sungkono Km.5 Kec. Kalimanah, Kab. Purbalingga</p>
                </div>
                <div class="footer-links-pair">
                    <nav class="footer-col" aria-label="Tautan portal">
                        <span class="footer-title">PORTAL</span>
                        <ul class="footer-link-list">
                            <?php foreach (['vmts', 'cpl', 'ppm', 'peminatan'] as $kode): ?>
                                <?php if (isset($bagian[$kode])): ?>
                                    <li><a href="<?= mteEscape($portalPath . rawurlencode($kode)) ?>/#isi"><?= mteEscape($bagian[$kode][1]) ?></a></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                    <nav class="footer-col" aria-label="Tautan jurusan">
                        <span class="footer-title">JURUSAN</span>
                        <ul class="footer-link-list">
                            <?php foreach ($tautanJurusan as [$icon, $fragment, $label]): ?>
                                <li><a href="<?= mteEscape($situsJurusan . $fragment) ?>" target="_blank" rel="noopener noreferrer"><?= mteEscape($label) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                </div>
                <div class="footer-location">
                    <span class="footer-title">LOKASI &amp; KONTAK</span>
                    <p>Jln. Mayjen Sungkono Km.5, Blater, Kalimanah, Kab. Purbalingga — Fakultas Teknik UNSOED.</p>
                    <div class="footer-contact-actions">
                        <a class="footer-contact-pill" href="mailto:mte@unsoed.ac.id">
                            <?php mteIcon($assetPath, 'mail'); ?>
                            <span>mte@unsoed.ac.id</span>
                        </a>
                        <a class="footer-contact-pill" href="https://maps.app.goo.gl/N1nw25njo52pv9v96" target="_blank" rel="noopener noreferrer">
                            <?php mteIcon($assetPath, 'map-pin'); ?>
                            <span>Buka Maps FT</span>
                        </a>
                    </div>
                </div>
            </div>
        </footer>
        <div class="page-copyright">
            <span>© 2026 Jurusan Teknik Elektro UNSOED. Seluruh hak cipta dilindungi.</span>
            <span>Fakultas Teknik · Universitas Jenderal Soedirman</span>
        </div>
    </main>

    <a class="floating-admisi-cta" href="https://pasca.admisi.unsoed.ac.id/" target="_blank" rel="noopener noreferrer" aria-label="Bergabunglah Bersama Kami - SPMB Pascasarjana UNSOED">
        <span class="floating-admisi-dot" aria-hidden="true"></span>
        <span>Bergabunglah Bersama Kami</span>
        <?php mteIcon($assetPath, 'arrow'); ?>
    </a>
</body>
</html>
