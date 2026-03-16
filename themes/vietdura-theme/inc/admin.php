<?php
// Admin-Anpassungen für VietDura

// ─── VietDura Einstellungsseite ───────────────────────────────────────────────

add_action( 'admin_menu', function () {
	add_submenu_page(
		'vietdura-settings',
		'Bestellsystem & Benachrichtigungen',
		'Bestellsystem',
		'manage_options',
		'vietdura-settings',
		'vd_settings_page'
	);
} );

add_action( 'admin_init', function () {
	register_setting( 'vietdura_settings', 'vd_twint_nummer',       [ 'sanitize_callback' => 'sanitize_text_field' ] );
	register_setting( 'vietdura_settings', 'vd_restaurant_email',   [ 'sanitize_callback' => 'sanitize_email' ] );
	register_setting( 'vietdura_settings', 'vd_reservierung_url',   [ 'sanitize_callback' => 'esc_url_raw' ] );
	register_setting( 'vietdura_settings', 'vd_sevenio_api_key',    [ 'sanitize_callback' => 'sanitize_text_field' ] );
	register_setting( 'vietdura_settings', 'vd_restaurant_telefon', [ 'sanitize_callback' => 'sanitize_text_field' ] );
	register_setting( 'vietdura_settings', 'vd_notify_channel',     [ 'sanitize_callback' => 'sanitize_text_field' ] );
} );

function vd_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;
	?>
	<div class="wrap">
		<h1>VietDura Einstellungen</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'vietdura_settings' ); ?>

			<h2>Bestellsystem</h2>
			<table class="form-table">
				<tr>
					<th><label for="vd_twint_nummer">Twint-Nummer</label></th>
					<td>
						<input type="text" id="vd_twint_nummer" name="vd_twint_nummer"
							value="<?php echo esc_attr( get_option( 'vd_twint_nummer', '' ) ); ?>"
							class="regular-text" placeholder="+41 79 000 00 00">
						<p class="description">Wird in der Bestätigungs-E-Mail an den Kunden angezeigt.</p>
					</td>
				</tr>
				<tr>
					<th><label for="vd_restaurant_email">Restaurant E-Mail</label></th>
					<td>
						<input type="email" id="vd_restaurant_email" name="vd_restaurant_email"
							value="<?php echo esc_attr( get_option( 'vd_restaurant_email', get_option( 'admin_email' ) ) ); ?>"
							class="regular-text" placeholder="bestellungen@vietdura.ch">
						<p class="description">Neue Bestellungen werden an diese Adresse gesendet.</p>
					</td>
				</tr>
			</table>

			<h2>WhatsApp / SMS Benachrichtigung (seven.io)</h2>
			<table class="form-table">
				<tr>
					<th><label for="vd_sevenio_api_key">seven.io API-Key</label></th>
					<td>
						<input type="text" id="vd_sevenio_api_key" name="vd_sevenio_api_key"
							value="<?php echo esc_attr( get_option( 'vd_sevenio_api_key', '' ) ); ?>"
							class="regular-text" placeholder="Ihr seven.io API-Key">
						<p class="description">API-Key aus <a href="https://app.seven.io/settings#api" target="_blank">app.seven.io → Einstellungen → API</a>.</p>
					</td>
				</tr>
				<tr>
					<th><label for="vd_restaurant_telefon">Restaurant Telefon (Empfänger)</label></th>
					<td>
						<input type="text" id="vd_restaurant_telefon" name="vd_restaurant_telefon"
							value="<?php echo esc_attr( get_option( 'vd_restaurant_telefon', '' ) ); ?>"
							class="regular-text" placeholder="+41791234567">
						<p class="description">Nummer die WhatsApp/SMS empfängt. Mit Ländervorwahl, ohne Leerzeichen (z.B. +41791234567).</p>
					</td>
				</tr>
				<tr>
					<th><label for="vd_notify_channel">Kanal</label></th>
					<td>
						<select id="vd_notify_channel" name="vd_notify_channel">
							<option value="whatsapp" <?php selected( get_option( 'vd_notify_channel', 'whatsapp' ), 'whatsapp' ); ?>>WhatsApp</option>
							<option value="sms"      <?php selected( get_option( 'vd_notify_channel', 'whatsapp' ), 'sms' ); ?>>SMS</option>
						</select>
					</td>
				</tr>
			</table>

			<h2>Navigation</h2>
			<table class="form-table">
				<tr>
					<th><label for="vd_reservierung_url">Reservierung URL</label></th>
					<td>
						<input type="text" id="vd_reservierung_url" name="vd_reservierung_url"
							value="<?php echo esc_attr( get_option( 'vd_reservierung_url', '#' ) ); ?>"
							class="regular-text" placeholder="https://...">
						<p class="description">Link des Reservation-Buttons im Header.</p>
					</td>
				</tr>
			</table>

			<?php submit_button( 'Einstellungen speichern' ); ?>
		</form>
	</div>
	<?php
}

// ─── Rank Math: Content AI deaktivieren ───────────────────────────────────────
add_filter( 'rank_math/ai/enabled', '__return_false' );

// ─── Admin CSS einbinden ───────────────────────────────────────────────────────

function vietdura_is_manager(): bool {
	$user = wp_get_current_user();
	return in_array( 'vietdura_manager', (array) $user->roles, true );
}

function vietdura_is_restricted_admin_user(): bool {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	$user             = wp_get_current_user();
	$user_roles       = (array) $user->roles;

	return ! in_array( 'administrator', $user_roles, true );
}

function vietdura_admin_styles() {
	if ( ! vietdura_is_manager() ) return;
	wp_enqueue_style(
		'vietdura-admin',
		get_template_directory_uri() . '/assets/css/admin.css',
		[],
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'admin_enqueue_scripts', 'vietdura_admin_styles' );


// ─── Admin-Menü vereinfachen ───────────────────────────────────────────────────

function vietdura_admin_menu_cleanup() {
	if ( ! vietdura_is_restricted_admin_user() ) {
		return;
	}

	$remove = [
		'edit.php',                          // Beiträge
		'edit-comments.php',                 // Kommentare
		'tools.php',                         // Werkzeuge
		'options-general.php',               // Einstellungen
		'themes.php',                        // Design
		'plugins.php',                       // Plugins
		'users.php',                         // Benutzer
	];

	foreach ( $remove as $item ) {
		remove_menu_page( $item );
	}
}
add_action( 'admin_menu', 'vietdura_admin_menu_cleanup', 999 );

function vietdura_hide_posts_menu_with_css(): void {
	if ( ! vietdura_is_restricted_admin_user() ) {
		return;
	}
	?>
	<style id="vietdura-hide-posts-menu">
		#menu-posts,
		#wp-admin-bar-new-post {
			display: none !important;
		}
	</style>
	<?php
}
add_action( 'admin_head', 'vietdura_hide_posts_menu_with_css', 1 );
add_action( 'wp_head', 'vietdura_hide_posts_menu_with_css', 1 );

function vietdura_remove_post_nodes_from_admin_bar( WP_Admin_Bar $wp_admin_bar ): void {
	if ( ! vietdura_is_restricted_admin_user() ) {
		return;
	}

	$wp_admin_bar->remove_node( 'new-post' );
}
add_action( 'admin_bar_menu', 'vietdura_remove_post_nodes_from_admin_bar', 999 );


// ─── Logo in Admin-Bar ersetzen ────────────────────────────────────────────────

function vietdura_admin_bar_logo( WP_Admin_Bar $wp_admin_bar ): void {
	if ( ! vietdura_is_manager() ) return;

	$logo_url = get_template_directory_uri() . '/assets/img/logo-horizontal-white.svg';

	// WordPress-Logo-Node ersetzen
	$wp_admin_bar->remove_node( 'wp-logo' );
	$wp_admin_bar->add_node( [
		'id'    => 'vd-logo',
		'title' => '<img src="' . esc_url( $logo_url ) . '" alt="Restaurant Vietdura" style="height:46px;width:auto;display:block;margin-top:-7px;">',
		'href'  => admin_url(),
		'meta'  => [ 'class' => 'vd-admin-bar-logo' ],
	] );
}
add_action( 'admin_bar_menu', 'vietdura_admin_bar_logo', 5 );


// ─── Nach Login zum Dashboard weiterleiten ─────────────────────────────────────

