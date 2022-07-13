<?php

namespace AbcDaConstrucao\AutenticacaoPackage\Http\Middleware;

use AbcDaConstrucao\AutenticacaoPackage\Facades\ACL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::guard($guard ?? ACL::getGuard($request))->user();
        $authCheck = !empty($user);
        $currentRoute = ACL::normalizeRouteByRequest($request);

        if (empty($currentRoute)) {
            return $this->notFound($request);
        }

        foreach (ACL::getMapRoutes() as $mapRoute) {
            if (ACL::routeMethodsToString($currentRoute->methods) == $mapRoute->method
                && ACL::compareUriElements($currentRoute->uri, $mapRoute->uri)) {
                if ($authCheck && ACL::isAuthSagJwtDriver($request) && ACL::validate($mapRoute, $user)) {
                    return $response;
                }

                $appIsActive = ACL::appIsActive();

                if ($authCheck && !ACL::isAuthSagJwtDriver($request) && $appIsActive) {
                    return $response;
                }

                if (!$authCheck && ACL::isAuthSagJwtDriver($request) && !$mapRoute->public) {
                    return $this->unauthorized($request);
                }

                if ($mapRoute->public && $appIsActive) {
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
        return $this->responseHandle($request, 403, 'Ação não autorizada.');
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    protected function unauthorized(Request $request)
    {
        return $this->responseHandle($request, 401, 'Usuário não autenticado.');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    protected function notFound(Request $request)
    {
        return $this->responseHandle($request, 404, 'Url inválida.');
    }

    /**
     * @param Request $request
     * @param int $statusCode
     * @param string $msg
     * @return mixed
     */
    protected function responseHandle(Request $request, int $statusCode, string $msg)
    {
        if (!$request->hasSession() && $request->acceptsJson()) {
            return response()->json(['code' => $statusCode, 'message' => $msg], $statusCode);
        } elseif (($request->hasSession() && $this->isLumen()) && $request->acceptsJson()) {
            return response()->json(['code' => $statusCode, 'message' => $msg], $statusCode);
        }

        return abort($statusCode, $msg);
    }

    /**
     * @return bool
     */
    protected function isLumen()
    {
        return class_exists('Laravel\Lumen\Application');
    }
}
