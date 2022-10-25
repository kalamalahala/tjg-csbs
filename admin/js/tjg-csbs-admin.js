(function ($) {
  "use strict";

  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  /*
   * DataTables Extensions List
   * https://datatables.net/extensions/
   * Buttons, HTML5 Export, DateTime, FixedHeader, Responsive, Scroller, SearchBuilder, SearchPanes, Select
   */

  $(document).ready(function () {
    console.log("Datatables loading...");
    console.log("Getting list of agent IDs and names...");

    // const agentList = ajax_object.agent_list;
    const ajax_url = ajax_object.ajax_url;
    const ajax_nonce = ajax_object.nonce;
    const ajax_action = ajax_object.action;
    const agentList = ajax_object.agent_list;
    const dtMethod = "get_candidates";
    const dtUrlString =
      ajax_url +
      "?action=" +
      ajax_action +
      "&method=" +
      dtMethod +
      "&nonce=" +
      ajax_nonce;

    console.log(agentList);

    // DataTables AJAX init
    $("#tjg-csbs-candidates").DataTable({
      ajax: {
        url: dtUrlString,
        dataSrc: "data",
      },
      columns: [
        { defaultContent: "" },
        { data: "id" },
        { data: "date_added_local" },
        { data: "date_updated_local" },
        { data: "first_name" },
        { data: "last_name" },
        { data: "email" },
        { data: "phone" },
        { data: "city" },
        { data: "state" },
        { data: "disposition" },
        { data: "merge_status" },
        { data: "lead_source" },
        { data: "rep_user_id" },
        { defaultContent: "" },
      ],
      createdRow: function (row, data, dataIndex) {
        let id = data.id ? data.id : "";
        let rep_id = data.rep_user_id ? data.rep_user_id : "";
        let disposition = data.disposition ? data.disposition : "";
        let lead_source = data.lead_source ? data.lead_source : "";

        // add data-id attribute to row
        $(row).attr("data-id", id);
        // add data-agent attribute to row
        $(row).attr("data-agent", rep_id);
        // add data-disposition attribute to row
        $(row).attr("data-disposition", disposition);
        // add data-lead-source attribute to row
        $(row).attr("data-lead-source", lead_source);
      },
      processing: true,
      language: {
        searchPanes: {
          clearMessage: "Clear All Filters",
          collapse: {
            0: "Filter Candidates",
            _: "Filter Candidates (%d)",
          },
        },
        loadingRecords: "Loading Candidates...",
      },
      columnDefs: [
        {
          targets: [0], // Column: Select
          orderable: false,
          searchable: false,
          className: "select-checkbox",
        },
        {
          targets: [1], // Column: ID
          visible: false,
        },
        {
          targets: [2, 3], // Column: Date Added, Date Updated
          render: function (data, type, row) {
            // return '' if data is null or undefined
            if (!data) {
              return "";
            }
            // parse Unix epoch timestamp to date string
            let date = new Date(data * 1000);
            // MM/DD/YYYY <br> HH:MM:SS AM/PM
            let dateString = date.toLocaleString();
            let dateArray = dateString.split(",");
            let dateFormatted = dateArray[0] + "<br>" + dateArray[1];
            return dateFormatted;
          },
        },
        {
          targets: [6], // Column: Email
          render: function (data, type, row) {
            /* Render email as a mailto link */
            return (
              '<a href="mailto:' +
              data +
              '"' +
              'target="_blank" title="Email ' +
              row.first_name +
              " " +
              row.last_name +
              '">' +
              data +
              "</a>"
            );
          },
        },
        {
          targets: [7], // Column: Phone
          render: function (data, type, row) {
            /* Render phone as a tel link
             *  title="Call {first_name} {last_name}"
             */
            return (
              '<a href="tel:' +
              data +
              '"' +
              'target="_blank" title="Call ' +
              row.first_name +
              " " +
              row.last_name +
              '">' +
              data +
              "</a>"
            );
          },
        },
        {
          targets: [11], // Column: Merge Status
          render: function (data, type, row) {
            // Convert merge_status first letter to uppercase if not null
            if (data !== null) {
              return data.charAt(0).toUpperCase() + data.slice(1);
            } else {
              return "";
            }
          }
        },
        {
          // Skip City, State, Disposition, Lead Source
          targets: [13], // Column: Rep User ID
          render: function (data, type, row) {
            // return '' if data is null or undefined
            if (data === null || data === undefined) {
              return "";
            }
            /* Render rep name from agentList */
            return agentList[data].agent_name;
          },
        },
        {
          targets: [-1], // Column: Actions
          orderable: false,
          searchable: false,
          render: function (data, type, row) {
            /* Render actions as a button group */
            // bootstrap 4 small button: View
            // Get the candidate id from the ajax request
            const idString = row.id.toString();
            const viewButton = '<a href="?page=tjg-csbs-admin-view-candidate&candidate_id='+idString+'" class="btn btn-sm btn-primary tjg-csbs-candidate-view" title="View Candidate">View</a>';
            const actionPopover = '<div class="btn-group dropleft tjg-csbs-candidate-actions" role="group" aria-label="Candidate Actions"><button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button><div class="dropdown-menu"><a class="dropdown-item tjg-csbs-candidate-view" href="?page=tjg-csbs-admin-view-candidate&candidate_id='+idString+'" title="View Candidate"><i class="fa fa-eye"></i> View</a><a class="dropdown-item tjg-csbs-candidate-update" href="#" data-id="'+idString+'" title="Update Candidate"><i class="fa fa-edit"></i> Update</a><div class="dropdown-divider"></div><a class="dropdown-item tjg-csbs-candidate-delete text-danger" href="#" data-id="'+idString+'" title="Delete Candidate"><i class="fa fa-exclamation-triangle"></i> Delete</a></div></div>';
            return actionPopover;
          },
        },
      ],
      buttons: {
        dom: {
          button: {
            className: "btn mb-2",
          },
        },
        buttons: [
          //   {
          //     extend: "selectAll",
          //     className: "btn-primary",
          //     text: "Select All",
          //   },
          {
            text: "Select Visible",
            action: function (e, dt, node, config) {
              dt.rows({ search: "applied" }).select();
            },
          },
          {
            extend: "selectNone",
            className: "btn-secondary",
            text: "De-select All",
          },
          {
            extend: "searchPanes",
            config: {
              cascadePanes: true,
            },
          },
        ],
      },
      select: {
        style: "multi",
        selector: "td:first-child",
      },
      order: [[1, "desc"]],
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100, 250, 500],
      responsive: true,
      dom: 'Bfl<"select-agent">rtip',
    });

    // Add Select Agent dropdown
    $("div.select-agent").html(
      '<form id="tjg-csbs-select-agent-form">' +
        '<label for="tjg-csbs-select-agent">Select Agent: </label>' +
        '<select id="tjg-csbs-select-agent" class="form-control"><option value="0">Select Agent</option></select>' +
        '<button id="tjg-csbs-assign-agent-btn" class="btn btn-primary btn-sm ml-2">Assign</button>' +
        "</form>"
    );

    // Populate Select Agent dropdown
    $.ajax({
      url: ajax_object.ajax_url,
      type: "POST",
      data: {
        action: "tjg_csbs_admin",
        method: "get_agents",
        nonce: ajax_object.nonce,
      },
      success: function (response) {
        var agents = response.data[0];
        $.each(agents, function (key, value) {
          // Ajax request the First Name and Last Name of the Agent
          $.ajax({
            url: ajax_object.ajax_url,
            type: "POST",
            data: {
              action: "tjg_csbs_admin",
              method: "get_agent_name",
              agent_id: value.data.ID,
              nonce: ajax_object.nonce,
            },
            success: function (nameCheck) {
              $("#tjg-csbs-select-agent").append(
                '<option value="' +
                  value.data.ID +
                  '">' +
                  nameCheck.data[0].agent_name +
                  "</option>"
              );
            },
          });
        });
      },
    });

    $("#tjg-csbs-candidates tbody").on(
      "click",
      "button.tjg-csbs-delete",
      function (e) {
        e.preventDefault();

        // Show confirmation dialog
        var r = confirm("Are you sure you want to delete this candidate?");
        if (r == true) {
          // Get ID from data-id attribute
          var id = $(this).data("id");
          // Get row
          var row = $(this).closest("tr");

          // Send AJAX to Delete Candidate
          $.ajax({
            url: ajax_object.ajax_url,
            type: "POST",
            data: {
              action: "tjg_csbs_admin",
              nonce: ajax_object.nonce,
              method: "delete_candidate",
              id: id,
            },
            success: function (response) {
              console.log(response);
              // Remove row from table
              $("#tjg-csbs-candidates").DataTable().row(row).remove().draw();
            },
            error: function (error) {
              console.log(error);
            },
          });
          console.log("Delete button clicked for ID: " + id);
        } else {
          console.log("Delete cancelled");
        }
      }
    );

    // Update Candidate
    $(document).on('click', '.tjg-csbs-candidate-update', function(e) {
      e.preventDefault();
      // Get ID from data-id attribute
      var id = $(this).data("id");
      // Get row
      var row = $(this).closest("tr");
      // get data from row
      var data = $("#tjg-csbs-candidates").DataTable().row(row).data();
      console.log(data);

      // set values in modal
      $('#tjg_csbs_first_name').val(data.first_name);
      $('#tjg_csbs_last_name').val(data.last_name);
      $('#tjg_csbs_email').val(data.email);
      $('#tjg_csbs_phone').val(data.phone);
      $('#tjg_csbs_email').val(data.email);
      $('#tjg_csbs_city').val(data.city);
      $('#tjg_csbs_state').val(data.state);

      // Show modal and populate form with candidate data
      $('#updateForm').modal('show');
    });
    
    // Delete Candidate
    $(document).on('click', '.tjg-csbs-candidate-delete', function(e) {
      e.preventDefault();

      // Show confirmation dialog
      var r = confirm('Are you sure you want to delete this candidate?');
      if (r == true) {
        // Get ID from data-id attribute
        var id = $(this).data('id');

        // Send AJAX to Delete Candidate
        $.ajax({
          url: ajax_object.ajax_url,
          type: 'POST',
          data: {
            action: 'tjg_csbs_admin',
            nonce: ajax_object.nonce,
            method: 'delete_candidate',
            id: id
          },
          success: function(response) {
            // redraw table
            $('#tjg-csbs-candidates').DataTable().ajax.reload();
          },
          error: function(error) {
            console.log(error);
          }
        });

      }
    });


    $("#tjg-csbs-assign-agent-btn").on("click", function (e) {
      e.preventDefault();
      // disable button
      $(this).prop("disabled", true);

      // Add rotating spinner
      $(this).html("Assigning...");

      // Get selected agent ID
      const agent_id = $("#tjg-csbs-select-agent").val();

      // Get candidate IDs from selected rows
      const candidate_ids = [];
      const selected_rows = $.map(
        $("#tjg-csbs-candidates").DataTable().rows(".selected").nodes(),
        function (item) {
          return $(item).data("id");
        }
      );
      // thank you https://stackoverflow.com/a/45563761/18775226

      // If no candidates are selected, or no agent is selected, show error message
      if (selected_rows.length == 0) {
        alert("Please select at least one candidate.");
        console.log(selected_rows);
        $(this).prop("disabled", false);
        $(this).html("Assign");
        return;
      } else if (agent_id == 0) {
        alert("Please select an agent.");
        $(this).prop("disabled", false);
        $(this).html("Assign");
        return;
      }

      $.ajax({
        url: ajax_object.ajax_url,
        type: "POST",
        data: {
          action: "tjg_csbs_admin",
          method: "assign_candidate",
          nonce: ajax_object.nonce,
          agent_id: agent_id,
          candidate_ids: selected_rows,
        },
        success: function (response) {
          // enable button
          $("#tjg-csbs-assign-agent-btn").prop("disabled", false);
          // remove spinner
          $("#tjg-csbs-assign-agent-btn").html("Assign");
          // clear selected rows
          $("#tjg-csbs-candidates").DataTable().rows(".selected").deselect();
          // clear selected agent
          $("#tjg-csbs-select-agent").val(0);
          // reload table
          $("#tjg-csbs-candidates").DataTable().ajax.reload();

          // show success message for 5 seconds, then hide, include number of candidates assigned
          let success_message = $(document.createElement("div"));
          success_message.addClass(
            "alert alert-success alert-dismissible fade show"
          );
          success_message.attr("id", "tjg-csbs-assign-agent-success");
          success_message.attr("role", "alert");
          success_message.html(
            "Successfully assigned " +
              response.data[0].candidates_assigned +
              " candidates to " +
              response.data[0].agent_name +
              " with " +
              response.data[0].error_count +
              " errors."
          );
          $("#tjg-csbs-select-agent-form").append(success_message);
          setTimeout(function () {
            $("#tjg-csbs-assign-agent-success").remove();
          }, 5000);

          console.log(response);
        },
        error: function (response) {
          console.log(response.responseText);
        },
      });
    });

    // Listen for Bulk Message submit
    $(document).on("submit", "#bulk-form", function (e) {
      e.preventDefault();

      // disable button
      $("#form-submit").prop("disabled", true);

      const numbers = $("#phone-numbers").val();
      const message = $("#message").val();

      // convert numbers to array by new line
      const numbers_array = numbers.split(/\r?\n/);

      // Send AJAX to send message
      $.ajax({
        url: ajax_object.ajax_url,
        type: "POST",
        data: {
          action: "tjg_csbs_admin",
          nonce: ajax_object.nonce,
          method: "send_bulk_sms",
          numbers: numbers_array,
          message: message,
        },
        success: function (response) {
          console.log(response);
          // enable button
          $("#form-submit").prop("disabled", false);
          // clear form
          $("#bulk-form").trigger("reset");
          // dump response to textarea
          const stringResponse = JSON.stringify(response);
          console.log(stringResponse);

          $("#phone-numbers").val(stringResponse);
        },
        error: function (response) {
          // enable button
          $("#form-submit").prop("disabled", false);
          console.log(response);
        },
      });

      console.log("bulk submit");
      console.log(numbers_array);
      console.log(message);
    });

    // Create single candidate: form #create-single-candidate
    $(document).on("submit", "#create-single-candidate", function (e) {
      e.preventDefault();

      // disable button
      $("#create-single-candidate-submit").prop("disabled", true);

      // Get form data with FormData
      let form_data = new FormData(this);
      form_data.append("action", "tjg_csbs_admin");
      form_data.append("nonce", ajax_object.nonce);
      form_data.append("method", "create_single_candidate");

      let successMessage = $('#success');
      let errorMessage = $('#error');

      // Send AJAX to create candidate
      $.ajax({
        url: ajax_object.ajax_url,
        type: "POST",
        contentType: false,
        processData: false,
        data: form_data,
        success: function (response) { // wp_send_json always returns here, so handle logic here
            // enable button
            $("#create-single-candidate-submit").prop("disabled", false);
            // clear form
            $("#create-single-candidate").trigger("reset");
            // show success message
            successMessage.prop('hidden', false);
            errorMessage.prop('hidden', true);
  
            // fade out success message after 5 seconds
            setTimeout(function () {
              successMessage.prop('hidden', true);
            }, 5000);
        },
        error: function (response) {
          // enable button
          $("#create-single-candidate-submit").prop("disabled", false);

          // append error message to error div
          errorMessage.html('<strong>Error</strong>: ' + response.responseJSON.data);
          errorMessage.prop('hidden', false);
          successMessage.prop('hidden', true);

          setTimeout(function () {
            errorMessage.prop('hidden', true);
          }, 5000);
          console.log(response);
        },
      });
    });

    // View Candidate page actions
    const updateButton    = $("#update-candidate");
    const deleteButton    = $("#delete-candidate");
    const sendEmailButton = $("#send-email");

    // Email AJAX
    $(document).on("click", "#send-email", function (e) {
      e.preventDefault();

      // disable button
      sendEmailButton.prop("disabled", true);

      // Confirm action
      if (!confirm("Are you sure you want to send an email to this candidate?")) {
        sendEmailButton.prop("disabled", false);
        return;
      } else {
        let candidate_id = $(this).data("id");
        // Send AJAX to send email
        $.ajax({
          url: ajax_object.ajax_url,
          type: "POST",
          data: {
            action: "tjg_csbs_admin",
            nonce: ajax_object.nonce,
            method: "send_confirmation_email",
            candidate_to_email: candidate_id,
          },
          success: function (response) {
            // enable button
            sendEmailButton.prop("disabled", false);
            console.log(response);
          },
          error: function (response) {
            console.log(response);
          },
        }); // end AJAX

      } // end else
    }); // end sendEmailButton click


  });
})(jQuery);

