<?php
/**
 * Template Name: Kontakt & Anfahrt
 * Liest alle Daten aus ACF Options – Single Source of Truth
 */
get_header();

// ── Alle Daten aus Options ────────────────────────────────────────────────────
$vd_adresse          = vietdura_option( 'adresse',          'Zürcherstrasse 48' );
$vd_plz_ort          = vietdura_option( 'plz_ort',          '8317 Tagelswangen ZH' );
$vd_telefon          = vietdura_option( 'telefon',          '+41 44 940 99 99' );
$vd_telefon_href     = vietdura_option( 'telefon_href',      '+41449409999' );
$vd_whatsapp         = vietdura_option( 'whatsapp_url',      'https://wa.me/41765798600' );
$vd_email            = vietdura_option( 'email',            'info@vietdura.ch' );
$vd_email_catering   = vietdura_option( 'email_catering',   'rh@vietdura.ch' );
$vd_maps_url         = vietdura_option( 'maps_url',         'https://maps.google.com/?q=Zürcherstrasse+48+Tagelswangen+ZH' );
$vd_maps_embed       = vietdura_option( 'maps_embed',       '' );
$vd_parkplatz        = vietdura_option( 'parkplatz_info',   'Kostenlose Parkplätze gegenüber (signalisiert)' );
$vd_reservierung     = vietdura_option( 'reservierung_url', home_url( '/reservierung/' ) );
$vd_oeffnungszeiten  = vietdura_option( 'oeffnungszeiten',  [] );
$vd_oeffnung_hinweis = vietdura_option( 'oeffnung_hinweis', 'Anrufe & WhatsApp nur während Öffnungszeiten' );

if ( empty( $vd_oeffnungszeiten ) ) {
    $vd_oeffnungszeiten = [
        [ 'tag' => 'Montag',             'zeiten' => '' ],
        [ 'tag' => 'Dienstag – Freitag', 'zeiten' => '11:00 – 14:00 & 17:00 – 22:00' ],
        [ 'tag' => 'Samstag & Sonntag',  'zeiten' => '17:00 – 22:00' ],
    ];
}
?>

