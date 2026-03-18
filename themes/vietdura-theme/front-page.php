<?php
/**
 * Template: Startseite (front-page.php)
 * Ziel: Reservierungen + Online-Bestellungen steigern
 * SEO: Restaurant Vietdura Tagelswangen – Vietnamesisch essen, Tisch reservieren
 */
get_header();

$front_page_id = (int) get_option( 'page_on_front' );

if ( ! function_exists( 'vietdura_front_field' ) ) {
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
}

// ── Hero-Bild ─────────────────────────────────────────────────────────────────
$hero_bg_image   = vietdura_front_field( 'hero_bg_image', $front_page_id, null );
$hero_bg_opacity = vietdura_front_field( 'hero_bg_overlay_opacity', $front_page_id, '0.6' );
$hero_bg_url     = '';
if ( $hero_bg_image ) {
    $hero_bg_url = is_array( $hero_bg_image ) ? ( $hero_bg_image['url'] ?? '' ) : wp_get_attachment_url( $hero_bg_image );
}

// ── Kontakt aus Options (Single Source of Truth) ──────────────────────────────
$vd_telefon_href  = vietdura_option( 'telefon_href',     '+41449409999' );
$vd_whatsapp      = vietdura_option( 'whatsapp_url',     'https://wa.me/41765798600' );
$vd_reservierung  = vietdura_option( 'reservierung_url', home_url( '/reservierung/' ) );
?>

<main class="site-main" id="main-content">

<!-- ═══════════════════════════════════════════════════════
     1. HERO – Hauptbotschaft + CTAs
════════════════════════════════════════════════════════ -->
<section class="hero hero--home<?php echo $hero_bg_url ? ' hero--has-bg' : ''; ?>"
    <?php if ( $hero_bg_url ) echo 'style="background-image:url(' . esc_url( $hero_bg_url ) . ');"'; ?>
    aria-label="Restaurant Vietdura – Vietnamesisches Restaurant Tagelswangen">

    <?php if ( $hero_bg_url ) : ?>
    <div class="hero-overlay" style="--hero-overlay-opacity:<?php echo esc_attr( $hero_bg_opacity ); ?>;"></div>
    <?php endif; ?>

    <div class="container hero-grid hero-grid--no-card">
        <div class="hero-content">
            <span class="hero-eyebrow">Vietnamesisches Restaurant · Tagelswangen ZH · 20 Min. ab Zürich</span>
            <h1>Hier kocht<br>die Familie.</h1>
            <p class="hero-lead">
                Südvietnamesische Familienrezepte. Schweizer Rind &amp; Zürcher Freilandeier.
                Kein Glutamat. Täglich frisch – für Dich gekocht.
            </p>
            <div class="hero-actions">
                <a href="<?php echo esc_url( $vd_reservierung ); ?>" class="btn btn-primary btn-lg">
                    🗓 Tisch reservieren
                </a>
                <a href="<?php echo esc_url( home_url( '/speisekarte/' ) ); ?>" class="btn btn-outline btn-lg">
                    Speisekarte &amp; Online bestellen
                </a>
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════
     2. TRUST BAR
════════════════════════════════════════════════════════ -->
<?php get_template_part( 'template-parts/trust-bar' ); ?>


<!-- ═══════════════════════════════════════════════════════
     2b. EMPFEHLUNG (ein/ausblendbar via Admin)