function vietdura_login_redirect( string $redirect_to, string $requested_redirect_to, WP_User $user ): string {
	if ( is_wp_error( $user ) ) {
		return $redirect_to;
	}
	return admin_url();
}
add_filter( 'login_redirect', 'vietdura_login_redirect', 10, 3 );

// Profil-Seite beim direkten Aufruf nach Login umleiten
function vietdura_redirect_away_from_profile(): void {
	global $pagenow;
	if ( $pagenow === 'profile.php' && isset( $_SERVER['HTTP_REFERER'] ) ) {
		$referer = wp_unslash( $_SERVER['HTTP_REFERER'] );
		if ( strpos( $referer, 'wp-login.php' ) !== false ) {
			wp_safe_redirect( admin_url() );
			exit;
		}
	}
}
add_action( 'admin_init', 'vietdura_redirect_away_from_profile' );


// ─── Editor für Seiten beim Manager ausblenden ────────────────────────────────

function vietdura_remove_page_editor(): void {
	if ( ! vietdura_is_manager() ) return;
	remove_post_type_support( 'page', 'editor' );
}
add_action( 'init', 'vietdura_remove_page_editor', 20 );

function vietdura_force_remove_posts_menu_for_restricted_users(): void {
	if ( ! vietdura_is_restricted_admin_user() ) {
		return;
	}

	global $menu, $submenu;

	foreach ( (array) $menu as $index => $item ) {
		if ( isset( $item[2] ) && 'edit.php' === $item[2] ) {
			unset( $menu[ $index ] );
		}
	}

	unset( $submenu['edit.php'] );
	remove_menu_page( 'edit.php' );
}
add_action( 'admin_head', 'vietdura_force_remove_posts_menu_for_restricted_users', 999 );


// ─── Dashboard: Standard-Widgets entfernen ─────────────────────────────────────

function vietdura_remove_dashboard_widgets() {
	if ( ! vietdura_is_manager() ) return;
	remove_meta_box( 'dashboard_right_now',        'dashboard', 'normal' );
	remove_meta_box( 'dashboard_activity',         'dashboard', 'normal' );
	remove_meta_box( 'dashboard_quick_press',      'dashboard', 'side' );
	remove_meta_box( 'dashboard_primary',          'dashboard', 'side' );
	remove_meta_box( 'dashboard_site_health',      'dashboard', 'normal' );
	remove_meta_box( 'dashboard_php_nag',          'dashboard', 'normal' );
	remove_meta_box( 'wpseo-dashboard-overview',   'dashboard', 'normal' );
	remove_meta_box( 'rg_forms_dashboard',         'dashboard', 'normal' );
}
add_action( 'wp_dashboard_setup', 'vietdura_remove_dashboard_widgets' );


// ─── Eigenes Dashboard-Widget ─────────────────────────────────────────────────

function vietdura_dashboard_widget_init() {
	if ( ! vietdura_is_manager() ) return;
	wp_add_dashboard_widget(
		'vietdura_dashboard',
		'VietDura',
		'vietdura_dashboard_widget_render'
	);

	// Widget an erste Stelle setzen
	global $wp_meta_boxes;
	$widget = $wp_meta_boxes['dashboard']['normal']['core']['vietdura_dashboard'] ?? null;
	if ( $widget ) {
		unset( $wp_meta_boxes['dashboard']['normal']['core']['vietdura_dashboard'] );
		$wp_meta_boxes['dashboard']['normal']['high']['vietdura_dashboard'] = $widget;
	}
}
add_action( 'wp_dashboard_setup', 'vietdura_dashboard_widget_init' );


function vietdura_dashboard_widget_render() {
	// Statistiken
	$speisen_count   = wp_count_posts( 'speise' )->publish ?? 0;
	$getraenke_count = wp_count_posts( 'getraenk' )->publish ?? 0;

	// Reservationen (CF7 / WPForms entries oder eigener CPT falls vorhanden)
	$reservationen_count = 0;
	if ( post_type_exists( 'reservation' ) ) {
		$reservationen_count = wp_count_posts( 'reservation' )->publish ?? 0;
	} elseif ( post_type_exists( 'wpcf7_contact_form' ) ) {
		$reservationen_count = '—';
	}

	// Nachrichten (ungelesene Kommentare als Proxy falls kein eigener CPT)
	$nachrichten_count = wp_count_comments()->awaiting_moderation ?? 0;

	// Admin-URL Shortcuts
	$url_speisen    = admin_url( 'edit.php?post_type=speise' );
	$url_getraenke  = admin_url( 'edit.php?post_type=getraenk' );
	$url_medien     = admin_url( 'upload.php' );
	$url_neue_speise = admin_url( 'post-new.php?post_type=speise' );
	$url_neues_getraenk = admin_url( 'post-new.php?post_type=getraenk' );

	$user = wp_get_current_user();
	$vorname = $user->first_name ?: $user->display_name;

	$stunde     = (int) current_time( 'G' );
	$wochentag  = (int) current_time( 'w' ); // 0 = Sonntag, 6 = Samstag

	if ( $wochentag === 6 ) {
		$gruss = 'Schönen Samstag';
	} elseif ( $wochentag === 0 ) {
		$gruss = 'Schönen Sonntag';
	} elseif ( $stunde < 11 ) {
		$gruss = 'Guten Morgen';
	} elseif ( $stunde < 14 ) {
		$gruss = 'Guten Mittag';
	} elseif ( $stunde < 18 ) {
		$gruss = 'Guten Nachmittag';
	} else {
		$gruss = 'Guten Abend';
	}
	?>
	<div class="vd-dashboard">

		<!-- Welcome Banner -->
		<div class="vd-welcome">
			<div class="vd-welcome-text">
				<h1><?php echo esc_html( $gruss . ', ' . $vorname ); ?>!</h1>
				<p>Willkommen im VietDura-Backend. Hier verwalten Sie Ihre Speisekarte, Getränke und Inhalte.</p>
			</div>
			<div class="vd-welcome-badge">🍜 VietDura Restaurant</div>
		</div>

		<!-- Stats -->
		<div class="vd-stats">
			<a href="<?php echo esc_url( $url_speisen ); ?>" class="vd-stat-card">
				<div class="vd-stat-icon vd-stat-icon--green">🍽️</div>
				<div class="vd-stat-info">
					<span class="vd-stat-number"><?php echo esc_html( $speisen_count ); ?></span>
					<span class="vd-stat-label">Speisen</span>
				</div>
			</a>
			<a href="<?php echo esc_url( $url_getraenke ); ?>" class="vd-stat-card">
				<div class="vd-stat-icon vd-stat-icon--terra">🥂</div>
				<div class="vd-stat-info">
					<span class="vd-stat-number"><?php echo esc_html( $getraenke_count ); ?></span>
					<span class="vd-stat-label">Getränke</span>
				</div>
			</a>
			<a href="<?php echo esc_url( $url_medien ); ?>" class="vd-stat-card">
				<div class="vd-stat-icon vd-stat-icon--gold">🖼️</div>
				<div class="vd-stat-info">
					<?php
			$img_count = (int) ( new WP_Query( [
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => false,
			] ) )->found_posts;
			?>
			<span class="vd-stat-number"><?php echo esc_html( $img_count ); ?></span>
					<span class="vd-stat-label">Bilder</span>
				</div>
			</a>
		</div>

		<!-- Two Column Grid -->
		<div class="vd-grid-2">

			<!-- Schnellaktionen -->
			<div class="vd-card">
				<div class="vd-card-header">
					<h2>Schnellaktionen</h2>
				</div>
				<div class="vd-card-body">
					<div class="vd-actions-grid">
						<a href="<?php echo esc_url( $url_neue_speise ); ?>" class="vd-action-btn vd-action-btn--primary">
							<span class="vd-action-icon">+</span> Neue Speise
						</a>
						<a href="<?php echo esc_url( $url_neues_getraenk ); ?>" class="vd-action-btn vd-action-btn--secondary">
							<span class="vd-action-icon">+</span> Neues Getränk
						</a>
						<a href="<?php echo esc_url( $url_speisen ); ?>" class="vd-action-btn vd-action-btn--secondary">
							<span class="vd-action-icon">📋</span> Speisekarte
						</a>
						<a href="<?php echo esc_url( $url_getraenke ); ?>" class="vd-action-btn vd-action-btn--secondary">
							<span class="vd-action-icon">🥤</span> Getränkekarte
						</a>
						<a href="<?php echo esc_url( $url_medien ); ?>" class="vd-action-btn vd-action-btn--terra">
							<span class="vd-action-icon">🖼️</span> Medien
						</a>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" class="vd-action-btn vd-action-btn--terra">
							<span class="vd-action-icon">↗</span> Website
						</a>
					</div>
				</div>
			</div>

			<!-- Tipps -->
			<div class="vd-card">
				<div class="vd-card-header">
					<h2>Tipps</h2>
				</div>
				<div class="vd-card-body">
					<ul class="vd-tips-list">
						<li>Speisen mit einem <strong>Bild</strong> werden auf der Website hervorgehoben.</li>
						<li>Die <strong>Reihenfolge</strong> der Speisen kann per Drag & Drop in der Liste angepasst werden.</li>
						<li>Über <strong>Nummer</strong> (z.B. 01, 02) können Sie Speisen nummerieren.</li>
						<li>Bilder am besten im Format <strong>16:9</strong> oder <strong>3:2</strong> hochladen.</li>
						<li>Änderungen auf der Website werden nach dem <strong>Speichern</strong> sofort sichtbar.</li>
					</ul>
				</div>
			</div>

		</div>

	</div>
	<?php
}


