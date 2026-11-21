<?php
declare(strict_types=1);

$requestedPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
if (is_string($requestedPath) && basename($requestedPath) === 'portal_lib.php') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/db.php';

function mte_portal_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function mte_portal_sections(): array
{
    return [
        'vmts' => 'VMTS',
        'cpl' => 'CPL Prodi',
        'ppm' => 'PPM Prodi',
        'kurikulum' => 'Kurikulum',
        'berita' => 'Berita',
        'dokumen' => 'Dokumen',
        'peminatan' => 'Peminatan',
    ];
}

function mte_portal_db(): mysqli
{
    return mte_db();
}

function mte_portal_stmt(string $sql, string $types = '', array $params = []): mysqli_stmt
{
    $connection = mte_portal_db();
    $statement = $connection->prepare($sql);
    if (!$statement) {
        throw new RuntimeException('Kueri database tidak dapat disiapkan.');
    }
    if ($types !== '' && !$statement->bind_param($types, ...$params)) {
        $statement->close();
        throw new RuntimeException('Parameter kueri tidak dapat dipasang.');
    }
    if (!$statement->execute()) {
        $statement->close();
        throw new RuntimeException('Kueri database gagal dijalankan.');
    }
    return $statement;
}

function mte_portal_rows(string $sql, string $types = '', array $params = []): array
{
    $statement = mte_portal_stmt($sql, $types, $params);
    $result = $statement->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $statement->close();
    return $rows;
}

function mte_portal_one(string $sql, string $types = '', array $params = []): ?array
{
    $rows = mte_portal_rows($sql, $types, $params);
    return $rows[0] ?? null;
}

function mte_portal_exec(string $sql, string $types = '', array $params = []): array
{
    $statement = mte_portal_stmt($sql, $types, $params);
    $result = ['id' => (int) mte_portal_db()->insert_id, 'affected' => $statement->affected_rows];
    $statement->close();
    return $result;
}

