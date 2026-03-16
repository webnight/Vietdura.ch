<?php
/**
 * Template Name: Speisekarte
 */
get_header();
    // Alle Kategorien mit Speisen laden
    $kategorien = vietdura_get_geordnete_kategorien( 'speisen_kategorie' );

    // Speisen ohne Kategorie
    $ohne_kategorie = new WP_Query( [
        'post_type'      => 'speise',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'no_found_rows'  => true,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'tax_query'      => [
            [
                'taxonomy' => 'speisen_kategorie',
                'operator' => 'NOT EXISTS',
            ],
        ],
    ] );

    $hat_kategorien = ! is_wp_error( $kategorien ) && ! empty( $kategorien );
    $hat_unkategorisiert = $ohne_kategorie->have_posts();
?>

<main class="site-main">

    <section class="page-hero section" id="speisekarte-start">
        <div class="container">
            <div class="section-heading">
                <span class="section-kicker">Unsere Küche</span>
                <h1>Speisekarte</h1>
                <p>Frisch zubereitet, authentisch gewürzt — täglich für Sie.</p>
            </div>
        </div>
    </section>

    <?php get_template_part( 'template-parts/trust-bar' ); ?>

    <?php if ( $hat_kategorien ) : ?>
    <div class="menu-category-nav-wrap-placeholder"></div>
    <section class="menu-category-nav-wrap">
        <div class="container">
            <nav class="menu-category-nav" aria-label="Kategorien der Speisekarte">
                <a class="menu-category-pill is-active" href="#" data-menu-target="speisekarte-start">Alle</a>
                <?php foreach ( $kategorien as $kategorie ) : ?>
                    <a
                        class="menu-category-pill"
                        href="#"
                        data-menu-target="<?php echo esc_attr( 'speisekarte-' . $kategorie->slug ); ?>"
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
            $speisen = new WP_Query( [
                'post_type'      => 'speise',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'no_found_rows'  => true,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
                'tax_query'      => [
                    [
                        'taxonomy' => 'speisen_kategorie',
                        'field'    => 'term_id',
                        'terms'    => $kategorie->term_id,
                    ],
                ],
            ] );
            ?>
            <?php if ( $speisen->have_posts() ) : ?>
            <section
                id="<?php echo esc_attr( 'speisekarte-' . $kategorie->slug ); ?>"
                class="menu-category section section-alt"
                data-menu-section
            >
                <div class="container">
                    <div class="menu-category-header">
                        <div class="menu-category-header-copy">
                            <span class="menu-category-eyebrow">Kategorie</span>
                            <h2 class="menu-category-title"><?php echo esc_html( $kategorie->name ); ?></h2>
                        </div>
                        <div class="menu-category-meta">
                            <span class="menu-category-count"><?php echo esc_html( $speisen->post_count ); ?> Gerichte</span>
                            <?php if ( $kategorie->description ) : ?>
                                <p class="menu-category-desc"><?php echo esc_html( $kategorie->description ); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="menu-grid">
                        <?php while ( $speisen->have_posts() ) : $speisen->the_post(); ?>
                            <?php vietdura_render_menu_item( get_the_ID() ); ?>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ( $hat_unkategorisiert ) : ?>
    <section id="speisekarte-weitere-speisen" class="menu-category section section-alt" data-menu-section>
        <div class="container">
            <div class="menu-category-header">
                <div class="menu-category-header-copy">
                    <span class="menu-category-eyebrow">Kategorie</span>
                    <h2 class="menu-category-title">Weitere Speisen</h2>
                </div>
                <div class="menu-category-meta">
                    <span class="menu-category-count"><?php echo esc_html( $ohne_kategorie->post_count ); ?> Gerichte</span>
                </div>
            </div>
            <div class="menu-grid">
                <?php while ( $ohne_kategorie->have_posts() ) : $ohne_kategorie->the_post(); ?>
                    <?php vietdura_render_menu_item( get_the_ID() ); ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ( ! $hat_kategorien && ! $hat_unkategorisiert ) : ?>
    <section class="section">
        <div class="container">
            <p style="text-align:center; color: #666;">Die Speisekarte wird derzeit aktualisiert — bitte bald wieder vorbeischauen.</p>
        </div>
    </section>
    <?php endif; ?>

</main>

<?php get_footer(); ?>
