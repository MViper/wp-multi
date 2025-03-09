# WP-Multi Plugin für WordPress

**WP-Multi** ist ein multifunktionales WordPress-Plugin, das eine breite Palette von leistungsstarken Funktionen zur Verwaltung deiner Website bietet. Es umfasst Statistiken, benutzerdefinierte Admin-Links, Schutzmechanismen gegen Spam und Brute-Force-Angriffe, sowie viele nützliche Tools für die Verbesserung des Benutzererlebnisses und der Website-Verwaltung.

Das Plugin ist darauf ausgelegt, dir zu helfen, eine detaillierte Übersicht über die Interaktionen auf deiner Seite zu erhalten, Beiträge effizient zu verwalten und gleichzeitig die Sicherheit und das Benutzererlebnis zu optimieren.

## Funktionen

### 1. **Statistik-Übersicht**
- **Zeigt detaillierte Statistiken an:**
  - Anzahl der Beiträge
  - Anzahl der Kommentare
  - Anzahl der Kategorien
  - Anzahl der Serien
- **Shortcode für die Anzeige der Statistik:** `[statistik_manager]`

### 2. **Benachrichtigungen auf Telegram und Discord**
- **Telegram & Discord Benachrichtigungen:**
  - Du erhältst Benachrichtigungen in Echtzeit über neue Beiträge auf deiner Website. So bleibst du immer auf dem Laufenden.
  - Unterstützt sowohl Telegram als auch Discord.

### 3. **Custom Admin Links**
- **Fügt benutzerdefinierte Links im WordPress-Adminbereich hinzu:**
  - Erstelle und verwalte eigene Links, die direkt im Admin-Dashboard angezeigt werden, um den Verwaltungsaufwand zu reduzieren und deine Arbeit zu optimieren.

### 4. **Gast-Autor**
- **Gast-Autor Feature:**
  - Ermögliche es, einen Gast-Autornamen für Beiträge festzulegen. Der Name wird im Frontend des Beitrags angezeigt und dient dazu, die Quellen klar darzustellen.
  
### 5. **Beitrags-Report**
- **Melden unangemessener oder falscher Beiträge:**
  - Benutzer können Beiträge melden, die unangemessen oder fehlerhaft sind. Du erhältst eine Benachrichtigung im Admin-Panel und kannst sofort handeln.
  - Der Admin kann alle gemeldeten Beiträge im Adminbereich einsehen und gegebenenfalls Maßnahmen ergreifen.

### 6. **Custom Textbox**
- **Fügt benutzerdefinierte Textboxen hinzu:**
  - Erstelle Textboxen mit vordefinierten Inhalten, die in jedem Beitrag angezeigt werden.
  - Diese Textboxen können leicht im Admin-Panel konfiguriert werden.

### 7. **Banner für Nachrichten**
- **Banner für Ankündigungen und Informationen:**
  - Zeige wichtige Nachrichten als Banner im Frontend deiner Website an. Die Banner können entweder im Header oder im Footer angezeigt werden.

### 8. **Admin Dashboard Update Anzeige**
- **Plugin-Update Benachrichtigung im Admin-Dashboard:**
  - Zeigt im Admin-Dashboard an, ob eine neue Version von **WP-Multi** verfügbar ist und ob ein Update notwendig ist.

### 9. **Gast Lesezeichen**
- **Lesezeichen für Gäste:**
  - Gäste können Beiträge mit einem Lesezeichen versehen, das über Cookies gespeichert wird.
  - **Shortcodes:**
    - `[add_bookmark]` – Fügt ein Lesezeichen hinzu.
    - `[display_bookmarks]` – Zeigt alle Lesezeichen des Besuchers an.

### 10. **Benutzer für Kommentare sperren**
- **Verhindert störende Kommentare:**
  - Sperre bestimmte Benutzer vom Kommentieren, indem du ihren Benutzernamen, ihre IP-Adresse oder E-Mail-Adresse eingibst. Diese Benutzer können dann keine weiteren Kommentare abgeben.

### 11. **Kommentar Filter**
- **Automatischer Filter für schadhafte Inhalte:**
  - Verhindert das Senden von bestimmten Informationen in Kommentaren wie:
    - Rufnummern
    - E-Mail-Adressen
    - URLs
    - IP-Adressen
    - Schimpfwörtern
  - Diese Elemente werden automatisch durch `*` ersetzt, wenn sie gesendet werden.

### 12. **Custom Shortcodes**
- **Erstellung benutzerdefinierter Shortcodes:**
  - Du kannst benutzerdefinierte Shortcodes erstellen, die im WordPress-Editor per Auswahl eingefügt werden können, um die Flexibilität und Funktionalität deiner Seite zu erweitern.

