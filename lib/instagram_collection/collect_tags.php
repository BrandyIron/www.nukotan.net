<?php
require_once(dirname(__FILE__) . '/../include/nukotanDbh2.php');

date_default_timezone_set('Asia/Tokyo');

function publishCloudTag() {
	$dbh = getPDO();

	$tags = array();

	$sql = 'SELECT title, link FROM instagram_rss ORDER BY article_date';
	foreach ($dbh->query($sql) as $row) {
		preg_match_all('/(#[^#]*)/', $row['title'], $matches);
		foreach ($matches[0] as $match) {
			$match = trim($match, '# ');
			if (array_key_exists($match, $tags)) {
				$tags[$match] = array('count' => $tags[$match]['count'] + 1, 'link' => $row['link']);
			} else {
				$tags[$match] = array('count' => 1, 'link' => $row['link']);
			}
		}
	}

	ksort($tags);

	$cnt = 2;
	foreach ($tags as $key => $val) {
		$fontsize = $val['count'] * 5;
		echo "<a href='{$val['link']}' class='tag-link-$cnt' title='{$val['count']} topics' style='font-size: {$fontsize}pt;'>{$key}</a>\n";
		$cnt++;
	}
}

