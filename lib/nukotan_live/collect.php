<?php
include('simplehtmldom/simple_html_dom.php');

$lastmonth = date('Ym', strtotime(date('Y-m-1').' -1 month'));
$thismonth = date('Ym');

if(isset($argv[1])) {
	$param = $argv[1];
} else {
	$param = "";
}

$baseurl = "http://www.onmyo-za.net/";

$biography_list = array('index.html');
for ($year = date("Y") - 1;$year >= 1999; $year--) {
	array_push($biography_list, "$year.html");
}

foreach ($biography_list as $l) {
	if ($param == "all" || preg_match("/$param/", $l)) {
		$setListUrls = getSetListUrl($baseurl . 'biography/' . $l);
		
		foreach ($setListUrls as $setListUrl) {
			$liveinfo = getLiveInfo($baseurl . $setListUrl);
			insertLiveInfo($liveinfo, $param);
		}
	} elseif ($param =="new") {
	// newest retrieve
		$setListUrls = getSetListUrl($baseurl . 'biography/' . $l);
		
		foreach ($setListUrls as $setListUrl) {
			if (preg_match("/$lastmonth|$thismonth/", $setListUrl)) {
				$liveinfo = getLiveInfo($baseurl . $setListUrl);
				insertLiveInfo($liveinfo, $param);
			}
		}
	}
}


function getSetListUrl($url) {
	$setListUrls = array();
	$html = file_get_html($url);
	
	foreach ($html->find('a') as $element) {
		preg_match('/setlist\/.*\.html/', $element, $matches);
		if ($matches) {
			array_push($setListUrls, $matches[0]);
		}
	}
	return $setListUrls;
}

function getLiveInfo($url) {
	$resultArray = array();
	$setLists = array();
	$html = file_get_html($url);
	
	$live_title = $html->find('div.header h1', 0)->plaintext;
	list($live_date, $live_place) = explode('/', $html->find('div.header p', 0)->plaintext);
	
	$pattern = array('/年|月/', '/日/');
	$replacement = array('-', '');
	$live_date = preg_replace($pattern, $replacement, $live_date);
	
	$resultArray['live_title'] = trim($live_title);
	$live_date_array = explode('-', trim($live_date));
	$resultArray['live_date'] = sprintf('%04d-%02d-%02d', $live_date_array[0], $live_date_array[1], $live_date_array[2]); 
	
	$resultArray['live_place'] = trim($live_place);
	
	foreach ($html->find('ul.right') as $element) {
		foreach ($element->find('li') as $song) {
			array_push($setLists, trim($song->plaintext));
		}
	}
	$resultArray['live_setLists'] = $setLists;
	
	return $resultArray;
}

function insertLiveInfo($liveinfo, $param) {
	$dsn = 'mysql:host=localhost;dbname=nukotan_live';
	$username = 'nukotan';
	$password = '0716';
	$options = array(
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
	);

	$dbh = new PDO($dsn, $username, $password, $options);
	
	foreach ($liveinfo['live_setLists'] as $song) {
		$sql = "SELECT count(id) FROM nukotan_live WHERE date = :live_date AND song = :song";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':live_date' => $liveinfo[live_date], ':song' => $song));
		$res = $sth->fetchAll();
		if ($res[0]['count(id)'] == 0 || $param != "new") {
			$sql = "INSERT INTO nukotan_live (title, date, place, song) VALUES (:live_title, :live_date, :live_place, :song)";
			$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$sth->execute(array(':live_title' => $liveinfo[live_title], ':live_date' => $liveinfo[live_date], ':live_place' => $liveinfo[live_place], ':song' => $song));

			echo "insert entry : $liveinfo[live_date] $song\n";
		}
	}
}

?>
