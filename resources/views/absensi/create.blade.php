@extends('layouts.app')

@section('title', 'Tambah Absensi - PayrollHub')

@section('content')
<div class="page-header">
    <div>
        <h1>Tambah Absensi</h1>

        <p class="muted">
            Catat absensi dan lembur seorang pegawai.
        </p>
    </div>

    <a
        href="{{ route('admin.absensi.index') }}"
        class="button button-secondary">
        Kembali
    </a>
</div>

<section class="card">
    <form
        method="POST"
        action="{{ route('admin.absensi.store') }}">
        @csrf

        @include('absensi._form')

        <div class="form-actions">
            <button class="button" type="submit">
                Simpan Absensi
            </button>

            <a
                href="{{ route('admin.absensi.index') }}"
                class="button button-secondary">
                Batal
            </a>
        </div>
    </form>
</section>
@endsection