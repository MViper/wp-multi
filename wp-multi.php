<?php
/**
 * Plugin Name: WP Multi
 * Plugin URI: https://git.viper.ipv64.net/M_Viper/wp-multi
 * Description: Erweiterter Anti-Spam-Schutz mit Honeypot, Keyword-Filter, Link-Limit und mehr. Jetzt mit Statistik im Dashboard und HappyForms-Integration.
 * Version: 2.4
 * Author: M_Viper
 * Author URI: https://m-viper.de
 * Requires at least: 6.7.2
 * Tested up to: 6.7.2
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-multi
 * Tags: anti-spam, security, honeypot, comment-protection, statistics, happyforms
 */

if (!defined('ABSPATH')) exit;



/*
* Index Verzeichnis [alphabetical_index]
*/


// Shortcode zum Erstellen des Indexes
function wp_multi_alphabetical_index($atts) {
    // Definiere die Argumente für den Shortcode
    $atts = shortcode_atts(array(
        'posts_per_page' => 20, // Maximale Beiträge pro Seite
    ), $atts, 'alphabetical_index');

    // Hole alle Beiträge
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    );

    $posts = get_posts($args);

    // Beiträge nach Anfangsbuchstaben gruppieren
    $alphabet = range('A', 'Z');
    $posts_by_letter = array();

    foreach ($posts as $post) {
        $first_letter = strtoupper(substr($post->post_title, 0, 1));
        if (in_array($first_letter, $alphabet)) {
            $posts_by_letter[$first_letter][] = $post;
        }
    }

    // Holen des aktuellen Buchstabens aus der URL
    $letter = isset($_GET['letter']) ? strtoupper($_GET['letter']) : ''; // Der Buchstabe aus der URL

    // Bestimme, welche Beiträge angezeigt werden
    $posts_in_letter = [];
    if ($letter && isset($posts_by_letter[$letter])) {
        $posts_in_letter = $posts_by_letter[$letter];
    }

    // Teile die Beiträge in zwei Hälften für die Boxen
    $halfway = ceil(count($posts_in_letter) / 2); // Rundet die Hälfte auf
    $first_half = array_slice($posts_in_letter, 0, $halfway); // Erste Hälfte der Beiträge
    $second_half = array_slice($posts_in_letter, $halfway); // Zweite Hälfte der Beiträge

    // Ausgabe
    ob_start();
    ?>

    <div class="alphabetical-index">
        <!-- Links zu den Buchstaben -->
        <div class="alphabet-links">
            <?php foreach ($alphabet as $char): ?>
                <a href="?letter=<?php echo $char; ?>" class="letter-link"><?php echo $char; ?></a>
            <?php endforeach; ?>
        </div>

        <?php if ($letter): ?>
            <!-- Box für den aktuellen Buchstaben -->
            <div class="letter-heading-box">
                <h2>Beiträge für: <?php echo $letter; ?></h2>
            </div>

            <!-- Zeige die Beiträge für den ausgewählten Buchstaben in zwei Boxen -->
            <div class="letter-pair-container">
                <div class="letter-box">
                    <ul class="post-list">
                        <?php foreach ($first_half as $post): ?>
                            <li><a href="<?php echo get_permalink($post->ID); ?>"><?php echo $post->post_title; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="letter-box">
                    <ul class="post-list">
                        <?php foreach ($second_half as $post): ?>
                            <li><a href="<?php echo get_permalink($post->ID); ?>"><?php echo $post->post_title; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <style>
    .alphabetical-index {
        font-family: Arial, sans-serif;
        margin: 20px;
    }

    .alphabet-links {
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
    }

    .alphabet-links a {
        margin-right: 10px;
        font-size: 18px;
        text-decoration: none;
        color: #0073aa;
    }

    .alphabet-links a:hover {
        text-decoration: underline;
    }

    .letter-heading-box {
        margin-bottom: 20px;
        background-color: #f0f0f0;
        padding: 20px;
        text-align: center;
        border-radius: 8px;
    }

    .letter-heading-box h2 {
        font-size: 24px;
        margin: 0;
    }

    .letter-pair-container {
        display: flex;
        gap: 30px;
        justify-content: space-between;
    }

    .letter-box {
        width: 48%;
        background-color: #f0f0f0;
        padding: 20px;
        border-radius: 8px;
    }

    .letter-box h2 {
        font-size: 24px;
        margin-bottom: 10px;
    }

    .post-list {
        list-style-type: none;
        padding: 0;
    }

    .post-list li {
        margin-bottom: 5px;
    }

    .post-list a {
        text-decoration: none;
        color: #333;
    }

    .post-list a:hover {
        text-decoration: underline;
    }
    </style>

    <?php
    return ob_get_clean();
}

// Shortcode registrieren
add_shortcode('alphabetical_index', 'wp_multi_alphabetical_index');


/*
* Sperre Trash Mail Adressen
*/


// Funktion zum Laden der Liste von Einweg-Mail-Anbietern
function load_disposable_email_list() {
    $file_path = plugin_dir_path(__FILE__) . 'includes/disposable_email_blocklist.conf'; // Pfad zur Datei im includes-Ordner
    if (file_exists($file_path)) {
        return file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
    return [];
}

// Funktion zum Überprüfen der E-Mail-Adresse eines Kommentators
function check_disposable_email($commentdata) {
    $disposable_list = load_disposable_email_list();
    $email = $commentdata['comment_author_email'];
    $domain = substr(strrchr($email, "@"), 1); // Nur die Domain extrahieren

    // Überprüfen, ob die Domain auf der Liste steht
    if (in_array($domain, $disposable_list)) {
        wp_die(__('Fehler: Trash-Mail-Adressen sind in Kommentaren nicht erlaubt.'));
    }

    return $commentdata;
}

// Die Funktion wird beim Absenden eines Kommentars ausgeführt
add_filter('preprocess_comment', 'check_disposable_email');

 
/*
* Text Copy Schutz und Schutz vor Entwicklertools
*/


// JavaScript für die Kopierschutz-Funktion einbinden
function wp_multi_enqueue_scripts() {
    wp_add_inline_script('jquery', "
        jQuery(document).ready(function($) {
            // Verhindert das Öffnen der Entwicklertools mit F12, Strg+Shift+I und Strg+Shift+C
            $(document).keydown(function(e) {
                // Blockiert F12, Strg + Shift + I, Strg + Shift + C (Entwicklertools)
                if (e.keyCode == 123 || (e.ctrlKey && e.shiftKey && e.keyCode == 73) || (e.ctrlKey && e.shiftKey && e.keyCode == 67)) {
                    e.preventDefault();
                }

                // Verhindert das Öffnen des Quellcodes mit Strg + U (view-source)
                if ((e.ctrlKey && e.keyCode == 85) || (e.ctrlKey && e.shiftKey && e.keyCode == 85)) {
                    e.preventDefault();
                }

                // Verhindert den Zugriff auf die Konsole mit Strg + Shift + J (Konsole-Tab)
                if ((e.ctrlKey && e.shiftKey && e.keyCode == 74) || (e.metaKey && e.altKey && e.keyCode == 74)) {
                    e.preventDefault();
                }

                // Verhindert das Öffnen des Quellcodes mit view-source
                if (e.ctrlKey && e.keyCode == 85) {
                    e.preventDefault();
                }
            });

            // Verhindert das Öffnen des Kontextmenüs (Rechtsklick)
            $('body').on('contextmenu', function(e) {
                e.preventDefault();
            });

            // Kopierschutz-Funktion
            $('body').on('copy', function(e) {
                e.preventDefault();
                var selectedText = window.getSelection().toString();
                var numericText = selectedText.replace(/./g, function(char) {
                    return Math.floor(Math.random() * 10);
                });
                
                e.originalEvent.clipboardData.setData('text/plain', numericText);
            });
        });
    ");
}
add_action('wp_enqueue_scripts', 'wp_multi_enqueue_scripts');


/*
* Login deaktivieren
*/


// Checkbox zum Benutzerprofil hinzufügen
function wp_multi_add_disable_login_checkbox($user) {
    ?>
    <h3>Login deaktivieren</h3>
    <table class="form-table">
        <tr>
            <th><label for="disable_login">Login deaktivieren</label></th>
            <td>
                <input type="checkbox" name="disable_login" id="disable_login" value="1" <?php checked( get_user_meta($user->ID, 'disable_login', true), 1 ); ?> />
                <span class="description">Markiere diese Option, um den Login des Benutzers zu deaktivieren.</span>
            </td>
        </tr>
    </table>
    <?php
}

// Speichern der Checkbox-Option
function wp_multi_save_disable_login_checkbox($user_id) {
    if ( isset( $_POST['disable_login'] ) ) {
        update_user_meta( $user_id, 'disable_login', 1 );
    } else {
        delete_user_meta( $user_id, 'disable_login' );
    }
}

// Die Checkbox in das Benutzerprofil einfügen
add_action( 'show_user_profile', 'wp_multi_add_disable_login_checkbox' );
add_action( 'edit_user_profile', 'wp_multi_add_disable_login_checkbox' );

// Speichern der Checkbox-Option
add_action( 'personal_options_update', 'wp_multi_save_disable_login_checkbox' );
add_action( 'edit_user_profile_update', 'wp_multi_save_disable_login_checkbox' );

// Login blockieren, wenn die Checkbox aktiviert ist
function wp_multi_block_login_if_disabled($user_login, $user) {
    // Prüfen, ob der Benutzer das Flag "Login deaktivieren" gesetzt hat
    if ( get_user_meta( $user->ID, 'disable_login', true ) ) {
        // Fehlermeldung anzeigen, wenn der Login deaktiviert ist
        wp_die( 'Dein Login wurde deaktiviert. Bitte kontaktiere den Administrator.' );
    }
}

// Der Filter wird bei jedem Login-Versuch angewendet
add_action( 'wp_login', 'wp_multi_block_login_if_disabled', 10, 2 );
 
 
/*
* Auto Tag
*/


// Automatische Tags zu Beiträgen hinzufügen
function wp_multi_auto_add_tags($post_id) {
    if (get_post_type($post_id) !== 'post' || wp_is_post_revision($post_id)) return;

    $existing_tags = wp_get_post_tags($post_id, ['fields' => 'names']);
    if (!empty($existing_tags)) return;

    $post = get_post($post_id);
    $content = strip_tags($post->post_content);
    $content = strtolower($content);

    // Stopwörter aus der Admin-Eingabe holen
    $custom_stopwords = get_option('wp_multi_custom_stopwords', '');
    $custom_stopwords = array_map('trim', explode(',', $custom_stopwords)); // In ein Array umwandeln

    // Standard-Stopwörter
    $default_stopwords = ['und', 'oder', 'ein', 'eine', 'der', 'die', 'das', 'in', 'mit', 'auf', 'zu', 'von', 
                          'für', 'ist', 'es', 'im', 'an', 'am', 'bei', 'auch', 'aber', 'so', 'dass', 'kann', 
                          'wenn', 'wie', 'wir', 'man', 'nur', 'nicht', 'mehr', 'als', 'sein', 'wurde', 'werden', 
                          'hat', 'haben', 'schon', 'doch', 'denn', 'diese', 'dieser', 'dieses', 'nach', 'sehr', 'Allgemein'];

    // Alle Stopwörter (standard und benutzerdefiniert)
    $stopwords = array_merge($default_stopwords, $custom_stopwords);

    preg_match_all('/\b[a-zäöüß]{4,}\b/u', $content, $matches);
    $words = array_unique(array_diff($matches[0], $stopwords));

    $word_counts = array_count_values($words);
    arsort($word_counts);

    $top_tags = array_slice(array_keys($word_counts), 0, 5);
    if (!empty($top_tags)) {
        wp_set_post_tags($post_id, implode(',', $top_tags), true);
    }
}

// Menüeintrag für Automatische Tags
function wp_multi_admin_menu() {
    add_submenu_page(
        'edit.php',
        'Automatische Tags',
        'Automatische Tags',
        'manage_options',
        'wp-multi-auto-tags',
        'wp_multi_auto_tags_page'
    );
}
add_action('admin_menu', 'wp_multi_admin_menu');

// Menüseite mit Banner & schöner Progress Bar
function wp_multi_auto_tags_page() {
    ?>
    <div class="wrap">
        <!-- Blauer Header mit Logo -->
        <div class="wp-multi-header">
            <img src="https://m-viper.de/img/logo.png" alt="M_Viper Logo">
            <h1>Automatische Tags</h1>
        </div>

        <p class="wp-multi-description">Diese Funktion fügt automatisch Tag zu Beiträgen hinzu, die noch keine haben.</p>

        <form method="post" action="options.php">
            <?php
            settings_fields('wp_multi_auto_tags_options');
            do_settings_sections('wp-multi-auto-tags');
            ?>
            <p>
                <label for="wp_multi_custom_stopwords">Benutzerdefinierte Tags die nicht genutzt werden sollten (kommagetrennt):</label><br>
                <textarea id="wp_multi_custom_stopwords" name="wp_multi_custom_stopwords" rows="5" cols="50"><?php echo esc_textarea(get_option('wp_multi_custom_stopwords', '')); ?></textarea>
                <br>
                <small>Trenne die Wörter durch Kommas, z. B. "wird, auch, aber".</small>
            </p>
            <p><input type="submit" value="Speichern" class="button button-primary"></p>
        </form>

        <button id="start-auto-tags" class="button button-primary wp-multi-btn">Jetzt ausführen</button>

        <div id="progress-container" class="wp-multi-progress-container">
            <div id="progress-bar" class="wp-multi-progress-bar">0%</div>
        </div>

        <p id="status-message" class="wp-multi-status-message"></p>
    </div>

    <style>
    /* Header */
    .wp-multi-header {
        background: #0073aa;
        color: white;
        text-align: center;
        padding: 25px;
        border-radius: 8px;
        margin-bottom: 30px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .wp-multi-header img {
        max-width: 120px;
        margin-bottom: 15px;
    }
    .wp-multi-header h1 {
        font-size: 24px;
        font-weight: 600;
        margin: 0;
    }

    /* Beschreibung */
    .wp-multi-description {
        font-size: 18px;
        margin-bottom: 25px;
        color: #555;
    }

    /* Button */
    .wp-multi-btn {
        background: #0073aa;
        color: white;
        border: none;
        padding: 12px 24px;
        font-size: 18px;
        cursor: pointer;
        border-radius: 5px;
        transition: background 0.3s ease, transform 0.3s ease;
    }
    .wp-multi-btn:hover {
        background: #005f8a;
        transform: translateY(-2px);
    }
    .wp-multi-btn:disabled {
        background: #cccccc;
        cursor: not-allowed;
        transform: none;
    }

    /* Stopwort Textarea */
    textarea {
        width: 100%;
        padding: 12px;
        border-radius: 5px;
        border: 1px solid #ddd;
        font-size: 16px;
        line-height: 1.5;
        box-sizing: border-box;
    }
    label {
        font-size: 16px;
        font-weight: 500;
        color: #333;
        margin-bottom: 8px;
        display: block;
    }
    small {
        font-size: 14px;
        color: #888;
    }

    /* Fortschrittsbalken */
    .wp-multi-progress-container {
        display: none;
        width: 100%;
        background: #f4f4f4;
        border-radius: 5px;
        margin-top: 20px;
    }
    .wp-multi-progress-bar {
        width: 0%;
        height: 30px;
        background:rgb(45, 168, 7);
        text-align: center;
        color: white;
        line-height: 30px;
        font-weight: bold;
        transition: width 0.4s ease-in-out;
        border-radius: 5px;
    }

    /* Status Nachricht */
    .wp-multi-status-message {
        margin-top: 15px;
        font-size: 16px;
        font-weight: bold;
        color: #0073aa;
    }

    /* Formularbereich */
    form {
        margin-bottom: 25px;
        background: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }
</style>


    <script>
        document.getElementById("start-auto-tags").addEventListener("click", function() {
            let button = this;
            button.disabled = true;
            button.innerText = "Wird verarbeitet...";
            
            let progressContainer = document.getElementById("progress-container");
            let progressBar = document.getElementById("progress-bar");
            let statusMessage = document.getElementById("status-message");

            progressContainer.style.display = "block";
            progressBar.style.width = "0%";
            progressBar.innerText = "0%";
            statusMessage.innerText = "Lade...";

            fetch(ajaxurl, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "action=wp_multi_process_auto_tags"
            })
            .then(response => response.json())
            .then(data => {
                let total = data.total;
                let processed = 0;
                let batchSize = 10;

                function updateProgress() {
                    fetch(ajaxurl, {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "action=wp_multi_process_auto_tags_step&batchSize=" + batchSize
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.done) {
                            processed += batchSize;
                            let percent = Math.round((processed / total) * 100);
                            progressBar.style.width = percent + "%";
                            progressBar.innerText = percent + "%";

                            if (processed < total) {
                                updateProgress();
                            } else {
                                button.innerText = "Jetzt ausführen";
                                button.disabled = false;
                                statusMessage.innerText = "Automatische Tags wurden erfolgreich hinzugefügt!";
                            }
                        }
                    });
                }
                updateProgress();
            });
        });
    </script>
    <?php
}

// Einstellungen registrieren
function wp_multi_auto_tags_settings_init() {
    register_setting('wp_multi_auto_tags_options', 'wp_multi_custom_stopwords');
}
add_action('admin_init', 'wp_multi_auto_tags_settings_init');

// AJAX-Aufrufe für schnelle Verarbeitung
add_action('wp_ajax_wp_multi_process_auto_tags', 'wp_multi_process_auto_tags');
function wp_multi_process_auto_tags() {
    $args = ['post_type' => 'post', 'posts_per_page' => -1, 'fields' => 'ids'];
    $posts = get_posts($args);
    
    set_transient('wp_multi_auto_tags_queue', $posts, 300);
    
    wp_send_json(['total' => count($posts)]);
}

add_action('wp_ajax_wp_multi_process_auto_tags_step', 'wp_multi_process_auto_tags_step');
function wp_multi_process_auto_tags_step() {
    $queue = get_transient('wp_multi_auto_tags_queue');
    $batchSize = isset($_POST['batchSize']) ? intval($_POST['batchSize']) : 10;

    if (!$queue || empty($queue)) {
        wp_send_json(['done' => false]);
    }

    $posts_to_process = array_splice($queue, 0, $batchSize);
    
    foreach ($posts_to_process as $post_id) {
        wp_multi_auto_add_tags($post_id);
    }

    set_transient('wp_multi_auto_tags_queue', $queue, 300);
    
    wp_send_json(['done' => true]);
}


/*
* Admin - Panel Banner 
*/


// Admin-Banner als Notice mit Blauem Hintergrund (#0073aa)
function wp_multi_add_warning_banner() {
    echo '
    <div class="notice notice-warning is-dismissible" style="background-color: #0073aa; color: white; border-left: 4px solid #005177;">
        <p><strong>Danke, dass du WP Multi verwendest!</strong> Dein Feedback hilft uns, das Plugin ständig zu verbessern. Wenn du Fehler entdeckst oder Verbesserungsvorschläge hast, besuche bitte unsere <a href="https://git.viper.ipv64.net/M_Viper/wp-multi" target="_blank" style="color: #FFDD00; text-decoration: none;">Gitea-Seite</a> und teile uns deine Ideen mit!</p>
    </div>';
}
add_action('admin_notices', 'wp_multi_add_warning_banner');


/*
* Anti Spam Honey 
*/


// Standardwerte setzen
function wp_multi_set_default_options() {
    add_option('wp_multi_honeypot_field', 'iwlxja5187');
    add_option('wp_multi_honeypot_error', 'Spamming or your Javascript is disabled !!');
    add_option('wp_multi_honeypot_widget', 0);
    add_option('wp_multi_max_links', 3);
    add_option('wp_multi_blocked_keywords', 'viagra,casino,bitcoin');
    add_option('wp_multi_blocked_ips', '');
    add_option('wp_multi_blocked_comments', 0); // Zähler für blockierte Kommentare
    add_option('wp_multi_honeypot_hits', 0); // Zähler für Honeypot-Aktivierungen
    add_option('wp_multi_spammer_ips', []); // Liste der blockierten Spammer-IP-Adressen
    add_option('wp_multi_spam_submissions', []); // Liste der Spam-Einreichungen
}
register_activation_hook(__FILE__, 'wp_multi_set_default_options');

// Menüpunkt "Sicherheit" und Statistik hinzufügen
function wp_multi_add_security_menu() {
    add_menu_page(
        'Sicherheit', 
        'Sicherheit', 
        'manage_options', 
        'wp-multi-security', 
        'wp_multi_security_settings_page', 
        'dashicons-shield', 
        80
    );
    add_submenu_page(
        'wp-multi-security', 
        'WP Multi Statistik', 
        'WP Multi Statistik', 
        'manage_options', 
        'wp-multi-statistics', 
        'wp_multi_statistics_page'
    );
}
add_action('admin_menu', 'wp_multi_add_security_menu');

// Einstellungsseite mit CSS & JS für Generator
function wp_multi_security_settings_page() {
    ?>
    <div class="wp-multi-security-wrap">
        <div class="wp-multi-banner">
            <img src="https://m-viper.de/img/logo.png" alt="WP Multi Logo">
            <h1>WP Multi - Anti Spam</h1>
        </div>
        <form method="post" action="options.php">
            <?php
            settings_fields('wp_multi_security_settings');
            do_settings_sections('wp-multi-security');
            submit_button();
            ?>
        </form>
    </div>
    <script>
        function generateHoneypotName() {
            let field = document.getElementById('wp_multi_honeypot_field');
            let randomString = Math.random().toString(36).substring(2, 12);
            field.value = randomString;
        }
    </script>
    <style>
        .wp-multi-security-wrap {
            max-width: 700px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .wp-multi-banner {
            background: #0073aa;
            padding: 15px;
            text-align: center;
            border-radius: 10px;
            color: #fff;
        }
        .wp-multi-banner img {
            max-height: 50px;
            display: block;
            margin: 0 auto 10px;
        }
        .wp-multi-banner h1 {
            margin: 0;
            font-size: 22px;
        }
        .wp-multi-honeypot-group {
            display: flex;
            align-items: center;
        }
        .wp-multi-honeypot-group input {
            flex: 1;
            margin-right: 10px;
        }
        button {
            cursor: pointer;
            background: #0073aa;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
        }
        button:hover {
            background: #005f88;
        }
    </style>
    <?php
}

// Statistikseite im Dashboard
function wp_multi_statistics_page() {
    $blocked_comments = get_option('wp_multi_blocked_comments', 0);
    $honeypot_hits = get_option('wp_multi_honeypot_hits', 0);
    $spammer_ips = get_option('wp_multi_spammer_ips', []);
    $spam_submissions = get_option('wp_multi_spam_submissions', []);
    
    ?>
    <div class="wrap wp-multi-statistics-wrap">
        <div class="wp-multi-banner">
            <img src="https://m-viper.de/img/logo.png" alt="WP Multi Logo">
            <h1>WP Multi - Anti Spam Statistik</h1>
        </div>

        <div class="wp-multi-statistics">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Statistik</th>
                        <th>Wert</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Blockierte Kommentare</td>
                        <td><?php echo $blocked_comments; ?></td>
                    </tr>
                    <tr>
                        <td>Aktivierte Honeypot-Felder</td>
                        <td><?php echo $honeypot_hits; ?></td>
                    </tr>
                    <tr>
                        <td>Spammer-IP-Adressen</td>
                        <td><?php echo count($spammer_ips); ?></td>
                    </tr>
                    <tr>
                        <td>Spam-Einreichungen</td>
                        <td><?php echo count($spam_submissions); ?></td>
                    </tr>
                </tbody>
            </table>

            <h2>Spammer-IP-Adressen</h2>
            <?php if (!empty($spammer_ips)): ?>
                <ul class="wp-multi-spammer-ips">
                    <?php foreach ($spammer_ips as $ip): ?>
                        <li><?php echo esc_html($ip); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Keine Spammer-IP-Adressen gefunden.</p>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .wp-multi-statistics-wrap {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .wp-multi-banner {
            background: #0073aa;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            color: #fff;
        }

        .wp-multi-banner img {
            max-height: 60px;
            display: block;
            margin: 0 auto 10px;
        }

        .wp-multi-banner h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .wp-multi-statistics table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .wp-multi-statistics th,
        .wp-multi-statistics td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .wp-multi-statistics th {
            background-color: #0073aa;
            color: #fff;
        }

        .wp-multi-statistics tbody tr:hover {
            background-color: #f1f1f1;
        }

        .wp-multi-spammer-ips {
            list-style-type: none;
            padding-left: 0;
        }

        .wp-multi-spammer-ips li {
            padding: 5px;
            background-color: #f1f1f1;
            margin: 5px 0;
            border-radius: 5px;
        }
    </style>
    <?php
}


// Einstellungen registrieren
function wp_multi_register_security_settings() {
    register_setting('wp_multi_security_settings', 'wp_multi_honeypot_field');
    register_setting('wp_multi_security_settings', 'wp_multi_honeypot_error');
    register_setting('wp_multi_security_settings', 'wp_multi_honeypot_widget');
    register_setting('wp_multi_security_settings', 'wp_multi_max_links');
    register_setting('wp_multi_security_settings', 'wp_multi_blocked_keywords');
    register_setting('wp_multi_security_settings', 'wp_multi_blocked_ips');

    add_settings_section('wp_multi_honeypot_section', 'Honeypot Einstellungen', null, 'wp-multi-security');

    add_settings_field('wp_multi_honeypot_field', 'Honey Pot Field Name', 'wp_multi_honeypot_field_callback', 'wp-multi-security', 'wp_multi_honeypot_section');
    add_settings_field('wp_multi_honeypot_error', 'Honey Pot Error Message', 'wp_multi_honeypot_error_callback', 'wp-multi-security', 'wp_multi_honeypot_section');
    add_settings_field('wp_multi_honeypot_widget', 'Disable Honeypot Test Widget', 'wp_multi_honeypot_widget_callback', 'wp-multi-security', 'wp_multi_honeypot_section');
    add_settings_field('wp_multi_max_links', 'Maximale Links im Kommentar', 'wp_multi_max_links_callback', 'wp-multi-security', 'wp_multi_honeypot_section');
    add_settings_field('wp_multi_blocked_keywords', 'Blockierte Schlüsselwörter', 'wp_multi_blocked_keywords_callback', 'wp-multi-security', 'wp_multi_honeypot_section');
    add_settings_field('wp_multi_blocked_ips', 'Blockierte IP-Adressen', 'wp_multi_blocked_ips_callback', 'wp-multi-security', 'wp_multi_honeypot_section');
}

add_action('admin_init', 'wp_multi_register_security_settings');

function wp_multi_honeypot_field_callback() {
    ?>
    <div class="wp-multi-honeypot-group">
        <input type="text" id="wp_multi_honeypot_field" name="wp_multi_honeypot_field" value="<?php echo esc_attr(get_option('wp_multi_honeypot_field')); ?>">
        <button type="button" onclick="generateHoneypotName()">Generieren</button>
    </div>
    <small>Verwenden Sie ein zufälliges Zeichenfolgen für das Honeypot-Feld.</small>
    <?php
}

function wp_multi_honeypot_error_callback() {
    ?>
    <input type="text" name="wp_multi_honeypot_error" value="<?php echo esc_attr(get_option('wp_multi_honeypot_error')); ?>">
    <small>Die Nachricht, die angezeigt wird, wenn ein Honeypot ausgelöst wird.</small>
    <?php
}

function wp_multi_honeypot_widget_callback() {
    ?>
    <input type="checkbox" name="wp_multi_honeypot_widget" value="1" <?php checked(1, get_option('wp_multi_honeypot_widget'), true); ?>>
    <small>Deaktivieren Sie das Honeypot-Test-Widget im Frontend.</small>
    <?php
}

function wp_multi_max_links_callback() {
    ?>
    <input type="number" name="wp_multi_max_links" value="<?php echo esc_attr(get_option('wp_multi_max_links')); ?>">
    <small>Maximale Anzahl von Links, die in einem Kommentar erlaubt sind.</small>
    <?php
}

function wp_multi_blocked_keywords_callback() {
    ?>
    <input type="text" name="wp_multi_blocked_keywords" value="<?php echo esc_attr(get_option('wp_multi_blocked_keywords')); ?>">
    <small>Schlüsselwörter, die blockiert werden sollen (durch Kommas getrennt).</small>
    <?php
}

function wp_multi_blocked_ips_callback() {
    ?>
    <textarea name="wp_multi_blocked_ips" rows="5"><?php echo esc_textarea(get_option('wp_multi_blocked_ips')); ?></textarea>
    <small>Blockierte IP-Adressen (jede Adresse in einer neuen Zeile).</small>
    <?php
}




/*
* Schutz vor Brute-Force-Angriffen
*/


// Funktion zur Erfassung der echten IP-Adresse des Benutzers
function get_user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Funktion zur Verfolgung von fehlgeschlagenen Anmeldeversuchen
function wp_multi_log_failed_login($username) {
    global $wpdb;

    // Holen der IP-Adresse
    $ip = get_user_ip();
    $table_name = $wpdb->prefix . 'blocked_ips'; // Tabelle für blockierte IPs
    $user = get_user_by('login', $username); // Benutzerinformationen basierend auf dem Anmeldenamen
    
    // Überprüfen, ob die IP bereits in der Tabelle existiert
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE ip = %s", $ip));

    if ($row) {
        // Wenn die IP existiert, erhöhen wir die Anzahl der fehlgeschlagenen Versuche
        $wpdb->update(
            $table_name,
            array('attempts' => $row->attempts + 1, 'last_attempt' => current_time('mysql')),
            array('ip' => $ip)
        );
    } else {
        // Wenn die IP nicht existiert, fügen wir sie hinzu
        $wpdb->insert(
            $table_name,
            array('ip' => $ip, 'attempts' => 1, 'last_attempt' => current_time('mysql')) // Die `last_attempt` sollte ebenfalls beim Einfügen gesetzt werden
        );
    }

    // Zähler für E-Mails und Versuche (maximal 3 E-Mails)
    $max_attempts = 3;

    // Wenn die Anzahl der Versuche größer oder gleich 5 ist, blockiere die IP und sende E-Mails
    if ($row && $row->attempts >= 5) {
        // Prüfen, ob bereits mehr als 3 E-Mails versendet wurden
        $email_sent = get_option('failed_login_email_sent_' . $ip, 0);

        if ($email_sent < $max_attempts) {
            // E-Mail an den betroffenen Benutzer senden (falls der Benutzer existiert)
            if ($user) {
                wp_mail(
                    $user->user_email,
                    'Deine IP-Adresse wurde gesperrt',
                    'Hallo ' . $user->user_login . ',\n\nDeine IP-Adresse wurde aufgrund zu vieler fehlgeschlagener Anmeldeversuche gesperrt. Bitte kontaktiere den Administrator, falls du Unterstützung benötigst.',
                    array('Content-Type: text/plain; charset=UTF-8')
                );
                // Zähler erhöhen
                update_option('failed_login_email_sent_' . $ip, $email_sent + 1);
            }

            // E-Mail an den Administrator senden
            $admin_email = get_option('admin_email');
            wp_mail(
                $admin_email,
                'Brute-Force-Angriff erkannt',
                'Es wurde ein Brute-Force-Angriff auf deine WordPress-Seite erkannt. Die IP-Adresse ' . $ip . ' wurde nach mehreren fehlgeschlagenen Anmeldeversuchen blockiert.',
                array('Content-Type: text/plain; charset=UTF-8')
            );
            // Zähler erhöhen
            update_option('failed_login_email_sent_' . $ip, $email_sent + 1);
        }

        // Benutzer sperren und eine Fehlermeldung anzeigen
        wp_die("Deine IP-Adresse wurde aufgrund zu vieler Fehlversuche gesperrt. Bitte versuche es später noch einmal.");
    }
}

// Funktion zur Überwachung von Benutzeranmeldungen
function wp_multi_failed_login_hook($username) {
    wp_multi_log_failed_login($username);
}

// Hook zum Abfangen fehlgeschlagener Anmeldungen
add_action('wp_login_failed', 'wp_multi_failed_login_hook');

// Funktion zur Erstellung der Tabelle für blockierte IPs (Einmal bei der Installation ausführen)
function wp_multi_create_blocked_ips_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'blocked_ips'; // Tabelle für blockierte IPs
    $charset_collate = $wpdb->get_charset_collate();

    // SQL-Anweisung zur Erstellung der Tabelle
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        ip varchar(45) NOT NULL,
        attempts int NOT NULL DEFAULT 0,
        last_attempt datetime NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql); // Tabellen erstellen oder aktualisieren
}

// Diese Funktion wird beim Aktivieren des Plugins aufgerufen
register_activation_hook(__FILE__, 'wp_multi_create_blocked_ips_table');

function wp_multi_blocked_ips_menu() {
    add_submenu_page(
        'wp-multi-security',  // Übergeordnetes Menü: "Sicherheit"
        'Blockierte IPs',     // Titel der Seite
        'Blockierte IPs',     // Menüname
        'manage_options',     // Berechtigung (nur Administratoren)
        'wp_multi_blocked_ips', // Slug
        'wp_multi_display_blocked_ips' // Callback-Funktion
    );
}
add_action('admin_menu', 'wp_multi_blocked_ips_menu');

// Callback-Funktion für die Anzeige der blockierten IPs
function wp_multi_display_blocked_ips() {
    global $wpdb;

    // Tabelle für blockierte IPs
    $table_name = $wpdb->prefix . 'blocked_ips';

    // Berechnen des Datums vor 5 Tagen
    $five_days_ago = date('Y-m-d H:i:s', strtotime('-5 days'));

    // Berechnung der Pagination
    $per_page = 50;
    $page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($page - 1) * $per_page;

    // Hole alle blockierten IPs aus der Datenbank, die innerhalb der letzten 5 Tage liegen
    $blocked_ips = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE last_attempt >= %s ORDER BY last_attempt DESC LIMIT %d OFFSET %d",
            $five_days_ago,
            $per_page,
            $offset
        )
    );

    // Wenn keine blockierten IPs vorhanden sind
    if (empty($blocked_ips)) {
        echo '<h1>Keine blockierten IPs gefunden</h1>';
        return;
    }

    // HTML-Tabelle zur Anzeige der blockierten IPs
    echo '<h1>Blockierte IPs (letzte 5 Tage)</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>ID</th><th>IP-Adresse</th><th>Versuche</th><th>Letzter Versuch</th><th>Aktionen</th></tr></thead>';
    echo '<tbody>';

    foreach ($blocked_ips as $ip) {
        echo '<tr>';
        echo '<td>' . $ip->id . '</td>';
        echo '<td>' . $ip->ip . '</td>';
        echo '<td>' . $ip->attempts . '</td>';
        echo '<td>' . $ip->last_attempt . '</td>';
        echo '<td><a href="' . admin_url('admin-post.php?action=remove_blocked_ip&id=' . $ip->id) . '">Entfernen</a></td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

    // Berechne die Gesamtzahl der blockierten IPs
    $total_ips = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE last_attempt >= '$five_days_ago'");

    // Pagination
    $total_pages = ceil($total_ips / $per_page);
    if ($total_pages > 1) {
        echo '<div class="tablenav"><div class="alignleft actions">';
        for ($i = 1; $i <= $total_pages; $i++) {
            $class = ($i == $page) ? ' class="current"' : '';
            echo '<a href="' . admin_url('users.php?page=wp_multi_blocked_ips&paged=' . $i) . '" ' . $class . '>' . $i . '</a> ';
        }
        echo '</div></div>';
    }

    // Automatische Löschung von IPs mit weniger als 10 Versuchen, die älter als 3 Tage sind
    $three_days_ago = date('Y-m-d H:i:s', strtotime('-3 days'));
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM $table_name WHERE attempts < 10 AND last_attempt < %s",
            $three_days_ago
        )
    );
}

