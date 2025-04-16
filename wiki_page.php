<?php
require_once '/includes/config.php';
require_once '/includes/functions.php';

// Wiki-Artikel laden
$slug = $_GET['slug'] ?? '';
$page = $pdo->prepare("SELECT * FROM wiki_pages WHERE slug = ?");
$page->execute([$slug]);
$page = $page->fetch();

if (!$page) {
    header('Location: /wiki');
    exit;
}

// Design-Einstellungen aus der Homepage übernehmen
$settings = getSiteSettings();
$primaryRgb = implode(',', hexToRgb($settings['primary_color']));
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page['title']) ?> | PROJECT-Z Wiki</title>
  <link href="https://fonts.googleapis.com/css2?family=<?= urlencode($settings['font_family']) ?>:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* 1:1 aus index.php übernommen */
    :root {
      --primary: <?= $settings['primary_color'] ?>;
      --primary-rgb: <?= $primaryRgb ?>;
      --bg-dark: #0a0a0a;
      --bg-light: #141414;
      --text-light: #f5f5f5;
      --text-muted: #888;
    }

    body {
      margin: 0;
      font-family: '<?= $settings['font_family'] ?>', sans-serif;
      background-color: var(--bg-dark);
      color: var(--text-light);
      line-height: 1.6;
    }

    /* Header */
    header {
      background-color: rgba(0, 0, 0, 0.7);
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 1000;
      border-bottom: 1px solid rgba(var(--primary-rgb), 0.3);
    }

    nav a {
      color: var(--primary);
      text-decoration: none;
      margin: 0 15px;
      transition: all 0.3s ease;
    }

    nav a:hover {
      color: #fff;
      text-shadow: 0 0 8px var(--primary);
    }

    /* Wiki-Inhaltscontainer */
    .wiki-container {
      max-width: 1000px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .wiki-header {
      text-align: center;
      margin-bottom: 40px;
    }

    .wiki-title {
      color: var(--primary);
      font-size: 2.5rem;
      text-shadow: 0 0 15px var(--primary);
      margin-bottom: 10px;
    }

    .wiki-content {
      background: var(--bg-light);
      border: 1px solid rgba(var(--primary-rgb), 0.3);
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(var(--primary-rgb), 0.1);
    }

    /* Typografie (wie Homepage) */
    h2, h3 {
      color: var(--primary);
      margin-top: 30px;
    }

    a {
      color: var(--primary);
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .wiki-container {
        padding: 0 15px;
      }
      .wiki-content {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <!-- Header exakt wie Homepage -->
  <header>
  <div style="color:var(--primary); font-size: 1.5rem;">
    <?php if (!empty($settings['logo_url'])): ?>
    <img src="<?= $settings['logo_url'] ?>" alt="Logo" height="40" style="vertical-align: middle;">
    <?php endif; ?>
    PROJECT-Z
  </div>
  <nav>
  <?php
  $items = explode(',', $settings['menu_items']);
  $links = explode(',', $settings['menu_links'] ?? '');
  
  foreach ($items as $index => $item): 
    $item = trim($item);
    $link = $links[$index] ?? '#';
  ?>
    <a href="<?= htmlspecialchars($link) ?>"><?= htmlspecialchars($item) ?></a>
  <?php endforeach; ?>
  
  <?php if (isset($_SESSION['logged_in'])): ?>
    <a href="/admin/dashboard.php" class="admin-link">Admin</a>
  <?php endif; ?>
</nav>
</header>

  <!-- Wiki-Inhalt -->
  <main class="wiki-container">
    <div class="wiki-header">
      <h1 class="wiki-title"><?= htmlspecialchars($page['title']) ?></h1>
      <a href="/wiki" style="color:var(--text-muted);">
        <i class="fas fa-arrow-left"></i> Zurück zur Übersicht
      </a>
    </div>

    <div class="wiki-content">
      <?= $page['content'] ?>
    </div>
  </main>

  <!-- Footer wie Homepage -->
  <footer style="text-align:center; padding:30px; color:var(--text-muted);">
    &copy; <?= date('Y') ?> PROJECT-Z | 
    <a href="/impressum" style="color:var(--text-muted);">Impressum</a>
  </footer>
</body>
</html>