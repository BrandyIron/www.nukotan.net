<?php
require_once(dirname(__FILE__) . '/../lib/include/header.php');
require_once(dirname(__FILE__) . '/../lib/include/footer.php');

publishHeader("ぬこたん☆どっとねっと");

echo <<<EOD
	<div class="jumbotron">
		<div class="container">
			<h1>ぬこたん☆どっとねっと(*´Д`)ﾊｧﾊｧ</h1>
			<p>ぬこたん☆どっとねっと(*´Д`)ﾊｧﾊｧはぬこたん特化型ポータルサイトです。</p>
		</div>
	</div>

	<div class="container">
		<div class="row">
			<div class="col-md-4">
				<h2><a href="http://www.nukotan.net/live/">ぬこたん公演まとめ(*´Д`)ﾊｧﾊｧ</a></h2>
				<p>ぬこたんの公演についての情報をまとめています。</p>
				<p>過去演奏された楽曲のランキングをチェックできます。</p>
			</div>
			<div class="col-md-4">
				<h2><a href="http://www.nukotan.net/chart/">ぬこたんグラフ(*´Д`)ﾊｧﾊｧ</a></h2>
				<p>さまざまなぬこたんの情報を視覚化しています。</p>
			</div>
			<div class="col-md-4">
				<h2><a href="http://www.nukotan.net/quiz/">ぬこたんクイズ(*´Д`)ﾊｧﾊｧ</a></h2>
				<p>ぬこたんに関するクイズです。</p>
				<p>4つのレベルを選択できます。</p>
			</div>
		</div>
		<div class="row">
			<div class="col-md-4">
				<h2><a href="http://nukomamma.jugem.jp/">つれづれなるぬこたん(*´Д`)ﾊｧﾊｧ</a></h2>
				<p>ねこまんま通信のフィッシングサイトです。</p>
				<p>陰陽座のギターカバーやぬこたんの料理レシピを紹介しています。</p>
			</div>
			<div class="col-md-4">
				<h2><a href="https://twitter.com/nukotan_bot/">ぬこたんBOT(*´Д`)ﾊｧﾊｧ</a></h2>
				<p>ぬこたんのお言葉を発信し、ねこまんま通信の更新をお届けするTwitter BOTです。</p>
				<p><a href="http://www.nukotan.net/image/Nukotan BOT system specifications2.png">仕様書（お言葉Tweet）(*´Д`)ﾊｧﾊｧ</a></p>
				<p><a href="http://www.nukotan.net/image/Nukotan BOT system specifications1.png">仕様書（ねこまんま更新検知）(*´Д`)ﾊｧﾊｧ</a></p>
			</div>
			<div class="col-md-4">
				<h2>Onmyo-za Guitar Covers</h2>
				<p>陰陽座の楽曲をギターで演奏しています。招鬼、狩姦の両パート演奏しています。</p>
				<p>2014年1月時点で、全てのぬこたんの楽曲(138曲)をカバーしています。</p>
				<p><a class="btn btn-default" href="http://www.youtube.com/playlist?list=PL5FFFA409EAE8FAC4">YouTube</a></p>
				<p><a class="btn btn-default" href="http://www.nicovideo.jp/mylist/12683379">ニコニコ動画</a></p>
			</div>
		</div>
		<div class="row">
			<p>管理人: BlackChubby。またの名をWhiteChubby。更にまたの名をBrandyIron。</p>
			<p>ぬこたんなしの生活は考えられません(*´Д`)ﾊｧﾊｧ。コンタクト先→<a href="https://twitter.com/BrandyIron">(*´Д`)ﾊｧﾊｧ</a></p>
		</div>
	</div>
EOD;

publishFooter();
