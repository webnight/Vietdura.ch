<?php
// ── Kontakt-Daten aus ACF Options (Single Source of Truth) ───────────────────
$vd_adresse          = vietdura_option( 'adresse',          'Zürcherstrasse 48' );
$vd_plz_ort          = vietdura_option( 'plz_ort',          '8317 Tagelswangen ZH' );
$vd_telefon          = vietdura_option( 'telefon',          '+41 44 940 99 99' );
$vd_telefon_href     = vietdura_option( 'telefon_href',      '+41449409999' );
$vd_whatsapp         = vietdura_option( 'whatsapp_url',      'https://wa.me/41449409999' );
$vd_email            = vietdura_option( 'email',            'info@vietdura.ch' );
$vd_email_catering   = vietdura_option( 'email_catering',   'rh@vietdura.ch' );
$vd_maps_url         = vietdura_option( 'maps_url',         'https://maps.google.com/?q=Zürcherstrasse+48+Tagelswangen+ZH' );
$vd_oeffnung_hinweis = vietdura_option( 'oeffnung_hinweis', 'Anrufe & WhatsApp nur während Öffnungszeiten' );

// Öffnungszeiten aus strukturierten Feldern zusammenbauen
function vd_footer_zeiten( string $tag ): string {
    $mi_von = vietdura_option( 'oz_' . $tag . '_mi_von', '' );
    $mi_bis = vietdura_option( 'oz_' . $tag . '_mi_bis', '' );
    $ab_von = vietdura_option( 'oz_' . $tag . '_ab_von', '' );
    $ab_bis = vietdura_option( 'oz_' . $tag . '_ab_bis', '' );
    $parts  = [];
    if ( $mi_von && $mi_bis ) $parts[] = $mi_von . ' – ' . $mi_bis;
    if ( $ab_von && $ab_bis ) $parts[] = $ab_von . ' – ' . $ab_bis;
    return empty( $parts ) ? '' : implode( ' & ', $parts );
}

$vd_oeffnungszeiten = [
    [ 'tag' => 'Montag',     'zeiten' => vd_footer_zeiten( 'mo' ) ],
    [ 'tag' => 'Dienstag',   'zeiten' => vd_footer_zeiten( 'di' ) ],
    [ 'tag' => 'Mittwoch',   'zeiten' => vd_footer_zeiten( 'mi' ) ],
    [ 'tag' => 'Donnerstag', 'zeiten' => vd_footer_zeiten( 'do' ) ],
    [ 'tag' => 'Freitag',    'zeiten' => vd_footer_zeiten( 'fr' ) ],
    [ 'tag' => 'Samstag',    'zeiten' => vd_footer_zeiten( 'sa' ) ],
    [ 'tag' => 'Sonntag',    'zeiten' => vd_footer_zeiten( 'so' ) ],
];
// Ruhetage ausblenden
$vd_oeffnungszeiten = array_filter( $vd_oeffnungszeiten, fn( $z ) => $z['zeiten'] !== '' );
?>
<footer class="site-footer">
    <div class="container footer-inner">

        <div class="footer-column footer-column--brand">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo-horizontal-white.svg' ); ?>"
                     alt="Restaurant Vietdura" class="footer-logo" width="180" height="38">
            </a>
            <p>Südvietnamesische Familienrezepte – frisch, regional, hausgemacht. Eine echte Stube unter Freunden.</p>
            <p class="footer-tagline">⭐⭐⭐⭐⭐ 590+ Google & TripAdvisor Bewertungen</p>
        </div>

        <div class="footer-column">
            <h3>Kontakt</h3>
            <ul class="footer-contact-list">
                <li>
                    <span class="footer-contact-icon">📍</span>
                    <a href="<?php echo esc_url( $vd_maps_url ); ?>" target="_blank" rel="noopener">
                        <?php echo esc_html( $vd_adresse ); ?><br>
                        <?php echo esc_html( $vd_plz_ort ); ?>
                    </a>
                </li>
                <li>
                    <span class="footer-contact-icon">📞</span>
                    <a href="tel:<?php echo esc_attr( $vd_telefon_href ); ?>"><?php echo esc_html( $vd_telefon ); ?></a>
                </li>
                <li>
                    <span class="footer-contact-icon">💬</span>
                    <a href="<?php echo esc_url( $vd_whatsapp ); ?>" target="_blank" rel="noopener">WhatsApp</a>
                </li>
                <li>
                    <span class="footer-contact-icon">✉️</span>
                    <a href="mailto:<?php echo esc_attr( $vd_email ); ?>"><?php echo esc_html( $vd_email ); ?></a>
                </li>
            </ul>
        </div>

        <div class="footer-column">
            <h3>Öffnungszeiten</h3>
            <table class="footer-hours">
                <?php foreach ( $vd_oeffnungszeiten as $zeile ) : ?>
                <tr>
                    <td><?php echo esc_html( $zeile['tag'] ); ?></td>
                    <td><?php echo empty( $zeile['zeiten'] ) ? '<em>Ruhetag</em>' : esc_html( $zeile['zeiten'] ); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php if ( $vd_oeffnung_hinweis ) : ?>
            <p class="footer-note"><?php echo esc_html( $vd_oeffnung_hinweis ); ?></p>
            <?php endif; ?>
        </div>

        <div class="footer-column">
            <h3>Navigation</h3>
            <ul class="footer-nav">
                <li><a href="<?php echo esc_url( home_url( '/speisekarte/' ) ); ?>">Speisekarte</a></li>
                <li><a href="<?php echo esc_url( home_url( '/mittagsmenu/' ) ); ?>">Mittags-Menü</a></li>
                <li><a href="<?php echo esc_url( home_url( '/catering/' ) ); ?>">Catering & Lieferung</a></li>
                <li><a href="<?php echo esc_url( home_url( '/ueber-uns/' ) ); ?>">Über uns</a></li>
                <li><a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>">Kontakt & Anfahrt</a></li>
                <li><a href="<?php echo esc_url( home_url( '/galerie/' ) ); ?>">Galerie</a></li>
                <li><a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>">FAQ</a></li>
            </ul>
        </div>

    </div>

    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <p>&copy; <?php echo date( 'Y' ); ?> Hammer Exquisit GmbH · Restaurant Vietdura · <?php echo esc_html( $vd_plz_ort ); ?></p>
            <ul class="footer-legal">
                <li><a href="<?php echo esc_url( home_url( '/impressum/' ) ); ?>">Impressum</a></li>
                <li><a href="<?php echo esc_url( home_url( '/datenschutz/' ) ); ?>">Datenschutz</a></li>
            </ul>
        </div>
    </div>
</footer>

<button id="scroll-to-top" aria-label="Nach oben scrollen">&#8679;</button>

<script>
(function() {
    var btn = document.getElementById('scroll-to-top');
    window.addEventListener('scroll', function() {
        btn.classList.toggle('visible', window.scrollY > 300);
    });
    btn.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
