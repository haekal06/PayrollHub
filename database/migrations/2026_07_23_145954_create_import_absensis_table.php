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
            'import_absensis',
            function (Blueprint $table): void {
                $table->id();

                $table->string(
                    'nama_file_asli',
                    255
                );

                $table->string('status', 20)
                    ->default('pratinjau');

                $table->unsignedInteger(
                    'jumlah_baris'
                )->default(0);

                $table->unsignedInteger(
                    'jumlah_valid'
                )->default(0);

                $table->unsignedInteger(
                    'jumlah_tidak_valid'
                )->default(0);

                $table->unsignedInteger(
                    'jumlah_ditambahkan'
                )->default(0);

                $table->unsignedInteger(
                    'jumlah_diperbarui'
                )->default(0);

                $table->foreignId('diimpor_oleh')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp(
                    'dikonfirmasi_pada'
                )->nullable();

                $table->timestamps();

                $table->index(
                    ['status', 'created_at'],
                    'import_absensis_status_waktu_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('import_absensis');
    }
};
