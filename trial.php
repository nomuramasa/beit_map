<?php

$api = 'https://www.geocoding.jp/api/?q=';
$station = '新宿駅';
$url = $api.$station;


$xml = simplexml_load_file($url); // これでURLをxmlデータとして使える

var_dump($xml->coordinate); 
// lat_dmsやlng_dmsの緯度経度DMSとは何のこと？

echo '<hr>';

echo $xml->coordinate->lat;

// $data = file_get_contents($url);

// echo htmlspecialchars($data);

?>
