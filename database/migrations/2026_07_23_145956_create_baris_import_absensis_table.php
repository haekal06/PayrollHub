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
            'baris_import_absensis',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('import_absensi_id')
                    ->constrained('import_absensis')
                    ->cascadeOnDelete();

                $table->unsignedInteger('nomor_baris');

                $table->json('data_asli');

                $table->json('data_normal')
                    ->nullable();

                $table->boolean('valid')
                    ->default(false);

                $table->json('kesalahan')
                    ->nullable();

                $table->timestamps();

                $table->unique(
                    ['import_absensi_id', 'nomor_baris'],
                    'baris_import_absensis_nomor_unique'
                );

                $table->index(
                    ['import_absensi_id', 'valid'],
                    'baris_import_absensis_valid_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'baris_import_absensis'
        );
    }
};
