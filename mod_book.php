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
$dbname = "Books";

// Verbindung zur Datenbank herstellen
$conn = new mysqli($servername, $username, $password, $dbname);

// Überprüfen, ob die Verbindung erfolgreich ist
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Überprüfen, ob eine Buch-ID übergeben wurde
if (isset($_GET['id'])) {
    $book_id = intval($_GET['id']);

    // Buchdaten abrufen
    $stmt = $conn->prepare("SELECT * FROM buecher WHERE id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    $stmt->close();

    if (!$book) {
        die("Buch nicht gefunden.");
    }
} else {
    die("Keine Buch-ID angegeben.");
}

// Überprüfen, ob das Formular abgeschickt wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Benutzereingaben abrufen und bereinigen
    $title = trim($_POST['title']);
    $kategorie = intval($_POST['kategorie']);
    $verkauft = intval($_POST['verkauft']);
    $kaufer = intval($_POST['kaufer']);
    $autor = trim($_POST['autor']);
    $beschreibung = trim($_POST['beschreibung']);
    $zustand = trim($_POST['zustand']);

    // SQL-Abfrage vorbereiten, um die Buchdaten zu aktualisieren
    $stmt = $conn->prepare("UPDATE buecher SET Title = ?, kategorie = ?, verkauft = ?, kaufer = ?, autor = ?, Beschreibung = ?, zustand = ? WHERE id = ?");
    $stmt->bind_param("siiisssi", $title, $kategorie, $verkauft, $kaufer, $autor, $beschreibung, $zustand, $book_id);

    // Ausführen der Abfrage und Überprüfung auf Erfolg
    if ($stmt->execute()) {
        $success = "Buch erfolgreich aktualisiert.";
        // Buchdaten erneut abrufen
        $stmt->close();
        $stmt = $conn->prepare("SELECT * FROM buecher WHERE id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();
    } else {
        $error = "Aktualisierung fehlgeschlagen: " . htmlspecialchars($stmt->error);
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buch bearbeiten</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li class="logo"><a href="index.html"><img src="../Website mit SQL/images/Buch.png" alt="Logo"></a></li>
                <li><a href="admin.php">Bibliothek</a></li>
                <li><a href="kunden.php">Kunden</a></li>
                <li><a href="passwort.php">Passwort ändern</a></li>
                <li><a href="mod_book.php?logout=1">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Buch bearbeiten</h1>
        <?php if (isset($success)): ?>
            <p style="color: green;"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <div class="form-container">
            <form method="POST" action="mod_book.php?id=<?php echo $book_id; ?>">
                <label for="title">Titel:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($book['Title']); ?>" required>

                <label for="beschreibung">Beschreibung:</label>
                <textarea id="beschreibung" name="beschreibung" rows="5" cols="50" required><?php echo htmlspecialchars($book['Beschreibung']); ?></textarea>

                <label for="kategorie">Kategorie:</label>
                <input type="number" id="kategorie" name="kategorie" value="<?php echo htmlspecialchars($book['kategorie']); ?>" required>

                <label for="autor">Autor:</label>
                <input type="text" id="autor" name="autor" value="<?php echo htmlspecialchars($book['autor']); ?>">

                <label>Zustand:</label>
                <div style="display: inline-flex; gap: 10px;">
                    <input type="radio" id="zustand_gut" name="zustand" value="G" <?php echo isset($book['zustand']) && $book['zustand'] === 'G' ? 'checked' : ''; ?>>
                    <label for="zustand_gut">Gut</label>

                    <input type="radio" id="zustand_mittel" name="zustand" value="M" <?php echo isset($book['zustand']) && $book['zustand'] === 'M' ? 'checked' : ''; ?>>
                    <label for="zustand_mittel">Mittel</label>

                    <input type="radio" id="zustand_schlecht" name="zustand" value="S" <?php echo isset($book['zustand']) && $book['zustand'] === 'S' ? 'checked' : ''; ?>>
                    <label for="zustand_schlecht">Schlecht</label>
                </div>

                <label>Verkauft:</label>
                <div style="display: inline-flex; gap: 10px;">
                    <input type="radio" id="verkauft_ja" name="verkauft" value="1" <?php echo $book['verkauft'] == 1 ? 'checked' : ''; ?>>
                    <label for="verkauft_ja">Ja</label>

                    <input type="radio" id="verkauft_nein" name="verkauft" value="0" <?php echo $book['verkauft'] == 0 ? 'checked' : ''; ?>>
                    <label for="verkauft_nein">Nein</label>
                </div>

                <label for="kaufer">Käufernummer:</label>
                <input type="number" id="kaufer" name="kaufer" value="<?php echo htmlspecialchars($book['kaufer']); ?>">

                <button type="submit">Speichern</button>
            </form>
        </div>
    </main>
    <footer>
        <p>&copy; 2025 Bibliothek. Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>
