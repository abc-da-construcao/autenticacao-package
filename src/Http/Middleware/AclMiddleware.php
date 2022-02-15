<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Http\Middleware;

use AbcDaConstrucao\AutorizacaoCliente\Facades\ACL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class AclMiddleware
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, $guard = null)
    {
        $response = $next($request);
        $currentRoute = ACL::normalizeRouteByRequest($request);

        foreach (ACL::getMapRoutes() as $mapRoute) {
            if (ACL::routeMethodsToString($currentRoute->methods) == $mapRoute->method && $currentRoute->uri == $mapRoute->uri) {
                if (Auth::guard($guard)->check() && ACL::validate($mapRoute, Auth::guard($guard)->user())) {
                    return $response;
                } elseif (!Auth::guard($guard)->check() && !$mapRoute->public) {
                    return $this->unauthorized($request);
                } elseif ($mapRoute->public && ACL::appIsActive()) {
                    return $response;
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
            return response()->json(['message' => 'Ação não autorizada.'], 403);
        }

        $sessionKey = Config::get('abc_autorizacao.acl_session_error');

        if ($request->hasSession() && $request->url() != $request->session()->previousUrl()) {
            return back()->with($sessionKey, 'Ação não autorizada.');
        } else {
            return redirect('/')->with($sessionKey, 'Ação não autorizada.');
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    private function unauthorized(Request $request)
    {
        if ($request->acceptsJson() || $request->ajax()) {
            return response()->json(['message' => 'Usuário não autenticado.'], 401);
        }

        $sessionKey = Config::get('abc_autorizacao.acl_session_error');

        if ($request->hasSession() && $request->url() != $request->session()->previousUrl()) {
            return back()->with($sessionKey, 'Usuário não autenticado.');
        } else {
            return redirect('/')->with($sessionKey, 'Usuário não autenticado.');
        }
    }
}
