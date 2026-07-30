@extends('layouts.app')

@section('title', 'Data Pegawai - PayrollHub')

@section('content')
<div class="page-header">
    <div>
        <h1>Data Pegawai</h1>

        <p class="muted">
            Kelola identitas, jabatan,
            status, dan akun pegawai.
        </p>
    </div>

    <a
        href="{{ route('admin.pegawai.create') }}"
        class="button">
        Tambah Pegawai
    </a>
</div>

@if ($daftarPegawai->isEmpty())
<section class="card">
    <p>Belum ada data pegawai.</p>

    <a
        href="{{ route('admin.pegawai.create') }}"
        class="button">
        Tambah Pegawai Pertama
    </a>
</section>
@else
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>NIP</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Jenis Kelamin</th>
                <th>Tanggal Masuk</th>
                <th>Status</th>
                <th>Akun</th>
                <th>Riwayat</th>
                <th class="no-print">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($daftarPegawai as $pegawai)
            <tr>
                <td>{{ $pegawai->nip }}</td>

                <td>{{ $pegawai->nama }}</td>

                <td>
                    {{ $pegawai->jabatan->nama }}
                </td>

                <td>
                    {{ $pegawai->jenis_kelamin === 'laki_laki'
                                ? 'Laki-laki'
                                : 'Perempuan' }}
                </td>

                <td>
                    {{ $pegawai->tanggal_masuk->format('d-m-Y') }}
                </td>

                <td>
                    @switch($pegawai->status_kepegawaian)
                    @case('aktif')
                    <span class="status-active">
                        Aktif
                    </span>
                    @break

                    @case('tidak_aktif')
                    <span class="status-inactive">
                        Tidak Aktif
                    </span>
                    @break

                    @case('mengundurkan_diri')
                    <span class="status-inactive">
                        Mengundurkan Diri
                    </span>
                    @break
                    @endswitch
                </td>

                <td>
                    @if ($pegawai->user !== null)
                    <div>
                        {{ $pegawai->user->email }}
                    </div>

                    @if ($pegawai->user->aktif)
                    <span class="status-active">
                        Akun aktif
                    </span>
                    @else
                    <span class="status-inactive">
                        Akun nonaktif
                    </span>
                    @endif
                    @elseif ($pegawai->masihAktif())
                    <a
                        href="{{ route(
                                        'admin.pegawai.akun.create',
                                        $pegawai
                                    ) }}"
                        class="button button-small">
                        Buat Akun
                    </a>
                    @else
                    <span class="muted">
                        Belum memiliki akun
                    </span>
                    @endif
                </td>

                <td>
                    {{ $pegawai->absensis_count }}
                    absensi,
                    {{ $pegawai->penggajians_count }}
                    penggajian
                </td>

                <td class="no-print">
                    <div class="form-actions" style="margin: 0;">
                        <a
                            href="{{ route(
                                        'admin.pegawai.edit',
                                        $pegawai
                                    ) }}"
                            class="button button-small">
                            Ubah
                        </a>

                        @if (
                        $pegawai->user_id === null
                        && $pegawai->absensis_count === 0
                        && $pegawai->penggajians_count === 0
                        )
                        <form
                            method="POST"
                            action="{{ route(
                                            'admin.pegawai.destroy',
                                            $pegawai
                                        ) }}"
                            onsubmit="
                                            return confirm(
                                                'Hapus pegawai ini?'
                                            );
                                        ">
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="button button-danger button-small">
                                Hapus
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if ($daftarPegawai->hasPages())
<div class="no-print">
    {{ $daftarPegawai->links() }}
</div>
@endif
@endif
@endsection