<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ValidateSignedUrl
{
    /**
     * Validate signed URLs against the relative path + query only, so the
     * signature survives scheme/host differences (reverse proxy SSL
     * termination, http vs https, www vs non-www, etc.).offer
     */
    public function handle(Request $request, Closure $next): Response
    {
        $valid = URL::hasValidRelativeSignature($request)
            || URL::hasValidSignature($request)
            || $this->hasValidLegacyAbsoluteSignature($request);

        if (! $valid || ! URL::signatureHasNotExpired($request)) {
            throw new InvalidSignatureException;
        }

        return $next($request);
    }

    /**
     * Links issued before relative signing were signed against the absolute
     * URL. When the app sits behind a proxy, the scheme/host seen by PHP can
     * differ from the one baked into the signature, so retry the common
     * variants (http/https, www/non-www). The HMAC still has to match, so
     * this does not weaken the signature.
     */
    private function hasValidLegacyAbsoluteSignature(Request $request): bool
    {
        $signature = $request->query('signature');

        if (! is_string($signature)) {
            return false;
        }

        $path = $request->getPathInfo();
        $queryString = (string) $request->server->get('QUERY_STRING');
        $host = $request->getHost();

        $schemes = [$request->getScheme(), $request->getScheme() === 'https' ? 'http' : 'https'];
        $hosts = [$host, str_starts_with($host, 'www.') ? substr($host, 4) : 'www.'.$host];

        foreach (array_unique($schemes) as $scheme) {
            foreach (array_unique($hosts) as $candidateHost) {
                $candidate = Request::create($scheme.'://'.$candidateHost.$path.'?'.$queryString, 'GET');

                if (URL::hasValidSignature($candidate)) {
                    return true;
                }
            }
        }

        return false;
    }
}
