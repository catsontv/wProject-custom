<?php if ( ! defined( 'ABSPATH' ) ) { exit; }

function theReport() { 
    
        $dark_mode		            = isset(user_details()['dark_mode']) ? user_details()['dark_mode'] : '';
    
        $report_id                  = $_GET['report-id'];
        $term                       = get_term( $report_id, 'project' );
        $term_meta                  = get_term_meta($report_id); 
        $description                = term_description($report_id);
        $slug                       = $term->slug;
        $project_full_description   = $term_meta['project_full_description'][0];
        $project_status             = $term_meta['project_status'][0];

        $pm_user                    = get_user_by('ID', $term_meta['project_manager'][0]);
        $pm_user_id                 = $pm_user->ID;
        $pm_name                    = $pm_user->first_name . ' ' . $pm_user->last_name;
        $pm_user_photo              = $pm_user->user_photo;
        $pm_user_info               = get_userdata($pm_user_id);
        $pm_email                   = $pm_user_info->user_email;
        $first_name                 = $pm_user->first_name;
        $last_name                  = $pm_user->last_name;

        $current_author             = get_current_user_id();

        $project_job_number         = $term_meta['project_job_number'][0];
        $project_start_date         = $term_meta['project_start_date'][0];
        $project_end_date           = $term_meta['project_end_date'][0];
        $project_status             = $term_meta['project_status'][0];
        $project_time_allocated     = $term_meta['project_time_allocated'][0];
        $project_hourly_rate        = $term_meta['project_hourly_rate'][0];

        if(preg_match("/[a-e]/i", $first_name[0])) {
            $colour = 'a-e';
        } else if(preg_match("/[f-j]/i", $first_name[0])) {
            $colour = 'f-j';
        } else if(preg_match("/[k-o]/i", $first_name[0])) {
            $colour = 'k-o';
        } else if(preg_match("/[p-t]/i", $first_name[0])) {
            $colour = 'p-t';
        } else if(preg_match("/[u-z]/i", $first_name[0])) {
            $colour = 'u-z';
        } else {
            $colour = '';
        }

        if(isset($term_meta['project_materials_list'][0])) {
            $project_materials_list = $term_meta['project_materials_list'][0];
        }
        if(isset($term_meta['project_materials_total'][0])) {
            $project_materials_total = $term_meta['project_materials_total'][0];
        }

        $date_format                = get_option('date_format'); 
        $today                      = strtotime('today');

        if($project_start_date && $project_end_date || $project_end_date) {
            $new_project_start_date = new DateTime($project_start_date);
            $the_project_start_date = $new_project_start_date->format($date_format);

            $new_project_end_date = new DateTime($project_end_date);
            $the_project_end_date = $new_project_end_date->format($date_format);
        }

        $due_date = strtotime($project_end_date);
        $overdue_class = '';
        if($today > $due_date && $project_status != 'complete') {
            $overdue_class = 'overdue';
        }

        $budget = 0;
        if($project_time_allocated && $project_hourly_rate) {
            $budget = $project_time_allocated * $project_hourly_rate;
        }

        $report_pro_settings        = wProject_report();
        $report_access              = $report_pro_settings['report_access'];
        $report_project_summary     = $report_pro_settings['report_project_summary'];
        $bar_chart_style            = $report_pro_settings['bar_chart_style'];
        $pie_chart_style            = $report_pro_settings['pie_chart_style'];
        $report_time_costs          = $report_pro_settings['report_time_costs'];
        $report_tasks               = $report_pro_settings['report_tasks'];
        $report_incomplete_tasks    = $report_pro_settings['report_incomplete_tasks'];
        $report_team                = $report_pro_settings['report_team'];
        $top_times                  = $report_pro_settings['top_times'];
        $top_time_logged            = $report_pro_settings['top_time_logged'];

        if($top_times) {
            $top_times = $top_times;
        } else {
            $top_times  = 10;
        }

        if($bar_chart_style == 'curve' || $bar_chart_style == 'line') {
            $line_or_bar = 'line';
        } else {
            $line_or_bar = 'bar';
        }

        /* Pie chart colours */
        $colour_materials           = '#c178b8';
        $colour_budget_remaining    = '#fbb546';
        $colour_cost_so_far         = '#4cc3d9';

        /* Bar chart colours */
        $colour_complete            = '#7bc8a4';
        $colour_incomplete          = '#f16745';
        $colour_inprogress          = '#ffc65d';
        $colour_onhold              = '#4cc3d9';
        $colour_todo                = '#c178b8';
        $colour_not_started         = '#90a9e6';
        $colour_blocked             = '#d5dcdc';

        $current_user               = wp_get_current_user();
        $user_role 				    = $current_user->roles[0];

        $wproject_settings          = wProject(); 
        $enable_time                = $wproject_settings['enable_time'];

        /* Count complete tasks */
        $args = array(
            'numberposts' 	=> -1,
            'post_status' 	=> 'publish',
            'post_type'     => 'task',
            'meta_key'          => 'task_status',
            'meta_value'        => 'complete',
            'tax_query' => array(
                array(
                    'taxonomy' => 'project',
                    'field'    => 'term_id',
                    'terms'    => $report_id
                ),
            ), 
        );
        $the_query   = new WP_Query( $args );
        $complete_tasks = $the_query->found_posts;
        
        /* Count in-progress tasks */
        $args = array(
            'numberposts' 	=> -1,
            'post_status' 	=> 'publish',
            'post_type'     => 'task',
            'meta_key'          => 'task_status',
            'meta_value'        => 'in-progress',
            'tax_query' => array(
                array(
                    'taxonomy' => 'project',
                    'field'    => 'term_id',
                    'terms'    => $report_id
                ),
            ), 
        );
        $the_query   = new WP_Query( $args );
        $in_progress_tasks = $the_query->found_posts;

        /* Count on-hold tasks */
        $args = array(
            'numberposts' 	=> -1,
            'post_status' 	=> 'publish',
            'post_type'     => 'task',
            'meta_key'          => 'task_status',
            'meta_value'        => 'on-hold',
            'tax_query' => array(
                array(
                    'taxonomy' => 'project',
                    'field'    => 'term_id',
                    'terms'    => $report_id
                ),
            ), 
        );
        $the_query   = new WP_Query( $args );
        $on_hold_tasks = $the_query->found_posts;

        /* Count to do tasks */
        $args = array(
            'numberposts' 	=> -1,
            'post_status' 	=> 'publish',
            'post_type'     => 'task',
            'meta_key'          => 'task_status',
            'meta_value'        => array('not-started', 'incomplete', 'on-hold'),
            'tax_query' => array(
                array(
                    'taxonomy' => 'project',
                    'field'    => 'term_id',
                    'terms'    => $report_id
                ),
            ), 
        );
        $the_query   = new WP_Query( $args );
        $to_do_tasks = $the_query->found_posts;

        /* Count not-started tasks */
        $args = array(
            'numberposts' 	=> -1,
            'post_status' 	=> 'publish',
            'post_type'     => 'task',
            'meta_key'          => 'task_status',
            'meta_value'        => 'not-started',
            'tax_query' => array(
                array(
                    'taxonomy' => 'project',
                    'field'    => 'term_id',
                    'terms'    => $report_id
                ),
            ), 
        );
        $the_query   = new WP_Query( $args );
        $not_started_tasks = $the_query->found_posts;

        /* Count blocked tasks */
        $args = array(
            'numberposts' 	=> -1,
            'post_status' 	=> 'publish',
            'post_type'     => 'task',
            'meta_key'      => 'task_relation',
            'meta_value'    => 'is_blocked_by',
            'tax_query' => array(
                array(
                    'taxonomy' => 'project',
                    'field'    => 'term_id',
                    'terms'    => $report_id
                ),
            ), 
        );
        $the_query   = new WP_Query( $args );
        $blocked_tasks = $the_query->found_posts;

        /* Count all tasks */
        $args = array(
            'numberposts' 	=> -1,
            'post_status' 	=> 'publish',
            'post_type'     => 'task',
            'tax_query' => array(
                array(
                    'taxonomy' => 'project',
                    'field'    => 'term_id',
                    'terms'    => $report_id
                ),
            ), 
        );
        $the_query   = new WP_Query( $args );
        $total_tasks = $the_query->found_posts;


        $incomplete_tasks   = $total_tasks - $complete_tasks;

        $round              = 1;

        if($total_tasks < 1) {
            echo '<script>$( document ).ready(function() { $(".export-csv a").attr("href", "javascript: void(0)"); $(".export-csv").css("filter", "grayscale(1)").css("pointer-events", "none").css("opacity", ".5"); $(".export-csv a span").text("' . __( 'No tasks to export', 'wproject-reports-pro' ) . '"); });</script>';
        }

        /* Project Progress (Avoid division by 0) */
        if($complete_tasks > 0 && $total_tasks > 0) {
            $the_project_progress = $complete_tasks / $total_tasks * 100;
        } else {
            $the_project_progress = 0;
        }

    ?>

    <!--/ Start Reports Pro /-->

    <div class="reports-pro <?php if($dark_mode == 'yes') { echo 'dark'; } ?>">

        <time><?php echo date(get_option('date_format')); ?></time>

        <!--/ Start Reports Pro Tabs /-->
        <div class="tabby tabby-report">

            <ul class="tab-nav">
                <li class="expand-all"><i data-feather="grid"></i></li>
                <?php if($report_project_summary) { ?>
                <li class="section-summary"><?php _e( 'Summary', 'wproject-reports-pro' ); ?></li>
                <?php } ?>
                <?php if($report_time_costs && $project_hourly_rate) { ?>
                <li class="section-time"><?php _e( 'Time & Costs', 'wproject-reports-pro' ); ?></li>
                <?php } ?>
                <?php if($report_tasks) { ?>
                <li class="section-complete"><?php _e( 'Tasks Chart', 'wproject-reports-pro' ); ?></li>
                <?php } ?>
                <?php if($report_team) { ?>
                <li class="section-team"><?php _e( 'Project Team', 'wproject-reports-pro' ); ?></li>
                <?php } ?>
            </ul>

        </div>
        <!--/ End Reports Pro Tabs /-->

        <!--/ Start Reports Pro Tab Sections /-->
        <main>

            <?php if($report_project_summary) { ?>
                <section class="section-summary">

                    <div class="block block-50">
                        <?php if($pm_user_photo) {
                            $avatar         = $pm_user_photo;
                            $avatar_id      = attachment_url_to_postid($avatar);
                            $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                            $avatar         = $small_avatar[0];
                            $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" />';
                        } else {
                            $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . substr($first_name,0,1) . substr($last_name,0,1) . '</div>';
                        } ?>
                        <h2><?php _e( 'Project Manager', 'wproject-reports-pro' ); ?></h2>
                        <div class="pm">
                            <?php echo $the_avatar; ?>
                            <p>
                                <strong><?php echo $pm_name; ?></strong>
                                <span><?php echo $pm_email; ?></span>
                            </p>
                        </div>
                    </div>
                    
                    <?php if($description) { ?>
                        <div class="block block-50">
                            <h2><?php _e( 'Brief Description', 'wproject-reports-pro' ); ?></h2>
                            <div class="brief-description">
                                <?php echo $description; ?>
                            </div>
                        </div>
                    <?php } ?>
                    
                    <?php if($project_full_description) { ?>
                        <div class="block block-50">
                            <h2><?php _e( 'Full Description', 'wproject-reports-pro' ); ?></h2>
                            <div class="desc-max-height">
                                <?php echo '<p>' . nl2br($project_full_description) . '</p>'; ?>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="block block-50">
                        <h2><?php _e( 'Details', 'wproject-reports-pro' ); ?></h2>
                        <dl>
                            <dt><?php _e( 'Progress', 'wproject-reports-pro' ); ?>:</dt>
                            <dd><i data-feather="percent"></i><?php echo round($the_project_progress, $round); ?>%</dd>
                            <?php if($complete_tasks > 0 && $total_tasks > 0) { ?>
                                <dt><?php _e( 'Tasks complete', 'wproject-reports-pro' ); ?>:</dt>
                                <dd><i data-feather="check-square"></i><?php echo $complete_tasks; ?> / <?php echo $total_tasks; ?></dd>
                            <?php } ?>
                            <dt><?php _e( 'Status', 'wproject-reports-pro' ); ?>:</dt>
                            <dd class="<?php echo $project_status; ?>"><i data-feather="activity"></i><?php if($project_status ) { echo ucfirst(str_replace('-', ' ', $project_status)); } ?></dd>
                            <?php if($project_job_number) { ?>
                            <dt><?php _e( 'Job #', 'wproject-reports-pro' ); ?>:</dt>
                            <dd><i data-feather="hash"></i><?php echo $project_job_number;  ?></dd>
                            <?php } ?>
                            <?php if($project_start_date || $project_end_date) { ?>
                            <dt><?php _e( 'Start & Due dates', 'wproject-reports-pro' ); ?>:</dt>
                            <dd class="<?php echo $overdue_class; ?>"><i data-feather="calendar"></i><?php if($project_start_date) { echo $the_project_start_date; } else { _e( 'No start date', 'wproject-reports-pro' ); }  ?> &#x276F; <?php if($project_end_date) { echo $the_project_end_date; } else { _e( 'No due date', 'wproject-reports-pro' ); }  ?></dd>
                            <?php } ?>
                            <?php if($project_time_allocated && $project_hourly_rate) { ?></dd>
                            <dt><?php _e( 'Budget', 'wproject-reports-pro' ); ?>:</dt>
                            <dd><i data-feather="dollar-sign"></i><?php if($wproject_settings['currency_symbol_position'] == 'l') { echo $wproject_settings['currency_symbol']; } ?><?php echo $budget; ?><?php if($wproject_settings['currency_symbol_position'] == 'r') { echo $wproject_settings['currency_symbol']; } ?></dd>
                            <?php } ?>
                            
                        </dl>
                    </div>

                </section>

                <script>
                    var block_count = $(".section-summary .block").length;
                    if(block_count == 3) {
                        $(".section-summary .block:nth-child(3)").addClass('block-100');
                    }
                </script>

            <?php } ?>

            <?php if($enable_time) { ?>
            <section class="section-time">
                <div class="block <?php if(!$project_time_allocated || !$project_hourly_rate) { echo 'block-100'; } else { echo 'block-50'; } ?>">
                    <h2><?php _e( 'Time', 'wproject-reports-pro' ); ?></h2>
                    <?php if(!$project_time_allocated || !$project_hourly_rate) { ?>
                        <p><?php _e( 'Time charts can not be displayed because this project does not have both a time budget and an hourly rate specified.', 'wproject-reports-pro' ); ?></p>
                    <?php } else { ?>
                    <dl>
                        <dt><?php _e( 'Time allocated', 'wproject-reports-pro' ); ?>:</dt>
                        <dd><i data-feather="watch"></i><?php echo $project_time_allocated; ?> <?php /* Abbreviation of 'hours': */ _e( 'hrs', 'wproject' ); ?></dd>
        
                        <?php if($enable_time) { ?>
                        <dt><?php _e( 'Time used', 'wproject-reports-pro' ); ?>:</dt>
                        <dd><i data-feather="watch"></i>
                            <?php 
                            $project_total	= get_term_meta($report_id, 'project_total_time', TRUE);
                            if($project_total) {
                                $hours              = floor($project_total / 3600);
                                $mins               = floor(($project_total / 60) % 60);
                                $seconds            = $project_total % 60;
                            } else {
                                $hours              = '00';
                                $mins               = '00';
                                $seconds            = '00';
                            }

                            if($project_total > 0 ) { ?>
                                <span>
                                    <?php printf("%02d:%02d:%02d", $hours, $mins, $seconds); ?>
                                </span>
                            <?php } ?>
                        </dd>
                        <?php } ?>

                        <dt><?php _e( 'Hourly rate', 'wproject-reports-pro' ); ?>:</dt>
                        <dd><i data-feather="dollar-sign"></i>
                            <?php if($wproject_settings['currency_symbol_position'] == 'l') { echo $wproject_settings['currency_symbol']; } ?><?php echo $project_hourly_rate; ?><?php if($wproject_settings['currency_symbol_position'] == 'r') { echo $wproject_settings['currency_symbol']; } ?>
                        </dd>
        
                        <dt><?php _e( 'Budget', 'wproject-reports-pro' ); ?>:</dt>
                        <dd><i data-feather="dollar-sign"></i>
                            <?php if($wproject_settings['currency_symbol_position'] == 'l') { echo $wproject_settings['currency_symbol']; } ?><?php echo $budget; ?><?php if($wproject_settings['currency_symbol_position'] == 'r') { echo $wproject_settings['currency_symbol']; } ?>
                        </dd>
                    </dl>

                    <?php if(isset($project_materials_list) && isset($project_materials_total)) { ?>
                        <h2><?php _e( 'Materials', 'wproject-reports-pro' ); ?></h2>
                        <dl>
                            <?php $all_rows = get_term_meta( (int)$report_id, 'project_materials_list', true);
                                if($all_rows) {
                                    if( count($all_rows ) > 0  ){
                                        sort($all_rows); /* Sort alphabetcially */
                                        foreach( $all_rows as $s_row ) {
                                        ?>
                                            <dt><?php echo $s_row['project_material_name'] ?>:</dt>
                                            <dd><?php if($wproject_settings['currency_symbol_position'] == 'l') { echo $wproject_settings['currency_symbol']; } ?><?php echo $s_row['project_material_cost']; ?><?php if($wproject_settings['currency_symbol_position'] == 'r') { echo $wproject_settings['currency_symbol']; } ?></dd>
                                        <?php 
                                        }
                                    }
                                }
                            ?>
                        </dl>
                    <?php } ?>

                    <p class="sub-total">
                        <span><?php _e( 'Materials Total', 'wproject-reports-pro' ); ?>:</span><i data-feather="dollar-sign"></i>
                        <?php if($wproject_settings['currency_symbol_position'] == 'l') { echo $wproject_settings['currency_symbol']; } ?><?php if($project_materials_total > 0 ) { echo $project_materials_total; } else { echo '0'; } ?><?php if($wproject_settings['currency_symbol_position'] == 'r') { echo $wproject_settings['currency_symbol']; } ?>
                    </p>

                    <?php
                        $hours_minutes          = $hours * 60;
                        $total_minutes          = $hours_minutes + $mins;

                        if(isset($project_materials_list) && isset($project_materials_total)) {
                            $project_total          = $total_minutes / 60 * $project_hourly_rate + $project_materials_total;
                        } else {
                            $project_total          = $total_minutes / 60 * $project_hourly_rate;
                        }

                        $budget_remaining       = $budget - $project_total;

                        if(isset($project_materials_list) && isset($project_materials_total) && $project_time_allocated && $project_hourly_rate) {
                            $materials_pc           = $project_materials_total / $budget * 100;
                        } else {
                            $materials_pc           = 0;
                        }


                        if($project_total > 0 && $project_time_allocated && $project_hourly_rate) {
                            $cost_so_far_pc         = $project_total / $budget * 100;
                            $budget_remaining_pc    = 100 - $materials_pc - $cost_so_far_pc;
                        } else {
                            $cost_so_far_pc         = 0;
                            $budget_remaining_pc    = 0;
                        }
                        

                    ?>
                        <p class="total">
                            <span><?php _e( 'Project Total', 'wproject-reports-pro' ); ?></span>
                            <span>
                                <?php if($wproject_settings['currency_symbol_position'] == 'l') { echo $wproject_settings['currency_symbol']; } ?><?php echo round($project_total); ?><?php if($wproject_settings['currency_symbol_position'] == 'r') { echo $wproject_settings['currency_symbol']; } ?>

                                <?php if($project_total > $budget) { ?>
                                    <em class="over">
                                        (<?php _e( 'over budget by', 'wproject-reports-pro' ); ?> 
                                        <?php if($wproject_settings['currency_symbol_position'] == 'l') { echo $wproject_settings['currency_symbol']; } ?><?php echo round($project_total) - $budget; ?><?php if($wproject_settings['currency_symbol_position'] == 'r') { echo $wproject_settings['currency_symbol']; } ?>)
                                    </em>
                                <?php } else { ?>
                                    <em class="under">
                                        (<?php _e( 'under budget by', 'wproject-reports-pro' ); ?> 
                                        <?php if($wproject_settings['currency_symbol_position'] == 'l') { echo $wproject_settings['currency_symbol']; } ?><?php echo $budget - round($project_total); ?><?php if($wproject_settings['currency_symbol_position'] == 'r') { echo $wproject_settings['currency_symbol']; } ?>)
                                    </em>
                                <?php } ?>
                            </span>
                        </p>
                </div>

                <div class="block block-50 pie-costs">
                    <div class="pie-position">
                        <?php if($enable_time) { ?>
                        <div class="chart-height">
                            <canvas id="timeChart"></canvas>
                        </div>
                        <div class="legend">
                            <span class="materials_pc"><strong><?php echo round($materials_pc, $round); ?><sup>%</sup></strong><?php _e( 'Materials', 'wproject-reports-pro' ); ?></span>
                            <span class="budget_remaining_pc"><strong><?php echo round($budget_remaining_pc, $round); ?><sup>%</sup></strong><?php _e( 'Budget remaining', 'wproject-reports-pro' ); ?></span>
                            <span class="cost_so_far_pc"><strong><?php echo round($cost_so_far_pc, $round); ?><sup>%</sup></strong><?php _e( 'Cost so far', 'wproject-reports-pro' ); ?></span>
                        </div>
                    </div>
                    
                    <script>
                        var ctx = document.getElementById("timeChart").getContext('2d');
                        var timeChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: ["<?php _e( 'Materials', 'wproject-reports-pro' ); ?>: <?php echo round($materials_pc, $round); ?>%", "<?php _e( 'Budget remaining', 'wproject-reports-pro' ); ?>: <?php echo round($budget_remaining_pc, $round); ?>%", "<?php _e( 'Cost so far', 'wproject-reports-pro' ); ?>: <?php echo round($cost_so_far_pc, $round); ?>%"],
                            datasets: [{
                            backgroundColor: [
                                '<?php echo $colour_materials; ?>',
                                '<?php echo $colour_budget_remaining; ?>',
                                '<?php echo $colour_cost_so_far; ?>'
                            ],
                            borderColor: [
                                '#fff',
                                '#fff',
                                '#fff'
                            ],
                            data: [<?php echo round($materials_pc, $round); ?>,<?php echo round($budget_remaining_pc, $round); ?>,<?php echo round($cost_so_far_pc, $round); ?>]
                            }]
                        },
                        // Chart config
                            options: {
                                <?php if($pie_chart_style == 'donut') { ?>
                                cutoutPercentage: 50,
                                <?php } ?>
                                maintainAspectRatio: false,
                                responsive: true,
                                elements: {
                                    arc: {
                                        borderWidth: 1
                                    }
                                },
                                legend: {
                                    display: false,
                                    position: 'bottom'
                                },
                                tooltips: {
                                    titleFontSize: 12,
                                    bodyFontSize: 12,
                                    bodyFontStyle: 'normal',
                                    bodyFontColor: '#FFFFFF',
                                    backgroundColor: '#3f5761',
                                    cornerRadius: 3,
                                    xPadding: 12,
                                    yPadding: 12,
                                    titleMarginBottom: 10,

                                    callbacks: { // Show percentage symbols
                                        label: function(tooltipItem, data) {
                                            var dataset = data.datasets[tooltipItem.datasetIndex];
                                            var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                                            return previousValue + currentValue;
                                        });
                                            var currentValue = dataset.data[tooltipItem.index];
                                            var precentage = Math.round(((currentValue/total) * 100));         
                                            return precentage + "%";
                                        }
                                    }
                                },
                                plugins: {
                                    datalabels: {
                                        color: "transparent",
                                        backgroundColor: "transparent"
                                    }
                                }
                            }
                        });
                    </script>

                </div>

                <?php if($top_time_logged) { ?>
                <div class="block block-100">
                    <h2><?php _e( 'Most Time Logged', 'wproject-reports-pro' ); ?></h2>

                    <ul class="top-tasks">
                        <li class="top-task-header">
                            <span><?php _e( 'Contributer', 'wproject-reports-pro' ); ?></span>
                            <span><?php _e( 'Task', 'wproject-reports-pro' ); ?></span>
                            <span><?php _e( 'Time', 'wproject-reports-pro' ); ?></span>
                        </li>
                        <?php 
                        global $wpdb;
                        $tablename = $wpdb->prefix.'time';
                        $query = "
                            SELECT * 
                            FROM $tablename 
                            ORDER BY `time_log` DESC
                        ";
                        $result = $wpdb->get_results($query);
                        foreach ($result as $i => $data) {

                        if ($i > $top_times) break;

                            $task_id        = $data->task_id;
                            $id             = $data->id;
                            $time           = $data->time_log;
                            $project_id     = $data->project_id;
                            $time_user      = get_userdata( $data->user_id );

                            /* User */
                            $first_name     = $time_user->first_name;
                            $last_name      = $time_user->last_name;
                            $user_photo     = $time_user->user_photo;
                            $user_role      = $time_user->roles[0];

                            $hours          = floor($time / 3600);
                            $minutes        = floor(($time / 60) % 60);
                            $seconds        = $time % 60;

                            $project_total	= get_term_meta($report_id, 'project_total_time', TRUE);

                            //TODO: sometimes is more than 100%
                            if($project_total > 0 && $time > 0 ) {
                                $progress_bar   = $time / $project_total * 100;
                            } else {
                                $progress_bar   = 0;
                            }
                            if($progress_bar > 100) {
                                $progress_bar = '100';
                                $progress_text = __( 'More than project time logged', 'wproject-reports-pro' );
                            } else {
                                $progress_bar = $progress_bar;
                                $progress_text = sprintf( __( 'Makes up %1$s%% of the project time', 'wproject-reports-pro' ), round($progress_bar, 1) );
                            }

                            if($user_photo) {
                                $avatar         = $user_photo;
                                $avatar_id      = attachment_url_to_postid($avatar);
                                $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                                $avatar         = $small_avatar[0];
                                $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" />';
                            } else {
                                $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . substr($first_name,0,1) . substr($last_name,0,1) . '</div>';
                            }
                            if($project_id == $report_id) {
                        ?>
                            <li>
                                <span>
                                    <?php echo $the_avatar; ?>
                                    <?php echo $first_name; ?> <?php echo $last_name; ?>
                                </span>
                                <span>
                                    <a href="<?php echo get_permalink($task_id); ?>"><?php echo get_the_title($task_id); ?></a>
                                </span>
                                <span><?php printf("%02d:%02d:%02d", $hours, $minutes, $seconds); ?></span>
                                <?php if($project_total > 0) { ?>
                                <em class="progress-bar progress-bar-0<?php echo $i++; ?>" style="width: <?php echo $progress_bar;?>%">
                                    <em class="pop"><?php echo $progress_text; ?></em>
                                </em>
                                <?php } ?>
                            </li>
                        <?php } 
                        } ?>
                    </ul>
                </div>
                    <?php } 
                    } 
                } ?>

            </section>
            <?php } ?>

            <?php if($report_tasks) { ?>
                <section class="section-complete">
                    <div class="block block-100">
                        <canvas id="tasksCompared"></canvas>
                        <img id="tasksCompared" class="chart-image" />
                        <script>
                            var ctx = document.getElementById("tasksCompared");
                            //debugger;
                            var tasksCompared = new Chart(ctx, {
                                type: '<?php echo $line_or_bar; ?>',
                                data: {
                                    labels: ["<?php _e( 'Complete', 'wproject-reports-pro' ); ?> (<?php echo $complete_tasks; ?>)", "<?php _e( 'Incomplete', 'wproject-reports-pro' ); ?> (<?php echo $incomplete_tasks; ?>)", "<?php _e( 'In progress', 'wproject-reports-pro' ); ?> (<?php echo $in_progress_tasks; ?>)", "<?php _e( 'On hold', 'wproject-reports-pro' ); ?> (<?php echo $on_hold_tasks; ?>)", "<?php _e( 'To do', 'wproject-reports-pro' ); ?> (<?php echo $to_do_tasks; ?>)", "<?php _e( 'Not started', 'wproject-reports-pro' ); ?> (<?php echo $to_do_tasks; ?>)"<?php if($blocked_tasks > 0) { ?>, "<?php _e( 'Blocked', 'wproject-reports-pro' ); ?> (<?php echo $blocked_tasks; ?>)"<?php } ?>],
                                    datasets: [{
                                        label: '<?php _e( 'Tasks', 'wproject-reports-pro' ); ?>',
                                        data: [<?php echo $complete_tasks; ?>,<?php echo $incomplete_tasks; ?>,<?php echo $in_progress_tasks; ?>,<?php echo $on_hold_tasks; ?>,<?php echo $to_do_tasks; ?>,<?php echo $not_started_tasks; ?><?php if($blocked_tasks > 0) { echo ',' , $blocked_tasks; } ?>],
                                        <?php if($line_or_bar == 'bar') { ?>
                                        backgroundColor: ["<?php echo $colour_complete; ?>", "<?php echo $colour_incomplete; ?>", "<?php echo $colour_inprogress; ?>", "<?php echo $colour_onhold; ?>", "<?php echo $colour_todo; ?>", "<?php echo $colour_not_started; ?>" <?php if($blocked_tasks > 0) { echo ',"' . $colour_blocked . '"'; } ?>],
                                        <?php } else { ?>
                                        backgroundColor: 'rgba(76,195,217,0.1)',
                                        borderColor: 'rgb(76,195,217)',
                                        <?php } ?>
                                        pointBackgroundColor: 'rgb(76,195,217)',
                                        pointBorderWidth: 1,
                                        borderWidth: 1,
                                        <?php if($bar_chart_style == 'curve') { ?>
                                        lineTension: 0.4,
                                        <?php } else { ?>
                                            lineTension: 0,
                                        <?php } ?>
                                        pointStyle: 'circle',
                                    }]
                                },
                                options: {
                                    "hover": {
                                    "animationDuration": 0
                                },
                                animation: {
                                    "duration": 1,
                                        "onComplete": function () {
                                            var chartInstance = this.chart,
                                                ctx = chartInstance.ctx;
                                            
                                            ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
                                            ctx.textAlign = 'center';
                                            ctx.textBaseline = 'bottom';

                                            this.data.datasets.forEach(function (dataset, i) {
                                                var meta = chartInstance.controller.getDatasetMeta(i);
                                                meta.data.forEach(function (bar, index) {
                                                    var data = dataset.data[index];                            
                                                    ctx.fillText(data, bar._model.x, bar._model.y - 5);
                                                });
                                            });
                                        }
                                    },
                                    legend: {
                                        display: false,
                                        labels: {
                                            display: false
                                        }
                                    },
                                    tooltips: {
                                        enabled: false
                                    },
                                    scales: {
                                        xAxes: [{
                                            display: true,
                                            ticks: {
                                                fontColor: '#3f5761'
                                            },
                                            gridLines: {
                                                display: false,
                                                color: 'rgba(0, 0, 0, 0.05)',
                                                zeroLineColor: 'transparent',
                                            }
                                        }],
                                        yAxes: [{
                                            display: true,
                                            scaleShowLabels: false,
                                            ticks: {
                                                fontColor: '#5b606c',
                                                beginAtZero: true
                                            },
                                            gridLines: {
                                                display: true,
                                                color: 'rgba(0, 0, 0, 0.05)',
                                                zeroLineColor: 'transparent'
                                            }
                                        }]
                                    },
                                    plugins: {
                                        datalabels: {
                                            display: false,
                                        },
                                    }
                                }
                            });
                            </script>
                    </div>
                </section>
            <?php } ?>

            <?php if($report_team) { ?>
                <section class="section-team">
                    <div class="block block-100">
                        <h2><?php _e( 'Project Team', 'wproject-reports-pro' ); ?></h2>
                        <ul class="team">
                        <?php
                        $category           = get_queried_object();
                        $taxonomy_name      = 'project';
                        $current_category   = $slug;
                        $author_array       = array();
                        $args = array(
                            'posts_per_page'    => -1,
                            'post_type'         => 'task',
                            'orderby'           => 'author',
                            'order'             => 'ASC',
                            'tax_query'         => array(
                            array(
                                'taxonomy'  => 'project',
                                'field'     => 'slug',
                                'terms'     => $current_category
                                ),
                            ),
                        );
                        $cat_posts = get_posts($args);
                        foreach ($cat_posts as $cat_post) :
                            if (!in_array($cat_post->post_author,$author_array)) {
                                $author_array[] = $cat_post->post_author;
                            }
                        endforeach;
                        
                        $team_count = 0;
                        $count = 0;
                        foreach ($author_array as $author) :
                            $id             = isset(get_userdata($author)->ID) ? get_userdata($author)->ID : '';
                            $first_name     = isset(get_userdata($author)->first_name) ? get_userdata($author)->first_name : '';
                            $last_name      = isset(get_userdata($author)->last_name) ? get_userdata($author)->last_name : '';
                            $user_photo     = isset(get_userdata($author)->user_photo) ? get_userdata($author)->user_photo : '';
                            $user_title     = isset(get_userdata($author)->title) ? get_userdata($author)->title : '';
                    
                            if($user_photo) {
                                $avatar         = $user_photo;
                                $avatar_id      = attachment_url_to_postid($avatar);
                                $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                                $avatar         = $small_avatar[0];
                                $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" />';
                            } else {
                                if(isset($_GET['print'])) {
                                    $the_avatar = '<img src="' . get_template_directory_uri() . '/images/default-user.png' . '"/>';
                                } else {
                                    $the_avatar = '<div class="letter-avatar avatar ' . $colour . '">' . substr($first_name,0,1) . substr($last_name,0,1) . '</div>';
                                }
                            }
                            if(!empty($id)) { // Solves issue of non-existant user being shown for unknown reason ?>
                                <li>
                                    <a href="<?php echo get_the_permalink(109); ?>?id=<?php echo $id; ?>">
                                        <?php echo $the_avatar; ?>
                                        <span>
                                            <strong><?php echo $first_name; ?> <?php echo $last_name; ?></strong>
                                            <?php if($user_title) { ?>
                                                <em><?php echo $user_title; ?></em>
                                            <?php } ?>
                                        </span>
                                    </a>
                                </li>
                            <?php
                        }
                        $team_count = $count++;
                        endforeach;
                        ?>
                        </ul>
                        <?php 
                        ?>
                    </div>
                </section>
            <?php } ?>

        </main>
        <!--/ End Reports Pro Tab Sections /-->

    </div>
    <!--/ End Reports Pro /-->

    <script>
        $('.reports-pro main section:first-child').addClass('active');
        $('.reports-pro .tabby-report .tab-nav li:first-child').addClass('active');
        $('.reports-pro .tabby-report .tab-nav li').click(function() {
            var section = $(this).attr('class');
            $('.reports-pro .tabby-report .tab-nav li').removeClass('active');
            $(this).addClass('active'); 
            $('.reports-pro main section').removeClass('active');
            $('.reports-pro main .'+section).addClass('active');
        });

        $('main section').addClass('active');
        $('main section').css('margin-bottom', '25px');

        $('.expand-all').click(function() {
            $('main section').addClass('active');
        });

        var tab_count = $('.tab-nav li').length;
        if(tab_count == 1) {
            $('.tab-nav').empty();
            $('.tab-nav').after('<p class="inclusion-notice"><?php printf( __( 'You need to enable some inclusions in the <a href="%1$s">settings</a>.', 'wproject-reports-pro' ), admin_url() . '/admin.php?page=wproject-settings&section=reports-pro' );?></p>');
            $('.inclusion-notice a').css('text-decoration', 'underline');
            $('.inclusion-notice').css('margin', '25px 0');
        }
    </script>


