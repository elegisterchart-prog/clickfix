<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TechnicianAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (! auth()->check() || auth()->user()->role !== 'technician') {
            return redirect()->route('login')->with('status', 'ต้องเป็นช่างในการเข้าถึงหน้านี้');
        }

        return $next($request);
    }
}
