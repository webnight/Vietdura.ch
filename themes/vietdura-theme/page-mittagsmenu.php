<?php
/**
 * Template Name: Mittagsmenu
 * Zeigt das aktuelle Tagesmenu: Positionen aus ACF Options + Speisen mit tages_menu=1
 */
get_header();

// ── Daten aus ACF Options ──────────────────────────────────────────────────────
$tm_aktiv     = function_exists( 'get_field' ) ? get_field( 'tagesmenu_aktiv',    'option' ) : false;
$tm_datum     = function_exists( 'get_field' ) ? get_field( 'tagesmenu_datum',    'option' ) : '';
$tm_intro     = function_exists( 'get_field' ) ? get_field( 'tagesmenu_intro',    'option' ) : '';
$tm_preis_info = function_exists( 'get_field' ) ? get_field( 'tagesmenu_preis_info', 'option' ) : '';
$tm_extras    = function_exists( 'get_field' ) ? get_field( 'tagesmenu_extras',   'option' ) : [];

// ── Speisen mit tages_menu = 1 ────────────────────────────────────────────────
$speisen_menu = get_posts( [
    'post_type'      => 'speise',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'meta_query'     => [ [
        'key'   => 'tages_menu',
        'value' => '1',
    ] ],
] );

// ── Tages Menue Post Type (eigene Einträge) ───────────────────────────────────
$tages_menue_posts = get_posts( [
    'post_type'      => 'mittagsmenu',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => [ 'meta_value_num' => 'ASC', 'date' => 'ASC' ],
    'meta_key'       => 'reihenfolge',
] );
?>

<main class="site-main" id="main-content">

<section class="page-hero section" id="mittagsmenu-start">
    <div class="container">
        <div class="section-heading">
            <span class="section-kicker">Mittagsmenu</span>
            <h1>Täglich frisch gekocht</h1>
            <?php if ( $tm_datum ) : ?>
            <p><?php echo esc_html( $tm_datum ); ?></p>
            <?php elseif ( $tm_intro ) : ?>
            <p><?php echo esc_html( $tm_intro ); ?></p>
            <?php else : ?>
            <p>Frisch zubereitet, authentisch gewürzt — täglich für Sie.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php get_template_part( 'template-parts/trust-bar' ); ?>

