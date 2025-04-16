<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Nur Admins dürfen Benutzer verwalten
checkRole('admin');

// CSRF-Token generieren
$csrfToken = generateCsrfToken();

// Benutzer anlegen/bearbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken($_POST['csrf_token'])) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Validierung
    if (empty($username) || empty($email)) {
        $error = "Benutzername und E-Mail sind erforderlich";
    } else {
        try {
            if ($id) {
                // Bestehenden Benutzer aktualisieren
                if (!empty($_POST['password'])) {
                    $passwordHash = hashPassword($_POST['password']);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, is_active = ?, password_hash = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $role, $isActive, $passwordHash, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $role, $isActive, $id]);
                }
                $success = "Benutzer erfolgreich aktualisiert";
            } else {
                // Neuen Benutzer anlegen
                if (empty($_POST['password'])) {
                    $error = "Für neue Benutzer ist ein Passwort erforderlich";
                } else {
                    $passwordHash = hashPassword($_POST['password']);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, role, is_active, password_hash) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $role, $isActive, $passwordHash]);
                    $success = "Benutzer erfolgreich angelegt";
                }
            }
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                $error = "Benutzername oder E-Mail bereits vergeben";
            } else {
                $error = "Datenbankfehler: " . $e->getMessage();
            }
        }
    }
}

// Benutzer löschen
if (isset($_GET['delete']) && validateCsrfToken($_GET['csrf_token'])) {
    $userId = (int)$_GET['delete'];
    if ($userId !== $_SESSION['user_id']) { // Sich selbst nicht löschen
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $success = "Benutzer erfolgreich gelöscht";
    } else {
        $error = "Sie können sich nicht selbst löschen";
    }
}

