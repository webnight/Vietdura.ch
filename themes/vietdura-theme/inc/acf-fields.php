<?php
// ACF-Feldgruppen werden direkt im ACF-Plugin verwaltet (Design → Feldgruppen).
// Die programmatische Registrierung ist deaktiviert damit die Felder im UI editierbar sind.
//
// WICHTIG: Falls die Feldgruppen noch nicht in der Datenbank existieren,
// müssen sie einmalig über ACF → Tools → Import JSON importiert werden.
// Die JSON-Exportdatei befindet sich in: inc/acf-export.json

// ── Speise: Feldgruppe mit Preis, Badge, Varianten ───────────────────────────
add_action( 'acf/init', function() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    acf_add_local_field_group( [
        'key'      => 'group_vd_speise_details',
        'title'    => 'Speise Details',
        'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'speise' ] ] ],
        'position' => 'normal',
        'style'    => 'default',
        'active'   => true,
        'fields'   => [
            [
                'key'         => 'field_vd_speise_nummer',
                'label'       => 'Menü-Nr.',
                'name'        => 'menu_nummer',
                'type'        => 'text',
                'placeholder' => 'z.B. 12',
                'wrapper'     => [ 'width' => '20' ],
                'menu_order'  => 0,
            ],
            [
                'key'          => 'field_vd_speise_preis',
                'label'        => 'Preis (CHF)',
                'name'         => 'preis',
                'type'         => 'text',
                'instructions' => 'Nur bei Speisen ohne Varianten. z.B. "24.50"',
                'placeholder'  => '24.50',
                'wrapper'      => [ 'width' => '30' ],
                'menu_order'   => 1,
            ],
            [
                'key'           => 'field_vd_speise_badge',
                'label'         => 'Hinweis',
                'name'          => 'badges',
                'type'          => 'checkbox',
                'choices'       => [
                    'beliebt'     => 'Beliebt',
                    'haus-hit'    => 'Haus-Hit',
                    'neu'         => 'Neu',
                    'vegetarisch' => 'Vegetarisch',
                    'scharf'      => 'Scharf',
                ],
                'default_value' => [],
                'return_format' => 'value',
                'layout'        => 'horizontal',
                'toggle'        => 0,
                'wrapper'       => [ 'width' => '50' ],
                'menu_order'    => 2,
            ],
            [
                'key'         => 'field_vd_speise_allergene',
                'label'       => 'Allergene',
                'name'        => 'allergene',
                'type'        => 'text',
                'placeholder' => 'z.B. Gluten, Soja, Erdnüsse',
                'wrapper'     => [ 'width' => '50' ],
                'menu_order'  => 3,
            ],
            [
                'key'           => 'field_vd_speise_reihenfolge',
                'label'         => 'Reihenfolge',
                'name'          => 'reihenfolge',
                'type'          => 'number',
                'default_value' => 10,
                'wrapper'       => [ 'width' => '25' ],
                'menu_order'    => 4,
            ],
            [
                'key'          => 'field_vd_speise_highlight',
                'label'        => 'Auf Startseite anzeigen',
                'name'         => 'highlight',
                'type'         => 'true_false',
                'instructions' => 'Speise wird im Empfehlungs-Abschnitt auf der Startseite angezeigt.',
                'default_value' => 0,
                'ui'           => 1,
                'ui_on_text'   => 'Ja',
                'ui_off_text'  => 'Nein',
                'wrapper'      => [ 'width' => '50' ],
                'menu_order'   => 5,
            ],
            [
                'key'          => 'field_vd_speise_tages_menu',
                'label'        => '🍜 Im Tagesmenu',
                'name'         => 'tages_menu',
                'type'         => 'true_false',
                'instructions' => 'Speise erscheint heute im Mittagsmenu auf der Website.',
                'default_value' => 0,
                'ui'           => 1,
                'ui_on_text'   => 'Ja – heute',
                'ui_off_text'  => 'Nein',
                'wrapper'      => [ 'width' => '50' ],
                'menu_order'   => 6,
            ],
            [
                'key'          => 'field_vd_speise_tages_preis',
                'label'        => 'Tagesmenu-Preis (CHF)',
                'name'         => 'tages_preis',
                'type'         => 'text',
                'instructions' => 'Sonderpreis für das Tagesmenu. Leer = normaler Preis wird verwendet.',
                'placeholder'  => '18.50',
                'wrapper'      => [ 'width' => '50' ],
                'conditional_logic' => [ [ [ 'field' => 'field_vd_speise_tages_menu', 'operator' => '==', 'value' => '1' ] ] ],
                'menu_order'   => 7,
            ],
        ],
    ] );
} );


// ── Startseite: Feldgruppe auch auf der Front Page anzeigen ──────────────────
add_filter( 'acf/location/rule_match/page_type', function( $match, $rule, $options ) {
    if ( $rule['value'] === 'front_page' && $rule['operator'] === '==' ) {
        $front_id = (int) get_option( 'page_on_front' );
        if ( $front_id && isset( $options['post_id'] ) && (int) $options['post_id'] === $front_id ) {
            return true;
        }
    }
    return $match;
}, 10, 3 );