<section class="mittagsmenu-page section">
    <div class="container mittagsmenu-container">

        <?php if ( ! $tm_aktiv && empty( $speisen_menu ) && empty( $tages_menue_posts ) ) : ?>
        <!-- Kein Tagesmenu aktiv -->
        <div class="mittagsmenu-empty">
            <div class="mittagsmenu-empty-icon">🍜</div>
            <h2>Heute kein Mittagsmenu</h2>
            <p>Das aktuelle Tagesmenu ist noch nicht verfügbar.<br>
               Ruf uns an oder schau später nochmal vorbei!</p>
            <a href="<?php echo esc_url( vietdura_option( 'telefon_href', 'tel:+41449409999' ) ); ?>"
               class="btn btn-primary">
                📞 <?php echo esc_html( vietdura_option( 'telefon', '+41 44 940 99 99' ) ); ?>
            </a>
        </div>

        <?php else : ?>

        <div class="menu-grid">

            <?php
            // ── Speisen aus der Speisekarte (tages_menu = 1) ──────────────
            foreach ( $speisen_menu as $speise ) :
                $preis_tages  = get_field( 'tages_preis', $speise->ID );
                $preis_normal = get_field( 'preis',       $speise->ID );
                $preis_wert   = $preis_tages ?: $preis_normal;
                $preis        = $preis_wert ? 'CHF ' . $preis_wert : '';
                $badges       = get_field( 'badges',      $speise->ID ) ?: [];
                $beschreibung = get_the_excerpt( $speise );
                $has_image    = has_post_thumbnail( $speise->ID );
            ?>
            <article class="speise-card">
                <div class="speise-card-image<?php echo $has_image ? '' : ' speise-card-image--empty'; ?>">
                    <?php if ( $has_image ) : ?>
                        <?php echo get_the_post_thumbnail( $speise->ID, 'speise-card', [ 'alt' => esc_attr( $speise->post_title ) ] ); ?>
                    <?php endif; ?>
                    <?php if ( ! empty( $badges ) ) : ?>
                    <div class="speise-card-badges">
                        <?php foreach ( (array) $badges as $badge ) : ?>
                        <span class="dish-badge <?php echo esc_attr( vietdura_badge_class( $badge ) ); ?>">
                            <?php echo esc_html( vietdura_badge_label( $badge ) ); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="speise-card-body">
                    <div class="speise-card-top">
                        <h3><?php echo esc_html( $speise->post_title ); ?></h3>
                        <?php if ( $preis ) : ?>
                        <span class="speise-card-price"><?php echo esc_html( $preis ); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ( $beschreibung ) : ?>
                    <p><?php echo esc_html( $beschreibung ); ?></p>
                    <?php endif; ?>
                    <?php if ( $preis ) : ?>
                    <button class="vd-add-btn"
                        data-cart-add
                        data-post-id="<?php echo esc_attr( $speise->ID ); ?>"
                        data-type="speise"
                        data-context="tagesmenu"
                        aria-label="<?php echo esc_attr( $speise->post_title ); ?> in den Warenkorb">
                        + Bestellen
                    </button>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>

            <?php
            // ── Zusätzliche Positionen aus ACF Options ────────────────────
            if ( $tm_extras ) :
                foreach ( $tm_extras as $extra ) :
                    $e_titel  = $extra['titel'] ?? '';
                    $e_beschr = $extra['beschreibung'] ?? '';
                    $e_preis  = $extra['preis'] ?? '';
                    $e_badge  = $extra['badge'] ?? '';
                    if ( ! $e_titel ) continue;
                    $e_preis_str = $e_preis ? 'CHF ' . $e_preis : '';
            ?>
            <article class="speise-card">
                <div class="speise-card-image speise-card-image--empty">
                    <?php if ( $e_badge ) : ?>
                    <div class="speise-card-badges">
                        <span class="dish-badge"><?php echo esc_html( $e_badge ); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="speise-card-body">
                    <div class="speise-card-top">
                        <h3><?php echo esc_html( $e_titel ); ?></h3>
                        <?php if ( $e_preis_str ) : ?>
                        <span class="speise-card-price"><?php echo esc_html( $e_preis_str ); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ( $e_beschr ) : ?>
                    <p><?php echo esc_html( $e_beschr ); ?></p>
                    <?php endif; ?>
                </div>
            </article>
            <?php
                endforeach;
            endif;
            ?>

            <?php
            // ── Tages Menue Post Type ─────────────────────────────────────
            foreach ( $tages_menue_posts as $tm_post ) :
                $tm_preis_wert = get_post_meta( $tm_post->ID, 'preis', true );
                $tm_preis      = $tm_preis_wert ? 'CHF ' . $tm_preis_wert : '';
                $tm_badges     = get_field( 'badges', $tm_post->ID ) ?: [];
                $tm_beschr     = get_the_excerpt( $tm_post );
                $tm_has_image  = has_post_thumbnail( $tm_post->ID );
            ?>
            <article class="speise-card">
                <div class="speise-card-image<?php echo $tm_has_image ? '' : ' speise-card-image--empty'; ?>">
                    <?php if ( $tm_has_image ) : ?>
                        <?php echo get_the_post_thumbnail( $tm_post->ID, 'speise-card', [ 'alt' => esc_attr( $tm_post->post_title ) ] ); ?>
                    <?php endif; ?>
                    <?php if ( ! empty( $tm_badges ) ) : ?>
                    <div class="speise-card-badges">
                        <?php foreach ( (array) $tm_badges as $badge ) : ?>
                        <span class="dish-badge <?php echo esc_attr( vietdura_badge_class( $badge ) ); ?>">
                            <?php echo esc_html( vietdura_badge_label( $badge ) ); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="speise-card-body">
                    <div class="speise-card-top">
                        <h3><?php echo esc_html( $tm_post->post_title ); ?></h3>
                        <?php if ( $tm_preis ) : ?>
                        <span class="speise-card-price"><?php echo esc_html( $tm_preis ); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ( $tm_beschr ) : ?>
                    <p><?php echo esc_html( $tm_beschr ); ?></p>
                    <?php endif; ?>
                    <?php if ( $tm_preis ) : ?>
                    <button class="vd-add-btn"
                        data-cart-add
                        data-post-id="<?php echo esc_attr( $tm_post->ID ); ?>"
                        data-type="mittagsmenu"
                        aria-label="<?php echo esc_attr( $tm_post->post_title ); ?> in den Warenkorb">
                        + Bestellen
                    </button>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>

        </div><!-- .menu-grid -->

        <?php if ( $tm_preis_info ) : ?>
        <p class="mittagsmenu-preis-info">ℹ️ <?php echo esc_html( $tm_preis_info ); ?></p>
        <?php endif; ?>

        <?php endif; ?>

    </div>
</section>

</main>

<?php get_footer(); ?>
