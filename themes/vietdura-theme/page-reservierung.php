<?php
/**
 * Template Name: Tischreservierung
 */
get_header();

$vd_telefon      = vietdura_option( 'telefon',      '+41 44 940 99 99' );
$vd_telefon_href = vietdura_option( 'telefon_href',  '+41449409999' );
$vd_whatsapp     = vietdura_option( 'whatsapp_url',  'https://wa.me/41765798600' );
$vd_oeffnungszeiten = vietdura_option( 'oeffnungszeiten', [] );

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
            <h1>Tisch reservieren</h1>
            <p>Sichern Sie sich Ihren Platz – einfach und schnell online reservieren.</p>
        </div>
    </section>

    <?php get_template_part( 'template-parts/trust-bar' ); ?>

    <!-- Reservierungsformular -->
    <section class="section">
        <div class="container">
            <div class="reservation-layout">

                <div class="reservation-form-wrap">
                    <h2>Online Reservierung</h2>
                    <p class="reservation-intro">Füllen Sie das Formular aus und wir bestätigen Ihre Reservierung so schnell wie möglich.</p>
                    <?php echo do_shortcode( '[booking-form]' ); ?>
                </div>

                <aside class="reservation-sidebar">
                    <div class="reservation-info-card">
                        <h3>Öffnungszeiten</h3>
                        <table class="hours-table">
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
                    </div>

                    <div class="reservation-info-card">
                        <h3>Lieber persönlich?</h3>
                        <p>Rufen Sie uns an oder schreiben Sie uns per WhatsApp:</p>
                        <div class="reservation-contact-actions">
                            <a href="tel:<?php echo esc_attr( $vd_telefon_href ); ?>" class="btn btn-phone">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                <?php echo esc_html( $vd_telefon ); ?>
                            </a>
                            <a href="<?php echo esc_url( $vd_whatsapp ); ?>" class="btn btn-whatsapp" target="_blank" rel="noopener">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                                WhatsApp Nachricht
                            </a>
                        </div>
                    </div>

                    <div class="reservation-info-card">
                        <h3>Gut zu wissen</h3>
                        <ul class="reservation-hints">
                            <li>Reservierungen nur während der Öffnungszeiten</li>
                            <li>Für Gruppen ab 8 Personen bitte telefonisch anfragen</li>
                            <li>Stornierung bis 2 Stunden vorher möglich</li>
                        </ul>
                    </div>
                </aside>

            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
