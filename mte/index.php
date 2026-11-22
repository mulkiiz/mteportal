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
    define('MTE_PORTAL_ROUTED', true);
    require_once __DIR__ . '/includes/portal_lib.php';
    require __DIR__ . '/admin/masuk_page.php';
    exit;
}
if ($routePath === $basePath . '/admin') {
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
    'berita' => ['Berita', 'Berita Program Studi', 'Berita, pengumuman, dan agenda program studi'],
    'dokumen' => ['Dokumen', 'Dokumen Program Studi', 'Pedoman dan berkas akademik'],
    'peminatan' => ['Peminatan', 'Peminatan Program Studi', 'Energi Listrik Cerdas (ELC) dan Teknologi Informasi dan Biomedis (TIB)'],
];
$pilihanBagian = $_GET['bagian'] ?? 'vmts';
$aktif = is_string($pilihanBagian) && isset($bagian[$pilihanBagian]) ? $pilihanBagian : 'vmts';

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

try {
    require_once __DIR__ . '/includes/portal_lib.php';
    $kontenDariDatabase = mte_portal_public_content();
    $vmts = $cpl = $ppm = $kurikulum = $berita = $dokumen = $peminatan = [];
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
    $iconHref = mteEscape($assetPath . '/portal-icons.svg?v=2#' . rawurlencode($name));
    ?>
    <svg class="ui-icon" width="18" height="18" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><use href="<?= $iconHref ?>"/></svg>
    <?php
}