### 13. **Besucher Analytics**
- **Verfolge die beliebtesten Beiträge:**
  - Sieh dir an, welche Beiträge am häufigsten angesehen oder kommentiert werden.
  - Es wird angezeigt:
    - Art der Aktion (View oder Comment)
    - Titel des Beitrags
    - Beitrag-ID
    - Zeitstempel der Aktion

### 14. **Pinwand für Administratoren**
- **Nachrichten und Ankündigungen für Administratoren:**
  - Erstelle, bearbeite und lösche Nachrichten auf der Pinwand im Admin-Panel. Diese Nachrichten können für andere Administratoren und Benutzer sichtbar sein.

### 15. **Schutz vor Brute-Force-Angriffen**
- **Sperrung nach Fehlversuchen:**
  - Alle fehlgeschlagenen Login-Versuche werden protokolliert. Nach fünf fehlgeschlagenen Versuchen wird der Account automatisch gesperrt und eine E-Mail-Benachrichtigung an den Administrator und den betroffenen Benutzer gesendet.

### 16. **Anti-Spam Honey**
- **Automatischer Spam-Schutz:**
  - Das Plugin erkennt Spam, Bots und andere unerwünschte Aktivitäten und blockiert diese automatisch.
  - Eine detaillierte Übersicht über blockierte Inhalte wird im Admin-Panel bereitgestellt.

### 17. **Auto Tagging**
- **Automatisches Hinzufügen von Tags:**
  - Wenn ein Beitrag keine Tags hat, fügt das Plugin automatisch relevante Tags hinzu.
  - Du kannst im Admin-Panel eine Liste von unerwünschten Tags definieren, die das Plugin niemals hinzufügen soll.

### 18. **Login Deaktivieren**
- **Deaktiviere das Login für bestimmte Benutzer:**
  - Du kannst das Login für bestimmte Benutzer deaktivieren, um unbefugten Zugriff zu verhindern. Diese Funktion kann direkt im Benutzerprofil aktiviert werden.

### 19. **Text Copy Schutz**
- **Schutz vor unerlaubtem Kopieren:**
  - Verhindert das Kopieren von Texten auf deiner Website, um die Inhalte zu schützen.

### 20. **Sperre Trash-Mail-Adressen**
- **Blockiere Trash-Mail-Adressen in Kommentaren:**
  - Trash-Mail-Adressen werden in Kommentaren blockiert. Die Liste der blockierten Domains kann nur vom Entwickler des Plugins erweitert werden.

### 21. **Inhaltsverzeichnis für Beiträge**
- **Erstelle ein Inhaltsverzeichnis für Beiträge:**
  - Erstelle automatisch ein alphabetisches Inhaltsverzeichnis aller Beiträge auf deiner Website.
  - **Shortcode:** `[alphabetical_index]`

---

## Installation

### Schritt 1: Plugin herunterladen
Lade das **WP-Multi Plugin** als ZIP-Datei von GitHub oder deinem bevorzugten Source-Repository herunter.

### Schritt 2: Plugin installieren
1. Gehe in deinem WordPress-Adminbereich zu **Plugins** > **Installieren**.
2. Klicke auf **Plugin hochladen** und wähle die ZIP-Datei des Plugins aus.
3. Klicke auf **Jetzt installieren** und dann auf **Aktivieren**.

### Schritt 3: Plugin konfigurieren
Nach der Aktivierung kannst du das Plugin über das **WP-Multi** Menü im Adminbereich konfigurieren. Passe die Einstellungen nach deinen Bedürfnissen an, um alle Funktionen optimal zu nutzen.

---

## Verwendung

Nach der Installation kannst du die Funktionen und Shortcodes im Adminbereich oder direkt in deinen Beiträgen verwenden. Hier sind einige nützliche Shortcodes:

- **Statistik anzeigen:** `[statistik_manager]`
- **Lesezeichen hinzufügen:** `[add_bookmark]`
- **Lesezeichen anzeigen:** `[display_bookmarks]`
- **Inhaltsverzeichnis anzeigen:** `[alphabetical_index]`

Im Adminbereich kannst du auch die benutzerdefinierten Funktionen wie die Textboxen, Kommentar-Filter und Anti-Spam-Maßnahmen konfigurieren.

---

## Lizenz

Dieses Plugin ist unter der [GPL-2.0 Lizenz](https://www.gnu.org/licenses/old-licenses/gpl-2.0.de.html) veröffentlicht.

---

## Unterstützung

Wenn du Fragen hast oder auf Probleme stößt, eröffne ein **Issue** auf GitHub. Wir sind gerne bereit, dir zu helfen!

---

Vielen Dank, dass du **WP-Multi** verwendest! Wir hoffen, dass es dir hilft, deine WordPress-Website effizient zu verwalten und zu schützen.
