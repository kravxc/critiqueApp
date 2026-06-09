<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('sanctum')->user();
        if ($user->role->name == 'admin') {
            return $next($request);
        }
        return response('Нет прав доступа', Response::HTTP_FORBIDDEN);
    }
}
