/* sample script here */

jQuery(document).ready(function($) {
	
	// Handle the AJAX field save action
	$('#wissc-plugin-base-ajax-form').on('submit', function(e) {
		e.preventDefault();
		
		var ajax_field_value = $('#wissc_option_from_ajax').val();
		
		 $.post(ajaxurl, {
			 	data: { 'wissc_option_from_ajax': ajax_field_value },
		             action: 'store_ajax_value'
				 }, function(status) {
					 	 $('#wissc_page_messages').html('Value updated successfully');
		           }
		);
	});
	
	// Handle the AJAX URL fetcher
	$('#wissc-plugin-base-http-form').on('submit', function(e) {
		e.preventDefault();
		
		var ajax_field_value = $('#wissc_url_for_ajax').val();
		
		 $.post(ajaxurl, {
			 	data: { 'wissc_url_for_ajax': ajax_field_value },
		             action: 'fetch_ajax_url_http'
				 }, function(status) {
					 	 $('#wissc_page_messages').html('The URL title is fetching in the frame below');
					 	 $('#resource-window').html( '<p>Site title: ' + status + '</p>');
		           }
		);
	});
});