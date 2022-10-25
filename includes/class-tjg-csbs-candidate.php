<?php

/**
 * Candidate object handler
 * 
 * @package     Tjg_Csbs
 * @subpackage  Tjg_Csbs/includes
 * @since       1.0.0
 * @author      Tyler Karle <tyler.karle@icloud.com>
 * 
 * @see         Tjg_Csbs_Common
 * 
 */
class Candidate 
{

    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $address;
    public $city;
    public $state;
    public $date_added;
    public $date_updated;
    public $date_worked;
    public $date_scheduled;
    public $call_back_time;
    public $disposition;
    public $confirmed_date;
    public $rep_user_id;
    public $interview_date;
    public $merge_status;
    public $lead_source;
    public $sg_message_id;
    public $sg_timestamp;
    
    public $candidate_table;

    public function __construct($id = null)
    {
        if ($id) {
            $this->id = $id;
            $methods = new Tjg_Csbs_Common();
            $candidate_array = $methods->get_candidate_by_id($id);
            $this->first_name = $candidate_array['first_name'];
            $this->last_name = $candidate_array['last_name'];
            $this->email = $candidate_array['email'];
            $this->phone = $candidate_array['phone'];
            $this->city = $candidate_array['city'];
            $this->state = $candidate_array['state'];
            $this->date_added = $candidate_array['date_added'];
            $this->date_updated = $candidate_array['date_updated'];
            $this->date_worked = $candidate_array['date_worked'];
            $this->date_scheduled = $candidate_array['date_scheduled'];
            $this->call_back_time = $candidate_array['call_back_time'];
            $this->disposition = $candidate_array['disposition'];
            $this->confirmed_date = $candidate_array['confirmed_date'];
            $this->rep_user_id = $candidate_array['rep_user_id'];
            $this->interview_date = $candidate_array['interview_date'];
            $this->merge_status = $candidate_array['merge_status'];
            $this->lead_source = $candidate_array['lead_source'];
            $this->sg_message_id = $candidate_array['sg_message_id'];
            $this->sg_timestamp = $candidate_array['sg_timestamp'];
        }

        global $wpdb;
        $this->candidate_table = (defined('TJG_CSBS_TABLE_NAME'))
            ? TJG_CSBS_TABLE_NAME : $wpdb->prefix . 'tjg_csbs_candidates';
    }

    #region Getters and Setters
    // Getters
    public static function get_table(): string {
        $empty_candidate = new Candidate();
        return $empty_candidate->candidate_table;
    }

    public function get_id(): int
    {
        return $this->id;
    }

    public function get_first_name(): string
    {
        return $this->first_name;
    }

    public function get_last_name(): string
    {
        return $this->last_name;
    }

    public function get_email(): string
    {
        return $this->email;
    }

    public function get_phone(): string
    {
        return $this->phone;
    }

    public function get_city(): string
    {
        return $this->city;
    }

    public function get_state(): string
    {
        return $this->state;
    }

    public function get_date_added(): Date
    {
        return $this->date_added;
    }

    public function get_date_updated(): Date
    {
        return $this->date_updated;
    }

    public function get_date_worked(): Date
    {
        return $this->date_worked;
    }

    public function get_date_scheduled(): Date
    {
        return $this->date_scheduled;
    }

    public function get_call_back_time(): Date
    {
        return $this->call_back_time;
    }

    public function get_disposition(): string
    {
        return $this->disposition;
    }

    public function get_confirmed_date(): Date
    {
        return $this->confirmed_date;
    }

    public function get_rep_user_id(): int
    {
        return $this->rep_user_id;
    }

    public function get_interview_date(): Date
    {
        return $this->interview_date;
    }

    public function get_merge_status(): string
    {
        return $this->merge_status;
    }

    public function get_lead_source(): string
    {
        return $this->lead_source;
    }

    public function get_sg_message_id(): string
    {
        return $this->sg_message_id;
    }

    public function get_sg_timestamp(): Date
    {
        return $this->sg_timestamp;
    }

    // Setters
    public function set_first_name(string $first_name)
    {
        $this->first_name = $first_name;
    }

    public function set_last_name(string $last_name)
    {
        $this->last_name = $last_name;
    }

    public function set_email(string $email)
    {
        $this->email = $email;
    }

    public function set_phone(string $phone)
    {
        $this->phone = $phone;
    }

    public function set_city(string $city)
    {
        $this->city = $city;
    }

    public function set_state(string $state)
    {
        $this->state = $state;
    }

    public function set_date_added(Date $date_added)
    {
        $this->date_added = $date_added;
    }

    public function set_date_updated(Date $date_updated)
    {
        $this->date_updated = $date_updated;
    }

