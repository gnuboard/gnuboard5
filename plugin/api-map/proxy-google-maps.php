<?php
require_once("_common.php");

// mapProxy.php
function map_proxy() {
    require_once('./config-map.php');
    
    $url = "https://maps.googleapis.com/maps/api/js?key={$GOOGLE_MAPS_API_KEY}&loading=async&callback=initMap";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    header('Content-Type: application/javascript');
    echo $response;
}

map_proxy();
?>
