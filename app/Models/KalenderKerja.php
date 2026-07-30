<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class KalenderKerja extends Model
{
    public const JENIS_HARI_KERJA = 'hari_kerja';
    public const JENIS_AKHIR_PEKAN = 'akhir_pekan';

    public const JENIS_LIBUR_NASIONAL =
    'libur_nasional';

    public const JENIS_LIBUR_PERUSAHAAN =
    'libur_perusahaan';

    protected $table = 'kalender_kerjas';

    protected $fillable = [
        'tanggal',
        'hari_kerja',
        'jenis_hari',
        'keterangan',
        'dibuat_oleh',
    ];

    protected $attributes = [
        'hari_kerja' => true,
        'jenis_hari' => self::JENIS_HARI_KERJA,
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'hari_kerja' => 'boolean',
        ];
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'dibuat_oleh'
        );
    }
}
