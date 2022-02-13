<?php

add_filter( 'user_contactmethods' , 'i_amir_homey_update_contact_methods' , 10 , 1 );

function i_amir_homey_update_contact_methods( $contactmethods ) {

// Add new fields
    $contactmethods['mobile'] = 'موبایل';
    $contactmethods['register_type'] = 'وضعیت ثبت نام';
    return $contactmethods;
}

function i_amir_homey_find_user_by_mobile($mobile) {
    global $wpdb;
    $table = $wpdb->prefix . 'usermeta';
    $query = $wpdb->get_row("SELECT * FROM `{$table}` WHERE `meta_key` LIKE 'mobile' AND `meta_value` LIKE '$mobile' ORDER BY `meta_value` DESC", ARRAY_A);
    return $query;
}

function i_amir_homey_validate_mobile($mobile) {
    if(preg_match("/^09[0-9]{9}$/", $mobile)) {
        return true;
    }else{
        return false;
    }
}

function i_amir_homey_validate_string($string) {
    if (preg_match("/^[ ؀-ۿa-zA-Z]*$/",$string)){
        return true;
    }else{
        return false;
    }
}
function i_amir_homey_send_code_mobile($mobile) {
    $code = rand(12345,65535);
    i_amir_homey_sms_send($mobile, ["code" => (string) $code], get_option("i_amir_homey_sms_pattern_code"));
    return $code;
}
function i_amir_homey_get_user_meta($user_id, $key) {
    return get_the_author_meta($key, $user_id);
}
function i_amir_homey_time_diff($time2,$time1,$type = null){
    $diff = strtotime($time2) - strtotime($time1);
    if($diff < 60 || $type == 'ss'){
        return array($diff," ثانیه");
    }
    elseif($diff < 3600 || $type == 'mm'){
        return array(round($diff / 60,0,1),"دقیقه");
    }
    elseif(($diff >= 3660 && $diff < 86400 ) || $type == 'hh'){
        return array(round($diff / 3600,0,1),"ساعت");
    }
    elseif(($diff > 86400 && $diff < 2592000) || $type == 'dd'){
        return array(round($diff / 86400,0,1),"روز");
    }
    elseif(($diff > 2592000 && $diff < 31536000) || $type == 'mh'){
        return array(round($diff / 2592000,0,1),"ماه");
    }
    elseif($diff > 31536000 || $type == 'yy'){
        return array(round($diff / 31536000,0,1),"سال");
    }
}