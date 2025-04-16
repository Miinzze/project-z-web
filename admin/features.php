<?php
// [PHP-Teil bleibt identisch wie zuvor]
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Feature-Verwaltung | PROJECT-Z</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary: #00ffaa;
      --primary-dark: #00cc88;
      --bg-dark: #0a0a0a;
      --bg-darker: #050505;
      --bg-light: #141414;
      --bg-lighter: #1e1e1e;
      --text-light: #f5f5f5;
      --text-muted: #888;
    }

    body {
      background-color: var(--bg-dark);
      color: var(--text-light);
      font-family: 'Orbitron', sans-serif;
    }

    .admin-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px;
    }

    .admin-title {
      font-size: 2.5rem;
      color: var(--primary);
      text-shadow: 0 0 15px var(--primary);
      margin-bottom: 30px;
      position: relative;
      display: inline-block;
    }

    .admin-title::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 100%;
      height: 2px;
      background: linear-gradient(90deg, var(--primary), transparent);
    }

    /* Feature-Formular */
    .feature-form {
      background: var(--bg-lighter);
      border: 1px solid var(--primary);
      padding: 30px;
      border-radius: 12px;
      margin-bottom: 40px;
      box-shadow: 0 0 20px rgba(0, 255, 170, 0.1);
    }

    .form-group {
      margin-bottom: 25px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: var(--primary);
      font-size: 1.1rem;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
      width: 100%;
      padding: 12px;
      background: var(--bg-light);
      border: 1px solid rgba(0, 255, 170, 0.3);
      border-radius: 6px;
      color: var(--text-light);
      font-family: 'Orbitron', sans-serif;
      transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus {
      border-color: var(--primary);
      box-shadow: 0 0 10px var(--primary);
      outline: none;
    }

    .form-group textarea {
      min-height: 120px;
    }

    .icon-preview {
      font-size: 2.5rem;
      margin: 15px 0;
      color: var(--primary);
      text-align: center;
      text-shadow: 0 0 10px var(--primary);
    }

    /* Features-Tabelle */
    .features-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 15px;
      margin-top: 30px;
    }

    .features-table th {
      text-align: left;
      padding: 15px;
      background: var(--bg-light);
      color: var(--primary);
      text-transform: uppercase;
      font-size: 0.9rem;
      letter-spacing: 1px;
    }

    .features-table td {
      padding: 20px;
      background: var(--bg-lighter);
      border: 1px solid rgba(0, 255, 170, 0.1);
      vertical-align: middle;
    }

    .features-table tr:hover td {
      background: rgba(0, 255, 170, 0.05);
      border-color: var(--primary);
    }

    .feature-icon {
      font-size: 1.8rem;
      color: var(--primary);
      text-align: center;
      width: 60px;
    }

    .status-active {
      color: var(--primary);
      font-weight: bold;
    }

    .status-inactive {
      color: var(--text-muted);
    }

    /* Buttons */
    .btn {
      padding: 12px 25px;
      border-radius: 6px;
      font-family: 'Orbitron', sans-serif;
      cursor: pointer;
      transition: all 0.3s ease;
      border: none;
      font-size: 1rem;
    }

    .btn-primary {
      background: var(--primary);
      color: var(--bg-dark);
    }

    .btn-primary:hover {
      background: #fff;
      box-shadow: 0 0 15px var(--primary);
    }

    .btn-secondary {
      background: transparent;
      color: var(--primary);
      border: 1px solid var(--primary);
      margin-left: 15px;
    }

    .btn-secondary:hover {
      background: rgba(0, 255, 170, 0.1);
    }

    .btn-sm {
      padding: 8px 15px;
      font-size: 0.9rem;
    }

    .btn-danger {
      background: #ff5555;
      color: white;
      margin-left: 10px;
    }

    .btn-danger:hover {
      background: #ff3333;
      box-shadow: 0 0 10px #ff5555;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .features-table {
        display: block;
        overflow-x: auto;
      }
      
      .admin-container {
        padding: 20px;
      }
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
  <!-- Header wie im Dashboard -->
  <header class="admin-header">
    <div style="color:#00ffaa; font-size: 1.5rem;">PROJECT-Z ADMIN</div>
    <nav class="admin-nav">
      <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Dashboard</a>
      <span style="color:#ddd;">Eingeloggt als: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
      <a href="logout.php" style="margin-left: 30px;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
  </header>

  <div class="admin-container">
    <h1 class="admin-title">Feature-Verwaltung</h1>

    <!-- Erfolgs-/Fehlermeldungen -->
    <?php if (isset($_SESSION['success'])): ?>
      <div style="background: rgba(0, 255, 170, 0.1); border-left: 4px solid var(--primary); padding: 15px; margin-bottom: 30px; color: var(--primary);">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
    <?php endif; ?>

    <!-- Feature-Formular -->
    <div class="feature-form">
      <h2 style="color: var(--primary); margin-top: 0; font-size: 1.8rem;">
        <?= $editingFeature ? '✏️ Feature bearbeiten' : '➕ Neues Feature' ?>
      </h2>
      
      <form method="POST">
        <input type="hidden" name="id" value="<?= $editingFeature['id'] ?? '' ?>">
        
        <div class="form-group">
          <label>Titel</label>
          <input type="text" name="title" value="<?= htmlspecialchars($editingFeature['title'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label>Beschreibung</label>
          <textarea name="description" rows="4" required><?= htmlspecialchars($editingFeature['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label>Icon (FontAwesome Klasse)</label>
          <input type="text" name="icon" value="<?= htmlspecialchars($editingFeature['icon'] ?? 'fas fa-star') ?>" required>
          <div class="icon-preview">
            <i class="<?= htmlspecialchars($editingFeature['icon'] ?? 'fas fa-star') ?>"></i>
          </div>
        </div>

        <div class="form-group">
          <label style="display: flex; align-items: center; gap: 10px;">
            <input type="checkbox" name="is_active" <?= ($editingFeature['is_active'] ?? 1) ? 'checked' : '' ?> style="width: auto;">
            <span>Aktiv</span>
          </label>
        </div>

        <div style="display: flex;">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Speichern
          </button>
          <?php if ($editingFeature): ?>
            <a href="features.php" class="btn btn-secondary">
              <i class="fas fa-times"></i> Abbrechen
            </a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- Features-Liste -->
    <h2 style="color: var(--primary); font-size: 1.8rem; margin-top: 50px;">
      <i class="fas fa-list"></i> Vorhandene Features
    </h2>
    
    <table class="features-table">
      <thead>
        <tr>
          <th style="width: 60px;">Icon</th>
          <th>Titel</th>
          <th style="width: 100px;">Status</th>
          <th style="width: 200px;">Aktionen</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($features as $feature): ?>
        <tr>
          <td class="feature-icon">
            <i class="<?= htmlspecialchars($feature['icon']) ?>"></i>
          </td>
          <td>
            <strong><?= htmlspecialchars($feature['title']) ?></strong><br>
            <small style="color: var(--text-muted);"><?= substr(htmlspecialchars($feature['description']), 0, 50) ?>...</small>
          </td>
          <td class="<?= $feature['is_active'] ? 'status-active' : 'status-inactive' ?>">
            <?= $feature['is_active'] ? 'Aktiv' : 'Inaktiv' ?>
          </td>
          <td>
            <a href="features.php?edit=<?= $feature['id'] ?>" class="btn btn-primary btn-sm">
              <i class="fas fa-edit"></i> Bearbeiten
            </a>
            <a href="features.php?delete=<?= $feature['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Feature wirklich löschen?')">
              <i class="fas fa-trash"></i> Löschen
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <script>
    // Live-Icon-Vorschau
    document.querySelector('input[name="icon"]').addEventListener('input', function() {
      const preview = document.querySelector('.icon-preview i');
      preview.className = this.value;
      
      // Fallback für ungültige Icons
      if (!preview.style.display || preview.style.display === 'inline-block') {
        preview.style.display = 'inline-block';
      } else {
        preview.style.display = 'none';
        setTimeout(() => preview.style.display = 'inline-block', 100);
      }
    });

    // Hover-Effekte für Tabellenzeilen
    document.querySelectorAll('.features-table tr').forEach(row => {
      row.addEventListener('mouseenter', () => {
        row.querySelector('td').style.borderLeftColor = 'var(--primary)';
        row.querySelector('td:last-child').style.borderRightColor = 'var(--primary)';
      });
      row.addEventListener('mouseleave', () => {
        row.querySelector('td').style.borderLeftColor = '';
        row.querySelector('td:last-child').style.borderRightColor = '';
      });
    });
  </script>
</body>
</html>