<?php
/**
 * Warenkorb-Logik (Session-basiert)
 * Mindestbestellwert: CHF 20.–
 */

define( 'VD_MIN_ORDER', 20.00 );

// Session starten
add_action( 'init', function () {
	if ( ! session_id() && ! headers_sent() ) {
		session_start();
	}
} );


// ── Warenkorb-Helfer ──────────────────────────────────────────────────────────

function vd_cart_get(): array {
	return $_SESSION['vd_cart'] ?? [];
}

function vd_cart_total(): float {
	$total = 0.0;
	foreach ( vd_cart_get() as $item ) {
		$total += (float) $item['preis'] * (int) $item['menge'];
	}
	return $total;
}

function vd_cart_count(): int {
	$count = 0;
	foreach ( vd_cart_get() as $item ) {
		$count += (int) $item['menge'];
	}
	return $count;
}

function vd_cart_clear(): void {
	$_SESSION['vd_cart'] = [];
}


// ── AJAX: Artikel hinzufügen ──────────────────────────────────────────────────

add_action( 'wp_ajax_vd_cart_add',        'vd_ajax_cart_add' );
add_action( 'wp_ajax_nopriv_vd_cart_add', 'vd_ajax_cart_add' );

function vd_ajax_cart_add(): void {
	check_ajax_referer( 'vd_cart_nonce', 'nonce' );

	$post_id = (int) ( $_POST['post_id'] ?? 0 );
	$type    = sanitize_key( $_POST['type'] ?? 'speise' ); // speise | getraenk

	if ( ! $post_id ) {
		wp_send_json_error( 'Ungültige ID' );
	}

	$post = get_post( $post_id );
	if ( ! $post || ! in_array( $post->post_type, [ 'speise', 'getraenk' ], true ) ) {
		wp_send_json_error( 'Artikel nicht gefunden' );
	}

	$preis_raw = get_post_meta( $post_id, 'preis', true );
	$preis     = (float) str_replace( [ "'", ',', ' ' ], [ '', '.', '' ], (string) $preis_raw );

	if ( ! $preis ) {
		wp_send_json_error( 'Kein Preis hinterlegt' );
	}

	$cart = vd_cart_get();
	$key  = 'item_' . $post_id;

	if ( isset( $cart[ $key ] ) ) {
		$cart[ $key ]['menge']++;
	} else {
		$cart[ $key ] = [
			'post_id' => $post_id,
			'name'    => get_the_title( $post_id ),
			'preis'   => $preis,
			'menge'   => 1,
			'type'    => $type,
		];
	}

	$_SESSION['vd_cart'] = $cart;

	wp_send_json_success( [
		'count'   => vd_cart_count(),
		'total'   => vd_cart_total(),
		'min_ok'  => vd_cart_total() >= VD_MIN_ORDER,
	] );
}


// ── AJAX: Menge ändern ────────────────────────────────────────────────────────

add_action( 'wp_ajax_vd_cart_update',        'vd_ajax_cart_update' );
add_action( 'wp_ajax_nopriv_vd_cart_update', 'vd_ajax_cart_update' );

function vd_ajax_cart_update(): void {
	check_ajax_referer( 'vd_cart_nonce', 'nonce' );

	$post_id = (int) ( $_POST['post_id'] ?? 0 );
	$menge   = (int) ( $_POST['menge']   ?? 0 );
	$key     = 'item_' . $post_id;
	$cart    = vd_cart_get();

	if ( $menge <= 0 ) {
		unset( $cart[ $key ] );
	} elseif ( isset( $cart[ $key ] ) ) {
		$cart[ $key ]['menge'] = $menge;
	}

	$_SESSION['vd_cart'] = $cart;

	wp_send_json_success( [
		'count'  => vd_cart_count(),
		'total'  => vd_cart_total(),
		'min_ok' => vd_cart_total() >= VD_MIN_ORDER,
		'cart'   => array_values( $cart ),
	] );
}


// ── AJAX: Warenkorb abrufen ───────────────────────────────────────────────────

add_action( 'wp_ajax_vd_cart_get',        'vd_ajax_cart_get' );
add_action( 'wp_ajax_nopriv_vd_cart_get', 'vd_ajax_cart_get' );

function vd_ajax_cart_get(): void {
	check_ajax_referer( 'vd_cart_nonce', 'nonce' );

	wp_send_json_success( [
		'count'  => vd_cart_count(),
		'total'  => vd_cart_total(),
		'min_ok' => vd_cart_total() >= VD_MIN_ORDER,
		'cart'   => array_values( vd_cart_get() ),
	] );
}


// ── JS-Variablen ausgeben ─────────────────────────────────────────────────────

add_action( 'wp_enqueue_scripts', function () {
	wp_localize_script( 'vietdura-cart', 'vdCart', [
		'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
		'nonce'     => wp_create_nonce( 'vd_cart_nonce' ),
		'minOrder'  => VD_MIN_ORDER,
		'cartUrl'   => home_url( '/bestellung/' ),
		'count'     => vd_cart_count(),
		'total'     => vd_cart_total(),
		'minOk'     => vd_cart_total() >= VD_MIN_ORDER,
	] );
}, 20 );
