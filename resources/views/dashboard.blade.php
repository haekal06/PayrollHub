@extends('layouts.app')

@section('title', 'Dashboard - PayrollHub')

@section('content')
<div class="page-header">
    <div>
        <h1>Dashboard</h1>

        <p>
            Selamat datang,
            <strong>{{ auth()->user()->nama }}</strong>.
        </p>
    </div>
</div>

<section class="card">
    @if (auth()->user()->adalahAdmin())
    <h2>Dashboard Admin HRD</h2>

    <p class="muted">
        Kelola data pegawai, absensi,
        dan proses penggajian perusahaan.
    </p>

    <div class="dashboard-grid">
        <a
            href="{{ route('admin.jabatan.index') }}"
            class="dashboard-card">
            <h3>Jabatan</h3>

            <p>
                Kelola gaji pokok, tunjangan,
                tarif lembur, dan status jabatan.
            </p>
        </a>

        <a
            href="{{ route('admin.pegawai.index') }}"
            class="dashboard-card">
            <h3>Pegawai</h3>

            <p>
                Kelola identitas, NIP otomatis,
                jabatan, status, dan akun pegawai.
            </p>
        </a>

        <a
            href="{{ route('admin.kalender-kerja.index') }}"
            class="dashboard-card">
            <h3>Kalender Kerja</h3>

            <p>
                Tentukan hari kerja, akhir pekan,
                libur nasional, dan libur perusahaan.
            </p>
        </a>

        <a
            href="{{ route('admin.absensi.index') }}"
            class="dashboard-card">
            <h3>Absensi</h3>

            <p>
                Kelola absensi harian, massal,
                impor Excel, dan jam lembur.
            </p>
        </a>

        <a
            href="{{ route('admin.penggajian.index') }}"
            class="dashboard-card">
            <h3>Penggajian</h3>

            <p>
                Proses, periksa, finalisasi,
                revisi, dan catat pembayaran gaji.
            </p>
        </a>

        <a
            href="{{ route('admin.pengguna.index') }}"
            class="dashboard-card">
            <h3>Pengguna</h3>

            <p>
                Kelola akun Admin HRD dan
                akun login milik pegawai.
            </p>
        </a>
    </div>
    @else
    <h2>Dashboard Pegawai</h2>

    <p class="muted">
        Melihat riwayat dan rincian slip gaji Anda.
    </p>

    <div class="dashboard-grid">
        <a
            href="{{ route('pegawai.slip-gaji.index') }}"
            class="dashboard-card">
            <h3>Slip Gaji Saya</h3>

            <p>
                Lihat rincian pendapatan,
                lembur, potongan, dan gaji bersih.
            </p>
        </a>
    </div>
    @endif
</section>
@endsection