// ── Startseite: alle Felder der Feldgruppe registrieren ──────────────────────
add_action( 'acf/init', function() {
    if ( ! function_exists( 'acf_add_local_field' ) ) return;
    if ( function_exists( 'acf_add_local_field_group' ) ) {
        acf_add_local_field_group( [
            'key'                   => 'group_vd_startseite',
            'title'                 => 'Startseite',
            'fields'                => [],
            'location'              => [
                [
                    [
                        'param'    => 'page_type',
                        'operator' => '==',
                        'value'    => 'front_page',
                    ],
                ],
                [
                    [
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => 'vietdura-startseite',
                    ],
                ],
            ],
            'menu_order'            => 0,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'active'                => true,
        ] );
    }

    // Tab: Hero-Text
    acf_add_local_field( [
        'key'        => 'field_vd_hero_text_tab',
        'label'      => 'Hero-Inhalt',
        'name'       => 'hero_text_tab',
        'type'       => 'tab',
        'parent'     => 'group_vd_startseite',
        'placement'  => 'top',
        'endpoint'   => 0,
        'menu_order' => 0,
    ] );

    acf_add_local_field( [
        'key'        => 'field_vd_hero_eyebrow',
        'label'      => 'Eyebrow (Kicker über dem Titel)',
        'name'       => 'hero_eyebrow',
        'type'       => 'text',
        'parent'     => 'group_vd_startseite',
        'placeholder' => 'Modernes vietnamesisches Restaurant in Tagelswangen',
        'required'   => 0,
        'menu_order' => 1,
    ] );

    acf_add_local_field( [
        'key'          => 'field_vd_hero_titel',
        'label'        => 'Hero-Titel',
        'name'         => 'hero_titel',
        'type'         => 'text',
        'parent'       => 'group_vd_startseite',
        'instructions' => 'Zeilenumbruch mit | markieren, z.B. "Frisch. Authentisch.|Modern interpretiert."',
        'placeholder'  => 'Frisch. Authentisch.|Modern interpretiert.',
        'required'     => 0,
        'menu_order'   => 2,
    ] );

    acf_add_local_field( [
        'key'        => 'field_vd_hero_text',
        'label'      => 'Hero-Text',
        'name'       => 'hero_text',
        'type'       => 'textarea',
        'parent'     => 'group_vd_startseite',
        'rows'       => 3,
        'new_lines'  => '',
        'required'   => 0,
        'menu_order' => 3,
    ] );

    acf_add_local_field( [
        'key'        => 'field_vd_hero_btn1_text',
        'label'      => 'Button 1 – Text',
        'name'       => 'hero_btn1_text',
        'type'       => 'text',
        'parent'     => 'group_vd_startseite',
        'placeholder' => 'Speisekarte ansehen',
        'required'   => 0,
        'wrapper'    => [ 'width' => '50', 'class' => '', 'id' => '' ],
        'menu_order' => 4,
    ] );

    acf_add_local_field( [
        'key'        => 'field_vd_hero_btn1_url',
        'label'      => 'Button 1 – URL',
        'name'       => 'hero_btn1_url',
        'type'       => 'text',
        'parent'     => 'group_vd_startseite',
        'placeholder' => '/speisekarte',
        'required'   => 0,
        'wrapper'    => [ 'width' => '50', 'class' => '', 'id' => '' ],
        'menu_order' => 5,
    ] );

    acf_add_local_field( [
        'key'        => 'field_vd_hero_btn2_text',
        'label'      => 'Button 2 – Text',
        'name'       => 'hero_btn2_text',
        'type'       => 'text',
        'parent'     => 'group_vd_startseite',
        'placeholder' => 'Tisch reservieren',
        'required'   => 0,
        'wrapper'    => [ 'width' => '50', 'class' => '', 'id' => '' ],
        'menu_order' => 6,
    ] );

    acf_add_local_field( [
        'key'        => 'field_vd_hero_btn2_url',
        'label'      => 'Button 2 – URL',
        'name'       => 'hero_btn2_url',
        'type'       => 'text',
        'parent'     => 'group_vd_startseite',
        'placeholder' => '/reservieren',
        'required'   => 0,
        'wrapper'    => [ 'width' => '50', 'class' => '', 'id' => '' ],
        'menu_order' => 7,
    ] );

    // Tab: Hintergrundbild
    acf_add_local_field( [
        'key'      => 'field_vd_hero_bg_tab',
        'label'    => 'Hintergrundbild',
        'name'     => 'hero_bg_tab',
        'type'     => 'tab',
        'parent'   => 'group_vd_startseite',
        'placement' => 'top',
        'endpoint'  => 0,
        'menu_order' => 10,
    ] );

    acf_add_local_field( [
        'key'          => 'field_vd_hero_bg_image',
        'label'        => 'Hintergrundbild',
        'name'         => 'hero_bg_image',
        'type'         => 'image',
        'parent'       => 'group_vd_startseite',
        'instructions' => 'Wird als Hero-Hintergrund verwendet. Empfohlen: min. 1920×800px.',
        'required'     => 0,
        'return_format' => 'array',
        'preview_size' => 'medium',
        'library'      => 'all',
        'menu_order'   => 11,
    ] );

    acf_add_local_field( [
        'key'          => 'field_vd_hero_bg_overlay_opacity',
        'label'        => 'Overlay-Deckkraft',
        'name'         => 'hero_bg_overlay_opacity',
        'type'         => 'select',
        'parent'       => 'group_vd_startseite',
        'instructions' => 'Grünes Overlay über dem Hintergrundbild. Höherer Wert = dunkler.',
        'required'     => 0,
        'choices'      => [
            '0.35' => 'Leicht (0.35)',
            '0.45' => 'Mittel-leicht (0.45)',
            '0.55' => 'Mittel (0.55)',
            '0.65' => 'Mittel-dunkel (0.65)',
            '0.75' => 'Dunkel (0.75)',
        ],
        'default_value' => '0.55',
        'return_format' => 'value',
        'menu_order'   => 12,
    ] );

    // Tab: Empfehlungs-Karte
    acf_add_local_field( [
        'key'       => 'field_vd_hero_card_tab',
        'label'     => 'Empfehlungs-Karte',
        'name'      => 'hero_card_tab',
        'type'      => 'tab',
        'parent'    => 'group_vd_startseite',
        'placement' => 'top',
        'endpoint'  => 0,
        'menu_order' => 20,
    ] );

    acf_add_local_field( [
        'key'          => 'field_vd_hero_show_featured_card',
        'label'        => 'Empfehlungs-Karte anzeigen',
        'name'         => 'hero_show_featured_card',
        'type'         => 'true_false',
        'parent'       => 'group_vd_startseite',
        'instructions' => 'Karte rechts im Hero ein- oder ausblenden.',
        'required'     => 0,
        'default_value' => 1,
        'ui'           => 1,
        'ui_on_text'   => 'Ja',
        'ui_off_text'  => 'Nein',
        'menu_order'   => 21,
    ] );

    $card_condition = [ [ [ 'field' => 'field_vd_hero_show_featured_card', 'operator' => '==', 'value' => '1' ] ] ];

    acf_add_local_field( [
        'key'               => 'field_vd_hero_featured_image',
        'label'             => 'Bild der Karte',
        'name'              => 'hero_featured_image',
        'type'              => 'image',
        'parent'            => 'group_vd_startseite',
        'instructions'      => 'Bild für die Empfehlungs-Karte. Empfohlen: 3:2 Format.',
        'required'          => 0,
        'return_format'     => 'array',
        'preview_size'      => 'thumbnail',
        'library'           => 'all',
        'conditional_logic' => $card_condition,
        'menu_order'        => 22,
    ] );

    acf_add_local_field( [
        'key'               => 'field_vd_hero_featured_badge',
        'label'             => 'Badge-Text',
        'name'              => 'hero_featured_badge',
        'type'              => 'text',
        'parent'            => 'group_vd_startseite',
        'placeholder'       => 'Empfohlen',
        'required'          => 0,
        'wrapper'           => [ 'width' => '50', 'class' => '', 'id' => '' ],
        'conditional_logic' => $card_condition,
        'menu_order'        => 23,
    ] );

    acf_add_local_field( [
        'key'               => 'field_vd_hero_featured_title',
        'label'             => 'Titel der Karte',
        'name'              => 'hero_featured_title',
        'type'              => 'text',
        'parent'            => 'group_vd_startseite',
        'placeholder'       => 'z.B. Bun Bowl Special',
        'required'          => 0,
        'wrapper'           => [ 'width' => '50', 'class' => '', 'id' => '' ],
        'conditional_logic' => $card_condition,
        'menu_order'        => 24,
    ] );

    acf_add_local_field( [
        'key'               => 'field_vd_hero_featured_text',
        'label'             => 'Beschreibung',
        'name'              => 'hero_featured_text',
        'type'              => 'textarea',
        'parent'            => 'group_vd_startseite',
        'rows'              => 2,
        'new_lines'         => '',
        'required'          => 0,
        'conditional_logic' => $card_condition,
        'menu_order'        => 25,
    ] );

    acf_add_local_field( [
        'key'               => 'field_vd_hero_featured_price',
        'label'             => 'Preis',
        'name'              => 'hero_featured_price',
        'type'              => 'text',
        'parent'            => 'group_vd_startseite',
        'instructions'      => 'Nur die Zahl, ohne Währung – z.B. "26.50"',
        'placeholder'       => '26.50',
        'required'          => 0,
        'wrapper'           => [ 'width' => '30', 'class' => '', 'id' => '' ],
        'conditional_logic' => $card_condition,
        'menu_order'        => 26,
    ] );
} );


