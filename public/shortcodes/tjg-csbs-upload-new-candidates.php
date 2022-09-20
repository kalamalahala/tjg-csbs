<?php

function new_candidate_form() {

    // Create upload form layout using bootstrap classes
    $new_candidate_form = '<div class="csbs_form_wrapper">';
    $new_candidate_form .= '<form id="csbs_upload_new_candidates" method="post" enctype="multipart/form-data">';
    $new_candidate_form .= '<div class="form-group">';
    $new_candidate_form .= '<label for="csbs_upload_new_candidates">Upload New Candidates</label>';
    $new_candidate_form .= '<input type="file" class="form-control-file" id="csbs_upload_new_candidates" name="csbs_upload_new_candidates">';
    $new_candidate_form .= '</div>';
    $new_candidate_form .= '<button type="submit" class="btn btn-primary">Submit</button>';
    $new_candidate_form .= '</form>'; 
    $new_candidate_form .= '</div>'; // End csbs_form_wrapper
    return $new_candidate_form;
}