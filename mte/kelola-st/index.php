<?php
if (!defined('MTE_PORTAL_ROUTED')) {
    http_response_code(404);
    exit;
}
require_once __DIR__.'/st_lib.php';
date_default_timezone_set('Asia/Jakarta');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

function mte_link($params = []) { return './' . ($params ? '?'.http_build_query($params) : ''); }
function mte_st_csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . mte_st_escape(mte_portal_csrf_token()) . '">';
}
function mte_redirect($params, $message) {
    $_SESSION['mte_st_flash'] = $message;
    header('Location: '.mte_link($params));
    exit;
}
function mte_filter_hidden($filter) {
    foreach ($filter as $key=>$value) echo '<input type="hidden" name="'.mte_st_escape($key).'" value="'.mte_st_escape(is_bool($value) ? (int)$value : $value).'">';
}
function mte_pdf_widget($id, $document) { ?>
<?php if ($document): ?><a class="st-pdf-link" href="<?= mte_st_escape(mte_link(['download_pdf'=>$id])) ?>">Unduh PDF ST</a><small class="st-block muted"><?= mte_st_escape($document['nama_asli']) ?></small><?php endif; ?>
<details class="st-pdf-upload no-print"><summary><?= $document?'Ganti PDF ST':'Unggah PDF ST' ?></summary>
<form method="post" enctype="multipart/form-data" <?= $document?'data-confirm="Ganti PDF ST yang tersimpan dengan berkas baru?"':'' ?>>
<?= mte_st_csrf_field() ?><input type="hidden" name="action" value="upload_pdf"><input type="hidden" name="id" value="<?= (int)$id ?>"><input type="hidden" name="MAX_FILE_SIZE" value="2097152">
<input type="file" name="st_pdf" accept="application/pdf,.pdf" aria-label="PDF ST kegiatan <?= (int)$id ?>" required><small>PDF ST terbit, maksimal 2 MB.</small><button type="submit" class="btn btn-sm">Simpan PDF</button>
</form></details>
<?php }
function mte_filter_form($filter, $view, $id = null) { ?>
<form method="get" class="st-filters no-print">
  <input type="hidden" name="view" value="<?= mte_st_escape($view) ?>">
  <?php if ($id): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>
  <label>Tahun pelaksanaan<input name="tahun" type="number" min="1900" max="2200" placeholder="Semua tahun" value="<?= mte_st_escape($filter['tahun']) ?>"></label>
  <label>Status<select name="status_filter">
    <?php foreach ([''=>'Usulan + terbit','usulan'=>'Usulan','terbit'=>'Terbit','dibatalkan'=>'Dibatalkan','semua'=>'Semua status'] as $value=>$label): ?><option value="<?= mte_st_escape($value) ?>" <?= $filter['status_filter']===$value?'selected':'' ?>><?= mte_st_escape($label) ?></option><?php endforeach; ?>
  </select></label>
  <label class="st-grow">Cari kegiatan atau nomor ST<input name="q" value="<?= mte_st_escape($filter['q']) ?>" placeholder="Ketik nama kegiatan atau nomor ST"></label>
  <?php if ($view === 'sebaran'): ?><label class="st-check"><input type="checkbox" name="termasuk_referensi" value="1" <?= $filter['termasuk_referensi']?'checked':'' ?>> Sertakan personel di luar jurusan</label><?php endif; ?>
  <button class="btn btn-primary" type="submit">Terapkan</button>
  <a class="btn" href="<?= mte_st_escape(mte_link(['view'=>$view]+($id?['id'=>$id]:[]))) ?>">Reset</a>
</form>
<?php }