// ── Tages Menue Post Type: Felder ────────────────────────────────────────────
add_action( 'acf/init', function() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    acf_add_local_field_group( [
        'key'      => 'group_vd_tages_menue_details',
        'title'    => 'Tages Menue Details',
        'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'mittagsmenu' ] ] ],
        'position' => 'normal',
        'style'    => 'default',
        'active'   => true,
        'fields'   => [
            [
                'key'         => 'field_vd_tm_post_nummer',
                'label'       => 'Menü-Nr.',
                'name'        => 'menu_nummer',
                'type'        => 'text',
                'placeholder' => 'z.B. 12',
                'wrapper'     => [ 'width' => '20' ],
                'menu_order'  => 0,
            ],
            [
                'key'          => 'field_vd_tm_post_preis',
                'label'        => 'Preis (CHF)',
                'name'         => 'preis',
                'type'         => 'text',
                'instructions' => 'z.B. "18.50"',
                'placeholder'  => '18.50',
                'wrapper'      => [ 'width' => '30' ],
                'menu_order'   => 1,
            ],
            [
                'key'           => 'field_vd_tm_post_badge',
                'label'         => 'Hinweis',
                'name'          => 'badges',
                'type'          => 'checkbox',
                'choices'       => [
                    'beliebt'     => 'Beliebt',
                    'haus-hit'    => 'Haus-Hit',
                    'neu'         => 'Neu',
                    'vegetarisch' => 'Vegetarisch',
                    'scharf'      => 'Scharf',
                ],
                'default_value' => [],
                'return_format' => 'value',
                'layout'        => 'horizontal',
                'toggle'        => 0,
                'wrapper'       => [ 'width' => '50' ],
                'menu_order'    => 2,
            ],
            [
                'key'         => 'field_vd_tm_post_allergene',
                'label'       => 'Allergene',
                'name'        => 'allergene',
                'type'        => 'text',
                'placeholder' => 'z.B. Gluten, Soja, Erdnüsse',
                'wrapper'     => [ 'width' => '50' ],
                'menu_order'  => 3,
            ],
            [
                'key'           => 'field_vd_tm_post_reihenfolge',
                'label'         => 'Reihenfolge',
                'name'          => 'reihenfolge',
                'type'          => 'number',
                'default_value' => 10,
                'wrapper'       => [ 'width' => '25' ],
                'menu_order'    => 4,
            ],
        ],
    ] );
} );


