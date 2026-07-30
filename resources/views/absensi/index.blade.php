@extends('layouts.app')

@section('title', 'Data Absensi - PayrollHub')

@section('content')
@php
$namaBulan = [
1 => 'Januari',
2 => 'Februari',
3 => 'Maret',
4 => 'April',
5 => 'Mei',
6 => 'Juni',
7 => 'Juli',
8 => 'Agustus',
9 => 'September',
10 => 'Oktober',
11 => 'November',
12 => 'Desember',
];

$labelStatus = [
'hadir' => 'Hadir',
'sakit' => 'Sakit',
'izin' => 'Izin',
'cuti' => 'Cuti',
'alpa' => 'Alpa',
];
@endphp

<div class="page-header">
    <div>
        <h1>Data Absensi</h1>

        <p class="muted">
            Pencatatan harian dan rekap kehadiran pegawai.
        </p>
    </div>

    <div class="form-actions no-print" style="margin: 0;">
        <a
            href="{{ route('admin.absensi.create') }}"
            class="button">
            Tambah Manual
        </a>

        <a
            href="{{ route('admin.absensi.massal.create') }}"
            class="button">
            Input Massal
        </a>

        <a
            href="{{ route('admin.import-absensi.index') }}"
            class="button">
            Import Excel
        </a>
    </div>
</div>

<div class="attendance-tabs no-print">
    <a
        href="{{ route(
            'admin.absensi.index',
            [
                'tampilan' => 'harian',
                'tanggal' => $tanggal->format('Y-m-d'),
            ]
        ) }}"
        class="tab-link
            {{ $tampilan === 'harian'
                ? 'tab-link-active'
                : '' }}">
        Absensi Harian
    </a>

    <a
        href="{{ route(
            'admin.absensi.index',
            [
                'tampilan' => 'bulanan',
                'bulan' => $bulan,
                'tahun' => $tahun,
            ]
        ) }}"
        class="tab-link
            {{ $tampilan === 'bulanan'
                ? 'tab-link-active'
                : '' }}">
        Rekap Bulanan
    </a>

    <a
        href="{{ route(
            'admin.absensi.index',
            [
                'tampilan' => 'detail',
                'bulan' => $bulan,
                'tahun' => $tahun,
            ]
        ) }}"
        class="tab-link
            {{ $tampilan === 'detail'
                ? 'tab-link-active'
                : '' }}">
        Detail Bulanan
    </a>
</div>

@if ($tampilan === 'harian')
<section class="card no-print" style="margin-bottom: 24px;">
    <form
        method="GET"
        action="{{ route('admin.absensi.index') }}">
        <input
            type="hidden"
            name="tampilan"
            value="harian">

        <div class="filter-grid">
            <div class="form-group">
                <label for="tanggal">Tanggal</label>

                <input
                    id="tanggal"
                    name="tanggal"
                    type="date"
                    value="{{ $tanggal->format('Y-m-d') }}"
                    max="{{ now()->format('Y-m-d') }}"
                    required>
            </div>

            <div class="form-group">
                <button class="button" type="submit">
                    Tampilkan
                </button>
            </div>
        </div>
    </form>
</section>

<div class="summary-strip">
    <div class="summary-item">
        Tanggal

        <strong style="font-size: 20px;">
            {{ $tanggal
                    ->locale('id')
                    ->translatedFormat('d F Y') }}
        </strong>
    </div>

    <div class="summary-item">
        Tercatat
        <strong>{{ $jumlahTercatat }}</strong>
    </div>

    <div class="summary-item">
        Belum Tercatat
        <strong>{{ $jumlahBelumTercatat }}</strong>
    </div>

    <div class="summary-item">
        Terkunci
        <strong>{{ $jumlahTerkunci }}</strong>
    </div>
</div>

@if ($pegawaiHarian->isEmpty())
<section class="card">
    <p>
        Tidak ada pegawai aktif yang dapat
        dicatat pada tanggal ini.
    </p>
