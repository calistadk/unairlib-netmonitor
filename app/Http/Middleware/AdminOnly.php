<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        // GET, HEAD, OPTIONS diizinkan untuk semua user yang sudah login
        if ($request->isMethod('GET') || $request->isMethod('HEAD') || $request->isMethod('OPTIONS')) {
            return $next($request);
        }

        // POST, PUT, PATCH, DELETE hanya untuk admin
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }
            return redirect()->back()->with('error', 'Akses ditolak. Hanya admin yang dapat melakukan tindakan ini.');
        }

        return $next($request);
    }
}