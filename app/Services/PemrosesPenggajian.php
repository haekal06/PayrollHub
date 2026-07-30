<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Pegawai;
use App\Models\Penggajian;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

final class PemrosesPenggajian
{
    public function __construct(
        private KalkulatorPenggajian $kalkulator,
        private RingkasanAbsensiPenggajian
        $ringkasanAbsensi,
    ) {}

    public function proses(
        Pegawai $pegawai,
        User $pemroses,
        int $bulan,
        int $tahun,
        float $bonus = 0,
        ?string $catatanBonus = null,
        float $potonganLain = 0,
        ?string $catatanPotongan = null,
    ): Penggajian {
        if (! $pemroses->adalahAdmin()) {
            throw new LogicException(
                'Penggajian hanya dapat diproses oleh Admin HRD.'
            );
        }

        if (! $pegawai->masihAktif()) {
            throw new LogicException(
                'Pegawai tidak aktif tidak dapat diproses.'
            );
        }

        if ($bulan < 1 || $bulan > 12) {
            throw new InvalidArgumentException(
                'Bulan penggajian tidak valid.'
            );
        }

        if ($tahun < 2000 || $tahun > 2100) {
            throw new InvalidArgumentException(
                'Tahun penggajian tidak valid.'
            );
        }

        return DB::transaction(
            function () use (
                $pegawai,
                $pemroses,
                $bulan,
                $tahun,
                $bonus,
                $catatanBonus,
                $potonganLain,
                $catatanPotongan,
            ): Penggajian {
                $penggajianLama =
                    Penggajian::query()
                    ->where(
                        'pegawai_id',
                        $pegawai->id
                    )
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->lockForUpdate()
                    ->first();

                if (
                    in_array(
                        $penggajianLama?->status,
                        [
                            Penggajian::STATUS_FINAL,
                            Penggajian::STATUS_DIBAYAR,
                        ],
                        true
                    )
                ) {
                    throw new LogicException(
                        'Penggajian final atau sudah dibayar tidak dapat diproses ulang.'
                    );
                }

                $pegawai->loadMissing('jabatan');

                $ringkasan =
                    $this->ringkasanAbsensi->hitung(
                        $pegawai,
                        $bulan,
                        $tahun
                    );

                $tarifLembur = (float) $pegawai
                    ->jabatan
                    ->tarif_lembur_per_jam;

                $hasil =
                    $this->kalkulator->hitung(
                        gajiPokok: (float) $pegawai
                            ->jabatan
                            ->gaji_pokok,

                        tunjangan: (float) $pegawai
                            ->jabatan
                            ->tunjangan,

                        jumlahHariKerja: $ringkasan['jumlah_hari_kerja'],

                        jumlahAlpa: $ringkasan['jumlah_alpa'],

                        jamLembur: $ringkasan['jam_lembur'],

                        tarifLembur: $tarifLembur,

                        bonus: $bonus,

                        potonganLain: $potonganLain,
                    );

                $penggajian =
                    $penggajianLama
                    ?? new Penggajian([
                        'pegawai_id' =>
                        $pegawai->id,

                        'bulan' => $bulan,
                        'tahun' => $tahun,
                    ]);

                $status =
                    $penggajianLama?->status ===
                    Penggajian::STATUS_REVISI
                    ? Penggajian::STATUS_REVISI
                    : Penggajian::STATUS_DRAF;

                $penggajian->fill(
                    array_merge(
                        [
                            'jumlah_hari_kerja' =>
                            $ringkasan['jumlah_hari_kerja'],

                            'jumlah_hadir' =>
                            $ringkasan['jumlah_hadir'],

                            'jumlah_sakit' =>
                            $ringkasan['jumlah_sakit'],

                            'jumlah_izin' =>
                            $ringkasan['jumlah_izin'],

                            'jumlah_cuti' =>
                            $ringkasan['jumlah_cuti'],

                            'jumlah_alpa' =>
                            $ringkasan['jumlah_alpa'],

                            'gaji_pokok' =>
                            $pegawai
                                ->jabatan
                                ->gaji_pokok,

                            'tunjangan' =>
                            $pegawai
                                ->jabatan
                                ->tunjangan,

                            'jam_lembur' =>
                            $ringkasan['jam_lembur'],

                            'tarif_lembur' =>
                            $tarifLembur,

                            'bonus' => $bonus,

                            'catatan_bonus' =>
                            $bonus > 0
                                ? trim(
                                    (string)
                                    $catatanBonus
                                )
                                : null,

                            'potongan_lain' =>
                            $potonganLain,

                            'catatan_potongan' =>
                            $potonganLain > 0
                                ? trim(
                                    (string)
                                    $catatanPotongan
                                )
                                : null,

                            'status' => $status,

                            'diproses_oleh' =>
                            $pemroses->id,

                            'diproses_pada' =>
                            now(),
                        ],
                        $hasil
                    )
                );

                $penggajian->save();

                return $penggajian->refresh();
            }
        );
    }
}
