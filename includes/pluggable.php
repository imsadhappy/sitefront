<?php

/**
 * @package storefront
 * @subpackage sitefront
 */

if ( ! function_exists( 'storefront_handheld_footer_bar_home_link' ) ) {
	/**
	 * The home callback function for the handheld footer bar
	 *
	 * @since 1.0.0
	 */
	function storefront_handheld_footer_bar_home_link () {

		$u = home_url();
		$c = 'default';
		$f = __FUNCTION__;
		$t = esc_attr__('Home');

		if ( is_single() ) {

			$c = 'back';
			$t = esc_attr__('Back');

			if ( is_blog() && $b = get_option('page_for_posts', false) )
				$u = get_permalink($b);

			if ( function_exists('is_shop') && is_shop() && $s = wc_get_page_id('shop') )
				$u = get_permalink($s);
		}

		printf( '<a href="%s" class="%s backlink FoUC">%s</a>',
				apply_filters($f, $u),
				apply_filters($f.'_class', $c),
				apply_filters($f.'_text', $t) );
	}
}

if ( ! function_exists( 'storefront_handheld_footer_bar_menu_link' ) ) {
	/**
	 * The menu callback function for the handheld footer bar
	 *
	 * @since 1.0.0
	 */
	function storefront_handheld_footer_bar_menu_link () {

		printf( '<a href="#" data-target="%s">%s</a>',
				apply_filters( __FUNCTION__, '#site-navigation .menu-toggle' ),
				esc_attr__( 'Menu', 'storefront' ) );
	}
}

if ( ! function_exists( 'storefront_handheld_footer_bar_account_menu' ) ) {
	/**
	 * The menu callback function for the handheld footer bar
	 *
	 * @since 1.0.0
	 */
	function storefront_handheld_footer_bar_account_menu () {

		if ( ! is_user_logged_in() )
			return storefront_handheld_footer_bar_account_link();

		?>
			<a href="#" class="account-menu toggler"><?php esc_attr_e( 'My Account', 'storefront' ) ?></a>
			<div class="toggled my-account-navigation">
				<div class="col-full">
					<?php do_action( __FUNCTION__ ); ?>
				</div>
			</div>
		<?php
	}
}

if ( ! function_exists( 'storefront_handheld_footer_bar_back_link' ) ) {
	/**
	 * The menu callback function for the handheld footer bar
	 *
	 * @since 1.0.0
	 */
	function storefront_handheld_footer_bar_back_link () {

		$u = home_url();

		switch (current_action()) {
			case 'woocommerce_view_order':
				$u = wc_get_account_endpoint_url( get_option( 'woocommerce_myaccount_orders_endpoint', 'orders' ) );
					break;
			case 'woocommerce_after_edit_address_form_billing':
			case 'woocommerce_after_edit_address_form_shipping':
				$u = wc_get_account_endpoint_url( get_option( 'woocommerce_myaccount_edit_address_endpoint', 'edit-address' ) );
					break;
			case 'woocommerce_checkout_after_terms_and_conditions':
				$u = wc_get_cart_url();
		}

		add_filter( 'storefront_handheld_footer_bar_home_link', function () use ( $u ) { return esc_url( $u ); } );
		add_filter( 'storefront_handheld_footer_bar_home_link_class', function () { return 'back'; } );
	}
}

if ( ! function_exists( 'storefront_post_taxonomy' ) ) {
	/**
	 * Display the post taxonomies
	 */
	function storefront_post_taxonomy() {

		/* translators: used between list items, there is a space after the comma */
		$categories_list = get_the_category_list( __( ', ', 'storefront' ) );

		/* translators: used between list items, there is a space after the comma */
		$tags_list = get_the_tag_list( '', __( ', ', 'storefront' ) );

		if ( $categories_list || $tags_list ) :

		?><aside class="entry-taxonomy">
			<?php if ( $categories_list ) : ?>
			<div class="cat-links">
				<?php echo esc_html( _n( 'Category:', 'Categories:', count( get_the_category() ), 'storefront' ) ); ?> <?php echo wp_kses_post( $categories_list ); ?>
			</div>
			<?php endif;

			if ( $tags_list ) : ?>
			<div class="tags-links">
				<?php echo esc_html( _n( 'Tag:', 'Tags:', count( get_the_tags() ), 'storefront' ) ); ?> <?php echo wp_kses_post( $tags_list ); ?>
			</div>
			<?php endif; ?>
		</aside><?php

		endif;
	}
}

