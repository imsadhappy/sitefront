<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package storefront
 * @subpackage sitefront
 */

if ( ! apply_filters( 'sitefront_do_header', true ) )
	return;

?><!DOCTYPE html>
<html <?php do_action( 'sitefront_html_tag' ) ?> <?php language_attributes() ?>>
<head><?php wp_head() ?></head>
<body <?php body_class() ?>>

<?php wp_body_open() ?>

<?php
/**
 * @hooked sitefront()->before_site()
 */
do_action( 'storefront_before_site' ) ?>

<div id="page" class="hfeed site">

<?php
/**
 * @hooked nothing
 */
do_action( 'storefront_before_header' );

if ( apply_filters( 'storefront_show_header', 'no' === get_option( 'disable_header', 'no' ) ) ) : ?>

<header id="masthead" class="<?php sitefront_header_class() ?>" role="banner" style="<?php storefront_header_styles() ?>">

	<div class="col-full">

		<?php
		/**
		* @hooked social_icons - 10
		* @hooked site_branding - 20
		* @hooked secondary_navigation - 30
		* @hooked product_search - 40
		* @hooked primary_navigation - 50
		* @hooked header_cart - 60
		 */
		do_action( 'storefront_header' ) ?>

	</div>

</header>

<?php else: ?>

<div class="header-disabled">

	<?php storefront_primary_navigation() ?>

</div>

<?php endif;

/**
 * @hooked storefront_header_widget_region - 10
 * @hooked woocommerce_breadcrumb - 10
 */
do_action( 'storefront_before_content' ) ?>

<div id="content" class="site-content" tabindex="-1">

	<div class="col-full">

	<?php
	/**
	 * @hooked storefront_shop_messages - 15
	 */
	do_action( 'storefront_content_top' );
