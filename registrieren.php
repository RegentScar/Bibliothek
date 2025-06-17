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

// Überprüfen, ob das Formular abgeschickt wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Benutzereingaben aus dem Formular abrufen und bereinigen
    $benutzername = trim($_POST['benutzername']);
    $name = trim($_POST['name']);
    $vorname = trim($_POST['vorname']);
    if (strlen($_POST['passwort']) < 8 || strlen($_POST['passwort']) > 64) {
        $error = "Das Passwort muss zwischen 8 und 64 Zeichen lang sein.";
    } else {
        $passwort = password_hash($_POST['passwort'], PASSWORD_DEFAULT); // Passwort hashen
    }
    $email = trim($_POST['email']);
    $admin = 0; // Standardmässig kein Admin

    // SQL-Abfrage vorbereiten, um die Benutzerdaten in die Datenbank einzufügen
    $stmt = $conn->prepare("INSERT INTO benutzer (benutzername, name, vorname, passwort, email, admin) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $benutzername, $name, $vorname, $passwort, $email, $admin);

    // Ausführen der Abfrage und Überprüfung auf Erfolg
    if ($stmt->execute()) {
        header("Location: login.php"); // Weiterleitung zur Login-Seite
        exit();
    } else {
        $error = "Registrierung fehlgeschlagen: " . htmlspecialchars($stmt->error);
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <!-- Meta-Informationen und Seitentitel -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrieren</title>
    <!-- Verknüpfung mit der externen CSS-Datei -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <!-- Navigationsleiste -->
        <nav>
            <ul>
                <!-- Logo mit Link zur Startseite -->
                <li class="logo"><a href="index.html"><img src="../Website mit SQL/images/Buch.png" alt="Logo"></a></li>
                <!-- Navigationslinks -->
                <li><a href="index.html">Startseite</a></li>
                <li><a href="bibliothek.php">Bibliothek</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <!-- Hauptinhalt: Registrierungsformular -->
        <h1>Registrieren</h1>
        <?php if (isset($error)): ?>
            <!-- Fehlermeldung anzeigen, falls die Registrierung fehlschlägt -->
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <div class="login-container">
            <form method="POST" action="registrieren.php">
                <!-- Eingabefelder für die Registrierung -->
                <label for="benutzername">Benutzername:</label>
                <input type="text" id="benutzername" name="benutzername" required>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
                <label for="vorname">Vorname:</label>
                <input type="text" id="vorname" name="vorname" required>
                <label for="passwort">Passwort:</label>
                <input type="password" id="passwort" name="passwort" minlength="8" maxlength="64" required>
                <label for="email">E-Mail:</label>
                <input type="email" id="email" name="email" required>
                <!-- Registrierungsbutton -->
                <button type="submit">Registrieren</button>
            </form>
        </div>
    </main>
    <footer>
        <!-- Footer mit Copyright-Informationen -->
        <p>&copy; 2025 Bibliothek. Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>