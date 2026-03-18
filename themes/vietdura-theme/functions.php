<?php
// Theme Setup und Includes

require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/cpt-speisen.php';
require_once get_template_directory() . '/inc/cpt-getraenke.php';
require_once get_template_directory() . '/inc/cpt-tages-menue.php';
require_once get_template_directory() . '/inc/theme-options.php';
require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/acf-fields.php';
require_once get_template_directory() . '/inc/roles.php';
require_once get_template_directory() . '/inc/bestellzeit.php';
require_once get_template_directory() . '/inc/cart.php';
require_once get_template_directory() . '/inc/order.php';
require_once get_template_directory() . '/inc/cpt-bestellungen.php';

if ( is_admin() ) {
	require_once get_template_directory() . '/inc/admin.php';
}

// ── Custom Login Page Styles ──────────────────────────────────────────────────
add_action( 'login_enqueue_scripts', function () {
	wp_enqueue_style(
		'vietdura-login',
		get_template_directory_uri() . '/assets/css/login.css',
		[],
		'1.0'
	);
	// Load Google Fonts used by the theme
	wp_enqueue_style(
		'vietdura-login-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@400;500;600&display=swap',
		[],
		null
	);
} );

// Change login logo link to homepage
add_filter( 'login_headerurl', fn() => home_url() );
add_filter( 'login_headertext', fn() => get_bloginfo( 'name' ) );

// Hero-Bild als Login-Hintergrund
add_action( 'login_enqueue_scripts', function () {
	$front_page_id = get_option( 'page_on_front' );
	$hero_bg = null;

	if ( $front_page_id ) {
		$hero_bg = get_field( 'hero_bg_image', $front_page_id );
		if ( ! $hero_bg ) $hero_bg = get_field( 'hero_bg_image', 'option' );
	}

	$bg_url = '';
	if ( $hero_bg ) {
		$bg_url = is_array( $hero_bg ) ? ( $hero_bg['url'] ?? '' ) : wp_get_attachment_url( $hero_bg );
	}

	if ( ! $bg_url ) return;

	echo '<style>
	body.login {
		background-image: url(' . esc_url( $bg_url ) . ') !important;
		background-size: cover !important;
		background-position: center !important;
		background-repeat: no-repeat !important;
	}
	body.login::before {
		content: "";
		position: fixed;
		inset: 0;
		background: rgba(31, 77, 58, 0.55);
		z-index: 0;
	}
	#login, #loginform, .login h1, .login #nav, .login #backtoblog, .language-switcher {
		position: relative;
		z-index: 1;
	}
	.login h1 a::before {
		color: #fff !important;
	}
	#loginform, #lostpasswordform {
		background: rgba(255,255,255,0.96) !important;
		backdrop-filter: blur(8px);
	}
	.login .message, .login .success {
		background: rgba(255,255,255,0.9) !important;
	}
	.language-switcher {
		color: rgba(255,255,255,0.7);
	}
	.language-switcher select {
		background: rgba(255,255,255,0.15);
		color: #fff;
		border-color: rgba(255,255,255,0.3);
	}
	</style>';
} );

// ── Five Star Restaurant Reservations – Formular-Labels übersetzen ───────────
add_filter( 'rtb_booking_form_fields', function ( $fields ) {
	// Name
	if ( isset( $fields['reservation']['fields']['name'] ) ) {
		$fields['reservation']['fields']['name']['title']       = 'Name';
		$fields['reservation']['fields']['name']['placeholder'] = 'Ihr vollständiger Name';
	}
	// E-Mail
	if ( isset( $fields['reservation']['fields']['email'] ) ) {
		$fields['reservation']['fields']['email']['title']       = 'E-Mail';
		$fields['reservation']['fields']['email']['placeholder'] = 'ihre@email.ch';
	}
	// Telefon
	if ( isset( $fields['reservation']['fields']['phone'] ) ) {
		$fields['reservation']['fields']['phone']['title']       = 'Telefon';
		$fields['reservation']['fields']['phone']['placeholder'] = '+41 79 000 00 00';
	}
	// Datum
	if ( isset( $fields['reservation']['fields']['date'] ) ) {
		$fields['reservation']['fields']['date']['title'] = 'Datum';
	}
	// Uhrzeit
	if ( isset( $fields['reservation']['fields']['time'] ) ) {
		$fields['reservation']['fields']['time']['title'] = 'Uhrzeit';
	}
	// Personenzahl
	if ( isset( $fields['reservation']['fields']['party'] ) ) {
		$fields['reservation']['fields']['party']['title']   = 'Anzahl Personen';
		$fields['reservation']['fields']['party']['blank_option'] = 'Personen wählen';
	}
	// Nachricht
	if ( isset( $fields['reservation']['fields']['message'] ) ) {
		$fields['reservation']['fields']['message']['title']       = 'Nachricht (optional)';
		$fields['reservation']['fields']['message']['placeholder'] = 'Besondere Wünsche, Allergien, Kinderstuhl usw.';
	}
	return $fields;
}, 20 );

