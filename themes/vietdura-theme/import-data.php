<?php
/**
 * VietDura Einmal-Import: Speisen + Getränke
 * Liest direkt aus data/vietdura_speisen_import.csv und data/vietdura_getraenke_import.csv
 *
 * WICHTIG: Diese Datei nach dem Import sofort löschen!
 *
 * Aufruf: http://vietdurach.local/wp-content/themes/vietdura-theme/import-data.php
 * Voraussetzung: Als Administrator in WordPress eingeloggt sein.
 */

// WordPress laden
$wp_load = __DIR__ . '/../../../wp-load.php';

if ( ! file_exists( $wp_load ) ) {
	exit( 'wp-load.php nicht gefunden: ' . $wp_load );
}

require_once $wp_load;

// Zugriffskontrolle
if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Zugriff verweigert.' );
}

// Bereits ausgeführt?
if ( get_option( 'vietdura_import_done' ) ) {
	wp_die( '⚠ Import wurde bereits ausgeführt. Datei löschen: import-data.php' );
}

$log     = [];
$counts  = [ 'ok' => 0, 'skip' => 0, 'error' => 0 ];
$base    = __DIR__ . '/data/';


// ─────────────────────────────────────────────────────────────────────────────
// HILFSFUNKTIONEN
// ─────────────────────────────────────────────────────────────────────────────

/**
 * CSV-Datei einlesen → Array of assoc arrays.
 */
function vd_read_csv( string $path ): array {
	if ( ! file_exists( $path ) ) {
		return [];
	}
	$rows   = [];
	$handle = fopen( $path, 'r' );
	// BOM entfernen
	$bom = fread( $handle, 3 );
	if ( $bom !== "\xEF\xBB\xBF" ) {
		fseek( $handle, 0 );
	}
	$header = fgetcsv( $handle );
	while ( ( $row = fgetcsv( $handle ) ) !== false ) {
		if ( count( $row ) === count( $header ) ) {
			$rows[] = array_combine( $header, $row );
		}
	}
	fclose( $handle );
	return $rows;
}

/**
 * Term holen oder anlegen, gibt term_id zurück.
 */
function vd_get_or_create_term( string $name, string $taxonomy ): int {
	$slug     = sanitize_title( $name );
	$existing = get_term_by( 'slug', $slug, $taxonomy );
	if ( $existing ) {
		return (int) $existing->term_id;
	}
	$result = wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
	if ( is_wp_error( $result ) ) {
		return 0;
	}
	return (int) $result['term_id'];
}

/**
 * Duplikat-Check: Titel + post_type + Taxonomie-Term.
 * Verhindert doppelte Einträge auch bei gleichem Titel in verschiedenen Kategorien nicht.
 */
function vd_post_exists( string $title, string $post_type, int $term_id, string $taxonomy ): bool {
	$args = [
		'post_type'   => $post_type,
		'post_status' => 'any',
		'fields'      => 'ids',
		'numberposts' => 1,
		'title'       => $title,
	];
	if ( $term_id ) {
		$args['tax_query'] = [ [
			'taxonomy' => $taxonomy,
			'field'    => 'term_id',
			'terms'    => $term_id,
		] ];
	}
	return ! empty( get_posts( $args ) );
}

/**
 * Post anlegen und ACF-Felder setzen.
 */
