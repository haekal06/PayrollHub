<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\ImportAbsensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

final class ImportAbsensiTest extends TestCase
{
    use RefreshDatabase;
    use MembuatDataUji;

    public function test_admin_dapat_memeriksa_dan_mengonfirmasi_import_absensi(): void
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

        $folderPengujian = storage_path(
            'framework/testing'
        );

        File::ensureDirectoryExists(
            $folderPengujian
        );

        $lokasiFile =
            $folderPengujian
            . DIRECTORY_SEPARATOR
            . 'import-absensi-test.xlsx';

        $spreadsheet = new Spreadsheet();
        $lembar = $spreadsheet->getActiveSheet();

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
                [
                    $pegawai->nip,
                    $tanggal,
                    'hadir',
                    2,
                    'Menyelesaikan laporan',
                    'Masuk tepat waktu',
                ],
            ],
            null,
            'A1'
        );

        $writer = new Xlsx($spreadsheet);
        $writer->save($lokasiFile);

        $spreadsheet->disconnectWorksheets();

        try {
            $file = new UploadedFile(
                path: $lokasiFile,
                originalName: 'import-absensi-test.xlsx',

                mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

                error: null,
                test: true,
            );

            $responsePratinjau = $this
                ->actingAs($admin)
                ->post(
                    route(
                        'admin.import-absensi.pratinjau'
                    ),
                    [
                        'file_absensi' => $file,
                    ]
                );
        } finally {
            if (File::exists($lokasiFile)) {
                File::delete($lokasiFile);
            }
        }

        $import = ImportAbsensi::query()
            ->firstOrFail();

        $responsePratinjau->assertRedirect(
            route(
                'admin.import-absensi.show',
                $import
            )
        );

        $this->assertSame(
            ImportAbsensi::STATUS_PRATINJAU,
            $import->status
        );

        $this->assertSame(
            1,
            $import->jumlah_baris
        );

        $this->assertSame(
            1,
            $import->jumlah_valid
        );

        $this->assertSame(
            0,
            $import->jumlah_tidak_valid
        );

        $this->assertDatabaseHas(
            'baris_import_absensis',
            [
                'import_absensi_id' =>
                $import->id,

                'nomor_baris' => 2,
                'valid' => 1,
            ]
        );

        $responseKonfirmasi = $this
            ->actingAs($admin)
            ->post(
                route(
                    'admin.import-absensi.konfirmasi',
                    $import
                )
            );

        $responseKonfirmasi->assertRedirect(
            route(
                'admin.import-absensi.show',
                $import
            )
        );

        $responseKonfirmasi
            ->assertSessionHas('success');

        $import->refresh();

        $this->assertSame(
            ImportAbsensi::STATUS_SELESAI,
            $import->status
        );

        $this->assertSame(
            1,
            $import->jumlah_ditambahkan
        );

        $this->assertSame(
            0,
            $import->jumlah_diperbarui
        );

        $this->assertDatabaseHas(
            'absensis',
            [
                'pegawai_id' =>
                $pegawai->id,

                'status' =>
                Absensi::STATUS_HADIR,

                'jam_lembur' => 2,

                'sumber' =>
                Absensi::SUMBER_IMPOR,

                'import_absensi_id' =>
                $import->id,

                'dibuat_oleh' =>
                $admin->id,
            ]
        );
    }
}
