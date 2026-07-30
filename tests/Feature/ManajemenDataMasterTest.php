<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

final class ManajemenDataMasterTest extends TestCase
{
    use RefreshDatabase;
    use MembuatDataUji;

    public function test_admin_dapat_membuat_jabatan(): void
    {
        $admin = $this->buatAdmin();

        $response = $this
            ->actingAs($admin)
            ->post(
                route('admin.jabatan.store'),
                [
                    'kode' => 'JBT-002',
                    'nama' => 'Programmer',
                    'gaji_pokok' => 6000000,
                    'tunjangan' => 1000000,

                    'tarif_lembur_per_jam' =>
                    40000,

                    'aktif' => 1,
                ]
            );

        $response->assertRedirect(
            route('admin.jabatan.index')
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseHas(
            'jabatans',
            [
                'kode' => 'JBT-002',
                'nama' => 'Programmer',

                'tarif_lembur_per_jam' =>
                40000,
            ]
        );
    }

    public function test_nip_pegawai_dibuat_otomatis_berurutan(): void
    {
        $admin = $this->buatAdmin();
        $jabatan = $this->buatJabatan();

        $dataPegawai = [
            'jabatan_id' => $jabatan->id,

            'jenis_kelamin' =>
            Pegawai::JENIS_KELAMIN_LAKI_LAKI,

            'telepon' => null,
            'alamat' => null,

            'tanggal_masuk' =>
            '2026-01-10',

            'status_kepegawaian' =>
            Pegawai::STATUS_AKTIF,
        ];

        $responsePertama = $this
            ->actingAs($admin)
            ->post(
                route('admin.pegawai.store'),
                array_merge(
                    $dataPegawai,
                    ['nama' => 'Andi Saputra']
                )
            );

        $responsePertama->assertRedirect(
            route('admin.pegawai.index')
        );

        $responseKedua = $this
            ->actingAs($admin)
            ->post(
                route('admin.pegawai.store'),
                array_merge(
                    $dataPegawai,
                    ['nama' => 'Budi Santoso']
                )
            );

        $responseKedua->assertRedirect(
            route('admin.pegawai.index')
        );

        $this->assertDatabaseHas(
            'pegawais',
            [
                'nip' => 'KRY-001',
                'nama' => 'Andi Saputra',
            ]
        );

        $this->assertDatabaseHas(
            'pegawais',
            [
                'nip' => 'KRY-002',
                'nama' => 'Budi Santoso',
            ]
        );
    }

    public function test_admin_dapat_membuat_akun_login_pegawai(): void
    {
        $admin = $this->buatAdmin();
        $jabatan = $this->buatJabatan();
        $pegawai = $this->buatPegawai($jabatan);

        $response = $this
            ->actingAs($admin)
            ->post(
                route(
                    'admin.pegawai.akun.store',
                    $pegawai
                ),
                [
                    'email' =>
                    'andi@payrollhub.test',

                    'password' =>
                    'pegawai123',

                    'password_confirmation' =>
                    'pegawai123',
                ]
            );

        $response->assertRedirect(
            route('admin.pegawai.index')
        );

        $response->assertSessionHas('success');

        $pengguna = User::query()
            ->where(
                'email',
                'andi@payrollhub.test'
            )
            ->firstOrFail();

        $this->assertSame(
            User::PERAN_PEGAWAI,
            $pengguna->peran
        );

        $this->assertTrue($pengguna->aktif);

        $this->assertSame(
            $pengguna->id,
            $pegawai->fresh()->user_id
        );
    }

    public function test_admin_tidak_dapat_menonaktifkan_akun_sendiri(): void
    {
        $admin = $this->buatAdmin();

        $response = $this
            ->actingAs($admin)
            ->put(
                route(
                    'admin.pengguna.update',
                    $admin
                ),
                [
                    'nama' => $admin->nama,
                    'email' => $admin->email,
                    'aktif' => 0,
                    'password' => null,

                    'password_confirmation' =>
                    null,
                ]
            );

        $response->assertSessionHas('error');

        $this->assertTrue(
            $admin->fresh()->aktif
        );
    }

    public function test_admin_aktif_terakhir_tidak_dapat_dinonaktifkan(): void
    {
        $adminPertama = $this->buatAdmin();

        $adminKedua = User::factory()
            ->admin()
            ->tidakAktif()
            ->create([
                'email' =>
                'admin2@payrollhub.test',
            ]);

        $response = $this
            ->actingAs($adminPertama)
            ->put(
                route(
                    'admin.pengguna.update',
                    $adminKedua
                ),
                [
                    'nama' => $adminKedua->nama,
                    'email' => $adminKedua->email,
                    'aktif' => 0,
                    'password' => null,

                    'password_confirmation' =>
                    null,
                ]
            );

        $response->assertRedirect(
            route('admin.pengguna.index')
        );

        $this->assertFalse(
            $adminKedua->fresh()->aktif
        );
    }
}
