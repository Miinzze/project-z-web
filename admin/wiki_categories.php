<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';
checkRole('admin');

// Kategorie hinzufügen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    $pdo->prepare("INSERT INTO wiki_categories (name, slug) VALUES (?, ?)")
        ->execute([$name, $slug]);
}

// Kategorie löschen
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM wiki_categories WHERE id = ?")->execute([$_GET['delete']]);
    header('Location: wiki_categories.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM wiki_categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Wiki Kategorien | PROJECT-Z ADMIN</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* Übernommen aus news.php */
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

    .admin-header {
      background-color: rgba(0, 0, 0, 0.7);
      padding: 20px 40px;
      border-bottom: 1px solid #00ffaa44;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .admin-nav a {
      color: var(--primary);
      text-decoration: none;
      margin: 0 15px;
    }

    .admin-container {
      padding: 40px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .admin-title {
      color: var(--primary);
      font-size: 2rem;
      text-shadow: 0 0 10px var(--primary);
    }

    .form-container {
      background: var(--bg-light);
      border: 1px solid #00ffaa44;
      padding: 30px;
      border-radius: 12px;
      margin-bottom: 40px;
    }

    .form-group input {
      width: 100%;
      padding: 12px;
      background: #141414;
      border: 1px solid #00ffaa44;
      color: #fff;
    }

    .btn {
      padding: 12px 25px;
      background: var(--primary);
      color: #0a0a0a;
      border: none;
      cursor: pointer;
    }

    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
    }

    .card {
      background: var(--bg-light);
      border: 1px solid #00ffaa44;
      padding: 20px;
      border-radius: 8px;
    }

    .btn-danger {
      background: #ff5555;
      color: white;
    }
  </style>
</head>
<body>
  <!-- Header wie in dashboard.php -->
  <header class="admin-header">
    <div style="color:#00ffaa; font-size: 1.5rem;">PROJECT-Z ADMIN</div>
    <nav class="admin-nav">
      <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Dashboard</a>
      <span style="color:#ddd;">Eingeloggt als: <?= htmlspecialchars($_SESSION['username']) ?></span>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
  </header>

  <div class="admin-container">
    <h1 class="admin-title">
      <i class="fas fa-tags"></i> Wiki Kategorien
    </h1>

    <div class="form-container">
      <h3>Neue Kategorie</h3>
      <form method="POST">
        <div class="form-group">
          <input type="text" name="name" placeholder="Kategorie-Name" required>
        </div>
        <button type="submit" name="add_category" class="btn">
          <i class="fas fa-plus"></i> Hinzufügen
        </button>
      </form>
    </div>

    <div class="card-grid">
      <?php foreach ($categories as $category): ?>
        <div class="card">
          <h3><?= htmlspecialchars($category['name']) ?></h3>
          <p>Slug: <?= htmlspecialchars($category['slug']) ?></p>
          <a href="wiki_categories.php?delete=<?= $category['id'] ?>" 
             class="btn btn-danger"
             onclick="return confirm('Kategorie wirklich löschen?')">
            <i class="fas fa-trash"></i> Löschen
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>