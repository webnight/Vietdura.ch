<?php
/**
 * VietDura Theme Options
 * Native WordPress Admin-Seiten — kein ACF PRO nötig.
 * Daten werden in wp_options gespeichert (Prefix: vd_opt_)
 */

// ── Hilfsfunktion: Option lesen ───────────────────────────────────────────────
if ( ! function_exists( 'vietdura_option' ) ) {
    function vietdura_option( string $name, $fallback = '' ) {
        $val = get_option( 'vd_opt_' . $name, null );
        if ( $val === null || $val === '' ) return $fallback;
        return $val;
    }
}

// ── Admin-Menü registrieren ───────────────────────────────────────────────────
add_action( 'admin_menu', function () {
    add_menu_page(
        'VietDura Einstellungen',
        'VietDura',
        'manage_options',
        'vietdura-settings',
        'vd_page_kontakt',
        'dashicons-clock',
        4
    );
    add_submenu_page( 'vietdura-settings', 'Kontakt & Öffnungszeiten', 'Kontakt', 'manage_options', 'vietdura-settings', 'vd_page_kontakt' );
} );

// ── Speichern ─────────────────────────────────────────────────────────────────
add_action( 'admin_post_vd_save_options', function () {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Keine Berechtigung' );
    check_admin_referer( 'vd_save_options' );

    $felder = $_POST['vd_opt'] ?? [];
    foreach ( $felder as $key => $val ) {
        $key = sanitize_key( $key );
        update_option( 'vd_opt_' . $key, sanitize_text_field( $val ) );
    }
    wp_redirect( add_query_arg( [ 'page' => sanitize_key( $_POST['vd_redirect'] ?? 'vietdura-settings' ), 'saved' => 1 ], admin_url( 'admin.php' ) ) );
    exit;
} );

// ── Hilfsfunktion: Formular-Wrapper ──────────────────────────────────────────
function vd_form_start( string $page ): void {
    if ( isset( $_GET['saved'] ) ) {
        echo '<div class="notice notice-success is-dismissible"><p>✅ Gespeichert.</p></div>';
    }
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
    echo '<input type="hidden" name="action" value="vd_save_options">';
    echo '<input type="hidden" name="vd_redirect" value="' . esc_attr( $page ) . '">';
    wp_nonce_field( 'vd_save_options' );
}
function vd_form_end(): void {
    echo '<p><button type="submit" class="button button-primary">Speichern</button></p></form>';
}
function vd_field( string $label, string $name, string $placeholder = '', string $desc = '', string $width = '25em' ): void {
    $val = esc_attr( vietdura_option( $name, '' ) );
    echo '<tr><th scope="row"><label for="vd_' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label></th>';
    echo '<td><input type="text" id="vd_' . esc_attr( $name ) . '" name="vd_opt[' . esc_attr( $name ) . ']" value="' . $val . '" placeholder="' . esc_attr( $placeholder ) . '" style="width:' . esc_attr( $width ) . '">';
    if ( $desc ) echo '<p class="description">' . esc_html( $desc ) . '</p>';
    echo '</td></tr>';
}
function vd_oz_tag( string $label, string $kuerzel ): void {
    echo '<tr><th colspan="2" style="background:#f0f0f0;padding:8px 12px;font-weight:700">' . esc_html( $label ) . '</th></tr>';
    vd_field( 'Mittag von', 'oz_' . $kuerzel . '_mi_von', '11:00', 'Leer = kein Mittagsservice', '8em' );
    vd_field( 'Mittag bis', 'oz_' . $kuerzel . '_mi_bis', '14:00', '', '8em' );
    vd_field( 'Abend von',  'oz_' . $kuerzel . '_ab_von', '17:00', 'Leer = Ruhetag / kein Abendservice', '8em' );
    vd_field( 'Abend bis',  'oz_' . $kuerzel . '_ab_bis', '22:00', '', '8em' );
}

// ── Seite: Kontakt & Öffnungszeiten ──────────────────────────────────────────
function vd_page_kontakt(): void {
    echo '<div class="wrap"><h1>Kontakt & Öffnungszeiten</h1>';
    vd_form_start( 'vietdura-settings' );
    echo '<h2>Kontakt</h2><table class="form-table">';
    vd_field( 'Adresse',             'adresse',          'Zürcherstrasse 48' );
    vd_field( 'PLZ & Ort',           'plz_ort',          '8317 Tagelswangen ZH' );
    vd_field( 'Telefon (Anzeige)',   'telefon',           '+41 44 940 99 99' );
    vd_field( 'Telefon (Link)',       'telefon_href',      '+41449409999', 'Nur Zahlen, kein Leerzeichen' );
    vd_field( 'WhatsApp URL',         'whatsapp_url',      'https://wa.me/41449409999' );
    vd_field( 'E-Mail',               'email',             'info@vietdura.ch' );
    vd_field( 'E-Mail Catering',      'email_catering',    'rh@vietdura.ch' );
    vd_field( 'Reservierungs-URL',    'reservierung_url',  'tel:+41449409999' );
    vd_field( 'Google Maps URL',      'maps_url',          'https://maps.google.com/...' );
    vd_field( 'Parkplatz-Info',       'parkplatz_info',    'Kostenlose Parkplätze gegenüber' );
    echo '</table>';

    echo '<h2>Öffnungszeiten</h2>';
    echo '<p class="description">Leer lassen = kein Service / Ruhetag. Bestellungen sind ab 30 Min. vor Öffnung möglich.</p>';
    echo '<table class="form-table">';
    vd_oz_tag( 'Montag',     'mo' );
    vd_oz_tag( 'Dienstag',   'di' );
    vd_oz_tag( 'Mittwoch',   'mi' );
    vd_oz_tag( 'Donnerstag', 'do' );
    vd_oz_tag( 'Freitag',    'fr' );
    vd_oz_tag( 'Samstag',    'sa' );
    vd_oz_tag( 'Sonntag',    'so' );
    echo '<tr><th><label>Hinweis</label></th><td>';
    $hinweis = esc_attr( vietdura_option( 'oeffnung_hinweis', '' ) );
    echo '<input type="text" name="vd_opt[oeffnung_hinweis]" value="' . $hinweis . '" placeholder="Anrufe & WhatsApp nur während Öffnungszeiten" style="width:30em">';
    echo '</td></tr></table>';
    vd_form_end();
    echo '</div>';
}

