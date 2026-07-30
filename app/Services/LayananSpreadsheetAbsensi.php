<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Absensi;
use App\Models\KalenderKerja;
use App\Models\Pegawai;
use App\Models\Penggajian;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

final class LayananSpreadsheetAbsensi
{
    private const HEADER = [
        'nip',
        'tanggal_absensi',
        'status',
        'jam_lembur',
        'catatan_lembur',
        'catatan',
    ];

    private const PETA_STATUS = [
        'hadir' => Absensi::STATUS_HADIR,
        'present' => Absensi::STATUS_HADIR,

        'sakit' => Absensi::STATUS_SAKIT,
        'sick' => Absensi::STATUS_SAKIT,

        'izin' => Absensi::STATUS_IZIN,
        'permission' => Absensi::STATUS_IZIN,

        'cuti' => Absensi::STATUS_CUTI,
        'leave' => Absensi::STATUS_CUTI,

        'alpa' => Absensi::STATUS_ALPA,
        'absent' => Absensi::STATUS_ALPA,
    ];

    /**
     * @return array{
     *     baris: array<int, array<string, mixed>>,
     *     jumlah_baris: int,
     *     jumlah_valid: int,
     *     jumlah_tidak_valid: int
     * }
     */
    public function analisis(
        UploadedFile $file
    ): array {
        try {
            $spreadsheet = IOFactory::load(
                (string) $file->getRealPath()
            );
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'file_absensi' =>
                'File tidak dapat dibaca sebagai spreadsheet.',
            ]);
        }

        $lembar = $spreadsheet->getActiveSheet();

        $this->validasiHeader($lembar);

        $barisTerakhir =
            $lembar->getHighestDataRow();

        if ($barisTerakhir > 5001) {
            $spreadsheet->disconnectWorksheets();

            throw ValidationException::withMessages([
                'file_absensi' =>
                'Maksimal 5.000 baris data dalam satu file.',
            ]);
        }

        $pegawai = Pegawai::query()
            ->get()
            ->keyBy(
                fn(Pegawai $data): string =>
                strtoupper($data->nip)
            );

        $kalender = KalenderKerja::query()
            ->get()
            ->keyBy(
                fn(KalenderKerja $data): string =>
                $data->tanggal->format('Y-m-d')
            );

        $barisHasil = [];

        for (
            $nomorBaris = 2;
            $nomorBaris <= $barisTerakhir;
            $nomorBaris++
        ) {
            $dataAsli = [
                'nip' => $lembar
                    ->getCell("A{$nomorBaris}")
                    ->getValue(),

                'tanggal_absensi' => $lembar
                    ->getCell("B{$nomorBaris}")
                    ->getValue(),

                'status' => $lembar
                    ->getCell("C{$nomorBaris}")
                    ->getValue(),

                'jam_lembur' => $lembar
                    ->getCell("D{$nomorBaris}")
                    ->getValue(),

                'catatan_lembur' => $lembar
                    ->getCell("E{$nomorBaris}")
                    ->getValue(),

                'catatan' => $lembar
                    ->getCell("F{$nomorBaris}")
                    ->getValue(),
            ];

            if ($this->barisKosong($dataAsli)) {
                continue;
            }

            $kesalahan = [];
            $dataNormal = [];

            $nip = strtoupper(
                trim(
                    (string) (
                        $dataAsli['nip'] ?? ''
                    )
                )
            );

            if ($nip === '') {
                $kesalahan[] = 'NIP wajib diisi.';
            }

            $dataPegawai = $pegawai->get($nip);

            if (
                $nip !== ''
                && $dataPegawai === null
            ) {
                $kesalahan[] =
                    "Pegawai dengan NIP {$nip} tidak ditemukan.";
            }

            if (
                $dataPegawai !== null
                && ! $dataPegawai->masihAktif()
            ) {
                $kesalahan[] =
                    "Pegawai {$nip} sudah tidak aktif.";
            }

            $tanggalAbsensi = null;

            try {
                $tanggalAbsensi =
                    $this->normalisasiTanggal(
                        $dataAsli['tanggal_absensi']
                    );
            } catch (InvalidArgumentException) {
                $kesalahan[] =
                    'Tanggal harus menggunakan format YYYY-MM-DD.';
            }

            if (
                $tanggalAbsensi !== null
                && $tanggalAbsensi->isAfter(
                    now()->startOfDay()
                )
            ) {
                $kesalahan[] =
                    'Tanggal absensi tidak boleh melebihi hari ini.';
            }

            if (
                $dataPegawai !== null
                && $tanggalAbsensi !== null
                && $dataPegawai->tanggal_masuk
                ->isAfter($tanggalAbsensi)
            ) {
                $kesalahan[] =
                    'Tanggal absensi lebih awal dari tanggal masuk pegawai.';
            }

            if ($tanggalAbsensi !== null) {
                $tanggal = $tanggalAbsensi
                    ->format('Y-m-d');

                $dataKalender =
                    $kalender->get($tanggal);

                if ($dataKalender === null) {
                    $kesalahan[] =
                        'Tanggal belum tersedia pada Kalender Kerja.';
                } elseif (
                    ! $dataKalender->hari_kerja
                ) {
                    $kesalahan[] =
                        'Tanggal tersebut merupakan hari libur.';
                }
            }

            $statusAsli = strtolower(
                trim(
                    (string) (
                        $dataAsli['status'] ?? ''
                    )
                )
            );

            $status = self::PETA_STATUS[$statusAsli] ?? null;

            if ($status === null) {
                $kesalahan[] =
                    'Status harus berupa hadir, sakit, izin, cuti, atau alpa.';
            }

            $jamLemburAsli =
                $dataAsli['jam_lembur'];

            $jamLembur =
                $jamLemburAsli === null
                || trim(
                    (string) $jamLemburAsli
                ) === ''
                ? 0.0
                : (
                    is_numeric($jamLemburAsli)
                    ? (float) $jamLemburAsli
                    : null
                );

            if ($jamLembur === null) {
                $kesalahan[] =
                    'Jam lembur harus berupa angka.';
            } elseif (
                $jamLembur < 0
                || $jamLembur > 12
            ) {
                $kesalahan[] =
                    'Jam lembur harus antara 0 sampai 12 jam.';
            } elseif (
                abs(
                    ($jamLembur * 2)
                        - round($jamLembur * 2)
                ) > 0.00001
            ) {
                $kesalahan[] =
                    'Jam lembur harus menggunakan kelipatan 0,5 jam.';
            }

            $catatanLembur = trim(
                (string) (
                    $dataAsli['catatan_lembur'] ?? ''
                )
            );

            $catatan = trim(
                (string) (
                    $dataAsli['catatan'] ?? ''
                )
            );

            if (
                $jamLembur !== null
                && $jamLembur > 0
                && $status !==
                Absensi::STATUS_HADIR
            ) {
                $kesalahan[] =
                    'Lembur hanya boleh untuk status Hadir.';
            }

            if (
                $jamLembur !== null
                && $jamLembur > 0
                && $catatanLembur === ''
            ) {
                $kesalahan[] =
                    'Keterangan lembur wajib diisi jika terdapat lembur.';
            }

            if (
                $dataPegawai !== null
                && $tanggalAbsensi !== null
                && $status !== null
                && $jamLembur !== null
            ) {
                $dataNormal = [
                    'pegawai_id' =>
                    $dataPegawai->id,

                    'nip' =>
                    $dataPegawai->nip,

                    'nama_pegawai' =>
                    $dataPegawai->nama,

                    'tanggal_absensi' =>
                    $tanggalAbsensi->format(
                        'Y-m-d'
                    ),

                    'bulan' =>
                    $tanggalAbsensi->month,

                    'tahun' =>
                    $tanggalAbsensi->year,

                    'status' => $status,

                    'jam_lembur' =>
                    $status ===
                        Absensi::STATUS_HADIR
                        ? $jamLembur
                        : 0,

                    'catatan_lembur' =>
                    $status ===
                        Absensi::STATUS_HADIR
                        && $jamLembur > 0
                        ? $catatanLembur
                        : null,

                    'catatan' =>
                    $catatan !== ''
                        ? $catatan
                        : null,
                ];
            }

            $barisHasil[] = [
                'nomor_baris' => $nomorBaris,

                'data_asli' =>
                $this->amanUntukJson(
                    $dataAsli
                ),

                'data_normal' => $dataNormal,

                'valid' => false,

                'kesalahan' => $kesalahan,
            ];
        }

        $spreadsheet->disconnectWorksheets();

        if ($barisHasil === []) {
            throw ValidationException::withMessages([
                'file_absensi' =>
                'File tidak memiliki data absensi.',
            ]);
        }

        return $this->lengkapiValidasiDatabase(
            $barisHasil
        );
    }

    /**
     * @param array<int, array<string, mixed>> $baris
     *
     * @return array{
     *     baris: array<int, array<string, mixed>>,
     *     jumlah_baris: int,
     *     jumlah_valid: int,
     *     jumlah_tidak_valid: int
     * }
     */
    private function lengkapiValidasiDatabase(
        array $baris
    ): array {
        $idPegawai = collect($baris)
            ->pluck('data_normal.pegawai_id')
            ->filter()
            ->unique()
            ->values();

        $tanggalAbsensi = collect($baris)
            ->pluck(
                'data_normal.tanggal_absensi'
            )
            ->filter()
            ->unique()
            ->values();

        $bulan = collect($baris)
            ->pluck('data_normal.bulan')
            ->filter()
            ->unique()
            ->values();

        $tahun = collect($baris)
            ->pluck('data_normal.tahun')
            ->filter()
            ->unique()
            ->values();

        $penggajianTerkunci =
            $this->ambilPenggajianTerkunci(
                $idPegawai,
                $bulan,
                $tahun
            );

        $absensiLama =
            $this->ambilAbsensiLama(
                $idPegawai,
                $tanggalAbsensi
            );

        $kunciSudahDibaca = [];

        foreach ($baris as &$dataBaris) {
            $normal =
                $dataBaris['data_normal'];

            if (
                empty($normal['pegawai_id'])
                || empty($normal['tanggal_absensi'])
            ) {
                $dataBaris['valid'] = false;

                continue;
            }

            $kunciAbsensi =
                $normal['pegawai_id']
                . '|'
                . $normal['tanggal_absensi'];

            if (
                isset(
                    $kunciSudahDibaca[$kunciAbsensi]
                )
            ) {
                $dataBaris['kesalahan'][] =
                    'Pegawai dan tanggal duplikat di dalam file.';
            }

            $kunciSudahDibaca[$kunciAbsensi] = true;

            $kunciPenggajian =
                $normal['pegawai_id']
                . '|'
                . $normal['tahun']
                . '|'
                . $normal['bulan'];

            if (
                $penggajianTerkunci->has(
                    $kunciPenggajian
                )
            ) {
                $dataBaris['kesalahan'][] =
                    'Periode sudah dikunci oleh penggajian final atau sudah dibayar.';
            }

            $dataBaris['data_normal']['tindakan'] =
                $absensiLama->has($kunciAbsensi)
                ? 'perbarui'
                : 'tambah';

            $dataBaris['valid'] =
                $dataBaris['kesalahan'] === [];
        }

        unset($dataBaris);

        $jumlahValid = collect($baris)
            ->where('valid', true)
            ->count();

        return [
            'baris' => $baris,

            'jumlah_baris' =>
            count($baris),

            'jumlah_valid' =>
            $jumlahValid,

            'jumlah_tidak_valid' =>
            count($baris) - $jumlahValid,
        ];
    }

    private function ambilPenggajianTerkunci(
        Collection $idPegawai,
        Collection $bulan,
        Collection $tahun
    ): Collection {
        if (
            $idPegawai->isEmpty()
            || $bulan->isEmpty()
            || $tahun->isEmpty()
        ) {
            return collect();
        }

        return Penggajian::query()
            ->whereIn(
                'pegawai_id',
                $idPegawai
            )
            ->whereIn('bulan', $bulan)
            ->whereIn('tahun', $tahun)
            ->whereIn('status', [
                Penggajian::STATUS_FINAL,
                Penggajian::STATUS_DIBAYAR,
            ])
            ->get()
            ->keyBy(
                fn(Penggajian $data): string =>
                $data->pegawai_id
                    . '|'
                    . $data->tahun
                    . '|'
                    . $data->bulan
            );
    }

    private function ambilAbsensiLama(
        Collection $idPegawai,
        Collection $tanggalAbsensi
    ): Collection {
        if (
            $idPegawai->isEmpty()
            || $tanggalAbsensi->isEmpty()
        ) {
            return collect();
        }

        return Absensi::query()
            ->whereIn(
                'pegawai_id',
                $idPegawai
            )
            ->whereDate(
                'tanggal_absensi',
                '>=',
                (string) $tanggalAbsensi->min()
            )
            ->whereDate(
                'tanggal_absensi',
                '<=',
                (string) $tanggalAbsensi->max()
            )
            ->get()
            ->keyBy(
                fn(Absensi $data): string =>
                $data->pegawai_id
                    . '|'
                    . $data->tanggal_absensi
                    ->format('Y-m-d')
            );
    }

    private function validasiHeader(
        Worksheet $lembar
    ): void {
        $header = [];

        foreach (
            range('A', 'F') as $kolom
        ) {
            $header[] = strtolower(
                trim(
                    (string) $lembar
                        ->getCell("{$kolom}1")
                        ->getValue()
                )
            );
        }

        if ($header !== self::HEADER) {
            throw ValidationException::withMessages([
                'file_absensi' =>
                'Header file tidak sesuai template. Urutannya harus: '
                    . implode(', ', self::HEADER)
                    . '.',
            ]);
        }
    }

    private function normalisasiTanggal(
        mixed $nilai
    ): CarbonImmutable {
        if (
            is_numeric($nilai)
            && (float) $nilai > 0
        ) {
            return CarbonImmutable::instance(
                Date::excelToDateTimeObject(
                    (float) $nilai
                )
            )->startOfDay();
        }

        $tanggal = trim((string) $nilai);

        if (
            preg_match(
                '/^\d{4}-\d{2}-\d{2}$/',
                $tanggal
            ) !== 1
        ) {
            throw new InvalidArgumentException();
        }

        try {
            $hasil =
                CarbonImmutable::createFromFormat(
                    '!Y-m-d',
                    $tanggal
                );
        } catch (Throwable) {
            throw new InvalidArgumentException();
        }

        if (
            $hasil === false
            || $hasil->format('Y-m-d')
            !== $tanggal
        ) {
            throw new InvalidArgumentException();
        }

        return $hasil;
    }

    /**
     * @param array<string, mixed> $baris
     */
    private function barisKosong(
        array $baris
    ): bool {
        foreach ($baris as $nilai) {
            if (
                $nilai !== null
                && trim((string) $nilai) !== ''
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function amanUntukJson(
        array $data
    ): array {
        return collect($data)
            ->map(
                static function (
                    mixed $nilai
                ): mixed {
                    if (
                        $nilai instanceof
                        DateTimeInterface
                    ) {
                        return $nilai->format(
                            'Y-m-d'
                        );
                    }

                    return $nilai;
                }
            )
            ->all();
    }
}
