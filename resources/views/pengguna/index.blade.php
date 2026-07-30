@extends('layouts.app')

@section('title', 'Data Pengguna - PayrollHub')

@section('content')
<div class="page-header">
    <div>
        <h1>Data Pengguna</h1>

        <p class="muted">
            Kelola akun Admin HRD dan akun login pegawai.
        </p>
    </div>

    <a
        href="{{ route('admin.pengguna.create') }}"
        class="button">
        Tambah Admin HRD
    </a>
</div>

@if ($daftarPengguna->isEmpty())
<section class="card">
    <p>Belum ada akun pengguna.</p>
</section>
@else
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Peran</th>
                <th>Pegawai</th>
                <th>Dibuat Oleh</th>
                <th>Status</th>
                <th class="no-print">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($daftarPengguna as $pengguna)
            <tr>
                <td>
                    {{ $pengguna->nama }}

                    @if ($pengguna->is(auth()->user()))
                    <div class="muted">
                        Akun Anda
                    </div>
                    @endif
                </td>

                <td>{{ $pengguna->email }}</td>

                <td>
                    @if ($pengguna->adalahAdmin())
                    <span class="status-badge status-final">
                        Admin HRD
                    </span>
                    @else
                    <span class="status-badge status-hadir">
                        Pegawai
                    </span>
                    @endif
                </td>

                <td>
                    @if ($pengguna->pegawai !== null)
                    {{ $pengguna->pegawai->nip }}
                    -
                    {{ $pengguna->pegawai->nama }}
                    @else
                    <span class="muted">
                        Tidak terhubung
                    </span>
                    @endif
                </td>

                <td>
                    {{ $pengguna->pembuat?->nama ?? 'Sistem' }}
                </td>

                <td>
                    @if ($pengguna->aktif)
                    <span class="status-active">
                        Aktif
                    </span>
                    @else
                    <span class="status-inactive">
                        Tidak Aktif
                    </span>
                    @endif
                </td>

                <td class="no-print">
                    <a
                        href="{{ route(
                                    'admin.pengguna.edit',
                                    $pengguna
                                ) }}"
                        class="button button-small">
                        Ubah
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if ($daftarPengguna->hasPages())
<div class="no-print">
    {{ $daftarPengguna->links() }}
</div>
@endif
@endif
@endsection