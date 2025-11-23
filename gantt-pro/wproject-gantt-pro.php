<?php
/*
Plugin Name:    Gantt Pro for wProject
Plugin URI:     https://rocketapps.com.au/product/gantt-pro/
Description:    Advanced Gantt chart with drag and drop interface.
Version:        1.3.9
Author: 		Rocket Apps
Author URI: 	https://rocketapps.com.au
Text Domain: 	wproject-gantt-pro
Author Email:   support@rocketapps.com.au
Domain Path:    /languages/
*/

/* Look for translation file. */
function wp_gantt_pro_textdomain() {
    load_plugin_textdomain( 'wproject-gantt-pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wp_gantt_pro_textdomain' );
update_option('gantt_pro_key', '************'); 
/* Remove old Gantt interface from admin */
function remove_old_gantt() { ?>
    <script>
        jQuery('.settings-nav #gantt, .settings-pane .settings-div.gantt').remove();
    </script>
<?php }
add_action('wproject_admin_settings_div_end', 'remove_old_gantt');


/* Constants */
define('GANTT_PRO_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('GANTT_PRO_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('GANTT_PRO_SPECIAL_KEY', '563931a7aaebe3.67050558');
define('GANTT_PRO_LICENSE_SERVER_URL', 'https://rocketapps.com.au');
define('GANTT_PRO_ITEM_REFERENCE', 'Gantt Pro'); 

/* Update checker */
function gantt_pro_update_checker() {
	$license_key = get_option('gantt_pro_key');
	$api_params = array(
		'slm_action'        => 'slm_check',
		'secret_key'        => GANTT_PRO_SPECIAL_KEY,
		'license_key'       => $license_key,
		'registered_domain' => $_SERVER['SERVER_NAME'],
		'item_reference'    => urlencode(GANTT_PRO_ITEM_REFERENCE),
	);

	/* Send query to the license manager server */
	$query = esc_url_raw(add_query_arg($api_params, GANTT_PRO_LICENSE_SERVER_URL));
	$response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

	/* Check for error in the response */
	if (is_wp_error($response)) { 
		_e('Unexpected Error! The query returned with an error.', 'gantt-pro');
	}

	/* License data */
	$license_data = json_decode(wp_remote_retrieve_body($response));

	if (isset($license_data->status) == 'active') {
		require 'plugin-update-checker/plugin-update-checker.php';
		$RPUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://rocketapps.com.au/files/wproject/gantt-pro/info.json',
			__FILE__,
			'gantt-pro'
		);
	}
}
/* 
	Only allow update check function within the following pages.
	This is to avoid constant license check requests being sent to server.
*/
$update_pages = array('/wp-admin/update-core.php',
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
if (in_array($_SERVER['SCRIPT_NAME'], $update_pages) && !empty(get_option('gantt_pro_key')) && is_admin()) {
    gantt_pro_update_checker();
}


/* Add Gantt Pro settings page to menu */
function add_gantt_pro_settings_page() {

	$wproject_capability = apply_filters( 'wproject_required_capabilities', 'manage_options' );

	$icon_url = plugins_url('/images/admin-icon.svg', __FILE__);

    add_menu_page( __( 'Gantt Pro','wproject-gantt-pro'), __( 'Gantt Pro','wproject-gantt-pro' ), 'manage_options', 'wproject-gantt-pro', 'gantt_pro_license_page' ,$icon_url, 32);

	do_action( 'gantt_pro_menu_items', 'wproject-license', $wproject_capability );
}
add_action( 'admin_menu', 'add_gantt_pro_settings_page' );

function gantt_pro_license_page() {
	require_once('gantt-pro-license.php');
}

/* Add pro Gantt interface menu item into admin */
function add_new_gantt_nav_item() { ?>
    <li data="gantt-pro" id="gantt-pro" <?php if(isset($_GET['section']) && $_GET['section'] == 'gantt-pro') { echo 'class="selected"'; } ?>><img src="<?php echo plugins_url('/images/icon.svg', __FILE__);?>" /><?php _e( 'Gantt Pro', 'wproject-gantt-pro' ); ?></li>
<?php }
add_action('wproject_admin_pro_nav_start', 'add_new_gantt_nav_item', 7);


/* wProject theme version check */
function wproject_theme_version_check_gantt() {
    $wproject_theme = wp_get_theme();
    $theme_version  = $wproject_theme->get( 'Version' );
    return $theme_version;
}

/* Get plugin version */
function ganttPluginVersion() {
    $plugin_data = get_plugin_data( __FILE__ );
    $plugin_version = $plugin_data['Version'];
    return $plugin_version;
}


/* Add pro Gantt settings div into admin */
function add_new_gantt_settings() {

    $options                        = get_option( 'wproject_settings' );
	$wproject_objects               = isset($options['objects'] ) ? $options['objects'] : array();

    /* Gantt options */
    $gantt_show_dashboard           = isset($options['gantt_show_dashboard']) ? $options['gantt_show_dashboard'] : '';
    $gantt_show_project             = isset($options['gantt_show_project']) ? $options['gantt_show_project'] : '';
    $gantt_show_all_project_page    = isset($options['gantt_show_all_project_page']) ? $options['gantt_show_all_project_page'] : '';
    $gantt_scale_tasks              = isset($options['gantt_scale_tasks']) ? $options['gantt_scale_tasks'] : '';
    $gantt_scale_projects           = isset($options['gantt_scale_projects']) ? $options['gantt_scale_projects'] : '';
    $gantt_order                    = isset($options['gantt_order']) ? $options['gantt_order'] : '';
    $gantt_show_subtasks            = isset($options['gantt_show_subtasks']) ? $options['gantt_show_subtasks'] : '';
    $gantt_show_project_task_list   = isset($options['gantt_show_project_task_list']) ? $options['gantt_show_project_task_list'] : '';
    $gantt_max_height               = isset($options['gantt_max_height']) ? $options['gantt_max_height'] : '';
    $gantt_popup_trigger            = isset($options['gantt_popup_trigger']) ? $options['gantt_popup_trigger'] : '';
    $gantt_bar_height               = isset($options['gantt_bar_height']) ? $options['gantt_bar_height'] : '';
    $gantt_bar_spacing              = isset($options['gantt_bar_spacing']) ? $options['gantt_bar_spacing'] : '';
    $primary_bar_colour             = isset($options['primary_bar_colour']) ? $options['primary_bar_colour'] : '';
    $secondary_bar_colour           = isset($options['secondary_bar_colour']) ? $options['secondary_bar_colour'] : '';
    $gantt_pro_key                  = get_option('gantt_pro_key');

    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'gantt-handle', plugins_url('js/gantt-pro-admin.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
    
    if(get_option('wproject_key')) {
        $button = '<input name="submit" class="button" value="' . __( 'Save Settings', 'wproject-gantt-pro' ) . '" type="submit" />';
    } else {
        $button = '<a href="' . admin_url() . 'admin.php?page=wproject-license" class="button warn">'. __( 'Activate License Key', 'wproject-gantt-pro' ) .'</a>';
    }
?>

    <!--/ Start Gantt Pro / -->
    <div class="settings-div gantt-pro">

    <h3><?php _e( 'Gantt Pro', 'wproject-gantt-pro' ); ?> <span>v<?php echo ganttPluginVersion(); ?></span></h3>
    <?php if(!$gantt_pro_key) { ?>
        <h4 class="warning"><?php printf( __('Please <a href="%1$s" rel="noopener">activate your license key</a> to unlock Gantt Pro.', 'wproject-gantt-pro'), admin_url() . 'admin.php?page=wproject-gantt-pro'); ?></h4>
    <?php } ?>

    <!--/ Start Version Check Notice / -->
    <?php 
        $required_theme_version = '4.6.2';
        $update_link = admin_url() . 'themes.php?theme=wproject';
        if (version_compare(wproject_theme_version_check_gantt(), $required_theme_version) < 0) { ?>
            <div class="wproject-notice">
                <strong><?php printf( __('wProject Gantt Pro requires at least wProject %1$s. <a href="%2$s" rel="noopener">Update now your theme now</a>.', 'wproject-gantt-pro'), $required_theme_version, $update_link); ?></strong>
            </div>
    <?php } ?>
    <!--/ End Version Check Notice / -->

    <!--/ Start Dashboard / -->
    <div class="wproject-plugin-dashboard">
        <h2><?php _e( 'Gantt Pro', 'wproject-gantt-pro' ); ?></h2>
        <a href="<?php echo admin_url(); ?>plugins.php?s=gantt+pro" class="wproject-button wproject-update-button"><?php _e( 'Check for update', 'wproject-reports-pro' ); ?></a>
        <a href="https://rocketapps.com.au/product/gantt-pro/#changelog" target="_blank" class="wproject-button"><?php _e( "What's new?", 'wproject-gantt-pro' ); ?></a>
        <a href="https://rocketapps.com.au/product/gantt-pro/" target="_blank" rel="noopener noreferrer" class="wproject-button"><?php _e( "Read the FAQ", 'wproject-gantt-pro' ); ?></a>
        <a href="https://rocketapps.com.au/log-ticket" target="_blank" rel="noopener noreferrer" class="wproject-button"><?php _e( "Support", 'wproject-gantt-pro' ); ?></a>
    </div>
    <!--/ End Dashboard / -->

    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Show the Gantt chart', 'wproject-gantt-pro' ); ?>
            <span><?php _e( 'Show the Gantt chart at these locations.', 'wproject-gantt-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="checkbox" name="wproject_settings[gantt_show_dashboard]" <?php if ( $gantt_show_dashboard ) { ?>checked<?php } ?> /> <?php _e( 'Dashboard', 'wproject-gantt-pro' ); ?>
            </li>
            <li>
                <input type="checkbox" name="wproject_settings[gantt_show_project]" <?php if ( $gantt_show_project ) { ?>checked<?php } ?> /> <?php _e( 'Individual projects', 'wproject-gantt-pro' ); ?>
            </li>
            <li>
                <input type="checkbox" name="wproject_settings[gantt_show_all_project_page]" <?php if ( $gantt_show_all_project_page ) { ?>checked<?php } ?> /> <?php _e( 'Projects page', 'wproject-gantt-pro' ); ?>
            </li>
        </ul>
    </div>

    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Initial Gantt scale (tasks)', 'wproject-gantt-pro' ); ?>
            <span><?php _e( 'The initial scale when the Gantt chart contains tasks.', 'wproject-gantt-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="radio" name="wproject_settings[gantt_scale_tasks]" value="Day" <?php if ( $gantt_scale_tasks == 'Day' ) { ?>checked<?php } ?> /> <?php _e( 'Days', 'wproject-gantt-pro' ); ?>   
            </li>
            <li>
                <input type="radio" name="wproject_settings[gantt_scale_tasks]" value="Week" <?php if ( $gantt_scale_tasks == 'Week' ) { ?>checked<?php } ?> /> <?php _e( 'Weeks', 'wproject-gantt-pro' ); ?> 
            </li>
            <li>
                <input type="radio" name="wproject_settings[gantt_scale_tasks]" value="Month" <?php if ( $gantt_scale_tasks == 'Month' ) { ?>checked<?php } ?> /> <?php _e( 'Months', 'wproject-gantt-pro' ); ?> 
            </li>
        </ul>
    </div>

    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Initial Gantt scale (projects)', 'wproject-gantt-pro' ); ?>
            <span><?php _e( 'The initial scale when the Gantt chart contains projects.', 'wproject-gantt-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="radio" name="wproject_settings[gantt_scale_projects]" value="Day" <?php if ( $gantt_scale_projects == 'Day' ) { ?>checked<?php } ?> /> <?php _e( 'Days', 'wproject-gantt-pro' ); ?>   
            </li>
            <li>
                <input type="radio" name="wproject_settings[gantt_scale_projects]" value="Week" <?php if ( $gantt_scale_projects == 'Week' ) { ?>checked<?php } ?> /> <?php _e( 'Weeks', 'wproject-gantt-pro' ); ?> 
            </li>
            <li>
                <input type="radio" name="wproject_settings[gantt_scale_projects]" value="Month" <?php if ( $gantt_scale_projects == 'Month' ) { ?>checked<?php } ?> /> <?php _e( 'Months', 'wproject-gantt-pro' ); ?> 
            </li>
        </ul>
    </div>

    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Order', 'wproject-gantt-pro' ); ?>
            <span><?php _e( 'The order to return tasks and projects.', 'wproject-gantt-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="radio" name="wproject_settings[gantt_order]" value="title" <?php if ( $gantt_order == 'title' ) { ?>checked<?php } ?> /> <?php _e( 'Alphabetical (default)', 'wproject-gantt-pro' ); ?>   
            </li>
            <li>
                <input type="radio" name="wproject_settings[gantt_order]" value="chronological" <?php if ( $gantt_order == 'chronological' ) { ?>checked<?php } ?> /> <?php _e( 'Chronological', 'wproject-gantt-pro' ); ?> 
            </li>
        </ul>
    </div>

    <hr />


    <div class="fleft">
        <p>
            <?php _e( 'Popup trigger', 'wproject-gantt-pro' ); ?>
            <span>
                <?php _e( 'How popups are triggered in the Gantt chart.', 'wproject-gantt-pro' ); ?>
                <br />
                <br />
                <strong><?php _e( 'Note', 'wproject-gantt-pro' ); ?>:</strong>
                <?php _e( 'Clients will not see popups regardless of this setting.', 'wproject-gantt-pro' ); ?>
            </span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="radio" name="wproject_settings[gantt_popup_trigger]" value="mouseover" <?php if ( $gantt_popup_trigger == 'mouseover' ) { ?>checked<?php } ?> /> <?php _e( 'On mouse over (default)', 'wproject-gantt-pro' ); ?>   
            </li>
            <li>
                <input type="radio" name="wproject_settings[gantt_popup_trigger]" value="disabled" <?php if ( $gantt_popup_trigger == 'disabled' ) { ?>checked<?php } ?> /> <?php _e( 'Disabled', 'wproject-gantt-pro' ); ?> 
            </li>
        </ul>
    </div>

    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Show subtasks', 'wproject-gantt-pro' ); ?>
            <span><?php _e( 'Show subtasks below the main tasks.', 'wproject-gantt-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="checkbox" name="wproject_settings[gantt_show_subtasks]" <?php if ( $gantt_show_subtasks ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject-gantt-pro' ); ?>
            </li>
        </ul>
    </div>

    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Show project / task list', 'wproject-gantt-pro' ); ?>
            <span><?php _e( 'Show the project / task names docked to the left of the Gantt chart.', 'wproject-gantt-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="checkbox" name="wproject_settings[gantt_show_project_task_list]" <?php if ( $gantt_show_project_task_list ) { ?>checked<?php } ?> /> <?php _e( 'Enable', 'wproject-gantt-pro' ); ?>
            </li>
        </ul>
    </div>

    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Bar height', 'wproject-gantt-pro' ); ?>
            <span><?php _e( 'The height of the Gantt bars. Default is 20.', 'wproject-gantt-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="number" name="wproject_settings[gantt_bar_height]" min="14" max="50" <?php if ( ! empty( $gantt_bar_height ) ) { echo 'value="' . $gantt_bar_height . '"'; } ?> placeholder="20" />
            </li>
        </ul>
    </div>

    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Bar spacing', 'wproject-gantt-pro' ); ?>
            <span><?php _e( 'The spacing between Gantt bars. Default is 10.', 'wproject-gantt-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="number" name="wproject_settings[gantt_bar_spacing]" min="6" max="30" <?php if ( ! empty( $gantt_bar_spacing ) ) { echo 'value="' . $gantt_bar_spacing . '"'; } ?> placeholder="20" />
            </li>
        </ul>
    </div>

    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Primary bar colour', 'wproject-gantt-pro' ); ?>
            <span><?php _e( 'The colour of the primary Gantt bars. If not specified, the priority colour will be displayed.', 'wproject-gantt-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="text" name="wproject_settings[primary_bar_colour]" <?php if ( ! empty( $primary_bar_colour ) ) { echo 'value="' . $primary_bar_colour . '"'; } ?> class="colour-picker"  />
            </li>
        </ul>
    </div>

    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Secondary bar colour', 'wproject-gantt-pro' ); ?>
            <span><?php _e( 'The colour of the secondary Gantt bars.', 'wproject-gantt-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="text" name="wproject_settings[secondary_bar_colour]" <?php if ( ! empty( $secondary_bar_colour ) ) { echo 'value="' . $secondary_bar_colour . '"'; } ?> class="colour-picker"  />
            </li>
        </ul>
    </div>

    <hr />

    <?php echo $button; ?>

    <?php if(!$gantt_pro_key) { ?>
    <script>
        jQuery('.gantt-pro input[type="submit"]').remove();
        jQuery('.gantt-pro input, .wp-picker-container').attr('disabled', 'disabled');
    </script>
    <?php } ?>

    <script>
        jQuery( document ).ready(function() {
            <?php $icon = isset($_GET['section']) ? $_GET['section'] : ''; ?>
            <?php if($icon && $icon == 'gantt-pro') { ?>
                jQuery('.settings-div h3 span img').attr('src', '<?php echo plugins_url('/images/admin-icon.svg', __FILE__) ?>');
                jQuery('.settings-div h3 span:first-child').removeClass('invert').css('margin', '0').css('opacity', '1');
            <?php } ?>
        });
    </script>
    
    </div>
    <!--/ End Gantt Pro / -->

<?php }
add_action('wproject_admin_settings_div_end', 'add_new_gantt_settings');

/* Check circle icon */
function check_circle() {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#8bc34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle-2 feather-icon" color="#ff9800"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="m9 12 2 2 4-4"></path></svg>';
}


/* Gantt pro scripts */
function gantt_pro_scripts() {
    wp_enqueue_style('gantt_pro_css', plugins_url('/gantt/frappe-gantt.css', __FILE__));
    wp_enqueue_script('svg_snap', plugins_url( '/js/snap.svg-min.js' , __FILE__ ), array('jquery'));
    wp_enqueue_script('grappe_gantt', plugins_url( '/gantt/frappe-gantt.min.js' , __FILE__ ), array('jquery'));
    wp_enqueue_script('gantt-pro_js', plugins_url( '/js/gantt-pro.min.js' , __FILE__ ), array(), false, true );

    $dark_mode	= isset(user_details()['dark_mode']) ? user_details()['dark_mode'] : '';

	if($dark_mode == 'yes') {
		wp_enqueue_style('gantt_pro_css_dark', plugins_url('/gantt/frappe-gantt-dark.css', __FILE__));
	}
}
add_action('wp_enqueue_scripts','gantt_pro_scripts');

/* Admin CSS */
function my_enqueue($hook) {
    if(isset($_GET['page']) == 'wproject-gantt-pro') {
        wp_enqueue_style( 'gantt_pro_admin_css', plugins_url('/css/gantt-pro-admin.css', __FILE__) );
    }
}
add_action( 'admin_enqueue_scripts', 'my_enqueue' );


/* Output Gantt Pro on a Project page */
function gantt_pro_project() { 

    $options            = get_option( 'wproject_settings' );
	$wproject_objects   = isset($options['objects'] ) ? $options['objects'] : array();
    $avatar_style       = isset($options['avatar_style']) ? $options['avatar_style'] : '';
    $dark_mode          = isset(user_details()['dark_mode']) ? user_details()['dark_mode'] : '';

    /* User options */
    $current_author     = get_current_user_id();
    $hide_gantt         = get_user_meta( $current_author, 'hide_gantt' , true );

    if(empty($avatar_style)) {
        $the_avatar_style = 'rounded-corners';
    } else {
        $the_avatar_style = $avatar_style;
    }

    /* Gantt options */
    $gantt_show_dashboard           = isset($options['gantt_show_dashboard']) ? $options['gantt_show_dashboard'] : '';
    $gantt_show_project             = isset($options['gantt_show_project']) ? $options['gantt_show_project'] : '';
    $gantt_show_all_project_page    = isset($options['gantt_show_all_project_page']) ? $options['gantt_show_all_project_page'] : '';
    $gantt_scale_tasks              = isset($options['gantt_scale_tasks']) ? $options['gantt_scale_tasks'] : '';
    $gantt_scale_projects           = isset($options['gantt_scale_projects']) ? $options['gantt_scale_projects'] : '';
    $gantt_order                    = isset($options['gantt_order']) ? $options['gantt_order'] : '';
    $gantt_show_subtasks            = isset($options['gantt_show_subtasks']) ? $options['gantt_show_subtasks'] : '';
    $gantt_show_project_task_list   = isset($options['gantt_show_project_task_list']) ? $options['gantt_show_project_task_list'] : '';
    $gantt_max_height               = isset($options['gantt_max_height']) ? $options['gantt_max_height'] : '';
    $gantt_popup_trigger            = isset($options['gantt_popup_trigger']) ? $options['gantt_popup_trigger'] : '';
    $gantt_bar_height               = isset($options['gantt_bar_height']) ? $options['gantt_bar_height'] : '';
    $gantt_bar_spacing              = isset($options['gantt_bar_spacing']) ? $options['gantt_bar_spacing'] : '';
    $secondary_bar_colour           = isset($options['secondary_bar_colour']) ? $options['secondary_bar_colour'] : '';
    $primary_bar_colour             = isset($options['primary_bar_colour']) ? $options['primary_bar_colour'] : '';
    $message                        = __( 'The Gantt chart was modified. Please refresh this page to see the latest Kanban board.', 'wproject-gantt-pro' );
    $alert_icon                     = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
    
    if($secondary_bar_colour) {
        $secondary_bar_colour = $secondary_bar_colour;
    } else {
        $secondary_bar_colour = '#747faf';
    }

	if($dark_mode == 'yes') {
		$secondary_bar_colour = '#2a2841';
    }

    if($gantt_bar_spacing) {
        $gantt_bar_spacing = $gantt_bar_spacing;
    } else {
        $gantt_bar_spacing = 10;
    }

    if($gantt_bar_height) {
        $gantt_bar_height = $gantt_bar_height;
    } else {
        $gantt_bar_height = 16;
    }

    if($gantt_scale_tasks) {
        $gantt_scale_tasks = $gantt_scale_tasks;
        $step = '7';
    } else {
        $gantt_scale_tasks = 'Day';
        $step = '7';
    }

    if($gantt_popup_trigger) {
        $gantt_popup_trigger = $gantt_popup_trigger;
    } else {
        $gantt_popup_trigger = 'mouseover';
    }

    $user   = get_userdata(get_current_user_id());
    $role   = $user->roles[0];

    if($hide_gantt !='yes' && $gantt_show_project == 'on' && empty($_GET['print'])) {

?>

    <!--/ Start Gantt Pro Form /-->
    <form class="update-gantt-pro-form <?php if($gantt_show_project_task_list == 'on') { echo 'project-task-list-on'; } ?>" method="post" id="update-gantt-pro-form">

        <div class="btn-group" role="group">
            <button type="button" class="btn btn-light <?php if($gantt_scale_tasks == 'Day') { echo 'active'; } ?>" id="Day"><?php _e( 'Day', 'wproject-gantt-pro' ); ?></button>
            <button type="button" class="btn btn-light <?php if($gantt_scale_tasks == 'Week') { echo 'active'; } ?>" id="Week"><?php _e( 'Week', 'wproject-gantt-pro' ); ?></button>
            <button type="button" class="btn btn-light <?php if($gantt_scale_tasks == 'Month') { echo 'active'; } ?>" id="Month"><?php _e( 'Month', 'wproject-gantt-pro' ); ?></button>
            <button type="button" class="btn btn-light" id="Year"><?php _e( 'Year', 'wproject-gantt-pro' ); ?></button>
            <span class="btn btn-light show-gantt">
                <i data-feather="maximize"></i>
            </span>
            <span class="btn btn-light hide-gantt">
                <i data-feather="eye-off"></i>
            </span>
        </div>

        <div class="gantt-target">
            <h1><?php echo single_cat_title(); ?></h1>
        </div>
        
        <input type="hidden" name="task_id" id="gantt_task_id" />
        <input type="hidden" name="task_start_date" id="task_start_date" />
        <input type="hidden" name="task_end_date" id="task_end_date" />
        <input type="hidden" name="task_pc_complete" id="task_pc_complete" />
        <input type="hidden" name="task_name" id="task_name" />
        <input type="hidden" name="gantt_mode" id="gantt_mode" />
        <input type="hidden" name="gantt_type" value="tasks" />

        <div class="gantt-pro-mask"><img src="<?php echo get_template_directory_uri();?>/images/spinner.svg" /></div>

        <style>
            .gantt .bar {
                fill: <?php echo $secondary_bar_colour; ?> !important;
            }
            .gantt .bar-progress {
                fill: <?php echo $primary_bar_colour; ?>
            }
        </style>

    <script>
        
        const month_names = {
            en: [
                '<?php _e( "January", "wproject-gantt-pro" ); ?>',
                '<?php _e( "February", "wproject-gantt-pro" ); ?>',
                '<?php _e( "March", "wproject-gantt-pro" ); ?>',
                '<?php _e( "April", "wproject-gantt-pro" ); ?>',
                '<?php _e( "May", "wproject-gantt-pro" ); ?>',
                '<?php _e( "June", "wproject-gantt-pro" ); ?>',
                '<?php _e( "July", "wproject-gantt-pro" ); ?>',
                '<?php _e( "August", "wproject-gantt-pro" ); ?>',
                '<?php _e( "September", "wproject-gantt-pro" ); ?>',
                '<?php _e( "October", "wproject-gantt-pro" ); ?>',
                '<?php _e( "November", "wproject-gantt-pro" ); ?>',
                '<?php _e( "December", "wproject-gantt-pro" ); ?>'
            ]
        };
		var tasks = [

            <?php 
            $current_author             = get_current_user_id();
            $date_format                = get_option('date_format'); /* WordPress date format */
            $term_id		            = get_queried_object()->term_id; 
            $term_meta                  = get_term_meta($term_id); 
            $term_object 	            = get_term( $term_id ); 
            $current_term               = get_term_by( 'id', $term_id, 'project' );
            $description                = $current_term->description;
            $project_full_description   = $term_meta['project_full_description'][0];

            /*
                PHP date format to moment js date format
                https://stackoverflow.com/a/30192680/3256143
            */
            function convertPHPToMomentFormat($date_format) {
                $replacements = [
                    'd' => 'DD',
                    'D' => 'ddd',
                    'j' => 'D',
                    'l' => 'dddd',
                    'N' => 'E',
                    'S' => 'o',
                    'w' => 'e',
                    'z' => 'DDD',
                    'W' => 'W',
                    'F' => 'MMMM',
                    'm' => 'MM',
                    'M' => 'MMM',
                    'n' => 'M',
                    't' => '', // no equivalent
                    'L' => '', // no equivalent
                    'o' => 'YYYY',
                    'Y' => 'YYYY',
                    'y' => 'YY',
                    'a' => 'a',
                    'A' => 'A',
                    'B' => '', // no equivalent
                    'g' => 'h',
                    'G' => 'H',
                    'h' => 'hh',
                    'H' => 'HH',
                    'i' => 'mm',
                    's' => 'ss',
                    'u' => 'SSS',
                    'e' => 'zz', // deprecated since version 1.6.0 of moment.js
                    'I' => '', // no equivalent
                    'O' => '', // no equivalent
                    'P' => '', // no equivalent
                    'T' => '', // no equivalent
                    'Z' => '', // no equivalent
                    'c' => '', // no equivalent
                    'r' => '', // no equivalent
                    'U' => 'X',
                ];
                $momentFormat = strtr($date_format, $replacements);
                return $momentFormat;
            }

            /* Query based on Sort setting */
            if($gantt_order == 'title') {

                $args = array(
                    'post_type'         => 'task',
                    'orderby'           => 'title',
                    'order'             => 'asc',
                    'posts_per_page'    => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'project',
                            'field'    => 'slug',
                            'terms'    => array( $term_object->slug ),
                            'operator' => 'IN'
                        ),
                    ),
                );

            } else if($gantt_order == 'chronological' || $gantt_order == '') {

                $args = array(
                    'post_type'         => 'task',
                    'orderby'           => 'task_start_date',
                    'order'             => 'asc',
                    'meta_query' => array(
                        array(
                           'key'        => 'task_start_date',
                           'compare'    => '==',
                           'type'       => 'DATE'
                       )
                    ),
                    'posts_per_page'    => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'project',
                            'field'    => 'slug',
                            'terms'    => array( $term_object->slug ),
                            'operator' => 'IN'
                        ),
                    ),
                );

            }

            $query = new WP_Query($args);
            $gantt_task_count = $query->post_count;
            
            while ($query->have_posts()) : $query->the_post();

            $task_id                = get_the_id();
            $author_id              = get_post_field ('post_author', $task_id);
            $task_start_date        = get_post_meta($task_id, 'task_start_date', TRUE);
            $task_end_date          = get_post_meta($task_id, 'task_end_date', TRUE);
            $task_priority          = get_post_meta($task_id, 'task_priority', TRUE);
            $task_status            = get_post_meta($task_id, 'task_status', TRUE);
            $task_time              = get_post_meta($task_id, 'task_time', TRUE);
            $task_job_number        = get_post_meta($task_id, 'task_job_number', TRUE);
            $task_private           = get_post_meta($task_id, 'task_private', TRUE);
            $task_description       = get_post_meta($task_id, 'task_description', TRUE);
            $subtask_list           = get_post_meta($task_id, 'subtask_list', TRUE);
            $task_pc_complete       = get_post_meta($task_id, 'task_pc_complete', TRUE);
            $user_photo             = get_the_author_meta( 'user_photo', $author_id );

            if($primary_bar_colour) {
                $task_priority = 'primary-colour';
            } else {
                $task_priority = $task_priority;
            }

            if($user_photo) {
                $avatar         = $user_photo;
                $avatar_id      = attachment_url_to_postid($avatar);
                $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                $avatar         = $small_avatar[0];
            } else {
                $avatar = get_template_directory_uri() . '/images/default-user.png';
            }

            if($task_priority) {
                $task_priority = $task_priority;
            } else {
                $task_priority = 'normal';
            }

            if($task_pc_complete) {
                $task_pc_complete = $task_pc_complete;
            } else if($task_pc_complete == '100') {
                $task_pc_complete = 100;
            } else {
                $task_pc_complete = 0;
            }

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
            } else {
                $task_priority_name = /* translators: One of 4 possible task priorities */ __('Normal', 'wproject');
            }
            
            $task_owner = get_the_author_meta('user_firstname',$author_id) . " " . get_the_author_meta('user_lastname',$author_id);

            if($task_start_date && $task_end_date) { ?>
            {
				start: '<?php echo $task_start_date; ?>',
				end: '<?php echo $task_end_date; ?>',
				name: '<?php if($gantt_popup_trigger != 'disabled') { ?><img src="<?php echo $avatar; ?>" class="avatar <?php echo $the_avatar_style; ?>" /><?php } ?><?php echo esc_html__(get_the_title($task_id)); ?>',
				id: "<?php echo $task_id; ?>",
				progress: <?php echo $task_pc_complete; ?>,
                custom_class: '<?php echo $task_priority; ?>',
                link: "<?php echo get_the_permalink($task_id); ?>",
                edit: "<?php echo home_url(); ?>/edit-task/?task-id=<?php echo $task_id; ?>",
                priority: "<?php echo $task_priority_name; ?>"
            },
                <?php if($gantt_show_subtasks) {
                if($subtask_list) { 
                    $subtask_rows = get_post_meta( (int)$task_id, 'subtask_list', true); 
                    if($subtask_rows) { 
                        if( count($subtask_rows ) > 0  ) { 
                        sort($subtask_rows); 
                        foreach( $subtask_rows as $subtask ) { 
                        if($subtask['subtask_status'] != '1') { ?>

                            {
                                start: '<?php echo $task_start_date; ?>',
                                end: '<?php echo $task_end_date; ?>',
                                name: '<?php echo addslashes($subtask['subtask_name']); ?>',
                                progress: 0,
                                id: "<?php echo $task_id; ?>",
                                dependencies: '<?php echo $task_id; ?>',
                                custom_class: 'subtask',
                            },
                        
                <?php } } } } } } ?>

            <?php }
            endwhile;
            wp_reset_postdata();
            ?>
            
		]
		var gantt_chart = new Gantt(".gantt-target", tasks.length ? tasks : [{}], {
            
			on_click: function (task) {
				//console.log(task.name);
			},
            /* When dragging the task from the middle */
			on_date_change: function(task) {
				//console.log(task, start, end);
                $('.gantt-pro-mask').addClass('show');
                
                const start_date = moment(task._start).format('YYYY-MM-DD');
                const end_date = moment(task._end).format('YYYY-MM-DD');

                $('#gantt_task_id').attr('value', task.id);
                $('#task_start_date').attr('value', start_date);
                $('#task_end_date').attr('value', end_date);
                $('#task_name').attr('value', task.name);

                //console.log('start date:  ' +moment(task._start).format('YYYY-MM-DD'));
                //console.log('end date:  ' +moment(task._end).format('YYYY-MM-DD'));
                //console.log(task.name+' task dates changed');

                $('.gantt-update-notice').remove();
                $('.kanban h1').after('<div class="gantt-update-notice"><?php echo $alert_icon; ?><?php echo $message; ?></div>');
                
                setTimeout(function() { 
                    $('#update-gantt-pro-form').submit();
                }, 250);

			},
            /* When dragging the task progress bar */
			on_progress_change: function(progress) {
				//console.log(task, progress);
                $('.gantt-pro-mask').addClass('show');

                const start_date = moment(progress._start).format('YYYY-MM-DD');
                const end_date = moment(progress._end).format('YYYY-MM-DD');

                $('#gantt_task_id').attr('value', progress.id);
                $('#task_start_date').attr('value', start_date);
                $('#task_end_date').attr('value', end_date);
                $('#task_name').attr('value', progress.name);
                $('#task_pc_complete').attr('value', progress.progress);
                
                var progress_value = progress.progress;
                if(progress_value == 100) {
                    $('#task-'+progress.id+' a').prepend('<?php echo check_circle(); ?>');
                } else if(progress_value < 100) {
                    $('#task-'+progress.id+' a svg').remove();
                }
                
                $('.gantt-update-notice').remove();
                $('.kanban h1').after('<div class="gantt-update-notice"><?php echo $alert_icon; ?><?php echo $message; ?></div>');

                //console.log('progress:  ' +progress);
                //console.log(progress.name+' progress changed');
                
                setTimeout(function() { 
                    $('#update-gantt-pro-form').submit();
                }, 250);
			},
			on_view_change: function(mode) {
				//console.log('mode ->  ' +mode);
                $('#gantt_mode').attr('value', mode);
			},
			language: 'en',
            header_height: 45,
            column_width: 1,
            step: <?php echo $step; ?>,
            //view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
            view_modes: ['<?php _e( 'Day', 'wproject-gantt-pro' ); ?>', '<?php _e( 'Week', 'wproject-gantt-pro' ); ?>', '<?php _e( 'Month', 'wproject-gantt-pro' ); ?>', '<?php _e( 'Year', 'wproject-gantt-pro' ); ?>'],
            view_mode: '<?php echo $gantt_scale_tasks; ?>',
            bar_height: <?php echo $gantt_bar_height; ?>,
            popup_trigger: '<?php echo $gantt_popup_trigger; ?>',
            padding: <?php echo $gantt_bar_spacing; ?>, 
            date_format: '<?php echo convertPHPToMomentFormat($date_format); ?>',
            <?php if($gantt_popup_trigger != 'disabled') { ?>
            custom_popup_html: function(task) {
                const start_date = moment(task._start).format('<?php echo convertPHPToMomentFormat($date_format); ?>');
                //const end_date = moment(task._end).format('<?php echo convertPHPToMomentFormat($date_format); ?>');
                /* Remove one day from end date because of Frappe Gantt bug */
                const end_date = moment(task._end).subtract(1, 'days').format('<?php echo convertPHPToMomentFormat($date_format); ?>');
                return `
                    <div class="details-container">
                        <p><strong><a href="${task.link}">${task.name}</a></strong></p>
                        <p><span><?php _e('Priority', 'wproject-gantt-pro'); ?>:</span> ${task.priority}</p>
                        <p><span><?php _e('Start', 'wproject-gantt-pro'); ?>:</span> ${start_date}</p>
                        <p><span><?php _e('Due', 'wproject-gantt-pro'); ?>:</span> ${end_date}</p>
                        <p><span><?php _e('Progress', 'wproject-gantt-pro'); ?>:</span> ${task.progress}%</p>
                        <p><span><?php _e('ID', 'wproject-gantt-pro'); ?>:</span> ${task.id}</p>
                        <a href="${task.link}" class="btn btn-light"><?php _e('Go to task', 'wproject-gantt-pro'); ?></a> 
                        <a href="${task.edit}" class="btn btn-light"><?php _e('Edit task', 'wproject-gantt-pro'); ?></a>
                    </div>
                `;
            }
            <?php } else { ?>
                custom_popup_html: function(task) {
                    return '';
                }
            <?php } ?>
		});

        /* If no tasks, remove the Gantt chart */
        if(tasks.length == 0) {
            $('#update-gantt-pro-form').remove();
        }

        /* Buttons */
        gantt_chart.change_view_mode('<?php echo $gantt_scale_tasks; ?>');
        $('.btn-group').on('click', 'button', function() {
            $btn = $(this);
            var mode = $btn.attr('id');
            gantt_chart.change_view_mode(mode);
            $btn.parent().find('button').removeClass('active');
            $btn.addClass('active');
            <?php if($role != 'project_manager' && $role != 'administrator') { ?>
            $('.gantt .bar-wrapper').css('pointer-events', 'none');
            <?php } ?>
        });
        
		//console.log(gantt_chart);

        $('.show-gantt').click(function() {
            $('.update-gantt-pro-form').toggleClass('full-screen');
            $('.gantt-target h1').addClass('show');
        });

        <?php if($role != 'project_manager' && $role != 'administrator') { ?>
            $('.gantt .bar-wrapper').css('pointer-events', 'none');
        <?php } ?>

	</script>
    
    <?php /* Task List (docked) */
    if($gantt_show_project_task_list == 'on' && !wp_is_mobile()) { ?>
    <ul class="gantt-task-list">
        <?php 

            /* Query based on Sort setting */
            if($gantt_order == 'title') {

                $item_args = array(
                    'post_type'         => 'task',
                    'orderby'           => 'title',
                    'order'             => 'asc',
                    'posts_per_page'    => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'project',
                            'field'    => 'slug',
                            'terms'    => array( $term_object->slug ),
                            'operator' => 'IN'
                        ),
                    ),
                );
            
            } else if($gantt_order == 'chronological' || $gantt_order == '') {

                $item_args = array(
                    'post_type'         => 'task',
                    'orderby'           => 'task_start_date',
                    'order'             => 'asc',
                    'meta_query' => array(
                        array(
                           'key'        => 'task_start_date',
                           'compare'    => '==',
                           'type'       => 'DATE'
                       )
                    ),
                    'posts_per_page'    => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'project',
                            'field'    => 'slug',
                            'terms'    => array( $term_object->slug ),
                            'operator' => 'IN'
                        ),
                    ),
                );

            }

            $query = new WP_Query($item_args);
            $gantt_task_count = $query->post_count;
            while ($query->have_posts()) : $query->the_post();

            $task_id            = get_the_id();
            $task_start_date    = get_post_meta($task_id, 'task_start_date', TRUE);
            $task_end_date      = get_post_meta($task_id, 'task_end_date', TRUE);
            $subtask_list       = get_post_meta($task_id, 'subtask_list', TRUE);
            $task_priority      = get_post_meta($task_id, 'task_priority', TRUE);
            $task_pc_complete   = get_post_meta($task_id, 'task_pc_complete', TRUE);

            if(!$task_priority) {
                $task_priority = 'low';
            }

            if($task_start_date && $task_end_date) { ?>
            <li class="<?php echo $task_priority; ?>" title="<?php echo the_title(); ?>" id="task-<?php echo $task_id; ?>">
            <a href="<?php echo get_the_permalink($task_id); ?>">
                <?php if($task_pc_complete == '100') { ?><i data-feather="check-circle-2"></i><?php } ?>
                <?php echo the_title(); ?></a>
            </li>
        <?php } 

            if($gantt_show_subtasks == 'on') {
                if($subtask_list) { 
                $subtask_rows = get_post_meta( (int)$task_id, 'subtask_list', true); 
                if($subtask_rows) { 
                    if( count($subtask_rows ) > 0 && $task_start_date && $task_end_date ) { 
                    sort($subtask_rows); 
                    foreach( $subtask_rows as $subtask ) { 
                    if($subtask['subtask_status'] != '1') { ?>

                        <li class="gantt-subtask-list" title="<?php echo $subtask['subtask_name']; ?>"><?php echo $subtask['subtask_name']; ?></li>
                    
            <?php } } } } } }

        endwhile;
        wp_reset_postdata();
            $gantt_list_item_height = $gantt_bar_height + $gantt_bar_spacing;
        } ?>
    </ul>
    <style>
        .gantt-task-list li {
            padding: <?php echo $gantt_bar_spacing; ?>px 20px;
            height: <?php echo $gantt_list_item_height; ?>px;
        }
        <?php if($gantt_show_project_task_list == 'on') { ?>
        .gantt-container {
            padding: 0 0 0 235px !important;
        }
        <?php } ?>
        
    </style>
    <script>
        //TODO: make .gantt-task-list draggable wider

        /* Link the scroll bars */
        $(function(){
            $('.linked').scroll(function(){
                $('.gantt-task-list').scrollTop($(this).scrollTop());    
            })
        })      
        $('.gantt-container').addClass('linked');

        /*
            Experimental Feature.
            TODO: Get values of the selected bar.
        
        $('.handle.progress').mousemove(function(e) {

            var bar_id          = $(this).attr('data-id');               
            var bar_progress    = $('.bar-progress').attr('width');
            var bar_width       = $('.bar-group .bar').attr('width');
            var bar_label       = $('.bar-label').text();
            console.log(Math.round(bar_progress / bar_width * 100)+'%');

        });
        */
        

    </script>
    <?php } ?>

    <script>
        /* Toggle Gantt visibility */
        function a() { 
            $('.gantt-target, .gantt-task-list, .btn-group button').fadeOut();
            $('header .icons').prepend('<li class="toggle-gantt-fs"><img src="<?php echo plugins_url('/images/icon.svg', __FILE__);?>" /></li>');
            $('.update-gantt-pro-form').fadeOut();
        }
        function b() { 
            $('.gantt-target, .gantt-task-list, .btn-group button').fadeIn();
            $('.toggle-gantt-fs').remove();
            $('.update-gantt-pro-form').fadeIn();
        }
        
        $(document).on('click', '.hide-gantt' , function() {
            return (this.tog = !this.tog) ? a() : a();
        });
        $(document).on('click', '.toggle-gantt-fs' , function() {
            return (this.tog = !this.tog) ? b() : b();
        });
    </script>
 
</form>
<!--/ End Gantt Pro Form /-->

<?php }
add_action( 'gantt_pro_project_page', 'gantt_pro_project' );



/* Output Gantt Pro Dashboard */
function gantt_pro_dashboard() { 

    $options 							= get_option( 'wproject_settings' );
	$wproject_objects					= isset($options['objects'] ) ? $options['objects'] : array();
    $avatar_style                       = isset($options['avatar_style']) ? $options['avatar_style'] : '';
    $dark_mode	                        = isset(user_details()['dark_mode']) ? user_details()['dark_mode'] : '';
    $date_format                        = get_option('date_format'); /* WordPress date format */

    /* User options */
    $current_author                     = get_current_user_id();
    $hide_gantt                         = get_user_meta( $current_author, 'hide_gantt' , true );

    if(empty($avatar_style)) {
        $the_avatar_style = 'rounded-corners';
    } else {
        $the_avatar_style = $avatar_style;
    }

    /* Gantt options */
    $gantt_show_dashboard           = isset($options['gantt_show_dashboard']) ? $options['gantt_show_dashboard'] : '';
    $gantt_show_all_project_page    = isset($options['gantt_show_all_project_page']) ? $options['gantt_show_all_project_page'] : '';
    $gantt_scale_tasks              = isset($options['gantt_scale_tasks']) ? $options['gantt_scale_tasks'] : '';
    $gantt_scale_projects           = isset($options['gantt_scale_projects']) ? $options['gantt_scale_projects'] : '';
    $gantt_show_subtasks            = isset($options['gantt_show_subtasks']) ? $options['gantt_show_subtasks'] : '';
    $gantt_order                    = isset($options['gantt_order']) ? $options['gantt_order'] : '';
    $gantt_show_project_task_list   = isset($options['gantt_show_project_task_list']) ? $options['gantt_show_project_task_list'] : '';
    $gantt_max_height               = isset($options['gantt_max_height']) ? $options['gantt_max_height'] : '';
    $gantt_popup_trigger            = isset($options['gantt_popup_trigger']) ? $options['gantt_popup_trigger'] : '';
    $gantt_bar_height               = isset($options['gantt_bar_height']) ? $options['gantt_bar_height'] : '';
    $gantt_bar_spacing              = isset($options['gantt_bar_spacing']) ? $options['gantt_bar_spacing'] : '';
    $secondary_bar_colour           = isset($options['secondary_bar_colour']) ? $options['secondary_bar_colour'] : '';
    $primary_bar_colour             = isset($options['primary_bar_colour']) ? $options['primary_bar_colour'] : '';

    if($gantt_scale_projects) {
        $gantt_scale_projects = $gantt_scale_projects;
    } else {
        $gantt_scale_projects = 'Week';
    }
    
    if($secondary_bar_colour) {
        $secondary_bar_colour = $secondary_bar_colour;
    } else {
        $secondary_bar_colour = '#747faf';
    }
    if($dark_mode == 'yes') {
		$secondary_bar_colour = '#2a2841';
    }

    if($gantt_bar_spacing) {
        $gantt_bar_spacing = $gantt_bar_spacing;
    } else {
        $gantt_bar_spacing = 10;
    }

    if($gantt_bar_height) {
        $gantt_bar_height = $gantt_bar_height;
    } else {
        $gantt_bar_height = 16;
    }

    if($gantt_scale_tasks) {
        $gantt_scale_tasks = $gantt_scale_tasks;
        $step = '7';
    } else {
        $gantt_scale_tasks = 'Day';
        $step = '7';
    }

    if($gantt_popup_trigger) {
        $gantt_popup_trigger = $gantt_popup_trigger;
    } else {
        $gantt_popup_trigger = 'mouseover';
    }

    $user   = get_userdata(get_current_user_id());
    $role   = $user->roles[0];

    if($hide_gantt !='yes' && empty($_GET['print']) && is_front_page() && $gantt_show_dashboard == 'on' || $hide_gantt !='yes' && empty($_GET['print']) && is_page(106) && $gantt_show_all_project_page == 'on') {

?>

    <!--/ Start Gantt Pro Form /-->
    <form class="update-gantt-pro-form <?php if($gantt_show_project_task_list == 'on') { echo 'project-task-list-on'; } ?>" method="post" id="update-gantt-pro-form">

        <div class="btn-group" role="group">
            <button type="button" class="btn btn-light <?php if($gantt_scale_projects == 'Day') { echo 'active'; } ?>" id="Day"><?php _e( 'Day', 'wproject-gantt-pro' ); ?></button>
            <button type="button" class="btn btn-light <?php if($gantt_scale_projects == 'Week') { echo 'active'; } ?>" id="Week"><?php _e( 'Week', 'wproject-gantt-pro' ); ?></button>
            <button type="button" class="btn btn-light <?php if($gantt_scale_projects == 'Month') { echo 'active'; } ?>" id="Month"><?php _e( 'Month', 'wproject-gantt-pro' ); ?></button>
            <button type="button" class="btn btn-light" id="Year"><?php _e( 'Year', 'wproject-gantt-pro' ); ?></button>
            <span class="btn btn-light show-gantt">
                <i data-feather="maximize"></i>
            </span>
            <span class="btn btn-light hide-gantt">
                <i data-feather="eye-off"></i>
            </span>
        </div>

        <div class="gantt-target">
            <h1>
                <?php
                    if(is_front_page() || is_page(106)) {
                        _e('Projects I manage', 'wproject-gantt-pro' );
                    }
                ?>
            </h1>
        </div>

        <input type="hidden" name="project_id" id="project_id" />
        <input type="hidden" name="project_start_date" id="project_start_date" />
        <input type="hidden" name="project_end_date" id="project_end_date" />
        <input type="hidden" name="project_name" id="project_name" />
        <input type="hidden" name="gantt_mode" id="gantt_mode" />
        <input type="hidden" name="gantt_type" value="projects" />

        <div class="gantt-pro-mask"><img src="<?php echo get_template_directory_uri();?>/images/spinner.svg" /></div>

        <style>
            .gantt .bar {
                fill: <?php echo $secondary_bar_colour; ?> !important;
            }
            .gantt .bar-progress {
                fill: <?php echo $primary_bar_colour; ?>
            }
        </style>

    <script>
        
        const month_names = {
            en: [
                '<?php _e( 'January', 'wproject-gantt-pro' ); ?>',
                '<?php _e( 'February', 'wproject-gantt-pro' ); ?>',
                '<?php _e( 'March', 'wproject-gantt-pro' ); ?>',
                '<?php _e( 'April', 'wproject-gantt-pro' ); ?>',
                '<?php _e( 'May', 'wproject-gantt-pro' ); ?>',
                '<?php _e( 'June', 'wproject-gantt-pro' ); ?>',
                '<?php _e( 'July', 'wproject-gantt-pro' ); ?>',
                '<?php _e( 'August', 'wproject-gantt-pro' ); ?>',
                '<?php _e( 'September', 'wproject-gantt-pro' ); ?>',
                '<?php _e( 'October', 'wproject-gantt-pro' ); ?>',
                '<?php _e( 'November', 'wproject-gantt-pro' ); ?>',
                '<?php _e( 'December', 'wproject-gantt-pro' ); ?>'
            ]
        };
		var tasks = [

            <?php 
            $current_author = get_current_user_id();
 
            /*
                PHP date format to moment js date format
                https://stackoverflow.com/a/30192680/3256143
            */
            function convertPHPToMomentFormat($date_format) {
                $replacements = [
                    'd' => 'DD',
                    'D' => 'ddd',
                    'j' => 'D',
                    'l' => 'dddd',
                    'N' => 'E',
                    'S' => 'o',
                    'w' => 'e',
                    'z' => 'DDD',
                    'W' => 'W',
                    'F' => 'MMMM',
                    'm' => 'MM',
                    'M' => 'MMM',
                    'n' => 'M',
                    't' => '', // no equivalent
                    'L' => '', // no equivalent
                    'o' => 'YYYY',
                    'Y' => 'YYYY',
                    'y' => 'YY',
                    'a' => 'a',
                    'A' => 'A',
                    'B' => '', // no equivalent
                    'g' => 'h',
                    'G' => 'H',
                    'h' => 'hh',
                    'H' => 'HH',
                    'i' => 'mm',
                    's' => 'ss',
                    'u' => 'SSS',
                    'e' => 'zz', // deprecated since version 1.6.0 of moment.js
                    'I' => '', // no equivalent
                    'O' => '', // no equivalent
                    'P' => '', // no equivalent
                    'T' => '', // no equivalent
                    'Z' => '', // no equivalent
                    'c' => '', // no equivalent
                    'r' => '', // no equivalent
                    'U' => 'X',
                ];
                $momentFormat = strtr($date_format, $replacements);
                return $momentFormat;
            }

            /* Query based on Sort setting */
            if($gantt_order == 'title') {

                $projects = array(
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
                    'meta_query' => array(
                        array(
                        'key'       => 'project_manager',
                        'value'     => get_current_user_id(),
                        'compare'   => '=='
                        )
                    ),
                );

            } else if($gantt_order == 'chronological' || $gantt_order == '') {

                $projects = array(
                    'taxonomy'      => 'project',
                    'hide_empty'    => 0,
                    'orderby'       => 'project_start_date',
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
                    'meta_query' => array(
                        array(
                            'key'        => 'project_start_date',
                            'compare'    => '==',
                            'type'       => 'DATE'
                        ),
                        array(
                            'key'       => 'project_manager',
                            'value'     => get_current_user_id(),
                            'compare'   => '=='
                        )
                    )
                );

            }
            $cats = get_categories($projects);
            
            $tasks_query = new WP_Query( $projects );
            $project_count = $tasks_query->found_posts;
    
            foreach($cats as $cat) {
                
                $term_id                    = $cat->term_id; 
                $term_meta                  = get_term_meta($term_id); 
                $term_object                = get_term( $term_id );
                $project_status             = $term_meta['project_status'][0];
                $current_term               = get_term_by( 'id', $term_id, 'project' );
                $description                = $current_term->description;
                $project_full_description   = $term_meta['project_full_description'][0];
                $project_start_date         = $term_meta['project_start_date'][0];
                $project_end_date           = $term_meta['project_end_date'][0];
                $project_job_number         = $term_meta['project_job_number'][0];
                $project_time_allocated     = $term_meta['project_time_allocated'][0];
                $project_hourly_rate        = $term_meta['project_hourly_rate'][0];
                $user_user                  = get_user_by('ID', $term_meta['project_manager'][0]);
                $user_name                  = $user_user->first_name . ' ' . $user_user->last_name;
                $first_name                 = $user_user->first_name;
                $last_name                  = $user_user->last_name;
                $user_id                    = $user_user->ID;
                $user_photo                 = $user_user->user_photo;
    
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

                if($user_photo) {
                    $avatar         = $user_photo;
                    $avatar_id      = attachment_url_to_postid($avatar);
                    $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                    $avatar         = $small_avatar[0];
                    $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" />';
                } else {
                    $the_avatar     = '<a href="' . home_url() . 'user-profile/?id=' . $user_id . '" class="letter-avatar avatar ' . $colour . '">' . $first_name[0] . $last_name[0] . '</a>';
                }
    
                if($project_start_date && $project_end_date) {
                    $new_project_start_date = new DateTime($project_start_date);
                    $the_project_start_date = $new_project_start_date->format($date_format);
    
                    $new_project_end_date   = new DateTime($project_end_date);
                    $the_project_end_date   = $new_project_end_date->format($date_format);
                }
    
                $milliseconds_start     = 1000 * strtotime($project_start_date);
                $new_start_date         = new DateTime($project_start_date);
                $the_project_start_date    = $new_start_date->format($date_format);
    
                $milliseconds_end       = 1000 * strtotime($project_end_date);
                $new_end_date           = new DateTime($project_end_date);
                $the_project_end_date   = $new_end_date->format($date_format);
    
                $budget = '-';
                if($project_time_allocated && $project_hourly_rate) {
                    $budget = $project_time_allocated * $project_hourly_rate;
                }
    
                /* Project status */
                if($project_status == 'in-progress') {
                    $the_project_status = __('In progress', 'wproject-gantt-pro');
                    $class = 'ganttDefault';
                } else if($project_status == 'planning') {
                    $the_project_status = __('Planning', 'wproject-gantt-pro');
                    $class = 'ganttDefault';
                } else if($project_status == 'proposed') {
                    $the_project_status = __('Proposed', 'wproject-gantt-pro');
                    $class = 'ganttDefault';
                } else if($project_status == 'setting-up') {
                    $the_project_status = __('Setting up', 'wproject-gantt-pro');
                    $class = 'ganttDefault';
                } else if($project_status == 'archived') {
                    $the_project_status = __('Archived', 'wproject-gantt-pro');
                    $class = 'ganttDefault';
                } else if($project_status == 'cancelled') {
                    $the_project_status = __('Cancelled', 'wproject-gantt-pro');
                    $class = 'ganttDefault';
                } else if($project_status == 'complete') {
                    $the_project_status = __('Complete', 'wproject-gantt-pro');
                    $class = 'ganttComplete'; 
                }
                
                if($project_start_date && $project_end_date) {

                    /* Get the total number of tasks in each project */
                    $completed_tasks_project_args = array(
                        'posts_per_page' 	=> -1,
                        'post_type' 		=> 'task',
                        'post_status'		=> array('publish', 'private'),
                        'numberposts'       => -1,
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

                    if($completed_tasks_project > 0) {
                        $progress = $completed_tasks_project / $cat->count * 100;
                    } else {
                        $progress = 0;
                    }

                    if($project_status !='archived' && $project_status !='cancelled') {
                ?>
            {
				start: '<?php echo $project_start_date; ?>',
				end: '<?php echo $project_end_date; ?>',
				name: '<?php if($gantt_popup_trigger != 'disabled') { ?><img src="<?php echo $avatar; ?>" class="avatar <?php echo $the_avatar_style; ?>" /><?php } ?><?php echo esc_html__(get_the_category_by_ID($term_id)); ?>',
				id: "<?php echo $term_id; ?>",
				progress: <?php echo round($progress, 1); ?>
            },
            <?php }
                }
             }
            wp_reset_postdata();
            ?>
            
		]
		var gantt_chart = new Gantt(".gantt-target", tasks.length ? tasks : [{}], {
			on_click: function (task) {
				//console.log(task.name);
			},
            /* When dragging the task from the middle */
			on_date_change: function(task) {
				//console.log(task, start, end);
                $('.gantt-pro-mask').addClass('show');
                
                const start_date = moment(task._start).format('YYYY-MM-DD');
                const end_date = moment(task._end).format('YYYY-MM-DD');

                $('#project_id').attr('value', task.id);
                $('#project_start_date').attr('value', start_date);
                $('#project_end_date').attr('value', end_date);
                $('#project_name').attr('value', task.name);

                //console.log('start date:  ' +moment(task._start).format('YYYY-MM-DD'));
                //console.log('end date:  ' +moment(task._end).format('YYYY-MM-DD'));
                //console.log(task.name+' task dates changed');
                
                setTimeout(function() { 
                    $('#update-gantt-pro-form').submit();
                }, 250);

			},
            /* When dragging the task progress bar */
			on_progress_change: function(progress) {
				//console.log(task, progress);
                $('.gantt-pro-mask').addClass('show');

                const start_date = moment(progress._start).format('YYYY-MM-DD');
                const end_date = moment(progress._end).format('YYYY-MM-DD');

                $('#project_id').attr('value', progress.id);
                $('#project_start_date').attr('value', start_date);
                $('#project_end_date').attr('value', end_date);
                $('#project_name').attr('value', progress.name);
                $('#project_pc_complete').attr('value', progress.progress);
                
                //console.log('progress:  ' +progress);
                //console.log(progress.name+' progress changed');
                
                setTimeout(function() { 
                    $('#update-gantt-pro-form').submit();
                }, 250);
			},
			on_view_change: function(mode) {
				//console.log('mode ->  ' +mode);
                $('#gantt_mode').attr('value', mode);
			},
			language: 'en',
            header_height: 45,
            column_width: 1,
            step: <?php echo $step; ?>,
            //view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
            view_modes: ['<?php _e( 'Day', 'wproject-gantt-pro' ); ?>', '<?php _e( 'Week', 'wproject-gantt-pro' ); ?>', '<?php _e( 'Month', 'wproject-gantt-pro' ); ?>', '<?php _e( 'Year', 'wproject-gantt-pro' ); ?>'],
            view_mode: '<?php echo $gantt_scale_projects; ?>',
            bar_height: <?php echo $gantt_bar_height; ?>,
            popup_trigger: '<?php echo $gantt_popup_trigger; ?>',
            padding: <?php echo $gantt_bar_spacing; ?>, 
            date_format: '<?php echo convertPHPToMomentFormat($date_format); ?>',
            <?php if($gantt_popup_trigger != 'disabled') { ?>
            custom_popup_html: function(task) {
                const start_date = moment(task._start).format('<?php echo convertPHPToMomentFormat($date_format); ?>');
                //const end_date = moment(task._end).format('<?php echo convertPHPToMomentFormat($date_format); ?>');
                /* Remove one day from end date because of Frappe Gantt bug */
                const end_date = moment(task._end).subtract(1, 'days').format('<?php echo convertPHPToMomentFormat($date_format); ?>');
                return `
                    <div class="details-container">
                        <p><strong><a href="<?php echo home_url();?>/?p=${task.id}">${task.name}</a></strong></p>
                        <p><span><?php _e('Start', 'wproject-gantt-pro'); ?>:</span> ${start_date}</p>
                        <p><span><?php _e('Due', 'wproject-gantt-pro'); ?>:</span> ${end_date}</p>
                        <p><span><?php _e('Progress', 'wproject-gantt-pro'); ?>:</span> ${task.progress}%</p>
                    </div>
                `;
            }
            <?php } else { ?>
                custom_popup_html: function(task) {
                    return '';
                }
            <?php } ?>
		});

        /* If no tasks, remove the Gantt chart */
        if(tasks.length == 0) {
            $('#update-gantt-pro-form').remove();
        }

        /* Buttons */
        gantt_chart.change_view_mode('<?php echo $gantt_scale_projects; ?>');
        $('.btn-group').on('click', 'button', function() {
            $btn = $(this);
            var mode = $btn.attr('id');
            gantt_chart.change_view_mode(mode);
            $btn.parent().find('button').removeClass('active');
            $btn.addClass('active');
            <?php if($role != 'project_manager' && $role != 'administrator') { ?>
            $('.gantt .bar-wrapper').css('pointer-events', 'none');
            <?php } ?>
        });
        
		//console.log(gantt_chart);

        $('.show-gantt').click(function() {
            $('.update-gantt-pro-form').toggleClass('full-screen');
            $('.gantt-target h1').addClass('show');
        });

        /* Prevent progress dragging in the project Gantt */
        $('.handle-group .progress').remove();

        <?php if($role != 'project_manager' && $role != 'administrator') { ?>
            $('.gantt .bar-wrapper').css('pointer-events', 'none');
        <?php } ?>

	</script>
    
    <?php if($gantt_show_project_task_list == 'on' && !wp_is_mobile()) { ?>
    <ul class="gantt-task-list">
        <?php 

            /* Query based on Sort setting */

            if($gantt_order == 'title') {
                
                $projects = array(
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
                    'meta_query' => array(
                        array(
                        'key'       => 'project_manager',
                        'value'     => get_current_user_id(),
                        'compare'   => '=='
                        )
                    ),
                );

            } else if($gantt_order == 'chronological' || $gantt_order == '') {

                $projects = array(
                    'taxonomy'      => 'project',
                    'hide_empty'    => 0,
                    'orderby'       => 'project_start_date',
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
                    'meta_query' => array(
                        array(
                            'key'        => 'project_start_date',
                            'compare'    => '==',
                            'type'       => 'DATE'
                        ),
                        array(
                            'key'       => 'project_manager',
                            'value'     => get_current_user_id(),
                            'compare'   => '=='
                        ),
                        array(
                            'key'       => 'project_status',
                            'value'     => 'archived',
                            'compare'   => '!='
                        ),
                        array(
                            'key'       => 'project_status',
                            'value'     => 'cancelled',
                            'compare'   => '!='
                        )
                    )
                );

            }
            $cats = get_categories($projects);
            
            $tasks_query = new WP_Query( $projects );
            $project_count = $tasks_query->found_posts;
    
            foreach($cats as $cat) {

                $term_id                    = $cat->term_id; 
                $term_meta                  = get_term_meta($term_id); 
                $term_object                = get_term( $term_id );
                $project_status             = $term_meta['project_status'][0];
                $current_term               = get_term_by( 'id', $term_id, 'project' );
                $description                = $current_term->description;
                $project_start_date         = $term_meta['project_start_date'][0];
                $project_end_date           = $term_meta['project_end_date'][0];
                $project_job_number         = $term_meta['project_job_number'][0];
                $project_time_allocated     = $term_meta['project_time_allocated'][0];
                $project_hourly_rate        = $term_meta['project_hourly_rate'][0];

            if($project_start_date && $project_end_date && $project_status != 'archived') { ?>
                <li title="<?php echo esc_html__(get_the_category_by_ID($term_id)); ?>">
                    <a href="<?php echo get_category_link($term_id); ?>"><?php echo esc_html__(get_the_category_by_ID($term_id)); ?></a>
                </li>
        <?php } 
        }
                wp_reset_postdata();
            $gantt_list_item_height = $gantt_bar_height + $gantt_bar_spacing;
        } ?>
    </ul>
    <style>
        .gantt-task-list li {
            padding: <?php echo $gantt_bar_spacing; ?>px 20px;
            height: <?php echo $gantt_list_item_height; ?>px;
        }
        <?php if($gantt_show_project_task_list == 'on') { ?>
        .gantt-container {
            padding: 0 0 0 235px !important;
        }
        <?php } ?>
        
    </style>
    <script>
        /* Link the scroll bars */
        $(function(){
            $('.linked').scroll(function(){
                $('.gantt-task-list').scrollTop($(this).scrollTop());    
            })
        })
        $('.gantt-container').addClass('linked');
    </script>
    <?php } ?>

    <script>
        /* Toggle Gantt visibility */
        function a() { 
            $('.gantt-target, .gantt-task-list, .btn-group button').fadeOut();
            $('header .icons').prepend('<li class="toggle-gantt-fs"><img src="<?php echo plugins_url('/images/icon.svg', __FILE__);?>" /></li>');
            $('.update-gantt-pro-form').fadeOut();
        }
        function b() { 
            $('.gantt-target, .gantt-task-list, .btn-group button').fadeIn();
            $('.toggle-gantt-fs').remove();
            $('.update-gantt-pro-form').fadeIn();
        }
        
        $(document).on('click', '.hide-gantt' , function() {
            return (this.tog = !this.tog) ? a() : a();
        });
        $(document).on('click', '.toggle-gantt-fs' , function() {
            return (this.tog = !this.tog) ? b() : b();
        });
    </script>
 
</form>
<!--/ End Gantt Pro Form /-->

<?php }
add_action( 'gantt_pro_dashboard_page', 'gantt_pro_dashboard' );