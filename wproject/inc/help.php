<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<!--/ Start Help /-->
<div class="help">
    <h3><?php _e('Help', 'wproject'); ?><i data-feather="x"></i></h3>
    <?php do_action( 'help_start' ); ?>
    <?php do_action( 'help_end' ); ?>
</div>
<!--/ End Help /-->

<script>
    $( document ).ready(function() {
        $('.toggle-help, .help h3, .toggle-help.active').click(function() {
            $('.help').toggleClass('move');
            $(this).toggleClass('active');
            $('.toggle-sidebar').removeClass('active');
        });
        $('.help').find('h4:first').addClass('help-01');

        <?php if(is_front_page() && !wp_is_mobile()) { ?>
            // $('.help').addClass('move');
            // $('.toggle-help').addClass('active');
        <?php } ?>

    });
</script>