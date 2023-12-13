<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\MAdmin;
use App\Models\SZAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!auth('api')->check()) {
            abort(401);
        }

        $user_oid = auth('api')->user()->_id;
        $admin = MAdmin::find($user_oid);
        $sZAdmin = SZAdmin::find($user_oid);
        if(!$sZAdmin){
            $permissions = $admin['permissions'] ?? [];
            if (!in_array($permission, $permissions)) {
                abort(401, 'No Permission');
            }
        }

        return $next($request);
    }
}
