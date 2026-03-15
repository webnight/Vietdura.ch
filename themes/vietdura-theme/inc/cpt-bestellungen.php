<?php
/**
 * CPT: Bestellungen
 * Speichert eingegangene Online-Bestellungen zur Verwaltung im Admin.
 */

// ── CPT Registration ──────────────────────────────────────────────────────────

add_action( 'init', 'vietdura_register_cpt_bestellungen' );

function vietdura_register_cpt_bestellungen(): void {
	register_post_type( 'bestellung', [
		'labels'        => [
			'name'               => 'Bestellungen',
			'singular_name'      => 'Bestellung',
			'menu_name'          => 'Bestellungen',
			'add_new'            => 'Neue Bestellung',
			'add_new_item'       => 'Neue Bestellung',
			'edit_item'          => 'Bestellung bearbeiten',
			'view_item'          => 'Bestellung ansehen',
			'search_items'       => 'Bestellungen suchen',
			'not_found'          => 'Keine Bestellungen gefunden',
			'not_found_in_trash' => 'Keine Bestellungen im Papierkorb',
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 4,
		'menu_icon'           => 'dashicons-cart',
		'supports'            => [ 'title' ],
		'capability_type'     => 'post',
		'capabilities'        => [
			'create_posts' => 'do_not_allow',
		],
		'map_meta_cap'        => true,
		'show_in_rest'        => false,
	] );
}


// ── Admin-Spalten ─────────────────────────────────────────────────────────────

add_filter( 'manage_bestellung_posts_columns', 'vietdura_bestellung_columns' );

function vietdura_bestellung_columns( array $cols ): array {
	return [
		'cb'          => $cols['cb'],
		'title'       => 'Bestell-Nr.',
		'vd_status'   => 'Status',
		'vd_name'     => 'Name',
		'vd_telefon'  => 'Telefon',
		'vd_abholung' => 'Abholung',
		'vd_total'    => 'Total',
		'date'        => 'Eingegangen',
	];
}

add_action( 'manage_bestellung_posts_custom_column', 'vietdura_bestellung_column_content', 10, 2 );

function vietdura_bestellung_column_content( string $col, int $post_id ): void {
	switch ( $col ) {
		case 'vd_status':
			$status = get_post_meta( $post_id, '_vd_status', true ) ?: 'neu';
			$labels = [
				'warte_auf_zahlung' => '<span style="background:#fff3cd;color:#856404;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600;">⏳ Warte auf Zahlung</span>',
				'neu'               => '<span style="background:#e8f4ef;color:#1f4d3a;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600;">✅ Zahlung bestätigt</span>',
				'bestaetigt'        => '<span style="background:#cce5ff;color:#004085;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600;">Bestätigt</span>',
				'bereit'            => '<span style="background:#d1ecf1;color:#0c5460;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600;">Bereit</span>',
				'abgeholt'          => '<span style="background:#d6d8db;color:#383d41;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600;">Abgeholt</span>',
			];
			echo $labels[ $status ] ?? esc_html( $status );
			break;
		case 'vd_name':
			echo esc_html( get_post_meta( $post_id, '_vd_name', true ) );
			break;
		case 'vd_telefon':
			$tel = get_post_meta( $post_id, '_vd_telefon', true );
			echo $tel ? '<a href="tel:' . esc_attr( $tel ) . '">' . esc_html( $tel ) . '</a>' : '—';
			break;
		case 'vd_abholung':
			echo esc_html( get_post_meta( $post_id, '_vd_abholung', true ) ?: '—' );
			break;
		case 'vd_total':
			$total = (float) get_post_meta( $post_id, '_vd_total', true );
			echo $total ? 'CHF ' . number_format( $total, 2, '.', "'" ) : '—';
			break;
	}
}


// ── Status-Feld im Edit-Screen ────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'vd_bestellung_details',
		'Bestelldetails',
		'vietdura_bestellung_meta_box',
		'bestellung',
		'normal',
		'high'
	);
} );

