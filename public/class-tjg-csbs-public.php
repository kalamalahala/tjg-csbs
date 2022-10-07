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
use Tjg_Csbs_Common as Common;

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
    #region Properties, Construct, and enqueue_scripts and styles ######################################
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
        $csb = in_array('csb', $URI);

        if ($csb) {
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
        $csb = in_array('csb', $uri);

        if ($csb == true) {
            // Add boostrap JS bundle
            wp_enqueue_script('tjg-csbs-bootstrap-js', plugin_dir_url(__FILE__)
                . 'js/bootstrap.bundle.min.js', array('jquery'), $this->version, false);
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__)
                . 'js/tjg-csbs-public.js', array('jquery'), $this->version, false);

            // Datatables
            wp_enqueue_script('datatables-nobootstrap', plugin_dir_url(__FILE__)
                . 'datatables-nobootstrap/datatables.min.js', array('jquery'), $this->version, false);

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
        } else {
            // do nothing
        }
    }
    #endregion Properties, Construct, and enqueue_scripts and styles ##########################################

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
        // Check nonce
        $verify = check_ajax_referer('tjg_csbs_nonce', 'nonce');
        if ($verify == false) {
            wp_send_json_error('Nonce verification failed');
        }
        // Check for ajax method
        $method = $_POST['method'] ?? $_GET['method'] ?? null;

        if (!isset($method)) {
            wp_send_json_error('No method specified');
        }

        // Instantiate method handler
        $common = new Common();


        $output = '';
        
        // Switch on method
        switch ($method) {
            case 'get_spreadsheet_summary':
                $file = $_FILES['file'] ?? null;
                if (is_null($file)) wp_send_json_error('No file specified');
                // Returns a summary of the spreadsheet to select headers
                $output = $common->tjg_csbs_ajax_get_spreadsheet_summary($file);
                break;

            case 'upload_new_candidates':
                $file = $_FILES['file'];
                if (is_null($file)) wp_send_json_error('No file specified');
                $mode = $_POST['mode'];
                if (is_null($mode)) wp_send_json_error('No mode specified');
                // Uploads new candidates to the database using desired headers
                $selected_columns = json_decode(stripslashes($_POST['selectData']));
                $output = $common->tjg_csbs_ajax_parse_spreadsheet(
                    $file,
                    $selected_columns,
                    $mode
                );

                break;

            case 'get_candidates':
                // Returns all candidates from the database
                // $output = $this->get_candidates();
                $output = $common->get_candidates();
                break;

            case 'get_candidate_by_id':
                // Returns a single candidate by ID
                $id = $_POST['id'] ?? null;
                if (is_null($id)) wp_send_json_error('No ID specified');
                $output = $common->get_candidate_by_id($id);
                break;

            case 'get_candidates_assigned_to_user':
                // Returns all candidates assigned to a user
                $user_id = $_POST['user_id'] ?? $_GET['user_id'] ?? null;
                if (is_null($user_id)) wp_send_json_error('No user ID specified');
                $output = $common->get_candidates_assigned_to_user($user_id);
                break;

            case 'update_candidate':
                // Updates a candidate in the database
                $id = $_POST['id'] ?? null;
                if (is_null($id)) wp_send_json_error('No ID specified');

                $data = json_decode(stripslashes($_POST['data']), true) ?? null;
                if (is_null($data)) wp_send_json_error('No data specified');

                $output = $common->update_candidate($id, $data);
                break;

            case 'delete_candidate':
                // Deletes a candidate from the database
                $id = $_POST['id'] ?? null;
                if (is_null($id)) wp_send_json_error('No ID specified');
                $output = $common->delete_candidate($id);
                break;

            case 'assign_candidate':
                // Assigns a candidate to a user
                $id = $_POST['id'] ?? null;
                if (is_null($id)) wp_send_json_error('No ID specified');

                $user_id = $_POST['user_id'] ?? null;
                if (is_null($user_id)) wp_send_json_error('No user ID specified');

                $output = $common->assign_candidate($id, $user_id);
                break;

            default:
                wp_send_json_error('Invalid method');
                die();
        }

        // Send output
        if ($output) wp_send_json_success($output);
        else wp_send_json_error('No output');
    }

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

    #endregion
}