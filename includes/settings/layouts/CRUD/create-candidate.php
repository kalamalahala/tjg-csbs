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

// Get the current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

$sendgrid_key = get_option('tjg_csbs_sendgrid_api_key');

echo 'sendgrid api key: ' . $sendgrid_key . '<br />';


// Sendgrid integration testing real quick
if ($_GET['sendgrid'] == 'test') {
    $email = new \SendGrid\Mail\Mail();
    $email->setFrom("info@vertical-businesssolutions.com", "Thomas Hammond");
    $email->setSubject("Sending with SendGrid is Fun");
    $email->addTo("solo.driver.bob@gmail.com", "Tyler Karle");
    $email->addContent("text/plain", "and easy to do anywhere, even with PHP");
    $email->addContent(
        "text/html",
        "<strong>and easy to do anywhere, even with PHP
        {{first_name}} {{last_name}} - {{custom_field}}
            </strong>"
    );

    // Add cadndidate information using Handlebars
    $email->addSubstitution("{{first_name}}", "Thomas");
    $email->addSubstitution("{{last_name}}", "Hammond");
    $email->addSubstitution("{{custom_field}}", "Custom Field Value");
    
    $sendgrid = new \SendGrid(get_option('tjg_csbs_sendgrid_api_key'));
    try {
        $response = $sendgrid->send($email);
        print $response->statusCode() . "\n";
        print_r($response->headers());
        print $response->body() . "\n";
    } catch (Exception $e) {
        echo 'Caught exception: ' . $e->getMessage() . "\n";
    }
}

?>

<div class="wrap">
    <form method="" action="">
        <h1 class="wp-heading-inline">Add New Candidate</h1>
        <hr class="wp-header-end">
        <div class="tjg-csbs-form">
            <div class="tjg-csbs-form-section">
                <div class="tjg-csbs-form-section-header">
                    <h2 class="tjg-csbs-form-section-title">Candidate Information</h2>
                </div>
                <div class="tjg-csbs-form-section-body">
                    <div class="tjg-csbs-form-section-body-row">
                        <div class="tjg-csbs-form-section-body-row-column">
                            <label for="tjg-csbs-first-name">First Name</label>
                            <input type="text" name="tjg-csbs-first-name" id="tjg-csbs-first-name" class="tjg-csbs-form-input" />
                        </div>
                        <div class="tjg-csbs-form-section-body-row-column">
                            <label for="tjg-csbs-last-name">Last Name</label>
                            <input type="text" name="tjg-csbs-last-name" id="tjg-csbs-last-name" class="tjg-csbs-form-input" />
                        </div>
                    </div>
                    <div class="tjg-csbs-form-section-body-row">
                        <div class="tjg-csbs-form-section-body-row-column">
                            <label for="tjg-csbs-email">Email Address</label>
                            <input type="email" name="tjg-csbs-email" id="tjg-csbs-email" class="tjg-csbs-form-input" />
                        </div>
                        <div class="tjg-csbs-form-section-body-row-column">
                            <label for="tjg-csbs-phone">Phone Number</label>
                            <input type="tel" name="tjg-csbs-phone" id="tjg-csbs-phone" class="tjg-csbs-form-input" />
                        </div>
                    </div>
                    <div class="tjg-csbs-form-section-body-row">
                        <div class="tjg-csbs-form-section-body-row-column">
                            <label for="tjg-csbs-city">City</label>
                            <input type="text" name="tjg-csbs-city" id="tjg-csbs-city" class="tjg-csbs-form-input" />
                        </div>

                        <div class="tjg-csbs-form-section-body-row-column">
                            <label for="tjg-csbs-state">State</label>
                            <select name="tjg-csbs-state" id="tjg-csbs-state" class="tjg-csbs-form-input">
                                <?php

                                $states = array(
                                    'AL' => 'ALABAMA',
                                    'AK' => 'ALASKA',
                                    'AS' => 'AMERICAN SAMOA',
                                    'AZ' => 'ARIZONA',
                                    'AR' => 'ARKANSAS',
                                    'CA' => 'CALIFORNIA',
                                    'CO' => 'COLORADO',
                                    'CT' => 'CONNECTICUT',
                                    'DE' => 'DELAWARE',
                                    'DC' => 'DISTRICT OF COLUMBIA',
                                    'FM' => 'FEDERATED STATES OF MICRONESIA',
                                    'FL' => 'FLORIDA',
                                    'GA' => 'GEORGIA',
                                    'GU' => 'GUAM GU',
                                    'HI' => 'HAWAII',
                                    'ID' => 'IDAHO',
                                    'IL' => 'ILLINOIS',
                                    'IN' => 'INDIANA',
                                    'IA' => 'IOWA',
                                    'KS' => 'KANSAS',
                                    'KY' => 'KENTUCKY',
                                    'LA' => 'LOUISIANA',
                                    'ME' => 'MAINE',
                                    'MH' => 'MARSHALL ISLANDS',
                                    'MD' => 'MARYLAND',
                                    'MA' => 'MASSACHUSETTS',
                                    'MI' => 'MICHIGAN',
                                    'MN' => 'MINNESOTA',
                                    'MS' => 'MISSISSIPPI',
                                    'MO' => 'MISSOURI',
                                    'MT' => 'MONTANA',
                                    'NE' => 'NEBRASKA',
                                    'NV' => 'NEVADA',
                                    'NH' => 'NEW HAMPSHIRE',
                                    'NJ' => 'NEW JERSEY',
                                    'NM' => 'NEW MEXICO',
                                    'NY' => 'NEW YORK',
                                    'NC' => 'NORTH CAROLINA',
                                    'ND' => 'NORTH DAKOTA',
                                    'MP' => 'NORTHERN MARIANA ISLANDS',
                                    'OH' => 'OHIO',
                                    'OK' => 'OKLAHOMA',
                                    'OR' => 'OREGON',
                                    'PW' => 'PALAU',
                                    'PA' => 'PENNSYLVANIA',
                                    'PR' => 'PUERTO RICO',
                                    'RI' => 'RHODE ISLAND',
                                    'SC' => 'SOUTH CAROLINA',
                                    'SD' => 'SOUTH DAKOTA',
                                    'TN' => 'TENNESSEE',
                                    'TX' => 'TEXAS',
                                    'UT' => 'UTAH',
                                    'VT' => 'VERMONT',
                                    'VI' => 'VIRGIN ISLANDS',
                                    'VA' => 'VIRGINIA',
                                    'WA' => 'WASHINGTON',
                                    'WV' => 'WEST VIRGINIA',
                                    'WI' => 'WISCONSIN',
                                    'WY' => 'WYOMING',
                                    'AE' => 'ARMED FORCES AFRICA \ CANADA \ EUROPE \ MIDDLE EAST',
                                    'AA' => 'ARMED FORCES AMERICA (EXCEPT CANADA)',
                                    'AP' => 'ARMED FORCES PACIFIC'
                                );

                                foreach ($states as $val => $state) {
                                    $formatted_state = ucwords($state);
                                    echo "<option value='$val'>$formatted_state</option>";
                                }

                                ?>
                            </select>

                        </div>
                        <div class="tjg-csbs-form-section-body-row-column">
                            <label for="tjg-csbs-source">Lead Source</label>
                            <select name="tjg-csbs-source" id="tjg-csbs-source" class="tjg-csbs-form-input">
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


    </form>
</div>