<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';
checkRole('admin');

// Bearbeitungslogik hier einfügen

$id = $_GET['id'] ?? 0;
$page = $id ? $pdo->query("SELECT * FROM wiki_pages WHERE id = $id")->fetch() : null;
$categories = $pdo->query("SELECT * FROM wiki_categories")->fetchAll();
$pageCategories = $id ? $pdo->query("SELECT category_id FROM wiki_page_category WHERE page_id = $id")->fetchAll(PDO::FETCH_COLUMN) : [];
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title><?= $id ? 'Wiki bearbeiten' : 'Neuer Wiki-Artikel' ?> | PROJECT-Z ADMIN</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* 1:1 aus news.php übernommen */
    :root {
      --primary: #00ffaa;
      --primary-dark: #00cc88;
      --bg-dark: #0a0a0a;
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

    .admin-header {
      background-color: rgba(0, 0, 0, 0.7);
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #00ffaa44;
    }

    .admin-nav a {
      color: var(--primary);
      text-decoration: none;
      margin: 0 15px;
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
      color: var(--primary);
      margin-bottom: 30px;
      text-shadow: 0 0 10px var(--primary);
    }

    /* Formular-Container aus news.php */
    .form-container {
      background: var(--bg-lighter);
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
      color: var(--primary);
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 12px;
      background: var(--bg-light);
      border: 1px solid #00ffaa44;
      border-radius: 6px;
      color: #fff;
      font-family: 'Orbitron', sans-serif;
    }

    .form-group textarea {
      min-height: 150px;
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
      background: var(--primary);
      color: #0a0a0a;
    }

    .btn-primary:hover {
      background: #fff;
      box-shadow: 0 0 15px var(--primary);
    }

    /* Kategorie-Checkboxen im Grid */
    .category-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      gap: 10px;
      margin-top: 10px;
    }

    .category-grid label {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
    }
  </style>
<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
<script>
  CKEDITOR.replace('editor', {
    skin: 'moono-dark',
    toolbar: [
      ['Bold', 'Italic', 'Link', 'BulletedList', 'NumberedList', 'Table']
    ]
  });
</script>
</head>
<body>
  <!-- Header exakt wie news.php -->
  <header class="admin-header">
    <div style="color:#00ffaa; font-size: 1.5rem;">PROJECT-Z ADMIN</div>
    <nav class="admin-nav">
      <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Dashboard</a>
      <span style="color:#ddd;">Eingeloggt als: <?= htmlspecialchars($_SESSION['username']) ?></span>
      <a href="logout.php" style="margin-left: 30px;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
  </header>

  <div class="admin-container">
    <h1 class="admin-title">
      <i class="fas fa-book"></i> <?= $id ? 'Wiki bearbeiten' : 'Neuer Wiki-Artikel' ?>
    </h1>

    <div class="form-container">
      <form method="POST" action="wiki_save.php">
        <input type="hidden" name="id" value="<?= $id ?>">
        
        <div class="form-group">
          <label>Titel</label>
          <input type="text" name="title" value="<?= htmlspecialchars($page['title'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
          <label>Slug (URL)</label>
          <input type="text" name="slug" value="<?= htmlspecialchars($page['slug'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
          <label>Kategorien</label>
          <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
              <label>
                <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>"
                  <?= in_array($cat['id'], $pageCategories) ? 'checked' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
        
        <div class="form-group">
          <label>Inhalt</label>
          <textarea id="editor" name="content"><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> Speichern
        </button>
      </form>
    </div>
  </div>

  <!-- TinyMCE Editor Konfiguration -->
  <script>
    tinymce.init({
      selector: '#editor',
      plugins: 'link lists code table',
      toolbar: 'formatselect | bold italic | link | bullist numlist | table | code',
      skin: 'oxide-dark',
      content_css: 'dark',
      height: 500
    });
  </script>
</body>
</html>