if ( ! function_exists( 'storefront_credit' ) ) {

	function storefront_credit() {

		ob_start();

		?><div class="site-info">

		<?php echo esc_html( apply_filters( 'storefront_copyright_text', $content = '&copy; ' . get_bloginfo( 'name' ) . ' ' . date( 'Y' ) ) ); ?>

		<?php do_action( 'after_storefront_copyright_text' ) ?>

		<?php if (function_exists('wc_get_page_id') && $terms = wc_get_page_id('terms')): ?>
			<a class="terms-link FoUC" href="<?php echo get_permalink($terms) ?>"><?php echo get_the_title($terms) ?></a>
		<?php endif ?>

		<?php if ( apply_filters( 'storefront_privacy_policy_link', true ) && function_exists( 'the_privacy_policy_link' ) ) : ?>
			<?php echo get_the_privacy_policy_link( '', ( ! empty( $links_output ) ? $separator : '' ) ) ?>
		<?php endif ?>

		<?php if ( apply_filters( 'storefront_credit_link', true ) ) : ?>
			<a href="https://woocommerce.com" target="_blank" rel="nofollow noopener noreferrer"><?php echo esc_html__( 'Built with Storefront &amp; WooCommerce', 'storefront' ) ?></a>
			<?php do_action( 'after_storefront_credit_link' ) ?>
		<?php endif ?>

		</div><!-- .site-info --><?php

		echo apply_filters( __FUNCTION__, preg_replace("/\r|\n|\t/", '', ob_get_clean()) );
	}
}

if ( ! function_exists( 'sitefront_header_class' ) ) {

	function sitefront_header_class () {

		$no_woocommerce = ! storefront_is_woocommerce_activated();
		$classes = array('site-header', 'sitefront-header');

		if ( 'yes' === get_option('sticky_header', 'no') )
			$classes[] = 'fixed';

		if ( $no_woocommerce || 'yes' === get_option('disable_header_search', 'no') )
			$classes[] = 'no-search';

		if ( $no_woocommerce || 'yes' === get_option('disable_header_search_resize', 'no') )
			$classes[] = 'no-search-resize';

		if ( $no_woocommerce || 'yes' === get_option('disable_header_cart', 'no') )
			$classes[] = 'no-cart';

		if ( '' == get_bloginfo( 'description' ) )
			$classes[] = 'no-description';

		if ( function_exists( 'the_custom_logo' ) && has_custom_logo() )
			$classes[] = 'has-custom-logo';

		echo implode(' ', apply_filters( __FUNCTION__, $classes ));
	}
}

if ( ! function_exists( 'sitefront_footer_class' ) ) {

	function sitefront_footer_class () {

		$classes = array('site-footer', 'sitefront-footer');

		if ( 'yes' === get_option('sticky_footer', 'no') )
			$classes[] = 'fixed';

		echo implode(' ', apply_filters( __FUNCTION__, $classes ));
	}
}

if ( ! function_exists( 'sitefront_logout_button' ) ) {

	function sitefront_logout_button() {

		$onclick = sprintf( "return confirm('%s?') ? goTo('%s') : 0",
							wp_strip_all_tags(__("Are you sure you want to log out? <a href=\"%s\">Confirm and log out</a>", 'woocommerce')),
							function_exists('wc_logout_url') ? wc_logout_url() : wp_logout_url() );

		?><a class="button logout" href="#" onclick="<?php echo $onclick ?>"><?php esc_html_e( 'Logout', 'woocommerce' ); ?></a><?php
	}
}

if ( ! function_exists( 'sitefront_password_change_toggler' ) ) {

	function sitefront_password_change_toggler() {

		?>
		<script type="text/javascript">
			(function(){
				var f = document.getElementById('password_current').parentNode.parentNode,
					b = document.createElement('button');
				f.classList.add('toggled', 'password-change');
				b.classList.add('toggler', 'bold', 'scroll-to', 'password-change');
				b.textContent = '<?php esc_html_e( 'Password', 'woocommerce' ); ?>';
				f.parentNode.insertBefore(b, f);
			})();
		</script>
		<?php
	}
}

if ( ! function_exists( 'sitefront_product_reviews_toggler' ) ) {

	function sitefront_product_reviews_toggler( $comment_form ) {

		if ( get_option( 'comment_registration' ) && !is_user_logged_in() )
			return $comment_form;

		ob_start();

		?>
		<a href="#" class="button toggler comment-form-toggler">
			<span class="toggler-state"><?php _e('Add a review', 'woocommerce') ?></span>
			<span class="toggler-state hidden"><?php _e('Cancel', 'woocommerce') ?></span>
		</a>
		<?php

		$comment_form['class_form'] = 'comment-form toggled';
		$comment_form['title_reply_before'] = ob_get_clean() . $comment_form['title_reply_before'];

		return $comment_form;
	}
}

