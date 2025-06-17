<?php
// Datenbankverbindungsdetails
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Books";

// Verbindung zur Datenbank herstellen
$conn = new mysqli($servername, $username, $password, $dbname);

// Überprüfen, ob die Verbindung erfolgreich ist
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// ID aus der URL abrufen und validieren
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

// SQL-Abfrage vorbereiten, um Buchdetails basierend auf der ID abzurufen
$sql = "
    SELECT b.Title, b.autor, b.Beschreibung, b.verfasser, b.verkauft, b.foto, 
           k.kategorie AS kategorie, 
           CASE 
               WHEN b.zustand = 'G' THEN 'Gut'
               WHEN b.zustand = 'M' THEN 'Mittel'
               WHEN b.zustand = 'S' THEN 'Schlecht'
               ELSE 'Unbekannt'
           END AS zustand
    FROM buecher b
    LEFT JOIN kategorien k ON b.kategorie = k.ID
    WHERE b.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Funktion zum Rendern der Buchdetails
function renderBookDetails($row) {
    // HTML-Ausgabe für die Buchdetails
    return "
        <div class='book-details-box'>
            <div class='book-details'>
                <h2>{$row['Title']}</h2>
                <p><strong>Autor:</strong> {$row['autor']}</p>
                <p><strong>Kategorie:</strong> " . (!empty($row['kategorie']) ? $row['kategorie'] : "Unbekannt") . "</p>
                <p><strong>Zustand:</strong> " . (!empty($row['zustand']) ? $row['zustand'] : "Unbekannt") . "</p>
                <p><strong>Beschreibung:</strong> {$row['Beschreibung']}</p>
                <p><strong>Verfasser:</strong> {$row['verfasser']}</p>
                <p><strong>Verkauft:</strong> " . ($row['verkauft'] ? 'Ja' : 'Nein') . "</p>
            </div>
        </div>
    ";
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <!-- Meta-Informationen und Verlinkung der CSS-Datei -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buchdetails</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <!-- Navigation mit Links zu verschiedenen Seiten -->
        <nav>
            <ul>
                <li class="logo"><a href="index.html"><img src="../Website mit SQL/images/Buch.png" alt="Logo"></a></li>
                <li><a href="index.html">Startseite</a></li>
                <li><a href="bibliothek.php">Bibliothek</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <!-- Hauptinhalt: Buchdetails anzeigen -->
        <h1>Buchdetails</h1>
        <?php if ($result->num_rows > 0): ?>
            <!-- Buchdetails rendern, wenn Daten vorhanden sind -->
            <?= renderBookDetails($result->fetch_assoc()); ?>
        <?php else: ?>
            <!-- Nachricht anzeigen, wenn keine Daten gefunden wurden -->
            <p>Keine Details für dieses Buch gefunden.</p>
        <?php endif; ?>
        <!-- Zurück-Button -->
        <a href="javascript:history.back()" class="button">Zurück</a>
    </main>
    <footer>
        <!-- Footer-Inhalt -->
        <p>&copy; 2025 Bibliothek. Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>

<?php
// Datenbankverbindung schliessen
$conn->close();
?>