// ─── Dashboard-Seite: Widget-Bereich auf volle Breite ────────────────────────

function vietdura_dashboard_columns( $columns ) {
	if ( ! vietdura_is_manager() ) return $columns;
	$columns['dashboard'] = 1;
	return $columns;
}
add_filter( 'screen_layout_columns', 'vietdura_dashboard_columns' );

function vietdura_dashboard_screen_options() {
	$screen = get_current_screen();
	if ( $screen && 'dashboard' === $screen->id ) {
		add_filter( 'get_user_option_screen_layout_dashboard', function() { return 1; } );
	}
}
add_action( 'current_screen', 'vietdura_dashboard_screen_options' );


// ─── Mediathek: Kategorien-Filter ─────────────────────────────────────────────
// Filtert Bilder anhand der Kategorie des zugehörigen Speise/Getränk-Posts

// Hilfsfunktion: Attachment-IDs aller Featured Images einer Kategorie
function vietdura_get_attachment_ids_by_cat( string $taxonomy, string $slug ): array {
	$term = get_term_by( 'slug', $slug, $taxonomy );
	if ( ! $term ) return [];

	$post_type = str_starts_with( $taxonomy, 'speisen' ) ? 'speise' : 'getraenk';

	$posts = get_posts( [
		'post_type'      => $post_type,
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'tax_query'      => [ [
			'taxonomy' => $taxonomy,
			'field'    => 'term_id',
			'terms'    => $term->term_id,
		] ],
	] );

	$ids = [];
	foreach ( $posts as $pid ) {
		$thumb = get_post_thumbnail_id( $pid );
		if ( $thumb ) $ids[] = (int) $thumb;
		$attached = get_posts( [
			'post_type'   => 'attachment',
			'post_parent' => $pid,
			'fields'      => 'ids',
			'numberposts' => -1,
		] );
		$ids = array_merge( $ids, $attached );
	}

	return array_unique( array_filter( $ids ) );
}

// Filter-Dropdowns in der Mediathek (Listenansicht)
add_action( 'restrict_manage_posts', function ( $post_type ) {
	if ( 'attachment' !== $post_type ) return;

	$groups = [
		'speisen_kategorie'   => '🍽 Speisen-Kategorie',
		'getraenke_kategorie' => '🥂 Getränke-Kategorie',
	];

	echo '<span style="display:inline-flex;align-items:center;flex-wrap:nowrap;gap:4px;">';
	foreach ( $groups as $tax => $label ) {
		$terms = get_terms( [ 'taxonomy' => $tax, 'hide_empty' => false ] );
		if ( is_wp_error( $terms ) || empty( $terms ) ) continue;

		$selected = isset( $_GET[ 'vd_' . $tax ] ) ? sanitize_text_field( $_GET[ 'vd_' . $tax ] ) : '';
		echo '<select name="vd_' . esc_attr( $tax ) . '" style="" onchange="this.form.submit()">';
		echo '<option value="">' . esc_html( $label ) . '</option>';
		foreach ( $terms as $term ) {
			printf( '<option value="%s"%s>%s</option>',
				esc_attr( $term->slug ),
				selected( $selected, $term->slug, false ),
				esc_html( $term->name )
			);
		}
		echo '</select> ';
	}

	// Reset-Button: nur wenn mind. ein Filter aktiv
	$active_filters = array_filter( [
		$_GET['vd_speisen_kategorie']   ?? '',
		$_GET['vd_getraenke_kategorie'] ?? '',
	] );
	if ( ! empty( $active_filters ) ) {
		$reset_url = remove_query_arg( [ 'vd_speisen_kategorie', 'vd_getraenke_kategorie' ] );
		echo '<a href="' . esc_url( $reset_url ) . '" style="'
			. 'padding:0 10px;height:28px;line-height:28px;'
			. 'border:1.5px solid rgba(179,92,61,0.35);border-radius:6px;'
			. 'font-size:12px;font-weight:600;background:rgba(179,92,61,0.07);'
			. 'color:#b35c3d;text-decoration:none;white-space:nowrap;'
			. '">✕ Reset</a>';
	}
	echo '</span>';
} );

// Filter in der Listenansicht anwenden
add_action( 'pre_get_posts', function ( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) return;
	$screen = get_current_screen();
	if ( ! $screen || 'upload' !== $screen->id ) return;

	$groups = [ 'speisen_kategorie', 'getraenke_kategorie' ];
	$ids    = null;

	foreach ( $groups as $tax ) {
		$slug = isset( $_GET[ 'vd_' . $tax ] ) ? sanitize_text_field( $_GET[ 'vd_' . $tax ] ) : '';
		if ( $slug === '' ) continue;
		$found = vietdura_get_attachment_ids_by_cat( $tax, $slug );
		$ids   = $ids === null ? $found : array_intersect( $ids, $found );
	}

	if ( $ids !== null ) {
		$query->set( 'post__in', empty( $ids ) ? [ 0 ] : array_values( $ids ) );
	}
} );

// Filter in der Grid-Ansicht (AJAX) anwenden
// WP übergibt Custom-Props als $_REQUEST['query']['vd_...'] (innerhalb des query-Arrays)
add_filter( 'ajax_query_attachments_args', function ( $query ) {
	$query_request = isset( $_REQUEST['query'] ) ? (array) $_REQUEST['query'] : [];
	$groups = [ 'speisen_kategorie', 'getraenke_kategorie' ];
	$ids    = null;

	foreach ( $groups as $tax ) {
		$slug = isset( $query_request[ 'vd_' . $tax ] ) ? sanitize_text_field( $query_request[ 'vd_' . $tax ] ) : '';
		if ( $slug === '' ) continue;
		$found = vietdura_get_attachment_ids_by_cat( $tax, $slug );
		$ids   = $ids === null ? $found : array_intersect( $ids, $found );
	}

	if ( $ids !== null ) {
		$query['post__in'] = empty( $ids ) ? [ 0 ] : array_values( $ids );
	}

	return $query;
} );

// Spalte: zeigt zugehörigen Speise/Getränk-Post
add_filter( 'manage_upload_columns', function ( $columns ) {
	$columns['vd_zugehoert'] = '🍽 Zugehört zu';
	return $columns;
} );

add_action( 'manage_media_custom_column', function ( $column, $attach_id ) {
	if ( 'vd_zugehoert' !== $column ) return;
	global $wpdb;
	$post_id = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_thumbnail_id' AND meta_value=%d LIMIT 1",
		$attach_id
	) );
	if ( ! $post_id ) {
		$post_id = (int) ( get_post( $attach_id )->post_parent ?? 0 );
	}
	if ( ! $post_id ) { echo '<span style="color:#ccc;">—</span>'; return; }

	$post  = get_post( $post_id );
	$terms = [];
	foreach ( [ 'speisen_kategorie', 'getraenke_kategorie' ] as $tax ) {
		$t = get_the_terms( $post_id, $tax );
		if ( $t && ! is_wp_error( $t ) ) $terms = array_merge( $terms, $t );
	}
	echo '<strong>' . esc_html( $post->post_title ) . '</strong>';
	if ( $terms ) echo '<br><small style="color:#888;">' . esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) ) . '</small>';
}, 10, 2 );

