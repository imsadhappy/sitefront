<?php

/**
 * @package storefront
 * @subpackage sitefront
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

class Sitefront_Customizer {

	private static $_instance = null;

	/**
	 * Singleton
	 *
	 * @return Sitefront_Customizer - Main instance.
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) )
			self::$_instance = new self();

		return self::$_instance;
	}

	/**
	 * Constructor
	 * Ensures only one instance of class is loaded.
	 */
	public function __construct() {

		if ( ! is_null( self::$_instance ) )
			return _doing_it_wrong( __FUNCTION__, __( 'Instance of '.__CLASS__.' already exists. Constructing new instances of this class is forbidden.' ), '1.0' );

		add_action( 'customize_register', array($this, 'setup'), -1 );
	}

	public function settings( $key = false ) {

		$settings = array(

			'disable_header' => array(
				'label'	=> __( 'Hide Header', 'sitefront' ),
				'section'  => 'header_image',
				'settings' => 'disable_header',
				'type'	 => 'checkbox'
			),

			'sticky_header' => array(
				'label'	=> __( 'Fixed header (make it "sticky")', 'sitefront' ),
				'section'  => 'header_image',
				'settings' => 'sticky_header',
				'type'	 => 'checkbox'
			),

			'disable_header_search' => array(
				'label'	=> __( 'Hide header search', 'sitefront' ),
				'section'  => 'header_image',
				'settings' => 'disable_header_search',
				'type'	 => 'checkbox'
			),

			'disable_header_search_resize' => array(
				'label'	=> __( 'Disable header search resize', 'sitefront' ),
				'section'  => 'header_image',
				'settings' => 'disable_header_search_resize',
				'type'	 => 'checkbox'
			),

			'disable_header_cart' => array(
				'label'	=> __( 'Hide header cart', 'sitefront' ),
				'section'  => 'header_image',
				'settings' => 'disable_header_cart',
				'type'	 => 'checkbox'
			),

			'disable_footer' => array(
				'label'	=> __( 'Hide Footer', 'sitefront' ),
				'section'  => 'storefront_footer',
				'settings' => 'disable_footer',
				'type'	 => 'checkbox'
			),

			'sticky_footer' => array(
				'label'	=> __( 'Fixed footer (make it "sticky")', 'sitefront' ),
				'section'  => 'storefront_footer',
				'settings' => 'sticky_footer',
				'type'	 => 'checkbox'
			)
		);

		return $key ? $settings[$key] : $settings;
	}

	public function setup( $wp_customize ) {

		$settings = $this->settings();

		/*
		if ( storefront_is_woocommerce_activated() ) {
			unset($settings['sticky_footer']);
		} else {
			unset($settings['disable_header_search'], $settings['disable_header_cart']);
		}
		*/

		foreach ($settings as $setting => $control) {
			$wp_customize->add_setting($setting, array(
				'sanitize_callback'	=> 'bool_to_str',
				'sanitize_js_callback' => 'str_to_bool',
				'default'			  => 'no',
				'type'				 => 'option'
			));
			$wp_customize->add_control($setting, $control);
		}

		add_action( 'customize_controls_print_footer_scripts', array($this, 'footer_scripts'), 31 );
	}

	public function footer_scripts() {

		?>
		<style type="text/css">.hide-next-siblings ~ li {display: none !important}</style>
		<script type="text/javascript" id="sitefront_customizer">
			(function(){
				var api = wp.customize;
				api.bind('ready', function(){
					['disable_header', 'disable_footer'].forEach(function(setting_name){
						api(setting_name, function(setting){
							api.control(setting_name, function(e){
								var visibility = function(){
									e.container[setting.get() ? 'addClass' : 'removeClass']('hide-next-siblings');
								};
								visibility();
								setting.bind(visibility);
							});
						});
					});
				});
			})();
		</script>
		<?php
	}
}