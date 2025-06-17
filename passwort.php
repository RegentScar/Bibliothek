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

// Passwort ändern
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($new_password) < 8 || strlen($new_password) > 64) {
        $error = "Das Passwort muss zwischen 8 und 64 Zeichen lang sein.";
    } else if ($new_password !== $confirm_password) {
        $error = "Die neuen Passwörter stimmen nicht überein.";
    } else {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT passwort FROM benutzer WHERE ID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (password_verify($current_password, $user['passwort'])) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $update_stmt = $conn->prepare("UPDATE benutzer SET passwort = ? WHERE ID = ?");
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            $update_stmt->execute();
            $success = "Passwort erfolgreich geändert.";
        } else {
            $error = "Das aktuelle Passwort ist falsch.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passwort ändern</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="admin.php">Zurück</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Passwort ändern</h1>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <p style="color: green;"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>
        <form method="POST" action="passwort.php">
            <label for="current_password">Aktuelles Passwort:</label>
            <input type="password" id="current_password" name="current_password" required>
            <label for="new_password">Neues Passwort:</label>
            <input type="password" id="new_password" name="new_password" minlength="8" maxlength="64" required>
            <label for="confirm_password">Neues Passwort bestätigen:</label>
            <input type="password" id="confirm_password" name="confirm_password" minlength="8" maxlength="64" required>
            <button type="submit">Passwort ändern</button>
        </form>
    </main>
</body>
</html>

<?php
$conn->close();
?>
