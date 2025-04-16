<?php
error_reporting(E_ALL); 
ini_set('display_errors', 1);

require_once 'includes/config.php';
require_once 'includes/functions.php';


// Design-Einstellungen laden
$settingsFile = __DIR__.'/cache/settings.cache';
$defaultSettings = [
    'font_family' => 'Orbitron',
    'primary_color' => '#00ffaa',
    'background_image' => 'img/background.png',
    'menu_items' => 'Start,Features,Download,Regeln,Discord',
    'logo_url' => 'img/logo.png'
];

if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true);
    $settings = array_merge($defaultSettings, $settings);
} else {
    $settings = $defaultSettings;
}

$primaryRgb = implode(',', hexToRgb($settings['primary_color']));

// News aus der Datenbank laden (für die Hauptseite)
$news = $pdo->query("SELECT * FROM news ORDER BY date DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PROJECT-Z | Zombie Survival</title>
  <link href="https://fonts.googleapis.com/css2?family=<?= urlencode($settings['font_family']) ?>:wght@600&display=swap" rel="stylesheet">
  <style>
:root {
  --primary-color: <?= $settings['primary_color'] ?>;  /* Dynamische Farbe */
  --font-family: '<?= $settings['font_family'] ?>', sans-serif; /* Dynamische Schrift */
}

/* Globale Textfarbe setzen */
body, h1, h2, h3, p, a, .text {
  color: var(--primary-color) !important; /* !important überschreibt statische Werte */
  font-family: var(--font-family) !important;
}

/* Alle bestehenden CSS-Regeln bleiben erhalten */
.hero h1 {
  color: var(--primary);
  text-shadow: 0 0 12px var(--primary);
}

nav a {
  color: var(--primary);
}
    
    .hero {
      background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                  url('<?= $settings['background_image'] ?>') center/cover no-repeat fixed;
    }
    
    .hero h1 {
      color: var(--primary);
      text-shadow: 0 0 12px var(--primary);
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      margin: 0;
      font-family: 'Orbitron', sans-serif;
      background-color: #0a0a0a;
      color: #f5f5f5;
    }

    header {
      background-color: rgba(0, 0, 0, 0.7);
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    nav a {
      color: #00ffaa;
      text-decoration: none;
      margin: 0 15px;
      font-size: 1rem;
      transition: color 0.3s ease;
    }

    nav a:hover {
      color: #fff;
    }

    .hero {
      height: 100vh;
      background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                  url('/img/background.png') center/cover no-repeat fixed;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 0 20px;
    }

    .hero h1 {
      font-size: 4rem;
      color: #00ffaa;
      text-shadow: 0 0 12px #00ffaa;
      margin: 0;
    }

    .hero p {
      font-size: 1.3rem;
      color: #ddd;
      max-width: 600px;
      margin-top: 20px;
    }

    section {
      padding: 60px 20px;
    }

    .features, .rules, .download, .discord {
      background: #141414;
    }

    .section-title {
      font-size: 2rem;
      color: #00ffaa;
      text-align: center;
      margin-bottom: 40px;
    }

    .feature-grid {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
    }

    .feature {
      background: #1e1e1e;
      border: 1px solid #00ffaa44;
      padding: 30px;
      border-radius: 12px;
      flex: 1 1 300px;
      max-width: 350px;
      box-shadow: 0 0 10px #00ffaa22;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .feature:hover {
      transform: translateY(-10px);
      box-shadow: 0 0 20px #00ffaa88;
    }

    footer {
      background: #0f0f0f;
      text-align: center;
      padding: 30px;
      color: #888;
      font-size: 0.9rem;
    }

    .news {
      background-color: #121212;
      padding: 60px 20px;
    }

    .news-container {
      max-width: 1000px;
      margin: auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      padding: 0 20px;
    }

    .news-item {
      background: #1a1a1a;
      border-left: 4px solid <?= $settings['primary_color'] ?>;
      margin-bottom: 15px;
      border-radius: 6px;
      overflow: hidden; /* Wichtig für Animation */
    }

    .news-header {
      padding: 15px;
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .news-item:hover {
      transform: scale(1.02);
      box-shadow: 0 0 15px #00ffaa66;
    }

    .news-item h3 {
      margin: 0;
      color: <?= $settings['primary_color'] ?>;
    }

    .news-content {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1); /* Smooth easing */
      padding: 0 15px;
    }

    .news-content-inner {
      padding: 0 0 15px 0;
    }

    .news-item.active .news-content {
      max-height: 1000px; /* Großzügiger Wert für langen Content */
    }

    .chevron {
      transition: transform 0.3s ease;
    }
.news-date {
  color: #888;
  font-size: 0.9rem;
  margin-top: 5px;
}

.news-item.active .chevron {
  transform: rotate(180deg);
}

    /* Admin-Link im Header */
    .admin-link {
      color: #ff5555 !important;
      font-weight: bold;
    }

      /* Zentrierungs-Wrapper */
  .server-status-wrapper {
    display: flex;
    justify-content: center;
    width: 100%;
    margin-top: 20px;
  }

      /* Zusätzliches CSS für die Status-Anzeige */
      .server-status-compact {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    background: rgba(0, 0, 0, 0.6);
    border-radius: 20px;
    font-family: 'Orbitron', sans-serif;
    font-size: 0.9rem;
    border: 1px solid #00ffaa44;
    backdrop-filter: blur(2px);
    box-shadow: 0 0 10px rgba(0, 255, 170, 0.2);
  }

  .status-icon {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #888;
    box-shadow: 0 0 5px currentColor;
    flex-shrink: 0;
  }

  .status-indicator {
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background: #888;
    box-shadow: 0 0 10px currentColor;
  }

  .status-text {
    font-family: 'Orbitron', sans-serif;
    font-size: 1.1rem;
    color: #fff;
  }

  .status-online {
    background: #00ffaa;
    color: #00ffaa;
    animation: pulse 1.5s infinite alternate;
  }

  .status-offline {
    background: #ff5555;
    color: #ff5555;
  }

  @keyframes pulse {
    0% { opacity: 0.7; }
    100% { opacity: 1; }
  }

  .twitch-carousel-wrapper {
    margin-top: 30px;
    text-align: center;
}

.twitch-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-top: 15px;
}

.twitch-streams {
    display: flex;
    overflow: hidden;
    width: 400px;
    scroll-behavior: smooth;
}

.twitch-stream {
    flex: 0 0 100%;
    transition: transform 0.3s ease;
}

.twitch-nav {
    background: rgba(0,0,0,0.5);
    border: 1px solid var(--primary-color);
    color: var(--primary-color);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    cursor: pointer;
    font-size: 18px;
}

.twitch-nav:hover {
    background: var(--primary-color);
    color: #000;
}

.stream-info {
    margin-top: 10px;
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

/* Eventkalender Styles */
.events {
    background: #141414;
    padding: 60px 20px;
}

.compact-calendar {
    max-width: 600px;
    margin: 0 auto 40px;
    background: #1e1e1e;
    border: 1px solid rgba(var(--primary-rgb), 0.3);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.calendar-header button {
    background: none;
    border: none;
    color: var(--primary);
    font-size: 1.2rem;
    cursor: pointer;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 5px;
}

.calendar-day-header {
    text-align: center;
    font-weight: bold;
    padding: 5px;
    color: var(--primary);
}

.calendar-day {
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    background: #2a2a2a;
    cursor: pointer;
    transition: all 0.2s ease;
}

.calendar-day:hover {
    background: rgba(var(--primary-rgb), 0.2);
}

.calendar-day.has-events {
    background: rgba(var(--primary-rgb), 0.3);
    position: relative;
}

.calendar-day.has-events::after {
    content: '';
    position: absolute;
    top: 5px;
    right: 5px;
    width: 6px;
    height: 6px;
    background: var(--primary);
    border-radius: 50%;
}

.calendar-day.today {
    border: 2px solid var(--primary);
}

.upcoming-events {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.event-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.event-card {
    background: #1e1e1e;
    border-left: 3px solid var(--primary);
    padding: 15px;
    text-align: left;
    border-radius: 4px;
}

.event-date {
    color: var(--primary);
    font-weight: bold;
}

.event-title {
    margin: 5px 0;
}

/* Modal für Event-Details */
.event-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.event-modal-content {
    background: #1e1e1e;
    padding: 30px;
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    border: 1px solid var(--primary);
    position: relative;
}

.close-modal {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 1.5rem;
    color: var(--primary);
    cursor: pointer;
}

@media (max-width: 768px) {
    .calendar-grid {
        gap: 2px;
    }
    
    .event-list {
        grid-template-columns: 1fr;
    }
}
  </style>
</head>
<body>

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

  <section class="hero" id="start">
  <div class="hero-content">
      <div style="margin-bottom:20px;">
      <a href="/whitelist" class="btn" style="background:var(--primary);color:#000;padding:12px 30px;border-radius:6px;text-decoration:none;font-weight:bold;">
        <i class="fas fa-file-alt"></i> Whitelist beantragen
      </a>
    </div>
    <h1 style="color: var(--primary-color);">Willkommen bei PROJECT-Z</h1>
    <center><p class="text" style="font-family: var(--font-family);">Überlebe die Apokalypse. Baue deine Basis. Jage Zombies. Verbünde dich – oder stirb allein.</p></center>
    
    <!-- Zentral ausgerichtete Status-Anzeige -->
    <div class="server-status-wrapper">
      <div class="server-status-compact">
        <span class="status-icon" id="server-status-icon"></span>
        <span id="server-status-text">Status wird geprüft...</span>
        <span id="player-count" style="display:none;">
          (<span id="online-players">0</span>/<span id="max-players">0</span>)
        </span>
      </div>
    </div>
  </div>

  <div class="twitch-carousel-wrapper">
    <h3>Live Streams</h3>
    <div class="twitch-carousel">
        <?php
        require_once __DIR__.'/includes/twitch_api.php';
        $twitch = new TwitchAPI($config['twitch_client_id'], $config['twitch_access_token']);
        $streamers = getTwitchStreamers($pdo);
        $liveStreamers = $twitch->getLiveStreamers($streamers);
        
        if (!empty($liveStreamers)): 
        ?>
        <div class="twitch-container">
            <button class="twitch-nav prev">❮</button>
            
            <div class="twitch-streams">
                <?php foreach ($liveStreamers as $streamer): ?>
                <div class="twitch-stream">
                    <iframe
                        src="https://player.twitch.tv/?channel=<?= $streamer['username'] ?>&parent=project-z-rp.de"
                        height="300"
                        width="400"
                        frameborder="0"
                        scrolling="no"
                        allowfullscreen>
                    </iframe>
                    <div class="stream-info">
                        <h4><?= htmlspecialchars($streamer['display_name']) ?></h4>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button class="twitch-nav next">❯</button>
        </div>
        <?php else: ?>
        <p>Aktuell keine Live-Streams</p>
        <?php endif; ?>
    </div>
</div>
</section>

  <section class="news" id="news">
    <h2 class="section-title">Neuigkeiten</h2>
    <div class="news-container">
      <?php foreach ($news as $item): ?>
        <div class="news-item">
          <div class="news-header">
            <div>
              <h3><?= htmlspecialchars($item['title']) ?></h3>
              <span class="news-date"><?= date('d.m.Y', strtotime($item['date'])) ?></span>
            </div>
            <i class="fas fa-chevron-down chevron"></i>
          </div>
          <div class="news-content">
            <div class="news-content-inner">
              <?= nl2br(htmlspecialchars($item['content'])) ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>  

<!-- Features Section -->
<section class="features" id="features">
    <h2 class="section-title">Features</h2>
    <div class="feature-grid">
        <?php
        $features = $pdo->query("SELECT * FROM features WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6")->fetchAll();
        foreach ($features as $feature):
        ?>
        <div class="feature">
            <h3><?= $feature['icon'] ?><?= htmlspecialchars($feature['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($feature['description'])) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

  <section class="rules" id="regeln">
    <h2 class="section-title">Server-Regeln</h2>
    
    <div class="rule-accordion">
        <?php
        $rules = $pdo->query("SELECT * FROM rules WHERE is_active = 1 ORDER BY sort_order, category, title")->fetchAll();
        $currentCategory = null;
        
        foreach ($rules as $rule):
            if ($rule['category'] !== $currentCategory):
                $currentCategory = $rule['category'];
        ?>
        <h3 style="color:#00ffaa; margin:30px 0 15px 0;"><?= htmlspecialchars($currentCategory) ?></h3>
            <?php endif; ?>
            
            <div class="rule-item">
                <div class="rule-header" onclick="toggleRule(<?= $rule['id'] ?>)">
                    <div class="rule-title">
                        <?= htmlspecialchars($rule['title']) ?>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                <div class="rule-content">
                    <div class="rule-content-inner">
                        <?= nl2br(htmlspecialchars($rule['description'])) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="events" id="events">
    <h2 class="section-title">Eventkalender</h2>
    
    <!-- Kompakte Monatsansicht -->
    <div class="compact-calendar">
        <div class="calendar-header">
            <button class="prev-month"><i class="fas fa-chevron-left"></i></button>
            <h3 id="current-month-year"></h3>
            <button class="next-month"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="calendar-grid" id="calendar-grid"></div>
    </div>

    <!-- Event-Liste (kommende 3 Events) -->
    <div class="upcoming-events">
        <h3>Kommende Events</h3>
        <div class="event-list" id="upcoming-events">
            <!-- Wird via JavaScript befüllt -->
        </div>
        <a href="#" id="view-all-events" style="color:var(--primary);">Alle Events anzeigen</a>
    </div>
</section>

  <footer>
    &copy; 2025 PROJECT-Z | Alle Rechte vorbehalten.
    <div style="margin-top: 20px;">
        <a href="impressum.php" style="color: <?= $settings['primary_color'] ?>;">Impressum</a>
    </div>
  </footer>

  <div id="cookie-consent" style="position:fixed;bottom:0;left:0;right:0;background:#1e1e1e;padding:15px;z-index:1000;display:none;border-top:1px solid var(--primary);">
  <div style="max-width:1200px;margin:0 auto;display:flex;flex-wrap:wrap;align-items:center;gap:15px;">
    <div style="flex:1;min-width:250px;">
      <p style="margin:0;">Wir verwenden Cookies, um Ihnen das beste Erlebnis zu bieten. Durch die Nutzung unserer Website stimmen Sie unserer <a href="/datenschutz" style="color:var(--primary);">Cookie-Richtlinie</a> zu.</p>
    </div>
    <div>
      <button id="cookie-accept" style="background:var(--primary);color:#000;border:none;padding:8px 20px;border-radius:4px;cursor:pointer;">Akzeptieren</button>
    </div>
  </div>
</div>
<script>

// Eventkalender Funktionalität
document.addEventListener('DOMContentLoaded', function() {
    // Globale Variablen
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    const eventsData = [];
    
    // DOM Elemente
    const monthYearElement = document.getElementById('current-month-year');
    const calendarGrid = document.getElementById('calendar-grid');
    const upcomingEventsList = document.getElementById('upcoming-events');
    const eventModal = document.createElement('div');
    eventModal.className = 'event-modal';
    eventModal.innerHTML = `
        <div class="event-modal-content">
            <span class="close-modal">&times;</span>
            <h3 id="modal-event-title"></h3>
            <div id="modal-event-date" class="event-date"></div>
            <div id="modal-event-description"></div>
        </div>
    `;
    document.body.appendChild(eventModal);
    
    // Monatsnamen
    const monthNames = ["Januar", "Februar", "März", "April", "Mai", "Juni", 
                       "Juli", "August", "September", "Oktober", "November", "Dezember"];
    
    // Events vom Server laden
    function loadEvents() {
        fetch('/api/get_events.php')
            .then(response => response.json())
            .then(events => {
                eventsData.length = 0; // Array leeren
                eventsData.push(...events);
                renderCalendar();
                renderUpcomingEvents();
            });
    }
    
    // Kalender rendern
    function renderCalendar() {
        // Monatsüberschrift aktualisieren
        monthYearElement.textContent = `${monthNames[currentMonth]} ${currentYear}`;
        
        // Ersten Tag des Monats und Wochentag ermitteln
        const firstDay = new Date(currentYear, currentMonth, 1);
        const startingDay = firstDay.getDay();
        
        // Letzten Tag des Monats ermitteln
        const lastDay = new Date(currentYear, currentMonth + 1, 0).getDate();
        
        // Kalender leeren
        calendarGrid.innerHTML = '';
        
        // Wochentags-Header hinzufügen
        const dayNames = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'];
        dayNames.forEach(day => {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day-header';
            dayElement.textContent = day;
            calendarGrid.appendChild(dayElement);
        });
        
        // Leere Tage am Anfang füllen
        for (let i = 0; i < (startingDay === 0 ? 6 : startingDay - 1); i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day empty';
            calendarGrid.appendChild(emptyDay);
        }
        
        // Tage des Monats hinzufügen
        for (let day = 1; day <= lastDay; day++) {
            const date = new Date(currentYear, currentMonth, day);
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            dayElement.textContent = day;
            
            // Heutigen Tag markieren
            const today = new Date();
            if (date.getDate() === today.getDate() && 
                date.getMonth() === today.getMonth() && 
                date.getFullYear() === today.getFullYear()) {
                dayElement.classList.add('today');
            }
            
            // Events für diesen Tag prüfen
            const dayEvents = eventsData.filter(event => {
                const eventDate = new Date(event.event_date);
                return eventDate.getDate() === day && 
                       eventDate.getMonth() === currentMonth && 
                       eventDate.getFullYear() === currentYear;
            });
            
            if (dayEvents.length > 0) {
                dayElement.classList.add('has-events');
                dayElement.addEventListener('click', () => showDayEvents(dayEvents));
            }
            
            calendarGrid.appendChild(dayElement);
        }
    }
    
    // Kommende Events anzeigen
    function renderUpcomingEvents() {
        const now = new Date();
        const upcoming = eventsData
            .filter(event => new Date(event.event_date) > now)
            .sort((a, b) => new Date(a.event_date) - new Date(b.event_date))
            .slice(0, 3);
        
        upcomingEventsList.innerHTML = '';
        
        if (upcoming.length === 0) {
            upcomingEventsList.innerHTML = '<p>Keine bevorstehenden Events</p>';
            return;
        }
        
        upcoming.forEach(event => {
            const eventDate = new Date(event.event_date);
            const eventElement = document.createElement('div');
            eventElement.className = 'event-card';
            eventElement.innerHTML = `
                <div class="event-date">${eventDate.toLocaleDateString('de-DE', { 
                    weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' 
                })}</div>
                <h4 class="event-title">${event.title}</h4>
                <p>${event.description.substring(0, 50)}...</p>
            `;
            eventElement.addEventListener('click', () => showEventDetails(event));
            upcomingEventsList.appendChild(eventElement);
        });
    }
    
    // Event-Details anzeigen
    function showEventDetails(event) {
        const eventDate = new Date(event.event_date);
        document.getElementById('modal-event-title').textContent = event.title;
        document.getElementById('modal-event-date').textContent = 
            eventDate.toLocaleDateString('de-DE', { 
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
        document.getElementById('modal-event-description').innerHTML = event.description;
        eventModal.style.display = 'flex';
    }
    
    // Events eines Tages anzeigen
    function showDayEvents(events) {
        const modalContent = eventModal.querySelector('.event-modal-content');
        modalContent.innerHTML = `
            <span class="close-modal">&times;</span>
            <h3>Events am ${events[0].event_date.split(' ')[0]}</h3>
            <div class="day-events-list">
                ${events.map(event => `
                    <div class="day-event">
                        <h4>${event.title}</h4>
                        <div class="event-time">${event.event_date.split(' ')[1]}</div>
                        <p>${event.description}</p>
                    </div>
                `).join('')}
            </div>
        `;
        eventModal.style.display = 'flex';
    }
    
    // Event-Modal schließen
    eventModal.addEventListener('click', (e) => {
        if (e.target === eventModal || e.target.classList.contains('close-modal')) {
            eventModal.style.display = 'none';
        }
    });
    
    // Monatsnavigation
    document.querySelector('.prev-month').addEventListener('click', () => {
        if (currentMonth === 0) {
            currentMonth = 11;
            currentYear--;
        } else {
            currentMonth--;
        }
        renderCalendar();
    });
    
    document.querySelector('.next-month').addEventListener('click', () => {
        if (currentMonth === 11) {
            currentMonth = 0;
            currentYear++;
        } else {
            currentMonth++;
        }
        renderCalendar();
    });
    
    // Alle Events anzeigen
    document.getElementById('view-all-events').addEventListener('click', (e) => {
        e.preventDefault();
        showDayEvents(eventsData
            .filter(event => new Date(event.event_date) >= new Date())
            .sort((a, b) => new Date(a.event_date) - new Date(b.event_date)));
    });
    
    // Initial laden
    loadEvents();
});
// Frontend Aufklappfunktion
function toggleRule(id) {
    const rule = document.querySelector(`#regeln .rule-item:nth-child(${id})`);
    rule.classList.toggle('active');
    
    // Rotate icon
    const icon = rule.querySelector('.fa-chevron-down');
    icon.style.transform = rule.classList.contains('active') ? 'rotate(180deg)' : 'rotate(0)';
}

// FiveM Server Status Check
function checkServerStatus() {
  fetch('api/server_status_index.php')
    .then(res => res.json())
    .then(data => {
      const icon = document.getElementById('server-status-icon');
      const text = document.getElementById('server-status-text');
      const players = document.getElementById('player-count');
      
      if (data.online) {
        icon.className = 'status-icon status-online';
        text.textContent = 'SERVER ONLINE';
        document.getElementById('online-players').textContent = data.players;
        document.getElementById('max-players').textContent = data.max;
        players.style.display = 'inline';
      } else {
        icon.className = 'status-icon status-offline';
        text.textContent = 'SERVER OFFLINE';
        players.style.display = 'none';
      }
    });
}

// Initialer Check + alle 30 Sekunden
checkServerStatus();
setInterval(checkServerStatus, 30000);

document.querySelectorAll('.news-header').forEach(header => {
  header.addEventListener('click', () => {
    const item = header.closest('.news-item');
    const content = item.querySelector('.news-content');
    
    // Schließe alle anderen geöffneten Items
    document.querySelectorAll('.news-item.active').forEach(openItem => {
      if (openItem !== item) {
        openItem.classList.remove('active');
      }
    });
    
    // Toggle aktuelles Item
    item.classList.toggle('active');
    
    // Scrollt sanft zum geöffneten Element
    if (item.classList.contains('active')) {
      item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  });
});

document.addEventListener('DOMContentLoaded', function() {
    const streams = document.querySelector('.twitch-streams');
    const prevBtn = document.querySelector('.twitch-nav.prev');
    const nextBtn = document.querySelector('.twitch-nav.next');
    let currentIndex = 0;
    const streamCount = <?= count($liveStreamers) ?>;

    function showStream(index) {
        streams.scrollTo({
            left: index * 420, // 400px width + 20px gap
            behavior: 'smooth'
        });
    }

    prevBtn.addEventListener('click', function() {
        currentIndex = (currentIndex - 1 + streamCount) % streamCount;
        showStream(currentIndex);
    });

    nextBtn.addEventListener('click', function() {
        currentIndex = (currentIndex + 1) % streamCount;
        showStream(currentIndex);
    });

    // Automatisches Rotieren alle 10 Sekunden
    setInterval(function() {
        if (streamCount > 1) {
            currentIndex = (currentIndex + 1) % streamCount;
            showStream(currentIndex);
        }
    }, 10000);
});

document.addEventListener('DOMContentLoaded', function() {
  if (!localStorage.getItem('cookie_consent')) {
    document.getElementById('cookie-consent').style.display = 'block';
  }
  
  document.getElementById('cookie-accept').addEventListener('click', function() {
    localStorage.setItem('cookie_consent', 'true');
    document.getElementById('cookie-consent').style.display = 'none';
  });
});
</script>
</body>
</html>