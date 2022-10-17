<?php

/**
 * Testing area for Tjg_Csbs_Common methods
 */

use Tjg_Csbs_Common as Common;
use Tjg_Csbs_Sendgrid as Sendgrid;

$sendgrid_api_key = 'SG.1q2w3e4r5t6y7u8i9o0p-1q2w3e4r5t6y7u8i9o0p';

$common = new Common();
$sendgrid = new Sendgrid( $sendgrid_api_key );
$user_id = get_current_user_id();

?>

<div class="card">
    <div class="card-header">
        <?php echo $common->get_agent_name($user_id, 'first_and_last', 'string'); ?>
        Symlink is functioning
    </div>
    <div class="card-body">
        <h5 class="card-title">Call Statistics</h5>
        <p class="card-text">
        <ul>
            <li>
                <strong>Calls Made:</strong>
                <?php
                echo $common->get_total_call_count($user_id);
                ?>
            </li>
            <li><strong>Assigned Candidates:</strong>
                <?php
                $candidates = $common->get_candidates_assigned_to_user($user_id);

                echo (is_array($candidates)) ? count($candidates) : '0';
                ?>
            </li>
            <li>
                <strong>API Key: </strong>
                <?php echo $sendgrid->get_api_key(); ?>
            </li>
            <li></li>
        </ul>
        </p>
    </div>
    <div class="card-footer">
        Footer
    </div>
</div>