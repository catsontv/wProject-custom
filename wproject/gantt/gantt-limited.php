<?php if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Limited Gantt View for clients - only allow viewing projects the client is involved in */

?>

<div class="gantt"></div>
<div class="toggle-gantt-fs">
    <i data-feather="maximize"></i>
</div>
<div class="toggle-gantt-visibility">
    <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 24 24" xml:space="preserve"><style>.st0{fill:none;stroke:#ff9800;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}</style><path class="st0" d="M4 18h10M4 12h16M14 6h6"/></svg>
</div>

<link href="<?php echo get_template_directory_uri();?>/gantt/css/gantt.css" type="text/css" rel="stylesheet">
<script src="<?php echo get_template_directory_uri();?>/gantt/js/jquery.fn.gantt.js"></script>
<script src="<?php echo get_template_directory_uri();?>/gantt/js/bootstrap.min.js"></script>

<?php function popup_centre() { ?>
    <style>
    .popover {
        position: absolute;
        top: 0;
        left: 0;
        z-index: 99999;
        display: block;
        min-width: 270px;
        max-width: 330px;
        border-radius: 2px;
        margin: -10px 0 0 0;
    }

    .popover .arrow {
        display: block;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 14px 7px 0 7px;
        border-color: #fff transparent transparent transparent;
        position: absolute;
        bottom: -14px;
        margin: 0 0 0 -7px;
    }
    </style>
<?php }

function popup_fixed() { ?>
    <style>
    .popover {
        position: fixed;
        top: 0 !important;
        right: 0 !important;
        left: auto !important;
        width: 300px;
        height: 100%;
    }
    .popover li {
        font-size: 1.3em;
        padding: 6px 0;
    }
    </style>
<?php }