// Funktion zum Entfernen einer blockierten IP
function wp_multi_remove_blocked_ip() {
    if (!current_user_can('manage_options')) {
        wp_die('Du hast nicht die erforderlichen Berechtigungen, um diese Aktion durchzuführen.');
    }

    global $wpdb;

    // Hole die IP-ID aus der URL
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id > 0) {
        // Lösche die blockierte IP aus der Datenbank
        $table_name = $wpdb->prefix . 'blocked_ips';
        $wpdb->delete($table_name, array('id' => $id));
    }

    // Weiterleitung zurück zur Admin-Seite der blockierten IPs
    wp_redirect(admin_url('users.php?page=wp_multi_blocked_ips'));
    exit;
}
add_action('admin_post_remove_blocked_ip', 'wp_multi_remove_blocked_ip');


/*
* Admin - Pinnwand
*/

// Funktion zum Erstellen der Datenbanktabelle für Nachrichten
function wp_multi_create_message_board_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'message_board'; // Tabelle für Nachrichten
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        message text NOT NULL,
        user_id bigint(20) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'wp_multi_create_message_board_table');

// Funktion zum Anzeigen der Nachrichten im Adminbereich
function wp_multi_add_message_board() {
    if (!current_user_can('administrator')) {
        return;
    }

    ?>
    <style>
        body {
            background-image: url('<?php echo plugin_dir_url( __FILE__ ); ?>img/pinwand.jpg');
            background-size: cover; 
            background-position: center; 
            background-repeat: no-repeat; 
            margin: 0;
            padding: 0;
            height: 100vh; 
            background-attachment: fixed; 
        }

        .message-board {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 20px;
            height: 100%; 
            overflow-y: auto; 
            z-index: 1; 
        }

        .message-card {
            background: #f4f4f4;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .message-card:hover {
            background: #e1e1e1;
        }

        .message-card a {
            display: block;
            margin-top: 10px;
        }

        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            margin-right: 10px;
        }

        .button:hover {
            background-color: #45a049;
        }

        .button-danger {
    background-color: red !important;
    color: white !important;
}

        .button-danger:hover {
            background-color: darkred;
        }

        .button-primary {
            background-color: blue;
            color: white;
        }

        .button-primary:hover {
            background-color: darkblue;
        }

        .message-card p {
            font-size: 14px;
            color: #333;
        }

        .message-card strong {
            font-size: 16px;
            color: #0073aa;
        }

        .button-container {
            display: flex;
            gap: 10px;
            justify-content: flex-start;
            margin-top: 20px; /* Abstand nach den Nachrichtenschaltflächen */
        }

        #messagePopup {
            display: none;
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            z-index: 999;
        }

        .message-form {
            margin-bottom: 30px; /* Abstand zum nächsten Inhalt */
        }
    </style>

    <div class="wrap">
        <h2>Pinwand</h2>

        <h3>Neue Nachricht erstellen</h3>
        <form class="message-form" method="post">
            <textarea name="new_message" rows="5" cols="50" required></textarea><br><br>
            <input type="submit" name="submit_message" value="Nachricht erstellen" class="button button-primary">
        </form>

        <?php
        global $wpdb;
        $table_name = $wpdb->prefix . 'message_board';
        $messages = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

        if ($messages) {
            echo '<div class="message-board">';
            foreach ($messages as $message) {
                $user_info = get_userdata($message->user_id);
                echo '<div class="message-card" onclick="openMessagePopup(' . $message->id . ')">'; 
                echo '<strong>' . esc_html($user_info->user_login) . ' (' . date('d-m-Y H:i:s', strtotime($message->created_at)) . ')</strong>';
                echo '<p>' . wp_trim_words($message->message, 20) . '...</p>';  // Zeige nur eine Vorschau
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p>Keine Nachrichten vorhanden.</p>';
        }
        ?>

    </div>

    <div id="messagePopup">
        <div id="messageContent"></div>
        <form id="editMessageForm" style="display:none;" method="post">
            <textarea name="message" id="messageText" rows="10" cols="50" required></textarea><br><br>
            <input type="submit" value="Nachricht aktualisieren" class="button button-primary">
        </form>
        <div class="button-container">
            <button onclick="closeMessagePopup()" class="button button-primary">Schließen</button>
            <button id="deleteMessageBtn" class="button button-danger" onclick="deleteMessage()">Löschen</button>
        </div>
    </div>

    <script>
        function openMessagePopup(messageId) {
            var data = {
                'action': 'wp_multi_get_message',
                'message_id': messageId
            };

            jQuery.post(ajaxurl, data, function(response) {
                var messageData = JSON.parse(response);
                document.getElementById('messageContent').innerHTML = '<h3>' + messageData.created_at + ' (' + messageData.user + ')</h3><p>' + messageData.message + '</p>';
                document.getElementById('messageText').value = messageData.message;
                document.getElementById('messagePopup').style.display = 'block';
                document.getElementById('editMessageForm').style.display = 'block';
                document.getElementById('deleteMessageBtn').setAttribute('data-message-id', messageId);
            });
        }

        function closeMessagePopup() {
            document.getElementById('messagePopup').style.display = 'none';
        }

        function deleteMessage() {
            var messageId = document.getElementById('deleteMessageBtn').getAttribute('data-message-id');

            var data = {
                'action': 'wp_multi_delete_message',
                'message_id': messageId
            };

            jQuery.post(ajaxurl, data, function(response) {
                if (response == 'success') {
                    closeMessagePopup();
                    location.reload();
                }
            });
        }
    </script>
    <?php
    // Nachricht erstellen
    if (isset($_POST['submit_message']) && !empty($_POST['new_message'])) {
        $new_message = sanitize_text_field($_POST['new_message']);
        $user_id = get_current_user_id();

        global $wpdb;
        $table_name = $wpdb->prefix . 'message_board';
        $wpdb->insert(
            $table_name,
            array(
                'message' => $new_message,
                'user_id' => $user_id
            )
        );
        echo '<p>Nachricht wurde erfolgreich erstellt.</p>';
        echo "<script>window.location.reload();</script>"; // Seite neu laden
    }
}

// Menüeintrag im Adminbereich hinzufügen
function wp_multi_add_message_board_menu() {
    add_menu_page(
        'Pinwand', // Seitentitel
        'Pinwand', // Menüeintrag
        'manage_options', // Berechtigung
        'message-board', // Slug
        'wp_multi_add_message_board', // Callback
        'dashicons-bell', // Icon
        6 // Position im Menü
    );
}
add_action('admin_menu', 'wp_multi_add_message_board_menu');

