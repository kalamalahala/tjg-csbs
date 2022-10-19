<?php

/**
 * The public-facing functionality of the plugin.
 * 
 * Handles all public-facing functionality of the plugin including shortcodes and 
 * AJAX calls.
 * 
 * php version 8.1.0
 * 
 * @category   Core
 * @package    Tjg_Csbs
 * @subpackage Tjg_Csbs/public
 * @author     Tyler Karle <tyler.karle@icloud.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
 * @link       https://thejohnson.group/
 * @since      1.0.0
 */

// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
require_once plugin_dir_path(__FILE__) . '../includes/class-tjg-csbs-methods.php';
require_once plugin_dir_path(__FILE__) . '../includes/class-tjg-csbs-sendgrid.php';

use Tjg_Csbs_Common as Common;
use Tjg_Csbs_Sendgrid as Sendgrid;

// use PhpOffice\PhpSpreadsheet\IOFactory;
// use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 * 
 * @category   Class
 * @package    Tjg_Csbs
 * @subpackage Tjg_Csbs/public
 * @author     Tyler Karle <tyler.karle@icloud.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
 * @link       https://thejohnson.group/
 */
class Tjg_Csbs_Public
{
    #region Properties, Construct, and enqueue_scripts and styles ############################################
    /**
     * The ID of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Primary wpdb table name.
     */
    private $table_name;

    private $common;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version     The version of this plugin.
     * 
     * @since 1.0.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->table_name = (defined('TJG_CSBS_TABLE_NAME'))
            ? TJG_CSBS_TABLE_NAME : $GLOBALS['wpdb']->prefix . 'tjg_csbs_candidates';
        $this->common = new Common();
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since  1.0.0
     * @return void
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Tjg_Csbs_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Tjg_Csbs_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        // Don't load stylesheets unless inside /csb/ URI path

        $current_URI = $_SERVER['REQUEST_URI'];
        $URI = explode('/', $current_URI);
        $vertical = in_array('vertical', $URI);
        $uid =  get_current_user_id();

        do_action('qm/debug', 'my user id is ' . $uid . '.');

