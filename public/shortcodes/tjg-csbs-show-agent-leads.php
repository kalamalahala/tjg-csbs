<?php

/**
 * File containing methods related to the Agent Leads table
 */

function get_candidate_layout()
{
    do_action('qm/debug', 'show_agent_leads was called');
    // include tjg-csbs-public-display.php
    ob_start();
    include plugin_dir_path(dirname(__FILE__)) . '/partials/tjg-csbs-public-display.php';
    $output = ob_get_clean();

    return $output;
}
