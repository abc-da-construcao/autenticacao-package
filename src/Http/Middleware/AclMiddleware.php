<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Http\Middleware;

use AbcDaConstrucao\AutorizacaoCliente\Facades\ACL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

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
		//dd($request->ajax());
		$currentRoute = Route::getRoutes()->match($request);
		foreach (ACL::getMapRoutes() as $mapRoute) {
            if (ACL::methodsToString($currentRoute->methods) == $mapRoute->methods &&
                $currentRoute->uri == $mapRoute->uri
            ) {
                if ($mapRoute->public) {
                    dd('aqui');
                    return $next($request);
                }

                if (Auth::guard($guard)->guest()) {
                    return $this->forbidden($request);
                }
            }
        }

        /* $userId = Auth::guard($guard)->user()->id;
        $validate = ACL::validate($methods, $route, $userId); */

        return $next($request);
    }

    private function forbidden(Request $request)
    {
        if ($request->ajax()) {
            return response()->json('Forbidden', 403);
        }

        $sessionKey = Config::get('autorizacao_abc.acl_session_error');

        if ($request->hasSession() && $request->url() != $request->session()->previousUrl()) {
            return back()->with($sessionKey, 'Não autorizado.');
        } else {
            return redirect('/')->with($sessionKey, 'Não autorizado.');
        }
    }
}
