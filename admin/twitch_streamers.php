<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';

checkRole('admin');

// CSRF-Token generieren
$csrfToken = generateCsrfToken();

// Streamer hinzufügen/bearbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken($_POST['csrf_token'])) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $username = trim($_POST['username']);
    $displayName = trim($_POST['display_name']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $priority = (int)$_POST['priority'];
    
    // Validierung
    if (empty($username) || empty($displayName)) {
        $_SESSION['error'] = "Benutzername und Anzeigename sind erforderlich";
    } else {
        try {
            if ($id) {
                // Bestehenden Streamer aktualisieren
                $stmt = $pdo->prepare("UPDATE twitch_streamers SET username = ?, display_name = ?, is_active = ?, priority = ? WHERE id = ?");
                $stmt->execute([$username, $displayName, $isActive, $priority, $id]);
            } else {
                // Neuen Streamer anlegen
                $stmt = $pdo->prepare("INSERT INTO twitch_streamers (username, display_name, is_active, priority) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $displayName, $isActive, $priority]);
            }
            $_SESSION['success'] = "Streamer erfolgreich gespeichert";
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                $_SESSION['error'] = "Dieser Twitch-Benutzername existiert bereits";
            } else {
                $_SESSION['error'] = "Datenbankfehler: " . $e->getMessage();
            }
        }
    }
    header('Location: twitch_streamers.php');
    exit;
}

// Streamer löschen
if (isset($_GET['delete']) && validateCsrfToken($_GET['csrf_token'])) {
    $stmt = $pdo->prepare("DELETE FROM twitch_streamers WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    $_SESSION['success'] = "Streamer erfolgreich gelöscht";
    header('Location: twitch_streamers.php');
    exit;
}

// Bearbeitungsmodus
$editingStreamer = null;
if (isset($_GET['edit'])) {
    $editingStreamer = $pdo->query("SELECT * FROM twitch_streamers WHERE id = " . (int)$_GET['edit'])->fetch();
}

// Alle Streamer laden
$streamers = $pdo->query("SELECT * FROM twitch_streamers ORDER BY priority DESC, display_name")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Twitch Streamer | PROJECT-Z ADMIN</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* Stile aus users.php übernehmen */
    :root {
      --primary: #00ffaa;
      --bg-dark: #0a0a0a;
      --bg-light: #1e1e1e;
      --text-light: #f5f5f5;
      --text-muted: #888;
    }

    body {
      margin: 0;
      font-family: 'Orbitron', sans-serif;
      background-color: var(--bg-dark);
      color: var(--text-light);
    }

    .admin-container {
      padding: 40px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .admin-title {
      font-size: 2rem;
      color: var(--primary);
      margin-bottom: 30px;
      text-shadow: 0 0 10px var(--primary);
    }

    .form-container {
      background: var(--bg-light);
      border: 1px solid #00ffaa44;
      padding: 30px;
      border-radius: 12px;
      margin-bottom: 40px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: var(--primary);
    }

    .form-group input, .form-group select {
      width: 100%;
      padding: 12px;
      background: #141414;
      border: 1px solid #00ffaa44;
      border-radius: 6px;
      color: #fff;
    }

    .btn {
      padding: 12px 25px;
      border-radius: 6px;
      cursor: pointer;
    }

    .btn-primary {
      background: var(--primary);
      color: #0a0a0a;
    }

    .streamer-list {
      background: var(--bg-light);
      border: 1px solid #00ffaa44;
      border-radius: 12px;
    }

    .streamer-item {
      padding: 20px;
      border-bottom: 1px solid #00ffaa22;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .streamer-item:last-child {
      border-bottom: none;
    }

    .streamer-actions {
      display: flex;
      gap: 10px;
    }

    .action-btn {
      padding: 8px 15px;
      border-radius: 4px;
      text-decoration: none;
    }

    .edit-btn {
      background: var(--primary);
      color: #0a0a0a;
    }

    .delete-btn {
      background: #ff5555;
      color: white;
    }

    .status-active {
      color: var(--primary);
    }

    .status-inactive {
      color: var(--text-muted);
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
  </style>
</head>
<body>
  <!-- Header wie in users.php -->
  <header class="admin-header">
    <div style="color:#00ffaa; font-size: 1.5rem;">PROJECT-Z ADMIN</div>
    <nav class="admin-nav">
      <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Dashboard</a>
      <span style="color:#ddd;">Eingeloggt als: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
      <a href="logout.php" style="margin-left: 30px;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
  </header>

  <div class="admin-container">
    <h1 class="admin-title">Twitch Streamer verwalten</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
      <div style="background: #00ffaa22; padding: 15px; border-left: 4px solid var(--primary); margin-bottom: 20px;">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
      <div style="background: #ff555522; padding: 15px; border-left: 4px solid #ff5555; margin-bottom: 20px;">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>
    
    <div class="form-container">
      <h2><?= $editingStreamer ? 'Streamer bearbeiten' : 'Neuen Streamer hinzufügen' ?></h2>
      
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="id" value="<?= $editingStreamer ? $editingStreamer['id'] : '' ?>">
        
        <div class="form-group">
          <label>Twitch Benutzername</label>
          <input type="text" name="username" value="<?= $editingStreamer ? htmlspecialchars($editingStreamer['username']) : '' ?>" required>
        </div>
        
        <div class="form-group">
          <label>Anzeigename</label>
          <input type="text" name="display_name" value="<?= $editingStreamer ? htmlspecialchars($editingStreamer['display_name']) : '' ?>" required>
        </div>
        
        <div class="form-group">
          <label>Priorität (höher = weiter oben)</label>
          <input type="number" name="priority" value="<?= $editingStreamer ? $editingStreamer['priority'] : 0 ?>">
        </div>
        
        <div class="form-group">
          <label>
            <input type="checkbox" name="is_active" value="1" <?= ($editingStreamer && $editingStreamer['is_active']) || !$editingStreamer ? 'checked' : '' ?>>
            Streamer ist aktiv
          </label>
        </div>
        
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> Speichern
        </button>
        
        <?php if ($editingStreamer): ?>
          <a href="twitch_streamers.php" class="btn" style="margin-left: 15px;">
            <i class="fas fa-times"></i> Abbrechen
          </a>
        <?php endif; ?>
      </form>
    </div>
    
    <h2>Alle Streamer</h2>
    
    <div class="streamer-list">
      <?php foreach ($streamers as $streamer): ?>
      <div class="streamer-item">
        <div>
          <strong><?= htmlspecialchars($streamer['display_name']) ?></strong> (<?= htmlspecialchars($streamer['username']) ?>)
          <div>
            <span class="<?= $streamer['is_active'] ? 'status-active' : 'status-inactive' ?>">
              <?= $streamer['is_active'] ? 'Aktiv' : 'Inaktiv' ?>
            </span>
            • Priorität: <?= $streamer['priority'] ?>
          </div>
        </div>
        
        <div class="streamer-actions">
          <a href="twitch_streamers.php?edit=<?= $streamer['id'] ?>" class="action-btn edit-btn">
            <i class="fas fa-edit"></i> Bearbeiten
          </a>
          <a href="twitch_streamers.php?delete=<?= $streamer['id'] ?>&csrf_token=<?= $csrfToken ?>" 
             class="action-btn delete-btn"
             onclick="return confirm('Streamer wirklich löschen?')">
            <i class="fas fa-trash"></i> Löschen
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>