$views = ['kegiatan','form','detail','sebaran','master','personel'];
$requestedView = $_GET['view'] ?? '';
$view = is_string($requestedView) && in_array($requestedView, $views, true) ? $requestedView : 'kegiatan';
$error = '';
$fatal = '';
$postedEvent = null;
$postedPerson = null;
$flash = $_SESSION['mte_st_flash'] ?? '';
unset($_SESSION['mte_st_flash']);
$filter = ['tahun'=>'','status_filter'=>'','q'=>'','termasuk_referensi'=>false];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        mte_portal_require_csrf();
    } catch (RuntimeException $e) {
        exit($e->getMessage());
    }
}
try {
    $filter = mte_filters($_GET);
    if (isset($_GET['download_pdf'])) {
        $document=mte_one('SELECT * FROM mte_st_dokumen WHERE kegiatan_id=?','i',[mte_id($_GET['download_pdf'])]);
        if (!$document || !is_file(mte_pdf_path($document['nama_simpan']))) { http_response_code(404); exit('PDF ST tidak ditemukan.'); }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="ST-'.(int)$document['kegiatan_id'].'.pdf"; filename*=UTF-8\'\''.rawurlencode($document['nama_asli']));
        header('Content-Length: '.filesize(mte_pdf_path($document['nama_simpan'])));
        readfile(mte_pdf_path($document['nama_simpan']));
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'upload_pdf') {
            $id=mte_id($_POST['id'] ?? '');
            mte_upload_pdf($id,$_FILES['st_pdf'] ?? null);
            mte_redirect(['view'=>'kegiatan'],'PDF ST berhasil disimpan.');
        } elseif ($action === 'save_event') {
            $postedEvent = $_POST;
            $view = 'form';
            $id = mte_save_event($_POST, (int)$adminUser['id']);
            mte_redirect(['view'=>'detail','id'=>$id], 'Kegiatan dan susunan tim berhasil disimpan.');
        } elseif ($action === 'delete_event') {
            mte_delete_event(mte_id($_POST['id'] ?? ''),mte_id($_POST['versi'] ?? ''));
            mte_redirect(['view'=>'kegiatan'],'Kegiatan beserta penugasannya berhasil dihapus.');
        } elseif ($action === 'save_person') {
            $postedPerson = $_POST;
            $view = 'master';
            mte_save_person($_POST);
            mte_redirect(['view'=>'master'],'Data personel berhasil disimpan.');
        } elseif ($action === 'export') {
            $exportFilter = mte_filters($_POST);
            $rows = mte_distribution($exportFilter);
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="sebaran_st_mte.csv"');
            $out = fopen('php://output','w');
            fwrite($out,"\xEF\xBB\xBF");
            fputcsv($out,['Tahun pelaksanaan',$exportFilter['tahun'] ?: 'Semua tahun','Status',$exportFilter['status_filter'] ?: 'Usulan + terbit','Pencarian',mte_csv_cell($exportFilter['q'])],';');
            fputcsv($out,['Nama','NIP','Kelompok','Ketua','Sekretaris','Anggota','Total'], ';');
            foreach ($rows as $r) fputcsv($out,[mte_csv_cell($r['nama']),$r['nip'] ? "'".$r['nip'] : '',$r['kelompok']==='dosen'?'Dosen jurusan':'Di luar jurusan',(int)$r['ketua'],(int)$r['sekretaris'],(int)$r['anggota'],(int)$r['total']],';');
            fclose($out);
            exit;
        } else throw new InvalidArgumentException('Aksi tidak dikenali.');
    }
} catch (InvalidArgumentException $e) { $error = $e->getMessage(); http_response_code(422); }
catch (Throwable $e) { error_log('[MTE ST] '.$e->getMessage()); $fatal = 'Data ST belum dapat dimuat. Hubungi pengelola aplikasi untuk memeriksa koneksi database atau instalasi modul.'; http_response_code(500); }

