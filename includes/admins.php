<?php


function i_amir_homey_menu() {
    add_menu_page("تنظیمات پیامک", "تنظیمات پیامک","manage_options", "i_amir_homey", "i_amir_homey_setting");
}


add_action("admin_menu", "i_amir_homey_menu");

function i_amir_homey_setting(){

    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    if ($_POST){
        foreach (['sms_sender', 'sms_url', 'sms_api_key', 'sms_pattern_code', 'sms_pattern_user_submit', 'sms_pattern_user_ok', 'sms_pattern_user_nok', 'sms_pattern_admin', 'sms_pattern_owner'] as $item) {
            if (isset($_POST[$item]) && (is_array($_POST[$item]) ? count($_POST[$item]) : strlen($_POST[$item]))) {
                update_option("i_amir_homey_$item", $_POST[$item]);
            }
        }
    }
    foreach (['sms_url','sms_sender',  'sms_api_key', 'sms_pattern_code', 'sms_pattern_user_submit', 'sms_pattern_user_ok', 'sms_pattern_user_nok', 'sms_pattern_admin', 'sms_pattern_owner'] as $item) {
        $$item = get_option("i_amir_homey_$item");
    }

    ?>
    <div class="wrap">

        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <form method="post">
            <div class="form-group row my-2">
                <label class="col-lg-2 col-form-label text-right" for="sms_url">آدرس سامانه پیامک</label>
                <div class="col-lg-4">
                    <input name="sms_url" type="text" id="sms_url" class="form-control text-left" dir="ltr" value="<?php echo $sms_url; ?>" >
                </div>
            </div>
            <div class="form-group row my-2">
                <label class="col-lg-2 col-form-label text-right" for="sms_sender">شماره ارسال پیامک</label>
                <div class="col-lg-4">
                    <input name="sms_sender" type="text" id="sms_sender" class="form-control text-left" dir="ltr" value="<?php echo $sms_sender; ?>" >
                </div>
            </div>
            <div class="form-group row my-2">
                <label class="col-lg-2 col-form-label text-right" for="sms_api_key">کلید وبسرویس پیامک</label>
                <div class="col-lg-4">
                    <input name="sms_api_key" type="password" id="sms_api_key" class="form-control text-left" dir="ltr" value="<?php echo $sms_api_key; ?>" >
                </div>
            </div>
            <div class="form-group row my-2">
                <label class="col-lg-2 col-form-label text-right" for="sms_pattern_code">پترن ارسال کد</label>
                <div class="col-lg-4">
                    <input name="sms_pattern_code" type="text" id="sms_pattern_code" class="form-control text-left" dir="ltr" value="<?php echo $sms_pattern_code; ?>" >
                </div>
            </div>
            <div class="form-group row my-2">
                <label class="col-lg-2 col-form-label text-right" for="sms_pattern_user_submit">پترن ارسال پیامک به کاربر(ثبت)</label>
                <div class="col-lg-4">
                    <input name="sms_pattern_user_submit" type="text" id="sms_pattern_user_submit" class="form-control text-left" dir="ltr" value="<?php echo $sms_pattern_user_submit; ?>" >
                </div>
            </div>
            <div class="form-group row my-2">
                <label class="col-lg-2 col-form-label text-right" for="sms_pattern_user_ok">پترن ارسال پیامک به کاربر(تایید)</label>
                <div class="col-lg-4">
                    <input name="sms_pattern_user_ok" type="text" id="sms_pattern_user_ok" class="form-control text-left" dir="ltr" value="<?php echo $sms_pattern_user_ok; ?>" >
                </div>
            </div>
            <div class="form-group row my-2">
                <label class="col-lg-2 col-form-label text-right" for="sms_pattern_user_nok">پترن ارسال پیامک به کاربر(لغو)</label>
                <div class="col-lg-4">
                    <input name="sms_pattern_user_nok" type="text" id="sms_pattern_user_nok" class="form-control text-left" dir="ltr" value="<?php echo $sms_pattern_user_nok; ?>" >
                </div>
            </div>
            <div class="form-group row my-2">
                <label class="col-lg-2 col-form-label text-right" for="sms_pattern_admin">پترن ارسال پیامک به مدیر</label>
                <div class="col-lg-4">
                    <input name="sms_pattern_admin" type="text" id="sms_pattern_admin" class="form-control text-left" dir="ltr" value="<?php echo $sms_pattern_admin; ?>" >
                </div>
            </div>
            <div class="form-group row my-2">
                <label class="col-lg-2 col-form-label text-right" for="sms_pattern_user">پترن ارسال پیامک به صاحب اقامتگاه</label>
                <div class="col-lg-4">
                    <input name="sms_pattern_owner" type="text" id="sms_pattern_owner" class="form-control text-left" dir="ltr" value="<?php echo $sms_pattern_owner; ?>" >
                </div>
            </div>

            <?php
            wp_nonce_field('acme-settings-save', 'acme-custom-message');
            submit_button();
            ?>
        </form>

    </div><!-- .wrap -->
    <?php
}
