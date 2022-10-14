(function ($) {
	'use strict';

	$(document).ready(function () {

		// log ajax object for this file
		console.log(tjg_csbs_candidates_ajax_object);
		console.log('tjg_csbs_candidates.js loaded');
		

		/*
		* Listen for buttons to be clicked:
		* - tjg-csbs-candidate-interview
		* - tjg-csbs-candidate-update
		* - tjg-csbs-candidate-delete
		*/
		$(document).on('click', '.tjg-csbs-candidate-interview', {}, handle_candidate_interview_click );
		$(document).on('click', '.tjg-csbs-candidate-update', {}, handle_candidate_update_click );
		$(document).on('click', '.tjg-csbs-candidate-delete', {}, handle_candidate_delete_click );

	});

	function handle_candidate_interview_click(event) {
		const candidateID = event.target.getAttribute('data-id');
		console.log('handle_candidate_interview_click:  data.id ' + candidateID );
		window.location.href = '?interview=y&candidate_id=' + candidateID;
	}

	function handle_candidate_update_click() {
		console.log( 'handle_candidate_update_click' );
	}

	function handle_candidate_delete_click() {
		console.log( 'handle_candidate_delete_click' );
	}

})(jQuery);
