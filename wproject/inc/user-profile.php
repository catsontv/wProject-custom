<?php if ( ! defined( 'ABSPATH' ) ) { exit; }
    $user_id            = isset($_GET['id']) ? $_GET['id'] : '';
    $user_info          = get_userdata($user_id);
    $first_name         = $user_info->first_name;
    $last_name          = $user_info->last_name;
    $title              = $user_info->title;
    $phone              = $user_info->phone;
    $email              = $user_info->user_email;
    $flock              = $user_info->flock;
    $hangouts           = $user_info->hangouts;
    $teams              = $user_info->teams;
    $skype              = $user_info->skype;
    $slack              = $user_info->slack;
    $bio                = $user_info->description;
    $last_login         = $user_info->last_login;
    $user_photo         = $user_info->user_photo;
    $task_wip           = $user_info->task_wip;


    $the_status = $user_info->the_status;
    if($the_status == 'available') {
        $the_status = __('Available', 'wproject');
    } else if($the_status == 'away') {
        $the_status = __('Away', 'wproject');
    } else if($the_status == 'bored') {
        $the_status = __('Bored', 'wproject');
    } else if($the_status == 'busy') {
        $the_status = __('Busy', 'wproject');
    } else if($the_status == 'commuting') {
        $the_status = __('Commuting', 'wproject');
    } else if($the_status == 'do-not-disturb') {
        $the_status = __('Do not disturb', 'wproject');
    } else if($the_status == 'in-a-meeting') {
        $the_status = __('In a meeting', 'wproject');
    } else if($the_status == 'need-something-to-do') {
        $the_status = __('Need something to do', 'wproject');
    } else if($the_status == 'on-vacation') {
        $the_status = __('On vacation', 'wproject');
    } else if($the_status == 'out-to-lunch') {
        $the_status = __('Out to lunch', 'wproject');
    } else if($the_status == 'ready-to-assist') {
        $the_status = __('Ready to assist', 'wproject');
    } else if($the_status == 'working-remotely') {
        $the_status = __('Working remotely', 'wproject');
    } 

    $user       = get_userdata($user_id);
    $user_role  = $user->roles[0];

    if($user_role == 'administrator') {
        $the_role = __('Administrator', 'wproject');
    } else if($user_role == 'project_manager') {
        $the_role = __('Project manager', 'wproject');
    } else if($user_role == 'team_member') {
        $the_role = __('Team member', 'wproject');
    } else if($user_role == 'client') {
        $the_role = __('Client', 'wproject');
    } else if($user_role == 'observer') {
        $the_role = __('Observer', 'wproject');
    } else {
        $the_role = __('Unknown', 'wproject');
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
        $large_avatar   = wp_get_attachment_image_src($avatar_id, 'medium-square');
        $the_avatar     = '<img src="' . $large_avatar[0] . '" class="avatar" />';
    } else {
        $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . $first_name[0] . $last_name[0] . '</div>';
    }
?>
<!--/ Start User Profile /-->
<div class="profile profile-<?php echo $user_id; ?> <?php echo $user_role; ?>">

    <div class="pic">
        <?php echo $the_avatar; ?>
    </div>

    <div class="details">
        <h1><?php echo $first_name; ?> <?php echo $last_name; ?></h1>   
    
        <?php if($title) { ?>
            <small><span><?php _e('Position', 'wproject'); ?></span></small>
            <ul class="contacts">
                <li><span><?php _e('Job title', 'wproject'); ?>: </span><?php echo $title; ?></li>
                <li><span><?php _e('Role', 'wproject'); ?>: </span><?php echo $the_role; ?></li>
            </ul>
        <?php } ?>

        <?php if($bio) { ?>
            <small><span><?php _e('Bio', 'wproject'); ?></span></small>
            <p><?php echo $bio; ?></p>
        <?php } ?>

        <small><span><?php _e('Contact Information', 'wproject'); ?></span></small>
        <ul class="contacts">
            <?php if($phone) { ?>
                <li><span><?php _e('Phone', 'wproject'); ?>: </span><a href="tel:<?php echo $phone; ?>"><?php echo $phone; ?></a></li>
            <?php } ?>
            <li><span><?php _e('Email', 'wproject'); ?>: </span><a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a></li>
            <?php if($phone) { ?>
                <li><span><?php _e('Phone', 'wproject'); ?>: </span><a href="tel:<?php echo $phone; ?>"><?php echo $phone; ?></a></li>
            <?php } ?>
            <?php if($flock) { ?>
                <li><span><?php _e('Flock', 'wproject'); ?>: </span><?php echo $flock; ?></li>
            <?php } ?>
            <?php if($hangouts) { ?>
                <li><span><?php _e('Google Meet', 'wproject'); ?>: </span><?php echo $hangouts; ?></li>
            <?php } ?>
            <?php if($teams) { ?>
                <li><span><?php _e('MS Teams', 'wproject'); ?>: </span><?php echo $teams; ?></li>
            <?php } ?>
            <?php if($skype) { ?>
                <li><span><?php _e('Skype', 'wproject'); ?>: </span><?php echo $skype; ?></li>
            <?php } ?>
            <?php if($slack) { ?>
                <li><span><?php _e('Slack', 'wproject'); ?>: </span><?php echo $slack; ?></li>
            <?php } ?>
        </ul>

        <?php if($the_status || $last_login || $task_wip) { ?>
            <small><span><?php _e('Status', 'wproject'); ?></span></small>
            <ul>
                <?php if($the_status) { ?>
                    <li><span><?php _e('Status', 'wproject'); ?>: </span><?php echo $the_status; ?></li>
                <?php } ?>
                <?php if($last_login) { ?>
                    <li><span><?php _e('Last login', 'wproject'); ?>: </span><?php echo human_time_diff( $user->last_login, time() ) ; ?> <?php /* translators: Example: The user logged in 10 days ago */ _e('ago', 'wproject'); ?></li>
                <?php } ?>
                <?php if($task_wip) { ?>
                    <li><span><?php _e('Timing', 'wproject'); ?>: </span><a href="<?php echo get_the_permalink($task_wip) ?>"><?php echo get_the_title($task_wip) ?></a></li>
                <?php } ?>
            </ul>
        <?php } ?>

        <?php if(get_current_user_id() == $user_id) { ?>
        <div id="pinned">
            <small><span><?php _e('Highlighted tasks', 'wproject'); ?></span></small>
            <ul class="pinned-tasks">
                <?php
                    if($_COOKIE) {
                        $i = 1;
                        foreach ($_COOKIE as $key=>$val) {
                            if($key == 'fav_task_' . $val) { ?>
                            <li>
                                <a href="<?php echo get_the_permalink($val); ?>"><?php echo get_the_title($val); ?></a> <a class="del" data-id="<?php echo $val; ?>"><i data-feather="x"></i></a>
                            </li>
                        <?php $count = $i++; 
                            }
                        }
                    }
                    if($count == 0) {
                        echo '<script>$("#pinned").remove();</script>';
                    }
                ?>
            </ul>
            <script>
                $('.pinned-tasks li .del').click(function() {
                    var pinned_task_id = $(this).attr('data-id');
                    Cookies.remove('fav_task_'+pinned_task_id);
                    $(this).closest('li').fadeOut();
                });
            </script>
            <?php } ?>
        </div>
        
    </div>
    
