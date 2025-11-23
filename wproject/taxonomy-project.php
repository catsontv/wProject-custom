<?php 
    get_header(); 

    $wproject_settings              = wProject(); 
    $show_task_id                   = $wproject_settings['show_task_id'];
    $print_hide_user_photos         = $wproject_settings['print_hide_user_photos'];
    $print_hide_task_descriptions   = $wproject_settings['print_hide_task_descriptions'];
    $users_can_claim_task_ownership = $wproject_settings['users_can_claim_task_ownership'];
    $enable_time                    = $wproject_settings['enable_time'];
    $enable_kanban                  = $wproject_settings['enable_kanban'];
    $task_spacing                   = $wproject_settings['task_spacing'];
    $avatar_style                   = $wproject_settings['avatar_style'];
    $print_hide_complete_tasks      = $wproject_settings['print_hide_complete_tasks'];
    $fade_on_hold                   = $wproject_settings['fade_on_hold'];
    $contacts_link_to_project       = $wproject_settings['contacts_link_to_project'];

    $date_format                    = get_option('date_format'); 
    $now                            = strtotime('today');
    
    $print                          = isset($_GET['print']) ? $_GET['print'] : '';

    if(function_exists('add_client_settings')) {
        $wproject_client_settings       = wProject_client();
        $client_project_access          = $wproject_client_settings['client_project_access'];
        $client_view_project_details    = $wproject_client_settings['client_view_project_details'];
        $client_view_others_tasks       = $wproject_client_settings['client_view_others_tasks'];
    } else {
        $client_project_access          = '';
        $client_view_project_details    = '';
        $client_view_others_tasks       = '';
    }

    $current_author                 = get_current_user_id();
    $task_order                     = get_user_meta( $current_author, 'default_task_order' , true );
    $hide_gantt                     = get_user_meta( $current_author, 'hide_gantt' , true );
    $show_tips                      = get_user_meta( $current_author, 'show_tips' , true );
    $minimise_complete_tasks        = get_user_meta( $current_author, 'minimise_complete_tasks' , true );
    $pm_auto_kanban_view            = get_user_meta( $current_author, 'pm_auto_kanban_view' , true );

    /* Get custom taxonomy field values */
    $term_id                        = get_queried_object()->term_id; 
    $term_meta                      = get_term_meta($term_id); 
    $term_object                    = get_term( $term_id );
    $description                    = get_the_archive_description();
    $project_status                 = $term_meta['project_status'][0];
    $project_full_description       = $term_meta['project_full_description'][0];
    $project_job_number             = $term_meta['project_job_number'][0];
    $project_start_date             = $term_meta['project_start_date'][0];
    $project_end_date               = $term_meta['project_end_date'][0];
    $project_time_allocated         = $term_meta['project_time_allocated'][0];
    $project_hourly_rate            = $term_meta['project_hourly_rate'][0];

    if (isset($term_meta['web_page_url'][0])) {
        $web_page_url = $term_meta['web_page_url'][0];
        $web_page_url_clean = str_replace(['http://', 'https://'], '', $web_page_url);
    } else {
        $web_page_url = '';
        $web_page_url_clean = '';
    }

    $project_created_date = $term_meta['project_created_date'][0] ?? '';

    if ($project_created_date) {
        $new_project_created_date = new DateTime($project_created_date);
    } else {
        $new_project_created_date = null;

    }

    if ($new_project_created_date instanceof DateTime) {
        $the_project_created_date = $new_project_created_date->format($date_format);
    } else {
        $the_project_created_date = '';
    }

    $project_contact                = isset($term_meta['project_contact'][0]) ? $term_meta['project_contact'][0] : '';

    $user                           = get_userdata($current_author);
    $role                           = $user->roles[0];

    if(isset($term_meta['project_materials_list'][0])) {
        $project_materials_list = $term_meta['project_materials_list'][0];
    }
    if(isset($term_meta['project_materials_total'][0])) {
        $project_materials_total = $term_meta['project_materials_total'][0];
    }
    
    $edit_project_url           = get_the_permalink(101) . '/?project-id=' . $term_id; // Edit Project page
    
    $budget = '-';
    if($project_time_allocated && $project_hourly_rate) {
        $budget = $project_time_allocated * $project_hourly_rate;
    }

    $pm_user    = get_user_by('ID', $term_meta['project_manager'][0]);
    $pm_name    = $pm_user->first_name . ' ' . $pm_user->last_name;

    if($project_start_date || $project_end_date) {
        $new_project_start_date = new DateTime($project_start_date);
        $the_project_start_date = $new_project_start_date->format($date_format);

        $new_project_end_date   = new DateTime($project_end_date);
        $the_project_end_date   = $new_project_end_date->format($date_format);
    }
?>

