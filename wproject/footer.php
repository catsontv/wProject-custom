<?php do_action( 'before_wp_footer' ); ?>    

<?php wp_footer(); ?>

<?php do_action( 'after_wp_footer' ); ?>    

<?php 
    /* Kanban */ 
    if(is_tax()) {
        require_once('inc/kanban.php');
    }
?>

<?php do_action( 'before_body_end' ); ?>

</body>

<?php do_action( 'after_body_end' ); ?>

</html>