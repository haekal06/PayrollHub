@extends('layouts.app')

@section('title', 'Data Penggajian - PayrollHub')

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
@endphp

<div class="page-header">
    <div>
        <h1>Data Penggajian</h1>

        <p class="muted">
            Proses, finalisasi, revisi,
            dan catat pembayaran gaji.
        </p>
    </div>

    <a
        href="{{ route('admin.penggajian.create') }}"
        class="button">
        Proses Penggajian
    </a>
</div>

<section
    class="card"
    style="margin-bottom: 24px;">
    <form
        method="GET"
        action="{{ route('admin.penggajian.index') }}">

        <div class="filter-grid">
            <div class="form-group">
                <label for="bulan">
                    Bulan
                </label>

                <select
                    id="bulan"
                    name="bulan">

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
                <label for="tahun">
                    Tahun
                </label>

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
                <label for="pegawai_id">
                    Pegawai
                </label>

                <select
                    id="pegawai_id"
                    name="pegawai_id">

                    <option value="">
                        Semua Pegawai
                    </option>

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
                <label for="status">
                    Status
                </label>

                <select
                    id="status"
                    name="status">

                    <option value="">
                        Semua Status
                    </option>

                    <option
                        value="draf"
                        @selected(
                        $statusDipilih==='draf'
                        )>
                        Draf
                    </option>

                    <option
                        value="revisi"
                        @selected(
                        $statusDipilih==='revisi'
                        )>
                        Revisi
                    </option>

                    <option
                        value="final"
                        @selected(
                        $statusDipilih==='final'
                        )>
                        Final
                    </option>

                    <option
                        value="dibayar"
                        @selected(
                        $statusDipilih==='dibayar'
                        )>
                        Dibayar
                    </option>
                </select>
            </div>

            <div class="form-group">
                <button
                    class="button"
                    type="submit">
                    Terapkan Filter
                </button>
            </div>
        </div>
    </form>
</section>

<div class="summary-strip">
    <div class="summary-item">
        Penggajian

        <strong>
            {{ $ringkasan['jumlah_penggajian'] }}
        </strong>
    </div>

    <div class="summary-item">
        Gaji Kotor

        <strong style="font-size: 19px;">
            Rp{{ number_format(
                $ringkasan['total_gaji_kotor'],
                0,
                ',',
                '.'
            ) }}
        </strong>
    </div>

    <div class="summary-item">
        Upah Lembur

        <strong style="font-size: 19px;">
            Rp{{ number_format(
                $ringkasan['total_upah_lembur'],
                0,
                ',',
                '.'
            ) }}
        </strong>
    </div>

    <div class="summary-item">
        Total Potongan

        <strong style="font-size: 19px;">
            Rp{{ number_format(
                $ringkasan['total_potongan'],
                0,
                ',',
                '.'
            ) }}
        </strong>
    </div>

    <div class="summary-item">
        Gaji Bersih

        <strong style="font-size: 19px;">
            Rp{{ number_format(
                $ringkasan['total_gaji_bersih'],
                0,
                ',',
                '.'
            ) }}
        </strong>
    </div>
</div>

<div class="summary-strip">
    <div class="summary-item">
        Draf

        <strong>
            {{ $ringkasan['jumlah_draf'] }}
        </strong>
    </div>

    <div class="summary-item">
        Revisi

        <strong>
            {{ $ringkasan['jumlah_revisi'] }}
        </strong>
    </div>

    <div class="summary-item">
        Final

        <strong>
            {{ $ringkasan['jumlah_final'] }}
        </strong>
    </div>

    <div class="summary-item">
        Dibayar

        <strong>
            {{ $ringkasan['jumlah_dibayar'] }}
        </strong>
    </div>
</div>

<div class="page-header">
    <h2>
        Periode
        {{ $namaBulan[$bulan] }}
        {{ $tahun }}
    </h2>

    <a
        href="{{ route(
            'admin.penggajian.cetak-rekap',
            [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'pegawai_id' => $pegawaiDipilih,
                'status' => $statusDipilih,
            ]
        ) }}"
        class="button button-secondary"
        target="_blank"
        rel="noopener">
        Cetak Rekap Penggajian
    </a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>NIP</th>
                <th>Pegawai</th>
                <th>Jabatan</th>
                <th>Status</th>
                <th>Hadir</th>
                <th>Alpa</th>
                <th>Lembur</th>
                <th>Gaji Kotor</th>
                <th>Potongan</th>
                <th>Gaji Bersih</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($daftarPenggajian as $penggajian)
            <tr>
                <td>
                    {{ $penggajian->pegawai->nip }}
                </td>

                <td>
                    {{ $penggajian->pegawai->nama }}
                </td>

                <td>
                    {{ $penggajian->pegawai
                            ->jabatan->nama }}
                </td>

                <td>
                    <span class="
                            status-badge
                            status-{{ $penggajian->status }}
                        ">
                        {{ ucfirst(
                                $penggajian->status
                            ) }}
                    </span>
                </td>

                <td>
                    {{ $penggajian->jumlah_hadir }}
                </td>

                <td>
                    {{ $penggajian->jumlah_alpa }}
                </td>

                <td>
                    {{ number_format(
                            (float) $penggajian
                                ->jam_lembur,
                            1,
                            ',',
                            '.'
                        ) }}
                    jam
                </td>

                <td>
                    Rp{{ number_format(
                            (float) $penggajian
                                ->gaji_kotor,
                            0,
                            ',',
                            '.'
                        ) }}
                </td>

                <td>
                    Rp{{ number_format(
                            (float) $penggajian
                                ->total_potongan,
                            0,
                            ',',
                            '.'
                        ) }}
                </td>

                <td>
                    <strong>
                        Rp{{ number_format(
                                (float) $penggajian
                                    ->gaji_bersih,
                                0,
                                ',',
                                '.'
                            ) }}
                    </strong>
                </td>

                <td>
                    <a
                        href="{{ route(
                                'admin.penggajian.show',
                                $penggajian
                            ) }}"
                        class="button button-small">
                        Lihat
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11">
                    Belum ada data penggajian
                    pada periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($daftarPenggajian->hasPages())
<div>
    {{ $daftarPenggajian->links() }}
</div>
@endif
@endsection