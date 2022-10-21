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
            // $this->populate_candidate_information($this);
        }
    }
    
    private function populate_candidate_information(object $candidate) {
    }

}