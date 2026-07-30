@extends('layouts.print')

@section('title', 'Rekap Absensi - PayrollHub')

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

    .attendance-summary {
        grid-template-columns: repeat(4, 1fr);
        margin-bottom: 18px;
    }

    .report-table {
        font-size: 10px;
    }

    .report-table th,
    .report-table td {
        padding: 6px;
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
@endphp

<header class="document-header">
    <div class="document-brand">
        <h1>PayrollHub</h1>
        <p>Sistem Pengelolaan Payroll</p>
    </div>

    <div class="document-title">
        <h2>Rekap Absensi Bulanan</h2>

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
</section>

<section class="document-section document-section-avoid">
    <h3 class="section-title">
        Ringkasan
    </h3>

    <div class="summary-grid attendance-summary">
        <div class="summary-item">
            <span>Jumlah Pegawai</span>
            <strong>{{ $total['pegawai'] }}</strong>
        </div>

        <div class="summary-item">
            <span>Total Tercatat</span>
            <strong>{{ $total['tercatat'] }}</strong>
        </div>

        <div class="summary-item">
            <span>Total Alpa</span>
            <strong>{{ $total['alpa'] }}</strong>
        </div>

        <div class="summary-item">
            <span>Total Jam Lembur</span>

            <strong>
                {{ number_format(
                    $total['jam_lembur'],
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
        Rekap Kehadiran Pegawai
    </h3>

    <table class="report-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>NIP</th>
                <th>Nama Pegawai</th>
                <th>Jabatan</th>
                <th class="text-center">Tercatat</th>
                <th class="text-center">Hadir</th>
                <th class="text-center">Sakit</th>
                <th class="text-center">Izin</th>
                <th class="text-center">Cuti</th>
                <th class="text-center">Alpa</th>
                <th class="text-center">Lembur</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($ringkasanBulanan as $pegawai)
            <tr>
                <td class="text-center">
                    {{ $loop->iteration }}
                </td>

                <td>{{ $pegawai->nip }}</td>
                <td>{{ $pegawai->nama }}</td>
                <td>{{ $pegawai->jabatan->nama }}</td>

                <td class="text-center">
                    {{ $pegawai->jumlah_tercatat }}
                </td>

                <td class="text-center">
                    {{ $pegawai->jumlah_hadir }}
                </td>

                <td class="text-center">
                    {{ $pegawai->jumlah_sakit }}
                </td>

                <td class="text-center">
                    {{ $pegawai->jumlah_izin }}
                </td>

                <td class="text-center">
                    {{ $pegawai->jumlah_cuti }}
                </td>

                <td class="text-center">
                    {{ $pegawai->jumlah_alpa }}
                </td>

                <td class="text-center">
                    {{ number_format(
                            (float) (
                                $pegawai->total_jam_lembur
                                ?? 0
                            ),
                            1,
                            ',',
                            '.'
                        ) }}
                    jam
                </td>
            </tr>
            @empty
            <tr>
                <td
                    colspan="11"
                    class="text-center">
                    Tidak ada data pegawai.
                </td>
            </tr>
            @endforelse
        </tbody>

        @if ($ringkasanBulanan->isNotEmpty())
        <tfoot>
            <tr class="table-total">
                <td colspan="4">
                    Total
                </td>

                <td class="text-center">
                    {{ $total['tercatat'] }}
                </td>

                <td class="text-center">
                    {{ $total['hadir'] }}
                </td>

                <td class="text-center">
                    {{ $total['sakit'] }}
                </td>

                <td class="text-center">
                    {{ $total['izin'] }}
                </td>

                <td class="text-center">
                    {{ $total['cuti'] }}
                </td>

                <td class="text-center">
                    {{ $total['alpa'] }}
                </td>

                <td class="text-center">
                    {{ number_format(
                            $total['jam_lembur'],
                            1,
                            ',',
                            '.'
                        ) }}
                    jam
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
</section>

<section class="signature-area">
    <div>
        <p class="document-note">
            Rekap dibuat berdasarkan data absensi
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
    PayrollHub — Rekap absensi bulanan.
</footer>
@endsection