// Grid-Ansicht: Filter-Dropdowns per JS einfügen
add_action( 'admin_footer-upload.php', function () {
	$speisen_terms   = get_terms( [ 'taxonomy' => 'speisen_kategorie',   'hide_empty' => false ] );
	$getraenke_terms = get_terms( [ 'taxonomy' => 'getraenke_kategorie', 'hide_empty' => false ] );
	if ( is_wp_error( $speisen_terms ) )   $speisen_terms   = [];
	if ( is_wp_error( $getraenke_terms ) ) $getraenke_terms = [];
	?>
	<script>
	jQuery(function($){
		var speisTerms    = <?php echo wp_json_encode( array_values( array_map( fn($t) => ['slug'=>$t->slug,'name'=>$t->name], $speisen_terms ) ) ); ?>;
		var getraenkTerms = <?php echo wp_json_encode( array_values( array_map( fn($t) => ['slug'=>$t->slug,'name'=>$t->name], $getraenke_terms ) ) ); ?>;

		function buildSelect(label, key, terms) {
			var $sel = $('<select>').css({
				padding:'0 8px', height:'30px',
				border:'1.5px solid rgba(47,107,85,0.3)', borderRadius:'6px',
				fontSize:'13px', background:'#f7f3ec', color:'#1f2933', cursor:'pointer'
			}).attr('data-vd-key', key);
			$sel.append($('<option>').val('').text(label));
			$.each(terms, function(_, t){
				$sel.append($('<option>').val(t.slug).text(t.name));
			});
			return $sel;
		}

		function injectFilters() {
			if ($('#vd-media-cat-filters').length) return;
			var $toolbar = $('.media-toolbar-secondary');
			if (!$toolbar.length) return;
			var $wrap = $('<span id="vd-media-cat-filters">').css({ display:'inline-flex', alignItems:'center', flexWrap:'nowrap', gap:'4px' });
			if (speisTerms.length)    $wrap.append(buildSelect('🍽 Speisen-Kat.', 'vd_speisen_kategorie', speisTerms));
			if (getraenkTerms.length) $wrap.append(buildSelect('🥂 Getränke-Kat.', 'vd_getraenke_kategorie', getraenkTerms));
			// Reset-Button
			var $reset = $('<button type="button" id="vd-media-reset">✕ Reset</button>').css({
				padding:'0 10px', height:'30px',
				border:'1.5px solid rgba(179,92,61,0.35)', borderRadius:'6px',
				fontSize:'12px', fontWeight:'600', background:'rgba(179,92,61,0.07)',
				color:'#b35c3d', cursor:'pointer', display:'none'
			});
			$wrap.append($reset);
			$toolbar.append($wrap);

			function applyGridFilter() {
				if (!wp.media || !wp.media.frame) return;
				try {
					var lib = wp.media.frame.content.get().collection;
					if (!lib) return;
					lib.props.unset('vd_speisen_kategorie',   {silent: true});
					lib.props.unset('vd_getraenke_kategorie', {silent: true});
					var extra = {};
					$('#vd-media-cat-filters select').each(function(){
						if ($(this).val()) extra[$(this).data('vd-key')] = $(this).val();
					});
					lib.props.set(extra);
				} catch(e) { console.error('VD media filter:', e); }
				// Reset-Button ein-/ausblenden
				var hasFilter = false;
				$('#vd-media-cat-filters select').each(function(){ if ($(this).val()) hasFilter = true; });
				$('#vd-media-reset').toggle(hasFilter);
			}

			$wrap.on('change', 'select', applyGridFilter);

			$wrap.on('click', '#vd-media-reset', function(){
				$('#vd-media-cat-filters select').val('');
				$('#vd-media-reset').hide();
				applyGridFilter();
			});
		}

		setTimeout(injectFilters, 600);
		$(document).on('click', '.view-switch button', function(){ setTimeout(injectFilters, 400); });
	});
	</script>
	<?php
} );


// ─── Speisen: Admin-Spalten ────────────────────────────────────────────────────

function vietdura_speise_columns( $columns ) {
	$new = [ 'vd_order' => '⠿' ];
	foreach ( $columns as $key => $value ) {
		if ( 'title' === $key ) {
			$new['speise_thumb']  = 'Bild';
			$new['speise_nummer'] = 'Nr.';
		}
		$new[ $key ] = $value;
	}
	$new['speise_preis']        = 'Preis';
	$new['speise_tagesmenu']    = '🍱 Mittagsmenü';
	$new['speise_kategorie']    = 'Kategorie';
	unset( $new['date'] );
	return $new;
}
add_filter( 'manage_speise_posts_columns', 'vietdura_speise_columns' );


function vietdura_speise_column_content( $column, $post_id ) {
	switch ( $column ) {

		case 'vd_order':
			echo '<span class="vd-drag-handle" style="cursor:grab;font-size:18px;color:#ccc;display:block;text-align:center;" title="Verschieben">⠿</span>';
			break;

		case 'speise_nummer':
			$nummer = get_post_meta( $post_id, 'nummer', true );
			echo '<span class="vd-inline-edit" data-post-id="' . esc_attr( $post_id ) . '" data-field="nummer" title="Klicken zum Bearbeiten">'
				. ( $nummer !== '' && $nummer !== false ? esc_html( $nummer ) : '' )
				. '</span>';
			break;

		case 'speise_thumb':
			$thumb_id = get_post_thumbnail_id( $post_id );
			$thumb    = get_the_post_thumbnail( $post_id, [ 96, 96 ] );
			echo '<span class="vd-thumb-edit" data-post-id="' . esc_attr( $post_id ) . '" data-thumb-id="' . esc_attr( $thumb_id ) . '" title="Klicken zum Ändern">';
			echo $thumb ?: '<span class="vd-thumb-placeholder">＋<br><small>Bild</small></span>';
			echo '</span>';
			break;

		case 'speise_preis':
			$preis = function_exists( 'get_field' ) ? get_field( 'preis', $post_id ) : '';
			echo '<span class="vd-inline-edit" data-post-id="' . esc_attr( $post_id ) . '" data-field="preis" title="Klicken zum Bearbeiten">'
				. ( $preis !== '' && $preis !== false ? esc_html( $preis ) : '' )
				. '</span>';
			break;

		case 'speise_tagesmenu':
			$aktiv       = function_exists( 'get_field' ) ? get_field( 'tages_menu', $post_id ) : get_post_meta( $post_id, 'tages_menu', true );
			$tages_preis = function_exists( 'get_field' ) ? get_field( 'tages_preis', $post_id ) : get_post_meta( $post_id, 'tages_preis', true );
			$btn_style   = $aktiv
				? 'color:#2f6b55;background:rgba(47,107,85,0.1);'
				: 'color:#b35c3d;background:rgba(179,92,61,0.1);';
			echo '<div style="display:inline-flex;align-items:center;gap:6px;" class="vd-tm-wrap" data-post-id="' . esc_attr( $post_id ) . '">';
			echo '<button class="vd-toggle-tagesmenu" data-post-id="' . esc_attr( $post_id ) . '" data-active="' . ( $aktiv ? '1' : '0' ) . '" data-preis="' . esc_attr( $tages_preis ?: '' ) . '" '
				. 'style="border:none;background:none;cursor:pointer;padding:3px 7px;border-radius:6px;font-size:0.75rem;font-weight:600;letter-spacing:0.03em;' . $btn_style . '">'
				. ( $aktiv ? '● An' : '○ Aus' )
				. '</button>';
			if ( $aktiv ) {
				echo '<span style="font-size:0.8rem;color:#555;">CHF</span>';
				echo '<span class="vd-inline-edit" data-post-id="' . esc_attr( $post_id ) . '" data-field="tages_preis" title="Klicken zum Bearbeiten" style="min-width:4em;cursor:pointer;">'
					. ( $tages_preis !== '' && $tages_preis !== false ? esc_html( $tages_preis ) : '<span style="color:#aaa;font-style:italic;">Preis…</span>' )
					. '</span>';
			}
			echo '</div>';
			break;

		case 'speise_kategorie':
			$terms     = get_the_terms( $post_id, 'speisen_kategorie' );
			$current   = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->slug : '';
			$status    = get_post_status( $post_id );
			$active    = ( $status === 'publish' );
			echo '<div style="display:flex;align-items:center;gap:8px;">';
			echo '<span class="vd-cat-edit" data-post-id="' . esc_attr( $post_id ) . '" data-current="' . esc_attr( $current ) . '">';
			echo $current ? esc_html( $terms[0]->name ) : '<span style="color:#aaa;">—</span>';
			echo '</span>';
			echo '<button class="vd-toggle-status" data-post-id="' . esc_attr( $post_id ) . '" data-active="' . ( $active ? '1' : '0' ) . '" title="' . ( $active ? 'Gericht verstecken' : 'Gericht anzeigen' ) . '" style="'
				. 'border:none;background:none;cursor:pointer;padding:3px 6px;border-radius:6px;font-size:0.75rem;font-weight:600;letter-spacing:0.03em;'
				. ( $active ? 'color:#2f6b55;background:rgba(47,107,85,0.1);' : 'color:#b35c3d;background:rgba(179,92,61,0.1);' )
				. '">' . ( $active ? '● Aktiv' : '○ Versteckt' ) . '</button>';
			echo '</div>';
			break;
	}
}
add_action( 'manage_speise_posts_custom_column', 'vietdura_speise_column_content', 10, 2 );


