(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
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

	$(document).ready(function() {

		let form = document.getElementById('tjg-csbs-upload-new-candidates');
		let submitButton = document.getElementById('tjg-csbs-upload-submit');
		let fileInput = document.getElementById('tjg-csbs-upload-new-candidates-file');
		let textarea = document.getElementById('tjg-csbs-upload-new-candidates-summary');
		let trashButton = document.getElementById('tjg-csbs-upload-new-candidates-trash');

		// Collect file information when selected
		$(fileInput).on('change', function() {
			// If trash icon has hidden attribute, remove it
			if (trashButton.hasAttribute('hidden')) {
				trashButton.removeAttribute('hidden');
			}


			let file = fileInput.files[0];

			console.log('File selected: ' + file.name);
			
			// Disable submit until file is parsed
			$(submitButton).disabled = true;
			
			// assemble ajax data array
			let ajaxData = new FormData();
			ajaxData.append('action', 'tjg_csbs_primary_ajax');
			ajaxData.append('method', 'get_spreadsheet_summary');
			ajaxData.append('nonce', tjg_csbs_ajax_object.nonce);
			ajaxData.append('file', file);

			// Send AJAX request to get spreadsheet summary
			$.ajax({
				url: tjg_csbs_ajax_object.ajax_url,
				type: 'POST',
				contentType: false,
				processData: false,
				data: ajaxData,
				success: function(response) {
					console.log('Response: ' + response.data)
					textarea.value = 'Response: ' + response.data;
				},
				error: function(error) {
					console.log('Error: ' + error.responseText);
					textarea.value = 'Error: ' + error.responseText;
				},
				complete: function() {
					$(submitButton).disabled = false;
				}
			}); // End AJAX request

		}); // End fileInput change event




		/**
		 * Candidate Form Submit Handler
		 */
		$(form).on('submit', form, function(e) {
			// Don't submit the form normally
			e.preventDefault();

			// Prevent the button from being clicked again
			submitButton.disabled = true;

			// Append spinning loading icon to button text
			submitButton.innerHTML = 'Uploading... <i class="fa fa-spinner fa-spin"></i>';

			// Get the file
			let file = fileInput.files[0];

			// If no file was selected, display an error message and re-enable the button
			if (file === undefined) {
				// Animate.min.css headShake animation
				$(fileInput).addClass('animate__animated animate__headShake');

				// Remove the animation class after 1 second
				setTimeout(function() {
					$(fileInput).removeClass('animate__animated animate__headShake');
				}, 1000);

				// Display error message
				textarea.innerHTML = 'No file selected. Please select a file and try again.';

				// Re-enable the button
				submitButton.innerHTML = 'Submit Candidate File';
				submitButton.disabled = false;
				return;
			}

			// Create a new FormData object.
			let formData = new FormData();

			// Add the file to the request.
			formData.append('file', file);
			formData.append('filetype', file.type);
			formData.append('filesize', file.size);
			formData.append('filename', file.name);
			formData.append('action', 'tjg_csbs_primary_ajax');
			formData.append('method', 'upload_new_candidates');
			formData.append('nonce', tjg_csbs_ajax_object.nonce);

			// Set up the request.

			// submit the form via ajax
			$.ajax({
				url: tjg_csbs_ajax_object.ajax_url,
				type: 'POST',
				contentType: false,
				processData: false,
				data: formData,
				success: function(response) {
					console.log(response);
					// Display the returned data in browser
					textarea.innerHTML = response;

					// Remove spinning loading icon from button text
					submitButton.innerHTML = 'Submit Candidate File';

					// Clear the file input
					fileInput.value = '';
				},
				error: function(response) {
					console.log(response);
					// Display the returned data in browser
					textarea.innerHTML = response;

					// Remove spinning loading icon from button text
					submitButton.innerHTML = 'Submit Candidate File';

					// Clear the file input
					fileInput.value = '';
				}
			});

			
		});
		

	});

})( jQuery );
