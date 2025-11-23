<!--/ Start Nav /-->
<?php if ( ! defined( 'ABSPATH' ) ) { exit; }

    $the_total_projects_count   = all_projects_count();
    $wproject_settings          = wProject();
    $remove_pages_nav           = $wproject_settings['remove_pages_nav'];
    $team_page                  = $wproject_settings['team_page'];
    $project_access             = $wproject_settings['project_access'];

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

    $user       = wp_get_current_user();
    $user_role  = !empty($user->roles) ? $user->roles[0] : '';

    if($wproject_settings['pages_label']) {
        $pages_label = $wproject_settings['pages_label'];
    } else {
        $pages_label =  __('Pages', 'wproject');
    }
    
?>
<ul class="main-nav">
    <li><a href="<?php echo home_url();?>/"><i data-feather="home"></i><?php _e('Dashboard', 'wproject'); ?></a></li>
    <li>
        <?php /* Project manager and Administrator logic */
        if($user_role == 'project_manager' || $user_role == 'administrator' || $user_role == 'observer') { ?>
            
            <a href="<?php echo get_the_permalink(106);?>"><i data-feather="folder"></i><?php _e('Projects', 'wproject'); ?></a><span class="circ project-count" data="<?php echo $the_total_projects_count['count']; ?>"><?php echo $the_total_projects_count['count']; ?></span>
            <?php all_projects_list(); ?>

        <?php /* Team Member logic */
        } else if($user_role == 'team_member') { ?>

            <?php if($project_access == 'limited' || $project_access == '') { ?>

                <a><i data-feather="folder"></i><?php _e('Projects', 'wproject'); ?></a><span class="circ project-count"></span>
                <?php limited_projects_list(); ?>  

            <?php } else if($project_access == 'all') { ?>

                <a href="<?php echo get_the_permalink(106);?>/"><i data-feather="folder"></i><?php _e('Projects', 'wproject'); ?></a><span class="circ project-count" data="<?php echo $the_total_projects_count['count']; ?>"><?php echo $the_total_projects_count['count']; ?></span>
                <?php all_projects_list(); ?>

            <?php } ?>
            
        <?php /* Client logic */
        } else if($user_role == 'client') { ?>

            <?php if($client_project_access == 'unlimited') { ?>

                <a href="<?php echo get_the_permalink(106);?>/"><i data-feather="folder"></i><?php _e('Projects', 'wproject'); ?></a><span class="circ project-count" data="<?php echo $the_total_projects_count['count']; ?>"><?php echo $the_total_projects_count['count']; ?></span>
                <?php all_projects_list(); ?>

            <?php } else if($client_project_access == 'limited' || $client_project_access == '') { ?>

                <a><i data-feather="folder"></i><?php _e('Projects', 'wproject'); ?></a><span class="circ project-count"></span>
                <?php limited_projects_list(); ?>

            <?php } else if($client_project_access == 'specific' && function_exists('add_client_settings')) { ?>
                <?php specific_projects_list(); ?>
            <?php } ?>

        <?php } ?>
    </li>
        
    <?php 
        if($remove_pages_nav != 'on') {
            if($client_access_pages && $user_role == 'client' || $user_role == 'administrator' || $user_role == 'project_manager' || $user_role == 'team_member' || $user_role == 'observer') { ?>
            <li class="pages-toggle"><a><i data-feather="layers"></i><?php echo $pages_label; ?></a></li>
        <?php }
        }
    ?>

    <?php if(!$team_page) {
        if($client_access_team && $user_role == 'client' || $user_role == 'administrator' || $user_role == 'project_manager' || $user_role == 'team_member' || $user_role == 'observer') { ?>
        <li><a href="<?php echo get_the_permalink(108); ?>"><i data-feather="users"></i><?php _e('Team', 'wproject'); ?></a></li>
    <?php }
    } ?>

    <?php do_action( 'nav_end' ); ?>

    <?php if(wp_is_mobile()) { ?>
    <li class="logout"><a href="<?php echo wp_logout_url( home_url() ); ?>" onclick="return confirm('<?php _e('Really log out?', 'wproject'); ?>')"><i data-feather="log-out"></i><?php _e('Logout', 'wproject'); ?></a></li>
    <?php } ?>
</ul>
<script>
    /* Dynamically get and calc values and copy them into element */
    var limited_projects_list_count = $('.projects-list li').length;
    $('.main-nav .project-count').text(limited_projects_list_count);
</script>
<!--/ End Nav /-->