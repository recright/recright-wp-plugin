<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.recright.com/
 * @since      1.0.0
 *
 * @package    Recright_Feed
 * @subpackage Recright_Feed/includes
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
 * @package    Recright_Feed
 * @subpackage Recright_Feed/includes
 * @author     Recright <support@recright.com>
 */
class Recright_Feed {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Recright_Feed_Loader    $loader    Maintains and registers all hooks for the plugin.
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
	 * Cache
	 */
	public $cache;

	/**
	 * Public alias for plugin_name
	 */
	public $name;

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
		if ( defined( 'RECRIGHT_FEED_VERSION' ) ) {
			$this->version = RECRIGHT_FEED_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = $this->name = RECRIGHT_FEED_NAME;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		$this->cache = [
			'path'	=> wp_upload_dir()['basedir'] . '/' . $this->name . '/',
			'file'	=> 'data.json',
			'feed'  => 'feed.log',
			'error'	=> 'error.log'
		];
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Recright_Feed_Loader. Orchestrates the hooks of the plugin.
	 * - Recright_Feed_i18n. Defines internationalization functionality.
	 * - Recright_Feed_Admin. Defines all hooks for the admin area.
	 * - Recright_Feed_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-recright-feed-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-recright-feed-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-recright-feed-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-recright-feed-public.php';

		$this->loader = new Recright_Feed_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Recright_Feed_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Recright_Feed_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Recright_Feed_Admin( $this );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'setup_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'initialize_settings' );
		$this->loader->add_action( 'plugin_action_links_' . RECRIGHT_FEED_BASENAME, $plugin_admin, 'links' );
		$this->loader->add_action( 'updated_option', $plugin_admin, 'update_option', 10, 3 );
		$this->loader->add_action( RECRIGHT_FEED_HOOK, $plugin_admin, 'update_feed' );

		$this->loader->add_filter( 'cron_schedules', $plugin_admin, 'cron_schedules' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Recright_Feed_Public( $this );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles', 20 );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'init', $plugin_public, 'initialize' );

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
	 * @return    Recright_Feed_Loader    Orchestrates the hooks of the plugin.
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

	/**
	 * Clear error logs
	 */
	public function clear_logs($type) {
		if ( ! in_array($type, ['feed', 'error']) ) {
			return $this;
		}
		$filename = $this->cache['path'] . $this->cache[$type];
		if ( is_file($filename) ) {
			@file_put_contents($filename, '');
		}
		return $this;
	}

	/**
	 * Default settings
	 */
	public function default_settings( $type = 'general' ) {
		$settings = [];
		switch ($type) {
			case 'general': 
				$settings = [
					'feed_url' 						=> 'https://www.recright.com/api/v1/careers/recright-demo-career-page/feed',
					'template_loop' 			=> 
						"<div class=\"" . $this->name . "-item\">\n" . 
						"  <div class=\"" . $this->name . "-title\">\n" . 
						"    <strong><a href=\"[adUrl]\" target=\"_blank\">[title]</a></strong>\n" . 
						"    <span>[location]</span>\n" . 
						"  </div>\n" . 
						"  <small class=\"" . $this->name . "-item-date\">\n" . 
						"    <time>[publishTime:date]</time>\n" . 
						"    <span>-</span>\n" . 
						"    <time>[endTime:date]</time>\n" . 
						"  </small>\n" . 
						"</div>\n",
					'template_css'				=> ''
				];
				break;
			case 'advanced':
				$settings = [
					'date_format' 				=> '',
					'shortcode_tag'				=> $this->name,
					'template_container' 	=> 
						"<div class=\"" . $this->name . "\">\n" . 
						"  [feed]\n" . 
						"</div>\n",
					'cron_interval'				=> 'sixtimeshourly'
				];
				break;
		}
		return $this->merge_config( $settings, $type );
	}

	/**
	 * Get error logs
	 */
	public function get_error_logs() {
		return $this->get_logs('error');
	}

	/**
	 * Get feed
	 */
	public function get_feed() {
		$path = wp_upload_dir()['basedir'] . '/' . $this->name . '/';
		if ( ! is_dir($path) ) {
			mkdir( $path );
		}
		if ( ! is_dir($this->cache['path']) ) {
			mkdir( $this->cache['path'] );
		}
		$filename = $this->cache['path'] . $this->cache['file'];
		if ( ! is_file($filename) ) {
			try {
				$this->pull_feed();
			} catch (Exception $ex) {
				// Do nothing
			}
		}
		// If there's still no file, then return empty
		if ( ! is_file($filename) ) {
			return [];
		}
		$data = @json_decode( @file_get_contents($filename), true );
		if ( ! $data ) {
			return [];
		}
		return $data;
	}

	/**
	 * Get feed logs
	 */
	public function get_feed_logs() {
		return $this->get_logs('feed');
	}

	/**
	 * Get fields
	 */
	public function get_fields() {
		return [
			'title',
			'location',
			'description',
			'publishTime',
			'endTime',
			'adUrl',
			'applyUrl',
		];
	}

	/**
	 * Get logs
	 */
	public function get_logs($type) {
		$filename = $this->cache['path'] . $this->cache[$type];
		if ( ! is_file( $filename )) {
			return '';
		}
		$lines = [];
		$handle = @fopen( $filename, 'r' );
		while ( ! @feof( $handle) ) {
			if ( $line = @fgets($handle) ) {
				$lines[] = trim( $line );
			}
		}
		@fclose( $handle );
		return implode( PHP_EOL, array_slice( array_reverse( $lines ), 0, RECRIGHT_FEED_LOG_LIMIT ) );
	}

	/**
	 * Get settings
	 */
	public function get_settings( $type = 'general' ) {
		return get_option( 'recright_feed_' . $type . '_settings' );
	}

	/**
	 * Get single option
	 */
	public function get_setting( $name, $type = 'general', $default = null ) {
		$settings = $this->get_settings( $type );
		return isset( $settings[ $name ] ) ? $settings[ $name ] : $default;
	}

	/**
	 * Get error messages
	 */
	public function get_error_messages( $error ) {
		if ( $error instanceof WP_Error ) {
			return $error->get_error_messages();
		} else if ( $error instanceof Exception ) {
			return [$error->getMessage()];
		} else {
			return [];
		}
	}

	/**
	 * Count log lines
	 */
	protected function count_log_lines( $file ) {
		$count = 0;
		$handle = @fopen( $this->cache['path'] . $file, 'r' );
		while ( ! @feof( $handle ) ) {
			if ( trim( @fgets( $handle ) ) ) {
				$count++;
			}
		}
		@fclose( $handle );
		return $count;
	}

	/**
	 * Log to file
	 */
	protected function log( $file, $message, $prefix = false ) {
		if ( ! is_dir($this->cache['path']) ) {
			mkdir($this->cache['path']);
		}
		if ( $message ) {
			if ( $prefix === false ) {
				$prefix = '[' . date_i18n('Y-m-d H:i:s') . '] ';
			}
			@file_put_contents( $this->cache['path'] . $file, trim($prefix . $message) . PHP_EOL, FILE_APPEND);
			$count = $this->count_log_lines( $file );
			if ( $count > ( RECRIGHT_FEED_LOG_LIMIT * 2 ) ) {
				$this->trim_log( $file, $count - RECRIGHT_FEED_LOG_LIMIT );
			}
		}
		return $this;
	}

	/**
	 * Log error
	 */
	public function log_error( $error ) {
		if ( $messages = $this->get_error_messages( $error ) ) {
			$this->log( $this->cache['error'], implode(PHP_EOL, array_map(function( $message ) {
				return '[' . date_i18n('Y-m-d H:i:s') . '] ' . $message;
			}, $messages)), '' );
		}
		return $error;
	}

	/**
	 * Log feed
	 */
	public function log_feed( $message ) {
		return $this->log( $this->cache['feed'], $message );
	}

	/**
	 * Merge config
	 */
	protected function merge_config( $settings, $type ) {
		if ( is_file(RECRIGHT_FEED_BASEPATH . 'config.json') ) {
			$json = @json_decode( @file_get_contents(RECRIGHT_FEED_BASEPATH . 'config.json'), true );
			if ( isset( $json[$type] ) && is_array( $json[$type] ) ) {
				$settings = array_merge( $settings, $json[$type] );
			}
		}
		return $settings;
	}

	/**
	 * Pull feed
	 */
	public function pull_feed($suffix = '') {
		$feed_url = $this->get_setting( 'feed_url' );
		if ( $feed_url === null ) {
			$settings = $this->default_settings();
			$feed_url = $settings['feed_url'];
		}
		$response = wp_remote_get( $feed_url );
		if ( $response instanceof WP_Error ) {
			return $this->log_error( $response );
		} else {
			$status = wp_remote_retrieve_response_code( $response );
			$headers = wp_remote_retrieve_headers( $response );
			$body = wp_remote_retrieve_body( $response );
			if ( $status !== 200 ) {
				return $this->log_error( new Exception( 'Error retrieving feed. Status: ' . $status ) );
			} 
			$type = explode(';', $headers->offsetGet('content-type'))[0];
			if ( $type !== 'application/json' ) {
				return $this->log_error( new Exception( 'Invalid feed content type: ' . $type ) );
			}
			$filename = $this->cache['path'] . $this->cache['file'];
			@file_put_contents( $filename, json_encode( json_decode( $body ), JSON_PRETTY_PRINT ) );
			$this->log_feed( 'Feed cache updated' . ($suffix ? (' ' . $suffix) : '') );
		}
		return $this;
	}

	/**
	 * Trim file
	 */
	protected function trim_log( $file, $start ) {
		$lines = [];
		$count = 0;
		$handle = @fopen( $this->cache['path'] . $file, 'r' );
		while ( ! @feof($handle) ) {
			if ( $line = trim( @fgets( $handle ) ) ) {
				if ( $count >= $start ) {
					$lines[] = $line;
				}
				$count++;
			}
		}
		@fclose( $handle );
		@file_put_contents( $this->cache['path'] . $file, implode(PHP_EOL, $lines) . PHP_EOL );
	}

	/**
	 * Update cron interval
	 */
	public function update_cron_interval($interval = null) {
		if ( $interval === null ) {
			$interval = $this->get_setting( 'cron_interval', 'advanced' );
		}
		if ( $interval !== null ) {
			wp_clear_scheduled_hook( RECRIGHT_FEED_HOOK );
			if ( ! empty($interval) ) {
				wp_schedule_event( time(), $interval, RECRIGHT_FEED_HOOK );
			}
		}
		return $this;
	}

}
