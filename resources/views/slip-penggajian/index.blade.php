@extends('layouts.app')

@section('title', 'Slip Gaji Saya - PayrollHub')

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
        <h1>Slip Gaji Saya</h1>

        <p class="muted">
            {{ $pegawai->nip }} -
            {{ $pegawai->nama }}
        </p>
    </div>
</div>

@if ($daftarPenggajian->isEmpty())
<section class="card">
    <p>
        Belum ada slip gaji final yang tersedia.
    </p>
</section>
@else
<div class="dashboard-grid">
    @foreach ($daftarPenggajian as $penggajian)
    <a
        href="{{ route(
                    'pegawai.slip-gaji.show',
                    $penggajian
                ) }}"
        class="dashboard-card">
        <h3>
            {{ $namaBulan[$penggajian->bulan] }}
            {{ $penggajian->tahun }}
        </h3>

        <p>
            Status:
            <strong>
                {{ ucfirst($penggajian->status) }}
            </strong>
        </p>

        <p>
            Gaji bersih:
            <strong>
                Rp{{ number_format(
                            (float) $penggajian->gaji_bersih,
                            0,
                            ',',
                            '.'
                        ) }}
            </strong>
        </p>
    </a>
    @endforeach
</div>

@if ($daftarPenggajian->hasPages())
<div style="margin-top: 24px;">
    {{ $daftarPenggajian->links() }}
</div>
@endif
@endif
@endsection