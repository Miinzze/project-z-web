<?php
require_once __DIR__.'/../../includes/config.php';
require_once __DIR__.'/../../includes/functions.php';

header('Content-Type: application/json');

function getServerStatus() {
    global $config;
    $url = "http://{$config['fivem_ip']}:{$config['fivem_port']}/dynamic.json";
    
    try {
        $response = file_get_contents($url, false, stream_context_create([
            'http' => ['timeout' => 3]
        ]));
        
        $data = json_decode($response, true);
        echo json_encode([
            'online' => true,
            'players' => $data['clients'] ?? 0,
            'max' => $data['sv_maxclients'] ?? 32,
            'servername' => $data['hostname'] ?? 'FiveM Server'
        ]);
    } catch (Exception $e) {
        echo json_encode(['online' => false]);
    }
}

getServerStatus();