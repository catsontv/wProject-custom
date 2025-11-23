<?php if ( ! defined( 'ABSPATH' ) ) { exit; } 
function cool_tips() { 

    $author_id          = get_current_user_id();
    $first_name         = get_the_author_meta( 'first_name', $author_id );

if(empty($_GET['print'])) { ?> 
<!--/ Start Tips /-->

<div class="tips">

    <p><strong><?php _e('Welcome back', 'wproject'); ?>, <?php echo $first_name; ?>.</strong></p>

    <?php get_template_part('inc/date-time'); ?>

    <?php
    $strings = array(
        __("You can change the status of a task to Complete, Incomplete, On Hold, In Progress or Not Started.", "wproject"),
        __("If the option is enabled, you can request ownership of a task that you don't own.", "wproject"),
        __("Because you are a human, you can only record time on one task at any given moment.", "wproject"),
        __("Check your notifications for any important messages.", "wproject"),
        __("Tasks can have subtasks, and can be checked off as you complete them.", "wproject"),
        __("Tasks can have relationships with other tasks, such as blocks, similarities or issues.", "wproject"),
        __("If you accidentally recorded too much time on a task, you can manually edit the time entry.", "wproject"),
        __("If you complete all your subtasks, the task will automatically be marked as complete.", "wproject"),
        __("Project managers and administrators can edit any task regardless of who owns it.", "wproject"),
        __("Project managers can only edit or delete projects they manage.", "wproject"),
        __("You can pin important tasks while on a project page.", "wproject"),
        __("If a task has multiple files, you can download them all with a single click.", "wproject"),
        __("If you use the filters on a project page, copying and pasting the URL will remember the view state.", "wproject"),
        sprintf(__('Go to your <a href="%1$s">account</a> page to tweak your preferences', 'wproject'), home_url() . '/account'),
        __("Clicking 'Add a task' from a project page will automatically select that project for the task.", "wproject"),
        __("The Kanban board can be enabled in wProject settings.", "wproject"),
        __("Quickly switch between light and dark modes by clicking the sun/moon icons.", "wproject"),
        __("As a project manager, you can transfer all your projects when you visit the team page of another project manager.", "wproject"),
        __("When typing into the search bar, tasks on the current page will be filtered in real-time. Press enter to go to the search results page instead.", "wproject"),
        __("Toggle between light and dark modes by clicking the sun or moon icons at the top.", "wproject")
    );
    $key = array_rand($strings);
    ?>
    <p><strong><?php _e('Pro tip:', 'wproject'); ?></strong> <?php echo $strings[$key]; ?></p>
</div>
<!--/ End Tips /-->
<?php }
}
add_action('side_nav', 'cool_tips', 10); ?>