<?php
// Custom Post Type: Speisen

function vietdura_register_cpt_speise() {
	$labels = [
		'name'               => 'Speisen',
		'singular_name'      => 'Speise',
		'add_new'            => 'Neue Speise',
		'add_new_item'       => 'Neue Speise hinzufügen',
		'edit_item'          => 'Speise bearbeiten',
		'new_item'           => 'Neue Speise',
		'view_item'          => 'Speise ansehen',
		'search_items'       => 'Speisen suchen',
		'not_found'          => 'Keine Speisen gefunden',
		'not_found_in_trash' => 'Keine Speisen im Papierkorb',
		'all_items'          => 'Alle Speisen',
		'menu_name'          => 'Speisen',
	];

	register_post_type( 'speise', [
		'labels'            => $labels,
		'public'            => true,
		'has_archive'       => false,
		'show_in_rest'      => true,
		'supports'          => [ 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ],
		'menu_icon'         => 'dashicons-food',
		'menu_position'     => 5,
		'rewrite'           => [ 'slug' => 'speisen' ],
		'capability_type'   => [ 'speise', 'speisen' ],
		'map_meta_cap'      => true,
	] );
}
add_action( 'init', 'vietdura_register_cpt_speise' );


// Taxonomie: Speisen-Kategorien

function vietdura_register_tax_speisen_kategorie() {
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

	register_taxonomy( 'speisen_kategorie', 'speise', [
		'labels'       => $labels,
		'hierarchical' => true,
		'show_in_rest' => true,
		'rewrite'      => [ 'slug' => 'speisen-kategorie' ],
		'capabilities' => [
			'manage_terms' => 'manage_categories',
			'edit_terms'   => 'manage_categories',
			'delete_terms' => 'manage_categories',
			'assign_terms' => 'edit_speisen',
		],
	] );
}
add_action( 'init', 'vietdura_register_tax_speisen_kategorie' );


// Kategorien anlegen und Reihenfolge sicherstellen

function vietdura_setup_speisen_kategorien(): void {
	$kategorien = [
		1 => 'Vorspeisen',
		2 => 'Salate Hauptgang',
		3 => 'Hauptgang',
		4 => 'Suppen-Welt',
	];

	foreach ( $kategorien as $reihenfolge => $name ) {
		$slug = sanitize_title( $name );
		if ( ! term_exists( $slug, 'speisen_kategorie' ) ) {
			wp_insert_term( $name, 'speisen_kategorie', [ 'slug' => $slug ] );
		}
		$term = get_term_by( 'slug', $slug, 'speisen_kategorie' );
		if ( $term ) {
			update_term_meta( $term->term_id, 'vietdura_order', $reihenfolge );
		}
	}
}
add_action( 'init', 'vietdura_setup_speisen_kategorien', 20 );


// Bildgrösse für Speisenkarte

add_action( 'after_setup_theme', function () {
	add_image_size( 'speise-card', 600, 420, true );
} );


/*
 * ACF-Felder für Speisen (via ACF-Plugin konfigurieren):
 *
 * Feldgruppe: "Speise Details"
 * Zeigen bei: Post Type = speise
 *
 * Felder:
 *   - preis        (Text, z.B. "24.50")
 *   - badge        (Select: "", "beliebt", "haus-hit", "neu", "vegetarisch")
 *   - allergene    (Textarea, optional)
 *   - reihenfolge  (Number, für Sortierung)
 */