// ─── Getränke: Admin-Spalten ───────────────────────────────────────────────────

function vietdura_getraenk_columns( $columns ) {
	$new = [ 'vd_order' => '⠿' ];
	foreach ( $columns as $key => $value ) {
		$new[ $key ] = $value;
	}
	$new['getraenk_preis']     = 'Preis';
	$new['getraenk_kategorie'] = 'Kategorie';
	unset( $new['date'] );
	return $new;
}
add_filter( 'manage_getraenk_posts_columns', 'vietdura_getraenk_columns' );


function vietdura_getraenk_column_content( $column, $post_id ) {
	switch ( $column ) {

		case 'vd_order':
			echo '<span class="vd-drag-handle" style="cursor:grab;font-size:18px;color:#ccc;display:block;text-align:center;" title="Verschieben">⠿</span>';
			break;

		case 'getraenk_preis':
			$preis = function_exists( 'get_field' ) ? get_field( 'preis', $post_id ) : '';
			echo '<span class="vd-inline-edit" data-post-id="' . esc_attr( $post_id ) . '" data-field="preis" title="Klicken zum Bearbeiten">'
				. ( $preis !== '' && $preis !== false ? esc_html( $preis ) : '' )
				. '</span>';
			break;

		case 'getraenk_kategorie':
			$terms  = get_the_terms( $post_id, 'getraenke_kategorie' );
			$status = get_post_status( $post_id );
			$active = ( $status === 'publish' );
			echo '<div style="display:flex;align-items:center;gap:8px;">';
			if ( $terms && ! is_wp_error( $terms ) ) {
				echo '<span>' . esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) ) . '</span>';
			} else {
				echo '<span style="color:#aaa;">—</span>';
			}
			echo '<button class="vd-toggle-status" data-post-id="' . esc_attr( $post_id ) . '" data-active="' . ( $active ? '1' : '0' ) . '" title="' . ( $active ? 'Getränk verstecken' : 'Getränk anzeigen' ) . '" style="'
				. 'border:none;background:none;cursor:pointer;padding:3px 6px;border-radius:6px;font-size:0.75rem;font-weight:600;letter-spacing:0.03em;'
				. ( $active ? 'color:#2f6b55;background:rgba(47,107,85,0.1);' : 'color:#b35c3d;background:rgba(179,92,61,0.1);' )
				. '">' . ( $active ? '● Aktiv' : '○ Versteckt' ) . '</button>';
			echo '</div>';
			break;
	}
}
add_action( 'manage_getraenk_posts_custom_column', 'vietdura_getraenk_column_content', 10, 2 );


// ─── Inline Editing: Titel, Nr. & Preis ───────────────────────────────────────

