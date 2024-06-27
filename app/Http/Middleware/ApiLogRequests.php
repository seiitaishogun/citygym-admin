<?php

namespace App\Http\Middleware;

use App\Models\RequestLog;
use Closure;
use Illuminate\Http\Request;

class ApiLogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $request->start = microtime(true);
        return $next($request);
    }

    public function terminate($request, $response)
    {
        $request->end = microtime(true);

        $this->log($request,$response);
    }

    protected function log($request,$response)
    {
        if (!$request->isMethod('get') && $request->getRequestUri()!='/api/auth/login') {
            $duration = $request->end - $request->start;

            $logRequest = [
                'duration' => $duration,
                'status_code' => $response->getStatusCode(),
                'url' => $request->fullUrl(),
                'method' => $request->getMethod(),
                'ip' => $request->getClientIp(),
                'Request' => json_encode($request->all()),
                'Response' => $response->getContent()
            ];

            RequestLog::create($logRequest);
        }
    }
}
