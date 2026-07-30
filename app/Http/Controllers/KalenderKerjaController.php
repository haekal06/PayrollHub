<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BuatKalenderKerjaRequest;
use App\Http\Requests\PerbaruiKalenderKerjaRequest;
use App\Models\KalenderKerja;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class KalenderKerjaController extends Controller
{
    public function index(
        Request $request
    ): View {
        $data = $request->validate([
            'bulan' => [
                'nullable',
                'integer',
                'between:1,12',
            ],

            'tahun' => [
                'nullable',
                'integer',
                'between:2000,2100',
            ],
        ]);

        $bulan = (int) (
            $data['bulan'] ?? now()->month
        );

        $tahun = (int) (
            $data['tahun'] ?? now()->year
        );

        $daftarKalender =
            KalenderKerja::query()
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('tanggal')
            ->get();

        $jumlahHariKerja =
            $daftarKalender
            ->where('hari_kerja', true)
            ->count();

        $jumlahHariLibur =
            $daftarKalender
            ->where('hari_kerja', false)
            ->count();

        $jumlahHariSeharusnya =
            CarbonImmutable::create(
                $tahun,
                $bulan,
                1
            )->daysInMonth;

        $kalenderLengkap =
            $daftarKalender->count()
            === $jumlahHariSeharusnya;

        return view(
            'kalender-kerja.index',
            compact(
                'daftarKalender',
                'bulan',
                'tahun',
                'jumlahHariKerja',
                'jumlahHariLibur',
                'jumlahHariSeharusnya',
                'kalenderLengkap'
            )
        );
    }

    public function cetak(
        Request $request
    ): View {
        $data = $request->validate([
            'bulan' => [
                'required',
                'integer',
                'between:1,12',
            ],

            'tahun' => [
                'required',
                'integer',
                'between:2000,2100',
            ],
        ]);

        $bulan = (int) $data['bulan'];
        $tahun = (int) $data['tahun'];

        $daftarKalender = KalenderKerja::query()
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('tanggal')
            ->get();

        $jumlahHariKerja = $daftarKalender
            ->where('hari_kerja', true)
            ->count();

        $jumlahHariLibur = $daftarKalender
            ->where('hari_kerja', false)
            ->count();

        $jumlahHariSeharusnya =
            CarbonImmutable::create(
                $tahun,
                $bulan,
                1
            )->daysInMonth;

        $kalenderLengkap =
            $daftarKalender->count()
            === $jumlahHariSeharusnya;

        return view(
            'kalender-kerja.cetak',
            compact(
                'daftarKalender',
                'bulan',
                'tahun',
                'jumlahHariKerja',
                'jumlahHariLibur',
                'jumlahHariSeharusnya',
                'kalenderLengkap'
            )
        );
    }

    public function buat(
        BuatKalenderKerjaRequest $request
    ): RedirectResponse {
        $bulan = (int) $request->validated(
            'bulan'
        );

        $tahun = (int) $request->validated(
            'tahun'
        );

        $tanggalAwal =
            CarbonImmutable::create(
                $tahun,
                $bulan,
                1
            )->startOfDay();

        $tanggalAkhir =
            $tanggalAwal->endOfMonth();

        DB::transaction(
            function () use (
                $request,
                $tanggalAwal,
                $tanggalAkhir
            ): void {
                $tanggal = $tanggalAwal;

                while (
                    $tanggal->lessThanOrEqualTo(
                        $tanggalAkhir
                    )
                ) {
                    $akhirPekan =
                        $tanggal->isSaturday()
                        || $tanggal->isSunday();

                    KalenderKerja::query()
                        ->firstOrCreate(
                            [
                                'tanggal' =>
                                $tanggal
                                    ->toDateString(),
                            ],
                            [
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
                                $request->user()->id,
                            ]
                        );

                    $tanggal =
                        $tanggal->addDay();
                }
            }
        );

        return redirect()
            ->route(
                'admin.kalender-kerja.index',
                [
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ]
            )
            ->with(
                'success',
                'Kalender kerja berhasil dibuat.'
            );
    }

    public function perbaruiBulan(
        PerbaruiKalenderKerjaRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        $bulan = (int) $data['bulan'];
        $tahun = (int) $data['tahun'];

        DB::transaction(
            function () use (
                $request,
                $data,
                $bulan,
                $tahun
            ): void {
                foreach (
                    $data['kalender'] as $baris
                ) {
                    $kalender =
                        KalenderKerja::query()
                        ->whereKey($baris['id'])
                        ->whereYear(
                            'tanggal',
                            $tahun
                        )
                        ->whereMonth(
                            'tanggal',
                            $bulan
                        )
                        ->firstOrFail();

                    $jenisHari =
                        $baris['jenis_hari'];

                    $kalender->update([
                        'jenis_hari' =>
                        $jenisHari,

                        'hari_kerja' =>
                        $jenisHari ===
                            KalenderKerja::JENIS_HARI_KERJA,

                        'keterangan' =>
                        filled(
                            $baris['keterangan'] ?? null
                        )
                            ? trim(
                                (string) $baris['keterangan']
                            )
                            : null,

                        'dibuat_oleh' =>
                        $request->user()->id,
                    ]);
                }
            }
        );

        return redirect()
            ->route(
                'admin.kalender-kerja.index',
                [
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ]
            )
            ->with(
                'success',
                'Kalender kerja berhasil diperbarui.'
            );
    }
}