function vd_insert_post( array $post_data, array $acf, string $taxonomy, int $term_id ): string {
	$post_id = wp_insert_post( $post_data, true );
	if ( is_wp_error( $post_id ) ) {
		return 'ERROR: ' . $post_id->get_error_message();
	}
	if ( $term_id ) {
		wp_set_post_terms( $post_id, [ $term_id ], $taxonomy );
	}
	foreach ( $acf as $key => $value ) {
		if ( $value === null || $value === '' ) {
			continue;
		}
		if ( function_exists( 'update_field' ) ) {
			update_field( $key, $value, $post_id );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
	return 'OK (ID ' . $post_id . ')';
}

/**
 * Kategorien-Mapping für Getränke: Wein-Unterkategorien → Wein Welt.
 */
function vd_map_getraenk_kategorie( string $raw ): string {
	$map = [
		'Wein Welt - Rotwein'   => 'Wein Welt',
		'Wein Welt - Weisswein' => 'Wein Welt',
		'Wein Welt - Rosewein'  => 'Wein Welt',
		'Wein Welt - Schaumwein'=> 'Wein Welt',
		'Warme Getränke'        => 'warme Getränke',
	];
	return $map[ $raw ] ?? $raw;
}

/**
 * Weinart aus Kategorie-Rohwert extrahieren (für Excerpt-Prefix).
 */
function vd_wein_art( string $raw ): string {
	$map = [
		'Wein Welt - Rotwein'    => 'Rotwein',
		'Wein Welt - Weisswein'  => 'Weisswein',
		'Wein Welt - Rosewein'   => 'Rosé',
		'Wein Welt - Schaumwein' => 'Schaumwein',
	];
	return $map[ $raw ] ?? '';
}


// ─────────────────────────────────────────────────────────────────────────────
// SPEISEN IMPORT
// ─────────────────────────────────────────────────────────────────────────────

$log[] = '═══════════════════════════════════════════';
$log[] = ' SPEISEN';
$log[] = '═══════════════════════════════════════════';

$speisen_rows = vd_read_csv( $base . 'vietdura_speisen_import.csv' );

if ( empty( $speisen_rows ) ) {
	$log[] = '  FEHLER: CSV nicht gefunden oder leer.';
} else {
	foreach ( $speisen_rows as $row ) {
		$title    = trim( $row['title'] );
		$cat      = trim( $row['category'] );
		$num      = trim( $row['menu_number'] );
		$excerpt  = trim( $row['excerpt'] );
		$price    = trim( $row['price_chf'] );
		$highlight = ( trim( $row['highlight_home'] ) === '1' ) ? 1 : 0;

		$badges = [];
		if ( trim( $row['badge_vegetarisch'] ) === '1' ) $badges[] = 'vegetarisch';
		if ( trim( $row['badge_vegan'] )       === '1' ) $badges[] = 'vegan';
		if ( trim( $row['badge_scharf'] )       === '1' ) $badges[] = 'scharf';

		$term_id = vd_get_or_create_term( $cat, 'speisen_kategorie' );

		if ( vd_post_exists( $title, 'speise', $term_id, 'speisen_kategorie' ) ) {
			$log[] = "  SKIP   [{$cat}] {$title}";
			$counts['skip']++;
			continue;
		}

		$result = vd_insert_post(
			[
				'post_type'    => 'speise',
				'post_title'   => $title,
				'post_excerpt' => $excerpt,
				'post_status'  => 'publish',
				'menu_order'   => (int) $num,
			],
			[
				'nummer'    => $num,
				'preis'     => $price,
				'highlight' => $highlight,
				'badges'    => $badges,
			],
			'speisen_kategorie',
			$term_id
		);

		$status = str_starts_with( $result, 'OK' ) ? 'OK    ' : 'ERROR ';
		$log[]  = "  {$status} [{$cat}] {$title} — CHF {$price} — {$result}";
		str_starts_with( $result, 'OK' ) ? $counts['ok']++ : $counts['error']++;
	}
}


// ─────────────────────────────────────────────────────────────────────────────
// GETRÄNKE IMPORT
// ─────────────────────────────────────────────────────────────────────────────

$log[] = '';
$log[] = '═══════════════════════════════════════════';
$log[] = ' GETRÄNKE';
$log[] = '═══════════════════════════════════════════';

$getraenke_rows = vd_read_csv( $base . 'vietdura_getraenke_import.csv' );

if ( empty( $getraenke_rows ) ) {
	$log[] = '  FEHLER: CSV nicht gefunden oder leer.';
} else {
	foreach ( $getraenke_rows as $row ) {
		$title_raw = trim( $row['title'] );
		$cat_raw   = trim( $row['category'] );
		$vol       = trim( $row['volume'] );
		$price     = trim( $row['price_chf'] );
		$excerpt   = trim( $row['excerpt'] );

		// Kategorie normalisieren
		$cat = vd_map_getraenk_kategorie( $cat_raw );

		// Weinart als Prefix im Excerpt wenn Wein-Unterkategorie
		$wein_art = vd_wein_art( $cat_raw );
		if ( $wein_art && $excerpt ) {
			$excerpt = $wein_art . '. ' . $excerpt;
		} elseif ( $wein_art ) {
			$excerpt = $wein_art . '.';
		}

		// Titel: Volumen anhängen wenn vorhanden (verhindert Duplikate bei Wein dl/Flasche)
		$title = $vol ? "{$title_raw} ({$vol})" : $title_raw;

		$term_id = vd_get_or_create_term( $cat, 'getraenke_kategorie' );

		if ( vd_post_exists( $title, 'getraenk', $term_id, 'getraenke_kategorie' ) ) {
			$log[] = "  SKIP   [{$cat}] {$title}";
			$counts['skip']++;
			continue;
		}

		$result = vd_insert_post(
			[
				'post_type'    => 'getraenk',
				'post_title'   => $title,
				'post_excerpt' => $excerpt,
				'post_status'  => 'publish',
			],
			[
				'preis'   => $price,
				'volumen' => $vol,
			],
			'getraenke_kategorie',
			$term_id
		);

		$status = str_starts_with( $result, 'OK' ) ? 'OK    ' : 'ERROR ';
		$log[]  = "  {$status} [{$cat}] {$title} — CHF {$price} — {$result}";
		str_starts_with( $result, 'OK' ) ? $counts['ok']++ : $counts['error']++;
	}
}


// ─────────────────────────────────────────────────────────────────────────────
// ABSCHLUSS
// ─────────────────────────────────────────────────────────────────────────────

update_option( 'vietdura_import_done', true );

$log[] = '';
$log[] = '═══════════════════════════════════════════';
$log[] = " FERTIG: {$counts['ok']} importiert · {$counts['skip']} übersprungen · {$counts['error']} Fehler";
$log[] = '═══════════════════════════════════════════';
$log[] = '⚠  BITTE DIESE DATEI JETZT LÖSCHEN: import-data.php';

header( 'Content-Type: text/plain; charset=utf-8' );
echo implode( "\n", $log );
