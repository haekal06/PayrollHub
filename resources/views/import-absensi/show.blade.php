@extends('layouts.app')

@section('title', 'Pratinjau Import Absensi - PayrollHub')

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
        <h1>Pratinjau Import Absensi</h1>

        <p class="muted">
            {{ $importAbsensi->nama_file_asli }}
        </p>
    </div>

    <a
        href="{{ route(
            'admin.import-absensi.index'
        ) }}"
        class="button button-secondary">
        Kembali
    </a>
</div>

@error('import')
<div class="alert alert-error">
    {{ $message }}
</div>
@enderror

<section class="card" style="margin-bottom: 24px;">
    <div class="summary-strip">
        <div class="summary-item">
            Status

            <strong style="font-size: 20px;">
                @switch($importAbsensi->status)
                @case('pratinjau')
                Pratinjau
                @break

                @case('selesai')
                Selesai
                @break

                @case('dibatalkan')
                Dibatalkan
                @break
                @endswitch
            </strong>
        </div>

        <div class="summary-item">
            Total Baris

            <strong>
                {{ $importAbsensi->jumlah_baris }}
            </strong>
        </div>

        <div class="summary-item">
            Baris Valid

            <strong style="color: #166534;">
                {{ $importAbsensi->jumlah_valid }}
            </strong>
        </div>

        <div class="summary-item">
            Tidak Valid

            <strong style="color: #991b1b;">
                {{ $importAbsensi->jumlah_tidak_valid }}
            </strong>
        </div>

        <div class="summary-item">
            Ditambahkan

            <strong>
                {{ $importAbsensi->jumlah_ditambahkan }}
            </strong>
        </div>

        <div class="summary-item">
            Diperbarui

            <strong>
                {{ $importAbsensi->jumlah_diperbarui }}
            </strong>
        </div>
    </div>

    <p>
        Diunggah oleh:
        <strong>
            {{ $importAbsensi->pengimpor?->nama ?? 'Sistem' }}
        </strong>
    </p>

    <p>
        Waktu unggah:
        <strong>
            {{ $importAbsensi->created_at->format(
                'd-m-Y H:i'
            ) }}
        </strong>
    </p>

    @if ($importAbsensi->dikonfirmasi_pada !== null)
    <p>
        Waktu konfirmasi:
        <strong>
            {{ $importAbsensi
                    ->dikonfirmasi_pada
                    ->format('d-m-Y H:i') }}
        </strong>
    </p>
    @endif
</section>

@if (
$importAbsensi->masihPratinjau()
&& $importAbsensi->jumlah_tidak_valid > 0
)
<div class="alert alert-warning">
    Terdapat
    {{ $importAbsensi->jumlah_tidak_valid }}
    baris tidak valid. Baris tersebut tidak akan
    dimasukkan. Periksa kolom kesalahan di bawah.
</div>
@endif

@if (
$importAbsensi->masihPratinjau()
&& $importAbsensi->jumlah_valid > 0
)
<section class="card no-print" style="margin-bottom: 24px;">
    <h2>Konfirmasi Import</h2>

    <p>
        Setelah dikonfirmasi, seluruh baris valid
        akan ditambahkan atau memperbarui data
        absensi yang sudah ada.
    </p>

    <div class="form-actions">
        <form
            method="POST"
            action="{{ route(
                    'admin.import-absensi.konfirmasi',
                    $importAbsensi
                ) }}"
            onsubmit="
                    return confirm(
                        'Konfirmasi import absensi ini?'
                    );
                ">
            @csrf

            <button
                class="button button-success"
                type="submit">
                Konfirmasi Import
            </button>
        </form>

        <form
            method="POST"
            action="{{ route(
                    'admin.import-absensi.batalkan',
                    $importAbsensi
                ) }}"
            onsubmit="
                    return confirm(
                        'Batalkan pratinjau import ini?'
                    );
                ">
            @csrf
            @method('PATCH')

            <button
                class="button button-danger"
                type="submit">
                Batalkan Import
            </button>
        </form>
    </div>
</section>
@elseif ($importAbsensi->masihPratinjau())
<section class="card no-print" style="margin-bottom: 24px;">
    <div class="alert alert-error">
        Tidak ada baris valid yang dapat dikonfirmasi.
    </div>

    <form
        method="POST"
        action="{{ route(
                'admin.import-absensi.batalkan',
                $importAbsensi
            ) }}">
        @csrf
        @method('PATCH')

        <button
            class="button button-danger"
            type="submit">
            Batalkan Import
        </button>
    </form>
</section>
@endif

<div class="page-header">
    <div>
        <h2>Rincian Baris</h2>

        <p class="muted">
            Maksimal 100 baris ditampilkan per halaman.
        </p>
    </div>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Baris</th>
                <th>Validasi</th>
                <th>NIP</th>
                <th>Pegawai</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Lembur</th>
                <th>Tindakan</th>
                <th>Catatan</th>
                <th>Kesalahan</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($daftarBaris as $baris)
            @php
            $asli = $baris->data_asli ?? [];
            $normal = $baris->data_normal ?? [];
            $kesalahan = $baris->kesalahan ?? [];

            $nip =
            $normal['nip']
            ?? $asli['nip']
            ?? '-';

            $tanggalBaris =
            $normal['tanggal_absensi']
            ?? $asli['tanggal_absensi']
            ?? '-';

            $statusBaris =
            $normal['status']
            ?? $asli['status']
            ?? '-';

            $jamLembur =
            $normal['jam_lembur']
            ?? $asli['jam_lembur']
            ?? 0;

            $catatan =
            $normal['catatan']
            ?? $asli['catatan']
            ?? null;
            @endphp

            <tr>
                <td>{{ $baris->nomor_baris }}</td>

                <td>
                    @if ($baris->valid)
                    <span class="
                                status-badge
                                status-hadir
                            ">
                        Valid
                    </span>
                    @else
                    <span class="
                                status-badge
                                status-alpa
                            ">
                        Tidak Valid
                    </span>
                    @endif
                </td>

                <td>{{ $nip }}</td>

                <td>
                    {{ $normal['nama_pegawai'] ?? '-' }}
                </td>

                <td>{{ $tanggalBaris }}</td>

                <td>
                    @if (
                    isset($labelStatus[$statusBaris])
                    )
                    <span class="
                                status-badge
                                status-{{ $statusBaris }}
                            ">
                        {{ $labelStatus[$statusBaris] }}
                    </span>
                    @else
                    {{ $statusBaris }}
                    @endif
                </td>

                <td>
                    {{ number_format(
                            (float) $jamLembur,
                            1,
                            ',',
                            '.'
                        ) }}
                    jam
                </td>

                <td>
                    @if (
                    ($normal['tindakan'] ?? null)
                    === 'perbarui'
                    )
                    <span class="
                                status-badge
                                status-draf
                            ">
                        Perbarui
                    </span>
                    @elseif (
                    ($normal['tindakan'] ?? null)
                    === 'tambah'
                    )
                    <span class="
                                status-badge
                                status-hadir
                            ">
                        Tambah
                    </span>
                    @else
                    -
                    @endif
                </td>

                <td>{{ $catatan ?: '-' }}</td>

                <td style="min-width: 280px;">
                    @if ($kesalahan !== [])
                    <ul style="
                                margin: 0;
                                padding-left: 18px;
                                color: #991b1b;
                            ">
                        @foreach ($kesalahan as $pesan)
                        <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                    @else
                    <span class="status-active">
                        Tidak ada kesalahan
                    </span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10">
                    Tidak ada rincian baris import.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($daftarBaris->hasPages())
{{ $daftarBaris->links() }}
@endif
@endsection