════════════════════════════════════════════════════════ -->
<?php
// Liest aus dem "empfehlung" Post Type – neuester veröffentlichter Eintrag
$emp_posts = get_posts( [
    'post_type'      => 'empfehlung',
    'posts_per_page' => 1,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
] );
$emp_post     = $emp_posts[0] ?? null;
$emp_id       = $emp_post ? $emp_post->ID : null;
$emp_aktiv    = $emp_id && function_exists( 'get_field' ) ? get_field( 'empfehlung_aktiv', $emp_id ) : false;
$emp_titel    = $emp_id && function_exists( 'get_field' ) ? get_field( 'empfehlung_titel', $emp_id ) : '';
$emp_text     = $emp_id && function_exists( 'get_field' ) ? get_field( 'empfehlung_text',  $emp_id ) : '';
$emp_preis    = $emp_id && function_exists( 'get_field' ) ? get_field( 'empfehlung_preis', $emp_id ) : '';
$emp_badge    = $emp_id && function_exists( 'get_field' ) ? get_field( 'empfehlung_badge', $emp_id ) : 'Heute empfohlen';
$emp_bild     = $emp_id && function_exists( 'get_field' ) ? get_field( 'empfehlung_bild',  $emp_id ) : null;
$emp_bild_url = '';
if ( $emp_bild ) {
    $emp_bild_url = is_array( $emp_bild ) ? ( $emp_bild['url'] ?? '' ) : wp_get_attachment_url( $emp_bild );
}
?>
<?php if ( $emp_aktiv && $emp_titel ) : ?>
<section class="empfehlung-section" aria-label="Empfehlung des Hauses">
    <div class="container">
        <div class="empfehlung-card">
            <?php if ( $emp_bild_url ) : ?>
            <div class="empfehlung-image">
                <img src="<?php echo esc_url( $emp_bild_url ); ?>" alt="<?php echo esc_attr( $emp_titel ); ?>" loading="lazy">
            </div>
            <?php endif; ?>
            <div class="empfehlung-content">
                <?php if ( $emp_badge ) : ?>
                <span class="empfehlung-badge"><?php echo esc_html( $emp_badge ); ?></span>
                <?php endif; ?>
                <h2 class="empfehlung-titel"><?php echo esc_html( $emp_titel ); ?></h2>
                <?php if ( $emp_text ) : ?>
                <p class="empfehlung-text"><?php echo esc_html( $emp_text ); ?></p>
                <?php endif; ?>
                <?php if ( $emp_preis ) : ?>
                <p class="empfehlung-preis">CHF <strong><?php echo esc_html( $emp_preis ); ?></strong></p>
                <?php endif; ?>
                <a href="<?php echo esc_url( home_url( '/speisekarte/' ) ); ?>" class="btn btn-primary">
                    Zur Speisekarte →
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════
     2c. TAGESMENU VORSCHAU (ein/ausblendbar via Admin)
════════════════════════════════════════════════════════ -->
<?php
$tm_aktiv    = function_exists( 'get_field' ) ? get_field( 'tagesmenu_aktiv', 'option' ) : false;
$tm_datum    = function_exists( 'get_field' ) ? get_field( 'tagesmenu_datum', 'option' ) : '';
$tm_intro    = function_exists( 'get_field' ) ? get_field( 'tagesmenu_intro', 'option' ) : '';
$tm_extras   = function_exists( 'get_field' ) ? get_field( 'tagesmenu_extras', 'option' ) : [];

// Speisen mit tages_menu = 1
$tm_speisen = get_posts( [
    'post_type'      => 'speise',
    'posts_per_page' => 3,
    'post_status'    => 'publish',
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'meta_query'     => [ [ 'key' => 'tages_menu', 'value' => '1' ] ],
] );

$tm_has_content = ! empty( $tm_speisen ) || ! empty( $tm_extras );
?>
<?php if ( $tm_aktiv && $tm_has_content ) : ?>
<section class="tagesmenu-preview section" aria-label="Tagesmenu Vorschau">
    <div class="container">
        <div class="section-heading">
            <span class="section-kicker">🍜 Mittagsmenu</span>
            <h2>Heute frisch gekocht</h2>
            <?php if ( $tm_datum ) : ?>
            <p class="tagesmenu-preview-datum"><?php echo esc_html( $tm_datum ); ?></p>
            <?php elseif ( $tm_intro ) : ?>
            <p><?php echo esc_html( $tm_intro ); ?></p>
            <?php endif; ?>
        </div>

        <div class="tagesmenu-preview-grid">
            <?php foreach ( $tm_speisen as $speise ) :
                $preis = get_field( 'tages_preis', $speise->ID ) ?: get_field( 'preis', $speise->ID );
                $badges = get_field( 'badges', $speise->ID ) ?: [];
                $thumb = get_the_post_thumbnail_url( $speise->ID, 'medium' );
            ?>
            <div class="tm-preview-card">
                <?php if ( $thumb ) : ?>
                <div class="tm-preview-image">
                    <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $speise->post_title ); ?>" loading="lazy">
                </div>
                <?php endif; ?>
                <div class="tm-preview-body">
                    <div class="tm-preview-meta">
                        <span class="tm-badge tm-badge--tagesmenu">Tagesmenu</span>
                        <?php foreach ( (array) $badges as $badge ) : ?>
                        <span class="tm-badge tm-badge--<?php echo esc_attr( $badge ); ?>"><?php echo esc_html( vietdura_badge_label( $badge ) ); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <h3><?php echo esc_html( $speise->post_title ); ?></h3>
                    <?php if ( $preis ) : ?>
                    <p class="tm-preview-preis">CHF <strong><?php echo esc_html( $preis ); ?></strong></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <?php foreach ( array_slice( (array) $tm_extras, 0, max( 0, 3 - count( $tm_speisen ) ) ) as $extra ) :
                if ( empty( $extra['titel'] ) ) continue;
            ?>
            <div class="tm-preview-card tm-preview-card--extra">
                <div class="tm-preview-body">
                    <div class="tm-preview-meta">
                        <?php if ( ! empty( $extra['badge'] ) ) : ?>
                        <span class="tm-badge tm-badge--custom"><?php echo esc_html( $extra['badge'] ); ?></span>
                        <?php endif; ?>
                    </div>
                    <h3><?php echo esc_html( $extra['titel'] ); ?></h3>
                    <?php if ( ! empty( $extra['preis'] ) ) : ?>
                    <p class="tm-preview-preis">CHF <strong><?php echo esc_html( $extra['preis'] ); ?></strong></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="section-cta">
            <a href="<?php echo esc_url( home_url( '/mittagsmenu/' ) ); ?>" class="btn btn-primary">
                🍜 Zum Mittagsmenu →
            </a>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════
     3. VOLLSTÄNDIGE SPEISEKARTE MIT FOTOS
