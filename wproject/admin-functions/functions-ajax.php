<?php if ( ! defined( 'ABSPATH' ) ) { exit; }
/* Ajax script on front-end */
function ajax_scripts() {
	$theme 				= wp_get_theme();
    $theme_version		= $theme->Version;
	$parameters = array(
		'ajaxurl'	=> admin_url('admin-ajax.php'),
		'nonce' 	=> wp_create_nonce('inputs')
	);
	wp_enqueue_script('wproject-ajax', get_template_directory_uri().'/js/min/ajax.min.js?ver=' . $theme_version, array('jquery'), null, true);
	wp_localize_script('wproject-ajax', 'inputs', $parameters );
}
add_action('wp_enqueue_scripts', 'ajax_scripts');


function ajaxStatus($status, $message, $data) {
	$response = array (
		'status' 	=> $status,
		'message'	=> $message,
		'data' 		=> $data
		);
	$output = json_encode($response);
	exit($output);
}

/* Update user details */
add_action( 'wp_ajax_update_user_details', 'update_user_details' );
add_action( 'wp_ajax_nopriv_update_user_details', 'update_user_details' );

function update_user_details() {
	if(isset($_POST["account-form"])) {

		$nonce = $_POST['nonce'];
		
		if(wp_verify_nonce($nonce, 'inputs') !== false) {
			$user_ID					= sanitize_text_field($_POST['user_id']);
			$first_name					= sanitize_text_field($_POST['first_name']);
			$last_name					= sanitize_text_field($_POST['last_name']);
			$user_email					= sanitize_text_field($_POST['user_email']);
			$nickname					= sanitize_text_field($_POST['first_name']);
			$description				= sanitize_text_field($_POST['description']);
			$phone						= sanitize_text_field($_POST['phone']);
			$flock						= sanitize_text_field($_POST['flock']);
			$slack						= sanitize_text_field($_POST['slack']);
			$teams						= sanitize_text_field($_POST['teams']);
			$skype						= sanitize_text_field($_POST['skype']);
			$hangouts					= sanitize_text_field($_POST['hangouts']);
			$title						= sanitize_text_field($_POST['title']);
			$the_status					= sanitize_text_field($_POST['the_status']);
			$default_task_order			= sanitize_text_field($_POST['default_task_order']);
			$default_task_ownership		= sanitize_text_field($_POST['default_task_ownership']);
			$recent_tasks				= sanitize_text_field($_POST['recent_tasks']);
			$latest_activity			= sanitize_text_field($_POST['latest_activity']);
			$show_tips					= sanitize_text_field($_POST['show_tips']);
			$old_photo					= sanitize_text_field($_POST['old_photo']);
			$hide_gantt					= sanitize_text_field($_POST['hide_gantt']);
			$minimise_complete_tasks	= sanitize_text_field($_POST['minimise_complete_tasks']);
            $pm_only_show_my_projects	= sanitize_text_field($_POST['pm_only_show_my_projects']);
            $pm_auto_kanban_view	    = sanitize_text_field($_POST['pm_auto_kanban_view']);
			$dark_mode					= sanitize_text_field($_POST['dark_mode']);
            $show_latest_activity       = sanitize_text_field($_POST['show_latest_activity']);
            $notifications_count        = sanitize_text_field($_POST['notifications_count']);
			$dashboard_bar_chart		= sanitize_text_field($_POST['dashboard_bar_chart']);
            $remove_photo		        = sanitize_text_field($_POST['remove_photo']);

			$attachment_id 				= media_handle_upload( 'user_photo', 0 ); /* This gets the ID if the image that was uploaded */
			
			/***** Start Photo upload *****/
			if($_FILES['user_photo']['size'] !== 0) {

				/* Delete the old photo */
				$old_avatar = attachment_url_to_postid($old_photo);
				wp_delete_attachment($old_avatar);

				require_once(ABSPATH . "wp-admin" . '/includes/image.php');
				require_once(ABSPATH . "wp-admin" . '/includes/file.php');
				require_once(ABSPATH . "wp-admin" . '/includes/media.php');

                /* Create image sub sizes */
                $image_src = wp_get_attachment_image_src( $attachment_id, 'full' );
                $image_sizes = wp_get_attachment_image_sizes( $attachment_id, 'full' );
                
                /* Loop through the available image sizes and create sub-sizes */
                foreach ( $image_sizes as $size ) {
                    $size_data = explode( ' ', $size );
                    $size_name = $size_data[0];
                    $size_dimensions = explode( 'x', $size_data[1] );
                
                    add_image_size( $size_name, $size_dimensions[0], $size_dimensions[1], true );
                }

				if ( is_wp_error( $attachment_id ) ) {
					/* Error */
				} else {
					/* Success */
				}
				// Add photo attachment ID to user profile field.
				if($attachment_id) { 
					update_user_meta( $user_ID, 'user_photo', wp_get_attachment_url($attachment_id));
				}
			}
			/***** End Photo upload *****/
			wp_update_user( array( 'ID' => $user_ID, 'user_email' => $user_email ) );
            wp_update_user( array( 'ID' => $user_ID, 'display_name' => $first_name . ' ' . $last_name ) );

			update_user_meta( $user_ID, 'first_name', $first_name);
			update_user_meta( $user_ID, 'last_name', $last_name);
			update_user_meta( $user_ID, 'nickname', $nickname);
			update_user_meta( $user_ID, 'description', $description);
			update_user_meta( $user_ID, 'phone', $phone);
			update_user_meta( $user_ID, 'flock', $flock);
			update_user_meta( $user_ID, 'slack', $slack);
			update_user_meta( $user_ID, 'teams', $teams);
			update_user_meta( $user_ID, 'skype', $skype);
			update_user_meta( $user_ID, 'hangouts', $hangouts);
			update_user_meta( $user_ID, 'title', $title);
			update_user_meta( $user_ID, 'show_tips', $show_tips);
			update_user_meta( $user_ID, 'the_status', $the_status);
			update_user_meta( $user_ID, 'default_task_order', $default_task_order);
            update_user_meta( $user_ID, 'notifications_count', $notifications_count);
			update_user_meta( $user_ID, 'default_task_ownership', $default_task_ownership);
			update_user_meta( $user_ID, 'recent_tasks', $recent_tasks);
			update_user_meta( $user_ID, 'latest_activity', $latest_activity);
			update_user_meta( $user_ID, 'hide_gantt', $hide_gantt);
			update_user_meta( $user_ID, 'minimise_complete_tasks', $minimise_complete_tasks);
            update_user_meta( $user_ID, 'pm_only_show_my_projects', $pm_only_show_my_projects);
            update_user_meta( $user_ID, 'pm_auto_kanban_view', $pm_auto_kanban_view);
			update_user_meta( $user_ID, 'dark_mode', $dark_mode);
            update_user_meta( $user_ID, 'show_latest_activity', $show_latest_activity);
			update_user_meta( $user_ID, 'dashboard_bar_chart', $dashboard_bar_chart);

            if($remove_photo == 'yes') {
                update_user_meta( $user_ID, 'user_photo', '');
            }
		
			ajaxStatus('success', __('Account details saved', 'wproject'), '');
			
		} else {

			// No nonce!
			ajaxStatus('error', __('Nonce check is not allowed to fail.', 'wproject'), '');
			
		}
		
	} else {

		ajaxStatus('error', __('Something went wrong.', 'wproject'), '');

	}
}

/* Switch theme */
add_action( 'wp_ajax_change_theme', 'change_theme' );
add_action( 'wp_ajax_nopriv_change_theme', 'change_theme' );

function change_theme() {

	$params = array();
    parse_str($_POST["post"], $params);

    $user_ID    = get_current_user_id();
	$dark_mode	= sanitize_text_field($params['dark_mode']);

    if($dark_mode == 'yes') {
        update_user_meta( $user_ID, 'dark_mode', 'yes');
        ajaxStatus('success-theme-switched', __('Dark mode enabled.', 'wproject'), $user_ID);
    } else if($dark_mode == 'no') {
        update_user_meta( $user_ID, 'dark_mode', 'no');
        ajaxStatus('success-theme-switched', __('Light mode enabled.', 'wproject'), $user_ID);
    }
}

/* Manage files */
add_action( 'wp_ajax_manage_files', 'manage_files' );
add_action( 'wp_ajax_nopriv_manage_files', 'manage_files' );

function manage_files() {

	$params = array();
    parse_str($_POST["post"], $params);

	$file_option	= sanitize_text_field($params['file_option']);
    $file_task_id	= sanitize_text_field($params['file_task_id']);

    /* Delete all files */
    if($file_option == 'delete-all-files') {

        $attachments = get_attached_media( '', $file_task_id );
        foreach ($attachments as $attachment) {
            wp_delete_attachment( $attachment->ID, 'false' );
        }

		delete_post_meta( $file_task_id, 'task_files');
        ajaxStatus('success-files-deleted', __('All files were deleted.', 'wproject'), $file_task_id);

    /* TODO: Download all files */
    } else if($file_option == 'download-all-files') {

        // ajaxStatus('success-files-downloading', __('Getting files ready for download.', 'wproject'), $file_task_id);

    }
}


/* New project */
add_action( 'wp_ajax_add_new_project', 'add_new_project' );
add_action( 'wp_ajax_nopriv_add_new_project', 'add_new_project' );

