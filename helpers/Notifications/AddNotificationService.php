<?php

namespace App\helpers\Notifications;

use App\Models\Notification;
use App\Models\Notify;
use Illuminate\Support\Facades\Log;

class AddNotificationService
{
    public function notificationStore($users, $title, $content)
    {
        $notify = Notification::create([
            "title" => $title,
            "content" => $content,
        ]);

        // التحقق من طبيعة $users قبل استخدام pluck()
        if (is_array($users)) {
            // إذا كانت مصفوفة من الكائنات
            $fcms = array_column($users, 'fcm');
        } else {
            // إذا كان كائنًا من النموذج
            $fcms = $users->pluck('fcm')->toArray();
        }

        $this->attachUser($users, $notify);
        Log::error($fcms);
        notificationServices::sendAllNotifications($fcms, $title, $content);
        return 1;
    }

    private function attachUser($users, $notify)
    {
        foreach ($users as $user){
            Notify::create([
                "user_id"=>$user['id'],
                "notification_id"=>$notify->id,
            ]);
        }
    }
}
