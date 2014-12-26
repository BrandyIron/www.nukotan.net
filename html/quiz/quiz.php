<?php
require_once(dirname(__FILE__) . '/../../lib/include/nukotanDbh2.php');
require_once(dirname(__FILE__) . '/../../lib/include/header.php');
require_once(dirname(__FILE__) . '/../../lib/include/footer.php');

$dbh = getPDO();

session_name('nkquiz');
session_start();

// Check client status
if (!session_name('nkquiz')
	|| ($_SESSION['nkquiz']['count'] == 0 &&
	($_SESSION['nkquiz']['ip'] != $_SERVER['REMOTE_ADDR']
	|| $_SESSION['nkquiz']['refer'] != '/quiz/index.php'))
	|| ($_SESSION['nkquiz']['count'] != 0 &&
	($_SESSION['nkquiz']['ip'] != $_SERVER['REMOTE_ADDR']
	|| $_SESSION['nkquiz']['refer'] != '/quiz/quiz.php'
	|| $_SESSION['nkquiz']['token'] != $_POST['token']))
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

// Create onetime token
$_SESSION['nkquiz']['token'] = md5(uniqid(mt_rand(), true));

// IF posting some answer
// Check Client Status
if ($_POST['question_id']) {
	// Over 20 seconds, setting answer number = 0(wrong number)
	if (!$_POST['question_answer']) $_POST['question_answer'] = 0;

	// Push answer information into session
	array_push($_SESSION['nkquiz']['answers'], array('id' => $_POST['question_id'], 'answer' => $_POST['question_answer'], 'answertime' => $_POST['answertime']));


	// If answer count reaches 20, the quiz is end and register answerer's information
	if ($_SESSION['nkquiz']['count'] == 20) {
		$score = 0;
		foreach ($_SESSION['nkquiz']['answers'] as $key => $value) {
			$stmt = $dbh->prepare('SELECT * FROM nukotan_quiz WHERE level =\'' . $_SESSION['nkquiz']['level'] . '\' AND id = \'' . $value['id'] . '\'');
			$stmt->execute();
			$res = $stmt->fetch(PDO::FETCH_ASSOC);
			if (($res['type'] == 'one_answer' && trim($value['answer']) == trim($res['answer1']))
				|| ($res['type'] == 'two_answer' && (
					(trim($value['answer'][0]) == trim($res['answer1']) && trim($value['answer'][1]) == trim($res['answer2']))
					|| (trim($value['answer'][0]) == trim($res['answer2']) && trim($value['answer'][1]) == trim($res['answer1']))
				))) $score += 5 - $value['answertime'] / 4;
		}
		$_SESSION['nkquiz']['score'] = floor($score);
		
		$stmt = $dbh->prepare('SELECT * FROM nukotan_quiz_answer WHERE ipaddress = \'' . $_SESSION['nkquiz']['ip'] . '\'');
		$stmt->execute();
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!count($res['id'])) {
			$sql = "INSERT INTO nukotan_quiz_user (ipaddress) VALUES (:ipaddress)";
			$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$sth->execute(array(':ipaddress' => $_SESSION['nkquiz']['ip']));
			$stmt = $dbh->prepare('SELECT * FROM nukotan_quiz_user WHERE ipaddress = \'' . $_SESSION['nkquiz']['ip'] . '\'');
			$stmt->execute();
			$res = $stmt->fetch(PDO::FETCH_ASSOC);
			$user_id = $res['id'];
		} else {
			$user_id = $res['id'];
		}

		$stmt = $dbh->prepare('SELECT * FROM nukotan_quiz_record WHERE user_id = \'' . $user_id . '\' AND level =\'' . $_SESSION['nkquiz']['level'] .'\'');
		$stmt->execute();
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!count($res['id'])) {
			// New record
			$sql = "INSERT INTO nukotan_quiz_record (user_id, level, score) VALUES (:user_id, :level, :score)";
			$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$sth->execute(array(':user_id' => $user_id, ':level' => $_SESSION['nkquiz']['level'], ':score' => $_SESSION['nkquiz']['score']));
		} else {
			// Update record
			$sql = "UPDATE nukotan_quiz_record SET score = :score WHERE user_id = :user_id AND level = :level";
			$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$sth->execute(array(':user_id' => $user_id, ':level' => $_SESSION['nkquiz']['level'], ':score' => $_SESSION['nkquiz']['score']));
		}

		header('Location: http://www.nukotan.net/quiz/result.php');
		exit;
	}
}

