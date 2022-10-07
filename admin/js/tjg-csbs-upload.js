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

		const form = document.getElementById('tjg-csbs-upload-new-candidates');
		const submitButton = document.getElementById('tjg-csbs-upload-submit');
		const fileInput = document.getElementById('tjg-csbs-upload-new-candidates-file');
		const textarea = document.getElementById('tjg-csbs-upload-new-candidates-summary');
		const trashButton = document.getElementById('tjg-csbs-upload-new-candidates-trash');
		const cancelButton = document.getElementById('tjg-csbs-upload-cancel');
		const selectGroupWrapper = document.getElementById('select-group-wrapper');


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
			ajaxData.append('action', 'tjg_csbs_admin');
			ajaxData.append('method', 'get_spreadsheet_summary');
			ajaxData.append('nonce', ajax_object.nonce);
			ajaxData.append('file', file);

			// Send AJAX request to get spreadsheet summary
			$.ajax({
				url: ajax_object.ajax_url,
				type: 'POST',
				contentType: false,
				processData: false,
				data: ajaxData,
				success: function(response) {
					createSelectors(response.data);
					console.log('Response: ' + response.data)
					// list object contents to textarea
					textarea.value = JSON.stringify(response.data, null, 2);
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

		// Clear file input when trash icon is clicked
		$(trashButton).on('click', function() {
			// Clear file input if it has a file
			if (fileInput.files.length > 0) {
				fileInput.value = '';
			}

			// Clear textarea
			textarea.value = '';

			// Hide select group wrapper
			selectGroupWrapper.setAttribute('hidden', true);

			// Hide trash icon
			trashButton.setAttribute('hidden', true);
		});

		// Clear form when cancel button is clicked
		$(cancelButton).on('click', function() {
			// Clear file input if it has a file
			if (fileInput.files.length > 0) {
				fileInput.value = '';
			}

			// Clear textarea
			textarea.value = '';

			// Hide select group wrapper
			selectGroupWrapper.setAttribute('hidden', true);

			// Hide trash icon if visible
			if (!trashButton.hasAttribute('hidden')) {
				trashButton.setAttribute('hidden', true);
			}
		});




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

			// Collect Select Option values and append to formData
			const firstNameColumn = document.getElementById('tjg-csbs-upload-new-candidates-first-name');
			const lastNameColumn = document.getElementById('tjg-csbs-upload-new-candidates-last-name');
			const emailColumn = document.getElementById('tjg-csbs-upload-new-candidates-email');
			const phoneColumn = document.getElementById('tjg-csbs-upload-new-candidates-phone');
			const cityColumn = document.getElementById('tjg-csbs-upload-new-candidates-city');
			const stateColumn = document.getElementById('tjg-csbs-upload-new-candidates-state');

			const selectData = {
				firstNameColumn: firstNameColumn.value,
				lastNameColumn: lastNameColumn.value,
				emailColumn: emailColumn.value,
				phoneColumn: phoneColumn.value,
				cityColumn: cityColumn.value,
				stateColumn: stateColumn.value
			};


			// Add the file to the request.
			formData.append('file', file);
			formData.append('filetype', file.type);
			formData.append('filesize', file.size);
			formData.append('filename', file.name);
			formData.append('selectData', JSON.stringify(selectData));
			formData.append('action', 'tjg_csbs_admin');
			formData.append('method', 'upload_new_candidates');
			// Set mode to 'gf' for Gravity Forms for now TODO: Add option to select mode
			formData.append('mode', 'db');
			formData.append('nonce', ajax_object.nonce);

			// Set up the request.

			// submit the form via ajax
			$.ajax({
				url: ajax_object.ajax_url,
				type: 'POST',
				contentType: false,
				processData: false,
				data: formData,
				success: function(response) {
					// Display the returned data in browser
					textarea.value = JSON.stringify(response);

					// Remove spinning loading icon from button text
					submitButton.innerHTML = 'Submit Candidate File';
					submitButton.disabled = false;

				},
				error: function(response) {
					// Display the returned data in browser
					textarea.value = JSON.stringify(response);

					// Remove spinning loading icon from button text
					submitButton.innerHTML = 'Submit Candidate File';
					submitButton.disabled = false;
				}
			});

			
		});


		

	});

	function createSelectors(headerData) {
		/* 
		 * Add Select / Option groups to form for these columns:
		 * First Name 	Element ID: tjg-csbs-upload-new-candidates-first-name
		 * Last Name 	Element ID: tjg-csbs-upload-new-candidates-last-name
		 * Phone 		Element ID: tjg-csbs-upload-new-candidates-phone
		 * Email 		Element ID: tjg-csbs-upload-new-candidates-email
		 * City 		Element ID: tjg-csbs-upload-new-candidates-city
		 * State 		Element ID: tjg-csbs-upload-new-candidates-state
		*/

		// select-group-wrapper remove hidden attribute
		const selectGroupWrapper = document.getElementById('select-group-wrapper');
		selectGroupWrapper.removeAttribute('hidden');

		const firstNameSelect = document.getElementById('tjg-csbs-upload-new-candidates-first-name');
		const lastNameSelect = document.getElementById('tjg-csbs-upload-new-candidates-last-name');
		const phoneSelect = document.getElementById('tjg-csbs-upload-new-candidates-phone');
		const emailSelect = document.getElementById('tjg-csbs-upload-new-candidates-email');
		const citySelect = document.getElementById('tjg-csbs-upload-new-candidates-city');
		const stateSelect = document.getElementById('tjg-csbs-upload-new-candidates-state');

		/* Apend options to select elements
		 * headerData.column
		 * headerData.value
		*/

		for (let i = 0; i < headerData.headers.length; i++) {
			let option = document.createElement('option');
			option.value = headerData.headers[i].column;
			option.text = headerData.headers[i].value;
			firstNameSelect.appendChild(option);
			lastNameSelect.appendChild(option.cloneNode(true));
			phoneSelect.appendChild(option.cloneNode(true));
			emailSelect.appendChild(option.cloneNode(true));
			citySelect.appendChild(option.cloneNode(true));
			stateSelect.appendChild(option.cloneNode(true));
		}
		
	}

})( jQuery );
