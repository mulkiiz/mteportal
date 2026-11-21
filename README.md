# Portal Magister Teknik Elektro UNSOED

Portal publik, dashboard pengelola, Kelola Surat Tugas, dan Simulasi Akreditasi. Aplikasi memakai PHP dengan mysqli, Apache dengan `mod_rewrite`, dan database MySQL atau MariaDB `mte_portal`.

## Menjalankan di XAMPP

1. Salin folder `mte/` ke `C:\xampp\htdocs\mte`. Pastikan `.htaccess` ikut tersalin dan Apache mengizinkan aturan rewrite dari berkas tersebut.
2. Buat database `mte_portal` dengan `utf8mb4`. Simpan konfigurasi di `C:\xampp\mte-private\config.php`, di luar `htdocs`. Untuk penempatan lain, tentukan lokasi konfigurasi dengan variabel lingkungan `MTE_CONFIG_PATH`.

   ```php
   <?php
   return [
       'host' => '127.0.0.1',
       'port' => 3306,
       'database' => 'mte_portal',
       'username' => 'mte_portal_app',
       'password' => 'ISI_PASSWORD_AKUN_DATABASE',
       'st_upload_dir' => 'C:/xampp/mte-private/st_files',
   ];
   ```

3. Untuk instalasi baru, gunakan konfigurasi CLI dengan izin membuat tabel dan jalankan kedua installer. Untuk pemindahan instalasi lama, impor ketiga tabel `mte_portal_*` dan kelima tabel `mte_st_*` beserta datanya. Aplikasi web cukup diberi izin membaca dan menulis pada database MTE.

   ```powershell
   C:\xampp\php\php.exe C:\xampp\htdocs\mte\install_portal.php
   C:\xampp\php\php.exe C:\xampp\htdocs\mte\install.php
   ```

4. Pindahkan juga berkas publik dari `unggah/` ke folder `unggah/` di instalasi baru. Pindahkan PDF Surat Tugas ke direktori privat yang ditentukan pada `st_upload_dir`. Proses PHP harus dapat menulis ke kedua direktori.

Installer portal membuat akun awal hanya jika database masih kosong. Ganti kredensial awal sebelum membuka akses publik. Instalasi ST yang baru dimulai dengan daftar kosong. Jika diperlukan, installer ST menerima argumen berupa path ke berkas seed lokal yang tepercaya.

Konfigurasi, data dosen, cadangan database, dan unggahan tidak disimpan di Git. Saat mencadangkan satu instalasi, simpan database, folder `unggah/`, dan direktori PDF ST bersama-sama.

## Alamat localhost

- Portal: `http://localhost/mte/`
- Masuk: `http://localhost/mte/masuk`
- Dashboard: `http://localhost/mte/admin/`
- Kelola Surat Tugas: `http://localhost/mte/kelola-st/`
- Simulasi Akreditasi: `http://localhost/mte/simulasi-akreditasi/`
