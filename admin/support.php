<?php 
function em_admin_support(){
	global $wpdb;
	?>
	<div class="wrap">
		<div id="icon-events" class="icon32"><br /></div>
		<h2>Getting Support for Events Manager</h2>
		<p>
			If you require further support or encounter any bugs please visit us at our <a href="http://davidebenini.it/events-manager-forum/">Forum</a>. We ask that you give the documentation a good read first, as this answers many common questions. 
		</p>
		<?php
		//Is this a previously imported installation? 
		$old_table_name = $wpdb->prefix.OLD_EVENTS_TBNAME;
		if( $wpdb->get_var("SHOW TABLES LIKE '$old_table_name'") == $old_table_name ){
			?>
			<div class="updated">
				<h3>Troubleshooting upgrades from version 2.x to 3.x</h3>
				<p>We notice that you upgraded from version 2, as we are now using new database tables, and we do not delete the old tables in case something went wrong with this upgrade.</p>
		   		<p>If somehthing went wrong with the update to version 3 you have two options:</p>
		   		<h4>The plugin is working, but for some reason the old events weren't imported</h4>
		   		<p>You can safely reimport your old events from the previous tables without any risk of deleting them. However, if you click the button below <b>YOU WILL OVERWRITE ANY NEW EVENTS YOU CREATED IN VERSION 3</b></p>
				<a href="<?php echo wp_nonce_url( get_bloginfo('wpurl').'/wp-admin/admin.php?page=events-manager-support&em_reimport=1', 'em_reimport' ) ?>">Reimport Events from version 2</a>
				<h4>The plugin is working, I want to go back to version 2!</h4>
				<p>You can safely downgrade and will not lose any information.</p>
				<ol> 
					<li>First of all, <a href='http://downloads.wordpress.org/plugin/events-manager.2.2.2.zip'>dowload a copy of version 2.2</a></li>
					<li>Deactivate and delete Events Manager in the plugin page</li>
					<li><a href="<?php bloginfo('wpurl'); ?>/wp-admin/plugin-install.php?tab=upload">Upload the zip file you just downloaded here</a></li>
					<li>Let the developers know, of any bugs you ran into while upgrading. We'll help you out if there is a simple solution, and will fix reported bugs within days, if not quicker!</li>
				<ol>
			</div>
			<?php
		}
		?>
	<?php
}
?>