<?php 
    $wproject_settings          = wProject();
	$enable_kanban			    = $wproject_settings['enable_kanban'];
	$kanban_density 		    = $wproject_settings['kanban_density'];
	$kanban_card_colours	    = $wproject_settings['kanban_card_colours'];
    $kanban_card_descriptions   = $wproject_settings['kanban_card_descriptions'];
	$kanban_unfocused_cards   	= $wproject_settings['kanban_unfocused_cards'];
	$enable_time			    = $wproject_settings['enable_time'];
    $client_use_kanban          = $wproject_settings['client_use_kanban'];

    $print                      = isset($_GET['print']) ? $_GET['print'] : '';
	$current_user_id			= get_current_user_id();
	$user                  	 	= get_userdata($current_user_id);
	$role                   	= $user->roles[0];
	global $post;

	if($kanban_unfocused_cards == 'hide') {
		$kanban_card_method = ".hide()";
		$kanban_card_return = ".show()";
	} else if($kanban_unfocused_cards == 'fade') {
		$kanban_card_method = ".css('opacity', '.3')";
		$kanban_card_return = ".css('opacity', '1')";
	} else if($kanban_unfocused_cards == '') {
		$kanban_card_method = ".css('opacity', '.3')";
		$kanban_card_return = ".css('opacity', '1')";
	}

	if(function_exists('add_client_settings')) {

        $wproject_client_settings   = wProject_client();
        $client_view_others_tasks   = $wproject_client_settings['client_view_others_tasks'];

		if($client_view_others_tasks == 'on') {
			$task_authors = '';
		} else {
			$task_authors = get_current_user_id();
		}

    } else {
		
        $task_authors = '';

    }

    if(empty($print)) {
	
	if($kanban_card_colours == 'on') {
		$kanban_card_colours = 'kanban-card-colours';
	} else {
		$kanban_card_colours = '';
	}

	if(is_tax('project') && $enable_kanban) { 

		global $wp;
		$term_id		= get_queried_object()->term_id; 
		$term_meta		= get_term_meta($term_id); 
		$term_object	= get_term( $term_id );
		$now			= strtotime('today');
	?>
		
		<!--/ Start Kanban /-->
		<form class="kanban <?php echo $kanban_density; ?>  <?php echo $kanban_card_colours; ?>" method="post" id="arrange-kanban">

			<h1><?php echo single_cat_title(); ?></h1>
			

			<!--/ Start Kanban Container /-->
			<div class='kanban-container'>

				<!--/ Start Section /-->
				<section data-status="not-started">
					<h3>
						<span class="not-started-count"></span> <?php _e('To do', 'wproject'); ?>
						<em class="kanban-column-reverse" title="Reverse order"><i data-feather="triangle"></i></em>
					</h3>

					<!--/ Start Box /-->
					<div class="box connectedSortable" id="not-started">

						<?php $todo = array(
							'post_type'         => 'task',
							'post_status'		=> 'publish',
							'category' 			=> $term_id,
							'orderby' 			=> 'name',
							'order' 			=> 'ASC',
							'posts_per_page'    => -1,
							'meta_key'          => 'task_status',
							'meta_value'        => array('not-started', 'incomplete'),
							'author'        	=>  $task_authors,
							'tax_query' => array(
								array(
									'taxonomy' => 'project',
									'field'    => 'slug',
									'terms'    => array( $term_object->slug ),
									'operator' => 'IN'
								),
							),
						);
						$query = new WP_Query($todo);
						$my_count = $query->post_count;
						while ($query->have_posts()) : $query->the_post();
						$task_id                = get_the_id();
						$author_id              = get_post_field ('post_author', $task_id);
						$user_ID                = get_the_author_meta( 'ID', $author_id );
						$first_name             = get_the_author_meta( 'first_name', $author_id );
						$last_name              = get_the_author_meta( 'last_name', $author_id );
						$user_photo             = get_the_author_meta( 'user_photo', $author_id );

						$task_status			= get_post_meta($task_id, 'task_status', TRUE);
                        $task_description       = get_post_meta($task_id, 'task_description', TRUE);
						$task_priority			= get_post_meta($task_id, 'task_priority', TRUE);
						$task_job_number        = get_post_meta($task_id, 'task_job_number', TRUE);
						$task_pc_complete		= get_post_meta($task_id, 'task_pc_complete', TRUE);
                        $task_relation          = get_post_meta($task_id, 'task_relation', TRUE);
                        $task_timer             = get_post_meta($task_id, 'task_timer', TRUE);
                        

                        if($task_relation == 'has_issues_with' ) {
                            $task_relation = __('Has issues', 'wproject');
                        } else if($task_relation == 'is_blocked_by' ) {
                            $task_relation = __('Blocked', 'wproject');
                        } else if($task_relation == 'is_similar_to' ) {
                            $task_relation = __('Similarity', 'wproject');
                        } else if($task_relation == 'relates_to' ) {
                            $task_relation = __('Related', 'wproject');
                        }

						$date_format    		= get_option('date_format'); 
						$task_start_date        = get_post_meta($task_id, 'task_start_date', TRUE);
                        $task_end_date          = get_post_meta($task_id, 'task_end_date', TRUE);
                        $context_label          = get_post_meta($task_id, 'context_label', TRUE);

						$due_date               = strtotime($task_end_date);
						
						if($task_start_date || $task_end_date) {
							$new_task_start_date    = new DateTime($task_start_date);
							$the_task_start_date    = $new_task_start_date->format($date_format);
					
							$new_task_end_date      = new DateTime($task_end_date);
							$the_task_end_date      = $new_task_end_date->format($date_format);
						}

						if($task_priority == 'low') {
							$task_priority_name = /* translators: One of 4 possible task priorities */ __('Low', 'wproject');
						} else if($task_priority == 'normal' || $task_priority == '') {
							$task_priority_name = /* translators: One of 4 possible task priorities */ __('Normal', 'wproject');
						} else if($task_priority == 'high') {
							$task_priority_name = /* translators: One of 4 possible task priorities */ __('High', 'wproject');
						} else if($task_priority == 'urgent') {
							$task_priority_name = /* translators: One of 4 possible task priorities */ __('Urgent', 'wproject');
						}

						if($task_priority == '') {
							$task_priority = 'normal';
						}

						$the_user = get_userdata($author_id);
                        if($author_id != '0') {
                            $the_role	= $the_user->roles[0];

                            if(preg_match("/[a-e]/i", $first_name[0])) {
                                $colour = 'a-e';
                            } else if(preg_match("/[f-j]/i", $first_name[0])) {
                                $colour = 'f-j';
                            } else if(preg_match("/[k-o]/i", $first_name[0])) {
                                $colour = 'k-o';
                            } else if(preg_match("/[p-t]/i", $first_name[0])) {
                                $colour = 'p-t';
                            } else if(preg_match("/[u-z]/i", $first_name[0])) {
                                $colour = 'u-z';
                            } else {
                                $colour = '';
                            }
                        }

						if($author_id != '0') {
                            if($user_photo) {
                                $avatar         = $user_photo;
                                $avatar_id      = attachment_url_to_postid($avatar);
                                $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                                $avatar         = $small_avatar[0];
                                $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" />';
                            } else {
                                $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . $first_name[0] . $last_name[0] . '</div>';
                            }
                        } else {
                            $the_avatar = '<img src="' . get_template_directory_uri() . '/images/unknown-user.svg' . '" class="avatar" />';
                        }
						?>
							
							<div class="ui-state-default priority <?php echo $task_priority; ?> <?php if($due_date && $now > $due_date && $task_status !='complete') { echo 'task-overdue'; } ?> <?php if ( get_comments_number($task_id) > 0 ) { echo 'has-comment'; } ?> <?php if($user_ID != $current_user_id) { echo 'undraggable'; } ?> <?php if ($task_description) { echo 'has-description'; } ?> <?php if($enable_time && $task_timer == 'on') { echo 'timing'; } ?> task-owner-<?php echo $user_ID; ?>" id="<?php echo $task_id; ?>" data-pc-complete="<?php echo $task_pc_complete; ?>" data-order="" data-user-id="<?php echo $user_ID; ?>">
                                
								<a href="<?php echo get_permalink(); ?>" class="kanban-title">
                                    <?php if($enable_time && $task_timer == 'on') { ?>
                                        <img src="<?php echo get_template_directory_uri();?>/images/clock.svg" class="full-spin" /> 
                                    <?php } ?>
                                    <?php echo the_title(); ?>
                                </a>
                                
                                <?php if($context_label || $task_relation) { ?>
                                    <div class="kanban-context-label">
                                        <?php if($context_label) { ?>
                                            <span class="context-label"><?php echo str_replace(' ', '-', strtolower($context_label)); ?></span>
                                        <?php } ?>
                                        <?php if($task_relation) { ?>
                                            <span class="pill kanban-task-relation"><?php echo $task_relation; ?></span>
                                        <?php } ?>
                                    </div>
                                <?php } ?>

								<?php if($task_end_date) { ?>
									<span class="<?php if($due_date && $now > $due_date && $task_status !='complete') { echo 'kanban-task-overdue'; } ?>">
                                        <?php if($due_date && $now > $due_date && $task_status !='complete') { echo '<i data-feather="alert-circle" class="overdue"></i>'; } ?>
										<?php _e('Due', 'wproject'); ?> <?php echo $the_task_end_date; ?>
									</span>
								<?php } ?>

                                <?php if($kanban_card_descriptions == 'on' && $task_description) { ?>
                                    <p><?php echo wp_trim_words( $task_description, 25 ); ?></p>
                                <?php } ?>
								
								<?php if (get_comments_number($task_id) > 0 ) { ?>
									<i class="comment-count">
										<i data-feather="message-circle"></i><?php echo get_comments_number($task_id); ?>
									</i>
								<?php } ?>

                                <?php if($task_pc_complete) { ?>
									<span class="pcc" title="<?php if($task_pc_complete == '100') { _e('100%', 'wproject');  }?>">
										<?php if($task_pc_complete == '100') { ?>
											<i data-feather="check-circle-2"></i>
										<?php } else { ?>
											<?php echo $task_pc_complete; ?>%
										<?php } ?>
									</span>
								<?php } ?>

                                <?php if($task_job_number) { ?>
									<span><strong><?php _e('Job #', 'wproject'); ?>:</strong> <?php echo $task_job_number; ?></span>
								<?php } ?>

                                <div class="kanban-card-lower">
                                    <?php echo $the_avatar; ?>
                                    <em>
                                        <?php if($author_id != '0') { ?>
                                            <?php echo $first_name; ?> <?php echo $last_name; ?>
                                        <?php } else { ?>
                                            <?php _e('Nobody', 'wproject'); ?>
                                        <?php } ?>
                                    </em>
                                </div>

							</div>

						<?php endwhile;
						wp_reset_postdata();
						?>
						<script>
							/* Inject count into H3 span */
							$('.not-started-count').text(<?php echo $my_count; ?>);
						</script>

					</div>
					<!--/ End Box /-->

				</section>
				<!--/ End Section /-->

				<!--/ Start Section /-->
				<section data-status="in-progress">
					<h3>
						<span class="in-progress-count"></span> <?php _e('In progress', 'wproject'); ?>
						<em class="kanban-column-reverse" title="Reverse order"><i data-feather="triangle"></i></em>
					</h3>

					<!--/ Start Box /-->
					<div class="box connectedSortable" id="in-progress">

						<?php $todo = array(
							'post_type'         => 'task',
							'post_status'		=> 'publish',
							'category' 			=> $term_id,
							'orderby' 			=> 'name',
							'order' 			=> 'ASC',
							'posts_per_page'    => -1,
							'meta_key'          => 'task_status',
							'meta_value'        => array('in-progress'),
							'author'        	=>  $task_authors,
							'tax_query' => array(
								array(
									'taxonomy' => 'project',
									'field'    => 'slug',
									'terms'    => array( $term_object->slug ),
									'operator' => 'IN'
								),
							),
						);
						$query = new WP_Query($todo);
						$my_count = $query->post_count;
						while ($query->have_posts()) : $query->the_post();
						$task_id                = get_the_id();
						$author_id              = get_post_field ('post_author', $task_id);
						$user_ID                = get_the_author_meta( 'ID', $author_id );
						$first_name             = get_the_author_meta( 'first_name', $author_id );
						$last_name              = get_the_author_meta( 'last_name', $author_id );
						$user_photo             = get_the_author_meta( 'user_photo', $author_id );

						$task_status			= get_post_meta($task_id, 'task_status', TRUE);
                        $task_description       = get_post_meta($task_id, 'task_description', TRUE);
						$task_priority			= get_post_meta($task_id, 'task_priority', TRUE);
						$task_job_number        = get_post_meta($task_id, 'task_job_number', TRUE);

						$date_format    		= get_option('date_format'); 
						$task_start_date        = get_post_meta($task_id, 'task_start_date', TRUE);
                        $task_end_date          = get_post_meta($task_id, 'task_end_date', TRUE);
                        $context_label          = get_post_meta($task_id, 'context_label', TRUE);
						$task_pc_complete		= get_post_meta($task_id, 'task_pc_complete', TRUE);
                        $task_relation          = get_post_meta($task_id, 'task_relation', TRUE);
                        $task_timer             = get_post_meta($task_id, 'task_timer', TRUE);

                        if($task_relation == 'has_issues_with' ) {
                            $task_relation = __('Has issues', 'wproject');
                        } else if($task_relation == 'is_blocked_by' ) {
                            $task_relation = __('Blocked', 'wproject');
                        } else if($task_relation == 'is_similar_to' ) {
                            $task_relation = __('Similarity', 'wproject');
                        } else if($task_relation == 'relates_to' ) {
                            $task_relation = __('Related', 'wproject');
                        }

						$due_date               = strtotime($task_end_date);
						
						if($task_start_date || $task_end_date) {
							$new_task_start_date    = new DateTime($task_start_date);
							$the_task_start_date    = $new_task_start_date->format($date_format);
					
							$new_task_end_date      = new DateTime($task_end_date);
							$the_task_end_date      = $new_task_end_date->format($date_format);
						}

						if($task_priority == 'low') {
							$task_priority_name = /* translators: One of 4 possible task priorities */ __('Low', 'wproject');
						} else if($task_priority == 'normal' || $task_priority == '') {
							$task_priority_name = /* translators: One of 4 possible task priorities */ __('Normal', 'wproject');
						} else if($task_priority == 'high') {
							$task_priority_name = /* translators: One of 4 possible task priorities */ __('High', 'wproject');
						} else if($task_priority == 'urgent') {
							$task_priority_name = /* translators: One of 4 possible task priorities */ __('Urgent', 'wproject');
						}

						if($task_priority == '') {
							$task_priority = 'normal';
						}

						$the_user = get_userdata($author_id);
                        if($author_id != '0') {
                            $the_role	= $the_user->roles[0];

                            if(preg_match("/[a-e]/i", $first_name[0])) {
                                $colour = 'a-e';
                            } else if(preg_match("/[f-j]/i", $first_name[0])) {
                                $colour = 'f-j';
                            } else if(preg_match("/[k-o]/i", $first_name[0])) {
                                $colour = 'k-o';
                            } else if(preg_match("/[p-t]/i", $first_name[0])) {
                                $colour = 'p-t';
                            } else if(preg_match("/[u-z]/i", $first_name[0])) {
                                $colour = 'u-z';
                            } else {
                                $colour = '';
                            }
                        }

						if($author_id != '0') {
                            if($user_photo) {
                                $avatar         = $user_photo;
                                $avatar_id      = attachment_url_to_postid($avatar);
                                $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                                $avatar         = $small_avatar[0];
                                $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" />';
                            } else {
                                $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . $first_name[0] . $last_name[0] . '</div>';
                            }
                        } else {
                            $the_avatar = '<img src="' . get_template_directory_uri() . '/images/unknown-user.svg' . '" class="avatar" />';
                        }
						?>
							
							<div class="ui-state-default priority <?php echo $task_priority; ?> <?php if($due_date && $now > $due_date && $task_status !='complete') { echo 'task-overdue'; } ?> <?php if ( get_comments_number($task_id) > 0 ) { echo 'has-comment'; } ?> <?php if($user_ID != $current_user_id) { echo 'undraggable'; } ?> <?php if ($task_description) { echo 'has-description'; } ?> <?php if($enable_time && $task_timer == 'on') { echo 'timing'; } ?> task-owner-<?php echo $user_ID; ?>" id="<?php echo $task_id; ?>" data-pc-complete="<?php echo $task_pc_complete; ?>" data-order="" data-user-id="<?php echo $user_ID; ?>">
                                
                            
                                <a href="<?php echo get_permalink(); ?>" class="kanban-title">
                                    <?php if($enable_time && $task_timer == 'on') { ?>
                                        <img src="<?php echo get_template_directory_uri();?>/images/clock.svg" class="full-spin" /> 
                                    <?php } ?>
                                    <?php echo the_title(); ?>
                                </a>
                                
                                <?php if($context_label || $task_relation) { ?>
                                    <div class="kanban-context-label">
                                        <?php if($context_label) { ?>
                                            <span class="context-label"><?php echo str_replace(' ', '-', strtolower($context_label)); ?></span>
                                        <?php } ?>
                                        <?php if($task_relation) { ?>
                                            <span class="pill kanban-task-relation"><?php echo $task_relation; ?></span>
                                        <?php } ?>
                                    </div>
                                <?php } ?>

								<?php if($task_end_date) { ?>
									<span class="<?php if($due_date && $now > $due_date && $task_status !='complete') { echo 'kanban-task-overdue'; } ?>">
                                        <?php if($due_date && $now > $due_date && $task_status !='complete') { echo '<i data-feather="alert-circle" class="overdue"></i>'; } ?>
										<?php _e('Due', 'wproject'); ?> <?php echo $the_task_end_date; ?>
									</span>
								<?php } ?>

                                <?php if($kanban_card_descriptions == 'on' && $task_description) { ?>
                                    <p><?php echo wp_trim_words( $task_description, 25 ); ?></p>
                                <?php } ?>
								
								<?php if ( get_comments_number($task_id) > 0 ) { ?>
									<i class="comment-count">
										<i data-feather="message-circle"></i><?php echo get_comments_number($task_id); ?>
									</i>
								<?php } ?>

                                <?php if($task_pc_complete) { ?>
									<span class="pcc" title="<?php if($task_pc_complete == '100') { _e('100%', 'wproject');  }?>">
										<?php if($task_pc_complete == '100') { ?>
											<i data-feather="check-circle-2"></i>
										<?php } else { ?>
											<?php echo $task_pc_complete; ?>%
										<?php } ?>
									</span>
								<?php } ?>

                                <?php if($task_job_number) { ?>
									<span><strong><?php _e('Job #', 'wproject'); ?>:</strong> <?php echo $task_job_number; ?></span>
								<?php } ?>

                                <div class="kanban-card-lower">
                                    <?php echo $the_avatar; ?>
                                    <em>
                                        <?php if($author_id != '0') { ?>
                                            <?php echo $first_name; ?> <?php echo $last_name; ?>
                                        <?php } else { ?>
                                            <?php _e('Nobody', 'wproject'); ?>
                                        <?php } ?>
                                    </em>
                                </div>

							</div>

						<?php endwhile;
						wp_reset_postdata();
						?>

						<script>
							/* Inject count into H3 span */
							$('.in-progress-count').text(<?php echo $my_count; ?>);
						</script>

					</div>
					<!--/ End Box /-->

				</section>
				<!--/ End Section /-->
				
				<!--/ Start Section /-->
				<section data-status="on-hold">
					<h3>
						<span class="on-hold-count"></span> <?php _e('On hold', 'wproject'); ?>
						<em class="kanban-column-reverse" title="Reverse order"><i data-feather="triangle"></i></em>
					</h3>

					<!--/ Start Box /-->
					<div class="box connectedSortable" id="on-hold">

						<?php $todo = array(
							'post_type'         => 'task',
							'post_status'		=> 'publish',
							'category' 			=> $term_id,
							'orderby' 			=> 'name',
							'order' 			=> 'ASC',
							'posts_per_page'    => -1,
							'meta_key'          => 'task_status',
							'meta_value'        => array('on-hold'),
							'author'        	=>  $task_authors,
							'tax_query' => array(
								array(
									'taxonomy' => 'project',
									'field'    => 'slug',
									'terms'    => array( $term_object->slug ),
									'operator' => 'IN'
								),
							),
						);
						$query = new WP_Query($todo);
						$my_count = $query->post_count;
						while ($query->have_posts()) : $query->the_post();
						$task_id                = get_the_id();
						$author_id              = get_post_field ('post_author', $task_id);
						$user_ID                = get_the_author_meta( 'ID', $author_id );
						$first_name             = get_the_author_meta( 'first_name', $author_id );
						$last_name              = get_the_author_meta( 'last_name', $author_id );
						$user_photo             = get_the_author_meta( 'user_photo', $author_id );

						$task_status			= get_post_meta($task_id, 'task_status', TRUE);
                        $task_description       = get_post_meta($task_id, 'task_description', TRUE);
						$task_priority			= get_post_meta($task_id, 'task_priority', TRUE);
						$task_job_number        = get_post_meta($task_id, 'task_job_number', TRUE);

						$date_format    		= get_option('date_format'); 
						$task_start_date        = get_post_meta($task_id, 'task_start_date', TRUE);
                        $task_end_date          = get_post_meta($task_id, 'task_end_date', TRUE);
                        $context_label          = get_post_meta($task_id, 'context_label', TRUE);
						$task_pc_complete		= get_post_meta($task_id, 'task_pc_complete', TRUE);
                        $task_relation          = get_post_meta($task_id, 'task_relation', TRUE);
                        $task_timer             = get_post_meta($task_id, 'task_timer', TRUE);

                        if($task_relation == 'has_issues_with' ) {
                            $task_relation = __('Has issues', 'wproject');
                        } else if($task_relation == 'is_blocked_by' ) {
                            $task_relation = __('Blocked', 'wproject');
                        } else if($task_relation == 'is_similar_to' ) {
                            $task_relation = __('Similarity', 'wproject');
                        } else if($task_relation == 'relates_to' ) {
                            $task_relation = __('Related', 'wproject');
                        }

						$due_date               = strtotime($task_end_date);
						
						if($task_start_date || $task_end_date) {
							$new_task_start_date    = new DateTime($task_start_date);
							$the_task_start_date    = $new_task_start_date->format($date_format);
					
							$new_task_end_date      = new DateTime($task_end_date);
							$the_task_end_date      = $new_task_end_date->format($date_format);
						}

						if($task_priority == 'low') {
							$task_priority_name = /* translators: One of 4 possible task priorities */ __('Low', 'wproject');
						} else if($task_priority == 'normal' || $task_priority == '') {
							$task_priority_name = /* translators: One of 4 possible task priorities */ __('Normal', 'wproject');
						} else if($task_priority == 'high') {
							$task_priority_name = /* translators: One of 4 possible task priorities */ __('High', 'wproject');
						} else if($task_priority == 'urgent') {
							$task_priority_name = /* translators: One of 4 possible task priorities */ __('Urgent', 'wproject');
						}

						if($task_priority == '') {
							$task_priority = 'normal';
						}

						$the_user = get_userdata($author_id);
                        if($author_id != '0') {
                            $the_role	= $the_user->roles[0];

                            if(preg_match("/[a-e]/i", $first_name[0])) {
                                $colour = 'a-e';
                            } else if(preg_match("/[f-j]/i", $first_name[0])) {
                                $colour = 'f-j';
                            } else if(preg_match("/[k-o]/i", $first_name[0])) {
                                $colour = 'k-o';
                            } else if(preg_match("/[p-t]/i", $first_name[0])) {
                                $colour = 'p-t';
                            } else if(preg_match("/[u-z]/i", $first_name[0])) {
                                $colour = 'u-z';
                            } else {
                                $colour = '';
                            }
                        }

						if($author_id != '0') {
                            if($user_photo) {
                                $avatar         = $user_photo;
                                $avatar_id      = attachment_url_to_postid($avatar);
                                $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                                $avatar         = $small_avatar[0];
                                $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" />';
                            } else {
                                $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . $first_name[0] . $last_name[0] . '</div>';
                            }
                        } else {
                            $the_avatar = '<img src="' . get_template_directory_uri() . '/images/unknown-user.svg' . '" class="avatar" />';
                        }
						?>
							
							<div class="ui-state-default priority <?php echo $task_priority; ?> <?php if($due_date && $now > $due_date && $task_status !='complete') { echo 'task-overdue'; } ?> <?php if ( get_comments_number($task_id) > 0 ) { echo 'has-comment'; } ?> <?php if($user_ID != $current_user_id) { echo 'undraggable'; } ?> <?php if ($task_description) { echo 'has-description'; } ?> <?php if($enable_time && $task_timer == 'on') { echo 'timing'; } ?> task-owner-<?php echo $user_ID; ?>" id="<?php echo $task_id; ?>" data-pc-complete="<?php echo $task_pc_complete; ?>" data-order="" data-user-id="<?php echo $user_ID; ?>">
                                
                            
                                <a href="<?php echo get_permalink(); ?>" class="kanban-title">
                                    <?php if($enable_time && $task_timer == 'on') { ?>
                                        <img src="<?php echo get_template_directory_uri();?>/images/clock.svg" class="full-spin" /> 
                                    <?php } ?>
                                    <?php echo the_title(); ?>
                                </a>
                                
                                <?php if($context_label || $task_relation) { ?>
                                    <div class="kanban-context-label">
                                        <?php if($context_label) { ?>
                                            <span class="context-label"><?php echo str_replace(' ', '-', strtolower($context_label)); ?></span>
                                        <?php } ?>
                                        <?php if($task_relation) { ?>
                                            <span class="pill kanban-task-relation"><?php echo $task_relation; ?></span>
                                        <?php } ?>
                                    </div>
                                <?php } ?>

								<?php if($task_end_date) { ?>
									<span class="<?php if($due_date && $now > $due_date && $task_status !='complete') { echo 'kanban-task-overdue'; } ?>">
                                        <?php if($due_date && $now > $due_date && $task_status !='complete') { echo '<i data-feather="alert-circle" class="overdue"></i>'; } ?>
										<?php _e('Due', 'wproject'); ?> <?php echo $the_task_end_date; ?>
									</span>
								<?php } ?>

                                <?php if($kanban_card_descriptions == 'on' && $task_description) { ?>
                                    <p><?php echo wp_trim_words( $task_description, 25 ); ?></p>
                                <?php } ?>
								
								<?php if ( get_comments_number($task_id) > 0 ) { ?>
									<i class="comment-count">
										<i data-feather="message-circle"></i><?php echo get_comments_number($task_id); ?>
									</i>
								<?php } ?>

                                <?php if($task_pc_complete) { ?>
									<span class="pcc" title="<?php if($task_pc_complete == '100') { _e('100%', 'wproject');  }?>">
										<?php if($task_pc_complete == '100') { ?>
											<i data-feather="check-circle-2"></i>
										<?php } else { ?>
											<?php echo $task_pc_complete; ?>%
										<?php } ?>
									</span>
								<?php } ?>

                                <?php if($task_job_number) { ?>
									<span><strong><?php _e('Job #', 'wproject'); ?>:</strong> <?php echo $task_job_number; ?></span>
								<?php } ?>

                                <div class="kanban-card-lower">
                                    <?php echo $the_avatar; ?>
                                    <em>
                                        <?php if($author_id != '0') { ?>
                                            <?php echo $first_name; ?> <?php echo $last_name; ?>
                                        <?php } else { ?>
                                            <?php _e('Nobody', 'wproject'); ?>
                                        <?php } ?>
                                    </em>
                                </div>

							</div>

						<?php endwhile;
						wp_reset_postdata();
						?>

						<script>
							/* Inject count into H3 span */
							$('.on-hold-count').text(<?php echo $my_count; ?>);
						</script>

					</div>
					<!--/ End Box /-->

				</section>
				<!--/ End Section /-->

				<!--/ Start Section /-->
				<section data-status="complete">
					<h3>
						<span class="complete-count"></span> <?php _e('Complete', 'wproject'); ?>
						<em class="kanban-column-reverse" title="Reverse order"><i data-feather="triangle"></i></em>
					</h3>

					<!--/ Start Box /-->
					<div class="box connectedSortable" id="complete">

						<?php $todo = array(
							'post_type'         => 'task',
							'post_status'		=> 'publish',
							'category' 			=> $term_id,
							'orderby' 			=> 'name',
							'order' 			=> 'ASC',
							'posts_per_page'    => -1,
							'meta_key'          => 'task_status',
							'meta_value'        => array('complete'),
							'author'        	=>  $task_authors,
							'tax_query' => array(
								array(
									'taxonomy' => 'project',
									'field'    => 'slug',
									'terms'    => array( $term_object->slug ),
									'operator' => 'IN'
								),
							),
						);
						$query = new WP_Query($todo);
						$my_count = $query->post_count;
						while ($query->have_posts()) : $query->the_post();
						$task_id                = get_the_id();
						$author_id              = get_post_field ('post_author', $task_id);
						$user_ID                = get_the_author_meta( 'ID', $author_id );
						$first_name             = get_the_author_meta( 'first_name', $author_id );
						$last_name              = get_the_author_meta( 'last_name', $author_id );
						$user_photo             = get_the_author_meta( 'user_photo', $author_id );

						$task_status			= get_post_meta($task_id, 'task_status', TRUE);
                        $task_description       = get_post_meta($task_id, 'task_description', TRUE);
						$task_priority			= get_post_meta($task_id, 'task_priority', TRUE);
						$task_job_number        = get_post_meta($task_id, 'task_job_number', TRUE);
						$task_pc_complete		= get_post_meta($task_id, 'task_pc_complete', TRUE);
                        $task_relation          = get_post_meta($task_id, 'task_relation', TRUE);
                        $task_timer             = get_post_meta($task_id, 'task_timer', TRUE);

                        if($task_relation == 'has_issues_with' ) {
                            $task_relation = __('Has issues', 'wproject');
                        } else if($task_relation == 'is_blocked_by' ) {
                            $task_relation = __('Blocked', 'wproject');
                        } else if($task_relation == 'is_similar_to' ) {
                            $task_relation = __('Similarity', 'wproject');
                        } else if($task_relation == 'relates_to' ) {
                            $task_relation = __('Related', 'wproject');
                        }

						$date_format    		= get_option('date_format'); 
						$task_start_date        = get_post_meta($task_id, 'task_start_date', TRUE);
                        $task_end_date          = get_post_meta($task_id, 'task_end_date', TRUE);
                        $context_label          = get_post_meta($task_id, 'context_label', TRUE);
						$due_date               = strtotime($task_end_date);
						
						if($task_start_date || $task_end_date) {
							$new_task_start_date    = new DateTime($task_start_date);
							$the_task_start_date    = $new_task_start_date->format($date_format);
					
							$new_task_end_date      = new DateTime($task_end_date);
							$the_task_end_date      = $new_task_end_date->format($date_format);
						}

						if($task_priority == 'low') {
							$task_priority_name = /* translators: One of 4 possible task priorities */ __('Low', 'wproject');
						} else if($task_priority == 'normal' || $task_priority == '') {
							$task_priority_name = /* translators: One of 4 possible task priorities */ __('Normal', 'wproject');
						} else if($task_priority == 'high') {
							$task_priority_name = /* translators: One of 4 possible task priorities */ __('High', 'wproject');
						} else if($task_priority == 'urgent') {
							$task_priority_name = /* translators: One of 4 possible task priorities */ __('Urgent', 'wproject');
						}

						$the_user = get_userdata($author_id);
                        if($author_id != '0') {
                            $the_role	= $the_user->roles[0];

                            if(preg_match("/[a-e]/i", $first_name[0])) {
                                $colour = 'a-e';
                            } else if(preg_match("/[f-j]/i", $first_name[0])) {
                                $colour = 'f-j';
                            } else if(preg_match("/[k-o]/i", $first_name[0])) {
                                $colour = 'k-o';
                            } else if(preg_match("/[p-t]/i", $first_name[0])) {
                                $colour = 'p-t';
                            } else if(preg_match("/[u-z]/i", $first_name[0])) {
                                $colour = 'u-z';
                            } else {
                                $colour = '';
                            }
                        }

						if($author_id != '0') {
                            if($user_photo) {
                                $avatar         = $user_photo;
                                $avatar_id      = attachment_url_to_postid($avatar);
                                $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                                $avatar         = $small_avatar[0];
                                $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" />';
                            } else {
                                $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . $first_name[0] . $last_name[0] . '</div>';
                            }
                        } else {
                            $the_avatar = '<img src="' . get_template_directory_uri() . '/images/unknown-user.svg' . '" class="avatar" />';
                        }
						?>
							
							<div class="ui-state-default priority <?php echo $task_priority; ?> <?php if($due_date && $now > $due_date && $task_status !='complete') { echo 'task-overdue'; } ?> <?php if ( get_comments_number($task_id) > 0 ) { echo 'has-comment'; } ?> <?php if($user_ID != $current_user_id) { echo 'undraggable'; } ?> <?php if ($task_description) { echo 'has-description'; } ?> <?php if($enable_time && $task_timer == 'on') { echo 'timing'; } ?> task-owner-<?php echo $user_ID; ?>" id="<?php echo $task_id; ?>" data-pc-complete="<?php echo $task_pc_complete; ?>" data-order="" data-user-id="<?php echo $user_ID; ?>">
                                
                            
                                <a href="<?php echo get_permalink(); ?>" class="kanban-title">
                                    <?php if($enable_time && $task_timer == 'on') { ?>
                                        <img src="<?php echo get_template_directory_uri();?>/images/clock.svg" class="full-spin" /> 
                                    <?php } ?>
                                    <?php echo the_title(); ?>
                                </a>
                                
                                <?php if($context_label || $task_relation) { ?>
                                    <div class="kanban-context-label">
                                        <?php if($context_label) { ?>
                                            <span class="context-label"><?php echo str_replace(' ', '-', strtolower($context_label)); ?></span>
                                        <?php } ?>
                                        <?php if($task_relation) { ?>
                                            <span class="pill kanban-task-relation"><?php echo $task_relation; ?></span>
                                        <?php } ?>
                                    </div>
                                <?php } ?>

								<?php if($task_end_date) { ?>
									<span class="<?php if($due_date && $now > $due_date && $task_status !='complete') { echo 'kanban-task-overdue'; } ?>">
                                        <?php if($due_date && $now > $due_date && $task_status !='complete') { echo '<i data-feather="alert-circle" class="overdue"></i>'; } ?>
										<?php _e('Due', 'wproject'); ?> <?php echo $the_task_end_date; ?>
									</span>
								<?php } ?>

                                <?php if($kanban_card_descriptions == 'on' && $task_description) { ?>
                                    <p><?php echo wp_trim_words( $task_description, 25 ); ?></p>
                                <?php } ?>
								
								<?php if ( get_comments_number($task_id) > 0 ) { ?>
									<i class="comment-count">
										<i data-feather="message-circle"></i><?php echo get_comments_number($task_id); ?>
									</i>
								<?php } ?>

                                <?php if($task_pc_complete) { ?>
									<span class="pcc" title="<?php if($task_pc_complete == '100') { _e('100%', 'wproject');  }?>">
										<?php if($task_pc_complete == '100') { ?>
											<i data-feather="check-circle-2"></i>
										<?php } else { ?>
											<?php echo $task_pc_complete; ?>%
										<?php } ?>
									</span>
								<?php } ?>

                                <?php if($task_job_number) { ?>
									<span><strong><?php _e('Job #', 'wproject'); ?>:</strong> <?php echo $task_job_number; ?></span>
								<?php } ?>

                                <div class="kanban-card-lower">
                                    <?php echo $the_avatar; ?>
                                    <em>
                                        <?php if($author_id != '0') { ?>
                                            <?php echo $first_name; ?> <?php echo $last_name; ?>
                                        <?php } else { ?>
                                            <?php _e('Nobody', 'wproject'); ?>
                                        <?php } ?>
                                    </em>
                                </div>

							</div>

						<?php endwhile;
						wp_reset_postdata();
						?>

						<script>
							/* Inject count into H3 span */
							$('.complete-count').text(<?php echo $my_count; ?>);
						</script>

					</div>
					<!--/ End Box /-->

				</section>
				<!--/ End Section /-->

                <?php /* Get the PM email address for use in notification */
                    $category = get_the_terms( $post->ID, 'project' );     
                    foreach ( (array)$category as $cat) {
                        $term_id = @$cat->term_id;
                    }
                    $term_meta  = get_term_meta($term_id); 
                    $pm_id      = @$term_meta['project_manager'][0];
                ?>

				<input type="hidden" name="kanban_task_id" id="kanban_task_id" />
				<input type="hidden" name="kanban_task_order_id" id="kanban_task_order_id" />
				<input type="hidden" name="kanban_previous_pc_complete" id="kanban_previous_pc_complete" />
				<input type="hidden" name="kanban_column_task_status" id="kanban_column_task_status" />
                <input type="hidden" id="pm_user_id" name="pm_user_id" value="<?php echo $pm_id; ?>" />
                <input type="hidden" id="project_name" name="project_name" value="<?php $project_id = get_term($term_id); echo $project_id->name; ?>" />
                <input type="hidden" id="project_url" name="project_url" value="<?php echo home_url( $wp->request ); ?>" />

			</form>
			<!--/ End Kanban Container /-->

			<a class="close">
				<i data-feather="x"></i>
			</a>

			<ul class="kanban-filter">
				<li class="all selected" title="<?php _e('All tasks', 'wproject'); ?>"><i data-feather="grid"></i></li>
				<li class="overdue" title="<?php _e('Due tasks', 'wproject'); ?>"><i data-feather="alert-circle"></i></li>
				<li class="comms" title="<?php _e('Tasks with comments', 'wproject'); ?>"><i data-feather="message-circle"></i></li>
				<li class="your-tasks" title="<?php _e('Your tasks', 'wproject'); ?>"><i data-feather="user"></i></li>
				<?php if($kanban_card_descriptions) { ?>
				<li class="descs" title="<?php _e('Tasks with descriptions', 'wproject'); ?>"><i data-feather="list"></i></li>
				<?php } ?>
                <?php if($enable_time) { ?>
				<li class="timing" title="<?php _e('Tasks recording time', 'wproject'); ?>"><i data-feather="clock"></i></li>
				<?php } ?>					
				<li class="low" title="<?php _e('Low priority', 'wproject'); ?>"><i data-feather="check"></i></li>
				<li class="normal" title="<?php _e('Normal priority', 'wproject'); ?>"><i data-feather="check"></i></li>
				<li class="high" title="<?php _e('High priority', 'wproject'); ?>"><i data-feather="check"></i></li>
				<li class="urgent" title="<?php _e('Urgent priority', 'wproject'); ?>"><i data-feather="check"></i></li>
			</ul>

		</div>
		<!--/ End Kanban /-->   


    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/js/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js"></script>

    
    <script>
	<?php /* Only allow Kanban interaction if a PM or Admin role */
	if($role == 'project_manager' || $role == 'administrator' || $role == 'client' && $client_use_kanban == 'basic' || $role == 'client' && $client_use_kanban == 'full') { ?>
	$( function() {

		/* Destroy jQuery UI datepicker */
		$('input[type=date]').datepicker( 'destroy' );
		$('input[type=date]').removeClass('hasDatepicker').removeAttr('id');

        <?php if($role == 'client' && $client_use_kanban == 'basic') { ?>
            $('.undraggable').css('pointer-events', 'none').css('cursor', 'not-allowed');   /* Present that the element can't be dragged */
            $('.undraggable').draggable({ cancel: '.no' });                                 /* Actually make the element undraggable */
        <?php } else if($role == 'client' && $client_use_kanban == 'full') { ?>
            $('.undraggable').css('pointer-events', 'all').css('cursor', 'pointer');
        <?php } ?>

        /* Init jqueryui-touch-punch */
        $().draggable();

		$( '#not-started, #on-hold, #in-progress, #complete' ).sortable({
            connectWith: '.connectedSortable',
            update: function(event, ui) {
				// console.log('dragged');
            }
		});
		//.disableSelection();

        /* Do something when card is dropped into the Complete column */
        $('#complete').on('drop', function(event, ui) {
            // Do something
        });

        /* Do something on card mousedown */
        $('.ui-state-default').on('mousedown', function() {
            // var task_user_id = $(this).data('user-id');
            // if(task_user_id == <?php echo $current_user_id; ?>) {

            // } else {
            //     $('.box').css('opacity', '.4');
            //     setTimeout(function() {
            //         $('.box').css('opacity', '1');
            //     }, 1000);
            // }
            // console.log(task_user_id);
        });

        /* Update count when removing and receiving connections */
		$( '#not-started' ).sortable({
            connectWith: '.connectedSortable',
            receive: function(event, ui) {
				$('.not-started-count').text(Number($('.not-started-count').text())+1);
            },
			connectWith: '.connectedSortable',
            remove: function(event, ui) {
                $('.not-started-count').text(Number($('.not-started-count').text())-1);
            }
		});

		$( '#in-progress' ).sortable({
            connectWith: '.connectedSortable',
            receive: function(event, ui) {
				$('.in-progress-count').text(Number($('.in-progress-count').text())+1);
            },
			connectWith: '.connectedSortable',
            remove: function(event, ui) {
                $('.in-progress-count').text(Number($('.in-progress-count').text())-1);
            }
		});

		$( '#on-hold' ).sortable({
            connectWith: '.connectedSortable',
            receive: function(event, ui) {
				$('.on-hold-count').text(Number($('.on-hold-count').text())+1);
            },
			connectWith: '.connectedSortable',
            remove: function(event, ui) {
                $('.on-hold-count').text(Number($('.on-hold-count').text())-1);
            }
		});

		$( '#complete' ).sortable({
            connectWith: '.connectedSortable',
            receive: function(event, ui) {
				$('.complete-count').text(Number($('.complete-count').text())+1);
            },
			connectWith: '.connectedSortable',
            remove: function(event, ui) {
                $('.complete-count').text(Number($('.complete-count').text())-1);
            }
		});

        /* TODO: Drag and drop card order */
        // $('#not-started').on('drop', function(event, ui) {
        //     setTimeout(function() { 
		// 		$('#not-started .ui-state-default').each(function(i) {
        //             $(this).attr('data-order', i+1);
        //         });
		// 	}, 500);
        // });
        
        /* 100% text when task is dragged into complete columns */
        $('#complete').on('drop', function(event, ui) {
            var task_id = ui.draggable.attr('id');
            $('.box #'+task_id+' .pcc').text('100%');
        });
        /* Change % complete back to original value when task is dragged into #not-started, #on-hold or #in-progress columns */
        $('#not-started, #on-hold, #in-progress').on('drop', function(event, ui) {
            var task_id = ui.draggable.attr('id');
            var task_pc_complete = ui.draggable.attr('data-pc-complete');
            $('.box #'+task_id+' .pcc').text(task_pc_complete+'%');
        });

     
     	$('.box').droppable({ drop: function(event, ui) { 
			var task_id = ui.draggable.attr('id');	/* Task ID */
			var task_pc_complete = ui.draggable.attr('data-pc-complete');	/* Task ID */
			var column_name = $(this).attr('id');	/* Column task status */

			$('#kanban_task_id').val(task_id);
			$('#kanban_previous_pc_complete').val(task_pc_complete);
			$('#kanban_column_task_status').val(column_name);
			$('#arrange-kanban .close').attr('href', '<?php echo home_url( add_query_arg( array(), $wp->request ) ); ?>'); /* Add anchor to close button */


			setTimeout(function() { 
				$('#arrange-kanban').submit();
			}, 500);

		}});  
	});
	<?php } ?>

    $('.kanban-filter li').click(function() {
        $('#not-started .ui-state-default, #in-progress .ui-state-default, #on-hold .ui-state-default')<?php echo $kanban_card_method; ?>;
        $('.low, .normal, .urgent, .high').removeClass('show-icon');
        $(this).addClass('show-icon');
		$('.kanban-filter li').removeClass('selected');
        $(this).addClass('selected');
    });
    $('.kanban-filter .overdue').click(function() {
        $('.box .task-overdue')<?php echo $kanban_card_return; ?>;
    });
    $('.kanban-filter .low').click(function() {
        $('.box .low')<?php echo $kanban_card_return; ?>;
    });
    $('.kanban-filter .normal').click(function() {
        $('.box .normal')<?php echo $kanban_card_return; ?>;
    });
    $('.kanban-filter .high').click(function() {
        $('.box .high')<?php echo $kanban_card_return; ?>;
    });
    $('.kanban-filter .urgent').click(function() {
        $('.box .urgent')<?php echo $kanban_card_return; ?>;
    });
    $('.kanban-filter .all').click(function() {
        $('.box .ui-state-default')<?php echo $kanban_card_return; ?>;
    });
    $('.kanban-filter .comms').click(function() {
        $('.box .has-comment')<?php echo $kanban_card_return; ?>;
    });
	$('.kanban-filter .descs').click(function() {
        $('.box .has-description')<?php echo $kanban_card_return; ?>;
    });
	$('.kanban-filter .your-tasks').click(function() {
		$('.task-owner-<?php echo get_current_user_id(); ?>')<?php echo $kanban_card_return; ?>;
		$('.task-owner-<?php echo get_current_user_id(); ?>').addClass('your-task');
    });
    $('.kanban-filter .timing').click(function() {
        $('.box .timing')<?php echo $kanban_card_return; ?>;
    });

    $('.project-kanban-toggle, .kanban .close').click(function() {
		$('.kanban, .kanban-filter').toggleClass('show');
        
        <?php /* Close the right sidebar when existing the kanban (small screens) */
        if(wp_is_mobile()) { ?>
            $('.right').removeClass('move');
        <?php } ?>
	});

	$('.kanban-container h3 .kanban-column-reverse').click(function() {
		$(this).toggleClass('active');
		$(this).parent().next('.box').toggleClass('reverse');
	});

</script>
<?php }
}