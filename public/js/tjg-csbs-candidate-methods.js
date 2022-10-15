(function ($) {
  "use strict";

  $(document).ready(function () {
    /*
     * Listen for buttons to be clicked:
     * - tjg-csbs-candidate-interview
     * - tjg-csbs-candidate-update
     * - tjg-csbs-candidate-delete
     */
    $(document).on(
      "click",
      ".tjg-csbs-candidate-interview",
      {},
      handle_candidate_interview_click
    );

    $(document).on(
      "click",
      ".tjg-csbs-candidate-update",
      {},
      handle_candidate_update_click
    );
    $(document).on(
      "click",
      ".tjg-csbs-candidate-clear",
      {},
      handle_candidate_clear_click
    );
  });

  function handle_candidate_interview_click(event) {
    let candidateID = $(this).attr("data-id");

    // AJAX Request to Begin Interview, then
    // Redirect to Interview Page with parameters:
    // candidate_id, call_id
    $.ajax({
      type: "POST",
      url: tjg_csbs_candidates_ajax_object.ajax_url,
      data: {
        action: "tjg_csbs_primary_ajax",
        method: "begin_interview",
        nonce: tjg_csbs_candidates_ajax_object.nonce,
        id: candidateID,
        user_id: tjg_csbs_candidates_ajax_object.current_user_id,
      },
      success: function (response) {
        // console.log(response);
        window.location.href = "?interview=y&candidate_id="
          + candidateID
          + "&call_id="
          + response.data.call_id;
      },
      error: function (response) {
        console.log(response);
      },
    });
    }

  function handle_candidate_update_click() {
        console.log("handle_candidate_update_click");
      }

  function handle_candidate_clear_click() {
        let candidateID = $(this).attr("data-id");
        // display confirmation dialog
        let confirm = window.confirm(
          "Are you sure you want to clear this candidate?"
        );
        if (confirm) {
          // AJAX request to Unassign Candidate
          $.ajax({
            url: tjg_csbs_candidates_ajax_object.ajax_url,
            type: "POST",
            data: {
              action: "tjg_csbs_primary_ajax",
              method: "unassign_candidate",
              nonce: tjg_csbs_candidates_ajax_object.nonce,
              id: candidateID,
              user_id: tjg_csbs_candidates_ajax_object.current_user_id,
            },
            success: function (response) {
              console.log(response);
              // Redraw the table
              $("#tjg-csbs-candidate-table").DataTable().ajax.reload();
            },
            error: function (error) {
              console.log(error);
            },
          });
        }
      }
}) (jQuery);
