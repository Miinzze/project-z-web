<?php
// dashboard.php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';

// Zugriffskontrolle
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

// FiveM Server Status abfragen
function checkFiveMServerStatus($ip, $port) {
    $url = "http://{$ip}:{$port}/dynamic.json";
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response === false) return false;
        
        $data = json_decode($response, true);
        return [
            'online' => true,
            'players' => $data['clients'] ?? 0,
            'max' => $data['sv_maxclients'] ?? 0,
            'servername' => $data['hostname'] ?? 'FiveM Server'
        ];
    } catch (Exception $e) {
        return ['online' => false];
    }
}

// Server-Daten abfragen (IP/Port aus Config laden)
$serverStatus = checkFiveMServerStatus(
    $config['fivem_ip'] ?? '127.0.0.1', 
    $config['fivem_port'] ?? '30120'
);

// Statistik-Daten abfragen
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$newsCount = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
$wikiPageCount = $pdo->query("SELECT COUNT(*) FROM wiki_pages")->fetchColumn();
$wikiCategoryCount = $pdo->query("SELECT COUNT(*) FROM wiki_categories")->fetchColumn();
$twitchStreamerCount = $pdo->query("SELECT COUNT(*) FROM twitch_streamers WHERE is_active = 1")->fetchColumn();
$liveStreamerCount = $pdo->query("SELECT COUNT(*) FROM twitch_streamers WHERE is_active = 1 AND last_live_check = 1")->fetchColumn();