function vietdura_inline_edit_footer() {
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, [ 'speise', 'getraenk' ], true ) ) return;
	$nonce = wp_create_nonce( 'vietdura_inline_edit' );
	?>
	<script>
	jQuery(function($){
		var nonce = <?php echo wp_json_encode( $nonce ); ?>;

		// ── Generisches Inline-Edit (Preis, Nr.) ──────────────────────────────
		$(document).on('click', '.vd-inline-edit', function(e){
			var $span  = $(this);
			if ( $span.find('input').length ) return;
			var val    = $span.find('span[style*="italic"]').length ? '' : $span.text().trim();
			var postId = $span.data('post-id');
			var field  = $span.data('field');
			var narrow = (field === 'nummer');
			var $input = $('<input>').attr({ type:'text', value:val })
				.css({ width: narrow ? '60px' : '90px', font:'inherit',
				       padding:'2px 6px', border:'1.5px solid #2f6b55',
				       borderRadius:'6px', background:'#f7f3ec', color:'#1f2933' });
			$span.empty().append($input);
			$input.focus().select();

			function save(){
				var newVal = $input.val().trim();
				$.post(ajaxurl, { action:'vietdura_inline_save',
					post_id:postId, field:field, value:newVal, nonce:nonce },
					function(){
						if (newVal) {
							$span.empty().text(newVal);
						} else {
							$span.empty().append('<span style="color:#aaa;font-style:italic;">Preis…</span>');
						}
					});
			}
			$input.on('blur', save).on('keydown', function(e){
				if (e.key==='Enter'){ e.preventDefault(); save(); }
				if (e.key==='Escape'){ $span.empty().text(val); }
			});
		});

		// ── Titel-Spalte ──────────────────────────────────────────────────────
		$(document).on('click', '.vd-title-edit', function(e){
			var $span  = $(this);
			if ( $span.find('input').length ) return;
			var val    = $span.text().trim();
			var postId = $span.data('post-id');
			var $input = $('<input>').attr({ type:'text', value:val })
				.css({ width:'100%', font:'inherit', fontWeight:'600',
				       padding:'2px 6px', border:'1.5px solid #2f6b55',
				       borderRadius:'6px', background:'#f7f3ec', color:'#1f2933',
				       boxSizing:'border-box' });
			$span.empty().append($input);
			$input.focus().select();

			function saveTitle(){
				var newVal = $input.val().trim();
				if (!newVal){ $span.empty().text(val); return; }
				$.post(ajaxurl, { action:'vietdura_inline_save',
					post_id:postId, field:'_title', value:newVal, nonce:nonce },
					function(){ $span.empty().text(newVal); });
			}
			$input.on('blur', saveTitle).on('keydown', function(e){
				if (e.key==='Enter'){ e.preventDefault(); saveTitle(); }
				if (e.key==='Escape'){ $span.empty().text(val); }
			});
		});

		// ── Kategorie-Dropdown ────────────────────────────────────────────────
		var allCats = <?php
			$all_terms = get_terms( [ 'taxonomy' => 'speisen_kategorie', 'hide_empty' => false ] );
			$cats = [];
			if ( ! is_wp_error( $all_terms ) ) {
				foreach ( $all_terms as $t ) {
					$cats[] = [ 'slug' => $t->slug, 'name' => $t->name ];
				}
			}
			echo wp_json_encode( $cats );
		?>;

		$(document).on('click', '.vd-cat-edit', function(e){
			var $span   = $(this);
			if ( $span.find('select').length ) return;
			var current = $span.data('current');
			var postId  = $span.data('post-id');
			var $sel    = $('<select>').css({ font:'inherit', padding:'2px 6px',
				border:'1.5px solid #2f6b55', borderRadius:'6px',
				background:'#f7f3ec', color:'#1f2933', cursor:'pointer' });
			$sel.append($('<option>').val('').text('— keine —'));
			$.each(allCats, function(_, cat){
				$sel.append($('<option>').val(cat.slug).text(cat.name)
					.prop('selected', cat.slug === current));
			});
			$span.empty().append($sel);
			$sel.focus();

			function saveCat(){
				var newSlug = $sel.val();
				var newName = $sel.find('option:selected').text();
				$.post(ajaxurl, { action:'vietdura_inline_save_cat',
					post_id:postId, term_slug:newSlug, nonce:nonce },
					function(r){
						$span.attr('data-current', newSlug);
						$span.empty().text(newName !== '— keine —' ? newName : '');
						if (!newName || newName === '— keine —') $span.html('<span style="color:#aaa;">—</span>');
					});
			}
			$sel.on('change', saveCat).on('blur', function(){ setTimeout(function(){
				if (!$span.find('select').length) return;
				$span.empty().text($sel.find('option:selected').text());
			}, 200); });
		});

		// ── Bild austauschen via Media Uploader ──────────────────────────────
		var mediaFrame;
		$(document).on('click', '.vd-thumb-edit', function(e){
			e.preventDefault();
			var $wrap  = $(this);
			var postId = $wrap.data('post-id');

			mediaFrame = wp.media({
				title: 'Bild auswählen',
				button: { text: 'Bild verwenden' },
				multiple: false,
				library: { type: 'image' }
			});

			mediaFrame.on('select', function(){
				var attachment = mediaFrame.state().get('selection').first().toJSON();
				var imgUrl = attachment.sizes && attachment.sizes.thumbnail
					? attachment.sizes.thumbnail.url : attachment.url;

				$.post(ajaxurl, {
					action: 'vietdura_inline_save_thumb',
					post_id: postId,
					thumb_id: attachment.id,
					nonce: nonce
				}, function(r){
					if (r.success) {
						$wrap.data('thumb-id', attachment.id);
						$wrap.find('img, .vd-thumb-placeholder').remove();
						$wrap.prepend($('<img>').attr('src', imgUrl)
							.css({ width:'96px', height:'72px', objectFit:'cover',
							       borderRadius:'8px', display:'block' }));
					}
				});
			});

			mediaFrame.open();
		});

		// ── Sichtbarkeit Toggle ───────────────────────────────────────────────
		$(document).on('click', '.vd-toggle-status', function(e){
			e.stopPropagation();
			var $btn   = $(this);
			var postId = $btn.data('post-id');
			var active = $btn.data('active') == '1';
			$btn.prop('disabled', true).css('opacity','0.5');
			$.post(ajaxurl, {
				action: 'vietdura_toggle_status',
				post_id: postId,
				nonce: nonce
			}, function(r){
				if (r.success) {
					var nowActive = r.data.status === 'publish';
					$btn.data('active', nowActive ? '1' : '0')
						.attr('title', nowActive ? 'Gericht verstecken' : 'Gericht anzeigen')
						.text(nowActive ? '● Aktiv' : '○ Versteckt')
						.css({
							color: nowActive ? '#2f6b55' : '#b35c3d',
							background: nowActive ? 'rgba(47,107,85,0.1)' : 'rgba(179,92,61,0.1)',
							opacity: '1'
						}).prop('disabled', false);
					// Zeile visuell markieren wenn versteckt
					$btn.closest('tr').css('opacity', nowActive ? '1' : '0.45');
				}
			});
		});

		// ── Mittagsmenü Toggle ────────────────────────────────────────────────
		$(document).on('click', '.vd-toggle-tagesmenu', function(e){
			e.stopPropagation();
			var $btn   = $(this);
			var postId = $btn.data('post-id');
			$btn.prop('disabled', true).css('opacity','0.5');
			$.post(ajaxurl, {
				action: 'vietdura_toggle_tagesmenu',
				post_id: postId,
				nonce: nonce
			}, function(r){
				if (r.success) {
					var nowActive = r.data.active;
					$btn.data('active', nowActive ? '1' : '0')
						.text(nowActive ? '● An' : '○ Aus')
						.css({
							color: nowActive ? '#2f6b55' : '#b35c3d',
							background: nowActive ? 'rgba(47,107,85,0.1)' : 'rgba(179,92,61,0.1)',
							opacity: '1'
						}).prop('disabled', false);
					// Preis-Feld ein-/ausblenden
					var $wrap = $btn.closest('.vd-tm-wrap');
					if (nowActive) {
						var savedPreis = $btn.data('preis') || '';
						if (!$wrap.find('.vd-inline-edit').length) {
							var displayVal = savedPreis || '';
							var spanHtml = savedPreis
								? $('<span>').text(savedPreis).html()
								: '<span style="color:#aaa;font-style:italic;">Preis…</span>';
							$wrap.append('<span style="font-size:0.8rem;color:#555;"> CHF </span><span class="vd-inline-edit" data-post-id="' + postId + '" data-field="tages_preis" title="Klicken zum Bearbeiten" style="min-width:4em;cursor:pointer;">' + spanHtml + '</span>');
						}
					} else {
						// aktuellen Preis merken bevor Feld entfernt wird
						var currentPreis = $wrap.find('.vd-inline-edit').text().trim();
						if (currentPreis) $btn.data('preis', currentPreis);
						$wrap.find('.vd-inline-edit').prev('span').remove();
						$wrap.find('.vd-inline-edit').remove();
					}
				}
			});
		});

		// Versteckte Zeilen beim Laden abdunkeln
		$('.vd-toggle-status[data-active="0"]').each(function(){
			$(this).closest('tr').css('opacity','0.45');
		});

		// Titel-Link durch editierbaren Span ersetzen
		$('table.wp-list-table tbody tr').each(function(){
			var $td   = $(this).find('td.column-title');
			var $link = $td.find('a.row-title');
			if (!$link.length) return;
			var postId = $(this).attr('id').replace('post-','');
			var $span  = $('<span>').addClass('vd-title-edit')
				.attr({'data-post-id':postId, title:'Klicken zum Bearbeiten'})
				.text($link.text().trim());
			$link.replaceWith($span);
		});
	});
	</script>
	<?php
}
add_action( 'admin_footer', 'vietdura_inline_edit_footer' );

// wp_enqueue_media() muss früh laufen (nicht im footer)
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( ! in_array( $hook, [ 'edit.php', 'post.php', 'post-new.php' ], true ) ) return;
	$screen = get_current_screen();
	if ( $screen && in_array( $screen->post_type, [ 'speise', 'getraenk' ], true ) ) {
		wp_enqueue_media();
	}
} );


// ─── AJAX: Inline-Feld speichern ──────────────────────────────────────────────

function vietdura_ajax_inline_save() {
	check_ajax_referer( 'vietdura_inline_edit', 'nonce' );

	$post_id = (int) $_POST['post_id'];
	$field   = sanitize_key( $_POST['field'] );
	$value   = sanitize_text_field( $_POST['value'] );

	if ( ! current_user_can( 'edit_speisen' ) && ! current_user_can( 'edit_getraenke' ) && ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( -1 );
	}

	if ( '_title' === $field ) {
		wp_update_post( [ 'ID' => $post_id, 'post_title' => $value ] );
	} else {
		update_post_meta( $post_id, $field, $value );
	}

	wp_send_json_success();
}
add_action( 'wp_ajax_vietdura_inline_save', 'vietdura_ajax_inline_save' );


// ─── AJAX: Kategorie wechseln ─────────────────────────────────────────────────

function vietdura_ajax_inline_save_cat() {
	check_ajax_referer( 'vietdura_inline_edit', 'nonce' );

	$post_id  = (int) $_POST['post_id'];
	$term_slug = sanitize_text_field( $_POST['term_slug'] );

	if ( ! current_user_can( 'edit_speisen' ) && ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( -1 );
	}

	if ( $term_slug === '' ) {
		wp_set_object_terms( $post_id, [], 'speisen_kategorie' );
	} else {
		$term = get_term_by( 'slug', $term_slug, 'speisen_kategorie' );
		if ( $term ) {
			wp_set_object_terms( $post_id, [ $term->term_id ], 'speisen_kategorie' );
		}
	}

	wp_send_json_success();
}
add_action( 'wp_ajax_vietdura_inline_save_cat', 'vietdura_ajax_inline_save_cat' );


// ─── AJAX: Bild (Featured Image) setzen ──────────────────────────────────────

function vietdura_ajax_inline_save_thumb() {
	check_ajax_referer( 'vietdura_inline_edit', 'nonce' );

	$post_id  = (int) $_POST['post_id'];
	$thumb_id = (int) $_POST['thumb_id'];

	if ( ! current_user_can( 'edit_speisen' ) && ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( -1 );
	}

	set_post_thumbnail( $post_id, $thumb_id );
	wp_send_json_success();
}
add_action( 'wp_ajax_vietdura_inline_save_thumb', 'vietdura_ajax_inline_save_thumb' );


