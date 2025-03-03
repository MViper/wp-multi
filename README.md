# WP-Multi Plugin

Das **WP-Multi** Plugin für WordPress bietet eine leistungsstarke Sammlung von Funktionen, die speziell entwickelt wurden, um die Verwaltung und Personalisierung deiner Website zu verbessern. Mit diesem Plugin kannst du eine Vielzahl von Statistiken wie die Gesamtzahl von Beiträgen, Kommentaren und Kategorien auf einfache Weise anzeigen. Zudem hast du die Möglichkeit, benutzerdefinierte Banner zu erstellen, um Besucher gezielt auf wichtige Inhalte oder Aktionen hinzuweisen.

WP-Multi geht darüber hinaus und ermöglicht dir die Integration von Benachrichtigungen via Telegram und Discord für neue Beiträge, die Verwaltung von Gastautoren und die Erstellung benutzerdefinierter Admin-Links. Das Plugin umfasst außerdem fortschrittliche Funktionen zur Kommentarmoderation, wie das Sperren von Benutzern und das Blockieren unerwünschter Inhalte. Alles lässt sich bequem und flexibel direkt im WordPress-Dashboard anpassen, sodass du deine Website mit wenigen Klicks noch effizienter und benutzerfreundlicher gestalten kannst.

## Funktionen

### Beiträge
- **Custom Textbox**:
  - Fügt eine benutzerdefinierte Textbox am Ende eines Beitrags hinzu (z.B. für Copyright-Informationen).
  
### Sidebar
- **Pinwand**:
  - Ermöglicht das Teilen von Informationen im Admin-Bereich, um wichtige Mitteilungen oder Nachrichten direkt an Administratoren und Benutzer zu senden.
  
- **Custom Shortcodes**:
  - Benutzerdefinierte Shortcodes können per Auswahl im Editor eingefügt werden.
  
- **Beitrags Report**:
  - Nutzer können Beiträge melden.
  - Verfügbar über den Shortcode: `[report_button]`, um es in Widgets oder direkt in Beiträge einzufügen.

### Kommentare
- **Kommentar Filter**:
  - Blockiert Schimpfwörter, Telefonnummern, E-Mail-Adressen und URLs in Kommentaren.

### Benutzer
- **Blockierte IPs**:
  - Zeigt alle blockierten IPs an und ermöglicht das Verwalten dieser.

- **Benutzer Analytics**:
  - Zeigt eine Übersicht über die Benutzeraktivitäten, z.B. die Anzahl der Kommentare eines Benutzers.

- **Benutzer sperren**:
  - Sperrt Benutzer anhand von Namen, E-Mail-Adresse oder IP-Adresse für Kommentare.

- **GastAutoren**:
  - Ermöglicht es, den Namen des Gastautors anzugeben, der im Frontend angezeigt wird.
  - Verfolgt, wie viele Beiträge jeder Gastautor verfasst hat.

### WP Stats & Notice
- **Statistik anzeigen**:
  - Zeigt Statistiken über die Gesamtzahl der veröffentlichten Beiträge, Kommentare, Kategorien und Serien (falls eine benutzerdefinierte Taxonomie für Serien existiert).
  - Verfügbar über den Shortcode: `[statistik_manager]`.
  
- **Banner für Hinweise oder Nachrichten**:
  - Ermöglicht das Hinzufügen eines anpassbaren Banners auf der Website.
  - Nutze es für Neuigkeiten, Angebote oder andere wichtige Inhalte.
  - Anpassbare Textfarbe, Hintergrundfarbe und Position des Banners.

### Werkzeuge
- **Admin Links**:
  - Ermöglicht das Hinzufügen benutzerdefinierter Links im WordPress-Adminbereich.
  - Sowohl interne als auch externe Links können hinzugefügt werden.

### Notify
- **Telegram Benachrichtigung bei neuem Beitrag**:
  - Sendet eine Benachrichtigung an Telegram, wenn ein neuer Beitrag veröffentlicht wird.

- **Discord Benachrichtigung bei neuem Beitrag**:
  - Sendet eine Benachrichtigung an Discord, wenn ein neuer Beitrag veröffentlicht wird.

### Sicherheit
- **Schutz vor Brute-Force-Angriffen**:
  - Bietet Schutzmechanismen, die gegen Brute-Force-Angriffe auf deine Login-Seite vorgehen, um die Sicherheit deiner Website zu erhöhen.

- **Besucher Analytics**:
  - Zeigt die meistbesuchten Beiträge auf der Website an, sodass du Einblicke in die beliebtesten Inhalte bekommst.

## Installation

1. Lade das Plugin herunter und entpacke die ZIP-Datei.
2. Gehe in deinem WordPress-Dashboard zu **Plugins** > **Installieren** > **Plugin hochladen**.
3. Wähle die entpackte ZIP-Datei aus und klicke auf **Jetzt installieren**.
4. Aktiviere das Plugin nach der Installation.

## Verwendung

