<?php if ( ! defined( 'ABSPATH' ) ) { exit; }

if(is_user_logged_in()) { ?>

<!--/ Start Home /-->
<section class="middle">
    <?php 
        $my_total_task_count        = my_total_task_count();
        $current_author             = get_current_user_id();
        $hide_gantt                 = get_user_meta( $current_author, 'hide_gantt' , true );
        $show_tips                  = get_user_meta( $current_author, 'show_tips' , true );
        $dashboard_bar_chart        = get_user_meta( $current_author, 'dashboard_bar_chart' , true );
        $fav_tasks                  = get_user_meta( $current_author, 'fav_tasks' , true );
        $wproject_settings          = wProject();
        $gantt_show_dashboard       = $wproject_settings['gantt_show_dashboard'];
        $user 					    = wp_get_current_user();
        $user_role 				    = $user->roles[0];

        if(function_exists('add_client_settings')) {
            $wproject_client_settings   = wProject_client();
            $client_project_access      = $wproject_client_settings['client_project_access'];
        } else {
            $wproject_client_settings   = '';
            $client_project_access      = '';
        }

        /* If not a observer */
        if($user_role != 'observer') {

            if($user_role == 'administrator' || $user_role == 'project_manager' || $user_role == 'team_member') {
                get_template_part('inc/status');
            }

            do_action( 'home_status' );

            if($dashboard_bar_chart == 'yes' && $user_role == 'project_manager' && !wp_is_mobile() || $dashboard_bar_chart == 'yes' && $user_role == 'administrator') {
                get_template_part('inc/project-chart');
            }
            
            if(function_exists('gantt_pro_dashboard')) {
                do_action('gantt_pro_dashboard_page');
            } else {
                $gantt_show_dashboard = $wproject_settings['gantt_show_dashboard'];

                if($hide_gantt !='yes' && $user_role == 'client' && $gantt_show_dashboard == 'on' && $client_project_access == 'limited' && empty($_GET['print'])) {
                    get_template_part('gantt/gantt-limited');
                } else if($hide_gantt !='yes' && $user_role == 'client' && $gantt_show_dashboard == 'on' && $client_project_access == 'unlimited' && empty($_GET['print'])) {
                    get_template_part('gantt/gantt');
                } else if($hide_gantt !='yes' && $user_role != 'client' && $gantt_show_dashboard == 'on' && empty($_GET['print'])) {
                    get_template_part('gantt/gantt');
                }
                
            }

        ?>

        <!--/ Start Tabby /-->
        <div class="tabby spacer">

            <ul class="tab-nav">
                <li class="my-latest-tasks active"><?php _e('My latest tasks', 'wproject'); ?><span></span></li>

                <?php if($user_role == 'administrator' || $user_role == 'project_manager' || $user_role == 'team_member') { ?>
                <li class="my-projects"><?php _e("Projects I manage", "wproject"); ?><span></span></li>
                <?php } ?>

            </ul>

            <?php get_template_part('inc/my-latest-tasks'); ?>
            
            <?php if($user_role == 'administrator' || $user_role == 'project_manager' || $user_role == 'team_member') {
                get_template_part('inc/projects-managed');
            } ?>
            
            <script>
                $('.tab-nav li').click(function() {
                    var theClass = $(this).attr('class');
                    $('.tab-nav li').removeClass('active').css('pointer-events', 'all');
                    $(this).addClass('active').css('pointer-events', 'none');
                    $('.tab-content').removeClass('active');
                    $('.tab-content-' + theClass).addClass('active');
                });
            </script>
        </div>
        <!--/ End Tabby /-->

        <!--/ Start Dashboard Widget /-->
        <?php if ( is_active_sidebar( 'wproject-dashboard-widget' ) ) { 
            dynamic_sidebar( 'wproject-dashboard-widget' );
        } ?>
        <!--/ End Dashboard Widget /-->

        <?php do_action( 'before_tips' ); ?>

        <?php if($show_tips == 'yes') {
            get_template_part('inc/tips');
        }

    } else {  /* ...otherwise... */ ?>

    <h1><?php _e('Welcome', 'wproject'); ?></h1>
    <p><?php _e('As an observer you are free to view any task, project or user profile, without the ability to participate or contribute. You are essentially in read-only mode.', 'wproject'); ?></p>
    <p><?php _e('If required, please contact an administrator below about upgrading your role.', 'wproject'); ?></p>

    <ul class="team-grid team-grid-home">
    <?php 
        $users = get_users( array( ) );
        foreach ( $users as $user ) { 
            $user_photo = $user->user_photo;

            $user           = get_userdata($user->ID);
            $user_role      = $user->roles[0];
            $first_name     = $user->first_name;
            $last_name      = $user->last_name;

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
                $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . $first_name[0] . $last_name[0] . '</div>';
            }

            $title      = $user->title;
            $the_status = $user->the_status;
            $title_lc   = strtolower($title);
            $title_slug = str_replace(' ', '-', $title_lc);

            if($user_role != 'operator' && $user_role == 'administrator') {
            ?>
            <li class="<?php echo esc_html( $user_role ); ?>">
                <a href="<?php echo get_the_permalink(109);?>?id=<?php echo esc_html( $user->ID ); ?>">
                    <?php echo $the_avatar; ?>
                    <strong><?php echo esc_html( $user->first_name ); ?> <?php echo esc_html( $user->last_name ); ?></strong>
                </a>
            </li>
        <?php }
        }
    ?>
    </ul>

    <?php }
     /* End if not a observer */
?> 

</section>
<!--/ End Home /-->
<?php } ?>

<?php /* Help topics */
function home_help() { 

    $user       = wp_get_current_user();
    $user_role  = !empty($user->roles) ? $user->roles[0] : '';

    if($user_role == 'administrator' || $user_role == 'project_manager' || $user_role == 'team_member') {

?>
    <h4><?php _e('Your tasks', 'wproject'); ?></h4>
    <p><?php _e('The total number of tasks you currently have outstanding, across all projects combined.', 'wproject'); ?></p>

    <h4><?php _e('Your responsibility', 'wproject'); ?></h4>
    <p><?php _e('The burdon you carry across all projects combined.', 'wproject'); ?></p>

    <h4><?php _e('Tasks for others', 'wproject'); ?></h4>
    <p><?php _e('The number of tasks across all projects that other users (not you) are responsible for.', 'wproject'); ?></p>

    <h4><?php _e('All tasks', 'wproject'); ?></h4>
    <p><?php _e('The total number of all tasks across all projects combined.', 'wproject'); ?></p>

    <h4><?php _e('All tasks', 'wproject'); ?></h4>
    <p><?php _e('The total number of all tasks across all projects combined.', 'wproject'); ?></p>

    <h4><?php _e('Latest activity', 'wproject'); ?></h4>
    <p><?php printf( __('A summary of the most recent task statuses across all projects. Go to your <a href="%1$s">account page</a> to change how many tasks are displayed here.', 'wproject'), home_url() . '/account' ); ?></p>

    <h4><?php _e('Task status icons', 'wproject'); ?></h4>
    <p><i data-feather="circle-ellipsis"></i> <?php _e('Reveal status icons', 'wproject'); ?><br />
    <i data-feather="check-circle-2"></i> <?php _e('Complete', 'wproject'); ?><br />
    <i data-feather="stop-circle"></i> <?php _e('Not started', 'wproject'); ?><br />
    <i data-feather="minus-circle"></i> <?php _e('Incomplete', 'wproject'); ?><br />
    <i data-feather="pause-circle"></i> <?php _e('Pause', 'wproject'); ?><br />
    <i data-feather="x-circle"></i> <?php _e('Delete', 'wproject'); ?>
    </p>
<?php }
}
add_action('help_start', 'home_help');

/* Side nav items */
function home_nav() { 
?>
    
<?php }
add_action('side_nav', 'home_nav');