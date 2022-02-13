<?php

function i_amir_homey_sms_curl_post($url, $params, $apikey)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: AccessKey ' . $apikey,
        'Accept: application/json',
        'Content-Type: application/json'
    ));
    $res = curl_exec($ch);
    curl_close($ch);

    return $res;
}

function i_amir_homey_sms_send($n, $values, $pattern_code)
{
    if(substr($n, 0, 1)=='+'){
        $n = substr($n,1);
    }
    if(substr($n, 0, 2)=='00'){
        $n = substr($n,2);
    }
    if(substr($n, 0, 1)=='0'){
        $n = substr($n,1);
    }
    $params = array(
        'pattern_code' => $pattern_code,
        'originator' => (string) "+98" . get_option("i_amir_homey_sms_sender"),
        'recipient' => (string) "+98" . $n,
        'values' => $values
    );
    $send = i_amir_homey_sms_curl_post(get_option("i_amir_homey_sms_url"), $params, get_option("i_amir_homey_sms_api_key"));
    return true;
}