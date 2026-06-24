<?php

namespace App\Http\Middleware;

use App\Support\PortugueseTextSanitizer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FixPortugueseEncoding
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$this->shouldFix($response)) {
            return $response;
        }

        $response->setContent(PortugueseTextSanitizer::fixMojibake((string) $response->getContent()));

        return $response;
    }

    private function shouldFix(Response $response): bool
    {
        if (!method_exists($response, 'getContent') || !method_exists($response, 'setContent')) {
            return false;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');

        return $contentType === '' || str_contains($contentType, 'text/html');
    }
}