function add_new_project() {

	$params = array();
    parse_str($_POST["post"], $params);

	    $project_name 				    = sanitize_text_field($params['project_name']);
		$project_brief_description	    = sanitize_text_field($params['description']);
		$project_full_description	    = sanitize_textarea_field($params['project_full_description']);
		$project_start_date			    = sanitize_text_field($params['project_start_date']);
		$project_end_date			    = sanitize_text_field($params['project_end_date']);
		$project_time_allocated		    = sanitize_text_field($params['project_time_allocated']);
		$project_job_number			    = sanitize_text_field($params['project_job_number']);
		$project_hourly_rate		    = sanitize_text_field($params['project_hourly_rate']);
		$project_manager			    = sanitize_text_field($params['project_manager']);
		$project_status				    = sanitize_text_field($params['project_status']);
		$project_creator			    = sanitize_text_field($params['project_creator']);
		$project_materials_total	    = sanitize_text_field($params['project_materials_total']);
		$project_contact			    = sanitize_text_field($params['project_contact']);
        $project_pep_talk_percentage    = sanitize_text_field($params['project_pep_talk_percentage']);
        $project_pep_talk_message       = sanitize_text_field($params['project_pep_talk_message']);
		$web_page_url					= sanitize_text_field($params['web_page_url']);
        $now				            = date('Y-m-d');

		$task_group_owner			    = sanitize_text_field($params['task_group_owner']);
		$task_group					    = sanitize_text_field($params['task_group']);

		/* Die if project name already exists */
		$term = term_exists( $project_name, 'project' );
        if ( $term !== 0 && $term !== null ) {
			ajaxStatus('error', __('A project with that name already exists.', 'wproject'), '');
			die;
        }

		$project_name_description = array(
			'cat_name'		=> $project_name,
			'taxonomy'		=> 'project',
			'category_description'	=> $project_brief_description
		);	
		$result 		= wp_insert_category($project_name_description); 
		$term_id 		= $result;
		$term 			= get_term( $term_id );
		$project_nav	= '<li><a href="' . home_url() . '/project/' . $term->slug . '">' . $term->name . '<span>0</span></a></li>';

		/* Insert material items */
		if($params['project_material_cost']) {
			$items_num = count( $params['project_material_cost'] );
		}

		for($i=0; $i< $items_num; $i++){
			$all_materials[] = array( 'project_material_name' => sanitize_text_field($params["project_material_name"][$i]), 'project_material_cost' => sanitize_text_field($params["project_material_cost"][$i] ));
		}
		$project_materials_list =  $all_materials;
		add_term_meta( $term_id, 'project_materials_list', $project_materials_list); 

		/* Pro tip: $term_id will return the category ID that was just created */
		add_term_meta( $term_id, "project_full_description" , $project_full_description );
		add_term_meta( $term_id, "project_start_date" , $project_start_date );
		add_term_meta( $term_id, "project_end_date" , $project_end_date );
		add_term_meta( $term_id, "project_time_allocated" , $project_time_allocated );
		add_term_meta( $term_id, "project_job_number" , $project_job_number );
		add_term_meta( $term_id, "project_hourly_rate" , $project_hourly_rate );
		add_term_meta( $term_id, "project_manager" , $project_manager );
		add_term_meta( $term_id, "project_status" , $project_status );
		add_term_meta( $term_id, "project_creator" , $project_creator );
		add_term_meta( $term_id, "project_materials_total" , $project_materials_total );
		add_term_meta( $term_id, "project_contact" , $project_contact );
        add_term_meta( $term_id, "project_pep_talk_percentage" , $project_pep_talk_percentage );
        add_term_meta( $term_id, "project_pep_talk_message" , $project_pep_talk_message );
		add_term_meta( $term_id, "web_page_url" , $web_page_url );
        add_term_meta( $term_id, "project_created_date" , $now );

		/* Create tasks from contacts */
		if($task_group && $task_group == 'task-contacts') {

            $group_posts = get_posts(array(
				'posts_per_page'	=> -1,
				'post_type' 		=> 'contacts_pro'
				)
			);
            
            foreach ($group_posts as $group_post) {

                if($task_group_owner == 'unchanged') {
                    $owner = $group_post->post_author;
                } else if($task_group_owner == 'remove') {
                    $owner = '';
                } else {
                    $owner = $task_group_owner;
                }

                $id = array(
                    'post_title'	=> $group_post->post_title,
                    'post_status'	=> 'publish',
                    'post_type'		=> 'task',
                    'post_author'	=> $owner,
                    'meta_key'		=> 'task_status',
                    'meta_value'	=> 'not-started',
                );
                $task_id = wp_insert_post($id);
                wp_set_object_terms( $task_id, intval($term_id), 'project', true );

                update_post_meta( $task_id, 'task_status' , 'not-started');
            
            }
			
        /* Otherwise if task group was selected */
		} else if($task_group) {

            $group_posts = get_posts(array(
				'posts_per_page'	=> -1,
				'post_type' 		=> 'task_group',
				'post_parent'		=> $task_group
				)
			);

			foreach ($group_posts as $group_post) {

				if($task_group_owner == 'unchanged') {
					$owner = $group_post->post_author;
				} else if($task_group_owner == 'remove') {
					$owner = '';
				} else {
					$owner = $task_group_owner;
				}
				
				$id = array(
					'post_title'	=> $group_post->post_title,
					'post_status'	=> 'publish',
					'post_type'		=> 'task',
					'post_author'	=> $owner,
					'meta_key'		=> 'task_status',
					'meta_value'	=> 'not-started',
				);
				$task_id = wp_insert_post($id);
				wp_set_object_terms( $task_id, intval($term_id), 'project', true );

				update_post_meta( $task_id, 'task_status' , 'not-started');
				update_post_meta( $task_id, "task_description" , get_post_meta($group_post->ID, 'task_description', TRUE));
				update_post_meta( $task_id, "task_priority" , get_post_meta($group_post->ID, 'task_priority', TRUE));
				update_post_meta( $task_id, "task_job_number" , get_post_meta($group_post->ID, 'task_job_number', TRUE));
				update_post_meta( $task_id, "task_milestone" , get_post_meta($group_post->ID, 'task_milestone', TRUE));
				update_post_meta( $task_id, "task_private" , get_post_meta($group_post->ID, 'task_private', TRUE));
				update_post_meta( $task_id, "task_relation" , get_post_meta($group_post->ID, 'task_relation', TRUE));
				update_post_meta( $task_id, "task_related" , get_post_meta($group_post->ID, 'task_related', TRUE));
				update_post_meta( $task_id, "task_explanation" , get_post_meta($group_post->ID, 'task_explanation', TRUE));
				update_post_meta( $task_id, 'subtask_list' , get_post_meta($group_post->ID, 'subtask_list', TRUE));
				update_post_meta( $task_id, "task_files" , get_post_meta($group_post->ID, 'task_files', TRUE));

				/* Include subtasks */
				if(isset($_POST['subtask_name'])) {
					$data = sanitize_text_field($_POST['subtask_name']);
					
					$items_num = count( $_POST['subtask_name'] );
					for($i=0; $i< $items_num; $i++) {
						$all_subtasks[] = array('subtask_name' => sanitize_text_field($_POST["subtask_name"][$i] ), 'subtask_status' => sanitize_text_field($_POST["subtask_status"][$i] ));
					}
					$subtask_list = $all_subtasks;
		
					update_post_meta( $post_id, 'subtask_list', $subtask_list); 
		
				}
			}
            /* End Add tasks from task group */
        }

		/* Add notification */
		$user 			= get_user_by('ID', $project_creator);
		$creator_name	= $user->first_name . ' ' . $user->last_name;
		$message_title	= sprintf( __('New Project', 'wproject'));
		$project_url	= home_url() . '/project/' . $term->slug;
		$message_body	= sprintf( __('Project <a href="%1$s" style="color:#00bcd4">%2$s</a> was created by %3$s.', 'wproject'),$project_url, $project_name, $creator_name);

		wp_insert_post(array (
			'post_type' 		=> 'message',
			'post_title' 		=> $message_title,
			'post_content' 		=> $message_body,
			'post_status' 		=> 'publish',
			'comment_status'	=> 'closed',
			'ping_status' 		=> 'closed'
		));

        /* Email notification if enabled */
        // $wproject_settings = wProject();
        // if($wproject_settings['notify_all_when_project_created']) {

        //     function all_recipients() {
        //         $args = array(
        //             'role__in'	=> array('team_member', 'project_manager', 'administrator')
        //         );
        //         $users = get_users($args);
        //         foreach ( $users as $user ) {
        //             return esc_html($user->user_email . ',');
        //         }
        //     }

        //     $subject 			= __('A new project was created', 'wproject');
        //     $sender             = get_option( 'admin_email' );
        //     $all_recipients     = all_recipients(); // TODO: Not working
        //     $link               = '...';
        //     $button_label       = __('View Project', 'wproject');

        //     $user_photo         = get_the_author_meta( 'user_photo', $task_creator_info->ID );

        //     if($user_photo) {
        //         $avatar         = $user_photo;
        //         $avatar_id      = attachment_url_to_postid($avatar);
        //         $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
        //         $avatar         = $small_avatar[0];
        //     } else {
        //         $avatar 		= get_template_directory_uri() . '/images/default-user.png';
        //     }

        //     include( get_stylesheet_directory() . '/inc/email-template.php' );
        // }

		// Success message
		ajaxStatus('success-project', __('New project created.', 'wproject'), $project_nav);
}


/* Edit project */
add_action( 'wp_ajax_edit_project', 'edit_project' );
add_action( 'wp_ajax_nopriv_edit_project', 'edit_project' );

function edit_project() {

	$params = array();
    parse_str($_POST["post"], $params);

	$project_id 				    = sanitize_text_field($params['project_id']);
	$project_name 				    = sanitize_text_field($params['project_name']);
	$project_brief_description	    = sanitize_text_field($params['description']);
	$project_full_description	    = sanitize_textarea_field($params['project_full_description']);
	$project_start_date			    = sanitize_text_field($params['project_start_date']);
	$project_end_date			    = sanitize_text_field($params['project_end_date']);
	$project_time_allocated		    = sanitize_text_field($params['project_time_allocated']);
	$project_job_number			    = sanitize_text_field($params['project_job_number']);
	$project_hourly_rate		    = sanitize_text_field($params['project_hourly_rate']);
	$project_manager			    = sanitize_text_field($params['project_manager']);
	$project_status				    = sanitize_text_field($params['project_status']);
	$project_creator			    = sanitize_text_field($params['project_creator']);
	$project_materials_total	    = sanitize_text_field($params['project_materials_total']);
	$project_contact			    = sanitize_text_field($params['project_contact']);
    $project_pep_talk_percentage    = sanitize_text_field($params['project_pep_talk_percentage']);
    $project_pep_talk_message       = sanitize_text_field($params['project_pep_talk_message']);
	$web_page_url       				= sanitize_text_field($params['web_page_url']);
	
	$term  			= get_term( $project_id, 'project' );
	$term_exists	= term_exists( $project_name, 'project' );
	$term_name 		= $term->name;
	$pname 			= $project_name;
	$project_slug	= str_replace(' ', '-', $project_name);

	/* Die if project name already exists */
	// if($term_name == $pname) {
	// 	$project_name = $project_name;
	// } else if ( $term_exists !== 0 && $term_exists !== null ) {
	// 	ajaxStatus('error', __('A project with that name already exists.', 'wproject'), '');
	// 	die;
	// }

	/* Insert material items */
	if($params['project_material_cost']) {
		$items_num = count( $params['project_material_cost'] );
	}

	for($i=0; $i< $items_num; $i++){
		$all_materials[] = array( 'project_material_name' => sanitize_text_field($params["project_material_name"][$i]), 'project_material_cost' => sanitize_text_field($params["project_material_cost"][$i] ));
	}
	$project_materials_list =  $all_materials;
	update_term_meta( $project_id, 'project_materials_list', $project_materials_list); 


	update_term_meta( $project_id, "project_full_description" , $project_full_description );
	update_term_meta( $project_id, "project_start_date" , $project_start_date );
	update_term_meta( $project_id, "project_end_date" , $project_end_date );
	update_term_meta( $project_id, "project_time_allocated" , $project_time_allocated );
	update_term_meta( $project_id, "project_job_number" , $project_job_number );
	update_term_meta( $project_id, "project_hourly_rate" , $project_hourly_rate );
	update_term_meta( $project_id, "project_manager" , $project_manager );
	update_term_meta( $project_id, "project_status" , $project_status );
	update_term_meta( $project_id, "project_creator" , $project_creator );
	update_term_meta( $project_id, "project_materials_total" , $project_materials_total );
	update_term_meta( $project_id, "project_contact" , $project_contact );
    update_term_meta( $project_id, "project_pep_talk_percentage" , $project_pep_talk_percentage );
    update_term_meta( $project_id, "project_pep_talk_message" , $project_pep_talk_message );
	update_term_meta( $project_id, "web_page_url" , $web_page_url );

	$update = wp_update_term( $project_id, 'project', array(
		'name' 			=> $project_name,
		'slug' 			=> $project_slug,
		'description'	=> $project_brief_description
	) );
	$update = $update;

	/* Add notification */
	$user 			= get_user_by('ID', $project_creator);
	$creator_name	= $user->first_name . ' ' . $user->last_name;
	$message_title	= sprintf( __('Project modified', 'wproject'));
	$project_url	= home_url() . '/project/' . $term->slug;
	$message_body	= sprintf( __('Project <a href="%1$s" style="color:#00bcd4">%2$s</a> was modified by %3$s.', 'wproject'),$project_url, $project_name, $creator_name);

	//$replace_h1	= sprintf( __('Edit Project : %1$s', 'wproject'), $project_name);

	wp_insert_post(array (
		'post_type' 		=> 'message',
		'post_title' 		=> $message_title,
		'post_content' 		=> $message_body,
		'post_status' 		=> 'publish',
		'comment_status'	=> 'closed',
		'ping_status' 		=> 'closed'
		));

	// Success message
	ajaxStatus('success-edit-project', __('Project edits saved.', 'wproject'), $project_name);
}