// ─── AJAX: Sichtbarkeit umschalten ────────────────────────────────────────────

function vietdura_ajax_toggle_status() {
	check_ajax_referer( 'vietdura_inline_edit', 'nonce' );

	$post_id = (int) $_POST['post_id'];

	if ( ! current_user_can( 'edit_speisen' ) && ! current_user_can( 'edit_getraenke' ) && ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( -1 );
	}

	$current = get_post_status( $post_id );
	$new     = ( $current === 'publish' ) ? 'draft' : 'publish';
	wp_update_post( [ 'ID' => $post_id, 'post_status' => $new ] );

	wp_send_json_success( [ 'status' => $new ] );
}
add_action( 'wp_ajax_vietdura_toggle_status', 'vietdura_ajax_toggle_status' );


// ─── AJAX: Mittagsmenü-Toggle ─────────────────────────────────────────────────

function vietdura_ajax_toggle_tagesmenu() {
	check_ajax_referer( 'vietdura_inline_edit', 'nonce' );
	$post_id = (int) $_POST['post_id'];
	if ( ! current_user_can( 'edit_post', $post_id ) ) wp_die( -1 );
	$current = get_post_meta( $post_id, 'tages_menu', true );
	$new     = $current ? '' : '1';
	update_post_meta( $post_id, 'tages_menu', $new );
	wp_send_json_success( [ 'active' => (bool) $new ] );
}
add_action( 'wp_ajax_vietdura_toggle_tagesmenu', 'vietdura_ajax_toggle_tagesmenu' );


// ─── Metabox: Sichtbarkeit auf der Speise-Detail-Seite ────────────────────────

add_action( 'add_meta_boxes', function () {
	foreach ( [ 'speise', 'getraenk' ] as $post_type ) {
		add_meta_box(
			'vietdura_visibility',
			'Sichtbarkeit',
			'vietdura_visibility_metabox',
			$post_type,
			'side',
			'high'
		);
	}
} );

function vietdura_visibility_metabox( $post ) {
	$active = ( $post->post_status === 'publish' );
	wp_nonce_field( 'vietdura_visibility_save', 'vietdura_visibility_nonce' );
	?>
	<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:4px 0;">
		<span id="vd-vis-label" style="font-size:0.88rem;color:<?php echo $active ? '#2f6b55' : '#b35c3d'; ?>;font-weight:600;">
			<?php echo $active ? '● Aktiv – sichtbar auf der Website' : '○ Versteckt – nicht sichtbar'; ?>
		</span>
		<button type="button" id="vd-vis-toggle"
			data-post-id="<?php echo esc_attr( $post->ID ); ?>"
			data-active="<?php echo $active ? '1' : '0'; ?>"
			style="border:none;cursor:pointer;padding:6px 14px;border-radius:8px;font-weight:600;font-size:0.82rem;
				<?php echo $active
					? 'background:rgba(179,92,61,0.1);color:#b35c3d;'
					: 'background:rgba(47,107,85,0.1);color:#2f6b55;'; ?>">
			<?php echo $active ? 'Verstecken' : 'Aktivieren'; ?>
		</button>
	</div>
	<script>
	jQuery(function($){
		$('#vd-vis-toggle').on('click', function(){
			var $btn   = $(this);
			var postId = $btn.data('post-id');
			$btn.prop('disabled', true);
			$.post(ajaxurl, {
				action: 'vietdura_toggle_status',
				post_id: postId,
				nonce: '<?php echo wp_create_nonce( 'vietdura_inline_edit' ); ?>'
			}, function(r){
				if (!r.success) return;
				var nowActive = r.data.status === 'publish';
				$btn.data('active', nowActive ? '1' : '0')
					.text(nowActive ? 'Verstecken' : 'Aktivieren')
					.css({
						background: nowActive ? 'rgba(179,92,61,0.1)' : 'rgba(47,107,85,0.1)',
						color: nowActive ? '#b35c3d' : '#2f6b55'
					}).prop('disabled', false);
				$('#vd-vis-label')
					.text(nowActive ? '● Aktiv – sichtbar auf der Website' : '○ Versteckt – nicht sichtbar')
					.css('color', nowActive ? '#2f6b55' : '#b35c3d');
			});
		});
	});
	</script>
	<?php
}


// ─── Drag & Drop Sortierung ────────────────────────────────────────────────────

function vietdura_sortable_enqueue( $hook ) {
	if ( 'edit.php' !== $hook ) return;
	$screen = get_current_screen();
	if ( ! in_array( $screen->post_type, [ 'speise', 'getraenk' ], true ) ) return;

	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_add_inline_script( 'jquery-ui-sortable', '
jQuery(function($){
    var post_type = "' . esc_js( $screen->post_type ) . '";

    $("table.wp-list-table tbody").sortable({
        items: "tr",
        axis: "y",
        handle: ".vd-drag-handle",
        helper: function(e, tr) {
            var originals = tr.children();
            var helper = tr.clone();
            helper.children().each(function(index) {
                $(this).width(originals.eq(index).width());
            });
            return helper;
        },
        update: function() {
            var order = [];
            $("table.wp-list-table tbody tr").each(function() {
                var id = $(this).attr("id");
                if (id) order.push(id.replace("post-", ""));
            });
            $.post(ajaxurl, {
                action: "vietdura_save_order",
                order: order,
                post_type: post_type,
                nonce: "' . wp_create_nonce( 'vietdura_save_order' ) . '"
            }, function(response) {
                if (response.success) {
                    $("table.wp-list-table tbody tr").css("background", "#eaffea");
                    setTimeout(function(){
                        $("table.wp-list-table tbody tr").css("background", "");
                    }, 600);
                }
            });
        }
    });
});
' );
}
add_action( 'admin_enqueue_scripts', 'vietdura_sortable_enqueue' );


// ─── AJAX: Reihenfolge speichern ───────────────────────────────────────────────

function vietdura_ajax_save_order() {
	check_ajax_referer( 'vietdura_save_order', 'nonce' );
	if ( ! current_user_can( 'edit_speisen' ) && ! current_user_can( 'edit_getraenke' ) && ! current_user_can( 'edit_posts' ) ) wp_die( -1 );

	$order = array_map( 'intval', (array) $_POST['order'] );
	foreach ( $order as $position => $post_id ) {
		wp_update_post( [
			'ID'         => $post_id,
			'menu_order' => $position,
		] );
	}
	wp_send_json_success();
}
add_action( 'wp_ajax_vietdura_save_order', 'vietdura_ajax_save_order' );


// ─── Standardmässig nach menu_order sortieren ─────────────────────────────────

function vietdura_default_orderby( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) return;
	$post_type = $query->get( 'post_type' );
	if ( ! in_array( $post_type, [ 'speise', 'getraenk' ], true ) ) return;
	if ( ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', 'menu_order' );
		$query->set( 'order', 'ASC' );
	}
}
add_action( 'pre_get_posts', 'vietdura_default_orderby' );


// ─── Kategorie-Filter Query ────────────────────────────────────────────────────

function vietdura_filter_by_category( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) return;
	if ( ! function_exists( 'get_current_screen' ) ) return;
	$screen = get_current_screen();
	if ( ! $screen || 'edit' !== $screen->base ) return;

	$tax_map = [
		'speise'   => 'speisen_kategorie',
		'getraenk' => 'getraenke_kategorie',
	];

	$post_type = $screen->post_type;
	if ( ! isset( $tax_map[ $post_type ] ) ) return;

	$tax  = $tax_map[ $post_type ];
	$slug = isset( $_GET[ $tax ] ) ? sanitize_text_field( $_GET[ $tax ] ) : '';

	if ( $slug !== '' ) {
		$query->set( 'tax_query', [ [
			'taxonomy' => $tax,
			'field'    => 'slug',
			'terms'    => $slug,
		] ] );
	}
}
add_action( 'pre_get_posts', 'vietdura_filter_by_category', 20 );


// ─── Kategorie-Filter Dropdown in Speisen/Getränke-Liste ─────────────────────

