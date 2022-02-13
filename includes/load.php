<?php
include "user.php";
include "sms.php";

add_action('admin_enqueue_scripts', 'i_amir_homey_admin_style');
function i_amir_homey_admin_style() {
    global $parent_file;
    if ($parent_file == 'i_amir_homey') {
        $main_dir = plugin_dir_url(__DIR__);
        wp_enqueue_style('i_amir_homey_admin_style_bootstrap', $main_dir . 'assets/css/bootstrap.rtl.min.css', [], filemtime($main_dir . 'assets/css/bootstrap.rtl.min.css'), 'all');
    }
}
function i_amir_homey_scripts()
{
    wp_enqueue_style('style', get_stylesheet_uri());
    $main_dir = plugin_dir_url(__DIR__);
    wp_enqueue_style('i_amir_homey_style_main', $main_dir . 'assets/css/styles.css', [], time(), 'all');
    wp_enqueue_script('i_amir_homey_scripts_main', $main_dir . 'assets/js/scripts.js', [], time(), true);
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}

add_action('wp_enqueue_scripts', 'i_amir_homey_scripts');

function i_amir_homey_send_notifications( $post_ID, $post) {
    if ($post->post_type == "homey_reservation" /* && get_post_meta($post_ID, "reservation_status",true) == "booked"*/) {
        $owner_id = get_post_meta($post_ID, "listing_owner",true);
        $renter_id = get_post_meta($post_ID, "listing_renter",true);

        foreach ([$owner_id => "sms_pattern_user", $renter_id => "sms_pattern_owner"] as $user_id => $sms_pattern) {
            if ($mobile = i_amir_homey_get_user_meta($user_id, "mobile")) {
                i_amir_homey_sms_send($mobile, ["id" => (string) $post_ID], get_option("i_amir_homey_$sms_pattern"));
            }
        }
    }
}

add_action( 'added_post_meta', 'i_amir_homey_save_post', 10, 4 );
function i_amir_homey_save_post( $meta_id, $post_id, $meta_key, $meta_value )
{
    if (in_array($meta_key, ['listing_owner', 'listing_renter'])) {
        $post = get_post($post_id);
        if ($post->post_type == "homey_reservation") {
            if ($mobile = i_amir_homey_get_user_meta($meta_value, "mobile")) {
                i_amir_homey_sms_send($mobile, ["id" => (string) $post_id], get_option("i_amir_homey_" .($meta_key == "listing_owner" ? "sms_pattern_owner" : "sms_pattern_user_submit")));
            }
            if ($meta_key == "listing_renter") {
                $admin_mobiles = i_amir_homey_admin_mobiles();
                foreach ($admin_mobiles as $admin_mobile) {
                    i_amir_homey_sms_send($admin_mobile, ["id" => (string) $post_id], get_option("i_amir_homey_sms_pattern_admin"));
                }
            }
        }
    }
    if ($meta_key == "reservation_status") {
        if (get_post_meta($post_id, "old_reservation_status",true) != $meta_value) {
            switch ($meta_value) {
                case "booked":
                case "declined":
                    $user_id = get_post_meta($post_id, "listing_renter",true);
                    if ($mobile = i_amir_homey_get_user_meta($user_id, "mobile")) {
                        i_amir_homey_sms_send($mobile, ["id" => (string) $post_id], get_option("i_amir_homey_" .($meta_value == "booked" ? "sms_pattern_user_ok" : "sms_pattern_user_nok")));
                    }
                    update_post_meta($post_id, "old_reservation_status", $meta_value);
                    break;

            }
        }
    }
}