function vietdura_bestellung_meta_box( WP_Post $post ): void {
	$status    = get_post_meta( $post->ID, '_vd_status', true ) ?: 'neu';
	$name      = get_post_meta( $post->ID, '_vd_name', true );
	$telefon   = get_post_meta( $post->ID, '_vd_telefon', true );
	$abholung  = get_post_meta( $post->ID, '_vd_abholung', true );
	$zahlung   = get_post_meta( $post->ID, '_vd_zahlung', true );
	$bemerkung = get_post_meta( $post->ID, '_vd_bemerkung', true );
	$total     = (float) get_post_meta( $post->ID, '_vd_total', true );
	$positionen = get_post_meta( $post->ID, '_vd_positionen', true );

	wp_nonce_field( 'vd_bestellung_save', 'vd_bestellung_nonce' );
	?>
	<table class="form-table" style="margin-bottom:16px;">
		<tr>
			<th style="width:140px;">Status</th>
			<td>
				<select name="vd_status">
					<option value="neu"        <?php selected( $status, 'neu' ); ?>>Neu</option>
					<option value="bestaetigt" <?php selected( $status, 'bestaetigt' ); ?>>Bestätigt</option>
					<option value="bereit"     <?php selected( $status, 'bereit' ); ?>>Bereit zur Abholung</option>
					<option value="abgeholt"   <?php selected( $status, 'abgeholt' ); ?>>Abgeholt</option>
				</select>
			</td>
		</tr>
		<tr><th>Name</th><td><?php echo esc_html( $name ); ?></td></tr>
		<tr><th>Telefon</th><td><?php echo $telefon ? '<a href="tel:' . esc_attr( $telefon ) . '">' . esc_html( $telefon ) . '</a>' : '—'; ?></td></tr>
		<tr><th>Abholzeit</th><td><?php echo esc_html( $abholung ?: '—' ); ?></td></tr>
		<tr><th>Zahlung</th><td><?php echo esc_html( $zahlung ?: '—' ); ?></td></tr>
		<?php if ( $bemerkung ) : ?>
		<tr><th>Bemerkung</th><td><?php echo esc_html( $bemerkung ); ?></td></tr>
		<?php endif; ?>
		<tr>
			<th>Total</th>
			<td><strong>CHF <?php echo number_format( $total, 2, '.', "'" ); ?></strong></td>
		</tr>
	</table>

	<?php if ( ! empty( $positionen ) && is_array( $positionen ) ) : ?>
	<h4 style="margin:16px 0 8px;">Bestellte Artikel</h4>
	<table style="width:100%;border-collapse:collapse;font-size:13px;">
		<thead>
			<tr style="background:#f0f0f0;">
				<th style="padding:6px 10px;text-align:left;">Artikel</th>
				<th style="padding:6px 10px;text-align:center;width:60px;">Menge</th>
				<th style="padding:6px 10px;text-align:right;width:100px;">Preis</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $positionen as $pos ) : ?>
			<tr style="border-bottom:1px solid #eee;">
				<td style="padding:6px 10px;"><?php echo esc_html( $pos['name'] ); ?></td>
				<td style="padding:6px 10px;text-align:center;"><?php echo (int) $pos['menge']; ?>×</td>
				<td style="padding:6px 10px;text-align:right;">CHF <?php echo number_format( (float) $pos['preis'] * (int) $pos['menge'], 2, '.', "'" ); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>

	<?php
	$whatsapp_sent = get_post_meta( $post->ID, '_vd_whatsapp_sent', true );
	$api_key       = get_option( 'vd_sevenio_api_key', '' );
	$rest_telefon  = get_option( 'vd_restaurant_telefon', '' );
	?>
	<div style="margin-top:20px;padding-top:16px;border-top:1px solid #ddd;">
		<?php if ( $whatsapp_sent ) : ?>
			<p style="color:#1f4d3a;background:#e8f4ef;padding:10px 14px;border-radius:6px;margin:0;font-size:13px;">
				✅ WhatsApp wurde gesendet am <?php echo esc_html( date_i18n( 'd.m.Y H:i', (int) $whatsapp_sent ) ); ?>
			</p>
		<?php elseif ( $api_key && $rest_telefon ) : ?>
			<p style="font-size:13px;color:#555;margin:0 0 10px;">Twint-Zahlung eingegangen? Dann WhatsApp ans Restaurant senden:</p>
			<button type="button" id="vd-send-whatsapp"
				data-post-id="<?php echo esc_attr( $post->ID ); ?>"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'vd_send_whatsapp_' . $post->ID ) ); ?>"
				style="background:#25D366;color:#fff;border:none;padding:10px 20px;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:8px;">
				📲 Zahlung bestätigt — WhatsApp senden
			</button>
			<span id="vd-whatsapp-result" style="margin-left:12px;font-size:13px;"></span>
			<script>
			document.getElementById('vd-send-whatsapp').addEventListener('click', function() {
				var btn = this;
				btn.disabled = true;
				btn.textContent = 'Wird gesendet …';
				var result = document.getElementById('vd-whatsapp-result');

				fetch(ajaxurl, {
					method: 'POST',
					headers: {'Content-Type': 'application/x-www-form-urlencoded'},
					body: new URLSearchParams({
						action:   'vd_confirm_payment',
						post_id:  btn.dataset.postId,
						nonce:    btn.dataset.nonce,
					})
				})
				.then(r => r.json())
				.then(res => {
					if (res.success) {
						btn.style.background = '#888';
						btn.textContent = '✅ Gesendet';
						result.style.color = '#1f4d3a';
						result.textContent = res.data.message;
					} else {
						btn.disabled = false;
						btn.textContent = '📲 Zahlung bestätigt — WhatsApp senden';
						result.style.color = '#c0392b';
						result.textContent = res.data || 'Fehler beim Senden.';
					}
				});
			});
			</script>
		<?php else : ?>
			<p style="font-size:13px;color:#888;margin:0;">⚠️ seven.io API-Key oder Telefonnummer nicht konfiguriert (VietDura → Einstellungen).</p>
		<?php endif; ?>
	</div>
	<?php
}

