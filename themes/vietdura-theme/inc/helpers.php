<?php
// Hilfsfunktionen


/**
 * ACF-Feld sicher abrufen.
 * Gibt einen Fallback zurück wenn ACF nicht installiert ist.
 *
 * @param string     $field_name  ACF-Feldname
 * @param int|false  $post_id     Post-ID (false = aktueller Post)
 * @param mixed      $fallback    Rückgabewert wenn Feld leer oder ACF fehlt
 * @return mixed
 */
function vietdura_field( string $field_name, $post_id = false, $fallback = '' ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $fallback;
	}
	$value = get_field( $field_name, $post_id );
	return ( $value !== null && $value !== '' ) ? $value : $fallback;
}


/**
 * Formatierten Preis ausgeben (z.B. "CHF 24.50").
 * Gibt leeren String zurück wenn kein Preis gesetzt.
 *
 * @param int|false $post_id
 * @return string
 */
function vietdura_get_price( $post_id = false ): string {
	$preis = vietdura_field( 'preis', $post_id );
	return $preis ? 'CHF ' . $preis : '';
}


/**
 * Badge-Slug in lesbare Bezeichnung übersetzen.
 *
 * @param string $badge_slug  ACF-Wert des Badge-Feldes
 * @return string
 */
function vietdura_badge_label( string $badge_slug ): string {
	$map = [
		'beliebt'     => 'Beliebt',
		'haus-hit'    => 'Haus-Hit',
		'neu'         => 'Neu',
		'vegetarisch' => 'Vegetarisch',
		'vegan'       => 'Vegan',
		'scharf'      => 'Scharf',
	];
	return $map[ $badge_slug ] ?? '';
}


/**
 * Highlight-Speisen für die Startseite laden.
 * Zuerst als Highlight markierte Speisen, Fallback auf die neuesten 3.
 *
 * @return WP_Query
 */
function vietdura_get_startseite_speisen(): WP_Query {
	$base_args = [
		'post_type'      => 'speise',
		'posts_per_page' => 3,
		'post_status'    => 'publish',
		'no_found_rows'  => true,
		'orderby'        => 'date',
		'order'          => 'DESC',
	];

	// Highlight-Speisen bevorzugen
	$query = new WP_Query( array_merge( $base_args, [
		'meta_query' => [
			[
				'key'     => 'highlight',
				'value'   => '1',
				'compare' => '=',
			],
		],
	] ) );

	// Fallback: neueste 3 Speisen
	if ( ! $query->have_posts() ) {
		$query = new WP_Query( $base_args );
	}

	return $query;
}


/**
 * Badge-CSS-Klasse für einen Badge-Slug zurückgeben.
 *
 * @param string $badge_slug
 * @return string
 */
function vietdura_badge_class( string $badge_slug ): string {
	$map = [
		'beliebt'     => 'badge-popular',
		'haus-hit'    => 'badge-hit',
		'neu'         => 'badge-new',
		'vegetarisch' => 'badge-veg',
		'vegan'       => 'badge-veg',
		'scharf'      => 'badge-hot',
	];
	return $map[ $badge_slug ] ?? '';
}


/**
 * Einzelne Speise als Menu-Item rendern (für Speisekarte).
 *
 * @param int $post_id
 */
function vietdura_render_menu_item( int $post_id ): void {
	$preis        = vietdura_get_price( $post_id );
	$badges       = vietdura_field( 'badges', $post_id );
	$beschreibung = get_the_excerpt();
	$has_image    = has_post_thumbnail();
	?>
	<article class="speise-card">
		<div class="speise-card-image<?php echo $has_image ? '' : ' speise-card-image--empty'; ?>">
			<?php if ( $has_image ) : ?>
				<?php the_post_thumbnail( 'speise-card', [ 'alt' => esc_attr( get_the_title() ) ] ); ?>
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
				<h3><?php the_title(); ?></h3>
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
					data-post-id="<?php echo esc_attr( $post_id ); ?>"
					data-type="speise"
					data-context="speisekarte"
					aria-label="<?php echo esc_attr( get_the_title() ); ?> in den Warenkorb">
					+ Bestellen
				</button>
			<?php endif; ?>
		</div>
	</article>
	<?php
}


/**
 * Einzelnes Getränk als Listen-Item rendern (für Getränkekarte).
 * Kein Bild, dafür Volumen-Anzeige.
 *
 * @param int $post_id
 */
function vietdura_render_getraenk_item( int $post_id ): void {
	$preis        = vietdura_get_price( $post_id );
	$volumen      = vietdura_field( 'volumen', $post_id );
	$beschreibung = get_the_excerpt();
	?>
	<article class="getraenk-item">
		<div class="getraenk-item-header">
			<h3><?php the_title(); ?></h3>
			<?php if ( $volumen ) : ?>
				<span class="getraenk-item-volume"><?php echo esc_html( $volumen ); ?></span>
			<?php endif; ?>
		</div>
		<?php if ( $preis ) : ?>
			<span class="getraenk-item-price"><?php echo esc_html( $preis ); ?></span>
		<?php endif; ?>
		<?php if ( $beschreibung ) : ?>
			<p class="getraenk-item-desc"><?php echo esc_html( $beschreibung ); ?></p>
		<?php endif; ?>
		<?php if ( $preis ) : ?>
			<button class="vd-add-btn"
				data-cart-add
				data-post-id="<?php echo esc_attr( $post_id ); ?>"
				data-type="getraenk"
				aria-label="<?php echo esc_attr( get_the_title() ); ?> in den Warenkorb">
				+ Bestellen
			</button>
		<?php endif; ?>
	</article>
	<?php
}


/**
 * Kategorien einer Taxonomie in der definierten vietdura_order-Reihenfolge laden.
 *
 * @param string $taxonomy  Taxonomie-Slug (z.B. 'speisen_kategorie')
 * @return WP_Term[]
 */
function vietdura_get_geordnete_kategorien( string $taxonomy ): array {
	$terms = get_terms( [
		'taxonomy'   => $taxonomy,
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	] );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return [];
	}

	usort( $terms, function ( $a, $b ) {
		$order_a = (int) get_term_meta( $a->term_id, 'vietdura_order', true );
		$order_b = (int) get_term_meta( $b->term_id, 'vietdura_order', true );
		return $order_a - $order_b;
	} );

	return $terms;
}


/**
 * ACF Options-Feld sicher abrufen (für Options Pages).
 *
 * @param string $field_name  ACF-Feldname
 * @param mixed  $fallback    Rückgabewert wenn Feld leer oder ACF fehlt
 * @return mixed
 */
// vietdura_option() ist in inc/theme-options.php definiert (wp_options, kein ACF nötig)
