<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->string('filter')->toString();
        $query = $request->user()->notifications();
        if ($filter === 'unread') $query->whereNull('read_at');
        if ($filter === 'read') $query->whereNotNull('read_at');

        return view('notifications.index', ['notifications' => $query->latest()->paginate(15)->withQueryString(), 'filter' => $filter]);
    }

    public function read(Request $request, string $notification): JsonResponse|RedirectResponse
    {
        $item = $request->user()->notifications()->findOrFail($notification);
        $item->markAsRead();

        return $request->expectsJson()
            ? response()->json(['success' => true, 'unread_count' => $request->user()->unreadNotifications()->count()])
            : back();
    }

    public function readAll(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return $request->expectsJson() ? response()->json(['success' => true, 'unread_count' => 0]) : back();
    }
}