// Seitentitel mit dynamischem Benutzernamen
$pageTitle = "PROJECT-Z ADMIN | " . htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary: #00ffaa;
      --primary-dark: #00cc88;
      --bg-dark: #0a0a0a;
      --bg-darker: #050505;
      --bg-light: #141414;
      --text-light: #f5f5f5;
      --text-muted: #888;
      --primary-color: <?= $settings['primary_color'] ?>;
    }

    body {
      margin: 0;
      font-family: 'Orbitron', sans-serif;
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

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
      margin-bottom: 50px;
    }

    .stat-card {
      background: var(--bg-light);
      border: 1px solid var(--primary);
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0, 255, 170, 0.1);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 0 30px rgba(0, 255, 170, 0.3);
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: var(--primary);
    }

    .stat-card h3 {
      color: var(--primary);
      margin-top: 0;
      font-size: 1.3rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .stat-number {
      font-size: 2.8rem;
      font-weight: bold;
      margin: 20px 0;
      background: linear-gradient(135deg, var(--primary), #00ccff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .admin-menu {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 25px;
    }

    .menu-card {
      background: var(--bg-light);
      border: 1px solid rgba(0, 255, 170, 0.3);
      padding: 30px;
      border-radius: 12px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .menu-card:hover {
      border-color: var(--primary);
      box-shadow: 0 0 25px rgba(0, 255, 170, 0.2);
      transform: translateY(-3px);
    }

    .menu-card a {
      color: var(--text-light);
      text-decoration: none;
      display: flex;
      flex-direction: column;
      height: 100%;
    }

    .menu-card i {
      font-size: 2.5rem;
      margin-bottom: 20px;
      color: var(--primary);
      text-align: center;
    }

    .menu-card h3 {
      margin: 0 0 10px 0;
      font-size: 1.4rem;
      color: var(--primary);
      text-align: center;
    }

    .menu-card p {
      color: var(--text-muted);
      text-align: center;
      flex-grow: 1;
    }

    .alert {
      padding: 15px;
      margin-bottom: 30px;
      border-radius: 8px;
      border-left: 4px solid;
    }

    .alert-success {
      background: rgba(0, 255, 170, 0.1);
      border-color: var(--primary);
      color: var(--primary);
    }

    .last-login {
      color: var(--text-muted);
      font-size: 0.9rem;
      margin-top: 5px;
    }

    /* Neues CSS für Server-Status */
    .server-status {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
    }
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }
    .status-online {
        background: #00ffaa;
        box-shadow: 0 0 10px #00ffaa;
    }
    .status-offline {
        background: #ff5555;
        box-shadow: 0 0 10px #ff5555;
    }
    .player-count {
        font-size: 1.2rem;
        color: #00ffaa;
    }

    .stat-card .fa-twitch {
  color: #9147ff; /* Twitch-Lila */
  font-size: 1.5rem;
}

@media (max-width: 768px) {
  /* Header Anpassungen */
  header {
    flex-direction: column;
    padding: 15px;
  }
  
  nav {
    margin-top: 15px;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
  }
  
  nav a {
    margin: 5px 10px;
  }
  
  /* Hero Bereich */
  .hero h1 {
    font-size: 2.5rem;
  }
  
  /* Feature Grid */
  .feature-grid {
    grid-template-columns: 1fr;
  }
  
  /* Whitelist Fragen */
  .question-box {
    padding: 15px;
    margin-bottom: 20px;
  }
  
  /* Kalender */
  #calendar-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  /* Noch kleinere Bildschirme */
  .hero h1 {
    font-size: 2rem;
  }
  
  #calendar-grid {
    grid-template-columns: 1fr;
  }
}
  </style>
</head>
<body>
  <header class="admin-header">
    <div style="color:#00ffaa; font-size: 1.5rem;">
      PROJECT-Z <span style="color:#fff;">ADMIN</span>
    </div>
    <nav class="admin-nav">
      <span style="color:#ddd;">
        <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['username']) ?>
      </span>
      <a href="logout.php" style="margin-left: 30px;">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </nav>
  </header>

  <div class="admin-container">
    <h1 class="admin-title">Admin Dashboard</h1>
    


    <div class="stats-grid">
      <!-- FiveM Server Status Card -->
      <div class="stat-card">
        <h3><i class="fas fa-gamepad"></i> FiveM Server</h3>
        <div class="server-status">
          <span class="status-indicator <?= $serverStatus['online'] ? 'status-online' : 'status-offline' ?>"></span>
          <?php if ($serverStatus['online']): ?>
            <span class="player-count">
              <?= $serverStatus['players'] ?> / <?= $serverStatus['max'] ?> Spieler
            </span>
          <?php else: ?>
            <span>Offline</span>
          <?php endif; ?>
        </div>
        <div style="margin-top: 15px;">
          <small><?= htmlspecialchars($serverStatus['servername'] ?? 'N/A') ?></small>
        </div>
        <div style="margin-top: 20px;">
          <button onclick="refreshServerStatus()" class="btn-refresh">
            <i class="fas fa-sync-alt"></i> Aktualisieren
          </button>
        </div>
      </div>
      
      <div class="stat-card">
        <h3><i class="fas fa-users"></i> Benutzer</h3>
        <div class="stat-number"><?= $userCount ?></div>
        <p>Registrierte Administratoren</p>
      </div>
      
      <div class="stat-card">
        <h3><i class="fas fa-newspaper"></i> News</h3>
        <div class="stat-number"><?= $newsCount ?></div>
        <p>Aktive Neuigkeiten</p>
      </div>
      
      <!-- Neue Statistik-Karte für Wiki-Einträge -->
      <div class="stat-card">
        <h3><i class="fas fa-book"></i> Wiki-Einträge</h3>
        <div class="stat-number"><?= $wikiPageCount ?></div>
        <p>Verfügbare Artikel</p>
      </div>
      
      <!-- Neue Statistik-Karte für Wiki-Kategorien -->
      <div class="stat-card">
        <h3><i class="fas fa-tags"></i> Wiki-Kategorien</h3>
        <div class="stat-number"><?= $wikiCategoryCount ?></div>
        <p>Verfügbare Kategorien</p>
      </div>
    </div>
    
    <div class="stat-card">
        <h3><i class="fab fa-twitch"></i> Streamer</h3>
        <div class="stat-number"><?= $twitchStreamerCount ?>
            <span class="live-count">(<?= $liveStreamerCount ?> live)</span>
        </div>
        <p>Registrierte Streamer</p>
    </div>

    <h2 style="color: #00ffaa; margin-bottom: 20px;">Verwaltung</h2>
    
    <div class="admin-menu">
      <div class="menu-card">
        <a href="users.php">
          <i class="fas fa-user-cog"></i>
          <h3>Benutzer</h3>
          <p>Admin-Accounts verwalten und Rechte zuweisen</p>
        </a>
      </div>
      
      <div class="menu-card">
        <a href="news.php">
          <i class="fas fa-newspaper"></i>
          <h3>News</h3>
          <p>Neuigkeiten erstellen und bearbeiten</p>
        </a>
      </div>
      
      <div class="menu-card">
    <a href="wiki_list.php">
        <i class="fas fa-book"></i>
        <h3>Wiki</h3>
        <p>Wiki-Artikel verwalten</p>
    </a>
