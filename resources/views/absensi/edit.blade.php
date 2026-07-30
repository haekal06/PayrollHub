@extends('layouts.app')

@section('title', 'Ubah Absensi - PayrollHub')

@section('content')
<div class="page-header">
    <div>
        <h1>Ubah Absensi</h1>

        <p class="muted">
            {{ $absensi->pegawai->nip }} -
            {{ $absensi->pegawai->nama }}
        </p>
    </div>

    <a
        href="{{ route(
            'admin.absensi.index',
            [
                'tampilan' => 'detail',
                'bulan' => $absensi->tanggal_absensi->month,
                'tahun' => $absensi->tanggal_absensi->year,
            ]
        ) }}"
        class="button button-secondary">
        Kembali
    </a>
</div>

<section class="card" style="margin-bottom: 24px;">
    <div class="summary-strip">
        <div class="summary-item">
            NIP

            <strong style="font-size: 20px;">
                {{ $absensi->pegawai->nip }}
            </strong>
        </div>

        <div class="summary-item">
            Pegawai

            <strong style="font-size: 20px;">
                {{ $absensi->pegawai->nama }}
            </strong>
        </div>

        <div class="summary-item">
            Sumber

            <strong style="font-size: 20px;">
                {{ ucfirst($absensi->sumber) }}
            </strong>
        </div>

        <div class="summary-item">
            Dicatat Oleh

            <strong style="font-size: 20px;">
                {{ $absensi->pembuat?->nama ?? 'Sistem' }}
            </strong>
        </div>
    </div>
</section>

<section class="card">
    <form
        method="POST"
        action="{{ route(
            'admin.absensi.update',
            $absensi
        ) }}">
        @csrf
        @method('PUT')

        @include('absensi._form')

        <div class="form-actions">
            <button class="button" type="submit">
                Simpan Perubahan
            </button>

            <a
                href="{{ route(
                    'admin.absensi.index',
                    [
                        'tampilan' => 'detail',
                        'bulan' => $absensi->tanggal_absensi->month,
                        'tahun' => $absensi->tanggal_absensi->year,
                    ]
                ) }}"
                class="button button-secondary">
                Batal
            </a>
        </div>
    </form>
</section>
@endsection