<?php

/**
 *
 * @link              https://www.recright.com/
 * @since             1.0.2
 * @package           RecRight
 *
 * @wordpress-plugin
 * Plugin Name:       RecRight Jobs
 * Description:       RecRight Jobs plugin for Wordpress
 * Version:           1.0.2
 * Author:            RecRight
 * Author URI:        https://www.recright.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       recright-feed
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'RECRIGHT_FEED_VERSION', '1.0.2' );

/**
 * Name
 */
define( 'RECRIGHT_FEED_NAME', 'recright-feed' );

/**
 * Hook
 */
define( 'RECRIGHT_FEED_HOOK', str_replace('-', '_', RECRIGHT_FEED_NAME) . '_hook' );

/**
 * Base name
 */
define( 'RECRIGHT_FEED_BASENAME', plugin_basename(__FILE__) );

/**
 * Base path
 */
define( 'RECRIGHT_FEED_BASEPATH', plugin_dir_path(__FILE__) );

/**
 * Recright feed log limit
 * Limit the logs so it won't be too big to manage
 */
define( 'RECRIGHT_FEED_LOG_LIMIT', 5 );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-recright-feed-activator.php
 */
function activate_recright_feed() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-recright-feed-activator.php';
	Recright_Feed_Activator::activate( get_recright_feed() );
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-recright-feed-deactivator.php
 */
function deactivate_recright_feed() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-recright-feed-deactivator.php';
	Recright_Feed_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_recright_feed' );
register_deactivation_hook( __FILE__, 'deactivate_recright_feed' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-recright-feed.php';

/**
 * Get static recright feed
 */
function get_recright_feed() {
	static $feed;
	if ( isset( $feed ) ) {
		return $feed;
	}
	return $feed = new Recright_Feed();
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_recright_feed() {
	$feed = get_recright_feed();
	$feed->run();
}
run_recright_feed();