════════════════════════════════════════════════════════ -->
<?php
$menu_kategorien = [
    'kindermenu'  => 'Kindermenü',
    'vorspeisen'  => 'Vorspeisen',
    'salate'      => 'Salate',
    'hauptgaenge' => 'Hauptgänge',
    'suppen'      => 'Suppen',
];
$menu_alle_speisen = [];
foreach ( array_keys( $menu_kategorien ) as $kat_slug ) {
    $kat_term = get_term_by( 'slug', $kat_slug, 'speisen_kategorie' );
    if ( ! $kat_term ) continue;
    $kat_speisen = get_posts( [
        'post_type'      => 'speise',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'tax_query'      => [ [ 'taxonomy' => 'speisen_kategorie', 'field' => 'term_id', 'terms' => $kat_term->term_id ] ],
        'meta_key'       => 'menu_nummer',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
    ] );
    if ( ! empty( $kat_speisen ) ) {
        $menu_alle_speisen[ $kat_slug ] = $kat_speisen;
    }
}
?>
<?php if ( ! empty( $menu_alle_speisen ) ) : ?>
<section class="speisekarte-section section" id="speisekarte" aria-labelledby="speisekarte-titel">
    <div class="container">
        <div class="section-heading">
            <span class="section-kicker">Frisch gekocht · Authentisch vietnamesisch</span>
            <h2 id="speisekarte-titel">Unsere Speisekarte 2026</h2>
            <p>Alle Gerichte werden täglich frisch zubereitet – aus Schweizer Zutaten und Original-Rezepten.</p>
        </div>

        <!-- Kategorie-Tabs -->
        <div class="menu-tabs" role="tablist">
            <?php $first = true; foreach ( $menu_kategorien as $slug => $label ) : ?>
            <?php if ( ! isset( $menu_alle_speisen[ $slug ] ) ) continue; ?>
            <button class="menu-tab<?php echo $first ? ' menu-tab--active' : ''; ?>"
                    role="tab"
                    data-tab="<?php echo esc_attr( $slug ); ?>"
                    aria-selected="<?php echo $first ? 'true' : 'false'; ?>">
                <?php echo esc_html( $label ); ?>
                <span class="menu-tab-count"><?php echo count( $menu_alle_speisen[ $slug ] ); ?></span>
            </button>
            <?php $first = false; endforeach; ?>
        </div>

        <!-- Kategorie-Panels -->
        <?php $first = true; foreach ( $menu_kategorien as $slug => $label ) : ?>
        <?php if ( ! isset( $menu_alle_speisen[ $slug ] ) ) continue; ?>
        <div class="menu-panel<?php echo $first ? ' menu-panel--active' : ''; ?>"
             id="menu-panel-<?php echo esc_attr( $slug ); ?>"
             role="tabpanel"
             data-panel="<?php echo esc_attr( $slug ); ?>">
            <div class="menu-grid">
                <?php foreach ( $menu_alle_speisen[ $slug ] as $speise ) :
                    $post_id   = $speise->ID;
                    $nummer    = get_post_meta( $post_id, 'menu_nummer', true );
                    $preis     = get_post_meta( $post_id, 'preis', true );
                    $highlight = get_post_meta( $post_id, 'highlight', true );
                    $thumb_url = get_the_post_thumbnail_url( $post_id, 'medium' );
                    $excerpt   = wp_trim_words( $speise->post_excerpt ?: $speise->post_content, 18 );
                    $allergene        = get_post_meta( $post_id, 'allergene', true );
                    $nicht_bestellbar = get_post_meta( $post_id, 'nicht_bestellbar', true );
                ?>
                <article class="menu-card<?php echo $highlight ? ' menu-card--highlight' : ''; ?>"
                         itemscope itemtype="https://schema.org/MenuItem">
                    <div class="menu-card__image">
                        <?php if ( $thumb_url ) : ?>
                        <img src="<?php echo esc_url( $thumb_url ); ?>"
                             alt="<?php echo esc_attr( $speise->post_title ); ?>"
                             loading="lazy"
                             itemprop="image">
                        <?php else : ?>
                        <div class="menu-card__no-photo">🍜</div>
                        <?php endif; ?>
                        <?php if ( $nummer !== '' ) : ?>
                        <span class="menu-card__nummer"><?php echo esc_html( $nummer ); ?></span>
                        <?php endif; ?>
                        <?php if ( $highlight ) : ?>
                        <span class="menu-card__top-badge">⭐ Haus-Hit</span>
                        <?php endif; ?>
                        <div class="menu-card__name-overlay">
                            <span><?php echo esc_html( $speise->post_title ); ?></span>
                        </div>
                    </div>
                    <div class="menu-card__body">
                        <h3 class="menu-card__title" itemprop="name">
                            <?php echo esc_html( $speise->post_title ); ?>
                        </h3>
                        <?php if ( $excerpt ) : ?>
                        <p class="menu-card__desc" itemprop="description">
                            <?php echo esc_html( $excerpt ); ?>
                        </p>
                        <?php endif; ?>
                        <?php if ( $allergene ) : ?>
                        <p class="menu-card__allergene">
                            <span class="menu-card__allergene-label">Enthält:</span>
                            <?php echo esc_html( $allergene ); ?>
                        </p>
                        <?php endif; ?>
                        <div class="menu-card__footer">
                            <?php if ( $preis ) : ?>
                            <span class="menu-card__preis" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                                <span itemprop="price" content="<?php echo esc_attr( $preis ); ?>"><span class="preis-chf">CHF</span> <?php echo esc_html( $preis ); ?></span>
                            </span>
                            <?php endif; ?>
                            <?php if ( $nicht_bestellbar ) : ?>
                            <span class="nur-restaurant">🏮 Nur im Restaurant</span>
                            <?php else : ?>
                            <button class="btn btn-sm btn-primary add-to-cart-btn"
                                data-post-id="<?php echo esc_attr( $post_id ); ?>"
                                data-title="<?php echo esc_attr( $speise->post_title ); ?>"
                                data-price="<?php echo esc_attr( $preis ); ?>"
                                aria-label="<?php echo esc_attr( $speise->post_title ); ?> bestellen">
                                Bestellen
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php $first = false; endforeach; ?>

        <div class="section-cta">
            <a href="<?php echo esc_url( home_url( '/speisekarte/' ) ); ?>" class="btn btn-outline">
                Zur vollständigen Speisekarte &amp; Online bestellen →
            </a>
            <a href="<?php echo esc_url( home_url( '/deklaration-herkunft/' ) ); ?>" class="dekl-link">
                Deklaration &amp; Herkunft unserer Zutaten
            </a>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════
     4. ÜBER DIE KÜCHE – Vertrauen + SEO
