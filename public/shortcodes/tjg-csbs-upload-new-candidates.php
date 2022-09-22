<?php

/**
 * File containing methods related to the New Candidate Upload form and requests
 */

function new_candidate_form()
{

    // include layout-new-candidates.html
    ob_start();
    include plugin_dir_path(dirname(__FILE__)) . '/partials/layout-new-candidates-upload.php';
    $output = ob_get_clean();

    return $output;
}
