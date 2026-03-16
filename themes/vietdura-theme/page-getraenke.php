<?php
/**
 * Template Name: Getränkekarte
 */
get_header();

$kategorien     = vietdura_get_geordnete_kategorien( 'getraenke_kategorie' );
$hat_kategorien = ! empty( $kategorien );

// Getränke ohne Kategorie
$ohne_kategorie = new WP_Query( [
    'post_type'      => 'getraenk',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'no_found_rows'  => true,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'tax_query'      => [
        [
            'taxonomy' => 'getraenke_kategorie',
            'operator' => 'NOT EXISTS',
        ],
    ],
] );

$hat_unkategorisiert = $ohne_kategorie->have_posts();
?>

<main class="site-main">

    <section id="getraenke-start"<?php vietdura_page_hero_bg_attrs(); ?>>
        <?php vietdura_page_hero_overlay(); ?>
        <div class="container-narrow">
            <div class="section-heading">
                <span class="section-kicker">Getränke</span>
                <h1>Getränkekarte</h1>
                <p>Eine sorgfältige Auswahl — von erfrischend bis festlich.</p>
            </div>
        </div>
    </section>

    <?php if ( $hat_kategorien ) : ?>
    <div class="menu-category-nav-wrap-placeholder getraenke-placeholder"></div>
    <section class="menu-category-nav-wrap getraenke-nav-wrap">
        <div class="container">
            <nav class="menu-category-nav" aria-label="Kategorien der Getränkekarte">
                <a class="menu-category-pill is-active" href="#" data-menu-target="getraenke-start">Alle</a>
                <?php foreach ( $kategorien as $kategorie ) : ?>
                    <a
                        class="menu-category-pill"
                        href="#"
                        data-menu-target="<?php echo esc_attr( 'getraenke-' . $kategorie->slug ); ?>"
                    >
                        <?php echo esc_html( $kategorie->name ); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </section>
    <?php endif; ?>

    <?php if ( $hat_kategorien ) : ?>
        <?php foreach ( $kategorien as $kategorie ) : ?>
            <?php
            $getraenke = new WP_Query( [
                'post_type'      => 'getraenk',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'no_found_rows'  => true,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
                'tax_query'      => [
                    [
                        'taxonomy' => 'getraenke_kategorie',
                        'field'    => 'term_id',
                        'terms'    => $kategorie->term_id,
                    ],
                ],
            ] );
            ?>
            <?php if ( $getraenke->have_posts() ) : ?>
            <section
                id="<?php echo esc_attr( 'getraenke-' . $kategorie->slug ); ?>"
                class="menu-category section section-alt"
                data-getraenke-section
            >
                <div class="container-narrow">
                    <div class="menu-category-header">
                        <div class="menu-category-header-copy">
                            <span class="menu-category-eyebrow">Kategorie</span>
                            <h2 class="menu-category-title"><?php echo esc_html( $kategorie->name ); ?></h2>
                        </div>
                        <div class="menu-category-meta">
                            <span class="menu-category-count"><?php echo esc_html( $getraenke->post_count ); ?> Getränke</span>
                            <?php if ( $kategorie->description ) : ?>
                                <p class="menu-category-desc"><?php echo esc_html( $kategorie->description ); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="getraenke-list">
                        <?php while ( $getraenke->have_posts() ) : $getraenke->the_post(); ?>
                            <?php vietdura_render_getraenk_item( get_the_ID() ); ?>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ( $hat_unkategorisiert ) : ?>
    <section id="getraenke-weitere" class="menu-category section section-alt" data-getraenke-section>
        <div class="container-narrow">
            <div class="menu-category-header">
                <div class="menu-category-header-copy">
                    <span class="menu-category-eyebrow">Kategorie</span>
                    <h2 class="menu-category-title">Weitere Getränke</h2>
                </div>
                <div class="menu-category-meta">
                    <span class="menu-category-count"><?php echo esc_html( $ohne_kategorie->post_count ); ?> Getränke</span>
                </div>
            </div>
            <div class="getraenke-list">
                <?php while ( $ohne_kategorie->have_posts() ) : $ohne_kategorie->the_post(); ?>
                    <?php vietdura_render_getraenk_item( get_the_ID() ); ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ( ! $hat_kategorien && ! $hat_unkategorisiert ) : ?>
    <section class="section">
        <div class="container">
            <p style="text-align:center; color: #666;">Die Getränkekarte wird derzeit aktualisiert — bitte bald wieder vorbeischauen.</p>
        </div>
    </section>
    <?php endif; ?>

</main>

<?php get_footer(); ?>
