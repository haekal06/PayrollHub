@extends('layouts.app')

@section('title', 'Proses Penggajian - PayrollHub')

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
        <h1>Proses Penggajian</h1>

        <p class="muted">
            Hari kerja, absensi, dan lembur
            dihitung otomatis oleh PayrollHub.
        </p>
    </div>

    <a
        href="{{ route('admin.penggajian.index') }}"
        class="button button-secondary">
        Kembali
    </a>
</div>

<h2>1. Pilih Pegawai dan Periode</h2>

<section class="card" style="margin-bottom: 24px;">
    <form
        method="GET"
        action="{{ route('admin.penggajian.create') }}">
        <div class="filter-grid">
            <div class="form-group">
                <label for="pegawai_id">Pegawai</label>

                <select
                    id="pegawai_id"
                    name="pegawai_id"
                    required>
                    <option value="">Pilih Pegawai</option>

                    @foreach ($daftarPegawai as $item)
                    <option
                        value="{{ $item->id }}"
                        @selected(
                        $pegawaiDipilih===$item->id
                        )>
                        {{ $item->nip }} -
                        {{ $item->nama }}
                        ({{ $item->jabatan->nama }})
                    </option>
                    @endforeach
                </select>
            </div>

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
                    Tampilkan Rekap Otomatis
                </button>
            </div>
        </div>
    </form>
</section>

@if ($kesalahanPratinjau !== null)
<div class="alert alert-error">
    {{ $kesalahanPratinjau }}
</div>
@endif

@if (
$pegawai !== null
&& $ringkasanAbsensi !== null
)
<h2>2. Periksa Rekap Otomatis</h2>

<section class="card" style="margin-bottom: 24px;">
    <h3>
        {{ $pegawai->nip }} -
        {{ $pegawai->nama }}
    </h3>

    <p class="muted">
        {{ $pegawai->jabatan->nama }} |
        {{ $namaBulan[$bulan] }} {{ $tahun }}
    </p>

    <div class="summary-strip">
        <div class="summary-item">
            Hari Kerja
            <strong>
                {{ $ringkasanAbsensi['jumlah_hari_kerja'] }}
            </strong>
        </div>

        <div class="summary-item">
            Tercatat
            <strong>
                {{ $ringkasanAbsensi['jumlah_tercatat'] }}
            </strong>
        </div>

        <div class="summary-item">
            Hadir
            <strong>
                {{ $ringkasanAbsensi['jumlah_hadir'] }}
            </strong>
        </div>

        <div class="summary-item">
            Sakit
            <strong>
                {{ $ringkasanAbsensi['jumlah_sakit'] }}
            </strong>
        </div>

        <div class="summary-item">
            Izin
            <strong>
                {{ $ringkasanAbsensi['jumlah_izin'] }}
            </strong>
        </div>

        <div class="summary-item">
            Cuti
            <strong>
                {{ $ringkasanAbsensi['jumlah_cuti'] }}
            </strong>
        </div>

        <div class="summary-item">
            Alpa
            <strong>
                {{ $ringkasanAbsensi['jumlah_alpa'] }}
            </strong>
        </div>

        <div class="summary-item">
            Jam Lembur

            <strong>
                {{ number_format(
                        $ringkasanAbsensi['jam_lembur'],
                        1,
                        ',',
                        '.'
                    ) }}
            </strong>
        </div>
    </div>
</section>

<h2>3. Bonus dan Potongan Tambahan</h2>

<section class="card">
    <form
        method="POST"
        action="{{ route(
                'admin.penggajian.store'
            ) }}">
        @csrf

        <input
            type="hidden"
            name="pegawai_id"
            value="{{ $pegawai->id }}">

        <input
            type="hidden"
            name="bulan"
            value="{{ $bulan }}">

        <input
            type="hidden"
            name="tahun"
            value="{{ $tahun }}">

        <div class="form-group">
            <label for="bonus">Bonus</label>

            <input
                id="bonus"
                name="bonus"
                type="number"
                min="0"
                step="0.01"
                value="{{ old('bonus', 0) }}"
                required>

            @error('bonus')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="catatan_bonus">
                Keterangan Bonus
            </label>

            <textarea
                id="catatan_bonus"
                name="catatan_bonus"
                maxlength="500"
                rows="3"
                placeholder="Wajib jika terdapat bonus">{{ old(
                        'catatan_bonus'
                    ) }}</textarea>

            @error('catatan_bonus')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="potongan_lain">
                Potongan Lain
            </label>

            <input
                id="potongan_lain"
                name="potongan_lain"
                type="number"
                min="0"
                step="0.01"
                value="{{ old('potongan_lain', 0) }}"
                required>

            @error('potongan_lain')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="catatan_potongan">
                Keterangan Potongan
            </label>

            <textarea
                id="catatan_potongan"
                name="catatan_potongan"
                maxlength="500"
                rows="3"
                placeholder="Wajib jika terdapat potongan lain">{{ old(
                        'catatan_potongan'
                    ) }}</textarea>

            @error('catatan_potongan')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="alert alert-info">
            Gaji pokok, tunjangan, tarif lembur,
            potongan alpa, dan gaji bersih akan
            dihitung otomatis saat diproses.
        </div>

        <div class="form-actions">
            <button class="button" type="submit">
                Proses sebagai Draf
            </button>

            <a
                href="{{ route(
                        'admin.penggajian.index'
                    ) }}"
                class="button button-secondary">
                Batal
            </a>
        </div>
    </form>
</section>
@endif
@endsection