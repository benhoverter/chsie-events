<?php

/**
 * The CHSIE Events plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           CHSIE_Events
 *
 * @wordpress-plugin
 * Plugin Name:       CHSIE Events
 * Description:       Modifies the Events Calendar and Events Tickets plugins from Modern Tribe to provide custom functionality for the UW Center for Health Sciences Interprofessional Education, Research and Practice.
 * Version:           1.0.0
 * Author:            Ben Hoverter
 * Author URI:        http://benhoverter.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       chsie-events
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die( "Sorry, your script's no good here." );
}


/**
 * Currently plugin version 1.0.0.
 */
define( 'CHSIE_EVENTS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-chsie-events-activator.php
 */
function activate_chsie_events() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-chsie-events-activator.php';
	CHSIE_Events_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-chsie-events-deactivator.php
 */
function deactivate_chsie_events() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-chsie-events-deactivator.php';
	CHSIE_Events_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_chsie_events' );
register_deactivation_hook( __FILE__, 'deactivate_chsie_events' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-chsie-events.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_chsie_events() {

	$plugin = new CHSIE_Events();
	$plugin->run();

}
run_chsie_events();


?>
