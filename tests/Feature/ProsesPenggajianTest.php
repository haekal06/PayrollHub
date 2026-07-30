<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Penggajian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

final class ProsesPenggajianTest extends TestCase
{
    use RefreshDatabase;
    use MembuatDataUji;

    public function test_alur_penggajian_dari_draf_sampai_dibayar(): void
    {
        $admin = $this->buatAdmin();
        $jabatan = $this->buatJabatan();
        $pegawai = $this->buatPegawai($jabatan);

        $tanggalHariKerja =
            $this->buatKalenderBulan(
                $admin,
                7,
                2026
            );

        $tanggalLembur =
            $tanggalHariKerja[0];

        $tanggalAlpa =
            $tanggalHariKerja[1];

        $this->buatAbsensiLengkap(
            pegawai: $pegawai,
            admin: $admin,

            tanggalHariKerja: $tanggalHariKerja,

            statusTanggal: [
                $tanggalAlpa =>
                Absensi::STATUS_ALPA,
            ],

            lemburTanggal: [
                $tanggalLembur => 4,
            ]
        );

        $responseProses = $this
            ->actingAs($admin)
            ->post(
                route(
                    'admin.penggajian.store'
                ),
                [
                    'pegawai_id' =>
                    $pegawai->id,

                    'bulan' => 7,
                    'tahun' => 2026,

                    'bonus' => 100000,

                    'catatan_bonus' =>
                    'Bonus kinerja',

                    'potongan_lain' =>
                    50000,

                    'catatan_potongan' =>
                    'Potongan koperasi',
                ]
            );

        $penggajian = Penggajian::query()
            ->firstOrFail();

        $responseProses->assertRedirect(
            route(
                'admin.penggajian.show',
                $penggajian
            )
        );

        $this->assertSame(
            Penggajian::STATUS_DRAF,
            $penggajian->status
        );

        $this->assertSame(
            '4.00',
            $penggajian->jam_lembur
        );

        $this->assertSame(
            '100000.00',
            $penggajian->upah_lembur
        );

        $this->assertSame(
            1,
            $penggajian->jumlah_alpa
        );

        $this->assertGreaterThan(
            0,
            (float) $penggajian->gaji_bersih
        );

        $responseFinal = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.penggajian.finalisasi',
                    $penggajian
                )
            );

        $responseFinal->assertSessionHas('success');

        $penggajian->refresh();

        $this->assertSame(
            Penggajian::STATUS_FINAL,
            $penggajian->status
        );

        $this->assertNotNull(
            $penggajian->difinalisasi_pada
        );

        /*
         * Absensi harus terkunci setelah final.
         */
        $absensi = Absensi::query()
            ->whereDate(
                'tanggal_absensi',
                $tanggalLembur
            )
            ->firstOrFail();

        $responseUbahTerkunci = $this
            ->actingAs($admin)
            ->put(
                route(
                    'admin.absensi.update',
                    $absensi
                ),
                [
                    'pegawai_id' =>
                    $pegawai->id,

                    'tanggal_absensi' =>
                    $tanggalLembur,

                    'status' =>
                    Absensi::STATUS_SAKIT,

                    'jam_lembur' => 0,
                    'catatan_lembur' => null,
                    'catatan' => 'Perbaikan',
                ]
            );

        $responseUbahTerkunci
            ->assertSessionHas('error');

        $this->assertSame(
            Absensi::STATUS_HADIR,
            $absensi->fresh()->status
        );

        /*
         * Buka revisi agar absensi dapat diperbaiki.
         */
        $responseRevisi = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.penggajian.buka-revisi',
                    $penggajian
                ),
                [
                    'alasan_revisi' =>
                    'Terdapat kesalahan pencatatan absensi pegawai.',
                ]
            );

        $responseRevisi->assertSessionHas('success');

        $this->assertSame(
            Penggajian::STATUS_REVISI,
            $penggajian->fresh()->status
        );

        $responseUbah = $this
            ->actingAs($admin)
            ->put(
                route(
                    'admin.absensi.update',
                    $absensi
                ),
                [
                    'pegawai_id' =>
                    $pegawai->id,

                    'tanggal_absensi' =>
                    $tanggalLembur,

                    'status' =>
                    Absensi::STATUS_HADIR,

                    'jam_lembur' => 2,

                    'catatan_lembur' =>
                    'Lembur dikoreksi',

                    'catatan' =>
                    'Data diperbaiki',
                ]
            );

        $responseUbah->assertSessionHas('success');

        /*
         * Finalisasi kembali menggunakan data terbaru.
         */
        $responseFinalUlang = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.penggajian.finalisasi',
                    $penggajian
                )
            );

        $responseFinalUlang
            ->assertSessionHas('success');

        $penggajian->refresh();

        $this->assertSame(
            Penggajian::STATUS_FINAL,
            $penggajian->status
        );

        $this->assertSame(
            '2.00',
            $penggajian->jam_lembur
        );

        $responseDibayar = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.penggajian.dibayar',
                    $penggajian
                ),
                [
                    'catatan_pembayaran' =>
                    'Transfer bank',
                ]
            );

        $responseDibayar->assertSessionHas('success');

        $penggajian->refresh();

        $this->assertSame(
            Penggajian::STATUS_DIBAYAR,
            $penggajian->status
        );

        $this->assertNotNull(
            $penggajian->dibayar_pada
        );

        $this->assertGreaterThanOrEqual(
            4,
            $penggajian
                ->riwayatStatus()
                ->count()
        );
    }
}
