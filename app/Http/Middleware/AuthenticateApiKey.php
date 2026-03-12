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

        // Stripe uses Basic auth — key as username
        if (!$header) {
            $header = $request->header('Authorization');
        }

        // Support both Bearer and Basic (Stripe style)
        $plaintext = null;

        if ($header && str_starts_with($header, 'Bearer ')) {
            $plaintext = substr($header, 7);
        } elseif ($header && str_starts_with($header, 'Basic ')) {
            $decoded   = base64_decode(substr($header, 6));
            $plaintext = explode(':', $decoded)[0]; // username is the key
        }

        if (!$plaintext) {
            return response()->json([
                'status'  => false,
                'message' => 'No API key provided. Pass your key as a Bearer token.',
            ], 401);
        }

        $apiKey = ApiKey::findByPlaintext($plaintext);

        if (!$apiKey) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or revoked API key.',
            ], 401);
        }

        // Validate provider match from URL
        $urlProvider = $this->detectProviderFromUrl($request->path());

        if ($urlProvider && $apiKey->project->provider !== $urlProvider) {
            $correctBase = $apiKey->project->providerBaseUrl();
            return response()->json([
                'status'  => false,
                'message' => "This key belongs to a {$apiKey->project->providerLabel()} project. "
                    . "Use {$correctBase} as your base URL.",
            ], 401);
        }

        $apiKey->update(['last_used_at' => now()]);

        $request->merge([
            '_api_project' => $apiKey->project,
            '_api_key'     => $apiKey,
        ]);

        return $next($request);
    }

    private function detectProviderFromUrl(string $path): ?string
    {
        if (str_contains($path, 'paystack'))    return 'paystack';
        if (str_contains($path, 'flutterwave')) return 'flutterwave';
        if (str_contains($path, 'stripe'))      return 'stripe';
        return null;
    }
}
