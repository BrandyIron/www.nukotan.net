<?php

include(dirname(__FILE__) . '/../include/simplehtmldom/simple_html_dom.php');

date_default_timezone_set('Asia/Tokyo');
$lastmonth = date('Ym', strtotime(date('Y-m-1').' -1 month'));
$thismonth = date('Ym');

$baseurl = "http://nekomamma.jugem.jp";

$dsn = 'mysql:host=localhost;dbname=nukotan_word';
$username = 'nukotan';
$password = '0716';
$options = array(
	PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
);

$dbh = new PDO($dsn, $username, $password, $options);
	
// Get monthly archive
$monthlyArchives = array();
$html = file_get_html($baseurl);
foreach ($html->find('div.menu_box div.linktext a[href]') as $element) {
	preg_match('/\/\?month=[0-9]{6}/', $element, $matches);
	if ($matches) {
		array_push($monthlyArchives, $matches[0]);
	}
}
$monthlyArchive = array_unique($monthlyArchives);

// Get day archive
foreach ($monthlyArchives as $monthlyArchive) {
	$html = file_get_html($baseurl . $monthlyArchive);
	$dayArchives = array();
	foreach ($html->find('div.menu_box a[href]') as $element) {
		preg_match('/\/\?day=[0-9]{8}/', $element, $matches);
		if ($matches) {
			array_push($dayArchives, $matches[0]);
		}
	}
	$dayArchives = array_unique($dayArchives);
	
	foreach ($dayArchives as $dayArchive) {
		$referer = $baseurl . $dayArchive;
		$html = file_get_html($referer);
		$e = $html->find('div.jgm_entry_desc_mark', 0);
		$wholeString = mb_convert_encoding(preg_replace('/\n|\s|&[a-zA-Z0-9[:punct:]].+?;/', '', $e->plaintext), 'UTF-8', 'EUC-JP');

		while($wholeString) {
			
			$angledBracket = $roundBracket = $detectPosArr = array();
			$angledBracket['open'] = mb_strpos($wholeString, "「");
			$angledBracket['close'] = mb_strpos($wholeString, "」");
			$roundBracket['open'] = mb_strpos($wholeString, "（");
			$roundBracket['close'] = mb_strpos($wholeString, "）");
			
			$detectArr = array('？', '！', '！！', '！！！', '！！！！', '！！！！！', '。', '♪', '?', '!', '!!');
			
			foreach ($detectArr as $searchWord) {
				if (($detectPos = mb_strpos($wholeString, $searchWord)) 
					&& (!(
						($angledBracket['open'] < $detectPos && $detectPos < $angledBracket['close']) 
						|| ($roundBracket['open'] < $detectPos && $detectPos < $roundBracket['close'])
						))
					) $detectPosArr += array($searchWord => $detectPos);
			}
			//print_r($detectPosArr);
			
			if (count($detectPosArr) == 0) {
				$next = 1;
			} else {
				$next = mb_strlen(max(array_keys($detectPosArr, min($detectPosArr))));
			}
			(count($detectPosArr) != 0 && min($detectPosArr) != 0) ? $len = min($detectPosArr) + $next : $len = mb_strlen($wholeString);

			$word = mb_substr($wholeString, 0, $len);
			$wholeString = mb_substr($wholeString, $len);

			echo $word . "\n";
			
			$sql = "SELECT count(id) FROM nukotan_word WHERE word = :word AND referer = :referer";
			$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$sth->execute(array(':word' => $word, ':referer' => $referer));
			$res = $sth->fetchAll();
			if ($res[0]['count(id)'] == 0) {
				$sql = "INSERT INTO nukotan_word (word, referer) VALUES (:word, :referer)";
				
				$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':word' => $word, ':referer' => $referer));
			}
		}
	}
}

?>