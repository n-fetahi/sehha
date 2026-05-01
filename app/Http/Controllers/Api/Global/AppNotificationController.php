<?php

namespace App\Http\Controllers\Api\Global;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class AppNotificationController extends Controller
{
    // =========================================================
    // 🟢 أولاً: جلب الإشعارات غير المُسلَّمة لمستخدم معين
    // =========================================================
    public function index(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $notifications = AppNotification::forUser($request->user_id)
            ->undelivered()
            ->get(['id', 'title', 'content']);

        return response()->json([
            'status' => 200,
            'data'   => $notifications,
        ]);
    }

    // =========================================================
    // 🟢 ثانياً: تعليم إشعار معين كمُسلَّم (is_delivered = true)
    // =========================================================
    public function markDelivered($notification_id)
    {
        $notification = AppNotification::find($notification_id);

        if (!$notification) {
            return response()->json([
                'status'  => 404,
                'message' => 'الإشعار غير موجود',
            ], 404);
        }

        $notification->markAsDelivered();

        return response()->json([
            'status'  => 200,
            'message' => 'تم تحديث حالة الإشعار بنجاح',
        ]);
    }
}
