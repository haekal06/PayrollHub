<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SimpanAbsensiMassalRequest;
use App\Models\Absensi;
use App\Models\KalenderKerja;
use App\Models\Pegawai;
use App\Models\Penggajian;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class AbsensiMassalController extends Controller
{
    public function create(
        Request $request
    ): View {
        $data = $request->validate([
            'tanggal' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],
        ]);

        $tanggal = CarbonImmutable::parse(
            $data['tanggal']
                ?? now()->toDateString()
        );

        $kalender = KalenderKerja::query()
            ->whereDate(
                'tanggal',
                $tanggal->toDateString()
            )
            ->first();

        $dapatDicatat =
            $kalender !== null
            && $kalender->hari_kerja;

        $daftarPegawai = Pegawai::query()
            ->with([
                'jabatan',

                'absensis' => function (
                    $query
                ) use ($tanggal): void {
                    $query->whereDate(
                        'tanggal_absensi',
                        $tanggal->toDateString()
                    );
                },

                'penggajians' => function (
                    $query
                ) use ($tanggal): void {
                    $query
                        ->where(
                            'bulan',
                            $tanggal->month
                        )
                        ->where(
                            'tahun',
                            $tanggal->year
                        )
                        ->whereIn('status', [
                            Penggajian::STATUS_FINAL,
                            Penggajian::STATUS_DIBAYAR,
                        ]);
                },
            ])
            ->where(
                'status_kepegawaian',
                Pegawai::STATUS_AKTIF
            )
            ->whereDate(
                'tanggal_masuk',
                '<=',
                $tanggal->toDateString()
            )
            ->orderBy('nama')
            ->get();

        $jumlahTerkunci = $daftarPegawai
            ->filter(
                fn(Pegawai $pegawai): bool =>
                $pegawai
                    ->penggajians
                    ->isNotEmpty()
            )
            ->count();

        $jumlahDapatDiubah =
            $daftarPegawai->count()
            - $jumlahTerkunci;

        return view(
            'absensi.massal',
            compact(
                'tanggal',
                'kalender',
                'dapatDicatat',
                'daftarPegawai',
                'jumlahTerkunci',
                'jumlahDapatDiubah'
            )
        );
    }

    public function store(
        SimpanAbsensiMassalRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        $tanggal = CarbonImmutable::parse(
            $data['tanggal_absensi']
        );

        $kalender = KalenderKerja::query()
            ->whereDate(
                'tanggal',
                $tanggal->toDateString()
            )
            ->first();

        if ($kalender === null) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Tanggal belum tersedia pada Kalender Kerja.'
                );
        }

        if (! $kalender->hari_kerja) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Absensi massal hanya dapat disimpan pada hari kerja.'
                );
        }

        $idPegawai = collect($data['data'])
            ->pluck('pegawai_id')
            ->map(
                fn(mixed $id): int => (int) $id
            )
            ->values();

        $adaPeriodeTerkunci =
            Penggajian::query()
            ->whereIn(
                'pegawai_id',
                $idPegawai
            )
            ->where(
                'bulan',
                $tanggal->month
            )
            ->where(
                'tahun',
                $tanggal->year
            )
            ->whereIn('status', [
                Penggajian::STATUS_FINAL,
                Penggajian::STATUS_DIBAYAR,
            ])
            ->exists();

        if ($adaPeriodeTerkunci) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Sebagian absensi tidak dapat diubah karena penggajian sudah final atau dibayar.'
                );
        }

        DB::transaction(
            function () use (
                $request,
                $data,
                $tanggal
            ): void {
                foreach (
                    $data['data'] as $baris
                ) {
                    $status = $baris['status'];

                    $jamLembur =
                        $status ===
                        Absensi::STATUS_HADIR
                        ? (float) $baris['jam_lembur']
                        : 0;

                    $catatanLembur =
                        $jamLembur > 0
                        ? $baris['catatan_lembur']
                        : null;

                    Absensi::query()
                        ->updateOrCreate(
                            [
                                'pegawai_id' =>
                                (int) $baris['pegawai_id'],

                                'tanggal_absensi' =>
                                $tanggal
                                    ->toDateString(),
                            ],
                            [
                                'status' => $status,

                                'jam_lembur' =>
                                $jamLembur,

                                'catatan_lembur' =>
                                $catatanLembur,

                                'catatan' =>
                                $baris['catatan']
                                    ?? null,

                                'sumber' =>
                                Absensi::SUMBER_MASSAL,

                                'import_absensi_id' =>
                                null,

                                'dibuat_oleh' =>
                                $request
                                    ->user()
                                    ->id,
                            ]
                        );
                }
            }
        );

        return redirect()
            ->route(
                'admin.absensi.index',
                [
                    'tampilan' => 'harian',
                    'tanggal' =>
                    $tanggal->toDateString(),
                ]
            )
            ->with(
                'success',
                'Absensi massal berhasil disimpan.'
            );
    }
}
