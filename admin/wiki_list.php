<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';
checkRole('admin');

$pages = $pdo->query("SELECT wp.*, GROUP_CONCAT(wc.name SEPARATOR ', ') as categories 
                     FROM wiki_pages wp
                     LEFT JOIN wiki_page_category wpc ON wp.id = wpc.page_id
                     LEFT JOIN wiki_categories wc ON wpc.category_id = wc.id
                     GROUP BY wp.id")->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Wiki Artikel | PROJECT-Z ADMIN</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* 1:1 aus news.php übernommen */
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

    /* News-Liste aus news.php */
    .news-list {
      background: var(--bg-light);
      border: 1px solid #00ffaa44;
      border-radius: 12px;
      overflow: hidden;
    }

    .news-item {
      padding: 20px;
      border-bottom: 1px solid #00ffaa22;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .news-item:last-child {
      border-bottom: none;
    }

    .news-item-info h3 {
      margin: 0 0 5px 0;
      color: var(--primary);
    }

    .news-item-date {
      color: var(--text-muted);
      font-size: 0.9rem;
    }

    .news-item-actions {
      display: flex;
      gap: 15px;
    }

    .action-btn {
      padding: 8px 15px;
      border-radius: 4px;
      text-decoration: none;
      font-size: 0.9rem;
    }

    .edit-btn {
      background: var(--primary);
      color: #0a0a0a;
    }

    .delete-btn {
      background: #ff5555;
      color: white;
    }

    /* Button für neue Artikel */
    .btn-new {
      background: var(--primary);
      color: #0a0a0a;
      padding: 12px 25px;
      border-radius: 6px;
      text-decoration: none;
      float: right;
    }
  </style>
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
      <i class="fas fa-book"></i> Wiki Artikel
      <a href="wiki_edit.php" class="btn-new">
        <i class="fas fa-plus"></i> Neu
      </a>
    </h1>

    <!-- Liste jetzt im news.php-Stil -->
    <div class="news-list">
      <?php foreach ($pages as $page): ?>
      <div class="news-item">
        <div class="news-item-info">
          <h3><?= htmlspecialchars($page['title']) ?></h3>
          <span class="news-item-date">
            <?= date('d.m.Y H:i', strtotime($page['updated_at'])) ?> | 
            Kategorien: <?= $page['categories'] ? htmlspecialchars($page['categories']) : '-' ?>
          </span>
        </div>
        <div class="news-item-actions">
          <a href="wiki_edit.php?id=<?= $page['id'] ?>" class="action-btn edit-btn">
            <i class="fas fa-edit"></i> Bearbeiten
          </a>
          <a href="wiki_delete.php?id=<?= $page['id'] ?>" class="action-btn delete-btn" 
             onclick="return confirm('Artikel wirklich löschen?')">
            <i class="fas fa-trash"></i> Löschen
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>