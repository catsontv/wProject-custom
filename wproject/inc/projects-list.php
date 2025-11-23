<!--/ Start Projects Gantt /-->
<?php if ( ! defined( 'ABSPATH' ) ) { exit; }
$term_id                        = get_queried_object()->term_id;
$current_author                 = get_current_user_id();
$user 					        = wp_get_current_user();
$user_role 				        = $user->roles[0];
$hide_gantt                     = get_user_meta( $current_author, 'hide_gantt' , true );
$pm_only_show_my_projects       = get_user_meta( $current_author, 'pm_only_show_my_projects' , true );

$wproject_settings              = wProject();

if(function_exists('add_client_settings')) {
    $wproject_client_settings = wProject_client();
    $client_project_access          = $wproject_client_settings['client_project_access'];
} else {
    $client_project_access          = '';
}

$gantt_show_all_project_page    = $wproject_settings['gantt_show_all_project_page'];
$enable_time                    = $wproject_settings['enable_time'];
$project_access                 = $wproject_settings['project_access'];

if(
    $user_role == 'team_member' && $project_access == 'all' || 
    $user_role == 'administrator' || 
    $user_role == 'project_manager' || 
    $user_role == 'observer' || 
    $user_role == 'client' && $client_project_access == 'unlimited'
    ) { 

    if(function_exists('gantt_pro_dashboard')) {
        do_action('gantt_pro_dashboard_page');
    } else {
        $gantt_show_dashboard = $wproject_settings['gantt_show_dashboard'];
        if($hide_gantt !='yes' && $gantt_show_dashboard == 'on' && empty($_GET['print'])) {
            get_template_part('gantt/gantt');
        }
    }
?>
<!--/ End Projects Gantt /-->

<!--/ Start Projects List /-->
<div class="rows all-projects">
    
    <?php if(!wp_is_mobile()) { ?>
    <ul class="header-row">
        <li><i data-feather="folder"></i><?php _e('Project', 'wproject'); ?></li>
        <li><i data-feather="check-circle-2"></i><?php _e('Tasks', 'wproject'); ?></li>
        <li><i data-feather="calendar"></i><?php _e('Due', 'wproject'); ?></li>
        <li><i data-feather="dollar-sign"></i><?php _e('Budget', 'wproject'); ?></li>
        <li><i data-feather="activity"></i><?php _e('Status', 'wproject'); ?></li>
    </ul>
    <?php } ?>

    <ul class="body-rows <?php if(wp_is_mobile()) { echo 'mobile'; } ?>">
        <?php 
            $date_format = get_option('date_format'); 
            $now        = strtotime('today');
            
            $projects = array(
                'taxonomy'      => 'project',
                'hide_empty'    => 0,
                'orderby'       => 'name',
                'post_status'   => 'publish',
                'order'         => 'ASC',
                'hierarchical'  => 0
            );
            $cats = get_categories($projects);
            
            $tasks_query = new WP_Query( $projects );
            $project_count = $tasks_query->found_posts;

            foreach($cats as $cat) {
                $term_id                    = $cat->term_id; 
                $term_meta                  = get_term_meta($term_id); 
                $term_object                = get_term( $term_id );
                $description                = term_description($term_id);
                $project_status             = $term_meta['project_status'][0];
                $project_start_date         = $term_meta['project_start_date'][0];
                $project_end_date           = $term_meta['project_end_date'][0];
                $project_time_allocated     = $term_meta['project_time_allocated'][0];
                $project_hourly_rate        = $term_meta['project_hourly_rate'][0];
                $pm_user                    = get_user_by('ID', $term_meta['project_manager'][0]);
                $pm_name                    = $pm_user->first_name . ' ' . $pm_user->last_name;
                $pm_id                      = $pm_user->ID;

                $project_created_date       = $term_meta['project_created_date'][0] ?? null;
                $new_project_created_date   = new DateTime($project_created_date);
                $the_project_created_date   = $new_project_created_date->format($date_format);

                /* Overdue check and class */
                $due_date		            = strtotime($project_end_date);
                $overdue_class = '';
                if($now && $due_date && $now > $due_date) {
                    $overdue_class = 'overdue'; 
                }

                if($project_start_date && $project_end_date) {
                    $new_project_start_date = new DateTime($project_start_date);
                    $the_project_start_date = $new_project_start_date->format($date_format);

                    $new_project_end_date   = new DateTime($project_end_date);
                    $the_project_end_date   = $new_project_end_date->format($date_format);
                } else {
                    $the_project_start_date = '';
                    $the_project_end_date = '';
                }

                $project_hourly_rate = '';
                if(isset($term_meta['project_hourly_rate'][0])) {
                    $project_hourly_rate = $term_meta['project_hourly_rate'][0];
                }

                $project_time_allocated = '';
                if(isset($term_meta['project_time_allocated'][0])) {
                    $project_time_allocated = $term_meta['project_time_allocated'][0];
                }

                $project_job_number = '';
                if(isset($term_meta['project_job_number'][0])) {
                    $project_job_number = $term_meta['project_job_number'][0];
                }

                $project_materials_total = '';
                if(isset($term_meta['project_materials_total'][0])) {
                    $project_materials_total = $term_meta['project_materials_total'][0];
                }

                $budget = '';
                if($project_time_allocated && $project_hourly_rate) {
                    $budget = $project_time_allocated * $project_hourly_rate;
                }
                
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
            ?>
            <li class="<?php echo $overdue_class; ?> <?php echo $project_status; ?> project-id-<?php echo $cat->term_id; ?> pm-user-id-<?php echo $pm_id; ?> <?php if($pm_id != get_current_user_id()) { echo 'not-mine'; } ?>">
                <span class="project-name project-<?php echo $cat->term_id; ?>">
                    <a href="<?php echo get_category_link( $cat->term_id ) ?>"><strong><?php echo $cat->name; ?></strong></a>
                    <?php if($description) { ?><em><?php echo wp_trim_words( strip_tags($description), 10, '...' ); ?></em><?php } ?>
                </span>

                <?php if(wp_is_mobile()) { ?>
                <span class="progress-bar">
                    <em><?php echo round($progress, 1); ?>%</em>
                    <b style="width: <?php echo round($progress, 1); ?>%" <?php if($progress == '100') { echo 'class="complete"'; } ?>)></b>
                </span>
                <?php } ?>

                <span class="project-task-count"><?php echo $completed_tasks_project; ?>/<?php echo $cat->count; ?></span>
                <?php if($project_end_date) { ?>
                <span class="date"><?php echo $the_project_end_date; ?></span>
                <?php } else { ?>
                    <?php if(!wp_is_mobile()) { ?>
                        <span></span>
                    <?php } ?>
                <?php } ?>
                
                <?php if($budget) { ?>
                    <span>
                        <?php if($wproject_settings['currency_symbol_position'] == 'l') { echo $wproject_settings['currency_symbol']; } ?><?php echo $budget; ?><?php if($wproject_settings['currency_symbol_position'] == 'r') { echo $wproject_settings['currency_symbol']; } ?>
                    </span>
                <?php } else { ?>
                    <?php if(!wp_is_mobile()) { ?>
                        <span></span>
                    <?php } ?>
                <?php } ?>

                <?php /* Project status */
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
                ?>
                
                <span class="status"><em><?php echo $the_project_status; ?></em></span>
                <span class="project-info">
                    <strong><?php /* Translators: Abbreviation of Project Manager: */ _e('PM', 'wproject'); ?>:</strong> <em><?php echo $pm_name; ?></em>

                    <?php if($project_job_number) { ?>
                        <strong><?php _e('Job #', 'wproject'); ?>:</strong> <em><?php echo $project_job_number; ?></em>
                    <?php } ?>

                    <?php if($project_materials_total) { ?>
                        <strong><?php _e('Materials', 'wproject'); ?>:</strong> 
                        <em>
                            <?php if($wproject_settings['currency_symbol_position'] == 'l') { echo $wproject_settings['currency_symbol']; } ?><?php echo $project_materials_total; ?><?php if($wproject_settings['currency_symbol_position'] == 'r') { echo $wproject_settings['currency_symbol']; } ?>
                            <?php } ?>
                        </em>
                    <?php if($project_time_allocated) { ?>
                        <strong><?php _e('Time allocated', 'wproject'); ?>:</strong> <em><?php echo $project_time_allocated; ?> <?php /* Translators: Abbreviation of 'hours': */ _e( 'hrs', 'wproject' ); ?></em>
                    <?php } ?>

                    <?php if($project_created_date) { ?>
                        <strong><?php _e('Created', 'wproject'); ?>:</strong> <em><?php echo $the_project_created_date; ?></em>
                    <?php } ?>


                    <?php 
                    $args = array(
                        'numberposts' 	=> -1,
                        'post_status' 	=> 'publish',
                        'post_type'     => 'task',
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'project',
                                'field'    => 'term_id',
                                'terms'    => $term_id
                            ),
                        ), 
                    );
                    $all_times = get_posts( $args );

                    

                    if($enable_time) { 
                        
                        $project_total	= get_term_meta($term_id, 'project_total_time', TRUE);
                        if($project_total) {
                            $hours              = floor($project_total / 3600);
                            $mins               = floor(($project_total / 60) % 60);
                            $seconds            = $project_total % 60;
                        } else {
                            $hours              = '00';
                            $mins               = '00';
                            $seconds            = '00';
                        }

                        if($project_total > 0 ) {
                        ?>
                        <strong><?php _e('Time', 'wproject'); ?>:</strong> 
                        <em><?php printf("%02d:%02d:%02d", $hours, $mins, $seconds); ?></em>
                    <?php } 
                    } ?>


                    <?php if($project_hourly_rate) { ?>
                        <strong><?php _e('Hourly rate', 'wproject'); ?>:</strong> 
                        <em><?php if($wproject_settings['currency_symbol_position'] == 'l') { echo $wproject_settings['currency_symbol']; } ?><?php echo $project_hourly_rate; ?><?php if($wproject_settings['currency_symbol_position'] == 'r') { echo $wproject_settings['currency_symbol']; } ?></em>
                    <?php } ?>
                </span>
                <?php if(!wp_is_mobile()) { ?>
                <span class="progress-bar">
                    <em><?php echo round($progress, 1); ?>%</em>
                    <b style="width: <?php echo round($progress, 1); ?>%" <?php if($progress == '100') { echo 'class="complete"'; } ?>></b>
                </span>
                <?php } ?>
            </li>
            <?php
            }
        ?>
    </ul>   
    <script>
        <?php if($pm_only_show_my_projects == 'yes') { ?>
            $('.not-mine').remove();
        <?php } ?>
    </script>
