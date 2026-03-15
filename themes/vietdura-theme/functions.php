<?php
// Theme Setup und Includes

require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/cpt-speisen.php';
require_once get_template_directory() . '/inc/cpt-getraenke.php';
require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/theme-options.php';
require_once get_template_directory() . '/inc/acf-fields.php';
require_once get_template_directory() . '/inc/roles.php';
require_once get_template_directory() . '/inc/cart.php';
require_once get_template_directory() . '/inc/order.php';
require_once get_template_directory() . '/inc/cpt-bestellungen.php';

if ( is_admin() ) {
	require_once get_template_directory() . '/inc/admin.php';
}

// ── Custom Login Page Styles ──────────────────────────────────────────────────
add_action( 'login_enqueue_scripts', function () {
	wp_enqueue_style(
		'vietdura-login',
		get_template_directory_uri() . '/assets/css/login.css',
		[],
		'1.0'
	);
	// Load Google Fonts used by the theme
	wp_enqueue_style(
		'vietdura-login-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@400;500;600&display=swap',
		[],
		null
	);
} );

// Change login logo link to homepage
add_filter( 'login_headerurl', fn() => home_url() );
add_filter( 'login_headertext', fn() => get_bloginfo( 'name' ) );

// Hero-Bild als Login-Hintergrund
add_action( 'login_enqueue_scripts', function () {
	$front_page_id = get_option( 'page_on_front' );
	$hero_bg = null;

	if ( $front_page_id ) {
		$hero_bg = get_field( 'hero_bg_image', $front_page_id );
		if ( ! $hero_bg ) $hero_bg = get_field( 'hero_bg_image', 'option' );
	}

	$bg_url = '';
	if ( $hero_bg ) {
		$bg_url = is_array( $hero_bg ) ? ( $hero_bg['url'] ?? '' ) : wp_get_attachment_url( $hero_bg );
	}

	if ( ! $bg_url ) return;

	echo '<style>
	body.login {
		background-image: url(' . esc_url( $bg_url ) . ') !important;
		background-size: cover !important;
		background-position: center !important;
		background-repeat: no-repeat !important;
	}
	body.login::before {
		content: "";
		position: fixed;
		inset: 0;
		background: rgba(31, 77, 58, 0.55);
		z-index: 0;
	}
	#login, #loginform, .login h1, .login #nav, .login #backtoblog, .language-switcher {
		position: relative;
		z-index: 1;
	}
	.login h1 a::before {
		color: #fff !important;
	}
	#loginform, #lostpasswordform {
		background: rgba(255,255,255,0.96) !important;
		backdrop-filter: blur(8px);
	}
	.login .message, .login .success {
		background: rgba(255,255,255,0.9) !important;
	}
	.language-switcher {
		color: rgba(255,255,255,0.7);
	}
	.language-switcher select {
		background: rgba(255,255,255,0.15);
		color: #fff;
		border-color: rgba(255,255,255,0.3);
	}
	</style>';
} );

// "← Zu vietdura.ch" Link entfernen
add_action( 'login_footer', function () {
	echo '<script>var el=document.getElementById("backtoblog");if(el)el.remove();</script>';
} );
