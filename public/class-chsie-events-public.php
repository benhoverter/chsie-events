<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    CHSIE_Events
 * @subpackage CHSIE_Events/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and user data variables,
 * the functions to enqueue CSS/JS, and the functions needed
 * to support the 'event-materials' and 'user-event-reservations' shortcodes.
 *
 * @package    CHSIE_Events
 * @subpackage CHSIE_Events/public
 * @author     Ben Hoverter <ben.hoverter@gmail.com>
 */
class CHSIE_Events_Public {

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
	 * The current user's name.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $user    The current user's name.
	 */
     private $user_name;

    /**
    * The current user's email.
    *
    * @since    1.0.0
    * @access   private
    * @var      string    $user    The current user's email.
    */
    private $user_email;

    /**
    * The current user's ID.
    *
    * @since    1.0.0
    * @access   private
    * @var      string    $user    The current user's ID.
    */
    private $user_id;

    /**
    * The plugin's basic options from the Settings menu.
    *
    * @since    1.0.0
    * @access   private
    * @var      array    $basic_options    The plugin's basic options.
    */
    private $basic_options;

    /**
    * The event name being updated by the user in the [user-event-reservations] shortcode.
    *
    * @since    1.0.0
    * @access   private
    * @var      string    $uer_update_event    The name of the event being updated by the user.
    */
    private $uer_update_event;

    /**
    * The event id of the ticket being updated by the user in the [user-event-reservations] shortcode.
    *
    * @since    1.0.0
    * @access   private
    * @var      string    $uer_update_event_id    The id of the event being updated by the user.
    */
    private $uer_update_event_id;

    /**
    * The RSVP post_id being updated by the user in the [user-event-reservations] shortcode.
    *
    * @since    1.0.0
    * @access   private
    * @var      string    $uer_update_rsvp_id    The post_id of the event being updated by the user.
    */
    private $uer_update_rsvp_id;

    /**
    * The product id of the ticket type being updated by the user in the [user-event-reservations] shortcode.
    *
    * @since    1.0.0
    * @access   private
    * @var      string    $uer_update_event    The name of the event being updated by the user.
    */
    private $uer_update_product_id;

    /**
    * The data object for public AJAX functions.
    *
    * @since    1.0.0
    * @access   private
    * @var      associative array    $ajax_data    The data for public AJAX functions.
    */
    private $ajax_data;

    /**
    * The nonce for the AJAX call.  Must be available to uer_ajax_update().  OTHERS?
    *
    * @since    1.0.0
    * @access   private
    * @var      string    $ajax_nonce    The nonce for the AJAX call.
    */
    private $ajax_nonce;

    /**
    * The object instance of the CHSIE_Events_DB_Functions class.
    *
    * @since    1.0.0
    * @access   private
    * @var      object    $db_functions    The object instance of the CHSIE_Events_DB_Functions class,
    *                                      passed in during construction.
    */
    private $db_functions;


 	/**
 	 * Initialize the class and set properties.
 	 *
 	 * @since    1.0.0
 	 * @param      string    $plugin_name      The name of the plugin.
 	 * @param      string    $version          The version of this plugin.
 	 */

    public function __construct( $plugin_name, $version, $db_functions ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->db_functions = $db_functions;

        $this->basic_options = get_option( 'chsie_events_basic_settings' );
        // $this->get_current_user() runs in CHSIE_Events::define_public_hooks().

	}



    /**
	 * Gets the current user object and sets properties.
     * Must be called once the plugin has loaded.
	 *
	 * @since    1.0.0
	 */
    public function get_current_user() {

        $current_user = wp_get_current_user();

        if ( !( $current_user instanceof WP_User )) {
            return;
        }

        $this->user_name = $current_user->display_name;
        $this->user_email = $current_user->user_email;
        $this->user_id = (string) $current_user->ID; // Cast as a string to play nicely with other functions.

    }



	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

        // Variable to hold the URL path for enqueueing.
        $public_css_url_path = plugin_dir_url( __FILE__ ) . 'css/chsie-events-public.css';

        // Variable to hold the server path for filemtime() and versioning.
        $public_css_dir_path = plugin_dir_path( __FILE__ ) . 'css/chsie-events-public.css';

