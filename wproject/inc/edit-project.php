<?php if ( ! defined( 'ABSPATH' ) ) { exit; }
    $wproject_settings              = wProject(); 
    $user_id                        = get_current_user_id();
    $project_id                     = isset($_GET['project-id']) ? $_GET['project-id'] : '';
    $description                    = term_description($project_id);
    $enable_time                    = $wproject_settings['enable_time'];
    $currency_symbol                = $wproject_settings['currency_symbol'];
    $currency_symbol_position       = $wproject_settings['currency_symbol_position'];

    if($currency_symbol) {
        $currency_symbol = $currency_symbol;
    } else {
        $currency_symbol = '$';
    }

    $project_object                 = get_term( $project_id );

    /* If project exists */
    if(!empty($project_object->name)) {
    

    $project_name                   = $project_object->name;
    $project_term_id                = $project_object->term_id;
    $project_description            = $project_object->description;

    $project_meta                   = get_term_meta($project_id); 
    $project_full_description       = isset($project_meta['project_full_description'][0]) ? $project_meta['project_full_description'][0] : '';
    $project_status                 = isset($project_meta['project_status'][0]) ? $project_meta['project_status'][0] : '';
    $project_start_date             = isset($project_meta['project_start_date'][0]) ? $project_meta['project_start_date'][0] : '';
    $project_end_date               = isset($project_meta['project_end_date'][0]) ? $project_meta['project_end_date'][0] : '';
    $project_time_allocated         = isset($project_meta['project_time_allocated'][0]) ? $project_meta['project_time_allocated'][0] : '';
    $project_hourly_rate            = isset($project_meta['project_hourly_rate'][0]) ? $project_meta['project_hourly_rate'][0] : '';
    $project_job_number             = isset($project_meta['project_job_number'][0]) ? $project_meta['project_job_number'][0] : '';
    $web_page_url                    = isset($project_meta['web_page_url'][0]) ? $project_meta['web_page_url'][0] : '';

    $project_pep_talk_percentage    = isset($project_meta['project_pep_talk_percentage'][0]) ? $project_meta['project_pep_talk_percentage'][0] : '';
	$project_pep_talk_message   	= isset($project_meta['project_pep_talk_message'][0]) ? $project_meta['project_pep_talk_message'][0] : '';

    $project_manager                = $project_meta['project_manager'][0];
    $project_contact                = isset($project_meta['project_contact'][0]) ? $project_meta['project_contact'][0] : '';
    $project_manager_id             = get_user_by('ID', $project_meta['project_manager'][0]);
    $project_owner_profile          = get_the_permalink(109) . '?id=' . $project_manager_id->ID;
    $project_owner_first_name       = get_user_meta( $project_manager_id->ID, 'first_name' , true); 
    $project_owner_last_name        = get_user_meta( $project_manager_id->ID, 'last_name' , true); 
    $project_owner_name             = $project_owner_first_name . ' ' . $project_owner_last_name;
    $project_owner_photo            = get_user_meta( $project_manager_id->ID, 'user_photo' , true); 

    if(preg_match("/[a-e]/i", $project_owner_first_name[0])) {
        $colour = 'a-e';
    } else if(preg_match("/[f-j]/i", $project_owner_first_name[0])) {
        $colour = 'f-j';
    } else if(preg_match("/[k-o]/i", $project_owner_first_name[0])) {
        $colour = 'k-o';
    } else if(preg_match("/[p-t]/i", $project_owner_first_name[0])) {
        $colour = 'p-t';
    } else if(preg_match("/[u-z]/i", $project_owner_first_name[0])) {
        $colour = 'u-z';
    } else {
        $colour = '';
    }

    if($project_owner_photo) {
        $avatar         = $project_owner_photo;
        $avatar_id      = attachment_url_to_postid($avatar);
        $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
        $avatar         = $small_avatar[0];
        $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" />';
    } else {
        $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . $project_owner_first_name[0] . $project_owner_last_name[0] . '</div>';
    }

    if(isset($project_meta['project_materials_total'][0])) {
        $project_materials_total = $project_meta['project_materials_total'][0];
    } else {
        $project_materials_total = '0';
    }

    $project_start_date_strtotime   = strtotime($project_start_date);
    $project_end_date_strtotime     = strtotime($project_end_date);

    $term = term_exists( 'Uncategorized', 'category' );

    $user               = wp_get_current_user();
    $user_role          = $user->roles[0];
    
    
    if($user_role == 'project_manager' || $user_role == 'administrator') {
    if($user_id == $project_manager_id->ID) {
?>
    <!--/ Start Edit Project /-->
    <form class="general-form edit-project-form" method="post" id="edit-project-form" enctype="multipart/form-data">
        <fieldset>
            <legend><?php _e('Project', 'wproject'); ?></legend>
            <ul>
                <li>
                    <label><?php _e('Project name', 'wproject'); ?></label>
                    <input type="text" name="project_name" id="project_name" value="<?php echo $project_name ?>" required />
                </li>
                <li>
                    <label><?php _e('Brief description', 'wproject'); ?></label>
                    <input type="text" name="description" value="<?php if($project_description) { echo $project_description; } ?>" />
                </li>
                <li>
                    <label><?php _e('Full description', 'wproject'); ?></label>
                    <textarea name="project_full_description"><?php if($project_full_description) { echo $project_full_description; } ?></textarea>
                </li>
                <li>
                    <label><?php _e('Status', 'wproject'); ?></label>
                    <select name="project_status" id="project_status" required>
                        <option value="archived" <?php if($project_status == 'archived') { echo 'selected'; } ?>><?php _e( 'Archived', 'wproject' ); ?></option>
                        <option value="cancelled" <?php if($project_status == 'cancelled') { echo 'selected'; } ?>><?php _e( 'Cancelled', 'wproject' ); ?></option>
                        <option value="complete" <?php if($project_status == 'complete') { echo 'selected'; } ?>><?php _e( 'Complete', 'wproject' ); ?></option>
                        <option value="in-progress" <?php if($project_status == 'in-progress') { echo 'selected'; } ?>><?php _e( 'In progress', 'wproject' ); ?></option>
                        <option value="planning" <?php if($project_status == 'planning') { echo 'selected'; } ?>><?php _e( 'Planning', 'wproject' ); ?></option>
                        <option value="proposed" <?php if($project_status == 'proposed') { echo 'selected'; } ?>><?php _e( 'Proposed', 'wproject' ); ?></option>	
                        <option value="setting-up" <?php if($project_status == 'setting-up') { echo 'selected'; } ?>><?php _e( 'Setting up', 'wproject' ); ?></option>
                    </select>
                </li>
                <li class="delete-project hidden warn">           
                    <input type="checkbox" name="delete-project" /> <span><strong><?php _e( 'Delete this project and associated tasks?', 'wproject' ); ?></strong></span>
                </li>
            </ul>
            <script>
                $('#project_status').change(function() {
                    if($(this).val() == 'cancelled') {
                        $('.delete-project').fadeIn();
                    } else {
                        $('.delete-project').fadeOut();
                        $( '.delete-project input' ).prop( 'checked', false );
                    }
                });
            </script>
        </fieldset>

        <fieldset>
            <legend><?php _e('Specifics', 'wproject'); ?></legend>
            <ul>
                <li class="split-2">
                    <label><?php _e('Start & end dates', 'wproject'); ?><em class="action clear-dates"><?php _e('Clear dates', 'wproject'); ?></em></label>
                    <input type="date" name="project_start_date" <?php if($project_start_date) { echo 'value="' . date("Y-m-d", $project_start_date_strtotime) . '"'; } ?> class="pick-start-date merge-start" />
                    <input type="date" name="project_end_date" <?php if($project_end_date) { echo 'value="' . date("Y-m-d", $project_end_date_strtotime) . '"'; } ?> class="pick-end-date merge-end" />
                </li>

                <?php if($enable_time) { ?>
                <li class="split-2">
                    <label><?php _e('Time & Rate', 'wproject'); ?></label>
                    <input type="number" min="1" step="1" class="half merge-start" name="project_time_allocated" placeholder="Hrs allocated" <?php if($project_time_allocated) { echo 'value="' . $project_time_allocated . '"'; } ?> />

                    <input type="number" min="1" step=".5" name="project_hourly_rate" placeholder="<?php echo $currency_symbol; ?> per hour" class="half merge-end" <?php if($project_hourly_rate) { echo 'value="' . $project_hourly_rate . '"'; } ?> />
                </li>
                <?php } ?>

                <li>
                    <label><?php _e('Job #', 'wproject'); ?></label>
                    <input type="text" name="project_job_number" <?php if($project_job_number) { echo 'value="' . $project_job_number . '"'; } ?> />
                </li>
                <li>
                    <label><?php _e('Project manager', 'wproject'); ?></label>
                    <select name="project_manager" required>
                        <?php
                            $users = get_users( array( 'role__in' => array( 'project_manager', 'administrator' ) ) );
                            foreach ( $users as $user ) { ?>
                                <option value="<?php echo esc_html( $user->ID ); ?>" <?php if($user->ID == $project_manager) { echo 'selected'; } ?>><?php echo esc_html( $user->first_name ); ?> <?php echo esc_html( $user->last_name ); ?> - <?php echo esc_html( $user->title ); ?></option>
                            <?php }
                        ?>
                    </select>
                </li>
                <li>
                    <label><?php _e('Web page', 'wproject'); ?></label>
                    <input type="url" name="web_page_url" <?php if($web_page_url) { echo 'value="' . $web_page_url . '"'; } ?> placeholder="<?php _e('https://', 'wproject'); ?>" />
                </li>
                <?php do_action( 'specifics_list_end' ); ?>
            </ul>
        </fieldset>

        <fieldset class="materials">
            <legend><?php _e('Materials', 'wproject'); ?></legend>
            <p class="add-item"><i data-feather="plus-square" class="remove"></i> <?php _e('Add material item', 'wproject'); ?></p>

            <ul class="material-items">            

                <?php 
                    $all_rows = get_term_meta( (int)$project_id, 'project_materials_list', true);
                    if($all_rows) {
                        if( count($all_rows ) > 0  ){
                            sort($all_rows); /* Sort alphabetically */
                            foreach( $all_rows as $s_row ) {
                            ?>
                                <li class="item">
                                    <span><input type="text" name="project_material_name[]" placeholder="<?php _e('Item', 'wproject'); ?>" data-lpignore="true" value="<?php echo $s_row['project_material_name'] ?>" required class="item" /></span>
                                    <span><input type="number" name="project_material_cost[]" class="project-material-cost" min="1" step=".01" placeholder="<?php echo $currency_symbol; ?>" value="<?php echo $s_row['project_material_cost']; ?>" required class="cost" /></span>
                                    <span class="remove"><i data-feather="trash-2"></i></span>
                                </li>
                            <?php 
                            }
                        }
                    }
                ?>

            </ul>

            <div class="materials-total">
                <span>
                    <?php _e('Cost', 'wproject'); ?>: 
                    <?php if($currency_symbol_position == 'l') { echo $currency_symbol; } ?><span class="materials-tally"><?php if($project_materials_total) { echo $project_materials_total; } ?></span><?php if($currency_symbol_position == 'r') { echo $currency_symbol; } ?>
                </span>
                <input type="hidden" name="project_materials_total" class="project-materials-total" value="<?php if($project_materials_total) { echo $project_materials_total; } ?>" readonly />
            </div>
        </fieldset>




        <fieldset class="pep-talk">
            <legend><?php _e('Pep Talk', 'wproject'); ?></legend>
            <ul>
                <li>
                    <label><?php _e('Pep talk trigger percentage', 'wproject'); ?></label>
                    <input type="number" name="project_pep_talk_percentage" id="project_pep_talk_percentage" min="1" max="99" value="<?php if($project_pep_talk_percentage) { echo  $project_pep_talk_percentage; } ?>" />
                </li>
                <li>
                    <label><?php _e('Pep talk message', 'wproject'); ?></label>
                    <input type="text" name="project_pep_talk_message" id="project_pep_talk_message" maxlength="65" placeholder="Good job team!" value="<?php if($project_pep_talk_message) { echo  $project_pep_talk_message; } ?>" />
                </li>
            </ul>
        </fieldset>



        <input type="hidden" name="project_creator" value="<?php echo get_current_user_id(); ?>" />
        
        <div class="submit">
            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
            <button id="update-project" disabled><?php _e('Save Changes', 'wproject'); ?></button>
        </div>

        <script type='text/javascript'>
            // Add/remove line items, do some math.
            var decimal_places  = 2;
            var percentage      = 10;
            var trash_icon      = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2 feather-icon" color="#ff9800"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>';

            jQuery(window).load(function() {
                jQuery(function() {
                    var materials = jQuery('.material-items');
                    var i = jQuery('.material-items li').size() + 1;
                    
                    jQuery('.add-item').click(function() {
                        jQuery('<li class="item"><span><input type="text" name="project_material_name[]" placeholder="<?php _e('Item', 'wproject'); ?>" data-lpignore="true" required class="item" /></span><span><input type="number" name="project_material_cost[]" class="project-material-cost" min="1" step=".01" placeholder="<?php echo $currency_symbol; ?>" required /></span><span class="remove">'+trash_icon+'</span></li>').appendTo(materials);
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

            /* Clear date fields */
            jQuery('.clear-dates').hide();

            <?php if($project_start_date || $project_end_date) { ?>
                jQuery('.clear-dates').show();
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
        <script type='text/javascript' src='<?php echo get_template_directory_uri();?>/js/min/form-submission-check.min.js'></script>
        

    </form>
    <!--/ End Edit Project /-->

    <?php } else { ?>
        
    <div class="no-access">
        <?php echo $the_avatar; ?>
        <p>
            <?php printf( __(  'Oops! This project is managed by <a href="%1$s">%2$s</a>.', 'wproject' ), $project_owner_profile, $project_owner_name ); ?>
        </p>
    </div>
    <?php } ?>

<?php } else { ?>
    <p class="info"><i data-feather="alert-triangle"></i><?php _e('The ability to manage projects is limited to project managers and administrators.', 'wproject'); ?></p>
<?php } 
} else /* End if project exists */ { ?>

    <div class="no-access">
        <i data-feather="alert-circle"></i>
        <p>
            <?php _e(  'This project does not exist.', 'wproject'); ?>
        </p>
    </div>

<?php } ?>

<?php /* Help topics */
function new_project_help() { ?>
    <h4><?php _e('Project', 'wproject'); ?></h4>
    <p><?php _e('The minimum details required to create a project. The project name and status are both mandatory.', 'wproject'); ?></p>

    <h4><?php _e('Specifics', 'wproject'); ?></h4>
    <p><?php _e('More details about the project.', 'wproject'); ?></p>

    <h4><?php _e('Materials', 'wproject'); ?></h4>
    <p><?php _e('Include the cost(s) of any materials that belong to this project. For example: Stock photography.', 'wproject'); ?></p>
<?php }
add_action('help_start', 'new_project_help');

/* Side nav items */
function new_project_nav() { 
    $project_id = isset($_GET['project-id']) ? $_GET['project-id'] : '';
    $project    = get_term( $project_id, 'project' );
?>
    <li><a href="<?php echo home_url(); ?>/project/<?php echo $project->slug; ?>"><i data-feather="folder"></i><?php _e('Go to project', 'wproject'); ?></a></li>
    <li><a href="<?php echo home_url(); ?>/report/?report-id=<?php echo $project_id; ?>"><i data-feather="bar-chart-2"></i><?php _e('Report', 'wproject'); ?></a></li>
    <li><a href="<?php echo get_the_permalink(106); ?>/"><i data-feather="chevrons-left"></i><?php _e('See all projects', 'wproject'); ?></a></li>
<?php }
add_action('side_nav', 'new_project_nav');