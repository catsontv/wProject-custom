<?php if ( ! defined( 'ABSPATH' ) ) { exit; }
    $wproject_settings              = wProject(); 
    $enable_subtask_descriptions    = $wproject_settings['enable_subtask_descriptions'];
    $task_id                        = isset($_GET['task-id']) ? $_GET['task-id'] : '';
    $user_id                        = get_current_user_id();
    $task_owner_id                  = get_post_field( 'post_author', $task_id );
    $task_owner_profile             = get_the_permalink(109) . '?id=' . $task_owner_id ;

    $users_can_assign_tasks         = isset($wproject_settings['users_can_assign_tasks']) ? $wproject_settings['users_can_assign_tasks'] : '';
    $enable_time        	        = isset($wproject_settings['enable_time']) ? $wproject_settings['enable_time'] : '';

    if(function_exists('add_client_settings')) {
        $wproject_client_settings   = wProject_client();
        $client_create_own_tasks    = $wproject_client_settings['client_create_own_tasks'];
        $client_can_assign_tasks    = $wproject_client_settings['client_can_assign_tasks'];
    } else {
        $client_create_own_tasks    = '';
        $client_can_assign_tasks    = '';
    }

    $relation_tasks             = isset($wproject_settings['relation_tasks']) ? $wproject_settings['relation_tasks'] : '';

    $user                       = get_userdata(get_current_user_id());
    $user_role                  = $user->roles[0];

    $cl        	                = isset($wproject_settings['context_labels']) ? $wproject_settings['context_labels'] : '';
    $context_labels             = rtrim($cl, ', ');
    $the_context_labels         = explode(',', $context_labels);
    $the_context_labels         = array_map('trim', $the_context_labels); /* Trim whitespace */
    $context_label_display      = isset($wproject_settings['context_label_display']) ? $wproject_settings['context_label_display'] : '';

    /* If post ID exists... */
    if ( 'publish' == get_post_status ( $task_id ) ) {

    $task_owner_photo           = get_user_meta( $task_owner_id, 'user_photo' , true); 
    $task_owner_first_name      = get_user_meta( $task_owner_id, 'first_name' , true); 
    $task_owner_last_name       = get_user_meta( $task_owner_id, 'last_name' , true); 
    $task_wip 	                = get_user_meta( $task_owner_id, 'task_wip' , true );
    $task_owner_name            = $task_owner_first_name . ' ' . $task_owner_last_name;

    if(preg_match("/[a-e]/i", $task_owner_first_name)) {
        $colour = 'a-e';
    } else if(preg_match("/[f-j]/i", $task_owner_first_name)) {
        $colour = 'f-j';
    } else if(preg_match("/[k-o]/i", $task_owner_first_name)) {
        $colour = 'k-o';
    } else if(preg_match("/[p-t]/i", $task_owner_first_name)) {
        $colour = 'p-t';
    } else if(preg_match("/[u-z]/i", $task_owner_first_name)) {
        $colour = 'u-z';
    } else {
        $colour = '';
    }

    $task_name			        = get_the_title( $task_id );
    $task_description           = get_post_meta( $task_id, 'task_description', true );
    $task_priority	            = get_post_meta( $task_id, 'task_priority', true );
    $task_start_date	        = get_post_meta( $task_id, 'task_start_date', true );
    $task_end_date	            = get_post_meta( $task_id, 'task_end_date', true );
    $task_job_number	        = get_post_meta( $task_id, 'task_job_number', true );
    $task_status	            = get_post_meta( $task_id, 'task_status', true );
    $task_milestone	            = get_post_meta( $task_id, 'task_milestone', true );
    $task_private	            = get_post_meta( $task_id, 'task_private', true );
    $task_pc_complete           = get_post_meta( $task_id, 'task_pc_complete', true );
    $task_relation	            = get_post_meta( $task_id, 'task_relation', true );
    $task_related	            = get_post_meta( $task_id, 'task_related', true );
    $task_explanation	        = get_post_meta( $task_id, 'task_explanation', true );
    $task_hours	                = get_post_meta( $task_id, 'task_hours', true );
    $task_files	                = get_post_meta( $task_id, 'task_files', true );
    $subtask_list               = get_post_meta( $task_id, 'subtask_list', true );
    $subtask_descriptions_list  = get_post_meta( $task_id, 'subtask_descriptions_list', true );
    $context_label              = get_post_meta( $task_id, 'context_label', true );
    $web_page_url               = get_post_meta( $task_id, 'web_page_url', true );

    $category = get_the_terms( $task_id, 'project' );     
    if($category !='') {
        foreach ( $category as $cat){
            $category_id = $cat->term_id;
        }
    } else {
        $category_id = '';
    }
?>
<!--/ Start Edit Task /-->

<?php if($user_id == $task_owner_id && $user_role != 'client' || $user_role == 'project_manager' || $user_role == 'administrator' || $user_role == 'client' && $client_create_own_tasks && $user_id == $task_owner_id) { ?>
<form class="general-form edit-task-form" method="post" id="edit-task-form" enctype="multipart/form-data">

    <fieldset>
        <legend><?php _e('Task', 'wproject'); ?></legend>
        <ul>
            <li>
                <label><?php _e('Task name', 'wproject'); ?></label>
                <input type="text" name="task_name" id="task_name" maxlength="100" value="<?php echo $task_name; ?>" required />
            </li>
            <li>
                <label><?php _e('Description', 'wproject'); ?></label>
                <textarea name="task_description"><?php if($task_description) { echo $task_description; } ?></textarea>
            </li>
            <li>
                <label><?php _e('Project', 'wproject'); ?></label>
                <?php do_action( 'projects_selection' ); ?>
            </li>
            
            <?php if($user_role == 'project_manager' || $user_role == 'administrator' || $user_role == 'team_member' && $users_can_assign_tasks || $user_role == 'client' && $client_can_assign_tasks || $user_role == 'client' && $client_create_own_tasks) { ?>
            <li>
                <label><?php _e('Assigned to', 'wproject'); ?></label>
                <select name="task_owner" required>
                    <option value="0"><?php _e('Nobody (unowned)', 'wproject'); ?></option>
                    <?php
                        $args = array(
                            'role__in'	=> array('team_member', 'project_manager', 'client', 'administrator'),
                            'orderby'	=> 'first_name',
                            'order'   	=> 'ASC'
                        );
                        $users = get_users($args);
                        foreach ( $users as $user ) { ?>
                            <option value="<?php echo esc_html( $user->ID ); ?>" <?php if($user->ID == $task_owner_id) { echo 'selected'; } ?>><?php echo esc_html( $user->first_name ); ?> <?php echo esc_html( $user->last_name ); ?> - <?php echo esc_html( $user->title ); ?></option>
                        <?php }
                    ?>
                </select>
            </li>
            <?php } ?>
        </ul>
    </fieldset>

    <fieldset>
        <legend><?php _e('Specifics', 'wproject'); ?></legend>
        <ul>
            <li>
                <label><?php _e('Priority', 'wproject'); ?></label>
                <div class="radio-group radio-group-4">
                    <label class="<?php if($task_priority == 'low') { echo 'selected'; } ?>">
                        <input type="radio" name="task_priority" value="low" <?php if($task_priority == 'low') { echo 'checked'; } ?> /> <?php /* translators: One of 4 possible task priorities */ _e('Low', 'wproject'); ?>
                    </label>
                    <label class="<?php if($task_priority == 'normal') { echo 'selected'; } ?>">
                        <input type="radio" name="task_priority" value="normal" <?php if($task_priority == 'normal' || !$task_priority) { echo 'checked'; } ?> /> <?php /* translators: One of 4 possible task priorities */ _e('Normal', 'wproject'); ?>
                    </label>
                    <label class="<?php if($task_priority == 'high') { echo 'selected'; } ?>">
                        <input type="radio" name="task_priority" value="high" <?php if($task_priority == 'high') { echo 'checked'; } ?> /> <?php /* translators: One of 4 possible task priorities */ _e('High', 'wproject'); ?>
                    </label>
                    <label class="<?php if($task_priority == 'urgent') { echo 'selected'; } ?>">
                        <input type="radio" name="task_priority" value="urgent" <?php if($task_priority == 'urgent') { echo 'checked'; } ?> /> <?php /* translators: One of 4 possible task priorities */ _e('Urgent', 'wproject'); ?>
                    </label>
                </div>
            </li>
            <li class="split-2">
                <label><?php _e('Start & end dates', 'wproject'); ?><em class="action clear-dates"><?php _e('Clear dates', 'wproject'); ?></em></label>
                <input type="date" name="task_start_date" <?php if($task_start_date) { echo 'value="' . $task_start_date . '"'; } ?> class="merge-start" />
                <input type="date" name="task_end_date" <?php if($task_end_date) { echo 'value="' . $task_end_date . '"'; } ?> class="merge-end" />
            </li>
            <li class="side-by-side">
                <div>
                    <label><?php _e('Job #', 'wproject'); ?></label>
                    <input type="text" name="task_job_number" <?php if($task_job_number) { echo 'value="' . $task_job_number . '"'; } ?> />
                </div>
                <div>
                    <label><?php _e('% Complete', 'wproject'); ?></label>
                    <input type="number" name="task_pc_complete" min="0" max="100" value="<?php if($task_pc_complete) { echo $task_pc_complete; } ?>" class="task-pc-complete" />
                </div>
            </li>
            <li>
                <label><?php _e('Status', 'wproject'); ?></label>
                <select name="task_status" class="task-status-selector">
                    <option value="complete" <?php if($task_status == 'complete') { echo 'selected'; } ?>><?php _e('Complete', 'wproject'); ?></option>
                    <option value="incomplete" <?php if($task_status == 'incomplete') { echo 'selected'; } ?>><?php _e('Incomplete', 'wproject'); ?></option>
                    <option value="in-progress" <?php if($task_status == 'in-progress') { echo 'selected'; } ?>><?php _e('In progress', 'wproject'); ?></option>
                    <option value="not-started" <?php if($task_status == 'not-started') { echo 'selected'; } ?>><?php _e('Not started', 'wproject'); ?></option>
                    <option value="on-hold" <?php if($task_status == 'on-hold') { echo 'selected'; } ?>><?php _e('On hold', 'wproject'); ?></option>
                </select>
            </li>
            <?php if($user_role !='client') { ?>
            <li>
                <label><?php _e('Web page', 'wproject'); ?></label>
                <input type="url" name="web_page_url" <?php if($web_page_url) { echo 'value="' . $web_page_url . '"'; } ?> placeholder="<?php _e('https://', 'wproject'); ?>" />
            </li>
            <li>
                <input type="hidden" name="task_milestone" value="no" />
                <label class="<?php if($task_milestone == 'yes') { echo 'selected'; } ?>">
                    <input type="checkbox" name="task_milestone" <?php if($task_milestone == 'yes') { echo 'checked'; } ?> value="yes"  /> <span><?php _e('This task is a milestone', 'wproject'); ?></span>
                </label>
            </li>
            <li>
                <input type="hidden" name="task_private" value="no" />
                <label class="<?php if($task_private == 'yes') { echo 'selected'; } ?>">
                    <input type="checkbox" name="task_private" <?php if($task_private == 'yes') { echo 'checked'; } ?> value="yes" /> <span><?php _e('Hide the task details from other users', 'wproject'); ?></span>
                </label>
            </li>
            <?php } ?>
        </ul>
    </fieldset>

    <?php if($user_role !='client') { ?>
    <!--/ Start Relationships /-->
    <fieldset class="relationship">
        <legend><?php _e('Relationship', 'wproject'); ?></legend>
        <ul>
            <li>
                <label><?php _e('This task', 'wproject'); ?></label>
                <select name="task_relation" class="relation">
                    <option value=""></option>
                    <option value="has_issues_with" <?php if($task_relation && $task_relation == 'has_issues_with') { echo 'selected'; } ?>><?php _e('Has issues with', 'wproject'); ?></option>
                    <option value="is_blocked_by" <?php if($task_relation && $task_relation == 'is_blocked_by') { echo 'selected'; } ?>><?php _e('Is blocked by', 'wproject'); ?></option>
                    <option value="is_similar_to" <?php if($task_relation && $task_relation == 'is_similar_to') { echo 'selected'; } ?>><?php _e('Is similar to', 'wproject'); ?></option>
                    <option value="relates_to" <?php if($task_relation && $task_relation == 'relates_to') { echo 'selected'; } ?>><?php _e('Relates to', 'wproject'); ?></option>
                </select>
            </li>
            <li>
                <label></label>
                <select name="task_related" class="related" <?php if(!$task_relation) { echo 'disabled'; } ?>>
                    <option value=""></option>
                    <?php   

                        /* If setting allows tasks from all projects to be shown */
                        if($relation_tasks) {

                            $project_args = array(
                                'orderby'	 => 'name',
                                'order'		 => 'ASC',
                                'taxonomy' => 'project',
                            );
                            $projects = get_categories($project_args);
    
                            foreach($projects as $cat) {
                                $args = array(
                                    'orderby' => 'name',
                                    'order'   => 'ASC'
                                );
                                
                                $task_args = array(
                                    'posts_per_page' 	=> -1,
                                    'meta_key'          => 'task_status',
                                    'meta_value'        => array('in-complete', 'in-progress', 'on-hold', 'not-started', ''),
                                    'post_type'			=> 'task',
                                    //'post__not_in' 		=> array($task_id), /* Exclude the current post ID */
                                    'orderby' 		    => 'title', 
                                    'order' 		    => 'ASC',
                                    'tax_query' => array(
                                        array(
                                            'taxonomy' => 'project',
                                            'field'    => 'slug',
                                            'terms'    => array( $cat->name ),
                                            'operator' => 'IN'
                                        ),
                                    ),
                                );
                                $posts = get_posts($task_args);
                                if ($posts) {
                                    echo '<optgroup label="' . $cat->name . '">';
                                    foreach($posts as $post) { 
                                        setup_postdata($post); ?>
                                        <option value="<?php echo $post->ID; ?>" <?php if($post->ID == $task_related) { echo 'selected'; } ?><?php if($post->ID == $task_id) { echo 'disabled'; } ?>><?php if($post->ID == $task_id) { echo '&#9755; '; } ?><?php the_title(); ?></option>
                                    <?php
                                        
                                    }
                                    echo '</optgroup> ';
                                }
                            }

                        /* Otherwise, if setting does not allow tasks from all projects to be shown */
                        } else {

                            $categories = get_the_terms( $task_id, 'project' );
                            foreach( $categories as $category ) { 
                            }
                            $project_id = $category->term_id; 

                            $term_object = get_term( $project_id );

                            $my_tasks = array(
                                'post_type'         => 'task',
                                'post_status'		=> 'publish',
                                //'author' 			=> get_current_user_id(),
                                'category' 			=> $project_id,
                                'orderby' 			=> 'name',
                                'order' 			=> 'ASC',
                                //'post__not_in' 		=> array($task_id), /* Exclude the current post ID */
                                
                                'posts_per_page'    => -1,
                                // 'meta_query' => array(
                                //     array(
                                //         'key' => 'task_status',
                                //         'value' => array( 'in-complete', 'in-progress', 'on-hold', 'not-started', '' ),
                                //         'compare' => 'IN',
                                //     ),
                                // ),
                                'orderby' 		    => 'title', 
                                'order' 		    => 'ASC',
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
                            while ($query->have_posts()) : $query->the_post();
                            $task_status = get_post_meta( $post->ID, 'task_status', true );
                            setup_postdata($post); ?>

                                <option value="<?php echo $post->ID; ?>" <?php if($post->ID == $task_related) { echo 'selected'; } ?> <?php if($task_status == 'complete' || $post->ID == $task_id) { echo 'disabled'; } ?>><?php if($task_status == 'complete') { echo '&#10004; '; } ?><?php if($post->ID == $task_id) { echo '&#9755; '; } ?><?php the_title(); ?></option>

                            <?php endwhile;
                            wp_reset_postdata();

                        }
                    ?>
                </select>
            </li>
            <li>
                <label><?php _e('Explanation', 'wproject'); ?></label>
                <textarea name="task_explanation" class="explanation" <?php if(!$task_relation) { echo 'disabled'; } ?>><?php if($task_explanation) { echo $task_explanation; } ?></textarea>
            </li>
        </ul>
    </fieldset>
    <!--/ End Relationships /-->
    <?php } ?>

    <!--/ Start Files /-->
    <fieldset>
        <legend><?php _e('Files', 'wproject'); ?><span class="files-count"></span></legend>
        <ul>
            <li>
                <label><?php _e('Attach more files', 'wproject'); ?></label>
                <input type="file" name="task_files[]" multiple="multiple" class="file-input" />
                <style>
                    .file-input:before {
                        content: '<?php _e('Browse', 'wproject'); ?>';
                    }
                </style>
            </li>
            <li>
                <?php 
                $file_count = '0';
                $attachments = get_posts( array(
                    'post_parent'    => $task_id,
                    'post_type'      => 'attachment',
                    'posts_per_page' => -1,
                    'orderby'        => 'menu_order',
                    'order'          => 'ASC',
                ) );
                $i = 1;
                echo '<ul class="files-list">';
                sort($attachments); /* Sort alphabetically */
                foreach ( $attachments as $attachment ) { 
                    $class      = "post-attachment mime-" . sanitize_title( $attachment->post_mime_type );
                    $is_image   = wp_attachment_is_image($attachment->ID);
                    $file_type   = wp_check_filetype(wp_get_attachment_url($attachment->ID));
                    ?>
                    <li class="<?php echo $class; ?>" title="<?php echo get_the_title($attachment->ID); ?>" id="file-<?php echo $attachment->ID; ?>">
                        <?php if($is_image == 1) { ?>
                            <a data="<?php echo wp_get_attachment_url($attachment->ID); ?>" class="file-image">
                                <img src="<?php echo wp_get_attachment_thumb_url($attachment->ID); ?>" />
                            </a>
                        <?php } else { ?>
                            <em class="file-type-icon">
                                <a href="<?php echo wp_get_attachment_url($attachment->ID); ?>" download>
                                    <img src="<?php echo get_template_directory_uri();?>/images/file.svg" class="file-icon" />
                                </a>
                            </em>
                        <?php } ?>
                        <strong><?php echo get_the_title($attachment->ID); ?></strong>
                    </li>
                    
                <?php 
                $file_count = $i++;
                }
                echo '</ul>';
                wp_reset_postdata(); ?>
                
                <script>
                    /* Basic lightbox */
                    $( document ).ready(function() {
                            
                        $('.files-count').text('<?php echo $file_count; ?>');
                        
                        $('.file-image').click(function() {
                            $('.mask').addClass('show');
                            var url = $(this).attr('data');
                            $('.mask, .image-container').addClass('show');
                            $('.image-container').append('<img src="'+url+'" />');
                        });
                        
                        $('.mask, .image-container').click(function() {
                            $('.mask, .image-container').removeClass('show');
                            $('.image-container img').remove();
                        });

                    });
                </script>
            </li>
        </ul>
    </fieldset>
    <!--/ End Files /-->

    <!--/ Start Subtasks /-->
    <fieldset>
        <legend><?php _e('Subtasks', 'wproject'); ?><span class="subtask-count"></span></legend>
        <p class="add-item"><i data-feather="plus-square" class="remove"></i> <?php _e('Add subtask', 'wproject'); ?></p>
        <ul class="subtask-items materials <?php if($enable_subtask_descriptions) { echo 'has-descriptions'; } ?>">     
        <?php 
            $all_rows = get_post_meta((int)$task_id, 'subtask_list', true);

            if ($all_rows) {
                if (count($all_rows) > 0) {
                    sort($all_rows); /* Sort alphabetically */
                    foreach ($all_rows as $s_row) {

                        $subtask_name           = isset($s_row['subtask_name']) ? $s_row['subtask_name'] : '';
                        $subtask_description    = isset($s_row['subtask_description']) ? $s_row['subtask_description'] : '';
                        $subtask_status         = isset($s_row['subtask_status']) ? $s_row['subtask_status'] : '';
                        
                        ?>
                        <li class="item <?php if ($subtask_status == '1') { echo 'done'; } ?>">
                            <span>
                                <input type="text" name="subtask_name[]" placeholder="<?php _e('Subtask', 'wproject'); ?>" data-lpignore="true" value="<?php echo $subtask_name; ?>" required />
                                <?php if($enable_subtask_descriptions) { ?><textarea name="subtask_description[]" placeholder="<?php _e('Description', 'wproject'); ?>"><?php echo $subtask_description; ?></textarea><?php } ?>
                                <input type="hidden" name="subtask_status[]" value="<?php echo $subtask_status ?>" />
                            </span>
                            <span class="remove" title="<?php _e('Remove (double click)', 'wproject'); ?>"><i data-feather="x"></i></span>
                        </li>
                        <?php
                    }
                }
            }
        ?>

        </ul>
        <?php if($subtask_list) { ?>
        <script>
            $( document ).ready(function() {
                $('.subtask-count').text('<?php echo count($all_rows ); ?>');
            });
        </script>
        <?php } ?>
    </fieldset>
    <!--/ End Subtasks /-->

    <!--/ Start Context Label /-->
    <fieldset>
        <legend><?php _e('Context label', 'wproject'); ?></legend>
        <ul>
            <li class="no-margin">
                
                <?php if($context_label_display == 'dropdown' || $context_label_display == '') { ?>
                    <label><?php _e('Add context', 'wproject'); ?></label>
                    <select name="context_label">
                        <option></option>
                        <?php 
                            sort($the_context_labels);
                            foreach($the_context_labels as $value) {
                                if($context_label == str_replace('-', ' ', trim($value))) {
                                    $selected = 'selected';
                                } else {
                                    $selected = '';
                                }
                                echo '<option value="' . str_replace('-', ' ', trim($value)) . '"' . $selected . '>' . str_replace('-', ' ', trim($value)) . '</option>';
                            }
                        ?>
                    </select>

                <?php } else { ?>

                    <div class="radio-group radio-group-2">
                        <?php 
                            sort($the_context_labels);
                            foreach($the_context_labels as $value) {
                                if($context_label == str_replace('-', ' ', trim($value))) {
                                    $checked = 'checked';
                                    $selected = 'selected';
                                } else {
                                    $checked = '';
                                    $selected = '';
                                }
                                echo '<label class="' . $selected . ' radio">';
                                echo '<input type="radio" name="context_label" value="' . str_replace('-', ' ', trim($value)) . '"' . $checked . ' />' . str_replace('-', ' ', trim($value));
                                echo '</label>';
                            }
                        ?>
                    </div>

                <?php } ?>
            </li>
        </ul>
    </fieldset>
    <!--/ End Context Label /-->

    <input type="hidden" name="task_id" value="<?php echo $task_id; ?>" />
    <input type="hidden" name="temp_field" class="temp_field" value="" />
    <input type="hidden" id="ajax_url" name="" value="<?php echo admin_url('admin-ajax.php'); ?>">

    <div class="submit">
        <button><?php _e('Save Changes', 'wproject'); ?></button>
    </div>

    <script type='text/javascript' src='<?php echo get_template_directory_uri();?>/js/min/form-submission-check.min.js'></script>

</form>
<script>
    /* 
        When using the file input, populate a hidden field with any value.
        Doing this because type=file does not seem to work with serialized (js/form-submission-check.js).
    */
    $('input[type="file"], .add-item').click(function() {
        $('.temp_field').val('1');
    });

    $('.relation').change(function() {

        var relationship = $('select[name=relation] option').filter(':selected').val();

        if ($(this).val() === '') {
            $('.relationship .explanation').attr('disabled','disabled');
            $('.relationship .relation, .relationship .related, .relationship textarea').val('');
            $('.relationship .related').attr('disabled', 'disabled');
        } else {
            $('.relationship .related').removeAttr('disabled');
            $('.relationship .related').attr('required', 'required');
            $().text();
        }
        if(relationship == 'is_blocked') {
            $( '.relationship .this-task-text' ).text( '<?php _e('Is blocked by', 'wproject');?>:' );
        } else if(relationship == 'is_similar') {
            $( '.relationship .this-task-text' ).text( '<?php _e('Is similar to', 'wproject');?>:' );
        } else if(relationship == 'has_issues') {
            $( '.relationship .this-task-text' ).text( '<?php _e('Has issues with', 'wproject');?>:' );
        } else if(relationship == 'relates') {
            $( '.relationship .this-task-text' ).text( '<?php _e('Relates to', 'wproject');?>:' );
        } else {
            $( '.relationship .this-task-text' ).text( '' );
        }
    });

    $('.related').change(function() {
        if ($(this).val() != '') {
            $('.relationship .explanation').removeAttr('disabled');
        }
        if ($(this).val() === '') {
            $('.relationship .explanation').attr('disabled','disabled');
            $('.relationship .relation, .relationship .related, .relationship textarea').val('');
            $('.relationship .related').attr('disabled', 'disabled');
            $('.relationship .this-task-text').text( '' );
        }
    });

    /* Subtask handling */
    var trash_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x feather-icon" color="#ff9800"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';

    $(window).load(function() {
        $(function() {
            var subtaskItems = $('.subtask-items');
            var i = $('.subtask-items li').size() + 1;
            
            $('.add-item').click(function() {
                $('<li class="item"><span><input type="text" name="subtask_name[]" placeholder="<?php _e('Subtask', 'wproject'); ?>" data-lpignore="true" required /><?php if($enable_subtask_descriptions) { ?><textarea name="subtask_description[]" placeholder="<?php _e('Additional information', 'wproject'); ?>"></textarea><?php } ?><input type="hidden" name="subtask_status[]" value="0" /></span><span class="remove" title="<?php _e('Remove (double click)', 'wproject'); ?>">'+trash_icon+'</span></li>').prependTo(subtaskItems);
                i++;
            });
            
            $('.subtask-items').on('dblclick', '.remove', function() {
                $('.subtask-items').find(this).parent().remove();
                $('.subtask-count').fadeOut();
                $('.temp_field').val(1); // Add value to temp hidden field to release submit button
                //return false;
            });
        });
    })

    /* 
        Change the task status to complete if task percentage is 100, 
        and swap it back to original status when less than 100.
    */
    var currentTaskStatus = $('.task-status').val();
    $('.task-pc-complete').change(function() {
        if ($(this).val() == '100') {
            $('.task-status').val('complete');
        } else {
            $('.task-status').val(currentTaskStatus);
        }
    });
    $('.task-status').change(function() {
        if ($(this).val() == 'complete') {
            $('.task-pc-complete').val('100');
        } else {
            $('.task-pc-complete').val(currentTaskStatus);
        }
    });

    /* Clear date fields */
    $('.clear-dates').hide();

    <?php if($task_start_date || $task_end_date) { ?>
        $('.clear-dates').show();
    <?php } ?>

    $('input[type="date"]').change(function() {
        $('.clear-dates').fadeIn();
    });
    $('.clear-dates').click(function() {
        $('input[type="date"]').val('');
    });

</script>
<script type='text/javascript' src='<?php echo get_template_directory_uri();?>/js/min/form-submission-check.min.js'></script>
<!--/ End Edit Task /-->

<?php } else { ?>

    <?php if($task_owner_id =='0') { ?>

        <p><?php _e( "You can't edit this task.", 'wproject' ); ?></p>

    <?php } else { ?>

    <?php if($task_owner_photo) {
            $avatar         = $task_owner_photo;
            $avatar_id      = attachment_url_to_postid($avatar);
            $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
            $avatar         = $small_avatar;
            $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" />';
        } else {
            $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . $task_owner_first_name . $task_owner_last_name . '</div>';
        }

    ?>
        <div class="no-access">
            <?php echo $the_avatar; ?>
            <?php if($user_role == 'client' && !$client_create_own_tasks) { ?>
                <p><?php _e(  "You can't edit this task.", 'wproject' ); ?></p>
                <script>
                    $('.no-access .avatar').text('');
                </script>
            <?php } else { ?>
                <p><?php printf( __( 'Oops! This task can only be edited by <a href="%1$s">%2$s</a>.', 'wproject' ), $task_owner_profile, $task_owner_name ); ?></p>
            <?php } ?>
        </div>

        
    <?php } ?>

<?php } ?>


<?php /* Help topics */
function new_task_help() { ?>
    <h4><?php _e('Task', 'wproject'); ?></h4>
    <p><?php _e('The bare minimum details of the task. A task name and project must be specified.', 'wproject'); ?></p>

    <h4><?php _e('Specifics', 'wproject'); ?></h4>
    <p><?php _e('Further information about this task. You can also hide this task from other users.', 'wproject'); ?></p>

    <h4><?php _e('Relationship', 'wproject'); ?></h4>
    <p><?php _e('Is this task related to another? If so, specify the relationship here.', 'wproject'); ?></p>

    <h4><?php _e('Files', 'wproject'); ?></h4>
    <p><?php _e('Attach files to the task.', 'wproject'); ?></p>

    <h4><?php _e('Subtasks', 'wproject'); ?></h4>
    <p><?php _e('Allow the task to be comprised of smaller, granular tasks.', 'wproject'); ?></p>

    <h4><?php _e('Context label', 'wproject'); ?></h4>
    <p><?php _e('Give this task some context.', 'wproject'); ?></p>
<?php }
add_action('help_start', 'new_task_help');

/* Side nav items */
function new_task_nav() {
    $task_id            = isset($_GET['task-id']) ? $_GET['task-id'] : '';
    $task_owner_id      = get_post_field( 'post_author', $task_id );
    $current_user_id    = get_current_user_id();
?>
    <li><a href="<?php echo get_the_permalink($task_id);?>"><i data-feather="check-circle-2"></i><?php _e('Go to task', 'wproject'); ?></a></li>
    <li><a href="<?php foreach ( get_the_terms( $task_id, 'project' ) as $tax ) { echo home_url() . '/project/' .  $tax->slug; } ?>"><i data-feather="folder"></i><?php _e('Go to project', 'wproject'); ?></a></li>
    
    <?php if($task_owner_id == $current_user_id) { ?>
    <form class="delete-task-form" id="delete-task-form" method="post" enctype="multipart/form-data">
        <li><a><i data-feather="x-circle"></i><?php _e('Delete this task', 'wproject'); ?></a></li>
        <input type="hidden" name="task_id" value="<?php echo $task_id; ?>" />
        <script>
            $(document).on('click', '.delete-task-form', function() {
                if (confirm('<?php _e('Delete this task and any time recorded on it?', 'wproject'); ?>')) {

                    setTimeout(function() { 
                        $('#delete-task-form').submit();
                    }, 250);

                }
            });
        </script>
    </form>

<?php }
}
add_action('side_nav', 'new_task_nav');

/* ..otherwise if post ID does not exist, redirect to the 404 page */
} else { ?>
    <script type='text/javascript'>document.location.href='<?php echo home_url(); ?>/404';</script>
<?php }