<?php

require_once(dirname(__FILE__) . '/../include/nukotanDbh2.php');

$dbh = getPDO();
$fp = fopen($argv[1], 'r');

$cnt = 0;
$sql = "SET NAMES utf8;TRUNCATE nukotan_quiz;";
while (($line = fgetcsv($fp, 1000, ',')) !== FALSE) {
	if ($cnt == 0) {
		$cnt++;
		continue;
	}

	list($question, $type, $answer1, $answer2, $answer3, $answer4, $level, $comment) = $line;
	$question = preg_replace("/'/", '\\\'', $question);
	$answer1 = preg_replace("/'/", '\\\'', $answer1);
	$answer2 = preg_replace("/'/", '\\\'', $answer2);
	$answer3 = preg_replace("/'/", '\\\'', $answer3);
	$answer4 = preg_replace("/'/", '\\\'', $answer4);
	$comment = preg_replace("/'/", '\\\'', $comment);
	$replace_from = array('/1/', '/2/', '/3/', '/4/');
	$replace_to = array('normal', 'hard', 'maniac', 'lunatic');
	$level = preg_replace($replace_from, $replace_to, $level);
	
	$sql .= "INSERT INTO nukotan_quiz (question, type, answer1, answer2, answer3, answer4, level, comment) VALUES ('$question', '$type', '$answer1', '$answer2', '$answer3', '$answer4', '$level', '$comment');\n";
}

$dbh->query($sql);
