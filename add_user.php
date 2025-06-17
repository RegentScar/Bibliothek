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

// Überprüfen, ob das Formular abgeschickt wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Benutzereingaben abrufen und bereinigen
    $name = trim($_POST['name']);
    $vorname = trim($_POST['vorname']);
    $geburtstag = trim($_POST['geburtstag']);
    $kunde_seit = trim($_POST['kunde_seit']);
    $email = trim($_POST['email']);
    $geschlecht = trim($_POST['geschlecht']);
    $kontaktpermail = isset($_POST['kontaktpermail']) ? 1 : 0;

    // SQL-Abfrage vorbereiten, um einen neuen Kunden hinzuzufügen
    $stmt = $conn->prepare("
        INSERT INTO kunden (name, vorname, geburtstag, kunde_seit, email, geschlecht, kontaktpermail) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssssi", $name, $vorname, $geburtstag, $kunde_seit, $email, $geschlecht, $kontaktpermail);

    // Ausführen der Abfrage und Überprüfung auf Erfolg
    if ($stmt->execute()) {
        $success = "Kunde erfolgreich hinzugefügt.";
    } else {
        $error = "Hinzufügen fehlgeschlagen: " . htmlspecialchars($stmt->error);
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
    <title>Kunde hinzufügen</title>
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
                <li><a href="mod_user.php?logout=1">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Kunde hinzufügen</h1>
        <?php if (isset($success)): ?>
            <p style="color: green;"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <div class="form-container">
            <form method="POST" action="add_user.php">
                <label for="name">Nachname:</label>
                <input type="text" id="name" name="name" required>

                <label for="vorname">Vorname:</label>
                <input type="text" id="vorname" name="vorname" required>

                <label for="geburtstag">Geburtstag:</label>
                <input type="date" id="geburtstag" name="geburtstag" required>

                <label for="kunde_seit">Kunde seit:</label>
                <input type="date" id="kunde_seit" name="kunde_seit" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label>Geschlecht:</label>
                <div style="display: inline-flex; gap: 10px;">
                    <input type="radio" id="geschlecht_m" name="geschlecht" value="M" required>
                    <label for="geschlecht_m">Männlich</label>

                    <input type="radio" id="geschlecht_f" name="geschlecht" value="F" required>
                    <label for="geschlecht_f">Weiblich</label>
                </div>

                <label for="kontaktpermail">Kontakt per Mail:</label>
                <input type="checkbox" id="kontaktpermail" name="kontaktpermail">

                <button type="submit">Hinzufügen</button>
            </form>
        </div>
    </main>
    <footer>
        <p>&copy; 2025 Kundenverwaltung. Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>
