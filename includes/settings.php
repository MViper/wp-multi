<?php
// Einstellungen des Plugins registrieren
function statistik_manager_register_settings() {
    // Optionen für das Plugin registrieren
    register_setting('statistik_manager_settings_group', 'statistik_manager_show_posts');
    register_setting('statistik_manager_settings_group', 'statistik_manager_show_comments');
    register_setting('statistik_manager_settings_group', 'statistik_manager_show_categories');
    register_setting('statistik_manager_settings_group', 'statistik_manager_show_series');
}

add_action('admin_init', 'statistik_manager_register_settings');