function mte_portal_install(): array
{
    $db = mte_portal_db();
    $db->query("CREATE TABLE IF NOT EXISTS mte_portal_users (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(80) COLLATE utf8mb4_bin NOT NULL UNIQUE,
        display_name VARCHAR(160) NOT NULL DEFAULT 'Administrator MTE',
        password_hash VARCHAR(255) NOT NULL,
        active TINYINT(1) NOT NULL DEFAULT 1,
        failed_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
        locked_until DATETIME NULL,
        password_changed_at DATETIME NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->query("CREATE TABLE IF NOT EXISTS mte_portal_content (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        section VARCHAR(24) NOT NULL,
        slug VARCHAR(140) NOT NULL,
        kind VARCHAR(40) NOT NULL DEFAULT '',
        label VARCHAR(120) NOT NULL DEFAULT '',
        code VARCHAR(30) NOT NULL DEFAULT '',
        publish_date DATE NULL,
        title VARCHAR(255) NOT NULL,
        summary TEXT NOT NULL,
        body LONGTEXT NOT NULL,
        file_name VARCHAR(255) NOT NULL DEFAULT '',
        sort_order INT UNSIGNED NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_mte_portal_section_slug (section, slug),
        INDEX idx_mte_portal_public (section, is_active, sort_order, id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->query("CREATE TABLE IF NOT EXISTS mte_portal_gallery (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        content_id INT UNSIGNED NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        alt_text VARCHAR(255) NOT NULL DEFAULT '',
        caption VARCHAR(255) NOT NULL DEFAULT '',
        sort_order INT UNSIGNED NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_mte_portal_gallery_content (content_id, sort_order, id),
        CONSTRAINT fk_mte_portal_gallery_content FOREIGN KEY (content_id) REFERENCES mte_portal_content(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $userCount = mte_portal_one('SELECT COUNT(*) AS jumlah FROM mte_portal_users');
    $adminCreated = false;
    if ((int) ($userCount['jumlah'] ?? 0) === 0) {
        $defaultHash = password_hash('admin', PASSWORD_DEFAULT);
        $statement = $db->prepare("INSERT INTO mte_portal_users (username, display_name, password_hash) VALUES ('admin', 'Administrator MTE', ?)");
        if (!$statement) {
            throw new RuntimeException('Tabel akun admin tidak dapat disiapkan.');
        }
        $statement->bind_param('s', $defaultHash);
        if (!$statement->execute()) {
            $statement->close();
            throw new RuntimeException('Akun awal admin tidak dapat dibuat.');
        }
        $adminCreated = $statement->affected_rows === 1;
        $statement->close();
    }

    $count = mte_portal_one('SELECT COUNT(*) AS jumlah FROM mte_portal_content');
    $seeded = 0;
    if ((int) ($count['jumlah'] ?? 0) === 0) {
        $seeded = mte_portal_seed_content();
    }
    $sampleNews = mte_portal_one("SELECT id FROM mte_portal_content WHERE section='berita' AND slug='seminar-riset' LIMIT 1");
    if ($sampleNews) {
        $galleryCount = mte_portal_one('SELECT COUNT(*) AS jumlah FROM mte_portal_gallery WHERE content_id=?', 'i', [(int) $sampleNews['id']]);
        if ((int) ($galleryCount['jumlah'] ?? 0) === 0) {
            mte_portal_seed_gallery((int) $sampleNews['id']);
        }
    }
    return ['akun_admin_disiapkan' => $adminCreated, 'konten_contoh_ditambahkan' => $seeded];
}

function mte_portal_seed_gallery(int $contentId): int
{
    $rows = [
        ['asset:galeri-mte-seminar-1.svg', 'Ilustrasi diskusi riset teknik elektro', 'Diskusi tim riset', 1],
        ['asset:galeri-mte-seminar-2.svg', 'Ilustrasi presentasi teknologi energi', 'Presentasi teknologi energi', 2],
        ['asset:galeri-mte-seminar-3.svg', 'Ilustrasi kolaborasi di laboratorium', 'Kolaborasi laboratorium', 3],
    ];
    $statement = mte_portal_db()->prepare('INSERT INTO mte_portal_gallery (content_id, file_name, alt_text, caption, sort_order) VALUES (?, ?, ?, ?, ?)');
    if (!$statement) {
        throw new RuntimeException('Galeri contoh tidak dapat disiapkan.');
    }
    $added = 0;
    foreach ($rows as $row) {
        [$file, $alt, $caption, $order] = $row;
        $statement->bind_param('isssi', $contentId, $file, $alt, $caption, $order);
        if (!$statement->execute()) {
            $statement->close();
            throw new RuntimeException('Galeri contoh tidak dapat disimpan.');
        }
        $added += $statement->affected_rows;
    }
    $statement->close();
    return $added;
}

function mte_portal_seed_content(): int
{
    $rows = [
        ['vmts','visi','VISI','','',null,'Arah pengembangan keilmuan','','Menjadi program magister teknik elektro yang unggul dalam pengembangan rekayasa dan teknologi untuk mendukung potensi wilayah.','',1],
        ['vmts','misi','MISI','','',null,'Pendidikan, riset, dan kemitraan','','Menyelenggarakan pendidikan magister, penelitian yang menjawab kebutuhan masyarakat, dan kerja sama dengan pemangku kepentingan.','',2],
        ['vmts','tujuan','TUJUAN','','',null,'Dampak bagi lulusan','','Menghasilkan lulusan yang mengembangkan keahlian, karya, dan kepemimpinan di bidang teknik elektro.','',3],
        ['vmts','strategi','STRATEGI','','',null,'Program dan indikator','','Meninjau kurikulum, memperluas kemitraan riset, dan mengevaluasi ketercapaian program secara berkala.','',4],
        ['cpl','cpl-01','','','01',null,'Solusi Energi Berkelanjutan','','Lulusan mampu menunjukkan pengetahuan mendalam tentang prinsip-prinsip teknik elektro dan penerapannya dalam mengembangkan solusi energi berkelanjutan.','',1],
        ['cpl','cpl-02','','','02',null,'Merancang Solusi Inovatif','','Lulusan mampu mengidentifikasi, menganalisis tantangan, dan menerapkan prinsip-prinsip teknik untuk merancang solusi inovatif.','',2],
        ['cpl','cpl-03','','','03',null,'Manajemen Sumber Daya','','Lulusan memiliki keterampilan untuk mengintegrasikan teknologi pintar, serta mempromosikan manajemen sumber daya yang efektif dan efisien.','',3],
        ['cpl','cpl-04','','','04',null,'Melakukan Penelitian','','Lulusan mampu menunjukkan kemampuan untuk melakukan penelitian yang membahas kesenjangan pemanfaatan teknologi elektro di masyarakat.','',4],
        ['cpl','cpl-05','','','05',null,'Praktik Rekayasa','','Lulusan mampu memahami dan menerapkan prinsip-prinsip etika dalam praktik rekayasa yang mendukung keadilan sosial dan keberlanjutan lingkungan.','',5],
        ['cpl','cpl-06','','','06',null,'Komunikasi dan Kolaborasi','','Lulusan mampu mengkomunikasikan informasi teknis secara efektif, untuk mendorong keterlibatan dan kolaborasi dalam inisiatif pembangunan.','',6],
        ['cpl','cpl-07','','','07',null,'Pembelajaran Seumur Hidup','','Lulusan mampu menyadari pentingnya pembelajaran seumur hidup dan pengembangan profesional, khususnya dalam beradaptasi dengan teknologi baru.','',7],
        ['cpl','cpl-08','','','08',null,'Merancang Sistem Modern','','Lulusan mampu menunjukkan kemahiran dalam menggunakan metode dan teknik modern untuk menganalisis dan merancang sistem pada masalah kompleks inter-multi disiplin.','',8],
        ['kurikulum','mte25111','MKU','1','MTE25111',null,'Komputasi Numeris','3','', '',1],
        ['kurikulum','mte25112','MKU','1','MTE25112',null,'Kecerdasan Buatan','3','', '',2],
        ['kurikulum','mte25113','MKU','1','MTE25113',null,'Internet of Things','3','', '',3],
        ['kurikulum','mte25114','MKU','1','MTE25114',null,'Etika Profesi','2','', '',4],
        ['kurikulum','mte25115','MKU','1','MTE25115',null,'Energi Berkelanjutan','3','', '',5],
        ['ppm','perekayasa-profesional','','01','',null,'Perekayasa profesional','','Dalam beberapa tahun setelah lulus, mampu mengembangkan solusi rekayasa elektro sesuai kebutuhan bidang kerjanya.','',1],
        ['ppm','peneliti-pengembang','','02','',null,'Peneliti dan pengembang','','Mampu memimpin atau berkontribusi pada penelitian dan pengembangan teknologi yang bermanfaat.','',2],
        ['ppm','pemimpin-pembelajar','','03','',null,'Pemimpin dan pembelajar','','Mampu memimpin kolaborasi profesional dan mengembangkan kompetensi secara berkelanjutan.','',3],
        ['berita','seminar-riset','agenda','','', '2026-10-12','Contoh: Seminar riset teknologi elektro','Program studi menyelenggarakan forum diskusi untuk mempertemukan gagasan riset dosen, mahasiswa, dan mitra.','Dalam contoh agenda ini, dosen, mahasiswa, dan mitra berbagi perkembangan penelitian, mendiskusikan tantangan di lapangan, serta menyusun peluang kolaborasi lanjutan. Narasi ini merupakan teks dummy untuk menunjukkan tampilan halaman berita.','',1],
        ['berita','kuliah-tamu','agenda','','', '2026-09-24','Contoh: Kuliah tamu bidang energi','Praktisi dan akademisi berbagi wawasan mengenai perkembangan rekayasa sistem tenaga dan energi.','Kuliah tamu ini memberi ruang bagi mahasiswa untuk mengenal persoalan profesi, pendekatan rekayasa mutakhir, dan topik penelitian bidang energi. Narasi ini merupakan teks dummy untuk halaman berita prodi.','',2],
        ['berita','lokakarya-proposal','pengumuman','','', '2026-08-15','Contoh: Lokakarya proposal penelitian','Mahasiswa mengikuti pendampingan penyusunan proposal, metodologi, dan rencana publikasi ilmiah.','Peserta meninjau rumusan masalah, metodologi, dan rencana luaran penelitian bersama dosen pembimbing. Isi ini disiapkan sebagai contoh tata letak berita.','',3],
        ['berita','kerja-sama-riset','berita','','', '2026-06-08','Contoh: Kerja sama penelitian','Pertemuan penjajakan memetakan peluang kolaborasi penelitian bersama mitra.','Pertemuan membahas peluang kolaborasi penelitian dan kebutuhan mitra. Seluruh naskah ini adalah konten dummy untuk demonstrasi portal.','',4],
        ['berita','kemajuan-riset','berita','','', '2026-04-18','Contoh: Presentasi kemajuan riset','Mahasiswa memaparkan kemajuan penelitian dan memperoleh masukan dari komunitas akademik.','Mahasiswa membagikan progres, temuan sementara, dan rencana lanjutan penelitian. Narasi ini hanya contoh konten halaman detail.','',5],
        ['berita','diskusi-kurikulum','agenda','','', '2026-02-10','Contoh: Diskusi kurikulum prodi','Forum pemangku kepentingan membahas relevansi pembelajaran dengan perkembangan keilmuan.','Pemangku kepentingan berdiskusi tentang perkembangan keilmuan dan keterkaitannya dengan proses pembelajaran. Narasi ini merupakan contoh untuk pratinjau portal.','',6],
        ['dokumen','pedoman-akademik','PDF','','',null,'Contoh: Pedoman Akademik MTE','Contoh informasi mengenai ketentuan akademik dan tahapan studi mahasiswa.','Dokumen ini dapat memuat ringkasan aturan akademik, tahapan perkuliahan, evaluasi studi, serta layanan akademik yang tersedia bagi mahasiswa. Narasi ini masih berupa contoh dan dapat diganti dengan keterangan resmi.','contoh.pdf',1],
        ['dokumen','panduan-penyusunan-tesis','PDF','','',null,'Contoh: Panduan Penyusunan Tesis','Contoh panduan struktur tesis, format penulisan, sitasi, dan penyerahan naskah.','Panduan ini dapat menjelaskan susunan naskah tesis, format penulisan, tata cara sitasi, pemeriksaan kemiripan, dan proses penyerahan naskah akhir. Silakan sesuaikan narasi dan berkas dengan pedoman yang berlaku di program studi.','contoh.pdf',2],
        ['dokumen','seminar-proposal','PDF','','',null,'Contoh: Panduan Seminar Proposal','Contoh alur pendaftaran, persyaratan, dan pelaksanaan seminar proposal penelitian.','Informasi detail dapat memuat persyaratan pendaftaran, dokumen yang perlu disiapkan, proses penjadwalan, serta tata tertib seminar proposal. Konten ini merupakan contoh awal untuk pengelolaan dokumen prodi.','contoh.pdf',3],
        ['dokumen','seminar-hasil','PDF','','',null,'Contoh: Panduan Seminar Hasil','Contoh persiapan dan ketentuan seminar hasil penelitian mahasiswa.','Halaman ini dapat merangkum syarat seminar hasil, kelengkapan administrasi, susunan presentasi, dan mekanisme pencatatan masukan penguji. Ganti uraian dan berkas contoh ini dengan ketentuan resmi program studi.','contoh.pdf',4],
        ['dokumen','ujian-tesis','PDF','','',null,'Contoh: Panduan Ujian Tesis','Contoh persyaratan administrasi dan tahapan pelaksanaan ujian tesis.','Detail dokumen dapat menerangkan persyaratan sebelum ujian, pengajuan jadwal, komposisi tim penguji, serta tindak lanjut setelah ujian. Narasi ini disiapkan sebagai dummy untuk contoh halaman detail.','contoh.pdf',5],
        ['dokumen','kalender-akademik','PDF','','',null,'Contoh: Kalender Akademik Program Studi','Contoh ringkasan jadwal penting kegiatan akademik dalam satu periode.','Kalender akademik dapat memuat periode registrasi, perkuliahan, pengajuan penelitian, seminar, dan kegiatan evaluasi. Perbarui isi dan berkas sesuai kalender akademik yang telah ditetapkan.','contoh.pdf',6],
        ['peminatan','energi-listrik-cerdas','','','',null,'Energi Listrik Cerdas (ELC)','','Pendalaman teknologi energi, sistem tenaga, jaringan cerdas, dan integrasi energi berkelanjutan.','',1],
        ['peminatan','teknologi-informasi-biomedis','','','',null,'Teknologi Informasi dan Biomedis (TIB)','','Pendalaman rekayasa teknologi informasi, sistem komputasi, instrumentasi, dan aplikasi biomedis.','',2],
    ];

    $sql = 'INSERT INTO mte_portal_content (section, slug, kind, label, code, publish_date, title, summary, body, file_name, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $statement = mte_portal_db()->prepare($sql);
    if (!$statement) {
        throw new RuntimeException('Konten awal tidak dapat disiapkan.');
    }
    $added = 0;
    foreach ($rows as $row) {
        [$section, $slug, $kind, $label, $code, $date, $title, $summary, $body, $fileName, $order] = $row;
        $statement->bind_param('ssssssssssi', $section, $slug, $kind, $label, $code, $date, $title, $summary, $body, $fileName, $order);
        if (!$statement->execute()) {
            $statement->close();
            throw new RuntimeException('Konten contoh tidak dapat disimpan.');
        }
        $added += $statement->affected_rows;
    }
    $statement->close();
    return $added;
}

function mte_portal_public_content(): array
{
    return mte_portal_rows('SELECT * FROM mte_portal_content WHERE is_active=1 ORDER BY section, sort_order, id');
}

function mte_portal_content_list(string $section): array
{
    if (!array_key_exists($section, mte_portal_sections())) {
        throw new InvalidArgumentException('Menu konten tidak dikenal.');
    }
    return mte_portal_rows('SELECT * FROM mte_portal_content WHERE section=? ORDER BY sort_order, id', 's', [$section]);
}

function mte_portal_content_by_id(int $id): ?array
{
    return $id > 0 ? mte_portal_one('SELECT * FROM mte_portal_content WHERE id=?', 'i', [$id]) : null;
}

function mte_portal_gallery_for_content(int $contentId): array
{
    return $contentId > 0
        ? mte_portal_rows('SELECT * FROM mte_portal_gallery WHERE content_id=? ORDER BY sort_order, id', 'i', [$contentId])
        : [];
}

function mte_portal_slug(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    if (function_exists('iconv')) {
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($converted !== false) {
            $value = $converted;
        }
    }
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

function mte_portal_text($value, int $max, string $label, bool $required = false): string
{
    if (!is_scalar($value) && $value !== null) {
        throw new InvalidArgumentException($label . ' tidak valid.');
    }
    $value = trim((string) $value);
    $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    if ($length > $max) {
        throw new InvalidArgumentException($label . ' terlalu panjang.');
    }
    if ($required && $value === '') {
        throw new InvalidArgumentException($label . ' wajib diisi.');
    }
    return $value;
}

function mte_portal_safe_href(string $href): string
{
    $href = trim($href);
    if ($href === '' || preg_match('/[\x00-\x20]/', $href)) {
        return '';
    }
    if (preg_match('/^(https?:\/\/|mailto:|tel:)/i', $href)) {
        return $href;
    }
    if ($href[0] === '#' || ($href[0] === '/' && substr($href, 0, 2) !== '//')) {
        return $href;
    }
    return '';
}

function mte_portal_clean_html_children(DOMNode $parent, DOMDocument $document, bool $forStorage): void
{
    $allowed = ['p', 'div', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'a', 'img', 'h2', 'h3', 'blockquote', 'table', 'thead', 'tbody', 'tr', 'th', 'td'];
    $removeWithContents = ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'form', 'input', 'video', 'audio'];
    $children = [];
    foreach ($parent->childNodes as $child) {
        $children[] = $child;
    }
    foreach ($children as $child) {
        if ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
            $parent->removeChild($child);
            continue;
        }
        if ($child->nodeType !== XML_ELEMENT_NODE) {
            continue;
        }
        $tag = strtolower($child->nodeName);
        if (in_array($tag, $removeWithContents, true)) {
            $parent->removeChild($child);
            continue;
        }
        mte_portal_clean_html_children($child, $document, $forStorage);
        if (!in_array($tag, $allowed, true)) {
            while ($child->firstChild) {
                $parent->insertBefore($child->firstChild, $child);
            }
            $parent->removeChild($child);
            continue;
        }
        $originalHref = (string) $child->getAttribute('href');
        $originalSource = (string) $child->getAttribute('src');
        $originalAlt = (string) $child->getAttribute('alt');
        $originalClass = (string) $child->getAttribute('class');
        for ($index = $child->attributes->length - 1; $index >= 0; $index--) {
            $attribute = $child->attributes->item($index);
            if ($attribute) {
                $child->removeAttributeNode($attribute);
            }
        }
        if ($tag === 'a') {
            $href = mte_portal_safe_href($originalHref);
            if ($href !== '') {
                $child->setAttribute('href', $href);
                if (preg_match('/^https?:\/\//i', $href)) {
                    $child->setAttribute('target', '_blank');
                    $child->setAttribute('rel', 'noopener noreferrer');
                }
            }
        } elseif ($tag === 'img') {
            $source = trim($originalSource);
            $relative = '';
            if (preg_match('#^mte-upload:(konten/[a-f0-9]{32}\.(?:jpg|png|webp))$#D', $source, $match)) {
                $relative = $match[1];
            } else {
                $path = parse_url($source, PHP_URL_PATH);
                $prefix = mte_portal_base_path() . '/unggah/';
                if (is_string($path) && strpos($path, $prefix . 'konten/') === 0) {
                    $candidate = substr($path, strlen($prefix));
                    if (preg_match('#^konten/[a-f0-9]{32}\.(?:jpg|png|webp)$#D', $candidate)) {
                        $relative = $candidate;
                    }
                }
            }
            $imagePath = $relative !== '' ? __DIR__ . '/unggah/' . $relative : '';
            if ($relative === '' || !is_file($imagePath)) {
                $parent->removeChild($child);
                continue;
            }
            $child->setAttribute('src', $forStorage ? 'mte-upload:' . $relative : mte_portal_url('/unggah/' . $relative));
            $child->setAttribute('alt', mte_portal_text($originalAlt, 255, 'Teks alternatif gambar'));
            $child->setAttribute('loading', 'lazy');
        } elseif ($tag === 'div' && $originalClass === 'rich-table-scroll') {
            $child->setAttribute('class', 'rich-table-scroll');
        }
    }
}

function mte_portal_sanitize_html(string $html, bool $forStorage = false): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }
    $hasFormatting = preg_match('/<\/?(?:p|div|br|strong|b|em|i|u|ul|ol|li|a|img|h2|h3|blockquote|table|thead|tbody|tr|th|td)\b/i', $html) === 1;
    if (!$hasFormatting) {
        return '<p>' . nl2br(mte_portal_escape($html), false) . '</p>';
    }
    if (!class_exists('DOMDocument')) {
        return '<p>' . nl2br(mte_portal_escape(strip_tags($html)), false) . '</p>';
    }
    $document = new DOMDocument('1.0', 'UTF-8');
    $previousErrors = libxml_use_internal_errors(true);
    $loaded = $document->loadHTML('<?xml encoding="UTF-8"><html><body><div id="mte-rich-root">' . $html . '</div></body></html>', LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previousErrors);
    if (!$loaded) {
        return '<p>' . nl2br(mte_portal_escape(strip_tags($html)), false) . '</p>';
    }
    $roots = (new DOMXPath($document))->query('//*[@id="mte-rich-root"]');
    $root = $roots ? $roots->item(0) : null;
    if (!$root) {
        return '<p>' . nl2br(mte_portal_escape(strip_tags($html)), false) . '</p>';
    }
    mte_portal_clean_html_children($root, $document, $forStorage);
    $output = '';
    foreach ($root->childNodes as $child) {
        $output .= $document->saveHTML($child);
    }
    return $output;
}

function mte_portal_inline_images(string $html): array
{
    preg_match_all('#mte-upload:(konten/[a-f0-9]{32}\.(?:jpg|png|webp))#', $html, $matches);
    return array_values(array_unique($matches[1] ?? []));
}

function mte_portal_validate_content(string $section, array $input): array
{
    if (!array_key_exists($section, mte_portal_sections())) {
        throw new InvalidArgumentException('Menu konten tidak dikenal.');
    }
    $title = mte_portal_text($input['title'] ?? '', 255, 'Judul', true);
    if ($section === 'cpl') {
        $title = function_exists('mb_convert_case')
            ? mb_convert_case($title, MB_CASE_TITLE, 'UTF-8')
            : ucwords(strtolower($title));
    }
    $code = mte_portal_text($input['code'] ?? '', 30, $section === 'kurikulum' ? 'Kode mata kuliah' : 'Kode', $section === 'kurikulum');
    $kind = mte_portal_text($input['kind'] ?? '', 40, $section === 'kurikulum' ? 'Kelompok mata kuliah' : 'Jenis', $section === 'kurikulum');
    $label = mte_portal_text($input['label'] ?? '', 120, $section === 'kurikulum' ? 'Semester' : 'Label', $section === 'kurikulum');
    $summary = mte_portal_text($input['summary'] ?? '', 5000, 'Ringkasan');
    if ($section === 'kurikulum') {
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{1,29}$/D', $code)) {
            throw new InvalidArgumentException('Kode mata kuliah hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.');
        }
        if (!preg_match('/^[1-8]$/D', $label)) {
            throw new InvalidArgumentException('Semester harus berupa angka dari 1 sampai 8.');
        }
        $credits = mte_portal_text($input['summary'] ?? '', 2, 'SKS', true);
        if (!preg_match('/^(?:[1-9]|10)$/D', $credits)) {
            throw new InvalidArgumentException('SKS harus berupa angka dari 1 sampai 10.');
        }
        $summary = (string) (int) $credits;
    }
    $slug = mte_portal_slug(mte_portal_text($input['slug'] ?? '', 140, 'Alamat konten'));
    if ($slug === '') {
        $slug = mte_portal_slug($section === 'kurikulum' ? $code : $title);
    }
    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)) {
        throw new InvalidArgumentException('Alamat konten harus terdiri dari huruf kecil, angka, dan tanda hubung.');
    }
    if ($section === 'berita' && !in_array($kind, ['berita', 'pengumuman', 'agenda'], true)) {
        throw new InvalidArgumentException('Pilih jenis informasi yang valid.');
    }
    if ($section === 'dokumen') {
        $kind = 'PDF';
    }
    $date = null;
    if ($section === 'berita') {
        $dateInput = mte_portal_text($input['publish_date'] ?? '', 10, 'Tanggal');
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $dateInput);
        if (!$parsed || $parsed->format('Y-m-d') !== $dateInput) {
            throw new InvalidArgumentException('Isi tanggal berita dengan format yang valid.');
        }
        $date = $dateInput;
    }
    $orderRaw = $input['sort_order'] ?? 0;
    if (!is_scalar($orderRaw) || !preg_match('/^\d{1,6}$/D', (string) $orderRaw)) {
        throw new InvalidArgumentException('Urutan konten harus berupa angka.');
    }
    return [
        'slug' => $slug,
        'kind' => $kind,
        'label' => $label,
        'code' => $code,
        'publish_date' => $date,
        'title' => $title,
        'summary' => $summary,
        'body' => mte_portal_sanitize_html(mte_portal_text($input['body'] ?? '', 30000, 'Detail'), true),
        'sort_order' => (int) $orderRaw,
        'is_active' => isset($input['is_active']) && (string) $input['is_active'] === '1' ? 1 : 0,
    ];
}

