<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<!--/ Start Right /-->
<section class="right">
    <?php do_action( 'before_side_nav' ); ?>
    <ul>
        <!--/ Default wProject items /-->
        <?php do_action( 'side_nav' ); ?>    
    </ul>
    
    <ul class="side-nav-other">
        <!--/ Other wProject items /-->
        <?php do_action( 'side_nav_other' ); ?>
    </ul>
    
    <script>
        /* Remove the .side-nav-other element if it doesn't have anything inside it. */
        if($('.side-nav-other li').length === 0) {
            $('.side-nav-other').remove();
        }
    </script>

    <!--/ Start Sidebar Widget /-->
    <?php if ( is_active_sidebar( 'wproject-sidebar-widget' ) ) { 
        dynamic_sidebar( 'wproject-sidebar-widget' );
    } ?>
    <!--/ End Sidebar Widget /-->

</section>
<!--/ End Right /-->