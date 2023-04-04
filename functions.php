<?php

/**
 * Functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * When using a child theme you can override certain functions (those wrapped
 * in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before
 * the parent theme's file, so the child theme functions would be used.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @link https://codex.wordpress.org/Child_Themes
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are
 * instead attached to a filter or action hook.
 *
 * For more information on hooks, actions, and filters,
 * {@link https://codex.wordpress.org/Plugin_API}
 *
 * @package storefront
 * @subpackage sitefront
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

require_once get_stylesheet_directory() . '/includes/pluggable.php';
require_once get_stylesheet_directory() . '/includes/class-sitefront.php';
require_once get_stylesheet_directory() . '/includes/class-sitefront-pwa.php';
require_once get_stylesheet_directory() . '/includes/class-sitefront-customizer.php';

/* Theme */

	function sitefront() {
		return Sitefront::instance();
	}

	sitefront();

	add_action( 'after_setup_theme', function () {

		add_theme_support( 'wc-catalog-lazyload' );
		remove_theme_support( 'wc-product-gallery-zoom' );
		remove_theme_support( 'wc-product-gallery-lightbox' );
	}, 99 );

/* Mobile back-button */

	foreach ( array('woocommerce_view_order',
					'woocommerce_after_edit_address_form_billing',
					'woocommerce_after_edit_address_form_shipping',
					'woocommerce_checkout_after_terms_and_conditions') as $hook )
		add_action( $hook, 'storefront_handheld_footer_bar_back_link', 9 );

/* Blog */

	add_action( 'storefront_loop_before', function () {
		remove_action( 'storefront_loop_post', 'storefront_post_content', 30 );
		add_action( 'storefront_loop_post', 'sitefront_loop_post_entry_content', 30 );
		add_action( 'storefront_loop_post', 'sitefront_loop_post_permalink', 41 );
	}, 9 );

/* Account */

	add_action( 'woocommerce_before_edit_account_form', 'sitefront_logout_button', 9 );
	add_action( 'woocommerce_edit_account_form', 'sitefront_password_change_toggler', 9 );
	add_action( 'woocommerce_after_edit_account_form', 'sitefront_language_select_form', 9 );

/* Product */

	add_action( 'woocommerce_before_single_product_summary', 'sitefront_product_children_toggler', 9 );
	add_filter( 'woocommerce_product_review_comment_form_args', 'sitefront_product_reviews_toggler', 9 );
	add_action( 'woocommerce_after_single_product_summary', 'woocommerce_template_single_title', 5 );

/* Footer */

	add_filter( 'storefront_handheld_footer_bar_links', 'sitefront_handheld_footer_bar_links', 9 );
	add_action( 'storefront_handheld_footer_bar_account_menu', 'sitefront_handheld_footer_bar_account_menu', 9 );
