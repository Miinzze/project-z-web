<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

checkRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';
    $stmt = $pdo->prepare("UPDATE impressum SET content = ? WHERE id = 1");
    if ($stmt->execute([$content])) {
        $_SESSION['success'] = "Impressum erfolgreich aktualisiert!";
        header('Location: impressum_edit.php');
        exit;
    }
}

$impressumContent = $pdo->query("SELECT content FROM impressum LIMIT 1")->fetchColumn();
$settings = getSiteSettings();
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Impressum bearbeiten | PROJECT-Z ADMIN</title>
  <link href="https://fonts.googleapis.com/css2?family=<?= urlencode($settings['font_family']) ?>:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary: <?= $settings['primary_color'] ?>;
      --bg-dark: #0a0a0a;
      --bg-light: #141414;
      --bg-lighter: #1e1e1e;
      --text-light: #f5f5f5;
    }

    body {
      margin: 0;
      font-family: '<?= $settings['font_family'] ?>', sans-serif;
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

    /* Exakt wie in news.php */
    .form-container {
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

    #editor {
      width: 100%;
      min-height: 300px;
      padding: 12px;
      background: #141414;
      border: 1px solid #00ffaa44;
      border-radius: 6px;
      color: #fff;
      font-family: '<?= $settings['font_family'] ?>', sans-serif;
    }

    .btn {
      padding: 12px 25px;
      font-family: '<?= $settings['font_family'] ?>', sans-serif;
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

    .alert-success {
      background: #00ffaa22;
      border: 1px solid #00ffaa;
      color: #00ffaa;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 6px;
    }
  </style>
</head>
<body>
  <header class="admin-header">
    <div style="color:#00ffaa; font-size: 1.5rem;">PROJECT-Z ADMIN</div>
    <nav class="admin-nav">
      <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Dashboard</a>
      <span style="color:#ddd;">Eingeloggt als: <?= htmlspecialchars($_SESSION['username']) ?></span>
      <a href="logout.php" style="margin-left: 30px;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
  </header>

  <div class="admin-container">
    <h1 class="admin-title">Impressum bearbeiten</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert-success">
        <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
      </div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="form-container">
      <form method="POST">
        <div class="form-group">
          <textarea id="editor" name="content"><?= htmlspecialchars($impressumContent) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> Speichern
        </button>
      </form>
    </div>
  </div>

  <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
  <script>
    CKEDITOR.replace('impressum-editor', {
      skin: 'moono-dark',
      toolbar: [
        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline'] },
        { name: 'links', items: ['Link', 'Unlink'] },
        { name: 'lists', items: ['NumberedList', 'BulletedList'] },
        { name: 'document', items: ['Source'] }
      ]
    });
  </script>
</body>
</html>