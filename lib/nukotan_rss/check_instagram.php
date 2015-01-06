<?php
require_once(dirname(__FILE__) . '/../include/nukotanDbh2.php');
require_once(dirname(__FILE__) . '/../include/tmhOAuth/tmhOAuth.php');
require_once(dirname(__FILE__) . '/../../data/twitter_key.php');

date_default_timezone_set('Asia/Tokyo');

$mode = $argv[1];

$dbh = getPDO();

// Get RSS from nukomamma
$rss_url = 'http://rss.stagram.tk/friendfeed.php?id=1614238207&username=brandy.iron&rss';
if ($fileContents = file_get_contents($rss_url)) {
	$xml = new SimpleXMLElement($fileContents);
	$title = $xml->channel->item[0]->title;
	$link = $xml->channel->item[0]->link;
	$image_object = new SimpleXMLElement($xml->channel->item[0]->description);
	$image_path = $image_object->a->img->attributes()->src[0];
	$pubDate = preg_replace('/ \+0900/', '', $xml->channel->item[0]->pubDate);
	$article_date = date('Y-m-d H:i:s', strtotime($pubDate));
}

// Check whether a same link article exists
$sql = 'SELECT link, title, article_date FROM instagram_rss WHERE link = :link';
$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':link' => $link));
$res = $sth->fetchAll();

$message_sql = "SELECT link, title, article_date FROM instagram_rss WHERE link = $link";
$message_sql .= print_r($res, true);
$update_flag = false;

// Set minimum tweet count to imported instagram article
$sql = 'SELECT MIN(tweet_count) FROM (SELECT tweet_count FROM nukotan_word UNION SELECT tweet_count FROM instagram_rss) AS mix';
$row = $dbh->query($sql)->fetch();
$tweet_count = $row['MIN(tweet_count)'];

if (!$res && $link) {
	// insert as a new article	
	$sql = 'INSERT INTO instagram_rss (link, title, article_date, image_path, tweet_count) VALUES (:link, :title, :article_date, :image_path, :tweet_count)';
	$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	if ($mode == "run") $sth->execute(array(
		':link' => $link,
		':title' => $title,
		':article_date' => $article_date,
		':image_path' => $image_path,
		':tweet_count' => $tweet_count
	));
	$message_sql .= "INSERT INTO instagram_rss (link, title, article_date, image_path, tweet_count) VALUES ($link, $title, $article_date, $image_path, $tweet_count)";
	$update_flag = true;
} elseif ($res[0]['article_date'] != $article_date) {
	// update as a modified article	
	$sql = 'UPDATE instagram_rss SET article_date = :article_date, title = :title, image_path = :image_path, tweet_count = :tweet_count WHERE link = :link';
	$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	if ($mode == "run") $sth->execute(array(
		':link' => $link,
		':title' => $title,
		':article_date' => $article_date,
		':image_path' => $image_path,
		':tweet_count' => $tweet_count
	));
	$message_sql .= "UPDATE instagram_rss SET article_date = $article_date, title = $title, image_path = $image_path, tweet_count = $tweet_count WHERE link = $link";
	$update_flag = true;
} 

if ($update_flag) {
	$message = "
ぬこたんInstagramが更新されました(*´Д`)ﾊｧﾊｧ

$title";
	if (mb_strlen($message) >= 90) {
		$message = mb_substr($message, 0, 87) . "...";
	}
	$message .= " $link";
	echo date('Ymd-His') . ' : ' . $message_sql . "\n";
	// Send tweet
	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $consumerKey,
		'consumer_secret' => $consumerSecret,
		'user_token' => $accessToken,
		'user_secret' => $accessTokenSecret,
		'curl_ssl_verifypeer' => false
	));
	if ($mode == "run") {
		$image_binary = file_get_contents($image_path);
		//print_r($image_binary);
		$res = $tmhOAuth->request('POST',
		       $tmhOAuth->url('1.1/statuses/update_with_media'),	
			array(
				'media[]' => "{$image_binary};type=image/jpeg;filename=nukotan_instagram",
				'status' => $message
			),
			true,
			true
		);
		if ($res == 200) {
			print_r(json_decode($tmhOAuth->response['response']));
		} else {
			print_r($tmhOAuth->response);
		}
	}
}
