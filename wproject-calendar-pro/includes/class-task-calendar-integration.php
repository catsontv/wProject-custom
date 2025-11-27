<?php
/**
 * Task-Calendar Integration
 *
 * Integrates wProject tasks with Calendar Pro
 * - Adds calendar selector to task creation/edit forms
 * - Automatically creates calendar events from tasks
 * - Syncs task updates with calendar events
 *
 * @package wProject Calendar Pro
 * @since 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WProject_Task_Calendar_Integration {

    /**
     * Initialize the integration
     */
    public static function init() {
        // Add calendar selector to new task form
        add_action( 'new_task_end', array( __CLASS__, 'add_calendar_selector_new_task' ) );

        // Add calendar selector to edit task form using admin_footer
        add_action( 'admin_footer', array( __CLASS__, 'inject_calendar_selector_edit_task' ) );

        // Save task to calendar when task is saved
        add_action( 'save_post_task', array( __CLASS__, 'handle_task_save' ), 10, 3 );

        // Delete calendar event when task is deleted
        add_action( 'before_delete_post', array( __CLASS__, 'delete_calendar_event_from_task' ), 10, 1 );
    }

    /**
     * Add calendar selector to new task form
     */
    public static function add_calendar_selector_new_task() {
        self::render_calendar_selector( '' );
    }

    /**
     * Inject calendar selector into edit task form using JavaScript
     */
    public static function inject_calendar_selector_edit_task() {
        // Only run on edit task page
        $screen = get_current_screen();
        if ( ! $screen || $screen->id !== 'task' || ! isset( $_GET['task-id'] ) ) {
            return;
        }

        $task_id = (int) $_GET['task-id'];
        $task_calendar_id = get_post_meta( $task_id, 'task_calendar_id', true );
        $user_calendars = WProject_Calendar_Core::get_user_calendars();

        if ( empty( $user_calendars ) ) {
            return;
        }

        // Auto-select default calendar if task doesn't have one
        if ( empty( $task_calendar_id ) ) {
            foreach ( $user_calendars as $calendar ) {
                if ( $calendar->name === 'Personal' || $calendar->is_default == 1 ) {
                    $task_calendar_id = $calendar->id;
                    break;
                }
            }
            if ( empty( $task_calendar_id ) ) {
                $task_calendar_id = $user_calendars[0]->id;
            }
        }
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Find the task status field and add calendar selector after it
            var statusField = $('select[name="task_status"]').closest('li');
            if (statusField.length) {
                var calendarHTML = '<li class="task-calendar-selector">' +
                    '<label><?php _e( 'Add to Calendar', 'wproject-calendar-pro' ); ?></label>' +
                    '<select name="task_calendar_id" id="task_calendar_id">' +
                    '<option value=""><?php _e( 'No calendar', 'wproject-calendar-pro' ); ?></option>' +
                    <?php foreach ( $user_calendars as $calendar ) : ?>
                    '<option value="<?php echo esc_js( $calendar->id ); ?>" <?php echo $task_calendar_id == $calendar->id ? 'selected' : ''; ?>><?php echo esc_js( $calendar->name ); ?></option>' +
                    <?php endforeach; ?>
                    '</select>' +
                    '<p style="font-size: 0.9em; color: #666; margin: 5px 0 0 0;"><?php _e( 'Task will be synced to calendar with project assignment', 'wproject-calendar-pro' ); ?></p>' +
                    '</li>';

                statusField.after(calendarHTML);
            }
        });
        </script>
        <?php
    }

    /**
     * Render calendar selector HTML
     */
    private static function render_calendar_selector( $selected_calendar_id = '' ) {
        // Get user's calendars
        $user_calendars = WProject_Calendar_Core::get_user_calendars();

        if ( empty( $user_calendars ) ) {
            return; // Don't show selector if no calendars
        }

        // Auto-select default calendar if none selected
        if ( empty( $selected_calendar_id ) ) {
            // Find default (Personal) calendar or use first calendar
            foreach ( $user_calendars as $calendar ) {
                if ( $calendar->name === 'Personal' || $calendar->is_default == 1 ) {
                    $selected_calendar_id = $calendar->id;
                    break;
                }
            }
            // If no default found, use first calendar
            if ( empty( $selected_calendar_id ) && ! empty( $user_calendars ) ) {
                $selected_calendar_id = $user_calendars[0]->id;
            }
        }
        ?>
        <fieldset class="calendar-integration">
            <legend><?php _e( 'Calendar', 'wproject-calendar-pro' ); ?></legend>
            <ul>
                <li class="task-calendar-selector">
                    <label><?php _e( 'Add to Calendar', 'wproject-calendar-pro' ); ?></label>
                    <select name="task_calendar_id" id="task_calendar_id">
                        <option value=""><?php _e( 'No calendar', 'wproject-calendar-pro' ); ?></option>
                        <?php foreach ( $user_calendars as $calendar ) : ?>
                            <option value="<?php echo esc_attr( $calendar->id ); ?>"
                                    <?php selected( $selected_calendar_id, $calendar->id ); ?>>
                                <?php echo esc_html( $calendar->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="field-description">
                        <?php _e( 'Task will be synced to selected calendar with project assignment', 'wproject-calendar-pro' ); ?>
                    </p>
                </li>
            </ul>
        </fieldset>
        <?php
    }

    /**
     * Handle task save (create or update)
     */
    public static function handle_task_save( $post_id, $post, $update ) {
        error_log( '=== TASK SAVE HANDLER CALLED ===' );
        error_log( 'Post ID: ' . $post_id );
        error_log( 'Post Type: ' . $post->post_type );
        error_log( 'Is Update: ' . ( $update ? 'YES' : 'NO' ) );

        // Avoid autosaves and revisions
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            error_log( 'Skipping: AUTOSAVE' );
            return;
        }

        // Check if this is a task post type
        if ( $post->post_type !== 'task' ) {
            error_log( 'Skipping: Not a task post type' );
            return;
        }

        // Check user permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            error_log( 'Skipping: No permission' );
            return;
        }

        // Get calendar ID from POST
        $calendar_id = isset( $_POST['task_calendar_id'] ) ? (int) $_POST['task_calendar_id'] : 0;
        error_log( 'Calendar ID from POST: ' . $calendar_id );
        error_log( 'POST data: ' . print_r( $_POST, true ) );

        // Get existing event ID
        $event_id = get_post_meta( $post_id, 'task_calendar_event_id', true );
        error_log( 'Existing Event ID: ' . ( $event_id ? $event_id : 'NONE' ) );

        // Determine action
        if ( $calendar_id && ! $event_id ) {
            // Create new event
            error_log( 'ACTION: Creating new calendar event' );
            self::create_calendar_event_from_task( $post_id );
        } else if ( $update && $event_id && $calendar_id ) {
            // Update existing event
            error_log( 'ACTION: Updating existing calendar event' );
            self::update_calendar_event_from_task( $post_id );
        } else if ( ! $calendar_id && $event_id ) {
            // Remove calendar association
            error_log( 'ACTION: Removing calendar event' );
            WProject_Event_Manager::delete_event( $event_id );
            delete_post_meta( $post_id, 'task_calendar_event_id' );
            delete_post_meta( $post_id, 'task_calendar_id' );
        } else {
            error_log( 'ACTION: No action taken (calendar_id=' . $calendar_id . ', event_id=' . $event_id . ')' );
        }

        error_log( '=== TASK SAVE HANDLER END ===' );
    }

    /**
     * Create calendar event from task
     *
     * @param int $task_id Task ID
     * @param array $task_data Task data
     */
    public static function create_calendar_event_from_task( $task_id, $task_data = array() ) {
        error_log( '[Calendar Pro] create_calendar_event_from_task called for task ' . $task_id );

        // Get calendar ID from POST or task meta
        $calendar_id = isset( $_POST['task_calendar_id'] ) ? (int) $_POST['task_calendar_id'] : 0;
        error_log( '[Calendar Pro] Calendar ID: ' . $calendar_id );

        if ( ! $calendar_id ) {
            error_log( '[Calendar Pro] No calendar ID, skipping event creation' );
            return;
        }

        // Save calendar ID to task meta
        update_post_meta( $task_id, 'task_calendar_id', $calendar_id );

        // Get task details
        $task = get_post( $task_id );
        if ( ! $task ) {
            error_log( '[Calendar Pro] Task not found' );
            return;
        }

        // Get task meta
        $task_start_date = get_post_meta( $task_id, 'task_start_date', true );
        $task_end_date = get_post_meta( $task_id, 'task_end_date', true );
        $task_status = get_post_meta( $task_id, 'task_status', true );
        $task_priority = get_post_meta( $task_id, 'task_priority', true );

        // Get task's project (tasks can belong to projects via taxonomy)
        $project_id = null;
        $task_projects = get_the_terms( $task_id, 'project' );
        if ( $task_projects && ! is_wp_error( $task_projects ) ) {
            // Use first project if task belongs to multiple
            $project = reset( $task_projects );
            $project_id = $project->term_id;
            error_log( '[Calendar Pro] Task belongs to project: ' . $project->name . ' (ID: ' . $project_id . ')' );
        }

        error_log( '[Calendar Pro] Task details - Title: ' . $task->post_title . ', Start: ' . $task_start_date . ', End: ' . $task_end_date );

        // Determine event color based on priority
        $color_map = array(
            'low'    => '#4caf50',  // Green
            'normal' => '#00bcd4',  // Cyan
            'high'   => '#ff9800',  // Orange
            'urgent' => '#f44336'   // Red
        );
        $event_color = isset( $color_map[ $task_priority ] ) ? $color_map[ $task_priority ] : '#00bcd4';

        // Prepare event data
        $event_data = array(
            'calendar_id'    => $calendar_id,
            'title'          => $task->post_title,
            'description'    => $task->post_content,
            'start_datetime' => $task_start_date ? $task_start_date . ' 09:00:00' : current_time( 'mysql' ),
            'end_datetime'   => $task_end_date ? $task_end_date . ' 17:00:00' : current_time( 'mysql' ),
            'all_day'        => 1,
            'event_type'     => 'task',
            'color'          => $event_color,
            'task_id'        => $task_id,
            'project_id'     => $project_id,  // CRITICAL: Set project from task
            'status'         => $task_status === 'complete' ? 'completed' : 'confirmed',
            'visibility'     => 'private'
        );

        error_log( '[Calendar Pro] Event data: ' . json_encode( $event_data ) );

        // Create the event
        $event_id = WProject_Event_Manager::create_event( $event_data );

        if ( $event_id ) {
            // Store event ID in task meta for future updates
            update_post_meta( $task_id, 'task_calendar_event_id', $event_id );
            error_log( '[Calendar Pro] SUCCESS: Created calendar event ' . $event_id . ' for task ' . $task_id );
        } else {
            error_log( '[Calendar Pro] ERROR: Failed to create calendar event for task ' . $task_id );
        }
    }

    /**
     * Update calendar event from task
     *
     * @param int $task_id Task ID
     * @param array $task_data Task data
     */
    public static function update_calendar_event_from_task( $task_id, $task_data = array() ) {
        // Get calendar ID from POST
        $calendar_id = isset( $_POST['task_calendar_id'] ) ? (int) $_POST['task_calendar_id'] : 0;

        // Get existing event ID
        $event_id = get_post_meta( $task_id, 'task_calendar_event_id', true );

        // Case 1: Calendar was removed (no calendar ID but event exists)
        if ( ! $calendar_id && $event_id ) {
            // Delete the event
            WProject_Event_Manager::delete_event( $event_id );
            delete_post_meta( $task_id, 'task_calendar_event_id' );
            delete_post_meta( $task_id, 'task_calendar_id' );
            error_log( '[Calendar Pro] Deleted calendar event ' . $event_id . ' for task ' . $task_id );
            return;
        }

        // Case 2: Calendar was added (calendar ID but no event)
        if ( $calendar_id && ! $event_id ) {
            self::create_calendar_event_from_task( $task_id, $task_data );
            return;
        }

        // Case 3: Calendar exists and event exists - update it
        if ( $calendar_id && $event_id ) {
            // Get task details
            $task = get_post( $task_id );
            if ( ! $task ) {
                return;
            }

            // Get task meta
            $task_start_date = get_post_meta( $task_id, 'task_start_date', true );
            $task_end_date = get_post_meta( $task_id, 'task_end_date', true );
            $task_status = get_post_meta( $task_id, 'task_status', true );
            $task_priority = get_post_meta( $task_id, 'task_priority', true );

            // Get task's project (in case it changed)
            $project_id = null;
            $task_projects = get_the_terms( $task_id, 'project' );
            if ( $task_projects && ! is_wp_error( $task_projects ) ) {
                $project = reset( $task_projects );
                $project_id = $project->term_id;
                error_log( '[Calendar Pro] Updating event with project: ' . $project->name . ' (ID: ' . $project_id . ')' );
            }

            // Determine event color based on priority
            $color_map = array(
                'low'    => '#4caf50',
                'normal' => '#00bcd4',
                'high'   => '#ff9800',
                'urgent' => '#f44336'
            );
            $event_color = isset( $color_map[ $task_priority ] ) ? $color_map[ $task_priority ] : '#00bcd4';

            // Prepare event update data
            $event_data = array(
                'calendar_id'    => $calendar_id,
                'title'          => $task->post_title,
                'description'    => $task->post_content,
                'start_datetime' => $task_start_date ? $task_start_date . ' 09:00:00' : null,
                'end_datetime'   => $task_end_date ? $task_end_date . ' 17:00:00' : null,
                'color'          => $event_color,
                'project_id'     => $project_id,  // CRITICAL: Sync project from task
                'status'         => $task_status === 'complete' ? 'completed' : 'confirmed'
            );

            // Remove null values
            $event_data = array_filter( $event_data, function( $value ) {
                return $value !== null;
            });

            // Update the event
            $updated = WProject_Event_Manager::update_event( $event_id, $event_data );

            // Update calendar ID meta
            update_post_meta( $task_id, 'task_calendar_id', $calendar_id );

            if ( $updated ) {
                error_log( '[Calendar Pro] Updated calendar event ' . $event_id . ' for task ' . $task_id );
            } else {
                error_log( '[Calendar Pro] Failed to update calendar event ' . $event_id . ' for task ' . $task_id );
            }
        }
    }

    /**
     * Delete calendar event from task
     *
     * @param int $post_id Post ID
     */
    public static function delete_calendar_event_from_task( $post_id ) {
        // Only process if this is a task post type
        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'task' ) {
            return;
        }

        $event_id = get_post_meta( $post_id, 'task_calendar_event_id', true );

        if ( $event_id ) {
            WProject_Event_Manager::delete_event( $event_id );
            error_log( '[Calendar Pro] Deleted calendar event ' . $event_id . ' for deleted task ' . $post_id );

            // Clean up meta
            delete_post_meta( $post_id, 'task_calendar_event_id' );
            delete_post_meta( $post_id, 'task_calendar_id' );
        }
    }
}

// Initialize the integration
WProject_Task_Calendar_Integration::init();
