<?php
// login.php - Korrigierte Version
ob_start(); // Output Buffering SOFORT aktivieren

// Fehlerberichterstattung (muss vor jeder Ausgabe kommen)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session starten (VOR jeglicher Ausgabe)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Datenbankverbindung einbinden
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';

// Login-Verarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Benutzername und Passwort eingeben!";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Session-Daten setzen
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['login_time'] = time();

                // Debug-Ausgabe vor Redirect
                error_log("Login erfolgreich für: $username");
                
                // Puffer leeren und redirecten
                ob_end_clean();
                header("Location: dashboard.php");
                exit();
            } else {
                $_SESSION['login_error'] = "Falsche Anmeldedaten!";
                error_log("Login fehlgeschlagen für: $username");
            }
        } catch (PDOException $e) {
            $_SESSION['login_error'] = "Systemfehler. Bitte versuchen Sie es später erneut.";
            error_log("Datenbankfehler: " . $e->getMessage());
        }
    }
}

// Falls kein Redirect erfolgt ist, Login-Formular anzeigen
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROJECT-Z | Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Orbitron', sans-serif;
            background-color: #0a0a0a;
            color: #f5f5f5;
        }
        .login-container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../background.png') center/cover;
        }
        .login-box {
            background: rgba(20, 20, 20, 0.9);
            padding: 40px;
            border-radius: 12px;
            border: 1px solid #00ffaa44;
            box-shadow: 0 0 20px #00ffaa88;
            width: 400px;
        }
        .login-title {
            font-size: 2rem;
            color: #00ffaa;
            text-shadow: 0 0 12px #00ffaa;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #00ffaa;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            background: #1e1e1e;
            border: 1px solid #00ffaa44;
            border-radius: 6px;
            color: #fff;
            font-family: 'Orbitron', sans-serif;
        }
        .login-btn {
            background: #00ffaa;
            color: #0a0a0a;
            border: none;
            padding: 12px 30px;
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
        }
        .error-message {
            color: #ff5555;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1 class="login-title">PROJECT-Z ADMIN</h1>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Benutzername</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Passwort</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="login-btn">Login</button>
            </form>
        </div>
    </div>
</body>
</html>