if ( ! function_exists( 'sitefront_product_children_toggler' ) ) {

	function sitefront_product_children_toggler() {

		global $product;

		if ( $product && $product->is_type('grouped') && count($product->get_children()) > 4 ) {

			add_action( 'woocommerce_before_add_to_cart_form', function () { ?>
				<div class="grouped-cart-wrapper">
					<button class="hidden toggler grouped_form-toggler">
						<span class="toggler-state"><?php _e( 'Show more...', 'sitefront' ) ?></span>
						<span class="toggler-state hidden"><?php _e( 'Show less...', 'sitefront' ) ?></span>
					</button>
				<?php
			}, 10 );

			add_action( 'woocommerce_after_add_to_cart_form', function () { ?>
				</div><!--#end .grouped-cart-wrapper --><?php
			} );
		}
	}
}

if ( ! function_exists( 'sitefront_loop_post_entry_content' ) ) {

	function sitefront_loop_post_entry_content() {

		$f = __FUNCTION__;
		$classes = array('entry-content', 'loop-entry-content');
		$use_excerpt = get_option( 'rss_use_excerpt' );

		if ( $use_excerpt )
			array_push($classes, 'excerpt-entry-content', 'cursor-pointer');

		?><div itemprop="articleBody"
				data-goto="<?php the_permalink() ?>"
				class="<?php echo implode(' ', apply_filters($f.'_classes', $classes)) ?>">
			<?php
				if ( has_post_thumbnail() )
					the_post_thumbnail( 'full', array( 'itemprop' => 'image' ) );

				ob_start();

				$use_excerpt ? the_excerpt() : the_content();

				echo apply_filters($f.'_content', ob_get_clean());
			?>
		</div><!-- .entry-content --><?php
	}
}

if ( ! function_exists( 'sitefront_loop_post_permalink' ) ) {

	function sitefront_loop_post_permalink() {

		?><a href="<?php the_permalink() ?>"
			 class="more-link FoUC"><?php printf(
			/* translators: %s: post title */
			__( 'Continue reading %s', 'storefront' ),
			'<span class="screen-reader-text">' . get_the_title() . '</span>'
		) ?></a><?php
	}
}

if ( ! function_exists( 'sitefront_language_select_form' ) ) {

	function sitefront_language_select_form() {

		$languages = get_available_languages();

		if ( count( $languages ) > 1 ) :

		?><form class="user-locale" method="get">

			<label for="locale">
				<span><?php _e( 'Language' ); ?></span>
				<span class="dashicons dashicons-translation" aria-hidden="true"></span>
			</label>

			<?php

			$user_locale = get_user_locale();

			if ( 'en_US' === $user_locale ) {
				$user_locale = '';
			} elseif ( '' === $user_locale || ! in_array( $user_locale, $languages, true ) ) {
				$user_locale = 'site-default';
			}

			$language_select = wp_dropdown_languages(
				array(
					'name'                        => 'locale',
					'id'                          => 'locale',
					'selected'                    => $user_locale,
					'languages'                   => $languages,
					'show_available_translations' => false,
					'show_option_site_default'    => true,
					'echo'                        => false
				)
			);

			echo str_replace('<select ', '<select class="input-text" ', $language_select);

			?>

		</form><?php endif;
	}
}

if ( ! function_exists( 'sitefront_handheld_footer_bar_links' ) ) {

	function sitefront_handheld_footer_bar_links( $links ) {

		$links = array(
			'home' => array(
				'priority' => 10,
				'callback' => 'storefront_handheld_footer_bar_home_link',
			),
			'search' => array(
				'priority' => 20,
				'callback' => 'storefront_handheld_footer_bar_search',
			),
			'cart' => array(
				'priority' => 30,
				'callback' => 'storefront_handheld_footer_bar_cart_link',
			),
			'my-account' => array(
				'priority' => 40,
				'callback' => 'storefront_handheld_footer_bar_account_menu',
			),
			'menu' => array(
				'priority' => 50,
				'callback' => 'storefront_handheld_footer_bar_menu_link',
			)
		);

		return apply_filters( __FUNCTION__, $links );
	}
}

if ( ! function_exists( 'sitefront_handheld_footer_bar_account_menu' ) ) {

	function sitefront_handheld_footer_bar_account_menu() {

		if ( function_exists('bp_get_displayed_user_nav') ):
			if ( ! bp_is_my_profile() )
				add_filter('bp_loggedin_user_domain', '__return_empty_string');
			?>
			<nav class="woocommerce-MyAccount-navigation">
				<ul class="bp-profile-navigation">
					<?php bp_get_displayed_user_nav() ?>
				</ul>
			</nav>
		<?php endif;

		do_action( 'woocommerce_account_navigation' );
	}
}

if ( ! function_exists( 'sitefront_extend_handheld_footer_bar' ) ) {

	function sitefront_extend_handheld_footer_bar() {

		?><div class="hidden extend-footer-bar"></div><?php
	}
}