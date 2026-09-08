<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Liste web des notifications de l'utilisateur connecté.
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = $user
            ->notifications()
            ->latest()
            ->paginate(20);

        $produitsEnAlerte = [];
        if ($user->tenant_id && $user->peutGererStock()) {
            $prods = \App\Models\Produit::where('tenant_id', $user->tenant_id)
                ->where('actif', true)
                ->get();

            foreach ($prods as $p) {
                $stk = (int) $p->stock;
                $seuil = (int) ($p->seuil_alerte ?? 5);
                if ($stk <= 5 || ($seuil > 0 && $stk <= $seuil)) {
                    $produitsEnAlerte[] = [
                        'id'     => $p->id,
                        'nom'    => $p->nom,
                        'stock'  => $stk,
                        'seuil'  => $seuil,
                    ];
                }
            }
        }

        return view('notifications.index', compact('notifications', 'produitsEnAlerte'));
    }

    /**
     * API : compteur + dernières notifications + alertes de stock (pour le mobile).
     */
    public function apiIndex()
    {
        $user = Auth::user();
        $allNotifs = $user->notifications()->latest()->take(30)->get();
        $unreadCount = $user->unreadNotifications->count();

        $items = $allNotifs->map(function ($n) {
            $data = $n->data;
            $data['id'] = $n->id;
            $data['read_at'] = $n->read_at;
            $data['created_at'] = $n->created_at;
            return $data;
        });

        // Alertes de stock
        $stockAlertes = [];
        if ($user->tenant_id) {
            $prods = \App\Models\Produit::where('tenant_id', $user->tenant_id)
                ->where('actif', true)
                ->get();
            foreach ($prods as $p) {
                $stk = (int) $p->stock;
                $seuil = (int) ($p->seuil_alerte ?? 5);
                if ($stk <= 5 || ($seuil > 0 && $stk <= $seuil)) {
                    $stockAlertes[] = [
                        'id'    => $p->id,
                        'nom'   => $p->nom,
                        'stock' => $stk,
                        'seuil' => $seuil,
                    ];
                }
            }
        }

        return response()->json([
            'success'      => true,
            'count'        => $unreadCount,
            'items'        => $items,
            'stockAlertes' => $stockAlertes,
        ]);
    }

    /**
     * API : marquer une notification comme lue.
     */
    public function apiMarkRead(Request $request, $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * API : marquer toutes les notifications comme lues.
     */
    public function apiMarkAllRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Web : marquer toutes les notifications comme lues.
     */
    public function markAllRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }
}
