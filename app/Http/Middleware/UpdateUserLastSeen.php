<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UpdateUserLastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            // On convertit en Carbon s'il ne l'est pas
            $lastSeen = $user->last_seen ? Carbon::parse($user->last_seen) : null;
            
            // Mise à jour de la date s'il n'y en a pas ou s'il s'est écoulé plus d'1 minute depuis le dernier enregistrement
            if (!$lastSeen || $lastSeen->diffInMinutes(Carbon::now()) >= 1) {
                // On met à jour sans polluer updated_at
                $user->timestamps = false;
                $user->last_seen = Carbon::now();
                $user->save();
            }
        }

        return $next($request);
    }
}
