<?php 
use Log;

function sendSMS($message, $number,$template_id=null) {
	//$url = 'http://sms.indiatext.in/api/mt/SendSMS?user=drishtee&password=drishtee123$&senderid=MIRICR&channel=Trans&DCS=0&flashsms=0&number='.$number.'&text='.rawurlencode($message).'&route=1';

  // http://sms.indiatext.in/api/mt/SendSMS?user=drishtee&password=drishtee123$&senderid=DRISHT&channel=Trans&DCS=0&flashsms=0&number=7355123279&text=test%20message&route=1


  $url = 'http://sms.messageindia.in/v2/sendSMS?username=drishtee&message='.rawurlencode($message).'&sendername=MIRICR&smstype=TRANS&numbers='.$number.'&apikey=f4e36e84-59bf-47d8-968c-c44287e7e5eb&peid=1201161527747662237&templateid='.$template_id;
                  $crl = curl_init();
     //s    dd($url);         
                  curl_setopt($crl, CURLOPT_URL, $url);
                  curl_setopt($crl, CURLOPT_FRESH_CONNECT, true);
                  curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
                  $response = curl_exec($crl);
                  // dd($response);
                  // Log::info($response);
                  // if(!$response){
                  //   Log::info("here");
                  //   // echo "hello";

                  //     // die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
                  // }
                  curl_close($crl);
}
