<?php

declare(strict_types=1);

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AbsensiMassalController;
use App\Http\Controllers\AkunPegawaiController;
use App\Http\Controllers\AutentikasiController;
use App\Http\Controllers\ImportAbsensiController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\KalenderKerjaController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PenggajianController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\SlipPenggajianController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')
    ->group(function (): void {
        Route::get(
            '/login',
            [AutentikasiController::class, 'tampilkanLogin']
        )->name('login');

        Route::post(
            '/login',
            [AutentikasiController::class, 'autentikasi']
        )
            ->middleware('throttle:6,1')
            ->name('login.autentikasi');
    });

Route::middleware(['auth', 'aktif'])
    ->group(function (): void {
        Route::view(
            '/dashboard',
            'dashboard'
        )->name('dashboard');

        Route::get(
            '/slip-gaji',
            [SlipPenggajianController::class, 'index']
        )->name('pegawai.slip-gaji.index');

        Route::get(
            '/slip-gaji/{penggajian}/cetak',
            [SlipPenggajianController::class, 'cetak']
        )->name('pegawai.slip-gaji.cetak');

        Route::get(
            '/slip-gaji/{penggajian}',
            [SlipPenggajianController::class, 'show']
        )->name('pegawai.slip-gaji.show');

        Route::post(
            '/logout',
            [AutentikasiController::class, 'keluar']
        )->name('logout');
    });

Route::middleware(['auth', 'aktif', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        /*
        |--------------------------------------------------------------------------
        | Jabatan
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'jabatan',
            JabatanController::class
        )->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | Pegawai dan Akun Pegawai
        |--------------------------------------------------------------------------
        */

        Route::get(
            'pegawai/{pegawai}/akun/create',
            [AkunPegawaiController::class, 'create']
        )->name('pegawai.akun.create');

        Route::post(
            'pegawai/{pegawai}/akun',
            [AkunPegawaiController::class, 'store']
        )->name('pegawai.akun.store');

        Route::resource(
            'pegawai',
            PegawaiController::class
        )->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | Kalender Kerja
        |--------------------------------------------------------------------------
        */

        Route::get(
            'kalender-kerja',
            [KalenderKerjaController::class, 'index']
        )->name('kalender-kerja.index');

        Route::get(
            'kalender-kerja/cetak',
            [KalenderKerjaController::class, 'cetak']
        )->name('kalender-kerja.cetak');

        Route::post(
            'kalender-kerja/buat',
            [KalenderKerjaController::class, 'buat']
        )->name('kalender-kerja.buat');

        Route::put(
            'kalender-kerja/bulan',
            [KalenderKerjaController::class, 'perbaruiBulan']
        )->name('kalender-kerja.perbarui-bulan');

        /*
        |--------------------------------------------------------------------------
        | Absensi Massal
        |--------------------------------------------------------------------------
        */

        Route::get(
            'absensi/massal',
            [AbsensiMassalController::class, 'create']
        )->name('absensi.massal.create');

        Route::post(
            'absensi/massal',
            [AbsensiMassalController::class, 'store']
        )->name('absensi.massal.store');

        /*
        |--------------------------------------------------------------------------
        | Import Absensi
        |--------------------------------------------------------------------------
        */

        Route::get(
            'absensi/import',
            [ImportAbsensiController::class, 'index']
        )->name('import-absensi.index');

        Route::get(
            'absensi/import/template',
            [ImportAbsensiController::class, 'unduhTemplate']
        )->name('import-absensi.template');

        Route::post(
            'absensi/import/pratinjau',
            [ImportAbsensiController::class, 'pratinjau']
        )->name('import-absensi.pratinjau');

        Route::get(
            'import-absensi/{importAbsensi}',
            [ImportAbsensiController::class, 'show']
        )->name('import-absensi.show');

        Route::post(
            'import-absensi/{importAbsensi}/konfirmasi',
            [ImportAbsensiController::class, 'konfirmasi']
        )->name('import-absensi.konfirmasi');

        Route::patch(
            'import-absensi/{importAbsensi}/batalkan',
            [ImportAbsensiController::class, 'batalkan']
        )->name('import-absensi.batalkan');

        /*
        |--------------------------------------------------------------------------
        | Absensi Manual
        |--------------------------------------------------------------------------
        */

        Route::get(
            'absensi/cetak-rekap',
            [AbsensiController::class, 'cetakRekap']
        )->name('absensi.cetak-rekap');

        Route::get(
            'absensi/cetak-detail',
            [AbsensiController::class, 'cetakDetail']
        )->name('absensi.cetak-detail');

        Route::resource(
            'absensi',
            AbsensiController::class
        )->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | Penggajian
        |--------------------------------------------------------------------------
        */

        Route::patch(
            'penggajian/{penggajian}/finalisasi',
            [PenggajianController::class, 'finalisasi']
        )->name('penggajian.finalisasi');

        Route::patch(
            'penggajian/{penggajian}/buka-revisi',
            [PenggajianController::class, 'bukaRevisi']
        )->name('penggajian.buka-revisi');

        Route::patch(
            'penggajian/{penggajian}/dibayar',
            [PenggajianController::class, 'tandaiDibayar']
        )->name('penggajian.dibayar');

        Route::get(
            'penggajian/cetak-rekap',
            [PenggajianController::class, 'cetakRekap']
        )->name('penggajian.cetak-rekap');

        Route::get(
            'penggajian/{penggajian}/cetak-slip',
            [PenggajianController::class, 'cetakSlip']
        )->name('penggajian.cetak-slip');

        Route::resource(
            'penggajian',
            PenggajianController::class
        )->only([
            'index',
            'create',
            'store',
            'show',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Pengguna
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'pengguna',
            PenggunaController::class
        )->only([
            'index',
            'create',
            'store',
            'edit',
            'update',
        ]);
    });