// Submit-Button Text übersetzen
add_filter( 'rtb_booking_form_submit_label', function () {
	return 'Tisch reservieren';
} );

// Erfolgsmeldung auf Deutsch
add_filter( 'rtb_booking_form_success_message', function () {
	return 'Vielen Dank! Ihre Reservierung wurde erfolgreich gesendet. Wir bestätigen diese so bald wie möglich.';
} );

// Stelle sicher, dass das Formular auf der Reservierungsseite auch dort POSTed.
// Sonst zeigt das Plugin ggf. auf eine andere "Booking Page" ohne Shortcode -> wirkt wie "funktioniert nicht".
add_filter( 'rtb-setting-booking-page', function ( $value ) {
	if ( is_page_template( 'page-reservierung.php' ) ) {
		return get_queried_object_id();
	}
	return $value;
} );

// ── Pickadate Kalender auf Deutsch erzwingen ────────────────────────────────
add_action( 'wp_enqueue_scripts', function () {
	// de_DE.js als Abhängigkeit zwischen pickadate und dem Booking-Form-Script einfügen
	wp_deregister_script( 'pickadate-i8n' );
	wp_register_script( 'pickadate-i8n',
		plugins_url(
			'lib/simple-admin-pages/lib/pickadate/translations/de_DE.js',
			WP_PLUGIN_DIR . '/restaurant-reservations/restaurant-reservations.php'
		),
		[ 'jquery', 'pickadate', 'pickadate-date', 'pickadate-time' ],
		'1.0',
		true
	);
}, 5 );
// Booking-Form-Script braucht pickadate-i8n als Abhängigkeit
add_action( 'wp_enqueue_scripts', function () {
	// rtb-booking-form abhängig von pickadate-i8n machen
	global $wp_scripts;
	if ( isset( $wp_scripts->registered['rtb-booking-form'] ) ) {
		if ( ! in_array( 'pickadate-i8n', $wp_scripts->registered['rtb-booking-form']->deps, true ) ) {
			$wp_scripts->registered['rtb-booking-form']->deps[] = 'pickadate-i8n';
		}
	}
}, 999 );

// ── Plugin-Texte auf Deutsch (gettext) ──────────────────────────────────────
add_filter( 'gettext', function ( $translated, $original, $domain ) {
	if ( $domain !== 'restaurant-reservations' ) return $translated;

	static $de = [
		'View/Cancel a Reservation'                => 'Reservierung ansehen / stornieren',
		'Make a Reservation'                       => 'Reservierung erstellen',
		'Email'                                    => 'E-Mail',
		'Phone'                                    => 'Telefon',
		'Message'                                  => 'Nachricht (optional)',
		'Add a Message'                            => 'Nachricht hinzufügen',
		'Use the form below to find your reservation' => 'Geben Sie Ihre Daten ein, um Ihre Reservierung zu finden',
		'Modification Code:'                       => 'Änderungscode:',
		'Find Reservations'                        => 'Reservierung suchen',
		'The email you entered is not valid.'      => 'Die eingegebene E-Mail-Adresse ist ungültig.',
		'Date'                                     => 'Datum',
		'Time'                                     => 'Uhrzeit',
		'Party'                                    => 'Anzahl Personen',
		'Name'                                     => 'Name',
		'Today'                                    => 'Heute',
		'Clear'                                    => 'Löschen',
		'Close'                                    => 'Schliessen',
		'Thanks, your booking request is waiting to be confirmed. Updates will be sent to the email address you provided.' => 'Vielen Dank! Ihre Reservierung wartet auf Bestätigung. Sie erhalten eine E-Mail, sobald wir diese bestätigt haben.',
		'Reservation'                              => 'Reservierung',
		'Your booking and personal information exactly matches another booking. If this was not caused by refreshing the page, please call us to make a booking.' => 'Ihre Reservierung stimmt genau mit einer bestehenden Buchung überein. Falls dies nicht durch ein Neuladen der Seite verursacht wurde, rufen Sie uns bitte an.',
	];

	return $de[ $original ] ?? $translated;
}, 10, 3 );

// "← Zu vietdura.ch" Link entfernen
add_action( 'login_footer', function () {
	echo '<script>var el=document.getElementById("backtoblog");if(el)el.remove();</script>';
} );

