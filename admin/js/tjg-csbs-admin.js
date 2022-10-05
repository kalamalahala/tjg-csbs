(function( $ ) {
	'use strict';

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

	$(document).ready(function() {
		console.log('DataTable loaded');
		$('#tjg-csbs-candidates').DataTable({
			"dom": 'Bfrtip',
			"buttons": [
				'copy', 'csv', 'excel', 'pdf', 'print'
			],
			"order": [[ 0, "desc" ]],
			"pageLength": 50,
			"lengthMenu": [ 10, 25, 50, 100, 250, 500 ],
			"responsive": true
		});
	});

})( jQuery );
