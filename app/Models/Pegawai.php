<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Pegawai extends Model
{
    use HasFactory;

    public const JENIS_KELAMIN_LAKI_LAKI =
    'laki_laki';

    public const JENIS_KELAMIN_PEREMPUAN =
    'perempuan';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_TIDAK_AKTIF = 'tidak_aktif';

    public const STATUS_MENGUNDURKAN_DIRI =
    'mengundurkan_diri';

    protected $table = 'pegawais';

    protected $fillable = [
        'user_id',
        'jabatan_id',
        'nip',
        'nama',
        'jenis_kelamin',
        'telepon',
        'alamat',
        'tanggal_masuk',
        'status_kepegawaian',
    ];

    protected $attributes = [
        'status_kepegawaian' => self::STATUS_AKTIF,
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function penggajians(): HasMany
    {
        return $this->hasMany(Penggajian::class);
    }

    public function masihAktif(): bool
    {
        return $this->status_kepegawaian ===
            self::STATUS_AKTIF;
    }
}
