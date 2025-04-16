<?php
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/functions.php';

$settings = getSiteSettings();
$impressumContent = $pdo->query("SELECT content FROM impressum LIMIT 1")->fetchColumn();
$primaryRgb = implode(',', hexToRgb($settings['primary_color']));
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Impressum | <?= htmlspecialchars($settings['site_name'] ?? 'PROJECT-Z') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=<?= urlencode($settings['font_family']) ?>:wght@600&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: <?= $settings['primary_color'] ?>;
      --primary-rgb: <?= $primaryRgb ?>;
      --bg-dark: #0a0a0a;
      --bg-light: #141414;
    }

    /* Konsistentes Homepage-Design */
    body {
      margin: 0;
      font-family: '<?= $settings['font_family'] ?>', sans-serif;
      background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                  url('<?= $settings['background_image'] ?>') center/cover fixed;
      color: #f5f5f5;
    }

    header {
      background-color: rgba(0, 0, 0, 0.7);
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid var(--primary);
    }

    nav a {
      color: var(--primary);
      text-decoration: none;
      margin: 0 15px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    nav a:hover {
      color: #fff;
      text-shadow: 0 0 8px var(--primary);
    }

    /* Content-Bereich wie Homepage */
    .main-container {
      max-width: 1200px;
      margin: 50px auto;
      padding: 0 20px;
    }

    .impressum-box {
      background: rgba(20, 20, 20, 0.9);
      border: 1px solid var(--primary);
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(var(--primary-rgb), 0.2);
      line-height: 1.6;
    }

    h1 {
      color: var(--primary);
      text-shadow: 0 0 12px var(--primary);
      margin-top: 0;
    }

    /* Footer wie Homepage */
    footer {
      background: rgba(0, 0, 0, 0.7);
      text-align: center;
      padding: 30px;
      margin-top: 50px;
      border-top: 1px solid var(--primary);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      header {
        flex-direction: column;
        padding: 15px;
      }
      nav {
        margin-top: 15px;
      }
      .impressum-box {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
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

  <div class="main-container">
    <div class="impressum-box">
      <h1><i class="fas fa-file-contract"></i> Impressum</h1>
      <?= $impressumContent ?>
    </div>
  </div>

  <footer>
    &copy; <?= date('Y') ?> <?= htmlspecialchars($settings['site_name'] ?? 'PROJECT-Z') ?> | 
    <a href="impressum.php" style="color:var(--primary);">Impressum</a>
  </footer>

  <!-- FontAwesome für Icons -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>