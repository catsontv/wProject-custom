<?php if ( ! defined( 'ABSPATH' ) ) { exit; }

$current_author     = get_current_user_id();
$date_format        = get_option('date_format'); 
$now                = strtotime('today');
$user_info          = get_userdata(get_current_user_id());
$fav_tasks          = $user_info->fav_tasks;
$fav_task_array     = explode(',', $fav_tasks); /* Convert to proper array */
?>
<!--/ Start Follows /-->
<div class="my-follows">
    <h3>
        <?php _e('Followed tasks', 'wproject'); ?>
        <?php if(wp_is_mobile()) { echo '<i data-feather="x"></i>'; } ?>
    </h3>
    <form id="follows-form" method="post" enctype="multipart/form-data">

        <?php 
            /* Debugging
            echo gettype($fav_task_array); ?> <br />
            <?php print_r($fav_task_array);
            */
        ?>

        <?php $fav_args = array(
            'post_type'         => 'task',
            'post__in'          => $fav_task_array,
            'order_by'          => 'title',
            'order'             => 'DESC',
            'post_type'         => 'task',
            'post_status'       => 'publish'
        );
        $fav_query              = new WP_Query($fav_args);
        $ft = 1;

        while ($fav_query->have_posts()) : $fav_query->the_post();

            $task_id            = get_the_ID();
            $task_start_date    = get_post_meta($task_id, 'task_start_date', TRUE);
            $task_end_date      = get_post_meta($task_id, 'task_end_date', TRUE);
            $task_priority      = get_post_meta($task_id, 'task_priority', TRUE);
            $task_status        = get_post_meta($task_id, 'task_status', TRUE);

            if ( 'publish' == get_post_status ( $task_id ) ) {

            /* Overdue check and class */
            $due_date           = strtotime($task_end_date);
            $overdue_class = '';
            if($now && $due_date && $now > $due_date && $task_status !='complete') {
                $overdue_class = 'overdue';
            }

            if($task_status == 'complete') {
                $the_task_status = '<i data-feather="check-circle-2"></i>' . __('Complete', 'wproject');
            } else if($task_status == 'incomplete') {
                $the_task_status = __('Incomplete', 'wproject');
            } else if($task_status == 'on-hold') {
                $the_task_status = __('On hold', 'wproject');
            } else if($task_status == 'in-progress') {
                $the_task_status = __('In progress', 'wproject');
            } else {
                $the_task_status = __('Not started', 'wproject');
            }

            if($task_start_date || $task_end_date) {
                $new_task_start_date     = new DateTime($task_start_date);
                $the_task_start_date     = $new_task_start_date->format($date_format);

                $new_task_end_date       = new DateTime($task_end_date);
                $the_task_end_date       = $new_task_end_date->format($date_format);
            } else {
                $the_task_start_date = '';
                $the_task_end_date   = '';
            }
            
            $author_id          = get_post_field( 'post_author', $task_id );
            $user_photo         = get_the_author_meta( 'user_photo', $author_id );
            $user_meta          = get_userdata($author_id);
            $first_name			= isset($user_meta->first_name) ? $user_meta->first_name : '';
            $last_name 			= isset($user_meta->last_name) ? $user_meta->last_name : '';

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
                $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                $avatar         = $small_avatar[0];
                $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" />';
            } else {
                $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . $first_name[0] . $last_name[0] . '</div>';
            }
        ?>
        <div class="follow-task-<?php echo $task_id; ?> <?php echo $overdue_class; ?> follow-0<?php echo $ft++; ?> priority <?php echo $task_priority; ?>">
            <a href="<?php echo get_the_permalink(); ?>">
                <?php echo $the_avatar; ?>
                <strong>
                    <?php echo $first_name; ?> <?php echo $last_name; ?>
                    <?php 
                    $terms = get_the_terms( $task_id , 'project' );
                    if($terms !='') {
                    foreach ( $terms as $term ) { ?>
                        <span><?php echo $term->name; ?></span>
                    <?php } 
                    }  else {
                        _e('No project', 'wproject');
                    } ?>
                </strong>
                <?php if($task_end_date) { ?>
                <span class="message-date"><?php _e('Due', 'wproject'); ?>: <?php echo $the_task_end_date; ?></span>
                <?php } ?>
                <p>
                    <?php echo get_the_title($task_id); ?>
                </p>
                <span class="status <?php echo $task_status; ?>">
                    <?php echo $the_task_status; ?>
                </span>
            </a>
            <span class="unfollow" data-unfollow-id='<?php echo $task_id; ?>'>
                <i data-feather="star"></i>
            </span>
        </div>
        <?php 
        }
        endwhile;
        wp_reset_postdata(); ?>

        <input type="hidden" name="follow-status" class="follow-status" value="" />
        <input type="hidden" name="task-id" class="task-id" value="" />

        <script>
            $( document ).ready(function() {
                $('.my-favs').click(function() {
                    $('.my-follows').toggle();
                    $(this).toggleClass('active');
                });
                $('.my-follows .unfollow').click(function() {
                    var unfollow_task_id = $(this).data('unfollow-id');
                    $('.task-id').val(unfollow_task_id);
                    $(this).closest('div').addClass('updating');

                    /* Submit the form */
                    setTimeout(function() { 
                        $('#follows-form').submit();
                    }, 500)
                });
            });

            var follow_count = $('#follows-form div.priority').length;
            if(follow_count > 0) {
                $('.my-favs').prepend('<span class="message-count follows">'+follow_count+'</span>');
                $('.my-favs .icon-container.fade').removeClass('fade');
            } else {
                $('.my-favs .icon-container.fade').addClass('fade');
            }
        </script>

    </form>
</div>
<!--/ End Follows /-->