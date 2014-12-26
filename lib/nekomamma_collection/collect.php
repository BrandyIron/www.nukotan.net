<?php

function nekomamma_collection ($month) {
	require_once(dirname(__FILE__) . '/../include/simplehtmldom/simple_html_dom.php');
	require_once(dirname(__FILE__) . '/../include/nukotanDbh2.php');
	require_once(dirname(__FILE__) . '/calc_posttime.php');

	date_default_timezone_set('Asia/Tokyo');
	$baseurl = 'http://nekomamma.jugem.jp';

	$dbh = getPDO();

	//Get monthly archive
	$monthlyArchives = array();

	if (!$month) {
		$html = file_get_html($baseurl);
		foreach ($html->find('div.menu_box div.linktext a[href]') as $element) {
			preg_match('/\/\?month=[0-9]{6}/', $element, $matches);
			if ($matches) {
				array_push($monthlyArchives, $matches[0]);
			}
		}
		$monthlyArchives = array_unique($monthlyArchives);
	} elseif (preg_match('/[0-9]{6}/', $month)) {
		array_push($monthlyArchives, '/?month=' . $month);
	}

	//Get day archive
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
			foreach ($html->find('td.entry_main') as $element) {
				$date = $element->find('div.entry_date', 0)->plaintext;
				$title = mb_convert_encoding($element->find('div.entry_title', 0)->plaintext, 'UTF-8', 'EUC-JP');
				$time = $element->find('div.entry_state a', 0)->plaintext;
				$article_date = preg_replace('/(\d+)\.(\d+)\.(\d+) (\w+)/', '${1}-${2}-${3}', $date) . ' ' . $time . ":00";
				$links = $element->find('div.entry_state a[href]');
				$body = mb_convert_encoding($element->find('div.jgm_entry_desc_mark', 0)->plaintext, 'UTF-8', 'EUC-JP');
				foreach ($links as $link) {
					if (preg_match('/\.\/\?eid=[0-9]+/', $link, $matches)) $link = $baseurl . preg_replace('/\./', '', $matches[0]); 
				}

				echo "article_date : $article_date\ntitle : $title\nlinks : $link\nbody :  $body\n";

				$sql = 'SELECT link, title, article_date, body FROM nukotan_article WHERE link = :link';
				$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':link' => $link));
				$res = $sth->fetchAll();

				if (!$res) {
					// insert as a new article
					$sql = 'INSERT INTO nukotan_article (title, article_date, link, body) VALUES (:title, :article_date, :link, :body)';
					$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':title' => $title, ':article_date' => $article_date, ':link' => $link, ':body' => $body));
				} elseif ($res[0]['article_date'] != $article_date || $res[0]['title'] != $title || $res[0]['body'] != $body) {
					// update as a modified article
					$sql = 'UPDATE nukotan_article SET title = :title, article_date = :article_date, body = :body) WHERE link = :link';
					$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':title' => $title, ':article_date' => $article_date, ':link' => $link, ':body' => $body));
				}
			}
		}
	}

	// update Nekomamma PostTime Chart
	publishPostTime();
}