════════════════════════════════════════════════════════ -->
<section class="about section" id="ueber-die-kueche" aria-labelledby="kueche-titel">
    <div class="container">
        <div class="about-grid">
            <div class="about-content">
                <span class="section-kicker">Vietnamesisches Restaurant Tagelswangen</span>
                <h2 id="kueche-titel">Echte vietnamesische Küche – keine Kompromisse.</h2>
                <p>
                    Duyen Hammer-Vo kocht nach Familienrezepten aus drei Generationen Südvietnam –
                    mit <strong>Schweizer Rind, Zürcher Freilandeiern</strong> und ohne ein Gramm Glutamat.
                    Kein Vorkochen. Kein Büfett. Alles à la minute – frisch für Deine Bestellung.
                </p>
                <p>
                    Was Gäste aus Winterthur, Zürich, Effretikon und Illnau immer wieder zurückbringt:
                    das Gefühl, nicht in einem Restaurant zu sein – sondern in einer <strong>Stube unter Freunden</strong>.
                </p>
                <div class="about-actions">
                    <a href="<?php echo esc_url( $vd_reservierung ); ?>" class="btn btn-primary">
                        🗓 Tisch reservieren
                    </a>
                    <a href="<?php echo esc_url( $vd_whatsapp ); ?>" class="btn btn-outline" target="_blank" rel="noopener">
                        💬 WhatsApp Bestellen
                    </a>
                </div>
            </div>
            <div class="about-highlights">
                <div class="about-stat">
                    <span class="about-stat-num">590+</span>
                    <span class="about-stat-label">⭐ Google &amp; TripAdvisor Bewertungen</span>
                </div>
                <div class="about-stat">
                    <span class="about-stat-num">3</span>
                    <span class="about-stat-label">Generationen südvietnamesischer Familienrezepte</span>
                </div>
                <div class="about-stat">
                    <span class="about-stat-num">0g</span>
                    <span class="about-stat-label">Glutamat – in keinem einzigen Gericht</span>
                </div>
                <div class="about-stat">
                    <span class="about-stat-num">10%</span>
                    <span class="about-stat-label">Rabatt auf jede Take-Away Bestellung</span>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════
     5. BEWERTUNGEN – Google + TripAdvisor
