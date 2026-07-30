<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SimpanDataJabatanRequest;
use App\Models\Jabatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class JabatanController extends Controller
{
    public function index(): View
    {
        $daftarJabatan = Jabatan::query()
            ->withCount('pegawais')
            ->orderBy('kode')
            ->paginate(10);

        return view(
            'jabatan.index',
            compact('daftarJabatan')
        );
    }

    public function create(): View
    {
        return view('jabatan.create');
    }

    public function store(
        SimpanDataJabatanRequest $request
    ): RedirectResponse {
        Jabatan::query()->create(
            $request->validated()
        );

        return redirect()
            ->route('admin.jabatan.index')
            ->with(
                'success',
                'Jabatan berhasil ditambahkan.'
            );
    }

    public function edit(
        Jabatan $jabatan
    ): View {
        return view(
            'jabatan.edit',
            compact('jabatan')
        );
    }

    public function update(
        SimpanDataJabatanRequest $request,
        Jabatan $jabatan
    ): RedirectResponse {
        $jabatan->update(
            $request->validated()
        );

        return redirect()
            ->route('admin.jabatan.index')
            ->with(
                'success',
                'Jabatan berhasil diperbarui.'
            );
    }

    public function destroy(
        Jabatan $jabatan
    ): RedirectResponse {
        if ($jabatan->pegawais()->exists()) {
            return back()->with(
                'error',
                'Jabatan tidak dapat dihapus karena masih digunakan oleh pegawai.'
            );
        }

        $jabatan->delete();

        return redirect()
            ->route('admin.jabatan.index')
            ->with(
                'success',
                'Jabatan berhasil dihapus.'
            );
    }
}
