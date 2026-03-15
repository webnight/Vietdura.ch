<?php
/**
 * Template Name: Bestellung
 * Checkout-Seite für Online-Bestellungen
 */

// Abholzeiten generieren (heute + morgen, 11:30–21:00 alle 30 Min.)
function vd_get_abholzeiten(): array {
	$slots = [];
	$now   = current_time( 'timestamp' );

	for ( $d = 0; $d <= 1; $d++ ) {
		$tag   = strtotime( "+{$d} days", $now );
		$label = $d === 0 ? 'Heute' : 'Morgen';

		for ( $h = 11; $h <= 20; $h++ ) {
			foreach ( [ '00', '30' ] as $m ) {
				$ts = mktime( $h, (int) $m, 0, date( 'n', $tag ), date( 'j', $tag ), date( 'Y', $tag ) );
				// Nur Slots in mindestens 30 Minuten Vorlaufzeit
				if ( $ts - $now >= 30 * 60 ) {
					$slots[] = $label . ', ' . date( 'H:i', $ts ) . ' Uhr';
				}
			}
		}
		// 21:00 auch anbieten
		$ts_21 = mktime( 21, 0, 0, date( 'n', $tag ), date( 'j', $tag ), date( 'Y', $tag ) );
		if ( $ts_21 - $now >= 30 * 60 ) {
			$slots[] = $label . ', 21:00 Uhr';
		}
	}
	return $slots;
}

get_header();
?>

<!-- Cart-Flyout (global, erscheint auf allen Seiten via header/footer aber hier nochmal für die Checkout-Seite) -->
<div id="vd-cart-overlay"></div>