$events = $distribution = $people = [];
$event = $person = $form = $personForm = null;
$stats = ['dosen'=>0,'referensi'=>0];
$documents = [];
if ($fatal === '') {
    try {
        $people = mte_rows('SELECT * FROM mte_st_personel ORDER BY kelompok,nama,id');
        foreach (mte_rows('SELECT kegiatan_id,nama_asli FROM mte_st_dokumen') as $document) $documents[$document['kegiatan_id']]=$document;
        foreach ($people as $p) $stats[$p['kelompok']]++;
        if ($view === 'kegiatan' || $view === 'sebaran' || $view === 'personel') $events = mte_events($filter);
        if ($view === 'sebaran') $distribution = mte_distribution($filter);
        if ($view === 'detail') {
            $event = mte_event(mte_id($_GET['id'] ?? ''));
            if (!$event) { http_response_code(404); $error = 'Kegiatan tidak ditemukan.'; }
        }
        if ($view === 'form') {
            $event = !empty($_GET['id']) ? mte_event(mte_id($_GET['id'])) : null;
            if (!empty($_GET['id']) && !$event && !$postedEvent) { http_response_code(404); $error = 'Kegiatan tidak ditemukan.'; $view='kegiatan'; $events=mte_events($filter); }
            else {
                $form = ['id'=>'','versi'=>'','nama'=>'','nomor_st'=>'','status'=>'usulan','presisi_waktu'=>'bulan','mulai'=>'','selesai'=>'','tempat'=>'','catatan'=>'','tim'=>[['personel_id'=>'','peran'=>'ketua'],['personel_id'=>'','peran'=>'sekretaris'],['personel_id'=>'','peran'=>'anggota']]];
                if ($event) $form = array_merge($form,$event,['mulai'=>substr($event['tanggal_mulai'],0,$event['presisi_waktu']==='bulan'?7:10),'selesai'=>substr($event['tanggal_selesai'],0,$event['presisi_waktu']==='bulan'?7:10)]);
                if ($postedEvent) $form = array_merge($form,array_intersect_key($postedEvent,$form));
                if (!is_array($form['tim'])) $form['tim']=[];
            }
        }
        if ($view === 'master') {
            if (!empty($_GET['edit_person'])) $personForm=mte_one('SELECT * FROM mte_st_personel WHERE id=?','i',[mte_id($_GET['edit_person'])]);
            if (isset($_GET['new_person'])) $personForm=['id'=>'','nama'=>'','nip'=>'','golongan'=>'','status_kepegawaian'=>'','kelompok'=>'dosen','aktif'=>1];
            if ($postedPerson) { $personForm=$postedPerson; $personForm['aktif']=isset($postedPerson['aktif']); }
        }
        if ($view === 'personel') {
            $person=mte_one('SELECT * FROM mte_st_personel WHERE id=?','i',[mte_id($_GET['id'] ?? '')]);
            if (!$person) { http_response_code(404); $error='Personel tidak ditemukan.'; }
        }
    } catch (Throwable $e) { error_log('[MTE ST] '.$e->getMessage()); $fatal='Data ST belum dapat dimuat. Silakan coba kembali.'; http_response_code(500); }
}
$activeTab = in_array($view,['form','detail'],true)?'kegiatan':($view==='personel'?'sebaran':$view);
$stTabs = [
    'kegiatan' => ['Kegiatan', 'surat-tugas'],
    'sebaran' => ['Sebaran penugasan', 'simulasi'],
    'master' => ['Dosen jurusan', 'dosen'],
];
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kelola Surat Tugas · MTE UNSOED</title>
<link rel="icon" type="image/svg+xml" href="<?= mte_st_escape($assetPath) ?>/unsoed-favicon.svg?v=1">
<link rel="stylesheet" href="<?= mte_portal_escape($assetPath) ?>/st-base.css?v=1">
<link rel="stylesheet" href="<?= mte_st_escape($assetPath) ?>/portal-admin.css?v=8">
<link rel="stylesheet" href="<?= mte_st_escape($assetPath) ?>/portal-brand.css?v=2">
<link rel="stylesheet" href="st.css?v=5">
<script src="st.js?v=3" defer></script>
</head>
<body class="st-app admin-page">
<header class="admin-header no-print">
  <a class="admin-brand" href="<?= mte_st_escape(mte_link()) ?>">
    <?php mteAdminBrand('Pengelolaan surat tugas'); ?>
  </a>
  <nav class="admin-header-actions" aria-label="Pengelolaan MTE">
    <a class="admin-button admin-button-quiet st-dashboard-link" href="<?= mte_st_escape(mte_portal_url('/admin/')) ?>"><?php mteIcon($assetPath, 'arrow'); ?> Dashboard MTE</a>
    <form method="post" action="<?= mte_st_escape(mte_portal_url('/admin/')) ?>">
      <?= mte_st_csrf_field() ?>
      <input type="hidden" name="action" value="logout">
      <button class="admin-button admin-button-quiet" type="submit"><?php mteIcon($assetPath, 'keluar'); ?> Keluar</button>
    </form>
  </nav>
</header>
<main class="container st-main">
<div class="page-head">
  <div class="admin-title">
    <span class="admin-title-icon no-print"><?php mteIcon($assetPath, 'surat-tugas'); ?></span>
    <div><h1>Surat Tugas MTE</h1><p class="muted">Atur susunan tim dan pantau sebaran penugasan dosen.</p></div>
  </div>
  <a href="<?= mte_st_escape(mte_link(['view'=>'form'])) ?>" class="admin-button admin-button-primary no-print"><?php mteIcon($assetPath, 'tambah'); ?> Tambah kegiatan</a>
</div>
<nav class="st-tabs no-print" aria-label="Pengelolaan ST">
  <?php foreach ($stTabs as $key => [$label, $icon]): ?>
    <a href="<?= mte_st_escape(mte_link(['view'=>$key])) ?>"<?= $activeTab === $key ? ' class="active" aria-current="page"' : '' ?>>
      <span class="admin-nav-icon"><?php mteIcon($assetPath, $icon); ?></span>
      <?= mte_st_escape($label) ?>
    </a>
  <?php endforeach; ?>