function mte_portal_save_content(string $section, int $id, array $input, ?string $uploadedFile): array
{
    $data = mte_portal_validate_content($section, $input);
    $existing = $id > 0 ? mte_portal_content_by_id($id) : null;
    if ($id > 0 && (!$existing || $existing['section'] !== $section)) {
        throw new InvalidArgumentException('Konten yang akan diubah tidak ditemukan.');
    }
    $fileName = $section === 'dokumen' ? (string) ($uploadedFile ?? ($existing['file_name'] ?? '')) : '';
    if ($section === 'dokumen' && $fileName === '') {
        throw new InvalidArgumentException('Unggah berkas PDF untuk dokumen baru.');
    }
    $oldFile = $existing['file_name'] ?? '';
    if ($id > 0) {
        $sql = 'UPDATE mte_portal_content SET slug=?, kind=?, label=?, code=?, publish_date=?, title=?, summary=?, body=?, file_name=?, sort_order=?, is_active=? WHERE id=? AND section=?';
        mte_portal_exec($sql, 'sssssssssiiis', [
            $data['slug'], $data['kind'], $data['label'], $data['code'], $data['publish_date'],
            $data['title'], $data['summary'], $data['body'], $fileName, $data['sort_order'],
            $data['is_active'], $id, $section,
        ]);
    } else {
        $sql = 'INSERT INTO mte_portal_content (section, slug, kind, label, code, publish_date, title, summary, body, file_name, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $result = mte_portal_exec($sql, 'ssssssssssii', [
            $section, $data['slug'], $data['kind'], $data['label'], $data['code'], $data['publish_date'],
            $data['title'], $data['summary'], $data['body'], $fileName, $data['sort_order'], $data['is_active'],
        ]);
        $id = $result['id'];
    }
    return ['id' => $id, 'old_file' => $oldFile, 'old_body' => (string) ($existing['body'] ?? ''), 'file_name' => $fileName];
}