/* Archive project */
add_action( 'wp_ajax_archive_project', 'archive_project' );
add_action( 'wp_ajax_nopriv_archive_project', 'archive_project' );

function archive_project() {

	$params = array();
    parse_str($_POST["post"], $params);

	$project_id 				= sanitize_text_field($params['project_id']);
	$project_archive_location 	= sanitize_text_field($params['project_archive_location']);

	update_term_meta( $project_id, 'project_status' , 'archived' );

	// Success message
	ajaxStatus('success-archive-project', __('The project was archived.', 'wproject'), $project_name);
}



/* Delete completed tasks in the project */
add_action( 'wp_ajax_delete_completed_project_tasks', 'delete_completed_project_tasks' );
add_action( 'wp_ajax_nopriv_delete_completed_project_tasks', 'delete_completed_project_tasks' );

function delete_completed_project_tasks() {

	$params = array();
    parse_str($_POST["post"], $params);

	$project_id = sanitize_text_field($params['project_id']);

    $args = array(
        'post_type'         => 'task',
        'tax_query' => array(
            array(
                'taxonomy' => 'project',
                'field' => 'term_id',
                'terms' => $project_id,
            )
        ),
        'posts_per_page'    => -1
    );
    $query = new WP_Query($args);
    while ($query->have_posts()) : $query->the_post();
        
        $task_status = get_post_meta(get_the_id(), 'task_status', TRUE);
        if($task_status == 'complete') {
            wp_delete_post(get_the_id());
        }

    endwhile;
    wp_reset_postdata();

	// Success message
	ajaxStatus('success-deleted-completed-project-tasks', __('All completed tasks were deleted from this project.', 'wproject'), $project_name);
}




/* New task */
add_action( 'wp_ajax_add_new_task', 'add_new_task' );
add_action( 'wp_ajax_nopriv_add_new_task', 'add_new_task' );

function add_new_task() {

	$task_name			= sanitize_text_field($_POST['task_name']);
	$task_owner			= sanitize_text_field($_POST['task_owner']);
	$task_description	= sanitize_textarea_field($_POST['task_description']);
	$task_priority		= sanitize_text_field($_POST['task_priority']);
	$task_project		= sanitize_text_field($_POST['task_project']);
	$task_start_date	= sanitize_text_field($_POST['task_start_date']);
	$task_end_date		= sanitize_text_field($_POST['task_end_date']);
	$task_job_number	= sanitize_text_field($_POST['task_job_number']);
	$task_status		= sanitize_text_field($_POST['task_status']);
	$task_milestone 	= isset( $_POST['task_milestone'] ) && $_POST['task_milestone'] === 'on' ? 'yes' : 'no';
	$task_private		= isset( $_POST['task_private'] ) && $_POST['task_private'] === 'on' ? 'yes' : 'no';
	$task_pc_complete	= sanitize_text_field($_POST['pc_complete']);
	$task_relation		= sanitize_text_field($_POST['task_relation']);
	$task_related		= sanitize_text_field($_POST['task_related']);
	$task_explanation	= sanitize_textarea_field($_POST['task_explanation']);
    $context_label		= sanitize_text_field($_POST['context_label']);
    $initiator_id    	= sanitize_text_field($_POST['initiator_id']);
	$web_page_url    	= sanitize_text_field($_POST['web_page_url']);
    $project_term       = get_term($task_project, 'project');
    $project_slug       = $project_term->slug;
    $project_url        = home_url() . '/project/' . $project_slug;
    $project_name       = $project_term->name;
    $taxonomy           = 'project';

    /* Check if post title already exists in the project */
    $args = array(
        'post_type' => 'task',
        'tax_query' => array(
            array(
                'taxonomy' => 'project',
                'field' => 'term_id',
                'terms' => $task_project,
            )
        ),
        'title' => $task_name,
        'posts_per_page' => 1,
    );
    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        ajaxStatus('task-exists', __('A task with this name already exists in the selected project.', 'wproject'), ' ' . $task_name);
        die;
    } else {
        /* The post title doesn't exist */
    }

	/* Insert post */
	$the_task_args = array(
		'post_title'    => $task_name,
		'post_status'   => 'publish',
		'post_type'   	=> 'task',
		'post_author'   => $task_owner
	);
	$post_id = wp_insert_post( $the_task_args );

	/* Insert subtasks */
	if($_POST['subtask_name']) {
		$items_num = count( $_POST['subtask_name'] );
	}

	for ($i = 0; $i < $items_num; $i++) {
		$all_subtasks[] = array(
			'subtask_name' => sanitize_text_field($_POST["subtask_name"][$i]),
			'subtask_description' => wp_kses_post(sanitize_textarea_field($_POST["subtask_description"][$i])),
			'subtask_status' => sanitize_text_field($_POST["subtask_status"][$i])
		);
	}
	$subtask_list = $all_subtasks;

	/* Add files */
	$file_url = array();
	if(!empty($_FILES['task_files']['name'][0])) {
		$files = $_FILES["task_files"];
		foreach ($files['name'] as $key => $value) {
			if ($files['name'][$key]) {
				$upload = wp_upload_bits($files['name'][$key], null, file_get_contents($files['tmp_name'][$key]));

				$filename = $upload['file'];
				$wp_filetype = wp_check_filetype($filename, null);
				
				$attachment_data = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_title' =>  $files['name'][$key] ,
					'post_content' => '',
					'post_status' => 'inherit'
				);
				$attachment_id = wp_insert_attachment( $attachment_data, $filename, $post_id );
				$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
				wp_update_attachment_metadata( $attachment_id, $attachment_data );

				$url = wp_get_attachment_image_src($attachment_id);
				array_push($file_url, $url);
			}
		}
		update_post_meta( $post_id, "task_files" , $file_url);
	}

	/* update post meta */
	update_post_meta( $post_id, "task_owner" , $task_owner);
	update_post_meta( $post_id, "task_description" , $task_description);
	update_post_meta( $post_id, "task_priority" , $task_priority);
	update_post_meta( $post_id, "task_start_date" , $task_start_date);
	update_post_meta( $post_id, "task_end_date" , $task_end_date);
	update_post_meta( $post_id, "task_job_number" , $task_job_number);
	update_post_meta( $post_id, "task_status" , $task_status);
	update_post_meta( $post_id, "task_milestone" , $task_milestone);
	update_post_meta( $post_id, "task_private" , $task_private);
	update_post_meta( $post_id, "task_pc_complete" , $task_pc_complete);
	update_post_meta( $post_id, "task_relation" , $task_relation);
	update_post_meta( $post_id, "task_related" , $task_related);
	update_post_meta( $post_id, "task_explanation" , $task_explanation);
	update_post_meta( $post_id, 'subtask_list', $subtask_list); 
    update_post_meta( $post_id, 'context_label', $context_label); 
    update_post_meta( $post_id, 'initiator_id', $initiator_id); 
	update_post_meta( $post_id, 'web_page_url', $web_page_url); 
	
	wp_set_object_terms( $post_id, intval( $task_project ), 'project' );

	/* 
		If the owner of the created task is NOT the same as the current user ID,
		then the task is for someone else, so let's notify that person.
	*/
	if($task_owner != get_current_user_id() ) {
		
		/* Add notification */
		$task_creator_info	= get_userdata(get_current_user_id());
		$task_owner_info	= get_userdata($task_owner);
		$creator_name		= $task_creator_info->first_name . ' ' . $task_creator_info->last_name;
		$creator_url		= get_the_permalink(109) . '/?id=' . $task_creator_info->ID;
		$message_title		= sprintf( __('New Task', 'wproject'));
		$task_url			= get_the_permalink($post_id);
		$task_title			= get_the_title($post_id);
		$message_body		= sprintf( __('<a href="%1$s" style="color:#00bcd4">%2$s</a> created a task for you: <a href="%3$s" style="color:#00bcd4">%4$s</a>.', 'wproject'),$creator_url, $creator_name, $task_url, $task_title);

		/* Email notification vars */
		$subject 			= __('You have a new task', 'wproject');
		$sender             = get_option( 'admin_email' );
		$recipient          = $task_owner_info->user_email;
		$link               = $task_url;
		$button_label       = __('View Task', 'wproject');

		$user_photo         = get_the_author_meta( 'user_photo', $task_creator_info->ID );

		if($user_photo) {
			$avatar         = $user_photo;
			$avatar_id      = attachment_url_to_postid($avatar);
			$small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
			$avatar         = $small_avatar[0];
		} else {
			$avatar 		= get_template_directory_uri() . '/images/default-user.png';
		}

		wp_insert_post(array (
			'post_type' 		=> 'message',
			'post_title' 		=> $message_title,
			'post_content' 		=> $message_body,
			'post_status' 		=> 'publish',
			'post_author'		=> $task_owner,
			'comment_status'	=> 'closed',
			'ping_status' 		=> 'closed'
		));
	}

	$wproject_settings = wProject();
	if($wproject_settings['notify_when_task_created']) {
        include( get_stylesheet_directory() . '/inc/email-template.php' );
	}

	// Success message
	ajaxStatus('task created', preg_replace('/[^A-Za-z0-9. -,]/', '', $task_name), get_the_permalink($post_id));
}


/* Edit task */
add_action( 'wp_ajax_edit_task', 'edit_task' );
add_action( 'wp_ajax_nopriv_edit_task', 'edit_task' );

