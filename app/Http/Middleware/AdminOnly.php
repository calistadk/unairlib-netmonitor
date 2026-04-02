<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            // Kalau request AJAX/form POST, redirect balik dengan pesan error
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }
            return redirect()->back()->with('error', 'Akses ditolak. Hanya admin yang dapat melakukan tindakan ini.');
        }

        return $next($request);
    }
}