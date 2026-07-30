<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PastikanPenggunaAdmin
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        if (
            ! $request->user()?->adalahAdmin()
        ) {
            abort(
                403,
                'Halaman ini hanya dapat diakses oleh Admin HRD.'
            );
        }

        return $next($request);
    }
}