function ganttProject() { /* Gantt chart on a project page */
    
    $wproject_settings      = wProject();

    $gantt_scale_tasks      = $wproject_settings['gantt_scale_tasks'];
    $gantt_pagination       = $wproject_settings['gantt_pagination'];
    $gantt_hide_completed   = $wproject_settings['gantt_hide_completed'];
    $gantt_jump_to_today    = $wproject_settings['gantt_jump_to_today'];
    $gantt_show_popup       = $wproject_settings['gantt_show_popup'];
    $gantt_show_subtasks    = $wproject_settings['gantt_show_subtasks'];
    $gantt_popup_position   = $wproject_settings['gantt_popup_position'];
    $enable_time            = $wproject_settings['enable_time'];


    if(function_exists('add_client_settings')) {
        $wproject_client_settings   = wProject_client();
        $client_view_others_tasks   = $wproject_client_settings['client_view_others_tasks'];
    } else {
        $wproject_client_settings   = '';
        $client_view_others_tasks   = '';
    }

    if($gantt_scale_tasks) {
        $gantt_scale_tasks = $gantt_scale_tasks;
    } else {
        $gantt_scale_tasks = 'days';
    }
    
    if($gantt_pagination) {
        $gantt_pagination = $gantt_pagination;
    } else {
        $gantt_pagination = 10;
    }
 
    if($gantt_jump_to_today == 'on') {
        $gantt_jump_to_today = 'true';
    } else {
        $gantt_jump_to_today = 'false';
    }

    if($gantt_hide_completed == 'on') {
        $gantt_hide_completed = 'complete';
    }
    
    if($gantt_popup_position == 'center') {
        $the_gantt_popup_position = 'center';
    } else if($gantt_popup_position == 'fixed') {
        $the_gantt_popup_position = 'fixed';
    } else {
        $the_gantt_popup_position = 'auto';
    }
?>

<script>
    $(function() {
        "use strict";

        var wProjectChart = [

            <?php 
            $current_author             = get_current_user_id();
            $date_format                = get_option('date_format');
            $term_id		            = get_queried_object()->term_id; 
            $term_meta                  = get_term_meta($term_id); 
            $term_object 	            = get_term( $term_id ); 
            $current_term               = get_term_by( 'id', $term_id, 'project' );
            $description                = $current_term->description;
            $project_full_description   = $term_meta['project_full_description'][0];
        
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
            $query = new WP_Query($args);
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
            $task_owner             = get_the_author_meta('user_firstname',$author_id) . " " . get_the_author_meta('user_lastname',$author_id);
            $first_name             = get_the_author_meta('user_firstname',$author_id);
            $last_name              = get_the_author_meta('user_lastname',$author_id);

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

            $user_photo         = get_the_author_meta( 'user_photo', $author_id );
            if($user_photo) {
                $avatar         = $user_photo;
                $avatar_id      = attachment_url_to_postid($avatar);
                $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                $avatar         = $small_avatar[0];
                $the_avatar     = "<img src='" . $small_avatar[0] . "' class='avatar' />";
            } else {
                $the_avatar     = "<div class='letter-avatar avatar '" . $colour . "'>" . $first_name[0] . $last_name[0] . "</div>";
            }

            $milliseconds_start     = 1000 * strtotime($task_start_date);
            $new_start_date         = new DateTime($task_start_date);
            $the_task_start_date    = $new_start_date->format($date_format);

            $milliseconds_end       = 1000 * strtotime($task_end_date);
            $new_end_date           = new DateTime($task_end_date);
            $the_task_end_date      = $new_end_date->format($date_format);

            if($task_time) {
                $task_time = $task_time;
            } else {
                $task_time = '0';
            }

            if($task_private == 'yes') {
                if($author_id == $current_author) {
                    $task_title = get_the_title();
                    $task_link = get_the_permalink();
                } else {
                    $task_title = 'Private task';
                    $task_link = '#';
                }
            } else {
                $task_title = get_the_title();
                $task_link = get_the_permalink();
            }

            if($task_priority == 'low' || !$task_priority) {
                $the_task_priority = 'Low';
                $class = 'ganttBlue';
            } else if($task_priority == 'normal') {
                $the_task_priority = 'Normal';
                $class = 'ganttGreen';
            } else if($task_priority == 'high') {
                $the_task_priority = 'High';
                $class = 'ganttOrange';
            } else if($task_priority == 'urgent') {
                $the_task_priority = 'Urgent';
                $class = 'ganttRed';
            } 
            
            if($task_status == 'complete') {
                $the_task_status = __('Complete', 'wproject');
                $class = 'ganttComplete'; 
            } else if($task_status == 'incomplete') {
                $the_task_status = __('Incomplete', 'wproject');
            } else if($task_status == 'on-hold') {
                $the_task_status = __('On hold', 'wproject');
            } else if($task_status == 'in-progress') {
                $the_task_status = __('In progress', 'wproject');
            } else {
                $the_task_status = __('Not started', 'wproject');
            }

            /* Clients can view other tasks */
                if($task_start_date && $task_end_date && $task_status != $gantt_hide_completed) { 
                    if($client_view_others_tasks == 'on') { ?>
                {
                name: "<a href='<?php echo $task_link; ?>'><?php echo $task_title; ?></a>",
                desc: "",
                values: [{
                    from: <?php echo $milliseconds_start; ?>,
                    to: <?php echo $milliseconds_end; ?>,
                    label: "<?php echo $task_title; ?>",
                    desc: "",
                    customClass: "<?php echo $class; ?>",
                    dataObj: {
                        title: "<?php echo $task_title; ?><?php echo $the_avatar; ?>", 
                        content: "<ul><li><strong><?php _e('Owner', 'wproject'); ?>: </strong><?php echo $task_owner; ?></li><li><strong><?php _e('Start date', 'wproject'); ?>: </strong><?php echo $the_task_start_date; ?></li><li><strong><?php _e('Due', 'wproject'); ?>: </strong><?php echo $the_task_end_date; ?></li><li><strong><?php _e('Priority', 'wproject'); ?>: </strong><?php echo $the_task_priority; ?></li><?php if($enable_time) { ?><li><strong><?php _e('Time', 'wproject'); ?>: </strong><?php echo $task_time; ?></li><?php } ?><li><strong><?php _e('Status', 'wproject'); ?>: </strong><?php echo $the_task_status; ?></li><?php if($task_job_number) { ?><li><strong><?php _e('Job #', 'wproject'); ?>: </strong><?php echo $task_job_number; ?></li><?php } ?></ul><?php if($gantt_popup_position == 'fixed') { ?><?php if($task_description) { ?><p><?php echo str_replace(array("\r","\n"),"",$task_description); ?></p><?php } ?><?php } ?>"
                    }
                }]
                },
            <?php } else { ?>
                <?php if(get_current_user_id() == $author_id) { ?>
                /* Clients can NOT view other tasks */
                {
                name: "<a href='<?php echo $task_link; ?>'><?php echo $task_title; ?></a>",
                desc: "",
                values: [{
                    from: <?php echo $milliseconds_start; ?>,
                    to: <?php echo $milliseconds_end; ?>,
                    label: "<?php echo $task_title; ?>",
                    desc: "",
                    customClass: "<?php echo $class; ?>",
                    dataObj: {
                        title: "<?php echo $task_title; ?><?php echo $the_avatar; ?>", 
                        content: "<ul><li><strong><?php _e('Owner', 'wproject'); ?>: </strong><?php echo $task_owner; ?></li><li><strong><?php _e('Start date', 'wproject'); ?>: </strong><?php echo $the_task_start_date; ?></li><li><strong><?php _e('Due', 'wproject'); ?>: </strong><?php echo $the_task_end_date; ?></li><li><strong><?php _e('Priority', 'wproject'); ?>: </strong><?php echo $the_task_priority; ?></li><?php if($enable_time) { ?><li><strong><?php _e('Time', 'wproject'); ?>: </strong><?php echo $task_time; ?></li><?php } ?><li><strong><?php _e('Status', 'wproject'); ?>: </strong><?php echo $the_task_status; ?></li><?php if($task_job_number) { ?><li><strong><?php _e('Job #', 'wproject'); ?>: </strong><?php echo $task_job_number; ?></li><?php } ?></ul><?php if($gantt_popup_position == 'fixed') { ?><?php if($task_description) { ?><p><?php echo str_replace(array("\r","\n"),"",$task_description); ?></p><?php } ?><?php } ?>"
                    }
                }]
                },
            <?php } } ?>
                <?php if($gantt_show_subtasks) {
                if($subtask_list) { 
                    $subtask_rows = get_post_meta( (int)$task_id, 'subtask_list', true); 
                    if($subtask_rows) { 
                        if( count($subtask_rows ) > 0  ) { 
                        sort($subtask_rows); 
                        foreach( $subtask_rows as $subtask ) { 
                        if($subtask['subtask_status'] != '1') { ?>
                        {
                        desc: "<?php echo $subtask['subtask_name']; ?>",
                        values: [{
                            label: "",
                            customClass: "subtask"
                        }]
                },
            
            <?php } } } } } } ?>
            
            <?php } endwhile;
                    wp_reset_postdata();
                ?>
            ];

            $(".gantt").gantt({
                source:         wProjectChart,
                navigate:       "scroll",
                scale:          "<?php echo $gantt_scale_tasks; ?>",
                maxScale:       "months",
                minScale:       "hours",
                waitText:       "<?php _e('Reloading chart...', 'wproject'); ?>",
                itemsPerPage:   <?php echo $gantt_pagination; ?>,
                scrollToToday:  <?php echo $gantt_jump_to_today; ?>,
                useCookie:      false,
                <?php if($gantt_show_subtasks) { ?>
                onRender: function () {
                    //$('.fn-label:empty, .bar subtask').parent().remove();
                },
                <?php } ?>
                months:         ["<?php /* translators: Month name */ _e('January', 'wproject'); ?>", "<?php /* translators: Month name */ _e('February', 'wproject'); ?>", "<?php /* translators: Month name */ _e('March', 'wproject'); ?>", "<?php /* translators: Month name */ _e('April', 'wproject'); ?>", "<?php /* translators: Month name */ _e('May', 'wproject'); ?>", "<?php /* translators: Month name */ _e('June', 'wproject'); ?>", "<?php /* translators: Month name */ _e('July', 'wproject'); ?>", "<?php /* translators: Month name */ _e('August', 'wproject'); ?>", "<?php /* translators: Month name */ _e('September', 'wproject'); ?>", "<?php /* translators: Month name */ _e('October', 'wproject'); ?>", "<?php /* translators: Month name */ _e('November', 'wproject'); ?>", "<?php /* translators: Month name */ _e('December', 'wproject'); ?>"],
                dow:            ["<?php /* translators: Abbreviated of the week */ _e('Sun', 'wproject'); ?>", "<?php /* translators: Abbreviated of the week */ _e('Mon', 'wproject'); ?>", "<?php /* translators: Abbreviated of the week */ _e('Tue', 'wproject'); ?>", "<?php /* translators: Abbreviated of the week */ _e('Wed', 'wproject'); ?>", "<?php /* translators: Abbreviated of the week */ _e('Thu', 'wproject'); ?>", "<?php /* translators: Abbreviated of the week */ _e('Fri', 'wproject'); ?>", "<?php /* translators: Abbreviated of the week */ _e('Sat', 'wproject'); ?>"]
            });

            <?php if($gantt_show_popup == 'on') { ?>
                $(".gantt").popover({
                    selector: ".bar",
                    title: function() {
                        return $(this).data('dataObj').title;
                    },
                    content: function() {
                        return $(this).data('dataObj').content;
                    },
                    placement: '<?php echo $the_gantt_popup_position; ?>',
                    trigger: "hover",
                    container: '.gantt',
                    html: true
                }
            );
            <?php } ?>
        });
    $('.toggle-gantt-fs').click(function() {
        $('.gantt').toggleClass('fs');
        $('.toggle-gantt-fs').toggleClass('move');
    });
    
</script>

<?php 
    if($gantt_popup_position == 'center') {
        popup_centre();
    } else if($gantt_popup_position == 'fixed') {
        popup_fixed();
    } else {
        popup_centre();
    }
?>
<?php } ?>

