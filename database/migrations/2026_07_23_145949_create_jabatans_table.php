<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jabatans', function (Blueprint $table): void {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100)->unique();

            $table->decimal('gaji_pokok', 15, 2);

            $table->decimal('tunjangan', 15, 2)
                ->default(0);

            $table->decimal(
                'tarif_lembur_per_jam',
                15,
                2
            )->default(0);

            $table->boolean('aktif')
                ->default(true);

            $table->timestamps();

            $table->index('aktif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jabatans');
    }
};