function mte_portal_store_pdf(array $upload): ?string
{
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !isset($upload['tmp_name']) || !is_uploaded_file($upload['tmp_name'])) {
        throw new InvalidArgumentException('Unggah PDF gagal. Coba pilih berkas kembali.');
    }
    if ((int) ($upload['size'] ?? 0) < 8 || (int) $upload['size'] > 5 * 1024 * 1024) {
        throw new InvalidArgumentException('Ukuran PDF harus kurang dari 5 MB.');
    }
    $extension = strtolower(pathinfo((string) ($upload['name'] ?? ''), PATHINFO_EXTENSION));
    if ($extension !== 'pdf') {
        throw new InvalidArgumentException('Hanya berkas PDF yang dapat diunggah.');
    }
    $signature = file_get_contents($upload['tmp_name'], false, null, 0, 5);
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if ($signature !== '%PDF-' || $finfo->file($upload['tmp_name']) !== 'application/pdf') {
        throw new InvalidArgumentException('Isi berkas tidak dikenali sebagai PDF.');
    }
    $directory = __DIR__ . '/unggah/dokumen';
    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
        throw new RuntimeException('Folder dokumen belum dapat disiapkan.');
    }
    $name = bin2hex(random_bytes(16)) . '.pdf';
    if (!move_uploaded_file($upload['tmp_name'], $directory . '/' . $name)) {
        throw new RuntimeException('PDF tidak dapat disimpan.');
    }
    return 'dokumen/' . $name;
}

