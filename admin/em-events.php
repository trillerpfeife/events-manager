<?php

/**
 * Determines whether to show event page or events page, and saves any updates to the event or events
 * @return null
 */
function em_admin_events_page() {
	//TODO Simplify panel for events, use form flags to detect certain actions (e.g. submitted, etc)
	global $wpdb;
	global $EM_Event;
	$action = ( !empty($_GET ['action']) ) ? $_GET ['action']:'';
	$order = ( !empty($_GET ['order']) ) ? $_GET ['order']:'ASC';
	$limit = ( !empty($_GET['limit']) ) ? $_GET['limit'] : 20;//Default limit
	$page = ( !empty($_GET['p']) ) ? $_GET['p']:1;
	$offset = ( $page > 1 ) ? ($page-1)*$limit : 0;
	$scope_names = array (
		'past' => __ ( 'Past events', 'dbem' ),
		'all' => __ ( 'All events', 'dbem' ),
		'future' => __ ( 'Future events', 'dbem' )
	);
	$scope = ( !empty($_GET ['scope']) && array_key_exists($_GET ['scope'], $scope_names) ) ? $_GET ['scope']:'future';
	$selectedEvents = ( !empty($_GET ['events']) ) ? $_GET ['events']:'';
	
	// DELETE action
	if ( $action == 'deleteEvents' && EM_Object::array_is_numeric($selectedEvents) ) {
		EM_Events::delete( $selectedEvents );
	}
	
	// No action, only showing the events list
	switch ($scope) {
		case "past" :
			$title = __ ( 'Past Events', 'dbem' );
			break;
		case "all" :
			$title = __ ( 'All Events', 'dbem' );
			break;
		default :
			$title = __ ( 'Future Events', 'dbem' );
			$scope = "future";
	}
	$events = EM_Events::get( array('scope'=>$scope, 'limit'=>0, 'order'=>$order ) );
	$events_count = count ( $events );
	
	$use_events_end = get_option ( 'dbem_use_event_end' );
	?>
	<div class="wrap">
		<div id="icon-events" class="icon32"><br />
		</div>
		<h2><?php echo $title; ?></h2>
		<?php
			em_hello_to_new_user ();
				
			$link = array ();
			$link ['past'] = "<a href='" . get_bloginfo ( 'wpurl' ) . "/wp-admin/admin.php?page=events-manager&amp;scope=past&amp;order=desc'>" . __ ( 'Past events', 'dbem' ) . "</a>";
			$link ['all'] = " <a href='" . get_bloginfo ( 'wpurl' ) . "/wp-admin/admin.php?page=events-manager&amp;scope=all&amp;order=desc'>" . __ ( 'All events', 'dbem' ) . "</a>";
			$link ['future'] = "  <a href='" . get_bloginfo ( 'wpurl' ) . "/wp-admin/admin.php?page=events-manager&amp;scope=future'>" . __ ( 'Future events', 'dbem' ) . "</a>";
		?> 
		<?php if ( !empty($_GET['error']) ) : ?>
		<div id='message' class='error'>
			<p><?php echo $_GET['error']; ?></p>
		</div>
		<?php endif; ?>
		<?php if ( !empty($_GET['message']) ) : ?>
		<div id='message' class='updated fade'>
			<p><?php echo $_GET['message']; ?></p>
		</div>
		<?php endif; ?>
		<form id="posts-filter" action="" method="get"><input type='hidden' name='page' value='events-manager' />
			<ul class="subsubsub">
				<li><a href='#' class="current"><?php _e ( 'Total', 'dbem' ); ?> <span class="count">(<?php echo (count ( $events )); ?>)</span></a></li>
			</ul>
			
			<div class="tablenav">
			
				<div class="alignleft actions">
					<select name="action">
						<option value="-1" selected="selected"><?php _e ( 'Bulk Actions' ); ?></option>
						<option value="deleteEvents"><?php _e ( 'Delete selected','dbem' ); ?></option>
					</select> 
					<input type="submit" value="<?php _e ( 'Apply' ); ?>" name="doaction2" id="doaction2" class="button-secondary action" /> 
					<select name="scope">
						<?php
						foreach ( $scope_names as $key => $value ) {
							$selected = "";
							if ($key == $scope)
								$selected = "selected='selected'";
							echo "<option value='$key' $selected>$value</option>  ";
						}
						?>
					</select> 
					<input id="post-query-submit" class="button-secondary" type="submit" value="<?php _e ( 'Filter' )?>" />
					<?php 
						//Pagination (if needed/requested)
						if( $events_count >= $limit ){
							//Show the pagination links (unless there's less than 10 events
							$page_link_template = preg_replace('/(&|\?)p=\d+/i','',$_SERVER['REQUEST_URI']);
							$page_link_template = em_add_get_params($page_link_template, array('p'=>'%PAGE%'));
							$events_nav = em_paginate( $page_link_template, $events_count, $limit, $page);
							echo $events_nav;
						}
					?>
				</div>
				<br class="clear" />
				
				<?php
				if (empty ( $events )) {
					// TODO localize
					_e ( 'no events','dbem' );
				} else {
				?>
						
				<table class="widefat">
					<thead>
						<tr>
							<th class='manage-column column-cb check-column' scope='col'>
								<input class='select-all' type="checkbox" value='1' />
							</th>
							<th><?php _e ( 'Name', 'dbem' ); ?></th>
				  	   		<th>&nbsp;</th>
				  	   		<th><?php _e ( 'Location', 'dbem' ); ?></th>
							<th colspan="2"><?php _e ( 'Date and time', 'dbem' ); ?></th>
						</tr>
					</thead>
					<tbody>
				  	  	<?php 
				  	  	$i = 1;
				  	  	$rowno = 0;
						foreach ( $events as $event ) {
							if( $i >= $offset && $i <= $offset+$limit ) {
								$rowno++;
								$class = ($rowno % 2) ? ' class="alternate"' : '';
								// FIXME set to american
								$localised_start_date = mysql2date ( __ ( 'D d M Y' ), $event->start_date );
								$localised_end_date = mysql2date ( __ ( 'D d M Y' ), $event->end_date );
								$style = "";
								$today = date ( "Y-m-d" );
								$location_summary = "<b>" . $event->location->name . "</b><br/>" . $event->location->address . " - " . $event->location->town;
								$category = EM_Category::get($event->id);
								
								if ($event->start_date < $today && $event->end_date < $today){
									$style = "style ='background-color: #FADDB7;'";
								}							
								?>
								<tr <?php echo "$class $style"; ?>>
					
									<td>
										<input type='checkbox' class='row-selector' value='<?php echo $event->id; ?>' name='events[]' />
									</td>
									<td>
										<strong>
										<a class="row-title" href="<?php bloginfo ( 'wpurl' )?>/wp-admin/admin.php?page=events-manager-event&amp;event_id=<?php echo $event->id ?>&amp;scope=<?php echo $scope ?>&amp;p=<?php echo $page ?>"><?php echo ($event->name); ?></a>
										</strong>
										<?php if($category) : ?>
										<br/><span title='<?php _e( 'Category', 'dbem' ).": ".$category['category_name'] ?>'><?php $category['category_name'] ?></span> 
										<?php endif; ?>
									</td>
									<td>
							 	    	<a href="<?php bloginfo ( 'wpurl' )?>/wp-admin/admin.php?page=events-manager-event&amp;action=duplicate&amp;event_id=<?php echo $event->id; ?>&amp;scope=<?php echo $scope ?>&amp;p=<?php echo $page ?>" title="<?php _e ( 'Duplicate this event', 'dbem' ); ?>">
							 	    		<strong>+</strong>
							 	    	</a>
							  	   	</td>
									<td>
						  	 			<?php echo $location_summary; ?>
									</td>
							
									<td>
							  	    	<?php echo $localised_start_date; ?>
							  	    	<?php echo ($localised_end_date != $localised_start_date) ? " - $localised_end_date":'' ?>
							  	    	<br />
							  	    	<?php
							  	    		//TODO Should 00:00 - 00:00 be treated as an all day event? 
							  	    		echo substr ( $event->start_time, 0, 5 ) . " - " . substr ( $event->end_time, 0, 5 ); 
							  	    	?>
									</td>
									<td>
										<?php 
										if ( $event->is_recurrence() ) {
											?>
											<strong>
											<?php echo $event->get_recurrence_description(); ?> <br />
											<a href="<?php bloginfo ( 'wpurl' )?>/wp-admin/admin.php?page=events-manager-event&amp;event_id=<?php echo $event->recurrence_id ?>&amp;scope=<?php echo $scope ?>&amp;p=<?php echo $page ?>"><?php _e ( 'Reschedule', 'dbem' ); ?></a>
											</strong>
											<?php
										}
										?>
									</td>
								</tr>
								<?php
							}
							$i ++;
						}
						?>
					</tbody>
				</table>  
				<?php
				} // end of table
				?>
				
				<div class='tablenav'>
					<div class="alignleft actions">
						<?php echo ( !empty($events_nav) ) ? $events_nav:''; ?>
					<br class='clear' />
					</div>
					<br class='clear' />
				</div>
			</div>
		</form>		
	</div>
	<?php
}

?>