<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $logMessage = date('Y-m-d H:i:s') . " - AdminMiddleware: Début de la vérification\n";
        $logMessage .= "URL: " . $request->url() . "\n";
        $logMessage .= "User ID: " . (auth()->check() ? auth()->user()->id : 'non connecté') . "\n";
        $logMessage .= "Is Admin: " . (auth()->check() ? (auth()->user()->is_admin ? 'oui' : 'non') : 'non connecté') . "\n";
        
        file_put_contents(storage_path('logs/debug.log'), $logMessage, FILE_APPEND);

        if (!auth()->check()) {
            file_put_contents(storage_path('logs/debug.log'), 
                date('Y-m-d H:i:s') . " - AdminMiddleware: Utilisateur non authentifié\n",
                FILE_APPEND
            );
            return redirect()->route('login');
        }

        if (!auth()->user()->is_admin) {
            file_put_contents(storage_path('logs/debug.log'), 
                date('Y-m-d H:i:s') . " - AdminMiddleware: Utilisateur non admin: " . auth()->user()->id . "\n",
                FILE_APPEND
            );
            abort(403, 'Accès non autorisé.');
        }

        file_put_contents(storage_path('logs/debug.log'), 
            date('Y-m-d H:i:s') . " - AdminMiddleware: Vérification réussie pour l'utilisateur: " . auth()->user()->id . "\n",
            FILE_APPEND
        );

        return $next($request);
    }
}
