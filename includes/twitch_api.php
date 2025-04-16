<?php
require_once __DIR__.'/config.php';

class TwitchAPI {
    private $clientId;
    private $accessToken;
    
    public function __construct($clientId, $accessToken) {
        $this->clientId = $clientId;
        $this->accessToken = $accessToken;
    }
    
    public function getStreamStatus($username) {
        $url = "https://api.twitch.tv/helix/streams?user_login=".$username;
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'Client-ID: '.$this->clientId,
                    'Authorization: Bearer '.str_replace('oauth:', '', $this->accessToken),
                    'Accept: application/vnd.twitchtv.v5+json'
                ],
                'timeout' => 3
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if($response === FALSE) {
            error_log("Twitch API Error: ".print_r($http_response_header, true));
            return false;
        }
        
        $data = json_decode($response, true);
        return !empty($data['data']);
    }
    
    public function getLiveStreamers($streamers) {
        $liveStreamers = [];
        foreach ($streamers as $streamer) {
            if ($this->getStreamStatus($streamer['username'])) {
                $liveStreamers[] = $streamer;
            }
        }
        return $liveStreamers;
    }
}
?>