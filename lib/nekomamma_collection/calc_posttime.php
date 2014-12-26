<?php

function publishPostTime() {
	require_once(dirname(__FILE__) . '/../include/nukotanDbh2.php');
	$dbh = getPDO();
	$output_filename = '/../../html/chart/posttimes.js';

	$header = "var posttimes = [['Hour', 'PostCount'],\n";
	$output = $header;

	$articleDateArr = array();
	$sql = 'SELECT article_date FROM nukotan_article';
	foreach ($dbh->query($sql) as $row) {
		preg_match('/(\d+):(\d+):(\d+)/', $row['article_date'], $matches);
		array_push($articleDateArr, $matches[1]);
	}

	$hourArr = array();
	foreach ($articleDateArr as $hour) {
		(!$hourArr[$hour]) ? $hourArr[$hour] = 1 : $hourArr[$hour]++;
	}

	arsort($hourArr);
	
	foreach ($hourArr as $key => $val) {
		$output .= "['$key o\'clock', $val],\n";
	}
	$output .= "];";

	file_put_contents(dirname(__FILE__) . $output_filename, $output);
}

