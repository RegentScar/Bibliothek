# Bibliotheksverwaltungssystem in PHP

Dieses Projekt ist ein webbasiertes Bibliotheksverwaltungssystem, entwickelt in PHP mit einer MySQL-Datenbank. Es ermöglicht die Verwaltung von Büchern und Kunden sowie die Administration von Benutzerkonten. Das System bietet Funktionen für Administratoren, um Bücher und Kunden hinzuzufügen, zu bearbeiten und zu löschen, sowie eine öffentliche Ansicht der Bibliotheksbestände. Benutzer können sich einloggen, um erweiterte Funktionen zu nutzen, wie z.B. die Verwaltung von Büchern oder das Ändern ihres Passworts.

## Funktionen

- **Benutzerverwaltung**: Registrierung und Login für Benutzer, mit Passwort-Hashing für Sicherheit.
- **Buchverwaltung**: Hinzufügen, Bearbeiten und Löschen von Büchern mit Details wie Titel, Autor, Kategorie, Zustand, Beschreibung, Verkaufsstatus und Käufernummer.
- **Kundenverwaltung**: Hinzufügen, Bearbeiten und Löschen von Kunden mit Details wie Name, Vorname, Geburtstag, Kunde-seit-Datum, E-Mail, Geschlecht und Präferenz für E-Mail-Kontakt.
- **Suche und Sortierung**: Suchfunktion für Bücher (nach Titel, Autor, Beschreibung) und Kunden (nach KID, Name, Vorname, E-Mail) mit Sortieroptionen.
- **Paginierung**: Anzeige von Büchern und Kunden in Seiten mit jeweils 20 Einträgen, inklusive Navigationslinks.
- **Admin-Bereich**: Geschützter Bereich für Administratoren mit erweiterten Funktionen wie Kunden- und Buchverwaltung sowie Passwortänderung.
- **Öffentliche Bibliotheksansicht**: Anzeige der verfügbaren Bücher für alle Besucher, mit detaillierten Buchinformationen.

## Voraussetzungen

- PHP 7.4 oder höher
- MySQL-Datenbank (z.B. MariaDB)
- Webserver (z.B. Apache oder Nginx)

## Installation

1. **Datenbank einrichten**:
   - Erstelle eine MySQL-Datenbank mit dem Namen `Books`.
   - Importiere die SQL-Datei.
2. **Datenbankverbindung konfigurieren**:
   - Passe die Verbindungsdetails (`$servername`, `$username`, `$password`, `$dbname`) an deine Datenbankumgebung an.
3. **Dateien auf den Webserver kopieren**:
   - Kopiere die Projektordnerstruktur auf deinen Webserver (z.B. in das `htdocs`-Verzeichnis von Apache).
4. **Webserver starten**:
   - Stelle sicher, dass dein Webserver läuft und PHP korrekt installiert ist.
5. **Zugriff auf die Anwendung**:
   - Öffne die Anwendung im Browser, z.B. `http://localhost/<projekt-ordner>/index.html`.

## Ausführen

- **Öffentliche Ansicht**: Öffne die `index.php` Datei und navigiere zur Bibliotheksseite, um eine Liste der Bücher zu sehen.
- **Login**: Gehe zu `login.php`, um dich als Benutzer oder Administrator anzumelden.
- **Admin-Bereich**: Nach dem Login als Admin (mit `admin = 1` in der `benutzer`-Tabelle) kannst du `admin.php` oder `kunden.php` aufrufen, um Bücher oder Kunden zu verwalten.

## Hinweise

- Die Anwendung verwendet `password_hash` und `password_verify` für sichere Passwortverarbeitung.
- Alle Eingaben werden mit `trim()` und `htmlspecialchars()` gegen XSS-Angriffe geschützt.
- Die Datenbankverbindung ist in jeder PHP-Datei separat definiert. Für eine Produktionsumgebung sollte eine zentrale Konfigurationsdatei verwendet werden.
- Der Pfad zum Logo (`../Website mit SQL/images/Buch.png`) muss ggf. angepasst werden, je nach Ordnerstruktur.
