@extends('layouts.app')

@section('title', 'Absensi Massal - PayrollHub')

@section('content')
@php
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
        <h1>Absensi Massal</h1>

        <p class="muted">
            Catat seluruh pegawai dalam satu halaman.
        </p>
    </div>

    <a
        href="{{ route('admin.absensi.index') }}"
        class="button button-secondary">
        Kembali
    </a>
</div>

<section class="card no-print" style="margin-bottom: 24px;">
    <form
        method="GET"
        action="{{ route(
            'admin.absensi.massal.create'
        ) }}">
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
                    Tampilkan Pegawai
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
        Dapat Diubah
        <strong>{{ $jumlahDapatDiubah }}</strong>
    </div>

    <div class="summary-item">
        Terkunci
        <strong>{{ $jumlahTerkunci }}</strong>
    </div>
</div>

@if ($kalender === null)
<div class="alert alert-error">
    Tanggal ini belum tersedia pada Kalender Kerja.
    Buat kalender terlebih dahulu.
</div>
@elseif (! $dapatDicatat)
<div class="alert alert-warning">
    Tanggal ini merupakan hari libur.
    Absensi massal tidak dapat disimpan.
</div>
@endif

@if ($daftarPegawai->isEmpty())
<section class="card">
    Tidak ada pegawai aktif pada tanggal ini.
</section>
@elseif ($dapatDicatat)
<form
    method="POST"
    action="{{ route(
            'admin.absensi.massal.store'
        ) }}">
    @csrf

    <input
        type="hidden"
        name="tanggal_absensi"
        value="{{ $tanggal->format('Y-m-d') }}">

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>NIP</th>
                    <th>Pegawai</th>
                    <th>Jabatan</th>
                    <th>Status</th>
                    <th>Jam Lembur</th>
                    <th>Keterangan Lembur</th>
                    <th>Catatan</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($daftarPegawai as $index => $pegawai)
                @php
                $absensi =
                $pegawai->absensis->first();

                $terkunci =
                $pegawai->penggajians->isNotEmpty();

                $statusAwal = old(
                "data.$index.status",
                $absensi->status ?? 'hadir'
                );

                $jamAwal = old(
                "data.$index.jam_lembur",
                $absensi->jam_lembur ?? 0
                );
                @endphp

                <tr
                    @if (! $terkunci)
                    data-baris-absensi
                    @endif>
                    <td>{{ $pegawai->nip }}</td>
                    <td>{{ $pegawai->nama }}</td>
                    <td>{{ $pegawai->jabatan->nama }}</td>

                    @if ($terkunci)
                    <td colspan="4">
                        <span class="
                                        status-badge
                                        status-terkunci
                                    ">
                            Periode penggajian terkunci
                        </span>
                    </td>
                    @else
                    <td style="min-width: 150px;">
                        <input
                            type="hidden"
                            name="data[{{ $index }}][pegawai_id]"
                            value="{{ $pegawai->id }}">

                        <select
                            name="data[{{ $index }}][status]"
                            data-status
                            required>
                            @foreach (
                            $labelStatus
                            as $nilai => $label
                            )
                            <option
                                value="{{ $nilai }}"
                                @selected(
                                $statusAwal===$nilai
                                )>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>

                        @error("data.$index.status")
                        <div class="error">
                            {{ $message }}
                        </div>
                        @enderror
                    </td>

                    <td style="min-width: 130px;">
                        <input
                            name="data[{{ $index }}][jam_lembur]"
                            type="number"
                            min="0"
                            max="12"
                            step="0.5"
                            value="{{ $jamAwal }}"
                            data-jam-lembur
                            required>

                        @error("data.$index.jam_lembur")
                        <div class="error">
                            {{ $message }}
                        </div>
                        @enderror
                    </td>

                    <td style="min-width: 240px;">
                        <input
                            name="data[{{ $index }}][catatan_lembur]"
                            type="text"
                            maxlength="1000"
                            value="{{ old(
                                            "data.$index.catatan_lembur",
                                            $absensi->catatan_lembur ?? ''
                                        ) }}"
                            data-catatan-lembur
                            placeholder="Wajib jika lembur">

                        @error("data.$index.catatan_lembur")
                        <div class="error">
                            {{ $message }}
                        </div>
                        @enderror
                    </td>

                    <td style="min-width: 220px;">
                        <input
                            name="data[{{ $index }}][catatan]"
                            type="text"
                            maxlength="1000"
                            value="{{ old(
                                            "data.$index.catatan",
                                            $absensi->catatan ?? ''
                                        ) }}"
                            placeholder="Opsional">

                        @error("data.$index.catatan")
                        <div class="error">
                            {{ $message }}
                        </div>
                        @enderror
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($jumlahDapatDiubah > 0)
    <div class="form-actions">
        <button class="button" type="submit">
            Simpan Absensi Massal
        </button>

        <a
            href="{{ route('admin.absensi.index') }}"
            class="button button-secondary">
            Batal
        </a>
    </div>
    @endif
</form>
@endif
@endsection

@push('scripts')
<script>
    document
        .querySelectorAll('[data-baris-absensi]')
        .forEach(function(baris) {
            const status =
                baris.querySelector('[data-status]');

            const jamLembur =
                baris.querySelector('[data-jam-lembur]');

            const catatanLembur =
                baris.querySelector(
                    '[data-catatan-lembur]'
                );

            function perbaruiLembur() {
                const hadir =
                    status.value === 'hadir';

                jamLembur.readOnly = !hadir;
                catatanLembur.readOnly = !hadir;

                if (!hadir) {
                    jamLembur.value = '0';
                    catatanLembur.value = '';
                }
            }

            status.addEventListener(
                'change',
                perbaruiLembur
            );

            perbaruiLembur();
        });
</script>
@endpush