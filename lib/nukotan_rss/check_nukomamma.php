<?php

require_once(dirname(__FILE__) . '/../include/simplehtmldom/simple_html_dom.php');
require_once(dirname(__FILE__) . '/../include/twitteroauth/twitteroauth.php');
require_once(dirname(__FILE__) . '/../include/nukotanDbh2.php');
require_once(dirname(__FILE__) . '/../../data/twitter_key.php');
require_once(dirname(__FILE__) . '/../nekomamma_collection/collect.php');

date_default_timezone_set('Asia/Tokyo');

$twitterURL = 'https://api.twitter.com/1.1/statuses/update.json';
$mode = $argv[1];

$dbh = getPDO();

// Parse nukomamma HTML
$baseurl = 'http://nekomamma.jugem.jp/';

// Get recent articles
$html = file_get_html($baseurl);

$eids = array();
foreach ($html->find('div.menu_box a[href*=eid]') as $element) {
	if (preg_match('/\?eid=[0-9]+/', $element, $matches)) {
		array_push($eids, $matches[0]);
	}
}
rsort($eids);

// Get latest article
$link = $baseurl . $eids[0];
$html = file_get_html($link);
foreach ($html->find('div.entry_title') as $element) {
	$title = mb_convert_encoding($element->plaintext, 'UTF-8', 'EUC-JP');
}
foreach ($html->find('div.entry_date') as $element) {
	$ymd = preg_replace('/\./', '-', explode(' ', $element->plaintext)[0]);
}
foreach ($html->find('div.entry_state') as $element) {
	preg_match('/[0-9]{2}:[0-9]{2}/', $element->plaintext, $hi);
}
$article_date = "$ymd {$hi[0]}:00";

// Check whether a same link article exists
$sql = 'SELECT link, title, article_date FROM nukotan_rss WHERE link = :link';
$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':link' => $link));
$res = $sth->fetchAll();

$message_sql = "SELECT link, title, article_date FROM nukotan_rss WHERE link = $link";
$message_sql .= print_r($res, true);
$update_flag = false;
if (!$res && $link) {
	// insert as a new article	
	$sql = 'INSERT INTO nukotan_rss (link, title, article_date) VALUES (:link, :title, :article_date)';
	$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	if ($mode == "run") $sth->execute(array(
		':link' => $link,
		':title' => $title,
		':article_date' => $article_date
	));
	$message_sql .= "INSERT INTO nukotan_rss (link, title, article_date) VALUES ($link, $title, $article_date)";
	$update_flag = true;
} elseif ($res[0]['article_date'] != $article_date || $res[0]['title'] != $title) {
	// update as a modified article	
	$sql = 'UPDATE nukotan_rss SET article_date = :article_date, title = :title WHERE link = :link';
	$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	if ($mode == "run") $sth->execute(array(
		':link' => $link,
		':title' => $title,
		':article_date' => $article_date
	));
	$message_sql .= "UPDATE nukotan_rss SET article_date = $article_date, title = $title WHERE link = $link";
	$update_flag = true;
} 

if ($update_flag) {
	$message = mb_substr("
黒猫のねこまんま通信が更新されました(*´Д`)ﾊｧﾊｧ 

$title", 0, 110);
	$message .= " $link";
	echo date('Ymd-His') . ' : ' . $message_sql . "\n";
	// Send tweet
	$to = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
	if ($mode == "run") $req = $to->OAuthRequest($twitterURL, 'POST', array('status' => $message));

	// Kick Nekomamma Collection
	nekomamma_collection(date('Ym'));
}