add_action( 'save_post_bestellung', function ( int $post_id ) {
	if ( ! isset( $_POST['vd_bestellung_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['vd_bestellung_nonce'], 'vd_bestellung_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$allowed = [ 'neu', 'bestaetigt', 'bereit', 'abgeholt' ];
	$status  = sanitize_text_field( $_POST['vd_status'] ?? 'neu' );
	if ( in_array( $status, $allowed, true ) ) {
		update_post_meta( $post_id, '_vd_status', $status );
	}
} );


// ── AJAX: Zahlungsbestätigung + WhatsApp senden ───────────────────────────────

add_action( 'wp_ajax_vd_confirm_payment', 'vd_ajax_confirm_payment' );

function vd_ajax_confirm_payment(): void {
	$post_id = (int) ( $_POST['post_id'] ?? 0 );
	if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
		wp_send_json_error( 'Keine Berechtigung.' );
	}

	check_ajax_referer( 'vd_send_whatsapp_' . $post_id, 'nonce' );

	// Bereits gesendet?
	if ( get_post_meta( $post_id, '_vd_whatsapp_sent', true ) ) {
		wp_send_json_error( 'WhatsApp wurde bereits gesendet.' );
	}

	// Bestelldaten laden
	$bestell_nr = get_the_title( $post_id );
	$name       = get_post_meta( $post_id, '_vd_name',       true );
	$telefon    = get_post_meta( $post_id, '_vd_telefon',    true );
	$abholung   = get_post_meta( $post_id, '_vd_abholung',   true );
	$zahlung    = get_post_meta( $post_id, '_vd_zahlung',    true );
	$bemerkung  = get_post_meta( $post_id, '_vd_bemerkung',  true );
	$total      = (float) get_post_meta( $post_id, '_vd_total', true );
	$positionen = get_post_meta( $post_id, '_vd_positionen', true );

	$zahlung_label = $zahlung === 'twint_voll' ? 'Twint (Vollbetrag)' : 'Twint-Anzahlung + Rest bar';

	// Artikelliste
	$artikel = '';
	if ( is_array( $positionen ) ) {
		foreach ( $positionen as $item ) {
			$artikel .= $item['menge'] . '× ' . $item['name'] . "\n";
		}
	}

	$text = "✅ ZAHLUNG BESTÄTIGT\n"
		. "🔔 BESTELLUNG {$bestell_nr}\n"
		. "────────────────\n"
		. "👤 {$name} | 📞 {$telefon}\n"
		. "⏰ Abholung: {$abholung}\n"
		. "💳 Zahlung: {$zahlung_label}\n"
		. "────────────────\n"
		. $artikel
		. "────────────────\n"
		. "💰 Total: CHF " . number_format( $total, 2, '.', "'" )
		. ( $bemerkung ? "\n📝 " . $bemerkung : '' );

	$api_key = get_option( 'vd_sevenio_api_key', '' );
	$to      = get_option( 'vd_restaurant_telefon', '' );
	$channel = get_option( 'vd_notify_channel', 'whatsapp' );

	if ( ! $api_key || ! $to ) {
		wp_send_json_error( 'API-Key oder Telefonnummer fehlt in den Einstellungen.' );
	}

	$endpoint = $channel === 'whatsapp'
		? 'https://gateway.seven.io/api/whatsapp/message'
		: 'https://gateway.seven.io/api/sms';

	$response = wp_remote_post( $endpoint, [
		'headers' => [
			'X-Api-Key'    => $api_key,
			'Content-Type' => 'application/json',
		],
		'body'    => wp_json_encode( [ 'to' => $to, 'text' => $text ] ),
		'timeout' => 15,
	] );

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( 'Verbindungsfehler: ' . $response->get_error_message() );
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		wp_send_json_error( 'seven.io Fehler (HTTP ' . $code . ').' );
	}

	// Status auf "bestätigt" setzen + Zeitstempel speichern
	update_post_meta( $post_id, '_vd_status',        'bestaetigt' );
	update_post_meta( $post_id, '_vd_whatsapp_sent', time() );

	wp_send_json_success( [ 'message' => 'WhatsApp erfolgreich gesendet!' ] );
}


// ── Admin-Menü für Manager sichtbar machen ────────────────────────────────────

add_action( 'admin_menu', function () {
	if ( function_exists( 'vietdura_is_manager' ) && vietdura_is_manager() ) {
		// Bestellungen CPT ist bereits über show_ui registriert — keine weitere Aktion nötig
	}
} );
