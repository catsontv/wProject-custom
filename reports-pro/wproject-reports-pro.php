<?php /*
Plugin Name:    Reports Pro for wProject
Plugin URI:     https://rocketapps.com.au/product/reports-pro/
Description:    Generate and download reports for wProject.
Version:        1.1.6
Author: 		Rocket Apps
Author URI: 	https://rocketapps.com.au
Text Domain: 	wproject-reports-pro
Author Email:   support@rocketapps.com.au
Domain Path:    /languages/
*/

/* Look for translation file. */
function wp_reports_pro_textdomain() {
    load_plugin_textdomain( 'wproject-reports-pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wp_reports_pro_textdomain' );
update_option('reports_pro_key', '************'); 


/* Constants */
define('REPORTS_PRO_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('REPORTS_PRO_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('REPORTS_PRO_SPECIAL_KEY', '563931a7aaebe3.67050558');
define('REPORTS_PRO_LICENSE_SERVER_URL', 'https://rocketapps.com.au');
define('REPORTS_PRO_ITEM_REFERENCE', 'Reports Pro'); 


/* Update checker */
function reports_pro_update_checker() {
	$license_key = get_option('report_pro_key');
	$api_params = array(
		'slm_action'        => 'slm_check',
		'secret_key'        => REPORTS_PRO_SPECIAL_KEY,
		'license_key'       => $license_key,
		'registered_domain' => $_SERVER['SERVER_NAME'],
		'item_reference'    => urlencode(REPORTS_PRO_ITEM_REFERENCE),
	);

	/* Send query to the license manager server */
	$query = esc_url_raw(add_query_arg($api_params, REPORTS_PRO_LICENSE_SERVER_URL));
	$response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

	/* Check for error in the response */
	if (is_wp_error($response)) { 
		_e('Unexpected Error! The query returned with an error.', 'wproject-reports-pro');
	}

	/* License data */
	$license_data = json_decode(wp_remote_retrieve_body($response));

	if (isset($license_data->status) == 'active') {
		require 'plugin-update-checker/plugin-update-checker.php';
		$RPUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://rocketapps.com.au/files/wproject/reports-pro/info.json',
			__FILE__,
			'reports-pro'
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
if (in_array($_SERVER['SCRIPT_NAME'], $update_pages) && !empty(get_option('report_pro_key')) && is_admin()) {
    reports_pro_update_checker();
} 

/* Add Reports Pro settings page to menu */
function add_reports_pro_settings_page() {

	$wproject_capability    = apply_filters( 'wproject_required_capabilities', 'manage_options' );
	$icon_url               = plugins_url('/images/admin-icon.svg', __FILE__);
    add_menu_page( __( 'Reports Pro','wproject-reports-pro'), __( 'Reports Pro','wproject-reports-pro' ), 'manage_options', 'wproject-reports-pro', 'reports_pro_license_page' ,$icon_url, 32);
	do_action( 'reports_pro_menu_items', 'wproject-license', $wproject_capability );
}
add_action( 'admin_menu', 'add_reports_pro_settings_page' );

function reports_pro_license_page() {
	require_once('reports-pro-license.php');
}

/* Add Reports Pro interface menu item into admin */
function add_new_reports_nav_item() { ?>
    <li data="reports-pro" id="reports-pro" <?php if(isset($_GET['section']) && $_GET['section'] == 'reports-pro') { echo 'class="selected"'; } ?>><img src="<?php echo plugins_url('/images/icon.svg', __FILE__);?>" /><?php _e( 'Reports Pro', 'wproject-reports-pro' ); ?></li>
<?php }
add_action('wproject_admin_pro_nav_start', 'add_new_reports_nav_item', 15);


/* wProject theme version check */
function reports_theme_version_check() {
    $wproject_theme = wp_get_theme();
    $theme_version  = $wproject_theme->get( 'Version' );
    return $theme_version;
}

/* Get plugin version */
function reportsPluginVersion() {
    $plugin_data = get_plugin_data( __FILE__ );
    $plugin_version = $plugin_data['Version'];
    return $plugin_version;
}

/* Add Report settings div into admin */
function add_report_settings() {

    $options                        = get_option( 'wproject_settings' );
	$wproject_objects               = isset($options['objects'] ) ? $options['objects'] : array();
    $report_project_summary         = isset($options['report_project_summary']) ? $options['report_project_summary'] : '';
    $bar_chart_style                = isset($options['bar_chart_style']) ? $options['bar_chart_style'] : '';    
    $pie_chart_style                = isset($options['pie_chart_style']) ? $options['pie_chart_style'] : '';    
    $report_tasks                   = isset($options['report_tasks']) ? $options['report_tasks'] : '';
    $report_incomplete_tasks        = isset($options['report_incomplete_tasks']) ? $options['report_incomplete_tasks'] : '';
    $report_team                    = isset($options['report_team']) ? $options['report_team'] : '';
    $report_time_costs              = isset($options['report_time_costs']) ? $options['report_time_costs'] : '';
    $report_access                  = isset($options['report_access']) ? $options['report_access'] : '';
    $top_times                      = isset($options['top_times']) ? $options['top_times'] : '';    
    $top_time_logged                = isset($options['top_time_logged']) ? $options['top_time_logged'] : '';    
    $enable_time                    = isset($options['enable_time']) ? $options['enable_time'] : '';    
    $report_pro_key                 = get_option('report_pro_key');
    
    if(get_option('wproject_key')) {
        $button = '<input name="submit" class="button" value="' . __( 'Save Settings', 'wproject-reports-pro' ) . '" type="submit" />';
    } else {
        $button = '<a href="' . admin_url() . 'admin.php?page=wproject-license" class="button warn">'. __( 'Activate License Key', 'wproject-reports-pro' ) .'</a>';
    }
?>

    <!--/ Start Reports Pro / -->
    <div class="settings-div reports-pro">

    <h3><?php _e( 'Reports Pro', 'wproject-reports-pro' ); ?> <span>v<?php echo reportsPluginVersion(); ?></span><a href="<?php echo admin_url(); ?>plugins.php?s=reports+pro" class="update-check"><?php _e( 'Check for update', 'wproject-reports-pro' ); ?></a></h3>
    <?php if(!$report_pro_key) { ?>
    <h4 class="warning"><?php printf( __('Please <a href="%1$s" rel="noopener">activate your license key</a> to unlock Reports Pro.', 'wproject-reports-pro'), admin_url() . 'admin.php?page=wproject-reports-pro'); ?></h4>
    <?php } ?>

    <!--/ Start Version Check Notice / -->
    <?php 
        $required_theme_version = '3.2.1';
        $update_link            = admin_url() . 'themes.php?theme=wproject';
        if (version_compare(reports_theme_version_check(), $required_theme_version) < 0) { ?>
            <div class="wproject-notice">
                <strong><?php printf( __('wProject Reports Pro requires at least wProject %1$s. <a href="%2$s" rel="noopener">Update now your theme now</a>.', 'wproject-reports-pro'), $required_version, $update_link); ?></strong>
            </div>
    <?php } ?>
    <!--/ End Version Check Notice / -->

    <!--/ Start Dashboard / -->
    <div class="wproject-plugin-dashboard">
        <h2><?php _e( 'Reports Pro', 'wproject-reports-pro' ); ?></h2>
        <a href="https://rocketapps.com.au/product/reports-pro/#changelog" target="_blank" class="wproject-button"><?php _e( "What's new?", 'wproject-reports-pro' ); ?></a>
        <a href="https://rocketapps.com.au/product/reports-pro/" target="_blank" rel="noopener noreferrer" class="wproject-button"><?php _e( "Read the FAQ", 'wproject-reports-pro' ); ?></a>
        <a href="https://rocketapps.com.au/log-ticket" target="_blank" rel="noopener noreferrer" class="wproject-button"><?php _e( "Support", 'wproject-reports-pro' ); ?></a>
    </div>
    <!--/ End Dashboard / -->

    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Report access', 'wproject-reports-pro' ); ?>
            <span><?php _e( 'Who can access reports.', 'wproject-reports-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="radio" name="wproject_settings[report_access]" value="limited" <?php if ( $report_access == 'limited' ) { ?>checked<?php } ?> /> <?php _e( 'Project managers and administrators only', 'wproject-reports-pro' ); ?>   
            </li>
            <li>
                <input type="radio" name="wproject_settings[report_access]" value="everyone" <?php if ( $report_access == 'everyone' ) { ?>checked<?php } ?> /> <?php _e( 'Everyone', 'wproject-reports-pro' ); ?> 
            </li>
        </ul>
    </div>

    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Inclusions', 'wproject-reports-pro' ); ?>
            <span><?php _e( 'What to include in reports.', 'wproject-reports-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="checkbox" name="wproject_settings[report_project_summary]" <?php if ( $report_project_summary ) { ?>checked<?php } ?> /> <?php _e( 'Summary', 'wproject-reports-pro' ); ?>
            </li>
            <li class="time-option">
                <input type="checkbox" name="wproject_settings[report_time_costs]" <?php if ( $report_time_costs ) { ?>checked<?php } ?> /> <?php _e( 'Time & Costs', 'wproject-reports-pro' ); ?> <?php if(!$enable_time) { ?>(<a href="<?php echo admin_url(); ?>admin.php?page=wproject-settings&section=time"><?php _e( 'Time needs to be enabled first.', 'wproject-reports-pro' ); ?></a>)<?php } ?>
            </li>
            <li class="time-option">
                <input type="checkbox" name="wproject_settings[top_time_logged]" <?php if ( $top_time_logged ) { ?>checked<?php } ?> /> <?php _e( 'Top time logged', 'wproject-reports-pro' ); ?> <?php if(!$enable_time) { ?>(<a href="<?php echo admin_url(); ?>admin.php?page=wproject-settings&section=time"><?php _e( 'Time needs to be enabled first.', 'wproject-reports-pro' ); ?></a>)<?php } ?>
            </li>
            <li>
                <input type="checkbox" name="wproject_settings[report_tasks]" <?php if ( $report_tasks ) { ?>checked<?php } ?> /> <?php _e( 'Task Status', 'wproject-reports-pro' ); ?>
            </li>
            <li>
                <input type="checkbox" name="wproject_settings[report_team]" <?php if ( $report_team ) { ?>checked<?php } ?> /> <?php _e( 'Project Team', 'wproject-reports-pro' ); ?>
            </li>
        </ul>
        <?php if(!$enable_time) { ?>
            <script>
                jQuery('.time-option input').attr('disabled', 'disabled');
            </script>
        <?php } ?>
    </div>

    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Most time logged limit', 'wproject-reports-pro' ); ?>
            <span><?php _e( 'How many logged times to show (default is 10).', 'wproject-reports-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="number" min="1" max="18" name="wproject_settings[top_times]" <?php if ( ! empty( $top_times ) ) { echo 'value="' . $top_times . '"'; } ?> />
            </li>
        </ul>
    </div>

    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Task chart style', 'wproject-reports-pro' ); ?>
            <span><?php _e( 'How the task chart is displayed.', 'wproject-reports-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="radio" name="wproject_settings[bar_chart_style]" value="bar" <?php if ( $bar_chart_style == 'bar' ) { ?>checked<?php } ?> /> <?php _e( 'Bars (default)', 'wproject-reports-pro' ); ?> 
            </li>
            <li>
                <input type="radio" name="wproject_settings[bar_chart_style]" value="curve" <?php if ( $bar_chart_style == 'curve' ) { ?>checked<?php } ?> /> <?php _e( 'Curves', 'wproject-reports-pro' ); ?>   
            </li>
            <li>
                <input type="radio" name="wproject_settings[bar_chart_style]" value="line" <?php if ( $bar_chart_style == 'line' ) { ?>checked<?php } ?> /> <?php _e( 'Lines', 'wproject-reports-pro' ); ?>   
            </li>
        </ul>
    </div>
    
    <hr />

    <div class="fleft">
        <p>
            <?php _e( 'Pie chart style', 'wproject-reports-pro' ); ?>
            <span><?php _e( 'How pie charts are displayed.', 'wproject-reports-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="radio" name="wproject_settings[pie_chart_style]" value="pie" <?php if ( $pie_chart_style == 'pie' ) { ?>checked<?php } ?> /> <?php _e( 'Pie (default)', 'wproject-reports-pro' ); ?> 
            </li>
            <li>
                <input type="radio" name="wproject_settings[pie_chart_style]" value="donut" <?php if ( $pie_chart_style == 'donut' ) { ?>checked<?php } ?> /> <?php _e( 'Donut', 'wproject-reports-pro' ); ?>   
            </li>
        </ul>
    </div>

    <hr />

    <?php echo $button; ?>

    <?php if(!$report_pro_key) { ?>
    <script>
        jQuery('.reports-pro input[type="submit"]').remove();
        jQuery('.reports-pro input').attr('disabled', 'disabled');
    </script>
    <?php } ?>

    <script>
        jQuery( document ).ready(function() {
            <?php $icon = isset($_GET['section']) ? $_GET['section'] : ''; ?>
            <?php if($icon && $icon == 'reports-pro') { ?>
                jQuery('.settings-div h3 span img').attr('src', '<?php echo plugins_url('/images/admin-icon.svg', __FILE__) ?>');
                jQuery('.settings-div h3 span:first-child').removeClass('invert').css('margin', '0').css('opacity', '1');
            <?php } ?>
        });
    </script>
    
    </div>
    <!--/ End Reports Pro / -->

<?php }
add_action('wproject_admin_settings_div_end', 'add_report_settings');


/* Report Nav Item on project page */
function report_front_end_nav() { 
    if(is_tax()) { 
        /* If a custom taxonomy page */
        $term_id                    = get_queried_object()->term_id;
        $current_user_id            = get_current_user_id();
        $user                       = get_userdata($current_user_id);
        $role                       = $user->roles[0];

        if($role == 'project_manager' || $role == 'administrator' || $role == 'team_member') {
        ?>
        <li><a href="<?php echo get_the_permalink(107); ?>?report-id=<?php echo $term_id; ?>"><i data-feather="bar-chart-2"></i><?php _e( 'Report', 'wproject-reports-pro' ); ?></a></li>
    <?php   }
        }
    }
add_action('side_nav', 'report_front_end_nav', 100);


/* Admin CSS */
function reports_enqueue($hook) {
    if(isset($_GET['page']) == 'wproject-reports-pro') {
        wp_enqueue_style( 'reports_admin_css', plugins_url('/css/reports-admin.css', __FILE__) );
    }
}
add_action( 'admin_enqueue_scripts', 'reports_enqueue' );


/* Enqueue CSS and Scripts to front-end */
function enqueue_reports_pro_front_end() {

    if(empty($_GET['print'])) {
        wp_enqueue_style('reports_pro_css', plugins_url('/css/reports-pro.css', __FILE__));
    }
    //wp_enqueue_script('reports-pro', plugins_url( '/js/reports-pro.js' , __FILE__ ), array('jquery'));

    if(isset($_GET['print']) == 'yes') {
        wp_enqueue_style( 'reports_print', plugins_url('/css/reports-print.css', __FILE__) );
    }
}
add_action('wp_enqueue_scripts','enqueue_reports_pro_front_end');

/* Report Pro Options */
function wProject_report() {

	$options                        = get_option( 'wproject_settings' );

	/* Report options */
	$report_access				    = isset($options['report_access']) ? $options['report_access'] : '';
	$report_project_summary         = isset($options['report_project_summary']) ? $options['report_project_summary'] : '';
    $bar_chart_style                = isset($options['bar_chart_style']) ? $options['bar_chart_style'] : '';  
    $pie_chart_style                = isset($options['pie_chart_style']) ? $options['pie_chart_style'] : '';  
	$report_time_costs              = isset($options['report_time_costs']) ? $options['report_time_costs'] : '';
	$report_tasks                   = isset($options['report_tasks']) ? $options['report_tasks'] : '';
	$report_incomplete_tasks        = isset($options['report_incomplete_tasks']) ? $options['report_incomplete_tasks'] : '';	
	$report_team			        = isset($options['report_team']) ? $options['report_team'] : '';
    $top_times			            = isset($options['top_times']) ? $options['top_times'] : '';
    $top_time_logged                = isset($options['top_time_logged']) ? $options['top_time_logged'] : '';
	
	$wprojectReportSettings = array(
	
		'report_access'						=> $report_access,
		'report_project_summary'			=> $report_project_summary,
        'bar_chart_style'                   => $bar_chart_style,
        'pie_chart_style'                   => $pie_chart_style,
		'report_time_costs'					=> $report_time_costs,
		'report_tasks'				        => $report_tasks,
		'report_incomplete_tasks'			=> $report_incomplete_tasks,
		'report_team'						=> $report_team,
        'top_times'						    => $top_times,
        'top_time_logged'                   => $top_time_logged

    );
	return $wprojectReportSettings;
	
	/*
		Template Usage:

		$wproject_settings = wProject_report();
        echo $wproject_settings['currency_symbol'];
	*/
}

require_once('report.php');