function vietdura_add_category_filter( $post_type ) {
	$tax_map = [
		'speise'   => 'speisen_kategorie',
		'getraenk' => 'getraenke_kategorie',
	];

	if ( ! isset( $tax_map[ $post_type ] ) ) return;

	$tax      = $tax_map[ $post_type ];
	$selected = $_GET[ $tax ] ?? '';

	$terms = get_terms( [
		'taxonomy'   => $tax,
		'hide_empty' => false,
		'meta_key'   => 'vietdura_order',
		'orderby'    => 'meta_value_num',
		'order'      => 'ASC',
	] );

	if ( is_wp_error( $terms ) || empty( $terms ) ) return;

	echo '<select name="' . esc_attr( $tax ) . '">';
	echo '<option value="">Alle Kategorien</option>';
	foreach ( $terms as $term ) {
		printf(
			'<option value="%s"%s>%s</option>',
			esc_attr( $term->slug ),
			selected( $selected, $term->slug, false ),
			esc_html( $term->name )
		);
	}
	echo '</select>';
}
add_action( 'restrict_manage_posts', 'vietdura_add_category_filter', 10, 1 );


// ─── Hinweis wenn ACF nicht aktiv ist ─────────────────────────────────────────

function vietdura_acf_notice() {
	if ( function_exists( 'get_field' ) ) return;
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, [ 'speise', 'getraenk' ], true ) ) return;
	echo '<div class="notice notice-warning is-dismissible"><p>';
	echo '<strong>VietDura:</strong> Das Plugin <em>Advanced Custom Fields (ACF)</em> ist nicht aktiv. ';
	echo 'Felder wie Preis und Volumen sind erst nach der Aktivierung verfügbar.';
	echo '</p></div>';
}
add_action( 'admin_notices', 'vietdura_acf_notice' );


// ─── Unnötige Meta-Boxen ausblenden ───────────────────────────────────────────

function vietdura_remove_meta_boxes() {
	remove_meta_box( 'slugdiv',            'speise',   'normal' );
	remove_meta_box( 'slugdiv',            'getraenk', 'normal' );
	remove_meta_box( 'commentsdiv',        'speise',   'normal' );
	remove_meta_box( 'commentsdiv',        'getraenk', 'normal' );
	remove_meta_box( 'commentstatusdiv',   'speise',   'normal' );
	remove_meta_box( 'commentstatusdiv',   'getraenk', 'normal' );
}
add_action( 'add_meta_boxes', 'vietdura_remove_meta_boxes', 20 );


// ─── Kategorien: Reihenfolge nach vietdura_order in Edit-Screen ───────────────

// Taxonomy-Checkbox-Liste im Edit-Screen nach vietdura_order sortieren
function vietdura_checklist_args( $args ) {
	$sorted = [ 'speisen_kategorie', 'getraenke_kategorie' ];
	if ( isset( $args['taxonomy'] ) && in_array( $args['taxonomy'], $sorted, true ) ) {
		$args['orderby']  = 'meta_value_num';
		$args['meta_key'] = 'vietdura_order';
		$args['order']    = 'ASC';
	}
	return $args;
}
add_filter( 'wp_terms_checklist_args', 'vietdura_checklist_args', 10, 1 );

// get_terms nach vietdura_order sortieren (nur für unsere Taxonomien)
function vietdura_sort_terms( $terms, $taxonomies, $args ) {
	$sorted = [ 'speisen_kategorie', 'getraenke_kategorie' ];
	$relevant = array_intersect( (array) $taxonomies, $sorted );
	if ( empty( $relevant ) || ! is_array( $terms ) ) return $terms;

	// Nur sortieren wenn keine explizite orderby-Angabe durch WP-Core
	if ( isset( $args['orderby'] ) && $args['orderby'] === 'meta_value_num' ) return $terms;

	usort( $terms, function( $a, $b ) {
		$oa = (int) get_term_meta( $a->term_id, 'vietdura_order', true );
		$ob = (int) get_term_meta( $b->term_id, 'vietdura_order', true );
		return $oa <=> $ob;
	} );

	return $terms;
}
add_filter( 'get_terms', 'vietdura_sort_terms', 10, 3 );


// ─── Kategorien: Drag & Drop Sortierung ───────────────────────────────────────

function vietdura_term_order_column( $columns ) {
	return array_merge( [ 'vd_term_order' => '⠿' ], $columns );
}
add_filter( 'manage_speisen_kategorie_custom_column', '__return_empty_string' );
add_filter( 'manage_getraenke_kategorie_custom_column', '__return_empty_string' );
add_filter( 'manage_edit-speisen_kategorie_columns', 'vietdura_term_order_column' );
add_filter( 'manage_edit-getraenke_kategorie_columns', 'vietdura_term_order_column' );

function vietdura_term_order_column_content( $value, $column_name, $term_id ) {
	if ( 'vd_term_order' === $column_name ) {
		$order = (int) get_term_meta( $term_id, 'vietdura_order', true );
		return '<span class="vd-drag-handle" data-term-id="' . esc_attr( $term_id ) . '" style="cursor:grab;font-size:18px;color:#ccc;display:block;text-align:center;" title="Verschieben">⠿</span>';
	}
	return $value;
}
add_filter( 'manage_speisen_kategorie_custom_column', 'vietdura_term_order_column_content', 10, 3 );
add_filter( 'manage_getraenke_kategorie_custom_column', 'vietdura_term_order_column_content', 10, 3 );

function vietdura_term_sortable_enqueue( $hook ) {
	if ( 'edit-tags.php' !== $hook ) return;
	$taxonomy = $_GET['taxonomy'] ?? '';
	if ( ! in_array( $taxonomy, [ 'speisen_kategorie', 'getraenke_kategorie' ], true ) ) return;

	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_add_inline_script( 'jquery-ui-sortable', '
jQuery(function($){
    var taxonomy = "' . esc_js( $taxonomy ) . '";

    // Tabelle sortierbar machen (ohne thead)
    $("table.wp-list-table tbody").sortable({
        items: "tr",
        axis: "y",
        handle: ".vd-drag-handle",
        helper: function(e, tr) {
            var originals = tr.children();
            var helper = tr.clone();
            helper.children().each(function(index){
                $(this).width(originals.eq(index).width());
            });
            return helper;
        },
        update: function() {
            var order = [];
            $("table.wp-list-table tbody tr").each(function(){
                var handle = $(this).find(".vd-drag-handle");
                var termId = handle.data("term-id");
                if (termId) order.push(termId);
            });
            $.post(ajaxurl, {
                action: "vietdura_save_term_order",
                order: order,
                taxonomy: taxonomy,
                nonce: "' . wp_create_nonce( 'vietdura_save_term_order' ) . '"
            }, function(response){
                if (response.success) {
                    $("table.wp-list-table tbody tr").css("background","#eaffea");
                    setTimeout(function(){
                        $("table.wp-list-table tbody tr").css("background","");
                    }, 600);
                }
            });
        }
    });
});
' );
}
add_action( 'admin_enqueue_scripts', 'vietdura_term_sortable_enqueue' );


function vietdura_ajax_save_term_order() {
	check_ajax_referer( 'vietdura_save_term_order', 'nonce' );
	if ( ! current_user_can( 'manage_categories' ) ) wp_die( -1 );

	$order    = array_map( 'intval', (array) $_POST['order'] );
	$taxonomy = sanitize_key( $_POST['taxonomy'] ?? '' );

	foreach ( $order as $position => $term_id ) {
		update_term_meta( $term_id, 'vietdura_order', $position + 1 );
	}
	wp_send_json_success();
}
add_action( 'wp_ajax_vietdura_save_term_order', 'vietdura_ajax_save_term_order' );


// ─── Admin Footer Text ─────────────────────────────────────────────────────────

// ─── Profil: Farbschema-Auswahl für Manager ausblenden ───────────────────────

add_action( 'admin_head-profile.php', function() {
	if ( ! vietdura_is_manager() ) return;
	echo '<style>
		#color-picker,
		.form-table tr:has(#color-picker) { display: none !important; }
	</style>';
} );

add_filter( 'admin_footer_text', function( $text ) {
	if ( ! vietdura_is_manager() ) return $text;
	return '<span style="color:#6b7c88;font-size:12px;">VietDura Restaurant · Backend</span>';
} );
