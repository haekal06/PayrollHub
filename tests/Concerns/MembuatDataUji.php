<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Absensi;
use App\Models\Jabatan;
use App\Models\KalenderKerja;
use App\Models\Pegawai;
use App\Models\User;
use Carbon\CarbonImmutable;

trait MembuatDataUji
{
    protected function buatAdmin(
        array $atribut = []
    ): User {
        return User::factory()
            ->admin()
            ->create(
                array_merge(
                    [
                        'nama' => 'Admin HRD',
                        'email' =>
                        'admin@payrollhub.test',
                    ],
                    $atribut
                )
            );
    }

    protected function buatJabatan(
        array $atribut = []
    ): Jabatan {
        return Jabatan::query()->create(
            array_merge(
                [
                    'kode' => 'JBT-001',
                    'nama' =>
                    'Staff Administrasi',

                    'gaji_pokok' =>
                    4000000,

                    'tunjangan' =>
                    500000,

                    'tarif_lembur_per_jam' =>
                    25000,

                    'aktif' => true,
                ],
                $atribut
            )
        );
    }

    protected function buatPegawai(
        Jabatan $jabatan,
        array $atribut = []
    ): Pegawai {
        return Pegawai::query()->create(
            array_merge(
                [
                    'jabatan_id' =>
                    $jabatan->id,

                    'nip' => 'KRY-001',
                    'nama' => 'Andi Saputra',

                    'jenis_kelamin' =>
                    Pegawai::JENIS_KELAMIN_LAKI_LAKI,

                    'telepon' => null,
                    'alamat' => null,

                    'tanggal_masuk' =>
                    '2026-01-10',

                    'status_kepegawaian' =>
                    Pegawai::STATUS_AKTIF,
                ],
                $atribut
            )
        );
    }

    /**
     * @return array<int, string>
     */
    protected function buatKalenderBulan(
        User $admin,
        int $bulan = 7,
        int $tahun = 2026
    ): array {
        $awal = CarbonImmutable::create(
            $tahun,
            $bulan,
            1
        )->startOfMonth();

        $akhir = $awal->endOfMonth();

        $tanggalHariKerja = [];
        $tanggal = $awal;

        while ($tanggal->lessThanOrEqualTo($akhir)) {
            $akhirPekan =
                $tanggal->isSaturday()
                || $tanggal->isSunday();

            KalenderKerja::query()->create([
                'tanggal' =>
                $tanggal->toDateString(),

                'hari_kerja' =>
                ! $akhirPekan,

                'jenis_hari' =>
                $akhirPekan
                    ? KalenderKerja::JENIS_AKHIR_PEKAN
                    : KalenderKerja::JENIS_HARI_KERJA,

                'keterangan' =>
                $akhirPekan
                    ? 'Libur akhir pekan'
                    : null,

                'dibuat_oleh' =>
                $admin->id,
            ]);

            if (! $akhirPekan) {
                $tanggalHariKerja[] =
                    $tanggal->toDateString();
            }

            $tanggal = $tanggal->addDay();
        }

        return $tanggalHariKerja;
    }

    /**
     * @param array<string, string> $statusTanggal
     * @param array<string, float> $lemburTanggal
     */
    protected function buatAbsensiLengkap(
        Pegawai $pegawai,
        User $admin,
        array $tanggalHariKerja,
        array $statusTanggal = [],
        array $lemburTanggal = []
    ): void {
        foreach ($tanggalHariKerja as $tanggal) {
            $status =
                $statusTanggal[$tanggal]
                ?? Absensi::STATUS_HADIR;

            $jamLembur =
                $status === Absensi::STATUS_HADIR
                ? ($lemburTanggal[$tanggal] ?? 0)
                : 0;

            Absensi::query()->create([
                'pegawai_id' =>
                $pegawai->id,

                'tanggal_absensi' =>
                $tanggal,

                'status' => $status,

                'jam_lembur' =>
                $jamLembur,

                'catatan_lembur' =>
                $jamLembur > 0
                    ? 'Pekerjaan tambahan'
                    : null,

                'sumber' =>
                Absensi::SUMBER_MANUAL,

                'import_absensi_id' =>
                null,

                'catatan' => null,

                'dibuat_oleh' =>
                $admin->id,
            ]);
        }
    }
}
