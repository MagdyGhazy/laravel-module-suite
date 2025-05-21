<?php

namespace Ghazym\ModuleBuilder\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Ghazym\ModuleBuilder\Traits\ResponseTrait;
use Ghazym\ModuleBuilder\Traits\HasPermissions;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    use ResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $user = Auth::user();
        
        if (!in_array(HasPermissions::class, class_uses_recursive($user))) {
            return $this->errorResponse('User model must use HasPermissions trait', 500);
        }

        if (!$user->hasPermission($permission)) {
            return $this->errorResponse('Forbidden: Insufficient permissions', 403);
        }

        return $next($request);
    }
} 