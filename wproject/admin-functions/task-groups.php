<?php if (!defined('ABSPATH')) exit; 

/* Custom post type for Task Groups */
$task_group_exists = post_type_exists( 'task_group' );
if(!$task_group_exists) {
    function create_posttype() {

    $plugin_directory = plugins_url('images/', __FILE__ );
    register_post_type( 'task_group',

        array(
            'labels' => array(
                'singular_name'     => __( 'Group Item', 'wproject'),
                'name' 				=> __( 'Task Groups', 'wproject' ),
                'add_new'           => __( 'Add Group Item', 'wproject'),
                'add_new_item'      => __( 'Add Group or Group Item', 'wproject'),
                'edit_item'         => __( 'Edit Group Item', 'wproject'),
                'new_item'          => __( 'New Group Item', 'wproject'),
                'search_items'      => __( 'Search Task Group Items', 'wproject'),
                'not_found'  		=> __( 'No Task Group Items found', 'wproject'),
                'not_found_in_trash'=> __( 'No Task Group Items found in Trash.', 'wproject'),
                'all_items'     	=> __( 'All Task Group Items', 'wproject')
            ),
            'public'			 	=> false, /* Hides the permalink */
            'has_archive' 			=> false,
            'rewrite'				=> array('slug' => 'group'),
            'publicly_queryable'  	=> false,
            'hierarchical'        	=> true,
            'show_ui' 				=> true,
            'exclude_from_search'	=> true,
            'query_var'				=> true,
            'menu_position'			=> 24,
            'can_export'          	=> true,
            'menu_icon'         	=> get_template_directory_uri() . '/images/admin/admin-icon-task-group.svg',
            'supports'  			=> array('title', 'revisions', 'page-attributes', 'author'),
        )
    );
}
add_action( 'init', 'create_posttype' );
}

