<?php
/**
 * Einmalig: Setzt die optimalen SEO-Einstellungen für "File Renaming on Upload".
 * Diese Datei kann nach dem ersten Seitenaufruf gelöscht werden.
 */

add_action('init', function () {
    // Nur einmal ausführen
    if (get_option('_frou_settings_applied')) {
        return;
    }

    // Plugin aktivieren
    update_option('frou_enable_plugin', 'yes');

    // Filename Structure: nur Post-Titel (saubere, SEO-freundliche Namen)
    update_option('frou_filename_structure', '{posttitle}');

    // Bindestrich als Trennzeichen (SEO-Standard)
    update_option('frou_filename_structure_chars_between', '-');

    // Max. Dateinamenlänge: 80 Zeichen
    update_option('frou_filename_structure_max_length', '80');

    // Markieren, dass Einstellungen gesetzt wurden
    update_option('_frou_settings_applied', true);

    error_log('FROU SEO settings applied successfully.');
});
