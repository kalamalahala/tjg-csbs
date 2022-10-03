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

        // Switch on method
        switch ($method) {
            case 'get_spreadsheet_summary':
                // Returns a summary of the spreadsheet to select headers
                $output = $this->tjg_csbs_ajax_get_spreadsheet_summary($file);
                break;
            case 'upload_new_candidates':
                // Uploads new candidates to the database using desired headers
                $columns = $_POST['columns'];
                $output = $this->tjg_csbs_ajax_parse_spreadsheet($file, $columns);
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
            $headers = [];
            $worksheet = $spreadsheet->getActiveSheet();

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

            unlink($upload['file']);
            wp_send_json_success($headers);
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
     * @return array $output
     */

    public function tjg_csbs_ajax_parse_spreadsheet($candidate_file, $columns = null)
    {
        // If no columns are passed, use default (return error for now)
        if ($columns == null) {
            wp_send_json_error('No columns passed');
            die();
        }

        // Check for columns and return list of provided columns then exit
        if ($columns) {
            for ($i = 0; $i < count($columns); $i++) {
                $output[] = $columns[$i];
            }

            wp_send_json_success($output);
            die();
        }

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

            // read first row
            foreach ($worksheet->getRowIterator(1, 1) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    if ($cell->getValue() != null) {
                        $headers[] = $cell->getValue();
                    }
                }
            }

            // read all rows
            $output = [];
            foreach ($worksheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $row = [];
                foreach ($cellIterator as $cell) {
                    if ($cell->getValue() != null) {
                        $row[] = $cell->getValue();
                    }
                }
                $output[] = array_combine($headers, $row);
            }

            // Remove special characters from names and phone numbers
            $output = array_map(function ($candidate) {
                $candidate['First Name'] = preg_replace('/[^A-Za-z0-9\-]/', '', $candidate['First Name']);
                $candidate['Last Name'] = preg_replace('/[^A-Za-z0-9\-]/', '', $candidate['Last Name']);
                $candidate['Phone Number'] = preg_replace('/[^0-9]/', '', $candidate['Phone Number']);
                return $candidate;
            }, $output);

            // Add current date and time to each record
            $output = array_map(function ($candidate) {
                $candidate['Date Added'] = date('Y-m-d H:i:s');
                return $candidate;
            }, $output);

            // Insert each candidate into the database
            $output = array_map(function ($candidate) {
                $insert = $this->tjg_csbs_insert_new_candidate($candidate);
                return $insert;
            }, $output);

            // Delete file from server
            unlink($upload['file']);

            // Return output
            return $output;
        } else {
            unlink($upload['file']);
            return 'Error loading file';
        }

        // Output results of candidate insertion
        wp_send_json_success();
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
     * @param  array $candidate_data
     * @return array
     */
    public function tjg_csbs_insert_new_candidate($candidate_data)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'tjg_csbs_candidates';
        $duplicates = [];
        $insertions = 0;

        // Select candidates with Date Added before now
        $query = "SELECT * FROM $table WHERE phone = %s";
        $query = $wpdb->prepare($query, $candidate_data['phone']);
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
