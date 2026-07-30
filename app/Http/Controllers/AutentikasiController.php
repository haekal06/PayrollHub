<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class AutentikasiController extends Controller
{
    public function tampilkanLogin(): View
    {
        return view('auth.login');
    }

    public function autentikasi(
        Request $request
    ): RedirectResponse {
        $data = $request->validate(
            [
                'email' => [
                    'required',
                    'email',
                ],

                'password' => [
                    'required',
                    'string',
                ],
            ],
            [
                'email.required' =>
                'Email wajib diisi.',

                'email.email' =>
                'Format email tidak valid.',

                'password.required' =>
                'Password wajib diisi.',
            ]
        );

        $kredensial = [
            'email' => $data['email'],
            'password' => $data['password'],
            'aktif' => true,
        ];

        $ingatSaya =
            $request->boolean('ingat_saya');

        if (
            ! Auth::attempt(
                $kredensial,
                $ingatSaya
            )
        ) {
            return back()
                ->withErrors([
                    'email' =>
                    'Email, password, atau status akun tidak sesuai.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function keluar(
        Request $request
    ): RedirectResponse {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
