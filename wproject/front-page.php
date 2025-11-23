<?php get_header(); ?>

<div class="container">

    <?php 
        get_template_part('inc/left');
        get_template_part('inc/home');
        
        if(empty($_GET['print'])) {
            get_template_part('inc/right');   
        }
        
        if(empty($_GET['print'])) {
            get_template_part('inc/help');
        }
    ?> 

</div>
<?php get_footer(); ?>