</div>

<!--/ End Projects List /-->
<?php } else { ?>
    <p><?php _e('Access to this page is limited.', 'wproject'); ?></p>
<?php } 
?>

<?php /* Help topics */
function all_project_help() { 
    if(empty($_GET['print'])) { 
        $current_author                 = get_current_user_id();
        $pm_only_show_my_projects       = get_user_meta( $current_author, 'pm_only_show_my_projects' , true );
    ?>
    <?php if($pm_only_show_my_projects != 'yes') { ?>
        <h4>All listed projects</h4>
        <p><?php _e('Projects managed by all project managers are displayed here.', 'wproject'); ?></p>
        <p><?php _e('To limit this view to only see projects that you manage, change the setting at the <a href="/account/">account page</a>.', 'wproject'); ?></p>
        <p><?php _e('Use the status filters to quickly change the project view.', 'wproject'); ?></p>
    <?php } else { ?>
        <h4>Projects</h4>
        <p><?php _e('Only projects that you manage are displayed here.', 'wproject'); ?></p>
        <p><?php _e('To change this view to see all projects, change the setting at the <a href="/account/">account page</a>.', 'wproject'); ?></p>
        <p><?php _e('Use the status filters to quickly change the project view.', 'wproject'); ?></p>
    <?php } ?>
<?php }
}
add_action('help_start', 'all_project_help');

