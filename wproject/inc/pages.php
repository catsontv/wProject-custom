<?php if ( ! defined( 'ABSPATH' ) ) { exit; } 
$wproject_settings = wProject(); 


if($wproject_settings['pages_label']) {
    $pages_label = $wproject_settings['pages_label'];;
} else {
    $pages_label =  __('Pages', 'wproject');
}

if(function_exists('add_client_settings')) {
    $wproject_client_settings   = wProject_client();
    $client_project_access      = $wproject_client_settings['client_project_access'];
    $client_access_pages        = $wproject_client_settings['client_access_pages'];
    $client_access_team         = $wproject_client_settings['client_access_team'];
} else {
    $client_project_access      = '';
    $client_access_pages        = '';
    $client_access_team         = '';
}

$user                       = wp_get_current_user();
    $user_role              = !empty($user->roles) ? $user->roles[0] : '';
$remove_pages_nav           = $wproject_settings['remove_pages_nav'];
$project_access             = $wproject_settings['project_access'];

if($remove_pages_nav != 'on') {
    if($client_access_pages && $user_role == 'client' || $project_access == '' || $user_role == 'administrator' || $user_role == 'project_manager' || $user_role == 'team_member' || $user_role == 'observer') {
?>
    <!--/ Start Pages /-->
    <div class="pages-nav">
        <h2><?php echo $pages_label; ?></h2>
        <?php all_pages(); ?>
        <i data-feather="x" class="close-pages top-right"></i>
    </div>
    <!--/ End Pages /-->
    <script>
        $( document ).ready(function() {
            $('.main-nav .pages-toggle').click(function() {
                $('.left .pages-nav').addClass('open');
            });
            $('.left .pages-nav .close-pages').click(function() {
                $('.left .pages-nav').removeClass('open');
            });
        });
    </script>
<?php }
}