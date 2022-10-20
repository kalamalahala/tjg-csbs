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
    public $date_created;
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
            $this->populate_candidate_information($this);
        }
    }
    
    public function populate_candidate_information(object $candidate) {
        $methods = new Tjg_Csbs_Common();
        $candidate_array = $methods->get_candidate_by_id($candidate->id);
        $candidate->first_name = $candidate_array['first_name'];
        $candidate->last_name = $candidate_array['last_name'];
        $candidate->email = $candidate_array['email'];
        $candidate->phone = $candidate_array['phone'];
        $candidate->city = $candidate_array['city'];
        $candidate->state = $candidate_array['state'];
        $candidate->date_created = $candidate_array['date_created'];
        $candidate->date_updated = $candidate_array['date_updated'];
        $candidate->date_worked = $candidate_array['date_worked'];
        $candidate->date_scheduled = $candidate_array['date_scheduled'];
        $candidate->call_back_time = $candidate_array['call_back_time'];
        $candidate->disposition = $candidate_array['disposition'];
        $candidate->confirmed_date = $candidate_array['confirmed_date'];
        $candidate->rep_user_id = $candidate_array['rep_user_id'];
        $candidate->interview_date = $candidate_array['interview_date'];
        $candidate->merge_status = $candidate_array['merge_status'];
        $candidate->lead_source = $candidate_array['lead_source'];

        return $candidate;
    }
}