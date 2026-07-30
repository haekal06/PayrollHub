<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'nama' => fake('id_ID')->name(),

            'email' =>
            fake()->unique()->safeEmail(),

            'email_verified_at' => now(),

            'password' =>
            self::$password ??=
                Hash::make('password'),

            'peran' =>
            User::PERAN_PEGAWAI,

            'aktif' => true,

            'dibuat_oleh' => null,

            'remember_token' =>
            Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(
            fn(array $atribut): array => [
                'peran' =>
                User::PERAN_ADMIN,
            ]
        );
    }

    public function pegawai(): static
    {
        return $this->state(
            fn(array $atribut): array => [
                'peran' =>
                User::PERAN_PEGAWAI,
            ]
        );
    }

    public function tidakAktif(): static
    {
        return $this->state(
            fn(array $atribut): array => [
                'aktif' => false,
            ]
        );
    }

    public function belumTerverifikasi(): static
    {
        return $this->state(
            fn(array $atribut): array => [
                'email_verified_at' => null,
            ]
        );
    }
}