// ── Empfehlung Options-Felder ─────────────────────────────────────────────────
add_action( 'acf/init', function() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    acf_add_local_field_group( [
        'key'      => 'group_vd_empfehlung',
        'title'    => 'Tagesempfehlung',
        'location' => [ [ [
            'param'    => 'options_page',
            'operator' => '==',
            'value'    => 'vietdura-empfehlung',
        ] ] ],
        'position'   => 'normal',
        'active'     => true,
        'fields'     => [
            [
                'key'           => 'field_vd_emp_aktiv',
                'label'         => 'Empfehlung anzeigen',
                'name'          => 'empfehlung_aktiv',
                'type'          => 'true_false',
                'instructions'  => 'EIN = Empfehlungs-Banner erscheint auf der Homepage. AUS = unsichtbar.',
                'default_value' => 0,
                'ui'            => 1,
                'ui_on_text'    => 'Ja – anzeigen',
                'ui_off_text'   => 'Nein – ausblenden',
                'menu_order'    => 0,
            ],
            [
                'key'               => 'field_vd_emp_badge',
                'label'             => 'Badge (z.B. "Heute", "Neu", "Saisonal")',
                'name'              => 'empfehlung_badge',
                'type'              => 'text',
                'placeholder'       => 'Heute empfohlen',
                'wrapper'           => [ 'width' => '40' ],
                'conditional_logic' => [ [ [ 'field' => 'field_vd_emp_aktiv', 'operator' => '==', 'value' => '1' ] ] ],
                'menu_order'        => 1,
            ],
            [
                'key'               => 'field_vd_emp_titel',
                'label'             => 'Titel / Gerichtname',
                'name'              => 'empfehlung_titel',
                'type'              => 'text',
                'placeholder'       => 'z.B. Bun Bowl Special',
                'required'          => 1,
                'wrapper'           => [ 'width' => '60' ],
                'conditional_logic' => [ [ [ 'field' => 'field_vd_emp_aktiv', 'operator' => '==', 'value' => '1' ] ] ],
                'menu_order'        => 2,
            ],
            [
                'key'               => 'field_vd_emp_text',
                'label'             => 'Kurzbeschreibung',
                'name'              => 'empfehlung_text',
                'type'              => 'textarea',
                'rows'              => 2,
                'new_lines'         => '',
                'conditional_logic' => [ [ [ 'field' => 'field_vd_emp_aktiv', 'operator' => '==', 'value' => '1' ] ] ],
                'menu_order'        => 3,
            ],
            [
                'key'               => 'field_vd_emp_preis',
                'label'             => 'Preis (nur Zahl, z.B. 26.50)',
                'name'              => 'empfehlung_preis',
                'type'              => 'text',
                'placeholder'       => '26.50',
                'wrapper'           => [ 'width' => '30' ],
                'conditional_logic' => [ [ [ 'field' => 'field_vd_emp_aktiv', 'operator' => '==', 'value' => '1' ] ] ],
                'menu_order'        => 4,
            ],
            [
                'key'               => 'field_vd_emp_bild',
                'label'             => 'Bild',
                'name'              => 'empfehlung_bild',
                'type'              => 'image',
                'return_format'     => 'array',
                'preview_size'      => 'medium',
                'instructions'      => 'Empfohlen: 3:2 Format, min. 600×400px.',
                'conditional_logic' => [ [ [ 'field' => 'field_vd_emp_aktiv', 'operator' => '==', 'value' => '1' ] ] ],
                'menu_order'        => 5,
            ],
        ],
    ] );
} );


