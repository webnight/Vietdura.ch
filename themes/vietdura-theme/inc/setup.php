<?php
// Theme Setup
function vietdura_theme_setup() {
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);
	register_nav_menus([
		'main_menu' => __('Hauptmenü', 'vietdura'),
	]);
}
add_action('after_setup_theme', 'vietdura_theme_setup');


// ACF Local JSON: Speicherpfad im Theme registrieren
add_filter( 'acf/settings/save_json', function() {
    return get_template_directory() . '/acf-json';
} );
add_filter( 'acf/settings/load_json', function( $paths ) {
    $paths[] = get_template_directory() . '/acf-json';
    return $paths;
} );


// Gutenberg / Block Editor deaktivieren

add_filter( 'use_block_editor_for_post', '__return_false' );
add_filter( 'use_block_editor_for_post_type', '__return_false' );
add_filter( 'use_widgets_block_editor', '__return_false' );
