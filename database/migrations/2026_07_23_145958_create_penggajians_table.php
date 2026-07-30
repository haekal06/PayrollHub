<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'penggajians',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('pegawai_id')
                    ->constrained('pegawais')
                    ->restrictOnDelete();

                /*
                 * Periode penggajian.
                 */
                $table->unsignedTinyInteger('bulan');
                $table->unsignedSmallInteger('tahun');

                /*
                 * Snapshot rekap hari kerja dan absensi.
                 */
                $table->unsignedTinyInteger(
                    'jumlah_hari_kerja'
                );

                $table->unsignedTinyInteger(
                    'jumlah_hadir'
                )->default(0);

                $table->unsignedTinyInteger(
                    'jumlah_sakit'
                )->default(0);

                $table->unsignedTinyInteger(
                    'jumlah_izin'
                )->default(0);

                $table->unsignedTinyInteger(
                    'jumlah_cuti'
                )->default(0);

                $table->unsignedTinyInteger(
                    'jumlah_alpa'
                )->default(0);

                /*
                 * Snapshot pendapatan.
                 */
                $table->decimal(
                    'gaji_pokok',
                    15,
                    2
                );

                $table->decimal(
                    'tunjangan',
                    15,
                    2
                )->default(0);

                $table->decimal(
                    'upah_harian',
                    15,
                    2
                )->default(0);

                $table->decimal(
                    'jam_lembur',
                    8,
                    2
                )->default(0);

                $table->decimal(
                    'tarif_lembur',
                    15,
                    2
                )->default(0);

                $table->decimal(
                    'upah_lembur',
                    15,
                    2
                )->default(0);

                $table->decimal(
                    'bonus',
                    15,
                    2
                )->default(0);

                $table->text('catatan_bonus')
                    ->nullable();

                $table->decimal(
                    'gaji_kotor',
                    15,
                    2
                );

                /*
                 * Snapshot potongan dan hasil akhir.
                 */
                $table->decimal(
                    'potongan_alpa',
                    15,
                    2
                )->default(0);

                $table->decimal(
                    'potongan_lain',
                    15,
                    2
                )->default(0);

                $table->text('catatan_potongan')
                    ->nullable();

                $table->decimal(
                    'total_potongan',
                    15,
                    2
                );

                $table->decimal(
                    'gaji_bersih',
                    15,
                    2
                );

                /*
                 * draf, final, revisi, atau dibayar.
                 */
                $table->string('status', 20)
                    ->default('draf');

                $table->foreignId('diproses_oleh')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('diproses_pada')
                    ->nullable();

                $table->foreignId('difinalisasi_oleh')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('difinalisasi_pada')
                    ->nullable();

                $table->foreignId('dibayar_oleh')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('dibayar_pada')
                    ->nullable();

                $table->timestamps();

                $table->unique(
                    ['pegawai_id', 'bulan', 'tahun'],
                    'penggajians_pegawai_periode_unique'
                );

                $table->index(
                    ['tahun', 'bulan'],
                    'penggajians_periode_index'
                );

                $table->index(
                    ['status', 'tahun', 'bulan'],
                    'penggajians_status_periode_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('penggajians');
    }
};
