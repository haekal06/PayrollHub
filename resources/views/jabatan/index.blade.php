@extends('layouts.app')

@section('title', 'Data Jabatan - PayrollHub')

@section('content')
<div class="page-header">
    <div>
        <h1>Data Jabatan</h1>

        <p class="muted">
            Kelola gaji pokok, tunjangan,
            dan tarif lembur setiap jabatan.
        </p>
    </div>

    <a
        href="{{ route('admin.jabatan.create') }}"
        class="button">
        Tambah Jabatan
    </a>
</div>

@if ($daftarJabatan->isEmpty())
<section class="card">
    <p>
        Belum ada data jabatan.
    </p>

    <a
        href="{{ route('admin.jabatan.create') }}"
        class="button">
        Buat Jabatan Pertama
    </a>
</section>
@else
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Jabatan</th>
                <th>Gaji Pokok</th>
                <th>Tunjangan</th>
                <th>Tarif Lembur/Jam</th>
                <th>Pegawai</th>
                <th>Status</th>
                <th class="no-print">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($daftarJabatan as $jabatan)
            <tr>
                <td>{{ $jabatan->kode }}</td>

                <td>{{ $jabatan->nama }}</td>

                <td>
                    Rp{{ number_format(
                                (float) $jabatan->gaji_pokok,
                                0,
                                ',',
                                '.'
                            ) }}
                </td>

                <td>
                    Rp{{ number_format(
                                (float) $jabatan->tunjangan,
                                0,
                                ',',
                                '.'
                            ) }}
                </td>

                <td>
                    Rp{{ number_format(
                                (float) $jabatan->tarif_lembur_per_jam,
                                0,
                                ',',
                                '.'
                            ) }}
                </td>

                <td>
                    {{ $jabatan->pegawais_count }}
                </td>

                <td>
                    @if ($jabatan->aktif)
                    <span class="status-active">
                        Aktif
                    </span>
                    @else
                    <span class="status-inactive">
                        Tidak Aktif
                    </span>
                    @endif
                </td>

                <td class="no-print">
                    <div class="form-actions" style="margin: 0;">
                        <a
                            href="{{ route(
                                        'admin.jabatan.edit',
                                        $jabatan
                                    ) }}"
                            class="button button-small">
                            Ubah
                        </a>

                        @if ($jabatan->pegawais_count === 0)
                        <form
                            method="POST"
                            action="{{ route(
                                            'admin.jabatan.destroy',
                                            $jabatan
                                        ) }}"
                            onsubmit="
                                            return confirm(
                                                'Hapus jabatan ini?'
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

@if ($daftarJabatan->hasPages())
<div class="no-print">
    {{ $daftarJabatan->links() }}
</div>
@endif
@endif
@endsection