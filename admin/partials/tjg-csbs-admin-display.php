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
            <h1>Cornerstone Candidates</h1><a href="https://thejohnson.group/csb/" target="_blank">Front End</a>
        </div>
        <div class="row">
            <div class="col-md-12">
                <!-- Table to display list of contacts in $wpdb -->
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Date Added</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>State</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get all contacts from $wpdb, paginate based on get_option('tjg_csbs_num_candidates')
                        $tjg_csbs_num_candidates = get_option('tjg_csbs_num_candidates');
                        $tjg_csbs_num_candidates = (int)$tjg_csbs_num_candidates;

                        global $wpdb;
                        $tjg_csbs_table_name = $wpdb->prefix . 'tjg_csbs_candidates';
                        $candidate_query = "SELECT * FROM $tjg_csbs_table_name ORDER BY date_added DESC";
                        $candidate_query .= " LIMIT $tjg_csbs_num_candidates";

                        // $_GET['next-page'] is set in the pagination links
                        if (isset($_GET['next-page'])) {
                            $next_page = $_GET['next-page'];
                            $next_page = (int)$next_page;
                            $next_page = $next_page * $tjg_csbs_num_candidates;
                            $candidate_query .= " OFFSET $next_page";
                        }

                        $candidate_results = $wpdb->get_results($candidate_query);

                        // Loop through results and display in table
                        foreach ($candidate_results as $candidate) {
                            $date_added = $candidate->date_added;
                            $first_name = $candidate->first_name;
                            $last_name = $candidate->last_name;
                            $email = $candidate->email;
                            $phone = $candidate->phone;
                            $city = $candidate->city;
                            $state = $candidate->state;
                            $id = $candidate->id;
                            echo "<tr>";
                            echo "<td>$date_added</td>";
                            echo "<td>$first_name</td>";
                            echo "<td>$last_name</td>";
                            echo "<td>$email</td>";
                            echo "<td>$phone</td>";
                            echo "<td>$city</td>";
                            echo "<td>$state</td>";
                            echo "<td><a href='?page=tjg-csbs-admin&delete=$id'>Delete</a></td>";
                            echo "</tr>";
                        }

                        // Next page
                        echo "<tr>";
                        echo "<td colspan='8'><a href='?page=tjg-csbs-admin&next_page=1'>Next Page</a></td>";
                        echo "</tr>";


                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>