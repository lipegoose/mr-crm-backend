<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Obter domínios permitidos do .env
        $allowedOrigins = explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173,https://bhelite.mrcrm.com.br'));
        $allowedOrigins = array_map('trim', $allowedOrigins);

        // Verificar se o Origin do request está na whitelist
        $origin = $request->header('Origin');
        $allowedOrigin = in_array($origin, $allowedOrigins) ? $origin : null;

        // Configurar headers CORS
        $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');

        // Se for uma requisição OPTIONS, retornar apenas os headers
        if ($request->isMethod('OPTIONS')) {
            $response->setStatusCode(200);
            $response->setContent('');
        }

        return $response;
    }
} 