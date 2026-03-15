<?php
/**
 * Bestellabwicklung: Zweistufig
 * Schritt 1 (vd_order_prepare): Bestellung speichern, Nummer generieren, Twint-Info anzeigen
 * Schritt 2 (vd_order_confirm): Kunde bestätigt Zahlung → WhatsApp ans Restaurant
 */

// ── Schritt 1: Bestellung vorbereiten ─────────────────────────────────────────

add_action( 'wp_ajax_vd_order_prepare',        'vd_ajax_order_prepare' );
add_action( 'wp_ajax_nopriv_vd_order_prepare', 'vd_ajax_order_prepare' );

function vd_ajax_order_prepare(): void {
	check_ajax_referer( 'vd_cart_nonce', 'nonce' );

	// Spam-Schutz: max. 3 Bestellungen pro IP pro Stunde
	$ip       = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' );
	$rate_key = 'vd_rate_' . md5( $ip );
	$rate_cnt = (int) get_transient( $rate_key );
	if ( $rate_cnt >= 3 ) {
		wp_send_json_error( 'Zu viele Bestellungen. Bitte warte eine Stunde und versuche es erneut.' );
	}

	// Eingaben validieren
	$name      = sanitize_text_field( $_POST['name']      ?? '' );
	$telefon   = sanitize_text_field( $_POST['telefon']   ?? '' );
	$abholung  = sanitize_text_field( $_POST['abholung']  ?? '' );
	$zahlung   = sanitize_text_field( $_POST['zahlung']   ?? '' );
	$bemerkung = sanitize_textarea_field( $_POST['bemerkung'] ?? '' );

	if ( ! $name || ! $telefon || ! $abholung || ! $zahlung ) {
		wp_send_json_error( 'Bitte alle Pflichtfelder ausfüllen.' );
	}

	$allowed_zahlungen = [ 'twint', 'twint_voll' ];
	if ( ! in_array( $zahlung, $allowed_zahlungen, true ) ) {
		wp_send_json_error( 'Ungültige Zahlungsart.' );
	}

	// Warenkorb prüfen
	$cart  = vd_cart_get();
	$total = vd_cart_total();

	if ( empty( $cart ) ) {
		wp_send_json_error( 'Der Warenkorb ist leer.' );
	}

	if ( $total < VD_MIN_ORDER ) {
		wp_send_json_error( 'Mindestbestellwert von CHF ' . number_format( VD_MIN_ORDER, 2, '.', "'" ) . ' nicht erreicht.' );
	}

	// Bestellnummer generieren
	$bestell_nr = 'VD-' . strtoupper( wp_generate_password( 5, false ) );

	// Bestellung als CPT speichern (Status: warte_auf_zahlung)
	$post_id = wp_insert_post( [
		'post_type'   => 'bestellung',
		'post_title'  => $bestell_nr,
		'post_status' => 'publish',
	] );

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( 'Bestellung konnte nicht gespeichert werden.' );
	}

	$zahlung_label = $zahlung === 'twint_voll' ? 'Twint (Vollbetrag)' : 'Twint-Anzahlung + Rest bar bei Abholung';
	$twint_nummer  = get_option( 'vd_twint_nummer', '' );
	$anzahlung     = $zahlung === 'twint_voll' ? $total : VD_MIN_ORDER;

	update_post_meta( $post_id, '_vd_status',     'warte_auf_zahlung' );
	update_post_meta( $post_id, '_vd_name',       $name );
	update_post_meta( $post_id, '_vd_telefon',    $telefon );
	update_post_meta( $post_id, '_vd_abholung',   $abholung );
	update_post_meta( $post_id, '_vd_zahlung',    $zahlung );
	update_post_meta( $post_id, '_vd_bemerkung',  $bemerkung );
	update_post_meta( $post_id, '_vd_total',      $total );
	update_post_meta( $post_id, '_vd_positionen', array_values( $cart ) );

	// Rate-Counter erhöhen
	set_transient( $rate_key, $rate_cnt + 1, HOUR_IN_SECONDS );

	// Warenkorb leeren
	vd_cart_clear();

	// Nonce für Schritt 2
	$confirm_nonce = wp_create_nonce( 'vd_order_confirm_' . $post_id );

	wp_send_json_success( [
		'post_id'       => $post_id,
		'bestell_nr'    => $bestell_nr,
		'total'         => $total,
		'anzahlung'     => $anzahlung,
		'twint_nummer'  => $twint_nummer,
		'zahlung'       => $zahlung,
		'confirm_nonce' => $confirm_nonce,
	] );
}


