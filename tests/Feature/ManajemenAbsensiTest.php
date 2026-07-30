<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\KalenderKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

final class ManajemenAbsensiTest extends TestCase
{
    use RefreshDatabase;
    use MembuatDataUji;

    public function test_admin_dapat_mencatat_absensi_manual(): void
    {
        $admin = $this->buatAdmin();
        $jabatan = $this->buatJabatan();
        $pegawai = $this->buatPegawai($jabatan);

        $tanggalHariKerja =
            $this->buatKalenderBulan(
                $admin,
                7,
                2026
            );

        $tanggal = $tanggalHariKerja[0];

        $response = $this
            ->actingAs($admin)
            ->post(
                route('admin.absensi.store'),
                [
                    'pegawai_id' =>
                    $pegawai->id,

                    'tanggal_absensi' =>
                    $tanggal,

                    'status' =>
                    Absensi::STATUS_HADIR,

                    'jam_lembur' => 2,

                    'catatan_lembur' =>
                    'Menyelesaikan laporan',

                    'catatan' =>
                    'Masuk tepat waktu',
                ]
            );

        $response->assertSessionHas('success');

        $absensi = Absensi::query()
            ->firstOrFail();

        $this->assertSame(
            $tanggal,
            $absensi->tanggal_absensi
                ->toDateString()
        );

        $this->assertSame(
            Absensi::STATUS_HADIR,
            $absensi->status
        );

        $this->assertSame(
            '2.00',
            $absensi->jam_lembur
        );
    }

    public function test_absensi_duplikat_ditolak(): void
    {
        $admin = $this->buatAdmin();
        $jabatan = $this->buatJabatan();
        $pegawai = $this->buatPegawai($jabatan);

        $tanggal = $this
            ->buatKalenderBulan(
                $admin,
                7,
                2026
            )[0];

        Absensi::query()->create([
            'pegawai_id' =>
            $pegawai->id,

            'tanggal_absensi' =>
            $tanggal,

            'status' =>
            Absensi::STATUS_HADIR,

            'jam_lembur' => 0,

            'sumber' =>
            Absensi::SUMBER_MANUAL,

            'dibuat_oleh' =>
            $admin->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(
                route('admin.absensi.store'),
                [
                    'pegawai_id' =>
                    $pegawai->id,

                    'tanggal_absensi' =>
                    $tanggal,

                    'status' =>
                    Absensi::STATUS_SAKIT,

                    'jam_lembur' => 0,
                    'catatan_lembur' => null,
                    'catatan' => 'Duplikat',
                ]
            );

        $response->assertSessionHasErrors(
            'tanggal_absensi'
        );

        $this->assertDatabaseCount(
            'absensis',
            1
        );
    }

    public function test_absensi_pada_hari_libur_ditolak(): void
    {
        $admin = $this->buatAdmin();
        $jabatan = $this->buatJabatan();
        $pegawai = $this->buatPegawai($jabatan);

        $this->buatKalenderBulan(
            $admin,
            7,
            2026
        );

        $hariLibur = KalenderKerja::query()
            ->where('hari_kerja', false)
            ->firstOrFail();

        $response = $this
            ->actingAs($admin)
            ->post(
                route('admin.absensi.store'),
                [
                    'pegawai_id' =>
                    $pegawai->id,

                    'tanggal_absensi' =>
                    $hariLibur->tanggal
                        ->toDateString(),

                    'status' =>
                    Absensi::STATUS_HADIR,

                    'jam_lembur' => 0,
                    'catatan_lembur' => null,
                    'catatan' => null,
                ]
            );

        $response->assertSessionHas('error');

        $this->assertDatabaseCount(
            'absensis',
            0
        );
    }

    public function test_admin_dapat_menyimpan_absensi_massal(): void
    {
        $admin = $this->buatAdmin();
        $jabatan = $this->buatJabatan();

        $pegawaiPertama =
            $this->buatPegawai($jabatan);

        $pegawaiKedua =
            $this->buatPegawai(
                $jabatan,
                [
                    'nip' => 'KRY-002',
                    'nama' => 'Budi Santoso',
                ]
            );

        $tanggal = $this
            ->buatKalenderBulan(
                $admin,
                7,
                2026
            )[0];

        $response = $this
            ->actingAs($admin)
            ->post(
                route(
                    'admin.absensi.massal.store'
                ),
                [
                    'tanggal_absensi' =>
                    $tanggal,

                    'data' => [
                        [
                            'pegawai_id' =>
                            $pegawaiPertama->id,

                            'status' =>
                            Absensi::STATUS_HADIR,

                            'jam_lembur' => 2,

                            'catatan_lembur' =>
                            'Lembur laporan',

                            'catatan' => null,
                        ],
                        [
                            'pegawai_id' =>
                            $pegawaiKedua->id,

                            'status' =>
                            Absensi::STATUS_SAKIT,

                            'jam_lembur' => 0,

                            'catatan_lembur' =>
                            null,

                            'catatan' =>
                            'Surat dokter',
                        ],
                    ],
                ]
            );

        $response->assertSessionHas('success');

        $this->assertDatabaseCount(
            'absensis',
            2
        );

        $this->assertDatabaseHas(
            'absensis',
            [
                'pegawai_id' =>
                $pegawaiKedua->id,

                'status' =>
                Absensi::STATUS_SAKIT,

                'sumber' =>
                Absensi::SUMBER_MASSAL,
            ]
        );
    }
}
