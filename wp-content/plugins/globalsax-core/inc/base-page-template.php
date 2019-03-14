<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-base-template"><br></div>
	<h2><?php _e( "Base plugin page", 'wisscbase' ); ?></h2>
	
	<p><?php _e( "Sample base plugin page", 'wisscbase' ); ?></p>
	
	<form id="wissc-plugin-base-form" action="options.php" method="POST">
		
			<?php settings_fields( 'wissc_setting' ) ?>
			<?php do_settings_sections( 'wissc-plugin-base' ) ?>
			
			<input type="submit" value="<?php _e( "Save", 'wisscbase' ); ?>" />
	</form> <!-- end of #wissctemplate-form -->
</div>