<?php
/**
 * Recurring Events Class
 *
 * Handles recurring event pattern generation and management
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WProject_Recurring_Events {

    /**
     * Create recurring event instances
     *
     * @param int $parent_event_id Parent event ID
     * @param array $recurrence_rule Recurrence rule data
     * @param string $end_date End date for recurrence (Y-m-d)
     * @return int Number of instances created
     */
    public static function create_instances( $parent_event_id, $recurrence_rule, $end_date ) {
        $parent_event = WProject_Event_Manager::get_event( $parent_event_id );

        if ( ! $parent_event ) {
            return 0;
        }

        $instances = self::generate_occurrences( $parent_event, $recurrence_rule, $end_date );
        $count = 0;

        foreach ( $instances as $instance ) {
            $event_data = (array) $parent_event;
            unset( $event_data['id'] );

            $event_data['start_datetime'] = $instance['start'];
            $event_data['end_datetime'] = $instance['end'];
            $event_data['recurrence_parent_id'] = $parent_event_id;
            $event_data['recurrence_rule'] = json_encode( $recurrence_rule );

            if ( WProject_Event_Manager::create_event( $event_data ) ) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Generate occurrence dates based on recurrence rule
     *
     * @param object $parent_event Parent event
     * @param array $rule Recurrence rule
     * @param string $end_date End date (Y-m-d)
     * @return array Array of occurrence dates
     */
    private static function generate_occurrences( $parent_event, $rule, $end_date ) {
        $occurrences = array();

        $frequency = isset( $rule['frequency'] ) ? $rule['frequency'] : 'daily';
        $interval = isset( $rule['interval'] ) ? (int) $rule['interval'] : 1;
        $count = isset( $rule['count'] ) ? (int) $rule['count'] : null;
        $by_day = isset( $rule['by_day'] ) ? $rule['by_day'] : array();

        $start = new DateTime( $parent_event->start_datetime );
        $end = new DateTime( $parent_event->end_datetime );
        $duration = $start->diff( $end );

        $limit = new DateTime( $end_date );
        $current = clone $start;

        $occurrence_count = 0;
        $max_iterations = $count ? $count : 365; // Prevent infinite loops

        switch ( $frequency ) {
            case 'daily':
                while ( $current <= $limit && $occurrence_count < $max_iterations ) {
                    if ( $current > $start ) { // Skip the parent event itself
                        $occurrence_end = clone $current;
                        $occurrence_end->add( $duration );

                        $occurrences[] = array(
                            'start' => $current->format( 'Y-m-d H:i:s' ),
                            'end'   => $occurrence_end->format( 'Y-m-d H:i:s' )
                        );

                        $occurrence_count++;
                    }

                    $current->modify( "+{$interval} day" );

                    if ( $count && $occurrence_count >= $count ) {
                        break;
                    }
                }
                break;

            case 'weekly':
                while ( $current <= $limit && $occurrence_count < $max_iterations ) {
                    if ( $current > $start ) {
                        // Check if current day matches by_day rule
                        if ( empty( $by_day ) || in_array( strtolower( $current->format( 'D' ) ), $by_day ) ) {
                            $occurrence_end = clone $current;
                            $occurrence_end->add( $duration );

                            $occurrences[] = array(
                                'start' => $current->format( 'Y-m-d H:i:s' ),
                                'end'   => $occurrence_end->format( 'Y-m-d H:i:s' )
                            );

                            $occurrence_count++;
                        }
                    }

                    if ( empty( $by_day ) ) {
                        $current->modify( "+{$interval} week" );
                    } else {
                        $current->modify( '+1 day' );
                    }

                    if ( $count && $occurrence_count >= $count ) {
                        break;
                    }
                }
                break;

            case 'monthly':
                while ( $current <= $limit && $occurrence_count < $max_iterations ) {
                    if ( $current > $start ) {
                        $occurrence_end = clone $current;
                        $occurrence_end->add( $duration );

                        $occurrences[] = array(
                            'start' => $current->format( 'Y-m-d H:i:s' ),
                            'end'   => $occurrence_end->format( 'Y-m-d H:i:s' )
                        );

                        $occurrence_count++;
                    }

                    $current->modify( "+{$interval} month" );

                    if ( $count && $occurrence_count >= $count ) {
                        break;
                    }
                }
                break;

            case 'yearly':
                while ( $current <= $limit && $occurrence_count < $max_iterations ) {
                    if ( $current > $start ) {
                        $occurrence_end = clone $current;
                        $occurrence_end->add( $duration );

                        $occurrences[] = array(
                            'start' => $current->format( 'Y-m-d H:i:s' ),
                            'end'   => $occurrence_end->format( 'Y-m-d H:i:s' )
                        );

                        $occurrence_count++;
                    }

                    $current->modify( "+{$interval} year" );

                    if ( $count && $occurrence_count >= $count ) {
                        break;
                    }
                }
                break;
        }

        return $occurrences;
    }

    /**
     * Update all instances of a recurring event
     *
     * @param int $parent_event_id Parent event ID
     * @param array $event_data Event data to update
     * @return int Number of instances updated
     */
    public static function update_all_instances( $parent_event_id, $event_data ) {
        global $wpdb;

        $table_events = $wpdb->prefix . 'wproject_events';

        // Update parent
        WProject_Event_Manager::update_event( $parent_event_id, $event_data );

        // Update all children
        $event_data['updated_at'] = current_time( 'mysql' );

        $result = $wpdb->update(
            $table_events,
            $event_data,
            array( 'recurrence_parent_id' => $parent_event_id ),
            null,
            array( '%d' )
        );

        return $result !== false ? $result : 0;
    }

    /**
     * Delete all instances of a recurring event
     *
     * @param int $parent_event_id Parent event ID
     * @return bool Success or failure
     */
    public static function delete_all_instances( $parent_event_id ) {
        global $wpdb;

        $table_events = $wpdb->prefix . 'wproject_events';

        // Get all instances
        $instances = $wpdb->get_results( $wpdb->prepare(
            "SELECT id FROM $table_events WHERE recurrence_parent_id = %d",
            $parent_event_id
        ) );

        // Delete each instance
        foreach ( $instances as $instance ) {
            WProject_Event_Manager::delete_event( $instance->id );
        }

        // Delete parent
        WProject_Event_Manager::delete_event( $parent_event_id );

        return true;
    }

    /**
     * Get all instances of a recurring event
     *
     * @param int $parent_event_id Parent event ID
     * @return array Array of event objects
     */
    public static function get_instances( $parent_event_id ) {
        global $wpdb;

        $table_events = $wpdb->prefix . 'wproject_events';

        $instances = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_events WHERE recurrence_parent_id = %d ORDER BY start_datetime ASC",
            $parent_event_id
        ) );

        return $instances;
    }

    /**
     * Parse recurrence rule string into array
     *
     * @param string $rule_string Recurrence rule string (e.g., "FREQ=WEEKLY;INTERVAL=1;BYDAY=MO,WE,FR")
     * @return array Parsed rule
     */
    public static function parse_rule_string( $rule_string ) {
        $rule = array();

        $parts = explode( ';', $rule_string );

        foreach ( $parts as $part ) {
            $kv = explode( '=', $part );
            if ( count( $kv ) == 2 ) {
                $key = strtolower( $kv[0] );
                $value = $kv[1];

                if ( $key == 'byday' ) {
                    $value = explode( ',', $value );
                    $value = array_map( 'strtolower', $value );
                }

                $rule[ $key ] = $value;
            }
        }

        return $rule;
    }

    /**
     * Convert rule array to string
     *
     * @param array $rule Rule array
     * @return string Rule string
     */
    public static function rule_to_string( $rule ) {
        $parts = array();

        foreach ( $rule as $key => $value ) {
            $key = strtoupper( $key );

            if ( is_array( $value ) ) {
                $value = implode( ',', array_map( 'strtoupper', $value ) );
            }

            $parts[] = "$key=$value";
        }

        return implode( ';', $parts );
    }
}
