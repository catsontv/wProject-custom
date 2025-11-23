<?php if ( ! defined( 'ABSPATH' ) ) { exit; }
/* Metaboxes for Task custom post type */
class task_details_meta_box {

	var $plugin_dir;
	var $plugin_url;

	function  __construct() {

		add_action( 'add_meta_boxes', array( $this, 'task_meta_box' ) );
		add_action( 'save_post', array($this, 'save_data') );
	}

	// Add the meta boxes to post types
	function task_meta_box(){
		
		$post_types = array ( 'task');
		
		foreach( $post_types as $post_type ) {
		
			add_meta_box(
			'task_details', /* metabox ID (will also be the HTML attribute) */
			'Additional Task Details',
			array( $this, 'meta_box_content' ),
			$post_type,
			'normal', /* position of the screen (normal, side, advanced) */
			'high'    /* priority over other metaboxes on this page (default, low, high, core) */
		  );
	  }
	}

	function meta_box_content() { ?>

		<!--/ Start New Task Admin -->
		<div class="new-task-admin">

			<div class="task-message"></div>
			<p class="task-button"></p>

			<script>
				jQuery( document ).ready(function() {
					if (jQuery('body').hasClass('post-new-php')) {
						jQuery('.task-message').html('<?php _e('Tasks must be created on the front-end.', 'wproject'); ?>');
						jQuery('.task-button').html('<a href="<?php echo home_url(); ?>/new-task/" class="button task-link"><?php _e('Create task', 'wproject'); ?></a>');
						jQuery('#side-sortables, #commentstatusdiv, #slugdiv, #authordiv, #advanced-sortables, #post-body-content').remove();
					} else {
						jQuery('.task-message').html('<?php _e('Full task editing must be done on the front-end.', 'wproject'); ?>');
						jQuery('p.task-button').html('<a href="<?php echo home_url(); ?>/edit-task/?task-id=<?php echo get_the_id(); ?>" class="button task-link"><?php _e('Edit This Task', 'wproject'); ?></a>');
						jQuery('#tagsdiv-project').remove();
					}
				});
			</script>
		</div>
		<!--/ End New Task Admin -->

		<?php
	}

	function save_data($post_id){
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		// Check permissions
		if ( isset($_POST['post_type']) == 'task' ){
			if ( !current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
		}

		// Update task fields
		if(isset($_POST['task_priority'])) {
			$data = sanitize_text_field($_POST['task_priority']);
			update_post_meta($post_id, 'task_priority', $data, get_post_meta($post_id, 'task_priority', TRUE));
		}
		if(isset($_POST['task_job_number'])) {
			$data = sanitize_text_field($_POST['task_job_number']);
			update_post_meta($post_id, 'task_job_number', $data, get_post_meta($post_id, 'task_job_number', TRUE));
		}
		if(isset($_POST['task_start_date'])) {
			$data = sanitize_text_field($_POST['task_start_date']);
			update_post_meta($post_id, 'task_start_date', $data, get_post_meta($post_id, 'task_start_date', TRUE));
		}
		if(isset($_POST['task_end_date'])) {
			$data = sanitize_text_field($_POST['task_end_date']);
			update_post_meta($post_id, 'task_end_date', $data, get_post_meta($post_id, 'task_end_date', TRUE));
		}
		if(isset($_POST['task_status'])) {
			$data = sanitize_text_field($_POST['task_status']);
			update_post_meta($post_id, 'task_status', $data, get_post_meta($post_id, 'task_status', TRUE));
		}
		if(isset($_POST['task_milestone'])) {
			$data = sanitize_text_field($_POST['task_milestone']);
			update_post_meta($post_id, 'task_milestone', $data, get_post_meta($post_id, 'task_milestone', TRUE));
		}
		if(isset($_POST['task_private'])) {
			$data = sanitize_text_field($_POST['task_private']);
			update_post_meta($post_id, 'task_private', $data, get_post_meta($post_id, 'task_private', TRUE));
		}
		if(isset($_POST['task_pc_complete'])) {
			$data = sanitize_text_field($_POST['task_pc_complete']);
			update_post_meta($post_id, 'task_pc_complete', $data, get_post_meta($post_id, 'task_pc_complete', TRUE));
		}
		if(isset($_POST['task_time'])) {
			$data = sanitize_text_field($_POST['task_time']);
			update_post_meta($post_id, 'task_time', $data, get_post_meta($post_id, 'task_time', TRUE));
		}
		if(isset($_POST['task_time'])) {
			$data = sanitize_text_field($_POST['task_time']);
			update_post_meta($post_id, 'task_time', $data, get_post_meta($post_id, 'task_time', TRUE));
		}
		if(isset($_POST['task_description'])) {
			$data = $_POST['task_description'];
			update_post_meta($post_id, 'task_description', implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $data ) ) ));
		}
		
		if(isset($_POST['task_relation'])) {
			$data = sanitize_text_field($_POST['task_relation']);
			update_post_meta($post_id, 'task_relation', $data, get_post_meta($post_id, 'task_relation', TRUE));
		}
		if(isset($_POST['task_related'])) {
			$data = sanitize_text_field($_POST['task_related']);
			update_post_meta($post_id, 'task_related', $data, get_post_meta($post_id, 'task_related', TRUE));
		}
		if(isset($_POST['task_explanation'])) {
			$data = sanitize_text_field($_POST['task_explanation']);
			update_post_meta($post_id, 'task_explanation', $data, get_post_meta($post_id, 'task_explanation', TRUE));
		}
		if(isset($_POST['task_takeover_request'])) {
			$data = sanitize_text_field($_POST['task_takeover_request']);
			update_post_meta($post_id, 'task_takeover_request', $data, get_post_meta($post_id, 'task_takeover_request', TRUE));
		}
        if(isset($_POST['context_label'])) {
			$data = sanitize_text_field($_POST['context_label']);
			update_post_meta($post_id, 'context_label', $data, get_post_meta($post_id, 'context_label', TRUE));
		}

		if(isset($_POST['subtask_name'])) {
					
			$items_num = count( $_POST['subtask_name'] );
			for($i=0; $i< $items_num; $i++) {
				$all_subtasks[] = array(
                    'subtask_name' => sanitize_text_field($_POST["subtask_name"][$i] ),
                    'subtask_description' => wp_kses_post(sanitize_textarea_field($_POST["subtask_description"][$i] )),
                    'subtask_status' => sanitize_text_field($_POST["subtask_status"][$i] )
                );
			}
			$subtask_list = $all_subtasks;

			update_post_meta( $post_id, 'subtask_list', $subtask_list); 

		}

		if ( isset($_POST['post_type']) == 'task' ){
			return $data;
		}
	}
}
$task_details_meta_box = new task_details_meta_box;