function mte_portal_store_gallery_image(array $upload): ?string
{
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !isset($upload['tmp_name']) || !is_uploaded_file($upload['tmp_name'])) {
        throw new InvalidArgumentException('Salah satu gambar galeri gagal diunggah.');
    }
    if ((int) ($upload['size'] ?? 0) < 8 || (int) $upload['size'] > 5 * 1024 * 1024) {
        throw new InvalidArgumentException('Ukuran setiap gambar galeri harus kurang dari 5 MB.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($upload['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime]) || !getimagesize($upload['tmp_name'])) {
        throw new InvalidArgumentException('Galeri hanya menerima gambar JPG, PNG, atau WebP yang valid.');
    }
    $directory = __DIR__ . '/unggah/galeri';
    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
        throw new RuntimeException('Folder galeri belum dapat disiapkan.');
    }
    $name = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($upload['tmp_name'], $directory . '/' . $name)) {
        throw new RuntimeException('Gambar galeri tidak dapat disimpan.');
    }
    return 'galeri/' . $name;
}

function mte_portal_store_inline_image(array $upload): string
{
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !isset($upload['tmp_name']) || !is_uploaded_file($upload['tmp_name'])) {
        throw new InvalidArgumentException('Pilih gambar yang akan disisipkan.');
    }
    if ((int) ($upload['size'] ?? 0) < 8 || (int) $upload['size'] > 5 * 1024 * 1024) {
        throw new InvalidArgumentException('Ukuran gambar harus kurang dari 5 MB.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime]) || !getimagesize($upload['tmp_name'])) {
        throw new InvalidArgumentException('Gambar hanya menerima JPG, PNG, atau WebP yang valid.');
    }
    $directory = __DIR__ . '/unggah/konten';
    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
        throw new RuntimeException('Folder gambar konten belum dapat disiapkan.');
    }
    $name = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($upload['tmp_name'], $directory . '/' . $name)) {
        throw new RuntimeException('Gambar tidak dapat disimpan.');
    }
    return 'konten/' . $name;
}

