<?php
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/functions.php';

// Design-Einstellungen aus der Homepage übernehmen
$settings = getSiteSettings();
$primaryRgb = implode(',', hexToRgb($settings['primary_color']));

// Wiki-Kategorien und Artikel laden
$categories = $pdo->query("SELECT * FROM wiki_categories ORDER BY name")->fetchAll();
$recentArticles = $pdo->query("SELECT title, slug FROM wiki_pages ORDER BY updated_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wiki | PROJECT-Z</title>
  <link href="https://fonts.googleapis.com/css2?family=<?= urlencode($settings['font_family']) ?>:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* 1:1 aus wiki_page.php übernommen */
    :root {
      --primary: <?= $settings['primary_color'] ?>;
      --primary-rgb: <?= $primaryRgb ?>;
      --bg-dark: #0a0a0a;
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

    /* Wiki-Hauptcontainer */
    .wiki-main {
      display: grid;
      grid-template-columns: 250px 1fr;
      gap: 30px;
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
    }

    /* Sidebar */
    .wiki-sidebar {
      background: var(--bg-light);
      border: 1px solid rgba(var(--primary-rgb), 0.3);
      border-radius: 12px;
      padding: 20px;
      height: fit-content;
    }

    .wiki-sidebar h3 {
      color: var(--primary);
      border-bottom: 1px solid rgba(var(--primary-rgb), 0.3);
      padding-bottom: 10px;
      margin-top: 0;
    }

    .wiki-sidebar ul {
      list-style: none;
      padding: 0;
    }

    .wiki-sidebar li {
      margin-bottom: 8px;
    }

    .wiki-sidebar a {
      color: var(--text-light);
      display: block;
      padding: 5px 0;
    }

    .wiki-sidebar a:hover {
      color: var(--primary);
    }

    /* Artikel-Liste */
    .wiki-articles {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
    }

    .wiki-card {
      background: var(--bg-light);
      border: 1px solid rgba(var(--primary-rgb), 0.3);
      border-radius: 12px;
      padding: 20px;
      transition: all 0.3s ease;
    }

    .wiki-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 0 20px rgba(var(--primary-rgb), 0.2);
      border-color: var(--primary);
    }

    .wiki-card h3 {
      color: var(--primary);
      margin-top: 0;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .wiki-main {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <!-- Header exakt wie Homepage/wiki_page.php -->
  <header>
  <div style="color:var(--primary); font-size: 1.5rem;">
  <a href="/" style="color:inherit; text-decoration:none;"> <!-- Link zur Homepage -->
      <?php if (!empty($settings['logo_url'])): ?>
        <img src="<?= $settings['logo_url'] ?>" alt="Logo" height="40" style="vertical-align:middle;">
      <?php endif; ?>
      <?= htmlspecialchars($settings['site_name'] ?? 'PROJECT-Z') ?>
    </a>
    PROJECT-Z
  </div>
  <nav>
  <a href="/">Startseite</a> <!-- Expliziter Link -->

  
  <?php if (isset($_SESSION['logged_in'])): ?>
    <a href="/admin/dashboard.php" class="admin-link">Admin</a>
  <?php endif; ?>
</nav>
</header>

  <!-- Wiki-Hauptbereich -->
  <main class="wiki-main">
    <!-- Sidebar mit Kategorien -->
    <aside class="wiki-sidebar">
      <h3><i class="fas fa-tags"></i> Kategorien</h3>
      <ul>
        <?php foreach ($categories as $category): ?>
          <li>
            <a href="/wiki?category=<?= urlencode($category['slug']) ?>">
              <i class="fas fa-folder"></i> <?= htmlspecialchars($category['name']) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </aside>

    <!-- Artikel-Übersicht -->
    <div class="wiki-content">
      <h1 style="color:var(--primary);text-shadow:0 0 10px var(--primary);margin-top:0;">
        <i class="fas fa-book"></i> Wiki-Übersicht
      </h1>

      <div class="wiki-articles">
        <?php
        $articles = $pdo->query("
          SELECT p.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS categories 
          FROM wiki_pages p
          LEFT JOIN wiki_page_category pc ON p.id = pc.page_id
          LEFT JOIN wiki_categories c ON pc.category_id = c.id
          GROUP BY p.id
          ORDER BY p.title
        ")->fetchAll();

        foreach ($articles as $article):
        ?>
          <article class="wiki-card">
            <h3>
              <a href="/wiki/<?= urlencode($article['slug']) ?>">
                <?= htmlspecialchars($article['title']) ?>
              </a>
            </h3>
            <?php if (!empty($article['categories'])): ?>
              <p style="color:var(--text-muted);font-size:0.9em;">
                <i class="fas fa-tag"></i> <?= htmlspecialchars($article['categories']) ?>
              </p>
            <?php endif; ?>
            <p><?= substr(strip_tags($article['content']), 0, 150) ?>...</p>
            <a href="/wiki/<?= urlencode($article['slug']) ?>" style="color:var(--primary);">
              Mehr lesen <i class="fas fa-arrow-right"></i>
            </a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </main>

  <!-- Footer wie Homepage -->
  <footer style="text-align:center; padding:30px; color:var(--text-muted);">
    &copy; <?= date('Y') ?> PROJECT-Z | 
    <a href="/impressum" style="color:var(--text-muted);">Impressum</a>
  </footer>
</body>
</html>