/* Custom metabox for Task Groups */
	class task_group_info_box {

	    var $plugin_dir;
	    var $plugin_url;

	    function  __construct() {

	        add_action( 'add_meta_boxes', array( $this, 'moreinfo_meta_box' ) );
	        add_action( 'save_post', array($this, 'save_data') );
	    }

	    function moreinfo_meta_box() {

            $post_types = array ( 'task_group' );
			
			foreach( $post_types as $post_type ) {
			
		        add_meta_box(
				'task_group_info',
				'Additional Information', 
				array( $this, 'meta_box_content' ),
				$post_type,
				'normal',
				'high'
			  );
		  }
	    }

	    function meta_box_content() {
	        global $post;
            $options 				= get_option( 'wproject_settings' ); 
            $task_priority			= get_post_meta($post->ID, 'task_priority', TRUE);
            $task_private 			= get_post_meta($post->ID, 'task_private', TRUE);
            $task_milestone			= get_post_meta($post->ID, 'task_milestone', TRUE);
            $task_description		= get_post_meta($post->ID, 'task_description', TRUE);	
            $related_ID 			= get_post_meta($post->ID, 'task_related', TRUE);
            $task_relation			= get_post_meta($post->ID, 'task_relation', TRUE);
            $related_title 			= get_the_title($task_relation);
            $related_URL 			= get_the_permalink($related_ID);
            $relates				= get_post_meta($post->ID, 'relates', TRUE);
            $related_tasks_status 	= get_post_meta( $related_ID, 'task_status', TRUE ); 
            $related_post_status  	= get_post_status( $related_ID );
            $is_blocked_by			= get_post_meta($post->ID, 'is_blocked_by', TRUE);
            $is_similar_to			= get_post_meta($post->ID, 'is_similar_to', TRUE);
            $has_issues_with		= get_post_meta($post->ID, 'has_issues_with', TRUE);
            $explanation 			= get_post_meta($post->ID, 'task_explanation', TRUE);
            $task_takeover_request	= get_post_meta($post->ID, 'task_takeover_request', TRUE);
            $context_label	        = get_post_meta($post->ID, 'context_label', TRUE);
            
            $cl                     = $options['context_labels'];
            $context_labels          = rtrim($cl, ', ');
            $the_context_labels     = explode(',', $context_labels);
                    
	        wp_nonce_field( plugin_basename( __FILE__ ), 'task_group_info_box_nounce' );
			

            /* Render UI */
			$the_post_type = get_post_type(); ?>

            <!--/ Start New Task Admin -->
		    <div class="new-task-admin">

                <!--/ Start Task Specifics -->
                <div class="section task-specifics">

                    <div class="radios-inline">
                        <strong><?php _e( "Priority", "wproject" ); ?></strong>

                        <label>
                            <input type="radio" name="task_priority" value="low" id="low" <?php if($task_priority == 'low') { echo 'checked'; } ?> /> <?php /* translators: One of 4 possible task priorities */ _e( "Low", "wproject" ); ?>
                        </label>

                        <label>
                            <input type="radio" name="task_priority" value="normal" id="normal" <?php if($task_priority == 'normal') { echo 'checked'; } ?> />
                            <?php /* translators: One of 4 possible task priorities */ _e( "Normal", "wproject" ); ?>
                        </label>

                        <label>
                            <input type="radio" name="task_priority" value="high" id="high" <?php if($task_priority == 'high') { echo 'checked'; } ?> />
                            <?php /* translators: One of 4 possible task priorities */ _e( "High", "wproject" ); ?>
                        </label>

                        <label>
                            <input type="radio" name="task_priority" value="urgent" id="urgent" <?php if($task_priority == 'urgent') { echo 'checked'; } ?> />
                            <?php /* translators: One of 4 possible task priorities */ _e( "Urgent", "wproject" ); ?>
                        </label>
                    </div>

                    <div class="milestone">
                        <label>
                            <strong><?php _e( "Milestone", "wproject" ); ?></strong>
                            <input type="hidden" name="task_milestone" value="no" />
                            <input type="checkbox" name="task_milestone" value="yes" id="task_milestone" <?php if($task_milestone == 'yes') { echo 'checked'; } ?> />
                            <?php _e( "Yes", "wproject" ); ?>
                        </label>
                    </div>

                    <div class="privacy">
                        <label>
                            <strong><?php _e( "Privacy", "wproject" ); ?></strong>
                            <input type="hidden" name="task_private" value="no" />
                            <input type="checkbox" name="task_private" value="yes" id="task_private" <?php if($task_private == 'yes') { echo 'checked'; } ?> />
                            <?php _e( "Obfuscate this task", "wproject" ); ?>
                        </label>
                    </div>

                    <script>
                        jQuery(document).ready(function() {
                            jQuery('#postbox-container-2').hide();
                            jQuery(".post-type-task_group #pageparentdiv .postbox-header h2").text('<?php _e('Add to task group', 'wproject'); ?>'); 
                            jQuery(".post-type-task_group #postbox-container-2, .menu-order-label-wrapper, #menu_order").hide();
                            var t = jQuery("#parent_id");
                            this.value;
                            t.change(function() {
                                "" == jQuery(this).val() ? jQuery("#postbox-container-2").fadeOut() : jQuery("#postbox-container-2").fadeIn()
                            }), jQuery("#parent_id").val() && jQuery("#postbox-container-2").show();
                            jQuery(".post-type-task_group .post-type-task_group .attached").remove();

                            /* Prevent children from being selected */
                            jQuery('#parent_id .level-1, #parent_id .level-2, #parent_id .level-3, #parent_id .level-4, #parent_id .level-5').remove();
                        });
                    </script>

                </div>
                <!--/ End Task Specifics -->

                <!--/ Start Task Details -->
                <div class="section task-details">
                    <label><strong><?php _e( "More details", "wproject" ); ?></strong>
                    <textarea id="task_description" name="task_description" size="20" class="large-text" rows="20"><?php echo $task_description; ?></textarea>
                    </label>
                </div>
                <!--/ End Task Details -->

                <!--/ Start Context Label -->
                <div class="section context-labels">
                    <div class="radios-inline">
                        <strong><?php _e( "Context label", "wproject" ); ?></strong>
                        
                        <?php 
                            foreach($the_context_labels as $value) {

                                if($context_label == str_replace('-', ' ', trim($value))) {
                                    $checked = 'checked';
                                } else {
                                    $checked = '';
                                }

                                echo '<label class="radio">';
                                echo '<input type="radio" name="context_label" value="' . str_replace('-', ' ', trim($value)) . '"' . $checked . ' />' . str_replace('-', ' ', trim($value));
                                echo '</label>';
                            }
                        ?>
                        </div>
                        <style>
                            .context-labels label.radio input {
                                margin: -2px 3px 0 0;
                            }
                    </style>
                </div>
                <!--/ End Context Label -->

                <?php require_once('subtasks.php'); ?>   

                <?php do_action( 'wpproject_admin_task' ); ?>

            </div>
            <!--/ End New Task Admin -->
			<?php
		}

	    function save_data($post_id) {
	        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
	            return;

	        if ( !wp_verify_nonce( isset($_POST['task_group_info_box_nounce']), plugin_basename( __FILE__ ) ) )
	            return;

	        // Check permissions
	        if ( 'task_group' == $_POST['post_type'] ) {
	            if ( !current_user_can( 'edit_page', $post_id ) )
	                return;
	        }

			// Update the fields
			$data = $_POST['task_description'];
            update_post_meta($post_id, 'task_description', implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $data ) ) ));

			$data = sanitize_text_field($_POST['task_start_date']);
			update_post_meta($post_id, 'task_start_date', $data, get_post_meta($post_id, 'task_start_date', TRUE));
			
			$data = sanitize_text_field($_POST['task_end_date']);
			update_post_meta($post_id, 'task_end_date', $data, get_post_meta($post_id, 'task_end_date', TRUE));

			$data = sanitize_text_field($_POST['task_priority']);
			update_post_meta($post_id, 'task_priority', $data, get_post_meta($post_id, 'task_priority', TRUE));
			
			$data = sanitize_text_field($_POST['task_status']);
			update_post_meta($post_id, 'task_status', $data, get_post_meta($post_id, 'task_status', TRUE));
            
            $data = sanitize_text_field($_POST['task_relation']);
			update_post_meta($post_id, 'task_relation', $data, get_post_meta($post_id, 'task_relation', TRUE));
            
            $data = sanitize_text_field($_POST['task_related']);
			update_post_meta($post_id, 'task_related', $data, get_post_meta($post_id, 'task_related', TRUE));
            
            $data = sanitize_text_field($_POST['task_explanation']);
			update_post_meta($post_id, 'task_explanation', $data, get_post_meta($post_id, 'task_explanation', TRUE));
			
			$data = sanitize_text_field($_POST['task_job_number']);
			update_post_meta($post_id, 'task_job_number', $data, get_post_meta($post_id, 'task_job_number', TRUE));

            $data = sanitize_text_field($_POST['context_label']);
            update_post_meta($post_id, 'context_label', $data, get_post_meta($post_id, 'context_label', TRUE));

            $data = sanitize_text_field($_POST['subtask_list']);
            update_post_meta($post_id, 'subtask_list', $data, get_post_meta($post_id, 'subtask_list', TRUE));

			return $data;
	    }
	}
	$task_group_info_box = new task_group_info_box;