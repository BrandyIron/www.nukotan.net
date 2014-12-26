<?php
require_once(dirname(__FILE__) . '/../include/twitteroauth/twitteroauth.php');
require_once(dirname(__FILE__) . '/../include/nukotanDbh2.php');
require_once(dirname(__FILE__) . '/../../data/twitter_key.php');
require_once(dirname(__FILE__) . '/../nekomamma_collection/collect.php');

date_default_timezone_set('Asia/Tokyo');

$twitterURL = 'https://api.twitter.com/1.1/statuses/update.json';
$mode = $argv[1];

$dbh = getPDO();

// Get RSS from nukomamma
$rss_url = 'http://nekomamma.jugem.jp/?mode=rss';
if ($fileContents = file_get_contents($rss_url)) {
	$xml = new SimpleXMLElement($fileContents);
	$title = $xml->item[0]->title;
	$link = $xml->item[0]->link;
	$dc = $xml->item[0]->children('http://purl.org/dc/elements/1.1/');
	$article_date = date('Y-m-d H:i:s', strtotime($dc->date));
}


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