// Alle Benutzer laden
$users = $pdo->query("SELECT id, username, email, role, is_active, last_login FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);

// Bearbeitungsmodus
$editingUser = null;
if (isset($_GET['edit'])) {
    $editingUser = $pdo->query("SELECT * FROM users WHERE id = " . (int)$_GET['edit'])->fetch();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PROJECT-Z | Benutzer verwalten</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap" rel="stylesheet">
  <style>
    html {
      scroll-behavior: smooth;
    }

    body {
      margin: 0;
      font-family: 'Orbitron', sans-serif;
      background-color: #0a0a0a;
      color: #f5f5f5;
    }

    .admin-header {
      background-color: rgba(0, 0, 0, 0.7);
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 1000;
      border-bottom: 1px solid #00ffaa44;
    }

    .admin-nav a {
      color: #00ffaa;
      text-decoration: none;
      margin: 0 15px;
      font-size: 1rem;
      transition: color 0.3s ease;
    }

    .admin-nav a:hover {
      color: #fff;
    }

    .admin-container {
      padding: 40px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .admin-title {
      font-size: 2rem;
      color: #00ffaa;
      margin-bottom: 30px;
      text-shadow: 0 0 10px #00ffaa;
    }

    .user-form {
      background: #1e1e1e;
      border: 1px solid #00ffaa44;
      padding: 30px;
      border-radius: 12px;
      margin-bottom: 40px;
      box-shadow: 0 0 10px #00ffaa22;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: #00ffaa;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 12px;
      background: #141414;
      border: 1px solid #00ffaa44;
      border-radius: 6px;
      color: #fff;
      font-family: 'Orbitron', sans-serif;
    }

    .form-actions {
      display: flex;
      gap: 15px;
      margin-top: 30px;
    }

    .btn {
      padding: 12px 25px;
      font-family: 'Orbitron', sans-serif;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-primary {
      background: #00ffaa;
      color: #0a0a0a;
    }

    .btn-primary:hover {
      background: #fff;
      box-shadow: 0 0 15px #00ffaa;
    }

    .btn-secondary {
      background: transparent;
      color: #00ffaa;
      border: 1px solid #00ffaa;
    }

    .btn-secondary:hover {
      background: #00ffaa11;
    }

    .user-list {
      background: #1e1e1e;
      border: 1px solid #00ffaa44;
      border-radius: 12px;
      overflow: hidden;
    }

    .user-item {
      padding: 20px;
      border-bottom: 1px solid #00ffaa22;
      display: grid;
      grid-template-columns: 2fr 2fr 1fr 1fr 1fr;
      align-items: center;
      gap: 15px;
    }

    .user-item:last-child {
      border-bottom: none;
    }

    .user-item-header {
      font-weight: bold;
      color: #00ffaa;
    }

    .user-actions {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
    }

    .action-btn {
      padding: 6px 12px;
      border-radius: 4px;
      text-decoration: none;
      font-size: 0.9rem;
    }

    .edit-btn {
      background: #00ffaa;
      color: #0a0a0a;
    }

    .delete-btn {
      background: #ff5555;
      color: white;
    }

    .status-active {
      color: #00ffaa;
    }

    .status-inactive {
      color: #ff5555;
    }

    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 6px;
    }

    .alert-success {
      background: #00ffaa22;
      border: 1px solid #00ffaa;
      color: #00ffaa;
    }

    .alert-error {
      background: #ff555522;
      border: 1px solid #ff5555;
      color: #ff5555;
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <header class="admin-header">
    <div style="color:#00ffaa; font-size: 1.5rem;">PROJECT-Z ADMIN</div>
    <nav class="admin-nav">
      <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Dashboard</a>
      <span style="color:#ddd;">Eingeloggt als: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
      <a href="logout.php" style="margin-left: 30px;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
  </header>

  <div class="admin-container">
    <h1 class="admin-title">Benutzerverwaltung</h1>
    
    <?php if (isset($success)): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
      </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
      <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
      </div>
    <?php endif; ?>
    
    <div class="user-form">
      <h2 style="color: #00ffaa; margin-top: 0;"><?php echo $editingUser ? 'Benutzer bearbeiten' : 'Neuen Benutzer anlegen'; ?></h2>
      
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        <input type="hidden" name="id" value="<?php echo $editingUser ? $editingUser['id'] : ''; ?>">
        
        <div class="form-group">
          <label for="username">Benutzername</label>
          <input type="text" id="username" name="username" 
                 value="<?php echo $editingUser ? htmlspecialchars($editingUser['username']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
          <label for="email">E-Mail</label>
          <input type="email" id="email" name="email" 
                 value="<?php echo $editingUser ? htmlspecialchars($editingUser['email']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
          <label for="password">Passwort</label>
          <input type="password" id="password" name="password" 
                 placeholder="<?php echo $editingUser ? 'Leer lassen, um nicht zu ändern' : ''; ?>">
          <?php if ($editingUser): ?>
            <small style="color: #888;">Nur ausfüllen, wenn das Passwort geändert werden soll</small>
          <?php endif; ?>
        </div>
        
        <div class="form-group">
          <label for="role">Rolle</label>
          <select id="role" name="role" required>
            <option value="editor" <?php echo ($editingUser && $editingUser['role'] === 'editor') ? 'selected' : ''; ?>>Editor</option>
            <option value="admin" <?php echo ($editingUser && $editingUser['role'] === 'admin') ? 'selected' : ''; ?>>Administrator</option>
          </select>
        </div>
        
        <div class="form-group">
          <label>
            <input type="checkbox" name="is_active" value="1" 
                  <?php echo ($editingUser && $editingUser['is_active']) || !$editingUser ? 'checked' : ''; ?>>
            Benutzer ist aktiv
          </label>
        </div>
        
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> <?php echo $editingUser ? 'Aktualisieren' : 'Anlegen'; ?>
          </button>
          
          <?php if ($editingUser): ?>
            <a href="users.php" class="btn btn-secondary">
              <i class="fas fa-times"></i> Abbrechen
            </a>
          <?php endif; ?>
        </div>
      </form>
    </div>
    
    <h2 style="color: #00ffaa;">Alle Benutzer</h2>
    
    <div class="user-list">
      <div class="user-item user-item-header">
        <div>Benutzername</div>
        <div>E-Mail</div>
        <div>Rolle</div>
        <div>Status</div>
        <div>Aktionen</div>
      </div>
      
      <?php foreach ($users as $user): ?>
      <div class="user-item">
        <div><?php echo htmlspecialchars($user['username']); ?></div>
        <div><?php echo htmlspecialchars($user['email']); ?></div>
        <div><?php echo $user['role'] === 'admin' ? 'Administrator' : 'Editor'; ?></div>
        <div class="<?php echo $user['is_active'] ? 'status-active' : 'status-inactive'; ?>">
          <?php echo $user['is_active'] ? 'Aktiv' : 'Inaktiv'; ?>
        </div>
        <div class="user-actions">
          <a href="users.php?edit=<?php echo $user['id']; ?>" class="action-btn edit-btn">
            <i class="fas fa-edit"></i> Bearbeiten
          </a>
          <?php if ($user['id'] !== $_SESSION['user_id']): ?>
            <a href="users.php?delete=<?php echo $user['id']; ?>&csrf_token=<?php echo $csrfToken; ?>" 
               class="action-btn delete-btn" 
               onclick="return confirm('Benutzer wirklich löschen?')">
              <i class="fas fa-trash"></i> Löschen
            </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>