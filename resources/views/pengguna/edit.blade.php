@extends('layouts.app')

@section('title', 'Ubah Pengguna - PayrollHub')

@section('content')
<div class="page-header">
    <div>
        <h1>Ubah Pengguna</h1>

        <p class="muted">
            Kelola email, password, dan status akun.
        </p>
    </div>

    <a
        href="{{ route('admin.pengguna.index') }}"
        class="button button-secondary">
        Kembali
    </a>
</div>

<section class="card" style="margin-bottom: 24px;">
    <div class="summary-strip">
        <div class="summary-item">
            Peran

            <strong style="font-size: 20px;">
                {{ $pengguna->adalahAdmin()
                    ? 'Admin HRD'
                    : 'Pegawai' }}
            </strong>
        </div>

        <div class="summary-item">
            Status Akun

            <strong style="font-size: 20px;">
                {{ $pengguna->aktif
                    ? 'Aktif'
                    : 'Tidak Aktif' }}
            </strong>
        </div>

        @if ($pengguna->pegawai !== null)
        <div class="summary-item">
            NIP

            <strong style="font-size: 20px;">
                {{ $pengguna->pegawai->nip }}
            </strong>
        </div>
        @endif
    </div>
</section>

@if ($pengguna->is(auth()->user()))
<div class="alert alert-warning">
    Ini adalah akun yang sedang Anda gunakan.
    Anda tidak dapat menonaktifkan akun sendiri.
</div>
@endif

<section class="card">
    <form
        method="POST"
        action="{{ route(
            'admin.pengguna.update',
            $pengguna
        ) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="nama">Nama Pengguna</label>

            <input
                id="nama"
                name="nama"
                type="text"
                maxlength="100"
                value="{{ old(
                    'nama',
                    $pengguna->nama
                ) }}"
                @readonly($pengguna->adalahPegawai())
            required>

            @if ($pengguna->adalahPegawai())
            <p class="muted">
                Nama akun pegawai mengikuti
                data pada menu Pegawai.
            </p>
            @endif

            @error('nama')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Email Login</label>

            <input
                id="email"
                name="email"
                type="email"
                maxlength="255"
                value="{{ old(
                    'email',
                    $pengguna->email
                ) }}"
                autocomplete="email"
                required>

            @error('email')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <input
                type="hidden"
                name="aktif"
                value="0">

            <label style="
                display: flex;
                gap: 9px;
                align-items: center;
                font-weight: normal;
            ">
                <input
                    name="aktif"
                    type="checkbox"
                    value="1"
                    @checked(
                    old( 'aktif' ,
                    $pengguna->aktif
                )
                )
                @disabled($pengguna->is(auth()->user()))>

                <span>Akun aktif</span>
            </label>

            @if ($pengguna->is(auth()->user()))
            <input
                type="hidden"
                name="aktif"
                value="1">
            @endif

            @error('aktif')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <hr style="
            margin: 28px 0;
            border: 0;
            border-top: 1px solid #e5e7eb;
        ">

        <h2>Ganti Password</h2>

        <p class="muted">
            Kosongkan kedua kolom berikut jika
            password tidak ingin diganti.
        </p>

        <div class="form-group">
            <label for="password">
                Password Baru
            </label>

            <input
                id="password"
                name="password"
                type="password"
                minlength="8"
                autocomplete="new-password">

            @error('password')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">
                Konfirmasi Password Baru
            </label>

            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                minlength="8"
                autocomplete="new-password">
        </div>

        <div class="form-actions">
            <button class="button" type="submit">
                Simpan Perubahan
            </button>

            <a
                href="{{ route('admin.pengguna.index') }}"
                class="button button-secondary">
                Batal
            </a>
        </div>
    </form>
</section>
@endsection