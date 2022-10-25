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
                                <a class="dropdown-item" href="?page=tjg-csbs-admin-create">Create Candidate</a>
                                <a class="dropdown-item" href="?page=tjg-csbs-admin-upload">Upload New Candidates</a>
                                <a class="dropdown-item" href="?page=tjg-csbs-admin-settings">Settings</a>
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
                        <th>Select</th> <!-- Checkbox [0] -->
                        <th>ID</th> <!-- ID [1] -->
                        <th>Date Added</th> <!-- Date Added [2] -->
                        <th>Date Updated</th> <!-- Date Updated [3] -->
                        <th>First Name</th> <!-- First Name [4] -->
                        <th>Last Name</th> <!-- Last Name [5] -->
                        <th>Email</th> <!-- Email [6] -->
                        <th>Phone</th> <!-- Phone [7] -->
                        <th>City</th> <!-- City [8] -->
                        <th>State</th> <!-- State [9] -->
                        <th>Disposition</th> <!-- Disposition [10] -->
                        <th>Merge Status</th> <!-- Merge Status [11] -->
                        <th>Lead Source</th> <!-- Lead Source [12] -->
                        <th>Assigned Agent</th> <!-- Assigned Agent [13] -->
                        <th>Actions</th> <!-- Actions [14] -->
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#updateForm">
      Launch
    </button>
    
    <!-- Modal -->
    <div class="modal fade" id="updateForm" tabindex="-1" role="dialog" aria-labelledby="Update Candidate" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                    <div class="modal-header">
                            <h5 class="modal-title">Update Candidate</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                        </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <form action="">
                            to display update form
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        $('#exampleModal').on('show.bs.modal', event => {
            var button = $(event.relatedTarget);
            var modal = $(this);
            // Use above variables to manipulate the DOM
            
        });
    </script>
</div>