</div>
<!--/ End User Profile /-->

<?php /* Help topics */
function user_profile_help() { ?>
    <p><?php _e('This page contains information about the user.', 'wproject'); ?></p>
<?php }
add_action('help_start', 'user_profile_help');

/* Side nav items */
function user_profile_nav() { 

    $options                = get_option( 'wproject_settings' );
    $client_project_access  = isset($options['client_project_access']) ? $options['client_project_access'] : '';
    
    $user_id            = isset($_GET['id']) ? $_GET['id'] : '';
    $user               = get_userdata($user_id);
    $first_name         = $user->first_name;
    $last_name          = $user->last_name;
    $full_name          = $first_name . ' ' . $last_name;
    $user_role          = $user->roles[0];
    $current_user_id    = get_userdata(get_current_user_id());
    $current_user_role  = $current_user_id->roles[0];
?>
    <li><a href="<?php echo get_the_permalink(100);?>"><i data-feather="user"></i><?php _e('My account', 'wproject'); ?></a></li>
    <li><a href="<?php echo get_the_permalink(108);?>"><i data-feather="users"></i><?php _e('See all team members', 'wproject'); ?></a></li>

    <?php if($user_role == 'client' && $client_project_access == 'specific') {
        if($current_user_role == 'administrator' || $current_user_role == 'project_manager') { ?>
        <li><a href="<?php echo admin_url();?>/user-edit.php?user_id=<?php echo $user_id; ?>#project-access"><i data-feather="lock"></i><?php _e('Project access', 'wproject'); ?></a></li>
    <?php }
    } ?>

    <?php if($current_user_role == 'project_manager' && get_current_user_id() != $user_id && $current_user_role != 'client' && $user_role != 'client' && $user_role != 'team_member' && $current_user_role != 'team_member' || $current_user_role == 'administrator' && get_current_user_id() != $user_id && $current_user_role != 'client' && $user_role != 'client' && $user_role != 'team_member' && $current_user_role != 'team_member') { ?>
    <form method="post" id="projects-transfer-form" enctype="multipart/form-data">
        <li class="transfer-projects"><a><i data-feather="shuffle"></i><?php printf( __('Give my projects to %1$s', 'wproject' ), $first_name); ?></a></li>
        <input type="hidden" name="this_user_id" value="<?php echo $user_id; ?>" />
    </form>

    <script>
        $(document).on('click', '.transfer-projects', function() {
                if (confirm('<?php printf( __('Use this option responsibly. If you proceed, you will be giving %1$s all your projects to manage. Continue?', 'wproject' ), $full_name); ?>')) {

                    if (confirm('<?php _e('Last chance. Are you sure?', 'wproject'); ?>')) {
                        setTimeout(function() { 
                            $('#projects-transfer-form').submit();
                        }, 500);
                    }  else {
                        /* Do nothing */
                    }
                }
            });
    </script>
    <?php } ?>

<?php }
add_action('side_nav', 'user_profile_nav');