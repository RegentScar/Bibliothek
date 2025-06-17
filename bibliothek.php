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

// Gültige Spalten für die Sortierung definieren
$valid_columns = ['Title', 'autor', 'kategorie'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $valid_columns) ? $_GET['sort'] : 'Title';

// Suchbegriff aus der URL abrufen und Bedingung erstellen
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = $search ? "WHERE b.Title LIKE CONCAT('%', ?, '%') OR b.autor LIKE CONCAT('%', ?, '%') OR b.Beschreibung LIKE CONCAT('%', ?, '%')" : '';

// Paginierungsparameter abrufen
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20; // Anzahl der Einträge pro Seite
$offset = ($page - 1) * $limit;

// SQL-Abfrage für die Gesamtanzahl der Ergebnisse vorbereiten
$total_sql = "
    SELECT COUNT(*) AS total
    FROM buecher b
    LEFT JOIN kategorien k ON b.kategorie = k.ID
    $search_condition
";
$total_stmt = $conn->prepare($total_sql);
if ($search) {
    $total_stmt->bind_param("sss", $search, $search, $search);
}
$total_stmt->execute();
$total_result = $total_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_result / $limit);

// SQL-Abfrage für die Bücherliste vorbereiten
$sql = "
    SELECT b.id, b.Title, b.autor, k.kategorie AS kategorie
    FROM buecher b
    LEFT JOIN kategorien k ON b.kategorie = k.ID
    $search_condition
    ORDER BY $sort
    LIMIT ?, ?
";

// Prepared Statement erstellen
$stmt = $conn->prepare($sql);

// Parameter binden, falls ein Suchbegriff vorhanden ist
if ($search) {
    $stmt->bind_param("sssii", $search, $search, $search, $offset, $limit);
} else {
    $stmt->bind_param("ii", $offset, $limit);
}

// Abfrage ausführen und Ergebnisse abrufen
$stmt->execute();
$result = $stmt->get_result();

// Funktion zur Erstellung der Paginierungslinks
function getPaginationLinks($search, $sort, $page, $total_pages) {
    $links = '';
    if ($page > 1) {
        $links .= '<a href="?search=' . urlencode($search) . '&sort=' . $sort . '&page=1">Zur ersten Seite</a>';
        $links .= '<a href="?search=' . urlencode($search) . '&sort=' . $sort . '&page=' . ($page - 1) . '">Zurück</a>';
    }
    $links .= '<span>Seite ' . $page . ' von ' . $total_pages . '</span>';
    if ($page < $total_pages) {
        $links .= '<a href="?search=' . urlencode($search) . '&sort=' . $sort . '&page=' . ($page + 1) . '">Vor</a>';
        $links .= '<a href="?search=' . urlencode($search) . '&sort=' . $sort . '&page=' . $total_pages . '">Zur letzten Seite</a>';
    }
    return $links;
}

// Funktion zur Darstellung eines Buches
function renderBookItem($row) {
    return "
        <div class='book-item'>
            <h2>" . nl2br($row["Title"]) . "</h2>
            <p><strong>Autor:</strong> " . nl2br($row["autor"]) . "</p>
            <p><strong>Kategorie:</strong> " . (!empty($row["kategorie"]) ? nl2br($row["kategorie"]) : "Unbekannt") . "</p>
            <a href='details.php?id=" . $row['id'] . "'><button>Details</button></a>
        </div>
    ";
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <!-- Metadaten und Verweise auf CSS -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bibliothek</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <!-- Navigationsleiste -->
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
        <h1>Unsere Bibliothek</h1>
        <div class="controls">
            <!-- Such- und Sortierformular -->
            <form method="GET" action="bibliothek.php">
                <div class="search-sort">
                    <div class="search-bar">
                        <label for="search">Suchen:</label>
                        <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Buchtitel, Autor oder Beschreibung">
                    </div>
                    <div class="sort-options">
                        <label for="sort">Sortieren:</label>
                        <select name="sort" id="sort" onchange="this.form.submit()">
                            <option value="Title" <?php if ($sort == 'Title') echo 'selected'; ?>>Titel</option>
                            <option value="autor" <?php if ($sort == 'autor') echo 'selected'; ?>>Autor</option>
                            <option value="kategorie" <?php if ($sort == 'kategorie') echo 'selected'; ?>>Kategorie</option>
                        </select>
                    </div>
                    <button type="submit">Anwenden</button>
                </div>
                <input type="hidden" name="page" value="1">
            </form>
        </div>
        <div class="book-list">
            <!-- Liste der Bücher anzeigen -->
            <?php while ($row = $result->fetch_assoc()): ?>
                <?= renderBookItem($row); ?>
            <?php endwhile; ?>
            <?php if ($result->num_rows === 0): ?>
                <p>Keine Bücher gefunden</p>
            <?php endif; ?>
        </div>
        <div class="pagination">
            <!-- Paginierungslinks anzeigen -->
            <?= getPaginationLinks($search, $sort, $page, $total_pages); ?>
        </div>
    </main>
    <footer>
        <!-- Fusszeile -->
        <p>&copy; 2025 Bibliothek. Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>

<?php
// Ressourcen freigeben und Verbindung schliessen
$stmt->close();
$total_stmt->close();
$conn->close();
?>