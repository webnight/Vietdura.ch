<?php
// CSS & JS einbinden
function vietdura_enqueue_scripts() {
	$theme_version = wp_get_theme()->get('Version');
	$uri = get_template_directory_uri();

	wp_enqueue_style( 'vietdura-main', $uri . '/assets/css/main.css', [], $theme_version );
	wp_enqueue_style( 'vietdura-cart', $uri . '/assets/css/cart.css', [ 'vietdura-main' ], $theme_version );

	wp_enqueue_script( 'vietdura-main', $uri . '/assets/js/main.js', [], $theme_version, true );
	wp_enqueue_script( 'vietdura-cart', $uri . '/assets/js/cart.js', [], $theme_version, true );
}
add_action( 'wp_enqueue_scripts', 'vietdura_enqueue_scripts' );
