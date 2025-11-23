<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<h1><?php _e('Dashboard', 'wproject'); ?></h1>

<?php 
    $wproject_settings          = wProject();
    $dashboard_message          = $wproject_settings['dashboard_message'];

    $my_total_task_count        = my_total_task_count();
    $all_other_tasks_count      = all_other_tasks_count();
    $all_tasks_count            = all_tasks_count();
    $all_projects_count         = all_projects_count();

    $project_access             = $wproject_settings['project_access'];
    $users_can_create_tasks     = $wproject_settings['users_can_create_tasks'];

    if(function_exists('add_client_settings')) {
        $wproject_client_settings   = wProject_client();
        $client_create_own_tasks    = $wproject_client_settings['client_create_own_tasks'];
    } else {
        $client_create_own_tasks    = '';
    }

    if($my_total_task_count > 0) {
        $my_load = $my_total_task_count / $all_tasks_count * 100;
    } else {
        $my_load = 0;
    }

    $user                       = wp_get_current_user();
    $user_role                  = $user->roles[0];

    if($dashboard_message) {
        echo '<div class="dashboard-message"><i data-feather="message-square"></i>' . $dashboard_message . '</div>';
    }
?>

<!--/ Start Status Box /-->
<div class="status-box">

    <!--/ Start Stats /-->
    <div class="stats full-width">

        <?php if($my_total_task_count > 0) { ?>   
            <div title="<?php echo $my_total_task_count; ?>">
                <strong>
                    <?php 
                        if($my_total_task_count == 1) {
                            _e( 'You only have 1 task', 'wproject' );
                        } else {
                        /* translators: Example: You have 2 tasks out of 9 to do. */  _e( 'Your tasks', 'wproject'); 
                        }
                    ?>
                </strong>
                <span class="value your-tasks-value"><?php echo $my_total_task_count; ?></span>

                <em class="fill-container">
                    <em class="fill" style="width:<?php echo round($my_load, 1); ?>%"></em>
                </em>
            </div>
        <?php } else { ?>
            <div>
                <strong><?php _e('No tasks for you yet.', 'wproject'); ?></strong>
            </div>
        <?php } ?>

        <div title="<?php echo round($my_load, 1); ?>%">
            <strong>
                <?php printf( esc_html__( 'Your responsibility', 'wproject' ), round($my_load, 1) ); ?>
            </strong>

            <span class="value"><?php echo round($my_load, 1); ?><sup>%</sup></span>

            <em class="fill-container">
                <em class="fill" style="width:<?php echo round($my_load, 1); ?>%"></em>
            </em>
        </div>

       
        <div title="<?php echo round(100 - $my_load, 1); ?>%">
            <strong> 
                <?php _e('Tasks for others', 'wproject' ); ?>
            </strong>   

            <span class="value"><?php echo $all_other_tasks_count; ?></span>

            <?php if($all_other_tasks_count > 0) { ?>
            <em class="fill-container">
                <em class="fill" style="width:<?php echo round(100 - $my_load, 1); ?>%"></em>
            </em>
            <?php } ?>
        </div>
      
        
        <div>
            <strong>
                <?php _e('All Tasks', 'wproject' ); ?>
            </strong>

            <span class="value">
                <?php 
                    if($all_tasks_count == .1) {
                        echo 0;
                    } else {
                        echo $all_tasks_count;
                    }
                ?>
            </span>
            <em class="fill-container">
                <em class="fill" style="width:100%"></em>
            </em>
        </div>
     

    </div>
    <!--/ End Stats /-->

</div>
<!--/ End Status Box /-->