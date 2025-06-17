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

// Fetch categories from the kategorien table
$categories = [];
$stmt = $conn->prepare("SELECT id, kategorie FROM kategorien");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
$stmt->close();

$success = "";
$error = "";

if (isset($_GET['id'])) {
    // Code zum Bearbeiten eines bestehenden Buches
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

    // Überprüfen, ob das Formular zum Bearbeiten abgeschickt wurde
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

} else {
    // Code zum Hinzufügen eines neuen Buches

    // Überprüfen, ob das Formular zum Hinzufügen abgeschickt wurde
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Benutzereingaben abrufen und bereinigen
        $title = trim($_POST['title']);
        $kategorie = intval($_POST['kategorie']);
        $verkauft = intval($_POST['verkauft']);
        $kaufer = intval($_POST['kaufer']);
        $autor = trim($_POST['autor']);
        $beschreibung = trim($_POST['beschreibung']);
        $zustand = trim($_POST['zustand']);

        // SQL-Abfrage vorbereiten, um ein neues Buch hinzuzufügen
        $stmt = $conn->prepare("INSERT INTO buecher (Title, kategorie, verkauft, kaufer, autor, Beschreibung, zustand) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siiisss", $title, $kategorie, $verkauft, $kaufer, $autor, $beschreibung, $zustand);

        // Ausführen der Abfrage und Überprüfung auf Erfolg
        if ($stmt->execute()) {
            $success = "Neues Buch erfolgreich hinzugefügt.";
        } else {
            $error = "Hinzufügen fehlgeschlagen: " . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($book_id) ? 'Buch bearbeiten' : 'Buch hinzufügen'; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li class="logo"><a href="index.html"><img src="images/Buch.png" alt="Logo"></a></li>
                <li><a href="admin.php">Bibliothek</a></li>
                <li><a href="kunden.php">Kunden</a></li>
                <li><a href="passwort.php">Passwort ändern</a></li>
                <li><a href="mod_book.php?logout=1">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1><?php echo isset($book_id) ? 'Buch bearbeiten' : 'Buch hinzufügen'; ?></h1>
        <?php if (isset($success) && $success != ""): ?>
            <p style="color: green;"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if (isset($error) && $error != ""): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <div class="form-container">
            <form method="POST" action="<?php echo isset($book_id) ? 'add_book.php?id=' . $book_id : 'add_book.php'; ?>">
                <label for="title">Titel:</label>
                <input type="text" id="title" name="title" value="<?php echo isset($book['Title']) ? htmlspecialchars($book['Title']) : ''; ?>" required>

                <label for="beschreibung">Beschreibung:</label>
                <textarea id="beschreibung" name="beschreibung" rows="5" cols="50" required><?php echo isset($book['Beschreibung']) ? htmlspecialchars($book['Beschreibung']) : ''; ?></textarea>

                <label for="kategorie">Kategorie:</label>
                <select name="kategorie" id="kategorie" required>
                    <option value="">Bitte wählen</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['id']) ?>" <?= isset($book['kategorie']) && $book['kategorie'] == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['kategorie']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="autor">Autor:</label>
                <input type="text" id="autor" name="autor" value="<?php echo isset($book['autor']) ? htmlspecialchars($book['autor']) : ''; ?>">

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
                    <input type="radio" id="verkauft_ja" name="verkauft" value="1" <?php echo isset($book['verkauft']) && $book['verkauft'] == 1 ? 'checked' : ''; ?>>
                    <label for="verkauft_ja">Ja</label>

                    <input type="radio" id="verkauft_nein" name="verkauft" value="0" <?php echo isset($book['verkauft']) && $book['verkauft'] == 0 ? 'checked' : ''; ?>>
                    <label for="verkauft_nein">Nein</label>
                </div>

                <label for="kaufer">Käufernummer:</label>
                <input type="number" id="kaufer" name="kaufer" value="<?php echo isset($book['kaufer']) ? htmlspecialchars($book['kaufer']) : ''; ?>">

                <button type="submit"><?php echo isset($book_id) ? 'Änderungen speichern' : 'Buch hinzufügen'; ?></button>
            </form>
        </div>
    </main>
    <footer>
        <p>&copy; 2025 Bibliothek. Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>