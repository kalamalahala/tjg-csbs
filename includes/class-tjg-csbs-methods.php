<?php

/**
 * Methods to include in the Public and Admin classes
 * 
 * Create, Read, Update, and Delete methods for the TJG_CSBS plugin
 * 
 * @since      1.0.0
 */

require_once plugin_dir_path(dirname(__FILE__)) . '/vendor/autoload.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tjg-csbs-candidate.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Twilio\Rest\Client as Client;

class Tjg_Csbs_Common
{
    #region Properties and Constructor ####################################

    private $candidate_table;
    private $log_table;
    private $notes_table;
    private $call_log_table;

    public function __construct()
    {
        global $wpdb;
        $this->candidate_table = (defined('TJG_CSBS_TABLE_NAME'))
            ? TJG_CSBS_TABLE_NAME : $wpdb->prefix . 'tjg_csbs_candidates';

        $this->log_table       = (defined('TJG_CSBS_LOG_TABLE_NAME'))
            ? TJG_CSBS_LOG_TABLE_NAME : $wpdb->prefix . 'tjg_csbs_log';

        $this->notes_table     = (defined('TJG_CSBS_NOTES_TABLE_NAME'))
            ? TJG_CSBS_NOTES_TABLE_NAME : $wpdb->prefix . 'tjg_csbs_notes';

        $this->call_log_table  = (defined('TJG_CSBS_CALL_LOG_TABLE_NAME'))
            ? TJG_CSBS_CALL_LOG_TABLE_NAME : $wpdb->prefix . 'tjg_csbs_call_log';
    }

    #endregion // Properties and Constructor ###############################

    #region Spreadsheet Handlers ##########################################

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

