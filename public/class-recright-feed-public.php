<?php

/**
 *
 * @link       https://www.recright.com/
 * @since      1.0.0
 *
 * @package    Recright_Feed
 * @subpackage Recright_Feed/public
 */

/**
 *
 * @package    Recright_Feed
 * @subpackage Recright_Feed/public
 * @author     Recright <support@recright.com>
 */
class Recright_Feed_Public {

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
	 * Settings
	 */
	private $settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
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
		if ( ! $this->settings ) {
			$this->settings = [];
		}
		if ( isset( $this->settings[$type] ) ) {
			return $this->settings[$type];
		}
		return $this->settings[$type] = $this->feed->get_settings( $type );
	}

	/**
	 * Get single option
	 */
	protected function get_setting( $name, $type = 'general', $default = null ) {
		return $this->feed->get_setting( $name, $type, $default );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		if ( $css = $this->getCSS() ) {
			$style_name = $this->feed->name . '-style';
			wp_register_style( $style_name, false );
			wp_enqueue_style( $style_name );
			wp_add_inline_style( $style_name, $css );
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		// Nothing
	}

	/**
	 * Apply filter
	 */
	protected function apply_filter($value, $filter, $param) {
		switch ($filter) {
			case 'date':
				if ( ! $value ) {
					return null;
				}
				if ( ! $param ) {
					$param = $this->get_setting('date_format', 'advanced') ?: get_option( 'date_format' );
				}
				return date_i18n($param ?: 'j.n.Y', strtotime($value));
			case 'safe':
				return esc_html($value);
		}
		return $value;
	}

	/**
	 * Default css
	 */
	public function defaultCSS() {
		return <<<CSS
.{$this->feed->name} {
	display: block;
	padding: 10px 0;
}
.{$this->feed->name}-item {
	padding: 5px 0;
}
.{$this->feed->name}-title {
	display: inline-block;
	width: 100%;
}
.{$this->feed->name}-title > *:first-child {
	float: left;
}
.{$this->feed->name}-title > *:last-child {
	float: right;
}
.{$this->feed->name}-item-date {
	font-size: 0.8rem;
	display: block;
	clear: both;
}
.{$this->feed->name} *:empty {
	display: none;
}
CSS;
	}

	/**
	 * Display shortcode
	 */
	public function display_feed() {
		$feed = $this->feed->get_feed();
		if ( $feed ) {
			$fields = $this->feed->get_fields();
			$general = $this->get_settings();
			$container = $this->get_setting( 'template_container', 'advanced' );
			$items = [];
			$feed = apply_filters( $this->feed->name . '-items', $feed, $this->feed );
			foreach ( $feed as $data ) {
				$item = $general['template_loop'];
				foreach ( $fields as $field ) {
					$item = $this->replace_item( $item, $field, isset( $data[$field] ) ? $data[$field] : null );
				}
				$items[] = $item;
			}
			return str_replace('[feed]', implode('', $items), $container);
		}
		// Return empty by default
		return '';
	}

	/**
	 * Get css
	 */
	protected function getCSS() {
		// For those who are wondering how to not output any CSS at all, 
		// just leave a whitespace character in the Template CSS input
		// If the CSS is empty, it will use the default CSS
		$css = $this->feed->get_setting( 'template_css' );
		if ( empty($css) ) {
			return $this->defaultCSS();
		}
		// If the trimmed CSS is still empty, a style tag won't be printed at all
		// Hence, the whitespace character workaround
		// This is useful if you need to put the CSS in your own theme's stylesheet
		// Since this plugin's CSS has higher priority, it will most likely be
		// below your theme's stylesheet and somehow make a mess
		return trim( $css );
	}

	/**
	 * Initialize
	 */
	public function initialize() {
		add_shortcode( $this->get_setting( 'shortcode_tag', 'advanced', $this->feed->name ), [$this, 'display_feed'] );
	}

	/**
	 * Replace item
	 */
	protected function replace_item($item, $name, $value) {
		$offset = 0;
		while (true) {
			$start = mb_strpos($item, '[' . $name, $offset);
			if ($start === false) {
				break;
			}
			$length = mb_strlen('[' . $name);
			$tail = mb_strpos($item, ']', $start + $length);
			if ($tail === false) {
				break;
			}
			$code = mb_substr($item, $start, $tail - $start + 1);
			$trail = mb_substr($code, $length, mb_strlen($code) - $length - 1);
			if ($trail && mb_substr($trail, 0, 1) !== ':') {
				$offset = $tail + 1;
				continue;
			}
			$args = explode(';', mb_substr($trail, 1));
			$has_html = false;
			$filters = [];
			if ($args) {
				foreach ($args as $arg) {
					$filter_name = $arg;
					$param = null;
					$equals = mb_strpos($arg, '=');
					if ($equals !== false) {
						$filter_name = mb_substr($arg, 0, $equals);
						$param = mb_substr($arg, $equals + 1);
					}
					$filters[] = [
						'filter'=> $filter_name,
						'param'	=> $param
					];
					if ( $filter_name === 'html' ) {
						$has_html = true;
					}
				}
			}
			if ( ! $has_html ) {
				$filters[] = [
					'filter'=> 'safe',
					'param'	=> null
				];
			}
			foreach ($filters as $filter) {
				$value = $this->apply_filter( $value, $filter['filter'], $filter['param'] );
			}
			$left = mb_substr($item, 0, $start);
			$right = mb_substr($item, $tail + 1);
			$middle = $left . $value;
			$item = $middle . $right;
			$offset = mb_strlen($middle);
		}
		return $item;
	}

}
