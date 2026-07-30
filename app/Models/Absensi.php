<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Absensi extends Model
{
    use HasFactory;

    public const STATUS_HADIR = 'hadir';
    public const STATUS_SAKIT = 'sakit';
    public const STATUS_IZIN = 'izin';
    public const STATUS_CUTI = 'cuti';
    public const STATUS_ALPA = 'alpa';

    public const SUMBER_MANUAL = 'manual';
    public const SUMBER_MASSAL = 'massal';
    public const SUMBER_IMPOR = 'impor';

    protected $table = 'absensis';

    protected $fillable = [
        'pegawai_id',
        'tanggal_absensi',
        'status',
        'jam_lembur',
        'catatan_lembur',
        'sumber',
        'import_absensi_id',
        'catatan',
        'dibuat_oleh',
    ];

    protected $attributes = [
        'jam_lembur' => 0,
        'sumber' => self::SUMBER_MANUAL,
    ];

    protected function casts(): array
    {
        return [
            'tanggal_absensi' => 'date',
            'jam_lembur' => 'decimal:2',
        ];
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'dibuat_oleh'
        );
    }

    public function importAbsensi(): BelongsTo
    {
        return $this->belongsTo(
            ImportAbsensi::class
        );
    }

    public function memilikiLembur(): bool
    {
        return (float) $this->jam_lembur > 0;
    }
}
