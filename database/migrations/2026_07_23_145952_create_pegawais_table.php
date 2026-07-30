<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pegawais', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('jabatan_id')
                ->constrained('jabatans')
                ->restrictOnDelete();

            $table->string('nip', 30)->unique();
            $table->string('nama', 100);

            $table->string(
                'jenis_kelamin',
                20
            );

            $table->string('telepon', 20)
                ->nullable();

            $table->text('alamat')
                ->nullable();

            $table->date('tanggal_masuk');

            $table->string(
                'status_kepegawaian',
                30
            )->default('aktif');

            $table->timestamps();

            $table->index(
                ['jabatan_id', 'status_kepegawaian'],
                'pegawais_jabatan_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};
