<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SimpanDataPegawaiRequest;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Services\GeneratorNipPegawai;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class PegawaiController extends Controller
{
    public function index(): View
    {
        $daftarPegawai = Pegawai::query()
            ->with([
                'jabatan',
                'user',
            ])
            ->withCount([
                'absensis',
                'penggajians',
            ])
            ->orderBy('nip')
            ->paginate(10);

        return view(
            'pegawai.index',
            compact('daftarPegawai')
        );
    }

    public function create(): View
    {
        $daftarJabatan = Jabatan::query()
            ->where('aktif', true)
            ->orderBy('nama')
            ->get();

        return view(
            'pegawai.create',
            compact('daftarJabatan')
        );
    }

    public function store(
        SimpanDataPegawaiRequest $request,
        GeneratorNipPegawai $generatorNip
    ): RedirectResponse {
        $pegawai = DB::transaction(
            function () use (
                $request,
                $generatorNip
            ): Pegawai {
                $data = $request->validated();

                $data['nip'] =
                    $generatorNip->berikutnya();

                return Pegawai::query()->create(
                    $data
                );
            }
        );

        return redirect()
            ->route('admin.pegawai.index')
            ->with(
                'success',
                "Pegawai {$pegawai->nip} berhasil ditambahkan."
            );
    }

    public function edit(
        Pegawai $pegawai
    ): View {
        $daftarJabatan = Jabatan::query()
            ->where('aktif', true)
            ->orWhere(
                'id',
                $pegawai->jabatan_id
            )
            ->orderBy('nama')
            ->get();

        return view(
            'pegawai.edit',
            compact(
                'pegawai',
                'daftarJabatan'
            )
        );
    }

    public function update(
        SimpanDataPegawaiRequest $request,
        Pegawai $pegawai
    ): RedirectResponse {
        DB::transaction(
            function () use (
                $request,
                $pegawai
            ): void {
                $pegawai->update(
                    $request->validated()
                );

                /*
                 * Nama akun mengikuti nama pegawai.
                 * Akun otomatis dinonaktifkan ketika
                 * status pegawai bukan aktif.
                 */
                $pegawai->load('user');

                if ($pegawai->user !== null) {
                    $dataAkun = [
                        'nama' => $pegawai->nama,
                    ];

                    if (! $pegawai->masihAktif()) {
                        $dataAkun['aktif'] = false;
                    }

                    $pegawai->user->update(
                        $dataAkun
                    );
                }
            }
        );

        return redirect()
            ->route('admin.pegawai.index')
            ->with(
                'success',
                'Data pegawai berhasil diperbarui.'
            );
    }

    public function destroy(
        Pegawai $pegawai
    ): RedirectResponse {
        if ($pegawai->user_id !== null) {
            return back()->with(
                'error',
                'Pegawai yang memiliki akun login tidak dapat dihapus.'
            );
        }

        if (
            $pegawai->absensis()->exists()
            || $pegawai->penggajians()->exists()
        ) {
            return back()->with(
                'error',
                'Pegawai yang memiliki riwayat absensi atau penggajian tidak dapat dihapus.'
            );
        }

        $nip = $pegawai->nip;

        $pegawai->delete();

        return redirect()
            ->route('admin.pegawai.index')
            ->with(
                'success',
                "Pegawai {$nip} berhasil dihapus."
            );
    }
}
