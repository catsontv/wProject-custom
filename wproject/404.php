<?php get_header(); ?>

<div class="container">

    <?php get_template_part('inc/left'); ?>

    <!--/ Start Section /-->
    <section class="middle <?php if(!is_404()) { echo $post->post_name; } else if(is_404()) { echo 'page-404'; } ?>">

        <div>
            <img src="<?php echo get_template_directory_uri();?>/images/robot.svg" class="searchbot" />
            <h1><?php _e('404', 'wproject');?></h1>
            <p><?php /* translators: The message on the 404 page */ _e('Bob the bot is very sorry that he could not find whatever it was you were looking for. Have you tried the search bar?', 'wproject'); ?></p>
        </div>

    </section>
    <!--/ End Section /-->
    <?php get_template_part('inc/right'); ?>    
    <?php get_template_part('inc/help'); ?> 

</div>
<?php get_footer(); ?>