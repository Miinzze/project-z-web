<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';

checkRole('admin');

// Standardwerte laden
$defaultSettings = [
    'font_family' => 'Orbitron',
    'primary_color' => '#00ffaa',
    'background_image' => 'background.png',
    'menu_items' => 'Start,Features,Download,Regeln,Discord',
    'logo_url' => 'logo.png'
];

// Datenbanktabelle für Einstellungen erstellen (falls nicht vorhanden)
$pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Einstellungen speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
      $pdo->beginTransaction();

              // Menü-Reihenfolge verarbeiten
              $menuOrder = $_POST['menu_order'] ?? [];
              $menuItems = $_POST['menu_items'] ?? [];
              $menuLinks = $_POST['menu_links'] ?? [];

                      // Sortiere nach Reihenfolge
        array_multisort($menuOrder, $menuItems, $menuLinks);

        // Als Strings speichern
        $menuItemsStr = implode(',', array_map('trim', $menuItems));
        $menuLinksStr = implode(',', array_map('trim', $menuLinks));
        $menuOrderStr = implode(',', $menuOrder);
      
      // Menü-Einstellungen speichern
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) 
                             VALUES ('menu_items', ?), ('menu_links', ?), ('menu_order', ?)
                             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$menuItemsStr, $menuLinksStr, $menuOrderStr]);

      // Andere Einstellungen speichern
      $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) 
                           VALUES (:key, :value) 
                           ON DUPLICATE KEY UPDATE setting_value = :value");
      
      foreach ($_POST['settings'] as $key => $value) {
          $stmt->execute([':key' => $key, ':value' => $value]);
      }


        if (!empty($_FILES['background_image']['tmp_name'])) {
          $uploadDir = __DIR__.'/../assets/uploads/';
          
          // Verzeichnis existiert nicht? Erstellen!
          if (!file_exists($uploadDir)) {
              mkdir($uploadDir, 0755, true);
          }
      
          $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
          $fileType = mime_content_type($_FILES['background_image']['tmp_name']);
      
          if (!in_array($fileType, $allowedTypes)) {
              $_SESSION['error'] = "Nur JPG, PNG oder WebP erlaubt!";
          } else {
              $fileName = 'bg-'.time().'.'.pathinfo($_FILES['background_image']['name'], PATHINFO_EXTENSION);
              $targetPath = $uploadDir.$fileName;
      
              if (move_uploaded_file($_FILES['background_image']['tmp_name'], $targetPath)) {
                  // Altes Bild löschen (optional)
                  if (!empty($settings['background_image']) && file_exists(__DIR__.'/../'.$settings['background_image'])) {
                      unlink(__DIR__.'/../'.$settings['background_image']);
                  }
                  
                  $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) 
                                        VALUES ('background_image', ?) 
                                        ON DUPLICATE KEY UPDATE setting_value = ?");
                  $stmt->execute(["assets/uploads/".$fileName, "assets/uploads/".$fileName]);
                  
                  $_SESSION['success'] = "Hintergrund erfolgreich hochgeladen!";
              } else {
                  $_SESSION['error'] = "Upload fehlgeschlagen!";
                  error_log("Upload Error: ".$_FILES['background_image']['error']);
              }
          }
      }
        
        $pdo->commit();
        updateSettingsCache($pdo);

		// Cache aktualisieren
$currentSettings = $pdo->query("SELECT setting_key, setting_value FROM site_settings")
                     ->fetchAll(PDO::FETCH_KEY_PAIR);
file_put_contents(__DIR__.'/../cache/settings.cache', json_encode($currentSettings));
		
        $_SESSION['success'] = "Einstellungen erfolgreich gespeichert!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Fehler beim Speichern: " . $e->getMessage();
    }
    
    header('Location: settings.php');
    exit;
}

