<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Penggajian;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

final class CetakLaporanTest extends TestCase
{
    use RefreshDatabase;
    use MembuatDataUji;

    public function test_laporan_cetak_dapat_diakses_sesuai_hak_akses(): void
    {
        $admin = $this->buatAdmin();
        $jabatan = $this->buatJabatan();

        $penggunaPegawai = User::factory()
            ->pegawai()
            ->create([
                'nama' => 'Andi Saputra',
                'email' => 'andi@payrollhub.test',
            ]);

        $pegawai = $this->buatPegawai(
            $jabatan,
            [
                'user_id' => $penggunaPegawai->id,
                'nama' => 'Andi Saputra',
            ]
        );

        /*
         * Membuat Kalender Kerja Juli 2026.
         */
        $tanggalHariKerja =
            $this->buatKalenderBulan(
                $admin,
                7,
                2026
            );

        $tanggalLembur =
            $tanggalHariKerja[0];

        $tanggalIzin =
            $tanggalHariKerja[1];

        $tanggalAlpa =
            $tanggalHariKerja[2];

        $tanggalIzinTampil =
            CarbonImmutable::parse(
                $tanggalIzin
            )->format('d-m-Y');

        /*
         * Membuat absensi lengkap.
         */
        $this->buatAbsensiLengkap(
            pegawai: $pegawai,
            admin: $admin,

            tanggalHariKerja: $tanggalHariKerja,

            statusTanggal: [
                $tanggalIzin =>
                Absensi::STATUS_IZIN,

                $tanggalAlpa =>
                Absensi::STATUS_ALPA,
            ],

            lemburTanggal: [
                $tanggalLembur => 2,
            ]
        );

        /*
         * Memproses penggajian.
         */
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

                    'bonus' => 100000,

                    'catatan_bonus' =>
                    'Bonus kinerja',

                    'potongan_lain' =>
                    50000,

                    'catatan_potongan' =>
                    'Potongan koperasi',
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

        /*
         * Finalisasi penggajian agar slip
         * dapat dilihat oleh pegawai.
         */
        $responseFinalisasi = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.penggajian.finalisasi',
                    $penggajian
                )
            );

        $responseFinalisasi
            ->assertSessionHas('success');

        $penggajian->refresh();

        $this->assertSame(
            Penggajian::STATUS_FINAL,
            $penggajian->status
        );

        /*
         * Admin dapat mencetak slip gaji.
         */
        $responseSlipAdmin = $this
            ->actingAs($admin)
            ->get(
                route(
                    'admin.penggajian.cetak-slip',
                    $penggajian
                )
            );

        $responseSlipAdmin->assertOk();

        $responseSlipAdmin->assertSee(
            'Slip Gaji Pegawai'
        );

        $responseSlipAdmin->assertSee(
            'Detail Absensi Harian'
        );

        $responseSlipAdmin->assertSee(
            $pegawai->nip
        );

        $responseSlipAdmin->assertSee(
            $tanggalIzinTampil
        );

        /*
         * Admin dapat mencetak rekap penggajian.
         */
        $responseRekapPenggajian = $this
            ->actingAs($admin)
            ->get(
                route(
                    'admin.penggajian.cetak-rekap',
                    [
                        'bulan' => 7,
                        'tahun' => 2026,
                    ]
                )
            );

        $responseRekapPenggajian->assertOk();

        $responseRekapPenggajian->assertSee(
            'Rekap Penggajian'
        );

        $responseRekapPenggajian->assertSee(
            $pegawai->nama
        );

        $responseRekapPenggajian->assertSee(
            $pegawai->nip
        );

        /*
         * Admin dapat mencetak rekap absensi.
         */
        $responseRekapAbsensi = $this
            ->actingAs($admin)
            ->get(
                route(
                    'admin.absensi.cetak-rekap',
                    [
                        'bulan' => 7,
                        'tahun' => 2026,
                    ]
                )
            );

        $responseRekapAbsensi->assertOk();

        $responseRekapAbsensi->assertSee(
            'Rekap Absensi Bulanan'
        );

        $responseRekapAbsensi->assertSee(
            $pegawai->nip
        );

        $responseRekapAbsensi->assertSee(
            $pegawai->nama
        );

        /*
         * Admin dapat mencetak detail absensi.
         */
        $responseDetailAbsensi = $this
            ->actingAs($admin)
            ->get(
                route(
                    'admin.absensi.cetak-detail',
                    [
                        'bulan' => 7,
                        'tahun' => 2026,

                        'pegawai_id' =>
                        $pegawai->id,
                    ]
                )
            );

        $responseDetailAbsensi->assertOk();

        $responseDetailAbsensi->assertSee(
            'Detail Absensi Bulanan'
        );

        $responseDetailAbsensi->assertSee(
            $tanggalIzinTampil
        );

        $responseDetailAbsensi->assertSee(
            'Izin'
        );

        /*
         * Admin dapat mencetak Kalender Kerja.
         */
        $responseKalender = $this
            ->actingAs($admin)
            ->get(
                route(
                    'admin.kalender-kerja.cetak',
                    [
                        'bulan' => 7,
                        'tahun' => 2026,
                    ]
                )
            );

        $responseKalender->assertOk();

        $responseKalender->assertSee(
            'Kalender Kerja'
        );

        $responseKalender->assertSee(
            'Juli'
        );

        $responseKalender->assertSee(
            '2026'
        );

        /*
         * Pegawai dapat melihat detail absensi
         * pada slip gaji miliknya.
         */
        $responseSlipPegawai = $this
            ->actingAs($penggunaPegawai)
            ->get(
                route(
                    'pegawai.slip-gaji.show',
                    $penggajian
                )
            );

        $responseSlipPegawai->assertOk();

        $responseSlipPegawai->assertSee(
            'Detail Absensi Harian'
        );

        $responseSlipPegawai->assertSee(
            $tanggalIzinTampil
        );

        $responseSlipPegawai->assertSee(
            'Izin'
        );

        /*
         * Pegawai dapat mencetak slip
         * gaji miliknya sendiri.
         */
        $responseCetakPegawai = $this
            ->actingAs($penggunaPegawai)
            ->get(
                route(
                    'pegawai.slip-gaji.cetak',
                    $penggajian
                )
            );

        $responseCetakPegawai->assertOk();

        $responseCetakPegawai->assertSee(
            'Slip Gaji Pegawai'
        );

        $responseCetakPegawai->assertSee(
            'Detail Absensi Harian'
        );

        $responseCetakPegawai->assertSee(
            $tanggalIzinTampil
        );

        /*
         * Pegawai tidak boleh membuka
         * laporan milik Admin HRD.
         */
        $responseTerlarang = $this
            ->actingAs($penggunaPegawai)
            ->get(
                route(
                    'admin.absensi.cetak-rekap',
                    [
                        'bulan' => 7,
                        'tahun' => 2026,
                    ]
                )
            );

        $responseTerlarang->assertForbidden();
    }
}