</section>
@else
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>NIP</th>
                <th>Pegawai</th>
                <th>Jabatan</th>
                <th>Status</th>
                <th>Lembur</th>
                <th>Catatan</th>
                <th class="no-print">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($pegawaiHarian as $pegawai)
            @php
            $absensi =
            $pegawai->absensis->first();

            $terkunci =
            $pegawai->penggajians->isNotEmpty();
            @endphp

            <tr>
                <td>{{ $pegawai->nip }}</td>
                <td>{{ $pegawai->nama }}</td>
                <td>{{ $pegawai->jabatan->nama }}</td>

                <td>
                    @if ($absensi !== null)
                    <span class="
                                        status-badge
                                        status-{{ $absensi->status }}
                                    ">
                        {{ $labelStatus[$absensi->status] }}
                    </span>
                    @elseif ($terkunci)
                    <span class="
                                        status-badge
                                        status-terkunci
                                    ">
                        Terkunci
                    </span>
                    @else
                    <span class="
                                        status-badge
                                        status-kosong
                                    ">
                        Belum Dicatat
                    </span>
                    @endif
                </td>

                <td>
                    @if (
                    $absensi !== null
                    && $absensi->memilikiLembur()
                    )
                    {{ number_format(
                                        (float) $absensi->jam_lembur,
                                        1,
                                        ',',
                                        '.'
                                    ) }}
                    jam
                    @else
                    -
                    @endif
                </td>

                <td>
                    {{ $absensi?->catatan ?? '-' }}
                </td>

                <td class="no-print">
                    @if ($absensi !== null && ! $terkunci)
                    <a
                        href="{{ route(
                                            'admin.absensi.edit',
                                            $absensi
                                        ) }}"
                        class="button button-small">
                        Ubah
                    </a>
                    @elseif ($absensi === null && ! $terkunci)
                    <a
                        href="{{ route(
                                            'admin.absensi.create',
                                            [
                                                'pegawai_id' => $pegawai->id,
                                                'tanggal_absensi' =>
                                                    $tanggal->format('Y-m-d'),
                                            ]
                                        ) }}"
                        class="button button-small">
                        Catat
                    </a>
                    @else
                    <span class="muted">
                        Periode terkunci
                    </span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endif

@if ($tampilan === 'bulanan')
<section class="card no-print" style="margin-bottom: 24px;">
    <form
        method="GET"
        action="{{ route('admin.absensi.index') }}">
        <input
            type="hidden"
            name="tampilan"
            value="bulanan">

        <div class="filter-grid">
            <div class="form-group">
                <label for="bulan">Bulan</label>

                <select id="bulan" name="bulan">
                    @foreach ($namaBulan as $nomor => $nama)
                    <option
                        value="{{ $nomor }}"
                        @selected($bulan===$nomor)>
                        {{ $nama }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="tahun">Tahun</label>

                <input
                    id="tahun"
                    name="tahun"
                    type="number"
                    min="2000"
                    max="2100"
                    value="{{ $tahun }}"
                    required>
            </div>

            <div class="form-group">
                <button class="button" type="submit">
                    Tampilkan Rekap
                </button>
            </div>
        </div>
    </form>
</section>

<div class="page-header">
    <h2>
        Rekap {{ $namaBulan[$bulan] }} {{ $tahun }}
    </h2>

    <a
        href="{{ route(
        'admin.absensi.cetak-rekap',
        [
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]
    ) }}"
        class="button button-secondary"
        target="_blank"
        rel="noopener">
        Cetak Rekap
    </a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>NIP</th>
                <th>Pegawai</th>
                <th>Jabatan</th>
                <th>Tercatat</th>
                <th>Hadir</th>
                <th>Sakit</th>
                <th>Izin</th>
                <th>Cuti</th>
                <th>Alpa</th>
                <th>Lembur</th>
                <th class="no-print">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($ringkasanBulanan as $pegawai)
            <tr>
                <td>{{ $pegawai->nip }}</td>
                <td>{{ $pegawai->nama }}</td>
                <td>{{ $pegawai->jabatan->nama }}</td>
                <td>{{ $pegawai->jumlah_tercatat }}</td>
                <td>{{ $pegawai->jumlah_hadir }}</td>
                <td>{{ $pegawai->jumlah_sakit }}</td>
                <td>{{ $pegawai->jumlah_izin }}</td>
                <td>{{ $pegawai->jumlah_cuti }}</td>
                <td>{{ $pegawai->jumlah_alpa }}</td>

                <td>
                    {{ number_format(
                                (float) ($pegawai->total_jam_lembur ?? 0),
                                1,
                                ',',
                                '.'
                            ) }}
                    jam
                </td>

                <td class="no-print">
                    <a
                        href="{{ route(
                                    'admin.absensi.index',
                                    [
                                        'tampilan' => 'detail',
                                        'bulan' => $bulan,
                                        'tahun' => $tahun,
                                        'pegawai_id' => $pegawai->id,
                                    ]
                                ) }}"
                        class="button button-small button-secondary">
                        Lihat Detail
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11">
                    Tidak ada data pegawai.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($ringkasanBulanan->hasPages())
