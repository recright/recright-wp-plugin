<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.recright.com/
 * @since      1.0.0
 *
 * @package    Recright_Feed
 * @subpackage Recright_Feed/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Recright_Feed
 * @subpackage Recright_Feed/includes
 * @author     Recright <support@recright.com>
 */
class Recright_Feed_Activator {

	/**
	 *
	 * @since    1.0.0
	 */
	public static function activate($feed) {
		$settings = $feed->default_settings( 'advanced' );
		$feed->update_cron_interval( $settings['cron_interval'] );
	}

}
