function i_amir_homey_auth_render($warpar) {
    (function ($) {
        if (typeof($warpar) === "string") $warpar = $($warpar);
        var method = "login";
        var $alerts = $(`<div class="alert alert-danger" role="alert"></div>`);
        $alerts.hide();
        var $form = $(`<form></form>`);

        var $fields = $(`<div>`);

        $fields.append(`<div class="form-group inumber"><input name="mobile" type="text" class="form-control" placeholder="شماره موبایل"></div>`);

        $form.append($fields);

        var $actionBtn = $(`<button type="submit" class="homey-login-button i-login-button btn btn-primary btn-full-width">ارسال کد یکبارمصرف</button>`);
        $form.append($actionBtn);

        $warpar.append($alerts);
        $warpar.append($form);
        function renderAlert(status, messages) {
            $alerts.empty();
            $alerts.show();
            $alerts.attr("class", status ? "alert alert-success" : "alert alert-danger")
            $alerts.append(messages.join("<br>"));
        }
        $form.submit(function (e) {
            $actionBtn.prop("disabled", true);
            $.ajax({
                method: 'POST',
                url: '/wp-json/iamir/homey/v1/' + method,
                data: $form.serialize()
            }).done(function (response) {   // success callback function
                if (response.data && response.data.status) {
                    renderAlert(true, response.data.message ? [response.data.message] : response.data.errors);
                    if (method == "login") {
                        method = "verify";
                        $fields.empty();
                        $fields.append(`<input name="mobile" type="hidden" class="form-control" value="${response.data.mobile}">`);
                        $fields.append(`<div class="form-group inumber"><input name="code" type="text" class="form-control" placeholder="کد یکبارمصرف"></div>`);
                        if(response.data.type === "registering") {
                            $fields.append(`<div class="form-group mt-10px"><input name="name" type="text" class="form-control text-center" placeholder="نام"></div>`);
                            $fields.append(`<div class="form-group mt-10px"><input name="family" type="text" class="form-control text-center" placeholder="نام خانوادگی"></div>`);
                            $fields.append(`<div class="form-group mt-10px"><select name="role" class="form-control text-center"><option value="">من می خواهم؟</option>
                                <option value="homey_renter">من می خواهم رزرو کنم</option>
                                <option value="homey_host">من می خواهم میزبانی کنم</option>
                            </select></div>`);
                        }
                        $actionBtn.text("ورود به ناحیه کاربری");
                    }else {
                        $fields.hide();
                        $actionBtn.hide();
                        setTimeout(function () {
                            window.location.href = location.protocol + "//"+ window.location.host
                        }, 3000)
                    }
                }else {
                    renderAlert(false, response.data.message ? [response.data.message] : response.data.errors);
                }
                $actionBtn.prop("disabled", false);
            });
            return false;
        })
    })(jQuery);
}
(function ($) {
    $(document).ready(function () {
        var $warpar = $("#modal-login").find(".modal-login-form");
        $("#modal-login").find(".modal-title").text("ورود / ثبت نام")
        $("#modal-login").find(".modal-body-left").hide();
        $("#modal-login").find(".modal-body-right").attr("class", "modal-body");

        $warpar.empty();
        //$warpar.append(`<p id="i-amir-login-title" class="text-center"><strong>ورود / ثبت نام</strong></p>`);
        i_amir_homey_auth_render($warpar);
    })
})(jQuery);
