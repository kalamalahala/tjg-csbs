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
    console.log("Loading DT AJAX");

    const ajax_url = ajax_object.ajax_url;
    const ajax_nonce = ajax_object.nonce;
    const ajax_action = ajax_object.action;
    const dtMethod = "get_candidates";
    const dtUrlString =
      ajax_url +
      "?action=" +
      ajax_action +
      "&method=" +
      dtMethod +
      "&nonce=" +
      ajax_nonce;

    console.log("DT AJAX URL: " + dtUrlString);
    // DataTables AJAX init
    $("#tjg-csbs-candidates").DataTable({
      ajax: {
        url: dtUrlString,
        dataSrc: "data",
      },
      columns: [
        { defaultContent: "" },
        { data: "date_added" },
        { data: "date_updated" },
        { data: "first_name" },
        { data: "last_name" },
        { data: "email" },
        { data: "phone" },
        { data: "city" },
        { data: "state" },
        { data: "disposition" },
        { data: "lead_source" },
        { data: "rep_user_id" },
        { defaultContent: "" },
      ],
      language: {
        searchPanes: {
          clearMessage: "Clear All Filters",
          collapse: {
            0: "Filter Candidates",
            _: "Filter Candidates (%d)",
          },
        },
      },
      columnDefs: [
        {
          targets: [0], // Column: Select
          orderable: false,
          searchable: false,
          className: "select-checkbox",
        },
        {
          targets: [1, 2], // Column: Date Added, Date Updated
          render: function (data, type, row) {
            // return '' if data is null or undefined
            if (!data) {
              return "";
            }
            /* Render date formatted as:
             *  MM/DD/YYYY<br>HH:MM:SS
             */
            const date = new Date(data);
            const dateStr = date.toLocaleDateString();
            const timeStr = date.toLocaleTimeString();
            return dateStr + "<br>" + timeStr;
          },
        },
        {
          targets: [5], // Column: Email
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
          targets: [6], // Column: Phone
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
          // Skip City, State, Disposition, Lead Source
          targets: [11], // Column: Rep User ID
          render: function (data, type, row) {
            // return '' if data is null or undefined
            if (!data) {
              return "";
            }
            // Get user name via AJAX request
            const user_id = data;
            const user_name = getCsbsUserName(user_id);
            console.log(user_name[0].agent_name);
            return 'tbd';
          },
        },
        {
          targets: [12], // Column: Actions
          orderable: false,
          searchable: false,
          render: function (data, type, row) {
            /* Render actions as a button group */
            return "TODO";
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
      pageLength: 50,
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

    $("#ajax-test").on("click", function () {
      console.log("ajax test");
      $.ajax({
        url: ajax_object.ajax_url,
        type: "post",
        data: {
          action: "tjg_csbs_admin",
          nonce: ajax_object.nonce,
          method: "get_candidates",
        },
        success: function (response) {
          console.log(response);
        },
        error: function (response) {
          console.log(response);
        },
      });
    });

    $("#tjg-csbs-assign-agent-btn").on("click", function (e) {
      e.preventDefault();
      // disable button
      $(this).prop("disabled", true);

      // Add rotating spinner
      $(this).html(
        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Assigning...'
      );

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
          // $("#tjg-csbs-candidates").DataTable().ajax.reload();

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
  });

  function getCsbsUserName(userId) {
    $.ajax({
      url: ajax_object.ajax_url,
      type: "POST",
      data: {
        action: "tjg_csbs_admin",
        nonce: ajax_object.nonce,
        method: "get_agent_name",
        agent_id: userId,
      },
      success: function (response) {
        console.log(response.data[0]);
        return response.data[0].agent_name;
      },
      error: function (response) {
        console.log(response);
      },
    });
  }
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
