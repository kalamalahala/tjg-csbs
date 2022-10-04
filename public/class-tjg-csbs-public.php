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

require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

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
        $this->table_name = (defined('TJG_CSBS_TABLE_NAME')) ? TJG_CSBS_TABLE_NAME : $GLOBALS['wpdb']->prefix . 'tjg_csbs_candidates';
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
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/tjg-csbs-public.css', array(), $this->version, 'all');
            // Add bootstrap CSS
            wp_enqueue_style('tjg-csbs-bootstrap-css', plugin_dir_url(__FILE__) . 'css/bootstrap.min.css', array(), $this->version, 'all');
            // Add Animate.min.css
            wp_enqueue_style('tjg-csbs-animate-css', plugin_dir_url(__FILE__) . 'css/animate.min.css', array(), $this->version, 'all');
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
            wp_enqueue_script('tjg-csbs-bootstrap-js', plugin_dir_url(__FILE__) . 'js/bootstrap.bundle.min.js', array('jquery'), $this->version, false);
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/tjg-csbs-public.js', array('jquery'), $this->version, false);

            // AJAX for New Candidates Upload
            wp_localize_script(
                $this->plugin_name,
                'tjg_csbs_ajax_object',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('tjg_csbs_nonce')
                )
            );
        } else {
            // do nothing
        }
    }

    /**
     * Get list of columns in $wpdb table
     * 
     * Returns a list of the columns in the primary table tjg_csbs_candidates
     * 
     * @return array $columns
     */
    public function get_columns() {
        global $wpdb;
        $table_name = $this->table_name;

        $columns = $wpdb->get_col("DESC $table_name", 0);

        return $columns;
    }



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
            die();
        }

        if (!isset($_POST['method'])) {
            wp_send_json_error('No method specified');
            die();
        }
        // Check for ajax method
        $method = $_POST['method'];
        $file = $_FILES['file'];
        $output = '';
        $table_columns = $this->get_columns();

        // Switch on method
        switch ($method) {
            case 'get_spreadsheet_summary':
                // Returns a summary of the spreadsheet to select headers
                $output = $this->tjg_csbs_ajax_get_spreadsheet_summary($file);
                break;
            case 'upload_new_candidates':
                // Uploads new candidates to the database using desired headers
                $selected_columns = json_decode(stripslashes($_POST['selectData']));
                $output = $this->tjg_csbs_ajax_parse_spreadsheet($file, $selected_columns, $table_columns);
                break;
            default:
                wp_send_json_error('Invalid method');
                die();
        }

        // Send output
        wp_send_json_success($output);
        die();
    }

    /**
     * AJAX handler for parsing spreadsheet
     */
    public function tjg_csbs_ajax_get_spreadsheet_summary($file)
    {

        // Pass file to wp_handle_upload
        $upload = wp_handle_upload($file, array('test_form' => false));

        // File type using IOFactory::identify()
        $file_type = IOFactory::identify($upload['file']);
        $reader = IOFactory::createReader($file_type);

        // Pass upload to reader
        $spreadsheet = $reader->load($upload['file']);


        if ($spreadsheet) {
            // Get worksheet
            $payload = [];
            $worksheet = $spreadsheet->getActiveSheet();

            // Number of rows besides header that has data
            $payload['num_rows'] = $worksheet->getHighestRow() - 1;

            // read first row
            foreach ($worksheet->getRowIterator(1, 1) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    $col = $cell->getColumn() ?? 'null column';
                    $val = $cell->getValue() ?? 'Column ' . $col;
                    
                    // Add column number and value to array
                    $headers[] = array(
                        'column' => $col,
                        'value' => $val
                    );                    
                }
            }

            // Add headers to payload
            $payload['headers'] = $headers;


            unlink($upload['file']);
            wp_send_json_success($payload);
            die();
        } else {
            unlink($upload['file']);
            wp_send_json_error('Error loading file');
            die();
        }
    }

    /**
     * Parse uploaded sheet.
     * 
     * Extracts data from uploaded Excel file, formatting it for
     * insertion into the database. Remove special characters from
     * names and phone numbers. Add current date and time to each
     * record passed to tjg_csbs_insert_new_candidate().
     * 
     * This function is called by tjg_csbs_ajax_primary() when the
     * 'upload_new_candidates' method is passed. It returns an array
     * of the inserted candidates, or an error message if the
     * candidate already exists.
     * 
     * After the file is uploaded, it is deleted from the server.
     * 
     * @since  1.0.0
     * @param  array $file
     * @param  object $selected_columns
     * @param  array $table_columns
     * @return array $output
     */

    public function tjg_csbs_ajax_parse_spreadsheet(array $candidate_file, object $selected_columns, array $columns = null)
    {
        // If no columns are passed, use default (return error for now)
        if ($columns == null) {
            wp_send_json_error('No columns passed');
            die();
        }

        $payload = [];

        // Specified column letters
        $first_name_column = $selected_columns->firstNameColumn;
        $last_name_column = $selected_columns->lastNameColumn;
        $phone_column = $selected_columns->phoneColumn;
        $email_column = $selected_columns->emailColumn;
        $city_column = $selected_columns->cityColumn;
        $state_column = $selected_columns->stateColumn;

        // Pass file to wp_handle_upload
        $upload = wp_handle_upload($candidate_file, array('test_form' => false));

        // File type using IOFactory::identify()
        $file_type = IOFactory::identify($upload['file']);
        $reader = IOFactory::createReader($file_type);

        // Pass upload to reader
        $spreadsheet = $reader->load($upload['file']);

        if ($spreadsheet) {
            // Get worksheet
            $worksheet = $spreadsheet->getActiveSheet();

            // Collect specified columns from each row and insert into database
            foreach ($worksheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    $col = $cell->getColumn();
                    $val = $cell->getValue();

                    // Add column number and value to array
                    $row_data[$col] = $val;
                }

                // Get data from selected columns
                $first_name = $row_data[$first_name_column];
                $last_name = $row_data[$last_name_column];
                $phone = $row_data[$phone_column];
                $email = $row_data[$email_column];
                $city = $row_data[$city_column];
                $state = $row_data[$state_column];

                // Format phone number
                $phone = preg_replace('/[^0-9]/', '', $phone);

                // Format name
                $first_name = preg_replace('/[^A-Za-z]/', '', $first_name);
                $last_name = preg_replace('/[^A-Za-z]/', '', $last_name);

                // Add current date and time
                $date = date('Y-m-d H:i:s');

                // Insert candidate
                $inserted = $this->tjg_csbs_insert_new_candidate($first_name, $last_name, $phone, $email, $city, $state, $date);

                /* $inserted returns
                 * true if candidate was inserted
                 * false if candidate already exists
                 * error string if error occurred
                 */
                switch ($inserted) {
                    case true:
                        $payload['inserted'][] = array(
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'phone' => $phone,
                            'email' => $email,
                            'city' => $city,
                            'state' => $state,
                            'date' => $date
                        );
                        break;
                    case false:
                        $payload['already_exists'][] = array(
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'phone' => $phone,
                            'email' => $email,
                            'city' => $city,
                            'state' => $state,
                            'date' => $date
                        );
                        break;
                    default:
                        $payload['error'][] = $inserted;
                        break;
                }
            }
 

            // Delete file from server
            unlink($upload['file']);

            // Send json success with payload
            wp_send_json_success($payload);
            die();
        } else {
            unlink($upload['file']);
            wp_send_json_error('Error loading file');
            die();
        }

        unlink($upload['file']);
        wp_send_json_error('Error loading file');
        die();
    }

    /**
     * Insert new candidates.
     * 
     * Insert all candidates in the uploaded file into the database
     * table 'tjg_csbs_candidates'. If the phone number already
     * exists in the database, the candidate will not be inserted
     * and the candidate's name will be added to the $duplicate
     * array. Function returns an array of duplicate candidates
     * and the number of candidates inserted.
     * 
     * @since  1.0.0
     * @param  string $first_name
     * @param  string $last_name
     * @param  string $phone
     * @param  string $email
     * @param  string $city
     * @param  string $state
     * @param  string $date
     * @return bool|string
     */
    public function tjg_csbs_insert_new_candidate($first_name, $last_name, $phone, $email, $city, $state, $date)
    {
        /*
        * $payload = array(
        *     'first_name' => $first_name,
        *     'last_name' => $last_name,
        *     'phone' => $phone,
        *     'email' => $email,
        *     'city' => $city,
        *     'state' => $state,
        *     'date' => $date
        * );
        */

        global $wpdb;
        $table = $this->table_name;
        $duplicates = [];
        $insertions = 0;

        // Select candidates with Date Added before now
        $query = "SELECT * FROM $table WHERE phone LIKE %s AND date_added < %s";
        $query = $wpdb->prepare($query, $phone, $date);
        $result = $wpdb->get_results($query);

        // If no results, insert candidate
        if (empty($result)) {
            $insert_query = "INSERT INTO $table
            (first_name, last_name, phone, email, city, state, date_added)
            VALUES (%s, %s, %s, %s, %s, %s, %s)";
            $insert_query = $wpdb->prepare($insert_query, $first_name, $last_name, $phone, $email, $city, $state, $date);
            $inserted = $wpdb->query($insert_query);
            if ($inserted) return true;
            else if (!$inserted) {
                // get query error
                $error = $wpdb->last_error;
                return $error;
            }
        } else {
            return false;
        }

    }

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

        include plugin_dir_path(dirname(__FILE__)) . 'public/shortcodes/tjg-csbs-upload-new-candidates.php';
        $output = new_candidate_form();
        return $output;
    }
}
