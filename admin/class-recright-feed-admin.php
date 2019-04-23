<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.recright.com/
 * @since      1.0.0
 *
 * @package    Recright_Feed
 * @subpackage Recright_Feed/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Recright_Feed
 * @subpackage Recright_Feed/admin
 * @author     Recright <support@recright.com>
 */
class Recright_Feed_Admin {

	/**
	 * Recright Feed object
	 */
	private $feed;

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
	 * Persistent settings
	 */
	private $settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $feed ) {

		$this->feed 				= $feed;
		$this->plugin_name 	= $feed->get_plugin_name();
		$this->version 			= $feed->get_version();

	}

	/**
	 * Get settings
	 */
	protected function get_settings( $type = 'general' ) {
		return $this->feed->get_settings( $type );
	}

	/**
	 * Get single option
	 */
	protected function get_setting( $name, $type = 'general', $default = null ) {
		return $this->feed->get_setting( $name, $type, $default );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/recright-feed-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/recright-feed-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Cron schedules
	 */
	public function cron_schedules($schedules) {
	  $schedules['minutely'] = [
	    'interval' => 1 * 60,
	    'display' => __( 'Once every minute', $this->feed->name )
	  ];
	  $schedules['sixtimeshourly'] = [
	    'interval' => 10 * 60,
	    'display' => __( 'Once every 10 minutes', $this->feed->name )
	  ];
	  $schedules['twicehourly'] = [
	    'interval' => 30 * 60,
	    'display' => __( 'Once every 30 minutes', $this->feed->name )
	  ];
	  $schedules['weekly'] = [
	    'interval' => 7 * 24 * 60 * 60,
	    'display' => __( 'Once every week', $this->feed->name )
	  ];
	  return $schedules;
	}

	/**
	 * Display settings page
	 *
	 * @since 	1.0.0
	 */
	public function display() {
		$admin = $this;
		if ( isset($_GET['clear']) ) {
			$this->feed->clear_logs( $_GET['clear'] );
		}
		if ( isset($_GET['refresh']) && $_GET['refresh'] === '1' ) {
			$this->pull_feed_and_report( 'recright_feed_advanced_settings', 'manually' );
		}
		require_once 'partials/recright-feed-admin-display.php';
	}

	/**
	 * All settings
	 */
	public function display_settings( $args ) {
?>
<h2><?= ucwords(str_replace( '_', ' ', $args['id'] )); ?></h2>
<?php
	}

	/**
	 * Links
	 */
	public function links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( '/plugins.php?page=recright_feed' ) )  . '">Settings</a>' );
		return $links;
	}

	/**
	 * Initialize settings
	 */
	public function initialize_settings() {
		$types = ['general', 'advanced'];
		foreach ($types as $type) {
			if ( $this->get_settings( $type ) === false ) {
				add_option( 'recright_feed_' . $type . '_settings', $this->feed->default_settings( $type ) );
			}
			add_settings_section(
				$type . '_settings',
				null,
				array( $this, 'display_settings' ),	
				'recright_feed_' . $type . '_settings'	
			);
			register_setting(
				'recright_feed_' . $type . '_settings',
				'recright_feed_' . $type . '_settings'
			);
		}

		/**
		 * Add logs
		 */
		add_settings_section(
			'error_logs',
			null,
			array( $this, 'display_settings' ),
			'recright_feed_error_logs'
		);

		/**
		 * General settings
		 */

		add_settings_field(
			'feed_url',	
			__( 'Feed URL', $this->feed->name ), 
			array( $this, 'setting_text'), 
			'recright_feed_general_settings',
			'general_settings',	
			array(
				'feed_url',
				__( 'Enter feed URL from RecRight', $this->feed->name ),
				'general'
			)
		);

		add_settings_field(
			'template_loop',	
			__( 'Template Loop', $this->feed->name ), 
			array( $this, 'setting_textarea'), 
			'recright_feed_general_settings',
			'general_settings',	
			array(
				'template_loop',
				__( 'Enter template loop HTML', $this->feed->name ),
				'general',
				'loop-vars'
			)
		);

		add_settings_field(
			'template_css',	
			__( 'Template CSS', $this->feed->name ), 
			array( $this, 'setting_textarea'), 
			'recright_feed_general_settings',
			'general_settings',	
			array(
				'template_css',
				__( 'Enter template custom CSS. Leave empty to use default CSS', $this->feed->name ),
				'general'
			)
		);

		/**
		 * Advanced settings
		 */

		add_settings_field(
			'date_format',
			__( 'Date Format', $this->feed->name ),
			array( $this, 'setting_text' ),
			'recright_feed_advanced_settings',
			'advanced_settings',
			array(
				'date_format',
				__( 'Enter custom date format. Leave empty to use Wordpress default format', $this->feed->name ),
				'advanced',
				'date-format'
			)
		);

		add_settings_field(
			'shortcode_tag',
			__( 'Shortcode Tag', $this->feed->name ),
			array( $this, 'setting_text' ),
			'recright_feed_advanced_settings',
			'advanced_settings',
			array(
				'shortcode_tag',
				__( 'Enter custom shortcode tag', $this->feed->name ),
				'advanced'
			)
		);

		add_settings_field(
			'template_container',	
			__( 'Template Container', $this->feed->name ), 
			array( $this, 'setting_textarea' ), 
			'recright_feed_advanced_settings',
			'advanced_settings',	
			array(
				'template_container',
				__( 'Enter template container HTML. Use [feed] variable to insert feed', $this->feed->name ),
				'advanced'
			)
		);

		add_settings_field(
			'cron_interval',
			__( 'Cron Interval', $this->feed->name ),
			array( $this, 'setting_select' ),
			'recright_feed_advanced_settings',
			'advanced_settings',
			array(
				'cron_interval',
				__( 'Select how often new jobs will be fetched.', $this->feed->name ),
				'advanced',
				'refresh-feed',
				[
					['', 'None'],
					['minutely', 'Once every minute'],
					['sixtimeshourly', 'Once every 10 minutes'],
					['twicehourly', 'Once every 30 minutes'],
					['hourly', 'Once every hour'],
					['twicedaily', 'Once every 12 hours'],
					['daily', 'Once every day'],
					['weekly', 'Once every week']
				]
			)
		);
	}

	/**
	 * Pull and report
	 */
	protected function pull_feed_and_report( $option, $suffix = '' ) {
		if ( $messages = $this->feed->get_error_messages( $this->feed->pull_feed($suffix) ) ) {
			foreach ( $messages as $message ) {
				add_settings_error( $option, 'pull_feed_error', __($message, $this->feed->name), 'error' );
			}
		} else {
			add_settings_error( $option, 'pull_feed_success', __('Feed successfully updated', $this->feed->name), 'updated' );
		}
	}

	/**
	 * Include partial
	 */
	public function require_partial( $args ) {
		if ( isset( $args[3] ) && $args[3] ) {
			$admin = $this;
			require_once dirname(__FILE__) . '/partials/recright-feed-admin-' . $args[3] . '.php';
		}
	}

	/**
	 * Select input
	 */
	public function setting_select( $args ) {
		$value = $this->get_setting( $args[0], $args[2] );
		$options = isset( $args[4] ) ? $args[4] : [];
?>
<label>
	<select class="recright-feed-<?= $args[0] ?>" name="recright_feed_<?= $args[2] ?>_settings[<?= $args[0] ?>]">
	<?php
	foreach ( $options as $option ) {
		?><option value="<?= esc_attr( $option[0] ) ?>"<?= ($value === $option[0]) ? ' selected' : '' ?>><?= esc_html( $option[1] ) ?></option>
		<?php
	}
	?>
	</select>
	<span><?= esc_html( $args[1] ) ?></span>
</label>
<?php
		$this->require_partial( $args );
	}

	/**
	 * Text
	 */
	public function setting_text( $args ) {
		$option = $this->get_setting( $args[0], $args[2] );
?>
<label>
	<input type="text" class="recright-feed-<?= $args[0] ?>" name="recright_feed_<?= $args[2] ?>_settings[<?= $args[0] ?>]" value="<?= esc_attr($option) ?>" />
	<span><?= esc_html( $args[1] ) ?></span>
</label>
<?php
		$this->require_partial( $args );
	}

	/**
	 * Text area
	 */
	public function setting_textarea( $args ) {
		$option = $this->get_setting( $args[0], $args[2] );
?>
<label>
	<textarea class="recright-feed-<?= $args[0] ?>" name="recright_feed_<?= $args[2] ?>_settings[<?= $args[0] ?>]"><?= esc_html( $option ) ?></textarea>
	<span><?= esc_html( $args[1] ) ?></span>
</label>
<?php
		$this->require_partial( $args );
	}

	/**
	 * Setup menu
	 */
	public function setup_menu() {
		add_plugins_page(
			'RecRight Jobs',
			'RecRight Jobs',
			'manage_options',
			'recright_feed',
			array( $this, 'display')
		);
	}

	/**
	 * Update feed via cron
	 */
	public function update_feed() {
		$this->feed->pull_feed( 'via cron' );
	}

	/**
	 * On update option
	 */
	public function update_option($option, $previous, $current) {
		switch ( $option ) {
			case 'recright_feed_general_settings':
				if ( isset( $current['feed_url'] ) &&
					(!isset( $previous['feed_url'] ) || ($previous['feed_url'] !== $current['feed_url']) ) ) {
					$this->pull_feed_and_report( $option );
				}
				break;
			case 'recright_feed_advanced_settings':
				// There's current value or the value for cron_interval has changed
				if ( isset( $current['cron_interval'] ) && 
					(!isset( $previous['cron_interval'] ) || ($previous['cron_interval'] !== $current['cron_interval']) ) ) {
					$this->feed->update_cron_interval( $current['cron_interval'] );
				}
				break;
		}
	}

}
