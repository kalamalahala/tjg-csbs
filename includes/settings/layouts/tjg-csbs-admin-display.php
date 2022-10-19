<?php
// include_once plugin_dir_path(__FILE__) . 'includes/class-tjg-csbs-methods.php';

use Tjg_Csbs_Common as Common;
$common = new Common();

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

<div class="tjg_csbs_bootstrap_wrapper ml-0 mt-0 p-3">
    <div class="row mb-3">
        <div class="col-md-12">
            <nav class="navbar navbar-expand-sm navbar-light bg-light">
                <a class="navbar-brand" href="admin.php?page=tjg-csbs-admin">Cornerstone Admin Panel</a>
                <button class="navbar-toggler d-lg-none" type="button" data-toggle="collapse" data-target="#collapsibleNavId" aria-controls="collapsibleNavId" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="collapsibleNavId">
                    <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
                        <li class="nav-item active">
                            <a class="nav-link" href="admin.php?page=tjg-csbs-admin">Home <span class="sr-only">(current)</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="https://thejohnson.group/vertical/">Agent Portal</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="dropdownId" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Admin Menu</a>
                            <div class="dropdown-menu" aria-labelledby="dropdownId">
                                <a class="dropdown-item" href="#">Action 1</a>
                                <a class="dropdown-item" href="#">Action 2</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
    <div class="row">
        <div class="col-md-10">
            <!-- Table to display list of contacts in $wpdb -->
            <caption for="tjg-csbs-candidates">
                <h3>Candidates</h3>
            </caption>
            <table id="tjg-csbs-candidates" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Select</th> <!-- Checkbox -->
                        <th>Date Added</th>
                        <th>Date Updated</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Disposition</th>
                        <th>Lead Source</th>
                        <th>Assigned Agent</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    // $candidate_results = $common->get_candidates();


                    // if (is_array($candidate_results)) {
                    //     // Loop through results and display in table
                    //     foreach ($candidate_results as $candidate) {
                    //         $date_added = $candidate['date_added'];
                    //         $formatted_date = date('m/d/Y', strtotime($date_added));
                    //         $first_name = $candidate['first_name'];
                    //         $last_name = $candidate['last_name'];
                    //         $email = $candidate['email'];
                    //         $phone = $candidate['phone'];
                    //         $city = $candidate['city'];
                    //         $state = $candidate['state'];
                    //         $id = $candidate['id'];
                    //         $assigned_agent_id = $candidate['rep_user_id'];
                    //         $lead_source = $candidate['lead_source'];
                    //         $candidate_call_count = $common->get_candidate_call_count($id);

                    //         $date_updated = $common->get_date_updated($id);
                    //         $formatted_date_updated = (empty($date_updated)) ? 'N/A' : date('m/d/Y', strtotime($date_updated));

                    //         if (is_null($assigned_agent_id)) {
                    //             $agent_name = 'Unassigned';
                    //         } else {
                    //             $assigned_agent_name = $common->get_agent_name($assigned_agent_id);
                    //             $agent_name = $assigned_agent_name['agent_name'];
                    //         }

                    //         // format phone to (xxx) xxx-xxxx
                    //         $formatted_phone = $common->format_phone($phone);

                    //         echo '<tr id="tjg-csbs-candidate-row-' . $id . '" data-id="' . $id . '">';
                    //         echo '<td></td>';
                    //         echo "<td>$formatted_date</td>";
                    //         echo "<td>$formatted_date_updated</td>";
                    //         echo "<td>$first_name</td>";
                    //         echo "<td>$last_name</td>";
                    //         echo "<td><a href=\"mailto:$email\">$email</a></td>";
                    //         echo "<td>$formatted_phone
                    //                 <span class=\"badge badge-secondary\"><i class=\"fa fa-phone\" aria-hidden=\"true\"></i>$candidate_call_count</span>
                    //                 </td>";
                    //         echo "<td>$city</td>";
                    //         echo "<td>$state</td>";
                    //         echo "<td>$lead_source</td>";
                    //         echo "<td>$agent_name</td>";
                    //         echo '<td><button type="button" class="btn btn-primary tjg-csbs-delete" data-id="' . $id . '">Delete</button></td>';
                    //         echo '</tr>';
                    //     }
                    // } else {
                    //     echo '<tr>';
                    //     echo '<td></td>';
                    //     echo '<td colspan="8">No candidates found.</td>';
                    //     echo '</tr>';
                    // }
                     ?>
                </tbody>
            </table>
        </div>
    </div>
</div>