<?php function ganttAllProjects() { /* Gantt chart on the all projects page */

    $wproject_settings      = wProject();

    $gantt_scale_projects   = $wproject_settings['gantt_scale_projects'];
    $gantt_pagination       = $wproject_settings['gantt_pagination'];
    $gantt_hide_completed   = $wproject_settings['gantt_hide_completed'];
    $gantt_jump_to_today    = $wproject_settings['gantt_jump_to_today'];
    $gantt_show_popup       = $wproject_settings['gantt_show_popup'];
    $gantt_popup_position   = $wproject_settings['gantt_popup_position'];

    if($gantt_scale_projects) {
        $gantt_scale_projects = $gantt_scale_projects;
    } else {
        $gantt_scale_projects = 'weeks';
    }

    if($gantt_pagination) {
        $gantt_pagination = $gantt_pagination;
    } else {
        $gantt_pagination = 10;
    }

    if($gantt_jump_to_today == 'on') {
        $gantt_jump_to_today = 'true';
    } else {
        $gantt_jump_to_today = 'false';
    }

    if($gantt_hide_completed == 'on') {
        $gantt_hide_completed = 'complete';
    }

    if($gantt_popup_position == 'center') {
        $the_gantt_popup_position = 'center';
    } else if($gantt_popup_position == 'fixed') {
        $the_gantt_popup_position = 'fixed';
    } else {
        $the_gantt_popup_position = 'auto';
    }

?>
<script>
    $(function() {
        "use strict";

        var wProjectChart = [

        <?php 
        $date_format = get_option('date_format'); 
        
        /* Prepare Gantt items */
        $user_id = get_current_user_id();
        global $wpdb;
        $client_categories = $wpdb->get_results("
            SELECT DISTINCT(terms.term_id) as ID, terms.name, terms.slug, tax.description
            FROM $wpdb->posts as posts
            LEFT JOIN $wpdb->term_relationships as relationships ON posts.ID = relationships.object_ID
            LEFT JOIN $wpdb->term_taxonomy as tax ON relationships.term_taxonomy_id = tax.term_taxonomy_id
            LEFT JOIN $wpdb->terms as terms ON tax.term_id = terms.term_id
            LEFT JOIN $wpdb->termmeta as termmeta ON terms.term_id = termmeta.term_id AND termmeta.meta_key = 'project_status'
            WHERE 
            posts.post_status = 'publish' AND 
            posts.post_type = 'task' AND 
            tax.taxonomy = 'project' AND 
            termmeta.meta_value != 'archived' AND 
            termmeta.meta_value != 'cancelled' AND 
            termmeta.meta_value != 'inactive' AND 
            posts.post_author = '$user_id'
            ORDER BY terms.name ASC
        ");

        foreach($client_categories as $client_category) : 
            
            $term_id                    = $client_category->ID; 
            $term_meta                  = get_term_meta($term_id); 
            $term_object                = get_term( $term_id );
            $project_status             = $term_meta['project_status'][0];
            $current_term               = get_term_by( 'id', $term_id, 'project' );
            $description                = $client_category->description;
            $project_full_description   = $term_meta['project_full_description'][0];
            $project_start_date         = $term_meta['project_start_date'][0];
            $project_end_date           = $term_meta['project_end_date'][0];
            $project_job_number         = $term_meta['project_job_number'][0];
            $project_time_allocated     = $term_meta['project_time_allocated'][0];
            $project_hourly_rate        = $term_meta['project_hourly_rate'][0];
            $pm_user                    = get_user_by('ID', $term_meta['project_manager'][0]);
            $pm_name                    = $pm_user->first_name . ' ' . $pm_user->last_name;
            $pm_photo                   = $pm_user->user_photo;
            $first_name                 = $pm_user->first_name;
            $last_name                  = $pm_user->last_name;

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

            if($pm_photo) {
                $avatar         = $pm_photo;
                $avatar_id      = attachment_url_to_postid($avatar);
                $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                $avatar         = $small_avatar[0];
                $the_avatar     = "<img src='" . $small_avatar[0] . "' class='avatar' />";
            } else {
                $the_avatar     = "<div class='letter-avatar avatar '" . $colour . "'>" . $first_name[0] . $last_name[0] . "</div>";
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
            $the_project_end_date      = $new_end_date->format($date_format);

            $budget = '-';
            if($project_time_allocated && $project_hourly_rate) {
                $budget = $project_time_allocated * $project_hourly_rate;
            }

            /* Project status */
            if($project_status == 'in-progress') {
                $the_project_status = __('In progress', 'wproject');
                $class = 'ganttDefault';
            } else if($project_status == 'planning') {
                $the_project_status = __('Planning', 'wproject');
                $class = 'ganttDefault';
            } else if($project_status == 'proposed') {
                $the_project_status = __('Proposed', 'wproject');
                $class = 'ganttDefault';
            } else if($project_status == 'setting-up') {
                $the_project_status = __('Setting up', 'wproject');
                $class = 'ganttDefault';
            } else if($project_status == 'archived') {
                $the_project_status = __('Archived', 'wproject');
                $class = 'ganttDefault';
            } else if($project_status == 'cancelled') {
                $the_project_status = __('Cancelled', 'wproject');
                $class = 'ganttDefault';
            } else if($project_status == 'complete') {
                $the_project_status = __('Complete', 'wproject');
                $class = 'ganttComplete'; 
            }
            
            if($project_start_date && $project_end_date) {
        ?>
        {
        /* Display the left column Gantt items */
        name: "<a href='<?php echo get_category_link( $client_category->ID ); ?>'><?php echo $client_category->name ?></a>",
        desc: "",
        values: [{
            from: <?php echo $milliseconds_start; ?>,
            to: <?php echo $milliseconds_end; ?>,
            label: "<?php echo $client_category->name ?>",
            desc: "",
            customClass: "<?php echo $class; ?>",
            dataObj: {
                title: "<?php echo $client_category->name ?><?php echo $the_avatar; ?>", 
                content: "<ul><li><strong><?php _e('Project manager', 'wproject'); ?>: </strong><?php echo $pm_name; ?></li><li><strong><?php _e('Start date', 'wproject'); ?>: </strong><?php echo $the_project_start_date; ?></li><li><strong><?php _e('Due', 'wproject'); ?>: </strong><?php echo $the_project_end_date; ?></li><li><strong><?php _e('Time allocated', 'wproject'); ?>: </strong><?php echo $project_time_allocated; ?> <?php /* Abbreviation of 'hours': */ _e( 'hrs', 'wproject' ); ?></li><li><strong><?php _e('Hourly rate', 'wproject'); ?>: </strong>$<?php echo $project_hourly_rate; ?></li><li><strong><?php _e('Budget', 'wproject'); ?>: </strong>$<?php echo $budget; ?></li><li><strong><?php _e('Status', 'wproject'); ?>: </strong><?php echo $the_project_status; ?></li><?php if($project_job_number) { ?><li><strong><?php _e('Job #', 'wproject'); ?>: </strong><?php echo $project_job_number; ?></li><?php } ?></ul><?php if($gantt_popup_position == 'fixed') { ?><?php if($description) { ?><p><strong><?php echo str_replace(array("\r", "\n"), '', $description); ?></strong></p><?php } ?><?php if($project_full_description) { ?><p><?php echo str_replace(array("\r", "\n"), '', $project_full_description); ?></p><?php } ?><?php } ?>"
            }
        }]
        },
        <?php 
            }
        endforeach;
        wp_reset_postdata(); ?>

        ];

        $(".gantt").gantt({
            source:         wProjectChart,
            navigate:       "scroll",
            scale:          "<?php echo $gantt_scale_projects; ?>",
            maxScale:       "months",
            minScale:       "hours",
            waitText:       "<?php _e('Reloading chart...', 'wproject'); ?>",
            itemsPerPage:   <?php echo $gantt_pagination; ?>,
            scrollToToday:  <?php echo $gantt_jump_to_today; ?>,
            useCookie:      false,
            months:         ["<?php _e('January', 'wproject'); ?>", "<?php _e('February', 'wproject'); ?>", "<?php _e('March', 'wproject'); ?>", "<?php _e('April', 'wproject'); ?>", "<?php _e('May', 'wproject'); ?>", "<?php _e('June', 'wproject'); ?>", "<?php _e('July', 'wproject'); ?>", "<?php _e('August', 'wproject'); ?>", "<?php _e('September', 'wproject'); ?>", "<?php _e('October', 'wproject'); ?>", "<?php _e('November', 'wproject'); ?>", "<?php _e('December', 'wproject'); ?>"],
            dow:            ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"]
        });

        <?php if($gantt_show_popup == 'on') { ?>
            $(".gantt").popover({
                selector: ".bar",
                title: function() {
                    return $(this).data('dataObj').title;
                },
                content: function() {
                    return $(this).data('dataObj').content;
                },
                placement: '<?php echo $the_gantt_popup_position; ?>',
                trigger: "hover",
                container: '.gantt',
                html: true
            }
        );
        <?php } ?>
    });

    /* Toggle full screen Gantt */
    $('.toggle-gantt-fs').click(function() {
        $('.gantt').toggleClass('fs');
        $('.toggle-gantt-fs').toggleClass('move');
    });
    
</script>

<?php 
    if($gantt_popup_position == 'center') {
        popup_centre();
    } else if($gantt_popup_position == 'fixed') {
        popup_fixed();
    } else {
        popup_centre();
    }
?>

<?php } ?>

<?php if ( is_tax()) {
    ganttProject();
} else if(is_page(106) || ('is_front_page()')) { // Projects page or Front page
    ganttAllProjects();
}
?>

<script>
    /* Gantt toggles */
    $( document ).ready(function() {
    
        $(document).on('click', '.toggle-gantt-visibility', function() {
            $('.gantt, .toggle-gantt-fs, .toggle-gantt-visibility').fadeOut();
            $('header .icons .show-gantt').hide();
            $('header .icons').prepend('<li class="show-gantt"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#5b606c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-watch feather-icon" stroke="#5b606c"><path d="M4 18h10M4 12h16M14 6h6"/></svg></li>');
            Cookies.set('gantt-state', 'hidden');
        });
        $(document).on('click', '.icons .show-gantt', function() {
            $('.gantt, .toggle-gantt-fs, .toggle-gantt-visibility').fadeIn();
            $('header .icons .show-gantt').hide();
            Cookies.set('gantt-state', 'visible');
        });

        if(Cookies.get('gantt-state') == 'hidden') {
            $('.gantt, .toggle-gantt-fs, .toggle-gantt-visibility').hide();
            $('header .icons').prepend('<li class="show-gantt"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#5b606c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-watch feather-icon" stroke="#5b606c"><path d="M4 18h10M4 12h16M14 6h6"/></svg></li>');
            Cookies.set('gantt-state', 'hidden');
        } else if(Cookies.get('gantt-state') == 'visible') {
            $('.gantt, .toggle-gantt-fs, .gantt-state').show();
        }
    });

</script>