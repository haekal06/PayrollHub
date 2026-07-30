@php
$namaBulan = [
1 => 'Januari',
2 => 'Februari',
3 => 'Maret',
4 => 'April',
5 => 'Mei',
6 => 'Juni',
7 => 'Juli',
8 => 'Agustus',
9 => 'September',
10 => 'Oktober',
11 => 'November',
12 => 'Desember',
];
@endphp

<div class="report-header">
    <h1>PayrollHub</h1>
    <h2>Slip Gaji Pegawai</h2>

    <p>
        Periode:
        {{ $namaBulan[$penggajian->bulan] }}
        {{ $penggajian->tahun }}
    </p>
</div>

<section class="card" style="margin-bottom: 24px;">
    <h2>Informasi Pegawai</h2>

    <div class="summary-strip">
        <div class="summary-item">
            NIP

            <strong style="font-size: 19px;">
                {{ $penggajian->pegawai->nip }}
            </strong>
        </div>

        <div class="summary-item">
            Nama

            <strong style="font-size: 19px;">
                {{ $penggajian->pegawai->nama }}
            </strong>
        </div>

        <div class="summary-item">
            Jabatan

            <strong style="font-size: 19px;">
                {{ $penggajian->pegawai->jabatan->nama }}
            </strong>
        </div>

        <div class="summary-item">
            Status

            <strong style="font-size: 19px;">
                {{ ucfirst($penggajian->status) }}
            </strong>
        </div>
    </div>
</section>

<section class="card" style="margin-bottom: 24px;">
    <h2>Rekap Absensi</h2>

    <div class="summary-strip">
        <div class="summary-item">
            Hari Kerja
            <strong>{{ $penggajian->jumlah_hari_kerja }}</strong>
        </div>

        <div class="summary-item">
            Hadir
            <strong>{{ $penggajian->jumlah_hadir }}</strong>
        </div>

        <div class="summary-item">
            Sakit
            <strong>{{ $penggajian->jumlah_sakit }}</strong>
        </div>

        <div class="summary-item">
            Izin
            <strong>{{ $penggajian->jumlah_izin }}</strong>
        </div>

        <div class="summary-item">
            Cuti
            <strong>{{ $penggajian->jumlah_cuti }}</strong>
        </div>

        <div class="summary-item">
            Alpa
            <strong>{{ $penggajian->jumlah_alpa }}</strong>
        </div>

        <div class="summary-item">
            Jam Lembur

            <strong>
                {{ number_format(
                    (float) $penggajian->jam_lembur,
                    1,
                    ',',
                    '.'
                ) }}
            </strong>
        </div>
    </div>
</section>

<section class="card" style="margin-bottom: 24px;">
    <h2>Pendapatan</h2>

    <div class="table-wrapper" style="box-shadow: none;">
        <table>
            <tbody>
                <tr>
                    <th>Gaji Pokok</th>

                    <td>
                        Rp{{ number_format(
                            (float) $penggajian->gaji_pokok,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>
                </tr>

                <tr>
                    <th>Tunjangan</th>

                    <td>
                        Rp{{ number_format(
                            (float) $penggajian->tunjangan,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>
                </tr>

                <tr>
                    <th>Upah Harian</th>

                    <td>
                        Rp{{ number_format(
                            (float) $penggajian->upah_harian,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>
                </tr>

                <tr>
                    <th>Tarif Lembur per Jam</th>

                    <td>
                        Rp{{ number_format(
                            (float) $penggajian->tarif_lembur,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>
                </tr>

                <tr>
                    <th>Upah Lembur</th>

                    <td>
                        Rp{{ number_format(
                            (float) $penggajian->upah_lembur,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>
                </tr>

                <tr>
                    <th>
                        Bonus

                        @if ($penggajian->catatan_bonus)
                        <div class="muted">
                            {{ $penggajian->catatan_bonus }}
                        </div>
                        @endif
                    </th>

                    <td>
                        Rp{{ number_format(
                            (float) $penggajian->bonus,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>
                </tr>

                <tr>
                    <th>Gaji Kotor</th>

                    <td>
                        <strong>
                            Rp{{ number_format(
                                (float) $penggajian->gaji_kotor,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section class="card" style="margin-bottom: 24px;">
    <h2>Potongan</h2>

    <div class="table-wrapper" style="box-shadow: none;">
        <table>
            <tbody>
                <tr>
                    <th>Potongan Alpa</th>

                    <td>
                        Rp{{ number_format(
                            (float) $penggajian->potongan_alpa,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>
                </tr>

                <tr>
                    <th>
                        Potongan Lain

                        @if ($penggajian->catatan_potongan)
                        <div class="muted">
                            {{ $penggajian->catatan_potongan }}
                        </div>
                        @endif
                    </th>

                    <td>
                        Rp{{ number_format(
                            (float) $penggajian->potongan_lain,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>
                </tr>

                <tr>
                    <th>Total Potongan</th>

                    <td>
                        <strong>
                            Rp{{ number_format(
                                (float) $penggajian->total_potongan,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section
    style="
        padding: 24px;
        margin-bottom: 24px;
        border: 1px solid #bbf7d0;
        border-radius: 12px;
        color: #166534;
        background: #dcfce7;
    ">
    <h2 style="margin-top: 0;">Gaji Bersih</h2>

    <strong style="font-size: 32px;">
        Rp{{ number_format(
            (float) $penggajian->gaji_bersih,
            0,
            ',',
            '.'
        ) }}
    </strong>
</section>

<section class="card">
    <h2>Informasi Proses</h2>

    <p>
        Diproses oleh:
        <strong>
            {{ $penggajian->pemroses?->nama ?? 'Sistem' }}
        </strong>
    </p>

    <p>
        Waktu proses:
        <strong>
            {{ $penggajian->diproses_pada?->format(
                'd-m-Y H:i'
            ) ?? '-' }}
        </strong>
    </p>

    @if ($penggajian->difinalisasi_pada !== null)
    <p>
        Difinalisasi oleh:
        <strong>
            {{ $penggajian->pemfinalisasi?->nama ?? 'Sistem' }}
        </strong>
    </p>

    <p>
        Waktu finalisasi:
        <strong>
            {{ $penggajian->difinalisasi_pada->format(
                    'd-m-Y H:i'
                ) }}
        </strong>
    </p>
    @endif

    @if ($penggajian->dibayar_pada !== null)
    <p>
        Ditandai dibayar oleh:
        <strong>
            {{ $penggajian->pembayar?->nama ?? 'Sistem' }}
        </strong>
    </p>

    <p>
        Waktu pembayaran:
        <strong>
            {{ $penggajian->dibayar_pada->format(
                    'd-m-Y H:i'
                ) }}
        </strong>
    </p>
    @endif
</section>