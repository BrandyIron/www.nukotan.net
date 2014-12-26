<?php

require_once(dirname(__FILE__) . '/../include/simplehtmldom/simple_html_dom.php');
require_once(dirname(__FILE__) . '/../include/nukotanDbh2.php');

$dbh = getPDO();

date_default_timezone_set('Asia/Tokyo');
$lastmonth = date('Ym', strtotime(date('Y-m-1').' -1 month'));
$thismonth = date('Ym');

$baseurl = "http://nekomamma.jugem.jp";

// Get monthly archive
$monthlyArchives = array();

if ($argc == 1) {
	$html = file_get_html($baseurl);
	foreach ($html->find('div.menu_box div.linktext a[href]') as $element) {
		preg_match('/\/\?month=[0-9]{6}/', $element, $matches);
		if ($matches) {
			array_push($monthlyArchives, $matches[0]);
		}
	}
	$monthlyArchives = array_unique($monthlyArchives);
} elseif ($argc == 2) {
	array_push($monthlyArchives, '/?month=' . $argv[1]);
}

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
				$sql = "SELECT MIN(tweet_count) FROM nukotan_word UNION SELECT MIN(tweet_count) FROM instagram_rss";
				$row = $dbh->query($sql)->fetch();
				$tweet_count = $row['MIN(tweet_count)'];
				$sql = "INSERT INTO nukotan_word (word, referer, tweet_count) VALUES (:word, :referer, :tweet_count)";
				
				$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':word' => $word, ':referer' => $referer, ':tweet_count' => $tweet_count));
			}
		}
	}
}

?>
