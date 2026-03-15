<?php
// ─── VietDura Manager Rolle ────────────────────────────────────────────────────
//
// Wird einmalig beim Theme-Aktivieren angelegt und bei Deaktivierung entfernt.
// Beim Update (Capabilities geändert) wird die Rolle neu gesetzt.

function vietdura_register_role(): void {
	$caps = [
		// WordPress Core: Medien
		'upload_files'           => true,

		// WordPress Core: Seiten vollständig bearbeiten
		'edit_pages'             => true,
		'edit_published_pages'   => true,
		'edit_others_pages'      => true,
		'edit_private_pages'     => true,
		'publish_pages'          => true,
		'read_private_pages'     => true,
		'read'                   => true,

		// Speisen – alle CRUD-Rechte
		'edit_speise'            => true,
		'read_speise'            => true,
		'delete_speise'          => true,
		'edit_speisen'           => true,
		'edit_others_speisen'    => true,
		'publish_speisen'        => true,
		'read_private_speisen'   => true,
		'delete_speisen'         => true,
		'delete_private_speisen' => true,
		'delete_published_speisen' => true,
		'delete_others_speisen'  => true,
		'edit_private_speisen'   => true,
		'edit_published_speisen' => true,

		// Getränke – alle CRUD-Rechte
		'edit_getraenk'              => true,
		'read_getraenk'              => true,
		'delete_getraenk'            => true,
		'edit_getraenke'             => true,
		'edit_others_getraenke'      => true,
		'publish_getraenke'          => true,
		'read_private_getraenke'     => true,
		'delete_getraenke'           => true,
		'delete_private_getraenke'   => true,
		'delete_published_getraenke' => true,
		'delete_others_getraenke'    => true,
		'edit_private_getraenke'     => true,
		'edit_published_getraenke'   => true,

		// Taxonomien (Kategorien für Speisen/Getränke) verwalten
		'manage_categories'      => true,

		// Kommentare / Nachrichten moderieren
		'moderate_comments'      => true,
		'edit_comment'           => true,

		// KEIN Zugriff auf:
		// activate_plugins, switch_themes, edit_themes, manage_options,
		// edit_users, delete_users, create_users, update_plugins, install_plugins
	];

	$existing = get_role( 'vietdura_manager' );

	if ( $existing ) {
		// Capabilities auf aktuellem Stand halten
		foreach ( $caps as $cap => $grant ) {
			$existing->add_cap( $cap, $grant );
		}
	} else {
		add_role( 'vietdura_manager', 'VietDura Manager', $caps );
	}
}

// Beim Laden des Themes sicherstellen dass die Rolle existiert
add_action( 'after_setup_theme', 'vietdura_register_role' );


// ─── Administrator: CPT-Capabilities hinzufügen ───────────────────────────────

function vietdura_admin_caps(): void {
	$admin = get_role( 'administrator' );
	if ( ! $admin ) return;

	$caps = [
		// Speisen
		'edit_speise', 'read_speise', 'delete_speise',
		'edit_speisen', 'edit_others_speisen', 'publish_speisen',
		'read_private_speisen', 'delete_speisen', 'delete_private_speisen',
		'delete_published_speisen', 'delete_others_speisen',
		'edit_private_speisen', 'edit_published_speisen',
		// Getränke
		'edit_getraenk', 'read_getraenk', 'delete_getraenk',
		'edit_getraenke', 'edit_others_getraenke', 'publish_getraenke',
		'read_private_getraenke', 'delete_getraenke', 'delete_private_getraenke',
		'delete_published_getraenke', 'delete_others_getraenke',
		'edit_private_getraenke', 'edit_published_getraenke',
	];

	foreach ( $caps as $cap ) {
		$admin->add_cap( $cap, true );
	}
}
add_action( 'after_setup_theme', 'vietdura_admin_caps' );

// Hinweis: WordPress hat keinen theme-deactivation hook.
// Rolle kann manuell entfernt werden via WP-CLI: wp role delete vietdura_manager