function mte_portal_remove_inline_image_if_unused(string $relativeName): void
{
    if (!preg_match('#^konten/[a-f0-9]{32}\.(?:jpg|png|webp)$#D', $relativeName)) {
        return;
    }
    $pattern = '%mte-upload:' . $relativeName . '%';
    $used = mte_portal_one('SELECT id FROM mte_portal_content WHERE body LIKE ? LIMIT 1', 's', [$pattern]);
    if ($used) {
        return;
    }
    $path = __DIR__ . '/unggah/' . $relativeName;
    if (is_file($path)) {
        unlink($path);
    }
}

function mte_portal_remove_gallery_file_if_unused(string $relativeName): void
{
    if ($relativeName === '' || strpos($relativeName, 'asset:') === 0 || !preg_match('#^galeri/[a-zA-Z0-9_-]+\.(?:jpg|png|webp)$#D', $relativeName)) {
        return;
    }
    $used = mte_portal_one('SELECT id FROM mte_portal_gallery WHERE file_name=? LIMIT 1', 's', [$relativeName]);
    if ($used) {
        return;
    }
    $path = __DIR__ . '/unggah/' . $relativeName;
    if (is_file($path)) {
        unlink($path);
    }
}

function mte_portal_save_gallery(int $contentId, array $input, array $uploads): void
{
    $existing = mte_portal_gallery_for_content($contentId);
    $existingById = [];
    foreach ($existing as $item) {
        $existingById[(int) $item['id']] = $item;
    }
    $deleteIds = $input['delete_gallery'] ?? [];
    if (!is_array($deleteIds)) {
        $deleteIds = [];
    }
    $deleteSet = [];
    foreach ($deleteIds as $deleteId) {
        $normalizedId = filter_var($deleteId, FILTER_VALIDATE_INT);
        if ($normalizedId && isset($existingById[(int) $normalizedId])) {
            $deleteSet[(int) $normalizedId] = true;
        }
    }
    $captions = isset($input['gallery_caption']) && is_array($input['gallery_caption']) ? $input['gallery_caption'] : [];
    $alts = isset($input['gallery_alt']) && is_array($input['gallery_alt']) ? $input['gallery_alt'] : [];
    foreach ($existing as $item) {
        $itemId = (int) $item['id'];
        if (isset($deleteSet[$itemId])) {
            mte_portal_exec('DELETE FROM mte_portal_gallery WHERE id=? AND content_id=?', 'ii', [$itemId, $contentId]);
            mte_portal_remove_gallery_file_if_unused((string) $item['file_name']);
            continue;
        }
        $caption = mte_portal_text($captions[$itemId] ?? $item['caption'], 255, 'Keterangan foto');
        $alt = mte_portal_text($alts[$itemId] ?? $item['alt_text'], 255, 'Teks alternatif foto');
        mte_portal_exec('UPDATE mte_portal_gallery SET caption=?, alt_text=? WHERE id=? AND content_id=?', 'ssii', [$caption, $alt, $itemId, $contentId]);
    }
    if (empty($uploads['name']) || !is_array($uploads['name'])) {
        return;
    }
    $maxOrder = mte_portal_one('SELECT COALESCE(MAX(sort_order),0) AS posisi FROM mte_portal_gallery WHERE content_id=?', 'i', [$contentId]);
    $order = (int) ($maxOrder['posisi'] ?? 0);
    foreach ($uploads['name'] as $index => $originalName) {
        $upload = [
            'name' => $originalName,
            'type' => $uploads['type'][$index] ?? '',
            'tmp_name' => $uploads['tmp_name'][$index] ?? '',
            'error' => $uploads['error'][$index] ?? UPLOAD_ERR_NO_FILE,
            'size' => $uploads['size'][$index] ?? 0,
        ];
        $fileName = mte_portal_store_gallery_image($upload);
        if ($fileName === null) {
            continue;
        }
        $baseCaption = pathinfo((string) $originalName, PATHINFO_FILENAME);
        $caption = mte_portal_text($baseCaption, 255, 'Nama foto');
        $alt = mte_portal_text('Dokumentasi ' . $caption, 255, 'Teks alternatif foto');
        $order++;
        mte_portal_exec('INSERT INTO mte_portal_gallery (content_id, file_name, alt_text, caption, sort_order) VALUES (?, ?, ?, ?, ?)', 'isssi', [$contentId, $fileName, $alt, $caption, $order]);
    }
}

