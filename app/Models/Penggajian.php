<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Penggajian extends Model
{
    use HasFactory;

    public const STATUS_DRAF = 'draf';
    public const STATUS_FINAL = 'final';
    public const STATUS_REVISI = 'revisi';
    public const STATUS_DIBAYAR = 'dibayar';

    protected $table = 'penggajians';

    protected $fillable = [
        'pegawai_id',
        'bulan',
        'tahun',
        'jumlah_hari_kerja',
        'jumlah_hadir',
        'jumlah_sakit',
        'jumlah_izin',
        'jumlah_cuti',
        'jumlah_alpa',
        'gaji_pokok',
        'tunjangan',
        'upah_harian',
        'jam_lembur',
        'tarif_lembur',
        'upah_lembur',
        'bonus',
        'catatan_bonus',
        'gaji_kotor',
        'potongan_alpa',
        'potongan_lain',
        'catatan_potongan',
        'total_potongan',
        'gaji_bersih',
        'status',
        'diproses_oleh',
        'diproses_pada',
        'difinalisasi_oleh',
        'difinalisasi_pada',
        'dibayar_oleh',
        'dibayar_pada',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAF,
    ];

    protected function casts(): array
    {
        return [
            'bulan' => 'integer',
            'tahun' => 'integer',
            'jumlah_hari_kerja' => 'integer',
            'jumlah_hadir' => 'integer',
            'jumlah_sakit' => 'integer',
            'jumlah_izin' => 'integer',
            'jumlah_cuti' => 'integer',
            'jumlah_alpa' => 'integer',
            'gaji_pokok' => 'decimal:2',
            'tunjangan' => 'decimal:2',
            'upah_harian' => 'decimal:2',
            'jam_lembur' => 'decimal:2',
            'tarif_lembur' => 'decimal:2',
            'upah_lembur' => 'decimal:2',
            'bonus' => 'decimal:2',
            'gaji_kotor' => 'decimal:2',
            'potongan_alpa' => 'decimal:2',
            'potongan_lain' => 'decimal:2',
            'total_potongan' => 'decimal:2',
            'gaji_bersih' => 'decimal:2',
            'diproses_pada' => 'datetime',
            'difinalisasi_pada' => 'datetime',
            'dibayar_pada' => 'datetime',
        ];
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function pemroses(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'diproses_oleh'
        );
    }

    public function pemfinalisasi(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'difinalisasi_oleh'
        );
    }

    public function pembayar(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'dibayar_oleh'
        );
    }

    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(
            RiwayatStatusPenggajian::class
        )->orderByDesc('diubah_pada');
    }

    public function absensiTerkunci(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_FINAL,
                self::STATUS_DIBAYAR,
            ],
            true
        );
    }

    public function dapatDiprosesUlang(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_DRAF,
                self::STATUS_REVISI,
            ],
            true
        );
    }
}