// Funktion zum Abrufen der vollständigen Nachricht
function wp_multi_get_message() {
    if (isset($_POST['message_id'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'message_board';
        $message_id = intval($_POST['message_id']);
        $message = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $message_id");

        if ($message) {
            // Datum im gewünschten Format (DD-MM-JJJJ HH:MM:SS)
            $formatted_date = date('d-m-Y H:i:s', strtotime($message->created_at));

            echo json_encode([
                'created_at' => $formatted_date,
                'message' => nl2br(esc_textarea($message->message)),
                'user' => get_userdata($message->user_id)->user_login
            ]);
        }
    }
    wp_die();
}

add_action('wp_ajax_wp_multi_get_message', 'wp_multi_get_message');

// Funktion zum Löschen einer Nachricht
function wp_multi_delete_message() {
    if (isset($_POST['message_id'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'message_board';
        $message_id = intval($_POST['message_id']);
        $wpdb->delete($table_name, array('id' => $message_id));

        echo 'success';
    }
    wp_die();
}
add_action('wp_ajax_wp_multi_delete_message', 'wp_multi_delete_message');

// Funktion zum Deaktivieren der Pinwand bei der Deinstallation
function wp_multi_delete_message_board_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'message_board';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
register_deactivation_hook(__FILE__, 'wp_multi_delete_message_board_table');

// Funktion, um das Dashboard-Widget zu registrieren
function wp_multi_dashboard_widget() {
    wp_add_dashboard_widget(
        'wp_multi_pinwand_widget',  // Widget-ID
        'Pinwand Übersicht',        // Widget-Titel
        'wp_multi_dashboard_widget_content'  // Callback-Funktion
    );
}
add_action('wp_dashboard_setup', 'wp_multi_dashboard_widget');

// Callback-Funktion, die den Inhalt des Widgets erstellt
function wp_multi_dashboard_widget_content() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'message_board';
    $messages = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 5");  // Zeige die neuesten 5 Nachrichten an

    if ($messages) {
        echo '<ul>';
        foreach ($messages as $message) {
            $user_info = get_userdata($message->user_id);
            echo '<li>';
            echo '<strong>' . esc_html($user_info->user_login) . ' (' . date('d-m-Y H:i:s', strtotime($message->created_at)) . ')</strong>: ';
            echo wp_trim_words($message->message, 10) . '...';  // Zeigt nur eine Vorschau der Nachricht
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>Keine neuen Nachrichten.</p>';
    }
}


/*
* Benutzer-Analytics
*/

// Funktion zur Erstellung der Datenbanktabelle für Benutzer-Analytics
function wp_multi_create_analytics_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_multi_user_analytics';

    // Überprüfen, ob die Tabelle bereits existiert
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        // Tabelle erstellen
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            action varchar(255) NOT NULL,
            post_id bigint(20) DEFAULT NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
register_activation_hook(__FILE__, 'wp_multi_create_analytics_table');

// Funktion zur Verfolgung von Benutzerinteraktionen (Kommentare und Beitragsaufrufe)
function wp_multi_track_user_activity($user_id, $action, $post_id = null) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_multi_user_analytics';

    // Wenn die Aktion ein 'view' ist, stelle sicher, dass wir die post_id korrekt setzen
    if ($action == 'view' && is_single()) {
        $post_id = get_the_ID();
    }

    // Benutzerinteraktionen in die Datenbank speichern
    $wpdb->insert(
        $table_name,
        array(
            'user_id'   => $user_id,
            'action'    => $action,
            'post_id'   => $post_id,
        )
    );
}

// Kommentar-Verfolgung
function wp_multi_comment_activity($comment_id) {
    $comment = get_comment($comment_id);
    $user_id = $comment->user_id;
    wp_multi_track_user_activity($user_id, 'comment', $comment->comment_post_ID);
}
add_action('comment_post', 'wp_multi_comment_activity');

// Beitragsaufruf-Verfolgung
function wp_multi_post_view_activity() {
    if (is_single() && is_user_logged_in()) {
        $user_id = get_current_user_id();
        $post_id = get_the_ID();
        wp_multi_track_user_activity($user_id, 'view', $post_id);
    }
}
add_action('wp_head', 'wp_multi_post_view_activity');

// Funktion zur Anzeige der Benutzer-Analytics im Admin-Bereich
function wp_multi_display_user_analytics() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_multi_user_analytics';

    // Abfrage, um die Benutzerinteraktionen zu holen
    $results = wp_multi_get_analytics_data();

    ?>
    <div class="wrap">
        <div style="background-color: #0073aa; padding: 20px; text-align: center; color: white;">
            <img src="https://m-viper.de/img/logo.png" alt="Logo" style="height: 50px; vertical-align: middle;">
            <h1 style="display: inline; margin-left: 10px;"><?php _e('Benutzer Analytics', 'wp-multi'); ?></h1>
        </div>

        <canvas id="userActivityChart" style="height: 300px; width: 100%;"></canvas>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var ctx = document.getElementById('userActivityChart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($results['dates']); ?>,  // Tag-basierte Labels
                        datasets: <?php echo json_encode($results['datasets']); ?>, // Kommentare und Beiträge
                    },
                    options: {
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Datum'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Anzahl'
                                },
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
        </script>

        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e('Benutzer ID', 'wp-multi'); ?></th>
                    <th><?php _e('Aktion', 'wp-multi'); ?></th>
                    <th><?php _e('Beitrag Titel', 'wp-multi'); ?></th>
                    <th><?php _e('Beitrag ID', 'wp-multi'); ?></th>
                    <th><?php _e('Zeitstempel', 'wp-multi'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($results['data'])): ?>
                    <?php foreach ($results['data'] as $index => $row) : ?>
                        <tr style="background-color: <?php echo ($index % 2 == 0) ? '#f9f9f9' : '#ffffff'; ?>;">
                            <td><?php echo esc_html($row->user_id); ?></td>
                            <td><?php echo esc_html($row->action); ?></td>
                            <td>
                                <?php 
                                // Titel des Beitrags abrufen
                                if ($row->post_id) {
                                    $post_title = get_the_title($row->post_id);
                                    echo esc_html($post_title ? $post_title : 'Kein Titel verfügbar');
                                } else {
                                    echo 'Kein Beitrag';
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html($row->post_id); ?></td>
                            <td><?php echo esc_html($row->timestamp); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5"><?php _e('Keine Daten verfügbar', 'wp-multi'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Funktion, um die Analytics-Daten zu holen (Datum und Anzahl der Aktivitäten)
function wp_multi_get_analytics_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_multi_user_analytics';

    // Die letzten 7 Tage abrufen
    $results = $wpdb->get_results(" 
        SELECT DATE(timestamp) AS date, action, post_id, COUNT(*) AS count, user_id, timestamp
        FROM $table_name 
        WHERE timestamp >= CURDATE() - INTERVAL 7 DAY 
        GROUP BY date, action, post_id, user_id, timestamp 
        ORDER BY date ASC
    ");

    // Daten für das Diagramm und die Tabelle organisieren
    $dates = array();
    $comment_counts = array();
    $view_counts = array();
    $post_titles = array();

    foreach ($results as $result) {
        $dates[] = $result->date;
        if ($result->action == 'comment') {
            $comment_counts[$result->date] = $result->count;
        } elseif ($result->action == 'view') {
            $view_counts[$result->date] = $result->count;
        }

        // Hinzufügen der Post-Titel für die Anzeige
        if (!empty($result->post_id)) {
            $post_titles[$result->post_id] = get_the_title($result->post_id);
        }
    }

    // Sicherstellen, dass alle Daten für die letzten 7 Tage vorhanden sind
    $unique_dates = array_unique($dates);
    $all_dates = array();
    $datasets = array(
        'comments' => [],
        'views' => []
    );

    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i day"));
        $all_dates[] = $date;
        $datasets['comments'][] = isset($comment_counts[$date]) ? $comment_counts[$date] : 0;
        $datasets['views'][] = isset($view_counts[$date]) ? $view_counts[$date] : 0;
    }

    // Rückgabe der Daten für das Diagramm und die Tabelle
    return [
        'dates' => array_reverse($all_dates),
        'datasets' => [
            [
                'label' => 'Kommentare',
                'data' => array_reverse($datasets['comments']),
                'borderColor' => 'rgba(75, 192, 192, 1)',
                'borderWidth' => 1,
                'fill' => false,
            ],
            [
                'label' => 'Beitragsaufrufe',
                'data' => array_reverse($datasets['views']),
                'borderColor' => 'rgba(153, 102, 255, 1)',
                'borderWidth' => 1,
                'fill' => false,
            ]
        ],
        'data' => $results
    ];
}

// Hinzufügen der Analytics-Seite unter "Benutzer" im Admin-Menü
function wp_multi_add_analytics_page() {
    add_submenu_page(
        'users.php', // Die übergeordnete Seite (Benutzer)
        __('Benutzer Analytics', 'wp-multi'), // Titel der Seite
        __('Benutzer Analytics', 'wp-multi'), // Text im Menü
        'manage_options', // Berechtigungen
        'wp_multi_analytics', // Menü-Slug
        'wp_multi_display_user_analytics' // Die Funktion, die die Seite anzeigt
    );
}
add_action('admin_menu', 'wp_multi_add_analytics_page');


/*
* User Daten Filtern (URL, Mail-Adresse usw...)
*/


// Admin-Einstellungen registrieren
function wp_multi_register_comment_filter_settings() {
    add_option('wp_multi_filter_phone', '1');
    add_option('wp_multi_filter_email', '1');
    add_option('wp_multi_filter_url', '1');
    add_option('wp_multi_filter_swear', '1');
    add_option('wp_multi_filter_ip', '1'); // Neue Option für IP-Filterung

    register_setting('wp_multi_filter_options_group', 'wp_multi_filter_phone');
    register_setting('wp_multi_filter_options_group', 'wp_multi_filter_email');
    register_setting('wp_multi_filter_options_group', 'wp_multi_filter_url');
    register_setting('wp_multi_filter_options_group', 'wp_multi_filter_swear');
    register_setting('wp_multi_filter_options_group', 'wp_multi_filter_ip'); // Neue Option für IP-Filterung
}
add_action('admin_init', 'wp_multi_register_comment_filter_settings');



    // Admin-Menü & Untermenü hinzufügen
    function wp_multi_create_menu() {
        // 'Benutzer sperren' Menü als Untermenü im Benutzer-Menü hinzufügen
        add_submenu_page(
            'users.php',  // 'Benutzer' Menü
            'Benutzer sperren', 
            'Benutzer sperren', 
            'manage_options', 
            'wp-multi-blocked-users', 
            'wp_multi_blocked_users_page'
        );

        // Kommentar-Filter unter Kommentare verschieben
        add_submenu_page(
            'edit-comments.php',  // 'Kommentare' Menü
            'Kommentar-Filter Einstellungen', 
            'Kommentar-Filter', 
            'manage_options', 
            'wp-multi-comment-filter-settings', 
            'wp_multi_comment_filter_settings_page'
        );
    }
    add_action('admin_menu', 'wp_multi_create_menu');




// Admin-Seite für Kommentar-Filter
function wp_multi_comment_filter_settings_page() {
    ?>
    <div class="wrap">
        <!-- Blaues Banner mit Logo -->
        <div class="wp-multi-banner">
            <img src="https://m-viper.de/img/logo.png" alt="Logo" class="wp-multi-logo">
        </div>

        <h1>Kommentar-Filter Einstellungen</h1>
        
        <form method="post" action="options.php">
            <?php settings_fields('wp_multi_filter_options_group'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="wp_multi_filter_phone">Rufnummern filtern</label></th>
                    <td><input type="checkbox" name="wp_multi_filter_phone" value="1" <?php checked(1, get_option('wp_multi_filter_phone'), true); ?>></td>
                </tr>
                <tr>
                    <th><label for="wp_multi_filter_email">E-Mail-Adressen filtern</label></th>
                    <td><input type="checkbox" name="wp_multi_filter_email" value="1" <?php checked(1, get_option('wp_multi_filter_email'), true); ?>></td>
                </tr>
                <tr>
                    <th><label for="wp_multi_filter_url">URLs filtern</label></th>
                    <td><input type="checkbox" name="wp_multi_filter_url" value="1" <?php checked(1, get_option('wp_multi_filter_url'), true); ?>></td>
                </tr>
                <tr>
                    <th><label for="wp_multi_filter_swear">Schimpfwörter filtern</label></th>
                    <td><input type="checkbox" name="wp_multi_filter_swear" value="1" <?php checked(1, get_option('wp_multi_filter_swear'), true); ?>></td>
                </tr>
                <tr>
                    <th><label for="wp_multi_filter_ip">IP-Adressen filtern</label></th>
                    <td><input type="checkbox" name="wp_multi_filter_ip" value="1" <?php checked(1, get_option('wp_multi_filter_ip'), true); ?>></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>

    <style>
        /* Banner Styling */
        .wp-multi-banner {
            background-color: #0073aa; /* Blaues Banner */
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            margin-bottom: 30px;
        }

        .wp-multi-logo {
            max-width: 200px;
            height: auto;
        }

        /* Anpassung für die Kommentar-Filter-Seite */
        .wrap {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #0073aa;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .form-table th {
            padding: 12px 15px;
            text-align: left;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }

        .form-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
        }

        .form-table input[type="checkbox"] {
            margin-right: 10px;
        }

        input[type="submit"] {
            background-color: #0073aa;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #005177;
        }
    </style>
    <?php
}

// Kommentar-Filter
function wp_multi_filter_comment_content($comment_content) {
    // Rufnummern filtern (mit verschiedenen Trennzeichen und Formaten)
    if (get_option('wp_multi_filter_phone') == 1) {
        $comment_content = preg_replace('/\b(\+?[0-9]{1,3}[-.\s]?)?(\(?\d{2,4}\)?[-.\s]?\d{2,4}[-.\s]?\d{2,4})\b/i', '**********', $comment_content);
    }

    // E-Mail-Adressen filtern (alle möglichen Varianten, z.B. mit und ohne Subdomains)
    if (get_option('wp_multi_filter_email') == 1) {
        $comment_content = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/i', '**********', $comment_content);
    }

    // URLs filtern (verschiedene Varianten, z.B. mit oder ohne http://, www)
    if (get_option('wp_multi_filter_url') == 1) {
        $comment_content = preg_replace('/\b((https?:\/\/)?(www\.)?[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})(\/\S*)?\b/i', '**************', $comment_content);
    }

    // Schimpfwörter filtern (verschiedene Schreibweisen und Abkürzungen berücksichtigen)
    if (get_option('wp_multi_filter_swear') == 1) {
        $swear_words = ['arsch', 'schlechtwort1', 'schlechtwort2', 'examplebadword']; // Echte Wörter einfügen

        foreach ($swear_words as $word) {
            $comment_content = preg_replace('/\b' . preg_quote($word, '/') . '\b/i', str_repeat('*', strlen($word)), $comment_content);
            // Alternative Schreibweisen oder Abkürzungen können hier auch berücksichtigt werden, z.B.:
            $comment_content = preg_replace('/\b' . preg_quote($word, '/') . '[s]{0,2}\b/i', str_repeat('*', strlen($word)), $comment_content);  // Beispiel für 'arssch' oder 'arschs'
        }
    }

    // IP-Adressen filtern (alle gängigen Formate)
    if (get_option('wp_multi_filter_ip') == 1) {
        $comment_content = preg_replace('/\b(?:\d{1,3}\.){3}\d{1,3}\b/', '**********', $comment_content);
    }

    return $comment_content;
}
add_filter('pre_comment_content', 'wp_multi_filter_comment_content');


/*
* User Kommentar Blocken
*/


// Funktion zum Erstellen der Tabelle für gesperrte Benutzer
function wp_multi_create_blocked_users_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'blocked_users'; 

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        username varchar(100) DEFAULT '' NOT NULL,
        email varchar(100) DEFAULT '' NOT NULL,
        ip_address varchar(45) DEFAULT '' NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

function wp_multi_activate() {
    wp_multi_create_blocked_users_table();
}

register_activation_hook( __FILE__, 'wp_multi_activate' );

// Funktion zum Sperren von Benutzernamen, E-Mail-Adressen und IP-Adressen
function wp_multi_block_user($username = '', $email = '', $ip_address = '') {
    global $wpdb;

    // Sicherstellen, dass mindestens eines der Felder ausgefüllt wurde
    if (empty($username) && empty($email) && empty($ip_address)) {
        return;
    }

    // Eintrag in die Datenbank einfügen
    $wpdb->insert(
        $wpdb->prefix . 'blocked_users',
        [
            'username' => $username,
            'email' => $email,
            'ip_address' => $ip_address
        ]
    );
}

// Funktion zum Löschen eines gesperrten Benutzers
function wp_multi_delete_blocked_user($id) {
    global $wpdb;
    $wpdb->delete($wpdb->prefix . 'blocked_users', ['id' => $id]);
}

// Admin-Seite für die Verwaltung der gesperrten Benutzer
function wp_multi_blocked_users_page() {
    global $wpdb;

    // Benutzer sperren
    if (isset($_POST['block_username']) || isset($_POST['block_email']) || isset($_POST['block_ip'])) {
        $username = sanitize_text_field($_POST['block_username']);
        $email = sanitize_email($_POST['block_email']);
        $ip_address = sanitize_text_field($_POST['block_ip']);

        wp_multi_block_user($username, $email, $ip_address);
        echo '<div class="updated"><p>Benutzer wurde gesperrt!</p></div>';
    }

    // Suche
    $search_query = '';
    if (isset($_GET['search'])) {
        $search_query = sanitize_text_field($_GET['search']);
    }

    // Abfrage der gesperrten Benutzer
    $blocked_users = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}blocked_users WHERE username LIKE %s OR email LIKE %s OR ip_address LIKE %s",
        '%' . $search_query . '%', '%' . $search_query . '%', '%' . $search_query . '%'
    ));

    ?>
    <div class="wrap">
        <h2>Benutzer sperren</h2>

        <form method="post">
            <h3>Benutzernamen sperren</h3>
            <input type="text" name="block_username" class="regular-text" placeholder="Benutzername">
            <h3>E-Mail-Adresse sperren</h3>
            <input type="email" name="block_email" class="regular-text" placeholder="E-Mail-Adresse">
            <h3>IP-Adresse sperren</h3>
            <input type="text" name="block_ip" class="regular-text" placeholder="IP-Adresse">
            <br><br>
            <input type="submit" class="button button-primary" value="Benutzer sperren">
        </form>

        <h2>Gesperrte Benutzer</h2>
        <form method="get">
            <input type="hidden" name="page" value="wp-multi-blocked-users">
            <input type="text" name="search" value="<?php echo esc_attr($search_query); ?>" placeholder="Benutzername, E-Mail oder IP suchen" class="regular-text">
            <input type="submit" class="button" value="Suchen">
        </form>

        <table class="widefat">
            <thead>
                <tr>
                    <th>Benutzername</th>
                    <th>E-Mail-Adresse</th>
                    <th>IP-Adresse</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($blocked_users) : ?>
                    <?php foreach ($blocked_users as $user) : ?>
                        <tr>
                            <td><?php echo esc_html($user->username); ?></td>
                            <td><?php echo esc_html($user->email); ?></td>
                            <td><?php echo esc_html($user->ip_address); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=wp-multi-blocked-users&delete=' . $user->id); ?>" class="button button-secondary">Löschen</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4">Keine gesperrten Benutzer gefunden.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Löschen eines gesperrten Benutzers
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    
    // Sicherstellen, dass die ID gültig ist
    if ($user_id > 0) {
        wp_multi_delete_blocked_user($user_id);
        // Redirect zur Admin-Seite nach dem Löschen
        wp_redirect(admin_url('admin.php?page=wp-multi-blocked-users'));
        exit;
    }
}

// Kommentar auf gesperrte Benutzer überprüfen
function wp_multi_check_blocked_user($commentdata) {
    global $wpdb;

    $username = isset($commentdata['comment_author']) ? $commentdata['comment_author'] : '';
    $email = isset($commentdata['comment_author_email']) ? $commentdata['comment_author_email'] : '';
    $ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

    $blocked_user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}blocked_users WHERE username = %s OR email = %s OR ip_address = %s",
        $username, $email, $ip_address
    ));

    if ($blocked_user) {
        wp_die('Ihr Kommentar konnte nicht abgesendet werden, da Sie gesperrt sind. Bitte wenden Sie sich an den Support.');
    }

    return $commentdata;
}
add_filter('preprocess_comment', 'wp_multi_check_blocked_user');


/*
* custom shortcodes
*/


// Funktion, um die Datenbanktabelle für Shortcodes zu erstellen
function wp_multi_create_shortcodes_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'wp_multi_shortcodes'; // Name der Tabelle mit Präfix
    $charset_collate = $wpdb->get_charset_collate();

    // SQL-Abfrage zum Erstellen der Tabelle
    $sql = "CREATE TABLE $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        shortcode_name varchar(255) NOT NULL,
        shortcode_content text NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY shortcode_name (shortcode_name)
    ) $charset_collate;";

    // Datenbank abfragen und ausführen
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'wp_multi_create_shortcodes_table' );

// Menü zum Verwalten von Shortcodes im Admin-Bereich hinzufügen
function wp_multi_add_shortcode_menu() {
    add_menu_page(
        'Custom Shortcodes',         // Seitentitel
        'Custom Shortcodes',         // Menü-Titel
        'manage_options',            // Berechtigungen
        'wp_multi_shortcodes',       // Menü-Slug
        'wp_multi_shortcode_page',   // Callback-Funktion zum Anzeigen der Seite
        'dashicons-editor-code',     // Symbol
        6                            // Position im Menü
    );
}
add_action('admin_menu', 'wp_multi_add_shortcode_menu');

