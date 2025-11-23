<?php if ( ! defined( 'ABSPATH' ) ) { exit; }
    $options 				    = get_option( 'wproject_settings' );
    $users_can_create_tasks	    = isset($options['users_can_create_tasks']) ? $options['users_can_create_tasks'] : '';

    if(function_exists('add_client_settings')) {
        $wproject_client_settings   = wProject_client();
        $client_create_own_tasks    = $wproject_client_settings['client_create_own_tasks'];
    } else {
        $client_create_own_tasks    = '';
    }

    $user       = wp_get_current_user();
    $user_role  = !empty($user->roles) ? $user->roles[0] : '';
?>

<?php if($user_role == 'team_member' && $users_can_create_tasks == 'on' || $user_role == 'client' && $client_create_own_tasks) { ?>
<a href="<?php echo get_the_permalink(105); ?>" class="new btn-light">
    <i data-feather="plus-circle"></i><?php _e('Task', 'wproject'); ?>
</a>
<?php } ?>

<?php if($users_can_create_tasks == 'on' && $user_role == 'project_manager' || $user_role == 'administrator') { ?>
    <div class="new btn-light">
        <i data-feather="plus-circle"></i><?php _e('Create', 'wproject'); ?>
    </div>
    <nav class="create">
        <ul>
            <?php do_action( 'create_start' ); ?>
            <li><a href="<?php echo get_the_permalink(105); ?>"><i data-feather="check-circle-2"></i><?php _e('Task', 'wproject'); ?></a></li>
            <li><a href="<?php echo get_the_permalink(104); ?>"><i data-feather="folder-plus"></i><?php _e('Project', 'wproject'); ?></a></li>
            <?php do_action( 'create_end' ); ?>
        </ul>
    </nav>
    <script>
        $('.create').css('display', 'none');
        $('.new').click(function() {
            $(this).toggleClass('active');
            $('.create').slideToggle(120);
        });

        /* Add class to last nav item if odd number, so it spans the width */
        var nav_item_count = $('.create ul li').length;
        if (nav_item_count %2 == 0) {
            
        } else {
            $('.create ul li:last-child').addClass('full-width');
        }
    </script>
<?php } ?>