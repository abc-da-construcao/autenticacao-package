<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Http\Middleware;

use AbcDaConstrucao\AutorizacaoCliente\Facades\ACL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

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
        if (!Auth::guard($guard)->check()) {
            return $this->unauthorized($request);
        }

        $currentRoute = ACL::normalizeRouteByRequest($request);

        foreach (ACL::getMapRoutes() as $mapRoute) {
            if (ACL::routeMethodsToString($currentRoute->methods) == $mapRoute->method
                && $currentRoute->uri == $mapRoute->uri
            ) {
                if ($mapRoute->public) {
                    return $next($request);
                }

                if (ACL::validate($mapRoute->method, $mapRoute->uri, Auth::guard($guard)->user())) {
                    return $next($request);
                }
            }
        }

        return $this->forbidden($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    private function forbidden(Request $request)
    {
        if ($request->acceptsJson() || $request->ajax()) {
            return response()->json('Forbidden', 403);
        }

        $sessionKey = Config::get('abc_autorizacao.acl_session_error');

        if ($request->hasSession() && $request->url() != $request->session()->previousUrl()) {
            return back()->with($sessionKey, 'Não autorizado.');
        } else {
            return redirect('/')->with($sessionKey, 'Não autorizado.');
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    private function unauthorized(Request $request)
    {
        if ($request->acceptsJson() || $request->ajax()) {
            return response()->json('Unauthorized', 401);
        }

        $sessionKey = Config::get('abc_autorizacao.acl_session_error');

        if ($request->hasSession() && $request->url() != $request->session()->previousUrl()) {
            return back()->with($sessionKey, 'Usuário não autenticado.');
        } else {
            return redirect('/')->with($sessionKey, 'Usuário não autenticado.');
        }
    }
}