<div class="no-print">
    {{ $ringkasanBulanan->links() }}
</div>
@endif
@endif

@if ($tampilan === 'detail')
<section class="card no-print" style="margin-bottom: 24px;">
    <form
        method="GET"
        action="{{ route('admin.absensi.index') }}">
        <input
            type="hidden"
            name="tampilan"
            value="detail">

        <div class="filter-grid">
            <div class="form-group">
                <label for="bulan">Bulan</label>

                <select id="bulan" name="bulan">
                    @foreach ($namaBulan as $nomor => $nama)
                    <option
                        value="{{ $nomor }}"
                        @selected($bulan===$nomor)>
                        {{ $nama }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="tahun">Tahun</label>

                <input
                    id="tahun"
                    name="tahun"
                    type="number"
                    min="2000"
                    max="2100"
                    value="{{ $tahun }}"
                    required>
            </div>

            <div class="form-group">
                <label for="pegawai_id">Pegawai</label>

                <select id="pegawai_id" name="pegawai_id">
                    <option value="">Semua Pegawai</option>

                    @foreach ($daftarPegawai as $pegawai)
                    <option
                        value="{{ $pegawai->id }}"
                        @selected(
                        $pegawaiDipilih===$pegawai->id
                        )>
                        {{ $pegawai->nip }} -
                        {{ $pegawai->nama }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <button class="button" type="submit">
                    Tampilkan Detail
                </button>
            </div>
        </div>
    </form>
</section>

<div class="page-header">
    <h2>
        Detail {{ $namaBulan[$bulan] }} {{ $tahun }}
    </h2>

    <a
        href="{{ route(
        'admin.absensi.cetak-detail',
        [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'pegawai_id' => $pegawaiDipilih,
        ]
    ) }}"
        class="button button-secondary"
        target="_blank"
        rel="noopener">
        Cetak Detail
    </a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>NIP</th>
                <th>Pegawai</th>
                <th>Status</th>
                <th>Lembur</th>
                <th>Keterangan Lembur</th>
                <th>Catatan</th>
                <th>Sumber</th>
                <th>Dicatat Oleh</th>
                <th class="no-print">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($daftarAbsensi as $absensi)
            <tr>
                <td>
                    {{ $absensi->tanggal_absensi->format('d-m-Y') }}
                </td>

                <td>{{ $absensi->pegawai->nip }}</td>
                <td>{{ $absensi->pegawai->nama }}</td>

                <td>
                    <span class="
                                status-badge
                                status-{{ $absensi->status }}
                            ">
                        {{ $labelStatus[$absensi->status] }}
                    </span>
                </td>

                <td>
                    {{ number_format(
                                (float) $absensi->jam_lembur,
                                1,
                                ',',
                                '.'
                            ) }}
                    jam
                </td>

                <td>
                    {{ $absensi->catatan_lembur ?? '-' }}
                </td>

                <td>{{ $absensi->catatan ?? '-' }}</td>

                <td>{{ ucfirst($absensi->sumber) }}</td>

                <td>
                    {{ $absensi->pembuat?->nama ?? 'Sistem' }}
                </td>

                <td class="no-print">
                    <div class="form-actions" style="margin: 0;">
                        <a
                            href="{{ route(
                                        'admin.absensi.edit',
                                        $absensi
                                    ) }}"
                            class="button button-small">
                            Ubah
                        </a>

                        <form
                            method="POST"
                            action="{{ route(
                                        'admin.absensi.destroy',
                                        $absensi
                                    ) }}"
                            onsubmit="
                                        return confirm(
                                            'Hapus data absensi ini?'
                                        );
                                    ">
                            @csrf
                            @method('DELETE')

                            <button
                                class="button button-danger button-small"
                                type="submit">
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10">
                    Belum ada data absensi
                    pada periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($daftarAbsensi->hasPages())
<div class="no-print">
    {{ $daftarAbsensi->links() }}
</div>
@endif
@endif
@endsection