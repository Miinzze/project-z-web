<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';
checkRole('admin');

if (isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM wiki_pages WHERE id = ?")->execute([$_GET['id']]);
    $_SESSION['success'] = "Artikel erfolgreich gelöscht!";
}

header('Location: wiki_list.php');
exit;