function mte_portal_remove_file_if_unused(string $relativeName): void
{
    if ($relativeName === '' || $relativeName === 'contoh.pdf') {
        return;
    }
    if (!preg_match('#^(?:dokumen/)?[a-zA-Z0-9_-]+\.pdf$#D', $relativeName)) {
        return;
    }
    $used = mte_portal_one('SELECT id FROM mte_portal_content WHERE section=\'dokumen\' AND file_name=? LIMIT 1', 's', [$relativeName]);
    if ($used) {
        return;
    }
    $path = __DIR__ . '/unggah/' . $relativeName;
    if (is_file($path)) {
        unlink($path);
    }
}

function mte_portal_delete_content(int $id, string $section): ?array
{
    $existing = mte_portal_content_by_id($id);
    if (!$existing || $existing['section'] !== $section) {
        return null;
    }
    $existing['gallery_files'] = mte_portal_gallery_for_content($id);
    $existing['inline_files'] = mte_portal_inline_images((string) $existing['body']);
    mte_portal_exec('DELETE FROM mte_portal_content WHERE id=? AND section=?', 'is', [$id, $section]);
    return $existing;
}

function mte_portal_base_path(): string
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $path = rtrim(str_replace('\\', '/', dirname($script)), '/');
    return ($path === '.' || $path === '/') ? '' : $path;
}

