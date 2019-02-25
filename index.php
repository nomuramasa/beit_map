<?php
// $url = 'https://shotworks.jp/sw/list/a_01/wd_2019-02-25/sd_2/md_1/work?istd=UA1lm8k&wtk=1&wdf=2019-02-25&sv=-M1'; // 1ページのみの場合
// スマホ用htmlは別で、クラスとかがまた変わってくるから、常にPC番のhtmlを取得するとか工夫が必要

require_once('phpQuery.php'); // phpQueryの読み込み

$url = 'https://shotworks.jp/sw/list/a_01/sd_2/md_1/work?sv='; // 解析したいのURL（条件を整えたページ）をここに代入
$html = file_get_contents($url); // htmlを取得
$host = parse_url($url, PHP_URL_HOST); // ホスト名 


// まずは、1ページ目の中だけ

$jobs = phpQuery::newDocument($html)->find('.workinfo_wrapper .catch_copy h2.catch a.work_list_click_log'); 

$i = 0; // カウント
foreach($jobs as $job){ 
	$job = pq($job); 
	$href = ($job)->attr('href'); 
	$job_url = $host.$href; // 仕事詳細のURL

	// まずは、1案件の場所情報を取得できるように
	// $job_url = 'https://shotworks.jp/sw/detail/W004465270?wtk=1'; // 地図なしページ
	// $job_url = 'https://shotworks.jp/sw/detail/W004464748?wtk=1'; // 地図ありページ
	$job_html = file_get_contents($job_url); // 1案件のHTML
	$doc = phpQuery::newDocument($job_html);

	$title = $doc->find('.catchPhraseBody h2')->text(); // タイトル
	$lat = $doc->find('#traffic #latitude')->val(); // 緯度
	$long = $doc->find('#traffic #longitude')->val(); // 経度
	$station = $doc->find('#traffic dl:eq(2) dd span.highlight01')->text(); // 駅
	// $distance = phpQuery::newDocument($job_html)->find('#traffic dl:eq(2) dd'); // 距離

	// 情報に代入
	$jobInfo[$i]['title'] = $title;
	// echo $jobInfo['title'];

	if($lat && $long){ // 地図あり
		$jobInfo[$i]['lat'] = $lat;
		$jobInfo[$i]['long'] = $long;

	}else{ //地図なし
		$jobInfo[$i]['station'] = $station;
	}

	$i += 1; // カウント
}


// 他のページもある場合は、全ページ一気に解析しよう
	
// $pager = phpQuery::newDocument($html)->find('ul.pager:eq(1)'); // pager(2箇所中1つのみ)
// if($pager){ // 他にもページある場合のみ
	// $pages_a = $pager->find('li.link a'); // リンク先

	// foreach($pages_a as $page_a){ // 他ページの数だけ回す。この$page_as_aって配列だったの？
	// 	$page_a = pq($page_a); // jQueryでいうところの$()と同様の利用方法

	// 	$href = ($page_a)->attr('href'); // リンク先（ルートパス）
	// 	$page_url = $host.$href;  // ホスト名と組み合わせて、他ページへのURLを完成させる。これも全ページ解析する。
	// 	// echo $page_url; // 
	// 	// echo '<br>'; // 改行
	// }
// }


?>

<div id='map'></div> <!-- 地図表示 -->

<?php for($i=0; $i<=0; $i++): ?>

	<script>

	// 複数ある場合は配列を作る必要あり
	var marker=[]; var data=[]; var wor_lat=[]; var wor_long=[]; var iw=[];　var now_iw=null;

	function initMap() {

		// スクレイプした情報をjsに代入
	  var jap_lat = <?php echo $jobInfo[0]['lat']; ?> 
	  var jap_long = <?php echo $jobInfo[0]['long']; ?> 

	  // ショットワークの緯度経度は日本基準なので、Google世界基準に合わせる
	  wor_lat[0] = jap_lat - jap_lat * 0.00010695 + jap_long * 0.000017464 + 0.0046017
	  wor_long[0] = jap_long - jap_lat * 0.000046038 - jap_long * 0.000083043 + 0.01004


	  // 地図を作成
	  map = new google.maps.Map(document.getElementById('map'), {
	    zoom: 13,
	    center: {lat: wor_lat[0], lng: wor_long[0]} //今はマーカー1つだから中心もそこに合わせてるけど、あとで平均を求めるようにする
	  });

		// マーカーを作成
	  marker[0] = new google.maps.Marker({
	    position: {lat: wor_lat[0], lng: wor_long[0]},  
	    map: map
	  });

		// 吹き出し
	  iw[0] = new google.maps.InfoWindow({
	    position: new google.maps.LatLng(wor_lat, wor_long),
	    content: '<?php echo $jobInfo[0]["title"]; ?>',
	    pixelOffset: new google.maps.Size( 0, -5 )
	  });
	  markerEvent(); //(i)にする // マーカーにクリックイベントを追加

	  // マーカークリックで吹き出し表示
		function markerEvent() {　// (i)にする
			marker[0].addListener('click', function() { // マーカーをクリックしたとき
				if(now_iw) { now_iw.close(); } // 他の吹き出しが開いてる場合は閉じる
				iw[0].open(map, marker[0]); // 吹き出しを表示
				now_iw = iw[0]; // 今の吹き出しを設定しとく
			});
		}

	}

	</script>

<?php endfor; ?>

<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBsgF9GId6mfoadD6VKTwkfGO0QGGBmitg&callback=initMap">//GoogleMapのAPIキー（&datum=wgs84）</script>

<style>#map{height:85vh; /*width:100%;*/ /*margin:0 auto*/;}</style>