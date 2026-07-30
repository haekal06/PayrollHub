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

$labelStatus = [
'hadir' => 'Hadir',
'sakit' => 'Sakit',
'izin' => 'Izin',
'cuti' => 'Cuti',
'alpa' => 'Alpa',
];
@endphp

<header class="document-header">
    <div class="document-brand">
        <h1>PayrollHub</h1>
        <p>Sistem Pengelolaan Payroll</p>
    </div>

    <div class="document-title">
        <h2>Slip Gaji Pegawai</h2>

        <p>
            Periode
            {{ $namaBulan[$penggajian->bulan] }}
            {{ $penggajian->tahun }}
        </p>
    </div>
</header>

<section class="document-meta">
    <div class="meta-item">
        <span class="meta-label">NIP</span>
        <strong>{{ $penggajian->pegawai->nip }}</strong>
    </div>

    <div class="meta-item">
        <span class="meta-label">Nama</span>
        <strong>{{ $penggajian->pegawai->nama }}</strong>
    </div>

    <div class="meta-item">
        <span class="meta-label">Jabatan</span>
        <span>{{ $penggajian->pegawai->jabatan->nama }}</span>
    </div>

    <div class="meta-item">
        <span class="meta-label">Status</span>

        <span>
            <span class="
                status
                status-{{ $penggajian->status }}
            ">
                {{ ucfirst($penggajian->status) }}
            </span>
        </span>
    </div>

    <div class="meta-item">
        <span class="meta-label">Tanggal Masuk</span>

        <span>
            {{ $penggajian->pegawai
                ->tanggal_masuk
                ->format('d-m-Y') }}
        </span>
    </div>

    <div class="meta-item">
        <span class="meta-label">Nomor Slip</span>

        <span>
            PH-{{ $penggajian->tahun }}-{{
                str_pad(
                    (string) $penggajian->bulan,
                    2,
                    '0',
                    STR_PAD_LEFT
                )
            }}-{{ str_pad(
                (string) $penggajian->id,
                5,
                '0',
                STR_PAD_LEFT
            ) }}
        </span>
    </div>
</section>

<section class="
    document-section
    document-section-avoid
">
    <h3 class="section-title">
        Ringkasan Kehadiran
    </h3>

    <div class="summary-grid">
        <div class="summary-item">
            <span>Hari Kerja</span>
            <strong>{{ $penggajian->jumlah_hari_kerja }}</strong>
        </div>

        <div class="summary-item">
            <span>Hadir</span>
            <strong>{{ $penggajian->jumlah_hadir }}</strong>
        </div>

        <div class="summary-item">
            <span>Sakit</span>
            <strong>{{ $penggajian->jumlah_sakit }}</strong>
        </div>

        <div class="summary-item">
            <span>Izin</span>
            <strong>{{ $penggajian->jumlah_izin }}</strong>
        </div>

        <div class="summary-item">
            <span>Cuti</span>
            <strong>{{ $penggajian->jumlah_cuti }}</strong>
        </div>

        <div class="summary-item">
            <span>Alpa</span>
            <strong>{{ $penggajian->jumlah_alpa }}</strong>
        </div>

        <div class="summary-item">
            <span>Jam Lembur</span>

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

<section class="
    document-section
    document-section-avoid
">
    <div class="finance-grid">
        <div>
            <h3 class="section-title">Pendapatan</h3>

            <table class="money-table">
                <tbody>
                    <tr>
                        <td>Gaji Pokok</td>

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
                        <td>Tunjangan</td>

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
                        <td>Upah Lembur</td>

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
                        <td>
                            Bonus

                            @if ($penggajian->catatan_bonus)
                            <div class="document-note">
                                {{ $penggajian->catatan_bonus }}
                            </div>
                            @endif
                        </td>

                        <td>
                            Rp{{ number_format(
                                (float) $penggajian->bonus,
                                0,
                                ',',
                                '.'
                            ) }}
                        </td>
                    </tr>

                    <tr class="table-total">
                        <td>Gaji Kotor</td>

                        <td>
                            Rp{{ number_format(
                                (float) $penggajian->gaji_kotor,
                                0,
                                ',',
                                '.'
                            ) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div>
            <h3 class="section-title">Potongan</h3>

            <table class="money-table">
                <tbody>
                    <tr>
                        <td>
                            Potongan Alpa
                            ({{ $penggajian->jumlah_alpa }} hari)
                        </td>

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
                        <td>
                            Potongan Lain

                            @if ($penggajian->catatan_potongan)
                            <div class="document-note">
                                {{ $penggajian->catatan_potongan }}
                            </div>
                            @endif
                        </td>

                        <td>
                            Rp{{ number_format(
                                (float) $penggajian->potongan_lain,
                                0,
                                ',',
                                '.'
                            ) }}
                        </td>
                    </tr>

                    <tr class="table-total">
                        <td>Total Potongan</td>

                        <td>
                            Rp{{ number_format(
                                (float) $penggajian->total_potongan,
                                0,
                                ',',
                                '.'
                            ) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="net-salary">
    <span>Gaji Bersih Diterima</span>

    <strong>
        Rp{{ number_format(
            (float) $penggajian->gaji_bersih,
            0,
            ',',
            '.'
        ) }}
    </strong>
</section>

<section class="document-section">
    <h3 class="section-title">
        Detail Absensi Harian
    </h3>

    <table>
        <thead>
            <tr>
                <th style="width: 13%;">Tanggal</th>
                <th style="width: 12%;">Hari</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 11%;">Lembur</th>
                <th style="width: 25%;">
                    Keterangan Lembur
                </th>
                <th>Catatan</th>
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
                            status
                            status-{{ $absensi->status }}
                        ">
                        {{ $labelStatus[$absensi->status] }}
                    </span>
                </td>

                <td class="text-center">
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
                <td colspan="6" class="text-center">
                    Tidak ada data absensi.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</section>

<section class="signature-area">
    <div>
        <p class="document-note">
            Dokumen ini dibuat oleh sistem PayrollHub
            berdasarkan data penggajian yang telah
            difinalisasi oleh Admin HRD.
        </p>

        <p class="document-note">
            Dicetak pada:
            {{ now()->locale('id')->translatedFormat(
                'd F Y, H:i'
            ) }}
        </p>
    </div>

    <div class="signature-box">
        <div>Admin HRD</div>

        <div class="signature-space"></div>

        <div class="signature-line">
            {{ $penggajian->pemfinalisasi?->nama
                ?? $penggajian->pemroses?->nama
                ?? 'Admin HRD' }}
        </div>
    </div>
</section>

<footer class="document-footer">
    PayrollHub — Slip gaji ini bersifat rahasia dan
    hanya ditujukan kepada pegawai terkait.
</footer>