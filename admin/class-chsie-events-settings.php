<?php

/**
 * The WP admin settings functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    CHSIE_Events
 * @subpackage CHSIE_Events/admin/partials
 */

/**
 *
 * This class defines the plugin slug, as well as the options page, sections and fields.
 *
 * @package    CHSIE_Events
 * @subpackage CHSIE_Events/admin/partials
 * @author     Ben Hoverter <ben.hoverter@gmail.com> (modifier)
 * @author     Tareq Hasan, WeDevs Settings API creator
 */
class CHSIE_Events_Settings {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

    /**
     * The snake_case slug of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_slug    The snake_case slug of this plugin.
     */
    private $plugin_slug;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * The object instance of the WeDevs Settings API class.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $settings_api    The object instance of the WeDevs Settings API class.
     */
    private $settings_api;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
        $this->plugin_slug = $this->get_plugin_slug( $plugin_name );

		$this->version = $version;

        require_once plugin_dir_path( __FILE__ ) . 'partials/class-settings-api.php';

        $this->settings_api = new WeDevs_Settings_API;

	}

    /**
     * Generate the snake_case slug from the $plugin_name.
     *
     * @since    Custom addition for CHSIE Settings API boilerplate.
     * @author   Ben Hoverter
     */
    private function get_plugin_slug( $plugin_name ) {

        $plugin_slug = str_replace( array( ' ', '-' ), '_', strtolower( $plugin_name ) );

        return $plugin_slug;
    }

    /**
     * Set and Initialize the sections and fields defined in this class
     * by passing them to the API Class.
     *
     * @since    Custom addition for WeDevs Settings API.
     *
     */
    public function admin_init() {
		/**
         * An instance of this class should be passed as the second parameter
         * of the run() function defined in CHSIE_Events_Loader
         * as all of the hooks are defined in that particular class.
		 *
		 * The CHSIE_Events_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        // Pass this class' settings into the API Class.
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        // Initialize the settings in the API Class.
        $this->settings_api->admin_init();
    }

    /**
     * Add the Options pages and menu items in for all Settings API pages.
     * Additional pages can be generated with new calls to add_options_page().
     * $plugin_name and $plugin_slug are followed by customizable text.
     *
     * @since    Custom addition for WeDevs Settings API.
     *
     */
    public function admin_menu() {
        /**
         * An instance of this class should be passed as the second parameter
         * of the run() function defined in CHSIE_Events_Loader
         * as all of the hooks are defined in that particular class.
         *
         * The CHSIE_Events_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        add_options_page( $this->plugin_name . ' Settings', $this->plugin_name, 'manage_options', $this->plugin_slug . '_settings', array($this, 'plugin_page') );
    }


    /**
     * Defines the settings page sections in an associative array.
     *
     * Modify values and number of elements for your needs.
     * Altering names in get_settings_sections() requires matching alteration
     * in get_settings_fields().
     *
     * @since    Custom addition for WeDevs Settings API.
     *
     */
    private function get_settings_sections() {
        $sections = array(
            array(
                'id'    => $this->plugin_slug . '_basic_settings',
                'title' => __( 'Basic Settings', 'textdomain' )
            )
        );
        return $sections;
    }


    /**
     * Defines and returns all the settings fields.
     * Modify values and number of elements for your needs.
     *
     * @since   Custom addition for WeDevs Settings API.
     * @return  Array of settings fields.
     */
    private function get_settings_fields() {
        $settings_fields = array(
            $this->plugin_slug . '_basic_settings' => array(
                array(
                    'name'    => 'user_reg_page',
                    'label'   => __( 'User Reservations Page', 'textdomain' ),
                    'desc'    => __( 'This is the page to which the plugin will direct users for info about their event reservations.<br>You must place the [user-event-reservations] shortcode on that page as well.', 'textdomain' ),
                    'type'    => 'pages',
                    'default' => ''
                ),
                array(
                    'name'              => 'event_materials_title',
                    'label'             => __( 'Title for the Event Materials section', 'textdomain' ),
                    'desc'              => __( 'This is the text that will headline the section generated by the [event-materials] shortcode.', 'textdomain' ),
                    'type'              => 'text',
                    'default'           => 'Event Materials',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                array(
                    'name'              => 'user_reservations_title',
                    'label'             => __( 'Title for the User Reservations info section', 'textdomain' ),
                    'desc'              => __( 'This is the text that will headline the section generated by the [user-event-reservations] shortcode,<br>as well as the registration link section on the Event page.', 'textdomain' ),
                    'type'              => 'text',
                    'default'           => 'Your Reservations',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        );

        return $settings_fields;
    }


    /**
     * Callback function to generate HTML elements for Settings Page.
     * Required for add_options_page().
     *
     * Must be duplicated and made unique for add'l Options pages.
     *
     * @since    Custom addition for WeDevs Settings API.
     *
     */
    public function plugin_page() {
        echo '<div class="wrap">';
        echo '<h1>' . $this->plugin_name . ' Settings</h1>';

        settings_errors();

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }


    /**
     * Retrieves all pages on the site for use in the Settings API.
     * WP's get_pages() takes parameters to filter pages retrieved.
     *
     * @since    Custom addition for WeDevs Settings API.
     * @return   An array of page names indexed by ID.
     */
    private function get_pages() {
        $pages = get_pages();
        $pages_options = array();
        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }

}

?>
