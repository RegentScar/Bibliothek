<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Prüfen, ob der Nutzer Admin ist
if ($_SESSION['admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Logout-Funktion
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Datenbankverbindungsdetails
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "books";

// Verbindung zur Datenbank herstellen
$conn = new mysqli($servername, $username, $password, $dbname);

// Überprüfen, ob die Verbindung erfolgreich ist
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Gültige Spalten für die Sortierung definieren
$valid_columns = ['kid', 'name', 'vorname', 'kunde_seit'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $valid_columns) ? $_GET['sort'] : 'kid';

// Suchbegriff aus der URL abrufen und Bedingung erstellen
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = $search ? "WHERE kid LIKE CONCAT('%', ?, '%') OR name LIKE CONCAT('%', ?, '%') OR vorname LIKE CONCAT('%', ?, '%') OR email LIKE CONCAT('%', ?, '%')" : '';

// Paginierungsparameter abrufen
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20; // Anzahl der Einträge pro Seite
$offset = ($page - 1) * $limit;

// SQL-Abfrage für die Gesamtanzahl der Ergebnisse vorbereiten
$total_sql = "
    SELECT COUNT(*) AS total
    FROM kunden
    $search_condition
";
$total_stmt = $conn->prepare($total_sql);
if ($search) {
    $total_stmt->bind_param("ssss", $search, $search, $search, $search);
}
$total_stmt->execute();
$total_result = $total_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_result / $limit);

// SQL-Abfrage für die Kundenliste vorbereiten
$sql = "
    SELECT kid, name, vorname, geburtstag, kunde_seit, email, geschlecht, kontaktpermail
    FROM kunden
    $search_condition
    ORDER BY $sort
    LIMIT ?, ?
";

// Prepared Statement erstellen
$stmt = $conn->prepare($sql);

// Parameter binden, falls ein Suchbegriff vorhanden ist
if ($search) {
    $stmt->bind_param("ssssii", $search, $search, $search, $search, $offset, $limit);
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

function renderCustomerItem($row) {
    return "
        <div class='book-item'>
            <h2>" . htmlspecialchars($row['vorname'] . ' ' . $row['name']) . "</h2>
            <p><strong>KID:</strong> " . htmlspecialchars($row['kid']) . "</p>
            <p><strong>Geburtstag:</strong> " . htmlspecialchars($row['geburtstag']) . "</p>
            <p><strong>Kunde seit:</strong> " . htmlspecialchars($row['kunde_seit']) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($row['email']) . "</p>
            <p><strong>Geschlecht:</strong> " . htmlspecialchars($row['geschlecht'] === 'M' ? 'Männlich' : ($row['geschlecht'] === 'F' ? 'Weiblich' : 'Divers')) . "</p>
            <p><strong>Kontakt per Mail:</strong> " . ($row['kontaktpermail'] ? 'Ja' : 'Nein') . "</p>
            <a href='mod_user.php?id=" . $row['kid'] . "'><button>Bearbeiten</button></a>
            <a href='#' onclick='this.nextElementSibling.submit(); return false;'>
                <button>Löschen</button>
            </a>
            <form method='POST' action='' style='display:none;'>
                <input type='hidden' name='delete_id' value='" . htmlspecialchars($row['kid']) . "'>
            </form>
        </div>
    ";
}

// Funktion zur Löschung von Kunden
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = filter_var($_POST['delete_id'], FILTER_VALIDATE_INT);
    if ($delete_id) {
        $delete_stmt = $conn->prepare("DELETE FROM kunden WHERE kid = ?");
        $delete_stmt->bind_param("i", $delete_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        // Seite neu laden, um die Änderungen anzuzeigen
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <!-- Metadaten und Verweise auf CSS -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kunden</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <!-- Navigationsleiste -->
        <nav>
            <ul>
                <li class="logo"><a href="index.html"><img src="../Website mit SQL/images/Buch.png" alt="Logo"></a></li>
                <li><a href="admin.php">Bibliothek</a></li>
                <li><a href="kunden.php">Kunden</a></li>
                <li><a href="passwort.php">Passwort ändern</a></li>
                <li><a href="admin.php?logout=1">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Kundenliste</h1>
        <div class="controls">
            <!-- Such- und Sortierformular -->
            <form method="GET" action="kunden.php">
                <div class="search-sort">
                    <div class="search-bar">
                        <label for="search">Suchen:</label>
                        <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Vorname, Nachname oder Email">
                    </div>
                    <div class="sort-options">
                        <label for="sort">Sortieren:</label>
                        <select name="sort" id="sort" onchange="this.form.submit()">
                            <option value="kid" <?php if ($sort == 'kid') echo 'selected'; ?>>KID</option>
                            <option value="name" <?php if ($sort == 'name') echo 'selected'; ?>>Nachname</option>
                            <option value="vorname" <?php if ($sort == 'vorname') echo 'selected'; ?>>Vorname</option>
                            <option value="kunde_seit" <?php if ($sort == 'kunde_seit') echo 'selected'; ?>>Kunde seit</option>
                        </select>
                    </div>
                    <button type="submit">Anwenden</button>
                </div>
                <input type="hidden" name="page" value="1">
            </form>
        </div>
        <a href='add_user.php'><button class="button">Kunden hinzufügen</button></a>
        <div class="customer-list">
            <!-- Liste der Kunden anzeigen -->
            <?php while ($row = $result->fetch_assoc()): ?>
                <?= renderCustomerItem($row); ?>
            <?php endwhile; ?>
            <?php if ($result->num_rows === 0): ?>
                <p>Keine Kunden gefunden</p>
            <?php endif; ?>
        </div>
        <div class="pagination">
            <!-- Paginierungslinks anzeigen -->
            <?= getPaginationLinks($search, $sort, $page, $total_pages); ?>
        </div>
    </main>
    <footer>
        <!-- Fusszeile -->
        <p>&copy; 2025 Kundenverwaltung. Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>

<?php
// Ressourcen freigeben und Verbindung schliessen
$stmt->close();
$total_stmt->close();
$conn->close();
?>