// Callback-Funktion für das Shortcode-Verwaltungs-Interface
function wp_multi_shortcode_page() {
    global $wpdb;

    $message = ''; // Variable für benutzerdefinierte Nachrichten

    // Verarbeite das Speichern von Shortcodes
    if (isset($_POST['wp_multi_shortcode_name']) && isset($_POST['wp_multi_shortcode_content'])) {
        // Hole die übermittelten Shortcodes
        $name = sanitize_text_field($_POST['wp_multi_shortcode_name']);
        $content = sanitize_textarea_field($_POST['wp_multi_shortcode_content']);
        
        // Prüfen, ob der Shortcode bereits existiert
        $existing_shortcode = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}wp_multi_shortcodes WHERE shortcode_name = %s", $name));

        if ($existing_shortcode) {
            // Aktualisiere den Shortcode, falls er bereits existiert
            $wpdb->update(
                $wpdb->prefix . 'wp_multi_shortcodes',
                ['shortcode_content' => $content],
                ['shortcode_name' => $name]
            );
            $message = 'Shortcode wurde aktualisiert!';
        } else {
            // Andernfalls einen neuen Shortcode einfügen
            $wpdb->insert(
                $wpdb->prefix . 'wp_multi_shortcodes',
                [
                    'shortcode_name' => $name,
                    'shortcode_content' => $content
                ]
            );
            $message = 'Shortcode wurde hinzugefügt!';
        }
    }

    // Shortcode löschen
    if (isset($_GET['delete_shortcode']) && !empty($_GET['delete_shortcode'])) {
        $delete_id = intval($_GET['delete_shortcode']);
        $wpdb->delete(
            $wpdb->prefix . 'wp_multi_shortcodes',
            ['id' => $delete_id]
        );
        $message = 'Shortcode wurde gelöscht!';
    }

    // Holen der gespeicherten Shortcodes aus der Datenbank
    $custom_shortcodes = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wp_multi_shortcodes");

    // HTML für die Seite
    ?>
    <div class="wrap wp-multi-admin-page">
        <h1><?php _e('WP Multi - Shortcodes Einstellungen', 'wp-multi'); ?></h1>

        <!-- Gemeinsame Box für Logo und Banner -->
        <div class="wp-multi-header-box">
            <div class="wp-multi-banner">
                <img src="https://m-viper.de/img/logo.png" alt="M_Viper Logo" class="wp-multi-logo" />
                <h1>Custom Shortcodes verwalten</h1>
            </div>
        </div>

        <!-- Benachrichtigungen und Standard-WordPress-Nachrichten -->
        <?php if (!empty($message)) : ?>
            <div class="wp-multi-custom-message">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>

        <form method="post">
            <table class="form-table wp-multi-table">
                <tr>
                    <th><label for="wp_multi_shortcode_name">Name des Shortcodes</label></th>
                    <td><input type="text" name="wp_multi_shortcode_name" id="wp_multi_shortcode_name" class="regular-text" required /></td>
                </tr>
                <tr>
                    <th><label for="wp_multi_shortcode_content">Inhalt des Shortcodes</label></th>
                    <td><textarea name="wp_multi_shortcode_content" id="wp_multi_shortcode_content" class="large-text" rows="5" required></textarea></td>
                </tr>
            </table>
            <?php submit_button('Shortcode speichern'); ?>
        </form>

        <h2>Verfügbare Shortcodes</h2>
        <ul class="wp-multi-shortcodes-list">
            <?php
            if (!empty($custom_shortcodes)) {
                foreach ($custom_shortcodes as $shortcode) {
                    echo '<li><strong>' . esc_html($shortcode->shortcode_name) . ':</strong> ' . esc_html($shortcode->shortcode_content) . ' 
                        <a href="' . esc_url(admin_url('admin.php?page=wp_multi_shortcodes&delete_shortcode=' . $shortcode->id)) . '" class="wp-multi-delete-button" onclick="return confirm(\'Möchten Sie diesen Shortcode wirklich löschen?\');">Löschen</a></li>';
                }
            } else {
                echo '<li>Keine benutzerdefinierten Shortcodes gefunden.</li>';
            }
            ?>
        </ul>
    </div>

    <style>
        /* Container für Logo und Banner in einer Box */
        .wp-multi-header-box {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            background-color: #f1f1f1;
        }

        .wp-multi-logo {
            max-height: 80px;
        }

        /* Banner-Stil */
        .wp-multi-banner {
            background-color: #0073aa; /* Blaues Banner */
            padding: 10px;
            text-align: center;
            margin-top: 20px;
        }

        .wp-multi-banner h1 {
            font-size: 30px;
            margin: 0;
            font-weight: 600;
        }

        /* Benutzerdefinierte Nachrichtenbox */
        .wp-multi-custom-message {
            background-color: #f1f1f1;
            padding: 15px;
            border-left: 4px solid #0073aa;
            margin: 20px 0;
            font-size: 16px;
        }

        /* Anpassungen für die Formularfelder */
        .form-table {
            margin-top: 30px;
        }

        .form-table th {
            width: 220px;
            font-weight: bold;
        }

        .form-table td {
            width: auto;
        }

        .wp-multi-table input, .wp-multi-table textarea {
            width: 100%;
            border-radius: 5px;
            padding: 10px;
            border: 1px solid #ccc;
        }

        .wp-multi-table input:focus, .wp-multi-table textarea:focus {
            border-color: #0073aa;
        }

        .wp-multi-shortcodes-list {
            list-style-type: none;
            margin-top: 30px;
            padding-left: 0;
        }

        .wp-multi-shortcodes-list li {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .wp-multi-shortcodes-list li:hover {
            background-color: #f1f1f1;
        }

        /* Löschen-Button Stil */
        .wp-multi-delete-button {
            color: #ff0000;
            margin-left: 10px;
            text-decoration: none;
        }

        .wp-multi-delete-button:hover {
            text-decoration: underline;
        }

        /* Anpassen des Buttons */
        .button-primary {
            background-color: #0073aa;
            border-color: #0073aa;
        }

        .button-primary:hover {
            background-color: #005f8d;
            border-color: #005f8d;
        }
    </style>

<?php
}

// Shortcode-Verwaltung: Ermöglicht Benutzern das Erstellen eigener Shortcodes
function wp_multi_register_custom_shortcodes() {
    global $wpdb;

    // Holen der gespeicherten Shortcodes aus der Datenbank
    $custom_shortcodes = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wp_multi_shortcodes");

    // Wenn keine benutzerdefinierten Shortcodes vorhanden sind, abbrechen
    if (empty($custom_shortcodes)) {
        return;
    }

    // Definiere die Shortcodes in WordPress
    foreach ($custom_shortcodes as $shortcode) {
        add_shortcode($shortcode->shortcode_name, function() use ($shortcode) {
            return $shortcode->shortcode_content;
        });
    }
}
add_action('init', 'wp_multi_register_custom_shortcodes');

// Inhalt der Meta-Box anzeigen
function wp_multi_render_shortcode_meta_box($post) {
    global $wpdb;

    // Alle gespeicherten Shortcodes aus der Datenbank holen
    $shortcodes = $wpdb->get_results("SELECT shortcode_name FROM {$wpdb->prefix}wp_multi_shortcodes");

    if (!empty($shortcodes)) {
        echo '<select id="wp_multi_shortcode_dropdown">';
        echo '<option value="">-- Shortcode auswählen --</option>';

        foreach ($shortcodes as $shortcode) {
            echo '<option value="' . esc_attr($shortcode->shortcode_name) . '">' . esc_html($shortcode->shortcode_name) . '</option>';
        }

        echo '</select>';
        echo '<button type="button" class="button button-primary" id="wp_multi_insert_shortcode">Einfügen</button>';
    } else {
        echo '<p>Keine Shortcodes vorhanden.</p>';
    }
}

    // JavaScript für Meta-Box einbinden
function wp_multi_enqueue_admin_scripts($hook) {
    if ('post.php' === $hook || 'post-new.php' === $hook) {
        wp_enqueue_script('wp-multi-shortcode', plugin_dir_url(__FILE__) . 'js/editor-shortcode.js', array('jquery'), null, true);
    }
}
add_action('admin_enqueue_scripts', 'wp_multi_enqueue_admin_scripts');

// Funktion zum Registrieren des TinyMCE Plugins
function wp_multi_add_shortcode_button() {
    add_filter('mce_external_plugins', 'wp_multi_register_tinymce_plugin');
    add_filter('mce_buttons', 'wp_multi_add_tinymce_button');
}
add_action('admin_head', 'wp_multi_add_shortcode_button');

// Plugin für TinyMCE registrieren (angepasster Pfad zum JS-File)
function wp_multi_register_tinymce_plugin($plugins) {
    $plugins['wp_multi_shortcodes'] = plugin_dir_url(__FILE__) . 'js/tinymce-shortcodes.js';
    return $plugins;
}

// Button zur TinyMCE Toolbar hinzufügen
function wp_multi_add_tinymce_button($buttons) {
    array_push($buttons, 'wp_multi_shortcodes');
    return $buttons;
}

// Shortcodes aus der Datenbank für das JavaScript bereitstellen
function wp_multi_localize_shortcodes() {
    global $wpdb;
    $shortcodes = $wpdb->get_results("SELECT shortcode_name FROM {$wpdb->prefix}wp_multi_shortcodes", ARRAY_A);
    
    // Shortcodes als JSON an das JS-File übergeben
    wp_enqueue_script('wp-multi-tinymce', plugin_dir_url(__FILE__) . 'js/tinymce-shortcodes.js', array('jquery'), null, true);
    wp_localize_script('wp-multi-tinymce', 'wpMultiShortcodes', $shortcodes);
}
add_action('admin_enqueue_scripts', 'wp_multi_localize_shortcodes');


/*
* Update Admin-Dashboard widget
*/


// Widget zum Admin-Dashboard hinzufügen
function wp_multi_update_dashboard_widget() {
    wp_add_dashboard_widget(
        'wp_multi_update_widget',        // Widget-ID
        'Verfügbare Updates für WP Multi', // Widget-Titel
        'wp_multi_update_dashboard_widget_content' // Callback-Funktion
    );
}
add_action('wp_dashboard_setup', 'wp_multi_update_dashboard_widget');

// Cron-Job registrieren
function wp_multi_update_schedule_check() {
    if (!wp_next_scheduled('wp_multi_update_check_event')) {
        // Registriere den Cron-Job, der alle 3 Minuten ausgeführt wird
        wp_schedule_event(time(), 'three_minutes', 'wp_multi_update_check_event');
    }
}
add_action('wp', 'wp_multi_update_schedule_check');

// Cron-Job für Update-Überprüfung
function wp_multi_update_check() {
    // Gitea API-URL
    $api_url = 'https://git.viper.ipv64.net/api/v1/repos/M_Viper/wp-multi/releases';
    
    // Die Version des Plugins aus den Metadaten der Plugin-Datei holen
    $plugin_data = get_plugin_data( __FILE__ );
    $installed_version = $plugin_data['Version']; // Die installierte Version aus den Plugin-Metadaten

    // Hole die Einstellung, ob PreRelease-Versionen angezeigt werden sollen
    $show_prereleases = get_option('wp_multi_update_show_prereleases', false);

    // Gitea API-Anfrage für die neuesten Releases ohne Authentifizierung
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return; // Fehler nicht weitergeben, aber nichts tun
    }

    // API-Antwort verarbeiten
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Finde das neueste, gültige Release (nicht PreRelease, falls deaktiviert)
    $valid_release = null;
    foreach ($data as $release) {
        // Wenn PreRelease deaktiviert ist, überspringe alle PreRelease-Versionen
        if (!$show_prereleases && isset($release['prerelease']) && $release['prerelease']) {
            continue;
        }

        if (!empty($release['tag_name'])) {
            $valid_release = $release;
            break; // Nur das erste gültige Release verwenden
        }
    }

    if ($valid_release) {
        $latest_version = $valid_release['tag_name'];
        $release_notes = isset($valid_release['body']) ? $valid_release['body'] : '';
        $is_prerelease = isset($valid_release['prerelease']) && $valid_release['prerelease'];

        // Speichern von Release-Daten
        update_option('wp_multi_update_latest_version', $latest_version);
        update_option('wp_multi_update_release_notes', $release_notes);
        update_option('wp_multi_update_is_prerelease', $is_prerelease);
    }
}
add_action('wp_multi_update_check_event', 'wp_multi_update_check');

// Callback-Funktion für das Widget
function wp_multi_update_dashboard_widget_content() {
    // Gitea API-URL
    $api_url = 'https://git.viper.ipv64.net/api/v1/repos/M_Viper/wp-multi/releases';
    
    // Die Version des Plugins aus den Metadaten der Plugin-Datei holen
    $plugin_data = get_plugin_data( __FILE__ );
    $installed_version = $plugin_data['Version']; // Die installierte Version aus den Plugin-Metadaten

    // Hole die Einstellung, ob PreRelease-Versionen angezeigt werden sollen
    $show_prereleases = get_option('wp_multi_update_show_prereleases', false);

    // Gitea API-Anfrage für die neuesten Releases ohne Authentifizierung
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        echo 'Fehler beim Abrufen der Versionsinformationen von Gitea.';
        return;
    }

    // API-Antwort verarbeiten
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Finde das neueste, gültige Release (nicht PreRelease, falls deaktiviert)
    $valid_release = null;
    foreach ($data as $release) {
        // Wenn PreRelease deaktiviert ist, überspringe alle PreRelease-Versionen
        if (!$show_prereleases && isset($release['prerelease']) && $release['prerelease']) {
            continue;
        }

        if (!empty($release['tag_name'])) {
            $valid_release = $release;
            break; // Nur das erste gültige Release verwenden
        }
    }

    if ($valid_release) {
        $latest_version = $valid_release['tag_name'];
        $release_notes = isset($valid_release['body']) ? $valid_release['body'] : '';
        $is_prerelease = isset($valid_release['prerelease']) && $valid_release['prerelease'];

        // Anzeige der Versionen und Text basierend auf PreRelease
        if (version_compare($installed_version, $latest_version, '>=')) {
            // Wenn die installierte Version gleich oder neuer ist als die Version in Gitea
            echo '<p style="color: green;">Ihre Version ist aktuell. Version ' . $installed_version . ' ist die neueste Version.</p>';
        } else {
            // Wenn die installierte Version älter ist als die Version in Gitea
            echo '<p style="color: red;">Es ist eine neue Version von WP Multi verfügbar! <strong>Version ' . $latest_version . '</strong> ist jetzt verfügbar.</p>';
            echo '<p>Aktuell installierte Version: <strong>' . $installed_version . '</strong></p>';
            echo '<p>Neue Version auf Gitea: <strong>' . $latest_version . '</strong></p>';

            // PreRelease in blauer Schrift anzeigen, wenn erlaubt und das Update ein PreRelease ist
            if ($is_prerelease && $show_prereleases) {
                echo '<p style="color: blue;">Dieses Update ist ein PreRelease.</p>';
            }

            // Verfassen-Text anzeigen, falls verfügbar
            if (!empty($release_notes)) {
                echo '<p><strong>Information zum Update:</strong></p>';
                echo '<p>' . nl2br(esc_html($release_notes)) . '</p>';
            }

            // Button-Text anpassen je nachdem, ob es ein PreRelease ist
            $button_text = $is_prerelease ? 'PreRelease herunterladen' : 'Update herunterladen';
            $download_url = $valid_release['assets'][0]['browser_download_url'];
            echo '<p><a href="' . esc_url($download_url) . '" class="button button-primary" target="_blank">' . esc_html($button_text) . '</a></p>';
        }
    } else {
        echo 'Fehler beim Abrufen der neuesten Version von Gitea.';
    }
}

// Benutzerdefinierte Intervalle für Cron hinzufügen
function wp_multi_update_custom_intervals($schedules) {
    // 3 Minuten Intervall hinzufügen
    $schedules['three_minutes'] = array(
        'interval' => 180,  // Alle 3 Minuten
        'display'  => __('Alle 3 Minuten'),
    );
    return $schedules;
}
add_filter('cron_schedules', 'wp_multi_update_custom_intervals');

// PreRelease Option in den Einstellungen hinzufügen
function wp_multi_update_register_settings() {
    add_option('wp_multi_update_show_prereleases', false);
    register_setting('general', 'wp_multi_update_show_prereleases');
    add_settings_field('wp_multi_update_show_prereleases', 'Pre-Release-Versionen anzeigen', 'wp_multi_update_show_prereleases_field', 'general');
}
add_action('admin_init', 'wp_multi_update_register_settings');

// Einstellung für PreRelease-Versionen
function wp_multi_update_show_prereleases_field() {
    $value = get_option('wp_multi_update_show_prereleases', false);
    echo '<input type="checkbox" id="wp_multi_update_show_prereleases" name="wp_multi_update_show_prereleases" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description" style="color: red;">Aktiviere diese Option, um Pre-Release-Versionen anzuzeigen, die noch nicht vollständig veröffentlicht wurden. Deaktiviere die Option, um nur stabile Versionen anzuzeigen.</p>';
}


/*
* Notify Seite Discord & Telegram
*/


// Übergeordnetes Menü "Notify" erstellen
function wp_multi_menu() {
    // Menüpunkt für "Notify"
    add_menu_page(
        'Notify',           
        'Notify',           
        'manage_options',   
        'wp-multi-notify',  
        'wp_multi_notify_page',  
        'dashicons-bell',   
        100                 
    );

    // Untermenüpunkt für DC-Notify
    add_submenu_page(
        'wp-multi-notify',             
        'DC-Notify Einstellungen',     
        'DC-Notify',                   
        'manage_options',              
        'wp-multi',                    
        'wp_multi_settings_page'       
    );

    // Untermenüpunkt für TG-Notify
    add_submenu_page(
        'wp-multi-notify',             
        'TG-Notify Einstellungen',     
        'TG-Notify',                   
        'manage_options',              
        'tg-notify',                   
        'tg_notify_page'               
    );
}
add_action('admin_menu', 'wp_multi_menu');

// Callback-Funktion für die Hauptseite Notify
function wp_multi_notify_page() {
    ?>
    <div class="wrap wp-multi-admin-page">
        <h1><?php _e('WP Multi - Notify Einstellungen', 'wp-multi'); ?></h1>

        <!-- Blaues Banner hinter dem Logo -->
        <div class="wp-multi-banner">
            <img src="https://m-viper.de/img/logo.png" alt="M_Viper Logo" class="wp-multi-logo-image" />
        </div>

        <div class="wp-multi-settings-header">
            <h2><?php _e('Einrichtung von Discord und Telegram Benachrichtigungen', 'wp-multi'); ?></h2>
            <p><?php _e('Um Benachrichtigungen zu Discord oder Telegram zu senden, müssen Sie zuerst die entsprechenden Webhooks und Bots einrichten. Diese Seite gibt Ihnen nur eine Übersicht und Anleitung, wie dies zu tun ist.', 'wp-multi'); ?></p>

            <h3><?php _e('Discord', 'wp-multi'); ?></h3>
            <p><?php _e('Erstellen Sie einen Webhook in einem Discord-Kanal und fügen Sie die Webhook-URL in die entsprechenden Felder ein.', 'wp-multi'); ?></p>
            <ol>
                <li><?php _e('Gehen Sie zu Ihrem Discord-Server und öffnen Sie die Server-Einstellungen.', 'wp-multi'); ?></li>
                <li><?php _e('Wählen Sie "Integrationen" und dann "Webhook erstellen".', 'wp-multi'); ?></li>
                <li><?php _e('Kopieren Sie die Webhook-URL und fügen Sie diese in das entsprechende Feld auf dieser Seite ein.', 'wp-multi'); ?></li>
            </ol>

            <h3><?php _e('Telegram', 'wp-multi'); ?></h3>
            <p><?php _e('Erstellen Sie einen Bot über BotFather und fügen Sie den Token und die Kanal-ID in die entsprechenden Felder ein.', 'wp-multi'); ?></p>
            <ol>
                <li><?php _e('Öffnen Sie Telegram und suchen Sie nach "BotFather".', 'wp-multi'); ?></li>
                <li><?php _e('Geben Sie /newbot ein, um einen neuen Bot zu erstellen.', 'wp-multi'); ?></li>
                <li><?php _e('Speichern Sie den Bot-Token und die Kanal-ID und tragen Sie diese in die Felder oben ein.', 'wp-multi'); ?></li>
            </ol>
        </div>
    </div>
    <?php
}


/*
*Discord Notify
*/


// Callback-Funktion für die DC-Notify Seite
function wp_multi_dc_notify_page() {
    ?>
    <div class="wrap wp-multi-admin-page">
        <h1><?php _e('DC-Notify Einstellungen', 'wp-multi'); ?></h1>
        <!-- Blaues Banner hinter dem Logo -->
        <div class="wp-multi-banner">
            <img src="https://m-viper.de/img/logo.png" alt="M_Viper Logo" class="wp-multi-logo-image" />
        </div>
        <p><?php _e('Hier können Sie Discord-Benachrichtigungen konfigurieren. Weitere Anweisungen finden Sie unten.', 'wp-multi'); ?></p>
        <h2><?php _e('Discord Setup Anleitung', 'wp-multi'); ?></h2>
        <ol>
            <li><?php _e('Gehen Sie zu Ihrem Discord-Server und öffnen Sie die Server-Einstellungen.', 'wp-multi'); ?></li>
            <li><?php _e('Wählen Sie "Integrationen" und dann "Webhook erstellen".', 'wp-multi'); ?></li>
            <li><?php _e('Kopieren Sie die Webhook-URL und fügen Sie diese in das entsprechende Feld ein.', 'wp-multi'); ?></li>
        </ol>
    </div>
    <?php
}

// Callback-Funktion für die TG-Notify Seite
function wp_multi_tg_notify_page() {
    ?>
    <div class="wrap wp-multi-admin-page">
        <h1><?php _e('TG-Notify Einstellungen', 'wp-multi'); ?></h1>
        <!-- Blaues Banner hinter dem Logo -->
        <div class="wp-multi-banner">
            <img src="https://m-viper.de/img/logo.png" alt="M_Viper Logo" class="wp-multi-logo-image" />
        </div>
        <p><?php _e('Hier können Sie Telegram-Benachrichtigungen konfigurieren. Weitere Anweisungen finden Sie unten.', 'wp-multi'); ?></p>
        <h2><?php _e('Telegram Setup Anleitung', 'wp-multi'); ?></h2>
        <ol>
            <li><?php _e('Öffnen Sie Telegram und suchen Sie nach "BotFather".', 'wp-multi'); ?></li>
            <li><?php _e('Geben Sie /newbot ein, um einen neuen Bot zu erstellen.', 'wp-multi'); ?></li>
            <li><?php _e('Speichern Sie den Bot-Token und die Kanal-ID und tragen Sie diese in die Felder oben ein.', 'wp-multi'); ?></li>
        </ol>
    </div>
    <?php
}

