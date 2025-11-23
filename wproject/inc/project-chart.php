<!--/ Start Chart /--->
<div class="projects-chart">
    <div class="projects-chart-container">
    <?php
        $projects = array(
            'taxonomy'      => 'project',
            'orderby'       => 'name',
            'parent'        => 0,
            'hide_empty'    => 1,
            'order'         => 'ASC',
            'meta_query' => array(
                array(
                    'key'       => 'project_manager',
                    'value'     => get_current_user_id(),
                    'compare'   => '=='
                )
            ),
        );
        $cats = get_categories($projects);
        $all_projects_count = count( $cats );
        
        $wproject_settings          = wProject(); 
        $enable_time			    = $wproject_settings['enable_time'];

        $tasks_query = new WP_Query( $projects );
        $project_count = $tasks_query->found_posts;
        $count1 = 0;
        $count2 = 0;
        $count3 = 0;
        $count4 = 0;
        $loops  = 0;
        $chart_labels = array();
        $chart_progress = array();

        if(wp_is_mobile()) {
            $bar_margin = '5px';
        } else {
            $bar_margin = '20px';
        }

        foreach($cats as $cat) {
            $date_format        		= get_option('date_format'); 
            $term_id                    = $cat->term_id; 
            $term_meta                  = get_term_meta($term_id); 
            $term_object                = get_term( $term_id );
            $project_status             = $term_meta['project_status'][0];
            $pm_user                    = get_user_by('ID', $term_meta['project_manager'][0]);
            $pm_name                    = $pm_user->first_name . ' ' . $pm_user->last_name;

            $project_start_date         = $term_meta['project_start_date'][0];
            $project_end_date           = $term_meta['project_end_date'][0];
            $project_time_allocated     = $term_meta['project_time_allocated'][0];
            $project_hourly_rate        = $term_meta['project_hourly_rate'][0];
            $project_job_number         = $term_meta['project_job_number'][0];
            $project_total_time     	= get_term_meta($term_id, 'project_total_time', TRUE);

            $end_date_timestamp         = strtotime($project_end_date);
            $current_timestamp          = time();
            
            
            if($project_time_allocated && $project_hourly_rate) {
                $budget = $project_time_allocated * $project_hourly_rate;
            }

            
            if($project_time_allocated) {
                $project_time_allocated = $project_time_allocated;
            }

            
            if($project_total_time) {
                $hours              = floor(intval($project_total_time / 3600));
                $minutes            = floor(intval($project_total_time / 60) % 60);
                $seconds            = $project_total_time % 60;
            } else {
                $hours              = '00';
                $minutes            = '00';
                $seconds            = '00';
            }
            
            if ($project_end_date && $end_date_timestamp < $current_timestamp) {
                $overdue_class = 'overdue';
            } else {
                $overdue_class = '';
            }

            if($project_start_date || $project_end_date) {
                $new_project_start_date     = new DateTime($project_start_date);
                $the_project_start_date     = $new_project_start_date->format($date_format);
        
                $new_project_end_date       = new DateTime($project_end_date);
                $the_project_end_date       = $new_project_end_date->format($date_format);
            } else {
                $the_project_start_date = '-';
                $the_project_end_date   = '-';
            }

            /* Project status */
            if($project_status == 'in-progress') {
                $the_project_status = __('In progress', 'wproject');
            } else if($project_status == 'planning') {
                $the_project_status = __('Planning', 'wproject');
            } else if($project_status == 'proposed') {
                $the_project_status = __('Proposed', 'wproject');
            } else if($project_status == 'setting-up') {
                $the_project_status = __('Setting up', 'wproject');
            } else if($project_status == 'archived') {
                $the_project_status = __('Archived', 'wproject');
            } else if($project_status == 'cancelled') {
                $the_project_status = __('Cancelled', 'wproject');
            } else if($project_status == 'complete') {
                $the_project_status = __('Complete', 'wproject');
            }

            $wproject_settings = wProject();

            /* Get the total number of tasks in each project */
            $completed_tasks_project_args = array(
                'posts_per_page' 	=> -1,
                'post_type' 		=> 'task',
                'post_status'		=> array('publish', 'private'),
                'numberposts'       => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'project',
                        'field'    => 'slug',
                        'terms'    => array( $cat->slug ),
                        'operator' => 'IN'
                    ),
                ),
                'meta_key' 		=> 'task_status',
                'meta_value'	=> array('complete')
            );
            $completed_tasks_posts_project = new WP_Query($completed_tasks_project_args);
            $completed_tasks_project = $completed_tasks_posts_project->post_count;

            if($completed_tasks_project > 0) {
                $progress = $completed_tasks_project / $cat->count * 100;
            } else {
                $progress = 0;
            }

            $remaining_progress = 100 - round($progress, 1);

            //echo $all_projects_count;
            $colors = array(
                '#7bc8a4','#f16745','#ffc65d','#4cc3d9','#c178b8','#90a9e6','#f89478','#af96a0','#66bbb1','#ef6778','#f67d5a','#585471','#7bc8a4','#f16745','#ffc65d','#4cc3d9','#c178b8','#90a9e6','#f89478','#af96a0','#66bbb1','#ef6778','#f67d5a','#585471','#7bc8a4','#f16745','#ffc65d','#4cc3d9','#c178b8','#90a9e6','#66bbb1','#ef6778','#f67d5a','#585471','#7bc8a4','#f16745','#ffc65d','#4cc3d9','#c178b8','#90a9e6'
            );

            if($project_status !== 'complete' && $project_status !== 'cancelled' && $project_status !== 'archived' && $project_status !== 'inactive' && $project_status !== 'on-hold') {

        ?>

            <div class="project-bar <?php if($progress <= 3) { echo 'low-progress';  } ?>" id="project-id-<?php echo $term_id; ?>">
                <span class="project-name" style="text-shadow: .85px .85px 0 <?php echo $colors[$count1++]; ?>;"><?php echo $cat->name; ?></span>
                <?php if(round($progress, 1) == 100) { ?>
                    <i data-feather="thumbs-up"></i>
                <?php } else { ?>
                    <span class="pc-complete"><?php echo round($progress, 1); ?>%</span>
                <?php } ?>

                <em class="remaining" style="height:100%; background:<?php echo $colors[$count2++]; ?>"></em>
                <em class="progress" style="height:<?php echo round($progress, 1); ?>%; background:<?php echo $colors[$count3++]; ?>"></em>
                <span class="details">
                    <strong class="mobile-title" style="background: <?php echo $colors[$count4++]; ?>;"><?php echo $cat->name; ?></strong>
                    <dl>
                        <dt><?php _e('Status', 'wproject'); ?>:</dt>
                        <dd><?php echo $the_project_status; ?> (<?php echo round($progress, 1); ?>%)</dd>
                        <dt><?php _e('Start date', 'wproject'); ?>:</dt>
                        <dd><?php echo $the_project_start_date; ?></dd>
                        <dt><?php _e('Due date', 'wproject'); ?>:</dt>
                        <dd class="<?php if(round($progress, 1) != 100) { echo $overdue_class; } ?>"><?php echo $the_project_end_date; ?></dd>

                        <?php if($enable_time) { ?>

                            <dt><?php _e('Time allocated', 'wproject'); ?>:</dt>
                                <?php if($project_time_allocated) { ?>
                            <dd><?php echo $project_time_allocated; ?></dd>
                            <?php } else { ?>
                                <dd>-</dd>
                            <?php 
                        } ?>

                            <dt><?php _e('Time used', 'wproject'); ?>:</dt>
                            <dd><?php printf("%02d:%02d:%02d", $hours, $minutes, $seconds); ?></dd>

                        <?php } ?>

                        <dt><?php _e('Budget', 'wproject'); ?>:</dt>
                        <?php if($project_time_allocated && $project_hourly_rate) { ?>
                            <dd><?php if($wproject_settings['currency_symbol_position'] == 'l') { echo $wproject_settings['currency_symbol']; } ?><?php if($project_time_allocated && $project_hourly_rate) { echo number_format($budget); } ?><?php if($wproject_settings['currency_symbol_position'] == 'r') { echo $wproject_settings['currency_symbol']; } ?></dd>
                        <?php } else { ?>
                            <dd>-</dd>
                        <?php } ?>

                        <dt class="no-border"><?php _e('Job#', 'wproject'); ?>:</dt>
                        <?php if($project_job_number) { ?>
                            <dd class="no-border"><?php echo $project_job_number; ?></dd>
                        <?php } else { ?>
                            <dd class="no-border">-</dd>
                        <?php } ?>
                        
                    </dl>
                    <div class="actions">
                        <a href="<?php echo get_category_link( $term_id ) ?>" class="btn-light"><?php _e('View', 'wproject'); ?></a>
                        <a href="<?php echo home_url(); ?>/edit-project/?project-id=<?php echo $term_id; ?>" class="btn-light"><?php _e('Edit', 'wproject'); ?></a>
                        <a class="btn-light archive-this-project" id="<?php echo $term_id; ?>" data="<?php echo $cat->name; ?>"><?php _e('Archive', 'wproject'); ?></a>
                    </div>
                </span>
            </div>

        <?php } 
            $loops++;
            if ($loops >= 21) { /* Limit to 20 projects */
            break;
            }
        }

    ?>
    </div>
