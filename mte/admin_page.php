<?php
if (!defined('MTE_PORTAL_ROUTED')) {
    http_response_code(404);
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'none'; script-src 'self'; style-src 'self'; img-src 'self' data:; connect-src 'self'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");

$adminUser = mte_portal_require_admin();
$adminSections = mte_portal_sections();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'unggah_gambar') {
    header('Content-Type: application/json; charset=UTF-8');
    try {
        mte_portal_require_csrf();
        $relativeImage = mte_portal_store_inline_image($_FILES['gambar'] ?? []);
        echo json_encode(['ok' => true, 'url' => mte_portal_url('/unggah/' . $relativeImage)], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    } catch (Throwable $error) {
        http_response_code(422);
        $message = $error instanceof InvalidArgumentException || $error instanceof RuntimeException ? $error->getMessage() : 'Gambar belum dapat diunggah.';
        echo json_encode(['ok' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
    }
    exit;
}
$menuParam = $_GET['menu'] ?? 'vmts';
$adminMenu = $menuParam === 'akun' ? 'akun' : (is_string($menuParam) && isset($adminSections[$menuParam]) ? $menuParam : 'vmts');
$pesanAdmin = '';
$pesanJenis = 'error';
$fileBaru = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        mte_portal_require_csrf();
        $action = $_POST['action'] ?? '';
        if (!is_string($action)) {
            throw new InvalidArgumentException('Permintaan tidak valid.');
        }
        if ($action === 'logout') {
            mte_portal_logout();
            header('Location: ' . mte_portal_url('/masuk'));
            exit;
        }
        if ($action === 'ubah_akun') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newUsername = $_POST['new_username'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            if (!is_string($currentPassword) || !is_string($newUsername) || !is_string($newPassword) || !is_string($confirmPassword)) {
                throw new InvalidArgumentException('Isi data akun dengan benar.');
            }
            if (!hash_equals($newPassword, $confirmPassword)) {
                throw new InvalidArgumentException('Konfirmasi password baru belum cocok.');
            }
            mte_portal_change_credentials((int) $adminUser['id'], $currentPassword, $newUsername, $newPassword);
            header('Location: ' . mte_portal_url('/admin/?menu=akun&berhasil=akun'));
            exit;
        }
        if ($action === 'hapus') {
            $section = $_POST['section'] ?? '';
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if (!is_string($section) || !isset($adminSections[$section]) || !$id || $id < 1) {
                throw new InvalidArgumentException('Konten yang dipilih tidak valid.');
            }
            $removed = mte_portal_delete_content((int) $id, $section);
            if (!$removed) {
                throw new InvalidArgumentException('Konten sudah tidak tersedia. Muat ulang halaman.');
            }
            if ($section === 'dokumen') {
                mte_portal_remove_file_if_unused((string) $removed['file_name']);
            }
            foreach ($removed['gallery_files'] as $galleryItem) {
                mte_portal_remove_gallery_file_if_unused((string) $galleryItem['file_name']);
            }
            foreach ($removed['inline_files'] as $inlineImage) {
                mte_portal_remove_inline_image_if_unused((string) $inlineImage);
            }
            header('Location: ' . mte_portal_url('/admin/?menu=' . rawurlencode($section) . '&berhasil=hapus'));
            exit;
        }
        if ($action === 'simpan') {
            $section = $_POST['section'] ?? '';
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            if (!is_string($section) || !isset($adminSections[$section]) || $id === false || $id < 0) {
                throw new InvalidArgumentException('Menu atau identitas konten tidak valid.');
            }
            $fileBaru = null;
            if ($section === 'dokumen') {
                $fileBaru = mte_portal_store_pdf($_FILES['file_pdf'] ?? []);
            }
            $saved = mte_portal_save_content($section, (int) $id, $_POST, $fileBaru);
            $currentContent = mte_portal_content_by_id((int) $saved['id']);
            $currentImages = array_fill_keys(mte_portal_inline_images((string) ($currentContent['body'] ?? '')), true);
            foreach (mte_portal_inline_images((string) $saved['old_body']) as $oldImage) {
                if (!isset($currentImages[$oldImage])) {
                    mte_portal_remove_inline_image_if_unused($oldImage);
                }
            }
            if ($section === 'dokumen' && $fileBaru !== null && $saved['old_file'] !== $fileBaru) {
                mte_portal_remove_file_if_unused((string) $saved['old_file']);
            }
            if ($section === 'berita') {
                mte_portal_save_gallery((int) $saved['id'], $_POST, $_FILES['gallery_files'] ?? []);
            }
            header('Location: ' . mte_portal_url('/admin/?menu=' . rawurlencode($section) . '&berhasil=simpan'));
            exit;
        }
        throw new InvalidArgumentException('Aksi dashboard tidak dikenal.');
    } catch (Throwable $error) {
        if ($fileBaru !== null) {
            try {
                mte_portal_remove_file_if_unused($fileBaru);
            } catch (Throwable $ignored) {
                // Database content remains authoritative; orphan cleanup can be retried later.
            }
        }
        $pesanAdmin = $error instanceof InvalidArgumentException || $error instanceof RuntimeException
            ? $error->getMessage()
            : 'Perubahan belum dapat disimpan. Periksa isian lalu coba lagi.';
        $pesanJenis = 'error';
        if (isset($_POST['section']) && is_string($_POST['section']) && isset($adminSections[$_POST['section']])) {
            $adminMenu = $_POST['section'];
        }
    }
}

$hasilParam = $_GET['berhasil'] ?? '';
if ($pesanAdmin === '' && is_string($hasilParam)) {
    if ($hasilParam === 'simpan') {
        $pesanAdmin = 'Konten berhasil disimpan.';
        $pesanJenis = 'success';
    } elseif ($hasilParam === 'hapus') {
        $pesanAdmin = 'Konten berhasil dihapus.';
        $pesanJenis = 'success';
    } elseif ($hasilParam === 'akun') {
        $pesanAdmin = 'Username dan password berhasil diperbarui.';
        $pesanJenis = 'success';
    }
}

$formData = (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'simpan') ? $_POST : [];
$editIdParam = $_GET['edit'] ?? ($formData['id'] ?? '');
$editId = is_scalar($editIdParam) ? filter_var((string) $editIdParam, FILTER_VALIDATE_INT) : false;
$editItem = $editId && $adminMenu !== 'akun' ? mte_portal_content_by_id((int) $editId) : null;
if ($editItem && $editItem['section'] !== $adminMenu) {
    $editItem = null;
}
$isNewItem = $adminMenu !== 'akun' && isset($_GET['baru']);
$showEditor = $adminMenu !== 'akun' && ($isNewItem || $editItem !== null || $formData !== []);
$deleteIdParam = $_GET['hapus'] ?? '';
$deleteId = is_scalar($deleteIdParam) ? filter_var((string) $deleteIdParam, FILTER_VALIDATE_INT) : false;
$deleteItem = $deleteId && $adminMenu !== 'akun' ? mte_portal_content_by_id((int) $deleteId) : null;
if ($deleteItem && $deleteItem['section'] !== $adminMenu) {
    $deleteItem = null;
}
$csrfAdmin = mte_portal_csrf_token();
$basePathAdmin = mte_portal_base_path();
$contentRows = $adminMenu !== 'akun' ? mte_portal_content_list($adminMenu) : [];
$newsGallery = $adminMenu === 'berita' && $editItem ? mte_portal_gallery_for_content((int) $editItem['id']) : [];

$formValue = static function (string $field, string $fallback = '') use ($formData, $editItem): string {
    if (array_key_exists($field, $formData) && is_scalar($formData[$field])) {
        return (string) $formData[$field];
    }
    return isset($editItem[$field]) ? (string) $editItem[$field] : $fallback;
};
$checkedActive = array_key_exists('is_active', $formData)
    ? (string) $formData['is_active'] === '1'
    : ($editItem ? (int) $editItem['is_active'] === 1 : true);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Dashboard Pengelola · MTE UNSOED</title>
    <link rel="stylesheet" href="<?= mteEscape($assetPath) ?>/portal-admin.css?v=7">
    <link rel="stylesheet" href="<?= mteEscape($assetPath) ?>/portal-brand.css?v=1">
</head>
<body class="admin-page">
    <header class="admin-header">
        <a class="admin-brand" href="<?= mte_portal_escape($basePathAdmin) ?>/">
            <?php mteAdminBrand('Dashboard pengelola konten'); ?>
        </a>
        <div class="admin-header-actions">
            <a class="admin-button admin-button-quiet" href="<?= mte_portal_escape($basePathAdmin) ?>/kelola-st/"><?php mteIcon($assetPath, 'surat-tugas'); ?> Kelola ST</a>
            <a class="admin-button admin-button-quiet" href="<?= mte_portal_escape($basePathAdmin) ?>/simulasi-akreditasi/"><?php mteIcon($assetPath, 'simulasi'); ?> Simulasi Akreditasi</a>
            <form method="post" action="<?= mte_portal_escape($basePathAdmin) ?>/admin/">
                <input type="hidden" name="csrf_token" value="<?= mte_portal_escape($csrfAdmin) ?>">
                <input type="hidden" name="action" value="logout">
                <button class="admin-button admin-button-quiet" type="submit"><?php mteIcon($assetPath, 'keluar'); ?> Keluar</button>
            </form>
        </div>
    </header>
    <main class="admin-shell">
        <aside class="admin-sidebar">
            <span class="admin-eyebrow">MENU PENGELOLAAN</span>
            <nav class="admin-nav" aria-label="Menu dashboard">
                <?php foreach ($adminSections as $key => $name): ?>
                    <a href="<?= mte_portal_escape($basePathAdmin) ?>/admin/?menu=<?= rawurlencode($key) ?>"<?= $adminMenu === $key ? ' aria-current="page"' : '' ?>>
                        <span class="admin-nav-icon"><?php mteIcon($assetPath, $key); ?></span>
                        <?= mte_portal_escape($name) ?>
                    </a>
                <?php endforeach; ?>
                <a href="<?= mte_portal_escape($basePathAdmin) ?>/admin/?menu=akun"<?= $adminMenu === 'akun' ? ' aria-current="page"' : '' ?>>
                    <span class="admin-nav-icon"><?php mteIcon($assetPath, 'akun'); ?></span>
                    Akun &amp; password
                </a>
            </nav>
            <p class="admin-sidebar-note">Konten yang berstatus tayang akan muncul pada portal publik.</p>
        </aside>
        <section class="admin-main">
            <?php if (empty($adminUser['password_changed_at'])): ?>
                <div class="admin-alert" role="status">Akun ini masih memakai kredensial awal. Ganti username dan password melalui menu <strong>Akun &amp; password</strong>.</div>
            <?php endif; ?>
            <?php if ($pesanAdmin !== ''): ?>
                <div class="admin-alert admin-alert-<?= mte_portal_escape($pesanJenis) ?>" role="status"><?= mte_portal_escape($pesanAdmin) ?></div>
            <?php endif; ?>

            <?php if ($adminMenu === 'akun'): ?>
                <div class="admin-page-heading">
                    <div class="admin-title"><span class="admin-title-icon"><?php mteIcon($assetPath, 'akun'); ?></span><div><h1>Akun &amp; password</h1><p>Ganti nama pengguna dan password pengelola portal.</p></div></div>
                </div>
                <form class="admin-form admin-form-narrow" method="post" action="<?= mte_portal_escape($basePathAdmin) ?>/admin/?menu=akun">
                    <input type="hidden" name="csrf_token" value="<?= mte_portal_escape($csrfAdmin) ?>">
                    <input type="hidden" name="action" value="ubah_akun">
                    <label>Password saat ini<input type="password" name="current_password" autocomplete="current-password" required></label>
                    <label>Username baru<input type="text" name="new_username" value="<?= mte_portal_escape((string) $adminUser['username']) ?>" autocomplete="username" minlength="3" maxlength="80" required></label>
                    <label>Password baru <small>Minimal 10 karakter</small><input type="password" name="new_password" autocomplete="new-password" minlength="10" maxlength="200" required></label>
                    <label>Ulangi password baru<input type="password" name="confirm_password" autocomplete="new-password" minlength="10" maxlength="200" required></label>
                    <div class="admin-form-actions"><button class="admin-button admin-button-primary" type="submit"><?php mteIcon($assetPath, 'simpan'); ?> Simpan akun</button></div>
                </form>
            <?php else: ?>
                <div class="admin-page-heading">
                    <div class="admin-title"><span class="admin-title-icon"><?php mteIcon($assetPath, $adminMenu); ?></span><div><h1><?= mte_portal_escape($adminSections[$adminMenu]) ?></h1><p>Tambah, ubah, tayangkan, atau hapus konten pada bagian ini.</p></div></div>
                    <?php if (!$showEditor && !$deleteItem): ?>
                        <a class="admin-button admin-button-primary" href="<?= mte_portal_escape($basePathAdmin) ?>/admin/?menu=<?= rawurlencode($adminMenu) ?>&amp;baru=1"><?php mteIcon($assetPath, 'tambah'); ?> Tambah konten</a>
                    <?php endif; ?>
                </div>

                <?php if ($deleteItem): ?>
                    <section class="admin-panel admin-confirm-panel">
                        <span class="admin-eyebrow">KONFIRMASI PENGHAPUSAN</span>
                        <h2>Hapus konten ini?</h2>
                        <p><strong><?= mte_portal_escape((string) $deleteItem['title']) ?></strong> akan dihapus dari database dan tidak lagi tampil di portal.</p>
                        <div class="admin-form-actions">
                            <form method="post" action="<?= mte_portal_escape($basePathAdmin) ?>/admin/">
                                <input type="hidden" name="csrf_token" value="<?= mte_portal_escape($csrfAdmin) ?>">
                                <input type="hidden" name="action" value="hapus">
                                <input type="hidden" name="section" value="<?= mte_portal_escape($adminMenu) ?>">
                                <input type="hidden" name="id" value="<?= (int) $deleteItem['id'] ?>">
                                <button class="admin-button admin-button-danger" type="submit"><?php mteIcon($assetPath, 'hapus'); ?> Ya, hapus konten</button>
                            </form>
                            <a class="admin-button" href="<?= mte_portal_escape($basePathAdmin) ?>/admin/?menu=<?= rawurlencode($adminMenu) ?>">Batal</a>
                        </div>
                    </section>
                <?php elseif ($showEditor): ?>
                    <?php $isEditing = $editItem !== null; ?>
                    <form class="admin-form" method="post" enctype="multipart/form-data" action="<?= mte_portal_escape($basePathAdmin) ?>/admin/">
                        <input type="hidden" name="csrf_token" value="<?= mte_portal_escape($csrfAdmin) ?>">
                        <input type="hidden" name="action" value="simpan">
                        <input type="hidden" name="section" value="<?= mte_portal_escape($adminMenu) ?>">
                        <input type="hidden" name="id" value="<?= $isEditing ? (int) $editItem['id'] : '0' ?>">
                        <div class="admin-form-heading"><div><span class="admin-eyebrow"><?= $isEditing ? 'UBAH KONTEN' : 'KONTEN BARU' ?></span><h2><?= $isEditing ? 'Perbarui informasi' : 'Tambah informasi' ?></h2></div><a href="<?= mte_portal_escape($basePathAdmin) ?>/admin/?menu=<?= rawurlencode($adminMenu) ?>">Kembali ke daftar</a></div>
                        <?php if ($adminMenu === 'vmts'): ?>
                            <label>Label kecil hijau <small>Contoh: VISI, MISI, TUJUAN</small><input type="text" name="label" maxlength="120" value="<?= mte_portal_escape($formValue('label')) ?>" required></label>
                        <?php elseif ($adminMenu === 'cpl'): ?>
                            <label>Kode CPL <small>Contoh: 01, 02 · tampil sebagai tulisan kecil hijau</small><input type="text" name="code" maxlength="30" value="<?= mte_portal_escape($formValue('code')) ?>"></label>
                            <label>Kata kunci <small>Ditampilkan pada bagian judul</small><input type="text" name="title" maxlength="255" value="<?= mte_portal_escape($formValue('title')) ?>" required></label>
                        <?php elseif ($adminMenu === 'ppm'): ?>
                            <label>Kode butir <small>Contoh: 01, 02</small><input type="text" name="code" maxlength="30" value="<?= mte_portal_escape($formValue('code')) ?>"></label>
                        <?php elseif ($adminMenu === 'kurikulum'): ?>
                            <div class="admin-form-grid">
                                <label>Semester<input type="number" name="label" min="1" max="8" value="<?= mte_portal_escape($formValue('label', '1')) ?>" required></label>
                                <label>Kode mata kuliah<input type="text" name="code" maxlength="30" value="<?= mte_portal_escape($formValue('code')) ?>" required></label>
                                <label>Kelompok<input type="text" name="kind" maxlength="40" placeholder="Contoh: MKU" value="<?= mte_portal_escape($formValue('kind', 'MKU')) ?>" required></label>
                                <label>SKS<input type="number" name="summary" min="1" max="10" value="<?= mte_portal_escape($formValue('summary', '3')) ?>" required></label>
                            </div>
                            <label>Nama mata kuliah<input type="text" name="title" maxlength="255" value="<?= mte_portal_escape($formValue('title')) ?>" required></label>
                        <?php elseif ($adminMenu === 'berita'): ?>
                            <div class="admin-form-grid">
                                <label>Jenis informasi<select name="kind" required><?php foreach (['berita' => 'Berita', 'pengumuman' => 'Pengumuman', 'agenda' => 'Agenda'] as $kindValue => $kindLabel): ?><option value="<?= $kindValue ?>"<?= $formValue('kind', 'berita') === $kindValue ? ' selected' : '' ?>><?= $kindLabel ?></option><?php endforeach; ?></select></label>
                                <label>Tanggal tayang<input type="date" name="publish_date" value="<?= mte_portal_escape($formValue('publish_date', date('Y-m-d'))) ?>" required></label>
                            </div>
                        <?php elseif ($adminMenu === 'dokumen'): ?>
                            <input type="hidden" name="kind" value="PDF">
                            <label>Berkas PDF <small>Maksimal 5 MB<?= $isEditing ? '; kosongkan jika tetap memakai berkas sekarang' : '' ?></small><input type="file" name="file_pdf" accept="application/pdf,.pdf"<?= $isEditing ? '' : ' required' ?>></label>
                            <?php if ($isEditing && !empty($editItem['file_name'])): ?><p class="admin-current-file">Berkas saat ini: <strong><?= mte_portal_escape(basename((string) $editItem['file_name'])) ?></strong></p><?php endif; ?>
                        <?php endif; ?>
                        <?php if ($adminMenu !== 'cpl' && $adminMenu !== 'kurikulum'): ?><label>Judul<input type="text" name="title" maxlength="255" value="<?= mte_portal_escape($formValue('title')) ?>" required></label><?php endif; ?>
                        <?php if ($adminMenu !== 'kurikulum'): ?><label>Slug URL <small>Boleh dikosongkan agar dibuat otomatis dari judul</small><input type="text" name="slug" maxlength="140" value="<?= mte_portal_escape($formValue('slug')) ?>" pattern="[A-Za-z0-9-]+"></label><?php endif; ?>
                        <?php if ($adminMenu === 'berita' || $adminMenu === 'dokumen'): ?>
                            <label>Ringkasan untuk daftar<textarea name="summary" rows="3" maxlength="5000"><?= mte_portal_escape($formValue('summary')) ?></textarea></label>
                        <?php endif; ?>
                        <?php if ($adminMenu !== 'kurikulum'): ?><div class="admin-rich-field">
                            <span class="admin-field-label"><?= $adminMenu === 'berita' || $adminMenu === 'dokumen' ? 'Narasi detail' : 'Detail' ?></span>
                            <div class="rich-editor-frame">
                                <div class="rich-toolbar" role="toolbar" aria-label="Pemformatan konten">
                                    <button type="button" data-rich-command="bold" aria-label="Tebal" title="Tebal"><strong>B</strong></button>
                                    <button type="button" data-rich-command="italic" aria-label="Miring" title="Miring"><em>I</em></button>
                                    <button type="button" data-rich-command="underline" aria-label="Garis bawah" title="Garis bawah"><u>U</u></button>
                                    <span class="rich-toolbar-separator" aria-hidden="true"></span>
                                    <button type="button" data-rich-command="insertUnorderedList" aria-label="Daftar bullet" title="Daftar bullet">• Daftar</button>
                                    <button type="button" data-rich-command="insertOrderedList" aria-label="Daftar bernomor" title="Daftar bernomor">1. Daftar</button>
                                    <span class="rich-toolbar-separator" aria-hidden="true"></span>
                                    <button type="button" data-rich-link aria-label="Sisipkan tautan" title="Sisipkan tautan">Tautan</button>
                                    <button type="button" data-rich-image aria-label="Sisipkan gambar" title="Sisipkan gambar">Gambar</button>
                                </div>
                                <div class="rich-editor" contenteditable="true" role="textbox" aria-multiline="true" aria-label="Detail konten" data-rich-editor data-placeholder="Tulis detail konten di sini…"><?= mte_portal_sanitize_html($formValue('body')) ?></div>
                            </div>
                            <input type="hidden" name="body" value="<?= mte_portal_escape($formValue('body')) ?>" data-rich-value>
                            <input type="file" accept="image/jpeg,image/png,image/webp" hidden data-rich-image-picker>
                            <p class="admin-rich-help">Pilih teks lalu gunakan toolbar. Gambar diunggah ke folder konten; JPG, PNG, atau WebP maksimal 5 MB.</p>
                        </div><?php endif; ?>
                        <?php if ($adminMenu === 'berita'): ?>
                            <section class="admin-gallery-manager" aria-labelledby="gallery-manager-title">
                                <div class="admin-gallery-heading"><div><span class="admin-eyebrow">DOKUMENTASI BERITA</span><h3 id="gallery-manager-title">Galeri foto</h3></div><small>JPG, PNG, atau WebP · maks. 5 MB/foto</small></div>
                                <?php if ($newsGallery): ?>
                                    <div class="admin-gallery-grid">
                                        <?php foreach ($newsGallery as $galleryItem): ?>
                                            <article class="admin-gallery-item">
                                                <img src="<?= mte_portal_escape(mte_portal_gallery_url((string) $galleryItem['file_name'])) ?>" alt="">
                                                <div class="admin-gallery-fields">
                                                    <label>Keterangan<input type="text" name="gallery_caption[<?= (int) $galleryItem['id'] ?>]" maxlength="255" value="<?= mte_portal_escape((string) $galleryItem['caption']) ?>"></label>
                                                    <label>Teks alternatif<input type="text" name="gallery_alt[<?= (int) $galleryItem['id'] ?>]" maxlength="255" value="<?= mte_portal_escape((string) $galleryItem['alt_text']) ?>"></label>
                                                    <label class="admin-check admin-gallery-delete"><input type="checkbox" name="delete_gallery[]" value="<?= (int) $galleryItem['id'] ?>"><span>Hapus foto ini</span></label>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="admin-gallery-empty">Belum ada foto pada galeri berita ini.</p>
                                <?php endif; ?>
                                <label>Tambah foto galeri <input type="file" name="gallery_files[]" accept="image/jpeg,image/png,image/webp" multiple></label>
                            </section>
                        <?php endif; ?>
                        <div class="admin-form-grid admin-form-bottom">
                            <label>Urutan tampil<input type="number" name="sort_order" min="0" max="999999" value="<?= mte_portal_escape($formValue('sort_order', $isEditing ? '0' : (string) (count($contentRows) + 1))) ?>" required></label>
                            <label class="admin-check"><input type="checkbox" name="is_active" value="1"<?= $checkedActive ? ' checked' : '' ?>><span>Tayangkan di portal</span></label>
                        </div>
                        <div class="admin-form-actions"><button class="admin-button admin-button-primary" type="submit"><?php mteIcon($assetPath, 'simpan'); ?> Simpan konten</button><a class="admin-button" href="<?= mte_portal_escape($basePathAdmin) ?>/admin/?menu=<?= rawurlencode($adminMenu) ?>">Batal</a></div>
                    </form>
                <?php else: ?>
                    <div class="admin-list-summary"><span><?= count($contentRows) ?> konten pada menu ini</span><span>Urutkan dengan kolom urutan tampil</span></div>
                    <?php if (!$contentRows): ?>
                        <div class="admin-empty">Belum ada konten untuk menu ini.</div>
                    <?php else: ?>
                        <div class="admin-table-wrap"><table class="admin-table">
                            <thead><tr><th>Judul</th><th>Label / jenis</th><th>Status</th><th>Urutan</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php foreach ($contentRows as $row): ?>
                                    <?php $rowMeta = $row['section'] === 'berita' ? ucfirst((string) $row['kind']) . ' · ' . (string) $row['publish_date'] : ($row['section'] === 'cpl' ? 'CPL-' . (string) $row['code'] : ($row['section'] === 'kurikulum' ? 'Semester ' . (string) $row['label'] . ' · ' . (string) $row['code'] . ' · ' . (string) $row['summary'] . ' SKS · ' . (string) $row['kind'] : ($row['label'] ?: ($row['code'] ?: ($row['kind'] ?: ($row['section'] === 'dokumen' ? basename((string) $row['file_name']) : '—')))))); ?>
                                    <tr>
                                        <td><strong><?= mte_portal_escape((string) $row['title']) ?></strong><small><?= mte_portal_escape((string) $row['slug']) ?></small></td>
                                        <td><?= mte_portal_escape($rowMeta) ?></td>
                                        <td><span class="admin-status<?= (int) $row['is_active'] === 1 ? ' is-live' : '' ?>"><?= (int) $row['is_active'] === 1 ? 'Tayang' : 'Draft' ?></span></td>
                                        <td><?= (int) $row['sort_order'] ?></td>
                                        <td><div class="admin-row-actions">
                                            <a href="<?= mte_portal_escape($basePathAdmin) ?>/admin/?menu=<?= rawurlencode($adminMenu) ?>&amp;edit=<?= (int) $row['id'] ?>"><?php mteIcon($assetPath, 'ubah'); ?> Ubah</a>
                                            <a class="admin-delete-link" href="<?= mte_portal_escape($basePathAdmin) ?>/admin/?menu=<?= rawurlencode($adminMenu) ?>&amp;hapus=<?= (int) $row['id'] ?>"><?php mteIcon($assetPath, 'hapus'); ?> Hapus</a>
                                        </div></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
<script src="<?= mte_portal_escape($basePathAdmin) ?>/simulasi_assets/portal-editor.js?v=1" defer></script>
</body>
</html>
