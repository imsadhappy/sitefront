<?php

/**
 * @package storefront
 * @subpackage sitefront
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

class Sitefront {

	private static $_instance = null;

	public $i18n_vocabulary = array();

	/**
	 * Singleton
	 *
	 * @return Sitefront - Main instance.
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

		$this->vars();
		$this->misc();

		if ( class_exists('Sitefront_PWA') )
			$this->pwa = new Sitefront_PWA;

		if ( class_exists('Sitefront_Customizer') )
			$this->customizer = new Sitefront_Customizer;

		foreach ( array('wp_head','admin_head','login_head') as $hook )
			add_action( $hook, array($this, 'wp_head'), -1 );

		add_filter( 'body_class', array($this, 'body_class'), 99, 2 );
		add_filter( 'login_body_class', array($this, 'body_class'), 99, 2 );
		add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts'), 40 ); /* 40 - After Storefront */
		add_action( 'login_enqueue_scripts', array($this, 'enqueue_scripts'), 40 );

		add_action( 'storefront_before_site', array($this, 'before_site') );
		add_action( 'storefront_header', array($this, 'before_header'), -1 );
		add_action( 'woocommerce_before_single_product', array($this, 'before_single_product') );
	}

	public function localize( $string, $translation = '', $domain = 'sitefront' ) {

		$this->i18n_vocabulary[$string] = empty($translation) ? __($string, $domain) : $translation;
	}

	public function wp_head() {

		ob_start();

		?>
		<meta charset="<?php bloginfo('charset') ?>">
		<meta name="google" content="notranslate">
		<meta name="viewport" content="<?php echo implode(', ', $this->viewport_vars) ?>">
		<?php if ( function_exists('wp_loading_indicator') ) call_user_func('wp_loading_indicator', 'css') ?>
		<script type="text/javascript">
		document.documentElement.style.opacity = <?php echo $this->document_opacity ? '1':'0' ?>;
		document.documentElement.classList.add("<?php echo implode('","', $this->document_styles) ?>");
		window.i18n_vocabulary = window.i18n_vocabulary || <?php echo json_encode($this->i18n_vocabulary) ?>;
		<?php foreach ($this->js_vars as $var => $value) printf('var %s=%s;', $var, var_export($value, true)) ?>
		</script>
		<?php

		echo apply_filters( 'sitefront_wp_head_html', ob_get_clean(), $this );
	}

	public function body_class( $classes, $class ) {

		$classes[] = 'theme-sitefront notranslate';

		if ( function_exists('is_blog') && call_user_func('is_blog') )
			$classes[] = 'blog';

		if ( is_home() )
			$classes[] = 'blog-home';

		/* BuddyPress */

			/**
			 * Removes the page-two-column CSS class from the body class in BuddyPress pages.
			 */

			if ( function_exists( 'bp_loaded' ) && ! call_user_func('bp_is_blog_page') ) {

				foreach($classes as $key => $value) {

					if ($value == 'page-two-column')
						unset($classes[$key]);
				}
			}

		return $classes;
	}

	public function enqueue_scripts() {

		$js = function ($f) { return sprintf('%s/assets/js/%s.js', $this->url, $f); };

		wp_enqueue_script( 'fastclick', $js('fastclick'), array(), $this->ver, false );
		wp_enqueue_script( 'window-extensions', $js('window-extensions'), array(), $this->ver, false );
		wp_enqueue_script( 'jquery-extensions', $js('jquery-extensions'), array('jquery'), $this->ver, false );

		do_action( 'sitefront_enqueue_scripts' );

		wp_enqueue_script( 'sitefront', $js('sitefront'), array('jquery'), $this->ver, true );
		wp_enqueue_script( 'sitefront-hooks', $js('hooks'), array('jquery', 'window-extensions', 'jquery-extensions', 'sitefront'), $this->ver, true );

		wp_enqueue_style('dashicons');
		wp_deregister_style('select2');
		wp_deregister_script('selectWoo');
		wp_dequeue_style('storefront-fonts');
		//wp_dequeue_script( 'storefront-navigation' );
		wp_dequeue_script('storefront-handheld-footer-bar');
		wp_add_inline_script('jquery-migrate', 'jQuery.migrateTrace = false');
	}

	public function before_site() {

		remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

		if ( ! is_front_page() && 'yes' === get_option('disable_header', 'no') )
			add_action( 'storefront_content_top', 'storefront_handheld_footer_bar_home_link', 9 );

		if ( function_exists('is_shop') ) {

			if ( is_shop() )
				add_filter( 'woocommerce_show_page_title', '__return_false' );

			/* remove edit links from frontend */
			if ( is_shop() || is_cart() || is_checkout() || is_account_page() )
				remove_action( 'storefront_page', 'storefront_edit_post_link', 30 );
		}

		/* remove header from login/register page */
		if ( function_exists('is_account_login_page') && call_user_func('is_account_login_page') )
			remove_action( 'storefront_page', 'storefront_page_header', 10 );

		/* remove header from home page */
		if ( is_front_page() )
			remove_action( 'storefront_homepage', 'storefront_homepage_header', 10 );

		foreach ( array( 5 => 'storefront_single_post_bottom',
						30 => 'storefront_page',
						60 => 'woocommerce_single_product_summary' ) as $i => $hook )
			remove_action( $hook, 'storefront_edit_post_link', $i );
	}

	public function before_header() {

		/* First un-hook default functions hooked into storefront_header action */

		foreach ( array(0 => 'header_container',
						5 => 'skip_links',
						10 => 'social_icons',
						20 => 'site_branding',
						30 => 'secondary_navigation',
						40 => 'product_search',
						41 => 'header_container_close',
						42 => 'primary_navigation_wrapper',
						50 => 'primary_navigation',
						60 => 'header_cart',
						68 => 'primary_navigation_wrapper_close') as $i => $hook )
			remove_action( 'storefront_header', 'storefront_' . $hook, $i );

		if ( 'yes' === get_option('disable_header_search', 'no') )
			add_filter( 'sitefront_product_search', '__return_false' );

		if ( 'yes' === get_option('disable_header_cart', 'no') )
			add_filter( 'sitefront_header_cart', '__return_false' );

		/* Then hook back again some of them */

		foreach ( array(10 => 'social_icons',
						20 => 'site_branding',
						30 => 'secondary_navigation',
						40 => 'product_search',
						50 => 'primary_navigation',
						60 => 'header_cart') as $i => $hook ) {
			if ( function_exists('storefront_' . $hook) && apply_filters( 'sitefront_' . $hook , true, $this ) )
				add_action( 'storefront_header', 'storefront_' . $hook, $i );
		}
	}

	public function before_single_product() {

		global $product;

		if ( ! $product->is_type('grouped') ) {
			remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_show_product_images', 1 );
		}

		if ( $product->is_type('simple') )
			add_action( 'storefront_footer', 'sitefront_extend_handheld_footer_bar', 0 );

		if ( $product->is_type('variable') )
			add_filter( 'woocommerce_dropdown_variation_attribute_options_args', function ( $args ) {

				$args['show_option_none'] = __('Select') . ' ' . $args['attribute'];

				return $args;
			} );

		add_filter( 'woocommerce_product_description_heading', function () use ( $product ) {

			return $product->get_title();
		} );
	}

	private function misc() {

		/* Account */

			add_filter( 'woocommerce_account_menu_items', function ( $items, $endpoints ) {

				unset($items['dashboard']);

				return $items;
			}, 9, 2 );

			add_action( 'wp_loaded', function () {

				if ( wc_get_page_permalink('myaccount') === home_url(remove_query_arg(array_keys($_GET), $_SERVER['REQUEST_URI'])) )
					wp_safe_redirect( wc_customer_edit_account_url() ) and exit;
			} );

			add_filter( 'woocommerce_my_account_my_orders_query', function ( $args ) {

				$args['posts_per_page'] = 10;

				return $args;
			}, 9 );

		/* Widget */

			add_filter( 'widget_title', function ( $title, $instance, $args = null ) {

				return empty($instance['title']) ? false : $title;
			}, 999, 3 );

		/* Cart */

			add_action( 'woocommerce_cart_is_empty', function () {

				?><style type="text/css">
					.storefront-handheld-footer-bar {
						border-top: 1px solid rgba(0,0,0,.15) !important;
						box-shadow: 0 -3px 6px -3px rgba(0,0,0,.05) !important;
					}
				</style><?php
			} );

		/* Catalog */

			add_filter( 'loop_shop_columns', function () { return 4; }, 999 );
			//add_filter( 'woocommerce_catalog_lazyload_columns', function () { return 4; }, 999 );

			add_filter( 'woocommerce_output_related_products_args', function ( $args ) {

				$args['posts_per_page'] = $args['columns'] = 3;

				return $args;
			}, 20 );

			add_filter( 'woocommerce_get_image_size_thumbnail', function () {

				return array( 'width'  => 680, 'height' => 680, 'crop' => 1 );
			} );

			add_filter( 'storefront_featured_products_args', function ( $args ){

				$args['title'] = '';

				return $args;
			}, 9 );

			add_filter( 'woocommerce_get_catalog_ordering_args', function ( $args, $orderby, $order ) {

				$args['orderby'] = 'date title';
				$args['order'] = 'DESC';

				return $args;
			}, 9, 3 );

			add_filter( 'woocommerce_product_add_to_cart_text', function ( $text, $product ) {
				return ( $product->is_type('grouped') || $product->is_type('variable') ) ? __( 'Select' ) : $text;
			}, 999, 2 );

		/* Product */

			add_filter( 'woocommerce_post_class', function ( $classes ) {

				$classes[] = 'sitefront-product';

				return $classes;
			}, 10 );

			add_filter( 'woocommerce_output_related_products_args', function ( $args ) {

				$args['posts_per_page'] = $args['columns'] = get_option( 'woocommerce_catalog_columns', 4 );

				return $args;
			} );

			add_filter( 'woocommerce_upsells_columns', function () { return get_option( 'woocommerce_catalog_columns', 4 ); } );
	}

	private function vars() {

		$this->url = get_stylesheet_directory_uri();
		$this->ver = wp_get_theme()->get('Version');
		$this->site_description = get_option('blogdescription_'.get_user_locale(), get_option('blogdescription'));
		$this->document_opacity = apply_filters( 'sitefront_document_opacity', is_customize_preview() || is_admin() || current_action('login_head') );

		$this->viewport_vars = apply_filters( 'sitefront_viewport_vars', array(
			'minimal-ui',
			'viewport-fit=cover',
			'user-scalable=no',
			'initial-scale=1',
			'maximum-scale=1',
			'minimum-scale=1',
			'width=device-width'
		) );

		$this->document_styles = array('sitefront');
		$this->document_styles[] = is_user_logged_in() ? 'user-logged-in' : 'user-not-logged-in';

		if ( is_admin_bar_showing() )
			$this->document_styles[] = 'adminbar-showing';

		$this->js_vars = array(
			'themeurl' => $this->url,
			'baseurl' => get_bloginfo('url'),
			'ajaxurl' => admin_url('admin-ajax.php'),
			'blogname' => get_bloginfo('name'),
			'user_id' => false,
			'user_email' => false,
			'isPhonegap' => ( function_exists('is_phonegap') && call_user_func('is_phonegap') ),
			'isAdmin' => current_user_can('administrator'),
			'isUnload' => false,
			'isLoad' => false
		);

		foreach (array('Install our App',
						'"Add to Home Screen"',
						'To install tap',
						'and choose',
						'Couldn\'t add product to cart') as $str) {
			$this->localize($str);
		}

		if ( $user = wp_get_current_user() ) {
			$this->js_vars['user_id'] = $user->ID;
			$this->js_vars['user_email'] = $user->user_email;
		}

		if ( function_exists('wp_loading_indicator') )
			$this->js_vars['loadingIndicator'] = call_user_func('wp_loading_indicator', 'html', false);
	}
}
