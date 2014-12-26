<?php
require_once(dirname(__FILE__) . '/../../lib/include/nukotanDbh2.php');
require_once(dirname(__FILE__) . '/../../lib/include/header.php');
require_once(dirname(__FILE__) . '/../../lib/include/footer.php');

session_name('nkquiz');
session_start();

if (!session_name('nkquiz')
	|| $_SESSION['nkquiz']['ip'] != $_SERVER['REMOTE_ADDR']
	|| $_SESSION['nkquiz']['refer'] != '/quiz/quiz.php'
) {
	$_SESSION = array();
	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
								                }
		session_destroy();
		publishHeader("エラー(*´Д`)ﾊｧﾊｧ");
echo <<<EOD
<div class="container">
<p>　</p>
<p>　</p>
<p>　</p>
<p>エラーが発生しました(*´Д`)ﾊｧﾊｧ</p>
<p>ブラウザのJavaScript/CookieがONになっているか確認してください(*´Д`)ﾊｧﾊｧ</p>
<p><a href="http://www.nukotan.net/quiz/">http://www.nukotan.net/quiz/</a>からやり直してください(*´Д`)ﾊｧﾊｧ</p>
</div>
EOD;
publishFooter();
exit();
}

// Publish Header
publishHeader("ぬこたんクイズ(*´Д`)ﾊｧﾊｧ");

$dbh = getPDO();
// Rating
$score = $_SESSION['nkquiz']['score'];
$comments = array();
foreach ($_SESSION['nkquiz']['answers'] as $key => $value) {
	$stmt = $dbh->prepare('SELECT * FROM nukotan_quiz WHERE level =\'' . $_SESSION['nkquiz']['level'] . '\' AND id = \'' . $value['id'] . '\'');
	$stmt->execute();
	$res = $stmt->fetch(PDO::FETCH_ASSOC);
	array_push($comments, array('question' => $res['question'], 'youranswer' => $value['answer'], 'answer1' => trim($res['answer1']), 'answer2' => trim($res['answer2']), 'type' => $res['type'], 'comment' => $res['comment']));
}

// Calcrating your ranking
$stmt = $dbh->prepare('SELECT * FROM nukotan_quiz_record WHERE level =\'' . $_SESSION['nkquiz']['level'] .'\' AND score >= \'' . $_SESSION['nkquiz']['score'] . '\'');
$stmt->execute();
$ranking = $stmt->rowCount();

$stmt = $dbh->prepare('SELECT * FROM nukotan_quiz_record WHERE level =\'' . $_SESSION['nkquiz']['level'] .'\'');
$stmt->execute();
$total_answerer = $stmt->rowCount();

echo <<<EOD
<div class="container">
	<p>　</p>
	        <div class="page-header">
		<h1>お疲れ様でした。採点結果です(*´Д`)ﾊｧﾊｧ</h1>
EOD;

echo "<h2>難易度:{$_SESSION['nkquiz']['level']}</h2>";
echo "<h2>得点:$score 点 (100点満点)</h2>";
echo "<h2>順位:$ranking 位 ($total_answerer 人中)</h2></div>";

echo '<p><a href="http://www.nukotan.net/quiz/">http://www.nukotan.net/quiz/</a>へ戻る(*´Д`)ﾊｧﾊｧ</p>';

echo "<h3>回答および解説</h3>";

$count = 1;
foreach ($comments as $value) {
	if ($value['type'] == 'one_answer') {
		if (trim($value['youranswer']) == $value['answer1']) {
			$bgtype = "success";
		} else {
			$bgtype = "danger";
		}

		echo "<table class='table'>
			<thead></thead>
			<tbody>
			<tr class='$bgtype'>
			<td colspan='3'>第 $count 問 : {$value ['question']}</td>
			</tr>
			<tr class='$bgtype'>
			<td>正解 : {$value['answer1']}</td>
			<td>あなたの回答 : {$value['youranswer']}</td>
			</tr>
			<tr class='$bgtype'><td colspan='3'>{$value['comment']}</td></tr>
			</tbody>
			</table>\n";
	} else {
		if ((trim($value['youranswer'][0]) == $value['answer1'] && trim($value['youranswer'][1]) == $value['answer2'])
			|| (trim($value['youranswer'][0]) == $value['answer2'] && trim($value['youranswer'][1]) == $value['answer1'])
		) {
			$bgtype = "success";
		} else {
			$bgtype = "danger";
		}

		echo "<table class='table'>
			<thead></thead>
			<tbody>
			<tr class='$bgtype'>
			<td colspan='3'>第 $count 問 : {$value ['question']}</td>
			</tr>
			<tr class='$bgtype'>
			<td>正解 : {$value['answer1']}, {$value['answer2']}</td>
			<td>あなたの回答 : {$value['youranswer'][0]}, {$value['youranswer'][1]}</td>
			</tr>
			<tr class='$bgtype'><td colspan='3'>{$value['comment']}</td></tr>
			</tbody>
			</table>\n";
	}

	$count++;
}

// Display ranking
echo "<h3>ランキング</h3>
	<table class='table table-striped'>
	<thead>
	<tr>
	<td>順位</td>
	<td>回答日時</td>
	<td>スコア</td>
	</tr>
	</thead>
	<tbody>\n";

$count = 1;
foreach ($dbh->query('SELECT * FROM nukotan_quiz_record WHERE level =\'' . $_SESSION['nkquiz']['level'] .'\' ORDER BY score desc') as $row) {
	echo "<tr>
		<td>$count</td>
		<td>{$row['answer_date']}</td>
		<td>{$row['score']}</td>
		</tr>\n";
	$count++;
}
echo "</tbody></table>\n";

publishFooter();
