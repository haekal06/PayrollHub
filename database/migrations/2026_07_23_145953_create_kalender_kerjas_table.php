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
            'kalender_kerjas',
            function (Blueprint $table): void {
                $table->id();

                $table->date('tanggal')
                    ->unique();

                $table->boolean('hari_kerja')
                    ->default(true);

                $table->string(
                    'jenis_hari',
                    30
                )->default('hari_kerja');

                $table->string('keterangan', 255)
                    ->nullable();

                $table->foreignId('dibuat_oleh')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();

                $table->index(
                    ['hari_kerja', 'tanggal'],
                    'kalender_kerjas_status_tanggal_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('kalender_kerjas');
    }
};