function edit_task() {

	$task_id			= sanitize_text_field($_POST['task_id']);
	$task_name			= sanitize_text_field($_POST['task_name']);
	$task_description	= sanitize_textarea_field($_POST['task_description']);
	$task_project		= sanitize_text_field($_POST['task_project']);
	$task_owner			= sanitize_text_field($_POST['task_owner']);
	$task_priority		= sanitize_text_field($_POST['task_priority']);
	$task_start_date	= sanitize_text_field($_POST['task_start_date']);
	$task_end_date		= sanitize_text_field($_POST['task_end_date']);
	$task_job_number	= sanitize_text_field($_POST['task_job_number']);
	$task_status		= sanitize_text_field($_POST['task_status']);
	$task_milestone		= sanitize_text_field($_POST['task_milestone']);
	$task_private		= sanitize_text_field($_POST['task_private']);
	$task_pc_complete	= sanitize_text_field($_POST['task_pc_complete']);
	$task_relation		= sanitize_text_field($_POST['task_relation']);
	$task_related		= sanitize_text_field($_POST['task_related']);
	$task_explanation	= sanitize_textarea_field($_POST['task_explanation']);
	$task_hours			= sanitize_text_field($_POST['task_hours']);
    $context_label      = sanitize_text_field($_POST['context_label']);
	$web_page_url    	= sanitize_text_field($_POST['web_page_url']);
	
	$edit_task_args = array(
		'ID'    		=> $task_id,
		'post_title'    => $task_name,
		'post_status'   => 'publish',
		'post_type'   	=> 'task',
		'post_author'   => $task_owner,
		'filter'		=> true,
		'post_name'		=> str_replace(' ', '-', $task_name)
	);
	
	$result		= wp_update_post( $edit_task_args );
	$post_id	= $result;
	$replace_h1	= sprintf( /* translators: Example: Edit task: Some task name */ __('Edit Task: %1$s', 'wproject'), $task_name);

	/* Update subtasks */
	if ($_POST['subtask_name']) {
		$items_num = count($_POST['subtask_name']);
	}
	
	for ($i = 0; $i < $items_num; $i++) {
		$all_subtasks[] = array(
			'subtask_name' => sanitize_text_field($_POST["subtask_name"][$i]),
			'subtask_description' => wp_kses_post(sanitize_textarea_field($_POST["subtask_description"][$i])),
			'subtask_status' => sanitize_text_field($_POST["subtask_status"][$i])
		);
	}
	$subtask_list = $all_subtasks;

	
	/* Add files */
	$file_url = array();
	
	$files = $_FILES["task_files"];
	foreach ($files['name'] as $key => $value) {
		if ($files['name'][$key]) {
			$upload = wp_upload_bits($files['name'][$key], null, file_get_contents($files['tmp_name'][$key]));

			$filename = $upload['file'];
			$wp_filetype = wp_check_filetype($filename, null);
			
			$attachment_data = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title' =>  $files['name'][$key] ,
				'post_content' => '',
				'post_status' => 'inherit'
			);
			$attachment_id = wp_insert_attachment( $attachment_data, $filename, $post_id );
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
			wp_update_attachment_metadata( $attachment_id, $attachment_data );

			$url = wp_get_attachment_image_src($attachment_id);
			array_push($file_url, $url);
		}
	}
	if(file_exists($_FILES['task_files']['tmp_name'][0])) {
		update_post_meta( $post_id, "task_files" , $file_url);
	}	

	update_post_meta( $post_id, 'subtask_list', $subtask_list); 
	update_post_meta( $post_id, "task_description" , $task_description);
	update_post_meta( $post_id, "task_owner" , $task_owner);
	update_post_meta( $post_id, "task_priority" , $task_priority);
	update_post_meta( $post_id, "task_start_date" , $task_start_date);
	update_post_meta( $post_id, "task_end_date" , $task_end_date);
	update_post_meta( $post_id, "task_job_number" , $task_job_number);
	update_post_meta( $post_id, "task_status" , $task_status);
	update_post_meta( $post_id, "task_milestone" , $task_milestone);
	update_post_meta( $post_id, "task_private" , $task_private);
	update_post_meta( $post_id, "task_pc_complete" , $task_pc_complete);
	update_post_meta( $post_id, "task_relation" , $task_relation);
	update_post_meta( $post_id, "task_related" , $task_related);
	update_post_meta( $post_id, "project_manager" , $project_manager);
	update_post_meta( $post_id, "task_explanation" , $task_explanation);
	update_post_meta( $post_id, "web_page_url" , $web_page_url);

    if($context_label) {
        update_post_meta( $post_id, "context_label" , $context_label);
    }

	wp_set_object_terms( $post_id, intval( $task_project ), 'project' );
	ajaxStatus('success-edit-task', __('Task saved.', 'wproject'), $replace_h1);
	
}


/* Delete file */
add_action( 'wp_ajax_delete_file', 'delete_file' );
add_action( 'wp_ajax_nopriv_delete_file', 'delete_file' );

function delete_file() {

	$params = array();
    parse_str($_POST["post"], $params);

	$task_id	= sanitize_text_field($params['task_id']);
	$file_id	= sanitize_text_field($params['file_id']);

	/* Delete physical attacment */
	wp_delete_attachment( $file_id);

	/* Delete task_files post meta if there are no files for the task */
	$get_attachments        = get_children( array( 'post_parent' => $task_id ) );
    $attachments_count      = count( $get_attachments );
	if($attachments_count == 0) {
		delete_post_meta( $task_id, 'task_files');
	}

	/* Success message */
	ajaxStatus('success-file-deleted', __('File deleted.', 'wproject'), $file_id);
}


/* Delete comment */
add_action( 'wp_ajax_delete_comment', 'delete_comment' );
add_action( 'wp_ajax_nopriv_delete_comment', 'delete_comment' );

function delete_comment() {

	$params = array();
    parse_str($_POST["post"], $params);

	$comment_id	        = sanitize_text_field($params['comment_id']);
    $comment_author_id 	= get_comment($comment_id)->user_id;

    if($comment_author_id == get_current_user_id()) {
        wp_delete_comment($comment_id, true);
	    ajaxStatus('success-comment-deleted', __('Comment deleted.', 'wproject'), $comment_id);
    } else {
        ajaxStatus('success-comment-delete-failed', __('The comment was not deleted.', 'wproject'), $comment_id);
    }
    
}


/* Comment Status */
add_action( 'wp_ajax_comment_status', 'comment_status' );
add_action( 'wp_ajax_nopriv_comment_status', 'comment_status' );

function comment_status() {

	$params = array();
    parse_str($_POST["post"], $params);

	$post_id            = sanitize_text_field($params['post_id']);
    $comment_status     = sanitize_text_field($params['comment_status']);

    if($comment_status == 'open') {
        $the_status = 'open';
    } else if($comment_status == 'closed') {
        $the_status = 'closed';
    }

    $update_post_args = array(
        'ID'                => $post_id,
        'comment_status'    => $the_status
    );
    wp_update_post($update_post_args);

    ajaxStatus('success-comments-status-updated', __('Comments status changing...', 'wproject'), $post_id);

}


/* Update Gantt Pro */
add_action( 'wp_ajax_update_gantt_pro', 'update_gantt_pro' );
add_action( 'wp_ajax_nopriv_update_gantt_pro', 'update_gantt_pro' );

function update_gantt_pro() {

	$params = array();
    parse_str($_POST["post"], $params);

	$gantt_mode			= sanitize_text_field($params['gantt_mode']);
	$gantt_type			= sanitize_text_field($params['gantt_type']);

	/* Tasks in the Gantt on a Project page */
	if($gantt_type == 'tasks') {

		$task_id	        = sanitize_text_field($params['task_id']);
		$task_start_date	= sanitize_text_field($params['task_start_date']);
		$task_end_date	    = sanitize_text_field($params['task_end_date']);
		$task_pc_complete   = sanitize_text_field($params['task_pc_complete']);
		$task_name          = sanitize_text_field($params['task_name']);

		/* FIXME: 
			Dragging end dates causes then to be 1 day short. This removes one day.
			For some reason this only happens when in Day view (mode).
			Obviosuly this is not a real fix, and the Frappe Gantt repo appears
			to be abandoned :-(
		*/
		if($gantt_mode == 'Day') {
			$end_date = new DateTime($task_end_date);
			$end_date->modify('-1 day');
			$task_end_date = $end_date->format('Y-m-d');
		} else {
			$task_end_date = sanitize_text_field($params['task_end_date']);
		}

		/* Convert dates to epoch */
		$new_start_date 	= new DateTime($task_start_date);
		$new_end_date 		= new DateTime($task_end_date);

		$the_new_start_date	= $new_start_date->getTimestamp();
		$the_new_end_date	= $new_end_date->getTimestamp();

		/* Make sure we have a start and end date, and end date is greater than start date... */
		if($task_start_date && $task_end_date && $the_new_end_date > $the_new_start_date) {

			/* If start and end date, and no percentage complete */
			if($task_start_date && $task_end_date && !$task_pc_complete) {

				update_post_meta( $task_id, 'task_start_date', $task_start_date); 
				update_post_meta( $task_id, 'task_end_date', $task_end_date); 

				ajaxStatus('success-gantt-pro-updated', $task_name . ' ' . __('updated.', 'wproject'), $task_id);
				//ajaxStatus('success-gantt-pro-updated', __('01', 'wproject'), $task_id);

			/* If start and end date, and percentage complete */
			} else if($task_start_date && $task_end_date && $task_pc_complete) {

				update_post_meta( $task_id, 'task_start_date', $task_start_date); 
				update_post_meta( $task_id, 'task_end_date', $task_end_date);
				update_post_meta( $task_id, 'task_pc_complete', $task_pc_complete);

				if($task_pc_complete > 0 && $task_pc_complete < 99) {
					update_post_meta( $task_id, 'task_status', 'in-progress');
					ajaxStatus('success-gantt-pro-updated', $task_name . ' ' . sprintf( __('progress changed to %1$s %2$s', 'wproject'),$task_pc_complete, '%'), $task_id);
				} else if($task_pc_complete == 0) {
					update_post_meta( $task_id, 'task_status', 'not-started');
					ajaxStatus('success-gantt-pro-updated', $task_name . ' ' . sprintf( __('progress changed to %1$s %2$s', 'wproject'),$task_pc_complete, '%'), $task_id);
				} else if($task_pc_complete == 100) {
					update_post_meta( $task_id, 'task_status', 'complete');
					ajaxStatus('success-gantt-pro-updated', $task_name . ' ' . sprintf( __('complete!', 'wproject'),$task_pc_complete, '%'), $task_id);
				}

			}
		}

	/* Tasks in the Gantt on the Dashboard and All Projects pages */
	} else if($gantt_type == 'projects') {

		$project_id	        	= sanitize_text_field($params['project_id']);
		$project_start_date		= sanitize_text_field($params['project_start_date']);
		$project_end_date		= sanitize_text_field($params['project_end_date']);
		$project_name          	= sanitize_text_field($params['project_name']);

		/* FIXME: 
			Dragging end dates causes then to be 1 day short. This removes one day.
			For some reason this only happens when in Day view (mode).
			Obviosuly this is not a real fix, and the Frappe Gantt repo appears
			to be abandoned :-(
		*/
		if($gantt_mode == 'Day') {
			$end_date = new DateTime($project_end_date);
			$end_date->modify('-1 day');
			$project_end_date = $end_date->format('Y-m-d');
		} else {
			$project_end_date = sanitize_text_field($params['project_end_date']);
		}

		/* Convert dates to epoch */
		$new_start_date 	= new DateTime($project_start_date);
		$new_end_date 		= new DateTime($project_end_date);

		$the_new_start_date	= $new_start_date->getTimestamp();
		$the_new_end_date	= $new_end_date->getTimestamp();

		/* Make sure we have a start and end date, and end date is greater than start date... */
		if($project_start_date && $project_end_date && $the_new_end_date > $the_new_start_date) {

			update_term_meta( $project_id, "project_start_date" , $project_start_date );
			update_term_meta( $project_id, "project_end_date" , $project_end_date );
			
			ajaxStatus('success-gantt-pro-updated', $project_name . ' ' . __('updated.', 'wproject'), $project_id);
		}


	} else {

		ajaxStatus('success-gantt-pro-updated', __('Something went wrong. Reload the page and try again.', 'wproject'), $task_id);

	}
}

/* Extend project */
add_action( 'wp_ajax_extend_project_deadline', 'extend_project_deadline' );
add_action( 'wp_ajax_nopriv_extend_project_deadline', 'extend_project_deadline' );

