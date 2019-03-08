<?php

$url = 'http://www.ekidata.jp/api/l/11302.xml';

$xml = simplexml_load_file($url);

var_dump($xml);
// $api = 'https://www.geocoding.jp/api/?q=';
// $station = '新宿駅';
// $url = $api.$station;

// $xml = simplexml_load_file($url); // これでURLをxmlデータとして使える

// $obj = get_object_vars($xml);

// echo '<pre>';
// var_dump($obj);
// echo '</pre>';

// echo '<hr>';

// $coordinate = $obj['coordinate'];

// $coordinate = get_object_vars($coordinate);

// $lat = $coordinate['lat'];

// var_dump($lat);

// var_dump($xml->coordinate); 
// // lat_dmsやlng_dmsの緯度経度DMSとは何のこと？

// echo '<hr>';

// $lat =  $xml->coordinate->lat[0];

// echo $lat;

// echo '<hr>';

// var_dump($lat);
// // $data = file_get_contents($url);

// echo htmlspecialchars($data);

?>
