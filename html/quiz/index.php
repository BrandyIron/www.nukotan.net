<?php
require_once(dirname(__FILE__) . '/../../lib/include/header.php');
require_once(dirname(__FILE__) . '/../../lib/include/footer.php');


session_name('nkquiz');
session_start();
$_SESSION['nkquiz']['count'] = 0;
$_SESSION['nkquiz']['answers'] = array();
$_SESSION['nkquiz']['ip'] = $_SERVER['REMOTE_ADDR'];
$_SESSION['nkquiz']['refer'] = $_SERVER['SCRIPT_NAME'];

publishHeader("ぬこたんクイズ(*´Д`)ﾊｧﾊｧ");

echo <<<EOD
<div class="container">
	<div class="page-header">
			<p>　</p>
                        <h1>ぬこたんクイズ(*´Д`)ﾊｧﾊｧ</h1>
                        <p class="lead">あなたは何問答えられる？！</p>
                        <p class="lead">ぬこたんファンによるぬこたんファンのためのぬこたんただひとりに狙いを定めたクイズです。</p>
                </div>
                <h3>注意書き</h3>
                <ul>
                        <li>ソースは主にねこまんま通信です。雑誌でのインタビュー、ラジオ番組での発言はあまりソースにしていません。</li>
                        <li>問題は20問でランダムで選ばれます。</li>
                        <li>一問一答形式です。ブラウザのCookie/JavaScriptを有効にしておいてください。リロードやブラウザバックするとエラーになります。</li>
                        <noscript><li>JavaScriptが無効なので、有効にしてください</li></noscript>
                        <li>一問20秒以内で答えてください。20秒過ぎると不正解となり自動的に次の質問へ移動します。回答時間が短いほど得点が上がります。</li>
                        <li>全ての問題に回答すると解説およびランキングが表示されます。回答者のIPアドレスベースでランキングを作成しますが、晒しませんのでご安心ください。</li>
                        <li>ぬこたんに(*´Д`)ﾊｧﾊｧしながら作成したものなのでおかしな挙動することがあるかもしれません。そんなときは<a href="https://twitter.com/BrandyIron">Twitter</a>で知らせてくれると助かります。</li>
                        <li>難易度は以下の4つを用意しています。</li>
                        <ul>
                                <li>ノーマル : <strong><font color="green">普通の陰陽座ファン</font></strong>向け。陰陽座公式庵や陰陽座の作品やWikipedia紹介レベルの知識があれば回答できると思います。</li>
                                <li>ハード : 好きなメンバーはぬこたん！という<strong><font color="blue">ぬこたんファン</font></strong>向け。少しぬこたんに詳しくないと答えられません。</li>
                                <li>マニアック : ぬこたんへの深い知識と洞察力をお持ちの<strong><font color="orange">ぬこたん研究者/探求者</font></strong>向け。ほぼ大体ぬこたんのことばかり考えてないと答えられません。</li>
                                <li>ルナティック : 暇な時間はねこまんまブログを徘徊しているような<strong><font color="red">ぬこたん廃人</font></strong>向け。美味しいちゃん関連の問題が多いです。難問奇問悪問多いです。</li>

                        </ul>
                </ul>

                <h3>上記を読み、同意された方は以下からどうぞ(*´Д`)ﾊｧﾊｧ！</h3>

		<form action="/quiz/quiz.php" method="POST">
                <p class="text-center"><button type="submit" class="btn btn-success btn-lg btn-block" name="level" value="normal">ノーマル</button></p>
                <p class="text-center"><button type="submit" class="btn btn-primary btn-lg btn-block" name="level" value="hard">ハード</button></p>
                <p class="text-center"><button type="submit" class="btn btn-warning btn-lg btn-block" name="level" value="maniac">マニアック</button></p>
                <p class="text-center"><button type="submit" class="btn btn-danger btn-lg btn-block" name="level" value="lunatic">ルナティック</button></p>
		</form>

EOD;

publishFooter();
