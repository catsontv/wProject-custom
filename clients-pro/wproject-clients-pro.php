<?php /*
Plugin Name:    Clients Pro for wProject
Plugin URI:     https://rocketapps.com.au/product/clients-pro/
Description:    Allow clients limited access to participate in specific projects.
Version:        1.4.0
Author: 		Rocket Apps
Author URI: 	https://rocketapps.com.au
Text Domain: 	wproject-clients-pro
Author Email:   support@rocketapps.com.au
Domain Path:    /languages/
*/

/* Look for translation file. */
function wp_clients_pro_textdomain() {
    load_plugin_textdomain( 'wproject-clients-pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wp_clients_pro_textdomain' );
update_option('clients_pro_key', '************'); 

/* Constants */
define('CLIENTS_PRO_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('CLIENTS_PRO_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('CLIENTS_PRO_SPECIAL_KEY', '563931a7aaebe3.67050558');
define('CLIENTS_PRO_LICENSE_SERVER_URL', 'https://rocketapps.com.au');
define('CLIENTS_PRO_ITEM_REFERENCE', 'Clients Pro'); 


/* Update checker */
function clients_pro_update_checker() {
	$license_key = get_option('clients_pro_key');
	$api_params = array(
		'slm_action'        => 'slm_check',
		'secret_key'        => CLIENTS_PRO_SPECIAL_KEY,
		'license_key'       => $license_key,
		'registered_domain' => $_SERVER['SERVER_NAME'],
		'item_reference'    => urlencode(CLIENTS_PRO_ITEM_REFERENCE),
	);

	/* Send query to the license manager server */
	$query = esc_url_raw(add_query_arg($api_params, CLIENTS_PRO_LICENSE_SERVER_URL));
	$response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => true));

	/* Check for error in the response */
	if (is_wp_error($response)) { 
		_e('Unexpected Error! The query returned with an error.', 'wproject-clients-pro');
	}

	/* License data */
	$license_data = json_decode(wp_remote_retrieve_body($response));

	if (isset($license_data->status) == 'active') {
		require 'plugin-update-checker/plugin-update-checker.php';
		$RPUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://rocketapps.com.au/files/wproject/clients-pro/info.json',
			__FILE__,
			'clients-pro'
		);
	}
}
/* 
	Only allow update check function within the following pages.
	This is to avoid constant license check requests being sent to server.
*/
$update_pages = array(
    '/wp-admin/update-core.php',
    '/wp-admin/plugins.php',
    '/wp-admin/plugin-install.php',
    '/wp-admin/includes/version.php',
    '/wp-admin/includes/plugin.php',
    '/wp-admin/includes/admin.php',
    '/wp-admin/admin-ajax.php',
    '/wp-admin/update.php',
    '/wp-admin/network/update-core.php',
    '/wp-admin/network/plugins.php',
    '/wp-admin/network/plugin-install.php',
    '/wp-admin/network/includes/version.php',
    '/wp-admin/network/includes/plugin.php',
    '/wp-admin/network/includes/admin.php',
    '/wp-admin/network/admin-ajax.php',
    '/wp-admin/network/update.php');
if (in_array($_SERVER['SCRIPT_NAME'], $update_pages) && !empty(get_option('clients_pro_key')) && is_admin()) {
    clients_pro_update_checker();
} 

/* Add Client role */
add_role(
	'client',
	'Client',
	array(
		'level_9'                => false,
		'level_8'                => false,
		'level_7'                => false,
		'level_6'                => false,
		'level_5'                => false,
		'level_4'                => false,
		'level_3'                => false,
		'level_2'                => false,
		'level_1'                => false,
		'level_0'                => false,
		'read'                   => false,
		'edit_posts'             => false,
		'edit_pages'             => false,
		'edit_published_posts'   => false,
		'edit_published_pages'   => false,
		'edit_others_pages'      => false,
		'publish_posts'          => false,
		'publish_pages'          => false,
		'delete_posts'           => false,
		'delete_pages'           => false,
		'delete_published_pages' => false,
		'delete_published_posts' => false,
		'delete_others_posts'    => false,
		'delete_others_pages'    => false,
		'manage_categories'      => false,
		'upload_files'           => false,
		'list_users'             => false
	)
);

