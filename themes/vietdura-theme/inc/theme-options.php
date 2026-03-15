<?php
// ACF Options Pages für VietDura

add_action( 'acf/init', 'vietdura_register_options_pages' );

function vietdura_register_options_pages() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	// Hauptseite im Admin-Menü (redirect zur ersten Unterseite)
	acf_add_options_page( [
		'page_title'  => 'VietDura Einstellungen',
		'menu_title'  => 'VietDura',
		'menu_slug'   => 'vietdura-einstellungen',
		'capability'  => 'manage_options',
		'icon_url'    => 'dashicons-restaurant',
		'position'    => 3,
		'redirect'    => true,
	] );

	// Unterseite: Startseite (Hero)
	acf_add_options_sub_page( [
		'page_title'  => 'Startseite',
		'menu_title'  => 'Startseite',
		'menu_slug'   => 'vietdura-startseite',
		'parent_slug' => 'vietdura-einstellungen',
	] );

	// Unterseite: Kontakt & Öffnungszeiten
	acf_add_options_sub_page( [
		'page_title'  => 'Kontakt & Öffnungszeiten',
		'menu_title'  => 'Kontakt',
		'menu_slug'   => 'vietdura-kontakt',
		'parent_slug' => 'vietdura-einstellungen',
	] );

	// Unterseite: Deklaration / Herkunft
	acf_add_options_sub_page( [
		'page_title'  => 'Deklaration & Herkunft',
		'menu_title'  => 'Deklaration',
		'menu_slug'   => 'vietdura-deklaration',
		'parent_slug' => 'vietdura-einstellungen',
	] );
}
