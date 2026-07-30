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
            'riwayat_status_penggajians',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('penggajian_id')
                    ->constrained('penggajians')
                    ->cascadeOnDelete();

                $table->string(
                    'status_asal',
                    20
                )->nullable();

                $table->string(
                    'status_tujuan',
                    20
                );

                $table->text('alasan')
                    ->nullable();

                $table->json('snapshot')
                    ->nullable();

                $table->foreignId('diubah_oleh')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('diubah_pada');

                $table->timestamps();

                $table->index(
                    ['penggajian_id', 'diubah_pada'],
                    'riwayat_penggajian_waktu_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'riwayat_status_penggajians'
        );
    }
};
