<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FebAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('feb_user_id')) {
            return redirect()->route('feb.login');
        }
        return $next($request);
    }
}