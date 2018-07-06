<?php

/**
 * The file that defines the core plugin class.
 *
 * Definition includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      1.0.0
 *
 * @package    CHSIE_Events
 * @subpackage CHSIE_Events/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    CHSIE_Events
 * @subpackage CHSIE_Events/includes
 * @author     Ben Hoverter <benhoverter@gmail.com>
 */
class CHSIE_Events {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      CHSIE_Events_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

    /**
	 * The instance of the DB Functions class, passed as a param to Admin and Public.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      object    $plugin_db_functions    The object instance to hold
     *                                             the DB functions for admin and public.
	 */
	protected $plugin_db_functions;


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		if ( defined( 'CHSIE_EVENTS_VERSION' ) ) {
			$this->version = CHSIE_EVENTS_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->plugin_name = 'CHSIE Events';

		$this->load_dependencies();   // Requires files and creates instance of loader.
		$this->set_locale();          // Creates instance of i18n class and hooks its load function to "plugins_loaded".

        $this->define_db_functions();    // Creates an instance of the db_functions class for use of admin and public classes.

		$this->define_admin_hooks();  // Creates an instance of the admin class and hooks its methods in.
		$this->define_public_hooks(); // Creates an instance of the public class and hooks its methods in.
        $this->define_settings_hooks(); // Creates an instance of the admin settings class and hooks its methods in.

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - CHSIE_Events_Loader. Orchestrates the hooks of the plugin.
	 * - CHSIE_Events_i18n. Defines internationalization functionality.
     * - CHSIE_Events_DB_Functions. Defines shared database interface methods.
	 * - CHSIE_Events_Admin. Defines all hooks for the non-Settings admin area.
     * - CHSIE_Events_Settings. Initializes WeDevs API and defines plugin Settings.
	 * - CHSIE_Events_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-chsie-events-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-chsie-events-i18n.php';

		/**
		 * The class responsible for defining shared DB functions.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-chsie-events-db-functions.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-chsie-events-admin.php';

        /**
         * The class responsible for defining all settings and menu options in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-chsie-events-settings.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-chsie-events-public.php';

		$this->loader = new CHSIE_Events_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the CHSIE_Events_i18n class in order to set the domain and to
     * register the hook with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new CHSIE_Events_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}


    /**
	 * Instantiate the class defining the shared database interface methods.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_db_functions() {

		$this->plugin_db_functions = new CHSIE_Events_DB_Functions( $this->get_plugin_name(), $this->get_version() );

    }


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new CHSIE_Events_Admin( $this->get_plugin_name(), $this->get_version(), $this->plugin_db_functions );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        // Functional admin hooks go here:
        $this->loader->add_action( 'add_meta_boxes_tribe_events', $plugin_admin, 'add_materials_meta_box' );
        $this->loader->add_action( 'save_post_tribe_events', $plugin_admin, 'save_meta_box' );
        $this->loader->add_action( 'save_post_tribe_events', $plugin_admin, 'set_tribe_stock_to_capacity' );

        // Runs on admin and public to ensure correct RSVP counts after "Move Ticket."
        $this->loader->add_action( 'admin_notices', $plugin_admin, 'do_moved_ticket_fix' );

        // Trying to get the ticket fix to happen on the frontend Event pages too.
        $this->loader->add_action( 'wp_footer', $plugin_admin, 'do_moved_ticket_fix' );

        // Admin AJAX hooks go here:
        $this->loader->add_action( 'wp_ajax_ce_admin', $plugin_admin, 'event_mats_ajax_save' );

	}


    /**
     * Register all of the hooks related to the admin Settings functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_settings_hooks() {

        $plugin_settings = new CHSIE_Events_Settings( $this->get_plugin_name(), $this->get_version() );

        // No need to enqueue scripts/styles here -- they are enqueued in the WeDevs_Settings_API class.

        // Standard functions that call dev-defined sections and menus in the Settings class:
        $this->loader->add_action( 'admin_menu', $plugin_settings, 'admin_menu' );
        $this->loader->add_action( 'admin_init', $plugin_settings, 'admin_init' );

    }

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

    $plugin_public = new CHSIE_Events_Public( $this->get_plugin_name(), $this->get_version(), $this->plugin_db_functions );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        // Functional public hooks go here:
        $this->loader->add_action( 'plugins_loaded', $plugin_public, 'get_current_user' );
        $this->loader->add_action( 'wp_footer', $plugin_public, 'echo_user_data' );

        // Shortcode hooks go here:
        add_shortcode( 'event-materials', array( $plugin_public, 'event_materials_shortcode' ) );
        add_shortcode( 'user-event-reservations', array( $plugin_public, 'user_event_reservations_shortcode' ) );

        // Public AJAX hooks go here:
        $this->loader->add_action( 'wp_ajax_ce_public', $plugin_public, 'uer_ajax_update' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
?>
