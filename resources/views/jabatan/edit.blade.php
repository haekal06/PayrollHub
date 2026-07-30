@extends('layouts.app')

@section('title', 'Ubah Jabatan - PayrollHub')

@section('content')
<div class="page-header">
    <div>
        <h1>Ubah Jabatan</h1>

        <p class="muted">
            {{ $jabatan->kode }} -
            {{ $jabatan->nama }}
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
        action="{{ route(
            'admin.jabatan.update',
            $jabatan
        ) }}">
        @csrf
        @method('PUT')

        @include('jabatan._form')

        <div class="form-actions">
            <button class="button" type="submit">
                Simpan Perubahan
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