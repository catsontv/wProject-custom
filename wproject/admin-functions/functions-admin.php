<?php if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Register settings */
function wproject_settings_init() {
	register_setting( 
		'wproject_settings', 
		'wproject_settings', 
		'wproject_settings_validate'
	);
}
add_action( 'admin_init', 'wproject_settings_init' );


/* Colour picker */
add_action( 'admin_enqueue_scripts', 'wproject_add_color_picker' );
function wproject_add_color_picker( $hook ) {
 
    if( is_admin() ) { 
     
        // Add the color picker css file       
        wp_enqueue_style( 'wp-color-picker' ); 
         
        // Include our custom jQuery file with WordPress Color Picker dependency
        wp_enqueue_script( 'color-script-handle', get_template_directory_uri() . '/js/min/color-script.min.js', array( 'wp-color-picker' ), false, true ); 
    }
}

/* Clean up the database */
function cleanup_DB() {
    global $wpdb;
    $tablename = $wpdb->prefix;

    /* Delete duplicate post meta */
    $cleanup = $wpdb->get_var("
        DELETE t1 FROM " . $tablename . "postmeta t1 
        INNER JOIN " . $tablename . "postmeta t2  
        WHERE  t1.meta_id < t2.meta_id 
        AND  t1.meta_key = t2.meta_key 
        AND t1.post_id=t2.post_id;
    ");

    /* Delete orphaned tasks */
    $args = array(
		'post_type'         => 'task',
        'post_status'       => array('publish', 'trash', 'draft'),
        'posts_per_page'    => -1,
		'tax_query'         => array(
			array(
				'taxonomy'	=> 'project',
				'field' 	=> 'id',
				'terms'     => get_terms( 'project', [ 'fields' => 'ids'  ] ),
                'operator'  => 'NOT IN'
			)
		)
	);
	$posts = get_posts($args);
	foreach ( $posts as $post ){
		wp_delete_post( $post->ID, true );
	}
}

/* Sanitisation */
function wproject_settings_validate( $input ) {
    $output = array();
    foreach ( $input as $key => $value ) {
        if ( isset( $input[$key] ) ) {
            if ( is_array( $input[$key] ) ) {
                $output[$key] = array_map( 'sanitize_text_field', $input[$key] );
            } else {
                $output[$key] = sanitize_text_field( $input[$key] );
            }
        }
    }
    return $output;
	wp_verify_nonce($_POST['wproject-noncecheck'], 'save-wproject-settings');
}


/* Constants required for updates and support */
define('WPROJECT_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('WPROJECT_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('SPECIAL_KEY', '563931a7aaebe3.67050558');
define('LICENSE_SERVER_URL', 'https://rocketapps.com.au');
define('ITEM_REFERENCE', 'wProject'); 


/* Add settings page to menu */
function add_settings_page() {

	$wproject_capability	= apply_filters( 'wproject_required_capabilities', 'manage_options' );

	$icon_url = get_template_directory_uri() . '/images/admin/wproject-logo.svg';
	add_menu_page( __( 'wProject','wproject'), __( 'wProject','wproject' ), 'manage_options', 'wproject-settings', 'wproject_settings_page' ,$icon_url, 31);
	add_submenu_page( 'wproject-settings', __( 'Licence', 'wproject' ), __( 'Licence', 'wproject' ), $wproject_capability, 'wproject-license', 'wproject_license_page' );
	do_action( 'wproject_menu_items', 'wproject-license', $wproject_capability );
}
add_action( 'admin_menu', 'add_settings_page' );

function wproject_license_page() {
	require_once('license.php');
}

function create_time_table() {
    global $wpdb;
    $charset_collate    = $wpdb->get_charset_collate();
    $table_name         = $wpdb->prefix . 'time'; 

    $sql = "CREATE TABLE $table_name (
    id          mediumint(9)    NOT NULL AUTO_INCREMENT,
    task_id     mediumint(6)    NOT NULL,
    project_id	mediumint(6)    NOT NULL,
    time_log    mediumint(9)    NOT NULL,
    user_id     mediumint(3)    NOT NULL,
    date        datetime        DEFAULT '0000-00-00 00:00:00' NOT NULL,
    PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

/* Start Project Settings */
function wproject_settings_page() { ?>

	<?php 

		wp_enqueue_media();

		$options 							= get_option( 'wproject_settings' );
		$wproject_objects					= isset($options['objects'] ) ? $options['objects'] : array();
		$reset								= isset($options['reset']) ? $options['reset'] : '';
		$cleanup_messages					= isset($options['cleanup_messages']) ? $options['cleanup_messages'] : '';
		$cleanup_all_messages				= isset($options['cleanup_all_messages']) ? $options['cleanup_all_messages'] : '';
        $cleanup_db					        = isset($options['cleanup_db']) ? $options['cleanup_db'] : '';

		/* Presentation options */
		$remove_pages_nav					= isset($options['remove_pages_nav']) ? $options['remove_pages_nav'] : '';
		$pages_label 						= isset($options['pages_label']) ? $options['pages_label'] : '';
		$branding_logo 						= isset($options['branding_logo']) ? $options['branding_logo'] : '';
		$favicon 							= isset($options['favicon']) ? $options['favicon'] : '';
		$show_task_id						= isset($options['show_task_id']) ? $options['show_task_id'] : '';
		$avatar_style						= isset($options['avatar_style']) ? $options['avatar_style'] : '';
        $system_busy_blur                   = isset($options['system_busy_blur']) ? $options['system_busy_blur'] : '';
        $system_busy_disable_ui             = isset($options['system_busy_disable_ui']) ? $options['system_busy_disable_ui'] : '';
        $force_avatar                       = isset($options['force_avatar']) ? $options['force_avatar'] : '';
		$task_spacing                       = isset($options['task_spacing']) ? $options['task_spacing'] : '';
        $pep_talk_percentage                = isset($options['pep_talk_percentage']) ? $options['pep_talk_percentage'] : '';
        $pep_talk_message                = isset($options['pep_talk_message']) ? $options['pep_talk_message'] : '';
        
		$project_list_style					= isset($options['project_list_style']) ? $options['project_list_style'] : '';
		
		/* Team privileges options */
		$users_can_assign_tasks				= isset($options['users_can_assign_tasks']) ? $options['users_can_assign_tasks'] : '';
		$users_can_task_takeover			= isset($options['users_can_task_takeover']) ? $options['users_can_task_takeover'] : '';
		$users_can_claim_task_ownership		= isset($options['users_can_claim_task_ownership']) ? $options['users_can_claim_task_ownership'] : '';
		$users_can_create_tasks				= isset($options['users_can_create_tasks']) ? $options['users_can_create_tasks'] : '';
		$allow_pm_admin_access				= isset($options['allow_pm_admin_access']) ? $options['allow_pm_admin_access'] : '';
        $project_access				        = isset($options['project_access']) ? $options['project_access'] : '';
		

		/* Gantt options */
		$gantt_show_dashboard				= isset($options['gantt_show_dashboard']) ? $options['gantt_show_dashboard'] : '';
		$gantt_show_project					= isset($options['gantt_show_project']) ? $options['gantt_show_project'] : '';
		$gantt_show_all_project_page		= isset($options['gantt_show_all_project_page']) ? $options['gantt_show_all_project_page'] : '';
		$gantt_scale_tasks					= isset($options['gantt_scale_tasks']) ? $options['gantt_scale_tasks'] : '';
		$gantt_scale_projects				= isset($options['gantt_scale_projects']) ? $options['gantt_scale_projects'] : '';
		$gantt_pagination					= isset($options['gantt_pagination']) ? $options['gantt_pagination'] : '';
		$gantt_hide_completed				= isset($options['gantt_hide_completed']) ? $options['gantt_hide_completed'] : '';
		$gantt_jump_to_today				= isset($options['gantt_jump_to_today']) ? $options['gantt_jump_to_today'] : '';
		$gantt_show_popup					= isset($options['gantt_show_popup']) ? $options['gantt_show_popup'] : '';
		$gantt_show_subtasks				= isset($options['gantt_show_subtasks']) ? $options['gantt_show_subtasks'] : '';
		$gantt_popup_position				= isset($options['gantt_popup_position']) ? $options['gantt_popup_position'] : '';

		/* Kanban options */
		$enable_kanban						= isset($options['enable_kanban']) ? $options['enable_kanban'] : '';
		$kanban_density						= isset($options['kanban_density']) ? $options['kanban_density'] : '';
		$kanban_card_colours				= isset($options['kanban_card_colours']) ? $options['kanban_card_colours'] : '';
        $kanban_card_descriptions           = isset($options['kanban_card_descriptions']) ? $options['kanban_card_descriptions'] : '';
		$kanban_unfocused_cards           	= isset($options['kanban_unfocused_cards']) ? $options['kanban_unfocused_cards'] : '';
		
		/* Time & Cost options */
		$enable_time						= isset($options['enable_time']) ? $options['enable_time'] : '';
        $overtime				            = isset($options['overtime']) ? $options['overtime'] : '';
		$currency_symbol					= isset($options['currency_symbol']) ? $options['currency_symbol'] : '';
		$currency_symbol_position			= isset($options['currency_symbol_position']) ? $options['currency_symbol_position'] : '';
		$default_project_rate				= isset($options['default_project_rate']) ? $options['default_project_rate'] : '';
		$logged_time_increments				= isset($options['logged_time_increments']) ? $options['logged_time_increments'] : '';

        /* Context labels */
        $context_labels						= isset($options['context_labels']) ? $options['context_labels'] : '';
		$context_label_colour				= isset($options['context_label_colour']) ? $options['context_label_colour'] : '';
		$context_label_display				= isset($options['context_label_display']) ? $options['context_label_display'] : '';

		/* Notification options */
		$notify_when_task_takeover			= isset($options['notify_when_task_takeover']) ? $options['notify_when_task_takeover'] : '';
		$notify_when_task_takeover_decided	= isset($options['notify_when_task_takeover_decided']) ? $options['notify_when_task_takeover_decided'] : '';
		$response_message_duration			= isset($options['response_message_duration']) ? $options['response_message_duration'] : '';
		$response_message_position			= isset($options['response_message_position']) ? $options['response_message_position'] : '';
		$notify_maximum_messages			= isset($options['notify_maximum_messages']) ? $options['notify_maximum_messages'] : '';
		$notify_when_task_created			= isset($options['notify_when_task_created']) ? $options['notify_when_task_created'] : '';
		$notify_when_comment_reply			= isset($options['notify_when_comment_reply']) ? $options['notify_when_comment_reply'] : '';
		$notify_all_when_project_created    = isset($options['notify_all_when_project_created']) ? $options['notify_all_when_project_created'] : '';
		$notify_when_project_complete		= isset($options['notify_when_project_complete']) ? $options['notify_when_project_complete'] : '';
		$notify_when_project_deleted		= isset($options['notify_when_project_deleted']) ? $options['notify_when_project_deleted'] : '';
		$notify_when_comment				= isset($options['notify_when_comment']) ? $options['notify_when_comment'] : '';
		$notify_pm_when_task_complete		= isset($options['notify_pm_when_task_complete']) ? $options['notify_pm_when_task_complete'] : '';
        $notify_pm_when_subtasks_complete   = isset($options['notify_pm_when_subtasks_complete']) ? $options['notify_pm_when_subtasks_complete'] : '';
        $sender_name		                = isset($options['sender_name']) ? $options['sender_name'] : '';
        $sender_email		                = isset($options['sender_email']) ? $options['sender_email'] : '';
        $relation_tasks                     = isset($options['relation_tasks']) ? $options['relation_tasks'] : '';
		$dashboard_message					= isset($options['dashboard_message']) ? $options['dashboard_message'] : '';
        $team_page					        = isset($options['team_page']) ? $options['team_page'] : '';
        $default_pm					        = isset($options['default_pm']) ? $options['default_pm'] : '';
        
		/* Comment options */
		$task_comments_enabled				= isset($options['task_comments_enabled']) ? $options['task_comments_enabled'] : '';
		$page_comments_enabled				= isset($options['page_comments_enabled']) ? $options['page_comments_enabled'] : '';
		$recent_comments_number				= isset($options['recent_comments_number']) ? $options['recent_comments_number'] : '';
        $comment_order				        = isset($options['comment_order']) ? $options['comment_order'] : '';
        $show_comment_dates                 = isset($options['show_comment_dates']) ? $options['show_comment_dates'] : '';

		/* Printing options */
		$print_hide_complete_tasks			= isset($options['print_hide_complete_tasks']) ? $options['print_hide_complete_tasks'] : '';
		
		/* Other options */
		$enable_leave_warning				= isset($options['enable_leave_warning']) ? $options['enable_leave_warning'] : '';
        $bypass_google				        = isset($options['bypass_google']) ? $options['bypass_google'] : '';
		$enable_subtask_descriptions		= isset($options['enable_subtask_descriptions']) ? $options['enable_subtask_descriptions'] : '';
		$job_number_prefix					= isset($options['job_number_prefix']) ? $options['job_number_prefix'] : '';
		$preferred_calendar					= isset($options['preferred_calendar']) ? $options['preferred_calendar'] : '';
		$print_hide_user_photos             = isset($options['print_hide_user_photos']) ? $options['prin_hide_user_photos'] : '';
        $print_hide_task_descriptions       = isset($options['print_hide_task_descriptions']) ? $options['print_hide_task_descriptions'] : '';
		$completed_projects_nav				= isset($options['completed_projects_nav']) ? $options['completed_projects_nav'] : '';
        $fade_on_hold				        = isset($options['fade_on_hold']) ? $options['fade_on_hold'] : '';
        $session_time				        = isset($options['session_time']) ? $options['session_time'] : '';
        $delete_projects_from_backend       = isset($options['delete_projects_from_backend']) ? $options['delete_projects_from_backend'] : '';

		if(get_option('wproject_key')) {
			$button 			= '<input name="submit" class="button" value="' . __( 'Save Settings', 'wproject' ) . '" type="submit" />';
		} else {
			$button = '<a href="' . admin_url() . 'admin.php?page=wproject-license" class="wproject-button warn">'. __( 'Activate License Key', 'wproject' ) .'</a>';
		}

		/* Get theme version */
		function wprojectThemeVersion() {
			$wproject_theme = wp_get_theme();
			$theme_version = esc_html( $wproject_theme->get( 'Version' ) );
			return $theme_version;
		}

        /* Get latest theme version from remove JSON */
        $json_path                          = file_get_contents('https://rocketapps.com.au/files/wproject/wproject/info.json');
        $json                               = json_decode($json_path, true);
        $remote_version                     = $json['version'];

        /* Create DB table for tracking time, if table doesn't exist */
        global $wpdb;
        $charset_collate    = $wpdb->get_charset_collate();
        $table_name         = $wpdb->prefix . 'time'; 

        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            create_time_table();
        } else {
            /* Do nothing */
        }

	?>

	<!--/ Start Settings Interface / -->
	<div class="wproject-wrap wrap">
		<?php /* Options saved */
		if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' && !$reset && !$cleanup_messages && !$cleanup_db && !$cleanup_all_messages) { ?>
			<div class="wproject-message notice-success">
				<p><strong><?php _e( 'Settings Saved', 'wproject' ); ?></strong></p>
			</div>
		<?php } ?>

        <?php /* Cleanup done */
		if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' && $cleanup_db) {
            cleanup_DB();
        ?>
			<div class="wproject-message notice-success">
				<p><strong><?php _e( 'Database maintenance complete', 'wproject' ); ?></strong></p>
			</div>
		<?php } ?>

		<?php /* Reset performed */
		if ($reset == '1') { 
			delete_option('wproject_settings');
			delete_site_option('wproject_settings');
		?>
			<div class="wproject-message notice-success">
				<p><strong><?php _e( 'All wProject settings were successfully deleted', 'wproject' ); ?></strong></p>
			</div>
		<?php } ?>

		<?php /* Messages cleaned up */
		if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' && $cleanup_messages) { 
			$posts = get_posts( array(
				'post_type'		=> 'message',
				'post_status'	=> array('draft', 'trash'),
				'numberposts'	=> -1
				) );
			foreach ($posts as $eachpost) {
				wp_delete_post( $eachpost->ID, true );
			}	
		?>
			<div class="wproject-message notice-success">
				<p><strong><?php _e( 'All read messages were successfully deleted', 'wproject' ); ?></strong></p>
			</div>
		<?php } ?>

		<?php /* ALL messages cleaned up */
		if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' && $cleanup_all_messages) { 
			$posts = get_posts( array(
				'post_type'		=> 'message',
				'post_status'	=> array('draft', 'auto-draft', 'trash', 'publish', 'inherit'),
				'numberposts'	=> -1
				) );
			foreach ($posts as $eachpost) {
				wp_delete_post( $eachpost->ID, true );
			}	
		?>
			<div class="wproject-message notice-success">
				<p><strong><?php _e( 'All messages were successfully deleted', 'wproject' ); ?></strong></p>
			</div>
		<?php } ?>

		<script>
			jQuery('.wproject-message').click(function() {
				jQuery(this).fadeOut();
			});
		</script>

		<!--/ Start Settings Form / -->
		<form method="post" action="options.php">

		<?php settings_fields( 'wproject_settings' ); ?>

			<!--/ Start Settings Nav / -->
			<div class="settings-nav">
				<ul>
					<li data="home" <?php if(isset($_GET['section']) && $_GET['section'] == 'home' || empty($_GET['section'])) { echo 'class="selected"'; } ?>><img src="<?php echo get_template_directory_uri();?>/images/admin/home.svg" /><?php _e( 'Home', 'wproject' ); ?></li>

                    <?php do_action('wproject_admin_nav_start') ?>

					<li data="presentation" id="presentation" <?php if(isset($_GET['section']) && $_GET['section'] == 'presentation') { echo 'class="selected"'; } ?>><img src="<?php echo get_template_directory_uri();?>/images/admin/presentation.svg" /><?php _e( 'Presentation', 'wproject' ); ?></li>
					<li data="permissions" id="permissions" <?php if(isset($_GET['section']) && $_GET['section'] == 'permissions') { echo 'class="selected"'; } ?>><img src="<?php echo get_template_directory_uri();?>/images/admin/permissions.svg" /><?php _e( 'Permissions', 'wproject' ); ?></li>
					<li data="gantt" id="gantt" <?php if(isset($_GET['section']) && $_GET['section'] == 'gantt') { echo 'class="selected"'; } ?>><img src="<?php echo get_template_directory_uri();?>/images/admin/gantt.svg" /><?php _e( 'Gantt Chart', 'wproject' ); ?></li>
					<li data="kanban" id="kanban" <?php if(isset($_GET['section']) && $_GET['section'] == 'kanban') { echo 'class="selected"'; } ?>><img src="<?php echo get_template_directory_uri();?>/images/admin/kanban.svg" /><?php _e( 'Kanban Board', 'wproject' ); ?></li>
					<li data="time" id="time" <?php if(isset($_GET['section']) && $_GET['section'] == 'time') { echo 'class="selected"'; } ?>><img src="<?php echo get_template_directory_uri();?>/images/admin/time.svg" /><?php _e( 'Time & Costs', 'wproject' ); ?></li>
                    <li data="labels" id="labels" <?php if(isset($_GET['section']) && $_GET['section'] == 'labels') { echo 'class="selected"'; } ?>><img src="<?php echo get_template_directory_uri();?>/images/admin/labels.svg" /><?php _e( 'Context Labels', 'wproject' ); ?></li>
					<li data="notifications" id="notifications" <?php if(isset($_GET['section']) && $_GET['section'] == 'notifications') { echo 'class="selected"'; } ?>><img src="<?php echo get_template_directory_uri();?>/images/admin/notifications.svg" /><?php _e( 'Notifications', 'wproject' ); ?></li>
					<li data="comments" id="comments" <?php if(isset($_GET['section']) && $_GET['section'] == 'comments') { echo 'class="selected"'; } ?>><img src="<?php echo get_template_directory_uri();?>/images/admin/comments.svg" /><?php _e( 'Comments', 'wproject' ); ?></li>
					<li data="print" id="print" <?php if(isset($_GET['section']) && $_GET['section'] == 'print') { echo 'class="selected"'; } ?>><img src="<?php echo get_template_directory_uri();?>/images/admin/print.svg" /><?php _e( 'Printing', 'wproject' ); ?></li>

                    <?php do_action('wproject_admin_nav_end') ?>

					<li data="other" id="other" <?php if(isset($_GET['section']) && $_GET['section'] == 'other') { echo 'class="selected"'; } ?>><img src="<?php echo get_template_directory_uri();?>/images/admin/other.svg" /><?php _e( 'Other Settings', 'wproject' ); ?></li>
					<li data="maintenance" id="maintenance" <?php if(isset($_GET['section']) && $_GET['section'] == 'maintenance') { echo 'class="selected"'; } ?>><img src="<?php echo get_template_directory_uri();?>/images/admin/maintenance.svg" /><?php _e( 'Maintenance', 'wproject' ); ?></li>
					
					<li data="credits" id="credits" <?php if(isset($_GET['section']) && $_GET['section'] == 'credits') { echo 'class="selected"'; } ?>><img src="<?php echo get_template_directory_uri();?>/images/admin/credits.svg" /><?php _e( 'Assists', 'wproject' ); ?></li>
				</ul>
				<script>
					jQuery('.settings-nav li.selected').css('pointer-events', 'none');
				</script>

				<ul class="pro-settings">
					<li class="pro-heading"><img src="<?php echo get_template_directory_uri();?>/images/admin/plug.svg" /><?php _e( 'Pro Addons', 'wproject' ); ?></li>
					<?php do_action('wproject_admin_pro_nav_start') ?>
					<?php do_action('wproject_admin_pro_nav_end') ?>
				</ul>
				<p class="get-pro">
					<a href="https://rocketapps.com.au/product-category/wproject-plugins/" rel="noopener noreferrer" target="_blank">
						<img src="<?php echo get_template_directory_uri();?>/images/admin/external-link.svg" />
						<?php _e( 'Get Pro Addons', 'wproject' ); ?>
					</a>
				</p>
			</div>
			<!--/ End Settings Nav / -->


			<!--/ Start Settings Pane / -->
			<div class="settings-pane">

				<div class="settings-div home">
					<h3>
                        <?php _e( "wProject", 'wproject' ); ?> <span>v<?php echo wprojectThemeVersion(); ?></span>
                    </h3>

                    <h1><?php _e( "Welcome to wProject", 'wproject' ); ?></h1>

                    <?php if (version_compare(wprojectThemeVersion(), $remote_version) >= 0) { ?>
                        <div class="wproject-update-status good">
                            <img src="<?php echo get_template_directory_uri();?>/images/admin/check-circle.svg" />
                            <?php _e( "wProject is at the latest version.", 'wproject' ); ?>
                        </div>
                    <?php } else { ?>
                        <div class="wproject-update-status bad">
                            <img src="<?php echo get_template_directory_uri();?>/images/admin/download-cloud.svg" />
                            <?php _e( "There is an update available for wProject.", 'wproject' ); ?> 
                            <a href="<?php echo get_admin_url(); ?>themes.php?theme=wproject"><?php _e( 'Update now', 'wproject' ); ?></a>
                        </div>
                    <?php } ?>

					<?php if(isset($_GET['section']) && $_GET['section'] == 'home' || !isset($_GET['section'])) {
							get_template_part('admin-functions/settings-system');
						}
					?>
				</div>
                


                <?php do_action('wproject_admin_settings_div_start') ?>

				<!--/ Start Presentation / -->
				<div class="settings-div presentation">

					<h3><?php _e( 'Presentation', 'wproject' ); ?></h3>

					<div class="fleft">
						<p>
							<?php _e( 'Branding', 'wproject' ); ?>
							<span><?php _e( 'Replace the login screen logo with your own.', 'wproject' ); ?></span>

							<?php
								// Get the full logo image path
								if($branding_logo) {
									$part = pathinfo($branding_logo);
									$dir_path =  $part['dirname'];
									$file_extension =  $part['extension'];
									$just_file_name = $part['filename'];
								} else {
									$part = '';
									$dir_path =  get_template_directory_uri() . '/images/system';
									$file_extension = 'png';
									$just_file_name = 'touch-icon';
								}
							?>
							<div class="wproject-logo">
								<img src="<?php echo $dir_path . '/' . $just_file_name . '.' . $file_extension; ?>" />
								<span class="dashicons dashicons-no"></span>
							</div>
						</p>
					</div>
					<div class="fright">
						<ul>
							<li class="wproject-uploader">
								<input id="branding" name="wproject_settings[branding_logo]" type="text" <?php if ( ! empty( $branding_logo ) ) { echo 'value="' . $branding_logo . '"'; } ?> />
								<input id="branding_button" class="button" name="branding_button" type="text" value="<?php _e( 'Browse', 'wproject'); ?>" />
								<script>
									/* Media uploader */
									jQuery(document).ready(function($) {
										var _custom_media = true,
										_orig_send_attachment = wp.media.editor.send.attachment;

										jQuery('.wproject-uploader .button').click(function(e) {
											var send_attachment_wp = wp.media.editor.send.attachment;
											var button = $(this);
											var id = button.attr('id').replace('_button', '');
											_custom_media = true;
											wp.media.editor.send.attachment = function(props, attachment){
												if ( _custom_media ) {
													jQuery('#'+id).val(attachment.url);
													jQuery('.wproject-logo img').attr('src', attachment.url)
												} else {
													return _orig_send_attachment.apply( this, [props, attachment] );
												};
											}

											wp.media.editor.open(button);
											return false;
										});

										jQuery('.add_media').on('click', function() {
											_custom_media = false;
										});
									});
									
									/* Replace image with default and clear field */
									jQuery('.wproject-logo .dashicons-no').click(function() {
										jQuery('.wproject-logo img').attr('src', '<?php echo get_template_directory_uri(); ?>/images/system/icon.png');
										jQuery('#branding').val('')
									});
								</script>
							</li>
						</ul>
					</div>
					
					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Favicon', 'wproject' ); ?>
							<span><?php printf( __('Replace the <a href="%1$s" target="_blank" rel="noopener">favicon</a> with your own. For best results, the image needs to be a square PNG and at least 100 x 100 pixels.', 'wproject' ), 'https://en.wikipedia.org/wiki/Favicon' ); ?></span>

							<?php
								// Get the full logo image path
								if($favicon) {
									$part = pathinfo($favicon);
									$dir_path =  $part['dirname'];
									$file_extension =  $part['extension'];
									$just_file_name = $part['filename'];
								} else {
									$part = '';
									$dir_path =  get_template_directory_uri() . '/images/system';
									$file_extension = 'png';
									$just_file_name = 'touch-icon';
								}
							?>
							<div class="favicon-image">
								<img src="<?php echo $dir_path . '/' . $just_file_name . '.' . $file_extension; ?>" />
								<span class="dashicons dashicons-no"></span>
							</div>
						</p>
					</div>
					<div class="fright">
						<ul>
							<li class="favicon-uploader">
								<input id="favicon" name="wproject_settings[favicon]" type="text" <?php if ( ! empty( $favicon ) ) { echo 'value="' . $favicon . '"'; } ?> />
								<input id="favicon_button" class="button" name="favicon_button" type="text" value="<?php _e( 'Browse', 'wproject'); ?>" />
								<script>
									/* Media uploader */
									jQuery(document).ready(function($) {
										var _custom_media = true,
										_orig_send_attachment = wp.media.editor.send.attachment;

										jQuery('.favicon-uploader .button').click(function(e) {
											var send_attachment_wp = wp.media.editor.send.attachment;
											var button = $(this);
											var id = button.attr('id').replace('_button', '');
											_custom_media = true;
											wp.media.editor.send.attachment = function(props, attachment){
												if ( _custom_media ) {
													jQuery('#'+id).val(attachment.url);
													jQuery('.favicon-image img').attr('src', attachment.url)
												} else {
													return _orig_send_attachment.apply( this, [props, attachment] );
												};
											}

											wp.media.editor.open(button);
											return false;
										});

										jQuery('.add_media').on('click', function() {
											_custom_media = false;
										});
									});
									
									/* Replace image with default and clear field */
									jQuery('.favicon-image .dashicons-no').click(function() {
										jQuery('.favicon-image img').attr('src', '<?php echo get_template_directory_uri(); ?>/images/system/icon.png');
										jQuery('#favicon').val('')
									});
								</script>
							</li>
						</ul>
					</div>

					<hr />
                    
                    <div class="fleft">
						<p>
							<?php _e( 'Remove Pages from nav', 'wproject' ); ?>
							<span><?php _e( 'Hide the Pages menu item in the navigation.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
                            <li>
                                <input type="checkbox" name="wproject_settings[remove_pages_nav]" <?php if ( $remove_pages_nav ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?> 
							</li>
						</ul>
					</div>
					
					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Pages navigation label', 'wproject' ); ?>
							<span><?php _e( 'The label that appears above the pages navigation.', 'wproject' ); ?></span>
						</p>
					</div>
					<div class="fright">
						<ul>
							<li>
								<input type="text" name="wproject_settings[pages_label]" <?php if ( ! empty( $pages_label ) ) { echo 'value="' . $pages_label . '"'; } ?> placeholder="Pages" />
							</li>
						</ul>
					</div>
					
					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Project list style', 'wproject' ); ?>
							<span><?php _e( 'Change the way projects are displayed in the left pane.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="radio" name="wproject_settings[project_list_style]" value="list" <?php if ( $project_list_style == 'list' ) { ?>checked<?php } ?> /> <?php _e( 'List (default)', 'wproject' ); ?>   
							</li>
							<li>
								<input type="radio" name="wproject_settings[project_list_style]" value="dropdown" <?php if ( $project_list_style == 'dropdown' ) { ?>checked<?php } ?> /> <?php _e( 'Dropdown', 'wproject' ); ?> 
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Avatar style', 'wproject' ); ?>
							<span><?php _e( 'The appearance of user avatars.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="radio" name="wproject_settings[avatar_style]" value="rounded-corners" <?php if ( $avatar_style == 'rounded-corners' ) { ?>checked<?php } ?> /> <?php _e( 'Rounded corners (default)', 'wproject' ); ?>   
							</li>
							<li>
								<input type="radio" name="wproject_settings[avatar_style]" value="circular" <?php if ( $avatar_style == 'circular' ) { ?>checked<?php } ?> /> <?php _e( 'Circular', 'wproject' ); ?> 
							</li>
							<li>
								<input type="radio" name="wproject_settings[avatar_style]" value="square" <?php if ( $avatar_style == 'square' ) { ?>checked<?php } ?> /> <?php _e( 'Square', 'wproject' ); ?> 
							</li>
						</ul>
					</div>

                    <hr />
                    
                    <div class="fleft">
						<p>
							<?php _e( 'Force avatar', 'wproject' ); ?>
							<span><?php _e( 'Force users to upload a profile photo during onboarding. This is recommended to make it easier for users to identify each other.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
                            <li>
                                <input type="checkbox" name="wproject_settings[force_avatar]" <?php if ( $force_avatar ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?> 
							</li>
						</ul>
					</div>

					<hr />
                    
                    <div class="fleft">
						<p>
							<?php _e( 'Task spacing', 'wproject' ); ?>
							<span><?php _e( 'Add some spacing between tasks listed on project pages.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
                            <li>
                                <input type="checkbox" name="wproject_settings[task_spacing]" <?php if ( $task_spacing ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?> 
							</li>
						</ul>
					</div>

                    <hr />

					<div class="fleft">
						<p>
							<?php _e( 'System busy state', 'wproject' ); ?>
							<span>
                                <?php _e( 'What to do while the system is performing an action.', 'wproject' ); ?>
                                <br />
                                <br />
                                <strong><?php _e( 'Note:', 'wproject' ); ?></strong> <?php _e( 'Disabling the UI will prevent users from navigating away until the performed action has finished.', 'wproject' ); ?>
                            </span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
                                <input type="checkbox" name="wproject_settings[system_busy_blur]" <?php if ( $system_busy_blur ) { ?>checked<?php } ?> /> <?php _e( 'Blur the background', 'wproject' ); ?> 
							</li>
                            <li>
                                <input type="checkbox" name="wproject_settings[system_busy_disable_ui]" <?php if ( $system_busy_disable_ui ) { ?>checked<?php } ?> /> <?php _e( 'Disable UI', 'wproject' ); ?> 
							</li>
						</ul>
					</div>

					<hr />

                    <div class="fleft">
						<p>
							<?php _e( 'Response message position', 'wproject' ); ?>
							<span><?php _e( 'Where to show the response message (desktop only).', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="radio" name="wproject_settings[response_message_position]" value="bottom-right" <?php if ( $response_message_position == 'bottom-right' ) { ?>checked<?php } ?> /> <?php _e( 'Bottom right (default)', 'wproject' ); ?>   
							</li>
							<li>
								<input type="radio" name="wproject_settings[response_message_position]" value="bottom-left" <?php if ( $response_message_position == 'bottom-left' ) { ?>checked<?php } ?> /> <?php _e( 'Bottom left', 'wproject' ); ?>
							</li>
							<li>
								<input type="radio" name="wproject_settings[response_message_position]" value="top-full" <?php if ( $response_message_position == 'top-full' ) { ?>checked<?php } ?> /> <?php _e( 'Top full width', 'wproject' ); ?>
							</li>
                            <li>
								<input type="radio" name="wproject_settings[response_message_position]" value="bottom-full" <?php if ( $response_message_position == 'bottom-full' ) { ?>checked<?php } ?> /> <?php _e( 'Bottom full width', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Pep talk trigger percentage', 'wproject' ); ?>
							<span>
                                <?php _e( 'The pep talk will be triggered when the project reaches this percentage. Set to 0 to disable.', 'wproject' ); ?>
                            </span>
						</p>
					</div>
					<div class="fright">
						<ul>
							<li>
								<input type="number" name="wproject_settings[pep_talk_percentage]" <?php if ( ! empty( $pep_talk_percentage ) ) { echo 'value="' . $pep_talk_percentage . '"'; } ?> min="0" max="99" /> %
							</li>
						</ul>
					</div>

                    <hr />

					<div class="fleft">
						<p>
							<?php _e( 'Pep talk message', 'wproject' ); ?>
							<span><?php _e( 'When the project reaches the percentage specified above, this message will show next to the project manager avatar.', 'wproject' ); ?></span>
						</p>
					</div>
					<div class="fright">
						<ul>
							<li>
                                <input type="text" name="wproject_settings[pep_talk_message]" <?php if ( ! empty( $pep_talk_message ) ) { echo 'value="' . $pep_talk_message . '"'; } ?> placeholder="Good job team!" maxlength="65" />
							</li>
						</ul>
					</div>

					<hr />
					<?php echo $button; ?>
				</div>
				<!--/ End Presentation / -->
			
				<!--/ Start Permissions / -->
				<div class="settings-div permissions">
					<h3><?php _e( 'Permissions', 'wproject' ); ?></h3>

					<div class="fleft">
						<p>
							<?php _e( 'Team capability', 'wproject' ); ?>
							<span><?php _e( 'What team members can do on the front-end.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[users_can_create_tasks]" <?php if ( $users_can_create_tasks ) { ?>checked<?php } ?> /> <?php _e( 'Create tasks', 'wproject' ); ?>
							</li>
							<li>
								<input type="checkbox" name="wproject_settings[users_can_assign_tasks]" <?php if ( $users_can_assign_tasks ) { ?>checked<?php } ?> /> <?php _e( 'Assign and reassign tasks to anyone', 'wproject' ); ?>
							</li>
							<li>
								<input type="checkbox" name="wproject_settings[users_can_task_takeover]" <?php if ( $users_can_task_takeover ) { ?>checked<?php } ?> /> <?php _e( 'Request ownership of tasks from other users', 'wproject' ); ?>
							</li>
							<li>
								<input type="checkbox" name="wproject_settings[users_can_claim_task_ownership]" <?php if ( $users_can_claim_task_ownership ) { ?>checked<?php } ?> /> <?php _e( 'Claim ownership of unowned tasks', 'wproject' ); ?>
							</li>
						</ul>
					</div>
					
					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Team project access', 'wproject' ); ?>
							<span>
                                <?php _e( 'How team members can access projects on the front-end.', 'wproject' ); ?> 
                                <br />
                                <br />
                                <strong><?php _e( 'Note:', 'wproject' ); ?></strong> <?php _e( 'Project managers and administrators can access all projects regardless of this setting.', 'wproject' ); ?>
                            </span>
						</p>
					</div>

					<div class="fright">
                        <ul>
                            <li>
								<input type="radio" name="wproject_settings[project_access]" value="limited" <?php if ( $project_access == 'limited' ) { ?>checked<?php } ?> /> <?php _e( 'Limited (default - only projects they have tasks in)', 'wproject' ); ?>
							</li>
							<li>
								<input type="radio" name="wproject_settings[project_access]" value="all" <?php if ( $project_access == 'all' ) { ?>checked<?php } ?> /> <?php _e( 'Unlimited (access all projects)', 'wproject' ); ?>   
							</li>
                        </ul>
					</div>
					
					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Project managers admin access', 'wproject' ); ?>
							<span><?php _e( 'Allow project managers access to the WordPress admin area. They will be able to manage users and wProject settings, but can not manage plugins.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[allow_pm_admin_access]" <?php if ( $allow_pm_admin_access ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?>
							</li>
						</ul>
					</div>
					
					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Hide team page', 'wproject' ); ?>
							<span><?php _e( 'Deny access to the team page.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
                            <li>
                                <input type="checkbox" name="wproject_settings[team_page]" <?php if ( $team_page ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?> 
							</li>
						</ul>
					</div>

                    <hr />

                    <div class="fleft">
						<p>
							<?php _e( 'Default project manager', 'wproject' ); ?>
							<span>
                                <?php _e( 'If a user is deleted, any projects they were managing will be inherited by this user.', 'wproject' ); ?>
                                <br /><br />
                                <strong><?php _e( 'Note:', 'wproject' ); ?></strong> <?php _e( 'In a rare situation where the default PM is also deleted, the projects will be assigned to the website administrator', 'wproject' ); ?> <?php $admin_data = get_userdata(1); echo $admin_data->user_email;  ?>.
                            </span>
                            
						</p>
					</div>

					<div class="fright">
						<ul>
                            <li>
                                <?php
                                    $users = get_users(array( 'role__in' => array( 'project_manager', 'administrator' ) ));
                                    echo '<select name="wproject_settings[default_pm]">';
                                    echo '<option></option>';
                                    foreach ($users as $user) { 

                                        if($user->first_name && $user->last_name) {
                                            $the_name = $user->first_name . ' ' . $user->last_name . ' (' . $user->user_email . ')';
                                        } else {
                                            $the_name = $user->user_email;
                                        }
                                    ?>
                                        <option value="<?php echo esc_attr($user->ID); ?>"<?php if($default_pm == $user->ID) { echo ' selected'; } ?>><?php echo $the_name; ?> </option>
                                    <?php }
                                    echo '</select>';
                                ?>
							</li>
						</ul>
					</div>

                    <hr />

					<?php echo $button; ?>
				</div>
				<!--/ End Permissions / -->

				<!--/ Start Gantt / -->
				<div class="settings-div gantt">

					<h3><?php _e( 'Gantt Chart', 'wproject' ); ?></h3>

					<div class="fleft">
						<p>
							<?php _e( 'Show the Gantt chart', 'wproject' ); ?>
							<span><?php _e( 'Show the Gantt chart at these locations.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[gantt_show_dashboard]" <?php if ( $gantt_show_dashboard ) { ?>checked<?php } ?> /> <?php _e( 'Dashboard', 'wproject' ); ?>
							</li>
							<li>
								<input type="checkbox" name="wproject_settings[gantt_show_project]" <?php if ( $gantt_show_project ) { ?>checked<?php } ?> /> <?php _e( 'Projects', 'wproject' ); ?>
							</li>
							<li>
								<input type="checkbox" name="wproject_settings[gantt_show_all_project_page]" <?php if ( $gantt_show_all_project_page ) { ?>checked<?php } ?> /> <?php _e( 'All projects page', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Initial Gantt scale (tasks)', 'wproject' ); ?>
							<span><?php _e( 'The initial scale when the Gantt chart contains tasks.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="radio" name="wproject_settings[gantt_scale_tasks]" value="days" <?php if ( $gantt_scale_tasks == 'days' ) { ?>checked<?php } ?> /> <?php _e( 'Days', 'wproject' ); ?>   
							</li>
							<li>
								<input type="radio" name="wproject_settings[gantt_scale_tasks]" value="weeks" <?php if ( $gantt_scale_tasks == 'weeks' ) { ?>checked<?php } ?> /> <?php _e( 'Weeks', 'wproject' ); ?> 
							</li>
							<li>
								<input type="radio" name="wproject_settings[gantt_scale_tasks]" value="months" <?php if ( $gantt_scale_tasks == 'months' ) { ?>checked<?php } ?> /> <?php _e( 'Months', 'wproject' ); ?> 
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Initial Gantt scale (projects)', 'wproject' ); ?>
							<span><?php _e( 'The initial scale when the Gantt chart contains projects.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="radio" name="wproject_settings[gantt_scale_projects]" value="days" <?php if ( $gantt_scale_projects == 'days' ) { ?>checked<?php } ?> /> <?php _e( 'Days', 'wproject' ); ?>   
							</li>
							<li>
								<input type="radio" name="wproject_settings[gantt_scale_projects]" value="weeks" <?php if ( $gantt_scale_projects == 'weeks' ) { ?>checked<?php } ?> /> <?php _e( 'Weeks', 'wproject' ); ?> 
							</li>
							<li>
								<input type="radio" name="wproject_settings[gantt_scale_projects]" value="months" <?php if ( $gantt_scale_projects == 'months' ) { ?>checked<?php } ?> /> <?php _e( 'Months', 'wproject' ); ?> 
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Pagination', 'wproject' ); ?>
							<span><?php _e( 'How many Gantt items to show per vertical pagination.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="number" name="wproject_settings[gantt_pagination]" min="1" max="100" placeholder="10" <?php if ( ! empty( $gantt_pagination ) ) { echo 'value="' . $gantt_pagination . '"'; } ?> />
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p><?php _e( 'Other Gannt settings', 'wproject' ); ?></p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[gantt_hide_completed]" <?php if ( $gantt_hide_completed ) { ?>checked<?php } ?> /> <?php _e( 'Hide completed tasks', 'wproject' ); ?>
							</li>
							<li>
								<input type="checkbox" name="wproject_settings[gantt_jump_to_today]" <?php if ( $gantt_jump_to_today ) { ?>checked<?php } ?> /> <?php _e( 'Jump to today', 'wproject' ); ?>
							</li>
							<li>
								<input type="checkbox" name="wproject_settings[gantt_show_popup]" <?php if ( $gantt_show_popup ) { ?>checked<?php } ?> /> <?php _e( 'Show popups', 'wproject' ); ?>
							</li>
							<li>
								<input type="checkbox" name="wproject_settings[gantt_show_subtasks]" <?php if ( $gantt_show_subtasks ) { ?>checked<?php } ?> /> <?php _e( 'Show subtasks (project page only)', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Popups position', 'wproject' ); ?>
							<span><?php _e( 'Where popups appear when you hover a task in the Gantt chart.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="radio" name="wproject_settings[gantt_popup_position]" value="centre" <?php if ( $gantt_popup_position == 'centre' ) { ?>checked<?php } ?> /> <?php _e( 'Centre (default)', 'wproject' ); ?>   
							</li>
							<li>
								<input type="radio" name="wproject_settings[gantt_popup_position]" value="fixed" <?php if ( $gantt_popup_position == 'fixed' ) { ?>checked<?php } ?> /> <?php _e( 'Fixed (top right)', 'wproject' ); ?> 
							</li>
						</ul>
					</div>

					<hr />

					<?php echo $button; ?>
				</div>
				<!--/ End Gantt / -->

				<!--/ Start Kanban / -->
				<div class="settings-div kanban">

					<h3><?php _e( 'Kanban Board', 'wproject' ); ?></h3>

					<div class="fleft">
						<p>
							<?php _e( 'Enable the Kanban board', 'wproject' ); ?>
							<span><?php _e( 'Allow viewing the Kanban board on projects. This is only available to administrators and project managers.', 'wproject' ); ?></span>
						</p>
					</div>
					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[enable_kanban]" <?php if ( $enable_kanban ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?>
							</li>
						</ul>
					</div>
					
					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Board density', 'wproject' ); ?>
							<span><?php _e( 'How comfortable the cards fit in the Kanban board.', 'wproject' ); ?></span>
						</p>
					</div>
					<div class="fright">
						<ul>
							<li>
								<select name="wproject_settings[kanban_density]">
									<option value="comfortable" <?php if ( $kanban_density == 'comfortable' ) { ?>selected<?php } ?>><?php _e('Comfortable', 'wproject'); ?></option>	
									<option value="loose" <?php if ( $kanban_density == 'loose' ) { ?>selected<?php } ?>><?php _e('Loose', 'wproject'); ?></option>
									<option value="tight" <?php if ( $kanban_density == 'tight' ) { ?>selected<?php } ?>><?php _e('Tight', 'wproject'); ?></option>									
								</select>
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Card colours', 'wproject' ); ?>
							<span><?php _e( 'Add priority colour emphasis to the Kanban cards.', 'wproject' ); ?></span>
						</p>
					</div>
					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[kanban_card_colours]" <?php if ( $kanban_card_colours ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?>
							</li>
						</ul>
					</div>
					
					<hr />

                    <div class="fleft">
						<p>
							<?php _e( 'Show descriptions', 'wproject' ); ?>
							<span><?php _e( 'Show the task descriptions on the Kanban cards.', 'wproject' ); ?></span>
						</p>
					</div>
					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[kanban_card_descriptions]" <?php if ( $kanban_card_descriptions ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?>
							</li>
						</ul>
					</div>
					
					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Unfocused cards', 'wproject' ); ?>
							<span><?php _e( 'Fade or hide unfocused cards when using the Kanban filters.', 'wproject' ); ?></span>
						</p>
					</div>
					<div class="fright">
						<ul>
							<li>
								<input type="radio" name="wproject_settings[kanban_unfocused_cards]" value="fade" <?php if ( $kanban_unfocused_cards == 'fade' ) { ?>checked<?php } ?> /> <?php _e( 'Fade (default)', 'wproject' ); ?>   
							</li>
							<li>
								<input type="radio" name="wproject_settings[kanban_unfocused_cards]" value="hide" <?php if ( $kanban_unfocused_cards == 'hide' ) { ?>checked<?php } ?> /> <?php _e( 'Hide', 'wproject' ); ?>
							</li>
						</ul>
					</div>
					
					<hr />

					<?php echo $button; ?>
				</div>
				<!--/ End Kanban / -->

				<!--/ Start Time / -->
				<div class="settings-div time">
					<h3><?php _e( 'Time & Costs', 'wproject' ); ?></h3>

					<div class="fleft">
						<p>
							<?php _e( 'Enable time', 'wproject' ); ?>
							<span><?php _e('Allow time to be tracked on tasks and projects.', 'wproject'); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[enable_time]" <?php if ( $enable_time ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?>
							</li>
						</ul>
					</div>
					
                    <hr />

                    <div class="fleft">
                        <p>
                            <?php _e( 'Overtime prompt', 'wproject' ); ?>
                            <span><?php _e('Prompt when any logged time entry is more than this many hours.', 'wproject'); ?></span>
                        </p>
                    </div>

                    <div class="fright">
                        <ul>
                            <li>
                                <input type="number" min="1" max="24" name="wproject_settings[overtime]" <?php if ( ! empty( $overtime ) ) { echo 'value="' . $overtime . '"'; } ?> /> <?php _e( 'Hours', 'wproject' ); ?>
                            </li>
                        </ul>
                    </div>

					<hr />
                    
					<div class="fleft">
						<p>
							<?php _e( 'Currency symbol', 'wproject' ); ?>
							<span><?php _e('The currency symbol to show next to costs. If not specified the $ symbol will be shown.', 'wproject'); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="text" name="wproject_settings[currency_symbol]" <?php if ( ! empty( $currency_symbol ) ) { echo 'value="' . $currency_symbol . '"'; } ?> />
							</li>
						</ul>
					</div>
					
					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Currency symbol position', 'wproject' ); ?>
							<span><?php _e('Show the currency symbol left or right of monetary values.', 'wproject'); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="radio" name="wproject_settings[currency_symbol_position]" value="l" <?php if ( $currency_symbol_position == 'l' ) { ?>checked<?php } ?> /> <?php _e( 'Left', 'wproject' ); ?>
							</li>
							<li>
								<input type="radio" name="wproject_settings[currency_symbol_position]" value="r" <?php if ( $currency_symbol_position == 'r' ) { ?>checked<?php } ?> /> <?php _e( 'Right', 'wproject' ); ?>   
							</li>
						</ul>
					</div>
					
					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Default project hourly rate', 'wproject' ); ?>
							<span><?php _e('The default hourly rate your company charges to work on projects. You can still change the rate when creating a new project.', 'wproject'); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="number" min="1" name="wproject_settings[default_project_rate]" <?php if ( ! empty( $default_project_rate ) ) { echo 'value="' . $default_project_rate . '"'; } ?> />
							</li>
						</ul>
					</div>
					
					<hr />

					<?php echo $button; ?>

				</div>
				<!--/ End Time / -->

                <!--/ Start Context Labels / -->
				<div class="settings-div labels">
					<h3><?php _e( 'Context Labels', 'wproject' ); ?></h3>

					<div class="fleft">
						<p>
							<?php _e( 'What are context labels?', 'wproject' ); ?>
							<span>
                                <?php _e( "Context labels are a simple way to improve the context of a task. This can assist in understanding it's purpose.", 'wproject' ); ?>
                                <br /><br />
                                <strong><?php _e( 'Note:', 'wproject' ); ?></strong> <?php _e( 'Enter comma separated words with letters, numbers or dashes.', 'wproject' ); ?>
                            </span>
						</p>
					</div>

					<div class="fright">
                        <ul>
							<li>
                                <textarea name="wproject_settings[context_labels]" id="context_labels"><?php if ( ! empty( $context_labels ) ) { echo $context_labels; } ?></textarea>
							</li>
						</ul>

                        <script>
                            jQuery('#context_labels').keyup(function() {
                                var context_labels = jQuery(this).val();
                                var context_labels_clean = context_labels.replace(/[ ,]+/g, ' ');
                                var context_labels_cleaner = context_labels_clean.replace(/[^a-zÀ-ÿA-Z,^0-9,-]/g, ', ');
                                jQuery('#context_labels').val(context_labels_cleaner);
                            });
                        </script>

					</div>

                    <hr />

					<div class="fleft">
						<p>
							<?php _e( 'Label colour', 'wproject' ); ?>
							<span>
                                <?php _e( "The primary colour of all context labels.", 'wproject' ); ?>
                            </span>
						</p>
					</div>

					<div class="fright">
                        <ul>
							<li>
								<input type="text" name="wproject_settings[context_label_colour]" <?php if ( ! empty( $context_label_colour ) ) { echo 'value="' . $context_label_colour . '"'; } ?> class="colour-picker" />
							</li>
						</ul>
					</div>

                    <hr />

					<div class="fleft">
						<p>
							<?php _e( 'Label display', 'wproject' ); ?>
							<span><?php _e( 'How to display the context labels when creating or editing a task.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="radio" name="wproject_settings[context_label_display]" value="dropdown" <?php if ( $context_label_display == 'dropdown' ) { ?>checked<?php } ?> /> <?php _e( 'Dropdown (default)', 'wproject' ); ?>   
							</li>
							<li>
								<input type="radio" name="wproject_settings[context_label_display]" value="radio" <?php if ( $context_label_display == 'radio' ) { ?>checked<?php } ?> /> <?php _e( 'Radio buttons', 'wproject' ); ?> 
							</li>
						</ul>
					</div>

					<hr />

					<?php echo $button; ?>
				</div>
				<!--/ End Context Labels / -->

				<!--/ Start Notifications / -->
				<div class="settings-div notifications">
					<h3><?php _e( 'Notifications', 'wproject' ); ?></h3>

					<div class="fleft">
						<p>
							<?php _e( 'Email notifications', 'wproject' ); ?>
							<span><?php _e( 'When to send an email notification.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[notify_when_task_takeover]" <?php if ( $notify_when_task_takeover ) { ?>checked<?php } ?> /> <?php _e( 'To task owner when task takeover is requested', 'wproject' ); ?>
							</li>
							<li>
								<input type="checkbox" name="wproject_settings[notify_when_task_takeover_decided]" <?php if ( $notify_when_task_takeover_decided ) { ?>checked<?php } ?> /> <?php _e( 'To user when task takeover decision was made', 'wproject' ); ?>
							</li>
							<li>
								<input type="checkbox" name="wproject_settings[notify_when_task_created]" <?php if ( $notify_when_task_created ) { ?>checked<?php } ?> /> <?php _e( 'To user when a task is created for them', 'wproject' ); ?>
							</li>
							<li>
								<input type="checkbox" name="wproject_settings[notify_when_comment_reply]" <?php if ( $notify_when_comment_reply ) { ?>checked<?php } ?> /> <?php _e( 'To user when someone replies to their comment', 'wproject' ); ?>
							</li>
                            <li>
								<input type="checkbox" name="wproject_settings[notify_pm_when_task_complete]" <?php if ( $notify_pm_when_task_complete ) { ?>checked<?php } ?> /> <?php _e( 'To project manager when a task is complete', 'wproject' ); ?>
							</li>
                            <li>
								<input type="checkbox" name="wproject_settings[notify_pm_when_subtasks_complete]" <?php if ( $notify_pm_when_subtasks_complete ) { ?>checked<?php } ?> /> <?php _e( 'To project manager when all subtasks on a task are complete', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Response message duration', 'wproject' ); ?>
							<span><?php _e( 'How long the response message remains on-screen before fading out. Default is 6 seconds.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="number" name="wproject_settings[response_message_duration]" min="1" max="120" <?php if ( ! empty( $response_message_duration ) ) { echo 'value="' . $response_message_duration . '"'; } ?> /> <?php /* translators: A unit of time */ _e( 'seconds', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Maximum notifications', 'wproject' ); ?>
							<span><?php _e( 'The maximum number of notifications to show in the dropdown.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="number" name="wproject_settings[notify_maximum_messages]" min="1" max="20" <?php if ( ! empty( $notify_maximum_messages ) ) { echo 'value="' . $notify_maximum_messages . '"'; } ?> placeholder="10" />
							</li>
						</ul>
					</div>
					
					<hr />

                    <div class="fleft">
						<p>
							<?php _e( 'Sender name', 'wproject' ); ?>
							<span><?php _e( 'If not specified, the sender name on email notifications will be:', 'wproject' ); ?> <strong><?php echo get_bloginfo( 'name' ); ?></strong></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="text" name="wproject_settings[sender_name]" id="sender_name" <?php if ( ! empty( $sender_name ) ) { echo 'value="' . $sender_name . '"'; } ?> />
							</li>
						</ul>
					</div>
					
					<hr />

                    <div class="fleft">
						<p>
							<?php _e( 'Sender email', 'wproject' ); ?>
							<span><?php _e( 'If not specified, the sender email address on email notifications will be:', 'wproject' ); ?> <strong><?php echo get_bloginfo( 'admin_email' ); ?></strong></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="text" name="wproject_settings[sender_email]" id="sender_email" <?php if ( ! empty( $sender_email ) ) { echo 'value="' . $sender_email . '"'; } ?> />
							</li>
						</ul>
					</div>
					
					<hr />

					<?php echo $button; ?>
					
				</div>
				<!--/ End Notifications / -->

				<!--/ Start Comments / -->
				<div class="settings-div comments">
					<h3><?php _e( 'Comments', 'wproject' ); ?></h3>

					<div class="fleft">
						<p>
							<?php _e( 'Task comments', 'wproject' ); ?>
							<span><?php _e( 'Allow users to comment on tasks', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[task_comments_enabled]" <?php if ( $task_comments_enabled ) { ?>checked<?php } ?> /> <?php _e( 'Yes', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Page comments', 'wproject' ); ?>
							<span><?php _e( 'Allow users to comment on pages', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[page_comments_enabled]" <?php if ( $page_comments_enabled ) { ?>checked<?php } ?> /> <?php _e( 'Yes', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Number of recent comments', 'wproject' ); ?>
							<span><?php _e( 'How many comments to show in the recent comments dropdown.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="number" name="wproject_settings[recent_comments_number]" min="1" max="20" <?php if ( ! empty( $recent_comments_number ) ) { echo 'value="' . $recent_comments_number . '"'; } ?> />
							</li>
						</ul>
					</div>

					<hr />

                    <div class="fleft">
						<p>
							<?php _e( 'Initial comment order', 'wproject' ); ?>
							<span><?php _e( 'The order in which comments are initially displayed.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="radio" name="wproject_settings[comment_order]" value="latest" <?php if ( $comment_order == 'latest' ) { ?>checked<?php } ?> /> <?php _e( 'Newest first (default)', 'wproject' ); ?>   
							</li>
							<li>
								<input type="radio" name="wproject_settings[comment_order]" value="oldest" <?php if ( $comment_order == 'oldest' ) { ?>checked<?php } ?> /> <?php _e( 'Oldest first', 'wproject' ); ?> 
							</li>
						</ul>
					</div>

					<hr />

                    <div class="fleft">
						<p>
							<?php _e( 'Always show comment dates', 'wproject' ); ?>
							<span><?php _e( 'Comment dates will show without having to click the comment.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[show_comment_dates]" <?php if ( $show_comment_dates ) { ?>checked<?php } ?> /> <?php _e( 'Yes', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

					<?php echo $button; ?>

				</div>
				<!--/ End Comments / -->

				<!--/ Start Printing / -->
				<div class="settings-div print">
					<h3><?php _e( 'Printing', 'wproject' ); ?></h3>

					<div class="fleft">
						<p>
							<?php _e( 'Hide completed tasks', 'wproject' ); ?>
							<span><?php _e( 'Do not include completed tasks when printing a project.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[print_hide_complete_tasks]" <?php if ( $print_hide_complete_tasks ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Disable user photos when printing', 'wproject' ); ?>
							<span><?php _e('User photos will not be shown on printed or PDF documents.', 'wproject'); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[print_hide_user_photos]" <?php if ( $print_hide_user_photos ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?>
							</li>
						</ul>
					</div>
					
					<hr />

                    <div class="fleft">
						<p>
							<?php _e( 'Disable task descriptions when printing', 'wproject' ); ?>
							<span><?php _e('Task descriptions will not be shown on printed or PDF documents.', 'wproject'); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[print_hide_task_descriptions]" <?php if ( $print_hide_task_descriptions ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?>
							</li>
						</ul>
					</div>
					
					<hr />

					<?php echo $button; ?>

				</div>
				<!--/ End Printing / -->

                <?php do_action('wproject_admin_settings_div_end') ?>

				<!--/ Start Other / -->
				<div class="settings-div other">
					<h3><?php _e( 'Other Settings', 'wproject' ); ?></h3>

                    <div class="fleft">
						<p>
							<?php _e( 'Subtasks additional information', 'wproject' ); ?><em class="new"><?php _e('New', 'wproject'); ?></em>
							<span><?php _e( 'Include fields for additional information when creating subtasks.', 'wproject' ); ?></span>
						</p>
					</div>
					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[enable_subtask_descriptions]" <?php if ( $enable_subtask_descriptions ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Bypass Google CDN', 'wproject' ); ?></em>
							<span><?php _e( 'Enable this if Google is blocked in your country.', 'wproject' ); ?></span>
						</p>
					</div>
					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[bypass_google]" <?php if ( $bypass_google ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Leave warnings', 'wproject' ); ?>
							<span><?php _e( 'A warning will appear when accidentally attempting to leave an incomplete new task or new project.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[enable_leave_warning]" <?php if ( $enable_leave_warning ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Job number prefix', 'wproject' ); ?>
							<span><?php _e( 'The default prefix for job numbers', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="text" name="wproject_settings[job_number_prefix]" placeholder="WP-" <?php if ( ! empty( $job_number_prefix ) ) { echo 'value="' . $job_number_prefix . '"'; } ?> />
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Show the task ID', 'wproject' ); ?>
							<span><?php _e( 'The post ID will show next to tasks.', 'wproject' ); ?></span>
						</p>
					</div>
					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[show_task_id]" <?php if ( $show_task_id ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Preferred calendar', 'wproject' ); ?>
							<span><?php _e('Used for when adding a task or project to your calendar.', 'wproject'); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<select name="wproject_settings[preferred_calendar]">
									<option value="0" <?php if ( $preferred_calendar == '0' ) { ?>selected<?php } ?>><?php _e('Disabled', 'wproject'); ?></option>
									<option value="google" <?php if ( $preferred_calendar == 'google' ) { ?>selected<?php } ?>><?php _e('Google (web)', 'wproject'); ?></option>
									<option value="yahoo" <?php if ( $preferred_calendar == 'yahoo' ) { ?>selected<?php } ?>><?php _e('Yahoo (web)', 'wproject'); ?></option>
									<option value="outlook" <?php if ( $preferred_calendar == 'outlook' ) { ?>selected<?php } ?>><?php _e('Microsoft Outlook (web)', 'wproject'); ?></option>
									<option value="ical" <?php if ( $preferred_calendar == 'ical' ) { ?>selected<?php } ?>><?php _e('iCal compatible (desktop)', 'wproject'); ?></option>
								</select>
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Completed projects navigation visibility', 'wproject' ); ?>
							<span><?php _e("Don't show completed projects in the main navigation.", 'wproject'); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[completed_projects_nav]" <?php if ( $completed_projects_nav ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

                    <div class="fleft">
						<p>
							<?php _e( 'Fade on hold', 'wproject' ); ?>
							<span><?php _e('Fade tasks that are on hold.', 'wproject'); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[fade_on_hold]" <?php if ( $fade_on_hold ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

                    <div class="fleft">
						<p>
							<?php _e( 'Show all project tasks on task relationships', 'wproject' ); ?>
							<span><?php _e( 'When selecting a task relationship, allow selection from all projects instead of just the current project (not recommended, may impact performance).', 'wproject' ); ?></span>
						</p>
					</div>
					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[relation_tasks]" <?php if ( $relation_tasks ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Dashboard message', 'wproject' ); ?>
							<span><?php _e( 'Add a message to the dashboard (visible to all users).', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="text" name="wproject_settings[dashboard_message]" id="dashboard_message" <?php if ( ! empty( $dashboard_message ) ) { echo 'value="' . $dashboard_message . '"'; } ?> />
							</li>
						</ul>
					</div>
					
					<hr />

                    <div class="fleft">
						<p>
							<?php _e( 'Extend session time', 'wproject' ); ?><em class="beta"><?php _e('Beta', 'wproject'); ?></em>
							<span><?php _e('Stay logged in for longer.', 'wproject'); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
                                <input type="number" name="wproject_settings[session_time]" <?php if ( ! empty( $session_time ) ) { echo 'value="' . $session_time . '"'; } ?> min="1" max="365" /> <?php _e('Days', 'wproject'); ?>
							</li>
						</ul>
					</div>

                    <hr />

                    <div class="fleft">
						<p>
							<?php _e( 'Allow deleting projects from the back-end interface', 'wproject' ); ?>
							<span><?php _e( 'This is not recommended. Deleting a project from the back-end will orphan all its tasks, leaving a mess to clean-up in the future.', 'wproject' ); ?></span>
						</p>
					</div>
					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[delete_projects_from_backend]" <?php if ( $delete_projects_from_backend ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

					<?php echo $button; ?>

				</div>
				<!--/ End Other / -->

				<!--/ Start Maintenance / -->
				<div class="settings-div maintenance">
					<h3><?php _e( 'Maintenance', 'wproject' ); ?></h3>

					<div class="fleft">
						<p>
							<?php _e( 'Theme maintenance', 'wproject' ); ?>
							<span><?php _e( 'This will reset all theme settings to default. Only run theme maintenance if you are experiencing problems.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[reset]" value="1" class="reset-theme" /> <?php _e( 'Reset all theme settings', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

					<div class="fleft">
						<p>
							<?php _e( 'Notification message maintenance', 'wproject' ); ?>
							<span><?php _e( 'Delete some or all notifications from the database.', 'wproject' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[cleanup_messages]" value="1" class="clean-up-messages" /> <?php _e( 'Purge trashed notifications only', 'wproject' ); ?>
							</li>
							<li>
								<input type="checkbox" name="wproject_settings[cleanup_all_messages]" value="1" class="clean-up-all-messages" /> <?php _e( 'Purge all notifications', 'wproject' ); ?>
							</li>
						</ul>
					</div>

					<hr />

                    <div class="fleft">
						<p>
							<?php _e( 'Database maintenance', 'wproject' ); ?>
							<span><?php printf( __('Cleanup all orphan task debris. This is highly recommended if you installed wProject before 27th October 2022, or if you are in the habit of deleting projects from the back-end. <a href="%1$s" target="_blank" rel="noopener">Learn why</a>.', 'wproject' ), 'https://rocketapps.com.au/wproject/maintenance-options/' ); ?></span>
						</p>
					</div>

					<div class="fright">
						<ul>
							<li>
								<input type="checkbox" name="wproject_settings[cleanup_db]" class="cleanup-db" /> <?php _e( 'Perform retroactive database cleanup', 'wproject' ); ?>
							</li>
						</ul>
					</div>

                    <script>
                        jQuery(document).on('click', '.cleanup-db', function() {
                            if (confirm('<?php _e('IMPORTANT! As a precaution, please backup your database before running this maintenance function.', 'wproject'); ?>')) {
                                if (confirm('<?php _e('ARE YOU SURE? Do not proceed unless you have you backed up your database.', 'wproject'); ?>')) {
                                    jQuery(this).val('1');
                                    jQuery('.reset-theme').prop('checked', false);
                                    jQuery('.clean-up-messages, .clean-up-all-messages').prop('checked', false);
                                } else {
                                    jQuery(this).prop('checked', false);    
                                }
                            } else {
                                jQuery(this).prop('checked', false);
                            }
                        });
                        jQuery('.reset-theme, .clean-up-messages, .clean-up-all-messages').click(function() {
                            jQuery('.reset-theme, .clean-up-messages, .cleanup-db, .clean-up-all-messages').prop('checked', false);
                            jQuery(this).prop('checked', true);
                        });
                    </script>

					<hr />
					
					<?php echo $button; ?>

				</div>
				<!--/ End Maintenance / -->

				<!--/ Start Credits / -->
				<div class="settings-div credits">
					<h3><?php _e( 'Assists', 'wproject' ); ?></h3>
                    <h4><?php _e( 'Assists', 'wproject' ); ?></h4>
					<p><?php _e( 'The following superstars have generously contributed their time to help improve wProject.', 'wproject' ); ?></p>
					
					<hr />

					<div class="helpers">

						<p class="helper">
							<img src="<?php echo get_template_directory_uri();?>/images/admin/adriano.jpg" class="avatar" />

							<strong>Adriano Dias</strong>
							<span>
								<img src="<?php echo get_template_directory_uri();?>/images/admin/star.svg" /> Português do Brasil <?php _e('translation', 'wproject'); ?>
							</span>
							<span>
								<img src="<?php echo get_template_directory_uri();?>/images/admin/star.svg" /> <?php _e('Feature feedback', 'wproject'); ?>
							</span>
							<span>							
								<img src="<?php echo get_template_directory_uri();?>/images/admin/star.svg" /> <?php _e('Beta testing', 'wproject'); ?>
							</span>
							<span>							
								<img src="<?php echo get_template_directory_uri();?>/images/admin/star.svg" /> <?php _e('Functionality suggestions', 'wproject'); ?>
							</span>
							<a href="https://www.duploa-ssma.com.br" target="_blank" rel="noopener nofollow">www.duploa-ssma.com.br</a><br />
							<a href="mailto:adriano.dias@me.com">adriano.dias@me.com</a>
						</p>

                        <p class="helper">
							<img src="<?php echo get_template_directory_uri();?>/images/admin/bilal.jpg" class="avatar" />

							<strong>Bilal Koç</strong>
							<span>
								<img src="<?php echo get_template_directory_uri();?>/images/admin/star.svg" /> Turkish <?php _e('translation', 'wproject'); ?>
							</span>
							<span>							
								<img src="<?php echo get_template_directory_uri();?>/images/admin/star.svg" /> <?php _e('Functionality suggestions', 'wproject'); ?>
							</span>
							<a href="https://bilalkoc.com.tr" target="_blank" rel="noopener nofollow">bilalkoc.com.tr</a><br />
							<a href="mailto:info@bilalkoc.com.tr">info@bilalkoc.com.tr</a>
						</p>

						<p class="helper">
							<img src="<?php echo get_template_directory_uri();?>/images/admin/luca.jpg" class="avatar" />

							<strong>Luca Alberto Deodati</strong>
							<span>
								<img src="<?php echo get_template_directory_uri();?>/images/admin/star.svg" /> Italiano <?php _e('translation', 'wproject'); ?>
							</span>
							<span>
								<img src="<?php echo get_template_directory_uri();?>/images/admin/star.svg" /> <?php _e('Feature feedback', 'wproject'); ?>
							</span>
							<span>							
								<img src="<?php echo get_template_directory_uri();?>/images/admin/star.svg" /> <?php _e('Beta testing', 'wproject'); ?>
							</span>
							<span>							
								<img src="<?php echo get_template_directory_uri();?>/images/admin/star.svg" /> <?php _e('Functionality suggestions', 'wproject'); ?>
							</span>
							<a href="https://www.compil-er.it" target="_blank" rel="noopener nofollow">compil-er.it</a><br />
							<a href="mailto:info@compil-er.it">info@compil-er.it</a>
						</p>

						<p class="helper">
							<img src="<?php echo get_template_directory_uri();?>/images/admin/shraban.jpg" class="avatar" />

							<strong>Mostafizur Rahman Shraban</strong>
							<span>
								<img src="<?php echo get_template_directory_uri();?>/images/admin/star.svg" /> <?php _e('Feature feedback', 'wproject'); ?>
							</span>
							<span>							
								<img src="<?php echo get_template_directory_uri();?>/images/admin/star.svg" /> <?php _e('Beta testing', 'wproject'); ?>
							</span>
							<span>							
								<img src="<?php echo get_template_directory_uri();?>/images/admin/star.svg" /> <?php _e('Functionality suggestions', 'wproject'); ?>
							</span>
							<a href="https://vanguardsln.com" target="_blank" rel="noopener nofollow">vanguardsln.com</a><br />
							<a href="mailto:shraban@vanguardsln.com">shraban@vanguardsln.com</a>
						</p>
						
					</div>
					
				</div>
				<!--/ End Credits / -->

			</div>
			<!--/ End Settings Pane / -->

			<?php if(!wp_is_mobile()) { get_template_part('admin-functions/settings-sidebar'); } ?>
			<?php wp_nonce_field( 'save-wproject-settings','wproject-noncecheck' ) ?>

		</form>
		<!--/ End Settings Form / -->

		<?php 
			if(isset($_GET['section']) && $_GET['section'] ) { 
				$section = $_GET['section'];
			} else {
				$section = 'home';
			}
		?>
		<script>
			jQuery('.<?php echo $section; ?>').addClass('active');;
			jQuery('.settings-nav li').click(function() {
				var theClass = jQuery(this).attr('data');
				var url = '<?php echo admin_url(); ?>admin.php?page=wproject-settings&section=';
				location.href = url + theClass;
				return false;
			});

            <?php $icon = isset($_GET['section']) ? $_GET['section'] : ''; ?>
            var title_icon = '<?php echo get_template_directory_uri(); ?>/images/admin/<?php echo $icon; ?>.svg';
            <?php if($icon && $icon != 'home') { ?>
                jQuery('.settings-div h3').prepend('<span class="invert"><img src="'+title_icon+'" /></span>');
            <?php } else if($icon == 'home') { ?>
                jQuery('.settings-div h3').prepend('<img src="<?php echo get_template_directory_uri(); ?>/images/admin/wproject-logo.svg" class="home-logo-icon" />');
            <?php } else { ?>
                jQuery('.settings-div h3').prepend('<img src="<?php echo get_template_directory_uri(); ?>/images/admin/wproject-logo.svg" class="home-logo-icon" />');
            <?php } ?>
		</script>

	</div>
	<!--/ End Settings Interface / -->

<?php
}
/* End Project Settings */

global $wp_roles;
/* Add roles */
remove_role('project_manager');
add_role(
	'project_manager',
	__( 'Project manager', 'wproject' ),
	array(
		'level_9'                => true,
		'level_8'                => true,
		'level_7'                => true,
		'level_6'                => true,
		'level_5'                => true,
		'level_4'                => true,
		'level_3'                => true,
		'level_2'                => true,
		'level_1'                => true,
		'manage_options'	     => true,
		'level_0'                => true,
		'read'                   => true,
		'list_users'             => true,
		'edit_users'             => true,
		'remove_users'			 => true,
		'add_users'			 	 => true,
		'create_users'			 => true,
		'promote_users'			 => true,
	)
);
//remove_role('project_manager');

remove_role('observer');
add_role(
	'observer',
	__( 'Observer', 'wproject' ),
	array(
		'read'                   => true,
	)
);
//remove_role('observer');

add_role(
	'operator',
	__( 'Operator', 'wproject' ),
	array(
		'level_9'                => true,
		'level_8'                => true,
		'level_7'                => true,
		'level_6'                => true,
		'level_5'                => true,
		'level_4'                => true,
		'level_3'                => true,
		'level_2'                => true,
		'level_1'                => true,
		'manage_options'	     => true,
		'level_0'                => true,
		'read'                   => true
	)
);
//remove_role('operator');

add_role(
	'team_member',
	__( 'Team member', 'wproject' ),
	array(
		'level_9'                => true,
		'level_8'                => true,
		'level_7'                => true,
		'level_6'                => true,
		'level_5'                => true,
		'level_4'                => true,
		'level_3'                => true,
		'level_2'                => true,
		'level_1'                => true,
		'level_0'                => true,
		'read'                   => true,
		'edit_posts'             => true,
		'edit_pages'             => true,
		'edit_published_posts'   => true,
		'edit_published_pages'   => true,
		'edit_others_pages'      => true,
		'publish_posts'          => true,
		'publish_pages'          => true,
		'delete_posts'           => true,
		'delete_pages'           => true,
		'delete_published_pages' => true,
		'delete_published_posts' => true,
		'delete_others_posts'    => true,
		'delete_others_pages'    => true,
		'manage_categories'      => true,
		'upload_files'           => true,
		'list_users'             => true
	)
);


/* Admin header stuff */
function wproject_admin_stuff() {

    $wproject_settings              = wProject(); 
    $delete_projects_from_backend   = $wproject_settings['delete_projects_from_backend'];

    ?>
	<script>
		jQuery( document ).ready(function() {

			<?php 
				/* For the new user page */
				if (stripos($_SERVER['REQUEST_URI'], 'user-new.php')) {
			?>

			/* Force fist and last names to be required in add user interface */
			jQuery('#first_name').attr('required', 'required');
			jQuery('.form-field:nth-child(3), .form-field:nth-child(4)').addClass('form-required');
			jQuery('.form-field:nth-child(3) th label, .form-field:nth-child(4) th label').append(' (<?php /* translators: Example: This field is required */ _e('required', 'wproject'); ?>)');

			<?php if(!isset($_GET['user_id'])) { ?>
			/* Make the default role 'Team member' in select box when adding new user */
			jQuery('#role option[value=team_member]').prop('selected', 'selected');
			<?php } ?>
			
			/* Disable the other roles */
			jQuery('#role option[value=subscriber], #role option[value=author], #role option[value=contributor], #role option[value=editor]').prop('disabled', 'disabled');

			/* Add some text explaining about the wProject roles */
			jQuery('.form-field:last-child td, .user-role-wrap td').append('<p><?php _e('<strong>Note:</strong> The only roles used by wProject are <strong>Team member</strong>, <strong>Project manager</strong>, <strong>Administrator</strong>, <strong>Operator</strong> and <strong>Client</strong> (if the Client Pro plugin is activated).', 'wproject'); ?></p>');

			/* Remove the gravatar and bio section on edit user admin page */
			jQuery('.user-profile-picture').remove();

			/* Limit short description characters on new project */
			jQuery('#tag-description').attr('maxlength', '150');

			<?php } ?>

			/* remove admin column(s) from Projects */
			jQuery(".taxonomy-project .column-slug").remove();

            <?php if(!$delete_projects_from_backend) { ?>
                jQuery('.taxonomy-project #the-list .delete, .taxonomy-project #delete-link a').remove();
            <?php } else { ?>
                jQuery('body.taxonomy-project .table-view-list, body.taxonomy-project #edittag').before('<div style="display: flex; align-items: center; justify-content: center; padding: 20px; background: #cee1f1; font-weight: 600; margin: 20px 0 0 0;"><img src="<?php echo get_template_directory_uri();?>/images/admin/alert-circle.svg" style="width: 18px; height: 18px; margin: 0 5px 0 0;" /><?php printf( __('Note: If you intend to delete a project, it is recommended you do it from the front-end (<a href="%1$s" target="_blank" rel="noopener">find out why</a>).', 'wproject' ), 'https://rocketapps.com.au/wproject/why-you-should-delete-projects-from-the-front-end/' ); ?></div>');
            <?php } ?>

		});
	</script>
<?php }
add_action( 'admin_head', 'wproject_admin_stuff' );

/* Admin CSS */
function admin_style() { 
	$theme 			= wp_get_theme();
    $theme_version	= $theme->Version;$wproject_settings = wProject();
?>
	<link rel='stylesheet' id='wproject-admin-style-css'  href='<?php echo get_template_directory_uri(); ?>/css/wproject-admin.css?ver=<?php echo $theme_version;?>' type='text/css' media='all' />
<?php }
add_action('admin_head', 'admin_style');

/* Do stuff on theme activation */
add_action('after_setup_theme', 'wproject_admin_setup');
function wproject_admin_setup() {

    /* Delete default page(s) */
    wp_delete_post(1, $force_delete = false ); /* Hello world */
    wp_delete_post(2, $force_delete = false ); /* Sample page */
    wp_delete_post(3, $force_delete = false ); /* Privacy policy */

    /* Create pages */
    $pages = array(
        // Array of Pages and associated Templates
        __('Account', 'wproject')		=> array(''=>'page.php'), // 100
        __('Edit Project', 'wproject')	=> array(''=>'page.php'), // 101
        __('Edit Task', 'wproject')		=> array(''=>'page.php'), // 102
        __('My Tasks', 'wproject')		=> array(''=>'page.php'), // 103
        __('New Project', 'wproject')	=> array(''=>'page.php'), // 104
        __('New Task', 'wproject')		=> array(''=>'page.php'), // 105
        __('Projects', 'wproject')		=> array(''=>'page.php'), // 106
        __('Report', 'wproject')		=> array(''=>'page.php'), // 107
        __('Team', 'wproject')			=> array(''=>'page.php'), // 108
        __('User Profile', 'wproject')	=> array(''=>'page.php')  // 109
    );

    foreach($pages as $page_url_title => $page_meta) {

        $args = array(
            'post_type' => 'page',
            'pagename' => sanitize_title($page_url_title),
            'post_status' => 'publish',
            'fields' => 'ids'
        );

        $page_id = new WP_Query($args);

        $id_start = 100;

        foreach ($page_meta as $page_content => $page_template){
            $page = array(
                'import_id'		=> $id_start++,
                'post_type'   	=> 'page',
                'post_title'  	=> $page_url_title,
                'post_name'   	 => sanitize_title($page_url_title),
                'post_status' 	=> 'publish',
                'post_content'	=> $page_content,
                'post_author' 	=> 1,
                'post_parent' 	=> ''
            );

            if(empty($page_id->posts)){
                $new_page_id = wp_insert_post($page);
                if(!empty($page_template)){
                    update_post_meta($new_page_id, 'my_page', '1');
                }
            }
        }
        wp_reset_postdata();
    }

	/* Register custom post type and taxonomy if 'task' custom post type doesn't exist */
	$exists = post_type_exists( 'task' );
	if(!$exists) {

		/* Set up custom post type for tasks
		https://codex.wordpress.org/Function_Reference/register_post_type */
		function create_tasks_post_type() {

			$plugin_directory = plugins_url('images/', __FILE__ );
			register_post_type( 'task',

				array(
					'labels' => array(
						'singular_name'     => __( 'Task', 'wproject'),
						'name' 				=> __( 'Tasks', 'wproject'),
						'add_new'           => __( 'Add Task', 'wproject'),
						'add_new_item'      => __( 'Add Task', 'wproject'),
						'edit_item'         => __( 'Edit Task', 'wproject'),
						'new_item'          => __( 'New Task', 'wproject'),
						'search_items'      => __( 'Search Tasks', 'wproject'),
						'not_found'  		=> __( 'No Tasks Found', 'wproject'),
						'not_found_in_trash'=> __( 'No Tasks Found in Trash', 'wproject'),
						'all_items'     	=> __( 'All Tasks','wproject')
					),
				'public'			 	=> true,
				'has_archive' 			=> false,
				'rewrite'				=> array('slug' => 'task'),
				'publicly_queryable'  	=> true,
				'hierarchical'        	=> true,
				'show_ui' 				=> true,
				'show_in_menu'          => true,
				'exclude_from_search'	=> false,
				'query_var'				=> true,
				'menu_position'			=> 20,
				'can_export'          	=> true,
				'menu_icon'         	=> get_template_directory_uri() . '/images/admin/admin-icon-tasks.svg',
				'supports'  			=> array('title', 'revisions', 'author', 'comments'),
				//'supports'  			=> array('title', 'revisions', 'author'),
				'capability_type'       => 'post',
				'taxonomies'            => array('projects'),
				'map_meta_cap'          => true,
				)
			);
		}
		add_action( 'init', 'create_tasks_post_type' );

		add_action( 'init', 'create_projects_taxonomy' );
		function create_projects_taxonomy() {

			$labels = array(
				'name'              => _x( 'Projects', 'wproject' ),
				'singular_name'     => _x( 'Project', 'wproject' ),
				'search_items'      => __( 'Search projects', 'wproject' ),
				'all_items'         => __( 'All projects', 'wproject' ),
				'parent_item'       => __( 'Parent projects', 'wproject' ),
				'parent_item_colon' => __( 'Parent project:', 'wproject' ),
				'edit_item'         => __( 'Edit project', 'wproject' ),
				'update_item'       => __( 'Update project', 'wproject' ),
				'add_new_item'      => __( 'Add new project', 'wproject' ),
				'new_item_name'     => __( 'New project name', 'wproject' ),
				'menu_name'         => __( 'Projects', 'wproject' ),
			);

			register_taxonomy(
				'project',
				'task',
				array(
					'label' 		=> __( 'Projects' ),
					'rewrite' 		=> array( 'slug' => 'project' ),
					'hierarchical' 	=> false, // Change to 'true' to be like categories (hierarchical)
					'labels'		=> $labels
				)
			);
		}
	}
}

/* Do stuff on theme activation */
if ( is_admin() && isset($_GET['activated'] ) && $pagenow == "themes.php" ) {
    /* Clear default widgets */
    update_option( 'widget_block', '' );
}

require_once('task-groups.php');

/* Admin column label for project status */
function project_status_column_header( $project_status_columns ){
$project_status_columns['p_status'] = 'Status'; 
	return $project_status_columns;
}
add_filter( "manage_edit-project_columns", 'project_status_column_header', 10);

/* Admin column value for project status */
function project_status_column_value( $value, $column_name, $tax_id ){
	$project_meta = get_term_meta($tax_id); 
	$project_status = $project_meta['project_status'][0];
	if($project_status == 'in-progress') {
		$the_project_status = __('In progress', 'wproject');
	} else if($project_status == 'planning') {
		$the_project_status = __('Planning', 'wproject');
	} else if($project_status == 'proposed') {
		$the_project_status = __('Proposed', 'wproject');
	} else if($project_status == 'setting-up') {
		$the_project_status = __('Setting up', 'wproject');
	} else if($project_status == 'archived') {
		$the_project_status = __('Archived', 'wproject');
	} else if($project_status == 'cancelled') {
		$the_project_status = __('Cancelled', 'wproject');
	} else if($project_status == 'complete') {
		$the_project_status = __('Complete', 'wproject');
	}
	return $the_project_status;
}
add_action( "manage_project_custom_column", 'project_status_column_value', 10, 3);


/* Register custom post for 'message' if custom post type doesn't exist */
$message_exists = post_type_exists( 'message' );
if(!$message_exists) {

	function create_messages_post_type() {

		$plugin_directory = plugins_url('images/', __FILE__ );
		register_post_type( 'message',

			array(
				'labels' => array(
					'singular_name'     => __( 'Message', 'wproject'),
					'name' 				=> __( 'Messages', 'wproject'),
					'add_new'           => __( 'Add Message', 'wproject'),
					'add_new_item'      => __( 'Add Message', 'wproject'),
					'edit_item'         => __( 'Edit Message', 'wproject'),
					'new_item'          => __( 'New Message', 'wproject'),
					'search_items'      => __( 'Search Messages', 'wproject'),
					'not_found'  		=> __( 'No Messages Found', 'wproject'),
					'not_found_in_trash'=> __( 'No Messages Found in Trash', 'wproject'),
					'all_items'     	=> __( 'All Messages','wproject')
				),
			'public'			 	=> false,
			'has_archive' 			=> false,
			'rewrite'				=> array('slug' => 'message'),
			'publicly_queryable'  	=> true,
			'hierarchical'        	=> false,
			'show_ui' 				=> true,
			'show_in_menu'          => true,
			'exclude_from_search'	=> true,
			'query_var'				=> true,
			'menu_position'			=> 26,
			'can_export'          	=> true,
			'menu_icon'         	=> get_template_directory_uri() . '/images/admin/admin-icon-messages.svg',
			'supports'  			=> array('title', 'author', 'editor'),
			'capability_type'       => 'post',
			'map_meta_cap'          => true,
			)
		);
	}
	add_action( 'init', 'create_messages_post_type' );
}


/* Add custom fields to Project taxonomy page */
function wproject_project_fields() { ?>

	<div class="form-field">
		<label><?php _e( 'Full description', 'wproject' ); ?></label>
		<textarea name="project_full_description" class="high-text-area"></textarea>
	</div>

	<!--/ Start half /-->
	<div class="wpr-half">

		<div class="form-field">
			<label for="project_manager"><?php _e( 'Project manager', 'wproject' ); ?></label>
			<select name="project_manager" required>
				<option></option>
				<?php
					$users = get_users( array( 'role__in' => array( 'project_manager', 'administrator' ) ) );
					foreach ( $users as $user ) {

						if($user->first_name) {
							$first_name = $user->first_name;
						} else {
							$first_name = $user->nickname;
						}
						if($user->last_name) {
							$last_name = $user->last_name;
						} else {
							$last_name = '';
						}
						if($user->title) {
							$title = ' - ' . $user->title;
						} else {
							$title = '';
						}
					?>
						<option value="<?php echo esc_html( $user->ID ); ?>" <?php if($user->ID == get_current_user_id()) { echo 'selected'; } ?>><?php echo esc_html( $first_name); ?> <?php echo esc_html( $last_name); ?> <?php echo esc_html( $title ); ?></option>
					<?php }
				?>
			</select>
		</div>
		<div class="form-field">
			<label for="project_job_number"><?php _e( 'Job #', 'wproject' ); ?></label>
			<input type="text" name="project_job_number" />
		</div> 
		
	</div>
	<!--/ End half /-->
	
	<!--/ Start quarter /-->
	<div class="wpr-quarter">

		<div class="form-field">
			<label><?php _e( 'Start date', 'wproject' ); ?></label>
			<input type="date" name="project_start_date" placeholder="<?php _e( 'Start date', 'wproject' ); ?>" />
		</div> 
		<div class="form-field">
			<label><?php _e( 'Due', 'wproject' ); ?></label>
			<input type="date" name="project_end_date" placeholder="<?php _e( 'Due', 'wproject' ); ?>" />
		</div> 
		<div class="form-field form-required">
			<label><?php _e( 'Time', 'wproject' ); ?></label>
			<input type="number" step=".1" min="0" name="project_time_allocated" placeholder="<?php /* Abbreviation of 'hours': */ _e( 'hrs', 'wproject' ); ?>" /> 
		</div>
		<div class="form-field form-required">
			<label><?php _e( 'Rate', 'wproject' ); ?></label>
			<input type="number" step=".1" min="0" name="project_hourly_rate" placeholder="<?php /* translators: A 'global' currency symbol placeholder. */ _e( '$', 'wproject' ); ?>" />
		</div>

	</div> 
	<!--/ End quarter /-->

	<div class="form-field form-required">
		<label for="project_status"><?php _e( 'Status', 'wproject' ); ?></label>
		<select name="project_status">
			<option></option>
			<option value="in-progress"><?php _e( 'In progress', 'wproject' ); ?></option>
			<option value="planning"><?php _e( 'Planning', 'wproject' ); ?></option>			
			<option value="proposed"><?php _e( 'Proposed', 'wproject' ); ?></option>	
			<option value="setting-up"><?php _e( 'Setting up', 'wproject' ); ?></option>		
		</select>
	</div> 
<?php 
} 
add_action('project_add_form_fields','wproject_project_fields', 10, 2 );

/* Add custom fields to Project taxonomy edit page */
function edit_wproject_project_fields($term) { 
	
	$term_meta = get_term_meta($term->term_id); 
	if(isset($term_meta['project_manager'][0])) {
		$pm_id = $term_meta['project_manager'][0];
	}
?> 
	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="project_full_description"><?php _e( 'Full description', 'wproject' ); ?></label>
		</th>
		<td>
			<textarea name="project_full_description" class="high-text-area"><?php if(isset($term_meta['project_full_description'][0])) { echo esc_attr( $term_meta['project_full_description'][0] ); } ?></textarea>
		</td>
	</tr> 
	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="project_manager"><?php _e( 'Project manager', 'wproject' ); ?></label>
		</th>
		<td>
			<select name="project_manager" required>
				<option></option>
				<?php
					$users = get_users( array( 'role__in' => array( 'project_manager', 'administrator' ) ) );
					foreach ( $users as $user ) { ?>
						<option value="<?php echo esc_html( $user->ID ); ?>" <?php if($pm_id == $user->ID) { echo 'selected'; } ?>><?php echo esc_html( $user->first_name ); ?> <?php echo esc_html( $user->last_name ); ?> - <?php echo esc_html( $user->title ); ?></option>
					<?php } ?>
			</select>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="project_job_number"><?php _e( 'Job #', 'wproject' ); ?></label>
		</th>
		<td>
			<input type="text" name="project_job_number" id="project_job_number" value="<?php if(isset($term_meta['project_job_number'][0])) { echo esc_attr( $term_meta['project_job_number'][0] ); } ?>" class="wpr-25" />
		</td>
	</tr> 
	<tr class="form-field">
		<th scope="row" valign="top">
			<label><?php _e( 'Start & end dates', 'wproject' ); ?></label>
		</th>
		<td>
			<input type="date" name="project_start_date" id="project_start_date" value="<?php if(isset($term_meta['project_start_date'][0])) { echo esc_attr( $term_meta['project_start_date'][0] ); } ?>" placeholder="<?php _e( 'Start date', 'wproject' ); ?>" class="wpr-25" /> 
			<input type="date" name="project_end_date" id="project_end_date" value="<?php if(isset($term_meta['project_end_date'][0])) { echo esc_attr( $term_meta['project_end_date'][0] ); } ?>" placeholder="<?php _e( 'Due', 'wproject' ); ?>" class="wpr-25" />
		</td>
	</tr> 
	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="project_time_allocated"><?php _e( 'Time allocated', 'wproject' ); ?></label>
		</th>
		<td>
			<input type="number" step=".1" min="0" name="project_time_allocated" id="project_time_allocated" value="<?php if(isset($term_meta['project_time_allocated'][0])) { echo esc_attr( $term_meta['project_time_allocated'][0] ); } ?>" class="wpr-25" />
		</td>
	</tr> 
	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="project_hourly_rate"><?php _e( 'Rate', 'wproject' ); ?></label>
		</th>
		<td>
			<input type="number" step=".1" min="0" name="project_hourly_rate" id="project_hourly_rate" value="<?php if(isset($term_meta['project_hourly_rate'][0])) { echo esc_attr( $term_meta['project_hourly_rate'][0] ); } ?>" class="wpr-25" />
		</td>
	</tr> 
	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="project_status"><?php _e( 'Status', 'wproject' ); ?></label>
		</th>
		<td>
			<select name="project_status">
				<option></option>
				<option value="archived" <?php if(isset($term_meta['project_status'][0]) && $term_meta['project_status'][0] == 'archived') { echo 'selected'; } ?>><?php _e( 'Archived', 'wproject' ); ?></option>
				<option value="cancelled" <?php if(isset($term_meta['project_status'][0]) && $term_meta['project_status'][0] == 'cancelled') { echo 'selected'; } ?>><?php _e( 'Cancelled', 'wproject' ); ?></option>
				<option value="complete" <?php if(isset($term_meta['project_status'][0]) && $term_meta['project_status'][0] == 'complete') { echo 'selected'; } ?>><?php _e( 'Complete', 'wproject' ); ?></option>
				<option value="inactive" <?php if(isset($term_meta['project_status'][0]) && $term_meta['project_status'][0] == 'inactive') { echo 'selected'; } ?>><?php _e( 'Inactive', 'wproject' ); ?></option>
				<option value="in-progress" <?php if(isset($term_meta['project_status'][0]) && $term_meta['project_status'][0] == 'in-progress') { echo 'selected'; } ?>><?php _e( 'In progress', 'wproject' ); ?></option>
                <option value="on-hold" <?php if(isset($term_meta['project_status'][0]) && $term_meta['project_status'][0] == 'on-hold') { echo 'selected'; } ?>><?php _e( 'On hold', 'wproject' ); ?></option>
				<option value="planning" <?php if(isset($term_meta['project_status'][0]) && $term_meta['project_status'][0] == 'planning') { echo 'selected'; } ?>><?php _e( 'Planning', 'wproject' ); ?></option>			
				<option value="proposed" <?php if(isset($term_meta['project_status'][0]) && $term_meta['project_status'][0] == 'proposed') { echo 'selected'; } ?>><?php _e( 'Proposed', 'wproject' ); ?></option>	
				<option value="setting-up" <?php if(isset($term_meta['project_status'][0]) && $term_meta['project_status'][0] == 'setting-up') { echo 'selected'; } ?>><?php _e( 'Setting up', 'wproject' ); ?></option>		
			</select>
		</td>
	</tr> 
    <tr class="form-field">
		<th scope="row" valign="top">
			<label for="web_page_url"><?php _e( 'Web page', 'wproject' ); ?></label>
		</th>
		<td>
			<input type="text" name="web_page_url" id="web_page_url" value="<?php if(isset($term_meta['web_page_url'][0])) { echo esc_attr( $term_meta['web_page_url'][0] ); } ?>" class="wpr-100" />
		</td>
	</tr> 
<?php 
} 
add_action( 'project_edit_form_fields', 'edit_wproject_project_fields', 10, 2 );

/* Save the fields. */
function save_wproject_project_fields( $term_id ) { 
	if ( isset( $_POST['project_full_description'] ) ) {
		$term_meta = get_term_meta($term_id); 
		update_term_meta($term_id, 'project_full_description', $_POST['project_full_description']); 
	} 
	if ( isset( $_POST['project_manager'] ) ) {
		$term_meta = get_term_meta($term_id); 
		update_term_meta($term_id, 'project_manager', $_POST['project_manager']); 
	} 
	if ( isset( $_POST['project_job_number'] ) ) {
		$term_meta = get_term_meta($term_id); 
		update_term_meta($term_id, 'project_job_number', $_POST['project_job_number']); 
	} 
	if ( isset( $_POST['project_start_date'] ) ) {
		$term_meta = get_term_meta($term_id); 
		update_term_meta($term_id, 'project_start_date', $_POST['project_start_date']); 
	} 
	if ( isset( $_POST['project_end_date'] ) ) {
		$term_meta = get_term_meta($term_id); 
		update_term_meta($term_id, 'project_end_date', $_POST['project_end_date']); 
	} 
	if ( isset( $_POST['project_time_allocated'] ) ) {
		$term_meta = get_term_meta($term_id); 
		update_term_meta($term_id, 'project_time_allocated', $_POST['project_time_allocated']); 
	} 
	if ( isset( $_POST['project_hourly_rate'] ) ) {
		$term_meta = get_term_meta($term_id); 
		update_term_meta($term_id, 'project_hourly_rate', $_POST['project_hourly_rate']); 
	}
	if ( isset( $_POST['project_status'] ) ) {
		$term_meta = get_term_meta($term_id); 
		update_term_meta($term_id, 'project_status', $_POST['project_status']); 
	}
    if ( isset( $_POST['web_page_url'] ) ) {
		$term_meta = get_term_meta($term_id); 
		update_term_meta($term_id, 'web_page_url', $_POST['web_page_url']); 
	}
} 


/* Custom metabox for pages */
class page_project_box {

	function  __construct() {

		add_action( 'add_meta_boxes', array( $this, 'page_project_metabox' ) );
		add_action( 'save_post', array($this, 'save_data') );
	}

	/* Add the meta boxes to post types */
	function page_project_metabox() {
		
		$post_types = array ('page'); 
		
		foreach( $post_types as $post_type ) {
		
			add_meta_box(
				'page_project',	// metabox ID, it also will be it id HTML attribute
				__('Project page', 'wproject'), // title
				array( $this, 'meta_box_content' ),
				$post_type,		// post types
				'side',			// position of the screen where metabox should be displayed (normal, side, advanced)
				'high'			// priority over another metaboxes on this page (default, low, high, core)
		  );
	  }
	}

	function meta_box_content() {
		global $post;
		$the_post_type = get_post_type(); ?>

		<div class="new-task-admin">
			
			<label><?php _e( 'Only show this page on this project', 'wproject' ); ?>:</label>
			<select name="page_project" style="width:100%">
				<option></option>
				<?php $projects = array(
						'taxonomy'      => 'project',
						'hide_empty'    => 0,
						'orderby'       => 'name',
						'post_status'   => 'publish',
						'order'         => 'ASC',
						'hierarchical'  => 0,
						'meta_query' => array(
							array(
							'key'       => 'project_status',
							'value'     => 'complete',
							'compare'   => '!='
							)
						),
					);
					$cats = get_categories($projects);
					
					$tasks_query = new WP_Query( $projects );
					$project_count = $tasks_query->found_posts;

					foreach($cats as $cat) {
						$term_meta = get_term_meta($cat->term_id); 
						$project_status = $term_meta['project_status'][0];

						/* Get the total number of tasks in each project */
						$completed_tasks_project_args = array(
							'posts_per_page' 	=> -1,
							'post_type' 		=> 'task',
							'post_status'		=> 'publish',
							'tax_query' => array(
								array(
									'taxonomy' => 'project',
									'field'    => 'slug',
									'terms'    => array( $cat->slug ),
									'operator' => 'IN'
								),
							),
							'meta_key' 		=> 'task_status',
							'meta_value'	=> array('complete')
						);
						$completed_tasks_posts_project = new WP_Query($completed_tasks_project_args);
						$completed_tasks_project = $completed_tasks_posts_project->post_count;
						$page_project = get_post_meta($post->ID, 'page_project', TRUE);

						if(isset($project_status) && $project_status != 'cancelled' && $project_status != 'archived') {
					?>
					<option value="<?php echo $cat->term_id; ?>" <?php if($page_project == $cat->term_id) { echo 'selected'; } ?>><?php echo $cat->name; ?></option>
					<?php
						}
					} ?>
			</select>
			

		</div>

		<?php
	}

	function save_data($post_id) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if(isset($_POST['page_project'])) {
			$data = $_POST['page_project'];
			update_post_meta($post_id, 'page_project', sanitize_text_field($data));
			return $data;
		}
	}
}
$page_project_box = new page_project_box;


/* Add actions so that the function runs when category is added or edited */
add_action( 'edited_project', 'save_wproject_project_fields', 10, 2 ); 
add_action( 'create_project', 'save_wproject_project_fields', 10, 2 );


/* Add the custom columns to the 'task' post type */
add_filter( 'manage_task_posts_columns', 'set_custom_edit_task_columns' );
function set_custom_edit_task_columns($columns) {
    unset( $columns['author'] );
    $columns['project_name'] = __( 'Project', 'wproject' );
	$columns['task_owner'] = __( 'Task owner', 'wproject' );
    return $columns;
}

/* Add the data to the custom columns for the 'task' post type */
add_action( 'manage_task_posts_custom_column' , 'custom_task_column', 10, 2 );
function custom_task_column( $column, $post_id ) {
    switch ( $column ) {
        case 'project_name' :
			$category = get_the_terms( get_the_id(), 'project' );     
				if(!empty($category)) {
					foreach ( $category as $cat) {
					echo $cat->name;
				}
			} else {
				echo '<span style="color:#f44336; background:#fcf2f0; font-size:12px; padding: 2px 5px; border-radius: 2px;">';
				_e('[Orphan]', 'wproject');
				echo '</span>';
			}
		break;

		case 'task_owner' :
			$author_id = get_post_field ('post_author', get_the_id());
			$first_name = get_the_author_meta( 'first_name' , $author_id ); 
			$last_name = get_the_author_meta( 'last_name' , $author_id ); 
			echo $first_name . ' ' . $last_name;
		break;
    }
}


// Add "Project Manager" column to "project" taxonomy
add_filter( 'manage_edit-project_columns', 'add_project_manager_column' );
function add_project_manager_column( $columns ) {
    $columns['project_manager'] = __( 'Project manager', 'text-domain' );
    return $columns;
}

/* Add Project Manager name into the Projects admin column */
add_filter( 'manage_project_custom_column', 'populate_project_manager_column', 10, 3 );
function populate_project_manager_column( $content, $column_name, $term_id ) {
    if ( 'project_manager' === $column_name ) {
        $tasks = get_posts( array(
            'post_type' => 'task',
            'tax_query' => array(
                array(
                    'taxonomy' => 'project',
                    'field' => 'term_id',
                    'terms' => $term_id,
                )
            ),
            'fields' => 'ids',
        ) );
        
        $term_meta  = get_term_meta($term_id); 
        $pm_user    = get_user_by('ID', $term_meta['project_manager'][0]);
        $pm_name    = $pm_user->first_name . ' ' . $pm_user->last_name;

        $content    = esc_html( $pm_name);
    }
    return $content;
}

/* Rename "Count" column to "Tasks" on the Projects admin column */
add_filter( 'manage_edit-project_columns', 'rename_count_column' );
function rename_count_column( $columns ) {
    $columns['posts'] = __( 'Tasks', 'text-domain' );
    return $columns;
}

/* Force first and last names to be mandatory in admin */
add_action('user_profile_update_errors', 'require_first_last_name', 10, 3);
function require_first_last_name($errors, $update, $user) {
    if (empty($_POST['first_name'])) {
        $errors->add('first_name_error', __('Please enter a first name.', 'wproject'));
    }
    if (empty($_POST['last_name'])) {
        $errors->add('last_name_error', __('Please enter a last name.', 'wproject'));
    }
}

/* Force the display name to be the first and last names */
function set_display_name_from_first_and_last_name( $user_id ) {
    $first_name = get_user_meta( $user_id, 'first_name', true );
    $last_name = get_user_meta( $user_id, 'last_name', true );
    $display_name = trim( $first_name . ' ' . $last_name );
    wp_update_user( array( 'ID' => $user_id, 'display_name' => $display_name ) );
}
add_action( 'user_register', 'set_display_name_from_first_and_last_name' );

