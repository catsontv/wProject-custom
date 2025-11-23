<?php if ( ! defined( 'ABSPATH' ) ) { exit; } 
$wproject_settings          = wProject(); 
$job_number_prefix          = $wproject_settings['job_number_prefix'];
$enable_time                = $wproject_settings['enable_time'];
$currency_symbol            = $wproject_settings['currency_symbol'];
$currency_symbol_position   = $wproject_settings['currency_symbol_position'];
$pep_talk_percentage        = $wproject_settings['pep_talk_percentage'];
$pep_talk_message           = $wproject_settings['pep_talk_message'];
$user                       = wp_get_current_user();
$user_role                  = $user->roles[0];
$project_name               = isset($_GET['project_name']) ? $_GET['project_name'] : '';

if($currency_symbol) {
    $currency_symbol = $currency_symbol;
} else {
    $currency_symbol = '$';
}
?>

<?php if($user_role == 'project_manager' || $user_role == 'administrator') { ?>

<!--/ Start New Project /-->
<form class="general-form new-project-form" method="post" id="new-project-form" enctype="multipart/form-data">
    <fieldset>
        <legend><?php _e('Project', 'wproject'); ?></legend>
        <ul>
            <li>
                <label><?php _e('Project name', 'wproject'); ?></label>
                <input type="text" name="project_name" id="project_name" required />
            </li>
            <li>
                <label><?php _e('Brief description', 'wproject'); ?></label>
                <input type="text" name="description" />
            </li>
            <li>
                <label><?php _e('Full description', 'wproject'); ?></label>
                <textarea name="project_full_description"></textarea>
            </li>
            <li>
                <label><?php _e('Status', 'wproject'); ?></label>
                <select name="project_status" required>
                    <option></option>
                    <option value="in-progress"><?php _e( 'In progress', 'wproject' ); ?></option>
                    <option value="planning"><?php _e( 'Planning', 'wproject' ); ?></option>
                    <option value="proposed"><?php _e( 'Proposed', 'wproject' ); ?></option>	
                    <option value="setting-up"><?php _e( 'Setting up', 'wproject' ); ?></option>
                </select>
            </li>
        </ul>
    </fieldset>

    <fieldset class="specifics">
        <legend><?php _e('Specifics', 'wproject'); ?></legend>
        <ul>
            <li class="split-2">
                <label><?php _e('Start & end dates', 'wproject'); ?><em class="action clear-dates"><?php _e('Clear dates', 'wproject'); ?></em></label>
                <input type="date" name="project_start_date" class="pick-start-date merge-start" min="<?php echo date('Y-m-d'); ?>" />
                <input type="date" name="project_end_date" class="pick-end-date merge-end" min="<?php echo date('Y-m-d'); ?>" />
            </li>

            <?php if($enable_time) { ?>
            <li class="split-2">
                <label><?php _e('Time & Rate', 'wproject'); ?></label>
                <input type="number" min="1" step="1" class="half merge-start" name="project_time_allocated" placeholder="Hrs allocated" />

                <input type="number" min="1" step=".5" name="project_hourly_rate" id="project-hourly-rate" placeholder="<?php echo $currency_symbol; ?> per hour" class="half merge-end" value="<?php if($wproject_settings['default_project_rate']) { echo $wproject_settings['default_project_rate']; } ?>" />
            </li>
            <?php } ?>

            <li>
                <label><?php _e('Job #', 'wproject'); ?></label>
                <input type="text" name="project_job_number" value="<?php if($job_number_prefix) { echo $job_number_prefix; } ?>" />
            </li>
            <li>
                <label><?php _e('Project manager', 'wproject'); ?></label>
                <select name="project_manager" required>
                    <option></option>
                    <?php
                        $users = get_users( array( 'role__in' => array( 'project_manager', 'administrator' ) ) );
                        foreach ( $users as $user ) { ?>
                            <option value="<?php echo esc_html( $user->ID ); ?>" <?php if($user->ID == get_current_user_id()) { echo 'selected'; } ?>><?php echo esc_html( $user->first_name ); ?> <?php echo esc_html( $user->last_name ); ?> - <?php echo esc_html( $user->title ); ?></option>
                        <?php }
                    ?>
                </select>
            </li>
            <li>
                <label><?php _e('Web page', 'wproject'); ?></label>
                <input type="url" name="web_page_url" placeholder="<?php _e('https://', 'wproject'); ?>" />
            </li>
            <?php do_action( 'specifics_list_end' ); ?>
        </ul>
    </fieldset>

    <fieldset class="materials">
        <legend><?php _e('Materials', 'wproject'); ?></legend>
        <p class="add-item"><i data-feather="plus-square" class="remove"></i> <?php _e('Add material item', 'wproject'); ?></p>
        <ul class="material-items">            
        </ul>
        <div class="materials-total">
            <span>
                <?php _e('Cost', 'wproject'); ?>: 
                <?php if($currency_symbol_position == 'l') { echo $currency_symbol; } ?><span class="materials-tally"></span><?php if($currency_symbol_position == 'r') { echo $currency_symbol; } ?>
            </span>
            <input type="hidden" name="project_materials_total" class="project-materials-total" readonly />
        </div>
    </fieldset>

    <fieldset>
        <legend><?php _e('Task Group', 'wproject'); ?></legend>

        <?php $count_task_groups = wp_count_posts( 'task_group' )->publish; ?>

        <ul>
            <?php if ( $count_task_groups > 0 ) { ?>
                <li>
                    <label><?php _e('Include tasks from this group', 'wproject'); ?></label>
                    <select name="task_group" id="task_group">
                        <option></option>
                        <?php $args = array(
                        'post_type'         => 'task_group',
                        'orderby'           => 'title',
                        'order'             => 'asc',
                        'post_parent' 	    => 0,
                        'posts_per_page'    => -1
                        );
                        $query = new WP_Query($args);
                        while ($query->have_posts()) : $query->the_post(); ?>
                        <option value="<?php echo get_the_ID(); ?>"><?php echo the_title(); ?></option>
                        <?php endwhile;
                        wp_reset_postdata(); ?>
                    </select>
                </li>

                <li class="task-ownership">
                    <label><?php _e('Task ownership', 'wproject'); ?></label>
                    <select name="task_group_owner" id="task_group_owner">
                        <option class="unchanged" value="unchanged"><?php _e('Unchanged', 'wproject'); ?></option>
                        <option class="remove" value="remove"><?php _e('Remove ownership', 'wproject'); ?></option>
                        <optgroup label="Assign all tasks in this project to:">
                        <?php
                            $args = array(
                                'role__in'	=> array('team_member', 'project_manager', 'administrator'),
                                'orderby'	=> 'first_name',
                                'order'   	=> 'ASC'
                            );
                            $users = get_users($args);
                            foreach ( $users as $user ) { ?>
                                <option value="<?php echo esc_html( $user->ID ); ?>"><?php echo esc_html( $user->first_name ); ?> <?php echo esc_html( $user->last_name ); ?> - <?php echo esc_html( $user->title ); ?></option>
                            <?php }
                        ?>
                        </optgroup>
                    </select>
                    <script>
                        jQuery('.task-ownership').hide();
                        jQuery('#task_group').change(function() {
                            if (jQuery('#task_group').val() != '') {
                                jQuery('.task-ownership').fadeIn();
                                jQuery('#task_group_owner').attr('required','required');
                            } else {
                                jQuery('.task-ownership').fadeOut();
                                jQuery('#task_group_owner').removeAttr('required');
                            }
                            if(jQuery(this).val() == 'task-contacts') {
                                jQuery('#task_group_owner .unchanged').attr('disabled','disabled');
                                jQuery('#task_group_owner .remove').prop('selected', true);
                            } else {
                                jQuery('#task_group_owner .unchanged').removeAttr('disabled');
                                jQuery('#task_group_owner .remove').prop('selected', false);
                            }
                        });
                    </script>
                </li>
                
            <?php } else { ?>

                <p><?php _e( 'There are currently no task groups.', 'wproject'); ?></p>

            <?php } ?>
        </ul>

    </fieldset>

    <fieldset>
        <legend><?php _e('Pep Talk', 'wproject'); ?></legend>
        <ul>
            <li>
                <label><?php _e('Pep talk trigger percentage', 'wproject'); ?></label>
                <input type="number" name="project_pep_talk_percentage" id="project_pep_talk_percentage" min="1" max="99" <?php if($pep_talk_percentage) { echo 'data-pep-percentage="' . $pep_talk_percentage . '"'; } ?> placeholder="90" />
            </li>
            <li>
                <label><?php _e('Pep talk message', 'wproject'); ?></label>
                <input type="text" name="project_pep_talk_message" id="project_pep_talk_message" maxlength="65" placeholder="Good job team!" <?php if($pep_talk_percentage) { echo 'data-pep-message="' . $pep_talk_message . '"'; } ?> />
                <br /><br />
                <em class="btn-light pep-defaults"><?php _e('Use defaults', 'wproject'); ?></em> <em class="btn-light pep-clear"><?php _e('Clear', 'wproject'); ?></em>
                <script>
                    $('.pep-defaults').click(function() {
                        var project_pep_talk_percentage    = $('#project_pep_talk_percentage').data('pep-percentage');
                        var project_pep_talk_message = $('#project_pep_talk_message').data('pep-message');
                        
                        $('#project_pep_talk_percentage').val(project_pep_talk_percentage);
                        $('#project_pep_talk_message').val(project_pep_talk_message);
                    });
                    $('.pep-clear').click(function() {
                        $('#project_pep_talk_percentage').val('');
                        $('#project_pep_talk_message').val('');
                    });
                </script>
            </li>
        </ul>
    </fieldset>

    <input type="hidden" name="new-project-form" value="1" />
    <input type="hidden" name="project_creator" value="<?php echo get_current_user_id(); ?>" />
    
    <div class="submit">
        <button><?php _e('Create project', 'wproject'); ?></button>
    </div>


    <script type='text/javascript'>
        var decimal_places  = 2;
        var percentage      = 10;
        var delete_icon     = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff9800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';

        jQuery(window).load(function() {
            jQuery(function() {
                var materials = jQuery('.material-items');
                var i = jQuery('.material-items li').size() + 1;
                
                jQuery('.add-item').click(function() {
                    jQuery('<li class="item"><span><input type="text" name="project_material_name[]" placeholder="<?php _e('Item', 'wproject'); ?>" data-lpignore="true" required class="item" /></span><span><input type="number" name="project_material_cost[]" class="project-material-cost" min="1" step=".01" placeholder="<?php echo $currency_symbol; ?>" required class="cost" /></span><span class="remove">'+delete_icon+'</span></li>').appendTo(materials);
                    i++;
                });
                
                jQuery('.material-items').on('click', '.remove', function() {
                    jQuery('.material-items').find(this).parent().remove();
                    updateTotal();
                    return false;
                });
            });
            jQuery(document).on('change','.item .project-material-cost', function(){
                try {
                    var p = jQuery(this).closest('.item');
                    updateTotal();
                }
                catch(x) {
                }
            });
        });
        function updateTotal() {
            var sum = 0;
            var rows = jQuery('.project-material-cost');
            for(var i=0;i<rows.length;++i)
                sum += (parseFloat(rows.eq(i).val()) || 0);
                //sum += ((sum * percentage)/100); // Tax
            jQuery('.project-materials-total').attr('value', sum.toFixed(decimal_places));         
            jQuery('.materials-tally').text(sum.toFixed(decimal_places));         
        }

        /* Focus project name input */
        //$('#project_name').focus();

        /* Focus task name input */
        //$('#task_name').focus();

       
        <?php if(!empty($project_name)) { ?>
            jQuery('#project_name').val('<?php echo $project_name; ?>');
            jQuery$('.new-project h1').text('<?php echo $project_name; ?>');
        <?php } ?>

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

</form>
<?php do_action( 'new_project_after_form' ); ?>

<!--/ End New Project /-->
<?php } else { ?>
    <p class="info"><i data-feather="alert-triangle"></i><?php _e('The ability to manage projects is limited to project managers and administrators.', 'wproject'); ?></p>
<?php } ?>

<?php /* Help topics */
function new_project_help() { ?>
    <h4><?php _e('Project', 'wproject'); ?></h4>
    <p><?php _e('The minimum details required to create a project. The project name and status are both mandatory.', 'wproject'); ?></p>

    <h4><?php _e('Specifics', 'wproject'); ?></h4>
    <p><?php _e('More details about the project.', 'wproject'); ?></p>

    <h4><?php _e('Materials', 'wproject'); ?></h4>
    <p><?php _e('Include the cost(s) of any materials that belong to this project. For example: Stock photography.', 'wproject'); ?></p>

    <h4><?php _e('Task group', 'wproject'); ?></h4>
    <p><?php _e('Include tasks from a task group (if any have been set up).', 'wproject'); ?></p>
<?php }
add_action('help_start', 'new_project_help');

/* Side nav items */
function new_project_nav() { ?>
    <li><a href="<?php echo home_url(); ?>/"><i data-feather="x-circle"></i><?php _e('Discard', 'wproject'); ?></a></li>
    <li><a href="<?php echo get_the_permalink(106); ?>/"><i data-feather="folder"></i><?php _e('See current projects', 'wproject'); ?></a></li>
<?php }
add_action('side_nav', 'new_project_nav');