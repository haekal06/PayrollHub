@extends('layouts.app')

@section('title', 'Tambah Pegawai - PayrollHub')

@section('content')
<div class="page-header">
    <div>
        <h1>Tambah Pegawai</h1>

        <p class="muted">
            NIP akan diberikan otomatis
            setelah data disimpan.
        </p>
    </div>

    <a
        href="{{ route('admin.pegawai.index') }}"
        class="button button-secondary">
        Kembali
    </a>
</div>

<section class="card">
    <form
        method="POST"
        action="{{ route('admin.pegawai.store') }}">
        @csrf

        @include('pegawai._form')

        <div class="form-actions">
            <button class="button" type="submit">
                Simpan Pegawai
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