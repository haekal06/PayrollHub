@extends('layouts.app')

@section('title', 'Tambah Jabatan - PayrollHub')

@section('content')
<div class="page-header">
    <div>
        <h1>Tambah Jabatan</h1>

        <p class="muted">
            Masukkan informasi jabatan dan
            komponen gajinya.
        </p>
    </div>

    <a
        href="{{ route('admin.jabatan.index') }}"
        class="button button-secondary">
        Kembali
    </a>
</div>

<section class="card">
    <form
        method="POST"
        action="{{ route('admin.jabatan.store') }}">
        @csrf

        @include('jabatan._form')

        <div class="form-actions">
            <button class="button" type="submit">
                Simpan Jabatan
            </button>

            <a
                href="{{ route('admin.jabatan.index') }}"
                class="button button-secondary">
                Batal
            </a>
        </div>
    </form>
</section>
@endsection