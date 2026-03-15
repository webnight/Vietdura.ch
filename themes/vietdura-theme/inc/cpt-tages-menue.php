<?php
// Custom Post Type: Mittagsmenü

function vietdura_register_cpt_tages_menue() {
	$labels = [
		'name'               => 'Mittagsmenü',
		'singular_name'      => 'Mittagsmenü',
		'add_new'            => 'Neues Mittagsmenü',
		'add_new_item'       => 'Neues Mittagsmenü hinzufügen',
		'edit_item'          => 'Mittagsmenü bearbeiten',
		'new_item'           => 'Neues Mittagsmenü',
		'view_item'          => 'Mittagsmenü ansehen',
		'search_items'       => 'Mittagsmenü suchen',
		'not_found'          => 'Kein Mittagsmenü gefunden',
		'not_found_in_trash' => 'Kein Mittagsmenü im Papierkorb',
		'all_items'          => 'Alle Mittagsmenüs',
		'menu_name'          => 'Mittagsmenü',
	];

	register_post_type( 'mittagsmenu', [
		'labels'          => $labels,
		'public'          => true,
		'has_archive'     => false,
		'show_in_rest'    => true,
		'supports'        => [ 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ],
		'menu_icon'       => 'dashicons-clock',
		'menu_position'   => 6,
		'rewrite'         => [ 'slug' => 'mittagsmenu' ],
	] );
}
add_action( 'init', 'vietdura_register_cpt_tages_menue' );
