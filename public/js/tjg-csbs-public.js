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
		let cancelButton = document.getElementById('tjg-csbs-upload-cancel');

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

	function createSelectors(headerData) {
		/* 
		 * Add Select / Option groups to form for these columns:
		 * First Name
		 * Last Name
		 * Phone
		 * Email
		 * City
		 * State
		 *  
		*/

		console.log('headerData length = ' + headerData.length);


		let summaryBox = document.getElementsByClassName('tjg-csbs-summary')[0];

		// Create Select / Option groups
		let firstNameSelect = document.createElement('select');
		let lastNameSelect = document.createElement('select');
		let phoneSelect = document.createElement('select');
		let emailSelect = document.createElement('select');
		let citySelect = document.createElement('select');
		let stateSelect = document.createElement('select');

		// Create Option groups
		let firstNameOption = document.createElement('option');
		let lastNameOption = document.createElement('option');
		let phoneOption = document.createElement('option');
		let emailOption = document.createElement('option');
		let cityOption = document.createElement('option');
		let stateOption = document.createElement('option');

		// Add Select / Option groups to form
		summaryBox.appendChild(firstNameSelect);
		summaryBox.appendChild(lastNameSelect);
		summaryBox.appendChild(phoneSelect);
		summaryBox.appendChild(emailSelect);
		summaryBox.appendChild(citySelect);
		summaryBox.appendChild(stateSelect);

		// Add Option groups to Select groups
		firstNameSelect.appendChild(firstNameOption);
		lastNameSelect.appendChild(lastNameOption);
		phoneSelect.appendChild(phoneOption);
		emailSelect.appendChild(emailOption);
		citySelect.appendChild(cityOption);
		stateSelect.appendChild(stateOption);

		// Add Select / Option group attributes
		firstNameSelect.setAttribute('id', 'tjg-csbs-upload-new-candidates-first-name');
		lastNameSelect.setAttribute('id', 'tjg-csbs-upload-new-candidates-last-name');
		phoneSelect.setAttribute('id', 'tjg-csbs-upload-new-candidates-phone');
		emailSelect.setAttribute('id', 'tjg-csbs-upload-new-candidates-email');
		citySelect.setAttribute('id', 'tjg-csbs-upload-new-candidates-city');
		stateSelect.setAttribute('id', 'tjg-csbs-upload-new-candidates-state');

		// Add Option group attributes
		firstNameOption.setAttribute('value', '');
		lastNameOption.setAttribute('value', '');
		phoneOption.setAttribute('value', '');
		emailOption.setAttribute('value', '');
		cityOption.setAttribute('value', '');
		stateOption.setAttribute('value', '');

		// Add Option group text
		firstNameOption.innerHTML = 'First Name';
		lastNameOption.innerHTML = 'Last Name';
		phoneOption.innerHTML = 'Phone';
		emailOption.innerHTML = 'Email';
		cityOption.innerHTML = 'City';
		stateOption.innerHTML = 'State';

		// Add Option groups to Select groups
		for (let i = 0; i < headerData.length; i++) {
			let option = document.createElement('option');
			option.setAttribute('value', headerData[i].column);
			option.innerHTML = headerData[i].value;
			firstNameSelect.appendChild(option);
			lastNameSelect.appendChild(option.cloneNode(true));
			phoneSelect.appendChild(option.cloneNode(true));
			emailSelect.appendChild(option.cloneNode(true));
			citySelect.appendChild(option.cloneNode(true));
			stateSelect.appendChild(option.cloneNode(true));
		}
	}

})( jQuery );
