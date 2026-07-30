<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

final class KalkulatorPenggajian
{
    /**
     * @return array{
     *     upah_harian: float,
     *     upah_lembur: float,
     *     potongan_alpa: float,
     *     gaji_kotor: float,
     *     total_potongan: float,
     *     gaji_bersih: float
     * }
     */
    public function hitung(
        float $gajiPokok,
        float $tunjangan,
        int $jumlahHariKerja,
        int $jumlahAlpa,
        float $jamLembur,
        float $tarifLembur,
        float $bonus = 0,
        float $potonganLain = 0,
    ): array {
        if ($jumlahHariKerja < 1) {
            throw new InvalidArgumentException(
                'Jumlah hari kerja minimal 1.'
            );
        }

        if (
            $jumlahAlpa < 0
            || $jumlahAlpa > $jumlahHariKerja
        ) {
            throw new InvalidArgumentException(
                'Jumlah alpa tidak valid.'
            );
        }

        $nilaiTidakBolehNegatif = [
            $gajiPokok,
            $tunjangan,
            $jamLembur,
            $tarifLembur,
            $bonus,
            $potonganLain,
        ];

        foreach (
            $nilaiTidakBolehNegatif as $nilai
        ) {
            if ($nilai < 0) {
                throw new InvalidArgumentException(
                    'Nilai perhitungan tidak boleh negatif.'
                );
            }
        }

        $upahHarian = round(
            $gajiPokok / $jumlahHariKerja,
            2
        );

        $upahLembur = round(
            $jamLembur * $tarifLembur,
            2
        );

        $potonganAlpa = round(
            $jumlahAlpa * $upahHarian,
            2
        );

        $gajiKotor = round(
            $gajiPokok
                + $tunjangan
                + $upahLembur
                + $bonus,
            2
        );

        $totalPotongan = round(
            $potonganAlpa + $potonganLain,
            2
        );

        if ($totalPotongan > $gajiKotor) {
            throw new InvalidArgumentException(
                'Total potongan tidak boleh melebihi gaji kotor.'
            );
        }

        $gajiBersih = round(
            $gajiKotor - $totalPotongan,
            2
        );

        return [
            'upah_harian' => $upahHarian,
            'upah_lembur' => $upahLembur,
            'potongan_alpa' => $potonganAlpa,
            'gaji_kotor' => $gajiKotor,
            'total_potongan' => $totalPotongan,
            'gaji_bersih' => $gajiBersih,
        ];
    }
}
