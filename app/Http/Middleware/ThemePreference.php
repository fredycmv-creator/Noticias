<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ThemePreference
{
    public function handle(Request $request, Closure $next)
    {
        $themes = config('themes');
        $choice = $request->query('tema');
        if (is_string($choice) && array_key_exists($choice, $themes)) {
            $request->session()->put('blog_theme', $choice);
        }
        $theme = $request->session()->get('blog_theme', 'light');
        if (! is_string($theme) || ! array_key_exists($theme, $themes)) {
            $theme = 'light';
        }
        View::share(['blogTheme' => $theme, 'blogThemes' => $themes]);

        return $next($request);
    }
}
