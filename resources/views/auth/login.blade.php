@extends('layouts.app')

@section('title', 'Login - PayrollHub')

@section('content')
<section class="card login-card">
    <div style="text-align: center; margin-bottom: 28px;">
        <h1 style="margin-bottom: 8px;">
            PayrollHub
        </h1>

        <p class="muted" style="margin: 0;">
            Sistem Pengelolaan Payroll
        </p>
    </div>

    <h2>Login</h2>

    <p class="muted">
        Masukkan email dan password akun Anda.
    </p>

    <form
        method="POST"
        action="{{ route('login.autentikasi') }}">
        @csrf

        <div class="form-group">
            <label for="email">Email</label>

            <input
                id="email"
                name="email"
                type="email"
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
                Password
            </label>

            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required>

            @error('password')
            <div class="error">
                {{ $message }}
            </div>
            @enderror
        </div>

        <div class="form-group">
            <label style="
                display: flex;
                gap: 8px;
                align-items: center;
                font-weight: normal;
            ">
                <input
                    name="ingat_saya"
                    type="checkbox"
                    value="1"
                    @checked(old('ingat_saya'))>

                <span>Ingat saya</span>
            </label>
        </div>

        <button
            class="button"
            style="width: 100%;"
            type="submit">
            Masuk
        </button>
    </form>
</section>
@endsection