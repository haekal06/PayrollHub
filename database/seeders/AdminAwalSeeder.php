<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class AdminAwalSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            [
                'email' =>
                'admin@payrollhub.test',
            ],
            [
                'nama' =>
                'Admin HRD',

                'password' =>
                'admin123',

                'peran' =>
                User::PERAN_ADMIN,

                'aktif' => true,

                'dibuat_oleh' => null,
            ]
        );
    }
}
