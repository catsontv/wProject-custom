<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
if ( ! defined( 'ABSPATH' ) ) exit;

$report_id      = $_GET['report-id'];

if(!$report_id) {
    exit;
}

$term           = get_term( $report_id, 'project' );
$project_name   = $term->name;
$term_meta      = get_term_meta($report_id); 
$description    = term_description($report_id);
$slug           = $term->slug;
$date_format    = get_option('date_format'); 
$today          = date('F j Y');

$all_tasks = array(
    'post_type'         => 'task',
    'post_status'		=> 'publish',
    'category' 			=> $report_id,
    'orderby' 			=> 'name',
    'order' 			=> 'ASC',
    'posts_per_page'    => -1,
    'meta_key'          => 'task_status',
    'meta_value'        => array('complete', 'in-progress', 'not-started', 'on-hold', 'incomplete'),
    'tax_query' => array(
        array(
            'taxonomy' => 'project',
            'field'    => 'slug',
            'terms'    => array( $slug ),
            'operator' => 'IN'
        ),
    ),
);
$query = new WP_Query($all_tasks);

while ($query->have_posts()) : $query->the_post();

    $task_id                = get_the_id();
    $author_id              = get_post_field ('post_author', $task_id);
    $user_ID                = get_the_author_meta( 'ID', $author_id );
    $first_name             = get_the_author_meta( 'first_name', $author_id );
    $last_name              = get_the_author_meta( 'last_name', $author_id );
    $task_job_number        = get_post_meta($task_id, 'task_job_number', TRUE);
    $task_start_date        = get_post_meta($task_id, 'task_start_date', TRUE);
    $task_end_date          = get_post_meta($task_id, 'task_end_date', TRUE);
    $task_total_time        = get_post_meta($task_id, 'task_total_time', TRUE);
    $task_description       = get_post_meta($task_id, 'task_description', TRUE);
    $task_status            = get_post_meta($task_id, 'task_status', TRUE);
    $task_priority          = get_post_meta($task_id, 'task_priority', TRUE);
    $task_milestone         = get_post_meta($task_id, 'task_milestone', TRUE);
    $task_private           = get_post_meta($task_id, 'task_private', TRUE);
    $task_total_time        = get_post_meta($task_id, 'task_total_time', TRUE);
    $task_pc_complete       = get_post_meta($task_id, 'task_pc_complete', TRUE);
    $task_relation          = get_post_meta($task_id, 'task_relation', TRUE);
    $task_related           = get_post_meta($task_id, 'task_related', TRUE);
    $task_explanation       = get_post_meta($task_id, 'task_explanation', TRUE);
    $user_photo             = get_the_author_meta( 'user_photo', $author_id );
    $post_status            = get_post_status ($task_id);

    if($task_relation) {

        if($task_relation == 'has_issues_with') {
            $relation_label = __('Has issues with', 'wproject-reports-pro');
        } else if($task_relation == 'is_blocked_by') {
            $relation_label = __('Is blocked by', 'wproject-reports-pro');
        } else if($task_relation == 'is_similar_to') {
            $relation_label = __('Is similar to', 'wproject-reports-pro');
        } else if($task_relation == 'relates_to') {
            $relation_label = __('Relates to', 'wproject-reports-pro');
        }

        $the_task_relation = $relation_label . ': ' . get_the_title($task_id);
    } else {
        $the_task_relation = '';
    }

    if($task_start_date || $task_end_date) {
        $new_task_start_date    = new DateTime($task_start_date);
        $the_task_start_date    = $new_task_start_date->format($date_format);

        $new_task_end_date      = new DateTime($task_end_date);
        $the_task_end_date      = $new_task_end_date->format($date_format);
    } else {
        $the_task_start_date    = '';
        $the_task_end_date      = '';
    }

    if($task_pc_complete) {
        $the_task_pc_complete = $task_pc_complete .'%';
    } else {
        $the_task_pc_complete = '';
    }

    $the_task_total_time        = gmdate('H:i:s', (int)$task_total_time);

    if($task_priority == 'low') {
        $the_task_priority = __( 'Low', 'wproject-reports-pro' );
    } else if($task_priority == 'normal') {
        $the_task_priority = __( 'Normal', 'wproject-reports-pro' );
    } else if($task_priority == 'high') {
        $the_task_priority = __( 'High', 'wproject-reports-pro' );
    } else if($task_priority == 'urgent') {
        $the_task_priority = __( 'Urgent', 'wproject-reports-pro' );
    } else if($task_priority == '') {
        $the_task_priority = __( 'Normal', 'wproject-reports-pro' );
    } 

    if($task_milestone == 'yes') {
        $the_task_milestone = __( 'Yes', 'wproject-reports-pro' );
    } else if($task_milestone == 'no') {
        $the_task_milestone = __( 'No', 'wproject-reports-pro' );
    } else if($task_milestone == '') {
        $the_task_milestone = __( 'No', 'wproject-reports-pro' );
    }

    if($task_private == 'yes') {
        $the_task_private = __( 'Yes', 'wproject-reports-pro' );
    } else if($task_private == 'no') {
        $the_task_private = __( 'No', 'wproject-reports-pro' );
    } else if($task_private == '') {
        $the_task_private = __( 'No', 'wproject-reports-pro' );
    } 

    if($task_status == 'complete') {
        $the_task_status = __( 'Complete', 'wproject-reports-pro' );
    } else if($task_status == 'in-progress') {
        $the_task_status = __( 'In progress', 'wproject-reports-pro' );
    } else if($task_status == 'not-started') {
        $the_task_status = __( 'Not started', 'wproject-reports-pro' );
    } else if($task_status == 'on-hold') {
        $the_task_status = __( 'On hold', 'wproject-reports-pro' );
    } else if($task_status == 'incomplete') {
        $the_task_status = __( 'Incomplete', 'wproject-reports-pro' );
    }

    setup_postdata($post);

    $task_items[]=array(
        $task_id,
        preg_replace('/[^a-zA-Z0-9\s]/', '', strip_tags(html_entity_decode(get_the_title($task_id)))),
        $task_job_number,
        $first_name . ' ' . $last_name,
        strip_tags(html_entity_decode($task_description)),
        $the_task_status,
        $the_task_start_date,
        $the_task_end_date,
        $the_task_total_time,
        $the_task_priority,
        $the_task_pc_complete,
        $the_task_private,
        $the_task_milestone,
        $the_task_relation
    );

