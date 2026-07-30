<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;

final class SimpanAdminRequest extends RequestAdmin
{
    public function rules(): array
    {
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
                'unique:users,email',
            ],

            'password' => [
                'required',
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

            'password.required' =>
            'Password wajib diisi.',

            'password.confirmed' =>
            'Konfirmasi password tidak sesuai.',

            'password.min' =>
            'Password minimal 8 karakter.',
        ];
    }
}