<div class="container project <?php if($print_hide_complete_tasks == 'on') { echo 'print-hide-tasks'; } ?>">

    <?php get_template_part('inc/left'); ?>

    <!--/ Start Section /-->
    <section class="middle">
        
        <?php 
        /* Project access logic */

        /* Get client projects array */
        $client_projects = get_user_meta( get_current_user_id(), 'client_projects', true );
        if ( ! is_array( $client_projects ) ) {
            $client_projects = array( $client_projects );
        }

        if(
            $role == 'team_member' && user_project_tasks_count() > 0 || 
            $role == 'client' && user_project_tasks_count() > 0 || 
            $role == 'client' && $client_project_access == 'unlimited' || 
            $role == 'client' && $client_project_access == 'specific' && in_array( $term_id, $client_projects ) || 
            $role == 'project_manager' || 
            $role == 'administrator' || 
            $role == 'observer'
        ) { ?>

        <h1><?php echo single_cat_title(); ?></h1>
    
        <?php if($project_status == 'archived' || $project_status == 'cancelled' || $project_status == 'inactive') { ?>
            <div class="project-status">
                <i data-feather="alert-triangle"></i>
                <p><?php printf( __('This project is <strong>%1$s</strong>. To access this project, change <a href="%2$s">status</a>.', 'wproject'), $project_status, $edit_project_url); ?></p>
            </div>
        <?php } else { ?>

        <?php if($description) { ?>
            <div class="project-description">
                <?php echo $description; ?>
            </div>
        <?php } ?>
        
        <?php echo project_progress(); ?>
        <?php echo project_status(); ?>
        <?php 
            if(function_exists('gantt_pro_project')) {
                do_action('gantt_pro_project_page');
            } else {
                if(!wp_is_mobile()) {
                    $gantt_show_dashboard = $wproject_settings['gantt_show_dashboard'];

                    if($hide_gantt !='yes' && $role == 'client' && $gantt_show_dashboard == 'on' && $client_project_access == 'limited' && empty($_GET['print'])) {
                        get_template_part('gantt/gantt-limited');
                    } else if($hide_gantt !='yes' && $role == 'client' && $gantt_show_dashboard == 'on' && $client_project_access == 'unlimited' && empty($_GET['print'])) {
                        get_template_part('gantt/gantt');
                    } else if($hide_gantt !='yes' && $role != 'client' && $gantt_show_dashboard == 'on' && empty($_GET['print'])) {
                        get_template_part('gantt/gantt');
                    }
                }
            } 
        ?>

            <!--/ Start Tabby /-->
            <div class="tabby tabby-project spacer">

                <!--/ Start Tabs /-->
                <ul class="tab-nav">
                    <li class="my-tasks active"><?php _e('My tasks', 'wproject'); ?><span></span></li>

                    <?php if($client_view_others_tasks == 'on' || $role == 'project_manager' || $role == 'administrator' || $role == 'team_member') { ?>
                    <li class="other-tasks"><?php _e('Other tasks', 'wproject'); ?><span></span></li>
                    <li class="all-milestones"><?php _e('All milestones', 'wproject'); ?><span></span></li>
                    <?php } ?>
                </ul>
                <!--/ End Tabs /-->
                

                <!--/ Start My Tasks /-->
                <form class="update-task-status-form" id="update-task-status-form" method="post" enctype="multipart/form-data">
                <div class="tab-content tab-content-my-tasks active">

                    <div class="rows">

                        <ul class="header-row">
                            <li><i data-feather="check-circle-2"></i><?php _e('Task', 'wproject'); ?></li>
                            <li><i data-feather="calendar"></i><?php _e('Start', 'wproject'); ?></li>
                            <li class="my-due-date-toggle toggle"><i data-feather="calendar"></i><?php _e('Due', 'wproject'); ?></li>
                            <li><i data-feather="info"></i><?php _e('Status', 'wproject'); ?></li>
                            <li class="filters"><i data-feather="filter"></i></li>
                        </ul>

                        <p class="filter-row">
                            <i data-feather="filter"></i>
                            <strong><?php _e('Filter', 'wproject'); ?>: <span class="filter-type"></span></strong> 
                            <i data-feather="x"></i>
                        </p>
                        
                        <ul class="body-rows sort-my-tasks">
                            <?php 
                            $my_tasks = array(
                                'post_type'         => 'task',
                                'post_status'		=> 'publish',
                                'author' 			=> $current_author,
                                'category' 			=> $term_id,
                                'orderby' 			=> $task_order,
                                'order' 			=> 'ASC',
                                'posts_per_page'    => -1,
                                'meta_key'          => 'task_status',
                                'meta_value'        => array('incomplete', 'in-progress', 'on-hold', 'complete', 'not-started'),
                                'tax_query' => array(
                                    array(
                                        'taxonomy' => 'project',
                                        'field'    => 'slug',
                                        'terms'    => array( $term_object->slug ),
                                        'operator' => 'IN'
                                    ),
                                ),
                            );
                            $query = new WP_Query($my_tasks);
                            $my_count = $query->post_count;
                            while ($query->have_posts()) : $query->the_post();

                                $task_id                = get_the_id();
                                $author_id              = get_post_field ('post_author', $task_id);
                                $user_ID                = get_the_author_meta( 'ID', $author_id );
                                $first_name             = get_the_author_meta( 'first_name', $author_id );
                                $last_name              = get_the_author_meta( 'last_name', $author_id );
                                $task_job_number        = get_post_meta($task_id, 'task_job_number', TRUE);
                                $task_start_date        = get_post_meta($task_id, 'task_start_date', TRUE);
                                $task_end_date          = get_post_meta($task_id, 'task_end_date', TRUE);
                                $task_files             = get_post_meta($task_id, 'task_files', TRUE);
                                $task_description       = get_post_meta($task_id, 'task_description', TRUE);
                                $task_milestone         = get_post_meta($task_id, 'task_milestone', TRUE);
                                $task_private           = get_post_meta($task_id, 'task_private', TRUE);
                                $task_pc_complete       = get_post_meta($task_id, 'task_pc_complete', TRUE);
                                $task_timer             = get_post_meta($task_id, 'task_timer', TRUE);
                                $user_photo             = get_the_author_meta( 'user_photo', $author_id );
                                $related_id             = get_post_meta($task_id, 'task_related', TRUE);
                                $relation 				= get_post_meta($task_id, 'task_relation', TRUE);
                                $related_title 			= get_the_title($related_id);
                                $related_url 			= get_the_permalink($related_id);
                                $relates				= get_post_meta($task_id, 'relates_to', TRUE);
                                $related_tasks_status   = get_post_meta($related_id, 'task_status', TRUE ); 
                                $related_post_status  	= get_post_status($related_id );
                                $is_blocked_by          = get_post_meta($task_id, 'is_blocked_by', TRUE);
                                $is_similar_to          = get_post_meta($task_id, 'is_similar_to', TRUE);
                                $has_issues_with        = get_post_meta($task_id, 'has_issues_with', TRUE);
                                $explanation 			= get_post_meta($task_id, 'task_explanation', TRUE);
                                $subtask_list           = get_post_meta($task_id, 'subtask_list', TRUE);
                                $post_status            = get_post_status ($task_id);
                                $context_label          = get_post_meta($task_id, 'context_label', TRUE);

                                $author                 = get_userdata($author_id);
	                            $author_role            = @$author->roles[0];

                                $get_attachments        = get_children( array( 'post_parent' => $task_id ) );
                                $attachments_count      = count( $get_attachments );

                                if($relation == 'has_issues_with') {
                                    $relation_label = __('Has issues', 'wproject');
                                    $relation_description = __('This task has issues with', 'wproject');
                                    $icon = 'alert-triangle';
                                } else if($relation == 'is_blocked_by') {
                                    $relation_label = __('Blocked', 'wproject');
                                    $relation_description = __('This task is blocked by', 'wproject');
                                    $icon = 'alert-triangle';
                                } else if($relation == 'is_similar_to') {
                                    $relation_label = __('Similar', 'wproject');
                                    $relation_description = __('This task is similar to', 'wproject');
                                    $icon = 'alert-circle';
                                } else if($relation == 'relates_to') {
                                    $relation_label = __('Relates', 'wproject');
                                    $relation_description = __('This task relates to', 'wproject');
                                    $icon = 'alert-circle';
                                }

                                

                                if($task_start_date || $task_end_date) {
                                    $new_task_start_date    = new DateTime($task_start_date);
                                    $the_task_start_date    = $new_task_start_date->format($date_format);
                            
                                    $new_task_end_date      = new DateTime($task_end_date);
                                    $the_task_end_date      = $new_task_end_date->format($date_format);
                                } else {
                                    $the_task_start_date    = '';
                                    $the_task_end_date      = '';
                                }

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

                                $task_status    = task_status();
                                $task_priority  = task_priority();

                                /* Overdue check and class */
                                $due_date = strtotime($task_end_date);
                                $overdue_class = '';
                                if($due_date && $now > $due_date && $task_status !='complete') {
                                    $overdue_class = 'overdue';
                                }

                                setup_postdata($post);
                            ?>
                                <li class="priority <?php echo $overdue_class; ?> <?php echo $task_priority['slug']; ?> <?php echo $task_status['slug']; ?> <?php echo $relation; ?> <?php if($task_milestone == 'yes') { echo 'milestone'; } ?> <?php if($print_hide_user_photos) { echo 'avatars-hidden'; } ?> <?php if($task_status['slug'] == 'complete' && $minimise_complete_tasks == 'yes') { echo 'minimise'; } ?> <?php if($task_spacing) { echo 'spacing'; } ?> <?php echo str_replace(' ', '-', strtolower($context_label)); ?> <?php if($fade_on_hold && $fade_on_hold == 'on' && $task_status['slug'] == 'on-hold') { echo 'faded'; } ?> <?php if($task_timer == 'on') { echo 'time'; }?>" id="task-id-<?php echo $task_id; ?>" data-date="<?php echo strtotime($task_end_date); ?>">
                                    
                                    <span class="avatar <?php if($print_hide_user_photos) { echo 'hide'; } ?>">
                                        <?php echo $the_avatar; ?>
                                    </span>
                                    
                                    <span>
                                        <strong>
                                            <?php if($task_private == 'yes') { ?>
                                                <?php if($author_id == $current_author) { ?>
                                                    <a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?><?php if($task_pc_complete) { echo ' - ' . $task_pc_complete; ?><?php _e('%', 'wproject'); } ?></a>
                                                <?php } else { ?>
                                                    <?php _e('Private task', 'wproject'); ?>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a>
                                            <?php } ?>
                                        </strong>
                                        <em class="task-owner"><?php echo $first_name; ?> <?php echo $last_name; ?></em>
                                    </span>

                                    <?php if(wp_is_mobile()) {
                                    if($task_description) { ?>
                                        <div class="more-description">
                                            <small class="toggle-next"><?php _e('More', 'wproject'); ?></small>
                                            <p><?php echo make_clickable(nl2br($task_description)); ?></p>
                                        </div>
                                    <?php } 
                                    } ?>


                                    <?php if(wp_is_mobile()) { ?>

                                        <?php if($task_start_date) { ?>
                                            <span class="date"><?php _e('Start date', 'wproject'); ?>: <?php echo $the_task_start_date; ?></span>
                                        <?php } ?>

                                        <?php if($task_end_date) { ?>
                                            <span class="date due-date"><?php _e('Due', 'wproject'); ?>: <?php echo $the_task_end_date; ?></span>
                                        <?php } ?>

                                    <?php } else { ?>

                                        <?php if($task_start_date) { ?>
                                            <span class="date"><?php echo $the_task_start_date; ?></span>
                                        <?php } else { ?>
                                            <?php if(!wp_is_mobile()) { ?>
                                                <span></span>
                                            <?php } ?>
                                        <?php } ?>

                                        <?php if($task_end_date) { ?>
                                            <span class="date due-date"><?php echo $the_task_end_date; ?></span>
                                        <?php } else { ?>
                                            <?php if(!wp_is_mobile()) { ?>
                                                <span></span>
                                            <?php } ?>
                                        <?php } ?>

                                    <?php } ?>


                                    <?php if($task_status['name'] !='') { ?>
                                    <span><em class="status status-<?php echo $task_id; ?> <?php echo $task_status['slug']; ?>"><?php echo $task_status['name']; ?></em></span>
                                    <?php } ?>

                                    <span class="task-status-container">
                                        <b class="task-status">

                                            <?php if($task_timer == 'on') { ?>

                                                <a href="<?php echo get_the_permalink(); ?>">
                                                    <img src="<?php echo get_template_directory_uri();?>/images/clock.svg" class="timing" />
                                                </a>
                                            
                                            <?php } else { ?>

                                                <i data-feather="circle-ellipsis"></i>
                                                <em>
                                                    <?php if(wp_is_mobile()) { ?>
                                                        <small class="task-title task-title-<?php echo $task_priority['slug']; ?>"><?php echo get_the_title(); ?></small>
                                                    <?php } ?>
                                                    <small value="complete" data="<?php echo $task_id; ?>" data-status="<?php _e('Complete', 'wproject'); ?>" <?php if($task_status['slug'] == 'complete') { echo 'class="disabled"'; } ?>><i data-feather="check-circle-2"></i><?php _e('Complete', 'wproject'); ?></small>
                                                    <small class="delete" value="delete" data="<?php echo $task_id; ?>" data-status="<?php _e('Delete', 'wproject'); ?>"><i data-feather="x-circle"></i><?php _e('Delete', 'wproject'); ?></small>
                                                    <small value="incomplete" data="<?php echo $task_id; ?>" data-status="<?php _e('Incomplete', 'wproject'); ?>" <?php if($task_status['slug'] == 'incomplete') { echo 'class="disabled"'; } ?>><i data-feather="minus-circle"></i><?php _e('Incomplete', 'wproject'); ?></small>
                                                    <small value="in-progress" data="<?php echo $task_id; ?>" data-status="<?php _e('In progress', 'wproject'); ?>" <?php if($task_status['slug'] == 'in-progress') { echo 'class="disabled"'; } ?>><i data-feather="arrow-right-circle"></i><?php _e('In progress', 'wproject'); ?></small>
                                                    <small value="not-started" data="<?php echo $task_id; ?>" data-status="<?php _e('Not started', 'wproject'); ?>" <?php if($task_status['slug'] == 'not-started') { echo 'class="disabled"'; } ?>><i data-feather="stop-circle"></i><?php _e('Not started', 'wproject'); ?></small>
                                                    <small value="on-hold" data="<?php echo $task_id; ?>" data-status="<?php _e('On hold', 'wproject'); ?>" <?php if($task_status['slug'] == 'on-hold') { echo 'class="disabled"'; } ?>><i data-feather="pause-circle"></i><?php _e('On hold', 'wproject'); ?></small>
                                                </em>

                                            <?php } ?>

                                            
                                        </b>
                                    </span>

                                    <div class="more">
                                        <?php if(!wp_is_mobile()) {
                                        if($task_description) { ?>
                                            <div class="more-description">
                                                <small class="toggle-next"><?php _e('More', 'wproject'); ?></small>
                                                <p><?php echo make_clickable(nl2br($task_description)); ?></p>
                                            </div>
                                        <?php } 
                                        } ?>
                                                                                
                                        <?php if($relation) { ?>
                                        <div class="relation-content relation-content-<?php echo $task_id; ?>">
                                            <p><strong><?php echo $relation_description; ?> <a href="<?php echo $related_url; ?>"><?php echo $related_title; ?></a>.</strong></p>
                                            <?php if($explanation) { ?>
                                                <p><?php echo $explanation; ?></p>
                                            <?php } ?>
                                        </div>
                                        <?php } ?>
                                        
                                        <!--/ Start More Details /-->
                                        <ul class="more-details">
                                            <?php if($context_label) { ?>
                                            <li class="context-label"><a><?php echo str_replace('-', ' ', $context_label); ?></a></li>
                                            <?php } ?>
                                            <?php if($relation) { ?>
                                            <li class="pill relation" data-relation-id="<?php echo $task_id; ?>"><a><i data-feather="<?php echo $icon; ?>"></i><?php echo $relation_label; ?></a></li>
                                            <?php } ?>
                                            
                                            <?php if(!empty($task_files)) { ?>
                                            <li class="pill default" data="file-set-<?php echo $task_id; ?>"><a href="<?php echo get_the_permalink(); ?>?tab=files"><i data-feather="file"></i><?php echo $attachments_count; ?></a></li>
                                            <?php } ?>

                                            <?php if($enable_time) { ?>
                                                <?php if($task_timer) { ?>
                                                    <li class="pill default"><a href="<?php echo get_the_permalink(); ?>?tab=time"><i data-feather="clock"></i><?php _e('Time', 'wproject'); ?></a></li>
                                                <?php } ?>
                                            <?php } ?>

                                            <?php if($subtask_list) { ?>
                                            <li class="pill default">
                                                <a href="<?php echo get_the_permalink(); ?>?tab=subtasks">
                                                <i data-feather="check-circle-2"></i>
                                                <?php $subtask_rows = get_post_meta( (int)$task_id, 'subtask_list', true);
                                                
                                                $subtask_counter = 0;
                                                foreach( $subtask_rows as $subtask ) {
                                                    if(isset($subtask['subtask_status']) && $subtask['subtask_status'] == '1') {
                                                        $subtask_counter++;
                                                    }
                                                }
                                                echo $subtask_counter . '/' . count($subtask_rows );
                                                ?>
                                                </a>
                                            </li>
                                            <?php } ?>

                                            <?php if(get_comments_number($task_id) > 0) { ?>
                                            <li class="pill default"><a href="<?php echo get_the_permalink($task_id); ?>?tab=comments"><i data-feather="message-circle"></i> <?php echo get_comments_number($task_id); ?></a></li>
                                            <?php } ?>

                                            <?php if($task_private == 'yes') { ?>
                                            <li class="pill"><a><i data-feather="eye-off"></i><?php _e('Private', 'wproject'); ?></a></li>
                                            <?php } ?>

                                            <?php if($task_milestone == 'yes') { ?>
                                            <li class="pill"><a><i data-feather="milestone"></i><?php _e('Milestone', 'wproject'); ?></a></li>
                                            <?php } ?>
                                            
                                            <?php if($task_job_number) { ?>
                                            <li class="pill"><a><i data-feather="hash"></i><?php echo $task_job_number; ?></a></li>
                                            <?php } ?>
                                            
                                            <li class="pill"><a><i data-feather="alert-circle"></i><?php echo $task_priority['name']; ?></a></li>
                                        
                                            <?php if($show_task_id == 'on') { ?>
                                                <li class="pill"><a><i data-feather="chevron-right"></i><?php echo $task_id; ?></a></li>
                                            <?php } ?>

                                            <li class="pill created-date" title="<?php _e('Created date', 'wproject' ); ?>"><a><i data-feather="calendar"></i><?php echo get_the_date(); ?></a></li>
                                        
                                        </ul>
                                        <!--/ End More Details /-->

                                        <a href="<?php echo get_the_permalink(102);?>?task-id=<?php echo $task_id; ?>" class="edit" title="<?php _e('Edit', 'wproject'); ?>"><i data-feather="edit-3"></i></a>
                                    </div>

                                    <em class="fav" data-fav="<?php echo $task_id; ?>" data-current-user="<?php echo $current_author; ?>" title="Pin to top">
                                        <i data-feather="pin"></i>
                                    </em>
                                </li>
                            <?php 
                            endwhile;
                            if($my_count == 0) { ?>
                                <li class="no-tasks"><p><i data-feather="thumbs-up"></i><strong><?php _e('You have nothing to do in this project.', 'wproject'); ?></strong></p></li>
                            <?php }
                            wp_reset_postdata();?>
                        </ul>
                    </div>

                </div>

                <?php /* Get the PM email address for use in notification */
                    $category = get_the_terms( get_the_id(), 'project' );
                    if ( !empty($category) ) {
                    foreach ($category as $cat) {
                        $term_id = $cat->term_id;
                    }
                    $term_meta  = get_term_meta($term_id); 
                    $pm_id      = $term_meta['project_manager'][0];
                ?>
                <input type="hidden" id="task_id" name="task_id" />
                <input type="hidden" id="task_status" name="task_status" />
                <input type="hidden" id="pm_user_id" name="pm_user_id" value="<?php echo $pm_id; ?>" />
                <input type="hidden" id="project_name" name="project_name" value="<?php echo single_cat_title(); ?>" />
                <input type="hidden" id="project_url" name="project_url" value="<?php echo home_url( $wp->request ); ?>" />
                </form>
                <!--/ End My Tasks /-->

                <?php } // End !empty

                if($client_view_others_tasks == 'on' || $role == 'project_manager' || $role == 'administrator' || $role == 'team_member') { ?>

                <!--/ Start Other Tasks /-->
                <div class="tab-content tab-content-other-tasks">

                    <div class="rows">

                        <?php if(!wp_is_mobile()) { ?>
                        <ul class="header-row">
                            <li><i data-feather="check-circle-2"></i><?php _e('Task', 'wproject'); ?></li>
                            <li><i data-feather="calendar"></i><?php _e('Start', 'wproject'); ?></li>
                            <li class="other-due-date-toggle toggle"><i data-feather="calendar"></i><?php _e('Due', 'wproject'); ?></li>
                            <li><i data-feather="info"></i><?php _e('Status', 'wproject'); ?></li>
                            <li class="filters"><i data-feather="filter"></i></li>
                        </ul>
                        <?php } ?>

                        <p class="filter-row">
                            <i data-feather="filter"></i>
                            <strong><?php _e('Filter', 'wproject'); ?>: <span class="filter-type"></span></strong> 
                            <i data-feather="x"></i>
                        </p>

                        <!--/ Start Claim Task Form /-->
                        <form class="claim-task-form" id="claim-task-form" method="post" enctype="multipart/form-data">
                            <ul class="body-rows sort-other-tasks">
                                <?php $other_tasks = array(
                                    'post_type'         => 'task',
                                    'orderby'           => 'date',
                                    'order'             => 'desc',
                                    'post_status'		=> 'publish',
                                    'taxonomy'          => 'project',
                                    'category' 			=> $term_id,
                                    'orderby' 			=> $task_order,
                                    'order' 			=> 'ASC',
                                    'posts_per_page'    => -1,
                                    'author'            => -$current_author,
                                    'tax_query' => array(
                                        array(
                                            'taxonomy' => 'project',
                                            'field'    => 'slug',
                                            'terms'    => array( $term_object->slug ),
                                            'operator' => 'IN'
                                        ),
                                    ),
                                );
                                $query = new WP_Query($other_tasks);
                                $other_count = $query->post_count;
                                while ($query->have_posts()) : $query->the_post();

                                    $task_id                = get_the_id();
                                    $author_id              = get_post_field ('post_author', $task_id);
                                    $user_ID                = get_the_author_meta( 'ID', $author_id );
                                    $first_name             = get_the_author_meta( 'first_name', $author_id );
                                    $last_name              = get_the_author_meta( 'last_name', $author_id );
                                    $task_job_number        = get_post_meta($task_id, 'task_job_number', TRUE);
                                    $task_start_date        = get_post_meta($task_id, 'task_start_date', TRUE);
                                    $task_end_date          = get_post_meta($task_id, 'task_end_date', TRUE);
                                    $task_files             = get_post_meta($task_id, 'task_files', TRUE);
                                    $task_description       = get_post_meta($task_id, 'task_description', TRUE);
                                    $task_milestone         = get_post_meta($task_id, 'task_milestone', TRUE);
                                    $task_private           = get_post_meta($task_id, 'task_private', TRUE);
                                    $task_pc_complete       = get_post_meta($task_id, 'task_pc_complete', TRUE);
                                    $task_timer             = get_post_meta($task_id, 'task_timer', TRUE);
                                    $user_photo             = get_the_author_meta( 'user_photo', $author_id );
                                    $related_id             = get_post_meta($task_id, 'task_related', TRUE);
                                    $relation 				= get_post_meta($task_id, 'task_relation', TRUE);
                                    $related_title 			= get_the_title($related_id);
                                    $related_url 			= get_the_permalink($related_id);
                                    $relates				= get_post_meta($task_id, 'relates_to', TRUE);
                                    $related_tasks_status   = get_post_meta($related_id, 'task_status', TRUE ); 
                                    $related_post_status  	= get_post_status($related_id );
                                    $is_blocked_by          = get_post_meta($task_id, 'is_blocked_by', TRUE);
                                    $is_similar_to          = get_post_meta($task_id, 'is_similar_to', TRUE);
                                    $has_issues_with        = get_post_meta($task_id, 'has_issues_with', TRUE);
                                    $explanation 			= get_post_meta($task_id, 'task_explanation', TRUE);
                                    $subtask_list           = get_post_meta($task_id, 'subtask_list', TRUE);
                                    $post_status            = get_post_status ($task_id);
                                    $context_label          = get_post_meta($task_id, 'context_label', TRUE);

                                    $author                 = get_userdata($author_id);
	                                $author_role            = @$author->roles[0];

                                    $get_attachments        = get_children( array( 'post_parent' => $task_id ) );
                                    $attachments_count      = count( $get_attachments );

                                    if($relation == 'has_issues_with') {
                                        $relation_label = __('Has issues', 'wproject');
                                        $relation_description = __('This task has issues with', 'wproject');
                                        $icon = 'alert-triangle';
                                    } else if($relation == 'is_blocked_by') {
                                        $relation_label = __('Blocked', 'wproject');
                                        $relation_description = __('This task is blocked by', 'wproject');
                                        $icon = 'alert-triangle';
                                    } else if($relation == 'is_similar_to') {
                                        $relation_label = __('Similar', 'wproject');
                                        $relation_description = __('This task is similar to', 'wproject');
                                        $icon = 'alert-circle';
                                    } else if($relation == 'relates_to') {
                                        $relation_label = __('Relates', 'wproject');
                                        $relation_description = __('This task relates to', 'wproject');
                                        $icon = 'alert-circle';
                                    }

                                    if($task_start_date || $task_end_date) {
                                        $new_task_start_date    = new DateTime($task_start_date);
                                        $the_task_start_date    = $new_task_start_date->format($date_format);
                                
                                        $new_task_end_date      = new DateTime($task_end_date);
                                        $the_task_end_date      = $new_task_end_date->format($date_format);
                                    } else {
                                        $the_task_start_date    = '';
                                        $the_task_end_date      = '';
                                    }

                                    if($author_id != '0') {
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
                                    }

                                    if($author_id != '0') {
                                        if($user_photo) {
                                            $avatar         = $user_photo;
                                            $avatar_id      = attachment_url_to_postid($avatar);
                                            $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                                            $avatar         = $small_avatar[0];
                                            $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" />';
                                        } else {
                                            $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . $first_name[0] . $last_name[0] . '</div>';
                                        }
                                    } else {
                                        $the_avatar = '<img src="' . get_template_directory_uri() . '/images/unknown-user.svg' . '" class="avatar" />';
                                    }

                                    $task_status    = task_status();
                                    $task_priority  = task_priority();

                                    /* Overdue check and class */
                                    $due_date = strtotime($task_end_date);
                                    $overdue_class = '';
                                    if($due_date && $now > $due_date && $task_status !='complete') {
                                        $overdue_class = 'overdue';
                                    }

                                    setup_postdata($post);
                                    ?>
                                    <li class="priority <?php echo $overdue_class; ?> <?php echo $task_priority['slug']; ?> <?php echo $task_status['slug']; ?> <?php echo $relation; ?> <?php if($task_milestone == 'yes') { echo 'milestone'; } ?> <?php if($print_hide_user_photos) { echo 'avatars-hidden'; } ?> <?php if($task_spacing) { echo 'spacing'; } ?> <?php echo str_replace(' ', '-', strtolower($context_label)); ?> <?php if($author_id == '0') { echo 'orphan'; } ?> <?php if($task_timer == 'on') { echo 'time'; }?>" data-date="<?php echo strtotime($task_end_date); ?> <?php if($fade_on_hold && $fade_on_hold == 'on' && $task_status['slug'] == 'on-hold') { echo 'faded'; } ?>" id="task-id-<?php echo $task_id; ?>">
                            
                                        <span class="avatar <?php if($print_hide_user_photos) { echo 'hide'; } ?>">
                                            <?php echo $the_avatar; ?>
                                        </span>

                                        <span>
                                            <strong>
                                                <?php if($task_private == 'yes') { ?>
                                                    <?php if($author_id == $current_author) { ?>
                                                        <a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a>
                                                    <?php } else { ?>
                                                        <?php _e('Private task', 'wproject'); ?>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a>
                                                <?php } ?>
                                            </strong>

                                            <?php if($author_id != '0') { ?>
                                                <em class="task-owner"><?php echo $first_name; ?> <?php echo $last_name; ?></em>
                                            <?php } else { ?>

                                                <?php if($users_can_claim_task_ownership) { ?>
                                                    <em class="task-owner no-owner" data-task-id="<?php echo $task_id; ?>" data-task-author="<?php echo get_current_user_id(); ?>" data-task-name="<?php echo get_the_title(); ?>">
                                                        <?php _e('Adopt', 'wproject'); ?>
                                                    </em>
                                                <?php } else { ?>
                                                    <em class="task-owner"><?php _e('Unowned task', 'wproject'); ?></em>
                                                <?php } ?>


                                            <?php } ?>

                                        </span>

                                        <?php if(wp_is_mobile()) {
                                        if($task_description) { ?>
                                            <div class="more-description">
                                                <small class="toggle-next"><?php _e('More', 'wproject'); ?></small>
                                                <p><?php echo make_clickable(nl2br($task_description)); ?></p>
                                            </div>
                                        <?php } 
                                        } ?>

                                        <?php if($task_start_date) { ?>
                                        <span class="date"><?php echo $the_task_start_date; ?></span>
                                        <?php } else { ?>
                                            <?php if(!wp_is_mobile()) { ?>
                                                <span></span>
                                            <?php } ?>
                                        <?php } ?>
                                        <?php if($task_end_date) { ?>
                                        <span class="date due-date"><?php echo $the_task_end_date; ?></span>
                                        <?php } else { ?>
                                            <?php if(!wp_is_mobile()) { ?>
                                                <span></span>
                                            <?php } ?>
                                        <?php } ?>

                                        <?php if($task_status['name'] !='') { ?>
                                        <span><em class="status <?php echo $task_status['slug']; ?>"><?php echo $task_status['name']; ?></em></span>
                                        <?php } ?>

                                        
                                        <span class="task-status-container">
                                            <b class="task-status">
                                                <?php if($task_timer == 'on') { ?>
                                                    <a href="<?php echo get_the_permalink(); ?>">
                                                        <img src="<?php echo get_template_directory_uri();?>/images/clock.svg" class="timing" />
                                                    </a>
                                                <?php } else { ?>
                                                <?php } ?>
                                            </b>
                                        </span>

                                        <div class="more">
                                        <?php if(!wp_is_mobile()) {
                                        if($task_description) { ?>

                                            <?php if($task_private == 'yes') { ?>

                                                <?php if($author_id == $current_author) { ?>

                                                    <div class="more-description">
                                                        <small><?php _e('More', 'wproject'); ?></small>
                                                        <p><?php echo make_clickable(nl2br($task_description)); ?></p>
                                                    </div>

                                                <?php } ?>

                                            <?php } else { ?>

                                                    <div class="more-description">
                                                        <small class="toggle-next"><?php _e('More', 'wproject'); ?></small>
                                                        <p><?php echo make_clickable(nl2br($task_description)); ?></p>
                                                    </div>

                                                <?php } ?>

                                            <?php } 
                                            } ?>
                                            
                                            <?php if($relation) { ?>
                                            <div class="relation-content relation-content-<?php echo $task_id; ?>">
                                                <p><strong><?php echo $relation_description; ?> <a href="<?php echo $related_url; ?>"><?php echo $related_title; ?></a>.</strong></p>
                                                <?php if($explanation) { ?>
                                                    <p><?php echo $explanation; ?></p>
                                                <?php } ?>
                                            </div>
                                            <?php } ?>

                                            <!--/ Start More Details /-->
                                            <ul class="more-details">
                                                <?php if($context_label) { ?>
                                                <li class="context-label"><?php echo str_replace('-', ' ', $context_label); ?></li>
                                                <?php } ?>
                                                <?php if($relation) { ?>
                                                <li class="pill relation" data-relation-id="<?php echo $task_id; ?>"><a><i data-feather="<?php echo $icon; ?>"></i><?php echo $relation_label; ?></a></li>
                                                <?php } ?>
                                                
                                                <?php if(!empty($task_files)) { ?>
                                                <li class="pill default" data="file-set-<?php echo $task_id; ?>"><a href="<?php echo get_the_permalink(); ?>?tab=files"><i data-feather="file"></i><?php echo $attachments_count; ?></a></li>
                                                <?php } ?>

                                                <?php if($enable_time) { ?>
                                                    <?php if($task_timer) { ?>
                                                        <li class="pill default"><a href="<?php echo get_the_permalink(); ?>?tab=time"><i data-feather="clock"></i><?php _e('Time', 'wproject'); ?></a></li>
                                                    <?php } ?>
                                                <?php } ?>

                                                <?php if($subtask_list) { ?>
                                                    <li class="pill default">
                                                        <a href="<?php echo get_the_permalink(); ?>?tab=subtasks">
                                                        <i data-feather="check-circle-2"></i>
                                                        <?php $subtask_rows = get_post_meta( (int)$task_id, 'subtask_list', true);
                                                        
                                                        $subtask_counter = 0;
                                                        foreach( $subtask_rows as $subtask ) {
                                                            if(isset($subtask['subtask_status']) && $subtask['subtask_status'] == '1') {
                                                                $subtask_counter++;
                                                            }
                                                        }
                                                        echo $subtask_counter . '/' . count($subtask_rows );
                                                    ?>
                                                    </a>
                                                </li>
                                                <?php } ?>

                                                <?php if(get_comments_number($task_id) > 0) { ?>
                                                <li class="pill default"><a href="<?php echo get_the_permalink($task_id); ?>?tab=comments"><i data-feather="message-circle"></i> <?php echo get_comments_number($task_id); ?></a></li>
                                                <?php } ?>

                                                <?php if($task_private == 'yes') { ?>
                                                <li><i data-feather="eye-off"></i><?php _e('Private', 'wproject'); ?></li>
                                                <?php } ?>

                                                <?php if($task_milestone == 'yes') { ?>
                                                <li><i data-feather="milestone"></i><?php _e('Milestone', 'wproject'); ?></li>
                                                <?php } ?>
                                                
                                                <?php if($task_job_number) { ?>
                                                <li><i data-feather="hash"></i><?php echo $task_job_number; ?></li>
                                                <?php } ?>
                                                
                                                <li><i data-feather="alert-circle"></i><?php echo $task_priority['name']; ?></li>
                                            
                                                <?php if($show_task_id == 'on') { ?>
                                                    <li><i data-feather="chevron-right"></i><?php echo $task_id; ?></li>
                                                <?php } ?>

                                                <li class="pill created-date" title="<?php _e('Created date', 'wproject' ); ?>"><a><i data-feather="calendar"></i><?php echo get_the_date(); ?></a></li>
                                            
                                            </ul>
                                            <!--/ End More Details /-->

                                        </div>
                                        <em class="fav" data-fav="<?php echo $task_id; ?>" data-current-user="<?php echo $current_author; ?>" title="Pin to top">
                                            <i data-feather="pin"></i>
                                        </em>
                                    </li>
                                <?php
                                endwhile;
                                if($my_count > 0 && $other_count == 0) { ?>

                                    <?php if($my_count <= 5) { ?>
                                    <li class="no-tasks"><p><i data-feather="thumbs-up"></i><strong><?php _e('There are no other tasks here.', 'wproject' ); ?></strong></p></li>
                                    <?php } else if($my_count > 5) { ?>
                                    <li class="no-tasks"><p><i data-feather="thumbs-up"></i><strong><?php printf( __('There are no other tasks here and you still have %1$s to do.', 'wproject' ), $my_count); ?></strong></p></li>
                                    <?php } ?>

                                <?php }
                                wp_reset_postdata();?>
                            </ul>
                            <input type="hidden" id="no_owner_task_id" name="no_owner_task_id" />
                            <input type="hidden" id="no_owner_task_author" name="no_owner_task_author" value="<?php echo $current_author; ?>" />
                            <input type="hidden" id="no_owner_task_name" name="no_owner_task_name" />
                        </form>
                        <!--/ End Claim Task Form /-->
                        
                        <script>
                            $('.other-tasks span').text( <?php echo $other_count; ?> );
                        </script>

                    </div>

                </div>
                <!--/ End Other Tasks /-->
                <?php } ?>

                 <!--/ Start Milesones /-->
                 <div class="tab-content tab-content-all-milestones">

                    <div class="rows">

                        <?php if(!wp_is_mobile()) { ?>
                        <ul class="header-row">
                            <li><i data-feather="check-circle-2"></i><?php _e('Task', 'wproject'); ?></li>
                            <li><i data-feather="calendar"></i><?php _e('Start', 'wproject'); ?></li>
                            <li class="other-due-date-toggle toggle"><i data-feather="calendar"></i><?php _e('Due', 'wproject'); ?></li>
                            <li><i data-feather="info"></i><?php _e('Status', 'wproject'); ?></li>
                            <li class="filters"><i data-feather="filter"></i></li>
                        </ul>
                        <?php } ?>

                    </div>

                    

                    <ul class="body-rows sort-all-milestones">
                    </ul>

                </div>
                <!--/ End Milesones /-->

                <?php echo task_filter(); ?>

                
                <script>
                    /* Milestone clone into new tab */
                    $(document).ready(function() {
                        
                        /* Find all list items with the class 'milestone' */
                        var milestones = $('.sort-my-tasks li.milestone, .sort-other-tasks li.milestone');

                        /* Clone each 'milestone' item and append it to the 'sort-all-milestones' list */
                        milestones.each(function() {
                            var clonedItem = $(this).clone();
                            clonedItem.appendTo('ul.sort-all-milestones');
                        });
                        var milestones_count = milestones.length;

                        if(milestones_count == 0) {
                            $('.all-milestones, .tab-content-all-milestones').remove();
                        }

                        $('.all-milestones span').text(milestones_count);
                        $('.sort-all-milestones .task-status-container').remove();
                        $('.tab-content-all-milestones .body-rows li .fav').remove();


                        /* Don't allow pinning of others tasks' */
                        $('.tab-content-other-tasks .body-rows li .fav').remove();
                        
    
                        $('.tab-nav li').click(function() {
                            var theClass = $(this).attr('class');
                            $('.tab-nav li').removeClass('active').css('pointer-events', 'all');
                            $(this).addClass('active').css('pointer-events', 'none');
                            $('.tab-content').removeClass('active');
                            $('.tab-content-' + theClass).addClass('active');
                        });
                        $('.minimise .avatar img').click(function() {
                            $(this).closest('li').toggleClass('minimise');
                        });
                        /* Toggle state of files and task relation warnings */
                        $( document ).ready(function() {
                            $('.relation').click(function() {
                                var relationId = $(this).attr('data-relation-id');
                                $('.relation-content-' + relationId).fadeToggle();
                                $(this).toggleClass('active');
                            });
                            $('.files').click(function() {
                                var filesId = $(this).attr('data-files-id');
                                $('.files-content-' + filesId).fadeToggle();
                            });
                            $('.filters ul li').click(function() {
                                $('.filters ul li').removeClass('active');
                                $(this).addClass('active');
                            });
                            $('.more-details .file').click(function() {
                                var fileSetId = $(this).attr('data');
                                $('#'+fileSetId).fadeToggle();
                            });
                        });

                        /* Claim ownership of a task */
                        $( document ).ready(function() {
                            $('.no-owner').click(function() {
                                var no_owner_task_id = $(this).attr('data-task-id');
                                var no_owner_task_name = $(this).attr('data-task-name');
                                $('#no_owner_task_id').val(no_owner_task_id);
                                $('#no_owner_task_name').val(no_owner_task_name);

                                var task_owner_name = '<?php echo get_the_author_meta('first_name', $current_author); ?> <?php echo get_the_author_meta('last_name', $current_author); ?>';
                                $(this).text(task_owner_name);
                                $(this).css('pointer-events', 'none');

                                setTimeout(function() { 
                                    $('#claim-task-form').submit();
                                }, 150);
                            });
                        });   
                        
                        /* 
                            Manipulate the Kanban.
                            We have to do this in case the tasks statuses get changed before using the Kanban.
                        */
                        $( document ).ready(function() {
                            $('.task-status em small').click(function() {
                                var changed_status = $(this).attr('value'); // The status the task is changed to
                                var changed_status_id = $(this).attr('data'); // The task ID

                                if(changed_status == 'complete') {
                                    $('#'+changed_status_id).prependTo('#complete').unbind('click');
                                } else if(changed_status == 'incomplete' || changed_status == 'not-started') {
                                    $('#'+changed_status_id).prependTo('#not-started').unbind('click');
                                } else if(changed_status == 'in-progress') {
                                    $('#'+changed_status_id).prependTo('#in-progress').unbind('click');
                                } else if(changed_status == 'on-hold') {
                                    $('#'+changed_status_id).prependTo('#on-hold').unbind('click');
                                } else if(changed_status == 'delete') {
                                    $('#'+changed_status_id).remove();
                                }
                            });

                            /* Start Toggle due date order */
                            <?php if($task_order == 'due_date') { ?>
                                var $wrapper = $('.sort-my-tasks');
                                $wrapper.find('.priority').sort(function (a, b) {
                                    return +a.getAttribute('data-date') - +b.getAttribute('data-date');
                                }).appendTo( $wrapper );
                            <?php } ?>

                            /* Toggle the task descriptions */
                            $('.more-description p').hide();
                            
                            $('.more-description .toggle-next').click(function() {
                                $(this).next('p').fadeToggle();
                                $(this).text($(this).text() == '<?php _e('Less', 'wproject'); ?>' ? '<?php _e('More', 'wproject'); ?>' : '<?php _e('Less', 'wproject'); ?>');
                            });

                            /* Pin a task */
                            $('body').on('click', '.fav', function() {
                                $(this).closest('li').addClass('pinned');
                                $(this).attr('title', '<?php _e('Pin to top', 'wproject'); ?>');
                                var fav_task_id = $(this).attr('data-fav');
                                Cookies.set('fav_task_'+fav_task_id, fav_task_id);

                                var $li = $(this).closest('li');
                                $li.prependTo($li.parent());

                            });
                            /* Unpin a task */
                            $('body').on('click', '.pinned .fav', function() {
                                $(this).closest('li').removeClass('pinned');
                                $(this).attr('title', '<?php _e('Pin to top', 'wproject'); ?>');
                                var fav_task_id = $(this).attr('data-fav');
                                Cookies.remove('fav_task_'+fav_task_id);
                            });

                            <?php /* Add 'pinned' class to tasks that have been pinned */
                                if($_COOKIE) {
                                    foreach ($_COOKIE as $key=>$val) {
                                        if($key == 'fav_task_' . $val) { ?>
                                        //echo $key.' is '.$val."<br>\n";
                                        $('#task-id-<?php echo $val; ?>').addClass('pinned');
                                        $('#task-id-<?php echo $val; ?> .fav').attr('title', '<?php _e('Unpin', 'wproject'); ?>');

                                        /* Move pinned tasks to the top */
                                        $('.sort-my-tasks').find('.pinned').prependTo('.sort-my-tasks');
                                    <?php }
                                    }
                                }
                            ?>
                        });   

                        
                    });

                </script>
            
                <?php due_date_sorting(); ?>

            </div>
            <!--/ End Tabby /-->

            <!--/ Start Project Widget /-->
            <?php if ( is_active_sidebar( 'wproject-project-widget' ) ) { 
                dynamic_sidebar( 'wproject-project-widget' );
            } ?>
            <!--/ End Project Widget /-->

        </section>
        <!--/ End Section /-->

        <?php if($role == 'project_manager' || $role == 'administrator' || $role == 'team_member' || $role == 'client' && $client_view_project_details == 'on') { ?>
        <!--/ Start Project Details Pane /-->
        <div class="project-details">
            <h2><?php echo single_cat_title(); ?></h2>
            <i data-feather="x" class="close-project-details top-right"></i>

            <p>
                <strong><?php _e('Project manager', 'wproject'); ?>:</strong>
                <span><i data-feather="user"></i><a href="<?php echo get_the_permalink(109);?>?id=<?php echo $term_meta['project_manager'][0]; ?>"><?php echo $pm_name; ?></a></span>
            </p>

            <?php if($project_created_date) { ?>
            <p>
                <strong><?php _e('Created', 'wproject'); ?>:</strong>
                <span><i data-feather="calendar"></i><?php echo $the_project_created_date; ?></span>
            </p>
            <?php } ?>

            <?php if($contacts_link_to_project && $project_contact) { ?>
                <p>
                    <strong><?php _e('Contact', 'wproject'); ?>:</strong>
                    <span><i data-feather="briefcase"></i><a href="<?php echo get_the_permalink($project_contact); ?>"><?php echo get_the_title($project_contact); ?></a></span>
                </p>
            <?php } ?>

            <p>
                <strong><?php _e('Milestones', 'wproject'); ?>:</strong>
                <span>
                    <i data-feather="milestone"></i>
                    <script>
                        var the_milestone_count = $('.tab-content .body-rows li.milestone').length;
                        document.write(the_milestone_count);
                    </script>
                </span>
            </p>

            <?php if($project_job_number) { ?>
            <p>
                <strong><?php _e('Job #', 'wproject'); ?>:</strong>
                <span><i data-feather="hash"></i><?php echo str_replace('-', ' ', ucfirst($project_job_number)); ?></span>
            </p>
            <?php } ?>

            <?php if($project_status) {
                /* Project status */
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
                } else if(!$project_status) {
                    $the_project_status = __('Proposed', 'wproject');
                }
            ?>
            <p>
                <strong><?php _e('Status', 'wproject'); ?>:</strong>
                <span><i data-feather="activity"></i><?php echo $the_project_status; ?></span>
            </p>
            <?php } ?>

            <?php if($project_start_date) { ?>
                <p>
                    <strong><?php _e('Start date', 'wproject'); ?>:</strong>
                    <span><i data-feather="calendar"></i><?php echo $the_project_start_date; ?></span>
                </p>
            <?php } ?>

            <?php if($project_end_date) { ?>
                <p class="due-date">
                    <strong><?php _e('Due date', 'wproject'); ?>:</strong>
                    <span><i data-feather="calendar"></i><?php echo $the_project_end_date; ?></span>
                </p>
            <?php } ?>
    
            <?php if($project_time_allocated) { ?>
                <p>
                    <strong><?php _e('Time allocated', 'wproject'); ?>:</strong>
                    <span><i data-feather="watch"></i><?php echo $project_time_allocated; ?> <?php /* Abbreviation of 'hours': */ _e( 'hrs', 'wproject' ); ?></span>
                </p>
            <?php } ?>
    
            <?php if($enable_time) { ?>
            <p class="time">
                <strong><?php _e('Time used', 'wproject'); ?>:</strong>

                <?php 
                $project_total_time	= get_term_meta($term_id, 'project_total_time', TRUE);
                if($project_total_time) {
                    $hours              = floor($project_total_time / 3600);
                    $minutes            = floor(($project_total_time / 60) % 60);
                    $seconds            = $project_total_time % 60;
                } else {
                    $hours              = '00';
                    $minutes            = '00';
                    $seconds            = '00';
                }

                if($project_total_time != '0' ) { ?>
                    <span>
                        <i data-feather="clock"></i>
                        <?php printf("%02d:%02d:%02d", $hours, $minutes, $seconds); ?>
                    </span>
                <?php } else { ?>
                    <span>
                        <i data-feather="calendar"></i>
                    </span>
                <?php }
                 ?>

                <?php /* If the time uses is more than the time allocated, add 'overdue' class. */
                if($project_time_allocated && $hours > $project_time_allocated) { ?>
                    <script>
                        $('.project-details .time').addClass('overdue');
                    </script>
                <?php } ?>
               
            </p>
            <?php } ?>

            <?php if($project_hourly_rate) { ?>
                <p>
                    <strong><?php _e('Hourly rate', 'wproject'); ?>:</strong>
                    <span><i data-feather="dollar-sign"></i><?php if($wproject_settings['currency_symbol_position'] == 'l') { echo $wproject_settings['currency_symbol']; } ?><?php echo $project_hourly_rate; ?><?php if($wproject_settings['currency_symbol_position'] == 'r') { echo $wproject_settings['currency_symbol']; } ?></span>
                </p>
            <?php } ?>

            <?php if($project_time_allocated && $project_hourly_rate ) { ?>
                <p>
                    <strong><?php _e('Budget', 'wproject'); ?>:</strong>
                    <span><i data-feather="dollar-sign"></i><?php if($wproject_settings['currency_symbol_position'] == 'l') { echo $wproject_settings['currency_symbol']; } ?><?php echo $budget; ?><?php if($wproject_settings['currency_symbol_position'] == 'r') { echo $wproject_settings['currency_symbol']; } ?> (<?php _e('time x hours', 'wproject'); ?>)</span>
                </p>
            <?php } ?>

            <?php if($web_page_url) { ?>
                <div class="web-page-url">
                    <span>
                        <i data-feather="globe"></i>
                    </span>
                    <a href="<?php echo $web_page_url; ?>">
                        <?php echo rtrim($web_page_url_clean, '/'); ?>
                    </a>
                </div>
            <?php } ?>

            <?php if($project_full_description) { 
                echo '<div class="full-description"><h3>';
                _e('Full description', 'wproject');
                echo '</h3>';
                echo '<p>' . make_clickable(nl2br($project_full_description)) . '</p></div>';
            } ?>

            <?php if(isset($project_materials_list)) { ?>
                <div class="materials-window">
                    <h3><?php _e('Materials', 'wproject'); ?> <span> (<?php if($wproject_settings['currency_symbol_position'] == 'l') { echo $wproject_settings['currency_symbol']; } ?><?php echo $project_materials_total; ?><?php if($wproject_settings['currency_symbol_position'] == 'r') { echo $wproject_settings['currency_symbol']; } ?>)</span></h3>
                    <div>
                        <?php 
                            $all_rows = get_term_meta( (int)$term_id, 'project_materials_list', true);
                            if($all_rows) {
                                if( count($all_rows ) > 0  ){
                                    sort($all_rows); /* Sort alphabetically */
                                    foreach( $all_rows as $s_row ) {
                                    ?>
                                        <p>
                                            <strong><?php echo $s_row['project_material_name'] ?>:</strong>
                                            <span><?php if($wproject_settings['currency_symbol_position'] == 'l') { echo $wproject_settings['currency_symbol']; } ?><?php echo $s_row['project_material_cost']; ?><?php if($wproject_settings['currency_symbol_position'] == 'r') { echo $wproject_settings['currency_symbol']; } ?></span>
                                        </p>
                                    <?php 
                                    }
                                }
                            }
                        ?>
                    </div>
                </div>
            <?php } ?>

            <div class="milestones-window">
                <h3><?php _e('Milestones', 'wproject'); ?></h3>
                <div class="milestone-bar">
                    <div></div>
                </div>
                <ul>
                </ul>
            </div>
            
            <script>
                $(document).ready(function() {
                    /* Find milestone tasks in My Tasks and Other Tasks only */
                    $('.sort-my-tasks li.milestone, .sort-other-tasks li.milestone').each(function() {
                        if ($(this).hasClass('milestone') ) {
                            var milestone_text = $(this).find('strong a').first().text();
                            var $milestone_list_item = $('<li>').text(milestone_text).addClass($(this).attr('class')).removeClass(function(index, className) {
                                return (className.match(/(^|\s)(?!complete|in-progress|not-started|on-hold|incomplete)\S+/g) || []).join(' ');
                            });
                            /* Add list of milesstone tasks titles into element */
                            $('.milestones-window ul').append($milestone_list_item);
                        }
                    });

                    /* Add check image to completed milestones */
                    var complete_count = 0;
                    $('.milestones-window ul li').each(function() {
                        if ($(this).hasClass('complete') ) {
                            $(this).prepend('<img src="<?php echo get_template_directory_uri(); ?>/images/check.svg" />');
                            complete_count++;
                        } else {
                            //
                        }
                    });

                    /* Remove milestone window if no milestones, otherwise show counts and progress bar */
                    var milestones_count = $('.sort-my-tasks li.milestone, .sort-other-tasks li.milestone').length;
                    var milestone_percentage = (complete_count / milestones_count) * 100;
                    if(milestones_count == 0) {
                        $('.milestones-window').remove();
                    } else {
                        $('.milestones-window h3').text("<?php _e('Milestones', 'wproject'); ?> ("+complete_count+"/"+milestones_count+")");
                        $('.milestones-window .milestone-bar div').css('width', milestone_percentage + '%');
                    }

                });
            </script>

        </div>
        <!--/ End Project Details Pane /-->
        <?php } ?>


        <script>

            /* Inject the task count/tasks remaining into the status box */
            <?php if($role == 'project_manager' || $role == 'administrator' || $role == 'team_member' || $role == 'observer') { ?>

                var total_tasks = $('.left .main-nav li ul .current span').text()
            
            <?php } else if($role == 'client') { ?>

                var my_tasks_num            = $('.tab-content-my-tasks .body-rows .priority').length;
                var other_tasks_num         = $('.tab-content-other-tasks .body-rows .priority');
                var total_tasks_num         = parseInt(+my_tasks_num) + parseInt(+other_tasks_num);
                
                var my_complete_tasks       = $('.tab-content-my-tasks .body-rows .priority.complete').length;
                var other_complete_tasks    = $('.tab-content-other-tasks .body-rows .priority.complete').length;
                var total_complete_tasks    = parseInt(+my_complete_tasks) + parseInt(+other_complete_tasks);
                var total_tasks             = total_complete_tasks+'/'+total_tasks_num;

            <?php } ?>
                $('.middle .status-box .total-tasks').text( total_tasks );
                
                $( document ).ready(function() {
                    $('.project-details-toggle').click(function() {
                        $('.project-details').addClass('move');
                    });
                    $('.close-project-details').click(function() {
                        $('.project-details').removeClass('move');
                    });
                });


            /* Inject total project 'Task remaining' status box */
            var all_tasks               = $('.middle .tab-content .body-rows .priority').length;
            var my_tasks_complete       = $('.middle .tab-content-my-tasks .priority.complete').length;
            var other_tasks_complete    = $('.middle .tab-content-other-tasks .body-rows .priority.complete').length;
            var all_complete_tasks      = parseInt(+my_tasks_complete) + parseInt(+other_tasks_complete);
            var all_tasks_remaining     = parseInt(+all_tasks) - parseInt(+all_complete_tasks);
            $('.middle .status-box .total-project-tasks .value').text( all_tasks_remaining );

            /* Inject my tasks count into My Tasks tab */
            var my_tasks_num = $('.tab-content-my-tasks .body-rows .priority').length;
            $('.my-tasks span').text(my_tasks_num);

            /* Inject total tasks count into status box */
            $('.total-task-count span').text(all_tasks);

            /* Inject complete tasks count into status box */
            $('.complete-project-tasks span').text(all_complete_tasks);

        </script>

    <?php /* Help topics */
        function project_help() { ?>

            <h4><?php _e('Project info', 'wproject'); ?></h4>
            <p><?php _e('Show the full details of the project.', 'wproject'); ?></p>

            <h4><?php _e('Task status icons', 'wproject'); ?></h4>
            <p><i data-feather="circle-ellipsis"></i> <?php _e('Reveal status icons', 'wproject'); ?><br />
            <i data-feather="check-circle-2"></i> <?php _e('Complete', 'wproject'); ?><br />
            <i data-feather="minus-circle"></i> <?php _e('Incomplete', 'wproject'); ?><br />
            <i data-feather="pause-circle"></i> <?php _e('Pause', 'wproject'); ?><br />
            <i data-feather="x-circle"></i> <?php _e('Delete', 'wproject'); ?>
            </p>
        <?php }
        add_action('help_start', 'project_help');

        if(wp_is_mobile()) {
            overdue();
        }

        /* Side nav items */
        function project_details_nav() { 

            $wproject_settings      = wProject(); 
            $enable_kanban          = $wproject_settings['enable_kanban'];

            if(function_exists('add_client_settings')) {
                $wproject_client_settings       = wProject_client();
                $client_view_project_details    = $wproject_client_settings['client_view_project_details'];
            } else {
                $client_view_project_details    = '';
            }

            $wproject_settings      = wProject(); 
            $term_id                = get_queried_object()->term_id; 
            $term_meta              = get_term_meta($term_id); 
            $current_user_id        = get_current_user_id();
            $project_manager_id     = get_user_by('ID', $term_meta['project_manager'][0]);
            $user                   = get_userdata($current_user_id);
            $role                   = $user->roles[0];
            $current_url            = $wproject_settings['current_url'];
        ?>
        

            <?php if($role != 'observer') { ?>
            <li><a href="<?php echo get_the_permalink(105); ?>?project-id=<?php echo $term_id; ?>"><i data-feather="plus-circle"></i><?php _e('Add a task', 'wproject'); ?></a></li>
            <?php } ?>
            
            <?php add_to_calendar(); ?>

            <?php if($current_user_id == $project_manager_id->ID) { ?>
            <li><a href="<?php echo get_the_permalink(101); ?>?project-id=<?php echo $term_id; ?>"><i data-feather="edit-3"></i><?php _e('Edit', 'wproject'); ?></a></li>
            
            <form class="delete-project-form" id="delete-project-form" method="post" enctype="multipart/form-data">
                <li><a><i data-feather="folder-delete"></i><?php _e('Delete', 'wproject'); ?></a></li>
                <input type="hidden" name="project_id" value="<?php echo $term_id; ?>" />
                <script>
                    $(document).on('click', '.delete-project-form', function() {
                        if (confirm('<?php _e('Really delete this project and all its tasks?', 'wproject'); ?>')) {

                            if (confirm('<?php _e('Last chance! Really delete this project and all its tasks?', 'wproject'); ?>')) {

                                setTimeout(function() { 
                                    $('#delete-project-form').submit();
                                }, 250);
                            }

                        } else {
                            
                        }
                    });
                </script>
            </form>

            <?php } ?>

            <?php /* Show Kanban nav item if option is enabled */
            if($enable_kanban) { ?>
            <li class="project-kanban-toggle"><a><i data-feather="columns"></i><?php _e('Kanban Board', 'wproject'); ?></a></li>   
            <?php
            } ?>
            
            <?php if($role == 'project_manager' || $role == 'administrator' || $role == 'team_member' || $role == 'client' && $client_view_project_details == 'on') { ?>
                <li class="project-details-toggle"><a><i data-feather="info"></i><?php _e('Project info', 'wproject'); ?></a></li> 
            <?php } 
            
            copy_link();
            ?>

            <?php /* Show the project archive button if PM owns this project or if administrator */
                if($current_user_id == $project_manager_id->ID || $role == 'administrator') {
                    archive_project_button();
                    delete_completed_project_tasks_button();
                }
            ?>

        <?php }
        add_action('side_nav', 'project_details_nav'); ?>

    <?php } 
    ?>

    <?php if(empty($_GET['print'])) {
        get_template_part('inc/right');
    }
    
    if(empty($_GET['print'])) {
        get_template_part('inc/help');
    } 

    if(isset($_GET['print'])) { ?>
        <script>
            $('.sort-my-tasks').before('<?php _e('<h2>My tasks</h2>', 'wproject'); ?>');
            $('.tab-content-other-tasks').before('<?php _e('<h2>Other tasks</h2>', 'wproject'); ?>');
            $('.project-details').before('<?php _e('<h2>Project details</h2>', 'wproject'); ?>');
        </script>
    <?php }

    if(isset($_GET['print']) && $_GET['print'] && $print_hide_task_descriptions) { ?>
        <script>
            $('.more').remove();
        </script>
    <?php } 

    if($role == 'observer') { ?>
        <script>
            $( document ).ready(function() {
                $('.tab-nav, .tab-content-my-tasks').remove();
                $('.tab-content-other-tasks').addClass('active');
                $('.no-owner').text('<?php _e('Nobody', 'wproject'); ?>').css('pointer-events', 'none');
                $('.claim-task-form').removeClass();
            });
        </script>
    <?php }
    
    } else { /* End Project access logic */ ?>
        <h1>:-(</h1>
        <p><?php _e('Access to this project is limited.', 'wproject'); ?></p>
    <?php } 
    ?> 

    <?php /* View logic */
    $view = isset($_GET['view']) ? $_GET['view'] : '';
    if($view) { ?>
    <script>
        $( document ).ready(function() {
            $('.tab-content-my-tasks .body-rows .priority, .tab-content-other-tasks .body-rows .priority').hide();
            $('.body-rows li.<?php echo $view; ?>').show();

            var my_filter_count = $('.tab-content-my-tasks li.<?php echo $view; ?>').length;
            var other_filter_count = $('.tab-content-other-tasks li.<?php echo $view; ?>').length;

            $('.tab-content-my-tasks .filter-row').css('display', 'flex');
            $('.tab-content-my-tasks .filter-row .filter-type').text('<?php echo ucfirst(str_replace('-', ' ', $view)); ?>'+ ' ('+my_filter_count+')');

            $('.tab-content-other-tasks .filter-row').css('display', 'flex');
            $('.tab-content-other-tasks .filter-row .filter-type').text('<?php echo ucfirst(str_replace('-', ' ', $view)); ?>'+ ' ('+other_filter_count+')');

            /* Copy the actual filter name text into the filter type (so the translation is honoured) */
            var filter_type = $('.filter-selection li[data="<?php echo $view; ?>"] em').text();
            $('.tab-content-my-tasks .filter-type').text(filter_type + ' ('+my_filter_count+')');
            $('.tab-content-other-tasks .filter-type').text(filter_type + ' ('+other_filter_count+')');

            <?php if($view == 'incomplete') {
                /* 
                    We have to do some trickery to allow for the fact that 'incomplete' tasks include 
                    the classes not-started, incomplete, on-hold and in-progress.
                 */
            ?>
                $('.body-rows li.not-started, .body-rows li.incomplete, .body-rows li.in-progress, .body-rows li.on-hold').show();
                var my_incomplete_count = $('.tab-content-my-tasks .body-rows li.not-started, .tab-content-my-tasks .body-rows li.incomplete, .tab-content-my-tasks .body-rows li.in-progress, .tab-content-my-tasks .body-rows li.on-hold').length;
                var other_incomplete_count = $('.tab-content-other-tasks .body-rows li.not-started, .tab-content-other-tasks .body-rows li.incomplete, .tab-content-other-tasks .body-rows li.in-progress, .tab-content-other-tasks .body-rows li.on-hold').length;
                $('.tab-content-my-tasks .filter-row .filter-type').text('<?php _e('Incomplete.', 'wproject'); ?> ('+my_incomplete_count+')');
                $('.tab-content-other-tasks .filter-row .filter-type').text('<?php _e('Incomplete.', 'wproject'); ?> ('+other_incomplete_count+')');
            <?php } ?>
            
        });
    </script>
    <?php } ?>

    <?php if($pm_auto_kanban_view == 'yes' && $enable_kanban) { ?>
    <script>
        $( document ).ready(function() {
            $('.kanban, .kanban-filter').addClass('show');
        });
    </script>
    <?php } ?>

</div>

<?php get_footer(); ?>