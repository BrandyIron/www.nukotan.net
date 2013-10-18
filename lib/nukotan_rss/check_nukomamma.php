<?php
require_once(dirname(__FILE__) . '/../include/twitteroauth/twitteroauth.php');
require_once(dirname(__FILE__) . '/../../data/twitter_key.php');

date_default_timezone_set('Asia/Tokyo');

$twitterURL = 'https://api.twitter.com/1.1/statuses/update.json';
$mode = $argv[1];

$dsn = 'mysql:host=localhost;dbname=nukotan_word';
$username = 'nukotan';
$password = '0716';
$options = array(
	PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
);
$dbh = new PDO($dsn, $username, $password, $options);

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
$sql = 'SELECT link, title, article_date FROM nukotan_article WHERE link = :link';
$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':link' => $link));
$res = $sth->fetchAll();

$update_flag = false;
if (!$res) {
	// insert as a new article	
	$sql = 'INSERT INTO nukotan_article (link, title, article_date) VALUES (:link, :title, :article_date)';
	$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	if ($mode == "run") $sth->execute(array(
		':link' => $link,
		':title' => $title,
		':article_date' => $article_date
	));
	$update_flag = true;
} elseif ($res[0]['article_date'] != $article_date || $res[0]['title'] != $title) {
	// update as a modified article	
	$sql = 'UPDATE nukotan_article SET article_date = :article_date, title = :title WHERE link = :link';
	$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	if ($mode == "run") $sth->execute(array(
		':link' => $link,
		':title' => $title,
		':article_date' => $article_date
	));
	$update_flag = true;
} 

if ($update_flag) {
	$message = mb_substr("
黒猫のねこまんま通信が更新されました(*´Д`)ﾊｧﾊｧ 
$title", 0, 110);
	$message .= " $link";
	echo date('Ymd-His') . ' : ' . $message . "\n";
	// Send tweet
	$to = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
	if ($mode == "run") $req = $to->OAuthRequest($twitterURL, 'POST', array('status' => $message));
}
