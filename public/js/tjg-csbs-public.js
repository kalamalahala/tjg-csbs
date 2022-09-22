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

	// When the page is ready, add text to the page
	$(document).ready(function() {
		let form = document.getElementById('tjg-csbs-upload-new-candidates');
		let submitButton = document.getElementById('tjg-csbs-upload-submit');
		let fileInput = document.getElementById('tjg-csbs-upload-new-candidates-file');
		let textarea = document.getElementById('tjg-csbs-upload-new-candidates-summary');
		
		$(document).on('submit', form, function(e) {
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
				textarea.innerHTML = 'No file selected. Please select a file and try again.';
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
		


		// Add text to the page
		$(textarea).text('Upload new candidates and script loaded');

	});

})( jQuery );
