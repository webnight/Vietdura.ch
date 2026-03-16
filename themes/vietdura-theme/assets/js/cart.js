/* VietDura — Warenkorb */
(function () {
  'use strict';

  if (typeof vdCart === 'undefined') return;

  var ajax    = vdCart.ajaxUrl;
  var nonce   = vdCart.nonce;
  var minOrd  = parseFloat(vdCart.minOrder) || 20;
  var cartUrl = vdCart.cartUrl;

  /* ── State ── */
  var state = {
    count : parseInt(vdCart.count)  || 0,
    total : parseFloat(vdCart.total) || 0,
    minOk : !!vdCart.minOk,
    items : [],
  };

  /* ── DOM-Helfer ── */
  function qs(sel, ctx)  { return (ctx || document).querySelector(sel); }
  function qsa(sel, ctx) { return (ctx || document).querySelectorAll(sel); }
  function fmt(n)        { return 'CHF ' + parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, "'"); }

  /* ── Mini-Cart Badge aktualisieren ── */
  function updateBadge() {
    qsa('.vd-cart-badge').forEach(function (el) {
      el.textContent = state.count;
      el.style.display = state.count > 0 ? 'flex' : 'none';
    });
    qsa('.vd-cart-total-display').forEach(function (el) {
      el.textContent = fmt(state.total);
    });
  }

  /* ── Gewählte Zahlungsart ermitteln ── */
  function getZahlung() {
    var checked = qs('input[name="zahlung"]:checked');
    return checked ? checked.value : null;
  }

  /* ── Twint-Info ein/ausblenden ── */
  function updateTwintInfo() {
    var info = qs('#vd-twint-info');
    if (!info) return;
    var zahlung = getZahlung();
    info.style.display = zahlung ? 'block' : 'none';
    // Text anpassen je nach Variante
    var textEl = qs('#vd-twint-info-text', info);
    if (textEl) {
      textEl.textContent = zahlung === 'twint_voll'
        ? 'Überweise den Vollbetrag (' + fmt(state.total) + ') an:'
        : 'Überweise mind. ' + fmt(minOrd) + ' an:';
    }
  }

  /* ── Checkout-Button Status ── */
  function updateCheckoutBtn() {
    var btn     = qs('#vd-checkout-btn');
    var hint    = qs('#vd-min-hint');
    if (!btn) return;

    var missing = Math.max(0, minOrd - state.total);
    var ok      = state.minOk && getZahlung() !== null;

    if (ok) {
      btn.disabled = false;
      btn.classList.remove('vd-btn--disabled');
      if (hint) hint.style.display = 'none';
    } else {
      btn.disabled = true;
      btn.classList.add('vd-btn--disabled');
      if (hint && missing > 0) {
        hint.textContent = 'Noch ' + fmt(missing) + ' bis Mindestbestellwert (CHF ' + minOrd.toFixed(2) + ')';
        hint.style.display = 'block';
      } else if (hint) {
        hint.style.display = 'none';
      }
    }
  }

  /* ── Warenkorb-Flyout aktualisieren ── */
  function renderFlyout() {
    var list = qs('#vd-cart-items');
    if (!list) return;

    if (!state.items.length) {
      list.innerHTML = '<p class="vd-cart-empty">Dein Warenkorb ist leer.</p>';
      return;
    }

    var html = '';
    state.items.forEach(function (item) {
      html += '<div class="vd-cart-row" data-id="' + item.post_id + '">';
      html += '<span class="vd-cart-row-name">' + item.name + '</span>';
      html += '<div class="vd-cart-row-right">';
      html += '<div class="vd-qty">';
      html += '<button class="vd-qty-btn vd-qty-minus" aria-label="Weniger">−</button>';
      html += '<span class="vd-qty-val">' + item.menge + '</span>';
      html += '<button class="vd-qty-btn vd-qty-plus" aria-label="Mehr">+</button>';
      html += '</div>';
      html += '<span class="vd-cart-row-price">' + fmt(item.preis * item.menge) + '</span>';
      html += '</div>';
      html += '</div>';
    });

    list.innerHTML = html;

    /* Mengen-Buttons */
    qsa('.vd-qty-plus', list).forEach(function (btn) {
      btn.addEventListener('click', function () {
        var row  = btn.closest('.vd-cart-row');
        var id   = parseInt(row.dataset.id);
        var item = state.items.find(function (i) { return i.post_id === id; });
        if (item) cartUpdate(id, item.menge + 1);
      });
    });
    qsa('.vd-qty-minus', list).forEach(function (btn) {
      btn.addEventListener('click', function () {
        var row  = btn.closest('.vd-cart-row');
        var id   = parseInt(row.dataset.id);
        var item = state.items.find(function (i) { return i.post_id === id; });
        if (item) cartUpdate(id, item.menge - 1);
      });
    });
  }

  /* ── AJAX: Hinzufügen ── */
  function cartAdd(postId, type, context) {
    var btn = qs('[data-cart-add][data-post-id="' + postId + '"]');
    if (btn) { btn.disabled = true; btn.textContent = '…'; }

    var fd = new FormData();
    fd.append('action',  'vd_cart_add');
    fd.append('nonce',   nonce);
    fd.append('post_id', postId);
    fd.append('type',    type || 'speise');
    fd.append('context', context || '');

    fetch(ajax, { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.success) {
          state.count = res.data.count;
          state.total = res.data.total;
          state.minOk = res.data.min_ok;
          updateBadge();
          updateCheckoutBtn();
          loadCart();
        } else if (res.data && res.data.closed) {
          if (btn) {
            btn.disabled = true;
            btn.classList.add('vd-add-btn--closed');
            btn.innerHTML = '<span class="vd-closed-icon">🕐</span> Bestellzeit vorbei';
            btn.title = res.data.message;
          }
        }
      })
      .finally(function () {
        if (btn && !btn.title) { btn.disabled = false; btn.textContent = '+ Bestellen'; }
      });
  }

  /* ── AJAX: Menge ändern ── */
  function cartUpdate(postId, menge) {
    var fd = new FormData();
    fd.append('action',  'vd_cart_update');
    fd.append('nonce',   nonce);
    fd.append('post_id', postId);
    fd.append('menge',   menge);

    fetch(ajax, { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.success) {
          state.count = res.data.count;
          state.total = res.data.total;
          state.minOk = res.data.min_ok;
          state.items = res.data.cart;
          updateBadge();
          updateCheckoutBtn();
          renderFlyout();
          updateCartTotals();
        }
      });
  }

  /* ── AJAX: Warenkorb laden ── */
  function loadCart(cb) {
    var fd = new FormData();
    fd.append('action', 'vd_cart_get');
    fd.append('nonce',  nonce);

    fetch(ajax, { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.success) {
          state.count = res.data.count;
          state.total = res.data.total;
          state.minOk = res.data.min_ok;
          state.items = res.data.cart;
          updateBadge();
          updateCheckoutBtn();
          renderFlyout();
          updateCartTotals();
          if (cb) cb();
        }
      });
  }

  /* ── Cart-Totals auf Checkout-Seite ── */
  function updateCartTotals() {
    var totalEl = qs('#vd-cart-total');
    if (totalEl) totalEl.textContent = fmt(state.total);

    var countEl = qs('#vd-cart-count');
    if (countEl) countEl.textContent = state.count;
  }

  /* ── Flyout öffnen/schliessen ── */
  function showFlyout() {
    var flyout = qs('#vd-cart-flyout');
    if (flyout) {
      flyout.classList.add('is-open');
      document.body.classList.add('vd-cart-open');
    }
  }

  function hideFlyout() {
    var flyout = qs('#vd-cart-flyout');
    if (flyout) {
      flyout.classList.remove('is-open');
      document.body.classList.remove('vd-cart-open');
    }
  }

  /* ── Schritt 1: Bestellung vorbereiten ── */
  function bindCheckoutForm() {
    var form = qs('#vd-order-form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      if (!state.minOk) {
        var hint = qs('#vd-min-hint');
        if (hint) hint.style.display = 'block';
        return;
      }

      var btn = qs('#vd-checkout-btn');
      if (btn) { btn.disabled = true; btn.textContent = 'Wird gesendet …'; }

      var fd = new FormData(form);
      fd.append('action', 'vd_order_prepare');
      fd.append('nonce',  nonce);

      fetch(ajax, { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.success) {
            /* Schritt 1 OK: Zahlungsaufforderung zeigen */
            var d = res.data;
            qs('#vd-order-form-wrap').style.display = 'none';

            var step2 = qs('#vd-payment-step');
            if (step2) {
              qs('#vd-pay-nr',       step2).textContent = d.bestell_nr;
              var nr2 = qs('#vd-pay-nr-2', step2);
              if (nr2) nr2.textContent = d.bestell_nr;
              qs('#vd-pay-betrag',   step2).textContent = fmt(d.anzahlung);
              qs('#vd-pay-twint-nr', step2).textContent = d.twint_nummer;
              if (d.zahlung === 'twint_voll') {
                qs('#vd-pay-hinweis', step2).textContent = 'Bitte überweise den Gesamtbetrag von ' + fmt(d.total) + ' via Twint.';
              } else {
                qs('#vd-pay-hinweis', step2).textContent = 'Bitte überweise mindestens ' + fmt(d.anzahlung) + ' via Twint. Den Rest (' + fmt(d.total - d.anzahlung) + ') bezahlst du bei Abholung bar.';
              }
              step2.dataset.postId       = d.post_id;
              step2.dataset.confirmNonce = d.confirm_nonce;
              step2.style.display = 'block';
            }

            state.count = 0; state.total = 0; state.minOk = false; state.items = [];
            updateBadge();
          } else {
            var err = qs('#vd-order-error');
            if (err) { err.textContent = res.data; err.style.display = 'block'; }
            if (btn) { btn.disabled = false; btn.textContent = 'Jetzt bestellen'; }
          }
        })
        .catch(function () {
          if (btn) { btn.disabled = false; btn.textContent = 'Jetzt bestellen'; }
        });
    });
  }

  /* ── Schritt 2: Zahlung bestätigen ── */
  function bindConfirmBtn() {
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('#vd-confirm-payment-btn');
      if (!btn) return;

      var step2    = qs('#vd-payment-step');
      var postId   = step2 ? step2.dataset.postId       : '';
      var confNonce = step2 ? step2.dataset.confirmNonce : '';

      btn.disabled = true;
      btn.textContent = 'Wird verarbeitet …';

      var fd = new FormData();
      fd.append('action',  'vd_order_confirm');
      fd.append('nonce',   confNonce);
      fd.append('post_id', postId);

      fetch(ajax, { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.success) {
            step2.style.display = 'none';
            var done = qs('#vd-order-confirm');
            if (done) {
              qs('#vd-confirm-nr', done).textContent = res.data.bestell_nr;
              done.style.display = 'block';
            }
          } else {
            var err = qs('#vd-pay-error');
            if (err) { err.textContent = res.data; err.style.display = 'block'; }
            btn.disabled = false;
            btn.textContent = 'Zahlung bestätigen & Bestellung abschliessen';
          }
        })
        .catch(function () {
          btn.disabled = false;
          btn.textContent = 'Zahlung bestätigen & Bestellung abschliessen';
        });
    });
  }

  /* ── Bestellstatus: Buttons beim Load prüfen ── */
  function checkBestellstatus() {
    var btns = qsa('[data-cart-add]');
    if (!btns.length) return;

    var fd = new FormData();
    fd.append('action', 'vd_bestellstatus');
    fd.append('nonce',  nonce);

    fetch(ajax, { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res.success) return;
        var status = res.data;
        btns.forEach(function (btn) {
          var context = btn.dataset.context || '';
          var type    = btn.dataset.type    || 'speise';
          var key     = (context === 'tagesmenu' || type === 'mittagsmenu') ? 'mittagsmenu' : (type === 'getraenk' ? 'getraenk' : 'speise');
          var s = status[key];
          if (s && !s.ok) {
            btn.disabled = true;
            btn.classList.add('vd-add-btn--closed');
            btn.innerHTML = '<span class="vd-closed-icon">🕐</span> Bestellzeit vorbei';
            btn.title = s.message;
          }
        });
      });
  }

  /* ── Init ── */
  document.addEventListener('DOMContentLoaded', function () {
    updateBadge();
    checkBestellstatus();

    /* Add-to-Cart Buttons */
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-cart-add]');
      if (!btn) return;
      e.preventDefault();
      if (btn.classList.contains('vd-add-btn--closed')) return; // bereits geschlossen
      cartAdd(parseInt(btn.dataset.postId), btn.dataset.type || 'speise', btn.dataset.context || '');
    });

    /* Flyout öffnen via Cart-Icon */
    document.addEventListener('click', function (e) {
      if (e.target.closest('#vd-cart-toggle')) {
        e.preventDefault();
        var flyout = qs('#vd-cart-flyout');
        flyout && flyout.classList.contains('is-open') ? hideFlyout() : (loadCart(), showFlyout());
      }
    });

    /* Flyout schliessen */
    document.addEventListener('click', function (e) {
      if (e.target.closest('#vd-cart-close') || e.target.id === 'vd-cart-overlay') {
        hideFlyout();
      }
    });

    /* Zahlungsart-Wechsel → Twint-Info + Button-Status aktualisieren */
    document.addEventListener('change', function (e) {
      if (e.target.name === 'zahlung') {
        updateTwintInfo();
        updateCheckoutBtn();
      }
    });

    /* Zum Checkout */
    document.addEventListener('click', function (e) {
      if (e.target.closest('#vd-cart-to-checkout')) {
        window.location.href = cartUrl;
      }
    });

    /* Checkout-Formular */
    bindCheckoutForm();
    bindConfirmBtn();

    /* Auf Checkout-Seite: Warenkorb laden */
    if (qs('#vd-order-form')) {
      loadCart();
    }
  });

})();
