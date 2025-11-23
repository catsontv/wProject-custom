<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="comments">
    <h3><?php _e('Recent Comments', 'wproject'); ?><?php if(wp_is_mobile()) { echo '<i data-feather="x"></i>'; } ?></h3>

    <?php 
    $wproject_settings = wProject();
    $comment_count = $wproject_settings['recent_comments_number'];

    if($comment_count) {
        $comment_count = $comment_count;
    } else {
        $comment_count = 5;
    }

    $args_comments = array(
        'status'        => 'approve',
        'post_status'   => 'publish',
        'posts_per_page' => -1
    );
    $recent_comments = get_comments($args_comments);

    $i = 0;
    $cc = 1;
    foreach ($recent_comments as $recent) {

        $comment            = get_comment($recent->comment_ID);
        $user_ID            = $comment->user_id;
        $user_info          = get_userdata($user_ID);
        $comment_post_id    = $comment->comment_post_ID;
        $comment_post_title = get_the_title($comment_post_id);
        $first_name			= @$user_info->first_name;
	    $last_name 			= @$user_info->last_name;
        $comment_status     = wp_get_comment_status( $recent->comment_ID );


            if($first_name == '') {
                $first_name = get_comment_author($comment);
                $initials = substr(get_comment_author($comment), 0, 1);

            } else {
                $initials = substr($first_name,0,1) . substr($last_name,0,1);
            }
        
            if($last_name == '') {
                $last_name = '';
            } else {
                $last_name = $last_name;
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

            $user_photo         = get_the_author_meta( 'user_photo', $user_ID );   
            if($user_photo) {
                $avatar         = $user_photo;
                $avatar_id      = attachment_url_to_postid($avatar);
                $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                $avatar         = $small_avatar[0];
                $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" />';
            } else {
                $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . $initials . '</div>';
            }

            $date_format    = get_option('date_format');
            $time_format    = get_option('time_format');
            $date_and_time  = $date_format . ' ' . $time_format;

            if($recent->comment_ID != 1) {

        ?>
            <div class="comment-0<?php echo $cc++; ?>">
                <a href="<?php echo get_comment_link($recent->comment_ID); ?>">
                    <?php echo $the_avatar; ?>
                    <strong>
                        <?php echo $first_name . " " . $last_name; ?>
                        <span><?php echo $comment_post_title; ?></span>
                    </strong>
                    <span class="message-date"><?php echo get_comment_date($date_and_time); ?></span>
                    <?php echo get_comment_excerpt($recent->comment_ID); ?>
                </a>
            </div>
        <?php }
        
        $i++;
        if($i == $comment_count) break;
    }
    ?>

</div>

<script>
    $( document ).ready(function() {
        $('.notify-comment').click(function() {
            $('.comments').toggle();
            $(this).toggleClass('active');
        });

        var comments_count = $('.comments div').length;
        if(comments_count == 0) {
            $('.comments').remove();
            $('.notify-comment').addClass('fade');
            $('.notify-comment').removeClass('active');
        }
    });
</script>