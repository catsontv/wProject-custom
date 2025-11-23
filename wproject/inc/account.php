<?php if ( ! defined( 'ABSPATH' ) ) { exit; }
    wp_get_current_user();
    $the_status                 = isset(user_details()['the_status']) ? user_details()['the_status'] : '';
    $show_tips                  = isset(user_details()['show_tips']) ? user_details()['show_tips'] : '';
    $default_task_order         = isset(user_details()['default_task_order']) ? user_details()['default_task_order'] : '';
    $recent_tasks               = isset(user_details()['recent_tasks']) ? user_details()['recent_tasks'] : '';
    $latest_activity            = isset(user_details()['latest_activity']) ? user_details()['latest_activity'] : '';
    $default_task_ownership     = isset(user_details()['default_task_ownership']) ? user_details()['default_task_ownership'] : '';
    $hide_gantt                 = isset(user_details()['hide_gantt']) ? user_details()['hide_gantt'] : '';
    $minimise_complete_tasks    = isset(user_details()['minimise_complete_tasks']) ? user_details()['minimise_complete_tasks'] : '';
    $pm_only_show_my_projects   = isset(user_details()['pm_only_show_my_projects']) ? user_details()['pm_only_show_my_projects'] : '';
    $show_latest_activity       = isset(user_details()['show_latest_activity']) ? user_details()['show_latest_activity'] : '';
    $dashboard_bar_chart        = isset(user_details()['dashboard_bar_chart']) ? user_details()['dashboard_bar_chart'] : '';
    $user_photo                 = isset(user_details()['user_photo']) ? user_details()['user_photo'] : '';
    $notifications_count        = isset(user_details()['notifications_count']) ? user_details()['notifications_count'] : '';
    $pm_auto_kanban_view        = isset(user_details()['pm_auto_kanban_view']) ? user_details()['pm_auto_kanban_view'] : '';
    $dark_mode                  = isset(user_details()['dark_mode']) ? user_details()['dark_mode'] : '';

    $wproject_settings          = wProject();
    $force_avatar               = $wproject_settings['force_avatar'];
    $enable_kanban              = $wproject_settings['enable_kanban'];

    $user 					    = wp_get_current_user();
    $user_role 				    = $user->roles[0];
