@extends('layouts.app')

@section('title', 'Kalender Kerja - PayrollHub')

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

$labelJenisHari = [
'hari_kerja' => 'Hari Kerja',
'akhir_pekan' => 'Akhir Pekan',
'libur_nasional' => 'Libur Nasional',
'libur_perusahaan' => 'Libur Perusahaan',
];
@endphp

<div class="page-header">
    <div>
        <h1>Kalender Kerja</h1>

        <p class="muted">
            Tentukan hari kerja, akhir pekan,
            dan hari libur perusahaan.
        </p>
    </div>
</div>

<section class="card no-print" style="margin-bottom: 24px;">
    <form
        method="GET"
        action="{{ route('admin.kalender-kerja.index') }}">
        <div class="filter-grid">
            <div class="form-group">
                <label for="bulan">Bulan</label>

                <select
                    id="bulan"
                    name="bulan"
                    required>
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
                    Tampilkan Kalender
                </button>
            </div>
        </div>
    </form>
</section>

<div class="summary-strip">
    <div class="summary-item">
        Periode

        <strong style="font-size: 20px;">
            {{ $namaBulan[$bulan] }}
            {{ $tahun }}
        </strong>
    </div>

    <div class="summary-item">
        Hari Kerja

        <strong>{{ $jumlahHariKerja }}</strong>
    </div>

    <div class="summary-item">
        Hari Libur

        <strong>{{ $jumlahHariLibur }}</strong>
    </div>

    <div class="summary-item">
        Data Tanggal

        <strong>
            {{ $daftarKalender->count() }}
            /
            {{ $jumlahHariSeharusnya }}
        </strong>
    </div>
</div>

@if (! $kalenderLengkap)
<div class="alert alert-warning">
    Kalender periode ini belum lengkap.
    Sistem perlu membuat data untuk seluruh
    tanggal pada bulan tersebut.
</div>

<section class="card no-print">
    <h2>Buat Kalender Otomatis</h2>

    <p>
        Senin sampai Jumat akan dibuat sebagai
        hari kerja. Sabtu dan Minggu akan dibuat
        sebagai akhir pekan.
    </p>

    <form
        method="POST"
        action="{{ route(
                'admin.kalender-kerja.buat'
            ) }}">
        @csrf

        <input
            type="hidden"
            name="bulan"
            value="{{ $bulan }}">

        <input
            type="hidden"
            name="tahun"
            value="{{ $tahun }}">

        <button class="button" type="submit">
            Buat Kalender
            {{ $namaBulan[$bulan] }}
            {{ $tahun }}
        </button>
    </form>
</section>
@else
<form
    method="POST"
    action="{{ route(
            'admin.kalender-kerja.perbarui-bulan'
        ) }}">
    @csrf
    @method('PUT')

    <input
        type="hidden"
        name="bulan"
        value="{{ $bulan }}">

    <input
        type="hidden"
        name="tahun"
        value="{{ $tahun }}">

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Hari</th>
                    <th>Jenis Hari</th>
                    <th>Keterangan</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($daftarKalender as $index => $kalender)
                <tr>
                    <td>
                        {{ $kalender->tanggal->format('d-m-Y') }}

                        <input
                            type="hidden"
                            name="kalender[{{ $index }}][id]"
                            value="{{ $kalender->id }}">
                    </td>

                    <td>
                        {{ $kalender->tanggal
                                    ->locale('id')
                                    ->translatedFormat('l') }}
                    </td>

                    <td style="min-width: 210px;">
                        <select
                            name="kalender[{{ $index }}][jenis_hari]"
                            required>
                            @foreach (
                            $labelJenisHari
                            as $nilai => $label
                            )
                            <option
                                value="{{ $nilai }}"
                                @selected(
                                old( "kalender.$index.jenis_hari" ,
                                $kalender->jenis_hari
                                ) === $nilai
                                )>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>

                        @error("kalender.$index.jenis_hari")
                        <div class="error">
                            {{ $message }}
                        </div>
                        @enderror
                    </td>

                    <td style="min-width: 280px;">
                        <input
                            name="kalender[{{ $index }}][keterangan]"
                            type="text"
                            maxlength="255"
                            value="{{ old(
                                        "kalender.$index.keterangan",
                                        $kalender->keterangan
                                    ) }}"
                            placeholder="Opsional">

                        @error("kalender.$index.keterangan")
                        <div class="error">
                            {{ $message }}
                        </div>
                        @enderror
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="form-actions no-print">
        <button class="button" type="submit">
            Simpan Kalender Kerja
        </button>

        <a
            href="{{ route(
                'admin.kalender-kerja.cetak',
                [
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ]
            ) }}"
            class="button button-secondary"
            target="_blank"
            rel="noopener">
            Cetak Kalender
        </a>
    </div>
</form>
@endif
@endsection