<?php
// includes/functions.php

// Passwort-Hashing
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Passwort-Validierung
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Benutzerrolle prüfen
function checkRole($requiredRole) {
    if (!isset($_SESSION['user_role'])) {
        header('Location: login.php');
        exit;
    }
    
    if ($_SESSION['user_role'] !== $requiredRole && $_SESSION['user_role'] !== 'admin') {
        die('Zugriff verweigert: Unzureichende Berechtigungen');
    }
}

// CSRF-Token generieren
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF-Token validieren
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function updateSettingsCache($pdo) {
    $settings = $pdo->query("SELECT setting_key, setting_value FROM site_settings")
                   ->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $cacheFile = __DIR__.'/../cache/settings.cache';
    file_put_contents($cacheFile, json_encode($settings));
}

function getSiteSettings() {
    $cacheFile = __DIR__.'/../cache/settings.cache';
    $defaults = [
        'font_family' => 'Orbitron',
        'primary_color' => '#00ffaa',
        'background_image' => 'assets/background.png',
        'menu_items' => 'Start,Features,Download,Regeln,Discord',
        'logo_url' => 'assets/logo.png'
    ];
    
    if (file_exists($cacheFile)) {
        return array_merge($defaults, json_decode(file_get_contents($cacheFile), true));
    }
    
    return $defaults;
}

function hexToRgb($hex) {
    $hex = str_replace('#', '', $hex);
    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
        $g = hexdec(substr($hex,1,1).substr($hex,1,1));
        $b = hexdec(substr($hex,2,1).substr($hex,2,1));
    } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    }
    return [$r, $g, $b];
}

function getImpressumContent($pdo) {
    $stmt = $pdo->query("SELECT content FROM impressum LIMIT 1");
    return $stmt->fetchColumn();
}

function updateImpressum($pdo, $content) {
    $content = sanitizeImpressumContent($content);
    $stmt = $pdo->prepare("UPDATE impressum SET content = ? WHERE id = 1");
    return $stmt->execute([$content]);
}

function sanitizeImpressumContent($content) {
    $allowedTags = '<h1><h2><h3><p><a><ul><ol><li><strong><em><br>';
    return strip_tags($content, $allowedTags);
}

// Wiki-Funktionen
function getWikiPages($pdo, $category = null) {
    if ($category) {
        $stmt = $pdo->prepare("SELECT p.* FROM wiki_pages p 
                              JOIN wiki_page_category pc ON p.id = pc.page_id
                              JOIN wiki_categories c ON pc.category_id = c.id
                              WHERE c.slug = ?");
        $stmt->execute([$category]);
    } else {
        $stmt = $pdo->query("SELECT * FROM wiki_pages ORDER BY title");
    }
    return $stmt->fetchAll();
}

function getWikiPage($pdo, $slug) {
    $stmt = $pdo->prepare("SELECT * FROM wiki_pages WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function saveWikiPage($pdo, $id, $title, $slug, $content, $categories = []) {
    $pdo->beginTransaction();
    
    // Seite speichern
    if ($id) {
        $stmt = $pdo->prepare("UPDATE wiki_pages SET title = ?, slug = ?, content = ? WHERE id = ?");
        $stmt->execute([$title, $slug, $content, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO wiki_pages (title, slug, content) VALUES (?, ?, ?)");
        $stmt->execute([$title, $slug, $content]);
        $id = $pdo->lastInsertId();
    }
    
    // Kategorien aktualisieren
    $pdo->prepare("DELETE FROM wiki_page_category WHERE page_id = ?")->execute([$id]);
    $catStmt = $pdo->prepare("INSERT INTO wiki_page_category (page_id, category_id) VALUES (?, ?)");
    foreach ($categories as $catId) {
        $catStmt->execute([$id, $catId]);
    }
    
    $pdo->commit();
    return $id;
}

// Füge diese Funktion hinzu
function getTwitchStreamers($pdo, $onlyActive = true) {
    $sql = "SELECT * FROM twitch_streamers";
    if ($onlyActive) {
        $sql .= " WHERE is_active = 1";
    }
    $sql .= " ORDER BY priority DESC";
    return $pdo->query($sql)->fetchAll();
}