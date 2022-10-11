<?php

/**
 * Admin settings for option groups
 */

?>

<div class="bootstrap-wrapper">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <form method="post" action="options.php">
                    <?php
                    settings_fields('tjg_csbs_option_group');
                    do_settings_sections('tjg-csbs-admin-settings');
                    submit_button();
                    ?>
                </form>
            </div>
        </div>
    </div>
</div>