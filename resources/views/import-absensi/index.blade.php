@extends('layouts.app')

@section('title', 'Import Absensi - PayrollHub')

@section('content')
<div class="page-header">
    <div>
        <h1>Import Absensi Excel</h1>

        <p class="muted">
            Unggah absensi dalam jumlah banyak
            menggunakan file Excel.
        </p>
    </div>

    <a
        href="{{ route('admin.absensi.index') }}"
        class="button button-secondary">
        Kembali ke Absensi
    </a>
</div>

<section class="card" style="margin-bottom: 24px;">
    <h2>1. Unduh Template</h2>

    <p>
        Gunakan template resmi agar susunan kolom,
        format tanggal, dan status absensi sesuai
        dengan ketentuan PayrollHub.
    </p>

    <a
        href="{{ route(
            'admin.import-absensi.template'
        ) }}"
        class="button">
        Unduh Template Excel
    </a>
</section>

<section class="card" style="margin-bottom: 24px;">
    <h2>2. Isi Data Absensi</h2>

    <div class="table-wrapper" style="box-shadow: none;">
        <table>
            <thead>
                <tr>
                    <th>Kolom</th>
                    <th>Nama Kolom</th>
                    <th>Ketentuan</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>A</td>
                    <td>NIP</td>
                    <td>
                        Harus sesuai data pegawai,
                        misalnya KRY-001.
                    </td>
                </tr>

                <tr>
                    <td>B</td>
                    <td>Tanggal Absensi</td>
                    <td>
                        Format tanggal:
                        <strong>YYYY-MM-DD</strong>.
                    </td>
                </tr>

                <tr>
                    <td>C</td>
                    <td>Status</td>
                    <td>
                        hadir, sakit, izin, cuti,
                        atau alpa.
                    </td>
                </tr>

                <tr>
                    <td>D</td>
                    <td>Jam Lembur</td>
                    <td>
                        Antara 0–12 jam dan menggunakan
                        kelipatan 0,5 jam.
                    </td>
                </tr>

                <tr>
                    <td>E</td>
                    <td>Catatan Lembur</td>
                    <td>
                        Wajib jika jam lembur lebih
                        dari nol.
                    </td>
                </tr>

                <tr>
                    <td>F</td>
                    <td>Catatan</td>
                    <td>Opsional.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="alert alert-warning">
        Tanggal harus tersedia sebagai hari kerja
        pada Kalender Kerja. Data pada periode
        penggajian yang sudah final atau dibayar
        tidak dapat diimpor.
    </div>
</section>

<section class="card" style="margin-bottom: 32px;">
    <h2>3. Unggah dan Periksa</h2>

    <p class="muted">
        File tidak langsung masuk ke absensi.
        Sistem akan menampilkan pratinjau terlebih dahulu.
    </p>

    <form
        method="POST"
        action="{{ route(
            'admin.import-absensi.pratinjau'
        ) }}"
        enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="file_absensi">
                File Absensi
            </label>

            <input
                id="file_absensi"
                name="file_absensi"
                type="file"
                accept=".xlsx,.xls,.csv"
                required>

            <p class="muted">
                Format yang diterima: XLSX, XLS,
                atau CSV. Maksimal 10 MB.
            </p>

            @error('file_absensi')
            <div class="error">
                {{ $message }}
            </div>
            @enderror
        </div>

        <button class="button" type="submit">
            Unggah dan Tampilkan Pratinjau
        </button>
    </form>
</section>

<div class="page-header">
    <div>
        <h2>Riwayat Import</h2>

        <p class="muted">
            Daftar file yang pernah diperiksa
            atau dikonfirmasi.
        </p>
    </div>
</div>

@if ($daftarImport->isEmpty())
<section class="card">
    Belum ada riwayat import absensi.
</section>
@else
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Nama File</th>
                <th>Pengimpor</th>
                <th>Status</th>
                <th>Baris</th>
                <th>Valid</th>
                <th>Tidak Valid</th>
                <th>Ditambahkan</th>
                <th>Diperbarui</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($daftarImport as $import)
            <tr>
                <td>
                    {{ $import->created_at->format(
                                'd-m-Y H:i'
                            ) }}
                </td>

                <td>{{ $import->nama_file_asli }}</td>

                <td>
                    {{ $import->pengimpor?->nama ?? 'Sistem' }}
                </td>

                <td>
                    @switch($import->status)
                    @case('pratinjau')
                    <span class="
                                        status-badge
                                        status-draf
                                    ">
                        Pratinjau
                    </span>
                    @break

                    @case('selesai')
                    <span class="
                                        status-badge
                                        status-dibayar
                                    ">
                        Selesai
                    </span>
                    @break

                    @case('dibatalkan')
                    <span class="
                                        status-badge
                                        status-alpa
                                    ">
                        Dibatalkan
                    </span>
                    @break
                    @endswitch
                </td>

                <td>{{ $import->jumlah_baris }}</td>

                <td>
                    <span class="status-active">
                        {{ $import->jumlah_valid }}
                    </span>
                </td>

                <td>
                    <span class="status-inactive">
                        {{ $import->jumlah_tidak_valid }}
                    </span>
                </td>

                <td>
                    {{ $import->jumlah_ditambahkan }}
                </td>

                <td>
                    {{ $import->jumlah_diperbarui }}
                </td>

                <td>
                    <a
                        href="{{ route(
                                    'admin.import-absensi.show',
                                    $import
                                ) }}"
                        class="button button-small">
                        Lihat
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if ($daftarImport->hasPages())
{{ $daftarImport->links() }}
@endif
@endif
@endsection