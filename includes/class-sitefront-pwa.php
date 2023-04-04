<?php

/**
 * @package storefront
 * @subpackage sitefront
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

class Sitefront_PWA {

	private static $_instance = null;

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

		$this->url = get_stylesheet_directory_uri();
		$this->ver = wp_get_theme()->get('Version');
		$this->color = apply_filters('sitefront_theme_color', '#FFFFFF');
		$this->site_name = get_option('blogname_'.get_user_locale(), get_option('blogname'));

		foreach ( array('manifest','service_worker','index') as $method )
			add_action( 'wp_loaded', array($this, $method), 2 );

		foreach ( array('wp_head','admin_head','login_head', 'eticket_head') as $hook )
			add_action( $hook, array($this, 'favicons'), 10 );

		add_action( 'sitefront_enqueue_scripts', array($this, 'enqueue_scripts') );
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts') );
	}

	public function manifest() {

		if ( strpos($_SERVER['REQUEST_URI'], 'manifest.json') === false )
			return;

		$name = get_option('blogname_'.$_GET['lang'], get_option('blogname'));

		wp_send_json( [
			"name" => $name,
			"short_name" => $name,
			"scope" => "/",
			"start_url" => "/pwa.html",
			"display" => "standalone",
			"orientation" => "portrait",
			"theme_color" => $this->color,
			"background_color" => $this->color,
			"icons" => [ [
					"src" => "{$this->url}/favicons/android-chrome-192x192.png?v={$this->ver}",
					"sizes" => "192x192",
					"type" => "image/png"
				], [
					"src" => "{$this->url}/favicons/android-chrome-512x512.png?v={$this->ver}",
					"sizes" => "512x512",
					"type" => "image/png"
				] ]
		] );
	}

	public function service_worker() {

		if ( strpos($_SERVER['REQUEST_URI'], 'service-worker.js') === false )
			return;

		header('Accept-Ranges: bytes');
		header('Content-Type: application/javascript');

		$sw = file_get_contents( get_stylesheet_directory() . '/assets/js/service-worker.js' );
		$vars = array(
			'cacheVersion' => "v$this->ver",
			'cacheList' => array('pwa.html')
		);

		if ( false && defined('MMR_CACHE_DIR') ) /* too many files cached */
			$vars['cacheList'] = array_merge($vars['cacheList'], glob(constant('MMR_CACHE_DIR')."/*.{min.js,min.css}", GLOB_BRACE));

		$sv = '';
		foreach ($vars as $name => $value) {
			$value = json_encode($value);
			$value = str_replace(ABSPATH, home_url('/'), $value);
			$sv .= sprintf("const %s = %s;\n", $name, $value);
		}

		exit($sv . $sw);
	}

	public function favicons() {

		if ( function_exists('is_phonegap') && call_user_func('is_phonegap') )
			return;

		$favicon = function ($f) { printf( '%s/favicons/%s?v=%s', $this->url, $f, $this->ver ); };

		ob_start();

		?>
		<link rel="icon" type="image/png" sizes="32x32" href="<?php $favicon('favicon-32x32.png') ?>">
		<link rel="icon" type="image/png" sizes="16x16" href="<?php $favicon('favicon-16x16.png') ?>">
		<link rel="manifest" href="<?php echo home_url(sprintf('/manifest.json?v=%s&lang=%s', $this->ver, get_user_locale())) ?>">
		<link rel="mask-icon" href="<?php $favicon('safari-pinned-tab.svg') ?>" color="<?php echo $this->color ?>">
		<link rel="shortcut icon" href="<?php $favicon('favicon.ico') ?>">
		<meta name="mobile-web-app-capable" content="yes" />
		<meta name="application-name" content="<?php echo $this->site_name ?>">
		<meta name="msapplication-TileColor" content="<?php echo $this->color ?>">
		<meta name="msapplication-config" content="<?php $favicon('browserconfig.xml') ?>">
		<meta name="theme-color" content="<?php echo $this->color ?>">
		<meta name="apple-touch-fullscreen" content="yes" />
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		<meta name="apple-mobile-web-app-title" content="<?php echo $this->site_name ?>">
		<link rel="apple-touch-icon" sizes="180x180" href="<?php $favicon('apple-touch-icon.png') ?>">
		<link rel="apple-touch-startup-image" media="screen and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" href="<?php $favicon('launch-828x1792.png') ?>" />
		<link rel="apple-touch-startup-image" media="screen and (device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" href="<?php $favicon('launch-750x1334.png') ?>" />
		<link rel="apple-touch-startup-image" media="screen and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" href="<?php $favicon('launch-1242x2688.png') ?>" />
		<link rel="apple-touch-startup-image" media="screen and (device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" href="<?php $favicon('launch-1242x2208.png') ?>" />
		<link rel="apple-touch-startup-image" media="screen and (device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" href="<?php $favicon('launch-1668x2388.png') ?>" />
		<link rel="apple-touch-startup-image" media="screen and (device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" href="<?php $favicon('launch-1125x2436.png') ?>" />
		<link rel="apple-touch-startup-image" media="screen and (device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" href="<?php $favicon('launch-2048x2732.png') ?>" />
		<link rel="apple-touch-startup-image" media="screen and (device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" href="<?php $favicon('launch-1668x2224.png') ?>" />
		<link rel="apple-touch-startup-image" media="screen and (device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" href="<?php $favicon('launch-640x1136.png') ?>" />
		<link rel="apple-touch-startup-image" media="screen and (device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" href="<?php $favicon('launch-1536x2048.png') ?>" />
		<script>
		if ('serviceWorker' in navigator) {
			window.addEventListener('load', function() {
				navigator.serviceWorker.register('<?php echo home_url('/service-worker.js?v='.$this->ver) ?>');
			});
		}
		</script><?php

		echo apply_filters( 'sitefront_favicons_html', ob_get_clean(), $this );
	}

	public function index() {

		if ( strpos($_SERVER['REQUEST_URI'], 'pwa.html') === false )
			return;

		printf('<html><body><script>window.location.href = "%s"</script></body></html>', home_url('/?from=pwa'));

		exit;
	}

	public function enqueue_scripts() {

		wp_enqueue_script( 'sitefront-pwa', $this->url . '/assets/js/pwa.js', array(), $this->ver, true );
		$this->enqueue_styles();
	}

	public function enqueue_styles() {

		?><style type="text/css">
		/* iPhone PWA */ @media not all and (display-mode: standalone) {
			.add-to-homescreen {
			  display: none;
			  position: fixed;
			  top: 0;
			  bottom: 0;
			  left: 0;
			  right: 0;
			  text-align: center;
			  color: #FFF;
			  box-sizing: border-box;
			  background-color: #000;
			  z-index: 9999;
			  background-color: rgba(0, 0, 0, 0.3);
			  z-index: +200500;
			}
			.add-to-homescreen-blur {
			  -webkit-filter: blur(10px);
			  filter: blur(10px);
			  -webkit-transition: -webkit-filter .3s linear;
			  transition: filter .3s linear;
			}
			.add-to-homescreen-text {
				position: absolute;
				left: 0;
				right: 0;
				line-height: 30px;
				bottom: 30px;
			}
			.add-to-homescreen-button {
				vertical-align: -10px;
				width: 35px;
				height: 46px;
				display: inline-block;
				background-repeat: no-repeat;
				background-position: center 0;
				background-size: cover;
			}
			.add-to-homescreen-pointer {
				background-repeat: no-repeat;
				background-position: center -70px;
				width: 100%;
				height: 45px;
				background-size: 35px;
				-webkit-animation-duration: 0.5s;
				animation-duration: 0.5s;
				-webkit-animation-name: topToBottom;
				animation-name: topToBottom;
				-webkit-animation-iteration-count: infinite;
				animation-iteration-count: infinite;
				-webkit-animation-direction: alternate;
				animation-direction: alternate;
			}
			.add-to-homescreen-close {
				position: absolute;
				top: 30px;
				right: 30px;
				width: 35px;
				height: 35px;
				background-size: cover;
				background-position: center bottom;
			}
			.add-to-homescreen-button,
			.add-to-homescreen-close,
			.add-to-homescreen-pointer {
				background-image: url('data:image/png;base64,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');
			}
			@keyframes topToBottom {
			  from {
				transform: translate(0, 0);
			  }
			  to {
				transform: translate(0, 20px);
			  }
			}
		}
		</style><?php
	}
}