        // Register the style using an automatic and unique version based on modification time.
        wp_register_style( $this->plugin_name, $public_css_url_path , array() ,  filemtime( $public_css_dir_path ) , 'all' );

        // Enqueue the style.
        wp_enqueue_style( $this->plugin_name );

	}



	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

        // Variable to hold the URL path for enqueueing.
        $public_js_url_path = plugin_dir_url( __FILE__ ) . 'js/chsie-events-public.js';

        // Variable to hold the server path for filemtime() and versioning.
        $public_js_dir_path = plugin_dir_path( __FILE__ ) . 'js/chsie-events-public.js';

        // Register the script using an automatic and unique version based on modification time.
        wp_register_script( $this->plugin_name, $public_js_url_path , array( 'jquery' ) ,  filemtime( $public_js_dir_path ) , false );

        // Enqueue the scripts.
        wp_enqueue_script( $this->plugin_name );

        // **** //

        // Variable to hold the AJAX URL path for enqueueing.
        $public_ajax_url_path = plugin_dir_url( __FILE__ ) . 'js/chsie-events-public-ajax.js';

        // Variable to hold the AJAX server path for filemtime() and versioning.
        $public_ajax_dir_path = plugin_dir_path( __FILE__ ) . 'js/chsie-events-public-ajax.js';

        // Register the script using an automatic and unique version based on modification time.
        wp_register_script( 'ce-public-ajax-js', $public_ajax_url_path , array( 'jquery' ) ,  filemtime( $public_ajax_dir_path ) , false );

        // Enqueue the scripts.
        wp_enqueue_script( 'ce-public-ajax-js' );

        // Localize the script to make PHP data available to AJAX JS.  Define data in get_ajax_data().
        wp_localize_script( 'ce-public-ajax-js', 'ce_public_ajax_data', $this->get_ajax_data() );

	}



    /**
     * Creates the output of the [event-materials] shortcode and returns that output.
     *
     * @since    1.0.0
     */
    public function event_materials_shortcode() {

        // Get the post meta by ID.
        $post_id = get_the_ID();
        $event_meta = get_post_meta( $post_id );

        // Verify the post meta.
        if ( ! isset( $event_meta[ "_event_materials" ] ) ||  $event_meta[ "_event_materials" ][0] === 'empty'  ) {

            $has_event_meta = false;
            $event_materials_public_output = "<div> <p>This event has no materials associated with it.</p> </div>" ;

        } else {

            $has_event_meta = true;

            // Parse the serialized array stored in the meta_value array.
            $event_materials_array = unserialize( $event_meta[ "_event_materials" ][0] );

            // Start the HTML section.
            $event_materials_public_output = "<div id='event-materials' class='tribe-events-single-section tribe-events-event-meta primary tribe-clearfix'><div><h3 class='tribe-events-single-section-title'>Event Materials</h3></div>";

            // Iterate.  For each event material selection, get its Title, Description, and URL, and build HTML from the data.
            for ( $i = 0 ; $i < count( $event_materials_array ) ; $i++ ) {

                $event_materials = $event_materials_array[$i];

                $event_materials_public_output .= "<div class='event-materials-group tribe-events-meta-group tribe-events-meta-group-details'>
                                                <h4>
                                                    <a href='{$event_materials['url']}' target='_blank'> {$event_materials['title']} </a>
                                                </h4>
                                                <div>
                                                    <p> <strong>Description: </strong>{$event_materials['description']}  </p>
                                                </div>
                                            </div>" ;
            }

            // Close out the section div:
            $event_materials_public_output .= "</div>" ;

        } // End else.

        // Return the HTML in a variable for Shortcode.
        return $event_materials_public_output;
    }



    /**
     * Encodes user data into a hidden <div> for jQuery access.
     * <div> is removed immediately after jQuery access.
     *
     * @since    1.0.0
     */
    public function echo_user_data() {

        // jQuery will pull this data then erase this node:
        echo "<div style='display: none;' id='current-user' data-user='{$this->user_name}' data-mail='{$this->user_email}' data-id='{$this->user_id}'></div>";

        if ( array_key_exists( 'user_reg_page', $this->basic_options ) ) {

            $user_reg_page_id = $this->basic_options['user_reg_page'];

        } else {

             $user_reg_page_id = get_the_ID();

        }

        $user_reg_page_url = get_permalink( $user_reg_page_id );

        // jQuery pulls the User Registration Page from this node:
        echo "<div style='display: none;' id='user-reg-page-url' data-user-reg-page-url='{$user_reg_page_url}' data-page-id='{$user_reg_page_id}'></div>";
    }



    /**
     * Creates the output of the [user-event-reservations] shortcode and returns that output.
     *
     * @since    1.0.0
     */
    public function user_event_reservations_shortcode() {

        // Print the starter HTML for the heading, message, and table.
        if ( array_key_exists( 'user_reservations_title', $this->basic_options ) ) {

            $uer_title = $this->basic_options['user_reservations_title'];

        }  else {

             $uer_title = 'Your Reservations';

        }

        $output = "<div id='current-user-registration-info'>
                        <h2>{$uer_title}</h2>
                        <div id='uer-frame'>";

        $output .= $this->uer_do_user_rsvps(); // This is what AJAX replaces.

        $output .= "</div></div>";  // Close the HTML.

        return $output;

    }

    // ****************** START AJAX METHODS ****************** //

    /**
	 * Retrieves the AJAX data for the public side of the plugin.
     *
     * Set array values manually.
     * Called in wp_localize_script() to pass data to AJAX JS.
	 *
	 * @since    1.0.0
	 */
	public function get_ajax_data() {

        $this->ajax_data[ 'ajax_url' ] = admin_url( 'admin-ajax.php' ); // Necessary. No touching.
        $this->ajax_data[ 'ajax_nonce' ] = wp_create_nonce( 'ce_public_ajax_nonce' ); // Gets checked in uer_ajax_update().
        // Add key => value pairs here.

        return $this->ajax_data;

    }


    /**
     * AJAX for the [user-event-reservations] shortcode.
     *
     * @since    1.0.0
     */
    public function uer_ajax_update() {

        check_ajax_referer( 'ce_public_ajax_nonce', 'ajax_nonce' ); // Dies if false.

        // Run the shortcode logic again.
        echo $this->uer_do_user_rsvps();
        wp_die();

    }

    // ****************** END AJAX METHODS ****************** //


    /**
     * NEW: For the [user-event-reservations] shortcode.
     * Generates the HTML for the ERROR and TABLE.
     *
     * @return   $rsvp_output   The HTML string containing the contents of #uer-frame.
     * @since    1.0.0
     */

    private function uer_do_user_rsvps() { // TODO: WE DON'T NEED A FORM WITH AJAX!! KILL IT WITH FIRE!

        // Run the validation and delete/update logic on page load/AJAX call.
        // Has to happen first in order to set the uer_update_event array property.
        $update_error = $this->uer_do_rsvp_update();

        // --- For testing. -----
        /*
        echo "<p>update_result is :</p><pre>";
        print_r( $update_error . ", " . gettype( $update_error ) );
        echo "</pre>";
        */
        //-----------------------

        $rsvp_output = "";

        // Add an update message if appropriate.
        if ( isset( $this->uer_update_event ) ) {

            $rsvp_output .= "<p class='update-msg'>You have successfully updated your RSVP for the {$this->uer_update_event}.</p>" ;

        }

        // Formerly called in shortcode function:
        $user_rsvp_array = $this->db_functions->get_user_rsvps( $this->user_id );

        if ( empty( $user_rsvp_array ) ) {

            $rsvp_output .= "<p>You are not currently registered for any events.</p>";

        } else {

            $rsvp_output .= "<p>Please verify that all registration info is correct.</p>";

            $rsvp_output .= $update_error; // Error message here.  OK?

            $rsvp_output .= "<form id='uer-form' method='post' >
                                            <table><thead><tr>
                                                <td class='rsvp-id'>RSVP ID</td>
                                                <td class='rsvp-event'>Event</td>
                                                <td class='rsvp-event-id'>Event ID</td>
                                                <td class='rsvp-date'>Start Date</td>
                                                <td class='rsvp-name'>Registered Name</td>
                                                <td class='rsvp-email'>Email</td>
                                                <td class='rsvp-role'>Registered Role</td>
                                                <td class='rsvp-product-id'>Ticket-type ID</td>
                                                <td class='rsvp-code'>Registration Code</td>
                                                <td class='rsvp-update-delete'></td>
                                            </tr></thead>
                                            <tbody>"; // Preps next row for data.

            foreach( $user_rsvp_array as $user_rsvp_single ){

                $rsvp_output .=  "<tr>";

                $rsvp_output .=  "<td class='rsvp-id'>{$user_rsvp_single['rsvp_id']}</td>";

                $rsvp_output .=  "<td class='rsvp-event'>
                                        <a href='{$user_rsvp_single['event_url']}'
                                            target='_blank'>{$user_rsvp_single['event_title']}</a>
                                      </td>";

                $rsvp_output .=  "<td class='rsvp-event-id'>{$user_rsvp_single['event_id']}</td>";

                $rsvp_output .=  "<td class='rsvp-date'>{$user_rsvp_single['date']}</td>";

                $rsvp_output .=  "<td class='rsvp-name'>{$user_rsvp_single['fullname']}</td>";

                $rsvp_output .=  "<td class='rsvp-email'>{$user_rsvp_single['email']}</td>";

                $rsvp_output .=  "<td class='rsvp-role'>{$user_rsvp_single['product_type']}</td>";

                $rsvp_output .=  "<td class='rsvp-product-id'>{$user_rsvp_single['product_id']}</td>";

                $rsvp_output .=  "<td class='rsvp-code'>{$user_rsvp_single['code']}</td>";

                $rsvp_output .= "<td class='rsvp-update-delete'>
                                    <a class='delete-button' href='#' > Cancel </a>
                                </td>";
                                            // Deleted: "<button class='update-button' type='submit' >Update</button>
                                            // <input class='update-ticket' type='hidden'>" after <a>

                $rsvp_output .=  "</tr>";
            }

            // Finish the table and form.
            $rsvp_output .= "</tbody></table>";

            $user_rsvp_nonce = wp_create_nonce( 'update_user_rsvp' );

            $rsvp_output .= "<input id='uer_nonce' name='uer_nonce' value='$user_rsvp_nonce' type='hidden' >";

            $rsvp_output .= "</form>";

        }

        return $rsvp_output;
    }



    /**
     * For the [user-event-reservations] shortcode.
     * Validates the form data on user's Update or Delete action.
     *
     * @return   $update_error      String message about error or success.
     * @since    1.0.0
     */
    public function uer_do_rsvp_update() {

        // Nonce verification for security.
        if ( isset( $_POST[ 'uer_nonce' ] ) ) {

            $uer_nonce = $_POST[ 'uer_nonce' ];

            if( !wp_verify_nonce( $uer_nonce, 'update_user_rsvp' ) ) {

                return $update_error = "Sorry, you may have to log in again to do this action.";

            } else if ( isset( $_POST[ 'update_ticket' ] ) ) {

                // Grab and sanitize the security code sent by the Update button's hidden input:
                $input_sec_code = filter_input( INPUT_POST, "update_ticket", FILTER_SANITIZE_NUMBER_INT ); // NEW.

                //echo "input_sec_code is {$input_sec_code}";

                $delete_ticket = true;

                // TODO: Future functionality:
                // Do the updates if they're triggered.
                if ( isset( $_POST[ 'update_username' ] ) ) {

                    $delete_ticket = false;
                    echo "<p>Updating user name field.<p>";

                    // Update user name here.

                    $update_error = ""; // Message here.

                }

                if ( isset( $_POST[ 'update_email' ] ) ) {

                    $delete_ticket = false;
                    echo "<p>Updating user email field.<p>";

                    // Update user email here.

                    $update_error = ""; // Message here.

                }

                // Delete the RSVP if "Delete" was pressed.
                if ( $delete_ticket === true ) {

                    $input_sec_code = $_POST['update_ticket'];

                    unset( $_POST );

                    /*
                    echo "<p>input_sec_code var is: </p><pre>";
                    var_dump( $input_sec_code ) ;
                    echo "</pre><p>*********</p>";
                    */

                    // Validate the $input_sec_code, then delete the RSVP if it passes validation.
                    $validate_result = $this->uer_validate_rsvp_deletion( $input_sec_code );

                    $update_error = ( $validate_result === true )  ?  $this->uer_delete_rsvp()  :  $validate_result;

                }

            } else {

                $update_error = "<p>POST[update_ticket] not set.</p>";
                //$update_error = "";

            }

            return $update_error;

        } else {

            return $update_error = "";

        }


    }



    /**
     * For the [user-event-reservations] shortcode ticket delete function.
     * Validates a delete request for a single user's ticket for a single event.
     * Called within uer_do_rsvp_update().
     *
     * @param   $input_sec_code    The security code from the form submission event.
     * @return  $validate_result     A string message indicating error or success.
     * @since    1.0.0
     */
    private function uer_validate_rsvp_deletion( $input_sec_code ) {

        global $wpdb;

        if ( strlen( $input_sec_code) !== 10 || !is_string( $input_sec_code )  ) {
            return $validate_result = "<p>Delete validation code rejected: incorrect length.</p>";
        }

        // TODO: Check for UTF-8 and character whitelist here.

        // Get all the user's RSVPS.  Restricts delete to current user.
        $user_rsvp_array = $this->db_functions->get_user_rsvps( $this->user_id );

        // Iterate over user's RSVPs to get the event_id and ticket_id of the ticket to delete.
        foreach( $user_rsvp_array as $user_rsvp_single ) {

            // Check a single event's Security Code against the $input_sec_code passed to $_POST.
            $real_sec_code = $user_rsvp_single['code'];

            if ( $input_sec_code === $real_sec_code  ) {
                // echo "Security codes match in uer_delete_rsvp().";
                /*
                echo "<pre>";
                var_dump( $user_rsvp_single );
                echo "</pre>";
                */

                $this->uer_update_event = $user_rsvp_single['event_title']; // String.
                $this->uer_update_event_id = $user_rsvp_single['event_id'];  // String.
                $this->uer_update_rsvp_id = $user_rsvp_single['rsvp_id']; // Integer.
                $this->uer_update_product_id = $user_rsvp_single['product_id']; // String.

                // echo "<p>event_id is</p><pre>$event_id</pre>";
                // echo "<p>ticket_id is</p><pre>$rsvp_id</pre>";

                break; // Only one deletion allowed per form submission.
            }

        }

        return $validate_result = true;
    }



    /**
     * For the [user-event-reservations] shortcode ticket delete function.
     *
     * Deletes a single user's ticket for a single event. Called within uer_do_rsvp_update().
     *
     * @return  $delete_error     A string message indicating error or success.
     * @since    1.0.0
     */
    private function uer_delete_rsvp() {

        global $wpdb;

        $delete_error = "";

        $event_id = $this->uer_update_event_id;
        $rsvp_id = $this->uer_update_rsvp_id;
        $product_id = $this->uer_update_product_id;

        // Verify the $event_id is set.  If not, bail.
        if ( !isset( $event_id ) ) {
            return $delete_error = "<p>event_id is not set.</p>";
        }

        // Verify the $rsvp_id is set.  If not, bail.
        if ( !isset( $rsvp_id )  ) {
            return $delete_error = "<p>rsvp_id is not set.</p>";
        }

        // NEXT: Decrement the 'total_sales' for this $rsvp_id's $product_id (must come before ):
        $total_sales_down = $this->db_functions->decrement_total_sales( $rsvp_id ); // NEW. Returns (new value) or false.

        // FAILURE CHECK:
        if ( $total_sales_down === false ) {

            return $delete_error = "<p>Error: Ticket sales couldn't be updated.</p>";

        }

        // NEXT:  Use the $event_id and $rsvp_id to get the event's attendee data and modify it.
        $transient_updated = $this->db_functions->update_tribe_transient( $rsvp_id ); // NEW.  Returns true or false.

        // FAILURE CHECK:
        if ( $transient_updated === false ) {

            // echo "<p>Ticket sales couldn't be updated.</p>";
            return $delete_error = "<p>Error: DB transient couldn't be updated.</p>";

        }

        // NEXT: Delete the post and postmeta for the Deleted $rsvp_id.  'true' forces permanent delete (or it will retain post_meta.)
        $rsvp_post_delete = wp_delete_post( $rsvp_id, true );

        // FAILURE CHECK:
        if ( $rsvp_post_delete === false ) {

            return $delete_error = "<p>Sorry, there was an error in deleting your RSVP (#{$rsvp_id}). Cannot cancel RSVP.<p>";

        }

        //echo "<p>Successfully deleted RSVP #{$rsvp_id}.<p>";

        return $delete_error;

    }


}
