<?php

namespace App\Http\Middleware;

use App\Support\Diagnostics\RequestDiagnostics;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogRequestDiagnostics
{
    public function __construct(
        protected RequestDiagnostics $diagnostics,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->diagnostics->begin($request);

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $this->diagnostics->fail($request, $exception);

            throw $exception;
        }

        $this->diagnostics->finish($request, $response);

        return $response;
    }
}