<main class="site-main" id="main-content">

    <!-- Page Hero -->
    <section<?php vietdura_page_hero_bg_attrs(); ?>>
        <?php vietdura_page_hero_overlay(); ?>
        <div class="container">
            <span class="section-kicker">Restaurant Vietdura · Tagelswangen ZH</span>
            <h1>Kontakt &amp; Anfahrt</h1>
            <p>Plätze sind beschränkt – Reservierung sehr empfohlen.</p>
            <div class="page-hero-actions">
                <a href="<?php echo esc_url( $vd_reservierung ); ?>" class="btn btn-primary">🗓 Tisch reservieren</a>
                <a href="<?php echo esc_url( $vd_whatsapp ); ?>" class="btn btn-outline" target="_blank" rel="noopener">💬 WhatsApp</a>
            </div>
        </div>
    </section>

    <!-- Kontakt + Öffnungszeiten -->
    <section class="section">
        <div class="container">
            <div class="contact-grid">

                <div class="contact-block">
                    <h2>Adresse &amp; Kontakt</h2>
                    <ul class="contact-list" itemscope itemtype="https://schema.org/Restaurant">
                        <meta itemprop="name" content="Restaurant Vietdura">
                        <li itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                            <span class="contact-label">📍 Adresse</span>
                            <span>
                                <span itemprop="streetAddress"><?php echo esc_html( $vd_adresse ); ?></span><br>
                                <span itemprop="postalCode addressLocality"><?php echo esc_html( $vd_plz_ort ); ?></span>
                            </span>
                        </li>
                        <li>
                            <span class="contact-label">📞 Telefon</span>
                            <a href="tel:<?php echo esc_attr( $vd_telefon_href ); ?>" itemprop="telephone">
                                <?php echo esc_html( $vd_telefon ); ?>
                            </a>
                        </li>
                        <li>
                            <span class="contact-label">💬 WhatsApp</span>
                            <a href="<?php echo esc_url( $vd_whatsapp ); ?>" target="_blank" rel="noopener">
                                Nachricht schreiben
                            </a>
                        </li>
                        <li>
                            <span class="contact-label">✉️ E-Mail</span>
                            <a href="mailto:<?php echo esc_attr( $vd_email ); ?>" itemprop="email">
                                <?php echo esc_html( $vd_email ); ?>
                            </a>
                        </li>
                        <li>
                            <span class="contact-label">🍽 Catering</span>
                            <a href="mailto:<?php echo esc_attr( $vd_email_catering ); ?>">
                                <?php echo esc_html( $vd_email_catering ); ?>
                            </a>
                        </li>
                        <?php if ( $vd_parkplatz ) : ?>
                        <li>
                            <span class="contact-label">🅿️ Parkplätze</span>
                            <span><?php echo esc_html( $vd_parkplatz ); ?></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <div class="contact-actions">
                        <a href="<?php echo esc_url( $vd_reservierung ); ?>" class="btn btn-primary">🗓 Tisch reservieren</a>
                        <a href="<?php echo esc_url( $vd_maps_url ); ?>" class="btn btn-outline" target="_blank" rel="noopener">📍 Route planen</a>
                    </div>
                </div>

                <div class="contact-block">
                    <h2>Öffnungszeiten</h2>
                    <table class="hours-table" itemprop="openingHours">
                        <?php foreach ( $vd_oeffnungszeiten as $zeile ) : ?>
                        <tr>
                            <td><?php echo esc_html( $zeile['tag'] ); ?></td>
                            <?php if ( empty( $zeile['zeiten'] ) ) : ?>
                            <td class="hours-closed">Ruhetag</td>
                            <?php else : ?>
                            <td><?php echo esc_html( $zeile['zeiten'] ); ?></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php if ( $vd_oeffnung_hinweis ) : ?>
                    <p class="contact-note"><?php echo esc_html( $vd_oeffnung_hinweis ); ?></p>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </section>

    <!-- Google Maps -->
    <?php if ( $vd_maps_embed ) : ?>
    <section class="maps-section">
        <div class="maps-embed">
            <?php echo $vd_maps_embed; // Maps iframe direkt ausgeben ?>
        </div>
    </section>
    <?php else : ?>
    <section class="maps-section">
        <div class="maps-embed maps-embed--placeholder">
            <p>
                Google Maps Embed-Code unter
                <strong>VietDura → Kontakt</strong> im Admin einfügen.<br>
                <a href="<?php echo esc_url( $vd_maps_url ); ?>" target="_blank" rel="noopener">
                    Direkt auf Google Maps öffnen →
                </a>
            </p>
        </div>
    </section>
    <?php endif; ?>

    <!-- Anfahrt -->
    <section class="section section-alt">
        <div class="container">
            <div class="section-heading">
                <span class="section-kicker">So findest Du uns</span>
                <h2>Anfahrt nach Tagelswangen</h2>
            </div>
            <div class="anfahrt-grid">
                <div class="anfahrt-card">
                    <span class="anfahrt-icon">🚗</span>
                    <h3>Mit dem Auto</h3>
                    <p>Zürcherstrasse 48, Tagelswangen – zwischen Effretikon und Bassersdorf. Kostenlose Parkplätze gegenüber dem Restaurant (signalisiert).</p>
                    <a href="<?php echo esc_url( $vd_maps_url ); ?>" class="btn btn-outline btn-sm" target="_blank" rel="noopener">Route starten →</a>
                </div>
                <div class="anfahrt-card">
                    <span class="anfahrt-icon">🚆</span>
                    <h3>Mit dem Zug / Bus</h3>
                    <p>Bahnhof Effretikon oder Bassersdorf – von dort ca. 10 Min. mit dem Bus oder Taxi. Ca. 20 Min. ab Zürich HB, 20 Min. ab Winterthur.</p>
                </div>
                <div class="anfahrt-card">
                    <span class="anfahrt-icon">📍</span>
                    <h3>Lage</h3>
                    <p><?php echo esc_html( $vd_adresse ); ?><br><?php echo esc_html( $vd_plz_ort ); ?></p>
                    <p>Zwischen Effretikon &amp; Bassersdorf · Kanton Zürich</p>
                </div>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