function extend_project_deadline() {

	$params = array();
    parse_str($_POST["post"], $params);

	$project_id			= sanitize_text_field($params['project_id']);
	$project_url		= sanitize_text_field($params['project_url']);
	$project_end_date	= sanitize_text_field($params['project_end_date']);
	
	$project_name		= get_term($project_id);
	$the_project_name	= $project_name->name;
	
	$term_meta			= get_term_meta($project_id); 
	$date_format		= get_option('date_format'); 
	
	/* Update the due date */		
	update_term_meta( $project_id, 'project_end_date', $project_end_date); 

	$new_date			= new DateTime($project_end_date);
	$text_end_date		= $new_date->format($date_format);

	/* FIXME: Add notification
	$message_title	= sprintf( __('Project deadline extended', 'wproject'));
	$message_body	= sprintf( __('The project deadline for <a href="%1$s" style="color:#00bcd4">%2$s</a> has been extended to %3$s.', 'wproject'),$project_url, $the_project_name, $text_end_date);
	$users = get_users();
	foreach ( $users as $user ) {
		wp_insert_post(array (
			'post_type' 		=> 'message',
			'post_title' 		=> $message_title,
			'post_content' 		=> $message_body,
			'post_author'		=> $user->ID,
			'post_status' 		=> 'publish',
			'comment_status'	=> 'closed',
			'ping_status' 		=> 'closed'
		));
	}
	*/

	ajaxStatus('success-project-extended', sprintf( __('Project extended to %1$s.', 'wproject'),$text_end_date), $project_id);
}


/* Transfer Projects */
add_action( 'wp_ajax_transfer_projects', 'transfer_projects' );
add_action( 'wp_ajax_nopriv_transfer_projects', 'transfer_projects' );

function transfer_projects() {

	$params = array();
    parse_str($_POST["post"], $params);

	$this_user_id   = sanitize_text_field($params['this_user_id']);
    $new_owner_name = get_user_meta( $this_user_id, 'first_name', true ) . ' ' . get_user_meta( $this_user_id, 'last_name', true );
	$old_owner_name = get_user_meta( get_current_user_id(), 'first_name', true ) . ' ' . get_user_meta( get_current_user_id(), 'last_name', true );
	$project_url 	= home_url() . '/projects';
	$new_user_info	= get_userdata($this_user_id);
	$new_user_email	= $new_user_info->user_email;
	$user_photo		= get_the_author_meta( 'user_photo', get_current_user_id() );

    $projects = array(
        'taxonomy'      => 'project',
        'hide_empty'    => 0,
        'meta_query' => array(
            array(
               'key'       => 'project_manager',
               'value'     => get_current_user_id(),
               'compare'   => '=='
            )
        ),
    );
    $cats = get_categories($projects);
    $i = 0;
    foreach($cats as $cat) { 

        $term_id                    = $cat->term_id; 
        $term_meta                  = get_term_meta($term_id); 
        $project_status             = $term_meta['project_status'][0];
        $i++;

        if( $i++ > 0) {
            update_term_meta( $term_id, "project_manager" , $this_user_id );
        }
        
    }

    $message_success = sprintf( __('Your projects were transferred to %1$s.', 'wproject'), $new_owner_name);
    $message_fail = 'You do not have any projects to transfer.';

    if($i == 0) {
        ajaxStatus('failed-projects-transferred', __($message_fail, 'wproject'), '');
    } else {


		/* Send email notification. */
		$message_title	= sprintf(__('You have inherited projects', 'wproject'));
		$message_body	= sprintf(__('Projects formerly managed by %1$s are now managed by you. Visit <a href="%2$s" style="color:#00bcd4">the projects page</a> to see your projects.', 'wproject'), $old_owner_name, $project_url);
		$subject		= sprintf( __('You have inherited projects', 'wproject'));
		$sender			= get_option( 'admin_email' );
        $recipient		= $new_user_email;
		$link			= $project_url;
        $button_label	= __('View Projects', 'wproject');
	
		if($user_photo) {
			$avatar         = $user_photo;
			$avatar_id      = attachment_url_to_postid($avatar);
			$small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
			$avatar         = $small_avatar[0];
		} else {
			$avatar 		= get_template_directory_uri() . '/images/default-user.png';
		}
	
		include( get_stylesheet_directory() . '/inc/email-template.php' );

        ajaxStatus('success-projects-transferred', __($message_success, 'wproject'), '');
    }
	
}


/* Update subtasks */
add_action( 'wp_ajax_update_subtask_list', 'update_subtask_list' );
add_action( 'wp_ajax_nopriv_update_subtask_list', 'update_subtask_list' );

function update_subtask_list() {

	$params = array();
    parse_str($_POST["post"], $params);

    $wproject_settings                  = wProject();
    
	$task_id 			                = sanitize_text_field($params['task_id']);
	$task_pc_complete	                = sanitize_text_field($params['task_pc_complete']);
    $task_status	                    = sanitize_text_field($params['task_status']);
    $pm_user_id		                    = sanitize_text_field($params['pm_user_id']);
    $pm_data                            = get_userdata( $pm_user_id );
    $task_owner_id		                = get_post_field( 'post_author', $task_id );
    $notify_pm_when_task_complete       = $wproject_settings['notify_pm_when_task_complete'];
    $notify_pm_when_subtasks_complete   = $wproject_settings['notify_pm_when_subtasks_complete'];
    

	/* Update subtasks */
	$items_num = count( $params['subtask_name'] );

	for($i=0; $i< $items_num; $i++){
		$all_subtasks[] = array(
            'subtask_name' => sanitize_text_field($params["subtask_name"][$i]),
            'subtask_description' => wp_kses_post(sanitize_textarea_field($params["subtask_description"][$i] )),
            'subtask_status' => sanitize_text_field($params["subtask_status"][$i] )
        );
	}
	$subtask_list = $all_subtasks;

	//print_r($subtask_list); die;

	update_post_meta( $task_id, 'subtask_list', $subtask_list); 
	update_post_meta( $task_id, 'task_pc_complete', $task_pc_complete); 
    update_post_meta( $task_id, 'task_status', $task_status); 

    /* If subtasks are complete, notify logic */
    if($notify_pm_when_subtasks_complete == 'on' && $task_status == 'complete') {

        /* Prepare email notification */
        $task_creator_info	= get_userdata($task_owner_id);
        $creator_name		= $task_creator_info->first_name . ' ' . $task_creator_info->last_name;
        $creator_url		= get_the_permalink(109) . '/?id=' . $task_creator_info->ID;
        $task_url			= get_the_permalink($task_id);
        $task_title			= get_the_title($task_id);
        $message_title		= sprintf( __('Task Complete: ' . $task_title, 'wproject'));
        $message_body		= sprintf( __('<a href="%1$s" style="color:#00bcd4">%2$s</a> has completed a task: <a href="%3$s" style="color:#00bcd4">%4$s</a>.', 'wproject'),$creator_url, $creator_name, $task_url, $task_title);

        /* Email notification vars */
        $subject 			= sprintf( __('Task Complete: ' . $task_title, 'wproject'));
        $sender             = get_option( 'admin_email' );
        $recipient          = $pm_data->user_email; // Make this the PM email address
        $link               = $task_url;
        $button_label       = __('View Task', 'wproject');
        $project_name       = sanitize_text_field($params['project_name']);
        $project_url        = sanitize_text_field($params['project_url']);

        $user_photo         = get_the_author_meta( 'user_photo', $task_creator_info->ID );

        if($user_photo) {
            $avatar         = $user_photo;
            $avatar_id      = attachment_url_to_postid($avatar);
            $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
            $avatar         = $small_avatar[0];
        } else {
            $avatar 		= get_template_directory_uri() . '/images/default-user.png';
        }
        include( get_stylesheet_directory() . '/inc/email-template.php' );
    }

    ajaxStatus('success-update-subtask-list', __('Subtask list updated.', 'wproject'), $task_id);
}


/* Update task status */
add_action( 'wp_ajax_update_task_status', 'update_task_status' );
add_action( 'wp_ajax_nopriv_update_task_status', 'update_task_status' );

function update_task_status() {

	$params = array();
    parse_str($_POST["post"], $params);

    $wproject_settings              = wProject();
    $notify_pm_when_task_complete   = $wproject_settings['notify_pm_when_task_complete'];
	$task_id			            = sanitize_text_field($params['task_id']);
	$task_status		            = sanitize_text_field($params['task_status']);
    $pm_user_id		                = sanitize_text_field($params['pm_user_id']);
    $pm_data                        = get_userdata( $pm_user_id );
	$task_owner_id		            = get_post_field( 'post_author', $task_id );
	$task_wip		                = get_user_meta( $task_owner_id, 'task_wip', true );
	
	global $wpdb;
	$tablename = $wpdb->prefix.'time';

	update_post_meta( $task_id, "task_status" , $task_status);

	/* Trash the post if deleted */
	if($task_status == 'delete') {
		$edit_task_args = array(
			'ID'    		=> $task_id,
			'post_status'   => 'trash',
		);
		$result = wp_update_post( $edit_task_args );

		/* Delete user meta if this task is currently recording time. */
		if($task_id == $task_wip) {
			delete_user_meta( $task_owner_id, 'task_wip');
		}

		delete_post_meta( $task_id, 'task_timer');
		delete_post_meta( $task_id, 'task_start_time');
		delete_post_meta( $task_id, 'task_end_time');

		/* Remove the time fom the project_total_time. */
		$categories = get_the_terms( $task_id, 'project' );
		foreach( $categories as $category ) { 
		}
		$project_id         = $category->term_id;
		$project_total_time	= get_term_meta($project_id, 'project_total_time', TRUE); 

		$query = "
			SELECT * 
			FROM $tablename
		";
		$result = $wpdb->get_results($query);
		$sum = 0;
		foreach ($result as $data) {
			if($data->task_id == $task_id ) {
				$sum+= $data->time_log;
			}
		} 

		/* Remove the time that is being deleted from the project_total_time */
		if($project_total_time) {
			$combined_time = ($project_total_time - $sum);
			update_term_meta( $project_id, 'project_total_time' , $combined_time );
		}
		$wpdb->delete( $tablename, array( 'task_id' => $task_id ) );

		ajaxStatus('success-update-task-status', __('Task deleted.', 'wproject'), $task_id);

	} else if($task_status == 'complete') {

        /* Task is complete, notify logic */
        if($notify_pm_when_task_complete == 'on') {

            /* Prepare email notification */
            $task_creator_info	= get_userdata($task_owner_id);
            $creator_name		= $task_creator_info->first_name . ' ' . $task_creator_info->last_name;
            $creator_url		= get_the_permalink(109) . '/?id=' . $task_creator_info->ID;
            $task_url			= get_the_permalink($task_id);
            $task_title			= get_the_title($task_id);
            $message_title		= sprintf( __('Task Complete: ' . $task_title, 'wproject'));
            $message_body		= sprintf( __('<a href="%1$s" style="color:#00bcd4">%2$s</a> has completed a task: <a href="%3$s" style="color:#00bcd4">%4$s</a>.', 'wproject'),$creator_url, $creator_name, $task_url, $task_title);

            /* Email notification vars */
            $subject 			= sprintf( __('Task Complete: ' . $task_title, 'wproject'));
            $sender             = get_option( 'admin_email' );
            $recipient          = $pm_data->user_email; // Make this the PM email address
            $link               = $task_url;
            $button_label       = __('View Task', 'wproject');
            $project_name       = sanitize_text_field($params['project_name']);
            $project_url        = sanitize_text_field($params['project_url']);

            $user_photo         = get_the_author_meta( 'user_photo', $task_creator_info->ID );

            if($user_photo) {
                $avatar         = $user_photo;
                $avatar_id      = attachment_url_to_postid($avatar);
                $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                $avatar         = $small_avatar[0];
            } else {
                $avatar 		= get_template_directory_uri() . '/images/default-user.png';
            }
            include( get_stylesheet_directory() . '/inc/email-template.php' );
        }

        /* Front-end notification */
        $user 			= get_user_by('ID', $task_owner_id);
        $task_owner	    = $user->first_name . ' ' . $user->last_name;
        $message_title	= sprintf( __('Task Complete', 'wproject'));
        $task_url	    = get_the_permalink($task_id);
        $message_body	= sprintf( __('Task <a href="%1$s" style="color:#00bcd4">%2$s</a> was completed by %3$s.', 'wproject'),$task_url, get_the_title($task_id), $task_owner);

        wp_insert_post(array (
            'post_type' 		=> 'message',
            'post_title' 		=> $message_title,
            'post_content' 		=> $message_body,
            'post_status' 		=> 'publish',
            'post_author'	    => $pm_user_id,
            'comment_status'	=> 'closed',
            'ping_status' 		=> 'closed'
        ));

		update_post_meta( $task_id, "task_pc_complete" , 100); /* If task is complete, progress should also be 100% */
		ajaxStatus('success-update-task-status', __('Task complete.', 'wproject'), $task_id);
        
	} else if($task_status == 'incomplete' || $task_status == 'in-progress' || $task_status == 'not-started' || $task_status == 'on-hold') {
		update_post_meta( $task_id, "task_pc_complete" , 0); /* Reset task progress to 0 */
		ajaxStatus('success-update-task-status', __('Task status updated.', 'wproject'), $task_id);
	} else {
		ajaxStatus('success-update-task-status', __('Task status updated.', 'wproject'), $task_id);
	}
}


