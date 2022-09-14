<?php 

function sendSMS($message, $number) {
	$url = 'http://sms.indiatext.in/api/mt/SendSMS?user=drishtee&password=drishtee123$&senderid=MIRICR&channel=Trans&DCS=0&flashsms=0&number='.$number.'&text='.rawurlencode($message).'&route=1';

  // http://sms.indiatext.in/api/mt/SendSMS?user=drishtee&password=drishtee123$&senderid=DRISHT&channel=Trans&DCS=0&flashsms=0&number=7355123279&text=test%20message&route=1
                  $crl = curl_init();
     //s    dd($url);         
                  curl_setopt($crl, CURLOPT_URL, $url);
                  curl_setopt($crl, CURLOPT_FRESH_CONNECT, true);
                  curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
                  $response = curl_exec($crl);
                  // dd($response);
                  if(!$response){
                      // die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
                  }
                  curl_close($crl);
}