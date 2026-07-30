<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class PastikanPenggunaAktif
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response|RedirectResponse {
        $pengguna = $request->user();

        if (
            $pengguna !== null
            && ! $pengguna->masihAktif()
        ) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' =>
                    'Akun Anda telah dinonaktifkan.',
                ]);
        }

        return $next($request);
    }
}