/* Delete project */
add_action( 'wp_ajax_delete_project', 'delete_project' );
add_action( 'wp_ajax_nopriv_delete_project', 'delete_project' );

function delete_project() {

	$params = array();
    parse_str($_POST["post"], $params);

	$project_id = sanitize_text_field($params['project_id']);

	/* Query tasks and delete them */
	$args = array(
		'post_type' => 'task',
        'posts_per_page'    => -1,
		'tax_query' => array(
			array(
				'taxonomy'	=> 'project',
				'field' 	=> 'id',
				'terms' 	=> $project_id,
                'operator'  => 'IN'
			)
		)
	);
	$posts = get_posts($args);
	foreach ( $posts as $post ){
		wp_delete_post( $post->ID, true );
	}

	/* Delete project by ID */
	wp_delete_term($project_id, 'project');

	ajaxStatus('success-project-deleted', __('Project deleted.', 'wproject'), $project_id);
}

/* Edit or Delete time */
add_action( 'wp_ajax_delete_time', 'delete_time' );
add_action( 'wp_ajax_nopriv_delete_time', 'delete_time' );

function delete_time() {

	$params = array();
    parse_str($_POST["post"], $params);

	$time_id				= sanitize_text_field($params['time-id']);			/* Time log table ID */
	$project_id				= sanitize_text_field($params['project-id']);
	$time_in_seconds		= sanitize_text_field($params['time-in-seconds']);			/* Time log table ID */
	$task_id				= sanitize_text_field($params['task-id']);			/* Time log table ID */
	$edit_time_hrs			= sanitize_text_field($params['edit-time-hours']);	/* Entered hrs */
	$edit_time_mins			= sanitize_text_field($params['edit-time-mins']);	/* Entered minutes */
	$edit_time				= sanitize_text_field($params['edit-time']);		/* Used to decide if editing time */

	$project_total_time		= get_term_meta($project_id, 'project_total_time', TRUE); 

	$task_time_hrs_in_secs	= intval($edit_time_hrs) * 3600;								/* Convert current hrs logged to seconds */
	$task_time_mins_in_secs	= intval($edit_time_mins) * 60;								/* Convert current mins logged to seconds */
	$total_time_in_secs		= intval($task_time_hrs_in_secs) + intval($task_time_mins_in_secs);	/* Convert mins and hrs to seconds */

	global $wpdb;
	$tablename = $wpdb->prefix.'time';

	/* If editing a time entry */
	if($edit_time == 'yes' && $project_total_time || $edit_time == 'yes' && $project_total_time == 0) {

		/* Update the time log entry */
		$wpdb->update(
			$tablename,
			array( 
				'time_log' => $total_time_in_secs /* Update the time_log column with this value... */
			),
			array(
				'id' => $time_id /* ...where the id is $time_id */
			)
		);

		/* Sum of all time logged for all tasks in the project */
		$query = "
			SELECT * 
			FROM $tablename
		";
		$result = $wpdb->get_results($query);
		$sum = 0;
		foreach ($result as $data) {

			if($data->project_id == $project_id ) {
				$sum+= $data->time_log;
			}
		}

		/* Update the project_total_time with the $sum */
		update_term_meta( $project_id, 'project_total_time' , $sum );

		ajaxStatus('success-time-edited', __('Time updated.', 'wproject'), $project_id);

	/* Otherwise, delete time entry */
	} else if($edit_time == '' && $project_total_time) {

		/* Update the project_total_time with the $sum */
		$combined_time = $project_total_time - $time_in_seconds;
		update_term_meta( $project_id, 'project_total_time' , $combined_time );
		$wpdb->delete( $tablename, array( 'id' => $time_id ) );
		ajaxStatus('success-time-deleted', __('Time deleted.', 'wproject'), $project_id);

	} else {
		ajaxStatus('success-time-edit-failed', __('Something went wrong.', 'wproject'), $project_id);
	}
}


/* Add Missed Time */
add_action( 'wp_ajax_add_missed_time', 'add_missed_time' );
add_action( 'wp_ajax_nopriv_add_missed_time', 'add_missed_time' );

function add_missed_time() {

    $params = array();
    parse_str($_POST["post"], $params);

	$user_id			= sanitize_text_field($params['user_id']);
	$task_id			= sanitize_text_field($params['task_id']);
	$project_id			= sanitize_text_field($params['project_id']);
	$missed_hrs         = sanitize_text_field($params['missed_hrs']);
	$missed_mins        = sanitize_text_field($params['missed_mins']);
    $missed_date        = sanitize_text_field($params['missed_date']);
	$project_total_time	= get_term_meta($project_id, 'project_total_time', TRUE); 

	/* Time logged */
    $total_time_in_mins = ($missed_hrs * 60) + $missed_mins;

	/* Prevent recorded time being over 24hrs */
	if($total_time_in_mins < 86400) {
		$total_time_in_mins = $total_time_in_mins * 60;
	} else {
		$total_time_in_mins = 86400 * 60;
	}

	/* Add the logged time to the DB */
	global $wpdb;
	$tablename = $wpdb->prefix.'time';

	$wpdb->insert($tablename, array(
			'task_id'   	=> $task_id, 
			'project_id'	=> $project_id, 
			'time_log'  	=> $total_time_in_mins,
			'user_id'   	=> $user_id, 
			'date'      	=> date($missed_date . ' H:i:s')
		),
		array( '%s', '%s', '%s', '%s' ) 
	);

	/* Update project total time */
	if($project_total_time) {
		$combined_time = $project_total_time + $total_time_in_mins;
		update_term_meta( $project_id, 'project_total_time' , $combined_time );
	} else {
		add_term_meta( $project_id, 'project_total_time' , $total_time_in_mins );
	}

	ajaxStatus('success-missed-time-added', __('Time was added.', 'wproject'), $task_id);
}



/* Delete task */
add_action( 'wp_ajax_delete_task', 'delete_task' );
add_action( 'wp_ajax_nopriv_delete_task', 'delete_task' );

function delete_task() {

	$params = array();
    parse_str($_POST["post"], $params);

	$task_id 			= sanitize_text_field($params['task_id']);
	$task_owner_id		= get_post_field( 'post_author', $task_id );
	$task_wip		    = get_user_meta( $task_owner_id, 'task_wip', true );

	global $wpdb;
	$tablename = $wpdb->prefix.'time';

	/* Delete user meta if this task is currently recording time. */
	if($task_id == $task_wip) {
		delete_user_meta( $task_owner_id, 'task_wip');
	}

	$query = "
		SELECT * 
		FROM $tablename
	";
	$result = $wpdb->get_results($query);
	$sum = 0;
	foreach ($result as $data) {
		if($data->task_id == $task_id ) {
			$sum+= $data->time_log;
		}
	} 

	/* Remove the time that is being deleted from the project_total_time */
	if($project_total_time) {
		$combined_time = ($project_total_time - $sum);
		update_term_meta( $project_id, 'project_total_time' , $combined_time );
	}
	$wpdb->delete( $tablename, array( 'task_id' => $task_id ) );

	/* Delete task. */
	wp_delete_post($task_id);
	delete_post_meta( $task_id, 'task_timer');
	delete_post_meta( $task_id, 'task_start_time');
	delete_post_meta( $task_id, 'task_end_time');

	ajaxStatus('success-task-deleted', __('Task deleted.', 'wproject'), $task_id);
}

/* Claim Task */
add_action( 'wp_ajax_claim_task', 'claim_task' );
add_action( 'wp_ajax_nopriv_claim_task', 'claim_task' );

function claim_task() {

	$params = array();
    parse_str($_POST["post"], $params);

	$no_owner_task_id		= sanitize_text_field($params['no_owner_task_id']);
	$no_owner_task_author	= sanitize_text_field($params['no_owner_task_author']);
	$no_owner_task_name		= sanitize_text_field($params['no_owner_task_name']);
	
	$update_post = array(
		'ID'           	    => $no_owner_task_id,
		'post_author'	    => $no_owner_task_author,
        'comment_status'    => 'open'
	);
	wp_update_post( $update_post );

	ajaxStatus('success-claim-task', __('Task claimed' . ': ', 'wproject'), $no_owner_task_name);
}


/* Request Task Takeover  */
add_action( 'wp_ajax_request_task_takeover', 'request_task_takeover' );
add_action( 'wp_ajax_nopriv_request_task_takeover', 'request_task_takeover' );

function request_task_takeover() {

	$params = array();
    parse_str($_POST["post"], $params);

	$task_id 			= sanitize_text_field($params['task_id']);
	$task_owner_id		= sanitize_text_field($params['task_owner_id']);
	$current_user_id	= sanitize_text_field($params['current_user_id']);
	$task_title			= get_the_title($task_id);
	$link				= get_the_permalink($task_id);
	$button_label		= __('Make a Decision', 'wproject');
    $project_name       = sanitize_text_field($params['project_name']);
    $project_url        = sanitize_text_field($params['project_url']);
	$requester_id		= get_user_by('ID', $current_user_id);
	$sender_name		= $requester_id->first_name . ' ' . $requester_id->last_name;
	$sender				= $requester_id->user_email;
	$user_photo         = get_the_author_meta( 'user_photo', $current_user_id );

	if($user_photo) {
		$avatar         = $user_photo;
		$avatar_id      = attachment_url_to_postid($avatar);
		$small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
		$avatar         = $small_avatar[0];
	} else {
		$avatar 		= get_template_directory_uri() . '/images/default-user.png';
	}

	/* Send task takeover request email notification. */
	$message_title		= sprintf( __('Transfer of ownership request', 'wproject'));
	$message_body		= sprintf( __('%1$s has requested ownership of your task: <a href="%2$s" style="color:#00bcd4">%3$s</a>.', 'wproject'), $sender_name, $link, $task_title);
	$subject 			= __('Transfer of ownership request', 'wproject');
	$current_owner_id	= get_user_by('ID', $task_owner_id);
	$task_owner_name	= $current_owner_id->first_name . ' ' . $current_owner_id->last_name;
	$recipient	= $current_owner_id->user_email;

	$wproject_settings = wProject();
	if($wproject_settings['notify_when_task_takeover']) {
		include( get_stylesheet_directory() . '/inc/email-template.php' );
	}
	
	update_post_meta( $task_id, "task_takeover_request", $current_user_id);

	/* Create message / notification. */
	wp_insert_post(array (
		'post_type' 		=> 'message',
		'post_author'		=> $task_owner_id,
		'post_title' 		=> $message_title,
		'post_content' 		=> $message_body,
		'post_status' 		=> 'publish',
		'comment_status'	=> 'closed',
		'ping_status' 		=> 'closed'
	));

	ajaxStatus('success-request-task-ownership', __('Ownership request sent.', 'wproject'), $task_id);
}

