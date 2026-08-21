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
        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * API : compteur + dernières notifications (pour le mobile / badge).
     */
    public function apiIndex()
    {
        $user = Auth::user();
        $unread = $user->unreadNotifications;

        $items = $unread->take(10)->map(function ($n) {
            $data = $n->data;
            $data['id'] = $n->id;
            $data['read_at'] = $n->read_at;
            $data['created_at'] = $n->created_at;
            return $data;
        });

        return response()->json([
            'success' => true,
            'count'   => $unread->count(),
            'items'   => $items,
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
