<?php
/**
 * @author tmtuan
 * created Date: 29-Mar-21
 */

namespace App\Domains\Acp\Traits;

use App\Services\FCMService;

trait PushNotification
{
    public function pushMessage(string $title, string $notification, array $data)
    {
        $pushNotificationService = new FCMService($data['app']);

        if ( !isset($data['data']) ) return $pushNotificationService->pushNotification($title, $notification, $data['group']);
        else return $pushNotificationService->pushNotification($title, $notification, $data['group'], $data['data']);
    }

//    public function pushMessages(array $deviceTokens, array $notification, array $data)
//    {
//        $pushNotificationService = new PushNotificationService();
//
//        return $pushNotificationService->sendMultiple($deviceTokens, $notification, $data);
//    }
}