/* Task Takeover Decision  */
add_action( 'wp_ajax_transfer_task_ownership', 'transfer_task_ownership' );
add_action( 'wp_ajax_nopriv_transfer_task_ownership', 'transfer_task_ownership' );

function transfer_task_ownership() {

	$params = array();
    parse_str($_POST["post"], $params);

	$task_id 				= sanitize_text_field($params['task_id']);
	$requester_id			= sanitize_text_field($params['requester_id']);
	$requester_user_info 	= get_userdata($requester_id);
	$task_owner_id			= sanitize_text_field($params['task_owner_id']);
	$task_takeover_choice	= sanitize_text_field($params['task_takeover_choice']);
	$current_owner_id		= get_user_by('ID', $task_owner_id);
	$task_owner_name		= $current_owner_id->first_name . ' ' . $current_owner_id->last_name;
	$sender_name			= $requester_id->first_name . ' ' . $requester_id->last_name;
	$task_takeover_user_id	= get_post_meta($task_id, 'task_takeover_request', TRUE);
	$recipient				= $requester_user_info->user_email;
	$sender					= $current_owner_id->user_email;
	$task_url				= get_the_permalink($task_id);
	$task_title				= get_the_title($task_id);
	$link					= get_the_permalink($task_id);
	$button_label			= __('Go to task', 'wproject');
    $project_name           = sanitize_text_field($params['project_name']);
    $project_url            = sanitize_text_field($params['project_url']);

	$user_photo         	= get_the_author_meta( 'user_photo', $task_owner_id );
    
	if($user_photo) {
		$avatar         	= $user_photo;
		$avatar_id      	= attachment_url_to_postid($avatar);
		$small_avatar   	= wp_get_attachment_image_src($avatar_id, 'thumbnail');
		$avatar         	= $small_avatar[0];
	} else {
		$avatar 			= get_template_directory_uri() . '/images/default-user.png';
	}

	/* Takeover request was approved */
	if($task_takeover_choice == 'approve') {

		$the_requester 	= $requester_id;
		$the_approval 	= /* translators: Example: The request was approved */ __('approved', 'wproject');

		$update_post = array(
			'ID'           	=> $task_id,
			'post_author'	=> $requester_id,
		);
		wp_update_post( $update_post );

	/* Takeover request was declined */
	} else if($task_takeover_choice == 'decline') {

		$the_requester = $requester_id;
		$the_approval 	= /* translators: Example: the request was declined */ __('declined', 'wproject');

	}

	//print_r($task_takeover_choice);

	/* Send task takeover request email notification. */
	$message_title	= sprintf( /* translators: %1$s will be either 'approved' or 'declined'. Eg: Task takeover approved  */ __('Task takeover %1$s', 'wproject'), $the_approval);
	$message_body	= sprintf( /* translators: %1$s is a user name, %2$s is the status of the task takeover. No need to touch %3$s and %4$s. Eg: Michael has approved your takeover of the task */ __('%1$s has %2$s your takeover of the task: <a href="%3$s" style="color:#00bcd4">%4$s</a>.', 'wproject'),$task_owner_name, $the_approval, $task_url, $task_title);
	$subject		= sprintf( __('Task takeover request %1$s', 'wproject'),$the_approval);


	$wproject_settings = wProject();
	if($wproject_settings['notify_when_task_takeover_decided']) {
		include( get_stylesheet_directory() . '/inc/email-template.php' );
	}

	/* Create message / notification. */
	wp_insert_post(array (
		'post_type' 		=> 'message',
		'post_author'		=> $the_requester,
		'post_title' 		=> $message_title,
		'post_content' 		=> $message_body,
		'post_status' 		=> 'publish',
		'comment_status'	=> 'closed',
		'ping_status' 		=> 'closed'
	));		

	update_post_meta( $task_id, "task_takeover_request", '');

	if($task_takeover_choice == 'approve') {
		ajaxStatus('task-takeover-decision', __('Takeover granted.', 'wproject'), $task_id);
	} else if($task_takeover_choice == 'decline') {
		ajaxStatus('task-takeover-decision', __('Takeover declined.', 'wproject'), $task_id);
	}
}



/* Mark message as read */
add_action( 'wp_ajax_mark_message_read', 'mark_message_read' );
add_action( 'wp_ajax_nopriv_mark_message_read', 'mark_message_read' );

function mark_message_read() {

	$params = array();
    parse_str($_POST["post"], $params);

	$message_id	= sanitize_text_field($params['message_id']);
		
	/* Change the message status to draft (so it's not visible or counted on front-end) */
	$message_args = array(
		'ID'    		=> $message_id,
		'post_status'   => 'trash',
		'post_type' 	=> 'message'
	);
	$result		= wp_update_post( $message_args );
	$message_id	= $result;
	
	ajaxStatus('success-message-read', __('Message read.', 'wproject'), $message_id);
}


/* Mark ALL message as read */
add_action( 'wp_ajax_read_all_messages', 'read_all_messages' );
add_action( 'wp_ajax_nopriv_read_all_messages', 'read_all_messages' );

function read_all_messages() {

	/* Query messages and change post status */
	$message_args = array(
		 'post_type'		=> 'message',
         'posts_per_page'	=> -1,
		 'author'			=> get_current_user_id()
    );
    $published_posts = get_posts($message_args);

    foreach($published_posts as $post_to_draft) {
		$query = array(
			'ID' 			=> $post_to_draft->ID,
			'post_status' 	=> 'trash',
			'author'		=> get_current_user_id()
		);
		wp_update_post( $query, true );  
    }
	ajaxStatus('success-messages-read', __('All message marked as read.', 'wproject'), '');
}


/* Arrange Kanban */
add_action( 'wp_ajax_kanban_arranged', 'kanban_arranged' );
add_action( 'wp_ajax_nopriv_kanban_arranged', 'kanban_arranged' );

function kanban_arranged() {

	$params = array();
    parse_str($_POST["post"], $params);

	$kanban_task_id					= sanitize_text_field($params['kanban_task_id']);
	$kanban_column_task_status		= sanitize_text_field($params['kanban_column_task_status']);
	$kanban_previous_pc_complete	= sanitize_text_field($params['kanban_previous_pc_complete']);
    $pm_user_id                     = sanitize_text_field($params['pm_user_id']);
    $pm_data                        = get_userdata( $pm_user_id );

	if($kanban_column_task_status == 'complete') {

        $wproject_settings              = wProject();
        $notify_pm_when_task_complete   = $wproject_settings['notify_pm_when_task_complete'];

        /* Task is complete, notify logic */
        if($notify_pm_when_task_complete == 'on') {

            /* Prepare email notification */
            $task_owner_id      = get_post_field( 'post_author', $kanban_task_id );
            $task_creator_info	= get_userdata($task_owner_id);
            $creator_name		= $task_creator_info->first_name . ' ' . $task_creator_info->last_name;
            $creator_url		= get_the_permalink(109) . '/?id=' . $task_creator_info->ID;
            $task_url			= get_the_permalink($kanban_task_id);
            $task_title			= get_the_title($kanban_task_id);
            $message_title		= sprintf( __('Task Complete: ' . $task_title, 'wproject'));
            $message_body		= sprintf( __('<a href="%1$s" style="color:#00bcd4">%2$s</a> has completed a task: <a href="%3$s" style="color:#00bcd4">%4$s</a>.', 'wproject'),$creator_url, $creator_name, $task_url, $task_title);

            /* Email notification vars */
            $subject 			= sprintf( __('Task Complete: ' . $task_title, 'wproject'));
            $sender             = get_option( 'admin_email' );
            $recipient          = $pm_data->user_email; // Make this the PM email address
            $link               = $task_url;
            $button_label       = __('View Task', 'wproject');
            $project_name       = sanitize_text_field($params['project_name']);
            $project_url        = sanitize_text_field($params['project_url']);

            $user_photo         = get_the_author_meta( 'user_photo', $task_creator_info->ID );

            if($user_photo) {
                $avatar         = $user_photo;
                $avatar_id      = attachment_url_to_postid($avatar);
                $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                $avatar         = $small_avatar[0];
            } else {
                $avatar 		= get_template_directory_uri() . '/images/default-user.png';
            }
            include( get_stylesheet_directory() . '/inc/email-template.php' );

            /* Front-end notification */
            $user 			= get_user_by('ID', $task_owner_id);
            $task_owner	    = $user->first_name . ' ' . $user->last_name;
            $message_title	= sprintf( __('Task Complete', 'wproject'));
            $task_url	    = get_the_permalink($kanban_task_id);
            $message_body	= sprintf( __('Task <a href="%1$s" style="color:#00bcd4">%2$s</a> was completed by %3$s.', 'wproject'),$task_url, get_the_title($kanban_task_id), $task_owner);

            wp_insert_post(array (
                'post_type' 		=> 'message',
                'post_title' 		=> $message_title,
                'post_content' 		=> $message_body,
                'post_status' 		=> 'publish',
                'comment_status'	=> 'closed',
                'post_author'	    => $pm_user_id,
                'ping_status' 		=> 'closed'
            ));
        }

		update_post_meta( $kanban_task_id, 'task_pc_complete', 100); 
	} else {
		update_post_meta( $kanban_task_id, 'task_pc_complete', $kanban_previous_pc_complete); 
	}

	// TODO: Dragged task order
	// $kanban_task_order_args = array(
	// 	'ID'    		=> $kanban_task_id,
	// 	'menu_order'	=> $kanban_task_order_id,
	// );
	// wp_update_post( $kanban_task_order_args );

	update_post_meta( $kanban_task_id, 'task_status', $kanban_column_task_status); 
	
	ajaxStatus('success-kanban-arranged', __('Task moved', 'wproject'), $kanban_task_id);
}


/* 
    Follow or Unfollow task.
    This applies to the single task sidebar form and the top icons form.

*/
add_action( 'wp_ajax_task_follow_status', 'task_follow_status' );
add_action( 'wp_ajax_nopriv_task_follow_status', 'task_follow_status' );

