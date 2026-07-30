<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;

final class SimpanAkunPegawaiRequest extends RequestAdmin
{
    public function rules(): array
    {
        return [
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
            'email.required' =>
            'Email login wajib diisi.',

            'email.email' =>
            'Format email tidak valid.',

            'email.unique' =>
            'Email sudah digunakan akun lain.',

            'password.required' =>
            'Password awal wajib diisi.',

            'password.confirmed' =>
            'Konfirmasi password tidak sesuai.',

            'password.min' =>
            'Password minimal 8 karakter.',
        ];
    }
}