        if ($vertical) {
            // Primary stylesheet
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__)
                . 'css/tjg-csbs-public.css', array(), $this->version, 'all');
            // Add bootstrap CSS
            wp_enqueue_style('tjg-csbs-bootstrap-css', plugin_dir_url(__FILE__)
                . 'css/bootstrap.min.css', array(), $this->version, 'all');
            // Add Animate.min.css
            wp_enqueue_style('tjg-csbs-animate-css', plugin_dir_url(__FILE__)
                . 'css/animate.min.css', array(), $this->version, 'all');
            // Add Datatables CSS
            wp_enqueue_style('datatables-nobootstrap', plugin_dir_url(__FILE__)
                . 'datatables-nobootstrap/datatables.min.css', array(), $this->version, 'all');
            // FontAwesome
            wp_enqueue_style('fa4', plugin_dir_url(__FILE__)
                . '../includes/css/font-awesome.css', array(), $this->version, 'all');
            // busy-app
            wp_enqueue_style('busy-app', plugin_dir_url(__FILE__)
                . 'css/busy-app.min.css', array(), $this->version, 'all');
        } else {
            // do nothing
        }
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since 1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Tjg_Csbs_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Tjg_Csbs_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        // Exit script loading unless csb is inside the URL
        $uri = $_SERVER['REQUEST_URI'];
        $uri = explode('/', $uri);
        $vertical = in_array('vertical', $uri);

        if ($vertical == true) {
            // Add boostrap JS bundle
            wp_enqueue_script('tjg-csbs-bootstrap-js', plugin_dir_url(__FILE__)
                . 'js/bootstrap.bundle.min.js', array('jquery'), $this->version, false);
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__)
                . 'js/tjg-csbs-public.js', array('jquery'), $this->version, false);
            wp_enqueue_script('candidate-methods', plugin_dir_url(__FILE__)
                . 'js/tjg-csbs-candidate-methods.js', array('jquery'), $this->version, false);

            // Datatables
            wp_enqueue_script('datatables-nobootstrap', plugin_dir_url(__FILE__)
                . 'datatables-nobootstrap/datatables.min.js', array('jquery'), $this->version, false);

            // Add busy-app
            wp_enqueue_script('busy-app', plugin_dir_url(__FILE__)
                . 'js/busy-app.min.js', array('jquery'), $this->version, false);

            // AJAX for New Candidates Upload
            wp_localize_script(
                $this->plugin_name,
                'tjg_csbs_ajax_object',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('tjg_csbs_nonce'),
                    'current_user_id' => get_current_user_id(),
                )
            );

            // AJAX for Candidate Methods
            wp_localize_script(
                'candidate-methods',
                'tjg_csbs_candidates_ajax_object',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('tjg_csbs_nonce'),
                    'current_user_id' => get_current_user_id(),
                )
            );
        } else {
            // do nothing
        }
    }
    #endregion Properties, Construct, and enqueue_scripts and styles #########################################

    #region AJAX Handlers for New Candidates by CSV Upload ###################################################

    /**
     * AJAX handler/router for the TJG CSBS Plugin
     * 
     * csbs_ajax()
     * 
     * @since  1.0.0
     * @return void
     */
    public function tjg_csbs_ajax_primary()
    {
        // Instantiate method handler
        $common = new Common();

        // Collect AJAX params
        $output           = '';
        $file             = $_FILES['file'] ?? null;
        $selected_columns = $_POST['selectData'] ?? $_GET['selectData'] ?? null;
        $mode             = $_POST['mode'] ?? $_GET['mode'] ?? 'db';
        $method           = $_POST['method'] ?? $_GET['method'] ?? null;
        $candidate_id     = $_POST['id'] ?? $_GET['id'] ?? null;
        $candidate_data   = $_POST['data'] ?? $_GET['data'] ?? null;
        $user_id          = $_POST['user_id'] ?? $_GET['user_id'] ?? null;
        $selectData       = (!is_null($selected_columns)) ? json_decode(stripslashes($selected_columns)) : null;
        $data             = (!is_null($candidate_data)) ? json_decode(stripslashes($candidate_data), true) : null;
        $verify           = check_ajax_referer('tjg_csbs_nonce', 'nonce');

        // Exit if nonce fails
        if ($verify == false) wp_send_json_error('Nonce verification failed');

        // Exit if no method specified
        if (!isset($method))  wp_send_json_error('No method specified');

        // Switch $method and call Common action handler
        switch ($method) {

                // Returns a list of headers in the provided file
            case 'get_spreadsheet_summary':
                if (is_null($file)) wp_send_json_error('No file specified');
                $output = $common->tjg_csbs_ajax_get_spreadsheet_summary($file);
                break;

                /**
                 * Parses the uploaded spreadsheet using PHPSpreadsheet
                 * and inserts the candidates into the database
                 * 
                 * return a JSON object of each candidate added
                 */
            case 'upload_new_candidates':
                if (is_null($file)) wp_send_json_error('No file specified');
                $output = $common->tjg_csbs_ajax_parse_spreadsheet(
                    $file,
                    $selectData,
                    $mode
                );
                break;

                // Returns all candidates from the database
            case 'get_candidates':
                $output = $common->get_candidates();
                break;

                // Returns a single candidate by ID
            case 'get_candidate_by_id':
                if (is_null($candidate_id)) wp_send_json_error('No ID specified');
                $output = $common->get_candidate_by_id($candidate_id);
                break;

                // Returns all candidates assigned to a user
            case 'get_candidates_assigned_to_user':
                if (is_null($user_id)) wp_send_json_error('No user ID specified');
                $output = $common->get_candidates_assigned_to_user($user_id);
                break;

                // Updates a candidate in the database
            case 'update_candidate':
                if (is_null($candidate_id)) wp_send_json_error('No ID specified');
                if (is_null($data)) wp_send_json_error('No data specified');
                $output = $common->update_candidate($candidate_id, $data);
                break;

                // Deletes a candidate from the database
            case 'delete_candidate':
                if (is_null($candidate_id)) wp_send_json_error('No ID specified');
                $output = $common->delete_candidate($candidate_id);
                break;

                // Assigns a candidate to a user
            case 'assign_candidate':
                if (is_null($candidate_id)) wp_send_json_error('No ID specified');
                if (is_null($user_id)) wp_send_json_error('No user ID specified');
                $output = $common->assign_candidate($candidate_id, $user_id);
                break;

                // Unassigns a candidate from a user
            case 'unassign_candidate':
                if (is_null($candidate_id)) wp_send_json_error('No ID specified');
                if (is_null($user_id)) wp_send_json_error('No user ID specified');
                $output = $common->unassign_candidate($candidate_id, $user_id);
                break;

                // Start interview and phone call timer
            case 'begin_interview':
                if (is_null($candidate_id)) wp_send_json_error('No ID specified');
                if (is_null($user_id)) wp_send_json_error('No user ID specified');
                $output = [];
                $output['call_id'] = $common->begin_interview($candidate_id, $user_id);
                break; // handle end of interview with GF submission handler

            default:
                wp_send_json_error('Invalid method');
                die();
        }

        // Send output
        if ($output) wp_send_json_success($output);
        else wp_send_json_error('No output, unknown error');
    }

    #endregion tjg_csbs_ajax_primary #########################################################################

    #region Handle Gravity Forms submission of Interview form ################################################

    /**
     * Update candidate information based on form data
     * 
     * Parses the Gravity Form data and performs the following:
     *  - Updates database record using data provided (if any)
     *  - Assigns candidate to the user that created the form (if not already assigned)
     *  - Dispositions candidate based on selected options
     * 
     * @since  1.0.0
     * @return void
     * 
     * @see  https://docs.gravityforms.com/gform_after_submission/ Gravity Form Docs
     */
    public function tjg_csbs_gform_submission($entry = null, $form = null)
    {
        // Instantiate method handler
        $common = new Common();
        // $sendgrid = new SendGrid();

        // User, Candidate, Call, and Date information
        $candidate_id   = $entry['28'] ?? null;
        $user_id        = $entry['created_by'];
        $call_id        = $entry['30'] ?? null;
        $date_created   = $entry['date_created'];
        $date_format    = date('Y-m-d H:i:s', strtotime($date_created));

        // Disposition fields
        $call_answered  = $common->gf_yes_no_bool($entry['8']);
        $no_answer      = $entry['35'] ?? null;
        $job_seeker     = $common->gf_job_seeker_bool($entry['11']);
        $can_zoom       = $common->gf_yes_no_bool($entry['18']);
        $dnc_field      = $entry['22'] ?? null;
        $dnc            = $common->gf_dnc_bool($dnc_field);
        $briefing_date  = $entry['29'] ?? null;

        // Update Logic Fields
        $update_name_phone    = $common->gf_yes_no_bool($entry['31']);
        $update_email         = $common->gf_yes_no_bool($entry['33']);

        $new_email_address    = ($update_email) ? $entry['3'] : null;
        if (!is_null($new_email_address)) {
            $lower_email = strtolower($new_email_address);
            $common->update_candidate_email($candidate_id, $lower_email);
        }
        
        // Do Not Call
        if ($dnc) { // DNC
            $worked_candidate = $common->disposition_candidate($candidate_id, 'DNC', $user_id);
            return $worked_candidate;
        }

        // Begin disposition logic
        if (!$call_answered) { // Call was not answered
            switch ($no_answer) {
                case 'Call Back':
                    $worked_candidate = $common->disposition_candidate($candidate_id, 'Call Back', $user_id);
                    break;
                default:
                    $worked_candidate = $common->disposition_candidate($candidate_id, $no_answer, $user_id);
                    break;
            }
            return $worked_candidate;
        } else if (!$job_seeker) { // Call answered, but not a job seeker
            $worked_candidate = $common->disposition_candidate($candidate_id, 'Not Looking', $user_id);
            return $worked_candidate;
        } else if (!$can_zoom) { // Call answered, job seeker, but can't use Zoom
            $worked_candidate = $common->disposition_candidate($candidate_id, 'Reschedule Zoom', $user_id);
            return $worked_candidate;
        } else { // Zoom Scheduled! 
            $worked_candidate = $common->disposition_candidate($candidate_id, 'Scheduled', $user_id);
            $interview = $common->schedule_candidate($candidate_id, $user_id, $briefing_date);
        }

        $fields = array(
            'candidate_id'      => $candidate_id,
            'user_id'           => $user_id,
            'call_id'           => $call_id,
            'date_created'      => $date_format,
            'call_answered'     => $call_answered,
            'no_answer'         => $no_answer,
            'job_seeker'        => $job_seeker,
            'can_zoom'          => $can_zoom,
            'briefing_date'     => $briefing_date,
            'dnc'               => $dnc,
            'worked_candidate'  => $worked_candidate,
            'interview'         => $interview,
            'update_name_phone' => $update_name_phone,
            'update_email'      => $update_email,
            'new_email_address' => $new_email_address,
        );

        error_log(print_r($fields, true));

        // End interview timer
        $common->end_interview($call_id, $candidate_id, $user_id);
    }
    #endregion Handle Gravity Forms submission of Interview form #############################################

    #region Shortcodes  ######################################################################################

    // Begin Shortcode inclusions

    /**
     * Upload form shortcode.
     * 
     * Loads the upload form for new candidates.
     * 
     * @return string
     */
    function csbs_upload_new_candidates_shortcode()
    {
        // Include the form

        include plugin_dir_path(dirname(__FILE__))
            . 'public/shortcodes/tjg-csbs-upload-new-candidates.php';
        $output = new_candidate_form();
        return $output;
    }

    function csbs_show_agent_leads_shortcode()
    {
        // Include the form

        include plugin_dir_path(dirname(__FILE__))
            . 'public/shortcodes/tjg-csbs-show-agent-leads.php';
        $output = get_candidate_layout();

        return $output;
    }

    function csbs_admin_link_shortcode()
    {
        $user = wp_get_current_user();
        $role = (array) $user->roles;
        $allowed = array('administrator', 'tjg_csbs_admin');
        if (array_intersect($allowed, $role)) {
            $output = '<a href="/wp-admin/admin.php?page=tjg-csbs-admin">Admin Panel</a>';
        } else {
            return false;
        }

        return $output;
    }

    #endregion
}
