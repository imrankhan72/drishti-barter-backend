<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Notification;
use URL;

class NotificationController extends Controller{
    
    // https://github.com/brozot/Laravel-FCM
    // https://www.youtube.com/watch?v=FHJ5GIsP2_I

    public static function toSingleDevice($dm,$data){
        $results = [];
        
        $noti = new Notification;
        $noti->dm_id = $dm->id;
        $noti->title = $data['title'];
        $noti->body = $data['body'];
        $noti->icon = URL::asset('/icon.png');
        $noti->click_action = null;
        $noti->device_token = $dm->device_token;
        $noti->notification_device = "Single";
        $noti->save();



        $msg = array
              (
            'body'  => $data['body'],
            'title' => $data['title'],
            'type' => $data['type'],
            'id'   => $data['id'], 
            'icon' => 'ic_notification',                       
              );
        $fields = array
            (
                'to'        => $dm->device_token,
                'data'  => $msg,
            );
    
            
        $headers = array
            (
                'Authorization: key= AAAALE6bfO4:APA91bE2firl0h-tDRQUDUkNlTlOAmIgyky9nI7ZRAXKmbyFQajKy5-AXa6bkhWk-RFjuWEDI5umY3gEMLmEAJ1Zi05QwgjBSKzggTf85JDzGzgivOsQ07Bcs-8O813yM5dpLEynPzdl',
                'Content-Type: application/json'
            );
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        array_push($results, $result);
        curl_close( $ch );

        return $results;
    }

    // public static function toMultipleDevice($model, $token=null, $title=null,$body=null,$icon,$click_action){
    //     $result = [];
    //     $optionBuilder = new OptionsBuilder();
    //     $optionBuilder->setTimeToLive(60*20);

    //     $notificationBuilder = new PayloadNotificationBuilder($title);
    //     $notificationBuilder->setBody($body)
    //          ->setBadge(1)
    //          ->setIcon($icon)
    //          ->setClickAction($click_action)
    //          ->setSound('default');
    //     $dataBuilder = new PayloadDataBuilder();
    //     // $dataBuilder->addData(['a_data' => 'my_data']);
    //     $option = $optionBuilder->build();
    //     $notification = $notificationBuilder->build();
    //     $data = $dataBuilder->build();
    //     // You must change it to get your tokens
    //     $tokens = $model->pluck('device_token')->toArray();
    //     $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);
    //     array_push($result,$downstreamResponse->numberSuccess());
    //     array_push($result,$downstreamResponse->numberFailure());
    //     array_push($result,$downstreamResponse->numberModification());
    //     // return Array - you must remove all this tokens in your database
    //     array_push($result,$downstreamResponse->tokensToDelete());
    //     // return Array (key : oldToken, value : new token - you must change the token in your database)
    //     array_push($result,$downstreamResponse->tokensToModify());
    //     // return Array - you should try to resend the message to the tokens in the array
    //     array_push($result,$downstreamResponse->tokensToRetry());
    //     // return Array (key:token, value:error) - in production you should remove from your database the tokens present in this array
    //     array_push($result,$downstreamResponse->tokensWithError());
    //     return $result;
    // }


    public static function getNotificationList($dm_id){
        $notifications = Notification::where("dm_id",$dm_id)->get();
        return response()->json($notifications,200);
    }
    public static function getNotification($dm_id, $notification_id){
        $notification = Notification::find($notification_id);
        return response()->json($notification,200);
    }

}