/* Side nav items */
function all_projects_nav() { 

    $user 					        = wp_get_current_user();
    $current_author                 = get_current_user_id();
    $pm_only_show_my_projects       = get_user_meta( $current_author, 'pm_only_show_my_projects' , true );
    $user_role 				        = $user->roles[0];
    $wproject_settings              = wProject();
    $enable_time                    = $wproject_settings['enable_time'];
    $project_access                 = $wproject_settings['project_access'];

    if($project_access == 'all' || $user_role == 'administrator' || $user_role == 'project_manager') { 

    if(empty($_GET['print'])) { ?>
    <?php if($pm_only_show_my_projects != 'yes') { ?>
    <li class="projects-filter projects-filter-all selected" data="all"><a><i data-feather="chevron-right"></i><?php _e('All projects', 'wproject'); ?></a></li>
    <li class="projects-filter projects-filter-mine" data="mine"><a><i data-feather="chevron-right"></i><?php _e('My projects', 'wproject'); ?></a></li>
    <?php } ?>
    <li class="projects-filter" data="archived"><a><i data-feather="chevron-right"></i><?php _e('Archived', 'wproject'); ?></a></li>
    <li class="projects-filter" data="cancelled"><a><i data-feather="chevron-right"></i><?php _e('Cancelled', 'wproject'); ?></a></li>
    <li class="projects-filter" data="complete"><a><i data-feather="chevron-right"></i><?php _e('Complete', 'wproject'); ?></a></li>
    <li class="projects-filter" data="in-progress"><a><i data-feather="chevron-right"></i><?php _e('In progress', 'wproject'); ?></a></li>
    <li class="projects-filter" data="planning"><a><i data-feather="chevron-right"></i><?php _e('Planning', 'wproject'); ?></a></li>
    <li class="projects-filter" data="proposed"><a><i data-feather="chevron-right"></i><?php _e('Proposed', 'wproject'); ?></a></li>
    <li class="projects-filter" data="overdue"><a><i data-feather="chevron-right"></i><?php _e('Overdue', 'wproject'); ?></a></li>
    <li class="projects-filter" data="setting-up"><a><i data-feather="chevron-right"></i><?php _e('Setting up', 'wproject'); ?></a></li>
<?php }
    }
}
add_action('side_nav', 'all_projects_nav');