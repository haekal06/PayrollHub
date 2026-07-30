# PayrollHub

PayrollHub adalah aplikasi web berbasis Laravel untuk mengelola data pegawai, kalender kerja, absensi, lembur, dan penggajian perusahaan.

## Teknologi

- PHP 8.3
- Laravel 13
- MySQL 8
- Composer
- PhpSpreadsheet
- Laragon
- Visual Studio Code

## Fitur Admin HRD

- Login dan logout.
- Manajemen jabatan.
- Pengaturan gaji pokok, tunjangan, dan tarif lembur.
- Manajemen data pegawai.
- Pembuatan NIP otomatis dengan format `KRY-001`.
- Pembuatan akun login pegawai.
- Manajemen akun Admin HRD cadangan.
- Kalender kerja bulanan.
- Pengaturan hari kerja, akhir pekan, libur nasional, dan libur perusahaan.
- Absensi manual.
- Absensi massal.
- Import absensi melalui Excel.
- Pratinjau dan validasi data sebelum import.
- Pencatatan jam serta keterangan lembur.
- Rekap absensi bulanan.
- Detail absensi harian.
- Proses penggajian otomatis berdasarkan kalender kerja dan absensi.
- Bonus dan potongan tambahan beserta keterangannya.
- Finalisasi penggajian.
- Penguncian absensi setelah finalisasi.
- Membuka penggajian untuk revisi.
- Menandai penggajian sudah dibayar.
- Cetak slip gaji.
- Cetak rekap penggajian.
- Cetak rekap dan detail absensi.
- Cetak kalender kerja.

## Fitur Pegawai

- Login menggunakan akun pegawai.
- Melihat daftar slip gaji sendiri.
- Melihat rincian penggajian.
- Melihat detail absensi per tanggal.
- Melihat status hadir, sakit, izin, cuti, atau alpa.
- Melihat jam dan keterangan lembur.
- Mencetak slip gaji.
- Tidak dapat mengakses data pegawai lain.
- Tidak dapat mengakses halaman Admin HRD.

## Status Penggajian

- `draf`: penggajian masih dapat diperiksa.
- `revisi`: periode dibuka kembali untuk perbaikan absensi.
- `final`: nilai gaji sudah disahkan dan absensi terkunci.
- `dibayar`: penggajian sudah dibayarkan kepada pegawai.

## Alur Penggunaan

1. Admin membuat data jabatan.
2. Admin membuat data pegawai.
3. Sistem menghasilkan NIP otomatis.
4. Admin membuat akun login pegawai.
5. Admin membuat Kalender Kerja.
6. Admin mencatat absensi manual, massal, atau melalui Excel.
7. Admin mencatat jam lembur pada absensi.
8. Admin memproses penggajian.
9. Sistem menghitung absensi, lembur, pendapatan, dan potongan.
10. Admin memeriksa draf penggajian.
11. Admin melakukan finalisasi.
12. Absensi periode tersebut terkunci.
13. Pegawai dapat melihat dan mencetak slip gaji.
14. Admin menandai penggajian sudah dibayar.
15. Jika terdapat kesalahan, penggajian dapat dibuka untuk revisi.

## Persyaratan

Pastikan perangkat sudah memiliki:

- PHP minimal 8.3
- Composer
- MySQL 8
- Ekstensi PHP `pdo_mysql`
- Ekstensi PHP `mbstring`
- Ekstensi PHP `openssl`
- Ekstensi PHP `zip`
- Ekstensi PHP `gd`

## Instalasi

Clone repository:

```bash
git clone https://github.com/haekal06/PayrollHub.git
cd PayrollHub
```

Pasang dependency:

```bash
composer install
```

Salin konfigurasi environment pada Windows:

```powershell
Copy-Item .env.example .env
```

Buat application key:

```bash
php artisan key:generate
```

Buat database MySQL dengan nama:

```text
payrollhub
```

Jalankan migration dan seeder:

```bash
php artisan migrate --seed
```

Bersihkan cache aplikasi:

```bash
php artisan optimize:clear
```

Jalankan aplikasi:

```bash
php artisan serve
```

Buka aplikasi melalui:

```text
http://127.0.0.1:8000/login
```

## Akun Admin Awal

```text
Email    : admin@payrollhub.test
Password : admin123
```

Password tersebut hanya digunakan untuk pengembangan dan demonstrasi lokal.

## Menjalankan Pengujian

```bash
php artisan test
```

## Keamanan Repository

File berikut tidak disimpan di GitHub:

- `.env`
- `vendor`
- `node_modules`
- cache aplikasi
- session lokal
- log aplikasi
- database lokal

Gunakan `.env.example` sebagai contoh konfigurasi dan jangan menyimpan password produksi ke repository.