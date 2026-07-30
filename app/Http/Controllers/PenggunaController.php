<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PerbaruiPenggunaRequest;
use App\Http\Requests\SimpanAdminRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class PenggunaController extends Controller
{
    public function index(): View
    {
        $daftarPengguna = User::query()
            ->with([
                'pegawai',
                'pembuat',
            ])
            ->orderBy('peran')
            ->orderBy('nama')
            ->paginate(10);

        return view(
            'pengguna.index',
            compact('daftarPengguna')
        );
    }

    public function create(): View
    {
        return view('pengguna.create');
    }

    public function store(
        SimpanAdminRequest $request
    ): RedirectResponse {
        User::query()->create([
            'nama' =>
            $request->validated('nama'),

            'email' =>
            $request->validated('email'),

            'password' =>
            $request->validated('password'),

            'peran' =>
            User::PERAN_ADMIN,

            'aktif' => true,

            'dibuat_oleh' =>
            Auth::id(),
        ]);

        return redirect()
            ->route('admin.pengguna.index')
            ->with(
                'success',
                'Akun Admin HRD berhasil dibuat.'
            );
    }

    public function edit(
        User $pengguna
    ): View {
        $pengguna->load('pegawai');

        return view(
            'pengguna.edit',
            compact('pengguna')
        );
    }

    public function update(
        PerbaruiPenggunaRequest $request,
        User $pengguna
    ): RedirectResponse {
        $data = $request->validated();

        $akanDinonaktifkan =
            ! (bool) $data['aktif'];

        if (
            $pengguna->is(Auth::user())
            && $akanDinonaktifkan
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Anda tidak dapat menonaktifkan akun yang sedang digunakan.'
                );
        }

        if (
            $pengguna->adalahAdmin()
            && $pengguna->masihAktif()
            && $akanDinonaktifkan
            && User::query()
            ->where(
                'peran',
                User::PERAN_ADMIN
            )
            ->where('aktif', true)
            ->count() <= 1
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Admin aktif terakhir tidak dapat dinonaktifkan.'
                );
        }

        $dataPerbarui = [
            'nama' => $data['nama'],
            'email' => $data['email'],
            'aktif' => $data['aktif'],
        ];

        if (! empty($data['password'])) {
            $dataPerbarui['password'] =
                $data['password'];
        }

        $pengguna->update($dataPerbarui);

        return redirect()
            ->route('admin.pengguna.index')
            ->with(
                'success',
                'Akun pengguna berhasil diperbarui.'
            );
    }
}