════════════════════════════════════════════════════════ -->
<section class="reviews section section-alt" id="bewertungen" aria-labelledby="reviews-titel">
    <div class="container">
        <div class="section-heading">
            <span class="section-kicker">Was Gäste sagen</span>
            <h2 id="reviews-titel">590+ Bewertungen – Google &amp; TripAdvisor</h2>
        </div>

        <?php
        // Trustindex Plugin Shortcodes (nach Plugin-Installation eintragen)
        // Google:      [trustindex-reviews type="google"]
        // TripAdvisor: [trustindex-reviews type="tripadvisor"]
        $google_shortcode      = get_option( 'vd_shortcode_google_reviews', '' );
        $tripadvisor_shortcode = get_option( 'vd_shortcode_tripadvisor_reviews', '' );
        ?>

        <?php if ( $google_shortcode || $tripadvisor_shortcode ) : ?>
        <div class="reviews-platforms">
            <?php if ( $google_shortcode ) : ?>
            <div class="reviews-platform">
                <div class="reviews-platform-label">
                    <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/google-logo.svg' ); ?>" alt="Google" width="80" height="26">
                </div>
                <?php echo do_shortcode( $google_shortcode ); ?>
            </div>
            <?php endif; ?>
            <?php if ( $tripadvisor_shortcode ) : ?>
            <div class="reviews-platform">
                <div class="reviews-platform-label">
                    <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/tripadvisor-logo.svg' ); ?>" alt="TripAdvisor" width="120" height="26">
                </div>
                <?php echo do_shortcode( $tripadvisor_shortcode ); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php else : ?>
        <!-- Platzhalter bis Trustindex Plugin konfiguriert ist -->
        <div class="reviews-grid">
            <blockquote class="review-card">
                <p>«Fühlt sich nicht wie ein Restaurant an – eher wie ein Wohnzimmer, in das man gerne wiederkommt.»</p>
                <footer><cite>— Google-Gast ⭐⭐⭐⭐⭐</cite></footer>
            </blockquote>
            <blockquote class="review-card">
                <p>«Man schmeckt, dass alles frisch ist. Kein Vergleich zu anderen Asianern. Portionen gross und ehrlich.»</p>
                <footer><cite>— Google-Gast ⭐⭐⭐⭐⭐</cite></footer>
            </blockquote>
            <blockquote class="review-card">
                <p>«Die Gastgeber machen den Unterschied. Duyen kocht mit Leidenschaft, Ralph empfängt jeden wie einen alten Freund.»</p>
                <footer><cite>— TripAdvisor-Gast ⭐⭐⭐⭐⭐</cite></footer>
            </blockquote>
        </div>
        <?php endif; ?>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════
     6. ZAHLUNG & TAKE-AWAY
