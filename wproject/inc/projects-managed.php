<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<!--/ Start My Projects /-->
<section class="tab-content tab-content-my-projects">
    
    <div class="rows projects-i-manage">

        <?php if(!wp_is_mobile()) { ?>
        <ul class="header-row">
            <li><i data-feather="folder"></i><?php _e('Project', 'wproject'); ?></li>
            <li><i data-feather="calendar"></i><?php _e('Start', 'wproject'); ?></li>
            <li><i data-feather="calendar"></i><?php _e('Due', 'wproject'); ?></li>
            <li><i data-feather="clock"></i><?php _e('Time', 'wproject'); ?></li>
            <li><i data-feather="dollar-sign"></i><?php _e('Budget', 'wproject'); ?></li>
        </ul>
        <?php } ?>

        <?php projects_managed(); ?>

    </div>

</section>
<!--/ End My Projects /-->

<?php /* Help topics */
function projects_managed_help() { 
    $user       = wp_get_current_user();
    $user_role  = $user->roles[0];

    if($user_role == 'administrator' || $user_role == 'project_manager' || $user_role == 'team_member') {
?>
    <h4><?php _e("Projects I manage", "wproject"); ?></h4>
    <p><?php _e('Projects that I am the project manager for.', 'wproject'); ?></p>
<?php }
}
add_action('help_end', 'projects_managed_help');