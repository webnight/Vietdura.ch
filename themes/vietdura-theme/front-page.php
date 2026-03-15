<?php get_header(); ?>

<main class="site-main">

    <?php
    // ── Hero-Daten: zuerst von der Front-Page selbst, dann aus Options ────────
    $front_page_id = (int) get_option( 'page_on_front' );

    /**
     * Liest ein ACF-Feld zuerst von der Front Page, dann von der Options Page.
     */
    function vietdura_front_field( string $name, int $front_page_id, $fallback = '' ) {
        if ( ! function_exists( 'get_field' ) ) return $fallback;
        if ( $front_page_id ) {
            $val = get_field( $name, $front_page_id );
            if ( $val !== null && $val !== '' && $val !== false ) return $val;
        }
        $val = get_field( $name, 'option' );
        if ( $val !== null && $val !== '' && $val !== false ) return $val;
        return $fallback;
    }

    // Text-Felder
    $hero_eyebrow   = vietdura_front_field( 'hero_eyebrow',   $front_page_id, 'Modernes vietnamesisches Restaurant in Tagelswangen' );
    $hero_titel     = vietdura_front_field( 'hero_titel',     $front_page_id, 'Frisch. Authentisch.|Modern interpretiert.' );
    $hero_text      = vietdura_front_field( 'hero_text',      $front_page_id, 'VietDura bringt vietnamesische Küche in einem warmen, modernen und hochwertigen Ambiente — direkt zu Ihnen.' );
    $hero_btn1_text = vietdura_front_field( 'hero_btn1_text', $front_page_id, 'Speisekarte ansehen' );
    $hero_btn1_url  = vietdura_front_field( 'hero_btn1_url',  $front_page_id, home_url( '/speisekarte/' ) );
    $hero_btn2_text = vietdura_front_field( 'hero_btn2_text', $front_page_id, 'Tisch reservieren' );
    $hero_btn2_url  = vietdura_front_field( 'hero_btn2_url',  $front_page_id, '#' );

    // Hintergrundbild
    $hero_bg_image   = vietdura_front_field( 'hero_bg_image', $front_page_id, null );
    $hero_bg_opacity = vietdura_front_field( 'hero_bg_overlay_opacity', $front_page_id, '0.55' );
    $hero_bg_url     = '';
    if ( $hero_bg_image ) {
        $hero_bg_url = is_array( $hero_bg_image ) ? ( $hero_bg_image['url'] ?? '' ) : wp_get_attachment_url( $hero_bg_image );
    }

    // Empfehlungs-Karte
    $show_card_value = null;
    if ( function_exists( 'get_field' ) ) {
        if ( $front_page_id ) {
            $show_card_value = get_field( 'hero_show_featured_card', $front_page_id );
        }
        if ( $show_card_value === null ) {
            $show_card_value = get_field( 'hero_show_featured_card', 'option' );
        }
    }
    $show_card            = ( $show_card_value === null ) ? true : (bool) $show_card_value;
    $card_badge           = vietdura_front_field( 'hero_featured_badge',     $front_page_id, 'Empfohlen' );
    $card_title           = vietdura_front_field( 'hero_featured_title',     $front_page_id, '' );
    $card_text            = vietdura_front_field( 'hero_featured_text',      $front_page_id, '' );
    $card_price           = vietdura_front_field( 'hero_featured_price',     $front_page_id, '' );
    $card_image           = vietdura_front_field( 'hero_featured_image',     $front_page_id, null );
    $card_image_url       = '';
    if ( $card_image ) {
        $card_image_url = is_array( $card_image ) ? ( $card_image['sizes']['speise-card'] ?? $card_image['url'] ?? '' ) : wp_get_attachment_image_url( $card_image, 'speise-card' );
    }

    // Titel: | als <br> rendern
    $hero_titel_html = nl2br( esc_html( str_replace( '|', "\n", $hero_titel ) ) );

    // Hero-Inline-Style: Hintergrundbild oder Gradient
    $hero_style = '';
    if ( $hero_bg_url ) {
        $opacity     = (float) $hero_bg_opacity;
        $opacity     = max( 0, min( 1, $opacity ) ); // 0–1 clamp
        $overlay_r   = 31; $overlay_g = 77; $overlay_b = 58; // --color-green-dark RGB
        $hero_style  = 'style="background-image: url(' . esc_url( $hero_bg_url ) . ');"';
    }
    ?>

    <section class="hero<?php echo $hero_bg_url ? ' hero--has-bg' : ''; ?>"<?php echo $hero_style; ?>>
        <?php if ( $hero_bg_url ) : ?>
        <div class="hero-overlay" style="--hero-overlay-opacity: <?php echo esc_attr( $hero_bg_opacity ); ?>;"></div>
        <?php endif; ?>
        <div class="container hero-grid<?php echo ! $show_card ? ' hero-grid--no-card' : ''; ?>">
            <div class="hero-content">
                <?php if ( $hero_eyebrow ) : ?>
                <span class="hero-eyebrow"><?php echo esc_html( $hero_eyebrow ); ?></span>
                <?php endif; ?>
                <h1><?php echo $hero_titel_html; ?></h1>
                <?php if ( $hero_text ) : ?>
                <p><?php echo esc_html( $hero_text ); ?></p>
                <?php endif; ?>
                <div class="hero-actions">
                    <?php if ( $hero_btn1_text ) : ?>
                        <a href="<?php echo esc_url( $hero_btn1_url ); ?>" class="btn btn-primary"><?php echo esc_html( $hero_btn1_text ); ?></a>
                    <?php endif; ?>
                    <?php if ( $hero_btn2_text ) : ?>
                        <a href="<?php echo esc_url( $hero_btn2_url ); ?>" class="btn btn-outline"><?php echo esc_html( $hero_btn2_text ); ?></a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ( $show_card ) : ?>
            <div class="hero-visual">
                <div class="hero-card">
                    <div class="hero-image-placeholder<?php echo $card_image_url ? ' hero-image-placeholder--photo' : ''; ?>">
                        <?php if ( $card_image_url ) : ?>
                            <img src="<?php echo esc_url( $card_image_url ); ?>" alt="<?php echo esc_attr( $card_title ); ?>">
                        <?php endif; ?>
                        <?php if ( $card_badge ) : ?>
                        <span class="hero-card-badge"><?php echo esc_html( $card_badge ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="hero-card-content">
                        <?php if ( $card_title ) : ?>
                        <h2><?php echo esc_html( $card_title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $card_text ) : ?>
                        <p><?php echo esc_html( $card_text ); ?></p>
                        <?php endif; ?>
                        <?php if ( $card_price ) : ?>
                        <span class="hero-card-price">CHF <?php echo esc_html( $card_price ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="intro section">
        <div class="container">
            <div class="section-heading">
                <span class="section-kicker">Über VietDura</span>
                <h2>Vietnamesische Küche mit Charakter</h2>
                <p>
                    Frische Zutaten, ausgewogene Aromen und eine moderne Präsentation machen VietDura zu einem besonderen kulinarischen Erlebnis.
                </p>
            </div>

            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon"></div>
                    <h3>Frisch</h3>
                    <p>Täglich zubereitet mit hochwertigen Zutaten und viel Sorgfalt.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"></div>
                    <h3>Authentisch</h3>
                    <p>Vietnamesische Klassiker mit echter Handschrift und ausgewogenem Geschmack.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"></div>
                    <h3>Modern interpretiert</h3>
                    <p>Elegante Präsentation, klare Struktur und zeitgemässer Auftritt.</p>
                </div>
            </div>
        </div>
    </section>

    <?php $speisen = vietdura_get_startseite_speisen(); ?>
    <?php if ( $speisen->have_posts() ) : ?>
    <section class="popular-dishes section section-alt">
        <div class="container">
            <div class="section-heading">
                <span class="section-kicker">Beliebte Speisen</span>
                <h2>Ein erster Eindruck aus der Küche</h2>
                <p>Ausgewählte Gerichte — frisch zubereitet, authentisch gewürzt.</p>
            </div>

            <div class="dish-grid">
                <?php while ( $speisen->have_posts() ) : $speisen->the_post(); ?>
                <?php
                    $post_id      = get_the_ID();
                    $preis        = vietdura_get_price( $post_id );
                    $badges       = vietdura_field( 'badges', $post_id );
                    $beschreibung = get_the_excerpt();
                    $has_image    = has_post_thumbnail();
                ?>
                <article class="dish-card">
                    <div class="dish-image<?php echo $has_image ? ' dish-image--photo' : ''; ?>">
                        <?php if ( $has_image ) : ?>
                            <?php the_post_thumbnail( 'speise-card', [ 'alt' => esc_attr( get_the_title() ) ] ); ?>
                        <?php endif; ?>
                        <?php if ( ! empty( $badges ) ) : ?>
                            <div class="dish-badges">
                                <?php foreach ( (array) $badges as $badge ) : ?>
                                    <span class="dish-badge <?php echo esc_attr( vietdura_badge_class( $badge ) ); ?>">
                                        <?php echo esc_html( vietdura_badge_label( $badge ) ); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="dish-content">
                        <h3><?php the_title(); ?></h3>
                        <?php if ( $beschreibung ) : ?>
                            <p><?php echo esc_html( $beschreibung ); ?></p>
                        <?php endif; ?>
                        <div class="dish-footer">
                            <?php if ( $preis ) : ?>
                                <span class="dish-price"><?php echo esc_html( $preis ); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="declaration-teaser section">
        <div class="container">
            <div class="declaration-box">
                <div>
                    <span class="section-kicker">Deklaration / Herkunft</span>
                    <h2>Transparente Herkunft unserer Zutaten</h2>
                    <p>Die Herkunft wichtiger Produkte wird auf der Website klar und zentral ausgewiesen.</p>
                </div>
                <div class="declaration-action">
                    <a href="<?php echo esc_url( vietdura_option( 'hero_btn1_url', '#' ) ); ?>" class="btn btn-primary">Zur Deklaration</a>
                </div>
            </div>
        </div>
    </section>

    <?php
    // ── Kontakt-Daten aus Options ─────────────────────────────────────────────
    $adresse         = vietdura_option( 'adresse',         'Musterstrasse 12' );
    $plz_ort         = vietdura_option( 'plz_ort',         '8317 Tagelswangen' );
    $telefon         = vietdura_option( 'telefon',         '+41 52 123 45 67' );
    $telefon_href    = vietdura_option( 'telefon_href',     '+41521234567' );
    $email           = vietdura_option( 'email',           'info@vietdura.ch' );
    $reservierung_url = vietdura_option( 'reservierung_url', '#' );
    $oeffnungszeiten = vietdura_option( 'oeffnungszeiten', [] );

    // Fallback-Öffnungszeiten wenn noch nichts gepflegt
    if ( empty( $oeffnungszeiten ) ) {
        $oeffnungszeiten = [
            [ 'tag' => 'Montag',             'zeiten' => '' ],
            [ 'tag' => 'Dienstag – Freitag', 'zeiten' => '11:30 – 14:00 & 17:30 – 22:00' ],
            [ 'tag' => 'Samstag',            'zeiten' => '11:30 – 22:30' ],
            [ 'tag' => 'Sonntag',            'zeiten' => '11:30 – 21:30' ],
        ];
    }
    ?>

    <section class="contact-section section">
        <div class="container">
            <div class="section-heading">
                <span class="section-kicker">Besuchen Sie uns</span>
                <h2>Kontakt &amp; Öffnungszeiten</h2>
            </div>
            <div class="contact-grid">
                <div class="contact-block">
                    <h3>Kontakt &amp; Adresse</h3>
                    <ul class="contact-list">
                        <?php if ( $adresse || $plz_ort ) : ?>
                        <li>
                            <span class="contact-label">Adresse</span>
                            <span>
                                <?php if ( $adresse ) echo esc_html( $adresse ) . '<br>'; ?>
                                <?php echo esc_html( $plz_ort ); ?>
                            </span>
                        </li>
                        <?php endif; ?>
                        <?php if ( $telefon ) : ?>
                        <li>
                            <span class="contact-label">Telefon</span>
                            <a href="tel:<?php echo esc_attr( $telefon_href ); ?>"><?php echo esc_html( $telefon ); ?></a>
                        </li>
                        <?php endif; ?>
                        <?php if ( $email ) : ?>
                        <li>
                            <span class="contact-label">E-Mail</span>
                            <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
                        </li>
                        <?php endif; ?>
                        <?php if ( $reservierung_url ) : ?>
                        <li>
                            <span class="contact-label">Reservation</span>
                            <a href="<?php echo esc_url( $reservierung_url ); ?>" class="btn btn-primary btn-sm">Tisch reservieren</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="contact-block">
                    <h3>Öffnungszeiten</h3>
                    <table class="hours-table">
                        <?php foreach ( $oeffnungszeiten as $zeile ) : ?>
                        <tr>
                            <td><?php echo esc_html( $zeile['tag'] ); ?></td>
                            <?php if ( empty( $zeile['zeiten'] ) ) : ?>
                                <td class="hours-closed">Geschlossen</td>
                            <?php else : ?>
                                <td><?php echo esc_html( $zeile['zeiten'] ); ?></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
