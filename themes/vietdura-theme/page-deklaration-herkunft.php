<?php
/**
 * Template Name: Deklaration & Herkunft
 * Liest Daten aus ACF-Feldern (dekl_intro, dekl_eintraege) + post_content als Fallback.
 */
get_header();

$pid = get_the_ID();
$dekl_intro          = get_post_meta( $pid, 'dekl_intro', true );
$dekl_count          = (int) get_post_meta( $pid, 'dekl_eintraege', true );
$dekl_hero_kicker    = get_post_meta( $pid, 'dekl_hero_kicker', true ) ?: 'Transparenz & Qualität';
$dekl_hero_titel     = get_post_meta( $pid, 'dekl_hero_titel',  true ) ?: 'Deklaration & Herkunft';
$dekl_hero_text      = get_post_meta( $pid, 'dekl_hero_text',   true ) ?: 'Wir legen Wert auf Qualität und Transparenz – hier findest du die Herkunft unserer wichtigsten Zutaten.';
$dekl_allergen_titel = get_post_meta( $pid, 'dekl_allergen_titel', true ) ?: 'Allergene & Unverträglichkeiten';
$dekl_allergen_text  = get_post_meta( $pid, 'dekl_allergen_text',  true ) ?: 'Bitte informiere unser Personal über Allergien oder Unverträglichkeiten. Wir berücksichtigen diese gerne bei der Zubereitung.';
$dekl_eintraege = [];
for ( $i = 0; $i < $dekl_count; $i++ ) {
    $dekl_eintraege[] = [
        'produkt'  => get_post_meta( $pid, "dekl_eintraege_{$i}_produkt",  true ),
        'herkunft' => get_post_meta( $pid, "dekl_eintraege_{$i}_herkunft", true ),
        'hinweis'  => get_post_meta( $pid, "dekl_eintraege_{$i}_hinweis",  true ),
    ];
}
?>

<main class="site-main" id="main-content">

    <!-- ── Hero ────────────────────────────────────────────────────────────── -->
    <section class="page-hero"<?php vietdura_page_hero_bg_attrs(); ?>>
        <?php vietdura_page_hero_overlay(); ?>
        <div class="container">
            <div class="section-heading">
                <span class="section-kicker"><?php echo esc_html( $dekl_hero_kicker ); ?></span>
                <h1><?php echo esc_html( $dekl_hero_titel ); ?></h1>
                <p><?php echo esc_html( $dekl_hero_text ); ?></p>
            </div>
        </div>
    </section>

    <?php get_template_part( 'template-parts/trust-bar' ); ?>

    <!-- ── Herkunfts-Tabelle ────────────────────────────────────────────────── -->
    <section class="section dekl-section">
        <div class="container-narrow">

            <?php if ( $dekl_intro ) : ?>
            <p class="dekl-intro"><?php echo nl2br( esc_html( $dekl_intro ) ); ?></p>
            <?php endif; ?>

            <?php if ( ! empty( $dekl_eintraege ) ) : ?>
            <table class="dekl-table">
                <thead>
                    <tr>
                        <th>Produkt</th>
                        <th>Herkunft</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $dekl_eintraege as $eintrag ) : ?>
                    <tr>
                        <td class="dekl-table__produkt"><?php echo esc_html( $eintrag['produkt'] ); ?></td>
                        <td class="dekl-table__herkunft">
                            <?php echo esc_html( $eintrag['herkunft'] ); ?>
                            <?php if ( $eintrag['hinweis'] ) : ?>
                            <span class="dekl-table__hinweis"><?php echo esc_html( $eintrag['hinweis'] ); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <!-- Editierbarer Zusatztext (post_content) -->
            <?php
            $content = get_the_content();
            if ( $content ) :
            ?>
            <div class="dekl-zusatz">
                <?php the_content(); ?>
            </div>
            <?php endif; ?>

        </div>
    </section>

    <!-- ── Allergene Hinweis ────────────────────────────────────────────────── -->
    <section class="dekl-allergen-section">
        <div class="container-narrow">
            <div class="dekl-allergen-box">
                <span class="dekl-allergen-icon">⚠️</span>
                <div>
                    <strong><?php echo esc_html( $dekl_allergen_titel ); ?></strong>
                    <p><?php echo esc_html( $dekl_allergen_text ); ?></p>
                </div>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
