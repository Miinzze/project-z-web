<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Regel-Verwaltung | PROJECT-Z</title>
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
      margin: 0;
      font-family: 'Orbitron', sans-serif;
      background-color: var(--bg-dark);
      color: var(--text-light);
    }

    /* Header - Exakt wie Homepage */
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

    /* Container - Wie Homepage */
    .admin-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px;
    }

    /* Titel - Neon-Effekt wie Homepage */
    .admin-title {
      font-size: 2.5rem;
      color: var(--primary);
      text-shadow: 0 0 15px var(--primary);
      margin-bottom: 30px;
      position: relative;
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

    /* Formular - Angepasst an Homepage */
    .rule-form {
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

    /* Regel-Akkordeon - Stil wie Homepage */
    .rule-accordion {
      margin-top: 30px;
    }

    .rule-item {
      background: var(--bg-lighter);
      border-left: 4px solid var(--primary);
      margin-bottom: 15px;
      border-radius: 4px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .rule-header {
      padding: 18px 25px;
      background: rgba(0, 255, 170, 0.05);
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .rule-header:hover {
      background: rgba(0, 255, 170, 0.1);
    }

    .rule-title {
      font-weight: bold;
      color: var(--primary);
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 1.2rem;
    }

    .rule-category {
      background: rgba(0, 255, 170, 0.15);
      padding: 4px 10px;
      border-radius: 4px;
      font-size: 0.85rem;
    }

    .rule-content {
      padding: 0 25px;
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.4s ease;
    }

    .rule-content-inner {
      padding: 20px 0;
      border-top: 1px solid rgba(0, 255, 170, 0.1);
      line-height: 1.6;
    }

    /* Buttons - Wie Homepage */
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

    .btn-sm {
      padding: 8px 15px;
      font-size: 0.9rem;
    }

    .btn-danger {
      background: #ff5555;
      color: white;
    }

    .btn-danger:hover {
      background: #ff3333;
      box-shadow: 0 0 10px #ff5555;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .admin-container {
        padding: 20px;
      }
      
      .rule-header {
        padding: 15px;
      }
      
      .rule-title {
        font-size: 1rem;
      }
    }
  </style>
</head>
<body>
  <!-- Header exakt wie Homepage -->
  <header class="admin-header">
    <div style="color:#00ffaa; font-size: 1.5rem;">PROJECT-Z ADMIN</div>
    <nav class="admin-nav">
      <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Dashboard</a>
      <span style="color:#ddd;">Eingeloggt als: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
      <a href="logout.php" style="margin-left: 30px;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
  </header>

  <div class="admin-container">
    <h1 class="admin-title">Regel-Verwaltung</h1>
    
    <!-- Erfolgs-/Fehlermeldungen -->
    <?php if (isset($_SESSION['success'])): ?>
      <div style="background: rgba(0, 255, 170, 0.1); padding: 15px; border-left: 4px solid var(--primary); margin-bottom: 30px; color: var(--primary);">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
    <?php endif; ?>

    <!-- Regel-Formular -->
    <div class="rule-form">
      <h2 style="color: var(--primary); margin-top: 0;">
        <?= $editingRule ? '✏️ Regel bearbeiten' : '➕ Neue Regel erstellen' ?>
      </h2>
      
      <form method="POST">
        <input type="hidden" name="id" value="<?= $editingRule['id'] ?? '' ?>">
        
        <div class="form-group">
          <label>Titel</label>
          <input type="text" name="title" value="<?= htmlspecialchars($editingRule['title'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
          <label>Kategorie</label>
          <input type="text" name="category" list="categories" value="<?= htmlspecialchars($editingRule['category'] ?? '') ?>" required>
          <datalist id="categories">
            <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>">
            <?php endforeach; ?>
          </datalist>
        </div>
        
        <div class="form-group">
          <label>Beschreibung</label>
          <textarea name="description" rows="6" required><?= htmlspecialchars($editingRule['description'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
          <label>Sortierung (niedrig = oben)</label>
          <input type="number" name="sort_order" value="<?= $editingRule['sort_order'] ?? 0 ?>">
        </div>
        
        <div class="form-group">
          <label style="display: flex; align-items: center; gap: 10px;">
            <input type="checkbox" name="is_active" <?= ($editingRule['is_active'] ?? 1) ? 'checked' : '' ?> style="width: auto;">
            <span>Aktiv</span>
          </label>
        </div>
        
        <div style="display: flex; gap: 15px;">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Speichern
          </button>
          <?php if ($editingRule): ?>
            <a href="rules.php" class="btn" style="background: transparent; color: var(--primary); border: 1px solid var(--primary);">
              <i class="fas fa-times"></i> Abbrechen
            </a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- Regeln-Liste mit Aufklappfunktion -->
    <h2 style="color: var(--primary); margin-top: 40px; font-size: 1.8rem;">
      <i class="fas fa-list"></i> Vorhandene Regeln
    </h2>
    
    <div class="rule-accordion">
      <?php foreach ($rules as $rule): ?>
      <div class="rule-item" id="rule-<?= $rule['id'] ?>">
        <div class="rule-header" onclick="toggleRule(<?= $rule['id'] ?>)">
          <div class="rule-title">
            <?= htmlspecialchars($rule['title']) ?>
            <span class="rule-category"><?= htmlspecialchars($rule['category']) ?></span>
            <?php if (!$rule['is_active']): ?>
            <span style="background:#ff5555; padding:2px 6px; border-radius:4px; font-size:0.8rem;">INAKTIV</span>
            <?php endif; ?>
          </div>
          <div style="display: flex; gap: 10px;">
            <a href="rules.php?edit=<?= $rule['id'] ?>" class="btn btn-primary btn-sm" onclick="event.stopPropagation()">
              <i class="fas fa-edit"></i>
            </a>
            <a href="rules.php?delete=<?= $rule['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Regel wirklich löschen?') && event.stopPropagation()">
              <i class="fas fa-trash"></i>
            </a>
          </div>
        </div>
        <div class="rule-content">
          <div class="rule-content-inner">
            <?= nl2br(htmlspecialchars($rule['description'])) ?>
            <div style="margin-top: 15px; color: var(--text-muted); font-size: 0.9rem;">
              Sortierung: <?= $rule['sort_order'] ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <script>
  // Aufklappfunktion
  function toggleRule(id) {
      const rule = document.getElementById(`rule-${id}`);
      rule.classList.toggle('active');
      
      // Schließe andere Regeln
      document.querySelectorAll('.rule-item').forEach(item => {
          if (item.id !== `rule-${id}`) {
              item.classList.remove('active');
          }
      });
  }
  
  // Öffne Regel wenn in URL angegeben
  if (window.location.hash.startsWith('#rule-')) {
      const ruleId = window.location.hash.substring(6);
      toggleRule(ruleId);
      document.getElementById(`rule-${ruleId}`).scrollIntoView({ behavior: 'smooth' });
  }
  </script>
</body>
</html>