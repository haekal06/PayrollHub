<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

final class AutentikasiDanAksesTest extends TestCase
{
    use RefreshDatabase;
    use MembuatDataUji;

    public function test_halaman_awal_diarahkan_ke_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(
            route('login')
        );
    }

    public function test_admin_aktif_dapat_login(): void
    {
        $admin = $this->buatAdmin([
            'password' => 'admin123',
        ]);

        $response = $this->post(
            route('login.autentikasi'),
            [
                'email' => $admin->email,
                'password' => 'admin123',
            ]
        );

        $response->assertRedirect(
            route('dashboard')
        );

        $this->assertAuthenticatedAs($admin);
    }

    public function test_akun_tidak_aktif_tidak_dapat_login(): void
    {
        $pengguna = User::factory()
            ->tidakAktif()
            ->create([
                'password' => 'password123',
            ]);

        $response = $this->post(
            route('login.autentikasi'),
            [
                'email' => $pengguna->email,
                'password' => 'password123',
            ]
        );

        $response->assertSessionHasErrors(
            'email'
        );

        $this->assertGuest();
    }

    public function test_pegawai_tidak_dapat_mengakses_halaman_admin(): void
    {
        $pegawaiUser = User::factory()
            ->pegawai()
            ->create();

        $response = $this
            ->actingAs($pegawaiUser)
            ->get(
                route('admin.jabatan.index')
            );

        $response->assertForbidden();
    }

    public function test_admin_dapat_mengakses_dashboard(): void
    {
        $admin = $this->buatAdmin();

        $response = $this
            ->actingAs($admin)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Admin HRD');
    }
}
