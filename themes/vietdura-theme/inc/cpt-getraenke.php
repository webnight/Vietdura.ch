<?php
// Custom Post Type: Getränke

function vietdura_register_cpt_getraenk() {
	$labels = [
		'name'               => 'Getränke',
		'singular_name'      => 'Getränk',
		'add_new'            => 'Neues Getränk',
		'add_new_item'       => 'Neues Getränk hinzufügen',
		'edit_item'          => 'Getränk bearbeiten',
		'new_item'           => 'Neues Getränk',
		'view_item'          => 'Getränk ansehen',
		'search_items'       => 'Getränke suchen',
		'not_found'          => 'Keine Getränke gefunden',
		'not_found_in_trash' => 'Keine Getränke im Papierkorb',
		'all_items'          => 'Alle Getränke',
		'menu_name'          => 'Getränke',
	];

	register_post_type( 'getraenk', [
		'labels'            => $labels,
		'public'            => true,
		'has_archive'       => false,
		'show_in_rest'      => true,
		'supports'          => [ 'title', 'editor', 'excerpt', 'page-attributes' ],
		'menu_icon'         => 'dashicons-coffee',
		'menu_position'     => 6,
		'rewrite'           => [ 'slug' => 'getraenke' ],
		'capability_type'   => [ 'getraenk', 'getraenke' ],
		'map_meta_cap'      => true,
	] );
}
add_action( 'init', 'vietdura_register_cpt_getraenk' );


// Taxonomie: Getränke-Kategorien

function vietdura_register_tax_getraenke_kategorie() {
	$labels = [
		'name'          => 'Kategorien',
		'singular_name' => 'Kategorie',
		'search_items'  => 'Kategorien suchen',
		'all_items'     => 'Alle Kategorien',
		'edit_item'     => 'Kategorie bearbeiten',
		'update_item'   => 'Kategorie aktualisieren',
		'add_new_item'  => 'Neue Kategorie hinzufügen',
		'new_item_name' => 'Neuer Kategoriename',
		'menu_name'     => 'Kategorien',
	];

	register_taxonomy( 'getraenke_kategorie', 'getraenk', [
		'labels'       => $labels,
		'hierarchical' => true,
		'show_in_rest' => true,
		'rewrite'      => [ 'slug' => 'getraenke-kategorie' ],
		'capabilities' => [
			'manage_terms' => 'manage_categories',
			'edit_terms'   => 'manage_categories',
			'delete_terms' => 'manage_categories',
			'assign_terms' => 'edit_getraenke',
		],
	] );
}
add_action( 'init', 'vietdura_register_tax_getraenke_kategorie' );


// Kategorien anlegen und Reihenfolge sicherstellen

function vietdura_setup_getraenke_kategorien(): void {
	$kategorien = [
		1 => 'Mineral-Softdrinks',
		2 => 'Bier Karte',
		3 => 'warme Getränke',
		4 => 'Wein Welt',
		5 => 'Apèro',
	];

	foreach ( $kategorien as $reihenfolge => $name ) {
		$slug = sanitize_title( $name );
		if ( ! term_exists( $slug, 'getraenke_kategorie' ) ) {
			wp_insert_term( $name, 'getraenke_kategorie', [ 'slug' => $slug ] );
		}
		$term = get_term_by( 'slug', $slug, 'getraenke_kategorie' );
		if ( $term ) {
			update_term_meta( $term->term_id, 'vietdura_order', $reihenfolge );
		}
	}
}
add_action( 'init', 'vietdura_setup_getraenke_kategorien', 20 );


/*
 * ACF-Felder für Getränke (via ACF-Plugin konfigurieren):
 *
 * Feldgruppe: "Getränk Details"
 * Zeigen bei: Post Type = getraenk
 *
 * Felder:
 *   - preis        (Text, z.B. "5.50")
 *   - volumen      (Text, z.B. "3 dl", "0.5 l")
 *   - reihenfolge  (Number, für Sortierung)
 */
