@extends('layouts.app')

@section('title', 'Detail Slip Gaji - PayrollHub')

@section('content')
@php
$labelStatus = [
'hadir' => 'Hadir',
'sakit' => 'Sakit',
'izin' => 'Izin',
'cuti' => 'Cuti',
'alpa' => 'Alpa',
];
@endphp

<div class="page-header no-print">
    <div>
        <h1>Detail Slip Gaji</h1>

        <p class="muted">
            {{ $penggajian->pegawai->nip }} -
            {{ $penggajian->pegawai->nama }}
        </p>
    </div>

    <div class="form-actions" style="margin: 0;">
        <a
            href="{{ route(
                'pegawai.slip-gaji.cetak',
                $penggajian
            ) }}"
            class="button"
            target="_blank"
            rel="noopener">
            Cetak Slip
        </a>

        <a
            href="{{ route(
                'pegawai.slip-gaji.index'
            ) }}"
            class="button button-secondary">
            Kembali
        </a>
    </div>
</div>

@include('penggajian._rincian')

<section
    class="card"
    style="margin-top: 24px;">
    <div class="page-header">
        <div>
            <h2>Detail Absensi Harian</h2>

            <p class="muted">
                Rincian tanggal kehadiran, sakit,
                izin, cuti, alpa, dan lembur pada
                periode penggajian ini.
            </p>
        </div>
    </div>

    <div class="table-wrapper" style="box-shadow: none;">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Hari</th>
                    <th>Status</th>
                    <th>Jam Lembur</th>
                    <th>Keterangan Lembur</th>
                    <th>Catatan Absensi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($detailAbsensi as $absensi)
                <tr>
                    <td>
                        {{ $absensi->tanggal_absensi
                                ->format('d-m-Y') }}
                    </td>

                    <td>
                        {{ $absensi->tanggal_absensi
                                ->locale('id')
                                ->translatedFormat('l') }}
                    </td>

                    <td>
                        <span class="
                                status-badge
                                status-{{ $absensi->status }}
                            ">
                            {{ $labelStatus[$absensi->status] }}
                        </span>
                    </td>

                    <td>
                        {{ number_format(
                                (float) $absensi->jam_lembur,
                                1,
                                ',',
                                '.'
                            ) }}
                        jam
                    </td>

                    <td>
                        {{ $absensi->catatan_lembur ?? '-' }}
                    </td>

                    <td>
                        {{ $absensi->catatan ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        Tidak ada data absensi
                        pada periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection