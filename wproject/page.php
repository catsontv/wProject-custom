<?php get_header(); 

    $wproject_settings          = wProject();
    $team_page                  = $wproject_settings['team_page'];
    $task_id 	                = isset($_GET['task-id']) ? $_GET['task-id'] : '';
    $project_id	                = isset($_GET['project-id']) ? $_GET['project-id'] : '';
    $report_id	                = isset($_GET['report-id']) ? $_GET['report-id'] : '';

    if(function_exists('add_client_settings')) {
        $wproject_client_settings   = wProject_client();
        $client_access_pages        = $wproject_client_settings['client_access_pages'];
        $client_access_team         = $wproject_client_settings['client_access_team'];
    } else {
        $client_access_pages        = '';
        $client_access_team         = '';
    }
    
    $user       = wp_get_current_user();
    $user_role  = $user->roles[0];
?>

<div class="container">

    <?php get_template_part('inc/left'); ?>

    <!--/ Start Section /-->
    <section class="middle <?php echo $post->post_name; ?>">

        <h1>
            <?php 
                /* If these roles, allow to see the page title */
                if($client_access_pages == 'on' && $user_role == 'client' || $client_access_team == 'on' && $user_role == 'client' || $user_role == 'administrator' || $user_role == 'project_manager' || $user_role == 'team_member') {
                    echo get_the_title(); 
                    
                    if(is_page(102)) { // Edit Task page
                        echo ': ' .  get_the_title($task_id);
                    }
                    if(is_page(101)) { // Edit Project page
                        $project = get_term( $project_id, 'project' );
                        if(!empty($project->name)) {
                            echo ': ' .  $project->name;
                        } else {
                            //
                        }
                    }
                    if(is_page(107)) { // Report page

                        if($report_id) {

                            $cat_to_check = get_term_by( 'id', $report_id, 'project');
                            if ($cat_to_check) {
                            $report = get_term( $report_id, 'project' );
                            echo ': ' .  $report->name;
                            } else {
                                echo '&nbsp;';
                                _e( 'Not Available', 'wproject' );
                            }

                        } else {
                            echo '&nbsp;';
                            _e( 'Not Available', 'wproject' );
                        }
                    }
                } else { /* Otherwise, do not allow to see the page title */
                    
                }
            ?>
        </h1>

        <?php do_action( 'page_start' ); ?>

        <?php 
            if(is_page(100)) {
                get_template_part('inc/account');
            } else if(is_page(105)) {
                if($user_role != 'observer') {
                    get_template_part('inc/new-task');
                }
            } else if(is_page(102)) {
                if($user_role != 'observer') {
                    get_template_part('inc/edit-task');
                }
            } else if(is_page(104)) {
                if($user_role != 'observer') {
                    get_template_part('inc/new-project');
                }
            } else if(is_page(101)) {
                if($user_role != 'observer') {
                    get_template_part('inc/edit-project');
                }
            } else if(is_page(108)) {
                if(!$team_page) {
                    if($client_access_team && $user_role == 'client' || $user_role == 'administrator' || $user_role == 'project_manager' || $user_role == 'team_member' || $user_role == 'observer') {
                        get_template_part('inc/team');
                    }
                }
            } else if(is_page(106)) {
                get_template_part('inc/projects-list');
            } else if(is_page(108)) {
                get_template_part('inc/users');
            } else if(is_page(109)) {
                if($client_access_team && $user_role == 'client' || $user_role == 'administrator' || $user_role == 'project_manager' || $user_role == 'team_member' || $user_role == 'observer') {
                    get_template_part('inc/user-profile');
                }
            } else { ?>
                <?php 
                    echo '<div class="page-content">';

                    /* If these roles, allow to see the page content */
                    if($client_access_pages == 'on' && $user_role == 'client' || $client_access_team == 'on' && $user_role == 'client' || $user_role == 'administrator' || $user_role == 'project_manager' || $user_role == 'team_member') { 

                        the_content(); 

                         /* Side nav items */
                         if($user_role == 'administrator' && !is_page('contacts') && !is_page('report')) {
                            function page_nav_items() { ?>
                                <li><a href="<?php echo admin_url(); ?>/post.php?post=<?php echo get_the_ID(); ?>&action=edit" target="_blank"><i data-feather="edit-3"></i><?php _e( 'Edit Page', 'wproject' ); ?></a></li>
                            <?php }
                            add_action('side_nav', 'page_nav_items');
                        }
    
                    }

                    if ( is_active_sidebar( 'wproject-page-widget' ) ) { 
                        dynamic_sidebar( 'wproject-page-widget' );
                    }

                    echo  '</div>';
                    
                    /* Comments logic */
                    $user 					    = wp_get_current_user();
                    $user_role 				    = $user->roles[0];

                    $options                    = get_option( 'wproject_settings' );
                    $page_comments_enabled      = isset($options['page_comments_enabled']) ? $options['page_comments_enabled'] : '';

                    if(function_exists('add_client_settings')) {
                        $wproject_client_settings   = wProject_client();
                        $client_comment_pages       = $wproject_client_settings['client_comment_pages'];
                    } else {
                        $client_comment_pages       = '';
                    }

                    if($page_comments_enabled == 'on') {
                        if($user_role == 'administrator' || $user_role == 'project_manager' || $user_role == 'team_member' || $user_role == 'client' && $client_comment_pages) {
                            if(comments_open()) {
                                comments_template('/inc/comments.php', true);
                            }
                        }
                    }
                ?>
            <?php }
        ?>

        <?php do_action( 'page_end' ); ?>

    </section>
    <!--/ End Section /-->
    <?php get_template_part('inc/right'); ?>    
    <?php if(empty($_GET['print'])) {
            get_template_part('inc/help');
        }
    ?> 

</div>
<?php get_footer(); ?>