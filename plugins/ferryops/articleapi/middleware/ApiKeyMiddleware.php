<?php namespace Ferryops\ArticleAPI\Middleware;

use Closure;
use Response;

class ApiKeyMiddleware
{
    public function handle($request, Closure $next)
    {
        $apiKey = $request->header('X-Api-Key');

        $validApiKey = config('ferryops.articleapi::api_key');

        if (empty($apiKey) || $apiKey !== $validApiKey) {
            return Response::json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing API key'
            ], 401);
        }

        return $next($request);
    }
}
