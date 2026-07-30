<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SimpanAkunPegawaiRequest;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class AkunPegawaiController extends Controller
{
    public function create(
        Pegawai $pegawai
    ): View|RedirectResponse {
        if ($pegawai->user_id !== null) {
            return redirect()
                ->route(
                    'admin.pengguna.edit',
                    $pegawai->user_id
                )
                ->with(
                    'error',
                    'Pegawai ini sudah memiliki akun login.'
                );
        }

        if (! $pegawai->masihAktif()) {
            return redirect()
                ->route('admin.pegawai.index')
                ->with(
                    'error',
                    'Akun hanya dapat dibuat untuk pegawai aktif.'
                );
        }

        $pegawai->load('jabatan');

        return view(
            'pegawai.akun-create',
            compact('pegawai')
        );
    }

    public function store(
        SimpanAkunPegawaiRequest $request,
        Pegawai $pegawai
    ): RedirectResponse {
        if (! $pegawai->masihAktif()) {
            return redirect()
                ->route('admin.pegawai.index')
                ->with(
                    'error',
                    'Akun hanya dapat dibuat untuk pegawai aktif.'
                );
        }

        DB::transaction(
            function () use (
                $request,
                $pegawai
            ): void {
                $pegawaiTerkunci =
                    Pegawai::query()
                    ->lockForUpdate()
                    ->findOrFail($pegawai->id);

                if (
                    $pegawaiTerkunci->user_id
                    !== null
                ) {
                    throw ValidationException::withMessages([
                        'email' =>
                        'Pegawai ini sudah memiliki akun login.',
                    ]);
                }

                $pengguna = User::query()->create([
                    'nama' =>
                    $pegawaiTerkunci->nama,

                    'email' =>
                    $request->validated('email'),

                    'password' =>
                    $request->validated('password'),

                    'peran' =>
                    User::PERAN_PEGAWAI,

                    'aktif' => true,

                    'dibuat_oleh' =>
                    Auth::id(),
                ]);

                $pegawaiTerkunci->update([
                    'user_id' => $pengguna->id,
                ]);
            }
        );

        return redirect()
            ->route('admin.pegawai.index')
            ->with(
                'success',
                'Akun login pegawai berhasil dibuat.'
            );
    }
}
