<?php

namespace App\helpers\Notifications;

use App\Models\Configration;

class notificationServices
{
    public static function sendOneNotifications($fcm_token, $title, $message, $click=""){
        //this will get from database base on appKey
        $con = Configration::where("key","fcmToken")->first();
        $push_notification_key = "AAAAqDLZOzg:APA91bFXwxKRhIw-z4raLMQNC_g-Jt-umqbB5awF2QrB3D_GeoIbp700EVZ3XrNnhLNn_j7s6VEmc7TScfJ-yAndTWDsJSSItUlRO0GGRcXlrhZ47YqPUMMARxixMMlspbbhfaSKa2Ss";
        $url = "https://fcm.googleapis.com/fcm/send";
        $header = array("authorization: key=" . $con->value['key'] . ",
            content-type: application/json"
        );

        $postdata = '{
        "to" : "' . $fcm_token . '",
        "notification" : {
            "title":"' . $title . '",
            "body":"' . $message . '",
        }
    }';

        $ch = curl_init();
        $timeout = 120;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        // Get URL content
        $result = curl_exec($ch);
        // close handle to release resources
        curl_close($ch);

        return $result;
    }

    public static function sendAllNotifications($fcm_token, $title, $message, $click=""){
        $con = Configration::where("key","fcmToken")->first();
        $push_notification_key = "AAAAqDLZOzg:APA91bFXwxKRhIw-z4raLMQNC_g-Jt-umqbB5awF2QrB3D_GeoIbp700EVZ3XrNnhLNn_j7s6VEmc7TScfJ-yAndTWDsJSSItUlRO0GGRcXlrhZ47YqPUMMARxixMMlspbbhfaSKa2Ss";
        $url = "https://fcm.googleapis.com/fcm/send";

        $data = [
            "registration_ids" => $fcm_token,
            "notification" => [
                "title" => $title,
                "body" => $message,
            ]
        ];
        $encodedData = json_encode($data);

        $headers = [
            'Authorization:key=' . $con->value['key'] ,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);
        // FCM response
        return $result;

    }
    public static function sendAllNotificationsWithAppKey($fcm_token, $title, $message,$appkey, $click=""){
        $con = Configration::where("key","fcmToken")->first();
        $push_notification_key = "AAAAqDLZOzg:APA91bFXwxKRhIw-z4raLMQNC_g-Jt-umqbB5awF2QrB3D_GeoIbp700EVZ3XrNnhLNn_j7s6VEmc7TScfJ-yAndTWDsJSSItUlRO0GGRcXlrhZ47YqPUMMARxixMMlspbbhfaSKa2Ss";
        $url = "https://fcm.googleapis.com/fcm/send";

        $data = [
            "registration_ids" => $fcm_token,
            "notification" => [
                "title" => $title,
                "body" => $message,
            ]
        ];
        $encodedData = json_encode($data);

        $headers = [
            'Authorization:key=' . $con->value['key'] ,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);
        // FCM response
        return $result;

    }
}