<?php }

/* Project Report */
function report_access_logic() { 

    /* If the Report page */
    if(is_page(107)) { 

        $report                     = isset($_GET['report-id']);
        $report_pro_settings        = wProject_report();
        $report_access              = $report_pro_settings['report_access'];
        $current_user               = wp_get_current_user();
        $user_role 				    = $current_user->roles[0];

        if($report_access == 'limited' && $user_role == 'administrator' || $report_access == 'limited' && $user_role == 'project_manager') {
    
            theReport();
    
        } else if($report_access == 'everyone') {

            theReport();

        } else {
            echo '<p>';
            _e( 'Access to reports is restricted to Project Managers and Administrators.', 'wproject-reports-pro' );
            echo '</p>';
        }

    } /* End if the Report page */

}
add_action('page_start', 'report_access_logic', 20);

/* Side nav items */
function reports_pro_nav() {

    $report_pro_settings        = wProject_report();
    $dark_mode		            = isset(user_details()['dark_mode']) ? user_details()['dark_mode'] : '';
    
    if(is_page(107)) {
        $wproject_settings  = wProject();
        $project            = get_term( $_GET['report-id'], 'project' );
        $edit_project_url   = get_the_permalink(101) . '?project-id=' . $_GET['report-id'];
        $project_list_style = $wproject_settings['project_list_style'];

?>

    <li><a href="<?php echo home_url(); ?>/project/<?php echo $project->slug; ?>"><i data-feather="folder"></i><?php _e( 'Go to project', 'wproject-reports-pro' ); ?></a></li>
    <li><a href="<?php echo $edit_project_url; ?>"><i data-feather="edit-3"></i><?php _e( 'Edit project', 'wproject-reports-pro' ); ?></a></li>
    <li class="export-csv"><a href="<?php echo plugin_dir_url( __FILE__ ) . 'inc/export-csv-tasks.php?report-id=' . $_GET['report-id']; ?>"><i data-feather="file"></i><span><?php _e( 'Export Tasks to CSV', 'wproject-reports-pro' ); ?></span></a></li>
    
    <script>
        //var projects_list = $('.projects-list').html();
        $('.projects-list').clone().appendTo($('.right'));

        $('header .icons .print').attr('onclick', 'return printPopUp("<?php echo home_url();?>/report/?report-id=<?php echo $_GET['report-id']; ?>&print=yes")');

        <?php if($project_list_style == 'dropdown') { ?>
            $('.right .projects-list').addClass('dropdown');
            $('.right .projects-list').before('<div class="dropdown-start"><?php _e( 'More Reports', 'wproject-reports-pro' ); ?><i data-feather="chevron-down"></i></div>');
            $('.right .projects-list').css('display', 'none');

            $('.right .dropdown-start').click(function() { 
				$(this).toggleClass('spin');
				$('.right .projects-list.dropdown').slideToggle();
			});

        <?php } else { ?>
            $('.right .projects-list').before('<p class="title"><?php _e( 'More Reports', 'wproject-reports-pro' ); ?></p>');
            $('.right .projects-list').removeClass('dropdown');
            $('.right .projects-list').addClass('list');
        <?php } ?>

        $('.right .projects-list li');
        <?php if($dark_mode == 'yes') { ?>
        $('.right .projects-list').addClass('dark');
        <?php } ?>
        //var project_id = $('.right .projects-list li').closest('li').attr('data');
        
        var listItems = $('.right .projects-list').find('li').each(function() {
            var project_id = $(this).attr('data');
            var report_id = (new URL(location.href)).searchParams.get('report-id');
            if(report_id == project_id) {
                $(this).addClass('current');
            }
            $('.right .projects-list #project-'+project_id+' a').attr('href', '<?php echo home_url(); ?>/report/?report-id='+project_id);
        });
    </script>
<?php }
}
add_action('side_nav', 'reports_pro_nav');


/* Help topics */
function reports_pro_help() { 
    
    if(is_page(107)) { /* Only show on Reports page */ ?>

    <h4><?php _e('Summary', 'wproject'); ?></h4>
    <p><?php _e('A summary of the current project status and the project manager responsible. If the project is late, the due date will be highlighted in red.', 'wproject'); ?></p>

    <h4><?php _e('Time & Costs', 'wproject'); ?></h4>
    <p><?php _e('A breakdown of the current running project costs, including calculated time spent and materials.', 'wproject'); ?></p>

    <h4><?php _e('Tasks', 'wproject'); ?></h4>
    <p><?php _e('A chart indicating the current status of all tasks and any blocked tasks (if any).', 'wproject'); ?></p>

    <h4><?php _e('Project team', 'wproject'); ?></h4>
    <p><?php _e('The team members involved in the project.', 'wproject'); ?></p>
    
<?php }
}
add_action('help_start', 'reports_pro_help');