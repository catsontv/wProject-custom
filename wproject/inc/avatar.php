<!--/ Start User /-->
<?php if ( ! defined( 'ABSPATH' ) ) { exit; }
    wp_get_current_user();

    $user_photo         = isset(user_details()['user_photo']) ? user_details()['user_photo'] : '';
    $author_id          = get_current_user_id();
    $first_name         = get_the_author_meta( 'first_name', $author_id );
    $last_name          = get_the_author_meta( 'last_name', $author_id );
    $user               = get_userdata($author_id);
    $role               = $user->roles[0];

    if($first_name && $last_name) {

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

    

        $avatar = get_template_directory_uri() . '/images/default-user.png';
        if($user_photo) {
            $avatar         = $user_photo;
            $avatar_id      = attachment_url_to_postid($avatar);
            $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
            $avatar         = $small_avatar[0];
            $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" id="avatar" />';
        } else {
            if(is_page(100)) {
                $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . $first_name[0] . $last_name[0] . '<img id="avatar" style="opacity:0" /></div>';
            } else {
                $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . $first_name[0] . $last_name[0] . '</div>';
            }
        }

    }
?>
<div class="user role-<?php echo $role; ?>">

    <?php if($first_name && $last_name) { ?>
        <a href="<?php echo get_the_permalink(100); ?>" class="avatar">
            <?php echo $the_avatar; ?>
            <i data-feather="user"></i>
        </a>
    <?php } ?>

    <div class="deets">
        <strong><?php echo $first_name; ?> <?php echo $last_name; ?></strong>

        <?php if($role != 'observer' && my_total_task_count() > 0) { ?>
            <span class="bars">
                <span class="fill" style="width:<?php echo my_total_task_count() / all_tasks_count() * 100; ?>%"></span>
            </span>
            <em>
                <?php
                    if(my_total_task_count() == 1) {
                        _e( 'You only have 1 task to do.', 'wproject' );
                    } else if(my_total_task_count() > 1) {
                        /* translators: Example: You have 2/9 (22.2%) tasks to do. */ printf( __( '%1$s/%2$s (%3$s&#37) tasks to do', 'wproject' ), my_total_task_count(), all_tasks_count(), round(my_total_task_count() / all_tasks_count() * 100, 1) ); 
                    } else if(my_total_task_count() < 1) {
                        _e( 'No tasks for you yet.', 'wproject' );
                    }
                ?>
           </em>
        <?php } ?>
    </div>
</div>
<!--/ End User /-->

<script>
    /* Preview the image */
    
    var loadFile = function(event) {
        var output = document.getElementById('avatar');
        output.src = URL.createObjectURL(event.target.files[0]);
        $('#avatar').css('opacity', '1');
        $('.letter-avatar').css('background', 'none');
    };
    
</script>