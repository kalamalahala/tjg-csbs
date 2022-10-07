<?php

/**
 * Methods to include in the Public and Admin classes
 */

require_once plugin_dir_path(dirname(__FILE__)) . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Tjg_Csbs_Common
{

    private $candidate_table;

    public function __construct()
    {

        if (defined('TJG_CSBS_TABLE_NAME')) {
            $this->candidate_table = TJG_CSBS_TABLE_NAME;
        } else {
            global $wpdb;
            $this->candidate_table = $wpdb->prefix . 'tjg_csbs_candidates';
        }

        if (defined('TJG_CSBS_LOG_TABLE_NAME')) {
            $this->log_table = TJG_CSBS_LOG_TABLE_NAME;
        } else {
            global $wpdb;
            $this->log_table = $wpdb->prefix . 'tjg_csbs_log';
        }

    }

    public function get_candidate_table()
    {
        $table = $this->candidate_table;
        return $this->candidate_table;
    }

    #region Spreadsheet Handlers #############################################################################

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

                // Format phone number
                $phone = preg_replace('/[^0-9]/', '', $phone);

                // Format name
                $first_name = preg_replace('/[^A-Za-z]/', '', $first_name);
                $last_name = preg_replace('/[^A-Za-z]/', '', $last_name);

                // Add current date and time
                $date = date('Y-m-d H:i:s');

                // Insert candidate based on mode
                switch ($mode) {
                    case 'db':
                        $inserted = $this->tjg_csbs_insert_new_candidate(
                            $first_name,
                            $last_name,
                            $phone,
                            $email,
                            $city,
                            $state,
                            $date
                        );
                        break;
                    case 'gf':
                        $inserted = $this->tjg_csbs_gf_insert_new_candidate(
                            $first_name,
                            $last_name,
                            $phone,
                            $email,
                            $city,
                            $state,
                            $date
                        );
                        break;
                    default:
                        wp_send_json_error('Invalid mode');
                        die();
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
        } else {
            unlink($upload['file']);
            wp_send_json_error('Error loading file');
            die();
        }

        unlink($upload['file']);
        wp_send_json_error('Error loading file');
        die();
    }
    #endregion Spreadsheet

    #region CRUD Operations for Candidates ###################################################################

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
        string $date
    ) {
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
        $candidate_table = $this->candidate_table;
        $log_table = $this->log_table;
        $duplicates = [];
        $insertions = 0;

        // Select candidates with Date Added before now
        $query = "SELECT * FROM $candidate_table WHERE phone LIKE %s AND date_added < %s";
        $query = $wpdb->prepare($query, $phone, $date);
        $result = $wpdb->get_results($query);

        // If no results, insert candidate
        if (empty($result)) {
            $candidate_query_raw = "INSERT INTO $candidate_table
            (first_name, last_name, phone, email, city, state, date_added)
            VALUES (%s, %s, %s, %s, %s, %s, %s)";
            $candidate_query = $wpdb->prepare(
                $candidate_query_raw,
                $first_name,
                $last_name,
                $phone,
                $email,
                $city,
                $state,
                $date
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

    public function tjg_csbs_gf_insert_new_candidate(
        $first_name,
        $last_name,
        $phone,
        $email,
        $city,
        $state,
        $date
    ) {
        // Collect form ID from Plugin Settings
        $form_id = get_option('tjg_csbs_gravity_forms_id');
        if (!$form_id) {
            echo 'No Gravity Form ID set';
            die();
        }

        // Check if candidate already exists by phone number
        $search_criteria = array(
            'status' => 'active',
            'field_filters' => array(
                array(
                    'key' => '3',
                    'value' => $phone
                )
            )
        );
        $entry = GFAPI::get_entries($form_id, $search_criteria);

        // If no entry exists, insert candidate
        if (empty($entry)) {
            $entry = array(
                'form_id' => $form_id,
                'date_created' => $date,
                'created_by' => 1,
                '1.3' => $first_name,
                '1.6' => $last_name,
                '3' => $phone,
                '4' => $email,
                '6' => $city,
                '7' => $state
            );

            // Insert entry try/catch
            try {
                // @php-ignore
                $inserted = GFAPI::add_entry($entry);
            } catch (Exception $e) {
                $error = $e->getMessage();
                return $error;
            }
        }
        if ($inserted) return true;
        else return false;
    }

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

        $query = "SELECT * FROM $table_name";
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
    public function get_candidates_assigned_to_user($user_id) {
        global $wpdb;
        $table_name = $this->candidate_table;

        $query = "SELECT * FROM $table_name WHERE rep_user_id = %d";
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
            error_log(print_r($error, true));
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

        $query = "SELECT * FROM $table_name WHERE id = %d";
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

        $query = "SELECT * FROM $table_name WHERE phone = %s";
        $query = $wpdb->prepare($query, $phone);
        $result = $wpdb->get_row($query, ARRAY_A);

        return $result;
    }

    /**
     * Get agent name by ID
     * 
     * Returns the name of the agent from the database table tjg_csbs_agents
     * 
     * @param int $id
     * @return string $agent_name
     */
    public function get_agent_name($user_id)
    {
        $user = get_user_by('id', $user_id);
        $name['agent_name'] = $user->first_name . ' ' . $user->last_name;
        return $name;
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

    /*
    * Update  ##########################################################################################
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

        $query = "SELECT date_updated FROM $table_name WHERE id = %d";
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

    #endregion

    #region Helper Functions ##############################################################################

    public function format_phone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $phone = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $phone);
        return $phone;
    }
}
