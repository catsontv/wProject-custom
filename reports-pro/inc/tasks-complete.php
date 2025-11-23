<ul class="report-tasks">
    <?php 
    $complete_tasks = array(
        'post_type'         => 'task',
        'post_status'		=> 'publish',
        'category' 			=> $report_id,
        'orderby' 			=> 'name',
        'order' 			=> 'ASC',
        'posts_per_page'    => -1,
        'meta_key'          => 'task_status',
        'meta_value'        => array('complete'),
        'tax_query' => array(
            array(
                'taxonomy' => 'project',
                'field'    => 'slug',
                'terms'    => array( $slug ),
                'operator' => 'IN'
            ),
        ),
    );
    $query = new WP_Query($complete_tasks);
    while ($query->have_posts()) : $query->the_post();

        $task_id                = get_the_id();
        $author_id              = get_post_field ('post_author', $task_id);
        $user_ID                = get_the_author_meta( 'ID', $author_id );
        $first_name             = get_the_author_meta( 'first_name', $author_id );
        $last_name              = get_the_author_meta( 'last_name', $author_id );
        $task_job_number        = get_post_meta($task_id, 'task_job_number', TRUE);
        $task_start_date        = get_post_meta($task_id, 'task_start_date', TRUE);
        $task_end_date          = get_post_meta($task_id, 'task_end_date', TRUE);
        $task_time              = get_post_meta($task_id, 'task_time', TRUE);
        $task_files             = get_post_meta($task_id, 'task_files', TRUE);
        $task_description       = get_post_meta($task_id, 'task_description', TRUE);
        $task_milestone         = get_post_meta($task_id, 'task_milestone', TRUE);
        $task_private           = get_post_meta($task_id, 'task_private', TRUE);
        $task_total_time        = get_post_meta($task_id, 'task_total_time', TRUE);
        $task_pc_complete       = get_post_meta($task_id, 'task_pc_complete', TRUE);
        $user_photo             = get_the_author_meta( 'user_photo', $author_id );
        $post_status            = get_post_status ($task_id);

        if($task_start_date || $task_end_date) {
            $new_task_start_date    = new DateTime($task_start_date);
            $the_task_start_date    = $new_task_start_date->format($date_format);
    
            $new_task_end_date      = new DateTime($task_end_date);
            $the_task_end_date      = $new_task_end_date->format($date_format);
        } else {
            $the_task_start_date    = '';
            $the_task_end_date      = '';
        }

        if($user_photo) {
            $avatar         = $user_photo;
            $avatar_id      = attachment_url_to_postid($avatar);
            $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
            $avatar         = $small_avatar[0];
        } else {
            $avatar = get_template_directory_uri() . '/images/default-user.svg';
        }

        $task_status    = task_status();
        $task_priority  = task_priority();

        /* Overdue check and class */
        $due_date = strtotime($task_end_date);
        $overdue_class = '';
        if($now > $due_date && $task_status !='complete') {
            $overdue_class = 'overdue';
        }

        setup_postdata($post);
    ?>
        <li class="priority <?php echo $overdue_class; ?> <?php echo $task_priority['slug']; ?> <?php echo $task_status['slug']; ?>">
        <?php if($task_private == 'yes') { ?>
            <?php if($author_id == $current_author) { ?>
                <a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?><?php if($task_pc_complete) { echo ' - ' . $task_pc_complete; ?><?php _e('%', 'wproject'); } ?></a>
            <?php } else { ?>
                <?php _e('Private task', 'wproject'); ?>
            <?php } ?>
        <?php } else { ?>
            <a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a>
        <?php } ?>
        </li>
    <?php endwhile; ?>
</ul>