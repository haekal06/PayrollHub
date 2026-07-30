<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Jabatan extends Model
{
    use HasFactory;

    protected $table = 'jabatans';

    protected $fillable = [
        'kode',
        'nama',
        'gaji_pokok',
        'tunjangan',
        'tarif_lembur_per_jam',
        'aktif',
    ];

    protected $attributes = [
        'tunjangan' => 0,
        'tarif_lembur_per_jam' => 0,
        'aktif' => true,
    ];

    protected function casts(): array
    {
        return [
            'gaji_pokok' => 'decimal:2',
            'tunjangan' => 'decimal:2',
            'tarif_lembur_per_jam' => 'decimal:2',
            'aktif' => 'boolean',
        ];
    }

    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class);
    }
}
