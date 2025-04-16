<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Datenbankverbindung
define('DB_HOST', 'localhost');
define('DB_USER', 'd0436715');
define('DB_PASS', 'Wj6hiDjBK3hrGXZY526W');
define('DB_NAME', 'd0436715');

// in includes/config.php
$config['allowed_fonts'] = [
    'Orbitron' => 'Orbitron',
    'Rajdhani' => 'Rajdhani',
    'Aldrich' => 'Aldrich',
    'Michroma' => 'Michroma'
];

$config['upload_dir'] = __DIR__.'/img';

$config['fivem_ip'] = '138.201.122.237';  // z.B. '123.123.123.123'
$config['fivem_port'] = '30125';          // Standard FiveM Port

$config['twitch_client_id'] = 'gp762nuuoqcoxypju8c569th9wz7q5';
$config['twitch_access_token'] = 'oauth:fdk4dy4dgy9zh30ov3enqk09glf2dk';
// Basis-Pfad
define('BASE_URL', 'https://project-z-rp.de');

// Verbindung herstellen
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}
?>