<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/kalamalahala
 * @since      1.0.0
 *
 * @package    Tjg_Csbs
 * @subpackage Tjg_Csbs/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="tjg_csbs_bootstrap_wrapper">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Custom Shortcode Builder Settings</h1>
                <p>Use the form below to create a shortcode.</p>
                <form method="post" action="options.php">
                    <?php
                    settings_fields($this->plugin_name);
                    do_settings_sections($this->plugin_name);
                    submit_button();
                    ?>
                </form>
            </div>
        </div>
    </div>