// ── Tagesmenu Options-Felder ──────────────────────────────────────────────────
add_action( 'acf/init', function() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    acf_add_local_field_group( [
        'key'      => 'group_vd_tagesmenu',
        'title'    => 'Tagesmenu',
        'location' => [ [ [
            'param'    => 'options_page',
            'operator' => '==',
            'value'    => 'vietdura-tagesmenu',
        ] ] ],
        'position' => 'normal',
        'active'   => true,
        'fields'   => [

            // ── Einstellungen ─────────────────────────────────────────────
            [
                'key'           => 'field_vd_tm_aktiv',
                'label'         => 'Tagesmenu anzeigen',
                'name'          => 'tagesmenu_aktiv',
                'type'          => 'true_false',
                'instructions'  => 'EIN = Tagesmenu erscheint auf der Homepage und der Mittagsmenu-Seite.',
                'default_value' => 0,
                'ui'            => 1,
                'ui_on_text'    => 'Ja – anzeigen',
                'ui_off_text'   => 'Nein – ausblenden',
                'menu_order'    => 0,
            ],
            [
                'key'               => 'field_vd_tm_datum',
                'label'             => 'Datum / Bezeichnung',
                'name'              => 'tagesmenu_datum',
                'type'              => 'text',
                'instructions'      => 'z.B. "Heute, 15. März" oder "Diese Woche"',
                'placeholder'       => 'Heute',
                'wrapper'           => [ 'width' => '50' ],
                'conditional_logic' => [ [ [ 'field' => 'field_vd_tm_aktiv', 'operator' => '==', 'value' => '1' ] ] ],
                'menu_order'        => 1,
            ],
            [
                'key'               => 'field_vd_tm_intro',
                'label'             => 'Einleitungstext',
                'name'              => 'tagesmenu_intro',
                'type'              => 'text',
                'placeholder'       => 'Frisch gekocht – täglich wechselndes Mittagsmenu',
                'wrapper'           => [ 'width' => '50' ],
                'conditional_logic' => [ [ [ 'field' => 'field_vd_tm_aktiv', 'operator' => '==', 'value' => '1' ] ] ],
                'menu_order'        => 2,
            ],
            [
                'key'               => 'field_vd_tm_preis_info',
                'label'             => 'Preis-Info',
                'name'              => 'tagesmenu_preis_info',
                'type'              => 'text',
                'instructions'      => 'Wird unter dem Menu angezeigt.',
                'placeholder'       => 'Inkl. Suppe oder Dessert · CHF 18.50 / 21.50',
                'conditional_logic' => [ [ [ 'field' => 'field_vd_tm_aktiv', 'operator' => '==', 'value' => '1' ] ] ],
                'menu_order'        => 3,
            ],

            // ── Zusätzliche Positionen (nicht aus Speisekarte) ────────────
            [
                'key'               => 'field_vd_tm_extras',
                'label'             => 'Zusätzliche Menu-Positionen',
                'name'              => 'tagesmenu_extras',
                'type'              => 'repeater',
                'instructions'      => 'Hier kannst du Gerichte erfassen die NICHT in der Speisekarte sind.',
                'min'               => 0,
                'max'               => 10,
                'layout'            => 'block',
                'button_label'      => '+ Position hinzufügen',
                'conditional_logic' => [ [ [ 'field' => 'field_vd_tm_aktiv', 'operator' => '==', 'value' => '1' ] ] ],
                'menu_order'        => 4,
                'sub_fields'        => [
                    [
                        'key'         => 'field_vd_tm_extra_badge',
                        'label'       => 'Badge (z.B. "Menu 1", "Suppe")',
                        'name'        => 'badge',
                        'type'        => 'text',
                        'placeholder' => 'Menu 1',
                        'wrapper'     => [ 'width' => '30' ],
                    ],
                    [
                        'key'         => 'field_vd_tm_extra_titel',
                        'label'       => 'Gericht',
                        'name'        => 'titel',
                        'type'        => 'text',
                        'placeholder' => 'z.B. Pho Bo mit Rindfleisch',
                        'required'    => 1,
                        'wrapper'     => [ 'width' => '70' ],
                    ],
                    [
                        'key'         => 'field_vd_tm_extra_beschreibung',
                        'label'       => 'Beschreibung',
                        'name'        => 'beschreibung',
                        'type'        => 'text',
                        'placeholder' => 'Kurze Beschreibung (optional)',
                        'wrapper'     => [ 'width' => '70' ],
                    ],
                    [
                        'key'         => 'field_vd_tm_extra_preis',
                        'label'       => 'Preis (CHF)',
                        'name'        => 'preis',
                        'type'        => 'text',
                        'placeholder' => '18.50',
                        'wrapper'     => [ 'width' => '30' ],
                    ],
                ],
            ],
        ],
    ] );
} );