/* Add Clients Pro settings page to menu */
function add_clients_pro_settings_page() {

	$wproject_capability    = apply_filters( 'wproject_required_capabilities', 'manage_options' );
	$icon_url               = plugins_url('/images/admin-icon.svg', __FILE__);
    add_menu_page( 
        __( 'Clients Pro','wproject-clients-pro'),
        __( 'Clients Pro','wproject-clients-pro' ), 'manage_options', 
        'wproject-clients-pro', 'clients_pro_license_page',
        $icon_url, 
        32
    );
	do_action( 'clients_pro_menu_items', 'wproject-license', $wproject_capability );
}
add_action( 'admin_menu', 'add_clients_pro_settings_page' );

function clients_pro_license_page() {
	require_once('clients-pro-license.php');
}

/* Add Clients Pro interface menu item into admin */
function add_new_clients_nav_item() { ?>
    <li data="clients-pro" id="clients-pro" <?php if(isset($_GET['section']) && $_GET['section'] == 'clients-pro') { echo 'class="selected"'; } ?>><img src="<?php echo plugins_url('/images/icon.svg', __FILE__);?>" /><?php _e( 'Clients Pro', 'wproject-clients-pro' ); ?></li>
<?php }
add_action('wproject_admin_pro_nav_start', 'add_new_clients_nav_item', 3);


/* wProject theme version check */
function clients_theme_version_check() {
    $wproject_theme = wp_get_theme();
    $theme_version  = $wproject_theme->get( 'Version' );
    return $theme_version;
}

/* Get plugin version */
function clientsPluginVersion() {
    $plugin_data = get_plugin_data( __FILE__ );
    $plugin_version = $plugin_data['Version'];
    return $plugin_version;
}

