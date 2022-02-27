<?php

namespace AbcDaConstrucao\AutenticacaoPackage\Http\Middleware;

use AbcDaConstrucao\AutenticacaoPackage\Facades\ACL;
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

        if (empty($currentRoute)) {
            return $this->notFound($request);
        }

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
    protected function forbidden(Request $request)
    {
        if (!$request->hasSession() && ($request->ajax() || $request->acceptsJson())) {
            return response()->json(['message' => 'Ação não autorizada.'], 403);
        }

        $sessionKey = Config::get('auth_abc.session.acl_error');

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
    protected function unauthorized(Request $request)
    {
        if (!$request->hasSession() && ($request->ajax() || $request->acceptsJson())) {
            return response()->json(['message' => 'Usuário não autenticado.'], 401);
        }

        $sessionKey = Config::get('auth_abc.session.acl_error');

        if ($request->hasSession() && $request->url() != $request->session()->previousUrl()) {
            return back()->with($sessionKey, 'Usuário não autenticado.');
        } else {
            return redirect('/')->with($sessionKey, 'Usuário não autenticado.');
        }
    }

    protected function notFound(Request $request)
    {
        if (!$request->hasSession() && ($request->ajax() || $request->acceptsJson())) {
            return response()->json(['message' => 'Url inválida.'], 404);
        }

        $sessionKey = Config::get('auth_abc.session.acl_error');

        if ($request->hasSession() && $request->url() != $request->session()->previousUrl()) {
            return back()->with($sessionKey, 'Url inválida.');
        } else {
            return redirect('/')->with($sessionKey, 'Url inválida.');
        }
    }
}
