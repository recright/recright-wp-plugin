<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.recright.com/
 * @since      1.0.0
 *
 * @package    Recright_Feed
 * @subpackage Recright_Feed/includes
 */

/**
 *
 * @since      1.0.0
 * @package    Recright_Feed
 * @subpackage Recright_Feed/includes
 * @author     Recright <support@recright.com>
 */
class Recright_Feed_Deactivator {

	/**
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		delete_option( 'recright_feed_general_settings' );
		delete_option( 'recright_feed_advanced_settings' );
		wp_clear_scheduled_hook( RECRIGHT_FEED_HOOK );
		static::deleteFiles();
	}

	/**
	 * Delete files
	 */
	protected static function deleteFiles() {
		$base = wp_upload_dir()['basedir'] . '/' . RECRIGHT_FEED_NAME . '/';
		$files = [
			'data.json',
			'error.log',
			'feed.log'
		];
		foreach ( $files as $file ) {
			$filepath = $base . $file;
			if ( is_file( $filepath ) ) {
				@wp_delete_file( $filepath );
			}
		}
	}

}
