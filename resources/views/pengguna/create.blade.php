@extends('layouts.app')

@section('title', 'Tambah Admin HRD - PayrollHub')

@section('content')
<div class="page-header">
    <div>
        <h1>Tambah Admin HRD</h1>

        <p class="muted">
            Buat akun admin tambahan sebagai
            cadangan atau pengganti Admin HRD.
        </p>
    </div>

    <a
        href="{{ route('admin.pengguna.index') }}"
        class="button button-secondary">
        Kembali
    </a>
</div>

<section class="card">
    <div class="alert alert-info">
        Akun yang dibuat dari halaman ini otomatis
        memiliki peran Admin HRD dan dapat mengelola
        seluruh data PayrollHub.
    </div>

    <form
        method="POST"
        action="{{ route('admin.pengguna.store') }}">
        @csrf

        <div class="form-group">
            <label for="nama">Nama Admin</label>

            <input
                id="nama"
                name="nama"
                type="text"
                maxlength="100"
                value="{{ old('nama') }}"
                required
                autofocus>

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
                value="{{ old('email') }}"
                autocomplete="email"
                required>

            @error('email')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password Awal</label>

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
            <div class="error">{{ $message }}</div>
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

        <div class="form-actions">
            <button class="button" type="submit">
                Buat Admin HRD
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