/*
$(document).ready(function() {
	$('#example').DataTable.ext.pager.numbers_length = 4;
	  $('#example').DataTable( {
		  "fnDrawCallback": function( oSettings ){
		  console.log("in");
			  },
			  dom: 'Bfrtip',
	   buttons: {
		buttons: [
			  { extend: 'copy', className: 'btn btn-success'},
			  { extend: 'excel', className: 'excelButton' }
				 ],
		 dom: {
			button: {
			className: 'btn'
			   }
		 }
	   },
		  "footerCallback": function ( row, data, start, end, display ) {
			  var api = this.api();
			  var intVal = function ( i ) {
				console.log(i, numeral(i).value());
				  return typeof i === 'string' ?
						  numeral(i).value() : i;};
			  total = api
				  .column( 2 )
				  .data()
				  .reduce( function (a, b) {
					  return intVal(a) + intVal(b);
				  }, 0 );
			  pageTotal = api
				  .column( 2, { page: 'current'} )
				  .data()
				  .reduce( function (a, b) {
					  return intVal(a) + intVal(b);
				  }, 0 );
			  total = numeral(total).format('0.0a');
			  pageTotal = numeral(pageTotal).format('0.0a');
						  $( api.column( 2 ).footer() ).html(
				  '--'+pageTotal +' ( --'+ total +' total)'
			  );
		  }
	  } );
  } );
  */