// ── Schritt 2: Zahlung bestätigen → WhatsApp senden ───────────────────────────

add_action( 'wp_ajax_vd_order_confirm',        'vd_ajax_order_confirm' );
add_action( 'wp_ajax_nopriv_vd_order_confirm', 'vd_ajax_order_confirm' );

function vd_ajax_order_confirm(): void {
	$post_id = (int) ( $_POST['post_id'] ?? 0 );
	if ( ! $post_id ) {
		wp_send_json_error( 'Ungültige Bestellung.' );
	}

	check_ajax_referer( 'vd_order_confirm_' . $post_id, 'nonce' );

	// Nur wenn noch wartend
	$status = get_post_meta( $post_id, '_vd_status', true );
	if ( $status !== 'warte_auf_zahlung' ) {
		wp_send_json_error( 'Bestellung wurde bereits bestätigt.' );
	}

	// Status aktualisieren
	update_post_meta( $post_id, '_vd_status', 'neu' );

	// Bestelldaten laden
	$bestell_nr = get_the_title( $post_id );
	$name       = get_post_meta( $post_id, '_vd_name',       true );
	$telefon    = get_post_meta( $post_id, '_vd_telefon',    true );
	$abholung   = get_post_meta( $post_id, '_vd_abholung',   true );
	$zahlung    = get_post_meta( $post_id, '_vd_zahlung',    true );
	$bemerkung  = get_post_meta( $post_id, '_vd_bemerkung',  true );
	$total      = (float) get_post_meta( $post_id, '_vd_total', true );
	$positionen = get_post_meta( $post_id, '_vd_positionen', true );

	$zahlung_label = $zahlung === 'twint_voll' ? 'Twint (Vollbetrag)' : 'Twint-Anzahlung + Rest bar bei Abholung';

	// WhatsApp ans Restaurant
	vd_sevenio_whatsapp( $bestell_nr, $name, $telefon, $abholung, $zahlung_label, $zahlung, $total, (array) $positionen, $bemerkung );

	// E-Mail ans Restaurant
	vd_send_restaurant_email( $bestell_nr, $name, $telefon, $abholung, $zahlung_label, $bemerkung, $total, (array) $positionen );

	wp_send_json_success( [
		'bestell_nr' => $bestell_nr,
		'message'    => 'Bestellung bestätigt!',
	] );
}


// ── WhatsApp via seven.io ──────────────────────────────────────────────────────

function vd_sevenio_whatsapp( string $bestell_nr, string $name, string $telefon, string $abholung, string $zahlung_label, string $zahlung, float $total, array $cart, string $bemerkung = '' ): void {
	$api_key = get_option( 'vd_sevenio_api_key', '' );
	$to      = get_option( 'vd_restaurant_telefon', '' );
	$channel = get_option( 'vd_notify_channel', 'whatsapp' );

	if ( ! $api_key || ! $to ) return;

	$artikel = '';
	foreach ( $cart as $item ) {
		$artikel .= $item['menge'] . '× ' . $item['name'] . "\n";
	}

	$twint_nummer = get_option( 'vd_twint_nummer', '' );
	if ( $zahlung === 'twint_voll' ) {
		$zahlung_hinweis = "Twint-Vollbetrag: CHF " . number_format( $total, 2, '.', "'" );
	} else {
		$rest            = round( $total - VD_MIN_ORDER, 2 );
		$zahlung_hinweis = "Twint-Anzahlung: CHF " . number_format( VD_MIN_ORDER, 2, '.', "'" )
			. ( $rest > 0 ? "\nRest bei Abholung: CHF " . number_format( $rest, 2, '.', "'" ) : '' );
	}

	$text = "✅ NEUE BESTELLUNG BEZAHLT\n"
		. "🔔 {$bestell_nr}\n"
		. "────────────────\n"
		. "👤 {$name}\n"
		. "📞 {$telefon}\n"
		. "⏰ {$abholung}\n"
		. "────────────────\n"
		. $artikel
		. "────────────────\n"
		. "💰 Total: CHF " . number_format( $total, 2, '.', "'" ) . "\n"
		. "💳 " . $zahlung_hinweis
		. ( $bemerkung ? "\n📝 " . $bemerkung : '' );

	$endpoint = $channel === 'whatsapp'
		? 'https://gateway.seven.io/api/whatsapp/message'
		: 'https://gateway.seven.io/api/sms';

	wp_remote_post( $endpoint, [
		'headers' => [
			'X-Api-Key'    => $api_key,
			'Content-Type' => 'application/json',
		],
		'body'    => wp_json_encode( [ 'to' => $to, 'text' => $text ] ),
		'timeout' => 10,
	] );
}


