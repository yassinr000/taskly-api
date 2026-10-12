<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ajoute les en-têtes de sécurité HTTP à toutes les réponses de l'API
 * (succès, erreurs 4xx/5xx, téléchargements de fichiers).
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            // Le navigateur ne doit pas "deviner" le type d'un fichier téléchargé.
            'X-Content-Type-Options' => 'nosniff',
            // L'API n'est jamais affichée dans une iframe.
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'no-referrer',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
            // L'API ne renvoie que du JSON ou des fichiers : aucune ressource active autorisée.
            'Content-Security-Policy' => "default-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'none'",
            'Cross-Origin-Resource-Policy' => 'same-origin',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];

        // HSTS seulement en HTTPS (derrière le proxy Railway, trustProxies le détecte).
        // Pas de includeSubDomains : le domaine railway.app n'est pas à nous.
        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000';
        }

        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        // Ne pas révéler la version de PHP.
        $response->headers->remove('X-Powered-By');
        header_remove('X-Powered-By');

        return $response;
    }
}