### Statistiken anzeigen

Um die Statistiken auf deiner Seite anzuzeigen, füge einfach den folgenden Shortcode in den Inhalt einer Seite oder eines Beitrags ein:

`[statistik_manager]`

Dieser Shortcode zeigt die verschiedenen Statistiken an, die im Admin-Bereich konfiguriert wurden.

### Banner anzeigen

Das Banner kann im Admin-Bereich konfiguriert werden und wird dann automatisch auf der Website angezeigt, basierend auf den konfigurierten Einstellungen.

### Eröffnungsdatum anzeigen

Im Admin-Bereich kannst du das Eröffnungsdatum deiner Website angeben. Wenn ein Datum eingetragen wurde, wird es zusammen mit der Statistik angezeigt. Falls kein Datum eingetragen ist, wird es nicht angezeigt.

### Beitrags Report anzeigen

Um den Report-Button in einem Beitrag oder Widget anzuzeigen, füge den Shortcode `[report_button]` an der gewünschten Stelle ein.

## Einstellungen

1. Gehe im WordPress-Dashboard zu **WP Stat & Notice** > **Einstellungen**.
2. Konfiguriere die gewünschten Optionen:
   - Statistiken (Beiträge, Kommentare, Kategorien, Serien)
   - Banner-Einstellungen (Text, Farben, Position)
   - Telegram und Discord Benachrichtigungen aktivieren
   - GastAutoren Einstellungen
   - Kommentar Sperren und Filter-Einstellungen
   - Besucher Analytics
   - Pinwand
   - Brute-Force-Schutz

## Optionen

### WP Stats & Notice
- **Beiträge anzeigen**: Zeigt die Gesamtzahl der veröffentlichten Beiträge.
- **Kommentare anzeigen**: Zeigt die Gesamtzahl der Kommentare.
- **Kategorien anzeigen**: Zeigt die Anzahl der Kategorien (oder nur die ausgewählten Kategorien).
- **Serien anzeigen**: Zeigt die Anzahl der Serien an (falls diese Taxonomie in deiner WordPress-Installation vorhanden ist).
- **Banner anzeigen**: Ermöglicht das Anzeigen eines anpassbaren Banners auf der Website.
- **Eröffnungsdatum der Webseite**: Ermöglicht das Hinzufügen eines Eröffnungsdatums, das unter den Statistiken angezeigt wird.

### Kommentare
- **Kommentar Filter**: Blockiert Schimpfwörter, URLs, E-Mail-Adressen und Telefonnummern.

### Benutzer
- **Blockierte IPs**: Zeigt blockierte IPs an und ermöglicht die Verwaltung dieser.
- **GastAutoren**: Zeigt die Anzahl der Beiträge eines Gastautors an.
- **Kommentar Sperren**: Sperrt Kommentare von bestimmten Nutzern basierend auf Namen, E-Mail-Adresse oder IP.
- **Benutzer Analytics**: Zeigt eine Übersicht der Benutzeraktivitäten, z.B. die Anzahl der Kommentare eines Benutzers.

### Sidebar
- **Pinwand**: Ermöglicht das Teilen von Nachrichten im Admin-Bereich.
- **Custom Shortcodes**: Benutzerdefinierte Shortcodes können im Editor eingefügt werden.
- **Beitrags Report**: Ermöglicht das Melden von Beiträgen durch die Benutzer.

### Sicherheit
- **Schutz vor Brute-Force-Angriffen**: Aktiviert Sicherheitsmaßnahmen gegen Brute-Force-Angriffe.

### Notify
- **Telegram Benachrichtigung**: Sende Benachrichtigungen an Telegram bei neuen Beiträgen.
- **Discord Benachrichtigung**: Sende Benachrichtigungen an Discord bei neuen Beiträgen.

## Screenshots

1. **Dashboard Ansicht** – Die Statistiken werden im Admin-Bereich angezeigt.
2. **Frontend Anzeige** – Die Statistiken und das Banner werden auf der Webseite angezeigt, wenn der Shortcode verwendet wird.
3. **Eröffnungsdatum** – Zeigt das Eröffnungsdatum der Webseite unter den Statistiken an, falls angegeben.
4. **Beitrags Report Button** – Zeigt den Button zum Melden von Beiträgen.

## Entwickler

- **Plugin Name**: WP Multi
- **Autor**: M_Viper
- **Website**: [https://m-viper.de](https://m-viper.de)
- **Gitea Repository**: [https://git.viper.ipv64.net/M_Viper/wp-multi](https://git.viper.ipv64.net/M_Viper/wp-multi)

## Lizenz

Dieses Plugin ist unter der [GPL-2.0 Lizenz](https://www.gnu.org/licenses/gpl-2.0.html) lizenziert.

## Contributing

Beiträge zum Plugin sind willkommen! Wenn du eine Idee für eine Verbesserung hast oder einen Fehler findest, kannst du einen **Issue** hier öffnen oder einen **Pull Request** einreichen.
