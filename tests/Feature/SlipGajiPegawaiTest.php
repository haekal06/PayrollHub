<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Penggajian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

final class SlipGajiPegawaiTest extends TestCase
{
    use RefreshDatabase;
    use MembuatDataUji;

    public function test_pegawai_dapat_melihat_slip_gaji_final_miliknya(): void
    {
        [
            $admin,
            $penggunaPegawai,
            $pegawai,
            $penggajian,
        ] = $this->siapkanPenggajianFinal();

        $responseDaftar = $this
            ->actingAs($penggunaPegawai)
            ->get(
                route(
                    'pegawai.slip-gaji.index'
                )
            );

        $responseDaftar->assertOk();

        $responseDaftar->assertSee(
            'Slip Gaji Saya'
        );

        $responseDaftar->assertSee(
            $pegawai->nip
        );

        $responseDetail = $this
            ->actingAs($penggunaPegawai)
            ->get(
                route(
                    'pegawai.slip-gaji.show',
                    $penggajian
                )
            );

        $responseDetail->assertOk();

        $responseDetail->assertSee(
            'Slip Gaji Pegawai'
        );

        $responseDetail->assertSee(
            $pegawai->nama
        );

        $responseDetail->assertSee(
            number_format(
                (float) $penggajian->gaji_bersih,
                0,
                ',',
                '.'
            )
        );
    }

    public function test_pegawai_tidak_dapat_melihat_slip_milik_pegawai_lain(): void
    {
        [
            $admin,
            $penggunaPertama,
            $pegawaiPertama,
            $penggajian,
            $jabatan,
        ] = $this->siapkanPenggajianFinal();

        $penggunaKedua = User::factory()
            ->pegawai()
            ->create([
                'nama' => 'Budi Santoso',

                'email' =>
                'budi@payrollhub.test',
            ]);

        $this->buatPegawai(
            $jabatan,
            [
                'user_id' =>
                $penggunaKedua->id,

                'nip' => 'KRY-002',
                'nama' => 'Budi Santoso',
            ]
        );

        $response = $this
            ->actingAs($penggunaKedua)
            ->get(
                route(
                    'pegawai.slip-gaji.show',
                    $penggajian
                )
            );

        $response->assertNotFound();
    }

    public function test_admin_tidak_menggunakan_halaman_slip_pegawai(): void
    {
        $admin = $this->buatAdmin();

        $response = $this
            ->actingAs($admin)
            ->get(
                route(
                    'pegawai.slip-gaji.index'
                )
            );

        $response->assertForbidden();
    }

    /**
     * @return array{
     *     0: User,
     *     1: User,
     *     2: Pegawai,
     *     3: Penggajian,
     *     4: Jabatan
     * }
     */
    private function siapkanPenggajianFinal(): array
    {
        $admin = $this->buatAdmin();

        $jabatan = $this->buatJabatan();

        $penggunaPegawai = User::factory()
            ->pegawai()
            ->create([
                'nama' => 'Andi Saputra',

                'email' =>
                'andi@payrollhub.test',
            ]);

        $pegawai = $this->buatPegawai(
            $jabatan,
            [
                'user_id' =>
                $penggunaPegawai->id,
            ]
        );

        $tanggalHariKerja =
            $this->buatKalenderBulan(
                $admin,
                7,
                2026
            );

        $this->buatAbsensiLengkap(
            pegawai: $pegawai,
            admin: $admin,

            tanggalHariKerja: $tanggalHariKerja,

            lemburTanggal: [
                $tanggalHariKerja[0] => 2,
            ]
        );

        $responseProses = $this
            ->actingAs($admin)
            ->post(
                route(
                    'admin.penggajian.store'
                ),
                [
                    'pegawai_id' =>
                    $pegawai->id,

                    'bulan' => 7,
                    'tahun' => 2026,

                    'bonus' => 0,
                    'catatan_bonus' => null,

                    'potongan_lain' => 0,

                    'catatan_potongan' =>
                    null,
                ]
            );

        $penggajian = Penggajian::query()
            ->firstOrFail();

        $responseProses->assertRedirect(
            route(
                'admin.penggajian.show',
                $penggajian
            )
        );

        $responseFinal = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.penggajian.finalisasi',
                    $penggajian
                )
            );

        $responseFinal->assertSessionHas(
            'success'
        );

        $penggajian->refresh();

        $this->assertSame(
            Penggajian::STATUS_FINAL,
            $penggajian->status
        );

        return [
            $admin,
            $penggunaPegawai,
            $pegawai,
            $penggajian,
            $jabatan,
        ];
    }
}
