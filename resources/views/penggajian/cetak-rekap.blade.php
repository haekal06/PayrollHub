@extends('layouts.print')

@section('title', 'Rekap Penggajian - PayrollHub')

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

    .report-summary {
        grid-template-columns: repeat(5, 1fr);
        margin-bottom: 18px;
    }

    .report-summary .summary-item strong {
        font-size: 13px;
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
        <h2>Rekap Penggajian</h2>

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
        <span class="meta-label">Status</span>

        <span>
            {{ $statusDipilih
                ? ucfirst($statusDipilih)
                : 'Semua Status' }}
        </span>
    </div>

    <div class="meta-item">
        <span class="meta-label">Dicetak Oleh</span>

        <span>
            {{ auth()->user()->nama }}
        </span>
    </div>
</section>

<section class="document-section document-section-avoid">
    <h3 class="section-title">
        Ringkasan Penggajian
    </h3>

    <div class="summary-grid report-summary">
        <div class="summary-item">
            <span>Jumlah Penggajian</span>
            <strong>
                {{ $ringkasan['jumlah_penggajian'] }}
            </strong>
        </div>

        <div class="summary-item">
            <span>Total Gaji Kotor</span>
            <strong>
                Rp{{ number_format(
                    $ringkasan['total_gaji_kotor'],
                    0,
                    ',',
                    '.'
                ) }}
            </strong>
        </div>

        <div class="summary-item">
            <span>Total Upah Lembur</span>
            <strong>
                Rp{{ number_format(
                    $ringkasan['total_upah_lembur'],
                    0,
                    ',',
                    '.'
                ) }}
            </strong>
        </div>

        <div class="summary-item">
            <span>Total Potongan</span>
            <strong>
                Rp{{ number_format(
                    $ringkasan['total_potongan'],
                    0,
                    ',',
                    '.'
                ) }}
            </strong>
        </div>

        <div class="summary-item">
            <span>Total Gaji Bersih</span>
            <strong>
                Rp{{ number_format(
                    $ringkasan['total_gaji_bersih'],
                    0,
                    ',',
                    '.'
                ) }}
            </strong>
        </div>
    </div>
</section>

<section class="document-section">
    <h3 class="section-title">
        Daftar Penggajian Pegawai
    </h3>

    <table class="report-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>NIP</th>
                <th>Nama Pegawai</th>
                <th>Jabatan</th>
                <th>Status</th>
                <th class="text-center">Hadir</th>
                <th class="text-center">Alpa</th>
                <th class="text-center">Lembur</th>
                <th class="text-right">Gaji Kotor</th>
                <th class="text-right">Potongan</th>
                <th class="text-right">Gaji Bersih</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($laporanPenggajian as $penggajian)
            <tr>
                <td class="text-center">
                    {{ $loop->iteration }}
                </td>

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
                            status
                            status-{{ $penggajian->status }}
                        ">
                        {{ ucfirst($penggajian->status) }}
                    </span>
                </td>

                <td class="text-center">
                    {{ $penggajian->jumlah_hadir }}
                </td>

                <td class="text-center">
                    {{ $penggajian->jumlah_alpa }}
                </td>

                <td class="text-center">
                    {{ number_format(
                            (float) $penggajian->jam_lembur,
                            1,
                            ',',
                            '.'
                        ) }}
                    jam
                </td>

                <td class="text-right">
                    Rp{{ number_format(
                            (float) $penggajian->gaji_kotor,
                            0,
                            ',',
                            '.'
                        ) }}
                </td>

                <td class="text-right">
                    Rp{{ number_format(
                            (float) $penggajian
                                ->total_potongan,
                            0,
                            ',',
                            '.'
                        ) }}
                </td>

                <td class="text-right">
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
            </tr>
            @empty
            <tr>
                <td
                    colspan="11"
                    class="text-center">
                    Belum ada data penggajian
                    sesuai filter yang dipilih.
                </td>
            </tr>
            @endforelse
        </tbody>

        @if ($laporanPenggajian->isNotEmpty())
        <tfoot>
            <tr class="table-total">
                <td colspan="8">
                    Total
                </td>

                <td class="text-right">
                    Rp{{ number_format(
                            $ringkasan['total_gaji_kotor'],
                            0,
                            ',',
                            '.'
                        ) }}
                </td>

                <td class="text-right">
                    Rp{{ number_format(
                            $ringkasan['total_potongan'],
                            0,
                            ',',
                            '.'
                        ) }}
                </td>

                <td class="text-right">
                    Rp{{ number_format(
                            $ringkasan['total_gaji_bersih'],
                            0,
                            ',',
                            '.'
                        ) }}
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
</section>

<section class="signature-area">
    <div>
        <p class="document-note">
            Laporan ini dibuat otomatis oleh PayrollHub
            berdasarkan filter penggajian yang dipilih.
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
    PayrollHub — Rekap penggajian perusahaan.
</footer>
@endsection