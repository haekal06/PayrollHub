@extends('layouts.print')

@section('title', 'Detail Absensi - PayrollHub')

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

    .detail-summary {
        grid-template-columns: repeat(7, 1fr);
        margin-bottom: 18px;
    }

    .report-table {
        font-size: 9px;
    }

    .report-table th,
    .report-table td {
        padding: 5px;
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

$labelStatus = [
'hadir' => 'Hadir',
'sakit' => 'Sakit',
'izin' => 'Izin',
'cuti' => 'Cuti',
'alpa' => 'Alpa',
];
@endphp

<header class="document-header">
    <div class="document-brand">
        <h1>PayrollHub</h1>
        <p>Sistem Pengelolaan Payroll</p>
    </div>

    <div class="document-title">
        <h2>Detail Absensi Bulanan</h2>

        <p>
            {{ $namaBulan[$bulan] }} {{ $tahun }}
        </p>
    </div>
</header>

<section class="document-meta">
    <div class="meta-item">
        <span class="meta-label">Pegawai</span>

        <span>
            @if ($pegawaiFilter)
            {{ $pegawaiFilter->nip }} -
            {{ $pegawaiFilter->nama }}
            @else
            Semua Pegawai
            @endif
        </span>
    </div>

    <div class="meta-item">
        <span class="meta-label">Dicetak Oleh</span>
        <span>{{ auth()->user()->nama }}</span>
    </div>
</section>

<section class="document-section document-section-avoid">
    <h3 class="section-title">Ringkasan</h3>

    <div class="summary-grid detail-summary">
        <div class="summary-item">
            <span>Tercatat</span>
            <strong>{{ $ringkasan['jumlah_data'] }}</strong>
        </div>

        <div class="summary-item">
            <span>Hadir</span>
            <strong>{{ $ringkasan['hadir'] }}</strong>
        </div>

        <div class="summary-item">
            <span>Sakit</span>
            <strong>{{ $ringkasan['sakit'] }}</strong>
        </div>

        <div class="summary-item">
            <span>Izin</span>
            <strong>{{ $ringkasan['izin'] }}</strong>
        </div>

        <div class="summary-item">
            <span>Cuti</span>
            <strong>{{ $ringkasan['cuti'] }}</strong>
        </div>

        <div class="summary-item">
            <span>Alpa</span>
            <strong>{{ $ringkasan['alpa'] }}</strong>
        </div>

        <div class="summary-item">
            <span>Lembur</span>

            <strong>
                {{ number_format(
                    $ringkasan['jam_lembur'],
                    1,
                    ',',
                    '.'
                ) }}
                jam
            </strong>
        </div>
    </div>
</section>

<section class="document-section">
    <h3 class="section-title">
        Data Absensi Harian
    </h3>

    <table class="report-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>NIP</th>
                <th>Nama Pegawai</th>
                <th>Status</th>
                <th>Lembur</th>
                <th>Keterangan Lembur</th>
                <th>Catatan</th>
                <th>Sumber</th>
                <th>Dicatat Oleh</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($daftarAbsensi as $absensi)
            <tr>
                <td class="text-center">
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{ $absensi->tanggal_absensi
                            ->format('d-m-Y') }}
                </td>

                <td>
                    {{ $absensi->tanggal_absensi
                            ->locale('id')
                            ->translatedFormat('l') }}
                </td>

                <td>{{ $absensi->pegawai->nip }}</td>
                <td>{{ $absensi->pegawai->nama }}</td>

                <td>
                    <span class="
                            status
                            status-{{ $absensi->status }}
                        ">
                        {{ $labelStatus[$absensi->status] }}
                    </span>
                </td>

                <td class="text-center">
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

                <td>
                    {{ $absensi->catatan ?? '-' }}
                </td>

                <td>
                    {{ ucfirst($absensi->sumber) }}
                </td>

                <td>
                    {{ $absensi->pembuat?->nama
                            ?? 'Sistem' }}
                </td>
            </tr>
            @empty
            <tr>
                <td
                    colspan="11"
                    class="text-center">
                    Belum ada data absensi.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</section>

<section class="signature-area">
    <div>
        <p class="document-note">
            Detail dibuat berdasarkan data absensi
            yang tersimpan pada PayrollHub.
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
    PayrollHub — Detail absensi pegawai.
</footer>
@endsection