// Publish Header
publishHeader("ぬこたんクイズ(*´Д`)ﾊｧﾊｧ");

// Timer
echo <<<EOD
<script type="text/javascript"><!--
count = 20;
current = 0;


function Timer() {
	setInterval("CountDown()", 1000);
}
function CountDown() {
	count--;
	current = 20 - count;
	document.getElementById("resttime").innerHTML = "残り"+count+"秒";
	document.getElementById("answertime").value = current;
	if (count == 0) {
		clearInterval(current);
		document.question.submit();
	}
}	
document.body.onload = Timer();
// --></script>
EOD;

// Initial
$ids = array();
if ($_SESSION['nkquiz']['count'] == 0) {
       	$_SESSION['nkquiz']['level'] = $_POST['level'];
	$stmt = $dbh->prepare('SELECT * FROM nukotan_quiz WHERE level = \'' . $_SESSION['nkquiz']['level'] . '\' ORDER BY RAND() LIMIT 1');
	$stmt->execute();
} else {
	$ids = array();
	foreach ($_SESSION['nkquiz']['answers'] as $key => $value) {
		array_push($ids, $value['id']);
	}
	$inQuery = implode(',', $ids);
	$stmt = $dbh->prepare('SELECT * FROM nukotan_quiz WHERE level = \'' . $_SESSION['nkquiz']['level'] . '\' AND id NOT IN(' . $inQuery . ') ORDER BY RAND() LIMIT 1');
	$stmt->execute();
}

$res = $stmt->fetch(PDO::FETCH_ASSOC);

// Increment answer count
$_SESSION['nkquiz']['count']++;
// Set script path
$_SESSION['nkquiz']['refer'] = $_SERVER['SCRIPT_NAME'];

echo <<<EOD
<div class="container">
<p>　</p>
	<div class="page-header">
		<h1>Nukotan Quiz (Level : 
EOD;

echo $_SESSION['nkquiz']['level'] . ')</h1>';
echo '<p>第' . $_SESSION['nkquiz']['count'] . '問 : ' . $res['question'] . '</p></div>';
echo '<p id="resttime">残り20秒</p>';
echo "<form action='{$_SERVER['PHP_SELF']}' method='post' name='question'>";
echo "<input type='hidden' name='question_id' value='{$res['id']}'>";
echo "<input type='hidden' id='token' name='token' value='{$_SESSION['nkquiz']['token']}'>";
echo "<input type='hidden' id='answertime' name='answertime' value=''>";
$answers = array();
if ($res['answer1']) array_push($answers, $res['answer1']);
if ($res['answer2']) array_push($answers, $res['answer2']);
if ($res['answer3']) array_push($answers, $res['answer3']);
if ($res['answer4']) array_push($answers, $res['answer4']);
shuffle($answers);

switch ($res['type']) {
case 'one_answer' : 
	$cnt = 1;
	foreach ($answers as $answer) {
		echo '<div class="radio">
			<label>
			<input type="radio" id="answer" name="question_answer" value=" ' . $answer . '">' . $answer . '</label></div>';
		$cnt++;
	}
	break;
case 'two_answer' :
	$cnt = 1;
	foreach ($answers as $answer) {
		echo '<div class="checkbox">
			<label>
			<input type="checkbox" id="answer" name="question_answer[]" value="' . $answer . '">' . $answer . '</label></div>';
		$cnt++;
	}
	break;

}
echo '<input type="submit" class="btn btn-primary" name="Go To Next">
	</form>
	';
publishFooter();