function task_follow_status() {

	$params = array();
    parse_str($_POST["post"], $params);

	/* Fav or unfav choice */
	$follow_status  = sanitize_text_field($params['follow-status']);
	/* Get the task ID */           
	$task_id        = sanitize_text_field($params['task-id']);
	/* Get the current user's favs */                
	$fav_tasks      = get_user_meta( get_current_user_id(), 'fav_tasks' , TRUE );
	/* Add the new task ID to the existing faved tasks  */ 
	$combined       = $fav_tasks  . $task_id . ',';

	
	/* If fav_tasks doesn't contain a comma, then there must only be one item followed */
	if (strpos($fav_tasks, ',') !== false) {
		$pre_remove = str_replace($task_id, '', $fav_tasks);
		$removed    = str_replace(',,', ',', $pre_remove);
	} else {
		$removed = ''; 
	}
	
	if($follow_status == 'followed') {

		update_user_meta( get_current_user_id(), 'fav_tasks', $combined);
		ajaxStatus('success-followed-task', __('Following task.', 'wproject'), $task_id);

	} else {
		
		update_user_meta( get_current_user_id(), 'fav_tasks', $removed);
		ajaxStatus('success-unfollowed-task', __('Not following task.', 'wproject'), $task_id);

	}
}


/* Change task status on task solo page */
add_action( 'wp_ajax_change_task_status', 'change_task_status' );
add_action( 'wp_ajax_nopriv_change_task_status', 'change_task_status' );

function change_task_status() {

	$params = array();
    parse_str($_POST["post"], $params);

    $wproject_settings = wProject();
    $enable_time                    = $wproject_settings['enable_time'];
    $notify_pm_when_task_complete   = $wproject_settings['notify_pm_when_task_complete'];
    $task_wip 		                = get_user_meta( get_current_user_id(), 'task_wip' , TRUE );
	$task_id			            = sanitize_text_field($params['task_id']);
	$task_status		            = sanitize_text_field($params['task_status']);
    $pm_email		                = sanitize_text_field($params['pm_email']);
    $pm_user_id                     = sanitize_text_field($params['pm_user_id']);
    $pm_data                        = get_userdata( $pm_user_id );
    $task_owner_id                  = sanitize_text_field($params['task_owner_id']);

	if($task_status == 'deleted') {
		delete_user_meta( get_current_user_id(), 'task_wip');
		wp_trash_post($task_id);
		global $wpdb;
		$tablename = $wpdb->prefix.'time';

		/* Remove the time fom the project_total_time. */
		$categories = get_the_terms( $task_id, 'project' );
		foreach( $categories as $category ) { 
		}
		$project_id         = $category->term_id;
		$project_total_time	= get_term_meta($project_id, 'project_total_time', TRUE); 

		$query = "
			SELECT * 
			FROM $tablename
		";
		$result = $wpdb->get_results($query);
		$sum = 0;
		foreach ($result as $data) {
			if($data->task_id == $task_id ) {
				$sum+= $data->time_log;
			}
		} 

		/* Remove the time that is being deleted from the project_total_time */
		if($project_total_time) {
			$combined_time = ($project_total_time - $sum);
			update_term_meta( $project_id, 'project_total_time' , $combined_time );
		}

		/* Delete time from time table */
		$wpdb->delete( $tablename, array( 'task_id' => $task_id ) );

		ajaxStatus('success-deleted-task', __('Task deleted.', 'wproject'), $task_id);
		
	} else if($task_status == 'complete') {
		update_post_meta( $task_id, 'task_pc_complete', 100); 
		update_post_meta( $task_id, 'task_status', $task_status); 
		update_post_meta( $task_id, 'task_pc_complete', '100'); 
			
		/* Task is complete, so perform timer stop duties */
		$now				= time();
		$task_start_time	= get_post_meta($task_id, 'task_start_time', TRUE);
		$task_stop_time		= get_post_meta($task_id, 'task_stop_time', TRUE);
		$task_timer			= get_post_meta($task_id, 'task_timer', TRUE);
		$time_diff			= intval($now) - intval($task_start_time);

		if($task_total_time) {
			update_post_meta( $task_id, 'task_stop_time', $now);
			$task_stop_time = get_post_meta($task_id, 'task_stop_time', TRUE);
			$new_time = intval($task_stop_time) + intval($task_total_time);
			$the_time = intval($new_time)- intval($task_start_time);
		} else {
			$the_time = $time_diff;
		}

		/* Delete proof that user is working on any task */
		delete_user_meta( get_current_user_id(), 'task_wip');

		/* Add the stop time */
		update_post_meta( $task_id, 'task_total_time', $the_time); /* Stored in the DB in seconds */
		update_post_meta( $task_id, 'task_timer', 'off');

        /* Task is complete, notify logic */
        if($notify_pm_when_task_complete == 'on') {

            /* Prepare email notification */
            $task_creator_info	= get_userdata($task_owner_id);
            $creator_name		= $task_creator_info->first_name . ' ' . $task_creator_info->last_name;
            $creator_url		= get_the_permalink(109) . '/?id=' . $task_creator_info->ID;
            $task_url			= get_the_permalink($task_id);
            $task_title			= get_the_title($task_id);
            $message_title		= sprintf( __('Task Complete: ' . $task_title, 'wproject'));
            $message_body		= sprintf( __('<a href="%1$s" style="color:#00bcd4">%2$s</a> has completed a task: <a href="%3$s" style="color:#00bcd4">%4$s</a>.', 'wproject'),$creator_url, $creator_name, $task_url, $task_title);

            /* Email notification vars */
            $subject 			= sprintf( __('Task Complete: ' . $task_title, 'wproject'));
            $sender             = get_option( 'admin_email' );
            $recipient          = $pm_data->user_email; // Make this the PM email address
            $link               = $task_url;
            $button_label       = __('View Task', 'wproject');
            $project_name       = sanitize_text_field($params['project_name']);
            $project_url        = sanitize_text_field($params['project_url']);

            $user_photo         = get_the_author_meta( 'user_photo', $task_creator_info->ID );

            if($user_photo) {
                $avatar         = $user_photo;
                $avatar_id      = attachment_url_to_postid($avatar);
                $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                $avatar         = $small_avatar[0];
            } else {
                $avatar 		= get_template_directory_uri() . '/images/default-user.png';
            }
            include( get_stylesheet_directory() . '/inc/email-template.php' );
        }

        /* Front-end notification */
        $user 			= get_user_by('ID', $task_owner_id);
        $task_owner	    = $user->first_name . ' ' . $user->last_name;
        $message_title	= sprintf( __('Task Complete', 'wproject'));
        $task_url	    = get_the_permalink($task_id);
        $message_body	= sprintf( __('Task <a href="%1$s">%2$s</a> was completed by %3$s.', 'wproject'),$task_url, get_the_title($task_id), $task_owner);

        wp_insert_post(array (
            'post_type' 		=> 'message',
            'post_title' 		=> $message_title,
            'post_content' 		=> $message_body,
            'post_status' 		=> 'publish',
            'comment_status'	=> 'closed',
            'post_author'	    => $pm_user_id,
            'ping_status' 		=> 'closed'
        ));

        if($enable_time == 'on' && !$task_wip) {
		    ajaxStatus('success-task-status-complete', __('Task Complete.', 'wproject'), $task_id);
        } else if($enable_time == 'on' && $task_wip) {
            ajaxStatus('success-task-status-complete-time-enabled-wip', __('Task complete, stopped recording time.', 'wproject'), $task_id);
        } else {
            ajaxStatus('success-task-status-complete', __('Task Complete.', 'wproject'), $task_id);
        }
		
	} else {
		update_post_meta( $task_id, 'task_status', $task_status); 
		update_post_meta( $task_id, 'task_pc_complete', ''); 
		ajaxStatus('success-change-task-status', __('Task status changed.', 'wproject'), $task_id);
	}
}

/* Start timer */
add_action( 'wp_ajax_start_timer', 'start_timer' );
add_action( 'wp_ajax_nopriv_start_timer', 'start_timer' );

function start_timer() {

	$params = array();
    parse_str($_POST["post"], $params);

	$user_id			= sanitize_text_field($params['user_id']);
	$task_id			= sanitize_text_field($params['task_id']);		
	$task_wip 		    = get_user_meta( $user_id, 'task_wip' , TRUE );
	
	/* If task is already in progress */
	if($task_wip) {
		ajaxStatus('failed-timer-started', __('You can only record time on one task at a time.', 'wproject'), $task_id);
	} else {
		update_post_meta( $task_id, 'task_timer', 'on');
		update_post_meta( $task_id, 'task_start_time', time());
		update_post_meta( $task_id, 'task_wip', $user_id);

		/* Add proof that user is working on the task */
		update_user_meta( $user_id, 'task_wip', $task_id); 
		
		ajaxStatus('success-timer-started', __('Recording time on this task.', 'wproject'), $task_id);
	}
}


/* Stop timer */
add_action( 'wp_ajax_stop_timer', 'stop_timer' );
add_action( 'wp_ajax_nopriv_stop_timer', 'stop_timer' );

function stop_timer() {

	$params = array();
    parse_str($_POST["post"], $params);

	$user_id			= sanitize_text_field($params['user_id']);
	$task_id			= sanitize_text_field($params['task_id']);
	$project_id			= sanitize_text_field($params['project_id']);
	$task_wip 		    = get_user_meta($user_id, 'task_wip' , TRUE );
	$task_start_time    = get_post_meta($task_id, 'task_start_time', TRUE);
	$project_total_time	= get_term_meta($project_id, 'project_total_time', TRUE); 
	
	update_post_meta( $task_id, 'task_timer', 'off');
	update_post_meta( $task_id, 'task_stop_time', time());

	$task_stop_time     = get_post_meta($task_id, 'task_stop_time', TRUE); /* This MUST be after the update_post_meta statements*/

	/* Time logged */
	$time_logged = $task_stop_time - $task_start_time;

	/* Prevent recorded time being over 24hrs */
	if($time_logged < 86400) {
		$time_logged = $time_logged;
	} else {
		$time_logged = 86400;
	}

	/* Delete proof that user is working on the task */
	delete_user_meta( $user_id, 'task_wip');

	/* 
		Log the project time.
		If time already exists, then combine the time recorded just now with the time
		that is already in the database.

		Otherwise, assume this is the first time being recorded fot this project 
		and just log the recorded time only.
	*/
	if($project_total_time) {
		$combined_time = $project_total_time + $time_logged;
		update_term_meta( $project_id, 'project_total_time' , $combined_time );
	} else {
		add_term_meta( $project_id, 'project_total_time' , $time_logged );
	}

	/* Delete task start and stop time (we don't need them now) */
	delete_post_meta( $task_id, 'task_start_time');
	delete_post_meta( $task_id, 'task_stop_time');
	delete_post_meta( $task_id, 'task_wip');

	/* Add the logged time to the DB */
	global $wpdb;
	$tablename = $wpdb->prefix.'time';

	$wpdb->insert($tablename, array(
			'task_id'   	=> $task_id, 
			'project_id'	=> $project_id, 
			'time_log'  	=> $time_logged,
			'user_id'   	=> $user_id, 
			'date'      	=> date('Y-m-d H:i:s')
		),
		array( '%s', '%s', '%s', '%s' ) 
	);

	ajaxStatus('success-timer-stopped', __('Stopped recording time.', 'wproject'), $task_id);
}


/* Complete Onboarding */
add_action( 'wp_ajax_complete_onboarding', 'complete_onboarding' );
add_action( 'wp_ajax_nopriv_complete_onboarding', 'complete_onboarding' );

function complete_onboarding() {

	$params = array();
    parse_str($_POST["post"], $params);

	$user_id = sanitize_text_field($params['user_id']);
	update_user_meta( get_current_user_id(), 'onboarding', 'complete');
	ajaxStatus('complete-onboarding', __('...loading...', 'wproject'), $user_id);
}