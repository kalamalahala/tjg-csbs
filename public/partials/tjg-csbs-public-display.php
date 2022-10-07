<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://github.com/kalamalahala
 * @since      1.0.0
 *
 * @package    Tjg_Csbs
 * @subpackage Tjg_Csbs/public/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<!-- init datatables table, fill with ajax -->
<div class="wrap">
    <table id="tjg-csbs-candidate-table">
        <thead>
            <tr>
                <th>Select</th>
                <th>Candidate ID</th>
                <th>Date Added</th>
                <th>Date Updated</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>City</th>
                <th>State</th>
                <th>Disposition</th>
                <th>Actions</th>
            </tr>
        </thead>
    </table>
</div>