?>
<!--/ Start Account /-->
<form class="general-form account-form" method="post" id="account-form" enctype="multipart/form-data">
    
    <fieldset>
        
        <legend><?php esc_html_e('About you', 'wproject'); ?></legend>
        <ul>
            <li>
                <label><?php esc_html_e('First name', 'wproject'); ?></label>
                <input type="text" name="first_name" value="<?php echo esc_html(user_details()['first_name']); ?>" required />
            </li>
            <li>
                <label><?php esc_html_e('Last name', 'wproject'); ?></label>
                <input type="text" name="last_name" value="<?php echo esc_html(user_details()['last_name']); ?>" required />
            </li>
            <li>
                <label><?php esc_html_e('Title', 'wproject'); ?></label>
                <input type="text" name="title" value="<?php echo esc_html(user_details()['title']); ?>" required />
            </li>
            <li>
                <label><?php esc_html_e('Bio', 'wproject'); ?></label>
                <textarea name="description"><?php echo esc_html(user_details()['description']); ?></textarea>
            </li>
            <li>
                <label><?php esc_html_e('Photo', 'wproject'); ?></label>
                <input type="file" name="user_photo" id="user_photo" accept="image/*" class="file-input" onchange="loadFile(event)" <?php if($force_avatar && !$user_photo) { echo 'required'; } ?> /> 
                <style>
                    .file-input:before {
                        content: '<?php esc_html_e('Upload image', 'wproject'); ?>';
                    }
                </style>
            </li>
            <?php if($user_photo) { ?>
            <li>
                <input type="hidden" name="remove_photo" value="no" />
                <label class="user-photo-toggle">
                    <input type="checkbox" name="remove_photo" id="remove_photo" value="yes" /> <span><?php esc_html_e('Remove photo', 'wproject'); ?></span>
                </label>
            </li>

            <script>
                jQuery( document ).ready(function() {

                    var user_photo = jQuery('.left #avatar').attr('src');
                    var no_photo = '<?php echo get_template_directory_uri();?>/images/default-user.png';
                
                    jQuery('.user-photo-toggle').click(function() {

                        if (jQuery('#remove_photo').prop('checked') ) {
                            jQuery('.left #avatar').attr('src', no_photo);
                        } else {
                            jQuery('.left #avatar').attr('src', user_photo);
                            jQuery('#user_photo').attr('src', user_photo);
                        }
                    });

                });
            </script>
            <?php } ?>
        </ul>
    </fieldset>

    <fieldset>
        <legend><?php esc_html_e('Contact Methods', 'wproject'); ?></legend>
        <ul>
            <li>
                <label><?php esc_html_e('Email', 'wproject'); ?></label>
                <input type="text" name="user_email" value="<?php echo esc_html(user_details()['user_email']); ?>" required />
            </li>
            <li>
                <label><?php esc_html_e('Flock', 'wproject'); ?></label>
                <input type="text" name="flock" value="<?php echo esc_html(user_details()['flock']); ?>" />
            </li>
            <li>
                <label><?php esc_html_e('Google Meet', 'wproject'); ?></label>
                <input type="text" name="hangouts" value="<?php echo esc_html(user_details()['hangouts']); ?>" />
            </li>
            <li>
                <label><?php esc_html_e('Microsoft Teams', 'wproject'); ?></label>
                <input type="text" name="teams" value="<?php echo esc_html(user_details()['teams']); ?>" />
            </li>
            <li>
                <label><?php esc_html_e('Phone', 'wproject'); ?></label>
                <input type="text" name="phone" value="<?php echo esc_html(user_details()['phone']); ?>" />
            </li>
            <li>
                <label><?php esc_html_e('Skype', 'wproject'); ?></label>
                <input type="text" name="skype" value="<?php echo esc_html(user_details()['skype']); ?>" />
            </li>
            <li>
                <label><?php esc_html_e('Slack', 'wproject'); ?></label>
                <input type="text" name="slack" value="<?php echo esc_html(user_details()['slack']); ?>" />
            </li>
        </ul>
    </fieldset>

    <fieldset>
        <legend><?php esc_html_e('Preferences', 'wproject'); ?></legend>
        <ul>
            <?php if($user_role != 'observer') { ?>
            <li>
                <label for="default_task_order"><?php esc_html_e('Default task order', 'wproject'); ?></label>
                <select name="default_task_order" required>
                    <option></option>
                    <option value="title" <?php if($default_task_order == 'title') { echo 'selected'; } ?>><?php esc_html_e('Alphabetical', 'wproject'); ?></option>
                    <option value="date" <?php if($default_task_order == 'date') { echo 'selected'; } ?>><?php esc_html_e('Date added', 'wproject'); ?></option>
                    <option value="due_date" <?php if($default_task_order == 'due_date') { echo 'selected'; } ?>><?php esc_html_e('Due date', 'wproject'); ?></option>
                    <option value="modified" <?php if($default_task_order == 'modified') { echo 'selected'; } ?>><?php esc_html_e('Last modified', 'wproject'); ?></option>
                    <option value="comment_count" <?php if($default_task_order == 'comment_count') { echo 'selected'; } ?>><?php esc_html_e('Most comments', 'wproject'); ?></option>
                    <option value="rand" <?php if($default_task_order == 'rand') { echo 'selected'; } ?>><?php esc_html_e('Random', 'wproject'); ?></option>
                    <option value="ID" <?php if($default_task_order == 'ID') { echo 'selected'; } ?>><?php esc_html_e('Task ID', 'wproject'); ?></option>
                </select>
                
            </li>
            <li>
                <label for="recent_tasks"><?php esc_html_e('Recent tasks are considered', 'wproject'); ?></label>
                <select name="recent_tasks"required>
                    <option></option>
                    <option value="1 day ago" <?php if($recent_tasks == '1 day ago') { echo 'selected'; } ?>><?php esc_html_e('1 day or newer', 'wproject'); ?></option>
                    <option value="2 days ago" <?php if($recent_tasks == '2 days ago') { echo 'selected'; } ?>><?php esc_html_e('2 days or newer', 'wproject'); ?></option>
                    <option value="3 days ago" <?php if($recent_tasks == '3 days ago') { echo 'selected'; } ?>><?php esc_html_e('3 days or newer', 'wproject'); ?></option>
                    <option value="4 days ago" <?php if($recent_tasks == '4 days ago') { echo 'selected'; } ?>><?php esc_html_e('4 days or newer', 'wproject'); ?></option>
                    <option value="5 days ago" <?php if($recent_tasks == '5 days ago') { echo 'selected'; } ?>><?php esc_html_e('5 days or newer', 'wproject'); ?></option>
                    <option value="10 days ago" <?php if($recent_tasks == '10 days ago') { echo 'selected'; } ?>><?php esc_html_e('10 days or newer', 'wproject'); ?></option>
                    <option value="20 days ago" <?php if($recent_tasks == '20 days ago') { echo 'selected'; } ?>><?php esc_html_e('20 days or newer', 'wproject'); ?></option>
                    <option value="30 days ago" <?php if($recent_tasks == '30 days ago') { echo 'selected'; } ?>><?php esc_html_e('30 days or newer', 'wproject'); ?></option>
                    <option value="60 days ago" <?php if($recent_tasks == '60 days ago') { echo 'selected'; } ?>><?php esc_html_e('60 days or newer', 'wproject'); ?></option>
                    <option value="90 days ago" <?php if($recent_tasks == '90 days ago') { echo 'selected'; } ?>><?php esc_html_e('90 days or newer', 'wproject'); ?></option>
                    <option value="120 days ago" <?php if($recent_tasks == '120 days ago') { echo 'selected'; } ?>><?php esc_html_e('120 days or newer', 'wproject'); ?></option>
                    <option value="150 days ago" <?php if($recent_tasks == '150 days ago') { echo 'selected'; } ?>><?php esc_html_e('150 days or newer', 'wproject'); ?></option>
                    <option value="180 days ago" <?php if($recent_tasks == '180 days ago') { echo 'selected'; } ?>><?php esc_html_e('180 days or newer', 'wproject'); ?></option>
                    <option value="210 days ago" <?php if($recent_tasks == '210 days ago') { echo 'selected'; } ?>><?php esc_html_e('210 days or newer', 'wproject'); ?></option>
                </select>
            </li>
            <li>
                <label for="the_status"><?php esc_html_e('Your status', 'wproject'); ?></label>
                <input type="text" name="the_status" value="<?php echo esc_html(user_details()['the_status']); ?>" maxlength="50" />
            </li>
            <li>
                <label for="latest_activity"><?php esc_html_e('Max number of latest activity items', 'wproject'); ?></label>
                <input type="number" name="latest_activity" value="<?php echo esc_html(user_details()['latest_activity']); ?>" min="0" max="60" required />
            </li>
            <li>
                <label for="notifications_count"><?php esc_html_e('Max number of notifications', 'wproject'); ?></label>
                <input type="number" name="notifications_count" value="<?php echo $notifications_count; ?>" min="1" max="100" />
            </li>
            <?php } ?>
            <?php if($user_role == 'project_manager' || $user_role == 'administrator') { ?>
            <li>
                <input type="hidden" name="dashboard_bar_chart" value="no" />
                <label class="<?php if($dashboard_bar_chart == 'yes') { echo 'selected'; } ?>">
                    <input type="checkbox" name="dashboard_bar_chart" id="dashboard_bar_chart" value="yes" <?php if($dashboard_bar_chart == 'yes') { echo 'checked'; } ?> /> <span><?php esc_html_e('Projects chart on the dashboard', 'wproject'); ?></span>
                </label>
            </li>
            <?php } ?>

            <?php if($user_role != 'client' && $user_role != 'observer') { ?>
            <li>
                <input type="hidden" name="default_task_ownership" value="no" />
                <label class="<?php if($default_task_ownership == 'yes') { echo 'selected'; } ?>">
                    <input type="checkbox" name="default_task_ownership" id="default_task_ownership" value="yes" <?php if($default_task_ownership == 'yes') { echo 'checked'; } ?> /> <span><?php esc_html_e('Default task ownership to me', 'wproject'); ?></span>
                </label>
            </li>
            <li>
                <input type="hidden" name="hide_gantt" value="no" />
                <label class="<?php if($hide_gantt == 'yes') { echo 'selected'; } ?>">
                    <input type="checkbox" name="hide_gantt" id="hide_gantt" value="yes" <?php if($hide_gantt == 'yes') { echo 'checked'; } ?> /> <span><?php esc_html_e('Always hide the Gantt chart', 'wproject'); ?></span>
                </label>
            </li>
            <li>
                <input type="hidden" name="minimise_complete_tasks" value="no" />
                <label class="<?php if($minimise_complete_tasks == 'yes') { echo 'selected'; } ?>">
                    <input type="checkbox" name="minimise_complete_tasks" id="minimise_complete_tasks" value="yes" <?php if($minimise_complete_tasks == 'yes') { echo 'checked'; } ?> /> <span><?php esc_html_e('Auto minimise completed tasks', 'wproject'); ?></span>
                </label>
            </li>
            <li>
                <input type="hidden" name="show_tips" value="no" />
                <label class="<?php if($show_tips == 'yes') { echo 'selected'; } ?>">
                    <input type="checkbox" name="show_tips" id="show_tips" value="yes" <?php if($show_tips == 'yes') { echo 'checked'; } ?> /> <span><?php esc_html_e('Show tips', 'wproject'); ?></span>
                </label>
            </li>
            <?php } else if($user_role == 'client') { ?>

                <script>
                    $( document ).ready(function() {
                        $('.right li').remove();
                    });
                </script>
                
            <?php } ?>

            <?php if($user_role == 'project_manager' || $user_role == 'administrator') { ?>
            <li>
                <input type="hidden" name="pm_only_show_my_projects" value="no" />
                <label class="<?php if($pm_only_show_my_projects == 'yes') { echo 'selected'; } ?>">
                    <input type="checkbox" name="pm_only_show_my_projects" id="pm_only_show_my_projects" value="yes" <?php if($pm_only_show_my_projects == 'yes') { echo 'checked'; } ?> /> <span><?php esc_html_e('Only show my projects on the Projects page', 'wproject'); ?></span>
                </label>
            </li>
            <?php } ?>

            <li>
                <input type="hidden" name="show_latest_activity" value="no" />
                <label class="<?php if($show_latest_activity == 'yes') { echo 'selected'; } ?>">
                    <input type="checkbox" name="show_latest_activity" id="show_latest_activity" value="yes" <?php if($show_latest_activity == 'yes') { echo 'checked'; } ?> /> <span><?php esc_html_e('Show the Latest Activity on the dashboard', 'wproject'); ?></span>
                </label>
            </li>

            <?php if($enable_kanban) {
                if($user_role == 'project_manager' || $user_role == 'administrator') { ?>
                <li>
                    <input type="hidden" name="pm_auto_kanban_view" value="no" />
                    <label class="<?php if($pm_auto_kanban_view == 'yes') { echo 'selected'; } ?>">
                        <input type="checkbox" name="pm_auto_kanban_view" id="pm_auto_kanban_view" value="yes" <?php if($pm_auto_kanban_view == 'yes') { echo 'checked'; } ?> /> <span><?php esc_html_e('Automatically open the Kanban board on project view', 'wproject'); ?></span>
                    </label>
                </li>
            <?php } 
            } ?>

            <li>
                <input type="hidden" name="dark_mode" value="no" />
                <label class="<?php if($dark_mode == 'yes') { echo 'selected'; } ?>">
                    <input type="checkbox" name="dark_mode" id="dark_mode" value="yes" <?php if($dark_mode == 'yes') { echo 'checked'; } ?> /> <span><?php esc_html_e('Dark mode', 'wproject'); ?></span>
                </label>
            </li>
        </ul>
    </fieldset>

    <?php wp_nonce_field('update-profile_' . $user_ID) ?>
    <input type="hidden" name="user_id" value="<?php echo get_current_user_id(); ?>" />
    <input type="hidden" name="old_photo" value="<?php echo esc_html($user_photo); ?>" />
    <input type="hidden" name="account-form" value="1" />

    <div class="submit">
        <button><?php esc_html_e('Update', 'wproject'); ?></button>
    </div>

</form>
<!--/ End Account /-->

<!--/ Start Account Widget /-->
<?php if ( is_active_sidebar( 'wproject-account-widget' ) ) { 
    dynamic_sidebar( 'wproject-account-widget' );
} ?>
<!--/ End Account Widget /-->

<?php /* Help topics */
function account_help() { ?>
    <h4><?php esc_html_e('About you'); ?></h4>
    <p><?php esc_html_e('Your primary details. First name, last name and title are mandatory.', 'wproject'); ?></p>

    <h4><?php esc_html_e('Contact methods'); ?></h4>
    <p><?php esc_html_e('Your preferred contact methods. Email address is mandatory.', 'wproject'); ?></p>

    <h4><?php esc_html_e('Preferences'); ?></h4>
    <p><?php esc_html_e('Various ways you can experience and interact with wProject.', 'wproject'); ?></p>
<?php }
add_action('help_start', 'account_help');

/* Side nav items */
function account_nav() { ?>
    <li><a href="<?php echo get_the_permalink(109);?>/?id=<?php echo get_current_user_id(); ?>"><i data-feather="user"></i><?php esc_html_e('View profile', 'wproject'); ?></a></li>
<?php }
add_action('side_nav', 'account_nav');