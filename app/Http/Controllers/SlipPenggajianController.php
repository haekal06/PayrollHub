<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Penggajian;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SlipPenggajianController extends Controller
{
    public function index(
        Request $request
    ): View {
        $pegawai = $this->ambilPegawai(
            $request
        );

        $daftarPenggajian =
            $pegawai->penggajians()
            ->whereIn('status', [
                Penggajian::STATUS_FINAL,
                Penggajian::STATUS_DIBAYAR,
            ])
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->paginate(12);

        return view(
            'slip-penggajian.index',
            compact(
                'pegawai',
                'daftarPenggajian'
            )
        );
    }

    public function show(
        Request $request,
        Penggajian $penggajian
    ): View {
        $pegawai = $this->ambilPegawai(
            $request
        );

        $this->pastikanSlipMilikPegawai(
            $pegawai,
            $penggajian
        );

        $this->muatRelasiPenggajian(
            $penggajian
        );

        $detailAbsensi =
            $this->ambilDetailAbsensi(
                $penggajian
            );

        return view(
            'slip-penggajian.show',
            compact(
                'penggajian',
                'detailAbsensi'
            )
        );
    }

    public function cetak(
        Request $request,
        Penggajian $penggajian
    ): View {
        $pegawai = $this->ambilPegawai(
            $request
        );

        $this->pastikanSlipMilikPegawai(
            $pegawai,
            $penggajian
        );

        $this->muatRelasiPenggajian(
            $penggajian
        );

        $detailAbsensi =
            $this->ambilDetailAbsensi(
                $penggajian
            );

        return view(
            'slip-penggajian.cetak',
            compact(
                'penggajian',
                'detailAbsensi'
            )
        );
    }

    private function ambilPegawai(
        Request $request
    ): Pegawai {
        /** @var User $pengguna */
        $pengguna = $request->user();

        abort_if(
            $pengguna->adalahAdmin(),
            403,
            'Halaman ini hanya untuk pegawai.'
        );

        $pegawai = $pengguna->pegawai;

        abort_if(
            $pegawai === null,
            403,
            'Akun belum terhubung dengan data pegawai.'
        );

        return $pegawai;
    }

    private function pastikanSlipMilikPegawai(
        Pegawai $pegawai,
        Penggajian $penggajian
    ): void {
        abort_unless(
            $penggajian->pegawai_id
                === $pegawai->id
                && in_array(
                    $penggajian->status,
                    [
                        Penggajian::STATUS_FINAL,
                        Penggajian::STATUS_DIBAYAR,
                    ],
                    true
                ),
            404
        );
    }

    private function muatRelasiPenggajian(
        Penggajian $penggajian
    ): void {
        $penggajian->load([
            'pegawai.jabatan',
            'pemroses',
            'pemfinalisasi',
            'pembayar',
        ]);
    }

    private function ambilDetailAbsensi(
        Penggajian $penggajian
    ): Collection {
        return $penggajian
            ->pegawai
            ->absensis()
            ->whereYear(
                'tanggal_absensi',
                $penggajian->tahun
            )
            ->whereMonth(
                'tanggal_absensi',
                $penggajian->bulan
            )
            ->orderBy('tanggal_absensi')
            ->get();
    }
}
