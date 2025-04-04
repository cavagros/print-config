<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        // Récupérer toutes les notifications de l'administrateur connecté
        $notifications = auth()->user()->notifications()->paginate(10);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back()->with('success', 'Notification marquée comme lue');
    }

    public function markAllAsRead(Request $request)
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues');
    }
} 