    public function set_date_worked(Date $date_worked)
    {
        $this->date_worked = $date_worked;
    }

    public function set_date_scheduled(Date $date_scheduled)
    {
        $this->date_scheduled = $date_scheduled;
    }

    public function set_call_back_time(Date $call_back_time)
    {
        $this->call_back_time = $call_back_time;
    }

    public function set_disposition(string $disposition)
    {
        $this->disposition = $disposition;
    }

    public function set_confirmed_date(Date $confirmed_date)
    {
        $this->confirmed_date = $confirmed_date;
    }

    public function set_rep_user_id(int $rep_user_id)
    {
        $this->rep_user_id = $rep_user_id;
    }

    public function set_interview_date(Date $interview_date)
    {
        $this->interview_date = $interview_date;
    }

    public function set_merge_status(string $merge_status)
    {
        $this->merge_status = $merge_status;
    }

    public function set_lead_source(string $lead_source)
    {
        $this->lead_source = $lead_source;
    }

    public function set_sg_message_id(string $sg_message_id)
    {
        $this->sg_message_id = $sg_message_id;
    }

    public function set_sg_timestamp(Date $sg_timestamp)
    {
        $this->sg_timestamp = $sg_timestamp;
    }
    #endregion

    // Methods

    /**
     * save_candidate
     * 
     * Saves the candidate to the database 
     * 
     * @param  Candidate $candidate
     * @return void
     */
    public function save() {
        global $wpdb;
        $table = $this->candidate_table;

        $data = array(
            'first_name' => $this->get_first_name(),               // string
            'last_name' => $this->get_last_name(),                 // string
            'email' => $this->get_email(),                         // string
            'phone' => $this->get_phone(),                         // string
            'city' => $this->get_city(),                           // string
            'state' => $this->get_state(),                         // string
            'date_added' => $this->get_date_added(),               // Date
            'date_updated' => $this->get_date_updated(),           // Date
            'date_worked' => $this->get_date_worked(),             // Date
            'date_scheduled' => $this->get_date_scheduled(),       // Date
            'call_back_time' => $this->get_call_back_time(),       // Date
            'disposition' => $this->get_disposition(),             // string
            'confirmed_date' => $this->get_confirmed_date(),       // Date
            'rep_user_id' => $this->get_rep_user_id(),             // int
            'interview_date' => $this->get_interview_date(),       // Date
            'merge_status' => $this->get_merge_status(),           // string
            'lead_source' => $this->get_lead_source(),             // string
            'sg_message_id' => $this->get_sg_message_id(),         // string
            'sg_timestamp' => $this->get_sg_timestamp()            // Date
        );

        $entry_format =
            array(
                '%s',   // first_name
                '%s',   // last_name
                '%s',   // email
                '%s',   // phone
                '%s',   // city
                '%s',   // state
                '%s',   // date_added
                '%s',   // date_updated
                '%s',   // date_worked
                '%s',   // date_scheduled
                '%s',   // call_back_time
                '%s',   // disposition
                '%s',   // confirmed_date
                '%d',   // rep_user_id
                '%s',   // interview_date
                '%s',   // merge_status
                '%s',   // lead_source
                '%s',   // sg_message_id
                '%s'    // sg_timestamp
            );

        // Update candidate by id
        if ($this->get_id() != null) {
            $where = array('id' => $this->get_id());
            $where_format = array('%d');
            try {
                $wpdb->update($table, $data, $where, $entry_format, $where_format);
            } catch (Exception $e) {
                error_log('Error updating candidate ' . $this->get_first_name() . ' ' . $this->get_last_name());
                error_log('WPDB Error: ' . $wpdb->last_error);
                error_log('WPDB Query' . $wpdb->last_query);
                error_log('Exception: ' . $e->getMessage());
            }
        } else {
            // Insert new candidate if email and phone are not already in the database
            try {
                $wpdb->insert($table, $data, $entry_format);
            } catch (Exception $e) {
                error_log('Error inserting candidate ' . $this->get_first_name() . ' ' . $this->get_last_name());
                error_log('WPDB Error: ' . $wpdb->last_error);
                error_log('WPDB Query' . $wpdb->last_query);
                error_log('Exception: ' . $e->getMessage());
            }
        }
    } // end save_candidate

    public static function email_exists(string $email): bool
    {
        global $wpdb;
        $table = Candidate::get_table();
        $query = "SELECT * FROM $table WHERE email = '$email'";
        $result = $wpdb->get_results($query);
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    } // end email_exists

    public static function phone_exists(string $phone): bool
    {
        global $wpdb;
        $table = Candidate::get_table();
        $query = "SELECT * FROM $table WHERE phone = '$phone'";
        $result = $wpdb->get_results($query);
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    } // end phone_exists
}