<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BarisImportAbsensi extends Model
{
    protected $table = 'baris_import_absensis';

    protected $fillable = [
        'import_absensi_id',
        'nomor_baris',
        'data_asli',
        'data_normal',
        'valid',
        'kesalahan',
    ];

    protected function casts(): array
    {
        return [
            'nomor_baris' => 'integer',
            'data_asli' => 'array',
            'data_normal' => 'array',
            'valid' => 'boolean',
            'kesalahan' => 'array',
        ];
    }

    public function importAbsensi(): BelongsTo
    {
        return $this->belongsTo(
            ImportAbsensi::class
        );
    }
}
