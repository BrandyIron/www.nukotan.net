<?php
require_once(dirname(__FILE__) . '/../../lib/include/header.php');
require_once(dirname(__FILE__) . '/../../lib/include/footer.php');

publishHeader("ぬこたんグラフ(*´Д`)ﾊｧﾊｧ");

echo <<<EOD
<script type="text/javascript">
	google.load('visualization', '1', {packages: ['corechart']});
</script>
<!-- Refer plot data -->
<script type="text/javascript" src="./deviations.js"></script>
<script type="text/javascript" src="./posttimes.js"></script>
<script type="text/javascript" src="./live_counts.js"></script>

<script type="text/javascript">
	function drawOnmyozaGuitarCovers() {
		var data = google.visualization.arrayToDataTable(deviations);
		var options = {
			title: 'Onmyo-za Guitar Covers',
			vAxis: {
				title: 'Video Title',
				textStyle: {fontSize: 10}
				},
			hAxis: {
				title: 'Deviation',
				gridlines: {color: '#CDCDCD', count: 50},
				minorGridlines: {color: '#CDCDCD', count: 40},
				logScale: true
			},
			legend: {
				position: 'top', textStyle: {fontSize: 12}
			},
			orientation: 'vertical',
			seriesTypre: 'line',
			dataOpacity: 0.5
		};
		var chart = new google.visualization.ComboChart(document.getElementById('chart_onmyo-za_guitar_cover'));
		chart.draw(data, options);
	}

	function drawNekomammaPostTime() {
		var data = google.visualization.arrayToDataTable(posttimes);
		var options = {
			title: 'Nekomamma PostTime',
			is3D: true
		};
		var chart = new google.visualization.PieChart(document.getElementById('chart_nekomamma_posttime'));
		chart.draw(data, options);
	}

	function drawLiveCounts() {
		var data = google.visualization.arrayToDataTable(live_counts);
		var options = {
			title: 'Live Counts',
			vAxis: {title: 'Counts'},
			hAxis: {title: 'Date'},
			series: {3: {targetAxisIndex: 1},
				4: {targetAxisIndex: 1}}
			};
		var chart = new google.visualization.AreaChart(document.getElementById('chart_live_counts'));
		chart.draw(data, options);
	}

	google.setOnLoadCallback(drawOnmyozaGuitarCovers);
	google.setOnLoadCallback(drawNekomammaPostTime);
	google.setOnLoadCallback(drawLiveCounts);
</script>



	<div class="container">
		<div class="page-header">
			<p>　</p>
			<h1>ぬこたんグラフ(*´Д`)ﾊｧﾊｧ</h1>
			<p class="lead">ぬこたんについてグラフ化できるものをまとめます。</p>
		</div>
		<div class="page-container">
			<div class="row">
				<nav class="span9" id="pageindex">
				<div class="well">
					<ul>
						<li><a href="#onmyo-za_guitar_cover">Onmyo-za Guitar Coversを折れ線グラフで表してみた</a></li>
						<li><a href="#nekomamma_posttime">ねこまんま投稿時間を円グラフで表してみた</a></li>
						<li><a href="#live_counts">ぬこたん公演回数推移をエリアグラフで表してみた</a></li>
					</ul>
				</div>
			</div>
		</div>

		<h3 id="onmyo-za_guitar_cover">Onmyo-za Guitar Coversを折れ線グラフで表してみた</h3>
		<ul>
			<li>私のギター的ぬこたん(*´Д`)ﾊｧﾊｧの軌跡を折れ線グラフにしました。</li>
			<li><a href="http://www.youtube.com/playlist?list=PL5FFFA409EAE8FAC4">Onmyo-za Guitar Covers(@YouTube)</a>と<a href="http://www.nicovideo.jp/mylist/12683379">ぬこたん(*´Д`)ﾊｧﾊｧ　陰陽座演奏動画(@ニコニコ動画)</a>それぞれの再生数(view)とレイティング(rating, ニコ動の場合はマイリス)を偏差値(deviation)で表しています。</li>
			<li>日次でデータを更新しています。</li>
			<li>動画情報を英語に統一し海外ユーザ向けに発信しているYouTubeと、国内向けに発信しているニコ動で再生数/レイティングの傾向の差を把握したくて作ってみました。</li>
			<li>動画数(行数)が多いせいか表示領域がなかなかいうことききません(；´Д｀)</li>
			<li>鬼子母神の無双っぷりにより、横軸は対数表示にしています。</li>
		</ul>

		<div id="chart_onmyo-za_guitar_cover" style="width: 1200px; height: 5000px;"></div>

		<h3 id="nekomamma_posttime">ねこまんま投稿時間を円グラフで表してみた</h3>
		<ul>
			<li>ぬこたんがねこまんまに投稿する時間帯の傾向を円グラフにしました。</li>
			<li>ねこまんま通信の記事の時間を取得し、これを「投稿時間」としています。ただし、実際に公開される時間と若干開きがあるようです(投稿時間ではなく記事を書き始めた時間?)。</li>
			<li>ねこまんま通信の更新を検知するタイミングで更新しています。</li>
			<li>投稿時間からぬこたんの生活リズムおよび傾向を探りたくて作ってみました。</li>
		</ul>

		<div id="chart_nekomamma_posttime" style="width: 900px; height: 500px;"></div>


		<h3 id="live_counts">ぬこたん公演回数推移をエリアグラフで表してみた</h3>
		<ul>
			<li>ぬこたんが執り行ってきた公演回数と演奏してきた楽曲の数の推移をエリアグラフにしました。</li>
			<li>月単位でその月の回数とその月までの累計をプロットしています。</li>
			<li>月次でデータを更新しています。</li>
			<li>よくぬこたんはれっきとしたライブバンドであると紹介されますが、その真偽の程を確かめるために作ってみました。</li>
		</ul>

		<div id="chart_live_counts" style="width: 1000px; height: 600px;"></div>


	</div><!-- /.container -->
EOD;

publishFooter();
