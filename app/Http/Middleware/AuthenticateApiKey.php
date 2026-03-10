<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'error'   => 'Unauthenticated.',
                'message' => 'Provide your API key as a Bearer token in the Authorization header.',
            ], 401);
        }

        $plaintext = substr($header, 7);
        $apiKey    = ApiKey::findByPlaintext($plaintext);

        if (!$apiKey) {
            return response()->json([
                'error'   => 'Invalid API key.',
                'message' => 'The provided key does not exist or has been revoked.',
            ], 401);
        }

        // Stamp last used
        $apiKey->update(['last_used_at' => now()]);

        // Attach project and key to request for use in API controllers
        $request->merge(['_api_project' => $apiKey->project]);
        $request->merge(['_api_key'     => $apiKey]);

        return $next($request);
    }
}
