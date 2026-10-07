<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return [
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $user->notifications()->latest()->limit(20)->get()
                ->map(fn ($n) => [
                    'id' => $n->id,
                    'data' => $n->data,
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at,
                ]),
        ];
    }

    public function read(Request $request, string $id)
    {
        // 🔒 On ne cherche que dans les notifications de l'utilisateur connecté.
        $request->user()->notifications()->findOrFail($id)->markAsRead();
        return response()->noContent();
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->noContent();
    }
}
