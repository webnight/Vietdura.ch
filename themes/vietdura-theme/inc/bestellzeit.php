<?php
/**
 * Bestellzeit-Prüfung
 *
 * Regeln:
 * - Mittagsmenu: bestellbar ab 30 Min. vor Mittag-Öffnung bis Mittag-Schliessung
 * - Speisen/Getränke Mittag: gleich wie Mittagsmenu
 * - Speisen/Getränke Abend: ab 30 Min. vor Abend-Öffnung bis Abend-Schliessung
 */

/**
 * Gibt den Wochentag-Kürzel für heute zurück: mo, di, mi, do, fr, sa, so
 */
function vd_heute_kuerzel(): string {
    $tz  = new DateTimeZone( 'Europe/Zurich' );
    $map = [ 1 => 'mo', 2 => 'di', 3 => 'mi', 4 => 'do', 5 => 'fr', 6 => 'sa', 0 => 'so' ];
    return $map[ (int) ( new DateTime( 'now', $tz ) )->format( 'w' ) ];
}

/**
 * Konvertiert "11:00" zu Integer 1100 für Vergleiche.
 */
function vd_zeit_int( string $zeit ): int {
    if ( ! $zeit ) return 0;
    return (int) str_replace( ':', '', trim( $zeit ) );
}

/**
 * Öffnungszeiten für einen Tag laden.
 * Gibt Array zurück: ['mi_von'=>'11:00','mi_bis'=>'14:00','ab_von'=>'17:00','ab_bis'=>'22:00']
 * Leere Werte = kein Service in dieser Periode.
 */
function vd_get_oeffnung( string $tag_kuerzel ): array {
    return [
        'mi_von' => vietdura_option( 'oz_' . $tag_kuerzel . '_mi_von', '' ),
        'mi_bis' => vietdura_option( 'oz_' . $tag_kuerzel . '_mi_bis', '' ),
        'ab_von' => vietdura_option( 'oz_' . $tag_kuerzel . '_ab_von', '' ),
        'ab_bis' => vietdura_option( 'oz_' . $tag_kuerzel . '_ab_bis', '' ),
    ];
}

/**
 * Prüft ob aktuell bestellt werden darf.
 *
 * @param string $typ  'mittagsmenu' | 'speise' | 'getraenk'
 * @return array ['ok' => bool, 'message' => string]
 */
function vd_bestellzeit_pruefen( string $typ ): array {
    $tz    = new DateTimeZone( 'Europe/Zurich' );
    $jetzt = (int) ( new DateTime( 'now', $tz ) )->format( 'Hi' );
    $tag   = vd_heute_kuerzel();
    $oz    = vd_get_oeffnung( $tag );

    $mi_von = vd_zeit_int( $oz['mi_von'] );
    $mi_bis = vd_zeit_int( $oz['mi_bis'] );
    $ab_von = vd_zeit_int( $oz['ab_von'] );
    $ab_bis = vd_zeit_int( $oz['ab_bis'] );

    // 30 Minuten vorher = -30 auf HHMM (vereinfacht, ohne Stunden-Übertrag nötig hier)
    $mi_bestell_ab = $mi_von > 0 ? $mi_von - 30 : 0;
    $ab_bestell_ab = $ab_von > 0 ? $ab_von - 30 : 0;

    if ( $typ === 'mittagsmenu' ) {
        // Nur Mittagsfenster
        if ( $mi_von && $mi_bis && $jetzt >= $mi_bestell_ab && $jetzt < $mi_bis ) {
            return [ 'ok' => true, 'message' => '' ];
        }
        $msg = $mi_von && $mi_bis
            ? 'Das Mittagsmenü ist heute von ' . $oz['mi_von'] . ' bis ' . $oz['mi_bis'] . ' Uhr bestellbar (ab ' . vd_int_zu_zeit( $mi_bestell_ab ) . ' Uhr).'
            : 'Das Mittagsmenü ist heute nicht verfügbar.';
        return [ 'ok' => false, 'message' => $msg ];
    }

    // Speisen & Getränke: Mittag- oder Abendfenster
    $im_mittag = $mi_von && $mi_bis && $jetzt >= $mi_bestell_ab && $jetzt < $mi_bis;
    $im_abend  = $ab_von && $ab_bis && $jetzt >= $ab_bestell_ab && $jetzt < $ab_bis;

    if ( $im_mittag || $im_abend ) {
        return [ 'ok' => true, 'message' => '' ];
    }

    // Fehlermeldung zusammenbauen
    $zeiten = [];
    if ( $mi_von && $mi_bis ) {
        $zeiten[] = vd_int_zu_zeit( $mi_bestell_ab ) . '–' . $oz['mi_bis'] . ' Uhr (Mittag)';
    }
    if ( $ab_von && $ab_bis ) {
        $zeiten[] = vd_int_zu_zeit( $ab_bestell_ab ) . '–' . $oz['ab_bis'] . ' Uhr (Abend)';
    }
    $msg = empty( $zeiten )
        ? 'Heute ist leider Ruhetag — keine Bestellungen möglich.'
        : 'Bestellungen heute möglich: ' . implode( ' und ', $zeiten ) . '.';

    return [ 'ok' => false, 'message' => $msg ];
}

/**
 * Integer 1030 → "10:30"
 */
function vd_int_zu_zeit( int $i ): string {
    if ( ! $i ) return '';
    return sprintf( '%02d:%02d', (int) ( $i / 100 ), $i % 100 );
}