add_action('rest_api_init', function () {
    /*register_rest_route('iamir/homey/v1', '/test', array(
        'methods' => 'POST',
        'callback' => function () {
            $mobiles = [];
            foreach (array_column(get_users(['role' => "administrator"]), "ID") as $admin_id) {
                if ($mobile = i_amir_homey_get_user_meta($admin_id, "mobile")) {
                    $mobiles[] = i_amir_homey_get_user_meta($admin_id, "mobile");
                }
            }
            return [
                "data" => $mobiles
            ];
        },
    ));*/
    register_rest_route('iamir/homey/v1', '/login', array(
        'methods' => 'POST',
        'callback' => function () {
            global $wpdb;
            $mobile = $_POST['mobile'];
            $type = "registering";
            if (!i_amir_homey_validate_mobile($mobile)) {
                return [
                    "data" => [
                        "status" => false,
                        "message" => "شماره موبایل وارد شده معتبر نمی باشد."
                    ]
                ];
            }
            $mUser = i_amir_homey_find_user_by_mobile($mobile);
            if ($mUser) {
                $user_id = $mUser['user_id'];
            } else {
                $user_id = wp_insert_user(
                    array(
                        'first_name' => apply_filters('pre_user_first_name', $mobile),
                        'last_name' => apply_filters('pre_user_last_name', ""),
                        'user_pass' => apply_filters('pre_user_user_pass', $mobile),
                        'user_login' => apply_filters('pre_user_user_login', $mobile),
                        'role' => 'homey_renter'
                    )
                );
                update_user_meta($user_id, 'mobile', $mobile);
                update_user_meta($user_id, 'register_type', $type);
            }
            $send_code_time = i_amir_homey_get_user_meta($user_id, 'send_code_time') ?: date('Y-m-d H:i:s');
            $send_code_time_diff = i_amir_homey_time_diff(date('Y-m-d H:i:s'), $send_code_time, "ss");
            if ($send_code_time_diff[0] > 60 || $send_code_time_diff[0] == 0) {
                $code = i_amir_homey_send_code_mobile($mobile);
                update_user_meta($user_id, 'send_code', $code);
                update_user_meta($user_id, 'mobile', $mobile);
                update_user_meta($user_id, 'send_code_time', date('Y-m-d H:i:s'));
                unset($code);
                if (i_amir_homey_get_user_meta($user_id, "register_type") == "registered") $type = "login";
            } else {
                return [
                    "data" => [
                        "status" => false,
                        "message" => "شما بعد از گذشت " . (60 - $send_code_time_diff[0]) . " ثانیه می توانید کد تایید را مجدد ارسال کنید",

                    ]
                ];
            }
            return [
                "data" => [
                    "status" => true,
                    "message" => i_amir_homey_get_user_meta($user_id, "register_type") == "registered" ? "لطفا کد دریافت شده را وارد کنید." : "لطفا فیلد های زیر را پر کنید.",
                    "type" => $type,
                    "mobile" => $mobile,
                ]
            ];
        },
    ));
    register_rest_route('iamir/homey/v1', '/verify', array(
        'methods' => 'POST',
        'callback' => function () {
            $errors = [];
            $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : null;
            $name = isset($_POST['name']) ? $_POST['name'] : null;
            $family = isset($_POST['family']) ? $_POST['family'] : null;
            $role = isset($_POST['role']) ? $_POST['role'] : null;
            $code = isset($_POST['code']) ? $_POST['code'] : null;
            if (!i_amir_homey_validate_mobile($mobile)) {
                $errors[] = "شماره موبایل وارد شده معتبر نمی باشد.";
            }
            if (!$code) {
                $errors[] = "کد یکبار مصرف را وارد کنید.";
            }
            $mUser = i_amir_homey_find_user_by_mobile($mobile);
            if (count($errors) == 0 && $mUser) {
                $user_id = $mUser['user_id'];
                if (i_amir_homey_get_user_meta($user_id, "register_type") == "registering") {
                    if (!i_amir_homey_validate_string($name)) {
                        $errors[] = "نام وارد شده معتبر نمی باشد.";
                    }
                    if (!i_amir_homey_validate_string($family)) {
                        $errors[] = "نام خانوادگی وارد شده معتبر نمی باشد.";
                    }
                    if (!$role) {
                        $errors[] = "نقش مورد نظر را انتخاب کنید.";
                    } elseif (!in_array($role, ['homey_renter', 'homey_host'])) {
                        $errors[] = "نقش وارد شده معتبر نمی باشد.";
                    }
                }
                if (count($errors) == 0) {
                    if (i_amir_homey_get_user_meta($user_id, "send_code") == $code) {
                        if (i_amir_homey_get_user_meta($user_id, "register_type") == "registering") {
                            wp_update_user([
                                "ID" => $user_id,
                                'first_name' => apply_filters('pre_user_first_name', $name),
                                'last_name' => apply_filters('pre_user_last_name', $family),
                                'user_pass' => apply_filters('pre_user_user_pass', ""),
                                'user_login' => apply_filters('pre_user_user_login', $mobile),
                                'role' => $role
                            ]);
                            update_user_meta($user_id, 'mobile', $mobile);
                            update_user_meta($user_id, 'register_type', "registered");
                        }
                        $user = get_user_by("ID", $user_id);
                        wp_set_current_user( $user_id, $user->user_login );
                        wp_set_auth_cookie( $user_id );
                        do_action( 'wp_login', $user->user_login, $user );
                        return [
                            "data" => [
                                "status" => true,
                                "message" => "در حال انتقال...",
                            ]
                        ];
                    } else {
                        $errors[] = "کد وارد شده معتبر نمی باشد.";
                    }
                }
            }
            return [
                'data' => [
                    'status' => false,
                    'errors' => $errors,
                ],
            ];
        },
    ));
});

add_action('user_profile_update_errors', 'wp_remove_new_user_email_error', 10, 3);
function wp_remove_new_user_email_error($errors, $update, $user)
{
    unset($errors->errors['empty_email']);
}