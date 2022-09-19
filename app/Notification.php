<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model{
	// https://github.com/brozot/Laravel-FCM
	// https://www.youtube.com/watch?v=FHJ5GIsP2_I

	use SoftDeletes;
	
    protected $table = 'notifications';
    protected $fillable = ['dm_id','title','body','icon','device_token','notification_device','click_action','read_at'];
    protected $dates = ['created_at','updated_at','deleted_at'];
     
  //   public static function toSingleDevice($token=null, $title=null,$body=null,$icon,$click_action){
  //   	$result = [];
  //   	$optionBuilder = new OptionsBuilder();
		// $optionBuilder->setTimeToLive(60*20);
		// $notificationBuilder = new PayloadNotificationBuilder($title);
		// $notificationBuilder->setBody($body)
		// 					->setBadge(1)
		// 					->setIcon($icon)
		// 					->setClickAction($click_action)
		// 				    ->setSound('default');
		// $dataBuilder = new PayloadDataBuilder();
		// // $dataBuilder->addData(['a_data' => 'my_data']);
		// $option = $optionBuilder->build();
		// $notification = $notificationBuilder->build();
		// $data = $dataBuilder->build();
		// $token = $token;
		// $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
		// array_push($result,$downstreamResponse->numberSuccess());
		// array_push($result,$downstreamResponse->numberFailure());
		// array_push($result,$downstreamResponse->numberModification());
		// // return Array - you must remove all this tokens in your database
		//  array_push($result,$downstreamResponse->tokensToDelete());
		// // return Array (key : oldToken, value : new token - you must change the token in your database)
		// array_push($result,$downstreamResponse->tokensToModify());
		// // return Array - you should try to resend the message to the tokens in the array
		// array_push($result,$downstreamResponse->tokensToRetry());
		// // return Array (key:token, value:error) - in production you should remove from your database the tokens
		// array_push($result,$downstreamResponse->tokensWithError());

		// return $result;
  //   }

  //   public static function toMultipleDevice($model, $token=null, $title=null,$body=null,$icon,$click_action){
  //   	$result = [];
  //   	$optionBuilder = new OptionsBuilder();
		// $optionBuilder->setTimeToLive(60*20);

		// $notificationBuilder = new PayloadNotificationBuilder($title);
		// $notificationBuilder->setBody($body)
		// 					->setBadge(1)
		// 					->setIcon($icon)
		// 					->setClickAction($click_action)
		// 				    ->setSound('default');
		// $dataBuilder = new PayloadDataBuilder();
		// // $dataBuilder->addData(['a_data' => 'my_data']);
		// $option = $optionBuilder->build();
		// $notification = $notificationBuilder->build();
		// $data = $dataBuilder->build();
		// // You must change it to get your tokens
		// $tokens = $model->pluck('device_token')->toArray();
		// $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);
		// array_push($result,$downstreamResponse->numberSuccess());
		// array_push($result,$downstreamResponse->numberFailure());
		// array_push($result,$downstreamResponse->numberModification());
		// // return Array - you must remove all this tokens in your database
		// array_push($result,$downstreamResponse->tokensToDelete());
		// // return Array (key : oldToken, value : new token - you must change the token in your database)
		// array_push($result,$downstreamResponse->tokensToModify());
		// // return Array - you should try to resend the message to the tokens in the array
		// array_push($result,$downstreamResponse->tokensToRetry());
		// // return Array (key:token, value:error) - in production you should remove from your database the tokens present in this array
		// array_push($result,$downstreamResponse->tokensWithError());
		// return $result;
  //   }

    // public static function numberAlert(){}
}
