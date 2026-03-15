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
