<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<header class="site-header">
    <div class="container header-inner">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-branding">
            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo-horizontal.svg' ); ?>"
                 alt="Restaurant Vietdura"
                 class="site-logo"
                 width="200"
                 height="42">
        </a>

        <nav class="main-navigation" id="main-nav-list" aria-label="Hauptnavigation">
            <?php
            wp_nav_menu( [
                'theme_location' => 'main_menu',
                'container'      => false,
                'menu_class'     => 'nav-list',
                'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                'fallback_cb'    => false,
            ] );
            ?>
            <?php $reservierung_url = get_option( 'vd_reservierung_url', '#' ); ?>
            <a href="<?php echo esc_url( $reservierung_url ); ?>" class="header-cta header-cta--menu">Reservation</a>
        </nav>

        <?php $reservierung_url = get_option( 'vd_reservierung_url', '#' ); ?>
        <a href="<?php echo esc_url( $reservierung_url ); ?>" class="header-cta header-cta--desktop">Reservation</a>

        <button id="vd-cart-toggle" class="vd-cart-btn" aria-label="Warenkorb öffnen">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <span class="vd-cart-badge" aria-live="polite">0</span>
        </button>

        <button class="burger-btn" aria-label="Menü öffnen" aria-expanded="false" aria-controls="main-nav-list">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>

<!-- Cart-Flyout -->
<div id="vd-cart-overlay"></div>
<div id="vd-cart-flyout" role="dialog" aria-modal="true" aria-label="Warenkorb">
    <div class="vd-cart-header">
        <h3>Warenkorb</h3>
        <button id="vd-cart-close" class="vd-cart-close-btn" aria-label="Warenkorb schliessen">×</button>
    </div>
    <div class="vd-cart-body">
        <div id="vd-cart-items">
            <p class="vd-cart-empty">Dein Warenkorb ist leer.</p>
        </div>
    </div>
    <div class="vd-cart-footer">
        <div class="vd-cart-footer-total">
            <span>Total</span>
            <span class="vd-cart-total-display">CHF 0.00</span>
        </div>
        <p class="vd-min-hint" id="vd-flyout-min-hint"></p>
        <button id="vd-cart-to-checkout" class="vd-btn">
            Zur Bestellung
        </button>
    </div>
</div>
