@extends('layouts.print')

@section('title', 'Kalender Kerja - PayrollHub')

@push('styles')
<style>
    @page {
        size: A4 landscape;
        margin: 10mm;
    }

    .print-document {
        width: 297mm;
        min-height: 210mm;
        padding: 10mm;
    }

    .calendar-summary {
        grid-template-columns: repeat(4, 1fr);
        margin-bottom: 18px;
    }

    .calendar-table {
        font-size: 10px;
    }

    .calendar-table th,
    .calendar-table td {
        padding: 5px 7px;
    }

    .day-working {
        color: #166534;
        background: #dcfce7;
    }

    .day-weekend {
        color: #4b5563;
        background: #f3f4f6;
    }

    .day-national {
        color: #991b1b;
        background: #fee2e2;
    }

    .day-company {
        color: #854d0e;
        background: #fef9c3;
    }

    .calendar-warning {
        padding: 10px 12px;
        margin-bottom: 18px;
        border: 1px solid #fca5a5;
        color: #991b1b;
        background: #fee2e2;
    }

    @media print {
        .print-document {
            width: auto;
            min-height: auto;
            padding: 0;
        }
    }
</style>
@endpush

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

$kelasJenisHari = [
'hari_kerja' => 'day-working',
'akhir_pekan' => 'day-weekend',
'libur_nasional' => 'day-national',
'libur_perusahaan' => 'day-company',
];
@endphp

<header class="document-header">
    <div class="document-brand">
        <h1>PayrollHub</h1>
        <p>Sistem Pengelolaan Payroll</p>
    </div>

    <div class="document-title">
        <h2>Kalender Kerja</h2>

        <p>
            Periode {{ $namaBulan[$bulan] }}
            {{ $tahun }}
        </p>
    </div>
</header>

<section class="document-meta">
    <div class="meta-item">
        <span class="meta-label">Periode</span>

        <strong>
            {{ $namaBulan[$bulan] }} {{ $tahun }}
        </strong>
    </div>

    <div class="meta-item">
        <span class="meta-label">Dicetak Oleh</span>
        <span>{{ auth()->user()->nama }}</span>
    </div>

    <div class="meta-item">
        <span class="meta-label">Kelengkapan</span>

        <span>
            {{ $daftarKalender->count() }}
            dari
            {{ $jumlahHariSeharusnya }}
            tanggal
        </span>
    </div>

    <div class="meta-item">
        <span class="meta-label">Status Data</span>

        <span>
            {{ $kalenderLengkap
                ? 'Lengkap'
                : 'Belum Lengkap' }}
        </span>
    </div>
</section>

@if (! $kalenderLengkap)
<div class="calendar-warning">
    Kalender kerja periode ini belum lengkap.
    Beberapa tanggal mungkin belum tersedia.
</div>
@endif

<section class="document-section document-section-avoid">
    <h3 class="section-title">Ringkasan Kalender</h3>

    <div class="summary-grid calendar-summary">
        <div class="summary-item">
            <span>Jumlah Tanggal</span>

            <strong>
                {{ $daftarKalender->count() }}
            </strong>
        </div>

        <div class="summary-item">
            <span>Hari Kerja</span>
            <strong>{{ $jumlahHariKerja }}</strong>
        </div>

        <div class="summary-item">
            <span>Hari Libur</span>
            <strong>{{ $jumlahHariLibur }}</strong>
        </div>

        <div class="summary-item">
            <span>Total Hari Bulan Ini</span>
            <strong>{{ $jumlahHariSeharusnya }}</strong>
        </div>
    </div>
</section>

<section class="document-section">
    <h3 class="section-title">
        Daftar Kalender Kerja
    </h3>

    <table class="calendar-table">
        <thead>
            <tr>
                <th style="width: 7%;">No.</th>
                <th style="width: 16%;">Tanggal</th>
                <th style="width: 16%;">Hari</th>
                <th style="width: 21%;">Jenis Hari</th>
                <th>Keterangan</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($daftarKalender as $kalender)
            <tr>
                <td class="text-center">
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{ $kalender->tanggal
                            ->format('d-m-Y') }}
                </td>

                <td>
                    {{ $kalender->tanggal
                            ->locale('id')
                            ->translatedFormat('l') }}
                </td>

                <td>
                    <span class="
                            status
                            {{ $kelasJenisHari[
                                $kalender->jenis_hari
                            ] ?? '' }}
                        ">
                        {{ $labelJenisHari[
                                $kalender->jenis_hari
                            ] ?? ucfirst(
                                $kalender->jenis_hari
                            ) }}
                    </span>
                </td>

                <td>
                    {{ $kalender->keterangan ?? '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td
                    colspan="5"
                    class="text-center">
                    Kalender kerja belum dibuat.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</section>

<section class="signature-area">
    <div>
        <p class="document-note">
            Kalender kerja menjadi dasar perhitungan
            jumlah hari kerja pada proses penggajian.
        </p>

        <p class="document-note">
            Dicetak pada:
            {{ now()->locale('id')->translatedFormat(
                'd F Y, H:i'
            ) }}
        </p>
    </div>

    <div class="signature-box">
        <div>Admin HRD</div>
        <div class="signature-space"></div>

        <div class="signature-line">
            {{ auth()->user()->nama }}
        </div>
    </div>
</section>

<footer class="document-footer">
    PayrollHub — Kalender kerja perusahaan.
</footer>
@endsection