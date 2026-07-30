@extends('layouts.app')

@section('title', 'Ubah Pegawai - PayrollHub')

@section('content')
<div class="page-header">
    <div>
        <h1>Ubah Pegawai</h1>

        <p class="muted">
            {{ $pegawai->nip }} -
            {{ $pegawai->nama }}
        </p>
    </div>

    <a
        href="{{ route('admin.pegawai.index') }}"
        class="button button-secondary">
        Kembali
    </a>
</div>

@if (
$pegawai->user !== null
&& $pegawai->status_kepegawaian !== 'aktif'
)
<div class="alert alert-warning">
    Jika pegawai dibuat tidak aktif atau mengundurkan diri,
    akun loginnya juga akan dinonaktifkan.
</div>
@endif

<section class="card">
    <form
        method="POST"
        action="{{ route(
            'admin.pegawai.update',
            $pegawai
        ) }}">
        @csrf
        @method('PUT')

        @include('pegawai._form')

        <div class="form-actions">
            <button class="button" type="submit">
                Simpan Perubahan
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