<main class="site-main">
  <section class="section">
    <div class="container">

      <div class="section-header" style="margin-bottom:48px;">
        <h1 style="font-family:var(--font-heading);font-size:clamp(32px,5vw,48px);font-weight:700;margin:0 0 12px;">Deine Bestellung</h1>
        <p style="color:#666;font-size:16px;margin:0;">
          <a href="<?php echo esc_url( get_permalink( get_page_by_path( 'speisekarte' ) ) ?: home_url( '/speisekarte/' ) ); ?>" style="color:var(--color-green);font-weight:600;">← Zurück zur Speisekarte</a>
        </p>
      </div>

      <!-- Schritt 2: Zahlungsaufforderung (nach vd_order_prepare) -->
      <div id="vd-payment-step" style="display:none;background:#f0f7f4;border-radius:12px;padding:40px 32px;max-width:600px;margin:0 auto;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
        <div style="font-size:48px;text-align:center;margin-bottom:16px;">💳</div>
        <h2 style="font-family:var(--font-heading);font-size:28px;text-align:center;margin:0 0 8px;">Jetzt mit Twint bezahlen</h2>
        <p style="text-align:center;color:#555;margin:0 0 28px;">Deine Bestellnummer: <strong id="vd-pay-nr" style="color:var(--color-green-dark);font-size:20px;"></strong></p>

        <div style="background:#fff;border-radius:8px;padding:20px 24px;margin-bottom:20px;text-align:center;">
          <p style="margin:0 0 4px;font-size:13px;color:#888;text-transform:uppercase;letter-spacing:.05em;">Betrag</p>
          <p style="font-size:36px;font-weight:700;color:var(--color-green-dark);margin:0 0 16px;" id="vd-pay-betrag"></p>
          <p style="margin:0 0 4px;font-size:13px;color:#888;text-transform:uppercase;letter-spacing:.05em;">Twint-Nummer</p>
          <p style="font-size:24px;font-weight:700;margin:0;" id="vd-pay-twint-nr"></p>
        </div>

        <p style="font-size:14px;color:#555;margin:0 0 8px;" id="vd-pay-hinweis"></p>
        <p style="font-size:13px;color:#888;margin:0 0 24px;">Bitte gib deine Bestellnummer <strong id="vd-pay-nr-2"></strong> als Verwendungszweck an.</p>

        <div id="vd-pay-error" style="display:none;background:#fff0f0;border-left:3px solid #e74c3c;color:#c0392b;padding:12px 16px;border-radius:4px;font-size:14px;margin-bottom:16px;"></div>

        <button id="vd-confirm-payment-btn" class="vd-btn" style="background:var(--color-green);">
          ✅ Zahlung abgeschlossen — Bestellung bestätigen
        </button>
        <p style="font-size:12px;color:#aaa;text-align:center;margin:12px 0 0;">Mit diesem Klick bestätigst du, dass du den Betrag via Twint überwiesen hast.</p>
      </div>

      <!-- Bestätigungsblock (wird nach vd_order_confirm eingeblendet) -->
      <div id="vd-order-confirm">
        <div class="vd-confirm-icon">✅</div>
        <h2>Bestellung aufgenommen!</h2>
        <p>Deine Bestell-Nr.: <span class="vd-confirm-nr" id="vd-confirm-nr"></span></p>
        <p>Das Restaurant wurde per WhatsApp benachrichtigt und bereitet deine Bestellung vor.<br>
        Bei Fragen erreichst du uns telefonisch.</p>
        <p style="margin-top:24px;">
          <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="vd-btn" style="width:auto;display:inline-flex;">Zurück zur Startseite</a>
        </p>
      </div>

      <!-- Checkout-Formular + Zusammenfassung -->
      <div class="vd-checkout-wrap" id="vd-order-form-wrap">

        <!-- Formular (links) -->
        <div class="vd-checkout-form">
          <h2>Deine Angaben</h2>

          <div id="vd-order-error"></div>

          <form id="vd-order-form" novalidate>

            <div class="vd-field">
              <label for="vd-name">Name *</label>
              <input type="text" id="vd-name" name="name" required placeholder="Dein Name" autocomplete="name">
            </div>

            <div class="vd-field">
              <label for="vd-telefon">Telefon *</label>
              <input type="tel" id="vd-telefon" name="telefon" required placeholder="+41 79 000 00 00" autocomplete="tel">
            </div>

            <div class="vd-field">
              <label for="vd-abholung">Abholzeit *</label>
              <select id="vd-abholung" name="abholung" required>
                <option value="">Bitte wählen …</option>
                <?php foreach ( vd_get_abholzeiten() as $slot ) : ?>
                  <option value="<?php echo esc_attr( $slot ); ?>"><?php echo esc_html( $slot ); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="vd-field">
              <label>Zahlung *</label>
              <p style="font-size:13px;color:#666;margin:0 0 10px;">Alle Bestellungen erfordern eine Twint-Anzahlung von mind. CHF <?php echo number_format( VD_MIN_ORDER, 2, '.', "'" ); ?>.</p>
              <div class="vd-payment-options">
                <label class="vd-payment-option">
                  <input type="radio" name="zahlung" value="twint" required>
                  <span class="vd-payment-option-label">Twint + Rest bar</span>
                  <span class="vd-payment-option-sub">Mind. CHF <?php echo number_format( VD_MIN_ORDER, 2, '.', "'" ); ?> via Twint, Rest bar bei Abholung</span>
                </label>
                <label class="vd-payment-option">
                  <input type="radio" name="zahlung" value="twint_voll">
                  <span class="vd-payment-option-label">Twint (Vollbetrag)</span>
                  <span class="vd-payment-option-sub">Gesamtbetrag vorab via Twint bezahlen</span>
                </label>
              </div>
            </div>

            <!-- Twint-Anzahlungsinfo (immer sichtbar sobald Zahlung gewählt) -->
            <?php $twint_nummer = get_option( 'vd_twint_nummer', '' ); ?>
            <?php if ( $twint_nummer ) : ?>
            <div id="vd-twint-info" style="display:none;background:#f0f7f4;border-left:4px solid #2f6b55;padding:16px 20px;margin-bottom:20px;border-radius:4px;font-size:14px;">
              <strong id="vd-twint-info-title">Twint-Anzahlung senden:</strong><br>
              <span id="vd-twint-info-text">Überweise mind. CHF <?php echo number_format( VD_MIN_ORDER, 2, '.', "'" ); ?> an:</span><br>
              <span style="font-size:20px;font-weight:700;display:block;margin:8px 0;"><?php echo esc_html( $twint_nummer ); ?></span>
              <small style="color:#666;">Bitte deine Bestellnummer als Verwendungszweck angeben. Das Restaurant bestätigt den Eingang und bereitet deine Bestellung vor.</small>
            </div>
            <?php endif; ?>

            <div class="vd-field">
              <label for="vd-bemerkung">Bemerkung <span style="font-weight:400;color:#999;">(optional)</span></label>
              <textarea id="vd-bemerkung" name="bemerkung" placeholder="z.B. kein Koriander, extra scharf …"></textarea>
            </div>

            <p id="vd-min-hint"></p>

            <button type="submit" class="vd-btn vd-btn--disabled" id="vd-checkout-btn" disabled>
              Jetzt bestellen
            </button>

          </form>
        </div>

        <!-- Zusammenfassung (rechts) -->
        <div class="vd-order-summary">
          <h3>Deine Bestellung</h3>
          <div class="vd-summary-items" id="vd-cart-items">
            <p class="vd-cart-empty">Warenkorb wird geladen …</p>
          </div>
          <div class="vd-summary-total">
            <span>Total</span>
            <span id="vd-cart-total">CHF 0.00</span>
          </div>
          <?php if ( defined( 'VD_MIN_ORDER' ) ) : ?>
          <p style="font-size:12px;color:#888;margin:8px 0 0;text-align:right;">
            Mindestbestellwert: CHF <?php echo number_format( VD_MIN_ORDER, 2, '.', "'" ); ?>
          </p>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
