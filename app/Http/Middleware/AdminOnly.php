<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->is_admin, 403, 'Solo el administrador puede gestionar noticias.');

        return $next($request);
    }
}