</nav>
<?php if ($flash): ?><div class="alert st-success" role="status"><?= mte_st_escape($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error" role="alert"><?= mte_st_escape($error) ?></div><?php endif; ?>
<?php if ($fatal): ?><div class="alert alert-error" role="alert"><?= mte_st_escape($fatal) ?></div>
<?php elseif ($view === 'kegiatan'): ?>
<?php $assignments=0; foreach ($events as $k) $assignments+=count($k['tim']); ?>
<div class="st-stats"><div><span>Kegiatan terpilih</span><strong><?= count($events) ?></strong></div><div><span>Penugasan terpilih</span><strong><?= $assignments ?></strong></div><div><span>Dosen jurusan</span><strong><?= $stats['dosen'] ?></strong></div><div><span>Personel di luar jurusan</span><strong><?= $stats['referensi'] ?></strong></div></div>
<?php mte_filter_form($filter,'kegiatan'); ?>
<div class="table-wrap"><table class="table st-events"><thead><tr><th>Kegiatan</th><th>Waktu, ketua &amp; sekretaris</th><th>Anggota</th><th class="no-print">Aksi</th></tr></thead><tbody>
<?php foreach ($events as $k): $members=array_filter($k['tim'],function($t){return $t['peran']==='anggota';}); ?>
<tr><td><a class="st-title" href="<?= mte_st_escape(mte_link(['view'=>'detail','id'=>$k['id']])) ?>"><?= mte_st_escape($k['nama']) ?></a><div class="st-meta"><span class="st-badge <?= mte_st_escape($k['status']) ?>"><?= mte_st_escape(ucfirst($k['status'])) ?></span><?php if ($k['nomor_st']): ?><span><?= mte_st_escape($k['nomor_st']) ?></span><?php else: ?><span class="muted">Nomor ST belum diisi</span><?php endif; ?></div></td><td class="st-date"><?= mte_st_escape(mte_period_label($k)) ?><?php if ($k['tempat']): ?><div class="small muted"><?= mte_st_escape($k['tempat']) ?></div><?php endif; ?><div class="st-team-time-divider"></div><?php foreach ($k['tim'] as $t): if ($t['peran']==='anggota') continue; ?><div class="st-leader"><small><?= $t['peran']==='ketua'?'Ketua':'Sekretaris' ?></small><?= mte_st_escape($t['nama']) ?></div><?php endforeach; ?></td><td><button type="button" class="st-members-toggle" popovertarget="st-members-<?= (int)$k['id'] ?>" aria-expanded="false" aria-controls="st-members-<?= (int)$k['id'] ?>"><span class="st-members-arrow" aria-hidden="true"></span><?= count($members) ?> anggota</button><div popover="auto" class="st-members-panel" id="st-members-<?= (int)$k['id'] ?>" role="region" aria-label="Anggota <?= mte_st_escape($k['nama']) ?>"><strong>Anggota kegiatan</strong><ul class="st-member-list"><?php foreach ($members as $t): ?><li><?= mte_st_escape($t['nama']) ?><?= $t['kelompok']==='referensi'?' *':'' ?></li><?php endforeach; ?></ul></div></td><td class="no-print"><a class="btn btn-sm" href="<?= mte_st_escape(mte_link(['view'=>'detail','id'=>$k['id']])) ?>">Detail</a> <a class="btn btn-sm" href="<?= mte_st_escape(mte_link(['view'=>'form','id'=>$k['id']])) ?>">Ubah</a><?php mte_pdf_widget($k['id'],$documents[$k['id']] ?? null); ?></td></tr>
<?php endforeach; ?>
<?php if (!$events): ?><tr><td colspan="4" class="st-empty">Belum ada kegiatan yang sesuai. Tambahkan kegiatan atau ubah filter.</td></tr><?php endif; ?>
</tbody></table></div><p class="small muted">* Personel di luar jurusan. Data contoh bersumber dari enam usulan dalam surat tanggal 16 November 2026.</p>
<?php elseif ($view === 'form' && $form): ?>
<div class="st-section-head"><h2><?= !empty($form['id'])?'Ubah kegiatan':'Tambah kegiatan' ?></h2><a href="<?= mte_st_escape(mte_link()) ?>" class="btn">Kembali</a></div>
<form method="post" class="form-grid st-editor" id="st-event-form">
<?= mte_st_csrf_field() ?><input type="hidden" name="action" value="save_event"><input type="hidden" name="id" value="<?= mte_st_escape($form['id']) ?>"><input type="hidden" name="versi" value="<?= mte_st_escape($form['versi']) ?>">
<fieldset><legend>Informasi kegiatan</legend>
<label>Nama kegiatan <span class="st-required">*</span><input type="text" name="nama" value="<?= mte_st_escape($form['nama']) ?>" maxlength="255" required placeholder="Contoh: Tim Pengukuran CPL S2 Elektro"></label>
<div class="row-2"><label>Nomor ST<input type="text" name="nomor_st" id="st-number" value="<?= mte_st_escape($form['nomor_st']) ?>" maxlength="120" placeholder="Diisi setelah ST diterbitkan"></label><label>Status<select name="status" id="st-status"><?php foreach (['usulan'=>'Usulan','terbit'=>'Terbit','dibatalkan'=>'Dibatalkan'] as $value=>$label): ?><option value="<?= mte_st_escape($value) ?>" <?= $form['status']===$value?'selected':'' ?>><?= mte_st_escape($label) ?></option><?php endforeach; ?></select></label></div>
<div class="row-3"><label>Ketelitian waktu<select name="presisi_waktu" id="st-precision"><option value="bulan" <?= $form['presisi_waktu']==='bulan'?'selected':'' ?>>Bulan</option><option value="tanggal" <?= $form['presisi_waktu']==='tanggal'?'selected':'' ?>>Tanggal</option></select></label><label>Mulai <span class="st-required">*</span><input type="<?= $form['presisi_waktu']==='tanggal'?'date':'month' ?>" name="mulai" id="st-start" value="<?= mte_st_escape($form['mulai']) ?>" required></label><label>Selesai <span class="st-required">*</span><input type="<?= $form['presisi_waktu']==='tanggal'?'date':'month' ?>" name="selesai" id="st-end" value="<?= mte_st_escape($form['selesai']) ?>" required></label></div>
<label>Tempat<input type="text" name="tempat" value="<?= mte_st_escape($form['tempat']) ?>" maxlength="255" placeholder="Opsional"></label><label>Catatan<textarea name="catatan" rows="3" maxlength="10000" placeholder="Opsional"><?= mte_st_escape($form['catatan']) ?></textarea></label>
</fieldset>
<fieldset><legend>Susunan tim</legend><p class="small muted st-team-help">Pilih nama dari autocomplete. Isi tepat satu ketua, satu sekretaris, dan minimal satu anggota. Nama di luar jurusan diberi label tersendiri.</p>
<div class="st-team-header"><span>Nama personel</span><span>Peran</span><span>Divisi &amp; tanggung jawab (opsional)</span><span></span></div>
<div id="st-team">
<?php $personMap=[]; foreach ($people as $p) $personMap[(int)$p['id']]=$p; foreach (array_values($form['tim']) as $i=>$t): if (!is_array($t)) continue; $selected=$personMap[mte_id($t['personel_id'] ?? '')] ?? null; ?>
<div class="st-team-row">
<div class="st-autocomplete"><label class="st-mobile-label">Nama personel</label><input type="hidden" class="st-person-id" name="tim[<?= $i ?>][personel_id]" value="<?= $selected?(int)$selected['id']:'' ?>"><input type="text" class="st-person-search" value="<?= mte_st_escape($selected['nama'] ?? '') ?>" placeholder="Cari nama atau NIP…" aria-label="Nama personel <?= $i+1 ?>" autocomplete="off" required><small class="st-person-info"><?= $selected?mte_st_escape($selected['kelompok']==='referensi'?'Di luar jurusan':($selected['nip'] ?: 'NIP belum diisi')):'' ?></small></div>
<label><span class="st-mobile-label">Peran</span><select class="st-role" name="tim[<?= $i ?>][peran]"><?php foreach (['ketua'=>'Ketua','sekretaris'=>'Sekretaris','anggota'=>'Anggota'] as $value=>$label): ?><option value="<?= mte_st_escape($value) ?>" <?= ($t['peran'] ?? '')===$value?'selected':'' ?>><?= mte_st_escape($label) ?></option><?php endforeach; ?></select></label>
<div><label class="st-mobile-label">Divisi &amp; tanggung jawab</label><input type="text" name="tim[<?= $i ?>][divisi]" value="<?= mte_st_escape($t['divisi'] ?? '') ?>" maxlength="200" placeholder="Divisi" aria-label="Divisi personel <?= $i+1 ?>"><textarea name="tim[<?= $i ?>][tanggung_jawab]" maxlength="1000" rows="2" placeholder="Tanggung jawab" aria-label="Tanggung jawab personel <?= $i+1 ?>"><?= mte_st_escape($t['tanggung_jawab'] ?? '') ?></textarea></div>
<button type="button" class="btn st-remove" aria-label="Hapus baris personel <?= $i+1 ?>">Hapus</button>
</div>
<?php endforeach; ?>
</div><button type="button" class="btn" id="st-add-member">+ Tambah anggota</button><p id="st-team-error" class="st-validation" role="alert"></p>
</fieldset><div class="form-actions"><button type="submit" class="btn btn-primary">Simpan kegiatan</button><a href="<?= mte_st_escape(mte_link()) ?>" class="btn">Batal</a></div>
</form>
<?php $roster=[]; foreach ($people as $p) $roster[]=['id'=>(int)$p['id'],'nama'=>$p['nama'],'nip'=>$p['nip'],'kelompok'=>$p['kelompok'],'aktif'=>(bool)$p['aktif']]; ?><script type="application/json" id="st-roster"><?= json_encode($roster, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?></script>
<?php elseif ($view === 'detail' && $event): ?>
<div class="st-section-head"><div><h2><?= mte_st_escape($event['nama']) ?></h2><span class="st-badge <?= mte_st_escape($event['status']) ?>"><?= mte_st_escape(ucfirst($event['status'])) ?></span></div><div class="st-actions no-print"><a href="<?= mte_st_escape(mte_link()) ?>" class="btn">Kembali</a><button type="button" class="btn" data-print>Cetak susunan tim</button><a class="btn btn-primary" href="<?= mte_st_escape(mte_link(['view'=>'form','id'=>$event['id']])) ?>">Ubah kegiatan</a></div></div>
<div class="card no-print"><?php mte_pdf_widget($event['id'],$documents[$event['id']] ?? null); ?></div>
<div class="st-detail-meta"><div><small>Nomor ST</small><strong><?= mte_st_escape($event['nomor_st'] ?: 'Belum diterbitkan') ?></strong></div><div><small>Waktu pelaksanaan</small><strong><?= mte_st_escape(mte_period_label($event)) ?></strong></div><div><small>Tempat</small><strong><?= mte_st_escape($event['tempat'] ?: '-') ?></strong></div></div>
<div class="table-wrap"><table class="table"><thead><tr><th>No.</th><th>Nama</th><th>NIP</th><th>Peran</th><th>Divisi</th><th>Tanggung jawab</th></tr></thead><tbody><?php foreach ($event['tim'] as $i=>$t): ?><tr><td><?= $i+1 ?></td><td><?= mte_st_escape($t['nama']) ?><?php if ($t['kelompok']==='referensi'): ?><small class="st-block muted">Di luar jurusan</small><?php endif; ?></td><td class="st-nip"><?= mte_st_escape($t['nip'] ?: '-') ?></td><td><span class="st-role-badge <?= mte_st_escape($t['peran']) ?>"><?= mte_st_escape(ucfirst($t['peran'])) ?></span></td><td><?= mte_st_escape($t['divisi'] ?: '-') ?></td><td><?= nl2br(mte_st_escape($t['tanggung_jawab'] ?: '-')) ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php if ($event['catatan']): ?><div class="card"><h3>Catatan</h3><p><?= nl2br(mte_st_escape($event['catatan'])) ?></p></div><?php endif; ?>
<form method="post" class="st-delete no-print" data-confirm="Hapus kegiatan ini beserta seluruh penugasannya? Rekap dosen akan ikut berubah."><?= mte_st_csrf_field() ?><input type="hidden" name="action" value="delete_event"><input type="hidden" name="id" value="<?= (int)$event['id'] ?>"><input type="hidden" name="versi" value="<?= (int)$event['versi'] ?>"><button class="btn btn-danger" type="submit">Hapus kegiatan</button></form>
<?php elseif ($view === 'sebaran'): ?>
<div class="st-section-head"><div><h2>Sebaran penugasan per dosen</h2><p class="small muted">Setiap peran dihitung satu kali per kegiatan. Dosen tanpa penugasan tetap ditampilkan.</p></div><div class="st-actions no-print"><button type="button" class="btn" data-print>Cetak rekap</button><form method="post"><?= mte_st_csrf_field() ?><input type="hidden" name="action" value="export"><?php mte_filter_hidden($filter); ?><button type="submit" class="btn">Unduh CSV</button></form></div></div>
<?php mte_filter_form($filter,'sebaran'); $sums=['ketua'=>0,'sekretaris'=>0,'anggota'=>0,'total'=>0]; foreach($distribution as $r) foreach($sums as $key=>$sum) $sums[$key]+=(int)$r[$key]; ?>
<div class="st-stats"><div><span>Ketua</span><strong><?= $sums['ketua'] ?></strong></div><div><span>Sekretaris</span><strong><?= $sums['sekretaris'] ?></strong></div><div><span>Anggota</span><strong><?= $sums['anggota'] ?></strong></div><div><span>Total penugasan</span><strong><?= $sums['total'] ?></strong></div></div>
<p class="small muted st-print-context">Tahun: <?= mte_st_escape($filter['tahun'] ?: 'Semua tahun') ?>. Status: <?= mte_st_escape($filter['status_filter'] ?: 'Usulan + terbit') ?>. <?= $filter['q']?'Pencarian: '.mte_st_escape($filter['q']).'. ':'' ?><?= $filter['termasuk_referensi']?'Termasuk personel di luar jurusan.':'Hanya dosen jurusan.' ?> Klik nama untuk melihat rincian kegiatan.</p>
<div class="table-wrap"><table class="table st-recap"><thead><tr><th>No.</th><th>Nama dosen / personel</th><th>NIP</th><th class="st-num">Ketua</th><th class="st-num">Sekretaris</th><th class="st-num">Anggota</th><th class="st-num">Total</th><th>Komposisi peran</th></tr></thead><tbody>
<?php $maxTotal=$distribution?max(array_column($distribution,'total')):0; foreach($distribution as $i=>$r): ?><tr><td><?= $i+1 ?></td><td><a href="<?= mte_st_escape(mte_link(['view'=>'personel','id'=>$r['id']]+$filter)) ?>"><?= mte_st_escape($r['nama']) ?></a><?php if ($r['kelompok']==='referensi'): ?><small class="st-block muted">Di luar jurusan</small><?php endif; ?><?php if (!(int)$r['aktif']): ?><small class="st-block muted">Nonaktif</small><?php endif; ?></td><td class="st-nip"><?= mte_st_escape($r['nip'] ?: '-') ?></td><td class="st-num"><?= (int)$r['ketua'] ?></td><td class="st-num"><?= (int)$r['sekretaris'] ?></td><td class="st-num"><?= (int)$r['anggota'] ?></td><td class="st-num"><strong><?= (int)$r['total'] ?></strong></td><td><div class="st-bar" aria-label="<?= (int)$r['ketua'] ?> ketua, <?= (int)$r['sekretaris'] ?> sekretaris, <?= (int)$r['anggota'] ?> anggota"><?php foreach(['ketua','sekretaris','anggota'] as $role): ?><span class="<?= mte_st_escape($role) ?>" style="width:<?= $maxTotal?round((int)$r[$role]/$maxTotal*100,2):0 ?>%"></span><?php endforeach; ?></div></td></tr><?php endforeach; ?>
</tbody><tfoot><tr><th colspan="3">Total</th><th class="st-num"><?= $sums['ketua'] ?></th><th class="st-num"><?= $sums['sekretaris'] ?></th><th class="st-num"><?= $sums['anggota'] ?></th><th class="st-num"><?= $sums['total'] ?></th><th></th></tr></tfoot></table></div><div class="st-legend"><span><i class="ketua"></i>Ketua</span><span><i class="sekretaris"></i>Sekretaris</span><span><i class="anggota"></i>Anggota</span></div>
<?php elseif ($view === 'master'): ?>
<div class="st-section-head"><div><h2>Data Dosen</h2><p class="small muted"><?= $stats['dosen'] ?> dosen dan <?= $stats['referensi'] ?> personel di luar jurusan. Data awal dosen berasal dari Excel November 2026.</p></div><a class="btn" href="?view=master&amp;new_person=1">+ Tambah personel</a></div>
<?php if ($personForm): ?>
<form method="post" class="form-grid st-master-form"><?= mte_st_csrf_field() ?><input type="hidden" name="action" value="save_person"><input type="hidden" name="id" value="<?= mte_st_escape($personForm['id'] ?? '') ?>"><h3><?= !empty($personForm['id'])?'Ubah personel':'Tambah personel' ?></h3><div class="row-2"><label>Nama *<input type="text" name="nama" value="<?= mte_st_escape($personForm['nama'] ?? '') ?>" maxlength="200" required></label><label>NIP<input type="text" name="nip" value="<?= mte_st_escape($personForm['nip'] ?? '') ?>" inputmode="numeric" pattern="[0-9]{18}" maxlength="18" placeholder="18 digit, atau kosongkan"></label></div><div class="row-3"><label>Golongan<input type="text" name="golongan" value="<?= mte_st_escape($personForm['golongan'] ?? '') ?>" maxlength="20"></label><label>Status kepegawaian<input type="text" name="status_kepegawaian" value="<?= mte_st_escape($personForm['status_kepegawaian'] ?? '') ?>" maxlength="30" placeholder="PNS / PPPK / lainnya"></label><label>Kelompok<select name="kelompok"><option value="dosen" <?= ($personForm['kelompok'] ?? '')==='dosen'?'selected':'' ?>>Dosen jurusan</option><option value="referensi" <?= ($personForm['kelompok'] ?? '')==='referensi'?'selected':'' ?>>Personel di luar jurusan</option></select></label></div><label class="st-check"><input type="checkbox" name="aktif" value="1" <?= !empty($personForm['aktif'])?'checked':'' ?>> Aktif untuk penugasan baru</label><p class="small muted">Menonaktifkan personel tetap mempertahankan riwayat penugasannya.</p><div class="form-actions"><button class="btn btn-primary" type="submit">Simpan personel</button><a class="btn" href="?view=master">Batal</a></div></form>
<?php endif; ?>
<div class="st-filters no-print"><label>Kelompok<select id="st-master-group"><option value="">Semua personel</option><option value="dosen" selected>Dosen jurusan</option><option value="referensi">Di luar jurusan</option></select></label><label class="st-grow">Cari nama atau NIP<input id="st-master-search" type="search" placeholder="Cari dalam data dosen"></label></div>
<div class="table-wrap"><table class="table" id="st-master-table"><thead><tr><th>Nama</th><th>NIP</th><th>Golongan</th><th>Status kepegawaian</th><th>Kelompok</th><th>Keaktifan</th><th>Aksi</th></tr></thead><tbody><?php foreach($people as $p): ?><tr data-group="<?= mte_st_escape($p['kelompok']) ?>" data-search="<?= mte_st_escape(mb_strtolower($p['nama'].' '.$p['nip'],'UTF-8')) ?>"><td><?= mte_st_escape($p['nama']) ?></td><td class="st-nip"><?= mte_st_escape($p['nip'] ?: '-') ?></td><td><?= mte_st_escape($p['golongan'] ?: '-') ?></td><td><?= mte_st_escape($p['status_kepegawaian'] ?: '-') ?></td><td><?= $p['kelompok']==='dosen'?'Dosen jurusan':'Di luar jurusan' ?></td><td><?= (int)$p['aktif']?'Aktif':'Nonaktif' ?></td><td><a class="btn btn-sm" href="<?= mte_st_escape(mte_link(['view'=>'master','edit_person'=>$p['id']])) ?>">Ubah</a></td></tr><?php endforeach; ?></tbody></table></div><p class="small muted" id="st-master-count"></p><p class="small muted">NIP dapat dilengkapi dan kelompok dapat diperbarui setelah verifikasi data dosen.</p>
<?php elseif ($view === 'personel' && $person): ?>
<div class="st-section-head"><div><h2><?= mte_st_escape($person['nama']) ?></h2><p class="muted">NIP <?= mte_st_escape($person['nip'] ?: 'belum tersedia') ?><?= $person['kelompok']==='referensi'?' · Di luar jurusan':'' ?></p></div><a class="btn no-print" href="<?= mte_st_escape(mte_link(['view'=>'sebaran']+$filter)) ?>">Kembali ke sebaran</a></div>
<?php mte_filter_form($filter,'personel',$person['id']); $assignments=[]; $roleCounts=['ketua'=>0,'sekretaris'=>0,'anggota'=>0]; foreach($events as $k) foreach($k['tim'] as $t) if ((int)$t['personel_id']===(int)$person['id']) {$assignments[]=['k'=>$k,'t'=>$t];$roleCounts[$t['peran']]++;} ?>
<div class="st-stats"><?php foreach($roleCounts as $role=>$count): ?><div><span><?= mte_st_escape(ucfirst($role)) ?></span><strong><?= $count ?></strong></div><?php endforeach; ?><div><span>Total penugasan</span><strong><?= count($assignments) ?></strong></div></div>
<div class="table-wrap"><table class="table"><thead><tr><th>Kegiatan</th><th>Waktu</th><th>Peran</th><th>Divisi</th></tr></thead><tbody><?php foreach($assignments as $a): ?><tr><td><a href="<?= mte_st_escape(mte_link(['view'=>'detail','id'=>$a['k']['id']])) ?>"><?= mte_st_escape($a['k']['nama']) ?></a></td><td><?= mte_st_escape(mte_period_label($a['k'])) ?></td><td><?= mte_st_escape(ucfirst($a['t']['peran'])) ?></td><td><?= mte_st_escape($a['t']['divisi'] ?: '-') ?></td></tr><?php endforeach; ?><?php if (!$assignments): ?><tr><td colspan="4" class="st-empty">Belum ada penugasan pada filter ini.</td></tr><?php endif; ?></tbody></table></div>
<?php endif; ?>
</main><footer class="footer">Magister Teknik Elektro · Universitas Jenderal Soedirman</footer>
</body></html>
