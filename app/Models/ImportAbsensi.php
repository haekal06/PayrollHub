<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ImportAbsensi extends Model
{
    public const STATUS_PRATINJAU = 'pratinjau';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    protected $table = 'import_absensis';

    protected $fillable = [
        'nama_file_asli',
        'status',
        'jumlah_baris',
        'jumlah_valid',
        'jumlah_tidak_valid',
        'jumlah_ditambahkan',
        'jumlah_diperbarui',
        'diimpor_oleh',
        'dikonfirmasi_pada',
    ];

    protected $attributes = [
        'status' => self::STATUS_PRATINJAU,
        'jumlah_baris' => 0,
        'jumlah_valid' => 0,
        'jumlah_tidak_valid' => 0,
        'jumlah_ditambahkan' => 0,
        'jumlah_diperbarui' => 0,
    ];

    protected function casts(): array
    {
        return [
            'jumlah_baris' => 'integer',
            'jumlah_valid' => 'integer',
            'jumlah_tidak_valid' => 'integer',
            'jumlah_ditambahkan' => 'integer',
            'jumlah_diperbarui' => 'integer',
            'dikonfirmasi_pada' => 'datetime',
        ];
    }

    public function pengimpor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'diimpor_oleh'
        );
    }

    public function baris(): HasMany
    {
        return $this->hasMany(
            BarisImportAbsensi::class
        )->orderBy('nomor_baris');
    }

    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function masihPratinjau(): bool
    {
        return $this->status ===
            self::STATUS_PRATINJAU;
    }

    public function sudahSelesai(): bool
    {
        return $this->status ===
            self::STATUS_SELESAI;
    }
}
