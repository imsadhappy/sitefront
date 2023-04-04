<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package storefront
 */

/**
 * @hooked nothing
 */
do_action( 'storefront_content_bottom' ) ?>

		</div><!-- .col-full -->
	</div><!-- #content -->

	<?php
	/**
	 * @hooked nothing
	 */
	do_action( 'storefront_before_footer' );

	if ( apply_filters( 'storefront_show_footer', 'no' === get_option( 'disable_footer', 'no' ) ) ) : ?>

	<footer id="colophon" class="<?php sitefront_footer_class() ?>" role="contentinfo">
		<div class="col-full">

			<?php
			/**
			 * Functions hooked in to storefront_footer action
			 *
			 * @hooked storefront_footer_widgets - 10
			 * @hooked storefront_credit         - 20
			 */
			do_action( 'storefront_footer' );
			?>

		</div><!-- .col-full -->
	</footer><!-- #colophon -->

	<?php else: ?>

	<div class="footer-disabled"></div>

	<?php endif;
	/**
	 * @hooked nothing
	 */
	do_action( 'storefront_after_footer' ); ?>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
