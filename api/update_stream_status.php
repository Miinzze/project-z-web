<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/twitch_api.php';

$twitch = new TwitchAPI($config['twitch_client_id'], $config['twitch_access_token']);
$streamers = $pdo->query("SELECT username FROM twitch_streamers WHERE is_active = 1")->fetchAll();

$liveCount = 0;
foreach ($streamers as $streamer) {
  $isLive = $twitch->getStreamStatus($streamer['username']);
  $pdo->prepare("UPDATE twitch_streamers SET last_live_check = ? WHERE username = ?")
     ->execute([$isLive ? 1 : 0, $streamer['username']]);
  if ($isLive) $liveCount++;
}

echo json_encode(['liveCount' => $liveCount]);