<?php

/** Dump candidate info provided by Candidate class */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!isset($_GET['candidate_id'])) {
    error_log('Candidate ID not set when attempting to view candidate');
    wp_die('Candidate ID not set when attempting to view candidate');
}

use Tjg_Csbs_Common as Common;
use Candidate as Candidate;

$common = new Common();
$candidate = new Candidate( $_GET['candidate_id'] );

?>

<div class="wrap">
    <h1>Viewing: <? echo $candidate->first_name . ' ' . $candidate->last_name ?></h1>
    rest of information to come
</div>