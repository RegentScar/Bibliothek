<?php
// Sitzung starten
session_start();

// Verbindung zur Datenbank herstellen
$conn = new mysqli("localhost", "root", "", "Books");

// Überprüfen, ob die Verbindung erfolgreich ist
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Formularverarbeitung
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $benutzername = trim($_POST['benutzername']);
    $passwort = $_POST['passwort'];

    // SQL-Abfrage vorbereiten und ausführen, um Benutzerdaten abzurufen
    $stmt = $conn->prepare("SELECT ID, passwort, admin FROM benutzer WHERE benutzername = ?");
    $stmt->bind_param("s", $benutzername);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password, $admin);
    $stmt->fetch();

    // Passwort überprüfen und Sitzungsvariablen setzen
    if ($stmt->num_rows > 0 && password_verify($passwort, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['admin'] = $admin;
        if ($admin) {
            header("Location: admin.php");
        } else {
            header("Location: bibliothek.php");
        }
        exit();
    } else {
        $error = "Ungültiger Benutzername oder Passwort";
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
    <title>Login</title>
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
        <!-- Login-Formular -->
        <h1>Login</h1>
        <?php if (isset($error)): ?>
            <!-- Fehlermeldung anzeigen, falls der Login fehlschlägt -->
            <p style="color: red;"><?= $error ?></p>
        <?php endif; ?>
        <div class="login-container">
            <form method="POST" action="login.php">
                <!-- Eingabefelder für Benutzername und Passwort -->
                <label for="benutzername">Benutzername:</label>
                <input type="text" id="benutzername" name="benutzername" required>
                <label for="passwort">Passwort:</label>
                <input type="password" id="passwort" name="passwort" required>
                <!-- Absenden-Button -->
                <button type="submit">Login</button>
            </form>
            <!-- Link zur Registrierungsseite -->
            <p>Noch keinen Account? <a href="registrieren.php">Registrieren</a></p>
        </div>
    </main>
    <footer>
        <!-- Footer mit Copyright-Informationen -->
        <p>&copy; 2025 Bibliothek. Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>