// Aktuelle Einstellungen laden
$settings = $defaultSettings;
$dbSettings = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$settings = array_merge($settings, $dbSettings);
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Design-Einstellungen | PROJECT-Z</title>
  <link href="https://fonts.googleapis.com/css2?family=<?= urlencode($settings['font_family']) ?>:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary: <?= $settings['primary_color'] ?>;
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
      font-family: '<?= $settings['font_family'] ?>', sans-serif;
      background-color: var(--bg-dark);
      color: var(--text-light);
      min-height: 100vh;
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
      border-bottom: 1px solid var(--primary);
    }

    .admin-nav a {
      color: var(--primary);
      text-decoration: none;
      margin: 0 15px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    .admin-nav a:hover {
      color: var(--text-light);
      text-shadow: 0 0 8px var(--primary);
    }

    .admin-container {
      padding: 40px;
      max-width: 1400px;
      margin: 0 auto;
    }

    .admin-title {
      font-size: 2.5rem;
      color: var(--primary);
      margin-bottom: 30px;
      text-shadow: 0 0 15px var(--primary);
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

    /* Style Preview */
    .style-preview {
      background: var(--bg-light);
      border: 1px solid var(--primary);
      padding: 30px;
      border-radius: 12px;
      margin-bottom: 40px;
      box-shadow: 0 0 20px rgba(0, 255, 170, 0.1);
    }

    .preview-header {
      color: var(--primary);
      text-shadow: 0 0 10px var(--primary);
      margin-bottom: 20px;
    }

    .preview-nav {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    .preview-nav a {
      color: var(--primary);
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .preview-nav a:hover {
      color: #fff;
      text-shadow: 0 0 10px var(--primary);
    }

    .font-preview {
      font-size: 1.5rem;
      margin: 20px 0;
      padding: 20px;
      background: var(--bg-lighter);
      border: 1px solid var(--primary);
      border-radius: 8px;
    }

    /* Formular */
    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      margin-bottom: 40px;
    }

    .form-group {
      margin-bottom: 25px;
    }

    .form-group label {
      display: block;
      margin-bottom: 10px;
      color: var(--primary);
      font-size: 1.1rem;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 12px;
      background: var(--bg-light);
      border: 1px solid rgba(0, 255, 170, 0.3);
      border-radius: 6px;
      color: var(--text-light);
      font-family: '<?= $settings['font_family'] ?>', sans-serif;
      transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 10px var(--primary);
      outline: none;
    }

    .color-picker {
      width: 50px;
      height: 50px;
      border: 2px solid var(--bg-light);
      border-radius: 6px;
      cursor: pointer;
      display: block;
      margin-top: 10px;
    }

    /* Buttons */
    .btn {
      padding: 12px 25px;
      border-radius: 6px;
      font-family: '<?= $settings['font_family'] ?>', sans-serif;
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

    /* Responsive */
    @media (max-width: 768px) {
      .admin-container {
        padding: 20px;
      }
      
      .form-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <header class="admin-header">
    <div style="color:var(--primary); font-size: 1.5rem;">
      PROJECT-Z <span style="color:#fff;">ADMIN</span>
    </div>
    <nav class="admin-nav">
      <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Dashboard</a>
      <span style="color:var(--text-muted);">
        <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['username']) ?>
      </span>
      <a href="logout.php" style="margin-left: 30px;">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </nav>
  </header>

  <div class="admin-container">
    <h1 class="admin-title">Design-Einstellungen</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
      <div style="background: rgba(0, 255, 170, 0.1); padding: 15px; border-left: 4px solid var(--primary); margin-bottom: 30px; color: var(--primary);">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
      <div style="background: rgba(255, 85, 85, 0.1); padding: 15px; border-left: 4px solid #ff5555; margin-bottom: 30px; color: #ff5555;">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>
    
    <!-- Echtzeit-Vorschau -->
    <div class="style-preview">
      <h2 class="preview-header">Live-Vorschau</h2>
      <div class="preview-nav">
        <?php foreach (explode(',', $settings['menu_items']) as $item): ?>
          <a href="#"><?= trim($item) ?></a>
        <?php endforeach; ?>
      </div>
      <div class="font-preview">
        Dies ist ein Beispieltext in <?= $settings['font_family'] ?>
      </div>
      <div style="margin-top: 20px;">
        <span style="color: var(--primary);">Primärfarbe:</span>
        <span style="display: inline-block; width: 20px; height: 20px; background: var(--primary); margin-left: 10px; border: 1px solid var(--bg-light);"></span>
        <?= $settings['primary_color'] ?>
      </div>
    </div>
    
    <!-- Einstellungsformular -->
    <form method="POST" enctype="multipart/form-data">
      <div class="form-grid">
        <!-- Schriftart -->
        <div class="form-group">
          <label>Schriftart</label>
          <select name="settings[font_family]" class="font-select">
            <option value="Orbitron" <?= $settings['font_family'] === 'Orbitron' ? 'selected' : '' ?>>Orbitron (Standard)</option>
            <option value="Rajdhani" <?= $settings['font_family'] === 'Rajdhani' ? 'selected' : '' ?>>Rajdhani</option>
            <option value="Aldrich" <?= $settings['font_family'] === 'Aldrich' ? 'selected' : '' ?>>Aldrich</option>
            <option value="Michroma" <?= $settings['font_family'] === 'Michroma' ? 'selected' : '' ?>>Michroma</option>
          </select>
        </div>
        
        <!-- Primärfarbe -->
        <div class="form-group">
          <label>Primärfarbe</label>
          <input type="color" name="settings[primary_color]" value="<?= $settings['primary_color'] ?>" class="color-picker">
        </div>
        
        <!-- Hintergrundbild -->
        <form method="POST" enctype="multipart/form-data">
    <!-- Andere Formularfelder... -->
    
    <div class="form-group">
        <label>Hintergrundbild</label>
        <input type="file" name="background_image" accept="image/*">
        <?php if (!empty($settings['background_image'])): ?>
            <div style="margin-top:15px;">
                <img src="../<?= $settings['background_image'] ?>" style="max-height:100px; border:2px solid var(--primary);">
                <p style="font-size:0.8em;">Aktuelles Bild: <?= basename($settings['background_image']) ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <button type="submit" class="btn btn-primary">Speichern</button>
</form>
        
        <!-- Menüpunkte -->
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

        <div class="form-group">
  <label>Menüpunkte (drag & drop zum Sortieren)</label>
  <div id="menu-editor" style="margin-top:10px;">
    <?php
    $menuItems = explode(',', $settings['menu_items'] ?? '');
    $menuLinks = explode(',', $settings['menu_links'] ?? '');
    $menuOrder = isset($settings['menu_order']) ? explode(',', $settings['menu_order']) : range(0, count($menuItems) - 1);
    
    // Sortiere nach gespeicherter Reihenfolge
    array_multisort($menuOrder, $menuItems, $menuLinks);
    
    foreach ($menuItems as $index => $item): 
      $link = $menuLinks[$index] ?? '#';
    ?>
      <div class="menu-item" style="display:flex;align-items:center;gap:10px;margin-bottom:10px;padding:10px;background:var(--bg-light);border-radius:6px;">
        <div class="drag-handle" style="cursor:grab;">
          <i class="fas fa-grip-vertical"></i>
        </div>
        <input type="hidden" name="menu_order[]" value="<?= $index ?>" class="menu-order">
        <input type="text" name="menu_items[]" value="<?= htmlspecialchars(trim($item)) ?>" 
               placeholder="Menütext" style="flex:1;">
        <input type="text" name="menu_links[]" value="<?= htmlspecialchars($link) ?>" 
               placeholder="Link (z.B. /wiki)" style="flex:1;">
        <button type="button" class="btn btn-danger remove-item">
          <i class="fas fa-trash"></i>
        </button>
      </div>
    <?php endforeach; ?>
  </div>
  <button type="button" id="add-menu-item" class="btn btn-sm" style="margin-top:10px;">
    <i class="fas fa-plus"></i> Neuen Menüpunkt hinzufügen
  </button>
</div>
        
        <!-- Logo URL -->
        <div class="form-group">
          <label>Logo URL</label>
          <input type="text" name="settings[logo_url]" value="<?= $settings['logo_url'] ?>">
        </div>
      </div>
    </form>
  </div>

  <script>
  // Echtzeit-Vorschau aktualisieren
  document.querySelectorAll('.font-select, .color-picker, input[name*="menu_items"]').forEach(element => {
    element.addEventListener('change', updatePreview);
  });
  
  function updatePreview() {
    // Schriftart aktualisieren
    const font = document.querySelector('.font-select').value;
    document.body.style.fontFamily = `'${font}', sans-serif`;
    document.querySelector('.font-preview').style.fontFamily = `'${font}'`;
    document.querySelector('.font-preview').textContent = `Dies ist ein Beispieltext in ${font}`;
    
    // Farbe aktualisieren
    const color = document.querySelector('.color-picker').value;
    document.documentElement.style.setProperty('--primary', color);
    
    // Menüpunkte aktualisieren
    const menuItems = document.querySelector('input[name*="menu_items"]').value.split(',');
    const menuContainer = document.querySelector('.preview-nav');
    menuContainer.innerHTML = '';
    menuItems.forEach(item => {
      if (item.trim()) {
        const link = document.createElement('a');
        link.href = '#';
        link.textContent = item.trim();
        menuContainer.appendChild(link);
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function() {
  // Neuen Menüpunkt hinzufügen
  document.getElementById('add-menu-item').addEventListener('click', function() {
    const editor = document.getElementById('menu-editor');
    const newItem = document.createElement('div');
    newItem.className = 'menu-item';
    newItem.style = 'display:flex;gap:10px;margin-bottom:10px;';
    newItem.innerHTML = `
      <input type="text" name="menu_items[]" placeholder="Menütext" style="flex:1;">
      <input type="text" name="menu_links[]" placeholder="Link (z.B. /wiki)" style="flex:1;">
      <button type="button" class="btn btn-danger remove-item"><i class="fas fa-trash"></i></button>
    `;
    editor.appendChild(newItem);
  });

  // Menüpunkt entfernen
  document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-item')) {
      e.target.closest('.menu-item').remove();
    }
  });
});

document.addEventListener('DOMContentLoaded', function() {
  // Drag & Drop für Menüpunkte
  new Sortable(document.getElementById('menu-editor'), {
    animation: 150,
    handle: '.drag-handle',
    onEnd: function() {
      updateMenuOrder();
    }
  });

  // Neuen Menüpunkt hinzufügen
  document.getElementById('add-menu-item').addEventListener('click', function() {
    const editor = document.getElementById('menu-editor');
    const newItem = document.createElement('div');
    newItem.className = 'menu-item';
    newItem.innerHTML = `
      <div class="drag-handle" style="cursor:grab;padding:10px;">
        <i class="fas fa-grip-vertical"></i>
      </div>
      <input type="text" name="menu_items[]" placeholder="Menütext" style="flex:1;">
      <input type="text" name="menu_links[]" placeholder="Link" style="flex:1;">
      <button type="button" class="btn btn-danger remove-item">
        <i class="fas fa-trash"></i>
      </button>
    `;
    editor.appendChild(newItem);
    updateMenuOrder();
  });

  // Menüpunkt entfernen
  document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-item')) {
      e.target.closest('.menu-item').remove();
      updateMenuOrder();
    }
  });

  // Reihenfolge aktualisieren
  function updateMenuOrder() {
    const items = document.querySelectorAll('.menu-item');
    items.forEach((item, index) => {
      item.querySelector('.menu-order').value = index;
    });
  }
});
  </script>
</body>
</html>