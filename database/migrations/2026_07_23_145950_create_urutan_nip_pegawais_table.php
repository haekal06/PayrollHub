<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'urutan_nip_pegawais',
            function (Blueprint $table): void {
                $table->string('nama', 50)
                    ->primary();

                $table->unsignedBigInteger('nomor_terakhir')
                    ->default(0);

                $table->timestamps();
            }
        );

        DB::table('urutan_nip_pegawais')->insert([
            'nama' => 'nip_pegawai',
            'nomor_terakhir' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('urutan_nip_pegawais');
    }
};
