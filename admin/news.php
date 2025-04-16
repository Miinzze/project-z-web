<?php
require_once '../includes/config.php';

if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

// News hinzufügen/bearbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $date = $_POST['date'];
    
    if (!empty($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE news SET title = ?, content = ?, date = ? WHERE id = ?");
        $stmt->execute([$title, $content, $date, $_POST['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO news (title, content, date) VALUES (?, ?, ?)");
        $stmt->execute([$title, $content, $date]);
    }
    
    header('Location: news.php');
    exit;
}

// News löschen
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: news.php');
    exit;
}

// Alle News laden
$news = $pdo->query("SELECT * FROM news ORDER BY date DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PROJECT-Z | News verwalten</title>
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

    .news-form {
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
    .form-group textarea {
      width: 100%;
      padding: 12px;
      background: #141414;
      border: 1px solid #00ffaa44;
      border-radius: 6px;
      color: #fff;
      font-family: 'Orbitron', sans-serif;
    }

    .form-group textarea {
      min-height: 150px;
    }

    .form-actions {
      display: flex;
      gap: 15px;
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

    .news-list {
      background: #1e1e1e;
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
      color: #00ffaa;
    }

    .news-item-date {
      color: #888;
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
      background: #00ffaa;
      color: #0a0a0a;
    }

    .delete-btn {
      background: #ff5555;
      color: white;
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
    <h1 class="admin-title">News verwalten</h1>
    
    <div class="news-form">
      <h2 style="color: #00ffaa; margin-top: 0;"><?php echo isset($_GET['edit']) ? 'News bearbeiten' : 'Neue News erstellen'; ?></h2>
      
      <?php
      $editingNews = null;
      if (isset($_GET['edit'])) {
          $editingNews = $pdo->query("SELECT * FROM news WHERE id = " . (int)$_GET['edit'])->fetch();
      }
      ?>
      
      <form method="POST">
        <input type="hidden" name="id" value="<?php echo $editingNews ? $editingNews['id'] : ''; ?>">
        
        <div class="form-group">
          <label for="title">Titel</label>
          <input type="text" id="title" name="title" value="<?php echo $editingNews ? htmlspecialchars($editingNews['title']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
          <label for="content">Inhalt</label>
          <textarea id="content" name="content" required><?php echo $editingNews ? htmlspecialchars($editingNews['content']) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
          <label for="date">Datum</label>
          <input type="date" id="date" name="date" value="<?php echo $editingNews ? $editingNews['date'] : date('Y-m-d'); ?>" required>
        </div>
        
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Speichern
          </button>
          
          <?php if (isset($_GET['edit'])): ?>
            <a href="news.php" class="btn btn-secondary">
              <i class="fas fa-times"></i> Abbrechen
            </a>
          <?php endif; ?>
        </div>
      </form>
    </div>
    
    <h2 style="color: #00ffaa;">Alle News-Einträge</h2>
    
    <div class="news-list">
      <?php foreach ($news as $item): ?>
      <div class="news-item">
        <div class="news-item-info">
          <h3><?php echo htmlspecialchars($item['title']); ?></h3>
          <span class="news-item-date"><?php echo date('d.m.Y', strtotime($item['date'])); ?></span>
        </div>
        
        <div class="news-item-actions">
          <a href="news.php?edit=<?php echo $item['id']; ?>" class="action-btn edit-btn">
            <i class="fas fa-edit"></i> Bearbeiten
          </a>
          <a href="news.php?delete=<?php echo $item['id']; ?>" class="action-btn delete-btn" onclick="return confirm('News wirklich löschen?')">
            <i class="fas fa-trash"></i> Löschen
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>