    public function tjg_csbs_ajax_parse_spreadsheet(
        array $candidate_file,
        object $selected_columns,
        string $mode = 'db'
    ) {
        $payload = [];

        // Specified column letters
        $first_name_column = $selected_columns->firstNameColumn;
        $last_name_column = $selected_columns->lastNameColumn;
        $phone_column = $selected_columns->phoneColumn;
        $email_column = $selected_columns->emailColumn;
        $city_column = $selected_columns->cityColumn;
        $state_column = $selected_columns->stateColumn;
        $source_column = $selected_columns->sourceColumn;

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
                $first_name = $row_data[$first_name_column] ?? '';
                $last_name = $row_data[$last_name_column] ?? '';
                $phone = $row_data[$phone_column] ?? '';
                $email = $row_data[$email_column] ?? '';
                $city = $row_data[$city_column] ?? '';
                $state = $row_data[$state_column] ?? '';
                $source = $row_data[$source_column] ?? '';

                // Format phone number
                $phone = preg_replace('/[^0-9]/', '', $phone);

                // Format name
                $first_name = preg_replace('/[^A-Za-z]/', '', $first_name);
                $last_name = preg_replace('/[^A-Za-z]/', '', $last_name);

                // Add current date and time
                $date = date("Y-m-d H:i:s");

                $inserted = $this->tjg_csbs_insert_new_candidate(
                            $first_name,
                            $last_name,
                            $phone,
                            $email,
                            $city,
                            $state,
                            $date,
                            $source
                        );
                }

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
    }

    #endregion Spreadsheet methods

    #region CRUD Operations for Candidates ################################

    #region CRUD Create Functions

    /*
    * Create ##########################################################################################
    */

    /**
     * Create new log entry.
     * 
     * Create a new entry in the log table.
     * @todo: call with AJAX sometimes
     * 
     * @since  1.0.0
     * @param  int user_id
     * @param  int candidate_id
     * @param  string action
     * @param  string date
     * @return int $log_id
     */
    public function tjg_csbs_create_log_entry(
        int $user_id,
        int $candidate_id,
        string $action,
        string $date
    ) {
        global $wpdb;
        $log_table = $this->log_table;
        if (empty($date)) $date = date('Y-m-d H:i:s');

        $log_query_raw = "INSERT INTO $log_table
        (wp_user_id, candidate_id, action, date)
        VALUES (%d, %d, %s, %s)";
        $log_query = $wpdb->prepare(
            $log_query_raw,
            $user_id,
            $candidate_id,
            $action,
            $date
        );
        $log_inserted = $wpdb->query($log_query);

        if ($log_inserted) {
            $log_id = $wpdb->insert_id;
            return $log_id;
        } else {
            return false;
        }
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
    public function tjg_csbs_insert_new_candidate(
        string $first_name,
        string $last_name,
        string $phone,
        string $email,
        string $city,
        string $state,
        string $date,
        string $source
    ) {
        global $wpdb;
        $candidate_table = $this->candidate_table;
        $errors = [];
        $insertions = 0;

        // Clean up phone number and validate length
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) != 10) {
            array_push($errors, array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => $phone,
                'email' => $email,
                'city' => $city,
                'state' => $state,
                'date' => $date,
                'error' => 'Invalid phone number'
            ));
            return $errors;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            array_push($errors, array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => $phone,
                'email' => $email,
                'city' => $city,
                'state' => $state,
                'date' => $date,
                'error' => 'Invalid email'
            ));
            return $errors;
        }

        // Insertion requires unique phone number and unique email
        $dup_check_query = "SELECT * FROM $candidate_table WHERE phone LIKE %s OR email LIKE %s";
        $dup_check_query = $wpdb->prepare($dup_check_query, $phone, $email);
        $dup_check_result = $wpdb->get_results($dup_check_query);
        if (!empty($dup_check_result)) {
            array_push($errors, array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => $phone,
                'email' => $email,
                'city' => $city,
                'state' => $state,
                'date' => $date,
                'error' => 'Candidate already exists with this phone number or email'
            ));
            return $errors;
        }

        // If no results, insert candidate
        if (empty($dup_check_result)) {
            $candidate_query_raw = "INSERT INTO $candidate_table
            (first_name, last_name, phone, email, city, state, date_added, lead_source)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s)";
            $candidate_query = $wpdb->prepare(
                $candidate_query_raw,
                $first_name,
                $last_name,
                $phone,
                $email,
                $city,
                $state,
                $date,
                $source
            );
            $inserted = $wpdb->query($candidate_query);

            $log_inserted = $this->tjg_csbs_create_log_entry(
                get_current_user_id(),
                $wpdb->insert_id,
                'add_candidate',
                $date
            );

            if ($inserted && $log_inserted) return true;
            else if (!$inserted) {
                // get query error
                $error = $wpdb->last_error;
                return $error;
            }
        } else {
            return false;
        }
    }

    #endregion CRUD Create Functions

    #region CRUD Read Functions
    /*
    * Read  ##########################################################################################
    */

    /**
     * Get list of columns in $wpdb table
     * 
     * Returns a list of the columns in the primary table tjg_csbs_candidates
     * 
     * @return array $columns
     */
    public function get_columns()
    {
        global $wpdb;
        $table_name = $this->candidate_table;

        $columns = $wpdb->get_col("DESC $table_name", 0);

        return $columns;
    }

    /**
     * Get all candidates
     * 
     * Returns all candidates in the database table tjg_csbs_candidates
     * 
     * @return array $candidates
     */
    public function get_candidates()
    {
        global $wpdb;
        $table_name = $this->candidate_table;

        $query = "SELECT *,
        UNIX_TIMESTAMP(CONVERT_TZ(`date_added`, '+00:00', @@session.time_zone)) AS `date_added_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`date_updated`, '+00:00', @@session.time_zone)) AS `date_updated_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`date_worked`, '+00:00', @@session.time_zone)) AS `date_worked_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`date_scheduled`, '+00:00', @@session.time_zone)) AS `date_scheduled_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`confirmed_date`, '+00:00', @@session.time_zone)) AS `confirmed_date_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`interview_date`, '+00:00', @@session.time_zone)) AS `interview_date_local`
         FROM $table_name";
        $results = $wpdb->get_results($query, ARRAY_A);

        if ($results) {
            return $results;
        } else {
            $error = [
                'error' => $wpdb->last_error,
                'query' => $query,
                'results' => $results,
                'table' => $table_name,
                'columns' => $this->get_columns(),
                'last_query' => $wpdb->last_query
            ];
            error_log(print_r($error, true));
            return false;
        }

        return $results;
    }

    /**
     * Get candidate assigned to a user ID
     * 
     * Returns all candidates assigned to a user ID
     * 
     * @param int $user_id
     * @return array $candidates
     */
    public function get_candidates_assigned_to_user($user_id)
    {
        global $wpdb;
        $table_name = $this->candidate_table;

        $query = "SELECT *,
        UNIX_TIMESTAMP(CONVERT_TZ(`date_added`, '+00:00', @@session.time_zone)) AS `date_added_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`date_updated`, '+00:00', @@session.time_zone)) AS `date_updated_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`date_worked`, '+00:00', @@session.time_zone)) AS `date_worked_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`date_scheduled`, '+00:00', @@session.time_zone)) AS `date_scheduled_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`confirmed_date`, '+00:00', @@session.time_zone)) AS `confirmed_date_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`interview_date`, '+00:00', @@session.time_zone)) AS `interview_date_local`
        FROM $table_name WHERE rep_user_id = %d";
        $query = $wpdb->prepare($query, $user_id);
        $results = $wpdb->get_results($query, ARRAY_A);

        if ($results) {
            return $results;
        } else {
            $error = [
                'error' => $wpdb->last_error,
                'query' => $query,
                'results' => $results,
                'table' => $table_name,
                'columns' => $this->get_columns(),
                'last_query' => $wpdb->last_query
            ];
            // error_log(print_r($error, true));
            return false;
        }

        return $results;
    }


    /**
     * Get candidates before date
     * 
     * Returns all candidates in the database table tjg_csbs_candidates
     * with a date_added before the given date
     * 
     * @param string $date
     * @return array $candidates
     */
    public function get_candidates_before_date(string $date)
    {
        global $wpdb;
        $table_name = $this->candidate_table;

        $query = "SELECT * FROM $table_name WHERE date_added < %s";
        $query = $wpdb->prepare($query, $date);
        $results = $wpdb->get_results($query, ARRAY_A);

        return $results;
    }

    /**
     * Get candidate by ID
     * 
     * Returns a candidate from the database table tjg_csbs_candidates
     * 
     * @param int $id
     * @return array $candidate
     */
    public function get_candidate_by_id($id)
    {
        global $wpdb;
        $table_name = $this->candidate_table;

        $query = "SELECT *,
        UNIX_TIMESTAMP(CONVERT_TZ(`date_added`, '+00:00', @@session.time_zone)) AS `date_added_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`date_updated`, '+00:00', @@session.time_zone)) AS `date_updated_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`date_worked`, '+00:00', @@session.time_zone)) AS `date_worked_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`date_scheduled`, '+00:00', @@session.time_zone)) AS `date_scheduled_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`confirmed_date`, '+00:00', @@session.time_zone)) AS `confirmed_date_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`interview_date`, '+00:00', @@session.time_zone)) AS `interview_date_local`
         FROM $table_name WHERE id = %d";
        $query = $wpdb->prepare($query, $id);
        $result = $wpdb->get_row($query, ARRAY_A);

        return $result;
    }

    /**
     * Get candidate by phone number
     * 
     * Returns a candidate from the database table tjg_csbs_candidates
     * 
     * @param string $phone
     * @return array $candidate
     */
    public function get_candidate_by_phone($phone)
    {
        global $wpdb;
        $table_name = $this->candidate_table;

        $query = "SELECT *,
        UNIX_TIMESTAMP(CONVERT_TZ(`date_added`, '+00:00', @@session.time_zone)) AS `date_added_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`date_updated`, '+00:00', @@session.time_zone)) AS `date_updated_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`date_worked`, '+00:00', @@session.time_zone)) AS `date_worked_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`date_scheduled`, '+00:00', @@session.time_zone)) AS `date_scheduled_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`confirmed_date`, '+00:00', @@session.time_zone)) AS `confirmed_date_local`,
        UNIX_TIMESTAMP(CONVERT_TZ(`interview_date`, '+00:00', @@session.time_zone)) AS `interview_date_local`
         FROM $table_name WHERE phone = %s";
        $query = $wpdb->prepare($query, $phone);
        $result = $wpdb->get_row($query, ARRAY_A);

        return $result;
    }

    /**
     * Get number of calls made to candidate
     * 
     * Returns the number of calls made to a candidate
     * from the call log table
     * 
     * @todo: Create tables for calls, texts, emails, and notes
     *        with dates and update this function to use 
     *        those tables
     * 
     * @param int $id Candidate ID
     * @return int $count Number of calls made
     */
    public function get_candidate_call_count($id)
    {
        global $wpdb;
        $table = $this->call_log_table;

        $query = "SELECT COUNT(*) FROM $table WHERE candidate_id = %d";
        $query = $wpdb->prepare($query, $id);
        $count = $wpdb->get_var($query);

        // if count is null, return 0
        $count = (!is_null($count)) ? $count : 0;
        return $count;
    }

    /**
     * Get total number of calls made to all candidates
     * 
     * Get the total number of calls made to all candidates
     * Optionally filter by user ID and date range
     * 
     * @param int $user_id
     * @param string $start_date
     * @param string $end_date
     * 
     * @return int $count
     */
    public function get_total_call_count($user_id = null, $start_date = null, $end_date = null)
    {
        global $wpdb;
        $table = $this->call_log_table;

        $query = "SELECT COUNT(*) FROM $table";

        if (!is_null($user_id)) {
            $query .= " WHERE rep_user_id = %d";
            $query = $wpdb->prepare($query, $user_id);
        }

        if (!is_null($start_date) && !is_null($end_date)) {
            $query .= " AND start_time BETWEEN %s AND %s";
            $query = $wpdb->prepare($query, $start_date, $end_date);
        }

        $count = $wpdb->get_var($query);

        // if count is null, return 0
        $count = (!is_null($count)) ? $count : 0;
        return $count;
    }

    #region Cornerstone Agent Helper Functions ##############################
    /**
     * Get agent name by ID
     * 
     * Returns the name of the agent from the database table tjg_csbs_agents
     * 
     * @param int $id
     * @return string $agent_name
     */
    public function get_agent_name($user_id, $first_last = 'first_and_last', $return_type = 'array')
    {
        $user = get_user_by('id', $user_id);

        switch ($first_last) {
            case 'first':
                $name = $user->first_name;
                $return_name = ($return_type == 'array') ? ['agent_name' => $name] : $name;
                break;
            case 'last':
                $name = $user->last_name;
                $return_name = ($return_type == 'array') ? ['agent_name' => $name] : $name;
                break;
            case 'first_and_last':
                $name = $user->first_name . ' ' . $user->last_name;
                $return_name = ($return_type == 'array') ? ['agent_name' => $name] : $name;
                break;
            default:
                $name = $user->first_name . ' ' . $user->last_name;
                $return_name = ($return_type == 'array') ? ['agent_name' => $name] : $name;
                break;
        }

        return $return_name;
    }

    /**
     * Get all Cornerstone agents
     * 
     * Returns all WP Users with Cornerstone in meta tag 'agent_position'
     * 
     * @return array $agents
     */
    public function get_agents()
    {
        $args = array(
            'meta_key' => 'agent_position',
            'meta_value' => 'Cornerstone',
            'meta_compare' => '='
        );
        $agents = get_users($args);
        return $agents;
    }
    #endregion Cornerstone Agent Helper Functions ###########################
    #endregion CRUD Read Functions

    #region CRUD Update Functions
    /*
    * Update  ############################################################
    */

    /**
     * Update candidate
     * 
     * Updates a candidate in the database table tjg_csbs_candidates
     * 
     * @param int $id
     * @param string $first_name
     * @param string $last_name
     * @param string $phone
     * @param string $email
     * @param string $city
     * @param string $state
     * @param string $date
     * @return bool $updated
     */
    public function update_candidate(
        $id,
        $data
    ) {
        global $wpdb;
        $table_name = $this->candidate_table;

        // Collect variables from $data array
        $first_name = $data['first_name'];
        $last_name = $data['last_name'];
        $phone = $data['phone'];
        $email = $data['email'];
        $city = $data['city'];
        $state = $data['state'];
        $date = $data['date'];

        $update_query = "UPDATE $table_name
            SET first_name = %s,
                last_name = %s,
                phone = %s,
                email = %s,
                city = %s,
                state = %s,
                date_added = %s
            WHERE id = %d";
        $update_query = $wpdb->prepare(
            $update_query,
            $first_name,
            $last_name,
            $phone,
            $email,
            $city,
            $state,
            $date,
            $id
        );
        $updated = $wpdb->query($update_query);
        // Update date_updated to current date
        $this->updated_candidate($id);

        if ($updated) return true;
        else if (!$updated) {
            // get query error
            $error = $wpdb->last_error;
            return $error;
        }
    }

    /**
     * Update candidate email
     * 
     * Updates a candidate's email in the database table tjg_csbs_candidates
     * 
     * @param int $id
     * @param string $email
     * @return bool|string $updated or $error
     */
    public function update_candidate_email($id, $email)
    {
        global $wpdb;
        $table_name = $this->candidate_table;

        $validate_email = preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/', $email);
        if (!$validate_email) return false;

        $update_query = "UPDATE $table_name
            SET email = %s
            WHERE id = %d";
        $update_query = $wpdb->prepare(
            $update_query,
            $email,
            $id
        );
        $updated = $wpdb->query($update_query);
        // Update date_updated to current date
        $this->updated_candidate($id);

        if ($updated) return true;
        else if (!$updated) {
            // get query error
            $error = $wpdb->last_error;
            return $error;
        }
    }

    /**
     * Get date updated
     * 
     * Returns the date_updated from the database table tjg_csbs_candidates
     * 
     * @param int $id
     * @return string $date_updated
     */
    public function get_date_updated($id)
    {
        global $wpdb;
        $table_name = $this->candidate_table;

        $query = "SELECT UNIX_TIMESTAMP(CONVERT_TZ(`date_updated`, '+00:00', @@session.time_zone)) AS `date_updated` FROM $table_name WHERE id = %d";
        $query = $wpdb->prepare($query, $id);
        $result = $wpdb->get_var($query);

        return $result;
    }



    /**
     * Assign candidate to user
     * 
     * Updates a candidate in the database table tjg_csbs_candidates
     * with the user_id of the user who is assigned to the candidate
     * 
     * @param int $user_id
     * @param array $candidate_ids
     * 
     * @return bool $updated
     */
    public function assign_candidate(int $user_id, array $candidate_ids)
    {
        global $wpdb;
        $table_name = $this->candidate_table;
        $candidates_assigned = 0;
        $error_count = 0;


        foreach ($candidate_ids as $id) {

            // // Set up query
            // $update_query_raw = "UPDATE $table_name
            //     SET rep_user_id = '%d'
            //     WHERE id = '%d'";
            // $update_query = $wpdb->prepare($update_query_raw, $user_id, $id);

            // // Run query
            // $updated = $wpdb->query($update_query);

            // Update entry using wpdb->update
            $updated = $wpdb->update(
                $table_name,
                array('rep_user_id' => $user_id),
                array('id' => $id)
            );

            if ($updated == true) {
                $candidates_assigned++;
                $fresh_date = $this->updated_candidate($id);
                $log_entry = $this->tjg_csbs_create_log_entry(
                    $user_id,
                    $id,
                    'assigned_candidate',
                    $fresh_date
                );
                $payload['updated'][] = $updated;
                $payload['log_entry'][] = $log_entry;
            } else {
                $error_count++;
                // get query error
                $error = $wpdb->last_error;
                $payload['error'][] = $user_id . ' ' . $id . ' ' . $error;
            }
        }

        $payload['candidates_assigned'] = $candidates_assigned;
        $payload['error_count'] = $error_count;
        $payload['agent_name'] = $this->get_agent_name($user_id)['agent_name'];

        wp_send_json_success($payload);

        if (in_array(0, $updated)) return wp_send_json_error($updated);
        else wp_send_json_success($updated);
    }

    /**
     * Unassign candidate
     * 
     * Resets a candidate in the database table tjg_csbs_candidates,
     * removing the user_id from the rep_user_id field.
     * 
     * Add log action 'unassigned_candidate'
     * 
     * @param int $candidate_id
     * @param int $user_id
     * 
     * @return bool|string $updated - updated = 1 or error string
     */
    public function unassign_candidate(int $candidate_id, int $user_id)
    {

        global $wpdb;
        $candidate_table = $this->candidate_table;

        $updated = $wpdb->update(
            $candidate_table,
            array('rep_user_id' => null),
            array('id' => $candidate_id)
        );

        if ($updated == true) {
            $fresh_date = $this->updated_candidate($candidate_id);
            $this->tjg_csbs_create_log_entry(
                $user_id,
                $candidate_id,
                'unassigned_candidate',
                $fresh_date
            );
            return $updated;
        } else {
            // get query error
            $error = $wpdb->last_error;
            return $error;
        }
    }

    /**
     * Update candidate date_updated
     * 
     * Updates a candidate's date_updated in the database table tjg_csbs_candidates
     * 
     * @param int $id
     * @return bool $updated
     */
    public function updated_candidate($id)
    {
        global $wpdb;
        $table_name = $this->candidate_table;

        $date = date('Y-m-d H:i:s');

        $update_query = "UPDATE $table_name
            SET date_updated = %s
            WHERE id = %d";
        $update_query = $wpdb->prepare(
            $update_query,
            $date,
            $id
        );
        $updated = $wpdb->query($update_query);

        if ($updated) return true;
        else if (!$updated) {
            // get query error
            $error = $wpdb->last_error;
            return $error;
        }
    }

    /**
     * Worked candidate
     * 
     * Adds timestamp to 'date_worked' column of candidate table
     * for a given candidate ID
     * 
     * @param int $id
     * 
     * @return bool|string true or error string
     */
    public function worked_candidate($id)
    {
        global $wpdb;
        $table_name = $this->candidate_table;

        $today = date("Y-m-d H:i:s");
        $updated_worked = $wpdb->update(
            $table_name,
            array('date_worked' => $today),
            array('id' => $id)
        );

        if ($updated_worked == true) return true;
        else if (!$updated_worked) {
            $error = $wpdb->last_error;
            return $error;
        }
    }

    /**
     * Begin interview
     * 
     * Logs an interview start time and updates date_updated in
     * the database table tjg_csbs_candidates for the specified candidate
     * 
     * Return the ID of the call log entry created
     * 
     * @param int $candidate_id
     * @param int $user_id
     * 
     * @return int|string $last_log_id - int or error string
     */
    public function begin_interview(int $candidate_id, int $user_id)
    {
        global $wpdb;
        $table = $this->get_call_log_table();

        $candidate_id = intval($candidate_id);
        $rep_user_id = intval($user_id);
        $timestamp = date("Y-m-d H:i:s");
        $direction = 'outbound';

        $logged = $wpdb->insert(
            $table,
            array(
                'candidate_id' => $candidate_id,
                'rep_user_id' => $rep_user_id,
                'direction' => $direction,
                'start_time' => $timestamp
            )
        );

        $last_log_id = $wpdb->insert_id;

        if ($logged == true) {
            $fresh_date = $this->updated_candidate($candidate_id);
            $this->tjg_csbs_create_log_entry(
                $user_id,
                $candidate_id,
                'begin_interview',
                $fresh_date
            );

            return $last_log_id;
        } else {
            // get query error
            $error = $wpdb->last_error;
            return $error;
        }
    }

    /**
     * End interview
     * 
     * Adds an end_time to an interview in the database 
     * table tjg_csbs_call_log, then updates date_updated in
     * the database table tjg_csbs_candidates for the specified candidate
     * 
     * @param int $call_log_id
     * @param int $candidate_id
     * @param int $user_id
     * 
     * @return bool|string $updated - updated = 1 or error string
     */
    public function end_interview(int $call_log_id, int $candidate_id, int $user_id)
    {
        global $wpdb;
        $table = $this->get_call_log_table();

        // force types
        $call_log_id = intval($call_log_id);
        $candidate_id = intval($candidate_id);
        $rep_user_id = intval($user_id);

        // get current date/time
        $timestamp = date("Y-m-d H:i:s");

        // update call - set end_time
        $updated = $wpdb->update(
            $table,
            array('end_time' => $timestamp),
            array('id' => $call_log_id)
        );

        // update candidate date_updated
        $fresh_date = $this->updated_candidate($candidate_id);

        // Add log entry
        $this->tjg_csbs_create_log_entry(
            $rep_user_id,
            $candidate_id,
            'end_interview',
            $fresh_date
        );

        // Update candidate's date_worked column
        $this->worked_candidate($candidate_id);

        if ($updated == true) return $updated;
        else {
            // get query error
            $error = $wpdb->last_error;
            return $error;
        }
    }

    /**
     * disposition_candidate
     * 
     * Adds a disposition to a candidate in the database
     * 
     * @param int $candidate_id
     * @param string $disposition
     * @param int $user_id
     * 
     * @return bool|string $updated - updated = 1 or error string
     */
    public function disposition_candidate(int $candidate_id, string $disposition, int $user_id)
    {
        global $wpdb;
        $table = $this->candidate_table;

        $candidate_id = intval($candidate_id);
        $rep_user_id = intval($user_id);

        $updated = $wpdb->update(
            $table,
            array('disposition' => $disposition),
            array('id' => $candidate_id)
        );

        if ($updated == true) {
            $fresh_date = $this->updated_candidate($candidate_id);
            $this->tjg_csbs_create_log_entry(
                $rep_user_id,
                $candidate_id,
                'disposition_candidate',
                $fresh_date
            );
            return $updated;
        } else {
            // get query error
            $error = $wpdb->last_error;
            return $error;
        }
    }

    public function schedule_candidate(int $candidate_id, int $user_id, string $scheduled_interview_date)
    {
        global $wpdb;
        $table = $this->candidate_table;

        $candidate_id = intval($candidate_id);
        $rep_user_id = intval($user_id);
        $date_scheduled = date("Y-m-d H:i:s", strtotime($scheduled_interview_date));

        $updated = $wpdb->update(
            $table,
            array('date_scheduled' => $date_scheduled),
            array('id' => $candidate_id)
        );

        if ($updated == true) {
            $fresh_date = $this->updated_candidate($candidate_id);
            $this->tjg_csbs_create_log_entry(
                $rep_user_id,
                $candidate_id,
                'schedule_candidate',
                $fresh_date
            );
            return $updated;
        } else {
            // get query error
            $error = $wpdb->last_error;
            return $error;
        }
    }

    public function schedule_callback(int $candidate_id, string $scheduled_callback_date, int $user_id)
    {
        global $wpdb;
        $table = $this->candidate_table;

        $candidate_id = intval($candidate_id);
        $rep_user_id = intval($user_id);
        $date_scheduled = date("Y-m-d H:i:s", strtotime($scheduled_callback_date));

        $updated = $wpdb->update(
            $table,
            array('call_back_time' => $date_scheduled),
            array('id' => $candidate_id)
        );

        if ($updated == true) {
            $fresh_date = $this->updated_candidate($candidate_id);
            $this->tjg_csbs_create_log_entry(
                $rep_user_id,
                $candidate_id,
                'schedule_callback',
                $fresh_date
            );
            return $updated;
        } else {
            // get query error
            $error = $wpdb->last_error;
            return $error;
        }
    }

    #endregion CRUD Update Functions

    #region CRUD Delete Functions

    /*
    * Delete  ##########################################################################################
    */

    /**
     * Delete candidate
     * 
     * Deletes a candidate from the database table tjg_csbs_candidates
     * 
     * @param int $id
     * @return bool $deleted
     */
    public function delete_candidate($id)
    {
        global $wpdb;
        $table_name = $this->candidate_table;

        $delete_query = "DELETE FROM $table_name WHERE id = %d";
        $delete_query = $wpdb->prepare($delete_query, $id);
        $deleted = $wpdb->query($delete_query);

        if ($deleted) return true;
        else if (!$deleted) {
            // get query error
            $error = $wpdb->last_error;
            error_log($error);
            return false;
        }
    }

    #endregion CRUD Delete Functions

    #endregion CRUD Functions

    #region Helper Functions ##############################################

    public function format_phone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $phone = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $phone);
        return $phone;
    }

    // Vonage API Functions
    public function vonage_api_key()
    {
        $key = get_option('tjg_csbs_vonage_api_key');
        return $key;
    }

    public function vonage_api_secret()
    {
        $secret = get_option('tjg_csbs_vonage_api_secret');
        return $secret;
    }

    // Misc Functions

    /**
     * gf_yes_no_bool
     * 
     * Converts a Gravity Forms Yes/No field value to a boolean,
     * or returns null if the value is not a Yes or No
     *
     * @param  string|null $value - 'Yes' or 'No'
     * @return bool|null
     */
    public function gf_yes_no_bool(string $value = null)
    {
        if (is_null($value)) return null;
        return $value == 'Yes' ? true : false;
    }

    /**
     * gf_job_seeker_bool
     * 
     * Converts a Gravity Forms Job Seeker field value to a boolean,
     * or returns null if the value is not valid
     * 
     * @param  string|null $value - 'Still Looking' or 'No Longer Looking'
     * @return bool|null
     */
    public function gf_job_seeker_bool(string $value = null)
    {
        if (is_null($value)) return null;
        return $value == 'Still Looking' ? true : false;
    }

    /**
     * gf_dnc_bool
     * 
     * Converts a Gravity Forms Do Not Call field value to a boolean
     *
     * @param  string|null $value
     * @return bool|null
     */
    public function gf_dnc_bool(string $value = null)
    {
        if (is_null($value)) return null;
        return ($value == 'Remove - Do Not Call') ? true : false;
    }

    // Table Names
    public function get_candidate_table()
    {
        $table = $this->candidate_table;
        return $this->candidate_table;
    }

    public function get_log_table()
    {
        $table = $this->log_table;
        return $this->log_table;
    }

    public function get_notes_table()
    {
        $table = $this->notes_table;
        return $this->notes_table;
    }

    public function get_call_log_table()
    {
        $table = $this->call_log_table;
        return $this->call_log_table;
    }

    // Emergency Twilio Messaging
    public function twilio_message(string $number, $message)
    {
        $twilio_sid = get_option('tjg_csbs_twilio_sid');
        $twilio_token = get_option('tjg_csbs_twilio_token');
        $twilio_msid = get_option('tjg_csbs_twilio_msid');

        $client = new Client($twilio_sid, $twilio_token);

        // $number_array = explode(',', $numbers);

        // foreach ($number_array as $number) {
        try {

            $client->messages->create(
                $number,
                array(
                    'messagingServiceSid' => $twilio_msid,
                    'body' => $message
                )
            );
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
        // }

        return true;
    }

    /**
     * states_array
     * 
     * Returns an array of US states with the state abbreviation as the key
     * 
     * @return array
     */
    public function get_states() {

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

        return $states;
    }

    #endregion Helper Functions

    #region SendGrid Functions ##############################################

    /**
     * sendgrid_api_key
     * 
     * Returns the SendGrid API key
     * 
     * @return string
     */
    public function sendgrid_api_key()
    {
        $key = get_option('tjg_csbs_sendgrid_api_key');
        return $key;
    }

    /**
     * sendgrid_email_from
     * 
     * Returns the SendGrid email from address
     * 
     * @return string
     */
    public function sendgrid_email_from()
    {
        $from = get_option('tjg_csbs_sendgrid_email_from');
        return $from;
    }

    /**
     * sendgrid_email_from_name
     * 
     * Returns the SendGrid email from name
     * 
     * @return string
     */
    public function sendgrid_email_from_name() {
        $from_name = get_option('tjg_csbs_sendgrid_email_from_name');
        return $from_name;
    }

    /**
     * sendgrid_email_send_confirmation
     * 
     * Sends a confirmation email to the candidate using
     * SendGrid template located in the SendGrid account
     * 
     * Substitutions expected:
     * - {{first_name}}
     * - {{last_name}}
     * - {{briefing_date}}
     * - {{briefing_url}}
     * 
     * @param object $candidate
     * @param string $template_id
     * @param string $webinar_link
     * @return bool
     */
    public function sendgrid_email_send_confirmation(Candidate $candidate, string $template_id, string $subject_line, string $webinar_link)
    {
        // echo '<h1>sendgrid_email_send_confirmation</h1>';
        // wp_send_json($candidate, 200, JSON_PRETTY_PRINT);

        $email = new \SendGrid\Mail\Mail(); // Create a new SendGrid Mail object

        $plugin_settings = array( // Check for plugin settings
            'sendgrid_api_key' => $this->sendgrid_api_key(),
            'sendgrid_email_from' => $this->sendgrid_email_from(),
            'sendgrid_email_from_name' => $this->sendgrid_email_from_name()
        );

        if (in_array(null, $plugin_settings)) { // If any of the plugin settings are null, return false
            error_log('SendGrid plugin settings are not configured');
            return false;
        }

        // Set the email properties
        $email->setFrom($plugin_settings['sendgrid_email_from'], $plugin_settings['sendgrid_email_from_name']);
        $email->setSubject($subject_line);
        $email->addTo($candidate->email, $candidate->first_name . ' ' . $candidate->last_name);
        $email->setTemplateId($template_id);

        // Set the substitutions
        $email->addDynamicTemplateData('first_name', $candidate->first_name);
        $email->addDynamicTemplateData('last_name', $candidate->last_name);
        $email->addDynamicTemplateData('briefing_date', $candidate->confirmed_date);
        $email->addDynamicTemplateData('briefing_url', $webinar_link);

        // Create a SendGrid client to send the email
        $sendgrid = new \SendGrid($plugin_settings['sendgrid_api_key']);

        try { // Try to send the email
            $response = $sendgrid->send($email);
            return true;
        } catch (Exception $e) { // If there is an error, log it and return false
            error_log($e->getMessage());
            return false;
        }
    }
    
}
