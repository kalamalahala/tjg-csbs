<?php

/**
 * Form for creating a new candidate
 * 
 * Applicable fields:
 * - First Name
 * - Last Name
 * - Email Address (required) (unique) (validated)
 * - Phone Number (required) (unique) (validated)
 * - City (text)
 * - State (dropdown)
 * - Lead Source (dropdown)
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

use Tjg_Csbs_Common as Common;

$common = new Common();

?>

<div class="wrap">
    <h1 class="wp-heading-inline">Create Candidate</h1>
    <hr class="wp-header-end">
    <div class="row">
        <div class="col-md-6">
            <form method="" action="">
                <!-- Bootstrap 4 Form Styling -->
                <div class="form-group row">
                    <label for="first_name" class="col-sm-2 col-form-label">First Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="last_name" class="col-sm-2 col-form-label">Last Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="email_address" class="col-sm-2 col-form-label">Email Address</label>
                    <div class="col-sm-10">
                        <input type="email" class="form-control" id="email_address" name="email_address" placeholder="Email Address">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="phone_number" class="col-sm-2 col-form-label">Phone Number</label>
                    <div class="col-sm-10">
                        <input type="tel" class="form-control" id="phone_number" name="phone_number" placeholder="Phone Number">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="city" class="col-sm-2 col-form-label">City</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="city" name="city" placeholder="City">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="state" class="col-sm-2 col-form-label">State</label>
                    <div class="col-sm-10">
                        <select name="state" id="state">
                            <?php

                            $states = $common->get_states();
                            foreach ($states as $val => $state) {
                                $lower_state = strtolower($state);
                                $formatted_state = ucwords($lower_state);
                                echo "<option value='$val'>$formatted_state</option>";
                            }

                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="lead_source" class="col-sm-2 col-form-label">Lead Source</label>
                    <div class="col-sm-10">
                        <select name="lead_source" id="lead_source">
                            <option value="Indeed">Indeed</option>
                            <option value="ZipRecruiter">ZipRecruiter</option>
                            <option value="CareerBuilder">CareerBuilder</option>
                            <option value="Monster">Monster</option>
                            <option value="LinkedIn">LinkedIn</option>
                            <option value="Facebook">Facebook</option>
                            <option value="Google">Google</option>
                            <option value="Personal Recruit">Personal Recruit</option>
                            <option value="Other">Other</option>

                        </select>
                    </div>
                </div>

                <!-- hidden success -->
                <div class="alert alert-success" role="alert" id="success" hidden>
                    <strong>Success!</strong> Candidate created successfully.
                </div>

                <!-- hidden error -->
                <div class="alert alert-danger" role="alert" id="error" hidden>
                    <strong>Error!</strong> There was an error creating the candidate.
                </div>

                <div class="form-group row">
                    <div class="col-sm-4">
                        <button type="submit" class="btn btn-primary">Create Candidate</button>
                    </div>
                    <div class="col-sm-4">
                        <button type="reset" class="btn btn-secondary">Clear</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>