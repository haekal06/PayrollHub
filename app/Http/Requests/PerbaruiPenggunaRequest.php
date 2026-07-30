<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class PerbaruiPenggunaRequest extends RequestAdmin
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'aktif' => $this->boolean('aktif'),
        ]);
    }

    public function rules(): array
    {
        $pengguna = $this->route('pengguna');

        return [
            'nama' => [
                'required',
                'string',
                'max:100',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore(
                        $pengguna instanceof User
                            ? $pengguna->id
                            : null
                    ),
            ],

            'aktif' => [
                'required',
                'boolean',
            ],

            'password' => [
                'nullable',
                'confirmed',
                Password::min(8),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' =>
            'Nama pengguna wajib diisi.',

            'nama.max' =>
            'Nama maksimal 100 karakter.',

            'email.required' =>
            'Email wajib diisi.',

            'email.email' =>
            'Format email tidak valid.',

            'email.unique' =>
            'Email sudah digunakan.',

            'aktif.required' =>
            'Status akun wajib dipilih.',

            'password.confirmed' =>
            'Konfirmasi password tidak sesuai.',

            'password.min' =>
            'Password minimal 8 karakter.',
        ];
    }
}