// ── Kontakt & Öffnungszeiten Options-Felder ───────────────────────────────────
add_action( 'acf/init', function() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    acf_add_local_field_group( [
        'key'      => 'group_vd_kontakt',
        'title'    => 'Kontakt & Öffnungszeiten',
        'location' => [ [ [
            'param'    => 'options_page',
            'operator' => '==',
            'value'    => 'vietdura-kontakt',
        ] ] ],
        'position'   => 'normal',
        'style'      => 'default',
        'active'     => true,
        'fields'     => [

            // ── Adresse ──────────────────────────────────────────────────────
            [
                'key'        => 'field_vd_k_tab_adresse',
                'label'      => 'Adresse & Kontakt',
                'name'       => 'k_tab_adresse',
                'type'       => 'tab',
                'placement'  => 'top',
                'menu_order' => 0,
            ],
            [
                'key'         => 'field_vd_k_strasse',
                'label'       => 'Strasse & Hausnummer',
                'name'        => 'adresse',
                'type'        => 'text',
                'placeholder' => 'Zürcherstrasse 48',
                'wrapper'     => [ 'width' => '50' ],
                'menu_order'  => 1,
            ],
            [
                'key'         => 'field_vd_k_plz_ort',
                'label'       => 'PLZ & Ort',
                'name'        => 'plz_ort',
                'type'        => 'text',
                'placeholder' => '8317 Tagelswangen ZH',
                'wrapper'     => [ 'width' => '50' ],
                'menu_order'  => 2,
            ],
            [
                'key'         => 'field_vd_k_telefon',
                'label'       => 'Telefon (Anzeige)',
                'name'        => 'telefon',
                'type'        => 'text',
                'placeholder' => '+41 44 940 99 99',
                'wrapper'     => [ 'width' => '50' ],
                'menu_order'  => 3,
            ],
            [
                'key'          => 'field_vd_k_telefon_href',
                'label'        => 'Telefon (Link, nur Zahlen)',
                'name'         => 'telefon_href',
                'type'         => 'text',
                'instructions' => 'Nur Zahlen ohne Leerzeichen, z.B. +41449409999',
                'placeholder'  => '+41449409999',
                'wrapper'      => [ 'width' => '50' ],
                'menu_order'   => 4,
            ],
            [
                'key'         => 'field_vd_k_whatsapp',
                'label'       => 'WhatsApp Link',
                'name'        => 'whatsapp_url',
                'type'        => 'text',
                'placeholder' => 'https://wa.me/41449409999',
                'wrapper'     => [ 'width' => '50' ],
                'menu_order'  => 5,
            ],
            [
                'key'         => 'field_vd_k_email',
                'label'       => 'E-Mail allgemein',
                'name'        => 'email',
                'type'        => 'email',
                'placeholder' => 'info@vietdura.ch',
                'wrapper'     => [ 'width' => '50' ],
                'menu_order'  => 6,
            ],
            [
                'key'         => 'field_vd_k_email_catering',
                'label'       => 'E-Mail Catering',
                'name'        => 'email_catering',
                'type'        => 'email',
                'placeholder' => 'rh@vietdura.ch',
                'wrapper'     => [ 'width' => '50' ],
                'menu_order'  => 7,
            ],
            [
                'key'         => 'field_vd_k_reservierung_url',
                'label'       => 'Reservierungs-URL / Tel-Link',
                'name'        => 'reservierung_url',
                'type'        => 'text',
                'placeholder' => 'tel:+41449409999',
                'wrapper'     => [ 'width' => '50' ],
                'menu_order'  => 8,
            ],
            [
                'key'         => 'field_vd_k_maps_url',
                'label'       => 'Google Maps URL',
                'name'        => 'maps_url',
                'type'        => 'url',
                'placeholder' => 'https://maps.google.com/?q=Zürcherstrasse+48+Tagelswangen',
                'wrapper'     => [ 'width' => '100' ],
                'menu_order'  => 9,
            ],
            [
                'key'          => 'field_vd_k_parkplatz',
                'label'        => 'Parkplatz-Info',
                'name'         => 'parkplatz_info',
                'type'         => 'text',
                'placeholder'  => 'Kostenlose Parkplätze gegenüber (signalisiert)',
                'wrapper'      => [ 'width' => '100' ],
                'menu_order'   => 10,
            ],

            // ── Öffnungszeiten ────────────────────────────────────────────────
            // Felder pro Tag: Mittag Von/Bis + Abend Von/Bis. Leer = geschlossen.
            // Ruhetag = alle 4 Felder leer lassen.
            [
                'key'        => 'field_vd_k_tab_oeffnung',
                'label'      => 'Öffnungszeiten',
                'name'       => 'k_tab_oeffnung',
                'type'       => 'tab',
                'placement'  => 'top',
                'menu_order' => 20,
            ],
            // Montag
            [ 'key' => 'field_vd_oz_mo_mi_von', 'label' => 'Montag Mittag von',  'name' => 'oz_mo_mi_von', 'type' => 'text', 'placeholder' => '11:00', 'instructions' => 'Leer = kein Mittagsservice', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 21 ],
            [ 'key' => 'field_vd_oz_mo_mi_bis', 'label' => 'Montag Mittag bis',  'name' => 'oz_mo_mi_bis', 'type' => 'text', 'placeholder' => '14:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 22 ],
            [ 'key' => 'field_vd_oz_mo_ab_von', 'label' => 'Montag Abend von',   'name' => 'oz_mo_ab_von', 'type' => 'text', 'placeholder' => '17:00', 'instructions' => 'Leer = kein Abendservice / Ruhetag', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 23 ],
            [ 'key' => 'field_vd_oz_mo_ab_bis', 'label' => 'Montag Abend bis',   'name' => 'oz_mo_ab_bis', 'type' => 'text', 'placeholder' => '22:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 24 ],
            // Dienstag
            [ 'key' => 'field_vd_oz_di_mi_von', 'label' => 'Dienstag Mittag von', 'name' => 'oz_di_mi_von', 'type' => 'text', 'placeholder' => '11:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 25 ],
            [ 'key' => 'field_vd_oz_di_mi_bis', 'label' => 'Dienstag Mittag bis', 'name' => 'oz_di_mi_bis', 'type' => 'text', 'placeholder' => '14:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 26 ],
            [ 'key' => 'field_vd_oz_di_ab_von', 'label' => 'Dienstag Abend von',  'name' => 'oz_di_ab_von', 'type' => 'text', 'placeholder' => '17:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 27 ],
            [ 'key' => 'field_vd_oz_di_ab_bis', 'label' => 'Dienstag Abend bis',  'name' => 'oz_di_ab_bis', 'type' => 'text', 'placeholder' => '22:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 28 ],
            // Mittwoch
            [ 'key' => 'field_vd_oz_mi_mi_von', 'label' => 'Mittwoch Mittag von', 'name' => 'oz_mi_mi_von', 'type' => 'text', 'placeholder' => '11:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 29 ],
            [ 'key' => 'field_vd_oz_mi_mi_bis', 'label' => 'Mittwoch Mittag bis', 'name' => 'oz_mi_mi_bis', 'type' => 'text', 'placeholder' => '14:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 30 ],
            [ 'key' => 'field_vd_oz_mi_ab_von', 'label' => 'Mittwoch Abend von',  'name' => 'oz_mi_ab_von', 'type' => 'text', 'placeholder' => '17:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 31 ],
            [ 'key' => 'field_vd_oz_mi_ab_bis', 'label' => 'Mittwoch Abend bis',  'name' => 'oz_mi_ab_bis', 'type' => 'text', 'placeholder' => '22:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 32 ],
            // Donnerstag
            [ 'key' => 'field_vd_oz_do_mi_von', 'label' => 'Donnerstag Mittag von', 'name' => 'oz_do_mi_von', 'type' => 'text', 'placeholder' => '11:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 33 ],
            [ 'key' => 'field_vd_oz_do_mi_bis', 'label' => 'Donnerstag Mittag bis', 'name' => 'oz_do_mi_bis', 'type' => 'text', 'placeholder' => '14:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 34 ],
            [ 'key' => 'field_vd_oz_do_ab_von', 'label' => 'Donnerstag Abend von',  'name' => 'oz_do_ab_von', 'type' => 'text', 'placeholder' => '17:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 35 ],
            [ 'key' => 'field_vd_oz_do_ab_bis', 'label' => 'Donnerstag Abend bis',  'name' => 'oz_do_ab_bis', 'type' => 'text', 'placeholder' => '22:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 36 ],
            // Freitag
            [ 'key' => 'field_vd_oz_fr_mi_von', 'label' => 'Freitag Mittag von', 'name' => 'oz_fr_mi_von', 'type' => 'text', 'placeholder' => '11:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 37 ],
            [ 'key' => 'field_vd_oz_fr_mi_bis', 'label' => 'Freitag Mittag bis', 'name' => 'oz_fr_mi_bis', 'type' => 'text', 'placeholder' => '14:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 38 ],
            [ 'key' => 'field_vd_oz_fr_ab_von', 'label' => 'Freitag Abend von',  'name' => 'oz_fr_ab_von', 'type' => 'text', 'placeholder' => '17:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 39 ],
            [ 'key' => 'field_vd_oz_fr_ab_bis', 'label' => 'Freitag Abend bis',  'name' => 'oz_fr_ab_bis', 'type' => 'text', 'placeholder' => '22:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 40 ],
            // Samstag
            [ 'key' => 'field_vd_oz_sa_mi_von', 'label' => 'Samstag Mittag von', 'name' => 'oz_sa_mi_von', 'type' => 'text', 'placeholder' => '', 'instructions' => 'Leer = kein Mittagsservice', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 41 ],
            [ 'key' => 'field_vd_oz_sa_mi_bis', 'label' => 'Samstag Mittag bis', 'name' => 'oz_sa_mi_bis', 'type' => 'text', 'placeholder' => '', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 42 ],
            [ 'key' => 'field_vd_oz_sa_ab_von', 'label' => 'Samstag Abend von',  'name' => 'oz_sa_ab_von', 'type' => 'text', 'placeholder' => '17:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 43 ],
            [ 'key' => 'field_vd_oz_sa_ab_bis', 'label' => 'Samstag Abend bis',  'name' => 'oz_sa_ab_bis', 'type' => 'text', 'placeholder' => '22:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 44 ],
            // Sonntag
            [ 'key' => 'field_vd_oz_so_mi_von', 'label' => 'Sonntag Mittag von', 'name' => 'oz_so_mi_von', 'type' => 'text', 'placeholder' => '', 'instructions' => 'Leer = kein Mittagsservice', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 45 ],
            [ 'key' => 'field_vd_oz_so_mi_bis', 'label' => 'Sonntag Mittag bis', 'name' => 'oz_so_mi_bis', 'type' => 'text', 'placeholder' => '', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 46 ],
            [ 'key' => 'field_vd_oz_so_ab_von', 'label' => 'Sonntag Abend von',  'name' => 'oz_so_ab_von', 'type' => 'text', 'placeholder' => '17:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 47 ],
            [ 'key' => 'field_vd_oz_so_ab_bis', 'label' => 'Sonntag Abend bis',  'name' => 'oz_so_ab_bis', 'type' => 'text', 'placeholder' => '22:00', 'wrapper' => [ 'width' => '25' ], 'menu_order' => 48 ],
            [
                'key'          => 'field_vd_k_oeffnung_hinweis',
                'label'        => 'Hinweis Öffnungszeiten',
                'name'         => 'oeffnung_hinweis',
                'type'         => 'text',
                'instructions' => 'Wird unter der Öffnungszeiten-Tabelle angezeigt',
                'placeholder'  => 'Anrufe & WhatsApp nur während Öffnungszeiten',
                'wrapper'      => [ 'width' => '100' ],
                'menu_order'   => 49,
            ],

            // ── Maps Embed ────────────────────────────────────────────────────
            [
                'key'        => 'field_vd_k_tab_map',
                'label'      => 'Google Maps Embed',
                'name'       => 'k_tab_map',
                'type'       => 'tab',
                'placement'  => 'top',
                'menu_order' => 30,
            ],
            [
                'key'          => 'field_vd_k_maps_embed',
                'label'        => 'Google Maps Embed-Code',
                'name'         => 'maps_embed',
                'type'         => 'textarea',
                'instructions' => 'Den iframe-Code von Google Maps hier einfügen (Google Maps → Teilen → Karte einbetten → HTML kopieren)',
                'rows'         => 4,
                'menu_order'   => 31,
            ],
        ],
    ] );
} );


// ── Seiten: Hintergrundbild für Hero ─────────────────────────────────────────
add_action( 'acf/init', function() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    acf_add_local_field_group( [
        'key'      => 'group_vd_page_hero',
        'title'    => 'Hero-Hintergrundbild',
        'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ] ] ],
        'position' => 'side',
        'style'    => 'default',
        'active'   => true,
        'fields'   => [],
    ] );

    acf_add_local_field( [
        'key'           => 'field_vd_page_hero_bg_image',
        'label'         => 'Hintergrundbild',
        'name'          => 'page_hero_bg_image',
        'type'          => 'image',
        'parent'        => 'group_vd_page_hero',
        'instructions'  => 'Hero-Hintergrund. Empfohlen: min. 1920×800px.',
        'required'      => 0,
        'return_format' => 'array',
        'preview_size'  => 'medium',
        'library'       => 'all',
        'menu_order'    => 1,
    ] );

    acf_add_local_field( [
        'key'           => 'field_vd_page_hero_bg_opacity',
        'label'         => 'Overlay-Deckkraft',
        'name'          => 'page_hero_bg_opacity',
        'type'          => 'select',
        'parent'        => 'group_vd_page_hero',
        'instructions'  => 'Dunkles Overlay über dem Bild.',
        'required'      => 0,
        'choices'       => [
            '0.35' => 'Leicht',
            '0.45' => 'Mittel-leicht',
            '0.55' => 'Mittel',
            '0.65' => 'Mittel-dunkel',
            '0.75' => 'Dunkel',
        ],
        'default_value' => '0.55',
        'return_format' => 'value',
        'menu_order'    => 2,
    ] );
} );
