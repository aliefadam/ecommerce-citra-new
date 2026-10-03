<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocalizeStorefrontUi
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (app()->getLocale() !== 'en' || $request->is('admin', 'admin/*', 'api', 'api/*', 'internal/*')) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type');
        if (! str_contains($contentType, 'text/html') || ! method_exists($response, 'getContent')) {
            return $response;
        }

        $content = $response->getContent();
        $translations = trans('ui');

        if (is_string($content) && is_array($translations)) {
            // The storefront predates Laravel localization. Exact phrase replacement
            // keeps its Blade and inline interaction messages consistent while those
            // templates are incrementally moved to named translation keys.
            $response->setContent(strtr($content, $translations));
        }

        return $response;
    }
}
