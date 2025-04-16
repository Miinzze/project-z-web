<?php
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/functions.php';

// Discord OAuth Login prüfen
if (!isset($_SESSION['discord_user'])) {
    header('Location: /api/discord-auth');
    exit;
}

// 10 zufällige Fragen laden
$questions = $pdo->query("SELECT * FROM whitelist_questions WHERE is_active = 1 ORDER BY RAND() LIMIT 10")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = [];
    $score = 0;
    
    foreach ($questions as $q) {
        $userAnswer = (int)$_POST['q_'.$q['id']];
        $isCorrect = ($userAnswer === $q['correct_answer']);
        
        $answers[] = [
            'question_id' => $q['id'],
            'answer' => $userAnswer,
            'correct' => $isCorrect
        ];
        
        if ($isCorrect) $score++;
    }
    
    // Bewerbung speichern
    $stmt = $pdo->prepare("INSERT INTO whitelist_applications 
                          (discord_id, discord_name, answers, score) 
                          VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['discord_user']['id'],
        $_SESSION['discord_user']['username'],
        json_encode($answers),
        $score
    ]);
    
    $_SESSION['whitelist_submitted'] = true;
    header('Location: /whitelist/status');
    exit;
}

// Design wie wiki_page.php
?>
<!-- Formular mit Fragen anzeigen -->
<form method="POST">
<?php foreach ($questions as $index => $q): ?>
<div class="question-box">
  <h3>Frage <?= $index+1 ?>: <?= htmlspecialchars($q['question']) ?></h3>
  <div class="answers">
    <?php for ($i = 1; $i <= 4; $i++): ?>
    <label>
      <input type="radio" name="q_<?= $q['id'] ?>" value="<?= $i ?>" required>
      <?= htmlspecialchars($q['answer'.$i]) ?>
    </label>
    <?php endfor; ?>
  </div>
</div>
<?php endforeach; ?>
<button type="submit">Bewerbung absenden</button>
</form>