/* Add Client settings div into admin */
function add_client_settings() {

    $options                        = get_option( 'wproject_settings' );
	$wproject_objects               = isset($options['objects'] ) ? $options['objects'] : array();
    $clients_pro_key                = get_option('clients_pro_key');
    $something                      = isset($options['something']) ? $options['something'] : '';
    $pages_label                    = isset($options['pages_label']) ? $options['pages_label'] : '';
    $client_access_pages            = isset($options['client_access_pages']) ? $options['client_access_pages'] : '';
    $client_use_kanban              = isset($options['client_use_kanban']) ? $options['client_use_kanban'] : '';
    $client_access_team             = isset($options['client_access_team']) ? $options['client_access_team'] : '';
    $client_create_own_tasks        = isset($options['client_create_own_tasks']) ? $options['client_create_own_tasks'] : '';
    $client_can_assign_tasks        = isset($options['client_can_assign_tasks']) ? $options['client_can_assign_tasks'] : '';
    $client_view_reports            = isset($options['client_view_reports']) ? $options['client_view_reports'] : '';
    $client_view_project_details    = isset($options['client_view_project_details']) ? $options['client_view_project_details'] : '';
    $client_comment_tasks           = isset($options['client_comment_tasks']) ? $options['client_comment_tasks'] : '';
    $client_comment_pages           = isset($options['client_comment_pages']) ? $options['client_comment_pages'] : '';
    $client_project_access          = isset($options['client_project_access']) ? $options['client_project_access'] : '';
    $client_use_search              = isset($options['client_use_search']) ? $options['client_use_search'] : '';
    $client_view_others_tasks       = isset($options['client_view_others_tasks']) ? $options['client_view_others_tasks'] : '';
    $enable_kanban                  = isset($options['enable_kanban']) ? $options['enable_kanban'] : '';
    $users_url                      = admin_url() . 'users.php';
    
    
    if(get_option('wproject_key')) {
        $button = '<input name="submit" class="button" value="' . __( 'Save Settings', 'wproject-clients-pro' ) . '" type="submit" />';
    } else {
        $button = '<a href="' . admin_url() . 'admin.php?page=wproject-license" class="button warn">'. __( 'Activate License Key', 'wproject-clients-pro' ) .'</a>';
    }
?>
    
    <!--/ Start Clients Pro / -->
    <div class="settings-div clients-pro">

    <h3><?php _e( 'Clients Pro', 'wproject-clients-pro' ); ?> <span>v<?php echo clientsPluginVersion(); ?></span><a href="<?php echo admin_url(); ?>plugins.php?s=clients+pro" class="update-check"><?php _e( 'Check for update', 'wproject-clients-pro' ); ?></a></h3>
    <?php if(!$clients_pro_key) { ?>
    <h4 class="warning"><?php printf( __('Please <a href="%1$s" rel="noopener">activate your license key</a> to unlock Clients Pro.', 'wproject-clients-pro'), admin_url() . 'admin.php?page=wproject-clients-pro'); ?></h4>
    <?php } ?>

    <!--/ Start Version Check Notice / -->
    <?php 
        $required_version   = '3.9.2';
        $update_link        = admin_url() . 'themes.php?theme=wproject';
        if (version_compare(clients_theme_version_check(), $required_version) < 0) { ?>
            <div class="wproject-notice">
                <strong><?php printf( __('wProject Clients Pro requires at least wProject %1$s. <a href="%2$s" rel="noopener">Update now</a>.', 'wproject-clients-pro'), $required_version, $update_link); ?></strong>
            </div>
    <?php } ?>
    <!--/ End Version Check Notice / -->

    <!--/ Start Dashboard / -->
    <div class="wproject-plugin-dashboard">
        <h2><?php _e( 'Clients Pro', 'wproject-clients-pro' ); ?></h2>
        <a href="https://rocketapps.com.au/product/clients-pro/#changelog" target="_blank" class="wproject-button"><?php _e( "What's new?", 'wproject-clients-pro' ); ?></a>
        <a href="https://rocketapps.com.au/product/clients-pro/" target="_blank" rel="noopener noreferrer" class="wproject-button"><?php _e( "Read the FAQ", 'wproject-clients-pro' ); ?></a>
        <a href="https://rocketapps.com.au/log-ticket" target="_blank" rel="noopener noreferrer" class="wproject-button"><?php _e( "Support", 'wproject-clients-pro' ); ?></a>
    </div>
    <!--/ End Dashboard / -->

    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Project access', 'wproject-clients-pro' ); ?>
            <span>
                <?php _e( 'Which projects can clients access on the front-end?', 'wproject-clients-pro' ); ?>
                <br />
                <br />
                <strong><?php _e( 'Note:', 'wproject-clients-pro' ); ?></strong> 
                <?php _e( 'If you choose specific project access, in addition to having access to projects you define the user will also have access to projects they already have existing tasks in (if any).', 'wproject-clients-pro' ); ?>
            </span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="radio" name="wproject_settings[client_project_access]" value="limited" <?php if ( $client_project_access == 'limited' ) { ?>checked<?php } ?> /> <?php _e( 'Limited (default - only projects they have tasks in)', 'wproject-clients-pro' ); ?>   
            </li>
            <li>
                <input type="radio" name="wproject_settings[client_project_access]" value="unlimited" <?php if ( $client_project_access == 'unlimited' ) { ?>checked<?php } ?> /> <?php _e( 'Unlimited (access all projects)', 'wproject-clients-pro' ); ?> 
            </li>
            <li>
                <input type="radio" name="wproject_settings[client_project_access]" value="specific" <?php if ( $client_project_access == 'specific' ) { ?>checked<?php } ?> /> <?php printf( __('Specific (defined in <a href="%1$s">user profiles</a>)', 'wproject-clients-pro' ), $users_url); ?> 
            </li>
        </ul>
    </div>

    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Capability', 'wproject-clients-pro' ); ?>
            <span><?php _e( 'What can clients do on the front-end?', 'wproject-clients-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="checkbox" name="wproject_settings[client_create_own_tasks]" <?php if ( $client_create_own_tasks ) { ?>checked<?php } ?> /> <?php _e( 'Create and edit their own tasks', 'wproject-clients-pro' ); ?>
            </li>
            <li>
                <input type="checkbox" name="wproject_settings[client_can_assign_tasks]" <?php if ( $client_can_assign_tasks ) { ?>checked<?php } ?> /> <?php _e( 'Create tasks for other users', 'wproject-clients-pro' ); ?>
            </li>
            <li>
                <input type="checkbox" name="wproject_settings[client_comment_tasks]" <?php if ( $client_comment_tasks ) { ?>checked<?php } ?> /> <?php _e( 'Comment on tasks (if comments are enabled on tasks)', 'wproject-clients-pro' ); ?>
            </li>
            <li>
                <input type="checkbox" name="wproject_settings[client_comment_pages]" <?php if ( $client_comment_pages ) { ?>checked<?php } ?> /> <?php _e( 'Comment on pages (if comments are enabled on pages)', 'wproject-clients-pro' ); ?>
            </li>
            <li>
                <input type="checkbox" name="wproject_settings[client_view_project_details]" <?php if ( $client_view_project_details ) { ?>checked<?php } ?> /> <?php _e( 'View project info', 'wproject-clients-pro' ); ?>
            </li>
            <li>
                <input type="checkbox" name="wproject_settings[client_access_team]" <?php if ( $client_access_team ) { ?>checked<?php } ?> /> <?php _e( 'View the team page', 'wproject-clients-pro' ); ?>
            </li>
            <li>
                <input type="checkbox" name="wproject_settings[client_view_others_tasks]" <?php if ( $client_view_others_tasks ) { ?>checked<?php } ?> /> <?php _e( 'View tasks owned by other users', 'wproject-clients-pro' ); ?>
            </li>
            <li>
                <input type="checkbox" name="wproject_settings[client_access_pages]" <?php if ( $client_access_pages ) { ?>checked<?php } ?> /> 
                <?php _e( 'Access', 'wproject-clients-pro' );  echo ' '; if($pages_label) { echo $pages_label; } else { _e( 'Pages', 'wproject-clients-pro' ); } ?>
            </li>
        </ul>
    </div>

    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Kanban board interaction', 'wproject-clients-pro' ); ?>
            <span>
                <?php _e( 'How much control do clients have over the Kanban board?', 'wproject-clients-pro' ); ?> 
                <?php if(!$enable_kanban) { ?>
                    <br />
                    <a href="<?php echo admin_url(); ?>admin.php?page=wproject-settings&section=kanban"><?php _e( 'The Kanban board needs to be enabled for this to work.', 'wproject-clients-pro' ); ?></a>
                <?php } ?>
            </span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="radio" name="wproject_settings[client_use_kanban]" value="none" <?php if ( $client_use_kanban == 'none' ) { ?>checked<?php } ?> /> <?php _e( 'None (read-only)', 'wproject-clients-pro' ); ?>   
            </li>
            <li>
                <input type="radio" name="wproject_settings[client_use_kanban]" value="basic" <?php if ( $client_use_kanban == 'basic' ) { ?>checked<?php } ?> /> <?php _e( 'Basic (can only drag and drop their own tasks)', 'wproject-clients-pro' ); ?> 
            </li>
            <li>
                <input type="radio" name="wproject_settings[client_use_kanban]" value="full" <?php if ( $client_use_kanban == 'full' ) { ?>checked<?php } ?> /> <?php _e( 'Full control (can drag and drop any task)', 'wproject-clients-pro' ); ?> 
            </li>
        </ul>
    </div>

    <hr />

    <?php echo $button; ?>

    <?php if(!$clients_pro_key) { ?>
    <script>
        jQuery('.clients-pro input[type="submit"]').remove();
        jQuery('.clients-pro input').attr('disabled', 'disabled');
    </script>
    <?php } ?>

    <script>
        jQuery( document ).ready(function() {
            <?php $icon = isset($_GET['section']) ? $_GET['section'] : ''; ?>
            <?php if($icon && $icon == 'clients-pro') { ?>
                jQuery('.settings-div h3 span img').attr('src', '<?php echo plugins_url('/images/admin-icon.svg', __FILE__) ?>');
                jQuery('.settings-div h3 span:first-child').removeClass('invert').css('margin', '0').css('opacity', '1');
            <?php } ?>
        });
    </script>
    
    </div>
    <!--/ End Clients Pro / -->

<?php }
add_action('wproject_admin_settings_div_end', 'add_client_settings');


/* Client dashboard welcome */
function client_home() { 
    
    wp_get_current_user();
    $current_user_id    = get_current_user_id();
	$user               = get_userdata($current_user_id);
	$role               = $user->roles[0];

    $first_name = user_details()['first_name'];
    $last_name = user_details()['last_name'];
    if($role == 'client') {
    
?>
	
    <h1><?php _e( 'Dashboard', 'wproject-clients-pro' ); ?></h1>
    <p class="client-intro"><?php echo sprintf( __('Welcome %1$s', 'wproject-clients-pro'), $first_name); ?>. <span></span></p>

    <script>
        $( document ).ready(function() {
            var welcome_text_details = $('section.left .user em').text();
            $('.client-intro span').text(welcome_text_details);
        });
    </script>

<?php }
}
add_action('home_status', 'client_home');


/* User avatar and info */
function client_avatar() {
    $current_user_id    = get_current_user_id();
	$user               = get_userdata($current_user_id);
	$role               = $user->roles[0];

    if($role == 'client') {
        include( plugin_dir_path( __FILE__ ) . 'inc/avatar.php');
    }
}
add_action('avatar', 'client_avatar');

/* Admin CSS */
function clients_enqueue($hook) {
    $screen = get_current_screen();
    if(isset($_GET['page']) == 'wproject-clients-pro' || $screen->id === 'user-edit') {
        wp_enqueue_style( 'clients_admin_css', plugins_url('/css/clients-admin.css', __FILE__) );
    }
}
add_action( 'admin_enqueue_scripts', 'clients_enqueue' );


/* Enqueue CSS and Scripts to front-end */
function enqueue_clients_pro_front_end() {

    if(empty($_GET['print'])) {
        wp_enqueue_style('clients_pro_css', plugins_url('/css/clients-pro.css', __FILE__));
    }
    //wp_enqueue_script('clients-pro', plugins_url( '/js/clients-pro.js' , __FILE__ ), array('jquery'));
}
add_action('wp_enqueue_scripts','enqueue_clients_pro_front_end');

/* Add CSS and Scripts to front-end */
function clients_pro_front_end() {
    echo '<script type="text/javascript" src=""></script>';
}
add_action( 'wp_head', 'clients_pro_front_end' );

/* Client Pro Options */
function wProject_client() {

	$options                            = get_option( 'wproject_settings' );

	/* Client Settings */
	$client_access_pages            	= isset($options['client_access_pages']) ? $options['client_access_pages'] : '';
    $client_access_team             	= isset($options['client_access_team']) ? $options['client_access_team'] : '';
    $client_use_kanban             	    = isset($options['client_use_kanban']) ? $options['client_use_kanban'] : '';
    $client_view_others_tasks           = isset($options['client_view_others_tasks']) ? $options['client_view_others_tasks'] : '';
    $client_create_own_tasks        	= isset($options['client_create_own_tasks']) ? $options['client_create_own_tasks'] : '';
    $client_can_assign_tasks            = isset($options['client_can_assign_tasks']) ? $options['client_can_assign_tasks'] : '';
    $client_view_reports            	= isset($options['client_view_reports']) ? $options['client_view_reports'] : '';
    $client_view_project_details        = isset($options['client_view_project_details']) ? $options['client_view_project_details'] : '';
    $client_comment_tasks               = isset($options['client_comment_tasks']) ? $options['client_comment_tasks'] : '';
    $client_comment_pages               = isset($options['client_comment_pages']) ? $options['client_comment_pages'] : '';
    $client_project_access          	= isset($options['client_project_access']) ? $options['client_project_access'] : '';
	$client_use_search          		= isset($options['client_use_search']) ? $options['client_use_search'] : '';
	
	$wprojectClientSettings = array(
	
		'client_access_pages'				=> $client_access_pages,
        'client_view_others_tasks'          => $client_view_others_tasks,
		'client_access_team'				=> $client_access_team,
        'client_use_kanban'				    => $client_use_kanban,
		'client_create_own_tasks'			=> $client_create_own_tasks,
		'client_can_assign_tasks'           => $client_can_assign_tasks,
		'client_view_reports'				=> $client_view_reports,
        'client_view_project_details'       => $client_view_project_details,
		'client_comment_tasks'              => $client_comment_tasks,
        'client_comment_pages'              => $client_comment_pages,
		'client_project_access'				=> $client_project_access,
		'client_access_pages'				=> $client_access_pages,
		'client_use_search'					=> $client_use_search

    );
	return $wprojectClientSettings;
	
	/*
		Template Usage:

		$wproject_client_settings = wProject_client();
        echo $wproject_client_settings['currency_symbol'];
	*/
}


/* Add projects list to user profile */
add_action( 'show_user_profile', 'client_projects_field' );
add_action( 'edit_user_profile', 'client_projects_field' );

function client_projects_field( $user ) {

    /* Get list of projects that are not archived */
    $projects = get_terms( 'project', array(
        'hide_empty' => false,
        'meta_query' => array(
            array(
                'key' => 'project_status',
                'value' => array( 'in-progress', 'complete', 'planning', 'proposed' ),
                'compare' => 'IN',
            ),
        ),
    ) );
    $client_projects = get_user_meta( $user->ID, 'client_projects', true );
    
    if ( ! is_array( $client_projects ) ) {
        $client_projects = array( $client_projects );
    }

    $wproject_client_settings       = wProject_client();
    $client_project_access          = $wproject_client_settings['client_project_access'];
    
    $first_name  = get_user_meta( $_GET['user_id'], 'first_name', true );
    $last_name   = get_user_meta( $_GET['user_id'], 'last_name', true );
    $full_name   = $first_name . ' ' . $last_name;

    $user_id = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : 0;
    $user_info = get_userdata( $user_id );
    $user_roles = isset( $user_info->roles ) ? $user_info->roles : array();

    //if ( in_array( 'client', $user_roles ) || in_array( 'team_member', $user_roles ) ) {
    if ( in_array( 'client', $user_roles ) && $client_project_access == 'specific') {

    ?>
    <h3 id="project-access"><?php _e( 'Project Access', 'wproject-clients-pro' ); ?></h3>
    <p><?php printf( __('%1$s can access the projects selected below.', 'wproject-clients-pro'), $full_name); ?></p>
    <table class="form-table client-projects-list">
        <tr>
            <td>
                <ul>
                <?php foreach ( $projects as $project ) {
                    $project_name   = $project->name;
                    $project_status = get_term_meta( $project->term_id, 'project_status', true );
                    $pm_user_id     = get_term_meta( $project->term_id, 'project_manager', true );
                    $pm_first_name  = get_user_meta( $pm_user_id, 'first_name', true );
                    $pm_last_name   = get_user_meta( $pm_user_id, 'last_name', true );
                    $pm_name        = $pm_first_name . ' ' . $pm_last_name;

                    if($project_status == 'in-progress') {
                        $the_project_status = __('In progress', 'wproject-clients-pro');
                    } else if($project_status == 'planning') {
                        $the_project_status = __('Planning', 'wproject-clients-pro');
                    } else if($project_status == 'proposed') {
                        $the_project_status = __('Proposed', 'wproject-clients-pro');
                    } else if($project_status == 'setting-up') {
                        $the_project_status = __('Setting up', 'wproject-clients-pro');
                    } else if($project_status == 'archived') {
                        $the_project_status = __('Archived', 'wproject-clients-pro');
                    } else if($project_status == 'cancelled') {
                        $the_project_status = __('Cancelled', 'wproject-clients-pro');
                    } else if($project_status == 'complete') {
                        $the_project_status = __('Complete', 'wproject-clients-pro');
                    } else if(!$project_status) {
                        $the_project_status = __('Proposed', 'wproject-clients-pro');
                    }

                    if ( in_array( $project->term_id, $client_projects ) ) {
                        $checked = 'checked="checked"';
                        $selected = 'selected';
                    } else {
                        $checked = '';
                        $selected = '';
                    }
                    
                    echo '<li class="'  . $selected . ' ' . $project_status . '"><input type="checkbox" name="client_projects[]" value="' . esc_attr( $project->term_id ) . '"';
                    echo $checked;
                    echo '><strong>' . esc_html( $project_name ) . '</strong><span class="status">' . esc_html( $the_project_status ) . '</span><span class="pm">' . __('PM:', 'wproject-clients-pro') . ' ' .  esc_html( $pm_name ) . '</span></li>';
                }
                ?>
                </ul>
            </td>
        </tr>
    </table>
    <script>
        jQuery(document).ready(function() {
            jQuery('.client-projects-list ul li input[type="checkbox"]').on('click', function() {
                if (jQuery(this).is(':checked')) {
                    jQuery(this).parent().addClass('selected');
                } else {
                    jQuery(this).parent().removeClass('selected');
                }
            });
        });
    </script>
    <?php } ?>
    <?php
}

/* Save user meta when profile is saved */
add_action( 'personal_options_update', 'save_client_projects' );
add_action( 'edit_user_profile_update', 'save_client_projects' );
function save_client_projects( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return;
    }

    /* Save checked projects */
    $client_projects = isset( $_POST['client_projects'] ) ? array_map( 'absint', $_POST['client_projects'] ) : array();
    update_user_meta( $user_id, 'client_projects', $client_projects );
}

/* Navigation for 'specific' projects */
function specific_projects_list() {
    $user_id = get_current_user_id();

    echo '<ul class="projects-list">';

    /* Get list of projects */
    $projects = get_terms( 'project', array(
        'hide_empty' => false,
        'meta_query' => array(
            array(
                'key' => 'project_status',
                'value' => array( 'in-progress', 'complete', 'planning', 'proposed' ),
                'compare' => 'IN',
            ),
        ),
    ) );
    $client_projects = get_user_meta( $user_id, 'client_projects', true );
    
    if ( ! is_array( $client_projects ) ) {
        $client_projects = array( $client_projects );
    }

    foreach ( $projects as $project ) {
        if ( in_array( $project->term_id, $client_projects ) ) {
            $project_name = $project->name;        
            echo '<li id="project-' . $project->term_id . '" data="' . $project->term_id . '"><a href="' . get_category_link( $project->term_id ) . '" title="' . $project_name . '">' . $project_name . '</a></li>';
        }
    }
    echo '</ul>';
}