<?php get_header(); 

    $wproject_settings          = wProject();
    $project_access             = $wproject_settings['project_access'];

    if(function_exists('add_client_settings')) {
        $wproject_client_settings   = wProject_client();
        $client_use_search          = $wproject_client_settings['client_use_search'];
    } else {
        $client_use_search          = '';
    }

    $user 					    = wp_get_current_user();
    $user_role 				    = $user->roles[0];

    $results                    = isset($_GET['s']) ? $_GET['s'] : '';

?>

<div class="container">

    <?php get_template_part('inc/left'); ?>

    <!--/ Start Section /-->
    <section class="middle <?php echo $post->post_name; ?> search-results">

        <?php if($user_role == 'administrator' || $user_role == 'project_manager' || $user_role == 'team_member' || $user_role == 'observer') {
        
        if (have_posts()) : ?>

        <h1><?php printf(__('Search Results for <span>%1$s</span>', 'wproject'), $results) ?></h1>

        <ul>
            <?php while (have_posts()) : the_post();
                $task_id            = get_the_ID();
                $date_format        = get_option('date_format'); 
                $task_description   = get_post_meta($task_id, 'task_description', TRUE);
                $task_private       = get_post_meta($task_id, 'task_private', TRUE);
                $task_status        = get_post_meta($task_id, 'task_status', TRUE);
                $task_end_date      = get_post_meta($task_id, 'task_end_date', TRUE);
                $task_job_number    = get_post_meta($task_id, 'task_job_number', TRUE);
                $task_priority      = get_post_meta($task_id, 'task_priority', TRUE);
                $mime               = get_post_mime_type( $id );

                if($task_priority ) {
                    $task_priority = $task_priority;
                } else {
                    $task_priority = 'normal';
                }

                if($task_end_date) {
                    $new_task_end_date       = new DateTime($task_end_date);
                    $the_task_end_date       = $new_task_end_date->format($date_format);
                }
                if('task' == get_post_type()) { /* Return tasks */
            ?>
            <li class="<?php echo $task_priority; ?>" title="<?php _e('Date created', 'wproject'); ?>: <?php echo get_the_date(); ?>">
                <strong>
                    <a href="<?php the_permalink() ?>">
                        <?php 
                            if($task_private == 'yes') {
                                $task_name = __('Private task', 'wproject');
                            } else {
                                $task_name = the_title();
                            }
                            echo $task_name;
                        ?>
                    </a>
                </strong>
                
                
                <?php if($task_description) { ?>
                <span class="description"><?php echo $task_description; ?></span>
                <?php } ?>

                <span class="details">

                    <span><strong><?php _e('Status', 'wproject'); ?>:</strong> <?php echo ucfirst(str_replace('-', ' ', $task_status)); ?></span>
                    
                    <?php if($task_end_date) { ?>
                    <span><strong><?php _e('Due', 'wproject'); ?>:</strong> <?php echo $the_task_end_date; ?></span>
                    <?php } ?>

                    <?php if($task_job_number) { ?>
                    <span><strong><?php _e('Job #', 'wproject'); ?>:</strong> <?php echo $task_job_number; ?></span>
                    <?php } ?>

                    <span><strong><?php _e('Date created', 'wproject'); ?>:</strong> <?php echo get_the_date(); ?></span>

                </span>
            </li>
            <?php } else if('page' == get_post_type() && $user_role != 'client') { /* Return pages */ ?>
                <li class="icon page" title="<?php _e('Date created', 'wproject'); ?>: <?php echo get_the_date(); ?>">
                    <a href="<?php echo get_the_permalink();?>">
                        <i data-feather="file-text"></i>
                        <?php echo get_the_title();?>
                    </a>
                </li>
            <?php } else if('attachment' == get_post_type() && $user_role != 'client') { /* Return attachments */ ?>
                <li class="icon attachment" title="<?php _e('Date created', 'wproject'); ?>: <?php echo get_the_date(); ?>">
                    <a href="<?php echo wp_get_attachment_url( $id );?>">
                        <i data-feather="paperclip"></i>
                        <?php echo get_the_title();?>
                    </a>
                    <?php if($mime == 'image/jpeg' || $mime == 'image/pjpeg' || $mime == 'image/png' || $mime == 'image/gif') {
                        $image_attributes = wp_get_attachment_image_src($id, 'full');
                        ?>
                        <a href="<?php echo wp_get_attachment_url( $id );?>">
                            <span>
                                <img src="<?php echo wp_get_attachment_image_url( $id, array( 100, 100) ); ?>" class="image-preview" />
                                <em class="image-attributes">
                                    <?php echo $image_attributes[1]; ?> x <?php echo $image_attributes[2]; ?>
                                </em>
                            </span>
                        </a>
                    <?php } ?>
                </li>
            <?php } else if('contacts_pro' == get_post_type() && $user_role != 'client') { /* Return contacts */ ?>
                <li class="icon contact" title="<?php _e('Date created', 'wproject'); ?>: <?php echo get_the_date(); ?>">
                    <a href="<?php echo get_the_permalink(); ?>">
                        <i data-feather="briefcase"></i>
                        <?php echo get_the_title();?>
                    </a>
                </li>
            <?php } ?>
        <?php endwhile; ?>
        </ul>

        <?php else : ?>

        <img src="<?php echo get_template_directory_uri();?>/images/robot.svg" class="searchbot" />
        <h2><?php printf(__('Bob the bot could not find <span>%1$s</span>.', 'wproject'), $_GET['s']) ?></h2>

        <?php endif;
        } ?>

    </section>
    <!--/ End Section /-->
    <?php get_template_part('inc/right'); ?>    
    <?php get_template_part('inc/help'); ?> 

</div>
<?php get_footer(); ?>