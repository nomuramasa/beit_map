<?php require_once('head.php') ?>

<div class='container'>
	<div class='row my-2'>
		<h5 class='col-9'>バイト先を地図で探そう</h5>
		<p class='col-3 text-right mb-0'><a href='explain' target='_blank' class='btn btn-sm btn-secondary'>使い方</a></p>
	</div>
	<form action='./#map' method='post'>
		<div class='row'>
			<input type='text' name='url' class='form-control' placeholder='ショットワークスURLを貼り付け' value='<?php if($_POST["url"]){echo $_POST["url"];} ?>'>

		</div>
		<div class='row mt-2'>
			<input type='text' name='home' class='form-control' placeholder='自宅を入力（近くの建物名などでもOK）' value='<?php if($_POST["home"]){echo $_POST["home"];} ?>'> 
		</div>
		<div class='text-center mt-2'>
			<input type='submit' class='btn btn-success' value='探す'>
		</div>
		
	</form>
</div>


<?php
// $url = 'https://shotworks.jp/sw/list/a_01/wd_2019-02-25/sd_2/md_1/work?istd=UA1lm8k&wtk=1&wdf=2019-02-25&sv=-M1'; // 1ページのみの場合
// スマホ用htmlは別で、クラスとかがまた変わってくるから、常にPC番のhtmlを取得するとか工夫が必要

require_once('phpQuery.php'); // phpQueryの読み込み

$url = $_POST['url'];

// $url = 'https://shotworks.jp/sw/list/a_01/wd_2019-03-01/work?istd=UA1lm8k&wtk=1&wdf=2019-02-26&sv=-M1';
// $url = 'https://shotworks.jp/sw/list/a_01/sd_2/md_1/work?sv='; // 解析したいのURL（条件を整えたページ）をここに代入
$html = file_get_contents($url); // htmlを取得
$host = parse_url($url, PHP_URL_HOST); // ホスト名 
$scheme = parse_url($url, PHP_URL_SCHEME); // スキーマ



$k = 0; // カウント
$pages[$k] = $url; // 今のページのURLを、$pages[0]へ

// 他のページもある場合は、全ページ一気に解析しよう
$pager = phpQuery::newDocument($html)->find('ul.pager:eq(1)'); // ページャー(2箇所中1つのみ)
if($pager){ // 他にもページある場合のみ
	$pages_a = $pager->find('li.link a'); // リンク先

	foreach($pages_a as $page_a){ // 他ページの数だけ回す。この$page_as_aって配列だったの？
		$k++ ; // カウント
		$page_a = pq($page_a); // jQueryでいうところの$()と同様の利用方法

		$href = ($page_a)->attr('href'); // リンク先（ルートパス）
		$page_url = $scheme.'://'.$host.$href;  // ホスト名と組み合わせて、他ページへのURL完成。これも全ページ解析する。

		$pages[$k] = $page_url; // 他のページ達を、配列に格納
	}
}


$i = 0; // カウント
foreach($pages as $page){ // ページ数だけループ

	sleep(1); // 1秒休憩
	$html = file_get_contents($page); // htmlを取得
	$jobs = phpQuery::newDocument($html)->find('.workinfo_wrapper .catch_copy h2.catch a.work_list_click_log'); 
	foreach($jobs as $job){ 
		$job = pq($job);
		$href = ($job)->attr('href'); 
		$job_url = $scheme.'://'.$host.$href; // 仕事詳細のURL

		// まずは、1案件の場所情報を取得できるように
		sleep(1); // 1秒休憩
		$job_html = file_get_contents($job_url); // 1案件のHTML
		$doc = phpQuery::newDocument($job_html);

		$content = $doc->find('.catchPhraseBody')->text(); // タイトルっていうか内容
		// $content = $doc->find('.maincolbodyContents02')->text(); // 多い内容
		$lat = $doc->find('#traffic #latitude')->val(); // 緯度
		$long = $doc->find('#traffic #longitude')->val(); // 経度
		$station = $doc->find('#traffic dl:eq(2) dd span.highlight01')->text(); // 駅
		// $distance = phpQuery::newDocument($job_html)->find('#traffic dl:eq(2) dd'); // 距離

		// 情報に代入
		$jobInfo[$i]['link'] = '<p style="text-align:center"><a href="'.$job_url.'" target="_blank">詳細ページへ</a></p>';	 // リンク
		$jobInfo[$i]['content'] = $content; // 内容

		if(empty($lat)){ //地図なしの場合
			if($station){ // 駅名だけでも取得できた場合
				$url = 'https://www.geocoding.jp/api/?q='.$station;
				sleep(1); // 1秒休憩
				$xml = simplexml_load_file($url); // URLをxmlデータとして扱う
				$obj = get_object_vars($xml); // xmlを配列に
				$coord_xml = $obj['coordinate']; // 1階層潜ってxmlデータを得る
				$coord = get_object_vars($coord_xml); // xmlを配列に
				$lat = $coord['lat'];
				$long = $coord['lng'];
			}
			// $jobInfo[$i]['station'] = $station; // 配列に駅情報も足す
		}

		if($lat && $long){ // 緯度経度セット済みの場合 // 駅名も無い場合は地図にも出ない
			$jobInfo[$i]['lat'] = $lat;
			$jobInfo[$i]['long'] = $long;
		}	

		$i++ ; // カウント
	}
}

