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
    <div class="container">
        <!-- Date Added, Date Updated, Date Worked 3 column table -->
        <table class="table table-light striped" id="csbs-date-info">
            <tbody>
                <tr>
                    <th>Date Added</th>
                    <th>Date Updated</th>
                    <th>Date Worked</th>
                </tr>
                <tr>
                    <td><? echo $candidate->date_added ?></td>
                    <td><? echo $candidate->date_updated ?></td>
                    <td><? echo $candidate->date_worked ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Candidate Information -->
    <div class="container">
        <h2>Candidate Information</h2>
        <table class="table table-light striped" id="csbs-candidate-info">
            <tbody>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                </tr>
                <tr>
                    <td><? echo $candidate->first_name ?></td>
                    <td><? echo $candidate->last_name ?></td>
                    <td><? echo $candidate->phone ?></td>
                    <td><? echo $candidate->email ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Agent Information -->
    <div class="container">
        <h2>Agent Information</h2>
        <table class="table table-light striped" id="csbs-agent-info">
            <tbody>
                <tr>
                    <th>Agent Name</th>
                </tr>
                <tr>
                    <td><? echo $common->get_agent_name($candidate->rep_user_id, 'first_and_last', 'string'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>


</div>