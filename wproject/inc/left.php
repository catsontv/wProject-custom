<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<!--/ Start Left /-->

<section class="left">
<?php if(empty($_GET['print'])) { ?>
    <?php 
        
        do_action( 'avatar' );
        get_template_part('inc/create');
        get_template_part('inc/nav');
        get_template_part('inc/pages');
    
    } else { // We need the nav because some counts are targeted with JS.
    get_template_part('inc/nav');
} ?>
</section>
<!--/ End Left /-->