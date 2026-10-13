<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditMiddleware
{
    /**
     * Auditer toutes les requêtes POST/PUT/PATCH/DELETE
     */
    public function handle(Request $request, Closure $next): Response
    {
        $method = $request->method();
        $auditableMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

        if (in_array($method, $auditableMethods)) {
            $response = $next($request);

            // Ne pas auditer les routes d'audit elles-mêmes
            if (!$request->is('admin/audits*')) {
                $this->logRequest($request, $response);
            }

            return $response;
        }

        return $next($request);
    }

    private function logRequest(Request $request, Response $response): void
    {
        $status = $response->getStatusCode();

        if ($status >= 200 && $status < 300) {
            $severity = 'info';
        } elseif ($status >= 400 && $status < 500) {
            $severity = 'warning';
        } elseif ($status >= 500) {
            $severity = 'critical';
        } else {
            return;
        }

        // Skip certaines routes
        $path = $request->path();
        $skip = ['_debugbar', 'telescope', 'horizon', 'login', 'logout'];
        foreach ($skip as $s) {
            if (str_contains($path, $s)) return;
        }

        AuditService::log(
            'http_' . strtolower($request->method()),
            null,
            "{$request->method()} /{$path}",
            null, null,
            [
                'status'     => $status,
                'params'     => $this->sanitizeParams($request->all()),
                'route_name' => $request->route()?->getName(),
            ],
            $severity,
            'http'
        );
    }

    private function sanitizeParams(array $params): array
    {
        $sensitive = ['password', 'password_confirmation', 'token', '_token', 'csrf'];

        foreach ($params as $key => $value) {
            if (in_array(strtolower($key), $sensitive)) {
                $params[$key] = '***';
            } elseif (is_array($value)) {
                $params[$key] = $this->sanitizeParams($value);
            } elseif (is_string($value) && strlen($value) > 200) {
                $params[$key] = substr($value, 0, 200) . '...';
            }
        }

        return $params;
    }
}