@extends('layouts.app')

@section('title', 'Buat Akun Pegawai - PayrollHub')

@section('content')
<div class="page-header">
    <div>
        <h1>Buat Akun Pegawai</h1>

        <p class="muted">
            Buat akun login untuk mengakses
            slip gaji pegawai.
        </p>
    </div>

    <a
        href="{{ route('admin.pegawai.index') }}"
        class="button button-secondary">
        Kembali
    </a>
</div>

<section class="card" style="margin-bottom: 24px;">
    <h2>Informasi Pegawai</h2>

    <div class="summary-strip">
        <div class="summary-item">
            NIP

            <strong style="font-size: 20px;">
                {{ $pegawai->nip }}
            </strong>
        </div>

        <div class="summary-item">
            Nama

            <strong style="font-size: 20px;">
                {{ $pegawai->nama }}
            </strong>
        </div>

        <div class="summary-item">
            Jabatan

            <strong style="font-size: 20px;">
                {{ $pegawai->jabatan->nama }}
            </strong>
        </div>
    </div>
</section>

<section class="card">
    <form
        method="POST"
        action="{{ route(
            'admin.pegawai.akun.store',
            $pegawai
        ) }}">
        @csrf

        <div class="form-group">
            <label for="email">Email Login</label>

            <input
                id="email"
                name="email"
                type="email"
                maxlength="255"
                value="{{ old('email') }}"
                autocomplete="email"
                required
                autofocus>

            @error('email')
            <div class="error">
                {{ $message }}
            </div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">
                Password Awal
            </label>

            <input
                id="password"
                name="password"
                type="password"
                minlength="8"
                autocomplete="new-password"
                required>

            <p class="muted">
                Password minimal 8 karakter.
            </p>

            @error('password')
            <div class="error">
                {{ $message }}
            </div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">
                Konfirmasi Password
            </label>

            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                minlength="8"
                autocomplete="new-password"
                required>
        </div>

        <div class="alert alert-info">
            Akun akan memiliki peran Pegawai dan hanya
            dapat melihat slip gajinya sendiri.
        </div>

        <div class="form-actions">
            <button class="button" type="submit">
                Buat Akun
            </button>

            <a
                href="{{ route('admin.pegawai.index') }}"
                class="button button-secondary">
                Batal
            </a>
        </div>
    </form>
</section>
@endsection