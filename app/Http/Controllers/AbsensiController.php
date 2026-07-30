<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SimpanDataAbsensiRequest;
use App\Models\Absensi;
use App\Models\KalenderKerja;
use App\Models\Pegawai;
use App\Models\Penggajian;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AbsensiController extends Controller
{
    public function index(
        Request $request
    ): View {
        $data = $request->validate([
            'tampilan' => [
                'nullable',
                'in:harian,bulanan,detail',
            ],

            'tanggal' => [
                'nullable',
                'date',
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

            'pegawai_id' => [
                'nullable',
                'integer',
                'exists:pegawais,id',
            ],
        ]);

        $tampilan = (string) (
            $data['tampilan'] ?? 'harian'
        );

        $tanggal = CarbonImmutable::parse(
            $data['tanggal']
                ?? now()->toDateString()
        );

        $bulan = (int) (
            $data['bulan'] ?? now()->month
        );

        $tahun = (int) (
            $data['tahun'] ?? now()->year
        );

        $pegawaiDipilih = isset(
            $data['pegawai_id']
        )
            ? (int) $data['pegawai_id']
            : null;

        $daftarPegawai = Pegawai::query()
            ->with('jabatan')
            ->orderBy('nama')
            ->get();

        $pegawaiHarian = collect();
        $jumlahTercatat = 0;
        $jumlahBelumTercatat = 0;
        $jumlahTerkunci = 0;
        $ringkasanBulanan = null;
        $daftarAbsensi = null;

        if ($tampilan === 'harian') {
            $pegawaiHarian = Pegawai::query()
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
                            ->whereIn(
                                'status',
                                [
                                    Penggajian::STATUS_FINAL,
                                    Penggajian::STATUS_DIBAYAR,
                                ]
                            );
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

            $jumlahTercatat =
                $pegawaiHarian
                ->filter(
                    fn(Pegawai $pegawai): bool =>
                    $pegawai
                        ->absensis
                        ->isNotEmpty()
                )
                ->count();

            $jumlahTerkunci =
                $pegawaiHarian
                ->filter(
                    fn(Pegawai $pegawai): bool =>
                    $pegawai
                        ->absensis
                        ->isEmpty()
                        && $pegawai
                        ->penggajians
                        ->isNotEmpty()
                )
                ->count();

            $jumlahBelumTercatat =
                $pegawaiHarian->count()
                - $jumlahTercatat
                - $jumlahTerkunci;
        }

        if ($tampilan === 'bulanan') {
            $ringkasanBulanan =
                Pegawai::query()
                ->with('jabatan')
                ->withCount([
                    'absensis as jumlah_tercatat' =>
                    fn($query) => $query
                        ->whereYear(
                            'tanggal_absensi',
                            $tahun
                        )
                        ->whereMonth(
                            'tanggal_absensi',
                            $bulan
                        ),

                    'absensis as jumlah_hadir' =>
                    fn($query) => $query
                        ->whereYear(
                            'tanggal_absensi',
                            $tahun
                        )
                        ->whereMonth(
                            'tanggal_absensi',
                            $bulan
                        )
                        ->where(
                            'status',
                            Absensi::STATUS_HADIR
                        ),

                    'absensis as jumlah_sakit' =>
                    fn($query) => $query
                        ->whereYear(
                            'tanggal_absensi',
                            $tahun
                        )
                        ->whereMonth(
                            'tanggal_absensi',
                            $bulan
                        )
                        ->where(
                            'status',
                            Absensi::STATUS_SAKIT
                        ),

                    'absensis as jumlah_izin' =>
                    fn($query) => $query
                        ->whereYear(
                            'tanggal_absensi',
                            $tahun
                        )
                        ->whereMonth(
                            'tanggal_absensi',
                            $bulan
                        )
                        ->where(
                            'status',
                            Absensi::STATUS_IZIN
                        ),

                    'absensis as jumlah_cuti' =>
                    fn($query) => $query
                        ->whereYear(
                            'tanggal_absensi',
                            $tahun
                        )
                        ->whereMonth(
                            'tanggal_absensi',
                            $bulan
                        )
                        ->where(
                            'status',
                            Absensi::STATUS_CUTI
                        ),

                    'absensis as jumlah_alpa' =>
                    fn($query) => $query
                        ->whereYear(
                            'tanggal_absensi',
                            $tahun
                        )
                        ->whereMonth(
                            'tanggal_absensi',
                            $bulan
                        )
                        ->where(
                            'status',
                            Absensi::STATUS_ALPA
                        ),
                ])
                ->withSum(
                    [
                        'absensis as total_jam_lembur' =>
                        fn($query) => $query
                            ->whereYear(
                                'tanggal_absensi',
                                $tahun
                            )
                            ->whereMonth(
                                'tanggal_absensi',
                                $bulan
                            ),
                    ],
                    'jam_lembur'
                )
                ->orderBy('nama')
                ->paginate(15)
                ->withQueryString();
        }

        if ($tampilan === 'detail') {
            $daftarAbsensi = Absensi::query()
                ->with([
                    'pegawai.jabatan',
                    'pembuat',
                ])
                ->whereYear(
                    'tanggal_absensi',
                    $tahun
                )
                ->whereMonth(
                    'tanggal_absensi',
                    $bulan
                )
                ->when(
                    $pegawaiDipilih !== null,
                    fn($query) => $query->where(
                        'pegawai_id',
                        $pegawaiDipilih
                    )
                )
                ->orderByDesc(
                    'tanggal_absensi'
                )
                ->orderBy('pegawai_id')
                ->paginate(15)
                ->withQueryString();
        }

        return view(
            'absensi.index',
            compact(
                'tampilan',
                'tanggal',
                'bulan',
                'tahun',
                'pegawaiDipilih',
                'daftarPegawai',
                'pegawaiHarian',
                'jumlahTercatat',
                'jumlahBelumTercatat',
                'jumlahTerkunci',
                'ringkasanBulanan',
                'daftarAbsensi'
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
        ]);

        $bulan = (int) $data['bulan'];
        $tahun = (int) $data['tahun'];

        $ringkasanBulanan = Pegawai::query()
            ->with('jabatan')
            ->withCount([
                'absensis as jumlah_tercatat' =>
                fn($query) => $query
                    ->whereYear(
                        'tanggal_absensi',
                        $tahun
                    )
                    ->whereMonth(
                        'tanggal_absensi',
                        $bulan
                    ),

                'absensis as jumlah_hadir' =>
                fn($query) => $query
                    ->whereYear(
                        'tanggal_absensi',
                        $tahun
                    )
                    ->whereMonth(
                        'tanggal_absensi',
                        $bulan
                    )
                    ->where(
                        'status',
                        Absensi::STATUS_HADIR
                    ),

                'absensis as jumlah_sakit' =>
                fn($query) => $query
                    ->whereYear(
                        'tanggal_absensi',
                        $tahun
                    )
                    ->whereMonth(
                        'tanggal_absensi',
                        $bulan
                    )
                    ->where(
                        'status',
                        Absensi::STATUS_SAKIT
                    ),

                'absensis as jumlah_izin' =>
                fn($query) => $query
                    ->whereYear(
                        'tanggal_absensi',
                        $tahun
                    )
                    ->whereMonth(
                        'tanggal_absensi',
                        $bulan
                    )
                    ->where(
                        'status',
                        Absensi::STATUS_IZIN
                    ),

                'absensis as jumlah_cuti' =>
                fn($query) => $query
                    ->whereYear(
                        'tanggal_absensi',
                        $tahun
                    )
                    ->whereMonth(
                        'tanggal_absensi',
                        $bulan
                    )
                    ->where(
                        'status',
                        Absensi::STATUS_CUTI
                    ),

                'absensis as jumlah_alpa' =>
                fn($query) => $query
                    ->whereYear(
                        'tanggal_absensi',
                        $tahun
                    )
                    ->whereMonth(
                        'tanggal_absensi',
                        $bulan
                    )
                    ->where(
                        'status',
                        Absensi::STATUS_ALPA
                    ),
            ])
            ->withSum(
                [
                    'absensis as total_jam_lembur' =>
                    fn($query) => $query
                        ->whereYear(
                            'tanggal_absensi',
                            $tahun
                        )
                        ->whereMonth(
                            'tanggal_absensi',
                            $bulan
                        ),
                ],
                'jam_lembur'
            )
            ->orderBy('nama')
            ->get();

        $total = [
            'pegawai' => $ringkasanBulanan->count(),
            'tercatat' => $ringkasanBulanan
                ->sum('jumlah_tercatat'),
            'hadir' => $ringkasanBulanan
                ->sum('jumlah_hadir'),
            'sakit' => $ringkasanBulanan
                ->sum('jumlah_sakit'),
            'izin' => $ringkasanBulanan
                ->sum('jumlah_izin'),
            'cuti' => $ringkasanBulanan
                ->sum('jumlah_cuti'),
            'alpa' => $ringkasanBulanan
                ->sum('jumlah_alpa'),
            'jam_lembur' => (float)
            $ringkasanBulanan
                ->sum('total_jam_lembur'),
        ];

        return view(
            'absensi.cetak-rekap',
            compact(
                'ringkasanBulanan',
                'total',
                'bulan',
                'tahun'
            )
        );
    }

    public function cetakDetail(
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
        ]);

        $bulan = (int) $data['bulan'];
        $tahun = (int) $data['tahun'];

        $pegawaiDipilih = isset($data['pegawai_id'])
            ? (int) $data['pegawai_id']
            : null;

        $daftarAbsensi = Absensi::query()
            ->with([
                'pegawai.jabatan',
                'pembuat',
            ])
            ->whereYear(
                'tanggal_absensi',
                $tahun
            )
            ->whereMonth(
                'tanggal_absensi',
                $bulan
            )
            ->when(
                $pegawaiDipilih !== null,
                fn($query) => $query->where(
                    'pegawai_id',
                    $pegawaiDipilih
                )
            )
            ->orderBy('tanggal_absensi')
            ->orderBy('pegawai_id')
            ->get();

        $pegawaiFilter = $pegawaiDipilih !== null
            ? Pegawai::query()->find($pegawaiDipilih)
            : null;

        $ringkasan = [
            'jumlah_data' => $daftarAbsensi->count(),

            'hadir' => $daftarAbsensi
                ->where('status', Absensi::STATUS_HADIR)
                ->count(),

            'sakit' => $daftarAbsensi
                ->where('status', Absensi::STATUS_SAKIT)
                ->count(),

            'izin' => $daftarAbsensi
                ->where('status', Absensi::STATUS_IZIN)
                ->count(),

            'cuti' => $daftarAbsensi
                ->where('status', Absensi::STATUS_CUTI)
                ->count(),

            'alpa' => $daftarAbsensi
                ->where('status', Absensi::STATUS_ALPA)
                ->count(),

            'jam_lembur' => (float)
            $daftarAbsensi->sum('jam_lembur'),
        ];

        return view(
            'absensi.cetak-detail',
            compact(
                'daftarAbsensi',
                'pegawaiFilter',
                'ringkasan',
                'bulan',
                'tahun'
            )
        );
    }



    public function create(): View
    {
        $daftarPegawai = Pegawai::query()
            ->with('jabatan')
            ->where(
                'status_kepegawaian',
                Pegawai::STATUS_AKTIF
            )
            ->orderBy('nama')
            ->get();

        return view(
            'absensi.create',
            compact('daftarPegawai')
        );
    }

    public function store(
        SimpanDataAbsensiRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        if (
            ! $this->merupakanHariKerja(
                $data['tanggal_absensi']
            )
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Absensi hanya dapat dicatat pada tanggal yang tersedia sebagai hari kerja.'
                );
        }

        if (
            $this->periodeTerkunci(
                (int) $data['pegawai_id'],
                $data['tanggal_absensi']
            )
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Absensi tidak dapat ditambahkan karena penggajian periode tersebut sudah final atau dibayar.'
                );
        }

        $data['dibuat_oleh'] =
            $request->user()->id;

        $data['sumber'] =
            Absensi::SUMBER_MANUAL;

        $data['import_absensi_id'] = null;

        Absensi::query()->create($data);

        $tanggal = CarbonImmutable::parse(
            $data['tanggal_absensi']
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
                'Absensi berhasil ditambahkan.'
            );
    }

    public function edit(
        Absensi $absensi
    ): View|RedirectResponse {
        if (
            $this->periodeTerkunci(
                $absensi->pegawai_id,
                $absensi->tanggal_absensi
                    ->toDateString()
            )
        ) {
            return redirect()
                ->route('admin.absensi.index')
                ->with(
                    'error',
                    'Absensi tidak dapat diubah karena penggajian periode tersebut terkunci.'
                );
        }

        $absensi->load([
            'pegawai.jabatan',
            'pembuat',
        ]);

        $daftarPegawai = Pegawai::query()
            ->with('jabatan')
            ->where(
                'status_kepegawaian',
                Pegawai::STATUS_AKTIF
            )
            ->orWhere(
                'id',
                $absensi->pegawai_id
            )
            ->orderBy('nama')
            ->get();

        return view(
            'absensi.edit',
            compact(
                'absensi',
                'daftarPegawai'
            )
        );
    }

    public function update(
        SimpanDataAbsensiRequest $request,
        Absensi $absensi
    ): RedirectResponse {
        $data = $request->validated();

        if (
            ! $this->merupakanHariKerja(
                $data['tanggal_absensi']
            )
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Absensi hanya dapat dicatat pada hari kerja.'
                );
        }

        if (
            $this->periodeTerkunci(
                $absensi->pegawai_id,
                $absensi->tanggal_absensi
                    ->toDateString()
            )
            || $this->periodeTerkunci(
                (int) $data['pegawai_id'],
                $data['tanggal_absensi']
            )
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Absensi tidak dapat diubah karena penggajian periode tersebut terkunci.'
                );
        }

        $absensi->update($data);

        $tanggal = CarbonImmutable::parse(
            $data['tanggal_absensi']
        );

        return redirect()
            ->route(
                'admin.absensi.index',
                [
                    'tampilan' => 'detail',
                    'bulan' => $tanggal->month,
                    'tahun' => $tanggal->year,
                ]
            )
            ->with(
                'success',
                'Absensi berhasil diperbarui.'
            );
    }

    public function destroy(
        Absensi $absensi
    ): RedirectResponse {
        if (
            $this->periodeTerkunci(
                $absensi->pegawai_id,
                $absensi->tanggal_absensi
                    ->toDateString()
            )
        ) {
            return back()->with(
                'error',
                'Absensi tidak dapat dihapus karena penggajian periode tersebut terkunci.'
            );
        }

        $tanggal = $absensi->tanggal_absensi;

        $absensi->delete();

        return redirect()
            ->route(
                'admin.absensi.index',
                [
                    'tampilan' => 'detail',
                    'bulan' => $tanggal->month,
                    'tahun' => $tanggal->year,
                ]
            )
            ->with(
                'success',
                'Absensi berhasil dihapus.'
            );
    }

    private function merupakanHariKerja(
        string $tanggal
    ): bool {
        return KalenderKerja::query()
            ->whereDate('tanggal', $tanggal)
            ->where('hari_kerja', true)
            ->exists();
    }

    private function periodeTerkunci(
        int $pegawaiId,
        string $tanggal
    ): bool {
        $periode = CarbonImmutable::parse(
            $tanggal
        );

        return Penggajian::query()
            ->where(
                'pegawai_id',
                $pegawaiId
            )
            ->where(
                'bulan',
                $periode->month
            )
            ->where(
                'tahun',
                $periode->year
            )
            ->whereIn('status', [
                Penggajian::STATUS_FINAL,
                Penggajian::STATUS_DIBAYAR,
            ])
            ->exists();
    }
}
