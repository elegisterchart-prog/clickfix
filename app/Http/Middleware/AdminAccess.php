<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('home')->with('status', 'ต้องเป็นผู้ดูแลระบบในการเข้าถึงหน้านี้');
        }

        return $next($request);
    }
}
