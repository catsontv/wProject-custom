<?php if ( ! defined( 'ABSPATH' ) ) { exit; }

    $wproject_settings              = wProject();
    $users_can_create_tasks         = $wproject_settings['users_can_create_tasks'];
    $enable_leave_warning           = $wproject_settings['enable_leave_warning'];
    $job_number_prefix              = $wproject_settings['job_number_prefix'];
    $users_can_assign_tasks         = $wproject_settings['users_can_assign_tasks'];
    $enable_subtask_descriptions    = $wproject_settings['enable_subtask_descriptions'];
    
    $cl                             = $wproject_settings['context_labels'];
    $context_labels                 = rtrim($cl, ', ');
    $the_context_labels             = explode(',', $context_labels);
    $the_context_labels             = array_map('trim', $the_context_labels); /* Trim whitespace */
    $context_label_display          = $wproject_settings['context_label_display'];

    if(function_exists('add_client_settings')) {
        $wproject_client_settings   = wProject_client();
        $client_create_own_tasks    = $wproject_client_settings['client_create_own_tasks'];
        $client_can_assign_tasks    = $wproject_client_settings['client_can_assign_tasks'];
    } else {
        $client_create_own_tasks    = '';
        $client_can_assign_tasks    = '';
    }

    $user                       = wp_get_current_user();
    $user_role                  = $user->roles[0];

    $user_info                  = get_userdata(get_current_user_id());
    $default_task_ownership     = $user_info->default_task_ownership;

    $the_total_projects_count   = all_projects_count();

    $project_id = '';
    if(isset($_GET['project-id'])) {
        $project_id = $_GET['project-id'];
    }

    if($the_total_projects_count['count'] > 0) { 

    if(
        $users_can_create_tasks == 'on' && $user_role != 'client' ||        /* Allow users if option is enabled, and if user is not a client */
        $user_role              == 'client' && $client_create_own_tasks ||  /* Allow clients if option to create own tasks is enabled */
        $user_role              == 'project_manager' ||                     /* Allow Project managers */
        $user_role              == 'administrator'                          /* Allow Administrators */
    ) {
?>


    <!--/ Start New Project /-->
    <form class="general-form new-task-form" method="post" id="new-task-form" enctype="multipart/form-data">

        <!--/ Start Task Details /-->
        <fieldset>
            <legend><?php _e('Task', 'wproject'); ?></legend>
            <ul>
                <li>
                    <label><?php _e('Task name', 'wproject'); ?></label>
                    <input type="text" name="task_name" id="task_name" class="text" maxlength="100" required />
                </li>
                <li>
                    <label><?php _e('Description', 'wproject'); ?></label>
                    <textarea name="task_description"></textarea>
                </li>
                <li>
                    <label><?php _e('Project', 'wproject'); ?></label>
                    <?php do_action( 'projects_selection' ); ?>
                </li>
                <li>
                    <label><?php _e('Assigned to', 'wproject'); ?></label>
                    <?php do_action( 'task_assignment' ); ?>
                </li>
            </ul>
        </fieldset>
        <!--/ End Task Details /-->

        <!--/ Start Task Specifics /-->
        <fieldset>
            <legend><?php _e('Specifics', 'wproject'); ?></legend>
            <ul>
                <li>
                    <label><?php _e('Priority', 'wproject'); ?></label>
                    <div class="radio-group radio-group-4">
                        <label>
                            <input type="radio" name="task_priority" value="low" /> <?php /* translators: One of 4 possible task priorities */ _e('Low', 'wproject'); ?>
                        </label>
                        <label>
                            <input type="radio" name="task_priority" value="normal" checked /> <?php /* translators: One of 4 possible task priorities */ _e('Normal', 'wproject'); ?>
                        </label>
                        <label>
                            <input type="radio" name="task_priority" value="high" /> <?php /* translators: One of 4 possible task priorities */ _e('High', 'wproject'); ?>
                        </label>
                        <label>
                            <input type="radio" name="task_priority" value="urgent" /> <?php /* translators: One of 4 possible task priorities */ _e('Urgent', 'wproject'); ?>
                        </label>
                    </div>
                </li>
                <li class="split-2">
                    <label><?php _e('Start & end dates', 'wproject'); ?><em class="action clear-dates"><?php _e('Clear dates', 'wproject'); ?></em></label>
                    <input type="date" name="task_start_date" class="pick-start-date merge-start" min="<?php echo date('Y-m-d'); ?>" />
                    <input type="date" name="task_end_date" class="pick-end-date merge-end" min="<?php echo date('Y-m-d'); ?>" />
                </li>
                <li class="side-by-side">
                    <div>
                        <label><?php _e('Job #', 'wproject'); ?></label>
                        <input type="text" name="task_job_number" value="<?php if($job_number_prefix) { echo $job_number_prefix; } ?>" />
                    </div>
                    <div>
                        <label><?php _e('% Complete', 'wproject'); ?></label>
                        <input type="number" name="task_pc_complete" min="0" max="100" value="0" class="task-pc-complete" />
                    </div>
                </li>
                <li>
                    <label><?php _e('Status', 'wproject'); ?></label>
                    <select name="task_status" class="task-status-selector">
                        <option value="complete"><?php _e('Complete', 'wproject'); ?></option>
                        <option value="in-progress"><?php _e('In progress', 'wproject'); ?></option>
                        <option value="not-started" selected><?php _e('Not started', 'wproject'); ?></option>
                        <option value="on-hold"><?php _e('On hold', 'wproject'); ?></option>
                    </select>
                </li>
                <?php if($user_role !='client') { ?>
                <li>
                    <label><?php _e('Web page', 'wproject'); ?></label>
                    <input type="url" name="web_page_url" placeholder="<?php _e('https://', 'wproject'); ?>" />
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="task_milestone" /> <span><?php _e('This task is a milestone', 'wproject'); ?></span>
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="task_private" /> <span><?php _e('Hide the task details from other users', 'wproject'); ?></span>
                    </label>
                </li>
                <?php } ?>

            </ul>
        </fieldset>
        <!--/ End Task Specifics /-->

        <?php if($user_role !='client') { ?>
        <!--/ Start Task Relationship /-->
        <fieldset class="relationship">
            <legend><?php _e('Relationship', 'wproject'); ?></legend>
            <ul>
                <li>
                    <label><?php _e('This task', 'wproject'); ?></label>
                    <select name="task_relation" class="relation">
                        <option value=""></option>
                        <option value="has_issues_with"><?php _e('Has issues with', 'wproject'); ?></option>
                        <option value="is_blocked_by"><?php _e('Is blocked by', 'wproject'); ?></option>
                        <option value="is_similar_to"><?php _e('Is similar to', 'wproject'); ?></option>
                        <option value="relates_to"><?php _e('Relates to', 'wproject'); ?></option>
                    </select>
                </li>
                <li>
                    <label></label>
                    <select name="task_related" class="related" disabled>
                        <option value=""></option>
                        <?php
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
                                    'post__not_in' 		=> array(get_the_ID()), /* Exclude the current post ID */
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
                                        <option value="<?php echo $post->ID; ?>"><?php the_title(); ?></option>
                                    <?php
                                    }
                                    echo '</optgroup> ';
                                }
                            }
                        ?>
                    </select>
                </li>
                <li>
                    <label><?php _e('Explanation', 'wproject'); ?></label>
                    <textarea name="task_explanation" class="explanation" disabled></textarea>
                </li>
            </ul>
        </fieldset>
        <!--/ End Task Relationship /-->
        <?php } ?>


        <!--/ Start Files /-->
        <fieldset>
            <legend><?php _e('Files', 'wproject'); ?><span class="files-count"></span></legend>
            <ul>
                <li class="no-margin">
                    <label><?php _e('Attach files', 'wproject'); ?></label>
                    <input type="file" name="task_files[]" id="task_files" multiple="multiple" class="file-input" />
                    <style>
                        .file-input:before {
                            content: '<?php _e('Browse', 'wproject'); ?>';
                        }
                    </style>
                </li>
            </ul>
        </fieldset>
        <!--/ End Files /-->


        <!--/ Start Subtasks /-->
        <fieldset>
            <legend><?php _e('Subtasks', 'wproject'); ?></legend>
            <p class="add-item"><i data-feather="plus-square" class="remove"></i> <?php _e('Add subtask', 'wproject'); ?></p>
            <ul class="subtask-items materials <?php if($enable_subtask_descriptions) { echo 'has-descriptions'; } ?>">            
            </ul>
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
                                    echo '<option value="' . str_replace('-', ' ', trim($value)) . '">' . str_replace('-', ' ', trim($value)) . '</option>';
                                }
                            ?>
                        </select>

                    <?php } else { ?>
                        
                        <div class="radio-group radio-group-2">
                        <?php 
                            sort($the_context_labels);
                            foreach($the_context_labels as $value) {
                                echo '<label class="radio">';
                                echo '<input type="radio" name="context_label" value="' . str_replace('-', ' ', trim($value)) . '" />' . str_replace('-', ' ', trim($value));
                                echo '</label>';
                            }
                        ?>
                        </div>

                    <?php } ?>

                </li>
            </ul>
        </fieldset>

        <!--/ End Context Label /-->

        <?php do_action( 'new_task_end' ); ?>

        <input type="hidden" name="task_takeover_request" />
        <input type="hidden" id="ajax_url" name="" value="<?php echo admin_url('admin-ajax.php'); ?>">
        <input type="hidden" id="initiator_id" name="initiator_id" value="<?php echo get_current_user_id(); ?>">

        <div class="submit">
            <button><?php _e('Create task', 'wproject'); ?></button>
        </div>

    </form>

    <?php do_action( 'new_task_after_form' ); ?>

    <script>
        $('.relation').change(function() {

            var relationship = $('select[name=relation] option').filter(':selected').val();

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
        var delete_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff9800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';

        $(window).load(function() {
            $(function() {
                var subtaskItems = $('.subtask-items');
                var i = $('.subtask-items li').size() + 1;
                
                $('.add-item').click(function() {
                    $('<li class="item"><span><input type="text" name="subtask_name[]" data-lpignore="true" placeholder="<?php _e('Subtask', 'wproject'); ?>" required /><?php if($enable_subtask_descriptions) { ?><textarea name="subtask_description[]" placeholder="<?php _e('Additional information', 'wproject'); ?>"></textarea><?php } ?><input type="hidden" name="subtask_status[]" value="0" /></span><span class="remove" title="<?php _e('Remove (double click)', 'wproject'); ?>">'+delete_icon+'</span></li>').prependTo(subtaskItems);
                    i++;
                });
                
                $('.subtask-items').on('dblclick', '.remove', function() {
                    $('.subtask-items').find(this).parent().remove();
                    updateTotal();
                    return false;
                });
            });
        });

        /* 
            Change the task status to complete if task percentage is 100, 
            and swap it back to original status when less than 100.
        */
        var currentTaskStatus = $('.task-status-selector').val();
        jQuery('.task-pc-complete').change(function() {
            if (jQuery(this).val() == '100') {
                jQuery('.task-status-selector').val('complete');
            } else {
                jQuery('.task-status-selector').val(currentTaskStatus);
            }
        });
        jQuery('.task-status-selector').change(function() {
            if (jQuery(this).val() == 'complete') {
                jQuery('.task-pc-complete').val('100');
            } else {
                jQuery('.task-pc-complete').val(currentTaskStatus);
            }
        });

        /* Focus task name input */
        //jQuery('#task_name').focus();

        /* Clear date fields */
        jQuery('.clear-dates').hide();
        jQuery('input[type="date"]').change(function() {
            jQuery('.clear-dates').fadeIn();
        });
        jQuery('.clear-dates').click(function() {
            jQuery('input[type="date"]').val('');
            jQuery('input[type="date"]').trigger('change');
        });

    </script>

    <script type='text/javascript' src="<?php echo get_template_directory_uri(); ?>/js/date-picker-logic.js" id="date-picker-logic"></script>

    <?php leave_warning() ?>

    <!--/ End New Project /-->
    <?php } else { ?>
        <p class="info"><i data-feather="alert-triangle"></i><?php _e('The ability to create new tasks has not been enabled. Discuss this with your project manager or an administrator.', 'wproject'); ?></p>
    <?php } ?>

<?php } else { ?>
    <p class="info"><i data-feather="alert-triangle"></i><?php _e('There needs to be at least one project before you can create a task.', 'wproject'); ?></p>
    <?php if(
        $users_can_create_tasks == 'on' || 
        $user_role              == 'project_manager' || 
        $user_role              == 'administrator'
    ) { ?>
    <a href="<?php echo get_the_permalink(104); ?>" class="btn-light"><?php _e('Create a project', 'wproject'); ?></a>
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

    <h4><?php _e('Subtasks', 'wproject'); ?></h4>
    <p><?php _e('Allow the task to be comprised of smaller, granular tasks.', 'wproject'); ?></p>

    <h4><?php _e('Context label', 'wproject'); ?></h4>
    <p><?php _e('Give this task some context.', 'wproject'); ?></p>
<?php }
add_action('help_start', 'new_task_help');

/* Side nav items */
function new_task_nav() { 
    
    $project_id = '';
    if(isset($_GET['project-id'])) {
        $project_id = $_GET['project-id'];
    }
    ?>

    <?php if($project_id !='') { ?>
    <li><a href="<?php echo home_url(); ?>/project/<?php echo get_term( $project_id )->slug; ?>"><i data-feather="arrow-left-circle"></i><span class="spawn"><?php _e('Back to ', 'wproject'); ?><?php echo get_term( $project_id )->name; ?></a></li>
    <?php } ?>

    <li><a href="<?php echo home_url(); ?>/"><i data-feather="x-circle"></i><?php _e('Discard', 'wproject'); ?></a></li>
<?php }
add_action('side_nav', 'new_task_nav');