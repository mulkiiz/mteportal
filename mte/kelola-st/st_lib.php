<?php
// Kelola ST memakai koneksi database portal MTE.
require_once __DIR__ . '/../includes/db.php';

function mte_st_escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function mte_stmt($sql, $types = '', $params = []) {
    $stmt = mte_db()->prepare($sql);
    if (!$stmt) throw new RuntimeException('Gagal menyiapkan kueri ST: ' . mte_db()->error);
    if ($types !== '') $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Gagal menjalankan kueri ST: ' . $error);
    }
    return $stmt;
}
function mte_rows($sql, $types = '', $params = []) {
    $stmt = mte_stmt($sql, $types, $params);
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}
function mte_one($sql, $types = '', $params = []) {
    $rows = mte_rows($sql, $types, $params);
    return $rows[0] ?? null;
}
function mte_exec($sql, $types = '', $params = []) {
    $stmt = mte_stmt($sql, $types, $params);
    $result = ['id' => (int)mte_db()->insert_id, 'affected' => $stmt->affected_rows];
    $stmt->close();
    return $result;
}
function mte_text($value, $max, $label) {
    if (!is_scalar($value) && $value !== null) throw new InvalidArgumentException($label . ' tidak valid.');
    $value = trim((string)$value);
    if (mb_strlen($value, 'UTF-8') > $max) throw new InvalidArgumentException($label . ' maksimal ' . $max . ' karakter.');
    return $value;
}
function mte_id($value) {
    return is_scalar($value) && preg_match('/^[1-9][0-9]{0,9}$/D', (string)$value) ? (int)$value : 0;
}
function mte_date($value, $precision, $end = false) {
    $value = mte_text($value, 10, 'Waktu');
    $format = $precision === 'bulan' ? 'Y-m' : 'Y-m-d';
    $date = DateTimeImmutable::createFromFormat('!' . $format, $value);
    if (!$date || $date->format($format) !== $value || (int)$date->format('Y') < 1900 || (int)$date->format('Y') > 2200) {
        throw new InvalidArgumentException('Isi waktu yang valid antara tahun 1900 dan 2200.');
    }
    return ($precision === 'bulan' && $end ? $date->modify('last day of this month') : $date)->format('Y-m-d');
}
function mte_period_label($event) {
    $months = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $format = function ($s) use ($months, $event) {
        $d = new DateTimeImmutable($s);
        return ($event['presisi_waktu'] === 'tanggal' ? $d->format('j') . ' ' : '') . $months[(int)$d->format('n')] . ' ' . $d->format('Y');
    };
    $start = $format($event['tanggal_mulai']);
    $end = $format($event['tanggal_selesai']);
    return $start === $end ? $start : $start . ' - ' . $end;
}
function mte_event($id) {
    $event = mte_one('SELECT * FROM mte_st_kegiatan WHERE id=?', 'i', [$id]);
    if ($event) $event['tim'] = mte_rows("SELECT t.*, p.nama, p.nip, p.kelompok, p.aktif FROM mte_st_penugasan t JOIN mte_st_personel p ON p.id=t.personel_id WHERE t.kegiatan_id=? ORDER BY FIELD(t.peran,'ketua','sekretaris','anggota'), t.urutan, t.id", 'i', [$id]);
    return $event;
}
function mte_validate_event($input, $existing = null) {
    $name = mte_text($input['nama'] ?? '', 255, 'Nama kegiatan');
    if ($name === '') throw new InvalidArgumentException('Nama kegiatan wajib diisi.');
    $precision = $input['presisi_waktu'] ?? '';
    if (!in_array($precision, ['bulan','tanggal'], true)) throw new InvalidArgumentException('Pilih jenis waktu.');
    $start = mte_date($input['mulai'] ?? '', $precision);
    $end = mte_date($input['selesai'] ?? '', $precision, true);
    if ($end < $start) throw new InvalidArgumentException('Waktu selesai tidak boleh sebelum waktu mulai.');
    $status = $input['status'] ?? '';
    if (!in_array($status, ['usulan','terbit','dibatalkan'], true)) throw new InvalidArgumentException('Status kegiatan tidak valid.');
    $number = mte_text($input['nomor_st'] ?? '', 120, 'Nomor ST');
    if ($status === 'terbit' && $number === '') throw new InvalidArgumentException('Nomor ST wajib diisi untuk status Terbit.');
    $team = $input['tim'] ?? [];
    if (!is_array($team) || count($team) < 3 || count($team) > 200) throw new InvalidArgumentException('Isi satu ketua, satu sekretaris, dan minimal satu anggota (maksimal 200 personel).');
    $counts = ['ketua'=>0,'sekretaris'=>0,'anggota'=>0];
    $seen = [];
    $normalized = [];
    $existingIds = $existing ? array_column($existing['tim'], 'personel_id') : [];
    foreach ($team as $row) {
        if (!is_array($row)) throw new InvalidArgumentException('Susunan tim tidak valid.');
        $id = mte_id($row['personel_id'] ?? '');
        $role = $row['peran'] ?? '';
        if (!$id || !is_string($role) || !isset($counts[$role])) throw new InvalidArgumentException('Pilih nama dari autocomplete dan tentukan perannya.');
        if (isset($seen[$id])) throw new InvalidArgumentException('Satu orang hanya boleh memegang satu peran dalam kegiatan yang sama.');
        $person = mte_one('SELECT id,aktif FROM mte_st_personel WHERE id=?', 'i', [$id]);
        if (!$person || (!(int)$person['aktif'] && !in_array($id, array_map('intval', $existingIds), true))) throw new InvalidArgumentException('Personel tidak ditemukan atau sudah nonaktif. Pilih dari data dosen aktif.');
        $seen[$id] = true;
        $counts[$role]++;
        $normalized[] = ['personel_id'=>$id, 'peran'=>$role, 'divisi'=>mte_text($row['divisi'] ?? '', 200, 'Divisi'), 'tanggung_jawab'=>mte_text($row['tanggung_jawab'] ?? '', 1000, 'Tanggung jawab')];
    }
    if ($counts['ketua'] !== 1 || $counts['sekretaris'] !== 1 || $counts['anggota'] < 1) throw new InvalidArgumentException('Susunan harus berisi tepat satu ketua, satu sekretaris, dan minimal satu anggota.');
    return ['nama'=>$name,'nomor_st'=>$number,'status'=>$status,'tanggal_mulai'=>$start,'tanggal_selesai'=>$end,'presisi_waktu'=>$precision,'tempat'=>mte_text($input['tempat'] ?? '',255,'Tempat'),'catatan'=>mte_text($input['catatan'] ?? '',10000,'Catatan'),'tim'=>$normalized];
}
function mte_save_event($input, $uid) {
    $id = mte_id($input['id'] ?? '');
    mte_db()->begin_transaction();
    try {
        $existing = null;
        if ($id) {
            $lock = mte_one('SELECT id,versi FROM mte_st_kegiatan WHERE id=? FOR UPDATE', 'i', [$id]);
            if (!$lock) throw new InvalidArgumentException('Kegiatan tidak ditemukan.');
            if ((int)$lock['versi'] !== mte_id($input['versi'] ?? '')) throw new InvalidArgumentException('Kegiatan sudah diubah di jendela lain. Muat ulang data sebelum menyimpan.');
            $existing = mte_event($id);
        }
        $data = mte_validate_event($input, $existing);
        $params = [$data['nama'],$data['nomor_st'],$data['status'],$data['tanggal_mulai'],$data['tanggal_selesai'],$data['presisi_waktu'],$data['tempat'],$data['catatan']];
        if ($id) {
            $params[] = $id;
            mte_exec('UPDATE mte_st_kegiatan SET nama=?,nomor_st=?,status=?,tanggal_mulai=?,tanggal_selesai=?,presisi_waktu=?,tempat=?,catatan=?,versi=versi+1 WHERE id=?','ssssssssi',$params);
            mte_exec('DELETE FROM mte_st_penugasan WHERE kegiatan_id=?','i',[$id]);
        } else {
            $params[] = $uid;
            $id = mte_exec('INSERT INTO mte_st_kegiatan (nama,nomor_st,status,tanggal_mulai,tanggal_selesai,presisi_waktu,tempat,catatan,dibuat_oleh) VALUES (?,?,?,?,?,?,?,?,?)','ssssssssi',$params)['id'];
        }
        foreach ($data['tim'] as $order=>$person) mte_exec('INSERT INTO mte_st_penugasan (kegiatan_id,personel_id,peran,urutan,divisi,tanggung_jawab) VALUES (?,?,?,?,?,?)','iisiss',[$id,$person['personel_id'],$person['peran'],$order,$person['divisi'],$person['tanggung_jawab']]);
        mte_db()->commit();
        return $id;
    } catch (Throwable $e) { mte_db()->rollback(); throw $e; }
}
function mte_delete_event($id, $version) {
    mte_db()->begin_transaction();
    try {
        mte_one('SELECT id FROM mte_st_kegiatan WHERE id=? FOR UPDATE','i',[$id]);
        $document = mte_one('SELECT nama_simpan FROM mte_st_dokumen WHERE kegiatan_id=?','i',[$id]);
        $result = mte_exec('DELETE FROM mte_st_kegiatan WHERE id=? AND versi=?','ii',[$id,$version]);
        if ($result['affected'] !== 1) throw new InvalidArgumentException('Data sudah berubah atau dihapus. Muat ulang halaman.');
        mte_db()->commit();
    } catch (Throwable $e) { mte_db()->rollback(); throw $e; }
    if ($document) mte_remove_pdf($document['nama_simpan']);
}
function mte_pdf_dir() {
    return mte_config()['st_upload_dir'];
}
function mte_pdf_path($name) {
    if (!preg_match('/^[a-f0-9]{32}\.pdf$/D',$name)) throw new RuntimeException('Nama simpan PDF tidak valid.');
    return mte_pdf_dir().'/'.$name;
}
function mte_remove_pdf($name) {
    $path=mte_pdf_path($name);
    if (is_file($path) && !unlink($path)) error_log('[MTE ST] Gagal membersihkan PDF lama.');
}
function mte_upload_pdf($id, $file) {
    if (!$id || !is_array($file)) throw new InvalidArgumentException('Pilih kegiatan dan PDF ST.');
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new InvalidArgumentException('Unggah gagal. Pilih PDF ST maksimal 2 MB.');
    $tmp=$file['tmp_name'] ?? '';
    if (!is_string($tmp) || !is_uploaded_file($tmp)) throw new InvalidArgumentException('Berkas unggahan tidak valid.');
    $size=filesize($tmp);
    $name=mte_text(basename(str_replace('\\','/',(string)($file['name'] ?? ''))),255,'Nama berkas');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    if ($size<5 || $size>2*1024*1024 || strtolower(pathinfo($name,PATHINFO_EXTENSION))!=='pdf' || $mime!=='application/pdf' || file_get_contents($tmp,false,null,0,5)!=='%PDF-') throw new InvalidArgumentException('Berkas harus PDF yang valid, maksimal 2 MB.');
    $dir=mte_pdf_dir();
    if (!is_dir($dir) && !mkdir($dir,0750,true)) throw new RuntimeException('Folder PDF tidak dapat dibuat.');
    if (file_put_contents($dir.'/.htaccess',"Require all denied\n")===false) throw new RuntimeException('Folder PDF tidak dapat dilindungi.');
    $stored=bin2hex(random_bytes(16)).'.pdf';
    $path=mte_pdf_path($stored);
    mte_db()->begin_transaction();
    try {
        if (!mte_one('SELECT id FROM mte_st_kegiatan WHERE id=? FOR UPDATE','i',[$id])) throw new InvalidArgumentException('Kegiatan tidak ditemukan.');
        $old=mte_one('SELECT nama_simpan FROM mte_st_dokumen WHERE kegiatan_id=?','i',[$id]);
        if (!move_uploaded_file($tmp,$path)) throw new RuntimeException('PDF tidak dapat disimpan.');
        mte_exec('INSERT INTO mte_st_dokumen (kegiatan_id,nama_asli,nama_simpan,ukuran) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE nama_asli=VALUES(nama_asli),nama_simpan=VALUES(nama_simpan),ukuran=VALUES(ukuran),diunggah_pada=CURRENT_TIMESTAMP','issi',[$id,$name,$stored,$size]);
        mte_exec('UPDATE mte_st_kegiatan SET versi=versi+1 WHERE id=?','i',[$id]);
        mte_db()->commit();
    } catch (Throwable $e) { mte_db()->rollback(); if (is_file($path)) mte_remove_pdf($stored); throw $e; }
    if ($old) mte_remove_pdf($old['nama_simpan']);
}
function mte_save_person($input) {
    $id = mte_id($input['id'] ?? '');
    $name = mte_text($input['nama'] ?? '',200,'Nama');
    if ($name === '') throw new InvalidArgumentException('Nama wajib diisi.');
    $nip = mte_text($input['nip'] ?? '',30,'NIP');
    if ($nip !== '' && !preg_match('/^[0-9]{18}$/D',$nip)) throw new InvalidArgumentException('NIP harus 18 digit atau dikosongkan jika belum tersedia.');
    $group = $input['kelompok'] ?? '';
    if (!in_array($group,['dosen','referensi'],true)) throw new InvalidArgumentException('Kelompok personel tidak valid.');
    if ($nip !== '' && mte_one('SELECT id FROM mte_st_personel WHERE nip=? AND id<>?','si',[$nip,$id])) throw new InvalidArgumentException('NIP sudah digunakan personel lain.');
    $params = [$name,$nip === '' ? null : $nip,mte_text($input['golongan'] ?? '',20,'Golongan'),mte_text($input['status_kepegawaian'] ?? '',30,'Status kepegawaian'),$group,isset($input['aktif']) ? 1 : 0];
    if ($id) {
        if (!mte_one('SELECT id FROM mte_st_personel WHERE id=?','i',[$id])) throw new InvalidArgumentException('Personel tidak ditemukan.');
        $params[] = $id;
        mte_exec('UPDATE mte_st_personel SET nama=?,nip=?,golongan=?,status_kepegawaian=?,kelompok=?,aktif=? WHERE id=?','sssssii',$params);
    } else {
        mte_exec("INSERT INTO mte_st_personel (nama,nip,golongan,status_kepegawaian,kelompok,aktif,sumber) VALUES (?,?,?,?,?,?,'Input manual')",'sssssi',$params);
    }
}
function mte_filters($input) {
    $year = mte_text($input['tahun'] ?? '',4,'Tahun');
    if ($year !== '' && (!ctype_digit($year) || (int)$year < 1900 || (int)$year > 2200)) throw new InvalidArgumentException('Tahun filter tidak valid.');
    $status = mte_text($input['status_filter'] ?? '',20,'Status filter');
    if (!in_array($status,['','usulan','terbit','dibatalkan','semua'],true)) throw new InvalidArgumentException('Status filter tidak valid.');
    $search = mte_text($input['q'] ?? '',150,'Pencarian');
    return ['tahun'=>$year,'status_filter'=>$status,'q'=>$search,'termasuk_referensi'=>!empty($input['termasuk_referensi'])];
}
function mte_where($filter, $alias = 'k') {
    $clauses = [];
    $types = '';
    $params = [];
    if ($filter['tahun'] !== '') {
        $clauses[] = "$alias.tanggal_mulai<=? AND $alias.tanggal_selesai>=?";
        $types .= 'ss';
        $params[] = $filter['tahun'].'-12-31';
        $params[] = $filter['tahun'].'-01-01';
    }
    if ($filter['status_filter'] === '') $clauses[] = "$alias.status<>'dibatalkan'";
    elseif ($filter['status_filter'] !== 'semua') {
        $clauses[] = "$alias.status=?";
        $types .= 's';
        $params[] = $filter['status_filter'];
    }
    if ($filter['q'] !== '') {
        $clauses[] = "($alias.nama LIKE ? OR $alias.nomor_st LIKE ?)";
        $types .= 'ss';
        $needle = '%'.str_replace(['!','%','_'],['!!','!%','!_'],$filter['q']).'%';
        // Explicit ESCAPE keeps literal %, _ and backslashes predictable.
        $clauses[count($clauses)-1] = "($alias.nama LIKE ? ESCAPE '!' OR $alias.nomor_st LIKE ? ESCAPE '!')";
        array_push($params,$needle,$needle);
    }
    return [$clauses ? implode(' AND ',$clauses) : '1=1',$types,$params];
}
function mte_events($filter) {
    list($where,$types,$params) = mte_where($filter);
    $events = mte_rows("SELECT k.* FROM mte_st_kegiatan k WHERE $where ORDER BY k.tanggal_mulai DESC,k.id DESC",$types,$params);
    $teams = mte_rows("SELECT t.*,p.nama,p.nip,p.kelompok FROM mte_st_penugasan t JOIN mte_st_personel p ON p.id=t.personel_id JOIN mte_st_kegiatan k ON k.id=t.kegiatan_id WHERE $where ORDER BY FIELD(t.peran,'ketua','sekretaris','anggota'),t.urutan,t.id",$types,$params);
    $byId = [];
    foreach ($teams as $row) $byId[$row['kegiatan_id']][] = $row;
    foreach ($events as &$event) $event['tim'] = $byId[$event['id']] ?? [];
    unset($event);
    return $events;
}
function mte_distribution($filter) {
    list($where,$types,$params) = mte_where($filter);
    $group = $filter['termasuk_referensi'] ? '1=1' : "p.kelompok='dosen'";
    return mte_rows("SELECT p.id,p.nama,p.nip,p.kelompok,p.aktif,COALESCE(x.ketua,0) ketua,COALESCE(x.sekretaris,0) sekretaris,COALESCE(x.anggota,0) anggota,COALESCE(x.total,0) total FROM mte_st_personel p LEFT JOIN (SELECT t.personel_id,SUM(t.peran='ketua') ketua,SUM(t.peran='sekretaris') sekretaris,SUM(t.peran='anggota') anggota,COUNT(*) total FROM mte_st_penugasan t JOIN mte_st_kegiatan k ON k.id=t.kegiatan_id WHERE $where GROUP BY t.personel_id) x ON x.personel_id=p.id WHERE $group ORDER BY total DESC,p.nama,p.id",$types,$params);
}
function mte_csv_cell($value) {
    $value = (string)$value;
    return preg_match('/^[\s]*[=+@\-\t\r\n]/u',$value) ? "'".$value : $value;
}
function mte_install(?string $seedPath = null) {
    if (PHP_SAPI !== 'cli') throw new RuntimeException('Instalasi hanya melalui CLI.');
    $schema = file_get_contents(__DIR__.'/schema.sql');
    foreach (preg_split('/;\s*(?:\r?\n|$)/',$schema) as $sql) {
        if (trim($sql) !== '' && !mte_db()->query($sql)) throw new RuntimeException('Gagal membuat tabel ST: '.mte_db()->error);
    }
    if ($seedPath !== null) {
        mte_import_st_seed($seedPath);
    }
    return mte_one("SELECT (SELECT COUNT(*) FROM mte_st_personel WHERE kelompok='dosen') dosen,(SELECT COUNT(*) FROM mte_st_personel WHERE kelompok='referensi') di_luar_master,(SELECT COUNT(*) FROM mte_st_kegiatan) kegiatan,(SELECT COUNT(*) FROM mte_st_penugasan) penugasan");
}