function mteEncodePath(string $path): string
{
    return implode('/', array_map('rawurlencode', explode('/', $path)));
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
header("Content-Security-Policy: default-src 'none'; img-src 'self' data:; script-src 'self'; style-src 'self' 'unsafe-inline'; base-uri 'none'; frame-ancestors 'none'");
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="description" content="Informasi akademik, capaian pembelajaran, profil profesional mandiri, dan berita Program Studi Magister Teknik Elektro Unsoed.">
    <title><?= mteEscape($bagian[$aktif][1]) ?> · MTE UNSOED</title>
    <link rel="icon" type="image/svg+xml" href="<?= mteEscape($assetPath) ?>/unsoed-favicon.svg?v=1">
    <link rel="stylesheet" href="<?= mteEscape($assetPath) ?>/simulasi.css?v=2">
    <style>
        .ui-icon { display: block; flex: none; }
        .brand-mark { background: linear-gradient(145deg, #3b82f6, #1d4ed8); box-shadow: 0 3px 8px #1d4ed833; text-decoration: none; }
        .brand-mark:hover { text-decoration: none; }
        .brand > div > span { font-size: 10px; letter-spacing: .3px; }
        .header-tools { gap: 14px; flex-wrap: wrap; justify-content: flex-end; }
        .header-tools a { display: inline-flex; align-items: center; gap: 6px; color: var(--muted); font-size: 11px; white-space: nowrap; }
        .header-tools a .ui-icon { width: 15px; height: 15px; color: var(--teal); }
        .header-tools a + a { padding-left: 14px; border-left: 1px solid var(--line); }
        .hero { margin-top: 24px; padding: clamp(24px, 4vw, 48px); border: 1px solid #d7e3f5; border-radius: 20px; background: radial-gradient(ellipse at 100% 0%, #dbe8ff 0%, transparent 60%), #f3f7ff; }
        .vision-index { display: block; max-width: 410px; justify-self: end; border: 1px solid #d7e3f5; border-radius: 12px; padding: 17px 20px; background: #ffffffc9; }
        .vision-index .mini-label { display: flex; align-items: center; gap: 7px; margin-bottom: 8px; }
        .vision-index .mini-label .ui-icon { width: 15px; height: 15px; }
        .vision-index blockquote { margin: 0; color: var(--ink); font-size: 12px; line-height: 1.75; }
        .tabs { margin-inline: -2px; }
        .tabs a { display: block; padding: 19px 0 16px; border-bottom: 3px solid transparent; color: var(--muted); font-size: 13px; font-weight: 600; white-space: nowrap; }
        .tabs a:hover { color: var(--teal); text-decoration: none; }
        .tabs a[aria-current="page"] { border-color: var(--teal); color: var(--teal); }
        .tab-panel { min-height: 285px; }
        .section-heading { justify-content: flex-start; gap: 14px; padding-bottom: 18px; border-bottom: 1px solid var(--line); }
        .section-icon { display: grid; width: 48px; height: 48px; flex: 0 0 48px; place-items: center; border: 1px solid #d0dffc; border-radius: 13px; background: #eaf1ff; color: var(--teal); }
        .section-icon .ui-icon { width: 23px; height: 23px; }
        .section-heading h1, .section-heading h2 { font-size: 30px; letter-spacing: -.9px; }
        .section-heading h1 { font-size: clamp(35px, 4vw, 53px); line-height: 1.1; letter-spacing: -2px; }
        .portal-layout { display: grid; grid-template-columns: 228px minmax(0, 1fr); gap: 24px; align-items: start; }
        .portal-outline { position: sticky; top: 86px; display: grid; gap: 3px; padding: 11px; border: 1px solid var(--line); border-radius: 13px; background: #fff; }
        .portal-outline > a { display: flex; align-items: flex-start; gap: 9px; padding: 8px; border: 1px solid transparent; border-radius: 8px; color: var(--ink); font-size: 12px; line-height: 1.45; }
        .portal-outline > a:hover, .portal-outline > a:focus-visible { border-color: #cedcf3; background: #f4f7ff; text-decoration: none; }
        .portal-outline > a strong, .portal-outline > a small { display: block; }
        .portal-outline > a strong { font-size: 12px; }
        .portal-outline > a small { margin-top: 3px; color: #53686b; font-size: 11px; }
        .portal-outline > a[aria-current="page"] { border-color: #c4d3f0; background: #eaf1ff; }
        .portal-outline > a[aria-current="page"] strong { color: var(--teal); }
        .portal-outline .outline-section { display: block; margin: 5px 8px 4px; color: #53686b; }
        .portal-outline .department-menu-label { margin-top: 9px; padding-top: 10px; border-top: 1px solid var(--line); }
        .menu-icon { display: grid; width: 29px; height: 29px; flex: 0 0 29px; place-items: center; border-radius: 7px; background: #edf3ff; color: var(--teal); }
        .menu-icon .ui-icon { width: 16px; height: 16px; }
        [aria-current="page"] > .menu-icon { background: #2563eb; color: #fff; }
        .menu-external { margin: 4px 0 0 auto; color: #53686b; }
        .menu-external .ui-icon { width: 12px; height: 12px; }
        .portal-content { min-width: 0; }
        .idea-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .vmts-grid { grid-template-columns: minmax(0, 1fr); }
        .idea-card { margin: 0; padding: 21px 23px; }
        .idea-card .mini-label { display: block; }
        .idea-card h3 { margin-top: 8px; font-size: 16px; letter-spacing: -.2px; }
        .idea-card p { margin: 9px 0 0; font-size: 12px; line-height: 1.8; }
        .outcome-list { display: grid; gap: 9px; }
        .outcome { display: grid; grid-template-columns: 63px minmax(0, 1fr); gap: 16px; padding: 17px 19px; border: 1px solid var(--line); border-radius: 10px; background: white; }
        .outcome-code { align-self: start; padding: 5px 7px; border-radius: 5px; background: var(--teal-light); color: var(--teal); font-size: 10px; font-weight: 750; text-align: center; }
        .outcome h3 { margin: 0 0 5px; font-size: 14px; }
        .outcome p { margin: 0; color: var(--muted); font-size: 12px; line-height: 1.8; }
        .curriculum-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; gap: 14px; }
        .curriculum-list > .empty-state { grid-column: 1 / -1; }
        .curriculum-semester { overflow: hidden; border: 1px solid #d7e2f3; border-radius: 11px; background: #fff; box-shadow: 0 3px 12px #163b430a; }
        .curriculum-semester-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 9px 12px; background: linear-gradient(105deg, #3575d2, #2d67c4); color: #fff; }
        .curriculum-semester-head h3 { margin: 0; color: inherit; font-size: 15px; }
        .curriculum-sks-total { padding: 3px 8px; border: 1px solid #ffffff55; border-radius: 999px; background: #ffffff1f; color: inherit; font-size: 11px; font-weight: 750; white-space: nowrap; }
        .curriculum-courses { margin: 0; padding: 0; list-style: none; }
        .curriculum-course { display: grid; grid-template-columns: 64px minmax(0, 1fr) 44px; align-items: center; gap: 8px; min-height: 38px; padding: 8px 10px; line-height: 1.4; }
        .curriculum-course + .curriculum-course { border-top: 1px solid #e6ebf5; }
        .curriculum-course:nth-child(even) { background: #f6f9ff; }
        .curriculum-course:hover { background: #eff5ff; }
        .curriculum-course-code { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .curriculum-course h4 { margin: 0; color: var(--ink); font-size: 12px; font-weight: 650; overflow-wrap: anywhere; }
        .curriculum-course-code { color: #4b6269; font: 600 10px/1.35 Consolas, "Courier New", monospace; }
        .curriculum-course-sks { padding-left: 6px; border-left: 1px solid #d5e0f3; color: var(--ink); font-size: 11px; font-weight: 650; font-variant-numeric: tabular-nums; text-align: right; white-space: nowrap; }
        .rich-content { color: var(--muted); font-size: 12px; line-height: 1.8; overflow-wrap: break-word; word-break: normal; hyphens: none; }
        .rich-content p { margin: 0 0 10px; color: inherit; font: inherit; line-height: inherit; }
        .rich-content p:last-child { margin-bottom: 0; }
        .rich-content ul, .rich-content ol { margin: 8px 0; padding-left: 22px; }
        .rich-content li { padding-left: 2px; }
        .rich-content .rich-table-scroll { max-width: 100%; overflow-x: auto; margin: 12px 0; }
        .rich-content table { width: 100%; min-width: 440px; border-collapse: collapse; color: var(--ink); font-size: 11px; }
        .rich-content th, .rich-content td { padding: 8px 9px; border: 1px solid var(--line); text-align: left; vertical-align: top; }
        .rich-content th { background: #f5f7f9; color: var(--ink); font-size: 10px; }
        .idea-grid > .document-card .rich-content th { background: linear-gradient(105deg, #3575d2, #2d67c4); color: #fff; }
        .rich-content th:last-child, .rich-content td:last-child { width: 62px; text-align: center; white-space: nowrap; }
        .rich-content a { color: var(--teal); text-decoration: underline; }
        .rich-content img { display: block; max-width: 100%; height: auto; margin: 12px 0; border-radius: 8px; }
        .rich-content blockquote { margin: 12px 0; padding: 8px 14px; border-left: 3px solid #8fb1f2; color: var(--ink); background: #f4f7ff; }
        .news-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 250px), 1fr)); align-items: stretch; gap: 11px; }
        .news-filters { display: flex; flex-wrap: wrap; gap: 7px; margin: 0 0 15px; }
        .news-filters a { display: inline-flex; align-items: center; min-height: 32px; padding: 6px 12px; border: 1px solid var(--line); border-radius: 999px; background: white; color: var(--muted); font-size: 11px; font-weight: 650; }
        .news-filters a:hover { border-color: var(--teal); color: var(--teal); text-decoration: none; }
        .news-filters a[aria-current="page"] { border-color: var(--teal); background: var(--teal); color: white; }
        .news-type-tag { display: inline-flex; width: fit-content; align-items: center; padding: 5px 9px; border-radius: 999px; background: #eaf1ff; color: var(--teal); font-size: 9px; font-weight: 750; letter-spacing: .4px; text-transform: uppercase; }
        .news-type-pengumuman { background: #f4f1e8; color: #80683b; }
        .news-type-agenda { background: #eaf0f8; color: #476689; }
        .news-card { margin: 0; padding: 20px 22px; }
        .news-card .mini-label { color: #80683b; }
        .news-card h3 { margin: 7px 0 6px; font-size: 16px; }
        .news-card p { margin: 0; font-size: 12px; line-height: 1.8; }
        .news-date { display: flex; align-items: center; gap: 5px; color: var(--muted); }
        .news-card .news-date { margin-top: 10px; font-size: 10px; }
        .news-read { display: inline-block; margin-top: 10px; font-size: 11px; font-weight: 650; }
        .pager { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 19px; padding-top: 14px; border-top: 1px solid var(--line); color: var(--muted); font-size: 11px; }
        .pager-nav { display: flex; flex-wrap: wrap; gap: 5px; align-items: center; }
        .pager a, .pager-current, .pager-disabled { display: inline-grid; min-width: 32px; min-height: 31px; place-items: center; padding: 4px 9px; border: 1px solid var(--line); border-radius: 5px; background: white; }
        .pager a { color: var(--teal); }
        .pager a:hover { border-color: var(--teal); text-decoration: none; }
        .pager-current { border-color: var(--teal); background: var(--teal); color: white; font-weight: 700; }
        .pager-disabled { color: #9aa7a5; }
        .detail-back { margin-bottom: 18px; }
        .detail-article { max-width: 760px; padding: 28px; }
        .news-detail-article { width: 100%; max-width: none; min-width: 0; }
        .detail-article h3 { margin: 9px 0 12px; font-size: 24px; }
        .detail-article p { color: var(--muted); font-size: 14px; line-height: 1.9; }
        .detail-article .news-date { margin: 0 0 14px; font-size: 11px; }
        .detail-article .gallery-section { margin-top: 20px; padding-top: 17px; border-top: 1px solid var(--line); }
        .gallery-heading { display: flex; align-items: end; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
        .gallery-heading .eyebrow { margin-bottom: 5px; }
        .gallery-heading h3 { margin: 0; font-size: 20px; }
        .gallery-heading > span { color: var(--muted); font-size: 11px; }
        .gallery-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .detail-article .gallery-grid { grid-template-columns: repeat(3, minmax(0, 170px)); gap: 10px; }
        .gallery-thumb { overflow: hidden; padding: 0; border: 1px solid var(--line); border-radius: 10px; background: white; text-align: left; cursor: pointer; transition: transform .18s ease, box-shadow .18s ease; }
        .gallery-thumb:hover, .gallery-thumb:focus-visible { transform: translateY(-3px); box-shadow: var(--shadow); }
        .gallery-thumb img { display: block; width: 100%; aspect-ratio: 16 / 10; object-fit: cover; }
        .gallery-thumb span { display: block; padding: 9px 11px; color: var(--ink); font-size: 11px; font-weight: 650; }
        .gallery-dialog { width: min(920px, calc(100% - 28px)); max-width: none; padding: 18px; border: 1px solid #ffffff30; border-radius: 14px; background: #10232c; color: white; }
        .gallery-dialog::backdrop { background: #06151dcc; backdrop-filter: blur(4px); }
        .gallery-dialog-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 12px; color: #c5d4d8; font-size: 11px; }
        .gallery-close, .gallery-arrow { border: 1px solid #ffffff35; border-radius: 8px; background: #ffffff12; color: white; cursor: pointer; }
        .gallery-close { padding: 8px 12px; font-size: 12px; }
        .gallery-stage { display: grid; grid-template-columns: 42px minmax(0, 1fr) 42px; align-items: center; gap: 10px; }
        .gallery-arrow { width: 40px; height: 48px; font-size: 28px; line-height: 1; }
        .gallery-stage figure { min-width: 0; margin: 0; text-align: center; }
        .gallery-stage img { display: block; width: 100%; max-height: 68vh; object-fit: contain; border-radius: 8px; }
        .gallery-stage figcaption { padding-top: 10px; color: #e2eaec; font-size: 12px; }
        .document-card { margin: 0; padding: 20px 22px; }
        .document-card h3 { margin: 8px 0 6px; font-size: 16px; }
        .document-card p { margin: 0; color: var(--muted); font-size: 12px; }
        .document-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 250px), 1fr)); gap: 12px; }
        .portal-content .news-read, .document-actions > a:first-child { display: inline-flex; align-items: center; gap: 6px; }
        .news-read .ui-icon, .document-actions > a:first-child .ui-icon { width: 14px; height: 14px; }
        .document-actions { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--line); }
        .document-actions > a:first-child { font-size: 11px; font-weight: 650; }
        .document-actions .download-icon { display: inline-flex; flex: 0 0 40px; align-items: center; justify-content: center; width: 40px; height: 40px; padding: 0; border: 1px solid var(--line); border-radius: 9px; background: #eaf1ff; color: var(--teal); line-height: 0; }
        .document-actions .download-icon svg { display: block; flex: 0 0 auto; width: 18px; height: 18px; }
        .download-icon:hover { border-color: var(--teal); background: var(--teal); color: white; text-decoration: none; }
        .document-meta { display: flex; flex-wrap: wrap; gap: 7px 12px; margin: 0 0 15px; padding: 12px 0; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); font-size: 11px; }
        .document-meta span { color: var(--muted); }
        .document-meta strong { margin-right: 14px; color: var(--ink); }
        .document-download { display: inline-flex; align-items: center; gap: 8px; margin-top: 8px; }
        .idea-card, .outcome, .news-card, .detail-article, .document-card, .portal-outline > a {
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .idea-card:hover, .outcome:hover, .news-card:hover, .detail-article:hover, .document-card:hover, .portal-outline > a:hover {
            transform: translateY(-3px);
            border-color: #b2c6ef;
            box-shadow: var(--shadow);
        }
        .page-footer { margin-top: 18px; }
        @media (min-width: 1160px) {
            .curriculum-list { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 7px; }
            .curriculum-semester-head { padding-inline: 12px; }
            .curriculum-course {
                grid-template-columns: 54px minmax(0, 1fr) 42px;
                gap: 7px;
                min-height: 38px;
                padding: 7px 9px;
            }
            .curriculum-course h4 { font-size: 12px; letter-spacing: -.1px; }
            .curriculum-course-code, .curriculum-course-sks { font-size: 10px; }
        }
        @media (max-width: 800px) {
            .portal-layout { grid-template-columns: minmax(0, 1fr); gap: 17px; }
            .portal-outline { position: static; grid-template-columns: repeat(auto-fit, minmax(min(100%, 145px), 1fr)); gap: 6px; }
            .portal-outline > .outline-section { grid-column: 1 / -1; }
            .portal-outline > a { padding: 7px 6px; }
            .menu-external { display: none; }
        }
        @media (max-width: 640px) {
            .site-header { height: auto; min-height: 76px; align-items: flex-start; flex-direction: column; gap: 8px; padding: 10px 16px; }
            .header-tools { width: 100%; justify-content: flex-start; gap: 8px; }
            .header-tools a { font-size: 10px; }
            .header-tools a + a { padding-left: 8px; }
            .hero { margin-top: 13px; gap: 18px; }
            .vision-index { max-width: none; justify-self: stretch; padding: 13px; }
            .vision-index blockquote { font-size: 11px; line-height: 1.65; }
            .tabs { gap: 19px; margin-right: -16px; margin-left: -16px; padding: 0 16px; }
            .tabs a { padding: 16px 0 13px; font-size: 11px; }
            .tab-panel { padding-top: 25px; }
            .section-heading { gap: 12px; margin-bottom: 19px; }
            .section-icon { width: 40px; height: 40px; flex-basis: 40px; }
            .section-heading h1, .section-heading h2 { font-size: 24px; }
            .section-heading p { font-size: 11px; }
            .idea-grid { grid-template-columns: minmax(0, 1fr); gap: 10px; }
            .curriculum-list { grid-template-columns: minmax(0, 1fr); gap: 10px; }
            .curriculum-course { grid-template-columns: 64px minmax(0, 1fr) 44px; gap: 8px; padding-inline: 9px; }
            .curriculum-course h4 { font-size: 12px; }
            .idea-card { padding: 18px; }
            .outcome { grid-template-columns: 52px minmax(0, 1fr); gap: 11px; padding: 15px 12px; }
            .news-card { padding: 17px; }
            .news-card h3 { font-size: 14px; }
            .detail-article { padding: 19px; }
            .detail-article h3 { font-size: 19px; }
            .document-meta { font-size: 10px; }
            .detail-article .gallery-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .gallery-stage { grid-template-columns: 32px minmax(0, 1fr) 32px; gap: 5px; }
            .gallery-arrow { width: 31px; height: 40px; }
            .gallery-dialog { padding: 10px; }
            .pager { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>
<body>
    <a class="skip-link" href="#main">Langsung ke konten</a>

    <header class="site-header">
        <a class="brand" href="<?= mteEscape($portalPath) ?>" style="text-decoration:none;color:inherit">
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
                <h1>Informasi akademik<br><span>Magister Teknik Elektro.</span></h1>
                <p>Menyajikan informasi VMTS, CPL, PPM, Pengumuman, Agenda dan Berita</p>
            </div>
            <div class="hero-index vision-index">
                <span class="mini-label"><?php mteIcon($assetPath, 'vmts'); ?> VISI PROGRAM STUDI</span>
                <blockquote>Menjadi program studi Magister Teknik Elektro yang unggul di tingkat internasional dalam pengembangan teknologi dan keilmuan Teknik Elektro untuk mendukung pengembangan sumber daya lokal.</blockquote>
            </div>
        </section>

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
                    <?php else: ?>
                        <p>Berita, pengumuman, dan agenda program studi dengan filter kategori.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="portal-layout">
                <nav class="portal-outline" aria-label="Menu portal MTE dan situs Jurusan Teknik Elektro">
                    <div class="outline-section">INFORMASI PRODI</div>
                    <?php foreach ($bagian as $kode => [$label, $judul, $deskripsi]): ?>
                        <a href="<?= mteEscape($portalPath . rawurlencode($kode)) ?>/#isi"<?= $aktif === $kode ? ' aria-current="page"' : '' ?>>
                            <span class="menu-icon"><?php mteIcon($assetPath, $kode); ?></span>
                            <span><strong><?= mteEscape($label) ?></strong><small><?= mteEscape($deskripsi) ?></small></span>
                        </a>
                    <?php endforeach; ?>
                    <div class="outline-section department-menu-label">INFORMASI JURUSAN</div>
                    <?php foreach ([
                        ['campus', '', 'Profil Jurusan', 'Identitas dan arah jurusan'],
                        ['dosen', '#/faculty', 'Dosen', 'Profil tenaga pengajar'],
                        ['riset', '#/research', 'Riset', 'Bidang dan kegiatan riset'],
                        ['kemahasiswaan', '#/student-life', 'Kemahasiswaan', 'Kegiatan dan kehidupan mahasiswa'],
                    ] as [$icon, $fragment, $label, $description]): ?>
                        <a href="<?= mteEscape($situsJurusan . $fragment) ?>" target="_blank" rel="noopener noreferrer">
                            <span class="menu-icon"><?php mteIcon($assetPath, $icon); ?></span>
                            <span><strong><?= mteEscape($label) ?></strong><small><?= mteEscape($description) ?></small></span>
                            <span class="menu-external"><?php mteIcon($assetPath, 'external'); ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>

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
                                    <header class="curriculum-semester-head">
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
            </div>
        </section>

        <footer class="page-footer">
            <span>© 2026 Jurusan Teknik Elektro UNSOED. Seluruh hak cipta dilindungi.</span>
            <span>Fakultas Teknik · Universitas Jenderal Soedirman</span>
        </footer>
    </main>
</body>
</html>
