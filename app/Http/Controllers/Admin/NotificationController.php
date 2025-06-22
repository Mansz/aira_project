<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display all notifications
     */
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(20);
        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return redirect()->back()->with('success', 'All notifications marked as read');
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json(['message' => 'Notification deleted']);
    }

    /**
     * Delete all notifications
     */
    public function destroyAll()
    {
        auth()->user()->notifications()->delete();
        return redirect()->back()->with('success', 'All notifications cleared');
    }
}