// echo '<pre>';
// var_dump($jobInfo);
// echo '</pre>';


if($_POST['home']){ // geocoadingで自宅の緯度経度を取得できた場合
	$home = $_POST['home'];
	$url = 'https://www.geocoding.jp/api/?q='.$home;
	$xml = simplexml_load_file($url); // URLをxmlデータとして扱う
	$obj = get_object_vars($xml); // xmlを配列に
	$coord_xml = $obj['coordinate']; // 1階層潜ってxmlデータを得る
	$coord = get_object_vars($coord_xml); // xmlを配列に
	$lat = $coord['lat'];
	$long = $coord['lng'];
	$homeInfo['lat'] = $lat;
	$homeInfo['long'] = $long;
}else{
	// 野村の家
	$homeInfo['lat'] = 35.8280597;
	$homeInfo['long'] = 139.6800843;
}

?>


<div id='map'></div> <!-- 地図表示 -->

<script>

// phpの配列を使う
var jobInfo = <?php echo json_encode($jobInfo); ?>;
var homeInfo = <?php echo json_encode($homeInfo); ?>;

// 複数ある場合は配列を作る必要あり
var marker=[]; var data=[]; var wor_lat=[]; var wor_long=[]; var iw=[];　var now_iw=null;


function initMap() {

	var myhome = {lat: Number(homeInfo['lat']), lng: Number(homeInfo['long'])} // 自分の家

  // 地図を作成
  map = new google.maps.Map(document.getElementById('map'), {
    zoom: 11,
    center: myhome // 自宅を中心に
  });

	var n = 1 // カウント(0は自宅用)
	for(var job in jobInfo) { // 緯度と経度をたくさん用意
		var info = jobInfo[job]

		if(info['lat'] && info['long']){ // 緯度と経度があるバイトのみ

			// スクレイプした情報をjsに代入
		  var jap_lat = info['lat']
		  var jap_long = info['long'] 

		  // ショットワークの緯度経度は日本基準なので、Google世界基準に合わせる
		  wor_lat = jap_lat - jap_lat * 0.00010695 + jap_long * 0.000017464 + 0.0046017
		  wor_long = jap_long - jap_lat * 0.000046038 - jap_long * 0.000083043 + 0.01004

			// マーカーを作成
		  marker[n] = new google.maps.Marker({
		    position: {lat: wor_lat, lng: wor_long},  
		    map: map
		  });

			// 吹き出し
		  iw[n] = new google.maps.InfoWindow({
		    position: new google.maps.LatLng(wor_lat, wor_long),
		    content: info['content']+'<hr>'+info['link'],
		    pixelOffset: new google.maps.Size(0,-5)
		  });
		  markerEvent(n);  // マーカーにクリックイベントを追加

		  // マーカークリックで吹き出し表示
			function markerEvent(n) {　
				marker[n].addListener('click', function() { // マーカーをクリックしたとき
					if(now_iw) { now_iw.close(); } // 他の吹き出しが開いてる場合は閉じる
					iw[n].open(map, marker[n]); // 吹き出しを表示
					now_iw = iw[n]; // 今の吹き出しを設定しとく
				});
			}

		}
	  n++ // カウント
	}

	// 自宅マーカー
	marker[0] = new google.maps.Marker({
		position: myhome,
		map: map,
		icon: {
			url: 'img/house.png', // 家の画像
			scaledSize: new google.maps.Size(40, 40) //サイズ
		}
	});


}
</script>

<script async defer src='https://maps.googleapis.com/maps/api/js?key=AIzaSyCXkBwyCJA1ic4XfE2NcoI_eKt3zhw75Rk&callback=initMap'>//GoogleMapのAPIキー（&datum=wgs84）</script>

<style> #map{height:100vh;} </style>