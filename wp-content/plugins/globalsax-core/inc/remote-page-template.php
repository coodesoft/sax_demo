<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-base-template"><br></div>
	<h2><?php _e( "Remote plugin page", 'wisscbase' ); ?></h2>
	
	<p><?php _e( "Performing side activities - AJAX and HTTP fetch", 'wisscbase' ); ?></p>
	<div id="wissc_page_messages"></div>
	
	<?php
		$wissc_ajax_value = get_option( 'wissc_option_from_ajax', '' );
	?>
	
	<h3><?php _e( 'Store a Database option with AJAX', 'wisscbase' ); ?></h3>
	<form id="wissc-plugin-base-ajax-form" action="options.php" method="POST">
			<input type="text" id="wissc_option_from_ajax" name="wissc_option_from_ajax" value="<?php echo $wissc_ajax_value; ?>" />
			
			<input type="submit" value="<?php _e( "Save with AJAX", 'wisscbase' ); ?>" />
	</form> <!-- end of #wissc-plugin-base-ajax-form -->
	
	<h3><?php _e( 'Fetch a title from URL with HTTP call through AJAX', 'wisscbase' ); ?></h3>
	<form id="wissc-plugin-base-http-form" action="options.php" method="POST">
			<input type="text" id="wissc_url_for_ajax" name="wissc_url_for_ajax" value="http://wordpress.org" />
			
			<input type="submit" value="<?php _e( "Fetch URL title with AJAX", 'wisscbase' ); ?>" />
	</form> <!-- end of #wissc-plugin-base-http-form -->
	
	<div id="resource-window">
	</div>
			
</div>