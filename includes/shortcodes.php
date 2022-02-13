<?php

function i_amir_homey_auth() {
    if (!is_user_logged_in()) {
        ?>
        <div class="i_amir_homey_auth"></div>
        <script>
            setTimeout(function () {
                i_amir_homey_auth_render(".i_amir_homey_auth")
            }, 1000)
        </script>
        <?php
    }
}
add_shortcode( 'i-amir-homey-auth', 'i_amir_homey_auth' );