</div>

<div class="menu-card">
    <a href="wiki_categories.php">
        <i class="fas fa-tags"></i>
        <h3>Wiki-Kategorien</h3>
        <p>Kategorien verwalten</p>
    </a>
</div>

      <div class="menu-card">
        <a href="features.php">
            <i class="fas fa-star"></i>
            <h3>Features</h3>
            <p>Seitenfeatures verwalten</p>
        </a>
    </div>

    <div class="menu-card">
        <a href="twitch_streamers.php">
            <i class="fas fa-tags"></i>
            <h3>Twitch Streamer</h3>
            <p>Streamer verwalten</p>
        </a>
    </div>

    <div class="menu-card">
        <a href="rules.php">
            <i class="fa-solid fa-align-left"></i>
            <h3>Regeln</h3>
            <p>Regeln verwalten</p>
        </a>
    </div>

    <div class="menu-card">
      <a href="whitelist_management.php">
        <i class="fas fa-user-check"></i>
        <h3>Whitelist</h3>
        <p>Bewerbungen verwalten</p>
      </a>
    </div>

    <div class="menu-card">
      <a href="event_management.php">
        <i class="fas fa-calendar-alt"></i>
        <h3>Eventkalender</h3>
        <p>Events verwalten</p>
      </a>
    </div>

    <div class="menu-card">
      <a href="bugtracker.php">
        <i class="fas fa-bug"></i>
        <h3>Bug-Tracker</h3>
        <p>Bugs & Vorschläge</p>
      </a>
    </div>

    <div class="menu-card">
    <a href="impressum_edit.php">
        <i class="fas fa-file-contract"></i>
        <h3>Impressum</h3>
        <p>Impressumstext bearbeiten</p>
    </a>
</div>
      <div class="menu-card">
        <a href="settings.php">
          <i class="fas fa-cog"></i>
          <h3>Einstellungen</h3>
          <p>Systemkonfiguration anpassen</p>
        </a>
      </div>
    </div>
  </div>

  <script>
    // Dynamisches Last-Login-Update
    document.addEventListener('DOMContentLoaded', function() {
      const lastLogin = document.querySelector('.last-login');
      if (lastLogin) {
        setInterval(() => {
          const now = new Date();
          lastLogin.textContent = 'Aktiv: ' + now.toLocaleTimeString();
        }, 1000);
      }
    });

    function refreshServerStatus() {
        fetch('api/server_status.php')
            .then(response => response.json())
            .then(data => {
                document.querySelector('.status-indicator').className = 
                    `status-indicator ${data.online ? 'status-online' : 'status-offline'}`;
                
                if (data.online) {
                    document.querySelector('.player-count').textContent = 
                        `${data.players} / ${data.max} Spieler`;
                    document.querySelector('small').textContent = data.servername;
                } else {
                    document.querySelector('.player-count').textContent = 'Offline';
                }
            });
    }

    // Alle 60 Sekunden aktualisieren
    setInterval(refreshServerStatus, 60000);

    // Alle 5 Minuten Live-Status aktualisieren
setInterval(() => {
  fetch('api/update_stream_status.php')
    .then(response => response.json())
    .then(data => {
      document.querySelector('.stat-number span').textContent = `(+${data.liveCount})`;
    });
}, 300000);
  </script>
</body>
</html>