function mte_portal_url(string $path): string
{
    return mte_portal_base_path() . '/' . ltrim($path, '/');
}

function mte_portal_gallery_url(string $fileName): string
{
    if (strpos($fileName, 'asset:') === 0) {
        return mte_portal_url('/simulasi_assets/' . rawurlencode(substr($fileName, 6)));
    }
    if (strpos($fileName, 'galeri/') === 0) {
        $encoded = implode('/', array_map('rawurlencode', explode('/', $fileName)));
        return mte_portal_url('/unggah/' . $encoded);
    }
    return mte_portal_url('/simulasi_assets/' . rawurlencode($fileName));
}

function mte_portal_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('MTEPORTALSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => mte_portal_base_path() . '/',
        'secure' => !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function mte_portal_csrf_token(): string
{
    mte_portal_session_start();
    if (empty($_SESSION['mte_portal_csrf'])) {
        $_SESSION['mte_portal_csrf'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['mte_portal_csrf'];
}

function mte_portal_require_csrf(): void
{
    mte_portal_session_start();
    $provided = $_POST['csrf_token'] ?? '';
    if (!is_string($provided) || empty($_SESSION['mte_portal_csrf']) || !hash_equals((string) $_SESSION['mte_portal_csrf'], $provided)) {
        http_response_code(403);
        throw new RuntimeException('Sesi formulir berakhir. Muat ulang halaman lalu coba lagi.');
    }
}

function mte_portal_login(string $username, string $password): bool
{
    $username = strtolower(trim($username));
    $user = mte_portal_one('SELECT id, username, password_hash, active, failed_attempts, locked_until FROM mte_portal_users WHERE username=? LIMIT 1', 's', [$username]);
    if (!$user || (int) $user['active'] !== 1) {
        return false;
    }
    if (!empty($user['locked_until'])) {
        if (strtotime($user['locked_until']) > time()) {
            return false;
        }
        mte_portal_exec('UPDATE mte_portal_users SET failed_attempts=0, locked_until=NULL WHERE id=?', 'i', [(int) $user['id']]);
        $user['failed_attempts'] = 0;
        $user['locked_until'] = null;
    }
    if (!password_verify($password, $user['password_hash'])) {
        mte_portal_exec('UPDATE mte_portal_users SET locked_until=CASE WHEN failed_attempts>=4 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE) ELSE locked_until END, failed_attempts=LEAST(failed_attempts+1, 5) WHERE id=?', 'i', [(int) $user['id']]);
        return false;
    }
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        mte_portal_exec('UPDATE mte_portal_users SET password_hash=? WHERE id=?', 'si', [$hash, (int) $user['id']]);
    }
    mte_portal_exec('UPDATE mte_portal_users SET failed_attempts=0, locked_until=NULL WHERE id=?', 'i', [(int) $user['id']]);
    mte_portal_session_start();
    session_regenerate_id(true);
    $_SESSION['mte_portal_user_id'] = (int) $user['id'];
    $_SESSION['mte_portal_username'] = $user['username'];
    $_SESSION['mte_portal_csrf'] = bin2hex(random_bytes(32));
    return true;
}

function mte_portal_current_user(): ?array
{
    mte_portal_session_start();
    $id = filter_var($_SESSION['mte_portal_user_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        return null;
    }
    $user = mte_portal_one('SELECT id, username, display_name, password_changed_at FROM mte_portal_users WHERE id=? AND active=1', 'i', [(int) $id]);
    if (!$user) {
        unset($_SESSION['mte_portal_user_id'], $_SESSION['mte_portal_username']);
    }
    return $user;
}

function mte_portal_require_admin(string $tool = ''): array
{
    $user = mte_portal_current_user();
    if (!$user) {
        $loginPath = '/masuk' . ($tool !== '' ? '?lanjut=' . rawurlencode($tool) : '');
        header('Location: ' . mte_portal_url($loginPath));
        exit;
    }
    return $user;
}

function mte_portal_change_credentials(int $userId, string $currentPassword, string $username, string $newPassword): void
{
    $username = strtolower(trim($username));
    if (!preg_match('/^[a-z0-9._-]{3,80}$/D', $username)) {
        throw new InvalidArgumentException('Username harus 3–80 karakter dan hanya berisi huruf, angka, titik, garis bawah, atau tanda hubung.');
    }
    if (strlen($newPassword) < 10 || strlen($newPassword) > 200) {
        throw new InvalidArgumentException('Password baru harus berisi 10–200 karakter.');
    }
    $user = mte_portal_one('SELECT password_hash FROM mte_portal_users WHERE id=? AND active=1', 'i', [$userId]);
    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        throw new InvalidArgumentException('Password saat ini tidak cocok.');
    }
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    mte_portal_exec('UPDATE mte_portal_users SET username=?, password_hash=?, password_changed_at=NOW(), failed_attempts=0, locked_until=NULL WHERE id=?', 'ssi', [$username, $hash, $userId]);
    mte_portal_session_start();
    session_regenerate_id(true);
    $_SESSION['mte_portal_username'] = $username;
}

function mte_portal_logout(): void
{
    mte_portal_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}