// ── E-Mail ans Restaurant ──────────────────────────────────────────────────────

function vd_send_restaurant_email( string $bestell_nr, string $name, string $telefon, string $abholung, string $zahlung_label, string $bemerkung, float $total, array $positionen ): void {
	$restaurant_name  = get_bloginfo( 'name' );
	$restaurant_email = get_option( 'vd_restaurant_email' ) ?: get_option( 'admin_email' );

	$artikel_html = '<table style="width:100%;border-collapse:collapse;font-size:14px;">';
	$artikel_html .= '<tr style="background:#f5f5f5;"><th style="padding:8px;text-align:left;">Artikel</th><th style="padding:8px;text-align:center;">Menge</th><th style="padding:8px;text-align:right;">Preis</th></tr>';
	foreach ( $positionen as $item ) {
		$zeile = (float) $item['preis'] * (int) $item['menge'];
		$artikel_html .= '<tr style="border-bottom:1px solid #eee;">';
		$artikel_html .= '<td style="padding:8px;">' . esc_html( $item['name'] ) . '</td>';
		$artikel_html .= '<td style="padding:8px;text-align:center;">' . (int) $item['menge'] . '×</td>';
		$artikel_html .= '<td style="padding:8px;text-align:right;">CHF ' . number_format( $zeile, 2, '.', "'" ) . '</td>';
		$artikel_html .= '</tr>';
	}
	$artikel_html .= '<tr style="font-weight:bold;background:#f5f5f5;"><td colspan="2" style="padding:10px;">Total</td><td style="padding:10px;text-align:right;">CHF ' . number_format( $total, 2, '.', "'" ) . '</td></tr>';
	$artikel_html .= '</table>';

	$betreff = '✅ Neue Bestellung ' . $bestell_nr . ' – ' . $name;
	$html    = vd_email_template( 'Neue Bestellung (Zahlung bestätigt)', '
		<p><strong>Neue Bestellung — Twint-Zahlung bestätigt!</strong></p>
		<table style="width:100%;margin:16px 0;border-collapse:collapse;">
			<tr><td style="padding:6px 0;color:#666;width:140px;">Bestell-Nr.</td><td style="padding:6px 0;font-weight:600;">' . esc_html( $bestell_nr ) . '</td></tr>
			<tr><td style="padding:6px 0;color:#666;">Name</td><td style="padding:6px 0;">' . esc_html( $name ) . '</td></tr>
			<tr><td style="padding:6px 0;color:#666;">Telefon</td><td style="padding:6px 0;"><a href="tel:' . esc_attr( $telefon ) . '">' . esc_html( $telefon ) . '</a></td></tr>
			<tr><td style="padding:6px 0;color:#666;">Abholung</td><td style="padding:6px 0;">' . esc_html( $abholung ) . '</td></tr>
			<tr><td style="padding:6px 0;color:#666;">Zahlung</td><td style="padding:6px 0;">' . esc_html( $zahlung_label ) . '</td></tr>
			' . ( $bemerkung ? '<tr><td style="padding:6px 0;color:#666;">Bemerkung</td><td style="padding:6px 0;">' . esc_html( $bemerkung ) . '</td></tr>' : '' ) . '
		</table>
		<h3 style="font-family:Georgia,serif;font-size:18px;margin-bottom:12px;">Bestellte Artikel</h3>
		' . $artikel_html . '
	' );

	wp_mail( $restaurant_email, $betreff, $html, [ 'Content-Type: text/html; charset=UTF-8' ] );
}


// ── E-Mail-Template ───────────────────────────────────────────────────────────

function vd_email_template( string $titel, string $inhalt ): string {
	$restaurant = get_bloginfo( 'name' );
	return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,sans-serif;">
	<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:40px 20px;">
	<tr><td align="center">
	<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;max-width:600px;width:100%;">
		<tr><td style="background:#2f6b55;padding:32px 40px;">
			<h1 style="color:#ffffff;font-family:Georgia,serif;font-size:24px;margin:0;">' . esc_html( $restaurant ) . '</h1>
			<p style="color:rgba(255,255,255,0.8);margin:8px 0 0;font-size:14px;">' . esc_html( $titel ) . '</p>
		</td></tr>
		<tr><td style="padding:40px;">' . $inhalt . '</td></tr>
		<tr><td style="background:#f5f5f5;padding:20px 40px;text-align:center;color:#999;font-size:12px;">
			© ' . date( 'Y' ) . ' ' . esc_html( $restaurant ) . '
		</td></tr>
	</table>
	</td></tr></table>
	</body></html>';
}
