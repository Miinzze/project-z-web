<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';
checkRole('admin');

// Formulardaten verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $content = $_POST['content'];
    $categories = $_POST['categories'] ?? [];

    // Validierung
    if (empty($title) || empty($slug)) {
        $_SESSION['error'] = "Titel und Slug sind erforderlich!";
        header('Location: wiki_edit.php?id=' . $id);
        exit;
    }

    // Daten speichern
    try {
        $pdo->beginTransaction();

        // Wiki-Seite speichern
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
        $stmt = $pdo->prepare("INSERT INTO wiki_page_category (page_id, category_id) VALUES (?, ?)");
        foreach ($categories as $catId) {
            $stmt->execute([$id, $catId]);
        }

        $pdo->commit();
        $_SESSION['success'] = "Artikel erfolgreich gespeichert!";
        header('Location: wiki_list.php');
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Datenbankfehler: " . $e->getMessage();
        header('Location: wiki_edit.php?id=' . $id);
        exit;
    }
}

// Falls direkt aufgerufen
header('Location: wiki_list.php');