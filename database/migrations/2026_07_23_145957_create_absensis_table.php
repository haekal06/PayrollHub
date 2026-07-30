<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('pegawai_id')
                ->constrained('pegawais')
                ->restrictOnDelete();

            $table->date('tanggal_absensi');

            $table->string('status', 20);

            $table->decimal(
                'jam_lembur',
                5,
                2
            )->default(0);

            $table->text('catatan_lembur')
                ->nullable();

            $table->string('sumber', 20)
                ->default('manual');

            $table->foreignId('import_absensi_id')
                ->nullable()
                ->constrained('import_absensis')
                ->nullOnDelete();

            $table->text('catatan')
                ->nullable();

            $table->foreignId('dibuat_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['pegawai_id', 'tanggal_absensi'],
                'absensis_pegawai_tanggal_unique'
            );

            $table->index('tanggal_absensi');

            $table->index(
                ['tanggal_absensi', 'status'],
                'absensis_tanggal_status_index'
            );

            $table->index('sumber');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
