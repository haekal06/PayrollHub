<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RiwayatStatusPenggajian extends Model
{
    protected $table =
    'riwayat_status_penggajians';

    protected $fillable = [
        'penggajian_id',
        'status_asal',
        'status_tujuan',
        'alasan',
        'snapshot',
        'diubah_oleh',
        'diubah_pada',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'diubah_pada' => 'datetime',
        ];
    }

    public function penggajian(): BelongsTo
    {
        return $this->belongsTo(
            Penggajian::class
        );
    }

    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'diubah_oleh'
        );
    }
}