// CSS für das Plugin
function wp_multi_admin_styles() {
    echo '
    <style>
        .wp-multi-admin-page {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }

        .wp-multi-banner {
            background-color: #0073aa; /* Blaues Banner */
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
        }

        .wp-multi-logo-image {
            width: 200px;
            height: auto;
            display: inline-block;
        }

        .wp-multi-settings-header h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .wp-multi-settings-header ol {
            margin-left: 20px;
        }

        .wp-multi-settings-header ol li {
            margin-bottom: 8px;
        }
    </style>
    ';
}
add_action('admin_head', 'wp_multi_admin_styles');

// Einstellungsseite für Discord Webhook
function wp_multi_settings_page() {
    ?>
    <div class="wrap">
        <!-- Header mit Banner und Logo -->
        <div class="wp-multi-settings-header">
            <div class="wp-multi-logo">
                <img src="https://m-viper.de/img/logo.png" alt="Logo" />
            </div>
        </div>

        <form method="post" action="options.php">
            <?php
            settings_fields('wp_multi_options_group');
            do_settings_sections('wp-multi');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Discord Webhook URL</th>
                    <td>
                        <input type="text" name="wp_multi_discord_webhook" value="<?php echo esc_attr(get_option('wp_multi_discord_webhook')); ?>" size="50">
                        <p class="description">Geben Sie die Webhook-URL für Discord ein, um Benachrichtigungen zu senden.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Bot Name</th>
                    <td>
                        <input type="text" name="wp_multi_discord_bot_name" value="<?php echo esc_attr(get_option('wp_multi_discord_bot_name', 'WP Multi Bot')); ?>" size="50">
                        <p class="description">Geben Sie den Namen des Bots ein, der in Discord angezeigt werden soll.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Discord Nachricht (Vorlage)</th>
                    <td>
                        <textarea name="wp_multi_discord_message_template" rows="4" cols="50"><?php echo esc_textarea(get_option('wp_multi_discord_message_template', 'Beitrag "{post_title}" von {post_author} | Link: {post_url}')); ?></textarea>
                        <p class="description">Passen Sie die Nachricht an, die an Discord gesendet wird. Verwenden Sie Platzhalter wie {post_title}, {post_author}, und {post_url}.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Discord Benutzerrollen ID (für Ping)</th>
                    <td>
                        <input type="text" name="wp_multi_discord_role_id" value="<?php echo esc_attr(get_option('wp_multi_discord_role_id')); ?>" size="50">
                        <p class="description">Geben Sie die ID der Discord-Benutzerrolle ein, die gepingt werden soll (z. B. @everyone).</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Discord Avatar-URL</th>
                    <td>
                        <input type="text" name="wp_multi_discord_avatar_url" value="<?php echo esc_attr(get_option('wp_multi_discord_avatar_url')); ?>" size="50">
                        <p class="description">Geben Sie die URL des Avatar-Bildes ein, das in den Discord-Nachrichten angezeigt werden soll.</p>
                    </td>
                </tr>

                <!-- Neues Feld für Footer-Text (Custom Text 2) -->
                <tr>
                    <th scope="row">Footer Text (Custom Text 2)</th>
                    <td>
                        <input type="text" name="wp_multi_discord_footer_text" value="<?php echo esc_attr(get_option('wp_multi_discord_footer_text')); ?>" size="50">
                        <p class="description">Geben Sie den benutzerdefinierten Text ein, der am Ende der Nachricht angezeigt wird (z. B. "Powered by WP Multi"). Sie können auch Platzhalter wie {post_title}, {post_author} und {post_url} verwenden.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <style>
        /* CSS nur für die Einstellungsseite */
        .wp-multi-settings-header {
            background-color: #0073aa; 
            padding: 50px 20px; 
            text-align: center;
            position: relative;
            margin-bottom: 30px; 
        }

        .wp-multi-settings-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #0073aa; 
            z-index: -1;
        }

        .wp-multi-logo img {
            max-width: 200px;
            display: block;
            margin: 0 auto;
        }

        .wrap .form-table {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .wrap .form-table th {
            font-weight: bold;
        }

        .wrap .form-table td {
            padding: 10px;
        }

        .wrap .form-table input[type="text"],
        .wrap .form-table textarea {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .wrap .form-table input[type="text"]:focus,
        .wrap .form-table textarea:focus {
            border-color: #1e3a8a;
        }

        .wrap .description {
            font-style: italic;
            color: #666;
        }

        .wrap .button-primary {
            background-color: #1e3a8a;
            border-color: #1e3a8a;
            box-shadow: none;
        }

        .wrap .button-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }
    </style>
    <?php
}

// Funktion, um die Einstellungen zu registrieren
function wp_multi_register_settings() {
    register_setting('wp_multi_options_group', 'wp_multi_discord_webhook');
    register_setting('wp_multi_options_group', 'wp_multi_discord_bot_name');
    register_setting('wp_multi_options_group', 'wp_multi_discord_message_template');
    register_setting('wp_multi_options_group', 'wp_multi_discord_role_id');
    register_setting('wp_multi_options_group', 'wp_multi_discord_avatar_url');
    register_setting('wp_multi_options_group', 'wp_multi_discord_footer_text'); 
}
add_action('admin_init', 'wp_multi_register_settings');

// Funktion, um die Discord-Benachrichtigung zu senden
function wp_multi_send_discord_notification($ID, $post) {
    // Überprüfen, ob die Checkbox aktiviert ist
    $send_notification = get_post_meta($ID, '_wp_multi_checkbox', true);
    if ($send_notification !== '1') {
        return;
    }

    // Webhook-URL aus den Optionen holen
    $webhook_url = get_option('wp_multi_discord_webhook');
    if (empty($webhook_url)) {
        return;
    }

    // Bot-Name und Avatar-URL aus den Optionen holen
    $bot_name = get_option('wp_multi_discord_bot_name', 'WP Multi Bot');
    $avatar_url = get_option('wp_multi_discord_avatar_url');

    // Post-Daten abrufen
    $post_title = esc_html(get_the_title($ID));
    $post_url = esc_url(get_permalink($ID));
    $post_author = esc_html(get_the_author_meta('display_name', $post->post_author));

    // Textvorschau (die ersten 5 Zeilen des Beitrags)
    $content = get_post_field('post_content', $ID);
    $excerpt = wp_trim_words($content, 60, '...');  

    // Benutzerrolle anpingen (optional)
    $role_id = get_option('wp_multi_discord_role_id');
    $mention_role = (!empty($role_id) && is_numeric($role_id)) ? "<@&" . esc_attr($role_id) . ">" : '';

    // Footer Text (Custom Text 2) aus den Optionen
    $footer_text = get_option('wp_multi_discord_footer_text');
    $footer = !empty($footer_text) ? str_replace(
        ['{post_title}', '{post_author}', '{post_url}'],
        [$post_title, $post_author, $post_url],
        $footer_text
    ) : '';

    // Nachrichtenvorlage zusammenstellen
    $message_template = get_option('wp_multi_discord_message_template', 'Beitrag "{post_title}" von {post_author} | Link: {post_url}');
    $message = str_replace(
        ['{post_title}', '{post_author}', '{post_url}'],
        [$post_title, $post_author, $post_url],
        $message_template
    );

    // Nachricht aufbauen
    $message .= "\n\n" . __('') . "\n" . $excerpt;

    // Fügt eine zusätzliche Zeile Abstand ein, bevor der Footer-Text erscheint
    $message .= "\n\n" . $footer;

    // Discord Webhook Daten vorbereiten
    $data = json_encode([
        'username' => $bot_name,
        'avatar_url' => $avatar_url,
        'content' => $mention_role . "\n" . $message
    ]);

    // Nachricht an Discord senden
    $response = wp_remote_post($webhook_url, [
        'method'    => 'POST',
        'body'      => $data,
        'headers'   => [
            'Content-Type' => 'application/json'
        ]
    ]);

    // Prüfen, ob die Nachricht erfolgreich gesendet wurde
    if (!is_wp_error($response)) {
        // Erhöhe den Discord-Nachrichtenzähler
        wp_multi_increment_discord_message_count();
    }
}

// Funktion zum Erhöhen des Discord-Nachrichtenzählers
function wp_multi_increment_discord_message_count() {
    $current_count = get_option('wp_multi_discord_message_count', 0);
    update_option('wp_multi_discord_message_count', $current_count + 1);
}

add_action('publish_post', 'wp_multi_send_discord_notification', 10, 2);

 

// Funktion, um die Checkbox in der Sidebar des Beitrag Editors hinzuzufügen
function wp_multi_add_checkbox_to_sidebar() {
    global $post;

    // Nonce-Feld für Sicherheitsüberprüfung
    wp_nonce_field('wp_multi_checkbox_nonce', 'wp_multi_checkbox_nonce_field');

    // Immer aktivieren (setze den Wert der Checkbox immer auf '1')
    $value = '1'; 
    
    // Checkbox im Sidebar Bereich (Veröffentlichen) anzeigen
    ?>
    <div class="misc-pub-section">
        <label for="wp_multi_checkbox">
            <input type="checkbox" name="wp_multi_checkbox" id="wp_multi_checkbox" value="1" <?php checked($value, '1'); ?>>
            Discord Benachrichtigung senden
        </label>
    </div>
    <?php
}
add_action('post_submitbox_misc_actions', 'wp_multi_add_checkbox_to_sidebar');

// Funktion, um den Wert der Checkbox zu speichern
function wp_multi_save_checkbox_value($post_id) {
    // Sicherheitsprüfung für das Nonce-Feld
    if (!isset($_POST['wp_multi_checkbox_nonce_field']) || !wp_verify_nonce($_POST['wp_multi_checkbox_nonce_field'], 'wp_multi_checkbox_nonce')) {
        return;
    }

    // Wenn die Checkbox aktiviert ist, den Wert speichern
    if (isset($_POST['wp_multi_checkbox']) && $_POST['wp_multi_checkbox'] === '1') {
        update_post_meta($post_id, '_wp_multi_checkbox', '1');
    } else {
        delete_post_meta($post_id, '_wp_multi_checkbox');
    }
}
add_action('save_post', 'wp_multi_save_checkbox_value');


$response = wp_remote_post($webhook_url, [
    'body'    => json_encode($message),
    'headers' => ['Content-Type' => 'application/json'],
    'method'  => 'POST'
]);

if (is_wp_error($response)) {
    $error_message = $response->get_error_message();
    error_log('Discord Webhook Fehler: ' . $error_message); 
} else {
    error_log('Webhook gesendet: ' . print_r($response, true)); 
}


/*
* Telegram Notify
*/


// Admin-Seiten Callback
function tg_notify_page() {
    ?>
    <div class="wrap tg-notify-settings">
        <h1><?php _e('TG-Notify Einstellungen', 'wp-stat-notice'); ?></h1>
        <form method="post" action="options.php" class="tg-notify-form">
            <?php
            settings_fields('tg_notify_options_group');
            do_settings_sections('tg-notify');
            submit_button('Speichern', 'primary', 'submit', true);
            ?>
        </form>
    </div>
    <style>
        .tg-notify-settings {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .tg-notify-settings h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }
        .tg-notify-form input,
        .tg-notify-form textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .tg-notify-form textarea {
            resize: vertical;
        }
        .tg-notify-form label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        .tg-notify-form p {
            font-size: 12px;
            color: #666;
        }
        .tg-notify-form input[type="checkbox"] {
            margin-right: 8px;
        }
        .tg-notify-settings .submit {
            background-color: #0073aa;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        .tg-notify-settings .submit:hover {
            background-color: #005c8a;
        }
    </style>
    <?php
}

// Einstellungen registrieren
function tg_notify_register_settings() {
    register_setting('tg_notify_options_group', 'tg_notify_bot_name');
    register_setting('tg_notify_options_group', 'tg_notify_bot_token');
    register_setting('tg_notify_options_group', 'tg_notify_chat_ids');
    register_setting('tg_notify_options_group', 'tg_notify_custom_message');

    add_settings_section('tg_notify_main_section', __('Telegram Einstellungen', 'wp-stat-notice'), null, 'tg-notify');

    add_settings_field('tg_notify_bot_name', __('Bot Name', 'wp-stat-notice'), 'tg_notify_bot_name_callback', 'tg-notify', 'tg_notify_main_section');
    add_settings_field('tg_notify_bot_token', __('Bot Token', 'wp-stat-notice'), 'tg_notify_bot_token_callback', 'tg-notify', 'tg_notify_main_section');
    add_settings_field('tg_notify_chat_ids', __('Kanal IDs', 'wp-stat-notice'), 'tg_notify_chat_ids_callback', 'tg-notify', 'tg_notify_main_section');
    add_settings_field('tg_notify_custom_message', __('Custom Nachricht', 'wp-stat-notice'), 'tg_notify_custom_message_callback', 'tg-notify', 'tg_notify_main_section');
}
add_action('admin_init', 'tg_notify_register_settings');

// Callback-Funktionen
function tg_notify_bot_name_callback() {
    $value = get_option('tg_notify_bot_name', '');
    echo '<input type="text" name="tg_notify_bot_name" value="' . esc_attr($value) . '" class="regular-text">';
}
function tg_notify_bot_token_callback() {
    $value = get_option('tg_notify_bot_token', '');
    echo '<input type="text" name="tg_notify_bot_token" value="' . esc_attr($value) . '" class="regular-text">';
}
function tg_notify_chat_ids_callback() {
    $value = get_option('tg_notify_chat_ids', '');
    echo '<textarea name="tg_notify_chat_ids" class="large-text code" rows="3">' . esc_textarea($value) . '</textarea>';
	echo '<p>Kanal ohne Thema: -1001234567890</p>';
	echo '<p>Kanal mit Thema: -1001234567890_123</p>';
}
function tg_notify_custom_message_callback() {
    $value = get_option('tg_notify_custom_message', '');
    echo '<textarea name="tg_notify_custom_message" class="large-text code" rows="5">' . esc_textarea($value) . '</textarea>';
    echo '<p>Verfügbare Variablen: {title}, {author}, {link}</p>';
}

// Checkbox beim Beitrag hinzufügen
function tg_notify_add_meta_box() {
    add_meta_box(
        'tg_notify_meta_box',
        __('Telegram Benachrichtigung', 'wp-stat-notice'),
        'tg_notify_meta_box_callback',
        'post',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'tg_notify_add_meta_box');

function tg_notify_meta_box_callback($post) {
    $value = get_post_meta($post->ID, '_tg_notify_send', true);

    // Standardmäßig auf 1 setzen, wenn der Beitrag neu ist
    if (empty($value) && get_post_status($post->ID) !== 'publish') {
        $value = 1;
    }

    wp_nonce_field('tg_notify_meta_box', 'tg_notify_meta_box_nonce');
    echo '<label><input type="checkbox" name="tg_notify_send" value="1" ' . checked($value, 1, false) . '> ' . __('Benachrichtigung senden', 'wp-stat-notice') . '</label>';
}

function tg_notify_save_post($post_id) {
    // Sicherheitsprüfungen
    if (!isset($_POST['tg_notify_meta_box_nonce']) || !wp_verify_nonce($_POST['tg_notify_meta_box_nonce'], 'tg_notify_meta_box')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Prüfen, ob der Beitrag wirklich veröffentlicht wurde
    if (get_post_status($post_id) !== 'publish') return;

    // Prüfen, ob die Nachricht bereits gesendet wurde
    $already_sent = get_post_meta($post_id, '_tg_notify_sent', true);
    if ($already_sent) return;

    $send_notification = isset($_POST['tg_notify_send']) ? 1 : 0;
    update_post_meta($post_id, '_tg_notify_send', $send_notification);

    if ($send_notification) {
        tg_notify_send_telegram_message($post_id);
        update_post_meta($post_id, '_tg_notify_sent', 1); 
    }
}


add_action('save_post', 'tg_notify_save_post');

function tg_notify_send_telegram_message($post_id) {
    $bot_token = get_option('tg_notify_bot_token');
    $chat_ids = explode("\n", get_option('tg_notify_chat_ids'));
    $message_template = get_option('tg_notify_custom_message');

    $post = get_post($post_id);
    // Überprüfen, ob der Beitrag von einem Gast-Author stammt
    $author_name = get_the_author_meta('display_name', $post->post_author);
    if (empty($author_name)) {
        // Falls kein Name vorhanden ist (Gast-Author), den Gast-Namen verwenden oder einen Platzhalter setzen
        $author_name = 'Gast-Author';
    }

    // Nachricht formatieren
    $message = str_replace(
        ['{title}', '{author}', '{link}'],
        [$post->post_title, $author_name, get_permalink($post_id)],
        $message_template
    );

    foreach ($chat_ids as $chat_id) {
        $chat_id = trim($chat_id);
        if (!empty($chat_id)) {
            // Überprüfen, ob die ID das Thema enthält (Format: -1001234567890_123)
            if (strpos($chat_id, '_') !== false) {
                // Kanal-ID und Themen-ID trennen
                list($channel_id, $topic_id) = explode('_', $chat_id);
                $chat_id = $channel_id; 

                // Telegram API-Anfrage senden
                $url = "https://api.telegram.org/bot$bot_token/sendMessage";
                $args = [
                    'body' => json_encode([
                        'chat_id' => $chat_id,
                        'text' => $message, 
                        'parse_mode' => 'HTML',
                        'reply_to_message_id' => $topic_id 
                    ]),
                    'headers' => ['Content-Type' => 'application/json'],
                    'method' => 'POST',
                ];

                // API-Request senden und Fehlerprotokollierung
                $response = wp_remote_post($url, $args);
                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    error_log("Telegram Fehler: $error_message");
                } else {
                    // Erhöhe den Telegram-Nachrichtenzähler
                    tg_notify_increment_telegram_message_count();
                    error_log('Telegram Antwort: ' . print_r($response, true));
                }
            } else {
                // Normaler Kanal ohne Thema
                $url = "https://api.telegram.org/bot$bot_token/sendMessage";
                $args = [
                    'body' => json_encode([
                        'chat_id' => $chat_id,
                        'text' => $message, 
                        'parse_mode' => 'HTML'
                    ]),
                    'headers' => ['Content-Type' => 'application/json'],
                    'method' => 'POST',
                ];

                // API-Request senden und Fehlerprotokollierung
                $response = wp_remote_post($url, $args);
                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    error_log("Telegram Fehler: $error_message");
                } else {
                    // Erhöhe den Telegram-Nachrichtenzähler
                    tg_notify_increment_telegram_message_count();
                    error_log('Telegram Antwort: ' . print_r($response, true));
                }
            }
        }
    }
}

function tg_notify_increment_telegram_message_count() {
    $current_count = get_option('wp_multi_telegram_message_count', 0);
    update_option('wp_multi_telegram_message_count', $current_count + 1);
}


/*
* Admin-Dashboard Nachrichten sende Zähler
*/


// Admin Dashboard Widget für Telegram und Discord Nachrichten Zähler
function wp_multi_add_dashboard_widgets() {
    wp_add_dashboard_widget(
        'wp_multi_dashboard_widget', 
        'Telegram & Discord Nachrichten Zähler', 
        'wp_multi_display_dashboard_widget' 
    );
}
add_action('wp_dashboard_setup', 'wp_multi_add_dashboard_widgets');

// Callback-Funktion, die den Inhalt des Widgets anzeigt
function wp_multi_display_dashboard_widget() {
    // Telegram-Nachrichtenzähler
    $telegram_message_count = get_option('wp_multi_telegram_message_count', 0);
    // Discord-Nachrichtenzähler
    $discord_message_count = get_option('wp_multi_discord_message_count', 0);

    // Ausgabe der Zähler
    echo '<p><strong>Telegram Nachrichten gesendet:</strong> ' . esc_html($telegram_message_count) . '</p>';
    echo '<p><strong>Discord Nachrichten gesendet:</strong> ' . esc_html($discord_message_count) . '</p>';
}


/*
* Gast Autoren
*/