function mte_import_st_seed(string $seedPath): void {
    if (!is_file($seedPath) || !is_readable($seedPath)) throw new RuntimeException('Berkas seed ST tidak dapat dibaca.');
    mte_exec("INSERT IGNORE INTO mte_st_meta (nama,nilai) VALUES ('seed_nov2026','')");
    mte_db()->begin_transaction();
    try {
        $marker = mte_one("SELECT nilai FROM mte_st_meta WHERE nama='seed_nov2026' FOR UPDATE");
        if ($marker['nilai'] === '') {
            $seed = require $seedPath;
            if (!is_array($seed)) throw new RuntimeException('Data seed tidak valid.');
            $ids = [];
            foreach ($seed['personel'] as $p) {
                $ids[$p['kode']] = mte_exec('INSERT INTO mte_st_personel (kode_sumber,nama,nip,golongan,status_kepegawaian,kelompok,sumber) VALUES (?,?,?,?,?,?,?)','sssssss',[$p['kode'],$p['nama'],$p['nip'],$p['golongan'],$p['status_kepegawaian'],$p['kelompok'],$p['sumber']])['id'];
            }
            foreach ($seed['kegiatan'] as $k) {
                $id = mte_exec("INSERT INTO mte_st_kegiatan (kode_sumber,nama,tanggal_mulai,tanggal_selesai,presisi_waktu,status,catatan) VALUES (?,?,?,?,'bulan','usulan',?)",'sssss',[$k['kode'],$k['nama'],$k['mulai'],$k['selesai'],'Sumber: Usul Surat Tugas MTE November 2026.pdf; 16 November 2026. Nama singkat dipadankan ke dosen jurusan Excel.'])['id'];
                foreach ($k['tim'] as $order=>$t) mte_exec('INSERT INTO mte_st_penugasan (kegiatan_id,personel_id,peran,urutan,divisi,tanggung_jawab) VALUES (?,?,?,?,?,?)','iisiss',[$id,$ids[$t['personel']],$t['peran'],$order,$t['divisi'],$t['tanggung_jawab']]);
            }
            mte_exec("UPDATE mte_st_meta SET nilai='1' WHERE nama='seed_nov2026'");
        }
        mte_db()->commit();
    } catch (Throwable $e) { mte_db()->rollback(); throw $e; }
}