endwhile;

$csv_fields = array();
$csv_fields[] = __( 'Task ID', 'wproject-reports-pro' );
$csv_fields[] = __( 'Task name', 'wproject-reports-pro' );
$csv_fields[] = __( 'Job #', 'wproject-reports-pro' );
$csv_fields[] = __( 'Owner', 'wproject-reports-pro' );
$csv_fields[] = __( 'Description', 'wproject-reports-pro' );
$csv_fields[] = __( 'Status', 'wproject-reports-pro' );
$csv_fields[] = __( 'Start date', 'wproject-reports-pro' );
$csv_fields[] = __( 'Due date', 'wproject-reports-pro' );
$csv_fields[] = __( 'Time', 'wproject-reports-pro' );
$csv_fields[] = __( 'Priority', 'wproject-reports-pro' );
$csv_fields[] = __( 'Progress', 'wproject-reports-pro' );
$csv_fields[] = __( 'Privacy', 'wproject-reports-pro' );
$csv_fields[] = __( 'Milestone', 'wproject-reports-pro' );
$csv_fields[] = __( 'Relation', 'wproject-reports-pro' );


$output_filename = __( 'Tasks for' . ' ' . $project_name . ' - ' . $today, 'wproject-reports-pro' ) . '.csv';
$output_handle = @fopen( 'php://output', 'w' );
    
header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
header( 'Content-Description: File Transfer' );
header( 'Content-type: text/csv' );
header( 'Content-Disposition: attachment; filename=' . $output_filename );
header( 'Expires: 0' );
header( 'Pragma: public' );	

/* Header row */
fputcsv( $output_handle, $csv_fields );

/* Parse results to csv format */
foreach ($task_items as $the_result) {
    $the_array = (array) $the_result; 
    fputcsv( $output_handle, $the_array );
}
    
/* Close output file stream */
fclose( $output_handle ); 