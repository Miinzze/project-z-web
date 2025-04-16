<?php
header('Content-Type: application/json');
$data = @file_get_contents("http://138.201.122.237:30125/dynamic.json", false, stream_context_create(['http'=>['timeout'=>2]]));
echo $data ? json_encode(['online'=>true,'players'=>json_decode($data)->clients??0,'max'=>json_decode($data)->sv_maxclients??32]) : json_encode(['online'=>false]);
?>