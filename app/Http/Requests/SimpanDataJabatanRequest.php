<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Jabatan;
use Illuminate\Validation\Rule;

final class SimpanDataJabatanRequest extends RequestAdmin
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'aktif' => $this->boolean('aktif'),
        ]);
    }

    public function rules(): array
    {
        $jabatan = $this->route('jabatan');

        $idJabatan = $jabatan instanceof Jabatan
            ? $jabatan->id
            : null;

        return [
            'kode' => [
                'required',
                'string',
                'max:20',
                Rule::unique('jabatans', 'kode')
                    ->ignore($idJabatan),
            ],

            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('jabatans', 'nama')
                    ->ignore($idJabatan),
            ],

            'gaji_pokok' => [
                'required',
                'numeric',
                'min:0',
            ],

            'tunjangan' => [
                'required',
                'numeric',
                'min:0',
            ],

            'tarif_lembur_per_jam' => [
                'required',
                'numeric',
                'min:0',
            ],

            'aktif' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required' =>
            'Kode jabatan wajib diisi.',

            'kode.unique' =>
            'Kode jabatan sudah digunakan.',

            'kode.max' =>
            'Kode jabatan maksimal 20 karakter.',

            'nama.required' =>
            'Nama jabatan wajib diisi.',

            'nama.unique' =>
            'Nama jabatan sudah digunakan.',

            'nama.max' =>
            'Nama jabatan maksimal 100 karakter.',

            'gaji_pokok.required' =>
            'Gaji pokok wajib diisi.',

            'gaji_pokok.numeric' =>
            'Gaji pokok harus berupa angka.',

            'gaji_pokok.min' =>
            'Gaji pokok tidak boleh negatif.',

            'tunjangan.required' =>
            'Tunjangan wajib diisi.',

            'tunjangan.numeric' =>
            'Tunjangan harus berupa angka.',

            'tunjangan.min' =>
            'Tunjangan tidak boleh negatif.',

            'tarif_lembur_per_jam.required' =>
            'Tarif lembur wajib diisi.',

            'tarif_lembur_per_jam.numeric' =>
            'Tarif lembur harus berupa angka.',

            'tarif_lembur_per_jam.min' =>
            'Tarif lembur tidak boleh negatif.',
        ];
    }
}
