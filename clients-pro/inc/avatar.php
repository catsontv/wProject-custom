<!--/ Start User /-->
<?php if ( ! defined( 'ABSPATH' ) ) { exit; }
wp_get_current_user();

    $user_photo = isset(user_details()['user_photo']) ? user_details()['user_photo'] : '';
    $mood       = isset(user_details()['mood']) ? user_details()['mood'] : '';
    $first_name = user_details()['first_name'];
    $last_name  = user_details()['last_name'];

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
<div class="user">
    <a href="<?php echo get_the_permalink(100); ?>" class="avatar">
        <?php echo $the_avatar; ?>
    </a>
    <div class="details">
        <strong><?php echo $first_name; ?> <?php echo $last_name; ?></strong>
        <em>
        <?php
            if(my_total_task_count() == 1) {

                _e( 'You only have 1 task to do.', 'wproject-clients-pro' );

            } else if(my_total_task_count() > 1) { ?>

                <script>
                    $('.left .user em').text('<?php _e('You have', 'wproject-clients-pro'); ?> <?php echo my_total_task_count(); ?> <?php _e('tasks to do.', 'wproject-clients-pro'); ?>');
                </script>

            <?php } else if(my_total_task_count() < 1) {

                _e( 'No tasks for you yet.', 'wproject-clients-pro' );

            }
        ?>
        </em>
    </div>
</div>
<!--/ End User /-->
<script>
    /* Preview the image */
    var loadFile = function(event) {
        var output = document.getElementById('avatar');
        output.src = URL.createObjectURL(event.target.files[0]);
    };
</script>