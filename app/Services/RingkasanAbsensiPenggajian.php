<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Absensi;
use App\Models\KalenderKerja;
use App\Models\Pegawai;
use Carbon\CarbonImmutable;
use LogicException;

final class RingkasanAbsensiPenggajian
{
    /**
     * @return array{
     *     jumlah_hari_kerja: int,
     *     jumlah_tercatat: int,
     *     jumlah_hadir: int,
     *     jumlah_sakit: int,
     *     jumlah_izin: int,
     *     jumlah_cuti: int,
     *     jumlah_alpa: int,
     *     jam_lembur: float
     * }
     */
    public function hitung(
        Pegawai $pegawai,
        int $bulan,
        int $tahun
    ): array {
        if ($bulan < 1 || $bulan > 12) {
            throw new LogicException(
                'Bulan penggajian tidak valid.'
            );
        }

        if ($tahun < 2000 || $tahun > 2100) {
            throw new LogicException(
                'Tahun penggajian tidak valid.'
            );
        }

        $awalPeriode = CarbonImmutable::create(
            $tahun,
            $bulan,
            1
        )->startOfMonth();

        $akhirPeriode =
            $awalPeriode->endOfMonth();

        $tanggalMasuk = CarbonImmutable::instance(
            $pegawai->tanggal_masuk
        )->startOfDay();

        if ($tanggalMasuk->isAfter($akhirPeriode)) {
            throw new LogicException(
                'Pegawai belum bekerja pada periode penggajian tersebut.'
            );
        }

        /*
         * Jika pegawai masuk di tengah bulan,
         * perhitungan dimulai dari tanggal masuk.
         */
        $awalEfektif = $tanggalMasuk->isAfter(
            $awalPeriode
        )
            ? $tanggalMasuk
            : $awalPeriode;

        $kalender = KalenderKerja::query()
            ->whereDate(
                'tanggal',
                '>=',
                $awalEfektif->toDateString()
            )
            ->whereDate(
                'tanggal',
                '<=',
                $akhirPeriode->toDateString()
            )
            ->orderBy('tanggal')
            ->get()
            ->keyBy(
                fn(KalenderKerja $tanggal): string =>
                $tanggal->tanggal->format('Y-m-d')
            );

        /*
         * Kalender harus memuat semua tanggal,
         * termasuk akhir pekan dan hari libur.
         */
        $tanggalKalenderHilang = [];
        $tanggalBerjalan = $awalEfektif;
        $tanggalTerakhir = $akhirPeriode;

        while (
            $tanggalBerjalan->lessThanOrEqualTo(
                $tanggalTerakhir
            )
        ) {
            $tanggal = $tanggalBerjalan->format(
                'Y-m-d'
            );

            if (! $kalender->has($tanggal)) {
                $tanggalKalenderHilang[] = $tanggal;
            }

            $tanggalBerjalan =
                $tanggalBerjalan->addDay();
        }

        if ($tanggalKalenderHilang !== []) {
            $contoh = collect(
                $tanggalKalenderHilang
            )
                ->take(5)
                ->map(
                    fn(string $tanggal): string =>
                    CarbonImmutable::parse($tanggal)
                        ->format('d-m-Y')
                )
                ->implode(', ');

            throw new LogicException(
                'Kalender Kerja periode ini belum lengkap. '
                    . "Tanggal yang belum tersedia: {$contoh}."
            );
        }

        $tanggalHariKerja = $kalender
            ->filter(
                fn(KalenderKerja $tanggal): bool =>
                $tanggal->hari_kerja
            )
            ->keys()
            ->values();

        $jumlahHariKerja =
            $tanggalHariKerja->count();

        if ($jumlahHariKerja < 1) {
            throw new LogicException(
                'Periode tersebut tidak memiliki hari kerja.'
            );
        }

        $absensi = $pegawai
            ->absensis()
            ->whereDate(
                'tanggal_absensi',
                '>=',
                $awalEfektif->toDateString()
            )
            ->whereDate(
                'tanggal_absensi',
                '<=',
                $akhirPeriode->toDateString()
            )
            ->get()
            ->keyBy(
                fn(Absensi $absensi): string =>
                $absensi->tanggal_absensi
                    ->format('Y-m-d')
            );

        /*
         * Absensi hari libur tidak ikut dihitung.
         */
        $pencarianHariKerja =
            $tanggalHariKerja->flip();

        $absensiHariKerja = $absensi
            ->filter(
                fn(
                    Absensi $absensi,
                    string $tanggal
                ): bool =>
                $pencarianHariKerja->has($tanggal)
            );

        $tanggalAbsensiHilang =
            $tanggalHariKerja
            ->reject(
                fn(string $tanggal): bool =>
                $absensiHariKerja->has($tanggal)
            )
            ->values();

        if ($tanggalAbsensiHilang->isNotEmpty()) {
            $contoh = $tanggalAbsensiHilang
                ->take(5)
                ->map(
                    fn(string $tanggal): string =>
                    CarbonImmutable::parse($tanggal)
                        ->format('d-m-Y')
                )
                ->implode(', ');

            throw new LogicException(
                'Absensi pegawai belum lengkap. '
                    . "{$tanggalAbsensiHilang->count()} "
                    . 'hari kerja belum dicatat. '
                    . "Contoh tanggal: {$contoh}."
            );
        }

        $jumlahStatus =
            $absensiHariKerja->countBy('status');

        $jamLembur = round(
            (float) $absensiHariKerja
                ->sum('jam_lembur'),
            2
        );

        return [
            'jumlah_hari_kerja' =>
            $jumlahHariKerja,

            'jumlah_tercatat' =>
            $absensiHariKerja->count(),

            'jumlah_hadir' => (int) (
                $jumlahStatus[Absensi::STATUS_HADIR] ?? 0
            ),

            'jumlah_sakit' => (int) (
                $jumlahStatus[Absensi::STATUS_SAKIT] ?? 0
            ),

            'jumlah_izin' => (int) (
                $jumlahStatus[Absensi::STATUS_IZIN] ?? 0
            ),

            'jumlah_cuti' => (int) (
                $jumlahStatus[Absensi::STATUS_CUTI] ?? 0
            ),

            'jumlah_alpa' => (int) (
                $jumlahStatus[Absensi::STATUS_ALPA] ?? 0
            ),

            'jam_lembur' => $jamLembur,
        ];
    }
}
