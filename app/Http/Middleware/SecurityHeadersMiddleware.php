<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityHeadersMiddleware
 *
 * Applies security response headers to every web response.
 * Configured for this app's specific requirements:
 *   - Bootstrap + Bootstrap Icons from cdn.jsdelivr.net
 *   - Chart.js + FullCalendar from cdn.jsdelivr.net
 *   - Alpine.js from cdn.jsdelivr.net
 *   - WhatsApp share links (wa.me / api.whatsapp.com)
 *   - Inline scripts in Blade templates (unsafe-inline required until nonces implemented)
 *   - PWA service worker (same-origin only)
 *   - QR images served via signed controller routes (same-origin)
 *   - Payment page with public signed URLs
 */
class SecurityHeadersMiddleware
{
    /**
     * Paths where CSP should be relaxed (e.g. public payment pages that
     * embed og:image and open external links — still protected but more permissive).
     */
    private const RELAXED_PATHS = ['/pay/', '/pay-login'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Don't apply to binary/download responses where headers may cause issues
        if ($this->isBinaryResponse($response)) {
            return $response;
        }

        $isProduction = app()->environment('production', 'staging');
        $isRelaxed    = $this->isRelaxedPath($request);

        // ── Content-Security-Policy ─────────────────────────────────────────
        // NOTE: 'unsafe-inline' is required for script-src because the app
        // uses inline <script> blocks extensively in Blade templates.
        // Future improvement: implement nonce-based CSP.
        $cdnOrigins = 'https://cdn.jsdelivr.net';
        $csp = implode('; ', array_filter([
            "default-src 'self'",
            // Scripts: self + CDN. unsafe-inline required for Blade inline scripts.
            "script-src 'self' 'unsafe-inline' {$cdnOrigins}",
            // Styles: self + CDN + unsafe-inline (Bootstrap, inline styles)
            "style-src 'self' 'unsafe-inline' {$cdnOrigins}",
            // Images: self + data URIs (avatars, charts) + blob (upload previews)
            "img-src 'self' data: blob:",
            // Fonts: self + CDN (Bootstrap Icons)
            "font-src 'self' {$cdnOrigins}",
            // Connections: self only (no external AJAX)
            "connect-src 'self'",
            // Frames: none (no iframes in this app)
            "frame-src 'none'",
            // Objects/plugins: none
            "object-src 'none'",
            // Workers: self only (PWA service worker)
            "worker-src 'self'",
            // Manifest: self only
            "manifest-src 'self'",
            // Form submissions: self + WhatsApp share links go through anchor not form
            "form-action 'self'",
            // Base URI locked to self (prevents base tag injection)
            "base-uri 'self'",
            // Upgrade insecure requests in production
            $isProduction ? "upgrade-insecure-requests" : null,
        ]));
        $response->headers->set('Content-Security-Policy', $csp);

        // ── X-Frame-Options ──────────────────────────────────────────────────
        // Prevents clickjacking. DENY is stricter than SAMEORIGIN.
        // Payment pages are shared via direct links, not embedded in iframes.
        $response->headers->set('X-Frame-Options', 'DENY');

        // ── X-Content-Type-Options ───────────────────────────────────────────
        // Prevents MIME-type sniffing — browsers must use declared Content-Type.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // ── Referrer-Policy ──────────────────────────────────────────────────
        // Sends origin only when navigating to external sites (not full URL).
        // Prevents leaking payment page signed URLs in Referer headers to
        // external CDNs (Bootstrap loads from Referer-containing requests).
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // ── Permissions-Policy ───────────────────────────────────────────────
        // Disable features this app doesn't use to reduce attack surface.
        // Camera: allowed on same-origin (QR scan via browser camera if added)
        // Geolocation: not used
        $response->headers->set('Permissions-Policy', implode(', ', [
            'camera=(self)',           // allow camera for QR scanning if needed
            'microphone=()',           // not used
            'geolocation=()',          // not used
            'payment=()',              // not used (UPI is QR-based, not Web Payments API)
            'usb=()',                  // not used
            'accelerometer=()',        // not used
            'gyroscope=()',            // not used
            'magnetometer=()',         // not used
        ]));

        // ── Strict-Transport-Security ────────────────────────────────────────
        // Only set on HTTPS (production/staging). Forces browsers to use HTTPS
        // for 1 year. includeSubDomains covers api.* subdomains if they exist.
        if ($isProduction && $request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        // ── X-Powered-By removal ─────────────────────────────────────────────
        // Remove PHP version disclosure (also configured in php.ini via
        // expose_php = Off, but belt-and-suspenders).
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }

    private function isBinaryResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        return str_starts_with($contentType, 'image/')
            || str_starts_with($contentType, 'application/pdf')
            || str_starts_with($contentType, 'application/octet-stream');
    }

    private function isRelaxedPath(Request $request): bool
    {
        foreach (self::RELAXED_PATHS as $path) {
            if (str_starts_with($request->getPathInfo(), $path)) {
                return true;
            }
        }
        return false;
    }
}
