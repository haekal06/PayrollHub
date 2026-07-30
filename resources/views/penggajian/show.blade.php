@extends('layouts.app')

@section('title', 'Detail Penggajian - PayrollHub')

@section('content')
<div class="page-header no-print">
    <div>
        <h1>Detail Penggajian</h1>

        <p class="muted">
            {{ $penggajian->pegawai->nip }} -
            {{ $penggajian->pegawai->nama }}
        </p>
    </div>

    <div class="form-actions" style="margin: 0;">
        <a
            href="{{ route(
                'admin.penggajian.cetak-slip',
                $penggajian
            ) }}"
            class="button button-secondary"
            target="_blank"
            rel="noopener">
            Cetak Slip
        </a>

        <a
            href="{{ route(
                'admin.penggajian.index',
                [
                    'bulan' => $penggajian->bulan,
                    'tahun' => $penggajian->tahun,
                ]
            ) }}"
            class="button button-secondary">
            Kembali
        </a>
    </div>
</div>

@if (
in_array(
$penggajian->status,
['draf', 'revisi'],
true
)
)
<section class="card no-print" style="margin-bottom: 24px;">
    <h2>Finalisasi Penggajian</h2>

    <p>
        Sistem akan menghitung ulang menggunakan
        absensi terbaru sebelum status menjadi final.
        Setelah final, absensi periode ini terkunci.
    </p>

    <form
        method="POST"
        action="{{ route(
                'admin.penggajian.finalisasi',
                $penggajian
            ) }}"
        onsubmit="
                return confirm(
                    'Finalisasi penggajian ini?'
                );
            ">
        @csrf
        @method('PATCH')

        <button
            class="button button-success"
            type="submit">
            Finalisasi Penggajian
        </button>
    </form>
</section>
@endif

@if ($penggajian->status === 'final')
<div
    class="filter-grid no-print"
    style="margin-bottom: 24px;">
    <section class="card">
        <h2>Tandai Sudah Dibayar</h2>

        <form
            method="POST"
            action="{{ route(
                    'admin.penggajian.dibayar',
                    $penggajian
                ) }}">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="catatan_pembayaran">
                    Catatan Pembayaran
                </label>

                <textarea
                    id="catatan_pembayaran"
                    name="catatan_pembayaran"
                    maxlength="500"
                    rows="3"
                    placeholder="Opsional">{{ old(
                            'catatan_pembayaran'
                        ) }}</textarea>

                @error('catatan_pembayaran')
                <div class="error">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <button
                class="button button-success"
                type="submit">
                Tandai Dibayar
            </button>
        </form>
    </section>

    <section class="card">
        <h2>Buka untuk Revisi</h2>

        <p class="muted">
            Absensi periode ini akan dibuka kembali.
        </p>

        <form
            method="POST"
            action="{{ route(
                    'admin.penggajian.buka-revisi',
                    $penggajian
                ) }}">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="alasan_revisi">
                    Alasan Revisi
                </label>

                <textarea
                    id="alasan_revisi"
                    name="alasan_revisi"
                    minlength="10"
                    maxlength="1000"
                    rows="3"
                    required>{{ old('alasan_revisi') }}</textarea>

                @error('alasan_revisi')
                <div class="error">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <button
                class="button button-warning"
                type="submit">
                Buka Revisi
            </button>
        </form>
    </section>
</div>
@endif

@include('penggajian._rincian')

@if ($penggajian->riwayatStatus->isNotEmpty())
<section class="card no-print" style="margin-top: 24px;">
    <h2>Riwayat Status</h2>

    <div class="table-wrapper" style="box-shadow: none;">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Status Asal</th>
                    <th>Status Tujuan</th>
                    <th>Diubah Oleh</th>
                    <th>Alasan/Catatan</th>
                </tr>
            </thead>

            <tbody>
                @foreach (
                $penggajian->riwayatStatus
                as $riwayat
                )
                <tr>
                    <td>
                        {{ $riwayat->diubah_pada->format(
                                    'd-m-Y H:i'
                                ) }}
                    </td>

                    <td>
                        {{ $riwayat->status_asal
                                    ? ucfirst($riwayat->status_asal)
                                    : '-' }}
                    </td>

                    <td>
                        {{ ucfirst(
                                    $riwayat->status_tujuan
                                ) }}
                    </td>

                    <td>
                        {{ $riwayat->pengubah?->nama
                                    ?? 'Sistem' }}
                    </td>

                    <td>
                        {{ $riwayat->alasan ?? '-' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif
@endsection