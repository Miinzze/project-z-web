<?php
require_once __DIR__.'/../includes/config.php';

header('Content-Type: application/json');

try {
    $db = new PDO(
        "mysql:host={$config['game_db_host']};dbname={$config['game_db_name']}",
        $config['game_db_user'],
        $config['game_db_pass'],
        [PDO::ATTR_TIMEOUT => 3]
    );
    
    if ($db->query("SELECT 1")->fetchColumn() === '1') {
        echo json_encode([
            'status' => 'online',
            'message' => "Verbunden mit {$config['game_db_name']}",
            'version' => $db->getAttribute(PDO::ATTR_SERVER_VERSION)
        ]);
        exit;
    }
} catch (PDOException $e) {
    error_log("DB Ping failed: " . $e->getMessage());
}

echo json_encode([
    'status' => 'offline',
    'message' => "Keine Verbindung zur Game-DB",
    'error' => $e->getMessage() ?? 'Unknown error'
]);
?>