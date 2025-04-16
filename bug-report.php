<?php
require_once __DIR__.'/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $type = $_POST['type'];
    $userId = isset($_SESSION['discord_user']) ? $_SESSION['discord_user']['id'] : 'guest';
    
    $stmt = $pdo->prepare("INSERT INTO bug_reports 
                          (title, description, type, created_by) 
                          VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $description, $type, $userId]);
    
    $_SESSION['report_submitted'] = true;
    header('Location: /bug-report/thanks');
    exit;
}
?>

<!-- Formular anzeigen -->
<form method="POST">
  <div>
    <label>Typ:</label>
    <select name="type" required>
      <option value="bug">Bug melden</option>
      <option value="feature">Feature-Vorschlag</option>
    </select>
  </div>
  <div>
    <label>Titel:</label>
    <input type="text" name="title" required>
  </div>
  <div>
    <label>Beschreibung:</label>
    <textarea name="description" required></textarea>
  </div>
  <button type="submit">Absenden</button>
</form>