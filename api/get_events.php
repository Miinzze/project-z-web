<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';

header('Content-Type: application/json');

try {
    $events = $pdo->query("
        SELECT id, title, description, event_date, 
               TIMESTAMPDIFF(MINUTE, NOW(), event_date) as minutes_until,
               CASE 
                   WHEN event_date < NOW() THEN 'past'
                   WHEN TIMESTAMPDIFF(HOUR, NOW(), event_date) < 24 THEN 'soon'
                   ELSE 'future'
               END as status
        FROM events
        WHERE event_date > DATE_SUB(NOW(), INTERVAL 1 DAY)
        ORDER BY event_date
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($events);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
}