</div>

<form name="archive-project" id="archive-project" method="post">
    <input type="hidden" name="project_id" value="" />
    <input type="hidden" name="project_archive_location" value="dashboard" />
</form>

<script>
    $( document ).ready(function() {
        let project_count = $('.projects-chart .project-bar').length;
        let new_width = (100 / project_count);

        if(project_count === 15) {
            $('.projects-chart .project-bar').attr('style', 'width: 6.6%');
        } else if(project_count === 14) {
            $('.projects-chart .project-bar').attr('style', 'width: 7.14%');
        } else if(project_count === 13) {
            $('.projects-chart .project-bar').attr('style', 'width: 7.6%');
        } else if(project_count === 12) {
            $('.projects-chart .project-bar').attr('style', 'width: 8.3%');
        } else if(project_count === 11) {
            $('.projects-chart .project-bar').attr('style', 'width: calc(10% - <?php echo $bar_margin; ?>');
        } else if(project_count === 10) {
            $('.projects-chart .project-bar').attr('style', 'width: calc(10% - <?php echo $bar_margin; ?>');
        } else if(project_count === 9) {
            $('.projects-chart .project-bar').attr('style', 'width: calc(11% - <?php echo $bar_margin; ?>');
        } else if(project_count === 8) {
            $('.projects-chart .project-bar').attr('style', 'width: calc(12.5% - <?php echo $bar_margin; ?>');
        } else if(project_count === 7) {
            $('.projects-chart .project-bar').attr('style', 'width: calc(14% - <?php echo $bar_margin; ?>');
        } else if(project_count === 6) {
            $('.projects-chart .project-bar').attr('style', 'width: calc(15% - <?php echo $bar_margin; ?>');
        } else if(project_count === 5) {
            $('.projects-chart .project-bar').attr('style', 'width: calc(16% - <?php echo $bar_margin; ?>');
            //$('.projects-chart').addClass('space-evenly');
        } else if(project_count === 4) {
            $('.projects-chart .project-bar').attr('style', 'width: calc(20% - <?php echo $bar_margin; ?>');
            $('.projects-chart').addClass('space-evenly');
        } else if(project_count === 3) {
            $('.projects-chart .project-bar').attr('style', 'width: calc(25% - <?php echo $bar_margin; ?>');
            $('.projects-chart').addClass('space-evenly');
        } else if(project_count === 2) {
            $('.projects-chart .project-bar').attr('style', 'width: calc(33.3333% - <?php echo $bar_margin; ?>');
            $('.projects-chart').addClass('space-evenly');
        } else if(project_count === 1) {
            $('.projects-chart .project-bar').attr('style', 'width: calc(33.3333% - <?php echo $bar_margin; ?>');
            $('.projects-chart').addClass('space-evenly');
        } else if(project_count > 15) {
            $('.projects-chart .project-bar').attr('style', 'width: calc(5% - 5px');
        } else {
            $('.projects-chart .project-bar').attr('style', 'width: calc(' + new_width + '% - <?php echo $bar_margin; ?>');
        }

        <?php if(wp_is_mobile()) { ?>
            if(project_count > 4) {
                $('.projects-chart-container').css('width', '300%');
                $('.projects-chart').css('overflow', 'auto');
            }
        <?php } else { ?>
            if(project_count > 10) {
                $('.projects-chart-container').css('width', '143.2%');
                $('.projects-chart').css('overflow', 'auto');
            }
        <?php } ?>

        if(project_count > 10) {
            $('.projects-chart-container .project-bar').slice(project_count - 4).addClass('flip-details');
        }

        /* Archive a project */
        $('.archive-this-project').click(function() {

            var project_id = $(this).attr('id');
            var project_name = $(this).attr('data');
            var nav_project_count = $('.main-nav .project-count').attr('data');
            var new_nav_project_count = (nav_project_count - 1);
            $('#archive-project input').val(project_id);

            if (confirm('<?php _e('Really archive this project?', 'wproject'); ?>' + ' ' + project_name)) {
                setTimeout(function() { 
                    $('#archive-project').submit();
                }, 100);
                setTimeout(function() { 
                    $('#project-id-'+project_id).fadeOut();
                    $('.projects-list #project-'+project_id).fadeOut();
                    $('.projects-list #project-'+project_id).fadeOut();
                    $('.main-nav .project-count').attr('data', new_nav_project_count);
                    $('.main-nav .project-count').text(new_nav_project_count);
                }, 2000);
            }

        });

        <?php if(!wp_is_mobile()) { ?>
            $('.mobile-title').remove();
        <?php } ?>

        /* If project count is 0 */
        if(project_count == 0) {
            $('.projects-chart-container').remove();
            $('.projects-chart').css('height', 'auto');
            $('.projects-chart').html('<p class="no-margin"><i data-feather="info"></i><?php _e('You are not managing any projects yet.', 'wproject'); ?></p>');
        }

        //console.log(project_count);
        //console.log($('.projects-chart .project-bar'))
    });
</script>
<!--/ End Chart /--->
