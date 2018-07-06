<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    CHSIE_Events
 * @subpackage CHSIE_Events/admin
 */

/**
 * This class defines the plugin name, version, and two hooks to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    CHSIE_Events
 * @subpackage CHSIE_Events/admin
 * @author     Ben Hoverter <ben.hoverter@gmail.com>
 */
class CHSIE_Events_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * The current instance of the CHSIE_Events_DB_Functions class.
     *
     * @since    1.0.0
     * @access   private
     * @var      object    $db_functions    The current instance of the CHSIE_Events_DB_Functions class.
     */
    private $db_functions;

	/**
	 * The page id of the current user registered events page.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      int    $current_user_reg_page_id    The page id of the current user registered events page.
	 */
	private $current_user_reg_page_id;

	/**
	 * The ticket product_ids in use for this event.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      int    $ticket_types    The ticket product_ids in use for this event.
	 */
	private $ticket_types;

    /**
    * The data array for admin AJAX functions.
    *
    * @since    1.0.0
    * @access   public
    * @var      associative array    $ajax_data    The data for admin AJAX functions.
    */
    public $ajax_data;

    /**
    * The nonce for the AJAX call.  Must be available to event_mats_ajax_save().
    *
    * @since    1.0.0
    * @access   public
    * @var      string    $ajax_nonce    The nonce for the AJAX call.
    */
    public $ajax_nonce;

    /**
    * The current post ID.  Needed for AJAX (otherwise unavailable).
    *
    * @since    1.0.0
    * @access   public
    * @var      object    $post_id    The current post ID.
    */
    public $post_id;



	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $db_functions ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->db_functions = $db_functions;

    }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
         * An instance of this class should be passed as the second parameter
         * of the run() function defined in CHSIE_Events_Loader
         * as all of the hooks are defined in that particular class.
		 *
		 * The CHSIE_Events_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        // Variable to hold the URL path for enqueueing.
        $admin_css_url_path = plugin_dir_url( __FILE__ ) . 'css/chsie-events-admin.css';

        // Variable to hold the server path for filemtime() and versioning.
        $admin_css_dir_path = plugin_dir_path( __FILE__ ) . 'css/chsie-events-admin.css';

        // Register the style using an automatic and unique version based on modification time.
        wp_register_style( $this->plugin_name, $admin_css_url_path , array() ,  filemtime( $admin_css_dir_path ) , 'all' );

        // Enqueue the style.
        wp_enqueue_style( $this->plugin_name );
        wp_enqueue_style( 'thickbox' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * An instance of this class should be passed as the second parameter
         * of the run() function defined in CHSIE_Events_Loader
         * as all of the hooks are defined in that particular class.
		 *
		 * The CHSIE_Events_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        // Variable to hold the URL path for enqueueing.
        $admin_js_url_path = plugin_dir_url( __FILE__ ) . 'js/chsie-events-admin.js';

        // Variable to hold the server path for filemtime() and versioning.
        $admin_js_dir_path = plugin_dir_path( __FILE__ ) . 'js/chsie-events-admin.js';

        // Register the script using an automatic and unique version based on modification time.
        wp_register_script( $this->plugin_name, $admin_js_url_path , array( 'jquery', 'thickbox', 'media-upload') ,  filemtime( $admin_js_dir_path ) , false );

        // Enqueue the scripts.
        wp_enqueue_script( $this->plugin_name );

        // Localize the script to make PHP data available to AJAX JS.  Define data in get_ajax_data().
        wp_localize_script( $this->plugin_name, 'ce_admin_ajax_data', $this->get_ajax_data() );

        // Add the JS Libraries and styles that WP needs.
        wp_enqueue_media();
        wp_enqueue_script( 'thickbox' );
        wp_enqueue_script( 'media-upload' );

	}


    /**
	 * Adds the Event Materials meta box to the admin area.
     * Hook with: 'add_meta_boxes_tribe_events'.
	 *
	 * @since    1.0.0
	 */
	public function add_materials_meta_box() {

        //Set the $post_id variable for later AJAX reference.
        $this->post_id = get_the_ID();

        // Do the metabox.
        add_meta_box(   'event-materials', // CSS ID
                        "Event Materials", // Display Title
                        array( $this, 'build_meta_box' ), // Callback to build HTML
                        '', // Slug
                        'normal', // Context
                        'high' //Priority
        );

    }

    /**
     * Builds the HTML content of the Event Materials admin area meta box.
     *
     * @since    1.0.0
     */
    public function build_meta_box() {

        $post_id = $this->post_id; // Formerly get_post().

        // Get all the metadata for the post.
        $event_meta = get_post_meta( $post_id );

        // Build the metabox HTML.
        ob_start();
        ?>

            <table id="event-materials-table" >
                <thead >
                    <tr id='no-materials-selected'
                        <?php if ( isset( $event_meta[ "_event_materials" ] ) && $event_meta[ "_event_materials" ][0] !== 'empty' ) {
                            $has_event_meta = true;
                            echo ' style="display: none;" ' ;
                        } else {
                            $has_event_meta = false;
                        } ?> >
                        <td>No materials selected.</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <input id='event-materials-info-empty' type='hidden' name='event_materials' value='empty'/>
                    <tr id="event-materials-headings" <?php  if ( ! $has_event_meta ) { echo ' style="display: none;" '; } ?> >
                        <td>Title <i>(click to edit)</i></td>
                        <td>Description for Users</td>
                        <td>Link</td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    //Get the IDs from the $event_meta entry.  They're serialized inside an Array(1).
                    if ( isset( $event_meta[ "_event_materials" ] ) && $event_meta[ "_event_materials" ][0] !== 'empty' ) {

                        $event_materials_array = unserialize( $event_meta[ "_event_materials" ][0] );

                        // var_dump( $event_materials_array );

                        // For each material selection, get its Title and URL,
                        // and build a row to hold the data along with a Delete link.
                        for ( $i = 0 ; $i < count( $event_materials_array ) ; $i++ ) {

                            $event_materials = $event_materials_array[$i];
                            ?>
                            <tr id="event-materials-row-<?php echo $i; ?>" class="event-materials-row">
                                <td class="event-materials-title">
                                    <input  class="event_materials" name="event_materials[<?php echo $i; ?>][title]" type="text" value="<?php echo esc_attr( $event_materials[ 'title' ] ); ?>" />
                                </td>

                                <td class="event-materials-description">
                                    <input  class="event_materials" name="event_materials[<?php echo $i; ?>][description]" type="textarea" autocomplete="off" maxlength="400" placeholder="Enter text here" value="<?php echo esc_attr( $event_materials[ 'description' ] ); ?>" />
                                </td>

                                <td class="event-materials-url">
                                    <a href="<?php echo $event_materials[ 'url' ]; ?>" target="_blank">URL </a>
                                    <input  class="event_materials" name="event_materials[<?php echo $i; ?>][url]" type="hidden" value="<?php echo esc_url( $event_materials[ 'url' ] ); ?>"   />
                                </td>

                                <td class="event-materials-delete"><a class="button" href="#">Delete </a></td>

                                <input class="event-materials-id event_materials" name="event_materials[<?php echo $i; ?>][id]" type="hidden" value="<?php echo esc_attr( $event_materials[ 'id' ] ); ?>"   />
                            </tr>

                            <?php
                        } // End for loop.

                    } // End if ( isset( $event_meta[ "_event_materials" ] ) && $event_meta[ "_event_materials" ][0] !== 'empty'  )
                    ?>

                </tbody>
            </table>

        <!-- The add media link -->
        <div class="hide-if-no-js">
            <div id="event-materials-upload" >
                <a class="button tribe-button-icon tribe-button-icon-plus" href="#" >
                    <?php _e('Add Materials') ?>
                </a>
            </div>

            <div id="event-materials-save-result">
                <span style="display: none;"></span>
            </div>

            <div id="event-materials-save">
                <a class="button button-primary" >
                    <?php _e('Save Materials') ?>
                </a>
            </div>
        </div>

        <?php
        $output = ob_get_clean();

        echo $output;
    }


    /**
	 * Saves the Event Materials meta box content.
	 *
	 * @since    1.0.0
	 */
	public function save_meta_box() {
        //echo "<p>Saving metabox......................................</p>";

        $post_id = get_the_ID();

        // Check user capabilities.
        if ( !current_user_can( 'edit_post' , $post_id ) ) {
            return;
        }

        // Store the values of our hidden materials inputs.
        if ( isset( $_POST[ 'event_materials' ] ) ) {
            // Takes post ID, db meta_key, and new db meta_value.
            update_post_meta( $post_id , '_event_materials' , $_POST['event_materials']  );
        }

    }


    /* ***************** START AJAX METHODS ***************** */

    /**
	 * Retrieves the AJAX data for the admin side of the plugin.
     *
     * Set array values manually.
     * Called in wp_localize_script() to pass data to AJAX JS.
	 *
	 * @since    1.0.0
	 */
	public function get_ajax_data() {

        //$this->ajax_data[ 'ajax_url' ] = admin_url( 'admin-ajax.php' ); // Unnecessary in admin area.
        $this->ajax_data[ 'ajax_nonce' ] = wp_create_nonce( 'ce_admin_ajax_nonce' ); // Gets checked in event_mats_ajax_save().
        // Add key => value pairs here.

        return $this->ajax_data;

    }


    /**
     * AJAX to save the Event Materals metabox.
     *
     * @since    1.0.0
     */
    public function event_mats_ajax_save() {

        check_ajax_referer( 'ce_admin_ajax_nonce', 'ajax_nonce' ); // Dies if false.

        // Get the post ID:
        $url = wp_get_referer();

        $query = parse_url( $url, PHP_URL_QUERY );

        $q_array = array();
        parse_str( $query, $q_array );

        if ( isset( $q_array[ 'post' ] ) && is_numeric( $q_array[ 'post' ] ) && isset( $q_array[ 'action' ] ) && $q_array[ 'action' ] === 'edit' ) {

            if ( $id = intval( $q_array[ 'post' ] ) ) {

                $post_id = $id;

            }

        }

        $updated = false;

        if ( isset( $_POST[ 'event_materials' ] ) ) {

            // Decode the string passed by AJAX thru $_POST :
            parse_str( $_POST['event_materials'], $mats_array);

            $event_materials = $mats_array['event_materials'];
            //
            // For testing:
            //var_dump( $event_materials );
            //

            // Takes post ID, db meta_key, and new db meta_value.
            // Returns true on success, false on failure, meta_id of new meta if DNE.
            $updated = update_post_meta( $post_id , '_event_materials', $event_materials );

        }

        $time = new DateTime();

        $time->setTimeZone( new DateTimeZone('America/Los_Angeles') );

        $timestamp = $time->format( 'g:i a' );

        echo $output = ( $updated !== false ) ? "Saved at {$timestamp}." : "Sorry, nothing was saved.";

        wp_die();

    }


    /**
	 * Saves the Event Materials meta box content.
	 *
	 * @since    1.0.0
	 */
	public function event_mats_save_metabox() {

        $post_id = $this->post_id; // Formerly get_the_ID()

        // Check user capabilities.
        if ( !current_user_can( 'edit_post' , $post_id ) ) {
            return false;
        }


        // Store the values of our hidden materials inputs. Serialized by jQ!
        if ( isset( $_POST[ 'event_materials' ] ) ) {

            // Get the string passed by AJAX thru $_POST :
            $materials = $_POST['event_materials'] ;

            // For testing:
            return $materials;

            // Takes post ID, db meta_key, and new db meta_value.
            //$updated = update_post_meta( $post_id , '_event_materials', $materials );

            //return ( $updated !== false );

        }
    }

    /* ***************** END AJAX METHODS ***************** */



    /**
     * Sets the '_stock' meta_key's meta_value equal to the
     * '_tribe_ticket_capacity' meta_value.  Necessary to manage a Tribe bug.
     *
     * Builds on the same metabox save functionality as $this->save_meta_box().
     * Runs on WP's 'save_post' action.
     *
     * @return   $stock_set     String containing success/error message.
     * @since    1.0.0
     */
    public function set_tribe_stock_to_capacity() {

        $post_id = get_the_ID();

        global $wpdb;

        // Check user capabilities and avoid autosave.
        if ( !current_user_can( 'edit_post' , $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        // Run a SQL query to set the stock equal to capacity for those ids.
        if ( isset( $_POST['event_ticket_types'] ) ) {

            $ticket_types = $_POST['event_ticket_types'];

            foreach( $ticket_types as $ticket_type ) {

                // Get the ticket capacity for the current type:
                $capacity = get_post_meta( $ticket_type, '_tribe_ticket_capacity', true );

                // Set the stock equal to the capacity for the current type
                $result = update_post_meta( $ticket_type, '_stock', $capacity );

                // Adds a 'stock_equalized' meta_value to verify activity.
                if ( $result == false ) {
                    update_post_meta( $ticket_type , '_stock_equalized' , 'no'  ); //
                } else {
                    update_post_meta( $ticket_type , '_stock_equalized' , 'yes'  );
                }

            } // END OF: foreach( $ticket_types as $ticket_type )

        } // END OF: if ( isset( $_POST['event_ticket_types'] ) )

    }


    /**
     * Wrapper for $this->fix_moved_tribe_ticket_counts().
     * Called on admin_notices action.  Checks for post_type = tribe_events.
     *
     * @since    1.0.0
     */
    public function do_moved_ticket_fix() {

        // echo "<div class='error fade'><p></p></div>";  <--Template.

        // The Attendees page passes the post_type via GET instead of the $post object:
        $post_type = isset( $_GET['post_type'] )  ?  $_GET['post_type']  :  get_post_type();

        if ( $post_type === 'tribe_events' ) {

            //echo "<p>Post type is {$post_type}.</p>";
            //echo "<p>Running fix_moved_tribe_ticket_counts(). Stand by.</p>";
            $fix_result = $this->fix_moved_tribe_ticket_counts();

            /*
            if(  is_admin() && sizeof( $fix_result ) > 0 ) {

                echo "<p>Ran fix_moved_tribe_ticket_counts().</p>";
                echo "<p>Results:<p><pre>";
                var_dump( $fix_result );
                echo "</pre>";

            }
            */

        }

    }


    /**
     * When a Tribe Ticket is moved from one type or event to another,
     * the plugin doesn't alter the ticket type counts.  This function does that.
     *
     * @return   $fix_result     An array of error and success messages.
     * @since    1.0.0
     */
    private function fix_moved_tribe_ticket_counts() {

        global $wpdb;

        // Get the event ID for the current post.
        $event_id = isset( $_GET['event_id'] )  ?  $_GET['event_id']  :  get_the_ID();

        $fix_result = array();

        // Get the rsvp_ids for this event.
        $rsvp_ids = $wpdb->get_col( $wpdb->prepare(
            "
            SELECT  post_id
            FROM    $wpdb->postmeta
            WHERE   meta_key = %s
            AND     meta_value = %s
            ",

            '_tribe_rsvp_event',
            $event_id
            )
        );

        if ( sizeof( $rsvp_ids ) === 0  ) {

            return $fix_result[] = "rsvp_ids returned no IDs.";

        } else {

            //$fix_result[] = "The following rsvp_ids are associated with this post:";
            //$fix_result[] = $rsvp_ids;
        }

        // Iterate across the rsvp_ids.
        foreach( $rsvp_ids as $i => $rsvp_id ) {

            // Check to see if the ticket has a history.
            if ( metadata_exists( 'post', $rsvp_id , '_tribe_post_history'  ) ) {

                // If it has a history, we need to:
                // - Check to see if the meta_values of each '_fixed_history_meta_id' field correspond to the meta_ids of the '_tribe_post_history' fields.  This is iterative.

                // How will we compare the meta_ids?
                $rsvp_history_meta_ids = $wpdb->get_col( $wpdb->prepare(
                    "
                    SELECT  meta_id
                    FROM    $wpdb->postmeta
                    WHERE   meta_key = %s
                    AND     post_id = %s
                    ",

                    '_tribe_post_history',
                    $rsvp_id // It's a string, right?
                    )
                );

                if ( sizeof( $rsvp_history_meta_ids ) === 0  ) {

                    $fix_result[] = "No '_tribe_post_history' meta_ids retrieved for post_id={$rsvp_id}.";

                }

                // Get the meta_ids (stored as meta values) of the history entries that have already been fixed:
                $rsvp_fixed_meta_ids = get_post_meta( $rsvp_id, '_fixed_history_meta_id' );

                if ( sizeof( $rsvp_fixed_meta_ids ) === 0  ) {

                    $fix_result[] = "No '_fixed_history_meta_id' meta_values retrieved for post_id={$rsvp_id}.";

                }

                // array_diff will find all values in the first array that are absent in the second.
                $unfixed_meta_ids = array_diff( $rsvp_history_meta_ids, $rsvp_fixed_meta_ids );

                if( sizeof( $unfixed_meta_ids ) > 0 ) {
                    $fix_result[] = "The following meta_ids will be fixed for post_id={$rsvp_id}:";
                    $fix_result[] = $unfixed_meta_ids;
                }

                // Then we get the meta_values for those meta_ids and parse them for the FROM and TO post_ids.
                 foreach( $unfixed_meta_ids as $unfixed_meta_id ) {

                     $history = $wpdb->get_col( $wpdb->prepare( // It's a string, right?
                         "
                         SELECT  meta_value
                         FROM    $wpdb->postmeta
                         WHERE   meta_id = %s
                         ",

                         $unfixed_meta_id // A string, right?
                         )
                     );

                     // Convert history to a string.
                     $history = $history[0];

                     if ( sizeof( $history ) === 0  ) {

                         $fix_result[] = "No history retrieved for meta_id={$unfixed_meta_id}.";

                     } else {

                         $fix_result[] = "meta_id={$unfixed_meta_id} had the post_history:";
                         $fix_result[] = esc_html($history);
                     }

                     // Get the TO product ID:
                     $to_startpos = strpos( $history, ';p=' ) + 3; // Search for the characters that precede the post_id.;
                     $to_endpos = strpos( $history, "\\", $to_startpos + 1 );
                     $fix_result[] = "First incidence of ';p=' in history string is at position={$to_startpos}.  Next backslash occurs at position={$to_endpos}.";

                     $to_product_id = substr( $history, $to_startpos, ( $to_endpos - $to_startpos ) );
                     $fix_result[] = "ID at position={$to_startpos} is {$to_product_id}.";


                     // Get the FROM product ID:
                     $from_startpos = strpos( $history, ';p=', $to_endpos + 1 ) + 3; // Search for the characters that precede the 2nd post_id.;
                     $from_endpos = strpos( $history, "\\", $from_startpos + 1 );
                     $fix_result[] = "Second incidence of ';p=' in history string is at position={$from_startpos}.  Next backslash occurs at position={$from_endpos}.";

                     $from_product_id = substr( $history, $from_startpos, ( $from_endpos - $from_startpos ) );
                     $fix_result[] = "ID at position={$from_startpos} is {$from_product_id}.";


                     // Now we fix the 'total_sales' meta_values for those two product_ids.
                     $to_value = get_post_meta( $to_product_id, 'total_sales', true );
                     $to_success = update_post_meta( $to_product_id, 'total_sales', $to_value + 1 );

                     if ( $to_success ) {

                         $fix_result[] = "TO 'total_sales' incremented for post_id={$to_product_id}.";

                     } else {

                         $fix_result[] = "TO 'total_sales' failed to increment for post_id={$to_product_id}.";

                     }

                     $from_value = get_post_meta( $from_product_id, 'total_sales', true );
                     $from_success = update_post_meta( $from_product_id, 'total_sales', $from_value - 1 );

                      if ( $from_success ) {

                          $fix_result[] = "FROM 'total_sales' incremented for post_id={$from_product_id}.";

                      } else {

                          $fix_result[] = "FROM 'total_sales' failed to increment for post_id={$from_product_id}.";

                      }

                      if ( $to_success && $from_success ) {
                          $fixed_meta_field_added = add_post_meta( $rsvp_id, '_fixed_history_meta_id', $unfixed_meta_id );
                      }

                 } // END OF:  foreach($unfixed_meta_ids as $unfixed_meta_id )

            }  else {

                // If no history, delete the matching element from the array and reindex it.
                unset( $rsvp_ids[$i] );
                $rsvp_ids = array_values( $rsvp_ids ); // TODO: Will foreach() skip the next index because it gets set to the index just used?

                //$fix_result[] = "No metadata exists with key '_tribe_post_history' for post_id={$rsvp_id}.";

            } // END OF:  if ( metadata_exists( 'post', $rsvp_id , '_tribe_post_history'  ) )

        } // END OF:  foreach( $rsvp_ids as $i => $rsvp_id )

        return $fix_result;
    }



    /**
     * NOTE: NOT YET IMPLEMENTED.
     * When a Tribe Ticket is canceled, the first waitlist ticket should get moved.
     *
     * @param    $rsvp_id    The post_id of the particular RSVP that was deleted.
     * @return   $?????      Some stuff??
     * @since    1.0.0
     */
    private function do_waitlist_fifo( $rsvp_id ) {

        global $wpdb;

        /* Pseudo-code: */
        /*
        - Detect cancellation: done on frontend, not on backend?
            - Could we just do a total_sales comparison to determine if there's an open spot?
                We'd need to check to see if stock - total_sales = count of deleted tickets. <- How do we track these?
                That would indicate that the just-deleted tix were at the "top of the pile."

            - Is there a Tribe action we can hook the function to?
                - Search src/functions for do_action() and apply_filters() calls.

        */
        // Call do_waitlist_fifo() in our public AJAX ticket cancellation.
        // Where do we get the ticket_id???  Does it get passed in during AJAX?
            // Gets set as a PROPERTY of CHSIE_Events_Public() during uer_validate_rsvp_deletion.
            //  If we call this after the deletion, we can use that rsvp_id (and event_id/product_id, too).

        /*
        - We need a way to pair the Waitlist tickets with their counterparts!
            - Get event_id -> get all product_ids -> get post_title -> search for "waitlist" -> within those, search for terms including portions of string in old ticket post_title...?  Blerg.  What if they share more than one term?  Just document it--put in a metabox?!?
            - OR... we could begin adding a new postmeta to rsvps indicating that they are/are not waitlists... for particular other rsvps...?  Yeah.  Not appealing.
            - DO THIS: JS to place instructions on the Tribe Tickets metabox: ROLENAME or ROLENAME WAITLIST.  That format will be easy to parse.
        */

        /*
        - Get all post_ids that have that waitlist product_id.
        - Put them in order of post_id.
        - Start with the lowest ticket post_id and iterate over n = number of deleted rsvps. <- How do we track these?
        */

        /*
        - Components of Move Ticket:
            1. Change the product_id to the new ticket type.
            2. Decrement old product total_sales, Increment new product total_sales.
            3. Alter _transient in event table.
            4. Create new _tribe_post_history for that ticket.  Needs a checksum!
        */


        // Get the event ID for the current post.
        $event_id = isset( $_GET['event_id'] )  ?  $_GET['event_id']  :  get_the_ID();

        // Get the rsvp_ids for this event.
        $rsvp_ids = $wpdb->get_col( $wpdb->prepare(
            "
            SELECT  post_id
            FROM    $wpdb->postmeta
            WHERE   meta_key = %s
            AND     meta_value = %s
            ",

            '_tribe_rsvp_event',
            $event_id
            )
        );


    }


}
