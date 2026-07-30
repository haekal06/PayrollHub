<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BukaRevisiPenggajianRequest;
use App\Http\Requests\ProsesPenggajianRequest;
use App\Http\Requests\TandaiPenggajianDibayarRequest;
use App\Models\Pegawai;
use App\Models\Penggajian;
use App\Models\Absensi;
use App\Services\LayananStatusPenggajian;
use App\Services\PemrosesPenggajian;
use App\Services\RingkasanAbsensiPenggajian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use LogicException;

final class PenggajianController extends Controller
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

            'pegawai_id' => [
                'nullable',
                'integer',
                'exists:pegawais,id',
            ],

            'status' => [
                'nullable',
                'string',
                'in:draf,revisi,final,dibayar',
            ],
        ]);

        $bulan = (int) (
            $data['bulan'] ?? now()->month
        );

        $tahun = (int) (
            $data['tahun'] ?? now()->year
        );

        $pegawaiDipilih =
            isset($data['pegawai_id'])
            ? (int) $data['pegawai_id']
            : null;

        $statusDipilih =
            $data['status'] ?? null;

        $queryDasar = Penggajian::query()
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->when(
                $pegawaiDipilih !== null,
                fn($query) => $query->where(
                    'pegawai_id',
                    $pegawaiDipilih
                )
            )
            ->when(
                $statusDipilih !== null,
                fn($query) => $query->where(
                    'status',
                    $statusDipilih
                )
            );

        $ringkasan = [
            'jumlah_penggajian' => (clone $queryDasar)->count(),

            'total_gaji_kotor' => (float)
            (clone $queryDasar)->sum(
                'gaji_kotor'
            ),

            'total_upah_lembur' => (float)
            (clone $queryDasar)->sum(
                'upah_lembur'
            ),

            'total_potongan' => (float)
            (clone $queryDasar)->sum(
                'total_potongan'
            ),

            'total_gaji_bersih' => (float)
            (clone $queryDasar)->sum(
                'gaji_bersih'
            ),

            'jumlah_draf' => (clone $queryDasar)
                ->where(
                    'status',
                    Penggajian::STATUS_DRAF
                )
                ->count(),

            'jumlah_revisi' => (clone $queryDasar)
                ->where(
                    'status',
                    Penggajian::STATUS_REVISI
                )
                ->count(),

            'jumlah_final' => (clone $queryDasar)
                ->where(
                    'status',
                    Penggajian::STATUS_FINAL
                )
                ->count(),

            'jumlah_dibayar' => (clone $queryDasar)
                ->where(
                    'status',
                    Penggajian::STATUS_DIBAYAR
                )
                ->count(),
        ];

        $daftarPenggajian =
            (clone $queryDasar)
            ->with([
                'pegawai.jabatan',
                'pemroses',
            ])
            ->orderBy('pegawai_id')
            ->paginate(15)
            ->withQueryString();

        /*
         * Tidak dipaginasi agar seluruh data
         * periode masuk ke laporan cetak.
         */
        $laporanPenggajian =
            (clone $queryDasar)
            ->with([
                'pegawai.jabatan',
                'pemroses',
            ])
            ->orderBy('pegawai_id')
            ->get();

        $daftarPegawai = Pegawai::query()
            ->orderBy('nama')
            ->get([
                'id',
                'nip',
                'nama',
                'status_kepegawaian',
            ]);

        return view(
            'penggajian.index',
            compact(
                'daftarPenggajian',
                'laporanPenggajian',
                'daftarPegawai',
                'ringkasan',
                'bulan',
                'tahun',
                'pegawaiDipilih',
                'statusDipilih'
            )
        );
    }

    public function create(
        Request $request,
        RingkasanAbsensiPenggajian
        $layananRingkasan
    ): View {
        $data = $request->validate([
            'pegawai_id' => [
                'nullable',
                'integer',
                'exists:pegawais,id',
            ],

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

        $daftarPegawai = Pegawai::query()
            ->with('jabatan')
            ->where(
                'status_kepegawaian',
                Pegawai::STATUS_AKTIF
            )
            ->orderBy('nama')
            ->get();

        $pegawaiDipilih =
            isset($data['pegawai_id'])
            ? (int) $data['pegawai_id']
            : null;

        $bulan = (int) (
            $data['bulan'] ?? now()->month
        );

        $tahun = (int) (
            $data['tahun'] ?? now()->year
        );

        $pegawai = null;
        $ringkasanAbsensi = null;
        $kesalahanPratinjau = null;

        if ($pegawaiDipilih !== null) {
            $pegawai = $daftarPegawai
                ->firstWhere(
                    'id',
                    $pegawaiDipilih
                );

            if ($pegawai === null) {
                $kesalahanPratinjau =
                    'Pegawai tidak ditemukan atau sudah tidak aktif.';
            } else {
                try {
                    $ringkasanAbsensi =
                        $layananRingkasan->hitung(
                            $pegawai,
                            $bulan,
                            $tahun
                        );
                } catch (
                    LogicException $exception
                ) {
                    $kesalahanPratinjau =
                        $exception->getMessage();
                }
            }
        }

        return view(
            'penggajian.create',
            compact(
                'daftarPegawai',
                'pegawaiDipilih',
                'pegawai',
                'ringkasanAbsensi',
                'kesalahanPratinjau',
                'bulan',
                'tahun'
            )
        );
    }

    public function store(
        ProsesPenggajianRequest $request,
        PemrosesPenggajian $pemroses
    ): RedirectResponse {
        $data = $request->validated();

        $pegawai = Pegawai::query()
            ->findOrFail($data['pegawai_id']);

        try {
            $penggajian = $pemroses->proses(
                pegawai: $pegawai,
                pemroses: $request->user(),
                bulan: (int) $data['bulan'],
                tahun: (int) $data['tahun'],
                bonus: (float) $data['bonus'],

                catatanBonus: $data['catatan_bonus'] ?? null,

                potonganLain: (float) $data['potongan_lain'],

                catatanPotongan: $data['catatan_potongan']
                    ?? null,
            );
        } catch (
            InvalidArgumentException
            | LogicException $exception
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    $exception->getMessage()
                );
        }

        return redirect()
            ->route(
                'admin.penggajian.show',
                $penggajian
            )
            ->with(
                'success',
                'Penggajian berhasil diproses sebagai draf.'
            );
    }

    public function show(
        Penggajian $penggajian
    ): View {
        $penggajian->load([
            'pegawai.jabatan',
            'pemroses',
            'pemfinalisasi',
            'pembayar',
            'riwayatStatus.pengubah',
        ]);

        return view(
            'penggajian.show',
            compact('penggajian')
        );
    }

    public function cetakSlip(
        Penggajian $penggajian
    ): View {
        $penggajian->load([
            'pegawai.jabatan',
            'pemroses',
            'pemfinalisasi',
            'pembayar',
        ]);

        $detailAbsensi = Absensi::query()
            ->where(
                'pegawai_id',
                $penggajian->pegawai_id
            )
            ->whereYear(
                'tanggal_absensi',
                $penggajian->tahun
            )
            ->whereMonth(
                'tanggal_absensi',
                $penggajian->bulan
            )
            ->orderBy('tanggal_absensi')
            ->get();

        return view(
            'penggajian.cetak-slip',
            compact(
                'penggajian',
                'detailAbsensi'
            )
        );
    }

    public function cetakRekap(
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

            'pegawai_id' => [
                'nullable',
                'integer',
                'exists:pegawais,id',
            ],

            'status' => [
                'nullable',
                'string',
                'in:draf,revisi,final,dibayar',
            ],
        ]);

        $bulan = (int) $data['bulan'];
        $tahun = (int) $data['tahun'];

        $pegawaiDipilih = isset($data['pegawai_id'])
            ? (int) $data['pegawai_id']
            : null;

        $statusDipilih = $data['status'] ?? null;

        $query = Penggajian::query()
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->when(
                $pegawaiDipilih !== null,
                fn($query) => $query->where(
                    'pegawai_id',
                    $pegawaiDipilih
                )
            )
            ->when(
                $statusDipilih !== null,
                fn($query) => $query->where(
                    'status',
                    $statusDipilih
                )
            );

        $ringkasan = [
            'jumlah_penggajian' => (clone $query)->count(),

            'total_gaji_kotor' => (float)
            (clone $query)->sum('gaji_kotor'),

            'total_upah_lembur' => (float)
            (clone $query)->sum('upah_lembur'),

            'total_potongan' => (float)
            (clone $query)->sum('total_potongan'),

            'total_gaji_bersih' => (float)
            (clone $query)->sum('gaji_bersih'),
        ];

        $laporanPenggajian = (clone $query)
            ->with([
                'pegawai.jabatan',
                'pemroses',
            ])
            ->orderBy('pegawai_id')
            ->get();

        $pegawaiFilter = $pegawaiDipilih !== null
            ? Pegawai::query()->find($pegawaiDipilih)
            : null;

        return view(
            'penggajian.cetak-rekap',
            compact(
                'laporanPenggajian',
                'ringkasan',
                'pegawaiFilter',
                'bulan',
                'tahun',
                'statusDipilih'
            )
        );
    }

    public function finalisasi(
        Request $request,
        Penggajian $penggajian,
        PemrosesPenggajian $pemroses,
        LayananStatusPenggajian $layananStatus
    ): RedirectResponse {
        $penggajian->load('pegawai');

        try {
            /*
             * Hitung ulang dari absensi terbaru
             * sebelum status menjadi final.
             */
            $hasilTerbaru = $pemroses->proses(
                pegawai: $penggajian->pegawai,
                pemroses: $request->user(),
                bulan: $penggajian->bulan,
                tahun: $penggajian->tahun,
                bonus: (float) $penggajian->bonus,

                catatanBonus: $penggajian->catatan_bonus,

                potonganLain: (float) $penggajian
                    ->potongan_lain,

                catatanPotongan: $penggajian
                    ->catatan_potongan,
            );

            $hasilTerbaru =
                $layananStatus->finalisasi(
                    $hasilTerbaru,
                    $request->user()
                );
        } catch (
            InvalidArgumentException
            | LogicException $exception
        ) {
            return back()->with(
                'error',
                $exception->getMessage()
            );
        }

        return redirect()
            ->route(
                'admin.penggajian.show',
                $hasilTerbaru
            )
            ->with(
                'success',
                'Penggajian berhasil difinalisasi.'
            );
    }

    public function bukaRevisi(
        BukaRevisiPenggajianRequest $request,
        Penggajian $penggajian,
        LayananStatusPenggajian $layananStatus
    ): RedirectResponse {
        try {
            $hasil = $layananStatus
                ->bukaUntukRevisi(
                    penggajian: $penggajian,
                    pelaku: $request->user(),

                    alasan: (string)
                    $request->validated(
                        'alasan_revisi'
                    ),
                );
        } catch (LogicException $exception) {
            return back()->with(
                'error',
                $exception->getMessage()
            );
        }

        return redirect()
            ->route(
                'admin.penggajian.show',
                $hasil
            )
            ->with(
                'success',
                'Penggajian dibuka untuk revisi. Absensi periode ini dapat diperbaiki kembali.'
            );
    }

    public function tandaiDibayar(
        TandaiPenggajianDibayarRequest $request,
        Penggajian $penggajian,
        LayananStatusPenggajian $layananStatus
    ): RedirectResponse {
        try {
            $hasil = $layananStatus
                ->tandaiDibayar(
                    penggajian: $penggajian,
                    pelaku: $request->user(),

                    catatan: $request->validated(
                        'catatan_pembayaran'
                    ),
                );
        } catch (LogicException $exception) {
            return back()->with(
                'error',
                $exception->getMessage()
            );
        }

        return redirect()
            ->route(
                'admin.penggajian.show',
                $hasil
            )
            ->with(
                'success',
                'Penggajian berhasil ditandai sudah dibayar.'
            );
    }
}
