<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Http\Middleware;

use AbcDaConstrucao\AutorizacaoCliente\Facades\ACL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AclMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            return $next($request);
        }

        /*$methods = ACL::defineMethods($request->route()->methods);
        $route = $request->route()->uri;
        $userId = Auth::guard($guard)->user()->id;
        $validate = ACL::validate($methods, $route, $userId);

        if (!$validate) {
            if ($request->ajax()) {
                return response()->json('Forbidden', 403);
            }

            return redirect('home')->withErrors('Não autorizado.');
        }*/

        return $next($request);
    }
}