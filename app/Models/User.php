<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

final class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;

    public const PERAN_ADMIN = 'admin';
    public const PERAN_PEGAWAI = 'pegawai';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'peran',
        'aktif',
        'dibuat_oleh',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'peran' => self::PERAN_PEGAWAI,
        'aktif' => true,
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'aktif' => 'boolean',
        ];
    }

    public function pegawai(): HasOne
    {
        return $this->hasOne(Pegawai::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'dibuat_oleh'
        );
    }

    public function penggunaDibuat(): HasMany
    {
        return $this->hasMany(
            User::class,
            'dibuat_oleh'
        );
    }

    public function absensiDicatat(): HasMany
    {
        return $this->hasMany(
            Absensi::class,
            'dibuat_oleh'
        );
    }

    public function importAbsensi(): HasMany
    {
        return $this->hasMany(
            ImportAbsensi::class,
            'diimpor_oleh'
        );
    }

    public function penggajianDiproses(): HasMany
    {
        return $this->hasMany(
            Penggajian::class,
            'diproses_oleh'
        );
    }

    public function penggajianDifinalisasi(): HasMany
    {
        return $this->hasMany(
            Penggajian::class,
            'difinalisasi_oleh'
        );
    }

    public function penggajianDibayar(): HasMany
    {
        return $this->hasMany(
            Penggajian::class,
            'dibayar_oleh'
        );
    }

    public function adalahAdmin(): bool
    {
        return $this->peran === self::PERAN_ADMIN;
    }

    public function adalahPegawai(): bool
    {
        return $this->peran === self::PERAN_PEGAWAI;
    }

    public function masihAktif(): bool
    {
        return (bool) $this->aktif;
    }
}
