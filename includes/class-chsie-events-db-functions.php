<?php

/**
 * The file that defines the plugin's shared database interface methods.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      1.0.0
 *
 * @package    CHSIE_Events
 * @subpackage CHSIE_Events/includes
 */

/**
 * The class that defines the plugin's shared database interface methods.
 *
 *
 * @since      1.0.0
 * @package    CHSIE_Events
 * @subpackage CHSIE_Events/includes
 * @author     Ben Hoverter <benhoverter@gmail.com>
 */
class CHSIE_Events_DB_Functions {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	private $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of the plugin.
	 */
	private $version;


	/**
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 *
	 * @since    1.0.0
	 */
     public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
		$this->version = $version;

	}



    /**
     * Gets and unserializes an array of tickets for a single event.
     *
     * @since    1.0.0
     *
     * @param   $event_id   (string)      The post_id of the event whose RSVPs we want.
     *
     * @return  $event_rsvps    The array of all RSVPs for that event.
     */
    public function get_event_rsvps( $event_id ) {

        global $wpdb;

        // Get all postmeta for every rsvp whose event matches the $event_id:
        // Get array of RSVP post_ids where '_tribe_rsvp_event' matches $event_id.
        $event_rsvps = $wpdb->get_col( $wpdb->prepare(
            "
            SELECT  post_id FROM $wpdb->postmeta
            WHERE   meta_key = %s
                    AND meta_value = %s
            ",
                '_tribe_rsvp_event',
                $event_id
            )
        );

        // This gets used in uer_delete_rsvp().
        // Must contain the rsvp_ids associated with the event so we can
        // check each one against the rsvp_id to delete.
        return $event_rsvps;

    }


    /**
     * Gets all relevant info for all of a user's registered events.
     *
     * @since    1.0.0
     *
     * @return   $user_rsvp_array    The array of all the user's RSVP data.
     */
    public function get_user_rsvps( $user_id ) {

        global $wpdb;

        // Get array of RSVP post_ids where '_tribe_tickets_attendee_user_id' matches $user_id.
        $rsvp_ids = $wpdb->get_col( $wpdb->prepare(
            "
            SELECT  post_id FROM $wpdb->postmeta
            WHERE   meta_key = %s
                    AND meta_value = %s
            ",
                '_tribe_tickets_attendee_user_id',
                $user_id
            )
        );

        /*
        echo "<p>RSVP IDs are: </p><pre>";
        var_dump( $rsvp_ids );
        echo "</pre>";
        */

        $rsvp_array = array();

        // For each RSVP post_id:
        foreach ( $rsvp_ids as $i => $rsvp_id ) {

            // Get the product (RSVP Role) info:
            $rsvp_array[$i]['product_id'] = get_post_meta( $rsvp_id, '_tribe_rsvp_product', true );
            $rsvp_array[$i]['product_type'] = get_the_title( $rsvp_array[$i]['product_id'] );

            // Get the event info:
            $rsvp_array[$i]['event_id'] = get_post_meta( $rsvp_id, '_tribe_rsvp_event', true );
            $rsvp_array[$i]['event_title'] = get_the_title( $rsvp_array[$i]['event_id'] );
            $rsvp_array[$i]['date'] = get_post_meta( $rsvp_array[$i]['event_id'], '_EventStartDate', true );
            $rsvp_array[$i]['event_url'] = get_post_meta( $rsvp_array[$i]['event_id'], '_EventURL', true );

            /*
            echo "<p>Event URL is: </p><pre>";
            var_dump( $rsvp_array[$i]['event_url'] );
            echo "</pre>";
            */

            // Sets the event URL to the permalink if it hasn't been user-defined.
            if( empty( $rsvp_array[$i]['event_url'] ) ) {
                $rsvp_array[$i]['event_url'] = esc_url( get_permalink( (int) $rsvp_array[$i]['event_id'] ) );
            } else {
                $rsvp_array[$i]['event_url'] = esc_url( $rsvp_array[$i]['event_url'] );
            };


            // Get the user RSVP info:
            $rsvp_array[$i]['fullname'] = get_post_meta( $rsvp_id, '_tribe_rsvp_full_name', true );
            $rsvp_array[$i]['email'] = get_post_meta( $rsvp_id, '_tribe_rsvp_email', true );
            $rsvp_array[$i]['code'] = get_post_meta( $rsvp_id, '_tribe_rsvp_security_code', true );
            $rsvp_array[$i]['rsvp_id'] = $rsvp_id;


            // Convert the event date to a Unix timestamp.
            $event_unix_time = strtotime( $rsvp_array[$i]['date'] );

            // Increment the time by 1 ms if duplicate ticket start date.
            if( isset( $rsvp_array[ $event_unix_time ] ) ) {
                $event_unix_time ++;
            }

            // Reformat the date for pretty display.
            $event_DateTime = new DateTime( $rsvp_array[$i]['date'] );
            $event_pretty_date = $event_DateTime->format( 'M d, Y \a\t g:i a' );

            // Set the date to the new pretty string.
            $rsvp_array[$i]['date'] = $event_pretty_date;


            // Replace this key with the Unix timestamp for ordering events by time.
            $rsvp_array[ $event_unix_time ] = $rsvp_array[$i];
            unset( $rsvp_array[$i] );

        } // END OF : foreach ( $rsvp_ids as $i => $rsvp_id )

        // Take the rsvp_array and sort it by timestamp key order.
        ksort( $rsvp_array, SORT_NUMERIC );  // Order by Unix time.

        // Return the array.
        return $rsvp_array; // The event_id-indexed 2D array.

    }



    /**
     * Decrements the 'total_sales' meta_value of the product type for an RSVP.
     *
     * @since    1.0.0
     *
     * @return   $total_sales_down    'false' on failure, or new meta_value on success.
     */
    public function decrement_total_sales( $rsvp_id ) {

        $product_id = get_post_meta( $rsvp_id, '_tribe_rsvp_product', true );
        //echo "<p>product_id meta returned {$product_id}.</p>";

        $product_sales = get_post_meta( $product_id, 'total_sales', true );
        //echo "<p>product_sales meta returned {$product_sales}.</p>";

        $decremented = update_post_meta( $product_id, 'total_sales', $product_sales - 1 );
        //echo "<p>decrement_total_sales() result is {$decremented} ( 1: true, 0:false ).</p>";

        $total_sales_down = ( $decremented !== false )  ?  get_post_meta( $product_id, 'total_sales', true )  :  false;

        return $total_sales_down;

    }


    /**
     * Gets all relevant info for all of a user's registered events.
     *
     * @since    1.0.0
     *
     * @return   $transient_updated    True/false, unless meta_value DNE: then meta_id.
     */
    public function update_tribe_transient( $rsvp_id ) {

        $event_id = get_post_meta( $rsvp_id, '_tribe_rsvp_event', true );

        $event_rsvps = $this->get_event_rsvps( $event_id );

        $transient_updated = false;

        //echo "<pre>" . var_dump($event_rsvps) . "</pre>";

        foreach( $event_rsvps as $index => $event_rsvp ) {

            // If the $event_rsvp doesn't match the Delete button's $rsvp_id, try again.
            if ( $event_rsvp !== $rsvp_id ) {
                continue;
            }

            // However, IF the $rsvp_id passed by the Delete button DOES match:

            // HANDLE THE '_transient_tribe_attendees' ARRAY FOR THE EVENT. MAY NOT EXIST IF TICKET WAS MOVED.
            $event_transient_array = get_post_meta( $event_id, '_transient_tribe_attendees', true );
            /*
            echo "<pre>";
            var_dump( $event_transient_array );
            echo "</pre>";
            */

            if( !empty( $event_transient_array ) ) {

                // Get the individual RSVP arrays and iterate over them:
                foreach ( $event_transient_array as $i => $rsvp_array ) {

                    // Look for a match to the Delete button's $rsvp_id in the array:
                    if( $rsvp_array['ticket_id'] != $rsvp_id ) {

                        //echo "event_transient_array index {$i} has been ignored. Ticket_id index value !== rsvp_id.";

                        continue;

                    } else {

                        // When a match is found, delete the matching element from the parent array and reindex it:
                        unset( $event_transient_array[$i] );
                        $event_transient_array = array_values( $event_transient_array );

                        /*
                        echo "<p>Event transient array changed to: </p><pre>";
                        var_dump( $event_transient_array );
                        echo "</pre>";
                        */

                        break;

                    }

                }

                // Returns true on success, false on failure or same meta_value; if meta DNE, returns meta_id.
                $transient_updated = update_post_meta( $event_id, '_transient_tribe_attendees', $event_transient_array );

            } // End of '_transient_tribe_attendees' update.

        } // End:  foreach( $event_rsvps as $index => $event_rsvp )

        return $transient_updated;

    }


}

?>