════════════════════════════════════════════════════════ -->
<section class="payment-section section" id="zahlung" aria-labelledby="zahlung-titel">
    <div class="container">
        <div class="payment-grid">
            <div class="payment-content">
                <span class="section-kicker">Bestellen &amp; Bezahlen</span>
                <h2 id="zahlung-titel">Take-Away, Lieferung &amp; Zahlung</h2>
                <p>Bestelle per WhatsApp auf Deine Wunschzeit oder lass Dir via Uber Eats liefern. <strong>10 % Rabatt</strong> auf jede Take-Away Bestellung.</p>
                <div class="takeaway-options">
                    <a href="<?php echo esc_url( $vd_whatsapp ); ?>" class="btn btn-primary" target="_blank" rel="noopener">
                        📱 WhatsApp Bestellen
                    </a>
                    <a href="https://www.ubereats.com/ch/store/vietdura" class="btn btn-outline" target="_blank" rel="noopener">
                        🛵 Uber Eats Lieferung
                    </a>
                </div>
            </div>
            <div class="payment-methods">
                <h3>Akzeptierte Zahlungsarten</h3>
                <div class="payment-icons">
                    <div class="payment-method">
                        <span class="payment-icon">💳</span>
                        <span>Kreditkarte<br><small>Visa / Mastercard</small></span>
                    </div>
                    <div class="payment-method">
                        <span class="payment-icon">📱</span>
                        <span>Twint</span>
                    </div>
                    <div class="payment-method payment-method--highlight">
                        <span class="payment-icon">🍱</span>
                        <span>Lunch-Check</span>
                    </div>
                    <div class="payment-method">
                        <span class="payment-icon">💵</span>
                        <span>Bar</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

</main>

<?php
// ── Restaurant JSON-LD Schema (SEO Rich Results) ──────────────────────────────
$vd_adresse_schema = vietdura_option( 'adresse', 'Zürcherstrasse 48' );
$vd_plz_schema     = vietdura_option( 'plz_ort', '8317 Tagelswangen ZH' );
$schema = [
    '@context'        => 'https://schema.org',
    '@type'           => 'Restaurant',
    'name'            => 'Restaurant Vietdura',
    'description'     => 'Südvietnamesische Familienrezepte – frisch, regional, hausgemacht. Schweizer Rind & Freiland-Poulet, ohne Glutamat. Vietnamesisches Restaurant in Tagelswangen ZH, 20 Minuten ab Zürich und Winterthur.',
    'url'             => 'https://vietdura.ch',
    'telephone'       => '+41449409999',
    'email'           => 'info@vietdura.ch',
    'servesCuisine'   => [ 'Vietnamesisch', 'Südvietnamesisch', 'Asiatisch' ],
    'priceRange'      => 'CHF 20–40',
    'currenciesAccepted' => 'CHF',
    'paymentAccepted' => 'Cash, Credit Card, Twint, Lunch-Check',
    'image'           => $hero_bg_url ?: get_template_directory_uri() . '/assets/img/logo-horizontal.svg',
    'aggregateRating' => [
        '@type'       => 'AggregateRating',
        'ratingValue' => '5',
        'reviewCount' => '590',
        'bestRating'  => '5',
        'worstRating' => '1',
    ],
    'address' => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => 'Zürcherstrasse 48',
        'addressLocality' => 'Tagelswangen',
        'postalCode'      => '8317',
        'addressCountry'  => 'CH',
        'addressRegion'   => 'ZH',
    ],
    'geo' => [
        '@type'     => 'GeoCoordinates',
        'latitude'  => '47.4723',
        'longitude' => '8.7518',
    ],
    'openingHoursSpecification' => [
        [ '@type' => 'OpeningHoursSpecification', 'dayOfWeek' => [ 'Tuesday', 'Wednesday', 'Thursday', 'Friday' ], 'opens' => '11:00', 'closes' => '22:00' ],
        [ '@type' => 'OpeningHoursSpecification', 'dayOfWeek' => [ 'Saturday', 'Sunday' ], 'opens' => '17:00', 'closes' => '22:00' ],
    ],
    'hasMap'    => 'https://maps.google.com/?q=Zürcherstrasse+48+Tagelswangen+ZH',
    'keywords'  => 'Vietnamesisches Restaurant Tagelswangen, Vietnamesisch essen Zürich, vietnamesisches Restaurant Winterthur, Pho Zürich, vietnamesisch Effretikon, Take-Away Tagelswangen, Lunch Tagelswangen',
];
echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
?>

<?php get_footer(); ?>
