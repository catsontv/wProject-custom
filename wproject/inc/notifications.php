<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="notifications dropdown">
    <h3>
        <?php _e('Notifications', 'wproject'); ?> 
        <em title="<?php _e('Mark all as read', 'wproject'); ?> "><i data-feather="check-square"></i></em>
    </h3>
    <?php messages(); ?>
</div>
<form class="read-all-messages" id="read-all-messages-form" method="post" enctype="multipart/form-data">
</form>

<script>
    $( document ).ready(function() {
        $('.notify').click(function() {
            $('.notifications').toggle();
        });

        $('.notify').click(function() {
            $(this).toggleClass('active');
        });
    });
    $('.notifications h3 em').click(function() {
        if(confirm('<?php _e('Mark all message as read?', 'wproject'); ?>' )){
            $('#read-all-messages-form').submit();
        }
        else{
            
        }
    });
</script>