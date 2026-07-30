<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PratinjauImportAbsensiRequest;
use App\Models\Absensi;
use App\Models\ImportAbsensi;
use App\Models\KalenderKerja;
use App\Models\Pegawai;
use App\Models\Penggajian;
use App\Services\LayananSpreadsheetAbsensi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ImportAbsensiController extends Controller
{
    public function index(): View
    {
        $daftarImport = ImportAbsensi::query()
            ->with('pengimpor')
            ->latest()
            ->paginate(15);

        return view(
            'import-absensi.index',
            compact('daftarImport')
        );
    }

    public function unduhTemplate(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        $spreadsheet->getProperties()
            ->setCreator(config('app.name'))
            ->setTitle('Template Import Absensi')
            ->setDescription(
                'Template import data absensi PayrollHub'
            );

        $lembar = $spreadsheet
            ->getActiveSheet();

        $lembar->setTitle('Data Absensi');

        $lembar->fromArray(
            [
                [
                    'nip',
                    'tanggal_absensi',
                    'status',
                    'jam_lembur',
                    'catatan_lembur',
                    'catatan',
                ],
            ],
            null,
            'A1'
        );

        $lembar->getStyle('A1:F1')
            ->getFont()
            ->setBold(true)
            ->getColor()
            ->setARGB('FFFFFFFF');

        $lembar->getStyle('A1:F1')
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFC61B1B');

        $lembar->freezePane('A2');
        $lembar->setAutoFilter('A1:F1');

        $lebarKolom = [
            'A' => 16,
            'B' => 20,
            'C' => 16,
            'D' => 18,
            'E' => 35,
            'F' => 35,
        ];

        foreach (
            $lebarKolom as $kolom => $lebar
        ) {
            $lembar
                ->getColumnDimension($kolom)
                ->setWidth($lebar);
        }

        $lembar->getStyle('B2:B5001')
            ->getNumberFormat()
            ->setFormatCode('yyyy-mm-dd');

        $validasiStatus =
            new DataValidation();

        $validasiStatus->setType(
            DataValidation::TYPE_LIST
        );

        $validasiStatus->setErrorStyle(
            DataValidation::STYLE_STOP
        );

        $validasiStatus->setAllowBlank(false);
        $validasiStatus->setShowDropDown(true);
        $validasiStatus->setShowErrorMessage(true);
        $validasiStatus->setShowInputMessage(true);

        $validasiStatus->setErrorTitle(
            'Status tidak valid'
        );

        $validasiStatus->setError(
            'Pilih hadir, sakit, izin, cuti, atau alpa.'
        );

        $validasiStatus->setPromptTitle(
            'Status Absensi'
        );

        $validasiStatus->setPrompt(
            'Pilih salah satu status yang tersedia.'
        );

        $validasiStatus->setFormula1(
            '"hadir,sakit,izin,cuti,alpa"'
        );

        for ($baris = 2; $baris <= 5001; $baris++) {
            $lembar->getCell("C{$baris}")
                ->setDataValidation(
                    clone $validasiStatus
                );
        }

        $validasiLembur =
            new DataValidation();

        $validasiLembur->setType(
            DataValidation::TYPE_DECIMAL
        );

        $validasiLembur->setOperator(
            DataValidation::OPERATOR_BETWEEN
        );

        $validasiLembur->setAllowBlank(true);
        $validasiLembur->setShowErrorMessage(
            true
        );

        $validasiLembur->setErrorTitle(
            'Jam lembur tidak valid'
        );

        $validasiLembur->setError(
            'Jam lembur harus antara 0 sampai 12.'
        );

        $validasiLembur->setFormula1('0');
        $validasiLembur->setFormula2('12');

        for ($baris = 2; $baris <= 5001; $baris++) {
            $lembar->getCell("D{$baris}")
                ->setDataValidation(
                    clone $validasiLembur
                );
        }

        $petunjuk = $spreadsheet->createSheet();

        $petunjuk->setTitle('Petunjuk');

        $petunjuk->fromArray(
            [
                ['PETUNJUK IMPORT ABSENSI'],
                [''],
                [
                    '1.',
                    'Jangan mengubah nama atau urutan kolom.',
                ],
                [
                    '2.',
                    'NIP harus sudah terdaftar di PayrollHub.',
                ],
                [
                    '3.',
                    'Format tanggal: YYYY-MM-DD.',
                ],
                [
                    '4.',
                    'Status: hadir, sakit, izin, cuti, atau alpa.',
                ],
                [
                    '5.',
                    'Jam lembur menggunakan kelipatan 0,5 jam.',
                ],
                [
                    '6.',
                    'Lembur hanya boleh untuk status hadir.',
                ],
                [
                    '7.',
                    'Keterangan lembur wajib diisi jika ada lembur.',
                ],
                [
                    '8.',
                    'Tanggal harus merupakan hari kerja.',
                ],
                [
                    '9.',
                    'Periode final atau dibayar tidak dapat diubah.',
                ],
                [
                    '10.',
                    'Maksimal 5.000 baris dalam satu file.',
                ],
            ],
            null,
            'A1'
        );

        $petunjuk->getStyle('A1:B1')
            ->getFont()
            ->setBold(true)
            ->setSize(14);

        $petunjuk
            ->getColumnDimension('A')
            ->setWidth(8);

        $petunjuk
            ->getColumnDimension('B')
            ->setWidth(85);

        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(
            function () use (
                $spreadsheet
            ): void {
                $penulis =
                    new Xlsx($spreadsheet);

                $penulis->save('php://output');

                $spreadsheet
                    ->disconnectWorksheets();
            },
            'template-import-absensi.xlsx',
            [
                'Content-Type' =>
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    public function pratinjau(
        PratinjauImportAbsensiRequest $request,
        LayananSpreadsheetAbsensi $layanan
    ): RedirectResponse {
        $file = $request->file(
            'file_absensi'
        );

        if ($file === null) {
            throw ValidationException::withMessages([
                'file_absensi' =>
                'File absensi tidak ditemukan.',
            ]);
        }

        $hasil = $layanan->analisis($file);

        $importAbsensi = DB::transaction(
            function () use (
                $hasil,
                $file,
                $request
            ): ImportAbsensi {
                $import = ImportAbsensi::query()
                    ->create([
                        'nama_file_asli' =>
                        $file
                            ->getClientOriginalName(),

                        'status' =>
                        ImportAbsensi::STATUS_PRATINJAU,

                        'jumlah_baris' =>
                        $hasil['jumlah_baris'],

                        'jumlah_valid' =>
                        $hasil['jumlah_valid'],

                        'jumlah_tidak_valid' =>
                        $hasil['jumlah_tidak_valid'],

                        'jumlah_ditambahkan' => 0,
                        'jumlah_diperbarui' => 0,

                        'diimpor_oleh' =>
                        $request->user()->id,
                    ]);

                $waktu = now();

                collect($hasil['baris'])
                    ->chunk(500)
                    ->each(
                        function (
                            $kumpulanBaris
                        ) use (
                            $import,
                            $waktu
                        ): void {
                            $rekaman =
                                $kumpulanBaris
                                ->map(
                                    function (
                                        array $baris
                                    ) use (
                                        $import,
                                        $waktu
                                    ): array {
                                        return [
                                            'import_absensi_id' =>
                                            $import->id,

                                            'nomor_baris' =>
                                            $baris['nomor_baris'],

                                            'data_asli' =>
                                            json_encode(
                                                $baris['data_asli'],
                                                JSON_THROW_ON_ERROR
                                            ),

                                            'data_normal' =>
                                            $baris['data_normal'] !== []
                                                ? json_encode(
                                                    $baris['data_normal'],
                                                    JSON_THROW_ON_ERROR
                                                )
                                                : null,

                                            'valid' =>
                                            $baris['valid'],

                                            'kesalahan' =>
                                            json_encode(
                                                $baris['kesalahan'],
                                                JSON_THROW_ON_ERROR
                                            ),

                                            'created_at' =>
                                            $waktu,

                                            'updated_at' =>
                                            $waktu,
                                        ];
                                    }
                                )
                                ->all();

                            DB::table(
                                'baris_import_absensis'
                            )->insert($rekaman);
                        }
                    );

                return $import;
            }
        );

        return redirect()
            ->route(
                'admin.import-absensi.show',
                $importAbsensi
            )
            ->with(
                'success',
                'File berhasil diperiksa. Periksa pratinjau sebelum konfirmasi.'
            );
    }

    public function show(
        ImportAbsensi $importAbsensi
    ): View {
        $importAbsensi->load('pengimpor');

        $daftarBaris = $importAbsensi
            ->baris()
            ->paginate(100);

        return view(
            'import-absensi.show',
            compact(
                'importAbsensi',
                'daftarBaris'
            )
        );
    }

    public function konfirmasi(
        Request $request,
        ImportAbsensi $importAbsensi
    ): RedirectResponse {
        DB::transaction(
            function () use (
                $request,
                $importAbsensi
            ): void {
                $import = ImportAbsensi::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $importAbsensi->id
                    );

                if (! $import->masihPratinjau()) {
                    throw ValidationException::withMessages([
                        'import' =>
                        'Import sudah diproses atau dibatalkan.',
                    ]);
                }

                $daftarBaris = $import
                    ->baris()
                    ->where('valid', true)
                    ->get();

                if ($daftarBaris->isEmpty()) {
                    throw ValidationException::withMessages([
                        'import' =>
                        'Tidak ada baris valid yang dapat diimpor.',
                    ]);
                }

                $idPegawai = $daftarBaris
                    ->pluck(
                        'data_normal.pegawai_id'
                    )
                    ->unique()
                    ->values();

                $tanggal = $daftarBaris
                    ->pluck(
                        'data_normal.tanggal_absensi'
                    )
                    ->unique()
                    ->values();

                $bulan = $daftarBaris
                    ->pluck('data_normal.bulan')
                    ->unique()
                    ->values();

                $tahun = $daftarBaris
                    ->pluck('data_normal.tahun')
                    ->unique()
                    ->values();

                $tanggalAwal = $tanggal->min();
                $tanggalAkhir = $tanggal->max();

                $pegawai = Pegawai::query()
                    ->whereIn('id', $idPegawai)
                    ->get()
                    ->keyBy('id');

                $kalender = KalenderKerja::query()
                    ->whereDate(
                        'tanggal',
                        '>=',
                        $tanggalAwal
                    )
                    ->whereDate(
                        'tanggal',
                        '<=',
                        $tanggalAkhir
                    )
                    ->get()
                    ->keyBy(
                        fn(
                            KalenderKerja $data
                        ): string =>
                        $data->tanggal
                            ->format('Y-m-d')
                    );

                $penggajianTerkunci =
                    Penggajian::query()
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
                        fn(
                            Penggajian $data
                        ): string =>
                        $data->pegawai_id
                            . '|'
                            . $data->tahun
                            . '|'
                            . $data->bulan
                    );

                $absensiLama = Absensi::query()
                    ->whereIn(
                        'pegawai_id',
                        $idPegawai
                    )
                    ->whereDate(
                        'tanggal_absensi',
                        '>=',
                        $tanggalAwal
                    )
                    ->whereDate(
                        'tanggal_absensi',
                        '<=',
                        $tanggalAkhir
                    )
                    ->lockForUpdate()
                    ->get()
                    ->keyBy(
                        fn(Absensi $data): string =>
                        $data->pegawai_id
                            . '|'
                            . $data
                            ->tanggal_absensi
                            ->format('Y-m-d')
                    );

                $kesalahanTerbaru = [];

                foreach (
                    $daftarBaris as $baris
                ) {
                    $data = $baris->data_normal;

                    $dataPegawai = $pegawai->get(
                        $data['pegawai_id']
                    );

                    $dataKalender = $kalender->get(
                        $data['tanggal_absensi']
                    );

                    $kunciPenggajian =
                        $data['pegawai_id']
                        . '|'
                        . $data['tahun']
                        . '|'
                        . $data['bulan'];

                    if (
                        $dataPegawai === null
                        || ! $dataPegawai
                            ->masihAktif()
                    ) {
                        $kesalahanTerbaru[] =
                            "Baris {$baris->nomor_baris}: pegawai sudah tidak aktif.";
                    }

                    if (
                        $dataKalender === null
                        || ! $dataKalender
                            ->hari_kerja
                    ) {
                        $kesalahanTerbaru[] =
                            "Baris {$baris->nomor_baris}: tanggal bukan hari kerja.";
                    }

                    if (
                        $penggajianTerkunci->has(
                            $kunciPenggajian
                        )
                    ) {
                        $kesalahanTerbaru[] =
                            "Baris {$baris->nomor_baris}: periode penggajian sudah dikunci.";
                    }
                }

                if ($kesalahanTerbaru !== []) {
                    throw ValidationException::withMessages([
                        'import' => implode(
                            ' ',
                            array_slice(
                                $kesalahanTerbaru,
                                0,
                                5
                            )
                        ),
                    ]);
                }

                $jumlahDitambahkan = 0;
                $jumlahDiperbarui = 0;

                foreach (
                    $daftarBaris as $baris
                ) {
                    $data = $baris->data_normal;

                    $kunciAbsensi =
                        $data['pegawai_id']
                        . '|'
                        . $data['tanggal_absensi'];

                    $absensi = $absensiLama->get(
                        $kunciAbsensi
                    );

                    if ($absensi === null) {
                        $absensi = new Absensi();
                        $jumlahDitambahkan++;
                    } else {
                        $jumlahDiperbarui++;
                    }

                    $absensi->fill([
                        'pegawai_id' =>
                        $data['pegawai_id'],

                        'tanggal_absensi' =>
                        $data['tanggal_absensi'],

                        'status' =>
                        $data['status'],

                        'jam_lembur' =>
                        $data['jam_lembur'],

                        'catatan_lembur' =>
                        $data['catatan_lembur'],

                        'catatan' =>
                        $data['catatan'],

                        'sumber' =>
                        Absensi::SUMBER_IMPOR,

                        'import_absensi_id' =>
                        $import->id,

                        'dibuat_oleh' =>
                        $request->user()->id,
                    ]);

                    $absensi->save();
                }

                $import->update([
                    'status' =>
                    ImportAbsensi::STATUS_SELESAI,

                    'jumlah_ditambahkan' =>
                    $jumlahDitambahkan,

                    'jumlah_diperbarui' =>
                    $jumlahDiperbarui,

                    'dikonfirmasi_pada' => now(),
                ]);
            }
        );

        return redirect()
            ->route(
                'admin.import-absensi.show',
                $importAbsensi
            )
            ->with(
                'success',
                'Import absensi berhasil dikonfirmasi.'
            );
    }

    public function batalkan(
        ImportAbsensi $importAbsensi
    ): RedirectResponse {
        if (
            ! $importAbsensi->masihPratinjau()
        ) {
            return back()->with(
                'error',
                'Import yang sudah selesai tidak dapat dibatalkan.'
            );
        }

        $importAbsensi->update([
            'status' =>
            ImportAbsensi::STATUS_DIBATALKAN,
        ]);

        return redirect()
            ->route(
                'admin.import-absensi.index'
            )
            ->with(
                'success',
                'Pratinjau import berhasil dibatalkan.'
            );
    }
}
