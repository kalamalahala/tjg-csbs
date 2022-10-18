<?php

/**
 * File containing methods related to the Agent Leads table
 */
use Tjg_Csbs_Common as Common;

function get_candidate_layout()
{
    $user = wp_get_current_user();
    $user_id = $user->ID;
    $leads = new Common();
    $num_leads = $leads->get_candidates_assigned_to_user($user_id);
    $num_leads = count($num_leads);
    $has_leads = ($num_leads > 0) ? true : false;
    if (!$has_leads) return '<p>You have no leads assigned to you.</p>';
    // include tjg-csbs-public-display.php
    ob_start();
    include plugin_dir_path(dirname(__FILE__)) . '/partials/tjg-csbs-public-display.php';
    $output = ob_get_clean();

    return $output;
}