// Gast-Autor Eingabefeld in der Sidebar im Admin-Bereich hinzufügen
function wp_multi_add_guest_author_field() {
    add_meta_box(
        'guest_author_meta_box',
        __('Gast-Autor', 'wp-multi'),
        'wp_multi_guest_author_field',
        ['post', 'page', 'dein_custom_post_type'], 
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'wp_multi_add_guest_author_field');

// Callback-Funktion, die das Eingabefeld anzeigt
function wp_multi_guest_author_field($post) {
    // Die Metadaten des Beitrags laden (ob ein Gast-Autor gesetzt ist)
    $guest_author = get_post_meta($post->ID, '_guest_author', true);
    ?>
    <label for="guest_author"><?php _e('Gast-Autor Name:', 'wp-multi'); ?></label>
    <input type="text" id="guest_author" name="guest_author" value="<?php echo esc_attr($guest_author); ?>" class="widefat" />
    <?php
}

// Speichern der Gast-Autor Daten
function wp_multi_save_guest_author_meta($post_id) {
    // Sicherheit: Verhindere, dass die Metadaten beim Autosave überschrieben werden
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    // Überprüfen, ob der Benutzer Berechtigungen hat
    if (!current_user_can('edit_post', $post_id)) return;

    // Überprüfen, ob der Gast-Autor Name gesetzt wurde
    if (isset($_POST['guest_author'])) {
        $guest_author = sanitize_text_field($_POST['guest_author']);
        update_post_meta($post_id, '_guest_author', $guest_author);
    } else {
        delete_post_meta($post_id, '_guest_author');
    }
}
add_action('save_post', 'wp_multi_save_guest_author_meta');

// Gast-Autor anzeigen anstelle des regulären Autors im Frontend
function wp_multi_display_guest_author($author_name) {
    if ((is_single() || is_archive() || is_home()) && !is_admin()) {  
        $post = get_post();
        if ($post) {
            // Wenn der Beitrag einen Gast-Autor hat, diesen verwenden
            $guest_author = get_post_meta($post->ID, '_guest_author', true);
            if (!empty($guest_author)) {
                // Ersetze den Standard-Autor mit dem Gast-Autor
                $author_name = $guest_author;
            }
        }
    }
    return $author_name;
}
add_filter('the_author', 'wp_multi_display_guest_author');

// Anzeige des Gast-Autors in der Beitragsübersicht (Backend)
function wp_multi_add_guest_author_column($columns) {
    if (isset($columns['author'])) {
        $columns['guest_author'] = __('Gast-Autor', 'wp-multi');
    }
    return $columns;
}
add_filter('manage_posts_columns', 'wp_multi_add_guest_author_column');

// Inhalt der Gast-Autor-Spalte
function wp_multi_display_guest_author_column($column_name, $post_id) {
    if ($column_name == 'guest_author') {
        $guest_author = get_post_meta($post_id, '_guest_author', true);
        if (!empty($guest_author)) {
            echo esc_html($guest_author);
        } else {
            echo __('Kein Gast-Autor', 'wp-multi');
        }
    }
}
add_action('manage_posts_custom_column', 'wp_multi_display_guest_author_column', 10, 2);

// Admin-Menü für die Gast-Autor-Übersicht unter Benutzer hinzufügen
function wp_multi_add_guest_author_page() {
    add_submenu_page(
        'users.php',  
        __('Gast-Autor Übersicht', 'wp-multi'),     
        __('Gast-Autoren', 'wp-multi'),             
        'manage_options',                          
        'guest_author_overview',                   
        'wp_multi_guest_author_overview_page'      
    );
}
add_action('admin_menu', 'wp_multi_add_guest_author_page');


// Callback-Funktion für die Gast-Autor-Übersicht
function wp_multi_guest_author_overview_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Gast-Autor Übersicht', 'wp-multi'); ?></h1>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <th><?php _e('Gast-Autor', 'wp-multi'); ?></th>
                    <th><?php _e('Anzahl der Beiträge', 'wp-multi'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Alle Autoren und die Anzahl der Beiträge abfragen
                global $wpdb;
                $guest_authors = $wpdb->get_results("SELECT DISTINCT pm.meta_value AS guest_author
                                                    FROM {$wpdb->posts} p
                                                    LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                                                    WHERE pm.meta_key = '_guest_author' AND p.post_status = 'publish'
                                                    ORDER BY guest_author ASC");

                // Alle Gast-Autoren anzeigen
                if ($guest_authors) {
                    foreach ($guest_authors as $author) {
                        // Anzahl der Beiträge für den Gast-Autor zählen
                        $author_posts = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*)
                                                                        FROM {$wpdb->posts} p
                                                                        LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                                                                        WHERE pm.meta_key = '_guest_author' 
                                                                        AND pm.meta_value = %s 
                                                                        AND p.post_status = 'publish'", $author->guest_author));
                        ?>
                        <tr>
                            <td><?php echo esc_html($author->guest_author); ?></td>
                            <td><?php echo $author_posts; ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    echo '<tr><td colspan="2">' . __('Keine Gast-Autoren gefunden.', 'wp-multi') . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Schönes CSS nur für die Gast-Autor-Übersicht
function wp_multi_guest_author_overview_css() {
    // CSS nur auf der Seite der Gast-Autor-Übersicht anwenden
    if (isset($_GET['page']) && $_GET['page'] == 'guest_author_overview') {
        ?>
        <style>
            .wp-list-table {
                border-collapse: collapse;
                width: 100%;
            }

            .wp-list-table th, .wp-list-table td {
                padding: 12px;
                text-align: left;
                border: 1px solid #ddd;
            }

            .wp-list-table th {
                background-color: #f4f4f4;
            }

            .wp-list-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            .wrap h1 {
                font-size: 24px;
                margin-bottom: 20px;
            }

            .wrap {
                background-color: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
        </style>
        <?php
    }
}
add_action('admin_head', 'wp_multi_guest_author_overview_css');


/*
* Custom Text Box
*/


// Funktion zur Registrierung der zusätzlichen Einstellungen für einen weiteren Custom Text
function wp_multi_custom_text_register_second_text() {
    add_option('wp_multi_second_custom_text', '');
    register_setting('wp_multi_options_group', 'wp_multi_second_custom_text');
}
register_setting('wp_multi_options_group', 'wp_multi_custom_texts'); 
register_setting('wp_multi_options_group', 'wp_multi_second_custom_text');


// Funktion zum Hinzufügen des Menüeintrags unter "Beiträge"
function wp_multi_custom_text_add_settings_page() {
    add_submenu_page(
        'edit.php', 
        __('WP Multi Custom Text Einstellungen', 'wp-multi'), 
        __('Custom Text Einstellungen', 'wp-multi'), 
        'manage_options', 
        'wp_multi_settings', 
        'wp_multi_custom_text_settings_page_content' 
    );
    
}
add_action('admin_menu', 'wp_multi_custom_text_add_settings_page');

// Funktion zur Registrierung der Option zum Aktivieren/Deaktivieren der benutzerdefinierten Texte
function wp_multi_custom_text_register_enable_option() {
    add_option('wp_multi_enable_custom_texts', '1'); // Standardmäßig aktiviert
    register_setting('wp_multi_options_group', 'wp_multi_enable_custom_texts');
}
add_action('admin_init', 'wp_multi_custom_text_register_enable_option');

// Funktion zum Erstellen der Einstellungsseite mit der Option zur Aktivierung/Deaktivierung
function wp_multi_custom_text_settings_page_content() {
    ?>
    <div class="wrap">
        <form method="post" action="options.php">
            <?php settings_fields('wp_multi_options_group'); ?>

            <div class="wp-multi-header-box">
            <div class="wp-multi-banner">
                <img src="https://m-viper.de/img/logo.png" alt="M_Viper Logo" class="wp-multi-logo" />
                <h1>Custom Text verwalten</h1>
            </div>
        </div>
            
            <table class="form-table">
                <!-- Option zum Aktivieren/Deaktivieren der Custom Texte -->
                <tr valign="top">
                    <th scope="row"><?php _e('Custom Texte aktivieren', 'wp-multi'); ?></th>
                    <td>
                        <input type="checkbox" name="wp_multi_enable_custom_texts" value="1" <?php checked(1, get_option('wp_multi_enable_custom_texts'), true); ?> />
                        <p class="description"><?php _e('Aktiviere oder deaktiviere die Anzeige der benutzerdefinierten Texte auf der Webseite.', 'wp-multi'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Custom Texte', 'wp-multi'); ?></th>
                    <td>
                        <textarea name="wp_multi_custom_texts" rows="10" cols="50" class="large-text"><?php echo get_option('wp_multi_custom_texts'); ?></textarea>
                        <p class="description"><?php _e('Gib jeden Text in einer neuen Zeile ein.', 'wp-multi'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Zweiter Custom Text', 'wp-multi'); ?></th>
                    <td>
                        <textarea name="wp_multi_second_custom_text" rows="10" cols="50" class="large-text"><?php echo get_option('wp_multi_second_custom_text'); ?></textarea>
                        <p class="description"><?php _e('Gib den zweiten Custom Text ein, der über der ersten Box angezeigt wird.', 'wp-multi'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>

    <style>
       /* Container für Logo und Banner in einer Box */
        .wp-multi-header-box {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            background-color: #f1f1f1;
        }

        .wp-multi-logo {
            max-height: 80px;
        }

        /* Banner-Stil */
        .wp-multi-banner {
            background-color: #0073aa; 
            padding: 10px;
            text-align: center;
            margin-top: 20px;
        }

        .wp-multi-banner h1 {
            font-size: 30px;
            margin: 0;
            font-weight: 600;
        }

        /* Benutzerdefinierte Nachrichtenbox */
        .wp-multi-custom-message {
            background-color: #f1f1f1;
            padding: 15px;
            border-left: 4px solid #0073aa;
            margin: 20px 0;
            font-size: 16px;
        }

        /* Anpassungen für die Formularfelder */
        .form-table {
            margin-top: 30px;
        }

        .form-table th {
            width: 220px;
            font-weight: bold;
        }

        .form-table td {
            width: auto;
        }

        .wp-multi-table input, .wp-multi-table textarea {
            width: 100%;
            border-radius: 5px;
            padding: 10px;
            border: 1px solid #ccc;
        }

        .wp-multi-table input:focus, .wp-multi-table textarea:focus {
            border-color: #0073aa;
        }

        .wp-multi-shortcodes-list {
            list-style-type: none;
            margin-top: 30px;
            padding-left: 0;
        }

        .wp-multi-shortcodes-list li {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .wp-multi-shortcodes-list li:hover {
            background-color: #f1f1f1;
        }

        /* Löschen-Button Stil */
        .wp-multi-delete-button {
            color: #ff0000;
            margin-left: 10px;
            text-decoration: none;
        }

        .wp-multi-delete-button:hover {
            text-decoration: underline;
        }

        /* Anpassen des Buttons */
        .button-primary {
            background-color: #0073aa;
            border-color: #0073aa;
        }

        .button-primary:hover {
            background-color: #005f8d;
            border-color: #005f8d;
        }
    </style>
    <?php
}

// Anzeige der Custom Texts in einer Box im Frontend mit Aktivierungsoption
function wp_multi_custom_text_display($content) {
    // Überprüfe, ob die benutzerdefinierten Texte aktiviert sind
    $enable_custom_texts = get_option('wp_multi_enable_custom_texts', '1'); 
    if ($enable_custom_texts != '1') {
        return $content; // Keine Anzeige der benutzerdefinierten Texte, wenn deaktiviert
    }

    if (is_single()) {
        global $post;

        // Autor ermitteln (Gastautor oder regulärer Autor)
        $guest_author_name = get_post_meta($post->ID, 'guest_author', true);
        $author_name = !empty($guest_author_name) ? $guest_author_name : get_the_author();

        // Holen der benutzerdefinierten Texte aus den Einstellungen
        $custom_texts = get_option('wp_multi_custom_texts', '');
        $second_custom_texts = get_option('wp_multi_second_custom_text', '');

        // Sicherstellen, dass wir eine Liste von Texten haben
        $custom_texts_array = array_filter(array_map('trim', explode("\n", $custom_texts)));
        $second_custom_texts_array = array_filter(array_map('trim', explode("\n", $second_custom_texts)));

        // Die aktuelle Auswahl des Custom Texts aus den Post-Metadaten
        $selected_custom_text = get_post_meta($post->ID, '_custom_text_choice', true);
        $selected_second_custom_text = get_post_meta($post->ID, '_second_custom_text_choice', true);

        // Falls keine Texte verfügbar sind, abbrechen
        if (empty($custom_texts_array) && empty($second_custom_texts_array)) {
            return $content;
        }

        // Die Ausgabe-Box erstellen
        $output = '<div class="custom-text-box" style="margin-top: 40px; padding: 20px; background-color: #f0f0f0; border: 2px solid #ddd; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); max-width: 400px; width: auto; font-size: 16px; line-height: 1.2; clear: both; margin-left: auto; margin-right: 0; display: block; margin-bottom: 20px; position: relative;">';

        // Anzeige des Autors und des zweiten benutzerdefinierten Texts in einer Zeile
        $output .= '<p><strong>' . __('Autor:', 'wp-multi') . ' ' . esc_html($author_name);

        if ($selected_second_custom_text !== '' && isset($second_custom_texts_array[$selected_second_custom_text])) {
            $output .= '  |  ' . esc_html($second_custom_texts_array[$selected_second_custom_text]); // Trennzeichen " | "
        }

        $output .= '</strong></p>';

        // Anzeige des ersten benutzerdefinierten Texts (unterer Bereich)
        if ($selected_custom_text !== '' && isset($custom_texts_array[$selected_custom_text])) {
            $output .= '<p><em>' . esc_html($custom_texts_array[$selected_custom_text]) . '</em></p>';
        }

        $output .= '</div>';

        return $content . $output;
    }
    return $content;
}
add_filter('the_content', 'wp_multi_custom_text_display');

// Funktion zum Hinzufügen der Meta-Box für beide Custom Texts
function wp_multi_add_custom_text_fields($post) {
    // Holen der benutzerdefinierten Texte aus den Einstellungen
    $custom_texts = get_option('wp_multi_custom_texts');
    $custom_texts_array = explode("\n", $custom_texts); 

    // Holen des zweiten benutzerdefinierten Textes aus den Einstellungen
    $second_custom_text = get_option('wp_multi_second_custom_text');
    $second_custom_text_array = explode("\n", $second_custom_text); 

    // Die aktuelle Auswahl des Custom Texts
    $selected_custom_text = get_post_meta($post->ID, '_custom_text_choice', true);
    $selected_second_custom_text = get_post_meta($post->ID, '_second_custom_text_choice', true);

    ?>
    <label for="custom_text_choice"><?php _e('Wähle den Custom Text (unterer Bereich):', 'wp-multi'); ?></label>
    <select name="custom_text_choice" id="custom_text_choice" class="widefat">
        <?php foreach ($custom_texts_array as $key => $value) { ?>
            <option value="<?php echo esc_attr($key); ?>" <?php selected($selected_custom_text, $key); ?>><?php echo esc_html(trim($value)); ?></option>
        <?php } ?>
    </select>

    <label for="second_custom_text_choice"><?php _e('Wähle den zweiten Custom Text (oberer Bereich):', 'wp-multi'); ?></label>
    <select name="second_custom_text_choice" id="second_custom_text_choice" class="widefat">
        <?php foreach ($second_custom_text_array as $key => $value) { ?>
            <option value="<?php echo esc_attr($key); ?>" <?php selected($selected_second_custom_text, $key); ?>><?php echo esc_html(trim($value)); ?></option>
        <?php } ?>
    </select>
    <?php
}

// Meta-Box hinzufügen
add_action('add_meta_boxes', function() {
    add_meta_box('wp_multi_custom_text', __('Custom Text Auswahl', 'wp-multi'), 'wp_multi_add_custom_text_fields', 'post', 'normal', 'high');
});

// Speichern der benutzerdefinierten Textauswahl im Beitrag
function wp_multi_save_custom_text_choice($post_id) {
    if (isset($_POST['custom_text_choice'])) {
        update_post_meta($post_id, '_custom_text_choice', sanitize_text_field($_POST['custom_text_choice']));
    }
    if (isset($_POST['second_custom_text_choice'])) {
        update_post_meta($post_id, '_second_custom_text_choice', sanitize_text_field($_POST['second_custom_text_choice']));
    }
}
add_action('save_post', 'wp_multi_save_custom_text_choice');


/*
* Custom Link im Admin Sidebar hinzufügen
*/


// Funktion zum Hinzufügen des benutzerdefinierten Menüs
function wp_stat_notice_add_custom_pages() {
    // Prüfen, ob es Seiten gibt, die hinzugefügt werden müssen
    $custom_pages = get_option('wp_stat_notice_custom_pages', []);

    // Menü hinzufügen, wenn es Seiten gibt
    if ($custom_pages && is_array($custom_pages)) {
        foreach ($custom_pages as $page) {
            if (isset($page['title']) && isset($page['url'])) {
                // Menü hinzufügen mit WordPress Icon
                add_menu_page(
                    $page['title'],               
                    $page['title'],               
                    'manage_options',             
                    $page['slug'],                
                    'wp_stat_notice_custom_page', 
                    $page['icon'],                
                    100                            
                );
            }
        }
    }
}
add_action('admin_menu', 'wp_stat_notice_add_custom_pages');

// Callback-Funktion für das Anzeigen der benutzerdefinierten Seiten
function wp_stat_notice_custom_page() {
    // Aktuelle Seite abrufen
    $current_slug = $_GET['page'] ?? '';

    // Seiten aus der Option abrufen
    $custom_pages = get_option('wp_stat_notice_custom_pages', []);
    
    if ($custom_pages && is_array($custom_pages)) {
        foreach ($custom_pages as $page) {
            if ($page['slug'] === $current_slug) {
                // Externe Seite anzeigen
                if (isset($page['url']) && filter_var($page['url'], FILTER_VALIDATE_URL)) {
                    // Link in einem neuen Fenster öffnen
                    echo '<script>window.open("' . esc_url($page['url']) . '", "_blank");</script>';
                }
                // Interne Seite anzeigen
                else if (isset($page['slug'])) {
                    echo '<h1>' . esc_html($page['title']) . '</h1>';
                    echo '<p>' . __('Dies ist eine benutzerdefinierte Seite im Admin-Bereich.', 'wp-stat-notice') . '</p>';
                }
                break;
            }
        }
    }
}

// Funktion zum Hinzufügen neuer benutzerdefinierter Seiten über ein Admin-Formular
function wp_stat_notice_add_custom_page_form() {
    // Alle Dashicons laden
    $dashicons = [
        'dashicons-admin-links',
        'dashicons-admin-site',
        'dashicons-admin-home',
        'dashicons-admin-plugins',
        'dashicons-admin-users',
        'dashicons-analytics',
        'dashicons-archive',
        'dashicons-book',
        'dashicons-calendar',
        'dashicons-camera',
        'dashicons-cart',
        'dashicons-cloud',
        'dashicons-clipboard',
        'dashicons-clock',
        'dashicons-cloud-upload',
        'dashicons-email',
        'dashicons-heart',
        'dashicons-laptop',
        'dashicons-lock',
        'dashicons-phone',
        'dashicons-rss',
        'dashicons-search',
        'dashicons-settings',
        'dashicons-share',
        'dashicons-tag',
        'dashicons-thumbs-up',
        'dashicons-welcome-learn-more',
        'dashicons-welcome-write-blog'
    ];

    ?>
    <div class="wrap">
        <h1><?php _e('Benutzerdefinierten Adminlink hinzufügen', 'wp-stat-notice'); ?></h1>
        
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="page_title"><?php _e('Titel der Seite', 'wp-stat-notice'); ?></label></th>
                    <td><input type="text" name="page_title" id="page_title" required class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="page_url"><?php _e('URL der Seite (intern oder extern)', 'wp-stat-notice'); ?></label></th>
                    <td><input type="text" name="page_url" id="page_url" required class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="page_slug"><?php _e('Slug der Seite', 'wp-stat-notice'); ?></label></th>
                    <td><input type="text" name="page_slug" id="page_slug" required class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="page_icon"><?php _e('Dashicon auswählen', 'wp-stat-notice'); ?></label></th>
                    <td>
                        <select name="page_icon" id="page_icon" onchange="updateIconPreview()">
                            <?php foreach ($dashicons as $dashicon): ?>
                                <option value="<?php echo esc_attr($dashicon); ?>"><?php echo esc_html($dashicon); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <br>
                        <div id="icon-preview" style="font-size: 24px; margin-top: 10px; display: inline-block;"></div>
                    </td>
                </tr>
            </table>
            
            <p>
                <input type="submit" name="add_custom_page" class="button button-primary" value="<?php _e('Seite hinzufügen', 'wp-stat-notice'); ?>">
            </p>
        </form>
        
        <?php
        // Formularverarbeitung, wenn der Benutzer auf "Seite hinzufügen" klickt
        if (isset($_POST['add_custom_page'])) {
            $title = sanitize_text_field($_POST['page_title']);
            $url = sanitize_text_field($_POST['page_url']);
            $slug = sanitize_title_with_dashes($_POST['page_slug']);
            $icon = sanitize_text_field($_POST['page_icon']); // Dashicon speichern

            // Aktuelle benutzerdefinierte Seiten abrufen und neue Seite hinzufügen
            $custom_pages = get_option('wp_stat_notice_custom_pages', []);
            $custom_pages[] = [
                'title' => $title,
                'url' => $url,
                'slug' => $slug,
                'icon' => $icon, 
            ];

            // Option speichern
            update_option('wp_stat_notice_custom_pages', $custom_pages);

            // Menü neu hinzufügen
            wp_stat_notice_add_custom_pages();

            // Bestätigung
            echo '<div class="updated"><p>' . __('Benutzerdefinierte Seite wurde hinzugefügt!', 'wp-stat-notice') . '</p></div>';
        }

        // Verwaltung der benutzerdefinierten Seiten
        $custom_pages = get_option('wp_stat_notice_custom_pages', []);
        if ($custom_pages) {
            echo '<h2>' . __('Verwaltung der benutzerdefinierten Seiten', 'wp-stat-notice') . '</h2>';
            echo '<table class="widefat fixed" cellspacing="0">';
            echo '<thead><tr><th>' . __('Titel', 'wp-stat-notice') . '</th><th>' . __('URL', 'wp-stat-notice') . '</th><th>' . __('Aktionen', 'wp-stat-notice') . '</th></tr></thead>';
            echo '<tbody>';
            foreach ($custom_pages as $index => $page) {
                echo '<tr>';
                echo '<td>' . esc_html($page['title']) . '</td>';
                echo '<td>' . esc_html($page['url']) . '</td>';
                echo '<td>';
                echo '<a href="?page=wp-stat-notice-custom-page&edit=' . $index . '">' . __('Bearbeiten', 'wp-stat-notice') . '</a> | ';
                echo '<a href="?page=wp-stat-notice-custom-page&delete=' . $index . '">' . __('Löschen', 'wp-stat-notice') . '</a>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        }

        // Bearbeiten und Löschen von Seiten
        if (isset($_GET['edit'])) {
            $edit_index = (int) $_GET['edit'];
            $edit_page = $custom_pages[$edit_index];

            // Formular zum Bearbeiten der Seite
            echo '<h2>' . __('Seite bearbeiten', 'wp-stat-notice') . '</h2>';
            ?>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="edit_page_title"><?php _e('Titel der Seite', 'wp-stat-notice'); ?></label></th>
                        <td><input type="text" name="edit_page_title" id="edit_page_title" value="<?php echo esc_attr($edit_page['title']); ?>" required class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="edit_page_url"><?php _e('URL der Seite', 'wp-stat-notice'); ?></label></th>
                        <td><input type="text" name="edit_page_url" id="edit_page_url" value="<?php echo esc_attr($edit_page['url']); ?>" required class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="edit_page_slug"><?php _e('Slug der Seite', 'wp-stat-notice'); ?></label></th>
                        <td><input type="text" name="edit_page_slug" id="edit_page_slug" value="<?php echo esc_attr($edit_page['slug']); ?>" required class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="edit_page_icon"><?php _e('Dashicon', 'wp-stat-notice'); ?></label></th>
                        <td><input type="text" name="edit_page_icon" id="edit_page_icon" value="<?php echo esc_attr($edit_page['icon']); ?>" class="regular-text"></td>
                    </tr>
                </table>

                <p>
                    <input type="submit" name="save_custom_page" class="button button-primary" value="<?php _e('Änderungen speichern', 'wp-stat-notice'); ?>">
                </p>
            </form>
            
            <?php
            // Speichern der bearbeiteten Seite
            if (isset($_POST['save_custom_page'])) {
                $custom_pages[$edit_index]['title'] = sanitize_text_field($_POST['edit_page_title']);
                $custom_pages[$edit_index]['url'] = sanitize_text_field($_POST['edit_page_url']);
                $custom_pages[$edit_index]['slug'] = sanitize_title_with_dashes($_POST['edit_page_slug']);
                $custom_pages[$edit_index]['icon'] = sanitize_text_field($_POST['edit_page_icon']); 
                update_option('wp_stat_notice_custom_pages', $custom_pages);

                echo '<div class="updated"><p>' . __('Seite erfolgreich bearbeitet!', 'wp-stat-notice') . '</p></div>';
            }
        }

        // Löschen der Seite
        if (isset($_GET['delete'])) {
            $delete_index = (int) $_GET['delete'];
            unset($custom_pages[$delete_index]);
            $custom_pages = array_values($custom_pages); 
            update_option('wp_stat_notice_custom_pages', $custom_pages);

            echo '<div class="updated"><p>' . __('Seite wurde gelöscht.', 'wp-stat-notice') . '</p></div>';
        }
        ?>
    </div>

    <script>
    // Funktion zur Aktualisierung der Dashicon-Vorschau
    function updateIconPreview() {
        var selectedIcon = document.getElementById('page_icon').value;
        var preview = document.getElementById('icon-preview');
        preview.className = 'dashicons ' + selectedIcon; 
    }

    // Initiale Vorschau laden
    updateIconPreview();
    </script>
    <?php
}

// Seite zum Hinzufügen benutzerdefinierter Seiten unter Werkzeuge im Admin-Menü anzeigen
function wp_stat_notice_custom_page_add_form() {
    add_submenu_page(
        'tools.php', 
        'Admin-Link hinzufügen', 
        'Admin-Link hinzufügen', 
        'manage_options',            
        'wp-stat-notice-custom-page', 
        'wp_stat_notice_add_custom_page_form', 
        10                           
    );
}
add_action('admin_menu', 'wp_stat_notice_custom_page_add_form');


/*
* Beitrags Report
*/


// Funktion zum Erstellen und Aktualisieren der Datenbanktabelle für gemeldete Beiträge
function wp_stat_notice_create_reported_posts_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'reported_posts';
    $charset_collate = $wpdb->get_charset_collate();

    // SQL für die Tabelle
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        post_id BIGINT(20) NOT NULL,
        report_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        reason TEXT NOT NULL,
        name VARCHAR(255) NOT NULL,
        status VARCHAR(20) DEFAULT 'reported',
        user_id BIGINT(20) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (id),
        KEY post_id (post_id),
        KEY user_id (user_id)
    ) $charset_collate;";

    // Zuerst prüfen, ob die Spalte `name` vorhanden ist
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");

    $column_names = array_map(function($column) {
        return $column->Field;
    }, $columns);

    // Wenn die Spalte 'name' nicht vorhanden ist, wird sie hinzugefügt
    if (!in_array('name', $column_names)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN `name` VARCHAR(255) NOT NULL");
    }

    // Tabelle erstellen oder aktualisieren
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql); 
}

register_activation_hook(__FILE__, 'wp_stat_notice_create_reported_posts_table');


// Shortcode für den "Beitrag melden"-Button
function wp_stat_notice_report_button($atts) {
    global $post;
    if (!is_user_logged_in()) return ''; 

    $atts = shortcode_atts(array('post_id' => $post->ID), $atts, 'report_button');
    $nonce = wp_create_nonce('report_post_nonce');

    // Report-Button & Eingabefelder für Name und Grund
    ob_start();
    ?>
    <button class="report-post" data-post-id="<?php echo esc_attr($atts['post_id']); ?>" data-nonce="<?php echo esc_attr($nonce); ?>">
        Beitrag melden
    </button>
    <div class="report-reason" style="display:none;">
        <input type="text" class="report-name" placeholder="Geben Sie Ihren Namen an" required />
        <textarea class="report-reason-text" placeholder="Geben Sie den Grund an" required></textarea>
        <button class="submit-report">Bericht absenden</button>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('report_button', 'wp_stat_notice_report_button');

// Stil für das Meldeformular
function wp_stat_notice_report_button_styles() {
    ?>
    <style>
        .report-reason {
            display: none;
            margin-top: 10px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 300px;
            margin-top: 10px;
        }
        .report-reason input, .report-reason textarea {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .report-reason button {
            background-color: #0073aa;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .report-reason button:hover {
            background-color: #005177;
        }
        .report-post {
            background-color: #ff7f00;
            color: white;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .report-post:hover {
            background-color: #e07b00;
        }
    </style>
    <?php
}
add_action('wp_head', 'wp_stat_notice_report_button_styles');

// Dashboard-Widget hinzufügen
function wp_stat_notice_add_dashboard_widget() {
    wp_add_dashboard_widget(
        'wp_stat_notice_dashboard_widget', 
        'Letzte 10 gemeldete Beiträge',    
        'wp_stat_notice_dashboard_widget_display' 
    );
}
add_action('wp_dashboard_setup', 'wp_stat_notice_add_dashboard_widget');

// Funktion, die das Dashboard-Widget anzeigt
function wp_stat_notice_dashboard_widget_display() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'reported_posts';

    // Abfrage, um die letzten 10 gemeldeten Beiträge zu holen
    $reports = $wpdb->get_results(
        "SELECT * FROM $table_name ORDER BY report_date DESC LIMIT 10"
    );

    if (empty($reports)) {
        echo '<p>Es gibt keine gemeldeten Beiträge.</p>';
        return;
    }

    // Tabelle mit den letzten 10 gemeldeten Beiträgen anzeigen
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Beitrag</th><th>Datum</th><th>Grund</th></tr></thead><tbody>';

    foreach ($reports as $report) {
        $post = get_post($report->post_id);
        echo '<tr>';
        echo '<td>' . esc_html($post->post_title) . '</td>';
        echo '<td>' . esc_html($report->report_date) . '</td>';
        echo '<td>' . esc_html($report->reason) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}

// AJAX-Handler zum Senden eines Reports
function wp_stat_notice_handle_report() {
    check_ajax_referer('report_post_nonce', 'nonce');

    if (!isset($_POST['post_id'], $_POST['reason'], $_POST['name']) || !is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Ungültige Anfrage.'));
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'reported_posts';

    $post_id = intval($_POST['post_id']);
    $reason = sanitize_textarea_field($_POST['reason']);
    $name = sanitize_text_field($_POST['name']);
    $user_id = get_current_user_id();

    // Versuche den Eintrag in die Datenbank zu schreiben
    $result = $wpdb->insert(
        $table_name,
        array(
            'post_id'     => $post_id,
            'reason'      => $reason,
            'name'        => $name,
            'status'      => 'reported',
            'user_id'     => $user_id
        ),
        array('%d', '%s', '%s', '%s', '%d') 
    );

    if ($result === false) {
        error_log("Datenbankfehler: " . $wpdb->last_error); // WP Debug Log
        wp_send_json_error(array('message' => 'Datenbankfehler: ' . $wpdb->last_error));
    } else {
        wp_send_json_success(array('message' => 'Bericht erfolgreich gesendet.'));
    }
}
add_action('wp_ajax_report_post', 'wp_stat_notice_handle_report');


// JavaScript in den Footer einfügen
function wp_stat_notice_inline_js() {
    ?>
    <script>
jQuery(document).ready(function ($) {
    $(document).on("click", ".report-post", function () {
        let reasonBox = $(this).next(".report-reason");
        reasonBox.toggle();
    });

    $(document).on("click", ".submit-report", function () {
        let button = $(this);
        let container = button.closest(".report-reason");
        let reason = container.find(".report-reason-text").val();
        let name = container.find(".report-name").val();
        let postId = button.closest(".report-reason").prev(".report-post").data("post-id");
        let nonce = button.closest(".report-reason").prev(".report-post").data("nonce");

        if (!reason || !name) {
            alert("Bitte geben Sie sowohl Ihren Namen als auch einen Grund an.");
            return;
        }

        $.ajax({
            url: "<?php echo admin_url('admin-ajax.php'); ?>",
            type: "POST",
            data: {
                action: "report_post",
                post_id: postId,
                reason: reason,
                name: name,
                nonce: nonce
            },
            success: function (response) {
                if (response.success) {
                    alert("Der Bericht wurde erfolgreich gesendet.");
                    container.hide();
                } else {
                    alert("Fehler: " + response.data.message);
                }
            }
        });
    });
});
</script>

    <?php
}
add_action('wp_footer', 'wp_stat_notice_inline_js');

// Admin-Seite für gemeldete Beiträge
function wp_stat_notice_reported_posts_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'reported_posts';
    $reports = $wpdb->get_results("SELECT * FROM $table_name ORDER BY report_date DESC");

    ?>
    <div class="wrap">
        <h1><?php _e('Gemeldete Beiträge', 'wp-stat-notice'); ?></h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Beitrag</th>
                    <th>Datum</th>
                    <th>Name</th>
                    <th>Grund</th>
                    <th>Status</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): 
                    $post = get_post($report->post_id); ?>
                    <tr>
                        <td><?php echo esc_html($post->post_title); ?></td>
                        <td><?php echo esc_html($report->report_date); ?></td>
                        <td><?php echo esc_html($report->name); ?></td>
                        <td><?php echo esc_html($report->reason); ?></td>
                        <td><?php echo esc_html($report->status); ?></td>
                        <td>
                            <a href="?page=reported-posts&delete_report=<?php echo esc_attr($report->id); ?>" class="delete-report">Report Löschen</a> |
                            <a href="?page=reported-posts&unpublish_report=<?php echo esc_attr($report->id); ?>" class="unpublish-report">Unpublish</a> |
                            <a href="?page=reported-posts&delete_post=<?php echo esc_attr($report->post_id); ?>" class="delete-post">Beitrag Löschen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Aktionen für Report-Handling
function wp_stat_notice_handle_report_actions() {
    global $wpdb;
    if (isset($_GET['delete_report'])) {
        $wpdb->delete($wpdb->prefix . 'reported_posts', array('id' => intval($_GET['delete_report'])));
    } elseif (isset($_GET['unpublish_report'])) {
        $wpdb->update($wpdb->prefix . 'reported_posts', array('status' => 'unpublished'), array('id' => intval($_GET['unpublish_report'])));
    } elseif (isset($_GET['delete_post'])) {
        wp_delete_post(intval($_GET['delete_post']), true);
    }
}
add_action('admin_init', 'wp_stat_notice_handle_report_actions');

// Menüpunkt im Admin-Bereich hinzufügen
function wp_stat_notice_add_reported_posts_menu() {
    add_menu_page(
        'Gemeldete Beiträge',
        'Gemeldete Beiträge',
        'manage_options',
        'reported-posts',
        'wp_stat_notice_reported_posts_page',
        'dashicons-warning',
        25
    );
}
add_action('admin_menu', 'wp_stat_notice_add_reported_posts_menu');


/*
* Gast Lesezeichen
*/


// Funktion zum Erstellen des benutzerdefinierten Post-Typs für Lesezeichen
function statistik_manager_create_bookmark_post_type() {
    register_post_type('bookmark',
        array(
            'labels' => array(
                'name' => __('Lesezeichen', 'statistik-manager'),
                'singular_name' => __('Lesezeichen', 'statistik-manager')
            ),
            'public' => false, // Privat, nur für Backend
            'show_ui' => false, // Nicht im Backend anzeigen
            'show_in_menu' => false, // Nicht im Menü anzeigen
            'supports' => array('title', 'custom-fields')
        )
    );
}
add_action('init', 'statistik_manager_create_bookmark_post_type');

// Funktion zum Speichern eines Lesezeichens für Gäste
function statistik_manager_save_bookmark($post_id) {
    if (isset($_COOKIE['guest_token'])) {
        update_post_meta($post_id, '_guest_token', $_COOKIE['guest_token']);
    }
}

// Funktion zum Abrufen der Lesezeichen eines Gastes
function statistik_manager_get_guest_bookmarks() {
    $guest_token = isset($_COOKIE['guest_token']) ? $_COOKIE['guest_token'] : null;

    if (!$guest_token) {
        // Wenn der Gast noch kein Token hat, erstellen und speichern
        $guest_token = wp_generate_uuid4(); // Ein zufälliger UUID-Token
        setcookie('guest_token', $guest_token, time() + 3600 * 24 * 30, COOKIEPATH, COOKIE_DOMAIN); // Cookie für 30 Tage setzen
    }

    // Abfrage der Lesezeichen für den aktuellen Gast
    $args = array(
        'post_type' => 'bookmark',
        'meta_key' => '_guest_token',
        'meta_value' => $guest_token,
        'posts_per_page' => -1,
        'post_status' => 'publish'
    );
    $bookmarks_query = new WP_Query($args);

    return $bookmarks_query->posts;
}

// Funktion zum Löschen eines Lesezeichens (nur für den aktuellen Gast)
function statistik_manager_delete_bookmark() {
    if (isset($_POST['bookmark_id']) && isset($_COOKIE['guest_token'])) {
        $bookmark_id = intval($_POST['bookmark_id']);
        $guest_token = $_COOKIE['guest_token'];

        // Überprüfen, ob das Lesezeichen diesem Gast gehört
        $stored_token = get_post_meta($bookmark_id, '_guest_token', true);
        if ($stored_token === $guest_token) {
            wp_delete_post($bookmark_id, true);
            echo 'Lesezeichen erfolgreich gelöscht!';
        } else {
            echo 'Du kannst nur deine eigenen Lesezeichen löschen!';
        }
    }
    wp_die(); // Beende die Anfrage
}
add_action('wp_ajax_delete_bookmark', 'statistik_manager_delete_bookmark');
add_action('wp_ajax_nopriv_delete_bookmark', 'statistik_manager_delete_bookmark');

// Funktion zum Anzeigen der Lesezeichen mit Löschen-Option
function statistik_manager_display_bookmarks() {
    $bookmarks = statistik_manager_get_guest_bookmarks();

    if (!empty($bookmarks)) {
        $output = '<div class="statistik-manager-bookmarks">';
        $output .= '<h3>' . __('Gespeicherte Lesezeichen', 'statistik-manager') . '</h3>';
        $output .= '<ul>';

        foreach ($bookmarks as $bookmark) {
            $bookmark_url = get_post_meta($bookmark->ID, '_bookmark_url', true);
            $bookmark_id = $bookmark->ID;
            $bookmark_name = get_the_title($bookmark);  // Benutzerdefinierter Name des Lesezeichens

            // Ausgabe des Lesezeichens mit getauschtem Button und Titel
            $output .= '<li>';
            // Button kommt nun vor dem Titel
            $output .= ' <button class="delete-bookmark-btn" data-bookmark-id="' . esc_attr($bookmark_id) . '">' . __('Lesezeichen Löschen', 'statistik-manager') . '</button>';
            $output .= '<a href="' . esc_url($bookmark_url) . '" target="_blank">' . esc_html($bookmark_name) . '</a>';
            $output .= '</li>';
        }

        $output .= '</ul>';
        $output .= '</div>';
        return $output;
    } else {
        return '<p>' . __('Keine Lesezeichen gefunden.', 'statistik-manager') . '</p>';
    }
}

// Funktion zum Hinzufügen eines Lesezeichens via AJAX
function statistik_manager_add_bookmark_ajax() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bookmark_url']) && isset($_POST['bookmark_name'])) {
        $bookmark_url = sanitize_text_field($_POST['bookmark_url']);
        $bookmark_name = sanitize_text_field($_POST['bookmark_name']); // Name des Lesezeichens

        // Neues Lesezeichen erstellen
        $post_id = wp_insert_post(array(
            'post_type' => 'bookmark',
            'post_title' => $bookmark_name, // Benutzerdefinierter Name für das Lesezeichen
            'post_status' => 'publish',
            'meta_input' => array(
                '_bookmark_url' => $bookmark_url
            )
        ));

        // Speichern des Gast-Token
        if (isset($_COOKIE['guest_token'])) {
            update_post_meta($post_id, '_guest_token', $_COOKIE['guest_token']);
        }

        // Rückgabe des neuen Lesezeichens als HTML
        $bookmark_html = '<li>';
        $bookmark_html .= '<button class="delete-bookmark-btn" data-bookmark-id="' . esc_attr($post_id) . '">' . __('Lesezeichen Löschen', 'statistik-manager') . '</button>';
        $bookmark_html .= '<a href="' . esc_url($bookmark_url) . '" target="_blank">' . esc_html($bookmark_name) . '</a>';
        $bookmark_html .= '</li>';

        echo $bookmark_html;
    }

    wp_die(); // Beende die Anfrage
}
add_action('wp_ajax_add_bookmark', 'statistik_manager_add_bookmark_ajax');
add_action('wp_ajax_nopriv_add_bookmark', 'statistik_manager_add_bookmark_ajax');

// JavaScript zum Hinzufügen des Lesezeichens ohne Seitenaktualisierung
function statistik_manager_add_bookmark_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            // Hinzufügen eines Lesezeichens über AJAX
            $('form#add-bookmark-form').on('submit', function(e){
                e.preventDefault(); // Verhindert das Standard-Formular-Absenden

                var bookmarkUrl = $('#bookmark_url').val();
                var bookmarkName = $('#bookmark_name').val();

                var data = {
                    action: 'add_bookmark',
                    bookmark_url: bookmarkUrl,
                    bookmark_name: bookmarkName
                };

                // AJAX-Anfrage senden, um das Lesezeichen hinzuzufügen
                $.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                    // Füge das neue Lesezeichen zur Liste hinzu, ohne die Seite neu zu laden
                    $('.statistik-manager-bookmarks ul').append(response);
                    // Leere die Eingabefelder
                    $('#bookmark_name').val('');
                    $('#bookmark_url').val('');
                });
            });

            // Löschen eines Lesezeichens über AJAX
            $('body').on('click', '.delete-bookmark-btn', function() {
                var bookmarkId = $(this).data('bookmark-id'); // Holen der ID des Lesezeichens
                
                var data = {
                    action: 'delete_bookmark',
                    bookmark_id: bookmarkId
                };

                // AJAX-Anfrage senden, um das Lesezeichen zu löschen
                $.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                    alert(response); // Zeige Antwort an (z.B. "Lesezeichen erfolgreich gelöscht!")
                    
                    // Entferne das gelöschte Lesezeichen aus der Liste
                    $('button[data-bookmark-id="' + bookmarkId + '"]').closest('li').remove();
                });
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'statistik_manager_add_bookmark_script');

// Shortcode zum Anzeigen der gespeicherten Beiträge
function statistik_manager_bookmarks_shortcode() {
    return statistik_manager_display_bookmarks();
}
add_shortcode('display_bookmarks', 'statistik_manager_bookmarks_shortcode');

// Shortcode zum Hinzufügen eines Beitrags als Lesezeichen
function statistik_manager_add_bookmark_shortcode() {
    ob_start();
    ?>
    <form method="POST" id="add-bookmark-form">
        <div class="form-field">
            <label for="bookmark_name"><?php _e('Gib den Namen deines Lesezeichens ein:', 'statistik-manager'); ?></label>
            <input type="text" name="bookmark_name" id="bookmark_name" required />
        </div>
        <div class="form-field">
            <label for="bookmark_url"><?php _e('Gib die URL des Lesezeichens ein:', 'statistik-manager'); ?></label>
            <input type="url" name="bookmark_url" id="bookmark_url" required />
        </div>
        <div class="form-field">
            <button type="submit"><?php _e('Lesezeichen hinzufügen', 'statistik-manager'); ?></button>
        </div>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('add_bookmark', 'statistik_manager_add_bookmark_shortcode');


/*
* Statistik & Banner
*/


// Funktion zum Einbinden von CSS direkt im Plugin-Code
function statistik_manager_inline_styles() {
    ?>
    <style>
        /* CSS für das Statistik Manager Plugin */
        .statistik-manager-bookmarks {
            font-family: Arial, sans-serif;
            margin-top: 20px;
        }
        .statistik-manager-bookmarks ul {
            list-style-type: none;
            padding: 0;
        }
        .statistik-manager-bookmarks li {
            margin-bottom: 10px;
        }
        .statistik-manager-bookmarks a {
            text-decoration: none;
            color: #0073aa;
            font-size: 1.6em; /* Größerer Text für den Titel */
        }
        .statistik-manager-bookmarks a:hover {
            color: #005177;
        }
        .delete-bookmark-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            margin-right: 10px; /* Abstand zwischen Button und Titel */
            margin-bottom: 10px; /* Mehr Abstand nach unten */
        }
        .delete-bookmark-btn:hover {
            background-color: #d32f2f;
        }
        .form-field {
            margin-bottom: 15px;
        }
        .form-field label {
            display: block;
            margin-bottom: 5px;
        }
        .form-field input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-field button {
            background-color: #0073aa;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
        .form-field button:hover {
            background-color: #005177;
        }
    </style>
    <?php
}
add_action('wp_head', 'statistik_manager_inline_styles');

// Font Awesome einbinden
function statistik_manager_enqueue_fontawesome() {
    wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', [], null);
}
add_action('wp_enqueue_scripts', 'statistik_manager_enqueue_fontawesome');

// Sprachdateien laden
function statistik_manager_load_textdomain() {
    load_plugin_textdomain('statistik-manager', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'statistik_manager_load_textdomain');

// Funktion zum Abrufen der Statistiken
function statistik_manager_get_statistics() {
    global $wpdb;

    // Beiträge
    $posts_count = wp_count_posts()->publish;

    // Kommentare
    $comments_count = wp_count_comments()->total_comments;

    // Kategorien
    $selected_categories = get_option('statistik_manager_selected_categories', []);
    $categories_count = 0;

    if (!empty($selected_categories)) {
        $categories_count = count($selected_categories);
    } else {
        // Alle Kategorien zählen, wenn keine ausgewählt wurden
        $categories_count = wp_count_terms('category');
    }

    // Serien (angenommen, Serien sind benutzerdefinierte Taxonomie)
    $series_count = wp_count_terms('series'); // 'series' ist die benutzerdefinierte Taxonomie

    // Eröffnungsdatum
    $opening_date = get_option('statistik_manager_opening_date', '');

    return [
        'posts_count' => $posts_count,
        'comments_count' => $comments_count,
        'categories_count' => $categories_count,
        'series_count' => $series_count,
        'opening_date' => $opening_date
    ];
}

		// Banner-Funktion
		function statistik_manager_display_banner($position) {
			if (!get_option('statistik_manager_banner_enabled', 1)) {
				return;
			}

			$banner_text = get_option('statistik_manager_banner_text', 'Willkommen auf unserer Webseite!');
			$banner_color = get_option('statistik_manager_banner_color', '#0073aa');
			$banner_position = get_option('statistik_manager_banner_position', 'top');
			$font_size = get_option('statistik_manager_banner_font_size', 'medium');
			$banner_icon = get_option('statistik_manager_banner_icon', 'fas fa-info-circle'); // Standard-Icon
			$show_icon = get_option('statistik_manager_show_icon', 1); // Option zur Aktivierung des Icons

			// Schriftgröße je nach Auswahl setzen
			switch ($font_size) {
				case 'small':
					$font_size_css = '14px';
					break;
				case 'medium':
					$font_size_css = '18px';
					break;
				case 'large':
					$font_size_css = '24px';
					break;
				default:
					$font_size_css = '18px';
			}

			if ($banner_position !== $position) {
				return;
			}

			// Standard-Styles für das Banner
			$style = "background-color: " . esc_attr($banner_color) . ";
					  color: #fff;
					  text-align: center;
					  padding: 10px;
					  width: 100%;
					  height: 50px; /* Feste Höhe */
					  line-height: 30px; /* Zentrierte Schrift */
					  z-index: 9999;
					  position: fixed;
					  top: 0;
					  left: 0;";

			$text_style = "font-size: " . esc_attr($font_size_css) . ";"; 
			$icon_style = "font-size: 24px; margin-right: 8px;"; // Feste Größe für Icon

			echo '<div class="statistik-manager-banner" id="statistik-manager-banner" style="' . esc_attr($style) . '">';
			
			// Icon nur anzeigen, wenn gewünscht
			if ($show_icon && !empty($banner_icon)) {
				echo '<i class="' . esc_attr($banner_icon) . '" style="' . esc_attr($icon_style) . '"></i>';
			}

			echo '<span style="' . esc_attr($text_style) . '">' . esc_html($banner_text) . '</span>';
			echo '</div>';
		}

		// Funktion für das Banner im Header (nach <body>)
		function statistik_manager_display_banner_header() {
			add_action('wp_body_open', function() {
				statistik_manager_display_banner('top');
			});
		}

		// Falls `wp_body_open` nicht unterstützt wird, als Fallback `wp_footer` nutzen
		function statistik_manager_display_banner_header_fallback() {
			add_action('wp_footer', function() {
				statistik_manager_display_banner('top');
			}, 5);
		}

		// Funktion für das Banner im Footer
		function statistik_manager_display_banner_footer() {
			add_action('wp_footer', function() {
				statistik_manager_display_banner('bottom');
			}, 10);
		}

		// Banner laden (Header mit Fallback)
		if (function_exists('wp_body_open')) {
			statistik_manager_display_banner_header();
		} else {
			statistik_manager_display_banner_header_fallback();
		}

		// Fix: Admin-Leiste (Wenn Admin angemeldet ist, Banner nach unten verschieben)
		function statistik_manager_admin_bar_fix() {
			if (is_admin_bar_showing()) {
				echo '<style>
					#statistik-manager-banner {
						top: 32px !important; /* Admin-Leiste ausgleichen */
					}
					body {
						padding-top: 82px !important; /* Extra Platz für Admin-Leiste */
					}
				</style>';
			} else {
				echo '<style>
					body {
						padding-top: 70px !important; /* Standard Abstand */
					}
				</style>';
			}
		}
		add_action('wp_head', 'statistik_manager_admin_bar_fix');

// Shortcode für die Anzeige der Statistiken
function statistik_manager_shortcode() {
    $statistics = statistik_manager_get_statistics();
    $webseitenname = get_bloginfo('name');

    $output = '<div class="statistik-manager">';
    $output .= '<h3>Statistikübersicht</h3>';
    $output .= '<div class="statistik-items">';

    if (get_option('statistik_manager_show_posts')) {
        $output .= '<div class="stat-item"><i class="fas fa-file-alt"></i>';
        $output .= '<p><strong>Beiträge:</strong></br> ' . $statistics['posts_count'] . '</p></div>';
    }
    if (get_option('statistik_manager_show_comments')) {
        $output .= '<div class="stat-item"><i class="fas fa-comments"></i>';
        $output .= '<p><strong>Kommentare:</strong></br> ' . $statistics['comments_count'] . '</p></div>';
    }
    if (get_option('statistik_manager_show_categories')) {
        $output .= '<div class="stat-item"><i class="fas fa-th-list"></i>';
        $output .= '<p><strong>Kategorien:</strong></br> ' . $statistics['categories_count'] . '</p></div>';
    }
    if (get_option('statistik_manager_show_series')) {
        $output .= '<div class="stat-item"><i class="fas fa-tv"></i>';
        $output .= '<p><strong>Serien:</strong></br> ' . $statistics['series_count'] . '</p></div>';
    }
    $output .= '</div>';

    // Eröffnungsdatum anzeigen, wenn gesetzt
    if (!empty($statistics['opening_date'])) {
        $formatted_date = date('d.m.Y', strtotime($statistics['opening_date']));
        $output .= '<div class="stat-opening-date">';
        $output .= '<i class="fas fa-calendar-alt"></i>'; 
        $output .= '<p><strong>' . sprintf(__('%s wurde am %s eröffnet.', 'statistik-manager'), esc_html($webseitenname), esc_html($formatted_date)) . '</strong></p>';
        $output .= '</div>';
    }

    $output .= '</div>';
    return $output;
}

add_shortcode('statistik_manager', 'statistik_manager_shortcode');

// Admin-Panel CSS einbinden, nur auf der Plugin-Seite
function statistik_manager_enqueue_admin_styles($hook) {
    if ($hook === 'toplevel_page_statistik_manager') {
        wp_enqueue_style('statistik-manager-admin-style', plugins_url('css/admin-style.css', __FILE__));
    }
}
add_action('admin_enqueue_scripts', 'statistik_manager_enqueue_admin_styles');

// Frontend CSS nur einbinden, wenn der Shortcode verwendet wird
function statistik_manager_enqueue_frontend_styles() {
    if (has_shortcode(get_post()->post_content, 'statistik_manager')) {
        wp_enqueue_style('statistik-manager-frontend-style', plugins_url('css/style.css', __FILE__));
    }
}
add_action('wp_enqueue_scripts', 'statistik_manager_enqueue_frontend_styles');

function statistik_manager_menu() {
    add_menu_page(
        'WP Stat & Notice', // Ändern Sie den Seitentitel
        'WP Stat & Notice', // Ändern Sie die Menübezeichnung
        'manage_options',    // Berechtigungen
        'statistik_manager', // Menü-Slug
        'statistik_manager_options_page', // Callback-Funktion
        'dashicons-chart-pie' // Dashicon-Icon
    );
}
add_action('admin_menu', 'statistik_manager_menu');

// Funktion für die Plugin-Optionen-Seite
function statistik_manager_options_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('WP Stat & Notice Einstellungen', 'statistik-manager'); ?></h1>

        <div class="statistik-manager-logo">
            <img src="https://m-viper.de/img/logo.png" alt="Dein Logo" style="max-width: 200px;"/>
        </div>

        <div class="statistik-manager-content">
            <div class="statistik-manager-settings">
                <form method="post" action="options.php">
                    <?php
                    settings_fields('statistik_manager_settings_group');
                    do_settings_sections('statistik_manager');
                    ?>
                    <h2><?php _e('Statistiken anzeigen', 'statistik-manager'); ?></h2>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php _e('Anzeigen', 'statistik-manager'); ?></th>
                            <td>
                                <input type="checkbox" name="statistik_manager_show_posts" value="1" <?php checked(get_option('statistik_manager_show_posts'), 1); ?> />
                                <label for="statistik_manager_show_posts"><?php _e('Beiträge anzeigen', 'statistik-manager'); ?></label><br>
                                <input type="checkbox" name="statistik_manager_show_comments" value="1" <?php checked(get_option('statistik_manager_show_comments'), 1); ?> />
                                <label for="statistik_manager_show_comments"><?php _e('Kommentare anzeigen', 'statistik-manager'); ?></label><br>
                                <input type="checkbox" name="statistik_manager_show_categories" value="1" <?php checked(get_option('statistik_manager_show_categories'), 1); ?> />
                                <label for="statistik_manager_show_categories"><?php _e('Kategorien anzeigen', 'statistik-manager'); ?></label><br>
                                <input type="checkbox" name="statistik_manager_show_series" value="1" <?php checked(get_option('statistik_manager_show_series'), 1); ?> />
                                <label for="statistik_manager_show_series"><?php _e('Serien anzeigen', 'statistik-manager'); ?></label>
                            </td>
                        </tr>
                    </table>

                    <h2><?php _e('Kategorien auswählen', 'statistik-manager'); ?></h2>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php _e('Angezeigte Kategorien', 'statistik-manager'); ?></th>
                            <td>
                                <?php
                                $categories = get_terms(array(
                                    'taxonomy' => 'category',
                                    'orderby' => 'name',
                                    'order' => 'ASC',
                                    'hide_empty' => false,
                                ));

                                if (!empty($categories) && !is_wp_error($categories)) :
                                    $selected_categories = get_option('statistik_manager_selected_categories', []);
                                    ?>
                                    <select name="statistik_manager_selected_categories[]" multiple="multiple" style="width: 300px; height: 150px;">
                                        <?php foreach ($categories as $category) : ?>
                                            <option value="<?php echo esc_attr($category->term_id); ?>"
                                                <?php echo in_array($category->term_id, $selected_categories) ? 'selected' : ''; ?>>
                                                <?php echo esc_html($category->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                    <h2><?php _e('Eröffnungsdatum der Webseite', 'statistik-manager'); ?></h2>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php _e('Eröffnungsdatum', 'statistik-manager'); ?></th>
                            <td>
                                <input type="date" name="statistik_manager_opening_date" value="<?php echo esc_attr(get_option('statistik_manager_opening_date')); ?>" />
                            </td>
                        </tr>
                    </table>

                    <h2>Banner-Einstellungen</h2>
				<table class="form-table">
					<tr>
						<th>Banner anzeigen</th>
						<td>
							<input type="checkbox" name="statistik_manager_banner_enabled" value="1" <?php checked(get_option('statistik_manager_banner_enabled', 1)); ?> />
							<label for="statistik_manager_banner_enabled">Banner aktivieren</label>
						</td>
					</tr>
					<tr>
						<th>Banner Text</th>
						<td><input type="text" name="statistik_manager_banner_text" value="<?php echo esc_attr(get_option('statistik_manager_banner_text', 'Willkommen auf unserer Webseite!')); ?>" /></td>
					</tr>
					<tr>
						<th>Banner Farbe</th>
						<td><input type="color" name="statistik_manager_banner_color" value="<?php echo esc_attr(get_option('statistik_manager_banner_color', '#0073aa')); ?>" /></td>
					</tr>
					<tr>
						<th>Banner Position</th>
						<td>
							<select name="statistik_manager_banner_position">
								<option value="top" <?php selected(get_option('statistik_manager_banner_position'), 'top'); ?>>Oben</option>
								<option value="bottom" <?php selected(get_option('statistik_manager_banner_position'), 'bottom'); ?>>Unten</option>
							</select>
						</td>
					</tr>
					<tr>
					<tr>
					<th>Icon auswählen</th>
					<td>
						<select name="statistik_manager_banner_icon">
							<option value="fas fa-info-circle" <?php selected(get_option('statistik_manager_banner_icon'), 'fas fa-info-circle'); ?>>ℹ️ Info</option>
							<option value="fas fa-exclamation-triangle" <?php selected(get_option('statistik_manager_banner_icon'), 'fas fa-exclamation-triangle'); ?>>⚠️ Warnung</option>
							<option value="fas fa-bell" <?php selected(get_option('statistik_manager_banner_icon'), 'fas fa-bell'); ?>>🔔 Benachrichtigung</option>
							<option value="fas fa-thumbs-up" <?php selected(get_option('statistik_manager_banner_icon'), 'fas fa-thumbs-up'); ?>>👍 Daumen hoch</option>
							<option value="">Kein Icon</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>Icon anzeigen</th>
					<td>
						<input type="checkbox" name="statistik_manager_show_icon" value="1" <?php checked(get_option('statistik_manager_show_icon', 1)); ?> />
						<label for="statistik_manager_show_icon">Icon anzeigen</label>
					</td>
				</tr>
				<tr>
					<th>Schriftgröße</th>
					<td>
						<select name="statistik_manager_banner_font_size">
							<option value="small" <?php selected(get_option('statistik_manager_banner_font_size'), 'small'); ?>>Klein</option>
							<option value="medium" <?php selected(get_option('statistik_manager_banner_font_size'), 'medium'); ?>>Mittel</option>
							<option value="large" <?php selected(get_option('statistik_manager_banner_font_size'), 'large'); ?>>Groß</option>
						</select>
					</td>
				</tr>
				</table>

                    <?php submit_button(); ?>
                </form>
            </div>

            <!-- Box mit weiteren Plugins -->
<div class="statistik-manager-advertisement">
<div class="statistik-manager-plugins">
    <h3><?php _e('Weitere Plugins', 'statistik-manager'); ?></h3>
    <ul>
        <li><a href="https://git.viper.ipv64.net/M_Viper/wp-multi">WP-Multi</a></li>
        <li><a href="https://git.viper.ipv64.net/M_Viper/wordpress-top-3">Top 3 Beiträge</a></li>
    </ul>
</div>

<!-- Nützliche Informationen Box -->
<div class="statistik-manager-advertisement">
    <h3>Kurzanleitung für den Statistik Manager</h3>
    <p>Verwenden Sie den Statistik Manager, um eine benutzerdefinierte Statistik Box auf Ihrer Website anzuzeigen und wichtige Statistiken zu verfolgen. Hier ist eine kurze Anleitung:</p>
    <ol>
        <li><strong>Fügen Sie den Shortcode ein:</strong> Um die Statistik Box anzuzeigen, fügen Sie den folgenden Shortcode an der gewünschten Stelle in Ihrem Beitrag oder Ihrer Seite ein: <code>[statistik_manager]</code>.</li>
        <li><strong>Statistiken anzeigen:</strong> Die Statistik Box zeigt automatisch verschiedene Statistiken an. Sie können folgende Statistiken anzeigen lassen:
            <ul>
                <li><strong>Beiträge:</strong> Zeigt die Gesamtzahl der veröffentlichten Beiträge auf Ihrer Webseite.</li>
                <li><strong>Kommentare:</strong> Zeigt die Gesamtzahl der eingegangenen Kommentare auf Ihren Beiträgen.</li>
                <li><strong>Kategorien:</strong> Zeigt die Anzahl der erstellten Kategorien auf Ihrer Webseite.</li>
                <li><strong>Serien:</strong> Zeigt die Gesamtzahl der Serien, falls Ihre Seite Serieninhalte enthält.</li>
            </ul>
        </li>
        <li><strong>Auswahl der zu zählenden Kategorien:</strong> In den Plugin-Einstellungen können Sie auswählen, welche Kategorien in die Statistik einbezogen werden sollen, z. B. Kommentare, Beiträge oder benutzerdefinierte Kategorien.</li>
        <li><strong>Eröffnungsdatum der Webseite:</strong> Das Eröffnungsdatum Ihrer Website wird automatisch in der Statistik Box angezeigt, damit Besucher sehen können, wie lange Ihre Seite bereits online ist.</li>
        <li><strong>Banner hinzufügen:</strong> Sie können in den Plugin-Einstellungen auch ein Banner für die Statistik Box hinzufügen, das individuell angepasst werden kann (z. B. als Werbung oder für besondere Hinweise).</li>
    </ol>
    <p>Die Statistik Box wird an der Stelle angezeigt, an der der Shortcode eingefügt wurde. Alle Statistiken und Inhalte können jederzeit über die Plugin-Einstellungen angepasst werden.</p>
    <p>Bei Fragen oder Problemen können Sie sich jederzeit an uns wenden!</p>

    <h3>Kurzanleitung zur Lesezeichen-Verwaltung</h3>
    <p>Zusätzlich zur Anzeige von Statistiken können Sie auch eine benutzerdefinierte Liste von Lesezeichen für Ihre Gäste verwalten. Hier sind die wichtigen Schritte:</p>
    <ol>
        <li><strong>Fügen Sie den Shortcode für Lesezeichen ein:</strong> Um die Lesezeichen-Liste anzuzeigen, fügen Sie den folgenden Shortcode an der gewünschten Stelle in Ihrem Beitrag oder Ihrer Seite ein: <code>[display_bookmarks]</code>.</li>
        <li><strong>Lesezeichen hinzufügen:</strong> Besucher können Lesezeichen zu Ihren Seiten hinzufügen. Diese erscheinen automatisch in der Liste der gespeicherten Lesezeichen. Um ein Lesezeichen hinzuzufügen, müssen sie den Shortcode <code>[add_bookmark]</code> verwenden, der ein Formular zum Speichern eines Lesezeichens anzeigt.</li>
        <li><strong>Lesezeichen löschen:</strong> Sie können Lesezeichen jederzeit löschen, indem Sie auf den „Lesezeichen Löschen“-Button neben dem jeweiligen Eintrag klicken. Nur der Besitzer des Lesezeichens kann es löschen.</li>
    </ol>
    <p>Wenn Sie Fragen oder Probleme haben, wenden Sie sich an uns!</p>
</div>





</div>
    </div>
    <?php
}

// Optionen registrieren
function statistik_manager_register_settings() {
    register_setting('statistik_manager_settings_group', 'statistik_manager_show_posts');
    register_setting('statistik_manager_settings_group', 'statistik_manager_show_comments');
    register_setting('statistik_manager_settings_group', 'statistik_manager_show_categories');
    register_setting('statistik_manager_settings_group', 'statistik_manager_show_series');
    register_setting('statistik_manager_settings_group', 'statistik_manager_selected_categories');
    register_setting('statistik_manager_settings_group', 'statistik_manager_opening_date');
    register_setting('statistik_manager_settings_group', 'statistik_manager_banner_text');
	register_setting('statistik_manager_settings_group', 'statistik_manager_banner_color');
	register_setting('statistik_manager_settings_group', 'statistik_manager_banner_position');
	register_setting('statistik_manager_settings_group', 'statistik_manager_banner_enabled');
	register_setting('statistik_manager_settings_group', 'statistik_manager_banner_icon');
    register_setting('statistik_manager_settings_group', 'statistik_manager_show_icon');
    register_setting('statistik_manager_settings_group', 'statistik_manager_banner_font_size');

}
add_action('admin_init', 'statistik_manager_register_settings');

// Standardwerte setzen
function statistik_manager_set_default_options() {
    if (get_option('statistik_manager_show_posts') === false) {
        update_option('statistik_manager_show_posts', 1);
    }
    if (get_option('statistik_manager_show_comments') === false) {
        update_option('statistik_manager_show_comments', 1);
    }
    if (get_option('statistik_manager_show_categories') === false) {
        update_option('statistik_manager_show_categories', 1);
    }
    if (get_option('statistik_manager_show_series') === false) {
        update_option('statistik_manager_show_series', 1);
    }
    if (get_option('statistik_manager_selected_categories') === false) {
        update_option('statistik_manager_selected_categories', []);
    }
	if (get_option('statistik_manager_banner_enabled') === false) {
        update_option('statistik_manager_banner_enabled', 1);
    }
	if (get_option('statistik_manager_banner_font_size') === false) {
    update_option('statistik_manager_banner_font_size', 'medium');
}

}
add_action('admin_init', 'statistik_manager_set_default_options');
